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
      'tab-sales-performance': 'admin/sales-performance',
      'tab-sales-leads': 'admin/sales-leads',
      'tab-sales-appointments': 'admin/sales-appointments',
      'tab-sales-quotes': 'admin/sales-quotes',
      'tab-sales-pipeline': 'admin/sales-pipeline',
      'tab-tenant-profile': 'tenant/profile',
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
      'pm/dashboard': 'tab-admin-dashboard',
      'pm': 'tab-admin-dashboard',
      'pm/buildings': 'tab-admin-buildings',
      'pm/contracts': 'tab-admin-contracts',
      'pm/tickets': 'tab-admin-tickets',
      'pm/billing': 'tab-admin-billing',
      'admin/sales-performance': 'tab-sales-performance',
      'admin/sales-leads': 'tab-sales-leads',
      'admin/sales-appointments': 'tab-sales-appointments',
      'admin/sales-quotes': 'tab-sales-quotes',
      'admin/sales-pipeline': 'tab-sales-pipeline',
      'tenant/profile': 'tab-tenant-profile',
      'tenant/workspace': 'tab-tenant-workspace',
      'tenant': 'tab-tenant-profile',
      'tenant/tickets': 'tab-tenant-tickets',
      'tenant/bills': 'tab-tenant-bills',
    };

    const TAB_BREADCRUMB_MAP = {
      'tab-admin-dashboard': 'Tổng quan vận hành tòa nhà',
      'tab-admin-buildings': 'Quản lý tài nguyên mặt bằng tòa nhà',
      'tab-admin-tenants': 'Quản lý khách hàng thuê',
      'tab-admin-contracts': 'Hợp đồng thuê & Biên bản bàn giao',
      'tab-admin-billing': 'Kế toán, nhập chỉ số & công nợ tòa nhà',
      'tab-admin-tickets': 'Tiếp nhận & Xử lý Ticket sự cố, thi công',
      'tab-admin-staff': 'Quản trị nhân sự & phân quyền RBAC',
      'tab-sales-performance': 'Báo cáo hiệu suất kinh doanh cá nhân',
      'tab-sales-leads': 'Quản lý Leads Khách hàng & Pipeline CRM',
      'tab-sales-appointments': 'Quản lý lịch hẹn khảo sát thực tế',
      'tab-sales-quotes': 'Lập báo giá & Thư ngỏ (LOI)',
      'tab-sales-pipeline': 'Bảng Kanban Pipeline CRM',
      'tab-tenant-profile': 'Hồ sơ doanh nghiệp, pháp lý & nhân viên',
      'tab-tenant-workspace': 'Hợp đồng thuê, lịch tăng giá & bàn giao',
      'tab-tenant-tickets': 'Yêu cầu hỗ trợ kỹ thuật & đăng ký thi công',
      'tab-tenant-bills': 'Cước phí hàng tháng & tải lên ủy nhiệm chi',
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
        // Chỉ khi mới tải trang (F5) mà chưa có hash thì mới khôi phục route nội bộ gần nhất
        const savedToken = localStorage.getItem('auth_token');
        const savedLastRoute = localStorage.getItem('last_active_route') || '';

        // Nếu trước đó đang ở một tab nội bộ (khác portal/home) và người dùng F5 thì giữ nguyên tab làm việc
        if (savedToken && savedLastRoute && savedLastRoute !== 'portal' && savedLastRoute !== 'home') {
          hash = savedLastRoute;
          history.replaceState({ route: hash }, '', '#' + hash);
        } else {
          // Mặc định khi truy cập URL gốc '/' hoặc vừa bấm Logo về trang chủ: luôn hiển thị Cổng thông tin công khai
          hash = 'portal';
          localStorage.setItem('last_active_route', 'portal');
        }
      }

      // 0. Chốt chặn bảo mật phiên đăng nhập (Auth Route Guard)
      // Nếu cố tình truy cập các phân hệ nội bộ (admin, pm, tenant) mà chưa đăng nhập
      const currentToken = localStorage.getItem('auth_token');
      if ((hash.startsWith('admin') || hash.startsWith('pm') || hash.startsWith('tenant')) && !currentToken) {
        console.warn('[Auth Guard] Truy cập phân hệ nội bộ khi chưa đăng nhập. Chuyển hướng về Cổng thông tin.');
        updateAppRoute('portal', true);
        switchView('portal-view', false);
        if (typeof showPublicPage === 'function') showPublicPage('page-home', false);
        if (typeof openLoginModal === 'function') {
          setTimeout(() => openLoginModal(), 200);
        }
        return;
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
        const savedRole = String(localStorage.getItem('auth_role') || '').toUpperCase();
        if (savedRole && savedRole !== 'TENANT' && savedRole !== 'ADMIN' && savedRole !== 'SUPER_ADMIN') {
          alert('Tài khoản nhân sự không truy cập Cổng Khách thuê. Hệ thống chuyển bạn về không gian làm việc tương ứng.');
          handleGoToWorkspace();
          return;
        }

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

      // 3. Phân hệ Ban Quản Lý (PM / Property Manager)
      if (hash.startsWith('pm')) {
        const savedRole = String(localStorage.getItem('auth_role') || '').toUpperCase();
        if (savedRole === 'TENANT') {
          alert('Tài khoản Khách thuê không có quyền truy cập trang quản lý.');
          handleRoleSwitch('tenant', false);
          switchFilamentTab('tab-tenant-workspace', document.querySelector('#nav-group-tenant .filament-link'), true);
          return;
        }
        if (savedRole === 'SALES' || savedRole === 'ACCOUNTANT') {
          alert('Bạn không có quyền truy cập phân hệ Ban Quản Lý.');
          handleGoToWorkspace();
          return;
        }

        handleRoleSwitch('pm', false);
        const tabId = ROUTE_TO_TAB[hash] || 'tab-admin-dashboard';
        const linkEl = document.querySelector(`.filament-link[onclick*="${tabId}"]`);
        switchFilamentTab(tabId, linkEl, false);
        return;
      }

      // 4. Phân hệ Admin / Sales / Kế toán
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

    /**
     * Điều hướng khi nhấn Logo ở trang Public Portal:
     * - Trở về Cổng thông tin công khai (portal-view)
     * - Hiển thị Trang chủ tìm kiếm tòa nhà (page-home)
     * - Cập nhật Route URL về '#portal'
     * - Cuộn mượt lên đầu trang
     */
    function handlePublicLogoClick(event) {
      localStorage.setItem('last_active_route', 'portal');

      // Nếu đang ở các trang con SSR độc lập (/nhan-bao-gia, /toa-nha/..., /quan-ly-...)
      // thì chuyển hướng trình duyệt về '/#portal'
      if (window.location.pathname !== '/' && window.location.pathname !== '') {
        window.location.href = '/#portal';
        if (event && typeof event.preventDefault === 'function') {
          event.preventDefault();
        }
        return false;
      }

      if (event && typeof event.preventDefault === 'function') {
        event.preventDefault();
      }
      if (typeof switchView === 'function') {
        switchView('portal-view', false);
      }
      if (typeof showPublicPage === 'function') {
        showPublicPage('page-home', false);
      }
      if (typeof updateAppRoute === 'function') {
        updateAppRoute('portal', false);
      }
      window.scrollTo({ top: 0, behavior: 'smooth' });
      return false;
    }

    /**
     * Điều hướng khi nhấn Logo ở trang Dashboard (BMS Panel):
     * - Trở về Tab Tổng quan / Dashboard chính của vai trò đang làm việc:
     *   + Admin: tab-admin-dashboard (#admin/dashboard)
     *   + PM: tab-admin-dashboard (#pm/dashboard)
     *   + Sales: tab-sales-performance (#admin/sales-performance)
     *   + Kế toán: tab-admin-billing (#admin/billing)
     *   + Tenant: tab-tenant-workspace (#tenant/workspace)
     */
    function handleDashboardLogoClick(event) {
      if (event && typeof event.preventDefault === 'function') {
        event.preventDefault();
      }
      if (typeof switchView === 'function') {
        switchView('admin-view', false);
      }

      const role = String(localStorage.getItem('auth_role') || window.currentUiRole || 'admin').toLowerCase();

      let targetTab = 'tab-admin-dashboard';
      let targetLink = document.getElementById('nav-item-dashboard');

      if (role === 'tenant') {
        targetTab = 'tab-tenant-workspace';
        targetLink = document.querySelector('#nav-group-tenant .filament-link[onclick*="tab-tenant-workspace"]')
                  || document.querySelector('#nav-group-tenant .filament-link');
      } else if (role === 'sales' || role === 'sale') {
        targetTab = 'tab-sales-performance';
        targetLink = document.getElementById('nav-sales-performance')
                  || document.querySelector('.filament-link[onclick*="tab-sales-performance"]');
      } else if (role === 'accountant') {
        targetTab = 'tab-admin-billing';
        targetLink = document.getElementById('nav-item-billing')
                  || document.querySelector('.filament-link[onclick*="tab-admin-billing"]');
      } else if (role === 'pm' || role === 'property_manager') {
        targetTab = 'tab-admin-dashboard';
        targetLink = document.getElementById('nav-item-dashboard');
      } else {
        targetTab = 'tab-admin-dashboard';
        targetLink = document.getElementById('nav-item-dashboard');
      }

      if (typeof switchFilamentTab === 'function') {
        switchFilamentTab(targetTab, targetLink, true);
      }
      window.scrollTo({ top: 0, behavior: 'smooth' });
      return false;
    }

    /**
     * Xử lý điều hướng khi nhấn nút "Phễu CRM" trên thanh điều hướng:
     * - Nếu chưa đăng nhập: Mở modal đăng nhập để truy cập.
     * - Nếu là Khách thuê: Thông báo chỉ dành cho Sales/Admin.
     * - Nếu là Sales hoặc Admin: Chuyển ngay vào Tab Phễu CRM Kanban (#admin/sales-pipeline) trong BMS.
     */
    function handleCrmNavClick(event) {
      if (event && typeof event.preventDefault === 'function') {
        event.preventDefault();
      }
      const token = localStorage.getItem('auth_token');
      const role = String(localStorage.getItem('auth_role') || '').toUpperCase();

      if (!token) {
        if (typeof openLoginModal === 'function') {
          openLoginModal();
        }
        return false;
      }

      if (role === 'TENANT') {
        alert('Phễu CRM chỉ dành cho chuyên viên Kinh doanh (Sales) hoặc Quản trị viên (Admin).');
        return false;
      }

      const targetRole = (role === 'ADMIN' || role === 'SUPER_ADMIN') ? 'admin' : 'sales';
      if (typeof handleRoleSwitch === 'function') {
        handleRoleSwitch(targetRole, false);
      }
      if (typeof switchFilamentTab === 'function') {
        const link = document.querySelector('.filament-link[onclick*="tab-sales-pipeline"]')
                  || document.querySelector('.filament-link[onclick*="tab-sales-leads"]')
                  || document.getElementById('nav-item-leads');
        switchFilamentTab('tab-sales-pipeline', link, true);
      }
      window.scrollTo({ top: 0, behavior: 'smooth' });
      return false;
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
        'tab-sales-performance', 'tab-sales-leads', 'tab-sales-appointments',
        'tab-sales-quotes', 'tab-sales-pipeline', 'tab-admin-staff',
        'tab-tenant-profile', 'tab-tenant-workspace', 'tab-tenant-tickets', 'tab-tenant-bills'
      ],
      pm: [
        'tab-admin-dashboard', 'tab-admin-buildings',
        'tab-admin-contracts', 'tab-admin-tickets', 'tab-admin-billing'
      ],
      sales: [
        'tab-sales-performance', 'tab-sales-leads', 'tab-sales-appointments',
        'tab-sales-quotes', 'tab-sales-pipeline', 'tab-admin-buildings'
      ],
      accountant: [
        'tab-admin-billing', 'tab-admin-buildings', 'tab-admin-tenants',
        'tab-admin-contracts'
      ],
      tenant: [
        'tab-tenant-profile', 'tab-tenant-workspace', 'tab-tenant-tickets', 'tab-tenant-bills'
      ]
    };

    const DEFAULT_TAB_BY_ROLE = {
      admin: 'tab-admin-dashboard',
      pm: 'tab-admin-dashboard',
      sales: 'tab-sales-performance',
      accountant: 'tab-admin-billing',
      tenant: 'tab-tenant-profile'
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
      const navStaffBld = document.getElementById('nav-item-bld-staff');
      if (navStaffBld) navStaffBld.style.display = (role === 'pm') ? 'flex' : 'none';
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

      const secPm = document.getElementById('dashboard-section-pm');
      const secAdmin = document.getElementById('dashboard-section-admin');
      if (secPm) secPm.style.display = isPmRole ? 'block' : 'none';
      if (secAdmin) secAdmin.style.display = isPmRole ? 'none' : 'block';

      const totalBldsEl = document.getElementById('dashboard-total-blds');
      const totalBldsSub = document.getElementById('dashboard-total-blds-sub');
      const totalUnitsSub = document.getElementById('dashboard-total-units-sub');

      // Tính toán động số lượng tòa nhà và mặt bằng thực tế
      let pmBldCount = 10;
      let pmUnitsCount = 32;
      let pmAreaSum = 199230;
      if (isPmRole) {
        if (typeof adminBuildingsList !== 'undefined' && adminBuildingsList.length > 0) {
          pmBldCount = adminBuildingsList.length;
          pmUnitsCount = adminBuildingsList.reduce((acc, b) => acc + (parseInt(b.units_count) || 0), 0) || (pmBldCount * 3);
          pmAreaSum = adminBuildingsList.reduce((acc, b) => acc + (parseFloat(b.total_leasable_area_sqm) || 0), 0) || 199230;
        } else if (realUser && Array.isArray(realUser.assigned_buildings) && realUser.assigned_buildings.length > 0) {
          pmBldCount = realUser.assigned_buildings.length;
          pmUnitsCount = pmBldCount * 3;
        } else if (realUser && Array.isArray(realUser.assigned_building_ids) && realUser.assigned_building_ids.length > 0) {
          pmBldCount = realUser.assigned_building_ids.length;
          pmUnitsCount = pmBldCount * 3;
        }
      }

      if (totalBldsEl) totalBldsEl.innerText = isPmRole ? String(pmBldCount) : '22';
      if (totalBldsSub) totalBldsSub.innerText = isPmRole ? 'tòa phụ trách' : 'tòa nhà';
      if (totalUnitsSub) totalUnitsSub.innerText = isPmRole ? `${pmUnitsCount} mặt bằng` : '65 mặt bằng';

      const card1Title = document.getElementById('dashboard-card1-title');
      const card1FootLabel = document.getElementById('dashboard-card1-foot-label');
      const card1FootVal = document.getElementById('dashboard-card1-foot-val');
      if (card1Title) card1Title.innerText = isPmRole ? 'TỔNG SỐ TÒA NHÀ PHỤ TRÁCH' : 'QUY MÔ TÀI SẢN & SÀN';
      if (card1FootLabel) card1FootLabel.innerText = isPmRole ? 'Đang hoạt động:' : 'Tổng diện tích quản lý:';
      if (card1FootVal) card1FootVal.innerText = isPmRole ? `${pmBldCount}/${pmBldCount} cao ốc` : '428.430 m²';

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
        const authedUser = realUser || (function() { try { return JSON.parse(localStorage.getItem('auth_user') || 'null'); } catch(e) { return null; } })();
        window.currentStaffUser = authedUser || ((typeof SERVER_STAFF !== 'undefined' && SERVER_STAFF.admins) ? SERVER_STAFF.admins[0] : null);
        const isAdmin = (realRole === 'ADMIN' || realRole === 'SUPER_ADMIN' || !realRole);
        const isSuper = (realRole === 'SUPER_ADMIN' || (realUser && (realUser.is_super_admin === true || realUser.is_super_admin === 1 || realUser.is_super_admin === '1')) || (!realRole && !authUserStr));
        if (roleSwitcherContainer) roleSwitcherContainer.style.display = isAdmin ? 'inline-flex' : 'none';
        if (sidebarFilamentBtn) sidebarFilamentBtn.style.display = isAdmin ? 'inline-flex' : 'none';

        sidebarRoleBadge.innerText = isSuper ? 'Super Admin' : 'Admin';
        userName.innerText = rawName || (isSuper ? 'Nguyễn Quản Trị Hệ Thống' : 'Phạm Quản Trị');
        userSub.innerText = isSuper ? 'Super Admin • Toàn hệ thống' : 'Quản trị viên • Vận hành';
        userAvatar.innerText = isSuper ? 'SA' : 'AD';
        if (groupAdmin) groupAdmin.style.display = 'block';
        if (navDashboard) navDashboard.style.display = 'flex';
        if (navBuildings) navBuildings.style.display = 'flex';
        if (navTenants) navTenants.style.display = 'flex';
        if (navContracts) navContracts.style.display = 'flex';
        if (navBilling) navBilling.style.display = 'flex';
        if (navTickets) navTickets.style.display = 'flex';
        const navStaffBldAdmin = document.getElementById('nav-item-bld-staff');
        if (navStaffBldAdmin) navStaffBldAdmin.style.display = 'none'; // Super Admin quản lý tập trung tại mục "Nhân sự & Phân quyền RBAC"
        if (groupSales) groupSales.style.display = 'none'; // Phân hệ kinh doanh chỉ dành riêng cho Sales
        if (groupTenant) groupTenant.style.display = 'none';

        // Ẩn sub-tab Admins nếu không phải Super Admin
        const btnSubtabAdmins = document.getElementById('btn-subtab-admins');
        const panelSubtabAdmins = document.getElementById('subpanel-staff-admins');
        if (btnSubtabAdmins) btnSubtabAdmins.style.display = isSuper ? 'inline-flex' : 'none';
        if (panelSubtabAdmins && !isSuper) panelSubtabAdmins.style.display = 'none';

        if (updateRoute) switchFilamentTab('tab-admin-dashboard', navDashboard, true);
      } else if (role === 'pm') {
        const authedUser = realUser || (function() { try { return JSON.parse(localStorage.getItem('auth_user') || 'null'); } catch(e) { return null; } })();
        window.currentStaffUser = authedUser || ((typeof SERVER_STAFF !== 'undefined' && SERVER_STAFF.pm) ? SERVER_STAFF.pm[0] : null);
        if (roleSwitcherContainer) roleSwitcherContainer.style.display = (realRole === 'ADMIN' || realRole === 'SUPER_ADMIN') ? 'inline-flex' : 'none';
        if (sidebarFilamentBtn) sidebarFilamentBtn.style.display = 'none';

        sidebarRoleBadge.innerText = 'Ban Quản Lý';
        userName.innerText = rawName || authedUser?.full_name || 'Ban Quản Lý';
        userSub.innerText = authedUser?.email || 'Vận hành & Kỹ thuật tòa nhà';
        userAvatar.innerText = (rawName || authedUser?.full_name || 'PM').substring(0, 2).toUpperCase();
        if (groupAdmin) groupAdmin.style.display = 'block';
        if (navDashboard) navDashboard.style.display = 'flex';
        if (navBuildings) navBuildings.style.display = 'flex';
        if (navTenants) navTenants.style.display = 'none'; // PM không có thẩm quyền quản lý danh bạ khách thuê toàn hệ thống
        if (navContracts) navContracts.style.display = 'flex';
        const navStaffBld = document.getElementById('nav-item-bld-staff');
        if (navStaffBld) navStaffBld.style.display = 'flex'; // Điều phối nhân sự trong tòa nhà
        if (navBilling) navBilling.style.display = 'flex'; // PM nhập chỉ số, đối soát UNC và quản lý công nợ
        if (navTickets) navTickets.style.display = 'flex'; // Tiếp nhận và xử lý sự cố, duyệt thi công ngoài giờ
        if (groupSales) groupSales.style.display = 'none';
        if (groupSystem) groupSystem.style.display = 'none';
        if (groupTenant) groupTenant.style.display = 'none';
        if (updateRoute) switchFilamentTab('tab-admin-dashboard', navDashboard, true);
      } else if (role === 'sales') {
        const authedUser = realUser || (function() { try { return JSON.parse(localStorage.getItem('auth_user') || 'null'); } catch(e) { return null; } })();
        window.currentStaffUser = authedUser || ((typeof SERVER_STAFF !== 'undefined' && SERVER_STAFF.sales) ? SERVER_STAFF.sales[0] : null);
        if (roleSwitcherContainer) roleSwitcherContainer.style.display = (realRole === 'ADMIN' || realRole === 'SUPER_ADMIN') ? 'inline-flex' : 'none';
        if (sidebarFilamentBtn) sidebarFilamentBtn.style.display = 'none';

        sidebarRoleBadge.innerText = 'Chuyên Viên Sales';
        userName.innerText = rawName || authedUser?.full_name || 'Chuyên Viên Sales';
        userSub.innerText = authedUser?.email || 'Chuyên viên Leasing & Báo giá';
        userAvatar.innerText = (rawName || authedUser?.full_name || 'SL').substring(0, 2).toUpperCase();
        // Sales chỉ thấy phân hệ Kinh doanh (Báo cáo cá nhân, Leads, Báo giá & LOI, Mặt bằng chào thuê)
        if (groupAdmin) groupAdmin.style.display = 'none';
        if (groupSales) groupSales.style.display = 'block';
        if (groupSystem) groupSystem.style.display = 'none'; // Ẩn Nhân sự RBAC
        if (groupTenant) groupTenant.style.display = 'none';
        const navSalesPerf = document.getElementById('nav-sales-performance') || document.getElementById('nav-item-leads');
        if (updateRoute) switchFilamentTab('tab-sales-performance', navSalesPerf, true);
      } else if (role === 'accountant') {
        const authedUser = realUser || (function() { try { return JSON.parse(localStorage.getItem('auth_user') || 'null'); } catch(e) { return null; } })();
        window.currentStaffUser = authedUser || ((typeof SERVER_STAFF !== 'undefined' && SERVER_STAFF.accountants) ? SERVER_STAFF.accountants[0] : null);
        if (roleSwitcherContainer) roleSwitcherContainer.style.display = (realRole === 'ADMIN' || realRole === 'SUPER_ADMIN') ? 'inline-flex' : 'none';
        if (sidebarFilamentBtn) sidebarFilamentBtn.style.display = 'none';

        sidebarRoleBadge.innerText = 'Kế Toán';
        userName.innerText = rawName || authedUser?.full_name || 'Kế Toán Tòa Nhà';
        userSub.innerText = authedUser?.email || 'Kế toán công nợ & Đối soát';
        userAvatar.innerText = (rawName || authedUser?.full_name || 'KT').substring(0, 2).toUpperCase();
        // Kế toán xem Công nợ & Thanh toán, Hợp đồng, Khách hàng, Tòa nhà
        if (groupAdmin) groupAdmin.style.display = 'block';
        if (navDashboard) navDashboard.style.display = 'none';
        if (navBuildings) navBuildings.style.display = 'flex';
        if (navTenants) navTenants.style.display = 'flex';
        if (navContracts) navContracts.style.display = 'flex';
        if (navBilling) navBilling.style.display = 'flex';
        if (navTickets) navTickets.style.display = 'none';
        if (groupSales) groupSales.style.display = 'none';
        if (groupSystem) groupSystem.style.display = 'none';
        if (groupTenant) groupTenant.style.display = 'none';
        if (updateRoute) switchFilamentTab('tab-admin-billing', navBilling, true);
      } else if (role === 'tenant') {
        window.currentStaffUser = realUser || ((typeof SAMPLE_TENANT !== 'undefined') ? SAMPLE_TENANT : null);
        if (roleSwitcherContainer) roleSwitcherContainer.style.display = (realRole === 'ADMIN' || realRole === 'SUPER_ADMIN') ? 'inline-flex' : 'none';
        if (sidebarFilamentBtn) sidebarFilamentBtn.style.display = 'none';

        const tenantDisplayName = rawName || realUser?.company_name || 'Doanh Nghiệp Khách Thuê';
        sidebarRoleBadge.innerText = 'Khách Thuê';
        userName.innerText = tenantDisplayName;
        userSub.innerText = realUser?.login_email || realUser?.billing_email || realUser?.email || 'Khách thuê';
        userAvatar.innerText = tenantDisplayName.substring(0, 2).toUpperCase();
        if (groupAdmin) groupAdmin.style.display = 'none';
        if (groupSales) groupSales.style.display = 'none';
        if (groupSystem) groupSystem.style.display = 'none';
        if (groupTenant) groupTenant.style.display = 'block';

        const welcomeTitle = document.getElementById("tenant-welcome-title");
        if (welcomeTitle) welcomeTitle.innerText = "Văn Phòng Của Tôi — " + tenantDisplayName;

        const contractRep = document.getElementById("tenant-contract-rep");
        if (contractRep && realUser?.representative_name) {
          contractRep.innerText = realUser.representative_name + (realUser.representative_position ? ` (${realUser.representative_position})` : ' (Đại diện pháp luật)');
        }

        const navTenantProfile = document.getElementById('nav-tenant-profile') || document.querySelector('#nav-group-tenant .filament-link');
        if (updateRoute) switchFilamentTab('tab-tenant-profile', navTenantProfile, true);

        // Nạp danh sách ticket thực tế của Tenant để đồng bộ badge và thông báo
        if (typeof loadTicketsList === 'function') {
          loadTicketsList('tenant');
        }
      } else if (role === 'portal') {
        switchView('portal-view', updateRoute);
      }

      // Tự động tải lại danh sách Tòa nhà theo đúng phạm vi phân công của vai trò
      if (typeof loadAdminBuildingsList === 'function') {
        loadAdminBuildingsList();
      }
    }

    function switchFilamentTab(tabId, el, updateRoute = true) {
      if (tabId === 'tab-sales-pipeline') {
        const leadsLink = document.getElementById('nav-item-leads');
        switchFilamentTab('tab-sales-leads', leadsLink || el, false);
        switchCrmViewMode('kanban');
        if (updateRoute) updateAppRoute('admin/sales-pipeline');
        return;
      }
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
          let route = TAB_TO_ROUTE[tabId] || 'admin/dashboard';
          if (window.currentUiRole === 'pm') {
            route = route.replace(/^admin\//, 'pm/');
          }
          updateAppRoute(route);
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
        if (typeof syncTenantPortalData === 'function') syncTenantPortalData();
      }
      if (tabId === 'tab-tenant-profile' || tabId === 'tab-tenant-bills') {
        if (typeof syncTenantPortalData === 'function') syncTenantPortalData();
      }
    }

    // Chuyển đổi giữa chế độ xem Bảng và Kanban trong phân hệ CRM Leads (Loại bỏ trùng lặp menu)
    function switchCrmViewMode(mode) {
      const tblView = document.getElementById('crm-table-view');
      const kanbanView = document.getElementById('crm-kanban-view');
      const btnTbl = document.getElementById('btn-crm-view-table');
      const btnKanban = document.getElementById('btn-crm-view-kanban');

      if (mode === 'kanban') {
        if (tblView) tblView.style.display = 'none';
        if (kanbanView) kanbanView.style.display = 'block';
        if (btnTbl) { btnTbl.style.background = 'transparent'; btnTbl.style.color = '#475569'; btnTbl.style.fontWeight = '600'; btnTbl.style.boxShadow = 'none'; }
        if (btnKanban) { btnKanban.style.background = '#fff'; btnKanban.style.color = '#0f172a'; btnKanban.style.fontWeight = '700'; btnKanban.style.boxShadow = '0 1px 2px rgba(0,0,0,0.06)'; }
        if (typeof renderCrmKanbanBoard === 'function') renderCrmKanbanBoard();
        updateAppRoute('admin/sales-pipeline');
      } else {
        if (tblView) tblView.style.display = 'block';
        if (kanbanView) kanbanView.style.display = 'none';
        if (btnTbl) { btnTbl.style.background = '#fff'; btnTbl.style.color = '#0f172a'; btnTbl.style.fontWeight = '700'; btnTbl.style.boxShadow = '0 1px 2px rgba(0,0,0,0.06)'; }
        if (btnKanban) { btnKanban.style.background = 'transparent'; btnKanban.style.color = '#475569'; btnKanban.style.fontWeight = '600'; btnKanban.style.boxShadow = 'none'; }
        updateAppRoute('admin/sales-leads');
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

    // Đồng bộ danh sách thông báo trên Topbar khi người dùng là Khách Thuê (Tenant)
    function updateTenantTopbarNotifications(role, tickets) {
      if (role !== 'tenant') return;
      const titleEl = document.getElementById('bms-notif-title');
      const badgeEl = document.getElementById('bms-notif-count-badge');
      const listContainer = document.getElementById('bms-notif-list-container');
      const dot = document.getElementById('bms-notif-dot');
      if (!listContainer) return;

      if (titleEl) titleEl.innerText = 'Thông báo khách thuê';

      const items = [];
      (tickets || []).forEach(tk => {
        const st = String(tk.status?.value || tk.status).toUpperCase();
        const code = tk.ticket_code || ('TK-' + (tk.id || '2026'));
        const title = tk.title || 'Phiếu hỗ trợ kỹ thuật';
        if (st === 'WAITING_CONFIRMATION' || st === 'RESOLVED') {
          items.push({
            icon: 'fa-solid fa-clipboard-check',
            icon_bg: '#eff6ff',
            icon_color: '#2563eb',
            badge_text: 'Nghiệm thu • Chờ xác nhận',
            badge_style: 'background:#eff6ff;color:#1d4ed8;',
            title: `Phiếu ${code}: Đã hoàn tất sửa chữa`,
            subtitle: `${title} — Vui lòng kiểm tra và xác nhận nghiệm thu`,
            target_tab: 'tab-tenant-tickets',
            nav_id: 'nav-tenant-tickets'
          });
        } else if (st === 'IN_PROGRESS') {
          items.push({
            icon: 'fa-solid fa-screwdriver-wrench',
            icon_bg: '#fffbeb',
            icon_color: '#d97706',
            badge_text: 'Bảo trì • Đang xử lý',
            badge_style: 'background:#fffbeb;color:#b45309;',
            title: `Phiếu ${code}: Ban Quản Lý đang xử lý`,
            subtitle: `${title}`,
            target_tab: 'tab-tenant-tickets',
            nav_id: 'nav-tenant-tickets'
          });
        } else if (st === 'NEW' || st === 'OPEN') {
          items.push({
            icon: 'fa-regular fa-clock',
            icon_bg: '#f1f5f9',
            icon_color: '#475569',
            badge_text: 'Yêu cầu • Đã tiếp nhận',
            badge_style: 'background:#f1f5f9;color:#475569;',
            title: `Phiếu ${code}: Đã gửi BQL tiếp nhận`,
            subtitle: `${title}`,
            target_tab: 'tab-tenant-tickets',
            nav_id: 'nav-tenant-tickets'
          });
        }
      });

      if (badgeEl) {
        badgeEl.innerText = `${items.length} việc cần chú ý`;
      }
      if (dot) {
        dot.style.display = items.length > 0 ? 'block' : 'none';
      }

      if (items.length === 0) {
        listContainer.innerHTML = `
          <div style="padding:28px 16px;text-align:center;color:#94a3b8;font-size:13px;">
            <i class="fa-regular fa-circle-check" style="font-size:22px;color:#10b981;display:block;margin-bottom:6px;"></i>
            Không có thông báo hoặc yêu cầu tồn đọng.
          </div>
        `;
      } else {
        listContainer.innerHTML = items.map(item => `
          <div onclick="handleBmsNotificationClick('${item.target_tab}', '${item.nav_id}')"
            style="padding:12px 16px;border-bottom:1px solid #f8fafc;display:flex;gap:12px;align-items:flex-start;cursor:pointer;transition:background 0.15s;"
            onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='#ffffff'">
            <div style="width:34px;height:34px;border-radius:999px;background:${item.icon_bg};color:${item.icon_color};display:flex;align-items:center;justify-content:center;font-size:14px;flex-shrink:0;margin-top:2px;">
              <i class="${item.icon}"></i>
            </div>
            <div style="flex:1;min-width:0;">
              <div style="margin-bottom:3px;">
                <span style="font-size:10.5px;font-weight:700;padding:2px 7px;border-radius:5px;${item.badge_style}">${item.badge_text}</span>
              </div>
              <div style="font-size:13px;font-weight:700;color:#0f172a;line-height:1.35;margin-bottom:2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${item.title}</div>
              <div style="font-size:11.5px;color:#64748b;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${item.subtitle}</div>
            </div>
          </div>
        `).join('');
      }
    }

    // Điều khiển Dropdown Thông báo Vận hành trên Topbar
    function toggleBmsNotificationDropdown(e) {
      if (e) e.stopPropagation();
      const currentRole = window.currentUiRole || String(localStorage.getItem('auth_role') || 'admin').toLowerCase();
      if (currentRole === 'tenant' && typeof updateTenantTopbarNotifications === 'function') {
        updateTenantTopbarNotifications('tenant', (typeof CURRENT_TICKETS_CACHE !== 'undefined' && CURRENT_TICKETS_CACHE) ? CURRENT_TICKETS_CACHE : []);
      }
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
