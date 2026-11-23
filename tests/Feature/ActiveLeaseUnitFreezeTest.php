<?php

namespace Tests\Feature;

use App\Enums\ContractStatus;
use App\Enums\UnitStatus;
use App\Exceptions\BusinessException;
use App\Models\Admin;
use App\Models\Building;
use App\Models\Contract;
use App\Models\ContractUnit;
use App\Models\Province;
use App\Models\Sale;
use App\Models\Tenant;
use App\Models\Unit;
use App\Services\UnitService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActiveLeaseUnitFreezeTest extends TestCase
{
    use RefreshDatabase;

    protected Admin $admin;
    protected Building $building;
    protected Tenant $tenant;
    protected Sale $sale;
    protected string $adminToken;

    protected function setUp(): void
    {
        parent::setUp();

        $province = Province::create([
            'code' => 79,
            'name' => 'Thành phố Hồ Chí Minh',
            'slug' => 'thanh-pho-ho-chi-minh',
            'division_type' => 'thành phố trung ương',
            'codename' => 'ho_chi_minh',
            'phone_code' => 28,
        ]);

        $this->admin = Admin::create([
            'employee_code' => 'ADM-FREEZE-01',
            'full_name' => 'Admin Freeze Test',
            'email' => 'admin.freeze@officeleasing.vn',
            'phone' => '0988776655',
            'password' => 'secret123',
            'role' => 'admin',
            'is_super_admin' => true,
            'status' => 'ACTIVE',
        ]);

        $this->adminToken = $this->admin->createToken('admin-token', ['admin'])->plainTextToken;

        $this->building = Building::create([
            'building_code' => 'BLD-FREEZE-01',
            'name' => 'Tòa Nhà Freeze Test',
            'slug' => 'toa-nha-freeze-test',
            'province_code' => $province->code,
            'province' => 'Hồ Chí Minh',
            'district' => 'Quận 1',
            'ward' => 'Bến Nghé',
            'address' => '123 Lê Lợi',
            'grade' => 'A',
            'total_floors' => 15,
            'total_leasable_area_sqm' => 3000.0,
            'is_published' => true,
        ]);

        $this->tenant = Tenant::create([
            'tenant_code' => 'TNT-FREEZE-01',
            'company_name' => 'Công ty Cổ phần Alpha Freeze',
            'tax_code' => '0312888999',
            'business_license_private_path' => 'licenses/alpha_freeze.pdf',
            'headquarter_address' => 'Tầng 1, Tòa nhà Test',
            'representative_name' => 'Nguyễn Văn Giám Đốc',
            'representative_position' => 'Giám đốc',
            'login_email' => 'ceo@alphafreeze.vn',
            'billing_email' => 'ketoan@alphafreeze.vn',
            'contact_phone' => '0901234567',
            'password' => 'secret123',
            'status' => 'ACTIVE',
        ]);

        $this->sale = Sale::create([
            'employee_code' => 'SALE-FREEZE-01',
            'full_name' => 'Lê Văn Sales',
            'email' => 'sales.freeze@officeleasing.vn',
            'phone' => '0912345678',
            'password' => 'secret123',
            'commission_rate' => 1.5,
            'max_concurrent_leads' => 10,
            'status' => 'ACTIVE',
        ]);
    }

    /**
     * Helper tạo mặt bằng.
     */
    protected function createUnit(string $unitCode = 'P.801', float $area = 100.0, float $price = 500000.0): Unit
    {
        return Unit::create([
            'building_id' => $this->building->id,
            'unit_code' => $unitCode,
            'name' => "Phòng {$unitCode}",
            'floor_number' => 8,
            'area_sqm' => $area,
            'base_rental_price' => $price,
            'status' => UnitStatus::AVAILABLE,
        ]);
    }

    /**
     * Helper tạo hợp đồng và gắn mặt bằng.
     */
    protected function createContractWithUnit(Unit $unit, ContractStatus $status = ContractStatus::ACTIVE): Contract
    {
        $contract = Contract::create([
            'contract_number' => 'HD-' . uniqid(),
            'building_id' => $this->building->id,
            'tenant_id' => $this->tenant->id,
            'created_by_sales_id' => $this->sale->id,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addYear()->toDateString(),
            'total_leased_area_sqm' => $unit->area_sqm,
            'deposit_amount' => $unit->area_sqm * $unit->base_rental_price * 3,
            'status' => $status,
        ]);

        ContractUnit::create([
            'building_id' => $this->building->id,
            'contract_id' => $contract->id,
            'unit_id' => $unit->id,
            'leased_area_sqm' => $unit->area_sqm,
            'agreed_rental_price_per_sqm' => $unit->base_rental_price,
            'agreed_service_fee_per_sqm' => 50000,
        ]);

        return $contract;
    }

    /**
     * 1. Test UnitService chặn toàn bộ chỉnh sửa khi có hợp đồng ACTIVE.
     */
    public function test_service_completely_blocks_updating_unit_with_active_contract(): void
    {
        $unit = $this->createUnit('P.801');
        $this->createContractWithUnit($unit, ContractStatus::ACTIVE);

        $unitService = app(UnitService::class);

        $this->expectException(BusinessException::class);
        $this->expectExceptionMessage("Mặt bằng [P.801] đang có hợp đồng thuê còn hiệu lực. Nghiêm cấm chỉnh sửa thông tin.");

        // Thử sửa tên hoặc giá hoặc diện tích -> Đều bị ném exception
        $unitService->updateUnit($unit, [
            'name' => 'Tên mới phòng 801',
        ]);
    }

    /**
     * 2. Test API PUT /api/v1/admin/units/{id} trả về HTTP 422 khi sửa mặt bằng đang có hợp đồng.
     */
    public function test_api_put_returns_422_when_updating_unit_under_active_lease(): void
    {
        $unit = $this->createUnit('P.802');
        $this->createContractWithUnit($unit, ContractStatus::ACTIVE);

        // Thử sửa đơn giá
        $response = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->putJson("/api/v1/admin/units/{$unit->id}", [
                'base_rental_price' => 750000,
            ]);

        $response->assertStatus(422)
            ->assertJsonPath('status', 'error')
            ->assertJsonPath('message', 'Mặt bằng [P.802] đang có hợp đồng thuê còn hiệu lực. Nghiêm cấm chỉnh sửa thông tin.');

        // Đảm bảo dữ liệu trong DB không bị thay đổi
        $unit->refresh();
        $this->assertEquals(500000.0, (float) $unit->base_rental_price);
    }

    /**
     * 3. Test API PATCH /api/v1/admin/units/{id}/status chặn đổi trạng thái khi có hợp đồng.
     */
    public function test_api_patch_status_returns_422_when_unit_has_active_lease(): void
    {
        $unit = $this->createUnit('P.803');
        $this->createContractWithUnit($unit, ContractStatus::ACTIVE);

        $response = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->patchJson("/api/v1/admin/units/{$unit->id}/status", [
                'status' => 'AVAILABLE',
            ]);

        $response->assertStatus(422)
            ->assertJsonPath('status', 'error')
            ->assertJsonPath('message', 'Mặt bằng [P.803] đang có hợp đồng thuê còn hiệu lực. Nghiêm cấm thay đổi trạng thái khi chưa hoàn tất thanh lý hợp đồng.');
    }

    /**
     * 4. Test API DELETE /api/v1/admin/units/{id} chặn xóa khi có hợp đồng.
     */
    public function test_api_delete_returns_422_when_unit_has_active_lease(): void
    {
        $unit = $this->createUnit('P.804');
        $this->createContractWithUnit($unit, ContractStatus::ACTIVE);

        $response = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->deleteJson("/api/v1/admin/units/{$unit->id}");

        $response->assertStatus(422)
            ->assertJsonPath('status', 'error')
            ->assertJsonPath('message', 'Không thể xóa mặt bằng [P.804] vì đang gắn với hợp đồng thuê còn hiệu lực.');
    }

    /**
     * 5. Test khi hợp đồng đã TERMINATED hoặc EXPIRED, mặt bằng được phép cập nhật bình thường.
     */
    public function test_can_update_unit_when_contract_is_terminated(): void
    {
        $unit = $this->createUnit('P.805', 100.0, 500000.0);
        $this->createContractWithUnit($unit, ContractStatus::TERMINATED);

        $response = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->putJson("/api/v1/admin/units/{$unit->id}", [
                'name' => 'Phòng 805 Đã Thanh Lý',
                'base_rental_price' => 600000,
                'area_sqm' => 105.0,
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'Phòng 805 Đã Thanh Lý')
            ->assertJsonPath('data.base_rental_price', '600000.00');

        $unit->refresh();
        $this->assertEquals('Phòng 805 Đã Thanh Lý', $unit->name);
        $this->assertEquals(600000.0, (float) $unit->base_rental_price);
    }

    /**
     * 6. Test mặt bằng chưa từng có hợp đồng thì cập nhật thoải mái.
     */
    public function test_can_update_unit_without_contract(): void
    {
        $unit = $this->createUnit('P.806', 80.0, 450000.0);

        $response = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->putJson("/api/v1/admin/units/{$unit->id}", [
                'name' => 'Phòng 806 Mới Hoàn Thiện',
                'base_rental_price' => 480000,
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'Phòng 806 Mới Hoàn Thiện');
    }
}
