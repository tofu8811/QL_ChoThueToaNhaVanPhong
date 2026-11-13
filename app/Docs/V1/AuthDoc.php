<?php

namespace App\Docs\V1;

use OpenApi\Attributes as OA;

class AuthDoc extends BaseDoc
{
    #[OA\Post(
        path: '/api/v1/auth/login',
        tags: ['Authentication'],
        summary: 'Đăng nhập đa tác nhân (Multi-guard Login)',
        description: 'Đăng nhập tài khoản đa tác nhân (Admin, Sales, Quản lý tòa nhà, Kế toán, Khách thuê) qua email và mật khẩu.',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email', 'password'],
                properties: [
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'admin@officeleasing.vn'),
                    new OA\Property(property: 'password', type: 'string', format: 'password', example: 'Admin@123456'),
                    new OA\Property(property: 'device_name', type: 'string', example: 'Windows Chrome', nullable: true),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Đăng nhập thành công, trả về Bearer Token và Role', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponseSuccess')),
            new OA\Response(response: 401, description: 'Email hoặc mật khẩu không chính xác', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponseError')),
            new OA\Response(response: 403, description: 'Tài khoản đã bị khóa hoặc chưa kích hoạt', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponseError')),
            new OA\Response(response: 422, description: 'Dữ liệu đầu vào không hợp lệ', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponseError')),
        ]
    )]
    public function login() {}

    #[OA\Post(
        path: '/api/v1/auth/logout',
        tags: ['Authentication'],
        summary: 'Đăng xuất tài khoản (Thu hồi token)',
        description: 'Thu hồi (revoke) token hiện tại khỏi cơ sở dữ liệu để vô hiệu hóa phiên làm việc ngay lập tức.',
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Đăng xuất thành công', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponseSuccess')),
            new OA\Response(response: 401, description: 'Chưa đăng nhập hoặc token không hợp lệ', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponseError')),
        ]
    )]
    public function logout() {}

    #[OA\Get(
        path: '/api/v1/auth/me',
        tags: ['Authentication'],
        summary: 'Xem hồ sơ cá nhân (User Profile)',
        description: 'Lấy thông tin chi tiết của người dùng đang đăng nhập (Profile, Role, danh sách ID các tòa nhà được phân quyền phụ trách).',
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Lấy thông tin người dùng thành công', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponseSuccess')),
            new OA\Response(response: 401, description: 'Chưa đăng nhập hoặc token không hợp lệ', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponseError')),
        ]
    )]
    public function me() {}

    #[OA\Post(
        path: '/api/v1/auth/refresh',
        tags: ['Authentication'],
        summary: 'Làm mới phiên làm việc (Refresh Token)',
        description: 'Làm mới phiên làm việc: Hủy token cũ và cấp token mới kéo dài hạn đăng nhập.',
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Làm mới token thành công', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponseSuccess')),
            new OA\Response(response: 401, description: 'Chưa đăng nhập hoặc token không hợp lệ', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponseError')),
        ]
    )]
    public function refresh() {}

    #[OA\Put(
        path: '/api/v1/auth/password',
        tags: ['Authentication'],
        summary: 'Đổi mật khẩu tài khoản cá nhân',
        description: 'Đổi mật khẩu tài khoản cá nhân. Yêu cầu nhập đúng mật khẩu hiện tại và xác nhận mật khẩu mới.',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['current_password', 'password', 'password_confirmation'],
                properties: [
                    new OA\Property(property: 'current_password', type: 'string', format: 'password', example: 'OldPassword@123'),
                    new OA\Property(property: 'password', type: 'string', format: 'password', example: 'NewPassword@123'),
                    new OA\Property(property: 'password_confirmation', type: 'string', format: 'password', example: 'NewPassword@123'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Đổi mật khẩu thành công', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponseSuccess')),
            new OA\Response(response: 401, description: 'Chưa đăng nhập hoặc token không hợp lệ', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponseError')),
            new OA\Response(response: 422, description: 'Mật khẩu hiện tại không đúng hoặc mật khẩu mới không đạt chuẩn', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponseError')),
        ]
    )]
    public function updatePassword() {}
}
