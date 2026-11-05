<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Building extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'buildings';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'is_published' => 'boolean',
            'total_floors' => 'integer',
            'total_basements' => 'integer',
            'total_leasable_area_sqm' => 'decimal:2',
            'default_service_fee_per_sqm' => 'decimal:2',
            'min_rent_price_per_sqm' => 'decimal:2',
            'max_rent_price_per_sqm' => 'decimal:2',
        ];
    }

    /**
     * Tạo đường dẫn mở trực tiếp vị trí trên Google Maps theo tọa độ GPS.
     * Cú pháp gọi trong Blade hoặc Controller: $building->google_maps_url
     */
    public function getGoogleMapsUrlAttribute(): ?string
    {
        if ($this->latitude && $this->longitude) {
            return "https://www.google.com/maps?q={$this->latitude},{$this->longitude}";
        }

        return "https://www.google.com/maps/search/?api=1&query=" . urlencode("{$this->name} {$this->address} {$this->ward} {$this->district} {$this->province}");
    }
}
