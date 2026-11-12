<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\V1\Unit\StoreUnitRequest;
use App\Http\Requests\V1\Unit\UpdateUnitRequest;
use App\Models\Unit;
use App\Services\UnitService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Controller xử lý tài nguyên Mặt bằng sàn văn phòng & Layout (Units & Floorplans).
 */
class UnitApiController extends BaseApiController
{
    public function __construct(
        protected UnitService $unitService
    ) {}

    // Lấy danh sách mặt bằng hiển thị công khai trên Portal
    public function portalIndex(Request $request): JsonResponse
    {
        $perPage = (int) $request->input('per_page', 12);
        $units = $this->unitService->getPublicUnits($request->all(), $perPage);

        return $this->paginateResponse($units, 'Lấy danh sách mặt bằng thành công.');
    }

    // Lấy chi tiết mặt bằng công khai theo ID
    public function portalShow(string $id): JsonResponse
    {
        $unit = $this->unitService->getPublicUnitById($id);

        return $this->successResponse($unit, 'Lấy thông tin chi tiết mặt bằng thành công.');
    }

    // Lấy danh sách mặt bằng cho trang quản trị (Admin)
    public function adminIndex(Request $request): JsonResponse
    {
        $perPage = (int) $request->input('per_page', 15);
        $units = $this->unitService->getAdminUnits($request->all(), $request->user(), $perPage);

        return $this->paginateResponse($units, 'Lấy danh sách quản trị mặt bằng thành công.');
    }

    // Tạo mới một mặt bằng văn phòng
    public function store(StoreUnitRequest $request): JsonResponse
    {
        $pmId = $request->user()?->id;
        $unit = $this->unitService->storeUnit($request->validated(), $pmId);

        return $this->createdResponse($unit, 'Khởi tạo mặt bằng văn phòng thành công.');
    }

    // Lấy thông tin chi tiết mặt bằng theo ID
    public function show(string $id): JsonResponse
    {
        $unit = Unit::with(['building.provinceRelation', 'images', 'primaryImage', 'updatedByPropertyManager'])->findOrFail($id);

        return $this->successResponse($unit, 'Lấy chi tiết mặt bằng thành công.');
    }

    // Cập nhật thông tin mặt bằng văn phòng
    public function update(UpdateUnitRequest $request, string $id): JsonResponse
    {
        $unit = Unit::findOrFail($id);
        $pmId = $request->user()?->id;
        $updated = $this->unitService->updateUnit($unit, $request->validated(), $pmId);

        return $this->successResponse($updated, 'Cập nhật thông tin mặt bằng thành công.');
    }

    // Xóa một mặt bằng văn phòng
    public function destroy(string $id): JsonResponse
    {
        $unit = Unit::findOrFail($id);
        $this->unitService->deleteUnit($unit);

        return $this->successResponse(null, "Đã xóa mặt bằng [{$unit->unit_code}] thành công.");
    }

    // Cập nhật trạng thái thuê của mặt bằng
    public function updateStatus(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'status' => ['required', 'string'],
        ], [
            'status.required' => 'Trạng thái mặt bằng mới là bắt buộc.',
        ]);

        $unit = Unit::findOrFail($id);
        $pmId = $request->user()?->id;
        $updated = $this->unitService->updateStatus($unit, $request->input('status'), $pmId);

        return $this->successResponse($updated, "Đã cập nhật trạng thái mặt bằng sang [{$updated->status->label()}].");
    }

    // Tải lên danh sách hình ảnh cho mặt bằng
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

        $unit = Unit::findOrFail($id);
        $uploaded = $this->unitService->uploadImages(
            $unit,
            $request->file('images'),
            $request->input('caption')
        );

        return $this->createdResponse($uploaded, 'Tải lên hình ảnh mặt bằng thành công.');
    }

    // Xóa một hình ảnh của mặt bằng
    public function deleteImage(string $id, int $imageId): JsonResponse
    {
        $unit = Unit::findOrFail($id);
        $this->unitService->deleteImage($unit, $imageId);

        return $this->successResponse(null, 'Đã xóa hình ảnh mặt bằng thành công.');
    }
}
