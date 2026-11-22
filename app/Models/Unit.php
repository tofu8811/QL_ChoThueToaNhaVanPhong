<?php

namespace App\Models;

use App\Enums\UnitStatus;
use App\Traits\HasUuidV7;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Models\Ticket;
class Unit extends Model
{
    use HasFactory, HasUuidV7, SoftDeletes;

    protected $table = 'units';

    /**
     * Thuộc tính tính toán sẵn từ Backend.
     *
     * @var list<string>
     */
    protected $appends = [
        'area',
        'rent_price_per_sqm',
        'formatted_area',
        'formatted_rent_price',
    ];

    /**
     * @var list<string>
     */
    protected $fillable = [
        'building_id', // Tòa nhà chứa mặt bằng này
        'unit_code', // Mã căn / phòng (VD: NEX-0501, P.801)
        'name', // Tên hiển thị đầy đủ mặt bằng văn phòng
        'floor_number', // Tầng chứa mặt bằng (âm: tầng hầm, dương: tầng nổi)
        'area_sqm', // Diện tích thông thủy cho thuê (m2)
        'base_rental_price', // Đơn giá thuê cơ sở niêm yết (VND/m2/tháng)
        'handover_condition', // Hiện trạng bàn giao: RAW | BASIC | FULL_FURNISHED
        'direction', // Hướng view phong thủy (Đông, Tây, Nam, Bắc,...)
        'floor_plan_image_url', // Bản vẽ sơ đồ mặt bằng chi tiết (Layout CAD / 2D)
        'available_from_date', // Ngày sẵn sàng bàn giao cho khách thuê
        'status', // Trạng thái: AVAILABLE | EXPIRING_SOON | LEASED | UNDER_MAINTENANCE
        'last_status_changed_at', // Thời điểm thay đổi trạng thái gần nhất
        'updated_by_pm_id', // Ban quản lý cập nhật hiện trạng
    ];

    /**
     * Tự động đồng bộ khoảng giá thuê của tòa nhà cha mỗi khi mặt bằng được lưu, xóa hoặc khôi phục.
     */
    protected static function booted(): void
    {
        static::saved(function (Unit $unit) {
            if ($unit->building_id) {
                app(\App\Services\BuildingService::class)->syncRentPriceRange($unit->building_id);
            }
        });

        static::deleted(function (Unit $unit) {
            if ($unit->building_id) {
                app(\App\Services\BuildingService::class)->syncRentPriceRange($unit->building_id);
            }
        });

        static::restored(function (Unit $unit) {
            if ($unit->building_id) {
                app(\App\Services\BuildingService::class)->syncRentPriceRange($unit->building_id);
            }
        });
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'floor_number' => 'integer',
            'area_sqm' => 'decimal:2',
            'base_rental_price' => 'decimal:2',
            'available_from_date' => 'date',
            'status' => UnitStatus::class, // Ép kiểu Backed Enum UnitStatus
            'last_status_changed_at' => 'datetime',
        ];
    }

    /**
     * Tòa nhà chứa mặt bằng này.
     */
    public function building(): BelongsTo
    {
        return $this->belongsTo(Building::class, 'building_id');
    }

    /**
     * Bộ sưu tập hình ảnh thực tế của mặt bằng.
     */
    public function images(): HasMany
    {
        return $this->hasMany(UnitImage::class, 'unit_id')->orderBy('display_order');
    }

    /**
     * Ảnh đại diện chính của mặt bằng.
     */
    public function primaryImage()
    {
        return $this->hasOne(UnitImage::class, 'unit_id')->where('is_primary', true);
    }

    /**
     * Ban Quản Lý (PM) cập nhật hiện trạng gần nhất.
     */
    public function updatedByPropertyManager(): BelongsTo
    {
        return $this->belongsTo(PropertyManager::class, 'updated_by_pm_id');
    }

    /**
     * Các hợp đồng thuê liên quan đến mặt bằng này.
     */
    public function contracts(): BelongsToMany
    {
        return $this->belongsToMany(Contract::class, 'contract_units', 'unit_id', 'contract_id')
            ->withPivot(['rental_price_per_sqm', 'service_fee_per_sqm', 'actual_handover_date', 'status'])
            ->withTimestamps();
    }

    /**
     * Scope lọc mặt bằng đang còn trống sẵn sàng cho thuê.
     */
    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('status', UnitStatus::AVAILABLE);
    }

    /**
     * Scope lọc theo tòa nhà.
     */
    public function scopeByBuilding(Builder $query, string $buildingId): Builder
    {
        return $query->where('building_id', $buildingId);
    }

    /**
     * Scope lọc theo khoảng diện tích (m2).
     */
    public function scopeAreaBetween(Builder $query, ?float $minArea, ?float $maxArea): Builder
    {
        if ($minArea !== null) {
            $query->where('area_sqm', '>=', $minArea);
        }
        if ($maxArea !== null) {
            $query->where('area_sqm', '<=', $maxArea);
        }

        return $query;
    }

    /**
     * Scope lọc theo khoảng đơn giá thuê (VND/m2/tháng).
     */
    public function scopePriceBetween(Builder $query, ?float $minPrice, ?float $maxPrice): Builder
    {
        if ($minPrice !== null) {
            $query->where('base_rental_price', '>=', $minPrice);
        }
        if ($maxPrice !== null) {
            $query->where('base_rental_price', '<=', $maxPrice);
        }

        return $query;
    }

    /**
     * Scope lọc mặt bằng đang ở trạng thái đặt cọc giữ chỗ (Booking).
     */
    public function scopeReserved(Builder $query): Builder
    {
        return $query->where('status', UnitStatus::RESERVED);
    }

    /**
     * Kiểm tra mặt bằng có đang trống và sẵn sàng cho thuê không.
     */
    public function isAvailable(): bool
    {
        return $this->status === UnitStatus::AVAILABLE;
    }

    /**
     * Kiểm tra mặt bằng có đang được đặt cọc giữ chỗ tạm thời hay không.
     */
    public function isReserved(): bool
    {
        return $this->status === UnitStatus::RESERVED;
    }
    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'unit_id');
    }

    /**
     * Lấy diện tích thông thủy của mặt bằng (m2).
     *
     * @return float
     */
    public function getAreaAttribute(): float
    {
        return (float) ($this->attributes['area_sqm'] ?? 0);
    }

    /**
     * Lấy đơn giá thuê cơ sở trên mỗi m2.
     *
     * @return float
     */
    public function getRentPricePerSqmAttribute(): float
    {
        return (float) ($this->attributes['base_rental_price'] ?? 0);
    }

    /**
     * Định dạng diện tích mặt bằng kèm đơn vị m².
     *
     * @return string
     */
    public function getFormattedAreaAttribute(): string
    {
        return number_format((float) ($this->attributes['area_sqm'] ?? 0), 0, ',', '.') . ' m²';
    }

    /**
     * Định dạng đơn giá thuê mặt bằng theo tiền tệ chuẩn.
     *
     * @return string
     */
    public function getFormattedRentPriceAttribute(): string
    {
        $raw = (float) ($this->attributes['base_rental_price'] ?? 0);

        return $raw > 1000
            ? number_format($raw, 0, ',', '.') . ' đ/m²'
            : '$' . number_format($raw, 0) . '/m²';
    }
}
