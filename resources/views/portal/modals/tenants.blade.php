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
