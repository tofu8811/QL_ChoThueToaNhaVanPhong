<!-- MODAL 1: LẬP HỢP ĐỒNG THUÊ MỚI (POST /api/v1/admin/contracts) -->
<div id="modal-create-contract" class="mo-modal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(15,23,42,0.6);backdrop-filter:blur(3px);z-index:9999;align-items:center;justify-content:center;">
  <div style="background:#ffffff;border-radius:12px;width:100%;max-width:760px;box-shadow:0 20px 25px -5px rgba(0,0,0,0.1);overflow:hidden;border:1px solid #e2e8f0;max-height:94vh;display:flex;flex-direction:column;">
    <div style="background:var(--mo-primary);color:#ffffff;padding:14px 20px;display:flex;justify-content:space-between;align-items:center;">
      <div>
        <h3 style="margin:0;font-size:16px;font-weight:700;"><i class="fa-solid fa-file-signature"></i> Khởi Tạo Hợp Đồng Thuê Văn Phòng (Bản Nháp)</h3>
        <p style="margin:2px 0 0;font-size:12px;opacity:0.85;">Dành cho Chuyên viên Sales & Ban Quản Lý (PM) — Hỗ trợ khóa bi quan lockForUpdate()</p>
      </div>
      <button type="button" onclick="closeCreateContractModal()" style="background:none;border:none;color:#ffffff;font-size:20px;cursor:pointer;">&times;</button>
    </div>
    <form id="form-create-contract" onsubmit="submitCreateContract(event)" novalidate style="padding:18px 20px;overflow-y:auto;flex:1;">
      
      <!-- PHẦN 1: THÔNG TIN PHÁP LÝ & VỊ TRÍ THUÊ -->
      <div style="font-size:12px;font-weight:700;color:var(--mo-primary);text-transform:uppercase;letter-spacing:0.5px;margin-bottom:8px;display:flex;align-items:center;gap:6px;">
        <i class="fa-solid fa-building-circle-check"></i> 1. Chủ thể & Vị trí mặt bằng
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px;">
        <div class="form-field">
          <label style="font-size:12px;font-weight:600;color:#334155;display:block;margin-bottom:4px;">Loại Hợp Đồng <span style="color:#ef4444;">*</span></label>
          <select id="contract-type-select" class="filament-input-sm">
            <option value="DRAFT" selected>Dự thảo hợp đồng thuê (DRAFT)</option>
            <option value="BOOKING">Hợp đồng đặt cọc giữ chỗ (BOOKING)</option>
          </select>
        </div>
        <div class="form-field">
          <label style="font-size:12px;font-weight:600;color:#334155;display:block;margin-bottom:4px;">Khách Thuê Doanh Nghiệp <span style="color:#ef4444;">*</span></label>
          <select id="contract-tenant-select" class="filament-input-sm" style="min-height:38px;height:auto;padding-top:6px;padding-bottom:6px;" onchange="handleContractTenantChange(this.value)">
            <!-- Rendered via JS -->
          </select>
          <div class="field-error-slot" id="err-contract-tenant"></div>
        </div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px;">
        <div class="form-field">
          <label style="font-size:12px;font-weight:600;color:#334155;display:block;margin-bottom:4px;">Tòa Nhà Văn Phòng <span style="color:#ef4444;">*</span></label>
          <select id="contract-building-select" class="filament-input-sm" style="min-height:38px;height:auto;padding-top:6px;padding-bottom:6px;" onchange="handleContractBuildingChange(this.value)">
            <!-- Rendered via JS -->
          </select>
          <div class="field-error-slot" id="err-contract-building"></div>
        </div>
        <div class="form-field">
          <label style="font-size:12px;font-weight:600;color:#334155;display:block;margin-bottom:4px;">Mặt Bằng Cho Thuê <span style="color:#ef4444;">*</span></label>
          <select id="contract-unit-select" class="filament-input-sm" style="min-height:38px;height:auto;padding-top:6px;padding-bottom:6px;" onchange="handleContractUnitChange()">
            <option value="">-- Vui lòng chọn Tòa nhà trước --</option>
          </select>
          <div class="field-error-slot" id="err-contract-unit"></div>
        </div>
      </div>

      <!-- PHẦN 2: THÔNG SỐ DIỆN TÍCH & ĐƠN GIÁ ĐÀM PHÁN (NGHIỆP VỤ LEASING) -->
      <div style="font-size:12px;font-weight:700;color:var(--mo-primary);text-transform:uppercase;letter-spacing:0.5px;margin-bottom:8px;display:flex;align-items:center;gap:6px;">
        <i class="fa-solid fa-ruler-combined"></i> 2. Thông số sàn & Đơn giá đàm phán (m² / tháng)
      </div>
      <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:12px;margin-bottom:14px;">
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;margin-bottom:10px;">
          <div class="form-field">
            <label style="font-size:11.5px;font-weight:600;color:#475569;display:block;margin-bottom:3px;">Diện Tích Thuê NLA (m²) <span style="color:#ef4444;">*</span></label>
            <input type="number" step="0.1" id="contract-unit-area" class="filament-input-sm" placeholder="VD: 150" oninput="recalculateContractFinancials()">
            <div class="field-error-slot" id="err-contract-unit-area"></div>
          </div>
          <div class="form-field">
            <label style="font-size:11.5px;font-weight:600;color:#475569;display:block;margin-bottom:3px;">Đơn Giá Thuê (VNĐ/m²/tháng) <span style="color:#ef4444;">*</span></label>
            <input type="number" step="1000" id="contract-unit-rent-price" class="filament-input-sm" placeholder="VD: 1400000" oninput="recalculateContractFinancials()">
            <div class="field-error-slot" id="err-contract-unit-rent-price"></div>
          </div>
          <div class="form-field">
            <label style="font-size:11.5px;font-weight:600;color:#475569;display:block;margin-bottom:3px;">Phí Dịch Vụ Sàn (VNĐ/m²/tháng)</label>
            <input type="number" step="1000" id="contract-unit-service-fee" class="filament-input-sm" placeholder="VD: 150000" oninput="recalculateContractFinancials()">
            <div class="field-error-slot" id="err-contract-unit-service-fee"></div>
          </div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;">
          <div class="form-field">
            <label style="font-size:11.5px;font-weight:600;color:#475569;display:block;margin-bottom:3px;">Tổng Tiền Thuê Sàn (VNĐ/tháng)</label>
            <input type="number" step="1000" id="contract-rent" class="filament-input-sm" style="font-weight:600;color:#0f172a;" placeholder="Tự động tính (m² × giá)" oninput="calculateCustomDeposit()">
            <div class="field-error-slot" id="err-contract-rent"></div>
          </div>
          <div class="form-field">
            <label style="font-size:11.5px;font-weight:600;color:#475569;display:block;margin-bottom:3px;">Tổng Phí Dịch Vụ (VNĐ/tháng)</label>
            <input type="number" step="1000" id="contract-service-fee" class="filament-input-sm" style="font-weight:600;color:#0f172a;" placeholder="Tự động tính (m² × phí DV)" oninput="calculateCustomDeposit()">
            <div class="field-error-slot" id="err-contract-service-fee"></div>
          </div>
          <div class="form-field">
            <label style="font-size:11.5px;font-weight:600;color:#475569;display:block;margin-bottom:3px;">Tiền Cọc Bảo Đảm (3 Tháng) <span style="color:#ef4444;">*</span></label>
            <input type="number" step="1000" id="contract-deposit" class="filament-input-sm" style="font-weight:700;color:#047857;" placeholder="Quy chuẩn cọc 3 tháng" oninput="moClearFieldError('contract-deposit', 'err-contract-deposit')">
            <div class="field-error-slot" id="err-contract-deposit"></div>
          </div>
        </div>
      </div>

      <!-- CARD TÓM TẮT TÀI CHÍNH & KHÓA BI QUAN -->
      <div id="contract-financial-card" style="display:none;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:12px;margin-bottom:14px;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
          <span style="font-size:12px;font-weight:700;color:#166534;"><i class="fa-solid fa-calculator"></i> BÓC TÁCH TÀI CHÍNH HỢP ĐỒNG & KHÓA BI QUAN</span>
          <span style="font-size:11px;background:#dcfce7;color:#15803d;padding:2px 8px;border-radius:12px;font-weight:600;border:1px solid #86efac;">
            <i class="fa-solid fa-lock"></i> lockForUpdate() Active
          </span>
        </div>
        <div id="contract-financial-breakdown" style="font-size:12px;color:#1e293b;line-height:1.6;">
          <!-- Dynamically populated -->
        </div>
      </div>

      <!-- PHẦN 3: THỜI HẠN & KỲ THANH TOÁN -->
      <div style="font-size:12px;font-weight:700;color:var(--mo-primary);text-transform:uppercase;letter-spacing:0.5px;margin-bottom:8px;display:flex;align-items:center;gap:6px;">
        <i class="fa-solid fa-calendar-days"></i> 3. Thời hạn hợp đồng & Kỳ thanh toán
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;margin-bottom:12px;">
        <div class="form-field">
          <label style="font-size:12px;font-weight:600;color:#334155;display:block;margin-bottom:4px;">Mã Hợp Đồng (Hệ thống đề xuất)</label>
          <input type="text" id="contract-code-input" class="filament-input-sm" placeholder="VD: HD-NEXUS-2026-0001" oninput="moClearFieldError('contract-code-input', 'err-contract-code')">
          <div class="field-error-slot" id="err-contract-code"></div>
        </div>
        <div class="form-field">
          <label style="font-size:12px;font-weight:600;color:#334155;display:block;margin-bottom:4px;">Ngày Bắt Đầu Thuê <span style="color:#ef4444;">*</span></label>
          <input type="date" id="contract-start-date" class="filament-input-sm" onchange="handleContractStartDateChange(this.value)">
          <div class="field-error-slot" id="err-contract-start-date"></div>
        </div>
        <div class="form-field">
          <label style="font-size:12px;font-weight:600;color:#334155;display:block;margin-bottom:4px;">Ngày Kết Thúc Thuê <span style="color:#ef4444;">*</span></label>
          <input type="date" id="contract-end-date" class="filament-input-sm" onchange="moClearFieldError('contract-end-date', 'err-contract-end-date'); recalculateContractFinancials();">
          <div class="field-error-slot" id="err-contract-end-date"></div>
        </div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;margin-bottom:12px;">
        <div class="form-field">
          <label style="font-size:12px;font-weight:600;color:#334155;display:block;margin-bottom:4px;">Ngày Bàn Giao Mặt Bằng</label>
          <input type="date" id="contract-handover-date" class="filament-input-sm" onchange="moClearFieldError('contract-handover-date', 'err-contract-handover-date')">
          <div class="field-error-slot" id="err-contract-handover-date"></div>
        </div>
        <div class="form-field">
          <label style="font-size:12px;font-weight:600;color:#334155;display:block;margin-bottom:4px;">Kỳ Thanh Toán (Tháng)</label>
          <input type="number" id="contract-billing-cycle" value="1" min="1" class="filament-input-sm" oninput="moClearFieldError('contract-billing-cycle', 'err-contract-billing-cycle')">
          <div class="field-error-slot" id="err-contract-billing-cycle"></div>
        </div>
        <div class="form-field">
          <label style="font-size:12px;font-weight:600;color:#334155;display:block;margin-bottom:4px;">Hạn Đóng Tiền Hàng Tháng</label>
          <input type="number" id="contract-payment-day" value="5" min="1" max="28" class="filament-input-sm" oninput="moClearFieldError('contract-payment-day', 'err-contract-payment-day')">
          <div class="field-error-slot" id="err-contract-payment-day"></div>
        </div>
      </div>
      <div class="form-field" style="margin-bottom:16px;">
        <label style="font-size:12px;font-weight:600;color:#334155;display:block;margin-bottom:4px;">Điều Khoản Bổ Sung / Ghi Chú Fit-out</label>
        <textarea id="contract-notes" rows="2" class="filament-input-sm" placeholder="Quy định miễn phí fit-out 30 ngày, tiêu chuẩn bàn giao trần sàn hoàn thiện..."></textarea>
      </div>
      <div style="display:flex;justify-content:space-between;align-items:center;border-top:1px solid #f1f5f9;padding-top:14px;">
        <span style="font-size:11.5px;color:#64748b;">
          <i class="fa-solid fa-shield-halved text-emerald-600"></i> Thuật toán chống trùng lịch thuê (validateNoOverlap) kiểm tra tự động khi gửi
        </span>
        <div style="display:flex;gap:8px;">
          <button type="button" class="btn-mo-outline" onclick="closeCreateContractModal()">Hủy</button>
          <button type="submit" id="btn-submit-create-contract" class="btn-mo-orange"><i class="fa-solid fa-file-circle-plus"></i> Khởi Tạo Bản Nháp</button>
        </div>
      </div>
    </form>
  </div>
</div>
    </form>
  </div>
</div>

<!-- MODAL 2: GIỮ CHỖ SÀN (POST /api/v1/admin/contracts/reserve) -->
<div id="modal-reserve-contract" class="mo-modal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(15,23,42,0.6);backdrop-filter:blur(3px);z-index:9999;align-items:center;justify-content:center;">
  <div style="background:#ffffff;border-radius:12px;width:100%;max-width:520px;box-shadow:0 20px 25px -5px rgba(0,0,0,0.1);overflow:hidden;border:1px solid #e2e8f0;">
    <div style="background:#d97706;color:#ffffff;padding:14px 18px;display:flex;justify-content:space-between;align-items:center;">
      <h3 style="margin:0;font-size:15px;font-weight:700;"><i class="fa-solid fa-hourglass-half"></i> Đặt Cọc Giữ Chỗ Sàn Văn Phòng</h3>
      <button type="button" onclick="closeReserveContractModal()" style="background:none;border:none;color:#ffffff;font-size:18px;cursor:pointer;">&times;</button>
    </div>
    <form id="form-reserve-contract" onsubmit="submitReserveContract(event)" style="padding:18px;">
      <div class="form-field" style="margin-bottom:12px;">
        <label style="font-size:12px;font-weight:600;color:#334155;">Khách Hàng Đặt Cọc Giữ Chỗ <span style="color:#ef4444;">*</span></label>
        <select id="reserve-tenant-select" class="filament-input-sm" required></select>
      </div>
      <div class="form-field" style="margin-bottom:12px;">
        <label style="font-size:12px;font-weight:600;color:#334155;">Tòa Nhà Văn Phòng <span style="color:#ef4444;">*</span></label>
        <select id="reserve-building-select" class="filament-input-sm" required></select>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px;">
        <div class="form-field">
          <label style="font-size:12px;font-weight:600;color:#334155;">Số Tiền Đặt Cọc Giữ Chỗ ($)</label>
          <input type="number" step="0.01" id="reserve-deposit" class="filament-input-sm" placeholder="VD: 5000">
        </div>
        <div class="form-field">
          <label style="font-size:12px;font-weight:600;color:#334155;">Hạn Chót Giữ Chỗ <span style="color:#ef4444;">*</span></label>
          <input type="date" id="reserve-expiry-date" class="filament-input-sm" required>
        </div>
      </div>
      <div class="form-field" style="margin-bottom:16px;">
        <label style="font-size:12px;font-weight:600;color:#334155;">Ghi Chú Giữ Chỗ</label>
        <textarea id="reserve-notes" rows="2" class="filament-input-sm" placeholder="Ghi chú mặt bằng giữ, thỏa thuận..."></textarea>
      </div>
      <div style="display:flex;justify-content:flex-end;gap:8px;">
        <button type="button" class="btn-mo-outline" onclick="closeReserveContractModal()">Hủy</button>
        <button type="submit" class="btn-mo-primary" style="background:#d97706;border-color:#d97706;"><i class="fa-solid fa-check"></i> Tạo Giữ Chỗ (RESERVED)</button>
      </div>
    </form>
  </div>
</div>

<!-- MODAL 3: CHI TIẾT HỢP ĐỒNG (GET /api/v1/admin/contracts/{id}) -->
<div id="modal-contract-detail" class="mo-modal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(15,23,42,0.6);backdrop-filter:blur(3px);z-index:9999;align-items:center;justify-content:center;">
  <div style="background:#ffffff;border-radius:12px;width:100%;max-width:920px;box-shadow:0 20px 25px -5px rgba(0,0,0,0.1);overflow:hidden;border:1px solid #e2e8f0;max-height:92vh;display:flex;flex-direction:column;">
    <div style="background:var(--mo-primary);color:#ffffff;padding:16px 20px;display:flex;justify-content:space-between;align-items:center;">
      <h3 style="margin:0;font-size:16px;font-weight:700;"><i class="fa-solid fa-file-contract"></i> Hồ Sơ Hợp Đồng Thuê & Tài Liệu Ký Kết</h3>
      <button type="button" onclick="closeContractDetailModal()" style="background:none;border:none;color:#ffffff;font-size:18px;cursor:pointer;">&times;</button>
    </div>
    <div id="contract-detail-content" style="padding:20px;overflow-y:auto;flex:1;"></div>
  </div>
</div>

<!-- MODAL 4: GÁN MẶT BẰNG VÀO HỢP ĐỒNG (POST /api/v1/admin/contracts/{id}/units) -->
<div id="modal-attach-unit" class="mo-modal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(15,23,42,0.6);backdrop-filter:blur(3px);z-index:9999;align-items:center;justify-content:center;">
  <div style="background:#ffffff;border-radius:12px;width:100%;max-width:480px;box-shadow:0 20px 25px -5px rgba(0,0,0,0.1);overflow:hidden;border:1px solid #e2e8f0;">
    <div style="background:var(--mo-primary);color:#ffffff;padding:14px 18px;display:flex;justify-content:space-between;align-items:center;">
      <h3 style="margin:0;font-size:15px;font-weight:700;"><i class="fa-solid fa-layer-group"></i> Gán Sàn Mặt Bằng Vào Hợp Đồng</h3>
      <button type="button" onclick="closeAttachUnitModal()" style="background:none;border:none;color:#ffffff;font-size:18px;cursor:pointer;">&times;</button>
    </div>
    <div style="padding:18px;">
      <div class="form-field" style="margin-bottom:12px;">
        <label style="font-size:12px;font-weight:600;color:#334155;">Chọn Mặt Bằng Đang Trống <span style="color:#ef4444;">*</span></label>
        <select id="attach-unit-select" class="filament-input-sm" style="width:100%;"></select>
      </div>
      <div class="form-field" style="margin-bottom:16px;">
        <label style="font-size:12px;font-weight:600;color:#334155;">Đơn Giá Thuê Thỏa Thuận (VNĐ/m²/tháng - Bỏ trống lấy giá niêm yết)</label>
        <input type="number" step="1000" id="attach-unit-price" class="filament-input-sm" placeholder="VD: 650000">
      </div>
      <div style="display:flex;justify-content:flex-end;gap:8px;">
        <button type="button" class="btn-mo-outline" onclick="closeAttachUnitModal()">Hủy</button>
        <button type="button" class="btn-mo-primary" onclick="submitAttachUnit()"><i class="fa-solid fa-plus"></i> Gán Sàn</button>
      </div>
    </div>
  </div>
</div>

<!-- MODAL 5: GIA HẠN HỢP ĐỒNG (POST /api/v1/admin/contracts/{id}/renew) -->
<div id="modal-renew-contract" class="mo-modal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(15,23,42,0.6);backdrop-filter:blur(3px);z-index:9999;align-items:center;justify-content:center;">
  <div style="background:#ffffff;border-radius:12px;width:100%;max-width:480px;box-shadow:0 20px 25px -5px rgba(0,0,0,0.1);overflow:hidden;border:1px solid #e2e8f0;">
    <div style="background:#0284c7;color:#ffffff;padding:14px 18px;display:flex;justify-content:space-between;align-items:center;">
      <h3 style="margin:0;font-size:15px;font-weight:700;"><i class="fa-solid fa-calendar-plus"></i> Lập Phụ Lục Gia Hạn Hợp Đồng</h3>
      <button type="button" onclick="closeRenewContractModal()" style="background:none;border:none;color:#ffffff;font-size:18px;cursor:pointer;">&times;</button>
    </div>
    <div style="padding:18px;">
      <p style="font-size:13px;color:#475569;margin-bottom:12px;">
        Hợp đồng <strong id="renew-contract-code" style="color:#0f172a;"></strong> hiện hết hạn ngày: <span id="renew-current-end-date" style="font-weight:600;color:#ea580c;"></span>
      </p>
      <div class="form-field" style="margin-bottom:12px;">
        <label style="font-size:12px;font-weight:600;color:#334155;">Ngày Kết Thúc Thuê Mới <span style="color:#ef4444;">*</span></label>
        <input type="date" id="renew-new-end-date" class="filament-input-sm" required>
      </div>
      <div class="form-field" style="margin-bottom:12px;">
        <label style="font-size:12px;font-weight:600;color:#334155;">Đơn Giá Thuê Mới Điều Chỉnh ($/tháng - Bỏ trống nếu giữ nguyên)</label>
        <input type="number" step="0.01" id="renew-new-rent" class="filament-input-sm" placeholder="VD: 4800">
      </div>
      <div class="form-field" style="margin-bottom:16px;">
        <label style="font-size:12px;font-weight:600;color:#334155;">Lý Do / Ghi Chú Phụ Lục Gia Hạn</label>
        <textarea id="renew-notes" rows="2" class="filament-input-sm" placeholder="Gia hạn thêm 2 năm theo thỏa thuận..."></textarea>
      </div>
      <div style="display:flex;justify-content:flex-end;gap:8px;">
        <button type="button" class="btn-mo-outline" onclick="closeRenewContractModal()">Hủy</button>
        <button type="button" class="btn-mo-primary" style="background:#0284c7;border-color:#0284c7;" onclick="submitRenewContract()"><i class="fa-solid fa-check"></i> Xác Nhận Gia Hạn</button>
      </div>
    </div>
  </div>
</div>

<!-- MODAL 6: THANH LÝ HỢP ĐỒNG (POST /api/v1/admin/contracts/{id}/terminate) -->
<div id="modal-terminate-contract" class="mo-modal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(15,23,42,0.6);backdrop-filter:blur(3px);z-index:9999;align-items:center;justify-content:center;">
  <div style="background:#ffffff;border-radius:12px;width:100%;max-width:500px;box-shadow:0 20px 25px -5px rgba(0,0,0,0.1);overflow:hidden;border:1px solid #e2e8f0;">
    <div style="background:#dc2626;color:#ffffff;padding:14px 18px;display:flex;justify-content:space-between;align-items:center;">
      <h3 style="margin:0;font-size:15px;font-weight:700;"><i class="fa-solid fa-file-circle-xmark"></i> Quyết Toán & Thanh Lý Hợp Đồng Thuê</h3>
      <button type="button" onclick="closeTerminateContractModal()" style="background:none;border:none;color:#ffffff;font-size:18px;cursor:pointer;">&times;</button>
    </div>
    <div style="padding:18px;">
      <p style="font-size:13px;color:#475569;margin-bottom:12px;">
        Thanh lý hợp đồng <strong id="terminate-contract-code" style="color:#0f172a;"></strong>. Toàn bộ sàn trực thuộc sẽ được giải phóng mặt bằng.
      </p>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px;">
        <div class="form-field">
          <label style="font-size:12px;font-weight:600;color:#334155;">Ngày Bàn Giao / Trả Phòng <span style="color:#ef4444;">*</span></label>
          <input type="date" id="terminate-date" class="filament-input-sm" required>
        </div>
        <div class="form-field">
          <label style="font-size:12px;font-weight:600;color:#334155;">Tiền Cọc Đang Giữ</label>
          <div id="terminate-deposit-held" style="font-weight:700;color:#16a34a;padding-top:6px;">$0</div>
        </div>
      </div>
      <div class="form-field" style="margin-bottom:12px;">
        <label style="font-size:12px;font-weight:600;color:#334155;">Số Tiền Cọc Hoàn Trả Khách Hàng ($)</label>
        <input type="number" step="0.01" id="terminate-refund-amount" class="filament-input-sm" placeholder="VD: 15000">
      </div>
      <div class="form-field" style="margin-bottom:16px;">
        <label style="font-size:12px;font-weight:600;color:#334155;">Lý Do Thanh Lý & Hiện Trạng Bàn Giao</label>
        <textarea id="terminate-reason" rows="2" class="filament-input-sm" placeholder="Khách hết hạn hợp đồng không gia hạn, đã nghiệm thu hoàn trả hiện trạng..."></textarea>
      </div>
      <div style="display:flex;justify-content:flex-end;gap:8px;">
        <button type="button" class="btn-mo-outline" onclick="closeTerminateContractModal()">Hủy</button>
        <button type="button" class="btn-mo-primary" style="background:#dc2626;border-color:#dc2626;" onclick="submitTerminateContract()"><i class="fa-solid fa-check"></i> Xác Nhận Thanh Lý</button>
      </div>
    </div>
  </div>
</div>

<!-- MODAL 7: UPLOAD SCAN HỢP ĐỒNG (POST /api/v1/admin/contracts/{id}/upload-files) -->
<div id="modal-upload-contract-scan" class="mo-modal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(15,23,42,0.6);backdrop-filter:blur(3px);z-index:9999;align-items:center;justify-content:center;">
  <div style="background:#ffffff;border-radius:12px;width:100%;max-width:450px;box-shadow:0 20px 25px -5px rgba(0,0,0,0.1);overflow:hidden;border:1px solid #e2e8f0;">
    <div style="background:var(--mo-primary);color:#ffffff;padding:14px 18px;display:flex;justify-content:space-between;align-items:center;">
      <h3 style="margin:0;font-size:15px;font-weight:700;"><i class="fa-solid fa-cloud-arrow-up"></i> Tải Lên Bản Scan Hợp Đồng Đã Ký</h3>
      <button type="button" onclick="closeUploadContractScanModal()" style="background:none;border:none;color:#ffffff;font-size:18px;cursor:pointer;">&times;</button>
    </div>
    <form id="form-upload-contract-scan" onsubmit="submitUploadContractScan(event)" style="padding:18px;">
      <div class="form-field" style="margin-bottom:16px;">
        <label style="font-size:12px;font-weight:600;color:#334155;">Chọn File Scan Hợp Đồng (PDF, PNG, JPG, tối đa 20MB)</label>
        <input type="file" id="contract-scan-file-input" accept=".pdf,.png,.jpg,.jpeg" multiple required style="font-size:12px;padding:8px;border:1px dashed #cbd5e1;border-radius:6px;width:100%;">
      </div>
      <div style="display:flex;justify-content:flex-end;gap:8px;">
        <button type="button" class="btn-mo-outline" onclick="closeUploadContractScanModal()">Hủy</button>
        <button type="submit" class="btn-mo-orange"><i class="fa-solid fa-upload"></i> Upload Supabase Storage</button>
      </div>
    </form>
  </div>
</div>

<!-- MODAL 8: BIÊN BẢN BÀN GIAO MẶT BẰNG (ROLE 4: BQL) -->
<div id="modal-contract-handover" class="mo-modal" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,0.65);backdrop-filter:blur(3px);z-index:9999;align-items:center;justify-content:center;padding:20px;">
  <div style="background:#fff;border-radius:12px;width:100%;max-width:540px;box-shadow:0 25px 50px -12px rgba(0,0,0,0.25);overflow:hidden;border:1px solid #cbd5e1;">
    <div style="background:linear-gradient(135deg,#0f172a,#2563eb);color:#fff;padding:16px 20px;display:flex;justify-content:space-between;align-items:center;">
      <h3 style="margin:0;font-size:16px;font-weight:700;"><i class="fa-solid fa-clipboard-check"></i> Biên Bản Bàn Giao Mặt Bằng Văn Phòng</h3>
      <button type="button" onclick="closeModal('modal-contract-handover')" style="background:none;border:none;color:#fff;font-size:22px;cursor:pointer;">&times;</button>
    </div>
    <form onsubmit="handleSaveHandover(event)" style="padding:20px;">
      <div style="margin-bottom:12px;">
        <label style="font-size:12px;font-weight:700;color:#334155;display:block;margin-bottom:4px;">HỢP ĐỒNG THUÊ</label>
        <input type="text" id="handover-contract-code" class="filament-input" readonly style="width:100%;background:#f1f5f9;font-weight:700;">
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px;">
        <div>
          <label style="font-size:12px;font-weight:700;color:#334155;display:block;margin-bottom:4px;">NGÀY BÀN GIAO THỰC TẾ <span style="color:#ef4444;">*</span></label>
          <input type="date" id="handover-actual-date" class="filament-input" required value="2026-11-18" style="width:100%;">
        </div>
        <div>
          <label style="font-size:12px;font-weight:700;color:#334155;display:block;margin-bottom:4px;">TIÊU CHUẨN BÀN GIAO</label>
          <select id="handover-standard" class="filament-input" style="width:100%;">
            <option value="BASIC">Hoàn thiện cơ bản (Trần, sàn, điều hòa)</option>
            <option value="RAW">Bàn giao thô (Sàn bê tông)</option>
            <option value="FULL">Đầy đủ nội thất văn phòng</option>
          </select>
        </div>
      </div>
      <div style="margin-bottom:12px;">
        <label style="font-size:12px;font-weight:700;color:#334155;display:block;margin-bottom:4px;">HIỆN TRẠNG KỸ THUẬT KHI BÀN GIAO</label>
        <textarea id="handover-notes" class="filament-input" rows="2" style="width:100%;">Hệ thống điều hòa Daikin hoạt động tốt, đồng hồ điện số 12450 kWh, 2 van nước khóa an toàn, 4 bình chữa cháy CO2 kiểm định đạt chuẩn.</textarea>
      </div>
      <div style="margin-bottom:16px;">
        <label style="font-size:12px;font-weight:700;color:#334155;display:block;margin-bottom:4px;">TẢI LÊN BẢN QUÉT BIÊN BẢN KÝ TAY (.PDF / .JPG)</label>
        <input type="file" accept=".pdf,image/*" class="filament-input" style="width:100%;">
      </div>
      <div style="display:flex;justify-content:flex-end;gap:10px;">
        <button type="button" class="btn-mo-outline" onclick="closeModal('modal-contract-handover')">Hủy</button>
        <button type="submit" class="btn-mo-primary"><i class="fa-solid fa-floppy-disk"></i> Lưu & Ký Bàn Giao</button>
      </div>
    </form>
  </div>
</div>

<!-- MODAL 9: LỊCH TRÌNH TĂNG GIÁ TỰ ĐỘNG (ROLE 4: BQL) -->
<div id="modal-contract-escalation" class="mo-modal" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,0.65);backdrop-filter:blur(3px);z-index:9999;align-items:center;justify-content:center;padding:20px;">
  <div style="background:#fff;border-radius:12px;width:100%;max-width:540px;box-shadow:0 25px 50px -12px rgba(0,0,0,0.25);overflow:hidden;border:1px solid #cbd5e1;">
    <div style="background:linear-gradient(135deg,#15803d,#16a34a);color:#fff;padding:16px 20px;display:flex;justify-content:space-between;align-items:center;">
      <h3 style="margin:0;font-size:16px;font-weight:700;"><i class="fa-solid fa-arrow-trend-up"></i> Thiết Lập Lịch Trình Tăng Giá Tự Động</h3>
      <button type="button" onclick="closeModal('modal-contract-escalation')" style="background:none;border:none;color:#fff;font-size:22px;cursor:pointer;">&times;</button>
    </div>
    <form onsubmit="handleSaveEscalation(event)" style="padding:20px;">
      <div style="margin-bottom:12px;">
        <label style="font-size:12px;font-weight:700;color:#334155;display:block;margin-bottom:4px;">HỢP ĐỒNG ÁP DỤNG</label>
        <input type="text" id="esc-contract-code" class="filament-input" readonly style="width:100%;background:#f1f5f9;font-weight:700;">
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px;">
        <div>
          <label style="font-size:12px;font-weight:700;color:#334155;display:block;margin-bottom:4px;">CHU KỲ ĐIỀU CHỈNH</label>
          <select id="esc-cycle" class="filament-input" style="width:100%;">
            <option value="12">Mỗi 12 tháng (Hàng năm)</option>
            <option value="24">Mỗi 24 tháng (2 năm một lần)</option>
          </select>
        </div>
        <div>
          <label style="font-size:12px;font-weight:700;color:#334155;display:block;margin-bottom:4px;">TỶ LỆ TĂNG GIÁ (%)</label>
          <input type="number" step="0.5" id="esc-rate" class="filament-input" value="5.0" style="width:100%;">
        </div>
      </div>
      <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:12px;margin-bottom:16px;font-size:12.5px;color:#166534;">
        <i class="fa-solid fa-calculator"></i> Hệ thống sẽ tự động điều chỉnh biểu phí thanh toán của khách thuê vào đầu mỗi năm hợp đồng và gửi thông báo trước 30 ngày.
      </div>
      <div style="display:flex;justify-content:flex-end;gap:10px;">
        <button type="button" class="btn-mo-outline" onclick="closeModal('modal-contract-escalation')">Hủy</button>
        <button type="submit" class="btn-mo-primary" style="background:#16a34a;"><i class="fa-solid fa-check"></i> Lưu Lịch Trình Tăng Giá</button>
      </div>
    </form>
  </div>
</div>

<!-- MODAL 10: THÔNG BÁO TÀI KHOẢN KHÁCH THUÊ ĐƯỢC CẤP (KÝ DUYỆT THÀNH CÔNG) -->
<div id="modal-tenant-credential" class="mo-modal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(15,23,42,0.65);backdrop-filter:blur(4px);z-index:99999;align-items:center;justify-content:center;">
  <div style="background:#ffffff;border-radius:12px;width:100%;max-width:540px;box-shadow:0 25px 50px -12px rgba(0,0,0,0.25);overflow:hidden;border:1px solid #cbd5e1;">
    <div style="background:linear-gradient(135deg, #15803d, #16a34a);color:#ffffff;padding:16px 20px;display:flex;justify-content:space-between;align-items:center;">
      <h3 style="margin:0;font-size:16px;font-weight:700;"><i class="fa-solid fa-shield-halved"></i> Ký Duyệt Thành Công — Cấp Mật Khẩu Khách Thuê</h3>
      <button type="button" onclick="closeTenantCredentialModal()" style="background:none;border:none;color:#ffffff;font-size:20px;cursor:pointer;">&times;</button>
    </div>
    <div style="padding:20px;">
      <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:12px;margin-bottom:16px;font-size:12.5px;color:#166534;line-height:1.5;">
        <i class="fa-solid fa-circle-check"></i> Hợp đồng đã kích hoạt <strong>ACTIVE</strong> và các sàn đã chuyển sang <strong>LEASED</strong>. Tài khoản Cổng Khách Thuê (Tenant Portal) đã được khởi tạo:
      </div>
      <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:14px;margin-bottom:16px;font-size:13px;line-height:1.8;">
        <div><strong>Doanh nghiệp:</strong> <span id="cred-company-name" style="color:#0f172a;font-weight:600;"></span></div>
        <div><strong>Mã khách thuê:</strong> <span id="cred-tenant-code" style="color:var(--mo-primary);font-weight:700;font-family:monospace;"></span></div>
        <div><strong>Email đăng nhập:</strong> <span id="cred-email" style="color:#2563eb;font-weight:600;"></span></div>
        <div style="margin-top:6px;padding-top:6px;border-top:1px dashed #cbd5e1;display:flex;align-items:center;justify-content:space-between;">
          <span><strong>Mật khẩu khởi tạo:</strong> <code id="cred-password" style="font-size:14px;font-weight:800;color:#dc2626;background:#fee2e2;padding:2px 8px;border-radius:4px;letter-spacing:1px;"></code></span>
          <button type="button" class="btn-mo-outline" style="padding:3px 10px;font-size:11.5px;font-weight:600;" onclick="copyTenantPassword()"><i class="fa-solid fa-copy"></i> Sao chép</button>
        </div>
      </div>
      <p style="font-size:11.5px;color:#64748b;margin:0 0 16px;">
        <i class="fa-solid fa-circle-info"></i> Khách thuê bắt buộc phải đổi mật khẩu ở lần đăng nhập đầu tiên (<code>must_change_password: true</code>).
      </p>
      <div style="display:flex;justify-content:flex-end;gap:10px;">
        <button type="button" class="btn-mo-primary" style="background:#16a34a;border:none;padding:8px 18px;" onclick="closeTenantCredentialModal()">
          <i class="fa-solid fa-check"></i> Hoàn Tất & Đóng
        </button>
      </div>
    </div>
  </div>
</div>
