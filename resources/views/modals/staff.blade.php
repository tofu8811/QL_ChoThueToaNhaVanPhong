<!-- ==========================================================================
         MODULE 5: STAFF & RBAC MODALS (ENDPOINTS 72 - 91)
         ========================================================================== -->

    <!-- 1. MODAL ADMIN: THÊM / SỬA (API 73 & 75) -->
    <div id="staff-admin-modal" class="mo-modal">
      <div class="mo-modal-content" style="max-width: 520px; border-radius: 12px; overflow: hidden;">
        <div class="mo-modal-header" style="background: linear-gradient(135deg, #0f3361, #1e40af); color: #fff; padding: 18px 24px;">
          <div>
            <h3 id="staff-admin-modal-title" style="margin:0; font-size: 17px; font-weight: 700; display:flex; align-items:center; gap:8px;">
              <i class="fa-solid fa-user-shield"></i> Thêm Quản Trị Viên Mới
            </h3>
            <span style="font-size: 12px; opacity: 0.85;">Quyền Quản trị viên cấp cao (Super Admin)</span>
          </div>
          <button class="mo-modal-close" style="color: #fff;" onclick="closeModal('staff-admin-modal')">&times;</button>
        </div>
        <div class="mo-modal-body" style="padding: 22px;">
          <form id="form-staff-admin" onsubmit="handleStaffAdminSubmit(event)">
            <input type="hidden" id="admin-form-id" value="">
            <input type="hidden" id="admin-form-mode" value="create">

            <div style="margin-bottom: 14px;">
              <label style="display:block; font-size:13px; font-weight:600; margin-bottom:5px;">Họ và tên Quản trị viên <span style="color:#ef4444;">*</span></label>
              <input type="text" id="admin-form-fullname" class="form-control" placeholder="VD: Nguyễn Văn An" oninput="moClearFieldError('admin-form-fullname', 'err-admin-fullname')" style="width:100%; height:40px; border-radius:6px; border:1px solid #cbd5e1; padding:0 12px;">
              <div id="err-admin-fullname" class="field-error-slot" style="display:none; color:#ef4444; font-size:11.5px; margin-top:4px; gap:4px; align-items:center;"></div>
            </div>

            <div style="margin-bottom: 14px;">
              <label style="display:block; font-size:13px; font-weight:600; margin-bottom:5px;">Email đăng nhập <span style="color:#ef4444;">*</span></label>
              <input type="email" id="admin-form-email" class="form-control" placeholder="admin@domain.com" oninput="moClearFieldError('admin-form-email', 'err-admin-email')" style="width:100%; height:40px; border-radius:6px; border:1px solid #cbd5e1; padding:0 12px;">
              <div id="err-admin-email" class="field-error-slot" style="display:none; color:#ef4444; font-size:11.5px; margin-top:4px; gap:4px; align-items:center;"></div>
            </div>

            <div style="margin-bottom: 14px;">
              <label style="display:block; font-size:13px; font-weight:600; margin-bottom:5px;">Số điện thoại liên hệ</label>
              <input type="tel" id="admin-form-phone" class="form-control" placeholder="0901234567" oninput="moSanitizeNumeric(this); moClearFieldError('admin-form-phone', 'err-admin-phone')" style="width:100%; height:40px; border-radius:6px; border:1px solid #cbd5e1; padding:0 12px;">
              <div id="err-admin-phone" class="field-error-slot" style="display:none; color:#ef4444; font-size:11.5px; margin-top:4px; gap:4px; align-items:center;"></div>
            </div>

            <div id="admin-form-password-group" style="margin-bottom: 14px;">
              <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:5px;">
                <label style="font-size:13px; font-weight:600; margin-bottom:0;">
                  Mật khẩu đăng nhập <span id="admin-form-password-req" style="color:#ef4444; display:none;">*</span>
                </label>
                <button type="button" onclick="generateRandomPasswordForInput('admin-form-password', 'Ad@')" class="btn-mo-outline" style="padding:2px 8px; font-size:11px; border-radius:4px;" title="Điền mật khẩu mặc định">
                  <i class="fa-solid fa-key"></i> Dùng MK Mặc Định
                </button>
              </div>
              <div style="position:relative;">
                <input type="password" id="admin-form-password" class="form-control" placeholder="Để trống để hệ thống tự đặt mật khẩu mặc định (123456Aa@)..." oninput="moClearFieldError('admin-form-password', 'err-admin-password')" style="width:100%; height:40px; border-radius:6px; border:1px solid #cbd5e1; padding:0 38px 0 12px;">
                <button type="button" onclick="moTogglePasswordVisibility('admin-form-password', this)" style="position:absolute; right:10px; top:50%; transform:translateY(-50%); background:none; border:none; color:#64748b; cursor:pointer; padding:4px;" title="Ẩn/Hiện mật khẩu">
                  <i class="fa-regular fa-eye"></i>
                </button>
              </div>
              <small id="admin-form-password-hint" style="color:#64748b; font-size:11.5px; display:block; margin-top:4px;">Để trống nếu không muốn đổi (khi sửa) hoặc để hệ thống tự sinh và gửi email (khi tạo mới).</small>
              <div id="err-admin-password" class="field-error-slot" style="display:none; color:#ef4444; font-size:11.5px; margin-top:4px; gap:4px; align-items:center;"></div>
            </div>

            <div id="admin-form-issuper-group" style="margin-bottom: 14px; background:#f8fafc; padding:12px; border-radius:8px; border:1px solid #e2e8f0; transition:all 0.2s;">
              <label id="admin-form-issuper-label" style="display:flex; align-items:center; gap:8px; font-size:13px; font-weight:600; cursor:pointer; margin-bottom:0;">
                <input type="checkbox" id="admin-form-issuper" style="accent-color:#ef4444; width:16px; height:16px;">
                <span id="admin-form-issuper-text">Cấp quyền Super Admin (Toàn quyền quản trị nhân sự)</span>
              </label>
              <div id="admin-form-issuper-hint" style="display:none; color:#b45309; background:#fffbeb; padding:7px 10px; border-radius:6px; font-size:11.5px; margin-top:8px; border:1px solid #fef3c7; align-items:center; gap:6px;">
                <i class="fa-solid fa-shield-halved" style="color:#d97706; font-size:12px;"></i>
                <span><strong>Bảo vệ phiên đăng nhập:</strong> Bạn không thể tự tước quyền Super Admin của chính mình để tránh mất quyền quản trị.</span>
              </div>
            </div>

            <div style="margin-bottom: 20px;">
              <label style="display:block; font-size:13px; font-weight:600; margin-bottom:5px;">Trạng thái tài khoản</label>
              <select id="admin-form-status" class="form-control" style="width:100%; height:40px; border-radius:6px; border:1px solid #cbd5e1; padding:0 12px; transition:all 0.2s;">
                <option value="ACTIVE">Hoạt động (ACTIVE)</option>
                <option value="LOCKED">Tạm khóa (LOCKED)</option>
                <option value="PENDING">Chờ kích hoạt (PENDING)</option>
              </select>
              <div id="admin-form-status-hint" style="display:none; color:#b45309; background:#fffbeb; padding:7px 10px; border-radius:6px; font-size:11.5px; margin-top:8px; border:1px solid #fef3c7; align-items:center; gap:6px;">
                <i class="fa-solid fa-lock" style="color:#d97706; font-size:12px;"></i>
                <span><strong>Bảo vệ phiên đăng nhập:</strong> Bạn không thể tự khóa hoặc đổi trạng thái tài khoản đang đăng nhập của chính mình.</span>
              </div>
            </div>

            <div style="display:flex; justify-content:flex-end; gap:10px;">
              <button type="button" class="btn-mo-outline" onclick="closeModal('staff-admin-modal')">Hủy bỏ</button>
              <button type="submit" id="btn-submit-staff-admin" class="btn-mo-orange">
                <i class="fa-solid fa-floppy-disk"></i> Lưu Thông Tin
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- 2. MODAL CHI TIẾT ADMIN (API 74) -->
    <div id="staff-admin-detail-modal" class="mo-modal">
      <div class="mo-modal-content" style="max-width: 480px; border-radius: 12px; overflow: hidden;">
        <div class="mo-modal-header" style="background:#0f3361; color:#fff; padding:16px 20px;">
          <h3 style="margin:0; font-size:16px; font-weight:700;"><i class="fa-solid fa-id-badge"></i> Chi Tiết Quản Trị Viên</h3>
          <button class="mo-modal-close" style="color:#fff;" onclick="closeModal('staff-admin-detail-modal')">&times;</button>
        </div>
        <div class="mo-modal-body" style="padding: 20px;" id="body-staff-admin-detail">
          <div style="text-align:center; padding:30px;"><i class="fa-solid fa-spinner fa-spin"></i> Đang tải dữ liệu...</div>
        </div>
      </div>
    </div>

    <!-- 3. MODAL SALES: THÊM / SỬA (API 78 & 80) -->
    <div id="staff-sale-modal" class="mo-modal">
      <div class="mo-modal-content" style="max-width: 600px; border-radius: 12px; overflow: hidden;">
        <div class="mo-modal-header" style="background: linear-gradient(135deg, #1e3a8a, #2563eb); color: #fff; padding: 18px 24px;">
          <div>
            <h3 id="staff-sale-modal-title" style="margin:0; font-size: 17px; font-weight: 700; display:flex; align-items:center; gap:8px;">
              <i class="fa-solid fa-user-tie"></i> Thêm Nhân Viên Sales Mới
            </h3>
            <span style="font-size: 12px; opacity: 0.85;">Quản trị nhân sự & phân bổ Lead khách hàng</span>
          </div>
          <button class="mo-modal-close" style="color: #fff;" onclick="closeModal('staff-sale-modal')">&times;</button>
        </div>
        <div class="mo-modal-body" style="padding: 22px; max-height: 80vh; overflow-y: auto;">
          <form id="form-staff-sale" onsubmit="handleStaffSaleSubmit(event)">
            <input type="hidden" id="sale-form-id" value="">
            <input type="hidden" id="sale-form-mode" value="create">

            <div style="display:grid; grid-template-columns: 1fr 2fr; gap:12px; margin-bottom: 14px;">
              <div>
                <label style="display:block; font-size:13px; font-weight:600; margin-bottom:5px;">Mã nhân viên</label>
                <input type="text" id="sale-form-code" readonly class="form-control" placeholder="Tự động cấp..." style="width:100%; height:40px; border-radius:6px; border:1px solid #cbd5e1; padding:0 12px; background:#f1f5f9; cursor:not-allowed; font-weight:700; color:#334155; font-family:monospace;">
                <small style="color:#64748b; font-size:11px; display:flex; align-items:center; gap:4px; margin-top:3px;"><i class="fa-solid fa-lock" style="font-size:10px;"></i> Tự động từ CSDL</small>
              </div>
              <div>
                <label style="display:block; font-size:13px; font-weight:600; margin-bottom:5px;">Họ và tên <span style="color:#ef4444;">*</span></label>
                <input type="text" id="sale-form-fullname" class="form-control" placeholder="VD: Lê Hoàng Long" oninput="moClearFieldError('sale-form-fullname', 'err-sale-fullname')" style="width:100%; height:40px; border-radius:6px; border:1px solid #cbd5e1; padding:0 12px;">
                <div id="err-sale-fullname" class="field-error-slot" style="display:none; color:#ef4444; font-size:11.5px; margin-top:4px; gap:4px; align-items:center;"></div>
              </div>
            </div>

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:12px; margin-bottom: 14px;">
              <div>
                <label style="display:block; font-size:13px; font-weight:600; margin-bottom:5px;">Email đăng nhập <span style="color:#ef4444;">*</span></label>
                <input type="email" id="sale-form-email" class="form-control" placeholder="long.le@domain.com" oninput="moClearFieldError('sale-form-email', 'err-sale-email')" style="width:100%; height:40px; border-radius:6px; border:1px solid #cbd5e1; padding:0 12px;">
                <div id="err-sale-email" class="field-error-slot" style="display:none; color:#ef4444; font-size:11.5px; margin-top:4px; gap:4px; align-items:center;"></div>
              </div>
              <div>
                <label style="display:block; font-size:13px; font-weight:600; margin-bottom:5px;">Số điện thoại</label>
                <input type="tel" id="sale-form-phone" class="form-control" placeholder="0908889999" oninput="moSanitizeNumeric(this); moClearFieldError('sale-form-phone', 'err-sale-phone')" style="width:100%; height:40px; border-radius:6px; border:1px solid #cbd5e1; padding:0 12px;">
                <div id="err-sale-phone" class="field-error-slot" style="display:none; color:#ef4444; font-size:11.5px; margin-top:4px; gap:4px; align-items:center;"></div>
              </div>
            </div>

            <div id="sale-form-password-group" style="margin-bottom: 14px;">
              <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:5px;">
                <label style="font-size:13px; font-weight:600; margin-bottom:0;">
                  Mật khẩu đăng nhập <span id="sale-form-password-req" style="color:#ef4444; display:none;">*</span>
                </label>
                <button type="button" onclick="generateRandomPasswordForInput('sale-form-password', 'Sa@')" class="btn-mo-outline" style="padding:2px 8px; font-size:11px; border-radius:4px;" title="Điền mật khẩu mặc định">
                  <i class="fa-solid fa-key"></i> Dùng MK Mặc Định
                </button>
              </div>
              <div style="position:relative;">
                <input type="password" id="sale-form-password" class="form-control" placeholder="Để trống để hệ thống tự đặt mật khẩu mặc định (123456Aa@)..." oninput="moClearFieldError('sale-form-password', 'err-sale-password')" style="width:100%; height:40px; border-radius:6px; border:1px solid #cbd5e1; padding:0 38px 0 12px;">
                <button type="button" onclick="moTogglePasswordVisibility('sale-form-password', this)" style="position:absolute; right:10px; top:50%; transform:translateY(-50%); background:none; border:none; color:#64748b; cursor:pointer; padding:4px;" title="Ẩn/Hiện mật khẩu">
                  <i class="fa-regular fa-eye"></i>
                </button>
              </div>
              <small id="sale-form-password-hint" style="color:#64748b; font-size:11.5px; display:block; margin-top:4px;">Để trống nếu không muốn đổi (khi sửa) hoặc để hệ thống tự sinh và gửi email (khi tạo mới).</small>
              <div id="err-sale-password" class="field-error-slot" style="display:none; color:#ef4444; font-size:11.5px; margin-top:4px; gap:4px; align-items:center;"></div>
            </div>

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:12px; margin-bottom: 14px;">
              <div>
                <label style="display:block; font-size:13px; font-weight:600; margin-bottom:5px;">Hạn mức Lead cùng lúc (Max)</label>
                <input type="number" id="sale-form-max-leads" min="1" value="10" class="form-control" style="width:100%; height:40px; border-radius:6px; border:1px solid #cbd5e1; padding:0 12px;">
              </div>
              <div>
                <label style="display:block; font-size:13px; font-weight:600; margin-bottom:5px;">Tỷ lệ hoa hồng (%)</label>
                <input type="number" step="0.1" id="sale-form-commission" placeholder="VD: 5.0" class="form-control" style="width:100%; height:40px; border-radius:6px; border:1px solid #cbd5e1; padding:0 12px;">
              </div>
            </div>

            <div style="margin-bottom: 20px;">
              <label style="display:block; font-size:13px; font-weight:600; margin-bottom:5px;">Trạng thái làm việc</label>
              <select id="sale-form-status" class="form-control" style="width:100%; height:40px; border-radius:6px; border:1px solid #cbd5e1; padding:0 12px;">
                <option value="ACTIVE">Hoạt động (ACTIVE - Nhận Lead)</option>
                <option value="LOCKED">Tạm ngưng (LOCKED - Loại khỏi vòng phân bổ)</option>
                <option value="PENDING">Chờ kích hoạt (PENDING)</option>
              </select>
            </div>

            <div style="display:flex; justify-content:flex-end; gap:10px;">
              <button type="button" class="btn-mo-outline" onclick="closeModal('staff-sale-modal')">Hủy bỏ</button>
              <button type="submit" id="btn-submit-staff-sale" class="btn-mo-orange">
                <i class="fa-solid fa-floppy-disk"></i> Lưu Nhân Viên Sales
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- 4. MODAL CHI TIẾT SALES (API 79) -->
    <div id="staff-sale-detail-modal" class="mo-modal">
      <div class="mo-modal-content" style="max-width: 580px; border-radius: 12px; overflow: hidden;">
        <div class="mo-modal-header" style="background:#1e3a8a; color:#fff; padding:16px 20px;">
          <h3 style="margin:0; font-size:16px; font-weight:700;"><i class="fa-solid fa-id-card"></i> Hồ Sơ Sales & Hiệu Suất KPI</h3>
          <button class="mo-modal-close" style="color:#fff;" onclick="closeModal('staff-sale-detail-modal')">&times;</button>
        </div>
        <div class="mo-modal-body" style="padding: 20px;" id="body-staff-sale-detail">
          <div style="text-align:center; padding:30px;"><i class="fa-solid fa-spinner fa-spin"></i> Đang tải dữ liệu...</div>
        </div>
      </div>
    </div>

    <!-- 5. MODAL BAN QUẢN LÝ (PM): THÊM / SỬA (API 83 & 85) -->
    <div id="staff-pm-modal" class="mo-modal">
      <div class="mo-modal-content" style="max-width: 600px; border-radius: 12px; overflow: hidden;">
        <div class="mo-modal-header" style="background: linear-gradient(135deg, #b45309, #d97706); color: #fff; padding: 18px 24px;">
          <div>
            <h3 id="staff-pm-modal-title" style="margin:0; font-size: 17px; font-weight: 700; display:flex; align-items:center; gap:8px;">
              <i class="fa-solid fa-helmet-safety"></i> Thêm Nhân Sự Ban Quản Lý (PM)
            </h3>
            <span style="font-size: 12px; opacity: 0.85;">Quản lý kỹ thuật, sự cố bảo trì & dịch vụ tòa nhà</span>
          </div>
          <button class="mo-modal-close" style="color: #fff;" onclick="closeModal('staff-pm-modal')">&times;</button>
        </div>
        <div class="mo-modal-body" style="padding: 22px; max-height: 80vh; overflow-y: auto;">
          <form id="form-staff-pm" onsubmit="handleStaffPmSubmit(event)">
            <input type="hidden" id="pm-form-id" value="">
            <input type="hidden" id="pm-form-mode" value="create">

            <div style="display:grid; grid-template-columns: 1fr 2fr; gap:12px; margin-bottom: 14px;">
              <div>
                <label style="display:block; font-size:13px; font-weight:600; margin-bottom:5px;">Mã nhân viên</label>
                <input type="text" id="pm-form-code" readonly class="form-control" placeholder="Tự động cấp..." style="width:100%; height:40px; border-radius:6px; border:1px solid #cbd5e1; padding:0 12px; background:#f1f5f9; cursor:not-allowed; font-weight:700; color:#334155; font-family:monospace;">
                <small style="color:#64748b; font-size:11px; display:flex; align-items:center; gap:4px; margin-top:3px;"><i class="fa-solid fa-lock" style="font-size:10px;"></i> Tự động từ CSDL</small>
              </div>
              <div>
                <label style="display:block; font-size:13px; font-weight:600; margin-bottom:5px;">Họ và tên <span style="color:#ef4444;">*</span></label>
                <input type="text" id="pm-form-fullname" class="form-control" placeholder="VD: Đỗ Minh Quang" oninput="moClearFieldError('pm-form-fullname', 'err-pm-fullname')" style="width:100%; height:40px; border-radius:6px; border:1px solid #cbd5e1; padding:0 12px;">
                <div id="err-pm-fullname" class="field-error-slot" style="display:none; color:#ef4444; font-size:11.5px; margin-top:4px; gap:4px; align-items:center;"></div>
              </div>
            </div>

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:12px; margin-bottom: 14px;">
              <div>
                <label style="display:block; font-size:13px; font-weight:600; margin-bottom:5px;">Email đăng nhập <span style="color:#ef4444;">*</span></label>
                <input type="email" id="pm-form-email" class="form-control" placeholder="pm.quang@domain.com" oninput="moClearFieldError('pm-form-email', 'err-pm-email')" style="width:100%; height:40px; border-radius:6px; border:1px solid #cbd5e1; padding:0 12px;">
                <div id="err-pm-email" class="field-error-slot" style="display:none; color:#ef4444; font-size:11.5px; margin-top:4px; gap:4px; align-items:center;"></div>
              </div>
              <div>
                <label style="display:block; font-size:13px; font-weight:600; margin-bottom:5px;">Số điện thoại di động</label>
                <input type="tel" id="pm-form-phone" class="form-control" placeholder="0912345678" oninput="moSanitizeNumeric(this); moClearFieldError('pm-form-phone', 'err-pm-phone')" style="width:100%; height:40px; border-radius:6px; border:1px solid #cbd5e1; padding:0 12px;">
                <div id="err-pm-phone" class="field-error-slot" style="display:none; color:#ef4444; font-size:11.5px; margin-top:4px; gap:4px; align-items:center;"></div>
              </div>
            </div>

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:12px; margin-bottom: 14px;">
              <div>
                <label style="display:block; font-size:13px; font-weight:600; margin-bottom:5px;">Hotline sự cố khẩn cấp (24/7)</label>
                <input type="tel" id="pm-form-hotline" class="form-control" placeholder="VD: 0909000111" oninput="moSanitizeNumeric(this); moClearFieldError('pm-form-hotline', 'err-pm-hotline')" style="width:100%; height:40px; border-radius:6px; border:1px solid #cbd5e1; padding:0 12px;">
                <div id="err-pm-hotline" class="field-error-slot" style="display:none; color:#ef4444; font-size:11.5px; margin-top:4px; gap:4px; align-items:center;"></div>
              </div>
              <div>
                <label style="display:block; font-size:13px; font-weight:600; margin-bottom:5px;">Bộ phận chuyên trách</label>
                <select id="pm-form-department" class="form-control" style="width:100%; height:40px; border-radius:6px; border:1px solid #cbd5e1; padding:0 12px;">
                  <option value="TECHNICAL">Kỹ thuật & Cơ điện (TECHNICAL)</option>
                  <option value="SECURITY">An ninh & Bảo vệ (SECURITY)</option>
                  <option value="CLEANING">Vệ sinh & Môi trường (CLEANING)</option>
                  <option value="CUSTOMER_CARE">Chăm sóc khách thuê (CUSTOMER_CARE)</option>
                </select>
              </div>
            </div>

            <div id="pm-form-password-group" style="margin-bottom: 14px;">
              <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:5px;">
                <label style="font-size:13px; font-weight:600; margin-bottom:0;">
                  Mật khẩu đăng nhập <span id="pm-form-password-req" style="color:#ef4444; display:none;">*</span>
                </label>
                <button type="button" onclick="generateRandomPasswordForInput('pm-form-password', 'Pm@')" class="btn-mo-outline" style="padding:2px 8px; font-size:11px; border-radius:4px;" title="Điền mật khẩu mặc định">
                  <i class="fa-solid fa-key"></i> Dùng MK Mặc Định
                </button>
              </div>
              <div style="position:relative;">
                <input type="password" id="pm-form-password" class="form-control" placeholder="Để trống để hệ thống tự đặt mật khẩu mặc định (123456Aa@)..." oninput="moClearFieldError('pm-form-password', 'err-pm-password')" style="width:100%; height:40px; border-radius:6px; border:1px solid #cbd5e1; padding:0 38px 0 12px;">
                <button type="button" onclick="moTogglePasswordVisibility('pm-form-password', this)" style="position:absolute; right:10px; top:50%; transform:translateY(-50%); background:none; border:none; color:#64748b; cursor:pointer; padding:4px;" title="Ẩn/Hiện mật khẩu">
                  <i class="fa-regular fa-eye"></i>
                </button>
              </div>
              <small id="pm-form-password-hint" style="color:#64748b; font-size:11.5px; display:block; margin-top:4px;">Để trống nếu không muốn đổi (khi sửa) hoặc để hệ thống tự sinh và gửi email (khi tạo mới).</small>
              <div id="err-pm-password" class="field-error-slot" style="display:none; color:#ef4444; font-size:11.5px; margin-top:4px; gap:4px; align-items:center;"></div>
            </div>

            <div style="margin-bottom: 20px;">
              <label style="display:block; font-size:13px; font-weight:600; margin-bottom:5px;">Trạng thái</label>
              <select id="pm-form-status" class="form-control" style="width:100%; height:40px; border-radius:6px; border:1px solid #cbd5e1; padding:0 12px;">
                <option value="ACTIVE">Hoạt động (ACTIVE)</option>
                <option value="LOCKED">Tạm khóa (LOCKED)</option>
                <option value="PENDING">Chờ kích hoạt (PENDING)</option>
              </select>
            </div>

            <div style="display:flex; justify-content:flex-end; gap:10px;">
              <button type="button" class="btn-mo-outline" onclick="closeModal('staff-pm-modal')">Hủy bỏ</button>
              <button type="submit" id="btn-submit-staff-pm" class="btn-mo-orange">
                <i class="fa-solid fa-floppy-disk"></i> Lưu Nhân Sự BQL
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- 6. MODAL CHI TIẾT BQL (PM) (API 84) -->
    <div id="staff-pm-detail-modal" class="mo-modal">
      <div class="mo-modal-content" style="max-width: 540px; border-radius: 12px; overflow: hidden;">
        <div class="mo-modal-header" style="background:#b45309; color:#fff; padding:16px 20px;">
          <h3 style="margin:0; font-size:16px; font-weight:700;"><i class="fa-solid fa-helmet-safety"></i> Chi Tiết Ban Quản Lý</h3>
          <button class="mo-modal-close" style="color:#fff;" onclick="closeModal('staff-pm-detail-modal')">&times;</button>
        </div>
        <div class="mo-modal-body" style="padding: 20px;" id="body-staff-pm-detail">
          <div style="text-align:center; padding:30px;"><i class="fa-solid fa-spinner fa-spin"></i> Đang tải dữ liệu...</div>
        </div>
      </div>
    </div>

    <!-- 7. MODAL KẾ TOÁN (ACCOUNTANTS): THÊM / SỬA (API 88 & 90) -->
    <div id="staff-accountant-modal" class="mo-modal">
      <div class="mo-modal-content" style="max-width: 600px; border-radius: 12px; overflow: hidden;">
        <div class="mo-modal-header" style="background: linear-gradient(135deg, #065f46, #059669); color: #fff; padding: 18px 24px;">
          <div>
            <h3 id="staff-accountant-modal-title" style="margin:0; font-size: 17px; font-weight: 700; display:flex; align-items:center; gap:8px;">
              <i class="fa-solid fa-calculator"></i> Thêm Nhân Viên Kế Toán Mới
            </h3>
            <span style="font-size: 12px; opacity: 0.85;">Quản lý hóa đơn cước phí & công nợ tòa nhà</span>
          </div>
          <button class="mo-modal-close" style="color: #fff;" onclick="closeModal('staff-accountant-modal')">&times;</button>
        </div>
        <div class="mo-modal-body" style="padding: 22px; max-height: 80vh; overflow-y: auto;">
          <form id="form-staff-accountant" onsubmit="handleStaffAccountantSubmit(event)">
            <input type="hidden" id="acc-form-id" value="">
            <input type="hidden" id="acc-form-mode" value="create">

            <div style="display:grid; grid-template-columns: 1fr 2fr; gap:12px; margin-bottom: 14px;">
              <div>
                <label style="display:block; font-size:13px; font-weight:600; margin-bottom:5px;">Mã nhân viên</label>
                <input type="text" id="acc-form-code" readonly class="form-control" placeholder="Tự động cấp..." style="width:100%; height:40px; border-radius:6px; border:1px solid #cbd5e1; padding:0 12px; background:#f1f5f9; cursor:not-allowed; font-weight:700; color:#334155; font-family:monospace;">
                <small style="color:#64748b; font-size:11px; display:flex; align-items:center; gap:4px; margin-top:3px;"><i class="fa-solid fa-lock" style="font-size:10px;"></i> Tự động từ CSDL</small>
              </div>
              <div>
                <label style="display:block; font-size:13px; font-weight:600; margin-bottom:5px;">Họ và tên <span style="color:#ef4444;">*</span></label>
                <input type="text" id="acc-form-fullname" class="form-control" placeholder="VD: Trần Thị Mai" oninput="moClearFieldError('acc-form-fullname', 'err-acc-fullname')" style="width:100%; height:40px; border-radius:6px; border:1px solid #cbd5e1; padding:0 12px;">
                <div id="err-acc-fullname" class="field-error-slot" style="display:none; color:#ef4444; font-size:11.5px; margin-top:4px; gap:4px; align-items:center;"></div>
              </div>
            </div>

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:12px; margin-bottom: 14px;">
              <div>
                <label style="display:block; font-size:13px; font-weight:600; margin-bottom:5px;">Email đăng nhập <span style="color:#ef4444;">*</span></label>
                <input type="email" id="acc-form-email" class="form-control" placeholder="mai.tran@domain.com" oninput="moClearFieldError('acc-form-email', 'err-acc-email')" style="width:100%; height:40px; border-radius:6px; border:1px solid #cbd5e1; padding:0 12px;">
                <div id="err-acc-email" class="field-error-slot" style="display:none; color:#ef4444; font-size:11.5px; margin-top:4px; gap:4px; align-items:center;"></div>
              </div>
              <div>
                <label style="display:block; font-size:13px; font-weight:600; margin-bottom:5px;">Số điện thoại</label>
                <input type="tel" id="acc-form-phone" class="form-control" placeholder="0933334444" oninput="moSanitizeNumeric(this); moClearFieldError('acc-form-phone', 'err-acc-phone')" style="width:100%; height:40px; border-radius:6px; border:1px solid #cbd5e1; padding:0 12px;">
                <div id="err-acc-phone" class="field-error-slot" style="display:none; color:#ef4444; font-size:11.5px; margin-top:4px; gap:4px; align-items:center;"></div>
              </div>
            </div>

            <div id="acc-form-password-group" style="margin-bottom: 14px;">
              <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:5px;">
                <label style="font-size:13px; font-weight:600; margin-bottom:0;">
                  Mật khẩu đăng nhập <span id="acc-form-password-req" style="color:#ef4444; display:none;">*</span>
                </label>
                <button type="button" onclick="generateRandomPasswordForInput('acc-form-password', 'Ac@')" class="btn-mo-outline" style="padding:2px 8px; font-size:11px; border-radius:4px;" title="Điền mật khẩu mặc định">
                  <i class="fa-solid fa-key"></i> Dùng MK Mặc Định
                </button>
              </div>
              <div style="position:relative;">
                <input type="password" id="acc-form-password" class="form-control" placeholder="Để trống để hệ thống tự đặt mật khẩu mặc định (123456Aa@)..." oninput="moClearFieldError('acc-form-password', 'err-acc-password')" style="width:100%; height:40px; border-radius:6px; border:1px solid #cbd5e1; padding:0 38px 0 12px;">
                <button type="button" onclick="moTogglePasswordVisibility('acc-form-password', this)" style="position:absolute; right:10px; top:50%; transform:translateY(-50%); background:none; border:none; color:#64748b; cursor:pointer; padding:4px;" title="Ẩn/Hiện mật khẩu">
                  <i class="fa-regular fa-eye"></i>
                </button>
              </div>
              <small id="acc-form-password-hint" style="color:#64748b; font-size:11.5px; display:block; margin-top:4px;">Để trống nếu không muốn đổi (khi sửa) hoặc để hệ thống tự sinh và gửi email (khi tạo mới).</small>
              <div id="err-acc-password" class="field-error-slot" style="display:none; color:#ef4444; font-size:11.5px; margin-top:4px; gap:4px; align-items:center;"></div>
            </div>

            <div style="margin-bottom: 14px; background:#ecfdf5; padding:12px; border-radius:8px; border:1px solid #a7f3d0;">
              <label style="display:flex; align-items:center; gap:8px; font-size:13px; font-weight:600; cursor:pointer; color:#065f46;">
                <input type="checkbox" id="acc-form-can-adjust" style="accent-color:#059669; width:16px; height:16px;">
                <span>Cho phép phê duyệt phiếu điều chỉnh cước (can_approve_adjustment)</span>
              </label>
            </div>

            <div style="margin-bottom: 20px;">
              <label style="display:block; font-size:13px; font-weight:600; margin-bottom:5px;">Trạng thái</label>
              <select id="acc-form-status" class="form-control" style="width:100%; height:40px; border-radius:6px; border:1px solid #cbd5e1; padding:0 12px;">
                <option value="ACTIVE">Hoạt động (ACTIVE)</option>
                <option value="LOCKED">Tạm khóa (LOCKED)</option>
                <option value="PENDING">Chờ kích hoạt (PENDING)</option>
              </select>
            </div>

            <div style="display:flex; justify-content:flex-end; gap:10px;">
              <button type="button" class="btn-mo-outline" onclick="closeModal('staff-accountant-modal')">Hủy bỏ</button>
              <button type="submit" id="btn-submit-staff-accountant" class="btn-mo-orange">
                <i class="fa-solid fa-floppy-disk"></i> Lưu Kế Toán
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- 8. MODAL CHI TIẾT KẾ TOÁN (API 89) -->
    <div id="staff-accountant-detail-modal" class="mo-modal">
      <div class="mo-modal-content" style="max-width: 540px; border-radius: 12px; overflow: hidden;">
        <div class="mo-modal-header" style="background:#065f46; color:#fff; padding:16px 20px;">
          <h3 style="margin:0; font-size:16px; font-weight:700;"><i class="fa-solid fa-calculator"></i> Chi Tiết Nhân Viên Kế Toán</h3>
          <button class="mo-modal-close" style="color:#fff;" onclick="closeModal('staff-accountant-detail-modal')">&times;</button>
        </div>
        <div class="mo-modal-body" style="padding: 20px;" id="body-staff-accountant-detail">
          <div style="text-align:center; padding:30px;"><i class="fa-solid fa-spinner fa-spin"></i> Đang tải dữ liệu...</div>
        </div>
      </div>
    </div>

    <!-- 9. MODAL THÔNG BÁO MẬT KHẨU KHỞI TẠO / CẤP LẠI (ONE-TIME CREDENTIAL MODAL) -->
    <div id="staff-credential-success-modal" class="mo-modal">
      <div class="mo-modal-content" style="max-width: 480px; border-radius: 14px; overflow: hidden; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2);">
        <div class="mo-modal-header" style="background: linear-gradient(135deg, #10b981, #059669); color: #fff; padding: 18px 24px;">
          <h3 id="staff-credential-modal-title" style="margin:0; font-size: 16.5px; font-weight: 700; display:flex; align-items:center; gap:8px;">
            <i class="fa-solid fa-circle-check"></i> Cấp Mật Khẩu Thành Công
          </h3>
          <button class="mo-modal-close" style="color: #fff;" onclick="closeModal('staff-credential-success-modal')">&times;</button>
        </div>
        <div class="mo-modal-body" style="padding: 22px;">
          <p id="staff-credential-modal-desc" style="font-size: 13.5px; color: #334155; margin-bottom: 16px; line-height: 1.5;">
            Tài khoản nhân sự đã được thiết lập thành công. Vui lòng sao chép mật khẩu bên dưới:
          </p>
          
          <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px; margin-bottom: 16px;">
            <div style="display:flex; justify-content:space-between; margin-bottom:8px; font-size:13px;">
              <span style="color:#64748b;">Họ và tên:</span>
              <strong id="staff-credential-name" style="color:#0f172a;">---</strong>
            </div>
            <div style="display:flex; justify-content:space-between; margin-bottom:8px; font-size:13px;">
              <span style="color:#64748b;">Email đăng nhập:</span>
              <strong id="staff-credential-email" style="color:#0f172a; font-family:monospace;">---</strong>
            </div>
            <div style="display:flex; justify-content:space-between; align-items:center; margin-top:12px; padding-top:12px; border-top:1px dashed #cbd5e1;">
              <span style="color:#64748b; font-size:13px; font-weight:600;">Mật khẩu:</span>
              <div style="display:flex; align-items:center; gap:8px;">
                <code id="staff-credential-password" style="font-size:16px; font-weight:700; color:#2563eb; background:#eff6ff; padding:4px 10px; border-radius:6px; letter-spacing:1px;">---</code>
                <button type="button" class="btn-mo-outline" onclick="copyStaffCredentialPassword()" style="padding:5px 10px; font-size:12px;" title="Sao chép mật khẩu">
                  <i class="fa-regular fa-copy"></i>
                </button>
              </div>
            </div>
          </div>

          <div style="background: #f0fdf4; border-left: 4px solid #10b981; border-radius: 6px; padding: 10px 14px; margin-bottom: 20px; font-size: 12.5px; color: #166534; display:flex; gap:8px; align-items:flex-start;">
            <i class="fa-solid fa-envelope-circle-check" style="margin-top:2px; font-size:14px;"></i>
            <span>Thông tin tài khoản và mật khẩu đã được tự động xếp vào hàng đợi gửi email thông báo cho nhân sự.</span>
          </div>

          <div style="display:flex; justify-content:flex-end;">
            <button type="button" class="btn-mo-primary" style="padding:8px 20px; font-size:13px; border-radius:6px; display:inline-flex; align-items:center; justify-content:center; gap:6px; text-align:center;" onclick="closeModal('staff-credential-success-modal')">
              <i class="fa-solid fa-check"></i> Đóng
            </button>
          </div>
        </div>
      </div>
    </div>
