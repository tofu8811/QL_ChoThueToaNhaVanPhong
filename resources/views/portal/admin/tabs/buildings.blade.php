<!-- TAB 2: QUẢN LÝ TÒA NHÀ & MẶT BẰNG (RESTFUL API - MODULE 1) -->
<div id="tab-admin-buildings" class="filament-tab-pane" style="display:none;">
  <div class="filament-page-header">
    <div>
      <div class="filament-breadcrumbs">Ban quản lý / Danh mục bất động sản</div>
      <h1 class="filament-page-title">Quản Lý Tòa Nhà & Diện Tích Cắt Thuê</h1>
    </div>
    <div style="display:flex;gap:8px;">
      <button class="btn-mo-outline" onclick="loadAdminBuildingsList()">
        <i class="fa-solid fa-arrows-rotate"></i> Tải Lại
      </button>
      <button class="btn-mo-orange" onclick="openCreateBuildingModal()">
        <i class="fa-solid fa-plus"></i> Thêm Tòa Nhà Mới
      </button>
    </div>
  </div>

  <div class="filament-card">
    <div class="filament-card-header">
      <div style="display:flex;gap:10px;align-items:center;">
        <input type="text" id="admin-building-search" class="filament-input-sm" placeholder="Tìm theo tên tòa nhà, quận, mã..." oninput="handleAdminBuildingSearch(this.value)" style="width:260px;">
        <select class="filament-input-sm" onchange="handleAdminBuildingGrade(this.value)">
          <option value="ALL">Tất cả Phân Hạng</option>
          <option value="A">Hạng A</option>
          <option value="B">Hạng B</option>
          <option value="C">Hạng C</option>
        </select>
      </div>
      <span style="font-size:13px;color:#64748b;">Tổng cộng: <strong id="admin-buildings-total-count" style="color:#0f172a;">0</strong> cao ốc trong CSDL</span>
    </div>

    <table class="mo-table">
      <thead>
        <tr>
          <th>Tên Tòa Nhà / Mã</th>
          <th>Địa Chỉ / Khu Vực</th>
          <th>Phân Hạng</th>
          <th>Tổng Diện Tích</th>
          <th>Đơn Giá Niêm Yết</th>
          <th>Phát Hành</th>
          <th style="text-align:right;">Thao tác</th>
        </tr>
      </thead>
      <tbody id="admin-buildings-tbody">
        <!-- Rendered via JS admin-buildings.blade.php -->
      </tbody>
    </table>
  </div>
</div>
