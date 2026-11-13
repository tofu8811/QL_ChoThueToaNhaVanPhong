<?php

namespace App\Docs\V1;

use OpenApi\Attributes as OA;

class BuildingAssignmentDoc extends BaseDoc
{
    #[OA\Get(
        path: '/api/v1/admin/buildings/{buildingId}/assignments',
        tags: ['Building Assignments & RBAC'],
        summary: '92. Xem Nhân sự Phụ trách Tòa nhà',
        description: 'Xem danh sách toàn bộ đội ngũ nhân sự đang phụ trách tòa nhà cụ thể: Các Sales phụ trách nhận Lead, BQL phụ trách sự cố, Kế toán phụ trách xuất hóa đơn. Trả về gom nhóm: { sales: [...], managers: [...], accountants: [...] }.',
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'buildingId', in: 'path', required: true, description: 'UUID định danh của tòa nhà', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Lấy danh sách nhân sự phụ trách thành công', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponseSuccess')),
            new OA\Response(response: 403, description: 'Không có quyền truy cập', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponseError')),
            new OA\Response(response: 404, description: 'Không tìm thấy tòa nhà', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponseError')),
        ]
    )]
    public function getAssignments() {}

    #[OA\Post(
        path: '/api/v1/admin/buildings/{buildingId}/assignments/sales',
        tags: ['Building Assignments & RBAC'],
        summary: '93. Gán Sales vào Tòa nhà',
        description: 'Gán một hoặc nhiều nhân viên Sales vào danh sách phụ trách bán mặt bằng tại tòa nhà này. Tự động kiểm tra trùng lặp (sales_id + building_id).',
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'buildingId', in: 'path', required: true, description: 'UUID của tòa nhà', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['sales_ids'],
                properties: [
                    new OA\Property(property: 'sales_ids', type: 'array', items: new OA\Items(type: 'string', format: 'uuid')),
                    new OA\Property(property: 'is_active', type: 'boolean', default: true),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Phân công Sales thành công', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponseSuccess')),
            new OA\Response(response: 422, description: 'Lỗi kiểm thực dữ liệu', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponseValidationError')),
        ]
    )]
    public function assignSales() {}

    #[OA\Delete(
        path: '/api/v1/admin/buildings/{buildingId}/assignments/sales/{saleId}',
        tags: ['Building Assignments & RBAC'],
        summary: '94. Gỡ Sales khỏi Tòa nhà',
        description: 'Gỡ bỏ phân công Sales khỏi tòa nhà. Xóa bản ghi trong bảng pivot sale_buildings.',
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'buildingId', in: 'path', required: true, description: 'UUID của tòa nhà', schema: new OA\Schema(type: 'string', format: 'uuid')),
            new OA\Parameter(name: 'saleId', in: 'path', required: true, description: 'UUID của nhân viên Sales', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Gỡ bỏ phân công thành công', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponseSuccess')),
            new OA\Response(response: 404, description: 'Không tìm thấy dữ liệu', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponseError')),
        ]
    )]
    public function removeSales() {}

    #[OA\Post(
        path: '/api/v1/admin/buildings/{buildingId}/assignments/property-managers',
        tags: ['Building Assignments & RBAC'],
        summary: '95. Gán BQL vào Tòa nhà',
        description: 'Gán Ban quản lý vào tòa nhà (kèm cờ is_head_manager nếu là Trưởng ban quản lý tòa nhà). Bảng pivot manager_buildings.',
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'buildingId', in: 'path', required: true, description: 'UUID của tòa nhà', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['pm_id'],
                properties: [
                    new OA\Property(property: 'pm_id', type: 'string', format: 'uuid'),
                    new OA\Property(property: 'is_primary', type: 'boolean', default: false),
                    new OA\Property(property: 'is_head_manager', type: 'boolean', default: false),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Phân công BQL thành công', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponseSuccess')),
            new OA\Response(response: 422, description: 'Lỗi kiểm thực dữ liệu', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponseValidationError')),
        ]
    )]
    public function assignPropertyManager() {}

    #[OA\Delete(
        path: '/api/v1/admin/buildings/{buildingId}/assignments/property-managers/{pmId}',
        tags: ['Building Assignments & RBAC'],
        summary: '96. Gỡ BQL khỏi Tòa nhà',
        description: 'Gỡ bỏ phân công Ban quản lý khỏi tòa nhà. Xóa bản ghi pivot manager_buildings.',
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'buildingId', in: 'path', required: true, description: 'UUID của tòa nhà', schema: new OA\Schema(type: 'string', format: 'uuid')),
            new OA\Parameter(name: 'pmId', in: 'path', required: true, description: 'UUID của Ban Quản Lý', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Gỡ bỏ BQL thành công', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponseSuccess')),
            new OA\Response(response: 404, description: 'Không tìm thấy dữ liệu', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponseError')),
        ]
    )]
    public function removePropertyManager() {}

    #[OA\Post(
        path: '/api/v1/admin/buildings/{buildingId}/assignments/accountants',
        tags: ['Building Assignments & RBAC'],
        summary: '97. Gán Kế toán vào Tòa nhà',
        description: 'Gán Kế toán phụ trách theo dõi công nợ, xuất Debit Note cho tòa nhà. Bảng pivot account_buildings.',
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'buildingId', in: 'path', required: true, description: 'UUID của tòa nhà', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['accountant_ids'],
                properties: [
                    new OA\Property(property: 'accountant_ids', type: 'array', items: new OA\Items(type: 'string', format: 'uuid')),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Phân công Kế toán thành công', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponseSuccess')),
            new OA\Response(response: 422, description: 'Lỗi kiểm thực dữ liệu', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponseValidationError')),
        ]
    )]
    public function assignAccountant() {}

    #[OA\Delete(
        path: '/api/v1/admin/buildings/{buildingId}/assignments/accountants/{accountantId}',
        tags: ['Building Assignments & RBAC'],
        summary: '98. Gỡ Kế toán khỏi Tòa nhà',
        description: 'Gỡ bỏ phân công Kế toán khỏi tòa nhà. Xóa bản ghi pivot account_buildings.',
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'buildingId', in: 'path', required: true, description: 'UUID của tòa nhà', schema: new OA\Schema(type: 'string', format: 'uuid')),
            new OA\Parameter(name: 'accountantId', in: 'path', required: true, description: 'UUID của Kế toán', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Gỡ bỏ Kế toán thành công', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponseSuccess')),
            new OA\Response(response: 404, description: 'Không tìm thấy dữ liệu', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponseError')),
        ]
    )]
    public function removeAccountant() {}
}
