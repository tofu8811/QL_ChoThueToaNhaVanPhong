<div id="admin-wrapper" style="display: none;">
    <div class="filament-shell">
      <!-- Filament Dark Sidebar -->
      <aside class="filament-sidebar">
        <div class="filament-brand">
          <div class="filament-brand-logo">
            <i class="fa-solid fa-layer-group" style="color:#f59e0b;"></i>
            <span>Office</span>
          </div>
          <span class="filament-badge-role" id="sidebar-role-badge">Admin</span>
        </div>

        <!-- Section: QUẢN TRỊ HỆ THỐNG & VẬN HÀNH (Admin / BQL) -->
        <div class="filament-nav-section" id="nav-group-admin">
          <div class="filament-section-heading">TỔNG QUAN & VẬN HÀNH</div>
          <a class="filament-link active" onclick="switchFilamentTab('tab-admin-dashboard', this)">
            <div class="filament-link-content">
              <i class="fa-solid fa-chart-pie"></i>
              <span>Dashboard Vận Hành</span>
            </div>
          </a>
          <a class="filament-link" onclick="switchFilamentTab('tab-admin-buildings', this)">
            <div class="filament-link-content">
              <i class="fa-solid fa-city"></i>
              <span>Quản Lý Tòa Nhà & Sàn</span>
            </div>
            <span class="filament-nav-badge" style="background:#334155;color:#94a3b8;">{{ $stats['total_buildings'] ?? 22 }}</span>
          </a>
          <a class="filament-link" onclick="switchFilamentTab('tab-admin-tickets', this)">
            <div class="filament-link-content">
              <i class="fa-solid fa-wrench"></i>
              <span>Ticket & Sửa Chữa BQL</span>
            </div>
            <span class="filament-nav-badge" style="background:#ef4444;color:#fff;">5 mới</span>
          </a>
          <a class="filament-link" onclick="switchFilamentTab('tab-admin-contracts', this)">
            <div class="filament-link-content">
              <i class="fa-solid fa-file-contract"></i>
              <span>Quản Lý Hợp Đồng Thuê</span>
            </div>
          </a>
          <a class="filament-link" onclick="switchFilamentTab('tab-admin-tenants', this)">
            <div class="filament-link-content">
              <i class="fa-solid fa-building-user"></i>
              <span>Hồ Sơ Khách Thuê</span>
            </div>
            <span class="filament-nav-badge" style="background:#0284c7;color:#fff;">{{ $stats['total_tenants'] ?? 10 }}</span>
          </a>
        </div>

        <a class="filament-link" id="nav-item-staff" onclick="switchFilamentTab('tab-admin-staff', this)">
          <div class="filament-link-content">
            <i class="fa-solid fa-users-gear"></i>
            <span>Nhân Sự & Phân Quyền</span>
          </div>
          <span class="filament-nav-badge" style="background:#10b981;color:#fff;">4 Bộ Phận</span>
        </a>

        <!-- Section: KINH DOANH & SALES PIPELINE (Sales) -->
        <div class="filament-nav-section" id="nav-group-sales">
          <div class="filament-section-heading">PHÂN HỆ KINH DOANH (SALES)</div>
          <a class="filament-link" onclick="switchFilamentTab('tab-sales-leads', this)">
            <div class="filament-link-content">
              <i class="fa-solid fa-filter-circle-dollar"></i>
              <span>Leads Khách Hàng</span>
            </div>
            <span class="filament-nav-badge" style="background:#f59e0b;color:#000;" id="lead-count-badge">3 mới</span>
          </a>
          <a class="filament-link" onclick="switchFilamentTab('tab-sales-quotes', this)">
            <div class="filament-link-content">
              <i class="fa-solid fa-receipt"></i>
              <span>Lập & Quản Lý Báo Giá</span>
            </div>
          </a>
          <a class="filament-link" onclick="switchFilamentTab('tab-sales-pipeline', this)">
            <div class="filament-link-content">
              <i class="fa-solid fa-timeline"></i>
              <span>Quy Trình Chốt HĐ (Pipeline)</span>
            </div>
          </a>
        </div>

        <!-- Section: CỔNG KHÁCH THUÊ (Tenant Portal) -->
        <div class="filament-nav-section" id="nav-group-tenant">
          <div class="filament-section-heading">CỔNG DOANH NGHIỆP THUÊ</div>
          <a class="filament-link" onclick="switchFilamentTab('tab-tenant-workspace', this)">
            <div class="filament-link-content">
              <i class="fa-solid fa-building-user"></i>
              <span>Văn Phòng Của Tôi</span>
            </div>
          </a>
          <a class="filament-link" onclick="switchFilamentTab('tab-tenant-tickets', this)">
            <div class="filament-link-content">
              <i class="fa-solid fa-ticket"></i>
              <span>Yêu Cầu Hỗ Trợ (Ticket)</span>
            </div>
          </a>
          <a class="filament-link" onclick="switchFilamentTab('tab-tenant-bills', this)">
            <div class="filament-link-content">
              <i class="fa-solid fa-file-invoice"></i>
              <span>Hóa Đơn & Đóng Phí</span>
            </div>
            <span class="filament-nav-badge" style="background:#eab308;color:#000;">Kỳ 11</span>
          </a>
        </div>

        <!-- Exit back to portal -->
        <div style="margin-top:auto;padding:16px;display:flex;flex-direction:column;gap:8px;">
          <a href="/admin" target="_blank" id="sidebar-filament-btn" class="btn-mo-primary" style="display:none;width:100%;justify-content:center;background:#2563eb;color:#fff;text-decoration:none;font-size:12.5px;padding:8px 12px;border-radius:6px;align-items:center;gap:6px;">
            <i class="fa-solid fa-arrow-up-right-from-square"></i> Mở Filament Admin Gốc
          </a>
          <button class="btn-mo-outline" style="width:100%;justify-content:center;background:#1e293b;border-color:#334155;color:#f8fafc;" onclick="switchView('portal-view')">
            <i class="fa-solid fa-arrow-left"></i> Về Cổng Thông Tin
          </button>
        </div>
      </aside>

      <!-- Filament Main Area -->
      <div class="filament-main">
        <!-- Filament Topbar -->
        <header class="filament-topbar">
          <div class="filament-search-mock">
            <i class="fa-solid fa-magnifying-glass"></i>
            <span>Tìm kiếm dữ liệu tòa nhà, lead, hợp đồng...</span>
            <kbd>Ctrl K</kbd>
          </div>

          <div class="filament-topbar-actions">
            <div class="role-selector-pill" id="role-switcher-container" style="display:none;">
              <i class="fa-solid fa-user-shield"></i>
              <span>Đang xem vai trò:</span>
              <select id="filament-role-select" onchange="handleRoleSwitch(this.value)">
                <option value="admin">Ban Quản Lý & Admin</option>
                <option value="sales">Chuyên Viên Sales / Môi Giới</option>
                <option value="tenant">Khách Thuê (DN An Khang Tech)</option>
              </select>
            </div>

            <div class="filament-user-pill" onclick="openProfileModal('tab-profile-info')" style="cursor:pointer;" title="Bấm để xem hồ sơ và đổi mật khẩu">
              <div class="filament-avatar" id="filament-user-avatar">AD</div>
              <div>
                <div style="font-weight:700;font-size:13.5px;" id="filament-user-name">Nguyễn Quản Trị</div>
                <div style="font-size:11px;color:#64748b;" id="filament-user-sub">Quản trị viên hệ thống</div>
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
          @include('portal.admin.tabs.tenant')
          @include('portal.admin.tabs.staff-rbac')
        </div>
      </div>
    </div>
  </div>
