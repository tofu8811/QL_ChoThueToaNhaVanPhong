<?php

namespace App\Livewire;

use App\Models\Building;
use App\Models\Unit;
use Livewire\Component;

/**
 * Livewire Component: Danh Sách Mặt Bằng & Diện Tích Cắt Thuê Trống Theo Thời Gian Thực (Module 1).
 */
class FloorVacancyList extends Component
{
    public string $buildingId = '';
    public ?Building $building = null;

    protected $listeners = [
        'refreshVacancyList' => '$refresh',
        'unitStatusUpdated'  => '$refresh',
    ];

    public function mount(string $buildingId): void
    {
        $this->buildingId = $buildingId;
        $this->building = Building::find($buildingId);
    }

    public function bookFloor(string $floor, string $area): void
    {
        $target = ($this->building?->name ?? 'Tòa nhà') . " - {$floor} ({$area})";
        $this->dispatch('open-quote-target', target: $target);
    }

    public function render()
    {
        $units = collect();

        if ($this->buildingId) {
            $units = Unit::query()
                ->where('building_id', $this->buildingId)
                ->whereIn('status', ['AVAILABLE', 'EXPIRING_SOON'])
                ->orderBy('floor_number')
                ->orderBy('unit_code')
                ->get();
        }

        return view('livewire.floor-vacancy-list', [
            'units' => $units,
        ]);
    }
}
