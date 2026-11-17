<!-- ==========================================================================
     PHÂN HỆ CỔNG THÔNG TIN DOANH NGHIỆP THUÊ (TENANT PORTAL)
     ĐÁP ỨNG ĐẦY ĐỦ 4 TIÊU CHÍ NGHIỆP VỤ ROLE 2: KHÁCH THUÊ
     ========================================================================== -->

<!-- TAB 1: QUẢN LÝ HỒ SƠ DOANH NGHIỆP & GIẤY PHÉP KINH DOANH -->
<div id="tab-tenant-profile" class="filament-tab-pane" style="display:none;">
  <div class="filament-page-header" style="align-items:center;">
    <div>
      <h1 class="filament-page-title" style="font-size:23px;margin-bottom:4px;">Hồ Sơ Doanh Nghiệp Khách Thuê</h1>
      <p style="font-size:13.5px;color:#64748b;margin:0;">Quản lý thông tin pháp lý công ty, người đại diện pháp luật và giấy phép đăng ký kinh doanh (GPKD).</p>
    </div>
  </div>

  <div style="display:grid;grid-template-columns:1.2fr 1fr;gap:20px;margin-bottom:24px;">
    <!-- Khối 1: Thông tin pháp lý công ty & Người đại diện -->
    <div class="filament-card" style="margin-bottom:0;">
      <div style="border-bottom:1px solid #f1f5f9;padding-bottom:12px;margin-bottom:16px;display:flex;justify-content:space-between;align-items:center;">
        <h3 style="font-size:16px;font-weight:700;color:#0f172a;margin:0;display:flex;align-items:center;gap:8px;">
          <i class="fa-solid fa-building" style="color:#2563eb;"></i> Thông Tin Pháp Lý Doanh Nghiệp
        </h3>
        <span class="badge-filament success" id="tp-badge-status"><i class="fa-solid fa-circle-check"></i> Đang hoạt động</span>
      </div>

      <form id="form-tenant-company-profile" onsubmit="handleSaveTenantProfile(event)">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:14px;">
          <div>
            <label style="font-size:12px;font-weight:700;color:#475569;display:block;margin-bottom:4px;">TÊN DOANH NGHIỆP</label>
            <input type="text" id="tp-company-name" class="filament-input" value="{{ $sampleTenant->company_name ?? '' }}" placeholder="Nhập tên doanh nghiệp..." required style="width:100%;">
          </div>
          <div>
            <label style="font-size:12px;font-weight:700;color:#475569;display:block;margin-bottom:4px;">MÃ SỐ THUẾ (MST)</label>
            <input type="text" id="tp-tax-code" class="filament-input" value="{{ $sampleTenant->tax_code ?? '' }}" placeholder="Mã số thuế..." required style="width:100%;">
          </div>
        </div>

        <div style="margin-bottom:14px;">
          <label style="font-size:12px;font-weight:700;color:#475569;display:block;margin-bottom:4px;">ĐỊA CHỈ TRỤ SỞ ĐĂNG KÝ KINH DOANH</label>
          <input type="text" id="tp-address" class="filament-input" value="{{ $sampleTenant->address ?? '' }}" placeholder="Địa chỉ đăng ký kinh doanh..." style="width:100%;">
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:14px;">
          <div>
            <label style="font-size:12px;font-weight:700;color:#475569;display:block;margin-bottom:4px;">NGƯỜI ĐẠI DIỆN PHÁP LUẬT</label>
            <input type="text" id="tp-rep-name" class="filament-input" value="{{ $sampleTenant->representative_name ?? '' }}" placeholder="Họ và tên người đại diện..." required style="width:100%;">
          </div>
          <div>
            <label style="font-size:12px;font-weight:700;color:#475569;display:block;margin-bottom:4px;">CHỨC DANH ĐẠI DIỆN</label>
            <input type="text" id="tp-rep-title" class="filament-input" value="{{ $sampleTenant->representative_position ?? 'Đại diện pháp luật' }}" placeholder="Chức danh..." style="width:100%;">
          </div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:16px;">
          <div>
            <label style="font-size:12px;font-weight:700;color:#475569;display:block;margin-bottom:4px;">EMAIL GIAO DỊCH CHÍNH</label>
            <input type="email" id="tp-email" class="filament-input" value="{{ $sampleTenant->contact_email ?? $sampleTenant->email ?? '' }}" placeholder="email@company.com" required style="width:100%;">
          </div>
          <div>
            <label style="font-size:12px;font-weight:700;color:#475569;display:block;margin-bottom:4px;">HOTLINE LIÊN HỆ</label>
            <input type="text" id="tp-phone" class="filament-input" value="{{ $sampleTenant->phone ?? '' }}" placeholder="Số điện thoại liên hệ..." required style="width:100%;">
          </div>
        </div>

        <div style="display:flex;justify-content:flex-end;gap:10px;">
          <button type="submit" class="btn-mo-primary" style="padding:7px 16px;">
            <i class="fa-solid fa-floppy-disk"></i> Lưu Thay Đổi Hồ Sơ
          </button>
        </div>
      </form>
    </div>

    <!-- Khối 2: Tải lên Giấy phép kinh doanh & Tài liệu pháp lý -->
    <div class="filament-card" style="margin-bottom:0;display:flex;flex-direction:column;justify-content:space-between;">
      <div>
        <div style="border-bottom:1px solid #f1f5f9;padding-bottom:12px;margin-bottom:16px;">
          <h3 style="font-size:16px;font-weight:700;color:#0f172a;margin:0;display:flex;align-items:center;gap:8px;">
            <i class="fa-solid fa-file-shield" style="color:#10b981;"></i> Giấy Phép Kinh Doanh (GPKD)
          </h3>
          <p style="font-size:12px;color:#64748b;margin:4px 0 0 0;">Tài liệu pháp lý chứng minh tư cách doanh nghiệp thuê mặt bằng văn phòng.</p>
        </div>

        <div style="background:#f8fafc;border:2px dashed #cbd5e1;border-radius:10px;padding:20px;text-align:center;margin-bottom:16px;">
          <i class="fa-solid fa-file-pdf" style="font-size:36px;color:#ef4444;margin-bottom:8px;display:block;"></i>
          <strong style="font-size:13.5px;color:#0f172a;display:block;" id="gpkd-file-name">{{ $sampleTenant?->business_license_url ? basename($sampleTenant->business_license_url) : 'Chưa có file GPKD' }}</strong>
          <span style="font-size:11.5px;color:#64748b;">Định dạng hỗ trợ: PDF, JPG, PNG (tối đa 10MB)</span>

          <div style="margin-top:14px;display:flex;justify-content:center;gap:10px;">
            <label class="btn-mo-primary" style="font-size:12px;padding:5px 12px;cursor:pointer;background:#2563eb;margin:0;">
              <i class="fa-solid fa-arrow-up-from-bracket"></i> Tải bản mới
              <input type="file" accept=".pdf,.jpg,.png" style="display:none;" onchange="handleUploadGpkd(this)">
            </label>
          </div>
        </div>

        <div style="font-size:12px;color:#64748b;line-height:1.5;">
          <i class="fa-solid fa-circle-info" style="color:#3b82f6;"></i> Khi doanh nghiệp có sự thay đổi về vốn điều lệ, người đại diện hoặc ngành nghề kinh doanh, vui lòng tải bản quét GPKD mới nhất để BQL cập nhật phụ lục hợp đồng.
        </div>
      </div>
    </div>
  </div>
</div>

<!-- TAB 2: QUẢN LÝ HỢP ĐỒNG THUÊ, LỊCH TRÌNH TĂNG GIÁ & BIÊN BẢN BÀN GIAO -->
<div id="tab-tenant-workspace" class="filament-tab-pane" style="display:none;">
  <div class="filament-page-header" style="align-items:center;">
    <div>
      <h1 class="filament-page-title" id="tenant-welcome-title" style="font-size:23px;margin-bottom:4px;">Hợp Đồng Thuê & Lịch Trình Tăng Giá Hàng Năm</h1>
      <p style="font-size:13.5px;color:#64748b;margin:0;">Xem thông tin hợp đồng đang hiệu lực, thời hạn thuê, biên bản bàn giao mặt bằng và lịch trình tăng giá thuê theo từng năm.</p>
    </div>
    <div style="display:flex;gap:8px;">
      <button class="btn-mo-outline" style="background:#fff;" onclick="loadTenantWorkspaceContracts()">
        <i class="fa-solid fa-arrows-rotate"></i> Làm mới
      </button>
      <button class="btn-mo-primary" style="background:#2563eb;" onclick="downloadHandoverProtocol()">
        <i class="fa-solid fa-file-arrow-down"></i> Tải Biên Bản Bàn Giao
      </button>
    </div>
  </div>

  <!-- 4 KPI Cards cho Cổng Khách Thuê -->
  <div class="bms-kpi-grid">
    <div class="bms-kpi-card">
      <div>
        <div class="bms-kpi-top">
          <div class="bms-kpi-title">HỢP ĐỒNG ĐANG HIỆU LỰC</div>
          <div class="bms-kpi-icon" style="background:#eff6ff;color:#2563eb;"><i class="fa-solid fa-file-contract"></i></div>
        </div>
        <div class="bms-kpi-main">
          <span class="bms-kpi-number" id="tenant-kpi-contract-count" style="color:#0f172a;font-size:26px;">{{ $sampleContract ? '01' : '00' }}</span>
          <span class="bms-kpi-pill" style="background:#ecfdf5;color:#047857;">{{ $sampleContract ? 'Đang hoạt động' : 'Chưa có HĐ' }}</span>
        </div>
      </div>
      <div class="bms-kpi-foot"><span>Mã HĐ:</span><strong id="tenant-kpi-contract-code">{{ $sampleContract->contract_code ?? '---' }}</strong></div>
    </div>

    <div class="bms-kpi-card">
      <div>
        <div class="bms-kpi-top">
          <div class="bms-kpi-title">DIỆN TÍCH SÀN THUÊ</div>
          <div class="bms-kpi-icon" style="background:#ecfdf5;color:#10b981;"><i class="fa-solid fa-ruler-combined"></i></div>
        </div>
        <div class="bms-kpi-main">
          <span class="bms-kpi-number" id="tenant-kpi-leased-area" style="color:#10b981;font-size:26px;">{{ $sampleContract ? number_format($sampleContract->total_leased_area_sqm ?? 0, 0, ',', '.') . ' m²' : '0 m²' }}</span>
          <span class="bms-kpi-pill" style="background:#eff6ff;color:#1d4ed8;">{{ $sampleContract?->building?->grade ? 'Hạng ' . $sampleContract->building->grade : 'Mặt bằng' }}</span>
        </div>
      </div>
      <div class="bms-kpi-foot"><span>Tòa nhà:</span><strong id="tenant-kpi-building-name">{{ $sampleContract?->building?->name ?? '---' }}</strong></div>
    </div>

    <div class="bms-kpi-card">
      <div>
        <div class="bms-kpi-top">
          <div class="bms-kpi-title">CƯỚC PHÍ KỲ HIỆN TẠI</div>
          <div class="bms-kpi-icon" style="background:#fef3c7;color:#d97706;"><i class="fa-solid fa-file-invoice-dollar"></i></div>
        </div>
        <div class="bms-kpi-main">
          <span class="bms-kpi-number" id="tenant-kpi-bill-amount" style="color:#d97706;font-size:24px;">{{ $sampleDebit ? number_format(($sampleDebit->total_amount ?? 0) / 1000000, 1, ',', '.') . ' Tr' : '0 Tr' }}</span>
          <span class="bms-kpi-pill" style="background:#fef3c7;color:#b45309;">{{ $sampleDebit ? ($sampleDebit->billing_period ?? 'Kỳ hiện tại') : 'Không có nợ' }}</span>
        </div>
      </div>
      <div class="bms-kpi-foot"><span>Hạn thanh toán:</span><strong>{{ $sampleDebit?->due_date ? $sampleDebit->due_date->format('d/m/Y') : 'Hàng tháng' }}</strong></div>
    </div>

    <div class="bms-kpi-card">
      <div>
        <div class="bms-kpi-top">
          <div class="bms-kpi-title">TICKET HỖ TRỢ KỸ THUẬT</div>
          <div class="bms-kpi-icon" style="background:#f3e8ff;color:#7e22ce;"><i class="fa-solid fa-screwdriver-wrench"></i></div>
        </div>
        <div class="bms-kpi-main">
          <span class="bms-kpi-number" id="tenant-kpi-ticket-count" style="color:#7e22ce;font-size:26px;">{{ sprintf('%02d', $stats['open_tickets'] ?? 0) }}</span>
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

<!-- TAB 3: TẠO VÀ THEO DÕI YÊU CẦU HỖ TRỢ, SỬA CHỮA & ĐĂNG KÝ THI CÔNG NGOÀI GIỜ -->
<div id="tab-tenant-tickets" class="filament-tab-pane" style="display:none;">
    <div class="filament-page-header" style="align-items:center;">
        <div>
            <h1 class="filament-page-title" style="font-size:23px;margin-bottom:4px;">Yêu Cầu Hỗ Trợ, Sửa Chữa & Đăng Ký Thi Công</h1>
            <p style="font-size:13.5px;color:#64748b;margin:0;">Báo cáo sự cố kỹ thuật hoặc nộp đơn xin đăng ký thi công nội thất ngoài giờ; theo dõi tiến độ xử lý và đánh giá CSAT.</p>
        </div>
        <div style="display:flex;gap:8px;">
            <button class="btn-mo-outline" style="background:#fff;" onclick="loadTicketsList('tenant')">
                <i class="fa-solid fa-arrows-rotate"></i> Làm mới
            </button>
            <button class="btn-mo-outline" style="background:#eff6ff;color:#2563eb;border-color:#93c5fd;font-weight:700;" onclick="openTenantAfterHoursModal()">
                <i class="fa-solid fa-clock"></i> Đăng ký thi công ngoài giờ
            </button>
            <button class="btn-mo-primary" style="background:#2563eb;" onclick="openTenantTicketModal()">
                <i class="fa-solid fa-plus"></i> Tạo Ticket kỹ thuật
            </button>
        </div>
    </div>

    <div class="filament-card">
        <table class="mo-table">
            <thead>
                <tr>
                    <th>Mã Ticket</th>
                    <th>Phân Loại</th>
                    <th>Tiêu Đề / Mô Tả</th>
                    <th>Thời Gian Tạo</th>
                    <th>Mức Ưu Tiên</th>
                    <th>Tiến Độ Xử Lý</th>
                    <th style="text-align:right;">Đánh Giá & Nghiệm Thu</th>
                </tr>
            </thead>
            <tbody id="tenant-tickets-tbody">
                <!-- Rendered via JS -->
            </tbody>
        </table>
    </div>
</div>

<!-- TAB 4: QUẢN LÝ CÔNG NỢ, THÔNG BÁO CƯỚC & TẢI LÊN ỦY NHIỆM CHI (UNC) -->
<div id="tab-tenant-bills" class="filament-tab-pane" style="display:none;">
    <div class="filament-page-header" style="align-items:center;">
        <div>
            <h1 class="filament-page-title" style="font-size:23px;margin-bottom:4px;">Thông Báo Cước Phí Hàng Tháng & Đối Soát Thanh Toán</h1>
            <p style="font-size:13.5px;color:#64748b;margin:0;">Xem thông báo tiền thuê, phí dịch vụ và tải lên ảnh chụp Ủy nhiệm chi (UNC) để kế toán đối soát.</p>
        </div>
        <div style="display:flex;gap:8px;">
            <button type="button" class="btn-mo-primary" style="background:#16a34a;" onclick="openUploadUncModal()">
                <i class="fa-solid fa-file-arrow-up"></i> Tải lên Ủy Nhiệm Chi (UNC)
            </button>
        </div>
    </div>

    <!-- Lịch sử các kỳ hóa đơn & Trạng thái Ủy nhiệm chi -->
    <div class="filament-card">
        <h3 style="font-size:15px;font-weight:700;color:#0f172a;margin:0 0 12px 0;">
          <i class="fa-solid fa-clock-rotate-left"></i> Lịch Sử Thông Báo Phí & Trạng Thái Đối Soát Kế Toán
        </h3>
        <table class="mo-table">
            <thead>
                <tr>
                    <th>Mã Hóa Đơn</th>
                    <th>Tòa Nhà & Văn Phòng</th>
                    <th>Kỳ Thanh Toán</th>
                    <th>Tổng Cộng (Gồm VAT)</th>
                    <th>Hạn Nộp</th>
                    <th>Chứng Từ UNC</th>
                    <th>Trạng Thái Thanh Toán</th>
                    <th style="text-align:right;">Thao Tác</th>
                </tr>
            </thead>
            <tbody id="tbody-tenant-bills">
                @php
                  $tenantDebits = $sampleTenant?->debitNotes ?? $recentDebitNotes ?? collect();
                @endphp
                @forelse($tenantDebits as $dn)
                  @php
                    $stRaw = $dn->status?->value ?? (string) $dn->status;
                    $stBadge = 'warning';
                    $stLabel = 'Chờ thanh toán';
                    if ($stRaw === 'PAID') { $stBadge = 'success'; $stLabel = 'Hoàn tất'; }
                    elseif ($stRaw === 'PARTIALLY_PAID') { $stBadge = 'purple'; $stLabel = 'Thanh toán một phần'; }
                    elseif ($stRaw === 'OVERDUE') { $stBadge = 'danger'; $stLabel = 'Quá hạn'; }

                    $bldName = $dn->building->name ?? 'Tòa nhà';
                    $period = $dn->billing_period ?? ($dn->created_at ? $dn->created_at->format('m/Y') : '11/2026');
                    $totalAmount = (float) ($dn->total_amount ?? 0);
                    $uncFile = $dn->proof_payment_url ? basename($dn->proof_payment_url) : null;
                    $dueDate = $dn->due_date ? $dn->due_date->format('d/m/Y') : ($dn->created_at ? $dn->created_at->addDays(15)->format('d/m/Y') : '05/12/2026');
                  @endphp
                  <tr>
                    <td><strong style="font-family:monospace;color:#1d4ed8;">{{ $dn->debit_note_code }}</strong></td>
                    <td><strong>{{ $bldName }}</strong></td>
                    <td><span class="badge-filament info">Kỳ {{ $period }}</span></td>
                    <td><strong style="color:{{ $stRaw === 'PAID' ? '#0f172a' : '#ea580c' }};">{{ number_format($totalAmount, 0, ',', '.') }} đ</strong></td>
                    <td>{{ $dueDate }}</td>
                    <td>
                      @if($uncFile)
                        <span style="color:#2563eb;font-size:12px;font-weight:700;"><i class="fa-solid fa-paperclip"></i> {{ $uncFile }}</span>
                      @else
                        <span style="color:#64748b;font-size:12px;"><i class="fa-solid fa-circle-xmark" style="color:#f59e0b;"></i> Chưa tải lên</span>
                      @endif
                    </td>
                    <td><span class="badge-filament {{ $stBadge }}">{{ $stLabel }}</span></td>
                    <td style="text-align:right;">
                      @if($stRaw !== 'PAID')
                        <button type="button" class="btn-mo-primary" style="padding:4px 10px;font-size:12px;background:#16a34a;" onclick="openUploadUncModal('{{ $dn->debit_note_code }}')">
                          <i class="fa-solid fa-upload"></i> Tải UNC
                        </button>
                      @else
                        <span class="badge-filament success"><i class="fa-solid fa-check"></i> Đã đối soát</span>
                      @endif
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="8" style="text-align:center;padding:28px;color:#94a3b8;">
                      Chưa có thông báo phí nào được phát hành cho doanh nghiệp này.
                    </td>
                  </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
