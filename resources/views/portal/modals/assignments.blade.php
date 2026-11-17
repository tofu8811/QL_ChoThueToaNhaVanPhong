<!-- ==========================================================================
     MODULE 5: BUILDING ASSIGNMENT MODALS (ENDPOINTS 92 - 98)
     ========================================================================== -->

<!-- MODAL CHÍNH: PHÂN CÔNG NHÂN SỰ PHỤ TRÁCH TÒA NHÀ (API 92 - 98) -->
<div id="modal-building-assignments" class="mo-modal" style="display:none; align-items:center; justify-content:center; position:fixed; inset:0; background:rgba(15,23,42,0.65); z-index:9999; backdrop-filter:blur(4px); padding:20px;">
  <div class="mo-modal-content" style="max-width: 980px; width: 100%; max-height: 90vh; overflow-y: auto; background: #fff; border-radius: 14px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.35); border: 1px solid #cbd5e1;">
    <!-- Header Modal -->
    <div style="background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 100%); color: #fff; padding: 20px 24px; border-radius: 14px 14px 0 0; display:flex; justify-content:space-between; align-items:center;">
      <div>
        <div style="display:flex; align-items:center; gap:10px;">
          <span style="background:rgba(59,130,246,0.25); border:1px solid rgba(147,197,253,0.3); padding:3px 9px; border-radius:6px; font-size:11px; font-weight:700; color:#93c5fd; letter-spacing:0.5px;">MOD-05 • RBAC TÒA NHÀ</span>
          <h3 style="margin:0; font-size:18px; font-weight:800; color:#fff; display:flex; align-items:center; gap:8px;">
            <i class="fa-solid fa-users-gear" style="color:#60a5fa;"></i> Phân Công Đội Ngũ Nhân Sự Phụ Trách
          </h3>
        </div>
        <p style="margin:6px 0 0 0; font-size:13px; color:#cbd5e1;">
          Tòa nhà: <strong id="mba-bld-name" style="color:#60a5fa; font-size:14px;">---</strong> • Mã cao ốc: <span id="mba-bld-code" style="font-family:monospace; color:#fde047; font-weight:700;">---</span>
        </p>
      </div>
      <button type="button" onclick="closeModal('modal-building-assignments')" style="background:rgba(255,255,255,0.1); border:none; color:#fff; font-size:24px; cursor:pointer; width:36px; height:36px; border-radius:8px; display:flex; align-items:center; justify-content:center; transition:background 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.2)'" onmouseout="this.style.background='rgba(255,255,255,0.1)'">&times;</button>
    </div>

    <!-- Body Modal -->
    <div style="padding: 24px;">
      <!-- Tab Navigation bên trong Modal -->
      <div style="display:flex; gap:8px; border-bottom:2px solid #e2e8f0; margin-bottom:20px; padding-bottom:2px;">
        <button type="button" id="mba-tab-btn-sales" class="mba-tab-btn active" onclick="switchMbaTab('sales')" style="padding:10px 18px; font-size:13.5px; font-weight:700; border:none; background:none; cursor:pointer; border-radius:8px 8px 0 0; border-bottom:3px solid #2563eb; color:#2563eb; display:flex; align-items:center; gap:8px;">
          <i class="fa-solid fa-user-tie"></i> 1. Sales Nhận Lead
          <span id="mba-badge-sales" style="background:#eff6ff; color:#2563eb; border:1px solid #bfdbfe; font-size:11px; padding:2px 7px; border-radius:10px;">0</span>
        </button>
        <button type="button" id="mba-tab-btn-pm" class="mba-tab-btn" onclick="switchMbaTab('pm')" style="padding:10px 18px; font-size:13.5px; font-weight:700; border:none; background:none; cursor:pointer; border-radius:8px 8px 0 0; border-bottom:3px solid transparent; color:#64748b; display:flex; align-items:center; gap:8px;">
          <i class="fa-solid fa-helmet-safety"></i> 2. Ban Quản Lý (PM)
          <span id="mba-badge-pm" style="background:#f1f5f9; color:#64748b; border:1px solid #e2e8f0; font-size:11px; padding:2px 7px; border-radius:10px;">0</span>
        </button>
        <button type="button" id="mba-tab-btn-acc" class="mba-tab-btn" onclick="switchMbaTab('acc')" style="padding:10px 18px; font-size:13.5px; font-weight:700; border:none; background:none; cursor:pointer; border-radius:8px 8px 0 0; border-bottom:3px solid transparent; color:#64748b; display:flex; align-items:center; gap:8px;">
          <i class="fa-solid fa-calculator"></i> 3. Kế Toán Công Nợ
          <span id="mba-badge-acc" style="background:#f1f5f9; color:#64748b; border:1px solid #e2e8f0; font-size:11px; padding:2px 7px; border-radius:10px;">0</span>
        </button>
      </div>

      <!-- TAB PANEL 1: SALES NHẬN LEAD (API 92, 93, 94) -->
      <div id="mba-panel-sales" class="mba-panel">
        <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; padding:16px; margin-bottom:20px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:14px;">
          <div>
            <h4 style="margin:0 0 4px 0; font-size:14.5px; font-weight:700; color:#0f172a;">Gán Thêm Nhân Viên Sales Vào Tòa Nhà (API 93)</h4>
            <p style="margin:0; font-size:12px; color:#64748b;">Sales được gán sẽ có quyền xem tòa nhà này và tự động nhận Lead khách có nhu cầu thuê.</p>
          </div>
          <button type="button" class="btn-mo-primary" style="background:#2563eb; padding:8px 16px; font-size:13px; font-weight:700;" onclick="openAssignSalesModal()">
            <i class="fa-solid fa-user-plus"></i> + Thêm Nhân Viên Sales
          </button>
        </div>

        <div class="table-responsive" style="border:1px solid #e2e8f0; border-radius:8px; overflow:hidden;">
          <table class="mo-table" style="margin:0;">
            <thead>
              <tr style="background:#f1f5f9;">
                <th style="padding:10px 14px;">Mã Sales</th>
                <th style="padding:10px 14px;">Họ Và Tên</th>
                <th style="padding:10px 14px;">Email / Điện Thoại</th>
                <th style="padding:10px 14px;">Hạn Mức Lead</th>
                <th style="padding:10px 14px;">Trạng Thái Nhận Lead</th>
                <th style="padding:10px 14px;">Thời Điểm Gán</th>
                <th style="text-align:right; padding:10px 14px;">Thao Tác (API 94)</th>
              </tr>
            </thead>
            <tbody id="mba-tbody-sales">
              <tr><td colspan="7" style="text-align:center; padding:24px; color:#94a3b8;"><i class="fa-solid fa-spinner fa-spin"></i> Đang nạp danh sách Sales phụ trách...</td></tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- TAB PANEL 2: BAN QUẢN LÝ (PM) (API 92, 95, 96) -->
      <div id="mba-panel-pm" class="mba-panel" style="display:none;">
        <div style="background:#fffbeb; border:1px solid #fde68a; border-radius:10px; padding:16px; margin-bottom:20px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:14px;">
          <div>
            <h4 style="margin:0 0 4px 0; font-size:14.5px; font-weight:700; color:#92400e;">Gán Ban Quản Lý (PM) Vào Tòa Nhà (API 95)</h4>
            <p style="margin:0; font-size:12px; color:#b45309;">Nhân viên BQL phụ trách trực tiếp xử lý sự cố kỹ thuật, phê duyệt thi công và nghiệm thu bảo trì.</p>
          </div>
          <button type="button" class="btn-mo-orange" style="background:#d97706; border-color:#d97706; padding:8px 16px; font-size:13px; font-weight:700;" onclick="openAssignPmModal()">
            <i class="fa-solid fa-helmet-safety"></i> + Thêm Nhân Sự BQL
          </button>
        </div>

        <div class="table-responsive" style="border:1px solid #e2e8f0; border-radius:8px; overflow:hidden;">
          <table class="mo-table" style="margin:0;">
            <thead>
              <tr style="background:#f1f5f9;">
                <th style="padding:10px 14px;">Mã PM</th>
                <th style="padding:10px 14px;">Họ Và Tên</th>
                <th style="padding:10px 14px;">Hotline Khẩn Cấp</th>
                <th style="padding:10px 14px;">Bộ Phận</th>
                <th style="padding:10px 14px;">Vai Trò Phụ Trách</th>
                <th style="padding:10px 14px;">Thời Điểm Gán</th>
                <th style="text-align:right; padding:10px 14px;">Thao Tác (API 96)</th>
              </tr>
            </thead>
            <tbody id="mba-tbody-pm">
              <tr><td colspan="7" style="text-align:center; padding:24px; color:#94a3b8;"><i class="fa-solid fa-spinner fa-spin"></i> Đang nạp danh sách BQL phụ trách...</td></tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- TAB PANEL 3: KẾ TOÁN CÔNG NỢ (API 92, 97, 98) -->
      <div id="mba-panel-acc" class="mba-panel" style="display:none;">
        <div style="background:#ecfdf5; border:1px solid #a7f3d0; border-radius:10px; padding:16px; margin-bottom:20px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:14px;">
          <div>
            <h4 style="margin:0 0 4px 0; font-size:14.5px; font-weight:700; color:#065f46;">Gán Kế Toán Theo Dõi Công Nợ (API 97)</h4>
            <p style="margin:0; font-size:12px; color:#047857;">Kế toán phụ trách lập phiếu thu (Debit Note), gửi thông báo thanh toán và đối soát tiền thuê hàng tháng.</p>
          </div>
          <button type="button" class="btn-mo-primary" style="background:#059669; border-color:#059669; padding:8px 16px; font-size:13px; font-weight:700;" onclick="openAssignAccountantModal()">
            <i class="fa-solid fa-calculator"></i> + Thêm Kế Toán Phụ Trách
          </button>
        </div>

        <div class="table-responsive" style="border:1px solid #e2e8f0; border-radius:8px; overflow:hidden;">
          <table class="mo-table" style="margin:0;">
            <thead>
              <tr style="background:#f1f5f9;">
                <th style="padding:10px 14px;">Mã Kế Toán</th>
                <th style="padding:10px 14px;">Họ Và Tên</th>
                <th style="padding:10px 14px;">Email / Điện Thoại</th>
                <th style="padding:10px 14px;">Quyền Duyệt Điều Chỉnh</th>
                <th style="padding:10px 14px;">Trạng Thái</th>
                <th style="padding:10px 14px;">Thời Điểm Gán</th>
                <th style="text-align:right; padding:10px 14px;">Thao Tác (API 98)</th>
              </tr>
            </thead>
            <tbody id="mba-tbody-acc">
              <tr><td colspan="7" style="text-align:center; padding:24px; color:#94a3b8;"><i class="fa-solid fa-spinner fa-spin"></i> Đang nạp danh sách Kế toán phụ trách...</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Footer Modal -->
    <div style="background:#f8fafc; border-top:1px solid #e2e8f0; padding:14px 24px; display:flex; justify-content:space-between; align-items:center; border-radius:0 0 14px 14px;">
      <span style="font-size:12px; color:#64748b;">
        <i class="fa-solid fa-shield-halved" style="color:#2563eb;"></i> Phân quyền RBAC trực tiếp vào các bảng pivot: <code>sale_buildings</code>, <code>manager_buildings</code>, <code>account_buildings</code>.
      </span>
      <button type="button" class="btn-mo-outline" style="background:#fff; padding:7px 18px; font-size:13px;" onclick="closeModal('modal-building-assignments')">
        Đóng Cửa Sổ
      </button>
    </div>
  </div>
</div>

<!-- ==========================================================================
     SUB-MODALS GÁN TỪNG NHÂN SỰ
     ========================================================================== -->

    <!-- MODAL GÁN SALES VÀO TÒA NHÀ (API 93) -->
    <div id="modal-assign-sales" class="mo-modal">
      <div class="mo-modal-content" style="max-width: 540px; border-radius: 12px; overflow: hidden;">
        <div class="mo-modal-header" style="background: linear-gradient(135deg, #1e3a8a, #2563eb); color: #fff; padding: 18px 24px;">
          <div>
            <h3 style="margin:0; font-size: 17px; font-weight: 700; display:flex; align-items:center; gap:8px;">
              <i class="fa-solid fa-user-plus"></i> Phân Công Nhân Viên Sales Vào Tòa Nhà
            </h3>
            <span style="font-size: 12px; opacity: 0.85;" id="assign-sales-modal-building-name">Tòa nhà: ---</span>
          </div>
          <button class="mo-modal-close" style="color: #fff;" onclick="closeModal('modal-assign-sales')">&times;</button>
        </div>
        <div class="mo-modal-body" style="padding: 22px;">
          <form id="form-assign-sales" onsubmit="handleAssignSalesSubmit(event)">
            <input type="hidden" id="assign-sales-building-id" value="">

            <div style="margin-bottom: 16px;">
              <label style="display:block; font-size:13px; font-weight:600; margin-bottom:6px;">
                Chọn nhân viên Sales để phân công <span style="color:#ef4444;">*</span>:
              </label>
              <div id="assign-sales-checkboxes-container" class="building-select-grid" style="max-height: 220px;">
                <span style="color:#94a3b8; font-size:12px;"><i class="fa-solid fa-spinner fa-spin"></i> Đang tải danh sách Sales...</span>
              </div>
            </div>

            <div style="margin-bottom: 20px; background:#f0f9ff; padding:12px; border-radius:8px; border:1px solid #bae6fd;">
              <label style="display:flex; align-items:center; gap:8px; font-size:13px; font-weight:600; cursor:pointer; color:#0369a1;">
                <input type="checkbox" id="assign-sales-is-active" checked style="accent-color:#0284c7; width:16px; height:16px;">
                <span>Kích hoạt nhận Lead ngay tại tòa nhà này (is_active)</span>
              </label>
            </div>

            <div style="display:flex; justify-content:flex-end; gap:10px;">
              <button type="button" class="btn-mo-outline" onclick="closeModal('modal-assign-sales')">Hủy bỏ</button>
              <button type="submit" id="btn-submit-assign-sales" class="btn-mo-orange">
                <i class="fa-solid fa-check"></i> Xác Nhận Phân Công Sales
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- MODAL GÁN BAN QUẢN LÝ (PM) VÀO TÒA NHÀ (API 95) -->
    <div id="modal-assign-pm" class="mo-modal">
      <div class="mo-modal-content" style="max-width: 500px; border-radius: 12px; overflow: hidden;">
        <div class="mo-modal-header" style="background: linear-gradient(135deg, #b45309, #d97706); color: #fff; padding: 18px 24px;">
          <div>
            <h3 style="margin:0; font-size: 17px; font-weight: 700; display:flex; align-items:center; gap:8px;">
              <i class="fa-solid fa-helmet-safety"></i> Gán Ban Quản Lý (PM) Vào Tòa Nhà
            </h3>
            <span style="font-size: 12px; opacity: 0.85;" id="assign-pm-modal-building-name">Tòa nhà: ---</span>
          </div>
          <button class="mo-modal-close" style="color: #fff;" onclick="closeModal('modal-assign-pm')">&times;</button>
        </div>
        <div class="mo-modal-body" style="padding: 22px;">
          <form id="form-assign-pm" onsubmit="handleAssignPmSubmit(event)">
            <input type="hidden" id="assign-pm-building-id" value="">

            <div style="margin-bottom: 16px;">
              <label style="display:block; font-size:13px; font-weight:600; margin-bottom:6px;">
                Chọn nhân sự Ban Quản Lý <span style="color:#ef4444;">*</span>:
              </label>
              <select id="assign-pm-select" required class="form-control" style="width:100%; height:42px; border-radius:6px; border:1px solid #cbd5e1; padding:0 12px; font-size:13.5px;">
                <option value="">-- Đang tải danh sách PM... --</option>
              </select>
            </div>

            <div style="margin-bottom: 20px; background:#fffbeb; padding:12px; border-radius:8px; border:1px solid #fde68a;">
              <label style="display:flex; align-items:center; gap:8px; font-size:13px; font-weight:600; cursor:pointer; color:#92400e;">
                <input type="checkbox" id="assign-pm-is-head" style="accent-color:#d97706; width:16px; height:16px;">
                <span>Bổ nhiệm làm Trưởng Ban Quản Lý tòa nhà (Head Manager / is_primary)</span>
              </label>
            </div>

            <div style="display:flex; justify-content:flex-end; gap:10px;">
              <button type="button" class="btn-mo-outline" onclick="closeModal('modal-assign-pm')">Hủy bỏ</button>
              <button type="submit" id="btn-submit-assign-pm" class="btn-mo-orange" style="background:#d97706; border-color:#d97706;">
                <i class="fa-solid fa-check"></i> Xác Nhận Gán BQL
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- MODAL GÁN KẾ TOÁN VÀO TÒA NHÀ (API 97) -->
    <div id="modal-assign-accountant" class="mo-modal">
      <div class="mo-modal-content" style="max-width: 540px; border-radius: 12px; overflow: hidden;">
        <div class="mo-modal-header" style="background: linear-gradient(135deg, #065f46, #059669); color: #fff; padding: 18px 24px;">
          <div>
            <h3 style="margin:0; font-size: 17px; font-weight: 700; display:flex; align-items:center; gap:8px;">
              <i class="fa-solid fa-calculator"></i> Gán Kế Toán Phụ Trách Tòa Nhà
            </h3>
            <span style="font-size: 12px; opacity: 0.85;" id="assign-acc-modal-building-name">Tòa nhà: ---</span>
          </div>
          <button class="mo-modal-close" style="color: #fff;" onclick="closeModal('modal-assign-accountant')">&times;</button>
        </div>
        <div class="mo-modal-body" style="padding: 22px;">
          <form id="form-assign-accountant" onsubmit="handleAssignAccountantSubmit(event)">
            <input type="hidden" id="assign-acc-building-id" value="">

            <div style="margin-bottom: 20px;">
              <label style="display:block; font-size:13px; font-weight:600; margin-bottom:6px;">
                Chọn nhân viên Kế toán phụ trách theo dõi công nợ <span style="color:#ef4444;">*</span>:
              </label>
              <div id="assign-acc-checkboxes-container" class="building-select-grid" style="max-height: 220px;">
                <span style="color:#94a3b8; font-size:12px;"><i class="fa-solid fa-spinner fa-spin"></i> Đang tải danh sách Kế toán...</span>
              </div>
            </div>

            <div style="display:flex; justify-content:flex-end; gap:10px;">
              <button type="button" class="btn-mo-outline" onclick="closeModal('modal-assign-accountant')">Hủy bỏ</button>
              <button type="submit" id="btn-submit-assign-acc" class="btn-mo-orange" style="background:#059669; border-color:#059669;">
                <i class="fa-solid fa-check"></i> Xác Nhận Gán Kế Toán
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
