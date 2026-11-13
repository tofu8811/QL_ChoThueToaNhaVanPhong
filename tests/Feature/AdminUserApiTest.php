<?php

namespace Tests\Feature;

use App\Models\Admin;
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
 * Class AdminUserApiTest
 *
 * Kiểm thử tự động Quản trị Nhân sự & Phân quyền: Quản trị viên (Admins)
 * Endpoints 72 - 76.
 */
class AdminUserApiTest extends TestCase
{
    use RefreshDatabase;

    protected Admin $superAdmin;
    protected Admin $normalAdmin;
    protected PropertyManager $pm;
    protected Tenant $tenant;

    protected string $superAdminToken;
    protected string $normalAdminToken;
    protected string $pmToken;
    protected string $tenantToken;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(StaffAndAccountSeeder::class);
        $this->seed(ProvinceSeeder::class);
        $this->seed(BuildingAndUnitSeeder::class);
        $this->seed(OperationalBusinessSeeder::class);

        $this->superAdmin = Admin::where('email', 'admin@officeleasing.vn')->firstOrFail();
        $this->normalAdmin = Admin::where('email', 'op.admin@officeleasing.vn')->firstOrFail();
        $this->pm = PropertyManager::where('email', 'pm.quang@officeleasing.vn')->firstOrFail();
        $this->tenant = Tenant::where('login_email', 'contact@vng.com.vn')->firstOrFail();

        $this->superAdminToken = $this->superAdmin->createToken('super-admin')->plainTextToken;
        $this->normalAdminToken = $this->normalAdmin->createToken('normal-admin')->plainTextToken;
        $this->pmToken = $this->pm->createToken('pm')->plainTextToken;
        $this->tenantToken = $this->tenant->createToken('tenant')->plainTextToken;
    }

    /**
     * 72. GET /api/v1/admin/users/admins: Super Admin lấy danh sách admin có tìm kiếm và phân trang.
     */
    public function test_super_admin_can_get_admin_list(): void
    {
        $response = $this->withHeader('Authorization', "Bearer {$this->superAdminToken}")
            ->getJson('/api/v1/admin/users/admins?per_page=10&keyword=officeleasing');

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
    }

    /**
     * 72b. Kiểm thử chặn truy cập đối với tài khoản không phải Super Admin (403).
     */
    public function test_non_super_admin_cannot_access_admin_list(): void
    {
        // Normal Admin (is_super_admin = false)
        $this->withHeader('Authorization', "Bearer {$this->normalAdminToken}")
            ->getJson('/api/v1/admin/users/admins')
            ->assertStatus(403);

        // PM
        $this->withHeader('Authorization', "Bearer {$this->pmToken}")
            ->getJson('/api/v1/admin/users/admins')
            ->assertStatus(403);

        // Tenant
        $this->withHeader('Authorization', "Bearer {$this->tenantToken}")
            ->getJson('/api/v1/admin/users/admins')
            ->assertStatus(403);
    }

    /**
     * 73. POST /api/v1/admin/users/admins: Tạo mới tài khoản Admin.
     */
    public function test_super_admin_can_create_admin(): void
    {
        $payload = [
            'full_name'      => 'Trần Quản Trị Viên Mới',
            'email'          => 'admin.new@officeleasing.vn',
            'phone'          => '0933445566',
            'password'       => 'SecureAdmin@123',
            'is_super_admin' => false,
            'status'         => 'ACTIVE',
        ];

        $response = $this->withHeader('Authorization', "Bearer {$this->superAdminToken}")
            ->postJson('/api/v1/admin/users/admins', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.full_name', $payload['full_name'])
            ->assertJsonPath('data.email', $payload['email'])
            ->assertJsonPath('data.phone', $payload['phone'])
            ->assertJsonPath('data.is_super_admin', false)
            ->assertJsonPath('data.status', 'ACTIVE')
            ->assertJsonMissing(['data' => ['password' => $payload['password']]]);

        $this->assertDatabaseHas('admins', [
            'email'     => $payload['email'],
            'full_name' => $payload['full_name'],
            'phone'     => $payload['phone'],
        ]);

        $createdAdmin = Admin::where('email', $payload['email'])->firstOrFail();
        $this->assertTrue(Hash::check('SecureAdmin@123', $createdAdmin->password));
    }

    /**
     * 73b. Kiểm thử validation khi tạo Admin: email trùng hoặc mật khẩu quá ngắn.
     */
    public function test_create_admin_validation_errors(): void
    {
        // Email trùng
        $response = $this->withHeader('Authorization', "Bearer {$this->superAdminToken}")
            ->postJson('/api/v1/admin/users/admins', [
                'full_name' => 'Test Name',
                'email'     => 'admin@officeleasing.vn', // Trùng email đã có
                'password'  => 'Admin@123456',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);

        // Mật khẩu yếu / ngắn
        $weakPassResponse = $this->withHeader('Authorization', "Bearer {$this->superAdminToken}")
            ->postJson('/api/v1/admin/users/admins', [
                'full_name' => 'Test Name',
                'email'     => 'admin.weak@officeleasing.vn',
                'password'  => '123',
            ]);

        $weakPassResponse->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    /**
     * 74. GET /api/v1/admin/users/admins/{id}: Xem chi tiết tài khoản Admin theo UUID.
     */
    public function test_super_admin_can_get_admin_detail(): void
    {
        $response = $this->withHeader('Authorization', "Bearer {$this->superAdminToken}")
            ->getJson("/api/v1/admin/users/admins/{$this->normalAdmin->id}");

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.id', $this->normalAdmin->id)
            ->assertJsonPath('data.email', $this->normalAdmin->email)
            ->assertJsonMissing(['data' => ['password' => '']]);
    }

    /**
     * 75. PUT /api/v1/admin/users/admins/{id}: Cập nhật tài khoản Admin.
     */
    public function test_super_admin_can_update_admin(): void
    {
        $payload = [
            'full_name'      => 'Trần Vận Hành Đã Đổi Tên',
            'phone'          => '0988990011',
            'is_super_admin' => true,
            'status'         => 'LOCKED',
        ];

        $response = $this->withHeader('Authorization', "Bearer {$this->superAdminToken}")
            ->putJson("/api/v1/admin/users/admins/{$this->normalAdmin->id}", $payload);

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.full_name', 'Trần Vận Hành Đã Đổi Tên')
            ->assertJsonPath('data.phone', '0988990011')
            ->assertJsonPath('data.is_super_admin', true)
            ->assertJsonPath('data.status', 'LOCKED');

        $this->assertDatabaseHas('admins', [
            'id'             => $this->normalAdmin->id,
            'full_name'      => 'Trần Vận Hành Đã Đổi Tên',
            'phone'          => '0988990011',
            'is_super_admin' => 1,
            'status'         => 'LOCKED',
        ]);
    }

    /**
     * 76. DELETE /api/v1/admin/users/admins/{id}: Xóa mềm tài khoản Admin khác.
     */
    public function test_super_admin_can_soft_delete_other_admin(): void
    {
        $targetAdmin = Admin::create([
            'full_name' => 'Admin Cần Xóa',
            'email'     => 'admin.to.delete@officeleasing.vn',
            'password'  => Hash::make('Admin@123456'),
            'status'    => 'ACTIVE',
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$this->superAdminToken}")
            ->deleteJson("/api/v1/admin/users/admins/{$targetAdmin->id}");

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success');

        $this->assertSoftDeleted('admins', ['id' => $targetAdmin->id]);
    }

    /**
     * 76b. DELETE /api/v1/admin/users/admins/{id}: Cấm Super Admin tự xóa tài khoản của chính mình (422).
     */
    public function test_super_admin_cannot_delete_self(): void
    {
        $response = $this->withHeader('Authorization', "Bearer {$this->superAdminToken}")
            ->deleteJson("/api/v1/admin/users/admins/{$this->superAdmin->id}");

        $response->assertStatus(422)
            ->assertJsonPath('status', 'error')
            ->assertJsonPath('message', 'Không được phép tự xóa tài khoản quản trị viên của chính mình.');

        $this->assertNotSoftDeleted('admins', ['id' => $this->superAdmin->id]);
    }
}
