<?php

namespace App\Services;

use App\Enums\UnitStatus;
use App\Exceptions\BusinessException;
use App\Models\Building;
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
    public function __construct(
        protected BuildingService $buildingService
    ) {}
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

        // Xác định đối tượng người dùng (Ưu tiên Sanctum Auth; Fallback sang Header / Demo khi chưa đăng nhập)
        if (!$user) {
            $user = auth('sanctum')->user() ?? request()->user();
        }
        if (!$user && request()->header('X-User-Role')) {
            $hdrRole = strtolower((string) request()->header('X-User-Role'));
            $hdrId = (string) request()->header('X-User-Id');
            if (in_array($hdrRole, ['sales', 'sale'])) {
                $user = ($hdrId ? \App\Models\Sale::find($hdrId) : null)
                    ?? \App\Models\Sale::first();
            } elseif (in_array($hdrRole, ['pm', 'property_manager'])) {
                $user = ($hdrId ? \App\Models\PropertyManager::find($hdrId) : null)
                    ?? \App\Models\PropertyManager::first();
            } elseif (in_array($hdrRole, ['accountant', 'accountants'])) {
                $user = ($hdrId ? \App\Models\Accountant::find($hdrId) : null)
                    ?? \App\Models\Accountant::first();
            } elseif ($hdrRole === 'tenant') {
                $user = ($hdrId ? \App\Models\Tenant::find($hdrId) : null)
                    ?? \App\Models\Tenant::first();
            } elseif (in_array($hdrRole, ['admin', 'super_admin'])) {
                $user = ($hdrId ? \App\Models\Admin::find($hdrId) : null)
                    ?? \App\Models\Admin::first();
            }
        }

        // Phân quyền theo tòa nhà cho từng vai trò:
        if ($user instanceof \App\Models\Sale) {
            $assignedIds = \Illuminate\Support\Facades\DB::table('sale_buildings')
                ->where('sales_id', $user->id)
                ->where('is_active', true)
                ->pluck('building_id')
                ->toArray();
            $query->whereIn('building_id', $assignedIds);
        } elseif ($user instanceof \App\Models\PropertyManager) {
            $assignedIds = \Illuminate\Support\Facades\DB::table('manager_buildings')
                ->where('pm_id', $user->id)
                ->pluck('building_id')
                ->toArray();
            $query->whereIn('building_id', $assignedIds);
        } elseif ($user instanceof \App\Models\Accountant) {
            $assignedIds = \Illuminate\Support\Facades\DB::table('account_buildings')
                ->where('accountant_id', $user->id)
                ->pluck('building_id')
                ->toArray();
            $query->whereIn('building_id', $assignedIds);
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
     * Kiểm tra vị trí tầng có phù hợp với quy mô số tầng và tầng hầm của tòa nhà hay không.
     *
     * @param Building $building
     * @param int $floorNumber
     * @return void
     * @throws BusinessException
     */
    public function validateFloorNumber(Building $building, int $floorNumber): void
    {
        if ($floorNumber === 0) {
            $rangeText = "từ tầng 1 đến tầng {$building->total_floors}";
            if ($building->total_basements > 0) {
                $rangeText .= " hoặc tầng hầm từ B1 đến B{$building->total_basements} (nhập -1 đến -{$building->total_basements})";
            }
            throw new BusinessException("Vị trí tầng không hợp lệ (tòa nhà không có tầng 0). Vui lòng chọn {$rangeText}.", 422);
        }

        if ($floorNumber > 0 && $floorNumber > (int) $building->total_floors) {
            throw new BusinessException(
                "Vị trí tầng [{$floorNumber}] vượt quá số tầng của tòa nhà [{$building->name}] (quy mô tối đa {$building->total_floors} tầng nổi).",
                422
            );
        }

        if ($floorNumber < 0) {
            $basements = (int) ($building->total_basements ?? 0);
            if ($basements <= 0) {
                throw new BusinessException(
                    "Tòa nhà [{$building->name}] không có tầng hầm. Vị trí tầng phải từ 1 đến {$building->total_floors}.",
                    422
                );
            }

            if (abs($floorNumber) > $basements) {
                throw new BusinessException(
                    "Vị trí tầng hầm [{$floorNumber}] vượt quá số tầng hầm của tòa nhà [{$building->name}] (tối đa {$basements} tầng hầm: B1 đến B{$basements}).",
                    422
                );
            }
        }
    }

    /**
     * Tự động sinh mã sàn (unit_code) duy nhất theo vị trí tầng và tòa nhà.
     * Quét cả bản ghi đã xóa mềm (withTrashed) để triệt tiêu nguy cơ trùng lặp.
     *
     * @param string $buildingId
     * @param int $floorNumber
     * @return string
     * @throws BusinessException
     */
    public function generateUnitCode(string $buildingId, int $floorNumber): string
    {
        $building = Building::withTrashed()->findOrFail($buildingId);
        $this->validateFloorNumber($building, $floorNumber);

        // Lấy danh sách các mã sàn hiện có trong tòa nhà (kể cả xóa mềm)
        $existingCodes = Unit::withTrashed()
            ->where('building_id', $buildingId)
            ->pluck('unit_code');

        // Xác định định dạng tiền tố tùy chỉnh nếu tòa nhà đã có các sàn theo mã riêng (VD: NEX-0501)
        $customPrefix = null;
        foreach ($existingCodes as $c) {
            if (preg_match('/^([A-Za-z0-9]+)-(\d{4})$/', (string) $c, $m)) {
                $customPrefix = strtoupper($m[1]);
                break;
            }
        }

        // Tìm số thứ tự lớn nhất hiện có trên tầng này
        $maxSeq = 0;
        foreach ($existingCodes as $c) {
            if ($floorNumber > 0) {
                if ($customPrefix && preg_match('/^' . preg_quote($customPrefix, '/') . '-' . sprintf('%02d', $floorNumber) . '(\d{2})$/', (string) $c, $m)) {
                    $s = (int) $m[1];
                    if ($s > $maxSeq) $maxSeq = $s;
                } elseif (preg_match('/^L' . sprintf('%02d', $floorNumber) . '-(\d+)$/i', (string) $c, $m)) {
                    $s = (int) $m[1];
                    if ($s > $maxSeq) $maxSeq = $s;
                }
            } else {
                $absFloor = abs($floorNumber);
                if ($customPrefix && preg_match('/^' . preg_quote($customPrefix, '/') . '-B' . $absFloor . '(\d{2})$/', (string) $c, $m)) {
                    $s = (int) $m[1];
                    if ($s > $maxSeq) $maxSeq = $s;
                } elseif (preg_match('/^B' . sprintf('%02d', $absFloor) . '-(\d+)$/i', (string) $c, $m)) {
                    $s = (int) $m[1];
                    if ($s > $maxSeq) $maxSeq = $s;
                }
            }
        }

        $seq = $maxSeq;
        do {
            $seq++;
            if ($floorNumber > 0) {
                $candidate = $customPrefix 
                    ? sprintf('%s-%02d%02d', $customPrefix, $floorNumber, $seq)
                    : sprintf('L%02d-%02d', $floorNumber, $seq);
            } else {
                $candidate = $customPrefix 
                    ? sprintf('%s-B%d%02d', $customPrefix, abs($floorNumber), $seq)
                    : sprintf('B%02d-%02d', abs($floorNumber), $seq);
            }
        } while (Unit::withTrashed()->where('building_id', $buildingId)->where('unit_code', $candidate)->exists());

        return $candidate;
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
        $building = Building::withTrashed()->findOrFail($data['building_id']);

        // 1. Kiểm tra tính hợp lệ của tầng so với số tầng tòa nhà
        $this->validateFloorNumber($building, (int) $data['floor_number']);

        // 2. Tự động sinh mã sàn nếu để trống hoặc null
        if (empty($data['unit_code'])) {
            $data['unit_code'] = $this->generateUnitCode($data['building_id'], (int) $data['floor_number']);
        }

        // 3. Kiểm tra unique composite (building_id, unit_code) bao gồm cả bản ghi đã xóa mềm
        $exists = Unit::withTrashed()
            ->where('building_id', $data['building_id'])
            ->where('unit_code', $data['unit_code'])
            ->exists();

        if ($exists) {
            throw new BusinessException("Mã mặt bằng [{$data['unit_code']}] đã tồn tại trong tòa nhà này.", 422);
        }

        if (empty($data['status'])) {
            $data['status'] = UnitStatus::AVAILABLE;
        }

        if (empty($data['handover_condition'])) {
            $data['handover_condition'] = 'BASIC';
        }

        if (empty($data['name'])) {
            $data['name'] = "Văn phòng {$data['unit_code']}";
        }

        if ($pmId) {
            $data['updated_by_pm_id'] = $pmId;
        }

        $data['last_status_changed_at'] = now();

        $unit = Unit::create($data);
        $this->buildingService->syncRentPriceRange($unit->building_id);

        return $unit;
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
        // 1. Kiểm tra hợp đồng còn hiệu lực: Khóa toàn diện mọi hành vi chỉnh sửa (Total Freeze)
        $hasActiveLease = ContractUnit::where('unit_id', $unit->id)
            ->whereHas('contract', function ($q) {
                $q->whereNotIn('status', ['TERMINATED', 'EXPIRED']);
            })
            ->exists();

        if ($hasActiveLease) {
            throw new BusinessException(
                "Mặt bằng [{$unit->unit_code}] đang có hợp đồng thuê còn hiệu lực. Nghiêm cấm chỉnh sửa thông tin.",
                422
            );
        }

        // 2. Kiểm tra vị trí tầng nếu có thay đổi
        if (isset($data['floor_number'])) {
            $building = $unit->building ?: Building::withTrashed()->findOrFail($unit->building_id);
            $this->validateFloorNumber($building, (int) $data['floor_number']);
        }

        // 3. Kiểm tra mã phòng trùng nếu có đổi unit_code
        if (!empty($data['unit_code']) && $data['unit_code'] !== $unit->unit_code) {
            $exists = Unit::withTrashed()
                ->where('building_id', $unit->building_id)
                ->where('unit_code', $data['unit_code'])
                ->where('id', '!=', $unit->id)
                ->exists();

            if ($exists) {
                throw new BusinessException("Mã mặt bằng [{$data['unit_code']}] đã tồn tại trong tòa nhà này.", 422);
            }
        }

        if ($pmId) {
            $data['updated_by_pm_id'] = $pmId;
        }

        $unit->update($data);
        $this->buildingService->syncRentPriceRange($unit->building_id);

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

        $buildingId = $unit->building_id;
        $deleted = (bool) $unit->delete();
        if ($deleted) {
            $this->buildingService->syncRentPriceRange($buildingId);
        }

        return $deleted;
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

        // Nếu mặt bằng đang có hợp đồng hiệu lực: Chặn chuyển sang bất kỳ trạng thái nào khác LEASED
        $hasActiveLease = ContractUnit::where('unit_id', $unit->id)
            ->whereHas('contract', function ($q) {
                $q->whereNotIn('status', ['TERMINATED', 'EXPIRED']);
            })
            ->exists();

        if ($hasActiveLease && $statusEnum !== UnitStatus::LEASED) {
            throw new BusinessException(
                "Mặt bằng [{$unit->unit_code}] đang có hợp đồng thuê còn hiệu lực. Nghiêm cấm thay đổi trạng thái khi chưa hoàn tất thanh lý hợp đồng.",
                422
            );
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
        $hasPrimary = $unit->images()->where('is_primary', true)->exists();
        $currentOrder = (int) ($unit->images()->max('display_order') ?? 0);

        foreach ($files as $index => $file) {
            $filename = Str::uuid()->toString() . '.' . $file->getClientOriginalExtension();
            $path = "units/{$unit->id}/{$filename}";

            try {
                if (config('filesystems.disks.supabase.key')) {
                    $mime = method_exists($file, 'getClientMimeType') ? $file->getClientMimeType() : 'image/jpeg';
                    $stream = fopen($file->getRealPath(), 'r');
                    Storage::disk('supabase')->put($path, $stream, [
                        'mimetype' => $mime ?: 'image/jpeg',
                        'visibility' => 'public',
                    ]);
                    if (is_resource($stream)) {
                        fclose($stream);
                    }
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

            $isPrimary = (!$hasPrimary && $index === 0);
            if ($isPrimary) {
                $hasPrimary = true;
            }

            $currentOrder++;
            $image = UnitImage::create([
                'unit_id' => $unit->id,
                'image_url' => $url,
                'caption' => $caption ?? $file->getClientOriginalName(),
                'is_primary' => $isPrimary,
                'display_order' => $currentOrder,
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
