<?php

namespace App\Services;

use App\Enums\ContractStatus;
use App\Enums\UnitStatus;
use App\Exceptions\BusinessException;
use App\Exceptions\LeaseOverlapException;
use App\Models\Admin;
use App\Models\Building;
use App\Models\Contract;
use App\Models\ContractImage;
use App\Models\ContractUnit;
use App\Models\PropertyManager;
use App\Models\Sale;
use App\Models\Unit;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class ContractService
{
    /**
     * Lấy danh sách hợp đồng có phân trang và bộ lọc.
     */
    public function getContracts(array $filters, mixed $user = null, int $perPage = 15): LengthAwarePaginator
    {
        $query = Contract::query()->with([
            'building:id,building_code,name',
            'tenant:id,tenant_code,company_name,tax_code',
            'units:id,unit_code,name,area_sqm',
        ]);

        if (!empty($filters['building_id'])) {
            $query->where('building_id', $filters['building_id']);
        }

        if (!empty($filters['tenant_id'])) {
            $query->where('tenant_id', $filters['tenant_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['search'])) {
            $keyword = '%' . trim($filters['search']) . '%';
            $query->where(function ($q) use ($keyword) {
                $q->where('contract_number', 'like', $keyword)
                  ->orWhereHas('tenant', fn ($t) => $t->where('company_name', 'like', $keyword)->orWhere('tax_code', 'like', $keyword));
            });
        }

        // Phân quyền dữ liệu theo phạm vi tòa nhà nếu là PropertyManager
        if ($user instanceof PropertyManager) {
            $assignedBuildingIds = $user->buildings()->pluck('buildings.id')->toArray();
            $query->whereIn('building_id', $assignedBuildingIds);
        }

        return $query->orderByDesc('created_at')->paginate($perPage);
    }

    /**
     * Lấy chi tiết hợp đồng thuê văn phòng.
     */
    public function getContractDetail(string $id, mixed $user = null): Contract
    {
        $contract = Contract::with([
            'building',
            'tenant',
            'contractUnits.unit',
            'images',
            'parentContract',
            'renewedContracts',
            'approvedByAdmin',
            'approvedByPm',
            'terminatedByAdmin',
            'terminatedByPm',
            'createdBySales',
        ])->findOrFail($id);

        if ($user instanceof PropertyManager) {
            $this->ensurePmAssignedToBuilding($user, $contract->building_id);
        }

        return $contract;
    }

    /**
     * Khởi tạo Hợp đồng thuê bản nháp (DRAFT).
     */
    public function createDraftContract(array $data, ?string $salesId = null): Contract
    {
        return DB::transaction(function () use ($data, $salesId) {
            $building = Building::findOrFail($data['building_id']);
            $contractNumber = $data['contract_number'] ?? $this->generateContractNumber($building->building_code);

            $contract = Contract::create([
                'contract_number' => $contractNumber,
                'building_id' => $data['building_id'],
                'tenant_id' => $data['tenant_id'],
                'created_by_sales_id' => $salesId ?? $data['created_by_sales_id'] ?? null,
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'total_leased_area_sqm' => 0.00,
                'deposit_amount' => $data['deposit_amount'],
                'booking_deposit_amount' => 0.00,
                'payment_cycle_months' => $data['payment_cycle_months'] ?? 1,
                'payment_due_day' => $data['payment_due_day'] ?? 5,
                'late_fee_interest_percent' => $data['late_fee_interest_percent'] ?? 0.05,
                'annual_price_escalation_percent' => $data['annual_price_escalation_percent'] ?? 5.00,
                'status' => ContractStatus::DRAFT,
            ]);

            // Gán danh sách mặt bằng ban đầu (nếu có)
            if (!empty($data['units']) && is_array($data['units'])) {
                foreach ($data['units'] as $unitData) {
                    $this->attachUnitToContract(
                        $contract,
                        $unitData['unit_id'],
                        (float) $unitData['leased_area_sqm'],
                        (float) $unitData['agreed_rental_price_per_sqm'],
                        (float) ($unitData['agreed_service_fee_per_sqm'] ?? 0.0)
                    );
                }
            }

            return $contract->fresh(['contractUnits.unit']);
        });
    }

    /**
     * Khởi tạo Hợp đồng Đặt cọc giữ chỗ tạm thời (Booking / RESERVED).
     */
    public function createBookingContract(array $data, ?string $salesId = null): Contract
    {
        return DB::transaction(function () use ($data, $salesId) {
            $building = Building::findOrFail($data['building_id']);
            $contractNumber = $data['contract_number'] ?? $this->generateContractNumber($building->building_code, 'BOOKING');

            $contract = Contract::create([
                'contract_number' => $contractNumber,
                'building_id' => $data['building_id'],
                'tenant_id' => $data['tenant_id'],
                'created_by_sales_id' => $salesId ?? $data['created_by_sales_id'] ?? null,
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'total_leased_area_sqm' => 0.00,
                'deposit_amount' => $data['deposit_amount'] ?? $data['booking_deposit_amount'],
                'booking_deposit_amount' => $data['booking_deposit_amount'],
                'reservation_expires_at' => $data['reservation_expires_at'],
                'payment_cycle_months' => $data['payment_cycle_months'] ?? 1,
                'payment_due_day' => $data['payment_due_day'] ?? 5,
                'status' => ContractStatus::RESERVED,
            ]);

            // Gán mặt bằng và chuyển trạng thái phòng sang RESERVED
            foreach ($data['units'] as $unitData) {
                $this->attachUnitToContract(
                    $contract,
                    $unitData['unit_id'],
                    (float) $unitData['leased_area_sqm'],
                    (float) $unitData['agreed_rental_price_per_sqm'],
                    (float) ($unitData['agreed_service_fee_per_sqm'] ?? 0.0)
                );
            }

            return $contract->fresh(['contractUnits.unit']);
        });
    }

    /**
     * Cập nhật điều khoản hợp đồng khi còn ở dạng DRAFT hoặc RESERVED.
     */
    public function updateDraftContract(Contract $contract, array $data): Contract
    {
        if (!in_array($contract->status, [ContractStatus::DRAFT, ContractStatus::RESERVED])) {
            throw new BusinessException('Chỉ có thể chỉnh sửa điều khoản khi hợp đồng ở trạng thái Dự thảo (DRAFT) hoặc Giữ chỗ (RESERVED).', 422);
        }

        return DB::transaction(function () use ($contract, $data) {
            $startDate = $data['start_date'] ?? $contract->start_date->toDateString();
            $endDate = $data['end_date'] ?? $contract->end_date->toDateString();

            // Nếu thay đổi ngày, kiểm tra lại xung đột lịch của toàn bộ các phòng đã gán
            if ($startDate !== $contract->start_date->toDateString() || $endDate !== $contract->end_date->toDateString()) {
                $assignedUnitIds = $contract->contractUnits()->pluck('unit_id')->toArray();
                foreach ($assignedUnitIds as $unitId) {
                    $this->validateNoOverlap($unitId, $startDate, $endDate, $contract->id);
                }
            }

            $contract->update($data);
            return $contract->fresh(['contractUnits.unit']);
        });
    }

    /**
     * Hủy bỏ / Xóa hợp đồng bản nháp hoặc giữ chỗ.
     */
    public function deleteDraftContract(Contract $contract): void
    {
        if (!in_array($contract->status, [ContractStatus::DRAFT, ContractStatus::RESERVED])) {
            throw new BusinessException('Không thể xóa hợp đồng đã có hiệu lực pháp lý hoặc đã thanh lý.', 422);
        }

        DB::transaction(function () use ($contract) {
            $unitIds = $contract->contractUnits()->pluck('unit_id')->toArray();

            // Nếu là hợp đồng giữ chỗ, giải phóng các mặt bằng về AVAILABLE
            if ($contract->status === ContractStatus::RESERVED) {
                Unit::whereIn('id', $unitIds)
                    ->where('status', UnitStatus::RESERVED)
                    ->update(['status' => UnitStatus::AVAILABLE]);
            }

            $contract->contractUnits()->delete();
            $contract->delete();
        });
    }

    /**
     * Gắn mặt bằng vào Hợp đồng với KHÓA BI QUAN (Pessimistic Lock) & CHỐNG TRÙNG LỊCH THUÊ.
     */
    public function attachUnitToContract(
        Contract $contract,
        string $unitId,
        float $leasedArea,
        float $rentalPrice,
        float $serviceFee = 0.0
    ): ContractUnit {
        return DB::transaction(function () use ($contract, $unitId, $leasedArea, $rentalPrice, $serviceFee) {
            // 1. Khóa bi quan hàng bản ghi Unit để serialize các request chốt phòng đồng thời
            $unit = Unit::where('id', $unitId)->lockForUpdate()->firstOrFail();

            if ($unit->building_id !== $contract->building_id) {
                throw new BusinessException('Mặt bằng không thuộc tòa nhà của hợp đồng này.', 422);
            }

            if ($unit->status === UnitStatus::MAINTENANCE) {
                throw new BusinessException(sprintf('Mặt bằng %s đang trong quá trình bảo trì kỹ thuật, tạm thời không thể đưa vào hợp đồng.', $unit->unit_code), 422);
            }

            // 2. Thuật toán kiểm tra giao thoa thời gian (StartA <= EndB && EndA >= StartB)
            $this->validateNoOverlap(
                $unit->id,
                $contract->start_date->toDateString(),
                $contract->end_date->toDateString(),
                $contract->id
            );

            // 3. Tạo hoặc cập nhật bản ghi contract_units
            $contractUnit = ContractUnit::updateOrCreate(
                [
                    'contract_id' => $contract->id,
                    'unit_id' => $unit->id,
                ],
                [
                    'building_id' => $contract->building_id,
                    'leased_area_sqm' => $leasedArea,
                    'agreed_rental_price_per_sqm' => $rentalPrice,
                    'agreed_service_fee_per_sqm' => $serviceFee,
                ]
            );

            // 4. Nếu hợp đồng là RESERVED, đổi trạng thái phòng sang RESERVED
            if ($contract->status === ContractStatus::RESERVED) {
                $unit->update(['status' => UnitStatus::RESERVED]);
            }

            // 5. Cập nhật lại tổng diện tích sàn thuê của hợp đồng
            $totalArea = $contract->contractUnits()->sum('leased_area_sqm');
            $contract->update(['total_leased_area_sqm' => $totalArea]);

            return $contractUnit;
        });
    }

    /**
     * Gỡ mặt bằng khỏi hợp đồng nháp.
     */
    public function detachUnitFromContract(Contract $contract, string $unitId): void
    {
        if (!in_array($contract->status, [ContractStatus::DRAFT, ContractStatus::RESERVED])) {
            throw new BusinessException('Chỉ có thể gỡ mặt bằng khi hợp đồng ở trạng thái Dự thảo hoặc Giữ chỗ.', 422);
        }

        DB::transaction(function () use ($contract, $unitId) {
            $unit = Unit::where('id', $unitId)->lockForUpdate()->firstOrFail();

            $contract->contractUnits()->where('unit_id', $unit->id)->delete();

            if ($contract->status === ContractStatus::RESERVED && $unit->status === UnitStatus::RESERVED) {
                $unit->update(['status' => UnitStatus::AVAILABLE]);
            }

            $totalArea = $contract->contractUnits()->sum('leased_area_sqm');
            $contract->update(['total_leased_area_sqm' => $totalArea]);
        });
    }

    /**
     * Ký duyệt & Kích hoạt hợp đồng chính thức (Sign).
     * Phân quyền: Admin hoặc Property Manager phụ trách tòa nhà.
     */
    public function signContract(Contract $contract, mixed $user): Contract
    {
        if (!in_array($contract->status, [ContractStatus::DRAFT, ContractStatus::RESERVED])) {
            throw new BusinessException('Hợp đồng đã được duyệt ký hoặc đang ở trạng thái không thể kích hoạt.', 422);
        }

        if ($contract->contractUnits()->count() === 0) {
            throw new BusinessException('Hợp đồng phải có ít nhất một mặt bằng văn phòng được gán trước khi ký duyệt.', 422);
        }

        if ($user instanceof PropertyManager) {
            $this->ensurePmAssignedToBuilding($user, $contract->building_id);
        }

        return DB::transaction(function () use ($contract, $user) {
            $updateData = [
                'status' => ContractStatus::ACTIVE,
                'signed_date' => now()->toDateString(),
            ];

            if ($user instanceof Admin) {
                $updateData['approved_by_admin_id'] = $user->id;
                $updateData['approved_by_pm_id'] = null;
            } elseif ($user instanceof PropertyManager) {
                $updateData['approved_by_pm_id'] = $user->id;
                $updateData['approved_by_admin_id'] = null;
            }

            $contract->update($updateData);

            // Đổi toàn bộ mặt bằng trong hợp đồng sang LEASED (Đang cho thuê)
            $unitIds = $contract->contractUnits()->pluck('unit_id')->toArray();
            Unit::whereIn('id', $unitIds)->update([
                'status' => UnitStatus::LEASED,
                'last_status_changed_at' => now(),
            ]);

            return $contract->fresh(['contractUnits.unit', 'building', 'tenant']);
        });
    }

    /**
     * Lập phụ lục gia hạn hợp đồng (Contract Renewal) bằng cách TẠO BẢN GHI MỚI liên kết parent_contract_id.
     */
    public function renewContract(Contract $oldContract, array $data, mixed $user): Contract
    {
        if (!in_array($oldContract->status, [ContractStatus::ACTIVE, ContractStatus::EXPIRING_SOON])) {
            throw new BusinessException('Chỉ có thể gia hạn hợp đồng đang có hiệu lực hoặc sắp hết hạn.', 422);
        }

        if ($user instanceof PropertyManager) {
            $this->ensurePmAssignedToBuilding($user, $oldContract->building_id);
        }

        $newEndDate = $data['new_end_date'];
        if ($newEndDate <= $oldContract->end_date->toDateString()) {
            throw new BusinessException('Ngày kết thúc mới cho chu kỳ gia hạn phải lớn hơn ngày kết thúc hiện tại.', 422);
        }

        $cycleStartDate = Carbon::parse($oldContract->end_date)->addDay()->toDateString();

        return DB::transaction(function () use ($oldContract, $data, $user, $cycleStartDate, $newEndDate) {
            // 1. Kiểm tra giao thoa thời gian chu kỳ mới cho toàn bộ các mặt bằng
            $units = $oldContract->contractUnits;
            foreach ($units as $cu) {
                $this->validateNoOverlap($cu->unit_id, $cycleStartDate, $newEndDate, $oldContract->id);
            }

            // 2. Chuyển hợp đồng cũ sang RENEWED
            $oldContract->update(['status' => ContractStatus::RENEWED]);

            // 3. Khởi tạo hợp đồng chu kỳ 2 (mới) kế thừa thông tin
            $escalation = (float) ($data['annual_price_escalation_percent'] ?? $oldContract->annual_price_escalation_percent);
            $newContractNumber = $data['contract_number'] ?? ($oldContract->contract_number . '-PL01');

            $newContractData = [
                'contract_number' => $newContractNumber,
                'parent_contract_id' => $oldContract->id,
                'building_id' => $oldContract->building_id,
                'tenant_id' => $oldContract->tenant_id,
                'created_by_sales_id' => $oldContract->created_by_sales_id,
                'signed_date' => now()->toDateString(),
                'start_date' => $cycleStartDate,
                'end_date' => $newEndDate,
                'total_leased_area_sqm' => $oldContract->total_leased_area_sqm,
                'deposit_amount' => $data['deposit_amount'] ?? $oldContract->deposit_amount,
                'payment_cycle_months' => $oldContract->payment_cycle_months,
                'payment_due_day' => $oldContract->payment_due_day,
                'late_fee_interest_percent' => $oldContract->late_fee_interest_percent,
                'annual_price_escalation_percent' => $escalation,
                'status' => ContractStatus::ACTIVE,
            ];

            if ($user instanceof Admin) {
                $newContractData['approved_by_admin_id'] = $user->id;
            } elseif ($user instanceof PropertyManager) {
                $newContractData['approved_by_pm_id'] = $user->id;
            }

            $newContract = Contract::create($newContractData);

            // 4. Sao chép các mặt bằng sang hợp đồng mới kèm điều chỉnh giá theo biên độ leo thang
            foreach ($units as $cu) {
                $newPrice = round($cu->agreed_rental_price_per_sqm * (1 + $escalation / 100), 2);
                ContractUnit::create([
                    'contract_id' => $newContract->id,
                    'unit_id' => $cu->unit_id,
                    'building_id' => $newContract->building_id,
                    'leased_area_sqm' => $cu->leased_area_sqm,
                    'agreed_rental_price_per_sqm' => $newPrice,
                    'agreed_service_fee_per_sqm' => $cu->agreed_service_fee_per_sqm,
                ]);
            }

            // 5. Đổi trạng thái các mặt bằng về LEASED
            Unit::whereIn('id', $units->pluck('unit_id'))->update(['status' => UnitStatus::LEASED]);

            return $newContract->fresh(['contractUnits.unit', 'parentContract']);
        });
    }

    /**
     * Thanh lý hợp đồng & Phân định tiền cọc (Terminate).
     * Phân quyền: Admin hoặc Property Manager phụ trách tòa nhà.
     */
    public function terminateContract(Contract $contract, array $data, mixed $user): Contract
    {
        if (!in_array($contract->status, [ContractStatus::ACTIVE, ContractStatus::EXPIRING_SOON])) {
            throw new BusinessException('Chỉ có thể thanh lý hợp đồng đang có hiệu lực hoặc sắp hết hạn.', 422);
        }

        if ($user instanceof PropertyManager) {
            $this->ensurePmAssignedToBuilding($user, $contract->building_id);
        }

        $refund = (float) $data['refund_deposit_amount'];
        $penalty = (float) $data['penalty_deposit_amount'];
        $deposit = (float) $contract->deposit_amount;

        if (round($refund + $penalty, 2) > round($deposit, 2)) {
            throw new BusinessException(sprintf('Tổng tiền cọc hoàn lại (%s) và tiền phạt vi phạm (%s) không được vượt quá số tiền cọc ban đầu (%s).', number_format($refund), number_format($penalty), number_format($deposit)), 422);
        }

        return DB::transaction(function () use ($contract, $data, $user, $refund, $penalty) {
            $updateData = [
                'status' => ContractStatus::TERMINATED,
                'terminated_at' => $data['terminated_at'] ?? now()->toDateString(),
                'termination_note' => $data['termination_note'],
                'refund_deposit_amount' => $refund,
                'penalty_deposit_amount' => $penalty,
            ];

            if ($user instanceof Admin) {
                $updateData['terminated_by_admin_id'] = $user->id;
                $updateData['terminated_by_pm_id'] = null;
            } elseif ($user instanceof PropertyManager) {
                $updateData['terminated_by_pm_id'] = $user->id;
                $updateData['terminated_by_admin_id'] = null;
            }

            $contract->update($updateData);

            // Tự động giải phóng toàn bộ các mặt bằng trong hợp đồng về AVAILABLE
            $unitIds = $contract->contractUnits()->pluck('unit_id')->toArray();
            Unit::whereIn('id', $unitIds)->update([
                'status' => UnitStatus::AVAILABLE,
                'last_status_changed_at' => now(),
            ]);

            return $contract->fresh(['contractUnits.unit', 'building', 'tenant']);
        });
    }

    /**
     * Tải lên các tệp scan hợp đồng, biên bản bàn giao, phụ lục vào Private Bucket.
     */
    public function uploadContractFiles(Contract $contract, array $files, string $documentType = 'SIGNED_CONTRACT_SCAN'): array
    {
        $uploadedRecords = [];

        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                $fileName = $file->getClientOriginalName();
                $extension = $file->getClientOriginalExtension();
                $storedName = 'contract_' . $contract->id . '_' . time() . '_' . Str::random(6) . '.' . $extension;
                $path = $file->storeAs('contracts', $storedName, 'local');

                $imageRecord = ContractImage::create([
                    'contract_id' => $contract->id,
                    'image_url' => $path,
                    'file_name' => $fileName,
                    'document_type' => $documentType,
                ]);

                // Nếu là bản scan hợp đồng chính và hợp đồng chưa có path chính, cập nhật luôn
                if ($documentType === 'SIGNED_CONTRACT_SCAN' && empty($contract->contract_pdf_private_path)) {
                    $contract->update(['contract_pdf_private_path' => $path]);
                }

                $uploadedRecords[] = $imageRecord;
            }
        }

        return $uploadedRecords;
    }

    /**
     * Sinh Signed URL tạm thời tải file scan hợp đồng.
     */
    public function generateFileDownloadUrl(Contract $contract, int $fileId, int $expirationMinutes = 10): string
    {
        $file = $contract->images()->findOrFail($fileId);

        try {
            return Storage::disk('local')->temporaryUrl(
                $file->image_url,
                now()->addMinutes($expirationMinutes)
            );
        } catch (\Throwable $e) {
            return URL::temporarySignedRoute(
                'v1.admin.contracts.downloadFile',
                now()->addMinutes($expirationMinutes),
                ['id' => $contract->id, 'fileId' => $file->id]
            );
        }
    }

    /**
     * THUẬT TOÁN TOÁN HỌC CHỐNG TRÙNG LỊCH THUÊ MẶT BẰNG:
     * Hai khoảng thời gian [StartA, EndA] và [StartB, EndB] giao nhau khi:
     * (StartA <= EndB) AND (EndA >= StartB)
     */
    public function validateNoOverlap(string $unitId, string $startDate, string $endDate, ?string $ignoreContractId = null): void
    {
        $overlappingContract = Contract::query()
            ->join('contract_units', 'contracts.id', '=', 'contract_units.contract_id')
            ->where('contract_units.unit_id', $unitId)
            ->whereNull('contracts.deleted_at')
            ->whereIn('contracts.status', [
                ContractStatus::ACTIVE,
                ContractStatus::EXPIRING_SOON,
                ContractStatus::RESERVED,
                ContractStatus::DRAFT,
            ])
            ->when($ignoreContractId, fn ($q) => $q->where('contracts.id', '!=', $ignoreContractId))
            ->where(function ($q) use ($startDate, $endDate) {
                $q->where('contracts.start_date', '<=', $endDate)
                  ->where('contracts.end_date', '>=', $startDate);
            })
            ->select('contracts.*')
            ->first();

        if ($overlappingContract) {
            $unit = Unit::find($unitId);
            $unitCode = $unit?->unit_code ?? $unitId;

            throw new LeaseOverlapException(
                sprintf(
                    'Mặt bằng %s đã có hợp đồng thuê khác (%s) trong khoảng thời gian từ %s đến %s. Không thể xếp trùng lịch.',
                    $unitCode,
                    $overlappingContract->contract_number,
                    $overlappingContract->start_date->format('d/m/Y'),
                    $overlappingContract->end_date->format('d/m/Y')
                ),
                [
                    'unit_id' => $unitId,
                    'unit_code' => $unitCode,
                    'conflicting_contract_id' => $overlappingContract->id,
                    'conflicting_contract_number' => $overlappingContract->contract_number,
                    'conflicting_range' => [
                        'start' => $overlappingContract->start_date->toDateString(),
                        'end' => $overlappingContract->end_date->toDateString(),
                    ],
                ]
            );
        }
    }

    /**
     * Đảm bảo Property Manager được phân công quản lý tòa nhà này.
     */
    protected function ensurePmAssignedToBuilding(PropertyManager $pm, string $buildingId): void
    {
        $isAssigned = $pm->buildings()->where('buildings.id', $buildingId)->exists();
        if (!$isAssigned) {
            throw new BusinessException('Bạn không được phân công quản lý tòa nhà này. Hành động bị từ chối.', 403);
        }
    }

    /**
     * Tự động sinh số hiệu hợp đồng pháp lý: HD-{YYYY}/{MM}-{BUILDING_CODE}-{SEQ}.
     */
    public function generateContractNumber(string $buildingCode, string $prefix = 'HD'): string
    {
        $year = date('Y');
        $month = date('m');
        $count = Contract::withTrashed()->whereYear('created_at', $year)->count() + 1;
        return sprintf('%s-%s/%s-%s-%04d', $prefix, $year, $month, strtoupper($buildingCode), $count);
    }
}
