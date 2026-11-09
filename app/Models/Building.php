<?php

namespace App\Models;

use App\Traits\HasUuidV7;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Building extends Model
{
    use HasFactory, HasUuidV7, SoftDeletes;

    protected $table = 'buildings';

    /**
     * Danh sách các thuộc tính cho phép gán hàng loạt (Mass Assignment).
     *
     * @var list<string>
     */
    protected $fillable = [
        'building_code', // Mã định danh tòa nhà (VD: NEXUS-TOWER, TDA-01)
        'name', // Tên thương mại của tòa nhà văn phòng
        'slug', // URL thân thiện SEO cho cổng tra cứu public
        'address', // Địa chỉ số nhà và tên đường
        'ward', // Phường / Xã
        'district', // Quận / Huyện
        'province', // Tỉnh / Thành phố trực thuộc trung ương
        'province_code', // Mã tỉnh chuẩn theo Open API v2 (VD: 79)
        'district_code', // Mã quận / huyện
        'ward_code', // Mã phường / xã
        'latitude', // Vĩ độ GPS phục vụ Google Maps & bản đồ số
        'longitude', // Kinh độ GPS phục vụ Google Maps & bản đồ số
        'total_floors', // Tổng số tầng nổi của tòa nhà
        'total_basements', // Số lượng tầng hầm để xe
        'total_leasable_area_sqm', // Tổng diện tích sàn khả dụng cho thuê (m2)
        'ceiling_height_meters', // Chiều cao trần tiêu chuẩn (m)
        'elevators_count', // Số lượng thang máy chở khách và hàng
        'air_conditioner_spec', // Thông số hệ thống điều hòa không khí
        'backup_generator_spec', // Thông số máy phát điện dự phòng
        'working_hours_desc', // Giờ làm việc tiêu chuẩn
        'overtime_fee_per_hour', // Phí làm việc ngoài giờ (VND/giờ)
        'motorbike_parking_fee', // Phí gửi xe máy (VND/xe/tháng)
        'car_parking_fee', // Phí gửi xe ô tô (VND/xe/tháng)
        'electricity_unit_price', // Đơn giá điện (VND/kWh)
        'water_unit_price', // Đơn giá nước sạch (VND/m3)
        'default_service_fee_per_sqm', // Phí quản lý dịch vụ mặc định (VND/m2/tháng)
        'min_rent_price_per_sqm', // Khoảng giá thuê thấp nhất (VND/m2/tháng)
        'max_rent_price_per_sqm', // Khoảng giá thuê cao nhất (VND/m2/tháng)
        'deposit_term_months', // Số tháng tiền cọc tối thiểu
        'payment_term_months', // Kỳ hạn thanh toán chuẩn (tháng/lần)
        'grade', // Phân hạng văn phòng: A | B | C
        'direction', // Hướng chính diện tòa nhà theo phong thủy
        'is_published', // Trạng thái hiển thị công khai trên Portal
        'created_by_admin_id', // Quản trị viên khởi tạo
    ];

    /**
     * Ép kiểu dữ liệu các cột thuộc tính theo chuẩn Laravel 11.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'is_published' => 'boolean',
            'total_floors' => 'integer',
            'total_basements' => 'integer',
            'total_leasable_area_sqm' => 'decimal:2',
            'ceiling_height_meters' => 'decimal:2',
            'elevators_count' => 'integer',
            'overtime_fee_per_hour' => 'decimal:2',
            'motorbike_parking_fee' => 'decimal:2',
            'car_parking_fee' => 'decimal:2',
            'electricity_unit_price' => 'decimal:2',
            'water_unit_price' => 'decimal:2',
            'default_service_fee_per_sqm' => 'decimal:2',
            'min_rent_price_per_sqm' => 'decimal:2',
            'max_rent_price_per_sqm' => 'decimal:2',
            'deposit_term_months' => 'integer',
            'payment_term_months' => 'integer',
            'province_code' => 'integer',
            'district_code' => 'integer',
            'ward_code' => 'integer',
        ];
    }

    /**
     * Tỉnh / Thành phố trực thuộc theo danh mục chuẩn.
     */
    public function provinceRelation(): BelongsTo
    {
        return $this->belongsTo(Province::class, 'province_code', 'code');
    }

    /**
     * Danh sách mặt bằng trực thuộc tòa nhà.
     */
    public function units(): HasMany
    {
        return $this->hasMany(Unit::class, 'building_id');
    }

    /**
     * Bộ sưu tập hình ảnh thực tế của tòa nhà.
     */
    public function images(): HasMany
    {
        return $this->hasMany(BuildingImage::class, 'building_id')->orderBy('display_order');
    }

    /**
     * Ảnh đại diện chính (Primary Image).
     */
    public function primaryImage()
    {
        return $this->hasOne(BuildingImage::class, 'building_id')->where('is_primary', true);
    }

    /**
     * Quản trị viên khởi tạo tòa nhà.
     */
    public function createdByAdmin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'created_by_admin_id');
    }

    /**
     * Danh sách chuyên viên Sales được phân công phụ trách tòa nhà.
     */
    public function sales(): BelongsToMany
    {
        return $this->belongsToMany(Sale::class, 'sale_buildings', 'building_id', 'sales_id')
            ->withPivot(['is_active', 'assigned_by_admin_id', 'assigned_at'])
            ->withTimestamps();
    }

    /**
     * Danh sách Ban Quản Lý (PM) phụ trách tòa nhà.
     */
    public function propertyManagers(): BelongsToMany
    {
        return $this->belongsToMany(PropertyManager::class, 'manager_buildings', 'building_id', 'pm_id')
            ->withPivot(['is_primary', 'assigned_by_admin_id', 'assigned_at'])
            ->withTimestamps();
    }

    /**
     * Danh sách Kế toán phụ trách công nợ tòa nhà.
     */
    public function accountants(): BelongsToMany
    {
        return $this->belongsToMany(Accountant::class, 'account_buildings', 'building_id', 'accountant_id')
            ->withPivot(['assigned_by_admin_id', 'assigned_at'])
            ->withTimestamps();
    }

    /**
     * Danh sách Hợp đồng thuê phát sinh tại tòa nhà.
     */
    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class, 'building_id');
    }

    /**
     * Scope lọc các tòa nhà đang hiển thị công khai trên website.
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    /**
     * Scope lọc theo Tỉnh / Thành phố qua mã province_code chuẩn.
     */
    public function scopeProvinceCode(Builder $query, ?int $provinceCode): Builder
    {
        return $provinceCode ? $query->where('province_code', $provinceCode) : $query;
    }

    /**
     * Scope lọc theo Quận / Huyện.
     */
    public function scopeDistrict(Builder $query, ?string $district): Builder
    {
        return $district ? $query->where('district', $district) : $query;
    }

    /**
     * Scope lọc theo phân hạng văn phòng (Hạng A, B, C).
     */
    public function scopeGrade(Builder $query, ?string $grade): Builder
    {
        return $grade ? $query->where('grade', strtoupper($grade)) : $query;
    }

    /**
     * Đường dẫn mở trực tiếp vị trí trên Google Maps theo tọa độ GPS.
     */
    public function getGoogleMapsUrlAttribute(): ?string
    {
        if ($this->latitude && $this->longitude) {
            return "https://www.google.com/maps?q={$this->latitude},{$this->longitude}";
        }

        return "https://www.google.com/maps/search/?api=1&query=" . urlencode("{$this->name} {$this->address} {$this->ward} {$this->district} {$this->province}");
    }
}
