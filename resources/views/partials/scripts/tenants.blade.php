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

        const hasLicense = Boolean(t.business_license_private_path || t.license_file_url);
        const licenseBtn = hasLicense
          ? `<button class="btn-mo-outline" style="padding:4px 8px;font-size:11px;color:#0284c7;border-color:#bae6fd;" onclick="downloadTenantLicense('${t.id}')" title="Tải GPKD Đã Upload"><i class="fa-solid fa-file-pdf"></i> Tải GPKD</button>`
          : `<button class="btn-mo-outline" style="padding:4px 8px;font-size:11px;color:#ea580c;border-color:#fed7aa;" onclick="openUploadLicenseModal('${t.id}', '${escapeHtml(t.company_name)}')" title="Tải Lên GPKD Mới"><i class="fa-solid fa-upload"></i> Upload GPKD</button>`;

        const emailVal = t.login_email || t.billing_email || t.contact_email || '';
        const addressVal = t.headquarter_address || t.business_address || '---';
        const codeVal = t.tenant_code || (t.tax_code ? `DN-${t.tax_code.slice(-6)}` : t.id.substring(0, 8));

        return `
          <tr>
            <td>
              <strong style="color:var(--mo-primary);font-family:monospace;font-size:13px;">${escapeHtml(codeVal)}</strong>
              <div style="font-size:11px;color:#94a3b8;">${escapeHtml(new Date(t.created_at).toLocaleDateString('vi-VN'))}</div>
            </td>
            <td>
              <div style="font-weight:700;color:#0f172a;font-size:14px;">${escapeHtml(t.company_name)}</div>
              <div style="font-size:12px;color:#475569;">
                MST: <code style="background:#f1f5f9;padding:1px 4px;border-radius:3px;font-weight:600;">${escapeHtml(t.tax_code || 'Chưa cập nhật')}</code>
              </div>
            </td>
            <td>
              <div style="font-weight:600;color:#334155;">${escapeHtml(t.representative_name || '---')}${t.representative_title ? ` <span style="font-weight:400;color:#64748b;font-size:11px;">(${escapeHtml(t.representative_title)})</span>` : ''}</div>
              <div style="font-size:12px;color:#64748b;">
                <i class="fa-solid fa-phone" style="font-size:11px;"></i> ${escapeHtml(t.contact_phone || '---')}
                ${emailVal ? `| <i class="fa-solid fa-envelope" style="font-size:11px;"></i> ${escapeHtml(emailVal)}` : ''}
              </div>
            </td>
            <td>
              <div style="font-size:12px;color:#475569;max-width:220px;">${escapeHtml(addressVal)}</div>
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
      const pwdInput = document.getElementById('tenant-password');
      if (pwdInput) pwdInput.value = '{{ \App\Helpers\PasswordHelper::DEFAULT_PASSWORD }}';
      ['company-name', 'tax-code', 'rep-name', 'contact-phone', 'contact-email', 'address', 'password'].forEach(f => {
        moClearFieldError(`tenant-${f}`, `err-tenant-${f}`);
      });
      modal.style.display = 'flex';
    }

    function closeCreateTenantModal() {
      const modal = document.getElementById('modal-create-tenant');
      if (modal) modal.style.display = 'none';
    }

    async function submitCreateTenant(e) {
      if (e) e.preventDefault();
      const form = document.getElementById('form-create-tenant');

      ['company-name', 'tax-code', 'rep-name', 'contact-phone', 'contact-email', 'address', 'password'].forEach(f => {
        moClearFieldError(`tenant-${f}`, `err-tenant-${f}`);
      });

      const companyName = document.getElementById('tenant-company-name')?.value.trim() || '';
      const taxCode = document.getElementById('tenant-tax-code')?.value.trim() || '';
      const repName = document.getElementById('tenant-rep-name')?.value.trim() || '';
      const contactPhone = document.getElementById('tenant-contact-phone')?.value.trim() || '';
      const loginEmail = document.getElementById('tenant-contact-email')?.value.trim() || '';
      const headquarterAddress = document.getElementById('tenant-address')?.value.trim() || '';
      const password = document.getElementById('tenant-password')?.value || '';
      const status = document.getElementById('tenant-status')?.value || 'ACTIVE';

      // 1. Validation 0ms
      if (!companyName || companyName.length < 2) {
        moSetFieldError('tenant-company-name', 'err-tenant-company-name', 'Tên doanh nghiệp / công ty là bắt buộc (tối thiểu 2 ký tự).');
        document.getElementById('tenant-company-name')?.focus();
        return;
      }

      const mstRegex = /^[0-9]{10}(-[0-9]{3})?$/;
      if (!taxCode || !mstRegex.test(taxCode)) {
        moSetFieldError('tenant-tax-code', 'err-tenant-tax-code', 'Mã số thuế là bắt buộc (10 hoặc 13 chữ số, VD: 0312345678).');
        document.getElementById('tenant-tax-code')?.focus();
        return;
      }

      if (!repName || repName.length < 2) {
        moSetFieldError('tenant-rep-name', 'err-tenant-rep-name', 'Họ tên người đại diện pháp luật là bắt buộc (tối thiểu 2 ký tự).');
        document.getElementById('tenant-rep-name')?.focus();
        return;
      }

      const vPhone = moValidatePhone(contactPhone);
      if (!vPhone.valid) {
        moSetFieldError('tenant-contact-phone', 'err-tenant-contact-phone', vPhone.message);
        document.getElementById('tenant-contact-phone')?.focus();
        return;
      }

      const vEmail = moValidateEmail(loginEmail);
      if (!vEmail.valid) {
        moSetFieldError('tenant-contact-email', 'err-tenant-contact-email', vEmail.message);
        document.getElementById('tenant-contact-email')?.focus();
        return;
      }

      if (!headquarterAddress || headquarterAddress.length < 5) {
        moSetFieldError('tenant-address', 'err-tenant-address', 'Địa chỉ trụ sở chính theo ĐKKD là bắt buộc.');
        document.getElementById('tenant-address')?.focus();
        return;
      }

      if (password && typeof moEvaluatePassword === 'function' && !moEvaluatePassword(password).valid) {
        moSetFieldError('tenant-password', 'err-tenant-password', 'Mật khẩu phải tối thiểu 8 ký tự, gồm chữ hoa, chữ thường, chữ số và ký tự đặc biệt.');
        document.getElementById('tenant-password')?.focus();
        return;
      }

      const payload = {
        company_name: companyName,
        tax_code: taxCode,
        representative_name: repName,
        contact_phone: contactPhone,
        login_email: loginEmail,
        billing_email: loginEmail,
        headquarter_address: headquarterAddress,
        status: status
      };
      if (password.trim() !== '') {
        payload.password = password.trim();
      }

      const btn = document.getElementById('btn-submit-create-tenant');
      const originalHtml = btn ? btn.innerHTML : '';
      if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Đang lưu hồ sơ...';
      }

      try {
        const res = await fetch('/api/v1/admin/tenants', {
          method: 'POST',
          headers: getAuthHeaders(),
          body: JSON.stringify(payload)
        });

        const json = await res.json();
        if (res.ok) {
          moToast(`Đã tạo hồ sơ khách thuê [${companyName}] thành công!`, 'success');
          closeCreateTenantModal();
          form?.reset();
          loadTenantsList();
        } else {
          if (json.errors) {
            moMapServerErrors(json.errors, {
              company_name: ['tenant-company-name', 'err-tenant-company-name'],
              tax_code: ['tenant-tax-code', 'err-tenant-tax-code'],
              representative_name: ['tenant-rep-name', 'err-tenant-rep-name'],
              contact_phone: ['tenant-contact-phone', 'err-tenant-contact-phone'],
              login_email: ['tenant-contact-email', 'err-tenant-contact-email'],
              headquarter_address: ['tenant-address', 'err-tenant-address'],
              password: ['tenant-password', 'err-tenant-password']
            });
          }
          moToast(json.message || 'Lỗi tạo khách thuê. Vui lòng kiểm tra lại.', 'error');
        }
      } catch (err) {
        moToast('Lỗi kết nối máy chủ API: ' + (err.message || 'Lỗi mạng'), 'error');
      } finally {
        if (btn) {
          btn.disabled = false;
          btn.innerHTML = originalHtml;
        }
      }
    }

    // 4. Mở Modal sửa thông tin pháp lý: PUT /api/v1/admin/tenants/{id}
    let currentEditTenantId = null;
    async function openEditTenantModal(tenantId) {
      currentEditTenantId = tenantId;
      const modal = document.getElementById('modal-edit-tenant');
      if (!modal) return;

      moClearFieldError('edit-tenant-company-name', 'err-edit-tenant-company-name');
      moClearFieldError('edit-tenant-tax-code', 'err-edit-tenant-tax-code');
      moClearFieldError('edit-tenant-rep-name', 'err-edit-tenant-rep-name');
      moClearFieldError('edit-tenant-contact-phone', 'err-edit-tenant-contact-phone');
      moClearFieldError('edit-tenant-contact-email', 'err-edit-tenant-contact-email');

      modal.style.display = 'flex';

      try {
        const res = await fetch(`/api/v1/admin/tenants/${tenantId}`, {
          method: 'GET',
          headers: getAuthHeaders()
        });
        const json = await res.json();
        if (!res.ok) {
          moToast('Lỗi tải thông tin khách thuê: ' + (json.message || ''), 'error');
          closeEditTenantModal();
          return;
        }

        const t = json.data;
        document.getElementById('edit-tenant-company-name').value = t.company_name || '';
        document.getElementById('edit-tenant-tax-code').value = t.tax_code || '';
        document.getElementById('edit-tenant-rep-name').value = t.representative_name || '';
        document.getElementById('edit-tenant-contact-phone').value = t.contact_phone || '';
        document.getElementById('edit-tenant-contact-email').value = t.login_email || t.billing_email || t.contact_email || '';
        document.getElementById('edit-tenant-address').value = t.headquarter_address || t.business_address || '';
        document.getElementById('edit-tenant-status').value = t.status || 'ACTIVE';
      } catch (err) {
        moToast('Lỗi kết nối khi tải hồ sơ khách thuê.', 'error');
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

      moClearFieldError('edit-tenant-company-name', 'err-edit-tenant-company-name');
      moClearFieldError('edit-tenant-tax-code', 'err-edit-tenant-tax-code');
      moClearFieldError('edit-tenant-rep-name', 'err-edit-tenant-rep-name');
      moClearFieldError('edit-tenant-contact-phone', 'err-edit-tenant-contact-phone');
      moClearFieldError('edit-tenant-contact-email', 'err-edit-tenant-contact-email');

      const companyName = document.getElementById('edit-tenant-company-name')?.value.trim() || '';
      const taxCode = document.getElementById('edit-tenant-tax-code')?.value.trim() || '';
      const repName = document.getElementById('edit-tenant-rep-name')?.value.trim() || '';
      const contactPhone = document.getElementById('edit-tenant-contact-phone')?.value.trim() || '';
      const contactEmail = document.getElementById('edit-tenant-contact-email')?.value.trim() || '';
      const businessAddress = document.getElementById('edit-tenant-address')?.value.trim() || '';
      const status = document.getElementById('edit-tenant-status')?.value || 'ACTIVE';

      // 1. Validation 0ms
      if (!companyName || companyName.length < 2) {
        moSetFieldError('edit-tenant-company-name', 'err-edit-tenant-company-name', 'Tên doanh nghiệp / công ty là bắt buộc (tối thiểu 2 ký tự).');
        document.getElementById('edit-tenant-company-name')?.focus();
        return;
      }

      if (taxCode) {
        const mstRegex = /^[0-9]{10}(-[0-9]{3})?$/;
        if (!mstRegex.test(taxCode)) {
          moSetFieldError('edit-tenant-tax-code', 'err-edit-tenant-tax-code', 'Mã số thuế phải có 10 hoặc 13 chữ số (VD: 0312345678 hoặc 0312345678-001).');
          document.getElementById('edit-tenant-tax-code')?.focus();
          return;
        }
      }

      if (repName && repName.length < 2) {
        moSetFieldError('edit-tenant-rep-name', 'err-edit-tenant-rep-name', 'Họ tên người đại diện tối thiểu 2 ký tự.');
        document.getElementById('edit-tenant-rep-name')?.focus();
        return;
      }

      if (contactPhone) {
        const vPhone = moValidatePhone(contactPhone);
        if (!vPhone.valid) {
          moSetFieldError('edit-tenant-contact-phone', 'err-edit-tenant-contact-phone', vPhone.message);
          document.getElementById('edit-tenant-contact-phone')?.focus();
          return;
        }
      }

      if (contactEmail) {
        const vEmail = moValidateEmail(contactEmail);
        if (!vEmail.valid) {
          moSetFieldError('edit-tenant-contact-email', 'err-edit-tenant-contact-email', vEmail.message);
          document.getElementById('edit-tenant-contact-email')?.focus();
          return;
        }
      }

      const payload = {
        company_name: companyName,
        tax_code: taxCode || null,
        representative_name: repName || null,
        contact_phone: contactPhone || null,
        login_email: contactEmail || null,
        headquarter_address: businessAddress || null,
        status: status
      };

      const btn = document.getElementById('btn-submit-edit-tenant');
      const originalHtml = btn ? btn.innerHTML : '';
      if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Đang cập nhật...';
      }

      try {
        const res = await fetch(`/api/v1/admin/tenants/${currentEditTenantId}`, {
          method: 'PUT',
          headers: getAuthHeaders(),
          body: JSON.stringify(payload)
        });

        const json = await res.json();
        if (res.ok) {
          moToast(`Đã cập nhật hồ sơ khách thuê [${companyName}] thành công!`, 'success');
          closeEditTenantModal();
          loadTenantsList();
        } else {
          if (json.errors) {
            moMapServerErrors(json.errors, {
              company_name: ['edit-tenant-company-name', 'err-edit-tenant-company-name'],
              tax_code: ['edit-tenant-tax-code', 'err-edit-tenant-tax-code'],
              representative_name: ['edit-tenant-rep-name', 'err-edit-tenant-rep-name'],
              contact_phone: ['edit-tenant-contact-phone', 'err-edit-tenant-contact-phone'],
              login_email: ['edit-tenant-contact-email', 'err-edit-tenant-contact-email'],
              headquarter_address: ['edit-tenant-address', 'err-edit-tenant-address']
            });
          }
          moToast(json.message || 'Lỗi cập nhật. Vui lòng kiểm tra lại.', 'error');
        }
      } catch (err) {
        moToast('Lỗi kết nối cập nhật khách thuê: ' + (err.message || 'Lỗi mạng'), 'error');
      } finally {
        if (btn) {
          btn.disabled = false;
          btn.innerHTML = originalHtml;
        }
      }
    }

    // 5. Tải lên Giấy phép kinh doanh: POST /api/v1/admin/tenants/{id}/upload-license
    function openUploadLicenseModal(tenantId, companyName) {
      currentUploadLicenseTenantId = tenantId;
      const modal = document.getElementById('modal-upload-license');
      if (!modal) return;
      const compNameEl = document.getElementById('upload-license-company-name');
      if (compNameEl) compNameEl.innerText = companyName;
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

    function doTenantSearch() {
      const inp = document.getElementById('tenant-search-input');
      handleTenantSearch(inp ? inp.value : '');
    }

    function handleTenantSearch(val) {
      currentTenantSearch = val.trim();
      loadTenantsList();
    }

    function handleTenantStatusFilter(status) {
      currentTenantStatus = status;
      const sel = document.getElementById('tenant-status-select');
      if (sel && sel.value !== status) sel.value = status;
      document.querySelectorAll('.tenant-status-btn').forEach(b => {
        b.classList.remove('active');
        if (b.getAttribute('data-status') === status) b.classList.add('active');
      });
      loadTenantsList();
    }

    function resetTenantFilters() {
      const inp = document.getElementById('tenant-search-input');
      if (inp) inp.value = '';
      const clrBtn = document.getElementById('btn-clear-tenant-search');
      if (clrBtn) clrBtn.style.display = 'none';
      const sel = document.getElementById('tenant-status-select');
      if (sel) sel.value = 'ALL';
      currentTenantSearch = '';
      currentTenantStatus = 'ALL';
      document.querySelectorAll('.tenant-status-btn').forEach(b => {
        b.classList.remove('active');
        if (b.getAttribute('data-status') === 'ALL') b.classList.add('active');
      });
      loadTenantsList();
    }

    window.handleTenantStatusFilter = handleTenantStatusFilter;
    window.resetTenantFilters = resetTenantFilters;
</script>
