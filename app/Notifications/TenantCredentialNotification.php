<?php

namespace App\Notifications;

use App\Models\Contract;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Notification gửi email bàn giao tài khoản Cổng Khách Thuê (Tenant Portal)
 * khi Hợp đồng thuê được phê duyệt ký chính thức.
 * Tác vụ chạy bất đồng bộ qua Laravel Queue.
 */
class TenantCredentialNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Số lần thử lại tối đa khi máy chủ mail bận hoặc lỗi kết nối mạng.
     *
     * @var int
     */
    public int $tries = 3;

    /**
     * Thời gian chờ giữa các lần thử lại (giây).
     *
     * @var int
     */
    public int $backoff = 60;

    /**
     * Khởi tạo Notification.
     *
     * @param Contract $contract Hợp đồng thuê đã duyệt
     * @param string $plainPassword Mật khẩu khởi tạo ngẫu nhiên dạng chuỗi thuần
     */
    public function __construct(
        public Contract $contract,
        public string $plainPassword
    ) {}

    /**
     * Kênh gửi thông báo.
     *
     * @param mixed $notifiable
     * @return array<int, string>
     */
    public function via(mixed $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Xây dựng nội dung email bàn giao tài khoản.
     *
     * @param mixed $notifiable
     * @return MailMessage
     */
    public function toMail(mixed $notifiable): MailMessage
    {
        $buildingName = $this->contract->building?->name ?? 'Tòa nhà văn phòng';
        $contractNumber = $this->contract->contract_number;
        $portalUrl = route('tenant-portal.page');

        $mail = (new MailMessage)
            ->replyTo(config('mail.reply_to.address', 'noreply@noreply.invalid'), config('mail.reply_to.name', 'No-Reply (Do Not Reply)'))
            ->subject("[{$buildingName}] Bàn giao tài khoản Cổng Khách Thuê - HĐ {$contractNumber}")
            ->line("⛔ **THƯ TỰ ĐỘNG - VUI LÒNG KHÔNG PHẢN HỒI (NO-REPLY)**")
            ->line("Hộp thư này được gửi tự động từ hệ thống quản lý tòa nhà và không tiếp nhận thư trả lời. Mọi thao tác 'Trả lời' (Reply) trực tiếp sẽ tự động bị từ chối hoặc hủy bỏ.")
            ->line("---")
            ->greeting("Kính gửi Quý Doanh nghiệp {$notifiable->company_name},")
            ->line("Ban Quản Lý Tòa Nhà xin thông báo: Hợp đồng thuê văn phòng số **{$contractNumber}** tại **{$buildingName}** đã được phê duyệt và chính thức kích hoạt.")
            ->line("Dưới đây là thông tin đăng nhập vào **Cổng Thông Tin Khách Thuê (Tenant Portal)** để theo dõi hợp đồng, quản lý diện tích thuê, tra cứu bảng kê hóa đơn cước và gửi yêu cầu hỗ trợ kỹ thuật:")
            ->line("---")
            ->line("🏢 **Mã định danh khách thuê:** `{$notifiable->tenant_code}`")
            ->line("👤 **Email đăng nhập:** `{$notifiable->login_email}`")
            ->line("🔑 **Mật khẩu khởi tạo:** `{$this->plainPassword}`")
            ->line("---")
            ->action('Truy Cập Cổng Khách Thuê Ngay', $portalUrl)
            ->line("⚠️ **Lưu ý bảo mật:** Đây là mật khẩu khởi tạo ban đầu do hệ thống cấp. Để bảo đảm an toàn dữ liệu doanh nghiệp, Quý khách vui lòng đổi sang mật khẩu mới ngay trong lần đầu tiên đăng nhập.")
            ->line("---")
            ->line("📌 *Nếu Quý khách cần hỗ trợ dịch vụ hoặc giải đáp thắc mắc, vui lòng gửi yêu cầu trực tiếp trên Cổng Khách Thuê hoặc liên hệ hotline Ban Quản Lý.*")
            ->salutation("Trân trọng,\nBan Quản Lý Tòa Nhà {$buildingName}")
            ->withSymfonyMessage(function (\Symfony\Component\Mime\Email $message) {
                $message->getHeaders()->addTextHeader('Auto-Submitted', 'auto-generated');
                $message->getHeaders()->addTextHeader('X-Auto-Response-Suppress', 'All');
                $message->getHeaders()->addTextHeader('Precedence', 'bulk');
            });

        // Nếu billing_email khác login_email, gửi thêm CC đến bộ phận kế toán của khách
        if (!empty($notifiable->billing_email) && $notifiable->billing_email !== $notifiable->login_email) {
            $mail->cc($notifiable->billing_email);
        }

        return $mail;
    }
}
