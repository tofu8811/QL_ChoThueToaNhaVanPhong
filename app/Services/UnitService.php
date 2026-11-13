<?php

namespace App\Services;

use App\Enums\UnitStatus;
use App\Exceptions\BusinessException;
use App\Models\ContractUnit;
use App\Models\Unit;
use App\Models\UnitImage;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Service tầng nghiệp vụ (BLL) phụ trách quản trị Mặt bằng sàn văn phòng & Layout.
 */
class UnitService
{
    /**
     * Lấy danh sách mặt bằng văn phòng chào thuê trên Public Portal.
     *
     * @param array<string, mixed> $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getPublicUnits(array $filters = [], int $perPage = 12): LengthAwarePaginator
    {
        $query = Unit::query()
            ->whereIn('status', [UnitStatus::AVAILABLE, UnitStatus::EXPIRING_SOON])
            ->whereHas('building', function ($q) {
                $q->published();
            })
            ->with(['building.provinceRelation', 'images', 'primaryImage']);

        if (!empty($filters['building_id'])) {
            $query->where('building_id', $filters['building_id']);
        }

        if (!empty($filters['floor_number'])) {
            $query->where('floor_number', (int) $filters['floor_number']);
        }

        if (!empty($filters['min_area'])) {
            $query->where('area_sqm', '>=', (float) $filters['min_area']);
        }
        if (!empty($filters['max_area'])) {
            $query->where('area_sqm', '<=', (float) $filters['max_area']);
        }

        if (!empty($filters['min_price'])) {
            $query->where('base_rental_price', '>=', (float) $filters['min_price']);
        }
        if (!empty($filters['max_price'])) {
            $query->where('base_rental_price', '<=', (float) $filters['max_price']);
        }

        if (!empty($filters['handover_condition'])) {
            $query->where('handover_condition', $filters['handover_condition']);
        }

        if (!empty($filters['direction'])) {
            $query->where('direction', $filters['direction']);
        }

        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = strtolower($filters['sort_order'] ?? 'desc') === 'asc' ? 'asc' : 'desc';

        if (in_array($sortBy, ['created_at', 'area_sqm', 'base_rental_price', 'floor_number'])) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->latest();
        }

        return $query->paginate($perPage);
    }

    /**
     * Lấy chi tiết mặt bằng sàn văn phòng trên Public Portal.
     *
     * @param string $id
     * @return Unit
     */
    public function getPublicUnitById(string $id): Unit
    {
        return Unit::query()
            ->whereIn('status', [UnitStatus::AVAILABLE, UnitStatus::EXPIRING_SOON])
            ->where('id', $id)
            ->with(['building.provinceRelation', 'images', 'primaryImage'])
            ->firstOrFail();
    }

    /**
     * Danh sách toàn bộ mặt bằng trong phân hệ Quản trị (Admin/BQL).
     *
     * @param array<string, mixed> $filters
     * @param mixed $user
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getAdminUnits(array $filters = [], $user = null, int $perPage = 15): LengthAwarePaginator
    {
        $query = Unit::query()
            ->with(['building.provinceRelation', 'primaryImage', 'updatedByPropertyManager']);

        // Phân quyền theo tòa nhà nếu không phải Super Admin
        if ($user && method_exists($user, 'isSuperAdmin') && !$user->isSuperAdmin()) {
            if (method_exists($user, 'assignedBuildings')) {
                $assignedIds = $user->assignedBuildings()->pluck('buildings.id')->toArray();
                $query->whereIn('building_id', $assignedIds);
            }
        }

        if (!empty($filters['building_id'])) {
            $query->where('building_id', $filters['building_id']);
        }

        if (!empty($filters['unit_code'])) {
            $query->where('unit_code', 'like', "%{$filters['unit_code']}%");
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['floor_number']) && $filters['floor_number'] !== '') {
            $query->where('floor_number', (int) $filters['floor_number']);
        }

        return $query->latest()->paginate($perPage);
    }

    /**
     * Thêm mới mặt bằng văn phòng vào tòa nhà.
     *
     * @param array<string, mixed> $data
     * @param string|null $pmId
     * @return Unit
     * @throws BusinessException
     */
    public function storeUnit(array $data, ?string $pmId = null): Unit
    {
        // Kiểm tra unique composite (building_id, unit_code)
        $exists = Unit::where('building_id', $data['building_id'])
            ->where('unit_code', $data['unit_code'])
            ->exists();

        if ($exists) {
            throw new BusinessException("Mã mặt bằng [{$data['unit_code']}] đã tồn tại trong tòa nhà này.", 422);
        }

        if (empty($data['status'])) {
            $data['status'] = UnitStatus::AVAILABLE;
        }

        if (empty($data['name'])) {
            $data['name'] = "Văn phòng {$data['unit_code']}";
        }

        if ($pmId) {
            $data['updated_by_pm_id'] = $pmId;
        }

        $data['last_status_changed_at'] = now();

        return Unit::create($data);
    }

    /**
     * Cập nhật thông số mặt bằng văn phòng.
     * Kiểm tra ràng buộc: Cấm sửa diện tích nếu mặt bằng đang có hợp đồng hiệu lực.
     *
     * @param Unit $unit
     * @param array<string, mixed> $data
     * @param string|null $pmId
     * @return Unit
     * @throws BusinessException
     */
    public function updateUnit(Unit $unit, array $data, ?string $pmId = null): Unit
    {
        // Kiểm tra mã phòng trùng nếu có đổi unit_code
        if (!empty($data['unit_code']) && $data['unit_code'] !== $unit->unit_code) {
            $exists = Unit::where('building_id', $unit->building_id)
                ->where('unit_code', $data['unit_code'])
                ->where('id', '!=', $unit->id)
                ->exists();

            if ($exists) {
                throw new BusinessException("Mã mặt bằng [{$data['unit_code']}] đã tồn tại trong tòa nhà này.", 422);
            }
        }

        // Kiểm tra sửa diện tích khi đang có hợp đồng
        if (isset($data['area_sqm']) && (float) $data['area_sqm'] !== (float) $unit->area_sqm) {
            $hasActiveLease = ContractUnit::where('unit_id', $unit->id)
                ->whereHas('contract', function ($q) {
                    $q->whereNotIn('status', ['TERMINATED', 'EXPIRED']);
                })
                ->exists();

            if ($hasActiveLease) {
                throw new BusinessException(
                    "Mặt bằng [{$unit->unit_code}] đang có hợp đồng thuê còn hiệu lực. Nghiêm cấm chỉnh sửa diện tích cho thuê.",
                    422
                );
            }
        }

        if ($pmId) {
            $data['updated_by_pm_id'] = $pmId;
        }

        $unit->update($data);
        return $unit->fresh(['building.provinceRelation', 'images', 'primaryImage']);
    }

    /**
     * Xóa mềm mặt bằng văn phòng.
     * Kiểm tra ràng buộc: Chặn xóa nếu có hợp đồng thuê hoặc công nợ.
     *
     * @param Unit $unit
     * @return bool
     * @throws BusinessException
     */
    public function deleteUnit(Unit $unit): bool
    {
        $hasActiveContracts = ContractUnit::where('unit_id', $unit->id)
            ->whereHas('contract', function ($q) {
                $q->whereNotIn('status', ['TERMINATED', 'EXPIRED']);
            })
            ->exists();

        if ($hasActiveContracts) {
            throw new BusinessException(
                "Không thể xóa mặt bằng [{$unit->unit_code}] vì đang gắn với hợp đồng thuê còn hiệu lực.",
                422
            );
        }

        return (bool) $unit->delete();
    }

    /**
     * Cập nhật trạng thái mặt bằng thủ công (Bảo trì / Sẵn sàng thuê).
     *
     * @param Unit $unit
     * @param string $newStatus
     * @param string|null $pmId
     * @return Unit
     * @throws BusinessException
     */
    public function updateStatus(Unit $unit, string $newStatus, ?string $pmId = null): Unit
    {
        $statusEnum = UnitStatus::tryFrom(strtoupper($newStatus));
        if (!$statusEnum) {
            throw new BusinessException("Trạng thái mặt bằng [{$newStatus}] không hợp lệ.", 422);
        }

        // Nếu chuyển sang AVAILABLE, kiểm tra xem còn hợp đồng hiệu lực không
        if ($statusEnum === UnitStatus::AVAILABLE) {
            $hasActiveLease = ContractUnit::where('unit_id', $unit->id)
                ->whereHas('contract', function ($q) {
                    $q->whereNotIn('status', ['TERMINATED', 'EXPIRED']);
                })
                ->exists();

            if ($hasActiveLease) {
                throw new BusinessException(
                    "Không thể chuyển mặt bằng [{$unit->unit_code}] sang trạng thái 'Đang trống' vì hợp đồng thuê hiện tại chưa được thanh lý.",
                    422
                );
            }
        }

        $unit->update([
            'status' => $statusEnum,
            'last_status_changed_at' => now(),
            'updated_by_pm_id' => $pmId ?? $unit->updated_by_pm_id,
        ]);

        return $unit->fresh();
    }

    /**
     * Tải lên hình ảnh mặt bằng / sơ đồ layout sàn.
     *
     * @param Unit $unit
     * @param array<UploadedFile> $files
     * @param string|null $caption
     * @return array<UnitImage>
     */
    public function uploadImages(Unit $unit, array $files, ?string $caption = null): array
    {
        $uploaded = [];

        foreach ($files as $index => $file) {
            $filename = Str::uuid()->toString() . '.' . $file->getClientOriginalExtension();
            $path = "units/{$unit->id}/{$filename}";

            try {
                if (config('filesystems.disks.supabase.key')) {
                    $mime = method_exists($file, 'getClientMimeType') ? $file->getClientMimeType() : null;
                    Storage::disk('supabase')->put($path, file_get_contents($file), [
                        'mimetype' => $mime ?: 'image/jpeg',
                        'visibility' => 'public',
                    ]);
                    $url = Storage::disk('supabase')->url($path);
                } else {
                    $localPath = $file->storeAs("public/units/{$unit->id}", $filename);
                    $url = Storage::url($localPath);
                }
            } catch (\Throwable $e) {
                Log::warning("Supabase upload failed, fallback to local storage: " . $e->getMessage());
                $localPath = $file->storeAs("public/units/{$unit->id}", $filename);
                $url = Storage::url($localPath);
            }

            $hasPrimary = $unit->images()->where('is_primary', true)->exists();
            $isPrimary = (!$hasPrimary && $index === 0);

            $image = UnitImage::create([
                'unit_id' => $unit->id,
                'image_url' => $url,
                'caption' => $caption ?? $file->getClientOriginalName(),
                'is_primary' => $isPrimary,
                'display_order' => $unit->images()->count() + 1,
            ]);

            $uploaded[] = $image;
        }

        return $uploaded;
    }

    /**
     * Xóa một hình ảnh mặt bằng.
     *
     * @param Unit $unit
     * @param int $imageId
     * @return bool
     */
    public function deleteImage(Unit $unit, int $imageId): bool
    {
        $image = UnitImage::where('unit_id', $unit->id)
            ->where('id', $imageId)
            ->firstOrFail();

        return (bool) $image->delete();
    }
}
