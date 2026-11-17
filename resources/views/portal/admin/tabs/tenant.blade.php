<!-- TAB 8: TENANT WORKSPACE (Không gian làm việc cho doanh nghiệp thuê - Chuẩn VIETOFFICE BMS Hình 2) -->
<div id="tab-tenant-workspace" class="filament-tab-pane" style="display:none;">
  <div class="filament-page-header" style="align-items:center;">
    <div>
      <h1 class="filament-page-title" id="tenant-welcome-title" style="font-size:23px;margin-bottom:4px;">Văn Phòng Của Tôi & Hồ Sơ Hợp Đồng Thuê</h1>
      <p style="font-size:13.5px;color:#64748b;margin:0;">Quản lý thông tin mặt bằng đang thuê, thời hạn hợp đồng, hồ sơ pháp lý và các thông báo vận hành từ Ban Quản Lý.</p>
    </div>
    <div style="display:flex;gap:8px;">
      <button class="btn-mo-outline" style="background:#fff;" onclick="loadTenantWorkspaceContracts()">
        <i class="fa-solid fa-arrows-rotate"></i> Làm mới
      </button>
      <button class="btn-mo-primary" style="background:#2563eb;" onclick="openTenantTicketModal()">
        <i class="fa-solid fa-plus"></i> Gửi yêu cầu hỗ trợ
      </button>
    </div>
  </div>

  <!-- 4 KPI Cards cho Cổng Khách Thuê đồng bộ thiết kế Hình 2 -->
  <div class="bms-kpi-grid">
    <div class="bms-kpi-card">
      <div>
        <div class="bms-kpi-top">
          <div class="bms-kpi-title">HỢP ĐỒNG ĐANG HIỆU LỰC</div>
          <div class="bms-kpi-icon" style="background:#eff6ff;color:#2563eb;"><i class="fa-solid fa-file-contract"></i></div>
        </div>
        <div class="bms-kpi-main">
          <span class="bms-kpi-number" style="color:#0f172a;font-size:26px;">01</span>
          <span class="bms-kpi-pill" style="background:#ecfdf5;color:#047857;">Đang hoạt động</span>
        </div>
      </div>
      <div class="bms-kpi-foot"><span>Mã HĐ:</span><strong>{{ $sampleContract->contract_code ?? 'HD-2026-0001' }}</strong></div>
    </div>

    <div class="bms-kpi-card">
      <div>
        <div class="bms-kpi-top">
          <div class="bms-kpi-title">DIỆN TÍCH SÀN THUÊ</div>
          <div class="bms-kpi-icon" style="background:#ecfdf5;color:#10b981;"><i class="fa-solid fa-ruler-combined"></i></div>
        </div>
        <div class="bms-kpi-main">
          <span class="bms-kpi-number" style="color:#10b981;font-size:26px;">350 m²</span>
          <span class="bms-kpi-pill" style="background:#eff6ff;color:#1d4ed8;">Hạng A</span>
        </div>
      </div>
      <div class="bms-kpi-foot"><span>Tòa nhà:</span><strong>{{ $sampleContract?->building?->name ?? 'Vietcombank Tower' }}</strong></div>
    </div>

    <div class="bms-kpi-card">
      <div>
        <div class="bms-kpi-top">
          <div class="bms-kpi-title">CƯỚC PHÍ KỲ HIỆN TẠI</div>
          <div class="bms-kpi-icon" style="background:#fef3c7;color:#d97706;"><i class="fa-solid fa-file-invoice-dollar"></i></div>
        </div>
        <div class="bms-kpi-main">
          <span class="bms-kpi-number" style="color:#d97706;font-size:24px;">{{ number_format(($sampleDebit->total_amount ?? 245000000) / 1000000, 1, ',', '.') }} Tr</span>
          <span class="bms-kpi-pill" style="background:#fef3c7;color:#b45309;">Kỳ 11/2026</span>
        </div>
      </div>
      <div class="bms-kpi-foot"><span>Hạn thanh toán:</span><strong>05/12/2026</strong></div>
    </div>

    <div class="bms-kpi-card">
      <div>
        <div class="bms-kpi-top">
          <div class="bms-kpi-title">TICKET HỖ TRỢ KỸ THUẬT</div>
          <div class="bms-kpi-icon" style="background:#f3e8ff;color:#7e22ce;"><i class="fa-solid fa-screwdriver-wrench"></i></div>
        </div>
        <div class="bms-kpi-main">
          <span class="bms-kpi-number" style="color:#7e22ce;font-size:26px;">02</span>
          <span class="bms-kpi-pill" style="background:#f3e8ff;color:#6d28d9;">BQL đang xử lý</span>
        </div>
      </div>
      <div class="bms-kpi-foot"><span>Cam kết SLA:</span><strong>Phản hồi &lt; 30 phút</strong></div>
    </div>
  </div>

  <div id="tenant-workspace-contracts-container">
    <!-- Rendered via JS contracts.blade.php (loadTenantWorkspaceContracts) -->
  </div>
</div>

<!-- TAB 9: TENANT TICKET MANAGEMENT (Module 4) -->
<div id="tab-tenant-tickets" class="filament-tab-pane" style="display:none;">
    <div class="filament-page-header" style="align-items:center;">
        <div>
            <h1 class="filament-page-title" style="font-size:23px;margin-bottom:4px;">Gửi Yêu Cầu Bảo Trì & Đăng Ký Thi Công</h1>
            <p style="font-size:13.5px;color:#64748b;margin:0;">Báo cáo sự cố kỹ thuật (điện, điều hòa, mạng, vệ sinh), theo dõi tiến độ xử lý và nghiệm thu đánh giá dịch vụ BQL.</p>
        </div>
        <div style="display:flex;gap:8px;">
            <button class="btn-mo-outline" style="background:#fff;" onclick="loadTicketsList('tenant')">
                <i class="fa-solid fa-arrows-rotate"></i> Làm mới
            </button>
            <button class="btn-mo-primary" style="background:#2563eb;" onclick="openTenantTicketModal()">
                <i class="fa-solid fa-plus"></i> Tạo Ticket mới
            </button>
        </div>
    </div>

    <div class="filament-card">
        <table class="mo-table">
            <thead>
                <tr>
                    <th>Mã Ticket</th>
                    <th>Loại Yêu Cầu</th>
                    <th>Tiêu Đề / Mô Tả</th>
                    <th>Thời Gian Tạo</th>
                    <th>Mức Ưu Tiên</th>
                    <th>Trạng Thái Xử Lý</th>
                    <th style="text-align:right;">Thao Tác</th>
                </tr>
            </thead>
            <tbody id="tenant-tickets-tbody">
                <!-- Rendered via JS -->
            </tbody>
        </table>
    </div>
</div>

<!-- TAB 10: TENANT BILLS & PAYMENTS (Module 3) -->
<div id="tab-tenant-bills" class="filament-tab-pane" style="display:none;">
    <div class="filament-page-header" style="align-items:center;">
        <div>
            <h1 class="filament-page-title" style="font-size:23px;margin-bottom:4px;">Hóa Đơn Thuê Văn Phòng & Cước Phí Hàng Tháng</h1>
            <p style="font-size:13.5px;color:#64748b;margin:0;">Tra cứu thông báo phí (Debit Note), chi tiết tiền thuê, phí quản lý, điện nước và thanh toán nhanh qua mã QR.</p>
        </div>
        <button type="button" class="btn-mo-primary" style="background:#2563eb;" onclick="alert('Đã tải xuống bảng kê chi tiết hóa đơn GTGT kỳ 11/2026!')">
            <i class="fa-solid fa-download"></i> Tải hóa đơn PDF
        </button>
    </div>

    <div class="filament-card">
        <table class="mo-table">
            <thead>
                <tr>
                    <th>Mã Hóa Đơn</th>
                    <th>Tòa Nhà & Văn Phòng</th>
                    <th>Kỳ Thanh Toán</th>
                    <th>Tiền Thuê & Dịch Vụ</th>
                    <th>Tổng Cộng (Gồm VAT)</th>
                    <th>Hạn Thanh Toán</th>
                    <th>Trạng Thái</th>
                    <th style="text-align:right;">Thanh Toán</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentDebitNotes ?? [] as $dn)
                  @php
                    $stClass = ($dn->status === 'PAID') ? 'success' : 'warning';
                    $stText = ($dn->status === 'PAID') ? 'Đã thanh toán' : 'Chờ thanh toán';
                  @endphp
                  <tr>
                    <td><strong style="font-family:monospace;color:#1d4ed8;">{{ $dn->debit_note_code ?? 'DN-202611-01' }}</strong></td>
                    <td><strong>{{ $dn->building?->name ?? 'Vietcombank Tower' }}</strong></td>
                    <td><span class="badge-filament info">{{ $dn->billing_month ?? 11 }}/{{ $dn->billing_year ?? 2026 }}</span></td>
                    <td>{{ number_format(($dn->total_amount ?? 245000000) * 0.9, 0, ',', '.') }} đ</td>
                    <td><strong style="color:#ea580c;">{{ number_format($dn->total_amount ?? 245000000, 0, ',', '.') }} đ</strong></td>
                    <td>{{ $dn->due_date ? \Carbon\Carbon::parse($dn->due_date)->format('d/m/Y') : '05/12/2026' }}</td>
                    <td><span class="badge-filament {{ $stClass }}">{{ $stText }}</span></td>
                    <td style="text-align:right;">
                      <button type="button" class="btn-mo-primary" style="padding:5px 12px;font-size:12px;background:#2563eb;" onclick="alert('Mã QR thanh toán hóa đơn {{ $dn->debit_note_code ?? 'DN-202611' }} đã sẵn sàng!')">
                        <i class="fa-solid fa-qrcode"></i> Quét mã QR
                      </button>
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td><strong style="font-family:monospace;color:#1d4ed8;">DN-202611-001</strong></td>
                    <td><strong>Vietcombank Tower</strong></td>
                    <td><span class="badge-filament info">11/2026</span></td>
                    <td>300.000.000 đ</td>
                    <td><strong style="color:#ea580c;">330.000.000 đ</strong></td>
                    <td>05/12/2026</td>
                    <td><span class="badge-filament warning">Chờ thanh toán</span></td>
                    <td style="text-align:right;">
                      <button type="button" class="btn-mo-primary" style="padding:5px 12px;font-size:12px;background:#2563eb;" onclick="alert('Mã QR thanh toán đã sẵn sàng!')">
                        <i class="fa-solid fa-qrcode"></i> Quét mã QR
                      </button>
                    </td>
                  </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
