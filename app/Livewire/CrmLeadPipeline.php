<?php

namespace App\Livewire;

use App\Enums\LeadStatus;
use App\Exceptions\BusinessException;
use App\Models\Building;
use App\Models\Lead;
use App\Models\Sale;
use App\Services\LeadActivityService;
use App\Services\LeadService;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Livewire Component: Quản Lý Phễu Bán Hàng & Chăm Sóc Khách Tiềm Năng CRM (Module 2).
 * Hỗ trợ Kanban Pipeline, Danh sách phân trang, Activity Timeline, Gán Sales & Chuyển đổi Tenant.
 */
class CrmLeadPipeline extends Component
{
    use WithPagination;

    public string $viewMode = 'kanban'; // 'kanban' hoặc 'table'
    public string $searchKeyword = '';
    public string $selectedBuildingId = 'all';
    public string $selectedStatus = 'all';
    public int $perPage = 10;

    // Trạng thái Drawer Chi tiết Lead & Timeline
    public bool $showDetailDrawer = false;
    public ?string $selectedLeadId = null;
    public ?Lead $selectedLead = null;

    // Form ghi nhật ký tương tác mới
    public string $newActivityType = 'CALL';
    public string $newActivityTitle = '';
    public string $newActivityContent = '';
    public ?string $newActivityEventAt = null;

    // Trạng thái Modal Đánh dấu Thất bại (LOST)
    public bool $showLostModal = false;
    public ?string $lostLeadId = null;
    public string $lostCategory = '';
    public string $lostDetail = '';

    // Trạng thái Modal Điều phối Sales (Assign)
    public bool $showAssignModal = false;
    public ?string $assignLeadId = null;
    public ?Lead $assignLead = null;
    public string $targetSalesId = '';
    public string $assignNote = '';

    // Trạng thái Modal Chuyển đổi Khách thuê (Convert to Tenant)
    public bool $showConvertModal = false;
    public ?string $convertLeadId = null;
    public ?Lead $convertLead = null;
    public string $tenantTaxCode = '';
    public string $tenantCompanyName = '';
    public string $tenantRepName = '';
    public string $tenantRepPosition = 'Đại diện theo pháp luật';
    public string $tenantHeadquarter = '';
    public string $tenantEmail = '';
    public string $tenantPhone = '';

    // Trạng thái Modal Tạo Lead Thủ Công (Sales tự nhập)
    public bool $showCreateModal = false;
    public string $manualContactName = '';
    public string $manualPhone = '';
    public string $manualEmail = '';
    public string $manualCompanyName = '';
    public ?string $manualBuildingId = null;
    public ?float $manualAreaSqm = null;
    public string $manualSource = 'HOTLINE';
    public string $manualNote = '';

    // Thông báo phản hồi giao diện
    public ?string $flashSuccess = null;
    public ?string $flashError = null;

    /**
     * Tùy chỉnh view phân trang theo phong cách Maison Office.
     */
    public function paginationView(): string
    {
        return 'livewire.pagination';
    }

    /**
     * Tự động reset trang khi thay đổi bộ lọc.
     */
    public function updated($property): void
    {
        if (in_array($property, ['searchKeyword', 'selectedBuildingId', 'selectedStatus', 'perPage'])) {
            $this->resetPage();
        }
    }

    public function setViewMode(string $mode): void
    {
        $this->viewMode = $mode;
    }

    public function clearFlash(): void
    {
        $this->flashSuccess = null;
        $this->flashError = null;
    }

    /**
     * Chuyển trạng thái Lead nhanh trên Kanban hoặc Table.
     */
    public function updateStatus(string $leadId, string $status, LeadService $leadService): void
    {
        $this->clearFlash();

        $lead = Lead::findOrFail($leadId);

        if ($status === 'LOST') {
            $this->lostLeadId = $leadId;
            $this->lostCategory = '';
            $this->lostDetail = '';
            $this->showLostModal = true;
            return;
        }

        try {
            $leadService->updateLeadStatus($lead, $status);
            $this->flashSuccess = "Đã cập nhật trạng thái của khách [{$lead->contact_full_name}] thành công.";
            if ($this->selectedLeadId === $leadId) {
                $this->selectedLead = $lead->fresh(['building', 'unit', 'assignedSales', 'convertedTenant']);
            }
        } catch (BusinessException $e) {
            $this->flashError = $e->getMessage();
        }
    }

    /**
     * Xác nhận đóng trạng thái Thất bại (LOST) kèm lý do bắt buộc.
     */
    public function confirmLost(LeadService $leadService): void
    {
        $this->clearFlash();

        $this->validate([
            'lostCategory' => ['required', 'string'],
            'lostDetail' => ['required', 'string', 'min:5'],
        ], [
            'lostCategory.required' => 'Vui lòng chọn danh mục lý do thất bại.',
            'lostDetail.required' => 'Vui lòng điền chi tiết lý do thất bại.',
            'lostDetail.min' => 'Chi tiết lý do phải có ít nhất 5 ký tự.',
        ]);

        $lead = Lead::findOrFail($this->lostLeadId);

        try {
            $leadService->updateLeadStatus($lead, LeadStatus::LOST, $this->lostCategory, $this->lostDetail);
            $this->flashSuccess = "Đã đóng trạng thái Thất bại cho khách [{$lead->contact_full_name}].";
            $this->showLostModal = false;
            $this->lostLeadId = null;
            if ($this->selectedLeadId === $lead->id) {
                $this->selectedLead = $lead->fresh(['building', 'unit', 'assignedSales', 'convertedTenant']);
            }
        } catch (BusinessException $e) {
            $this->flashError = $e->getMessage();
        }
    }

    /**
     * Mở Drawer xem hồ sơ chi tiết Lead và Dòng thời gian (Timeline).
     */
    public function openDetail(string $leadId, LeadActivityService $activityService): void
    {
        $this->clearFlash();
        $this->selectedLeadId = $leadId;
        $this->selectedLead = Lead::with(['building', 'unit', 'assignedSales', 'convertedTenant', 'activities.sales'])
            ->findOrFail($leadId);

        $this->newActivityType = 'CALL';
        $this->newActivityTitle = '';
        $this->newActivityContent = '';
        $this->newActivityEventAt = now()->format('Y-m-d\TH:i');

        $this->showDetailDrawer = true;
    }

    public function closeDetail(): void
    {
        $this->showDetailDrawer = false;
        $this->selectedLeadId = null;
        $this->selectedLead = null;
    }

    /**
     * Ghi thêm một dòng nhật ký tương tác mới vào Activity Timeline.
     */
    public function addActivity(LeadActivityService $activityService): void
    {
        $this->clearFlash();

        $this->validate([
            'newActivityType' => ['required', 'string'],
            'newActivityTitle' => ['required', 'string', 'min:3'],
            'newActivityContent' => ['nullable', 'string'],
        ], [
            'newActivityTitle.required' => 'Vui lòng nhập tóm tắt tiêu đề tương tác.',
            'newActivityTitle.min' => 'Tiêu đề tương tác cần có ít nhất 3 ký tự.',
        ]);

        if (!$this->selectedLead) {
            return;
        }

        try {
            $activityService->logActivity($this->selectedLead, [
                'activity_type' => $this->newActivityType,
                'title' => $this->newActivityTitle,
                'content' => $this->newActivityContent,
                'event_at' => $this->newActivityEventAt ?: now(),
            ], $this->selectedLead->assigned_sales_id);

            $this->flashSuccess = 'Đã lưu nhật ký tương tác mới vào Timeline.';
            $this->newActivityTitle = '';
            $this->newActivityContent = '';
            $this->selectedLead = $this->selectedLead->fresh(['building', 'unit', 'assignedSales', 'convertedTenant', 'activities.sales']);
        } catch (BusinessException $e) {
            $this->flashError = $e->getMessage();
        }
    }

    /**
     * Mở Modal điều phối / Gán Sales phụ trách.
     */
    public function openAssign(string $leadId): void
    {
        $this->clearFlash();
        $this->assignLeadId = $leadId;
        $this->assignLead = Lead::with('assignedSales')->findOrFail($leadId);
        $this->targetSalesId = (string) ($this->assignLead->assigned_sales_id ?? '');
        $this->assignNote = '';
        $this->showAssignModal = true;
    }

    /**
     * Xác nhận điều phối Sales.
     */
    public function submitAssign(LeadService $leadService): void
    {
        $this->clearFlash();

        $this->validate([
            'targetSalesId' => ['required', 'string', 'exists:sales,id'],
            'assignNote' => ['nullable', 'string'],
        ], [
            'targetSalesId.required' => 'Vui lòng chọn nhân viên Sales phụ trách.',
            'targetSalesId.exists' => 'Nhân viên Sales không tồn tại.',
        ]);

        $lead = Lead::findOrFail($this->assignLeadId);

        try {
            $leadService->assignSales($lead, $this->targetSalesId, $this->assignNote);
            $this->flashSuccess = "Đã bàn giao Lead [{$lead->contact_full_name}] cho nhân viên Sales thành công.";
            $this->showAssignModal = false;
            $this->assignLeadId = null;
            if ($this->selectedLeadId === $lead->id) {
                $this->selectedLead = $lead->fresh(['building', 'unit', 'assignedSales', 'convertedTenant']);
            }
        } catch (BusinessException $e) {
            $this->flashError = $e->getMessage();
        }
    }

    /**
     * Mở Modal chuyển đổi Lead WON sang Khách thuê chính thức (Tenant).
     */
    public function openConvert(string $leadId): void
    {
        $this->clearFlash();
        $this->convertLeadId = $leadId;
        $this->convertLead = Lead::findOrFail($leadId);

        $this->tenantTaxCode = '';
        $this->tenantCompanyName = $this->convertLead->company_name ?: $this->convertLead->contact_full_name;
        $this->tenantRepName = $this->convertLead->contact_full_name;
        $this->tenantRepPosition = 'Đại diện theo pháp luật';
        $this->tenantHeadquarter = 'TP. Hồ Chí Minh';
        $this->tenantEmail = $this->convertLead->email;
        $this->tenantPhone = $this->convertLead->phone;

        $this->showConvertModal = true;
    }

    /**
     * Xác nhận chuyển đổi Lead sang Tenant.
     */
    public function submitConvert(LeadService $leadService): void
    {
        $this->clearFlash();

        $this->validate([
            'tenantTaxCode' => ['required', 'string', 'regex:/^[0-9\-]{10,14}$/'],
            'tenantCompanyName' => ['required', 'string', 'min:3'],
            'tenantRepName' => ['required', 'string'],
            'tenantEmail' => ['required', 'email'],
            'tenantPhone' => ['required', 'string'],
        ], [
            'tenantTaxCode.required' => 'Vui lòng nhập mã số thuế doanh nghiệp.',
            'tenantTaxCode.regex' => 'Mã số thuế không đúng định dạng (10 hoặc 13 số).',
            'tenantCompanyName.required' => 'Vui lòng nhập tên công ty doanh nghiệp.',
        ]);

        $lead = Lead::findOrFail($this->convertLeadId);

        try {
            $tenant = $leadService->convertToTenant($lead, [
                'tax_code' => $this->tenantTaxCode,
                'company_name' => $this->tenantCompanyName,
                'representative_name' => $this->tenantRepName,
                'representative_position' => $this->tenantRepPosition,
                'headquarter_address' => $this->tenantHeadquarter,
                'login_email' => $this->tenantEmail,
                'contact_phone' => $this->tenantPhone,
            ]);

            $this->flashSuccess = "Chuyển đổi thành công! Đã tạo hồ sơ Khách thuê chính thức [{$tenant->tenant_code}].";
            $this->showConvertModal = false;
            $this->convertLeadId = null;
            if ($this->selectedLeadId === $lead->id) {
                $this->selectedLead = $lead->fresh(['building', 'unit', 'assignedSales', 'convertedTenant']);
            }
        } catch (BusinessException $e) {
            $this->flashError = $e->getMessage();
        }
    }

    /**
     * Mở modal tạo Lead thủ công từ hotline/offline.
     */
    public function openCreateLead(): void
    {
        $this->clearFlash();
        $this->manualContactName = '';
        $this->manualPhone = '';
        $this->manualEmail = '';
        $this->manualCompanyName = '';
        $this->manualBuildingId = null;
        $this->manualAreaSqm = null;
        $this->manualSource = 'HOTLINE';
        $this->manualNote = '';
        $this->showCreateModal = true;
    }

    /**
     * Lưu Lead mới nhập thủ công từ CRM.
     */
    public function submitCreateManual(LeadService $leadService): void
    {
        $this->clearFlash();

        $this->validate([
            'manualContactName' => ['required', 'string', 'min:2'],
            'manualPhone' => ['required', 'string', 'regex:/^([0-9\s\-\+\(\)]*)$/'],
            'manualEmail' => ['required', 'email'],
            'manualBuildingId' => ['nullable', 'exists:buildings,id'],
        ], [
            'manualContactName.required' => 'Vui lòng nhập họ tên khách hàng.',
            'manualPhone.required' => 'Vui lòng nhập số điện thoại liên hệ.',
            'manualEmail.required' => 'Vui lòng nhập email liên hệ.',
        ]);

        try {
            $lead = $leadService->storeCrmLead([
                'contact_full_name' => $this->manualContactName,
                'company_name' => $this->manualCompanyName ?: null,
                'phone' => $this->manualPhone,
                'email' => $this->manualEmail,
                'lead_source' => $this->manualSource,
                'building_id' => $this->manualBuildingId ?: null,
                'desired_area_sqm' => $this->manualAreaSqm ? (float) $this->manualAreaSqm : null,
                'customer_note' => $this->manualNote ?: null,
            ]);

            $this->flashSuccess = "Đã tiếp nhận hồ sơ Lead mới [{$lead->contact_full_name}] thành công.";
            $this->showCreateModal = false;
        } catch (BusinessException $e) {
            $this->flashError = $e->getMessage();
        }
    }

    public function render(LeadService $leadService)
    {
        $filters = [];
        if ($this->selectedBuildingId !== 'all') {
            $filters['building_id'] = $this->selectedBuildingId;
        }
        if ($this->selectedStatus !== 'all') {
            $filters['status'] = $this->selectedStatus;
        }
        if (!empty($this->searchKeyword)) {
            $filters['keyword'] = $this->searchKeyword;
        }

        // Truy vấn danh sách Lead theo bộ lọc
        $paginatedLeads = $leadService->getLeads($filters, null, $this->perPage);

        // Chuẩn bị dữ liệu cho 4 cột Kanban chuẩn nghiệp vụ
        $kanbanQuery = Lead::query()
            ->with(['building', 'unit', 'assignedSales'])
            ->when($this->selectedBuildingId !== 'all', fn ($q) => $q->where('building_id', $this->selectedBuildingId))
            ->when(!empty($this->searchKeyword), function ($q) {
                $kw = $this->searchKeyword;
                $q->where(function ($sub) use ($kw) {
                    $sub->where('contact_full_name', 'like', "%{$kw}%")
                        ->orWhere('company_name', 'like', "%{$kw}%")
                        ->orWhere('phone', 'like', "%{$kw}%");
                });
            })
            ->latest();

        $allLeads = $kanbanQuery->get();

        $kanbanColumns = [
            'NEW' => [
                'title' => '1. TIẾP NHẬN LEAD',
                'badge' => 'info',
                'leads' => $allLeads->filter(fn ($l) => in_array($l->status->value, ['PENDING_ASSIGN', 'NEW'])),
            ],
            'SURVEYING' => [
                'title' => '2. ĐẶT LỊCH KHẢO SÁT',
                'badge' => 'warning',
                'leads' => $allLeads->filter(fn ($l) => $l->status === LeadStatus::NEGOTIATING && !empty($l->preferred_site_tour_at)),
            ],
            'NEGOTIATING' => [
                'title' => '3. ĐÀM PHÁN BÁO GIÁ',
                'badge' => 'info',
                'leads' => $allLeads->filter(fn ($l) => $l->status === LeadStatus::NEGOTIATING && empty($l->preferred_site_tour_at)),
            ],
            'WON' => [
                'title' => '4. CHỐT KÝ HỢP ĐỒNG',
                'badge' => 'success',
                'leads' => $allLeads->filter(fn ($l) => $l->status === LeadStatus::WON),
            ],
        ];

        $buildings = Building::query()->published()->orderBy('name')->get(['id', 'name']);
        $salesList = Sale::query()->where('status', 'ACTIVE')->orderBy('full_name')->get(['id', 'full_name', 'employee_code', 'max_concurrent_leads']);

        return view('livewire.crm-lead-pipeline', [
            'paginatedLeads' => $paginatedLeads,
            'kanbanColumns' => $kanbanColumns,
            'buildings' => $buildings,
            'salesList' => $salesList,
            'totalActiveLeads' => $allLeads->whereIn('status.value', ['NEW', 'NEGOTIATING', 'PENDING_ASSIGN'])->count(),
            'totalWonLeads' => $allLeads->where('status.value', 'WON')->count(),
        ]);
    }
}
