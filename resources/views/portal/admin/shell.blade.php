<div id="admin-wrapper" style="display: block;">
    <div class="filament-shell">
      <!-- VIETOFFICE BMS - PROPTECH CORE SIDEBAR (KẾT HỢP HÌNH 1 & HÌNH 2) -->
      <aside class="filament-sidebar" id="bms-main-sidebar">
        <!-- Brand Header -->
        <div class="bms-brand-header" style="border-bottom:1px solid #1e293b;padding-bottom:16px;">
          <div class="bms-brand-left" style="cursor:pointer;" title="Về trang Tổng quan (Dashboard)" onclick="handleDashboardLogoClick(event)">
            <div class="bms-brand-icon">
              <i class="fa-solid fa-building"></i>
            </div>
            <div class="bms-brand-title" style="font-size:18px;font-weight:800;letter-spacing:0.2px;">
              <span>Office</span>
            </div>
          </div>
          <span class="filament-badge-role" id="sidebar-role-badge">Super Admin</span>
        </div>

        <!-- Section 1: BAN QUẢN LÝ TÒA NHÀ & QUẢN TRỊ VẬN HÀNH (Admin / BQL) -->
        <div class="filament-nav-section" id="nav-group-admin" style="padding-top:12px;">
          <div class="filament-section-heading" id="nav-heading-operations">QUẢN TRỊ & VẬN HÀNH</div>

          <a class="filament-link active" id="nav-item-dashboard" data-breadcrumb="Tổng quan vận hành tòa nhà" onclick="switchFilamentTab('tab-admin-dashboard', this)">
            <div class="filament-link-content">
              <i class="fa-solid fa-chart-pie"></i>
              <span id="nav-text-dashboard">Tổng quan vận hành</span>
            </div>
          </a>

          <a class="filament-link" id="nav-item-buildings" data-breadcrumb="Quản lý tài nguyên mặt bằng tòa nhà" onclick="switchFilamentTab('tab-admin-buildings', this)">
            <div class="filament-link-content">
              <i class="fa-solid fa-building"></i>
              <span id="nav-text-buildings">Tài nguyên mặt bằng</span>
            </div>
            <span class="filament-nav-badge" id="nav-badge-buildings" style="background:rgba(148,163,184,0.14);color:#cbd5e1;border:1px solid rgba(148,163,184,0.22);">{{ $stats['total_buildings'] ?? 22 }}</span>
          </a>

          <a class="filament-link" id="nav-item-contracts" data-breadcrumb="Hợp đồng thuê & Biên bản bàn giao" onclick="switchFilamentTab('tab-admin-contracts', this)">
            <div class="filament-link-content">
              <i class="fa-solid fa-file-contract"></i>
              <span>Hợp đồng & Bàn giao</span>
            </div>
          </a>

          <a class="filament-link" id="nav-item-bld-staff" data-breadcrumb="Điều phối nhân sự trong tòa nhà" onclick="goToBuildingStaffCoordination()" style="display:none;">
            <div class="filament-link-content">
              <i class="fa-solid fa-users-viewfinder"></i>
              <span>Điều phối nhân sự</span>
            </div>
          </a>

          <a class="filament-link" id="nav-item-tickets" data-breadcrumb="Tiếp nhận & Xử lý Ticket sự cố, thi công" onclick="switchFilamentTab('tab-admin-tickets', this)">
            <div class="filament-link-content">
              <i class="fa-solid fa-screwdriver-wrench"></i>
              <span>Xử lý Ticket & Thi công</span>
            </div>
            <span class="filament-nav-badge" id="nav-badge-tickets" style="background:rgba(239,68,68,0.18);color:#f87171;border:1px solid rgba(239,68,68,0.3);">{{ $stats['open_tickets'] ?? 1 }}</span>
          </a>

          <a class="filament-link" id="nav-item-billing" data-breadcrumb="Kế toán, nhập chỉ số & công nợ tòa nhà" onclick="switchFilamentTab('tab-admin-billing', this)">
            <div class="filament-link-content">
              <i class="fa-solid fa-file-invoice-dollar"></i>
              <span id="nav-text-billing">Kế toán & Công nợ</span>
            </div>
          </a>

          <a class="filament-link" id="nav-item-tenants" data-breadcrumb="Quản lý khách hàng thuê" onclick="switchFilamentTab('tab-admin-tenants', this)">
            <div class="filament-link-content">
              <i class="fa-solid fa-building-user"></i>
              <span>Quản lý khách hàng</span>
            </div>
            <span class="filament-nav-badge" style="background:rgba(56,189,248,0.14);color:#38bdf8;border:1px solid rgba(56,189,248,0.25);">{{ $stats['total_tenants'] ?? 10 }}</span>
          </a>
        </div>

        <!-- Section 2: PHÂN HỆ KINH DOANH & SALES (Dành riêng cho Chuyên viên Sales) -->
        <div class="filament-nav-section" id="nav-group-sales" style="padding-top:8px;display:none;">
          <div class="filament-section-heading">PHÂN HỆ KINH DOANH (SALES)</div>

          <a class="filament-link" id="nav-sales-performance" data-breadcrumb="Báo cáo hiệu suất kinh doanh cá nhân" onclick="switchFilamentTab('tab-sales-performance', this)">
            <div class="filament-link-content">
              <i class="fa-solid fa-chart-line"></i>
              <span>Báo cáo cá nhân</span>
            </div>
          </a>

          <a class="filament-link" id="nav-item-leads" data-breadcrumb="Quản lý Leads & Pipeline CRM" onclick="switchFilamentTab('tab-sales-leads', this)">
            <div class="filament-link-content">
              <i class="fa-solid fa-filter-circle-dollar"></i>
              <span>Leads Khách hàng</span>
            </div>
            <span class="filament-nav-badge" style="background:rgba(245,158,11,0.16);color:#fbbf24;border:1px solid rgba(245,158,11,0.3);" id="lead-count-badge">{{ count($leads ?? []) ?: 3 }}</span>
          </a>

          <a class="filament-link" id="nav-item-quotes" data-breadcrumb="Lập báo giá & Thư ngỏ (LOI)" onclick="switchFilamentTab('tab-sales-quotes', this)">
            <div class="filament-link-content">
              <i class="fa-solid fa-calculator"></i>
              <span>Báo giá & Thư ngỏ (LOI)</span>
            </div>
          </a>

          <a class="filament-link" id="nav-sales-buildings" data-breadcrumb="Danh mục mặt bằng trống chào thuê" onclick="switchFilamentTab('tab-admin-buildings', this)">
            <div class="filament-link-content">
              <i class="fa-solid fa-city"></i>
              <span>Mặt bằng chào thuê</span>
            </div>
          </a>
        </div>

        <!-- Section 3: HỆ THỐNG & PHÂN QUYỀN (Super Admin) -->
        <div class="filament-nav-section" id="nav-group-system" style="padding-top:8px;">
          <div class="filament-section-heading">HỆ THỐNG & CÀI ĐẶT</div>
          <a class="filament-link" id="nav-item-staff" data-breadcrumb="Quản trị nhân sự & phân quyền" onclick="switchFilamentTab('tab-admin-staff', this)">
            <div class="filament-link-content">
              <i class="fa-solid fa-sliders"></i>
              <span>Nhân sự & Phân quyền</span>
            </div>
          </a>
        </div>

        <!-- Sidebar Bottom Action -->
        <div style="margin-top:auto;padding:12px 14px 16px;display:flex;flex-direction:column;gap:8px;border-top:1px solid #1e293b;">
          <a href="{{ route('home') }}" class="btn-mo-outline" style="width:100%;justify-content:center;background:transparent;border-color:#334155;color:#94a3b8;font-size:12px;padding:7px 12px;border-radius:7px;text-decoration:none;display:inline-flex;align-items:center;gap:6px;">
            <i class="fa-solid fa-arrow-left"></i> Về Cổng Thông Tin
          </a>
        </div>
      </aside>

      <!-- Filament Main Area -->
      <div class="filament-main">
        <!-- Topbar (Breadcrumb động bên trái + Tìm kiếm + Thông báo + Hồ sơ User bên phải) -->
        <header class="filament-topbar">
          <div class="bms-topbar-left">
            <span style="cursor:pointer;" title="Về trang Tổng quan (Dashboard)" onclick="handleDashboardLogoClick(event)">Trang chủ</span>
            <i class="fa-solid fa-chevron-right" style="font-size:9px;color:#cbd5e1;"></i>
            <span class="bms-bc-current" id="topbar-current-breadcrumb">Tổng quan hệ thống</span>
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
                    <div onclick="handleBmsNotificationClick('{{ $notif['target_tab'] }}', '{{ $notif['nav_id'] }}')"
                      style="padding:12px 16px;border-bottom:1px solid #f8fafc;display:flex;gap:12px;align-items:flex-start;cursor:pointer;transition:background 0.15s;"
                      onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='#ffffff'">
                      <div style="width:34px;height:34px;border-radius:9px;background:{{ $notif['icon_bg'] }};color:{{ $notif['icon_color'] }};display:flex;align-items:center;justify-content:center;font-size:14px;flex-shrink:0;margin-top:2px;">
                        <i class="{{ $notif['icon'] }}"></i>
                      </div>
                      <div style="flex:1;min-width:0;">
                        <div style="margin-bottom:3px;">
                          <span style="font-size:10.5px;font-weight:700;padding:2px 7px;border-radius:5px;{{ $notif['badge_style'] }}">{{ $notif['badge_text'] }}</span>
                        </div>
                        <div style="font-size:13px;font-weight:700;color:#0f172a;line-height:1.35;margin-bottom:2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $notif['title'] }}</div>
                        <div style="font-size:11.5px;color:#64748b;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $notif['subtitle'] }}</div>
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
                <div class="filament-avatar" id="filament-user-avatar" style="background:#dbeafe;color:#1d4ed8;font-size:13px;width:34px;height:34px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;flex-shrink:0;">AD</div>
                <div style="text-align:left;min-width:0;">
                  <div style="font-weight:700;font-size:13px;color:#0f172a;line-height:1.25;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:220px;" id="filament-user-name">Nguyễn Quản Trị</div>
                  <div style="font-size:11px;color:#64748b;line-height:1.2;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:220px;" id="filament-user-sub">Super Admin • Toàn hệ thống</div>
                </div>
                <i class="fa-solid fa-chevron-down admin-user-dropdown-caret" id="admin-user-caret" style="font-size:10px;color:#94a3b8;margin-left:4px;transition:transform 0.2s;flex-shrink:0;"></i>
              </div>

              <!-- Admin User Dropdown Menu (Giống hệt cổng Public) -->
              <div id="admin-user-dropdown-menu" class="user-nav-dropdown" style="top: calc(100% + 8px); right: 0; width: 260px;">
                <div class="user-dropdown-header">
                  <div style="font-weight:700; color:#0f3361; font-size:13.5px;" id="admin-dropdown-full-name">Nguyễn Quản Trị</div>
                  <div style="font-size:11.5px; color:#64748b;" id="admin-dropdown-email">admin.demo@officeleasing.vn</div>
                  <div style="margin-top:6px;"><span class="badge-role-pill" id="admin-dropdown-role-badge">SUPER ADMIN</span></div>
                </div>
                <div class="user-dropdown-divider"></div>
                <a href="#" class="user-dropdown-item" onclick="openProfileModal('tab-profile-password'); closeAdminUserDropdown(); return false;">
                  <i class="fa-solid fa-key" style="color:#f59e0b;"></i> Đổi Mật Khẩu Cá Nhân
                </a>
                <div class="user-dropdown-divider"></div>
                <a href="#" class="user-dropdown-item text-danger" onclick="handleLogout(); closeAdminUserDropdown(); return false;">
                  <i class="fa-solid fa-arrow-right-from-bracket" style="color:#dc2626;"></i> Đăng Xuất (Thoát)
                </a>
              </div>
            </div>
          </div>
        </header>

        <!-- Filament Body Content -->
        <div class="filament-body">
          @include('portal.admin.tabs.dashboard')
          @include('portal.admin.tabs.buildings')
          @include('portal.admin.tabs.tickets')
          @include('portal.admin.tabs.contracts')
          @include('portal.admin.tabs.tenants')
          @include('portal.admin.tabs.sales')
          @include('portal.admin.tabs.staff-rbac')
          @include('portal.admin.tabs.billing')
        </div>
      </div>
    </div>
  </div>
