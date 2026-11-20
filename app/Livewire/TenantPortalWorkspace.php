<?php

namespace App\Livewire;

use App\Models\Contract;
use App\Models\DebitNote;
use App\Models\Tenant;
use App\Models\Unit;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

/**
 * Livewire Component: Cổng Doanh Nghiệp Thuê Văn Phòng (Tenant Portal Workspace - Module 3).
 * Cho phép khách thuê theo dõi chi tiết văn phòng, hợp đồng thuê đang hiệu lực,
 * tra cứu bảng kê cước phí hàng tháng, tải hóa đơn và sao kê công nợ.
 */
class TenantPortalWorkspace extends Component
{
    public string $activeTab = 'workspace'; // 'workspace' | 'bills'
    public ?string $tenantId = null;

    // Modal chi tiết phiếu cước (Debit Note Detail)
    public bool $showDebitModal = false;
    public ?DebitNote $selectedDebitNote = null;

    // Modal xem chi tiết & sơ đồ mặt bằng
    public bool $showUnitModal = false;
    public ?Unit $selectedUnit = null;

    public function mount(?string $tenantId = null): void
    {
        $requested = $tenantId ?: (request('tenant_id') ?: request('tenant'));
        if ($requested) {
            $matched = Tenant::where('id', $requested)
                ->orWhere('tenant_code', $requested)
                ->orWhere('tax_code', $requested)
                ->first();
            if ($matched) {
                $this->tenantId = $matched->id;
                return;
            }
        }

        $user = Auth::guard('tenant')->user() ?? Auth::user();
        if ($user instanceof Tenant) {
            $this->tenantId = $user->id;
        }
    }

    public function switchTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function openDebitModal(string $debitNoteId): void
    {
        $this->selectedDebitNote = DebitNote::with(['building', 'contract', 'details.unit'])->findOrFail($debitNoteId);
        $this->showDebitModal = true;
    }

    public function closeDebitModal(): void
    {
        $this->showDebitModal = false;
        $this->selectedDebitNote = null;
    }

    public function viewUnitDetail(string $unitId): void
    {
        $this->selectedUnit = Unit::with(['images', 'building'])->find($unitId);
        $this->showUnitModal = true;
    }

    public function closeUnitModal(): void
    {
        $this->showUnitModal = false;
        $this->selectedUnit = null;
    }

    public function render()
    {
        $tenant = null;
        $activeContract = null;
        $recentDebitNotes = collect();
        $latestDebitNote = null;

        if ($this->tenantId) {
            $tenant = Tenant::with([
                'contracts' => function ($q) {
                    $q->with(['building', 'contractUnits.unit'])->latest();
                },
                'debitNotes' => function ($q) {
                    $q->with(['building', 'details.unit'])->latest();
                },
            ])->find($this->tenantId);

            if ($tenant) {
                // Tìm hợp đồng ACTIVE hoặc gần nhất
                $activeContract = $tenant->contracts->firstWhere('status', 'ACTIVE')
                    ?? $tenant->contracts->firstWhere('status', 'EXPIRING_SOON')
                    ?? $tenant->contracts->first();

                $recentDebitNotes = $tenant->debitNotes;
                $latestDebitNote = $recentDebitNotes->first();
            }
        }

        return view('livewire.tenant-portal-workspace', [
            'tenant' => $tenant,
            'activeContract' => $activeContract,
            'recentDebitNotes' => $recentDebitNotes,
            'latestDebitNote' => $latestDebitNote,
        ]);
    }
}
