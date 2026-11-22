<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Setting\TestMailSettingRequest;
use App\Http\Requests\V1\Setting\UpdateMailSettingRequest;
use App\Services\MailSettingService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

/**
 * Controller phụ trách các API quản trị cấu hình email gửi thông báo hệ thống.
 * Cung cấp chức năng đọc/ghi cấu hình tùy chỉnh và kiểm tra kết nối SMTP.
 */
class MailSettingApiController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected MailSettingService $mailSettingService
    ) {}

    // Lấy thông tin cấu hình email gửi thông báo hiện tại của hệ thống
    public function show(): JsonResponse
    {
        $settings = $this->mailSettingService->getDisplaySettings();
        return $this->successResponse($settings, 'Lấy cấu hình email thành công.');
    }

    // Cập nhật cấu hình email gửi thông báo hệ thống
    public function update(UpdateMailSettingRequest $request): JsonResponse
    {
        $settings = $this->mailSettingService->saveSettings($request->validated());
        return $this->successResponse($settings, 'Cập nhật cấu hình email thành công và đã áp dụng ngay.');
    }

    // Bật hoặc tắt trạng thái gửi email thông báo hệ thống
    public function toggle(\Illuminate\Http\Request $request): JsonResponse
    {
        $request->validate([
            'is_enabled' => 'required|boolean',
        ]);

        $settings = $this->mailSettingService->toggleStatus((bool) $request->input('is_enabled'));
        $statusText = $settings['is_enabled'] ? 'bật' : 'tắt';

        return $this->successResponse($settings, "Đã {$statusText} tính năng gửi email thông báo hệ thống thành công.");
    }

    // Gửi email thử nghiệm để kiểm tra thông số kết nối máy chủ SMTP
    public function test(TestMailSettingRequest $request): JsonResponse
    {
        $result = $this->mailSettingService->sendTestEmail($request->validated('recipient_email'));
        return $this->successResponse($result, 'Kết nối máy chủ SMTP và gửi email thử nghiệm thành công.');
    }

    // Khôi phục cấu hình email về mặc định theo file môi trường (.env)
    public function reset(): JsonResponse
    {
        $settings = $this->mailSettingService->resetToDefault();
        return $this->successResponse($settings, 'Đã khôi phục cấu hình email về mặc định theo hệ thống.');
    }
}
