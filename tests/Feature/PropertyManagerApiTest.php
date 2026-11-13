<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Building;
use App\Models\PropertyManager;
use App\Models\Tenant;
use Database\Seeders\BuildingAndUnitSeeder;
use Database\Seeders\OperationalBusinessSeeder;
use Database\Seeders\ProvinceSeeder;
use Database\Seeders\StaffAndAccountSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Class PropertyManagerApiTest
 *
 * Kiểm thử tự động Quản trị Nhân sự & Phân quyền: Ban Quản lý Tòa nhà (PM)
 * Endpoints 82 - 86.
 */
class PropertyManagerApiTest extends TestCase
{
    use RefreshDatabase;

    protected Admin $admin;
    protected PropertyManager $pm1;
    protected PropertyManager $pm2;
    protected Tenant $tenant;
    protected Building $building;

    protected string $adminToken;
    protected string $pm1Token;
    protected string $pm2Token;
    protected string $tenantToken;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(StaffAndAccountSeeder::class);
        $this->seed(ProvinceSeeder::class);
        $this->seed(BuildingAndUnitSeeder::class);
        $this->seed(OperationalBusinessSeeder::class);

        $this->admin = Admin::where('email', 'admin@officeleasing.vn')->firstOrFail();
        $this->pm1 = PropertyManager::where('email', 'pm.quang@officeleasing.vn')->firstOrFail();
        $this->pm2 = PropertyManager::where('email', 'pm.minh@officeleasing.vn')->firstOrFail();
        $this->tenant = Tenant::where('login_email', 'contact@vng.com.vn')->firstOrFail();
        $this->building = Building::firstOrFail();

        $this->adminToken = $this->admin->createToken('admin')->plainTextToken;
        $this->pm1Token = $this->pm1->createToken('pm1')->plainTextToken;
        $this->pm2Token = $this->pm2->createToken('pm2')->plainTextToken;
        $this->tenantToken = $this->tenant->createToken('tenant')->plainTextToken;
    }

    /**
     * 82. GET /api/v1/admin/users/property-managers: Danh sách Ban Quản lý (PM).
     */
    public function test_admin_can_get_property_manager_list(): void
    {
        $response = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->getJson('/api/v1/admin/users/property-managers?per_page=10&keyword=quang');

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
        $this->assertArrayHasKey('buildings', $items[0]);
    }

    /**
     * 82b. Chặn quyền truy cập nếu không phải Admin (403).
     */
    public function test_non_admin_cannot_get_property_manager_list(): void
    {
        $this->withHeader('Authorization', "Bearer {$this->tenantToken}")
            ->getJson('/api/v1/admin/users/property-managers')
            ->assertStatus(403);
    }

    /**
     * 83. POST /api/v1/admin/users/property-managers: Tạo mới Ban Quản lý (PM).
     */
    public function test_admin_can_create_property_manager(): void
    {
        $payload = [
            'full_name'              => 'Nguyễn Kỹ Thuật Trưởng',
            'email'                  => 'pm.truong@officeleasing.vn',
            'phone'                  => '0988776655',
            'emergency_hotline'      => '19008888',
            'password'               => 'PmPass@123',
            'department'             => 'TECHNICAL',
            'status'                 => 'ACTIVE',
            'building_ids'           => [$this->building->id],
            'is_primary_building_id' => $this->building->id,
        ];

        $response = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->postJson('/api/v1/admin/users/property-managers', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.full_name', $payload['full_name'])
            ->assertJsonPath('data.email', $payload['email'])
            ->assertJsonPath('data.department', 'TECHNICAL')
            ->assertJsonPath('data.status', 'ACTIVE')
            ->assertJsonMissing(['data' => ['password' => $payload['password']]]);

        $empCode = $response->json('data.employee_code');
        $this->assertNotEmpty($empCode);
        $this->assertStringStartsWith('NV-BQL-', $empCode);

        $this->assertDatabaseHas('property_managers', [
            'email'      => $payload['email'],
            'full_name'  => $payload['full_name'],
            'department' => 'TECHNICAL',
        ]);

        $this->assertDatabaseHas('manager_buildings', [
            'pm_id'       => $response->json('data.id'),
            'building_id' => $this->building->id,
            'is_primary'  => 1,
        ]);
    }

    /**
     * 83b. Validation khi tạo PM (email trùng hoặc mật khẩu quá ngắn).
     */
    public function test_create_property_manager_validation(): void
    {
        $response = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->postJson('/api/v1/admin/users/property-managers', [
                'full_name' => 'Tên Test',
                'email'     => 'pm.quang@officeleasing.vn', // Trùng email
                'password'  => '123',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'password']);
    }

    /**
     * 84a. GET /api/v1/admin/users/property-managers/{id}: Admin xem chi tiết PM.
     */
    public function test_admin_can_view_property_manager_detail(): void
    {
        $response = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->getJson("/api/v1/admin/users/property-managers/{$this->pm1->id}");

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.id', $this->pm1->id)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'employee_code',
                    'full_name',
                    'email',
                    'department',
                    'buildings',
                    'statistics' => [
                        'assigned_tickets',
                        'resolved_tickets',
                        'pending_tickets',
                    ]
                ]
            ]);
    }

    /**
     * 84b. GET /api/v1/admin/users/property-managers/{id}: PM xem hồ sơ của chính mình thành công (200).
     */
    public function test_pm_can_view_own_profile(): void
    {
        $response = $this->withHeader('Authorization', "Bearer {$this->pm1Token}")
            ->getJson("/api/v1/admin/users/property-managers/{$this->pm1->id}");

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.id', $this->pm1->id);
    }

    /**
     * 84c. GET /api/v1/admin/users/property-managers/{id}: PM xem hồ sơ của PM khác bị từ chối (403).
     */
    public function test_pm_cannot_view_other_pm_profile(): void
    {
        $response = $this->withHeader('Authorization', "Bearer {$this->pm1Token}")
            ->getJson("/api/v1/admin/users/property-managers/{$this->pm2->id}");

        $response->assertStatus(403);
    }

    /**
     * 85. PUT /api/v1/admin/users/property-managers/{id}: Cập nhật Ban Quản lý (PM).
     */
    public function test_admin_can_update_property_manager(): void
    {
        $payload = [
            'full_name'         => 'Quang Đã Đổi Tên',
            'department'        => 'CUSTOMER_CARE',
            'status'            => 'LOCKED',
            'emergency_hotline' => '0999888777',
            'building_ids'      => [$this->building->id],
        ];

        $response = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->putJson("/api/v1/admin/users/property-managers/{$this->pm1->id}", $payload);

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.full_name', 'Quang Đã Đổi Tên')
            ->assertJsonPath('data.department', 'CUSTOMER_CARE')
            ->assertJsonPath('data.status', 'LOCKED');

        $this->assertDatabaseHas('property_managers', [
            'id'         => $this->pm1->id,
            'full_name'  => 'Quang Đã Đổi Tên',
            'department' => 'CUSTOMER_CARE',
            'status'     => 'LOCKED',
        ]);
    }

    /**
     * 86. DELETE /api/v1/admin/users/property-managers/{id}: Xóa mềm Ban Quản lý (PM).
     */
    public function test_admin_can_soft_delete_property_manager(): void
    {
        $response = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->deleteJson("/api/v1/admin/users/property-managers/{$this->pm2->id}");

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success');

        $this->assertSoftDeleted('property_managers', ['id' => $this->pm2->id]);
    }
}
