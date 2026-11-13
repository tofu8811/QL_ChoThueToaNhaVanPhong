<!-- ==========================================================================
         MODULE 5: BUILDING ASSIGNMENT MODALS (ENDPOINTS 93, 95, 97)
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
