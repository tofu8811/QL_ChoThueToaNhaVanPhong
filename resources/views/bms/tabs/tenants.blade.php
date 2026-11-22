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
  <div class="filament-card" style="margin-bottom:16px;padding:14px 18px;">
    <div style="display:flex;flex-wrap:wrap;gap:12px;align-items:center;justify-content:space-between;">
      <div style="display:flex;gap:10px;align-items:center;flex:1;min-width:320px;">
        <div style="position:relative;flex:1;display:flex;align-items:center;">
          <i class="fa-solid fa-magnifying-glass" style="position:absolute;left:12px;color:#94a3b8;font-size:13px;pointer-events:none;"></i>
          <input type="text" id="tenant-search-input" class="filament-input-sm" placeholder="Tìm kiếm theo Mã số thuế (MST), Tên công ty, Đại diện..." oninput="toggleSearchClearBtn(this)" onkeydown="if(event.key==='Enter'){ event.preventDefault(); doTenantSearch(); }" style="width:100%;padding-left:34px;padding-right:32px;height:38px;font-size:13px;border-radius:6px;">
          <button type="button" class="bms-search-clear-btn" id="btn-clear-tenant-search" onclick="handleClearSearchInput('tenant-search-input', doTenantSearch)" title="Xóa tìm kiếm">
            <i class="fa-solid fa-xmark"></i>
          </button>
        </div>
        <button type="button" class="btn-mo-primary" onclick="doTenantSearch()" style="background:#2563eb;height:38px;padding:0 16px;font-size:13px;font-weight:600;display:flex;align-items:center;gap:6px;white-space:nowrap;border:none;border-radius:6px;cursor:pointer;">
          <i class="fa-solid fa-magnifying-glass"></i> Tìm kiếm
        </button>
      </div>
      <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
        <div style="display:flex;align-items:center;gap:6px;">
          <label for="tenant-status-select" style="font-size:12.5px;font-weight:600;color:#64748b;white-space:nowrap;margin:0;">Trạng thái:</label>
          <select id="tenant-status-select" class="filament-input-sm" onchange="handleTenantStatusFilter(this.value)" style="height:38px;font-size:13px;border-radius:6px;min-width:180px;cursor:pointer;">
            <option value="ALL">-- Tất cả trạng thái --</option>
            <option value="ACTIVE">Đang hoạt động (ACTIVE)</option>
            <option value="INACTIVE">Ngừng hoạt động (INACTIVE)</option>
          </select>
        </div>
        <button type="button" class="btn-mo-outline" onclick="resetTenantFilters()" style="height:38px;padding:0 14px;font-size:12.5px;font-weight:600;display:inline-flex;align-items:center;gap:6px;border-radius:6px;cursor:pointer;background:#fff;" title="Đặt lại bộ lọc & hiển thị toàn bộ Khách thuê">
          <i class="fa-solid fa-rotate-left"></i> Đặt lại
        </button>
        <span style="font-size:13px;color:#64748b;margin-left:4px;white-space:nowrap;">Tổng cộng: <strong id="admin-tenants-count" style="color:#0f172a;">0</strong> doanh nghiệp</span>
      </div>
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
