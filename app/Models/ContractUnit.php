<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ContractUnit extends Pivot
{
    public $incrementing = true;

    protected $table = 'contract_units';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'building_id', // Tòa nhà trực thuộc
        'contract_id', // Hợp đồng thuê mặt bằng
        'unit_id', // Mặt bằng cụ thể được giao
        'leased_area_sqm', // Diện tích thực tế chốt thuê trong hợp đồng (m2)
        'agreed_rental_price_per_sqm', // Đơn giá thuê đã thỏa thuận (VND/m2/tháng)
        'agreed_service_fee_per_sqm', // Phí dịch vụ quản lý tòa nhà (VND/m2/tháng)
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'leased_area_sqm' => 'decimal:2',
            'agreed_rental_price_per_sqm' => 'decimal:2',
            'agreed_service_fee_per_sqm' => 'decimal:2',
            'monthly_base_rent' => 'decimal:2',
            'monthly_service_fee' => 'decimal:2',
        ];
    }

    /**
     * Hợp đồng thuê.
     */
    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class, 'contract_id');
    }

    /**
     * Mặt bằng văn phòng.
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    /**
     * Tòa nhà.
     */
    public function building(): BelongsTo
    {
        return $this->belongsTo(Building::class, 'building_id');
    }
}
