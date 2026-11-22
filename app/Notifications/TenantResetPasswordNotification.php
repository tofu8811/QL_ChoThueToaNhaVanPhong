<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Notification gửi email khôi phục mật khẩu cho Khách thuê doanh nghiệp (Tenant).
 * Tác vụ chạy bất đồng bộ qua Laravel Queue.
 */
class TenantResetPasswordNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;
    public int $backoff = 60;

    /**
     * Khởi tạo Notification với token bảo mật.
     *
     * @param string $token Token khôi phục mật khẩu
     */
    public function __construct(
        public string $token
    ) {}

    /**
     * Kênh gửi thông báo.
     *
     * @param mixed $notifiable
     * @return array<int, string>
     */
    public function via(mixed $notifiable): array
    {
        // Chỉ gửi email khi Admin đã cấu hình và tính năng gửi email đang BẬT
        return app(\App\Services\MailSettingService::class)->isEnabled() ? ['mail'] : [];
    }

    /**
     * Xây dựng nội dung email khôi phục mật khẩu.
     *
     * @param mixed $notifiable
     * @return MailMessage
     */
    public function toMail(mixed $notifiable): MailMessage
    {
        $email = $notifiable->getEmailForPasswordReset();
        $resetUrl = route('tenant-portal.page', [
            'action' => 'reset-password',
            'token' => $this->token,
            'email' => $email,
        ]);

        return (new MailMessage)
            ->replyTo(config('mail.reply_to.address', 'noreply@noreply.invalid'), config('mail.reply_to.name', 'No-Reply (Do Not Reply)'))
            ->subject('[Hệ thống Quản lý Tòa nhà] Yêu cầu đặt lại mật khẩu Cổng Khách Thuê')
            ->line("⛔ **THƯ TỰ ĐỘNG - VUI LÒNG KHÔNG PHẢN HỒI (NO-REPLY)**")
            ->line("Hộp thư này được gửi tự động và không có nhân viên trực tiếp nhận phản hồi. Vui lòng không nhấn nút 'Trả lời' (Reply).")
            ->line("---")
            ->greeting("Kính gửi Quý Khách thuê {$notifiable->company_name},")
            ->line('Hệ thống vừa nhận được yêu cầu đặt lại mật khẩu truy cập Cổng Khách Thuê cho tài khoản email: **' . $email . '**.')
            ->line('Vui lòng bấm vào nút bên dưới để thiết lập mật khẩu mới:')
            ->action('Đặt Lại Mật Khẩu Ngay', $resetUrl)
            ->line('Liên kết này có hiệu lực trong vòng **30 phút**. Sau thời gian này, liên kết sẽ tự động hết hạn.')
            ->line('Nếu Quý khách không gửi yêu cầu này, vui lòng bỏ qua email và mật khẩu hiện tại vẫn được giữ nguyên an toàn.')
            ->line("---")
            ->line('📌 *Nếu cần hỗ trợ kỹ thuật, Quý khách vui lòng liên hệ trực tiếp số hotline của Ban Quản Lý Tòa Nhà.*')
            ->salutation("Trân trọng,\nBan Quản Trị Hệ Thống Quản Lý Tòa Nhà")
            ->withSymfonyMessage(function (\Symfony\Component\Mime\Email $message) {
                $message->getHeaders()->addTextHeader('Auto-Submitted', 'auto-generated');
                $message->getHeaders()->addTextHeader('X-Auto-Response-Suppress', 'All');
                $message->getHeaders()->addTextHeader('Precedence', 'bulk');
            });
    }
}
