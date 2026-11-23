<!-- VIETOFFICE BMS - SIDEBAR KẾ TOÁN & TÀI CHÍNH TÒA NHÀ -->
<aside class="filament-sidebar" id="bms-main-sidebar">
  <!-- Brand Header -->
  <div class="bms-brand-header" style="border-bottom:1px solid #1e293b;padding-bottom:16px;">
    <a href="{{ route('accountant.dashboard') }}" class="bms-brand-left" style="cursor:pointer;text-decoration:none;" title="Về trang Tổng quan Kế toán">
      <div class="bms-brand-icon" style="background:#059669;color:#fff;">
        <i class="fa-solid fa-file-invoice-dollar"></i>
      </div>
      <div class="bms-brand-title" style="font-size:18px;font-weight:800;letter-spacing:0.2px;">
        <span>Office</span>
      </div>
    </a>
    <div style="display:flex;align-items:center;gap:8px;">
      <span class="filament-badge-role" style="background:rgba(16,185,129,0.18);color:#34d399;border:1px solid rgba(16,185,129,0.3);">Kế Toán</span>
      <button type="button" class="bms-sidebar-close-btn" onclick="closeBmsMobileSidebar()" title="Đóng menu">&times;</button>
    </div>
  </div>

  <!-- Section: PHÂN HỆ TÀI CHÍNH & CÔNG NỢ -->
  <div class="filament-nav-section" style="padding-top:12px;">
    <div class="filament-section-heading">TÀI CHÍNH & CÔNG NỢ</div>

    <a href="{{ route('accountant.dashboard') }}" class="filament-link {{ request()->routeIs('accountant.dashboard') ? 'active' : '' }}">
      <div class="filament-link-content">
        <i class="fa-solid fa-chart-pie"></i>
        <span>Tổng quan công nợ</span>
      </div>
    </a>

    <a href="{{ route('accountant.billing') }}" class="filament-link {{ request()->routeIs('accountant.billing') ? 'active' : '' }}">
      <div class="filament-link-content">
        <i class="fa-solid fa-file-invoice"></i>
        <span>Thông báo cước (Debit Notes)</span>
      </div>
    </a>

    <a href="{{ route('accountant.contracts') }}" class="filament-link {{ request()->routeIs('accountant.contracts') ? 'active' : '' }}">
      <div class="filament-link-content">
        <i class="fa-solid fa-file-contract"></i>
        <span>Hợp đồng & Bảng kê thanh toán</span>
      </div>
    </a>

    <a href="{{ route('accountant.tenants') }}" class="filament-link {{ request()->routeIs('accountant.tenants') ? 'active' : '' }}">
      <div class="filament-link-content">
        <i class="fa-solid fa-building-user"></i>
        <span>Sổ danh bạ khách thuê</span>
      </div>
      <span class="filament-nav-badge" style="background:rgba(56,189,248,0.14);color:#38bdf8;border:1px solid rgba(56,189,248,0.25);">{{ $stats['total_tenants'] ?? 10 }}</span>
    </a>
  </div>

  <!-- Sidebar Bottom Action -->
  <div style="margin-top:auto;padding:12px 14px 16px;display:flex;flex-direction:column;gap:8px;border-top:1px solid #1e293b;">
    <a href="{{ route('home') }}" class="btn-mo-outline" style="width:100%;justify-content:center;background:transparent;border-color:#334155;color:#94a3b8;font-size:12px;padding:7px 12px;border-radius:7px;text-decoration:none;display:inline-flex;align-items:center;gap:6px;">
      <i class="fa-solid fa-arrow-left"></i> Về Cổng Thông Tin
    </a>
  </div>
</aside>
