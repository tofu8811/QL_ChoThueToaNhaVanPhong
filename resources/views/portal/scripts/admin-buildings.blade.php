<script>
    /* ==========================================================================
       MODULE 1: REAL ESTATE ASSETS & BUILDINGS MANAGEMENT (ENDPOINTS 5 - 22)
       ========================================================================== */
    let adminBuildingsList = [];
    let currentAdminBuildingSearch = '';
    let currentAdminBuildingGrade = 'ALL';
    let currentActiveBuildingId = null;
    let currentActiveUnitBuildingId = null;

    // 1. Nạp danh sách tòa nhà quản trị: GET /api/v1/admin/buildings
    async function loadAdminBuildingsList() {
      const tbody = document.getElementById('admin-buildings-tbody');
      if (tbody) {
        tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;padding:24px;color:#94a3b8;"><i class="fa-solid fa-spinner fa-spin"></i> Đang nạp danh sách cao ốc văn phòng từ API...</td></tr>';
      }

      try {
        let url = '/api/v1/admin/buildings?per_page=100';
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
          adminBuildingsList = json.data?.data || json.data || [];
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
      const countEl = document.getElementById('admin-buildings-total-count');
      if (countEl) countEl.innerText = adminBuildingsList.length;

      if (!tbody) return;
      if (adminBuildingsList.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;padding:32px;color:#94a3b8;">Không tìm thấy tòa nhà nào trong hệ thống.</td></tr>';
        return;
      }

      tbody.innerHTML = adminBuildingsList.map(b => {
        const gradeBadge = b.grade === 'A'
          ? '<span class="badge-filament success" style="font-weight:700;">Hạng A</span>'
          : (b.grade === 'B' ? '<span class="badge-filament info" style="font-weight:700;">Hạng B</span>' : '<span class="badge-filament">Hạng C</span>');

        const address = `${escapeHtml(b.address || '')}, ${escapeHtml(b.district || 'TP.HCM')}`;
        const totalFloors = `${b.total_floors || 0} tầng (${b.basements || 0} hầm)`;
        const pricePerSqm = b.base_rent_price_usd ? `$${b.base_rent_price_usd}/m²` : (b.priceUsd ? `$${b.priceUsd}/m²` : '---');

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
              <strong style="color:#0284c7;">${b.total_net_area ? `${Number(b.total_net_area).toLocaleString()} m²` : (b.totalArea || '---')}</strong>
            </td>
            <td>
              <span style="font-weight:700;color:#ea580c;font-size:13px;">${pricePerSqm}</span>
              <div style="font-size:11px;color:#64748b;">Phí QL: $${b.service_fee_usd || 0}/m²</div>
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
              <button class="btn-mo-outline" style="padding:4px 8px;font-size:12px;color:#4f46e5;border-color:#c7d2fe;margin-left:4px;" onclick="openBuildingAssignmentsFromBuilding('${b.id}', '${escapeHtml(b.name)}')" title="Phân công BQL">
                <i class="fa-solid fa-users-gear"></i> Phân quyền
              </button>
              <button class="btn-mo-outline" style="padding:4px 8px;font-size:12px;color:#334155;margin-left:4px;" onclick="openEditBuildingModal('${b.id}')" title="Sửa thông số tòa nhà">
                <i class="fa-solid fa-pen"></i>
              </button>
              <button class="btn-mo-outline" style="padding:4px 8px;font-size:12px;color:#ef4444;border-color:#fecaca;margin-left:4px;" onclick="deleteBuilding('${b.id}')" title="Xóa tòa nhà">
                <i class="fa-solid fa-trash"></i>
              </button>
            </td>
          </tr>
        `;
      }).join('');
    }

    // 3. Mở Modal tạo mới Tòa nhà: POST /api/v1/admin/buildings
    function openCreateBuildingModal() {
      const modal = document.getElementById('modal-create-building');
      if (!modal) return;
      document.getElementById('form-create-building')?.reset();
      modal.style.display = 'flex';
    }

    function closeCreateBuildingModal() {
      const modal = document.getElementById('modal-create-building');
      if (modal) modal.style.display = 'none';
    }

    async function submitCreateBuilding(e) {
      if (e) e.preventDefault();
      const payload = {
        name: document.getElementById('build-name').value.trim(),
        building_code: document.getElementById('build-code').value.trim() || undefined,
        grade: document.getElementById('build-grade').value || 'B',
        address: document.getElementById('build-address').value.trim(),
        district: document.getElementById('build-district').value.trim() || 'Quận 1',
        total_floors: parseInt(document.getElementById('build-floors').value) || 10,
        basements: parseInt(document.getElementById('build-basements').value) || 1,
        total_net_area: parseFloat(document.getElementById('build-net-area').value) || null,
        base_rent_price_usd: parseFloat(document.getElementById('build-base-price').value) || null,
        service_fee_usd: parseFloat(document.getElementById('build-service-fee').value) || null,
        electricity_fee_desc: document.getElementById('build-electric-fee').value.trim() || null,
        water_fee_desc: document.getElementById('build-water-fee').value.trim() || null,
        motorcycle_parking_fee: document.getElementById('build-motor-fee').value.trim() || null,
        car_parking_fee: document.getElementById('build-car-fee').value.trim() || null,
        is_published: document.getElementById('build-published').checked
      };

      if (!payload.name || !payload.address) {
        alert('Vui lòng nhập tên tòa nhà và địa chỉ.');
        return;
      }

      try {
        const res = await fetch('/api/v1/admin/buildings', {
          method: 'POST',
          headers: getAuthHeaders(),
          body: JSON.stringify(payload)
        });

        const json = await res.json();
        if (res.ok) {
          alert('Tạo tòa nhà văn phòng mới thành công!');
          closeCreateBuildingModal();
          loadAdminBuildingsList();
        } else {
          alert('Lỗi tạo tòa nhà: ' + (json.message || ''));
        }
      } catch (err) {
        alert('Lỗi kết nối API Tòa nhà.');
      }
    }

    // 4. Mở Modal sửa Tòa nhà: PUT /api/v1/admin/buildings/{id}
    let currentEditBuildingId = null;
    async function openEditBuildingModal(buildingId) {
      currentEditBuildingId = buildingId;
      const modal = document.getElementById('modal-edit-building');
      if (!modal) return;
      modal.style.display = 'flex';

      try {
        const res = await fetch(`/api/v1/admin/buildings/${buildingId}`, {
          method: 'GET',
          headers: getAuthHeaders()
        });
        const json = await res.json();
        if (!res.ok) {
          alert('Không thể tải thông tin tòa nhà: ' + (json.message || ''));
          closeEditBuildingModal();
          return;
        }

        const b = json.data;
        document.getElementById('edit-build-name').value = b.name || '';
        document.getElementById('edit-build-code').value = b.building_code || '';
        document.getElementById('edit-build-grade').value = b.grade || 'B';
        document.getElementById('edit-build-address').value = b.address || '';
        document.getElementById('edit-build-district').value = b.district || 'Quận 1';
        document.getElementById('edit-build-floors').value = b.total_floors || 10;
        document.getElementById('edit-build-basements').value = b.basements || 1;
        document.getElementById('edit-build-net-area').value = b.total_net_area || '';
        document.getElementById('edit-build-base-price').value = b.base_rent_price_usd || '';
        document.getElementById('edit-build-service-fee').value = b.service_fee_usd || '';
        document.getElementById('edit-build-published').checked = Boolean(b.is_published);
      } catch (e) {
        alert('Lỗi kết nối khi tải chi tiết tòa nhà.');
        closeEditBuildingModal();
      }
    }

    function closeEditBuildingModal() {
      const modal = document.getElementById('modal-edit-building');
      if (modal) modal.style.display = 'none';
      currentEditBuildingId = null;
    }

    async function submitEditBuilding(e) {
      if (e) e.preventDefault();
      if (!currentEditBuildingId) return;

      const payload = {
        name: document.getElementById('edit-build-name').value.trim(),
        building_code: document.getElementById('edit-build-code').value.trim() || undefined,
        grade: document.getElementById('edit-build-grade').value || 'B',
        address: document.getElementById('edit-build-address').value.trim(),
        district: document.getElementById('edit-build-district').value.trim() || 'Quận 1',
        total_floors: parseInt(document.getElementById('edit-build-floors').value) || 10,
        basements: parseInt(document.getElementById('edit-build-basements').value) || 1,
        total_net_area: parseFloat(document.getElementById('edit-build-net-area').value) || null,
        base_rent_price_usd: parseFloat(document.getElementById('edit-build-base-price').value) || null,
        service_fee_usd: parseFloat(document.getElementById('edit-build-service-fee').value) || null,
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
          alert('Cập nhật thông tin tòa nhà thành công!');
          closeEditBuildingModal();
          loadAdminBuildingsList();
        } else {
          alert('Lỗi cập nhật: ' + (json.message || ''));
        }
      } catch (e) {
        alert('Lỗi kết nối cập nhật tòa nhà.');
      }
    }

    // 5. Xóa mềm tòa nhà: DELETE /api/v1/admin/buildings/{id}
    async function deleteBuilding(buildingId) {
      if (!confirm('Bạn có chắc chắn muốn xóa tòa nhà này? Bắt buộc toàn bộ các sàn không còn hợp đồng hiệu lực.')) return;

      try {
        const res = await fetch(`/api/v1/admin/buildings/${buildingId}`, {
          method: 'DELETE',
          headers: getAuthHeaders()
        });

        const json = await res.json();
        if (res.ok) {
          alert('Đã xóa mềm tòa nhà thành công.');
          loadAdminBuildingsList();
        } else {
          alert('Không thể xóa: ' + (json.message || ''));
        }
      } catch (e) {
        alert('Lỗi kết nối khi xóa tòa nhà.');
      }
    }

    // 6. Quản lý Album ảnh Tòa nhà (Supabase Storage): POST/DELETE /images & PATCH /set-primary
    async function openBuildingMediaModal(buildingId, buildingName) {
      currentActiveBuildingId = buildingId;
      const modal = document.getElementById('modal-building-media');
      if (!modal) return;
      document.getElementById('media-building-name').innerText = buildingName;
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
          <div style="position:relative;border-radius:8px;overflow:hidden;border:1px solid #e2e8f0;background:#f8fafc;">
            <img src="${escapeHtml(img.image_url)}" style="width:100%;height:130px;object-fit:cover;display:block;">
            <div style="padding:6px;display:flex;justify-content:space-between;align-items:center;background:#fff;border-top:1px solid #e2e8f0;">
              ${img.is_primary ? '<span class="badge-filament success" style="font-size:10px;"><i class="fa-solid fa-star"></i> Ảnh Chính</span>' : `
                <button class="btn-mo-outline" style="padding:2px 6px;font-size:10px;" onclick="setBuildingPrimaryImage('${buildingId}', '${img.id}')">Đặt Chính</button>
              `}
              <button class="btn-mo-outline" style="padding:2px 6px;font-size:10px;color:#ef4444;border-color:#fecaca;" onclick="deleteBuildingImage('${buildingId}', '${img.id}')">
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
        alert('Vui lòng chọn ít nhất 1 ảnh (PNG, JPG).');
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
          alert('Tải lên ảnh tòa nhà lên Supabase Storage thành công!');
          fileInput.value = '';
          loadBuildingMedia(currentActiveBuildingId);
        } else {
          alert('Lỗi tải ảnh: ' + (json.message || ''));
        }
      } catch (err) {
        alert('Lỗi kết nối tải ảnh.');
      }
    }

    async function deleteBuildingImage(buildingId, imageId) {
      if (!confirm('Xóa ảnh này khỏi thư viện?')) return;
      try {
        const res = await fetch(`/api/v1/admin/buildings/${buildingId}/images/${imageId}`, {
          method: 'DELETE',
          headers: getAuthHeaders()
        });
        const json = await res.json();
        if (res.ok) {
          loadBuildingMedia(buildingId);
        } else {
          alert('Lỗi xóa ảnh: ' + (json.message || ''));
        }
      } catch (e) {
        alert('Lỗi kết nối xóa ảnh.');
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
          alert('Lỗi đặt ảnh chính: ' + (json.message || ''));
        }
      } catch (e) {
        alert('Lỗi kết nối đặt ảnh chính.');
      }
    }

    // 7. Quản lý Mặt Bằng / Diện Tích Cắt Thuê (Units): GET/POST/PUT/DELETE /admin/units
    async function openManageBuildingUnitsModal(buildingId, buildingName) {
      currentActiveUnitBuildingId = buildingId;
      const modal = document.getElementById('modal-manage-units');
      if (!modal) return;
      document.getElementById('units-modal-building-name').innerText = buildingName;
      modal.style.display = 'flex';
      loadBuildingUnits(buildingId);
    }

    function closeManageBuildingUnitsModal() {
      const modal = document.getElementById('modal-manage-units');
      if (modal) modal.style.display = 'none';
      currentActiveUnitBuildingId = null;
    }

    async function loadBuildingUnits(buildingId) {
      const tbody = document.getElementById('building-units-tbody');
      if (!tbody) return;
      tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;padding:20px;color:#94a3b8;"><i class="fa-solid fa-spinner fa-spin"></i> Đang nạp danh sách mặt bằng...</td></tr>';

      try {
        const res = await fetch(`/api/v1/admin/units?building_id=${buildingId}&per_page=100`, {
          method: 'GET',
          headers: getAuthHeaders()
        });
        const json = await res.json();
        const units = json.data?.data || json.data || [];
        if (units.length === 0) {
          tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;padding:24px;color:#94a3b8;">Tòa nhà chưa có mặt bằng sàn nào được tạo.</td></tr>';
          return;
        }

        tbody.innerHTML = units.map(u => {
          let statusBadge = '<span class="badge-filament success">AVAILABLE (Trống)</span>';
          if (u.status === 'RESERVED') statusBadge = '<span class="badge-filament warning">RESERVED (Giữ chỗ)</span>';
          else if (u.status === 'OCCUPIED') statusBadge = '<span class="badge-filament danger">OCCUPIED (Đang thuê)</span>';
          else if (u.status === 'MAINTENANCE') statusBadge = '<span class="badge-filament" style="background:#fee2e2;color:#b91c1c;">MAINTENANCE (Bảo trì)</span>';

          return `
            <tr>
              <td><strong style="color:var(--mo-primary);font-size:13px;">${escapeHtml(u.unit_code)}</strong></td>
              <td>Tầng ${escapeHtml(u.floor_number || '---')}</td>
              <td><strong>${u.area || 0} m²</strong></td>
              <td><span style="color:#ea580c;font-weight:700;">$${u.rent_price_per_sqm || 0}/m²</span></td>
              <td>${statusBadge}</td>
              <td style="text-align:right;white-space:nowrap;">
                ${u.status === 'AVAILABLE' ? `
                  <button class="btn-mo-outline" style="padding:2px 6px;font-size:11px;color:#b91c1c;" onclick="changeUnitStatus('${u.id}', 'MAINTENANCE')">Chuyển Bảo Trì</button>
                ` : (u.status === 'MAINTENANCE' ? `
                  <button class="btn-mo-outline" style="padding:2px 6px;font-size:11px;color:#16a34a;" onclick="changeUnitStatus('${u.id}', 'AVAILABLE')">Chuyển Trống</button>
                ` : '<span style="font-size:11px;color:#94a3b8;">Đã gắn HĐ</span>')}
                <button class="btn-mo-outline" style="padding:2px 6px;font-size:11px;color:#ef4444;margin-left:4px;" onclick="deleteUnit('${u.id}')" title="Xóa sàn">
                  <i class="fa-solid fa-trash"></i>
                </button>
              </td>
            </tr>
          `;
        }).join('');
      } catch (e) {
        tbody.innerHTML = '<tr><td colspan="6" style="color:#ef4444;text-align:center;">Lỗi kết nối tải mặt bằng.</td></tr>';
      }
    }

    async function submitCreateUnit(e) {
      if (e) e.preventDefault();
      if (!currentActiveUnitBuildingId) return;

      const payload = {
        building_id: currentActiveUnitBuildingId,
        unit_code: document.getElementById('new-unit-code').value.trim(),
        floor_number: document.getElementById('new-unit-floor').value.trim(),
        area: parseFloat(document.getElementById('new-unit-area').value) || 0,
        rent_price_per_sqm: parseFloat(document.getElementById('new-unit-price').value) || 0,
        service_fee_per_sqm: parseFloat(document.getElementById('new-unit-service-fee').value) || 0,
        handover_condition: document.getElementById('new-unit-handover').value.trim() || null
      };

      if (!payload.unit_code || !payload.area || !payload.rent_price_per_sqm) {
        alert('Vui lòng nhập Mã sàn, Diện tích và Đơn giá thuê.');
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
          alert('Tạo mặt bằng sàn mới thành công!');
          document.getElementById('form-create-unit')?.reset();
          loadBuildingUnits(currentActiveUnitBuildingId);
          loadAdminBuildingsList();
        } else {
          alert('Lỗi tạo mặt bằng: ' + (json.message || ''));
        }
      } catch (e) {
        alert('Lỗi kết nối khi tạo mặt bằng.');
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
          if (currentActiveUnitBuildingId) loadBuildingUnits(currentActiveUnitBuildingId);
        } else {
          alert('Lỗi cập nhật trạng thái: ' + (json.message || ''));
        }
      } catch (e) {
        alert('Lỗi kết nối cập nhật trạng thái sàn.');
      }
    }

    async function deleteUnit(unitId) {
      if (!confirm('Xóa mặt bằng này? Chỉ cho phép xóa khi sàn chưa có hợp đồng thuê hiệu lực.')) return;
      try {
        const res = await fetch(`/api/v1/admin/units/${unitId}`, {
          method: 'DELETE',
          headers: getAuthHeaders()
        });
        const json = await res.json();
        if (res.ok) {
          if (currentActiveUnitBuildingId) loadBuildingUnits(currentActiveUnitBuildingId);
          loadAdminBuildingsList();
        } else {
          alert('Lỗi xóa mặt bằng: ' + (json.message || ''));
        }
      } catch (e) {
        alert('Lỗi kết nối xóa mặt bằng.');
      }
    }

    // 8. Tải lên và Xóa ảnh mặt bằng: POST & DELETE /api/v1/admin/units/{id}/images
    async function uploadUnitImages(unitId, fileInput) {
      if (!fileInput || !fileInput.files || fileInput.files.length === 0) return;
      const formData = new FormData();
      for (let i = 0; i < fileInput.files.length; i++) {
        formData.append('images[]', fileInput.files[i]);
      }
      try {
        const res = await fetch(`/api/v1/admin/units/${unitId}/images`, {
          method: 'POST',
          headers: getAuthHeaders(true),
          body: formData
        });
        const json = await res.json();
        if (res.ok) {
          alert('Tải lên ảnh mặt bằng thành công!');
          if (currentActiveUnitBuildingId) loadBuildingUnits(currentActiveUnitBuildingId);
        } else {
          alert('Lỗi upload ảnh mặt bằng: ' + (json.message || ''));
        }
      } catch (e) {
        alert('Lỗi kết nối upload ảnh mặt bằng.');
      }
    }

    async function deleteUnitImage(unitId, imageId) {
      if (!confirm('Xóa ảnh sơ đồ mặt bằng này?')) return;
      try {
        const res = await fetch(`/api/v1/admin/units/${unitId}/images/${imageId}`, {
          method: 'DELETE',
          headers: getAuthHeaders()
        });
        const json = await res.json();
        if (res.ok) {
          if (currentActiveUnitBuildingId) loadBuildingUnits(currentActiveUnitBuildingId);
        } else {
          alert('Lỗi xóa ảnh mặt bằng: ' + (json.message || ''));
        }
      } catch (e) {
        alert('Lỗi kết nối khi xóa ảnh sàn.');
      }
    }

    function handleAdminBuildingSearch(val) {
      currentAdminBuildingSearch = val.trim();
      loadAdminBuildingsList();
    }

    function handleAdminBuildingGrade(grade) {
      currentAdminBuildingGrade = grade;
      loadAdminBuildingsList();
    }
</script>
