<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Models\Accountant;
use App\Models\Admin;
use App\Models\Building;
use App\Models\PropertyManager;
use App\Models\Sale;
use App\Models\Tenant;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

/**
 * Class AuthService
 * 
 * Tầng nghiệp vụ xử lý toàn bộ logic xác thực đa tác nhân (Multi-guard Multi-actor Authentication),
 * tương thích chuẩn cấu trúc Model trên nhánh develop (status ACTIVE/LOCKED, UUID v7, login_email).
 */
class AuthService
{
    /**
     * Bản đồ ánh xạ giữa UserRole enum và lớp Model tương ứng.
     *
     * @var array<string, class-string>
     */
    protected array $roleModelMap = [
        UserRole::ADMIN->value            => Admin::class,
        UserRole::SALES->value            => Sale::class,
        UserRole::PROPERTY_MANAGER->value => PropertyManager::class,
        UserRole::ACCOUNTANT->value       => Accountant::class,
        UserRole::TENANT->value           => Tenant::class,
    ];

    /**
     * Xử lý đăng nhập đa tác nhân qua email và mật khẩu.
     *
     * @param  array{email: string, password: string}  $credentials  Thông tin đăng nhập
     * @param  string|null  $requestedRole  Vai trò chỉ định (nếu có)
     * @return array{token_type: string, access_token: string, role: string, user: mixed, assigned_building_ids: array}
     * 
     * @throws ValidationException
     */
    public function login(array $credentials, ?string $requestedRole = null): array
    {
        $email = trim($credentials['email']);
        $password = $credentials['password'];

        $rolesToCheck = $requestedRole
            ? [$requestedRole]
            : array_keys($this->roleModelMap);

        $authenticatedUser = null;
        $matchedRole = null;

        // Quét qua danh sách vai trò để tìm kiếm tài khoản trùng khớp
        foreach ($rolesToCheck as $roleKey) {
            $modelClass = $this->roleModelMap[$roleKey] ?? null;
            if (! $modelClass) {
                continue;
            }

            // Tenant hỗ trợ tìm kiếm qua login_email hoặc email
            if ($modelClass === Tenant::class) {
                $user = $modelClass::where('login_email', $email)
                    ->orWhere('email', $email)
                    ->first();
            } else {
                $user = $modelClass::where('email', $email)->first();
            }

            if ($user && Hash::check($password, $user->password)) {
                $authenticatedUser = $user;
                $matchedRole = $roleKey;
                break;
            }
        }

        // Trường hợp không tìm thấy tài khoản hoặc mật khẩu sai
        if (! $authenticatedUser || ! $matchedRole) {
            Log::warning('Đăng nhập thất bại cho email: ' . $email, [
                'requested_role' => $requestedRole,
            ]);

            throw ValidationException::withMessages([
                'email' => ['Thông tin đăng nhập hoặc mật khẩu không chính xác.'],
            ]);
        }

        // Kiểm tra trạng thái hoạt động: chuẩn 'ACTIVE' hoặc boolean true
        $isActive = ($authenticatedUser->status ?? null) === 'ACTIVE'
            || ($authenticatedUser->is_active ?? false) === true;

        if (! $isActive) {
            Log::warning('Đăng nhập thất bại: Tài khoản đang bị khóa', [
                'user_id' => $authenticatedUser->id,
                'role'    => $matchedRole,
                'status'  => $authenticatedUser->status ?? 'INACTIVE',
            ]);

            throw ValidationException::withMessages([
                'email' => ['Tài khoản của bạn đã bị khóa hoặc ngừng hoạt động. Vui lòng liên hệ Quản trị viên.'],
            ]);
        }

        try {
            // Cập nhật thời điểm đăng nhập gần nhất nếu cột tồn tại
            if (isset($authenticatedUser->last_login_at)) {
                $authenticatedUser->update(['last_login_at' => now()]);
            }

            // Cấp phát Sanctum Bearer Token với quyền (abilities) tương ứng với vai trò
            $tokenResult = $authenticatedUser->createToken('auth-token', [$matchedRole]);
            $accessToken = $tokenResult->plainTextToken;

            // Xác định danh sách ID các tòa nhà được phân quyền phụ trách
            $assignedBuildingIds = $this->resolveAssignedBuildingIds($authenticatedUser, $matchedRole);

            Log::info('Đăng nhập thành công', [
                'user_id' => $authenticatedUser->id,
                'role'    => $matchedRole,
            ]);

            return [
                'token_type'            => 'Bearer',
                'access_token'          => $accessToken,
                'role'                  => $matchedRole,
                'user'                  => $authenticatedUser,
                'assigned_building_ids' => $assignedBuildingIds,
            ];
        } catch (\Throwable $e) {
            Log::error('Lỗi phát sinh trong quá trình tạo phiên đăng nhập: ' . $e->getMessage(), [
                'user_id' => $authenticatedUser->id,
                'trace'   => $e->getTraceAsString(),
            ]);

            throw ValidationException::withMessages([
                'email' => ['Không thể khởi tạo phiên làm việc do lỗi hệ thống. Vui lòng thử lại sau.'],
            ]);
        }
    }

    /**
     * Đăng xuất tài khoản: Thu hồi token hiện tại.
     *
     * @param  mixed  $user  Người dùng đang xác thực
     * @return void
     */
    public function logout($user): void
    {
        try {
            if ($user && method_exists($user, 'currentAccessToken') && $user->currentAccessToken()) {
                $user->currentAccessToken()->delete();
                Log::info('Đăng xuất thành công, đã thu hồi token', [
                    'user_id' => $user->id,
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('Lỗi khi thu hồi token đăng xuất: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Lấy thông tin hồ sơ chi tiết và eager load các tòa nhà được phân quyền.
     *
     * @param  mixed  $user  Người dùng đang xác thực
     * @return array
     */
    public function getProfile($user): array
    {
        $role = $this->resolveRoleFromUser($user);
        $assignedBuildings = [];

        // Eager load quan hệ assigned buildings theo đặc tả Module 5
        if ($user instanceof Sale) {
            $user->load(['buildings' => function ($query) {
                $query->select('buildings.id', 'buildings.building_code', 'buildings.name', 'buildings.grade');
            }]);
            $assignedBuildings = $user->buildings;
        } elseif ($user instanceof PropertyManager) {
            $user->load(['buildings' => function ($query) {
                $query->select('buildings.id', 'buildings.building_code', 'buildings.name', 'buildings.grade');
            }]);
            $assignedBuildings = $user->buildings;
        } elseif ($user instanceof Accountant) {
            $user->load(['buildings' => function ($query) {
                $query->select('buildings.id', 'buildings.building_code', 'buildings.name', 'buildings.grade');
            }]);
            $assignedBuildings = $user->buildings;
        } elseif ($user instanceof Admin) {
            // Super Admin quan sát toàn bộ tòa nhà
            $assignedBuildings = Building::select('id', 'building_code', 'name', 'grade')->get();
        }

        return [
            'role'               => $role,
            'user'               => $user,
            'assigned_buildings' => $assignedBuildings,
        ];
    }

    /**
     * Làm mới phiên làm việc (Refresh Token): Hủy token hiện tại và cấp token mới.
     *
     * @param  mixed  $user  Người dùng đang xác thực
     * @return array{token_type: string, access_token: string}
     */
    public function refreshToken($user): array
    {
        $role = $this->resolveRoleFromUser($user);

        // Hủy token hiện tại
        if (method_exists($user, 'currentAccessToken') && $user->currentAccessToken()) {
            $user->currentAccessToken()->delete();
        }

        // Cấp phát token mới
        $newToken = $user->createToken('auth-token', [$role])->plainTextToken;

        Log::info('Làm mới phiên làm việc thành công', [
            'user_id' => $user->id,
            'role'    => $role,
        ]);

        return [
            'token_type'   => 'Bearer',
            'access_token' => $newToken,
        ];
    }

    /**
     * Đổi mật khẩu tài khoản cá nhân.
     *
     * @param  mixed  $user  Người dùng đang xác thực
     * @param  string  $currentPassword  Mật khẩu hiện tại
     * @param  string  $newPassword  Mật khẩu mới
     * @return void
     * 
     * @throws ValidationException
     */
    public function changePassword($user, string $currentPassword, string $newPassword): void
    {
        // 1. Kiểm tra tính chính xác của mật khẩu hiện tại
        if (! Hash::check($currentPassword, $user->password)) {
            Log::warning('Đổi mật khẩu thất bại: Mật khẩu hiện tại không đúng', [
                'user_id' => $user->id,
            ]);

            throw ValidationException::withMessages([
                'current_password' => ['Mật khẩu hiện tại không chính xác.'],
            ]);
        }

        // 2. Không cho phép đặt mật khẩu mới trùng mật khẩu hiện tại
        if (Hash::check($newPassword, $user->password)) {
            throw ValidationException::withMessages([
                'password' => ['Mật khẩu mới không được trùng với mật khẩu hiện tại.'],
            ]);
        }

        // 3. Cập nhật mật khẩu mới mã hóa bcrypt
        $user->password = Hash::make($newPassword);
        $user->save();

        Log::info('Đổi mật khẩu thành công', [
            'user_id' => $user->id,
        ]);
    }

    /**
     * Trích xuất danh sách ID các tòa nhà được phân quyền theo vai trò.
     *
     * @param  mixed  $user
     * @param  string  $role
     * @return array<string>
     */
    protected function resolveAssignedBuildingIds($user, string $role): array
    {
        if ($user instanceof Sale) {
            return $user->buildings()->pluck('buildings.id')->toArray();
        }

        if ($user instanceof PropertyManager) {
            return $user->buildings()->pluck('buildings.id')->toArray();
        }

        if ($user instanceof Accountant) {
            return $user->buildings()->pluck('buildings.id')->toArray();
        }

        if ($user instanceof Admin) {
            return Building::pluck('id')->toArray();
        }

        return [];
    }

    /**
     * Xác định tên vai trò chuẩn hóa từ đối tượng Model.
     *
     * @param  mixed  $user
     * @return string
     */
    public function resolveRoleFromUser($user): string
    {
        return match (true) {
            $user instanceof Admin           => UserRole::ADMIN->value,
            $user instanceof Sale            => UserRole::SALES->value,
            $user instanceof PropertyManager => UserRole::PROPERTY_MANAGER->value,
            $user instanceof Accountant      => UserRole::ACCOUNTANT->value,
            $user instanceof Tenant          => UserRole::TENANT->value,
            default                          => 'user',
        };
    }
}
