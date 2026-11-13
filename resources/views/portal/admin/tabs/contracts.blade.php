<!-- TAB 4: QUẢN LÝ HỢP ĐỒNG THUÊ VĂN PHÒNG (RESTFUL API - MODULE 3) -->
<div id="tab-admin-contracts" class="filament-tab-pane" style="display:none;">
  <div class="filament-page-header">
    <div>
      <div class="filament-breadcrumbs">Phân hệ Quản trị / Hợp đồng</div>
      <h1 class="filament-page-title">Quản Lý Hợp Đồng Thuê Văn Phòng</h1>
    </div>
    <div style="display:flex;gap:8px;">
      <button class="btn-mo-outline" onclick="loadContractsList()">
        <i class="fa-solid fa-arrows-rotate"></i> Tải Lại
      </button>
      <button class="btn-mo-outline" style="color:#d97706;border-color:#fde68a;" onclick="openReserveContractModal()">
        <i class="fa-solid fa-hourglass-half"></i> Giữ Chỗ Sàn
      </button>
      <button class="btn-mo-orange" onclick="openCreateContractModal()">
        <i class="fa-solid fa-plus"></i> Lập Hợp Đồng Mới
      </button>
    </div>
  </div>

  <!-- Bộ lọc nhanh & Tìm kiếm -->
  <div class="filament-card" style="margin-bottom:16px;padding:16px;">
    <div style="display:flex;flex-wrap:wrap;gap:12px;align-items:center;justify-content:space-between;">
      <div style="display:flex;gap:10px;align-items:center;flex:1;max-width:480px;">
        <input type="text" id="contract-search-input" class="filament-input-sm" placeholder="Tìm theo Mã HĐ, Tên doanh nghiệp..." oninput="handleContractSearch(this.value)" style="width:100%;">
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
