<?php

namespace App\Services;

use App\Exceptions\BusinessException;
use App\Models\Accountant;
use App\Models\Admin;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Service tầng nghiệp vụ quản lý tài khoản Kế toán công nợ tòa nhà (Accountants).
 * Thuộc Module 5: Nhân sự & RBAC (Endpoints 87 - 91).
 */
class AccountantUserService
{
    /**
     * Danh sách nhân viên Kế toán có phân trang, tìm kiếm và eager load tòa nhà phụ trách.
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getAccountantList(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $query = Accountant::query()
            ->with(['buildings:id,building_code,name,address'])
            ->withCount([
                'debitNotes as debit_notes_count',
                'verifiedPayments as verified_payments_count',
            ]);

        // 1. Tìm kiếm theo từ khóa (họ tên, email, số điện thoại, mã nhân viên)
        $keyword = $filters['keyword'] ?? $filters['q'] ?? null;
        if (!empty($keyword)) {
            $term = '%' . trim($keyword) . '%';
            $query->where(function ($q) use ($term) {
                $q->where('full_name', 'like', $term)
                  ->orWhere('email', 'like', $term)
                  ->orWhere('phone', 'like', $term)
                  ->orWhere('employee_code', 'like', $term);
            });
        }

        // 2. Lọc theo trạng thái tài khoản (ACTIVE, LOCKED, PENDING)
        if (!empty($filters['status'])) {
            $query->where('status', strtoupper(trim($filters['status'])));
        }

        // 3. Lọc theo cờ quyền hạn can_approve_adjustment
        if (isset($filters['can_approve_adjustment'])) {
            $canApprove = filter_var($filters['can_approve_adjustment'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if (!is_null($canApprove)) {
                $query->where('can_approve_adjustment', $canApprove);
            }
        }

        // 4. Lọc theo tòa nhà phụ trách
        if (!empty($filters['building_id'])) {
            $query->whereHas('buildings', fn ($q) => $q->where('buildings.id', $filters['building_id']));
        }

        return $query->latest('created_at')->paginate($perPage);
    }

    /**
     * Tạo tài khoản Kế toán tòa nhà mới.
     *
     * @param array $data
     * @param Admin $admin
     * @return Accountant
     */
    public function createAccountant(array $data, Admin $admin): Accountant
    {
        return DB::transaction(function () use ($data, $admin) {
            // 1. Tự động sinh mã nhân viên nếu chưa có (VD: NV-KT-001)
            $code = $data['employee_code'] ?? null;
            if (empty($code)) {
                $latest = Accountant::where('employee_code', 'like', 'NV-KT-%')
                    ->orderBy('employee_code', 'desc')
                    ->lockForUpdate()
                    ->value('employee_code');

                $seq = 1;
                if ($latest && preg_match('/-(\d+)$/', $latest, $m)) {
                    $seq = ((int) $m[1]) + 1;
                }
                $code = sprintf('NV-KT-%03d', $seq);
            }

            // 2. Khởi tạo bản ghi Accountant
            $accountant = Accountant::create([
                'employee_code'          => $code,
                'full_name'              => $data['full_name'],
                'email'                  => $data['email'],
                'phone'                  => $data['phone'] ?? null,
                'password'               => Hash::make($data['password']),
                'can_approve_adjustment' => (bool) ($data['can_approve_adjustment'] ?? false),
                'status'                 => $data['status'] ?? 'ACTIVE',
                'created_by_admin_id'    => $admin->id,
            ]);

            // 3. Phân công tòa nhà theo dõi công nợ
            if (!empty($data['building_ids']) && is_array($data['building_ids'])) {
                $syncData = [];
                foreach ($data['building_ids'] as $buildingId) {
                    $syncData[$buildingId] = [
                        'assigned_by_admin_id' => $admin->id,
                        'assigned_at'          => now(),
                    ];
                }
                $accountant->buildings()->sync($syncData);
            }

            return $accountant->load(['buildings']);
        });
    }

    /**
     * Xem chi tiết hồ sơ Kế toán và các tòa nhà đang phụ trách theo dõi công nợ.
     *
     * @param string $id
     * @param mixed $user
     * @return array
     * @throws BusinessException
     */
    public function getAccountantDetail(string $id, mixed $user): array
    {
        // Phân quyền: Chỉ Admin hoặc chính Kế toán đó mới được xem
        if ($user instanceof Accountant && $user->id !== $id) {
            throw new BusinessException('Bạn chỉ có quyền xem thông tin hồ sơ của chính mình.', 403);
        }

        if (!($user instanceof Admin) && !($user instanceof Accountant)) {
            throw new BusinessException('Không có quyền truy cập hồ sơ Kế toán.', 403);
        }

        $accountant = Accountant::with(['buildings:id,building_code,name,address'])->findOrFail($id);

        $debitNotesCount = $accountant->debitNotes()->count();
        $verifiedPaymentsCount = $accountant->verifiedPayments()->count();

        $response = $accountant->toArray();
        $response['statistics'] = [
            'debit_notes_count'       => $debitNotesCount,
            'verified_payments_count' => $verifiedPaymentsCount,
        ];

        return $response;
    }

    /**
     * Cập nhật thông tin tài khoản Kế toán và danh sách tòa nhà phụ trách.
     *
     * @param string $id
     * @param array $data
     * @param Admin $admin
     * @return Accountant
     */
    public function updateAccountant(string $id, array $data, Admin $admin): Accountant
    {
        return DB::transaction(function () use ($id, $data, $admin) {
            $accountant = Accountant::findOrFail($id);

            $updateData = [];

            if (array_key_exists('full_name', $data)) {
                $updateData['full_name'] = $data['full_name'];
            }
            if (array_key_exists('email', $data)) {
                $updateData['email'] = $data['email'];
            }
            if (array_key_exists('phone', $data)) {
                $updateData['phone'] = $data['phone'];
            }
            if (!empty($data['password'])) {
                $updateData['password'] = Hash::make($data['password']);
            }
            if (array_key_exists('can_approve_adjustment', $data)) {
                $updateData['can_approve_adjustment'] = (bool) $data['can_approve_adjustment'];
            }
            if (array_key_exists('status', $data)) {
                $updateData['status'] = $data['status'];
            }

            $accountant->update($updateData);

            if (array_key_exists('building_ids', $data) && is_array($data['building_ids'])) {
                $syncData = [];
                foreach ($data['building_ids'] as $buildingId) {
                    $syncData[$buildingId] = [
                        'assigned_by_admin_id' => $admin->id,
                        'assigned_at'          => now(),
                    ];
                }
                $accountant->buildings()->sync($syncData);
            }

            return $accountant->load(['buildings']);
        });
    }

    /**
     * Xóa mềm tài khoản Kế toán.
     *
     * @param string $id
     * @return bool
     */
    public function deleteAccountant(string $id): bool
    {
        $accountant = Accountant::findOrFail($id);

        return (bool) $accountant->delete();
    }
}
