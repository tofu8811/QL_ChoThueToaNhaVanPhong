<?php

namespace App\Services;

use App\Models\Accountant;
use App\Models\Admin;
use App\Models\Building;
use App\Models\PropertyManager;
use App\Models\Sale;
use Illuminate\Support\Facades\DB;

/**
 * Service quản lý Phân công Tòa nhà & RBAC (Building Assignments).
 * Module 5: Xác Thực, Quản Trị Nhân Sự & Phân Quyền Tòa Nhà (Endpoints 92 - 98).
 */
class BuildingAssignmentService
{
    /**
     * 92. Lấy danh sách toàn bộ đội ngũ nhân sự phụ trách tòa nhà (Sales, BQL, Kế toán).
     *
     * @param string $buildingId
     * @return array<string, mixed>
     */
    public function getAssignments(string $buildingId): array
    {
        /** @var Building $building */
        $building = Building::with([
            'sales' => function ($query) {
                $query->select([
                    'sales.id',
                    'sales.employee_code',
                    'sales.full_name',
                    'sales.email',
                    'sales.phone',
                    'sales.max_concurrent_leads',
                    'sales.status',
                ]);
            },
            'propertyManagers' => function ($query) {
                $query->select([
                    'property_managers.id',
                    'property_managers.employee_code',
                    'property_managers.full_name',
                    'property_managers.email',
                    'property_managers.phone',
                    'property_managers.department',
                    'property_managers.status',
                ]);
            },
            'accountants' => function ($query) {
                $query->select([
                    'accountants.id',
                    'accountants.employee_code',
                    'accountants.full_name',
                    'accountants.email',
                    'accountants.phone',
                    'accountants.can_approve_adjustment',
                    'accountants.status',
                ]);
            },
        ])->findOrFail($buildingId);

        $sales = $building->sales->map(function (Sale $sale) {
            return [
                'id' => $sale->id,
                'employee_code' => $sale->employee_code,
                'full_name' => $sale->full_name,
                'email' => $sale->email,
                'phone' => $sale->phone,
                'max_concurrent_leads' => $sale->max_concurrent_leads,
                'status' => $sale->status,
                'is_active' => (bool) ($sale->pivot->is_active ?? true),
                'assigned_by_admin_id' => $sale->pivot->assigned_by_admin_id,
                'assigned_at' => $sale->pivot->assigned_at,
            ];
        });

        $managers = $building->propertyManagers->map(function (PropertyManager $pm) {
            $isPrimary = (bool) ($pm->pivot->is_primary ?? false);

            return [
                'id' => $pm->id,
                'employee_code' => $pm->employee_code,
                'full_name' => $pm->full_name,
                'email' => $pm->email,
                'phone' => $pm->phone,
                'department' => $pm->department,
                'status' => $pm->status,
                'is_primary' => $isPrimary,
                'is_head_manager' => $isPrimary,
                'assigned_by_admin_id' => $pm->pivot->assigned_by_admin_id,
                'assigned_at' => $pm->pivot->assigned_at,
            ];
        });

        $accountants = $building->accountants->map(function (Accountant $accountant) {
            return [
                'id' => $accountant->id,
                'employee_code' => $accountant->employee_code,
                'full_name' => $accountant->full_name,
                'email' => $accountant->email,
                'phone' => $accountant->phone,
                'can_approve_adjustment' => (bool) $accountant->can_approve_adjustment,
                'status' => $accountant->status,
                'assigned_by_admin_id' => $accountant->pivot->assigned_by_admin_id,
                'assigned_at' => $accountant->pivot->assigned_at,
            ];
        });

        return [
            'building' => [
                'id' => $building->id,
                'building_code' => $building->building_code,
                'name' => $building->name,
                'address' => $building->address,
            ],
            'sales' => $sales,
            'managers' => $managers,
            'accountants' => $accountants,
        ];
    }

    /**
     * 93. Gán một hoặc nhiều nhân viên Sales vào tòa nhà (Check duplicate).
     *
     * @param string $buildingId
     * @param array<int, string> $salesIds
     * @param bool|null $isActive
     * @param Admin $admin
     * @return array<string, mixed>
     */
    public function assignSales(string $buildingId, array $salesIds, ?bool $isActive, Admin $admin): array
    {
        $building = Building::findOrFail($buildingId);
        $assignedCount = 0;
        $updatedCount = 0;

        DB::transaction(function () use ($buildingId, $salesIds, $isActive, $admin, &$assignedCount, &$updatedCount) {
            $now = now();
            $activeStatus = $isActive ?? true;

            foreach ($salesIds as $saleId) {
                $existing = DB::table('sale_buildings')
                    ->where('building_id', $buildingId)
                    ->where('sales_id', $saleId)
                    ->first();

                if ($existing) {
                    DB::table('sale_buildings')
                        ->where('id', $existing->id)
                        ->update([
                            'is_active' => $activeStatus,
                            'assigned_by_admin_id' => $admin->id,
                            'assigned_at' => $now,
                        ]);
                    $updatedCount++;
                } else {
                    DB::table('sale_buildings')->insert([
                        'building_id' => $buildingId,
                        'sales_id' => $saleId,
                        'is_active' => $activeStatus,
                        'assigned_by_admin_id' => $admin->id,
                        'assigned_at' => $now,
                    ]);
                    $assignedCount++;
                }
            }
        });

        return [
            'building_id' => $building->id,
            'assigned_count' => $assignedCount,
            'updated_count' => $updatedCount,
            'total_sales' => count($salesIds),
        ];
    }

    /**
     * 94. Gỡ Sales khỏi tòa nhà.
     *
     * @param string $buildingId
     * @param string $salesId
     * @return bool
     */
    public function removeSales(string $buildingId, string $salesId): bool
    {
        $building = Building::findOrFail($buildingId);

        $deleted = DB::table('sale_buildings')
            ->where('building_id', $building->id)
            ->where('sales_id', $salesId)
            ->delete();

        return $deleted > 0;
    }

    /**
     * 95. Gán Ban quản lý (PM) vào tòa nhà (kèm cờ is_head_manager / is_primary).
     *
     * @param string $buildingId
     * @param string $pmId
     * @param bool $isPrimary
     * @param Admin $admin
     * @return array<string, mixed>
     */
    public function assignPropertyManager(string $buildingId, string $pmId, bool $isPrimary, Admin $admin): array
    {
        $building = Building::findOrFail($buildingId);
        $pm = PropertyManager::findOrFail($pmId);

        DB::transaction(function () use ($buildingId, $pmId, $isPrimary, $admin) {
            $now = now();

            // Nếu đặt làm Trưởng ban quản lý chính (is_primary = true), đưa các PM khác của tòa về false
            if ($isPrimary) {
                DB::table('manager_buildings')
                    ->where('building_id', $buildingId)
                    ->where('pm_id', '!=', $pmId)
                    ->update(['is_primary' => false]);
            }

            $existing = DB::table('manager_buildings')
                ->where('building_id', $buildingId)
                ->where('pm_id', $pmId)
                ->first();

            if ($existing) {
                DB::table('manager_buildings')
                    ->where('id', $existing->id)
                    ->update([
                        'is_primary' => $isPrimary,
                        'assigned_by_admin_id' => $admin->id,
                        'assigned_at' => $now,
                    ]);
            } else {
                DB::table('manager_buildings')->insert([
                    'building_id' => $buildingId,
                    'pm_id' => $pmId,
                    'is_primary' => $isPrimary,
                    'assigned_by_admin_id' => $admin->id,
                    'assigned_at' => $now,
                ]);
            }
        });

        return [
            'building_id' => $building->id,
            'pm_id' => $pm->id,
            'full_name' => $pm->full_name,
            'is_primary' => $isPrimary,
            'is_head_manager' => $isPrimary,
        ];
    }

    /**
     * 96. Gỡ Ban Quản Lý (PM) khỏi tòa nhà.
     *
     * @param string $buildingId
     * @param string $pmId
     * @return bool
     */
    public function removePropertyManager(string $buildingId, string $pmId): bool
    {
        $building = Building::findOrFail($buildingId);

        $deleted = DB::table('manager_buildings')
            ->where('building_id', $building->id)
            ->where('pm_id', $pmId)
            ->delete();

        return $deleted > 0;
    }

    /**
     * 97. Gán Kế toán phụ trách công nợ cho tòa nhà.
     *
     * @param string $buildingId
     * @param array<int, string> $accountantIds
     * @param Admin $admin
     * @return array<string, mixed>
     */
    public function assignAccountant(string $buildingId, array $accountantIds, Admin $admin): array
    {
        $building = Building::findOrFail($buildingId);
        $assignedCount = 0;
        $updatedCount = 0;

        DB::transaction(function () use ($buildingId, $accountantIds, $admin, &$assignedCount, &$updatedCount) {
            $now = now();

            foreach ($accountantIds as $accountantId) {
                $existing = DB::table('account_buildings')
                    ->where('building_id', $buildingId)
                    ->where('accountant_id', $accountantId)
                    ->first();

                if ($existing) {
                    DB::table('account_buildings')
                        ->where('id', $existing->id)
                        ->update([
                            'assigned_by_admin_id' => $admin->id,
                            'assigned_at' => $now,
                        ]);
                    $updatedCount++;
                } else {
                    DB::table('account_buildings')->insert([
                        'building_id' => $buildingId,
                        'accountant_id' => $accountantId,
                        'assigned_by_admin_id' => $admin->id,
                        'assigned_at' => $now,
                    ]);
                    $assignedCount++;
                }
            }
        });

        return [
            'building_id' => $building->id,
            'assigned_count' => $assignedCount,
            'updated_count' => $updatedCount,
            'total_accountants' => count($accountantIds),
        ];
    }

    /**
     * 98. Gỡ Kế toán khỏi tòa nhà.
     *
     * @param string $buildingId
     * @param string $accountantId
     * @return bool
     */
    public function removeAccountant(string $buildingId, string $accountantId): bool
    {
        $building = Building::findOrFail($buildingId);

        $deleted = DB::table('account_buildings')
            ->where('building_id', $building->id)
            ->where('accountant_id', $accountantId)
            ->delete();

        return $deleted > 0;
    }
}
