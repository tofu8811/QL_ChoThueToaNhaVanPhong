<script>
  /* ==========================================================================
     REALTIME TOAST NOTIFICATIONS (Laravel Reverb + Echo + moToast)
     Hỗ trợ các kịch bản:
     1. Phân phối / Gỡ tòa nhà cho Sale / PM / Accountant (staff.{id}, sales.{id}, user.{id})
     2. Lead mới tự động phân phối cho Sale (sales.{id}, staff.{id})
     3. Hợp đồng ký duyệt / từ chối / thay đổi trạng thái (staff.{id} & admin-notifications)
     4. Cảnh báo Admin khi Lead không tự động phân phối được (admin-notifications)
     ========================================================================== */
  (function () {
    window._activeEchoChannels = window._activeEchoChannels || [];
    window._recentNotifKeys = window._recentNotifKeys || new Set();

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

    // Hỗ trợ tự động đẩy thông báo vào Trung tâm Thông báo (chuông Topbar)
    function appendNotificationToTopbar(item) {
      const container = document.getElementById('bms-notif-list-container');
      if (!container) return;

      // Xóa khối placeholder rỗng "Không có cảnh báo..." nếu đang tồn tại
      const emptyPlaceholder = container.querySelector('.fa-circle-check');
      if (emptyPlaceholder && emptyPlaceholder.parentElement) {
        emptyPlaceholder.parentElement.remove();
      }

      const el = document.createElement('div');
      el.style.cssText = 'padding:12px 16px;border-bottom:1px solid #f8fafc;display:flex;gap:12px;align-items:flex-start;cursor:pointer;transition:background 0.15s;animation:moToastSlideIn 0.3s ease;';
      el.onmouseover = function() { this.style.background = '#f8fafc'; };
      el.onmouseout = function() { this.style.background = '#ffffff'; };
      el.onclick = function() {
        if (typeof handleBmsNotificationClick === 'function') {
          handleBmsNotificationClick(item.target_tab || '', item.nav_id || '');
        }
      };

      const safeTitle = typeof escapeHtml === 'function' ? escapeHtml(item.title) : item.title;
      const safeSub = typeof escapeHtml === 'function' ? escapeHtml(item.subtitle) : item.subtitle;

      el.innerHTML = `
        <div style="width:34px;height:34px;border-radius:9px;background:${item.icon_bg || '#eff6ff'};color:${item.icon_color || '#2563eb'};display:flex;align-items:center;justify-content:center;font-size:14px;flex-shrink:0;margin-top:2px;">
          <i class="${item.icon || 'fa-solid fa-bell'}"></i>
        </div>
        <div style="flex:1;min-width:0;">
          <div style="margin-bottom:3px;">
            <span style="font-size:10.5px;font-weight:700;padding:2px 7px;border-radius:5px;${item.badge_style || ''}">${item.badge_text || 'THÔNG BÁO'}</span>
          </div>
          <div style="font-size:13px;font-weight:700;color:#0f172a;line-height:1.35;margin-bottom:2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${safeTitle}</div>
          <div style="font-size:11.5px;color:#64748b;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${safeSub}</div>
        </div>
      `;

      container.insertBefore(el, container.firstChild);

      // Cập nhật số lượng thông báo trên badge và hiển thị chấm đỏ
      const badge = document.getElementById('bms-notif-count-badge');
      if (badge) {
        let count = parseInt(badge.innerText) || 0;
        count++;
        badge.innerText = count + ' việc cần chú ý';
      }
      const notifBtn = document.querySelector('.bms-notif-btn');
      if (notifBtn && !document.getElementById('bms-notif-dot')) {
        const dot = document.createElement('span');
        dot.id = 'bms-notif-dot';
        dot.className = 'bms-notif-dot';
        notifBtn.appendChild(dot);
      }
    }
    window.appendNotificationToTopbar = appendNotificationToTopbar;

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
    function initRealtimeNotifications(overrideUser = null, overrideRole = null) {
      let authUserStr = localStorage.getItem('auth_user');
      let authRole = overrideRole || window.currentUiRole || String(localStorage.getItem('auth_role') || '').toUpperCase();

      let user = overrideUser || null;
      if (!user) {
        if (authUserStr) {
          try {
            user = JSON.parse(authUserStr);
          } catch (e) {}
        }
      }
      if (!user && window.currentStaffUser) {
        user = window.currentStaffUser;
      }

      if (!user || !user.id) return;
      authRole = String(authRole || '').toUpperCase();

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
      const userChannelName = 'user.' + user.id;
      const adminChannelName = 'admin-notifications';

      // -------------------------------------------------------------
      // 1 & 2 & 3. KÊNH CÁ NHÂN NHÂN SỰ: SALE, PM, ACCOUNTANT
      // -------------------------------------------------------------
      if (role === 'SALES' || role === 'SALE' || role === 'PM' || role === 'PROPERTY_MANAGER' || role === 'ACCOUNTANT') {
        const staffChannel = window.Echo.channel(staffChannelName);
        window._activeEchoChannels.push(staffChannelName);

        const userChannel = window.Echo.channel(userChannelName);
        window._activeEchoChannels.push(userChannelName);

        // Xử lý sự kiện Phân công tòa nhà mới
        const handleBuildingAssigned = function (data) {
          const notifKey = 'bld_assign_' + (data.building_id || '') + '_' + (data.staff_id || user.id);
          if (window._recentNotifKeys.has(notifKey)) return;
          window._recentNotifKeys.add(notifKey);
          setTimeout(() => window._recentNotifKeys.delete(notifKey), 3000);

          const msg = data.message || `Bạn vừa được phân công phụ trách tòa nhà ${data.building_name || ''} (${data.building_code || ''}).`;
          if (typeof moToast === 'function') {
            moToast(msg, 'info', 'Phân công tòa nhà mới');
          }
          playNotificationChime('info');

          appendNotificationToTopbar({
            icon: 'fa-solid fa-building-circle-check',
            icon_bg: '#eff6ff',
            icon_color: '#2563eb',
            badge_text: 'Tòa nhà mới',
            badge_style: 'background:#eff6ff;color:#1d4ed8;',
            title: `Phân công tòa: ${data.building_name || 'Tòa nhà mới'}`,
            subtitle: `Mã: ${data.building_code || 'BLD'} • Do ${data.assigned_by || 'Ban Quản Trị'} phân bổ`,
            target_tab: (role === 'SALES' || role === 'SALE') ? 'tab-sales-buildings' : 'tab-admin-buildings',
            nav_id: (role === 'SALES' || role === 'SALE') ? 'nav-sales-buildings' : 'nav-item-buildings'
          });

          // Tự động cập nhật bảng danh sách tòa nhà nếu đang mở
          if (typeof loadAdminBuildingsList === 'function') {
            loadAdminBuildingsList();
          }
          if (typeof reloadBuildingAssignments === 'function') {
            reloadBuildingAssignments();
          }
        };

        // Xử lý sự kiện Gỡ nhân sự khỏi tòa nhà
        const handleBuildingRemoved = function (data) {
          const notifKey = 'bld_removed_' + (data.building_id || '') + '_' + (data.staff_id || user.id);
          if (window._recentNotifKeys.has(notifKey)) return;
          window._recentNotifKeys.add(notifKey);
          setTimeout(() => window._recentNotifKeys.delete(notifKey), 3000);

          const msg = data.message || `Bạn vừa được gỡ khỏi danh sách phụ trách tòa nhà ${data.building_name || ''} (${data.building_code || ''}).`;
          if (typeof moToast === 'function') {
            moToast(msg, 'warning', 'Thay đổi phân công tòa nhà');
          }
          playNotificationChime('warning');

          appendNotificationToTopbar({
            icon: 'fa-solid fa-building-circle-xmark',
            icon_bg: '#fef2f2',
            icon_color: '#e11d48',
            badge_text: 'Gỡ phân công',
            badge_style: 'background:#fef2f2;color:#e11d48;',
            title: `Gỡ khỏi tòa: ${data.building_name || 'Tòa nhà'}`,
            subtitle: `Mã: ${data.building_code || 'BLD'}`,
            target_tab: (role === 'SALES' || role === 'SALE') ? 'tab-sales-buildings' : 'tab-admin-buildings',
            nav_id: (role === 'SALES' || role === 'SALE') ? 'nav-sales-buildings' : 'nav-item-buildings'
          });

          if (typeof loadAdminBuildingsList === 'function') {
            loadAdminBuildingsList();
          }
          if (typeof reloadBuildingAssignments === 'function') {
            reloadBuildingAssignments();
          }
        };

        // Đăng ký cả 2 dạng tên sự kiện (có prefix dot theo Pusher protocol và class name nguyên bản)
        staffChannel.listen('.building.assigned', handleBuildingAssigned);
        staffChannel.listen('building.assigned', handleBuildingAssigned);
        staffChannel.listen('.building.removed', handleBuildingRemoved);
        staffChannel.listen('building.removed', handleBuildingRemoved);

        userChannel.listen('.building.assigned', handleBuildingAssigned);
        userChannel.listen('building.assigned', handleBuildingAssigned);
        userChannel.listen('.building.removed', handleBuildingRemoved);
        userChannel.listen('building.removed', handleBuildingRemoved);

        // Kịch bản 3: Hợp đồng của tòa nhà quản lý thay đổi trạng thái
        const handleContractStatusChanged = function (data) {
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

          appendNotificationToTopbar({
            icon: 'fa-solid fa-file-contract',
            icon_bg: toastType === 'success' ? '#f0fdf4' : (toastType === 'error' ? '#fef2f2' : '#eff6ff'),
            icon_color: toastType === 'success' ? '#16a34a' : (toastType === 'error' ? '#e11d48' : '#2563eb'),
            badge_text: 'Hợp đồng • ' + action,
            badge_style: toastType === 'success' ? 'background:#f0fdf4;color:#15803d;' : 'background:#eff6ff;color:#1d4ed8;',
            title: `Hợp đồng: ${data.contract_code || 'HĐ mới'}`,
            subtitle: data.message || `${data.actor_name || 'Hệ thống'} đã cập nhật trạng thái hợp đồng`,
            target_tab: 'tab-admin-contracts',
            nav_id: 'nav-item-contracts'
          });

          // Tự động tải lại bảng hợp đồng nếu đang mở tab hợp đồng
          if (typeof loadContractsList === 'function') {
            loadContractsList();
          }
        };

        staffChannel.listen('.contract.status_changed', handleContractStatusChanged);
        staffChannel.listen('contract.status_changed', handleContractStatusChanged);
        userChannel.listen('.contract.status_changed', handleContractStatusChanged);
        userChannel.listen('contract.status_changed', handleContractStatusChanged);

        // Kịch bản riêng cho Sales: Lead mới được phân bổ & Đăng ký kênh sales.{id}
        if (role === 'SALES' || role === 'SALE') {
          const salesChannel = window.Echo.channel(salesChannelName);
          window._activeEchoChannels.push(salesChannelName);

          // Sales nhận thông báo gán/gỡ tòa qua cả kênh sales.{id}
          salesChannel.listen('.building.assigned', handleBuildingAssigned);
          salesChannel.listen('building.assigned', handleBuildingAssigned);
          salesChannel.listen('.building.removed', handleBuildingRemoved);
          salesChannel.listen('building.removed', handleBuildingRemoved);

          const handleLeadAssigned = function (data) {
            const notifKey = 'lead_assign_' + (data.id || '');
            if (window._recentNotifKeys.has(notifKey)) return;
            window._recentNotifKeys.add(notifKey);
            setTimeout(() => window._recentNotifKeys.delete(notifKey), 3000);

            if (typeof moToast === 'function') {
              moToast(data.message || `Bạn vừa được phân công chăm sóc khách hàng ${data.contact_full_name || ''}.`, 'success', 'Khách hàng tiềm năng mới (Lead)');
            }
            playNotificationChime('success');

            appendNotificationToTopbar({
              icon: 'fa-solid fa-filter-circle-dollar',
              icon_bg: '#f0fdf4',
              icon_color: '#16a34a',
              badge_text: 'Lead mới',
              badge_style: 'background:#f0fdf4;color:#15803d;',
              title: data.contact_full_name || data.company_name || 'Khách hàng mới',
              subtitle: `Tòa quan tâm: ${data.building_name || 'Mặt bằng'} • SĐT: ${data.phone || '---'}`,
              target_tab: 'tab-sales-leads',
              nav_id: 'nav-item-leads'
            });

            // Tự động tải lại bảng Leads nếu đang mở tab CRM
            if (typeof loadCrmLeadsList === 'function') {
              loadCrmLeadsList();
            }
          };

          salesChannel.listen('.lead.assigned', handleLeadAssigned);
          salesChannel.listen('lead.assigned', handleLeadAssigned);
          staffChannel.listen('.lead.assigned', handleLeadAssigned);
          staffChannel.listen('lead.assigned', handleLeadAssigned);
        }
      }

      // -------------------------------------------------------------
      // 4. KÊNH QUẢN TRỊ VIÊN: ADMIN (admin-notifications)
      // -------------------------------------------------------------
      if (role === 'ADMIN' || role === 'SUPER_ADMIN') {
        const adminChannel = window.Echo.channel(adminChannelName);
        window._activeEchoChannels.push(adminChannelName);

        // Kịch bản 4: Lead không được phân phối tự động -> cảnh báo Admin phân bổ bằng tay
        adminChannel.listen('.lead.pending_assignment', function (data) {
          const alertMsg = data.message || `Khách hàng ${data.contact_full_name || ''} đang chờ phân bổ chuyên viên kinh doanh thủ công.`;
          if (typeof moToast === 'function') {
            moToast(alertMsg, 'warning', 'Cảnh báo: Lead chờ phân bổ');
          }
          playNotificationChime('warning');

          appendNotificationToTopbar({
            icon: 'fa-solid fa-triangle-exclamation',
            icon_bg: '#fef3c7',
            icon_color: '#d97706',
            badge_text: 'Cảnh báo Lead',
            badge_style: 'background:#fef3c7;color:#b45309;',
            title: `Chờ phân bổ: ${data.contact_full_name || data.company_name || 'Khách hàng'}`,
            subtitle: `Tòa quan tâm: ${data.building_name || 'Tòa nhà'} • Chưa có Sales phụ trách`,
            target_tab: 'tab-crm-leads',
            nav_id: 'nav-item-crm'
          });

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

          appendNotificationToTopbar({
            icon: 'fa-solid fa-file-contract',
            icon_bg: toastType === 'success' ? '#f0fdf4' : (toastType === 'error' ? '#fef2f2' : '#eff6ff'),
            icon_color: toastType === 'success' ? '#16a34a' : (toastType === 'error' ? '#e11d48' : '#2563eb'),
            badge_text: 'Hợp đồng • ' + action,
            badge_style: toastType === 'success' ? 'background:#f0fdf4;color:#15803d;' : 'background:#eff6ff;color:#1d4ed8;',
            title: `Hợp đồng: ${data.contract_code || 'HĐ'} (${action})`,
            subtitle: data.message || 'Cập nhật hợp đồng',
            target_tab: 'tab-admin-contracts',
            nav_id: 'nav-item-contracts'
          });

          if (typeof loadContractsList === 'function') {
            loadContractsList();
          }
        });
      }
    }

    // Tự động kích hoạt khi trang tải xong
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', function () {
        initRealtimeNotifications();
      });
    } else {
      initRealtimeNotifications();
    }
  })();
</script>
