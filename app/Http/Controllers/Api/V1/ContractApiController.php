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
        $contracts = $this->contractService->getContracts($request->all(), $request->user(), $perPage);

        return $this->paginateResponse($contracts, 'Lấy danh sách hợp đồng thuê thành công.');
    }

    /**
     * Khởi tạo Hợp đồng thuê bản nháp (DRAFT).
     */
    public function store(StoreContractRequest $request): JsonResponse
    {
        $salesId = $request->user()?->id;
        $contract = $this->contractService->createDraftContract($request->validated(), $salesId);

        return $this->createdResponse($contract, 'Khởi tạo hợp đồng thuê bản nháp thành công.');
    }

    /**
     * Lập hợp đồng Đặt cọc giữ chỗ tạm thời (Booking / RESERVED).
     */
    public function reserve(ReserveContractRequest $request): JsonResponse
    {
        $salesId = $request->user()?->id;
        $contract = $this->contractService->createBookingContract($request->validated(), $salesId);

        return $this->createdResponse($contract, 'Lập hợp đồng giữ chỗ thành công.');
    }

    /**
     * Xem thông tin chi tiết một hợp đồng cụ thể.
     */
    public function show(string $id, Request $request): JsonResponse
    {
        $contract = $this->contractService->getContractDetail($id, $request->user());

        return $this->successResponse($contract, 'Lấy chi tiết hợp đồng thành công.');
    }

    /**
     * Cập nhật điều khoản hợp đồng khi còn ở dạng DRAFT hoặc RESERVED.
     */
    public function update(UpdateContractRequest $request, string $id): JsonResponse
    {
        $contract = Contract::findOrFail($id);
        $updatedContract = $this->contractService->updateDraftContract($contract, $request->validated());

        return $this->successResponse($updatedContract, 'Cập nhật điều khoản hợp đồng thành công.');
    }

    /**
     * Xóa hợp đồng bản nháp hoặc giữ chỗ.
     */
    public function destroy(string $id): JsonResponse
    {
        $contract = Contract::findOrFail($id);
        $this->contractService->deleteDraftContract($contract);

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
        $signedContract = $this->contractService->signContract($contract, $request->user());

        return $this->successResponse($signedContract, 'Ký duyệt và kích hoạt hợp đồng thành công.');
    }

    /**
     * Gia hạn hợp đồng (Renew) tạo bản ghi mới liên kết parent_contract_id.
     */
    public function renew(RenewContractRequest $request, string $id): JsonResponse
    {
        $contract = Contract::findOrFail($id);
        $renewedContract = $this->contractService->renewContract($contract, $request->validated(), $request->user());

        return $this->createdResponse($renewedContract, 'Lập phụ lục gia hạn hợp đồng thành công.');
    }

    /**
     * Thanh lý hợp đồng & Phân định tiền cọc (Terminate).
     */
    public function terminate(TerminateContractRequest $request, string $id): JsonResponse
    {
        $contract = Contract::findOrFail($id);
        $terminatedContract = $this->contractService->terminateContract($contract, $request->validated(), $request->user());

        return $this->successResponse($terminatedContract, 'Thanh lý hợp đồng và nghiệm thu trả phòng thành công.');
    }

    /**
     * Tải lên các file scan hợp đồng, phụ lục, biên bản bàn giao.
     */
    public function uploadFiles(UploadContractFilesRequest $request, string $id): JsonResponse
    {
        $contract = Contract::findOrFail($id);
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
