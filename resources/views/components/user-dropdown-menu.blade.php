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
    <a href="#" class="user-dropdown-item" onclick="openProfileModal('tab-profile-password'); closeUserDropdown(); return false;">
      <i class="fa-solid fa-key" style="color:#f59e0b;"></i> Đổi Mật Khẩu Cá Nhân
    </a>
    <div class="user-dropdown-divider"></div>
    <a href="#" class="user-dropdown-item" id="dropdown-workspace-link" onclick="if (typeof handleGoToWorkspace === 'function') { handleGoToWorkspace(); } else { window.location.href = '{{ route('workspace.redirect') }}'; } closeUserDropdown(); return false;">
      <i class="fa-solid fa-desktop" style="color:#6366f1;"></i> <span id="dropdown-workspace-text">Không Gian Làm Việc Số</span>
    </a>
    <a href="/filament" target="_blank" class="user-dropdown-item" id="dropdown-filament-link" style="display:none;">
      <i class="fa-solid fa-gear" style="color:#ea580c;"></i> Quản Trị Filament Gốc
    </a>
    <div class="user-dropdown-divider"></div>
    <a href="#" class="user-dropdown-item text-danger" onclick="event.preventDefault(); handleLogout(); closeUserDropdown(); return false;">
      <i class="fa-solid fa-arrow-right-from-bracket" style="color:#dc2626;"></i> Đăng Xuất (Thoát)
    </a>
  </div>
</div>
