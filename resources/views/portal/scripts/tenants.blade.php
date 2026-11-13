<script>
    /* ==========================================================================
       MODULE 3: TENANTS MANAGEMENT JAVASCRIPT (ENDPOINTS 34 - 42)
       ========================================================================== */
    let tenantsList = [];
    let currentTenantSearch = '';
    let currentTenantStatus = 'ALL';
    let currentUploadLicenseTenantId = null;

    // 1. Tải danh sách khách thuê doanh nghiệp: GET /api/v1/admin/tenants
    async function loadTenantsList() {
      const tbody = document.getElementById('admin-tenants-tbody');
      if (tbody) {
        tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;padding:24px;color:#94a3b8;"><i class="fa-solid fa-spinner fa-spin"></i> Đang tải dữ liệu khách thuê từ API...</td></tr>';
      }

      try {
        let url = '/api/v1/admin/tenants?per_page=100';
        if (currentTenantSearch) {
          url += `&search=${encodeURIComponent(currentTenantSearch)}`;
        }
        if (currentTenantStatus && currentTenantStatus !== 'ALL') {
          url += `&status=${encodeURIComponent(currentTenantStatus)}`;
        }

        const res = await fetch(url, {
          method: 'GET',
          headers: getAuthHeaders()
        });

        if (res.status === 401) {
          if (tbody) tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;color:#ef4444;padding:20px;">Vui lòng đăng nhập với tài khoản Admin / PM / Sales.</td></tr>';
          return;
        }

        const json = await res.json();
        if (res.ok) {
          tenantsList = json.data?.data || json.data || [];
          renderTenantsTable();
        } else {
          if (tbody) tbody.innerHTML = `<tr><td colspan="7" style="text-align:center;color:#ef4444;padding:20px;">Lỗi: ${escapeHtml(json.message || 'Không thể tải khách thuê')}</td></tr>`;
        }
      } catch (err) {
        if (tbody) tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;color:#ef4444;padding:20px;">Lỗi kết nối máy chủ API Khách thuê.</td></tr>';
      }
    }

    // 2. Render bảng danh sách khách thuê
    function renderTenantsTable() {
      const tbody = document.getElementById('admin-tenants-tbody');
      const countEl = document.getElementById('admin-tenants-count');
      if (countEl) countEl.innerText = tenantsList.length;

      if (!tbody) return;
      if (tenantsList.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;padding:32px;color:#94a3b8;">Chưa có hồ sơ khách thuê doanh nghiệp nào.</td></tr>';
        return;
      }

      tbody.innerHTML = tenantsList.map(t => {
        const statusBadge = t.status === 'ACTIVE'
          ? '<span class="badge-filament success"><i class="fa-solid fa-circle-check"></i> Đang Hoạt Động</span>'
          : '<span class="badge-filament danger"><i class="fa-solid fa-circle-xmark"></i> Ngừng Hoạt Động</span>';

        const licenseBtn = t.license_file_url
          ? `<button class="btn-mo-outline" style="padding:4px 8px;font-size:11px;color:#0284c7;border-color:#bae6fd;" onclick="downloadTenantLicense('${t.id}')" title="Tải GPKD Đã Upload"><i class="fa-solid fa-file-pdf"></i> Tải GPKD</button>`
          : `<button class="btn-mo-outline" style="padding:4px 8px;font-size:11px;color:#ea580c;border-color:#fed7aa;" onclick="openUploadLicenseModal('${t.id}', '${escapeHtml(t.company_name)}')" title="Tải Lên GPKD Mới"><i class="fa-solid fa-upload"></i> Upload GPKD</button>`;

        return `
          <tr>
            <td>
              <strong style="color:var(--mo-primary);font-family:monospace;font-size:13px;">${escapeHtml(t.tenant_code || t.id.substring(0,8))}</strong>
              <div style="font-size:11px;color:#94a3b8;">${escapeHtml(new Date(t.created_at).toLocaleDateString('vi-VN'))}</div>
            </td>
            <td>
              <div style="font-weight:700;color:#0f172a;font-size:14px;">${escapeHtml(t.company_name)}</div>
              <div style="font-size:12px;color:#475569;">
                MST: <code style="background:#f1f5f9;padding:1px 4px;border-radius:3px;font-weight:600;">${escapeHtml(t.tax_code || 'Chưa cập nhật')}</code>
              </div>
            </td>
            <td>
              <div style="font-weight:600;color:#334155;">${escapeHtml(t.representative_name || '---')}</div>
              <div style="font-size:12px;color:#64748b;">
                <i class="fa-solid fa-phone" style="font-size:11px;"></i> ${escapeHtml(t.contact_phone || '---')}
                ${t.contact_email ? `| <i class="fa-solid fa-envelope" style="font-size:11px;"></i> ${escapeHtml(t.contact_email)}` : ''}
              </div>
            </td>
            <td>
              <div style="font-size:12px;color:#475569;max-width:200px;">${escapeHtml(t.business_address || '---')}</div>
            </td>
            <td>${statusBadge}</td>
            <td>${licenseBtn}</td>
            <td style="text-align:right;white-space:nowrap;">
              <button class="btn-mo-outline" style="padding:4px 8px;font-size:12px;" onclick="openEditTenantModal('${t.id}')" title="Sửa thông tin pháp lý">
                <i class="fa-solid fa-pen-to-square"></i> Sửa
              </button>
              <button class="btn-mo-outline" style="padding:4px 8px;font-size:12px;color:#ef4444;border-color:#fecaca;margin-left:4px;" onclick="deleteTenant('${t.id}')" title="Vô hiệu hóa / Xóa">
                <i class="fa-solid fa-trash"></i>
              </button>
            </td>
          </tr>
        `;
      }).join('');
    }

    // 3. Mở Modal tạo khách thuê: POST /api/v1/admin/tenants
    function openCreateTenantModal() {
      const modal = document.getElementById('modal-create-tenant');
      if (!modal) return;
      document.getElementById('form-create-tenant')?.reset();
      modal.style.display = 'flex';
    }

    function closeCreateTenantModal() {
      const modal = document.getElementById('modal-create-tenant');
      if (modal) modal.style.display = 'none';
    }

    async function submitCreateTenant(e) {
      if (e) e.preventDefault();
      const payload = {
        company_name: document.getElementById('tenant-company-name').value.trim(),
        tax_code: document.getElementById('tenant-tax-code').value.trim() || null,
        representative_name: document.getElementById('tenant-rep-name').value.trim() || null,
        contact_phone: document.getElementById('tenant-contact-phone').value.trim() || null,
        contact_email: document.getElementById('tenant-contact-email').value.trim() || null,
        business_address: document.getElementById('tenant-address').value.trim() || null,
        status: document.getElementById('tenant-status').value || 'ACTIVE'
      };

      if (!payload.company_name) {
        alert('Vui lòng nhập tên công ty / doanh nghiệp.');
        return;
      }

      try {
        const res = await fetch('/api/v1/admin/tenants', {
          method: 'POST',
          headers: getAuthHeaders(),
          body: JSON.stringify(payload)
        });

        const json = await res.json();
        if (res.ok) {
          alert('Tạo hồ sơ khách thuê doanh nghiệp thành công!');
          closeCreateTenantModal();
          loadTenantsList();
        } else {
          alert('Lỗi tạo khách thuê: ' + (json.message || ''));
        }
      } catch (err) {
        alert('Lỗi kết nối máy chủ API.');
      }
    }

    // 4. Mở Modal sửa thông tin pháp lý: PUT /api/v1/admin/tenants/{id}
    let currentEditTenantId = null;
    async function openEditTenantModal(tenantId) {
      currentEditTenantId = tenantId;
      const modal = document.getElementById('modal-edit-tenant');
      if (!modal) return;
      modal.style.display = 'flex';

      try {
        const res = await fetch(`/api/v1/admin/tenants/${tenantId}`, {
          method: 'GET',
          headers: getAuthHeaders()
        });
        const json = await res.json();
        if (!res.ok) {
          alert('Lỗi tải thông tin khách thuê: ' + (json.message || ''));
          closeEditTenantModal();
          return;
        }

        const t = json.data;
        document.getElementById('edit-tenant-company-name').value = t.company_name || '';
        document.getElementById('edit-tenant-tax-code').value = t.tax_code || '';
        document.getElementById('edit-tenant-rep-name').value = t.representative_name || '';
        document.getElementById('edit-tenant-contact-phone').value = t.contact_phone || '';
        document.getElementById('edit-tenant-contact-email').value = t.contact_email || '';
        document.getElementById('edit-tenant-address').value = t.business_address || '';
        document.getElementById('edit-tenant-status').value = t.status || 'ACTIVE';
      } catch (err) {
        alert('Lỗi kết nối khi tải hồ sơ khách thuê.');
        closeEditTenantModal();
      }
    }

    function closeEditTenantModal() {
      const modal = document.getElementById('modal-edit-tenant');
      if (modal) modal.style.display = 'none';
      currentEditTenantId = null;
    }

    async function submitEditTenant(e) {
      if (e) e.preventDefault();
      if (!currentEditTenantId) return;

      const payload = {
        company_name: document.getElementById('edit-tenant-company-name').value.trim(),
        tax_code: document.getElementById('edit-tenant-tax-code').value.trim() || null,
        representative_name: document.getElementById('edit-tenant-rep-name').value.trim() || null,
        contact_phone: document.getElementById('edit-tenant-contact-phone').value.trim() || null,
        contact_email: document.getElementById('edit-tenant-contact-email').value.trim() || null,
        business_address: document.getElementById('edit-tenant-address').value.trim() || null,
        status: document.getElementById('edit-tenant-status').value || 'ACTIVE'
      };

      try {
        const res = await fetch(`/api/v1/admin/tenants/${currentEditTenantId}`, {
          method: 'PUT',
          headers: getAuthHeaders(),
          body: JSON.stringify(payload)
        });

        const json = await res.json();
        if (res.ok) {
          alert('Cập nhật thông tin khách thuê thành công!');
          closeEditTenantModal();
          loadTenantsList();
        } else {
          alert('Lỗi cập nhật: ' + (json.message || ''));
        }
      } catch (err) {
        alert('Lỗi kết nối cập nhật khách thuê.');
      }
    }

    // 5. Tải lên Giấy phép kinh doanh: POST /api/v1/admin/tenants/{id}/upload-license
    function openUploadLicenseModal(tenantId, companyName) {
      currentUploadLicenseTenantId = tenantId;
      const modal = document.getElementById('modal-upload-license');
      if (!modal) return;
      document.getElementById('upload-license-company-name').innerText = companyName;
      document.getElementById('form-upload-license')?.reset();
      modal.style.display = 'flex';
    }

    function closeUploadLicenseModal() {
      const modal = document.getElementById('modal-upload-license');
      if (modal) modal.style.display = 'none';
      currentUploadLicenseTenantId = null;
    }

    async function submitUploadLicense(e) {
      if (e) e.preventDefault();
      if (!currentUploadLicenseTenantId) return;

      const fileInput = document.getElementById('license-file-input');
      if (!fileInput || !fileInput.files || fileInput.files.length === 0) {
        alert('Vui lòng chọn file GPKD (PDF/JPG/PNG).');
        return;
      }

      const formData = new FormData();
      formData.append('license_file', fileInput.files[0]);

      try {
        const res = await fetch(`/api/v1/admin/tenants/${currentUploadLicenseTenantId}/upload-license`, {
          method: 'POST',
          headers: getAuthHeaders(true),
          body: formData
        });

        const json = await res.json();
        if (res.ok) {
          alert('Tải lên Giấy phép kinh doanh lên Supabase Storage thành công!');
          closeUploadLicenseModal();
          loadTenantsList();
        } else {
          alert('Lỗi upload GPKD: ' + (json.message || ''));
        }
      } catch (err) {
        alert('Lỗi kết nối khi tải lên file.');
      }
    }

    // 6. Lấy Link Ký số Tải Giấy phép ĐKKD: GET /api/v1/admin/tenants/{id}/download-license
    async function downloadTenantLicense(tenantId) {
      try {
        const res = await fetch(`/api/v1/admin/tenants/${tenantId}/download-license`, {
          method: 'GET',
          headers: getAuthHeaders()
        });

        const json = await res.json();
        if (res.ok && json.data?.download_url) {
          window.open(json.data.download_url, '_blank');
        } else {
          alert('Không thể lấy link tải: ' + (json.message || 'File không tồn tại.'));
        }
      } catch (e) {
        alert('Lỗi kết nối lấy link tải GPKD.');
      }
    }

    // 7. Xóa hoặc vô hiệu hóa khách thuê: DELETE /api/v1/admin/tenants/{id}
    async function deleteTenant(tenantId) {
      if (!confirm('Bạn có chắc chắn muốn xóa/vô hiệu hóa hồ sơ khách thuê này? Chú ý: Không thể xóa nếu đang có hợp đồng hiệu lực.')) return;

      try {
        const res = await fetch(`/api/v1/admin/tenants/${tenantId}`, {
          method: 'DELETE',
          headers: getAuthHeaders()
        });

        const json = await res.json();
        if (res.ok) {
          alert('Xử lý thành công.');
          loadTenantsList();
        } else {
          alert('Không thể xóa: ' + (json.message || ''));
        }
      } catch (e) {
        alert('Lỗi kết nối khi xóa khách thuê.');
      }
    }

    function handleTenantSearch(val) {
      currentTenantSearch = val.trim();
      loadTenantsList();
    }

    function handleTenantStatusFilter(status) {
      currentTenantStatus = status;
      document.querySelectorAll('.tenant-status-btn').forEach(b => {
        b.classList.remove('active');
        if (b.getAttribute('data-status') === status) b.classList.add('active');
      });
      loadTenantsList();
    }
</script>
