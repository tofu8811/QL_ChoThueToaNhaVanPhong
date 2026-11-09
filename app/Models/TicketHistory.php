<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketHistory extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'ticket_history';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'ticket_id',
        'actor_role', // TENANT | PM | ADMIN
        'actor_id', // ID nhân sự/khách thực hiện hành động
        'action', // STATUS_CHANGE | COMMENT | ASSIGNMENT | ATTACHMENT
        'old_status',
        'new_status',
        'message',
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
}
