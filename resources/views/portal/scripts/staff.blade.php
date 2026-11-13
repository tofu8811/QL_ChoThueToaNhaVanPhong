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
          ? buildings.slice(0, 2).map(b => `<span style="background:#e0f2fe; color:#0369a1; padding:2px 6px; border-radius:4px; font-size:11px; margin-right:4px;">${escapeHtml(b.name || 'Tòa nhà')}</span>`).join('') + (buildings.length > 2 ? `<small style="color:#64748b;">+${buildings.length - 2}</small>` : '')
          : '<span style="color:#94a3b8; font-size:11.5px;">Toàn hệ thống</span>';

        const statusClass = (s.status === 'ACTIVE') ? 'success' : 'danger';
        const statusText = (s.status === 'ACTIVE') ? 'Hoạt động' : 'Tạm khóa';

        return `
          <tr>
            <td><span style="font-family:monospace; font-weight:700; color:#0f3361;">${escapeHtml(s.employee_code || 'SL-' + s.id?.substring(0, 4))}</span></td>
            <td><strong>${escapeHtml(s.full_name || s.name || '---')}</strong></td>
            <td>
              <div style="font-size:12.5px; color:#2563eb;">${escapeHtml(s.email)}</div>
              <div style="font-size:11.5px; color:#64748b;">${escapeHtml(s.phone || '---')}</div>
            </td>
            <td>${bldHtml}</td>
            <td>
              <span style="font-weight:700; color:#0f172a;">${s.active_leads_count || 0}</span> / <span style="color:#64748b;">${s.max_concurrent_leads || 10} leads</span>
            </td>
            <td><span style="color:#ea580c; font-weight:700;">${s.commission_rate ? s.commission_rate + '%' : '---'}</span></td>
            <td><span class="badge-filament ${statusClass}">${statusText}</span></td>
            <td style="text-align: right; white-space: nowrap;">
              <button class="btn-mo-outline" style="padding:4px 8px; font-size:12px;" onclick="viewStaffSale('${s.id}')" title="Xem chi tiết & KPI">
                <i class="fa-regular fa-eye"></i>
              </button>
              <button class="btn-mo-outline" style="padding:4px 8px; font-size:12px;" onclick="openEditSaleModal('${s.id}')" title="Chỉnh sửa">
                <i class="fa-regular fa-pen-to-square"></i>
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
          ? buildings.slice(0, 2).map(b => `<span style="background:#fef3c7; color:#92400e; padding:2px 6px; border-radius:4px; font-size:11px; margin-right:4px;">${escapeHtml(b.name || 'Tòa nhà')}</span>`).join('') + (buildings.length > 2 ? `<small style="color:#64748b;">+${buildings.length - 2}</small>` : '')
          : '<span style="color:#94a3b8; font-size:11.5px;">Chưa phân công</span>';

        const statusClass = (p.status === 'ACTIVE') ? 'success' : 'danger';
        const deptText = deptLabels[p.department] || p.department || 'Vận hành';

        return `
          <tr>
            <td><span style="font-family:monospace; font-weight:700; color:#b45309;">${escapeHtml(p.employee_code || 'PM-' + p.id?.substring(0, 4))}</span></td>
            <td>
              <strong>${escapeHtml(p.full_name || p.name || '---')}</strong>
              <div style="font-size:11.5px; color:#64748b;">${escapeHtml(p.email)}</div>
            </td>
            <td>
              <span style="color:#dc2626; font-weight:700;"><i class="fa-solid fa-phone-volume"></i> ${escapeHtml(p.emergency_hotline || p.phone || '---')}</span>
            </td>
            <td><span style="background:#f1f5f9; color:#334155; padding:3px 8px; border-radius:6px; font-size:11.5px; font-weight:600;">${deptText}</span></td>
            <td>${bldHtml}</td>
            <td><span class="badge-filament ${statusClass}">${escapeHtml(p.status || 'ACTIVE')}</span></td>
            <td style="text-align: right; white-space: nowrap;">
              <button class="btn-mo-outline" style="padding:4px 8px; font-size:12px;" onclick="viewStaffPm('${p.id}')" title="Xem chi tiết">
                <i class="fa-regular fa-eye"></i>
              </button>
              <button class="btn-mo-outline" style="padding:4px 8px; font-size:12px;" onclick="openEditPmModal('${p.id}')" title="Chỉnh sửa">
                <i class="fa-regular fa-pen-to-square"></i>
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
          ? buildings.slice(0, 2).map(b => `<span style="background:#ecfdf5; color:#065f46; padding:2px 6px; border-radius:4px; font-size:11px; margin-right:4px;">${escapeHtml(b.name || 'Tòa nhà')}</span>`).join('') + (buildings.length > 2 ? `<small style="color:#64748b;">+${buildings.length - 2}</small>` : '')
          : '<span style="color:#94a3b8; font-size:11.5px;">Toàn hệ thống</span>';

        const canAdjustHtml = a.can_approve_adjustment
          ? '<span style="background:#dcfce7; color:#15803d; font-size:11.5px; font-weight:700; padding:2px 8px; border-radius:9999px;"><i class="fa-solid fa-check"></i> Được phép</span>'
          : '<span style="color:#94a3b8; font-size:11.5px;">Không</span>';

        const statusClass = (a.status === 'ACTIVE') ? 'success' : 'danger';

        return `
          <tr>
            <td><span style="font-family:monospace; font-weight:700; color:#065f46;">${escapeHtml(a.employee_code || 'ACC-' + a.id?.substring(0, 4))}</span></td>
            <td><strong>${escapeHtml(a.full_name || a.name || '---')}</strong></td>
            <td>
              <div style="font-size:12.5px; color:#2563eb;">${escapeHtml(a.email)}</div>
              <div style="font-size:11.5px; color:#64748b;">${escapeHtml(a.phone || '---')}</div>
            </td>
            <td>${canAdjustHtml}</td>
            <td>${bldHtml}</td>
            <td><span class="badge-filament ${statusClass}">${escapeHtml(a.status || 'ACTIVE')}</span></td>
            <td style="text-align: right; white-space: nowrap;">
              <button class="btn-mo-outline" style="padding:4px 8px; font-size:12px;" onclick="viewStaffAccountant('${a.id}')" title="Xem chi tiết">
                <i class="fa-regular fa-eye"></i>
              </button>
              <button class="btn-mo-outline" style="padding:4px 8px; font-size:12px;" onclick="openEditAccountantModal('${a.id}')" title="Chỉnh sửa">
                <i class="fa-regular fa-pen-to-square"></i>
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

    // Khởi tạo số lượng đếm badge cho 4 nhóm nhân sự từ dữ liệu server
    function initStaffBadges() {
      const bAdmins = document.getElementById('badge-count-admins');
      const bSales = document.getElementById('badge-count-sales');
      const bPm = document.getElementById('badge-count-pm');
      const bAcc = document.getElementById('badge-count-accountants');
      const bAss = document.getElementById('badge-count-assignments');

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

        tbody.innerHTML = admins.map(a => {
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
                <button class="btn-mo-outline" style="padding:4px 8px; font-size:12px; color:#dc2626; border-color:#fca5a5;" onclick="deleteStaffAdmin('${a.id}', '${escapeHtml(a.full_name || a.name)}')" title="Xóa mềm">
                  <i class="fa-regular fa-trash-can"></i>
                </button>
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

    // Mở modal tạo mới Admin
    function openCreateAdminModal() {
      document.getElementById('form-staff-admin').reset();
      document.getElementById('admin-form-id').value = '';
      document.getElementById('admin-form-mode').value = 'create';
      document.getElementById('staff-admin-modal-title').innerHTML = '<i class="fa-solid fa-user-plus"></i> Thêm Quản Trị Viên Mới';
      document.getElementById('admin-form-password').required = true;
      document.getElementById('admin-form-password-req').style.display = 'inline';
      document.getElementById('admin-form-password-hint').style.display = 'none';
      openModal('staff-admin-modal');
    }

    // Mở modal sửa Admin (API 74 + 75)
    async function openEditAdminModal(id) {
      const token = getSanctumToken();
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
        document.getElementById('admin-form-issuper').checked = !!a.is_super_admin;
        document.getElementById('admin-form-status').value = a.status || 'ACTIVE';

        // Password optional khi sửa
        document.getElementById('admin-form-password').value = '';
        document.getElementById('admin-form-password').required = false;
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

      const payload = {
        full_name: document.getElementById('admin-form-fullname').value.trim(),
        email: document.getElementById('admin-form-email').value.trim(),
        phone: document.getElementById('admin-form-phone').value.trim() || null,
        is_super_admin: document.getElementById('admin-form-issuper').checked,
        status: document.getElementById('admin-form-status').value
      };

      const pwd = document.getElementById('admin-form-password').value;
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
          alert(json.message || 'Lưu thông tin Quản trị viên thành công!');
          closeModal('staff-admin-modal');
          loadStaffAdmins(1);
        } else {
          alert('Lỗi: ' + (json.message || JSON.stringify(json.errors || 'Thất bại')));
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

        tbody.innerHTML = sales.map(s => {
          const buildings = s.sale_buildings || s.buildings || [];
          const bldHtml = buildings.length > 0
            ? buildings.slice(0, 2).map(b => `<span style="background:#e0f2fe; color:#0369a1; padding:2px 6px; border-radius:4px; font-size:11px; margin-right:4px;">${escapeHtml(b.name || 'Tòa nhà')}</span>`).join('') + (buildings.length > 2 ? `<small style="color:#64748b;">+${buildings.length - 2}</small>` : '')
            : '<span style="color:#94a3b8; font-size:11.5px;">Toàn hệ thống</span>';

          const statusClass = (s.status === 'ACTIVE') ? 'success' : 'danger';
          const statusText = (s.status === 'ACTIVE') ? 'Hoạt động' : 'Tạm khóa';

          return `
            <tr>
              <td><span style="font-family:monospace; font-weight:700; color:#0f3361;">${escapeHtml(s.employee_code || 'SL-' + s.id?.substring(0, 4))}</span></td>
              <td><strong>${escapeHtml(s.full_name || s.name || '---')}</strong></td>
              <td>
                <div style="font-size:12.5px; color:#2563eb;">${escapeHtml(s.email)}</div>
                <div style="font-size:11.5px; color:#64748b;">${escapeHtml(s.phone || '---')}</div>
              </td>
              <td>${bldHtml}</td>
              <td>
                <span style="font-weight:700; color:#0f172a;">${s.active_leads_count || 0}</span> / <span style="color:#64748b;">${s.max_concurrent_leads || 10} leads</span>
              </td>
              <td><span style="color:#ea580c; font-weight:700;">${s.commission_rate ? s.commission_rate + '%' : '---'}</span></td>
              <td><span class="badge-filament ${statusClass}">${statusText}</span></td>
              <td style="text-align: right; white-space: nowrap;">
                <button class="btn-mo-outline" style="padding:4px 8px; font-size:12px;" onclick="viewStaffSale('${s.id}')" title="Xem chi tiết & KPI">
                  <i class="fa-regular fa-eye"></i>
                </button>
                <button class="btn-mo-outline" style="padding:4px 8px; font-size:12px;" onclick="openEditSaleModal('${s.id}')" title="Chỉnh sửa">
                  <i class="fa-regular fa-pen-to-square"></i>
                </button>
                <button class="btn-mo-outline" style="padding:4px 8px; font-size:12px; color:#dc2626; border-color:#fca5a5;" onclick="deleteStaffSale('${s.id}', '${escapeHtml(s.full_name || s.name)}')" title="Xóa mềm">
                  <i class="fa-regular fa-trash-can"></i>
                </button>
              </td>
            </tr>
          `;
        }).join('');

        renderStaffPagination('pagination-staff-sales', meta, loadStaffSales);
      } catch (err) {
        console.error('Load sales error:', err);
        if (typeof SERVER_STAFF !== 'undefined' && SERVER_STAFF.sales && SERVER_STAFF.sales.length > 0) {
          const sales = SERVER_STAFF.sales;
          document.getElementById('badge-count-sales')?.innerText && (document.getElementById('badge-count-sales').innerText = sales.length);
          tbody.innerHTML = sales.map(s => `
            <tr>
              <td><span style="font-family:monospace; font-weight:700; color:#0f3361;">${escapeHtml(s.employee_code || 'SL-' + s.id?.substring(0, 4))}</span></td>
              <td><strong>${escapeHtml(s.full_name || s.name || '---')}</strong></td>
              <td><div style="font-size:12.5px; color:#2563eb;">${escapeHtml(s.email)}</div></td>
              <td><span style="color:#94a3b8; font-size:11.5px;">Toàn hệ thống</span></td>
              <td><span style="font-weight:700;">${s.active_leads_count || 0}</span> / 10 leads</td>
              <td><span style="color:#ea580c; font-weight:700;">${s.commission_rate || 5}%</span></td>
              <td><span class="badge-filament success">Hoạt động</span></td>
              <td style="text-align: right;"><button class="btn-mo-outline" style="padding:4px 8px; font-size:12px;" onclick="viewStaffSale('${s.id}')"><i class="fa-regular fa-eye"></i></button></td>
            </tr>
          `).join('');
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
      document.getElementById('sale-form-password').required = true;
      document.getElementById('sale-form-password-req').style.display = 'inline';
      document.getElementById('sale-form-password-hint').style.display = 'none';

      await fetchStaffBuildingsList();
      renderBuildingCheckboxes('sale-form-buildings', []);
      openModal('staff-sale-modal');
    }

    // Mở modal sửa Sales (API 79 + 80)
    async function openEditSaleModal(id) {
      const token = getSanctumToken();
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
        document.getElementById('sale-form-password').required = false;
        document.getElementById('sale-form-password-req').style.display = 'none';
        document.getElementById('sale-form-password-hint').style.display = 'block';

        await fetchStaffBuildingsList();
        const assignedIds = (s.sale_buildings || s.buildings || []).map(b => b.id || b.building_id);
        renderBuildingCheckboxes('sale-form-buildings', assignedIds);

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

      const payload = {
        employee_code: document.getElementById('sale-form-code').value.trim() || null,
        full_name: document.getElementById('sale-form-fullname').value.trim(),
        email: document.getElementById('sale-form-email').value.trim(),
        phone: document.getElementById('sale-form-phone').value.trim() || null,
        max_concurrent_leads: parseInt(document.getElementById('sale-form-max-leads').value) || 10,
        commission_rate: parseFloat(document.getElementById('sale-form-commission').value) || null,
        status: document.getElementById('sale-form-status').value,
        building_ids: getSelectedBuildingIds('sale-form-buildings')
      };

      const pwd = document.getElementById('sale-form-password').value;
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
          alert(json.message || 'Lưu nhân viên Sales thành công!');
          closeModal('staff-sale-modal');
          loadStaffSales(1);
        } else {
          alert('Lỗi: ' + (json.message || JSON.stringify(json.errors || 'Thất bại')));
        }
      } catch (err) {
        console.error('Submit sale error:', err);
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

        const deptLabels = {
          TECHNICAL: 'Kỹ thuật & Cơ điện',
          SECURITY: 'An ninh & Bảo vệ',
          CLEANING: 'Vệ sinh & Cảnh quan',
          CUSTOMER_CARE: 'CSKH Tòa Nhà'
        };

        tbody.innerHTML = pms.map(p => {
          const buildings = p.manager_buildings || p.buildings || [];
          const bldHtml = buildings.length > 0
            ? buildings.slice(0, 2).map(b => `<span style="background:#fef3c7; color:#92400e; padding:2px 6px; border-radius:4px; font-size:11px; margin-right:4px;">${escapeHtml(b.name || 'Tòa nhà')}</span>`).join('') + (buildings.length > 2 ? `<small style="color:#64748b;">+${buildings.length - 2}</small>` : '')
            : '<span style="color:#94a3b8; font-size:11.5px;">Chưa phân công</span>';

          const statusClass = (p.status === 'ACTIVE') ? 'success' : 'danger';
          const deptText = deptLabels[p.department] || p.department || 'Vận hành';

          return `
            <tr>
              <td><span style="font-family:monospace; font-weight:700; color:#b45309;">${escapeHtml(p.employee_code || 'PM-' + p.id?.substring(0, 4))}</span></td>
              <td>
                <strong>${escapeHtml(p.full_name || p.name || '---')}</strong>
                <div style="font-size:11.5px; color:#64748b;">${escapeHtml(p.email)}</div>
              </td>
              <td>
                <span style="color:#dc2626; font-weight:700;"><i class="fa-solid fa-phone-volume"></i> ${escapeHtml(p.emergency_hotline || p.phone || '---')}</span>
              </td>
              <td><span style="background:#f1f5f9; color:#334155; padding:3px 8px; border-radius:6px; font-size:11.5px; font-weight:600;">${deptText}</span></td>
              <td>${bldHtml}</td>
              <td><span class="badge-filament ${statusClass}">${escapeHtml(p.status || 'ACTIVE')}</span></td>
              <td style="text-align: right; white-space: nowrap;">
                <button class="btn-mo-outline" style="padding:4px 8px; font-size:12px;" onclick="viewStaffPm('${p.id}')" title="Xem chi tiết">
                  <i class="fa-regular fa-eye"></i>
                </button>
                <button class="btn-mo-outline" style="padding:4px 8px; font-size:12px;" onclick="openEditPmModal('${p.id}')" title="Chỉnh sửa">
                  <i class="fa-regular fa-pen-to-square"></i>
                </button>
                <button class="btn-mo-outline" style="padding:4px 8px; font-size:12px; color:#dc2626; border-color:#fca5a5;" onclick="deleteStaffPm('${p.id}', '${escapeHtml(p.full_name || p.name)}')" title="Xóa mềm">
                  <i class="fa-regular fa-trash-can"></i>
                </button>
              </td>
            </tr>
          `;
        }).join('');

        renderStaffPagination('pagination-staff-pm', meta, loadStaffPm);
      } catch (err) {
        console.error('Load PM error:', err);
        if (typeof SERVER_STAFF !== 'undefined' && SERVER_STAFF.pm && SERVER_STAFF.pm.length > 0) {
          const pms = SERVER_STAFF.pm;
          document.getElementById('badge-count-pm')?.innerText && (document.getElementById('badge-count-pm').innerText = pms.length);
          tbody.innerHTML = pms.map(p => `
            <tr>
              <td><span style="font-family:monospace; font-weight:700; color:#b45309;">${escapeHtml(p.employee_code || 'PM-' + p.id?.substring(0, 4))}</span></td>
              <td><strong>${escapeHtml(p.full_name || p.name || '---')}</strong><div style="font-size:11.5px; color:#64748b;">${escapeHtml(p.email)}</div></td>
              <td><span style="color:#dc2626; font-weight:700;">${escapeHtml(p.emergency_hotline || p.phone || '0901234567')}</span></td>
              <td><span style="background:#f1f5f9; color:#334155; padding:3px 8px; border-radius:6px; font-size:11.5px; font-weight:600;">Kỹ thuật</span></td>
              <td><span style="color:#94a3b8; font-size:11.5px;">Chưa phân công</span></td>
              <td><span class="badge-filament success">ACTIVE</span></td>
              <td style="text-align: right;"><button class="btn-mo-outline" style="padding:4px 8px; font-size:12px;" onclick="viewStaffPm('${p.id}')"><i class="fa-regular fa-eye"></i></button></td>
            </tr>
          `).join('');
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
      document.getElementById('pm-form-password').required = true;
      document.getElementById('pm-form-password-req').style.display = 'inline';
      document.getElementById('pm-form-password-hint').style.display = 'none';

      await fetchStaffBuildingsList();
      renderBuildingCheckboxes('pm-form-buildings', []);
      openModal('staff-pm-modal');
    }

    // Mở modal sửa PM (API 84 + 85)
    async function openEditPmModal(id) {
      const token = getSanctumToken();
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
        document.getElementById('pm-form-password').required = false;
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

      const payload = {
        employee_code: document.getElementById('pm-form-code').value.trim() || null,
        full_name: document.getElementById('pm-form-fullname').value.trim(),
        email: document.getElementById('pm-form-email').value.trim(),
        phone: document.getElementById('pm-form-phone').value.trim() || null,
        emergency_hotline: document.getElementById('pm-form-hotline').value.trim() || null,
        department: document.getElementById('pm-form-department').value,
        status: document.getElementById('pm-form-status').value,
        building_ids: getSelectedBuildingIds('pm-form-buildings')
      };

      const pwd = document.getElementById('pm-form-password').value;
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
          alert(json.message || 'Lưu nhân sự BQL thành công!');
          closeModal('staff-pm-modal');
          loadStaffPm(1);
        } else {
          alert('Lỗi: ' + (json.message || JSON.stringify(json.errors || 'Thất bại')));
        }
      } catch (err) {
        console.error('Submit PM error:', err);
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

        tbody.innerHTML = accs.map(a => {
          const buildings = a.account_buildings || a.buildings || [];
          const bldHtml = buildings.length > 0
            ? buildings.slice(0, 2).map(b => `<span style="background:#ecfdf5; color:#065f46; padding:2px 6px; border-radius:4px; font-size:11px; margin-right:4px;">${escapeHtml(b.name || 'Tòa nhà')}</span>`).join('') + (buildings.length > 2 ? `<small style="color:#64748b;">+${buildings.length - 2}</small>` : '')
            : '<span style="color:#94a3b8; font-size:11.5px;">Toàn hệ thống</span>';

          const canAdjustHtml = a.can_approve_adjustment
            ? '<span style="background:#dcfce7; color:#15803d; font-size:11.5px; font-weight:700; padding:2px 8px; border-radius:9999px;"><i class="fa-solid fa-check"></i> Được phép</span>'
            : '<span style="color:#94a3b8; font-size:11.5px;">Không</span>';

          const statusClass = (a.status === 'ACTIVE') ? 'success' : 'danger';

          return `
            <tr>
              <td><span style="font-family:monospace; font-weight:700; color:#065f46;">${escapeHtml(a.employee_code || 'ACC-' + a.id?.substring(0, 4))}</span></td>
              <td><strong>${escapeHtml(a.full_name || a.name || '---')}</strong></td>
              <td>
                <div style="font-size:12.5px; color:#2563eb;">${escapeHtml(a.email)}</div>
                <div style="font-size:11.5px; color:#64748b;">${escapeHtml(a.phone || '---')}</div>
              </td>
              <td>${canAdjustHtml}</td>
              <td>${bldHtml}</td>
              <td><span class="badge-filament ${statusClass}">${escapeHtml(a.status || 'ACTIVE')}</span></td>
              <td style="text-align: right; white-space: nowrap;">
                <button class="btn-mo-outline" style="padding:4px 8px; font-size:12px;" onclick="viewStaffAccountant('${a.id}')" title="Xem chi tiết">
                  <i class="fa-regular fa-eye"></i>
                </button>
                <button class="btn-mo-outline" style="padding:4px 8px; font-size:12px;" onclick="openEditAccountantModal('${a.id}')" title="Chỉnh sửa">
                  <i class="fa-regular fa-pen-to-square"></i>
                </button>
                <button class="btn-mo-outline" style="padding:4px 8px; font-size:12px; color:#dc2626; border-color:#fca5a5;" onclick="deleteStaffAccountant('${a.id}', '${escapeHtml(a.full_name || a.name)}')" title="Xóa mềm">
                  <i class="fa-regular fa-trash-can"></i>
                </button>
              </td>
            </tr>
          `;
        }).join('');

        renderStaffPagination('pagination-staff-accountants', meta, loadStaffAccountants);
      } catch (err) {
        console.error('Load Accountants error:', err);
        if (typeof SERVER_STAFF !== 'undefined' && SERVER_STAFF.accountants && SERVER_STAFF.accountants.length > 0) {
          const accs = SERVER_STAFF.accountants;
          document.getElementById('badge-count-accountants')?.innerText && (document.getElementById('badge-count-accountants').innerText = accs.length);
          tbody.innerHTML = accs.map(a => `
            <tr>
              <td><span style="font-family:monospace; font-weight:700; color:#065f46;">${escapeHtml(a.employee_code || 'ACC-' + a.id?.substring(0, 4))}</span></td>
              <td><strong>${escapeHtml(a.full_name || a.name || '---')}</strong></td>
              <td><div style="font-size:12.5px; color:#2563eb;">${escapeHtml(a.email)}</div></td>
              <td><span style="background:#dcfce7; color:#15803d; font-size:11.5px; font-weight:700; padding:2px 8px; border-radius:9999px;">Được phép</span></td>
              <td><span style="color:#94a3b8; font-size:11.5px;">Toàn hệ thống</span></td>
              <td><span class="badge-filament success">ACTIVE</span></td>
              <td style="text-align: right;"><button class="btn-mo-outline" style="padding:4px 8px; font-size:12px;" onclick="viewStaffAccountant('${a.id}')"><i class="fa-regular fa-eye"></i></button></td>
            </tr>
          `).join('');
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
      document.getElementById('acc-form-password').required = true;
      document.getElementById('acc-form-password-req').style.display = 'inline';
      document.getElementById('acc-form-password-hint').style.display = 'none';

      await fetchStaffBuildingsList();
      renderBuildingCheckboxes('acc-form-buildings', []);
      openModal('staff-accountant-modal');
    }

    // Mở modal sửa Kế toán (API 89 + 90)
    async function openEditAccountantModal(id) {
      const token = getSanctumToken();
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
        document.getElementById('acc-form-password').required = false;
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

      const payload = {
        employee_code: document.getElementById('acc-form-code').value.trim() || null,
        full_name: document.getElementById('acc-form-fullname').value.trim(),
        email: document.getElementById('acc-form-email').value.trim(),
        phone: document.getElementById('acc-form-phone').value.trim() || null,
        can_approve_adjustment: document.getElementById('acc-form-can-adjust').checked,
        status: document.getElementById('acc-form-status').value,
        building_ids: getSelectedBuildingIds('acc-form-buildings')
      };

      const pwd = document.getElementById('acc-form-password').value;
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
          alert(json.message || 'Lưu nhân viên Kế toán thành công!');
          closeModal('staff-accountant-modal');
          loadStaffAccountants(1);
        } else {
          alert('Lỗi: ' + (json.message || JSON.stringify(json.errors || 'Thất bại')));
        }
      } catch (err) {
        console.error('Submit Accountant error:', err);
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
    // Tự động tải trước toàn bộ dữ liệu nhân sự và số lượng badge ngay khi nạp script
    if (typeof preloadAllStaffData === 'function') {
      preloadAllStaffData();
    }
    document.addEventListener('DOMContentLoaded', function() {
      if (typeof preloadAllStaffData === 'function') {
        preloadAllStaffData();
      }
    });
  </script>
