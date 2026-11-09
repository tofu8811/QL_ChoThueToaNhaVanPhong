<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DebitNoteDetail extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'debitnote_detail';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'debit_note_id', // Thuộc về giấy báo cước nào
        'item_type', // Loại khoản thu: RENT | SERVICE | ELECTRICITY | WATER | OVERTIME | PARKING | OTHER
        'description', // Diễn giải chi tiết khoản tiền
        'quantity', // Số lượng tính (Diện tích, số kWh, số m3, số xe, số giờ)
        'unit_name', // Đơn vị tính: m2, kWh, m3, xe, giờ
        'unit_price', // Đơn giá tương ứng
        'amount', // Thành tiền = quantity * unit_price
        'created_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'unit_price' => 'decimal:2',
            'amount' => 'decimal:2',
            'created_at' => 'datetime',
        ];
    }

    public function debitNote(): BelongsTo
    {
        return $this->belongsTo(DebitNote::class, 'debit_note_id');
    }
}
