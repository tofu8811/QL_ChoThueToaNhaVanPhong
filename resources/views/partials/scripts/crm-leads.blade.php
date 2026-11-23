<script>
    /* ==========================================================================
       MODULE 2: CRM & LEADS MANAGEMENT JAVASCRIPT (ENDPOINTS 23 - 33)
       ========================================================================== */
    let crmLeadsList = [];
    let currentCrmStatusFilter = 'ALL';
    let currentCrmBuildingFilter = 'ALL';
    let currentCrmSearch = '';
    let currentViewingLeadId = null;

    // 1. Nạp danh sách Leads từ REST API: GET /api/v1/crm/leads
    async function loadCrmLeads() {
      const tbody = document.getElementById('crm-leads-tbody');
      if (tbody) {
        tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;padding:24px;color:#94a3b8;"><i class="fa-solid fa-spinner fa-spin"></i> Đang tải dữ liệu khách hàng tiềm năng từ API...</td></tr>';
      }

      try {
        let url = '/api/v1/crm/leads?per_page=100';
        if (currentCrmStatusFilter && currentCrmStatusFilter !== 'ALL') {
          url += `&pipeline_status=${encodeURIComponent(currentCrmStatusFilter)}`;
        }
        if (currentCrmBuildingFilter && currentCrmBuildingFilter !== 'ALL') {
          url += `&building_id=${encodeURIComponent(currentCrmBuildingFilter)}`;
        }
        if (currentCrmSearch) {
          url += `&search=${encodeURIComponent(currentCrmSearch)}`;
        }

        const res = await fetch(url, {
          method: 'GET',
          headers: getAuthHeaders()
        });

        if (res.status === 401) {
          if (tbody) tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;color:#ef4444;padding:20px;">Vui lòng đăng nhập tài khoản Sales / Admin để truy cập CRM.</td></tr>';
          return;
        }

        const json = await res.json();
        if (res.ok) {
          crmLeadsList = json.data?.data || json.data || [];
          renderCrmLeadsTable();
          renderCrmKanbanBoard();
        } else {
          if (tbody) tbody.innerHTML = `<tr><td colspan="7" style="text-align:center;color:#ef4444;padding:20px;">Lỗi: ${escapeHtml(json.message || 'Không thể tải danh sách Lead')}</td></tr>`;
        }
      } catch (err) {
        console.error('Lỗi kết nối API Leads:', err);
        if (tbody) tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;color:#ef4444;padding:20px;">Không thể kết nối máy chủ API CRM.</td></tr>';
      }
    }

    // Helper hiển thị badge trạng thái Pipeline
    function getPipelineStatusBadge(status) {
      switch (status) {
        case 'PENDING_ASSIGN':
          return '<span class="badge-filament warning" style="background:#fef3c7;color:#b45309;border:1px solid #fde68a;font-weight:700;"><i class="fa-solid fa-clock"></i> Chờ phân bổ</span>';
        case 'NEW':
          return '<span class="badge-filament info"><i class="fa-solid fa-sparkles"></i> Mới tiếp nhận</span>';
        case 'NEGOTIATING':
          return '<span class="badge-filament purple" style="background:#f3e8ff;color:#7e22ce;"><i class="fa-solid fa-handshake"></i> Đang đàm phán</span>';
        case 'WON':
          return '<span class="badge-filament success"><i class="fa-solid fa-trophy"></i> Thành công (Chốt thuê)</span>';
        case 'LOST':
          return '<span class="badge-filament danger"><i class="fa-solid fa-ban"></i> Thất bại (Từ chối)</span>';
        default:
          return `<span class="badge-filament">${escapeHtml(status)}</span>`;
      }
    }

    // 2. Render bảng danh sách Lead (Table View)
    function renderCrmLeadsTable() {
      const tbody = document.getElementById('crm-leads-tbody');
      const countEl = document.getElementById('crm-leads-count');
      if (countEl) countEl.innerText = crmLeadsList.length;
      const sideBadgeEl = document.getElementById('lead-count-badge');
      if (sideBadgeEl && crmLeadsList.length > 0) sideBadgeEl.innerText = crmLeadsList.length;

      // Cập nhật số lượng Lead chờ phân bổ
      const pendingLeads = crmLeadsList.filter(l => l.pipeline_status === 'PENDING_ASSIGN' || (!l.assigned_sales && !l.assignedSales));
      const pendingCountEl = document.getElementById('crm-pending-count');
      if (pendingCountEl) pendingCountEl.innerText = pendingLeads.length;
      const optPending = document.getElementById('opt-pending-assign');
      if (optPending) optPending.innerText = `⚠️ Chờ phân bổ (${pendingLeads.length})`;

      if (!tbody) return;
      if (crmLeadsList.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;padding:32px;color:#94a3b8;">Không tìm thấy khách hàng tiềm năng nào phù hợp.</td></tr>';
        return;
      }

      tbody.innerHTML = crmLeadsList.map(lead => {
        const hasSales = Boolean(lead.assigned_sales?.full_name || lead.assignedSales?.full_name);
        const assignedSalesName = hasSales
          ? `<i class="fa-solid fa-user-tie" style="color:#6366f1;font-size:12px;"></i> ${escapeHtml(lead.assigned_sales?.full_name || lead.assignedSales?.full_name)}`
          : `<span class="badge-filament danger" style="background:#fee2e2;color:#dc2626;border:1px solid #fca5a5;font-weight:700;"><i class="fa-solid fa-triangle-exclamation"></i> Chưa phân bổ</span>`;
        const buildingName = lead.building?.name || '<em style="color:#94a3b8;">Chưa chỉ định</em>';
        const areaNeeded = lead.expected_area ? `${lead.expected_area} m²` : '---';
        const contactName = lead.contact_name || lead.contact_full_name || 'Khách hàng';

        const isSales = (function() {
          if (typeof window.isSalesUser !== 'undefined') return Boolean(window.isSalesUser);
          const role = (window.currentUiRole || localStorage.getItem('auth_role') || '').toLowerCase();
          if (role === 'sales' || role === 'sale') return true;
          const sidebar = document.getElementById('bms-main-sidebar');
          if (sidebar) {
            const roleBadge = sidebar.querySelector('.filament-badge-role')?.textContent || '';
            if (/sales/i.test(roleBadge)) return true;
          }
          const topBadge = document.getElementById('admin-dropdown-role-badge')?.textContent || '';
          if (/sales/i.test(topBadge)) return true;
          return false;
        })();

        const isAdmin = !isSales && (window.location.pathname.startsWith('/admin') || (window.currentUiRole === 'admin') || (localStorage.getItem('auth_role') === 'admin'));

        let assignBtn = '';
        if (isAdmin) {
          assignBtn = !hasSales
            ? `<button class="btn-mo-primary" style="padding:4px 10px;font-size:12px;background:#2563eb;color:#fff;margin-left:4px;font-weight:600;border:none;border-radius:6px;cursor:pointer;" onclick="openAssignLeadSalesModal('${lead.id}', '${escapeHtml(contactName)}')" title="Gán Sales ngay"><i class="fa-solid fa-user-plus"></i> Gán Sales</button>`
            : `<button class="btn-mo-outline" style="padding:4px 8px;font-size:12px;color:#6366f1;border-color:#c7d2fe;margin-left:4px;" onclick="openAssignLeadSalesModal('${lead.id}', '${escapeHtml(contactName)}')" title="Đổi Sales phụ trách"><i class="fa-solid fa-user-gear"></i> Đổi</button>`;
        }

        const deleteBtn = isAdmin
          ? `<button class="btn-mo-outline" style="padding:4px 8px;font-size:12px;color:#ef4444;border-color:#fecaca;margin-left:4px;" onclick="deleteLead('${lead.id}')" title="Xóa"><i class="fa-solid fa-trash"></i></button>`
          : '';

        return `
          <tr>
            <td>
              <strong style="color:var(--mo-primary);font-family:monospace;font-size:13px;">${escapeHtml(lead.lead_code || lead.id.substring(0,8))}</strong>
              <div style="font-size:11px;color:#64748b;">${escapeHtml(new Date(lead.created_at).toLocaleDateString('vi-VN'))}</div>
            </td>
            <td>
              <div style="font-weight:600;color:#1e293b;">${escapeHtml(contactName)}</div>
              <div style="font-size:12px;color:#475569;">
                <i class="fa-solid fa-phone" style="font-size:11px;color:#94a3b8;"></i> ${escapeHtml(lead.contact_phone || '---')}
                ${lead.contact_email ? `| <i class="fa-solid fa-envelope" style="font-size:11px;color:#94a3b8;"></i> ${escapeHtml(lead.contact_email)}` : ''}
              </div>
              ${lead.company_name ? `<div style="font-size:12px;color:#0284c7;"><i class="fa-solid fa-building"></i> ${escapeHtml(lead.company_name)}</div>` : ''}
            </td>
            <td>
              <div style="font-weight:500;">${escapeHtml(buildingName)}</div>
              <div style="font-size:12px;color:#64748b;">Nhu cầu: ${escapeHtml(areaNeeded)}</div>
            </td>
            <td>${getPipelineStatusBadge(lead.pipeline_status)}</td>
            <td>
              <div style="font-size:13px;font-weight:500;color:#334155;">
                ${assignedSalesName}
              </div>
            </td>
            <td style="font-size:12px;color:#64748b;max-width:180px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
              ${escapeHtml(lead.notes || '---')}
            </td>
            <td style="text-align:right;white-space:nowrap;">
              <button class="btn-mo-outline" style="padding:4px 8px;font-size:12px;" onclick="openLeadDetailModal('${lead.id}')" title="Xem hồ sơ & Nhật ký">
                <i class="fa-solid fa-eye"></i> Chi tiết
              </button>
              ${assignBtn}
              ${lead.pipeline_status !== 'WON' ? `
                <button class="btn-mo-outline" style="padding:4px 8px;font-size:12px;color:#16a34a;border-color:#bbf7d0;margin-left:4px;" onclick="openConvertToTenantModal('${lead.id}', '${escapeHtml(contactName)}', '${escapeHtml(lead.company_name || '')}')" title="Chuyển đổi thành khách thuê">
                  <i class="fa-solid fa-arrow-right-to-bracket"></i> Chốt
                </button>
              ` : ''}
              ${deleteBtn}
            </td>
          </tr>
        `;
      }).join('');
    }

    // 3. Render bảng Kanban Pipeline (Kanban View)
    function renderCrmKanbanBoard() {
      const statuses = ['NEW', 'NEGOTIATING', 'WON', 'LOST'];
      statuses.forEach(st => {
        const colEl = document.getElementById(`kanban-col-${st.toLowerCase()}`);
        const countBadge = document.getElementById(`kanban-count-${st.toLowerCase()}`);
        if (!colEl) return;

        const filtered = crmLeadsList.filter(l => {
          if (st === 'NEW') {
            return l.pipeline_status === 'NEW' || l.pipeline_status === 'PENDING_ASSIGN';
          }
          return l.pipeline_status === st;
        });
        if (countBadge) countBadge.innerText = filtered.length;

        if (filtered.length === 0) {
          colEl.innerHTML = '<div style="font-size:12px;color:#94a3b8;text-align:center;padding:18px;border:1px dashed #cbd5e1;border-radius:6px;">Trống</div>';
          return;
        }

        colEl.innerHTML = filtered.map(lead => {
          let kanbanAction = '';
          if (lead.pipeline_status === 'WON') {
            kanbanAction = '<span class="badge-filament success" style="font-size:10.5px;font-weight:700;"><i class="fa-solid fa-check"></i> Đã chốt thuê</span>';
          } else {
            kanbanAction = `
              <select onchange="updateLeadStatus('${lead.id}', this.value)" style="font-size:11px;padding:2px 4px;border-radius:4px;border:1px solid #cbd5e1;background:#f8fafc;cursor:pointer;">
                <option value="NEW" ${lead.pipeline_status === 'NEW' ? 'selected' : ''}>Mới tiếp nhận</option>
                <option value="NEGOTIATING" ${lead.pipeline_status === 'NEGOTIATING' ? 'selected' : ''}>Đang đàm phán</option>
                <option value="WON" ${lead.pipeline_status === 'WON' ? 'selected' : ''}>Thành công (Chốt cọc)</option>
                <option value="LOST" ${lead.pipeline_status === 'LOST' ? 'selected' : ''}>Thất bại</option>
              </select>
            `;
          }

          return `
          <div class="kanban-lead-card" onclick="openLeadDetailModal('${lead.id}')" style="background:#fff;border:1px solid #e2e8f0;border-radius:8px;padding:12px;margin-bottom:10px;box-shadow:0 1px 3px rgba(0,0,0,0.05);cursor:pointer;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">
              <span style="font-size:11px;font-weight:700;color:var(--mo-primary);font-family:monospace;">${escapeHtml(lead.lead_code || lead.id.substring(0,8))}</span>
              <span style="font-size:11px;color:#94a3b8;">${escapeHtml(new Date(lead.created_at).toLocaleDateString('vi-VN'))}</span>
            </div>
            <div style="font-weight:600;color:#1e293b;font-size:13px;margin-bottom:4px;">${escapeHtml(lead.contact_name)}</div>
            ${lead.company_name ? `<div style="font-size:12px;color:#0284c7;margin-bottom:4px;"><i class="fa-solid fa-building"></i> ${escapeHtml(lead.company_name)}</div>` : ''}
            <div style="font-size:12px;color:#64748b;margin-bottom:8px;">
              <i class="fa-solid fa-location-dot" style="font-size:11px;"></i> ${escapeHtml(lead.building?.name || 'Tòa nhà tự do')}
              ${lead.expected_area ? ` - <strong>${lead.expected_area} m²</strong>` : ''}
            </div>
            <div style="display:flex;justify-content:space-between;align-items:center;border-top:1px solid #f1f5f9;padding-top:8px;margin-top:6px;">
              <span style="font-size:11px;color:#475569;">
                <i class="fa-solid fa-user-tie" style="font-size:10px;color:#6366f1;"></i> ${escapeHtml(lead.assigned_sales?.full_name || lead.assignedSales?.full_name || 'Chưa gán')}
              </span>
              <div onclick="event.stopPropagation();">
                ${kanbanAction}
              </div>
            </div>
          </div>
        `;
        }).join('');
      });
    }

    // 4. Mở Modal tạo Lead thủ công: POST /api/v1/crm/leads
    function openCreateLeadModal() {
      const modal = document.getElementById('modal-create-lead');
      if (!modal) return;
      document.getElementById('form-create-lead')?.reset();

      // Nạp danh sách tòa nhà vào select
      const bSelect = document.getElementById('create-lead-building');
      if (bSelect) {
        bSelect.innerHTML = '<option value="">-- Chọn Tòa Nhà Khách Quan Tâm --</option>' +
          BUILDINGS_DATA.map(b => `<option value="${b.id}">${escapeHtml(b.name)}</option>`).join('');
      }

      modal.style.display = 'flex';
    }

    function closeCreateLeadModal() {
      const modal = document.getElementById('modal-create-lead');
      if (modal) modal.style.display = 'none';
    }

    async function submitCreateLead(e) {
      if (e) e.preventDefault();
      const form = document.getElementById('form-create-lead');
      if (!form) return;

      moClearFieldError('lead-contact-name', 'err-lead-contact-name');
      moClearFieldError('lead-contact-phone', 'err-lead-contact-phone');
      moClearFieldError('lead-contact-email', 'err-lead-contact-email');
      moClearFieldError('lead-company-name', 'err-lead-company-name');
      moClearFieldError('create-lead-building', 'err-create-lead-building');
      moClearFieldError('lead-expected-area', 'err-lead-expected-area');
      moClearFieldError('lead-expected-budget', 'err-lead-expected-budget');

      const contactName = document.getElementById('lead-contact-name')?.value?.trim() || '';
      const contactPhone = document.getElementById('lead-contact-phone')?.value?.trim() || '';
      const contactEmail = document.getElementById('lead-contact-email')?.value?.trim() || '';
      const companyName = document.getElementById('lead-company-name')?.value?.trim() || '';
      const buildingId = document.getElementById('create-lead-building')?.value || null;
      const expectedAreaStr = document.getElementById('lead-expected-area')?.value?.trim() || '';
      const expectedBudgetStr = document.getElementById('lead-expected-budget')?.value?.trim() || '';
      const notes = document.getElementById('lead-notes')?.value?.trim() || null;
      const leadSource = document.getElementById('lead-source')?.value || 'DIRECT_CALL';

      let hasError = false;

      // 1. Validation 0ms
      const vName = moValidateFullName(contactName, 2);
      if (!vName.valid) {
        moSetFieldError('lead-contact-name', 'err-lead-contact-name', vName.message);
        document.getElementById('lead-contact-name')?.focus();
        hasError = true;
      }

      const vPhone = moValidatePhone(contactPhone);
      if (!vPhone.valid) {
        moSetFieldError('lead-contact-phone', 'err-lead-contact-phone', vPhone.message);
        if (!hasError) { document.getElementById('lead-contact-phone')?.focus(); hasError = true; }
      }

      if (contactEmail) {
        const vEmail = moValidateEmail(contactEmail);
        if (!vEmail.valid) {
          moSetFieldError('lead-contact-email', 'err-lead-contact-email', vEmail.message);
          if (!hasError) { document.getElementById('lead-contact-email')?.focus(); hasError = true; }
        }
      }

      let expectedArea = null;
      if (expectedAreaStr) {
        expectedArea = parseFloat(expectedAreaStr);
        if (isNaN(expectedArea) || expectedArea <= 0) {
          moSetFieldError('lead-expected-area', 'err-lead-expected-area', 'Diện tích cần thuê phải là số lớn hơn 0 m².');
          if (!hasError) { document.getElementById('lead-expected-area')?.focus(); hasError = true; }
        }
      }

      let expectedBudget = null;
      if (expectedBudgetStr) {
        expectedBudget = parseFloat(expectedBudgetStr);
        if (isNaN(expectedBudget) || expectedBudget <= 0) {
          moSetFieldError('lead-expected-budget', 'err-lead-expected-budget', 'Ngân sách tối đa phải là số lớn hơn 0.');
          if (!hasError) { document.getElementById('lead-expected-budget')?.focus(); hasError = true; }
        }
      }

      if (hasError) return;

      const validLeadSource = (leadSource === 'DIRECT_CALL' ? 'HOTLINE' : (leadSource === 'WALK_IN' ? 'DIRECT_WALK_IN' : leadSource)) || 'HOTLINE';
      const cleanEmail = contactEmail || `${contactPhone}@crmlead.local`;

      const payload = {
        contact_name: contactName,
        contact_full_name: contactName,
        contact_phone: contactPhone,
        phone: contactPhone,
        contact_email: cleanEmail,
        email: cleanEmail,
        company_name: companyName || null,
        building_id: buildingId,
        expected_area: expectedArea,
        desired_area_sqm: expectedArea,
        expected_budget: expectedBudget,
        budget_max: expectedBudget,
        notes: notes,
        customer_note: notes,
        lead_source: validLeadSource
      };

      const btn = document.getElementById('btn-submit-create-lead');
      const originalHtml = btn ? btn.innerHTML : '';
      if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Đang lưu hồ sơ...';
      }

      try {
        const res = await fetch('/api/v1/crm/leads', {
          method: 'POST',
          headers: getAuthHeaders(),
          body: JSON.stringify(payload)
        });

        const json = await res.json();
        if (res.ok) {
          moToast(`Đã tiếp nhận khách hàng tiềm năng [${contactName}] thành công!`, 'success');
          closeCreateLeadModal();
          form.reset();
          loadCrmLeads();
        } else {
          if (json.errors) {
            moMapServerErrors(json.errors, {
              contact_name: ['lead-contact-name', 'err-lead-contact-name'],
              contact_full_name: ['lead-contact-name', 'err-lead-contact-name'],
              contact_phone: ['lead-contact-phone', 'err-lead-contact-phone'],
              phone: ['lead-contact-phone', 'err-lead-contact-phone'],
              contact_email: ['lead-contact-email', 'err-lead-contact-email'],
              email: ['lead-contact-email', 'err-lead-contact-email'],
              company_name: ['lead-company-name', 'err-lead-company-name'],
              expected_area: ['lead-expected-area', 'err-lead-expected-area'],
              desired_area_sqm: ['lead-expected-area', 'err-lead-expected-area'],
              expected_budget: ['lead-expected-budget', 'err-lead-expected-budget'],
              budget_max: ['lead-expected-budget', 'err-lead-expected-budget']
            });
          }
          moToast(json.message || 'Dữ liệu không hợp lệ. Vui lòng kiểm tra lại.', 'error');
        }
      } catch (err) {
        moToast('Lỗi kết nối khi gửi dữ liệu tạo Lead: ' + (err.message || 'Lỗi mạng'), 'error');
      } finally {
        if (btn) {
          btn.disabled = false;
          btn.innerHTML = originalHtml;
        }
      }
    }

    // 5. Xem chi tiết Hồ sơ Lead + Lịch sử tương tác: GET /api/v1/crm/leads/{id} & /activities
    async function openLeadDetailModal(leadId) {
      currentViewingLeadId = leadId;
      const modal = document.getElementById('modal-lead-detail');
      if (!modal) return;
      modal.style.display = 'flex';

      const container = document.getElementById('lead-detail-content');
      if (container) {
        container.innerHTML = '<div style="text-align:center;padding:40px;color:#94a3b8;"><i class="fa-solid fa-spinner fa-spin"></i> Đang tải hồ sơ Lead từ API...</div>';
      }

      try {
        const res = await fetch(`/api/v1/crm/leads/${leadId}`, {
          method: 'GET',
          headers: getAuthHeaders()
        });

        const json = await res.json();
        if (!res.ok) {
          if (container) container.innerHTML = `<div style="color:#ef4444;text-align:center;">Lỗi: ${escapeHtml(json.message || 'Không tìm thấy Lead')}</div>`;
          return;
        }

        const lead = json.data;
        renderLeadDetailContent(lead);
        loadLeadActivities(leadId);
      } catch (err) {
        if (container) container.innerHTML = '<div style="color:#ef4444;text-align:center;">Lỗi kết nối máy chủ API.</div>';
      }
    }

    function closeLeadDetailModal() {
      const modal = document.getElementById('modal-lead-detail');
      if (modal) modal.style.display = 'none';
      if (window.leadStatusChanged && window.location.pathname.includes('/sales/dashboard')) {
        window.leadStatusChanged = false;
        window.location.reload();
      }
      currentViewingLeadId = null;
    }

    function renderLeadDetailContent(lead) {
      const container = document.getElementById('lead-detail-content');
      if (!container) return;

      container.innerHTML = `
        <div style="display:grid;grid-template-columns:1.2fr 1fr;gap:20px;">
          <!-- Cột trái: Thông tin liên hệ & Nhu cầu -->
          <div style="background:#f8fafc;padding:16px;border-radius:8px;border:1px solid #e2e8f0;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
              <div>
                <span style="font-family:monospace;font-weight:700;color:var(--mo-primary);font-size:14px;">${escapeHtml(lead.lead_code || lead.id)}</span>
                <div style="font-size:18px;font-weight:700;color:#0f172a;margin-top:2px;">${escapeHtml(lead.contact_name)}</div>
              </div>
              <div>${getPipelineStatusBadge(lead.pipeline_status)}</div>
            </div>

            <div style="display:flex;flex-direction:column;gap:8px;font-size:13px;color:#334155;">
              <div><strong>Số điện thoại:</strong> <a href="tel:${escapeHtml(lead.contact_phone)}" style="color:var(--mo-blue-accent);">${escapeHtml(lead.contact_phone)}</a></div>
              <div><strong>Email:</strong> ${lead.contact_email ? `<a href="mailto:${escapeHtml(lead.contact_email)}" style="color:var(--mo-blue-accent);">${escapeHtml(lead.contact_email)}</a>` : '<em>Chưa có</em>'}</div>
              <div><strong>Công ty:</strong> ${escapeHtml(lead.company_name || 'Khách cá nhân')}</div>
              <hr style="border:none;border-top:1px dashed #cbd5e1;margin:8px 0;">
              <div><strong>Tòa nhà quan tâm:</strong> ${escapeHtml(lead.building?.name || 'Tự do')}</div>
              <div><strong>Mặt bằng quan tâm:</strong> ${escapeHtml(lead.unit?.unit_code || 'Chưa chọn sàn')}</div>
              <div><strong>Diện tích cần thuê:</strong> ${lead.expected_area ? `${lead.expected_area} m²` : 'Chưa xác định'}</div>
              <div><strong>Ngân sách dự kiến:</strong> ${lead.expected_budget ? `$${lead.expected_budget.toLocaleString()}/tháng` : 'Thương lượng'}</div>
              <div><strong>Nhân viên phụ trách:</strong> <span style="color:#6366f1;font-weight:600;">${escapeHtml(lead.assigned_sales?.full_name || lead.assignedSales?.full_name || 'Chưa gán')}</span></div>
              <div><strong>Ghi chú:</strong> <span style="color:#64748b;">${escapeHtml(lead.notes || 'Không có')}</span></div>
            </div>

            <div style="margin-top:16px;padding-top:14px;border-top:1px solid #e2e8f0;display:flex;gap:8px;">
              ${lead.pipeline_status === 'WON' ? `
                <div style="flex:1;background:#dcfce7;color:#15803d;padding:8px 12px;border-radius:6px;font-weight:700;font-size:13px;display:flex;align-items:center;gap:6px;border:1px solid #bbf7d0;">
                  <i class="fa-solid fa-circle-check"></i> Đã Chốt Hợp Đồng Thành Công (WON)
                </div>
              ` : `
                <select id="detail-change-status" style="font-size:12.5px;padding:6px 10px;border-radius:6px;border:1.5px solid #cbd5e1;flex:1;color:#0f172a;background:#ffffff;font-weight:600;outline:none;">
                  <option value="NEW" ${lead.pipeline_status === 'NEW' ? 'selected' : ''}>Mới tiếp nhận (NEW)</option>
                  <option value="NEGOTIATING" ${lead.pipeline_status === 'NEGOTIATING' ? 'selected' : ''}>Đang đàm phán (NEGOTIATING)</option>
                  <option value="WON" ${lead.pipeline_status === 'WON' ? 'selected' : ''}>Thành công (Chốt cọc / WON)</option>
                  <option value="LOST" ${lead.pipeline_status === 'LOST' ? 'selected' : ''}>Thất bại (LOST)</option>
                </select>
                <button class="btn-mo-primary" style="padding:6px 14px;font-size:12.5px;font-weight:600;" onclick="applyLeadStatusChange('${lead.id}')">
                  Cập Nhật
                </button>
                <button class="btn-mo-secondary" style="background:#10b981;color:#fff;border:none;padding:6px 12px;font-size:12.5px;font-weight:600;display:inline-flex;align-items:center;gap:5px;border-radius:6px;cursor:pointer;" onclick="closeLeadDetailModal(); openConvertToTenantModal('${lead.id}', '${escapeHtml(lead.contact_name || '')}', '${escapeHtml(lead.company_name || '')}')">
                  <i class="fa-solid fa-trophy"></i> Chốt cọc
                </button>
              `}
            </div>

            ${lead.pipeline_status === 'WON' ? `
              <div style="margin-top:12px;">
                <button type="button" class="btn-mo-primary" style="width:100%;background:#16a34a;border:none;padding:9px 12px;font-size:12.5px;font-weight:700;display:flex;align-items:center;justify-content:center;gap:6px;border-radius:6px;cursor:pointer;" onclick="window.location.href='/admin/contracts?open_create=1&tenant_id=${lead.converted_tenant_id || ''}&building_id=${lead.building_id || ''}&unit_id=${lead.unit_id || ''}'">
                  <i class="fa-solid fa-file-signature"></i> Lập Hợp Đồng Thuê (Bản Nháp)
                </button>
              </div>
            ` : ''}
          </div>

          <!-- Cột phải: Timeline tương tác (Activities) -->
          <div>
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
              <h4 style="font-size:14px;font-weight:700;color:#0f172a;margin:0;">
                <i class="fa-solid fa-clock-rotate-left"></i> Nhật Ký Chăm Sóc
              </h4>
              <button class="btn-mo-orange" style="padding:4px 8px;font-size:11px;" onclick="toggleAddActivityForm()">
                <i class="fa-solid fa-plus"></i> Ghi chú mới
              </button>
            </div>

            <!-- Form thêm hoạt động nhanh -->
            <div id="add-activity-box" style="display:none;background:#fff;border:1px solid #cbd5e1;border-radius:6px;padding:12px;margin-bottom:12px;">
              <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:8px;">
                <select id="new-act-type" style="font-size:12px;padding:4px;border:1px solid #cbd5e1;border-radius:4px;">
                  <option value="CALL">Cuộc gọi điện</option>
                  <option value="EMAIL">Gửi Email</option>
                  <option value="MEETING">Gặp trực tiếp</option>
                  <option value="SITE_TOUR">Khảo sát sàn thực tế (Site tour)</option>
                  <option value="NOTE">Ghi chú nội bộ</option>
                  <option value="QUOTATION_SENT">Gửi Báo giá / LOI</option>
                </select>
                <input type="text" id="new-act-summary" placeholder="Tiêu đề (VD: Hẹn xem tầng 12)" style="font-size:12px;padding:4px;border:1px solid #cbd5e1;border-radius:4px;">
              </div>
              <textarea id="new-act-content" placeholder="Nội dung chi tiết cuộc trao đổi..." rows="2" style="width:100%;font-size:12px;padding:6px;border:1px solid #cbd5e1;border-radius:4px;margin-bottom:4px;" oninput="moClearFieldError('new-act-content', 'err-new-act-content')"></textarea>
              <div class="field-error-slot" id="err-new-act-content" style="margin-bottom:8px;"></div>
              <div style="text-align:right;">
                <button class="btn-mo-outline" style="padding:4px 8px;font-size:11px;" onclick="toggleAddActivityForm()">Hủy</button>
                <button class="btn-mo-primary" style="padding:4px 10px;font-size:11px;margin-left:4px;" onclick="submitNewLeadActivity('${lead.id}')">Lưu Nhật Ký</button>
              </div>
            </div>

            <!-- Danh sách timeline -->
            <div id="lead-activities-timeline" style="max-height:320px;overflow-y:auto;padding-right:4px;">
              <div style="text-align:center;color:#94a3b8;font-size:12px;padding:16px;">Đang tải nhật ký...</div>
            </div>
          </div>
        </div>
      `;
    }

    // Nạp danh sách hoạt động tương tác: GET /api/v1/crm/leads/{id}/activities
    async function loadLeadActivities(leadId) {
      const container = document.getElementById('lead-activities-timeline');
      if (!container) return;

      try {
        const res = await fetch(`/api/v1/crm/leads/${leadId}/activities`, {
          method: 'GET',
          headers: getAuthHeaders()
        });

        const json = await res.json();
        if (!res.ok) {
          container.innerHTML = `<div style="color:#ef4444;font-size:12px;">Không tải được nhật ký tương tác: ${escapeHtml(json.message || '')}</div>`;
          return;
        }

        const activities = json.data || [];
        if (activities.length === 0) {
          container.innerHTML = '<div style="color:#94a3b8;font-size:12px;text-align:center;padding:16px;">Chưa có hoạt động chăm sóc nào được ghi nhận.</div>';
          return;
        }

        container.innerHTML = activities.map(act => {
          let icon = 'fa-comment';
          let color = '#64748b';
          const actType = act.activity_type;
          if (actType === 'CALL') { icon = 'fa-phone'; color = '#0284c7'; }
          else if (actType === 'EMAIL') { icon = 'fa-envelope'; color = '#ea580c'; }
          else if (actType === 'SITE_TOUR' || actType === 'SITE_VISIT') { icon = 'fa-building-circle-check'; color = '#16a34a'; }
          else if (actType === 'MEETING') { icon = 'fa-handshake'; color = '#7c3aed'; }
          else if (actType === 'QUOTATION_SENT') { icon = 'fa-file-invoice-dollar'; color = '#d97706'; }

          const actTitle = act.title || act.summary || act.activity_type;
          const actTime = act.event_at || act.interaction_at || act.created_at;
          const salesName = act.sales?.full_name || act.actor?.full_name || act.user?.full_name || 'Chuyên viên Sales';

          return `
            <div style="display:flex;gap:10px;margin-bottom:12px;border-left:3px solid ${color};padding-left:10px;">
              <div>
                <div style="display:flex;justify-content:space-between;align-items:center;">
                  <strong style="font-size:12.5px;color:#1e293b;">
                    <i class="fa-solid ${icon}" style="color:${color};margin-right:4px;"></i> ${escapeHtml(actTitle)}
                  </strong>
                  <span style="font-size:11px;color:#94a3b8;">${escapeHtml(new Date(actTime).toLocaleString('vi-VN'))}</span>
                </div>
                <div style="font-size:12px;color:#475569;margin-top:3px;">${escapeHtml(act.content || 'Không có mô tả')}</div>
                <div style="font-size:11px;color:#94a3b8;margin-top:3px;">Ghi nhận bởi: <strong style="color:#475569;">${escapeHtml(salesName)}</strong></div>
              </div>
            </div>
          `;
        }).join('');
      } catch (err) {
        container.innerHTML = '<div style="color:#ef4444;font-size:12px;">Lỗi kết nối tải nhật ký tương tác.</div>';
      }
    }

    function toggleAddActivityForm() {
      const box = document.getElementById('add-activity-box');
      if (box) box.style.display = (box.style.display === 'none' ? 'block' : 'none');
    }

    // Gửi tạo mới hoạt động: POST /api/v1/crm/leads/{id}/activities
    async function submitNewLeadActivity(leadId) {
      const type = document.getElementById('new-act-type').value;
      const summary = document.getElementById('new-act-summary').value.trim();
      const content = document.getElementById('new-act-content').value.trim();

      moClearFieldError('new-act-content', 'err-new-act-content');

      const vContent = moValidateRequired(content, 'Nội dung chi tiết cuộc trao đổi');
      if (!vContent.valid) {
        moSetFieldError('new-act-content', 'err-new-act-content', vContent.message);
        document.getElementById('new-act-content')?.focus();
        return;
      }

      const defaultTitle = summary || (
        type === 'CALL' ? 'Cuộc gọi điện tư vấn nhu cầu' :
        (type === 'SITE_TOUR' ? 'Khảo sát thực tế sàn văn phòng' :
        (type === 'EMAIL' ? 'Gửi email báo giá và thông tin sàn' : 'Ghi nhận chăm sóc khách hàng'))
      );

      try {
        const res = await fetch(`/api/v1/crm/leads/${leadId}/activities`, {
          method: 'POST',
          headers: getAuthHeaders(),
          body: JSON.stringify({
            activity_type: type,
            title: defaultTitle,
            summary: defaultTitle,
            content: content,
            event_at: new Date().toISOString(),
            interaction_at: new Date().toISOString()
          })
        });

        const json = await res.json();
        if (res.ok) {
          moToast('Ghi nhận nhật ký chăm sóc thành công!', 'success');
          toggleAddActivityForm();
          loadLeadActivities(leadId);
          loadCrmLeads();
        } else {
          moToast(json.message || 'Dữ liệu nhật ký không hợp lệ.', 'error');
        }
      } catch (e) {
        moToast('Lỗi kết nối khi lưu nhật ký tương tác: ' + (e.message || 'Lỗi mạng'), 'error');
      }
    }

    // 6. Cập nhật trạng thái Pipeline: PATCH /api/v1/crm/leads/{id}/status
    async function updateLeadStatus(leadId, newStatus) {
      if (newStatus === 'WON') {
        const leadObj = (crmLeadsList || []).find(l => String(l.id) === String(leadId));
        const contactName = leadObj?.contact_name || leadObj?.contact_full_name || 'Khách hàng';
        const compName = leadObj?.company_name || '';
        moToast('Quy chuẩn CRM: Trạng thái Thành công (WON) chỉ được xác lập khi chuyển đổi sang Khách Thuê Doanh Nghiệp.', 'info');
        openConvertToTenantModal(leadId, contactName, compName);
        return;
      }

      let reasonCategory = 'KHAC';
      let reasonDetail = 'Khách hàng từ chối tiếp tục nhu cầu thuê.';

      if (newStatus === 'LOST') {
        const userInput = prompt('Vui lòng nhập lý do khách từ chối / Thất bại (hoặc để trống để dùng lý do mặc định):');
        if (userInput === null) {
          return;
        }
        if (userInput.trim()) {
          reasonDetail = userInput.trim();
        }
      }

      try {
        const payload = {
          status: newStatus,
          pipeline_status: newStatus,
          lost_reason_category: reasonCategory,
          lost_reason_detail: reasonDetail,
          lost_reason: reasonDetail
        };

        const res = await fetch(`/api/v1/crm/leads/${leadId}/status`, {
          method: 'PATCH',
          headers: getAuthHeaders(),
          body: JSON.stringify(payload)
        });

        const json = await res.json();
        if (res.ok) {
          window.leadStatusChanged = true;
          moToast(`Cập nhật trạng thái Lead thành công!`, 'success');
          const detailModal = document.getElementById('modal-lead-detail');
          if (detailModal && detailModal.style.display !== 'none' && currentViewingLeadId === leadId) {
            openLeadDetailModal(leadId);
          }
          if (typeof loadCrmLeads === 'function') {
            loadCrmLeads();
          }
        } else {
          moToast(json.message || 'Lỗi cập nhật trạng thái Lead.', 'error');
          if (typeof loadCrmLeads === 'function') {
            loadCrmLeads();
          }
        }
      } catch (e) {
        moToast('Lỗi kết nối khi cập nhật trạng thái.', 'error');
        if (typeof loadCrmLeads === 'function') {
          loadCrmLeads();
        }
      }
    }

    function applyLeadStatusChange(leadId) {
      const select = document.getElementById('detail-change-status');
      if (select) updateLeadStatus(leadId, select.value);
    }

    // 7. Phân công nhân viên Sales phụ trách Lead CRM: POST /api/v1/crm/leads/{id}/assign
    let currentAssignLeadId = null;
    async function openAssignLeadSalesModal(leadId, contactName) {
      currentAssignLeadId = leadId;
      const modal = document.getElementById('modal-assign-lead-sales');
      if (!modal) return;

      const nameEl = document.getElementById('assign-lead-contact-name');
      if (nameEl) nameEl.innerText = contactName;
      const sSelect = document.getElementById('assign-sales-select');
      sSelect.innerHTML = '<option value="">Đang tải danh sách Sales...</option>';
      modal.style.display = 'flex';

      try {
        const res = await fetch('/api/v1/admin/users/sales?per_page=100', {
          method: 'GET',
          headers: getAuthHeaders()
        });
        const json = await res.json();
        const salesList = json.data?.data || json.data || [];
        sSelect.innerHTML = '<option value="">-- Chọn Nhân Viên Sales Phụ Trách --</option>' +
          salesList.map(s => {
            const currentLoads = s.active_leads_count ?? s.leads_count ?? 0;
            const maxLoads = s.max_concurrent_leads ?? 10;
            const isFull = currentLoads >= maxLoads;
            return `<option value="${s.id}" ${isFull ? 'disabled style="color:#94a3b8;"' : ''}>${escapeHtml(s.full_name)} (${escapeHtml(s.email)}) - Đang xử lý: ${currentLoads}/${maxLoads} leads ${isFull ? '[ĐẦY TẢI]' : ''}</option>`;
          }).join('');
      } catch (e) {
        sSelect.innerHTML = '<option value="">Lỗi tải danh sách Sales</option>';
      }
    }

    window.openAssignLeadSalesModal = openAssignLeadSalesModal;
    window.closeAssignSalesModal = closeAssignSalesModal;

    // Hỗ trợ tương thích: Nếu gọi openAssignSalesModal với leadId thì gán cho Lead, nếu không thì phân công tòa nhà
    const _prevAssignSalesModal = window.openAssignSalesModal;
    window.openAssignSalesModal = function(leadId, contactName) {
      if (leadId) {
        return openAssignLeadSalesModal(leadId, contactName);
      }
      if (typeof window.openAssignBuildingSalesModal === 'function') {
        return window.openAssignBuildingSalesModal();
      }
      if (typeof _prevAssignSalesModal === 'function') {
        return _prevAssignSalesModal();
      }
    };

    function closeAssignSalesModal() {
      const modal = document.getElementById('modal-assign-lead-sales');
      if (modal) modal.style.display = 'none';
      currentAssignLeadId = null;
    }

    async function submitAssignLeadSales() {
      if (!currentAssignLeadId) return;
      const salesId = document.getElementById('assign-sales-select').value;
      if (!salesId) {
        moToast('Vui lòng chọn nhân viên Sales phụ trách.', 'warning');
        return;
      }

      try {
        const res = await fetch(`/api/v1/crm/leads/${currentAssignLeadId}/assign`, {
          method: 'POST',
          headers: getAuthHeaders(),
          body: JSON.stringify({ sales_id: salesId, assigned_sales_id: salesId })
        });

        const json = await res.json();
        if (res.ok) {
          moToast('Phân công Sales thành công!', 'success');
          closeAssignSalesModal();
          loadCrmLeads();
          if (currentViewingLeadId === currentAssignLeadId) {
            openLeadDetailModal(currentAssignLeadId);
          }
        } else {
          moToast('Lỗi phân công: ' + (json.message || 'Không thể phân công'), 'error');
        }
      } catch (e) {
        moToast('Lỗi kết nối khi phân công Sales: ' + (e.message || 'Lỗi mạng'), 'error');
      }
    }

    // 8. Chuyển đổi Lead thành Khách thuê chính thức: POST /api/v1/crm/leads/{id}/convert-to-tenant
    let currentConvertLeadId = null;
    function openConvertToTenantModal(leadId, contactName, companyName) {
      currentConvertLeadId = leadId;
      const modal = document.getElementById('modal-convert-lead');
      if (!modal) return;

      moClearFieldError('convert-company-name', 'err-convert-company-name');
      moClearFieldError('convert-tax-code', 'err-convert-tax-code');

      const leadNameEl = document.getElementById('convert-lead-name');
      if (leadNameEl) leadNameEl.innerText = contactName;
      document.getElementById('convert-company-name').value = companyName || '';
      document.getElementById('convert-tax-code').value = '';
      modal.style.display = 'flex';
    }

    function closeConvertToTenantModal() {
      const modal = document.getElementById('modal-convert-lead');
      if (modal) modal.style.display = 'none';
      moClearFieldError('convert-company-name', 'err-convert-company-name');
      moClearFieldError('convert-tax-code', 'err-convert-tax-code');
      currentConvertLeadId = null;
      if (typeof renderCrmKanbanBoard === 'function') {
        renderCrmKanbanBoard();
      }
    }

    async function submitConvertToTenant() {
      if (!currentConvertLeadId) return;
      const compName = document.getElementById('convert-company-name')?.value?.trim() || '';
      const taxCode = document.getElementById('convert-tax-code')?.value?.trim() || '';

      moClearFieldError('convert-company-name', 'err-convert-company-name');
      moClearFieldError('convert-tax-code', 'err-convert-tax-code');

      let hasErr = false;
      const vComp = moValidateRequired(compName, 'Tên doanh nghiệp / Công ty');
      if (!vComp.valid) {
        moSetFieldError('convert-company-name', 'err-convert-company-name', vComp.message);
        document.getElementById('convert-company-name')?.focus();
        hasErr = true;
      }

      const vTax = moValidateRequired(taxCode, 'Mã số thuế (MST)');
      if (!vTax.valid) {
        moSetFieldError('convert-tax-code', 'err-convert-tax-code', vTax.message);
        if (!hasErr) {
          document.getElementById('convert-tax-code')?.focus();
          hasErr = true;
        }
      }

      if (hasErr) return;

      try {
        const res = await fetch(`/api/v1/crm/leads/${currentConvertLeadId}/convert-to-tenant`, {
          method: 'POST',
          headers: getAuthHeaders(),
          body: JSON.stringify({
            company_name: compName,
            tax_code: taxCode
          })
        });

        const json = await res.json();
        if (res.ok) {
          moToast('Chuyển đổi sang Khách Thuê Doanh Nghiệp thành công!', 'success');
          const tenant = json.data;
          const leadObj = (crmLeadsList || []).find(l => String(l.id) === String(currentConvertLeadId));
          closeConvertToTenantModal();
          loadCrmLeads();

          showLeadConvertedCredentialModal(tenant, leadObj);
        } else {
          if (json.errors) {
            moMapServerErrors(json.errors, {
              company_name: { inputId: 'convert-company-name', slotId: 'err-convert-company-name' },
              tax_code: { inputId: 'convert-tax-code', slotId: 'err-convert-tax-code' }
            });
          }
          moToast(json.message || 'Dữ liệu chuyển đổi không hợp lệ. Vui lòng kiểm tra lại.', 'error');
        }
      } catch (e) {
        moToast('Lỗi kết nối máy chủ khi chuyển đổi Lead: ' + (e.message || 'Lỗi mạng'), 'error');
      }
    }

    // Modal hiển thị thông tin tài khoản Khách Thuê sau khi chuyển đổi thành công
    let currentConvertedTenantData = null;
    function showLeadConvertedCredentialModal(tenant, leadObj) {
      currentConvertedTenantData = tenant;
      const modal = document.getElementById('modal-lead-converted-credential');
      if (!modal) return;

      document.getElementById('cred-lead-code').innerText = tenant.tenant_code || '---';
      document.getElementById('cred-lead-company').innerText = tenant.company_name || '---';
      document.getElementById('cred-lead-email').innerText = tenant.login_email || '---';
      document.getElementById('cred-lead-phone').innerText = tenant.contact_phone || '---';
      document.getElementById('cred-lead-password').innerText = tenant.initial_plain_password || '******';

      const gotoBtn = document.getElementById('btn-lead-goto-contract');
      if (gotoBtn) {
        gotoBtn.onclick = function() {
          const bldId = leadObj?.building_id || '';
          const untId = leadObj?.unit_id || '';
          window.location.href = `/admin/contracts?open_create=1&tenant_id=${tenant.id}&building_id=${bldId}&unit_id=${untId}`;
        };
      }

      modal.style.display = 'flex';
    }

    function closeLeadCredentialModal() {
      const modal = document.getElementById('modal-lead-converted-credential');
      if (modal) modal.style.display = 'none';
      currentConvertedTenantData = null;
    }

    function copyLeadCredential() {
      if (!currentConvertedTenantData) return;
      const text = `THÔNG TIN TÀI KHOẢN CỔNG KHÁCH THUÊ (TENANT PORTAL)\n` +
        `- Doanh nghiệp: ${currentConvertedTenantData.company_name}\n` +
        `- Mã khách thuê: ${currentConvertedTenantData.tenant_code}\n` +
        `- Email đăng nhập: ${currentConvertedTenantData.login_email}\n` +
        `- Mật khẩu khởi tạo: ${currentConvertedTenantData.initial_plain_password || 'Đã cấp mật khẩu an toàn'}\n` +
        `- Đường link đăng nhập: ${window.location.origin}/portal/login`;

      if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(text).then(() => {
          moToast('Đã sao chép thông tin tài khoản vào bộ nhớ tạm!', 'success');
        });
      } else {
        prompt('Sao chép thông tin tài khoản bên dưới:', text);
      }
    }

    // 9. Xóa mềm Lead: DELETE /api/v1/crm/leads/{id}
    async function deleteLead(leadId) {
      if (!confirm('Bạn có chắc chắn muốn xóa hồ sơ khách hàng tiềm năng này?')) return;

      try {
        const res = await fetch(`/api/v1/crm/leads/${leadId}`, {
          method: 'DELETE',
          headers: getAuthHeaders()
        });

        const json = await res.json();
        if (res.ok) {
          moToast('Đã xóa Lead thành công.', 'success');
          loadCrmLeads();
        } else {
          moToast('Lỗi xóa Lead: ' + (json.message || ''), 'error');
        }
      } catch (e) {
        moToast('Lỗi kết nối khi xóa Lead: ' + (e.message || 'Lỗi mạng'), 'error');
      }
    }

    // 10. Bộ lọc & Tìm kiếm nhanh
    function doCrmSearch() {
      const inp = document.getElementById('crm-search-input');
      handleCrmSearch(inp ? inp.value : '');
    }

    function handleCrmSearch(val) {
      currentCrmSearch = val.trim();
      loadCrmLeads();
    }

    function handleCrmStatusFilter(status) {
      currentCrmStatusFilter = status;
      const sel = document.getElementById('crm-status-select');
      if (sel && sel.value !== status) sel.value = status;
      document.querySelectorAll('.crm-status-btn').forEach(b => {
        b.classList.remove('active');
        if (b.getAttribute('data-status') === status) b.classList.add('active');
      });
      loadCrmLeads();
    }

    function resetCrmFilters() {
      const inp = document.getElementById('crm-search-input');
      if (inp) inp.value = '';
      const clrBtn = document.getElementById('btn-clear-crm-search');
      if (clrBtn) clrBtn.style.display = 'none';
      const sel = document.getElementById('crm-status-select');
      if (sel) sel.value = 'ALL';
      currentCrmSearch = '';
      currentCrmStatusFilter = 'ALL';
      document.querySelectorAll('.crm-status-btn').forEach(b => {
        b.classList.remove('active');
        if (b.getAttribute('data-status') === 'ALL') b.classList.add('active');
      });
      loadCrmLeads();
    }

    window.handleCrmStatusFilter = handleCrmStatusFilter;
    window.resetCrmFilters = resetCrmFilters;
    window.openLeadDetailModal = openLeadDetailModal;
    window.openCrmDetailModal = openLeadDetailModal;
    window.closeLeadDetailModal = closeLeadDetailModal;
    window.closeLeadCredentialModal = closeLeadCredentialModal;
    window.copyLeadCredential = copyLeadCredential;
</script>
