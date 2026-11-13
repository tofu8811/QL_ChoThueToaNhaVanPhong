<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Building;
use App\Models\Lead;
use App\Models\Sale;
use App\Models\Tenant;
use Database\Seeders\BuildingAndUnitSeeder;
use Database\Seeders\OperationalBusinessSeeder;
use Database\Seeders\ProvinceSeeder;
use Database\Seeders\StaffAndAccountSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Class SaleUserApiTest
 *
 * Kiểm thử tự động Quản trị Nhân sự & Phân quyền: Nhân viên Sales
 * Endpoints 77 - 81.
 */
class SaleUserApiTest extends TestCase
{
    use RefreshDatabase;

    protected Admin $admin;
    protected Sale $sales1;
    protected Sale $sales2;
    protected Tenant $tenant;
    protected Building $building;

    protected string $adminToken;
    protected string $sales1Token;
    protected string $sales2Token;
    protected string $tenantToken;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(StaffAndAccountSeeder::class);
        $this->seed(ProvinceSeeder::class);
        $this->seed(BuildingAndUnitSeeder::class);
        $this->seed(OperationalBusinessSeeder::class);

        $this->admin = Admin::where('email', 'admin@officeleasing.vn')->firstOrFail();
        $this->sales1 = Sale::where('email', 'sales.hoang@officeleasing.vn')->firstOrFail();
        $this->sales2 = Sale::where('email', 'sales.linh@officeleasing.vn')->firstOrFail();
        $this->tenant = Tenant::where('login_email', 'contact@vng.com.vn')->firstOrFail();
        $this->building = Building::firstOrFail();

        $this->adminToken = $this->admin->createToken('admin')->plainTextToken;
        $this->sales1Token = $this->sales1->createToken('sales1')->plainTextToken;
        $this->sales2Token = $this->sales2->createToken('sales2')->plainTextToken;
        $this->tenantToken = $this->tenant->createToken('tenant')->plainTextToken;
    }

    /**
     * 77. GET /api/v1/admin/users/sales: Danh sách Nhân viên Sales.
     */
    public function test_admin_can_get_sales_list_with_active_leads_count_and_buildings(): void
    {
        $response = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->getJson('/api/v1/admin/users/sales?per_page=10&keyword=hoang');

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonStructure([
                'status',
                'message',
                'code',
                'data',
                'meta' => [
                    'current_page',
                    'last_page',
                    'per_page',
                    'total',
                ]
            ]);

        $items = $response->json('data');
        $this->assertNotEmpty($items);
        $this->assertArrayHasKey('active_leads_count', $items[0]);
        $this->assertArrayHasKey('buildings', $items[0]);
    }

    /**
     * 77b. Kiểm thử chặn quyền khi người dùng không phải Admin xem danh sách Sales (403).
     */
    public function test_non_admin_cannot_get_sales_list(): void
    {
        $this->withHeader('Authorization', "Bearer {$this->tenantToken}")
            ->getJson('/api/v1/admin/users/sales')
            ->assertStatus(403);
    }

    /**
     * 78. POST /api/v1/admin/users/sales: Tạo mới Nhân viên Sales kèm phân công tòa nhà.
     */
    public function test_admin_can_create_sale_with_assigned_buildings(): void
    {
        $payload = [
            'full_name'            => 'Lê Thị Thu Môi Giới',
            'email'                => 'sales.thu@officeleasing.vn',
            'phone'                => '0911223344',
            'password'             => 'SalesPass@123',
            'commission_rate'      => 2.0,
            'max_concurrent_leads' => 15,
            'status'               => 'ACTIVE',
            'building_ids'         => [$this->building->id],
        ];

        $response = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->postJson('/api/v1/admin/users/sales', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.full_name', $payload['full_name'])
            ->assertJsonPath('data.email', $payload['email'])
            ->assertJsonPath('data.max_concurrent_leads', 15)
            ->assertJsonPath('data.status', 'ACTIVE')
            ->assertJsonMissing(['data' => ['password' => $payload['password']]]);

        $empCode = $response->json('data.employee_code');
        $this->assertNotEmpty($empCode);
        $this->assertStringStartsWith('NV-SALE-', $empCode);

        $this->assertDatabaseHas('sales', [
            'email'     => $payload['email'],
            'full_name' => $payload['full_name'],
        ]);

        $this->assertDatabaseHas('sale_buildings', [
            'sales_id'    => $response->json('data.id'),
            'building_id' => $this->building->id,
            'is_active'   => 1,
        ]);
    }

    /**
     * 78b. Kiểm thử validation khi tạo Sales (trùng email hoặc mật khẩu quá ngắn).
     */
    public function test_create_sale_validation(): void
    {
        $response = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->postJson('/api/v1/admin/users/sales', [
                'full_name' => 'Tên Test',
                'email'     => 'sales.hoang@officeleasing.vn', // Trùng email
                'password'  => '123', // Mật khẩu quá ngắn
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'password']);
    }

    /**
     * 79a. GET /api/v1/admin/users/sales/{id}: Admin xem chi tiết Sales kèm thống kê conversion rate.
     */
    public function test_admin_can_view_sale_detail_with_conversion_rate(): void
    {
        $response = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->getJson("/api/v1/admin/users/sales/{$this->sales1->id}");

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.id', $this->sales1->id)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'employee_code',
                    'full_name',
                    'email',
                    'buildings',
                    'statistics' => [
                        'total_leads',
                        'won_leads',
                        'lost_leads',
                        'active_leads',
                        'conversion_rate',
                    ]
                ]
            ]);
    }

    /**
     * 79b. GET /api/v1/admin/users/sales/{id}: Sales xem hồ sơ cá nhân của chính mình thành công (200).
     */
    public function test_sales_can_view_own_profile(): void
    {
        $response = $this->withHeader('Authorization', "Bearer {$this->sales1Token}")
            ->getJson("/api/v1/admin/users/sales/{$this->sales1->id}");

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.id', $this->sales1->id);
    }

    /**
     * 79c. GET /api/v1/admin/users/sales/{id}: Sales xem hồ sơ của Sales khác bị từ chối (403).
     */
    public function test_sales_cannot_view_other_sales_profile(): void
    {
        $response = $this->withHeader('Authorization', "Bearer {$this->sales1Token}")
            ->getJson("/api/v1/admin/users/sales/{$this->sales2->id}");

        $response->assertStatus(403);
    }

    /**
     * 80. PUT /api/v1/admin/users/sales/{id}: Cập nhật thông tin Sales.
     */
    public function test_admin_can_update_sale_and_buildings(): void
    {
        $payload = [
            'full_name'            => 'Hoàng Đã Cập Nhật',
            'max_concurrent_leads' => 20,
            'status'               => 'LOCKED',
            'building_ids'         => [$this->building->id],
        ];

        $response = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->putJson("/api/v1/admin/users/sales/{$this->sales1->id}", $payload);

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.full_name', 'Hoàng Đã Cập Nhật')
            ->assertJsonPath('data.max_concurrent_leads', 20)
            ->assertJsonPath('data.status', 'LOCKED');

        $this->assertDatabaseHas('sales', [
            'id'                   => $this->sales1->id,
            'full_name'            => 'Hoàng Đã Cập Nhật',
            'max_concurrent_leads' => 20,
            'status'               => 'LOCKED',
        ]);
    }

    /**
     * 81a. DELETE /api/v1/admin/users/sales/{id}: Cảnh báo 422 khi Sales còn Lead đang xử lý mà chưa re-assign.
     */
    public function test_delete_sale_without_reassigning_active_leads_fails(): void
    {
        // Gán 1 lead active cho sales1
        Lead::first()->update([
            'assigned_sales_id' => $this->sales1->id,
            'status'            => 'NEW',
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->deleteJson("/api/v1/admin/users/sales/{$this->sales1->id}");

        $response->assertStatus(422)
            ->assertJsonPath('status', 'error');

        $this->assertNotSoftDeleted('sales', ['id' => $this->sales1->id]);
    }

    /**
     * 81b. DELETE /api/v1/admin/users/sales/{id}: Xóa mềm thành công khi re-assign Lead cho Sales khác.
     */
    public function test_delete_sale_with_reassigning_active_leads_succeeds(): void
    {
        $lead = Lead::first();
        $lead->update([
            'assigned_sales_id' => $this->sales1->id,
            'status'            => 'NEW',
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->deleteJson("/api/v1/admin/users/sales/{$this->sales1->id}", [
                're_assign_to_sales_id' => $this->sales2->id,
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success');

        $this->assertSoftDeleted('sales', ['id' => $this->sales1->id]);

        // Kiểm tra Lead đã được chuyển giao cho sales2
        $this->assertDatabaseHas('leads', [
            'id'                => $lead->id,
            'assigned_sales_id' => $this->sales2->id,
        ]);
    }
}
