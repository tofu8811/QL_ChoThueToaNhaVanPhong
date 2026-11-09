<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContractImage extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'contract_images';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'contract_id',
        'image_url',
        'display_order',
        'description',
        'created_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'display_order' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    /**
     * Hợp đồng sở hữu hình ảnh này.
     */
    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class, 'contract_id');
    }
}
