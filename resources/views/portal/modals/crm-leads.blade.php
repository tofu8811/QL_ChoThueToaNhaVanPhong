<!-- MODAL 1: TẠO MỚI LEAD THỦ CÔNG (POST /api/v1/crm/leads) -->
<div id="modal-create-lead" class="mo-modal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(15,23,42,0.6);backdrop-filter:blur(3px);z-index:9999;align-items:center;justify-content:center;">
  <div style="background:#ffffff;border-radius:12px;width:100%;max-width:560px;box-shadow:0 20px 25px -5px rgba(0,0,0,0.1);overflow:hidden;border:1px solid #e2e8f0;">
    <div style="background:var(--mo-primary);color:#ffffff;padding:16px 20px;display:flex;justify-content:space-between;align-items:center;">
      <h3 style="margin:0;font-size:16px;font-weight:700;"><i class="fa-solid fa-user-plus"></i> Tiếp Nhận Khách Hàng Tiềm Năng Mới</h3>
      <button type="button" onclick="closeCreateLeadModal()" style="background:none;border:none;color:#ffffff;font-size:18px;cursor:pointer;">&times;</button>
    </div>
    <form id="form-create-lead" onsubmit="submitCreateLead(event)" style="padding:20px;">
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px;">
        <div class="form-field">
          <label style="font-size:12px;font-weight:600;color:#334155;">Họ và Tên <span style="color:#ef4444;">*</span></label>
          <input type="text" id="lead-contact-name" class="filament-input-sm" required placeholder="Nguyễn Văn A">
        </div>
        <div class="form-field">
          <label style="font-size:12px;font-weight:600;color:#334155;">Số Điện Thoại <span style="color:#ef4444;">*</span></label>
          <input type="tel" id="lead-contact-phone" class="filament-input-sm" required placeholder="0901234567">
        </div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px;">
        <div class="form-field">
          <label style="font-size:12px;font-weight:600;color:#334155;">Email</label>
          <input type="email" id="lead-contact-email" class="filament-input-sm" placeholder="khachhang@company.vn">
        </div>
        <div class="form-field">
          <label style="font-size:12px;font-weight:600;color:#334155;">Tên Doanh Nghiệp / Công Ty</label>
          <input type="text" id="lead-company-name" class="filament-input-sm" placeholder="Công ty TNHH Giải Pháp AI">
        </div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px;">
        <div class="form-field">
          <label style="font-size:12px;font-weight:600;color:#334155;">Tòa Nhà Quan Tâm</label>
          <select id="create-lead-building" class="filament-input-sm">
            <!-- Rendered via JS -->
          </select>
        </div>
        <div class="form-field">
          <label style="font-size:12px;font-weight:600;color:#334155;">Nguồn Tiếp Cận</label>
          <select id="lead-source" class="filament-input-sm">
            <option value="DIRECT_CALL">Hotline / Gọi Trực Tiếp</option>
            <option value="WEBSITE">Form Đăng Ký Website</option>
            <option value="REFERRAL">Người Quen Giới Thiệu</option>
            <option value="WALK_IN">Khách Vãng Lai Trực Tiếp</option>
          </select>
        </div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px;">
        <div class="form-field">
          <label style="font-size:12px;font-weight:600;color:#334155;">Diện Tích Cần Thuê (m²)</label>
          <input type="number" step="0.1" id="lead-expected-area" class="filament-input-sm" placeholder="VD: 150">
        </div>
        <div class="form-field">
          <label style="font-size:12px;font-weight:600;color:#334155;">Ngân Sách Tối Đa ($/tháng)</label>
          <input type="number" step="1" id="lead-expected-budget" class="filament-input-sm" placeholder="VD: 5000">
        </div>
      </div>
      <div class="form-field" style="margin-bottom:16px;">
        <label style="font-size:12px;font-weight:600;color:#334155;">Ghi Chú Nhu Cầu Cụ Thể</label>
        <textarea id="lead-notes" rows="2" class="filament-input-sm" placeholder="Yêu cầu hướng đẹp, bàn giao thô hoặc hoàn thiện..."></textarea>
      </div>
      <div style="display:flex;justify-content:flex-end;gap:8px;border-top:1px solid #f1f5f9;padding-top:14px;">
        <button type="button" class="btn-mo-outline" onclick="closeCreateLeadModal()">Hủy Bỏ</button>
        <button type="submit" class="btn-mo-orange"><i class="fa-solid fa-check"></i> Lưu Hồ Sơ Lead</button>
      </div>
    </form>
  </div>
</div>

<!-- MODAL 2: CHI TIẾT LEAD & TIMELINE CHĂM SÓC (GET /api/v1/crm/leads/{id}) -->
<div id="modal-lead-detail" class="mo-modal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(15,23,42,0.6);backdrop-filter:blur(3px);z-index:9999;align-items:center;justify-content:center;">
  <div style="background:#ffffff;border-radius:12px;width:100%;max-width:850px;box-shadow:0 20px 25px -5px rgba(0,0,0,0.1);overflow:hidden;border:1px solid #e2e8f0;max-height:90vh;display:flex;flex-direction:column;">
    <div style="background:var(--mo-primary);color:#ffffff;padding:16px 20px;display:flex;justify-content:space-between;align-items:center;">
      <h3 style="margin:0;font-size:16px;font-weight:700;"><i class="fa-solid fa-address-card"></i> Hồ Sơ Chi Tiết Khách Hàng Tiềm Năng & Timeline</h3>
      <button type="button" onclick="closeLeadDetailModal()" style="background:none;border:none;color:#ffffff;font-size:18px;cursor:pointer;">&times;</button>
    </div>
    <div id="lead-detail-content" style="padding:20px;overflow-y:auto;flex:1;">
      <!-- Rendered via JS -->
    </div>
  </div>
</div>

<!-- MODAL 3: PHÂN CÔNG SALES (POST /api/v1/crm/leads/{id}/assign) -->
<div id="modal-assign-lead-sales" class="mo-modal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(15,23,42,0.6);backdrop-filter:blur(3px);z-index:9999;align-items:center;justify-content:center;">
  <div style="background:#ffffff;border-radius:12px;width:100%;max-width:440px;box-shadow:0 20px 25px -5px rgba(0,0,0,0.1);overflow:hidden;border:1px solid #e2e8f0;">
    <div style="background:var(--mo-primary);color:#ffffff;padding:14px 18px;display:flex;justify-content:space-between;align-items:center;">
      <h3 style="margin:0;font-size:15px;font-weight:700;"><i class="fa-solid fa-user-gear"></i> Phân Công Sales Phụ Trách</h3>
      <button type="button" onclick="closeAssignSalesModal()" style="background:none;border:none;color:#ffffff;font-size:18px;cursor:pointer;">&times;</button>
    </div>
    <div style="padding:18px;">
      <p style="font-size:13px;color:#475569;margin-bottom:12px;">
        Điều phối khách hàng <strong id="assign-lead-contact-name" style="color:#0f172a;"></strong> cho nhân viên Sales:
      </p>
      <div class="form-field" style="margin-bottom:16px;">
        <label style="font-size:12px;font-weight:600;color:#334155;">Nhân Viên Sales</label>
        <select id="assign-sales-select" class="filament-input-sm" style="width:100%;">
          <option value="">Đang tải danh sách Sales...</option>
        </select>
      </div>
      <div style="display:flex;justify-content:flex-end;gap:8px;">
        <button type="button" class="btn-mo-outline" onclick="closeAssignSalesModal()">Đóng</button>
        <button type="button" class="btn-mo-primary" onclick="submitAssignLeadSales()"><i class="fa-solid fa-check"></i> Xác Nhận Phân Công</button>
      </div>
    </div>
  </div>
</div>

<!-- MODAL 4: CHUYỂN ĐỔI LEAD THÀNH KHÁCH THUÊ (POST /api/v1/crm/leads/{id}/convert-to-tenant) -->
<div id="modal-convert-lead" class="mo-modal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(15,23,42,0.6);backdrop-filter:blur(3px);z-index:9999;align-items:center;justify-content:center;">
  <div style="background:#ffffff;border-radius:12px;width:100%;max-width:480px;box-shadow:0 20px 25px -5px rgba(0,0,0,0.1);overflow:hidden;border:1px solid #e2e8f0;">
    <div style="background:#16a34a;color:#ffffff;padding:14px 18px;display:flex;justify-content:space-between;align-items:center;">
      <h3 style="margin:0;font-size:15px;font-weight:700;"><i class="fa-solid fa-award"></i> Chốt Hợp Đồng - Chuyển Đổi Sang Khách Thuê</h3>
      <button type="button" onclick="closeConvertToTenantModal()" style="background:none;border:none;color:#ffffff;font-size:18px;cursor:pointer;">&times;</button>
    </div>
    <div style="padding:18px;">
      <p style="font-size:13px;color:#475569;margin-bottom:14px;">
        Xác nhận chốt đàm phán thành công cho Lead <strong id="convert-lead-name" style="color:#0f172a;"></strong>. Hệ thống sẽ tự động tạo hồ sơ Khách Thuê Doanh Nghiệp (Tenant) mới để lập hợp đồng.
      </p>
      <div class="form-field" style="margin-bottom:12px;">
        <label style="font-size:12px;font-weight:600;color:#334155;">Tên Doanh Nghiệp / Công Ty <span style="color:#ef4444;">*</span></label>
        <input type="text" id="convert-company-name" class="filament-input-sm" required placeholder="Công ty Cổ phần Alpha">
      </div>
      <div class="form-field" style="margin-bottom:16px;">
        <label style="font-size:12px;font-weight:600;color:#334155;">Mã Số Thuế (MST)</label>
        <input type="text" id="convert-tax-code" class="filament-input-sm" placeholder="0312345678">
      </div>
      <div style="display:flex;justify-content:flex-end;gap:8px;">
        <button type="button" class="btn-mo-outline" onclick="closeConvertToTenantModal()">Hủy</button>
        <button type="button" class="btn-mo-primary" style="background:#16a34a;border-color:#16a34a;" onclick="submitConvertToTenant()">
          <i class="fa-solid fa-arrow-right-to-bracket"></i> Tạo Khách Thuê & Chốt Won
        </button>
      </div>
    </div>
  </div>
</div>
