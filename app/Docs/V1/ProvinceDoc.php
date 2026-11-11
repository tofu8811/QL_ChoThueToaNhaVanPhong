<?php

namespace App\Docs\V1;

use OpenApi\Attributes as OA;

class ProvinceDoc extends BaseDoc
{
    #[OA\Get(
        path: '/api/v1/provinces',
        tags: ['Provinces'],
        summary: 'Lấy danh mục tất cả Tỉnh / Thành phố',
        description: 'Trả về danh sách 63 tỉnh/thành phố phục vụ bộ lọc tìm kiếm và dropdown địa chỉ.',
        responses: [
            new OA\Response(response: 200, description: 'Lấy danh sách tỉnh thành thành công', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponseSuccess')),
            new OA\Response(response: 500, description: 'Lỗi máy chủ nội bộ', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponseError')),
        ]
    )]
    public function index() {}

    #[OA\Get(
        path: '/api/v1/provinces/{code}',
        tags: ['Provinces'],
        summary: 'Lấy chi tiết một Tỉnh / Thành phố kèm dữ liệu địa giới',
        description: 'Trả về thông tin chi tiết tỉnh/thành kèm danh sách quận huyện / phường xã từ bộ nhớ đệm Geo.',
        parameters: [
            new OA\Parameter(
                name: 'code',
                in: 'path',
                required: true,
                description: 'Mã code hành chính của tỉnh (VD: 79)',
                schema: new OA\Schema(type: 'integer', example: 79)
            ),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Lấy thông tin tỉnh thành thành công', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponseSuccess')),
            new OA\Response(response: 404, description: 'Không tìm thấy tỉnh thành', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponseError')),
        ]
    )]
    public function show() {}
}
