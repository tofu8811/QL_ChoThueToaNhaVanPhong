<?php

namespace Tests\Feature;

use App\Models\Accountant;
use App\Models\Admin;
use App\Models\Building;
use App\Models\PropertyManager;
use App\Models\Sale;
use App\Models\Tenant;
use Database\Seeders\BuildingAndUnitSeeder;
use Database\Seeders\OperationalBusinessSeeder;
use Database\Seeders\ProvinceSeeder;
use Database\Seeders\StaffAndAccountSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Class BuildingAssignmentApiTest
 *
 * Kiểm thử tự động Phân công Tòa nhà & Phân quyền RBAC (Building Assignments)
 * Module 5: Endpoints 92 - 98.
 */
class BuildingAssignmentApiTest extends TestCase
{
    use RefreshDatabase;

    protected Admin $admin;
    protected Sale $sale;
    protected PropertyManager $pm;
    protected Accountant $accountant;
    protected Tenant $tenant;
    protected Building $building;

    protected string $adminToken;
    protected string $tenantToken;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(StaffAndAccountSeeder::class);
        $this->seed(ProvinceSeeder::class);
        $this->seed(BuildingAndUnitSeeder::class);
        $this->seed(OperationalBusinessSeeder::class);

        $this->admin = Admin::where('email', 'admin@officeleasing.vn')->firstOrFail();
        $this->sale = Sale::firstOrFail();
        $this->pm = PropertyManager::firstOrFail();
        $this->accountant = Accountant::firstOrFail();
        $this->tenant = Tenant::firstOrFail();
        $this->building = Building::firstOrFail();

        $this->adminToken = $this->admin->createToken('admin_test_token')->plainTextToken;
        $this->tenantToken = $this->tenant->createToken('tenant_test_token')->plainTextToken;
    }

    /**
     * 92. GET: Xem danh sách toàn bộ đội ngũ nhân sự phụ trách tòa nhà (gom nhóm sales, managers, accountants).
     */
    public function test_admin_can_view_building_assignments_grouped(): void
    {
        $response = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->getJson("/api/v1/admin/buildings/{$this->building->id}/assignments");

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonStructure([
                'status',
                'message',
                'code',
                'data' => [
                    'building' => ['id', 'building_code', 'name'],
                    'sales',
                    'managers',
                    'accountants',
                ],
            ]);
    }

    /**
     * 92. GET: Chặn người dùng không phải Quản trị viên (Admin) xem danh sách phân công.
     */
    public function test_non_admin_cannot_view_building_assignments(): void
    {
        $response = $this->withHeader('Authorization', "Bearer {$this->tenantToken}")
            ->getJson("/api/v1/admin/buildings/{$this->building->id}/assignments");

        $response->assertStatus(403)
            ->assertJsonPath('status', 'error');
    }

    /**
     * 93. POST: Gán Sales vào tòa nhà & kiểm tra cơ chế chống lỗi trùng lặp (Duplicate check).
     */
    public function test_admin_can_assign_sales_and_handle_duplicate(): void
    {
        $payload = [
            'sales_ids' => [$this->sale->id],
            'is_active' => true,
        ];

        // Lần gán thứ 1
        $response1 = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->postJson("/api/v1/admin/buildings/{$this->building->id}/assignments/sales", $payload);

        $response1->assertStatus(201)
            ->assertJsonPath('status', 'success');

        $this->assertDatabaseHas('sale_buildings', [
            'building_id' => $this->building->id,
            'sales_id' => $this->sale->id,
            'is_active' => 1,
        ]);

        // Lần gán thứ 2 (duplicate) -> cập nhật không bị lỗi Exception SQL
        $response2 = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->postJson("/api/v1/admin/buildings/{$this->building->id}/assignments/sales", $payload);

        $response2->assertStatus(201)
            ->assertJsonPath('status', 'success');
    }

    /**
     * 94. DELETE: Gỡ bỏ phân công Sales khỏi tòa nhà.
     */
    public function test_admin_can_remove_sales_from_building(): void
    {
        // Gán trước
        $this->building->sales()->syncWithoutDetaching([
            $this->sale->id => [
                'is_active' => true,
                'assigned_by_admin_id' => $this->admin->id,
                'assigned_at' => now(),
            ],
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->deleteJson("/api/v1/admin/buildings/{$this->building->id}/assignments/sales/{$this->sale->id}");

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success');

        $this->assertDatabaseMissing('sale_buildings', [
            'building_id' => $this->building->id,
            'sales_id' => $this->sale->id,
        ]);
    }

    /**
     * 95. POST: Gán Ban Quản Lý (PM) vào tòa nhà kèm cờ is_head_manager.
     */
    public function test_admin_can_assign_property_manager_with_head_manager_flag(): void
    {
        $payload = [
            'pm_id' => $this->pm->id,
            'is_head_manager' => true,
        ];

        $response = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->postJson("/api/v1/admin/buildings/{$this->building->id}/assignments/property-managers", $payload);

        $response->assertStatus(201)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.is_primary', true);

        $this->assertDatabaseHas('manager_buildings', [
            'building_id' => $this->building->id,
            'pm_id' => $this->pm->id,
            'is_primary' => 1,
        ]);
    }

    /**
     * 96. DELETE: Gỡ bỏ phân công Ban Quản Lý khỏi tòa nhà.
     */
    public function test_admin_can_remove_property_manager_from_building(): void
    {
        $this->building->propertyManagers()->syncWithoutDetaching([
            $this->pm->id => [
                'is_primary' => true,
                'assigned_by_admin_id' => $this->admin->id,
                'assigned_at' => now(),
            ],
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->deleteJson("/api/v1/admin/buildings/{$this->building->id}/assignments/property-managers/{$this->pm->id}");

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success');

        $this->assertDatabaseMissing('manager_buildings', [
            'building_id' => $this->building->id,
            'pm_id' => $this->pm->id,
        ]);
    }

    /**
     * 97. POST: Gán Kế toán phụ trách công nợ tòa nhà.
     */
    public function test_admin_can_assign_accountants_to_building(): void
    {
        $payload = [
            'accountant_ids' => [$this->accountant->id],
        ];

        $response = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->postJson("/api/v1/admin/buildings/{$this->building->id}/assignments/accountants", $payload);

        $response->assertStatus(201)
            ->assertJsonPath('status', 'success');

        $this->assertDatabaseHas('account_buildings', [
            'building_id' => $this->building->id,
            'accountant_id' => $this->accountant->id,
        ]);
    }

    /**
     * 98. DELETE: Gỡ bỏ phân công Kế toán khỏi tòa nhà.
     */
    public function test_admin_can_remove_accountant_from_building(): void
    {
        $this->building->accountants()->syncWithoutDetaching([
            $this->accountant->id => [
                'assigned_by_admin_id' => $this->admin->id,
                'assigned_at' => now(),
            ],
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->deleteJson("/api/v1/admin/buildings/{$this->building->id}/assignments/accountants/{$this->accountant->id}");

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success');

        $this->assertDatabaseMissing('account_buildings', [
            'building_id' => $this->building->id,
            'accountant_id' => $this->accountant->id,
        ]);
    }

    /**
     * Kiểm tra bắt lỗi validation khi truyền ID không tồn tại.
     */
    public function test_validation_fails_for_invalid_foreign_keys(): void
    {
        $fakeUuid = '018f3a5e-0000-7000-8000-000000000000';

        // Gán sales với ID không tồn tại
        $resSales = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->postJson("/api/v1/admin/buildings/{$this->building->id}/assignments/sales", [
                'sales_ids' => [$fakeUuid],
            ]);
        $resSales->assertStatus(422);

        // Gán PM với ID không tồn tại
        $resPm = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->postJson("/api/v1/admin/buildings/{$this->building->id}/assignments/property-managers", [
                'pm_id' => $fakeUuid,
            ]);
        $resPm->assertStatus(422);

        // Gán Accountant với ID không tồn tại
        $resAcc = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->postJson("/api/v1/admin/buildings/{$this->building->id}/assignments/accountants", [
                'accountant_ids' => [$fakeUuid],
            ]);
        $resAcc->assertStatus(422);
    }

    /**
     * RBAC Scope: Nhân viên Sales và Ban Quản Lý chỉ được xem các tòa nhà được phân công phụ trách.
     */
    public function test_building_list_is_scoped_strictly_by_role_assignments(): void
    {
        // 1. Sales chỉ xem các tòa nhà mà mình được phân công trong sale_buildings
        $saleToken = $this->sale->createToken('sale_token')->plainTextToken;
        $expectedSaleBuildingIds = \Illuminate\Support\Facades\DB::table('sale_buildings')
            ->where('sales_id', $this->sale->id)
            ->where('is_active', true)
            ->pluck('building_id')
            ->toArray();

        $resSale = $this->withHeader('Authorization', "Bearer {$saleToken}")
            ->getJson('/api/v1/admin/buildings?per_page=100');

        $resSale->assertStatus(200);
        $saleBuildings = $resSale->json('data.data') ?? $resSale->json('data') ?? [];
        $returnedSaleBuildingIds = array_column($saleBuildings, 'id');

        $this->assertNotEmpty($returnedSaleBuildingIds);
        $this->assertEquals(count($expectedSaleBuildingIds), count($returnedSaleBuildingIds));
        foreach ($returnedSaleBuildingIds as $bId) {
            $this->assertContains($bId, $expectedSaleBuildingIds);
        }

        // 2. Ban Quản Lý (PM) chỉ xem các tòa nhà mà mình được phân công trong manager_buildings
        app('auth')->forgetGuards();
        $pmToken = $this->pm->createToken('pm_token')->plainTextToken;
        $expectedPmBuildingIds = \Illuminate\Support\Facades\DB::table('manager_buildings')
            ->where('pm_id', $this->pm->id)
            ->pluck('building_id')
            ->toArray();

        $resPm = $this->withHeader('Authorization', "Bearer {$pmToken}")
            ->getJson('/api/v1/admin/buildings?per_page=100');

        $resPm->assertStatus(200);
        $pmBuildings = $resPm->json('data.data') ?? $resPm->json('data') ?? [];
        $returnedPmBuildingIds = array_column($pmBuildings, 'id');

        $this->assertNotEmpty($returnedPmBuildingIds);
        $this->assertEquals(count($expectedPmBuildingIds), count($returnedPmBuildingIds));
        foreach ($returnedPmBuildingIds as $bId) {
            $this->assertContains($bId, $expectedPmBuildingIds);
        }

        // 3. Admin được xem toàn bộ tòa nhà trong hệ thống
        app('auth')->forgetGuards();
        $totalSystemBuildings = Building::count();
        $resAdmin = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->getJson('/api/v1/admin/buildings?per_page=100');

        $resAdmin->assertStatus(200);
        $adminBuildings = $resAdmin->json('data.data') ?? $resAdmin->json('data') ?? [];
        $this->assertEquals($totalSystemBuildings, count($adminBuildings));

        // 4. Sales không có quyền gán nhân sự (403 Forbidden)
        app('auth')->forgetGuards();
        $resForbidden = $this->withHeader('Authorization', "Bearer {$saleToken}")
            ->postJson("/api/v1/admin/buildings/{$this->building->id}/assignments/sales", [
                'sales_ids' => [$this->sale->id],
            ]);
        $resForbidden->assertStatus(403);
    }
}
