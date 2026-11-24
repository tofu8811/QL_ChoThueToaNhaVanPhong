  <script>
    /* ==========================================================================
       1. DATABASE & SERVER SYNC (BLADE + RESTFUL API)
       ========================================================================== */
    const SERVER_BUILDINGS = @json($serverBuildings ?? []);
    const BUILDINGS_DATA = (SERVER_BUILDINGS && SERVER_BUILDINGS.length > 0) ? SERVER_BUILDINGS : [];
    let currentSelectedBuilding = BUILDINGS_DATA[0] || null;

    const SERVER_STAFF = @json($serverStaff ?? []);

    /* ==========================================================================
       TOAST NOTIFICATION SYSTEM (B2B Enterprise UX - Góc Trên Bên Phải Màn Hình)
       ========================================================================== */
    function getOrCreateToastContainer() {
      let container = document.getElementById('mo-toast-container');
      if (!container) {
        container = document.createElement('div');
        container.id = 'mo-toast-container';
        document.body.appendChild(container);
      }
      return container;
    }

    function moToast(message, type = 'success', title = '') {
      if (!message) return;
      const container = getOrCreateToastContainer();
      const toast = document.createElement('div');

      const isError = type === 'error' || /lỗi|thất bại|không thể|từ chối|sai/i.test(message);
      const isWarning = type === 'warning' || /cảnh báo|chú ý/i.test(message);
      const isInfo = type === 'info';
      const actualType = isError ? 'error' : (isWarning ? 'warning' : (isInfo ? 'info' : 'success'));

      toast.className = `mo-toast mo-toast-${actualType}`;

      let iconHtml = '<i class="fa-solid fa-circle-check mo-toast-icon"></i>';
      let defaultTitle = 'Thành công';
      if (actualType === 'error') {
        iconHtml = '<i class="fa-solid fa-circle-xmark mo-toast-icon"></i>';
        defaultTitle = 'Có lỗi xảy ra';
      } else if (actualType === 'warning') {
        iconHtml = '<i class="fa-solid fa-triangle-exclamation mo-toast-icon"></i>';
        defaultTitle = 'Cảnh báo';
      } else if (actualType === 'info') {
        iconHtml = '<i class="fa-solid fa-circle-info mo-toast-icon"></i>';
        defaultTitle = 'Thông báo';
      }

      toast.innerHTML = `
        ${iconHtml}
        <div class="mo-toast-content">
          <div class="mo-toast-title">${escapeHtml(title || defaultTitle)}</div>
          <div>${escapeHtml(message)}</div>
        </div>
        <button type="button" class="mo-toast-close" title="Đóng">&times;</button>
      `;

      const closeBtn = toast.querySelector('.mo-toast-close');
      const dismiss = () => {
        toast.classList.add('mo-toast-hiding');
        setTimeout(() => {
          if (toast.parentNode) toast.parentNode.removeChild(toast);
        }, 260);
      };

      if (closeBtn) closeBtn.onclick = dismiss;
      const autoTimer = setTimeout(dismiss, 3800);
      toast.onmouseenter = () => clearTimeout(autoTimer);
      toast.onmouseleave = () => setTimeout(dismiss, 2000);

      container.appendChild(toast);
    }

    // Override thông minh window.alert để chuyển đổi toàn bộ popup trình duyệt thành Toast Notifications
    if (!window._origAlert) {
      window._origAlert = window.alert;
      window.alert = function(msg) {
        if (typeof msg === 'string') {
          const isErr = /lỗi|thất bại|không thể|vui lòng|sai|chưa|từ chối/i.test(msg);
          moToast(msg, isErr ? 'error' : 'success');
        } else {
          window._origAlert(msg);
        }
      };
    }

    // Hàm lấy Token Sanctum toàn cục phục vụ các API nội bộ
    function getSanctumToken() {
      const storedToken = localStorage.getItem('auth_token');
      // Luôn ưu tiên Token Sanctum thực tế của người dùng đang đăng nhập (Admin, Sales, PM, Kế toán, Tenant)
      if (storedToken && storedToken.trim() !== '') {
        return storedToken;
      }
      // Kiểm tra trong Cookie nếu có
      const match = document.cookie.match(new RegExp('(^| )auth_token=([^;]+)'));
      if (match && match[2]) {
        return decodeURIComponent(match[2]);
      }
      return '';
    }

    /* ==========================================================================
       FETCH / AXIOS INTERCEPTOR (Tự động gắn Bearer Token & Bắt lỗi HTTP tiếng Việt)
       ========================================================================== */
    (function setupFetchInterceptor() {
      const originalFetch = window.fetch;
      window.fetch = async function(resource, config = {}) {
        const url = typeof resource === 'string' ? resource : (resource?.url || '');
        // 1. Request Interceptor: Đính kèm Token (Bearer Auth) & Headers tự động cho mọi request tới /api/
        if (url.includes('/api/')) {
          config.headers = config.headers || {};
          const token = getSanctumToken();
          if (token) {
            if (config.headers instanceof Headers) {
              if (!config.headers.has('Authorization')) config.headers.set('Authorization', 'Bearer ' + token);
            } else if (!config.headers['Authorization'] && !config.headers['authorization']) {
              config.headers['Authorization'] = 'Bearer ' + token;
            }
          }
          const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
          if (csrf) {
            if (config.headers instanceof Headers) {
              if (!config.headers.has('X-CSRF-TOKEN')) config.headers.set('X-CSRF-TOKEN', csrf);
            } else if (!config.headers['X-CSRF-TOKEN']) {
              config.headers['X-CSRF-TOKEN'] = csrf;
            }
          }
          const curRole = window.currentUiRole || localStorage.getItem('auth_role');
          if (curRole) {
            if (config.headers instanceof Headers) {
              if (!config.headers.has('X-User-Role')) config.headers.set('X-User-Role', curRole);
            } else if (!config.headers['X-User-Role']) {
              config.headers['X-User-Role'] = curRole;
            }
          }
        }

        // 2. Response Interceptor: Bắt mã lỗi từ Backend & cảnh báo
        try {
          const response = await originalFetch(resource, config);
          if (!response.ok && url.includes('/api/')) {
            try {
              const cloned = response.clone();
              const errData = await cloned.json();
              let errMsg = errData.message;
              if (errData.errors) {
                const firstKey = Object.keys(errData.errors)[0];
                if (firstKey && errData.errors[firstKey]?.[0]) {
                  errMsg = errData.errors[firstKey][0];
                }
              }
              if (response.status === 403) {
                moToast(errMsg || 'Bạn không có quyền thực hiện hành động này.', 'error', 'Từ chối truy cập (403)');
              } else if (response.status >= 500) {
                moToast('Lỗi máy chủ nội bộ. Vui lòng thử lại sau hoặc liên hệ Admin.', 'error', 'Lỗi hệ thống (500)');
              }
            } catch (e) {}
          }
          return response;
        } catch (netErr) {
          console.error('[Fetch Interceptor Error]', netErr);
          throw netErr;
        }
      };
    })();

    // Helper toàn cục bảo vệ XSS khi render HTML động
    function escapeHtml(str) {
      if (str === null || str === undefined) return '';
      return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
    }

    // Helper tạo headers chuẩn cho API request
    function getAuthHeaders(isFormData = false) {
      const token = getSanctumToken();
      const headers = { 'Accept': 'application/json' };
      if (!isFormData) {
        headers['Content-Type'] = 'application/json';
      }
      if (token) {
        headers['Authorization'] = 'Bearer ' + token;
      }
      const role = window.currentUiRole || localStorage.getItem('auth_role');
      if (role) {
        headers['X-User-Role'] = role;
      }
      // Luôn ưu tiên ID người dùng thực tế đang đăng nhập
      let authedUserId = null;
      try {
        const u = JSON.parse(localStorage.getItem('auth_user') || '{}');
        if (u && u.id) authedUserId = u.id;
      } catch (e) {}

      if (authedUserId) {
        headers['X-User-Id'] = authedUserId;
      } else if (window.currentStaffUser && window.currentStaffUser.id) {
        headers['X-User-Id'] = window.currentStaffUser.id;
      }
      return headers;
    }

    // Hỗ trợ cuộn chuột lăn tự nhiên cho hàng ảnh nhỏ thumbnail
    document.addEventListener('wheel', (e) => {
      const thumbs = e.target.closest('.detail-sub-thumbs');
      if (thumbs && e.deltaY !== 0) {
        e.preventDefault();
        thumbs.scrollLeft += e.deltaY;
      }
    }, { passive: false });

    // Điều hướng & Lọc thông minh từ thanh Header
    function handleHeaderFilter(event, type, value, fallbackUrl) {
      const isHome = window.location.pathname === '/' || window.location.pathname === '';
      const filterSection = document.getElementById('mo-building-filter-section');

      if (isHome && filterSection && typeof Livewire !== 'undefined') {
        if (event) {
          event.preventDefault();
        }

        if (type === 'province') {
          Livewire.dispatch('filter-province', { province: value });
        } else if (type === 'district') {
          Livewire.dispatch('filter-district', { district: value });
        } else if (type === 'grade') {
          Livewire.dispatch('filter-grade', { grade: value });
        } else if (type === 'price') {
          Livewire.dispatch('filter-price', { price: value });
        } else if (type === 'reset') {
          Livewire.dispatch('reset-filters');
        }

        // Cập nhật gạch chân active trên navbar
        document.querySelectorAll('.mo-nav .mo-nav-item').forEach(item => item.classList.remove('active'));
        if (event && event.currentTarget) {
          const parentWrap = event.currentTarget.closest('.mo-nav-dropdown-wrap');
          if (parentWrap) {
            const parentBtn = parentWrap.querySelector('.mo-nav-item');
            if (parentBtn) parentBtn.classList.add('active');
          } else {
            event.currentTarget.classList.add('active');
          }
        }

        // Tự động đóng dropdown sau khi bấm chọn
        document.querySelectorAll('.mo-nav-dropdown-wrap.open').forEach(el => el.classList.remove('open'));

        // Cuộn mượt xuống khối tìm kiếm danh sách tòa nhà
        const offset = 80;
        const elementPosition = filterSection.getBoundingClientRect().top;
        const offsetPosition = elementPosition + window.pageYOffset - offset;

        window.scrollTo({
          top: offsetPosition,
          behavior: 'smooth'
        });

        return false;
      }

      // Đang ở trang khác (trang chi tiết tòa nhà, báo giá...): chuyển về trang chủ kèm bộ lọc
      if (fallbackUrl) {
        window.location.href = fallbackUrl;
      }
      return true;
    }

    // Helper mở modal dùng chung toàn hệ thống
    function openModal(modalId) {
      const modal = document.getElementById(modalId);
      if (modal) {
        modal.classList.add('show');
        modal.style.display = 'flex';
      }
    }

    // Helper đóng modal dùng chung toàn hệ thống
    function closeModal(modalId) {
      const modal = document.getElementById(modalId);
      if (modal) {
        modal.classList.remove('show');
        modal.style.display = 'none';
      }
    }

    // Toggle click cho Dropdown Header Navigation (Kịch bản 1)
    window.toggleNavDropdown = function(event, element) {
      // Nếu click trúng link bên trong dropdown thì để link tự điều hướng
      if (event.target.closest('.mo-nav-dropdown')) {
        return;
      }

      event.preventDefault();
      event.stopPropagation();

      const wrap = element.closest('.mo-nav-dropdown-wrap') || element;
      const isOpen = wrap.classList.contains('open');

      // Đóng tất cả dropdown đang mở khác
      document.querySelectorAll('.mo-nav-dropdown-wrap.open').forEach(el => {
        if (el !== wrap) el.classList.remove('open');
      });

      if (isOpen) {
        wrap.classList.remove('open');
      } else {
        wrap.classList.add('open');
      }
    };

    // Tự động đóng dropdown khi click ra ngoài màn hình
    document.addEventListener('click', function(e) {
      if (!e.target.closest('.mo-nav-dropdown-wrap')) {
        document.querySelectorAll('.mo-nav-dropdown-wrap.open').forEach(el => {
          el.classList.remove('open');
        });
      }
    });

    /* ==========================================================================
       MOBILE MENU DRAWER CONTROLLER
       ========================================================================== */
    window.toggleMobileMenu = function() {
      const drawer = document.getElementById('mo-mobile-drawer');
      const backdrop = document.getElementById('mo-mobile-backdrop');
      if (!drawer) return;
      if (drawer.classList.contains('open')) {
        window.closeMobileMenu();
      } else {
        window.openMobileMenu();
      }
    };

    window.openMobileMenu = function() {
      const drawer = document.getElementById('mo-mobile-drawer');
      const backdrop = document.getElementById('mo-mobile-backdrop');
      if (drawer) drawer.classList.add('open');
      if (backdrop) backdrop.classList.add('open');
      document.body.style.overflow = 'hidden';
    };

    window.closeMobileMenu = function() {
      const drawer = document.getElementById('mo-mobile-drawer');
      const backdrop = document.getElementById('mo-mobile-backdrop');
      if (drawer) drawer.classList.remove('open');
      if (backdrop) backdrop.classList.remove('open');
      document.body.style.overflow = '';
    };

    window.toggleDrawerAccordion = function(button) {
      const item = button.closest('.mo-drawer-accordion-item');
      if (!item) return;
      const wasOpen = item.classList.contains('open');
      document.querySelectorAll('.mo-drawer-accordion-item.open').forEach(el => {
        if (el !== item) el.classList.remove('open');
      });
      if (wasOpen) {
        item.classList.remove('open');
      } else {
        item.classList.add('open');
      }
    };

    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape') {
        window.closeMobileMenu();
      }
    });

    // Kịch bản 1: Xử lý submit Lead Capture từ Empty State Card
    window.handleEmptyLeadSubmit = async function(event, regionName) {
      if (event) event.preventDefault();
      const nameInput = document.getElementById('empty-lead-name');
      const phoneInput = document.getElementById('empty-lead-phone');
      const areaInput = document.getElementById('empty-lead-area');

      const name = nameInput ? nameInput.value.trim() : '';
      const phone = phoneInput ? phoneInput.value.trim() : '';
      const area = areaInput ? parseFloat(areaInput.value) || 100 : 100;

      if (!phone) {
        alert('Vui lòng nhập số điện thoại để nhận thông báo.');
        return;
      }

      // 1. Thử gửi trực tiếp qua API v1 portal leads
      try {
        const cleanPhone = phone.replace(/[^0-9]/g, '');
        const payload = {
          contact_full_name: name || ('Khách hàng quan tâm ' + regionName),
          phone: phone,
          email: 'lead_' + cleanPhone + '@officeleasing.vn',
          desired_area_sqm: area,
          customer_note: '[Giữ chỗ ưu tiên] Đăng ký nhận thông báo mặt bằng mới tại: ' + regionName
        };

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const res = await fetch('/api/v1/portal/leads', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken || ''
          },
          body: JSON.stringify(payload)
        });

        if (res.ok) {
          alert('Đăng ký giữ chỗ thành công! Chuyên viên phụ trách khu vực ' + regionName + ' sẽ liên hệ ngay khi có mặt bằng trống mới phù hợp.');
          if (nameInput) nameInput.value = '';
          if (phoneInput) phoneInput.value = '';
          return;
        }
      } catch (err) {
        console.warn('API lead submit fallback to modal:', err);
      }

      // 2. Fallback: Mở Quote Modal với dữ liệu đã điền sẵn
      if (typeof openQuoteForBuilding === 'function') {
        openQuoteForBuilding('Đăng ký giữ chỗ tại ' + regionName);
        const modalName = document.getElementById('modal-name');
        const modalPhone = document.getElementById('modal-phone');
        const modalArea = document.getElementById('modal-area');
        if (modalName && name) modalName.value = name;
        if (modalPhone && phone) modalPhone.value = phone;
        if (modalArea && area) modalArea.value = area;
      } else {
        alert('Cảm ơn quý khách! Chúng tôi đã ghi nhận nhu cầu thuê tại ' + regionName + ' và sẽ liên hệ sớm nhất.');
      }
    };

    /* ==========================================================================
       SMART STICKY HEADER (Auto-hide on scroll down, show on scroll up - Headroom style)
       ========================================================================== */
    (function initSmartHeader() {
      const header = document.querySelector('.mo-header');
      if (!header) return;

      let lastScrollY = window.pageYOffset || document.documentElement.scrollTop || 0;
      let isHidden = false;

      function onScroll() {
        const currentScrollY = window.pageYOffset || document.documentElement.scrollTop || 0;

        // 1. Khi ở gần đỉnh trang (< 80px): Luôn hiển thị và bỏ shadow đậm
        if (currentScrollY <= 80) {
          if (isHidden) {
            header.classList.remove('header-hidden');
            isHidden = false;
          }
          header.classList.remove('header-scrolled');
          lastScrollY = currentScrollY <= 0 ? 0 : currentScrollY;
          return;
        }

        header.classList.add('header-scrolled');

        // Bỏ qua vi rung (jitter < 5px)
        const delta = currentScrollY - lastScrollY;
        if (Math.abs(delta) < 5) return;

        // 2. Cuộn XUỐNG quá 120px: Ẩn thanh header lên trên
        if (delta > 0 && currentScrollY > 120) {
          if (!isHidden) {
            header.classList.add('header-hidden');
            isHidden = true;
            // Tự động đóng dropdown nếu đang mở dở
            document.querySelectorAll('.mo-nav-dropdown-wrap.open').forEach(el => el.classList.remove('open'));
          }
        }
        // 3. Cuộn LÊN: Lập tức trượt header xuống mượt mà
        else if (delta < 0) {
          if (isHidden) {
            header.classList.remove('header-hidden');
            isHidden = false;
          }
        }

        lastScrollY = currentScrollY <= 0 ? 0 : currentScrollY;
      }

      function onResize() {
        if (isHidden) {
          header.classList.remove('header-hidden');
          isHidden = false;
        }
        if (window.innerWidth > 1024 && typeof window.closeMobileMenu === 'function') {
          window.closeMobileMenu();
        }
      }

      window.addEventListener('scroll', onScroll, { passive: true });
      window.addEventListener('resize', onResize, { passive: true });
      onScroll();
    })();

    /* ==========================================================================
       STATEFUL ACTION CONFIRMATION MODAL CONTROLLER (Tái sử dụng toàn hệ thống)
       ========================================================================== */
    const _moConfirmState = {
      options: null,
      autoDismissTimer: null
    };

    function _moSwitchConfirmState(state) {
      const p = document.getElementById('confirm-state-prompt');
      const pr = document.getElementById('confirm-state-processing');
      const s = document.getElementById('confirm-state-success');
      const f = document.getElementById('confirm-state-failed');
      const x = document.getElementById('confirm-modal-close-x');

      if (p) p.style.display = state === 'prompt' ? 'flex' : 'none';
      if (pr) pr.style.display = state === 'processing' ? 'flex' : 'none';
      if (s) s.style.display = state === 'success' ? 'flex' : 'none';
      if (f) f.style.display = state === 'failed' ? 'flex' : 'none';
      if (x) x.style.display = state === 'processing' ? 'none' : 'block';
    }

    window.moConfirmAction = function(options) {
      _moConfirmState.options = Object.assign({
        title: 'Xác Nhận Thao Tác',
        message: 'Bạn có chắc chắn muốn thực hiện hành động này?',
        targetName: '',
        note: '',
        confirmText: 'Xóa',
        cancelText: 'Hủy bỏ',
        type: 'danger', // 'danger' | 'warning' | 'primary' | 'info'
        action: async () => {},
        onSuccess: () => {}
      }, options || {});

      if (_moConfirmState.autoDismissTimer) {
        clearTimeout(_moConfirmState.autoDismissTimer);
        _moConfirmState.autoDismissTimer = null;
      }

      const modal = document.getElementById('modal-confirm-action');
      if (!modal) return;

      const type = _moConfirmState.options.type;
      const badge = document.getElementById('confirm-modal-icon-badge');
      const icon = document.getElementById('confirm-modal-icon');
      const btnAction = document.getElementById('confirm-modal-btn-action');
      const btnIcon = document.getElementById('confirm-modal-btn-icon');
      const btnText = document.getElementById('confirm-modal-btn-action-text');

      if (badge && icon && btnAction && btnIcon && btnText) {
        if (type === 'danger') {
          badge.style.background = '#fee2e2';
          badge.style.color = '#dc2626';
          badge.style.boxShadow = '0 0 0 6px #fef2f2';
          icon.className = _moConfirmState.options.icon || 'fa-solid fa-trash-can';
          btnAction.style.background = '#dc2626';
          btnAction.style.boxShadow = '0 2px 4px rgba(220,38,38,0.25)';
          btnIcon.className = _moConfirmState.options.btnIcon || 'fa-solid fa-trash-can';
        } else if (type === 'warning') {
          badge.style.background = '#fef3c7';
          badge.style.color = '#d97706';
          badge.style.boxShadow = '0 0 0 6px #fffbeb';
          icon.className = _moConfirmState.options.icon || 'fa-solid fa-triangle-exclamation';
          btnAction.style.background = '#d97706';
          btnAction.style.boxShadow = '0 2px 4px rgba(217,119,6,0.25)';
          btnIcon.className = _moConfirmState.options.btnIcon || 'fa-solid fa-triangle-exclamation';
        } else if (type === 'success') {
          badge.style.background = '#dcfce7';
          badge.style.color = '#16a34a';
          badge.style.boxShadow = '0 0 0 6px #f0fdf4';
          icon.className = _moConfirmState.options.icon || 'fa-solid fa-circle-check';
          btnAction.style.background = '#16a34a';
          btnAction.style.boxShadow = '0 2px 4px rgba(22,163,74,0.25)';
          btnIcon.className = _moConfirmState.options.btnIcon || 'fa-solid fa-check';
        } else {
          badge.style.background = '#dbeafe';
          badge.style.color = '#2563eb';
          badge.style.boxShadow = '0 0 0 6px #eff6ff';
          icon.className = _moConfirmState.options.icon || 'fa-solid fa-circle-question';
          btnAction.style.background = '#2563eb';
          btnAction.style.boxShadow = '0 2px 4px rgba(37,99,235,0.25)';
          btnIcon.className = _moConfirmState.options.btnIcon || 'fa-solid fa-check';
        }
      }

      const elTitle = document.getElementById('confirm-modal-title');
      const elMsg = document.getElementById('confirm-modal-message');
      const elCancel = document.getElementById('confirm-modal-btn-cancel');

      if (elTitle) elTitle.innerText = _moConfirmState.options.title;
      if (elMsg) {
        if (_moConfirmState.options.message) {
          elMsg.innerText = _moConfirmState.options.message;
          elMsg.style.display = 'block';
        } else {
          elMsg.innerText = '';
          elMsg.style.display = 'none';
        }
      }
      if (btnText) btnText.innerText = _moConfirmState.options.confirmText;
      if (elCancel) elCancel.innerText = _moConfirmState.options.cancelText;

      const targetWrap = document.getElementById('confirm-modal-target-wrap');
      const targetText = document.getElementById('confirm-modal-target-text');
      if (targetWrap && targetText) {
        if (_moConfirmState.options.targetName) {
          targetText.innerText = _moConfirmState.options.targetName;
          targetWrap.style.display = 'block';
        } else {
          targetWrap.style.display = 'none';
        }
      }

      const noteWrap = document.getElementById('confirm-modal-note-wrap');
      const noteText = document.getElementById('confirm-modal-note-text');
      if (noteWrap && noteText) {
        if (_moConfirmState.options.note) {
          noteText.innerText = _moConfirmState.options.note;
          noteWrap.style.display = 'flex';
        } else {
          noteWrap.style.display = 'none';
        }
      }

      _moSwitchConfirmState('prompt');
      modal.style.display = 'flex';
    };

    window.moCloseConfirmAction = function() {
      if (_moConfirmState.autoDismissTimer) {
        clearTimeout(_moConfirmState.autoDismissTimer);
        _moConfirmState.autoDismissTimer = null;
      }
      const modal = document.getElementById('modal-confirm-action');
      if (modal) modal.style.display = 'none';
    };

    window.moDismissSuccessConfirmAction = function() {
      if (_moConfirmState.autoDismissTimer) {
        clearTimeout(_moConfirmState.autoDismissTimer);
        _moConfirmState.autoDismissTimer = null;
      }
      const modal = document.getElementById('modal-confirm-action');
      if (modal) modal.style.display = 'none';
      if (_moConfirmState.options && typeof _moConfirmState.options.onSuccess === 'function') {
        try {
          _moConfirmState.options.onSuccess();
        } catch (err) {
          console.error(err);
        }
      }
    };

    window.moRetryConfirmAction = function() {
      _moSwitchConfirmState('prompt');
    };

    window.moExecuteConfirmAction = async function() {
      if (!_moConfirmState.options || typeof _moConfirmState.options.action !== 'function') return;

      _moSwitchConfirmState('processing');

      try {
        const result = await _moConfirmState.options.action();
        const successMsg = (typeof result === 'string' && result) ? result :
                           (result && result.message) ? result.message :
                           'Dữ liệu đã được cập nhật thành công.';

        const elSuccessMsg = document.getElementById('confirm-modal-success-message');
        if (elSuccessMsg) elSuccessMsg.innerText = successMsg;

        const bar = document.getElementById('confirm-modal-success-progress');
        if (bar) {
          bar.style.transition = 'none';
          bar.style.width = '100%';
          setTimeout(() => {
            bar.style.transition = 'width 1.5s linear';
            bar.style.width = '0%';
          }, 30);
        }

        _moSwitchConfirmState('success');

        _moConfirmState.autoDismissTimer = setTimeout(() => {
          moDismissSuccessConfirmAction();
        }, 1500);

      } catch (err) {
        const errorMsg = (err && err.message) ? err.message : 'Có lỗi không xác định xảy ra khi thực hiện thao tác.';
        const elFailedMsg = document.getElementById('confirm-modal-failed-message');
        if (elFailedMsg) elFailedMsg.innerText = errorMsg;
        _moSwitchConfirmState('failed');
      }
    };

    // Close on click backdrop or escape
    document.addEventListener('DOMContentLoaded', function() {
      const modal = document.getElementById('modal-confirm-action');
      if (modal) {
        modal.addEventListener('click', function(e) {
          if (e.target === modal) {
            const pr = document.getElementById('confirm-state-processing');
            if (!pr || pr.style.display !== 'flex') {
              moCloseConfirmAction();
            }
          }
        });
      }
    });

    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape') {
        const modal = document.getElementById('modal-confirm-action');
        if (modal && modal.style.display === 'flex') {
          const pr = document.getElementById('confirm-state-processing');
          if (!pr || pr.style.display !== 'flex') {
            moCloseConfirmAction();
          }
        }
      }
    });
  </script>
