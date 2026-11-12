<?php

namespace App\Docs\V1;

use OpenApi\Attributes as OA;

class LeadDoc extends BaseDoc
{
    #[OA\Post(
        path: '/api/v1/portal/leads',
        tags: ['Leads (Public Portal)'],
        summary: 'Gửi yêu cầu Báo giá / Site tour từ Public Portal',
        description: 'Khách hàng vãng lai gửi form liên hệ. Tự động kiểm tra chống trùng 30 ngày và phân bổ cho Sales theo Least Active Leads.',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['contact_full_name', 'phone', 'email', 'building_id'],
                properties: [
                    new OA\Property(property: 'contact_full_name', type: 'string', example: 'Nguyễn Văn A'),
                    new OA\Property(property: 'company_name', type: 'string', example: 'Công ty Cổ phần Alpha'),
                    new OA\Property(property: 'phone', type: 'string', example: '0901234567'),
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'contact@alpha.com'),
                    new OA\Property(property: 'building_id', type: 'string', format: 'uuid'),
                    new OA\Property(property: 'unit_id', type: 'string', format: 'uuid', nullable: true),
                    new OA\Property(property: 'desired_area_sqm', type: 'number', example: 150),
                    new OA\Property(property: 'budget_min', type: 'number', example: 30000000),
                    new OA\Property(property: 'budget_max', type: 'number', example: 50000000),
                    new OA\Property(property: 'preferred_site_tour_at', type: 'string', format: 'date-time'),
                    new OA\Property(property: 'customer_note', type: 'string', example: 'Cần khảo sát vào sáng thứ Bảy'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Tạo Lead thành công', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponseSuccess')),
            new OA\Response(response: 422, description: 'Lỗi xác thực dữ liệu', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponseError')),
        ]
    )]
    public function portalStore() {}

    #[OA\Get(
        path: '/api/v1/crm/leads',
        tags: ['CRM Leads'],
        summary: 'Danh sách Lead theo Pipeline CRM',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\QueryParameter(name: 'status', description: 'Trạng thái: PENDING_ASSIGN, NEW, NEGOTIATING, WON, LOST'),
            new OA\QueryParameter(name: 'building_id', description: 'Lọc theo ID tòa nhà'),
            new OA\QueryParameter(name: 'assigned_sales_id', description: 'Lọc theo ID Sales phụ trách'),
            new OA\QueryParameter(name: 'keyword', description: 'Từ khóa tên, SĐT, email, công ty'),
            new OA\QueryParameter(name: 'page', schema: new OA\Schema(type: 'integer', default: 1)),
            new OA\QueryParameter(name: 'per_page', schema: new OA\Schema(type: 'integer', default: 15)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Thành công', content: new OA\JsonContent(ref: '#/components/schemas/ApiPaginateResponse')),
            new OA\Response(response: 'default', description: 'Lỗi', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponseError')),
        ]
    )]
    public function crmIndex() {}

    #[OA\Post(
        path: '/api/v1/crm/leads',
        tags: ['CRM Leads'],
        summary: 'Tạo hồ sơ Lead thủ công từ CRM (Hotline/Trực tiếp)',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['contact_full_name', 'phone', 'email'],
                properties: [
                    new OA\Property(property: 'contact_full_name', type: 'string'),
                    new OA\Property(property: 'company_name', type: 'string', nullable: true),
                    new OA\Property(property: 'phone', type: 'string'),
                    new OA\Property(property: 'email', type: 'string'),
                    new OA\Property(property: 'lead_source', type: 'string', example: 'HOTLINE'),
                    new OA\Property(property: 'building_id', type: 'string', format: 'uuid', nullable: true),
                    new OA\Property(property: 'unit_id', type: 'string', format: 'uuid', nullable: true),
                    new OA\Property(property: 'assigned_sales_id', type: 'string', format: 'uuid', nullable: true),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Khởi tạo thành công', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponseSuccess')),
            new OA\Response(response: 422, description: 'Lỗi xác thực', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponseError')),
        ]
    )]
    public function crmStore() {}

    #[OA\Get(
        path: '/api/v1/crm/leads/{id}',
        tags: ['CRM Leads'],
        summary: 'Xem chi tiết hồ sơ Lead và toàn bộ lịch sử chăm sóc',
        security: [['bearerAuth' => []]],
        parameters: [new OA\PathParameter(name: 'id', description: 'UUID của Lead')],
        responses: [
            new OA\Response(response: 200, description: 'Thành công', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponseSuccess')),
            new OA\Response(response: 404, description: 'Không tìm thấy', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponseError')),
        ]
    )]
    public function crmShow() {}

    #[OA\Put(
        path: '/api/v1/crm/leads/{id}',
        tags: ['CRM Leads'],
        summary: 'Cập nhật thông tin nhu cầu của Lead',
        security: [['bearerAuth' => []]],
        parameters: [new OA\PathParameter(name: 'id', description: 'UUID của Lead')],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'desired_area_sqm', type: 'number'),
                    new OA\Property(property: 'budget_min', type: 'number'),
                    new OA\Property(property: 'budget_max', type: 'number'),
                    new OA\Property(property: 'customer_note', type: 'string'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Thành công', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponseSuccess')),
        ]
    )]
    public function crmUpdate() {}

    #[OA\Delete(
        path: '/api/v1/crm/leads/{id}',
        tags: ['CRM Leads'],
        summary: 'Xóa mềm hồ sơ Lead',
        security: [['bearerAuth' => []]],
        parameters: [new OA\PathParameter(name: 'id', description: 'UUID của Lead')],
        responses: [
            new OA\Response(response: 200, description: 'Xóa thành công', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponseSuccess')),
            new OA\Response(response: 422, description: 'Không được phép xóa nếu đã WON', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponseError')),
        ]
    )]
    public function crmDestroy() {}

    #[OA\Patch(
        path: '/api/v1/crm/leads/{id}/status',
        tags: ['CRM Leads'],
        summary: 'Cập nhật trạng thái Pipeline (Bắt buộc lý do nếu chuyển sang LOST)',
        security: [['bearerAuth' => []]],
        parameters: [new OA\PathParameter(name: 'id', description: 'UUID của Lead')],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['status'],
                properties: [
                    new OA\Property(property: 'status', type: 'string', enum: ['PENDING_ASSIGN', 'NEW', 'NEGOTIATING', 'WON', 'LOST']),
                    new OA\Property(property: 'lost_reason_category', type: 'string', nullable: true),
                    new OA\Property(property: 'lost_reason_detail', type: 'string', nullable: true),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Cập nhật thành công', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponseSuccess')),
            new OA\Response(response: 422, description: 'Thiếu lý do khi LOST', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponseError')),
        ]
    )]
    public function crmUpdateStatus() {}

    #[OA\Post(
        path: '/api/v1/crm/leads/{id}/assign',
        tags: ['CRM Leads'],
        summary: 'Điều phối hoặc phân công lại Sales phụ trách Lead',
        security: [['bearerAuth' => []]],
        parameters: [new OA\PathParameter(name: 'id', description: 'UUID của Lead')],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['sales_id'],
                properties: [
                    new OA\Property(property: 'sales_id', type: 'string', format: 'uuid'),
                    new OA\Property(property: 'assignment_note', type: 'string', nullable: true),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Điều phối thành công', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponseSuccess')),
        ]
    )]
    public function crmAssign() {}

    #[OA\Post(
        path: '/api/v1/crm/leads/{id}/convert-to-tenant',
        tags: ['CRM Leads'],
        summary: 'Chuyển đổi Lead thành công sang Khách thuê chính thức (Tenant)',
        security: [['bearerAuth' => []]],
        parameters: [new OA\PathParameter(name: 'id', description: 'UUID của Lead')],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['tax_code'],
                properties: [
                    new OA\Property(property: 'tax_code', type: 'string', example: '0312345678'),
                    new OA\Property(property: 'company_name', type: 'string', nullable: true),
                    new OA\Property(property: 'representative_position', type: 'string', nullable: true),
                    new OA\Property(property: 'headquarter_address', type: 'string', nullable: true),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Chuyển đổi thành công', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponseSuccess')),
            new OA\Response(response: 422, description: 'Lead đã được chuyển đổi trước đó', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponseError')),
        ]
    )]
    public function crmConvertToTenant() {}

    #[OA\Get(
        path: '/api/v1/crm/leads/{id}/activities',
        tags: ['Lead Activities'],
        summary: 'Lấy dòng thời gian (Timeline) các lần gọi, email, site tour của Lead',
        security: [['bearerAuth' => []]],
        parameters: [new OA\PathParameter(name: 'id', description: 'UUID của Lead')],
        responses: [
            new OA\Response(response: 200, description: 'Thành công', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponseSuccess')),
        ]
    )]
    public function activityIndex() {}

    #[OA\Post(
        path: '/api/v1/crm/leads/{id}/activities',
        tags: ['Lead Activities'],
        summary: 'Ghi nhật ký tương tác mới (Tự động kích hoạt first_contacted_at)',
        security: [['bearerAuth' => []]],
        parameters: [new OA\PathParameter(name: 'id', description: 'UUID của Lead')],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['activity_type', 'title'],
                properties: [
                    new OA\Property(property: 'activity_type', type: 'string', enum: ['CALL', 'SITE_TOUR', 'EMAIL', 'MEETING', 'NOTE', 'QUOTATION_SENT']),
                    new OA\Property(property: 'title', type: 'string', example: 'Gọi điện báo giá mặt bằng P.1201'),
                    new OA\Property(property: 'content', type: 'string', nullable: true),
                    new OA\Property(property: 'event_at', type: 'string', format: 'date-time', nullable: true),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Ghi nhận thành công', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponseSuccess')),
        ]
    )]
    public function activityStore() {}
}
