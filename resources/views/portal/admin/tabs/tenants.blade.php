<!-- TAB ADMIN: QUẢN LÝ KHÁCH THUÊ DOANH NGHIỆP (RESTFUL API - MODULE 3) -->
<div id="tab-admin-tenants" class="filament-tab-pane" style="display:none;">
  <div class="filament-page-header">
    <div>
      <div class="filament-breadcrumbs">Phân hệ Quản trị / Khách thuê</div>
      <h1 class="filament-page-title">Hồ Sơ Doanh Nghiệp Khách Thuê (Tenants)</h1>
    </div>
    <div style="display:flex;gap:8px;">
      <button class="btn-mo-outline" onclick="loadTenantsList()">
        <i class="fa-solid fa-arrows-rotate"></i> Tải Lại
      </button>
      <button class="btn-mo-orange" onclick="openCreateTenantModal()">
        <i class="fa-solid fa-plus"></i> Thêm Khách Thuê Mới
      </button>
    </div>
  </div>

  <!-- Bộ lọc nhanh & Tìm kiếm MST -->
  <div class="filament-card" style="margin-bottom:16px;padding:16px;">
    <div style="display:flex;flex-wrap:wrap;gap:12px;align-items:center;justify-content:space-between;">
      <div style="display:flex;gap:10px;align-items:center;flex:1;max-width:500px;">
        <input type="text" id="tenant-search-input" class="filament-input-sm" placeholder="Tìm kiếm theo Mã số thuế (MST), Tên công ty, Đại diện..." oninput="handleTenantSearch(this.value)" style="width:100%;">
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
