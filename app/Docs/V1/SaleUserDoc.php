<?php

namespace App\Docs\V1;

use OpenApi\Attributes as OA;

class SaleUserDoc extends BaseDoc
{
    #[OA\Get(
        path: '/api/v1/admin/users/sales',
        tags: ['Sales Management'],
        summary: '77. Danh sách Nhân viên Sales',
        description: 'Danh sách nhân viên kinh doanh / môi giới (Sales). Hiển thị kèm số lượng Lead đang xử lý (active_leads_count) và các tòa nhà được phân công phụ trách.',
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'page', in: 'query', required: false, description: 'Số trang', schema: new OA\Schema(type: 'integer', default: 1)),
            new OA\Parameter(name: 'per_page', in: 'query', required: false, description: 'Số bản ghi mỗi trang', schema: new OA\Schema(type: 'integer', default: 10)),
            new OA\Parameter(name: 'keyword', in: 'query', required: false, description: 'Tìm theo họ tên, email, phone, mã nhân viên', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'status', in: 'query', required: false, description: 'Trạng thái (ACTIVE, LOCKED, PENDING)', schema: new OA\Schema(type: 'string', enum: ['ACTIVE', 'LOCKED', 'PENDING'])),
            new OA\Parameter(name: 'building_id', in: 'query', required: false, description: 'Lọc theo ID tòa nhà phụ trách', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Lấy danh sách Sales thành công', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponseSuccess')),
            new OA\Response(response: 403, description: 'Không có quyền truy cập', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponseError')),
        ]
    )]
    public function index() {}

    #[OA\Post(
        path: '/api/v1/admin/users/sales',
        tags: ['Sales Management'],
        summary: '78. Tạo mới Nhân viên Sales',
        description: 'Tạo mới nhân viên Sales (full_name, email, phone, password, max_concurrent_leads, building_ids). Mật khẩu được mã hóa bcrypt.',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['full_name', 'email', 'password'],
                properties: [
                    new OA\Property(property: 'employee_code', type: 'string', example: 'NV-SALE-010', nullable: true),
                    new OA\Property(property: 'full_name', type: 'string', example: 'Nguyễn Văn Kinh Doanh'),
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'sales.kinhdoanh@officeleasing.vn'),
                    new OA\Property(property: 'phone', type: 'string', example: '0912345678', nullable: true),
                    new OA\Property(property: 'password', type: 'string', format: 'password', example: 'SalesPass@123'),
                    new OA\Property(property: 'commission_rate', type: 'number', format: 'float', example: 1.5, default: 1.5),
                    new OA\Property(property: 'max_concurrent_leads', type: 'integer', example: 10, default: 10),
                    new OA\Property(property: 'status', type: 'string', enum: ['ACTIVE', 'LOCKED', 'PENDING'], default: 'ACTIVE'),
                    new OA\Property(property: 'building_ids', type: 'array', items: new OA\Items(type: 'string', format: 'uuid')),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Tạo nhân viên Sales thành công', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponseSuccess')),
            new OA\Response(response: 422, description: 'Dữ liệu không hợp lệ', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponseError')),
        ]
    )]
    public function store() {}

    #[OA\Get(
        path: '/api/v1/admin/users/sales/{id}',
        tags: ['Sales Management'],
        summary: '79. Chi tiết Nhân viên Sales (kèm KPI tỷ lệ chốt)',
        description: 'Xem chi tiết hồ sơ nhân viên Sales, danh sách tòa nhà phụ trách và thống kê tỷ lệ chốt hợp đồng thành công (conversion_rate). Hỗ trợ Sales xem chính mình.',
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'UUID của nhân viên Sales', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Lấy chi tiết thành công', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponseSuccess')),
            new OA\Response(response: 403, description: 'Không có quyền xem hồ sơ', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponseError')),
        ]
    )]
    public function show() {}

    #[OA\Put(
        path: '/api/v1/admin/users/sales/{id}',
        tags: ['Sales Management'],
        summary: '80. Cập nhật Nhân viên Sales',
        description: 'Cập nhật thông tin nhân viên Sales, hạn mức tiếp nhận Lead (max_concurrent_leads) hoặc trạng thái làm việc (ACTIVE, LOCKED, PENDING).',
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'UUID của nhân viên Sales', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'full_name', type: 'string', example: 'Nguyễn Văn Sales Đổi Tên'),
                    new OA\Property(property: 'phone', type: 'string', example: '0912349999'),
                    new OA\Property(property: 'max_concurrent_leads', type: 'integer', example: 15),
                    new OA\Property(property: 'status', type: 'string', enum: ['ACTIVE', 'LOCKED', 'PENDING']),
                    new OA\Property(property: 'building_ids', type: 'array', items: new OA\Items(type: 'string', format: 'uuid')),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Cập nhật thành công', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponseSuccess')),
            new OA\Response(response: 422, description: 'Dữ liệu không hợp lệ', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponseError')),
        ]
    )]
    public function update() {}

    #[OA\Delete(
        path: '/api/v1/admin/users/sales/{id}',
        tags: ['Sales Management'],
        summary: '81. Xóa mềm Nhân viên Sales',
        description: 'Xóa mềm nhân viên Sales. Cần cảnh báo hoặc re-assign các Lead đang hoạt động trước khi xóa.',
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'UUID của nhân viên Sales', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        requestBody: new OA\RequestBody(
            required: false,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 're_assign_to_sales_id', type: 'string', format: 'uuid', description: 'ID của sales tiếp nhận lại Lead chưa đóng', nullable: true),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Xóa nhân viên Sales thành công', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponseSuccess')),
            new OA\Response(response: 422, description: 'Nhân viên còn Lead chưa hoàn tất cần chuyển giao', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponseError')),
        ]
    )]
    public function destroy() {}
}
