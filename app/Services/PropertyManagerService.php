<?php

namespace App\Services;

use App\Exceptions\BusinessException;
use App\Models\Admin;
use App\Models\PropertyManager;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Service tầng nghiệp vụ quản lý tài khoản Ban Quản lý & Kỹ thuật tòa nhà (PM).
 * Thuộc Module 5: Nhân sự & RBAC (Endpoints 82 - 86).
 */
class PropertyManagerService
{
    /**
     * Danh sách nhân viên Ban quản lý tòa nhà (PM) có phân trang, tìm kiếm và eager load tòa nhà.
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getPropertyManagerList(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $query = PropertyManager::query()
            ->with(['buildings:id,building_code,name,address'])
            ->withCount(['assignedTickets as assigned_tickets_count']);

        // 1. Tìm kiếm theo từ khóa (họ tên, email, số điện thoại/hotline, mã nhân viên)
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

        // 3. Lọc theo bộ phận chuyên trách (TECHNICAL, SECURITY, CLEANING, CUSTOMER_CARE)
        if (!empty($filters['department'])) {
            $query->where('department', strtoupper(trim($filters['department'])));
        }

        // 4. Lọc theo tòa nhà phụ trách
        if (!empty($filters['building_id'])) {
            $query->whereHas('buildings', fn ($q) => $q->where('buildings.id', $filters['building_id']));
        }

        return $query->latest('created_at')->paginate($perPage);
    }

    /**
     * Tạo tài khoản Ban Quản lý tòa nhà mới.
     *
     * @param array $data
     * @param Admin $admin
     * @return PropertyManager
     */
    public function createPropertyManager(array $data, Admin $admin): PropertyManager
    {
        return DB::transaction(function () use ($data, $admin) {
            // 1. Tự động sinh mã nhân viên nếu chưa có (VD: NV-BQL-001)
            $code = $data['employee_code'] ?? null;
            if (empty($code)) {
                $latest = PropertyManager::where('employee_code', 'like', 'NV-BQL-%')
                    ->orderBy('employee_code', 'desc')
                    ->lockForUpdate()
                    ->value('employee_code');

                $seq = 1;
                if ($latest && preg_match('/-(\d+)$/', $latest, $m)) {
                    $seq = ((int) $m[1]) + 1;
                }
                $code = sprintf('NV-BQL-%03d', $seq);
            }

            // 2. Khởi tạo bản ghi PropertyManager
            $pm = PropertyManager::create([
                'employee_code'       => $code,
                'full_name'           => $data['full_name'],
                'email'               => $data['email'],
                'phone'               => $data['phone'] ?? $data['emergency_hotline'] ?? null,
                'password'            => Hash::make($data['password']),
                'department'          => $data['department'] ?? 'TECHNICAL',
                'status'              => $data['status'] ?? 'ACTIVE',
                'created_by_admin_id' => $admin->id,
            ]);

            // 3. Phân công tòa nhà quản lý vận hành
            if (!empty($data['building_ids']) && is_array($data['building_ids'])) {
                $syncData = [];
                $primaryBuildingId = $data['is_primary_building_id'] ?? null;

                foreach ($data['building_ids'] as $buildingId) {
                    $syncData[$buildingId] = [
                        'is_primary'           => ($buildingId === $primaryBuildingId),
                        'assigned_by_admin_id' => $admin->id,
                        'assigned_at'          => now(),
                    ];
                }
                $pm->buildings()->sync($syncData);
            }

            return $pm->load(['buildings']);
        });
    }

    /**
     * Xem chi tiết hồ sơ Ban Quản lý tòa nhà và các tòa nhà được giao trách nhiệm.
     *
     * @param string $id
     * @param mixed $user
     * @return array
     * @throws BusinessException
     */
    public function getPropertyManagerDetail(string $id, mixed $user): array
    {
        // Phân quyền: Chỉ Admin hoặc chính nhân sự BQL đó mới được xem
        if ($user instanceof PropertyManager && $user->id !== $id) {
            throw new BusinessException('Bạn chỉ có quyền xem thông tin hồ sơ của chính mình.', 403);
        }

        if (!($user instanceof Admin) && !($user instanceof PropertyManager)) {
            throw new BusinessException('Không có quyền truy cập hồ sơ Ban Quản lý.', 403);
        }

        $pm = PropertyManager::with(['buildings:id,building_code,name,address'])->findOrFail($id);

        $assignedTickets = $pm->assignedTickets()->count();
        $resolvedTickets = $pm->assignedTickets()->whereIn('status', ['WAITING_CONFIRMATION', 'COMPLETED'])->count();
        $pendingTickets = $pm->assignedTickets()->whereNotIn('status', ['WAITING_CONFIRMATION', 'COMPLETED', 'CANCELLED'])->count();

        $response = $pm->toArray();
        $response['statistics'] = [
            'assigned_tickets' => $assignedTickets,
            'resolved_tickets' => $resolvedTickets,
            'pending_tickets'  => $pendingTickets,
        ];

        return $response;
    }

    /**
     * Cập nhật thông tin nhân viên BQL và danh sách tòa nhà phụ trách.
     *
     * @param string $id
     * @param array $data
     * @param Admin $admin
     * @return PropertyManager
     */
    public function updatePropertyManager(string $id, array $data, Admin $admin): PropertyManager
    {
        return DB::transaction(function () use ($id, $data, $admin) {
            $pm = PropertyManager::findOrFail($id);

            $updateData = [];

            if (array_key_exists('full_name', $data)) {
                $updateData['full_name'] = $data['full_name'];
            }
            if (array_key_exists('email', $data)) {
                $updateData['email'] = $data['email'];
            }
            if (array_key_exists('phone', $data)) {
                $updateData['phone'] = $data['phone'];
            } elseif (array_key_exists('emergency_hotline', $data)) {
                $updateData['phone'] = $data['emergency_hotline'];
            }
            if (!empty($data['password'])) {
                $updateData['password'] = Hash::make($data['password']);
            }
            if (array_key_exists('department', $data)) {
                $updateData['department'] = $data['department'];
            }
            if (array_key_exists('status', $data)) {
                $updateData['status'] = $data['status'];
            }

            $pm->update($updateData);

            if (array_key_exists('building_ids', $data) && is_array($data['building_ids'])) {
                $syncData = [];
                $primaryBuildingId = $data['is_primary_building_id'] ?? null;

                foreach ($data['building_ids'] as $buildingId) {
                    $syncData[$buildingId] = [
                        'is_primary'           => ($buildingId === $primaryBuildingId),
                        'assigned_by_admin_id' => $admin->id,
                        'assigned_at'          => now(),
                    ];
                }
                $pm->buildings()->sync($syncData);
            }

            return $pm->load(['buildings']);
        });
    }

    /**
     * Xóa mềm nhân viên Ban Quản lý tòa nhà.
     *
     * @param string $id
     * @return bool
     */
    public function deletePropertyManager(string $id): bool
    {
        $pm = PropertyManager::findOrFail($id);

        return (bool) $pm->delete();
    }
}
