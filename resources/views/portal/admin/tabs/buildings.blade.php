<!-- TAB 2: QUẢN LÝ TÒA NHÀ & MẶT BẰNG (RESTFUL API - MODULE 1) -->
<div id="tab-admin-buildings" class="filament-tab-pane" style="display:none;">
  <div class="filament-page-header" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;">
    <div>
      <h1 class="filament-page-title" style="font-size:23px;margin-bottom:4px;">Quản Lý Tòa Nhà & Mặt Bằng Văn Phòng</h1>
      <p style="font-size:13.5px;color:#64748b;margin:0;">Quản lý danh mục cao ốc văn phòng, biểu phí vận hành, cấu hình thanh toán và trạng thái sàn trống.</p>
    </div>
    <div style="display:flex;gap:8px;flex-wrap:wrap;">
      <button class="btn-mo-outline" style="background:#fff;" onclick="loadAdminBuildingsList()">
        <i class="fa-solid fa-arrows-rotate"></i> Làm mới
      </button>
      <button class="btn-mo-outline" id="btn-top-building-assignments" style="background:#fff;color:#2563eb;border-color:#93c5fd;font-weight:600;" onclick="goToBuildingAssignments()">
        <i class="fa-solid fa-users-gear"></i> Phân công nhân sự
      </button>
      <button class="btn-mo-primary" id="btn-create-building" style="background:#2563eb;" onclick="openCreateBuildingModal()">
        <i class="fa-solid fa-plus"></i> Thêm tòa nhà mới
      </button>
    </div>
  </div>

  <div class="filament-card">
    <div class="filament-card-header">
      <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
        <div style="position:relative;width:280px;display:flex;align-items:center;">
          <input type="text" id="admin-building-search" class="filament-input-sm" placeholder="Tìm theo tên tòa nhà, quận, mã..." oninput="toggleSearchClearBtn(this)" onkeydown="if(event.key==='Enter'){ event.preventDefault(); doBuildingSearch(); }" style="width:100%;padding-right:32px;">
          <button type="button" class="bms-search-clear-btn" id="btn-clear-building-search" onclick="handleClearSearchInput('admin-building-search', doBuildingSearch)" title="Xóa tìm kiếm">
            <i class="fa-solid fa-xmark"></i>
          </button>
        </div>
        <button type="button" class="btn-mo-primary" onclick="doBuildingSearch()" style="background:#2563eb;padding:5px 14px;font-size:12.5px;display:flex;align-items:center;gap:6px;white-space:nowrap;border:none;border-radius:6px;cursor:pointer;">
          <i class="fa-solid fa-magnifying-glass"></i> Tìm kiếm
        </button>
        <select class="filament-input-sm" onchange="handleAdminBuildingGrade(this.value)">
          <option value="ALL">Tất cả Phân Hạng</option>
          <option value="A">Hạng A</option>
          <option value="B">Hạng B</option>
          <option value="C">Hạng C</option>
        </select>
      </div>
      <span style="font-size:13px;color:#64748b;">Tổng cộng: <strong id="admin-buildings-total-count" style="color:#0f172a;">0</strong> tòa nhà</span>
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
