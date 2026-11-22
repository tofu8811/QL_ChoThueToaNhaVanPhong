<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Building;
use App\Models\Province;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BuildingCodeGenerationTest extends TestCase
{
    use RefreshDatabase;

    protected Province $province;
    protected Admin $admin;
    protected string $adminToken;

    protected function setUp(): void
    {
        parent::setUp();

        $this->province = Province::create([
            'code' => 79,
            'name' => 'TP Hồ Chí Minh',
            'slug' => 'tp-ho-chi-minh',
            'code_name' => 'thanh_pho_ho_chi_minh',
        ]);

        $this->admin = Admin::create([
            'employee_code' => 'ADM-GEN-01',
            'full_name' => 'Admin Test Code Generator',
            'email' => 'admin.codegen@officeleasing.vn',
            'phone' => '0988776655',
            'password' => 'secret123',
            'is_super_admin' => true,
            'status' => 'ACTIVE',
        ]);

        $this->adminToken = $this->admin->createToken('admin-token', ['admin'])->plainTextToken;
    }

    /**
     * Helper tạo payload hợp lệ khi khởi tạo tòa nhà.
     */
    protected function getBasePayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Tòa Nhà Thử Nghiệm ' . uniqid(),
            'address' => '123 Điện Biên Phủ',
            'ward' => 'Phường 25',
            'district' => 'Bình Thạnh',
            'province' => 'TP Hồ Chí Minh',
            'province_code' => $this->province->code,
            'total_floors' => 20,
            'total_leasable_area_sqm' => 15000,
            'electricity_unit_price' => 4000,
            'water_unit_price' => 20000,
            'default_service_fee_per_sqm' => 100000,
            'min_rent_price_per_sqm' => 500000,
            'max_rent_price_per_sqm' => 800000,
            'grade' => 'B',
            'is_published' => true,
        ], $overrides);
    }

    /**
     * 1. Khi để trống building_code, hệ thống tự động sinh mã theo định dạng BLD-{YYYY}-{SEQ}.
     */
    public function test_auto_generates_building_code_with_correct_format_when_omitted(): void
    {
        $payload = $this->getBasePayload();
        unset($payload['building_code']);

        $res = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->postJson('/api/v1/admin/buildings', $payload);

        $res->assertStatus(201)
            ->assertJsonPath('status', 'success');

        $year = date('Y');
        $code = $res->json('data.building_code');
        $this->assertMatchesRegularExpression("/^BLD-{$year}-\\d{4}$/", $code);
        $this->assertEquals("BLD-{$year}-0001", $code);
    }

    /**
     * 2. Tự sinh mã tăng dần liên tiếp cho các tòa nhà tiếp theo.
     */
    public function test_sequential_auto_generation_increments_sequence(): void
    {
        $year = date('Y');

        $res1 = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->postJson('/api/v1/admin/buildings', $this->getBasePayload(['name' => 'Tòa 1']));
        $res1->assertStatus(201);
        $this->assertEquals("BLD-{$year}-0001", $res1->json('data.building_code'));

        $res2 = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->postJson('/api/v1/admin/buildings', $this->getBasePayload(['name' => 'Tòa 2']));
        $res2->assertStatus(201);
        $this->assertEquals("BLD-{$year}-0002", $res2->json('data.building_code'));
    }

    /**
     * 3. Tự sinh mã không bao giờ trùng với tòa nhà đã bị xóa mềm (Soft-deleted).
     */
    public function test_auto_generation_never_collides_with_soft_deleted_buildings(): void
    {
        $year = date('Y');

        // Tạo 2 tòa nhà: BLD-2026-0001 và BLD-2026-0002
        $res1 = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->postJson('/api/v1/admin/buildings', $this->getBasePayload(['name' => 'Tòa 1']));
        $res2 = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->postJson('/api/v1/admin/buildings', $this->getBasePayload(['name' => 'Tòa 2']));

        $building2Id = $res2->json('data.id');

        // Xóa mềm tòa nhà thứ 2 (BLD-2026-0002)
        $delRes = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->deleteJson("/api/v1/admin/buildings/{$building2Id}");
        $delRes->assertStatus(200);

        // Tạo tòa nhà thứ 3 -> Mã sinh ra bắt buộc phải là BLD-2026-0003, KHÔNG được tái sử dụng BLD-2026-0002
        $res3 = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->postJson('/api/v1/admin/buildings', $this->getBasePayload(['name' => 'Tòa 3']));
        $res3->assertStatus(201);
        $this->assertEquals("BLD-{$year}-0003", $res3->json('data.building_code'));
    }

    /**
     * 4. Cho phép người dùng tự nhập mã tùy ý (VD: BLD-LM81) và lưu trữ chính xác.
     */
    public function test_manual_building_code_is_persisted(): void
    {
        $payload = $this->getBasePayload([
            'building_code' => 'BLD-LM81',
            'name' => 'Landmark 81 Tower',
        ]);

        $res = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->postJson('/api/v1/admin/buildings', $payload);

        $res->assertStatus(201);
        $this->assertEquals('BLD-LM81', $res->json('data.building_code'));
    }

    /**
     * 5. Chặn trùng mã khi người dùng tự nhập mã trùng với tòa nhà đang hoạt động.
     */
    public function test_manual_building_code_cannot_duplicate_active_building(): void
    {
        // Tạo tòa nhà đầu tiên với mã BLD-EXISTING
        $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->postJson('/api/v1/admin/buildings', $this->getBasePayload([
                'building_code' => 'BLD-EXISTING',
            ]))->assertStatus(201);

        // Thử tạo tòa thứ hai với cùng mã
        $res = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->postJson('/api/v1/admin/buildings', $this->getBasePayload([
                'building_code' => 'BLD-EXISTING',
            ]));

        $res->assertStatus(422);
    }

    /**
     * 6. Chặn trùng mã khi người dùng tự nhập mã trùng với tòa nhà đã bị xóa mềm.
     */
    public function test_manual_building_code_cannot_duplicate_soft_deleted_building(): void
    {
        // Tạo tòa nhà với mã BLD-SOFT-DEL
        $resCreate = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->postJson('/api/v1/admin/buildings', $this->getBasePayload([
                'building_code' => 'BLD-SOFT-DEL',
            ]));
        $resCreate->assertStatus(201);
        $id = $resCreate->json('data.id');

        // Xóa mềm tòa nhà này
        $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->deleteJson("/api/v1/admin/buildings/{$id}")
            ->assertStatus(200);

        // Xác nhận bản ghi đã bị xóa mềm trong DB
        $this->assertSoftDeleted('buildings', ['id' => $id]);

        // Thử tạo tòa nhà mới tự nhập lại mã BLD-SOFT-DEL -> Phải chặn 422
        $resDuplicate = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->postJson('/api/v1/admin/buildings', $this->getBasePayload([
                'building_code' => 'BLD-SOFT-DEL',
            ]));

        $resDuplicate->assertStatus(422);
    }

    /**
     * 7. Cập nhật mã tòa nhà sang mã của một tòa nhà khác đã bị xóa mềm -> Phải chặn 422.
     */
    public function test_updating_building_code_cannot_duplicate_soft_deleted_building(): void
    {
        // 1. Tạo tòa nhà A
        $resA = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->postJson('/api/v1/admin/buildings', $this->getBasePayload([
                'building_code' => 'BLD-AAA',
                'name' => 'Tòa Nhà A',
            ]));
        $resA->assertStatus(201);
        $idA = $resA->json('data.id');

        // 2. Tạo tòa nhà B
        $resB = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->postJson('/api/v1/admin/buildings', $this->getBasePayload([
                'building_code' => 'BLD-BBB',
                'name' => 'Tòa Nhà B',
            ]));
        $resB->assertStatus(201);
        $idB = $resB->json('data.id');

        // 3. Xóa mềm tòa nhà B
        $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->deleteJson("/api/v1/admin/buildings/{$idB}")
            ->assertStatus(200);

        // 4. Cố gắng cập nhật mã của tòa nhà A thành 'BLD-BBB' -> Phải bị chặn 422
        $resUpdate = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->putJson("/api/v1/admin/buildings/{$idA}", [
                'building_code' => 'BLD-BBB',
            ]);

        $resUpdate->assertStatus(422);
    }

    /**
     * 8. API next-code trả về HTTP 200 và mã kế tiếp chính xác cho Admin/PM.
     */
    public function test_next_building_code_api_returns_success_and_correct_format(): void
    {
        $year = date('Y');

        $res = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->getJson('/api/v1/admin/buildings/next-code');

        $res->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.next_code', "BLD-{$year}-0001");
    }

    /**
     * 9. API next-code chặn người dùng chưa xác thực (401) hoặc vai trò không hợp lệ (403).
     */
    public function test_next_building_code_api_rejects_unauthorized_access(): void
    {
        // 9.1 Chưa đăng nhập -> 401
        $this->getJson('/api/v1/admin/buildings/next-code')->assertStatus(401);

        // 9.2 Đăng nhập với vai trò Sales -> 403 (chỉ Admin, PM được phép)
        $sale = \App\Models\Sale::create([
            'employee_code' => 'SAL-TEST-01',
            'full_name' => 'Nhân Viên Sale Test',
            'email' => 'sale.test@officeleasing.vn',
            'password' => 'secret123',
            'status' => 'ACTIVE',
        ]);
        $saleToken = $sale->createToken('sale-token', ['sales'])->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$saleToken}")
            ->getJson('/api/v1/admin/buildings/next-code')
            ->assertStatus(403);
    }

    /**
     * 10. Tạo tòa nhà thành công khi truyền province_code, district và address từ form Cascading dropdown.
     */
    public function test_store_building_with_cascading_address_fields_and_prepare_for_validation(): void
    {
        $payload = [
            'name' => 'Tòa Nhà Diamond Plaza',
            'province_code' => 79,
            'district' => 'Quận 1',
            'address' => '34 Lê Duẩn',
            'grade' => 'A',
            'total_floors' => 22,
            'basements' => 2,
            'total_net_area' => 25000,
            'min_rent_price_per_sqm' => 900000,
            'default_service_fee_per_sqm' => 150000,
            'electricity_fee_desc' => 'Đồng hồ riêng',
            'water_fee_desc' => 'Miễn phí',
            'is_published' => true,
        ];

        $res = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->postJson('/api/v1/admin/buildings', $payload);

        $res->assertStatus(201)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.province_code', 79)
            ->assertJsonPath('data.province', 'TP Hồ Chí Minh')
            ->assertJsonPath('data.district', 'Quận 1')
            ->assertJsonPath('data.address', '34 Lê Duẩn')
            ->assertJsonPath('data.total_leasable_area_sqm', '25000.00');

        $this->assertDatabaseHas('buildings', [
            'name' => 'Tòa Nhà Diamond Plaza',
            'province_code' => 79,
            'district' => 'Quận 1',
            'address' => '34 Lê Duẩn',
        ]);
    }
}
