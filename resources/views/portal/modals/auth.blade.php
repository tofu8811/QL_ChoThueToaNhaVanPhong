<!-- Auth Login Modal (Xác Thực Đa Tác Nhân) -->
    <div id="login-modal" class="mo-modal">
      <div class="login-modal-box">
        <!-- Header Sang Trọng -->
        <div class="login-modal-header">
          <div class="login-header-badge">
            <i class="fa-solid fa-shield-halved"></i> Cổng Đăng Nhập Hệ Thống
          </div>
          <h3 class="login-modal-title">Đăng Nhập Hệ Thống Quản Trị</h3>
          <p class="login-modal-sub">Truy cập không gian làm việc số theo vai trò của bạn</p>
          <div class="login-modal-close" onclick="closeLoginModal()">
            <i class="fa-solid fa-xmark"></i>
          </div>
        </div>

        <!-- Body Modal -->
        <div class="login-modal-body">
          <!-- Chọn nhanh vai trò thử nghiệm (Quick 1-Click) -->
          <div style="margin-bottom: 6px;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 8px;">
              <span style="font-size:12px; font-weight:700; color:#475569; text-transform:uppercase; letter-spacing:0.4px;">
                <i class="fa-solid fa-bolt" style="color:#f59e0b;"></i> Chọn nhanh vai trò dùng thử:
              </span>
              <span style="font-size:11px; color:#2563eb; font-weight:600; cursor:pointer;" onclick="selectSampleRole('admin', document.getElementById('chip-role-admin'), true)">⚡ 1-Click Đăng nhập</span>
            </div>

            <div class="sample-roles-grid">
              <!-- Role 1: Super Admin -->
              <div class="sample-role-pill" id="chip-role-admin" onclick="selectSampleRole('admin', this)" ondblclick="selectSampleRole('admin', this, true)">
                <div class="sample-role-icon" style="background:#dbeafe; color:#1d4ed8;">
                  <i class="fa-solid fa-crown"></i>
                </div>
                <div class="sample-role-meta" style="flex:1;">
                  <strong>Super Admin</strong>
                  <small>Toàn quyền hệ thống</small>
                </div>
                <button type="button" class="btn-pill-direct-login" onclick="event.stopPropagation(); selectSampleRole('admin', document.getElementById('chip-role-admin'), true);" title="Đăng nhập ngay">
                  Vào ngay
                </button>
              </div>

              <!-- Role 2: Ban Quản Lý (PM) -->
              <div class="sample-role-pill" id="chip-role-pm" onclick="selectSampleRole('pm', this)" ondblclick="selectSampleRole('pm', this, true)">
                <div class="sample-role-icon" style="background:#dcfce7; color:#15803d;">
                  <i class="fa-solid fa-building-user"></i>
                </div>
                <div class="sample-role-meta" style="flex:1;">
                  <strong>Ban Quản Lý (PM)</strong>
                  <small>Vận hành tòa nhà</small>
                </div>
                <button type="button" class="btn-pill-direct-login" onclick="event.stopPropagation(); selectSampleRole('pm', document.getElementById('chip-role-pm'), true);" title="Đăng nhập ngay">
                  Vào ngay
                </button>
              </div>

              <!-- Role 3: Sales / Môi Giới -->
              <div class="sample-role-pill" id="chip-role-sales" onclick="selectSampleRole('sales', this)" ondblclick="selectSampleRole('sales', this, true)">
                <div class="sample-role-icon" style="background:#ffedd5; color:#c2410c;">
                  <i class="fa-solid fa-briefcase"></i>
                </div>
                <div class="sample-role-meta" style="flex:1;">
                  <strong>Chuyên Viên Sales</strong>
                  <small>Leads & Báo giá</small>
                </div>
                <button type="button" class="btn-pill-direct-login" onclick="event.stopPropagation(); selectSampleRole('sales', document.getElementById('chip-role-sales'), true);" title="Đăng nhập ngay">
                  Vào ngay
                </button>
              </div>

              <!-- Role 4: Kế Toán Công Nợ -->
              <div class="sample-role-pill" id="chip-role-accountant" onclick="selectSampleRole('accountant', this)" ondblclick="selectSampleRole('accountant', this, true)">
                <div class="sample-role-icon" style="background:#d1fae5; color:#047857;">
                  <i class="fa-solid fa-file-invoice-dollar"></i>
                </div>
                <div class="sample-role-meta" style="flex:1;">
                  <strong>Kế Toán (Acc)</strong>
                  <small>Công nợ & Thanh toán</small>
                </div>
                <button type="button" class="btn-pill-direct-login" onclick="event.stopPropagation(); selectSampleRole('accountant', document.getElementById('chip-role-accountant'), true);" title="Đăng nhập ngay">
                  Vào ngay
                </button>
              </div>

              <!-- Role 5: Doanh Nghiệp Khách Thuê -->
              <div class="sample-role-pill" id="chip-role-tenant" style="grid-column: 1 / -1;" onclick="selectSampleRole('tenant', this)" ondblclick="selectSampleRole('tenant', this, true)">
                <div class="sample-role-icon" style="background:#ede9fe; color:#6d28d9;">
                  <i class="fa-solid fa-building"></i>
                </div>
                <div class="sample-role-meta" style="flex:1;">
                  <strong>Khách Thuê Doanh Nghiệp (Tenant)</strong>
                  <small>Cty Demo Tech — Hợp đồng, Ticket & Hóa đơn</small>
                </div>
                <button type="button" class="btn-pill-direct-login" onclick="event.stopPropagation(); selectSampleRole('tenant', document.getElementById('chip-role-tenant'), true);" title="Đăng nhập ngay">
                  Vào ngay
                </button>
              </div>
            </div>
          </div>

          <div class="login-divider-text">
            <span>Hoặc nhập tài khoản</span>
          </div>

          <!-- Main Login Form -->
          <form id="auth-login-form" onsubmit="handleLoginSubmit(event)">
            <!-- Email Field -->
            <div class="input-icon-wrapper">
              <label for="login-email">Địa chỉ Email đăng nhập *</label>
              <i class="fa-solid fa-envelope field-icon"></i>
              <input type="email" id="login-email" required placeholder="name@company.com" autocomplete="email">
            </div>

            <!-- Password Field -->
            <div class="input-icon-wrapper">
              <label for="login-password">Mật khẩu *</label>
              <i class="fa-solid fa-lock field-icon"></i>
              <input type="password" id="login-password" required placeholder="••••••••" autocomplete="current-password">
              <button type="button" class="toggle-pwd-btn" onclick="togglePasswordVisibility()" title="Ẩn/Hiện mật khẩu">
                <i class="fa-regular fa-eye" id="pwd-eye-icon"></i>
              </button>
            </div>

            <!-- Error Box -->
            <div id="login-error-alert" style="display:none; background:#fef2f2; border:1px solid #fecaca; color:#b91c1c; padding:10px 14px; border-radius:8px; font-size:12.5px; margin-bottom:14px;">
              <i class="fa-solid fa-circle-exclamation"></i>
              <span id="login-error-text">Thông tin đăng nhập không hợp lệ</span>
            </div>

            <!-- Submit Button -->
            <button type="submit" id="btn-submit-login" class="btn-login-submit">
              <i class="fa-solid fa-right-to-bracket"></i> Đăng Nhập Không Gian Làm Việc
            </button>
          </form>
        </div>
      </div>
    </div>

<!-- User Profile & Security Modal (Đổi Mật Khẩu & Bảo Mật - Endpoints 70, 71) -->
    <div id="profile-modal" class="mo-modal">
      <div class="profile-modal-box">
        <!-- Header -->
        <div class="login-modal-header" style="padding: 20px 24px 14px;">
          <div style="display:flex; justify-content:space-between; align-items:center;">
            <div>
              <div class="login-header-badge">
                <i class="fa-solid fa-shield-halved"></i> Quản Trị Tài Khoản
              </div>
              <h3 class="login-modal-title" style="font-size:18px;">Đổi Mật Khẩu & Bảo Mật Tài Khoản</h3>
            </div>
            <div class="login-modal-close" onclick="closeProfileModal()">
              <i class="fa-solid fa-xmark"></i>
            </div>
          </div>

          <!-- Tab Navigation -->
          <div class="profile-tabs-header">
            <button class="profile-tab-btn active" id="btn-tab-profile-password" onclick="switchProfileModalTab('tab-profile-password', this)">
              <i class="fa-solid fa-key"></i> Đổi Mật Khẩu Cá Nhân
            </button>
          </div>
        </div>

        <!-- Body -->
        <div class="profile-modal-body">
          <!-- TAB: ĐỔI MẬT KHẨU CÁ NHÂN (Endpoint 71: PUT /api/v1/auth/password) -->
          <div id="tab-profile-password" class="profile-tab-pane active" style="display:block;">
            <p style="color:#64748b; font-size:12.5px; margin-bottom:16px;">
              Để đảm bảo an toàn tài khoản, vui lòng đặt mật khẩu có độ dài tối thiểu 8 ký tự, bao gồm chữ hoa, chữ thường, số và ký tự đặc biệt.
            </p>

            <form id="change-password-form" onsubmit="handleChangePasswordSubmit(event)">
              <div class="input-icon-wrapper">
                <label for="pwd-current">Mật khẩu hiện tại *</label>
                <i class="fa-solid fa-lock field-icon"></i>
                <input type="password" id="pwd-current" required placeholder="Nhập mật khẩu đang sử dụng" autocomplete="current-password">
              </div>

              <div class="input-icon-wrapper">
                <label for="pwd-new">Mật khẩu mới *</label>
                <i class="fa-solid fa-key field-icon"></i>
                <input type="password" id="pwd-new" required placeholder="Tối thiểu 8 ký tự (hoa, thường, số, ký tự đặc biệt)" autocomplete="new-password">
              </div>

              <div class="input-icon-wrapper">
                <label for="pwd-confirm">Xác nhận mật khẩu mới *</label>
                <i class="fa-solid fa-check-double field-icon"></i>
                <input type="password" id="pwd-confirm" required placeholder="Nhập lại mật khẩu mới" autocomplete="new-password">
              </div>

              <div id="change-pwd-alert" style="display:none; padding:10px 14px; border-radius:8px; font-size:12.5px; margin-bottom:14px;"></div>

              <button type="submit" id="btn-submit-change-pwd" class="btn-login-submit" style="background:linear-gradient(135deg,#0f3361,#2563eb);">
                <i class="fa-solid fa-floppy-disk"></i> Cập Nhật Mật Khẩu Mới
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>
