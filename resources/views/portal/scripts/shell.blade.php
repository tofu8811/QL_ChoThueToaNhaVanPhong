<script>
    /* ==========================================================================
       7. SPA HASH ROUTING & VIEW NAVIGATION (GIỮ NGUYÊN TRANG KHI F5)
       ========================================================================== */
    const TAB_TO_ROUTE = {
      'tab-admin-dashboard': 'admin/dashboard',
      'tab-admin-buildings': 'admin/buildings',
      'tab-admin-tenants': 'admin/tenants',
      'tab-admin-contracts': 'admin/contracts',
      'tab-admin-billing': 'admin/billing',
      'tab-admin-tickets': 'admin/tickets',
      'tab-admin-staff': 'admin/staff',
      'tab-sales-leads': 'admin/sales-leads',
      'tab-sales-quotes': 'admin/sales-quotes',
      'tab-sales-pipeline': 'admin/sales-pipeline',
      'tab-tenant-workspace': 'tenant/workspace',
      'tab-tenant-tickets': 'tenant/tickets',
      'tab-tenant-bills': 'tenant/bills',
    };

    const ROUTE_TO_TAB = {
      'admin/dashboard': 'tab-admin-dashboard',
      'admin': 'tab-admin-dashboard',
      'admin/buildings': 'tab-admin-buildings',
      'admin/tenants': 'tab-admin-tenants',
      'admin/contracts': 'tab-admin-contracts',
      'admin/billing': 'tab-admin-billing',
      'admin/tickets': 'tab-admin-tickets',
      'admin/staff': 'tab-admin-staff',
      'admin/sales-leads': 'tab-sales-leads',
      'admin/sales-quotes': 'tab-sales-quotes',
      'admin/sales-pipeline': 'tab-sales-pipeline',
      'tenant/workspace': 'tab-tenant-workspace',
      'tenant': 'tab-tenant-workspace',
      'tenant/tickets': 'tab-tenant-tickets',
      'tenant/bills': 'tab-tenant-bills',
    };

    const TAB_BREADCRUMB_MAP = {
      'tab-admin-dashboard': 'Tổng quan hệ thống',
      'tab-admin-buildings': 'Quản lý tòa nhà & diện tích sàn',
      'tab-admin-tenants': 'Quản lý khách hàng thuê',
      'tab-admin-contracts': 'Quản lý hợp đồng thuê',
      'tab-admin-billing': 'Quản lý công nợ & thanh toán',
      'tab-admin-tickets': 'Quản lý bảo trì & sửa chữa',
      'tab-admin-staff': 'Quản trị nhân sự & phân quyền RBAC',
      'tab-sales-leads': 'Quản lý Leads & Pipeline CRM',
      'tab-sales-quotes': 'Lập & Quản lý báo giá',
      'tab-sales-pipeline': 'Bảng Kanban Pipeline CRM',
      'tab-tenant-workspace': 'Văn phòng & hợp đồng của tôi',
      'tab-tenant-tickets': 'Yêu cầu hỗ trợ & đăng ký thi công',
      'tab-tenant-bills': 'Hóa đơn & thanh toán trực tuyến',
    };

    // Hàm cập nhật URL hash & đẩy vào ngăn xếp lịch sử trình duyệt (Hỗ trợ nút Back / Forward của Chrome)
    function updateAppRoute(routePath, replace = false) {
      if (!routePath) return;
      const cleanRoute = routePath.replace(/^#\/?/, '').trim();
      localStorage.setItem('last_active_route', cleanRoute);
      const currentHash = window.location.hash ? window.location.hash.replace(/^#\/?/, '').trim() : '';
      if (currentHash !== cleanRoute) {
        if (replace) {
          history.replaceState({ route: cleanRoute }, '', '#' + cleanRoute);
        } else {
          history.pushState({ route: cleanRoute }, '', '#' + cleanRoute);
        }
      }
    }

    // Tự động đóng các Modal đang mở khi người dùng nhấn nút Back / Forward trên trình duyệt
    function closeAllOpenModalsOnHistoryNav() {
      document.querySelectorAll('.mo-modal-overlay').forEach(m => {
        if (m.style.display === 'flex' || m.style.display === 'block') {
          m.style.display = 'none';
        }
      });
      document.querySelectorAll('.mo-modal.show').forEach(m => m.classList.remove('show'));
      const notifDropdown = document.getElementById('bms-notif-dropdown');
      if (notifDropdown) notifDropdown.style.display = 'none';
      const searchDropdown = document.getElementById('bms-topbar-search-results');
      if (searchDropdown) searchDropdown.style.display = 'none';
    }

    // Hàm điều hướng tuyến SPA khi nạp trang (F5) hoặc khi bấm nút Back / Forward trên trình duyệt
    function handleAppRouting(isHistoryNavigation = false) {
      // Nếu đang ở các trang SSR độc lập (ví dụ /quan-ly-hop-dong, /cong-khach-thue, /quan-ly-leads, /toa-nha/...)
      // thì không can thiệp điều hướng SPA của trang chủ
      if (window.location.pathname !== '/' && window.location.pathname !== '') {
        return;
      }
      let hash = window.location.hash ? window.location.hash.replace(/^#\/?/, '').trim() : '';

      if (isHistoryNavigation) {
        closeAllOpenModalsOnHistoryNav();
        // Khi người dùng bấm Back/Forward trên trình duyệt, đồng bộ lại last_active_route theo URL hiện tại
        localStorage.setItem('last_active_route', hash || 'portal');
      } else if (!hash) {
        // Chỉ khi mới tải trang (F5) mà chưa có hash thì mới khôi phục route gần nhất bằng replaceState
        const savedToken = localStorage.getItem('auth_token');
        const savedRole = String(localStorage.getItem('auth_role') || '').toLowerCase();
        const savedLastRoute = localStorage.getItem('last_active_route') || '';

        if (savedToken && savedLastRoute && savedLastRoute !== 'portal' && savedLastRoute !== 'home') {
          hash = savedLastRoute;
          history.replaceState({ route: hash }, '', '#' + hash);
        } else if (savedToken && savedRole) {
          if (savedRole === 'tenant') {
            hash = 'tenant/workspace';
          } else if (savedRole === 'sales' || savedRole === 'sale') {
            hash = 'admin/sales-leads';
          } else if (savedRole === 'pm' || savedRole === 'property_manager') {
            hash = 'admin/buildings';
          } else if (savedRole === 'accountant') {
            hash = 'admin/billing';
          } else if (savedRole === 'admin' || savedRole === 'super_admin') {
            hash = 'admin/dashboard';
          }
          if (hash) {
            history.replaceState({ route: hash }, '', '#' + hash);
          }
        }
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

      // 3. Phân hệ Admin / BQL / Sales / Kế toán
      if (hash.startsWith('admin')) {
        const savedRole = String(localStorage.getItem('auth_role') || '').toUpperCase();
        // RBAC Chặn Khách thuê không được truy cập phân hệ quản trị
        if (savedRole === 'TENANT') {
          alert('Tài khoản Khách thuê không có quyền truy cập trang quản trị.');
          handleRoleSwitch('tenant', false);
          switchFilamentTab('tab-tenant-workspace', document.querySelector('#nav-group-tenant .filament-link'), true);
          return;
        }

        let uiRole = 'admin';
        if (savedRole === 'PM' || savedRole === 'PROPERTY_MANAGER') uiRole = 'pm';
        else if (savedRole === 'SALES') uiRole = 'sales';
        else if (savedRole === 'ACCOUNTANT') uiRole = 'accountant';
        else if (hash === 'admin/sales-leads' || hash === 'admin/sales-quotes' || hash === 'admin/sales-pipeline') {
          if (!savedRole) uiRole = 'sales';
        }

        handleRoleSwitch(uiRole, false);

        if (hash.startsWith('admin/staff')) {
          if (uiRole !== 'admin') {
            alert('Bạn không có quyền truy cập module Quản trị nhân sự & RBAC.');
            const fallbackTab = (typeof DEFAULT_TAB_BY_ROLE !== 'undefined' ? DEFAULT_TAB_BY_ROLE[uiRole] : 'tab-admin-dashboard');
            const fallbackLink = document.querySelector(`.filament-link[onclick*="${fallbackTab}"]`);
            switchFilamentTab(fallbackTab, fallbackLink, true);
            return;
          }

          const parts = hash.split('/');
          let subtab = parts[2] || 'admins';
          // Nếu không phải Super Admin thì chặn vào subtab admins, tự chuyển sang sales
          if (subtab === 'admins' && typeof isSuperAdminUser === 'function' && !isSuperAdminUser()) {
            subtab = 'sales';
          }

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

    // Lắng nghe sự kiện Back / Forward của trình duyệt (popstate & hashchange)
    window.addEventListener('popstate', function () {
      handleAppRouting(true);
    });
    window.addEventListener('hashchange', function () {
      handleAppRouting(true);
    });

    function switchView(viewType, updateRoute = true) {
      const portal = document.getElementById("portal-wrapper");
      const admin = document.getElementById("admin-wrapper");

      if (!admin && !portal) return;

      if (!admin) {
        if (portal) portal.style.display = 'block';
        return;
      }
      if (!portal) {
        if (admin) admin.style.display = 'block';
        return;
      }

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
      } else if (role === 'ACCOUNTANT') {
        handleRoleSwitch('accountant');
      } else if (role === 'TENANT') {
        handleRoleSwitch('tenant');
      } else {
        handleRoleSwitch('admin');
      }
    }

    // Ma trận phân quyền truy cập Tab theo từng Role (RBAC Route & UI Guard)
    const ALLOWED_TABS_BY_ROLE = {
      admin: [
        'tab-admin-dashboard', 'tab-admin-buildings', 'tab-admin-tenants',
        'tab-admin-contracts', 'tab-admin-billing', 'tab-admin-tickets',
        'tab-sales-leads', 'tab-sales-pipeline', 'tab-sales-quotes', 'tab-admin-staff'
      ],
      pm: [
        'tab-admin-dashboard', 'tab-admin-buildings', 'tab-admin-tenants',
        'tab-admin-contracts', 'tab-admin-tickets'
      ],
      sales: [
        'tab-sales-leads', 'tab-sales-pipeline', 'tab-sales-quotes',
        'tab-admin-buildings', 'tab-admin-tenants', 'tab-admin-contracts'
      ],
      accountant: [
        'tab-admin-billing', 'tab-admin-buildings', 'tab-admin-tenants',
        'tab-admin-contracts'
      ],
      tenant: [
        'tab-tenant-workspace', 'tab-tenant-tickets', 'tab-tenant-bills'
      ]
    };

    const DEFAULT_TAB_BY_ROLE = {
      admin: 'tab-admin-dashboard',
      pm: 'tab-admin-dashboard',
      sales: 'tab-sales-leads',
      accountant: 'tab-admin-billing',
      tenant: 'tab-tenant-workspace'
    };

    function handleRoleSwitch(role, updateRoute = true) {
      window.currentUiRole = role;
      switchView('admin-view', false);
      const selectHeader = document.getElementById("filament-role-select");
      if (selectHeader) selectHeader.value = role;

      const sidebarRoleBadge = document.getElementById("sidebar-role-badge");
      const userName = document.getElementById("filament-user-name");
      const userSub = document.getElementById("filament-user-sub");
      const userAvatar = document.getElementById("filament-user-avatar");
      const roleSwitcherContainer = document.getElementById("role-switcher-container");
      const sidebarFilamentBtn = document.getElementById("sidebar-filament-btn");
      const buildingSwitcher = document.getElementById("sidebar-building-switcher");

      const groupAdmin = document.getElementById("nav-group-admin");
      const groupSales = document.getElementById("nav-group-sales");
      const groupSystem = document.getElementById("nav-group-system");
      const groupTenant = document.getElementById("nav-group-tenant");

      const navDashboard = document.getElementById("nav-item-dashboard");
      const navBuildings = document.getElementById("nav-item-buildings");
      const navTenants = document.getElementById("nav-item-tenants");
      const navContracts = document.getElementById("nav-item-contracts");
      const navBilling = document.getElementById("nav-item-billing");
      const navTickets = document.getElementById("nav-item-tickets");
      const navStaff = document.getElementById("nav-item-staff");

      // Lấy thông tin user đăng nhập thực tế nếu có
      const authUserStr = localStorage.getItem('auth_user');
      const realRole = String(localStorage.getItem('auth_role') || '').toUpperCase();
      let realUser = null;
      if (authUserStr) {
        try { realUser = JSON.parse(authUserStr); } catch (e) {}
      }

      const rawName = realUser?.company_name || realUser?.full_name || realUser?.name;

      // Reset tất cả các item trong nav-group-admin về mặc định flex
      [navDashboard, navBuildings, navTenants, navContracts, navBilling, navTickets].forEach(item => {
        if (item) item.style.display = 'flex';
      });
      if (navStaff) navStaff.style.display = (role === 'admin') ? 'flex' : 'none';
      if (groupSystem) groupSystem.style.display = (role === 'admin') ? 'block' : 'none';
      if (buildingSwitcher) buildingSwitcher.style.display = (role === 'tenant') ? 'none' : 'flex';

      // Nút Tạo Tòa Nhà: Chỉ Admin và PM
      const btnCreateBuilding = document.getElementById("btn-create-building");
      if (btnCreateBuilding) {
        btnCreateBuilding.style.display = (role === 'admin' || role === 'pm') ? 'inline-flex' : 'none';
      }

      // Cập nhật giao diện Dashboard nghiệp vụ theo Role (Admin vs PM theo feature/frontend)
      const isPmRole = (role === 'pm');
      document.querySelectorAll('.kpi-admin-content').forEach(el => {
        el.style.display = isPmRole ? 'none' : (el.classList.contains('bms-alert-card') ? 'flex' : 'block');
      });
      document.querySelectorAll('.kpi-pm-content').forEach(el => {
        el.style.display = isPmRole ? (el.classList.contains('bms-alert-card') ? 'flex' : 'block') : 'none';
      });

      const dashTitle = document.getElementById('dashboard-page-title');
      const dashSub = document.getElementById('dashboard-page-subtitle');
      if (dashTitle) {
        dashTitle.innerText = isPmRole ? 'Tổng Quan Vận Hành & Lấp Đầy Cao Ốc' : 'Tổng Quan Vận Hành & Kinh Doanh Tòa Nhà';
      }
      if (dashSub) {
        dashSub.innerText = isPmRole
          ? 'Bức tranh toàn cảnh về hiện trạng tòa nhà, tỷ lệ lấp đầy, tiến độ xử lý sự cố kỹ thuật và phê duyệt vận hành.'
          : 'Bức tranh toàn cảnh về quy mô tài sản, hợp đồng thuê, doanh thu công nợ và trạng thái vận hành toàn hệ thống.';
      }

      if (role === 'admin') {
        window.currentStaffUser = (typeof SERVER_STAFF !== 'undefined' && SERVER_STAFF.admins) ? SERVER_STAFF.admins[0] : null;
        const isAdmin = (realRole === 'ADMIN' || realRole === 'SUPER_ADMIN' || !realRole);
        const isSuper = (realRole === 'SUPER_ADMIN' || (realUser && (realUser.is_super_admin === true || realUser.is_super_admin === 1 || realUser.is_super_admin === '1')) || (!realRole && !authUserStr));
        if (roleSwitcherContainer) roleSwitcherContainer.style.display = isAdmin ? 'inline-flex' : 'none';
        if (sidebarFilamentBtn) sidebarFilamentBtn.style.display = isAdmin ? 'inline-flex' : 'none';

        sidebarRoleBadge.innerText = isSuper ? 'Super Admin' : 'Admin';
        userName.innerText = rawName || (isSuper ? 'Nguyễn Quản Trị Hệ Thống' : 'Phạm Quản Trị');
        userSub.innerText = isSuper ? 'Super Admin • Toàn hệ thống' : 'Quản trị viên • Vận hành';
        userAvatar.innerText = isSuper ? 'SA' : 'AD';
        if (groupAdmin) groupAdmin.style.display = 'block';
        if (groupSales) groupSales.style.display = 'block';
        if (groupTenant) groupTenant.style.display = 'none';

        // Ẩn sub-tab Admins nếu không phải Super Admin
        const btnSubtabAdmins = document.getElementById('btn-subtab-admins');
        const panelSubtabAdmins = document.getElementById('subpanel-staff-admins');
        if (btnSubtabAdmins) btnSubtabAdmins.style.display = isSuper ? 'inline-flex' : 'none';
        if (panelSubtabAdmins && !isSuper) panelSubtabAdmins.style.display = 'none';

        if (updateRoute) switchFilamentTab('tab-admin-dashboard', navDashboard, true);
      } else if (role === 'pm') {
        window.currentStaffUser = (typeof SERVER_STAFF !== 'undefined' && SERVER_STAFF.pm) ? SERVER_STAFF.pm[0] : null;
        if (roleSwitcherContainer) roleSwitcherContainer.style.display = (realRole === 'ADMIN' || realRole === 'SUPER_ADMIN') ? 'inline-flex' : 'none';
        if (sidebarFilamentBtn) sidebarFilamentBtn.style.display = 'none';

        sidebarRoleBadge.innerText = 'Ban Quản Lý';
        userName.innerText = rawName || 'Đỗ Minh Quang';
        userSub.innerText = 'Trưởng ban Quản lý';
        userAvatar.innerText = 'QA';
        if (groupAdmin) groupAdmin.style.display = 'block';
        if (navBilling) navBilling.style.display = 'none'; // PM không có thẩm quyền Công nợ kế toán
        if (groupSales) groupSales.style.display = 'none';
        if (groupSystem) groupSystem.style.display = 'none';
        if (groupTenant) groupTenant.style.display = 'none';
        if (updateRoute) switchFilamentTab('tab-admin-dashboard', navDashboard, true);
      } else if (role === 'sales') {
        window.currentStaffUser = (typeof SERVER_STAFF !== 'undefined' && SERVER_STAFF.sales) ? SERVER_STAFF.sales[0] : null;
        if (roleSwitcherContainer) roleSwitcherContainer.style.display = (realRole === 'ADMIN' || realRole === 'SUPER_ADMIN') ? 'inline-flex' : 'none';
        if (sidebarFilamentBtn) sidebarFilamentBtn.style.display = 'none';

        sidebarRoleBadge.innerText = 'Chuyên Viên Sales';
        userName.innerText = rawName || 'Lê Hoàng Long';
        userSub.innerText = 'Chuyên viên Leasing & Báo giá';
        userAvatar.innerText = 'SL';
        // Sales được xem Quản lý tòa nhà & sàn (chỉ đọc), Khách hàng, Hợp đồng + nhóm Kinh doanh (Leads & Báo giá)
        if (groupAdmin) groupAdmin.style.display = 'block';
        if (navDashboard) navDashboard.style.display = 'none'; // Ẩn Tổng quan để bảo mật doanh thu, công nợ và sự cố kỹ thuật
        if (navBilling) navBilling.style.display = 'none'; // Ẩn Công nợ kế toán
        if (navTickets) navTickets.style.display = 'none'; // Ẩn Bảo trì kỹ thuật
        if (groupSales) groupSales.style.display = 'block';
        if (groupSystem) groupSystem.style.display = 'none'; // Ẩn Nhân sự RBAC
        if (groupTenant) groupTenant.style.display = 'none';
        if (updateRoute) switchFilamentTab('tab-sales-leads', document.getElementById('nav-item-leads'), true);
      } else if (role === 'accountant') {
        window.currentStaffUser = (typeof SERVER_STAFF !== 'undefined' && SERVER_STAFF.accountants) ? SERVER_STAFF.accountants[0] : null;
        if (roleSwitcherContainer) roleSwitcherContainer.style.display = (realRole === 'ADMIN' || realRole === 'SUPER_ADMIN') ? 'inline-flex' : 'none';
        if (sidebarFilamentBtn) sidebarFilamentBtn.style.display = 'none';

        sidebarRoleBadge.innerText = 'Kế Toán';
        userName.innerText = rawName || 'Hoàng Minh Thư';
        userSub.innerText = 'Kế toán công nợ & Đối soát';
        userAvatar.innerText = 'KT';
        // Kế toán xem Tổng quan, Công nợ & Thanh toán, Hợp đồng, Khách hàng
        if (groupAdmin) groupAdmin.style.display = 'block';
        if (navTickets) navTickets.style.display = 'none';
        if (groupSales) groupSales.style.display = 'none';
        if (groupSystem) groupSystem.style.display = 'none';
        if (groupTenant) groupTenant.style.display = 'none';
        if (updateRoute) switchFilamentTab('tab-admin-billing', navBilling, true);
      } else if (role === 'tenant') {
        window.currentStaffUser = (typeof SAMPLE_TENANT !== 'undefined') ? SAMPLE_TENANT : null;
        if (roleSwitcherContainer) roleSwitcherContainer.style.display = (realRole === 'ADMIN' || realRole === 'SUPER_ADMIN') ? 'inline-flex' : 'none';
        if (sidebarFilamentBtn) sidebarFilamentBtn.style.display = 'none';

        sidebarRoleBadge.innerText = 'Khách Thuê';
        userName.innerText = rawName || 'Công ty Cổ phần VNG';
        userSub.innerText = realUser?.login_email || realUser?.billing_email || realUser?.email || 'Doanh nghiệp khách thuê';
        userAvatar.innerText = 'DN';
        if (groupAdmin) groupAdmin.style.display = 'none';
        if (groupSales) groupSales.style.display = 'none';
        if (groupSystem) groupSystem.style.display = 'none';
        if (groupTenant) groupTenant.style.display = 'block';

        const welcomeTitle = document.getElementById("tenant-welcome-title");
        if (welcomeTitle) welcomeTitle.innerText = "Văn Phòng Của Tôi — " + (rawName || 'Công ty Cổ phần VNG');

        const contractRep = document.getElementById("tenant-contract-rep");
        if (contractRep && realUser?.representative_name) {
          contractRep.innerText = realUser.representative_name + (realUser.representative_position ? ` (${realUser.representative_position})` : ' (Đại diện pháp luật)');
        }

        if (updateRoute) switchFilamentTab('tab-tenant-workspace', document.querySelector('#nav-group-tenant .filament-link'), true);
      } else if (role === 'portal') {
        switchView('portal-view', updateRoute);
      }

      // Tự động tải lại danh sách Tòa nhà theo đúng phạm vi phân công của vai trò
      if (typeof loadAdminBuildingsList === 'function') {
        loadAdminBuildingsList();
      }
    }

    function switchFilamentTab(tabId, el, updateRoute = true) {
      // RBAC Security Route Guard: Chặn truy cập tab vượt thẩm quyền
      const currentRole = window.currentUiRole || 'admin';
      const allowed = ALLOWED_TABS_BY_ROLE[currentRole] || ALLOWED_TABS_BY_ROLE.admin;
      if (!allowed.includes(tabId)) {
        console.warn(`[RBAC Guard] Vai trò '${currentRole}' không có quyền truy cập tab '${tabId}'. Chuyển hướng về tab được phép.`);
        const fallbackTab = DEFAULT_TAB_BY_ROLE[currentRole] || 'tab-admin-dashboard';
        const fallbackLink = document.querySelector(`.filament-link[onclick*="${fallbackTab}"]`);
        return switchFilamentTab(fallbackTab, fallbackLink, true);
      }

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
      if (el) {
        el.classList.add('active');
      } else if (tabId === 'tab-sales-pipeline') {
        document.getElementById('nav-item-leads')?.classList.add('active');
      }

      // Cập nhật Breadcrumb động trên Topbar chuẩn Hình 2
      const bcEl = document.getElementById('topbar-current-breadcrumb');
      if (bcEl) {
        bcEl.innerText = el?.getAttribute('data-breadcrumb') || TAB_BREADCRUMB_MAP[tabId] || 'Quản lý trạng thái mặt bằng';
      }

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

    // Chuyển đổi giữa chế độ xem Bảng và Kanban trong phân hệ CRM Leads (Loại bỏ trùng lặp menu)
    function switchCrmViewMode(mode) {
      const leadsLink = document.getElementById('nav-item-leads');
      if (mode === 'kanban') {
        switchFilamentTab('tab-sales-pipeline', leadsLink, true);
      } else {
        switchFilamentTab('tab-sales-leads', leadsLink, true);
      }
    }

    // Chuyển đổi giữa Hiệu suất theo tòa nhà (Hình 2) và Biểu đồ 6 tháng (Hình 1) trên Dashboard
    function toggleDashboardPerfView(mode) {
      const bldBox = document.getElementById('dashboard-perf-building-box');
      const trendBox = document.getElementById('dashboard-perf-trend-box');
      const btnBld = document.getElementById('btn-perf-view-building');
      const btnTrend = document.getElementById('btn-perf-view-trend');
      if (!bldBox || !trendBox) return;

      if (mode === 'trend') {
        bldBox.style.display = 'none';
        trendBox.style.display = 'block';
        if (btnTrend) {
          btnTrend.style.background = '#fff';
          btnTrend.style.color = '#0f172a';
          btnTrend.style.fontWeight = '700';
          btnTrend.style.boxShadow = '0 1px 2px rgba(0,0,0,0.06)';
        }
        if (btnBld) {
          btnBld.style.background = 'transparent';
          btnBld.style.color = '#64748b';
          btnBld.style.fontWeight = '600';
          btnBld.style.boxShadow = 'none';
        }
      } else {
        bldBox.style.display = 'block';
        trendBox.style.display = 'none';
        if (btnBld) {
          btnBld.style.background = '#fff';
          btnBld.style.color = '#0f172a';
          btnBld.style.fontWeight = '700';
          btnBld.style.boxShadow = '0 1px 2px rgba(0,0,0,0.06)';
        }
        if (btnTrend) {
          btnTrend.style.background = 'transparent';
          btnTrend.style.color = '#64748b';
          btnTrend.style.fontWeight = '600';
          btnTrend.style.boxShadow = 'none';
        }
      }
    }

    // Ẩn thanh lăn bên Sidebar mặc định, chỉ hiện ra khi đang lăn chuột
    let sidebarScrollTimer = null;
    document.addEventListener('DOMContentLoaded', function() {
      const sidebarEl = document.getElementById('bms-main-sidebar') || document.querySelector('.filament-sidebar');
      if (sidebarEl) {
        const showScrollbarTemporary = () => {
          sidebarEl.classList.add('is-scrolling');
          if (sidebarScrollTimer) clearTimeout(sidebarScrollTimer);
          sidebarScrollTimer = setTimeout(() => {
            sidebarEl.classList.remove('is-scrolling');
          }, 800);
        };
        sidebarEl.addEventListener('scroll', showScrollbarTemporary, { passive: true });
        sidebarEl.addEventListener('wheel', showScrollbarTemporary, { passive: true });
      }
    });

    // Xử lý tìm kiếm nhanh đồng bộ toàn cục trên Topbar
    function handleTopbarQuickSearch(keyword) {
      const kw = (keyword || '').trim();
      const lowerKw = kw.toLowerCase();
      const resultsBox = document.getElementById('bms-topbar-search-results');
      const activePane = document.querySelector('.filament-tab-pane.active');
      const paneId = activePane ? activePane.id : '';

      // 1. Lọc trực tiếp bảng dữ liệu của tab đang mở
      if (paneId === 'tab-admin-buildings' && typeof handleAdminBuildingSearch === 'function') {
        const inp = document.getElementById('admin-building-search');
        if (inp) inp.value = kw;
        handleAdminBuildingSearch(kw);
      } else if (paneId === 'tab-admin-contracts' && typeof handleContractSearch === 'function') {
        const inp = document.getElementById('contract-search-input');
        if (inp) inp.value = kw;
        handleContractSearch(kw);
      } else if (paneId === 'tab-admin-tenants' && typeof handleTenantSearch === 'function') {
        const inp = document.getElementById('tenant-search-input');
        if (inp) inp.value = kw;
        handleTenantSearch(kw);
      } else if (paneId === 'tab-sales-leads' && typeof handleCrmSearch === 'function') {
        const inp = document.getElementById('crm-search-input');
        if (inp) inp.value = kw;
        handleCrmSearch(kw);
      } else if (paneId === 'tab-admin-tickets' && typeof loadTicketsList === 'function') {
        const inp = document.getElementById('ticket-filter-keyword');
        if (inp) inp.value = kw;
        loadTicketsList('admin');
      } else if (paneId === 'tab-admin-billing') {
        const inp = document.getElementById('billing-search-input');
        if (inp) inp.value = kw;
        if (typeof filterAdminBillingRows === 'function') {
          filterAdminBillingRows();
        } else {
          const rows = document.querySelectorAll('#admin-billing-tbody tr');
          rows.forEach(r => {
            r.style.display = (!lowerKw || r.innerText.toLowerCase().includes(lowerKw)) ? '' : 'none';
          });
        }
      } else if (paneId === 'tab-admin-staff') {
        const sub = (typeof currentStaffTab !== 'undefined') ? currentStaffTab : 'admins';
        const tbodyId = `tbody-staff-${sub}`;
        const rows = document.querySelectorAll(`#${tbodyId} tr`);
        rows.forEach(r => {
          r.style.display = (!lowerKw || r.innerText.toLowerCase().includes(lowerKw)) ? '' : 'none';
        });
      }

      // 2. Hiển thị Dropdown kết quả tìm kiếm nhanh toàn cục (hữu ích khi đứng ở Dashboard hoặc muốn nhảy nhanh sang mục khác)
      if (!resultsBox) return;
      if (!kw) {
        resultsBox.style.display = 'none';
        resultsBox.innerHTML = '';
        return;
      }

      let htmlItems = [];

      // Tìm trong danh sách Tòa nhà (BUILDINGS_DATA)
      if (typeof BUILDINGS_DATA !== 'undefined' && Array.isArray(BUILDINGS_DATA)) {
        const matchedBlds = BUILDINGS_DATA.filter(b =>
          (b.name && b.name.toLowerCase().includes(lowerKw)) ||
          (b.district && b.district.toLowerCase().includes(lowerKw)) ||
          (b.address && b.address.toLowerCase().includes(lowerKw))
        ).slice(0, 4);

        if (matchedBlds.length > 0) {
          htmlItems.push(`<div style="font-size:10.5px;font-weight:700;color:#64748b;text-transform:uppercase;padding:6px 10px 4px;">Tòa nhà văn phòng (${matchedBlds.length})</div>`);
          matchedBlds.forEach(b => {
            htmlItems.push(`
              <div onclick="jumpFromTopbarSearch('tab-admin-buildings', '${escapeHtml(b.name)}')" style="padding:8px 10px;border-radius:8px;cursor:pointer;display:flex;align-items:center;gap:10px;transition:background 0.15s;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='transparent'">
                <div style="width:28px;height:28px;border-radius:6px;background:#eff6ff;color:#2563eb;display:flex;align-items:center;justify-content:center;font-size:12px;flex-shrink:0;"><i class="fa-solid fa-building"></i></div>
                <div style="min-width:0;flex:1;">
                  <div style="font-weight:700;font-size:12.5px;color:#0f172a;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${escapeHtml(b.name)}</div>
                  <div style="font-size:11px;color:#64748b;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${escapeHtml(b.address || b.district || '')}</div>
                </div>
              </div>
            `);
          });
        }
      }

      // Lối tắt tìm kiếm trực tiếp trong Khách thuê, Hợp đồng, Bảo trì
      htmlItems.push(`<div style="font-size:10.5px;font-weight:700;color:#64748b;text-transform:uppercase;padding:8px 10px 4px;border-top:1px solid #f1f5f9;margin-top:4px;">Tra cứu nhanh trong phân hệ</div>`);
      htmlItems.push(`
        <div onclick="jumpFromTopbarSearch('tab-admin-tenants', '${escapeHtml(kw)}')" style="padding:7px 10px;border-radius:8px;cursor:pointer;display:flex;align-items:center;gap:9px;font-size:12.5px;color:#334155;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='transparent'">
          <i class="fa-solid fa-building-user" style="color:#0284c7;width:16px;text-align:center;"></i>
          <span>Tìm khách thuê: <strong>"${escapeHtml(kw)}"</strong></span>
        </div>
        <div onclick="jumpFromTopbarSearch('tab-admin-contracts', '${escapeHtml(kw)}')" style="padding:7px 10px;border-radius:8px;cursor:pointer;display:flex;align-items:center;gap:9px;font-size:12.5px;color:#334155;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='transparent'">
          <i class="fa-solid fa-file-contract" style="color:#2563eb;width:16px;text-align:center;"></i>
          <span>Tìm hợp đồng: <strong>"${escapeHtml(kw)}"</strong></span>
        </div>
        <div onclick="jumpFromTopbarSearch('tab-admin-billing', '${escapeHtml(kw)}')" style="padding:7px 10px;border-radius:8px;cursor:pointer;display:flex;align-items:center;gap:9px;font-size:12.5px;color:#334155;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='transparent'">
          <i class="fa-solid fa-file-invoice-dollar" style="color:#10b981;width:16px;text-align:center;"></i>
          <span>Tìm hóa đơn công nợ: <strong>"${escapeHtml(kw)}"</strong></span>
        </div>
        <div onclick="jumpFromTopbarSearch('tab-admin-tickets', '${escapeHtml(kw)}')" style="padding:7px 10px;border-radius:8px;cursor:pointer;display:flex;align-items:center;gap:9px;font-size:12.5px;color:#334155;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='transparent'">
          <i class="fa-solid fa-screwdriver-wrench" style="color:#ef4444;width:16px;text-align:center;"></i>
          <span>Tìm phiếu bảo trì: <strong>"${escapeHtml(kw)}"</strong></span>
        </div>
        <div onclick="jumpFromTopbarSearch('tab-sales-leads', '${escapeHtml(kw)}')" style="padding:7px 10px;border-radius:8px;cursor:pointer;display:flex;align-items:center;gap:9px;font-size:12.5px;color:#334155;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='transparent'">
          <i class="fa-solid fa-filter-circle-dollar" style="color:#d97706;width:16px;text-align:center;"></i>
          <span>Tìm Lead CRM: <strong>"${escapeHtml(kw)}"</strong></span>
        </div>
      `);

      resultsBox.innerHTML = htmlItems.join('');
      resultsBox.style.display = 'block';
    }

    function jumpFromTopbarSearch(tabId, keyword) {
      const resultsBox = document.getElementById('bms-topbar-search-results');
      if (resultsBox) resultsBox.style.display = 'none';

      const linkEl = document.querySelector(`.filament-link[onclick*="${tabId}"]`);
      switchFilamentTab(tabId, linkEl, true);

      setTimeout(() => {
        if (tabId === 'tab-admin-buildings' && typeof handleAdminBuildingSearch === 'function') {
          const inp = document.getElementById('admin-building-search');
          if (inp) inp.value = keyword;
          handleAdminBuildingSearch(keyword);
        } else if (tabId === 'tab-admin-tenants' && typeof handleTenantSearch === 'function') {
          const inp = document.getElementById('tenant-search-input');
          if (inp) inp.value = keyword;
          handleTenantSearch(keyword);
        } else if (tabId === 'tab-admin-contracts' && typeof handleContractSearch === 'function') {
          const inp = document.getElementById('contract-search-input');
          if (inp) inp.value = keyword;
          handleContractSearch(keyword);
        } else if (tabId === 'tab-admin-billing') {
          const inp = document.getElementById('billing-search-input');
          if (inp) inp.value = keyword;
          if (typeof filterAdminBillingRows === 'function') filterAdminBillingRows();
        } else if (tabId === 'tab-admin-tickets' && typeof loadTicketsList === 'function') {
          const inp = document.getElementById('ticket-filter-keyword');
          if (inp) inp.value = keyword;
          loadTicketsList('admin');
        } else if (tabId === 'tab-sales-leads' && typeof handleCrmSearch === 'function') {
          const inp = document.getElementById('crm-search-input');
          if (inp) inp.value = keyword;
          handleCrmSearch(keyword);
        }
      }, 150);
    }

    // Tự động đóng dropdown tìm kiếm khi click ra ngoài
    document.addEventListener('click', function(e) {
      const wrap = document.getElementById('bms-topbar-search-wrapper');
      const box = document.getElementById('bms-topbar-search-results');
      if (wrap && box && !wrap.contains(e.target)) {
        box.style.display = 'none';
      }
    });

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

    // Điều khiển Dropdown Thông báo Vận hành trên Topbar
    function toggleBmsNotificationDropdown(e) {
      if (e) e.stopPropagation();
      const dropdown = document.getElementById('bms-notif-dropdown');
      if (!dropdown) return;
      const isVisible = dropdown.style.display === 'block';
      dropdown.style.display = isVisible ? 'none' : 'block';
    }

    function handleBmsNotificationClick(targetTab, navId) {
      const dropdown = document.getElementById('bms-notif-dropdown');
      if (dropdown) dropdown.style.display = 'none';
      const navEl = navId ? document.getElementById(navId) : null;
      switchFilamentTab(targetTab, navEl);
    }

    function markAllBmsNotificationsRead() {
      const dot = document.getElementById('bms-notif-dot');
      if (dot) dot.style.display = 'none';
      const dropdown = document.getElementById('bms-notif-dropdown');
      if (dropdown) dropdown.style.display = 'none';
    }

    document.addEventListener('click', function (e) {
      const wrapper = document.getElementById('bms-notif-wrapper');
      const dropdown = document.getElementById('bms-notif-dropdown');
      if (dropdown && wrapper && !wrapper.contains(e.target)) {
        dropdown.style.display = 'none';
      }
    });
    </script>
