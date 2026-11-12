<?php

namespace App\Services;

use App\Exceptions\BusinessException;
use App\Models\Building;
use App\Models\BuildingImage;
use App\Models\Contract;
use App\Models\ContractStatus;
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
        }

        // 3. Lọc theo Quận / Huyện
        if (!empty($filters['district_code'])) {
            $query->where('district_code', (int) $filters['district_code']);
        } elseif (!empty($filters['district'])) {
            $query->where('district', $filters['district']);
        }

        // 4. Lọc theo Phân hạng văn phòng (Grade A/B/C)
        if (!empty($filters['grade'])) {
            $query->where('grade', strtoupper($filters['grade']));
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
            ->where('slug', $slug)
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

        // Phân quyền theo tòa nhà nếu không phải Super Admin
        if ($user && method_exists($user, 'isSuperAdmin') && !$user->isSuperAdmin()) {
            if (method_exists($user, 'assignedBuildings')) {
                $assignedIds = $user->assignedBuildings()->pluck('buildings.id')->toArray();
                $query->whereIn('id', $assignedIds);
            }
        }

        if (!empty($filters['keyword'])) {
            $keyword = trim($filters['keyword']);
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                  ->orWhere('building_code', 'like', "%{$keyword}%")
                  ->orWhere('address', 'like', "%{$keyword}%");
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
     * Khởi tạo mới tòa nhà văn phòng.
     *
     * @param array<string, mixed> $data
     * @param string|null $adminId
     * @return Building
     */
    public function storeBuilding(array $data, ?string $adminId = null): Building
    {
        return DB::transaction(function () use ($data, $adminId) {
            $slugBase = Str::slug($data['name']);
            $slug = $slugBase;
            $counter = 1;
            while (Building::where('slug', $slug)->exists()) {
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
            if (!empty($data['name']) && $data['name'] !== $building->name) {
                $slugBase = Str::slug($data['name']);
                $slug = $slugBase;
                $counter = 1;
                while (Building::where('slug', $slug)->where('id', '!=', $building->id)->exists()) {
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
    public function uploadImages(Building $building, array $files, ?string $caption = null): array
    {
        $uploaded = [];

        foreach ($files as $index => $file) {
            $filename = Str::uuid()->toString() . '.' . $file->getClientOriginalExtension();
            $path = "buildings/{$building->id}/{$filename}";

            // Fallback lưu local nếu Supabase disk chưa cấu hình
            try {
                if (config('filesystems.disks.supabase.key')) {
                    Storage::disk('supabase')->put($path, file_get_contents($file));
                    $url = Storage::disk('supabase')->url($path);
                } else {
                    $localPath = $file->storeAs("public/buildings/{$building->id}", $filename);
                    $url = Storage::url($localPath);
                }
            } catch (\Throwable $e) {
                Log::warning("Supabase upload failed, fallback to local storage: " . $e->getMessage());
                $localPath = $file->storeAs("public/buildings/{$building->id}", $filename);
                $url = Storage::url($localPath);
            }

            $hasPrimary = $building->images()->where('is_primary', true)->exists();
            $isPrimary = (!$hasPrimary && $index === 0);

            $image = BuildingImage::create([
                'building_id' => $building->id,
                'image_url' => $url,
                'caption' => $caption ?? $file->getClientOriginalName(),
                'is_primary' => $isPrimary,
                'display_order' => $building->images()->count() + 1,
            ]);

            $uploaded[] = $image;
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
