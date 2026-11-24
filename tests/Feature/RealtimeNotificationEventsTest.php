<?php

namespace Tests\Feature;

use App\Enums\ContractStatus;
use App\Enums\LeadStatus;
use App\Enums\UserRole;
use App\Events\BuildingAssignedToStaffEvent;
use App\Events\ContractStatusChangedEvent;
use App\Events\LeadAssignedToSalesEvent;
use App\Events\LeadPendingAssignmentEvent;
use App\Models\Accountant;
use App\Models\Admin;
use App\Models\Building;
use App\Models\Contract;
use App\Models\Lead;
use App\Models\PropertyManager;
use App\Models\Sale;
use App\Models\Tenant;
use App\Models\Unit;
use App\Services\BuildingAssignmentService;
use App\Services\ContractService;
use App\Services\LeadService;
use App\Services\SaleUserService;
use Database\Seeders\BuildingAndUnitSeeder;
use Database\Seeders\OperationalBusinessSeeder;
use Database\Seeders\ProvinceSeeder;
use Database\Seeders\StaffAndAccountSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class RealtimeNotificationEventsTest extends TestCase
{
    use RefreshDatabase;

    protected Admin $admin;
    protected Sale $sale;
    protected PropertyManager $pm;
    protected Accountant $accountant;
    protected Building $building;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(StaffAndAccountSeeder::class);
        $this->seed(ProvinceSeeder::class);
        $this->seed(BuildingAndUnitSeeder::class);
        $this->seed(OperationalBusinessSeeder::class);

        $this->admin = Admin::firstOrFail();
        $this->sale = Sale::firstOrFail();
        $this->pm = PropertyManager::firstOrFail();
        $this->accountant = Accountant::firstOrFail();
        $this->building = Building::firstOrFail();
    }

    /**
     * Kịch bản 1: Kiểm thử cấu trúc Event BuildingAssignedToStaffEvent (Channels & Payload).
     */
    public function test_building_assigned_to_staff_event_structure(): void
    {
        $event = new BuildingAssignedToStaffEvent(
            $this->building,
            $this->sale->id,
            UserRole::SALES,
            $this->admin
        );

        $this->assertSame('building.assigned', $event->broadcastAs());

        $channels = collect($event->broadcastOn())->map(fn ($c) => $c->name)->all();
        $this->assertContains("staff.{$this->sale->id}", $channels);
        $this->assertContains("user.{$this->sale->id}", $channels);
        $this->assertContains("sales.{$this->sale->id}", $channels);

        $payload = $event->broadcastWith();
        $this->assertSame($this->building->id, $payload['building_id']);
        $this->assertSame($this->building->name, $payload['building_name']);
        $this->assertSame('sales', $payload['role']);
        $this->assertStringContainsString($this->building->name, $payload['message']);
    }

    /**
     * Kịch bản 1: BuildingAssignmentService phát sóng sự kiện khi gán Sale, PM, Accountant.
     */
    public function test_building_assignment_service_dispatches_events(): void
    {
        Event::fake([BuildingAssignedToStaffEvent::class]);

        $service = app(BuildingAssignmentService::class);

        // 1. Gán Sales
        $service->assignSales($this->building->id, [$this->sale->id], true, $this->admin);
        Event::assertDispatched(BuildingAssignedToStaffEvent::class, function ($e) {
            return $e->staffId === $this->sale->id && $e->building->id === $this->building->id;
        });

        // 2. Gán PM
        $service->assignPropertyManager($this->building->id, $this->pm->id, true, $this->admin);
        Event::assertDispatched(BuildingAssignedToStaffEvent::class, function ($e) {
            return $e->staffId === $this->pm->id && $e->building->id === $this->building->id;
        });

        // 3. Gán Accountant
        $service->assignAccountant($this->building->id, [$this->accountant->id], $this->admin);
        Event::assertDispatched(BuildingAssignedToStaffEvent::class, function ($e) {
            return $e->staffId === $this->accountant->id && $e->building->id === $this->building->id;
        });
    }

    /**
     * Kịch bản 1 mở rộng: Phát sóng sự kiện và thông báo khi gỡ nhân viên khỏi tòa nhà.
     */
    public function test_building_staff_removed_event_dispatched_on_remove(): void
    {
        Event::fake([\App\Events\BuildingStaffRemovedEvent::class]);

        $service = app(BuildingAssignmentService::class);

        // Gán trước để có bản ghi xóa
        DB::table('sale_buildings')->updateOrInsert(
            ['building_id' => $this->building->id, 'sales_id' => $this->sale->id],
            ['is_active' => true, 'assigned_by_admin_id' => $this->admin->id]
        );

        $service->removeSales($this->building->id, $this->sale->id);

        Event::assertDispatched(\App\Events\BuildingStaffRemovedEvent::class, function ($e) {
            return $e->staffId === $this->sale->id && $e->building->id === $this->building->id;
        });
    }

    /**
     * Kịch bản 2: LeadAssignedToSalesEvent phát sóng đúng kênh và payload cho Sales.
     */
    public function test_lead_assigned_to_sales_event_structure(): void
    {
        $lead = Lead::firstOrFail();
        $lead->assigned_sales_id = $this->sale->id;
        $lead->save();

        $event = new LeadAssignedToSalesEvent($lead);

        $this->assertSame('lead.assigned', $event->broadcastAs());

        $channels = collect($event->broadcastOn())->map(fn ($c) => $c->name)->all();
        $this->assertContains("sales.{$this->sale->id}", $channels);
        $this->assertContains("staff.{$this->sale->id}", $channels);

        $payload = $event->broadcastWith();
        $this->assertSame($lead->id, $payload['id']);
        $this->assertSame($lead->contact_full_name, $payload['contact_full_name']);
        $this->assertStringContainsString($lead->contact_full_name, $payload['message']);
    }

    /**
     * Kịch bản 3: ContractStatusChangedEvent broadcast tới toàn bộ nhân sự phụ trách tòa nhà.
     */
    public function test_contract_status_changed_event_broadcasts_to_all_assigned_staff(): void
    {
        // Gán nhân sự vào tòa nhà
        DB::table('sale_buildings')->updateOrInsert(
            ['building_id' => $this->building->id, 'sales_id' => $this->sale->id],
            ['is_active' => true, 'assigned_by_admin_id' => $this->admin->id]
        );
        DB::table('manager_buildings')->updateOrInsert(
            ['building_id' => $this->building->id, 'pm_id' => $this->pm->id],
            ['is_primary' => true, 'assigned_by_admin_id' => $this->admin->id]
        );
        DB::table('account_buildings')->updateOrInsert(
            ['building_id' => $this->building->id, 'accountant_id' => $this->accountant->id],
            ['assigned_by_admin_id' => $this->admin->id]
        );

        $contract = Contract::firstOrFail();
        $contract->building_id = $this->building->id;
        $contract->save();

        $event = new ContractStatusChangedEvent(
            $contract,
            'SIGNED',
            ContractStatus::DRAFT->value,
            ContractStatus::ACTIVE->value,
            $this->admin
        );

        $this->assertSame('contract.status_changed', $event->broadcastAs());

        $channels = collect($event->broadcastOn())->map(fn ($c) => $c->name)->all();

        // Kênh thông báo Quản trị
        $this->assertContains('admin-notifications', $channels);
        // Kênh theo dõi của tòa nhà
        $this->assertContains("building.{$this->building->id}.contracts", $channels);
        // Kênh từng nhân sự phụ trách
        $this->assertContains("staff.{$this->sale->id}", $channels);
        $this->assertContains("staff.{$this->pm->id}", $channels);
        $this->assertContains("staff.{$this->accountant->id}", $channels);

        $payload = $event->broadcastWith();
        $this->assertSame($contract->id, $payload['contract_id']);
        $this->assertSame('SIGNED', $payload['action']);
        $this->assertStringContainsString('ký duyệt', $payload['message']);
    }

    /**
     * Kịch bản 3: ContractService dispatches ContractStatusChangedEvent khi ký duyệt & xóa/hủy nháp.
     */
    public function test_contract_service_dispatches_status_changed_event(): void
    {
        Event::fake([ContractStatusChangedEvent::class]);

        $contractService = app(ContractService::class);

        // Tạo hợp đồng nháp kèm 1 unit
        $tenant = Tenant::firstOrFail();
        $unit = Unit::where('building_id', $this->building->id)->where('status', 'AVAILABLE')->firstOrFail();

        $draft = Contract::create([
            'contract_number' => 'HD-TEST-RT-' . time(),
            'building_id' => $this->building->id,
            'tenant_id' => $tenant->id,
            'created_by_sales_id' => $this->sale->id,
            'start_date' => now()->addDay()->toDateString(),
            'end_date' => now()->addYear()->toDateString(),
            'total_leased_area_sqm' => 100,
            'deposit_amount' => 50000000,
            'payment_cycle_months' => 3,
            'payment_due_day' => 5,
            'late_fee_interest_percent' => 0.05,
            'status' => ContractStatus::DRAFT,
        ]);

        $draft->contractUnits()->create([
            'unit_id' => $unit->id,
            'building_id' => $this->building->id,
            'leased_area_sqm' => 100,
            'agreed_rental_price_per_sqm' => 500000,
            'agreed_service_fee_per_sqm' => 50000,
        ]);

        // 1. Ký duyệt hợp đồng -> phát sóng SIGNED
        $signed = $contractService->signContract($draft, $this->admin);

        Event::assertDispatched(ContractStatusChangedEvent::class, function ($e) use ($signed) {
            return $e->contract->id === $signed->id && $e->action === 'SIGNED';
        });

        // 2. Tạo một draft khác rồi xóa -> phát sóng REJECTED
        $draft2 = Contract::create([
            'contract_number' => 'HD-TEST-RT2-' . time(),
            'building_id' => $this->building->id,
            'tenant_id' => $tenant->id,
            'created_by_sales_id' => $this->sale->id,
            'start_date' => now()->addDays(5)->toDateString(),
            'end_date' => now()->addYear()->toDateString(),
            'total_leased_area_sqm' => 50,
            'deposit_amount' => 20000000,
            'payment_cycle_months' => 3,
            'payment_due_day' => 5,
            'late_fee_interest_percent' => 0.05,
            'status' => ContractStatus::DRAFT,
        ]);

        $contractService->deleteDraftContract($draft2, $this->admin);

        Event::assertDispatched(ContractStatusChangedEvent::class, function ($e) use ($draft2) {
            return $e->contract->id === $draft2->id && $e->action === 'REJECTED';
        });
    }

    /**
     * Kịch bản 4: LeadPendingAssignmentEvent được kích hoạt cho Admin khi không có Sales khả dụng.
     */
    public function test_lead_pending_assignment_event_dispatched_for_admin(): void
    {
        Event::fake([LeadPendingAssignmentEvent::class]);

        $leadService = app(LeadService::class);

        // Khóa tất cả Sales để không có Sales nào khả dụng
        Sale::query()->update(['status' => 'LOCKED']);

        $lead = $leadService->storePortalLead([
            'contact_full_name' => 'Công Ty Test Realtime Lead',
            'phone' => '0988776655',
            'email' => 'contact@testrealtime.vn',
            'building_id' => $this->building->id,
        ]);

        $this->assertSame(LeadStatus::PENDING_ASSIGN, $lead->status);

        Event::assertDispatched(LeadPendingAssignmentEvent::class, function ($e) use ($lead) {
            return $e->lead->id === $lead->id;
        });
    }

    /**
     * Kịch bản 4: Xóa Sales và đưa leads dở dang về PENDING_ASSIGN kích hoạt LeadPendingAssignmentEvent.
     */
    public function test_delete_sale_with_unassign_dispatches_pending_assignment_event(): void
    {
        Event::fake([LeadPendingAssignmentEvent::class]);

        $saleUserService = app(SaleUserService::class);

        // Tạo 1 lead đang phụ trách bởi $this->sale
        $lead = Lead::create([
            'contact_full_name' => 'Doanh Nghiệp Chuyển Giao',
            'phone' => '0911223344',
            'email' => 'transfer@test.vn',
            'status' => LeadStatus::NEW,
            'assigned_sales_id' => $this->sale->id,
            'assigned_at' => now(),
        ]);

        // Xóa Sales với chế độ unassign
        $saleUserService->deleteSale($this->sale->id, 'unassign');

        Event::assertDispatched(LeadPendingAssignmentEvent::class, function ($e) use ($lead) {
            return $e->lead->id === $lead->id;
        });
    }
}
