<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\V1\Contract\AttachContractUnitRequest;
use App\Http\Requests\V1\Contract\RenewContractRequest;
use App\Http\Requests\V1\Contract\ReserveContractRequest;
use App\Http\Requests\V1\Contract\StoreContractRequest;
use App\Http\Requests\V1\Contract\TerminateContractRequest;
use App\Http\Requests\V1\Contract\UpdateContractRequest;
use App\Http\Requests\V1\Contract\UploadContractFilesRequest;
use App\Models\Contract;
use App\Services\ContractService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Controller Quản trị Hợp đồng Thuê Văn phòng (Contracts & Units).
 */
class ContractApiController extends BaseApiController
{
    public function __construct(
        protected ContractService $contractService
    ) {}

    /**
     * Danh sách hợp đồng thuê có phân trang và bộ lọc.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->input('per_page', 15);
        $user = auth('sanctum')->user() ?? $request->user() ?? auth()->user();
        $contracts = $this->contractService->getContracts($request->all(), $user, $perPage);

        return $this->paginateResponse($contracts, 'Lấy danh sách hợp đồng thuê thành công.');
    }

    /**
     * Tự động sinh mã hợp đồng kế tiếp từ CSDL theo tòa nhà.
     */
    public function nextContractCode(Request $request): JsonResponse
    {
        $buildingId = $request->input('building_id');
        $prefix = $request->input('prefix', 'HD');
        $nextCode = $this->contractService->getNextContractCode($buildingId, $prefix);

        return $this->successResponse(['next_code' => $nextCode], 'Lấy mã hợp đồng tự động thành công.');
    }

    /**
     * Khởi tạo Hợp đồng thuê bản nháp (DRAFT).
     */
    public function store(StoreContractRequest $request): JsonResponse
    {
        $user = auth('sanctum')->user() ?? $request->user() ?? auth()->user();
        $salesId = $user instanceof \App\Models\Sale ? $user->id : null;
        if (!$salesId && !empty($request->input('tenant_id'))) {
            $tenant = \App\Models\Tenant::find($request->input('tenant_id'));
            $salesId = $tenant?->created_by_sales_id;
        }
        $contract = $this->contractService->createDraftContract($request->validated(), $salesId);

        return $this->createdResponse($contract, 'Khởi tạo hợp đồng thuê bản nháp thành công.');
    }

    /**
     * Lập hợp đồng Đặt cọc giữ chỗ tạm thời (Booking / RESERVED).
     */
    public function reserve(ReserveContractRequest $request): JsonResponse
    {
        $user = auth('sanctum')->user() ?? $request->user() ?? auth()->user();
        $salesId = $user instanceof \App\Models\Sale ? $user->id : null;
        if (!$salesId && !empty($request->input('tenant_id'))) {
            $tenant = \App\Models\Tenant::find($request->input('tenant_id'));
            $salesId = $tenant?->created_by_sales_id;
        }
        $contract = $this->contractService->createBookingContract($request->validated(), $salesId);

        return $this->createdResponse($contract, 'Lập hợp đồng giữ chỗ thành công.');
    }

    /**
     * Xem thông tin chi tiết một hợp đồng cụ thể.
     */
    public function show(string $id, Request $request): JsonResponse
    {
        $user = auth('sanctum')->user() ?? $request->user() ?? auth()->user();
        $contract = $this->contractService->getContractDetail($id, $user);

        return $this->successResponse($contract, 'Lấy chi tiết hợp đồng thành công.');
    }

    /**
     * Cập nhật điều khoản hợp đồng khi còn ở dạng DRAFT hoặc RESERVED.
     */
    public function update(UpdateContractRequest $request, string $id): JsonResponse
    {
        $contract = Contract::findOrFail($id);
        $user = auth('sanctum')->user() ?? $request->user() ?? auth()->user();

        // Kiểm tra quyền sở hữu với tài khoản Sales
        if ($user instanceof \App\Models\Sale) {
            if ($contract->created_by_sales_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn không có quyền chỉnh sửa hợp đồng do nhân sự khác tạo.',
                ], 403);
            }
        }

        if (!in_array($contract->status, [\App\Enums\ContractStatus::DRAFT, \App\Enums\ContractStatus::RESERVED])) {
            return response()->json([
                'success' => false,
                'message' => 'Tuyệt đối cấm sửa hợp đồng đã được phê duyệt hoặc có hiệu lực pháp lý.',
            ], 403);
        }

        $updatedContract = $this->contractService->updateDraftContract($contract, $request->validated());

        return $this->successResponse($updatedContract, 'Cập nhật điều khoản hợp đồng thành công.');
    }

    /**
     * Xóa hợp đồng bản nháp hoặc giữ chỗ.
     */
    public function destroy(string $id, Request $request): JsonResponse
    {
        $contract = Contract::findOrFail($id);
        $user = auth('sanctum')->user() ?? $request->user() ?? auth()->user();

        // Kiểm tra quyền sở hữu với tài khoản Sales
        if ($user instanceof \App\Models\Sale) {
            if ($contract->created_by_sales_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn không có quyền xóa hợp đồng do nhân sự khác tạo.',
                ], 403);
            }
        }

        if (!in_array($contract->status, [\App\Enums\ContractStatus::DRAFT, \App\Enums\ContractStatus::RESERVED])) {
            return response()->json([
                'success' => false,
                'message' => 'Tuyệt đối cấm xóa hợp đồng đã được ký duyệt hoặc có hiệu lực pháp lý.',
            ], 403);
        }

        $this->contractService->deleteDraftContract($contract, $user);

        return $this->successResponse(null, 'Xóa hợp đồng nháp thành công.');
    }

    /**
     * Gắn mặt bằng vào Hợp đồng kèm Pessimistic Lock & Kiểm tra trùng lịch.
     */
    public function attachUnit(AttachContractUnitRequest $request, string $id): JsonResponse
    {
        $contract = Contract::findOrFail($id);
        $contractUnit = $this->contractService->attachUnitToContract(
            $contract,
            $request->validated('unit_id'),
            (float) $request->validated('leased_area_sqm'),
            (float) $request->validated('agreed_rental_price_per_sqm'),
            (float) ($request->validated('agreed_service_fee_per_sqm') ?? 0.0)
        );

        return $this->createdResponse([
            'contract_unit' => $contractUnit,
            'contract' => $contract->fresh(['contractUnits.unit']),
        ], 'Gán mặt bằng vào hợp đồng thành công.');
    }

    /**
     * Gỡ mặt bằng khỏi hợp đồng nháp.
     */
    public function detachUnit(string $id, string $unitId): JsonResponse
    {
        $contract = Contract::findOrFail($id);
        $this->contractService->detachUnitFromContract($contract, $unitId);

        return $this->successResponse([
            'contract' => $contract->fresh(['contractUnits.unit']),
        ], 'Gỡ mặt bằng khỏi hợp đồng thành công.');
    }

    /**
     * Ký duyệt & Kích hoạt hợp đồng chính thức (Sign).
     * Phân quyền: Admin hoặc Property Manager phụ trách tòa nhà.
     */
    public function sign(string $id, Request $request): JsonResponse
    {
        $contract = Contract::findOrFail($id);
        $user = auth('sanctum')->user() ?? $request->user() ?? auth()->user();
        $signedContract = $this->contractService->signContract($contract, $user);

        return $this->successResponse($signedContract, 'Ký duyệt và kích hoạt hợp đồng thành công.');
    }

    /**
     * Gia hạn hợp đồng (Renew) tạo bản ghi mới liên kết parent_contract_id.
     */
    public function renew(RenewContractRequest $request, string $id): JsonResponse
    {
        $contract = Contract::findOrFail($id);
        $user = auth('sanctum')->user() ?? $request->user() ?? auth()->user();
        $renewedContract = $this->contractService->renewContract($contract, $request->validated(), $user);

        return $this->createdResponse($renewedContract, 'Lập phụ lục gia hạn hợp đồng thành công.');
    }

    /**
     * Thanh lý hợp đồng & Phân định tiền cọc (Terminate).
     */
    public function terminate(TerminateContractRequest $request, string $id): JsonResponse
    {
        $contract = Contract::findOrFail($id);
        $user = auth('sanctum')->user() ?? $request->user() ?? auth()->user();
        $terminatedContract = $this->contractService->terminateContract($contract, $request->validated(), $user);

        return $this->successResponse($terminatedContract, 'Thanh lý hợp đồng và nghiệm thu trả phòng thành công.');
    }

    /**
     * Tải lên các file scan hợp đồng, phụ lục, biên bản bàn giao hoặc chứng từ cọc.
     */
    public function uploadFiles(UploadContractFilesRequest $request, string $id): JsonResponse
    {
        $contract = Contract::findOrFail($id);
        $user = $request->user();

        // Kiểm tra quyền đối với tài khoản Sales: chỉ tải lên file cho hợp đồng do chính mình tạo
        if ($user instanceof \App\Models\Sale) {
            if ($contract->created_by_sales_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn không có quyền tải lên tài liệu cho hợp đồng do nhân sự khác phụ trách.',
                ], 403);
            }
        }

        $docType = $request->input('document_type', 'SIGNED_CONTRACT_SCAN');
        $uploaded = $this->contractService->uploadContractFiles($contract, $request->file('files'), $docType);

        return $this->createdResponse($uploaded, 'Tải lên tài liệu hợp đồng thành công.');
    }

    /**
     * Lấy Signed URL tải file scan hợp đồng.
     */
    public function downloadFile(string $id, int $fileId): JsonResponse
    {
        $contract = Contract::findOrFail($id);
        $url = $this->contractService->generateFileDownloadUrl($contract, $fileId);

        return $this->successResponse([
            'download_url' => $url,
            'expires_in_minutes' => 10,
        ], 'Tạo liên kết tải file hợp đồng thành công.');
    }
}
