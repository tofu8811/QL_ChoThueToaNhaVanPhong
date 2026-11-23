<!-- VIETOFFICE BMS - SIDEBAR BAN QUẢN LÝ (PROPERTY MANAGER - PM) -->
<aside class="filament-sidebar" id="bms-main-sidebar">
  <!-- Brand Header -->
  <div class="bms-brand-header" style="border-bottom:1px solid #1e293b;padding-bottom:16px;">
    <a href="{{ route('pm.dashboard') }}" class="bms-brand-left" style="cursor:pointer;text-decoration:none;" title="Về trang Tổng quan Ban Quản Lý">
      <div class="bms-brand-icon" style="background:#0284c7;color:#fff;">
        <i class="fa-solid fa-city"></i>
      </div>
      <div class="bms-brand-title" style="font-size:18px;font-weight:800;letter-spacing:0.2px;">
        <span>Office</span>
      </div>
    </a>
    <div style="display:flex;align-items:center;gap:8px;">
      <span class="filament-badge-role" style="background:rgba(56,189,248,0.18);color:#38bdf8;border:1px solid rgba(56,189,248,0.3);">Ban Quản Lý</span>
      <button type="button" class="bms-sidebar-close-btn" onclick="closeBmsMobileSidebar()" title="Đóng menu">&times;</button>
    </div>
  </div>

  <!-- Section: BAN QUẢN LÝ TÒA NHÀ & VẬN HÀNH KỸ THUẬT -->
  <div class="filament-nav-section" style="padding-top:12px;">
    <div class="filament-section-heading">VẬN HÀNH & KỸ THUẬT</div>

    <a href="{{ route('pm.dashboard') }}" class="filament-link {{ request()->routeIs('pm.dashboard') ? 'active' : '' }}">
      <div class="filament-link-content">
        <i class="fa-solid fa-chart-pie"></i>
        <span>Tổng quan vận hành</span>
      </div>
    </a>

    <a href="{{ route('pm.buildings') }}" class="filament-link {{ request()->routeIs('pm.buildings') ? 'active' : '' }}">
      <div class="filament-link-content">
        <i class="fa-solid fa-building"></i>
        <span>Tài nguyên mặt bằng</span>
      </div>
      <span class="filament-nav-badge" style="background:rgba(148,163,184,0.14);color:#cbd5e1;border:1px solid rgba(148,163,184,0.22);">{{ $assignedBuildingsCount ?? ($stats['total_buildings'] ?? 1) }}</span>
    </a>

    <a href="{{ route('pm.contracts') }}" class="filament-link {{ request()->routeIs('pm.contracts') ? 'active' : '' }}">
      <div class="filament-link-content">
        <i class="fa-solid fa-file-signature"></i>
        <span>Duyệt Hợp đồng & Bàn giao</span>
      </div>
    </a>

    <a href="{{ route('pm.tickets') }}" class="filament-link {{ request()->routeIs('pm.tickets') ? 'active' : '' }}">
      <div class="filament-link-content">
        <i class="fa-solid fa-screwdriver-wrench"></i>
        <span>Sự cố kỹ thuật & Thi công</span>
      </div>
      <span class="filament-nav-badge" style="background:rgba(239,68,68,0.18);color:#f87171;border:1px solid rgba(239,68,68,0.3);">{{ $stats['open_tickets'] ?? 1 }}</span>
    </a>

    <a href="{{ route('pm.utilities') }}" class="filament-link {{ request()->routeIs('pm.utilities') ? 'active' : '' }}">
      <div class="filament-link-content">
        <i class="fa-solid fa-bolt"></i>
        <span>Chỉ số Điện Nước & Dịch vụ</span>
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
