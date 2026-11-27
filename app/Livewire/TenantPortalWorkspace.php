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
    public string $activeTab = 'workspace'; // 'workspace' | 'bills' | 'tickets'
    public ?string $tenantId = null;

    // Bộ lọc tab Tickets
    public string $ticketSearchKeyword = '';
    public string $ticketCategory = 'ALL';
    public string $ticketStatus = 'ALL';

    // Modal chi tiết phiếu cước (Debit Note Detail)
    public bool $showDebitModal = false;
    public ?DebitNote $selectedDebitNote = null;

    // Modal xem chi tiết & sơ đồ mặt bằng
    public bool $showUnitModal = false;
    public ?Unit $selectedUnit = null;

    // Bắt buộc đổi mật khẩu lần đầu (First-login password change)
    public bool $showForceChangeModal = false;
    public string $current_password = '';
    public string $new_password = '';
    public string $new_password_confirmation = '';
    public ?string $passwordFlashError = null;
    public ?string $passwordFlashSuccess = null;

    public function mount(?string $tenantId = null): void
    {
        $requested = $tenantId ?: (request('tenant_id') ?: (request('tenant') ?: request()->cookie('tenant_id')));
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

        // Kiểm tra Sanctum Token từ cookie hoặc header
        $rawToken = request()->bearerToken() ?? request()->cookie('auth_token') ?? $_COOKIE['auth_token'] ?? null;
        if ($rawToken) {
            $token = (string) $rawToken;
            try {
                $decrypted = decrypt($rawToken, false);
                $validated = \Illuminate\Cookie\CookieValuePrefix::validate('auth_token', $decrypted, [app('encrypter')->getKey()]);
                $token = $validated ?? \Illuminate\Cookie\CookieValuePrefix::remove($decrypted);
            } catch (\Throwable $e) {}

            $token = urldecode(trim($token, "\"' "));
            $accessToken = \Laravel\Sanctum\PersonalAccessToken::findToken($token);
            if ($accessToken && $accessToken->tokenable instanceof Tenant) {
                $this->tenantId = $accessToken->tokenable->id;
                Auth::guard('tenant')->setUser($accessToken->tokenable);
                return;
            }
        }

        $user = Auth::guard('tenant')->user() ?? Auth::user();
        if ($user instanceof Tenant) {
            $this->tenantId = $user->id;
            return;
        }

        // Mặc định demo tenant nếu chưa xác định được để luôn hiển thị giao diện mẫu đầy đủ
        if (!$this->tenantId) {
            $demoTenant = Tenant::where('login_email', 'tenant.demo@officeleasing.vn')->first()
                ?? Tenant::where('status', 'ACTIVE')->has('contracts')->first()
                ?? Tenant::first();
            if ($demoTenant) {
                $this->tenantId = $demoTenant->id;
            }
        }

        // Tự động kích hoạt modal đổi mật khẩu nếu khách có cờ must_change_password
        if ($this->tenantId) {
            $t = Tenant::find($this->tenantId);
            if ($t && $t->must_change_password) {
                $this->showForceChangeModal = true;
            }
        }
    }

    // Đổi mật khẩu khởi tạo lần đầu cho khách thuê
    public function updateInitialPassword(): void
    {
        $this->passwordFlashError = null;
        $this->passwordFlashSuccess = null;

        $this->validate([
            'current_password' => ['required'],
            'new_password' => ['required', 'string', \App\Helpers\PasswordHelper::strongRule(), 'confirmed'],
        ], [
            'current_password.required' => 'Vui lòng nhập mật khẩu khởi tạo hiện tại.',
            'new_password.required' => 'Vui lòng nhập mật khẩu mới.',
            'new_password.min' => 'Mật khẩu mới phải có tối thiểu 8 ký tự.',
            'new_password.confirmed' => 'Xác nhận mật khẩu mới không khớp.',
        ]);

        $tenant = Tenant::find($this->tenantId);
        if (!$tenant || !\Illuminate\Support\Facades\Hash::check($this->current_password, $tenant->password)) {
            $this->passwordFlashError = 'Mật khẩu khởi tạo hiện tại không chính xác.';
            return;
        }

        $tenant->update([
            'password' => $this->new_password,
            'must_change_password' => false,
            'password_changed_at' => now(),
        ]);

        $this->passwordFlashSuccess = 'Đổi mật khẩu thành công! Quý khách đã bảo vệ an toàn cho tài khoản doanh nghiệp.';
        $this->showForceChangeModal = false;
        $this->current_password = '';
        $this->new_password = '';
        $this->new_password_confirmation = '';
    }

    public function closeForceChangeModal(): void
    {
        $this->showForceChangeModal = false;
        $this->passwordFlashError = null;
    }

    public function switchTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function clearTicketSearch(): void
    {
        $this->ticketSearchKeyword = '';
    }

    public function filterTicketCategory(string $category): void
    {
        $this->ticketCategory = $category;
    }

    public function filterTicketStatus(string $status): void
    {
        $this->ticketStatus = $status;
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
        $tenantTickets = collect();
        $openTicketsCount = 0;
        $totalTicketsCount = 0;

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

            // Truy vấn danh sách Ticket hỗ trợ kỹ thuật và thi công ngoài giờ của Khách thuê
            $ticketsQuery = \App\Models\Ticket::with(['building', 'unit', 'contract'])
                ->where(function ($q) {
                    $q->where('tenant_id', $this->tenantId)
                      ->orWhere('creator_tenant_id', $this->tenantId);
                });

            $totalTicketsCount = (clone $ticketsQuery)->count();
            $openTicketsCount = (clone $ticketsQuery)
                ->whereIn('status', ['NEW', 'OPEN', 'IN_PROGRESS', 'WAITING_CONFIRMATION', 'RESOLVED'])
                ->count();

            if (!empty($this->ticketSearchKeyword)) {
                $term = '%' . trim($this->ticketSearchKeyword) . '%';
                $ticketsQuery->where(function ($q) use ($term) {
                    $q->where('ticket_code', 'like', $term)
                      ->orWhere('title', 'like', $term)
                      ->orWhere('description', 'like', $term);
                });
            }

            if ($this->ticketCategory !== 'ALL' && !empty($this->ticketCategory)) {
                $ticketsQuery->where('category', $this->ticketCategory);
            }

            if ($this->ticketStatus !== 'ALL' && !empty($this->ticketStatus)) {
                $ticketsQuery->where('status', $this->ticketStatus);
            }

            $tenantTickets = $ticketsQuery->latest()->get();
        }

        $rentedBuildings = collect();
        if ($tenant && $tenant->contracts) {
            $rentedBuildings = $tenant->contracts->pluck('building')->filter()->unique('id')->values();
        }
        if ($rentedBuildings->isEmpty() && isset($activeContract) && $activeContract?->building) {
            $rentedBuildings = collect([$activeContract->building]);
        }

        return view('livewire.tenant-portal-workspace', [
            'tenant' => $tenant,
            'activeContract' => $activeContract,
            'recentDebitNotes' => $recentDebitNotes,
            'latestDebitNote' => $latestDebitNote,
            'tenantTickets' => $tenantTickets,
            'openTicketsCount' => $openTicketsCount,
            'totalTicketsCount' => $totalTicketsCount,
            'rentedBuildings' => $rentedBuildings,
        ]);
    }
}
