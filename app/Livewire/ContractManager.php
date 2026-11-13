<?php

namespace App\Livewire;

use App\Enums\ContractStatus;
use App\Exceptions\BusinessException;
use App\Exceptions\LeaseOverlapException;
use App\Models\Admin;
use App\Models\Building;
use App\Models\Contract;
use App\Models\PropertyManager;
use App\Models\Sale;
use App\Models\Tenant;
use App\Models\Unit;
use App\Services\ContractService;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Livewire Component: Quản Lý Hợp Đồng Thuê & Chống Trùng Lịch Sàn Văn Phòng (Module 3).
 * Xử lý danh sách, tìm kiếm, lọc, tạo mới HĐ với Pessimistic Lock / Overlap Check,
 * chi tiết, ký duyệt (Sign), gia hạn (Renew) và thanh lý (Terminate).
 */
class ContractManager extends Component
{
    use WithPagination;

    // Bộ lọc & Tìm kiếm
    public string $searchKeyword = '';
    public string $selectedBuildingId = 'all';
    public string $selectedTenantId = 'all';
    public string $selectedStatus = 'all';
    public int $perPage = 10;

    // Thông báo nhanh
    public ?string $flashMessage = null;
    public string $flashType = 'success'; // 'success' | 'danger' | 'warning'

    // Modal Xem chi tiết hợp đồng
    public bool $showDetailModal = false;
    public ?string $selectedContractId = null;
    public ?Contract $contractDetail = null;

    // Modal Tạo mới hợp đồng
    public bool $showCreateModal = false;
    public string $createType = 'DRAFT'; // 'DRAFT' | 'BOOKING'
    public string $building_id = '';
    public string $tenant_id = '';
    public ?string $created_by_sales_id = null;
    public string $contract_number = '';
    public string $start_date = '';
    public string $end_date = '';
    public float $deposit_amount = 0;
    public float $booking_deposit_amount = 0;
    public ?string $reservation_expires_at = null;
    public int $payment_cycle_months = 1;
    public int $payment_due_day = 5;
    public float $late_fee_interest_percent = 0.05;
    public float $annual_price_escalation_percent = 5.0;
    public array $selectedUnits = [];
    public ?string $selectedUnitToAdd = '';
    public ?string $overlapWarning = null;

    // Modal Gia hạn hợp đồng
    public bool $showRenewModal = false;
    public ?string $renewContractId = null;
    public ?Contract $renewContract = null;
    public string $new_end_date = '';
    public float $renew_escalation_percent = 5.0;
    public string $renew_contract_number = '';
    public ?float $renew_deposit_amount = null;

    // Modal Thanh lý hợp đồng
    public bool $showTerminateModal = false;
    public ?string $terminateContractId = null;
    public ?Contract $terminateContract = null;
    public string $terminated_at = '';
    public string $termination_note = '';
    public float $refund_deposit_amount = 0;
    public float $penalty_deposit_amount = 0;
    public float $maxDepositAllowed = 0;

    public function mount(): void
    {
        $this->start_date = Carbon::now()->addDays(7)->toDateString();
        $this->end_date = Carbon::now()->addDays(7)->addYears(2)->toDateString();
        $this->terminated_at = Carbon::now()->toDateString();
    }

    public function updatingSearchKeyword(): void
    {
        $this->resetPage();
    }

    public function updatingSelectedBuildingId(): void
    {
        $this->resetPage();
    }

    public function updatingSelectedStatus(): void
    {
        $this->resetPage();
    }

    public function updatingSelectedTenantId(): void
    {
        $this->resetPage();
    }

    // Mở modal tạo hợp đồng mới
    public function openCreateModal(): void
    {
        $this->resetValidation();
        $this->overlapWarning = null;
        $this->selectedUnits = [];
        $this->selectedUnitToAdd = '';
        $this->building_id = '';
        $this->tenant_id = '';
        $this->contract_number = '';
        $this->deposit_amount = 0;
        $this->booking_deposit_amount = 0;
        $this->createType = 'DRAFT';
        $this->start_date = Carbon::now()->addDays(7)->toDateString();
        $this->end_date = Carbon::now()->addDays(7)->addYears(2)->toDateString();
        $this->showCreateModal = true;
    }

    public function closeCreateModal(): void
    {
        $this->showCreateModal = false;
        $this->overlapWarning = null;
    }

    // Khi chọn tòa nhà, tự động cập nhật tiền tố số HĐ
    public function updatedBuildingId(): void
    {
        $this->selectedUnits = [];
        $this->selectedUnitToAdd = '';
        $this->overlapWarning = null;

        if ($this->building_id) {
            $building = Building::find($this->building_id);
            if ($building) {
                $prefix = $this->createType === 'BOOKING' ? 'BOOKING' : 'HD';
                $this->contract_number = sprintf('%s-%s-%s-%04d', $prefix, $building->building_code, now()->format('Y'), rand(100, 999));
            }
        }
    }

    // Khi đổi loại hợp đồng (DRAFT vs BOOKING)
    public function updatedCreateType(): void
    {
        if ($this->building_id) {
            $building = Building::find($this->building_id);
            if ($building) {
                $prefix = $this->createType === 'BOOKING' ? 'BOOKING' : 'HD';
                $this->contract_number = sprintf('%s-%s-%s-%04d', $prefix, $building->building_code, now()->format('Y'), rand(100, 999));
            }
        }
    }

    // Thêm mặt bằng vào hợp đồng kèm kiểm tra trùng lịch (Overlap Check) tức thì
    public function addUnit(ContractService $contractService): void
    {
        $this->overlapWarning = null;

        if (empty($this->selectedUnitToAdd)) {
            $this->overlapWarning = 'Vui lòng chọn một mặt bằng từ danh sách!';
            return;
        }

        if (empty($this->start_date) || empty($this->end_date)) {
            $this->overlapWarning = 'Vui lòng xác định Ngày bắt đầu và Ngày kết thúc thuê trước khi chọn mặt bằng!';
            return;
        }

        if ($this->start_date >= $this->end_date) {
            $this->overlapWarning = 'Ngày kết thúc thuê phải sau ngày bắt đầu!';
            return;
        }

        // Kiểm tra xem mặt bằng đã có trong danh sách chọn chưa
        foreach ($this->selectedUnits as $u) {
            if ($u['unit_id'] === $this->selectedUnitToAdd) {
                $this->overlapWarning = 'Mặt bằng này đã được thêm vào danh sách.';
                return;
            }
        }

        $unit = Unit::find($this->selectedUnitToAdd);
        if (!$unit) {
            $this->overlapWarning = 'Không tìm thấy thông tin mặt bằng.';
            return;
        }

        // Kiểm tra chống trùng lịch giao thoa bằng ContractService
        try {
            $contractService->validateNoOverlap($unit->id, $this->start_date, $this->end_date);
        } catch (LeaseOverlapException | BusinessException $e) {
            $this->overlapWarning = $e->getMessage();
            return;
        }

        $price = (float) ($unit->base_rental_price ?: ($unit->building?->min_rent_price_per_sqm ?: 500000));
        $serviceFee = (float) ($unit->building?->default_service_fee_per_sqm ?: 100000);
        $area = (float) $unit->area_sqm;

        $this->selectedUnits[] = [
            'unit_id' => $unit->id,
            'unit_code' => $unit->unit_code,
            'name' => $unit->name,
            'floor_number' => $unit->floor_number,
            'leased_area_sqm' => $area,
            'agreed_rental_price_per_sqm' => $price,
            'agreed_service_fee_per_sqm' => $serviceFee,
        ];

        // Tự động tính tiền cọc đề xuất (3 tháng tiền thuê)
        $totalMonthlyRent = 0;
        foreach ($this->selectedUnits as $item) {
            $totalMonthlyRent += $item['leased_area_sqm'] * $item['agreed_rental_price_per_sqm'];
        }
        $this->deposit_amount = round($totalMonthlyRent * 3);
        $this->booking_deposit_amount = round($totalMonthlyRent * 0.5);

        $this->selectedUnitToAdd = '';
    }

    // Xóa mặt bằng khỏi danh sách chọn
    public function removeUnit(int $index): void
    {
        unset($this->selectedUnits[$index]);
        $this->selectedUnits = array_values($this->selectedUnits);

        $totalMonthlyRent = 0;
        foreach ($this->selectedUnits as $item) {
            $totalMonthlyRent += $item['leased_area_sqm'] * $item['agreed_rental_price_per_sqm'];
        }
        $this->deposit_amount = round($totalMonthlyRent * 3);
        $this->booking_deposit_amount = round($totalMonthlyRent * 0.5);
    }

    // Lưu hợp đồng mới
    public function saveContract(ContractService $contractService): void
    {
        $this->overlapWarning = null;

        $rules = [
            'building_id' => 'required|exists:buildings,id',
            'tenant_id' => 'required|exists:tenants,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'deposit_amount' => 'required|numeric|min:0',
            'selectedUnits' => 'required|array|min:1',
        ];

        if ($this->createType === 'BOOKING') {
            $rules['booking_deposit_amount'] = 'required|numeric|min:1';
            $rules['reservation_expires_at'] = 'required|date|after:now';
        }

        $this->validate($rules, [
            'building_id.required' => 'Vui lòng chọn tòa nhà.',
            'tenant_id.required' => 'Vui lòng chọn khách thuê.',
            'start_date.required' => 'Vui lòng chọn ngày bắt đầu hợp đồng.',
            'end_date.required' => 'Vui lòng chọn ngày kết thúc hợp đồng.',
            'end_date.after' => 'Ngày kết thúc phải sau ngày bắt đầu.',
            'selectedUnits.min' => 'Hợp đồng phải có ít nhất 1 mặt bằng văn phòng được chọn.',
        ]);

        try {
            $payload = [
                'building_id' => $this->building_id,
                'tenant_id' => $this->tenant_id,
                'created_by_sales_id' => $this->created_by_sales_id,
                'contract_number' => $this->contract_number ?: null,
                'start_date' => $this->start_date,
                'end_date' => $this->end_date,
                'deposit_amount' => $this->deposit_amount,
                'booking_deposit_amount' => $this->booking_deposit_amount,
                'reservation_expires_at' => $this->reservation_expires_at,
                'payment_cycle_months' => $this->payment_cycle_months,
                'payment_due_day' => $this->payment_due_day,
                'late_fee_interest_percent' => $this->late_fee_interest_percent,
                'annual_price_escalation_percent' => $this->annual_price_escalation_percent,
                'units' => array_map(function ($u) {
                    return [
                        'unit_id' => $u['unit_id'],
                        'leased_area_sqm' => $u['leased_area_sqm'],
                        'agreed_rental_price_per_sqm' => $u['agreed_rental_price_per_sqm'],
                        'agreed_service_fee_per_sqm' => $u['agreed_service_fee_per_sqm'],
                    ];
                }, $this->selectedUnits),
            ];

            if ($this->createType === 'BOOKING') {
                $newContract = $contractService->createBookingContract($payload, $this->created_by_sales_id);
                $this->flashMessage = "Đã khởi tạo Hợp đồng Giữ chỗ thành công: {$newContract->contract_number}!";
            } else {
                $newContract = $contractService->createDraftContract($payload, $this->created_by_sales_id);
                $this->flashMessage = "Đã tạo bản nháp Hợp đồng thuê thành công: {$newContract->contract_number}!";
            }

            $this->flashType = 'success';
            $this->closeCreateModal();
        } catch (\Throwable $e) {
            $this->overlapWarning = $e->getMessage();
        }
    }

    // Mở modal xem chi tiết
    public function openDetailModal(string $contractId, ContractService $contractService): void
    {
        $this->selectedContractId = $contractId;
        $this->contractDetail = $contractService->getContractDetail($contractId);
        $this->showDetailModal = true;
    }

    public function closeDetailModal(): void
    {
        $this->showDetailModal = false;
        $this->selectedContractId = null;
        $this->contractDetail = null;
    }

    // Ký duyệt & kích hoạt hợp đồng (Sign)
    public function signContract(string $contractId, ContractService $contractService): void
    {
        try {
            $contract = Contract::findOrFail($contractId);
            $user = Auth::user() ?? Admin::first() ?? PropertyManager::first();

            $contractService->signContract($contract, $user);

            $this->flashMessage = "Hợp đồng {$contract->contract_number} đã được Ký duyệt & Kích hoạt chính thức (ACTIVE)!";
            $this->flashType = 'success';

            if ($this->showDetailModal && $this->selectedContractId === $contractId) {
                $this->contractDetail = $contractService->getContractDetail($contractId);
            }
        } catch (\Throwable $e) {
            $this->flashMessage = 'Lỗi ký hợp đồng: ' . $e->getMessage();
            $this->flashType = 'danger';
        }
    }

    // Mở modal Gia hạn hợp đồng (Renew)
    public function openRenewModal(string $contractId): void
    {
        $contract = Contract::with(['contractUnits', 'building', 'tenant'])->findOrFail($contractId);
        $this->renewContractId = $contractId;
        $this->renewContract = $contract;
        $this->renew_contract_number = $contract->contract_number . '-PL01';
        $this->new_end_date = Carbon::parse($contract->end_date)->addYears(1)->toDateString();
        $this->renew_escalation_percent = (float) $contract->annual_price_escalation_percent ?: 5.0;
        $this->renew_deposit_amount = (float) $contract->deposit_amount;
        $this->showRenewModal = true;
    }

    public function closeRenewModal(): void
    {
        $this->showRenewModal = false;
        $this->renewContractId = null;
        $this->renewContract = null;
    }

    // Xác nhận gia hạn hợp đồng
    public function confirmRenew(ContractService $contractService): void
    {
        $this->validate([
            'new_end_date' => 'required|date',
            'renew_escalation_percent' => 'required|numeric|min:0',
        ]);

        try {
            $oldContract = Contract::findOrFail($this->renewContractId);
            $user = Auth::user() ?? Admin::first() ?? PropertyManager::first();

            $newContract = $contractService->renewContract($oldContract, [
                'new_end_date' => $this->new_end_date,
                'annual_price_escalation_percent' => $this->renew_escalation_percent,
                'contract_number' => $this->renew_contract_number ?: null,
                'deposit_amount' => $this->renew_deposit_amount,
            ], $user);

            $this->flashMessage = "Gia hạn hợp đồng thành công! Hợp đồng chu kỳ mới: {$newContract->contract_number}";
            $this->flashType = 'success';
            $this->closeRenewModal();
        } catch (\Throwable $e) {
            $this->flashMessage = 'Lỗi gia hạn hợp đồng: ' . $e->getMessage();
            $this->flashType = 'danger';
        }
    }

    // Mở modal Thanh lý hợp đồng (Terminate)
    public function openTerminateModal(string $contractId): void
    {
        $contract = Contract::with(['contractUnits', 'building', 'tenant'])->findOrFail($contractId);
        $this->terminateContractId = $contractId;
        $this->terminateContract = $contract;
        $this->terminated_at = Carbon::now()->toDateString();
        $this->termination_note = 'Thanh lý hợp đồng theo nguyện vọng khách thuê / bàn giao hiện trạng đạt chuẩn.';
        $this->maxDepositAllowed = (float) $contract->deposit_amount;
        $this->refund_deposit_amount = (float) $contract->deposit_amount;
        $this->penalty_deposit_amount = 0;
        $this->showTerminateModal = true;
    }

    public function closeTerminateModal(): void
    {
        $this->showTerminateModal = false;
        $this->terminateContractId = null;
        $this->terminateContract = null;
    }

    // Xác nhận thanh lý hợp đồng
    public function confirmTerminate(ContractService $contractService): void
    {
        $this->validate([
            'terminated_at' => 'required|date',
            'termination_note' => 'required|string|min:5',
            'refund_deposit_amount' => 'required|numeric|min:0',
            'penalty_deposit_amount' => 'required|numeric|min:0',
        ]);

        try {
            $contract = Contract::findOrFail($this->terminateContractId);
            $user = Auth::user() ?? Admin::first() ?? PropertyManager::first();

            $contractService->terminateContract($contract, [
                'terminated_at' => $this->terminated_at,
                'termination_note' => $this->termination_note,
                'refund_deposit_amount' => $this->refund_deposit_amount,
                'penalty_deposit_amount' => $this->penalty_deposit_amount,
            ], $user);

            $this->flashMessage = "Hợp đồng {$contract->contract_number} đã được thanh lý thành công! Các mặt bằng đã được tự động giải phóng về trạng thái TRỐNG (AVAILABLE).";
            $this->flashType = 'success';
            $this->closeTerminateModal();
        } catch (\Throwable $e) {
            $this->flashMessage = 'Lỗi thanh lý hợp đồng: ' . $e->getMessage();
            $this->flashType = 'danger';
        }
    }

    public function render(ContractService $contractService)
    {
        $filters = [
            'search' => $this->searchKeyword,
            'building_id' => $this->selectedBuildingId !== 'all' ? $this->selectedBuildingId : null,
            'tenant_id' => $this->selectedTenantId !== 'all' ? $this->selectedTenantId : null,
            'status' => $this->selectedStatus !== 'all' ? $this->selectedStatus : null,
        ];

        $contracts = $contractService->getContracts($filters, Auth::user(), $this->perPage);

        $buildings = Building::orderBy('name')->get(['id', 'name', 'building_code']);
        $tenants = Tenant::orderBy('company_name')->get(['id', 'company_name', 'tax_code']);
        $salesList = Sale::where('status', 'ACTIVE')->get(['id', 'full_name', 'employee_code']);

        $availableUnitsInBuilding = [];
        if ($this->building_id) {
            $availableUnitsInBuilding = Unit::where('building_id', $this->building_id)
                ->whereIn('status', ['AVAILABLE', 'EXPIRING_SOON'])
                ->orderBy('floor_number')
                ->get();
        }

        return view('livewire.contract-manager', [
            'contracts' => $contracts,
            'buildings' => $buildings,
            'tenants' => $tenants,
            'salesList' => $salesList,
            'availableUnitsInBuilding' => $availableUnitsInBuilding,
        ]);
    }
}
