<?php

namespace App\Docs\V1;

use OpenApi\Attributes as OA;

class AdminUserDoc extends BaseDoc
{
    #[OA\Get(
        path: '/api/v1/admin/users/admins',
        tags: ['Admin Management'],
        summary: '72. Danh sách Quản trị viên (Admin)',
        description: 'Liệt kê danh sách các tài khoản Quản trị viên toàn hệ thống. Hỗ trợ phân trang, tìm kiếm theo tên, email, số điện thoại. Chỉ Super Admin mới có quyền truy cập.',
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'page', in: 'query', required: false, description: 'Số trang', schema: new OA\Schema(type: 'integer', default: 1)),
            new OA\Parameter(name: 'per_page', in: 'query', required: false, description: 'Số bản ghi mỗi trang', schema: new OA\Schema(type: 'integer', default: 10)),
            new OA\Parameter(name: 'keyword', in: 'query', required: false, description: 'Từ khóa tìm kiếm (họ tên, email, phone)', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'status', in: 'query', required: false, description: 'Trạng thái (ACTIVE, LOCKED, PENDING)', schema: new OA\Schema(type: 'string', enum: ['ACTIVE', 'LOCKED', 'PENDING'])),
            new OA\Parameter(name: 'is_super_admin', in: 'query', required: false, description: 'Lọc quyền Super Admin (true/false)', schema: new OA\Schema(type: 'boolean')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Lấy danh sách thành công', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponseSuccess')),
            new OA\Response(response: 403, description: 'Chỉ Super Admin mới có quyền truy cập', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponseError')),
        ]
    )]
    public function index() {}

    #[OA\Post(
        path: '/api/v1/admin/users/admins',
        tags: ['Admin Management'],
        summary: '73. Tạo mới Tài khoản Admin',
        description: 'Tạo tài khoản Admin mới (full_name, email, phone, password, is_super_admin, status). Mật khẩu được mã hóa bcrypt trước khi lưu. Không trả về hash password.',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['full_name', 'email', 'password'],
                properties: [
                    new OA\Property(property: 'full_name', type: 'string', example: 'Lê Văn Quản Trị'),
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'admin.le@officeleasing.vn'),
                    new OA\Property(property: 'phone', type: 'string', example: '0988776655', nullable: true),
                    new OA\Property(property: 'password', type: 'string', format: 'password', example: 'AdminPass@123'),
                    new OA\Property(property: 'is_super_admin', type: 'boolean', default: false),
                    new OA\Property(property: 'status', type: 'string', enum: ['ACTIVE', 'LOCKED', 'PENDING'], default: 'ACTIVE'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Tạo tài khoản admin thành công', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponseSuccess')),
            new OA\Response(response: 422, description: 'Dữ liệu đầu vào không hợp lệ', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponseError')),
        ]
    )]
    public function store() {}

    #[OA\Get(
        path: '/api/v1/admin/users/admins/{id}',
        tags: ['Admin Management'],
        summary: '74. Chi tiết Tài khoản Admin',
        description: 'Xem chi tiết một tài khoản Admin theo UUID v7.',
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'UUID v7 của tài khoản Admin', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Lấy thông tin thành công', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponseSuccess')),
            new OA\Response(response: 404, description: 'Không tìm thấy tài khoản admin', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponseError')),
        ]
    )]
    public function show() {}

    #[OA\Put(
        path: '/api/v1/admin/users/admins/{id}',
        tags: ['Admin Management'],
        summary: '75. Cập nhật Tài khoản Admin',
        description: 'Cập nhật thông tin tài khoản Admin (họ tên, số điện thoại, quyền super admin, trạng thái).',
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'UUID v7 của tài khoản Admin', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'full_name', type: 'string', example: 'Lê Văn Quản Trị Đã Đổi Tên'),
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'admin.le@officeleasing.vn'),
                    new OA\Property(property: 'phone', type: 'string', example: '0988776655'),
                    new OA\Property(property: 'is_super_admin', type: 'boolean'),
                    new OA\Property(property: 'status', type: 'string', enum: ['ACTIVE', 'LOCKED', 'PENDING']),
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
        path: '/api/v1/admin/users/admins/{id}',
        tags: ['Admin Management'],
        summary: '76. Xóa mềm Tài khoản Admin',
        description: 'Xóa mềm (Soft Delete) tài khoản Admin. Cấm tự xóa tài khoản của chính mình.',
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'UUID v7 của tài khoản Admin', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Xóa tài khoản thành công', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponseSuccess')),
            new OA\Response(response: 422, description: 'Không được phép tự xóa tài khoản của chính mình', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponseError')),
        ]
    )]
    public function destroy() {}
}
