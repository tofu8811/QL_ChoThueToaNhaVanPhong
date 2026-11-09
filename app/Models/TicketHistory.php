<?php

namespace App\Models;

use App\Enums\TicketStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'actor_type', // TENANT | PM | SALE | ADMIN | SYSTEM
        'tenant_actor_id',
        'pm_actor_id',
        'sales_actor_id',
        'admin_actor_id',
        'from_status',
        'to_status',
        'message',
        'created_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'created_at'  => 'datetime',
            'from_status' => TicketStatus::class,
            'to_status'   => TicketStatus::class,
        ];
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class, 'ticket_id');
    }

    public function tenantActor(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_actor_id');
    }

    public function pmActor(): BelongsTo
    {
        return $this->belongsTo(PropertyManager::class, 'pm_actor_id');
    }

    public function salesActor(): BelongsTo
    {
        return $this->belongsTo(Sale::class, 'sales_actor_id');
    }

    public function adminActor(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'admin_actor_id');
    }

    public function proofImages(): HasMany
    {
        return $this->hasMany(TicketProofImage::class, 'ticket_history_id');
    }

    /**
     * Lấy thực thể Người gửi tin nhắn / hành động.
     */
    public function getActorAttribute()
    {
        return match ($this->actor_type) {
            'TENANT' => $this->tenantActor,
            'PM'     => $this->pmActor,
            'SALE'   => $this->salesActor,
            'ADMIN'  => $this->adminActor,
            default  => null,
        };
    }
}
