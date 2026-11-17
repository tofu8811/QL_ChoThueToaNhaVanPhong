<!-- ==========================================================================
     TAB KẾ TOÁN & CÔNG NỢ TÒA NHÀ (ROLE 4: BQL & KẾ TOÁN)
     ĐÁP ỨNG NGHIỆP VỤ: NHẬP CHỈ SỐ ĐIỆN, NƯỚC, GỬI XE, XUẤT THÔNG BÁO CƯỚC,
     KIỂM TRA ỦY NHIỆM CHI (UNC) VÀ XÁC NHẬN TRẠNG THÁI THANH TOÁN
     ========================================================================== -->
<div id="tab-admin-billing" class="filament-tab-pane" style="display:none;">
  <div class="filament-page-header" style="align-items:center;">
    <div>
      <h1 class="filament-page-title" style="font-size:23px;margin-bottom:4px;">Kế Toán, Nhập Chỉ Số Tiêu Thụ & Quản Lý Công Nợ Tòa Nhà</h1>
      <p style="font-size:13.5px;color:#64748b;margin:0;">Nhập chỉ số điện, nước, gửi xe hàng tháng để hệ thống tự động xuất thông báo phí (Debit Note); kiểm tra Ủy nhiệm chi và xác nhận thanh toán.</p>
    </div>
    <div style="display:flex;gap:8px;">
      <button class="btn-mo-outline" style="background:#fff;" onclick="window.location.reload()">
        <i class="fa-solid fa-arrows-rotate"></i> Làm mới
      </button>
      <button class="btn-mo-primary" style="background:#059669;" onclick="openRecordUtilitiesModal()">
        <i class="fa-solid fa-calculator"></i> Nhập chỉ số điện, nước & gửi xe
      </button>
    </div>
  </div>

  <!-- 4 KPI Cards Công nợ vận hành -->
  <div class="bms-kpi-grid">
    <div class="bms-kpi-card">
      <div>
        <div class="bms-kpi-top">
          <div class="bms-kpi-title">TỔNG PHẢI THU KỲ HIỆN TẠI</div>
          <div class="bms-kpi-icon" style="background:#eff6ff;color:#2563eb;"><i class="fa-solid fa-file-invoice-dollar"></i></div>
        </div>
        <div class="bms-kpi-main">
          <span class="bms-kpi-number" style="color:#0f172a;font-size:26px;">4.280 Tr</span>
          <span class="bms-kpi-pill" style="background:#eff6ff;color:#1d4ed8;">Kỳ 11/2026</span>
        </div>
      </div>
      <div class="bms-kpi-foot"><span>Tổng số thông báo phí:</span><strong>10 hóa đơn</strong></div>
    </div>

    <div class="bms-kpi-card">
      <div>
        <div class="bms-kpi-top">
          <div class="bms-kpi-title">ĐÃ THU & KHỚP ỦY NHIỆM CHI</div>
          <div class="bms-kpi-icon" style="background:#ecfdf5;color:#10b981;"><i class="fa-solid fa-circle-check"></i></div>
        </div>
        <div class="bms-kpi-main">
          <span class="bms-kpi-number" style="color:#10b981;font-size:26px;">3.424 Tr</span>
          <span class="bms-kpi-pill" style="background:#ecfdf5;color:#047857;">8 / 10 khách đã nộp</span>
        </div>
      </div>
      <div class="bms-kpi-foot"><span>Tỷ lệ hoàn thành:</span><strong>80.0% tổng doanh thu</strong></div>
    </div>

    <div class="bms-kpi-card">
      <div>
        <div class="bms-kpi-top">
          <div class="bms-kpi-title">CÔNG NỢ CHỜ ĐỐI SOÁT</div>
          <div class="bms-kpi-icon" style="background:#fef3c7;color:#d97706;"><i class="fa-solid fa-clock-rotate-left"></i></div>
        </div>
        <div class="bms-kpi-main">
          <span class="bms-kpi-number" style="color:#d97706;font-size:26px;">856 Tr</span>
          <span class="bms-kpi-pill" style="background:#fef3c7;color:#b45309;">2 khách chờ đối soát</span>
        </div>
      </div>
      <div class="bms-kpi-foot"><span>Hạn chót thanh toán:</span><strong>05/12/2026</strong></div>
    </div>

    <div class="bms-kpi-card">
      <div>
        <div class="bms-kpi-top">
          <div class="bms-kpi-title">CHỈ SỐ TIÊU THỤ NĂNG LƯỢNG</div>
          <div class="bms-kpi-icon" style="background:#f3e8ff;color:#7e22ce;"><i class="fa-solid fa-bolt"></i></div>
        </div>
        <div class="bms-kpi-main">
          <span class="bms-kpi-number" style="color:#7e22ce;font-size:26px;">32.500 kWh</span>
          <span class="bms-kpi-pill" style="background:#f3e8ff;color:#6d28d9;">Đã chốt công tơ</span>
        </div>
      </div>
      <div class="bms-kpi-foot"><span>Nước tiêu thụ:</span><strong>450 m³ sinh hoạt</strong></div>
    </div>
  </div>

  <!-- Bảng Thông Báo Phí & Danh Sách Đối Soát UNC -->
  <div class="filament-card" style="margin-top:20px;">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;flex-wrap:wrap;gap:10px;">
      <div>
        <h3 style="font-size:16px;font-weight:700;color:#0f172a;margin:0;display:flex;align-items:center;gap:8px;">
          <i class="fa-solid fa-receipt" style="color:#059669;"></i> Danh Sách Thông Báo Phí Hàng Tháng & Chứng Từ UNC Đối Soát
        </h3>
        <p style="font-size:12.5px;color:#64748b;margin:3px 0 0 0;">Kiểm tra ảnh chụp Ủy nhiệm chi khách hàng tải lên và cập nhật trạng thái thanh toán.</p>
      </div>
    </div>

    <table class="mo-table">
      <thead>
        <tr>
          <th>Mã Thông Báo Phí</th>
          <th>Doanh Nghiệp & Tòa Nhà</th>
          <th>Kỳ Cước</th>
          <th>Tiền Thuê & Dịch Vụ</th>
          <th>Điện + Nước + Xe</th>
          <th>Tổng Phải Thu (VAT)</th>
          <th>Ủy Nhiệm Chi (UNC)</th>
          <th>Trạng Thái Thanh Toán</th>
          <th style="text-align:right;">Thao Tác</th>
        </tr>
      </thead>
      <tbody id="tbody-admin-billing">
        <tr>
          <td><strong style="font-family:monospace;color:#1d4ed8;">DN-202611-001</strong></td>
          <td>
            <strong>Công ty Cổ phần VNG</strong>
            <div style="font-size:12px;color:#64748b;">Vietcombank Tower - Tầng 12 (350 m²)</div>
          </td>
          <td><span class="badge-filament info">11/2026</span></td>
          <td>404.394.375 đ</td>
          <td>23.193.500 đ</td>
          <td><strong style="color:#ea580c;font-size:14px;">427.587.875 đ</strong></td>
          <td>
            <button class="btn-mo-outline" style="padding:3px 8px;font-size:11.5px;color:#2563eb;font-weight:700;" onclick="openReconcileUncModal('DN-202611-001', 'Công ty Cổ phần VNG', '427.587.875 đ', 'UNC_VNG_Nov2026.png', 'PENDING')">
              <i class="fa-solid fa-paperclip"></i> UNC_Nov2026.png
            </button>
          </td>
          <td><span class="badge-filament warning" id="badge-dn-status-DN-202611-001">Chờ thanh toán</span></td>
          <td style="text-align:right;">
            <button class="btn-mo-primary" style="padding:4px 10px;font-size:12px;background:#059669;" onclick="openReconcileUncModal('DN-202611-001', 'Công ty Cổ phần VNG', '427.587.875 đ', 'UNC_VNG_Nov2026.png', 'PENDING')">
              <i class="fa-solid fa-check-double"></i> Đối soát UNC
            </button>
          </td>
        </tr>
        <tr>
          <td><strong style="font-family:monospace;color:#1d4ed8;">DN-202611-002</strong></td>
          <td>
            <strong>Tập đoàn FPT Software</strong>
            <div style="font-size:12px;color:#64748b;">The Nexus Tower - Tầng 15 (500 m²)</div>
          </td>
          <td><span class="badge-filament info">11/2026</span></td>
          <td>580.000.000 đ</td>
          <td>35.400.000 đ</td>
          <td><strong style="color:#0f172a;font-size:14px;">615.400.000 đ</strong></td>
          <td>
            <button class="btn-mo-outline" style="padding:3px 8px;font-size:11.5px;color:#16a34a;font-weight:700;" onclick="openReconcileUncModal('DN-202611-002', 'FPT Software', '615.400.000 đ', 'UNC_FPT_VCB_Passed.pdf', 'PAID')">
              <i class="fa-solid fa-file-pdf"></i> UNC_FPT_VCB.pdf
            </button>
          </td>
          <td><span class="badge-filament success" id="badge-dn-status-DN-202611-002">Hoàn tất</span></td>
          <td style="text-align:right;">
            <span class="badge-filament success"><i class="fa-solid fa-check"></i> Đã thanh toán</span>
          </td>
        </tr>
        <tr>
          <td><strong style="font-family:monospace;color:#1d4ed8;">DN-202611-003</strong></td>
          <td>
            <strong>Shopee Express Vietnam</strong>
            <div style="font-size:12px;color:#64748b;">Saigon Centre Tower 2 - Tầng 6 (280 m²)</div>
          </td>
          <td><span class="badge-filament info">11/2026</span></td>
          <td>320.000.000 đ</td>
          <td>18.500.000 đ</td>
          <td><strong style="color:#0f172a;font-size:14px;">338.500.000 đ</strong></td>
          <td>
            <button class="btn-mo-outline" style="padding:3px 8px;font-size:11.5px;color:#d97706;font-weight:700;" onclick="openReconcileUncModal('DN-202611-003', 'Shopee Express', '338.500.000 đ', 'UNC_Shopee_Dot1.png', 'PARTIAL')">
              <i class="fa-solid fa-paperclip"></i> UNC_Dot1.png
            </button>
          </td>
          <td><span class="badge-filament purple" style="background:#f3e8ff;color:#7e22ce;" id="badge-dn-status-DN-202611-003">Thanh toán một phần</span></td>
          <td style="text-align:right;">
            <button class="btn-mo-primary" style="padding:4px 10px;font-size:12px;background:#059669;" onclick="openReconcileUncModal('DN-202611-003', 'Shopee Express', '338.500.000 đ', 'UNC_Shopee_Dot1.png', 'PARTIAL')">
              <i class="fa-solid fa-check-double"></i> Đối soát UNC
            </button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</div>

<!-- MODAL NHẬP CHỈ SỐ ĐIỆN, NƯỚC, GỬI XE (ROLE 4: BQL KẾ TOÁN) -->
<div id="modal-record-utilities" class="mo-modal" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,0.65);backdrop-filter:blur(3px);z-index:9999;align-items:center;justify-content:center;padding:20px;">
  <div style="background:#fff;border-radius:12px;width:100%;max-width:620px;box-shadow:0 25px 50px -12px rgba(0,0,0,0.25);overflow:hidden;border:1px solid #cbd5e1;">
    <div style="background:linear-gradient(135deg,#065f46,#059669);color:#fff;padding:16px 20px;display:flex;justify-content:space-between;align-items:center;">
      <h3 style="margin:0;font-size:16px;font-weight:700;"><i class="fa-solid fa-bolt"></i> Nhập Chỉ Số Điện, Nước & Gửi Xe Để Xuất Thông Báo Phí</h3>
      <button type="button" onclick="closeModal('modal-record-utilities')" style="background:none;border:none;color:#fff;font-size:22px;cursor:pointer;">&times;</button>
    </div>
    <form onsubmit="handleCalculateUtilities(event)" style="padding:20px;">
      <div style="display:grid;grid-template-columns:1.5fr 1fr;gap:12px;margin-bottom:14px;">
        <div>
          <label style="font-size:12px;font-weight:700;color:#334155;display:block;margin-bottom:4px;">KHÁCH THUÊ DOANH NGHIỆP <span style="color:#ef4444;">*</span></label>
          <select id="util-tenant" class="filament-input" style="width:100%;">
            <option value="Công ty Cổ phần VNG">Công ty Cổ phần VNG - Tầng 12 (Vietcombank Tower)</option>
            <option value="Tập đoàn FPT Software">Tập đoàn FPT Software - Tầng 15 (The Nexus Tower)</option>
            <option value="Shopee Express Vietnam">Shopee Express Vietnam - Tầng 6 (Saigon Centre)</option>
          </select>
        </div>
        <div>
          <label style="font-size:12px;font-weight:700;color:#334155;display:block;margin-bottom:4px;">KỲ TÍNH CƯỚC <span style="color:#ef4444;">*</span></label>
          <input type="text" id="util-month" class="filament-input" value="11/2026" style="width:100%;">
        </div>
      </div>

      <!-- Chỉ số điện -->
      <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:12px;margin-bottom:12px;">
        <strong style="font-size:13px;color:#0f172a;display:block;margin-bottom:8px;"><i class="fa-solid fa-plug" style="color:#2563eb;"></i> 1. Tiêu Thụ Điện Năng (Đơn giá: 3.500 đ / kWh)</strong>
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px;">
          <div>
            <label style="font-size:11px;color:#64748b;">Số đầu kỳ (kWh)</label>
            <input type="number" id="elec-start" class="filament-input" value="12450" oninput="recalcUtilPreview()">
          </div>
          <div>
            <label style="font-size:11px;color:#64748b;">Số cuối kỳ (kWh)</label>
            <input type="number" id="elec-end" class="filament-input" value="15700" oninput="recalcUtilPreview()">
          </div>
          <div>
            <label style="font-size:11px;color:#64748b;">Thành tiền điện (đ)</label>
            <input type="text" id="elec-total" class="filament-input" value="11.375.000 đ" readonly style="background:#f1f5f9;font-weight:700;">
          </div>
        </div>
      </div>

      <!-- Chỉ số nước -->
      <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:12px;margin-bottom:12px;">
        <strong style="font-size:13px;color:#0f172a;display:block;margin-bottom:8px;"><i class="fa-solid fa-droplet" style="color:#0284c7;"></i> 2. Tiêu Thụ Nước Sinh Hoạt (Đơn giá: 18.000 đ / m³)</strong>
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px;">
          <div>
            <label style="font-size:11px;color:#64748b;">Số khối đầu kỳ (m³)</label>
            <input type="number" id="water-start" class="filament-input" value="420" oninput="recalcUtilPreview()">
          </div>
          <div>
            <label style="font-size:11px;color:#64748b;">Số khối cuối kỳ (m³)</label>
            <input type="number" id="water-end" class="filament-input" value="465" oninput="recalcUtilPreview()">
          </div>
          <div>
            <label style="font-size:11px;color:#64748b;">Thành tiền nước (đ)</label>
            <input type="text" id="water-total" class="filament-input" value="810.000 đ" readonly style="background:#f1f5f9;font-weight:700;">
          </div>
        </div>
      </div>

      <!-- Vé gửi xe -->
      <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:12px;margin-bottom:16px;">
        <strong style="font-size:13px;color:#0f172a;display:block;margin-bottom:8px;"><i class="fa-solid fa-motorcycle" style="color:#d97706;"></i> 3. Phí Trông Giữ Xe Hàng Tháng</strong>
        <div style="display:grid;grid-template-columns:1fr 1fr 1.2fr;gap:10px;">
          <div>
            <label style="font-size:11px;color:#64748b;">Số lượng Ô tô (2.2Tr/xe)</label>
            <input type="number" id="park-cars" class="filament-input" value="2" oninput="recalcUtilPreview()">
          </div>
          <div>
            <label style="font-size:11px;color:#64748b;">Số lượng Xe máy (180k/xe)</label>
            <input type="number" id="park-motos" class="filament-input" value="25" oninput="recalcUtilPreview()">
          </div>
          <div>
            <label style="font-size:11px;color:#64748b;">Tổng tiền gửi xe (đ)</label>
            <input type="text" id="park-total" class="filament-input" value="8.900.000 đ" readonly style="background:#f1f5f9;font-weight:700;">
          </div>
        </div>
      </div>

      <div style="display:flex;justify-content:flex-end;gap:10px;">
        <button type="button" class="btn-mo-outline" onclick="closeModal('modal-record-utilities')">Hủy</button>
        <button type="submit" class="btn-mo-primary" style="background:#059669;"><i class="fa-solid fa-paper-plane"></i> Tự Động Xuất Thông Báo Phí (Debit Note)</button>
      </div>
    </form>
  </div>
</div>

<!-- MODAL ĐỐI SOÁT ỦY NHIỆM CHI & DUYỆT THANH TOÁN (ROLE 4: BQL KẾ TOÁN) -->
<div id="modal-reconcile-unc" class="mo-modal" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,0.65);backdrop-filter:blur(3px);z-index:9999;align-items:center;justify-content:center;padding:20px;">
  <div style="background:#fff;border-radius:12px;width:100%;max-width:560px;box-shadow:0 25px 50px -12px rgba(0,0,0,0.25);overflow:hidden;border:1px solid #cbd5e1;">
    <div style="background:linear-gradient(135deg,#065f46,#10b981);color:#fff;padding:16px 20px;display:flex;justify-content:space-between;align-items:center;">
      <h3 style="margin:0;font-size:16px;font-weight:700;"><i class="fa-solid fa-stamp"></i> Đối Soát Ủy Nhiệm Chi & Cập Nhật Trạng Thái</h3>
      <button type="button" onclick="closeModal('modal-reconcile-unc')" style="background:none;border:none;color:#fff;font-size:22px;cursor:pointer;">&times;</button>
    </div>
    <form onsubmit="handleSaveReconcileStatus(event)" style="padding:20px;">
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px;">
        <div>
          <label style="font-size:12px;font-weight:700;color:#334155;display:block;margin-bottom:4px;">MÃ HÓA ĐƠN</label>
          <input type="text" id="rec-code" class="filament-input" readonly style="width:100%;background:#f1f5f9;font-weight:700;">
        </div>
        <div>
          <label style="font-size:12px;font-weight:700;color:#334155;display:block;margin-bottom:4px;">SỐ TIỀN CẦN THANH TOÁN</label>
          <input type="text" id="rec-amount" class="filament-input" readonly style="width:100%;background:#f1f5f9;font-weight:700;color:#dc2626;">
        </div>
      </div>

      <div style="margin-bottom:12px;">
        <label style="font-size:12px;font-weight:700;color:#334155;display:block;margin-bottom:4px;">DOANH NGHIỆP NỘP TIỀN</label>
        <input type="text" id="rec-company" class="filament-input" readonly style="width:100%;background:#f1f5f9;">
      </div>

      <!-- Ảnh xem thử UNC -->
      <div style="margin-bottom:14px;">
        <label style="font-size:12px;font-weight:700;color:#334155;display:block;margin-bottom:4px;">ẢNH CHỨNG TỪ ỦY NHIỆM CHI (UNC) ĐÃ TẢI LÊN</label>
        <div style="background:#f8fafc;border:1px solid #cbd5e1;border-radius:8px;padding:12px;display:flex;align-items:center;justify-content:space-between;">
          <div style="display:flex;align-items:center;gap:10px;">
            <i class="fa-solid fa-file-image" style="font-size:24px;color:#2563eb;"></i>
            <div>
              <strong style="font-size:13px;color:#0f172a;" id="rec-file-name">UNC_VNG_Nov2026.png</strong>
              <div style="font-size:11px;color:#64748b;">Ngân hàng chuyển: Vietcombank • Mã FT261117894562</div>
            </div>
          </div>
          <button type="button" class="btn-mo-outline" style="font-size:12px;padding:4px 10px;" onclick="alert('Đang hiển thị bản phóng to hình ảnh Ủy Nhiệm Chi hợp lệ!')">Xem ảnh lớn</button>
        </div>
      </div>

      <!-- Lựa chọn trạng thái xác nhận theo yêu cầu -->
      <div style="margin-bottom:16px;">
        <label style="font-size:12px;font-weight:700;color:#334155;display:block;margin-bottom:6px;">XÁC NHẬN TRẠNG THÁI THANH TOÁN (ROLE 4)</label>
        <div style="display:flex;flex-direction:column;gap:8px;">
          <label style="display:flex;align-items:center;gap:8px;font-size:13px;cursor:pointer;">
            <input type="radio" name="reconcile_status" value="PENDING" id="rad-status-pending">
            <span class="badge-filament warning">Chờ thanh toán</span> - Chưa khớp lệnh hoặc số dư chưa nổi
          </label>
          <label style="display:flex;align-items:center;gap:8px;font-size:13px;cursor:pointer;">
            <input type="radio" name="reconcile_status" value="PARTIAL" id="rad-status-partial">
            <span class="badge-filament purple" style="background:#f3e8ff;color:#7e22ce;">Thanh toán một phần</span> - Khách đã tạm ứng 50%
          </label>
          <label style="display:flex;align-items:center;gap:8px;font-size:13px;cursor:pointer;">
            <input type="radio" name="reconcile_status" value="PAID" id="rad-status-paid" checked>
            <span class="badge-filament success">Hoàn tất</span> - Đã khớp toàn bộ cước phí và xuất hóa đơn GTGT
          </label>
        </div>
      </div>

      <div style="display:flex;justify-content:flex-end;gap:10px;">
        <button type="button" class="btn-mo-outline" onclick="closeModal('modal-reconcile-unc')">Đóng</button>
        <button type="submit" class="btn-mo-primary" style="background:#059669;"><i class="fa-solid fa-floppy-disk"></i> Lưu Xác Nhận Đối Soát</button>
      </div>
    </form>
  </div>
</div>
