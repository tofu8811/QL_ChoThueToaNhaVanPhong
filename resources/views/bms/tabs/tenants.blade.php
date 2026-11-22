<!-- TAB ADMIN: QUẢN LÝ KHÁCH THUÊ DOANH NGHIỆP (RESTFUL API - MODULE 3) -->
<div id="tab-admin-tenants" class="filament-tab-pane" style="display:none;">
  <div class="filament-page-header" style="align-items:center;">
    <div>
      <h1 class="filament-page-title" style="font-size:23px;margin-bottom:4px;">Quản Lý Hồ Sơ Doanh Nghiệp Khách Thuê</h1>
      <p style="font-size:13.5px;color:#64748b;margin:0;">Quản lý thông tin pháp nhân, mã số thuế, người đại diện pháp luật và giấy phép đăng ký kinh doanh của khách thuê.</p>
    </div>
    <div style="display:flex;gap:8px;">
      <button class="btn-mo-outline" style="background:#fff;" onclick="loadTenantsList()">
        <i class="fa-solid fa-arrows-rotate"></i> Làm mới
      </button>
      <button class="btn-mo-primary" style="background:#2563eb;" onclick="openCreateTenantModal()">
        <i class="fa-solid fa-plus"></i> Thêm khách thuê mới
      </button>
    </div>
  </div>

  <!-- Bộ lọc nhanh & Tìm kiếm MST -->
  <div class="filament-card" style="margin-bottom:16px;padding:16px;">
    <div style="display:flex;flex-wrap:wrap;gap:12px;align-items:center;justify-content:space-between;">
      <div style="display:flex;gap:10px;align-items:center;flex:1;max-width:540px;">
        <div style="position:relative;flex:1;display:flex;align-items:center;">
          <input type="text" id="tenant-search-input" class="filament-input-sm" placeholder="Tìm kiếm theo Mã số thuế (MST), Tên công ty, Đại diện..." oninput="toggleSearchClearBtn(this)" onkeydown="if(event.key==='Enter'){ event.preventDefault(); doTenantSearch(); }" style="width:100%;padding-right:32px;">
          <button type="button" class="bms-search-clear-btn" id="btn-clear-tenant-search" onclick="handleClearSearchInput('tenant-search-input', doTenantSearch)" title="Xóa tìm kiếm">
            <i class="fa-solid fa-xmark"></i>
          </button>
        </div>
        <button type="button" class="btn-mo-primary" onclick="doTenantSearch()" style="background:#2563eb;padding:6px 14px;font-size:12.5px;display:flex;align-items:center;gap:6px;white-space:nowrap;border:none;border-radius:6px;cursor:pointer;">
          <i class="fa-solid fa-magnifying-glass"></i> Tìm kiếm
        </button>
      </div>
      <div style="display:flex;gap:6px;">
        <button class="btn-mo-outline tenant-status-btn active" data-status="ALL" onclick="handleTenantStatusFilter('ALL')" style="font-size:12px;padding:4px 10px;">Tất cả</button>
        <button class="btn-mo-outline tenant-status-btn" data-status="ACTIVE" onclick="handleTenantStatusFilter('ACTIVE')" style="font-size:12px;padding:4px 10px;">Đang hoạt động</button>
        <button class="btn-mo-outline tenant-status-btn" data-status="INACTIVE" onclick="handleTenantStatusFilter('INACTIVE')" style="font-size:12px;padding:4px 10px;">Ngừng hoạt động</button>
      </div>
      <span style="font-size:13px;color:#64748b;">Tổng cộng: <strong id="admin-tenants-count" style="color:#0f172a;">0</strong> doanh nghiệp</span>
    </div>
  </div>

  <!-- Bảng dữ liệu Khách thuê -->
  <div class="filament-card">
    <div class="table-responsive">
      <table class="mo-table">
        <thead>
          <tr>
            <th>Mã Doanh Nghiệp</th>
            <th>Tên Công Ty / MST</th>
            <th>Đại Diện / Liên Hệ</th>
            <th>Địa Chỉ ĐKKD</th>
            <th>Trạng Thái</th>
            <th>Hồ Sơ GPKD</th>
            <th style="text-align:right;">Thao Tác</th>
          </tr>
        </thead>
        <tbody id="admin-tenants-tbody">
          <!-- Rendered via JS tenants.blade.php -->
        </tbody>
      </table>
    </div>
  </div>
</div>
