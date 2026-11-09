<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class AccountBuilding extends Pivot
{
    public $timestamps = false;
    public $incrementing = true;

    protected $table = 'account_buildings';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'accountant_id',
        'building_id',
        'assigned_by_admin_id',
        'assigned_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'assigned_at' => 'datetime',
        ];
    }

    public function accountant(): BelongsTo
    {
        return $this->belongsTo(Accountant::class, 'accountant_id');
    }

    public function building(): BelongsTo
    {
        return $this->belongsTo(Building::class, 'building_id');
    }

    public function assignedByAdmin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'assigned_by_admin_id');
    }
}
