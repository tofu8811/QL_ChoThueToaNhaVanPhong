<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UtilityFee extends Model
{
    use HasFactory;

    protected $table = 'utility_fee';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'building_id', // Tòa nhà phát sinh chỉ số
        'unit_id', // Mặt bằng tiêu thụ
        'billing_year', // Năm chốt chỉ số tính cước
        'billing_month', // Tháng chốt chỉ số (1 - 12)
        'reading_date', // Ngày nhân viên kỹ thuật chốt đồng hồ
        'elec_start_index', // Chỉ số điện đầu kỳ (kWh)
        'elec_end_index', // Chỉ số điện cuối kỳ (kWh)
        'water_start_index', // Chỉ số nước đầu kỳ (m3)
        'water_end_index', // Chỉ số nước cuối kỳ (m3)
        'motorbike_count', // Số lượng xe máy đăng ký đỗ
        'car_count', // Số lượng ô tô đăng ký đỗ
        'overtime_hours', // Tổng số giờ làm ngoài giờ
        'meter_photo_private_path', // Ảnh chụp công tơ đồng hồ đối soát
        'recorded_by_pm_id', // Nhân viên kỹ thuật ghi nhận số liệu
        'is_locked', // True: Số liệu đã chốt đưa vào hóa đơn
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'billing_year' => 'integer',
            'billing_month' => 'integer',
            'reading_date' => 'date',
            'elec_start_index' => 'decimal:2',
            'elec_end_index' => 'decimal:2',
            'water_start_index' => 'decimal:2',
            'water_end_index' => 'decimal:2',
            'motorbike_count' => 'integer',
            'car_count' => 'integer',
            'overtime_hours' => 'decimal:2',
            'is_locked' => 'boolean',
        ];
    }

    /**
     * Lượng điện tiêu thụ trong kỳ (kWh).
     */
    public function getElectricityUsageAttribute(): float
    {
        return max(0, (float) $this->elec_end_index - (float) $this->elec_start_index);
    }

    /**
     * Lượng nước tiêu thụ trong kỳ (m3).
     */
    public function getWaterUsageAttribute(): float
    {
        return max(0, (float) $this->water_end_index - (float) $this->water_start_index);
    }

    public function building(): BelongsTo
    {
        return $this->belongsTo(Building::class, 'building_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    public function recordedByPropertyManager(): BelongsTo
    {
        return $this->belongsTo(PropertyManager::class, 'recorded_by_pm_id');
    }
}
