<script>
    /* ==========================================================================
       7. SWITCHING BETWEEN MAISON OFFICE PORTAL & FILAMENT ADMIN SHELL
       ========================================================================== */
    function switchView(viewType) {
      const portal = document.getElementById("portal-wrapper");
      const admin = document.getElementById("admin-wrapper");

      if (viewType === 'admin-view') {
        portal.style.display = 'none';
        admin.style.display = 'block';
        window.scrollTo({ top: 0, behavior: 'smooth' });
        renderAdminTables();
      } else {
        admin.style.display = 'none';
        portal.style.display = 'block';
        window.scrollTo({ top: 0, behavior: 'smooth' });
      }
    }

    function handleGoToWorkspace() {
      const role = String(localStorage.getItem('auth_role') || '').toUpperCase();
      if (!role) {
        openLoginModal();
        return;
      }
      if (role === 'ADMIN' || role === 'SUPER_ADMIN') {
        handleRoleSwitch('admin');
      } else if (role === 'PM' || role === 'PROPERTY_MANAGER') {
        handleRoleSwitch('pm');
      } else if (role === 'SALES') {
        handleRoleSwitch('sales');
      } else if (role === 'TENANT') {
        handleRoleSwitch('tenant');
      } else {
        handleRoleSwitch('admin');
      }
    }

    function handleRoleSwitch(role) {
      switchView('admin-view');
      const selectHeader = document.getElementById("filament-role-select");
      if (selectHeader) selectHeader.value = role;

      const sidebarRoleBadge = document.getElementById("sidebar-role-badge");
      const userName = document.getElementById("filament-user-name");
      const userSub = document.getElementById("filament-user-sub");
      const userAvatar = document.getElementById("filament-user-avatar");
      const roleSwitcherContainer = document.getElementById("role-switcher-container");
      const sidebarFilamentBtn = document.getElementById("sidebar-filament-btn");

      const groupAdmin = document.getElementById("nav-group-admin");
      const groupSales = document.getElementById("nav-group-sales");
      const groupTenant = document.getElementById("nav-group-tenant");

      // Lấy thông tin user đăng nhập thực tế nếu có
      const authUserStr = localStorage.getItem('auth_user');
      const realRole = String(localStorage.getItem('auth_role') || '').toUpperCase();
      let realUser = null;
      if (authUserStr) {
        try { realUser = JSON.parse(authUserStr); } catch (e) {}
      }

      const rawName = realUser?.company_name || realUser?.full_name || realUser?.name;

      const navStaff = document.getElementById("nav-item-staff");
      if (navStaff) navStaff.style.display = (role === 'admin') ? 'flex' : 'none';

      if (role === 'admin') {
        const isAdmin = (realRole === 'ADMIN' || realRole === 'SUPER_ADMIN' || !realRole);
        if (roleSwitcherContainer) roleSwitcherContainer.style.display = isAdmin ? 'inline-flex' : 'none';
        if (sidebarFilamentBtn) sidebarFilamentBtn.style.display = isAdmin ? 'inline-flex' : 'none';

        sidebarRoleBadge.innerText = 'Super Admin';
        userName.innerText = rawName || 'Nguyễn Quản Trị';
        userSub.innerText = realUser?.login_email || realUser?.email || 'Quản trị viên toàn hệ thống';
        userAvatar.innerText = 'AD';
        groupAdmin.style.display = 'block';
        groupSales.style.display = 'block';
        groupTenant.style.display = 'none';
        switchFilamentTab('tab-admin-dashboard', document.querySelector('#nav-group-admin .filament-link'));
      } else if (role === 'pm') {
        if (roleSwitcherContainer) roleSwitcherContainer.style.display = (realRole === 'ADMIN') ? 'inline-flex' : 'none';
        if (sidebarFilamentBtn) sidebarFilamentBtn.style.display = 'none';

        sidebarRoleBadge.innerText = 'Ban Quản Lý (PM)';
        userName.innerText = rawName || 'Đỗ Minh Quang';
        userSub.innerText = realUser?.login_email || realUser?.email || 'Quản lý vận hành cao ốc';
        userAvatar.innerText = 'PM';
        groupAdmin.style.display = 'block';
        groupSales.style.display = 'none';
        groupTenant.style.display = 'none';
        switchFilamentTab('tab-admin-dashboard', document.querySelector('#nav-group-admin .filament-link'));
      } else if (role === 'sales') {
        if (roleSwitcherContainer) roleSwitcherContainer.style.display = (realRole === 'ADMIN') ? 'inline-flex' : 'none';
        if (sidebarFilamentBtn) sidebarFilamentBtn.style.display = 'none';

        sidebarRoleBadge.innerText = 'Chuyên Viên Sales';
        userName.innerText = rawName || 'Lê Hoàng Long';
        userSub.innerText = realUser?.login_email || realUser?.email || 'Chuyên viên Báo giá & Leasing';
        userAvatar.innerText = 'SL';
        groupAdmin.style.display = 'none';
        groupSales.style.display = 'block';
        groupTenant.style.display = 'none';
        switchFilamentTab('tab-sales-leads', document.querySelector('#nav-group-sales .filament-link'));
      } else if (role === 'tenant') {
        if (roleSwitcherContainer) roleSwitcherContainer.style.display = (realRole === 'ADMIN') ? 'inline-flex' : 'none';
        if (sidebarFilamentBtn) sidebarFilamentBtn.style.display = 'none';

        sidebarRoleBadge.innerText = 'Khách Thuê';
        userName.innerText = rawName || 'Doanh Nghiệp Thuê';
        userSub.innerText = realUser?.login_email || realUser?.billing_email || realUser?.email || 'Văn phòng đại diện';
        userAvatar.innerText = 'DN';
        groupAdmin.style.display = 'none';
        groupSales.style.display = 'none';
        groupTenant.style.display = 'block';

        // Cập nhật thông tin thực tế của Doanh nghiệp thuê vào Workspace
        const welcomeTitle = document.getElementById("tenant-welcome-title");
        if (welcomeTitle) welcomeTitle.innerText = "Chào Mừng, " + (rawName || 'Doanh Nghiệp Thuê');

        const contractRep = document.getElementById("tenant-contract-rep");
        if (contractRep && realUser?.representative_name) {
          contractRep.innerText = realUser.representative_name + (realUser.representative_position ? ` (${realUser.representative_position})` : ' (Đại diện pháp luật)');
        }

        switchFilamentTab('tab-tenant-workspace', document.querySelector('#nav-group-tenant .filament-link'));
      } else if (role === 'portal') {
        switchView('portal-view');
      }
    }

    function switchFilamentTab(tabId, el) {
      document.querySelectorAll('.filament-tab-pane').forEach(pane => {
        pane.style.display = 'none';
        pane.classList.remove('active');
      });
      const target = document.getElementById(tabId);
      if (target) {
        target.style.display = 'block';
        target.classList.add('active');
      }

      document.querySelectorAll('.filament-link').forEach(link => link.classList.remove('active'));
      if (el) el.classList.add('active');
      window.scrollTo({ top: 0, behavior: 'smooth' });

      if (tabId === 'tab-admin-tickets' || tabId === 'tab-tenant-tickets') {
        loadTicketsList();
      }
      if (tabId === 'tab-admin-staff') {
        switchStaffSubTab(currentStaffTab);
      }
      if (tabId === 'tab-sales-leads') {
        loadAdminLeadsList();
      }
    }

    /* ==========================================================================
       8. POPULATING FILAMENT ADMIN TABLES
       ========================================================================== */
    function renderAdminTables() {
      // 1. Admin Buildings Table
      const bTable = document.getElementById("admin-buildings-tbody");
      if (bTable) {
        bTable.innerHTML = BUILDINGS_DATA.map(b => `
          <tr>
            <td><strong>${b.name}</strong></td>
            <td>${b.district}</td>
            <td><span class="badge-filament info">${b.grade}</span></td>
            <td>${b.totalArea}</td>
            <td><strong style="color:#10b981;">85%</strong></td>
            <td>$${b.priceUsd} / m²</td>
            <td>
              <button class="btn-mo-outline" style="padding:4px 8px;font-size:12px;" onclick="openBuildingDetail('${b.id}')">Sửa sàn</button>
              <button class="btn-mo-outline" style="padding:4px 8px;font-size:12px;color:#4f46e5;border-color:#c7d2fe;margin-left:4px;" onclick="openBuildingAssignmentsFromBuilding('${b.id}', '${escapeHtml(b.name)}')"><i class="fa-solid fa-users-gear"></i> Phân công</button>
            </td>
          </tr>
        `).join('');
      }

      // 2 & 4: Load and render Tickets from API
      loadTicketsList();

      // 3. Sales Leads Table
      loadAdminLeadsList();
    }

    async function loadAdminLeadsList() {
      const sLeadsTable = document.getElementById("sales-leads-tbody");
      if (!sLeadsTable) return;
      const token = getSanctumToken();
      if (token) {
        try {
          const res = await fetch('/api/v1/crm/leads?per_page=15', {
            headers: { 'Accept': 'application/json', 'Authorization': 'Bearer ' + token }
          });
          const json = await res.json();
          if (res.ok && json.data) {
            const leads = json.data?.data || json.data || [];
            if (leads.length > 0) {
              const stageMap = {
                'NEW': { label: 'Tiếp nhận mới', badge: 'warning' },
                'CONTACTED': { label: 'Đã liên hệ', badge: 'info' },
                'SURVEYING': { label: 'Khảo sát thực tế', badge: 'info' },
                'PROPOSAL_SENT': { label: 'Đã gửi báo giá', badge: 'info' },
                'NEGOTIATING': { label: 'Đang đàm phán', badge: 'warning' },
                'WON': { label: 'Chốt thành công', badge: 'success' },
                'LOST': { label: 'Thất bại', badge: 'danger' }
              };
              sLeadsTable.innerHTML = leads.map(l => {
                const bldName = l.building?.name || 'Tòa nhà văn phòng';
                const st = stageMap[l.status] || { label: l.status || 'Tiếp nhận', badge: 'info' };
                const createdDate = l.created_at ? new Date(l.created_at).toLocaleDateString('vi-VN') : 'Mới';
                return `
                  <tr>
                    <td>${createdDate}</td>
                    <td><strong>${escapeHtml(l.contact_name || 'Khách hàng')}</strong> <br><small style="color:#64748b;">${escapeHtml(l.contact_phone || '')}</small></td>
                    <td>${escapeHtml(l.company_name || 'Doanh nghiệp')}</td>
                    <td><span style="color:var(--mo-primary);font-weight:600;">${escapeHtml(bldName)}</span></td>
                    <td>${l.expected_area_sqm ? l.expected_area_sqm + ' m²' : '---'}</td>
                    <td><span class="badge-filament ${st.badge}">${st.label}</span></td>
                    <td>
                      <button class="btn-mo-orange" style="padding:4px 8px;font-size:12px;" onclick="alert('Chi tiết Lead: ' + '${escapeHtml(l.contact_name)}' + ' - Nhu cầu: ' + '${l.expected_area_sqm || 0}' + ' m²')">Chi tiết Lead</button>
                    </td>
                  </tr>
                `;
              }).join('');
              return;
            } else {
              sLeadsTable.innerHTML = `<tr><td colspan="7" style="text-align:center;padding:24px;color:#94a3b8;"><i class="fa-solid fa-inbox" style="font-size:28px;margin-bottom:8px;display:block;"></i>Chưa có khách hàng tiềm năng nào trong CRM.</td></tr>`;
              return;
            }
          }
        } catch (e) {
          console.warn('Load leads from API fallback:', e);
        }
      }

      sLeadsTable.innerHTML = `<tr><td colspan="7" style="text-align:center;padding:24px;color:#94a3b8;"><i class="fa-solid fa-user-lock" style="font-size:28px;margin-bottom:8px;display:block;"></i>Vui lòng đăng nhập để xem danh sách Leads thật từ CRM.</td></tr>`;
    }

    /* ==========================================================================
       9. FORMS & MODALS HANDLERS (REAL API SYNC)
       ========================================================================== */
    function openQuoteModal() {
      document.getElementById("quote-modal").classList.add("show");
    }
    function closeQuoteModal() {
      document.getElementById("quote-modal").classList.remove("show");
    }
    function openQuoteForBuilding(bName) {
      document.getElementById("modal-target").value = bName;
      openQuoteModal();
    }

    async function handleQuickQuoteModal(e) {
      e.preventDefault();
      const name = document.getElementById("modal-name").value;
      const phone = document.getElementById("modal-phone").value;
      const target = document.getElementById("modal-target").value;
      const area = document.getElementById("modal-area").value;

      const matchedB = BUILDINGS_DATA.find(b => b.name === target) || BUILDINGS_DATA[0];
      const buildingId = (matchedB && typeof matchedB.id === 'string' && matchedB.id.length > 20) ? matchedB.id : null;

      if (buildingId) {
        try {
          const cleanPhone = phone.replace(/[^0-9]/g, '');
          const payload = {
            contact_full_name: name,
            phone: phone,
            email: `lead_${cleanPhone}@officeleasing.vn`,
            building_id: buildingId,
            desired_area_sqm: parseFloat(area) || 100,
            customer_note: `[Quick Quote Modal] Yêu cầu báo giá nhanh tòa nhà: ${target}`
          };

          const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
          fetch('/api/v1/portal/leads', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'Accept': 'application/json',
              'X-CSRF-TOKEN': token || ''
            },
            body: JSON.stringify(payload)
          }).catch(err => console.log('Lead API Sync Note:', err));
        } catch (e) {
          console.error(e);
        }
      }

      closeQuoteModal();
      alert(`Cảm ơn bạn ${name}! Chuyên viên tư vấn Office đã tiếp nhận yêu cầu và sẽ liên hệ qua SĐT ${phone} trong vòng 30 phút.`);
      renderAdminTables();
    }

    async function handleLeadSubmit(e) {
      e.preventDefault();
      const name = document.getElementById("quote-name").value;
      const phone = document.getElementById("quote-phone").value;
      const company = document.getElementById("quote-company").value;
      const building = document.getElementById("quote-building").value;
      const area = document.getElementById("quote-area-need").value;
      const notes = document.getElementById("quote-notes")?.value || "";

      const matchedB = BUILDINGS_DATA.find(b => building.includes(b.name)) || BUILDINGS_DATA[0];
      const buildingId = (matchedB && typeof matchedB.id === 'string' && matchedB.id.length > 20) ? matchedB.id : null;

      if (buildingId) {
        try {
          const cleanPhone = phone.replace(/[^0-9]/g, '');
          const payload = {
            contact_full_name: name,
            company_name: company,
            phone: phone,
            email: `lead_${cleanPhone}@officeleasing.vn`,
            building_id: buildingId,
            desired_area_sqm: parseFloat(area) || 150,
            customer_note: `[Form Báo Giá] Quan tâm: ${building}. Ghi chú: ${notes}`
          };

          const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
          fetch('/api/v1/portal/leads', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'Accept': 'application/json',
              'X-CSRF-TOKEN': token || ''
            },
            body: JSON.stringify(payload)
          }).catch(err => console.log('Lead API Sync Note:', err));
        } catch (e) {
          console.error(e);
        }
      }

      alert(`Đã ghi nhận yêu cầu báo giá của ${company} thành công!\nChuyên viên tư vấn của Office sẽ liên hệ ngay qua số điện thoại ${phone}.`);
      document.getElementById("lead-consult-form").reset();
      showPublicPage('page-home');
      renderAdminTables();
    }

    function openTenantTicketModal() {
      document.getElementById("ticket-modal").classList.add("show");
    }
    function closeTenantTicketModal() {
      document.getElementById("ticket-modal").classList.remove("show");
    }

    async function handleTenantNewTicket(e) {
      e.preventDefault();
      const title = document.getElementById("ticket-title").value;
      const desc = document.getElementById("ticket-desc").value;
      const category = document.getElementById("ticket-category").value;
      const priority = document.getElementById("ticket-priority").value;

      const token = localStorage.getItem('auth_token');
      const buildingId = (BUILDINGS_DATA[0]?.id && typeof BUILDINGS_DATA[0].id === 'string' && BUILDINGS_DATA[0].id.length > 20) ? BUILDINGS_DATA[0].id : null;

      // Nếu có auth token của Tenant, gửi API thật tới Backend
      if (token && buildingId) {
        try {
          const res = await fetch('/api/v1/tenant/tickets', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'Accept': 'application/json',
              'Authorization': `Bearer ${token}`
            },
            body: JSON.stringify({
              building_id: buildingId,
              title: title,
              description: desc,
              category: category,
              priority: priority
            })
          });
          const resData = await res.json();
          if (res.ok) {
            console.log('Ticket Created via API:', resData);
          }
        } catch (err) {
          console.log('Ticket API Note:', err);
        }
      }

      closeTenantTicketModal();
      alert("Đã gửi phiếu yêu cầu hỗ trợ lên Ban Quản Lý thành công!\nKỹ thuật viên sẽ xử lý và liên hệ khắc phục sớm nhất.");
      renderAdminTables();
    }

    function calculateQuickQuote() {
      const pricePerM2 = parseFloat(document.getElementById("calc-building")?.value) || 0;
      const area = parseFloat(document.getElementById("calc-area")?.value) || 0;
      const service = parseFloat(document.getElementById("calc-service")?.value) || 0;

      const totalUsd = area * (pricePerM2 + service);
      const totalVnd = totalUsd * 24500;

      if (document.getElementById("calc-total-usd")) {
        document.getElementById("calc-total-usd").innerText = `$${totalUsd.toLocaleString()} / tháng`;
      }
      if (document.getElementById("calc-total-vnd")) {
        document.getElementById("calc-total-vnd").innerText = `Tương đương: ~${totalVnd.toLocaleString('vi-VN')} VNĐ / tháng (Chưa gồm VAT)`;
      }
    }


    </script>
