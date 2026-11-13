<?php

namespace App\Docs\V1;

use OpenApi\Attributes as OA;

class AccountantUserDoc extends BaseDoc
{
    #[OA\Get(
        path: '/api/v1/admin/users/accountants',
        tags: ['Accountants Management'],
        summary: '87. Danh sách Nhân viên Kế toán',
        description: 'Danh sách nhân viên Kế toán. Kèm cờ quyền hạn can_approve_adjustment (lập phiếu điều chỉnh cước). Eager load account_buildings.',
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'page', in: 'query', required: false, description: 'Số trang', schema: new OA\Schema(type: 'integer', default: 1)),
            new OA\Parameter(name: 'per_page', in: 'query', required: false, description: 'Số bản ghi mỗi trang', schema: new OA\Schema(type: 'integer', default: 10)),
            new OA\Parameter(name: 'keyword', in: 'query', required: false, description: 'Tìm theo họ tên, email, phone, mã nhân viên', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'status', in: 'query', required: false, description: 'Trạng thái (ACTIVE, LOCKED, PENDING)', schema: new OA\Schema(type: 'string', enum: ['ACTIVE', 'LOCKED', 'PENDING'])),
            new OA\Parameter(name: 'can_approve_adjustment', in: 'query', required: false, description: 'Lọc theo quyền duyệt điều chỉnh (true/false)', schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'building_id', in: 'query', required: false, description: 'Lọc theo ID tòa nhà phụ trách', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Lấy danh sách Kế toán thành công', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponseSuccess')),
            new OA\Response(response: 403, description: 'Không có quyền truy cập', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponseError')),
        ]
    )]
    public function index() {}

    #[OA\Post(
        path: '/api/v1/admin/users/accountants',
        tags: ['Accountants Management'],
        summary: '88. Tạo mới Kế toán',
        description: 'Tạo tài khoản Kế toán mới (full_name, email, phone, can_approve_adjustment, building_ids). Lưu ID Admin tạo.',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['full_name', 'email', 'password'],
                properties: [
                    new OA\Property(property: 'employee_code', type: 'string', example: 'NV-KT-005', nullable: true),
                    new OA\Property(property: 'full_name', type: 'string', example: 'Đỗ Thị Kế Toán'),
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'acc.ketoan@officeleasing.vn'),
                    new OA\Property(property: 'phone', type: 'string', example: '0933221100', nullable: true),
                    new OA\Property(property: 'password', type: 'string', format: 'password', example: 'AccPass@123'),
                    new OA\Property(property: 'can_approve_adjustment', type: 'boolean', default: false),
                    new OA\Property(property: 'status', type: 'string', enum: ['ACTIVE', 'LOCKED', 'PENDING'], default: 'ACTIVE'),
                    new OA\Property(property: 'building_ids', type: 'array', items: new OA\Items(type: 'string', format: 'uuid')),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Tạo Kế toán thành công', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponseSuccess')),
            new OA\Response(response: 422, description: 'Dữ liệu không hợp lệ', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponseError')),
        ]
    )]
    public function store() {}

    #[OA\Get(
        path: '/api/v1/admin/users/accountants/{id}',
        tags: ['Accountants Management'],
        summary: '89. Chi tiết Nhân viên Kế toán',
        description: 'Xem chi tiết tài khoản Kế toán và các tòa nhà đang phụ trách theo dõi công nợ. Accountant xem được trang profile của mình.',
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'UUID của Kế toán', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Lấy chi tiết thành công', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponseSuccess')),
            new OA\Response(response: 403, description: 'Không có quyền truy cập', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponseError')),
        ]
    )]
    public function show() {}

    #[OA\Put(
        path: '/api/v1/admin/users/accountants/{id}',
        tags: ['Accountants Management'],
        summary: '90. Cập nhật Kế toán',
        description: 'Cập nhật tài khoản Kế toán (quyền duyệt điều chỉnh, trạng thái làm việc, tòa nhà phụ trách).',
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'UUID của Kế toán', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'full_name', type: 'string', example: 'Đỗ Thị Kế Toán Đổi Tên'),
                    new OA\Property(property: 'phone', type: 'string', example: '0933229988'),
                    new OA\Property(property: 'can_approve_adjustment', type: 'boolean'),
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
        path: '/api/v1/admin/users/accountants/{id}',
        tags: ['Accountants Management'],
        summary: '91. Xóa mềm Kế toán',
        description: 'Xóa mềm tài khoản Kế toán.',
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'UUID của Kế toán', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Xóa nhân sự Kế toán thành công', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponseSuccess')),
        ]
    )]
    public function destroy() {}
}
