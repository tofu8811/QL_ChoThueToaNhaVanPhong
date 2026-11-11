<?php

namespace App\Docs\V1;

use OpenApi\Attributes as OA;

class BuildingDoc extends BaseDoc
{
    #[OA\Get(
        path: '/api/v1/portal/buildings',
        tags: ['Buildings (Public)'],
        summary: 'Danh sách Tòa nhà văn phòng chào thuê (Public Portal)',
        description: 'Tìm kiếm và lọc tòa nhà văn phòng công khai theo quận huyện, phân hạng A/B/C, khoảng giá, diện tích trống.',
        parameters: [
            new OA\QueryParameter(name: 'keyword', description: 'Từ khóa tên tòa nhà, địa chỉ'),
            new OA\QueryParameter(name: 'province_code', schema: new OA\Schema(type: 'integer')),
            new OA\QueryParameter(name: 'district_code', schema: new OA\Schema(type: 'integer')),
            new OA\QueryParameter(name: 'grade', schema: new OA\Schema(type: 'string', enum: ['A', 'B', 'C'])),
            new OA\QueryParameter(name: 'min_price', schema: new OA\Schema(type: 'number')),
            new OA\QueryParameter(name: 'max_price', schema: new OA\Schema(type: 'number')),
            new OA\QueryParameter(name: 'min_area', schema: new OA\Schema(type: 'number')),
            new OA\QueryParameter(name: 'max_area', schema: new OA\Schema(type: 'number')),
            new OA\QueryParameter(name: 'page', schema: new OA\Schema(type: 'integer', default: 1)),
            new OA\QueryParameter(name: 'per_page', schema: new OA\Schema(type: 'integer', default: 10)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Thành công', content: new OA\JsonContent(ref: '#/components/schemas/ApiPaginateResponse')),
            new OA\Response(response: 'default', description: 'Lỗi', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponseError')),
        ]
    )]
    public function portalIndex() {}

    #[OA\Get(
        path: '/api/v1/portal/buildings/{slug}',
        tags: ['Buildings (Public)'],
        summary: 'Xem chi tiết Tòa nhà văn phòng theo slug chuẩn SEO',
        parameters: [new OA\PathParameter(name: 'slug', description: 'Slug SEO tòa nhà')],
        responses: [
            new OA\Response(response: 200, description: 'Thành công', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponseSuccess')),
            new OA\Response(response: 404, description: 'Không tìm thấy', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponseError')),
        ]
    )]
    public function portalShow() {}

    #[OA\Get(
        path: '/api/v1/admin/buildings',
        tags: ['Buildings (Admin)'],
        summary: 'Danh sách Tòa nhà trong phân hệ Quản trị (Admin / BQL)',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\QueryParameter(name: 'keyword'),
            new OA\QueryParameter(name: 'grade'),
            new OA\QueryParameter(name: 'is_published', schema: new OA\Schema(type: 'boolean')),
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
        path: '/api/v1/admin/buildings',
        tags: ['Buildings (Admin)'],
        summary: 'Tạo mới Tòa nhà văn phòng',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/StoreBuildingInput')),
        responses: [
            new OA\Response(response: 201, description: 'Tạo mới thành công', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponseSuccess')),
            new OA\Response(response: 'default', description: 'Lỗi', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponseError')),
        ]
    )]
    public function store() {}

    #[OA\Get(
        path: '/api/v1/admin/buildings/{id}',
        tags: ['Buildings (Admin)'],
        summary: 'Xem chi tiết Tòa nhà phục vụ chỉnh sửa quản trị',
        security: [['bearerAuth' => []]],
        parameters: [new OA\PathParameter(name: 'id', description: 'UUID tòa nhà')],
        responses: [
            new OA\Response(response: 200, description: 'Thành công', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponseSuccess')),
            new OA\Response(response: 'default', description: 'Lỗi', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponseError')),
        ]
    )]
    public function show() {}

    #[OA\Put(
        path: '/api/v1/admin/buildings/{id}',
        tags: ['Buildings (Admin)'],
        summary: 'Cập nhật thông tin Tòa nhà',
        security: [['bearerAuth' => []]],
        parameters: [new OA\PathParameter(name: 'id', description: 'UUID tòa nhà')],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/StoreBuildingInput')),
        responses: [
            new OA\Response(response: 200, description: 'Cập nhật thành công', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponseSuccess')),
            new OA\Response(response: 'default', description: 'Lỗi', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponseError')),
        ]
    )]
    public function update() {}

    #[OA\Delete(
        path: '/api/v1/admin/buildings/{id}',
        tags: ['Buildings (Admin)'],
        summary: 'Xóa mềm Tòa nhà văn phòng',
        security: [['bearerAuth' => []]],
        parameters: [new OA\PathParameter(name: 'id', description: 'UUID tòa nhà')],
        responses: [
            new OA\Response(response: 200, description: 'Xóa thành công', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponseSuccess')),
            new OA\Response(response: 'default', description: 'Lỗi', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponseError')),
        ]
    )]
    public function destroy() {}

    #[OA\Post(
        path: '/api/v1/admin/buildings/{id}/images',
        tags: ['Buildings (Admin)'],
        summary: 'Tải lên hình ảnh Tòa nhà',
        security: [['bearerAuth' => []]],
        parameters: [new OA\PathParameter(name: 'id', description: 'UUID tòa nhà')],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    required: ['images'],
                    properties: [
                        new OA\Property(property: 'images', type: 'array', items: new OA\Items(type: 'string', format: 'binary')),
                        new OA\Property(property: 'caption', type: 'string', example: 'Mặt tiền sảnh chính'),
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
        path: '/api/v1/admin/buildings/{id}/images/{imageId}',
        tags: ['Buildings (Admin)'],
        summary: 'Xóa hình ảnh Tòa nhà',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\PathParameter(name: 'id', description: 'UUID tòa nhà'),
            new OA\PathParameter(name: 'imageId', schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Xóa ảnh thành công', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponseSuccess')),
            new OA\Response(response: 'default', description: 'Lỗi', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponseError')),
        ]
    )]
    public function deleteImage() {}

    #[OA\Patch(
        path: '/api/v1/admin/buildings/{id}/images/{imageId}/set-primary',
        tags: ['Buildings (Admin)'],
        summary: 'Đặt hình ảnh làm ảnh đại diện chính của Tòa nhà',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\PathParameter(name: 'id', description: 'UUID tòa nhà'),
            new OA\PathParameter(name: 'imageId', schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Thành công', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponseSuccess')),
            new OA\Response(response: 'default', description: 'Lỗi', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponseError')),
        ]
    )]
    public function setPrimaryImage() {}
}
