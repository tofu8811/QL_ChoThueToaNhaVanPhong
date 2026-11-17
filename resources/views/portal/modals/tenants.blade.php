<!-- MODAL 1: TẠO KHÁCH THUÊ MỚI (POST /api/v1/admin/tenants) -->
<div id="modal-create-tenant" class="mo-modal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(15,23,42,0.6);backdrop-filter:blur(3px);z-index:9999;align-items:center;justify-content:center;">
  <div style="background:#ffffff;border-radius:12px;width:100%;max-width:540px;box-shadow:0 20px 25px -5px rgba(0,0,0,0.1);overflow:hidden;border:1px solid #e2e8f0;">
    <div style="background:var(--mo-primary);color:#ffffff;padding:16px 20px;display:flex;justify-content:space-between;align-items:center;">
      <h3 style="margin:0;font-size:16px;font-weight:700;"><i class="fa-solid fa-building-user"></i> Tạo Hồ Sơ Khách Thuê Doanh Nghiệp Mới</h3>
      <button type="button" onclick="closeCreateTenantModal()" style="background:none;border:none;color:#ffffff;font-size:18px;cursor:pointer;">&times;</button>
    </div>
    <form id="form-create-tenant" onsubmit="submitCreateTenant(event)" style="padding:20px;">
      <div style="display:grid;grid-template-columns:1.4fr 1fr;gap:12px;margin-bottom:12px;">
        <div class="form-field">
          <label style="font-size:12px;font-weight:600;color:#334155;">Tên Doanh Nghiệp / Công Ty <span style="color:#ef4444;">*</span></label>
          <input type="text" id="tenant-company-name" class="filament-input-sm" required placeholder="Công ty TNHH VinaTech">
        </div>
        <div class="form-field">
          <label style="font-size:12px;font-weight:600;color:#334155;">Mã Số Thuế (MST)</label>
          <input type="text" id="tenant-tax-code" class="filament-input-sm" placeholder="0312345678">
        </div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px;">
        <div class="form-field">
          <label style="font-size:12px;font-weight:600;color:#334155;">Người Đại Diện Pháp Luật</label>
          <input type="text" id="tenant-rep-name" class="filament-input-sm" placeholder="Nguyễn Văn B">
        </div>
        <div class="form-field">
          <label style="font-size:12px;font-weight:600;color:#334155;">Số Điện Thoại Liên Hệ</label>
          <input type="tel" id="tenant-contact-phone" class="filament-input-sm" placeholder="0987654321">
        </div>
      </div>
      <div style="display:grid;grid-template-columns:1.2fr 1fr;gap:12px;margin-bottom:12px;">
        <div class="form-field">
          <label style="font-size:12px;font-weight:600;color:#334155;">Email Công Ty / Kế Toán</label>
          <input type="email" id="tenant-contact-email" class="filament-input-sm" placeholder="contact@vinatech.vn">
        </div>
        <div class="form-field">
          <label style="font-size:12px;font-weight:600;color:#334155;">Trạng Thái Hồ Sơ</label>
          <select id="tenant-status" class="filament-input-sm">
            <option value="ACTIVE">Đang Hoạt Động</option>
            <option value="INACTIVE">Ngừng Hoạt Động</option>
          </select>
        </div>
      </div>
      <div class="form-field" style="margin-bottom:16px;">
        <label style="font-size:12px;font-weight:600;color:#334155;">Địa Chỉ Trụ Sở ĐKKD</label>
        <input type="text" id="tenant-address" class="filament-input-sm" placeholder="Tầng 10, Tòa nhà Bitexco, Q.1, TP.HCM">
      </div>
      <div style="display:flex;justify-content:flex-end;gap:8px;border-top:1px solid #f1f5f9;padding-top:14px;">
        <button type="button" class="btn-mo-outline" onclick="closeCreateTenantModal()">Hủy Bỏ</button>
        <button type="submit" class="btn-mo-orange"><i class="fa-solid fa-check"></i> Lưu Hồ Sơ Khách Thuê</button>
      </div>
    </form>
  </div>
</div>

<!-- MODAL 2: SỬA HỒ SƠ PHÁP LÝ KHÁCH THUÊ (PUT /api/v1/admin/tenants/{id}) -->
<div id="modal-edit-tenant" class="mo-modal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(15,23,42,0.6);backdrop-filter:blur(3px);z-index:9999;align-items:center;justify-content:center;">
  <div style="background:#ffffff;border-radius:12px;width:100%;max-width:540px;box-shadow:0 20px 25px -5px rgba(0,0,0,0.1);overflow:hidden;border:1px solid #e2e8f0;">
    <div style="background:var(--mo-primary);color:#ffffff;padding:16px 20px;display:flex;justify-content:space-between;align-items:center;">
      <h3 style="margin:0;font-size:16px;font-weight:700;"><i class="fa-solid fa-pen-to-square"></i> Cập Nhật Thông Tin Khách Thuê</h3>
      <button type="button" onclick="closeEditTenantModal()" style="background:none;border:none;color:#ffffff;font-size:18px;cursor:pointer;">&times;</button>
    </div>
    <form id="form-edit-tenant" onsubmit="submitEditTenant(event)" style="padding:20px;">
      <div style="display:grid;grid-template-columns:1.4fr 1fr;gap:12px;margin-bottom:12px;">
        <div class="form-field">
          <label style="font-size:12px;font-weight:600;color:#334155;">Tên Doanh Nghiệp <span style="color:#ef4444;">*</span></label>
          <input type="text" id="edit-tenant-company-name" class="filament-input-sm" required>
        </div>
        <div class="form-field">
          <label style="font-size:12px;font-weight:600;color:#334155;">Mã Số Thuế (MST)</label>
          <input type="text" id="edit-tenant-tax-code" class="filament-input-sm">
        </div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px;">
        <div class="form-field">
          <label style="font-size:12px;font-weight:600;color:#334155;">Người Đại Diện</label>
          <input type="text" id="edit-tenant-rep-name" class="filament-input-sm">
        </div>
        <div class="form-field">
          <label style="font-size:12px;font-weight:600;color:#334155;">Số Điện Thoại</label>
          <input type="tel" id="edit-tenant-contact-phone" class="filament-input-sm">
        </div>
      </div>
      <div style="display:grid;grid-template-columns:1.2fr 1fr;gap:12px;margin-bottom:12px;">
        <div class="form-field">
          <label style="font-size:12px;font-weight:600;color:#334155;">Email Liên Hệ</label>
          <input type="email" id="edit-tenant-contact-email" class="filament-input-sm">
        </div>
        <div class="form-field">
          <label style="font-size:12px;font-weight:600;color:#334155;">Trạng Thái</label>
          <select id="edit-tenant-status" class="filament-input-sm">
            <option value="ACTIVE">Đang Hoạt Động</option>
            <option value="INACTIVE">Ngừng Hoạt Động</option>
          </select>
        </div>
      </div>
      <div class="form-field" style="margin-bottom:16px;">
        <label style="font-size:12px;font-weight:600;color:#334155;">Địa Chỉ Trụ Sở</label>
        <input type="text" id="edit-tenant-address" class="filament-input-sm">
      </div>
      <div style="display:flex;justify-content:flex-end;gap:8px;border-top:1px solid #f1f5f9;padding-top:14px;">
        <button type="button" class="btn-mo-outline" onclick="closeEditTenantModal()">Đóng</button>
        <button type="submit" class="btn-mo-primary"><i class="fa-solid fa-save"></i> Cập Nhật Hồ Sơ</button>
      </div>
    </form>
  </div>
</div>

<!-- MODAL 3: UPLOAD GIẤY PHÉP ĐKKD LÊN SUPABASE (POST /api/v1/admin/tenants/{id}/upload-license) -->
<div id="modal-upload-license" class="mo-modal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(15,23,42,0.6);backdrop-filter:blur(3px);z-index:9999;align-items:center;justify-content:center;">
  <div style="background:#ffffff;border-radius:12px;width:100%;max-width:440px;box-shadow:0 20px 25px -5px rgba(0,0,0,0.1);overflow:hidden;border:1px solid #e2e8f0;">
    <div style="background:var(--mo-primary);color:#ffffff;padding:14px 18px;display:flex;justify-content:space-between;align-items:center;">
      <h3 style="margin:0;font-size:15px;font-weight:700;"><i class="fa-solid fa-cloud-arrow-up"></i> Tải Lên Giấy Phép Kinh Doanh</h3>
      <button type="button" onclick="closeUploadLicenseModal()" style="background:none;border:none;color:#ffffff;font-size:18px;cursor:pointer;">&times;</button>
    </div>
    <form id="form-upload-license" onsubmit="submitUploadLicense(event)" style="padding:18px;">
      <p style="font-size:13px;color:#475569;margin-bottom:14px;">
        Tải lên tài liệu scan GPKD / Giấy phép thành lập cho: <strong id="upload-license-company-name" style="color:#0f172a;"></strong>
      </p>
      <div class="form-field" style="margin-bottom:16px;">
        <label style="font-size:12px;font-weight:600;color:#334155;">Chọn Tập Tin (PDF, PNG, JPG, tối đa 10MB)</label>
        <input type="file" id="license-file-input" accept=".pdf,.png,.jpg,.jpeg" required style="font-size:12px;padding:8px;border:1px dashed #cbd5e1;border-radius:6px;width:100%;">
      </div>
      <div style="display:flex;justify-content:flex-end;gap:8px;">
        <button type="button" class="btn-mo-outline" onclick="closeUploadLicenseModal()">Hủy</button>
        <button type="submit" class="btn-mo-orange"><i class="fa-solid fa-upload"></i> Upload Supabase Storage</button>
      </div>
    </form>
  </div>
</div>

<!-- MODAL 4: ĐĂNG KÝ THÊM NHÂN VIÊN VĂN PHÒNG (ROLE 2: TENANT) -->
<div id="modal-add-tenant-employee" class="mo-modal" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,0.65);backdrop-filter:blur(3px);z-index:9999;align-items:center;justify-content:center;padding:20px;">
  <div style="background:#fff;border-radius:12px;width:100%;max-width:500px;box-shadow:0 25px 50px -12px rgba(0,0,0,0.25);overflow:hidden;border:1px solid #cbd5e1;">
    <div style="background:linear-gradient(135deg,#1e3a8a,#2563eb);color:#fff;padding:16px 20px;display:flex;justify-content:space-between;align-items:center;">
      <h3 style="margin:0;font-size:16px;font-weight:700;"><i class="fa-solid fa-user-plus"></i> Đăng Ký Cấp Thẻ Nhân Viên Mới</h3>
      <button type="button" onclick="closeModal('modal-add-tenant-employee')" style="background:none;border:none;color:#fff;font-size:22px;cursor:pointer;">&times;</button>
    </div>
    <form onsubmit="handleSaveNewEmployee(event)" style="padding:20px;">
      <div style="margin-bottom:12px;">
        <label style="font-size:12px;font-weight:700;color:#334155;display:block;margin-bottom:4px;">HỌ VÀ TÊN NHÂN VIÊN <span style="color:#ef4444;">*</span></label>
        <input type="text" id="new-emp-name" class="filament-input" required placeholder="Nguyễn Văn A" style="width:100%;">
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px;">
        <div>
          <label style="font-size:12px;font-weight:700;color:#334155;display:block;margin-bottom:4px;">EMAIL CÔNG TY <span style="color:#ef4444;">*</span></label>
          <input type="email" id="new-emp-email" class="filament-input" required placeholder="staff@company.vn" style="width:100%;">
        </div>
        <div>
          <label style="font-size:12px;font-weight:700;color:#334155;display:block;margin-bottom:4px;">SỐ ĐIỆN THOẠI</label>
          <input type="text" id="new-emp-phone" class="filament-input" placeholder="0901234567" style="width:100%;">
        </div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:16px;">
        <div>
          <label style="font-size:12px;font-weight:700;color:#334155;display:block;margin-bottom:4px;">PHÒNG BAN / CHỨC VỤ</label>
          <input type="text" id="new-emp-dept" class="filament-input" placeholder="Kỹ thuật / Marketing" style="width:100%;">
        </div>
        <div>
          <label style="font-size:12px;font-weight:700;color:#334155;display:block;margin-bottom:4px;">BIỂN SỐ XE (NẾU CÓ)</label>
          <input type="text" id="new-emp-plate" class="filament-input" placeholder="59A-123.45" style="width:100%;">
        </div>
      </div>
      <div style="display:flex;justify-content:flex-end;gap:10px;">
        <button type="button" class="btn-mo-outline" onclick="closeModal('modal-add-tenant-employee')">Hủy bỏ</button>
        <button type="submit" class="btn-mo-primary"><i class="fa-solid fa-check"></i> Cấp thẻ & Gửi BQL</button>
      </div>
    </form>
  </div>
</div>

<!-- MODAL 5: ĐĂNG KÝ THI CÔNG NGOÀI GIỜ (ROLE 2: TENANT -> ROLE 4: BQL DUYỆT) -->
<div id="modal-tenant-after-hours" class="mo-modal" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,0.65);backdrop-filter:blur(3px);z-index:9999;align-items:center;justify-content:center;padding:20px;">
  <div style="background:#fff;border-radius:12px;width:100%;max-width:560px;box-shadow:0 25px 50px -12px rgba(0,0,0,0.25);overflow:hidden;border:1px solid #cbd5e1;">
    <div style="background:linear-gradient(135deg,#0f172a,#d97706);color:#fff;padding:16px 20px;display:flex;justify-content:space-between;align-items:center;">
      <h3 style="margin:0;font-size:16px;font-weight:700;"><i class="fa-solid fa-clock"></i> Đăng Ký Giấy Phép Thi Công Ngoài Giờ</h3>
      <button type="button" onclick="closeModal('modal-tenant-after-hours')" style="background:none;border:none;color:#fff;font-size:22px;cursor:pointer;">&times;</button>
    </div>
    <form onsubmit="handleSubmitAfterHours(event)" style="padding:20px;">
      <div style="margin-bottom:12px;">
        <label style="font-size:12px;font-weight:700;color:#334155;display:block;margin-bottom:4px;">NỘI DUNG / HẠNG MỤC THI CÔNG <span style="color:#ef4444;">*</span></label>
        <input type="text" id="ah-title" class="filament-input" required placeholder="Lắp đặt vách kính cách âm phòng họp & đi dây mạng" style="width:100%;">
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px;">
        <div>
          <label style="font-size:12px;font-weight:700;color:#334155;display:block;margin-bottom:4px;">NGÀY THI CÔNG <span style="color:#ef4444;">*</span></label>
          <input type="date" id="ah-date" class="filament-input" required value="2026-11-20" style="width:100%;">
        </div>
        <div>
          <label style="font-size:12px;font-weight:700;color:#334155;display:block;margin-bottom:4px;">KHUNG GIỜ THI CÔNG <span style="color:#ef4444;">*</span></label>
          <select id="ah-time" class="filament-input" style="width:100%;">
            <option value="18:00 - 22:00">18:00 - 22:00 (Buổi tối ngày thường)</option>
            <option value="22:00 - 06:00">22:00 - 06:00 (Xuyên đêm)</option>
            <option value="08:00 - 18:00 T7/CN">08:00 - 18:00 (Thứ 7 & Chủ Nhật)</option>
          </select>
        </div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px;">
        <div>
          <label style="font-size:12px;font-weight:700;color:#334155;display:block;margin-bottom:4px;">SỐ LƯỢNG CÔNG NHÂN</label>
          <input type="number" id="ah-workers" class="filament-input" value="4" min="1" max="50" style="width:100%;">
        </div>
        <div>
          <label style="font-size:12px;font-weight:700;color:#334155;display:block;margin-bottom:4px;">ĐƠN VỊ THI CÔNG (NHÀ THẦU)</label>
          <input type="text" id="ah-contractor" class="filament-input" placeholder="Công ty Nội Thất An Cường" style="width:100%;">
        </div>
      </div>
      <div style="margin-bottom:14px;">
        <label style="font-size:12px;font-weight:700;color:#334155;display:block;margin-bottom:4px;">DANH SÁCH CCCD CÔNG NHÂN VÀO TÒA NHÀ</label>
        <textarea id="ah-worker-list" class="filament-input" rows="2" placeholder="1. Nguyễn Văn Hùng - 079089001234&#10;2. Trần Văn Nam - 079090005678" style="width:100%;"></textarea>
      </div>
      <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:8px;padding:10px 12px;margin-bottom:16px;font-size:12px;color:#92400e;">
        <i class="fa-solid fa-triangle-exclamation"></i> Doanh nghiệp cam kết tuân thủ quy chuẩn PCCC, không sử dụng hàn xì khi chưa có bình chữa cháy dự phòng và dọn dẹp vệ sinh trước 06:00 sáng.
      </div>
      <div style="display:flex;justify-content:flex-end;gap:10px;">
        <button type="button" class="btn-mo-outline" onclick="closeModal('modal-tenant-after-hours')">Hủy</button>
        <button type="submit" class="btn-mo-primary" style="background:#d97706;"><i class="fa-solid fa-paper-plane"></i> Gửi BQL Phê Duyệt</button>
      </div>
    </form>
  </div>
</div>

<!-- MODAL 6: TẢI LÊN ỦY NHIỆM CHI (UNC) ĐỐI SOÁT CÔNG NỢ (ROLE 2: TENANT -> ROLE 4: BQL) -->
<div id="modal-upload-unc" class="mo-modal" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,0.65);backdrop-filter:blur(3px);z-index:9999;align-items:center;justify-content:center;padding:20px;">
  <div style="background:#fff;border-radius:12px;width:100%;max-width:500px;box-shadow:0 25px 50px -12px rgba(0,0,0,0.25);overflow:hidden;border:1px solid #cbd5e1;">
    <div style="background:linear-gradient(135deg,#065f46,#10b981);color:#fff;padding:16px 20px;display:flex;justify-content:space-between;align-items:center;">
      <h3 style="margin:0;font-size:16px;font-weight:700;"><i class="fa-solid fa-file-invoice-dollar"></i> Tải Lên Ủy Nhiệm Chi (UNC) Đối Soát</h3>
      <button type="button" onclick="closeModal('modal-upload-unc')" style="background:none;border:none;color:#fff;font-size:22px;cursor:pointer;">&times;</button>
    </div>
    <form onsubmit="handleSubmitUnc(event)" style="padding:20px;">
      <div style="margin-bottom:12px;">
        <label style="font-size:12px;font-weight:700;color:#334155;display:block;margin-bottom:4px;">HÓA ĐƠN / KỲ CƯỚC THANH TOÁN</label>
        <input type="text" id="unc-debit-code" class="filament-input" value="DN-202611-001 (Kỳ 11/2026 - 427.587.875 đ)" readonly style="width:100%;background:#f1f5f9;font-weight:700;">
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px;">
        <div>
          <label style="font-size:12px;font-weight:700;color:#334155;display:block;margin-bottom:4px;">NGÂN HÀNG CHUYỂN <span style="color:#ef4444;">*</span></label>
          <input type="text" id="unc-bank-name" class="filament-input" required value="Vietcombank" placeholder="Vietcombank / Techcombank" style="width:100%;">
        </div>
        <div>
          <label style="font-size:12px;font-weight:700;color:#334155;display:block;margin-bottom:4px;">MÃ GIAO DỊCH (FT CODE) <span style="color:#ef4444;">*</span></label>
          <input type="text" id="unc-ref-code" class="filament-input" required placeholder="FT261117894562" style="width:100%;font-family:monospace;">
        </div>
      </div>
      <div style="margin-bottom:16px;">
        <label style="font-size:12px;font-weight:700;color:#334155;display:block;margin-bottom:4px;">ẢNH CHỤP ỦY NHIỆM CHI / BIÊN LAI CHUYỂN KHOẢN <span style="color:#ef4444;">*</span></label>
        <div style="border:2px dashed #cbd5e1;padding:16px;text-align:center;border-radius:8px;background:#f8fafc;">
          <i class="fa-solid fa-cloud-arrow-up" style="font-size:28px;color:#059669;margin-bottom:6px;display:block;"></i>
          <span style="font-size:12.5px;color:#475569;display:block;margin-bottom:8px;" id="unc-preview-name">Chọn ảnh chụp UNC (.png, .jpg, .pdf)</span>
          <label class="btn-mo-primary" style="font-size:12px;padding:5px 14px;cursor:pointer;background:#059669;margin:0;">
            <i class="fa-solid fa-image"></i> Chọn tập tin
            <input type="file" accept="image/*,.pdf" style="display:none;" onchange="handleSelectUncFile(this)" required>
          </label>
        </div>
      </div>
      <div style="display:flex;justify-content:flex-end;gap:10px;">
        <button type="button" class="btn-mo-outline" onclick="closeModal('modal-upload-unc')">Đóng</button>
        <button type="submit" class="btn-mo-primary" style="background:#059669;"><i class="fa-solid fa-check"></i> Gửi Kế Toán Đối Soát</button>
      </div>
    </form>
  </div>
</div>

<!-- MODAL 7: ĐÁNH GIÁ CHẤT LƯỢNG CSAT DỊCH VỤ BQL (ROLE 2: TENANT -> CSAT RATING) -->
<div id="modal-tenant-csat" class="mo-modal" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,0.65);backdrop-filter:blur(3px);z-index:9999;align-items:center;justify-content:center;padding:20px;">
  <div style="background:#fff;border-radius:12px;width:100%;max-width:460px;box-shadow:0 25px 50px -12px rgba(0,0,0,0.25);overflow:hidden;border:1px solid #cbd5e1;">
    <div style="background:linear-gradient(135deg,#7e22ce,#a855f7);color:#fff;padding:16px 20px;display:flex;justify-content:space-between;align-items:center;">
      <h3 style="margin:0;font-size:16px;font-weight:700;"><i class="fa-solid fa-star"></i> Đánh Giá Chất Lượng Dịch Vụ (CSAT)</h3>
      <button type="button" onclick="closeModal('modal-tenant-csat')" style="background:none;border:none;color:#fff;font-size:22px;cursor:pointer;">&times;</button>
    </div>
    <form onsubmit="handleSubmitCsat(event)" style="padding:20px;">
      <p style="font-size:13px;color:#475569;margin-bottom:14px;">
        Nghiệm thu Ticket: <strong id="csat-ticket-code" style="color:#7e22ce;">TK-202611-0001</strong>
      </p>
      <div style="text-align:center;margin-bottom:18px;">
        <label style="font-size:12.5px;font-weight:700;color:#334155;display:block;margin-bottom:8px;">MỨC ĐỘ HÀI LÒNG CỦA BẠN</label>
        <div style="font-size:28px;color:#f59e0b;display:flex;justify-content:center;gap:10px;cursor:pointer;" id="csat-stars-container">
          <i class="fa-solid fa-star csat-star" data-val="1" onclick="setCsatRating(1)"></i>
          <i class="fa-solid fa-star csat-star" data-val="2" onclick="setCsatRating(2)"></i>
          <i class="fa-solid fa-star csat-star" data-val="3" onclick="setCsatRating(3)"></i>
          <i class="fa-solid fa-star csat-star" data-val="4" onclick="setCsatRating(4)"></i>
          <i class="fa-solid fa-star csat-star" data-val="5" onclick="setCsatRating(5)"></i>
        </div>
        <span style="font-size:12px;color:#64748b;margin-top:4px;display:block;" id="csat-rating-text">5 sao - Rất hài lòng</span>
      </div>
      <div style="margin-bottom:16px;">
        <label style="font-size:12px;font-weight:700;color:#334155;display:block;margin-bottom:4px;">GÓP Ý CHO ĐỘI NGŨ KỸ THUẬT / BQL</label>
        <textarea id="csat-feedback" class="filament-input" rows="3" placeholder="Kỹ thuật viên có mặt đúng hẹn, xử lý chuyên nghiệp và dọn dẹp sạch sẽ sau sửa chữa." style="width:100%;"></textarea>
      </div>
      <div style="display:flex;justify-content:flex-end;gap:10px;">
        <button type="button" class="btn-mo-outline" onclick="closeModal('modal-tenant-csat')">Bỏ qua</button>
        <button type="submit" class="btn-mo-primary" style="background:#7e22ce;"><i class="fa-solid fa-paper-plane"></i> Gửi Đánh Giá</button>
      </div>
    </form>
  </div>
</div>
