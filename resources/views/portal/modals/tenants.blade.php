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

@php
  $activeTenantId = request('tenant_id')
      ?? request()->cookie('tenant_id')
      ?? (\Illuminate\Support\Facades\Auth::guard('tenant')->id() ?? \Illuminate\Support\Facades\Auth::id());
  if (!$activeTenantId && request()->is('tenant*')) {
      $demoTenant = \App\Models\Tenant::where('login_email', 'tenant.demo@officeleasing.vn')->first()
          ?? \App\Models\Tenant::where('status', 'ACTIVE')->has('contracts')->first()
          ?? \App\Models\Tenant::first();
      $activeTenantId = $demoTenant?->id;
  }

  $tenantRentedBuildings = collect();
  if ($activeTenantId) {
      $tenantRentedBuildings = \App\Models\Building::whereHas('contracts', function($q) use ($activeTenantId) {
          $q->where('tenant_id', $activeTenantId);
      })->orderBy('name')->get();
  }

  if ($tenantRentedBuildings->isEmpty()) {
      if (request()->is('tenant*')) {
          $nexus = \App\Models\Building::where('name', 'like', '%Nexus%')->first();
          $tenantRentedBuildings = $nexus ? collect([$nexus]) : \App\Models\Building::take(1)->get();
      } else {
          $tenantRentedBuildings = \App\Models\Building::orderBy('name')->get();
      }
  }
@endphp

<!-- MODAL 5: ĐĂNG KÝ THI CÔNG NGOÀI GIỜ (ROLE 2: TENANT -> ROLE 4: BQL DUYỆT) -->
<div id="modal-tenant-after-hours" class="mo-modal" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,0.65);backdrop-filter:blur(4px);z-index:9999;align-items:center;justify-content:center;padding:16px;">
  <div style="background:#fff;border-radius:16px;width:100%;max-width:580px;box-shadow:0 25px 60px -15px rgba(15,23,42,0.3);overflow:hidden;border:1px solid #cbd5e1;max-height:92vh;display:flex;flex-direction:column;">
    <!-- Modal Header -->
    <div style="background:linear-gradient(135deg,#0f172a 0%,#1e293b 60%,#b45309 100%);color:#fff;padding:18px 22px;display:flex;justify-content:space-between;align-items:center;flex-shrink:0;">
      <div style="display:flex;align-items:center;gap:12px;">
        <div style="width:38px;height:38px;border-radius:10px;background:rgba(245,158,11,0.2);border:1px solid rgba(245,158,11,0.4);display:flex;align-items:center;justify-content:center;color:#fbbf24;font-size:16px;">
          <i class="fa-solid fa-clock"></i>
        </div>
        <div>
          <h3 style="margin:0;font-size:16px;font-weight:800;letter-spacing:-0.2px;">Đăng Ký Giấy Phép Thi Công Ngoài Giờ</h3>
          <span style="font-size:12px;opacity:0.85;display:block;margin-top:2px;">BQL thẩm định an toàn PCCC & phê duyệt trong vòng 2h làm việc</span>
        </div>
      </div>
      <button type="button" onclick="closeModal('modal-tenant-after-hours')" style="background:rgba(255,255,255,0.1);border:none;color:#fff;width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:18px;cursor:pointer;transition:all 0.15s;" onmouseover="this.style.background='rgba(255,255,255,0.2)'" onmouseout="this.style.background='rgba(255,255,255,0.1)'">&times;</button>
    </div>

    <!-- Modal Form -->
    <form id="form-tenant-after-hours" novalidate onsubmit="handleSubmitAfterHours(event)" style="padding:22px;overflow-y:auto;flex:1;">
      <!-- Tòa nhà áp dụng (Chỉ hiển thị tòa nhà mà Tenant đang có hợp đồng thuê) -->
      <div style="margin-bottom:14px;">
        <label style="font-size:12px;font-weight:700;color:#334155;display:flex;align-items:center;gap:6px;margin-bottom:5px;">
          <i class="fa-solid fa-building" style="color:#2563eb;"></i> TÒA NHÀ VĂN PHÒNG ÁP DỤNG <span style="color:#ef4444;">*</span>
        </label>
        <select id="ah-building-id" class="filament-input" style="width:100%;font-weight:600;" onchange="clearSingleFieldError('ah-building-id')">
          @foreach($tenantRentedBuildings as $bld)
            <option value="{{ $bld->id }}" selected>{{ $bld->name }} ({{ $bld->district ?? 'TP.HCM' }})</option>
          @endforeach
        </select>
        <div id="err-ah-building-id" class="field-error-msg" style="display:none;"></div>
      </div>

      <!-- Nội dung / Hạng mục thi công -->
      <div style="margin-bottom:14px;">
        <label style="font-size:12px;font-weight:700;color:#334155;display:flex;align-items:center;gap:6px;margin-bottom:5px;">
          <i class="fa-solid fa-screwdriver-wrench" style="color:#d97706;"></i> NỘI DUNG / HẠNG MỤC THI CÔNG <span style="color:#ef4444;">*</span>
        </label>
        <input type="text" id="ah-title" class="filament-input" placeholder="Ví dụ: Lắp đặt vách kính cách âm phòng họp & đi dây mạng" oninput="clearSingleFieldError('ah-title')" style="width:100%;">
        <div id="err-ah-title" class="field-error-msg" style="display:none;"></div>
      </div>

      <!-- Ngày thi công & Khung giờ -->
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:14px;">
        <div>
          <label style="font-size:12px;font-weight:700;color:#334155;display:flex;align-items:center;gap:6px;margin-bottom:5px;">
            <i class="fa-solid fa-calendar-days" style="color:#0284c7;"></i> NGÀY THI CÔNG <span style="color:#ef4444;">*</span>
          </label>
          <input type="date" id="ah-date" class="filament-input" onchange="clearSingleFieldError('ah-date')" style="width:100%;">
          <div id="err-ah-date" class="field-error-msg" style="display:none;"></div>
        </div>
        <div>
          <label style="font-size:12px;font-weight:700;color:#334155;display:flex;align-items:center;gap:6px;margin-bottom:5px;">
            <i class="fa-solid fa-business-time" style="color:#7c3aed;"></i> KHUNG GIỜ THI CÔNG <span style="color:#ef4444;">*</span>
          </label>
          <select id="ah-time" class="filament-input" onchange="clearSingleFieldError('ah-time')" style="width:100%;">
            <option value="">-- Chọn khung giờ đăng ký --</option>
            <option value="18:00 - 22:00">18:00 - 22:00 (Buổi tối ngày thường)</option>
            <option value="22:00 - 06:00">22:00 - 06:00 (Xuyên đêm - Giám sát PCCC)</option>
            <option value="08:00 - 18:00 T7/CN">08:00 - 18:00 (Thứ 7 & Chủ Nhật)</option>
            <option value="18:00 - 06:00 T7/CN">18:00 - 06:00 (Xuyên đêm cuối tuần)</option>
          </select>
          <div id="err-ah-time" class="field-error-msg" style="display:none;"></div>
        </div>
      </div>

      <!-- Số lượng công nhân & Đơn vị thi công -->
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:14px;">
        <div>
          <label style="font-size:12px;font-weight:700;color:#334155;display:flex;align-items:center;gap:6px;margin-bottom:5px;">
            <i class="fa-solid fa-users" style="color:#059669;"></i> SỐ LƯỢNG CÔNG NHÂN <span style="color:#ef4444;">*</span>
          </label>
          <input type="number" id="ah-workers" class="filament-input" min="1" max="50" placeholder="Số người vào tòa nhà" oninput="clearSingleFieldError('ah-workers')" style="width:100%;">
          <div id="err-ah-workers" class="field-error-msg" style="display:none;"></div>
        </div>
        <div>
          <label style="font-size:12px;font-weight:700;color:#334155;display:flex;align-items:center;gap:6px;margin-bottom:5px;">
            <i class="fa-solid fa-helmet-safety" style="color:#ea580c;"></i> ĐƠN VỊ THI CÔNG (NHÀ THẦU) <span style="color:#ef4444;">*</span>
          </label>
          <input type="text" id="ah-contractor" class="filament-input" placeholder="Ví dụ: Công ty Nội Thất An Cường" oninput="clearSingleFieldError('ah-contractor')" style="width:100%;">
          <div id="err-ah-contractor" class="field-error-msg" style="display:none;"></div>
        </div>
      </div>

      <!-- Danh sách CCCD công nhân -->
      <div style="margin-bottom:14px;">
        <label style="font-size:12px;font-weight:700;color:#334155;display:flex;align-items:center;gap:6px;margin-bottom:5px;">
          <i class="fa-solid fa-address-card" style="color:#475569;"></i> DANH SÁCH CCCD CÔNG NHÂN VÀO TÒA NHÀ <span style="color:#ef4444;">*</span>
        </label>
        <textarea id="ah-worker-list" class="filament-input" rows="3" placeholder="1. Nguyễn Văn Hùng - CCCD: 079089001234 - SĐT: 0908123456&#10;2. Trần Văn Nam - CCCD: 079090005678 - SĐT: 0912345678" oninput="clearSingleFieldError('ah-worker-list')" style="width:100%;"></textarea>
        <div id="err-ah-worker-list" class="field-error-msg" style="display:none;"></div>
      </div>

      <!-- Hộp cam kết PCCC & Vệ sinh môi trường -->
      <div style="background:#fffbeb;border:1.5px solid #fde68a;border-radius:10px;padding:12px 14px;margin-bottom:18px;">
        <label style="display:flex;align-items:flex-start;gap:10px;cursor:pointer;margin:0;">
          <input type="checkbox" id="ah-agreement" style="width:18px;height:18px;margin-top:2px;accent-color:#d97706;cursor:pointer;flex-shrink:0;" onchange="clearSingleFieldError('ah-agreement')">
          <span style="font-size:12px;color:#92400e;line-height:1.5;">
            <strong style="color:#b45309;"><i class="fa-solid fa-shield-halved"></i> Cam kết PCCC & An ninh tòa nhà:</strong>
            Doanh nghiệp và nhà thầu cam kết tuân thủ nghiêm ngặt quy chuẩn PCCC, không sử dụng hàn xì khi chưa có bình chữa cháy dự phòng, không gây tiếng ồn quá mức và dọn dẹp vệ sinh trước 06:00 sáng. <span style="color:#ef4444;font-weight:700;">*</span>
          </span>
        </label>
        <div id="err-ah-agreement" class="field-error-msg" style="display:none;padding-left:28px;"></div>
      </div>

      <!-- Footer Buttons -->
      <div style="display:flex;justify-content:flex-end;gap:10px;border-top:1px solid #f1f5f9;padding-top:16px;">
        <button type="button" class="btn-mo-outline" onclick="closeModal('modal-tenant-after-hours')" style="font-weight:600;padding:9px 18px;border-radius:8px;">Hủy bỏ</button>
        <button type="submit" id="btn-submit-after-hours" class="btn-mo-primary" style="background:linear-gradient(135deg,#d97706,#b45309);font-weight:700;padding:9px 22px;border-radius:8px;box-shadow:0 4px 12px rgba(217,119,6,0.3);display:inline-flex;align-items:center;gap:8px;">
          <i class="fa-solid fa-paper-plane"></i> Gửi BQL Phê Duyệt
        </button>
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
