<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UnitImage extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'unit_images';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'unit_id', // Thuộc về mặt bằng nào
        'image_url', // Đường dẫn ảnh trên Cloud Storage / S3 / Supabase
        'is_primary', // Ảnh chính đại diện của phòng
        'display_order', // Thứ tự hiển thị
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
     * Mặt bằng sở hữu hình ảnh này.
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }
}
