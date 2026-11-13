<?php

namespace Tests\Feature;

use App\Models\Building;
use Database\Seeders\BuildingAndUnitSeeder;
use Database\Seeders\ProvinceSeeder;
use Database\Seeders\StaffAndAccountSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Class PortalWebTest
 *
 * Kiểm thử render trang chủ Portal tích hợp Blade, Modals & Auth.
 */
class PortalWebTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(StaffAndAccountSeeder::class);
        $this->seed(ProvinceSeeder::class);
        $this->seed(BuildingAndUnitSeeder::class);
        $this->seed(\Database\Seeders\OperationalBusinessSeeder::class);
    }

    /**
     * Kiểm tra tải trang chủ Public Portal thành công.
     */
    public function test_home_page_renders_successfully(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Cho Thuê Văn Phòng TP.HCM');
        $response->assertSee('Filament Admin');

        // Kiểm tra dữ liệu thật từ database được render lên trang chủ
        $response->assertSee('22+', false);
        $response->assertSee('428.430', false);
        $response->assertSee('10+', false);
    }

    /**
     * Kiểm tra trang chủ đã tích hợp Login Modal và các thành phần Auth.
     */
    public function test_home_page_contains_login_modal_and_auth_elements(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('id="login-modal"', false);
        $response->assertSee('Đăng Nhập Hệ Thống Đa Tác Nhân');
        $response->assertSee('admin@officeleasing.vn');
        $response->assertSee('btn-open-login');
        $response->assertSee('auth-login-form');
    }

    /**
     * Kiểm tra trang chủ đã tích hợp Profile Modal và các endpoint Auth & Profile.
     */
    public function test_home_page_contains_profile_and_security_modal(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('id="profile-modal"', false);
        $response->assertSee('Hồ Sơ Cá Nhân & Bảo Mật', false);
        $response->assertSee('change-password-form');
        $response->assertSee('/api/v1/auth/me', false);
        $response->assertSee('/api/v1/auth/password', false);
        $response->assertSee('/api/v1/auth/refresh', false);
    }

    /**
     * Kiểm tra trang web tích hợp toàn bộ giao diện & Modals Module 4 (Tickets & Maintenance).
     */
    public function test_home_page_contains_module_4_ticket_and_maintenance_modals(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('id="ticket-modal"', false);
        $response->assertSee('id="ticket-detail-modal"', false);
        $response->assertSee('id="ticket-assign-modal"', false);
        $response->assertSee('id="ticket-status-modal"', false);
        $response->assertSee('id="ticket-upload-proof-modal"', false);
        $response->assertSee('id="ticket-confirm-modal"', false);
        $response->assertSee('id="ticket-rate-modal"', false);
        $response->assertSee('id="ticket-edit-modal"', false);
        $response->assertSee('/api/v1/tickets', false);
        $response->assertSee('/api/v1/tenant/tickets', false);
    }

    /**
     * Kiểm tra trang web tích hợp toàn bộ giao diện, sub-tabs, Modals & Endpoints Module 5 (Staff & RBAC 72-91).
     */
    public function test_home_page_contains_module_5_staff_and_rbac_elements(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        // Sidebar & Sub-tabs
        $response->assertSee('id="nav-item-staff"', false);
        $response->assertSee('id="tab-admin-staff"', false);
        $response->assertSee('id="subpanel-staff-admins"', false);
        $response->assertSee('id="subpanel-staff-sales"', false);
        $response->assertSee('id="subpanel-staff-pm"', false);
        $response->assertSee('id="subpanel-staff-accountants"', false);

        // Modals 4 nhóm
        $response->assertSee('id="staff-admin-modal"', false);
        $response->assertSee('id="staff-admin-detail-modal"', false);
        $response->assertSee('id="staff-sale-modal"', false);
        $response->assertSee('id="staff-sale-detail-modal"', false);
        $response->assertSee('id="staff-pm-modal"', false);
        $response->assertSee('id="staff-pm-detail-modal"', false);
        $response->assertSee('id="staff-accountant-modal"', false);
        $response->assertSee('id="staff-accountant-detail-modal"', false);

        // Endpoints Module 5
        $response->assertSee('/api/v1/admin/users/admins', false);
        $response->assertSee('/api/v1/admin/users/sales', false);
        $response->assertSee('/api/v1/admin/users/property-managers', false);
        $response->assertSee('/api/v1/admin/users/accountants', false);
    }

    /**
     * Kiểm tra trang web tích hợp toàn bộ giao diện Phân công Tòa nhà & RBAC (Endpoints 92 - 98).
     */
    public function test_home_page_contains_module_5_building_assignments_elements(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);

        // Subtab & Panel
        $response->assertSee('id="btn-subtab-assignments"', false);
        $response->assertSee('id="subpanel-staff-assignments"', false);
        $response->assertSee('id="select-assignment-building"', false);
        $response->assertSee('id="tbody-building-assigned-sales"', false);
        $response->assertSee('id="tbody-building-assigned-pm"', false);
        $response->assertSee('id="tbody-building-assigned-accountants"', false);

        // Modals Gán nhân sự
        $response->assertSee('id="modal-assign-sales"', false);
        $response->assertSee('id="modal-assign-pm"', false);
        $response->assertSee('id="modal-assign-accountant"', false);

        // Endpoints 92 - 98
        $response->assertSee('/assignments/sales', false);
        $response->assertSee('/assignments/property-managers', false);
        $response->assertSee('/assignments/accountants', false);
    }
}
