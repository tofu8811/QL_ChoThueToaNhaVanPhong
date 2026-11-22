<!-- VIETOFFICE BMS - SIDEBAR CHUYÊN VIÊN KINH DOANH (SALES) -->
<aside class="filament-sidebar" id="bms-main-sidebar">
  <!-- Brand Header -->
  <div class="bms-brand-header" style="border-bottom:1px solid #1e293b;padding-bottom:16px;">
    <a href="{{ route('sales.dashboard') }}" class="bms-brand-left" style="cursor:pointer;text-decoration:none;" title="Về trang Hiệu suất Sales">
      <div class="bms-brand-icon" style="background:#f59e0b;color:#fff;">
        <i class="fa-solid fa-chart-line"></i>
      </div>
      <div class="bms-brand-title" style="font-size:18px;font-weight:800;letter-spacing:0.2px;">
        <span>Office</span>
      </div>
    </a>
    <span class="filament-badge-role" style="background:rgba(245,158,11,0.18);color:#fbbf24;border:1px solid rgba(245,158,11,0.3);">Sales Rep</span>
  </div>

  <!-- Section: PHÂN HỆ KINH DOANH & CRM -->
  <div class="filament-nav-section" style="padding-top:12px;">
    <div class="filament-section-heading">KINH DOANH & CRM</div>

    <a href="{{ route('sales.dashboard') }}" class="filament-link {{ request()->routeIs('sales.dashboard') ? 'active' : '' }}">
      <div class="filament-link-content">
        <i class="fa-solid fa-chart-pie"></i>
        <span>Báo cáo hiệu suất cá nhân</span>
      </div>
    </a>

    <a href="{{ route('sales.leads') }}" class="filament-link {{ request()->routeIs('sales.leads') ? 'active' : '' }}">
      <div class="filament-link-content">
        <i class="fa-solid fa-filter-circle-dollar"></i>
        <span>Quản lý Leads Khách hàng</span>
      </div>
      <span class="filament-nav-badge" style="background:rgba(245,158,11,0.16);color:#fbbf24;border:1px solid rgba(245,158,11,0.3);">{{ $activeLeadsCount ?? (count($leads ?? []) ?: 3) }}</span>
    </a>

    <a href="{{ route('sales.quotes') }}" class="filament-link {{ request()->routeIs('sales.quotes') ? 'active' : '' }}">
      <div class="filament-link-content">
        <i class="fa-solid fa-calculator"></i>
        <span>Lập Báo giá & Thư ngỏ (LOI)</span>
      </div>
    </a>

    <a href="{{ route('admin.contracts') }}" class="filament-link {{ request()->routeIs('admin.contracts') || request()->routeIs('sales.contracts') ? 'active' : '' }}">
      <div class="filament-link-content">
        <i class="fa-solid fa-file-signature"></i>
        <span>Hợp đồng & Lập HĐ nháp</span>
      </div>
    </a>

    <a href="{{ route('sales.available-units') }}" class="filament-link {{ request()->routeIs('sales.available-units') ? 'active' : '' }}">
      <div class="filament-link-content">
        <i class="fa-solid fa-city"></i>
        <span>Mặt bằng trống chào thuê</span>
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
