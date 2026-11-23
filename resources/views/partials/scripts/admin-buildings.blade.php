<script>
    /* ==========================================================================
       MODULE 1: REAL ESTATE ASSETS & BUILDINGS MANAGEMENT (ENDPOINTS 5 - 22)
       ========================================================================== */
    let adminBuildingsList = [];
    let currentAdminBuildingSearch = '';
    let currentAdminBuildingGrade = 'ALL';
    let currentActiveBuildingId = null;
    let currentActiveUnitBuildingId = null;
    let currentAdminBuildingPage = 1;
    let adminBuildingPageSize = 10;
    let adminBuildingPaginationMeta = {};

    let currentUnitPage = 1;
    let unitPageSize = 5;
    let unitPaginationMeta = {};

    // 1. Nạp danh sách tòa nhà quản trị: GET /api/v1/admin/buildings
    async function loadAdminBuildingsList(page = null) {
      if (page !== null && page !== undefined) {
        currentAdminBuildingPage = page;
      }
      const tbody = document.getElementById('admin-buildings-tbody');
      if (tbody) {
        tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;padding:24px;color:#94a3b8;"><i class="fa-solid fa-spinner fa-spin"></i> Đang nạp danh sách cao ốc văn phòng từ API...</td></tr>';
      }

      try {
        let url = `/api/v1/admin/buildings?page=${currentAdminBuildingPage}&per_page=${adminBuildingPageSize}`;
        if (currentAdminBuildingSearch) {
          url += `&search=${encodeURIComponent(currentAdminBuildingSearch)}`;
        }
        if (currentAdminBuildingGrade && currentAdminBuildingGrade !== 'ALL') {
          url += `&grade=${encodeURIComponent(currentAdminBuildingGrade)}`;
        }

        const res = await fetch(url, {
          method: 'GET',
          headers: getAuthHeaders()
        });

        if (res.status === 401) {
          if (tbody) tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;color:#ef4444;padding:20px;">Vui lòng đăng nhập với tài khoản Quản trị viên (Admin/PM).</td></tr>';
          return;
        }

        const json = await res.json();
        if (res.ok) {
          adminBuildingsList = Array.isArray(json.data) ? json.data : (json.data?.data || []);
          adminBuildingPaginationMeta = json.meta || json.data?.meta || {
            current_page: currentAdminBuildingPage,
            last_page: 1,
            per_page: adminBuildingPageSize,
            total: adminBuildingsList.length
          };
          window.BUILDINGS_DATA = adminBuildingsList;
          renderAdminBuildingsTable();
        } else {
          if (tbody) tbody.innerHTML = `<tr><td colspan="7" style="text-align:center;color:#ef4444;padding:20px;">Lỗi: ${escapeHtml(json.message || 'Không thể tải danh sách tòa nhà')}</td></tr>`;
        }
      } catch (err) {
        if (tbody) tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;color:#ef4444;padding:20px;">Lỗi kết nối máy chủ API Tòa nhà.</td></tr>';
      }
    }

    // 2. Render bảng danh sách tòa nhà
    function renderAdminBuildingsTable() {
      const tbody = document.getElementById('admin-buildings-tbody');
      const totalCount = (adminBuildingPaginationMeta.total !== undefined) ? adminBuildingPaginationMeta.total : adminBuildingsList.length;
      const countEl = document.getElementById('admin-buildings-total-count');
      if (countEl) countEl.innerText = totalCount;

      const navBadge = document.getElementById('nav-badge-buildings');
      if (navBadge) navBadge.innerText = totalCount;

      const effectiveRole = window.currentUiRole || String(localStorage.getItem('auth_role') || 'admin').toLowerCase();
      const isAdmin = (effectiveRole === 'admin' || effectiveRole === 'super_admin');
      const isPm = (effectiveRole === 'pm' || effectiveRole === 'property_manager');
      const isSales = (effectiveRole === 'sales' || effectiveRole === 'sale');

      if (isPm) {
        const totalBldsEl = document.getElementById('dashboard-total-blds');
        if (totalBldsEl) totalBldsEl.innerText = totalCount;
        const totalBldsSub = document.getElementById('dashboard-total-blds-sub');
        if (totalBldsSub) totalBldsSub.innerText = 'tòa phụ trách';
        const card1FootVal = document.getElementById('dashboard-card1-foot-val');
        if (card1FootVal) card1FootVal.innerText = `${totalCount}/${totalCount} cao ốc`;

        const totalUnitsCount = adminBuildingsList.reduce((acc, b) => acc + (parseInt(b.units_count) || 0), 0) || (adminBuildingsList.length * 3);
        const totalUnitsSub = document.getElementById('dashboard-total-units-sub');
        if (totalUnitsSub) totalUnitsSub.innerText = `${totalUnitsCount} mặt bằng`;

        const totalAreaSum = adminBuildingsList.reduce((acc, b) => acc + (parseFloat(b.total_leasable_area_sqm) || 0), 0);
        if (totalAreaSum > 0) {
          const pmCard3Val = document.getElementById('pm-kpi-total-area');
          if (pmCard3Val) pmCard3Val.innerText = Math.round(totalAreaSum).toLocaleString('vi-VN');
          const pmCard2Foot = document.getElementById('pm-kpi-exploiting-area');
          if (pmCard2Foot) pmCard2Foot.innerText = Math.round(totalAreaSum * 0.846).toLocaleString('vi-VN') + ' m²';
          const pmCard3Foot = document.getElementById('pm-kpi-vacant-area');
          if (pmCard3Foot) pmCard3Foot.innerText = Math.round(totalAreaSum * (1 - 0.846)).toLocaleString('vi-VN') + ' m²';
        }
        const chartSub = document.getElementById('dashboard-pm-chart-sub');
        if (chartSub) chartSub.innerText = `Thống kê tiến độ phủ đầy mặt bằng tại ${totalCount} cao ốc Ban Quản Lý phụ trách`;
      }

      // Ẩn/Hiện nút Thêm tòa nhà mới và Phân công nhân sự (Chỉ Admin mới có quyền)
      const btnCreate = document.getElementById('btn-create-building');
      if (btnCreate) btnCreate.style.display = isAdmin ? 'inline-flex' : 'none';
      const btnTopAssignments = document.getElementById('btn-top-building-assignments');
      if (btnTopAssignments) btnTopAssignments.style.display = isAdmin ? 'inline-flex' : 'none';

      if (!tbody) return;
      if (adminBuildingsList.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;padding:32px;color:#94a3b8;">Không tìm thấy tòa nhà nào trong hệ thống.</td></tr>';
        renderAdminBuildingPagination(adminBuildingPaginationMeta);
        return;
      }

      tbody.innerHTML = adminBuildingsList.map(b => {
        const gradeBadge = b.grade === 'A'
          ? '<span class="badge-filament success" style="font-weight:700;">Hạng A</span>'
          : (b.grade === 'B' ? '<span class="badge-filament info" style="font-weight:700;">Hạng B</span>' : '<span class="badge-filament">Hạng C</span>');

        const address = `${escapeHtml(b.address || '')}, ${escapeHtml(b.district || 'TP.HCM')}`;
        const totalFloors = `${b.total_floors || 0} tầng (${b.basements || 0} hầm)`;
        const areaDisplay = b.formatted_area || b.totalArea || (b.total_leasable_area_sqm ? `${Number(b.total_leasable_area_sqm).toLocaleString('vi-VN')} m²` : '---');
        const pricePerSqm = b.min_rent_price_per_sqm ? `${Number(b.min_rent_price_per_sqm).toLocaleString('vi-VN')} đ/m²` : (b.priceVnd ? `${b.priceVnd}/m²` : (b.formatted_rent_price || '---'));
        const serviceFeeDisplay = b.default_service_fee_per_sqm ? `Phí QL: ${Number(b.default_service_fee_per_sqm).toLocaleString('vi-VN')} đ/m²` : (b.formatted_service_fee || 'Phí QL: Đã gồm');

        return `
          <tr>
            <td>
              <div style="font-weight:700;color:var(--mo-primary);font-size:14px;">${escapeHtml(b.name)}</div>
              <div style="font-size:11px;color:#64748b;font-family:monospace;">${escapeHtml(b.building_code || b.slug || b.id.substring(0,8))}</div>
            </td>
            <td>
              <div style="font-size:13px;color:#334155;">${address}</div>
              <div style="font-size:11px;color:#94a3b8;">${totalFloors}</div>
            </td>
            <td>${gradeBadge}</td>
            <td>
              <strong style="color:#0284c7;">${areaDisplay}</strong>
            </td>
            <td>
              <span style="font-weight:700;color:#ea580c;font-size:13px;">${pricePerSqm}</span>
              <div style="font-size:11px;color:#64748b;">${serviceFeeDisplay}</div>
            </td>
            <td>
              ${b.is_published
                ? '<span class="badge-filament success"><i class="fa-solid fa-earth-americas"></i> Đang hiển thị</span>'
                : '<span class="badge-filament" style="background:#f1f5f9;color:#64748b;"><i class="fa-solid fa-eye-slash"></i> Ẩn Portal</span>'}
            </td>
            <td style="text-align:right;white-space:nowrap;">
              <button class="btn-mo-outline" style="padding:4px 8px;font-size:12px;" onclick="openManageBuildingUnitsModal('${b.id}', '${escapeHtml(b.name)}')" title="Quản lý các sàn mặt bằng">
                <i class="fa-solid fa-layer-group"></i> Sàn (${b.units_count ?? (b.available_units_count ?? 'QL')})
              </button>
              <button class="btn-mo-outline" style="padding:4px 8px;font-size:12px;color:#0284c7;border-color:#bae6fd;margin-left:4px;" onclick="openBuildingMediaModal('${b.id}', '${escapeHtml(b.name)}')" title="Album ảnh tòa nhà">
                <i class="fa-solid fa-images"></i> Ảnh
              </button>
              ${isAdmin ? `
              <button class="btn-mo-outline" style="padding:4px 8px;font-size:12px;color:#2563eb;border-color:#93c5fd;background:#eff6ff;margin-left:4px;font-weight:600;" onclick="openBuildingAssignmentsFromBuilding('${b.id}', '${escapeHtml(b.name)}', '${escapeHtml(b.building_code || b.code || '')}')" title="Phân công nhân sự phụ trách tòa nhà (Sales, BQL, Kế toán)">
                <i class="fa-solid fa-user-gear"></i> Gán nhân sự
              </button>` : ''}
              ${(isAdmin || isPm) ? `
              <button class="btn-mo-outline" style="padding:4px 8px;font-size:12px;color:#334155;margin-left:4px;" onclick="openEditBuildingModal('${b.id}')" title="Sửa thông số tòa nhà">
                <i class="fa-solid fa-pen"></i>
              </button>` : ''}
              ${isAdmin ? `
              <button class="btn-mo-outline" style="padding:4px 8px;font-size:12px;color:#ef4444;border-color:#fecaca;margin-left:4px;" onclick="deleteBuilding('${b.id}', '${escapeHtml(b.name)}')" title="Xóa tòa nhà">
                <i class="fa-solid fa-trash"></i>
              </button>` : ''}
            </td>
          </tr>
        `;
      }).join('');
      renderAdminBuildingPagination(adminBuildingPaginationMeta);
    }

    // 2.1. Render thanh điều hướng phân trang danh sách tài nguyên tòa nhà & mặt bằng
    function renderAdminBuildingPagination(meta) {
      const container = document.getElementById('admin-buildings-pagination');
      if (!container) return;

      const cur = parseInt(meta.current_page) || 1;
      const last = parseInt(meta.last_page) || 1;
      const total = parseInt(meta.total) || 0;
      const perPage = parseInt(meta.per_page) || adminBuildingPageSize;

      if (total === 0) {
        container.innerHTML = '<span style="font-size:12.5px;color:#94a3b8;">Không có dữ liệu tòa nhà.</span>';
        return;
      }

      const from = Math.min((cur - 1) * perPage + 1, total);
      const to = Math.min(cur * perPage, total);

      let pageButtonsHtml = '';
      let startPage = Math.max(1, cur - 2);
      let endPage = Math.min(last, cur + 2);
      if (cur <= 2) endPage = Math.min(last, 5);
      if (cur >= last - 1) startPage = Math.max(1, last - 4);

      if (startPage > 1) {
        pageButtonsHtml += `<button type="button" class="btn-mo-outline" style="padding:4px 9px;font-size:12px;min-width:32px;background:#fff;border-radius:6px;" onclick="loadAdminBuildingsList(1)">1</button>`;
        if (startPage > 2) {
          pageButtonsHtml += `<span style="color:#94a3b8;font-size:12px;padding:0 2px;">...</span>`;
        }
      }

      for (let p = startPage; p <= endPage; p++) {
        if (p === cur) {
          pageButtonsHtml += `<button type="button" class="btn-mo-primary" style="background:#2563eb;color:#fff;padding:4px 10px;font-size:12px;font-weight:700;min-width:32px;border:none;border-radius:6px;cursor:default;">${p}</button>`;
        } else {
          pageButtonsHtml += `<button type="button" class="btn-mo-outline" style="background:#fff;padding:4px 9px;font-size:12px;min-width:32px;border-radius:6px;" onclick="loadAdminBuildingsList(${p})">${p}</button>`;
        }
      }

      if (endPage < last) {
        if (endPage < last - 1) {
          pageButtonsHtml += `<span style="color:#94a3b8;font-size:12px;padding:0 2px;">...</span>`;
        }
        pageButtonsHtml += `<button type="button" class="btn-mo-outline" style="padding:4px 9px;font-size:12px;min-width:32px;background:#fff;border-radius:6px;" onclick="loadAdminBuildingsList(${last})">${last}</button>`;
      }

      container.innerHTML = `
        <div style="font-size:13px;color:#64748b;">
          Trang <strong>${cur}</strong> / <strong>${last}</strong> &nbsp;•&nbsp; Hiển thị <strong>${from} - ${to}</strong> trong tổng số <strong>${total}</strong> tòa nhà
        </div>
        <div style="display:flex;align-items:center;gap:6px;">
          <button type="button" class="btn-mo-outline" style="padding:4px 10px;font-size:12px;background:#fff;border-radius:6px;display:inline-flex;align-items:center;gap:4px;" ${cur <= 1 ? 'disabled style="opacity:0.5;cursor:not-allowed;padding:4px 10px;font-size:12px;border-radius:6px;background:#f8fafc;"' : `onclick="loadAdminBuildingsList(${cur - 1})"`}>
            <i class="fa-solid fa-chevron-left"></i> Trước
          </button>
          <div style="display:flex;align-items:center;gap:4px;">
            ${pageButtonsHtml}
          </div>
          <button type="button" class="btn-mo-outline" style="padding:4px 10px;font-size:12px;background:#fff;border-radius:6px;display:inline-flex;align-items:center;gap:4px;" ${cur >= last ? 'disabled style="opacity:0.5;cursor:not-allowed;padding:4px 10px;font-size:12px;border-radius:6px;background:#f8fafc;"' : `onclick="loadAdminBuildingsList(${cur + 1})"`}>
            Sau <i class="fa-solid fa-chevron-right"></i>
          </button>
        </div>
      `;
    }

    // 3. Mở Modal tạo mới Tòa nhà: POST /api/v1/admin/buildings
    // Cache lưu trữ danh sách quận/huyện theo mã tỉnh (province_code => [quận 1, quận 2,...])
    const provinceDistrictsCache = {};

    // Helper: Nạp danh sách Quận/Huyện cho dropdown theo mã Tỉnh/Thành
    async function loadDistrictsForProvince(provinceCode, districtSelectId, selectedDistrict = null) {
      const select = document.getElementById(districtSelectId);
      if (!select) return;
      select.innerHTML = '<option value="">-- Đang nạp danh sách quận/huyện... --</option>';

      if (!provinceCode) {
        select.innerHTML = '<option value="">-- Vui lòng chọn Khu vực trước --</option>';
        return;
      }

      let districts = [];
      if (provinceDistrictsCache[provinceCode]) {
        districts = provinceDistrictsCache[provinceCode];
      } else {
        try {
          const res = await fetch(`/api/v1/provinces/${provinceCode}`);
          if (res.ok) {
            const json = await res.json();
            districts = json.data?.districts || json.data?.geo_details?.areas || [];
            if (districts.length > 0) {
              provinceDistrictsCache[provinceCode] = districts;
            }
          }
        } catch (err) {
          console.error('Lỗi tải danh mục quận huyện:', err);
        }
      }

      if (!districts || districts.length === 0) {
        select.innerHTML = '<option value="">-- Không tìm thấy danh sách quận/huyện --</option>';
        if (selectedDistrict) {
          const customOpt = document.createElement('option');
          customOpt.value = selectedDistrict;
          customOpt.textContent = selectedDistrict;
          customOpt.selected = true;
          select.appendChild(customOpt);
        }
        return;
      }

      select.innerHTML = '<option value="">-- Chọn Quận / Huyện --</option>';
      let foundSelected = false;
      districts.forEach(d => {
        const opt = document.createElement('option');
        opt.value = d;
        opt.textContent = d;
        if (selectedDistrict && (selectedDistrict.toLowerCase().trim() === d.toLowerCase().trim() || selectedDistrict.toLowerCase().includes(d.toLowerCase()))) {
          opt.selected = true;
          foundSelected = true;
        }
        select.appendChild(opt);
      });

      // Nếu có selectedDistrict mà không khớp danh sách chuẩn, tự tạo 1 option riêng để không làm mất dữ liệu cũ
      if (selectedDistrict && !foundSelected) {
        const customOpt = document.createElement('option');
        customOpt.value = selectedDistrict;
        customOpt.textContent = selectedDistrict;
        customOpt.selected = true;
        select.appendChild(customOpt);
      }
    }

    // Sự kiện khi thay đổi Dropdown Tỉnh/Thành phố
    async function onBuildingProvinceChange(selectEl, districtSelectId) {
      const provinceCode = selectEl.value;
      await loadDistrictsForProvince(provinceCode, districtSelectId);
    }

    // =========================================================================
    // QUẢN LÝ CHUYỂN TAB TRONG MODAL TÒA NHÀ (CREATE & EDIT)
    // =========================================================================
    window.currentBldTab_create = 1;
    window.currentBldTab_edit = 1;

    function switchBuildingModalTab(modalType, tabIndex) {
      window[`currentBldTab_${modalType}`] = tabIndex;
      for (let i = 1; i <= 4; i++) {
        const pane = document.getElementById(`tab-pane-${modalType}-bld-${i}`);
        const btn = document.getElementById(`tab-btn-${modalType}-bld-${i}`);
        if (pane) pane.style.display = (i === tabIndex) ? 'flex' : 'none';
        if (btn) {
          if (i === tabIndex) {
            btn.style.borderBottom = '3px solid var(--mo-primary)';
            btn.style.color = 'var(--mo-primary)';
            btn.style.fontWeight = '700';
            btn.style.background = '#ffffff';
          } else {
            btn.style.borderBottom = '3px solid transparent';
            btn.style.color = '#64748b';
            btn.style.fontWeight = '600';
            btn.style.background = 'none';
          }
        }
      }
    }

    // Quản lý ảnh đính kèm khi Tạo tòa nhà mới (Tab 4 - Bắt buộc)
    let selectedCreateBuildingFiles = [];
    let selectedCreateBuildingPrimaryIndex = 0;

    function handleCreateBuildingImagesSelect(input) {
      if (!input.files || input.files.length === 0) return;
      for (let i = 0; i < input.files.length; i++) {
        const file = input.files[i];
        if (file.size > 5 * 1024 * 1024) {
          moToast(`Ảnh "${file.name}" vượt quá 5MB. Vui lòng chọn ảnh dung lượng nhỏ hơn.`, 'warning', 'Tệp quá lớn');
          continue;
        }
        selectedCreateBuildingFiles.push(file);
      }
      input.value = '';
      renderCreateBuildingPreviews();
    }

    function handleCreateBuildingDrop(e) {
      e.preventDefault();
      e.currentTarget.style.borderColor = '#cbd5e1';
      if (e.dataTransfer && e.dataTransfer.files) {
        for (let i = 0; i < e.dataTransfer.files.length; i++) {
          const file = e.dataTransfer.files[i];
          if (!file.type.startsWith('image/')) continue;
          if (file.size > 5 * 1024 * 1024) {
            moToast(`Ảnh "${file.name}" vượt quá 5MB. Vui lòng chọn ảnh dung lượng nhỏ hơn.`, 'warning', 'Tệp quá lớn');
            continue;
          }
          selectedCreateBuildingFiles.push(file);
        }
        renderCreateBuildingPreviews();
      }
    }

    function removeCreateBuildingImage(index) {
      selectedCreateBuildingFiles.splice(index, 1);
      if (selectedCreateBuildingPrimaryIndex === index) {
        selectedCreateBuildingPrimaryIndex = 0;
      } else if (selectedCreateBuildingPrimaryIndex > index) {
        selectedCreateBuildingPrimaryIndex--;
      }
      renderCreateBuildingPreviews();
    }

    function setCreateBuildingPrimaryImage(index) {
      if (index < 0 || index >= selectedCreateBuildingFiles.length) return;
      selectedCreateBuildingPrimaryIndex = index;
      renderCreateBuildingPreviews();
    }
    window.setCreateBuildingPrimaryImage = setCreateBuildingPrimaryImage;

    function renderCreateBuildingPreviews() {
      const previewEl = document.getElementById('create-build-images-preview');
      if (!previewEl) return;
      if (selectedCreateBuildingFiles.length === 0) {
        selectedCreateBuildingPrimaryIndex = 0;
        previewEl.innerHTML = `
          <div id="create-build-images-empty" style="grid-column:1/-1;text-align:center;padding:36px 10px;color:#94a3b8;font-size:12.5px;">
            <i class="fa-regular fa-images" style="font-size:32px;margin-bottom:8px;display:block;color:#cbd5e1;"></i>
            Chưa có ảnh nào được chọn. Nhấn nút phía trên để tải lên hình ảnh tòa nhà.
          </div>
        `;
        return;
      }

      // Đã có ít nhất 1 ảnh được chọn -> Tự động xóa thông báo lỗi ở Tab 4 nếu có
      moClearFieldError('create-build-images-input', 'err-build-images');

      previewEl.innerHTML = selectedCreateBuildingFiles.map((file, idx) => {
        const url = URL.createObjectURL(file);
        const isPrimary = (idx === selectedCreateBuildingPrimaryIndex);
        return `
          <div style="position:relative;border-radius:8px;overflow:hidden;border:${isPrimary ? '2px solid #10b981' : '1px solid #e2e8f0'};background:#f8fafc;box-shadow:${isPrimary ? '0 0 0 2px rgba(16,185,129,0.15)' : 'none'};transition:all 0.2s;">
            <img src="${url}" style="width:100%;height:110px;object-fit:cover;display:block;cursor:${isPrimary ? 'default' : 'pointer'};" ${!isPrimary ? `onclick="setCreateBuildingPrimaryImage(${idx})" title="Nhấn để chọn ảnh này làm ảnh chính"` : 'title="Ảnh đại diện chính"'}>
            <div style="padding:4px 6px;display:flex;justify-content:space-between;align-items:center;background:#fff;border-top:1px solid #e2e8f0;">
              ${isPrimary ? '<span class="badge-filament success" style="font-size:9.5px;padding:2px 6px;display:inline-flex;align-items:center;gap:3px;" title="Ảnh đại diện chính"><i class="fa-solid fa-star"></i> Ảnh Chính</span>' : `
                <button type="button" class="btn-mo-outline" style="padding:2px 7px;font-size:11px;cursor:pointer;color:#94a3b8;border-color:#cbd5e1;background:#fff;border-radius:4px;" onclick="setCreateBuildingPrimaryImage(${idx})" title="Đặt làm ảnh chính">
                  <i class="fa-regular fa-star"></i>
                </button>
              `}
              <button type="button" class="btn-mo-outline" style="padding:2px 5px;font-size:10px;color:#ef4444;border-color:#fecaca;" onclick="removeCreateBuildingImage(${idx})" title="Xóa ảnh này">
                <i class="fa-solid fa-trash"></i>
              </button>
            </div>
          </div>
        `;
      }).join('');
    }

    async function openCreateBuildingModal() {
      const effectiveRole = window.currentUiRole || String(localStorage.getItem('auth_role') || 'admin').toLowerCase();
      if (effectiveRole !== 'admin' && effectiveRole !== 'super_admin' && effectiveRole !== 'pm') {
        moToast('Bạn không có quyền tạo mới tòa nhà theo phân quyền tài khoản.', 'error', 'Từ chối truy cập');
        return;
      }
      const modal = document.getElementById('modal-create-building');
      if (!modal) return;
      document.getElementById('form-create-building')?.reset();
      selectedCreateBuildingFiles = [];
      selectedCreateBuildingPrimaryIndex = 0;
      renderCreateBuildingPreviews();
      switchBuildingModalTab('create', 1);
      modal.style.display = 'flex';

      // Xóa toàn bộ trạng thái lỗi trước đó nếu có
      BUILDING_VALIDATION_SCHEMA.forEach(rule => {
        const { inputId, slotId } = getBuildingFieldIds('create', rule.field);
        moClearFieldError(inputId, slotId);
      });
      moClearFieldError('create-build-images-input', 'err-build-images');

      // Tỉnh / Thành phố: ban đầu chưa chọn tỉnh nào
      const provSelect = document.getElementById('build-province');
      if (provSelect) provSelect.value = '';
      const districtSelect = document.getElementById('build-district');
      if (districtSelect) districtSelect.innerHTML = '<option value="">-- Vui lòng chọn Tỉnh / TP trước --</option>';

      // Tự động tải mã tòa nhà kế tiếp từ CSDL và điền sẵn vào ô Mã Tòa Nhà
      const codeInput = document.getElementById('build-code');
      if (codeInput) {
        codeInput.value = '';
        codeInput.placeholder = 'Đang tải mã...';
        try {
          const res = await fetch('/api/v1/admin/buildings/next-code', {
            headers: getAuthHeaders()
          });
          const json = await res.json();
          if (res.ok && json.data && json.data.next_code) {
            codeInput.value = json.data.next_code;
            codeInput.placeholder = json.data.next_code;
          } else {
            codeInput.placeholder = 'VD: BLD-001 (tự sinh)';
          }
        } catch (e) {
          codeInput.placeholder = 'VD: BLD-001 (tự sinh)';
        }
      }
    }

    function closeCreateBuildingModal() {
      const modal = document.getElementById('modal-create-building');
      if (modal) modal.style.display = 'none';
      // Xóa sạch trạng thái lỗi khi đóng modal
      BUILDING_VALIDATION_SCHEMA.forEach(rule => {
        const { inputId, slotId } = getBuildingFieldIds('create', rule.field);
        moClearFieldError(inputId, slotId);
      });
      moClearFieldError('create-build-images-input', 'err-build-images');
    }

    /**
     * ==============================================================================
     * BỘ QUY TẮC & ENGINE XÁC THỰC DỮ LIỆU TÒA NHÀ (BUILDING VALIDATION ENGINE)
     * Tái sử dụng 100% BMS Validation Engine (validation.blade.php), 0ms delay, no alert()
     * ==============================================================================
     */
    const BUILDING_VALIDATION_SCHEMA = [
      // TAB 1: Thông tin & Địa giới
      {
        field: 'name',
        tab: 1,
        label: 'Tên tòa nhà',
        validate: (val) => {
          const req = moValidateRequired(val, 'Tên tòa nhà');
          if (!req.valid) return req;
          if (val.trim().length < 3) {
            return { valid: false, message: 'Tên tòa nhà phải có tối thiểu 3 ký tự.' };
          }
          return { valid: true, message: '' };
        }
      },
      {
        field: 'code',
        tab: 1,
        label: 'Mã tòa nhà',
        validate: (val) => {
          if (!val || val.trim() === '') return { valid: true, message: '' };
          if (val.trim().length > 50) {
            return { valid: false, message: 'Mã tòa nhà tối đa 50 ký tự.' };
          }
          return { valid: true, message: '' };
        }
      },
      {
        field: 'grade',
        tab: 1,
        label: 'Phân hạng',
        validate: (val) => {
          if (!val || !['A', 'B', 'C'].includes(val.toUpperCase())) {
            return { valid: false, message: 'Vui lòng chọn phân hạng tòa nhà (Hạng A, B hoặc C).' };
          }
          return { valid: true, message: '' };
        }
      },
      {
        field: 'province',
        tab: 1,
        label: 'Tỉnh / Thành phố',
        validate: (val) => {
          if (!val || val === '') {
            return { valid: false, message: 'Vui lòng chọn Tỉnh / Thành phố.' };
          }
          return { valid: true, message: '' };
        }
      },
      {
        field: 'district',
        tab: 1,
        label: 'Quận / Huyện',
        validate: (val) => {
          if (!val || val.trim() === '' || val.includes('--')) {
            return { valid: false, message: 'Vui lòng chọn Quận / Huyện.' };
          }
          return { valid: true, message: '' };
        }
      },
      {
        field: 'address',
        tab: 1,
        label: 'Địa chỉ chi tiết',
        validate: (val) => {
          const req = moValidateRequired(val, 'Địa chỉ chi tiết');
          if (!req.valid) return req;
          if (val.trim().length < 5) {
            return { valid: false, message: 'Địa chỉ chi tiết phải có tối thiểu 5 ký tự.' };
          }
          return { valid: true, message: '' };
        }
      },

      // TAB 2: Thông số kỹ thuật
      {
        field: 'floors',
        tab: 2,
        label: 'Số tầng nổi',
        validate: (val) => {
          if (val === '' || val === null || val === undefined) {
            return { valid: false, message: 'Vui lòng nhập Số tầng nổi (tối thiểu 1).' };
          }
          return moValidateMinMax(val, 1, 200, 'Số tầng nổi');
        }
      },
      {
        field: 'basements',
        tab: 2,
        label: 'Số tầng hầm',
        validate: (val) => {
          if (val === '' || val === null || val === undefined) return { valid: true, message: '' };
          return moValidateMinMax(val, 0, 20, 'Số tầng hầm');
        }
      },
      {
        field: 'net-area',
        tab: 2,
        label: 'Tổng diện tích sàn',
        validate: (val) => {
          if (val === '' || val === null || val === undefined) {
            return { valid: false, message: 'Vui lòng nhập Tổng diện tích sàn khả dụng (m²).' };
          }
          return moValidateMinMax(val, 1, null, 'Tổng diện tích sàn khả dụng');
        }
      },
      {
        field: 'ceiling-height',
        tab: 2,
        label: 'Chiều cao trần',
        validate: (val) => {
          if (val === '' || val === null || val === undefined) return { valid: true, message: '' };
          return moValidateMinMax(val, 1.0, 10.0, 'Chiều cao trần (m)');
        }
      },
      {
        field: 'elevators',
        tab: 2,
        label: 'Số thang máy',
        validate: (val) => {
          if (val === '' || val === null || val === undefined) return { valid: true, message: '' };
          return moValidateMinMax(val, 0, 100, 'Số lượng thang máy');
        }
      },

      // TAB 3: Biểu phí & Chính sách
      {
        field: 'service-fee',
        tab: 3,
        label: 'Phí quản lý dịch vụ',
        validate: (val) => {
          if (val === '' || val === null || val === undefined) {
            return { valid: false, message: 'Vui lòng nhập Phí quản lý dịch vụ (nhập 0 nếu miễn phí).' };
          }
          return moValidateMinMax(val, 0, null, 'Phí quản lý dịch vụ');
        }
      },
      {
        field: 'electric-fee',
        tab: 3,
        label: 'Đơn giá điện',
        validate: (val) => {
          if (val === '' || val === null || val === undefined) {
            return { valid: false, message: 'Vui lòng nhập Đơn giá điện (nhập 0 nếu tính theo giá nhà nước).' };
          }
          return moValidateMinMax(val, 0, null, 'Đơn giá điện');
        }
      },
      {
        field: 'water-fee',
        tab: 3,
        label: 'Đơn giá nước',
        validate: (val) => {
          if (val === '' || val === null || val === undefined) {
            return { valid: false, message: 'Vui lòng nhập Đơn giá nước (nhập 0 nếu miễn phí).' };
          }
          return moValidateMinMax(val, 0, null, 'Đơn giá nước');
        }
      },
      {
        field: 'deposit-term',
        tab: 3,
        label: 'Đặt cọc (tháng)',
        validate: (val) => {
          if (val === '' || val === null || val === undefined) return { valid: true, message: '' };
          return moValidateMinMax(val, 1, 24, 'Thời hạn đặt cọc');
        }
      },
      {
        field: 'payment-term',
        tab: 3,
        label: 'Kỳ thanh toán (tháng)',
        validate: (val) => {
          if (val === '' || val === null || val === undefined) return { valid: true, message: '' };
          return moValidateMinMax(val, 1, 12, 'Kỳ thanh toán');
        }
      }
    ];

    const SERVER_TO_CLIENT_FIELD_MAP_CREATE = {
      name: { inputId: 'build-name', slotId: 'err-build-name', tab: 1 },
      building_code: { inputId: 'build-code', slotId: 'err-build-code', tab: 1 },
      description: { inputId: 'build-description', slotId: 'err-build-description', tab: 1 },
      province_code: { inputId: 'build-province', slotId: 'err-build-province', tab: 1 },
      province: { inputId: 'build-province', slotId: 'err-build-province', tab: 1 },
      district: { inputId: 'build-district', slotId: 'err-build-district', tab: 1 },
      address: { inputId: 'build-address', slotId: 'err-build-address', tab: 1 },
      grade: { inputId: 'build-grade', slotId: 'err-build-grade', tab: 1 },
      total_floors: { inputId: 'build-floors', slotId: 'err-build-floors', tab: 2 },
      total_basements: { inputId: 'build-basements', slotId: 'err-build-basements', tab: 2 },
      total_leasable_area_sqm: { inputId: 'build-net-area', slotId: 'err-build-net-area', tab: 2 },
      ceiling_height_meters: { inputId: 'build-ceiling-height', slotId: 'err-build-ceiling-height', tab: 2 },
      elevators_count: { inputId: 'build-elevators', slotId: 'err-build-elevators', tab: 2 },
      default_service_fee_per_sqm: { inputId: 'build-service-fee', slotId: 'err-build-service-fee', tab: 3 },
      electricity_unit_price: { inputId: 'build-electric-fee', slotId: 'err-build-electric-fee', tab: 3 },
      water_unit_price: { inputId: 'build-water-fee', slotId: 'err-build-water-fee', tab: 3 },
      motorbike_parking_fee: { inputId: 'build-motor-fee', slotId: 'err-build-motor-fee', tab: 3 },
      car_parking_fee: { inputId: 'build-car-fee', slotId: 'err-build-car-fee', tab: 3 },
      overtime_fee_per_hour: { inputId: 'build-overtime-fee', slotId: 'err-build-overtime-fee', tab: 3 },
      deposit_term_months: { inputId: 'build-deposit-term', slotId: 'err-build-deposit-term', tab: 3 },
      payment_term_months: { inputId: 'build-payment-term', slotId: 'err-build-payment-term', tab: 3 },
      images: { inputId: 'create-build-images-input', slotId: 'err-build-images', tab: 4 }
    };

    const SERVER_TO_CLIENT_FIELD_MAP_EDIT = {
      name: { inputId: 'edit-build-name', slotId: 'err-edit-build-name', tab: 1 },
      building_code: { inputId: 'edit-build-code', slotId: 'err-edit-build-code', tab: 1 },
      description: { inputId: 'edit-build-description', slotId: 'err-edit-build-description', tab: 1 },
      province_code: { inputId: 'edit-build-province', slotId: 'err-edit-build-province', tab: 1 },
      province: { inputId: 'edit-build-province', slotId: 'err-edit-build-province', tab: 1 },
      district: { inputId: 'edit-build-district', slotId: 'err-edit-build-district', tab: 1 },
      address: { inputId: 'edit-build-address', slotId: 'err-edit-build-address', tab: 1 },
      grade: { inputId: 'edit-build-grade', slotId: 'err-edit-build-grade', tab: 1 },
      total_floors: { inputId: 'edit-build-floors', slotId: 'err-edit-build-floors', tab: 2 },
      total_basements: { inputId: 'edit-build-basements', slotId: 'err-edit-build-basements', tab: 2 },
      total_leasable_area_sqm: { inputId: 'edit-build-net-area', slotId: 'err-edit-build-net-area', tab: 2 },
      ceiling_height_meters: { inputId: 'edit-build-ceiling-height', slotId: 'err-edit-build-ceiling-height', tab: 2 },
      elevators_count: { inputId: 'edit-build-elevators', slotId: 'err-edit-build-elevators', tab: 2 },
      default_service_fee_per_sqm: { inputId: 'edit-build-service-fee', slotId: 'err-edit-build-service-fee', tab: 3 },
      electricity_unit_price: { inputId: 'edit-build-electric-fee', slotId: 'err-edit-build-electric-fee', tab: 3 },
      water_unit_price: { inputId: 'edit-build-water-fee', slotId: 'err-edit-build-water-fee', tab: 3 },
      motorbike_parking_fee: { inputId: 'edit-build-motor-fee', slotId: 'err-edit-build-motor-fee', tab: 3 },
      car_parking_fee: { inputId: 'edit-build-car-fee', slotId: 'err-edit-build-car-fee', tab: 3 },
      overtime_fee_per_hour: { inputId: 'edit-build-overtime-fee', slotId: 'err-edit-build-overtime-fee', tab: 3 },
      deposit_term_months: { inputId: 'edit-build-deposit-term', slotId: 'err-edit-build-deposit-term', tab: 3 },
      payment_term_months: { inputId: 'edit-build-payment-term', slotId: 'err-edit-build-payment-term', tab: 3 },
      images: { inputId: 'edit-build-images-input', slotId: 'err-edit-build-images', tab: 4 }
    };

    function getBuildingFieldIds(formType, fieldKey) {
      const prefix = formType === 'create' ? 'build-' : 'edit-build-';
      const inputId = prefix + fieldKey;
      const slotId = 'err-' + prefix + fieldKey;
      return { inputId, slotId };
    }

    function validateBuildingField(formType, fieldKey) {
      const rule = BUILDING_VALIDATION_SCHEMA.find(r => r.field === fieldKey);
      if (!rule) return true;

      const { inputId, slotId } = getBuildingFieldIds(formType, fieldKey);
      const el = document.getElementById(inputId);
      if (!el) return true;

      const val = el.value;
      const result = rule.validate(val);

      if (!result.valid) {
        moSetFieldError(inputId, slotId, result.message);
        return false;
      } else {
        moClearFieldError(inputId, slotId);
        return true;
      }
    }
    window.validateBuildingField = validateBuildingField;

    function validateBuildingForm(formType) {
      let isValid = true;
      let firstError = null;

      // 1. Quét toàn bộ các trường theo schema
      for (const rule of BUILDING_VALIDATION_SCHEMA) {
        const { inputId, slotId } = getBuildingFieldIds(formType, rule.field);
        const el = document.getElementById(inputId);
        if (!el) continue;

        const val = el.value;
        const res = rule.validate(val);
        if (!res.valid) {
          moSetFieldError(inputId, slotId, res.message);
          isValid = false;
          if (!firstError) {
            firstError = { tab: rule.tab, inputId, message: res.message };
          }
        } else {
          moClearFieldError(inputId, slotId);
        }
      }

      // 2. Kiểm tra điều kiện hình ảnh ở Tab 4
      const imageSlotId = formType === 'create' ? 'err-build-images' : 'err-edit-build-images';
      if (formType === 'create') {
        if (!selectedCreateBuildingFiles || selectedCreateBuildingFiles.length === 0) {
          const imgSlot = document.getElementById(imageSlotId);
          if (imgSlot) {
            imgSlot.innerHTML = '<span>Bắt buộc tải lên ít nhất 1 hình ảnh đại diện cho tòa nhà.</span>';
          }
          isValid = false;
          if (!firstError) {
            firstError = { tab: 4, inputId: 'create-build-images-input', message: 'Tòa nhà văn phòng bắt buộc phải có ít nhất 1 hình ảnh đại diện.' };
          }
        } else {
          const imgSlot = document.getElementById(imageSlotId);
          if (imgSlot) { imgSlot.innerHTML = ''; }
        }
      } else {
        // Edit mode
        if ((!currentEditBuildingImages || currentEditBuildingImages.length === 0)) {
          const imgSlot = document.getElementById(imageSlotId);
          if (imgSlot) {
            imgSlot.innerHTML = '<span>Tòa nhà văn phòng bắt buộc phải có ít nhất 1 hình ảnh đại diện.</span>';
          }
          isValid = false;
          if (!firstError) {
            firstError = { tab: 4, inputId: 'edit-build-images-input', message: 'Tòa nhà văn phòng bắt buộc phải có ít nhất 1 hình ảnh đại diện.' };
          }
        } else {
          const imgSlot = document.getElementById(imageSlotId);
          if (imgSlot) { imgSlot.innerHTML = ''; }
        }
      }

      // 3. Nếu có lỗi: Điều hướng Tab thông minh + Focus (Không hiện moToast popup, đồng bộ style với form báo giá)
      if (!isValid && firstError) {
        switchBuildingModalTab(formType, firstError.tab);
        const firstEl = document.getElementById(firstError.inputId);
        if (firstEl && typeof firstEl.focus === 'function') {
          firstEl.focus();
        }
      }

      return isValid;
    }
    window.validateBuildingForm = validateBuildingForm;

    async function submitCreateBuilding(e) {
      if (e) e.preventDefault();

      // 1. Kiểm tra toàn diện Client-side trước khi đóng gói gửi API
      if (!validateBuildingForm('create')) {
        return;
      }

      const provSelect = document.getElementById('build-province');
      const selectedProvOpt = provSelect?.options[provSelect.selectedIndex];
      const provinceCode = parseInt(provSelect?.value);
      const provinceName = selectedProvOpt?.getAttribute('data-name') || selectedProvOpt?.textContent;
      const district = document.getElementById('build-district').value.trim();
      const ward = document.getElementById('build-ward').value.trim();

      const payload = {
        name: document.getElementById('build-name').value.trim(),
        building_code: document.getElementById('build-code').value.trim() || undefined,
        province_code: provinceCode,
        province: provinceName,
        district: district,
        ward: ward || undefined,
        address: document.getElementById('build-address').value.trim(),
        grade: document.getElementById('build-grade').value,
        direction: document.getElementById('build-direction').value || null,
        description: document.getElementById('build-description') ? document.getElementById('build-description').value.trim() || null : null,
        total_floors: parseInt(document.getElementById('build-floors').value),
        total_basements: document.getElementById('build-basements').value !== '' ? parseInt(document.getElementById('build-basements').value) : 0,
        total_leasable_area_sqm: parseFloat(document.getElementById('build-net-area').value),
        total_net_area: parseFloat(document.getElementById('build-net-area').value),
        ceiling_height_meters: document.getElementById('build-ceiling-height').value !== '' ? parseFloat(document.getElementById('build-ceiling-height').value) : null,
        elevators_count: document.getElementById('build-elevators').value !== '' ? parseInt(document.getElementById('build-elevators').value) : null,
        air_conditioner_spec: document.getElementById('build-air-conditioner').value.trim() || null,
        backup_generator_spec: document.getElementById('build-backup-generator').value.trim() || null,
        working_hours_desc: document.getElementById('build-working-hours').value.trim() || null,
        overtime_fee_per_hour: document.getElementById('build-overtime-fee').value !== '' ? parseFloat(document.getElementById('build-overtime-fee').value) : 0,
        deposit_term_months: document.getElementById('build-deposit-term').value !== '' ? parseInt(document.getElementById('build-deposit-term').value) : null,
        payment_term_months: document.getElementById('build-payment-term').value !== '' ? parseInt(document.getElementById('build-payment-term').value) : null,
        default_service_fee_per_sqm: parseFloat(document.getElementById('build-service-fee').value),
        electricity_unit_price: parseFloat(document.getElementById('build-electric-fee').value),
        water_unit_price: parseFloat(document.getElementById('build-water-fee').value),
        motorbike_parking_fee: document.getElementById('build-motor-fee').value !== '' ? parseFloat(document.getElementById('build-motor-fee').value) : 0,
        car_parking_fee: document.getElementById('build-car-fee').value !== '' ? parseFloat(document.getElementById('build-car-fee').value) : 0,
        is_published: document.getElementById('build-published').checked
      };

      // Đóng gói FormData gửi kèm hình ảnh tải lên
      const formData = new FormData();
      for (const [k, v] of Object.entries(payload)) {
        if (v !== undefined && v !== null) {
          if (k === 'is_published') {
            formData.append(k, v ? '1' : '0');
          } else {
            formData.append(k, v);
          }
        }
      }
      formData.append('primary_image_index', selectedCreateBuildingPrimaryIndex);
      for (let i = 0; i < selectedCreateBuildingFiles.length; i++) {
        formData.append('images[]', selectedCreateBuildingFiles[i]);
      }

      try {
        const res = await fetch('/api/v1/admin/buildings', {
          method: 'POST',
          headers: getAuthHeaders(true),
          body: formData
        });

        const json = await res.json();
        if (res.ok) {
          moToast('Tạo tòa nhà văn phòng mới và tải lên hình ảnh thành công!', 'success', 'Thành công');
          closeCreateBuildingModal();
          loadAdminBuildingsList();
        } else {
          // Ánh xạ lỗi 422 từ Laravel Server: hiển thị dòng đỏ dưới input, không popup toast
          if (json.errors) {
            moMapServerErrors(json.errors, SERVER_TO_CLIENT_FIELD_MAP_CREATE);
            const firstErrKey = Object.keys(json.errors)[0];
            const mapItem = SERVER_TO_CLIENT_FIELD_MAP_CREATE[firstErrKey];
            if (mapItem && mapItem.tab) {
              switchBuildingModalTab('create', mapItem.tab);
              const el = document.getElementById(mapItem.inputId);
              if (el && typeof el.focus === 'function') el.focus();
            }
          } else {
            moToast(json.message || 'Lỗi tạo tòa nhà.', 'error', 'Lỗi hệ thống');
          }
        }
      } catch (err) {
        moToast('Lỗi kết nối API Tòa nhà.', 'error', 'Lỗi kết nối');
      }
    }

    // 4. Mở Modal sửa Tòa nhà: PUT /api/v1/admin/buildings/{id}
    let currentEditBuildingId = null;
    let currentEditBuildingImages = [];

    function renderEditBuildingImages(images) {
      const countEl = document.getElementById('edit-bld-images-count');
      if (countEl) countEl.innerText = images.length;

      const gridEl = document.getElementById('edit-build-images-grid');
      if (!gridEl) return;

      if (!images || images.length === 0) {
        gridEl.innerHTML = `
          <div style="grid-column:1/-1;text-align:center;padding:36px 10px;color:#ef4444;font-size:12.5px;">
            <i class="fa-solid fa-triangle-exclamation" style="font-size:30px;margin-bottom:8px;display:block;"></i>
            Tòa nhà hiện chưa có ảnh nào! Bắt buộc phải có ít nhất 1 ảnh đại diện. Vui lòng nhấn nút "Tải Thêm Ảnh" phía trên.
          </div>
        `;
        return;
      }

      gridEl.innerHTML = images.map(img => `
        <div style="position:relative;border-radius:8px;overflow:hidden;border:${img.is_primary ? '2px solid #10b981' : '1px solid #e2e8f0'};background:#f8fafc;box-shadow:${img.is_primary ? '0 0 0 2px rgba(16,185,129,0.15)' : 'none'};transition:all 0.2s;">
          <img src="${escapeHtml(img.image_url)}" style="width:100%;height:110px;object-fit:cover;display:block;cursor:${img.is_primary ? 'default' : 'pointer'};" ${!img.is_primary ? `onclick="setEditBuildingPrimaryImage('${img.id}')" title="Nhấn để chọn ảnh này làm ảnh đại diện chính"` : 'title="Ảnh đại diện chính"'}>
          <div style="padding:4px 6px;display:flex;justify-content:space-between;align-items:center;background:#fff;border-top:1px solid #e2e8f0;">
            ${img.is_primary ? '<span class="badge-filament success" style="font-size:9.5px;padding:2px 6px;display:inline-flex;align-items:center;gap:3px;" title="Ảnh đại diện chính"><i class="fa-solid fa-star"></i> Ảnh Chính</span>' : `
              <button type="button" class="btn-mo-outline" style="padding:2px 7px;font-size:11px;cursor:pointer;color:#94a3b8;border-color:#cbd5e1;background:#fff;border-radius:4px;" onclick="setEditBuildingPrimaryImage('${img.id}')" title="Đặt làm ảnh chính">
                <i class="fa-regular fa-star"></i>
              </button>
            `}
            <button type="button" class="btn-mo-outline" style="padding:2px 6px;font-size:10px;color:#ef4444;border-color:#fecaca;" onclick="deleteEditBuildingImage('${img.id}')" title="Xóa ảnh này">
              <i class="fa-solid fa-trash"></i>
            </button>
          </div>
        </div>
      `).join('');
    }

    async function handleEditUploadBuildingImages(input) {
      if (!input.files || input.files.length === 0) return;
      if (!currentEditBuildingId) return;

      const formData = new FormData();
      for (let i = 0; i < input.files.length; i++) {
        const file = input.files[i];
        if (file.size > 5 * 1024 * 1024) {
          moToast(`Ảnh "${file.name}" vượt quá 5MB. Vui lòng chọn ảnh dung lượng nhỏ hơn.`, 'warning', 'Tệp quá lớn');
          continue;
        }
        formData.append('images[]', file);
      }
      input.value = '';

      try {
        const res = await fetch(`/api/v1/admin/buildings/${currentEditBuildingId}/images`, {
          method: 'POST',
          headers: getAuthHeaders(true),
          body: formData
        });
        const json = await res.json();
        if (res.ok) {
          moToast('Tải lên hình ảnh tòa nhà thành công!', 'success', 'Thành công');
          await reloadEditBuildingImages();
          loadAdminBuildingsList();
        } else {
          moToast('Lỗi tải ảnh: ' + (json.message || ''), 'error', 'Tải ảnh thất bại');
        }
      } catch (e) {
        moToast('Lỗi kết nối tải ảnh lên Supabase Storage.', 'error', 'Lỗi kết nối');
      }
    }

    async function reloadEditBuildingImages() {
      if (!currentEditBuildingId) return;
      try {
        const res = await fetch(`/api/v1/admin/buildings/${currentEditBuildingId}`, {
          method: 'GET',
          headers: getAuthHeaders()
        });
        const json = await res.json();
        if (res.ok && json.data) {
          currentEditBuildingImages = json.data.images || [];
          renderEditBuildingImages(currentEditBuildingImages);
        }
      } catch (e) {
        console.error('Error reloading building images:', e);
      }
    }

    async function setEditBuildingPrimaryImage(imageId) {
      if (!currentEditBuildingId) return;
      try {
        const res = await fetch(`/api/v1/admin/buildings/${currentEditBuildingId}/images/${imageId}/set-primary`, {
          method: 'PATCH',
          headers: getAuthHeaders()
        });
        const json = await res.json();
        if (res.ok) {
          await reloadEditBuildingImages();
          loadAdminBuildingsList();
        } else {
          moToast('Lỗi đặt ảnh chính: ' + (json.message || ''), 'error', 'Thao tác thất bại');
        }
      } catch (e) {
        moToast('Lỗi kết nối đặt ảnh chính.', 'error', 'Lỗi kết nối');
      }
    }

    async function deleteEditBuildingImage(imageId) {
      if (currentEditBuildingImages.length <= 1) {
        moToast('Tòa nhà phải có ít nhất 1 hình ảnh đại diện, không thể xóa hình ảnh cuối cùng.', 'warning', 'Không thể xóa');
        return;
      }
      try {
        const res = await fetch(`/api/v1/admin/buildings/${currentEditBuildingId}/images/${imageId}`, {
          method: 'DELETE',
          headers: getAuthHeaders()
        });
        const json = await res.json();
        if (res.ok) {
          moToast('Xóa ảnh khỏi thư viện thành công.', 'success', 'Thành công');
          await reloadEditBuildingImages();
          loadAdminBuildingsList();
        } else {
          moToast('Lỗi xóa ảnh: ' + (json.message || ''), 'error', 'Xóa ảnh thất bại');
        }
      } catch (e) {
        moToast('Lỗi kết nối khi xóa ảnh.', 'error', 'Lỗi kết nối');
      }
    }

    async function openEditBuildingModal(buildingId) {
      currentEditBuildingId = buildingId;
      const modal = document.getElementById('modal-edit-building');
      if (!modal) return;
      switchBuildingModalTab('edit', 1);
      modal.style.display = 'flex';

      // Xóa sạch trạng thái lỗi trước đó nếu có
      BUILDING_VALIDATION_SCHEMA.forEach(rule => {
        const { inputId, slotId } = getBuildingFieldIds('edit', rule.field);
        moClearFieldError(inputId, slotId);
      });
      moClearFieldError('edit-build-images-input', 'err-edit-build-images');

      try {
        const res = await fetch(`/api/v1/admin/buildings/${buildingId}`, {
          method: 'GET',
          headers: getAuthHeaders()
        });
        const json = await res.json();
        if (!res.ok) {
          moToast('Không thể tải thông tin tòa nhà: ' + (json.message || ''), 'error', 'Lỗi tải dữ liệu');
          closeEditBuildingModal();
          return;
        }

        const b = json.data;
        document.getElementById('edit-build-name').value = b.name || '';
        document.getElementById('edit-build-code').value = b.building_code || '';
        document.getElementById('edit-build-grade').value = b.grade || 'A';
        document.getElementById('edit-build-direction').value = b.direction || '';
        document.getElementById('edit-build-ward').value = b.ward || '';
        document.getElementById('edit-build-address').value = b.address || '';
        if (document.getElementById('edit-build-description')) {
          document.getElementById('edit-build-description').value = b.description || '';
        }
        document.getElementById('edit-build-floors').value = b.total_floors || 1;
        document.getElementById('edit-build-basements').value = (b.total_basements !== undefined && b.total_basements !== null) ? b.total_basements : (b.basements || 0);
        document.getElementById('edit-build-net-area').value = b.total_leasable_area_sqm || b.total_net_area || '';
        document.getElementById('edit-build-ceiling-height').value = b.ceiling_height_meters || 1;
        document.getElementById('edit-build-elevators').value = (b.elevators_count !== undefined && b.elevators_count !== null) ? b.elevators_count : 0;
        document.getElementById('edit-build-air-conditioner').value = b.air_conditioner_spec || '';
        document.getElementById('edit-build-backup-generator').value = b.backup_generator_spec || '';
        document.getElementById('edit-build-working-hours').value = b.working_hours_desc || '';
        document.getElementById('edit-build-overtime-fee').value = b.overtime_fee_per_hour !== undefined ? b.overtime_fee_per_hour : 0;
        document.getElementById('edit-build-deposit-term').value = b.deposit_term_months || 1;
        document.getElementById('edit-build-payment-term').value = b.payment_term_months || 1;
        document.getElementById('edit-build-service-fee').value = b.default_service_fee_per_sqm !== undefined ? b.default_service_fee_per_sqm : 0;
        document.getElementById('edit-build-electric-fee').value = b.electricity_unit_price !== undefined ? b.electricity_unit_price : 0;
        document.getElementById('edit-build-water-fee').value = b.water_unit_price !== undefined ? b.water_unit_price : 0;
        document.getElementById('edit-build-motor-fee').value = b.motorbike_parking_fee !== undefined ? b.motorbike_parking_fee : 0;
        document.getElementById('edit-build-car-fee').value = b.car_parking_fee !== undefined ? b.car_parking_fee : 0;

        const priceRangeEl = document.getElementById('edit-build-price-range');
        if (priceRangeEl) {
          const minP = b.min_rent_price_per_sqm ? Number(b.min_rent_price_per_sqm).toLocaleString('vi-VN') : '0';
          const maxP = b.max_rent_price_per_sqm ? Number(b.max_rent_price_per_sqm).toLocaleString('vi-VN') : '0';
          if ((parseFloat(b.min_rent_price_per_sqm) || 0) > 0 || (parseFloat(b.max_rent_price_per_sqm) || 0) > 0) {
            priceRangeEl.value = `${minP} - ${maxP} đ/m²`;
          } else {
            priceRangeEl.value = 'Tự động cập nhật theo mặt bằng';
          }
        }
        document.getElementById('edit-build-published').checked = Boolean(b.is_published);

        // Nạp và chọn đúng Tỉnh/Thành phố cùng Quận/Huyện của tòa nhà đang sửa
        const editProvSelect = document.getElementById('edit-build-province');
        const bProvinceCode = b.province_code || (b.province && b.province.includes('Hà Nội') ? 1 : 79);
        if (editProvSelect) editProvSelect.value = String(bProvinceCode);
        await loadDistrictsForProvince(bProvinceCode, 'edit-build-district', b.district || 'Quận 1');

        // Nạp và hiển thị thư viện ảnh vào Tab 4
        currentEditBuildingImages = b.images || [];
        renderEditBuildingImages(currentEditBuildingImages);
      } catch (e) {
        moToast('Lỗi kết nối khi tải chi tiết tòa nhà.', 'error', 'Lỗi kết nối');
        closeEditBuildingModal();
      }
    }

    function closeEditBuildingModal() {
      const modal = document.getElementById('modal-edit-building');
      if (modal) modal.style.display = 'none';
      // Xóa sạch trạng thái lỗi khi đóng modal
      BUILDING_VALIDATION_SCHEMA.forEach(rule => {
        const { inputId, slotId } = getBuildingFieldIds('edit', rule.field);
        moClearFieldError(inputId, slotId);
      });
      moClearFieldError('edit-build-images-input', 'err-edit-build-images');
      currentEditBuildingId = null;
    }

    async function submitEditBuilding(e) {
      if (e) e.preventDefault();
      if (!currentEditBuildingId) return;

      // 1. Kiểm tra toàn diện Client-side trước khi gửi API
      if (!validateBuildingForm('edit')) {
        return;
      }

      const editProvSelect = document.getElementById('edit-build-province');
      const editProvOpt = editProvSelect?.options[editProvSelect.selectedIndex];
      const editProvinceCode = parseInt(editProvSelect?.value);
      const editProvinceName = editProvOpt?.getAttribute('data-name') || editProvOpt?.textContent;
      const editDistrict = document.getElementById('edit-build-district').value.trim();
      const editWard = document.getElementById('edit-build-ward').value.trim();

      const payload = {
        name: document.getElementById('edit-build-name').value.trim(),
        building_code: document.getElementById('edit-build-code').value.trim() || undefined,
        province_code: editProvinceCode,
        province: editProvinceName,
        district: editDistrict,
        ward: editWard || undefined,
        address: document.getElementById('edit-build-address').value.trim(),
        grade: document.getElementById('edit-build-grade').value,
        direction: document.getElementById('edit-build-direction').value || null,
        description: document.getElementById('edit-build-description') ? document.getElementById('edit-build-description').value.trim() || null : null,
        total_floors: parseInt(document.getElementById('edit-build-floors').value),
        total_basements: document.getElementById('edit-build-basements').value !== '' ? parseInt(document.getElementById('edit-build-basements').value) : 0,
        total_leasable_area_sqm: parseFloat(document.getElementById('edit-build-net-area').value),
        total_net_area: parseFloat(document.getElementById('edit-build-net-area').value),
        ceiling_height_meters: document.getElementById('edit-build-ceiling-height').value !== '' ? parseFloat(document.getElementById('edit-build-ceiling-height').value) : null,
        elevators_count: document.getElementById('edit-build-elevators').value !== '' ? parseInt(document.getElementById('edit-build-elevators').value) : null,
        air_conditioner_spec: document.getElementById('edit-build-air-conditioner').value.trim() || null,
        backup_generator_spec: document.getElementById('edit-build-backup-generator').value.trim() || null,
        working_hours_desc: document.getElementById('edit-build-working-hours').value.trim() || null,
        overtime_fee_per_hour: document.getElementById('edit-build-overtime-fee').value !== '' ? parseFloat(document.getElementById('edit-build-overtime-fee').value) : 0,
        deposit_term_months: document.getElementById('edit-build-deposit-term').value !== '' ? parseInt(document.getElementById('edit-build-deposit-term').value) : null,
        payment_term_months: document.getElementById('edit-build-payment-term').value !== '' ? parseInt(document.getElementById('edit-build-payment-term').value) : null,
        default_service_fee_per_sqm: parseFloat(document.getElementById('edit-build-service-fee').value),
        electricity_unit_price: parseFloat(document.getElementById('edit-build-electric-fee').value),
        water_unit_price: parseFloat(document.getElementById('edit-build-water-fee').value),
        motorbike_parking_fee: document.getElementById('edit-build-motor-fee').value !== '' ? parseFloat(document.getElementById('edit-build-motor-fee').value) : 0,
        car_parking_fee: document.getElementById('edit-build-car-fee').value !== '' ? parseFloat(document.getElementById('edit-build-car-fee').value) : 0,
        is_published: document.getElementById('edit-build-published').checked
      };

      try {
        const res = await fetch(`/api/v1/admin/buildings/${currentEditBuildingId}`, {
          method: 'PUT',
          headers: getAuthHeaders(),
          body: JSON.stringify(payload)
        });

        const json = await res.json();
        if (res.ok) {
          moToast('Cập nhật thông tin tòa nhà thành công!', 'success', 'Thành công');
          closeEditBuildingModal();
          loadAdminBuildingsList();
        } else {
          // Xử lý lỗi trả về từ Laravel Backend (422): hiển thị dòng đỏ dưới input, không popup toast
          if (json.errors) {
            moMapServerErrors(json.errors, SERVER_TO_CLIENT_FIELD_MAP_EDIT);
            const firstErrKey = Object.keys(json.errors)[0];
            const mapItem = SERVER_TO_CLIENT_FIELD_MAP_EDIT[firstErrKey];
            if (mapItem && mapItem.tab) {
              switchBuildingModalTab('edit', mapItem.tab);
              const el = document.getElementById(mapItem.inputId);
              if (el && typeof el.focus === 'function') el.focus();
            }
          } else {
            moToast(json.message || 'Lỗi cập nhật tòa nhà.', 'error', 'Lỗi hệ thống');
          }
        }
      } catch (e) {
        moToast('Lỗi kết nối cập nhật tòa nhà.', 'error', 'Lỗi kết nối');
      }
    }

    // 5. Xóa mềm tòa nhà: DELETE /api/v1/admin/buildings/{id}
    async function deleteBuilding(buildingId, buildingName) {
      const bld = (adminBuildingsList || []).find(b => String(b.id) === String(buildingId))
        || ((typeof BUILDINGS_DATA !== 'undefined' && Array.isArray(BUILDINGS_DATA)) ? BUILDINGS_DATA.find(b => String(b.id) === String(buildingId)) : null);
      const bldName = (bld && bld.name) ? bld.name : (buildingName || 'Tòa nhà');

      moConfirmAction({
        title: 'Xác Nhận Xóa Tòa Nhà',
        targetName: bldName,
        message: '',
        note: 'Bắt buộc toàn bộ các sàn mặt bằng không còn hợp đồng hiệu lực. Hành động này sẽ chuyển trạng thái tòa nhà sang đã xóa mềm.',
        confirmText: 'Xóa',
        type: 'danger',
        action: async () => {
          const res = await fetch(`/api/v1/admin/buildings/${buildingId}`, {
            method: 'DELETE',
            headers: getAuthHeaders()
          });
          const json = await res.json();
          if (!res.ok) {
            throw new Error(json.message || 'Không thể xóa tòa nhà.');
          }
          return json.message || `Đã xóa tòa nhà [${bldName}] thành công.`;
        },
        onSuccess: () => {
          loadAdminBuildingsList();
        }
      });
    }

    // 6. Quản lý Album ảnh Tòa nhà (Supabase Storage): POST/DELETE /images & PATCH /set-primary
    async function openBuildingMediaModal(buildingId, buildingName) {
      currentActiveBuildingId = buildingId;
      const modal = document.getElementById('modal-building-media');
      if (!modal) return;
      const mediaBldName = document.getElementById('media-building-name');
      if (mediaBldName) mediaBldName.innerText = buildingName;
      modal.style.display = 'flex';
      loadBuildingMedia(buildingId);
    }

    function closeBuildingMediaModal() {
      const modal = document.getElementById('modal-building-media');
      if (modal) modal.style.display = 'none';
      currentActiveBuildingId = null;
    }

    async function loadBuildingMedia(buildingId) {
      const container = document.getElementById('building-images-grid');
      if (!container) return;
      container.innerHTML = '<div style="text-align:center;padding:24px;color:#94a3b8;"><i class="fa-solid fa-spinner fa-spin"></i> Đang tải album ảnh...</div>';

      try {
        const res = await fetch(`/api/v1/admin/buildings/${buildingId}`, {
          method: 'GET',
          headers: getAuthHeaders()
        });
        const json = await res.json();
        const images = json.data?.images || [];
        if (images.length === 0) {
          container.innerHTML = '<div style="text-align:center;padding:32px;color:#94a3b8;grid-column:1/-1;">Tòa nhà chưa có ảnh nào. Hãy tải lên ảnh mặt tiền hoặc sảnh.</div>';
          return;
        }

        container.innerHTML = images.map(img => `
          <div style="position:relative;border-radius:8px;overflow:hidden;border:${img.is_primary ? '2px solid #10b981' : '1px solid #e2e8f0'};background:#f8fafc;box-shadow:${img.is_primary ? '0 0 0 2px rgba(16,185,129,0.15)' : 'none'};transition:all 0.2s;">
            <img src="${escapeHtml(img.image_url)}" style="width:100%;height:130px;object-fit:cover;display:block;cursor:${img.is_primary ? 'default' : 'pointer'};" ${!img.is_primary ? `onclick="setBuildingPrimaryImage('${buildingId}', '${img.id}')" title="Nhấn để chọn ảnh này làm ảnh chính"` : 'title="Ảnh đại diện chính"'}>
            <div style="padding:6px;display:flex;justify-content:space-between;align-items:center;background:#fff;border-top:1px solid #e2e8f0;">
              ${img.is_primary ? '<span class="badge-filament success" style="font-size:10px;"><i class="fa-solid fa-star"></i> Ảnh Chính</span>' : `
                <button class="btn-mo-outline" style="padding:2px 7px;font-size:11px;cursor:pointer;color:#94a3b8;border-color:#cbd5e1;background:#fff;border-radius:4px;" onclick="setBuildingPrimaryImage('${buildingId}', '${img.id}')" title="Đặt làm ảnh chính"><i class="fa-regular fa-star"></i></button>
              `}
              <button class="btn-mo-outline" style="padding:2px 6px;font-size:10px;color:#ef4444;border-color:#fecaca;" onclick="deleteBuildingImage('${buildingId}', '${img.id}')" title="Xóa ảnh này">
                <i class="fa-solid fa-trash"></i>
              </button>
            </div>
          </div>
        `).join('');
      } catch (e) {
        container.innerHTML = '<div style="color:#ef4444;text-align:center;">Lỗi kết nối tải album ảnh.</div>';
      }
    }

    async function submitUploadBuildingImages(e) {
      if (e) e.preventDefault();
      if (!currentActiveBuildingId) return;

      const fileInput = document.getElementById('building-images-input');
      if (!fileInput || !fileInput.files || fileInput.files.length === 0) {
        moToast('Vui lòng chọn ít nhất 1 ảnh (PNG, JPG).', 'warning', 'Chưa chọn tệp');
        return;
      }

      const formData = new FormData();
      for (let i = 0; i < fileInput.files.length; i++) {
        formData.append('images[]', fileInput.files[i]);
      }

      try {
        const res = await fetch(`/api/v1/admin/buildings/${currentActiveBuildingId}/images`, {
          method: 'POST',
          headers: getAuthHeaders(true),
          body: formData
        });

        const json = await res.json();
        if (res.ok) {
          moToast('Tải ảnh tòa nhà lên Supabase Storage thành công!', 'success', 'Thành công');
          fileInput.value = '';
          loadBuildingMedia(currentActiveBuildingId);
        } else {
          moToast('Lỗi tải ảnh: ' + (json.message || ''), 'error', 'Tải ảnh thất bại');
        }
      } catch (err) {
        moToast('Lỗi kết nối tải ảnh.', 'error', 'Lỗi kết nối');
      }
    }

    async function deleteBuildingImage(buildingId, imageId) {
      try {
        const res = await fetch(`/api/v1/admin/buildings/${buildingId}/images/${imageId}`, {
          method: 'DELETE',
          headers: getAuthHeaders()
        });
        const json = await res.json();
        if (res.ok) {
          moToast('Xóa ảnh tòa nhà thành công.', 'success', 'Thành công');
          loadBuildingMedia(buildingId);
          loadAdminBuildingsList();
        } else {
          moToast('Lỗi xóa ảnh: ' + (json.message || ''), 'error', 'Xóa ảnh thất bại');
        }
      } catch (e) {
        moToast('Lỗi kết nối xóa ảnh.', 'error', 'Lỗi kết nối');
      }
    }

    async function setBuildingPrimaryImage(buildingId, imageId) {
      try {
        const res = await fetch(`/api/v1/admin/buildings/${buildingId}/images/${imageId}/set-primary`, {
          method: 'PATCH',
          headers: getAuthHeaders()
        });
        const json = await res.json();
        if (res.ok) {
          loadBuildingMedia(buildingId);
        } else {
          moToast('Lỗi đặt ảnh chính: ' + (json.message || ''), 'error', 'Thao tác thất bại');
        }
      } catch (e) {
        moToast('Lỗi kết nối đặt ảnh chính.', 'error', 'Lỗi kết nối');
      }
    }

    // 7. Quản lý Mặt Bằng / Diện Tích Cắt Thuê (Units): GET/POST/PUT/DELETE /admin/units
    async function openManageBuildingUnitsModal(buildingId, buildingName) {
      currentActiveUnitBuildingId = buildingId;
      currentUnitPage = 1;
      const modal = document.getElementById('modal-manage-units');
      if (!modal) return;
      const unitsBldName = document.getElementById('units-modal-building-name');
      if (unitsBldName) unitsBldName.innerText = buildingName;
      modal.style.display = 'flex';
      loadBuildingUnits(buildingId, 1);
    }

    function closeManageBuildingUnitsModal() {
      const modal = document.getElementById('modal-manage-units');
      if (modal) modal.style.display = 'none';
      currentActiveUnitBuildingId = null;
      currentUnitPage = 1;
      const paginationEl = document.getElementById('building-units-pagination');
      if (paginationEl) paginationEl.innerHTML = '';
    }

    async function loadBuildingUnits(buildingId, page = null) {
      if (page !== null && page !== undefined) {
        currentUnitPage = page;
      }
      const tbody = document.getElementById('building-units-tbody');
      const paginationEl = document.getElementById('building-units-pagination');
      if (!tbody) return;
      tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;padding:20px;color:#94a3b8;"><i class="fa-solid fa-spinner fa-spin"></i> Đang nạp danh sách mặt bằng...</td></tr>';

      try {
        const res = await fetch(`/api/v1/admin/units?building_id=${buildingId}&page=${currentUnitPage}&per_page=${unitPageSize}`, {
          method: 'GET',
          headers: getAuthHeaders()
        });
        const json = await res.json();
        const units = Array.isArray(json.data) ? json.data : (json.data?.data || []);
        unitPaginationMeta = json.meta || json.data?.meta || {
          current_page: currentUnitPage,
          last_page: 1,
          per_page: unitPageSize,
          total: units.length
        };

        if (units.length === 0) {
          tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;padding:24px;color:#94a3b8;">Tòa nhà chưa có mặt bằng sàn nào được tạo.</td></tr>';
          if (paginationEl) paginationEl.innerHTML = '';
          return;
        }

        tbody.innerHTML = units.map(u => {
          let statusBadge = '<span class="badge-filament success">Đang trống (AVAILABLE)</span>';
          if (u.status === 'RESERVED' || u.status === 'EXPIRING_SOON') {
            statusBadge = `<span class="badge-filament warning">${u.status === 'EXPIRING_SOON' ? 'Sắp hết hạn' : 'Giữ chỗ'} (${u.status})</span>`;
          } else if (u.status === 'LEASED' || u.status === 'OCCUPIED') {
            statusBadge = '<span class="badge-filament info">Đang cho thuê (LEASED)</span>';
          } else if (u.status === 'UNDER_MAINTENANCE' || u.status === 'MAINTENANCE') {
            statusBadge = '<span class="badge-filament danger">Đang bảo trì</span>';
          }

          return `
            <tr>
              <td>
                <strong style="color:var(--mo-primary);font-size:13px;">${escapeHtml(u.unit_code)}</strong>
                ${u.name ? `<div style="font-size:11px;color:#64748b;">${escapeHtml(u.name)}</div>` : ''}
              </td>
              <td>Tầng ${escapeHtml(String(u.floor_number ?? '---'))}</td>
              <td><strong>${escapeHtml(u.formatted_area || `${u.area || 0} m²`)}</strong></td>
              <td><span style="color:#ea580c;font-weight:700;">${escapeHtml(u.formatted_rent_price || '---')}</span></td>
              <td>${statusBadge}</td>
              <td style="text-align:right;white-space:nowrap;">
                ${u.status === 'AVAILABLE' ? `
                  <button class="btn-mo-outline" style="padding:2px 6px;font-size:11px;color:#b91c1c;" onclick="changeUnitStatus('${u.id}', 'UNDER_MAINTENANCE')">Chuyển Bảo Trì</button>
                ` : ((u.status === 'MAINTENANCE' || u.status === 'UNDER_MAINTENANCE') ? `
                  <button class="btn-mo-outline" style="padding:2px 6px;font-size:11px;color:#16a34a;" onclick="changeUnitStatus('${u.id}', 'AVAILABLE')">Chuyển Trống</button>
                ` : '<span style="font-size:11px;color:#94a3b8;">Đang khai thác</span>')}
                <button class="btn-mo-outline" style="padding:2px 8px;font-size:11px;color:#0284c7;border-color:#bae6fd;margin-left:4px;" onclick="openUnitMediaModal('${u.id}', '${escapeHtml(u.unit_code)}')" title="Album ảnh & Layout sàn">
                  <i class="fa-solid fa-camera"></i> Ảnh (${u.images ? u.images.length : (u.images_count || '0')})
                </button>
                <button class="btn-mo-outline" style="padding:2px 6px;font-size:11px;color:#ef4444;margin-left:4px;" onclick="deleteUnit('${u.id}', '${escapeHtml(u.unit_code || u.name || '')}')" title="Xóa sàn">
                  <i class="fa-solid fa-trash"></i>
                </button>
              </td>
            </tr>
          `;
        }).join('');

        renderBuildingUnitsPagination(unitPaginationMeta);
      } catch (e) {
        tbody.innerHTML = '<tr><td colspan="6" style="color:#ef4444;text-align:center;">Lỗi kết nối tải mặt bằng.</td></tr>';
        if (paginationEl) paginationEl.innerHTML = '';
      }
    }

    // 7.1. Render phân trang danh sách các sàn trong modal
    function renderBuildingUnitsPagination(meta) {
      const container = document.getElementById('building-units-pagination');
      if (!container) return;

      const cur = parseInt(meta.current_page) || 1;
      const last = parseInt(meta.last_page) || 1;
      const total = parseInt(meta.total) || 0;
      const perPage = parseInt(meta.per_page) || unitPageSize;

      if (total === 0) {
        container.innerHTML = '';
        return;
      }

      const from = Math.min((cur - 1) * perPage + 1, total);
      const to = Math.min(cur * perPage, total);

      let pageButtonsHtml = '';
      for (let p = 1; p <= last; p++) {
        if (p === cur) {
          pageButtonsHtml += `<button type="button" class="btn-mo-primary" style="background:#2563eb;color:#fff;padding:2px 8px;font-size:11px;font-weight:700;border:none;border-radius:4px;cursor:default;">${p}</button>`;
        } else {
          pageButtonsHtml += `<button type="button" class="btn-mo-outline" style="background:#fff;padding:2px 7px;font-size:11px;border-radius:4px;" onclick="loadBuildingUnits('${currentActiveUnitBuildingId}', ${p})">${p}</button>`;
        }
      }

      container.innerHTML = `
        <span style="font-size:12px;color:#64748b;">
          Trang <strong>${cur}</strong> / <strong>${last}</strong> &nbsp;•&nbsp; Hiển thị <strong>${from} - ${to}</strong> / ${total} sàn
        </span>
        <div style="display:flex;align-items:center;gap:5px;">
          <button type="button" class="btn-mo-outline" style="padding:2px 8px;font-size:11px;background:#fff;border-radius:4px;display:inline-flex;align-items:center;gap:3px;" ${cur <= 1 ? 'disabled style="opacity:0.5;cursor:not-allowed;padding:2px 8px;font-size:11px;border-radius:4px;background:#f8fafc;"' : `onclick="loadBuildingUnits('${currentActiveUnitBuildingId}', ${cur - 1})"`}>
            <i class="fa-solid fa-chevron-left"></i> Trước
          </button>
          <div style="display:flex;align-items:center;gap:3px;">
            ${pageButtonsHtml}
          </div>
          <button type="button" class="btn-mo-outline" style="padding:2px 8px;font-size:11px;background:#fff;border-radius:4px;display:inline-flex;align-items:center;gap:3px;" ${cur >= last ? 'disabled style="opacity:0.5;cursor:not-allowed;padding:2px 8px;font-size:11px;border-radius:4px;background:#f8fafc;"' : `onclick="loadBuildingUnits('${currentActiveUnitBuildingId}', ${cur + 1})"`}>
            Sau <i class="fa-solid fa-chevron-right"></i>
          </button>
        </div>
      `;
    }

    async function submitCreateUnit(e) {
      if (e) e.preventDefault();
      if (!currentActiveUnitBuildingId) return;

      const payload = {
        building_id: currentActiveUnitBuildingId,
        unit_code: document.getElementById('new-unit-code').value.trim(),
        floor_number: parseInt(document.getElementById('new-unit-floor').value.trim()) || 1,
        area_sqm: parseFloat(document.getElementById('new-unit-area').value) || 0,
        base_rental_price: parseFloat(document.getElementById('new-unit-price').value) || 0,
        area: parseFloat(document.getElementById('new-unit-area').value) || 0,
        rent_price_per_sqm: parseFloat(document.getElementById('new-unit-price').value) || 0,
        service_fee_per_sqm: parseFloat(document.getElementById('new-unit-service-fee').value) || 0,
        handover_condition: document.getElementById('new-unit-handover')?.value?.trim() || null
      };

      if (!payload.unit_code || !payload.area || !payload.rent_price_per_sqm) {
        moToast('Vui lòng nhập đầy đủ Mã sàn, Diện tích và Đơn giá thuê.', 'warning', 'Thiếu thông tin');
        return;
      }

      try {
        const res = await fetch('/api/v1/admin/units', {
          method: 'POST',
          headers: getAuthHeaders(),
          body: JSON.stringify(payload)
        });

        const json = await res.json();
        if (res.ok) {
          moToast('Tạo mặt bằng sàn mới thành công!', 'success', 'Thành công');
          document.getElementById('form-create-unit')?.reset();
          loadBuildingUnits(currentActiveUnitBuildingId, 1);
          loadAdminBuildingsList(currentAdminBuildingPage);
        } else {
          moToast('Lỗi tạo mặt bằng: ' + (json.message || ''), 'error', 'Tạo mặt bằng thất bại');
        }
      } catch (e) {
        moToast('Lỗi kết nối khi tạo mặt bằng.', 'error', 'Lỗi kết nối');
      }
    }

    async function changeUnitStatus(unitId, newStatus) {
      try {
        const res = await fetch(`/api/v1/admin/units/${unitId}/status`, {
          method: 'PATCH',
          headers: getAuthHeaders(),
          body: JSON.stringify({ status: newStatus })
        });
        const json = await res.json();
        if (res.ok) {
          moToast('Cập nhật trạng thái mặt bằng thành công!', 'success', 'Thành công');
          if (currentActiveUnitBuildingId) loadBuildingUnits(currentActiveUnitBuildingId, currentUnitPage);
        } else {
          moToast('Lỗi cập nhật trạng thái: ' + (json.message || ''), 'error', 'Cập nhật thất bại');
        }
      } catch (e) {
        moToast('Lỗi kết nối cập nhật trạng thái sàn.', 'error', 'Lỗi kết nối');
      }
    }

    async function deleteUnit(unitId, unitCode = '') {
      moConfirmAction({
        title: 'Xác Nhận Xóa Mặt Bằng',
        targetName: unitCode ? `Mặt bằng ${unitCode}` : '',
        message: '',
        note: 'Chỉ cho phép xóa khi sàn chưa có hợp đồng thuê hoặc hợp đồng đã thanh lý hoàn tất.',
        confirmText: 'Xóa',
        type: 'danger',
        action: async () => {
          const res = await fetch(`/api/v1/admin/units/${unitId}`, {
            method: 'DELETE',
            headers: getAuthHeaders()
          });
          const json = await res.json();
          if (!res.ok) {
            throw new Error(json.message || 'Không thể xóa mặt bằng.');
          }
          return json.message || 'Xóa mặt bằng thành công.';
        },
        onSuccess: () => {
          if (currentActiveUnitBuildingId) {
            if (unitPaginationMeta.total && (unitPaginationMeta.total - 1) <= (currentUnitPage - 1) * unitPageSize && currentUnitPage > 1) {
              currentUnitPage--;
            }
            loadBuildingUnits(currentActiveUnitBuildingId, currentUnitPage);
          }
          loadAdminBuildingsList(currentAdminBuildingPage);
        }
      });
    }

    // 8. Quản lý Album ảnh Mặt Bằng (Unit Images): GET /admin/units/{id}, POST /images, DELETE /images/{imgId}
    let currentActiveUnitId = null;

    async function openUnitMediaModal(unitId, unitCode) {
      currentActiveUnitId = unitId;
      const modal = document.getElementById('modal-unit-media');
      if (!modal) return;
      const codeEl = document.getElementById('media-unit-code');
      if (codeEl) codeEl.innerText = unitCode;
      modal.style.display = 'flex';
      loadUnitMedia(unitId);
    }

    function closeUnitMediaModal() {
      const modal = document.getElementById('modal-unit-media');
      if (modal) modal.style.display = 'none';
      currentActiveUnitId = null;
    }

    async function loadUnitMedia(unitId) {
      const container = document.getElementById('unit-images-grid');
      if (!container) return;
      container.innerHTML = '<div style="text-align:center;padding:24px;color:#94a3b8;grid-column:1/-1;"><i class="fa-solid fa-spinner fa-spin"></i> Đang tải album ảnh mặt bằng...</div>';

      try {
        const res = await fetch(`/api/v1/admin/units/${unitId}`, {
          method: 'GET',
          headers: getAuthHeaders()
        });
        const json = await res.json();
        const images = json.data?.images || [];
        if (images.length === 0) {
          container.innerHTML = '<div style="text-align:center;padding:32px;color:#94a3b8;grid-column:1/-1;">Mặt bằng chưa có ảnh thực tế hoặc sơ đồ 2D. Hãy tải lên ảnh bên trên.</div>';
          return;
        }

        container.innerHTML = images.map(img => `
          <div style="position:relative;border-radius:8px;overflow:hidden;border:1px solid #e2e8f0;background:#f8fafc;">
            <img src="${escapeHtml(img.image_url)}" style="width:100%;height:130px;object-fit:cover;display:block;">
            <div style="padding:6px;display:flex;justify-content:space-between;align-items:center;background:#fff;border-top:1px solid #e2e8f0;">
              <span style="font-size:11px;color:#64748b;">${img.is_primary ? '<span class="badge-filament success" style="font-size:10px;"><i class="fa-solid fa-star"></i> Ảnh Chính</span>' : 'Ảnh sàn'}</span>
              <button class="btn-mo-outline" style="padding:2px 6px;font-size:10px;color:#ef4444;border-color:#fecaca;" onclick="deleteUnitImageAction('${unitId}', '${img.id}')">
                <i class="fa-solid fa-trash"></i>
              </button>
            </div>
          </div>
        `).join('');
      } catch (e) {
        container.innerHTML = '<div style="color:#ef4444;text-align:center;grid-column:1/-1;">Lỗi kết nối tải album ảnh mặt bằng.</div>';
      }
    }

    async function submitUploadUnitImages(e) {
      if (e) e.preventDefault();
      if (!currentActiveUnitId) return;

      const fileInput = document.getElementById('unit-images-input');
      if (!fileInput || !fileInput.files || fileInput.files.length === 0) {
        moToast('Vui lòng chọn ít nhất 1 ảnh sơ đồ mặt bằng (PNG, JPG).', 'warning', 'Chưa chọn tệp');
        return;
      }

      const formData = new FormData();
      for (let i = 0; i < fileInput.files.length; i++) {
        formData.append('images[]', fileInput.files[i]);
      }

      try {
        const res = await fetch(`/api/v1/admin/units/${currentActiveUnitId}/images`, {
          method: 'POST',
          headers: getAuthHeaders(true),
          body: formData
        });

        const json = await res.json();
        if (res.ok) {
          moToast('Tải lên ảnh sơ đồ mặt bằng thành công!', 'success', 'Thành công');
          fileInput.value = '';
          loadUnitMedia(currentActiveUnitId);
          if (currentActiveUnitBuildingId) loadBuildingUnits(currentActiveUnitBuildingId);
        } else {
          moToast('Lỗi tải ảnh: ' + (json.message || ''), 'error', 'Tải ảnh thất bại');
        }
      } catch (err) {
        moToast('Lỗi kết nối tải ảnh mặt bằng.', 'error', 'Lỗi kết nối');
      }
    }

    async function deleteUnitImageAction(unitId, imageId) {
      try {
        const res = await fetch(`/api/v1/admin/units/${unitId}/images/${imageId}`, {
          method: 'DELETE',
          headers: getAuthHeaders()
        });
        const json = await res.json();
        if (res.ok) {
          moToast('Xóa ảnh mặt bằng thành công.', 'success', 'Thành công');
          loadUnitMedia(unitId);
          if (currentActiveUnitBuildingId) loadBuildingUnits(currentActiveUnitBuildingId);
        } else {
          moToast('Lỗi xóa ảnh: ' + (json.message || ''), 'error', 'Xóa ảnh thất bại');
        }
      } catch (e) {
        moToast('Lỗi kết nối khi xóa ảnh sàn.', 'error', 'Lỗi kết nối');
      }
    }

    function doBuildingSearch() {
      const inp = document.getElementById('admin-building-search');
      handleAdminBuildingSearch(inp ? inp.value : '');
    }

    function handleAdminBuildingSearch(val) {
      currentAdminBuildingSearch = val.trim();
      currentAdminBuildingPage = 1;
      loadAdminBuildingsList(1);
    }

    function handleAdminBuildingGrade(grade) {
      currentAdminBuildingGrade = grade;
      const sel = document.getElementById('admin-building-grade-select');
      if (sel && sel.value !== grade) sel.value = grade;
      currentAdminBuildingPage = 1;
      loadAdminBuildingsList(1);
    }

    function resetBuildingFilters() {
      const inp = document.getElementById('admin-building-search');
      if (inp) inp.value = '';
      const clrBtn = document.getElementById('btn-clear-building-search');
      if (clrBtn) clrBtn.style.display = 'none';
      const sel = document.getElementById('admin-building-grade-select');
      if (sel) sel.value = 'ALL';
      currentAdminBuildingSearch = '';
      currentAdminBuildingGrade = 'ALL';
      currentAdminBuildingPage = 1;
      loadAdminBuildingsList(1);
    }

    window.doBuildingSearch = doBuildingSearch;
    window.handleAdminBuildingGrade = handleAdminBuildingGrade;
    window.resetBuildingFilters = resetBuildingFilters;
    window.loadAdminBuildingsList = loadAdminBuildingsList;
    window.loadBuildingUnits = loadBuildingUnits;
</script>
