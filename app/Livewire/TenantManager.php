<?php

namespace App\Livewire;

use App\Exceptions\BusinessException;
use App\Models\Tenant;
use App\Services\TenantService;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Livewire Component: Quản Lý Khách Thuê Doanh Nghiệp (Module 3).
 * Phục vụ danh sách, tìm kiếm MST/Công ty, lập hồ sơ doanh nghiệp mới,
 * cập nhật thông tin pháp lý, kiểm soát điều kiện khóa/vô hiệu hóa.
 */
class TenantManager extends Component
{
    use WithPagination;

    // Bộ lọc & Tìm kiếm
    public string $searchKeyword = '';
    public string $selectedStatus = 'all';
    public int $perPage = 10;

    // Thông báo nhanh
    public ?string $flashMessage = null;
    public string $flashType = 'success'; // 'success' | 'danger' | 'warning'

    // Form Modal (Tạo mới & Cập nhật)
    public bool $showFormModal = false;
    public bool $isEditing = false;
    public ?string $tenantId = null;

    public string $tenant_code = '';
    public string $company_name = '';
    public string $tax_code = '';
    public string $representative_name = '';
    public string $representative_position = 'Người đại diện theo pháp luật';
    public string $headquarter_address = '';
    public string $login_email = '';
    public string $billing_email = '';
    public string $contact_phone = '';
    public string $password = '';

    // Modal Chi tiết Khách thuê & Hợp đồng
    public bool $showDetailModal = false;
    public ?Tenant $selectedTenant = null;

    public function updatingSearchKeyword(): void
    {
        $this->resetPage();
    }

    public function updatingSelectedStatus(): void
    {
        $this->resetPage();
    }

    // Mở modal thêm khách thuê mới
    public function openCreateModal(TenantService $tenantService): void
    {
        $this->resetValidation();
        $this->isEditing = false;
        $this->tenantId = null;
        $this->tenant_code = $tenantService->generateTenantCode();
        $this->company_name = '';
        $this->tax_code = '';
        $this->representative_name = '';
        $this->representative_position = 'Giám đốc điều hành';
        $this->headquarter_address = '';
        $this->login_email = '';
        $this->billing_email = '';
        $this->contact_phone = '';
        $this->password = Str::random(10);
        $this->showFormModal = true;
    }

    // Mở modal sửa thông tin khách thuê
    public function openEditModal(string $id, TenantService $tenantService): void
    {
        $this->resetValidation();
        $this->isEditing = true;
        $this->tenantId = $id;

        $tenant = $tenantService->getTenantDetail($id);
        $this->tenant_code = $tenant->tenant_code;
        $this->company_name = $tenant->company_name;
        $this->tax_code = $tenant->tax_code;
        $this->representative_name = $tenant->representative_name;
        $this->representative_position = $tenant->representative_position ?? 'Người đại diện';
        $this->headquarter_address = $tenant->headquarter_address;
        $this->login_email = $tenant->login_email;
        $this->billing_email = $tenant->billing_email;
        $this->contact_phone = $tenant->contact_phone;
        $this->password = '';
        $this->showFormModal = true;
    }

    public function closeFormModal(): void
    {
        $this->showFormModal = false;
        $this->tenantId = null;
    }

    // Lưu thông tin khách thuê (Tạo mới hoặc Cập nhật)
    public function saveTenant(TenantService $tenantService): void
    {
        $taxRule = $this->isEditing
            ? "required|string|max:50|unique:tenants,tax_code,{$this->tenantId}"
            : "required|string|max:50|unique:tenants,tax_code";

        $emailRule = $this->isEditing
            ? "required|email|max:100|unique:tenants,login_email,{$this->tenantId}"
            : "required|email|max:100|unique:tenants,login_email";

        $this->validate([
            'company_name' => 'required|string|max:255',
            'tax_code' => $taxRule,
            'representative_name' => 'required|string|max:100',
            'headquarter_address' => 'required|string|max:255',
            'login_email' => $emailRule,
            'billing_email' => 'required|email|max:100',
            'contact_phone' => 'required|string|max:20',
        ], [
            'company_name.required' => 'Vui lòng nhập tên công ty doanh nghiệp.',
            'tax_code.required' => 'Vui lòng nhập mã số thuế.',
            'tax_code.unique' => 'Mã số thuế này đã tồn tại trong hệ thống.',
            'login_email.required' => 'Vui lòng nhập email đăng nhập.',
            'login_email.unique' => 'Email đăng nhập này đã được sử dụng.',
            'representative_name.required' => 'Vui lòng nhập họ tên người đại diện pháp luật.',
            'contact_phone.required' => 'Vui lòng nhập số điện thoại liên hệ.',
        ]);

        try {
            $data = [
                'company_name' => $this->company_name,
                'tax_code' => $this->tax_code,
                'representative_name' => $this->representative_name,
                'representative_position' => $this->representative_position,
                'headquarter_address' => $this->headquarter_address,
                'login_email' => $this->login_email,
                'billing_email' => $this->billing_email,
                'contact_phone' => $this->contact_phone,
            ];

            if ($this->isEditing) {
                $tenant = Tenant::findOrFail($this->tenantId);
                $tenantService->updateTenant($tenant, $data);
                $this->flashMessage = "Cập nhật hồ sơ doanh nghiệp {$tenant->company_name} thành công!";
            } else {
                $data['tenant_code'] = $this->tenant_code;
                $data['password'] = $this->password ?: Str::random(12);
                $tenant = $tenantService->createTenant($data);
                $this->flashMessage = "Thêm mới khách thuê {$tenant->company_name} ({$tenant->tenant_code}) thành công!";
            }

            $this->flashType = 'success';
            $this->closeFormModal();
        } catch (\Throwable $e) {
            $this->flashMessage = 'Lỗi lưu thông tin khách thuê: ' . $e->getMessage();
            $this->flashType = 'danger';
        }
    }

    // Mở modal xem hồ sơ chi tiết
    public function openDetailModal(string $id, TenantService $tenantService): void
    {
        $this->selectedTenant = $tenantService->getTenantDetail($id);
        $this->showDetailModal = true;
    }

    public function closeDetailModal(): void
    {
        $this->showDetailModal = false;
        $this->selectedTenant = null;
    }

    // Khóa / Vô hiệu hóa tài khoản khách thuê
    public function deactivateTenant(string $id, TenantService $tenantService): void
    {
        try {
            $tenant = Tenant::findOrFail($id);
            $tenantService->deactivateTenant($tenant);

            $this->flashMessage = "Đã vô hiệu hóa tài khoản doanh nghiệp {$tenant->company_name}!";
            $this->flashType = 'success';
        } catch (\Throwable $e) {
            $this->flashMessage = 'Không thể khóa khách thuê: ' . $e->getMessage();
            $this->flashType = 'danger';
        }
    }

    // Kích hoạt lại tài khoản khách thuê
    public function activateTenant(string $id): void
    {
        try {
            $tenant = Tenant::findOrFail($id);
            $tenant->update(['status' => 'ACTIVE']);

            $this->flashMessage = "Đã kích hoạt lại tài khoản doanh nghiệp {$tenant->company_name}!";
            $this->flashType = 'success';
        } catch (\Throwable $e) {
            $this->flashMessage = 'Lỗi: ' . $e->getMessage();
            $this->flashType = 'danger';
        }
    }

    public function render(TenantService $tenantService)
    {
        $filters = [
            'search' => $this->searchKeyword,
            'status' => $this->selectedStatus !== 'all' ? $this->selectedStatus : null,
        ];

        $tenants = $tenantService->getTenants($filters, $this->perPage);

        return view('livewire.tenant-manager', [
            'tenants' => $tenants,
        ]);
    }
}
