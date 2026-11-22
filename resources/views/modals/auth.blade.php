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

          <!-- Main Login Form (Validation 0ms) -->
          <form id="auth-login-form" novalidate onsubmit="handleLoginSubmit(event)">
            <!-- Email Field -->
            <div style="margin-bottom:12px;">
              <label for="login-email" style="display:block; font-size:12.5px; font-weight:600; color:#334155; margin-bottom:6px;">Địa chỉ Email đăng nhập <span style="color:#ef4444;">*</span></label>
              <div style="position:relative;">
                <i class="fa-solid fa-envelope" style="position:absolute; left:14px; top:50%; transform:translateY(-50%); color:#94a3b8; font-size:14px; pointer-events:none;"></i>
                <input type="email" id="login-email" style="width:100%; padding:10px 42px 10px 38px; border:1px solid #cbd5e1; border-radius:8px; font-size:13.5px; box-sizing:border-box; transition:all 0.2s;" placeholder="name@company.com" autocomplete="email" oninput="moClearFieldError('login-email', 'err-login-email')">
              </div>
              <div class="field-error-slot" id="err-login-email" style="display:none; color:#ef4444; font-size:11.5px; margin-top:4px; gap:4px; align-items:center;"></div>
            </div>

            <!-- Password Field -->
            <div style="margin-bottom:10px;">
              <label for="login-password" style="display:block; font-size:12.5px; font-weight:600; color:#334155; margin-bottom:6px;">Mật khẩu <span style="color:#ef4444;">*</span></label>
              <div style="position:relative;">
                <i class="fa-solid fa-lock" style="position:absolute; left:14px; top:50%; transform:translateY(-50%); color:#94a3b8; font-size:14px; pointer-events:none;"></i>
                <input type="password" id="login-password" style="width:100%; padding:10px 42px 10px 38px; border:1px solid #cbd5e1; border-radius:8px; font-size:13.5px; box-sizing:border-box; transition:all 0.2s;" placeholder="••••••••" autocomplete="current-password" oninput="moClearFieldError('login-password', 'err-login-password')">
                <button type="button" class="toggle-pwd-btn" onclick="moTogglePasswordVisibility('login-password', this)" style="position:absolute; right:12px; top:50%; transform:translateY(-50%); background:none; border:none; color:#94a3b8; cursor:pointer; font-size:14px; padding:4px;" title="Ẩn/Hiện mật khẩu">
                  <i class="fa-regular fa-eye" id="pwd-eye-icon"></i>
                </button>
              </div>
              <div class="field-error-slot" id="err-login-password" style="display:none; color:#ef4444; font-size:11.5px; margin-top:4px; gap:4px; align-items:center;"></div>
            </div>

            <!-- Forgot Password Link -->
            <div style="display:flex; justify-content:flex-end; margin-top:-6px; margin-bottom:14px;">
              <a href="javascript:void(0)" onclick="openForgotPasswordModal()" style="font-size:12.5px; color:#2563eb; font-weight:600; text-decoration:none;">
                <i class="fa-solid fa-key" style="font-size:11px; margin-right:4px;"></i>Quên mật khẩu?
              </a>
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
            <p style="color:#64748b; font-size:12.5px; margin-bottom:14px; line-height:1.5;">
              Để đảm bảo an toàn tài khoản, vui lòng đặt mật khẩu có độ dài tối thiểu 8 ký tự, bao gồm chữ hoa, chữ thường, số và ký tự đặc biệt.
            </p>

            <form id="change-password-form" onsubmit="handleChangePasswordSubmit(event)" novalidate>
              <!-- 1. Mật khẩu hiện tại -->
              <div class="pwd-field-group" style="margin-bottom:12px;">
                <label for="pwd-current" style="display:block; font-size:12.5px; font-weight:600; color:#334155; margin-bottom:5px;">
                  Mật khẩu hiện tại <span style="color:#ef4444;">*</span>
                </label>
                <div style="position:relative;">
                  <i class="fa-solid fa-lock" style="position:absolute; left:14px; top:50%; transform:translateY(-50%); color:#94a3b8; font-size:14px; pointer-events:none;"></i>
                  <input type="password" 
                         id="pwd-current" 
                         style="width:100%; padding:10px 42px 10px 38px; border:1px solid #cbd5e1; border-radius:8px; font-size:13.5px; transition:all 0.2s; box-sizing:border-box;"
                         placeholder="Nhập mật khẩu đang sử dụng" 
                         autocomplete="current-password"
                         oninput="handleCurrentPwdInput(this)"
                         onblur="handleCurrentPwdBlur(this)">
                  <button type="button" class="toggle-pwd-btn" onclick="toggleFieldPasswordVisibility('pwd-current', this)" style="position:absolute; right:12px; top:50%; transform:translateY(-50%); background:none; border:none; color:#94a3b8; cursor:pointer; font-size:14px; padding:4px;" title="Ẩn/Hiện mật khẩu">
                    <i class="fa-regular fa-eye"></i>
                  </button>
                </div>
                <div class="field-error-slot" id="err-pwd-current" style="display:none; min-height:18px; font-size:11.5px; line-height:18px; color:#ef4444; margin-top:3px;"></div>
              </div>

              <!-- 2. Mật khẩu mới -->
              <div class="pwd-field-group" style="margin-bottom:12px;">
                <label for="pwd-new" style="display:block; font-size:12.5px; font-weight:600; color:#334155; margin-bottom:5px;">
                  Mật khẩu mới <span style="color:#ef4444;">*</span>
                </label>
                <div style="position:relative;">
                  <i class="fa-solid fa-key" style="position:absolute; left:14px; top:50%; transform:translateY(-50%); color:#94a3b8; font-size:14px; pointer-events:none;"></i>
                  <input type="password" 
                         id="pwd-new" 
                         style="width:100%; padding:10px 42px 10px 38px; border:1px solid #cbd5e1; border-radius:8px; font-size:13.5px; transition:all 0.2s; box-sizing:border-box;"
                         placeholder="Tối thiểu 8 ký tự (hoa, thường, số, ký tự đặc biệt)" 
                         autocomplete="new-password"
                         oninput="handleNewPwdInput(this)"
                         onblur="handleNewPwdBlur(this)">
                  <button type="button" class="toggle-pwd-btn" onclick="toggleFieldPasswordVisibility('pwd-new', this)" style="position:absolute; right:12px; top:50%; transform:translateY(-50%); background:none; border:none; color:#94a3b8; cursor:pointer; font-size:14px; padding:4px;" title="Ẩn/Hiện mật khẩu">
                    <i class="fa-regular fa-eye"></i>
                  </button>
                </div>
                <div class="field-error-slot" id="err-pwd-new" style="display:none; min-height:18px; font-size:11.5px; line-height:18px; color:#ef4444; margin-top:3px;"></div>

                <!-- Password Strength Meter & Real-time Checklist -->
                <div id="pwd-strength-container" style="margin-top:8px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:10px 12px;">
                  <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                    <span style="font-size:11.5px; color:#64748b; font-weight:600;">Độ mạnh mật khẩu:</span>
                    <span id="pwd-strength-label" style="font-size:11.5px; font-weight:700; color:#94a3b8;">Chưa nhập</span>
                  </div>
                  <!-- Thanh tiến trình độ mạnh 4 nấc -->
                  <div style="height:5px; background:#e2e8f0; border-radius:4px; overflow:hidden; margin-bottom:8px; display:flex; gap:3px;">
                    <div id="pwd-bar-1" style="flex:1; height:100%; background:#e2e8f0; border-radius:2px; transition:background 0.2s;"></div>
                    <div id="pwd-bar-2" style="flex:1; height:100%; background:#e2e8f0; border-radius:2px; transition:background 0.2s;"></div>
                    <div id="pwd-bar-3" style="flex:1; height:100%; background:#e2e8f0; border-radius:2px; transition:background 0.2s;"></div>
                    <div id="pwd-bar-4" style="flex:1; height:100%; background:#e2e8f0; border-radius:2px; transition:background 0.2s;"></div>
                  </div>
                  <!-- Checklist 4 tiêu chuẩn -->
                  <div style="display:grid; grid-template-columns:1fr 1fr; gap:4px 8px; font-size:11.5px;">
                    <div id="rule-len" style="color:#94a3b8; display:flex; align-items:center; gap:5px; transition:color 0.2s;">
                      <i class="fa-solid fa-circle-xmark rule-icon" style="font-size:11px;"></i> Ít nhất 8 ký tự
                    </div>
                    <div id="rule-case" style="color:#94a3b8; display:flex; align-items:center; gap:5px; transition:color 0.2s;">
                      <i class="fa-solid fa-circle-xmark rule-icon" style="font-size:11px;"></i> Chữ hoa & thường
                    </div>
                    <div id="rule-num" style="color:#94a3b8; display:flex; align-items:center; gap:5px; transition:color 0.2s;">
                      <i class="fa-solid fa-circle-xmark rule-icon" style="font-size:11px;"></i> Chứa ít nhất 1 số
                    </div>
                    <div id="rule-sym" style="color:#94a3b8; display:flex; align-items:center; gap:5px; transition:color 0.2s;">
                      <i class="fa-solid fa-circle-xmark rule-icon" style="font-size:11px;"></i> Ký tự đặc biệt (!@#...)
                    </div>
                  </div>
                </div>
              </div>

              <!-- 3. Xác nhận mật khẩu mới -->
              <div class="pwd-field-group" style="margin-bottom:14px;">
                <label for="pwd-confirm" style="display:block; font-size:12.5px; font-weight:600; color:#334155; margin-bottom:5px;">
                  Xác nhận mật khẩu mới <span style="color:#ef4444;">*</span>
                </label>
                <div style="position:relative;">
                  <i class="fa-solid fa-check-double" style="position:absolute; left:14px; top:50%; transform:translateY(-50%); color:#94a3b8; font-size:14px; pointer-events:none;"></i>
                  <input type="password" 
                         id="pwd-confirm" 
                         style="width:100%; padding:10px 42px 10px 38px; border:1px solid #cbd5e1; border-radius:8px; font-size:13.5px; transition:all 0.2s; box-sizing:border-box;"
                         placeholder="Nhập lại mật khẩu mới" 
                         autocomplete="new-password"
                         oninput="handleConfirmPwdInput(this)"
                         onblur="handleConfirmPwdBlur(this)">
                  <button type="button" class="toggle-pwd-btn" onclick="toggleFieldPasswordVisibility('pwd-confirm', this)" style="position:absolute; right:12px; top:50%; transform:translateY(-50%); background:none; border:none; color:#94a3b8; cursor:pointer; font-size:14px; padding:4px;" title="Ẩn/Hiện mật khẩu">
                    <i class="fa-regular fa-eye"></i>
                  </button>
                </div>
                <div class="field-error-slot" id="err-pwd-confirm" style="display:none; min-height:18px; font-size:11.5px; line-height:18px; color:#ef4444; margin-top:3px;"></div>
              </div>

              <!-- Alert tổng quát -->
              <div id="change-pwd-alert" style="display:none; padding:10px 14px; border-radius:8px; font-size:12.5px; margin-bottom:14px;"></div>

              <button type="submit" id="btn-submit-change-pwd" class="btn-login-submit" style="background:linear-gradient(135deg,#0f3361,#2563eb); font-weight:600; padding:11px; font-size:14px; border-radius:8px; display:flex; align-items:center; justify-content:center; gap:8px;">
                <i class="fa-solid fa-floppy-disk"></i> Cập Nhật Mật Khẩu Mới
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>

    <!-- Forgot Password Modal (Khôi Phục Mật Khẩu Tenant) -->
    <div id="forgot-password-modal" class="mo-modal">
      <div class="login-modal-box">
        <div class="login-modal-header">
          <div class="login-header-badge">
            <i class="fa-solid fa-shield-halved"></i> Khôi Phục Tài Khoản
          </div>
          <h3 class="login-modal-title">Quên Mật Khẩu Khách Thuê</h3>
          <p class="login-modal-sub">Nhập email hoặc mã khách thuê để nhận liên kết khôi phục</p>
          <div class="login-modal-close" onclick="closeForgotPasswordModal()">
            <i class="fa-solid fa-xmark"></i>
          </div>
        </div>

        <div class="login-modal-body">
          <form id="forgot-password-form" novalidate onsubmit="handleForgotPasswordSubmit(event)">
            <div style="margin-bottom:12px;">
              <label for="forgot-email" style="display:block; font-size:12.5px; font-weight:600; color:#334155; margin-bottom:6px;">Địa chỉ Email hoặc Mã khách thuê <span style="color:#ef4444;">*</span></label>
              <div style="position:relative;">
                <i class="fa-solid fa-envelope" style="position:absolute; left:14px; top:50%; transform:translateY(-50%); color:#94a3b8; font-size:14px; pointer-events:none;"></i>
                <input type="text" id="forgot-email" style="width:100%; padding:10px 42px 10px 38px; border:1px solid #cbd5e1; border-radius:8px; font-size:13.5px; box-sizing:border-box; transition:all 0.2s;" placeholder="name@company.com hoặc TNT-..." autocomplete="email" oninput="moClearFieldError('forgot-email', 'err-forgot-email')">
              </div>
              <div class="field-error-slot" id="err-forgot-email" style="display:none; color:#ef4444; font-size:11.5px; margin-top:4px; gap:4px; align-items:center;"></div>
            </div>

            <div id="forgot-error-alert" style="display:none; background:#fef2f2; border:1px solid #fecaca; color:#b91c1c; padding:10px 14px; border-radius:8px; font-size:12.5px; margin-bottom:14px;">
              <i class="fa-solid fa-circle-exclamation"></i>
              <span id="forgot-error-text">Lỗi khôi phục</span>
            </div>

            <div id="forgot-success-alert" style="display:none; background:#ecfdf5; border:1px solid #a7f3d0; color:#047857; padding:10px 14px; border-radius:8px; font-size:12.5px; margin-bottom:14px;">
              <i class="fa-solid fa-circle-check"></i>
              <span id="forgot-success-text">Liên kết khôi phục đã được gửi vào email!</span>
            </div>

            <button type="submit" id="btn-submit-forgot" class="btn-login-submit" style="background:linear-gradient(135deg,#0f3361,#2563eb);">
              <i class="fa-solid fa-paper-plane"></i> Gửi Yêu Cầu Khôi Phục
            </button>

            <div style="text-align:center; margin-top:14px;">
              <a href="javascript:void(0)" onclick="closeForgotPasswordModal(); openLoginModal();" style="font-size:13px; color:#475569; text-decoration:none; font-weight:600;">
                <i class="fa-solid fa-arrow-left"></i> Quay lại Đăng nhập
              </a>
            </div>
          </form>
        </div>
      </div>
    </div>
