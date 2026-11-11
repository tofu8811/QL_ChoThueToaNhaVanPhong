<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Province;
use App\Services\LocationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

/**
 * Controller xử lý danh mục Địa giới Hành chính (Provinces).
 */
class ProvinceApiController extends BaseApiController
{
    public function __construct(
        protected LocationService $locationService
    ) {}

    // Lấy danh sách tất cả các tỉnh thành
    public function index(): JsonResponse
    {
        $provinces = Cache::rememberForever('provinces_all_list', function () {
            return Province::query()
                ->orderBy('name')
                ->get(['code', 'name', 'slug', 'division_type', 'codename', 'phone_code']);
        });

        return $this->successResponse($provinces, 'Lấy danh sách tỉnh thành thành công.');
    }

    // Lấy thông tin chi tiết và dữ liệu địa lý của tỉnh thành theo mã code
    public function show(int $code): JsonResponse
    {
        $province = Province::where('code', $code)->firstOrFail();
        $geoData = $this->locationService->getProvinceGeoData($code);

        return $this->successResponse([
            'province' => $province,
            'geo_details' => $geoData,
        ], 'Lấy thông tin tỉnh thành thành công.');
    }
}
