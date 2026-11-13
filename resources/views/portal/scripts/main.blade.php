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

        if (type === 'district') {
          Livewire.dispatch('filter-district', { district: value });
        } else if (type === 'grade') {
          Livewire.dispatch('filter-grade', { grade: value });
        } else if (type === 'reset') {
          Livewire.dispatch('reset-filters');
        }

        // Cập nhật gạch chân active trên navbar
        document.querySelectorAll('.mo-nav .mo-nav-item').forEach(item => item.classList.remove('active'));
        if (event && event.currentTarget) {
          event.currentTarget.classList.add('active');
        }

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


  </script>
