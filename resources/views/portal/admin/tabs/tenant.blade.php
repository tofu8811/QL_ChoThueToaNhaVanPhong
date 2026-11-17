<!-- ==========================================================================
     PHÂN HỆ CỔNG THÔNG TIN DOANH NGHIỆP THUÊ (TENANT PORTAL)
     ĐÁP ỨNG ĐẦY ĐỦ 4 TIÊU CHÍ NGHIỆP VỤ ROLE 2: KHÁCH THUÊ
     ========================================================================== -->

<!-- TAB 1: QUẢN LÝ HỒ SƠ DOANH NGHIỆP & NHÂN SỰ LÀM VIỆC TẠI VĂN PHÒNG -->
<div id="tab-tenant-profile" class="filament-tab-pane" style="display:none;">
  <div class="filament-page-header" style="align-items:center;">
    <div>
      <h1 class="filament-page-title" style="font-size:23px;margin-bottom:4px;">Hồ Sơ Doanh Nghiệp & Nhân Sự Văn Phòng</h1>
      <p style="font-size:13.5px;color:#64748b;margin:0;">Quản lý thông tin pháp lý công ty, người đại diện pháp luật, giấy phép kinh doanh và danh sách nhân viên cấp thẻ ra vào tòa nhà.</p>
    </div>
    <div style="display:flex;gap:8px;">
      <button type="button" class="btn-mo-primary" style="background:#2563eb;" onclick="openAddTenantEmployeeModal()">
        <i class="fa-solid fa-user-plus"></i> Cấp thẻ nhân viên mới
      </button>
    </div>
  </div>

  <div style="display:grid;grid-template-columns:1.2fr 1fr;gap:20px;margin-bottom:24px;">
    <!-- Khối 1: Thông tin pháp lý công ty & Người đại diện -->
    <div class="filament-card" style="margin-bottom:0;">
      <div style="border-bottom:1px solid #f1f5f9;padding-bottom:12px;margin-bottom:16px;display:flex;justify-content:space-between;align-items:center;">
        <h3 style="font-size:16px;font-weight:700;color:#0f172a;margin:0;display:flex;align-items:center;gap:8px;">
          <i class="fa-solid fa-building" style="color:#2563eb;"></i> Thông Tin Pháp Lý Doanh Nghiệp
        </h3>
        <span class="badge-filament success"><i class="fa-solid fa-circle-check"></i> Đã xác thực GPKD</span>
      </div>

      <form id="form-tenant-company-profile" onsubmit="handleSaveTenantProfile(event)">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:14px;">
          <div>
            <label style="font-size:12px;font-weight:700;color:#475569;display:block;margin-bottom:4px;">TÊN DOANH NGHIỆP</label>
            <input type="text" id="tp-company-name" class="filament-input" value="Công ty Cổ phần VNG" required style="width:100%;">
          </div>
          <div>
            <label style="font-size:12px;font-weight:700;color:#475569;display:block;margin-bottom:4px;">MÃ SỐ THUẾ (MST)</label>
            <input type="text" id="tp-tax-code" class="filament-input" value="0303615599" required style="width:100%;">
          </div>
        </div>

        <div style="margin-bottom:14px;">
          <label style="font-size:12px;font-weight:700;color:#475569;display:block;margin-bottom:4px;">ĐỊA CHỈ TRỤ SỞ ĐĂNG KÝ KINH DOANH</label>
          <input type="text" id="tp-address" class="filament-input" value="Z06, Đường số 13, Phường Tân Thuận Đông, Quận 7, TP.HCM" style="width:100%;">
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:14px;">
          <div>
            <label style="font-size:12px;font-weight:700;color:#475569;display:block;margin-bottom:4px;">NGƯỜI ĐẠI DIỆN PHÁP LUẬT</label>
            <input type="text" id="tp-rep-name" class="filament-input" value="Lê Hồng Minh" required style="width:100%;">
          </div>
          <div>
            <label style="font-size:12px;font-weight:700;color:#475569;display:block;margin-bottom:4px;">CHỨC DANH ĐẠI DIỆN</label>
            <input type="text" id="tp-rep-title" class="filament-input" value="Tổng Giám Đốc" style="width:100%;">
          </div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:16px;">
          <div>
            <label style="font-size:12px;font-weight:700;color:#475569;display:block;margin-bottom:4px;">EMAIL GIAO DỊCH CHÍNH</label>
            <input type="email" id="tp-email" class="filament-input" value="leasing-admin@vng.com.vn" required style="width:100%;">
          </div>
          <div>
            <label style="font-size:12px;font-weight:700;color:#475569;display:block;margin-bottom:4px;">HOTLINE LIÊN HỆ</label>
            <input type="text" id="tp-phone" class="filament-input" value="0903123456" required style="width:100%;">
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
          <strong style="font-size:13.5px;color:#0f172a;display:block;" id="gpkd-file-name">GPKD_VNG_Corp_Signed_2026.pdf</strong>
          <span style="font-size:11.5px;color:#64748b;">Dung lượng: 2.4 MB • Đã xác thực bởi Ban Quản Lý</span>

          <div style="margin-top:14px;display:flex;justify-content:center;gap:10px;">
            <a href="javascript:void(0)" onclick="alert('Đang mở file Giấy Phép Kinh Doanh bản gốc...')" class="btn-mo-outline" style="font-size:12px;padding:5px 12px;background:#fff;">
              <i class="fa-solid fa-eye"></i> Xem file
            </a>
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

  <!-- Khối 3: Danh sách nhân viên làm việc tại văn phòng & Đăng ký gửi xe -->
  <div class="filament-card">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;flex-wrap:wrap;gap:10px;">
      <div>
        <h3 style="font-size:16px;font-weight:700;color:#0f172a;margin:0;display:flex;align-items:center;gap:8px;">
          <i class="fa-solid fa-users" style="color:#6366f1;"></i> Danh Sách Nhân Viên Làm Việc Tại Văn Phòng
        </h3>
        <p style="font-size:12.5px;color:#64748b;margin:3px 0 0 0;">Cấp quyền ra vào sảnh thang máy tòa nhà và đăng ký thẻ gửi xe máy / ô tô hàng tháng.</p>
      </div>
      <div>
        <span style="font-size:13px;color:#64748b;">Tổng cộng: <strong id="tenant-emp-count" style="color:#0f172a;">4</strong> nhân sự</span>
      </div>
    </div>

    <table class="mo-table">
      <thead>
        <tr>
          <th>Mã Thẻ</th>
          <th>Họ Và Tên</th>
          <th>Email / Số Điện Thoại</th>
          <th>Phòng Ban / Chức Vụ</th>
          <th>Biển Số Xe Đăng Ký</th>
          <th>Trạng Thái Thẻ</th>
          <th style="text-align:right;">Thao Tác</th>
        </tr>
      </thead>
      <tbody id="tbody-tenant-employees">
        <tr>
          <td><strong style="font-family:monospace;color:#2563eb;">CARD-001</strong></td>
          <td><strong>Lê Hồng Minh</strong></td>
          <td>minh.le@vng.com.vn • 0903123456</td>
          <td>Ban Giám Đốc</td>
          <td><span style="background:#f1f5f9;padding:2px 7px;border-radius:4px;font-family:monospace;font-weight:700;">51A-888.99 (Ô tô)</span></td>
          <td><span class="badge-filament success">Đang hoạt động</span></td>
          <td style="text-align:right;">
            <button class="btn-mo-outline" style="padding:3px 8px;font-size:11.5px;color:#ef4444;" onclick="deleteTenantEmployee(this)">Hủy thẻ</button>
          </td>
        </tr>
        <tr>
          <td><strong style="font-family:monospace;color:#2563eb;">CARD-002</strong></td>
          <td><strong>Vương Quang Khải</strong></td>
          <td>khai.vuong@vng.com.vn • 0908234567</td>
          <td>Khối Kỹ Thuật (Tech Lead)</td>
          <td><span style="background:#f1f5f9;padding:2px 7px;border-radius:4px;font-family:monospace;font-weight:700;">59B1-123.45 (Xe máy)</span></td>
          <td><span class="badge-filament success">Đang hoạt động</span></td>
          <td style="text-align:right;">
            <button class="btn-mo-outline" style="padding:3px 8px;font-size:11.5px;color:#ef4444;" onclick="deleteTenantEmployee(this)">Hủy thẻ</button>
          </td>
        </tr>
        <tr>
          <td><strong style="font-family:monospace;color:#2563eb;">CARD-003</strong></td>
          <td><strong>Trần Phương Dung</strong></td>
          <td>dung.tran@vng.com.vn • 0912345678</td>
          <td>Hành Chính Nhân Sự (HR)</td>
          <td><span style="background:#f1f5f9;padding:2px 7px;border-radius:4px;font-family:monospace;font-weight:700;">59C2-678.90 (Xe máy)</span></td>
          <td><span class="badge-filament success">Đang hoạt động</span></td>
          <td style="text-align:right;">
            <button class="btn-mo-outline" style="padding:3px 8px;font-size:11.5px;color:#ef4444;" onclick="deleteTenantEmployee(this)">Hủy thẻ</button>
          </td>
        </tr>
        <tr>
          <td><strong style="font-family:monospace;color:#2563eb;">CARD-004</strong></td>
          <td><strong>Nguyễn Văn Tuấn</strong></td>
          <td>tuan.nguyen@vng.com.vn • 0987654321</td>
          <td>Kế Toán Trưởng</td>
          <td><span style="background:#f1f5f9;padding:2px 7px;border-radius:4px;font-family:monospace;font-weight:700;">51G-678.12 (Ô tô)</span></td>
          <td><span class="badge-filament success">Đang hoạt động</span></td>
          <td style="text-align:right;">
            <button class="btn-mo-outline" style="padding:3px 8px;font-size:11.5px;color:#ef4444;" onclick="deleteTenantEmployee(this)">Hủy thẻ</button>
          </td>
        </tr>
      </tbody>
    </table>
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

  <!-- Khối Lịch trình tăng giá thuê tự động theo từng năm (Rent Escalation Schedule) -->
  <div class="filament-card" style="margin-top:20px;">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;">
      <div>
        <h3 style="font-size:16px;font-weight:700;color:#0f172a;margin:0;display:flex;align-items:center;gap:8px;">
          <i class="fa-solid fa-arrow-trend-up" style="color:#2563eb;"></i> Lịch Trình Tăng Giá Thuê Tự Động Theo Từng Năm
        </h3>
        <p style="font-size:12.5px;color:#64748b;margin:3px 0 0 0;">Quy định biên độ điều chỉnh đơn giá thuê theo chu kỳ từng năm đã ký kết trong hợp đồng.</p>
      </div>
      <span class="badge-filament info">Hợp đồng 3 năm</span>
    </div>

    <table class="mo-table">
      <thead>
        <tr>
          <th>Giai Đoạn Thuê</th>
          <th>Thời Gian Áp Dụng</th>
          <th>Đơn Giá Thuê / m²</th>
          <th>Tỷ Lệ Điều Chỉnh</th>
          <th>Tổng Tiền Thuê / Tháng</th>
          <th>Trạng Thái</th>
        </tr>
      </thead>
      <tbody>
        <tr style="background:#f0fdf4;">
          <td><strong style="color:#15803d;">Năm thứ 1 (Năm đầu)</strong></td>
          <td>01/01/2025 - 31/12/2025</td>
          <td><strong>$35.00 / m²</strong> (~857.500 đ)</td>
          <td><span class="badge-filament success">Giá cố định ban đầu</span></td>
          <td><strong>300.125.000 đ</strong></td>
          <td><span class="badge-filament success">Đã hoàn thành</span></td>
        </tr>
        <tr style="background:#eff6ff;">
          <td><strong style="color:#1d4ed8;">Năm thứ 2 (Hiện tại)</strong></td>
          <td>01/01/2026 - 31/12/2026</td>
          <td><strong>$36.75 / m²</strong> (~900.375 đ)</td>
          <td><span class="badge-filament primary">+5.0% so với Năm 1</span></td>
          <td><strong>315.131.250 đ</strong></td>
          <td><span class="badge-filament primary">Đang áp dụng</span></td>
        </tr>
        <tr>
          <td><strong>Năm thứ 3 (Kế tiếp)</strong></td>
          <td>01/01/2027 - 31/12/2027</td>
          <td><strong>$38.59 / m²</strong> (~945.394 đ)</td>
          <td><span class="badge-filament info">+5.0% so với Năm 2</span></td>
          <td><strong>330.887.800 đ</strong></td>
          <td><span class="badge-filament">Dự kiến</span></td>
        </tr>
      </tbody>
    </table>
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
            <p style="font-size:13.5px;color:#64748b;margin:0;">Xem bảng kê chi tiết tiền thuê, phí quản lý, điện, nước, gửi xe và tải lên ảnh chụp Ủy nhiệm chi để kế toán đối soát.</p>
        </div>
        <div style="display:flex;gap:8px;">
            <button type="button" class="btn-mo-outline" style="background:#fff;" onclick="alert('Đã tải xuống bảng kê chi tiết hóa đơn GTGT kỳ 11/2026!')">
                <i class="fa-solid fa-download"></i> Tải thông báo phí PDF
            </button>
            <button type="button" class="btn-mo-primary" style="background:#16a34a;" onclick="openUploadUncModal()">
                <i class="fa-solid fa-file-arrow-up"></i> Tải lên Ủy Nhiệm Chi (UNC)
            </button>
        </div>
    </div>

    <!-- Bảng phân bổ 5 cấu phần chi phí cước tháng hiện tại -->
    <div class="filament-card" style="margin-bottom:20px;border-top:3px solid #2563eb;">
      <h3 style="font-size:15px;font-weight:700;color:#0f172a;margin:0 0 12px 0;">
        <i class="fa-solid fa-receipt" style="color:#2563eb;"></i> Chi Tiết 5 Cấu Phần Cước Phí Kỳ 11/2026 (Hạn nộp: 05/12/2026)
      </h3>
      <table class="mo-table">
        <thead>
          <tr>
            <th>Hạng Mục Phí</th>
            <th>Khối Lượng / Tiêu Thụ</th>
            <th>Đơn Giá Niêm Yết</th>
            <th>Thành Tiền (Chưa VAT)</th>
            <th>VAT (10%)</th>
            <th style="text-align:right;">Tổng Thanh Toán</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td><strong>1. Tiền thuê sàn văn phòng (Tầng 12)</strong></td>
            <td>350 m²</td>
            <td>900.375 đ / m²</td>
            <td>315.131.250 đ</td>
            <td>31.513.125 đ</td>
            <td style="text-align:right;font-weight:700;color:#0f172a;">346.644.375 đ</td>
          </tr>
          <tr>
            <td><strong>2. Phí quản lý vận hành tòa nhà</strong></td>
            <td>350 m²</td>
            <td>150.000 đ / m²</td>
            <td>52.500.000 đ</td>
            <td>5.250.000 đ</td>
            <td style="text-align:right;font-weight:700;color:#0f172a;">57.750.000 đ</td>
          </tr>
          <tr>
            <td><strong>3. Tiền điện sử dụng & điều hòa</strong></td>
            <td>3.250 kWh (Chỉ số: 12450 - 15700)</td>
            <td>3.500 đ / kWh</td>
            <td>11.375.000 đ</td>
            <td>1.137.500 đ</td>
            <td style="text-align:right;font-weight:700;color:#0f172a;">12.512.500 đ</td>
          </tr>
          <tr>
            <td><strong>4. Tiền nước sinh hoạt</strong></td>
            <td>45 m³ (Chỉ số: 420 - 465)</td>
            <td>18.000 đ / m³</td>
            <td>810.000 đ</td>
            <td>81.000 đ</td>
            <td style="text-align:right;font-weight:700;color:#0f172a;">891.000 đ</td>
          </tr>
          <tr>
            <td><strong>5. Phí gửi xe nhân viên</strong></td>
            <td>2 Ô tô + 25 Xe máy</td>
            <td>Ô tô: 2.2Tr • Xe máy: 180k</td>
            <td>8.900.000 đ</td>
            <td>890.000 đ</td>
            <td style="text-align:right;font-weight:700;color:#0f172a;">9.790.000 đ</td>
          </tr>
          <tr style="background:#f8fafc;font-size:14px;">
            <td colspan="5"><strong style="color:#0f172a;">TỔNG CỘNG CƯỚC PHÍ PHẢI THANH TOÁN (GỒM THUẾ GTGT)</strong></td>
            <td style="text-align:right;"><strong style="font-size:17px;color:#dc2626;">427.587.875 đ</strong></td>
          </tr>
        </tbody>
      </table>
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
            <tbody>
                <tr>
                    <td><strong style="font-family:monospace;color:#1d4ed8;">DN-202611-001</strong></td>
                    <td><strong>Vietcombank Tower - Tầng 12</strong></td>
                    <td><span class="badge-filament info">Kỳ 11/2026</span></td>
                    <td><strong style="color:#ea580c;">427.587.875 đ</strong></td>
                    <td>05/12/2026</td>
                    <td><span style="color:#64748b;font-size:12px;"><i class="fa-solid fa-circle-xmark" style="color:#f59e0b;"></i> Chưa tải lên</span></td>
                    <td><span class="badge-filament warning">Chờ thanh toán</span></td>
                    <td style="text-align:right;">
                      <button type="button" class="btn-mo-primary" style="padding:4px 10px;font-size:12px;background:#16a34a;" onclick="openUploadUncModal('DN-202611-001')">
                        <i class="fa-solid fa-upload"></i> Tải UNC
                      </button>
                    </td>
                </tr>
                <tr>
                    <td><strong style="font-family:monospace;color:#1d4ed8;">DN-202610-001</strong></td>
                    <td><strong>Vietcombank Tower - Tầng 12</strong></td>
                    <td><span class="badge-filament info">Kỳ 10/2026</span></td>
                    <td><strong style="color:#0f172a;">412.350.000 đ</strong></td>
                    <td>05/11/2026</td>
                    <td><a href="javascript:void(0)" onclick="alert('Xem ảnh UNC_VNG_Oct2026.png đã đối soát hợp lệ')" style="color:#2563eb;font-size:12px;font-weight:700;"><i class="fa-solid fa-paperclip"></i> UNC_Oct2026.png</a></td>
                    <td><span class="badge-filament success">Hoàn tất</span></td>
                    <td style="text-align:right;">
                      <span class="badge-filament success"><i class="fa-solid fa-check"></i> Đã đối soát</span>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
