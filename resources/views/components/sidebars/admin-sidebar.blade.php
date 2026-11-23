<!-- VIETOFFICE BMS - SIDEBAR SUPER ADMIN / ADMIN -->
<aside class="filament-sidebar" id="bms-main-sidebar">
  <!-- Brand Header -->
  <div class="bms-brand-header" style="border-bottom:1px solid #1e293b;padding-bottom:16px;">
    <a href="{{ route('admin.dashboard') }}" class="bms-brand-left" style="cursor:pointer;text-decoration:none;" title="Về trang Tổng quan Admin">
      <div class="bms-brand-icon">
        <i class="fa-solid fa-building-shield"></i>
      </div>
      <div class="bms-brand-title" style="font-size:18px;font-weight:800;letter-spacing:0.2px;">
        <span>Office</span>
      </div>
    </a>
    <div style="display:flex;align-items:center;gap:8px;">
      <span class="filament-badge-role" style="background:rgba(239,68,68,0.18);color:#f87171;border:1px solid rgba(239,68,68,0.3);">{{ $roleBadge ?? 'Admin' }}</span>
      <button type="button" class="bms-sidebar-close-btn" onclick="closeBmsMobileSidebar()" title="Đóng menu">&times;</button>
    </div>
  </div>

  <!-- Section 1: QUẢN TRỊ & VẬN HÀNH TOÀN CÔNG TY -->
  <div class="filament-nav-section" style="padding-top:12px;">
    <div class="filament-section-heading">QUẢN TRỊ VẬN HÀNH</div>

    <a href="{{ route('admin.dashboard') }}" class="filament-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
      <div class="filament-link-content">
        <i class="fa-solid fa-chart-pie"></i>
        <span>Tổng quan hệ thống</span>
      </div>
    </a>

    <a href="{{ route('admin.buildings') }}" class="filament-link {{ request()->routeIs('admin.buildings') ? 'active' : '' }}">
      <div class="filament-link-content">
        <i class="fa-solid fa-building"></i>
        <span>Tài nguyên mặt bằng</span>
      </div>
      <span class="filament-nav-badge" style="background:rgba(148,163,184,0.14);color:#cbd5e1;border:1px solid rgba(148,163,184,0.22);">{{ $stats['total_buildings'] ?? 22 }}</span>
    </a>

    <a href="{{ route('admin.contracts') }}" class="filament-link {{ request()->routeIs('admin.contracts') ? 'active' : '' }}">
      <div class="filament-link-content">
        <i class="fa-solid fa-file-contract"></i>
        <span>Hợp đồng & Bàn giao</span>
      </div>
    </a>

    <a href="{{ route('admin.leads') }}" class="filament-link {{ request()->routeIs('admin.leads') ? 'active' : '' }}">
      <div class="filament-link-content">
        <i class="fa-solid fa-filter-circle-dollar"></i>
        <span>Quản lý Leads CRM</span>
      </div>
      @if(($stats['pending_leads'] ?? 0) > 0)
        <span class="filament-nav-badge" style="background:rgba(245,158,11,0.2);color:#fbbf24;border:1px solid rgba(245,158,11,0.35);font-weight:700;" title="{{ $stats['pending_leads'] }} leads đang chờ phân bổ">{{ $stats['pending_leads'] }}</span>
      @endif
    </a>

    <a href="{{ route('admin.tenants') }}" class="filament-link {{ request()->routeIs('admin.tenants') ? 'active' : '' }}">
      <div class="filament-link-content">
        <i class="fa-solid fa-building-user"></i>
        <span>Quản lý khách thuê</span>
      </div>
      <span class="filament-nav-badge" style="background:rgba(56,189,248,0.14);color:#38bdf8;border:1px solid rgba(56,189,248,0.25);">{{ $stats['total_tenants'] ?? 10 }}</span>
    </a>

    <a href="{{ route('admin.tickets') }}" class="filament-link {{ request()->routeIs('admin.tickets') ? 'active' : '' }}">
      <div class="filament-link-content">
        <i class="fa-solid fa-screwdriver-wrench"></i>
        <span>Xử lý Ticket sự cố</span>
      </div>
      <span class="filament-nav-badge" style="background:rgba(239,68,68,0.18);color:#f87171;border:1px solid rgba(239,68,68,0.3);">{{ $stats['open_tickets'] ?? 1 }}</span>
    </a>

    <a href="{{ route('admin.billing') }}" class="filament-link {{ request()->routeIs('admin.billing') ? 'active' : '' }}">
      <div class="filament-link-content">
        <i class="fa-solid fa-file-invoice-dollar"></i>
        <span>Kế toán & Công nợ</span>
      </div>
    </a>
  </div>

  <!-- Section 2: HỆ THỐNG & CÀI ĐẶT -->
  <div class="filament-nav-section" style="padding-top:8px;">
    <div class="filament-section-heading">HỆ THỐNG & CÀI ĐẶT</div>
    <a href="{{ route('admin.staff') }}" class="filament-link {{ request()->routeIs('admin.staff') ? 'active' : '' }}">
      <div class="filament-link-content">
        <i class="fa-solid fa-sliders"></i>
        <span>Nhân sự & Phân quyền</span>
      </div>
    </a>
    <a href="{{ route('admin.mail-settings') }}" class="filament-link {{ request()->routeIs('admin.mail-settings') ? 'active' : '' }}">
      <div class="filament-link-content">
        <i class="fa-solid fa-envelope-open-text"></i>
        <span>Thiết lập Email</span>
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
