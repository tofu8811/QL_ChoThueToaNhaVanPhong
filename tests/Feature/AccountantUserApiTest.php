<?php

namespace Tests\Feature;

use App\Models\Accountant;
use App\Models\Admin;
use App\Models\Building;
use App\Models\Tenant;
use Database\Seeders\BuildingAndUnitSeeder;
use Database\Seeders\OperationalBusinessSeeder;
use Database\Seeders\ProvinceSeeder;
use Database\Seeders\StaffAndAccountSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Class AccountantUserApiTest
 *
 * Kiểm thử tự động Quản trị Nhân sự & Phân quyền: Kế toán (Accountants)
 * Endpoints 87 - 91.
 */
class AccountantUserApiTest extends TestCase
{
    use RefreshDatabase;

    protected Admin $admin;
    protected Accountant $acc1;
    protected Accountant $acc2;
    protected Tenant $tenant;
    protected Building $building;

    protected string $adminToken;
    protected string $acc1Token;
    protected string $acc2Token;
    protected string $tenantToken;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(StaffAndAccountSeeder::class);
        $this->seed(ProvinceSeeder::class);
        $this->seed(BuildingAndUnitSeeder::class);
        $this->seed(OperationalBusinessSeeder::class);

        $this->admin = Admin::where('email', 'admin@officeleasing.vn')->firstOrFail();
        $this->acc1 = Accountant::where('email', 'acc.thu@officeleasing.vn')->firstOrFail();
        $this->acc2 = Accountant::where('email', 'acc.hang@officeleasing.vn')->firstOrFail();
        $this->tenant = Tenant::where('login_email', 'contact@vng.com.vn')->firstOrFail();
        $this->building = Building::firstOrFail();

        $this->adminToken = $this->admin->createToken('admin')->plainTextToken;
        $this->acc1Token = $this->acc1->createToken('acc1')->plainTextToken;
        $this->acc2Token = $this->acc2->createToken('acc2')->plainTextToken;
        $this->tenantToken = $this->tenant->createToken('tenant')->plainTextToken;
    }

    /**
     * 87. GET /api/v1/admin/users/accountants: Danh sách Nhân viên Kế toán.
     */
    public function test_admin_can_get_accountant_list(): void
    {
        $response = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->getJson('/api/v1/admin/users/accountants?per_page=10&keyword=thu');

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
        $this->assertArrayHasKey('can_approve_adjustment', $items[0]);
    }

    /**
     * 87b. Chặn quyền truy cập nếu không phải Admin (403).
     */
    public function test_non_admin_cannot_get_accountant_list(): void
    {
        $this->withHeader('Authorization', "Bearer {$this->tenantToken}")
            ->getJson('/api/v1/admin/users/accountants')
            ->assertStatus(403);
    }

    /**
     * 88. POST /api/v1/admin/users/accountants: Tạo mới Kế toán.
     */
    public function test_admin_can_create_accountant(): void
    {
        $payload = [
            'full_name'              => 'Phạm Thị Kế Toán Tổng Hợp',
            'email'                  => 'acc.tonghop@officeleasing.vn',
            'phone'                  => '0944556677',
            'password'               => 'AccPass@123',
            'can_approve_adjustment' => true,
            'status'                 => 'ACTIVE',
            'building_ids'           => [$this->building->id],
        ];

        $response = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->postJson('/api/v1/admin/users/accountants', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.full_name', $payload['full_name'])
            ->assertJsonPath('data.email', $payload['email'])
            ->assertJsonPath('data.can_approve_adjustment', true)
            ->assertJsonPath('data.status', 'ACTIVE')
            ->assertJsonMissing(['data' => ['password' => $payload['password']]]);

        $empCode = $response->json('data.employee_code');
        $this->assertNotEmpty($empCode);
        $this->assertStringStartsWith('NV-KT-', $empCode);

        $this->assertDatabaseHas('accountants', [
            'email'                  => $payload['email'],
            'full_name'              => $payload['full_name'],
            'can_approve_adjustment' => 1,
        ]);

        $this->assertDatabaseHas('account_buildings', [
            'accountant_id' => $response->json('data.id'),
            'building_id'   => $this->building->id,
        ]);
    }

    /**
     * 88b. Validation khi tạo Kế toán (email trùng hoặc mật khẩu quá ngắn).
     */
    public function test_create_accountant_validation(): void
    {
        $response = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->postJson('/api/v1/admin/users/accountants', [
                'full_name' => 'Tên Test',
                'email'     => 'acc.thu@officeleasing.vn', // Trùng email
                'password'  => '123',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'password']);
    }

    /**
     * 89a. GET /api/v1/admin/users/accountants/{id}: Admin xem chi tiết Kế toán.
     */
    public function test_admin_can_view_accountant_detail(): void
    {
        $response = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->getJson("/api/v1/admin/users/accountants/{$this->acc1->id}");

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.id', $this->acc1->id)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'employee_code',
                    'full_name',
                    'email',
                    'can_approve_adjustment',
                    'buildings',
                    'statistics' => [
                        'debit_notes_count',
                        'verified_payments_count',
                    ]
                ]
            ]);
    }

    /**
     * 89b. GET /api/v1/admin/users/accountants/{id}: Kế toán xem hồ sơ của chính mình thành công (200).
     */
    public function test_accountant_can_view_own_profile(): void
    {
        $response = $this->withHeader('Authorization', "Bearer {$this->acc1Token}")
            ->getJson("/api/v1/admin/users/accountants/{$this->acc1->id}");

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.id', $this->acc1->id);
    }

    /**
     * 89c. GET /api/v1/admin/users/accountants/{id}: Kế toán xem hồ sơ của Kế toán khác bị từ chối (403).
     */
    public function test_accountant_cannot_view_other_accountant_profile(): void
    {
        $response = $this->withHeader('Authorization', "Bearer {$this->acc1Token}")
            ->getJson("/api/v1/admin/users/accountants/{$this->acc2->id}");

        $response->assertStatus(403);
    }

    /**
     * 90. PUT /api/v1/admin/users/accountants/{id}: Cập nhật Kế toán.
     */
    public function test_admin_can_update_accountant(): void
    {
        $payload = [
            'full_name'              => 'Thu Đã Đổi Tên',
            'can_approve_adjustment' => true,
            'status'                 => 'LOCKED',
            'phone'                  => '0988112233',
            'building_ids'           => [$this->building->id],
        ];

        $response = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->putJson("/api/v1/admin/users/accountants/{$this->acc1->id}", $payload);

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.full_name', 'Thu Đã Đổi Tên')
            ->assertJsonPath('data.can_approve_adjustment', true)
            ->assertJsonPath('data.status', 'LOCKED');

        $this->assertDatabaseHas('accountants', [
            'id'                     => $this->acc1->id,
            'full_name'              => 'Thu Đã Đổi Tên',
            'can_approve_adjustment' => 1,
            'status'                 => 'LOCKED',
        ]);
    }

    /**
     * 91. DELETE /api/v1/admin/users/accountants/{id}: Xóa mềm Kế toán.
     */
    public function test_admin_can_soft_delete_accountant(): void
    {
        $response = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->deleteJson("/api/v1/admin/users/accountants/{$this->acc2->id}");

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success');

        $this->assertSoftDeleted('accountants', ['id' => $this->acc2->id]);
    }
}
