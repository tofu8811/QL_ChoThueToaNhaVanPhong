<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\V1\Ticket\AssignTicketRequest;
use App\Http\Requests\V1\Ticket\ConfirmTicketRequest;
use App\Http\Requests\V1\Ticket\RateTicketRequest;
use App\Http\Requests\V1\Ticket\StoreTenantTicketRequest;
use App\Http\Requests\V1\Ticket\UpdateTicketRequest;
use App\Http\Requests\V1\Ticket\UpdateTicketStatusRequest;
use App\Http\Requests\V1\Ticket\UploadProofImagesRequest;
use App\Models\Tenant;
use App\Services\TicketService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Controller xử lý Quản lý Yêu cầu Hỗ trợ & Sự cố Bảo trì (Tickets & Maintenance).
 * Module 4 (Endpoints 58 - 66).
 */
class TicketApiController extends BaseApiController
{
    public function __construct(
        protected TicketService $ticketService
    ) {}

    /**
     * 58. POST /api/v1/tenant/tickets: Khách thuê gửi yêu cầu hỗ trợ mới.
     */
    public function createTenantTicket(StoreTenantTicketRequest $request): JsonResponse
    {
        $tenant = $request->user();
        if (!($tenant instanceof Tenant)) {
            return $this->errorResponse('Chỉ tài khoản Khách thuê (Tenant) mới có quyền tạo phiếu hỗ trợ qua cổng này.', 403);
        }

        $ticket = $this->ticketService->createTenantTicket($request->validated(), $tenant);

        return $this->successResponse($ticket, 'Gửi yêu cầu hỗ trợ thành công. Đã thông báo tới Ban quản lý.', 201);
    }

    /**
     * 59. GET /api/v1/tickets/{id}: Xem chi tiết tiến trình xử lý Ticket & Lịch sử.
     */
    public function show(string $id, Request $request): JsonResponse
    {
        $ticket = $this->ticketService->getTicketDetails($id, $request->user());

        return $this->successResponse($ticket, 'Lấy chi tiết phiếu yêu cầu hỗ trợ thành công.');
    }

    /**
     * 60. PUT /api/v1/tickets/{id}: Cập nhật thông tin chi tiết ticket (khi còn ở trạng thái Mới OPEN).
     */
    public function update(string $id, UpdateTicketRequest $request): JsonResponse
    {
        $ticket = $this->ticketService->updateTicket($id, $request->validated(), $request->user());

        return $this->successResponse($ticket, 'Cập nhật thông tin phiếu yêu cầu hỗ trợ thành công.');
    }

    /**
     * 61. DELETE /api/v1/tickets/{id}: Xóa mềm ticket (chỉ áp dụng cho Quản trị viên Admin).
     */
    public function destroy(string $id, Request $request): JsonResponse
    {
        $this->ticketService->deleteTicket($id, $request->user());

        return $this->successResponse(null, 'Xóa phiếu yêu cầu hỗ trợ thành công.');
    }

    /**
     * 62. PATCH /api/v1/tickets/{id}/assign: BQL tiếp nhận và phân công nhân viên kỹ thuật.
     */
    public function assign(string $id, AssignTicketRequest $request): JsonResponse
    {
        $ticket = $this->ticketService->assignTicket($id, $request->validated(), $request->user());

        return $this->successResponse($ticket, 'Tiếp nhận và phân công nhân viên xử lý thành công.');
    }

    /**
     * 63. PATCH /api/v1/tickets/{id}/status: Cập nhật tiến độ sửa chữa (ON_HOLD / RESOLVED).
     */
    public function updateStatus(string $id, UpdateTicketStatusRequest $request): JsonResponse
    {
        $ticket = $this->ticketService->updateTicketStatus($id, $request->validated(), $request->user());

        return $this->successResponse($ticket, 'Cập nhật tiến độ xử lý ticket thành công.');
    }

    /**
     * 64. POST /api/v1/tickets/{id}/proof-images: Tải lên hình ảnh bằng chứng (Before / After).
     */
    public function uploadProofImages(string $id, UploadProofImagesRequest $request): JsonResponse
    {
        $proofs = $this->ticketService->uploadProofImages($id, $request->all(), $request->user());

        return $this->successResponse($proofs, 'Tải lên hình ảnh bằng chứng thành công.', 201);
    }

    /**
     * 65. POST /api/v1/tenant/tickets/{id}/confirm: Khách thuê xác nhận nghiệm thu (Hoàn tất hoặc Từ chối).
     */
    public function confirm(string $id, ConfirmTicketRequest $request): JsonResponse
    {
        $tenant = $request->user();
        if (!($tenant instanceof Tenant)) {
            return $this->errorResponse('Chỉ khách thuê mới có quyền xác nhận nghiệm thu.', 403);
        }

        $ticket = $this->ticketService->confirmTicket($id, $request->validated(), $tenant);

        $msg = $ticket->status->value === 'COMPLETED'
            ? 'Xác nhận nghiệm thu hoàn tất phiếu hỗ trợ thành công.'
            : 'Đã gửi phản hồi yêu cầu kiểm tra và khắc phục lại.';

        return $this->successResponse($ticket, $msg);
    }

    /**
     * 66. POST /api/v1/tenant/tickets/{id}/rate: Khách thuê đánh giá chất lượng phục vụ (1-5 sao).
     */
    public function rate(string $id, RateTicketRequest $request): JsonResponse
    {
        $tenant = $request->user();
        if (!($tenant instanceof Tenant)) {
            return $this->errorResponse('Chỉ khách thuê mới có quyền gửi đánh giá.', 403);
        }

        $ticket = $this->ticketService->rateTicket($id, $request->validated(), $tenant);

        return $this->successResponse($ticket, 'Đánh giá chất lượng phục vụ thành công. Cảm ơn phản hồi của bạn!');
    }
}
