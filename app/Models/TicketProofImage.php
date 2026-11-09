<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketProofImage extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'ticket_proof_images';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'ticket_id',
        'history_id',
        'image_url',
        'stage', // REPORTED | IN_PROGRESS | RESOLVED | REJECTED
        'description',
        'created_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class, 'ticket_id');
    }

    public function history(): BelongsTo
    {
        return $this->belongsTo(TicketHistory::class, 'history_id');
    }
}
