<script>
  /* ==========================================================================
     REALTIME TOAST NOTIFICATIONS (Laravel Reverb + Echo + moToast)
     Hỗ trợ 4 kịch bản:
     1. Phân phối tòa nhà cho Sale / PM / Accountant (staff.{id})
     2. Lead mới tự động phân phối cho Sale (sales.{id})
     3. Hợp đồng ký duyệt / từ chối / thay đổi trạng thái (staff.{id} & admin-notifications)
     4. Cảnh báo Admin khi Lead không tự động phân phối được (admin-notifications)
     ========================================================================== */
  (function () {
    window._activeEchoChannels = window._activeEchoChannels || [];

    // Âm thanh thông báo nhẹ nhàng chuẩn B2B bằng Web Audio API (0 external file dependency)
    function playNotificationChime(type) {
      try {
        const AudioCtx = window.AudioContext || window.webkitAudioContext;
        if (!AudioCtx) return;
        const ctx = new AudioCtx();
        if (ctx.state === 'suspended') {
          ctx.resume();
        }

        const now = ctx.currentTime;
        let freq1 = 523.25; // C5
        let freq2 = 783.99; // G5

        if (type === 'warning') {
          freq1 = 659.25; // E5
          freq2 = 523.25; // C5
        } else if (type === 'error') {
          freq1 = 698.46; // F5
          freq2 = 349.23; // F4
        } else if (type === 'info') {
          freq1 = 440.00; // A4
          freq2 = 659.25; // E5
        }

        const osc = ctx.createOscillator();
        const gain = ctx.createGain();

        osc.type = 'sine';
        osc.frequency.setValueAtTime(freq1, now);
        osc.frequency.exponentialRampToValueAtTime(freq2, now + 0.12);

        gain.gain.setValueAtTime(0.08, now);
        gain.gain.exponentialRampToValueAtTime(0.001, now + 0.35);

        osc.connect(gain);
        gain.connect(ctx.destination);

        osc.start(now);
        osc.stop(now + 0.36);
      } catch (e) {
        // Trình duyệt có thể chặn audio khi chưa có user gesture
      }
    }
    window.playNotificationChime = playNotificationChime;

    // Hủy đăng ký các kênh cũ khi logout hoặc chuyển đổi người dùng
    function teardownRealtimeNotifications() {
      if (!window.Echo || !window._activeEchoChannels) return;
      window._activeEchoChannels.forEach(function (channelName) {
        try {
          window.Echo.leave(channelName);
        } catch (e) {}
      });
      window._activeEchoChannels = [];
    }
    window.teardownRealtimeNotifications = teardownRealtimeNotifications;

    // Khởi tạo các kênh lắng nghe WebSocket Reverb
    function initRealtimeNotifications() {
      const authUserStr = localStorage.getItem('auth_user');
      const authRole = String(localStorage.getItem('auth_role') || '').toUpperCase();

      if (!authUserStr) return;

      let user = null;
      try {
        user = JSON.parse(authUserStr);
      } catch (e) {
        return;
      }

      if (!user || !user.id) return;

      // Đợi window.Echo được nạp từ Vite bundle
      if (!window.Echo) {
        let attempts = 0;
        const checkEchoInterval = setInterval(function () {
          attempts++;
          if (window.Echo) {
            clearInterval(checkEchoInterval);
            setupEchoChannels(user, authRole);
          } else if (attempts > 30) {
            clearInterval(checkEchoInterval);
            console.warn('[Realtime] Laravel Echo chưa sẵn sàng sau 3s.');
          }
        }, 100);
        return;
      }

      setupEchoChannels(user, authRole);
    }
    window.initRealtimeNotifications = initRealtimeNotifications;

    function setupEchoChannels(user, role) {
      teardownRealtimeNotifications();

      const staffChannelName = 'staff.' + user.id;
      const salesChannelName = 'sales.' + user.id;
      const adminChannelName = 'admin-notifications';

      // -------------------------------------------------------------
      // 1 & 2 & 3. KÊNH CÁ NHÂN NHÂN SỰ: SALE, PM, ACCOUNTANT (staff.{id})
      // -------------------------------------------------------------
      if (role === 'SALES' || role === 'SALE' || role === 'PM' || role === 'PROPERTY_MANAGER' || role === 'ACCOUNTANT') {
        const staffChannel = window.Echo.channel(staffChannelName);
        window._activeEchoChannels.push(staffChannelName);

        // Kịch bản 1: Phân phối tòa nhà cho nhân sự
        staffChannel.listen('.building.assigned', function (data) {
          if (typeof moToast === 'function') {
            moToast(data.message, 'info', 'Phân công tòa nhà mới');
          }
          playNotificationChime('info');

          // Tự động cập nhật giao diện nếu đang ở danh sách tòa nhà
          if (typeof loadServerBuildings === 'function') {
            loadServerBuildings();
          }
        });

        // Kịch bản: Nhân sự bị gỡ khỏi tòa nhà
        staffChannel.listen('.building.removed', function (data) {
          if (typeof moToast === 'function') {
            moToast(data.message, 'warning', 'Thay đổi phân công tòa nhà');
          }
          playNotificationChime('warning');

          if (typeof loadServerBuildings === 'function') {
            loadServerBuildings();
          }
        });

        // Kịch bản 3: Hợp đồng của tòa nhà quản lý thay đổi trạng thái
        staffChannel.listen('.contract.status_changed', function (data) {
          const action = String(data.action || '').toUpperCase();
          let toastType = 'info';
          let toastTitle = 'Cập nhật hợp đồng';

          if (action === 'SIGNED') {
            toastType = 'success';
            toastTitle = 'Ký duyệt hợp đồng';
          } else if (action === 'REJECTED') {
            toastType = 'error';
            toastTitle = 'Hợp đồng bị từ chối / Hủy';
          } else if (action === 'TERMINATED') {
            toastType = 'warning';
            toastTitle = 'Thanh lý hợp đồng';
          } else if (action === 'RENEWED') {
            toastType = 'info';
            toastTitle = 'Gia hạn hợp đồng';
          }

          if (typeof moToast === 'function') {
            moToast(data.message, toastType, toastTitle);
          }
          playNotificationChime(toastType);

          // Tự động tải lại bảng hợp đồng nếu đang mở tab hợp đồng
          if (typeof loadContractsList === 'function') {
            loadContractsList();
          }
        });

        // Kịch bản 2: Lead mới được phân bổ cho Sales (qua kênh sales.{id} hoặc staff.{id})
        if (role === 'SALES' || role === 'SALE') {
          const salesChannel = window.Echo.channel(salesChannelName);
          window._activeEchoChannels.push(salesChannelName);

          const handleLeadAssigned = function (data) {
            if (typeof moToast === 'function') {
              moToast(data.message, 'success', 'Khách hàng tiềm năng mới (Lead)');
            }
            playNotificationChime('success');

            // Tự động tải lại bảng Leads nếu đang mở tab CRM
            if (typeof loadCrmLeadsList === 'function') {
              loadCrmLeadsList();
            }
          };

          salesChannel.listen('.lead.assigned', handleLeadAssigned);
          staffChannel.listen('.lead.assigned', handleLeadAssigned);
        }
      }

      // -------------------------------------------------------------
      // 4. KÊNH QUẢN TRỊ VIÊN: ADMIN (admin-notifications)
      // -------------------------------------------------------------
      if (role === 'ADMIN') {
        const adminChannel = window.Echo.channel(adminChannelName);
        window._activeEchoChannels.push(adminChannelName);

        // Kịch bản 4: Lead không được phân phối tự động -> cảnh báo Admin phân bổ bằng tay
        adminChannel.listen('.lead.pending_assignment', function (data) {
          const alertMsg = data.message || `Khách hàng ${data.contact_full_name || ''} đang chờ phân bổ chuyên viên kinh doanh thủ công.`;
          if (typeof moToast === 'function') {
            moToast(alertMsg, 'warning', 'Cảnh báo: Lead chờ phân bổ');
          }
          playNotificationChime('warning');

          // Tự động reload danh sách lead CRM để hiển thị lead mới trong tab chờ duyệt
          if (typeof loadCrmLeadsList === 'function') {
            loadCrmLeadsList();
          }
        });

        // Admin cũng nhận thông báo trạng thái hợp đồng toàn hệ thống
        adminChannel.listen('.contract.status_changed', function (data) {
          const action = String(data.action || '').toUpperCase();
          const toastType = (action === 'SIGNED') ? 'success' : ((action === 'REJECTED') ? 'error' : 'info');

          if (typeof moToast === 'function') {
            moToast(data.message, toastType, 'Quản lý hợp đồng');
          }
          playNotificationChime(toastType);

          if (typeof loadContractsList === 'function') {
            loadContractsList();
          }
        });
      }
    }

    // Tự động kích hoạt khi trang tải xong
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', initRealtimeNotifications);
    } else {
      initRealtimeNotifications();
    }
  })();
</script>
