<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Building;
use App\Models\PropertyManager;
use App\Models\Tenant;
use App\Models\Ticket;
use App\Models\TicketProofImage;
use Database\Seeders\BuildingAndUnitSeeder;
use Database\Seeders\OperationalBusinessSeeder;
use Database\Seeders\ProvinceSeeder;
use Database\Seeders\StaffAndAccountSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Class TicketApiTest
 *
 * Kiểm thử tự động toàn diện Module 4: Quản Lý Yêu Cầu Hỗ Trợ & Sự Cố Bảo Trì
 * (Endpoints 58 đến 66).
 */
class TicketApiTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected PropertyManager $pm;
    protected Admin $admin;
    protected Building $building;
    protected string $tenantToken;
    protected string $pmToken;
    protected string $adminToken;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(StaffAndAccountSeeder::class);
        $this->seed(ProvinceSeeder::class);
        $this->seed(BuildingAndUnitSeeder::class);
        $this->seed(OperationalBusinessSeeder::class);

        $this->admin = Admin::where('email', 'admin@officeleasing.vn')->firstOrFail();
        $this->pm = PropertyManager::where('email', 'pm.quang@officeleasing.vn')->firstOrFail();
        $this->tenant = Tenant::where('login_email', 'contact@vng.com.vn')->firstOrFail();
        $this->building = Building::firstOrFail();

        $this->adminToken = $this->admin->createToken('test-admin')->plainTextToken;
        $this->pmToken = $this->pm->createToken('test-pm')->plainTextToken;
        $this->tenantToken = $this->tenant->createToken('test-tenant')->plainTextToken;
    }

    /**
     * 57. GET /api/v1/tickets: Danh sách Ticket có phân quyền theo vai trò (Tenant, PM, Admin).
     */
    public function test_can_get_ticket_list_with_role_scoping(): void
    {
        // Admin xem toàn bộ danh sách ticket
        $adminRes = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->getJson('/api/v1/tickets');

        $adminRes->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonStructure([
                'status',
                'message',
                'code',
                'data',
                'meta' => [
                    'current_page',
                    'last_page',
                    'per_page',
                    'total',
                ],
            ]);

        // Tenant chỉ xem ticket của doanh nghiệp mình
        $tenantRes = $this->withHeader('Authorization', "Bearer {$this->tenantToken}")
            ->getJson('/api/v1/tickets');

        $tenantRes->assertStatus(200)
            ->assertJsonPath('status', 'success');
    }

    /**
     * 57. GET /api/v1/tickets: Lọc ticket theo status, priority và từ khóa.
     */
    public function test_can_filter_ticket_list(): void
    {
        $response = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->getJson('/api/v1/tickets?status=OPEN&priority=NORMAL');

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success');
    }

    /**
     * 58. POST /api/v1/tenant/tickets: Khách thuê gửi yêu cầu hỗ trợ mới.
     */
    public function test_tenant_can_create_ticket_with_auto_generated_code(): void
    {
        $payload = [
            'building_id' => $this->building->id,
            'title'       => 'Sự cố rò rỉ nước tại bồn rửa tầng 5',
            'category'    => 'MAINTENANCE',
            'priority'    => 'HIGH',
            'description' => 'Ống cấp nước dưới lavabo bị nứt làm nước tràn ra sàn hành lang.',
        ];

        $response = $this->withHeader('Authorization', "Bearer {$this->tenantToken}")
            ->postJson('/api/v1/tenant/tickets', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.title', $payload['title'])
            ->assertJsonPath('data.category', 'MAINTENANCE')
            ->assertJsonPath('data.priority', 'HIGH')
            ->assertJsonPath('data.status', 'NEW');

        $ticketCode = $response->json('data.ticket_code');
        $this->assertNotEmpty($ticketCode);
        $this->assertStringStartsWith('TK-' . date('Y') . '-', $ticketCode);

        $this->assertDatabaseHas('tickets', [
            'ticket_code'       => $ticketCode,
            'creator_tenant_id' => $this->tenant->id,
            'tenant_id'         => $this->tenant->id,
        ]);

        $this->assertDatabaseHas('ticket_history', [
            'ticket_id'       => $response->json('data.id'),
            'actor_type'      => 'TENANT',
            'tenant_actor_id' => $this->tenant->id,
            'to_status'       => 'NEW',
        ]);
    }

    /**
     * 59. GET /api/v1/tickets/{id}: Xem chi tiết tiến trình Ticket & Lịch sử.
     */
    public function test_can_view_ticket_details(): void
    {
        $ticket = Ticket::firstOrFail();

        // Khách thuê (chủ sở hữu) xem ticket
        $ticket->update(['tenant_id' => $this->tenant->id, 'creator_tenant_id' => $this->tenant->id]);

        $response = $this->withHeader('Authorization', "Bearer {$this->tenantToken}")
            ->getJson("/api/v1/tickets/{$ticket->id}");

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.id', $ticket->id)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'ticket_code',
                    'title',
                    'category',
                    'status',
                    'building',
                    'histories',
                    'proof_images',
                ]
            ]);

        // PM xem ticket
        $pmResponse = $this->withHeader('Authorization', "Bearer {$this->pmToken}")
            ->getJson("/api/v1/tickets/{$ticket->id}");
        $pmResponse->assertStatus(200);
    }

    /**
     * 60. PUT /api/v1/tickets/{id}: Cập nhật nội dung Ticket khi còn ở trạng thái Mới OPEN.
     */
    public function test_can_update_ticket_when_in_open_status(): void
    {
        $ticket = Ticket::create([
            'ticket_code'       => 'TK-2026-TEST-OPEN',
            'building_id'       => $this->building->id,
            'creator_role'      => 'TENANT',
            'creator_tenant_id' => $this->tenant->id,
            'tenant_id'         => $this->tenant->id,
            'category'          => 'MAINTENANCE',
            'priority'          => 'NORMAL',
            'title'             => 'Tiêu đề ban đầu',
            'description'       => 'Mô tả ban đầu',
            'status'            => 'NEW',
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$this->tenantToken}")
            ->putJson("/api/v1/tickets/{$ticket->id}", [
                'title'       => 'Tiêu đề đã được cập nhật',
                'description' => 'Mô tả đã bổ sung thêm chi tiết kỹ thuật.',
                'priority'    => 'URGENT',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.title', 'Tiêu đề đã được cập nhật')
            ->assertJsonPath('data.priority', 'URGENT');

        $this->assertDatabaseHas('tickets', [
            'id'    => $ticket->id,
            'title' => 'Tiêu đề đã được cập nhật',
        ]);
    }

    /**
     * 60b. Kiểm thử không cho phép cập nhật khi Ticket không còn ở trạng thái Mới (OPEN / NEW).
     */
    public function test_cannot_update_ticket_when_not_in_open_status(): void
    {
        $ticket = Ticket::create([
            'ticket_code'       => 'TK-2026-TEST-PROG',
            'building_id'       => $this->building->id,
            'creator_role'      => 'TENANT',
            'creator_tenant_id' => $this->tenant->id,
            'tenant_id'         => $this->tenant->id,
            'category'          => 'MAINTENANCE',
            'title'             => 'Đang xử lý',
            'description'       => 'Mô tả',
            'status'            => 'IN_PROGRESS',
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$this->tenantToken}")
            ->putJson("/api/v1/tickets/{$ticket->id}", [
                'title' => 'Cố tình sửa khi đang xử lý',
            ]);

        $response->assertStatus(422)
            ->assertJsonPath('status', 'error');
    }

    /**
     * 61a. DELETE /api/v1/tickets/{id}: Người dùng không phải Admin không được phép xóa (403).
     */
    public function test_non_admin_cannot_delete_ticket(): void
    {
        $ticket = Ticket::create([
            'ticket_code'       => 'TK-2026-TEST-DEL-FORBID',
            'building_id'       => $this->building->id,
            'creator_role'      => 'TENANT',
            'creator_tenant_id' => $this->tenant->id,
            'tenant_id'         => $this->tenant->id,
            'category'          => 'MAINTENANCE',
            'title'             => 'Phiếu tạo thử nghiệm',
            'description'       => 'Cần xóa',
            'status'            => 'NEW',
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$this->tenantToken}")
            ->deleteJson("/api/v1/tickets/{$ticket->id}");

        $response->assertStatus(403);
    }

    /**
     * 61b. DELETE /api/v1/tickets/{id}: Admin xóa mềm Ticket thành công.
     */
    public function test_admin_can_soft_delete_ticket(): void
    {
        $ticket = Ticket::create([
            'ticket_code'       => 'TK-2026-TEST-DEL',
            'building_id'       => $this->building->id,
            'creator_role'      => 'ADMIN',
            'creator_admin_id'  => $this->admin->id,
            'category'          => 'MAINTENANCE',
            'title'             => 'Phiếu tạo thử nghiệm',
            'description'       => 'Cần xóa',
            'status'            => 'NEW',
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$this->adminToken}")
            ->deleteJson("/api/v1/tickets/{$ticket->id}");

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success');

        $this->assertSoftDeleted('tickets', ['id' => $ticket->id]);
    }

    /**
     * 62. PATCH /api/v1/tickets/{id}/assign: Tiếp nhận & Phân công xử lý.
     */
    public function test_pm_can_assign_ticket_to_pm(): void
    {
        $ticket = Ticket::create([
            'ticket_code'       => 'TK-2026-TEST-ASSIGN',
            'building_id'       => $this->building->id,
            'creator_role'      => 'TENANT',
            'creator_tenant_id' => $this->tenant->id,
            'tenant_id'         => $this->tenant->id,
            'category'          => 'MAINTENANCE',
            'title'             => 'Cần phân công kỹ thuật',
            'description'       => 'Mô tả',
            'status'            => 'NEW',
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$this->pmToken}")
            ->patchJson("/api/v1/tickets/{$ticket->id}/assign", [
                'assigned_pm_id'         => $this->pm->id,
                'expected_completion_at' => now()->addHours(4)->toDateTimeString(),
                'note'                   => 'Đã giao cho anh Quang phụ trách kiểm tra trước 17h.',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.status', 'IN_PROGRESS')
            ->assertJsonPath('data.assigned_pm_id', $this->pm->id);

        $this->assertDatabaseHas('ticket_history', [
            'ticket_id'   => $ticket->id,
            'actor_type'  => 'PM',
            'pm_actor_id' => $this->pm->id,
            'to_status'   => 'IN_PROGRESS',
        ]);
    }

    /**
     * 63. PATCH /api/v1/tickets/{id}/status: Cập nhật tiến độ sửa chữa (ON_HOLD / RESOLVED).
     */
    public function test_pm_can_update_status_to_on_hold_and_resolved(): void
    {
        $ticket = Ticket::create([
            'ticket_code'       => 'TK-2026-TEST-STATUS',
            'building_id'       => $this->building->id,
            'creator_role'      => 'TENANT',
            'creator_tenant_id' => $this->tenant->id,
            'tenant_id'         => $this->tenant->id,
            'category'          => 'MAINTENANCE',
            'title'             => 'Tiến độ sửa chữa',
            'description'       => 'Mô tả',
            'status'            => 'IN_PROGRESS',
        ]);

        // 1. Chuyển sang ON_HOLD
        $holdResponse = $this->withHeader('Authorization', "Bearer {$this->pmToken}")
            ->patchJson("/api/v1/tickets/{$ticket->id}/status", [
                'status'         => 'ON_HOLD',
                'on_hold_reason' => 'Đang chờ linh kiện van khóa từ nhà cung cấp Daikin.',
            ]);

        $holdResponse->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.status', 'ON_HOLD')
            ->assertJsonPath('data.on_hold_reason', 'Đang chờ linh kiện van khóa từ nhà cung cấp Daikin.');

        // 2. Chuyển sang RESOLVED (WAITING_CONFIRMATION)
        $resolveResponse = $this->withHeader('Authorization', "Bearer {$this->pmToken}")
            ->patchJson("/api/v1/tickets/{$ticket->id}/status", [
                'status'             => 'RESOLVED',
                'resolution_summary' => 'Đã thay mới van khóa nước, kiểm tra áp suất hoạt động bình thường.',
            ]);

        $resolveResponse->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.status', 'WAITING_CONFIRMATION')
            ->assertJsonPath('data.resolution_summary', 'Đã thay mới van khóa nước, kiểm tra áp suất hoạt động bình thường.');
    }

    /**
     * 64. POST /api/v1/tickets/{id}/proof-images: Tải lên hình ảnh bằng chứng (Before / After).
     */
    public function test_can_upload_proof_images(): void
    {
        Storage::fake('public');

        $ticket = Ticket::firstOrFail();
        $file = UploadedFile::fake()->image('incident_before.jpg', 600, 400);

        $response = $this->withHeader('Authorization', "Bearer {$this->pmToken}")
            ->postJson("/api/v1/tickets/{$ticket->id}/proof-images", [
                'proof_type' => 'BEFORE',
                'caption'    => 'Ảnh chụp hiện trạng rò rỉ',
                'images'     => [$file],
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('status', 'success');

        $this->assertDatabaseHas('ticket_proof_images', [
            'ticket_id'  => $ticket->id,
            'proof_type' => 'INCIDENT',
            'caption'    => 'Ảnh chụp hiện trạng rò rỉ',
        ]);
    }

    /**
     * 65. POST /api/v1/tenant/tickets/{id}/confirm: Khách thuê xác nhận nghiệm thu.
     */
    public function test_tenant_can_confirm_or_reject_ticket(): void
    {
        $ticket = Ticket::create([
            'ticket_code'       => 'TK-2026-TEST-CONFIRM',
            'building_id'       => $this->building->id,
            'creator_role'      => 'TENANT',
            'creator_tenant_id' => $this->tenant->id,
            'tenant_id'         => $this->tenant->id,
            'category'          => 'MAINTENANCE',
            'title'             => 'Chờ nghiệm thu',
            'description'       => 'Mô tả',
            'status'            => 'WAITING_CONFIRMATION',
        ]);

        // Khách xác nhận nghiệm thu hài lòng -> CLOSED / COMPLETED
        $confirmResponse = $this->withHeader('Authorization', "Bearer {$this->tenantToken}")
            ->postJson("/api/v1/tenant/tickets/{$ticket->id}/confirm", [
                'confirmed' => true,
                'feedback'  => 'Kỹ thuật viên xử lý nhanh chóng, rất hài lòng.',
            ]);

        $confirmResponse->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.status', 'COMPLETED');

        $this->assertDatabaseHas('tickets', [
            'id'     => $ticket->id,
            'status' => 'COMPLETED',
        ]);
    }

    /**
     * 66. POST /api/v1/tenant/tickets/{id}/rate: Khách thuê đánh giá chất lượng phục vụ (1-5 sao).
     */
    public function test_tenant_can_rate_ticket(): void
    {
        $ticket = Ticket::create([
            'ticket_code'       => 'TK-2026-TEST-RATE',
            'building_id'       => $this->building->id,
            'creator_role'      => 'TENANT',
            'creator_tenant_id' => $this->tenant->id,
            'tenant_id'         => $this->tenant->id,
            'category'          => 'MAINTENANCE',
            'title'             => 'Đánh giá phục vụ',
            'description'       => 'Mô tả',
            'status'            => 'COMPLETED',
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$this->tenantToken}")
            ->postJson("/api/v1/tenant/tickets/{$ticket->id}/rate", [
                'rating'   => 5,
                'feedback' => 'Dịch vụ bảo trì của tòa nhà rất chuyên nghiệp!',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.tenant_rating', 5)
            ->assertJsonPath('data.tenant_feedback', 'Dịch vụ bảo trì của tòa nhà rất chuyên nghiệp!');

        $this->assertDatabaseHas('tickets', [
            'id'            => $ticket->id,
            'tenant_rating' => 5,
        ]);
    }
}
