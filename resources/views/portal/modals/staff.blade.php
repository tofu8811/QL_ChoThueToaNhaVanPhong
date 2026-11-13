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
              <input type="text" id="admin-form-fullname" required class="form-control" placeholder="VD: Nguyễn Văn An" style="width:100%; height:40px; border-radius:6px; border:1px solid #cbd5e1; padding:0 12px;">
            </div>

            <div style="margin-bottom: 14px;">
              <label style="display:block; font-size:13px; font-weight:600; margin-bottom:5px;">Email đăng nhập <span style="color:#ef4444;">*</span></label>
              <input type="email" id="admin-form-email" required class="form-control" placeholder="admin@domain.com" style="width:100%; height:40px; border-radius:6px; border:1px solid #cbd5e1; padding:0 12px;">
            </div>

            <div style="margin-bottom: 14px;">
              <label style="display:block; font-size:13px; font-weight:600; margin-bottom:5px;">Số điện thoại liên hệ</label>
              <input type="tel" id="admin-form-phone" class="form-control" placeholder="0901234567" style="width:100%; height:40px; border-radius:6px; border:1px solid #cbd5e1; padding:0 12px;">
            </div>

            <div id="admin-form-password-group" style="margin-bottom: 14px;">
              <label style="display:block; font-size:13px; font-weight:600; margin-bottom:5px;">
                Mật khẩu đăng nhập <span id="admin-form-password-req" style="color:#ef4444;">*</span>
              </label>
              <input type="password" id="admin-form-password" class="form-control" placeholder="Tối thiểu 8 ký tự (hoa, thường, số, ký tự đặc biệt)" style="width:100%; height:40px; border-radius:6px; border:1px solid #cbd5e1; padding:0 12px;">
              <small id="admin-form-password-hint" style="color:#64748b; font-size:11.5px; display:none; margin-top:4px;">Để trống nếu không muốn đổi mật khẩu.</small>
            </div>

            <div style="margin-bottom: 14px; background:#f8fafc; padding:12px; border-radius:8px; border:1px solid #e2e8f0;">
              <label style="display:flex; align-items:center; gap:8px; font-size:13px; font-weight:600; cursor:pointer;">
                <input type="checkbox" id="admin-form-issuper" style="accent-color:#ef4444; width:16px; height:16px;">
                <span>Cấp quyền Super Admin (Toàn quyền quản trị nhân sự)</span>
              </label>
            </div>

            <div style="margin-bottom: 20px;">
              <label style="display:block; font-size:13px; font-weight:600; margin-bottom:5px;">Trạng thái tài khoản</label>
              <select id="admin-form-status" class="form-control" style="width:100%; height:40px; border-radius:6px; border:1px solid #cbd5e1; padding:0 12px;">
                <option value="ACTIVE">Hoạt động (ACTIVE)</option>
                <option value="LOCKED">Tạm khóa (LOCKED)</option>
                <option value="PENDING">Chờ kích hoạt (PENDING)</option>
              </select>
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
                <input type="text" id="sale-form-code" class="form-control" placeholder="VD: SL-009" style="width:100%; height:40px; border-radius:6px; border:1px solid #cbd5e1; padding:0 12px;">
              </div>
              <div>
                <label style="display:block; font-size:13px; font-weight:600; margin-bottom:5px;">Họ và tên <span style="color:#ef4444;">*</span></label>
                <input type="text" id="sale-form-fullname" required class="form-control" placeholder="VD: Lê Hoàng Long" style="width:100%; height:40px; border-radius:6px; border:1px solid #cbd5e1; padding:0 12px;">
              </div>
            </div>

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:12px; margin-bottom: 14px;">
              <div>
                <label style="display:block; font-size:13px; font-weight:600; margin-bottom:5px;">Email đăng nhập <span style="color:#ef4444;">*</span></label>
                <input type="email" id="sale-form-email" required class="form-control" placeholder="long.le@domain.com" style="width:100%; height:40px; border-radius:6px; border:1px solid #cbd5e1; padding:0 12px;">
              </div>
              <div>
                <label style="display:block; font-size:13px; font-weight:600; margin-bottom:5px;">Số điện thoại</label>
                <input type="tel" id="sale-form-phone" class="form-control" placeholder="0908889999" style="width:100%; height:40px; border-radius:6px; border:1px solid #cbd5e1; padding:0 12px;">
              </div>
            </div>

            <div id="sale-form-password-group" style="margin-bottom: 14px;">
              <label style="display:block; font-size:13px; font-weight:600; margin-bottom:5px;">
                Mật khẩu đăng nhập <span id="sale-form-password-req" style="color:#ef4444;">*</span>
              </label>
              <input type="password" id="sale-form-password" class="form-control" placeholder="Tối thiểu 8 ký tự" style="width:100%; height:40px; border-radius:6px; border:1px solid #cbd5e1; padding:0 12px;">
              <small id="sale-form-password-hint" style="color:#64748b; font-size:11.5px; display:none; margin-top:4px;">Để trống nếu không đổi mật khẩu.</small>
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

            <div style="margin-bottom: 14px;">
              <label style="display:block; font-size:13px; font-weight:600; margin-bottom:5px;">Trạng thái làm việc</label>
              <select id="sale-form-status" class="form-control" style="width:100%; height:40px; border-radius:6px; border:1px solid #cbd5e1; padding:0 12px;">
                <option value="ACTIVE">Hoạt động (ACTIVE - Nhận Lead)</option>
                <option value="LOCKED">Tạm ngưng (LOCKED - Loại khỏi vòng phân bổ)</option>
                <option value="PENDING">Chờ kích hoạt (PENDING)</option>
              </select>
            </div>

            <div style="margin-bottom: 20px;">
              <label style="display:block; font-size:13px; font-weight:600; margin-bottom:6px;">Phân công tòa nhà phụ trách kinh doanh</label>
              <div id="sale-form-buildings" class="building-select-grid">
                <span style="color:#94a3b8; font-size:12px;"><i class="fa-solid fa-spinner fa-spin"></i> Đang tải danh sách tòa nhà...</span>
              </div>
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
                <input type="text" id="pm-form-code" class="form-control" placeholder="VD: PM-002" style="width:100%; height:40px; border-radius:6px; border:1px solid #cbd5e1; padding:0 12px;">
              </div>
              <div>
                <label style="display:block; font-size:13px; font-weight:600; margin-bottom:5px;">Họ và tên <span style="color:#ef4444;">*</span></label>
                <input type="text" id="pm-form-fullname" required class="form-control" placeholder="VD: Đỗ Minh Quang" style="width:100%; height:40px; border-radius:6px; border:1px solid #cbd5e1; padding:0 12px;">
              </div>
            </div>

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:12px; margin-bottom: 14px;">
              <div>
                <label style="display:block; font-size:13px; font-weight:600; margin-bottom:5px;">Email đăng nhập <span style="color:#ef4444;">*</span></label>
                <input type="email" id="pm-form-email" required class="form-control" placeholder="pm.quang@domain.com" style="width:100%; height:40px; border-radius:6px; border:1px solid #cbd5e1; padding:0 12px;">
              </div>
              <div>
                <label style="display:block; font-size:13px; font-weight:600; margin-bottom:5px;">Số điện thoại di động</label>
                <input type="tel" id="pm-form-phone" class="form-control" placeholder="0912345678" style="width:100%; height:40px; border-radius:6px; border:1px solid #cbd5e1; padding:0 12px;">
              </div>
            </div>

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:12px; margin-bottom: 14px;">
              <div>
                <label style="display:block; font-size:13px; font-weight:600; margin-bottom:5px;">Hotline sự cố khẩn cấp (24/7)</label>
                <input type="tel" id="pm-form-hotline" class="form-control" placeholder="VD: 0909000111" style="width:100%; height:40px; border-radius:6px; border:1px solid #cbd5e1; padding:0 12px;">
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
              <label style="display:block; font-size:13px; font-weight:600; margin-bottom:5px;">
                Mật khẩu đăng nhập <span id="pm-form-password-req" style="color:#ef4444;">*</span>
              </label>
              <input type="password" id="pm-form-password" class="form-control" placeholder="Tối thiểu 8 ký tự" style="width:100%; height:40px; border-radius:6px; border:1px solid #cbd5e1; padding:0 12px;">
              <small id="pm-form-password-hint" style="color:#64748b; font-size:11.5px; display:none; margin-top:4px;">Để trống nếu không đổi mật khẩu.</small>
            </div>

            <div style="margin-bottom: 14px;">
              <label style="display:block; font-size:13px; font-weight:600; margin-bottom:5px;">Trạng thái</label>
              <select id="pm-form-status" class="form-control" style="width:100%; height:40px; border-radius:6px; border:1px solid #cbd5e1; padding:0 12px;">
                <option value="ACTIVE">Hoạt động (ACTIVE)</option>
                <option value="LOCKED">Tạm khóa (LOCKED)</option>
                <option value="PENDING">Chờ kích hoạt (PENDING)</option>
              </select>
            </div>

            <div style="margin-bottom: 20px;">
              <label style="display:block; font-size:13px; font-weight:600; margin-bottom:6px;">Phân công tòa nhà quản lý & tiếp nhận sự cố</label>
              <div id="pm-form-buildings" class="building-select-grid">
                <span style="color:#94a3b8; font-size:12px;"><i class="fa-solid fa-spinner fa-spin"></i> Đang tải danh sách tòa nhà...</span>
              </div>
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
                <input type="text" id="acc-form-code" class="form-control" placeholder="VD: ACC-001" style="width:100%; height:40px; border-radius:6px; border:1px solid #cbd5e1; padding:0 12px;">
              </div>
              <div>
                <label style="display:block; font-size:13px; font-weight:600; margin-bottom:5px;">Họ và tên <span style="color:#ef4444;">*</span></label>
                <input type="text" id="acc-form-fullname" required class="form-control" placeholder="VD: Trần Thị Mai" style="width:100%; height:40px; border-radius:6px; border:1px solid #cbd5e1; padding:0 12px;">
              </div>
            </div>

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:12px; margin-bottom: 14px;">
              <div>
                <label style="display:block; font-size:13px; font-weight:600; margin-bottom:5px;">Email đăng nhập <span style="color:#ef4444;">*</span></label>
                <input type="email" id="acc-form-email" required class="form-control" placeholder="mai.tran@domain.com" style="width:100%; height:40px; border-radius:6px; border:1px solid #cbd5e1; padding:0 12px;">
              </div>
              <div>
                <label style="display:block; font-size:13px; font-weight:600; margin-bottom:5px;">Số điện thoại</label>
                <input type="tel" id="acc-form-phone" class="form-control" placeholder="0933334444" style="width:100%; height:40px; border-radius:6px; border:1px solid #cbd5e1; padding:0 12px;">
              </div>
            </div>

            <div id="acc-form-password-group" style="margin-bottom: 14px;">
              <label style="display:block; font-size:13px; font-weight:600; margin-bottom:5px;">
                Mật khẩu đăng nhập <span id="acc-form-password-req" style="color:#ef4444;">*</span>
              </label>
              <input type="password" id="acc-form-password" class="form-control" placeholder="Tối thiểu 8 ký tự" style="width:100%; height:40px; border-radius:6px; border:1px solid #cbd5e1; padding:0 12px;">
              <small id="acc-form-password-hint" style="color:#64748b; font-size:11.5px; display:none; margin-top:4px;">Để trống nếu không đổi mật khẩu.</small>
            </div>

            <div style="margin-bottom: 14px; background:#ecfdf5; padding:12px; border-radius:8px; border:1px solid #a7f3d0;">
              <label style="display:flex; align-items:center; gap:8px; font-size:13px; font-weight:600; cursor:pointer; color:#065f46;">
                <input type="checkbox" id="acc-form-can-adjust" style="accent-color:#059669; width:16px; height:16px;">
                <span>Cho phép phê duyệt phiếu điều chỉnh cước (can_approve_adjustment)</span>
              </label>
            </div>

            <div style="margin-bottom: 14px;">
              <label style="display:block; font-size:13px; font-weight:600; margin-bottom:5px;">Trạng thái</label>
              <select id="acc-form-status" class="form-control" style="width:100%; height:40px; border-radius:6px; border:1px solid #cbd5e1; padding:0 12px;">
                <option value="ACTIVE">Hoạt động (ACTIVE)</option>
                <option value="LOCKED">Tạm khóa (LOCKED)</option>
                <option value="PENDING">Chờ kích hoạt (PENDING)</option>
              </select>
            </div>

            <div style="margin-bottom: 20px;">
              <label style="display:block; font-size:13px; font-weight:600; margin-bottom:6px;">Phân công tòa nhà theo dõi công nợ</label>
              <div id="acc-form-buildings" class="building-select-grid">
                <span style="color:#94a3b8; font-size:12px;"><i class="fa-solid fa-spinner fa-spin"></i> Đang tải danh sách tòa nhà...</span>
              </div>
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
