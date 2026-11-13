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
        case 'NEW':
          return '<span class="badge-filament info"><i class="fa-solid fa-sparkles"></i> Mới tiếp nhận</span>';
        case 'CONTACTED':
          return '<span class="badge-filament primary"><i class="fa-solid fa-phone"></i> Đã liên hệ</span>';
        case 'SITE_VISIT':
          return '<span class="badge-filament warning"><i class="fa-solid fa-building-circle-check"></i> Đã đi xem sàn</span>';
        case 'NEGOTIATING':
          return '<span class="badge-filament purple" style="background:#f3e8ff;color:#7e22ce;"><i class="fa-solid fa-handshake"></i> Đang đàm phán</span>';
        case 'WON':
          return '<span class="badge-filament success"><i class="fa-solid fa-trophy"></i> Thành công (Won)</span>';
        case 'LOST':
          return '<span class="badge-filament danger"><i class="fa-solid fa-ban"></i> Thất bại (Lost)</span>';
        default:
          return `<span class="badge-filament">${escapeHtml(status)}</span>`;
      }
    }

    // 2. Render bảng danh sách Lead (Table View)
    function renderCrmLeadsTable() {
      const tbody = document.getElementById('crm-leads-tbody');
      const countEl = document.getElementById('crm-leads-count');
      if (countEl) countEl.innerText = crmLeadsList.length;

      if (!tbody) return;
      if (crmLeadsList.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;padding:32px;color:#94a3b8;">Không tìm thấy khách hàng tiềm năng nào phù hợp.</td></tr>';
        return;
      }

      tbody.innerHTML = crmLeadsList.map(lead => {
        const assignedSalesName = lead.assigned_sales?.full_name || lead.assignedSales?.full_name || '<em style="color:#94a3b8;">Chưa phân công</em>';
        const buildingName = lead.building?.name || '<em style="color:#94a3b8;">Chưa chỉ định</em>';
        const areaNeeded = lead.expected_area ? `${lead.expected_area} m²` : '---';

        return `
          <tr>
            <td>
              <strong style="color:var(--mo-primary);font-family:monospace;font-size:13px;">${escapeHtml(lead.lead_code || lead.id.substring(0,8))}</strong>
              <div style="font-size:11px;color:#64748b;">${escapeHtml(new Date(lead.created_at).toLocaleDateString('vi-VN'))}</div>
            </td>
            <td>
              <div style="font-weight:600;color:#1e293b;">${escapeHtml(lead.contact_name)}</div>
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
                <i class="fa-solid fa-user-tie" style="color:#6366f1;font-size:12px;"></i> ${assignedSalesName}
              </div>
            </td>
            <td style="font-size:12px;color:#64748b;max-width:180px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
              ${escapeHtml(lead.notes || '---')}
            </td>
            <td style="text-align:right;white-space:nowrap;">
              <button class="btn-mo-outline" style="padding:4px 8px;font-size:12px;" onclick="openLeadDetailModal('${lead.id}')" title="Xem hồ sơ & Nhật ký">
                <i class="fa-solid fa-eye"></i> Chi tiết
              </button>
              <button class="btn-mo-outline" style="padding:4px 8px;font-size:12px;color:#6366f1;border-color:#c7d2fe;margin-left:4px;" onclick="openAssignSalesModal('${lead.id}', '${escapeHtml(lead.contact_name)}')" title="Phân công Sales">
                <i class="fa-solid fa-user-plus"></i> Gán
              </button>
              ${lead.pipeline_status !== 'WON' ? `
                <button class="btn-mo-outline" style="padding:4px 8px;font-size:12px;color:#16a34a;border-color:#bbf7d0;margin-left:4px;" onclick="openConvertToTenantModal('${lead.id}', '${escapeHtml(lead.contact_name)}', '${escapeHtml(lead.company_name || '')}')" title="Chuyển đổi thành khách thuê">
                  <i class="fa-solid fa-arrow-right-to-bracket"></i> Chốt
                </button>
              ` : ''}
              <button class="btn-mo-outline" style="padding:4px 8px;font-size:12px;color:#ef4444;border-color:#fecaca;margin-left:4px;" onclick="deleteLead('${lead.id}')" title="Xóa">
                <i class="fa-solid fa-trash"></i>
              </button>
            </td>
          </tr>
        `;
      }).join('');
    }

    // 3. Render bảng Kanban Pipeline (Kanban View)
    function renderCrmKanbanBoard() {
      const statuses = ['NEW', 'CONTACTED', 'SITE_VISIT', 'NEGOTIATING', 'WON', 'LOST'];
      statuses.forEach(st => {
        const colEl = document.getElementById(`kanban-col-${st.toLowerCase()}`);
        const countBadge = document.getElementById(`kanban-count-${st.toLowerCase()}`);
        if (!colEl) return;

        const filtered = crmLeadsList.filter(l => l.pipeline_status === st);
        if (countBadge) countBadge.innerText = filtered.length;

        if (filtered.length === 0) {
          colEl.innerHTML = '<div style="font-size:12px;color:#94a3b8;text-align:center;padding:18px;border:1px dashed #cbd5e1;border-radius:6px;">Trống</div>';
          return;
        }

        colEl.innerHTML = filtered.map(lead => `
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
                <select onchange="updateLeadStatus('${lead.id}', this.value)" style="font-size:11px;padding:2px 4px;border-radius:4px;border:1px solid #cbd5e1;background:#f8fafc;">
                  <option value="NEW" ${lead.pipeline_status === 'NEW' ? 'selected' : ''}>Mới</option>
                  <option value="CONTACTED" ${lead.pipeline_status === 'CONTACTED' ? 'selected' : ''}>Đã gọi</option>
                  <option value="SITE_VISIT" ${lead.pipeline_status === 'SITE_VISIT' ? 'selected' : ''}>Xem sàn</option>
                  <option value="NEGOTIATING" ${lead.pipeline_status === 'NEGOTIATING' ? 'selected' : ''}>Đàm phán</option>
                  <option value="WON" ${lead.pipeline_status === 'WON' ? 'selected' : ''}>Thành công</option>
                  <option value="LOST" ${lead.pipeline_status === 'LOST' ? 'selected' : ''}>Thất bại</option>
                </select>
              </div>
            </div>
          </div>
        `).join('');
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

      const payload = {
        contact_name: document.getElementById('lead-contact-name').value.trim(),
        contact_phone: document.getElementById('lead-contact-phone').value.trim(),
        contact_email: document.getElementById('lead-contact-email').value.trim() || null,
        company_name: document.getElementById('lead-company-name').value.trim() || null,
        building_id: document.getElementById('create-lead-building').value || null,
        expected_area: parseFloat(document.getElementById('lead-expected-area').value) || null,
        expected_budget: parseFloat(document.getElementById('lead-expected-budget').value) || null,
        notes: document.getElementById('lead-notes').value.trim() || null,
        lead_source: document.getElementById('lead-source').value || 'DIRECT_CALL'
      };

      if (!payload.contact_name || !payload.contact_phone) {
        alert('Vui lòng nhập Họ tên và Số điện thoại khách hàng.');
        return;
      }

      try {
        const res = await fetch('/api/v1/crm/leads', {
          method: 'POST',
          headers: getAuthHeaders(),
          body: JSON.stringify(payload)
        });

        const json = await res.json();
        if (res.ok) {
          alert('Tạo khách hàng tiềm năng thành công!');
          closeCreateLeadModal();
          loadCrmLeads();
        } else {
          alert('Lỗi tạo Lead: ' + (json.message || 'Dữ liệu không hợp lệ.'));
        }
      } catch (err) {
        alert('Lỗi kết nối khi gửi dữ liệu tạo Lead.');
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
              <select id="detail-change-status" style="font-size:12px;padding:6px;border-radius:4px;border:1px solid #cbd5e1;flex:1;">
                <option value="NEW" ${lead.pipeline_status === 'NEW' ? 'selected' : ''}>Mới (NEW)</option>
                <option value="CONTACTED" ${lead.pipeline_status === 'CONTACTED' ? 'selected' : ''}>Đã liên hệ (CONTACTED)</option>
                <option value="SITE_VISIT" ${lead.pipeline_status === 'SITE_VISIT' ? 'selected' : ''}>Đã xem sàn (SITE_VISIT)</option>
                <option value="NEGOTIATING" ${lead.pipeline_status === 'NEGOTIATING' ? 'selected' : ''}>Đang đàm phán (NEGOTIATING)</option>
                <option value="WON" ${lead.pipeline_status === 'WON' ? 'selected' : ''}>Thành công (WON)</option>
                <option value="LOST" ${lead.pipeline_status === 'LOST' ? 'selected' : ''}>Thất bại (LOST)</option>
              </select>
              <button class="btn-mo-primary" style="padding:6px 12px;font-size:12px;" onclick="applyLeadStatusChange('${lead.id}')">
                Cập Nhật
              </button>
            </div>
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
                  <option value="SITE_VISIT">Khảo sát sàn</option>
                  <option value="NOTE">Ghi chú nội bộ</option>
                </select>
                <input type="text" id="new-act-summary" placeholder="Tiêu đề (VD: Hẹn xem tầng 12)" style="font-size:12px;padding:4px;border:1px solid #cbd5e1;border-radius:4px;">
              </div>
              <textarea id="new-act-content" placeholder="Nội dung chi tiết cuộc trao đổi..." rows="2" style="width:100%;font-size:12px;padding:6px;border:1px solid #cbd5e1;border-radius:4px;margin-bottom:8px;"></textarea>
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
          if (act.activity_type === 'CALL') { icon = 'fa-phone'; color = '#0284c7'; }
          else if (act.activity_type === 'EMAIL') { icon = 'fa-envelope'; color = '#ea580c'; }
          else if (act.activity_type === 'SITE_VISIT') { icon = 'fa-building-circle-check'; color = '#16a34a'; }
          else if (act.activity_type === 'MEETING') { icon = 'fa-handshake'; color = '#7c3aed'; }

          return `
            <div style="display:flex;gap:10px;margin-bottom:12px;border-left:2px solid ${color};padding-left:10px;">
              <div>
                <div style="display:flex;justify-content:space-between;align-items:center;">
                  <strong style="font-size:12px;color:#1e293b;">
                    <i class="fa-solid ${icon}" style="color:${color};margin-right:4px;"></i> ${escapeHtml(act.summary || act.activity_type)}
                  </strong>
                  <span style="font-size:11px;color:#94a3b8;">${escapeHtml(new Date(act.interaction_at || act.created_at).toLocaleString('vi-VN'))}</span>
                </div>
                <div style="font-size:12px;color:#475569;margin-top:2px;">${escapeHtml(act.content || 'Không có mô tả')}</div>
                <div style="font-size:11px;color:#94a3b8;margin-top:2px;">Ghi nhận bởi: <strong>${escapeHtml(act.actor?.full_name || act.user?.full_name || 'Hệ thống')}</strong></div>
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

      if (!content) {
        alert('Vui lòng nhập nội dung chi tiết tương tác.');
        return;
      }

      try {
        const res = await fetch(`/api/v1/crm/leads/${leadId}/activities`, {
          method: 'POST',
          headers: getAuthHeaders(),
          body: JSON.stringify({
            activity_type: type,
            summary: summary || null,
            content: content,
            interaction_at: new Date().toISOString()
          })
        });

        const json = await res.json();
        if (res.ok) {
          toggleAddActivityForm();
          loadLeadActivities(leadId);
        } else {
          alert('Lỗi lưu nhật ký: ' + (json.message || 'Dữ liệu không hợp lệ.'));
        }
      } catch (e) {
        alert('Lỗi kết nối khi lưu nhật ký tương tác.');
      }
    }

    // 6. Cập nhật trạng thái Pipeline: PATCH /api/v1/crm/leads/{id}/status
    async function updateLeadStatus(leadId, newStatus) {
      let reason = null;
      if (newStatus === 'LOST') {
        reason = prompt('Vui lòng nhập lý do khách từ chối / Thất bại:');
        if (reason === null) {
          loadCrmLeads();
          return;
        }
      }

      try {
        const res = await fetch(`/api/v1/crm/leads/${leadId}/status`, {
          method: 'PATCH',
          headers: getAuthHeaders(),
          body: JSON.stringify({
            pipeline_status: newStatus,
            lost_reason: reason
          })
        });

        const json = await res.json();
        if (res.ok) {
          loadCrmLeads();
        } else {
          alert('Lỗi cập nhật trạng thái Lead: ' + (json.message || ''));
          loadCrmLeads();
        }
      } catch (e) {
        alert('Lỗi kết nối khi cập nhật trạng thái.');
        loadCrmLeads();
      }
    }

    function applyLeadStatusChange(leadId) {
      const select = document.getElementById('detail-change-status');
      if (select) updateLeadStatus(leadId, select.value);
    }

    // 7. Phân công nhân viên Sales phụ trách: POST /api/v1/crm/leads/{id}/assign
    let currentAssignLeadId = null;
    async function openAssignSalesModal(leadId, contactName) {
      currentAssignLeadId = leadId;
      const modal = document.getElementById('modal-assign-lead-sales');
      if (!modal) return;

      document.getElementById('assign-lead-contact-name').innerText = contactName;
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
          salesList.map(s => `<option value="${s.id}">${escapeHtml(s.full_name)} (${escapeHtml(s.email)})</option>`).join('');
      } catch (e) {
        sSelect.innerHTML = '<option value="">Lỗi tải danh sách Sales</option>';
      }
    }

    function closeAssignSalesModal() {
      const modal = document.getElementById('modal-assign-lead-sales');
      if (modal) modal.style.display = 'none';
      currentAssignLeadId = null;
    }

    async function submitAssignLeadSales() {
      if (!currentAssignLeadId) return;
      const salesId = document.getElementById('assign-sales-select').value;
      if (!salesId) {
        alert('Vui lòng chọn nhân viên Sales.');
        return;
      }

      try {
        const res = await fetch(`/api/v1/crm/leads/${currentAssignLeadId}/assign`, {
          method: 'POST',
          headers: getAuthHeaders(),
          body: JSON.stringify({ assigned_sales_id: salesId })
        });

        const json = await res.json();
        if (res.ok) {
          alert('Phân công Sales thành công!');
          closeAssignSalesModal();
          loadCrmLeads();
          if (currentViewingLeadId === currentAssignLeadId) {
            openLeadDetailModal(currentAssignLeadId);
          }
        } else {
          alert('Lỗi phân công: ' + (json.message || ''));
        }
      } catch (e) {
        alert('Lỗi kết nối khi phân công Sales.');
      }
    }

    // 8. Chuyển đổi Lead thành Khách thuê chính thức: POST /api/v1/crm/leads/{id}/convert-to-tenant
    let currentConvertLeadId = null;
    function openConvertToTenantModal(leadId, contactName, companyName) {
      currentConvertLeadId = leadId;
      const modal = document.getElementById('modal-convert-lead');
      if (!modal) return;

      document.getElementById('convert-lead-name').innerText = contactName;
      document.getElementById('convert-company-name').value = companyName || '';
      document.getElementById('convert-tax-code').value = '';
      modal.style.display = 'flex';
    }

    function closeConvertToTenantModal() {
      const modal = document.getElementById('modal-convert-lead');
      if (modal) modal.style.display = 'none';
      currentConvertLeadId = null;
    }

    async function submitConvertToTenant() {
      if (!currentConvertLeadId) return;
      const compName = document.getElementById('convert-company-name').value.trim();
      const taxCode = document.getElementById('convert-tax-code').value.trim();

      if (!compName) {
        alert('Vui lòng nhập Tên doanh nghiệp/Công ty.');
        return;
      }

      try {
        const res = await fetch(`/api/v1/crm/leads/${currentConvertLeadId}/convert-to-tenant`, {
          method: 'POST',
          headers: getAuthHeaders(),
          body: JSON.stringify({
            company_name: compName,
            tax_code: taxCode || null
          })
        });

        const json = await res.json();
        if (res.ok) {
          alert('Chúc mừng! Đã chốt Deal và chuyển đổi Lead thành Khách Thuê thành công!');
          closeConvertToTenantModal();
          loadCrmLeads();
        } else {
          alert('Lỗi chuyển đổi: ' + (json.message || ''));
        }
      } catch (e) {
        alert('Lỗi kết nối máy chủ khi chuyển đổi Lead.');
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
          alert('Đã xóa Lead thành công.');
          loadCrmLeads();
        } else {
          alert('Lỗi xóa Lead: ' + (json.message || ''));
        }
      } catch (e) {
        alert('Lỗi kết nối khi xóa Lead.');
      }
    }

    // 10. Bộ lọc & Tìm kiếm nhanh
    function handleCrmSearch(val) {
      currentCrmSearch = val.trim();
      loadCrmLeads();
    }

    function handleCrmStatusFilter(status) {
      currentCrmStatusFilter = status;
      document.querySelectorAll('.crm-status-btn').forEach(b => {
        b.classList.remove('active');
        if (b.getAttribute('data-status') === status) b.classList.add('active');
      });
      loadCrmLeads();
    }
</script>
