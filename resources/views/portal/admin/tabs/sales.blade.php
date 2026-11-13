<!-- TAB 5: QUẢN LÝ TIẾP NHẬN LEAD CRM (RESTFUL API - MODULE 2) -->
<div id="tab-sales-leads" class="filament-tab-pane" style="display:none;">
  <div class="filament-page-header">
    <div>
      <div class="filament-breadcrumbs">Phân hệ Kinh doanh / Tiếp nhận Lead</div>
      <h1 class="filament-page-title">Quản Lý Khách Hàng Tiềm Năng (CRM Leads)</h1>
    </div>
    <div style="display:flex;gap:8px;">
      <button class="btn-mo-outline" onclick="loadCrmLeads()">
        <i class="fa-solid fa-arrows-rotate"></i> Tải Lại
      </button>
      <button class="btn-mo-orange" onclick="openCreateLeadModal()">
        <i class="fa-solid fa-plus"></i> Tạo Lead Mới
      </button>
    </div>
  </div>

  <!-- Bộ lọc nhanh & Tìm kiếm -->
  <div class="filament-card" style="margin-bottom:16px;padding:16px;">
    <div style="display:flex;flex-wrap:wrap;gap:12px;align-items:center;justify-content:space-between;">
      <div style="display:flex;gap:10px;align-items:center;flex:1;max-width:500px;">
        <input type="text" id="crm-search-input" class="filament-input-sm" placeholder="Tìm theo tên khách, SĐT, công ty..." oninput="handleCrmSearch(this.value)" style="width:100%;">
      </div>
      <div style="display:flex;gap:6px;flex-wrap:wrap;">
        <button class="btn-mo-outline crm-status-btn active" data-status="ALL" onclick="handleCrmStatusFilter('ALL')" style="font-size:12px;padding:4px 10px;">Tất cả</button>
        <button class="btn-mo-outline crm-status-btn" data-status="NEW" onclick="handleCrmStatusFilter('NEW')" style="font-size:12px;padding:4px 10px;">Mới</button>
        <button class="btn-mo-outline crm-status-btn" data-status="CONTACTED" onclick="handleCrmStatusFilter('CONTACTED')" style="font-size:12px;padding:4px 10px;">Đã gọi</button>
        <button class="btn-mo-outline crm-status-btn" data-status="SITE_VISIT" onclick="handleCrmStatusFilter('SITE_VISIT')" style="font-size:12px;padding:4px 10px;">Xem sàn</button>
        <button class="btn-mo-outline crm-status-btn" data-status="NEGOTIATING" onclick="handleCrmStatusFilter('NEGOTIATING')" style="font-size:12px;padding:4px 10px;">Đàm phán</button>
        <button class="btn-mo-outline crm-status-btn" data-status="WON" onclick="handleCrmStatusFilter('WON')" style="font-size:12px;padding:4px 10px;">Thành công</button>
        <button class="btn-mo-outline crm-status-btn" data-status="LOST" onclick="handleCrmStatusFilter('LOST')" style="font-size:12px;padding:4px 10px;">Thất bại</button>
      </div>
      <span style="font-size:13px;color:#64748b;">Tổng cộng: <strong id="crm-leads-count" style="color:#0f172a;">0</strong> leads</span>
    </div>
  </div>

  <!-- Bảng dữ liệu Leads -->
  <div class="filament-card">
    <table class="mo-table">
      <thead>
        <tr>
          <th>Mã Lead / Ngày</th>
          <th>Khách Hàng / Công Ty</th>
          <th>Tòa Nhà / Nhu Cầu</th>
          <th>Trạng Thái Pipeline</th>
          <th>Sales Phụ Trách</th>
          <th>Ghi Chú</th>
          <th style="text-align:right;">Thao Tác</th>
        </tr>
      </thead>
      <tbody id="crm-leads-tbody">
        <!-- Rendered via JS crm-leads.blade.php -->
      </tbody>
    </table>
  </div>
</div>

<!-- TAB 6: LẬP BÁO GIÁ CHO SALES & CÔNG CỤ TÍNH NHANH -->
<div id="tab-sales-quotes" class="filament-tab-pane" style="display:none;">
  <div class="filament-page-header">
    <div>
      <div class="filament-breadcrumbs">Phân hệ Kinh doanh / Báo giá</div>
      <h1 class="filament-page-title">Lập & Quản Lý Hồ Sơ Báo Giá Cho Khách</h1>
    </div>
    <a href="{{ route('quote.page') }}" target="_blank" class="btn-mo-orange" style="text-decoration:none;">
      <i class="fa-solid fa-file-circle-plus"></i> Mở Trang Nhận Báo Giá SSR
    </a>
  </div>

  <div class="filament-card" style="padding:24px;">
    <h3 style="font-size:18px;font-weight:700;margin-bottom:14px;color:var(--mo-primary);">
      <i class="fa-solid fa-calculator"></i> Công Cụ Tính Nhanh Chi Phí Thuê Văn Phòng
    </h3>
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:18px;">
      <div class="form-field">
        <label>Chọn Tòa Nhà</label>
        <select id="calc-building" onchange="calculateQuickQuote()">
          @forelse($serverBuildings ?? [] as $b)
            <option value="{{ $b['priceUsd'] ?? 30 }}">{{ $b['name'] }} (${{ $b['priceUsd'] ?? 30 }}/m²)</option>
          @empty
            <option value="48">Vietcombank Tower ($48/m²)</option>
            <option value="58">Deutsches Haus ($58/m²)</option>
            <option value="28">Lim Tower 3 ($28/m²)</option>
            <option value="22">Pearl Plaza ($22/m²)</option>
          @endforelse
        </select>
      </div>
      <div class="form-field">
        <label>Diện Tích Thuê (m²)</label>
        <input type="number" id="calc-area" value="250" oninput="calculateQuickQuote()">
      </div>
      <div class="form-field">
        <label>Phí Quản Lý ($/m²)</label>
        <input type="number" id="calc-service" value="6" oninput="calculateQuickQuote()">
      </div>
    </div>

    <!-- Calculation Result Banner -->
    <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:20px;display:flex;justify-content:space-between;align-items:center;">
      <div>
        <div style="font-size:13px;color:#64748b;">Tổng tiền thuê dự kiến hàng tháng:</div>
        <div style="font-size:28px;font-weight:800;color:#ea580c;" id="calc-total-usd">$13,500 / tháng</div>
        <div style="font-size:13px;color:#94a3b8;" id="calc-total-vnd">Tương đương: ~330.750.000 VNĐ / tháng (Chưa gồm VAT)</div>
      </div>
      <button class="btn-mo-primary" type="button" onclick="alert('Đã xuất file báo giá PDF gửi cho khách hàng!')">
        <i class="fa-solid fa-paper-plane"></i> Xuất Báo Giá PDF
      </button>
    </div>
  </div>
</div>

<!-- TAB 7: PIPELINE KANBAN (VÒNG ĐỜI KHÁCH HÀNG CRM) -->
<div id="tab-sales-pipeline" class="filament-tab-pane" style="display:none;">
  <div class="filament-page-header">
    <div>
      <div class="filament-breadcrumbs">Phân hệ Kinh doanh / Pipeline</div>
      <h1 class="filament-page-title">Bảng Kanban Tiến Trình Chăm Sóc Khách Hàng (CRM)</h1>
    </div>
    <div style="display:flex;gap:8px;">
      <button class="btn-mo-outline" onclick="loadCrmLeads()">
        <i class="fa-solid fa-arrows-rotate"></i> Làm Mới
      </button>
      <button class="btn-mo-orange" onclick="openCreateLeadModal()">
        <i class="fa-solid fa-plus"></i> Tạo Lead Mới
      </button>
    </div>
  </div>

  <!-- Kanban 6 Columns -->
  <div style="display:grid;grid-template-columns:repeat(6, 1fr);gap:14px;overflow-x:auto;padding-bottom:16px;">
    <!-- Column 1: NEW -->
    <div style="background:#f1f5f9;border-radius:8px;padding:12px;min-width:240px;">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
        <span style="font-weight:700;font-size:13px;color:#0369a1;"><i class="fa-solid fa-sparkles"></i> Mới Tiếp Nhận</span>
        <span id="kanban-count-new" class="badge-filament info" style="font-size:11px;">0</span>
      </div>
      <div id="kanban-col-new" style="min-height:200px;"></div>
    </div>

    <!-- Column 2: CONTACTED -->
    <div style="background:#f1f5f9;border-radius:8px;padding:12px;min-width:240px;">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
        <span style="font-weight:700;font-size:13px;color:#2563eb;"><i class="fa-solid fa-phone"></i> Đã Liên Hệ</span>
        <span id="kanban-count-contacted" class="badge-filament primary" style="font-size:11px;">0</span>
      </div>
      <div id="kanban-col-contacted" style="min-height:200px;"></div>
    </div>

    <!-- Column 3: SITE_VISIT -->
    <div style="background:#f1f5f9;border-radius:8px;padding:12px;min-width:240px;">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
        <span style="font-weight:700;font-size:13px;color:#d97706;"><i class="fa-solid fa-building-circle-check"></i> Khảo Sát Sàn</span>
        <span id="kanban-count-site_visit" class="badge-filament warning" style="font-size:11px;">0</span>
      </div>
      <div id="kanban-col-site_visit" style="min-height:200px;"></div>
    </div>

    <!-- Column 4: NEGOTIATING -->
    <div style="background:#f1f5f9;border-radius:8px;padding:12px;min-width:240px;">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
        <span style="font-weight:700;font-size:13px;color:#7e22ce;"><i class="fa-solid fa-handshake"></i> Đàm Phán Giá</span>
        <span id="kanban-count-negotiating" class="badge-filament purple" style="font-size:11px;background:#f3e8ff;color:#7e22ce;">0</span>
      </div>
      <div id="kanban-col-negotiating" style="min-height:200px;"></div>
    </div>

    <!-- Column 5: WON -->
    <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:12px;min-width:240px;">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
        <span style="font-weight:700;font-size:13px;color:#15803d;"><i class="fa-solid fa-trophy"></i> Thành Công (Won)</span>
        <span id="kanban-count-won" class="badge-filament success" style="font-size:11px;">0</span>
      </div>
      <div id="kanban-col-won" style="min-height:200px;"></div>
    </div>

    <!-- Column 6: LOST -->
    <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:12px;min-width:240px;">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
        <span style="font-weight:700;font-size:13px;color:#b91c1c;"><i class="fa-solid fa-ban"></i> Thất Bại (Lost)</span>
        <span id="kanban-count-lost" class="badge-filament danger" style="font-size:11px;">0</span>
      </div>
      <div id="kanban-col-lost" style="min-height:200px;"></div>
    </div>
  </div>
</div>
