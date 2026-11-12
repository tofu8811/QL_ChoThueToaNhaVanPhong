<?php

namespace Tests\Feature;

use App\Enums\ContractStatus;
use App\Enums\UnitStatus;
use App\Models\Admin;
use App\Models\Building;
use App\Models\Contract;
use App\Models\PropertyManager;
use App\Models\Sale;
use App\Models\Tenant;
use App\Models\Unit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContractAndUnitEnhancementTest extends TestCase
{
    use RefreshDatabase;

    private Building $building;
    private Tenant $tenant;
    private Sale $sale;
    private Admin $admin;
    private PropertyManager $pm;

    protected function setUp(): void
    {
        parent::setUp();

        $this->building = Building::create([
            'building_code' => 'BLD-TEST-01',
            'name' => 'Tòa nhà Test Enhancement',
            'slug' => 'toa-nha-test-enhancement',
            'total_floors' => 10,
            'total_leasable_area_sqm' => 5000.00,
            'address' => '123 Đường Test',
            'district' => 'Quận 1',
            'ward' => 'Bến Nghé',
            'province' => 'Hồ Chí Minh',
            'grade' => 'B',
            'is_published' => true,
        ]);

        $this->tenant = Tenant::create([
            'tenant_code' => 'TNT-2026-TEST',
            'company_name' => 'Công ty TNHH Phần Mềm Alpha',
            'tax_code' => '0312999999',
            'business_license_private_path' => 'licenses/alpha_license.pdf',
            'representative_name' => 'Nguyễn Văn Giám Đốc',
            'representative_position' => 'Tổng Giám Đốc',
            'headquarter_address' => 'Tầng 5, Tòa nhà Test',
            'login_email' => 'login@alpha.vn',
            'billing_email' => 'ketoan@alpha.vn',
            'contact_phone' => '0901234567',
            'password' => 'secret123',
            'status' => 'ACTIVE',
        ]);

        $this->sale = Sale::create([
            'employee_code' => 'SALE-ENHANCE-01',
            'full_name' => 'Lê Văn Sales',
            'email' => 'sales.enhance@office.vn',
            'phone' => '0912345678',
            'password' => 'secret123',
            'commission_rate' => 1.5,
            'max_concurrent_leads' => 10,
            'status' => 'ACTIVE',
        ]);

        $this->admin = Admin::create([
            'employee_code' => 'ADM-ENHANCE-01',
            'full_name' => 'Trần Văn Admin',
            'email' => 'admin.enhance@office.vn',
            'phone' => '0922334455',
            'password' => 'secret123',
            'role' => 'SUPER_ADMIN',
            'status' => 'ACTIVE',
        ]);

        $this->pm = PropertyManager::create([
            'employee_code' => 'PM-ENHANCE-01',
            'full_name' => 'Hoàng Thị BQL',
            'email' => 'pm.enhance@office.vn',
            'phone' => '0933445566',
            'password' => 'secret123',
            'status' => 'ACTIVE',
        ]);
    }

    public function test_unit_can_be_set_to_reserved_status(): void
    {
        $unit = Unit::create([
            'building_id' => $this->building->id,
            'unit_code' => 'P.1001',
            'name' => 'Phòng 1001 - Góc View Công Viên',
            'floor_number' => 10,
            'area_sqm' => 120.50,
            'base_rental_price' => 500000,
            'handover_condition' => 'BASIC',
            'status' => UnitStatus::RESERVED,
        ]);

        $this->assertDatabaseHas('units', [
            'id' => $unit->id,
            'status' => 'RESERVED',
        ]);

        $this->assertTrue($unit->isReserved());
        $this->assertFalse($unit->isAvailable());

        $reservedUnits = Unit::reserved()->get();
        $this->assertTrue($reservedUnits->contains('id', $unit->id));
    }

    public function test_booking_contract_creation_with_reservation_fields(): void
    {
        $reservationExpires = now()->addDays(7);

        $contract = Contract::create([
            'contract_number' => 'BOOKING-2026-001',
            'building_id' => $this->building->id,
            'tenant_id' => $this->tenant->id,
            'created_by_sales_id' => $this->sale->id,
            'start_date' => now()->addDays(10)->toDateString(),
            'end_date' => now()->addYears(1)->toDateString(),
            'total_leased_area_sqm' => 120.50,
            'deposit_amount' => 100000000,
            'booking_deposit_amount' => 20000000,
            'reservation_expires_at' => $reservationExpires,
            'payment_cycle_months' => 3,
            'payment_due_day' => 5,
            'status' => ContractStatus::RESERVED,
        ]);

        $this->assertDatabaseHas('contracts', [
            'id' => $contract->id,
            'contract_number' => 'BOOKING-2026-001',
            'status' => 'RESERVED',
            'created_by_sales_id' => $this->sale->id,
        ]);

        $this->assertTrue($contract->status->isReserved());
        $this->assertEquals(20000000, (float) $contract->booking_deposit_amount);
        $this->assertEquals($this->sale->id, $contract->createdBySales->id);

        $reservedContracts = Contract::reserved()->get();
        $this->assertTrue($reservedContracts->contains('id', $contract->id));

        $activeOrReserved = Contract::activeOrReserved()->get();
        $this->assertTrue($activeOrReserved->contains('id', $contract->id));
    }

    public function test_contract_renewal_with_parent_contract_relation(): void
    {
        // 1. Hợp đồng chu kỳ 1 (gốc)
        $parentContract = Contract::create([
            'contract_number' => 'HD-2024-VCB01',
            'building_id' => $this->building->id,
            'tenant_id' => $this->tenant->id,
            'created_by_sales_id' => $this->sale->id,
            'signed_date' => '2024-01-01',
            'start_date' => '2024-01-01',
            'end_date' => '2026-01-01',
            'total_leased_area_sqm' => 200.00,
            'deposit_amount' => 150000000,
            'status' => ContractStatus::RENEWED,
        ]);

        // 2. Hợp đồng chu kỳ 2 (gia hạn mới) trỏ về parentContract
        $renewedContract = Contract::create([
            'contract_number' => 'HD-2026-VCB01-PL01',
            'parent_contract_id' => $parentContract->id,
            'building_id' => $this->building->id,
            'tenant_id' => $this->tenant->id,
            'created_by_sales_id' => $this->sale->id,
            'approved_by_pm_id' => $this->pm->id,
            'signed_date' => '2026-01-02',
            'start_date' => '2026-01-02',
            'end_date' => '2028-01-01',
            'total_leased_area_sqm' => 200.00,
            'deposit_amount' => 150000000,
            'status' => ContractStatus::ACTIVE,
        ]);

        $this->assertDatabaseHas('contracts', [
            'id' => $renewedContract->id,
            'parent_contract_id' => $parentContract->id,
            'status' => 'ACTIVE',
        ]);

        $this->assertEquals($parentContract->id, $renewedContract->parentContract->id);
        $this->assertTrue($parentContract->renewedContracts->contains('id', $renewedContract->id));
        $this->assertTrue($parentContract->status->isRenewed());
    }

    public function test_approver_and_terminator_polymorphic_actor_accessors(): void
    {
        // Ký duyệt bởi Admin
        $contractAdminApproved = Contract::create([
            'contract_number' => 'HD-SIGN-ADMIN',
            'building_id' => $this->building->id,
            'tenant_id' => $this->tenant->id,
            'approved_by_admin_id' => $this->admin->id,
            'start_date' => '2026-02-01',
            'end_date' => '2027-02-01',
            'total_leased_area_sqm' => 100.00,
            'deposit_amount' => 50000000,
            'status' => ContractStatus::ACTIVE,
        ]);

        $this->assertInstanceOf(Admin::class, $contractAdminApproved->approver);
        $this->assertEquals($this->admin->id, $contractAdminApproved->approver->id);

        // Ký duyệt bởi PM và Thanh lý bởi Admin với phân định hoàn/phạt cọc
        $contractPmApproved = Contract::create([
            'contract_number' => 'HD-SIGN-PM',
            'building_id' => $this->building->id,
            'tenant_id' => $this->tenant->id,
            'approved_by_pm_id' => $this->pm->id,
            'terminated_by_admin_id' => $this->admin->id,
            'start_date' => '2026-03-01',
            'end_date' => '2027-03-01',
            'total_leased_area_sqm' => 100.00,
            'deposit_amount' => 60000000,
            'refund_deposit_amount' => 40000000,
            'penalty_deposit_amount' => 20000000,
            'terminated_at' => '2026-08-01',
            'termination_note' => 'Khách đơn phương chấm dứt sớm, phạt 20 triệu theo phụ lục',
            'status' => ContractStatus::TERMINATED,
        ]);

        $this->assertInstanceOf(PropertyManager::class, $contractPmApproved->approver);
        $this->assertEquals($this->pm->id, $contractPmApproved->approver->id);

        $this->assertInstanceOf(Admin::class, $contractPmApproved->terminator);
        $this->assertEquals($this->admin->id, $contractPmApproved->terminator->id);

        $this->assertEquals(40000000, (float) $contractPmApproved->refund_deposit_amount);
        $this->assertEquals(20000000, (float) $contractPmApproved->penalty_deposit_amount);

        // Thanh lý bởi PM
        $contractPmTerminated = Contract::create([
            'contract_number' => 'HD-TERM-PM',
            'building_id' => $this->building->id,
            'tenant_id' => $this->tenant->id,
            'terminated_by_pm_id' => $this->pm->id,
            'start_date' => '2026-04-01',
            'end_date' => '2027-04-01',
            'total_leased_area_sqm' => 80.00,
            'deposit_amount' => 40000000,
            'status' => ContractStatus::TERMINATED,
        ]);

        $this->assertInstanceOf(PropertyManager::class, $contractPmTerminated->terminator);
        $this->assertEquals($this->pm->id, $contractPmTerminated->terminator->id);
    }
}
