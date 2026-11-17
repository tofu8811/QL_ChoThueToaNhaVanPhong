<div id="tab-admin-dashboard" class="filament-tab-pane active">
  @php
    $totalBlds = $stats['total_buildings'] ?? 22;
    $totalU = $stats['total_units'] ?? 65;
    $rentedU = $stats['rented_units'] ?? 23;
    $availU = $stats['available_units'] ?? 39;
    $expU = $stats['expiring_units'] ?? 3;

    $rentedArea = $stats['rented_area'] ?? 6620;
    $availArea = $stats['available_area'] ?? 11940;
    $expArea = $stats['expiring_area'] ?? 2450;
    $totalUnitsArea = $stats['total_units_area'] ?? 21010;

    $rentedPct = $stats['rented_pct'] ?? 35.4;
    $availPct = $stats['available_pct'] ?? 60.0;
    $expPct = $stats['expiring_pct'] ?? 4.6;

    $offsetAvail = $stats['offset_avail'] ?? -35.4;
    $offsetExp = $stats['offset_exp'] ?? -95.4;

    $buildingPerfs = $stats['building_performances'] ?? [];
    $tPriorityLabel = $stats['first_ticket_priority_label'] ?? 'Ưu tiên cao';
    $tStatusLabel = $stats['first_ticket_status_label'] ?? 'Đang xử lý';
  @endphp

  <!-- Header Tổng quan vận hành & kinh doanh tòa nhà -->
  <div class="filament-page-header" style="align-items:center;margin-bottom:22px;">
    <div>
      <h1 class="filament-page-title" id="dashboard-page-title" style="font-size:23px;margin-bottom:4px;">Tổng Quan Vận Hành & Kinh Doanh Tòa Nhà</h1>
      <p id="dashboard-page-subtitle" style="font-size:13.5px;color:#64748b;margin:0;">Bức tranh toàn cảnh về quy mô tài sản, hợp đồng thuê, doanh thu công nợ và trạng thái vận hành toàn hệ thống.</p>
    </div>
    <div style="display:flex;gap:10px;align-items:center;">
      <button type="button" class="btn-mo-outline" style="background:#fff;padding:8px 15px;font-size:13px;border-radius:8px;display:inline-flex;align-items:center;gap:7px;" onclick="window.location.reload();">
        <i class="fa-solid fa-arrows-rotate"></i> Làm mới
      </button>
      <a href="{{ route('admin.dashboard.export-csv') }}" class="btn-mo-primary" style="background:#2563eb;padding:8px 16px;font-size:13px;border-radius:8px;display:inline-flex;align-items:center;gap:7px;box-shadow:0 4px 12px rgba(37,99,235,0.25);text-decoration:none;">
        <i class="fa-solid fa-file-export"></i> Xuất báo cáo
      </a>
    </div>
  </div>

  <!-- HÀNG 1: 4 THẺ KPI TRỤ CỘT TOÀN HỆ THỐNG (TÀI SẢN - HỢP ĐỒNG - DOANH THU - VẬN HÀNH) -->
  <div class="bms-kpi-grid">
    <!-- KPI 1: Quy mô tòa nhà & mặt bằng -->
    <div class="bms-kpi-card" style="cursor:pointer;" onclick="switchFilamentTab('tab-admin-buildings', document.getElementById('nav-item-buildings'))">
      <div>
        <div class="bms-kpi-top">
          <div class="bms-kpi-title" id="dashboard-card1-title">QUY MÔ TÀI SẢN & SÀN</div>
          <div class="bms-kpi-icon" style="background:#f1f5f9;color:#334155;">
            <i class="fa-solid fa-city"></i>
          </div>
        </div>
        <div class="bms-kpi-main">
          <span class="bms-kpi-number" id="dashboard-total-blds" style="color:#0f172a;">{{ $totalBlds }}</span>
          <span id="dashboard-total-blds-sub" style="font-size:13.5px;font-weight:600;color:#64748b;">tòa nhà</span>
          <span class="bms-kpi-pill" id="dashboard-total-units-sub" style="background:#f1f5f9;color:#334155;">{{ $totalU }} mặt bằng</span>
        </div>
      </div>
      <div class="bms-kpi-foot">
        <span id="dashboard-card1-foot-label">Tổng diện tích quản lý:</span>
        <strong id="dashboard-card1-foot-val">{{ number_format($stats['total_leasable_area'] ?? 428430, 0, ',', '.') }} m²</strong>
      </div>
    </div>

    <!-- KPI 2: Hợp đồng & Khách thuê (Admin) / Tỷ lệ lấp đầy trung bình (PM theo feature/frontend) -->
    <div class="bms-kpi-card" style="cursor:pointer;" onclick="switchFilamentTab('tab-admin-contracts', document.getElementById('nav-item-contracts'))">
      <div class="kpi-admin-content">
        <div class="bms-kpi-top">
          <div class="bms-kpi-title">HỢP ĐỒNG & KHÁCH THUÊ</div>
          <div class="bms-kpi-icon" style="background:#eff6ff;color:#2563eb;">
            <i class="fa-solid fa-file-contract"></i>
          </div>
        </div>
        <div class="bms-kpi-main">
          <span class="bms-kpi-number" style="color:#2563eb;">{{ sprintf('%02d', count($recentContracts ?? [])) }}</span>
          <span style="font-size:13.5px;font-weight:600;color:#64748b;">hợp đồng</span>
          <span class="bms-kpi-pill" style="background:#eff6ff;color:#1d4ed8;">{{ $stats['total_tenants'] ?? 10 }} DN thuê</span>
        </div>
        <div class="bms-kpi-foot">
          <span>Diện tích đang khai thác:</span>
          <strong>{{ number_format($rentedArea, 0, ',', '.') }} m²</strong>
        </div>
      </div>
      <div class="kpi-pm-content" style="display:none;">
        <div class="bms-kpi-top">
          <div class="bms-kpi-title">TỶ LỆ LẤP ĐẦY TRUNG BÌNH</div>
          <div class="bms-kpi-icon" style="background:#eff6ff;color:#2563eb;">
            <i class="fa-solid fa-arrow-trend-up"></i>
          </div>
        </div>
        <div class="bms-kpi-main">
          <span class="bms-kpi-number" style="color:#2563eb;">84,6%</span>
          <span class="bms-kpi-pill" style="background:#ecfdf5;color:#047857;">+4,2% so tháng trước</span>
        </div>
        <div class="bms-kpi-foot">
          <span>Diện tích đang khai thác:</span>
          <strong>{{ number_format($rentedArea, 0, ',', '.') }} m²</strong>
        </div>
      </div>
    </div>

    <!-- KPI 3: Doanh thu & Công nợ kỳ (Admin) / Tổng diện tích đang quản lý (PM theo feature/frontend) -->
    <div class="bms-kpi-card" style="cursor:pointer;" onclick="if(window.currentUiRole==='admin'||window.currentUiRole==='super_admin'){ switchFilamentTab('tab-admin-billing', document.getElementById('nav-item-billing')); } else { switchFilamentTab('tab-admin-buildings', document.getElementById('nav-item-buildings')); }">
      <div class="kpi-admin-content">
        <div class="bms-kpi-top">
          <div class="bms-kpi-title">DOANH THU & CÔNG NỢ KỲ</div>
          <div class="bms-kpi-icon" style="background:#ecfdf5;color:#10b981;">
            <i class="fa-solid fa-file-invoice-dollar"></i>
          </div>
        </div>
        <div class="bms-kpi-main">
          <span class="bms-kpi-number" style="color:#10b981;font-size:28px;">{{ number_format(($stats['billing_total'] ?? 888387500) / 1000000, 1, ',', '.') }} Tr</span>
          <span class="bms-kpi-pill" style="background:#ecfdf5;color:#047857;">Đã thu {{ $stats['billing_paid_pct'] ?? 50.2 }}%</span>
        </div>
        <div class="bms-kpi-foot">
          <span>Đã đối soát:</span>
          <strong>{{ number_format(($stats['billing_paid'] ?? 445720000) / 1000000, 1, ',', '.') }} Tr ({{ count($recentDebitNotes ?? []) }} phiếu)</strong>
        </div>
      </div>
      <div class="kpi-pm-content" style="display:none;">
        <div class="bms-kpi-top">
          <div class="bms-kpi-title">TỔNG DIỆN TÍCH QUẢN LÝ</div>
          <div class="bms-kpi-icon" style="background:#ecfdf5;color:#10b981;">
            <i class="fa-solid fa-cube"></i>
          </div>
        </div>
        <div class="bms-kpi-main">
          <span class="bms-kpi-number" style="color:#10b981;font-size:26px;">{{ number_format($stats['total_leasable_area'] ?? 428430, 0, ',', '.') }}</span>
          <span style="font-size:13.5px;font-weight:600;color:#64748b;">m²</span>
          <span class="bms-kpi-pill" style="background:#ecfdf5;color:#047857;">Hạng A, B, C</span>
        </div>
        <div class="bms-kpi-foot">
          <span>Tình trạng sàn:</span>
          <strong>Tỷ lệ khai thác ổn định cao</strong>
        </div>
      </div>
    </div>

    <!-- KPI 4: Bảo trì & Leads mới (Admin) / Ticket bảo trì đang mở (PM theo feature/frontend) -->
    <div class="bms-kpi-card" style="cursor:pointer;" onclick="switchFilamentTab('tab-admin-tickets', document.getElementById('nav-item-tickets'))">
      <div class="kpi-admin-content">
        <div class="bms-kpi-top">
          <div class="bms-kpi-title">BẢO TRÌ & LEADS MỚI</div>
          <div class="bms-kpi-icon" style="background:#fef2f2;color:#e11d48;">
            <i class="fa-solid fa-screwdriver-wrench"></i>
          </div>
        </div>
        <div class="bms-kpi-main">
          <span class="bms-kpi-number" style="color:#e11d48;">{{ sprintf('%02d', $stats['open_tickets'] ?? 1) }}</span>
          <span style="font-size:13.5px;font-weight:600;color:#64748b;">ticket mở</span>
          <span class="bms-kpi-pill" style="background:#fef3c7;color:#b45309;">{{ count($leads ?? []) }} leads CRM</span>
        </div>
        <div class="bms-kpi-foot">
          <span>Mặt bằng cần chú ý:</span>
          <strong>{{ $expU }} sàn ({{ number_format($expArea, 0, ',', '.') }} m²)</strong>
        </div>
      </div>
      <div class="kpi-pm-content" style="display:none;">
        <div class="bms-kpi-top">
          <div class="bms-kpi-title">TICKET BẢO TRÌ ĐANG MỞ</div>
          <div class="bms-kpi-icon" style="background:#fef2f2;color:#e11d48;">
            <i class="fa-solid fa-screwdriver-wrench"></i>
          </div>
        </div>
        <div class="bms-kpi-main">
          <span class="bms-kpi-number" style="color:#e11d48;">{{ sprintf('%02d', $stats['open_tickets'] ?? 1) }}</span>
          <span style="font-size:13.5px;font-weight:600;color:#64748b;">phiếu sự cố</span>
          <span class="bms-kpi-pill" style="background:#fee2e2;color:#b91c1c;">Xử lý trong ngày</span>
        </div>
        <div class="bms-kpi-foot">
          <span>Mặt bằng cần chú ý:</span>
          <strong>{{ $expU }} sàn ({{ number_format($expArea, 0, ',', '.') }} m²)</strong>
        </div>
      </div>
    </div>
  </div>

  <!-- PHÂN HỆ VẬN HÀNH DÀNH RIÊNG CHO BAN QUẢN LÝ (PM WORKFLOW THEO FEATURE/FRONTEND) -->
  <div id="dashboard-section-pm" style="display:none;margin-top:20px;">
    <div style="display:grid;grid-template-columns:1.5fr 1fr;gap:24px;margin-bottom:24px;">
      <!-- Khối trái: Biểu đồ cột Tỷ Lệ Lấp Đầy & Diện Tích Thuê 6 Tháng Gần Nhất -->
      <div class="bms-panel-card">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;">
          <div>
            <h3 style="font-size:16px;font-weight:800;color:#0f172a;margin:0 0 4px 0;display:flex;align-items:center;gap:8px;">
              <i class="fa-solid fa-chart-column" style="color:#2563eb;"></i> Tỷ Lệ Lấp Đầy & Diện Tích Thuê (6 Tháng Gần Nhất)
            </h3>
            <p style="font-size:12px;color:#64748b;margin:0;">Thống kê tiến độ phủ đầy mặt bằng tại 8 cao ốc Ban Quản Lý phụ trách</p>
          </div>
          <span class="badge-filament info" style="font-weight:700;">Năm 2026</span>
        </div>

        <div style="padding:10px 0 16px;">
          <div style="display:flex;align-items:flex-end;gap:16px;height:210px;padding-top:20px;border-bottom:1px solid #e2e8f0;">
            <div style="flex:1;display:flex;flex-direction:column;align-items:center;height:100%;justify-content:flex-end;">
              <div style="font-size:11px;font-weight:700;color:#64748b;margin-bottom:4px;">76%</div>
              <div style="width:100%;height:76%;background:linear-gradient(180deg,#bfdbfe,#3b82f6);border-radius:6px 6px 0 0;"></div>
              <div style="font-size:11px;margin-top:8px;color:#64748b;font-weight:600;">T6</div>
            </div>
            <div style="flex:1;display:flex;flex-direction:column;align-items:center;height:100%;justify-content:flex-end;">
              <div style="font-size:11px;font-weight:700;color:#64748b;margin-bottom:4px;">78%</div>
              <div style="width:100%;height:78%;background:linear-gradient(180deg,#bfdbfe,#3b82f6);border-radius:6px 6px 0 0;"></div>
              <div style="font-size:11px;margin-top:8px;color:#64748b;font-weight:600;">T7</div>
            </div>
            <div style="flex:1;display:flex;flex-direction:column;align-items:center;height:100%;justify-content:flex-end;">
              <div style="font-size:11px;font-weight:700;color:#64748b;margin-bottom:4px;">80%</div>
              <div style="width:100%;height:80%;background:linear-gradient(180deg,#bfdbfe,#3b82f6);border-radius:6px 6px 0 0;"></div>
              <div style="font-size:11px;margin-top:8px;color:#64748b;font-weight:600;">T8</div>
            </div>
            <div style="flex:1;display:flex;flex-direction:column;align-items:center;height:100%;justify-content:flex-end;">
              <div style="font-size:11px;font-weight:700;color:#64748b;margin-bottom:4px;">81%</div>
              <div style="width:100%;height:81%;background:linear-gradient(180deg,#bfdbfe,#3b82f6);border-radius:6px 6px 0 0;"></div>
              <div style="font-size:11px;margin-top:8px;color:#64748b;font-weight:600;">T9</div>
            </div>
            <div style="flex:1;display:flex;flex-direction:column;align-items:center;height:100%;justify-content:flex-end;">
              <div style="font-size:11px;font-weight:700;color:#64748b;margin-bottom:4px;">83%</div>
              <div style="width:100%;height:83%;background:linear-gradient(180deg,#bfdbfe,#3b82f6);border-radius:6px 6px 0 0;"></div>
              <div style="font-size:11px;margin-top:8px;color:#64748b;font-weight:600;">T10</div>
            </div>
            <div style="flex:1;display:flex;flex-direction:column;align-items:center;height:100%;justify-content:flex-end;">
              <div style="font-size:11px;font-weight:800;color:#ea580c;margin-bottom:4px;">84.6%</div>
              <div style="width:100%;height:84.6%;background:linear-gradient(180deg,#f59e0b,#ea580c);border-radius:6px 6px 0 0;box-shadow:0 4px 10px rgba(234,88,12,0.3);"></div>
              <div style="font-size:11px;margin-top:8px;color:#ea580c;font-weight:700;">T11 (Nay)</div>
            </div>
          </div>
        </div>

        <div style="margin-top:12px;display:flex;justify-content:space-between;align-items:center;font-size:12.5px;color:#64748b;">
          <span><i class="fa-solid fa-arrow-trend-up" style="color:#16a34a;"></i> Tỷ lệ lấp đầy tăng liên tục qua 6 tháng (+8,6%)</span>
          <strong style="color:#0f172a;">8 tòa nhà phân công quản trị</strong>
        </div>
      </div>

      <!-- Khối phải: Việc cần phê duyệt ngay (BQL & Kỹ thuật) -->
      <div class="bms-panel-card">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
          <div>
            <h3 style="font-size:16px;font-weight:800;color:#0f172a;margin:0 0 2px 0;display:flex;align-items:center;gap:8px;">
              <i class="fa-solid fa-list-check" style="color:#f59e0b;"></i> Việc Cần Phê Duyệt Ngay
            </h3>
            <p style="font-size:12px;color:#64748b;margin:0;">Yêu cầu vận hành chờ Trưởng ban duyệt</p>
          </div>
          <span class="badge-filament warning" style="font-weight:700;">4 mục</span>
        </div>

        <div style="display:flex;flex-direction:column;gap:10px;">
          <div style="padding:10px 12px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;display:flex;justify-content:space-between;align-items:center;">
            <div>
              <div style="font-weight:700;font-size:13px;color:#0f172a;">Duyệt thi công ngoài giờ (Tầng 12)</div>
              <div style="font-size:11.5px;color:#64748b;">Công ty ABC Tech • Vietcombank Tower</div>
            </div>
            <button class="btn-mo-primary" style="padding:5px 12px;font-size:12px;" onclick="alert('Đã phê duyệt giấy phép thi công ngoài giờ cho Công ty ABC Tech!')">Duyệt</button>
          </div>

          <div style="padding:10px 12px;background:#fffbeb;border:1px solid #fde68a;border-radius:8px;display:flex;justify-content:space-between;align-items:center;">
            <div>
              <div style="font-weight:700;font-size:13px;color:#92400e;">Bảo trì điều hòa trung tâm phòng Server</div>
              <div style="font-size:11.5px;color:#b45309;">Deutsches Haus • Yêu cầu khẩn cấp trong ngày</div>
            </div>
            <button class="btn-mo-orange" style="padding:5px 12px;font-size:12px;" onclick="alert('Đã phân công kỹ sư BQL tới hiện trường xử lý!')">Giao việc</button>
          </div>

          <div style="padding:10px 12px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;display:flex;justify-content:space-between;align-items:center;">
            <div>
              <div style="font-weight:700;font-size:13px;color:#0f172a;">Gia hạn hợp đồng thuê thêm 2 năm</div>
              <div style="font-size:11.5px;color:#64748b;">Tập đoàn Nova Logistics • Lim Tower</div>
            </div>
            <button class="btn-mo-outline" style="padding:5px 12px;font-size:12px;" onclick="alert('Đã mở hồ sơ gia hạn hợp đồng thuê!')">Xem</button>
          </div>

          <div style="padding:10px 12px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;display:flex;justify-content:space-between;align-items:center;">
            <div>
              <div style="font-weight:700;font-size:13px;color:#0f172a;">Nghiệm thu khắc phục rò rỉ nước phòng máy</div>
              <div style="font-size:11.5px;color:#64748b;">Saigon Centre Tower 2 • Đã hoàn thành sửa chữa</div>
            </div>
            <button class="btn-mo-primary" style="padding:5px 12px;font-size:12px;background:#10b981;border-color:#10b981;" onclick="alert('Đã xác nhận nghiệm thu kỹ thuật!')">Nghiệm thu</button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- PHÂN HỆ TÀI CHÍNH & TÀI SẢN DÀNH CHO QUẢN TRỊ VIÊN HỆ THỐNG (ADMIN SECTION) -->
  <div id="dashboard-section-admin">
  <!-- HÀNG 2: PHÂN TÍCH TRẠNG THÁI MẶT BẰNG (DONUT CHART) & HIỆU SUẤT KHAI THÁC THEO TÒA NHÀ -->
  <div class="bms-analytics-grid">
    <!-- Cột trái: Phân bổ trạng thái mặt bằng (Donut Chart) -->
    <div class="bms-panel-card">
      <div style="margin-bottom:16px;">
        <h3 style="font-size:16px;font-weight:800;color:#0f172a;margin:0 0 4px 0;">Quản Lý Trạng Thái Mặt Bằng</h3>
        <p style="font-size:12px;color:#64748b;margin:0;line-height:1.4;">Phân bổ {{ $totalU }} mặt bằng ({{ number_format($totalUnitsArea, 0, ',', '.') }} m²) trên {{ $totalBlds }} tòa nhà</p>
      </div>

      <!-- Biểu đồ Donut SVG động 100% theo dữ liệu CSDL -->
      <div style="display:flex;justify-content:center;align-items:center;padding:10px 0 18px;">
        <div style="position:relative;width:168px;height:168px;display:flex;align-items:center;justify-content:center;">
          <svg width="168" height="168" viewBox="0 0 42 42" style="transform:rotate(-90deg);">
            <circle cx="21" cy="21" r="15.9155" fill="transparent" stroke="#f1f5f9" stroke-width="5"></circle>
            <!-- Đang thuê ổn định -->
            <circle cx="21" cy="21" r="15.9155" fill="transparent" stroke="#2563eb" stroke-width="5" stroke-dasharray="{{ $rentedPct }} {{ 100 - $rentedPct }}" stroke-dashoffset="0"></circle>
            <!-- Trống sẵn sàng -->
            <circle cx="21" cy="21" r="15.9155" fill="transparent" stroke="#10b981" stroke-width="5" stroke-dasharray="{{ $availPct }} {{ 100 - $availPct }}" stroke-dashoffset="{{ $offsetAvail }}"></circle>
            <!-- Sắp hết hạn & bảo trì -->
            <circle cx="21" cy="21" r="15.9155" fill="transparent" stroke="#e11d48" stroke-width="5" stroke-dasharray="{{ $expPct }} {{ 100 - $expPct }}" stroke-dashoffset="{{ $offsetExp }}"></circle>
          </svg>
          <div style="position:absolute;text-align:center;">
            <div style="font-size:24px;font-weight:800;color:#0f172a;line-height:1.1;">{{ $rentedPct }}%</div>
            <div style="font-size:10px;font-weight:700;color:#64748b;letter-spacing:0.5px;margin-top:2px;">ĐÃ CHO THUÊ</div>
          </div>
        </div>
      </div>

      <!-- Chú thích 3 trạng thái mặt bằng thực tế (kèm cả số căn và diện tích m²) -->
      <div style="border-top:1px solid #f1f5f9;padding-top:14px;display:flex;flex-direction:column;gap:10px;font-size:12.5px;">
        <div style="display:flex;justify-content:space-between;align-items:center;">
          <span style="display:flex;align-items:center;gap:8px;color:#475569;">
            <span style="width:8px;height:8px;border-radius:50%;background:#2563eb;display:inline-block;"></span>
            Đang thuê ổn định
          </span>
          <strong style="color:#0f172a;">{{ $rentedU }} căn • {{ number_format($rentedArea, 0, ',', '.') }} m² ({{ $rentedPct }}%)</strong>
        </div>
        <div style="display:flex;justify-content:space-between;align-items:center;">
          <span style="display:flex;align-items:center;gap:8px;color:#475569;">
            <span style="width:8px;height:8px;border-radius:50%;background:#10b981;display:inline-block;"></span>
            Trống sẵn sàng bàn giao
          </span>
          <strong style="color:#0f172a;">{{ $availU }} căn • {{ number_format($availArea, 0, ',', '.') }} m² ({{ $availPct }}%)</strong>
        </div>
        <div style="display:flex;justify-content:space-between;align-items:center;">
          <span style="display:flex;align-items:center;gap:8px;color:#475569;">
            <span style="width:8px;height:8px;border-radius:50%;background:#e11d48;display:inline-block;"></span>
            Sắp hết hạn / Bảo trì
          </span>
          <strong style="color:#0f172a;">{{ $expU }} căn • {{ number_format($expArea, 0, ',', '.') }} m² ({{ $expPct }}%)</strong>
        </div>
      </div>
    </div>

    <!-- Cột phải: Hiệu Suất Lấp Đầy Theo Tòa Nhà (Truy vấn trực tiếp từ CSDL) -->
    <div class="bms-panel-card">
      <div>
        <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:12px;margin-bottom:20px;">
          <div>
            <h3 style="font-size:16px;font-weight:800;color:#0f172a;margin:0 0 4px 0;">Hiệu Suất Lấp Đầy Theo Tòa Nhà</h3>
            <p style="font-size:12px;color:#64748b;margin:0;">Thống kê mặt bằng đang cho thuê thực tế tại các tòa nhà trọng điểm</p>
          </div>
          <div style="display:flex;align-items:center;gap:12px;font-size:11.5px;color:#64748b;">
            <span style="display:inline-flex;align-items:center;gap:5px;">
              <span style="width:9px;height:9px;border-radius:2px;background:#2563eb;display:inline-block;"></span> Đang khai thác
            </span>
            <span style="display:inline-flex;align-items:center;gap:5px;">
              <span style="width:9px;height:9px;border-radius:2px;background:#f59e0b;display:inline-block;"></span> Còn nhiều sàn trống
            </span>
          </div>
        </div>

        <div id="dashboard-perf-building-box">
          @forelse($buildingPerfs as $idx => $bp)
            <div class="bms-building-perf-item" style="{{ $loop->last ? 'margin-bottom:4px;' : '' }}cursor:pointer;padding:8px 10px;margin-left:-10px;margin-right:-10px;border-radius:8px;transition:background 0.15s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'" onclick="switchFilamentTab('tab-admin-buildings', document.getElementById('nav-item-buildings')); setTimeout(() => { if(typeof openManageBuildingUnitsModal === 'function') openManageBuildingUnitsModal('{{ $bp['id'] }}', '{{ addslashes($bp['name']) }}'); }, 200);" title="Bấm để xem & quản lý các mặt bằng của {{ $bp['name'] }}">
              <div class="bms-building-perf-head">
                <div style="display:flex;align-items:center;gap:8px;">
                  <strong style="color:#0f172a;">{{ $bp['name'] }} ({{ $bp['district'] }})</strong>
                  <span style="background:#f1f5f9;color:#475569;font-size:11px;padding:2px 7px;border-radius:5px;font-weight:600;">{{ $bp['total_units'] }} mặt bằng</span>
                </div>
                <div>
                  <span style="color:#64748b;font-size:12px;margin-right:6px;">
                    Đã thuê {{ $bp['leased_units'] }}/{{ $bp['total_units'] }} sàn (còn {{ $bp['available_units'] ?? ($bp['total_units'] - $bp['leased_units']) }} trống)
                  </span>
                  <strong style="color:{{ $bp['bar_color'] ?? '#2563eb' }};font-size:13px;">{{ number_format($bp['occupancy_pct'], 1) }}%</strong>
                </div>
              </div>
              <div class="bms-progress-track">
                <div class="bms-progress-fill" style="width:{{ min(100, $bp['occupancy_pct']) }}%;background:{{ $bp['bar_color'] ?? '#2563eb' }};"></div>
              </div>
            </div>
          @empty
            <div style="padding:20px;text-align:center;color:#94a3b8;">Chưa có dữ liệu mặt bằng theo tòa nhà.</div>
          @endforelse
        </div>
      </div>

      <!-- Footer Nhận xét tự động từ dữ liệu thật -->
      <div style="border-top:1px solid #f1f5f9;padding-top:14px;margin-top:14px;display:flex;justify-content:space-between;align-items:center;gap:12px;font-size:12.5px;">
        <div style="display:flex;align-items:center;gap:8px;color:#475569;">
          <i class="fa-solid fa-circle-info" style="color:#2563eb;font-size:14px;"></i>
          <span>
            @if(!empty($buildingPerfs))
              <strong>{{ $buildingPerfs[0]['name'] }}</strong> dẫn đầu tỷ lệ khai thác ({{ $buildingPerfs[0]['leased_units'] }}/{{ $buildingPerfs[0]['total_units'] }} mặt bằng), toàn hệ thống hiện có <strong>{{ $availU }} mặt bằng trống</strong> ({{ number_format($availArea, 0, ',', '.') }} m²) sẵn sàng chào thuê.
            @else
              Thống kê hiệu suất lấp đầy thực tế tại các tòa nhà trọng điểm.
            @endif
          </span>
        </div>
        <a href="javascript:void(0)" onclick="switchFilamentTab('tab-admin-buildings', document.getElementById('nav-item-buildings'))" style="color:#2563eb;font-weight:700;white-space:nowrap;text-decoration:none;">
          Quản lý tòa nhà <i class="fa-solid fa-chevron-right" style="font-size:10px;"></i>
        </a>
      </div>
    </div>
  </div>

  <!-- HÀNG 3: THEO DÕI TRỌNG ĐIỂM TỪ 3 PHÂN HỆ (HỢP ĐỒNG - CÔNG NỢ - BẢO TRÌ) -->
  <div>
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
      <div style="display:flex;align-items:center;gap:10px;">
        <h3 style="font-size:16px;font-weight:800;color:#0f172a;margin:0;">Theo Dõi Trọng Điểm: Hợp Đồng • Công Nợ • Bảo Trì</h3>
      </div>
      <a href="javascript:void(0)" onclick="switchFilamentTab('tab-admin-contracts', document.getElementById('nav-item-contracts'))" style="font-size:12.5px;font-weight:700;color:#2563eb;text-decoration:none;">
        Xem tất cả hợp đồng <i class="fa-solid fa-chevron-right" style="font-size:10px;"></i>
      </a>
    </div>

    <div class="bms-alerts-grid">
      <!-- Card 1: Hợp đồng đang hiệu lực (Từ bảng contracts) -->
      <div class="bms-alert-card">
        <div>
          <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
            <span style="background:#ecfdf5;color:#047857;font-size:11px;font-weight:700;padding:3px 9px;border-radius:6px;">
              ● Hợp đồng đang hiệu lực
            </span>
            <span style="font-family:monospace;font-size:11.5px;color:#64748b;font-weight:700;">{{ $firstContract?->contract_number ?? $firstContract?->contract_code ?? 'HD-2026/01' }}</span>
          </div>
          <h4 style="font-size:14.5px;font-weight:800;color:#0f172a;margin:0 0 4px 0;">{{ $firstContract?->tenant?->company_name ?? 'Chưa có khách thuê' }}</h4>
          <p style="font-size:12.5px;color:#64748b;margin:0;">
            Mã sàn: <strong>{{ $firstContract?->units?->first()?->unit_code ?? 'NEX-0801' }}</strong>
            • {{ number_format($firstContract?->total_leased_area_sqm ?? 200, 0, ',', '.') }} m²
            • {{ $firstContract?->building?->name ?? 'Tòa Nhà The Nexus Tower' }}
          </p>
        </div>
        <div style="display:flex;justify-content:space-between;align-items:center;padding-top:12px;border-top:1px solid #f1f5f9;font-size:12px;">
          <span style="color:#64748b;">Hiệu lực đến: <strong style="color:#0f172a;">{{ $firstContract?->end_date ? \Carbon\Carbon::parse($firstContract->end_date)->format('d/m/Y') : '31/12/2028' }}</strong></span>
          <button type="button" class="btn-mo-outline" style="padding:5px 12px;font-size:12px;border-radius:7px;" onclick="switchFilamentTab('tab-admin-contracts', document.getElementById('nav-item-contracts'))">
            Chi tiết HĐ
          </button>
        </div>
      </div>

      <!-- Card 2: Thông báo phí chờ thanh toán (Admin) / Việc cần phê duyệt ngay (PM theo feature/frontend) -->
      <div class="bms-alert-card kpi-admin-content">
        <div>
          <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
            <span style="background:#fef3c7;color:#b45309;font-size:11px;font-weight:700;padding:3px 9px;border-radius:6px;">
              ● Công nợ chờ thanh toán
            </span>
            <span style="font-family:monospace;font-size:11.5px;color:#64748b;font-weight:700;">{{ $pendingDebit?->debit_note_code ?? 'DN-202611-0001' }}</span>
          </div>
          <h4 style="font-size:14.5px;font-weight:800;color:#0f172a;margin:0 0 4px 0;">{{ $pendingDebit?->tenant?->company_name ?? 'CÔNG TY CỔ PHẦN VNG' }}</h4>
          <p style="font-size:12.5px;color:#64748b;margin:0;">
            Cần thu: <strong style="color:#ea580c;">{{ number_format($pendingDebit?->total_amount ?? 442667500, 0, ',', '.') }} đ</strong>
            • Kỳ {{ $pendingDebit?->billing_month ?? 11 }}/{{ $pendingDebit?->billing_year ?? 2026 }}
            • {{ $pendingDebit?->building?->name ?? 'Tòa Nhà The Nexus Tower' }}
          </p>
        </div>
        <div style="display:flex;justify-content:space-between;align-items:center;padding-top:12px;border-top:1px solid #f1f5f9;font-size:12px;">
          <span style="color:#64748b;">Hạn đóng: <strong style="color:#d97706;">{{ $pendingDebit?->due_date ? \Carbon\Carbon::parse($pendingDebit->due_date)->format('d/m/Y') : '15/11/2026' }}</strong></span>
          <button type="button" style="background:#eff6ff;border:1px solid #bfdbfe;color:#1d4ed8;font-weight:700;padding:5px 12px;font-size:12px;border-radius:7px;cursor:pointer;" onclick="switchFilamentTab('tab-admin-billing', document.getElementById('nav-item-billing'))">
            Đối soát thu
          </button>
        </div>
      </div>

      <!-- Card 2 (PM view theo feature/frontend): Việc cần phê duyệt ngay -->
      <div class="bms-alert-card kpi-pm-content" style="display:none;">
        <div>
          <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
            <span style="background:#fef3c7;color:#b45309;font-size:11px;font-weight:700;padding:3px 9px;border-radius:6px;">
              ● Việc Cần Phê Duyệt Ngay (BQL)
            </span>
            <span style="background:#fee2e2;color:#b91c1c;font-size:11px;font-weight:700;padding:2px 8px;border-radius:4px;">Khẩn cấp</span>
          </div>
          <h4 style="font-size:14.5px;font-weight:800;color:#0f172a;margin:0 0 4px 0;">Duyệt thi công ngoài giờ (Tầng 12)</h4>
          <p style="font-size:12.5px;color:#64748b;margin:0;">
            Công ty ABC Tech • Tòa nhà Vietcombank Tower • Đăng ký lắp đặt mạng cáp
          </p>
        </div>
        <div style="display:flex;justify-content:space-between;align-items:center;padding-top:12px;border-top:1px solid #f1f5f9;font-size:12px;">
          <span style="color:#64748b;">Thời hạn: <strong style="color:#d97706;">Hôm nay 18:00</strong></span>
          <button type="button" class="btn-mo-primary" style="padding:5px 12px;font-size:12px;border-radius:7px;" onclick="alert('Đã phê duyệt giấy phép thi công ngoài giờ thành công!')">
            Duyệt thi công
          </button>
        </div>
      </div>

      <!-- Card 3: Phiếu bảo trì kỹ thuật đang xử lý (Từ bảng tickets) -->
      <div class="bms-alert-card">
        <div>
          <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
            <span style="background:#fef2f2;color:#e11d48;font-size:11px;font-weight:700;padding:3px 9px;border-radius:6px;">
              ● Bảo trì: {{ $tPriorityLabel }}
            </span>
            <span style="font-family:monospace;font-size:11.5px;color:#64748b;font-weight:700;">{{ $firstTicket?->ticket_code ?? 'TK-202611-0001' }}</span>
          </div>
          <h4 style="font-size:14.5px;font-weight:800;color:#0f172a;margin:0 0 4px 0;">{{ $firstTicket?->title ?? 'Kiểm tra bảo dưỡng hệ thống kỹ thuật' }}</h4>
          <p style="font-size:12.5px;color:#64748b;margin:0;">
            {{ $firstTicket?->tenant?->company_name ?? 'Khách thuê' }}
            • {{ $firstTicket?->building?->name ?? 'Tòa nhà văn phòng' }}
          </p>
        </div>
        <div style="display:flex;justify-content:space-between;align-items:center;padding-top:12px;border-top:1px solid #f1f5f9;font-size:12px;">
          <span style="color:#64748b;">Trạng thái: <strong style="color:#d97706;">{{ $tStatusLabel }}</strong></span>
          <button type="button" class="btn-mo-outline" style="padding:5px 12px;font-size:12px;border-radius:7px;" onclick="switchFilamentTab('tab-admin-tickets', document.getElementById('nav-item-tickets'))">
            Phiếu bảo trì
          </button>
        </div>
      </div>
    </div>
  </div>
  </div>
</div>
