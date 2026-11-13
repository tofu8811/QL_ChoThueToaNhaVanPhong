<script>
    /* ==========================================================================
       7. SPA HASH ROUTING & VIEW NAVIGATION (GIỮ NGUYÊN TRANG KHI F5)
       ========================================================================== */
    const TAB_TO_ROUTE = {
      'tab-admin-dashboard': 'admin/dashboard',
      'tab-admin-buildings': 'admin/buildings',
      'tab-admin-tickets': 'admin/tickets',
      'tab-admin-contracts': 'admin/contracts',
      'tab-admin-staff': 'admin/staff',
      'tab-sales-leads': 'admin/sales-leads',
      'tab-tenant-workspace': 'tenant/workspace',
      'tab-tenant-tickets': 'tenant/tickets',
      'tab-tenant-bills': 'tenant/bills',
    };

    const ROUTE_TO_TAB = {
      'admin/dashboard': 'tab-admin-dashboard',
      'admin': 'tab-admin-dashboard',
      'admin/buildings': 'tab-admin-buildings',
      'admin/tickets': 'tab-admin-tickets',
      'admin/contracts': 'tab-admin-contracts',
      'admin/staff': 'tab-admin-staff',
      'admin/sales-leads': 'tab-sales-leads',
      'tenant/workspace': 'tab-tenant-workspace',
      'tenant': 'tab-tenant-workspace',
      'tenant/tickets': 'tab-tenant-tickets',
      'tenant/bills': 'tab-tenant-bills',
    };

    // Hàm cập nhật URL hash & lưu session lịch sử
    function updateAppRoute(routePath) {
      if (!routePath) return;
      const cleanRoute = routePath.replace(/^#\/?/, '').trim();
      localStorage.setItem('last_active_route', cleanRoute);
      if (window.location.hash !== '#' + cleanRoute) {
        history.replaceState(null, '', '#' + cleanRoute);
      }
    }

    // Hàm điều hướng tuyến SPA khi nạp trang (F5) hoặc hash thay đổi
    function handleAppRouting() {
      let hash = window.location.hash ? window.location.hash.replace(/^#\/?/, '').trim() : '';
      if (!hash) {
        hash = localStorage.getItem('last_active_route') || '';
      }

      // 1. Cổng thông tin công khai (Portal)
      if (!hash || hash === 'portal' || hash === 'home') {
        switchView('portal-view', false);
        if (typeof showPublicPage === 'function') showPublicPage('page-home', false);
        return;
      }

      if (hash.startsWith('building/')) {
        const bId = hash.replace('building/', '').trim();
        switchView('portal-view', false);
        if (typeof openBuildingDetail === 'function') openBuildingDetail(bId, false);
        return;
      }

      if (hash === 'quote') {
        switchView('portal-view', false);
        if (typeof showPublicPage === 'function') showPublicPage('page-quote', false);
        return;
      }

      // 2. Cổng Khách Thuê (Tenant Workspace)
      if (hash.startsWith('tenant')) {
        handleRoleSwitch('tenant', false);
        if (hash === 'tenant/tickets') {
          switchFilamentTab('tab-tenant-tickets', document.querySelector('.filament-link[onclick*="tab-tenant-tickets"]'), false);
        } else if (hash === 'tenant/bills') {
          switchFilamentTab('tab-tenant-bills', document.querySelector('.filament-link[onclick*="tab-tenant-bills"]'), false);
        } else {
          switchFilamentTab('tab-tenant-workspace', document.querySelector('.filament-link[onclick*="tab-tenant-workspace"]'), false);
        }
        return;
      }

      // 3. Phân hệ Admin / BQL / Sales
      if (hash.startsWith('admin')) {
        if (hash === 'admin/sales-leads') {
          handleRoleSwitch('sales', false);
          switchFilamentTab('tab-sales-leads', document.querySelector('.filament-link[onclick*="tab-sales-leads"]'), false);
          return;
        }

        handleRoleSwitch('admin', false);

        if (hash.startsWith('admin/staff')) {
          const parts = hash.split('/');
          const subtab = parts[2] || 'admins';
          const staffLink = document.getElementById('nav-item-staff');
          switchFilamentTab('tab-admin-staff', staffLink, false);
          if (typeof switchStaffSubTab === 'function') {
            const btnEl = document.getElementById(`btn-subtab-${subtab}`);
            switchStaffSubTab(subtab, btnEl, false);
          }
          return;
        }

        const tabId = ROUTE_TO_TAB[hash] || 'tab-admin-dashboard';
        const linkEl = document.querySelector(`.filament-link[onclick*="${tabId}"]`);
        switchFilamentTab(tabId, linkEl, false);
        return;
      }

      // Mặc định về portal
      switchView('portal-view', false);
    }

    // Lắng nghe thay đổi hash trên thanh địa chỉ trình duyệt (Back / Forward)
    window.addEventListener('hashchange', handleAppRouting);

    function switchView(viewType, updateRoute = true) {
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
        if (updateRoute) updateAppRoute('portal');
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

    function handleRoleSwitch(role, updateRoute = true) {
      switchView('admin-view', false);
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
        if (updateRoute) switchFilamentTab('tab-admin-dashboard', document.querySelector('#nav-group-admin .filament-link'), true);
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
        if (updateRoute) switchFilamentTab('tab-admin-dashboard', document.querySelector('#nav-group-admin .filament-link'), true);
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
        if (updateRoute) switchFilamentTab('tab-sales-leads', document.querySelector('#nav-group-sales .filament-link'), true);
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

        if (updateRoute) switchFilamentTab('tab-tenant-workspace', document.querySelector('#nav-group-tenant .filament-link'), true);
      } else if (role === 'portal') {
        switchView('portal-view', updateRoute);
      }
    }

    function switchFilamentTab(tabId, el, updateRoute = true) {
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

      // Cập nhật địa chỉ URL để khi F5 không bị quăng về trang chủ
      if (updateRoute) {
        if (tabId === 'tab-admin-staff') {
          updateAppRoute('admin/staff/' + (typeof currentStaffTab !== 'undefined' ? currentStaffTab : 'admins'));
        } else {
          updateAppRoute(TAB_TO_ROUTE[tabId] || 'admin/dashboard');
        }
      }

      if (tabId === 'tab-admin-tickets' || tabId === 'tab-tenant-tickets') {
        loadTicketsList();
      }
      if (tabId === 'tab-admin-staff') {
        if (typeof preloadAllStaffData === 'function') preloadAllStaffData();
        if (typeof switchStaffSubTab === 'function') switchStaffSubTab(currentStaffTab, null, updateRoute);
      }
      if (tabId === 'tab-admin-buildings') {
        loadAdminBuildingsList();
      }
      if (tabId === 'tab-sales-leads' || tabId === 'tab-sales-pipeline') {
        loadCrmLeads();
      }
      if (tabId === 'tab-admin-tenants') {
        loadTenantsList();
      }
      if (tabId === 'tab-admin-contracts') {
        loadContractsList();
      }
      if (tabId === 'tab-tenant-workspace') {
        loadTenantWorkspaceContracts();
      }
    }

    /* ==========================================================================
       8. POPULATING FILAMENT ADMIN TABLES
       ========================================================================== */
    function renderAdminTables() {
      // 1. Admin Buildings Table từ RESTful API
      loadAdminBuildingsList();

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
