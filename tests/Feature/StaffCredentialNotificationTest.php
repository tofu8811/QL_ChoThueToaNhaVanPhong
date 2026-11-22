<?php

namespace Tests\Feature;

use App\Models\Accountant;
use App\Models\Admin;
use App\Models\Building;
use App\Models\PropertyManager;
use App\Models\Sale;
use App\Notifications\Accountant\AccountantCredentialNotification;
use App\Notifications\Admin\AdminCredentialNotification;
use App\Notifications\PropertyManager\PropertyManagerCredentialNotification;
use App\Notifications\Sale\SaleCredentialNotification;
use App\Services\AuthService;
use App\Services\MailSettingService;
use Database\Seeders\BuildingAndUnitSeeder;
use Database\Seeders\OperationalBusinessSeeder;
use Database\Seeders\ProvinceSeeder;
use Database\Seeders\StaffAndAccountSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Kiểm thử tự động tính năng cấp tài khoản, tự sinh mật khẩu,
 * cấp lại mật khẩu (Admin-Assisted Reset) và gửi email thông báo cho các vai trò nhân sự
 * (Admin, Sale, Property Manager, Accountant).
 */
class StaffCredentialNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected Admin $superAdmin;
    protected string $superAdminToken;
    protected Building $building;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(StaffAndAccountSeeder::class);
        $this->seed(ProvinceSeeder::class);
        $this->seed(BuildingAndUnitSeeder::class);
        $this->seed(OperationalBusinessSeeder::class);

        $this->superAdmin = Admin::where('email', 'admin@officeleasing.vn')->firstOrFail();
        $this->superAdminToken = $this->superAdmin->createToken('super-admin')->plainTextToken;
        $this->building = Building::firstOrFail();
    }

    /**
     * 1. Test tạo mới Admin để trống mật khẩu -> Hệ thống tự sinh pass Ad@... và gửi AdminCredentialNotification.
     */
    public function test_creating_admin_with_auto_generated_password_dispatches_notification(): void
    {
        Notification::fake();

        $payload = [
            'full_name'      => 'Đỗ Minh Admin Test',
            'email'          => 'admin.auto@officeleasing.vn',
            'phone'          => '0911223344',
            'password'       => '', // Để trống để backend tự sinh mật khẩu an toàn
            'is_super_admin' => false,
            'status'         => 'ACTIVE',
        ];

        $res = $this->withHeader('Authorization', "Bearer {$this->superAdminToken}")
            ->postJson('/api/v1/admin/users/admins', $payload);

        $res->assertStatus(201);
        $plainPass = $res->json('data.plain_password');
        $this->assertNotEmpty($plainPass);
        $this->assertStringStartsWith('Ad@', $plainPass);

        $newAdmin = Admin::where('email', 'admin.auto@officeleasing.vn')->firstOrFail();
        $this->assertTrue(Hash::check($plainPass, $newAdmin->password));

        Notification::assertSentTo($newAdmin, AdminCredentialNotification::class, function ($n) use ($plainPass) {
            return $n->plainPassword === $plainPass && $n->isReset === false;
        });
    }

    /**
     * 2. Test Super Admin cấp lại mật khẩu cho Admin -> Sinh pass mới, xóa token cũ và gửi notification.
     */
    public function test_super_admin_can_reset_admin_password(): void
    {
        Notification::fake();

        $targetAdmin = Admin::create([
            'full_name' => 'Admin Cần Reset',
            'email' => 'admin.reset@officeleasing.vn',
            'password' => Hash::make('OldPassword123!'),
            'status' => 'ACTIVE',
        ]);
        $targetToken = $targetAdmin->createToken('old-session')->plainTextToken;
        $this->assertCount(1, $targetAdmin->tokens);

        $res = $this->withHeader('Authorization', "Bearer {$this->superAdminToken}")
            ->postJson("/api/v1/admin/users/admins/{$targetAdmin->id}/reset-password");

        $res->assertStatus(200);
        $newPass = $res->json('data.new_password');
        $this->assertNotEmpty($newPass);
        $this->assertStringStartsWith('Ad@', $newPass);

        // Kiểm tra mật khẩu trong DB đã đổi sang mật khẩu mới
        $freshAdmin = $targetAdmin->fresh();
        $this->assertTrue(Hash::check($newPass, $freshAdmin->password));
        // Kiểm tra token cũ đã bị thu hồi
        $this->assertCount(0, $freshAdmin->tokens);

        Notification::assertSentTo($freshAdmin, AdminCredentialNotification::class, function ($n) use ($newPass) {
            return $n->plainPassword === $newPass && $n->isReset === true;
        });
    }

    /**
     * 3. Test tạo mới Sales kèm tòa nhà -> Sinh pass Sa@... và gửi SaleCredentialNotification.
     */
    public function test_creating_sale_and_resetting_password(): void
    {
        Notification::fake();

        $payload = [
            'full_name'            => 'Lê Thị Sale Test',
            'email'                => 'sale.auto@officeleasing.vn',
            'phone'                => '0933557799',
            'password'             => '', // Tự sinh
            'commission_rate'      => 2.0,
            'max_concurrent_leads' => 15,
            'status'               => 'ACTIVE',
            'building_ids'         => [$this->building->id],
        ];

        $res = $this->withHeader('Authorization', "Bearer {$this->superAdminToken}")
            ->postJson('/api/v1/admin/users/sales', $payload);

        $res->assertStatus(201);
        $plainPass = $res->json('data.plain_password');
        $this->assertStringStartsWith('Sa@', $plainPass);

        $newSale = Sale::where('email', 'sale.auto@officeleasing.vn')->firstOrFail();
        Notification::assertSentTo($newSale, SaleCredentialNotification::class, function ($n) use ($plainPass) {
            return $n->plainPassword === $plainPass 
                && $n->isReset === false
                && in_array($this->building->name, $n->assignedBuildings);
        });

        // Test Cấp lại mật khẩu cho Sales
        $resetRes = $this->withHeader('Authorization', "Bearer {$this->superAdminToken}")
            ->postJson("/api/v1/admin/users/sales/{$newSale->id}/reset-password");

        $resetRes->assertStatus(200);
        $newPass = $resetRes->json('data.new_password');
        $this->assertStringStartsWith('Sa@', $newPass);

        Notification::assertSentTo($newSale->fresh(), SaleCredentialNotification::class, function ($n) use ($newPass) {
            return $n->plainPassword === $newPass && $n->isReset === true;
        });
    }

    /**
     * 4. Test tạo mới Property Manager (PM) và cấp lại mật khẩu.
     */
    public function test_creating_pm_and_resetting_password(): void
    {
        Notification::fake();

        $payload = [
            'full_name'         => 'Nguyễn Kỹ Thuật PM Test',
            'email'             => 'pm.auto@officeleasing.vn',
            'phone'             => '0944668800',
            'emergency_hotline' => '0944668800',
            'password'          => '',
            'department'        => 'TECHNICAL',
            'status'            => 'ACTIVE',
            'building_ids'      => [$this->building->id],
        ];

        $res = $this->withHeader('Authorization', "Bearer {$this->superAdminToken}")
            ->postJson('/api/v1/admin/users/property-managers', $payload);

        $res->assertStatus(201);
        $plainPass = $res->json('data.plain_password');
        $this->assertStringStartsWith('Pm@', $plainPass);

        $newPm = PropertyManager::where('email', 'pm.auto@officeleasing.vn')->firstOrFail();
        Notification::assertSentTo($newPm, PropertyManagerCredentialNotification::class, function ($n) use ($plainPass) {
            return $n->plainPassword === $plainPass 
                && $n->isReset === false
                && $n->department === 'Kỹ thuật & Bảo trì';
        });

        // Test Cấp lại mật khẩu cho PM
        $resetRes = $this->withHeader('Authorization', "Bearer {$this->superAdminToken}")
            ->postJson("/api/v1/admin/users/property-managers/{$newPm->id}/reset-password");

        $resetRes->assertStatus(200);
        $newPass = $resetRes->json('data.new_password');
        $this->assertStringStartsWith('Pm@', $newPass);

        Notification::assertSentTo($newPm->fresh(), PropertyManagerCredentialNotification::class, function ($n) use ($newPass) {
            return $n->plainPassword === $newPass && $n->isReset === true;
        });
    }

    /**
     * 5. Test tạo mới Kế toán (Accountant) và cấp lại mật khẩu.
     */
    public function test_creating_accountant_and_resetting_password(): void
    {
        Notification::fake();

        $payload = [
            'full_name'              => 'Phạm Thu Kế Toán Test',
            'email'                  => 'acc.auto@officeleasing.vn',
            'phone'                  => '0977889900',
            'password'               => '',
            'can_approve_adjustment' => true,
            'status'                 => 'ACTIVE',
            'building_ids'           => [$this->building->id],
        ];

        $res = $this->withHeader('Authorization', "Bearer {$this->superAdminToken}")
            ->postJson('/api/v1/admin/users/accountants', $payload);

        $res->assertStatus(201);
        $plainPass = $res->json('data.plain_password');
        $this->assertStringStartsWith('Ac@', $plainPass);

        $newAcc = Accountant::where('email', 'acc.auto@officeleasing.vn')->firstOrFail();
        Notification::assertSentTo($newAcc, AccountantCredentialNotification::class, function ($n) use ($plainPass) {
            return $n->plainPassword === $plainPass 
                && $n->isReset === false
                && in_array($this->building->name, $n->assignedBuildings);
        });

        // Test Cấp lại mật khẩu cho Kế toán
        $resetRes = $this->withHeader('Authorization', "Bearer {$this->superAdminToken}")
            ->postJson("/api/v1/admin/users/accountants/{$newAcc->id}/reset-password");

        $resetRes->assertStatus(200);
        $newPass = $resetRes->json('data.new_password');
        $this->assertStringStartsWith('Ac@', $newPass);

        Notification::assertSentTo($newAcc->fresh(), AccountantCredentialNotification::class, function ($n) use ($newPass) {
            return $n->plainPassword === $newPass && $n->isReset === true;
        });
    }

    /**
     * 6. Test cơ chế kiểm soát gửi mail: Khi MailSettingService tắt -> via() trả về [].
     */
    public function test_notification_via_returns_empty_when_mail_disabled(): void
    {
        $mailService = $this->createMock(MailSettingService::class);
        $mailService->method('isEnabled')->willReturn(false);
        $this->app->instance(MailSettingService::class, $mailService);

        $notif = new AdminCredentialNotification('Pass123456@', false, false);
        $channels = $notif->via($this->superAdmin);

        $this->assertSame([], $channels);
    }

    /**
     * 7. Test nhân sự đăng nhập thành công với mật khẩu vừa cấp và must_change_password là false.
     */
    public function test_staff_can_login_with_issued_password_without_forced_password_change(): void
    {
        Notification::fake();

        $payload = [
            'full_name'      => 'Vũ Nhân Viên Mới',
            'email'          => 'vu.staff@officeleasing.vn',
            'phone'          => '0988776655',
            'password'       => '',
            'status'         => 'ACTIVE',
        ];

        $res = $this->withHeader('Authorization', "Bearer {$this->superAdminToken}")
            ->postJson('/api/v1/admin/users/sales', $payload);

        $plainPass = $res->json('data.plain_password');

        $authService = app(AuthService::class);
        $loginResult = $authService->login([
            'email'    => 'vu.staff@officeleasing.vn',
            'password' => $plainPass,
        ]);

        $this->assertEquals('sales', $loginResult['role']);
        $this->assertNotEmpty($loginResult['access_token']);
        // Bắt buộc: must_change_password phải là false đối với nhân sự nội bộ
        $this->assertFalse($loginResult['must_change_password']);
    }
}
