<script>
    /* ==========================================================================
       MODULE 5: STAFF & RBAC JAVASCRIPT LOGIC (ENDPOINTS 72 - 91)
       ========================================================================== */
    let currentStaffTab = 'admins';
    let cachedStaffBuildings = [];
    let isStaffPreloaded = false;

    // Helper render HTML danh sách Admins
    function renderStaffAdminsHtml(admins) {
      if (!admins || admins.length === 0) {
        return '<tr><td colspan="7" style="text-align:center; padding: 25px; color:#94a3b8;">Không tìm thấy tài khoản Quản trị viên nào phù hợp.</td></tr>';
      }
      return admins.map(a => {
        const isSuper = !!a.is_super_admin;
        const statusClass = (a.status === 'ACTIVE') ? 'success' : ((a.status === 'LOCKED') ? 'danger' : 'warning');
        const statusText = (a.status === 'ACTIVE') ? 'Hoạt động' : ((a.status === 'LOCKED') ? 'Đã khóa' : 'Chờ kích hoạt');
        const createdDate = a.created_at ? new Date(a.created_at).toLocaleDateString('vi-VN') : '---';

        return `
          <tr>
            <td>
              <div style="display:flex; align-items:center; gap:9px;">
                <div style="width:32px; height:32px; border-radius:50%; background:${isSuper ? '#ef4444' : '#0f3361'}; color:#fff; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:12px;">
                  ${isSuper ? 'SA' : 'AD'}
                </div>
                <div>
                  <strong style="color:#0f172a; font-size:13.5px;">${escapeHtml(a.full_name || a.name || '---')}</strong>
                </div>
              </div>
            </td>
            <td><span style="font-family:monospace; color:#2563eb;">${escapeHtml(a.email)}</span></td>
            <td>${escapeHtml(a.phone || '---')}</td>
            <td>
              ${isSuper 
                ? '<span class="staff-badge-super"><i class="fa-solid fa-crown"></i> Super Admin</span>' 
                : '<span class="staff-badge-admin">Admin Thường</span>'}
            </td>
            <td><span class="badge-filament ${statusClass}">${statusText}</span></td>
            <td><span style="color:#64748b; font-size:12px;">${createdDate}</span></td>
            <td style="text-align: right; white-space: nowrap;">
              <button class="btn-mo-outline" style="padding:4px 8px; font-size:12px;" onclick="viewStaffAdmin('${a.id}')" title="Xem chi tiết">
                <i class="fa-regular fa-eye"></i>
              </button>
              <button class="btn-mo-outline" style="padding:4px 8px; font-size:12px;" onclick="openEditAdminModal('${a.id}')" title="Chỉnh sửa">
                <i class="fa-regular fa-pen-to-square"></i>
              </button>
              <button class="btn-mo-outline" style="padding:4px 8px; font-size:12px; color:#d97706; border-color:#fde68a;" onclick="resetStaffPassword('admin', '${a.id}', '${escapeHtml(a.full_name || a.name)}')" title="Cấp lại mật khẩu & gửi email">
                <i class="fa-solid fa-key"></i>
              </button>
              <button class="btn-mo-outline" style="padding:4px 8px; font-size:12px; color:#dc2626; border-color:#fca5a5;" onclick="deleteStaffAdmin('${a.id}', '${escapeHtml(a.full_name || a.name)}')" title="Xóa mềm">
                <i class="fa-regular fa-trash-can"></i>
              </button>
            </td>
          </tr>
        `;
      }).join('');
    }

    // Helper render HTML danh sách Sales
    function renderStaffSalesHtml(sales) {
      if (!sales || sales.length === 0) {
        return '<tr><td colspan="8" style="text-align:center; padding: 25px; color:#94a3b8;">Chưa có dữ liệu nhân viên Sales nào.</td></tr>';
      }
      return sales.map(s => {
        const buildings = s.sale_buildings || s.buildings || [];
        const bldHtml = buildings.length > 0
          ? `<div style="display:flex;flex-wrap:wrap;gap:5px;align-items:center;">` + buildings.slice(0, 2).map(b => `<span style="background:#e0f2fe; color:#0369a1; padding:3px 8px; border-radius:6px; font-size:11.5px; font-weight:600; white-space:nowrap;">${escapeHtml(b.name || 'Tòa nhà')}</span>`).join('') + (buildings.length > 2 ? `<span style="background:#f1f5f9; color:#475569; padding:2px 7px; border-radius:999px; font-size:11px; font-weight:700; white-space:nowrap;">+${buildings.length - 2} tòa</span>` : '') + `</div>`
          : '<span style="color:#94a3b8; font-size:11.5px;">Toàn hệ thống</span>';

        const statusClass = (s.status === 'ACTIVE') ? 'success' : 'danger';
        const statusText = (s.status === 'ACTIVE') ? 'Hoạt động' : 'Tạm khóa';

        return `
          <tr>
            <td style="white-space:nowrap;"><span style="font-family:monospace; font-weight:700; color:#0f3361; background:#f1f5f9; padding:3px 8px; border-radius:6px; font-size:12px;">${escapeHtml(s.employee_code || 'SL-' + s.id?.substring(0, 4))}</span></td>
            <td style="white-space:nowrap;"><strong>${escapeHtml(s.full_name || s.name || '---')}</strong></td>
            <td>
              <div style="font-size:12.5px; color:#2563eb;">${escapeHtml(s.email)}</div>
              <div style="font-size:11.5px; color:#64748b;">${escapeHtml(s.phone || '---')}</div>
            </td>
            <td>${bldHtml}</td>
            <td style="white-space:nowrap;">
              <span style="font-weight:700; color:#0f172a;">${s.active_leads_count || 1}</span> / <span style="color:#64748b;">${s.max_concurrent_leads || 20} leads</span>
            </td>
            <td style="white-space:nowrap;"><span style="color:#ea580c; font-weight:700;">${s.commission_rate ? s.commission_rate + '%' : '---'}</span></td>
            <td style="white-space:nowrap;"><span class="badge-filament ${statusClass}">${statusText}</span></td>
            <td style="text-align: right; white-space: nowrap;">
              <button class="btn-mo-outline" style="padding:4px 8px; font-size:12px;" onclick="viewStaffSale('${s.id}')" title="Xem chi tiết & KPI">
                <i class="fa-regular fa-eye"></i>
              </button>
              <button class="btn-mo-outline" style="padding:4px 8px; font-size:12px;" onclick="openEditSaleModal('${s.id}')" title="Chỉnh sửa">
                <i class="fa-regular fa-pen-to-square"></i>
              </button>
              <button class="btn-mo-outline" style="padding:4px 8px; font-size:12px; color:#d97706; border-color:#fde68a;" onclick="resetStaffPassword('sales', '${s.id}', '${escapeHtml(s.full_name || s.name)}')" title="Cấp lại mật khẩu & gửi email">
                <i class="fa-solid fa-key"></i>
              </button>
              <button class="btn-mo-outline" style="padding:4px 8px; font-size:12px; color:#dc2626; border-color:#fca5a5;" onclick="deleteStaffSale('${s.id}', '${escapeHtml(s.full_name || s.name)}')" title="Xóa mềm">
                <i class="fa-regular fa-trash-can"></i>
              </button>
            </td>
          </tr>
        `;
      }).join('');
    }

    // Helper render HTML danh sách PM
    function renderStaffPmHtml(pms) {
      if (!pms || pms.length === 0) {
        return '<tr><td colspan="7" style="text-align:center; padding: 25px; color:#94a3b8;">Chưa có dữ liệu nhân sự Ban Quản lý nào.</td></tr>';
      }
      const deptLabels = {
        TECHNICAL: 'Kỹ thuật & Cơ điện',
        SECURITY: 'An ninh & Bảo vệ',
        CLEANING: 'Vệ sinh & Cảnh quan',
        CUSTOMER_CARE: 'CSKH Tòa Nhà'
      };

      return pms.map(p => {
        const buildings = p.manager_buildings || p.buildings || [];
        const bldHtml = buildings.length > 0
          ? `<div style="display:flex;flex-wrap:wrap;gap:5px;align-items:center;">` + buildings.slice(0, 2).map(b => `<span style="background:#fef3c7; color:#92400e; padding:3px 8px; border-radius:6px; font-size:11.5px; font-weight:600; white-space:nowrap;">${escapeHtml(b.name || 'Tòa nhà')}</span>`).join('') + (buildings.length > 2 ? `<span style="background:#f1f5f9; color:#475569; padding:2px 7px; border-radius:999px; font-size:11px; font-weight:700; white-space:nowrap;">+${buildings.length - 2} tòa</span>` : '') + `</div>`
          : '<span style="color:#94a3b8; font-size:11.5px;">Chưa phân công</span>';

        const statusClass = (p.status === 'ACTIVE') ? 'success' : 'danger';
        const statusText = (p.status === 'ACTIVE') ? 'Hoạt động' : 'Tạm khóa';
        const deptText = deptLabels[p.department] || p.department || 'Vận hành';

        return `
          <tr>
            <td style="white-space:nowrap;"><span style="font-family:monospace; font-weight:700; color:#b45309; background:#fef3c7; padding:3px 8px; border-radius:6px; font-size:12px;">${escapeHtml(p.employee_code || 'PM-' + p.id?.substring(0, 4))}</span></td>
            <td style="white-space:nowrap;">
              <strong>${escapeHtml(p.full_name || p.name || '---')}</strong>
              <div style="font-size:11.5px; color:#64748b;">${escapeHtml(p.email)}</div>
            </td>
            <td style="white-space:nowrap;">
              <span style="color:#dc2626; font-weight:700;"><i class="fa-solid fa-phone-volume"></i> ${escapeHtml(p.emergency_hotline || p.phone || '---')}</span>
            </td>
            <td style="white-space:nowrap;"><span style="background:#f1f5f9; color:#334155; padding:3px 8px; border-radius:6px; font-size:11.5px; font-weight:600;">${deptText}</span></td>
            <td>${bldHtml}</td>
            <td style="white-space:nowrap;"><span class="badge-filament ${statusClass}">${statusText}</span></td>
            <td style="text-align: right; white-space: nowrap;">
              <button class="btn-mo-outline" style="padding:4px 8px; font-size:12px;" onclick="viewStaffPm('${p.id}')" title="Xem chi tiết">
                <i class="fa-regular fa-eye"></i>
              </button>
              <button class="btn-mo-outline" style="padding:4px 8px; font-size:12px;" onclick="openEditPmModal('${p.id}')" title="Chỉnh sửa">
                <i class="fa-regular fa-pen-to-square"></i>
              </button>
              <button class="btn-mo-outline" style="padding:4px 8px; font-size:12px; color:#d97706; border-color:#fde68a;" onclick="resetStaffPassword('pm', '${p.id}', '${escapeHtml(p.full_name || p.name)}')" title="Cấp lại mật khẩu & gửi email">
                <i class="fa-solid fa-key"></i>
              </button>
              <button class="btn-mo-outline" style="padding:4px 8px; font-size:12px; color:#dc2626; border-color:#fca5a5;" onclick="deleteStaffPm('${p.id}', '${escapeHtml(p.full_name || p.name)}')" title="Xóa mềm">
                <i class="fa-regular fa-trash-can"></i>
              </button>
            </td>
          </tr>
        `;
      }).join('');
    }

    // Helper render HTML danh sách Kế toán
    function renderStaffAccountantsHtml(accs) {
      if (!accs || accs.length === 0) {
        return '<tr><td colspan="7" style="text-align:center; padding: 25px; color:#94a3b8;">Chưa có dữ liệu nhân viên Kế toán nào.</td></tr>';
      }
      return accs.map(a => {
        const buildings = a.account_buildings || a.buildings || [];
        const bldHtml = buildings.length > 0
          ? `<div style="display:flex;flex-wrap:wrap;gap:5px;align-items:center;">` + buildings.slice(0, 2).map(b => `<span style="background:#ecfdf5; color:#065f46; padding:3px 8px; border-radius:6px; font-size:11.5px; font-weight:600; white-space:nowrap;">${escapeHtml(b.name || 'Tòa nhà')}</span>`).join('') + (buildings.length > 2 ? `<span style="background:#f1f5f9; color:#475569; padding:2px 7px; border-radius:999px; font-size:11px; font-weight:700; white-space:nowrap;">+${buildings.length - 2} tòa</span>` : '') + `</div>`
          : '<span style="color:#94a3b8; font-size:11.5px;">Toàn hệ thống</span>';

        const canAdjustHtml = a.can_approve_adjustment
          ? '<span style="background:#dcfce7; color:#15803d; font-size:11.5px; font-weight:700; padding:2px 8px; border-radius:9999px; white-space:nowrap;"><i class="fa-solid fa-check"></i> Được phép</span>'
          : '<span style="color:#94a3b8; font-size:11.5px;">Không</span>';

        const statusClass = (a.status === 'ACTIVE') ? 'success' : 'danger';
        const statusText = (a.status === 'ACTIVE') ? 'Hoạt động' : 'Tạm khóa';

        return `
          <tr>
            <td style="white-space:nowrap;"><span style="font-family:monospace; font-weight:700; color:#065f46; background:#ecfdf5; padding:3px 8px; border-radius:6px; font-size:12px;">${escapeHtml(a.employee_code || 'ACC-' + a.id?.substring(0, 4))}</span></td>
            <td style="white-space:nowrap;"><strong>${escapeHtml(a.full_name || a.name || '---')}</strong></td>
            <td>
              <div style="font-size:12.5px; color:#2563eb;">${escapeHtml(a.email)}</div>
              <div style="font-size:11.5px; color:#64748b;">${escapeHtml(a.phone || '---')}</div>
            </td>
            <td style="white-space:nowrap;">${canAdjustHtml}</td>
            <td>${bldHtml}</td>
            <td style="white-space:nowrap;"><span class="badge-filament ${statusClass}">${statusText}</span></td>
            <td style="text-align: right; white-space: nowrap;">
              <button class="btn-mo-outline" style="padding:4px 8px; font-size:12px;" onclick="viewStaffAccountant('${a.id}')" title="Xem chi tiết">
                <i class="fa-regular fa-eye"></i>
              </button>
              <button class="btn-mo-outline" style="padding:4px 8px; font-size:12px;" onclick="openEditAccountantModal('${a.id}')" title="Chỉnh sửa">
                <i class="fa-regular fa-pen-to-square"></i>
              </button>
              <button class="btn-mo-outline" style="padding:4px 8px; font-size:12px; color:#d97706; border-color:#fde68a;" onclick="resetStaffPassword('accountant', '${a.id}', '${escapeHtml(a.full_name || a.name)}')" title="Cấp lại mật khẩu & gửi email">
                <i class="fa-solid fa-key"></i>
              </button>
              <button class="btn-mo-outline" style="padding:4px 8px; font-size:12px; color:#dc2626; border-color:#fca5a5;" onclick="deleteStaffAccountant('${a.id}', '${escapeHtml(a.full_name || a.name)}')" title="Xóa mềm">
                <i class="fa-regular fa-trash-can"></i>
              </button>
            </td>
          </tr>
        `;
      }).join('');
    }

    // Tải trước dữ liệu cho TẤT CẢ các sub-tab nhân sự để bấm vào là có ngay dữ liệu tức thì
    function preloadAllStaffData() {
      const curRole = String(localStorage.getItem('auth_role') || '').toLowerCase();
      if (curRole !== 'admin' && curRole !== 'super_admin') return;

      initStaffBadges();

      if (typeof SERVER_STAFF !== 'undefined' && SERVER_STAFF) {
        const tbAdmins = document.getElementById('tbody-staff-admins');
        const tbSales = document.getElementById('tbody-staff-sales');
        const tbPm = document.getElementById('tbody-staff-pm');
        const tbAcc = document.getElementById('tbody-staff-accountants');

        if (tbAdmins && SERVER_STAFF.admins && (!tbAdmins.children.length || tbAdmins.innerText.includes('Đang tải'))) {
          tbAdmins.innerHTML = renderStaffAdminsHtml(SERVER_STAFF.admins);
        }
        if (tbSales && SERVER_STAFF.sales && (!tbSales.children.length || tbSales.innerText.includes('Đang tải'))) {
          tbSales.innerHTML = renderStaffSalesHtml(SERVER_STAFF.sales);
        }
        if (tbPm && SERVER_STAFF.pm && (!tbPm.children.length || tbPm.innerText.includes('Đang tải'))) {
          tbPm.innerHTML = renderStaffPmHtml(SERVER_STAFF.pm);
        }
        if (tbAcc && SERVER_STAFF.accountants && (!tbAcc.children.length || tbAcc.innerText.includes('Đang tải'))) {
          tbAcc.innerHTML = renderStaffAccountantsHtml(SERVER_STAFF.accountants);
        }
      }

      // Khởi tạo luôn dropdown phân công và tải tòa nhà đầu tiên
      if (typeof populateBuildingAssignmentDropdown === 'function') {
        populateBuildingAssignmentDropdown();
      }

      isStaffPreloaded = true;

      // Đồng bộ ngầm trong nền không gây nhấp nháy spinner
      setTimeout(() => {
        try {
          Promise.allSettled([
            loadStaffAdmins(1, false),
            loadStaffSales(1, false),
            loadStaffPm(1, false),
            loadStaffAccountants(1, false)
          ]);
        } catch (e) {}
      }, 100);
    }

    function isSuperAdminUser() {
      const authUserStr = localStorage.getItem('auth_user');
      const realRole = String(localStorage.getItem('auth_role') || '').toUpperCase();
      if (!realRole && !authUserStr) return true; // Môi trường demo / guest admin mặc định
      if (realRole === 'SUPER_ADMIN') return true;
      if (authUserStr) {
        try {
          const u = JSON.parse(authUserStr);
          if (u && (u.is_super_admin === true || u.is_super_admin === 1 || u.is_super_admin === '1')) return true;
        } catch (e) {}
      }
      return false;
    }

    // Khởi tạo số lượng đếm badge cho 4 nhóm nhân sự từ dữ liệu server
    function initStaffBadges() {
      const bAdmins = document.getElementById('badge-count-admins');
      const bSales = document.getElementById('badge-count-sales');
      const bPm = document.getElementById('badge-count-pm');
      const bAcc = document.getElementById('badge-count-accountants');
      const bAss = document.getElementById('badge-count-assignments');

      // RBAC Super Admin: Ẩn sub-tab Admins nếu là Admin thường
      const btnAdmins = document.getElementById('btn-subtab-admins');
      const panelAdmins = document.getElementById('subpanel-staff-admins');
      const isSuper = isSuperAdminUser();
      if (btnAdmins) btnAdmins.style.display = isSuper ? 'inline-flex' : 'none';
      if (panelAdmins && !isSuper) panelAdmins.style.display = 'none';

      const getCount = (val, fallback) => {
        if (!val) return fallback;
        if (Array.isArray(val)) return val.length;
        if (typeof val === 'object') return Object.keys(val).length;
        return fallback;
      };

      const adminsLen = (typeof SERVER_STAFF !== 'undefined') ? getCount(SERVER_STAFF.admins, 2) : 2;
      const salesLen = (typeof SERVER_STAFF !== 'undefined') ? getCount(SERVER_STAFF.sales, 3) : 3;
      const pmLen = (typeof SERVER_STAFF !== 'undefined') ? getCount(SERVER_STAFF.pm, 3) : 3;
      const accLen = (typeof SERVER_STAFF !== 'undefined') ? getCount(SERVER_STAFF.accountants, 2) : 2;

      if (bAdmins) bAdmins.innerText = adminsLen;
      if (bSales) bSales.innerText = salesLen;
      if (bPm) bPm.innerText = pmLen;
      if (bAcc) bAcc.innerText = accLen;
      if (bAss && (bAss.innerText === '0' || bAss.innerText === 'Chọn tòa nhà')) bAss.innerText = '3 NV';
    }

    // 1. Tải danh sách tòa nhà phục vụ phân quyền phụ trách
    async function fetchStaffBuildingsList() {
      if (cachedStaffBuildings && cachedStaffBuildings.length > 0) return cachedStaffBuildings;
      if (typeof BUILDINGS_DATA !== 'undefined' && Array.isArray(BUILDINGS_DATA) && BUILDINGS_DATA.length > 0) {
        cachedStaffBuildings = BUILDINGS_DATA;
        return cachedStaffBuildings;
      }
      try {
        const token = getSanctumToken();
        const headers = { 'Accept': 'application/json' };
        if (token) headers['Authorization'] = 'Bearer ' + token;
        const res = await fetch('/api/v1/portal/buildings?per_page=100', { headers });
        if (res.ok) {
          const json = await res.json();
          cachedStaffBuildings = json.data?.data || json.data || [];
        }
      } catch (e) {
        console.warn('Không tải được danh sách tòa nhà phân quyền:', e);
      }
      if ((!cachedStaffBuildings || cachedStaffBuildings.length === 0) && typeof BUILDINGS_DATA !== 'undefined' && Array.isArray(BUILDINGS_DATA)) {
        cachedStaffBuildings = BUILDINGS_DATA;
      }
      return cachedStaffBuildings;
    }

    // Render checkbox danh sách tòa nhà vào container
    function renderBuildingCheckboxes(containerId, selectedIds = []) {
      const container = document.getElementById(containerId);
      if (!container) return;
      if (!cachedStaffBuildings || cachedStaffBuildings.length === 0) {
        container.innerHTML = '<span style="color:#94a3b8; font-size:12px;">Chưa có dữ liệu tòa nhà trong hệ thống.</span>';
        return;
      }
      const selSet = new Set((selectedIds || []).map(String));
      container.innerHTML = cachedStaffBuildings.map(b => `
        <label class="building-check-label">
          <input type="checkbox" name="${containerId}_bld[]" value="${b.id}" ${selSet.has(String(b.id)) ? 'checked' : ''}>
          <span><strong>${escapeHtml(b.name)}</strong> <small style="color:#64748b;">(${escapeHtml(b.district || 'TP.HCM')})</small></span>
        </label>
      `).join('');
    }

    // Lấy mảng ID các tòa nhà đã tích
    function getSelectedBuildingIds(containerId) {
      const boxes = document.querySelectorAll(`#${containerId} input[type="checkbox"]:checked`);
      return Array.from(boxes).map(cb => cb.value);
    }

    // 2. Chuyển đổi giữa các sub-tab nhân sự & cập nhật routing
    function switchStaffSubTab(tabName, btn, updateRoute = true) {
      // RBAC Super Admin: Chặn Admin thường vào xem sub-tab Admins (API 72-76)
      if (tabName === 'admins' && !isSuperAdminUser()) {
        alert('Chỉ Quản trị viên cấp cao (Super Admin) mới có quyền truy cập danh mục Quản Trị Viên.');
        return switchStaffSubTab('sales', document.getElementById('btn-subtab-sales'), updateRoute);
      }

      currentStaffTab = tabName;
      initStaffBadges();
      // Active button
      document.querySelectorAll('.staff-subtab-btn').forEach(b => b.classList.remove('active'));
      if (btn) {
        btn.classList.add('active');
      } else {
        const activeBtn = document.getElementById(`btn-subtab-${tabName}`);
        if (activeBtn) activeBtn.classList.add('active');
      }

      // Hide all panels
      document.querySelectorAll('.staff-subpanel').forEach(p => p.style.display = 'none');
      const targetPanel = document.getElementById(`subpanel-staff-${tabName}`);
      if (targetPanel) targetPanel.style.display = 'block';

      // Cập nhật URL route để khi F5 không bị văng về trang chủ hoặc mất tab con
      if (updateRoute && typeof updateAppRoute === 'function') {
        updateAppRoute('admin/staff/' + tabName);
      }

      // Nạp trước dữ liệu toàn bộ subtab nếu chưa nạp
      if (!isStaffPreloaded) {
        preloadAllStaffData();
      }

      // Nếu bảng rỗng thì nạp dữ liệu
      if (tabName === 'admins') {
        const tb = document.getElementById('tbody-staff-admins');
        if (!tb || !tb.children.length || tb.innerText.includes('Đang tải')) loadStaffAdmins(1);
      } else if (tabName === 'sales') {
        const tb = document.getElementById('tbody-staff-sales');
        if (!tb || !tb.children.length || tb.innerText.includes('Đang tải')) loadStaffSales(1);
      } else if (tabName === 'pm') {
        const tb = document.getElementById('tbody-staff-pm');
        if (!tb || !tb.children.length || tb.innerText.includes('Đang tải')) loadStaffPm(1);
      } else if (tabName === 'accountants') {
        const tb = document.getElementById('tbody-staff-accountants');
        if (!tb || !tb.children.length || tb.innerText.includes('Đang tải')) loadStaffAccountants(1);
      } else if (tabName === 'assignments') {
        const sel = document.getElementById('select-assignment-building');
        if (!sel || !sel.options || sel.options.length <= 1) populateBuildingAssignmentDropdown();
      }
    }

    function reloadCurrentStaffTab() {
      switchStaffSubTab(currentStaffTab);
    }

    // =========================================================================
    // 2. NHÓM QUẢN TRỊ VIÊN (ADMINS) — API 72 - 76
    // =========================================================================

    // API 72: Danh sách Admin
    async function loadStaffAdmins(page = 1, showLoading = true) {
      const tbody = document.getElementById('tbody-staff-admins');
      if (!tbody) return;
      if (showLoading && (!tbody.children.length || tbody.innerText.includes('Đang tải'))) {
        tbody.innerHTML = '<tr><td colspan="7" style="text-align:center; padding: 25px; color:#94a3b8;"><i class="fa-solid fa-spinner fa-spin"></i> Đang tải dữ liệu Admins...</td></tr>';
      }

      try {
        const token = getSanctumToken();
        const kw = document.getElementById('filter-admin-keyword')?.value?.trim() || '';
        const st = document.getElementById('filter-admin-status')?.value || '';

        const url = new URL('/api/v1/admin/users/admins', window.location.origin);
        url.searchParams.set('page', page);
        url.searchParams.set('per_page', 10);
        if (kw) url.searchParams.set('search', kw);
        if (st) url.searchParams.set('status', st);

        let admins = [];
        let meta = {};

        const res = await fetch(url.toString(), {
          headers: {
            'Accept': 'application/json',
            'Authorization': 'Bearer ' + token
          }
        });
        const json = await res.json();
        if (res.ok && json.data) {
          admins = Array.isArray(json.data) ? json.data : (json.data?.data || []);
          meta = json.meta || json.data?.meta || { total: admins.length, current_page: page, last_page: 1 };
        } else if (typeof SERVER_STAFF !== 'undefined' && SERVER_STAFF.admins && SERVER_STAFF.admins.length > 0) {
          admins = SERVER_STAFF.admins;
          meta = { total: admins.length, current_page: 1, last_page: 1 };
        } else {
          tbody.innerHTML = `<tr><td colspan="7" style="text-align:center; padding: 25px; color:#ef4444;"><i class="fa-solid fa-triangle-exclamation"></i> ${escapeHtml(json.message || 'Yêu cầu quyền Super Admin để truy cập danh sách Admin.')}</td></tr>`;
          return;
        }

        const countBadge = document.getElementById('badge-count-admins');
        if (countBadge) countBadge.innerText = (meta.total !== undefined ? meta.total : admins.length);

        if (admins.length === 0) {
          tbody.innerHTML = '<tr><td colspan="7" style="text-align:center; padding: 25px; color:#94a3b8;">Không tìm thấy tài khoản Quản trị viên nào phù hợp.</td></tr>';
          return;
        }

        const authUserStr = localStorage.getItem('auth_user');
        let currentAdminId = null;
        let currentAdminEmail = null;
        if (authUserStr) {
          try {
            const u = JSON.parse(authUserStr);
            currentAdminId = u?.id;
            currentAdminEmail = u?.email;
          } catch(e) {}
        }

        tbody.innerHTML = admins.map(a => {
          const isSuper = !!a.is_super_admin;
          const statusClass = (a.status === 'ACTIVE') ? 'success' : ((a.status === 'LOCKED') ? 'danger' : 'warning');
          const statusText = (a.status === 'ACTIVE') ? 'Hoạt động' : ((a.status === 'LOCKED') ? 'Đã khóa' : 'Chờ kích hoạt');
          const createdDate = a.created_at ? new Date(a.created_at).toLocaleDateString('vi-VN') : '---';
          const isSelf = (currentAdminId && String(currentAdminId) === String(a.id)) ||
                         (currentAdminEmail && String(currentAdminEmail).toLowerCase() === String(a.email).toLowerCase());

          return `
            <tr>
              <td>
                <div style="display:flex; align-items:center; gap:9px;">
                  <div style="width:32px; height:32px; border-radius:50%; background:${isSuper ? '#ef4444' : '#0f3361'}; color:#fff; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:12px;">
                    ${isSuper ? 'SA' : 'AD'}
                  </div>
                  <div>
                    <strong style="color:#0f172a; font-size:13.5px;">${escapeHtml(a.full_name || a.name || '---')}</strong>
                    ${isSelf ? '<span style="background:#dbeafe; color:#1d4ed8; font-size:10.5px; font-weight:700; padding:1px 6px; border-radius:4px; margin-left:6px;"><i class="fa-solid fa-user-check"></i> Bạn</span>' : ''}
                  </div>
                </div>
              </td>
              <td><span style="font-family:monospace; color:#2563eb;">${escapeHtml(a.email)}</span></td>
              <td>${escapeHtml(a.phone || '---')}</td>
              <td>
                ${isSuper 
                  ? '<span class="staff-badge-super"><i class="fa-solid fa-crown"></i> Super Admin</span>' 
                  : '<span class="staff-badge-admin">Admin Thường</span>'}
              </td>
              <td><span class="badge-filament ${statusClass}">${statusText}</span></td>
              <td><span style="color:#64748b; font-size:12px;">${createdDate}</span></td>
              <td style="text-align: right; white-space: nowrap;">
                <button class="btn-mo-outline" style="padding:4px 8px; font-size:12px;" onclick="viewStaffAdmin('${a.id}')" title="Xem chi tiết">
                  <i class="fa-regular fa-eye"></i>
                </button>
                <button class="btn-mo-outline" style="padding:4px 8px; font-size:12px;" onclick="openEditAdminModal('${a.id}')" title="Chỉnh sửa">
                  <i class="fa-regular fa-pen-to-square"></i>
                </button>
                ${isSelf 
                  ? `<button class="btn-mo-outline" style="padding:4px 8px; font-size:12px; color:#94a3b8; border-color:#e2e8f0; cursor:not-allowed; opacity:0.5;" disabled title="Không thể tự xóa tài khoản của chính mình"><i class="fa-regular fa-trash-can"></i></button>`
                  : `<button class="btn-mo-outline" style="padding:4px 8px; font-size:12px; color:#dc2626; border-color:#fca5a5;" onclick="deleteStaffAdmin('${a.id}', '${escapeHtml(a.full_name || a.name)}')" title="Xóa mềm"><i class="fa-regular fa-trash-can"></i></button>`
                }
              </td>
            </tr>
          `;
        }).join('');

        renderStaffPagination('pagination-staff-admins', meta, loadStaffAdmins);
      } catch (err) {
        console.error('Load admins error:', err);
        if (typeof SERVER_STAFF !== 'undefined' && SERVER_STAFF.admins && SERVER_STAFF.admins.length > 0) {
          const admins = SERVER_STAFF.admins;
          document.getElementById('badge-count-admins')?.innerText && (document.getElementById('badge-count-admins').innerText = admins.length);
          tbody.innerHTML = admins.map(a => {
            const isSuper = !!a.is_super_admin;
            const statusClass = (a.status === 'ACTIVE') ? 'success' : 'danger';
            const statusText = (a.status === 'ACTIVE') ? 'Hoạt động' : 'Tạm khóa';
            const createdDate = a.created_at ? new Date(a.created_at).toLocaleDateString('vi-VN') : '---';
            return `
              <tr>
                <td>
                  <div style="display:flex; align-items:center; gap:9px;">
                    <div style="width:32px; height:32px; border-radius:50%; background:${isSuper ? '#ef4444' : '#0f3361'}; color:#fff; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:12px;">
                      ${isSuper ? 'SA' : 'AD'}
                    </div>
                    <div><strong style="color:#0f172a; font-size:13.5px;">${escapeHtml(a.full_name || a.name || '---')}</strong></div>
                  </div>
                </td>
                <td><span style="font-family:monospace; color:#2563eb;">${escapeHtml(a.email)}</span></td>
                <td>${escapeHtml(a.phone || '---')}</td>
                <td>${isSuper ? '<span class="staff-badge-super"><i class="fa-solid fa-crown"></i> Super Admin</span>' : '<span class="staff-badge-admin">Admin Thường</span>'}</td>
                <td><span class="badge-filament ${statusClass}">${statusText}</span></td>
                <td><span style="color:#64748b; font-size:12px;">${createdDate}</span></td>
                <td style="text-align: right; white-space: nowrap;">
                  <button class="btn-mo-outline" style="padding:4px 8px; font-size:12px;" onclick="viewStaffAdmin('${a.id}')"><i class="fa-regular fa-eye"></i></button>
                  <button class="btn-mo-outline" style="padding:4px 8px; font-size:12px;" onclick="openEditAdminModal('${a.id}')"><i class="fa-regular fa-pen-to-square"></i></button>
                </td>
              </tr>
            `;
          }).join('');
        } else {
          tbody.innerHTML = '<tr><td colspan="7" style="text-align:center; padding: 25px; color:#ef4444;">Lỗi kết nối API danh sách Admin.</td></tr>';
        }
      }
    }

    // Helper toàn cục: Lấy mã nhân viên tự động từ CSDL
    async function fetchNextEmployeeCode(role, inputId) {
      const input = document.getElementById(inputId);
      if (!input) return;
      input.value = 'Đang tải mã...';
      try {
        const token = getSanctumToken();
        const res = await fetch(`/api/v1/admin/users/next-employee-code?role=${role}`, {
          headers: { 'Accept': 'application/json', 'Authorization': 'Bearer ' + token }
        });
        const json = await res.json();
        if (res.ok && json.data && json.data.next_code) {
          input.value = json.data.next_code;
        } else {
          input.value = '';
        }
      } catch (e) {
        console.error('Fetch next employee code error:', e);
        input.value = '';
      }
    }

    // Mở modal tạo mới Admin
    function openCreateAdminModal() {
      document.getElementById('form-staff-admin').reset();
      document.getElementById('admin-form-id').value = '';
      document.getElementById('admin-form-mode').value = 'create';
      document.getElementById('staff-admin-modal-title').innerHTML = '<i class="fa-solid fa-user-plus"></i> Thêm Quản Trị Viên Mới';
      document.getElementById('admin-form-password-req').style.display = 'none';
      document.getElementById('admin-form-password-hint').style.display = 'block';

      // Reset quyền Super Admin & Trạng thái tài khoản
      const isSuperCb = document.getElementById('admin-form-issuper');
      const isSuperLabel = document.getElementById('admin-form-issuper-label');
      const isSuperHint = document.getElementById('admin-form-issuper-hint');
      const statusSelect = document.getElementById('admin-form-status');
      const statusHint = document.getElementById('admin-form-status-hint');

      if (isSuperCb) {
        isSuperCb.disabled = false;
        isSuperCb.checked = false;
      }
      if (isSuperLabel) isSuperLabel.style.cursor = 'pointer';
      if (isSuperHint) isSuperHint.style.display = 'none';

      if (statusSelect) {
        statusSelect.disabled = false;
        statusSelect.value = 'ACTIVE';
        statusSelect.style.background = '#fff';
        statusSelect.style.cursor = 'default';
      }
      if (statusHint) statusHint.style.display = 'none';

      moClearFieldError('admin-form-fullname', 'err-admin-fullname');
      moClearFieldError('admin-form-email', 'err-admin-email');
      moClearFieldError('admin-form-phone', 'err-admin-phone');
      moClearFieldError('admin-form-password', 'err-admin-password');

      openModal('staff-admin-modal');
    }

    // Mở modal sửa Admin (API 74 + 75)
    async function openEditAdminModal(id) {
      const token = getSanctumToken();
      moClearFieldError('admin-form-fullname', 'err-admin-fullname');
      moClearFieldError('admin-form-email', 'err-admin-email');
      moClearFieldError('admin-form-phone', 'err-admin-phone');
      moClearFieldError('admin-form-password', 'err-admin-password');

      try {
        const res = await fetch(`/api/v1/admin/users/admins/${id}`, {
          headers: { 'Accept': 'application/json', 'Authorization': 'Bearer ' + token }
        });
        const json = await res.json();
        if (!res.ok) {
          alert('Không thể tải chi tiết Admin: ' + (json.message || ''));
          return;
        }
        const a = json.data;
        document.getElementById('admin-form-id').value = a.id;
        document.getElementById('admin-form-mode').value = 'edit';
        document.getElementById('admin-form-fullname').value = a.full_name || a.name || '';
        document.getElementById('admin-form-email').value = a.email || '';
        document.getElementById('admin-form-phone').value = a.phone || '';

        // Kiểm tra xem có đang chỉnh sửa chính tài khoản đang đăng nhập hay không
        const authUserStr = localStorage.getItem('auth_user');
        let currentAdminId = null;
        let currentAdminEmail = null;
        if (authUserStr) {
          try {
            const u = JSON.parse(authUserStr);
            currentAdminId = u?.id;
            currentAdminEmail = u?.email;
          } catch(e) {}
        }
        const isSelf = (currentAdminId && String(currentAdminId) === String(a.id)) ||
                       (currentAdminEmail && String(currentAdminEmail).toLowerCase() === String(a.email).toLowerCase());

        const isSuperCb = document.getElementById('admin-form-issuper');
        const isSuperLabel = document.getElementById('admin-form-issuper-label');
        const isSuperHint = document.getElementById('admin-form-issuper-hint');
        const statusSelect = document.getElementById('admin-form-status');
        const statusHint = document.getElementById('admin-form-status-hint');

        isSuperCb.checked = !!a.is_super_admin;
        statusSelect.value = a.status || 'ACTIVE';

        if (isSelf) {
          // Bảo vệ tài khoản Super Admin đang đăng nhập: Không cho tự hạ quyền
          if (a.is_super_admin) {
            isSuperCb.disabled = true;
            isSuperCb.checked = true;
            if (isSuperLabel) isSuperLabel.style.cursor = 'not-allowed';
            if (isSuperHint) isSuperHint.style.display = 'flex';
          } else {
            isSuperCb.disabled = false;
            if (isSuperLabel) isSuperLabel.style.cursor = 'pointer';
            if (isSuperHint) isSuperHint.style.display = 'none';
          }

          // Bảo vệ trạng thái hoạt động: Không cho tự khóa tài khoản của chính mình
          statusSelect.value = 'ACTIVE';
          statusSelect.disabled = true;
          statusSelect.style.background = '#f1f5f9';
          statusSelect.style.cursor = 'not-allowed';
          if (statusHint) statusHint.style.display = 'flex';
        } else {
          isSuperCb.disabled = false;
          if (isSuperLabel) isSuperLabel.style.cursor = 'pointer';
          if (isSuperHint) isSuperHint.style.display = 'none';

          statusSelect.disabled = false;
          statusSelect.style.background = '#fff';
          statusSelect.style.cursor = 'default';
          if (statusHint) statusHint.style.display = 'none';
        }

        // Password optional khi sửa
        document.getElementById('admin-form-password').value = '';
        document.getElementById('admin-form-password-req').style.display = 'none';
        document.getElementById('admin-form-password-hint').style.display = 'block';

        document.getElementById('staff-admin-modal-title').innerHTML = '<i class="fa-solid fa-user-pen"></i> Cập Nhật Quản Trị Viên';
        openModal('staff-admin-modal');
      } catch (err) {
        console.error('Fetch admin error:', err);
      }
    }

    // Submit form Admin (API 73: POST / API 75: PUT)
    async function handleStaffAdminSubmit(e) {
      e.preventDefault();
      const mode = document.getElementById('admin-form-mode').value;
      const id = document.getElementById('admin-form-id').value;
      const token = getSanctumToken();

      moClearFieldError('admin-form-fullname', 'err-admin-fullname');
      moClearFieldError('admin-form-email', 'err-admin-email');
      moClearFieldError('admin-form-phone', 'err-admin-phone');
      moClearFieldError('admin-form-password', 'err-admin-password');

      const fullName = document.getElementById('admin-form-fullname').value.trim();
      const email = document.getElementById('admin-form-email').value.trim();
      const phone = document.getElementById('admin-form-phone').value.trim();
      const pwd = document.getElementById('admin-form-password').value;

      // Validation 0ms
      const vName = moValidateFullName(fullName, 2);
      if (!vName.valid) {
        moSetFieldError('admin-form-fullname', 'err-admin-fullname', vName.message);
        document.getElementById('admin-form-fullname').focus();
        return;
      }

      const vEmail = moValidateEmail(email);
      if (!vEmail.valid) {
        moSetFieldError('admin-form-email', 'err-admin-email', vEmail.message);
        document.getElementById('admin-form-email').focus();
        return;
      }

      if (phone) {
        const vPhone = moValidatePhone(phone);
        if (!vPhone.valid) {
          moSetFieldError('admin-form-phone', 'err-admin-phone', vPhone.message);
          document.getElementById('admin-form-phone').focus();
          return;
        }
      }

      if (pwd && pwd.length < 8) {
        moSetFieldError('admin-form-password', 'err-admin-password', 'Mật khẩu phải có tối thiểu 8 ký tự.');
        document.getElementById('admin-form-password').focus();
        return;
      }

      // Xác định nếu đang sửa tài khoản của chính mình
      const authUserStr = localStorage.getItem('auth_user');
      let currentAdminId = null;
      let currentAdminEmail = null;
      if (authUserStr) {
        try {
          const u = JSON.parse(authUserStr);
          currentAdminId = u?.id;
          currentAdminEmail = u?.email;
        } catch(e) {}
      }
      const isSelf = (currentAdminId && String(currentAdminId) === String(id)) ||
                     (currentAdminEmail && String(currentAdminEmail).toLowerCase() === String(email).toLowerCase());

      let isSuperAdminVal = document.getElementById('admin-form-issuper').checked;
      let statusVal = document.getElementById('admin-form-status').value;

      if (mode === 'edit' && isSelf) {
        isSuperAdminVal = true; // Luôn bảo lưu quyền Super Admin
        statusVal = 'ACTIVE';    // Luôn bảo lưu trạng thái ACTIVE
      }

      const payload = {
        full_name: fullName,
        email: email,
        phone: phone || null,
        is_super_admin: isSuperAdminVal,
        status: statusVal
      };

      if (pwd) payload.password = pwd;

      const url = (mode === 'create') 
        ? '/api/v1/admin/users/admins' 
        : `/api/v1/admin/users/admins/${id}`;
      const method = (mode === 'create') ? 'POST' : 'PUT';

      const btn = document.getElementById('btn-submit-staff-admin');
      const originalText = btn.innerHTML;
      btn.disabled = true;
      btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Đang lưu...';

      try {
        const res = await fetch(url, {
          method,
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'Authorization': 'Bearer ' + token
          },
          body: JSON.stringify(payload)
        });
        const json = await res.json();
        if (res.ok) {
          closeModal('staff-admin-modal');
          loadStaffAdmins(1);
          if (mode === 'create' && json.data?.plain_password) {
            showStaffCredentialSuccessModal({
              title: 'Khởi Tạo Quản Trị Viên Thành Công',
              desc: `Tài khoản cho Quản trị viên <strong>${escapeHtml(json.data.full_name || '')}</strong> đã được khởi tạo thành công. Email thông báo tài khoản và mật khẩu đã được tự động xếp vào hàng đợi gửi đi.`,
              name: json.data.full_name || '',
              email: json.data.email || '',
              password: json.data.plain_password
            });
          } else {
            alert(json.message || 'Lưu thông tin Quản trị viên thành công!');
          }
        } else {
          if (json.errors) {
            if (json.errors.full_name) moSetFieldError('admin-form-fullname', 'err-admin-fullname', json.errors.full_name[0]);
            if (json.errors.email) moSetFieldError('admin-form-email', 'err-admin-email', json.errors.email[0]);
            if (json.errors.phone) moSetFieldError('admin-form-phone', 'err-admin-phone', json.errors.phone[0]);
            if (json.errors.password) moSetFieldError('admin-form-password', 'err-admin-password', json.errors.password[0]);
          } else {
            alert('Lỗi: ' + (json.message || 'Thao tác không thành công.'));
          }
        }
      } catch (err) {
        console.error('Submit admin error:', err);
        alert('Lỗi kết nối máy chủ khi lưu Admin.');
      } finally {
        btn.disabled = false;
        btn.innerHTML = originalText;
      }
    }

    // API 74: Xem chi tiết Admin
    async function viewStaffAdmin(id) {
      openModal('staff-admin-detail-modal');
      const body = document.getElementById('body-staff-admin-detail');
      body.innerHTML = '<div style="text-align:center; padding:30px;"><i class="fa-solid fa-spinner fa-spin"></i> Đang tải dữ liệu...</div>';
      const token = getSanctumToken();

      try {
        const res = await fetch(`/api/v1/admin/users/admins/${id}`, {
          headers: { 'Accept': 'application/json', 'Authorization': 'Bearer ' + token }
        });
        const json = await res.json();
        if (!res.ok) {
          body.innerHTML = `<div style="color:#ef4444; padding:20px; text-align:center;">${escapeHtml(json.message || 'Không thể xem chi tiết')}</div>`;
          return;
        }
        const a = json.data;
        body.innerHTML = `
          <div style="background:#f8fafc; border-radius:8px; padding:16px; border:1px solid #e2e8f0; margin-bottom:14px;">
            <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
              <span style="color:#64748b; font-size:12.5px;">Mã định danh (UUID):</span>
              <strong style="font-family:monospace; font-size:11.5px; color:#0f3361;">${escapeHtml(a.id)}</strong>
            </div>
            <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
              <span style="color:#64748b; font-size:12.5px;">Họ và tên:</span>
              <strong style="font-size:13.5px; color:#0f172a;">${escapeHtml(a.full_name || a.name || '---')}</strong>
            </div>
            <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
              <span style="color:#64748b; font-size:12.5px;">Email đăng nhập:</span>
              <span style="font-family:monospace; color:#2563eb;">${escapeHtml(a.email)}</span>
            </div>
            <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
              <span style="color:#64748b; font-size:12.5px;">Số điện thoại:</span>
              <span>${escapeHtml(a.phone || 'Chưa cập nhật')}</span>
            </div>
            <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
              <span style="color:#64748b; font-size:12.5px;">Cấp bậc quyền hạn:</span>
              <span>${a.is_super_admin ? '<span class="staff-badge-super"><i class="fa-solid fa-crown"></i> Super Admin</span>' : '<span class="staff-badge-admin">Admin Thường</span>'}</span>
            </div>
            <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
              <span style="color:#64748b; font-size:12.5px;">Trạng thái tài khoản:</span>
              <span class="badge-filament ${(a.status === 'ACTIVE') ? 'success' : 'danger'}">${escapeHtml(a.status || 'ACTIVE')}</span>
            </div>
            <div style="display:flex; justify-content:space-between;">
              <span style="color:#64748b; font-size:12.5px;">Ngày khởi tạo:</span>
              <span style="font-size:12px;">${a.created_at ? new Date(a.created_at).toLocaleString('vi-VN') : '---'}</span>
            </div>
          </div>
          <div style="text-align:right;">
            <button class="btn-mo-primary" style="padding:6px 14px; font-size:13px;" onclick="closeModal('staff-admin-detail-modal')">Đóng cửa sổ</button>
          </div>
        `;
      } catch (e) {
        body.innerHTML = '<div style="color:#ef4444; padding:20px; text-align:center;">Lỗi kết nối khi tải chi tiết Admin.</div>';
      }
    }

    // API 76: Xóa mềm Admin
    async function deleteStaffAdmin(id, name) {
      const authUserStr = localStorage.getItem('auth_user');
      let currentAdminId = null;
      if (authUserStr) {
        try { currentAdminId = JSON.parse(authUserStr)?.id; } catch(e) {}
      }
      if (currentAdminId && currentAdminId === id) {
        alert('Cảnh báo bảo mật: Bạn không thể tự xóa tài khoản Quản trị viên của chính mình!');
        return;
      }

      if (!confirm(`Bạn có chắc chắn muốn xóa mềm tài khoản Quản trị viên [${name}] khỏi hệ thống?`)) return;

      const token = getSanctumToken();
      try {
        const res = await fetch(`/api/v1/admin/users/admins/${id}`, {
          method: 'DELETE',
          headers: { 'Accept': 'application/json', 'Authorization': 'Bearer ' + token }
        });
        const json = await res.json();
        if (res.ok) {
          alert(json.message || 'Đã xóa mềm tài khoản Quản trị viên thành công.');
          loadStaffAdmins(1);
        } else {
          alert('Lỗi: ' + (json.message || 'Không thể xóa tài khoản.'));
        }
      } catch (err) {
        console.error('Delete admin error:', err);
      }
    }

    // =========================================================================
    // 3. NHÓM NHÂN VIÊN SALES — API 77 - 81
    // =========================================================================

    // API 77: Danh sách Sales
    async function loadStaffSales(page = 1, showLoading = true) {
      const tbody = document.getElementById('tbody-staff-sales');
      if (!tbody) return;
      if (showLoading && (!tbody.children.length || tbody.innerText.includes('Đang tải'))) {
        tbody.innerHTML = '<tr><td colspan="8" style="text-align:center; padding: 25px; color:#94a3b8;"><i class="fa-solid fa-spinner fa-spin"></i> Đang tải danh sách Sales...</td></tr>';
      }

      try {
        const token = getSanctumToken();
        const kw = document.getElementById('filter-sale-keyword')?.value?.trim() || '';
        const st = document.getElementById('filter-sale-status')?.value || '';

        const url = new URL('/api/v1/admin/users/sales', window.location.origin);
        url.searchParams.set('page', page);
        url.searchParams.set('per_page', 10);
        if (kw) url.searchParams.set('search', kw);
        if (st) url.searchParams.set('status', st);

        let sales = [];
        let meta = {};

        const res = await fetch(url.toString(), {
          headers: { 'Accept': 'application/json', 'Authorization': 'Bearer ' + token }
        });
        const json = await res.json();
        if (res.ok && json.data) {
          sales = Array.isArray(json.data) ? json.data : (json.data?.data || []);
          meta = json.meta || json.data?.meta || { total: sales.length, current_page: page, last_page: 1 };
        } else if (typeof SERVER_STAFF !== 'undefined' && SERVER_STAFF.sales && SERVER_STAFF.sales.length > 0) {
          sales = SERVER_STAFF.sales;
          meta = { total: sales.length, current_page: 1, last_page: 1 };
        } else {
          tbody.innerHTML = `<tr><td colspan="8" style="text-align:center; padding: 25px; color:#ef4444;">${escapeHtml(json.message || 'Không thể tải danh sách Sales.')}</td></tr>`;
          return;
        }

        const countBadge = document.getElementById('badge-count-sales');
        if (countBadge) countBadge.innerText = (meta.total !== undefined ? meta.total : sales.length);

        if (sales.length === 0) {
          tbody.innerHTML = '<tr><td colspan="8" style="text-align:center; padding: 25px; color:#94a3b8;">Chưa có dữ liệu nhân viên Sales nào.</td></tr>';
          return;
        }

        tbody.innerHTML = renderStaffSalesHtml(sales);
        renderStaffPagination('pagination-staff-sales', meta, loadStaffSales);
      } catch (err) {
        console.error('Load sales error:', err);
        if (typeof SERVER_STAFF !== 'undefined' && SERVER_STAFF.sales && SERVER_STAFF.sales.length > 0) {
          const sales = SERVER_STAFF.sales;
          document.getElementById('badge-count-sales')?.innerText && (document.getElementById('badge-count-sales').innerText = sales.length);
          tbody.innerHTML = renderStaffSalesHtml(sales);
        } else {
          tbody.innerHTML = '<tr><td colspan="8" style="text-align:center; padding: 25px; color:#ef4444;">Lỗi kết nối API danh sách Sales.</td></tr>';
        }
      }
    }

    // Mở modal tạo mới Sales
    async function openCreateSaleModal() {
      document.getElementById('form-staff-sale').reset();
      document.getElementById('sale-form-id').value = '';
      document.getElementById('sale-form-mode').value = 'create';
      document.getElementById('staff-sale-modal-title').innerHTML = '<i class="fa-solid fa-user-plus"></i> Thêm Nhân Viên Sales Mới';
      document.getElementById('sale-form-password-req').style.display = 'none';
      document.getElementById('sale-form-password-hint').style.display = 'block';

      moClearFieldError('sale-form-fullname', 'err-sale-fullname');
      moClearFieldError('sale-form-email', 'err-sale-email');
      moClearFieldError('sale-form-phone', 'err-sale-phone');
      moClearFieldError('sale-form-password', 'err-sale-password');

      await fetchNextEmployeeCode('sales', 'sale-form-code');
      openModal('staff-sale-modal');
    }

    // Mở modal sửa Sales (API 79 + 80)
    async function openEditSaleModal(id) {
      const token = getSanctumToken();
      moClearFieldError('sale-form-fullname', 'err-sale-fullname');
      moClearFieldError('sale-form-email', 'err-sale-email');
      moClearFieldError('sale-form-phone', 'err-sale-phone');
      moClearFieldError('sale-form-password', 'err-sale-password');

      try {
        const res = await fetch(`/api/v1/admin/users/sales/${id}`, {
          headers: { 'Accept': 'application/json', 'Authorization': 'Bearer ' + token }
        });
        const json = await res.json();
        if (!res.ok) {
          alert('Không thể tải chi tiết Sales: ' + (json.message || ''));
          return;
        }
        const s = json.data?.sale || json.data;
        document.getElementById('sale-form-id').value = s.id;
        document.getElementById('sale-form-mode').value = 'edit';
        document.getElementById('sale-form-code').value = s.employee_code || '';
        document.getElementById('sale-form-fullname').value = s.full_name || s.name || '';
        document.getElementById('sale-form-email').value = s.email || '';
        document.getElementById('sale-form-phone').value = s.phone || '';
        document.getElementById('sale-form-max-leads').value = s.max_concurrent_leads || 10;
        document.getElementById('sale-form-commission').value = s.commission_rate || '';
        document.getElementById('sale-form-status').value = s.status || 'ACTIVE';

        document.getElementById('sale-form-password').value = '';
        document.getElementById('sale-form-password-req').style.display = 'none';
        document.getElementById('sale-form-password-hint').style.display = 'block';

        document.getElementById('staff-sale-modal-title').innerHTML = '<i class="fa-solid fa-user-pen"></i> Cập Nhật Nhân Viên Sales';
        openModal('staff-sale-modal');
      } catch (err) {
        console.error('Fetch sale detail error:', err);
      }
    }

    // Submit form Sales (API 78: POST / API 80: PUT)
    async function handleStaffSaleSubmit(e) {
      e.preventDefault();
      const mode = document.getElementById('sale-form-mode').value;
      const id = document.getElementById('sale-form-id').value;
      const token = getSanctumToken();

      moClearFieldError('sale-form-fullname', 'err-sale-fullname');
      moClearFieldError('sale-form-email', 'err-sale-email');
      moClearFieldError('sale-form-phone', 'err-sale-phone');
      moClearFieldError('sale-form-password', 'err-sale-password');

      const fullName = document.getElementById('sale-form-fullname').value.trim();
      const email = document.getElementById('sale-form-email').value.trim();
      const phone = document.getElementById('sale-form-phone').value.trim();
      const pwd = document.getElementById('sale-form-password').value;

      // Validation 0ms
      const vName = moValidateFullName(fullName, 2);
      if (!vName.valid) {
        moSetFieldError('sale-form-fullname', 'err-sale-fullname', vName.message);
        document.getElementById('sale-form-fullname').focus();
        return;
      }

      const vEmail = moValidateEmail(email);
      if (!vEmail.valid) {
        moSetFieldError('sale-form-email', 'err-sale-email', vEmail.message);
        document.getElementById('sale-form-email').focus();
        return;
      }

      if (phone) {
        const vPhone = moValidatePhone(phone);
        if (!vPhone.valid) {
          moSetFieldError('sale-form-phone', 'err-sale-phone', vPhone.message);
          document.getElementById('sale-form-phone').focus();
          return;
        }
      }

      if (pwd && pwd.length < 8) {
        moSetFieldError('sale-form-password', 'err-sale-password', 'Mật khẩu phải có tối thiểu 8 ký tự.');
        document.getElementById('sale-form-password').focus();
        return;
      }

      const payload = {
        employee_code: document.getElementById('sale-form-code').value.trim() || null,
        full_name: fullName,
        email: email,
        phone: phone || null,
        max_concurrent_leads: parseInt(document.getElementById('sale-form-max-leads').value) || 10,
        commission_rate: parseFloat(document.getElementById('sale-form-commission').value) || null,
        status: document.getElementById('sale-form-status').value,
        building_ids: []
      };

      if (pwd) payload.password = pwd;

      const url = (mode === 'create') ? '/api/v1/admin/users/sales' : `/api/v1/admin/users/sales/${id}`;
      const method = (mode === 'create') ? 'POST' : 'PUT';

      const btn = document.getElementById('btn-submit-staff-sale');
      const originalText = btn.innerHTML;
      btn.disabled = true;
      btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Đang lưu...';

      try {
        const res = await fetch(url, {
          method,
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'Authorization': 'Bearer ' + token
          },
          body: JSON.stringify(payload)
        });
        const json = await res.json();
        if (res.ok) {
          closeModal('staff-sale-modal');
          loadStaffSales(1);
          if (mode === 'create' && json.data?.plain_password) {
            showStaffCredentialSuccessModal({
              title: 'Khởi Tạo Nhân Viên Sales Thành Công',
              desc: `Tài khoản cho chuyên viên Sales <strong>${escapeHtml(json.data.full_name || '')}</strong> đã được khởi tạo thành công. Email thông báo tài khoản và mật khẩu đã được tự động xếp vào hàng đợi gửi đi.`,
              name: json.data.full_name || '',
              email: json.data.email || '',
              password: json.data.plain_password
            });
          } else {
            alert(json.message || 'Lưu nhân viên Sales thành công!');
          }
        } else {
          if (json.errors) {
            if (json.errors.full_name) moSetFieldError('sale-form-fullname', 'err-sale-fullname', json.errors.full_name[0]);
            if (json.errors.email) moSetFieldError('sale-form-email', 'err-sale-email', json.errors.email[0]);
            if (json.errors.phone) moSetFieldError('sale-form-phone', 'err-sale-phone', json.errors.phone[0]);
            if (json.errors.password) moSetFieldError('sale-form-password', 'err-sale-password', json.errors.password[0]);
          } else {
            alert('Lỗi: ' + (json.message || 'Thao tác không thành công.'));
          }
        }
      } catch (err) {
        console.error('Submit sale error:', err);
        alert('Lỗi kết nối máy chủ khi lưu Sales.');
      } finally {
        btn.disabled = false;
        btn.innerHTML = originalText;
      }
    }

    // API 79: Xem chi tiết Sales & KPI
    async function viewStaffSale(id) {
      openModal('staff-sale-detail-modal');
      const body = document.getElementById('body-staff-sale-detail');
      body.innerHTML = '<div style="text-align:center; padding:30px;"><i class="fa-solid fa-spinner fa-spin"></i> Đang tải dữ liệu...</div>';
      const token = getSanctumToken();

      try {
        const res = await fetch(`/api/v1/admin/users/sales/${id}`, {
          headers: { 'Accept': 'application/json', 'Authorization': 'Bearer ' + token }
        });
        const json = await res.json();
        if (!res.ok) {
          body.innerHTML = `<div style="color:#ef4444; padding:20px; text-align:center;">${escapeHtml(json.message || 'Không thể xem chi tiết')}</div>`;
          return;
        }
        const data = json.data || {};
        const s = data.sale || data;
        const buildings = s.sale_buildings || s.buildings || [];
        const leadsCount = s.active_leads_count ?? s.leads_count ?? 0;
        const conversionRate = data.conversion_rate ?? '0%';

        body.innerHTML = `
          <div style="background:#f8fafc; border-radius:8px; padding:16px; border:1px solid #e2e8f0; margin-bottom:14px;">
            <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
              <span style="color:#64748b; font-size:12.5px;">Mã nhân viên:</span>
              <strong style="font-family:monospace; color:#0f3361;">${escapeHtml(s.employee_code || '---')}</strong>
            </div>
            <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
              <span style="color:#64748b; font-size:12.5px;">Họ và tên:</span>
              <strong style="color:#0f172a; font-size:14px;">${escapeHtml(s.full_name || s.name || '---')}</strong>
            </div>
            <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
              <span style="color:#64748b; font-size:12.5px;">Email đăng nhập:</span>
              <span style="font-family:monospace; color:#2563eb;">${escapeHtml(s.email)}</span>
            </div>
            <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
              <span style="color:#64748b; font-size:12.5px;">Số điện thoại:</span>
              <span>${escapeHtml(s.phone || 'Chưa cập nhật')}</span>
            </div>
            <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
              <span style="color:#64748b; font-size:12.5px;">Hạn mức Lead cùng lúc:</span>
              <span><strong>${leadsCount}</strong> / ${s.max_concurrent_leads || 10} leads</span>
            </div>
            <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
              <span style="color:#64748b; font-size:12.5px;">Tỷ lệ chốt thành công (KPI):</span>
              <span style="color:#10b981; font-weight:700;">${conversionRate}</span>
            </div>
            <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
              <span style="color:#64748b; font-size:12.5px;">Hoa hồng:</span>
              <span style="color:#ea580c; font-weight:700;">${s.commission_rate ? s.commission_rate + '%' : '---'}</span>
            </div>
            <div style="display:flex; justify-content:space-between;">
              <span style="color:#64748b; font-size:12.5px;">Trạng thái:</span>
              <span class="badge-filament ${(s.status === 'ACTIVE') ? 'success' : 'danger'}">${escapeHtml(s.status || 'ACTIVE')}</span>
            </div>
          </div>

          <div style="margin-bottom:16px;">
            <h4 style="font-size:13px; font-weight:700; color:#334155; margin-bottom:8px;">Tòa nhà phân công phụ trách (${buildings.length}):</h4>
            <div style="display:flex; flex-wrap:wrap; gap:6px;">
              ${buildings.length > 0 
                ? buildings.map(b => `<span style="background:#e0e7ff; color:#3730a3; padding:4px 8px; border-radius:6px; font-size:12px;"><i class="fa-regular fa-building"></i> ${escapeHtml(b.name || 'Tòa nhà')}</span>`).join('')
                : '<span style="color:#94a3b8; font-size:12px;">Được tiếp cận toàn bộ tòa nhà trong hệ thống.</span>'}
            </div>
          </div>

          <div style="text-align:right;">
            <button class="btn-mo-primary" style="padding:6px 14px; font-size:13px;" onclick="closeModal('staff-sale-detail-modal')">Đóng cửa sổ</button>
          </div>
        `;
      } catch (e) {
        body.innerHTML = '<div style="color:#ef4444; padding:20px; text-align:center;">Lỗi kết nối khi tải hồ sơ Sales.</div>';
      }
    }

    // API 81: Xóa mềm Sales
    async function deleteStaffSale(id, name) {
      if (!confirm(`Bạn có chắc chắn muốn xóa mềm nhân viên Sales [${name}]?\n(Các Lead đang xử lý sẽ được thu hồi hoặc chuyển giao)`)) return;
      const token = getSanctumToken();
      try {
        const res = await fetch(`/api/v1/admin/users/sales/${id}`, {
          method: 'DELETE',
          headers: { 'Accept': 'application/json', 'Authorization': 'Bearer ' + token }
        });
        const json = await res.json();
        if (res.ok) {
          alert(json.message || 'Đã xóa mềm nhân viên Sales thành công.');
          loadStaffSales(1);
        } else {
          alert('Lỗi: ' + (json.message || 'Không thể xóa nhân viên.'));
        }
      } catch (err) {
        console.error('Delete sale error:', err);
      }
    }

    // =========================================================================
    // 4. NHÓM BAN QUẢN LÝ (PM) — API 82 - 86
    // =========================================================================

    // API 82: Danh sách PM
    async function loadStaffPm(page = 1, showLoading = true) {
      const tbody = document.getElementById('tbody-staff-pm');
      if (!tbody) return;
      if (showLoading && (!tbody.children.length || tbody.innerText.includes('Đang tải'))) {
        tbody.innerHTML = '<tr><td colspan="7" style="text-align:center; padding: 25px; color:#94a3b8;"><i class="fa-solid fa-spinner fa-spin"></i> Đang tải danh sách BQL...</td></tr>';
      }

      try {
        const token = getSanctumToken();
        const kw = document.getElementById('filter-pm-keyword')?.value?.trim() || '';
        const dept = document.getElementById('filter-pm-department')?.value || '';
        const st = document.getElementById('filter-pm-status')?.value || '';

        const url = new URL('/api/v1/admin/users/property-managers', window.location.origin);
        url.searchParams.set('page', page);
        url.searchParams.set('per_page', 10);
        if (kw) url.searchParams.set('search', kw);
        if (dept) url.searchParams.set('department', dept);
        if (st) url.searchParams.set('status', st);

        let pms = [];
        let meta = {};

        const res = await fetch(url.toString(), {
          headers: { 'Accept': 'application/json', 'Authorization': 'Bearer ' + token }
        });
        const json = await res.json();
        if (res.ok && json.data) {
          pms = Array.isArray(json.data) ? json.data : (json.data?.data || []);
          meta = json.meta || json.data?.meta || { total: pms.length, current_page: page, last_page: 1 };
        } else if (typeof SERVER_STAFF !== 'undefined' && SERVER_STAFF.pm && SERVER_STAFF.pm.length > 0) {
          pms = SERVER_STAFF.pm;
          meta = { total: pms.length, current_page: 1, last_page: 1 };
        } else {
          tbody.innerHTML = `<tr><td colspan="7" style="text-align:center; padding: 25px; color:#ef4444;">${escapeHtml(json.message || 'Không thể tải danh sách BQL.')}</td></tr>`;
          return;
        }

        const countBadge = document.getElementById('badge-count-pm');
        if (countBadge) countBadge.innerText = (meta.total !== undefined ? meta.total : pms.length);

        if (pms.length === 0) {
          tbody.innerHTML = '<tr><td colspan="7" style="text-align:center; padding: 25px; color:#94a3b8;">Chưa có dữ liệu nhân sự Ban Quản lý nào.</td></tr>';
          return;
        }

        tbody.innerHTML = renderStaffPmHtml(pms);
        renderStaffPagination('pagination-staff-pm', meta, loadStaffPm);
      } catch (err) {
        console.error('Load PM error:', err);
        if (typeof SERVER_STAFF !== 'undefined' && SERVER_STAFF.pm && SERVER_STAFF.pm.length > 0) {
          const pms = SERVER_STAFF.pm;
          document.getElementById('badge-count-pm')?.innerText && (document.getElementById('badge-count-pm').innerText = pms.length);
          tbody.innerHTML = renderStaffPmHtml(pms);
        } else {
          tbody.innerHTML = '<tr><td colspan="7" style="text-align:center; padding: 25px; color:#ef4444;">Lỗi kết nối API danh sách BQL.</td></tr>';
        }
      }
    }

    // Mở modal tạo mới PM
    async function openCreatePmModal() {
      document.getElementById('form-staff-pm').reset();
      document.getElementById('pm-form-id').value = '';
      document.getElementById('pm-form-mode').value = 'create';
      document.getElementById('staff-pm-modal-title').innerHTML = '<i class="fa-solid fa-user-plus"></i> Thêm Nhân Sự Ban Quản Lý (PM)';
      document.getElementById('pm-form-password-req').style.display = 'none';
      document.getElementById('pm-form-password-hint').style.display = 'block';

      moClearFieldError('pm-form-fullname', 'err-pm-fullname');
      moClearFieldError('pm-form-email', 'err-pm-email');
      moClearFieldError('pm-form-phone', 'err-pm-phone');
      moClearFieldError('pm-form-hotline', 'err-pm-hotline');
      moClearFieldError('pm-form-password', 'err-pm-password');

      await fetchNextEmployeeCode('pm', 'pm-form-code');
      await fetchStaffBuildingsList();
      renderBuildingCheckboxes('pm-form-buildings', []);
      openModal('staff-pm-modal');
    }

    // Mở modal sửa PM (API 84 + 85)
    async function openEditPmModal(id) {
      const token = getSanctumToken();
      moClearFieldError('pm-form-fullname', 'err-pm-fullname');
      moClearFieldError('pm-form-email', 'err-pm-email');
      moClearFieldError('pm-form-phone', 'err-pm-phone');
      moClearFieldError('pm-form-hotline', 'err-pm-hotline');
      moClearFieldError('pm-form-password', 'err-pm-password');

      try {
        const res = await fetch(`/api/v1/admin/users/property-managers/${id}`, {
          headers: { 'Accept': 'application/json', 'Authorization': 'Bearer ' + token }
        });
        const json = await res.json();
        if (!res.ok) {
          alert('Không thể tải chi tiết BQL: ' + (json.message || ''));
          return;
        }
        const p = json.data?.property_manager || json.data;
        document.getElementById('pm-form-id').value = p.id;
        document.getElementById('pm-form-mode').value = 'edit';
        document.getElementById('pm-form-code').value = p.employee_code || '';
        document.getElementById('pm-form-fullname').value = p.full_name || p.name || '';
        document.getElementById('pm-form-email').value = p.email || '';
        document.getElementById('pm-form-phone').value = p.phone || '';
        document.getElementById('pm-form-hotline').value = p.emergency_hotline || '';
        document.getElementById('pm-form-department').value = p.department || 'TECHNICAL';
        document.getElementById('pm-form-status').value = p.status || 'ACTIVE';

        document.getElementById('pm-form-password').value = '';
        document.getElementById('pm-form-password-req').style.display = 'none';
        document.getElementById('pm-form-password-hint').style.display = 'block';

        await fetchStaffBuildingsList();
        const assignedIds = (p.manager_buildings || p.buildings || []).map(b => b.id || b.building_id);
        renderBuildingCheckboxes('pm-form-buildings', assignedIds);

        document.getElementById('staff-pm-modal-title').innerHTML = '<i class="fa-solid fa-user-pen"></i> Cập Nhật Nhân Sự BQL';
        openModal('staff-pm-modal');
      } catch (err) {
        console.error('Fetch PM detail error:', err);
      }
    }

    // Submit form PM (API 83: POST / API 85: PUT)
    async function handleStaffPmSubmit(e) {
      e.preventDefault();
      const mode = document.getElementById('pm-form-mode').value;
      const id = document.getElementById('pm-form-id').value;
      const token = getSanctumToken();

      moClearFieldError('pm-form-fullname', 'err-pm-fullname');
      moClearFieldError('pm-form-email', 'err-pm-email');
      moClearFieldError('pm-form-phone', 'err-pm-phone');
      moClearFieldError('pm-form-hotline', 'err-pm-hotline');
      moClearFieldError('pm-form-password', 'err-pm-password');

      const fullName = document.getElementById('pm-form-fullname').value.trim();
      const email = document.getElementById('pm-form-email').value.trim();
      const phone = document.getElementById('pm-form-phone').value.trim();
      const hotline = document.getElementById('pm-form-hotline').value.trim();
      const pwd = document.getElementById('pm-form-password').value;

      // Validation 0ms
      const vName = moValidateFullName(fullName, 2);
      if (!vName.valid) {
        moSetFieldError('pm-form-fullname', 'err-pm-fullname', vName.message);
        document.getElementById('pm-form-fullname').focus();
        return;
      }

      const vEmail = moValidateEmail(email);
      if (!vEmail.valid) {
        moSetFieldError('pm-form-email', 'err-pm-email', vEmail.message);
        document.getElementById('pm-form-email').focus();
        return;
      }

      if (phone) {
        const vPhone = moValidatePhone(phone);
        if (!vPhone.valid) {
          moSetFieldError('pm-form-phone', 'err-pm-phone', vPhone.message);
          document.getElementById('pm-form-phone').focus();
          return;
        }
      }

      if (hotline) {
        const vHotline = moValidatePhone(hotline);
        if (!vHotline.valid) {
          moSetFieldError('pm-form-hotline', 'err-pm-hotline', 'Hotline sự cố không hợp lệ (bắt đầu bằng 0, gồm 10 hoặc 11 chữ số).');
          document.getElementById('pm-form-hotline').focus();
          return;
        }
      }

      if (pwd && pwd.length < 8) {
        moSetFieldError('pm-form-password', 'err-pm-password', 'Mật khẩu phải có tối thiểu 8 ký tự.');
        document.getElementById('pm-form-password').focus();
        return;
      }

      const payload = {
        employee_code: document.getElementById('pm-form-code').value.trim() || null,
        full_name: fullName,
        email: email,
        phone: phone || null,
        emergency_hotline: hotline || null,
        department: document.getElementById('pm-form-department').value,
        status: document.getElementById('pm-form-status').value,
        building_ids: getSelectedBuildingIds('pm-form-buildings')
      };

      if (pwd) payload.password = pwd;

      const url = (mode === 'create') ? '/api/v1/admin/users/property-managers' : `/api/v1/admin/users/property-managers/${id}`;
      const method = (mode === 'create') ? 'POST' : 'PUT';

      const btn = document.getElementById('btn-submit-staff-pm');
      const originalText = btn.innerHTML;
      btn.disabled = true;
      btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Đang lưu...';

      try {
        const res = await fetch(url, {
          method,
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'Authorization': 'Bearer ' + token
          },
          body: JSON.stringify(payload)
        });
        const json = await res.json();
        if (res.ok) {
          closeModal('staff-pm-modal');
          loadStaffPm(1);
          if (mode === 'create' && json.data?.plain_password) {
            showStaffCredentialSuccessModal({
              title: 'Khởi Tạo Nhân Sự BQL Thành Công',
              desc: `Tài khoản cho nhân sự Ban Quản Lý <strong>${escapeHtml(json.data.full_name || '')}</strong> đã được khởi tạo thành công. Email thông báo tài khoản và mật khẩu đã được tự động xếp vào hàng đợi gửi đi.`,
              name: json.data.full_name || '',
              email: json.data.email || '',
              password: json.data.plain_password
            });
          } else {
            alert(json.message || 'Lưu nhân sự BQL thành công!');
          }
        } else {
          if (json.errors) {
            if (json.errors.full_name) moSetFieldError('pm-form-fullname', 'err-pm-fullname', json.errors.full_name[0]);
            if (json.errors.email) moSetFieldError('pm-form-email', 'err-pm-email', json.errors.email[0]);
            if (json.errors.phone) moSetFieldError('pm-form-phone', 'err-pm-phone', json.errors.phone[0]);
            if (json.errors.emergency_hotline) moSetFieldError('pm-form-hotline', 'err-pm-hotline', json.errors.emergency_hotline[0]);
            if (json.errors.password) moSetFieldError('pm-form-password', 'err-pm-password', json.errors.password[0]);
          } else {
            alert('Lỗi: ' + (json.message || 'Thao tác không thành công.'));
          }
        }
      } catch (err) {
        console.error('Submit PM error:', err);
        alert('Lỗi kết nối máy chủ khi lưu BQL.');
      } finally {
        btn.disabled = false;
        btn.innerHTML = originalText;
      }
    }

    // API 84: Chi tiết PM
    async function viewStaffPm(id) {
      openModal('staff-pm-detail-modal');
      const body = document.getElementById('body-staff-pm-detail');
      body.innerHTML = '<div style="text-align:center; padding:30px;"><i class="fa-solid fa-spinner fa-spin"></i> Đang tải dữ liệu...</div>';
      const token = getSanctumToken();

      try {
        const res = await fetch(`/api/v1/admin/users/property-managers/${id}`, {
          headers: { 'Accept': 'application/json', 'Authorization': 'Bearer ' + token }
        });
        const json = await res.json();
        if (!res.ok) {
          body.innerHTML = `<div style="color:#ef4444; padding:20px; text-align:center;">${escapeHtml(json.message || 'Không thể xem chi tiết')}</div>`;
          return;
        }
        const data = json.data || {};
        const p = data.property_manager || data;
        const buildings = p.manager_buildings || p.buildings || [];

        body.innerHTML = `
          <div style="background:#f8fafc; border-radius:8px; padding:16px; border:1px solid #e2e8f0; margin-bottom:14px;">
            <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
              <span style="color:#64748b; font-size:12.5px;">Mã nhân viên BQL:</span>
              <strong style="font-family:monospace; color:#b45309;">${escapeHtml(p.employee_code || '---')}</strong>
            </div>
            <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
              <span style="color:#64748b; font-size:12.5px;">Họ và tên:</span>
              <strong style="color:#0f172a; font-size:14px;">${escapeHtml(p.full_name || p.name || '---')}</strong>
            </div>
            <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
              <span style="color:#64748b; font-size:12.5px;">Email đăng nhập:</span>
              <span style="font-family:monospace; color:#2563eb;">${escapeHtml(p.email)}</span>
            </div>
            <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
              <span style="color:#64748b; font-size:12.5px;">Hotline xử lý sự cố:</span>
              <span style="color:#dc2626; font-weight:700;"><i class="fa-solid fa-phone-volume"></i> ${escapeHtml(p.emergency_hotline || p.phone || 'Chưa có')}</span>
            </div>
            <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
              <span style="color:#64748b; font-size:12.5px;">Bộ phận phụ trách:</span>
              <span style="font-weight:600;">${escapeHtml(p.department || 'TECHNICAL')}</span>
            </div>
            <div style="display:flex; justify-content:space-between;">
              <span style="color:#64748b; font-size:12.5px;">Trạng thái:</span>
              <span class="badge-filament ${(p.status === 'ACTIVE') ? 'success' : 'danger'}">${escapeHtml(p.status || 'ACTIVE')}</span>
            </div>
          </div>

          <div style="margin-bottom:16px;">
            <h4 style="font-size:13px; font-weight:700; color:#334155; margin-bottom:8px;">Tòa nhà phân công phụ trách (${buildings.length}):</h4>
            <div style="display:flex; flex-wrap:wrap; gap:6px;">
              ${buildings.length > 0 
                ? buildings.map(b => `<span style="background:#fef3c7; color:#92400e; padding:4px 8px; border-radius:6px; font-size:12px;"><i class="fa-regular fa-building"></i> ${escapeHtml(b.name || 'Tòa nhà')}</span>`).join('')
                : '<span style="color:#94a3b8; font-size:12px;">Chưa phân công tòa nhà phụ trách cụ thể.</span>'}
            </div>
          </div>

          <div style="text-align:right;">
            <button class="btn-mo-primary" style="padding:6px 14px; font-size:13px;" onclick="closeModal('staff-pm-detail-modal')">Đóng cửa sổ</button>
          </div>
        `;
      } catch (e) {
        body.innerHTML = '<div style="color:#ef4444; padding:20px; text-align:center;">Lỗi kết nối khi tải hồ sơ BQL.</div>';
      }
    }

    // API 86: Xóa mềm PM
    async function deleteStaffPm(id, name) {
      if (!confirm(`Bạn có chắc chắn muốn xóa mềm nhân sự Ban Quản lý [${name}] khỏi hệ thống?`)) return;
      const token = getSanctumToken();
      try {
        const res = await fetch(`/api/v1/admin/users/property-managers/${id}`, {
          method: 'DELETE',
          headers: { 'Accept': 'application/json', 'Authorization': 'Bearer ' + token }
        });
        const json = await res.json();
        if (res.ok) {
          alert(json.message || 'Đã xóa mềm nhân sự BQL thành công.');
          loadStaffPm(1);
        } else {
          alert('Lỗi: ' + (json.message || 'Không thể xóa nhân sự.'));
        }
      } catch (err) {
        console.error('Delete PM error:', err);
      }
    }

    // =========================================================================
    // 5. NHÓM KẾ TOÁN (ACCOUNTANTS) — API 87 - 91
    // =========================================================================

    // API 87: Danh sách Kế toán
    async function loadStaffAccountants(page = 1, showLoading = true) {
      const tbody = document.getElementById('tbody-staff-accountants');
      if (!tbody) return;
      if (showLoading && (!tbody.children.length || tbody.innerText.includes('Đang tải'))) {
        tbody.innerHTML = '<tr><td colspan="7" style="text-align:center; padding: 25px; color:#94a3b8;"><i class="fa-solid fa-spinner fa-spin"></i> Đang tải danh sách Kế toán...</td></tr>';
      }

      try {
        const token = getSanctumToken();
        const kw = document.getElementById('filter-acc-keyword')?.value?.trim() || '';
        const adj = document.getElementById('filter-acc-adjustment')?.value || '';
        const st = document.getElementById('filter-acc-status')?.value || '';

        const url = new URL('/api/v1/admin/users/accountants', window.location.origin);
        url.searchParams.set('page', page);
        url.searchParams.set('per_page', 10);
        if (kw) url.searchParams.set('search', kw);
        if (adj !== '') url.searchParams.set('can_approve_adjustment', adj);
        if (st) url.searchParams.set('status', st);

        let accs = [];
        let meta = {};

        const res = await fetch(url.toString(), {
          headers: { 'Accept': 'application/json', 'Authorization': 'Bearer ' + token }
        });
        const json = await res.json();
        if (res.ok && json.data) {
          accs = Array.isArray(json.data) ? json.data : (json.data?.data || []);
          meta = json.meta || json.data?.meta || { total: accs.length, current_page: page, last_page: 1 };
        } else if (typeof SERVER_STAFF !== 'undefined' && SERVER_STAFF.accountants && SERVER_STAFF.accountants.length > 0) {
          accs = SERVER_STAFF.accountants;
          meta = { total: accs.length, current_page: 1, last_page: 1 };
        } else {
          tbody.innerHTML = `<tr><td colspan="7" style="text-align:center; padding: 25px; color:#ef4444;">${escapeHtml(json.message || 'Không thể tải danh sách Kế toán.')}</td></tr>`;
          return;
        }

        const countBadge = document.getElementById('badge-count-accountants');
        if (countBadge) countBadge.innerText = (meta.total !== undefined ? meta.total : accs.length);

        if (accs.length === 0) {
          tbody.innerHTML = '<tr><td colspan="7" style="text-align:center; padding: 25px; color:#94a3b8;">Chưa có dữ liệu nhân viên Kế toán nào.</td></tr>';
          return;
        }

        tbody.innerHTML = renderStaffAccountantsHtml(accs);
        renderStaffPagination('pagination-staff-accountants', meta, loadStaffAccountants);
      } catch (err) {
        console.error('Load Accountants error:', err);
        if (typeof SERVER_STAFF !== 'undefined' && SERVER_STAFF.accountants && SERVER_STAFF.accountants.length > 0) {
          const accs = SERVER_STAFF.accountants;
          document.getElementById('badge-count-accountants')?.innerText && (document.getElementById('badge-count-accountants').innerText = accs.length);
          tbody.innerHTML = renderStaffAccountantsHtml(accs);
        } else {
          tbody.innerHTML = '<tr><td colspan="7" style="text-align:center; padding: 25px; color:#ef4444;">Lỗi kết nối API danh sách Kế toán.</td></tr>';
        }
      }
    }

    // Mở modal tạo mới Kế toán
    async function openCreateAccountantModal() {
      document.getElementById('form-staff-accountant').reset();
      document.getElementById('acc-form-id').value = '';
      document.getElementById('acc-form-mode').value = 'create';
      document.getElementById('staff-accountant-modal-title').innerHTML = '<i class="fa-solid fa-user-plus"></i> Thêm Nhân Viên Kế Toán Mới';
      document.getElementById('acc-form-password-req').style.display = 'none';
      document.getElementById('acc-form-password-hint').style.display = 'block';

      moClearFieldError('acc-form-fullname', 'err-acc-fullname');
      moClearFieldError('acc-form-email', 'err-acc-email');
      moClearFieldError('acc-form-phone', 'err-acc-phone');
      moClearFieldError('acc-form-password', 'err-acc-password');

      await fetchNextEmployeeCode('accountant', 'acc-form-code');
      await fetchStaffBuildingsList();
      renderBuildingCheckboxes('acc-form-buildings', []);
      openModal('staff-accountant-modal');
    }

    // Mở modal sửa Kế toán (API 89 + 90)
    async function openEditAccountantModal(id) {
      const token = getSanctumToken();
      moClearFieldError('acc-form-fullname', 'err-acc-fullname');
      moClearFieldError('acc-form-email', 'err-acc-email');
      moClearFieldError('acc-form-phone', 'err-acc-phone');
      moClearFieldError('acc-form-password', 'err-acc-password');

      try {
        const res = await fetch(`/api/v1/admin/users/accountants/${id}`, {
          headers: { 'Accept': 'application/json', 'Authorization': 'Bearer ' + token }
        });
        const json = await res.json();
        if (!res.ok) {
          alert('Không thể tải chi tiết Kế toán: ' + (json.message || ''));
          return;
        }
        const a = json.data?.accountant || json.data;
        document.getElementById('acc-form-id').value = a.id;
        document.getElementById('acc-form-mode').value = 'edit';
        document.getElementById('acc-form-code').value = a.employee_code || '';
        document.getElementById('acc-form-fullname').value = a.full_name || a.name || '';
        document.getElementById('acc-form-email').value = a.email || '';
        document.getElementById('acc-form-phone').value = a.phone || '';
        document.getElementById('acc-form-can-adjust').checked = !!a.can_approve_adjustment;
        document.getElementById('acc-form-status').value = a.status || 'ACTIVE';

        document.getElementById('acc-form-password').value = '';
        document.getElementById('acc-form-password-req').style.display = 'none';
        document.getElementById('acc-form-password-hint').style.display = 'block';

        await fetchStaffBuildingsList();
        const assignedIds = (a.account_buildings || a.buildings || []).map(b => b.id || b.building_id);
        renderBuildingCheckboxes('acc-form-buildings', assignedIds);

        document.getElementById('staff-accountant-modal-title').innerHTML = '<i class="fa-solid fa-user-pen"></i> Cập Nhật Kế Toán';
        openModal('staff-accountant-modal');
      } catch (err) {
        console.error('Fetch Accountant detail error:', err);
      }
    }

    // Submit form Kế toán (API 88: POST / API 90: PUT)
    async function handleStaffAccountantSubmit(e) {
      e.preventDefault();
      const mode = document.getElementById('acc-form-mode').value;
      const id = document.getElementById('acc-form-id').value;
      const token = getSanctumToken();

      moClearFieldError('acc-form-fullname', 'err-acc-fullname');
      moClearFieldError('acc-form-email', 'err-acc-email');
      moClearFieldError('acc-form-phone', 'err-acc-phone');
      moClearFieldError('acc-form-password', 'err-acc-password');

      const fullName = document.getElementById('acc-form-fullname').value.trim();
      const email = document.getElementById('acc-form-email').value.trim();
      const phone = document.getElementById('acc-form-phone').value.trim();
      const pwd = document.getElementById('acc-form-password').value;

      // Validation 0ms
      const vName = moValidateFullName(fullName, 2);
      if (!vName.valid) {
        moSetFieldError('acc-form-fullname', 'err-acc-fullname', vName.message);
        document.getElementById('acc-form-fullname').focus();
        return;
      }

      const vEmail = moValidateEmail(email);
      if (!vEmail.valid) {
        moSetFieldError('acc-form-email', 'err-acc-email', vEmail.message);
        document.getElementById('acc-form-email').focus();
        return;
      }

      if (phone) {
        const vPhone = moValidatePhone(phone);
        if (!vPhone.valid) {
          moSetFieldError('acc-form-phone', 'err-acc-phone', vPhone.message);
          document.getElementById('acc-form-phone').focus();
          return;
        }
      }

      if (pwd && pwd.length < 8) {
        moSetFieldError('acc-form-password', 'err-acc-password', 'Mật khẩu phải có tối thiểu 8 ký tự.');
        document.getElementById('acc-form-password').focus();
        return;
      }

      const payload = {
        employee_code: document.getElementById('acc-form-code').value.trim() || null,
        full_name: fullName,
        email: email,
        phone: phone || null,
        can_approve_adjustment: document.getElementById('acc-form-can-adjust').checked,
        status: document.getElementById('acc-form-status').value,
        building_ids: getSelectedBuildingIds('acc-form-buildings')
      };

      if (pwd) payload.password = pwd;

      const url = (mode === 'create') ? '/api/v1/admin/users/accountants' : `/api/v1/admin/users/accountants/${id}`;
      const method = (mode === 'create') ? 'POST' : 'PUT';

      const btn = document.getElementById('btn-submit-staff-accountant');
      const originalText = btn.innerHTML;
      btn.disabled = true;
      btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Đang lưu...';

      try {
        const res = await fetch(url, {
          method,
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'Authorization': 'Bearer ' + token
          },
          body: JSON.stringify(payload)
        });
        const json = await res.json();
        if (res.ok) {
          closeModal('staff-accountant-modal');
          loadStaffAccountants(1);
          if (mode === 'create' && json.data?.plain_password) {
            showStaffCredentialSuccessModal({
              title: 'Khởi Tạo Kế Toán Thành Công',
              desc: `Tài khoản cho nhân viên Kế toán <strong>${escapeHtml(json.data.full_name || '')}</strong> đã được khởi tạo thành công. Email thông báo tài khoản và mật khẩu đã được tự động xếp vào hàng đợi gửi đi.`,
              name: json.data.full_name || '',
              email: json.data.email || '',
              password: json.data.plain_password
            });
          } else {
            alert(json.message || 'Lưu nhân viên Kế toán thành công!');
          }
        } else {
          if (json.errors) {
            if (json.errors.full_name) moSetFieldError('acc-form-fullname', 'err-acc-fullname', json.errors.full_name[0]);
            if (json.errors.email) moSetFieldError('acc-form-email', 'err-acc-email', json.errors.email[0]);
            if (json.errors.phone) moSetFieldError('acc-form-phone', 'err-acc-phone', json.errors.phone[0]);
            if (json.errors.password) moSetFieldError('acc-form-password', 'err-acc-password', json.errors.password[0]);
          } else {
            alert('Lỗi: ' + (json.message || 'Thao tác không thành công.'));
          }
        }
      } catch (err) {
        console.error('Submit Accountant error:', err);
        alert('Lỗi kết nối máy chủ khi lưu Kế toán.');
      } finally {
        btn.disabled = false;
        btn.innerHTML = originalText;
      }
    }

    // API 89: Chi tiết Kế toán
    async function viewStaffAccountant(id) {
      openModal('staff-accountant-detail-modal');
      const body = document.getElementById('body-staff-accountant-detail');
      body.innerHTML = '<div style="text-align:center; padding:30px;"><i class="fa-solid fa-spinner fa-spin"></i> Đang tải dữ liệu...</div>';
      const token = getSanctumToken();

      try {
        const res = await fetch(`/api/v1/admin/users/accountants/${id}`, {
          headers: { 'Accept': 'application/json', 'Authorization': 'Bearer ' + token }
        });
        const json = await res.json();
        if (!res.ok) {
          body.innerHTML = `<div style="color:#ef4444; padding:20px; text-align:center;">${escapeHtml(json.message || 'Không thể xem chi tiết')}</div>`;
          return;
        }
        const data = json.data || {};
        const a = data.accountant || data;
        const buildings = a.account_buildings || a.buildings || [];

        body.innerHTML = `
          <div style="background:#f8fafc; border-radius:8px; padding:16px; border:1px solid #e2e8f0; margin-bottom:14px;">
            <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
              <span style="color:#64748b; font-size:12.5px;">Mã nhân viên:</span>
              <strong style="font-family:monospace; color:#065f46;">${escapeHtml(a.employee_code || '---')}</strong>
            </div>
            <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
              <span style="color:#64748b; font-size:12.5px;">Họ và tên:</span>
              <strong style="color:#0f172a; font-size:14px;">${escapeHtml(a.full_name || a.name || '---')}</strong>
            </div>
            <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
              <span style="color:#64748b; font-size:12.5px;">Email đăng nhập:</span>
              <span style="font-family:monospace; color:#2563eb;">${escapeHtml(a.email)}</span>
            </div>
            <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
              <span style="color:#64748b; font-size:12.5px;">Số điện thoại:</span>
              <span>${escapeHtml(a.phone || 'Chưa cập nhật')}</span>
            </div>
            <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
              <span style="color:#64748b; font-size:12.5px;">Quyền duyệt điều chỉnh cước:</span>
              <span>${a.can_approve_adjustment ? '<span style="color:#15803d; font-weight:700;"><i class="fa-solid fa-circle-check"></i> Được phép duyệt cước</span>' : '<span style="color:#64748b;">Chỉ xem & lập phiếu</span>'}</span>
            </div>
            <div style="display:flex; justify-content:space-between;">
              <span style="color:#64748b; font-size:12.5px;">Trạng thái:</span>
              <span class="badge-filament ${(a.status === 'ACTIVE') ? 'success' : 'danger'}">${escapeHtml(a.status || 'ACTIVE')}</span>
            </div>
          </div>

          <div style="margin-bottom:16px;">
            <h4 style="font-size:13px; font-weight:700; color:#334155; margin-bottom:8px;">Tòa nhà theo dõi công nợ (${buildings.length}):</h4>
            <div style="display:flex; flex-wrap:wrap; gap:6px;">
              ${buildings.length > 0 
                ? buildings.map(b => `<span style="background:#ecfdf5; color:#065f46; padding:4px 8px; border-radius:6px; font-size:12px;"><i class="fa-regular fa-building"></i> ${escapeHtml(b.name || 'Tòa nhà')}</span>`).join('')
                : '<span style="color:#94a3b8; font-size:12px;">Theo dõi công nợ toàn hệ thống.</span>'}
            </div>
          </div>

          <div style="text-align:right;">
            <button class="btn-mo-primary" style="padding:6px 14px; font-size:13px;" onclick="closeModal('staff-accountant-detail-modal')">Đóng cửa sổ</button>
          </div>
        `;
      } catch (e) {
        body.innerHTML = '<div style="color:#ef4444; padding:20px; text-align:center;">Lỗi kết nối khi tải hồ sơ Kế toán.</div>';
      }
    }

    // API 91: Xóa mềm Kế toán
    async function deleteStaffAccountant(id, name) {
      if (!confirm(`Bạn có chắc chắn muốn xóa mềm nhân sự Kế toán [${name}] khỏi hệ thống?`)) return;
      const token = getSanctumToken();
      try {
        const res = await fetch(`/api/v1/admin/users/accountants/${id}`, {
          method: 'DELETE',
          headers: { 'Accept': 'application/json', 'Authorization': 'Bearer ' + token }
        });
        const json = await res.json();
        if (res.ok) {
          alert(json.message || 'Đã xóa mềm nhân sự Kế toán thành công.');
          loadStaffAccountants(1);
        } else {
          alert('Lỗi: ' + (json.message || 'Không thể xóa nhân sự.'));
        }
      } catch (err) {
        console.error('Delete Accountant error:', err);
      }
    }

    // Phân trang dùng chung cho các sub-tab Staff
    function renderStaffPagination(containerId, meta, loadFnName) {
      const container = document.getElementById(containerId);
      if (!container) return;
      const cur = meta.current_page || 1;
      const last = meta.last_page || 1;
      const total = (meta.total !== undefined) ? meta.total : (Array.isArray(meta) ? meta.length : (meta.data ? meta.data.length : 0));

      container.innerHTML = `
        <span style="font-size:12px; color:#64748b;">Trang <strong>${cur}</strong> / <strong>${last}</strong> (Tổng số: ${total})</span>
        <div style="display:flex; gap:6px;">
          <button class="btn-mo-outline" style="padding:4px 10px; font-size:12px;" ${cur <= 1 ? 'disabled' : ''} onclick="${loadFnName.name}(${cur - 1})">
            <i class="fa-solid fa-chevron-left"></i> Trước
          </button>
          <button class="btn-mo-outline" style="padding:4px 10px; font-size:12px;" ${cur >= last ? 'disabled' : ''} onclick="${loadFnName.name}(${cur + 1})">
            Sau <i class="fa-solid fa-chevron-right"></i>
          </button>
        </div>
      `;
    }
    // Sinh mật khẩu ngẫu nhiên an toàn vào input
    function generateRandomPasswordForInput(inputId, prefix = 'Bms@') {
      const upper = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
      const lower = 'abcdefghjkmnpqrstuvwxyz';
      const digits = '23456789';
      const specials = '#!$%*';
      const all = upper + lower + digits + specials;
      
      let pass = [
        upper[Math.floor(Math.random() * upper.length)],
        lower[Math.floor(Math.random() * lower.length)],
        digits[Math.floor(Math.random() * digits.length)],
        specials[Math.floor(Math.random() * specials.length)]
      ];
      while (pass.length < 6) {
        pass.push(all[Math.floor(Math.random() * all.length)]);
      }
      pass.sort(() => 0.5 - Math.random());
      const generated = prefix + pass.join('');

      const input = document.getElementById(inputId);
      if (input) {
        input.value = generated;
        input.type = 'text'; // Tạm thời hiện rõ để người tạo có thể thấy
        const errSlot = inputId.replace('-form-', '-');
        moClearFieldError(inputId, 'err-' + errSlot);
      }
    }

    // Sao chép mật khẩu One-Time vào Clipboard
    function copyStaffCredentialPassword() {
      const passCode = document.getElementById('staff-credential-password');
      if (passCode && passCode.innerText) {
        navigator.clipboard.writeText(passCode.innerText).then(() => {
          alert('Đã sao chép mật khẩu vào bộ nhớ tạm: ' + passCode.innerText);
        }).catch(() => {
          prompt('Sao chép mật khẩu thủ công:', passCode.innerText);
        });
      }
    }

    // Mở popup hiển thị mật khẩu khởi tạo / cấp lại
    function showStaffCredentialSuccessModal(opts) {
      if (opts.title) document.getElementById('staff-credential-modal-title').innerHTML = '<i class="fa-solid fa-circle-check"></i> ' + opts.title;
      if (opts.desc) document.getElementById('staff-credential-modal-desc').innerHTML = opts.desc;
      if (opts.name) document.getElementById('staff-credential-name').innerText = opts.name;
      if (opts.email) document.getElementById('staff-credential-email').innerText = opts.email;
      if (opts.password) document.getElementById('staff-credential-password').innerText = opts.password;
      openModal('staff-credential-success-modal');
    }

    // Cấp lại mật khẩu cho nhân sự (Admin-Assisted Password Reset)
    async function resetStaffPassword(role, id, name) {
      if (!confirm(`Bạn có chắc chắn muốn cấp lại mật khẩu cho nhân sự [${name}]?\n\nHệ thống sẽ tự động tạo mật khẩu ngẫu nhiên mới, thu hồi toàn bộ phiên đăng nhập cũ và gửi email thông báo mật khẩu mới cho nhân sự.`)) {
        return;
      }

      const token = getSanctumToken();
      let url = '';
      if (role === 'admin') url = `/api/v1/admin/users/admins/${id}/reset-password`;
      else if (role === 'sales') url = `/api/v1/admin/users/sales/${id}/reset-password`;
      else if (role === 'pm') url = `/api/v1/admin/users/property-managers/${id}/reset-password`;
      else if (role === 'accountant') url = `/api/v1/admin/users/accountants/${id}/reset-password`;

      try {
        const res = await fetch(url, {
          method: 'POST',
          headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'Authorization': 'Bearer ' + token
          }
        });
        const json = await res.json();
        if (!res.ok) {
          alert('Cấp lại mật khẩu thất bại: ' + (json.message || 'Lỗi không xác định.'));
          return;
        }

        const newPass = json.data?.new_password || '';
        showStaffCredentialSuccessModal({
          title: 'Cấp Lại Mật Khẩu Thành Công',
          desc: `Đã cấp lại mật khẩu mới cho nhân sự <strong>${escapeHtml(name)}</strong>. Email thông báo mật khẩu mới đã được tự động xếp vào hàng đợi gửi đi.`,
          name: name,
          email: json.data?.email || '',
          password: newPass
        });
      } catch (e) {
        console.error('Reset staff password error:', e);
        alert('Lỗi kết nối máy chủ khi cấp lại mật khẩu.');
      }
    }

    // Dữ liệu nhân sự sẽ được nạp theo cơ chế lazy load khi người dùng mở tab Nhân Sự trong Admin Panel
    function resetStaffAdminFilters() {
      const kw = document.getElementById('filter-admin-keyword');
      if (kw) kw.value = '';
      const clrBtn = document.getElementById('btn-clear-filter-admin');
      if (clrBtn) clrBtn.style.display = 'none';
      const st = document.getElementById('filter-admin-status');
      if (st) st.value = '';
      loadStaffAdmins(1);
    }

    function resetStaffSaleFilters() {
      const kw = document.getElementById('filter-sale-keyword');
      if (kw) kw.value = '';
      const clrBtn = document.getElementById('btn-clear-filter-sale');
      if (clrBtn) clrBtn.style.display = 'none';
      const st = document.getElementById('filter-sale-status');
      if (st) st.value = '';
      loadStaffSales(1);
    }

    function resetStaffPmFilters() {
      const kw = document.getElementById('filter-pm-keyword');
      if (kw) kw.value = '';
      const clrBtn = document.getElementById('btn-clear-filter-pm');
      if (clrBtn) clrBtn.style.display = 'none';
      const dep = document.getElementById('filter-pm-department');
      if (dep) dep.value = '';
      const st = document.getElementById('filter-pm-status');
      if (st) st.value = '';
      loadStaffPm(1);
    }

    function resetStaffAccFilters() {
      const kw = document.getElementById('filter-acc-keyword');
      if (kw) kw.value = '';
      const clrBtn = document.getElementById('btn-clear-filter-acc');
      if (clrBtn) clrBtn.style.display = 'none';
      const adj = document.getElementById('filter-acc-adjustment');
      if (adj) adj.value = '';
      const st = document.getElementById('filter-acc-status');
      if (st) st.value = '';
      loadStaffAccountants(1);
    }

    window.resetStaffAdminFilters = resetStaffAdminFilters;
    window.resetStaffSaleFilters = resetStaffSaleFilters;
    window.resetStaffPmFilters = resetStaffPmFilters;
    window.resetStaffAccFilters = resetStaffAccFilters;
  </script>
