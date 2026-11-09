<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BuildingImage extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'building_images';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'building_id', // Tòa nhà sở hữu hình ảnh
        'image_url', // Đường dẫn ảnh trên Cloud Storage / S3 / Supabase
        'is_primary', // True: Ảnh đại diện hiển thị ngoài danh sách tìm kiếm
        'display_order', // Thứ tự hiển thị trong bộ sưu tập (Slider)
        'created_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
            'display_order' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    /**
     * Tòa nhà sở hữu ảnh này.
     */
    public function building(): BelongsTo
    {
        return $this->belongsTo(Building::class, 'building_id');
    }
}
