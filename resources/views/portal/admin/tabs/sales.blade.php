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
          <span class="bms-kpi-number" style="color:#0f172a;font-size:26px;">12</span>
          <span class="bms-kpi-pill" style="background:#eff6ff;color:#1d4ed8;">Đang chăm sóc</span>
        </div>
      </div>
      <div class="bms-kpi-foot"><span>Hạn mức nhận khách:</span><strong>15 leads tối đa</strong></div>
    </div>

    <div class="bms-kpi-card">
      <div>
        <div class="bms-kpi-top">
          <div class="bms-kpi-title">TỶ LỆ CHUYỂN ĐỔI DEAL</div>
          <div class="bms-kpi-icon" style="background:#ecfdf5;color:#10b981;"><i class="fa-solid fa-chart-pie"></i></div>
        </div>
        <div class="bms-kpi-main">
          <span class="bms-kpi-number" style="color:#10b981;font-size:26px;">33.3%</span>
          <span class="bms-kpi-pill" style="background:#ecfdf5;color:#047857;">4 deal chốt / 12 lead</span>
        </div>
      </div>
      <div class="bms-kpi-foot"><span>Mục tiêu đội ngũ:</span><strong>Vượt chỉ tiêu +8.3%</strong></div>
    </div>

    <div class="bms-kpi-card">
      <div>
        <div class="bms-kpi-top">
          <div class="bms-kpi-title">DOANH SỐ CHỐT HỢP ĐỒNG</div>
          <div class="bms-kpi-icon" style="background:#fef3c7;color:#d97706;"><i class="fa-solid fa-file-invoice-dollar"></i></div>
        </div>
        <div class="bms-kpi-main">
          <span class="bms-kpi-number" style="color:#d97706;font-size:24px;">4.850.000.000 đ</span>
          <span class="bms-kpi-pill" style="background:#fef3c7;color:#b45309;">Năm 2026</span>
        </div>
      </div>
      <div class="bms-kpi-foot"><span>Diện tích chốt thành công:</span><strong>1.450 m² sàn</strong></div>
    </div>

    <div class="bms-kpi-card">
      <div>
        <div class="bms-kpi-top">
          <div class="bms-kpi-title">HOA HỒNG TẠM TÍNH</div>
          <div class="bms-kpi-icon" style="background:#f3e8ff;color:#7e22ce;"><i class="fa-solid fa-sack-dollar"></i></div>
        </div>
        <div class="bms-kpi-main">
          <span class="bms-kpi-number" style="color:#7e22ce;font-size:24px;">97.000.000 đ</span>
          <span class="bms-kpi-pill" style="background:#f3e8ff;color:#6d28d9;">Đã quyết toán 70%</span>
        </div>
      </div>
      <div class="bms-kpi-foot"><span>Kỳ tính thưởng:</span><strong>Tháng 11/2026</strong></div>
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

    <table class="mo-table">
      <thead>
        <tr>
          <th>Khách Hàng / Doanh Nghiệp</th>
          <th>Tòa Nhà & Diện Tích Dự Kiến</th>
          <th>Đơn Giá Đàm Phán</th>
          <th>Tiến Độ Pipeline</th>
          <th>Khả Năng Chốt</th>
          <th style="text-align:right;">Thao Tác</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>
            <strong>Công ty Fintech NextGen</strong>
            <div style="font-size:12px;color:#64748b;">Đại diện: Mr. Hoàng Nam • 0918.777.888</div>
          </td>
          <td><strong>The Nexus Tower</strong> - 250 m² (Tầng 16)</td>
          <td><strong style="color:#2563eb;">$46.00 / m²</strong> (Fit-out 45 ngày)</td>
          <td><span class="badge-filament purple" style="background:#f3e8ff;color:#7e22ce;"><i class="fa-solid fa-handshake"></i> Đang đàm phán LOI</span></td>
          <td><span class="badge-filament success">85% chắc chắn</span></td>
          <td style="text-align:right;">
            <button class="btn-mo-primary" style="padding:4px 10px;font-size:12px;" onclick="switchFilamentTab('tab-sales-quotes', document.getElementById('nav-item-quotes'))">Gửi LOI</button>
          </td>
        </tr>
        <tr>
          <td>
            <strong>Tập đoàn Logistics Á Châu</strong>
            <div style="font-size:12px;color:#64748b;">Đại diện: Ms. Bích Thảo • 0909.112.233</div>
          </td>
          <td><strong>Saigon Centre Tower 2</strong> - 400 m² (Tầng 8)</td>
          <td><strong style="color:#2563eb;">$52.00 / m²</strong> (Fit-out 30 ngày)</td>
          <td><span class="badge-filament purple" style="background:#f3e8ff;color:#7e22ce;"><i class="fa-solid fa-handshake"></i> Đang duyệt hợp đồng</span></td>
          <td><span class="badge-filament success">90% chốt tuần này</span></td>
          <td style="text-align:right;">
            <button class="btn-mo-primary" style="padding:4px 10px;font-size:12px;" onclick="switchFilamentTab('tab-sales-leads', document.getElementById('nav-item-leads'))">Xem Deal</button>
          </td>
        </tr>
      </tbody>
    </table>
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
      <div style="display:flex;gap:10px;align-items:center;flex:1;max-width:500px;">
        <input type="text" id="crm-search-input" class="filament-input-sm" placeholder="Tìm theo tên khách, SĐT, công ty..." oninput="handleCrmSearch(this.value)" style="width:100%;">
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

<!-- TAB 3: QUẢN LÝ LỊCH HẸN DẪN KHÁCH KHẢO SÁT MẶT BẰNG THỰC TẾ -->
<div id="tab-sales-appointments" class="filament-tab-pane" style="display:none;">
  <div class="filament-page-header" style="align-items:center;">
    <div>
      <h1 class="filament-page-title" style="font-size:23px;margin-bottom:4px;">Quản Lý Lịch Hẹn Khảo Sát Mặt Bằng Thực Tế</h1>
      <p style="font-size:13.5px;color:#64748b;margin:0;">Lên lịch hẹn dẫn khách đi xem mặt bằng văn phòng, điều phối bảo vệ mở cửa sàn và ghi chú phản hồi sau buổi khảo sát.</p>
    </div>
    <div style="display:flex;gap:8px;">
      <button class="btn-mo-primary" style="background:#2563eb;" onclick="openCreateAppointmentModal()">
        <i class="fa-solid fa-calendar-plus"></i> Đặt lịch hẹn khảo sát mới
      </button>
    </div>
  </div>

  <div class="filament-card">
    <table class="mo-table">
      <thead>
        <tr>
          <th>Mã Lịch Hẹn</th>
          <th>Khách Hàng & Doanh Nghiệp</th>
          <th>Tòa Nhà & Sàn Khảo Sát</th>
          <th>Thời Gian Hẹn</th>
          <th>Sales Dẫn Đi</th>
          <th>Trạng Thái</th>
          <th>Phản Hồi Sau Khảo Sát</th>
          <th style="text-align:right;">Thao Tác</th>
        </tr>
      </thead>
      <tbody id="tbody-sales-appointments">
        <tr>
          <td><strong style="font-family:monospace;color:#2563eb;">APT-2026-001</strong></td>
          <td>
            <strong>Mr. Hoàng Nam</strong>
            <div style="font-size:12px;color:#64748b;">Fintech NextGen • 0918.777.888</div>
          </td>
          <td><strong>The Nexus Tower</strong> - Tầng 16 (250 m²)</td>
          <td><strong style="color:#d97706;">14:30 - Ngày 18/11/2026</strong></td>
          <td>Lê Hoàng Long</td>
          <td><span class="badge-filament warning">Sắp diễn ra</span></td>
          <td><em>Chưa khảo sát</em></td>
          <td style="text-align:right;">
            <button class="btn-mo-outline" style="padding:4px 8px;font-size:11.5px;color:#2563eb;" onclick="openFeedbackAppointmentModal('APT-2026-001', 'Mr. Hoàng Nam - The Nexus Tower')">Ghi chú phản hồi</button>
          </td>
        </tr>
        <tr>
          <td><strong style="font-family:monospace;color:#2563eb;">APT-2026-002</strong></td>
          <td>
            <strong>Ms. Bích Thảo</strong>
            <div style="font-size:12px;color:#64748b;">Logistics Á Châu • 0909.112.233</div>
          </td>
          <td><strong>Saigon Centre Tower 2</strong> - Tầng 8 (400 m²)</td>
          <td>10:00 - Ngày 16/11/2026</td>
          <td>Lê Hoàng Long</td>
          <td><span class="badge-filament success">Đã khảo sát</span></td>
          <td><span style="font-size:12px;color:#15803d;font-weight:600;">Khách ưng view bờ sông, yêu cầu gửi LOI miễn phí fit-out 30 ngày.</span></td>
          <td style="text-align:right;">
            <button class="btn-mo-outline" style="padding:4px 8px;font-size:11.5px;color:#64748b;" onclick="openFeedbackAppointmentModal('APT-2026-002', 'Ms. Bích Thảo')">Sửa phản hồi</button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</div>

<!-- TAB 4: LẬP BÁO GIÁ & THƯ NGỎ (QUOTATION / LOI) -->
<div id="tab-sales-quotes" class="filament-tab-pane" style="display:none;">
  <div class="filament-page-header" style="align-items:center;">
    <div>
      <h1 class="filament-page-title" style="font-size:23px;margin-bottom:4px;">Lập Báo Giá & Thư Ngỏ Thuê Văn Phòng (Quotation / LOI)</h1>
      <p style="font-size:13.5px;color:#64748b;margin:0;">Chọn mặt bằng trống, tính toán chi phí thuê, phí dịch vụ, thời gian miễn phí thi công (fit-out) và xuất file báo giá gửi khách hàng.</p>
    </div>
  </div>

  <div class="filament-card" style="padding:24px;margin-bottom:18px;">
    <h3 style="font-size:16.5px;font-weight:800;margin:0 0 16px 0;color:#0f172a;">
      <i class="fa-solid fa-calculator" style="color:#2563eb;margin-right:6px;"></i> Thiết Lập Thông Số Thư Ngỏ LOI & Báo Giá
    </h3>
    <div style="display:grid;grid-template-columns:repeat(5,1fr);gap:16px;margin-bottom:20px;">
      <div class="form-field">
        <label style="font-size:12px;font-weight:700;color:#334155;">Chọn Tòa Nhà Văn Phòng</label>
        <select id="calc-building" onchange="calculateQuickQuoteDetail()" class="filament-input" style="width:100%;">
          @forelse($serverBuildings ?? [] as $b)
            <option value="{{ $b['priceUsd'] ?? 30 }}" data-name="{{ $b['name'] }}" data-district="{{ $b['district'] ?? 'Quận 1' }}">{{ $b['name'] }} (${{ $b['priceUsd'] ?? 30 }}/m²)</option>
          @empty
            <option value="48" data-name="The Nexus Tower" data-district="Quận 1">The Nexus Tower ($48/m²)</option>
            <option value="58" data-name="Saigon Centre Tower 2" data-district="Quận 1">Saigon Centre Tower 2 ($58/m²)</option>
          @endforelse
        </select>
      </div>
      <div class="form-field">
        <label style="font-size:12px;font-weight:700;color:#334155;">Diện Tích Thuê (m²)</label>
        <input type="number" id="calc-area" value="200" min="20" oninput="calculateQuickQuoteDetail()" class="filament-input" style="width:100%;">
      </div>
      <div class="form-field">
        <label style="font-size:12px;font-weight:700;color:#334155;">Phí Dịch Vụ Quản Lý ($/m²)</label>
        <input type="number" id="calc-service" value="6" min="0" oninput="calculateQuickQuoteDetail()" class="filament-input" style="width:100%;">
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

    <!-- Bảng Chi Tiết Dự Toán Ngân Sách -->
    <div style="border:1px solid #e2e8f0;border-radius:10px;overflow:hidden;margin-bottom:20px;">
      <table class="mo-table" style="margin:0;">
        <thead>
          <tr style="background:#f8fafc;">
            <th>Hạng Mục Dự Toán</th>
            <th>Công Thức Tính Toán</th>
            <th>Đơn Giá / Tháng (USD)</th>
            <th style="text-align:right;">Thành Tiền Quy Đổi (VNĐ)</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td><strong>1. Tiền thuê văn phòng cơ bản</strong></td>
            <td id="quote-row-rent-formula">{{ $defaultQuoteEstimate['rent_formula'] ?? '200 m² × $48/m²' }}</td>
            <td><strong style="color:#2563eb;" id="quote-row-rent-usd">{{ $defaultQuoteEstimate['rent_usd_formatted'] ?? '$9,600' }}</strong></td>
            <td style="text-align:right;"><strong style="color:#2563eb;" id="quote-row-rent-vnd">{{ $defaultQuoteEstimate['rent_vnd_formatted'] ?? '235.200.000 đ' }}</strong></td>
          </tr>
          <tr>
            <td><strong>2. Phí dịch vụ quản lý tòa nhà</strong></td>
            <td id="quote-row-service-formula">{{ $defaultQuoteEstimate['service_formula'] ?? '200 m² × $6/m²' }}</td>
            <td><strong id="quote-row-service-usd">{{ $defaultQuoteEstimate['service_usd_formatted'] ?? '$1,200' }}</strong></td>
            <td style="text-align:right;"><strong id="quote-row-service-vnd">{{ $defaultQuoteEstimate['service_vnd_formatted'] ?? '29.400.000 đ' }}</strong></td>
          </tr>
          <tr>
            <td><strong>3. Thuế GTGT (VAT 10%)</strong></td>
            <td>(Tiền thuê + Phí quản lý) × 10%</td>
            <td id="quote-row-vat-usd">{{ $defaultQuoteEstimate['vat_usd_formatted'] ?? '$1,080' }}</td>
            <td style="text-align:right;" id="quote-row-vat-vnd">{{ $defaultQuoteEstimate['vat_vnd_formatted'] ?? '26.460.000 đ' }}</td>
          </tr>
          <tr style="background:#f1f5f9;font-weight:800;">
            <td>TỔNG CHI PHÍ THUÊ / THÁNG (GỒM VAT)</td>
            <td>Tiền thanh toán định kỳ hàng tháng</td>
            <td style="color:#dc2626;font-size:15px;" id="calc-total-usd">{{ $defaultQuoteEstimate['total_usd_formatted'] ?? '$11,880' }}</td>
            <td style="text-align:right;color:#dc2626;font-size:16px;" id="calc-total-vnd">{{ $defaultQuoteEstimate['total_vnd_formatted'] ?? '291.060.000 đ' }}</td>
          </tr>
          <tr style="background:#f0fdf4;">
            <td><strong style="color:#047857;">4. Tiền đặt cọc bảo đảm hợp đồng</strong></td>
            <td id="quote-row-deposit-formula">{{ $defaultQuoteEstimate['deposit_formula'] ?? '3 tháng tiền thuê' }}</td>
            <td><strong style="color:#047857;" id="quote-row-deposit-usd">{{ $defaultQuoteEstimate['deposit_usd_formatted'] ?? '$32,400' }}</strong></td>
            <td style="text-align:right;"><strong style="color:#047857;" id="quote-row-deposit-vnd">{{ $defaultQuoteEstimate['deposit_vnd_formatted'] ?? '793.800.000 đ' }}</strong></td>
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
        <button class="btn-mo-outline" type="button" onclick="alert('Đã xuất bản Thư Ngỏ Thuê (LOI - Letter of Intent) chính thức!')">
          <i class="fa-solid fa-file-contract"></i> Xuất Thư Ngỏ (LOI)
        </button>
        <button class="btn-mo-primary" type="button" style="background:#2563eb;" onclick="exportQuickQuoteCsv()">
          <i class="fa-solid fa-file-arrow-down"></i> Tải Bảng Báo Giá (CSV)
        </button>
      </div>
    </div>
  </div>
</div>
