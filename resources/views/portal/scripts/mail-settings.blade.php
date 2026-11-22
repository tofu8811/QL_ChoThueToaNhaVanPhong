<script>
  /* ==========================================================================
     QUẢN TRỊ CẤU HÌNH EMAIL GỬI THÔNG BÁO HỆ THỐNG (MAIL SETTINGS)
     ========================================================================== */

  // Biến cờ lưu trạng thái cấu hình và trạng thái bật/tắt hiện tại
  let isMailSystemConfigured = false;
  let isMailSystemEnabled = false;

  // Cập nhật giao diện Toggle Switch và Banner thông báo theo trạng thái thực tế
  function renderMailStatusUI(isConfigured, isEnabled) {
    const toggleInput = document.getElementById('toggle-mail-status-input');
    const badgeToggle = document.getElementById('badge-mail-toggle-status');
    const banner = document.getElementById('banner-mail-active-status');
    const textNotice = document.getElementById('text-mail-active-status');
    const iconNotice = document.getElementById('icon-mail-active-status');
    const btnClear = document.getElementById('btn-clear-mail-settings');

    if (toggleInput) {
      toggleInput.checked = !!(isConfigured && isEnabled);
    }

    if (!isConfigured) {
      if (badgeToggle) {
        badgeToggle.innerText = 'Chưa thiết lập';
        badgeToggle.style.background = '#f1f5f9';
        badgeToggle.style.color = '#64748b';
      }
      if (banner) {
        banner.style.background = '#eff6ff';
        banner.style.borderLeft = '4px solid #3b82f6';
        banner.style.color = '#1e40af';
      }
      if (iconNotice) iconNotice.className = 'fa-solid fa-circle-info';
      if (textNotice) {
        textNotice.innerHTML = 'Điền đầy đủ thông tin máy chủ SMTP bên dưới và bấm <strong>"Lưu & Kích Hoạt Gửi Email"</strong> để kích hoạt gửi thông báo. Nếu chưa cấu hình, hệ thống sẽ tự động bỏ qua việc gửi email mà không gây gián đoạn quy trình nghiệp vụ.';
      }
      if (btnClear) btnClear.style.display = 'none';
    } else if (!isEnabled) {
      if (badgeToggle) {
        badgeToggle.innerText = 'Đang Tạm Tắt';
        badgeToggle.style.background = '#fef3c7';
        badgeToggle.style.color = '#b45309';
      }
      if (banner) {
        banner.style.background = '#fffbeb';
        banner.style.borderLeft = '4px solid #f59e0b';
        banner.style.color = '#b45309';
      }
      if (iconNotice) iconNotice.className = 'fa-solid fa-triangle-exclamation';
      if (textNotice) {
        textNotice.innerHTML = '<strong>Tính năng gửi email thông báo đang TẠM TẮT.</strong> Tài khoản và mật khẩu của bạn vẫn được lưu an toàn. Mọi giao dịch hợp đồng hoặc quên mật khẩu sẽ không gửi email cho khách thuê. Gạt công tắc <em>"Gửi email"</em> ở trên để kích hoạt lại.';
      }
      if (btnClear) btnClear.style.display = 'inline-flex';
    } else {
      if (badgeToggle) {
        badgeToggle.innerText = 'Đang Bật';
        badgeToggle.style.background = '#dcfce7';
        badgeToggle.style.color = '#15803d';
      }
      if (banner) {
        banner.style.background = '#f0fdf4';
        banner.style.borderLeft = '4px solid #22c55e';
        banner.style.color = '#166534';
      }
      if (iconNotice) iconNotice.className = 'fa-solid fa-circle-check';
      if (textNotice) {
        textNotice.innerHTML = '<strong>Đang BẬT gửi email thông báo tự động.</strong> Khách thuê sẽ nhận được email khi duyệt hợp đồng hoặc khôi phục mật khẩu. Bạn có thể gạt công tắc phía trên để tạm ngưng bất cứ lúc nào.';
      }
      if (btnClear) btnClear.style.display = 'inline-flex';
    }
  }

  // Hàm xử lý gạt công tắc BẬT / TẮT gửi email
  async function toggleMailStatus(checked) {
    const token = localStorage.getItem('auth_token');
    if (!token) {
      alert('Vui lòng đăng nhập lại.');
      renderMailStatusUI(isMailSystemConfigured, isMailSystemEnabled);
      return;
    }

    if (!isMailSystemConfigured && checked) {
      alert('Bạn chưa thiết lập tài khoản Gmail gửi thông báo.\n\nVui lòng điền thông tin tài khoản Gmail, Mật khẩu ứng dụng ở form bên dưới và bấm "Lưu & Kích Hoạt Gửi Email" trước khi bật.');
      renderMailStatusUI(false, false);
      return;
    }

    const toggleInput = document.getElementById('toggle-mail-status-input');
    if (toggleInput) toggleInput.disabled = true;

    try {
      const res = await fetch('/api/v1/admin/mail-settings/toggle', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'Authorization': 'Bearer ' + token
        },
        body: JSON.stringify({ is_enabled: checked })
      });
      const json = await res.json();

      if (res.ok && json.data) {
        isMailSystemEnabled = !!json.data.is_enabled;
        renderMailStatusUI(isMailSystemConfigured, isMailSystemEnabled);
      } else {
        alert(json.message || 'Thao tác bật/tắt gửi email thất bại.');
        renderMailStatusUI(isMailSystemConfigured, isMailSystemEnabled);
      }
    } catch (err) {
      console.error('Lỗi khi bật/tắt gửi email:', err);
      alert('Đã xảy ra lỗi mạng khi cập nhật trạng thái gửi email.');
      renderMailStatusUI(isMailSystemConfigured, isMailSystemEnabled);
    } finally {
      if (toggleInput) toggleInput.disabled = false;
    }
  }

  // Tự động điền email của Admin vào ô test khi mở trang
  function initMailSettingsTestEmail() {
    const adminEmail = localStorage.getItem('auth_email') || '';
    const testInput = document.getElementById('test_recipient_email');
    if (testInput && !testInput.value && adminEmail) {
      testInput.value = adminEmail;
    }
  }

  // Tự động đồng bộ tài khoản Gmail sang địa chỉ người gửi (From Address)
  function handleGmailUsernameInput(val) {
    const cleanVal = (val || '').trim();
    const fromAddr = document.getElementById('mail_from_address');
    if (fromAddr && (!fromAddr.value || fromAddr.dataset.autoFilled === 'true')) {
      fromAddr.value = cleanVal;
      fromAddr.dataset.autoFilled = 'true';
    }
  }

  // 1. Tải cấu hình Email hiện tại từ máy chủ
  async function loadMailSettings() {
    const token = localStorage.getItem('auth_token');
    if (!token) return;

    try {
      const res = await fetch('/api/v1/admin/mail-settings', {
        headers: {
          'Accept': 'application/json',
          'Authorization': 'Bearer ' + token
        }
      });
      const json = await res.json();

      if (res.ok && json.data) {
        const d = json.data;
        isMailSystemConfigured = !!d.is_configured;
        isMailSystemEnabled = !!d.is_enabled;

        // Điền vào form (luôn fallback thông số Gmail nếu chưa có)
        if (document.getElementById('mail_mailer')) document.getElementById('mail_mailer').value = d.mail_mailer || 'smtp';
        if (document.getElementById('mail_host')) document.getElementById('mail_host').value = d.mail_host || 'smtp.gmail.com';
        if (document.getElementById('mail_port')) document.getElementById('mail_port').value = d.mail_port || 587;
        if (document.getElementById('mail_encryption')) document.getElementById('mail_encryption').value = d.mail_encryption || 'tls';
        if (document.getElementById('mail_username')) document.getElementById('mail_username').value = d.mail_username || '';
        if (document.getElementById('mail_password')) document.getElementById('mail_password').value = d.mail_password_masked || '';
        if (document.getElementById('mail_from_name')) document.getElementById('mail_from_name').value = d.mail_from_name || '';
        if (document.getElementById('mail_from_address')) document.getElementById('mail_from_address').value = d.mail_from_address || '';
        if (document.getElementById('mail_reply_to_name')) document.getElementById('mail_reply_to_name').value = 'No-Reply (Do Not Reply)';
        if (document.getElementById('mail_reply_to_address')) document.getElementById('mail_reply_to_address').value = 'noreply@officeleasing.vn';

        renderMailStatusUI(isMailSystemConfigured, isMailSystemEnabled);
        initMailSettingsTestEmail();
      } else {
        alert('Không thể tải cấu hình email: ' + (json.message || 'Lỗi không xác định'));
      }
    } catch (err) {
      console.error('Lỗi khi tải cấu hình email:', err);
    }
  }

  // 2. Lưu cấu hình email mới và kích hoạt gửi email
  async function saveMailSettings() {
    const token = localStorage.getItem('auth_token');
    if (!token) {
      alert('Vui lòng đăng nhập lại để tiếp tục.');
      return;
    }

    const mailHostEl = document.getElementById('mail_host');
    const mailPortEl = document.getElementById('mail_port');
    const mailEncEl = document.getElementById('mail_encryption');
    const mailMailerEl = document.getElementById('mail_mailer');

    const payload = {
      mail_mailer: (mailMailerEl && mailMailerEl.value.trim()) ? mailMailerEl.value.trim() : 'smtp',
      mail_host: (mailHostEl && mailHostEl.value.trim()) ? mailHostEl.value.trim() : 'smtp.gmail.com',
      mail_port: (mailPortEl && parseInt(mailPortEl.value, 10)) ? parseInt(mailPortEl.value, 10) : 587,
      mail_encryption: (mailEncEl && mailEncEl.value.trim()) ? mailEncEl.value.trim() : 'tls',
      mail_username: (document.getElementById('mail_username')?.value || '').trim(),
      mail_password: (document.getElementById('mail_password')?.value || '').trim(),
      mail_from_name: (document.getElementById('mail_from_name')?.value || '').trim(),
      mail_from_address: (document.getElementById('mail_from_address')?.value || '').trim(),
      mail_reply_to_name: 'No-Reply (Do Not Reply)',
      mail_reply_to_address: 'noreply@officeleasing.vn',
    };

    // Client-side validation: Chỉ kiểm tra các trường mà người dùng thực tế nhập trên form
    if (!payload.mail_username || !payload.mail_from_address || !payload.mail_from_name) {
      alert('Vui lòng điền đầy đủ các trường bắt buộc có dấu (*): Tài khoản Gmail gửi, Tên người gửi hiển thị và Địa chỉ email người gửi.');
      return;
    }

    if (!payload.mail_password) {
      alert('Vui lòng nhập Mật khẩu ứng dụng (App Password) để kích hoạt tài khoản gửi mail.');
      document.getElementById('mail_password').focus();
      return;
    }

    const btn = document.getElementById('btn-save-mail-settings');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Đang lưu & kích hoạt...';

    try {
      const res = await fetch('/api/v1/admin/mail-settings', {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'Authorization': 'Bearer ' + token
        },
        body: JSON.stringify(payload)
      });
      const json = await res.json();

      if (res.ok) {
        alert(json.message || 'Đã lưu cấu hình email thành công! Hệ thống đã kích hoạt tính năng gửi email thông báo tự động.');
        loadMailSettings();
      } else {
        let errMsg = json.message || 'Lưu cấu hình thất bại.';
        if (json.errors) {
          const firstErr = Object.values(json.errors)[0];
          if (Array.isArray(firstErr)) errMsg += ' ' + firstErr[0];
        }
        alert(errMsg);
      }
    } catch (err) {
      console.error('Lỗi khi lưu cấu hình email:', err);
      alert('Đã xảy ra lỗi kết nối mạng khi lưu cấu hình email.');
    } finally {
      btn.disabled = false;
      btn.innerHTML = originalText;
    }
  }

  // 3. Kiểm tra kết nối & Gửi email thử nghiệm (Test SMTP Connection)
  async function testMailConnection() {
    const token = localStorage.getItem('auth_token');
    if (!token) {
      alert('Vui lòng đăng nhập lại.');
      return;
    }

    if (!isMailSystemConfigured) {
      alert('Hệ thống hiện CHƯA được lưu cấu hình email. Vui lòng điền thông tin và bấm "Lưu & Kích Hoạt Gửi Email" trước khi gửi thư thử nghiệm.');
      return;
    }

    if (!isMailSystemEnabled) {
      alert('Tính năng gửi email hiện đang TẠM TẮT. Vui lòng gạt công tắc "Gửi email" ở trên sang BẬT trước khi gửi thư thử nghiệm.');
      return;
    }

    const recipientInput = document.getElementById('test_recipient_email');
    const recipientEmail = recipientInput ? recipientInput.value.trim() : '';

    if (!recipientEmail || !recipientEmail.includes('@')) {
      alert('Vui lòng nhập địa chỉ email nhận thư thử nghiệm hợp lệ.');
      if (recipientInput) recipientInput.focus();
      return;
    }

    const btn = document.getElementById('btn-test-mail-connection');
    const resultBox = document.getElementById('test-mail-result-box');
    const originalText = btn.innerHTML;

    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Đang kiểm tra & gửi thư...';
    if (resultBox) {
      resultBox.style.display = 'block';
      resultBox.style.background = '#f8fafc';
      resultBox.style.color = '#334155';
      resultBox.style.border = '1px solid #cbd5e1';
      resultBox.innerHTML = '<i class="fa-solid fa-spinner fa-spin" style="color:#2563eb;"></i> Đang kết nối máy chủ SMTP và thực hiện gửi thư thử nghiệm...';
    }

    try {
      const res = await fetch('/api/v1/admin/mail-settings/test', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'Authorization': 'Bearer ' + token
        },
        body: JSON.stringify({ recipient_email: recipientEmail })
      });
      const json = await res.json();

      if (res.ok) {
        if (resultBox) {
          resultBox.style.display = 'block';
          resultBox.style.background = '#f0fdf4';
          resultBox.style.color = '#15803d';
          resultBox.style.border = '1px solid #86efac';
          resultBox.innerHTML = `<strong><i class="fa-solid fa-circle-check"></i> Kết nối thành công!</strong><br>${json.message || 'Thư thử nghiệm đã được gửi đi.'}`;
        }
      } else {
        if (resultBox) {
          resultBox.style.display = 'block';
          resultBox.style.background = '#fef2f2';
          resultBox.style.color = '#b91c1c';
          resultBox.style.border = '1px solid #fca5a5';
          resultBox.innerHTML = `<strong><i class="fa-solid fa-triangle-exclamation"></i> Gửi thử nghiệm thất bại:</strong><br>${json.message || 'Lỗi kết nối máy chủ SMTP.'}`;
        }
      }
    } catch (err) {
      console.error('Lỗi khi gửi email thử nghiệm:', err);
      if (resultBox) {
        resultBox.style.display = 'block';
        resultBox.style.background = '#fef2f2';
        resultBox.style.color = '#b91c1c';
        resultBox.style.border = '1px solid #fca5a5';
        resultBox.innerHTML = '<strong><i class="fa-solid fa-triangle-exclamation"></i> Lỗi mạng:</strong> Không thể kết nối tới máy chủ API.';
      }
    } finally {
      btn.disabled = false;
      btn.innerHTML = originalText;
    }
  }

  // 4. Xóa cấu hình email (Xóa thông tin tài khoản đã lưu)
  async function clearMailSettings() {
    if (!confirm('Bạn có chắc chắn muốn XÓA toàn bộ thông tin cấu hình email không?\n\nLưu ý: Nếu bạn chỉ muốn tạm ngưng gửi thư, hãy sử dụng công tắc "Gửi email" ở trên thay vì xóa cấu hình.\n\nSau khi xóa, tài khoản và mật khẩu sẽ bị xóa khỏi hệ thống.')) {
      return;
    }

    const token = localStorage.getItem('auth_token');
    if (!token) return;

    try {
      const res = await fetch('/api/v1/admin/mail-settings/reset', {
        method: 'POST',
        headers: {
          'Accept': 'application/json',
          'Authorization': 'Bearer ' + token
        }
      });
      const json = await res.json();

      if (res.ok) {
        alert('Đã xóa cấu hình email thành công. Hệ thống hiện đang TẮT gửi email thông báo.');
        loadMailSettings();
      } else {
        alert('Thao tác thất bại: ' + (json.message || 'Lỗi không xác định'));
      }
    } catch (err) {
      console.error('Lỗi khi xóa cấu hình email:', err);
    }
  }

  // 5. Ẩn / Hiện mật khẩu ứng dụng
  function toggleMailPasswordVisibility() {
    const input = document.getElementById('mail_password');
    const icon = document.getElementById('icon-toggle-mail-pwd');
    if (!input || !icon) return;

    if (input.type === 'password') {
      input.type = 'text';
      icon.classList.remove('fa-eye');
      icon.classList.add('fa-eye-slash');
    } else {
      input.type = 'password';
      icon.classList.remove('fa-eye-slash');
      icon.classList.add('fa-eye');
    }
  }
</script>
