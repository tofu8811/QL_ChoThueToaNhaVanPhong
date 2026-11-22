<script>
    /* ==========================================================================
       GLOBAL INITIALIZATION & SESSION PERSISTENCE (LIFECYCLE INIT)
       Giải quyết triệt để vấn đề: F5 / Refresh / Reset trang không bị đăng xuất
       và tự động duy trì đúng trạng thái người dùng cùng tuyến điều hướng SPA.
       ========================================================================== */
    (function () {
      function initializeApp() {
        // 1. Phục hồi trạng thái đăng nhập từ localStorage (Avatar, Tên, Quyền hạn)
        if (typeof checkAuthState === 'function') {
          checkAuthState();
        }

        // 2. Khởi tạo danh sách tòa nhà hiển thị trên Portal công khai
        if (typeof renderBuildings === 'function' && typeof BUILDINGS_DATA !== 'undefined' && Array.isArray(BUILDINGS_DATA)) {
          renderBuildings(BUILDINGS_DATA);
        }

        // 3. Phục hồi định tuyến SPA (Hash Routing: Back / Forward / F5)
        if (typeof handleAppRouting === 'function') {
          handleAppRouting(false);
        }

        // 4. Nếu là Admin, nạp trước danh mục nhân sự RBAC
        const currentRole = String(localStorage.getItem('auth_role') || '').toLowerCase();
        if ((currentRole === 'admin' || currentRole === 'super_admin') && typeof preloadAllStaffData === 'function') {
          preloadAllStaffData();
        }

        // 5. Tự động mở modal đăng nhập nếu có tham số auth=required trong URL
        try {
          const urlParams = new URLSearchParams(window.location.search);
          if (urlParams.get('auth') === 'required' || window.location.hash === '#login') {
            if (typeof openLoginModal === 'function') {
              openLoginModal();
            }
          }
        } catch (e) {}
      }

      if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeApp);
      } else {
        initializeApp();
      }
    })();
</script>
