<?php

namespace App\Docs\V1;

use OpenApi\Attributes as OA;

class TicketDoc extends BaseDoc
{
    #[OA\Post(
        path: '/api/v1/tenant/tickets',
        tags: ['Tickets & Maintenance'],
        summary: '58. Khách thuê gửi yêu cầu hỗ trợ mới (Tenant Portal)',
        description: 'Khách thuê gửi yêu cầu hỗ trợ sự cố mới từ Tenant Portal: Tiêu đề, danh mục sự cố, mức độ ưu tiên, mô tả hiện trường, mặt bằng và ảnh chụp.',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['building_id', 'title', 'description', 'category'],
                properties: [
                    new OA\Property(property: 'building_id', type: 'string', format: 'uuid', example: '018f3a2b-1111-7000-8000-000000000001'),
                    new OA\Property(property: 'unit_id', type: 'string', format: 'uuid', nullable: true, example: '018f3a2b-2222-7000-8000-000000000002'),
                    new OA\Property(property: 'contract_id', type: 'string', format: 'uuid', nullable: true),
                    new OA\Property(property: 'title', type: 'string', example: 'Hỏng điều hòa trung tâm phòng họp'),
                    new OA\Property(property: 'category', type: 'string', enum: ['MAINTENANCE', 'OVERTIME_CONSTRUCTION', 'SECURITY_CLEANING', 'SERVICE_COMPLAINT'], example: 'MAINTENANCE'),
                    new OA\Property(property: 'priority', type: 'string', enum: ['LOW', 'NORMAL', 'HIGH', 'URGENT'], example: 'HIGH'),
                    new OA\Property(property: 'description', type: 'string', example: 'Điều hòa chảy nước và phát tiếng kêu lớn tại khu vực phòng họp số 2.'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Gửi yêu cầu hỗ trợ thành công', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponseSuccess')),
            new OA\Response(response: 422, description: 'Dữ liệu đầu vào không hợp lệ', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponseError')),
        ]
    )]
    public function createTenantTicket() {}

    #[OA\Get(
        path: '/api/v1/tickets/{id}',
        tags: ['Tickets & Maintenance'],
        summary: '59. Xem chi tiết tiến trình xử lý Ticket & Lịch sử',
        description: 'Xem chi tiết tiến trình xử lý Ticket: Nội dung phản ánh ban đầu, ảnh hiện trường sự cố, nhân viên BQL đang phụ trách, lịch sử các mốc chuyển trạng thái và ảnh nghiệm thu.',
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'UUID của Ticket', schema: new OA\Schema(type: 'string', format: 'uuid'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Lấy chi tiết ticket thành công', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponseSuccess')),
            new OA\Response(response: 404, description: 'Không tìm thấy ticket', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponseError')),
        ]
    )]
    public function show() {}

    #[OA\Put(
        path: '/api/v1/tickets/{id}',
        tags: ['Tickets & Maintenance'],
        summary: '60. Cập nhật thông tin chi tiết ticket (khi còn ở trạng thái Mới OPEN)',
        description: 'Khách thuê hoặc BQL cập nhật bổ sung mô tả, tiêu đề, mức độ ưu tiên khi ticket còn ở trạng thái OPEN / NEW.',
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'UUID của Ticket', schema: new OA\Schema(type: 'string', format: 'uuid'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Cập nhật ticket thành công', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponseSuccess')),
            new OA\Response(response: 422, description: 'Ticket không còn ở trạng thái OPEN', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponseError')),
        ]
    )]
    public function update() {}

    #[OA\Delete(
        path: '/api/v1/tickets/{id}',
        tags: ['Tickets & Maintenance'],
        summary: '61. Xóa mềm ticket (Admin)',
        description: 'Xóa mềm ticket (chỉ áp dụng cho Quản trị viên Admin nếu là yêu cầu thử nghiệm hoặc tạo nhầm).',
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'UUID của Ticket', schema: new OA\Schema(type: 'string', format: 'uuid'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Xóa ticket thành công', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponseSuccess')),
            new OA\Response(response: 403, description: 'Không có quyền xóa ticket', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponseError')),
        ]
    )]
    public function destroy() {}

    #[OA\Patch(
        path: '/api/v1/tickets/{id}/assign',
        tags: ['Tickets & Maintenance'],
        summary: '62. Ban quản lý tiếp nhận & phân công nhân viên kỹ thuật',
        description: 'Ban quản lý tiếp nhận và phân công nhân viên kỹ thuật trực tiếp xử lý sự cố (assigned_pm_id). Chuyển trạng thái sang IN_PROGRESS.',
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'UUID của Ticket', schema: new OA\Schema(type: 'string', format: 'uuid'))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['assigned_pm_id'],
                properties: [
                    new OA\Property(property: 'assigned_pm_id', type: 'string', format: 'uuid', example: '018f3a2b-3333-7000-8000-000000000003'),
                    new OA\Property(property: 'expected_completion_at', type: 'string', format: 'date-time', nullable: true),
                    new OA\Property(property: 'note', type: 'string', nullable: true),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Phân công nhân viên thành công', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponseSuccess')),
            new OA\Response(response: 422, description: 'Dữ liệu không hợp lệ', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponseError')),
        ]
    )]
    public function assign() {}

    #[OA\Patch(
        path: '/api/v1/tickets/{id}/status',
        tags: ['Tickets & Maintenance'],
        summary: '63. Cập nhật tiến độ sửa chữa (ON_HOLD / RESOLVED)',
        description: 'Cập nhật tiến độ xử lý: Chuyển sang ON_HOLD (kèm lý do) hoặc RESOLVED (kèm tóm tắt cách xử lý).',
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'UUID của Ticket', schema: new OA\Schema(type: 'string', format: 'uuid'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Cập nhật trạng thái thành công', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponseSuccess')),
            new OA\Response(response: 422, description: 'Dữ liệu không hợp lệ', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponseError')),
        ]
    )]
    public function updateStatus() {}

    #[OA\Post(
        path: '/api/v1/tickets/{id}/proof-images',
        tags: ['Tickets & Maintenance'],
        summary: '64. Tải lên hình ảnh bằng chứng nghiệm thu (Before / After)',
        description: 'Tải lên hình ảnh bằng chứng nghiệm thu sự cố (proof_type: BEFORE - hiện trạng hư hỏng, AFTER - kết quả sau khi sửa chữa).',
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'UUID của Ticket', schema: new OA\Schema(type: 'string', format: 'uuid'))
        ],
        responses: [
            new OA\Response(response: 201, description: 'Tải lên hình ảnh thành công', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponseSuccess')),
        ]
    )]
    public function uploadProofImages() {}

    #[OA\Post(
        path: '/api/v1/tenant/tickets/{id}/confirm',
        tags: ['Tickets & Maintenance'],
        summary: '65. Khách thuê xác nhận nghiệm thu kết quả xử lý',
        description: 'Khách thuê xác nhận nghiệm thu kết quả xử lý. Chuyển sang CLOSED (COMPLETED) hoặc Từ chối chuyển về IN_PROGRESS kèm phản hồi.',
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'UUID của Ticket', schema: new OA\Schema(type: 'string', format: 'uuid'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Xác nhận nghiệm thu thành công', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponseSuccess')),
        ]
    )]
    public function confirm() {}

    #[OA\Post(
        path: '/api/v1/tenant/tickets/{id}/rate',
        tags: ['Tickets & Maintenance'],
        summary: '66. Khách thuê đánh giá chất lượng phục vụ (1-5 sao)',
        description: 'Khách thuê đánh giá chất lượng phục vụ của Ban quản lý cho ticket (1 đến 5 sao và ý kiến đóng góp tenant_feedback).',
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'UUID của Ticket', schema: new OA\Schema(type: 'string', format: 'uuid'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Đánh giá chất lượng phục vụ thành công', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponseSuccess')),
        ]
    )]
    public function rate() {}
}
