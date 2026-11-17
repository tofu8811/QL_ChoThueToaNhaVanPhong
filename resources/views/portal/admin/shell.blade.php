<div id="admin-wrapper" style="display: none;">
    <div class="filament-shell">
      <!-- VIETOFFICE BMS - PROPTECH CORE SIDEBAR (KẾT HỢP HÌNH 1 & HÌNH 2) -->
      <aside class="filament-sidebar" id="bms-main-sidebar">
        <!-- Brand Header -->
        <div class="bms-brand-header" style="border-bottom:1px solid #1e293b;padding-bottom:16px;">
          <div class="bms-brand-left" style="cursor:pointer;" title="Về trang Tổng quan (Dashboard)" onclick="const r = String(localStorage.getItem('auth_role')||'').toUpperCase(); if(r==='TENANT'){ switchFilamentTab('tab-tenant-workspace', document.querySelector('#nav-group-tenant .filament-link')); } else { switchFilamentTab('tab-admin-dashboard', document.getElementById('nav-item-dashboard')); }">
            <div class="bms-brand-icon">
              <i class="fa-solid fa-building"></i>
            </div>
            <div class="bms-brand-title" style="font-size:18px;font-weight:800;letter-spacing:0.2px;">
              <span>Office</span>
            </div>
          </div>
          <span class="filament-badge-role" id="sidebar-role-badge">Super Admin</span>
        </div>

        <!-- Section 1: QUẢN TRỊ VẬN HÀNH & TÀI SẢN (Admin / BQL / Kế toán) -->
        <div class="filament-nav-section" id="nav-group-admin" style="padding-top:12px;">
          <div class="filament-section-heading" id="nav-heading-operations">QUẢN TRỊ & VẬN HÀNH</div>

          <a class="filament-link active" id="nav-item-dashboard" data-breadcrumb="Tổng quan hệ thống" onclick="switchFilamentTab('tab-admin-dashboard', this)">
            <div class="filament-link-content">
              <i class="fa-solid fa-chart-pie"></i>
              <span>Tổng quan</span>
            </div>
          </a>

          <a class="filament-link" id="nav-item-buildings" data-breadcrumb="Quản lý tòa nhà & mặt bằng" onclick="switchFilamentTab('tab-admin-buildings', this)">
            <div class="filament-link-content">
              <i class="fa-solid fa-building"></i>
              <span>Quản lý tòa nhà</span>
            </div>
            <span class="filament-nav-badge" id="nav-badge-buildings" style="background:rgba(148,163,184,0.14);color:#cbd5e1;border:1px solid rgba(148,163,184,0.22);">{{ $stats['total_buildings'] ?? 22 }}</span>
          </a>

          <a class="filament-link" id="nav-item-tenants" data-breadcrumb="Quản lý khách hàng thuê" onclick="switchFilamentTab('tab-admin-tenants', this)">
            <div class="filament-link-content">
              <i class="fa-solid fa-building-user"></i>
              <span>Quản lý khách hàng</span>
            </div>
            <span class="filament-nav-badge" style="background:rgba(56,189,248,0.14);color:#38bdf8;border:1px solid rgba(56,189,248,0.25);">{{ $stats['total_tenants'] ?? 10 }}</span>
          </a>

          <a class="filament-link" id="nav-item-contracts" data-breadcrumb="Quản lý hợp đồng thuê" onclick="switchFilamentTab('tab-admin-contracts', this)">
            <div class="filament-link-content">
              <i class="fa-solid fa-file-contract"></i>
              <span>Quản lý hợp đồng</span>
            </div>
          </a>

          <a class="filament-link" id="nav-item-billing" data-breadcrumb="Quản lý công nợ & thanh toán" onclick="switchFilamentTab('tab-admin-billing', this)">
            <div class="filament-link-content">
              <i class="fa-solid fa-file-invoice-dollar"></i>
              <span>Công nợ & Thanh toán</span>
            </div>
          </a>

          <a class="filament-link" id="nav-item-tickets" data-breadcrumb="Quản lý bảo trì & sửa chữa" onclick="switchFilamentTab('tab-admin-tickets', this)">
            <div class="filament-link-content">
              <i class="fa-solid fa-screwdriver-wrench"></i>
              <span>Quản lý bảo trì</span>
            </div>
            <span class="filament-nav-badge" style="background:rgba(239,68,68,0.18);color:#f87171;border:1px solid rgba(239,68,68,0.3);">{{ $stats['open_tickets'] ?? 5 }}</span>
          </a>
        </div>

        <!-- Section 2: KINH DOANH & CHĂM SÓC LEADS (Sales / Admin) -->
        <div class="filament-nav-section" id="nav-group-sales" style="padding-top:8px;">
          <div class="filament-section-heading">KINH DOANH & LEASING</div>

          <a class="filament-link" id="nav-item-leads" data-breadcrumb="Quản lý Leads & Pipeline CRM" onclick="switchFilamentTab('tab-sales-leads', this)">
            <div class="filament-link-content">
              <i class="fa-solid fa-filter-circle-dollar"></i>
              <span>Leads & Pipeline</span>
            </div>
            <span class="filament-nav-badge" style="background:rgba(245,158,11,0.16);color:#fbbf24;border:1px solid rgba(245,158,11,0.3);" id="lead-count-badge">{{ count($leads ?? []) ?: 3 }}</span>
          </a>

          <a class="filament-link" id="nav-item-quotes" data-breadcrumb="Lập & Quản lý báo giá" onclick="switchFilamentTab('tab-sales-quotes', this)">
            <div class="filament-link-content">
              <i class="fa-solid fa-calculator"></i>
              <span>Báo giá & Dự toán</span>
            </div>
          </a>
        </div>

        <!-- Section 3: HỆ THỐNG & PHÂN QUYỀN (Super Admin) -->
        <div class="filament-nav-section" id="nav-group-system" style="padding-top:8px;">
          <div class="filament-section-heading">HỆ THỐNG & CÀI ĐẶT</div>
          <a class="filament-link" id="nav-item-staff" data-breadcrumb="Quản trị nhân sự & phân quyền RBAC" onclick="switchFilamentTab('tab-admin-staff', this)">
            <div class="filament-link-content">
              <i class="fa-solid fa-sliders"></i>
              <span>Nhân sự & Phân quyền</span>
            </div>
            <span class="filament-nav-badge" style="background:rgba(16,185,129,0.14);color:#34d399;border:1px solid rgba(16,185,129,0.25);">RBAC</span>
          </a>
        </div>

        <!-- Section 4: CỔNG KHÁCH THUÊ (Tenant Portal) -->
        <div class="filament-nav-section" id="nav-group-tenant">
          <div class="filament-section-heading">CỔNG DOANH NGHIỆP THUÊ</div>
          <a class="filament-link" data-breadcrumb="Văn phòng & hợp đồng của tôi" onclick="switchFilamentTab('tab-tenant-workspace', this)">
            <div class="filament-link-content">
              <i class="fa-solid fa-building-user"></i>
              <span>Văn phòng của tôi</span>
            </div>
          </a>
          <a class="filament-link" data-breadcrumb="Yêu cầu hỗ trợ & đăng ký thi công" onclick="switchFilamentTab('tab-tenant-tickets', this)">
            <div class="filament-link-content">
              <i class="fa-solid fa-screwdriver-wrench"></i>
              <span>Yêu cầu bảo trì</span>
            </div>
          </a>
          <a class="filament-link" data-breadcrumb="Hóa đơn & thanh toán trực tuyến" onclick="switchFilamentTab('tab-tenant-bills', this)">
            <div class="filament-link-content">
              <i class="fa-solid fa-file-invoice-dollar"></i>
              <span>Hóa đơn & Đóng phí</span>
            </div>
            <span class="filament-nav-badge" style="background:rgba(245,158,11,0.16);color:#fbbf24;border:1px solid rgba(245,158,11,0.3);">Kỳ 11</span>
          </a>
        </div>

        <!-- Sidebar Bottom Action -->
        <div style="margin-top:auto;padding:12px 14px 16px;display:flex;flex-direction:column;gap:8px;border-top:1px solid #1e293b;">
          <button class="btn-mo-outline" style="width:100%;justify-content:center;background:transparent;border-color:#334155;color:#94a3b8;font-size:12px;padding:7px 12px;border-radius:7px;" onclick="switchView('portal-view')">
            <i class="fa-solid fa-arrow-left"></i> Về Cổng Thông Tin
          </button>
        </div>
      </aside>

      <!-- Filament Main Area -->
      <div class="filament-main">
        <!-- Topbar (Breadcrumb động bên trái + Tìm kiếm + Thông báo + Hồ sơ User bên phải) -->
        <header class="filament-topbar">
          <div class="bms-topbar-left">
            <span style="cursor:pointer;" onclick="switchFilamentTab('tab-admin-dashboard', document.getElementById('nav-item-dashboard'))">Trang chủ</span>
            <i class="fa-solid fa-chevron-right" style="font-size:9px;color:#cbd5e1;"></i>
            <span class="bms-bc-current" id="topbar-current-breadcrumb">Tổng quan hệ thống</span>
          </div>

          <div class="filament-topbar-actions">
            <!-- Ô tìm kiếm nhanh đồng bộ toàn cục trên Topbar -->
            <div style="position:relative;" id="bms-topbar-search-wrapper">
              <div class="filament-search-mock" style="width:280px;padding:6px 12px;background:#f8fafc;">
                <i class="fa-solid fa-magnifying-glass" style="color:#94a3b8;font-size:12.5px;"></i>
                <input type="text" id="bms-topbar-search-input" placeholder="Tìm tòa nhà, khách thuê, HĐ, ticket..." oninput="handleTopbarQuickSearch(this.value)" onfocus="if(this.value.trim()) handleTopbarQuickSearch(this.value)" style="border:none;background:transparent;outline:none;width:100%;font-size:12.5px;color:#334155;">
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
                    <strong style="font-size:13.5px;color:#0f172a;">Thông báo vận hành</strong>
                    <span style="background:#eff6ff;color:#1d4ed8;font-size:11px;font-weight:700;padding:2px 8px;border-radius:999px;">{{ count($notifications ?? []) }} việc cần chú ý</span>
                  </div>
                  <button type="button" onclick="markAllBmsNotificationsRead()" style="border:none;background:transparent;color:#2563eb;font-size:11.5px;font-weight:600;cursor:pointer;padding:0;">Đã xem</button>
                </div>

                <div style="max-height:340px;overflow-y:auto;">
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

            <!-- Thông tin tài khoản đang đăng nhập -->
            <div class="filament-user-pill" onclick="openProfileModal('tab-profile-info')" style="cursor:pointer;padding:4px 6px 4px 10px;border-left:1px solid #f1f5f9;" title="Bấm để xem hồ sơ và đổi mật khẩu">
              <div class="filament-avatar" id="filament-user-avatar" style="background:#dbeafe;color:#1d4ed8;font-size:13px;">AD</div>
              <div>
                <div style="font-weight:700;font-size:13px;color:#0f172a;line-height:1.25;" id="filament-user-name">Nguyễn Quản Trị</div>
                <div style="font-size:11px;color:#64748b;" id="filament-user-sub">Super Admin • Toàn hệ thống</div>
              </div>
              <i class="fa-solid fa-chevron-down" style="font-size:10px;color:#94a3b8;margin-left:2px;"></i>
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
          @include('portal.admin.tabs.tenant')
          @include('portal.admin.tabs.staff-rbac')
        </div>
      </div>
    </div>
  </div>
