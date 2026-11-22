<?php

namespace Tests\Feature;

use App\Models\Accountant;
use App\Models\Admin;
use App\Models\PropertyManager;
use App\Models\Sale;
use Database\Seeders\BuildingAndUnitSeeder;
use Database\Seeders\ProvinceSeeder;
use Database\Seeders\StaffAndAccountSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Kiểm thử tính cô lập và phân quyền giữa các phân hệ giao diện:
 * /admin, /pm, /sales, /accountant
 */
class RoleBasedPortalSeparationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(StaffAndAccountSeeder::class);
        $this->seed(ProvinceSeeder::class);
        $this->seed(BuildingAndUnitSeeder::class);
    }

    /**
     * 1. Khách chưa đăng nhập truy cập bất kỳ phân hệ nào đều bị chặn và chuyển về trang chủ.
     */
    public function test_unauthenticated_guests_are_redirected_to_login_prompt(): void
    {
        $portals = ['/admin/dashboard', '/pm/dashboard', '/sales/dashboard', '/accountant/dashboard'];

        foreach ($portals as $url) {
            $response = $this->get($url);
            $response->assertRedirect('/?auth=required');
        }
    }

    /**
     * 2. Super Admin có quyền truy cập vào tất cả các phân hệ để kiểm tra, không bị lỗi crash.
     */
    public function test_super_admin_can_access_all_portals_without_crashing(): void
    {
        $superAdmin = Admin::where('is_super_admin', true)->first();
        $this->assertNotNull($superAdmin);

        $token = $superAdmin->createToken('test-token')->plainTextToken;

        // Admin Portal
        $respAdmin = $this->withCookie('auth_token', $token)->get('/admin/dashboard');
        $respAdmin->assertStatus(200);
        $respAdmin->assertSee('Tổng quan vận hành toàn hệ thống');

        // PM Portal (Super Admin inspection)
        $respPm = $this->withCookie('auth_token', $token)->get('/pm/dashboard');
        $respPm->assertStatus(200);
        $respPm->assertSee('Ban Quản Lý');

        // Sales Portal (Super Admin inspection)
        $respSales = $this->withCookie('auth_token', $token)->get('/sales/dashboard');
        $respSales->assertStatus(200);
        $respSales->assertSee('Báo cáo hiệu suất kinh doanh cá nhân');

        // Accountant Portal (Super Admin inspection)
        $respAcc = $this->withCookie('auth_token', $token)->get('/accountant/dashboard');
        $respAcc->assertStatus(200);
        $respAcc->assertSee('Kế Toán');
    }

    /**
     * 3. Chuyên viên Sales đăng nhập vào phân hệ Sales thành công, bị chặn khi vào Admin/Staff.
     */
    public function test_sales_user_can_access_sales_portal_but_blocked_from_admin_staff(): void
    {
        $sale = Sale::first();
        $this->assertNotNull($sale);

        $token = $sale->createToken('test-sales-token')->plainTextToken;

        // Vào phân hệ Sales thành công
        $respSales = $this->withCookie('auth_token', $token)->get('/sales/dashboard');
        $respSales->assertStatus(200);
        $respSales->assertSee('Báo cáo hiệu suất kinh doanh cá nhân');

        // Thử truy cập Admin Staff -> Bị redirect về Sales Dashboard kèm cảnh báo
        $respBlocked = $this->withCookie('auth_token', $token)->get('/admin/staff');
        $respBlocked->assertRedirect(route('sales.dashboard'));
        $respBlocked->assertSessionHas('warning');
    }

    /**
     * 4. Ban Quản Lý (PM) truy cập phân hệ PM thành công, bị chặn khi vào Admin Mail Settings.
     */
    public function test_pm_user_can_access_pm_portal_but_blocked_from_admin_mail_settings(): void
    {
        $pm = PropertyManager::first();
        $this->assertNotNull($pm);

        $token = $pm->createToken('test-pm-token')->plainTextToken;

        // Vào phân hệ PM thành công
        $respPm = $this->withCookie('auth_token', $token)->get('/pm/dashboard');
        $respPm->assertStatus(200);
        $respPm->assertSee('Ban Quản Lý');

        // Thử truy cập Admin Mail Settings -> Bị redirect về PM Dashboard kèm cảnh báo
        $respBlocked = $this->withCookie('auth_token', $token)->get('/admin/mail-settings');
        $respBlocked->assertRedirect(route('pm.dashboard'));
        $respBlocked->assertSessionHas('warning');
    }

    /**
     * 5. Kế toán truy cập phân hệ Kế toán thành công, bị chặn khi vào Admin Staff.
     */
    public function test_accountant_user_can_access_accountant_portal_but_blocked_from_admin_staff(): void
    {
        $acc = Accountant::first();
        $this->assertNotNull($acc);

        $token = $acc->createToken('test-acc-token')->plainTextToken;

        // Vào phân hệ Kế toán thành công
        $respAcc = $this->withCookie('auth_token', $token)->get('/accountant/dashboard');
        $respAcc->assertStatus(200);
        $respAcc->assertSee('Kế Toán');

        // Thử truy cập Admin Staff -> Bị redirect về Accountant Dashboard kèm cảnh báo
        $respBlocked = $this->withCookie('auth_token', $token)->get('/admin/staff');
        $respBlocked->assertRedirect(route('accountant.dashboard'));
        $respBlocked->assertSessionHas('warning');
    }

    /**
     * 6. Kiểm tra an toàn bảo mật: Giao diện tuyệt đối không in DEFAULT_STAFF_TOKEN ra HTML.
     */
    public function test_pages_do_not_leak_default_staff_token(): void
    {
        $superAdmin = Admin::where('is_super_admin', true)->first();
        $token = $superAdmin->createToken('test-token')->plainTextToken;

        $response = $this->withCookie('auth_token', $token)->get('/admin/dashboard');
        $response->assertStatus(200);
        $response->assertDontSee('DEFAULT_STAFF_TOKEN');
    }

    /**
     * 7. Kiểm tra Cookie thô không mã hóa từ JavaScript (Browser 1-Click Login) hoạt động hoàn hảo.
     */
    public function test_browser_unencrypted_cookie_and_url_encoded_token_authenticates_successfully(): void
    {
        $superAdmin = Admin::where('is_super_admin', true)->first();
        $token = $superAdmin->createToken('test-token')->plainTextToken;

        // Mô phỏng Cookie thô và URL-encoded được thiết lập từ Javascript trong trình duyệt thực tế
        $response = $this->withUnencryptedCookie('auth_token', urlencode($token))->get('/admin/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Tổng quan vận hành toàn hệ thống');
    }

    /**
     * 8. Kiểm tra Đăng xuất qua Form POST: Thu hồi Token DB, hủy Session và redirect về trang chủ.
     */
    public function test_web_logout_action_clears_session_and_revokes_token_successfully(): void
    {
        $admin = Admin::first();
        $tokenObj = $admin->createToken('test-logout-token');
        $plainToken = $tokenObj->plainTextToken;

        // Gửi form POST logout kèm input auth_token
        $response = $this->withSession(['test' => 1])
            ->withCookie('auth_token', $plainToken)
            ->post(route('web.logout'), [
                'auth_token' => $plainToken,
            ]);

        $response->assertRedirect('/');
        $response->assertSessionHas('info', 'Bạn đã đăng xuất khỏi hệ thống thành công.');

        // Token trong CSDL đã bị xóa
        $this->assertDatabaseMissing('personal_access_tokens', [
            'id' => $tokenObj->accessToken->id,
        ]);
    }

    /**
     * 9. Kiểm tra Đăng xuất qua AJAX: Trả về JSON thành công kèm dọn Cookie.
     */
    public function test_web_logout_ajax_request_returns_success_json(): void
    {
        $admin = Admin::first();
        $tokenObj = $admin->createToken('test-ajax-token');
        $plainToken = $tokenObj->plainTextToken;

        $response = $this->json('POST', route('web.logout'), [
            'auth_token' => $plainToken,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'message' => 'Bạn đã đăng xuất khỏi hệ thống thành công.',
        ]);

        $this->assertDatabaseMissing('personal_access_tokens', [
            'id' => $tokenObj->accessToken->id,
        ]);
    }
}
