<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ManagerBuilding extends Pivot
{
    public $timestamps = false;
    public $incrementing = true;

    protected $table = 'manager_buildings';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'pm_id',
        'building_id',
        'is_primary',
        'assigned_by_admin_id',
        'assigned_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
            'assigned_at' => 'datetime',
        ];
    }

    public function propertyManager(): BelongsTo
    {
        return $this->belongsTo(PropertyManager::class, 'pm_id');
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
