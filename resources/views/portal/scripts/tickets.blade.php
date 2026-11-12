<script>
    /* ==========================================================================
       MODULE 4: TICKETS & MAINTENANCE CLIENT LOGIC (ENDPOINTS 57 - 66)
       ========================================================================== */
    let CURRENT_TICKETS_CACHE = [];
    let ACTIVE_TICKET_DETAIL = null;
    let CSAT_SELECTED_STAR = 5;

    // Helper: Định dạng nhãn và badge trạng thái
    function getTicketStatusMeta(status) {
      const s = String(status).toUpperCase();
      switch (s) {
        case 'NEW':
        case 'OPEN':
          return { label: 'Mới tiếp nhận', class: 'status-NEW', badge: 'info', icon: 'fa-circle-dot' };
        case 'IN_PROGRESS':
          return { label: 'Đang xử lý', class: 'status-IN_PROGRESS', badge: 'warning', icon: 'fa-screwdriver-wrench' };
        case 'ON_HOLD':
          return { label: 'Tạm hoãn', class: 'status-ON_HOLD', badge: 'warning', icon: 'fa-pause' };
        case 'WAITING_CONFIRMATION':
        case 'RESOLVED':
          return { label: 'Chờ nghiệm thu', class: 'status-WAITING_CONFIRMATION', badge: 'primary', icon: 'fa-clipboard-check' };
        case 'COMPLETED':
        case 'CLOSED':
          return { label: 'Hoàn tất', class: 'status-COMPLETED', badge: 'success', icon: 'fa-check-double' };
        case 'CANCELLED':
          return { label: 'Đã hủy', class: 'status-CANCELLED', badge: 'danger', icon: 'fa-ban' };
        default:
          return { label: status || 'Không rõ', class: 'status-NEW', badge: 'info', icon: 'fa-circle' };
      }
    }

    function getTicketPriorityMeta(priority) {
      const p = String(priority).toUpperCase();
      switch (p) {
        case 'URGENT':
          return { label: 'Khẩn cấp', class: 'priority-URGENT', badge: 'danger' };
        case 'HIGH':
          return { label: 'Cao', class: 'priority-HIGH', badge: 'warning' };
        case 'NORMAL':
          return { label: 'Bình thường', class: 'priority-NORMAL', badge: 'info' };
        case 'LOW':
          return { label: 'Thấp', class: 'priority-LOW', badge: 'secondary' };
        default:
          return { label: priority || 'Thường', class: 'priority-NORMAL', badge: 'info' };
      }
    }

    function getTicketCategoryLabel(category) {
      const c = String(category).toUpperCase();
      switch (c) {
        case 'MAINTENANCE': return 'Sửa chữa kỹ thuật / Điện lạnh';
        case 'OVERTIME_CONSTRUCTION': return 'Đăng ký thi công ngoài giờ';
        case 'SECURITY_CLEANING': return 'Vệ sinh & An ninh tòa nhà';
        case 'SERVICE_COMPLAINT': return 'Ý kiến đóng góp & Phản ánh';
        default: return category || 'Kỹ thuật';
      }
    }

    // 57. GET /api/v1/tickets: Tải danh sách phiếu yêu cầu sự cố
    async function loadTicketsList(viewType = 'all') {
      const token = localStorage.getItem('auth_token');
      const kw = document.getElementById("ticket-filter-keyword")?.value?.trim() || '';
      const st = document.getElementById("ticket-filter-status")?.value || '';
      const pr = document.getElementById("ticket-filter-priority")?.value || '';

      const queryParams = new URLSearchParams();
      if (kw) queryParams.append('keyword', kw);
      if (st) queryParams.append('status', st);
      if (pr) queryParams.append('priority', pr);
      queryParams.append('per_page', '50');

      let fetchedList = null;

      if (token) {
        try {
          const res = await fetch(`/api/v1/tickets?` + queryParams.toString(), {
            headers: {
              'Authorization': `Bearer ` + token,
              'Accept': 'application/json'
            }
          });
          if (res.ok) {
            const json = await res.json();
            fetchedList = json.data?.data || json.data || [];
            CURRENT_TICKETS_CACHE = fetchedList;
          }
        } catch (e) {
          console.error("Error fetching tickets from API:", e);
        }
      }

      const listToRender = fetchedList || CURRENT_TICKETS_CACHE;
      renderAdminTicketsTable(listToRender);
      renderTenantTicketsTable(listToRender);
    }

    function renderAdminTicketsTable(items) {
      const tbody = document.getElementById("admin-tickets-tbody");
      if (!tbody) return;

      if (!items || items.length === 0) {
        tbody.innerHTML = `<tr><td colspan="8" style="text-align:center;padding:24px;color:#94a3b8;"><i class="fa-solid fa-inbox" style="font-size:28px;margin-bottom:8px;display:block;"></i>Không có phiếu yêu cầu nào phù hợp.</td></tr>`;
        return;
      }

      tbody.innerHTML = items.map(t => {
        const id = t.id;
        const code = t.ticket_code || ('#TK-' + (t.id || '2026'));
        const tenantName = t.tenant?.company_name || t.tenant?.representative_name || t.tenant || 'Khách thuê';
        const buildingName = t.building?.name || t.location || 'Tòa nhà văn phòng';
        const floorUnit = t.unit ? (`Tầng ` + t.unit.floor_number + ` (` + t.unit.unit_code + `)`) : '';
        const title = t.title || 'Sự cố bảo trì';
        const pmName = t.assigned_pm?.full_name || (t.assigned_pm_id ? 'Đã phân công' : '<span style="color:#94a3b8;">Chưa giao</span>');
        const stMeta = getTicketStatusMeta(t.status?.value || t.status);
        const prMeta = getTicketPriorityMeta(t.priority?.value || t.priority);

        return `
          <tr>
            <td><strong style="color:#0f3361;cursor:pointer;" onclick="openTicketDetail('` + id + `')">` + code + `</strong></td>
            <td><strong>` + tenantName + `</strong></td>
            <td><div style="font-weight:600;">` + buildingName + `</div><div style="font-size:11px;color:#64748b;">` + floorUnit + `</div></td>
            <td style="max-width:240px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="` + title + `">` + title + `</td>
            <td><span class="ticket-badge-pill ` + prMeta.class + `">` + prMeta.label + `</span></td>
            <td>` + pmName + `</td>
            <td><span class="ticket-badge-pill ` + stMeta.class + `"><i class="fa-solid ` + stMeta.icon + `"></i> ` + stMeta.label + `</span></td>
            <td style="text-align:right;white-space:nowrap;">
              <button class="btn-mo-primary" style="padding:4px 8px;font-size:11.5px;background:#2563eb;" onclick="openTicketDetail('` + id + `')">
                <i class="fa-solid fa-eye"></i> Xem
              </button>
              <button class="btn-mo-outline" style="padding:4px 8px;font-size:11.5px;" onclick="openAssignTicketModal('` + id + `')" title="Phân công nhân viên">
                <i class="fa-solid fa-user-gear"></i>
              </button>
              <button class="btn-mo-outline" style="padding:4px 8px;font-size:11.5px;" onclick="openUpdateTicketStatusModal('` + id + `')" title="Cập nhật tiến độ">
                <i class="fa-solid fa-pen-nib"></i>
              </button>
              <button class="btn-mo-outline" style="padding:4px 8px;font-size:11.5px;color:#dc2626;border-color:#fca5a5;" onclick="handleDeleteTicket('` + id + `')" title="Xóa mềm">
                <i class="fa-solid fa-trash-can"></i>
              </button>
            </td>
          </tr>
        `;
      }).join('');
    }

    function renderTenantTicketsTable(items) {
      const tbody = document.getElementById("tenant-tickets-tbody");
      if (!tbody) return;

      if (!items || items.length === 0) {
        tbody.innerHTML = `<tr><td colspan="7" style="text-align:center;padding:24px;color:#94a3b8;"><i class="fa-solid fa-inbox" style="font-size:28px;margin-bottom:8px;display:block;"></i>Chưa có phiếu yêu cầu hỗ trợ nào.</td></tr>`;
        return;
      }

      tbody.innerHTML = items.map(t => {
        const id = t.id;
        const code = t.ticket_code || ('#TK-' + (t.id || '2026'));
        const catLabel = getTicketCategoryLabel(t.category?.value || t.category);
        const title = t.title || 'Sự cố bảo trì';
        const time = t.created_at ? new Date(t.created_at).toLocaleDateString('vi-VN') : (t.time || 'Vừa xong');
        const stRaw = t.status?.value || t.status;
        const stMeta = getTicketStatusMeta(stRaw);
        const prMeta = getTicketPriorityMeta(t.priority?.value || t.priority);

        let actionBtns = `<button class="btn-mo-primary" style="padding:4px 8px;font-size:11.5px;" onclick="openTicketDetail('` + id + `')"><i class="fa-solid fa-eye"></i> Xem</button>`;
        if (stRaw === 'NEW' || stRaw === 'OPEN') {
          actionBtns += ` <button class="btn-mo-outline" style="padding:4px 8px;font-size:11.5px;" onclick="openEditTicketModal('` + id + `')" title="Sửa nội dung"><i class="fa-solid fa-pen"></i></button>`;
        }
        if (stRaw === 'WAITING_CONFIRMATION' || stRaw === 'RESOLVED') {
          actionBtns += ` <button class="btn-mo-orange" style="padding:4px 8px;font-size:11.5px;" onclick="openConfirmTicketModal('` + id + `')"><i class="fa-solid fa-check"></i> Nghiệm thu</button>`;
        }
        if (stRaw === 'COMPLETED' || stRaw === 'CLOSED') {
          actionBtns += ` <button class="btn-mo-outline" style="padding:4px 8px;font-size:11.5px;color:#d97706;border-color:#fde68a;" onclick="openRateTicketModal('` + id + `')"><i class="fa-solid fa-star"></i> Đánh giá</button>`;
        }

        return `
          <tr>
            <td><strong style="color:#0f3361;cursor:pointer;" onclick="openTicketDetail('` + id + `')">` + code + `</strong></td>
            <td><span class="badge-filament info">` + catLabel + `</span></td>
            <td><div style="font-weight:600;max-width:260px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">` + title + `</div><div style="font-size:11.5px;color:#64748b;">` + (t.description || '').slice(0, 45) + `...</div></td>
            <td>` + time + `</td>
            <td><span class="ticket-badge-pill ` + prMeta.class + `">` + prMeta.label + `</span></td>
            <td><span class="ticket-badge-pill ` + stMeta.class + `"><i class="fa-solid ` + stMeta.icon + `"></i> ` + stMeta.label + `</span></td>
            <td style="text-align:right;white-space:nowrap;">` + actionBtns + `</td>
          </tr>
        `;
      }).join('');
    }

    // 58. POST /api/v1/tenant/tickets: Tạo Ticket mới
    async function handleTenantNewTicket(e) {
      e.preventDefault();
      const building_id = document.getElementById("ticket-building-id")?.value;
      const title = document.getElementById("ticket-title").value.trim();
      const category = document.getElementById("ticket-category").value;
      const priority = document.getElementById("ticket-priority").value;
      const description = document.getElementById("ticket-desc").value.trim();
      const imgUrl = document.getElementById("ticket-image-url")?.value?.trim();
      const btn = document.getElementById("btn-submit-create-ticket");

      const token = localStorage.getItem('auth_token');
      if (!token) {
        alert("Vui lòng đăng nhập với tư cách Khách thuê (Tenant) để gửi phiếu sự cố!");
        closeTenantTicketModal();
        openLoginModal();
        return;
      }

      if (btn) {
        btn.disabled = true;
        btn.innerHTML = `<i class="fa-solid fa-spinner fa-spin"></i> Đang gửi yêu cầu...`;
      }

      try {
        const payload = {
          building_id: building_id,
          title: title,
          category: category,
          priority: priority,
          description: description,
          images: imgUrl ? [imgUrl] : []
        };

        const res = await fetch('/api/v1/tenant/tickets', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'Authorization': 'Bearer ' + token
          },
          body: JSON.stringify(payload)
        });

        const json = await res.json();
        if (res.ok && json.status === 'success') {
          alert("Gửi phiếu yêu cầu hỗ trợ sự cố thành công!\nMã phiếu: " + (json.data?.ticket_code || 'Mới') + "\nĐã gửi thông báo tới Ban Quản Lý tòa nhà.");
          closeTenantTicketModal();
          document.getElementById("ticket-title").value = '';
          document.getElementById("ticket-desc").value = '';
          if (document.getElementById("ticket-image-url")) document.getElementById("ticket-image-url").value = '';
          loadTicketsList();
        } else {
          alert("Lỗi khi tạo ticket: " + (json.message || 'Dữ liệu không hợp lệ'));
        }
      } catch (err) {
        console.error("Create ticket error:", err);
        alert("Lỗi kết nối máy chủ khi tạo phiếu hỗ trợ.");
      } finally {
        if (btn) {
          btn.disabled = false;
          btn.innerHTML = `<i class="fa-solid fa-paper-plane"></i> Gửi Phiếu Sự Cố Đến Ban Quản Lý (Tạo Ticket)`;
        }
      }
    }

    // 59. GET /api/v1/tickets/{id}: Chi tiết Ticket & Lịch sử
    async function openTicketDetail(ticketId) {
      const modal = document.getElementById("ticket-detail-modal");
      if (!modal) return;
      modal.classList.add("show");

      const token = localStorage.getItem('auth_token');
      let ticket = CURRENT_TICKETS_CACHE.find(t => String(t.id) === String(ticketId));

      if (token) {
        try {
          const res = await fetch(`/api/v1/tickets/` + ticketId, {
            headers: {
              'Authorization': 'Bearer ' + token,
              'Accept': 'application/json'
            }
          });
          if (res.ok) {
            const json = await res.json();
            if (json.data) ticket = json.data;
          }
        } catch (e) {
          console.error("Fetch ticket detail error:", e);
        }
      }

      if (!ticket) {
        alert("Không tìm thấy thông tin chi tiết của ticket này.");
        closeTicketDetailModal();
        return;
      }

      ACTIVE_TICKET_DETAIL = ticket;

      // Populate Data
      const stRaw = ticket.status?.value || ticket.status;
      const stMeta = getTicketStatusMeta(stRaw);
      const prMeta = getTicketPriorityMeta(ticket.priority?.value || ticket.priority);

      const stBadge = document.getElementById("td-status-badge");
      const prBadge = document.getElementById("td-priority-badge");
      if (stBadge) {
        stBadge.className = 'ticket-badge-pill ' + stMeta.class;
        stBadge.innerHTML = `<i class="fa-solid ` + stMeta.icon + `"></i> ` + stMeta.label;
      }
      if (prBadge) {
        prBadge.className = 'ticket-badge-pill ' + prMeta.class;
        prBadge.innerText = prMeta.label;
      }

      document.getElementById("td-code").innerText = ticket.ticket_code || ('#' + ticket.id);
      document.getElementById("td-title").innerText = ticket.title || '-';
      document.getElementById("td-building-name").innerText = ticket.building?.name || ticket.location || '-';
      document.getElementById("td-unit-info").innerText = ticket.unit ? (`Tầng ` + ticket.unit.floor_number + ` (Mã ` + ticket.unit.unit_code + `)`) : 'Khu vực chung';
      document.getElementById("td-tenant-name").innerText = ticket.tenant?.company_name || ticket.tenant?.representative_name || ticket.tenant || 'Khách thuê';
      document.getElementById("td-category-name").innerText = getTicketCategoryLabel(ticket.category?.value || ticket.category);
      document.getElementById("td-pm-name").innerText = ticket.assigned_pm?.full_name ? (ticket.assigned_pm.full_name + ` (` + (ticket.assigned_pm.employee_code || '') + `)`) : 'Chưa phân công';
      document.getElementById("td-expected-date").innerText = ticket.expected_completion_at ? new Date(ticket.expected_completion_at).toLocaleString('vi-VN') : 'Chưa xác định';
      document.getElementById("td-created-at").innerText = ticket.created_at ? new Date(ticket.created_at).toLocaleString('vi-VN') : 'Vừa xong';
      document.getElementById("td-description").innerText = ticket.description || '-';

      // Resolution Summary
      const resBox = document.getElementById("td-resolution-box");
      const resText = document.getElementById("td-resolution-summary");
      if (ticket.resolution_summary) {
        if (resBox) resBox.style.display = 'block';
        if (resText) resText.innerText = ticket.resolution_summary;
      } else {
        if (resBox) resBox.style.display = 'none';
      }

      // Proof of Work Gallery
      const proofList = document.getElementById("td-proof-images-list");
      const images = ticket.proof_images || ticket.proofImages || [];
      if (proofList) {
        if (images.length > 0) {
          proofList.innerHTML = images.map(img => {
            const isBefore = (img.proof_type === 'BEFORE' || img.proof_type === 'INCIDENT');
            return `
              <div class="proof-card" title="` + (img.caption || '') + `">
                <img src="` + img.image_url + `" alt="Proof">
                <span class="proof-ribbon ` + (isBefore ? 'before' : 'after') + `">` + (isBefore ? 'Before' : 'After') + `</span>
              </div>
            `;
          }).join('');
        } else {
          proofList.innerHTML = `<div style="grid-column:1/-1;font-size:12px;color:#94a3b8;padding:8px;background:#f8fafc;border-radius:6px;text-align:center;"><i class="fa-solid fa-image"></i> Chưa có ảnh bằng chứng nào được tải lên.</div>`;
        }
      }

      // CSAT
      const csatBox = document.getElementById("td-csat-box");
      const csatStars = document.getElementById("td-csat-stars");
      const csatFeedback = document.getElementById("td-csat-feedback");
      if (ticket.tenant_rating) {
        if (csatBox) csatBox.style.display = 'block';
        if (csatFeedback) csatFeedback.innerText = `"` + (ticket.tenant_feedback || 'Rất hài lòng') + `"`;
        if (csatStars) {
          let starsHtml = '';
          for (let i = 1; i <= 5; i++) {
            starsHtml += `<i class="fa-solid fa-star ` + (i <= ticket.tenant_rating ? 'active' : '') + `"></i>`;
          }
          csatStars.innerHTML = starsHtml;
        }
      } else {
        if (csatBox) csatBox.style.display = 'none';
      }

      // Histories Timeline
      const timelineList = document.getElementById("td-timeline-list");
      const histories = ticket.histories || [];
      if (timelineList) {
        if (histories.length > 0) {
          timelineList.innerHTML = histories.map(h => {
            const dateStr = h.created_at ? new Date(h.created_at).toLocaleString('vi-VN') : 'Vừa xong';
            const actorName = h.pm_actor?.full_name || h.admin_actor?.name || h.tenant_actor?.company_name || h.actor_type || 'Hệ thống';
            const toSt = h.to_status?.value || h.to_status || '';
            let dotClass = '';
            if (toSt === 'COMPLETED') dotClass = 'completed';
            else if (toSt === 'ON_HOLD') dotClass = 'hold';
            else if (toSt === 'WAITING_CONFIRMATION') dotClass = 'warning';

            return `
              <div class="ticket-timeline-item">
                <div class="timeline-dot ` + dotClass + `"></div>
                <div class="timeline-content">
                  <div class="timeline-header">
                    <strong style="color:#0f3361;">` + actorName + `</strong>
                    <span style="color:#94a3b8;font-size:11px;">` + dateStr + `</span>
                  </div>
                  <div style="color:#475569;">` + (h.message || 'Cập nhật tiến trình') + `</div>
                </div>
              </div>
            `;
          }).join('');
        } else {
          timelineList.innerHTML = `
            <div class="ticket-timeline-item">
              <div class="timeline-dot"></div>
              <div class="timeline-content">
                <div class="timeline-header"><strong>Khách thuê</strong><span style="color:#94a3b8;font-size:11px;">` + (ticket.created_at ? new Date(ticket.created_at).toLocaleString('vi-VN') : 'Hôm nay') + `</span></div>
                <div style="color:#475569;">Khởi tạo phiếu yêu cầu hỗ trợ mới.</div>
              </div>
            </div>
          `;
        }
      }

      // Render Dynamic Actions Bar
      renderTicketDetailActions(ticket);
    }

    function renderTicketDetailActions(ticket) {
      const actionsBar = document.getElementById("td-actions-bar");
      if (!actionsBar) return;

      const role = localStorage.getItem('auth_role') || 'ADMIN';
      const st = ticket.status?.value || ticket.status;
      const tid = ticket.id;
      let html = '';

      // Quyền Quản trị & BQL (Admin, PM)
      if (role === 'ADMIN' || role === 'SUPER_ADMIN' || role === 'PM' || role === 'PROPERTY_MANAGER') {
        html += `<button type="button" class="btn-mo-primary" style="background:#2563eb;color:#fff;padding:8px 14px;font-size:12.5px;" onclick="openAssignTicketModal('` + tid + `')"><i class="fa-solid fa-user-gear"></i> Phân công Kỹ thuật</button>`;
        html += `<button type="button" class="btn-mo-primary" style="background:#059669;color:#fff;padding:8px 14px;font-size:12.5px;" onclick="openUpdateTicketStatusModal('` + tid + `')"><i class="fa-solid fa-list-check"></i> Cập nhật Tiến độ</button>`;
        html += `<button type="button" class="btn-mo-outline" style="padding:8px 14px;font-size:12.5px;" onclick="openUploadProofModal('` + tid + `')"><i class="fa-solid fa-cloud-arrow-up"></i> Tải ảnh Before/After</button>`;
        html += `<button type="button" class="btn-mo-outline" style="color:#dc2626;border-color:#fca5a5;padding:8px 14px;font-size:12.5px;" onclick="handleDeleteTicket('` + tid + `')"><i class="fa-solid fa-trash-can"></i> Xóa Ticket</button>`;
      }

      // Quyền Khách thuê (Tenant)
      if (role === 'TENANT') {
        if (st === 'NEW' || st === 'OPEN') {
          html += `<button type="button" class="btn-mo-primary" style="background:#2563eb;color:#fff;padding:8px 14px;font-size:12.5px;" onclick="openEditTicketModal('` + tid + `')"><i class="fa-solid fa-pen-to-square"></i> Chỉnh sửa nội dung</button>`;
        }
        if (st === 'WAITING_CONFIRMATION' || st === 'RESOLVED') {
          html += `<button type="button" class="btn-mo-orange" style="padding:8px 16px;font-size:12.5px;" onclick="openConfirmTicketModal('` + tid + `')"><i class="fa-solid fa-circle-check"></i> Nghiệm thu kết quả</button>`;
        }
        if (st === 'COMPLETED' || st === 'CLOSED') {
          html += `<button type="button" class="btn-mo-primary" style="background:#f59e0b;color:#fff;padding:8px 16px;font-size:12.5px;" onclick="openRateTicketModal('` + tid + `')"><i class="fa-solid fa-star"></i> Đánh giá phục vụ (CSAT)</button>`;
        }
        html += `<button type="button" class="btn-mo-outline" style="padding:8px 14px;font-size:12.5px;" onclick="openUploadProofModal('` + tid + `')"><i class="fa-solid fa-camera"></i> Đính kèm ảnh</button>`;
      }

      actionsBar.innerHTML = html;
    }

    function closeTicketDetailModal() {
      const modal = document.getElementById("ticket-detail-modal");
      if (modal) modal.classList.remove("show");
    }

    // 60. PUT /api/v1/tickets/{id}: Cập nhật nội dung ticket khi OPEN
    function openEditTicketModal(ticketId) {
      const ticket = CURRENT_TICKETS_CACHE.find(t => String(t.id) === String(ticketId)) || ACTIVE_TICKET_DETAIL;
      if (!ticket) return;

      document.getElementById("edit-ticket-id").value = ticket.id;
      document.getElementById("edit-ticket-title").value = ticket.title || '';
      document.getElementById("edit-ticket-category").value = ticket.category?.value || ticket.category || 'MAINTENANCE';
      document.getElementById("edit-ticket-priority").value = ticket.priority?.value || ticket.priority || 'NORMAL';
      document.getElementById("edit-ticket-desc").value = ticket.description || '';

      document.getElementById("ticket-edit-modal").classList.add("show");
    }

    function closeTicketEditModal() {
      document.getElementById("ticket-edit-modal").classList.remove("show");
    }

    async function handleUpdateTicketSubmit(e) {
      e.preventDefault();
      const id = document.getElementById("edit-ticket-id").value;
      const title = document.getElementById("edit-ticket-title").value;
      const category = document.getElementById("edit-ticket-category").value;
      const priority = document.getElementById("edit-ticket-priority").value;
      const description = document.getElementById("edit-ticket-desc").value;
      const token = localStorage.getItem('auth_token');

      try {
        const res = await fetch(`/api/v1/tickets/` + id, {
          method: 'PUT',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'Authorization': 'Bearer ' + token
          },
          body: JSON.stringify({ title, category, priority, description })
        });
        const json = await res.json();
        if (res.ok && json.status === 'success') {
          alert("Cập nhật thông tin phiếu yêu cầu hỗ trợ thành công!");
          closeTicketEditModal();
          openTicketDetail(id);
          loadTicketsList();
        } else {
          alert("Lỗi cập nhật: " + (json.message || 'Không thể lưu thay đổi'));
        }
      } catch (err) {
        console.error("Update ticket error:", err);
      }
    }

    // 61. DELETE /api/v1/tickets/{id}: Xóa mềm ticket (Admin)
    async function handleDeleteTicket(ticketId) {
      if (!confirm("Bạn có chắc chắn muốn xóa mềm phiếu yêu cầu hỗ trợ này khỏi hệ thống?")) return;
      const token = localStorage.getItem('auth_token');

      try {
        const res = await fetch(`/api/v1/tickets/` + ticketId, {
          method: 'DELETE',
          headers: {
            'Authorization': 'Bearer ' + token,
            'Accept': 'application/json'
          }
        });
        const json = await res.json();
        if (res.ok && json.status === 'success') {
          alert("Đã xóa mềm phiếu yêu cầu hỗ trợ thành công.");
          closeTicketDetailModal();
          loadTicketsList();
        } else {
          alert("Lỗi khi xóa ticket: " + (json.message || 'Chỉ Quản trị viên Admin mới có quyền xóa.'));
        }
      } catch (err) {
        console.error("Delete ticket error:", err);
      }
    }

    // 62. PATCH /api/v1/tickets/{id}/assign: Phân công nhân viên xử lý
    function openAssignTicketModal(ticketId) {
      const id = ticketId || ACTIVE_TICKET_DETAIL?.id;
      if (!id) return;
      document.getElementById("assign-ticket-id").value = id;
      document.getElementById("ticket-assign-modal").classList.add("show");
    }

    function closeTicketAssignModal() {
      document.getElementById("ticket-assign-modal").classList.remove("show");
    }

    async function handleAssignTicketSubmit(e) {
      e.preventDefault();
      const id = document.getElementById("assign-ticket-id").value;
      const assigned_pm_id = document.getElementById("assign-pm-id").value;
      const expected_completion_at = document.getElementById("assign-expected-date").value;
      const note = document.getElementById("assign-note").value;
      const token = localStorage.getItem('auth_token');

      try {
        const res = await fetch(`/api/v1/tickets/` + id + `/assign`, {
          method: 'PATCH',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'Authorization': 'Bearer ' + token
          },
          body: JSON.stringify({ assigned_pm_id, expected_completion_at, note })
        });
        const json = await res.json();
        if (res.ok && json.status === 'success') {
          alert("Tiếp nhận và phân công nhân viên xử lý thành công!\nTrạng thái chuyển sang: IN_PROGRESS.");
          closeTicketAssignModal();
          openTicketDetail(id);
          loadTicketsList();
        } else {
          alert("Lỗi phân công: " + (json.message || 'Yêu cầu quyền BQL / Admin'));
        }
      } catch (err) {
        console.error("Assign ticket error:", err);
      }
    }

    // 63. PATCH /api/v1/tickets/{id}/status: Cập nhật tiến độ xử lý
    function openUpdateTicketStatusModal(ticketId) {
      const id = ticketId || ACTIVE_TICKET_DETAIL?.id;
      if (!id) return;
      document.getElementById("status-ticket-id").value = id;
      document.getElementById("ticket-status-modal").classList.add("show");
    }

    function closeTicketStatusModal() {
      document.getElementById("ticket-status-modal").classList.remove("show");
    }

    function handleStatusSelectionChange(val) {
      const resGroup = document.getElementById("status-resolved-group");
      const holdGroup = document.getElementById("status-hold-group");
      if (val === 'RESOLVED') {
        if (resGroup) resGroup.style.display = 'block';
        if (holdGroup) holdGroup.style.display = 'none';
      } else if (val === 'ON_HOLD') {
        if (resGroup) resGroup.style.display = 'none';
        if (holdGroup) holdGroup.style.display = 'block';
      } else {
        if (resGroup) resGroup.style.display = 'none';
        if (holdGroup) holdGroup.style.display = 'none';
      }
    }

    async function handleUpdateTicketStatusSubmit(e) {
      e.preventDefault();
      const id = document.getElementById("status-ticket-id").value;
      const status = document.getElementById("status-new-value").value;
      const resolution_summary = document.getElementById("status-resolution-summary")?.value;
      const on_hold_reason = document.getElementById("status-hold-reason")?.value;
      const note = document.getElementById("status-note")?.value;
      const token = localStorage.getItem('auth_token');

      try {
        const payload = { status, note };
        if (status === 'RESOLVED') payload.resolution_summary = resolution_summary;
        if (status === 'ON_HOLD') payload.on_hold_reason = on_hold_reason;

        const res = await fetch(`/api/v1/tickets/` + id + `/status`, {
          method: 'PATCH',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'Authorization': 'Bearer ' + token
          },
          body: JSON.stringify(payload)
        });
        const json = await res.json();
        if (res.ok && json.status === 'success') {
          alert("Cập nhật tiến độ xử lý ticket thành công!");
          closeTicketStatusModal();
          openTicketDetail(id);
          loadTicketsList();
        } else {
          alert("Lỗi cập nhật tiến độ: " + (json.message || 'Không thành công'));
        }
      } catch (err) {
        console.error("Update status error:", err);
      }
    }

    // 64. POST /api/v1/tickets/{id}/proof-images: Tải lên ảnh bằng chứng Before / After
    function openUploadProofModal(ticketId) {
      const id = ticketId || ACTIVE_TICKET_DETAIL?.id;
      if (!id) return;
      document.getElementById("proof-ticket-id").value = id;
      document.getElementById("ticket-upload-proof-modal").classList.add("show");
    }

    function closeUploadProofModal() {
      document.getElementById("ticket-upload-proof-modal").classList.remove("show");
    }

    async function handleUploadProofSubmit(e) {
      e.preventDefault();
      const id = document.getElementById("proof-ticket-id").value;
      const proof_type = document.getElementById("proof-type").value;
      const caption = document.getElementById("proof-caption").value;
      const url = document.getElementById("proof-url")?.value?.trim() || 'https://images.unsplash.com/photo-1581092160607-ee22621dd758?auto=format&fit=crop&w=800&q=80';
      const token = localStorage.getItem('auth_token');

      try {
        const res = await fetch(`/api/v1/tickets/` + id + `/proof-images`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'Authorization': 'Bearer ' + token
          },
          body: JSON.stringify({
            proof_type,
            caption,
            images: [url]
          })
        });
        const json = await res.json();
        if (res.ok && json.status === 'success') {
          alert("Tải lên hình ảnh bằng chứng thành công!");
          closeUploadProofModal();
          openTicketDetail(id);
          loadTicketsList();
        } else {
          alert("Lỗi tải ảnh: " + (json.message || 'Không thành công'));
        }
      } catch (err) {
        console.error("Upload proof error:", err);
      }
    }

    // 65. POST /api/v1/tenant/tickets/{id}/confirm: Nghiệm thu kết quả
    function openConfirmTicketModal(ticketId) {
      const id = ticketId || ACTIVE_TICKET_DETAIL?.id;
      if (!id) return;
      document.getElementById("confirm-ticket-id").value = id;
      document.getElementById("ticket-confirm-modal").classList.add("show");
    }

    function closeTicketConfirmModal() {
      document.getElementById("ticket-confirm-modal").classList.remove("show");
    }

    async function submitTicketConfirm(isConfirmed) {
      const id = document.getElementById("confirm-ticket-id").value;
      const feedback = document.getElementById("confirm-feedback").value;
      const token = localStorage.getItem('auth_token');

      try {
        const res = await fetch(`/api/v1/tenant/tickets/` + id + `/confirm`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'Authorization': 'Bearer ' + token
          },
          body: JSON.stringify({
            confirmed: isConfirmed,
            feedback: feedback
          })
        });
        const json = await res.json();
        if (res.ok && json.status === 'success') {
          alert(json.message || (isConfirmed ? "Đã nghiệm thu hoàn tất phiếu hỗ trợ!" : "Đã gửi phản hồi yêu cầu BQL khắc phục lại!"));
          closeTicketConfirmModal();
          openTicketDetail(id);
          loadTicketsList();
        } else {
          alert("Lỗi nghiệm thu: " + (json.message || 'Yêu cầu tài khoản Khách thuê (Tenant)'));
        }
      } catch (err) {
        console.error("Confirm ticket error:", err);
      }
    }

    // 66. POST /api/v1/tenant/tickets/{id}/rate: Đánh giá CSAT
    function openRateTicketModal(ticketId) {
      const id = ticketId || ACTIVE_TICKET_DETAIL?.id;
      if (!id) return;
      document.getElementById("rate-ticket-id").value = id;
      setCsatStar(5);
      document.getElementById("ticket-rate-modal").classList.add("show");
    }

    function closeTicketRateModal() {
      document.getElementById("ticket-rate-modal").classList.remove("show");
    }

    function setCsatStar(star) {
      CSAT_SELECTED_STAR = star;
      const stars = document.querySelectorAll("#rate-stars-input i");
      stars.forEach(s => {
        const val = parseInt(s.getAttribute("data-star"));
        if (val <= star) s.classList.add("active");
        else s.classList.remove("active");
      });

      const labelEl = document.getElementById("rate-star-label");
      const labels = {
        1: "1 sao - Rất không hài lòng",
        2: "2 sao - Chưa hài lòng",
        3: "3 sao - Tạm được",
        4: "4 sao - Hài lòng",
        5: "5 sao - Rất hài lòng & Xuất sắc"
      };
      if (labelEl) labelEl.innerText = labels[star] || (star + " sao");
    }

    async function submitCsatRating() {
      const id = document.getElementById("rate-ticket-id").value;
      const feedback = document.getElementById("rate-feedback").value;
      const token = localStorage.getItem('auth_token');

      try {
        const res = await fetch(`/api/v1/tenant/tickets/` + id + `/rate`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'Authorization': 'Bearer ' + token
          },
          body: JSON.stringify({
            rating: CSAT_SELECTED_STAR,
            feedback: feedback
          })
        });
        const json = await res.json();
        if (res.ok && json.status === 'success') {
          alert("Cảm ơn bạn đã gửi đánh giá chất lượng phục vụ cho Ban Quản Lý!");
          closeTicketRateModal();
          openTicketDetail(id);
          loadTicketsList();
        } else {
          alert("Lỗi gửi đánh giá: " + (json.message || 'Yêu cầu tài khoản Khách thuê'));
        }
      } catch (err) {
        console.error("Rate ticket error:", err);
      }
    }


    </script>
