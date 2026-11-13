  <script>
    /* ==========================================================================
       1. DATABASE & SERVER SYNC (BLADE + RESTFUL API)
       ========================================================================== */
    const SERVER_BUILDINGS = @json($serverBuildings ?? []);
    const BUILDINGS_DATA = (SERVER_BUILDINGS && SERVER_BUILDINGS.length > 0) ? SERVER_BUILDINGS : [];
    let currentSelectedBuilding = BUILDINGS_DATA[0] || null;

    const SERVER_STAFF = @json($serverStaff ?? []);
    const DEFAULT_STAFF_TOKEN = @json($defaultStaffToken ?? '');

    // Hàm lấy Token Sanctum toàn cục phục vụ các API nội bộ
    function getSanctumToken() {
      const storedRole = String(localStorage.getItem('auth_role') || '').toUpperCase();
      const storedToken = localStorage.getItem('auth_token');
      // Nếu đang đăng nhập vai trò Admin thì ưu tiên dùng token cá nhân
      if (storedToken && (storedRole === 'ADMIN' || storedRole === 'SUPER_ADMIN')) {
        return storedToken;
      }
      // Ưu tiên token Super Admin mặc định cho các tác vụ quản trị RBAC
      if (typeof DEFAULT_STAFF_TOKEN === 'string' && DEFAULT_STAFF_TOKEN.trim() !== '') {
        return DEFAULT_STAFF_TOKEN;
      }
      return storedToken || '';
    }

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
          customer_note: '[Kịch bản 1 - Giữ chỗ ưu tiên] Đăng ký nhận thông báo mặt bằng mới tại: ' + regionName
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

      window.addEventListener('scroll', onScroll, { passive: true });
      onScroll();
    })();
  </script>
