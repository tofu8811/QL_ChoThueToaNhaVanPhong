<?php

namespace Tests\Feature;

use Database\Seeders\BuildingAndUnitSeeder;
use Database\Seeders\OperationalBusinessSeeder;
use Database\Seeders\ProvinceSeeder;
use Database\Seeders\StaffAndAccountSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Class AuthApiTest
 * 
 * Kiểm thử tự động toàn diện 5 API Xác thực (Endpoints 67 - 71) thuộc Module 5
 * trên nền tảng cơ sở dữ liệu chuẩn hóa của nhánh develop.
 */
class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Nạp đầy đủ dữ liệu mẫu chuẩn theo cấu trúc của dự án
        $this->seed(StaffAndAccountSeeder::class);
        $this->seed(ProvinceSeeder::class);
        $this->seed(BuildingAndUnitSeeder::class);
        $this->seed(OperationalBusinessSeeder::class);
    }

    /**
     * 67. Kiểm thử Đăng nhập Đa Tác nhân cho toàn bộ 5 đối tượng (Admin, Sales, PM, Accountant, Tenant).
     */
    public function test_multi_guard_login_for_all_five_actors(): void
    {
        $actors = [
            ['email' => 'admin@officeleasing.vn', 'password' => 'Admin@123456', 'role' => 'admin'],
            ['email' => 'sales.hoang@officeleasing.vn', 'password' => 'Sales@123456', 'role' => 'sales'],
            ['email' => 'pm.quang@officeleasing.vn', 'password' => 'Pm@123456', 'role' => 'property_manager'],
            ['email' => 'acc.thu@officeleasing.vn', 'password' => 'Accountant@123456', 'role' => 'accountant'],
            ['email' => 'contact@vng.com.vn', 'password' => 'Tenant@123456', 'role' => 'tenant'],
        ];

        foreach ($actors as $actor) {
            $response = $this->postJson('/api/v1/auth/login', [
                'email'    => $actor['email'],
                'password' => $actor['password'],
            ]);

            $response->assertStatus(200)
                ->assertJsonPath('status', 'success')
                ->assertJsonPath('data.token_type', 'Bearer')
                ->assertJsonPath('data.role', $actor['role']);

            $this->assertNotEmpty($response->json('data.access_token'));
        }
    }

    /**
     * 67. Kiểm thử đăng nhập thất bại với mật khẩu sai.
     */
    public function test_login_fails_with_invalid_credentials(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email'    => 'admin@officeleasing.vn',
            'password' => 'WrongPassword@999',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /**
     * 69. Kiểm thử Xem Hồ sơ Cá nhân (Profile) và Eager Load Assigned Buildings.
     */
    public function test_get_profile_and_eager_load_assigned_buildings(): void
    {
        // 1. Đăng nhập Sales
        $loginRes = $this->postJson('/api/v1/auth/login', [
            'email'    => 'sales.hoang@officeleasing.vn',
            'password' => 'Sales@123456',
        ]);
        $token = $loginRes->json('data.access_token');

        // 2. Gọi /api/v1/auth/me với Bearer Token
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/auth/me');

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.role', 'sales')
            ->assertJsonPath('data.user.email', 'sales.hoang@officeleasing.vn');

        // Kiểm tra danh sách tòa nhà được eager load
        $buildings = $response->json('data.assigned_buildings');
        $this->assertIsArray($buildings);

        // 3. Kiểm tra gọi không truyền Token trả về 401 Unauthorized
        $this->flushHeaders();
        $this->app['auth']->forgetGuards();
        $unauthRes = $this->getJson('/api/v1/auth/me');
        $unauthRes->assertStatus(401);
    }

    /**
     * 70. Kiểm thử Làm mới Phiên (Refresh Token): Hủy token cũ và nhận token mới.
     */
    public function test_refresh_token_revokes_old_and_issues_new(): void
    {
        // 1. Đăng nhập lấy token ban đầu
        $loginRes = $this->postJson('/api/v1/auth/login', [
            'email'    => 'pm.quang@officeleasing.vn',
            'password' => 'Pm@123456',
        ]);
        $oldToken = $loginRes->json('data.access_token');

        // 2. Gọi API Refresh Token
        $refreshRes = $this->withHeader('Authorization', 'Bearer ' . $oldToken)
            ->postJson('/api/v1/auth/refresh');

        $refreshRes->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.token_type', 'Bearer');

        $newToken = $refreshRes->json('data.access_token');
        $this->assertNotEmpty($newToken);
        $this->assertNotEquals($oldToken, $newToken);

        // 3. Thử dùng token cũ gọi /me -> Phải bị từ chối 401
        $this->flushHeaders();
        $this->app['auth']->forgetGuards();
        $oldTokenCheck = $this->withHeader('Authorization', 'Bearer ' . $oldToken)
            ->getJson('/api/v1/auth/me');
        $oldTokenCheck->assertStatus(401);

        // 4. Dùng token mới gọi /me -> Thành công 200
        $this->flushHeaders();
        $this->app['auth']->forgetGuards();
        $newTokenCheck = $this->withHeader('Authorization', 'Bearer ' . $newToken)
            ->getJson('/api/v1/auth/me');
        $newTokenCheck->assertStatus(200);
    }

    /**
     * 71. Kiểm thử Đổi Mật khẩu Cá nhân và kiểm thực mật khẩu phức tạp.
     */
    public function test_change_password_validations_and_success(): void
    {
        $loginRes = $this->postJson('/api/v1/auth/login', [
            'email'    => 'acc.thu@officeleasing.vn',
            'password' => 'Accountant@123456',
        ]);
        $token = $loginRes->json('data.access_token');

        // A. Thất bại khi mật khẩu hiện tại sai
        $wrongCurrent = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson('/api/v1/auth/password', [
                'current_password'      => 'WrongPass@123',
                'password'              => 'NewPassword@2026',
                'password_confirmation' => 'NewPassword@2026',
            ]);
        $wrongCurrent->assertStatus(422)
            ->assertJsonValidationErrors(['current_password']);

        // B. Thất bại khi mật khẩu mới không đủ độ phức tạp
        $weakPass = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson('/api/v1/auth/password', [
                'current_password'      => 'Accountant@123456',
                'password'              => 'weakpass',
                'password_confirmation' => 'weakpass',
            ]);
        $weakPass->assertStatus(422)
            ->assertJsonValidationErrors(['password']);

        // C. Thành công khi nhập mật khẩu hợp lệ (tối thiểu 8 ký tự, có hoa, thường, số, ký tự đặc biệt)
        $successChange = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson('/api/v1/auth/password', [
                'current_password'      => 'Accountant@123456',
                'password'              => 'NewPassword@2026',
                'password_confirmation' => 'NewPassword@2026',
            ]);
        $successChange->assertStatus(200)
            ->assertJsonPath('status', 'success');

        // D. Đăng nhập lại bằng mật khẩu cũ phải thất bại
        $this->flushHeaders();
        $this->app['auth']->forgetGuards();
        $loginOld = $this->postJson('/api/v1/auth/login', [
            'email'    => 'acc.thu@officeleasing.vn',
            'password' => 'Accountant@123456',
        ]);
        $loginOld->assertStatus(422);

        // E. Đăng nhập bằng mật khẩu mới phải thành công
        $loginNew = $this->postJson('/api/v1/auth/login', [
            'email'    => 'acc.thu@officeleasing.vn',
            'password' => 'NewPassword@2026',
        ]);
        $loginNew->assertStatus(200);
    }

    /**
     * 68. Kiểm thử Đăng xuất: Thu hồi Token khỏi CSDL.
     */
    public function test_logout_revokes_token_immediately(): void
    {
        $loginRes = $this->postJson('/api/v1/auth/login', [
            'email'    => 'contact@vng.com.vn',
            'password' => 'Tenant@123456',
        ]);
        $token = $loginRes->json('data.access_token');

        // Gọi Đăng xuất
        $logoutRes = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/v1/auth/logout');

        $logoutRes->assertStatus(200)
            ->assertJsonPath('status', 'success');

        // Gọi lại /me với token đã thu hồi -> Phải trả về 401
        $this->flushHeaders();
        $this->app['auth']->forgetGuards();
        $checkRes = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/auth/me');
        $checkRes->assertStatus(401);
    }
}
