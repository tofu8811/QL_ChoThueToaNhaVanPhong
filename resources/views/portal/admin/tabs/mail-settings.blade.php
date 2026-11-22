<!-- TAB: CẤU HÌNH TÀI KHOẢN EMAIL GỬI THÔNG BÁO -->
<div id="tab-admin-mail-settings" class="filament-tab-pane" style="display:none;">
  <style>
    .mail-toggle-switch {
      position: relative;
      display: inline-block;
      width: 44px;
      height: 24px;
      margin: 0;
      cursor: pointer;
      vertical-align: middle;
    }
    .mail-toggle-switch input {
      opacity: 0;
      width: 0;
      height: 0;
      position: absolute;
    }
    .mail-toggle-slider {
      position: absolute;
      cursor: pointer;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background-color: #cbd5e1;
      transition: 0.25s ease-in-out;
      border-radius: 24px;
    }
    .mail-toggle-slider:before {
      position: absolute;
      content: "";
      height: 18px;
      width: 18px;
      left: 3px;
      bottom: 3px;
      background-color: #ffffff;
      transition: 0.25s ease-in-out;
      border-radius: 50%;
      box-shadow: 0 1px 3px rgba(0,0,0,0.25);
    }
    .mail-toggle-switch input:checked + .mail-toggle-slider {
      background-color: #16a34a;
    }
    .mail-toggle-switch input:checked + .mail-toggle-slider:before {
      transform: translateX(20px);
    }
  </style>

  <!-- 1. Header Trang Chuẩn Đồng Bộ Với Các Tab BMS (Buildings, Contracts, Billing...) -->
  <div class="filament-page-header" style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 22px;">
    <div>
      <h1 class="filament-page-title" style="font-size: 23px; font-weight: 800; color: #0f172a; margin-bottom: 4px; display: flex; align-items: center; gap: 10px;">
        <i class="fa-solid fa-envelope-open-text" style="color: #2563eb;"></i> Thiết Lập Email
      </h1>
    </div>
    <div style="display: flex; gap: 10px; align-items: center;">
      <!-- Công tắc BẬT / TẮT gửi email độc lập -->
      <div id="box-mail-toggle-wrapper" style="display: flex; align-items: center; gap: 9px; background: #ffffff; border: 1px solid #e2e8f0; padding: 6px 14px; border-radius: 8px; box-shadow: 0 1px 2px rgba(0,0,0,0.03);">
        <span style="font-size: 13px; font-weight: 600; color: #334155;">Gửi email:</span>
        <label class="mail-toggle-switch">
          <input type="checkbox" id="toggle-mail-status-input" onchange="toggleMailStatus(this.checked)">
          <span class="mail-toggle-slider"></span>
        </label>
        <span id="badge-mail-toggle-status" class="badge-pill" style="font-size: 11.5px; font-weight: 700; padding: 3px 9px; border-radius: 6px; background: #fef3c7; color: #b45309;">
          Đang tắt
        </span>
      </div>

      <button type="button" class="btn-mo-outline" style="background: #ffffff; padding: 8px 16px; font-size: 13px; border-radius: 8px; display: inline-flex; align-items: center; gap: 7px;" onclick="loadMailSettings()">
        <i class="fa-solid fa-arrows-rotate"></i> Làm mới
      </button>
      <button type="button" id="btn-clear-mail-settings" class="btn-mo-outline text-danger" style="background: #ffffff; padding: 8px 16px; font-size: 13px; border-radius: 8px; border-color: #fca5a5; color: #dc2626; display: none; align-items: center; gap: 7px;" onclick="clearMailSettings()">
        <i class="fa-solid fa-trash-can"></i> Xóa cấu hình
      </button>
    </div>
  </div>

  <!-- 3. Khu Vực Nội Dung Chính: 2 Cột Bố Trí Cân Đối & Padding Đầy Đủ -->
  <div style="display: grid; grid-template-columns: 1fr 380px; gap: 24px; align-items: start;">
    <!-- CỘT TRÁI: FORM CẤU HÌNH SMTP & SENDER (Padding 24px) -->
    <div class="filament-card" style="padding: 24px; background: #ffffff;">

      <form id="form-mail-settings" onsubmit="event.preventDefault(); saveMailSettings();">
        <!-- Ẩn các thông số kỹ thuật xuống Backend, mặc định kết nối Gmail SMTP -->
        <input type="hidden" id="mail_mailer" value="smtp">
        <input type="hidden" id="mail_host" value="smtp.gmail.com">
        <input type="hidden" id="mail_port" value="587">
        <input type="hidden" id="mail_encryption" value="tls">

        <h4 style="font-size: 15.5px; font-weight: 700; color: #0f172a; margin-top: 0; margin-bottom: 18px; padding-bottom: 10px; border-bottom: 1px solid #f1f5f9; display: flex; align-items: center; gap: 8px;">
          <i class="fa-brands fa-google" style="color: #ea4335;"></i> 1. Tài Khoản Gmail Gửi Thông Báo (SMTP)
        </h4>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 18px; margin-bottom: 26px;">
          <div>
            <label style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px;">
              Tài khoản Gmail gửi <span style="color: #dc2626;">*</span>
            </label>
            <input type="email" id="mail_username" class="form-control" placeholder="Ví dụ: bql.office@gmail.com" style="width: 100%; height: 40px; font-size: 13px; border-radius: 7px;" oninput="handleGmailUsernameInput(this.value)" required>
          </div>

          <div>
            <label style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px;">
              Mật khẩu ứng dụng (App Password) <span style="color: #dc2626;">*</span>
            </label>
            <div style="position: relative;">
              <input type="password" id="mail_password" class="form-control" placeholder="Nhập mật khẩu ứng dụng 16 ký tự" style="width: 100%; height: 40px; font-size: 13px; border-radius: 7px; padding-right: 38px;">
              <button type="button" onclick="toggleMailPasswordVisibility()" style="position: absolute; right: 10px; top: 10px; background: transparent; border: none; color: #94a3b8; cursor: pointer; padding: 0;" title="Hiện / Ẩn mật khẩu">
                <i class="fa-solid fa-eye" id="icon-toggle-mail-pwd"></i>
              </button>
            </div>
            <small style="color: #94a3b8; font-size: 11.5px; margin-top: 5px; display: block;" id="help-mail-password">
              Khi đã lưu: để trống nếu muốn giữ nguyên mật khẩu cũ.
            </small>
          </div>
        </div>

        <h4 style="font-size: 15.5px; font-weight: 700; color: #0f172a; margin-top: 0; margin-bottom: 18px; padding-bottom: 10px; border-bottom: 1px solid #f1f5f9; display: flex; align-items: center; gap: 8px;">
          <i class="fa-solid fa-id-badge" style="color: #059669;"></i> 2. Thông Tin Người Gửi Hiển Thị & Phản Hồi (No-Reply)
        </h4>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 18px; margin-bottom: 20px;">
          <div>
            <label style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px;">
              Tên người gửi hiển thị (From Name) <span style="color: #dc2626;">*</span>
            </label>
            <input type="text" id="mail_from_name" class="form-control" placeholder="Ví dụ: Ban Quản Lý Tòa Nhà (No-Reply)" style="width: 100%; height: 40px; font-size: 13px; border-radius: 7px;" required>
          </div>

          <div>
            <label style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px;">
              Địa chỉ email người gửi (From Address) <span style="color: #dc2626;">*</span>
            </label>
            <input type="email" id="mail_from_address" class="form-control" placeholder="Ví dụ: bql.office@gmail.com" style="width: 100%; height: 40px; font-size: 13px; border-radius: 7px;" required>
          </div>
        </div>

        <!-- Khóa cứng cấu hình No-Reply tự động (Không để khách reply về email cá nhân) -->
        <input type="hidden" id="mail_reply_to_address" value="noreply@officeleasing.vn">
        <input type="hidden" id="mail_reply_to_name" value="No-Reply (Do Not Reply)">

        <div style="display: flex; justify-content: flex-end; gap: 12px; border-top: 1px solid #f1f5f9; padding-top: 20px;">
          <button type="submit" id="btn-save-mail-settings" class="btn-mo-primary" style="font-size: 13.5px; padding: 10px 26px; border-radius: 7px;">
            <i class="fa-solid fa-floppy-disk"></i> Lưu & Kích Hoạt Gửi Email
          </button>
        </div>
      </form>
    </div>

    <!-- CỘT PHẢI: TEST KẾT NỐI & HƯỚNG DẪN CẤU HÌNH (Padding 22px) -->
    <div style="display: flex; flex-direction: column; gap: 22px;">
      <!-- Khối Kiểm Tra Kết Nối SMTP -->
      <div class="filament-card" style="padding: 22px; border-top: 3px solid #2563eb; background: #ffffff;">
        <h4 style="font-size: 15px; font-weight: 700; color: #0f172a; margin-top: 0; margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
          <i class="fa-solid fa-paper-plane" style="color: #2563eb;"></i> Kiểm Tra & Gửi Email Thử Nghiệm
        </h4>
        <p style="font-size: 12.5px; color: #64748b; margin-top: 0; margin-bottom: 16px; line-height: 1.55;">
          Gửi thử 1 bức thư kiểm tra kết nối SMTP thực tế đến hộp thư của bạn trước khi đưa vào vận hành.
        </p>

        <div style="margin-bottom: 14px;">
          <label style="display: block; font-size: 12.5px; font-weight: 600; color: #334155; margin-bottom: 6px;">
            Địa chỉ email nhận thử nghiệm <span style="color: #dc2626;">*</span>
          </label>
          <input type="email" id="test_recipient_email" class="form-control" placeholder="Nhập email của bạn để nhận test" style="width: 100%; height: 40px; font-size: 13px; border-radius: 7px;">
        </div>

        <button type="button" id="btn-test-mail-connection" class="btn-mo-orange" style="width: 100%; justify-content: center; height: 40px; font-size: 13px; border-radius: 7px;" onclick="testMailConnection()">
          <i class="fa-solid fa-bolt"></i> Gửi Thử Nghiệm Ngay
        </button>

        <!-- Khối hiển thị thông báo kết quả test -->
        <div id="test-mail-result-box" style="display: none; margin-top: 14px; padding: 12px 14px; border-radius: 6px; font-size: 12.5px; line-height: 1.55;"></div>
      </div>

      <!-- Khối Hướng dẫn cấu hình Google App Password -->
      <div class="filament-card" style="padding: 18px 20px; background: #fafafa; border: 1px dashed #cbd5e1;">
        <h5 style="font-size: 13.5px; font-weight: 700; color: #334155; margin-top: 0; margin-bottom: 10px; display: flex; align-items: center; gap: 6px;">
          <i class="fa-brands fa-google" style="color: #ea4335;"></i> Hướng dẫn tạo Mật khẩu ứng dụng (Gmail)
        </h5>
        <ol style="margin: 0; padding-left: 18px; font-size: 12px; color: #64748b; line-height: 1.65;">
          <li>Bật <strong>Xác thực 2 bước (2FA)</strong> trong tài khoản Google.</li>
          <li>Truy cập mục <em>Bảo mật &rarr; Mật khẩu ứng dụng (App Passwords)</em>.</li>
          <li>Tạo mật khẩu mới cho ứng dụng (ví dụ đặt tên: <em>OfficeBMS</em>).</li>
          <li>Copy chuỗi 16 ký tự (ví dụ: <code style="background:#e2e8f0;padding:1px 5px;border-radius:4px;">xxxx yyyy zzzz wwww</code>) dán vào ô <strong>Mật khẩu ứng dụng</strong> ở trên.</li>
        </ol>
      </div>
    </div>
  </div>
</div>
