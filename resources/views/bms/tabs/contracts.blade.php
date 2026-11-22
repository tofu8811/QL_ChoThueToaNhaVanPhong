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
  <div class="filament-card" style="margin-bottom:16px;padding:14px 18px;">
    <div style="display:flex;flex-wrap:wrap;gap:12px;align-items:center;justify-content:space-between;">
      <div style="display:flex;gap:10px;align-items:center;flex:1;min-width:320px;">
        <div style="position:relative;flex:1;display:flex;align-items:center;">
          <i class="fa-solid fa-magnifying-glass" style="position:absolute;left:12px;color:#94a3b8;font-size:13px;pointer-events:none;"></i>
          <input type="text" id="contract-search-input" class="filament-input-sm" placeholder="Tìm theo Mã HĐ, Tên doanh nghiệp..." oninput="toggleSearchClearBtn(this)" onkeydown="if(event.key==='Enter'){ event.preventDefault(); doContractSearch(); }" style="width:100%;padding-left:34px;padding-right:32px;height:38px;font-size:13px;border-radius:6px;">
          <button type="button" class="bms-search-clear-btn" id="btn-clear-contract-search" onclick="handleClearSearchInput('contract-search-input', doContractSearch)" title="Xóa tìm kiếm">
            <i class="fa-solid fa-xmark"></i>
          </button>
        </div>
        <button type="button" class="btn-mo-primary" onclick="doContractSearch()" style="background:#2563eb;height:38px;padding:0 16px;font-size:13px;font-weight:600;display:flex;align-items:center;gap:6px;white-space:nowrap;border:none;border-radius:6px;cursor:pointer;">
          <i class="fa-solid fa-magnifying-glass"></i> Tìm kiếm
        </button>
      </div>
      <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
        <div style="display:flex;align-items:center;gap:6px;">
          <label for="contract-status-select" style="font-size:12.5px;font-weight:600;color:#64748b;white-space:nowrap;margin:0;">Trạng thái:</label>
          <select id="contract-status-select" class="filament-input-sm" onchange="handleContractStatusFilter(this.value)" style="height:38px;font-size:13px;border-radius:6px;min-width:180px;cursor:pointer;">
            <option value="ALL">-- Tất cả trạng thái --</option>
            <option value="DRAFT">Bản nháp (DRAFT)</option>
            <option value="RESERVED">Giữ chỗ (RESERVED)</option>
            <option value="ACTIVE">Hiệu lực (ACTIVE)</option>
            <option value="EXPIRING_SOON">Sắp hết hạn (EXPIRING_SOON)</option>
            <option value="TERMINATED">Đã thanh lý (TERMINATED)</option>
          </select>
        </div>
        <button type="button" class="btn-mo-outline" onclick="resetContractFilters()" style="height:38px;padding:0 14px;font-size:12.5px;font-weight:600;display:inline-flex;align-items:center;gap:6px;border-radius:6px;cursor:pointer;background:#fff;" title="Đặt lại bộ lọc & hiển thị toàn bộ Hợp đồng">
          <i class="fa-solid fa-rotate-left"></i> Đặt lại
        </button>
        <span style="font-size:13px;color:#64748b;margin-left:4px;white-space:nowrap;">Tổng cộng: <strong id="admin-contracts-count" style="color:#0f172a;">0</strong> hợp đồng</span>
      </div>
    </div>
  </div>

  <!-- Bảng dữ liệu Hợp đồng -->
  <div class="filament-card">
    <div class="table-responsive">
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
</div>