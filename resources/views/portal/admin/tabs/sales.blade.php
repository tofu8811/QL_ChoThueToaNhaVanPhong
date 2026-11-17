<!-- TAB 5: QUẢN LÝ TIẾP NHẬN LEAD & PIPELINE CRM (KẾT HỢP BẢNG & KANBAN - LOẠI BỎ TRÙNG LẶP) -->
<div id="tab-sales-leads" class="filament-tab-pane" style="display:none;">
  <div class="filament-page-header" style="align-items:center;">
    <div>
      <h1 class="filament-page-title" style="font-size:23px;margin-bottom:4px;">Quản Lý Leads & Quy Trình Chốt Hợp Đồng (CRM Pipeline)</h1>
      <p style="font-size:13.5px;color:#64748b;margin:0;">Theo dõi phễu khách hàng tiềm năng, đặt lịch khảo sát mặt bằng và chuyển đổi trạng thái đàm phán chốt hợp đồng.</p>
    </div>
    <div style="display:flex;gap:8px;align-items:center;">
      <!-- Nút chuyển đổi chế độ xem: Bảng Danh Sách vs Bảng Kanban Pipeline -->
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
  <div class="filament-page-header" style="align-items:center;">
    <div>
      <h1 class="filament-page-title" style="font-size:23px;margin-bottom:4px;">Lập & Quản Lý Hồ Sơ Báo Giá Cho Khách</h1>
      <p style="font-size:13.5px;color:#64748b;margin:0;">Dự toán chi tiết cấu thành chi phí thuê văn phòng (tiền thuê sàn, phí dịch vụ quản lý, thuế GTGT 10%, tiền cọc) và xuất bảng báo giá.</p>
    </div>
    <a href="{{ route('quote.page') }}" target="_blank" class="btn-mo-primary" style="background:#2563eb;text-decoration:none;">
      <i class="fa-solid fa-file-circle-plus"></i> Mở Trang Nhận Báo Giá SSR
    </a>
  </div>

  <div class="filament-card" style="padding:24px;margin-bottom:18px;">
    <h3 style="font-size:16.5px;font-weight:800;margin:0 0 16px 0;color:#0f172a;">
      <i class="fa-solid fa-calculator" style="color:#2563eb;margin-right:6px;"></i> Công Cụ Dự Toán Chi Phí Thuê Văn Phòng Chi Tiết
    </h3>
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:20px;">
      <div class="form-field">
        <label>Chọn Tòa Nhà Văn Phòng</label>
        <select id="calc-building" onchange="calculateQuickQuoteDetail()">
          @forelse($serverBuildings ?? [] as $b)
            <option value="{{ $b['priceUsd'] ?? 30 }}" data-name="{{ $b['name'] }}" data-district="{{ $b['district'] ?? 'Quận 1' }}">{{ $b['name'] }} (${{ $b['priceUsd'] ?? 30 }}/m²)</option>
          @empty
            <option value="48" data-name="Vietcombank Tower" data-district="Quận 1">Vietcombank Tower ($48/m²)</option>
            <option value="58" data-name="Deutsches Haus" data-district="Quận 1">Deutsches Haus ($58/m²)</option>
          @endforelse
        </select>
      </div>
      <div class="form-field">
        <label>Diện Tích Thuê (m²)</label>
        <input type="number" id="calc-area" value="200" min="20" oninput="calculateQuickQuoteDetail()">
      </div>
      <div class="form-field">
        <label>Phí Dịch Vụ Quản Lý ($/m²)</label>
        <input type="number" id="calc-service" value="6" min="0" oninput="calculateQuickQuoteDetail()">
      </div>
      <div class="form-field">
        <label>Số Tháng Đặt Cọc</label>
        <select id="calc-deposit-months" onchange="calculateQuickQuoteDetail()">
          <option value="3" selected>Đặt cọc 3 tháng (Tiêu chuẩn)</option>
          <option value="2">Đặt cọc 2 tháng</option>
          <option value="6">Đặt cọc 6 tháng</option>
        </select>
      </div>
    </div>

    <!-- Bảng Chi Tiết Cấu Thành Chi Phí -->
    <div style="border:1px solid #e2e8f0;border-radius:10px;overflow:hidden;margin-bottom:18px;">
      <table class="mo-table" style="margin:0;">
        <thead>
          <tr>
            <th>Hạng Mục Chi Phí</th>
            <th>Đơn Giá / Công Thức</th>
            <th>Quy Đổi USD / Tháng</th>
            <th style="text-align:right;">Thành Tiền VNĐ (Tỷ giá 25.000đ)</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td><strong>1. Tiền thuê mặt bằng cơ sở (Base Rent)</strong></td>
            <td id="quote-row-rent-formula">{{ $defaultQuoteEstimate['rent_formula'] }}</td>
            <td><strong id="quote-row-rent-usd">{{ $defaultQuoteEstimate['rent_usd_formatted'] }}</strong></td>
            <td style="text-align:right;"><strong id="quote-row-rent-vnd">{{ $defaultQuoteEstimate['rent_vnd_formatted'] }}</strong></td>
          </tr>
          <tr>
            <td><strong>2. Phí dịch vụ quản lý vận hành (Service Charge)</strong></td>
            <td id="quote-row-service-formula">{{ $defaultQuoteEstimate['service_formula'] }}</td>
            <td><strong id="quote-row-service-usd">{{ $defaultQuoteEstimate['service_usd_formatted'] }}</strong></td>
            <td style="text-align:right;"><strong id="quote-row-service-vnd">{{ $defaultQuoteEstimate['service_vnd_formatted'] }}</strong></td>
          </tr>
          <tr>
            <td><strong>3. Thuế Giá Trị Gia Tăng (VAT 10%)</strong></td>
            <td>10% × (Tiền thuê + Phí quản lý)</td>
            <td><strong id="quote-row-vat-usd">{{ $defaultQuoteEstimate['vat_usd_formatted'] }}</strong></td>
            <td style="text-align:right;"><strong id="quote-row-vat-vnd">{{ $defaultQuoteEstimate['vat_vnd_formatted'] }}</strong></td>
          </tr>
          <tr style="background:#f8fafc;">
            <td><strong style="color:#0f172a;">TỔNG CHI PHÍ HÀNG THÁNG (ĐÃ GỒM VAT)</strong></td>
            <td>Thanh toán định kỳ hàng tháng</td>
            <td><strong style="color:#ea580c;font-size:15px;" id="calc-total-usd">{{ $defaultQuoteEstimate['total_usd_formatted'] }}</strong></td>
            <td style="text-align:right;"><strong style="color:#ea580c;font-size:15px;" id="calc-total-vnd">{{ $defaultQuoteEstimate['total_vnd_formatted'] }}</strong></td>
          </tr>
          <tr>
            <td><strong style="color:#047857;">4. Tiền đặt cọc bảo đảm hợp đồng</strong></td>
            <td id="quote-row-deposit-formula">{{ $defaultQuoteEstimate['deposit_formula'] }}</td>
            <td><strong style="color:#047857;" id="quote-row-deposit-usd">{{ $defaultQuoteEstimate['deposit_usd_formatted'] }}</strong></td>
            <td style="text-align:right;"><strong style="color:#047857;" id="quote-row-deposit-vnd">{{ $defaultQuoteEstimate['deposit_vnd_formatted'] }}</strong></td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Banner Hành Động Xuất Báo Giá -->
    <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:16px 20px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;">
      <div style="font-size:13px;color:#475569;">
        <i class="fa-solid fa-circle-check" style="color:#10b981;margin-right:6px;"></i>
        Báo giá được tính toán từ Backend (gồm điều hòa trung tâm trong giờ hành chính, bảo vệ 24/7 và vệ sinh khu vực công cộng).
      </div>
      <div style="display:flex;gap:10px;">
        <button class="btn-mo-outline" type="button" onclick="window.print()">
          <i class="fa-solid fa-print"></i> In Báo Giá
        </button>
        <button class="btn-mo-primary" type="button" style="background:#2563eb;" onclick="exportQuickQuoteCsv()">
          <i class="fa-solid fa-file-arrow-down"></i> Tải Bảng Báo Giá (CSV)
        </button>
      </div>
    </div>
  </div>

  <script>
    async function calculateQuickQuoteDetail() {
      const sel = document.getElementById('calc-building');
      if (!sel) return;
      const bName = sel.options[sel.selectedIndex]?.getAttribute('data-name') || 'Tòa nhà văn phòng';
      const params = new URLSearchParams({
        building_name: bName,
        price_usd: sel.value || 30,
        area_sqm: document.getElementById('calc-area')?.value || 200,
        service_fee_usd: document.getElementById('calc-service')?.value || 6,
        deposit_months: document.getElementById('calc-deposit-months')?.value || 3,
      });

      try {
        const res = await fetch(`{{ route('admin.quote.estimate') }}?${params.toString()}`);
        const json = await res.json();
        if (json.status && json.data) {
          const d = json.data;
          document.getElementById('quote-row-rent-formula').innerText = d.rent_formula;
          document.getElementById('quote-row-rent-usd').innerText = d.rent_usd_formatted;
          document.getElementById('quote-row-rent-vnd').innerText = d.rent_vnd_formatted;

          document.getElementById('quote-row-service-formula').innerText = d.service_formula;
          document.getElementById('quote-row-service-usd').innerText = d.service_usd_formatted;
          document.getElementById('quote-row-service-vnd').innerText = d.service_vnd_formatted;

          document.getElementById('quote-row-vat-usd').innerText = d.vat_usd_formatted;
          document.getElementById('quote-row-vat-vnd').innerText = d.vat_vnd_formatted;

          document.getElementById('calc-total-usd').innerText = d.total_usd_formatted;
          document.getElementById('calc-total-vnd').innerText = d.total_vnd_formatted;

          document.getElementById('quote-row-deposit-formula').innerText = d.deposit_formula;
          document.getElementById('quote-row-deposit-usd').innerText = d.deposit_usd_formatted;
          document.getElementById('quote-row-deposit-vnd').innerText = d.deposit_vnd_formatted;
        }
      } catch (e) {
        console.warn('Lỗi gọi API dự toán báo giá:', e);
      }
    }

    function exportQuickQuoteCsv() {
      const sel = document.getElementById('calc-building');
      const bName = sel?.options[sel.selectedIndex]?.getAttribute('data-name') || 'Tòa nhà văn phòng';
      const params = new URLSearchParams({
        building_name: bName,
        price_usd: sel?.value || 30,
        area_sqm: document.getElementById('calc-area')?.value || 200,
        service_fee_usd: document.getElementById('calc-service')?.value || 6,
        deposit_months: document.getElementById('calc-deposit-months')?.value || 3,
      });
      window.location.href = `{{ route('admin.quote.export-csv') }}?${params.toString()}`;
    }
  </script>
</div>

<!-- TAB 7: PIPELINE KANBAN (VÒNG ĐỜI KHÁCH HÀNG CRM) -->
<div id="tab-sales-pipeline" class="filament-tab-pane" style="display:none;">
  <div class="filament-page-header" style="align-items:center;">
    <div>
      <h1 class="filament-page-title" style="font-size:23px;margin-bottom:4px;">Bảng Kanban Tiến Trình Chăm Sóc Khách Hàng (CRM)</h1>
      <p style="font-size:13.5px;color:#64748b;margin:0;">Trực quan hóa 6 bước chuyển đổi khách hàng từ lúc mới tiếp nhận đến khi ký hợp đồng thuê thành công.</p>
    </div>
    <div style="display:flex;gap:8px;align-items:center;">
      <div style="display:inline-flex;background:#e2e8f0;padding:3px;border-radius:8px;margin-right:4px;">
        <button type="button" onclick="switchCrmViewMode('table')" style="border:none;background:transparent;color:#475569;font-size:12px;font-weight:600;padding:6px 12px;border-radius:6px;cursor:pointer;">
          <i class="fa-solid fa-table-list"></i> Dạng Bảng
        </button>
        <button type="button" onclick="switchCrmViewMode('kanban')" style="border:none;background:#fff;color:#0f172a;font-size:12px;font-weight:700;padding:6px 12px;border-radius:6px;cursor:pointer;box-shadow:0 1px 2px rgba(0,0,0,0.06);">
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
