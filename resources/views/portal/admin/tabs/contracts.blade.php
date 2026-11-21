<!-- TAB 4: QUẢN LÝ HỢP ĐỒNG THUÊ VĂN PHÒNG (RESTFUL API - MODULE 3) -->
<div id="tab-admin-contracts" class="filament-tab-pane" style="display:none;">
  <div class="filament-page-header" style="align-items:center;">
    <div>
      <h1 class="filament-page-title" style="font-size:23px;margin-bottom:4px;">Quản Lý Hợp Đồng Thuê Văn Phòng</h1>
      <p style="font-size:13.5px;color:#64748b;margin:0;">Theo dõi vòng đời hợp đồng thuê, đặt cọc giữ chỗ, ký duyệt hiệu lực, gia hạn và thanh lý hoàn trả mặt bằng.</p>
    </div>
    <div style="display:flex;gap:8px;">
      <button class="btn-mo-outline" style="background:#fff;" onclick="loadContractsList()">
        <i class="fa-solid fa-arrows-rotate"></i> Làm mới
      </button>
      <button class="btn-mo-outline" style="background:#fff;color:#d97706;border-color:#fde68a;" onclick="openReserveContractModal()">
        <i class="fa-solid fa-hourglass-half"></i> Giữ chỗ sàn
      </button>
      <button class="btn-mo-primary" style="background:#2563eb;" onclick="openCreateContractModal()">
        <i class="fa-solid fa-plus"></i> Lập hợp đồng mới
      </button>
    </div>
  </div>

  <!-- Bộ lọc nhanh & Tìm kiếm -->
  <div class="filament-card" style="margin-bottom:16px;padding:16px;">
    <div style="display:flex;flex-wrap:wrap;gap:12px;align-items:center;justify-content:space-between;">
      <div style="display:flex;gap:10px;align-items:center;flex:1;max-width:540px;">
        <div style="position:relative;flex:1;display:flex;align-items:center;">
          <input type="text" id="contract-search-input" class="filament-input-sm" placeholder="Tìm theo Mã HĐ, Tên doanh nghiệp..." oninput="toggleSearchClearBtn(this)" onkeydown="if(event.key==='Enter'){ event.preventDefault(); doContractSearch(); }" style="width:100%;padding-right:32px;">
          <button type="button" class="bms-search-clear-btn" id="btn-clear-contract-search" onclick="handleClearSearchInput('contract-search-input', doContractSearch)" title="Xóa tìm kiếm">
            <i class="fa-solid fa-xmark"></i>
          </button>
        </div>
        <button type="button" class="btn-mo-primary" onclick="doContractSearch()" style="background:#2563eb;padding:6px 14px;font-size:12.5px;display:flex;align-items:center;gap:6px;white-space:nowrap;border:none;border-radius:6px;cursor:pointer;">
          <i class="fa-solid fa-magnifying-glass"></i> Tìm kiếm
        </button>
      </div>
      <div style="display:flex;gap:6px;flex-wrap:wrap;">
        <button class="btn-mo-outline contract-status-btn active" data-status="ALL" onclick="handleContractStatusFilter('ALL')" style="font-size:12px;padding:4px 10px;">Tất cả</button>
        <button class="btn-mo-outline contract-status-btn" data-status="DRAFT" onclick="handleContractStatusFilter('DRAFT')" style="font-size:12px;padding:4px 10px;">Bản nháp</button>
        <button class="btn-mo-outline contract-status-btn" data-status="RESERVED" onclick="handleContractStatusFilter('RESERVED')" style="font-size:12px;padding:4px 10px;">Giữ chỗ</button>
        <button class="btn-mo-outline contract-status-btn" data-status="ACTIVE" onclick="handleContractStatusFilter('ACTIVE')" style="font-size:12px;padding:4px 10px;">Hiệu lực</button>
        <button class="btn-mo-outline contract-status-btn" data-status="EXPIRING_SOON" onclick="handleContractStatusFilter('EXPIRING_SOON')" style="font-size:12px;padding:4px 10px;">Sắp hết hạn</button>
        <button class="btn-mo-outline contract-status-btn" data-status="TERMINATED" onclick="handleContractStatusFilter('TERMINATED')" style="font-size:12px;padding:4px 10px;">Đã thanh lý</button>
      </div>
      <span style="font-size:13px;color:#64748b;">Tổng cộng: <strong id="admin-contracts-count" style="color:#0f172a;">0</strong> hợp đồng</span>
    </div>
  </div>

  <!-- Bảng dữ liệu Hợp đồng -->
  <div class="filament-card">
    <table class="mo-table">
      <thead>
        <tr>
          <th>Mã Hợp Đồng</th>
          <th>Doanh Nghiệp Thuê</th>
          <th>Tòa Nhà & Sàn Thuê</th>
          <th>Thời Hạn Thuê</th>
          <th>Giá Thuê / Cọc</th>
          <th>Trạng Thái</th>
          <th style="text-align:right;">Thao Tác</th>
        </tr>
      </thead>
      <tbody id="admin-contracts-tbody">
        <!-- Rendered via JS contracts.blade.php -->
      </tbody>
    </table>
  </div>
</div>