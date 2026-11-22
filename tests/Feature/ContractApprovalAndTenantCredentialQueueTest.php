<?php

namespace Tests\Feature;

use App\Enums\ContractStatus;
use App\Enums\UnitStatus;
use App\Livewire\ContractManager;
use App\Models\Admin;
use App\Models\Building;
use App\Models\Contract;
use App\Models\ContractUnit;
use App\Models\Sale;
use App\Models\Tenant;
use App\Models\Unit;
use App\Notifications\Tenant\TenantCredentialNotification;
use App\Notifications\Tenant\TenantResetPasswordNotification;
use App\Services\AuthService;
use App\Services\ContractService;
use App\Services\TenantService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Livewire\Livewire;
use Tests\TestCase;

class ContractApprovalAndTenantCredentialQueueTest extends TestCase
{
    use RefreshDatabase;

    private Building $building;
    private Unit $unit;
    private Sale $sale;
    private Admin $admin;
    private Tenant $tenant;
    private Contract $contract;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = Admin::create([
            'employee_code' => 'ADM-TEST-01',
            'email' => 'admin.test@officeleasing.vn',
            'full_name' => 'Super Administrator Test',
            'password' => Hash::make('AdminSecret123!'),
            'status' => 'ACTIVE',
        ]);

        $this->sale = Sale::create([
            'employee_code' => 'SALES-TEST-01',
            'email' => 'sale.test@officeleasing.vn',
            'full_name' => 'Nguyễn Văn Sales',
            'phone' => '0901234567',
            'password' => Hash::make('SaleSecret123!'),
            'status' => 'ACTIVE',
            'max_concurrent_leads' => 10,
        ]);

        $this->building = Building::create([
            'building_code' => 'BLD-TEST',
            'name' => 'Bitexco Test Tower',
            'slug' => 'bitexco-test-tower',
            'total_floors' => 20,
            'total_leasable_area_sqm' => 5000.00,
            'address' => '2 Hải Triều',
            'district' => 'Quận 1',
            'ward' => 'Bến Nghé',
            'province' => 'Hồ Chí Minh',
            'grade' => 'A',
            'rental_price_usd_per_sqm' => 35.00,
            'service_fee_usd_per_sqm' => 6.00,
            'status' => 'ACTIVE',
        ]);

        $this->unit = Unit::create([
            'unit_code' => 'U-1001',
            'building_id' => $this->building->id,
            'floor_number' => 10,
            'name' => 'Phòng 1001',
            'area_sqm' => 120.00,
            'base_rental_price' => 800000.00,
            'gross_rental_price_per_sqm' => 35.00,
            'net_rental_price_per_sqm' => 30.00,
            'service_fee_per_sqm' => 6.00,
            'status' => UnitStatus::AVAILABLE,
        ]);

        $this->tenant = Tenant::create([
            'tenant_code' => 'TNT-2026-0001',
            'company_name' => 'Công Ty TNHH Alpha Solutions',
            'tax_code' => '0318999888',
            'business_license_private_path' => 'licenses/default.pdf',
            'representative_name' => 'Trần Văn Alpha',
            'representative_position' => 'Tổng Giám Đốc',
            'headquarter_address' => 'Tầng 10, Tòa nhà Bitexco, Q1, TP.HCM',
            'login_email' => 'contact@alphasolutions.test',
            'billing_email' => 'accounting@alphasolutions.test',
            'contact_phone' => '0988776655',
            'password' => Hash::make('InitialPassword123'),
            'status' => 'ACTIVE',
            'must_change_password' => false,
            'last_login_at' => null,
            'initial_password_issued_at' => null,
            'created_by_sales_id' => $this->sale->id,
        ]);

        $this->contract = Contract::create([
            'contract_number' => 'HD-BLD-TEST-2026-0001',
            'building_id' => $this->building->id,
            'tenant_id' => $this->tenant->id,
            'created_by_sales_id' => $this->sale->id,
            'signed_date' => now()->toDateString(),
            'start_date' => now()->toDateString(),
            'end_date' => now()->addYears(2)->toDateString(),
            'total_leased_area_sqm' => 120.00,
            'deposit_amount' => 126000000,
            'payment_cycle_months' => 3,
            'payment_due_day' => 5,
            'late_fee_interest_percent' => 0.05,
            'annual_price_escalation_percent' => 5.00,
            'contract_pdf_private_path' => 'contracts/scans/default.pdf',
            'status' => ContractStatus::DRAFT,
        ]);

        ContractUnit::create([
            'building_id' => $this->building->id,
            'contract_id' => $this->contract->id,
            'unit_id' => $this->unit->id,
            'leased_area_sqm' => 120.00,
            'agreed_rental_price_per_sqm' => 35.00,
            'agreed_service_fee_per_sqm' => 6.00,
        ]);
    }

    /**
     * Test 1: Khi Admin duyệt hợp đồng, tự động cấp tài khoản, sinh mật khẩu ngẫu nhiên an toàn,
     * đánh dấu must_change_password = true, và đẩy Notification vào Queue.
     */
    public function test_sign_contract_automatically_generates_credentials_and_queues_email(): void
    {
        Notification::fake();

        $contractService = app(ContractService::class);
        $signedContract = $contractService->signContract($this->contract, $this->admin);

        // 1. Kiểm tra trạng thái Hợp đồng & Mặt bằng
        $this->assertEquals(ContractStatus::ACTIVE, $signedContract->status);
        $this->assertEquals(UnitStatus::LEASED, $this->unit->fresh()->status);

        // 2. Kiểm tra thông tin Tenant trong CSDL
        $updatedTenant = $this->tenant->fresh();
        $this->assertTrue($updatedTenant->must_change_password);
        $this->assertNotNull($updatedTenant->initial_password_issued_at);

        // 3. Kiểm tra thông tin credentials trả về cho UI
        $this->assertNotEmpty($signedContract->tenant_credential_issued);
        $issued = $signedContract->tenant_credential_issued;
        $this->assertEquals('contact@alphasolutions.test', $issued['login_email']);
        $this->assertEquals('TNT-2026-0001', $issued['tenant_code']);
        $this->assertStringStartsWith('Tn@', $issued['password']);
        $this->assertEquals(10, strlen($issued['password']));

        // 4. Mật khẩu thuần khớp với hash đã lưu
        $this->assertTrue(Hash::check($issued['password'], $updatedTenant->password));

        // 5. Kiểm tra Notification được đẩy vào Queue
        Notification::assertSentTo($updatedTenant, TenantCredentialNotification::class, function ($notification) use ($issued) {
            return $notification->plainPassword === $issued['password'];
        });
    }

    /**
     * Test 2: Tenant có thể đăng nhập bằng cả login_email và tenant_code với mật khẩu mới cấp,
     * và payload phản hồi chứa cờ must_change_password = true.
     */
    public function test_tenant_can_login_with_generated_password_and_tenant_code(): void
    {
        Notification::fake();

        $contractService = app(ContractService::class);
        $signedContract = $contractService->signContract($this->contract, $this->admin);
        $plainPassword = $signedContract->tenant_credential_issued['password'];

        $authService = app(AuthService::class);

        // 1. Đăng nhập qua login_email
        $resultEmail = $authService->login([
            'email' => 'contact@alphasolutions.test',
            'password' => $plainPassword,
        ]);

        $this->assertEquals('tenant', $resultEmail['role']);
        $this->assertTrue($resultEmail['must_change_password']);
        $this->assertNotEmpty($resultEmail['access_token']);

        // 2. Đăng nhập qua tenant_code
        $resultCode = $authService->login([
            'email' => 'TNT-2026-0001',
            'password' => $plainPassword,
        ]);

        $this->assertEquals('tenant', $resultCode['role']);
        $this->assertTrue($resultCode['must_change_password']);
        $this->assertNotEmpty($resultCode['access_token']);
    }

    /**
     * Test 3: Cơ chế quên mật khẩu qua email tự động (Token Broker & Queue Notification & Reset).
     */
    public function test_tenant_forgot_password_and_reset_flow(): void
    {
        Notification::fake();

        $authService = app(AuthService::class);

        // 1. Gửi yêu cầu quên mật khẩu
        $authService->sendTenantPasswordResetLink($this->tenant->login_email);

        // 2. Kiểm tra email notification được phát qua Queue
        Notification::assertSentTo($this->tenant, TenantResetPasswordNotification::class);

        // 3. Kiểm tra token đã được lưu vào password_reset_tokens
        $this->assertDatabaseHas('password_reset_tokens', [
            'email' => $this->tenant->login_email,
        ]);

        // Tạo token hợp lệ để test đặt lại mật khẩu
        $token = Password::broker('tenants')->createToken($this->tenant);

        // 4. Đặt lại mật khẩu mới
        $authService->resetTenantPassword([
            'token' => $token,
            'email' => $this->tenant->login_email,
            'password' => 'NewSecurePassword123#',
            'password_confirmation' => 'NewSecurePassword123#',
        ]);

        // 5. Kiểm tra mật khẩu mới đã có hiệu lực và must_change_password chuyển về false
        $refreshedTenant = $this->tenant->fresh();
        $this->assertTrue(Hash::check('NewSecurePassword123#', $refreshedTenant->password));
        $this->assertFalse($refreshedTenant->must_change_password);
        $this->assertNotNull($refreshedTenant->password_changed_at);
    }

    /**
     * Test 4: Quản trị viên / Sales cấp lại mật khẩu tạm thời cho khách (Admin-Assisted Reset).
     */
    public function test_admin_can_reset_tenant_password_assisted(): void
    {
        Notification::fake();

        $tenantService = app(TenantService::class);
        $tempPassword = $tenantService->adminResetPassword($this->tenant, $this->admin);

        // 1. Kiểm tra mật khẩu tạm mới sinh ra
        $this->assertStringStartsWith('Tn@', $tempPassword);
        $this->assertEquals(10, strlen($tempPassword));

        // 2. Mật khẩu mới khớp với CSDL
        $updated = $this->tenant->fresh();
        $this->assertTrue(Hash::check($tempPassword, $updated->password));
        $this->assertTrue($updated->must_change_password);

        // 3. Notification được gửi tới khách qua Queue
        Notification::assertSentTo($updated, TenantCredentialNotification::class);
    }

    /**
     * Test 5: Giao diện Livewire ContractManager hiển thị One-Time Modal khi Admin bấm Sign Contract.
     */
    public function test_contract_manager_livewire_reveals_credential_modal_on_sign(): void
    {
        Notification::fake();

        Auth::setUser($this->admin);

        Livewire::test(ContractManager::class)
            ->call('signContract', $this->contract->id)
            ->assertSet('showCredentialModal', true)
            ->assertSee('Bàn giao tài khoản Cổng Khách Thuê')
            ->assertSee('contact@alphasolutions.test')
            ->assertSee('Tn@');
    }

    /**
     * Test 6: Kiểm tra cấu hình No-Reply bắt buộc (Reply-To & RFC Headers chặn phản hồi tự động).
     */
    public function test_notifications_enforce_no_reply_headers_and_blackhole_reply_to(): void
    {
        $notification = new TenantCredentialNotification($this->contract, 'Tn@1234567');
        $mail = $notification->toMail($this->tenant);

        // Kiểm tra Reply-To được cấu hình
        $this->assertNotEmpty($mail->replyTo);
        $this->assertEquals(config('mail.reply_to.address', 'noreply@noreply.invalid'), $mail->replyTo[0][0]);

        // Kiểm tra callbacks chứa Symfony Message headers Auto-Submitted & X-Auto-Response-Suppress
        $this->assertNotEmpty($mail->callbacks);
        $symfonyEmail = new \Symfony\Component\Mime\Email();
        foreach ($mail->callbacks as $callback) {
            $callback($symfonyEmail);
        }

        $this->assertEquals('auto-generated', $symfonyEmail->getHeaders()->get('Auto-Submitted')?->getBodyAsString());
        $this->assertEquals('All', $symfonyEmail->getHeaders()->get('X-Auto-Response-Suppress')?->getBodyAsString());
        $this->assertEquals('bulk', $symfonyEmail->getHeaders()->get('Precedence')?->getBodyAsString());
    }
}
