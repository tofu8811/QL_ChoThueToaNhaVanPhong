<?php

namespace App\Notifications\Concerns;

use App\Services\MailSettingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Base Notification dùng chung cho toàn bộ các tài khoản nhân sự nội bộ
 * (Admin, Sale, Property Manager, Accountant).
 * Tác vụ gửi email bất đồng bộ qua Laravel Queue.
 */
abstract class BaseStaffCredentialNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Số lần thử lại tối đa khi máy chủ mail bận hoặc lỗi kết nối.
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
     * Khởi tạo thông báo nhân sự.
     *
     * @param string $plainPassword Mật khẩu chuỗi thuần (vừa tạo hoặc vừa reset)
     * @param string $roleName Tên vai trò hiển thị tiếng Việt
     * @param string|null $employeeCode Mã nhân viên (nếu có)
     * @param array<int, string> $assignedBuildings Danh sách tên các tòa nhà phụ trách
     * @param string|null $loginUrl Đường dẫn phân hệ làm việc tương ứng
     * @param bool $isReset Cờ xác định là cấp lại mật khẩu (true) hay cấp mới (false)
     * @param string|null $department Bộ phận chuyên trách (dành cho PM)
     */
    public function __construct(
        public string $plainPassword,
        public string $roleName,
        public ?string $employeeCode = null,
        public array $assignedBuildings = [],
        public ?string $loginUrl = null,
        public bool $isReset = false,
        public ?string $department = null
    ) {}

    /**
     * Xác định kênh gửi thông báo.
     * Chỉ gửi email khi Admin đã cấu hình và tính năng gửi email đang BẬT.
     *
     * @param mixed $notifiable
     * @return array<int, string>
     */
    public function via(mixed $notifiable): array
    {
        return app(MailSettingService::class)->isEnabled() ? ['mail'] : [];
    }

    /**
     * Xây dựng nội dung email thông báo tài khoản cho nhân sự.
     *
     * @param mixed $notifiable
     * @return MailMessage
     */
    public function toMail(mixed $notifiable): MailMessage
    {
        $subject = $this->isReset
            ? "[Office BMS] Cấp lại mật khẩu tài khoản công tác - {$this->roleName}"
            : "[Office BMS] Thông tin tài khoản công tác - {$this->roleName}";

        $leadAction = $this->isReset ? 'cấp lại' : 'khởi tạo';
        $passwordLabel = $this->isReset ? 'Mật khẩu mới được cấp' : 'Mật khẩu khởi tạo';
        $loginUrl = $this->loginUrl ?? url('/login');

        $mail = (new MailMessage)
            ->replyTo(config('mail.reply_to.address', 'noreply@officeleasing.vn'), config('mail.reply_to.name', 'No-Reply (Do Not Reply)'))
            ->subject($subject)
            ->line("**THƯ TỰ ĐỘNG - VUI LÒNG KHÔNG PHẢN HỒI (NO-REPLY)**")
            ->line("Hộp thư này được gửi tự động từ Hệ Thống Quản Lý & Cho Thuê Tòa Nhà Văn Phòng (Office BMS). Mọi thao tác phản hồi (Reply) trực tiếp sẽ không được tiếp nhận.")
            ->line("---")
            ->greeting("Xin chào {$notifiable->full_name},")
            ->line("Ban Quản Trị Hệ Thống xin thông báo: Tài khoản công tác của bạn đã được **{$leadAction}** thành công để phục vụ vận hành trên hệ thống.")
            ->line("---")
            ->line("**Vai trò:** {$this->roleName}");

        if (!empty($this->employeeCode)) {
            $mail->line("**Mã định danh nhân viên:** `{$this->employeeCode}`");
        }

        if (!empty($this->department)) {
            $mail->line("**Bộ phận chuyên trách:** {$this->department}");
        }

        if (!empty($this->assignedBuildings)) {
            $buildingsText = implode(', ', $this->assignedBuildings);
            $mail->line("**Tòa nhà phụ trách:** {$buildingsText}");
        }

        $mail->line("**Email đăng nhập:** `{$notifiable->email}`")
            ->line("**{$passwordLabel}:** `{$this->plainPassword}`")
            ->line("---")
            ->action('Đăng nhập ', $loginUrl)
            ->line("**Khuyến nghị bảo mật:** Đây là tài khoản công tác nội bộ. Để bảo đảm an toàn dữ liệu và quyền riêng tư, bạn có thể chủ động thay đổi mật khẩu bất kỳ lúc nào tại mục Thông tin cá nhân sau khi đăng nhập.")
            ->salutation("Trân trọng,\nBan Quản Trị Hệ Thống Office BMS")
            ->withSymfonyMessage(function (\Symfony\Component\Mime\Email $message) {
                $message->getHeaders()->addTextHeader('Auto-Submitted', 'auto-generated');
                $message->getHeaders()->addTextHeader('X-Auto-Response-Suppress', 'All');
                $message->getHeaders()->addTextHeader('Precedence', 'bulk');
            });

        return $mail;
    }
}
