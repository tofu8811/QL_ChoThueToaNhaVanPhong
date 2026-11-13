<!-- MODAL 1: LẬP HỢP ĐỒNG THUÊ MỚI (POST /api/v1/admin/contracts) -->
<div id="modal-create-contract" class="mo-modal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(15,23,42,0.6);backdrop-filter:blur(3px);z-index:9999;align-items:center;justify-content:center;">
  <div style="background:#ffffff;border-radius:12px;width:100%;max-width:680px;box-shadow:0 20px 25px -5px rgba(0,0,0,0.1);overflow:hidden;border:1px solid #e2e8f0;max-height:92vh;display:flex;flex-direction:column;">
    <div style="background:var(--mo-primary);color:#ffffff;padding:16px 20px;display:flex;justify-content:space-between;align-items:center;">
      <h3 style="margin:0;font-size:16px;font-weight:700;"><i class="fa-solid fa-file-signature"></i> Khởi Tạo Hợp Đồng Thuê Văn Phòng Mới (Bản Nháp)</h3>
      <button type="button" onclick="closeCreateContractModal()" style="background:none;border:none;color:#ffffff;font-size:18px;cursor:pointer;">&times;</button>
    </div>
    <form id="form-create-contract" onsubmit="submitCreateContract(event)" style="padding:20px;overflow-y:auto;flex:1;">
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px;">
        <div class="form-field">
          <label style="font-size:12px;font-weight:600;color:#334155;">Khách Thuê Doanh Nghiệp <span style="color:#ef4444;">*</span></label>
          <select id="contract-tenant-select" class="filament-input-sm" required>
            <!-- Rendered via JS -->
          </select>
        </div>
        <div class="form-field">
          <label style="font-size:12px;font-weight:600;color:#334155;">Tòa Nhà Văn Phòng <span style="color:#ef4444;">*</span></label>
          <select id="contract-building-select" class="filament-input-sm" required>
            <!-- Rendered via JS -->
          </select>
        </div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;margin-bottom:12px;">
        <div class="form-field">
          <label style="font-size:12px;font-weight:600;color:#334155;">Mã Hợp Đồng (Tự sinh nếu bỏ trống)</label>
          <input type="text" id="contract-code-input" class="filament-input-sm" placeholder="VD: HD-2026-001">
        </div>
        <div class="form-field">
          <label style="font-size:12px;font-weight:600;color:#334155;">Ngày Bắt Đầu Thuê <span style="color:#ef4444;">*</span></label>
          <input type="date" id="contract-start-date" class="filament-input-sm" required>
        </div>
        <div class="form-field">
          <label style="font-size:12px;font-weight:600;color:#334155;">Ngày Kết Thúc Thuê <span style="color:#ef4444;">*</span></label>
          <input type="date" id="contract-end-date" class="filament-input-sm" required>
        </div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;margin-bottom:12px;">
        <div class="form-field">
          <label style="font-size:12px;font-weight:600;color:#334155;">Tiền Thuê Sàn ($/tháng)</label>
          <input type="number" step="0.01" id="contract-rent" class="filament-input-sm" placeholder="VD: 4500">
        </div>
        <div class="form-field">
          <label style="font-size:12px;font-weight:600;color:#334155;">Phí Dịch Vụ ($/tháng)</label>
          <input type="number" step="0.01" id="contract-service-fee" class="filament-input-sm" placeholder="VD: 500">
        </div>
        <div class="form-field">
          <label style="font-size:12px;font-weight:600;color:#334155;">Tiền Cọc Bảo Đảm ($)</label>
          <input type="number" step="0.01" id="contract-deposit" class="filament-input-sm" placeholder="VD: 15000">
        </div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;margin-bottom:12px;">
        <div class="form-field">
          <label style="font-size:12px;font-weight:600;color:#334155;">Ngày Bàn Giao Mặt Bằng</label>
          <input type="date" id="contract-handover-date" class="filament-input-sm">
        </div>
        <div class="form-field">
          <label style="font-size:12px;font-weight:600;color:#334155;">Kỳ Thanh Toán (Tháng)</label>
          <input type="number" id="contract-billing-cycle" value="1" min="1" class="filament-input-sm">
        </div>
        <div class="form-field">
          <label style="font-size:12px;font-weight:600;color:#334155;">Hạn Đóng Tiền Hàng Tháng</label>
          <input type="number" id="contract-payment-day" value="5" min="1" max="28" class="filament-input-sm">
        </div>
      </div>
      <div class="form-field" style="margin-bottom:16px;">
        <label style="font-size:12px;font-weight:600;color:#334155;">Điều Khoản Bổ Sung / Ghi Chú</label>
        <textarea id="contract-notes" rows="2" class="filament-input-sm" placeholder="Quy định ngày fit-out, miễn phí thời gian thi công..."></textarea>
      </div>
      <div style="display:flex;justify-content:flex-end;gap:8px;border-top:1px solid #f1f5f9;padding-top:14px;">
        <button type="button" class="btn-mo-outline" onclick="closeCreateContractModal()">Hủy</button>
        <button type="submit" class="btn-mo-orange"><i class="fa-solid fa-file-circle-plus"></i> Khởi Tạo Bản Nháp</button>
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
        <label style="font-size:12px;font-weight:600;color:#334155;">Đơn Giá Thuê Thỏa Thuận ($/m²/tháng - Bỏ trống lấy giá niêm yết)</label>
        <input type="number" step="0.01" id="attach-unit-price" class="filament-input-sm" placeholder="VD: 32">
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
