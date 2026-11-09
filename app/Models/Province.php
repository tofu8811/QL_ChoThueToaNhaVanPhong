<?php

namespace App\Models;

use App\Enums\UnitStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Province extends Model
{
    use HasFactory;

    protected $table = 'provinces';

    /**
     * Khóa chính là mã chuẩn của Tỉnh/Thành phố (dạng số nguyên không tự tăng).
     */
    protected $primaryKey = 'code';

    public $incrementing = false;

    protected $keyType = 'int';

    /**
     * Danh sách các trường cho phép gán hàng loạt (Mass Assignment).
     *
     * @var list<string>
     */
    protected $fillable = [
        'code',
        'name',
        'slug',
        'division_type',
        'codename',
        'phone_code',
    ];

    /**
     * Ép kiểu các trường dữ liệu.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'code' => 'integer',
            'phone_code' => 'integer',
        ];
    }

    /**
     * Danh sách các Tòa nhà văn phòng thuộc tỉnh/thành phố này.
     */
    public function buildings(): HasMany
    {
        return $this->hasMany(Building::class, 'province_code', 'code');
    }

    /**
     * Scope lọc các Tỉnh/Thành phố hiện có tòa nhà đang hiển thị công khai trên hệ thống.
     */
    public function scopeHasPublishedBuildings(Builder $query): Builder
    {
        return $query->whereHas('buildings', function (Builder $q) {
            $q->where('is_published', true);
        });
    }

    /**
     * Scope lọc các Tỉnh/Thành phố đang có mặt bằng văn phòng còn trống (AVAILABLE) cho thuê.
     * Phục vụ nhanh cho Lead/Khách hàng tìm kiếm phòng theo khu vực có sẵn.
     */
    public function scopeHasAvailableUnits(Builder $query): Builder
    {
        return $query->whereHas('buildings.units', function (Builder $q) {
            $q->where('status', UnitStatus::AVAILABLE);
        });
    }
}
