<?php

namespace App\Docs\V1;

use OpenApi\Attributes as OA;

class PropertyManagerDoc extends BaseDoc
{
    #[OA\Get(
        path: '/api/v1/admin/users/property-managers',
        tags: ['Property Managers Management'],
        summary: '82. Danh sách Ban Quản lý (PM)',
        description: 'Danh sách nhân viên Ban quản lý tòa nhà (PM). Kèm số hotline sự cố và phòng ban phụ trách kỹ thuật. Eager load manager_buildings.',
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'page', in: 'query', required: false, description: 'Số trang', schema: new OA\Schema(type: 'integer', default: 1)),
            new OA\Parameter(name: 'per_page', in: 'query', required: false, description: 'Số bản ghi mỗi trang', schema: new OA\Schema(type: 'integer', default: 10)),
            new OA\Parameter(name: 'keyword', in: 'query', required: false, description: 'Tìm theo họ tên, email, phone, mã nhân viên', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'status', in: 'query', required: false, description: 'Trạng thái (ACTIVE, LOCKED, PENDING)', schema: new OA\Schema(type: 'string', enum: ['ACTIVE', 'LOCKED', 'PENDING'])),
            new OA\Parameter(name: 'department', in: 'query', required: false, description: 'Bộ phận (TECHNICAL, SECURITY, CLEANING, CUSTOMER_CARE)', schema: new OA\Schema(type: 'string', enum: ['TECHNICAL', 'SECURITY', 'CLEANING', 'CUSTOMER_CARE'])),
            new OA\Parameter(name: 'building_id', in: 'query', required: false, description: 'Lọc theo ID tòa nhà phụ trách', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Lấy danh sách Ban Quản lý thành công', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponseSuccess')),
            new OA\Response(response: 403, description: 'Không có quyền truy cập', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponseError')),
        ]
    )]
    public function index() {}

    #[OA\Post(
        path: '/api/v1/admin/users/property-managers',
        tags: ['Property Managers Management'],
        summary: '83. Tạo mới Ban Quản lý (PM)',
        description: 'Tạo tài khoản Ban quản lý tòa nhà mới (full_name, email, phone, emergency_hotline, department, building_ids). Lưu ID Admin tạo.',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['full_name', 'email', 'password'],
                properties: [
                    new OA\Property(property: 'employee_code', type: 'string', example: 'NV-BQL-005', nullable: true),
                    new OA\Property(property: 'full_name', type: 'string', example: 'Trần Kỹ Thuật Trưởng'),
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'pm.trantruong@officeleasing.vn'),
                    new OA\Property(property: 'phone', type: 'string', example: '0977665544', nullable: true),
                    new OA\Property(property: 'emergency_hotline', type: 'string', example: '19001234', nullable: true),
                    new OA\Property(property: 'password', type: 'string', format: 'password', example: 'PmPass@123'),
                    new OA\Property(property: 'department', type: 'string', enum: ['TECHNICAL', 'SECURITY', 'CLEANING', 'CUSTOMER_CARE'], default: 'TECHNICAL'),
                    new OA\Property(property: 'status', type: 'string', enum: ['ACTIVE', 'LOCKED', 'PENDING'], default: 'ACTIVE'),
                    new OA\Property(property: 'building_ids', type: 'array', items: new OA\Items(type: 'string', format: 'uuid')),
                    new OA\Property(property: 'is_primary_building_id', type: 'string', format: 'uuid', nullable: true),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Tạo Ban Quản lý thành công', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponseSuccess')),
            new OA\Response(response: 422, description: 'Dữ liệu không hợp lệ', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponseError')),
        ]
    )]
    public function store() {}

    #[OA\Get(
        path: '/api/v1/admin/users/property-managers/{id}',
        tags: ['Property Managers Management'],
        summary: '84. Chi tiết Ban Quản lý (PM)',
        description: 'Xem chi tiết tài khoản Ban quản lý tòa nhà và các tòa nhà được giao trách nhiệm kèm thống kê tickets. PM có thể xem hồ sơ của mình.',
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'UUID của nhân sự BQL', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Lấy chi tiết thành công', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponseSuccess')),
            new OA\Response(response: 403, description: 'Không có quyền truy cập', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponseError')),
        ]
    )]
    public function show() {}

    #[OA\Put(
        path: '/api/v1/admin/users/property-managers/{id}',
        tags: ['Property Managers Management'],
        summary: '85. Cập nhật Ban Quản lý (PM)',
        description: 'Cập nhật thông tin nhân viên BQL (hotline khẩn cấp, phòng kỹ thuật, trạng thái, danh sách tòa nhà).',
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'UUID của nhân sự BQL', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'full_name', type: 'string', example: 'Trần Kỹ Thuật Đổi Tên'),
                    new OA\Property(property: 'emergency_hotline', type: 'string', example: '0977889900'),
                    new OA\Property(property: 'department', type: 'string', enum: ['TECHNICAL', 'SECURITY', 'CLEANING', 'CUSTOMER_CARE']),
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
        path: '/api/v1/admin/users/property-managers/{id}',
        tags: ['Property Managers Management'],
        summary: '86. Xóa mềm Ban Quản lý (PM)',
        description: 'Xóa mềm tài khoản Ban quản lý tòa nhà.',
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'UUID của nhân sự BQL', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Xóa nhân sự BQL thành công', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponseSuccess')),
        ]
    )]
    public function destroy() {}
}
