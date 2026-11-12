<?php

namespace App\Services;

use App\Enums\LeadStatus;
use App\Events\LeadAssignedToSalesEvent;
use App\Events\LeadPendingAssignmentEvent;
use App\Exceptions\BusinessException;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\Sale;
use App\Models\Tenant;
use App\Models\Unit;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Service tầng nghiệp vụ (BLL) phụ trách CRM & Quản lý Khách hàng tiềm năng (Leads).
 */
class LeadService
{
    /**
     * Tiếp nhận yêu cầu tư vấn / Báo giá / Site tour từ Public Portal.
     *
     * @param array<string, mixed> $data
     * @return Lead
     * @throws BusinessException
     */
    public function storePortalLead(array $data): Lead
    {
        $buildingId = $data['building_id'];
        $phone = trim($data['phone']);
        $email = trim($data['email']);

        // 1. Kiểm tra chống trùng lặp 30 ngày (Anti-duplicate 30 days)
        $existingLead = Lead::query()
            ->where('building_id', $buildingId)
            ->where('created_at', '>=', now()->subDays(30))
            ->where(function ($q) use ($phone, $email) {
                $q->where('phone', $phone)->orWhere('email', $email);
            })
            ->first();

        if ($existingLead) {
            // Append một dòng hoạt động mới vào Lead cũ thay vì nhân bản Lead rác
            LeadActivity::create([
                'lead_id' => $existingLead->id,
                'sales_id' => $existingLead->assigned_sales_id,
                'activity_type' => 'NOTE',
                'title' => 'Khách gửi lại yêu cầu tư vấn từ Website',
                'content' => $data['customer_note'] ?? 'Khách hàng gửi lại form liên hệ trên Portal.',
                'event_at' => now(),
            ]);

            $existingLead->touch();
            return $existingLead;
        }

        // 2. Ràng buộc toàn vẹn: Mặt bằng (nếu chọn) phải thuộc đúng tòa nhà
        if (!empty($data['unit_id'])) {
            $unitExists = Unit::query()
                ->where('id', $data['unit_id'])
                ->where('building_id', $buildingId)
                ->exists();

            if (!$unitExists) {
                throw new BusinessException('Mặt bằng văn phòng đã chọn không thuộc tòa nhà này.', 422);
            }
        }

        // 3. Phân bổ Sales theo giải thuật Least-Active-Leads
        $optimalSales = $this->findOptimalSales($buildingId);

        return DB::transaction(function () use ($data, $optimalSales) {
            $isAssigned = $optimalSales !== null;

            $lead = Lead::create([
                'contact_full_name' => $data['contact_full_name'],
                'company_name' => $data['company_name'] ?? null,
                'phone' => $data['phone'],
                'email' => $data['email'],
                'lead_source' => $data['lead_source'] ?? 'WEBSITE',
                'building_id' => $data['building_id'],
                'unit_id' => $data['unit_id'] ?? null,
                'desired_area_sqm' => $data['desired_area_sqm'] ?? null,
                'budget_min' => $data['budget_min'] ?? null,
                'budget_max' => $data['budget_max'] ?? null,
                'preferred_site_tour_at' => $data['preferred_site_tour_at'] ?? null,
                'customer_note' => $data['customer_note'] ?? null,
                'assigned_sales_id' => $isAssigned ? $optimalSales->id : null,
                'assigned_at' => $isAssigned ? now() : null,
                'sla_deadline_at' => $isAssigned ? now()->addHours(24) : null,
                'first_contacted_at' => null,
                'status' => $isAssigned ? LeadStatus::NEW : LeadStatus::PENDING_ASSIGN,
            ]);

            // 4. Phát sóng Realtime qua Reverb
            if ($isAssigned) {
                LeadAssignedToSalesEvent::dispatch($lead);
            } else {
                LeadPendingAssignmentEvent::dispatch($lead);
            }

            return $lead;
        });
    }

    /**
     * Tìm kiếm Sales tối ưu nhất theo giải thuật Least-Active-Leads kết hợp Tie-Breaker.
     *
     * @param string $buildingId
     * @return Sale|null
     */
    public function findOptimalSales(string $buildingId): ?Sale
    {
        return Sale::query()
            ->where('status', 'ACTIVE')
            ->whereHas('buildings', function ($q) use ($buildingId) {
                $q->where('buildings.id', $buildingId)
                  ->where('sale_buildings.is_active', true);
            })
            ->withCount(['leads as active_leads_count' => function ($q) {
                $q->whereIn('status', [LeadStatus::NEW, LeadStatus::NEGOTIATING]);
            }])
            ->withMax(['leads as last_assigned_at' => function ($q) {
                $q->whereNotNull('assigned_at');
            }], 'assigned_at')
            ->get()
            ->filter(fn (Sale $s) => $s->active_leads_count < $s->max_concurrent_leads)
            ->sort(function (Sale $a, Sale $b) {
                // 1. Ưu tiên Sales có ít lead active nhất
                if ($a->active_leads_count !== $b->active_leads_count) {
                    return $a->active_leads_count <=> $b->active_leads_count;
                }

                // 2. Tie-Breaker: Ưu tiên người chưa từng nhận lead (last_assigned_at is null)
                if ($a->last_assigned_at === null && $b->last_assigned_at !== null) {
                    return -1;
                }
                if ($a->last_assigned_at !== null && $b->last_assigned_at === null) {
                    return 1;
                }

                // 3. Ưu tiên người có lần nhận lead cuối cùng sớm nhất (xa nhất trong quá khứ)
                return strcmp((string)$a->last_assigned_at, (string)$b->last_assigned_at);
            })
            ->first();
    }

    /**
     * Lấy danh sách Leads có phân trang và bộ lọc chuyên sâu cho CRM.
     *
     * @param array<string, mixed> $filters
     * @param mixed $user
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getLeads(array $filters = [], mixed $user = null, int $perPage = 15): LengthAwarePaginator
    {
        $query = Lead::query()
            ->with(['building', 'unit', 'assignedSales', 'convertedTenant']);

        // Phân quyền dữ liệu theo vai trò (Data Isolation)
        if ($user instanceof Sale) {
            $query->where(function ($q) use ($user) {
                $q->where('assigned_sales_id', $user->id)
                  ->orWhereHas('building.sales', function ($b) use ($user) {
                      $b->where('sales.id', $user->id);
                  });
            });
        }

        // Lọc theo trạng thái phễu Pipeline
        if (!empty($filters['status'])) {
            $statusValue = is_string($filters['status']) ? strtoupper($filters['status']) : $filters['status']->value;
            $query->where('status', $statusValue);
        }

        // Lọc theo Tòa nhà quan tâm
        if (!empty($filters['building_id'])) {
            $query->where('building_id', $filters['building_id']);
        }

        // Lọc theo Nhân viên Sales phụ trách
        if (!empty($filters['assigned_sales_id'])) {
            $query->where('assigned_sales_id', $filters['assigned_sales_id']);
        }

        // Lọc theo Nguồn tiếp nhận
        if (!empty($filters['lead_source'])) {
            $query->where('lead_source', strtoupper($filters['lead_source']));
        }

        // Tìm kiếm theo từ khóa (Họ tên, SĐT, Email, Tên công ty)
        if (!empty($filters['keyword'])) {
            $kw = trim($filters['keyword']);
            $query->where(function ($q) use ($kw) {
                $q->where('contact_full_name', 'like', "%{$kw}%")
                  ->orWhere('company_name', 'like', "%{$kw}%")
                  ->orWhere('phone', 'like', "%{$kw}%")
                  ->orWhere('email', 'like', "%{$kw}%");
            });
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    /**
     * Tạo Lead thủ công từ CRM (Sales tiếp nhận qua hotline/gặp trực tiếp).
     *
     * @param array<string, mixed> $data
     * @param mixed $user
     * @return Lead
     */
    public function storeCrmLead(array $data, mixed $user = null): Lead
    {
        $salesId = $data['assigned_sales_id'] ?? ($user instanceof Sale ? $user->id : null);

        // Nếu chưa gán sales và có building_id, tự động tìm sales
        if (!$salesId && !empty($data['building_id'])) {
            $optimal = $this->findOptimalSales($data['building_id']);
            $salesId = $optimal?->id;
        }

        $isAssigned = !empty($salesId);

        return DB::transaction(function () use ($data, $salesId, $isAssigned) {
            $lead = Lead::create([
                'contact_full_name' => $data['contact_full_name'],
                'company_name' => $data['company_name'] ?? null,
                'phone' => $data['phone'],
                'email' => $data['email'],
                'lead_source' => $data['lead_source'] ?? 'HOTLINE',
                'building_id' => $data['building_id'] ?? null,
                'unit_id' => $data['unit_id'] ?? null,
                'desired_area_sqm' => $data['desired_area_sqm'] ?? null,
                'budget_min' => $data['budget_min'] ?? null,
                'budget_max' => $data['budget_max'] ?? null,
                'preferred_site_tour_at' => $data['preferred_site_tour_at'] ?? null,
                'customer_note' => $data['customer_note'] ?? null,
                'assigned_sales_id' => $salesId,
                'assigned_at' => $isAssigned ? now() : null,
                'sla_deadline_at' => $isAssigned ? now()->addHours(24) : null,
                'first_contacted_at' => null,
                'status' => $isAssigned ? LeadStatus::NEW : LeadStatus::PENDING_ASSIGN,
            ]);

            if ($isAssigned) {
                LeadAssignedToSalesEvent::dispatch($lead);
            } else {
                LeadPendingAssignmentEvent::dispatch($lead);
            }

            return $lead;
        });
    }

    /**
     * Cập nhật thông tin nhu cầu của Lead.
     *
     * @param Lead $lead
     * @param array<string, mixed> $data
     * @return Lead
     * @throws BusinessException
     */
    public function updateLead(Lead $lead, array $data): Lead
    {
        if ($lead->status === LeadStatus::WON) {
            throw new BusinessException('Không thể chỉnh sửa thông tin Lead đã ký hợp đồng thành công.', 422);
        }

        $buildingId = $data['building_id'] ?? $lead->building_id;
        $unitId = $data['unit_id'] ?? $lead->unit_id;

        if ($unitId && $buildingId) {
            $unitExists = Unit::query()
                ->where('id', $unitId)
                ->where('building_id', $buildingId)
                ->exists();

            if (!$unitExists) {
                throw new BusinessException('Mặt bằng văn phòng đã chọn không thuộc tòa nhà này.', 422);
            }
        }

        $lead->update($data);
        return $lead->fresh(['building', 'unit', 'assignedSales']);
    }

    /**
     * Xóa mềm hồ sơ Lead (Chỉ áp dụng khi Lead rác hoặc trùng lặp).
     *
     * @param Lead $lead
     * @return bool
     * @throws BusinessException
     */
    public function deleteLead(Lead $lead): bool
    {
        if ($lead->status === LeadStatus::WON) {
            throw new BusinessException('Không thể xóa Lead đã chốt thuê và chuyển đổi thành Khách thuê chính thức.', 422);
        }

        return (bool) $lead->delete();
    }

    /**
     * Cập nhật trạng thái tiến trình Sales (Pipeline Status).
     *
     * @param Lead $lead
     * @param string|LeadStatus $status
     * @param string|null $lostCategory
     * @param string|null $lostDetail
     * @return Lead
     * @throws BusinessException
     */
    public function updateLeadStatus(
        Lead $lead,
        string|LeadStatus $status,
        ?string $lostCategory = null,
        ?string $lostDetail = null
    ): Lead {
        $newStatus = is_string($status) ? LeadStatus::from($status) : $status;

        // Ràng buộc cấm đóng trạng thái LOST mà thiếu lý do
        if ($newStatus === LeadStatus::LOST) {
            if (empty($lostCategory) || empty($lostDetail)) {
                throw new BusinessException('Bắt buộc phải cung cấp danh mục và chi tiết lý do khi chuyển sang trạng thái Thất bại (LOST).', 422);
            }
            $lead->lost_reason_category = $lostCategory;
            $lead->lost_reason_detail = $lostDetail;
        }

        // Tự động kích hoạt first_contacted_at nếu chuyển sang NEGOTIATING mà chưa có
        if ($newStatus === LeadStatus::NEGOTIATING && $lead->first_contacted_at === null) {
            $lead->first_contacted_at = now();
        }

        $lead->status = $newStatus;
        $lead->save();

        return $lead;
    }

    /**
     * Điều phối thủ công / Gán Lead cho Sales khác.
     *
     * @param Lead $lead
     * @param string $salesId
     * @param string|null $assignmentNote
     * @return Lead
     * @throws BusinessException
     */
    public function assignSales(Lead $lead, string $salesId, ?string $note = null): Lead
    {
        $sale = Sale::findOrFail($salesId);

        if ($sale->status !== 'ACTIVE') {
            throw new BusinessException('Nhân viên Sales được chỉ định đang bị khóa hoặc không hoạt động.', 422);
        }

        // Kiểm tra trần tải của Sales
        $activeCount = Lead::query()
            ->where('assigned_sales_id', $sale->id)
            ->whereIn('status', [LeadStatus::NEW, LeadStatus::NEGOTIATING])
            ->count();

        if ($activeCount >= $sale->max_concurrent_leads) {
            throw new BusinessException("Nhân viên [{$sale->full_name}] đã đạt trần tải tối đa ({$sale->max_concurrent_leads} leads đang xử lý).", 422);
        }

        return DB::transaction(function () use ($lead, $sale, $note) {
            $oldSalesName = $lead->assignedSales?->full_name ?? 'Chưa phân bổ';

            $lead->assigned_sales_id = $sale->id;
            $lead->assigned_at = now();
            $lead->sla_deadline_at = now()->addHours(24);

            if ($lead->status === LeadStatus::PENDING_ASSIGN) {
                $lead->status = LeadStatus::NEW;
            }

            $lead->save();

            // Ghi nhật ký chuyển giao hệ thống vào lead_activities
            LeadActivity::create([
                'lead_id' => $lead->id,
                'sales_id' => $sale->id,
                'activity_type' => 'NOTE',
                'title' => 'Điều phối nhân sự phụ trách',
                'content' => "Bàn giao khách hàng từ [{$oldSalesName}] sang [{$sale->full_name}]. " . ($note ? "Ghi chú: {$note}" : ''),
                'event_at' => now(),
            ]);

            LeadAssignedToSalesEvent::dispatch($lead);

            return $lead->fresh(['assignedSales', 'building']);
        });
    }

    /**
     * Chuyển đổi Lead thành công (WON) sang Khách thuê chính thức (Tenant).
     *
     * @param Lead $lead
     * @param array<string, mixed> $tenantData
     * @return Tenant
     * @throws BusinessException
     */
    public function convertToTenant(Lead $lead, array $tenantData): Tenant
    {
        if ($lead->converted_tenant_id !== null) {
            throw new BusinessException('Hồ sơ Lead này đã được chuyển đổi sang Khách thuê trước đó.', 422);
        }

        return DB::transaction(function () use ($lead, $tenantData) {
            $tenantCode = $tenantData['tenant_code'] ?? 'TN-' . date('Y') . '-' . str_pad((string)(Tenant::withTrashed()->count() + 1), 4, '0', STR_PAD_LEFT);

            $tenant = Tenant::create([
                'tenant_code' => $tenantCode,
                'converted_from_lead_id' => $lead->id,
                'company_name' => $tenantData['company_name'] ?? $lead->company_name ?? $lead->contact_full_name,
                'tax_code' => $tenantData['tax_code'],
                'business_license_private_path' => $tenantData['business_license_private_path'] ?? 'licenses/default.pdf',
                'representative_name' => $tenantData['representative_name'] ?? $lead->contact_full_name,
                'representative_position' => $tenantData['representative_position'] ?? 'Đại diện theo pháp luật',
                'headquarter_address' => $tenantData['headquarter_address'] ?? 'Đang cập nhật',
                'login_email' => $tenantData['login_email'] ?? $lead->email,
                'billing_email' => $tenantData['billing_email'] ?? $lead->email,
                'contact_phone' => $tenantData['contact_phone'] ?? $lead->phone,
                'password' => $tenantData['password'] ?? Str::random(12),
                'status' => 'ACTIVE',
                'created_by_sales_id' => $lead->assigned_sales_id,
            ]);

            $lead->status = LeadStatus::WON;
            $lead->converted_tenant_id = $tenant->id;
            $lead->converted_at = now();
            $lead->save();

            LeadActivity::create([
                'lead_id' => $lead->id,
                'sales_id' => $lead->assigned_sales_id,
                'activity_type' => 'NOTE',
                'title' => 'Chuyển đổi thành công sang Khách thuê chính thức',
                'content' => "Khách hàng chốt thuê thành công và được tạo hồ sơ doanh nghiệp mã [{$tenant->tenant_code}].",
                'event_at' => now(),
            ]);

            return $tenant;
        });
    }
}
