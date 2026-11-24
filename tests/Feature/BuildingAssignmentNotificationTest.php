<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Events\BuildingAssignedToStaffEvent;
use App\Events\BuildingStaffRemovedEvent;
use App\Models\Admin;
use App\Models\Building;
use App\Models\Sale;
use App\Services\BuildingAssignmentService;
use App\Services\SaleUserService;
use Database\Seeders\BuildingAndUnitSeeder;
use Database\Seeders\ProvinceSeeder;
use Database\Seeders\StaffAndAccountSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class BuildingAssignmentNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected Admin $admin;
    protected Sale $sale;
    protected Building $building;
    protected Building $building2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(StaffAndAccountSeeder::class);
        $this->seed(ProvinceSeeder::class);
        $this->seed(BuildingAndUnitSeeder::class);

        $this->admin = Admin::firstOrFail();
        $this->sale = Sale::firstOrFail();

        $buildings = Building::take(2)->get();
        $this->building = $buildings[0];
        $this->building2 = $buildings[1];
    }

    /**
     * 1. Kiểm thử BuildingAssignmentService::assignSales phát sóng BuildingAssignedToStaffEvent.
     */
    public function test_assign_sales_dispatches_assigned_event(): void
    {
        Event::fake([BuildingAssignedToStaffEvent::class]);

        $service = app(BuildingAssignmentService::class);
        $service->assignSales($this->building->id, [$this->sale->id], true, $this->admin);

        Event::assertDispatched(BuildingAssignedToStaffEvent::class, function (BuildingAssignedToStaffEvent $event) {
            $channels = collect($event->broadcastOn())->map(fn ($c) => $c->name)->all();

            $this->assertContains("staff.{$this->sale->id}", $channels);
            $this->assertContains("sales.{$this->sale->id}", $channels);
            $this->assertContains("user.{$this->sale->id}", $channels);
            $this->assertSame('building.assigned', $event->broadcastAs());

            $payload = $event->broadcastWith();
            $this->assertSame($this->building->id, $payload['building_id']);
            $this->assertSame('sales', $payload['role']);
            $this->assertStringContainsString($this->building->name, $payload['message']);

            return $event->staffId === $this->sale->id && $event->building->id === $this->building->id;
        });
    }

    /**
     * 2. Kiểm thử BuildingAssignmentService::removeSales phát sóng BuildingStaffRemovedEvent.
     */
    public function test_remove_sales_dispatches_removed_event(): void
    {
        Event::fake([BuildingStaffRemovedEvent::class]);

        DB::table('sale_buildings')->updateOrInsert(
            ['building_id' => $this->building->id, 'sales_id' => $this->sale->id],
            ['is_active' => true, 'assigned_by_admin_id' => $this->admin->id]
        );

        $service = app(BuildingAssignmentService::class);
        $result = $service->removeSales($this->building->id, $this->sale->id);

        $this->assertTrue($result);

        Event::assertDispatched(BuildingStaffRemovedEvent::class, function (BuildingStaffRemovedEvent $event) {
            $channels = collect($event->broadcastOn())->map(fn ($c) => $c->name)->all();

            $this->assertContains("staff.{$this->sale->id}", $channels);
            $this->assertContains("sales.{$this->sale->id}", $channels);
            $this->assertContains("user.{$this->sale->id}", $channels);
            $this->assertSame('building.removed', $event->broadcastAs());

            $payload = $event->broadcastWith();
            $this->assertSame($this->building->id, $payload['building_id']);
            $this->assertStringContainsString($this->building->name, $payload['message']);

            return $event->staffId === $this->sale->id && $event->building->id === $this->building->id;
        });
    }

    /**
     * 3. Kiểm thử SaleUserService::createSale phát sóng BuildingAssignedToStaffEvent khi gán tòa nhà.
     */
    public function test_sale_user_service_create_sale_dispatches_event(): void
    {
        Event::fake([BuildingAssignedToStaffEvent::class]);

        $service = app(SaleUserService::class);
        $newSale = $service->createSale([
            'full_name' => 'Nguyễn Văn Test',
            'email' => 'test.sale.notif@officeleasing.vn',
            'phone' => '0988776655',
            'building_ids' => [$this->building->id, $this->building2->id],
        ], $this->admin);

        Event::assertDispatched(BuildingAssignedToStaffEvent::class, function ($e) use ($newSale) {
            return $e->staffId === $newSale->id && $e->building->id === $this->building->id;
        });

        Event::assertDispatched(BuildingAssignedToStaffEvent::class, function ($e) use ($newSale) {
            return $e->staffId === $newSale->id && $e->building->id === $this->building2->id;
        });
    }

    /**
     * 4. Kiểm thử SaleUserService::updateSale phát sóng cả sự kiện gán mới và gỡ bỏ tòa nhà.
     */
    public function test_sale_user_service_update_sale_dispatches_assigned_and_removed_events(): void
    {
        Event::fake([BuildingAssignedToStaffEvent::class, BuildingStaffRemovedEvent::class]);

        // Ban đầu gán tòa 1
        DB::table('sale_buildings')->insert([
            'building_id' => $this->building->id,
            'sales_id' => $this->sale->id,
            'is_active' => true,
            'assigned_by_admin_id' => $this->admin->id,
            'assigned_at' => now(),
        ]);

        $service = app(SaleUserService::class);

        // Cập nhật đổi sang tòa 2 (gỡ tòa 1, thêm tòa 2)
        $service->updateSale($this->sale->id, [
            'building_ids' => [$this->building2->id],
        ], $this->admin);

        // Tòa 2 được thêm -> phát sóng BuildingAssignedToStaffEvent
        Event::assertDispatched(BuildingAssignedToStaffEvent::class, function ($e) {
            return $e->staffId === $this->sale->id && $e->building->id === $this->building2->id;
        });

        // Tòa 1 bị gỡ -> phát sóng BuildingStaffRemovedEvent
        Event::assertDispatched(BuildingStaffRemovedEvent::class, function ($e) {
            return $e->staffId === $this->sale->id && $e->building->id === $this->building->id;
        });
    }

    /**
     * 5. Kiểm thử API Endpoints gán và gỡ Sales vào tòa nhà.
     */
    public function test_api_assign_and_remove_sales_endpoints(): void
    {
        Event::fake([BuildingAssignedToStaffEvent::class, BuildingStaffRemovedEvent::class]);

        $token = $this->admin->createToken('admin-test', ['admin'])->plainTextToken;

        // API 93: Gán Sales
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson("/api/v1/admin/buildings/{$this->building->id}/assignments/sales", [
                'sales_ids' => [$this->sale->id],
                'is_active' => true,
            ]);

        $response->assertStatus(201);
        Event::assertDispatched(BuildingAssignedToStaffEvent::class);

        // API 94: Gỡ Sales
        $responseDelete = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->deleteJson("/api/v1/admin/buildings/{$this->building->id}/assignments/sales/{$this->sale->id}");

        $responseDelete->assertStatus(200);
        Event::assertDispatched(BuildingStaffRemovedEvent::class);
    }
}
