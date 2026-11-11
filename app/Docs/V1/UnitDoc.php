<?php

namespace App\Docs\V1;

use OpenApi\Attributes as OA;

class UnitDoc extends BaseDoc
{
    #[OA\Get(
        path: '/api/v1/portal/units',
        tags: ['Units (Public)'],
        summary: 'Danh sách Mặt bằng văn phòng chào thuê khả dụng (Public Portal)',
        parameters: [
            new OA\QueryParameter(name: 'building_id'),
            new OA\QueryParameter(name: 'floor_number', schema: new OA\Schema(type: 'integer')),
            new OA\QueryParameter(name: 'min_area', schema: new OA\Schema(type: 'number')),
            new OA\QueryParameter(name: 'max_area', schema: new OA\Schema(type: 'number')),
            new OA\QueryParameter(name: 'min_price', schema: new OA\Schema(type: 'number')),
            new OA\QueryParameter(name: 'max_price', schema: new OA\Schema(type: 'number')),
            new OA\QueryParameter(name: 'page', schema: new OA\Schema(type: 'integer', default: 1)),
            new OA\QueryParameter(name: 'per_page', schema: new OA\Schema(type: 'integer', default: 12)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Thành công', content: new OA\JsonContent(ref: '#/components/schemas/ApiPaginateResponse')),
            new OA\Response(response: 'default', description: 'Lỗi', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponseError')),
        ]
    )]
    public function portalIndex() {}

    #[OA\Get(
        path: '/api/v1/portal/units/{id}',
        tags: ['Units (Public)'],
        summary: 'Chi tiết Mặt bằng văn phòng (Public Portal)',
        parameters: [new OA\PathParameter(name: 'id', description: 'UUID mặt bằng')],
        responses: [
            new OA\Response(response: 200, description: 'Thành công', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponseSuccess')),
            new OA\Response(response: 404, description: 'Không tìm thấy', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponseError')),
        ]
    )]
    public function portalShow() {}

    #[OA\Get(
        path: '/api/v1/admin/units',
        tags: ['Units (Admin)'],
        summary: 'Danh sách Quản lý Mặt bằng (Admin / BQL)',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\QueryParameter(name: 'building_id'),
            new OA\QueryParameter(name: 'unit_code'),
            new OA\QueryParameter(name: 'status', schema: new OA\Schema(type: 'string', enum: ['AVAILABLE', 'EXPIRING_SOON', 'LEASED', 'MAINTENANCE'])),
            new OA\QueryParameter(name: 'floor_number', schema: new OA\Schema(type: 'integer')),
            new OA\QueryParameter(name: 'page', schema: new OA\Schema(type: 'integer', default: 1)),
            new OA\QueryParameter(name: 'per_page', schema: new OA\Schema(type: 'integer', default: 15)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Thành công', content: new OA\JsonContent(ref: '#/components/schemas/ApiPaginateResponse')),
            new OA\Response(response: 'default', description: 'Lỗi', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponseError')),
        ]
    )]
    public function adminIndex() {}

    #[OA\Post(
        path: '/api/v1/admin/units',
        tags: ['Units (Admin)'],
        summary: 'Tạo mới Mặt bằng văn phòng',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/StoreUnitInput')),
        responses: [
            new OA\Response(response: 201, description: 'Tạo mới thành công', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponseSuccess')),
            new OA\Response(response: 'default', description: 'Lỗi', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponseError')),
        ]
    )]
    public function store() {}

    #[OA\Get(
        path: '/api/v1/admin/units/{id}',
        tags: ['Units (Admin)'],
        summary: 'Xem chi tiết kỹ thuật Mặt bằng',
        security: [['bearerAuth' => []]],
        parameters: [new OA\PathParameter(name: 'id', description: 'UUID mặt bằng')],
        responses: [
            new OA\Response(response: 200, description: 'Thành công', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponseSuccess')),
            new OA\Response(response: 'default', description: 'Lỗi', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponseError')),
        ]
    )]
    public function show() {}

    #[OA\Put(
        path: '/api/v1/admin/units/{id}',
        tags: ['Units (Admin)'],
        summary: 'Cập nhật thông số Mặt bằng',
        security: [['bearerAuth' => []]],
        parameters: [new OA\PathParameter(name: 'id', description: 'UUID mặt bằng')],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/StoreUnitInput')),
        responses: [
            new OA\Response(response: 200, description: 'Cập nhật thành công', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponseSuccess')),
            new OA\Response(response: 'default', description: 'Lỗi', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponseError')),
        ]
    )]
    public function update() {}

    #[OA\Delete(
        path: '/api/v1/admin/units/{id}',
        tags: ['Units (Admin)'],
        summary: 'Xóa mềm Mặt bằng văn phòng',
        security: [['bearerAuth' => []]],
        parameters: [new OA\PathParameter(name: 'id', description: 'UUID mặt bằng')],
        responses: [
            new OA\Response(response: 200, description: 'Xóa thành công', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponseSuccess')),
            new OA\Response(response: 'default', description: 'Lỗi', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponseError')),
        ]
    )]
    public function destroy() {}

    #[OA\Patch(
        path: '/api/v1/admin/units/{id}/status',
        tags: ['Units (Admin)'],
        summary: 'Cập nhật trạng thái Mặt bằng thủ công (Bảo trì / Sẵn sàng thuê)',
        security: [['bearerAuth' => []]],
        parameters: [new OA\PathParameter(name: 'id', description: 'UUID mặt bằng')],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['status'],
                properties: [
                    new OA\Property(property: 'status', type: 'string', example: 'MAINTENANCE', enum: ['AVAILABLE', 'EXPIRING_SOON', 'LEASED', 'MAINTENANCE']),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Thành công', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponseSuccess')),
            new OA\Response(response: 'default', description: 'Lỗi', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponseError')),
        ]
    )]
    public function updateStatus() {}

    #[OA\Post(
        path: '/api/v1/admin/units/{id}/images',
        tags: ['Units (Admin)'],
        summary: 'Tải lên ảnh chụp thực tế / Sơ đồ layout Mặt bằng',
        security: [['bearerAuth' => []]],
        parameters: [new OA\PathParameter(name: 'id', description: 'UUID mặt bằng')],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    required: ['images'],
                    properties: [
                        new OA\Property(property: 'images', type: 'array', items: new OA\Items(type: 'string', format: 'binary')),
                        new OA\Property(property: 'caption', type: 'string', example: 'Bản vẽ sơ đồ mặt bằng CAD 2D'),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Upload thành công', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponseSuccess')),
            new OA\Response(response: 'default', description: 'Lỗi', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponseError')),
        ]
    )]
    public function uploadImages() {}

    #[OA\Delete(
        path: '/api/v1/admin/units/{id}/images/{imageId}',
        tags: ['Units (Admin)'],
        summary: 'Xóa hình ảnh Mặt bằng',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\PathParameter(name: 'id', description: 'UUID mặt bằng'),
            new OA\PathParameter(name: 'imageId', schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Xóa thành công', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponseSuccess')),
            new OA\Response(response: 'default', description: 'Lỗi', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponseError')),
        ]
    )]
    public function deleteImage() {}
}
