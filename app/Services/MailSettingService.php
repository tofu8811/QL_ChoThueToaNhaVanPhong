<?php

namespace App\Services;

use App\Exceptions\BusinessException;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Service tầng nghiệp vụ quản lý cấu hình email gửi thông báo hệ thống.
 * Cơ chế:
 * - Nếu Admin CHƯA cấu hình: Hệ thống TẮT gửi email thông báo cho khách.
 * - Nếu Admin ĐÃ cấu hình: Hệ thống BẬT gửi email thông báo tự động theo tài khoản đã lưu.
 * - Lưu trữ cấu hình tại storage/app/mail_settings.json (0 đụng CSDL, KISS & YAGNI).
 */
class MailSettingService
{
    /**
     * Đường dẫn file lưu trữ cấu hình tùy chỉnh của Admin.
     *
     * @return string
     */
    protected function getFilePath(): string
    {
        return storage_path('app/mail_settings.json');
    }

    /**
     * Kiểm tra xem Admin đã cấu hình tài khoản email gửi thông báo hay chưa.
     *
     * @return bool
     */
    public function isConfigured(): bool
    {
        $settings = $this->getRawSettings();
        if (!$settings) {
            return false;
        }

        return !empty($settings['mail_host']) 
            && !empty($settings['mail_username']) 
            && !empty($settings['mail_password']) 
            && !empty($settings['mail_from_address']);
    }

    /**
     * Kiểm tra xem tính năng gửi email thông báo có đang được BẬT hay không.
     * Điều kiện: Đã cấu hình đầy đủ VÀ cờ is_enabled = true.
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        if (!$this->isConfigured()) {
            return false;
        }

        $settings = $this->getRawSettings();
        return (bool) ($settings['is_enabled'] ?? true);
    }

    /**
     * Lấy cấu hình thô đầy đủ từ file JSON nội bộ (nếu có).
     *
     * @return array<string, mixed>|null
     */
    public function getRawSettings(): ?array
    {
        $filePath = $this->getFilePath();

        if (File::exists($filePath)) {
            try {
                $content = File::get($filePath);
                $decoded = json_decode($content, true);
                if (is_array($decoded) && !empty($decoded)) {
                    return $decoded;
                }
            } catch (\Throwable $e) {
                Log::warning('Không thể đọc file cấu hình mail: ' . $e->getMessage());
            }
        }

        return null;
    }

    /**
     * Lấy cấu hình email phục vụ hiển thị trên giao diện người dùng.
     * Tuyệt đối không lấy fallback từ .env. Nếu chưa cấu hình thì trả về trạng thái rỗng.
     *
     * @return array<string, mixed>
     */
    public function getDisplaySettings(): array
    {
        $settings = $this->getRawSettings();
        $isConfigured = $this->isConfigured();

        if (!$settings || !$isConfigured) {
            return [
                'is_configured' => false,
                'is_enabled' => false,
                'mail_mailer' => 'smtp',
                'mail_host' => '',
                'mail_port' => 587,
                'mail_encryption' => 'tls',
                'mail_username' => '',
                'has_password' => false,
                'mail_password_masked' => '',
                'mail_from_address' => '',
                'mail_from_name' => '',
                'mail_reply_to_address' => '',
                'mail_reply_to_name' => '',
                'updated_at' => null,
            ];
        }

        $hasPassword = !empty($settings['mail_password']);
        $isEnabled = (bool) ($settings['is_enabled'] ?? true);

        return [
            'is_configured' => true,
            'is_enabled' => $isEnabled,
            'mail_mailer' => $settings['mail_mailer'] ?? 'smtp',
            'mail_host' => $settings['mail_host'] ?? '',
            'mail_port' => (int) ($settings['mail_port'] ?? 587),
            'mail_encryption' => $settings['mail_encryption'] ?? 'tls',
            'mail_username' => $settings['mail_username'] ?? '',
            'has_password' => $hasPassword,
            'mail_password_masked' => $hasPassword ? '••••••••' : '',
            'mail_from_address' => $settings['mail_from_address'] ?? '',
            'mail_from_name' => $settings['mail_from_name'] ?? '',
            'mail_reply_to_address' => $settings['mail_reply_to_address'] ?? 'noreply@officeleasing.vn',
            'mail_reply_to_name' => $settings['mail_reply_to_name'] ?? 'No-Reply (Do Not Reply)',
            'updated_at' => $settings['updated_at'] ?? null,
        ];
    }

    /**
     * Cập nhật cấu hình email gửi thông báo vào file JSON nội bộ và kích hoạt gửi mail.
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function saveSettings(array $data): array
    {
        $current = $this->getRawSettings();

        // Xử lý mật khẩu: Nếu không nhập mới hoặc giữ nguyên masked, dùng lại mật khẩu cũ
        $newPassword = $data['mail_password'] ?? null;
        if (empty($newPassword) || $newPassword === '••••••••') {
            $passwordToSave = $current['mail_password'] ?? '';
        } else {
            $passwordToSave = trim($newPassword);
        }

        if (empty($passwordToSave)) {
            throw new BusinessException('Vui lòng nhập Mật khẩu ứng dụng (App Password) cho tài khoản email.', 422);
        }

        $newSettings = [
            'is_enabled' => isset($data['is_enabled']) ? (bool) $data['is_enabled'] : ($current['is_enabled'] ?? true),
            'mail_mailer' => trim($data['mail_mailer'] ?? 'smtp') ?: 'smtp',
            'mail_host' => trim($data['mail_host'] ?? '') ?: 'smtp.gmail.com',
            'mail_port' => !empty($data['mail_port']) ? (int) $data['mail_port'] : 587,
            'mail_encryption' => trim($data['mail_encryption'] ?? 'tls') ?: 'tls',
            'mail_username' => trim($data['mail_username'] ?? ''),
            'mail_password' => $passwordToSave,
            'mail_from_address' => trim($data['mail_from_address'] ?? ''),
            'mail_from_name' => trim($data['mail_from_name'] ?? ''),
            // Khóa cứng địa chỉ No-Reply để khách không thể reply về hộp thư cá nhân
            'mail_reply_to_address' => 'noreply@officeleasing.vn',
            'mail_reply_to_name' => 'No-Reply (Do Not Reply)',
            'updated_at' => now()->format('Y-m-d H:i:s'),
        ];

        $filePath = $this->getFilePath();
        $dir = dirname($filePath);
        if (!File::isDirectory($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

        File::put($filePath, json_encode($newSettings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        // Nạp cấu hình mới ngay lập tức vào runtime config
        $this->applyConfig();

        return $this->getDisplaySettings();
    }

    /**
     * Bật hoặc tắt trạng thái gửi email thông báo hệ thống mà không cần xóa thông tin tài khoản.
     *
     * @param bool $enabled
     * @return array<string, mixed>
     * @throws BusinessException
     */
    public function toggleStatus(bool $enabled): array
    {
        if ($enabled && !$this->isConfigured()) {
            throw new BusinessException('Vui lòng điền và lưu đầy đủ thông tin tài khoản Gmail trước khi bật tính năng gửi email.', 422);
        }

        $current = $this->getRawSettings();
        if (!$current) {
            throw new BusinessException('Hệ thống chưa được thiết lập tài khoản email. Vui lòng cấu hình trước.', 422);
        }

        $current['is_enabled'] = $enabled;
        $current['updated_at'] = now()->format('Y-m-d H:i:s');

        $filePath = $this->getFilePath();
        $dir = dirname($filePath);
        if (!File::isDirectory($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

        File::put($filePath, json_encode($current, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        // Nạp cấu hình runtime mới ngay lập tức
        $this->applyConfig();

        return $this->getDisplaySettings();
    }

    /**
     * Xóa cấu hình email (Tắt tính năng gửi email thông báo hệ thống).
     *
     * @return array<string, mixed>
     */
    public function resetToDefault(): array
    {
        $filePath = $this->getFilePath();
        if (File::exists($filePath)) {
            File::delete($filePath);
        }

        // Tắt gửi mail trong runtime config
        $this->applyConfig();

        return $this->getDisplaySettings();
    }

    /**
     * Nạp cấu hình email vào runtime config của Laravel:
     * - Nếu chưa cấu hình hoặc tính năng đang TẮT: Chuyển mailer sang 'log' (không gửi thư qua mạng).
     * - Nếu đã cấu hình và đang BẬT: Ghi đè thông số SMTP đã lưu.
     *
     * @return void
     */
    public function applyConfig(): void
    {
        // Trong môi trường testing, không ghi đè runtime config để tuân thủ phpunit.xml (dùng array/null mailer)
        if (app()->environment('testing')) {
            return;
        }

        if (!$this->isEnabled()) {
            // Khi chưa cấu hình hoặc đã TẮT: Đặt mailer mặc định là log để tránh gửi thư rác hay lỗi kết nối
            config([
                'mail.default' => 'log',
            ]);
            app()->forgetInstance('mail.manager');
            app()->forgetInstance('mailer');
            return;
        }

        $settings = $this->getRawSettings();

        // Ghi đè vào runtime config
        config([
            'mail.default' => $settings['mail_mailer'] ?: 'smtp',
            'mail.mailers.smtp.host' => $settings['mail_host'],
            'mail.mailers.smtp.port' => $settings['mail_port'] ?: 587,
            'mail.mailers.smtp.encryption' => ($settings['mail_encryption'] !== 'none') ? $settings['mail_encryption'] : null,
            'mail.mailers.smtp.username' => $settings['mail_username'],
            'mail.mailers.smtp.password' => $settings['mail_password'],
            'mail.from.address' => $settings['mail_from_address'],
            'mail.from.name' => $settings['mail_from_name'],
            'mail.reply_to.address' => 'noreply@officeleasing.vn',
            'mail.reply_to.name' => 'No-Reply (Do Not Reply)',
        ]);

        // Xóa instance mailer đã khởi tạo trước đó để Laravel Mail tạo transport mới
        app()->forgetInstance('mail.manager');
        app()->forgetInstance('mailer');
    }

    /**
     * Gửi email thử nghiệm để kiểm tra thông số kết nối máy chủ SMTP.
     *
     * @param string $recipientEmail
     * @return array<string, mixed>
     * @throws BusinessException
     */
    public function sendTestEmail(string $recipientEmail): array
    {
        if (!$this->isConfigured()) {
            throw new BusinessException('Hệ thống chưa được thiết lập tài khoản email gửi thông báo. Vui lòng điền thông tin và bấm "Lưu Cấu Hình Email" trước khi gửi thử nghiệm.', 422);
        }

        if (!$this->isEnabled()) {
            throw new BusinessException('Tính năng gửi email hiện đang bị TẮT. Vui lòng BẬT công tắc gửi email trước khi gửi thư thử nghiệm.', 422);
        }

        $recipientEmail = trim($recipientEmail);
        if (!filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) {
            throw new BusinessException('Địa chỉ email nhận thử nghiệm không đúng định dạng.', 422);
        }

        // Đảm bảo cấu hình runtime mới nhất đã được nạp
        $this->applyConfig();

        $senderName = config('mail.from.name', 'Ban Quản Lý Tòa Nhà');
        $senderEmail = config('mail.from.address', 'noreply@officeleasing.vn');
        $smtpHost = config('mail.mailers.smtp.host', 'smtp.gmail.com');
        $smtpPort = config('mail.mailers.smtp.port', 587);
        $sentTime = now()->format('d/m/Y H:i:s');

        $htmlContent = <<<HTML
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thư thử nghiệm cấu hình Email</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #334155; margin: 0; padding: 20px; background-color: #f8fafc;">
    <div style="max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 8px; border: 1px solid #e2e8f0; padding: 24px;">
        <h2 style="color: #0f172a; margin-top: 0; border-bottom: 2px solid #2563eb; padding-bottom: 10px;">
            🎉 Thử Nghiệm Kết Nối Email Thành Công!
        </h2>
        <p>Xin chào Ban Quản Trị,</p>
        <p>Đây là email kiểm tra được gửi tự động từ <strong>Hệ Thống Quản Lý Và Cho Thuê Tòa Nhà Văn Phòng (Office BMS)</strong>.</p>
        <p>Nếu Quý vị nhận được thư này, điều đó chứng minh cấu hình tài khoản gửi thông báo qua giao diện Admin đã hoạt động chính xác 100%.</p>
        
        <div style="background: #f1f5f9; padding: 15px; border-radius: 6px; margin: 20px 0; font-size: 13.5px;">
            <strong>Thông số kiểm tra:</strong>
            <ul style="margin: 8px 0 0 0; padding-left: 20px;">
                <li><strong>Thời gian gửi:</strong> {$sentTime}</li>
                <li><strong>Máy chủ SMTP:</strong> {$smtpHost}:{$smtpPort}</li>
                <li><strong>Tên hiển thị người gửi:</strong> {$senderName}</li>
                <li><strong>Địa chỉ gửi:</strong> {$senderEmail}</li>
                <li><strong>Địa chỉ nhận test:</strong> {$recipientEmail}</li>
                <li><strong>Địa chỉ phản hồi (Reply-To):</strong> noreply@officeleasing.vn (Khóa cứng No-Reply)</li>
            </ul>
        </div>

        <div style="margin: 16px 0; padding: 12px 16px; background: #fef2f2; border-left: 4px solid #ef4444; border-radius: 6px; font-size: 13px; color: #991b1b; line-height: 1.5;">
            ⛔ <strong>Chế độ No-Reply tự động:</strong> Email này đã được khóa cứng địa chỉ phản hồi là <code>noreply@officeleasing.vn</code>. Khi người nhận bấm nút "Trả lời" (Reply), ứng dụng thư sẽ tự động chuyển hướng về hộp thư No-Reply mà không bao giờ gửi về tài khoản Gmail cá nhân của Quý vị.
        </div>

        <p style="color: #64748b; font-size: 13px;">
            <em>Lưu ý: Mọi email bàn giao tài khoản Cổng Khách Thuê và khôi phục mật khẩu trong hệ thống từ thời điểm này sẽ được gửi từ tài khoản cấu hình này.</em>
        </p>

        <p style="margin-top: 24px; border-top: 1px solid #f1f5f9; padding-top: 14px; font-size: 13px; color: #94a3b8;">
            Trân trọng,<br>
            <strong>{$senderName}</strong>
        </p>
    </div>
</body>
</html>
HTML;

        try {
            Mail::html($htmlContent, function ($message) use ($recipientEmail, $senderName, $senderEmail) {
                $message->to($recipientEmail)
                    ->from($senderEmail, $senderName)
                    ->replyTo('noreply@officeleasing.vn', 'No-Reply (Do Not Reply)')
                    ->subject('[Office BMS] Thư thử nghiệm cấu hình email hệ thống thành công');

                $message->getHeaders()->addTextHeader('Auto-Submitted', 'auto-generated');
                $message->getHeaders()->addTextHeader('X-Auto-Response-Suppress', 'All');
                $message->getHeaders()->addTextHeader('Precedence', 'bulk');
            });
        } catch (\Throwable $e) {
            Log::error('Lỗi kiểm tra gửi mail thử nghiệm SMTP: ' . $e->getMessage(), [
                'exception' => $e,
                'recipient' => $recipientEmail,
                'smtp_host' => $smtpHost,
            ]);

            throw new BusinessException(
                'Không thể kết nối máy chủ SMTP hoặc gửi mail thất bại: ' . $e->getMessage(),
                500
            );
        }

        return [
            'success' => true,
            'message' => "Email thử nghiệm đã được gửi thành công đến [{$recipientEmail}]. Vui lòng kiểm tra hộp thư đến (hoặc thư rác/spam).",
            'sent_at' => $sentTime,
            'recipient' => $recipientEmail,
        ];
    }
}
