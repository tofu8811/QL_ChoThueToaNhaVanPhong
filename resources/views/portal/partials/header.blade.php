    <!-- Dòng kẻ xanh dương trên cùng -->
    <div class="mo-topbar"></div>

    <!-- Main Header -->
    <header class="mo-header">
      <div class="container">
        <div class="mo-logo" onclick="showPublicPage('page-home')" style="cursor: pointer;">
          <div class="mo-logo-icon">
            <i class="fa-solid fa-building-wheat"></i>
          </div>
          <div class="mo-logo-text">
            <h1>Office</h1>
          </div>
        </div>

        <nav class="mo-nav">
          <div class="mo-nav-item active" onclick="showPublicPage('page-home')">Cho Thuê VP TP.HCM</div>
          <div class="mo-nav-item" onclick="filterByDistrict('Quận 1')">Văn Phòng Quận 1</div>
          <div class="mo-nav-item" onclick="filterByDistrict('Quận 3')">Văn Phòng Quận 3</div>
          <div class="mo-nav-item" onclick="filterByGrade('Hạng A')">Tòa Nhà Hạng A</div>
          <div class="mo-nav-item" onclick="showPublicPage('page-quote')">Nhận Báo Giá</div>
        </nav>

        <div class="mo-header-actions">
          <button class="btn-mo-orange" onclick="openQuoteModal()">
            <i class="fa-solid fa-file-invoice-dollar"></i> Báo Giá Nhanh
          </button>
          
          <button class="btn-mo-outline" id="btn-workspace-nav" style="display:none;" onclick="handleGoToWorkspace()">
            <i class="fa-solid fa-desktop"></i> <span id="btn-workspace-text">Không Gian Làm Việc</span>
          </button>

          <!-- Nút Đăng Nhập / Thông tin User Auth -->
          <div id="auth-actions-container" style="display: inline-flex; align-items: center; position: relative;">
            <!-- Chưa đăng nhập: Nút mở Modal Login -->
            <button id="btn-open-login" class="btn-mo-primary" style="background:#0f3361; color:#fff; padding:7px 14px; border-radius:6px; font-weight:700; font-size:13px; white-space: nowrap; border: 1px solid #2563eb; box-shadow: 0 2px 8px rgba(37, 99, 235, 0.25);" onclick="openLoginModal()">
              <i class="fa-solid fa-right-to-bracket"></i> Đăng Nhập
            </button>

            <!-- Đã đăng nhập: User Profile Pill với Dropdown Menu -->
            <div id="user-logged-box" style="display: none; position: relative;">
              <div id="user-profile-trigger" class="user-profile-pill" onclick="toggleUserDropdown(event)">
                <div class="user-avatar-circle" id="user-avatar-text">US</div>
                <div class="user-info-text">
                  <span class="user-name-label" id="user-display-name">Người dùng</span>
                  <span class="user-role-tag" id="user-display-role">ROLE</span>
                </div>
                <i class="fa-solid fa-chevron-down user-dropdown-caret"></i>
              </div>

              <!-- User Dropdown Menu -->
              <div id="user-dropdown-menu" class="user-nav-dropdown">
                <div class="user-dropdown-header">
                  <div style="font-weight:700; color:#0f3361; font-size:13px;" id="dropdown-full-name">Người dùng</div>
                  <div style="font-size:11.5px; color:#64748b;" id="dropdown-email">user@officeleasing.vn</div>
                  <div style="margin-top:5px;"><span class="badge-role-pill" id="dropdown-role-badge">ROLE</span></div>
                </div>
                <div class="user-dropdown-divider"></div>
                <a href="#" class="user-dropdown-item" onclick="openProfileModal('tab-profile-info'); closeUserDropdown(); return false;">
                  <i class="fa-solid fa-id-card-clip" style="color:#2563eb;"></i> Hồ Sơ & Tòa Nhà Phụ Trách
                </a>
                <a href="#" class="user-dropdown-item" onclick="openProfileModal('tab-profile-password'); closeUserDropdown(); return false;">
                  <i class="fa-solid fa-key" style="color:#f59e0b;"></i> Đổi Mật Khẩu Cá Nhân
                </a>
                <a href="#" class="user-dropdown-item" id="dropdown-session-link" style="display:none;" onclick="openProfileModal('tab-profile-session'); closeUserDropdown(); return false;">
                  <i class="fa-solid fa-shield-halved" style="color:#10b981;"></i> Quản Trị Phiên & Token (Admin)
                </a>
                <div class="user-dropdown-divider"></div>
                <a href="#" class="user-dropdown-item" id="dropdown-workspace-link" onclick="handleGoToWorkspace(); closeUserDropdown(); return false;">
                  <i class="fa-solid fa-desktop" style="color:#6366f1;"></i> <span id="dropdown-workspace-text">Không Gian Làm Việc Số</span>
                </a>
                <a href="/admin" target="_blank" class="user-dropdown-item" id="dropdown-filament-link" style="display:none;">
                  <i class="fa-solid fa-gear" style="color:#ea580c;"></i> Quản Trị Filament Gốc
                </a>
                <div class="user-dropdown-divider"></div>
                <a href="#" class="user-dropdown-item text-danger" onclick="handleLogout(); closeUserDropdown(); return false;">
                  <i class="fa-solid fa-arrow-right-from-bracket" style="color:#dc2626;"></i> Đăng Xuất (Thoát)
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </header>
