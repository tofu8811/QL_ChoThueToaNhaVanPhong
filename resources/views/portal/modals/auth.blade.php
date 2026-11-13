<!-- Auth Login Modal (Xác Thực Đa Tác Nhân) -->
    <div id="login-modal" class="mo-modal">
      <div class="login-modal-box">
        <!-- Header Sang Trọng -->
        <div class="login-modal-header">
          <div class="login-header-badge">
            <i class="fa-solid fa-shield-halved"></i> Hệ Thống Xác Thực Đa Tác Nhân
          </div>
          <h3 class="login-modal-title">Đăng Nhập Hệ Thống Đa Tác Nhân</h3>
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
              <span style="font-size:11px; color:#2563eb; font-weight:600; cursor:pointer;" onclick="selectSampleRole('admin', document.getElementById('chip-role-admin'))">1-Click điền</span>
            </div>

            <div class="sample-roles-grid">
              <!-- Role 1: Super Admin -->
              <div class="sample-role-pill" id="chip-role-admin" onclick="selectSampleRole('admin', this)">
                <div class="sample-role-icon" style="background:#dbeafe; color:#1d4ed8;">
                  <i class="fa-solid fa-crown"></i>
                </div>
                <div class="sample-role-meta">
                  <strong>Super Admin</strong>
                  <small>Toàn quyền</small>
                </div>
              </div>

              <!-- Role 2: Ban Quản Lý (PM) -->
              <div class="sample-role-pill" id="chip-role-pm" onclick="selectSampleRole('pm', this)">
                <div class="sample-role-icon" style="background:#dcfce7; color:#15803d;">
                  <i class="fa-solid fa-building-user"></i>
                </div>
                <div class="sample-role-meta">
                  <strong>Ban Quản Lý (PM)</strong>
                  <small>Vận hành tòa nhà</small>
                </div>
              </div>

              <!-- Role 3: Sales / Môi Giới -->
              <div class="sample-role-pill" id="chip-role-sales" onclick="selectSampleRole('sales', this)">
                <div class="sample-role-icon" style="background:#ffedd5; color:#c2410c;">
                  <i class="fa-solid fa-briefcase"></i>
                </div>
                <div class="sample-role-meta">
                  <strong>Chuyên Viên Sales</strong>
                  <small>Leads & Báo giá</small>
                </div>
              </div>

              <!-- Role 4: Doanh Nghiệp Khách Thuê -->
              <div class="sample-role-pill" id="chip-role-tenant" onclick="selectSampleRole('tenant', this)">
                <div class="sample-role-icon" style="background:#ede9fe; color:#6d28d9;">
                  <i class="fa-solid fa-building"></i>
                </div>
                <div class="sample-role-meta">
                  <strong>Khách Thuê (Tenant)</strong>
                  <small>DN An Khang Tech</small>
                </div>
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

<!-- User Profile & Security Modal (Hồ Sơ Cá Nhân & Đổi Mật Khẩu - Endpoints 69, 70, 71) -->
    <div id="profile-modal" class="mo-modal">
      <div class="profile-modal-box">
        <!-- Header -->
        <div class="login-modal-header" style="padding: 20px 24px 14px;">
          <div style="display:flex; justify-content:space-between; align-items:center;">
            <div>
              <div class="login-header-badge">
                <i class="fa-solid fa-id-card-clip"></i> Quản Trị Tài Khoản
              </div>
              <h3 class="login-modal-title" style="font-size:18px;">Hồ Sơ Cá Nhân & Bảo Mật</h3>
            </div>
            <div class="login-modal-close" onclick="closeProfileModal()">
              <i class="fa-solid fa-xmark"></i>
            </div>
          </div>

          <!-- Tab Navigation -->
          <div class="profile-tabs-header">
            <button class="profile-tab-btn active" id="btn-tab-profile-info" onclick="switchProfileModalTab('tab-profile-info', this)">
              <i class="fa-solid fa-user-gear"></i> Thông Tin & Tòa Nhà
            </button>
            <button class="profile-tab-btn" id="btn-tab-profile-password" onclick="switchProfileModalTab('tab-profile-password', this)">
              <i class="fa-solid fa-key"></i> Đổi Mật Khẩu
            </button>
            <button class="profile-tab-btn" id="btn-tab-profile-session" onclick="switchProfileModalTab('tab-profile-session', this)">
              <i class="fa-solid fa-shield-halved"></i> Phiên Đăng Nhập
            </button>
          </div>
        </div>

        <!-- Body -->
        <div class="profile-modal-body">
          <!-- TAB 1: THÔNG TIN HỒ SƠ & TÒA NHÀ PHỤ TRÁCH (Endpoint 69: GET /api/v1/auth/me) -->
          <div id="tab-profile-info" class="profile-tab-pane active">
            <!-- Loading Indicator -->
            <div id="profile-loading" style="text-align:center; padding:30px 0; color:#64748b;">
              <i class="fa-solid fa-spinner fa-spin" style="font-size:24px; color:#2563eb;"></i>
              <div style="margin-top:8px; font-size:13px;">Đang tải hồ sơ từ máy chủ...</div>
            </div>

            <!-- Profile Content -->
            <div id="profile-content" style="display:none;">
              <!-- User Summary Card -->
              <div class="profile-user-card">
                <div class="profile-big-avatar" id="profile-avatar-display">AD</div>
                <div class="profile-user-details">
                  <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                    <h4 class="profile-user-name" id="profile-name-display">Đang tải...</h4>
                    <span class="badge-role-pill" id="profile-role-badge">ROLE</span>
                    <span class="badge-filament success" style="font-size:11px;" id="profile-status-badge">Đang hoạt động</span>
                  </div>
                  <div class="profile-meta-row">
                    <span><i class="fa-solid fa-envelope"></i> <span id="profile-email-display">-</span></span>
                    <span><i class="fa-solid fa-phone"></i> <span id="profile-phone-display">-</span></span>
                    <span><i class="fa-solid fa-id-badge"></i> <span id="profile-code-display">-</span></span>
                  </div>
                </div>
              </div>

              <!-- Assigned Buildings Section -->
              <div style="margin-top:20px;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                  <h5 style="font-size:13.5px; font-weight:700; color:#0f3361; margin:0;">
                    <i class="fa-solid fa-building-circle-check" style="color:#2563eb;"></i> Danh Sách Tòa Nhà Được Phân Công Phụ Trách
                  </h5>
                  <span style="font-size:12px; color:#64748b;" id="profile-buildings-count">0 tòa nhà</span>
                </div>

                <div class="profile-buildings-grid" id="profile-buildings-list">
                  <!-- Rendered via JS -->
                </div>
              </div>
            </div>
          </div>

          <!-- TAB 2: ĐỔI MẬT KHẨU CÁ NHÂN (Endpoint 71: PUT /api/v1/auth/password) -->
          <div id="tab-profile-password" class="profile-tab-pane" style="display:none;">
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

          <!-- TAB 3: PHIÊN ĐĂNG NHẬP & TOKEN (Endpoint 70: Refresh Token & Endpoint 68: Logout) -->
          <div id="tab-profile-session" class="profile-tab-pane" style="display:none;">
            <div class="session-info-box">
              <div style="display:flex; align-items:center; gap:12px; margin-bottom:14px;">
                <div style="width:40px; height:40px; border-radius:10px; background:#eff6ff; color:#2563eb; display:flex; align-items:center; justify-content:center; font-size:20px;">
                  <i class="fa-solid fa-shield-halved"></i>
                </div>
                <div>
                  <div style="font-weight:700; color:#0f3361; font-size:14px;">Phiên Làm Việc Laravel Sanctum</div>
                  <div style="font-size:12px; color:#64748b;">Mã hóa Bearer Token an toàn theo tiêu chuẩn RESTful API</div>
                </div>
              </div>

              <div style="background:#fff; border:1px solid #e2e8f0; border-radius:8px; padding:12px 14px; font-size:12.5px; margin-bottom:16px;">
                <div style="display:flex; justify-content:space-between; margin-bottom:6px;">
                  <span style="color:#64748b;">Loại Token:</span>
                  <strong style="color:#0f3361;">Bearer Token (Personal Access Token)</strong>
                </div>
                <div style="display:flex; justify-content:space-between; margin-bottom:6px;">
                  <span style="color:#64748b;">Trạng thái phiên:</span>
                  <span class="badge-filament success" style="font-size:10.5px;">Đang hoạt động (Active)</span>
                </div>
                <div style="display:flex; justify-content:space-between;">
                  <span style="color:#64748b;">Thời gian kiểm tra:</span>
                  <span id="session-login-time" style="color:#334155; font-weight:600;">Vừa xong</span>
                </div>
              </div>

              <div id="refresh-token-alert" style="display:none; padding:10px 14px; border-radius:8px; font-size:12.5px; margin-bottom:14px;"></div>

              <div style="display:flex; gap:10px;">
                <button type="button" class="btn-mo-primary" style="flex:1; justify-content:center; background:#2563eb; color:#fff; font-size:13px; padding:10px;" onclick="handleRefreshTokenClick(this)">
                  <i class="fa-solid fa-arrows-rotate"></i> Làm Mới Phiên (Refresh Token)
                </button>
                <button type="button" class="btn-logout" style="justify-content:center; font-size:13px; padding:10px 16px;" onclick="closeProfileModal(); handleLogout();">
                  <i class="fa-solid fa-arrow-right-from-bracket"></i> Đăng Xuất
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
