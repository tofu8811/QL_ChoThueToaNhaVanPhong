<script>
    /* ==========================================================================
       MODULE 5: BUILDING ASSIGNMENT LOGIC (ENDPOINTS 92 - 98)
       ========================================================================== */
    let currentAssignmentBuildingId = '';
    let currentAssignedSalesIds = new Set();
    let currentAssignedPmIds = new Set();
    let currentAssignedAccountantIds = new Set();

    // Nạp danh sách các tòa nhà vào dropdown phân công
    async function populateBuildingAssignmentDropdown(preferredBuildingId = null) {
      const select = document.getElementById('select-assignment-building');
      if (!select) return;

      const buildings = await fetchStaffBuildingsList();
      if (!buildings || buildings.length === 0) {
        select.innerHTML = '<option value="">-- Hệ thống chưa có dữ liệu tòa nhà --</option>';
        return;
      }

      select.innerHTML = '<option value="">-- Chọn một tòa nhà văn phòng --</option>' + buildings.map(b => `
        <option value="${b.id}" ${(preferredBuildingId && preferredBuildingId === b.id) ? 'selected' : ''}>
          ${escapeHtml(b.name)} [${escapeHtml(b.code || 'BLD')}] - ${escapeHtml(b.district || 'TP.HCM')}
        </option>
      `).join('');

      if (preferredBuildingId) {
        select.value = preferredBuildingId;
        onBuildingAssignmentChange(preferredBuildingId);
      } else if (!currentAssignmentBuildingId && buildings.length > 0) {
        select.value = buildings[0].id;
        onBuildingAssignmentChange(buildings[0].id);
      } else if (currentAssignmentBuildingId) {
        select.value = currentAssignmentBuildingId;
        onBuildingAssignmentChange(currentAssignmentBuildingId);
      }
    }

    // Khi người dùng đổi tòa nhà trong dropdown
    function onBuildingAssignmentChange(buildingId) {
      if (!buildingId) {
        currentAssignmentBuildingId = '';
        document.getElementById('box-assignment-building-info').style.display = 'none';
        document.getElementById('tbody-building-assigned-sales').innerHTML = '<tr><td colspan="7" style="text-align:center; padding: 25px; color:#94a3b8;">Vui lòng chọn tòa nhà để xem danh sách Sales phụ trách.</td></tr>';
        document.getElementById('tbody-building-assigned-pm').innerHTML = '<tr><td colspan="7" style="text-align:center; padding: 25px; color:#94a3b8;">Vui lòng chọn tòa nhà để xem danh sách BQL phụ trách.</td></tr>';
        document.getElementById('tbody-building-assigned-accountants').innerHTML = '<tr><td colspan="7" style="text-align:center; padding: 25px; color:#94a3b8;">Vui lòng chọn tòa nhà để xem danh sách Kế toán phụ trách.</td></tr>';
        return;
      }
      currentAssignmentBuildingId = buildingId;
      loadBuildingAssignments(buildingId);
    }

    function reloadBuildingAssignments() {
      if (currentAssignmentBuildingId) {
        loadBuildingAssignments(currentAssignmentBuildingId);
      } else {
        populateBuildingAssignmentDropdown();
      }
    }

    // Mở nhanh phân công tòa nhà từ bất kỳ đâu (ví dụ bảng tòa nhà)
    function openBuildingAssignmentsFromBuilding(buildingId, buildingName) {
      switchFilamentTab('tab-admin-staff', document.getElementById('nav-item-staff'));
      switchStaffSubTab('assignments', document.getElementById('btn-subtab-assignments'));
      populateBuildingAssignmentDropdown(buildingId);
    }

    // =========================================================================
    // API 92: GET /api/v1/admin/buildings/{buildingId}/assignments
    // Xem danh sách toàn bộ đội ngũ nhân sự phụ trách tòa nhà
    // =========================================================================
    async function loadBuildingAssignments(buildingId) {
      if (!buildingId) return;

      const tbSales = document.getElementById('tbody-building-assigned-sales');
      const tbPm = document.getElementById('tbody-building-assigned-pm');
      const tbAcc = document.getElementById('tbody-building-assigned-accountants');

      if (tbSales) tbSales.innerHTML = '<tr><td colspan="7" style="text-align:center; padding:20px; color:#94a3b8;"><i class="fa-solid fa-spinner fa-spin"></i> Đang tải dữ liệu Sales...</td></tr>';
      if (tbPm) tbPm.innerHTML = '<tr><td colspan="7" style="text-align:center; padding:20px; color:#94a3b8;"><i class="fa-solid fa-spinner fa-spin"></i> Đang tải dữ liệu BQL...</td></tr>';
      if (tbAcc) tbAcc.innerHTML = '<tr><td colspan="7" style="text-align:center; padding:20px; color:#94a3b8;"><i class="fa-solid fa-spinner fa-spin"></i> Đang tải dữ liệu Kế toán...</td></tr>';

      const token = getSanctumToken();
      try {
        const res = await fetch(`/api/v1/admin/buildings/${buildingId}/assignments`, {
          headers: { 'Accept': 'application/json', 'Authorization': 'Bearer ' + token }
        });
        const json = await res.json();
        if (!res.ok) {
          const errMsg = json.message || 'Không thể tải phân công nhân sự.';
          if (tbSales) tbSales.innerHTML = `<tr><td colspan="7" style="text-align:center; color:#ef4444; padding:20px;">${escapeHtml(errMsg)}</td></tr>`;
          if (tbPm) tbPm.innerHTML = `<tr><td colspan="7" style="text-align:center; color:#ef4444; padding:20px;">${escapeHtml(errMsg)}</td></tr>`;
          if (tbAcc) tbAcc.innerHTML = `<tr><td colspan="7" style="text-align:center; color:#ef4444; padding:20px;">${escapeHtml(errMsg)}</td></tr>`;
          return;
        }

        const data = json.data || {};
        const bld = data.building || {};
        const sales = data.sales || [];
        const managers = data.managers || [];
        const accountants = data.accountants || [];

        // Lưu set id để lọc khi gán thêm
        currentAssignedSalesIds = new Set(sales.map(s => String(s.id)));
        currentAssignedPmIds = new Set(managers.map(m => String(m.id)));
        currentAssignedAccountantIds = new Set(accountants.map(a => String(a.id)));

        // Cập nhật thông tin tòa nhà
        const boxInfo = document.getElementById('box-assignment-building-info');
        if (boxInfo) {
          boxInfo.style.display = 'flex';
          document.getElementById('info-bld-code').innerText = bld.code || '---';
          document.getElementById('info-bld-name').innerText = bld.name || '---';
          document.getElementById('info-bld-address').innerText = bld.address || (bld.district ? bld.district + ', TP.HCM' : 'TP.HCM');
          document.getElementById('info-bld-grade').innerText = bld.grade || 'Tiêu chuẩn';
        }

        const badgeAssignments = document.getElementById('badge-count-assignments');
        if (badgeAssignments) {
          badgeAssignments.innerText = `${sales.length + managers.length + accountants.length} NV`;
        }

        // Render Sales Table (API 92)
        if (sales.length === 0) {
          tbSales.innerHTML = '<tr><td colspan="7" style="text-align:center; padding:25px; color:#94a3b8;">Chưa có nhân viên Sales nào được phân công phụ trách tòa nhà này.</td></tr>';
        } else {
          tbSales.innerHTML = sales.map(s => {
            const assignedAt = s.assigned_at ? new Date(s.assigned_at).toLocaleString('vi-VN') : 'Mặc định';
            const isActive = (s.is_active !== false);
            return `
              <tr>
                <td><span style="font-family:monospace; font-weight:700; color:#0f3361;">${escapeHtml(s.employee_code || 'SL-' + s.id.substring(0, 4))}</span></td>
                <td><strong>${escapeHtml(s.full_name || '---')}</strong></td>
                <td>
                  <div style="font-size:12px; color:#2563eb;">${escapeHtml(s.email)}</div>
                  <div style="font-size:11.5px; color:#64748b;">${escapeHtml(s.phone || '---')}</div>
                </td>
                <td><span style="font-weight:700;">${s.max_concurrent_leads || 10}</span> leads</td>
                <td>
                  ${isActive 
                    ? '<span class="badge-filament success"><i class="fa-solid fa-circle-check"></i> Đang nhận Lead</span>' 
                    : '<span class="badge-filament warning">Tạm ngưng</span>'}
                </td>
                <td><span style="font-size:12px; color:#64748b;">${assignedAt}</span></td>
                <td style="text-align:right;">
                  <button class="btn-mo-outline" style="padding:4px 10px; font-size:12px; color:#dc2626; border-color:#fca5a5;" onclick="removeSalesAssignment('${s.id}', '${escapeHtml(s.full_name)}')" title="Gỡ Sales khỏi tòa nhà (API 94)">
                    <i class="fa-solid fa-user-minus"></i> Gỡ Bỏ
                  </button>
                </td>
              </tr>
            `;
          }).join('');
        }

        // Render Property Managers Table (API 92)
        if (managers.length === 0) {
          tbPm.innerHTML = '<tr><td colspan="7" style="text-align:center; padding:25px; color:#94a3b8;">Chưa có Ban Quản Lý được phân công phụ trách tòa nhà này.</td></tr>';
        } else {
          const deptLabels = { TECHNICAL: 'Kỹ thuật', SECURITY: 'An ninh', CLEANING: 'Vệ sinh', CUSTOMER_CARE: 'CSKH' };
          tbPm.innerHTML = managers.map(p => {
            const assignedAt = p.assigned_at ? new Date(p.assigned_at).toLocaleString('vi-VN') : 'Mặc định';
            const isHead = !!(p.is_primary || p.is_head_manager);
            return `
              <tr>
                <td><span style="font-family:monospace; font-weight:700; color:#b45309;">${escapeHtml(p.employee_code || 'PM-' + p.id.substring(0, 4))}</span></td>
                <td>
                  <strong>${escapeHtml(p.full_name || '---')}</strong>
                  <div style="font-size:11.5px; color:#64748b;">${escapeHtml(p.email)}</div>
                </td>
                <td><span style="color:#dc2626; font-weight:700;"><i class="fa-solid fa-phone-volume"></i> ${escapeHtml(p.emergency_hotline || p.phone || '---')}</span></td>
                <td><span style="background:#f1f5f9; padding:2px 8px; border-radius:4px; font-size:11.5px;">${deptLabels[p.department] || p.department || 'BQL'}</span></td>
                <td>
                  ${isHead 
                    ? '<span style="background:#fee2e2; color:#b91c1c; font-weight:700; font-size:11.5px; padding:2px 8px; border-radius:9999px;"><i class="fa-solid fa-star"></i> Trưởng Ban Quản Lý</span>' 
                    : '<span style="color:#64748b; font-size:11.5px;">Thành viên BQL</span>'}
                </td>
                <td><span style="font-size:12px; color:#64748b;">${assignedAt}</span></td>
                <td style="text-align:right;">
                  <button class="btn-mo-outline" style="padding:4px 10px; font-size:12px; color:#dc2626; border-color:#fca5a5;" onclick="removePmAssignment('${p.id}', '${escapeHtml(p.full_name)}')" title="Gỡ BQL khỏi tòa nhà (API 96)">
                    <i class="fa-solid fa-user-minus"></i> Gỡ Bỏ
                  </button>
                </td>
              </tr>
            `;
          }).join('');
        }

        // Render Accountants Table (API 92)
        if (accountants.length === 0) {
          tbAcc.innerHTML = '<tr><td colspan="7" style="text-align:center; padding:25px; color:#94a3b8;">Chưa có Kế toán được phân công theo dõi công nợ cho tòa nhà này.</td></tr>';
        } else {
          tbAcc.innerHTML = accountants.map(a => {
            const assignedAt = a.assigned_at ? new Date(a.assigned_at).toLocaleString('vi-VN') : 'Mặc định';
            const canAdjust = !!a.can_approve_adjustment;
            return `
              <tr>
                <td><span style="font-family:monospace; font-weight:700; color:#065f46;">${escapeHtml(a.employee_code || 'ACC-' + a.id.substring(0, 4))}</span></td>
                <td><strong>${escapeHtml(a.full_name || '---')}</strong></td>
                <td>
                  <div style="font-size:12px; color:#2563eb;">${escapeHtml(a.email)}</div>
                  <div style="font-size:11.5px; color:#64748b;">${escapeHtml(a.phone || '---')}</div>
                </td>
                <td>
                  ${canAdjust 
                    ? '<span style="color:#15803d; font-weight:700; font-size:11.5px;"><i class="fa-solid fa-circle-check"></i> Được phép duyệt cước</span>' 
                    : '<span style="color:#64748b; font-size:11.5px;">Chỉ xem & lập phiếu</span>'}
                </td>
                <td><span class="badge-filament ${(a.status === 'ACTIVE') ? 'success' : 'danger'}">${escapeHtml(a.status || 'ACTIVE')}</span></td>
                <td><span style="font-size:12px; color:#64748b;">${assignedAt}</span></td>
                <td style="text-align:right;">
                  <button class="btn-mo-outline" style="padding:4px 10px; font-size:12px; color:#dc2626; border-color:#fca5a5;" onclick="removeAccountantAssignment('${a.id}', '${escapeHtml(a.full_name)}')" title="Gỡ Kế toán khỏi tòa nhà (API 98)">
                    <i class="fa-solid fa-user-minus"></i> Gỡ Bỏ
                  </button>
                </td>
              </tr>
            `;
          }).join('');
        }

      } catch (err) {
        console.error('Load building assignments error:', err);
      }
    }

    // =========================================================================
    // API 93: POST /api/v1/admin/buildings/{buildingId}/assignments/sales
    // Gán Sales vào Tòa nhà
    // =========================================================================
    async function openAssignSalesModal() {
      if (!currentAssignmentBuildingId) {
        alert('Vui lòng chọn một tòa nhà trước khi phân công Sales.');
        return;
      }

      const bldName = document.getElementById('info-bld-name')?.innerText || 'Tòa nhà';
      document.getElementById('assign-sales-modal-building-name').innerText = 'Tòa nhà: ' + bldName;
      document.getElementById('assign-sales-building-id').value = currentAssignmentBuildingId;

      const container = document.getElementById('assign-sales-checkboxes-container');
      container.innerHTML = '<span style="color:#94a3b8; font-size:12px;"><i class="fa-solid fa-spinner fa-spin"></i> Đang tải danh sách nhân viên Sales...</span>';
      openModal('modal-assign-sales');

      const token = getSanctumToken();
      try {
        const res = await fetch('/api/v1/admin/users/sales?per_page=100', {
          headers: { 'Accept': 'application/json', 'Authorization': 'Bearer ' + token }
        });
        const json = await res.json();
        const allSales = json.data?.data || json.data || [];

        // Lọc các sales chưa gán
        const availableSales = allSales.filter(s => !currentAssignedSalesIds.has(String(s.id)));

        if (availableSales.length === 0) {
          container.innerHTML = '<span style="color:#64748b; font-size:12.5px;">Tất cả nhân viên Sales đã được phân công vào tòa nhà này, hoặc không có nhân viên Sales mới nào.</span>';
          return;
        }

        container.innerHTML = availableSales.map(s => `
          <label class="building-check-label">
            <input type="checkbox" name="assign_sales_chk[]" value="${s.id}">
            <span><strong>${escapeHtml(s.full_name || s.name)}</strong> <small style="color:#64748b;">(${escapeHtml(s.employee_code || s.email)})</small></span>
          </label>
        `).join('');

      } catch (e) {
        container.innerHTML = '<span style="color:#ef4444; font-size:12px;">Lỗi kết nối khi tải danh sách Sales.</span>';
      }
    }

    async function handleAssignSalesSubmit(e) {
      e.preventDefault();
      const buildingId = document.getElementById('assign-sales-building-id').value;
      const checkedBoxes = document.querySelectorAll('input[name="assign_sales_chk[]"]:checked');
      const salesIds = Array.from(checkedBoxes).map(cb => cb.value);

      if (salesIds.length === 0) {
        alert('Vui lòng tích chọn ít nhất một nhân viên Sales để phân công.');
        return;
      }

      const isActive = document.getElementById('assign-sales-is-active').checked;
      const token = getSanctumToken();
      const btn = document.getElementById('btn-submit-assign-sales');
      const originalText = btn.innerHTML;
      btn.disabled = true;
      btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Đang gán...';

      try {
        const res = await fetch(`/api/v1/admin/buildings/${buildingId}/assignments/sales`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'Authorization': 'Bearer ' + token
          },
          body: JSON.stringify({ sales_ids: salesIds, is_active: isActive })
        });
        const json = await res.json();
        if (res.ok) {
          alert(json.message || 'Phân công Sales vào tòa nhà thành công!');
          closeModal('modal-assign-sales');
          loadBuildingAssignments(buildingId);
        } else {
          alert('Lỗi: ' + (json.message || JSON.stringify(json.errors || 'Thất bại')));
        }
      } catch (err) {
        console.error('Assign sales error:', err);
      } finally {
        btn.disabled = false;
        btn.innerHTML = originalText;
      }
    }

    // =========================================================================
    // API 94: DELETE /api/v1/admin/buildings/{buildingId}/assignments/sales/{saleId}
    // Gỡ Sales khỏi Tòa nhà
    // =========================================================================
    async function removeSalesAssignment(saleId, saleName) {
      if (!confirm(`Bạn có chắc muốn gỡ nhân viên Sales [${saleName}] khỏi danh sách phụ trách tòa nhà này?`)) return;

      const token = getSanctumToken();
      try {
        const res = await fetch(`/api/v1/admin/buildings/${currentAssignmentBuildingId}/assignments/sales/${saleId}`, {
          method: 'DELETE',
          headers: { 'Accept': 'application/json', 'Authorization': 'Bearer ' + token }
        });
        const json = await res.json();
        if (res.ok) {
          alert(json.message || 'Đã gỡ Sales khỏi tòa nhà thành công.');
          loadBuildingAssignments(currentAssignmentBuildingId);
        } else {
          alert('Lỗi: ' + (json.message || 'Không thể gỡ Sales.'));
        }
      } catch (err) {
        console.error('Remove sales error:', err);
      }
    }

    // =========================================================================
    // API 95: POST /api/v1/admin/buildings/{buildingId}/assignments/property-managers
    // Gán Ban Quản Lý (PM) vào Tòa nhà
    // =========================================================================
    async function openAssignPmModal() {
      if (!currentAssignmentBuildingId) {
        alert('Vui lòng chọn một tòa nhà trước khi gán BQL.');
        return;
      }

      const bldName = document.getElementById('info-bld-name')?.innerText || 'Tòa nhà';
      document.getElementById('assign-pm-modal-building-name').innerText = 'Tòa nhà: ' + bldName;
      document.getElementById('assign-pm-building-id').value = currentAssignmentBuildingId;

      const select = document.getElementById('assign-pm-select');
      select.innerHTML = '<option value="">-- Đang tải danh sách PM... --</option>';
      openModal('modal-assign-pm');

      const token = getSanctumToken();
      try {
        const res = await fetch('/api/v1/admin/users/property-managers?per_page=100', {
          headers: { 'Accept': 'application/json', 'Authorization': 'Bearer ' + token }
        });
        const json = await res.json();
        const allPm = json.data?.data || json.data || [];

        // Lọc PM chưa gán
        const availablePm = allPm.filter(p => !currentAssignedPmIds.has(String(p.id)));

        if (availablePm.length === 0) {
          select.innerHTML = '<option value="">-- Tất cả PM đã được phân công vào tòa nhà này --</option>';
          return;
        }

        select.innerHTML = '<option value="">-- Chọn nhân sự Ban Quản Lý --</option>' + availablePm.map(p => `
          <option value="${p.id}">
            ${escapeHtml(p.full_name || p.name)} (${escapeHtml(p.department || 'BQL')}) - ${escapeHtml(p.emergency_hotline || p.phone || p.email)}
          </option>
        `).join('');

      } catch (e) {
        select.innerHTML = '<option value="">-- Lỗi tải danh sách PM --</option>';
      }
    }

    async function handleAssignPmSubmit(e) {
      e.preventDefault();
      const buildingId = document.getElementById('assign-pm-building-id').value;
      const pmId = document.getElementById('assign-pm-select').value;
      const isHead = document.getElementById('assign-pm-is-head').checked;

      if (!pmId) {
        alert('Vui lòng chọn nhân sự Ban Quản Lý.');
        return;
      }

      const token = getSanctumToken();
      const btn = document.getElementById('btn-submit-assign-pm');
      const originalText = btn.innerHTML;
      btn.disabled = true;
      btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Đang gán...';

      try {
        const res = await fetch(`/api/v1/admin/buildings/${buildingId}/assignments/property-managers`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'Authorization': 'Bearer ' + token
          },
          body: JSON.stringify({ pm_id: pmId, is_primary: isHead, is_head_manager: isHead })
        });
        const json = await res.json();
        if (res.ok) {
          alert(json.message || 'Gán Ban Quản Lý vào tòa nhà thành công!');
          closeModal('modal-assign-pm');
          loadBuildingAssignments(buildingId);
        } else {
          alert('Lỗi: ' + (json.message || JSON.stringify(json.errors || 'Thất bại')));
        }
      } catch (err) {
        console.error('Assign PM error:', err);
      } finally {
        btn.disabled = false;
        btn.innerHTML = originalText;
      }
    }

    // =========================================================================
    // API 96: DELETE /api/v1/admin/buildings/{buildingId}/assignments/property-managers/{pmId}
    // Gỡ Ban Quản Lý khỏi Tòa nhà
    // =========================================================================
    async function removePmAssignment(pmId, pmName) {
      if (!confirm(`Bạn có chắc muốn gỡ Ban Quản Lý [${pmName}] khỏi danh sách phụ trách tòa nhà này?`)) return;

      const token = getSanctumToken();
      try {
        const res = await fetch(`/api/v1/admin/buildings/${currentAssignmentBuildingId}/assignments/property-managers/${pmId}`, {
          method: 'DELETE',
          headers: { 'Accept': 'application/json', 'Authorization': 'Bearer ' + token }
        });
        const json = await res.json();
        if (res.ok) {
          alert(json.message || 'Đã gỡ Ban Quản Lý khỏi tòa nhà thành công.');
          loadBuildingAssignments(currentAssignmentBuildingId);
        } else {
          alert('Lỗi: ' + (json.message || 'Không thể gỡ BQL.'));
        }
      } catch (err) {
        console.error('Remove PM error:', err);
      }
    }

    // =========================================================================
    // API 97: POST /api/v1/admin/buildings/{buildingId}/assignments/accountants
    // Gán Kế toán vào Tòa nhà
    // =========================================================================
    async function openAssignAccountantModal() {
      if (!currentAssignmentBuildingId) {
        alert('Vui lòng chọn một tòa nhà trước khi gán Kế toán.');
        return;
      }

      const bldName = document.getElementById('info-bld-name')?.innerText || 'Tòa nhà';
      document.getElementById('assign-acc-modal-building-name').innerText = 'Tòa nhà: ' + bldName;
      document.getElementById('assign-acc-building-id').value = currentAssignmentBuildingId;

      const container = document.getElementById('assign-acc-checkboxes-container');
      container.innerHTML = '<span style="color:#94a3b8; font-size:12px;"><i class="fa-solid fa-spinner fa-spin"></i> Đang tải danh sách Kế toán...</span>';
      openModal('modal-assign-accountant');

      const token = getSanctumToken();
      try {
        const res = await fetch('/api/v1/admin/users/accountants?per_page=100', {
          headers: { 'Accept': 'application/json', 'Authorization': 'Bearer ' + token }
        });
        const json = await res.json();
        const allAcc = json.data?.data || json.data || [];

        // Lọc Kế toán chưa gán
        const availableAcc = allAcc.filter(a => !currentAssignedAccountantIds.has(String(a.id)));

        if (availableAcc.length === 0) {
          container.innerHTML = '<span style="color:#64748b; font-size:12.5px;">Tất cả nhân viên Kế toán đã được phân công vào tòa nhà này.</span>';
          return;
        }

        container.innerHTML = availableAcc.map(a => `
          <label class="building-check-label">
            <input type="checkbox" name="assign_acc_chk[]" value="${a.id}">
            <span><strong>${escapeHtml(a.full_name || a.name)}</strong> <small style="color:#64748b;">(${escapeHtml(a.employee_code || a.email)})</small></span>
          </label>
        `).join('');

      } catch (e) {
        container.innerHTML = '<span style="color:#ef4444; font-size:12px;">Lỗi kết nối khi tải danh sách Kế toán.</span>';
      }
    }

    async function handleAssignAccountantSubmit(e) {
      e.preventDefault();
      const buildingId = document.getElementById('assign-acc-building-id').value;
      const checkedBoxes = document.querySelectorAll('input[name="assign_acc_chk[]"]:checked');
      const accountantIds = Array.from(checkedBoxes).map(cb => cb.value);

      if (accountantIds.length === 0) {
        alert('Vui lòng tích chọn ít nhất một nhân viên Kế toán để phân công.');
        return;
      }

      const token = getSanctumToken();
      const btn = document.getElementById('btn-submit-assign-acc');
      const originalText = btn.innerHTML;
      btn.disabled = true;
      btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Đang gán...';

      try {
        const res = await fetch(`/api/v1/admin/buildings/${buildingId}/assignments/accountants`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'Authorization': 'Bearer ' + token
          },
          body: JSON.stringify({ accountant_ids: accountantIds })
        });
        const json = await res.json();
        if (res.ok) {
          alert(json.message || 'Gán Kế toán vào tòa nhà thành công!');
          closeModal('modal-assign-accountant');
          loadBuildingAssignments(buildingId);
        } else {
          alert('Lỗi: ' + (json.message || JSON.stringify(json.errors || 'Thất bại')));
        }
      } catch (err) {
        console.error('Assign Accountant error:', err);
      } finally {
        btn.disabled = false;
        btn.innerHTML = originalText;
      }
    }

    // =========================================================================
    // API 98: DELETE /api/v1/admin/buildings/{buildingId}/assignments/accountants/{accountantId}
    // Gỡ Kế toán khỏi Tòa nhà
    // =========================================================================
    async function removeAccountantAssignment(accountantId, accountantName) {
      if (!confirm(`Bạn có chắc muốn gỡ Kế toán [${accountantName}] khỏi danh sách theo dõi tòa nhà này?`)) return;

      const token = getSanctumToken();
      try {
        const res = await fetch(`/api/v1/admin/buildings/${currentAssignmentBuildingId}/assignments/accountants/${accountantId}`, {
          method: 'DELETE',
          headers: { 'Accept': 'application/json', 'Authorization': 'Bearer ' + token }
        });
        const json = await res.json();
        if (res.ok) {
          alert(json.message || 'Đã gỡ Kế toán khỏi tòa nhà thành công.');
          loadBuildingAssignments(currentAssignmentBuildingId);
        } else {
          alert('Lỗi: ' + (json.message || 'Không thể gỡ Kế toán.'));
        }
      } catch (err) {
        console.error('Remove Accountant error:', err);
      }
    }
        // Chuyển đổi giữa 4 sub-tab nhân sự

    // Initialize on load
    window.addEventListener("DOMContentLoaded", () => {
      renderBuildings(BUILDINGS_DATA);
      renderAdminTables();
      calculateQuickQuote();
      checkAuthState();
    });
  </script>