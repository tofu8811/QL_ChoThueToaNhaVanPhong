<?php

namespace App\Services;

use App\Exceptions\BusinessException;
use App\Helpers\StorageUploadHelper;
use App\Models\Building;
use App\Models\BuildingImage;
use App\Models\Contract;
use App\Models\ContractStatus;
use App\Models\Unit;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Service tầng nghiệp vụ (BLL) phụ trách quản trị Tòa nhà Văn phòng & Media.
 */
class BuildingService
{
    /**
     * Lấy danh sách tòa nhà hiển thị trên Public Portal kèm bộ lọc đa tiêu chí.
     *
     * @param array<string, mixed> $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getPublicBuildings(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $query = Building::query()
            ->published()
            ->with(['provinceRelation', 'images', 'primaryImage'])
            ->withCount([
                'units as available_units_count' => function ($q) {
                    $q->whereIn('status', ['AVAILABLE', 'EXPIRING_SOON']);
                }
            ]);

        // 1. Lọc theo từ khóa tìm kiếm (Tên tòa nhà, địa chỉ, mã)
        if (!empty($filters['keyword'])) {
            $keyword = trim($filters['keyword']);
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                  ->orWhere('building_code', 'like', "%{$keyword}%")
                  ->orWhere('address', 'like', "%{$keyword}%")
                  ->orWhere('district', 'like', "%{$keyword}%");
            });
        }

        // 2. Lọc theo Tỉnh / Thành phố
        if (!empty($filters['province_code'])) {
            $query->where('province_code', (int) $filters['province_code']);
        } elseif (!empty($filters['province']) && $filters['province'] !== 'all') {
            if (is_numeric($filters['province'])) {
                $query->where('province_code', (int) $filters['province']);
            } else {
                $query->where(function ($q) use ($filters) {
                    $q->where('province', 'like', "%{$filters['province']}%");
                });
            }
        }

        // 3. Lọc theo Quận / Huyện / Phường (Hỗ trợ địa chỉ sau sáp nhập e.g. "Phường Sài Gòn (Quận 1)")
        if (!empty($filters['district_code'])) {
            $query->where('district_code', (int) $filters['district_code']);
        } elseif (!empty($filters['district']) && $filters['district'] !== 'all') {
            $distVal = trim($filters['district']);
            if (preg_match('/^(.*?)\s*\((.*?)\)$/', $distVal, $m)) {
                $wardPart = trim($m[1]);
                $districtPart = trim($m[2]);
                $query->where(function ($q) use ($districtPart, $wardPart) {
                    $q->where('district', 'like', "%{$districtPart}%")
                      ->orWhere('ward', 'like', "%{$wardPart}%")
                      ->orWhere('address', 'like', "%{$wardPart}%")
                      ->orWhere('address', 'like', "%{$districtPart}%");
                });
            } else {
                $query->where(function ($q) use ($distVal) {
                    $q->where('district', 'like', "%{$distVal}%")
                      ->orWhere('ward', 'like', "%{$distVal}%")
                      ->orWhere('address', 'like', "%{$distVal}%");
                });
            }
        }

        // 4. Lọc theo Phân hạng văn phòng (Grade A/B/C)
        if (!empty($filters['grade']) && $filters['grade'] !== 'all') {
            $cleanedGrade = strtoupper(trim(str_replace(['Hạng ', 'hạng ', 'Tòa nhà ', 'Văn phòng '], '', $filters['grade'])));
            $query->where('grade', $cleanedGrade);
        }

        // 5. Lọc theo khoảng giá thuê (VND/m2/tháng)
        if (!empty($filters['min_price'])) {
            $query->where('min_rent_price_per_sqm', '>=', (float) $filters['min_price']);
        }
        if (!empty($filters['max_price'])) {
            $query->where('max_rent_price_per_sqm', '<=', (float) $filters['max_price']);
        }

        // 6. Lọc theo khoảng diện tích trống
        if (!empty($filters['min_area']) || !empty($filters['max_area'])) {
            $query->whereHas('units', function ($q) use ($filters) {
                $q->whereIn('status', ['AVAILABLE', 'EXPIRING_SOON']);
                if (!empty($filters['min_area'])) {
                    $q->where('area_sqm', '>=', (float) $filters['min_area']);
                }
                if (!empty($filters['max_area'])) {
                    $q->where('area_sqm', '<=', (float) $filters['max_area']);
                }
            });
        }

        // Sắp xếp
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = strtolower($filters['sort_order'] ?? 'desc') === 'asc' ? 'asc' : 'desc';

        if (in_array($sortBy, ['created_at', 'name', 'min_rent_price_per_sqm', 'total_leasable_area_sqm'])) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->latest();
        }

        return $query->paginate($perPage);
    }

    /**
     * Lấy chi tiết tòa nhà theo slug phục vụ SEO trên Public Portal.
     *
     * @param string $slug
     * @return Building
     */
    public function getPublicBuildingBySlug(string $slug): Building
    {
        return Building::query()
            ->published()
            ->where(function ($q) use ($slug) {
                $q->where('slug', $slug)
                  ->orWhere('id', $slug);
            })
            ->with([
                'provinceRelation',
                'images',
                'primaryImage',
                'units' => function ($q) {
                    $q->whereIn('status', ['AVAILABLE', 'EXPIRING_SOON'])
                      ->with(['images', 'primaryImage'])
                      ->orderBy('floor_number')
                      ->orderBy('unit_code');
                }
            ])
            ->firstOrFail();
    }

    /**
     * Danh sách tòa nhà phục vụ phân hệ Quản trị (Admin/BQL).
     *
     * @param array<string, mixed> $filters
     * @param mixed $user
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getAdminBuildings(array $filters = [], $user = null, int $perPage = 15): LengthAwarePaginator
    {
        $query = Building::query()
            ->with(['provinceRelation', 'primaryImage'])
            ->withCount(['units', 'contracts']);

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

        // Scope RBAC theo phân công tòa nhà:
        // 1. Sales: Chỉ xem các tòa nhà được phân công bán/cho thuê (sale_buildings)
        if ($user instanceof \App\Models\Sale) {
            $assignedIds = DB::table('sale_buildings')
                ->where('sales_id', $user->id)
                ->where('is_active', true)
                ->pluck('building_id')
                ->toArray();
            $query->whereIn('id', $assignedIds);
        }
        // 2. Ban Quản Lý (PM): Chỉ xem các tòa nhà được phân công quản lý (manager_buildings)
        elseif ($user instanceof \App\Models\PropertyManager) {
            $assignedIds = DB::table('manager_buildings')
                ->where('pm_id', $user->id)
                ->pluck('building_id')
                ->toArray();
            $query->whereIn('id', $assignedIds);
        }
        // 3. Kế toán: Chỉ xem các tòa nhà được phân công theo dõi công nợ (account_buildings)
        elseif ($user instanceof \App\Models\Accountant) {
            $assignedIds = DB::table('account_buildings')
                ->where('accountant_id', $user->id)
                ->pluck('building_id')
                ->toArray();
            $query->whereIn('id', $assignedIds);
        }
        // 4. Doanh nghiệp khách thuê: Chỉ xem các tòa nhà có hợp đồng của doanh nghiệp mình
        elseif ($user instanceof \App\Models\Tenant) {
            $assignedIds = DB::table('contracts')
                ->where('tenant_id', $user->id)
                ->pluck('building_id')
                ->toArray();
            $query->whereIn('id', $assignedIds);
        }
        // 5. Admin: Xem toàn bộ tòa nhà hệ thống

        $rawKeyword = $filters['keyword'] ?? $filters['search'] ?? '';
        if (!empty($rawKeyword)) {
            $keyword = trim((string) $rawKeyword);
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                  ->orWhere('building_code', 'like', "%{$keyword}%")
                  ->orWhere('address', 'like', "%{$keyword}%")
                  ->orWhere('district', 'like', "%{$keyword}%");
            });
        }

        if (!empty($filters['grade'])) {
            $query->where('grade', strtoupper($filters['grade']));
        }

        if (isset($filters['is_published']) && $filters['is_published'] !== '') {
            $query->where('is_published', (bool) $filters['is_published']);
        }

        return $query->latest()->paginate($perPage);
    }

    /**
     * Tự động sinh mã tòa nhà văn phòng theo cấu trúc: BLD-{YYYY}-{SEQ_4_DIGITS}.
     * Quét toàn bộ CSDL kể cả các bản ghi đã xóa mềm (withTrashed) để triệt tiêu nguy cơ trùng lặp.
     *
     * @return string
     */
    public function generateBuildingCode(): string
    {
        $year = date('Y');
        $prefix = "BLD-{$year}-";

        // Quét toàn bộ mã tòa nhà trong CSDL (bao gồm cả các bản ghi đã xóa mềm) khớp tiền tố của năm
        $existingCodes = Building::withTrashed()
            ->where('building_code', 'like', "{$prefix}%")
            ->pluck('building_code');

        $maxSeq = 0;
        foreach ($existingCodes as $code) {
            if (preg_match('/^BLD-\d{4}-(\d+)$/', (string) $code, $matches)) {
                $seq = (int) $matches[1];
                if ($seq > $maxSeq) {
                    $maxSeq = $seq;
                }
            }
        }

        // Đảm bảo mã sinh ra là duy nhất tuyệt đối trong CSDL (kể cả bản ghi đã xóa mềm)
        do {
            $maxSeq++;
            $candidateCode = sprintf('BLD-%s-%04d', $year, $maxSeq);
        } while (Building::withTrashed()->where('building_code', $candidateCode)->exists());

        return $candidateCode;
    }

    /**
     * Khởi tạo mới tòa nhà văn phòng.
     *
     * @param array<string, mixed> $data
     * @param string|null $adminId
     * @return Building
     */
    public function storeBuilding(array $data, ?string $adminId = null): Building
    {
        return DB::transaction(function () use ($data, $adminId) {
            // 1. Tự động sinh mã tòa nhà nếu người dùng để trống, hoặc kiểm tra trùng lặp (kể cả đã xóa mềm) nếu tự nhập
            $rawCode = isset($data['building_code']) ? trim((string) $data['building_code']) : '';
            if ($rawCode === '') {
                $data['building_code'] = $this->generateBuildingCode();
            } else {
                $data['building_code'] = strtoupper($rawCode);
                if (Building::withTrashed()->where('building_code', $data['building_code'])->exists()) {
                    throw new BusinessException("Mã tòa nhà [{$data['building_code']}] đã tồn tại trong hệ thống (kể cả bản ghi đã xóa mềm).", 422);
                }
            }

            // 2. Tự động sinh slug thân thiện SEO và bảo đảm tính duy nhất
            $slugBase = Str::slug($data['name']);
            $slug = $slugBase;
            $counter = 1;
            while (Building::withTrashed()->where('slug', $slug)->exists()) {
                $slug = "{$slugBase}-{$counter}";
                $counter++;
            }
            $data['slug'] = $slug;

            if ($adminId) {
                $data['created_by_admin_id'] = $adminId;
            }

            if (!isset($data['is_published'])) {
                $data['is_published'] = true;
            }

            if (!isset($data['min_rent_price_per_sqm']) || $data['min_rent_price_per_sqm'] === null) {
                $data['min_rent_price_per_sqm'] = 0.00;
            }
            if (!isset($data['max_rent_price_per_sqm']) || $data['max_rent_price_per_sqm'] === null) {
                $data['max_rent_price_per_sqm'] = 0.00;
            }

            return Building::create($data);
        });
    }

    /**
     * Cập nhật thông tin tòa nhà văn phòng.
     *
     * @param Building $building
     * @param array<string, mixed> $data
     * @return Building
     */
    public function updateBuilding(Building $building, array $data): Building
    {
        return DB::transaction(function () use ($building, $data) {
            if (!empty($data['building_code'])) {
                $newCode = strtoupper(trim((string) $data['building_code']));
                if ($newCode !== $building->building_code) {
                    if (Building::withTrashed()->where('building_code', $newCode)->where('id', '!=', $building->id)->exists()) {
                        throw new BusinessException("Mã tòa nhà [{$newCode}] đã tồn tại trong hệ thống (kể cả bản ghi đã xóa mềm).", 422);
                    }
                    $data['building_code'] = $newCode;
                }
            }

            if (!empty($data['name']) && $data['name'] !== $building->name) {
                $slugBase = Str::slug($data['name']);
                $slug = $slugBase;
                $counter = 1;
                while (Building::withTrashed()->where('slug', $slug)->where('id', '!=', $building->id)->exists()) {
                    $slug = "{$slugBase}-{$counter}";
                    $counter++;
                }
                $data['slug'] = $slug;
            }

            $building->update($data);
            return $building->fresh(['provinceRelation', 'images', 'primaryImage']);
        });
    }

    /**
     * Tự động tính toán và đồng bộ lại khoảng giá thuê (min_rent_price_per_sqm & max_rent_price_per_sqm)
     * của tòa nhà dựa trên danh sách các mặt bằng (units) đang hoạt động.
     *
     * @param string $buildingId
     * @return void
     */
    public function syncRentPriceRange(string $buildingId): void
    {
        $prices = Unit::where('building_id', $buildingId)
            ->where('base_rental_price', '>', 0)
            ->pluck('base_rental_price');

        if ($prices->isEmpty()) {
            Building::where('id', $buildingId)->update([
                'min_rent_price_per_sqm' => 0.00,
                'max_rent_price_per_sqm' => 0.00,
            ]);
            return;
        }

        Building::where('id', $buildingId)->update([
            'min_rent_price_per_sqm' => (float) $prices->min(),
            'max_rent_price_per_sqm' => (float) $prices->max(),
        ]);
    }

    /**
     * Xóa mềm tòa nhà văn phòng.
     * Kiểm tra ràng buộc: Chặn xóa nếu còn hợp đồng thuê đang có hiệu lực.
     *
     * @param Building $building
     * @return bool
     * @throws BusinessException
     */
    public function deleteBuilding(Building $building): bool
    {
        // Kiểm tra xem có hợp đồng nào đang ACTIVE tại tòa nhà không
        $hasActiveContracts = Contract::where('building_id', $building->id)
            ->whereNotIn('status', ['TERMINATED', 'EXPIRED'])
            ->exists();

        if ($hasActiveContracts) {
            throw new BusinessException(
                "Không thể xóa tòa nhà [{$building->name}] vì vẫn còn hợp đồng thuê đang có hiệu lực. Vui lòng thanh lý toàn bộ hợp đồng trước khi thực hiện xóa.",
                422
            );
        }

        return (bool) $building->delete();
    }

    /**
     * Tải lên danh sách hình ảnh thực tế của tòa nhà.
     *
     * @param Building $building
     * @param array<UploadedFile> $files
     * @param string|null $caption
     * @return array<BuildingImage>
     */
    public function uploadImages(Building $building, array $files, ?string $caption = null, ?int $primaryIndex = 0): array
    {
        $uploaded = [];
        $targetPrimary = $primaryIndex ?? 0;
        $hasPrimary = $building->images()->where('is_primary', true)->exists();
        $currentOrder = (int) ($building->images()->max('display_order') ?? 0);

        $items = [];
        foreach ($files as $index => $file) {
            if (!$file instanceof UploadedFile) {
                continue;
            }
            $filename = Str::uuid()->toString() . '.' . $file->getClientOriginalExtension();
            $items[$index] = [
                'file' => $file,
                'path' => "buildings/{$building->id}/{$filename}",
                'fallback_dir' => "public/buildings/{$building->id}",
                'filename' => $filename,
            ];
        }

        $uploadResults = StorageUploadHelper::uploadBatch($items, 'supabase', 'public', 'public');

        foreach ($uploadResults as $index => $result) {
            $isPrimary = (!$hasPrimary && $index === $targetPrimary);
            if ($isPrimary) {
                $hasPrimary = true;
            }

            $currentOrder++;
            $uploaded[] = BuildingImage::create([
                'building_id' => $building->id,
                'image_url' => $result['url'],
                'caption' => $caption ?? $result['original_name'],
                'is_primary' => $isPrimary,
                'display_order' => $currentOrder,
            ]);
        }

        return $uploaded;
    }

    /**
     * Xóa một hình ảnh tòa nhà.
     *
     * @param Building $building
     * @param int $imageId
     * @return bool
     */
    public function deleteImage(Building $building, int $imageId): bool
    {
        $image = BuildingImage::where('building_id', $building->id)
            ->where('id', $imageId)
            ->firstOrFail();

        return (bool) $image->delete();
    }

    /**
     * Đặt ảnh làm ảnh đại diện chính của tòa nhà.
     *
     * @param Building $building
     * @param int $imageId
     * @return BuildingImage
     */
    public function setPrimaryImage(Building $building, int $imageId): BuildingImage
    {
        return DB::transaction(function () use ($building, $imageId) {
            $image = BuildingImage::where('building_id', $building->id)
                ->where('id', $imageId)
                ->firstOrFail();

            // Đặt tất cả ảnh khác thành false
            BuildingImage::where('building_id', $building->id)->update(['is_primary' => false]);

            $image->update(['is_primary' => true]);

            return $image;
        });
    }
}
