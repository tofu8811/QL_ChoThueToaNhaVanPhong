<!-- Topbar (Breadcrumb động bên trái + Tìm kiếm + Thông báo + Hồ sơ User bên phải) -->
<header class="filament-topbar">
  <div class="bms-topbar-left" style="display:flex;align-items:center;gap:10px;">
    <!-- Nút Hamburger Menu Sidebar trên màn hình nhỏ -->
    <button type="button" class="bms-mobile-menu-btn" onclick="toggleBmsMobileSidebar()" title="Mở danh mục điều hướng (Sidebar)">
      <i class="fa-solid fa-bars"></i>
    </button>
    <a href="{{ route('home') }}" class="bms-topbar-home-link" style="color:#64748b;text-decoration:none;font-weight:600;" title="Về trang chủ Portal">Cổng thông tin</a>
    <i class="fa-solid fa-chevron-right bms-topbar-sep" style="font-size:9px;color:#cbd5e1;"></i>
    <span class="bms-bc-current" id="topbar-current-breadcrumb">@yield('breadcrumb', 'Tổng quan hệ thống')</span>
  </div>

  <div class="filament-topbar-actions">
    <!-- Ô tìm kiếm nhanh đồng bộ toàn cục trên Topbar -->
    <div style="position:relative;" id="bms-topbar-search-wrapper">
      <div class="filament-search-mock" style="width:280px;padding:6px 30px 6px 12px;background:#f8fafc;position:relative;">
        <i class="fa-solid fa-magnifying-glass" style="color:#94a3b8;font-size:12.5px;"></i>
        <input type="text" id="bms-topbar-search-input" placeholder="Tìm tòa nhà, khách thuê, HĐ, ticket..." oninput="toggleSearchClearBtn(this); handleTopbarQuickSearch(this.value)" onfocus="if(this.value.trim()) handleTopbarQuickSearch(this.value)" style="border:none;background:transparent;outline:none;width:100%;font-size:12.5px;color:#334155;">
        <button type="button" class="bms-search-clear-btn" id="btn-clear-topbar-search" onclick="handleClearSearchInput('bms-topbar-search-input', () => handleTopbarQuickSearch(''))" title="Xóa tìm kiếm">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>
      <!-- Dropdown kết quả tìm kiếm nhanh toàn cục -->
      <div id="bms-topbar-search-results" style="display:none;position:absolute;top:calc(100% + 6px);left:0;width:360px;max-height:380px;overflow-y:auto;background:#ffffff;border:1px solid #e2e8f0;border-radius:12px;box-shadow:0 12px 30px rgba(15,23,42,0.14);z-index:999;padding:8px;"></div>
    </div>

    <!-- Nút chuông & Trung tâm Thông báo Vận hành (Lấy dữ liệu thật từ Backend Service) -->
    <div style="position:relative;" id="bms-notif-wrapper">
      <button type="button" class="bms-notif-btn" onclick="toggleBmsNotificationDropdown(event)" title="Xem thông báo & việc cần xử lý">
        <i class="fa-regular fa-bell"></i>
        @if(count($notifications ?? []) > 0)
          <span class="bms-notif-dot" id="bms-notif-dot"></span>
        @endif
      </button>

      <!-- Dropdown danh sách thông báo thật từ CSDL -->
      <div id="bms-notif-dropdown" style="display:none;position:absolute;top:calc(100% + 8px);right:0;width:360px;background:#ffffff;border:1px solid #e2e8f0;border-radius:12px;box-shadow:0 14px 34px rgba(15,23,42,0.15);z-index:1000;overflow:hidden;">
        <div style="padding:12px 16px;border-bottom:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center;background:#f8fafc;">
          <div style="display:flex;align-items:center;gap:8px;">
            <strong style="font-size:13.5px;color:#0f172a;" id="bms-notif-title">Thông báo vận hành</strong>
            <span id="bms-notif-count-badge" style="background:#eff6ff;color:#1d4ed8;font-size:11px;font-weight:700;padding:2px 8px;border-radius:999px;">{{ count($notifications ?? []) }} việc cần chú ý</span>
          </div>
          <button type="button" onclick="markAllBmsNotificationsRead()" style="border:none;background:transparent;color:#2563eb;font-size:11.5px;font-weight:600;cursor:pointer;padding:0;">Đã xem</button>
        </div>

        <div id="bms-notif-list-container" style="max-height:340px;overflow-y:auto;">
          @forelse($notifications ?? [] as $notif)
            <div onclick="handleBmsNotificationClick('{{ $notif['target_tab'] ?? '' }}', '{{ $notif['nav_id'] ?? '' }}')"
              style="padding:12px 16px;border-bottom:1px solid #f8fafc;display:flex;gap:12px;align-items:flex-start;cursor:pointer;transition:background 0.15s;"
              onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='#ffffff'">
              <div style="width:34px;height:34px;border-radius:9px;background:{{ $notif['icon_bg'] ?? '#e2e8f0' }};color:{{ $notif['icon_color'] ?? '#334155' }};display:flex;align-items:center;justify-content:center;font-size:14px;flex-shrink:0;margin-top:2px;">
                <i class="{{ $notif['icon'] ?? 'fa-solid fa-bell' }}"></i>
              </div>
              <div style="flex:1;min-width:0;">
                <div style="margin-bottom:3px;">
                  <span style="font-size:10.5px;font-weight:700;padding:2px 7px;border-radius:5px;{{ $notif['badge_style'] ?? '' }}">{{ $notif['badge_text'] ?? 'THÔNG BÁO' }}</span>
                </div>
                <div style="font-size:13px;font-weight:700;color:#0f172a;line-height:1.35;margin-bottom:2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $notif['title'] ?? '' }}</div>
                <div style="font-size:11.5px;color:#64748b;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $notif['subtitle'] ?? '' }}</div>
              </div>
            </div>
          @empty
            <div style="padding:28px 16px;text-align:center;color:#94a3b8;font-size:13px;">
              <i class="fa-regular fa-circle-check" style="font-size:22px;color:#10b981;display:block;margin-bottom:6px;"></i>
              Không có cảnh báo hoặc công việc tồn đọng.
            </div>
          @endforelse
        </div>
      </div>
    </div>

    <!-- Thông tin tài khoản đang đăng nhập & Dropdown Menu -->
    <div id="admin-user-box" style="position: relative; display: inline-flex; align-items: center;">
      <div class="filament-user-pill" id="admin-user-profile-trigger" onclick="toggleAdminUserDropdown(event)" style="border-left:1px solid #f1f5f9;" title="Tài khoản & Thiết lập">
        <div class="filament-avatar" id="filament-user-avatar" style="background:#dbeafe;color:#1d4ed8;font-size:13px;width:34px;height:34px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;flex-shrink:0;">{{ $avatarInitial ?? 'US' }}</div>
        <div style="text-align:left;min-width:0;">
          <div style="font-weight:700;font-size:13px;color:#0f172a;line-height:1.25;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:220px;" id="filament-user-name">{{ $currentUserName ?? 'Người Dùng Hệ Thống' }}</div>
          <div style="font-size:11px;color:#64748b;line-height:1.2;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:220px;" id="filament-user-sub">{{ $currentUserSub ?? 'Phân hệ quản trị' }}</div>
        </div>
        <i class="fa-solid fa-chevron-down admin-user-dropdown-caret" id="admin-user-caret" style="font-size:10px;color:#94a3b8;margin-left:4px;transition:transform 0.2s;flex-shrink:0;"></i>
      </div>

      <!-- Admin User Dropdown Menu -->
      <div id="admin-user-dropdown-menu" class="user-nav-dropdown" style="top: calc(100% + 8px); right: 0; width: 260px;">
        <div class="user-dropdown-header">
          <div style="font-weight:700; color:#0f3361; font-size:13.5px;" id="admin-dropdown-full-name">{{ $currentUserName ?? 'Người Dùng' }}</div>
          <div style="font-size:11.5px; color:#64748b;" id="admin-dropdown-email">{{ $currentUserEmail ?? 'user@officeleasing.vn' }}</div>
          <div style="margin-top:6px;"><span class="badge-role-pill" id="admin-dropdown-role-badge">{{ strtoupper($currentUserRoleLabel ?? 'USER') }}</span></div>
        </div>
        <div class="user-dropdown-divider"></div>
        <a href="#" class="user-dropdown-item" onclick="openProfileModal('tab-profile-password'); closeAdminUserDropdown(); return false;">
          <i class="fa-solid fa-key" style="color:#f59e0b;"></i> Đổi Mật Khẩu Cá Nhân
        </a>
        <div class="user-dropdown-divider"></div>
        <a href="{{ route('web.logout') }}" class="user-dropdown-item text-danger" onclick="event.preventDefault(); handleAdminLogout();">
          <i class="fa-solid fa-arrow-right-from-bracket" style="color:#dc2626;"></i> Đăng Xuất (Thoát)
        </a>
        <form id="web-logout-form" action="{{ route('web.logout') }}" method="POST" style="display:none;">
          @csrf
          <input type="hidden" name="auth_token" id="web-logout-token" value="">
        </form>
      </div>
    </div>
  </div>
</header>
