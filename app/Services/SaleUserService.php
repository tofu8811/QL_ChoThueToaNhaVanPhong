<?php

namespace App\Services;

use App\Exceptions\BusinessException;
use App\Models\Admin;
use App\Models\Lead;
use App\Models\Sale;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Service tầng nghiệp vụ quản lý tài khoản Nhân viên Sales.
 * Thuộc Module 5: Nhân sự & RBAC (Endpoints 77 - 81).
 */
class SaleUserService
{
    /**
     * Danh sách nhân viên kinh doanh / môi giới (Sales) có phân trang, tìm kiếm và thống kê Lead.
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getSaleList(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $query = Sale::query()
            ->with(['buildings:id,building_code,name,address'])
            ->withCount([
                'leads as active_leads_count' => fn ($q) => $q->whereNotIn('status', ['WON', 'LOST']),
                'leads as total_leads_count',
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

        // 3. Lọc theo tòa nhà phụ trách
        if (!empty($filters['building_id'])) {
            $query->whereHas('buildings', fn ($q) => $q->where('buildings.id', $filters['building_id']));
        }

        return $query->latest('created_at')->paginate($perPage);
    }

    /**
     * Tạo mới nhân viên Sales và gán danh sách tòa nhà phụ trách.
     *
     * @param array $data
     * @param Admin $admin
     * @return Sale
     */
    public function createSale(array $data, Admin $admin): Sale
    {
        return DB::transaction(function () use ($data, $admin) {
            // 1. Tự động sinh mã nhân viên nếu chưa có (VD: NV-SALE-001)
            $code = $data['employee_code'] ?? null;
            if (empty($code)) {
                $latest = Sale::where('employee_code', 'like', 'NV-SALE-%')
                    ->orderBy('employee_code', 'desc')
                    ->lockForUpdate()
                    ->value('employee_code');

                $seq = 1;
                if ($latest && preg_match('/-(\d+)$/', $latest, $m)) {
                    $seq = ((int) $m[1]) + 1;
                }
                $code = sprintf('NV-SALE-%03d', $seq);
            }

            // 2. Khởi tạo bản ghi Sales
            $sale = Sale::create([
                'employee_code'        => $code,
                'full_name'            => $data['full_name'],
                'email'                => $data['email'],
                'phone'                => $data['phone'] ?? null,
                'password'             => Hash::make($data['password']),
                'commission_rate'      => $data['commission_rate'] ?? 1.50,
                'max_concurrent_leads' => $data['max_concurrent_leads'] ?? 10,
                'status'               => $data['status'] ?? 'ACTIVE',
                'created_by_admin_id'  => $admin->id,
            ]);

            // 3. Phân công tòa nhà bán/cho thuê
            if (!empty($data['building_ids']) && is_array($data['building_ids'])) {
                $syncData = [];
                foreach ($data['building_ids'] as $buildingId) {
                    $syncData[$buildingId] = [
                        'is_active'            => true,
                        'assigned_by_admin_id' => $admin->id,
                        'assigned_at'          => now(),
                    ];
                }
                $sale->buildings()->sync($syncData);
            }

            return $sale->load(['buildings']);
        });
    }

    /**
     * Xem chi tiết hồ sơ nhân viên Sales và thống kê tỷ lệ chốt hợp đồng thành công.
     *
     * @param string $id
     * @param mixed $user
     * @return array
     * @throws BusinessException
     */
    public function getSaleDetail(string $id, mixed $user): array
    {
        // Phân quyền: Chỉ Admin hoặc chính nhân viên Sales đó mới được xem
        if ($user instanceof Sale && $user->id !== $id) {
            throw new BusinessException('Bạn chỉ có quyền xem thông tin hồ sơ của chính mình.', 403);
        }

        if (!($user instanceof Admin) && !($user instanceof Sale)) {
            throw new BusinessException('Không có quyền truy cập hồ sơ nhân viên Sales.', 403);
        }

        $sale = Sale::with(['buildings:id,building_code,name,address'])->findOrFail($id);

        $totalLeads = $sale->leads()->count();
        $wonLeads = $sale->leads()->where('status', 'WON')->count();
        $lostLeads = $sale->leads()->where('status', 'LOST')->count();
        $activeLeads = $sale->leads()->whereNotIn('status', ['WON', 'LOST'])->count();
        $conversionRate = $totalLeads > 0 ? round(($wonLeads / $totalLeads) * 100, 2) : 0.0;

        $response = $sale->toArray();
        $response['statistics'] = [
            'total_leads'     => $totalLeads,
            'won_leads'       => $wonLeads,
            'lost_leads'      => $lostLeads,
            'active_leads'    => $activeLeads,
            'conversion_rate' => $conversionRate,
        ];

        return $response;
    }

    /**
     * Cập nhật thông tin nhân viên Sales và danh sách tòa nhà phụ trách.
     *
     * @param string $id
     * @param array $data
     * @param Admin $admin
     * @return Sale
     */
    public function updateSale(string $id, array $data, Admin $admin): Sale
    {
        return DB::transaction(function () use ($id, $data, $admin) {
            $sale = Sale::findOrFail($id);

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
            if (array_key_exists('commission_rate', $data)) {
                $updateData['commission_rate'] = $data['commission_rate'];
            }
            if (array_key_exists('max_concurrent_leads', $data)) {
                $updateData['max_concurrent_leads'] = $data['max_concurrent_leads'];
            }
            if (array_key_exists('status', $data)) {
                $updateData['status'] = $data['status'];
            }

            $sale->update($updateData);

            if (array_key_exists('building_ids', $data) && is_array($data['building_ids'])) {
                $syncData = [];
                foreach ($data['building_ids'] as $buildingId) {
                    $syncData[$buildingId] = [
                        'is_active'            => true,
                        'assigned_by_admin_id' => $admin->id,
                        'assigned_at'          => now(),
                    ];
                }
                $sale->buildings()->sync($syncData);
            }

            return $sale->load(['buildings']);
        });
    }

    /**
     * Xóa mềm nhân viên Sales.
     * Kiểm tra và cảnh báo hoặc chuyển giao (re-assign) các Lead đang hoạt động trước khi xóa.
     *
     * @param string $id
     * @param string|null $reAssignToSalesId
     * @return bool
     * @throws BusinessException
     */
    public function deleteSale(string $id, ?string $reAssignToSalesId = null): bool
    {
        $sale = Sale::findOrFail($id);

        $activeLeadsCount = $sale->leads()->whereNotIn('status', ['WON', 'LOST'])->count();

        if ($activeLeadsCount > 0) {
            if (empty($reAssignToSalesId)) {
                throw new BusinessException("Nhân viên Sales này đang phụ trách {$activeLeadsCount} Lead chưa hoàn tất. Vui lòng chuyển giao (re-assign) Lead trước khi xóa.", 422);
            }

            $targetSale = Sale::where('status', 'ACTIVE')->findOrFail($reAssignToSalesId);

            $sale->leads()->whereNotIn('status', ['WON', 'LOST'])->update([
                'assigned_sales_id' => $targetSale->id,
            ]);
        }

        return (bool) $sale->delete();
    }
}
