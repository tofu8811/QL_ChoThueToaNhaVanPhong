<!-- ==========================================================================
     PHÂN HỆ KINH DOANH & SALES PIPELINE (ROLE 3: SALES)
     ĐÁP ỨNG ĐẦY ĐỦ 4 TIÊU CHÍ NGHIỆP VỤ ROLE 3: NHÂN VIÊN MÔI GIỚI / SALES
     ========================================================================== -->

<!-- TAB 1: BÁO CÁO HIỆU SUẤT KINH DOANH CÁ NHÂN (ROLE 3: SALES KPI) -->
<div id="tab-sales-performance" class="filament-tab-pane" style="display:none;">
  <div class="filament-page-header" style="align-items:center;">
    <div>
      <h1 class="filament-page-title" style="font-size:23px;margin-bottom:4px;">Báo Cáo Hiệu Suất Kinh Doanh Cá Nhân</h1>
      <p style="font-size:13.5px;color:#64748b;margin:0;">Thống kê số lượng Lead đang phụ trách, tỷ lệ chuyển đổi, doanh số chốt hợp đồng và tiến độ đạt KPI của chuyên viên Sales.</p>
    </div>
    <div style="display:flex;gap:8px;">
      <button class="btn-mo-outline" style="background:#fff;" onclick="window.location.reload()">
        <i class="fa-solid fa-arrows-rotate"></i> Làm mới
      </button>
      <button class="btn-mo-primary" style="background:#2563eb;" onclick="switchFilamentTab('tab-sales-leads', document.getElementById('nav-item-leads'))">
        <i class="fa-solid fa-filter-circle-dollar"></i> Mở phễu CRM Leads
      </button>
    </div>
  </div>

  <!-- 4 KPI Thống kê cá nhân của Sales -->
  <div class="bms-kpi-grid">
    <div class="bms-kpi-card">
      <div>
        <div class="bms-kpi-top">
          <div class="bms-kpi-title">LEADS ĐANG PHỤ TRÁCH</div>
          <div class="bms-kpi-icon" style="background:#eff6ff;color:#2563eb;"><i class="fa-solid fa-users"></i></div>
        </div>
        <div class="bms-kpi-main">
          <span class="bms-kpi-number" style="color:#0f172a;font-size:26px;">{{ count($leads ?? []) }}</span>
          <span class="bms-kpi-pill" style="background:#eff6ff;color:#1d4ed8;">Đang tiếp nhận</span>
        </div>
      </div>
      <div class="bms-kpi-foot"><span>Hạn mức nhận khách:</span><strong>15 leads tối đa</strong></div>
    </div>

    <div class="bms-kpi-card">
      <div>
        <div class="bms-kpi-top">
          <div class="bms-kpi-title">DEAL ĐANG TIẾN TRIỂN</div>
          <div class="bms-kpi-icon" style="background:#ecfdf5;color:#10b981;"><i class="fa-solid fa-chart-pie"></i></div>
        </div>
        <div class="bms-kpi-main">
          <span class="bms-kpi-number" style="color:#10b981;font-size:26px;">{{ count(($leads ?? collect())->filter(fn($l) => in_array($l->status?->value ?? (string)$l->status, ['NEGOTIATING', 'PROPOSAL', 'CONTACTED']))) }}</span>
          <span class="bms-kpi-pill" style="background:#ecfdf5;color:#047857;">Đang chăm sóc</span>
        </div>
      </div>
      <div class="bms-kpi-foot"><span>Tỷ lệ tiếp cận:</span><strong>Đạt tiến độ phễu</strong></div>
    </div>

    <div class="bms-kpi-card">
      <div>
        <div class="bms-kpi-top">
          <div class="bms-kpi-title">DOANH SỐ HỢP ĐỒNG THEO DÕI</div>
          <div class="bms-kpi-icon" style="background:#fef3c7;color:#d97706;"><i class="fa-solid fa-file-invoice-dollar"></i></div>
        </div>
        <div class="bms-kpi-main">
          <span class="bms-kpi-number" style="color:#d97706;font-size:22px;">{{ number_format(($recentContracts ?? collect())->sum('monthly_rent') * 12, 0, ',', '.') }} đ</span>
          <span class="bms-kpi-pill" style="background:#fef3c7;color:#b45309;">Hợp đồng thuê</span>
        </div>
      </div>
      <div class="bms-kpi-foot"><span>Tổng giá trị quy năm:</span><strong>Năm 2026</strong></div>
    </div>

    <div class="bms-kpi-card">
      <div>
        <div class="bms-kpi-top">
          <div class="bms-kpi-title">HỢP ĐỒNG ĐÃ KÝ</div>
          <div class="bms-kpi-icon" style="background:#f3e8ff;color:#7e22ce;"><i class="fa-solid fa-file-signature"></i></div>
        </div>
        <div class="bms-kpi-main">
          <span class="bms-kpi-number" style="color:#7e22ce;font-size:26px;">{{ count($recentContracts ?? []) }}</span>
          <span class="bms-kpi-pill" style="background:#f3e8ff;color:#6d28d9;">Đã ký kết</span>
        </div>
      </div>
      <div class="bms-kpi-foot"><span>Trạng thái:</span><strong>Hợp đồng có hiệu lực</strong></div>
    </div>
  </div>

  <!-- Bảng tóm tắt các Deal đang ở giai đoạn nước rút (Thương lượng & Chốt) -->
  <div class="filament-card" style="margin-top:20px;">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;">
      <div>
        <h3 style="font-size:16px;font-weight:700;color:#0f172a;margin:0;display:flex;align-items:center;gap:8px;">
          <i class="fa-solid fa-trophy" style="color:#f59e0b;"></i> Deal Nước Rút Cần Đẩy Nhanh Trong Tháng (Đàm Phán & Chốt)
        </h3>
        <p style="font-size:12.5px;color:#64748b;margin:3px 0 0 0;">Danh sách khách hàng tiềm năng đã qua khảo sát mặt bằng và đang thương lượng điều khoản hợp đồng.</p>
      </div>
    </div>

    <div class="table-responsive">
    <table class="mo-table">
      <thead>
        <tr>
          <th>Khách Hàng / Doanh Nghiệp</th>
          <th>Tòa Nhà & Diện Tích Dự Kiến</th>
          <th>Đơn Giá Đàm Phán</th>
          <th>Tiến Độ Pipeline</th>
          <th>Trạng Thái</th>
          <th style="text-align:right;">Thao Tác</th>
        </tr>
      </thead>
      <tbody>
        @forelse(($leads ?? collect()) as $dl)
          @php
            $leadCompany = $dl->company_name ?: ($dl->contact_full_name ?? 'Khách hàng');
            $leadContact = ($dl->contact_full_name ?? '') . ($dl->phone ? ' • ' . $dl->phone : '');
            $bldName = $dl->building->name ?? 'Tòa nhà văn phòng';
            $areaText = $dl->desired_area_sqm ? number_format($dl->desired_area_sqm, 0, ',', '.') . ' m²' : 'Mặt bằng trống';
            $priceText = $dl->target_price_per_sqm ? number_format($dl->target_price_per_sqm, 0, ',', '.') . ' đ/m²' : 'Thương lượng';
            $stRaw = $dl->status?->value ?? (string)$dl->status;
          @endphp
          <tr>
            <td>
              <strong>{{ $leadCompany }}</strong>
              <div style="font-size:12px;color:#64748b;">{{ $leadContact }}</div>
            </td>
            <td><strong>{{ $bldName }}</strong> - {{ $areaText }}</td>
            <td><strong style="color:#2563eb;">{{ $priceText }}</strong></td>
            <td><span class="badge-filament purple" style="background:#f3e8ff;color:#7e22ce;"><i class="fa-solid fa-handshake"></i> {{ $dl->status?->label() ?? $stRaw }}</span></td>
            <td><span class="badge-filament success">Đang chăm sóc</span></td>
            <td style="text-align:right;">
              <button class="btn-mo-primary" style="padding:4px 10px;font-size:12px;" onclick="openCrmDetailModal('{{ $dl->id }}')">Xem Deal</button>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="6" style="text-align:center;padding:24px;color:#94a3b8;">
              Chưa có deal nào trong hệ thống CRM.
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
    </div>
  </div>
</div>

<!-- TAB 2: QUẢN LÝ KHÁCH HÀNG TIỀM NĂNG & PIPELINE CRM -->
<div id="tab-sales-leads" class="filament-tab-pane" style="display:none;">
  <div class="filament-page-header" style="align-items:center;">
    <div>
      <h1 class="filament-page-title" style="font-size:23px;margin-bottom:4px;">Quản Lý Leads & Phễu Chăm Sóc Khách Hàng (CRM)</h1>
      <p style="font-size:13.5px;color:#64748b;margin:0;">Tiếp nhận Lead từ cổng thông tin hoặc tạo mới; cập nhật quy trình: Tiếp nhận → Đi xem sàn → Gửi báo giá/LOI → Thương lượng → Chốt hợp đồng.</p>
    </div>
    <div style="display:flex;gap:8px;align-items:center;">
      <div style="display:inline-flex;background:#e2e8f0;padding:3px;border-radius:8px;margin-right:4px;">
        <button type="button" id="btn-crm-view-table" onclick="switchCrmViewMode('table')" style="border:none;background:#fff;color:#0f172a;font-size:12px;font-weight:700;padding:6px 12px;border-radius:6px;cursor:pointer;box-shadow:0 1px 2px rgba(0,0,0,0.06);">
          <i class="fa-solid fa-table-list"></i> Dạng Bảng
        </button>
        <button type="button" id="btn-crm-view-kanban" onclick="switchCrmViewMode('kanban')" style="border:none;background:transparent;color:#475569;font-size:12px;font-weight:600;padding:6px 12px;border-radius:6px;cursor:pointer;">
          <i class="fa-solid fa-table-columns"></i> Kanban Pipeline
        </button>
      </div>
      <button class="btn-mo-outline" style="background:#fff;" onclick="loadCrmLeads()">
        <i class="fa-solid fa-arrows-rotate"></i> Làm mới
      </button>
      <button class="btn-mo-primary" style="background:#2563eb;" onclick="openCreateLeadModal()">
        <i class="fa-solid fa-plus"></i> Tạo Lead mới
      </button>
    </div>
  </div>

  <!-- Bộ lọc nhanh & Tìm kiếm -->
  <div class="filament-card" style="margin-bottom:16px;padding:16px;">
    <div style="display:flex;flex-wrap:wrap;gap:12px;align-items:center;justify-content:space-between;">
      <div style="display:flex;gap:10px;align-items:center;flex:1;max-width:540px;">
        <div style="position:relative;flex:1;display:flex;align-items:center;">
          <input type="text" id="crm-search-input" class="filament-input-sm" placeholder="Tìm theo tên khách, SĐT, công ty..." oninput="toggleSearchClearBtn(this)" onkeydown="if(event.key==='Enter'){ event.preventDefault(); doCrmSearch(); }" style="width:100%;padding-right:32px;">
          <button type="button" class="bms-search-clear-btn" id="btn-clear-crm-search" onclick="handleClearSearchInput('crm-search-input', doCrmSearch)" title="Xóa tìm kiếm">
            <i class="fa-solid fa-xmark"></i>
          </button>
        </div>
        <button type="button" class="btn-mo-primary" onclick="doCrmSearch()" style="background:#2563eb;padding:6px 14px;font-size:12.5px;display:flex;align-items:center;gap:6px;white-space:nowrap;border:none;border-radius:6px;cursor:pointer;">
          <i class="fa-solid fa-magnifying-glass"></i> Tìm kiếm
        </button>
      </div>
      <div style="display:flex;gap:6px;flex-wrap:wrap;">
        <button class="btn-mo-outline crm-status-btn active" data-status="ALL" onclick="handleCrmStatusFilter('ALL')" style="font-size:12px;padding:4px 10px;">Tất cả</button>
        <button class="btn-mo-outline crm-status-btn" data-status="NEW" onclick="handleCrmStatusFilter('NEW')" style="font-size:12px;padding:4px 10px;">1. Tiếp nhận</button>
        <button class="btn-mo-outline crm-status-btn" data-status="CONTACTED" onclick="handleCrmStatusFilter('CONTACTED')" style="font-size:12px;padding:4px 10px;">Đã gọi điện</button>
        <button class="btn-mo-outline crm-status-btn" data-status="SITE_VISIT" onclick="handleCrmStatusFilter('SITE_VISIT')" style="font-size:12px;padding:4px 10px;">2. Đi xem mặt bằng</button>
        <button class="btn-mo-outline crm-status-btn" data-status="NEGOTIATING" onclick="handleCrmStatusFilter('NEGOTIATING')" style="font-size:12px;padding:4px 10px;">3. Gửi LOI / Thương lượng</button>
        <button class="btn-mo-outline crm-status-btn" data-status="WON" onclick="handleCrmStatusFilter('WON')" style="font-size:12px;padding:4px 10px;">4. Chốt hợp đồng (Won)</button>
        <button class="btn-mo-outline crm-status-btn" data-status="LOST" onclick="handleCrmStatusFilter('LOST')" style="font-size:12px;padding:4px 10px;">Thất bại (Lost)</button>
      </div>
      <span style="font-size:13px;color:#64748b;">Tổng cộng: <strong id="crm-leads-count" style="color:#0f172a;">0</strong> leads</span>
    </div>
  </div>

  <!-- Bảng dữ liệu Leads (Chế độ xem Bảng) -->
  <div class="filament-card" id="crm-table-view">
    <div class="table-responsive">
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

  <!-- Bảng Kanban Pipeline CRM (Chế độ xem Kanban) -->
  <div id="crm-kanban-view" style="display:none;margin-top:10px;">
    <div style="display:grid;grid-template-columns:repeat(6, minmax(220px, 1fr));gap:14px;align-items:start;overflow-x:auto;padding-bottom:12px;">
      <!-- 1. Mới (NEW) -->
      <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:12px;box-shadow:0 1px 3px rgba(0,0,0,0.04);">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;padding-bottom:8px;border-bottom:2px solid #3b82f6;">
          <strong style="font-size:13px;color:#1e3a8a;"><i class="fa-solid fa-inbox"></i> 1. Tiếp nhận</strong>
          <span class="badge-filament info" id="kanban-count-new">0</span>
        </div>
        <div id="kanban-col-new" style="min-height:200px;display:flex;flex-direction:column;gap:8px;"></div>
      </div>

      <!-- 2. Đã gọi (CONTACTED) -->
      <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:12px;box-shadow:0 1px 3px rgba(0,0,0,0.04);">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;padding-bottom:8px;border-bottom:2px solid #6366f1;">
          <strong style="font-size:13px;color:#3730a3;"><i class="fa-solid fa-phone"></i> 2. Đã liên hệ</strong>
          <span class="badge-filament info" id="kanban-count-contacted">0</span>
        </div>
        <div id="kanban-col-contacted" style="min-height:200px;display:flex;flex-direction:column;gap:8px;"></div>
      </div>

      <!-- 3. Xem sàn (SITE_VISIT) -->
      <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:12px;box-shadow:0 1px 3px rgba(0,0,0,0.04);">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;padding-bottom:8px;border-bottom:2px solid #f59e0b;">
          <strong style="font-size:13px;color:#92400e;"><i class="fa-solid fa-eye"></i> 3. Đi xem sàn</strong>
          <span class="badge-filament warning" id="kanban-count-site_visit">0</span>
        </div>
        <div id="kanban-col-site_visit" style="min-height:200px;display:flex;flex-direction:column;gap:8px;"></div>
      </div>

      <!-- 4. Đàm phán (NEGOTIATING) -->
      <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:12px;box-shadow:0 1px 3px rgba(0,0,0,0.04);">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;padding-bottom:8px;border-bottom:2px solid #8b5cf6;">
          <strong style="font-size:13px;color:#5b21b6;"><i class="fa-solid fa-file-contract"></i> 4. Báo giá / LOI</strong>
          <span class="badge-filament purple" style="background:#f3e8ff;color:#7e22ce;" id="kanban-count-negotiating">0</span>
        </div>
        <div id="kanban-col-negotiating" style="min-height:200px;display:flex;flex-direction:column;gap:8px;"></div>
      </div>

      <!-- 5. Thành công (WON) -->
      <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:12px;box-shadow:0 1px 3px rgba(0,0,0,0.04);">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;padding-bottom:8px;border-bottom:2px solid #10b981;">
          <strong style="font-size:13px;color:#065f46;"><i class="fa-solid fa-handshake"></i> 5. Chốt cọc (Won)</strong>
          <span class="badge-filament success" id="kanban-count-won">0</span>
        </div>
        <div id="kanban-col-won" style="min-height:200px;display:flex;flex-direction:column;gap:8px;"></div>
      </div>

      <!-- 6. Thất bại (LOST) -->
      <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:12px;box-shadow:0 1px 3px rgba(0,0,0,0.04);">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;padding-bottom:8px;border-bottom:2px solid #ef4444;">
          <strong style="font-size:13px;color:#991b1b;"><i class="fa-solid fa-ban"></i> 6. Thất bại (Lost)</strong>
          <span class="badge-filament danger" id="kanban-count-lost">0</span>
        </div>
        <div id="kanban-col-lost" style="min-height:200px;display:flex;flex-direction:column;gap:8px;"></div>
      </div>
    </div>
  </div>
</div>

<!-- TAB 3: LẬP BÁO GIÁ & THƯ NGỎ (QUOTATION / LOI) -->
<div id="tab-sales-quotes" class="filament-tab-pane" style="display:none;">
  <div class="filament-page-header" style="align-items:center;">
    <div>
      <h1 class="filament-page-title" style="font-size:23px;margin-bottom:4px;">Lập Báo Giá & Thư Ngỏ Thuê Văn Phòng (Quotation / LOI)</h1>
      <p style="font-size:13.5px;color:#64748b;margin:0;">Chọn mặt bằng trống, tính toán chi phí thuê, phí dịch vụ, thời gian miễn phí thi công (fit-out) và xuất file báo giá gửi khách hàng.</p>
    </div>
  </div>

  <div class="filament-card" style="padding:24px;margin-bottom:18px;">
    <h3 style="font-size:16.5px;font-weight:800;margin:0 0 16px 0;color:#0f172a;">
      <i class="fa-solid fa-calculator" style="color:#2563eb;margin-right:6px;"></i> Thiết Lập Thông Số Thư Ngỏ LOI & Báo Giá (100% VNĐ)
    </h3>
    <div style="display:grid;grid-template-columns:repeat(5,1fr);gap:16px;margin-bottom:20px;">
      <div class="form-field">
        <label style="font-size:12px;font-weight:700;color:#334155;">Chọn Tòa Nhà Văn Phòng</label>
        <select id="calc-building" onchange="calculateQuickQuoteDetail()" class="filament-input" style="width:100%;">
          @forelse($serverBuildings ?? [] as $b)
            <option value="{{ $b['minRentPrice'] ?? 600000 }}" data-name="{{ $b['name'] }}" data-district="{{ $b['district'] ?? 'Quận 1' }}" data-service="{{ $b['serviceFeeVnd'] ?? 120000 }}">{{ $b['name'] }} ({{ $b['priceVnd'] ?? number_format($b['minRentPrice'] ?? 600000, 0, ',', '.') . ' đ' }}/m²)</option>
          @empty
            <option value="1150000" data-name="Vietcombank Tower" data-district="Quận 1" data-service="140000">Vietcombank Tower (1.150.000 đ/m²)</option>
            <option value="1400000" data-name="Deutsches Haus" data-district="Quận 1" data-service="160000">Deutsches Haus (1.400.000 đ/m²)</option>
            <option value="680000" data-name="Lim Tower 3" data-district="Quận 1" data-service="120000">Lim Tower 3 (680.000 đ/m²)</option>
            <option value="530000" data-name="Pearl Plaza" data-district="Bình Thạnh" data-service="100000">Pearl Plaza (530.000 đ/m²)</option>
          @endforelse
        </select>
      </div>
      <div class="form-field">
        <label style="font-size:12px;font-weight:700;color:#334155;">Diện Tích Thuê (m²)</label>
        <input type="number" id="calc-area" value="200" min="20" oninput="calculateQuickQuoteDetail()" class="filament-input" style="width:100%;">
      </div>
      <div class="form-field">
        <label style="font-size:12px;font-weight:700;color:#334155;">Phí Dịch Vụ Quản Lý (VNĐ/m²)</label>
        <input type="number" id="calc-service" value="120000" step="10000" min="0" oninput="calculateQuickQuoteDetail()" class="filament-input" style="width:100%;">
      </div>
      <div class="form-field">
        <label style="font-size:12px;font-weight:700;color:#334155;">Miễn Phí Thi Công (Fit-out)</label>
        <select id="calc-fitout-days" class="filament-input" style="width:100%;">
          <option value="30" selected>30 ngày miễn phí</option>
          <option value="45">45 ngày miễn phí</option>
          <option value="60">60 ngày (Diện tích lớn)</option>
          <option value="15">15 ngày</option>
          <option value="0">Không áp dụng</option>
        </select>
      </div>
      <div class="form-field">
        <label style="font-size:12px;font-weight:700;color:#334155;">Tiền Đặt Cọc Hợp Đồng</label>
        <select id="calc-deposit-months" onchange="calculateQuickQuoteDetail()" class="filament-input" style="width:100%;">
          <option value="3" selected>Đặt cọc 3 tháng</option>
          <option value="2">Đặt cọc 2 tháng</option>
          <option value="6">Đặt cọc 6 tháng</option>
        </select>
      </div>
    </div>

    <!-- Bảng Chi Tiết Dự Toán Ngân Sách Thuần VNĐ -->
    <div style="border:1px solid #e2e8f0;border-radius:10px;overflow:hidden;margin-bottom:20px;">
      <table class="mo-table" style="margin:0;">
        <thead>
          <tr style="background:#f8fafc;">
            <th style="width:35%;">Hạng Mục Dự Toán Chi Phí</th>
            <th style="width:40%;">Công Thức Tính Toán</th>
            <th style="text-align:right;width:25%;">Thành Tiền (VNĐ)</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td><strong>1. Tiền thuê văn phòng cơ bản</strong></td>
            <td id="quote-row-rent-formula">{{ $defaultQuoteEstimate['rent_formula'] ?? '200 m² × 600.000 đ/m²' }}</td>
            <td style="text-align:right;"><strong style="color:#2563eb;" id="quote-row-rent-vnd">{{ $defaultQuoteEstimate['rent_vnd_formatted'] ?? '120.000.000 đ' }}</strong></td>
          </tr>
          <tr>
            <td><strong>2. Phí dịch vụ quản lý tòa nhà</strong></td>
            <td id="quote-row-service-formula">{{ $defaultQuoteEstimate['service_formula'] ?? '200 m² × 120.000 đ/m²' }}</td>
            <td style="text-align:right;"><strong id="quote-row-service-vnd">{{ $defaultQuoteEstimate['service_vnd_formatted'] ?? '24.000.000 đ' }}</strong></td>
          </tr>
          <tr>
            <td><strong>3. Thuế GTGT (VAT 10%)</strong></td>
            <td id="quote-row-vat-formula">{{ $defaultQuoteEstimate['vat_formula'] ?? '(Tiền thuê + Phí quản lý) × 10%' }}</td>
            <td style="text-align:right;" id="quote-row-vat-vnd">{{ $defaultQuoteEstimate['vat_vnd_formatted'] ?? '14.400.000 đ' }}</td>
          </tr>
          <tr style="background:#f1f5f9;font-weight:800;">
            <td>TỔNG CHI PHÍ THUÊ / THÁNG (GỒM VAT)</td>
            <td>Tiền thanh toán định kỳ hàng tháng</td>
            <td style="text-align:right;color:#dc2626;font-size:16px;" id="calc-total-vnd">{{ $defaultQuoteEstimate['total_vnd_formatted'] ?? '158.400.000 đ / tháng' }}</td>
          </tr>
          <tr style="background:#f0fdf4;">
            <td><strong style="color:#047857;">4. Tiền đặt cọc bảo đảm hợp đồng</strong></td>
            <td id="quote-row-deposit-formula">{{ $defaultQuoteEstimate['deposit_formula'] ?? '3 tháng tiền thuê cơ sở' }}</td>
            <td style="text-align:right;"><strong style="color:#047857;" id="quote-row-deposit-vnd">{{ $defaultQuoteEstimate['deposit_vnd_formatted'] ?? '360.000.000 đ' }}</strong></td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Banner Hành Động Xuất Báo Giá -->
    <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:16px 20px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;">
      <div style="font-size:13px;color:#475569;">
        <i class="fa-solid fa-circle-check" style="color:#10b981;margin-right:6px;"></i>
        Báo giá kèm điều khoản ưu đãi miễn phí thi công Fit-out, bảo vệ 24/7 và điều hòa trung tâm.
      </div>
      <div style="display:flex;gap:10px;">
        <button class="btn-mo-primary" type="button" style="background:#2563eb;" onclick="exportQuickQuoteCsv()">
          <i class="fa-solid fa-file-arrow-down"></i> Tải Bảng Báo Giá & Dự Toán (CSV)
        </button>
      </div>
    </div>
  </div>
</div>
