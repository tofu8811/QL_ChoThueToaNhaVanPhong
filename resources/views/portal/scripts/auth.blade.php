<script>
    /* ==========================================================================
       2. AUTHENTICATION & LOGIN/LOGOUT LOGIC (API 67 & 68)
       ========================================================================== */
    function openLoginModal() {
      document.getElementById("login-modal").classList.add("show");
      const errBox = document.getElementById("login-error-alert");
      if (errBox) errBox.style.display = "none";
      // Mặc định chọn vai trò Admin để dùng thử nhanh nếu form trống
      if (!document.getElementById("login-email").value) {
        const firstChip = document.getElementById("chip-role-admin");
        if (firstChip) selectSampleRole('admin', firstChip);
      }
    }

    function closeLoginModal() {
      document.getElementById("login-modal").classList.remove("show");
    }

    function selectSampleRole(role, el, autoSubmit = false) {
      document.querySelectorAll('.sample-role-pill').forEach(pill => pill.classList.remove('selected'));
      if (el) el.classList.add('selected');

      const emailInput = document.getElementById("login-email");
      const passInput = document.getElementById("login-password");
      if (!emailInput || !passInput) return;

      const navStaff = document.getElementById("nav-item-staff");
      if (navStaff) navStaff.style.display = (role === 'admin') ? 'flex' : 'none';

      if (role === 'admin') {
        emailInput.value = 'admin.demo@officeleasing.vn';
        passInput.value = 'Password@123';
      } else if (role === 'pm') {
        emailInput.value = 'pm.demo@officeleasing.vn';
        passInput.value = 'Password@123';
      } else if (role === 'sales') {
        emailInput.value = 'sales.demo@officeleasing.vn';
        passInput.value = 'Password@123';
      } else if (role === 'accountant') {
        emailInput.value = 'acc.demo@officeleasing.vn';
        passInput.value = 'Password@123';
      } else if (role === 'tenant') {
        emailInput.value = 'tenant.demo@officeleasing.vn';
        passInput.value = 'Password@123';
      }

      if (autoSubmit) {
        handleLoginSubmit({ preventDefault: () => {} });
      }
    }

    function togglePasswordVisibility() {
      const pwd = document.getElementById("login-password");
      const icon = document.getElementById("pwd-eye-icon");
      if (!pwd) return;
      if (pwd.type === "password") {
        pwd.type = "text";
        if (icon) {
          icon.classList.remove("fa-eye");
          icon.classList.add("fa-eye-slash");
        }
      } else {
        pwd.type = "password";
        if (icon) {
          icon.classList.remove("fa-eye-slash");
          icon.classList.add("fa-eye");
        }
      }
    }

    function toggleUserDropdown(event) {
      if (event) event.stopPropagation();
      const menu = document.getElementById("user-dropdown-menu");
      const trigger = document.getElementById("user-profile-trigger");
      if (!menu) return;
      menu.classList.toggle("show");
      if (trigger) trigger.classList.toggle("active");
    }

    function closeUserDropdown() {
      const menu = document.getElementById("user-dropdown-menu");
      const trigger = document.getElementById("user-profile-trigger");
      if (menu) menu.classList.remove("show");
      if (trigger) trigger.classList.remove("active");
    }

    function toggleAdminUserDropdown(event) {
      if (event) event.stopPropagation();
      const menu = document.getElementById("admin-user-dropdown-menu");
      const trigger = document.getElementById("admin-user-profile-trigger");
      const caret = document.getElementById("admin-user-caret");
      const notifMenu = document.getElementById("bms-notif-dropdown");
      if (notifMenu) notifMenu.style.display = "none";
      if (!menu) return;
      const isShowing = menu.classList.toggle("show");
      if (trigger) trigger.classList.toggle("active", isShowing);
      if (caret) caret.style.transform = isShowing ? "rotate(180deg)" : "rotate(0deg)";
    }

    function closeAdminUserDropdown() {
      const menu = document.getElementById("admin-user-dropdown-menu");
      const trigger = document.getElementById("admin-user-profile-trigger");
      const caret = document.getElementById("admin-user-caret");
      if (menu) menu.classList.remove("show");
      if (trigger) trigger.classList.remove("active");
      if (caret) caret.style.transform = "rotate(0deg)";
    }

    // Đóng dropdown & modal khi click bên ngoài backdrop
    window.addEventListener("click", (e) => {
      if (!e.target.closest("#user-logged-box")) {
        closeUserDropdown();
      }
      if (!e.target.closest("#admin-user-box")) {
        closeAdminUserDropdown();
      }
      if (e.target.id === 'login-modal') closeLoginModal();
      if (e.target.id === 'profile-modal') closeProfileModal();
      if (e.target.id === 'building-modal') closeBuildingModal();
      if (e.target.id === 'ticket-modal' || e.target.id === 'tenant-ticket-modal') closeTenantTicketModal();
      if (e.target.id === 'ticket-detail-modal') closeTicketDetailModal();
      if (e.target.id === 'ticket-assign-modal') closeTicketAssignModal();
      if (e.target.id === 'ticket-status-modal') closeTicketStatusModal();
      if (e.target.id === 'ticket-upload-proof-modal') closeUploadProofModal();
      if (e.target.id === 'ticket-confirm-modal') closeTicketConfirmModal();
      if (e.target.id === 'ticket-rate-modal') closeTicketRateModal();
      if (e.target.id === 'ticket-edit-modal') closeTicketEditModal();
      if (e.target.id === 'modal-assign-sales') closeModal('modal-assign-sales');
      if (e.target.id === 'modal-assign-pm') closeModal('modal-assign-pm');
      if (e.target.id === 'modal-assign-accountant') closeModal('modal-assign-accountant');
      if (e.target.id && e.target.id.startsWith('staff-') && e.target.classList.contains('mo-modal')) closeModal(e.target.id);
    });

    async function handleLoginSubmit(e) {
      e.preventDefault();
      const email = document.getElementById("login-email").value.trim();
      const password = document.getElementById("login-password").value;
      const btn = document.getElementById("btn-submit-login");
      const errBox = document.getElementById("login-error-alert");
      const errText = document.getElementById("login-error-text");

      btn.disabled = true;
      btn.innerHTML = `<i class="fa-solid fa-spinner fa-spin"></i> Đang xác thực...`;
      if (errBox) errBox.style.display = "none";

      try {
        const response = await fetch('/api/v1/auth/login', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
          },
          body: JSON.stringify({ email, password })
        });

        const result = await response.json();

        if (response.ok && result.status === 'success') {
          const authData = result.data;
          // Lưu token và user info
          localStorage.setItem('auth_token', authData.access_token);
          localStorage.setItem('auth_role', authData.role);
          localStorage.setItem('auth_user', JSON.stringify(authData.user));

          // Thiết lập cookie ngay lập tức để đồng bộ với SSR
          document.cookie = 'auth_token=' + authData.access_token + '; path=/; max-age=86400; SameSite=Lax';
          if (authData.user && authData.user.id) {
            localStorage.setItem('tenant_id', authData.user.id);
            document.cookie = 'tenant_id=' + authData.user.id + '; path=/; max-age=86400; SameSite=Lax';
          }

          closeLoginModal();
          updateAuthUI(authData.user, authData.role);

          // Tự động chuyển trang phù hợp theo đúng Role
          const upperRole = String(authData.role || '').toUpperCase();
          if (upperRole === 'TENANT') {
            window.location.href = '/tenant/portal' + (authData.user?.id ? '?tenant_id=' + authData.user.id : '');
          } else {
            if (window.location.pathname.startsWith('/admin')) {
              const rMap = {
                'ADMIN': 'admin', 'SUPER_ADMIN': 'admin',
                'PM': 'pm', 'PROPERTY_MANAGER': 'pm',
                'SALES': 'sales', 'ACCOUNTANT': 'accountant'
              };
              handleRoleSwitch(rMap[upperRole] || 'admin');
            } else {
              window.location.href = '/admin';
            }
          }
        } else {
          const msg = result.message || 'Email hoặc mật khẩu không chính xác. Vui lòng kiểm tra lại.';
          if (errText) errText.innerText = msg;
          else if (errBox) errBox.innerText = msg;
          if (errBox) errBox.style.display = "flex";
        }
      } catch (error) {
        console.error('Login Error:', error);
        const msg = 'Lỗi kết nối máy chủ xác thực. Vui lòng thử lại sau.';
        if (errText) errText.innerText = msg;
        else if (errBox) errBox.innerText = msg;
        if (errBox) errBox.style.display = "flex";
      } finally {
        btn.disabled = false;
        btn.innerHTML = `<i class="fa-solid fa-right-to-bracket"></i> Đăng Nhập Không Gian Làm Việc`;
      }
    }

    async function handleLogout() {
      const token = localStorage.getItem('auth_token');
      if (token) {
        try {
          await fetch('/api/v1/auth/logout', {
            method: 'POST',
            headers: {
              'Authorization': `Bearer ${token}`,
              'Accept': 'application/json'
            }
          });
        } catch (e) {
          console.log('Logout cleanup error:', e);
        }
      }

      localStorage.removeItem('auth_token');
      localStorage.removeItem('auth_role');
      localStorage.removeItem('auth_user');
      localStorage.removeItem('tenant_id');
      document.cookie = 'auth_token=; path=/; max-age=0; expires=Thu, 01 Jan 1970 00:00:00 GMT';
      document.cookie = 'tenant_id=; path=/; max-age=0; expires=Thu, 01 Jan 1970 00:00:00 GMT';

      updateAuthUI(null, null);
      if (typeof switchView === 'function') {
        switchView('portal-view');
      }
      window.location.href = '/';
    }

    function updateAuthUI(user, role) {
      const btnLogin = document.getElementById("btn-open-login");
      const loggedBox = document.getElementById("user-logged-box");
      const nameEl = document.getElementById("user-display-name");
      const roleEl = document.getElementById("user-display-role");
      const avatarEl = document.getElementById("user-avatar-text");
      const dropFullName = document.getElementById("dropdown-full-name");
      const dropEmail = document.getElementById("dropdown-email");
      const dropRoleBadge = document.getElementById("dropdown-role-badge");
      const dropFilamentLink = document.getElementById("dropdown-filament-link");

      if (user && role) {
        window.currentUiRole = String(role).toLowerCase();
        const rawName = user.company_name || user.full_name || user.name || 'Người dùng';
        let initials = 'US';
        if (user.company_name) {
          const words = user.company_name.replace(/^(CÔNG TY|TNHH|CP|CỔ PHẦN)\s+/gi, '').trim().split(' ');
          initials = words.map(w => w[0]).filter(Boolean).slice(0, 2).join('').toUpperCase() || 'DN';
        } else {
          const cleanName = rawName.replace(/\(.*?\)/g, '').trim();
          initials = cleanName.split(' ').map(n => n[0]).filter(Boolean).slice(-2).join('').toUpperCase() || 'US';
        }

        const roleLabels = {
          'ADMIN': 'Super Admin',
          'SUPER_ADMIN': 'Super Admin',
          'PM': 'Ban Quản Lý',
          'PROPERTY_MANAGER': 'Ban Quản Lý',
          'SALES': 'Chuyên Viên Sales',
          'TENANT': 'Khách Thuê',
          'ACCOUNTANT': 'Kế Toán'
        };
        const prettyRole = roleLabels[String(role).toUpperCase()] || role;

        if (btnLogin) btnLogin.style.display = "none";
        if (loggedBox) loggedBox.style.display = "inline-flex";
        if (nameEl) nameEl.innerText = rawName;
        if (roleEl) roleEl.innerText = prettyRole;
        if (avatarEl) avatarEl.innerText = initials;

        if (dropFullName) dropFullName.innerText = rawName;
        if (dropEmail) dropEmail.innerText = user.login_email || user.email || user.billing_email || '';
        if (dropRoleBadge) dropRoleBadge.innerText = prettyRole;

        if (dropFilamentLink) {
          dropFilamentLink.style.display = (role === 'ADMIN' || role === 'SUPER_ADMIN') ? 'flex' : 'none';
        }

        const dropWorkspaceTxt = document.getElementById("dropdown-workspace-text");
        const dropSessionLink = document.getElementById("dropdown-session-link");
        const btnTabSession = document.getElementById("btn-tab-profile-session");

        const normalizedRole = String(role || '').toUpperCase();
        const isAdmin = (normalizedRole === 'ADMIN' || normalizedRole === 'SUPER_ADMIN');

        if (dropWorkspaceTxt) {
          if (isAdmin) dropWorkspaceTxt.innerText = "Portal Quản Trị Hệ Thống";
          else if (normalizedRole === 'PM' || normalizedRole === 'PROPERTY_MANAGER') dropWorkspaceTxt.innerText = "Bàn Làm Việc BQL Tòa Nhà";
          else if (normalizedRole === 'SALES') dropWorkspaceTxt.innerText = "Không Gian Làm Việc Sales";
          else if (normalizedRole === 'TENANT') dropWorkspaceTxt.innerText = "Cổng Dịch Vụ Khách Thuê";
          else dropWorkspaceTxt.innerText = "Không Gian Làm Việc Số";
        }

        // Ẩn Tab & Link Quản Trị Token Sanctum
        if (dropSessionLink) dropSessionLink.style.display = "none";
        if (btnTabSession) btnTabSession.style.display = "none";

        // Cập nhật profile trên sidebar Admin Shell
        const sideName = document.getElementById("filament-user-name");
        const sideSub = document.getElementById("filament-user-sub");
        const sideAvatar = document.getElementById("filament-user-avatar");
        if (sideName) sideName.innerText = rawName;
        if (sideSub) sideSub.innerText = `${prettyRole} • ${user.login_email || user.email || ''}`;
        if (sideAvatar) sideAvatar.innerText = initials;

        // Cập nhật dropdown Admin
        const adminDropFullName = document.getElementById("admin-dropdown-full-name");
        const adminDropEmail = document.getElementById("admin-dropdown-email");
        const adminDropRoleBadge = document.getElementById("admin-dropdown-role-badge");
        const adminDropSessionLink = document.getElementById("admin-dropdown-session-link");

        if (adminDropFullName) adminDropFullName.innerText = rawName;
        if (adminDropEmail) adminDropEmail.innerText = user.login_email || user.email || user.billing_email || '';
        if (adminDropRoleBadge) adminDropRoleBadge.innerText = prettyRole.toUpperCase();
        if (adminDropSessionLink) adminDropSessionLink.style.display = "none";

        if (typeof syncTenantPortalData === 'function') syncTenantPortalData();
      } else {
        if (btnLogin) btnLogin.style.display = "inline-flex";
        if (loggedBox) loggedBox.style.display = "none";
        const btnWorkspace = document.getElementById("btn-workspace-nav");
        if (btnWorkspace) btnWorkspace.style.display = "none";
        const dropSessionLink = document.getElementById("dropdown-session-link");
        if (dropSessionLink) dropSessionLink.style.display = "none";
        const btnTabSession = document.getElementById("btn-tab-profile-session");
        if (btnTabSession) btnTabSession.style.display = "none";
      }
    }

    function checkAuthState() {
      const token = localStorage.getItem('auth_token');
      const userStr = localStorage.getItem('auth_user');
      const role = localStorage.getItem('auth_role');

      if (token && userStr) {
        try {
          const user = JSON.parse(userStr);
          window.currentUiRole = String(role || 'admin').toLowerCase();
          updateAuthUI(user, role);
        } catch (e) {
          console.error('Lỗi nạp phiên người dùng:', e);
          localStorage.removeItem('auth_user');
          updateAuthUI(null, null);
        }
      } else {
        updateAuthUI(null, null);
      }
    }

    /* ==========================================================================
       2.1. USER PROFILE, ASSIGNED BUILDINGS & SECURITY (API 69, 70, 71)
       ========================================================================== */
    function openProfileModal(tabName = 'tab-profile-password') {
      const modal = document.getElementById("profile-modal");
      if (!modal) return;

      const role = String(localStorage.getItem('auth_role') || '').toUpperCase();
      const isAdmin = (role === 'ADMIN' || role === 'SUPER_ADMIN');

      // Fallback về tab-profile-password
      if (tabName === 'tab-profile-info' || tabName === 'tab-profile-session' || !tabName) {
        tabName = 'tab-profile-password';
      }

      modal.classList.add("show");
      
      const targetBtn = document.getElementById(`btn-${tabName}`);
      switchProfileModalTab(tabName, targetBtn);

      // Cập nhật thời gian check phiên
      const timeEl = document.getElementById("session-login-time");
      if (timeEl) timeEl.innerText = new Date().toLocaleTimeString('vi-VN') + ' - ' + new Date().toLocaleDateString('vi-VN');

      // Tải hồ sơ từ API 69
      loadUserProfile();
    }

    function closeProfileModal() {
      const modal = document.getElementById("profile-modal");
      if (modal) modal.classList.remove("show");
    }

    function switchProfileModalTab(tabId, el) {
      document.querySelectorAll('.profile-tab-btn').forEach(btn => btn.classList.remove('active'));
      document.querySelectorAll('.profile-tab-pane').forEach(pane => {
        pane.classList.remove('active');
        pane.style.display = 'none';
      });

      if (el) el.classList.add('active');
      const targetPane = document.getElementById(tabId);
      if (targetPane) {
        targetPane.classList.add('active');
        targetPane.style.display = 'block';
      }
    }

    async function loadUserProfile() {
      const token = localStorage.getItem('auth_token');
      const loadingEl = document.getElementById("profile-loading");
      const contentEl = document.getElementById("profile-content");
      
      if (!token) {
        alert("Phiên đăng nhập đã hết hạn. Vui lòng đăng nhập lại.");
        closeProfileModal();
        openLoginModal();
        return;
      }

      if (loadingEl) loadingEl.style.display = "block";
      if (contentEl) contentEl.style.display = "none";

      try {
        const response = await fetch('/api/v1/auth/me', {
          method: 'GET',
          headers: {
            'Authorization': `Bearer ${token}`,
            'Accept': 'application/json'
          }
        });

        const result = await response.json();

        if (response.ok && result.status === 'success') {
          const profileData = result.data;
          const user = profileData.user;
          const role = profileData.role;
          const assignedBuildings = profileData.assigned_buildings || [];

          // Lưu cập nhật vào localStorage
          localStorage.setItem('auth_user', JSON.stringify(user));
          localStorage.setItem('auth_role', role);
          updateAuthUI(user, role);

          // Cập nhật thông tin trên modal
          const rawName = user.company_name || user.full_name || user.name || 'Người dùng';
          let initials = 'US';
          if (user.company_name) {
            const words = user.company_name.replace(/^(CÔNG TY|TNHH|CP|CỔ PHẦN)\s+/gi, '').trim().split(' ');
            initials = words.map(w => w[0]).filter(Boolean).slice(0, 2).join('').toUpperCase() || 'DN';
          } else {
            initials = rawName.split(' ').map(n => n[0]).filter(Boolean).slice(-2).join('').toUpperCase() || 'US';
          }

          const roleLabels = {
            'ADMIN': 'Super Admin',
            'SUPER_ADMIN': 'Super Admin',
            'PM': 'Ban Quản Lý',
            'PROPERTY_MANAGER': 'Ban Quản Lý',
            'SALES': 'Chuyên Viên Sales',
            'TENANT': 'Khách Thuê',
            'ACCOUNTANT': 'Kế Toán'
          };
          const prettyRole = roleLabels[String(role).toUpperCase()] || role;

          const avatarDisplay = document.getElementById("profile-avatar-display");
          const nameDisplay = document.getElementById("profile-name-display");
          const roleBadge = document.getElementById("profile-role-badge");
          const emailDisplay = document.getElementById("profile-email-display");
          const phoneDisplay = document.getElementById("profile-phone-display");
          const codeDisplay = document.getElementById("profile-code-display");
          const countDisplay = document.getElementById("profile-buildings-count");
          const listDisplay = document.getElementById("profile-buildings-list");

          if (avatarDisplay) avatarDisplay.innerText = initials;
          if (nameDisplay) nameDisplay.innerText = rawName;
          if (roleBadge) roleBadge.innerText = prettyRole;
          if (emailDisplay) emailDisplay.innerText = user.login_email || user.email || user.billing_email || '-';
          if (phoneDisplay) phoneDisplay.innerText = user.phone_number || user.phone || '-';
          if (codeDisplay) codeDisplay.innerText = user.tenant_code || user.staff_code || user.employee_code || `ID: #${user.id}`;

          // Render danh sách tòa nhà phụ trách
          if (countDisplay) countDisplay.innerText = `${assignedBuildings.length} tòa nhà`;
          if (listDisplay) {
            if (assignedBuildings.length > 0) {
              listDisplay.innerHTML = assignedBuildings.map(b => `
                <div class="profile-building-card">
                  <div class="profile-building-icon">
                    <i class="fa-solid fa-building"></i>
                  </div>
                  <div class="profile-building-info">
                    <div class="profile-building-name" title="${b.name}">${b.name}</div>
                    <div class="profile-building-meta">
                      <span class="badge-filament primary" style="font-size:10px;">${b.grade || 'Hạng B'}</span>
                      <span>Mã: ${b.building_code || '#' + b.id}</span>
                    </div>
                  </div>
                </div>
              `).join('');
            } else {
              listDisplay.innerHTML = `
                <div style="grid-column: 1 / -1; background:#f8fafc; border:1px dashed #cbd5e1; border-radius:8px; padding:16px; text-align:center; color:#64748b; font-size:12.5px;">
                  <i class="fa-solid fa-circle-info" style="color:#2563eb; margin-right:4px;"></i>
                  ${role === 'TENANT' ? 'Tài khoản khách thuê được liên kết thông qua hợp đồng thuê văn phòng.' : 'Chưa được phân công tòa nhà phụ trách trực tiếp.'}
                </div>
              `;
            }
          }

          if (loadingEl) loadingEl.style.display = "none";
          if (contentEl) contentEl.style.display = "block";
        } else if (response.status === 401) {
          alert("Phiên đăng nhập đã hết hạn. Vui lòng đăng nhập lại.");
          handleLogout();
        } else {
          if (loadingEl) loadingEl.innerHTML = `<span style="color:#dc2626;"><i class="fa-solid fa-circle-exclamation"></i> Không thể tải hồ sơ: ${result.message || 'Lỗi không xác định'}</span>`;
        }
      } catch (err) {
        console.error('Error loading profile:', err);
        if (loadingEl) loadingEl.innerHTML = `<span style="color:#dc2626;"><i class="fa-solid fa-circle-exclamation"></i> Lỗi kết nối đến máy chủ.</span>`;
      }
    }

    async function handleRefreshTokenClick(btn) {
      const token = localStorage.getItem('auth_token');
      const alertBox = document.getElementById("refresh-token-alert");
      if (!token) return;

      if (btn) {
        btn.disabled = true;
        btn.innerHTML = `<i class="fa-solid fa-spinner fa-spin"></i> Đang gia hạn...`;
      }
      if (alertBox) alertBox.style.display = "none";

      try {
        const response = await fetch('/api/v1/auth/refresh', {
          method: 'POST',
          headers: {
            'Authorization': `Bearer ${token}`,
            'Accept': 'application/json'
          }
        });

        const result = await response.json();

        if (response.ok && result.status === 'success') {
          const newToken = result.data.access_token;
          localStorage.setItem('auth_token', newToken);

          if (alertBox) {
            alertBox.style.display = "block";
            alertBox.style.background = "#f0fdf4";
            alertBox.style.border = "1px solid #bbf7d0";
            alertBox.style.color = "#15803d";
            alertBox.innerHTML = `<i class="fa-solid fa-circle-check"></i> Làm mới phiên làm việc thành công! Token bảo mật mới đã được cấp và bảo lưu.`;
          }

          const timeEl = document.getElementById("session-login-time");
          if (timeEl) timeEl.innerText = new Date().toLocaleTimeString('vi-VN') + ' - ' + new Date().toLocaleDateString('vi-VN');
        } else {
          if (alertBox) {
            alertBox.style.display = "block";
            alertBox.style.background = "#fef2f2";
            alertBox.style.border = "1px solid #fecaca";
            alertBox.style.color = "#b91c1c";
            alertBox.innerHTML = `<i class="fa-solid fa-circle-exclamation"></i> Gia hạn thất bại: ${result.message || 'Phiên không hợp lệ'}`;
          }
        }
      } catch (err) {
        console.error('Refresh token error:', err);
        if (alertBox) {
          alertBox.style.display = "block";
          alertBox.style.background = "#fef2f2";
          alertBox.style.border = "1px solid #fecaca";
          alertBox.style.color = "#b91c1c";
          alertBox.innerHTML = `<i class="fa-solid fa-circle-exclamation"></i> Lỗi kết nối khi làm mới token.`;
        }
      } finally {
        if (btn) {
          btn.disabled = false;
          btn.innerHTML = `<i class="fa-solid fa-arrows-rotate"></i> Làm Mới Phiên (Refresh Token)`;
        }
      }
    }

    async function handleChangePasswordSubmit(e) {
      e.preventDefault();
      const current_password = document.getElementById("pwd-current").value;
      const password = document.getElementById("pwd-new").value;
      const password_confirmation = document.getElementById("pwd-confirm").value;
      const btn = document.getElementById("btn-submit-change-pwd");
      const alertBox = document.getElementById("change-pwd-alert");
      const token = localStorage.getItem('auth_token');

      if (!token) {
        alert("Vui lòng đăng nhập để đổi mật khẩu.");
        return;
      }

      if (password !== password_confirmation) {
        if (alertBox) {
          alertBox.style.display = "block";
          alertBox.style.background = "#fef2f2";
          alertBox.style.border = "1px solid #fecaca";
          alertBox.style.color = "#b91c1c";
          alertBox.innerHTML = `<i class="fa-solid fa-circle-exclamation"></i> Mật khẩu xác nhận không khớp với mật khẩu mới.`;
        }
        return;
      }

      btn.disabled = true;
      btn.innerHTML = `<i class="fa-solid fa-spinner fa-spin"></i> Đang xử lý...`;
      if (alertBox) alertBox.style.display = "none";

      try {
        const response = await fetch('/api/v1/auth/password', {
          method: 'PUT',
          headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
          },
          body: JSON.stringify({
            current_password,
            password,
            password_confirmation
          })
        });

        const result = await response.json();

        if (response.ok && result.status === 'success') {
          if (alertBox) {
            alertBox.style.display = "block";
            alertBox.style.background = "#f0fdf4";
            alertBox.style.border = "1px solid #bbf7d0";
            alertBox.style.color = "#15803d";
            alertBox.innerHTML = `<i class="fa-solid fa-circle-check"></i> Đổi mật khẩu cá nhân thành công!`;
          }
          document.getElementById("change-password-form").reset();
        } else {
          let errDetail = result.message || 'Không thể đổi mật khẩu.';
          if (result.errors) {
            const errArr = Object.values(result.errors).flat();
            if (errArr.length > 0) errDetail = errArr.join('<br>');
          }

          if (alertBox) {
            alertBox.style.display = "block";
            alertBox.style.background = "#fef2f2";
            alertBox.style.border = "1px solid #fecaca";
            alertBox.style.color = "#b91c1c";
            alertBox.innerHTML = `<i class="fa-solid fa-circle-exclamation"></i> ${errDetail}`;
          }
        }
      } catch (err) {
        console.error('Change password error:', err);
        if (alertBox) {
          alertBox.style.display = "block";
          alertBox.style.background = "#fef2f2";
          alertBox.style.border = "1px solid #fecaca";
          alertBox.style.color = "#b91c1c";
          alertBox.innerHTML = `<i class="fa-solid fa-circle-exclamation"></i> Lỗi kết nối khi gửi yêu cầu đổi mật khẩu.`;
        }
      } finally {
        btn.disabled = false;
        btn.innerHTML = `<i class="fa-solid fa-floppy-disk"></i> Cập Nhật Mật Khẩu Mới`;
      }
    }

    </script>
