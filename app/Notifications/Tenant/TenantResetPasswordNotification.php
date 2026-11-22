<?php

namespace App\Notifications\Tenant;

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
            ->line("Hộp thư này được gửi tự động từ hệ thống quản lý tòa nhà và không tiếp nhận thư trả lời.")
            ->line("---")
            ->greeting("Kính gửi Quý Doanh nghiệp,")
            ->line("Ban Quản Lý Tòa Nhà nhận được yêu cầu khôi phục mật khẩu truy cập **Cổng Thông Tin Khách Thuê (Tenant Portal)** cho địa chỉ email: **{$email}**.")
            ->line("Nếu Quý khách thực hiện yêu cầu này, vui lòng bấm vào nút bên dưới để tiến hành thiết lập mật khẩu mới (liên kết có hiệu lực trong 60 phút):")
            ->action('Đặt Lại Mật Khẩu Ngay', $resetUrl)
            ->line("⚠️ **Lưu ý bảo mật:** Nếu Quý khách KHÔNG gửi yêu cầu này, vui lòng bỏ qua email hoặc thông báo khẩn cấp cho Ban Quản Lý Tòa Nhà để kiểm tra bảo mật tài khoản.")
            ->salutation("Trân trọng,\nBan Quản Lý Tòa Nhà");
    }
}
