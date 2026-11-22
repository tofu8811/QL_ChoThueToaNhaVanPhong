<?php

namespace Tests\Feature;

use App\Models\Accountant;
use App\Models\Admin;
use App\Models\PropertyManager;
use App\Models\Sale;
use App\Models\Tenant;
use Database\Seeders\ProvinceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature Test Suite: Kiểm tra xác thực & phân quyền RBAC cho phân hệ Thiết Lập Email.
 * Ràng buộc:
 * - Chỉ duy nhất Admin hệ thống mới có quyền xem, sửa, bật/tắt, test và reset cấu hình email.
 * - Sale, Property Manager (PM), Accountant, Tenant và Guest tuyệt đối KHÔNG có quyền truy cập (bị chặn 401 / 403).
 */
class MailSettingSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected Admin $admin;
    protected string $adminToken;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ProvinceSeeder::class);

        $this->admin = Admin::create([
            'employee_code' => 'ADM-SEC-01',
            'full_name' => 'System Admin Security',
            'email' => 'admin.sec@office.vn',
            'phone' => '0911223344',
            'password' => 'secret123',
            'role' => 'ADMIN',
            'status' => 'ACTIVE',
            'is_super_admin' => false,
        ]);
        $this->adminToken = $this->admin->createToken('admin_sec_token', ['admin'])->plainTextToken;
    }

    protected function tearDown(): void
    {
        $testFile = storage_path('app/mail_settings_testing.json');
        if (file_exists($testFile)) {
            @unlink($testFile);
        }
        parent::tearDown();
    }

    /**
     * 1. Guest (Chưa đăng nhập) bị chặn 401 trên toàn bộ các route cấu hình email.
     */
    public function test_guest_is_blocked_with_401_on_all_mail_settings_routes(): void
    {
        $this->getJson('/api/v1/admin/mail-settings')->assertStatus(401);
        $this->putJson('/api/v1/admin/mail-settings', [])->assertStatus(401);
        $this->postJson('/api/v1/admin/mail-settings/toggle', ['is_enabled' => true])->assertStatus(401);
        $this->postJson('/api/v1/admin/mail-settings/test', ['recipient_email' => 'test@test.vn'])->assertStatus(401);
        $this->postJson('/api/v1/admin/mail-settings/reset')->assertStatus(401);
    }

    /**
     * 2. Property Manager (PM) có token bị chặn 403 Forbidden trên toàn bộ các route cấu hình email.
     */
    public function test_property_manager_is_blocked_with_403_on_mail_settings_routes(): void
    {
        $pm = PropertyManager::create([
            'employee_code' => 'PM-SEC-01',
            'full_name' => 'PM Security',
            'email' => 'pm.sec@office.vn',
            'phone' => '0922334455',
            'password' => 'secret123',
            'status' => 'ACTIVE',
        ]);
        $pmToken = $pm->createToken('pm_token', ['pm'])->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$pmToken}")
            ->getJson('/api/v1/admin/mail-settings')->assertStatus(403);

        $this->withHeader('Authorization', "Bearer {$pmToken}")
            ->putJson('/api/v1/admin/mail-settings', [
                'mail_username' => 'hacker@gmail.com',
                'mail_password' => 'hackpass123',
                'mail_from_name' => 'Hacker Name',
                'mail_from_address' => 'hacker@gmail.com',
            ])->assertStatus(403);

        $this->withHeader('Authorization', "Bearer {$pmToken}")
            ->postJson('/api/v1/admin/mail-settings/toggle', ['is_enabled' => false])->assertStatus(403);

        $this->withHeader('Authorization', "Bearer {$pmToken}")
            ->postJson('/api/v1/admin/mail-settings/test', ['recipient_email' => 'pm@office.vn'])->assertStatus(403);

        $this->withHeader('Authorization', "Bearer {$pmToken}")
            ->postJson('/api/v1/admin/mail-settings/reset')->assertStatus(403);
    }

    /**
     * 3. Chuyên viên Sales có token bị chặn 403 Forbidden trên toàn bộ các route cấu hình email.
     */
    public function test_sales_user_is_blocked_with_403_on_mail_settings_routes(): void
    {
        $sale = Sale::create([
            'employee_code' => 'SALE-SEC-01',
            'full_name' => 'Sales Security',
            'email' => 'sale.sec@office.vn',
            'phone' => '0933445566',
            'password' => 'secret123',
            'status' => 'ACTIVE',
        ]);
        $saleToken = $sale->createToken('sale_token', ['sales'])->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$saleToken}")
            ->getJson('/api/v1/admin/mail-settings')->assertStatus(403);

        $this->withHeader('Authorization', "Bearer {$saleToken}")
            ->putJson('/api/v1/admin/mail-settings', [])->assertStatus(403);

        $this->withHeader('Authorization', "Bearer {$saleToken}")
            ->postJson('/api/v1/admin/mail-settings/toggle', ['is_enabled' => false])->assertStatus(403);

        $this->withHeader('Authorization', "Bearer {$saleToken}")
            ->postJson('/api/v1/admin/mail-settings/test', ['recipient_email' => 'sale@office.vn'])->assertStatus(403);

        $this->withHeader('Authorization', "Bearer {$saleToken}")
            ->postJson('/api/v1/admin/mail-settings/reset')->assertStatus(403);
    }

    /**
     * 4. Kế toán (Accountant) có token bị chặn 403 Forbidden trên toàn bộ các route cấu hình email.
     */
    public function test_accountant_user_is_blocked_with_403_on_mail_settings_routes(): void
    {
        $accountant = Accountant::create([
            'employee_code' => 'ACC-SEC-01',
            'full_name' => 'Accountant Security',
            'email' => 'acc.sec@office.vn',
            'phone' => '0944556677',
            'password' => 'secret123',
            'status' => 'ACTIVE',
        ]);
        $accToken = $accountant->createToken('acc_token', ['accountant'])->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$accToken}")
            ->getJson('/api/v1/admin/mail-settings')->assertStatus(403);

        $this->withHeader('Authorization', "Bearer {$accToken}")
            ->putJson('/api/v1/admin/mail-settings', [])->assertStatus(403);

        $this->withHeader('Authorization', "Bearer {$accToken}")
            ->postJson('/api/v1/admin/mail-settings/toggle', ['is_enabled' => false])->assertStatus(403);

        $this->withHeader('Authorization', "Bearer {$accToken}")
            ->postJson('/api/v1/admin/mail-settings/test', ['recipient_email' => 'acc@office.vn'])->assertStatus(403);

        $this->withHeader('Authorization', "Bearer {$accToken}")
            ->postJson('/api/v1/admin/mail-settings/reset')->assertStatus(403);
    }

    /**
     * 5. Khách thuê (Tenant) có token bị chặn 403 Forbidden trên toàn bộ các route cấu hình email.
     */
    public function test_tenant_user_is_blocked_with_403_on_mail_settings_routes(): void
    {
        $tenant = Tenant::create([
            'tenant_code' => 'TNT-SEC-01',
            'company_name' => 'Công Ty Khách Thuê Security',
            'tax_code' => '0312345678',
            'business_license_private_path' => 'licenses/tnt-sec-01.pdf',
            'representative_name' => 'Đại Diện Khách Thuê',
            'representative_position' => 'Giám Đốc',
            'headquarter_address' => '123 Nguyễn Huệ, Q1',
            'login_email' => 'tenant.sec@company.vn',
            'billing_email' => 'billing.sec@company.vn',
            'contact_phone' => '0912345678',
            'password' => 'secret123',
            'status' => 'ACTIVE',
        ]);
        $tenantToken = $tenant->createToken('tenant_token', ['tenant'])->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$tenantToken}")
            ->getJson('/api/v1/admin/mail-settings')->assertStatus(403);

        $this->withHeader('Authorization', "Bearer {$tenantToken}")
            ->putJson('/api/v1/admin/mail-settings', [])->assertStatus(403);

        $this->withHeader('Authorization', "Bearer {$tenantToken}")
            ->postJson('/api/v1/admin/mail-settings/toggle', ['is_enabled' => false])->assertStatus(403);

        $this->withHeader('Authorization', "Bearer {$tenantToken}")
            ->postJson('/api/v1/admin/mail-settings/test', ['recipient_email' => 'tenant@company.vn'])->assertStatus(403);

        $this->withHeader('Authorization', "Bearer {$tenantToken}")
            ->postJson('/api/v1/admin/mail-settings/reset')->assertStatus(403);
    }

    /**
     * 6. Admin hệ thống có quyền truy cập đầy đủ các route cấu hình email (200 OK).
     */
    public function test_system_admin_has_full_access_to_mail_settings_routes(): void
    {
        // GET cấu hình
        $response = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->getJson('/api/v1/admin/mail-settings');
        $response->assertStatus(200);

        // PUT cập nhật cấu hình
        $putResponse = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->putJson('/api/v1/admin/mail-settings', [
                'mail_mailer' => 'smtp',
                'mail_host' => 'smtp.gmail.com',
                'mail_port' => 587,
                'mail_encryption' => 'tls',
                'mail_username' => 'admin.test@gmail.com',
                'mail_password' => 'apppass123456789',
                'mail_from_name' => 'Ban Quản Lý Tòa Nhà',
                'mail_from_address' => 'admin.test@gmail.com',
            ]);
        $putResponse->assertStatus(200)
            ->assertJsonPath('data.is_configured', true);

        // POST toggle bật/tắt
        $toggleResponse = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->postJson('/api/v1/admin/mail-settings/toggle', [
                'is_enabled' => false,
            ]);
        $toggleResponse->assertStatus(200)
            ->assertJsonPath('data.is_enabled', false);

        // Khôi phục bật lại
        $toggleOnResponse = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->postJson('/api/v1/admin/mail-settings/toggle', [
                'is_enabled' => true,
            ]);
        $toggleOnResponse->assertStatus(200)
            ->assertJsonPath('data.is_enabled', true);
    }
}
