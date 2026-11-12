<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\V1\Building\StoreBuildingRequest;
use App\Http\Requests\V1\Building\UpdateBuildingRequest;
use App\Models\Building;
use App\Services\BuildingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Controller xử lý tài nguyên Tòa nhà Văn phòng & Thư viện ảnh (Buildings & Media).
 */
class BuildingApiController extends BaseApiController
{
    public function __construct(
        protected BuildingService $buildingService
    ) {}

    // Lấy danh sách tòa nhà hiển thị công khai trên Portal
    public function portalIndex(Request $request): JsonResponse
    {
        $perPage = (int) $request->input('per_page', 10);
        $buildings = $this->buildingService->getPublicBuildings($request->all(), $perPage);

        return $this->paginateResponse($buildings, 'Lấy danh sách tòa nhà thành công.');
    }

    // Lấy chi tiết tòa nhà công khai theo slug
    public function portalShow(string $slug): JsonResponse
    {
        $building = $this->buildingService->getPublicBuildingBySlug($slug);

        return $this->successResponse($building, 'Lấy thông tin chi tiết tòa nhà thành công.');
    }

    // Lấy danh sách tòa nhà cho trang quản trị (Admin)
    public function adminIndex(Request $request): JsonResponse
    {
        $perPage = (int) $request->input('per_page', 15);
        $buildings = $this->buildingService->getAdminBuildings($request->all(), $request->user(), $perPage);

        return $this->paginateResponse($buildings, 'Lấy danh sách quản trị tòa nhà thành công.');
    }

    // Tạo mới một tòa nhà văn phòng
    public function store(StoreBuildingRequest $request): JsonResponse
    {
        $adminId = $request->user()?->id;
        $building = $this->buildingService->storeBuilding($request->validated(), $adminId);

        return $this->createdResponse($building, 'Khởi tạo tòa nhà văn phòng thành công.');
    }

    // Lấy thông tin chi tiết tòa nhà theo ID
    public function show(string $id): JsonResponse
    {
        $building = Building::with(['provinceRelation', 'images', 'primaryImage', 'units'])->findOrFail($id);

        return $this->successResponse($building, 'Lấy chi tiết tòa nhà thành công.');
    }

    // Cập nhật thông tin tòa nhà văn phòng
    public function update(UpdateBuildingRequest $request, string $id): JsonResponse
    {
        $building = Building::findOrFail($id);
        $updated = $this->buildingService->updateBuilding($building, $request->validated());

        return $this->successResponse($updated, 'Cập nhật thông tin tòa nhà thành công.');
    }

    // Xóa một tòa nhà văn phòng
    public function destroy(string $id): JsonResponse
    {
        $building = Building::findOrFail($id);
        $this->buildingService->deleteBuilding($building);

        return $this->successResponse(null, "Đã xóa tòa nhà [{$building->name}] thành công.");
    }

    // Tải lên danh sách hình ảnh cho tòa nhà
    public function uploadImages(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'images' => ['required', 'array', 'min:1'],
            'images.*' => ['required', 'file', 'image', 'max:5120'],
            'caption' => ['nullable', 'string', 'max:255'],
        ], [
            'images.required' => 'Vui lòng chọn ít nhất 1 tệp hình ảnh.',
            'images.*.image' => 'Tệp tải lên phải là định dạng hình ảnh hợp lệ.',
            'images.*.max' => 'Dung lượng mỗi ảnh tối đa là 5MB.',
        ]);

        $building = Building::findOrFail($id);
        $uploaded = $this->buildingService->uploadImages(
            $building,
            $request->file('images'),
            $request->input('caption')
        );

        return $this->createdResponse($uploaded, 'Tải lên hình ảnh tòa nhà thành công.');
    }

    // Xóa một hình ảnh của tòa nhà
    public function deleteImage(string $id, int $imageId): JsonResponse
    {
        $building = Building::findOrFail($id);
        $this->buildingService->deleteImage($building, $imageId);

        return $this->successResponse(null, 'Đã xóa hình ảnh tòa nhà thành công.');
    }

    // Đặt hình ảnh làm ảnh đại diện chính của tòa nhà
    public function setPrimaryImage(string $id, int $imageId): JsonResponse
    {
        $building = Building::findOrFail($id);
        $image = $this->buildingService->setPrimaryImage($building, $imageId);

        return $this->successResponse($image, 'Đã đặt hình ảnh làm ảnh đại diện chính thành công.');
    }
}
