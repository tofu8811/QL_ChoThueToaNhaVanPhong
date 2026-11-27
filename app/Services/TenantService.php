<?php

namespace App\Services;

use App\Enums\ContractStatus;
use App\Exceptions\BusinessException;
use App\Helpers\PasswordHelper;
use App\Models\Tenant;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class TenantService
{
    /**
     * Lấy danh sách khách thuê doanh nghiệp có phân trang và bộ lọc.
     */
    public function getTenants(array $filters, int $perPage = 15, $user = null): LengthAwarePaginator
    {
        $query = Tenant::query()->with(['createdBySales'])->withCount('contracts');

        // Phân quyền dữ liệu (RBAC Data Isolation)
        if ($user instanceof \App\Models\Sale) {
            $query->where(function ($q) use ($user) {
                $q->where('created_by_sales_id', $user->id)
                  ->orWhereHas('convertedFromLead', function ($lq) use ($user) {
                      $lq->where('assigned_sales_id', $user->id);
                  });
            });
        } elseif ($user instanceof \App\Models\PropertyManager) {
            $assignedBldIds = $user->buildings()->pluck('buildings.id')->toArray();
            $query->where(function ($q) use ($assignedBldIds) {
                $q->whereHas('contracts', function ($cq) use ($assignedBldIds) {
                    $cq->whereIn('building_id', $assignedBldIds);
                })->orWhereHas('convertedFromLead', function ($lq) use ($assignedBldIds) {
                    $lq->whereIn('building_id', $assignedBldIds);
                })->orWhereDoesntHave('contracts');
            });
        }

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
            $rawPassword = !empty($data['password'])
                ? trim((string) $data['password'])
                : PasswordHelper::DEFAULT_PASSWORD;

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
        $objectPath = "licenses/{$fileName}";
        $hasSupabase = !empty(config('filesystems.disks.supabase_private.key'));

        try {
            if ($hasSupabase) {
                $path = $file->storeAs('licenses', $fileName, [
                    'disk' => 'supabase_private',
                    'visibility' => 'private',
                ]);
            } else {
                $path = $file->storeAs('licenses', $fileName, 'local');
            }
        } catch (\Throwable $e) {
            Log::warning("Supabase license upload failed, fallback to local storage: " . $e->getMessage());
            $path = $file->storeAs('licenses', $fileName, 'local');
        }

        $tenant->update([
            'business_license_private_path' => $path,
        ]);

        return $path;
    }

    /**
     * Sinh đường dẫn ký số tạm thời (Signed URL) để tải hoặc xem Giấy phép ĐKKD.
     */
    public function getLicenseDownloadUrl(Tenant $tenant, ?int $expirationMinutes = null): string
    {
        $path = $tenant->business_license_private_path;

        if (empty($path)) {
            throw new BusinessException('Doanh nghiệp này chưa cập nhật Giấy chứng nhận ĐKKD.', 404);
        }

        $ttl = $expirationMinutes ?? (int) env('SUPABASE_SIGNED_URL_EXPIRATION', 10);

        if (!empty(config('filesystems.disks.supabase_private.key'))) {
            try {
                return Storage::disk('supabase_private')->temporaryUrl(
                    $path,
                    now()->addMinutes($ttl)
                );
            } catch (\Throwable $e) {
                Log::warning("Supabase license temporaryUrl failed, fallback to local/signed route: " . $e->getMessage());
            }
        }

        try {
            return Storage::disk('local')->temporaryUrl(
                $path,
                now()->addMinutes($ttl)
            );
        } catch (\Throwable $e) {
            // Fallback an toàn nếu disk không hỗ trợ driver temporaryUrl trực tiếp
            return URL::temporarySignedRoute(
                'v1.admin.tenants.downloadLicense',
                now()->addMinutes($ttl),
                ['id' => $tenant->id]
            );
        }
    }

    /**
     * Tự động sinh mã định danh khách thuê theo chuẩn TNT-YYYY-XXXX (hoặc TN-YYYY-XXXX).
     * Quét toàn bộ CSDL kể cả các bản ghi đã xóa mềm (withTrashed) để triệt tiêu hoàn toàn nguy cơ trùng lặp.
     */
    public function generateTenantCode(string $prefix = 'TNT'): string
    {
        $year = date('Y');
        $existingCodes = Tenant::withTrashed()
            ->where(function ($q) use ($year) {
                $q->where('tenant_code', 'LIKE', "TNT-{$year}-%")
                  ->orWhere('tenant_code', 'LIKE', "TN-{$year}-%");
            })
            ->pluck('tenant_code');

        $maxSeq = 0;
        foreach ($existingCodes as $code) {
            if (preg_match('/(?:TNT|TN)-\d{4}-(\d+)$/', (string) $code, $matches)) {
                $seq = (int) $matches[1];
                if ($seq > $maxSeq) {
                    $maxSeq = $seq;
                }
            }
        }

        do {
            $maxSeq++;
            $candidate = sprintf('%s-%s-%04d', $prefix, $year, $maxSeq);
        } while (Tenant::withTrashed()->where('tenant_code', $candidate)->exists());

        return $candidate;
    }

    /**
     * Quản trị viên / Sales cấp lại mật khẩu tạm thời cho Khách thuê (Admin-Assisted Reset).
     *
     * @param Tenant $tenant
     * @param mixed $actor
     * @return string Mật khẩu ngẫu nhiên mới sinh ra (dạng chuỗi thuần)
     */
    public function adminResetPassword(Tenant $tenant, mixed $actor): string
    {
        return DB::transaction(function () use ($tenant, $actor) {
            $newPassword = \App\Helpers\PasswordHelper::generateSecurePassword(10);

            $tenant->update([
                'password' => $newPassword,
                'status' => 'ACTIVE',
                'must_change_password' => true,
                'password_changed_at' => now(),
            ]);

            // Thu hồi toàn bộ Sanctum tokens phiên cũ để chặn truy cập trái phép
            $tenant->tokens()->delete();

            // Gửi email thông báo cập nhật mật khẩu mới qua Queue
            $latestContract = $tenant->contracts()
                ->whereIn('status', [ContractStatus::ACTIVE, ContractStatus::EXPIRING_SOON, ContractStatus::DRAFT])
                ->latest()
                ->first();

            if ($latestContract) {
                $tenant->notify(
                    (new \App\Notifications\Tenant\TenantCredentialNotification($latestContract, $newPassword))->afterCommit()
                );
            }

            return $newPassword;
        });
    }
}
