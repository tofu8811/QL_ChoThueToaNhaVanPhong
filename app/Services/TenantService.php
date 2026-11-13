<?php

namespace App\Services;

use App\Enums\ContractStatus;
use App\Exceptions\BusinessException;
use App\Models\Tenant;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class TenantService
{
    /**
     * Lấy danh sách khách thuê doanh nghiệp có phân trang và bộ lọc.
     */
    public function getTenants(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = Tenant::query()->with(['createdBySales'])->withCount('contracts');

        if (!empty($filters['search'])) {
            $keyword = '%' . trim($filters['search']) . '%';
            $query->where(function ($q) use ($keyword) {
                $q->where('company_name', 'like', $keyword)
                  ->orWhere('tax_code', 'like', $keyword)
                  ->orWhere('tenant_code', 'like', $keyword)
                  ->orWhere('representative_name', 'like', $keyword);
            });
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->orderByDesc('created_at')->paginate($perPage);
    }

    /**
     * Lấy thông tin chi tiết một khách thuê doanh nghiệp.
     */
    public function getTenantDetail(string $id): Tenant
    {
        return Tenant::with([
            'createdBySales',
            'contracts' => function ($q) {
                $q->with('building')->orderByDesc('created_at');
            },
        ])->findOrFail($id);
    }

    /**
     * Khởi tạo mới hồ sơ khách thuê doanh nghiệp.
     */
    public function createTenant(array $data, ?string $salesId = null): Tenant
    {
        return DB::transaction(function () use ($data, $salesId) {
            $tenantCode = $data['tenant_code'] ?? $this->generateTenantCode();
            $rawPassword = $data['password'] ?? Str::random(12);

            return Tenant::create([
                'tenant_code' => $tenantCode,
                'company_name' => $data['company_name'],
                'tax_code' => $data['tax_code'],
                'business_license_private_path' => $data['business_license_private_path'] ?? 'licenses/default.pdf',
                'representative_name' => $data['representative_name'],
                'representative_position' => $data['representative_position'] ?? 'Người đại diện theo pháp luật',
                'headquarter_address' => $data['headquarter_address'],
                'login_email' => $data['login_email'],
                'billing_email' => $data['billing_email'] ?? $data['login_email'],
                'contact_phone' => $data['contact_phone'],
                'password' => $rawPassword,
                'status' => 'ACTIVE',
                'created_by_sales_id' => $salesId ?? $data['created_by_sales_id'] ?? null,
                'converted_from_lead_id' => $data['converted_from_lead_id'] ?? null,
            ]);
        });
    }

    /**
     * Cập nhật thông tin hồ sơ pháp lý khách thuê.
     */
    public function updateTenant(Tenant $tenant, array $data): Tenant
    {
        $tenant->update($data);
        return $tenant->fresh(['createdBySales']);
    }

    /**
     * Vô hiệu hóa khách thuê (Chặn nếu còn hợp đồng active hoặc còn công nợ).
     */
    public function deactivateTenant(Tenant $tenant): void
    {
        // 1. Kiểm tra hợp đồng còn hiệu lực hoặc đang giữ chỗ
        $hasActiveContracts = $tenant->contracts()
            ->whereIn('status', [ContractStatus::ACTIVE, ContractStatus::EXPIRING_SOON, ContractStatus::RESERVED])
            ->exists();

        if ($hasActiveContracts) {
            throw new BusinessException('Không thể vô hiệu hóa khách thuê vì đang có hợp đồng hoặc phòng giữ chỗ còn hiệu lực.', 422);
        }

        // 2. Kiểm tra công nợ chưa quyết toán trong debit_notes (nếu có)
        $hasUnpaidDebitNotes = $tenant->debitNotes()
            ->whereIn('status', ['ISSUED', 'OVERDUE'])
            ->exists();

        if ($hasUnpaidDebitNotes) {
            throw new BusinessException('Không thể vô hiệu hóa khách thuê vì còn thông báo cước chưa quyết toán thanh toán.', 422);
        }

        $tenant->update(['status' => 'LOCKED']);
    }

    /**
     * Tải lên tài liệu scan Giấy chứng nhận ĐKKD vào Private Bucket.
     */
    public function uploadBusinessLicense(Tenant $tenant, UploadedFile $file): string
    {
        $extension = $file->getClientOriginalExtension();
        $fileName = 'license_' . $tenant->id . '_' . time() . '.' . $extension;
        $path = $file->storeAs('licenses', $fileName, 'local');

        $tenant->update([
            'business_license_private_path' => $path,
        ]);

        return $path;
    }

    /**
     * Sinh đường dẫn ký số tạm thời (Signed URL) để tải hoặc xem Giấy phép ĐKKD.
     */
    public function getLicenseDownloadUrl(Tenant $tenant, int $expirationMinutes = 10): string
    {
        $path = $tenant->business_license_private_path;

        if (empty($path)) {
            throw new BusinessException('Doanh nghiệp này chưa cập nhật Giấy chứng nhận ĐKKD.', 404);
        }

        try {
            return Storage::disk('local')->temporaryUrl(
                $path,
                now()->addMinutes($expirationMinutes)
            );
        } catch (\Throwable $e) {
            // Fallback an toàn nếu disk không hỗ trợ driver temporaryUrl trực tiếp
            return URL::temporarySignedRoute(
                'v1.admin.tenants.downloadLicense',
                now()->addMinutes($expirationMinutes),
                ['id' => $tenant->id]
            );
        }
    }

    /**
     * Tự động sinh mã định danh khách thuê theo chuẩn TNT-YYYY-XXXX.
     */
    public function generateTenantCode(): string
    {
        $year = date('Y');
        $count = Tenant::withTrashed()->whereYear('created_at', $year)->count() + 1;
        return sprintf('TNT-%s-%04d', $year, $count);
    }
}
