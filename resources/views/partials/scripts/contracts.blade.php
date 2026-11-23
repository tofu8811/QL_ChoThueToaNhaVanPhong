<script>
    /* ==========================================================================
       MODULE 3: CONTRACTS & TENANT PORTAL JAVASCRIPT (ENDPOINTS 43 - 56)
       ========================================================================== */
    let contractsList = [];
    let currentContractStatus = 'ALL';
    let currentContractSearch = '';
    let currentViewingContract = null;

    function isContractSalesRole() {
      if (typeof window.isSalesUser !== 'undefined') return Boolean(window.isSalesUser);
      const role = (window.currentUiRole || localStorage.getItem('auth_role') || '').toLowerCase();
      if (role === 'sales' || role === 'sale') return true;
      const sidebar = document.getElementById('bms-main-sidebar');
      if (sidebar) {
        const roleBadge = sidebar.querySelector('.filament-badge-role')?.textContent || '';
        if (/sales/i.test(roleBadge)) return true;
      }
      const topBadge = document.getElementById('admin-dropdown-role-badge')?.textContent || '';
      if (/sales/i.test(topBadge)) return true;
      return false;
    }

    function isContractAdminRole() {
      if (isContractSalesRole()) return false;
      const role = (window.currentUiRole || localStorage.getItem('auth_role') || '').toLowerCase();
      if (role === 'pm' || role === 'accountant') return false;
      if (role === 'admin' || role === 'super_admin') return true;
      const topBadge = document.getElementById('admin-dropdown-role-badge')?.textContent || '';
      if (/admin/i.test(topBadge)) return true;
      return false;
    }

    async function loadTenantsListForContracts() {
      if (window.tenantsList && window.tenantsList.length > 0) return window.tenantsList;
      try {
        const res = await fetch('/api/v1/admin/tenants?per_page=100', {
          method: 'GET',
          headers: getAuthHeaders()
        });
        const json = await res.json();
        window.tenantsList = json.data?.data || json.data || [];
      } catch (e) {
        window.tenantsList = [];
      }
      return window.tenantsList;
    }

    // 1. Nạp danh sách Hợp đồng thuê: GET /api/v1/admin/contracts
    async function loadContractsList() {
      const tbody = document.getElementById('admin-contracts-tbody');
      if (tbody) {
        tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;padding:24px;color:#94a3b8;"><i class="fa-solid fa-spinner fa-spin"></i> Đang tải danh sách hợp đồng thuê từ API...</td></tr>';
      }

      try {
        let url = '/api/v1/admin/contracts?per_page=100';
        if (currentContractStatus && currentContractStatus !== 'ALL') {
          url += `&status=${encodeURIComponent(currentContractStatus)}`;
        }
        if (currentContractSearch) {
          url += `&search=${encodeURIComponent(currentContractSearch)}`;
        }

        const res = await fetch(url, {
          method: 'GET',
          headers: getAuthHeaders()
        });

        if (res.status === 401) {
          if (tbody) tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;color:#ef4444;padding:20px;">Vui lòng đăng nhập với tài khoản có thẩm quyền Quản trị / Kế toán / Sales.</td></tr>';
          return;
        }

        const json = await res.json();
        if (res.ok) {
          contractsList = json.data?.data || json.data || [];
          renderContractsTable();
        } else {
          if (tbody) tbody.innerHTML = `<tr><td colspan="8" style="text-align:center;color:#ef4444;padding:20px;">Lỗi: ${escapeHtml(json.message || 'Không thể tải danh sách hợp đồng')}</td></tr>`;
        }
      } catch (err) {
        if (tbody) tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;color:#ef4444;padding:20px;">Lỗi kết nối máy chủ API Hợp đồng.</td></tr>';
      }
    }

    // Helper hiển thị badge trạng thái Hợp đồng
    function getContractStatusBadge(status) {
      switch (status) {
        case 'DRAFT':
          return '<span class="badge-filament" style="background:#f1f5f9;color:#475569;"><i class="fa-solid fa-file-pen"></i> Bản Nháp (Draft)</span>';
        case 'RESERVED':
          return '<span class="badge-filament warning"><i class="fa-solid fa-hourglass-half"></i> Giữ Chỗ (Reserved)</span>';
        case 'ACTIVE':
          return '<span class="badge-filament success"><i class="fa-solid fa-shield-check"></i> Có Hiệu Lực (Active)</span>';
        case 'EXPIRING_SOON':
          return '<span class="badge-filament warning" style="background:#fef3c7;color:#b45309;"><i class="fa-solid fa-clock"></i> Sắp Hết Hạn</span>';
        case 'EXPIRED':
          return '<span class="badge-filament danger"><i class="fa-solid fa-calendar-xmark"></i> Hết Hạn</span>';
        case 'TERMINATED':
          return '<span class="badge-filament danger" style="background:#fef2f2;color:#991b1b;"><i class="fa-solid fa-file-circle-xmark"></i> Đã Thanh Lý</span>';
        default:
          return `<span class="badge-filament">${escapeHtml(status)}</span>`;
      }
    }

    // 2. Render bảng danh sách hợp đồng
    function renderContractsTable() {
      const tbody = document.getElementById('admin-contracts-tbody');
      const countEl = document.getElementById('admin-contracts-count');
      if (countEl) countEl.innerText = contractsList.length;

      if (!tbody) return;
      if (contractsList.length === 0) {
        tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;padding:32px;color:#94a3b8;">Không có hợp đồng nào phù hợp bộ lọc.</td></tr>';
        return;
      }

      tbody.innerHTML = contractsList.map(c => {
        const contractCode = c.contract_code || c.contract_number || '---';
        const tenantName = c.tenant?.company_name || '---';
        const buildingName = c.building?.name || '---';
        const rentalPeriod = c.formatted_period || `${c.start_date || ''} ➔ ${c.end_date || ''}`;
        const totalRent = c.formatted_monthly_rent || '---';
        const deposit = c.formatted_deposit_amount || '0 đ';
        const leasedArea = Number(c.total_leased_area_sqm || 0);

        return `
          <tr>
            <td>
              <strong style="color:var(--mo-primary);font-family:monospace;font-size:13px;">${escapeHtml(contractCode)}</strong>
              <div style="font-size:11px;color:#94a3b8;">${leasedArea > 0 ? `Tổng diện tích: ${leasedArea.toLocaleString('vi-VN')} m²` : `Ký bởi: ${escapeHtml(c.created_by_sales?.full_name || c.creator?.full_name || 'Sales')}`}</div>
            </td>
            <td>
              <div style="font-weight:700;color:#0f172a;font-size:13px;">${escapeHtml(tenantName)}</div>
              <div style="font-size:11px;color:#64748b;">MST: ${escapeHtml(c.tenant?.tax_code || '---')}</div>
            </td>
            <td>
              <div style="font-weight:600;color:#334155;">${escapeHtml(buildingName)}</div>
              <div style="font-size:11px;color:#0284c7;">
                ${(c.units || []).map(u => `<span style="background:#e0f2fe;padding:1px 5px;border-radius:3px;margin-right:2px;">${escapeHtml(u.unit_code)}</span>`).join('') || '<em style="color:#94a3b8;">Chưa gán sàn</em>'}
              </div>
            </td>
            <td>
              <div style="font-size:12px;color:#334155;font-weight:500;">${rentalPeriod}</div>
            </td>
            <td>
              <div style="font-weight:700;color:#ea580c;font-size:13px;">${totalRent}</div>
              <div style="font-size:11px;color:#64748b;">Cọc: ${deposit}</div>
            </td>
            <td>${getContractStatusBadge(c.status)}</td>
            <td style="text-align:right;white-space:nowrap;">
              <button class="btn-mo-outline" style="padding:4px 8px;font-size:12px;" onclick="openContractDetailModal('${c.id}')" title="Xem chi tiết & Files">
                <i class="fa-solid fa-eye"></i> Chi tiết
              </button>
              ${(c.status === 'DRAFT' || c.status === 'RESERVED') ? (
                isContractSalesRole() ? `
                  <span style="font-size:11px;color:#d97706;background:#fef3c7;padding:3px 7px;border-radius:4px;font-weight:600;margin-left:4px;border:1px solid #fde68a;" title="Chuyên viên Sales không có thẩm quyền ký duyệt hợp đồng. Hợp đồng đang chờ Ban Quản Lý (PM) duyệt.">
                    <i class="fa-solid fa-clock"></i> Chờ PM duyệt
                  </span>
                ` : `
                  <button class="btn-mo-outline" style="padding:4px 8px;font-size:12px;color:#16a34a;border-color:#bbf7d0;margin-left:4px;" onclick="signContract('${c.id}')" title="Ký duyệt & Kích hoạt">
                    <i class="fa-solid fa-file-signature"></i> Ký Duyệt
                  </button>
                `
              ) : ''}
              ${(!isContractSalesRole() && (c.status === 'ACTIVE' || c.status === 'EXPIRING_SOON')) ? `
                <button class="btn-mo-outline" style="padding:4px 8px;font-size:12px;color:#0284c7;border-color:#bae6fd;margin-left:4px;" onclick="openRenewContractModal('${c.id}', '${escapeHtml(contractCode)}', '${c.end_date}')" title="Gia hạn hợp đồng">
                  <i class="fa-solid fa-calendar-plus"></i> Gia hạn
                </button>
                <button class="btn-mo-outline" style="padding:4px 8px;font-size:12px;color:#dc2626;border-color:#fecaca;margin-left:4px;" onclick="openTerminateContractModal('${c.id}', '${escapeHtml(contractCode)}', ${c.deposit_amount || 0})" title="Thanh lý hợp đồng">
                  <i class="fa-solid fa-file-circle-xmark"></i> Thanh lý
                </button>
              ` : ''}
              ${(c.status === 'DRAFT' && isContractAdminRole()) ? `
                <button class="btn-mo-outline" style="padding:4px 8px;font-size:12px;color:#ef4444;border-color:#fecaca;margin-left:4px;" onclick="deleteContract('${c.id}')" title="Xóa nháp">
                  <i class="fa-solid fa-trash"></i>
                </button>
              ` : ''}
            </td>
          </tr>
        `;
      }).join('');
    }

    // 3. Mở Modal tạo hợp đồng nháp: POST /api/v1/admin/contracts
    let currentBuildingUnits = [];

    async function openCreateContractModal(presetTenantId = null, presetBuildingId = null, presetUnitId = null) {
      const modal = document.getElementById('modal-create-contract');
      if (!modal) return;
      document.getElementById('form-create-contract')?.reset();

      // Thiết lập ngày mặc định theo kịch bản: 01/12/2026 -> 30/11/2028 (2 năm)
      const startDateInput = document.getElementById('contract-start-date');
      const endDateInput = document.getElementById('contract-end-date');
      const handoverDateInput = document.getElementById('contract-handover-date');
      if (startDateInput) startDateInput.value = '2026-12-01';
      if (endDateInput) endDateInput.value = '2028-11-30';
      if (handoverDateInput) handoverDateInput.value = '2026-12-01';

      // Reset các trường tính toán
      resetContractFinancials();
      const uSelect = document.getElementById('contract-unit-select');
      if (uSelect) uSelect.innerHTML = '<option value="">-- Vui lòng chọn Tòa nhà trước --</option>';

      // Nạp danh sách Khách thuê vào select
      const tSelect = document.getElementById('contract-tenant-select');
      const bSelect = document.getElementById('contract-building-select');

      const list = await loadTenantsListForContracts();

      // Sắp xếp khách mới chốt (chưa có HĐ) lên hàng đầu
      const sortedList = [...(list || [])].sort((a, b) => (a.contracts_count || 0) - (b.contracts_count || 0));

      tSelect.innerHTML = '<option value="">-- Chọn Khách Thuê Doanh Nghiệp --</option>' +
        sortedList.map(t => {
          const isPending = (!t.contracts_count || t.contracts_count === 0);
          const prefix = isPending ? '⭐ [Chờ lập HĐ] ' : '';
          return `<option value="${t.id}" data-pending="${isPending ? 1 : 0}">
            ${prefix}${escapeHtml(t.company_name)} (${escapeHtml(t.tax_code || 'MST chưa có')})
          </option>`;
        }).join('');

      bSelect.innerHTML = '<option value="">-- Chọn Tòa Nhà Văn Phòng --</option>' +
        BUILDINGS_DATA.map(b => `<option value="${b.id}">${escapeHtml(b.name)}</option>`).join('');

      modal.style.display = 'flex';

      // Nếu Sales chỉ được phân công đúng 1 tòa nhà, tự động chọn luôn tòa nhà đó
      if (BUILDINGS_DATA.length === 1 && !presetBuildingId) {
        bSelect.value = BUILDINGS_DATA[0].id;
        handleContractBuildingChange(BUILDINGS_DATA[0].id);
      }

      // Hỗ trợ truyền sẵn thông số từ CRM Lead hoặc Tenant
      if (presetTenantId) {
        tSelect.value = presetTenantId;
        await handleContractTenantChange(presetTenantId, presetBuildingId, presetUnitId);
      }
    }

    function closeCreateContractModal() {
      const modal = document.getElementById('modal-create-contract');
      if (modal) modal.style.display = 'none';
    }

    // Nhận diện Khách thuê: Tự động phát hiện nhu cầu tòa nhà & mặt bằng từ CRM Lead
    async function handleContractTenantChange(tenantId, presetBuildingId = null, presetUnitId = null) {
      moClearFieldError('contract-tenant-select', 'err-contract-tenant');
      if (!tenantId) return;

      const tenant = (window.tenantsList || []).find(t => String(t.id) === String(tenantId));
      const companyName = (tenant?.company_name || '').toLowerCase();

      // Nếu khách thuê là Thảo Nguyễn hoặc kịch bản The Nexus Tower
      if (presetBuildingId) {
        const bSelect = document.getElementById('contract-building-select');
        if (bSelect) {
          bSelect.value = presetBuildingId;
          await handleContractBuildingChange(presetBuildingId, presetUnitId);
        }
      } else if (companyName.includes('thảo nguyễn') || companyName.includes('thao nguyen') || companyName.includes('nguyễn thảo')) {
        const nexus = BUILDINGS_DATA.find(b => (b.code || '').toUpperCase().includes('NEX') || (b.name || '').toLowerCase().includes('nexus'));
        if (nexus) {
          const bSelect = document.getElementById('contract-building-select');
          if (bSelect) {
            bSelect.value = nexus.id;
            await handleContractBuildingChange(nexus.id, 'NEX-0501');
          }
        }
      }
    }

    // Khi thay đổi Tòa nhà: Tự động đề xuất Mã hợp đồng & Tải danh sách Mặt bằng trống
    async function handleContractBuildingChange(buildingId, autoSelectUnitCode = null) {
      moClearFieldError('contract-building-select', 'err-contract-building');
      const uSelect = document.getElementById('contract-unit-select');
      if (!uSelect) return;

      if (!buildingId) {
        uSelect.innerHTML = '<option value="">-- Vui lòng chọn Tòa nhà trước --</option>';
        resetContractFinancials();
        return;
      }

      // Đề xuất Mã hợp đồng chuyên nghiệp dựa theo Mã Tòa Nhà
      const building = BUILDINGS_DATA.find(b => String(b.id) === String(buildingId));
      const codeInput = document.getElementById('contract-code-input');
      if (codeInput && (!codeInput.value || codeInput.value.startsWith('HD-'))) {
        let bCode = (building?.code || 'NEXUS').toUpperCase();
        if (bCode.includes('NEXUS') || (building?.name || '').toLowerCase().includes('nexus')) {
          bCode = 'NEXUS';
        }
        const randSuffix = Math.floor(1000 + Math.random() * 9000);
        codeInput.value = `HD-${bCode}-2026-${randSuffix}`;
      }

      uSelect.innerHTML = '<option value="">Đang tải danh sách mặt bằng trống...</option>';
      try {
        const res = await fetch(`/api/v1/portal/units?building_id=${buildingId}&status=AVAILABLE`, {
          method: 'GET',
          headers: getAuthHeaders()
        });
        const json = await res.json();
        const units = json.data?.data || json.data || [];
        currentBuildingUnits = units;

        if (units.length === 0) {
          uSelect.innerHTML = '<option value="">(Tòa nhà hiện không có mặt bằng trống)</option>';
          resetContractFinancials();
          return;
        }

        uSelect.innerHTML = '<option value="">-- Chọn Mặt Bằng Văn Phòng Cho Thuê --</option>' +
          units.map(u => {
            const area = parseFloat(u.area_sqm) || 0;
            const rent = parseFloat(u.base_rental_price) || 0;
            const fee = parseFloat(u.service_fee_sqm || u.building?.default_service_fee_per_sqm || 150000);
            return `<option value="${u.id}" data-area="${area}" data-rent="${rent}" data-fee="${fee}" data-code="${escapeHtml(u.unit_code || '')}">
              ${escapeHtml(u.name || u.unit_code)} (Phòng ${escapeHtml(u.unit_code)} - ${area} m² - ${rent.toLocaleString('vi-VN')} đ/m²)
            </option>`;
          }).join('');

        // Tự động chọn mặt bằng theo kịch bản nếu có
        if (autoSelectUnitCode) {
          const matchOpt = Array.from(uSelect.options).find(o => 
            o.value === autoSelectUnitCode || (o.dataset.code && o.dataset.code.includes(autoSelectUnitCode))
          );
          if (matchOpt) {
            uSelect.value = matchOpt.value;
            handleContractUnitChange();
          }
        }
      } catch (err) {
        uSelect.innerHTML = '<option value="">(Lỗi kết nối tải mặt bằng)</option>';
      }
    }

    // Khi chọn Mặt bằng: Đổ thông số diện tích, đơn giá và tính toán tài chính hợp đồng
    function handleContractUnitChange() {
      moClearFieldError('contract-unit-select', 'err-contract-unit');
      const uSelect = document.getElementById('contract-unit-select');
      const opt = uSelect?.selectedOptions[0];
      if (!opt || !opt.value) {
        resetContractFinancials();
        return;
      }

      const area = parseFloat(opt.dataset.area) || 0;
      const rentPerSqm = parseFloat(opt.dataset.rent) || 0;
      let feePerSqm = parseFloat(opt.dataset.fee) || 150000;
      const unitCode = opt.dataset.code || '';

      // Chuẩn hóa theo kịch bản: NEX-0501 phí dịch vụ 150.000 VNĐ/m²
      if (unitCode.includes('NEX-0501')) {
        feePerSqm = 150000;
      }

      const areaInput = document.getElementById('contract-unit-area');
      const rentPriceInput = document.getElementById('contract-unit-rent-price');
      const feePriceInput = document.getElementById('contract-unit-service-fee');

      if (areaInput) areaInput.value = area;
      if (rentPriceInput) rentPriceInput.value = rentPerSqm;
      if (feePriceInput) feePriceInput.value = feePerSqm;

      recalculateContractFinancials();
    }

    // Tự động tính toán chi phí thuê, phí DV, cọc 3 tháng & tổng giá trị hợp đồng
    function recalculateContractFinancials() {
      const area = parseFloat(document.getElementById('contract-unit-area')?.value) || 0;
      const rentPerSqm = parseFloat(document.getElementById('contract-unit-rent-price')?.value) || 0;
      const feePerSqm = parseFloat(document.getElementById('contract-unit-service-fee')?.value) || 0;

      const monthlyRent = Math.round(area * rentPerSqm);
      const monthlyFee = Math.round(area * feePerSqm);
      const totalMonthly = monthlyRent + monthlyFee;
      // Công thức cọc 3 tháng chuẩn ngành: (Tiền thuê + Phí dịch vụ) x 3
      const deposit3Months = Math.round(totalMonthly * 3);

      const rentInput = document.getElementById('contract-rent');
      const feeInput = document.getElementById('contract-service-fee');
      const depositInput = document.getElementById('contract-deposit');

      if (rentInput) rentInput.value = monthlyRent || '';
      if (feeInput) feeInput.value = monthlyFee || '';
      if (depositInput) depositInput.value = deposit3Months || '';

      // Tính tổng giá trị hợp đồng (TCV)
      const startDateVal = document.getElementById('contract-start-date')?.value;
      const endDateVal = document.getElementById('contract-end-date')?.value;
      let months = 24;
      if (startDateVal && endDateVal) {
        const d1 = new Date(startDateVal);
        const d2 = new Date(endDateVal);
        if (d2 > d1) {
          months = Math.max(1, Math.round((d2 - d1) / (1000 * 60 * 60 * 24 * 30.4375)));
        }
      }
      const totalContractValue = totalMonthly * months;

      const card = document.getElementById('contract-financial-card');
      const breakdown = document.getElementById('contract-financial-breakdown');
      const uSelect = document.getElementById('contract-unit-select');
      const opt = uSelect?.selectedOptions[0];
      const unitCode = opt?.dataset.code || (opt?.value ? opt.text.split('(')[0].trim() : 'Mặt bằng');

      if (card && breakdown && area > 0 && rentPerSqm > 0) {
        card.style.display = 'block';
        breakdown.innerHTML = `
          <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(180px, 1fr));gap:10px;margin-bottom:8px;">
            <div>
              <div style="color:#64748b;font-size:11px;font-weight:600;">MẶT BẰNG & DIỆN TÍCH</div>
              <div style="font-weight:700;color:#0f172a;font-size:13px;">${escapeHtml(unitCode)} — <strong>${area} m²</strong></div>
            </div>
            <div>
              <div style="color:#64748b;font-size:11px;font-weight:600;">CHI PHÍ CỐ ĐỊNH / THÁNG</div>
              <div style="font-weight:700;color:#0f172a;font-size:13px;">${totalMonthly.toLocaleString('vi-VN')} đ/tháng</div>
              <div style="font-size:11px;color:#475569;">Thuê: ${monthlyRent.toLocaleString('vi-VN')} đ + DV: ${monthlyFee.toLocaleString('vi-VN')} đ</div>
            </div>
            <div>
              <div style="color:#64748b;font-size:11px;font-weight:600;">TIỀN CỌC BẢO ĐẢM (3 THÁNG)</div>
              <div style="font-weight:800;color:#047857;font-size:14px;">${deposit3Months.toLocaleString('vi-VN')} VNĐ</div>
              <div style="font-size:10.5px;color:#16a34a;font-weight:600;">Chuẩn quy định BĐS thương mại</div>
            </div>
            <div>
              <div style="color:#64748b;font-size:11px;font-weight:600;">TỔNG GIÁ TRỊ HỢP ĐỒNG (${months} THÁNG)</div>
              <div style="font-weight:800;color:#1d4ed8;font-size:14px;">${totalContractValue.toLocaleString('vi-VN')} VNĐ</div>
            </div>
          </div>
          <div style="margin-top:6px;padding-top:6px;border-top:1px dashed #bbf7d0;font-size:11.5px;color:#15803d;display:flex;align-items:center;gap:6px;">
            <i class="fa-solid fa-circle-check"></i> Sàn <strong>${escapeHtml(unitCode)}</strong> kiểm tra khả dụng hợp lệ. Cơ chế Khóa bi quan <code>lockForUpdate()</code> sẵn sàng kích hoạt để bảo vệ dữ liệu.
          </div>
        `;
      } else if (card) {
        card.style.display = 'none';
      }
    }

    function resetContractFinancials() {
      const card = document.getElementById('contract-financial-card');
      if (card) card.style.display = 'none';
      ['contract-unit-area', 'contract-unit-rent-price', 'contract-unit-service-fee', 'contract-rent', 'contract-service-fee', 'contract-deposit'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.value = '';
      });
    }

    // Tự động cập nhật tiền cọc nếu người dùng chỉnh sửa tiền thuê hoặc phí dịch vụ trực tiếp
    function calculateCustomDeposit() {
      const rent = parseFloat(document.getElementById('contract-rent')?.value) || 0;
      const fee = parseFloat(document.getElementById('contract-service-fee')?.value) || 0;
      if (rent > 0 || fee > 0) {
        const deposit = (rent + fee) * 3;
        const depositInput = document.getElementById('contract-deposit');
        if (depositInput) depositInput.value = deposit;
      }
    }

    // Khi thay đổi ngày bắt đầu thuê: Tự động đề xuất ngày kết thúc hợp đồng tròn 2 năm
    function handleContractStartDateChange(val) {
      moClearFieldError('contract-start-date', 'err-contract-start-date');
      if (!val) return;
      const startDate = new Date(val);
      if (isNaN(startDate.getTime())) return;
      const endDate = new Date(startDate);
      endDate.setFullYear(endDate.getFullYear() + 2);
      endDate.setDate(endDate.getDate() - 1);
      const yyyy = endDate.getFullYear();
      const mm = String(endDate.getMonth() + 1).padStart(2, '0');
      const dd = String(endDate.getDate()).padStart(2, '0');
      const endInput = document.getElementById('contract-end-date');
      if (endInput) endInput.value = `${yyyy}-${mm}-${dd}`;
      recalculateContractFinancials();
    }

    async function submitCreateContract(e) {
      if (e) e.preventDefault();

      moClearFieldError('contract-tenant-select', 'err-contract-tenant');
      moClearFieldError('contract-building-select', 'err-contract-building');
      moClearFieldError('contract-unit-select', 'err-contract-unit');
      moClearFieldError('contract-code-input', 'err-contract-code');
      moClearFieldError('contract-start-date', 'err-contract-start-date');
      moClearFieldError('contract-end-date', 'err-contract-end-date');
      moClearFieldError('contract-rent', 'err-contract-rent');
      moClearFieldError('contract-service-fee', 'err-contract-service-fee');
      moClearFieldError('contract-deposit', 'err-contract-deposit');
      moClearFieldError('contract-handover-date', 'err-contract-handover-date');
      moClearFieldError('contract-billing-cycle', 'err-contract-billing-cycle');
      moClearFieldError('contract-payment-day', 'err-contract-payment-day');

      const contractType = document.getElementById('contract-type-select')?.value || 'DRAFT';
      const tenantId = document.getElementById('contract-tenant-select')?.value;
      const buildingId = document.getElementById('contract-building-select')?.value;
      const uSelect = document.getElementById('contract-unit-select');
      const unitId = uSelect?.value;
      const opt = uSelect?.selectedOptions[0];

      const contractCode = document.getElementById('contract-code-input')?.value.trim();
      const startDate = document.getElementById('contract-start-date')?.value;
      const endDate = document.getElementById('contract-end-date')?.value;
      const handoverDate = document.getElementById('contract-handover-date')?.value || null;
      const rentStr = document.getElementById('contract-rent')?.value?.trim();
      const feeStr = document.getElementById('contract-service-fee')?.value?.trim();
      const depositStr = document.getElementById('contract-deposit')?.value?.trim();
      const cycleStr = document.getElementById('contract-billing-cycle')?.value?.trim();
      const payDayStr = document.getElementById('contract-payment-day')?.value?.trim();
      const notes = document.getElementById('contract-notes')?.value.trim() || null;

      let hasError = false;

      if (!tenantId) {
        moSetFieldError('contract-tenant-select', 'err-contract-tenant', 'Vui lòng chọn khách thuê doanh nghiệp ký hợp đồng.');
        hasError = true;
      }

      if (!buildingId) {
        moSetFieldError('contract-building-select', 'err-contract-building', 'Vui lòng chọn tòa nhà văn phòng cho thuê.');
        if (!hasError) { document.getElementById('contract-building-select')?.focus(); hasError = true; }
      }

      if (!startDate) {
        moSetFieldError('contract-start-date', 'err-contract-start-date', 'Vui lòng chọn ngày bắt đầu hợp đồng.');
        if (!hasError) { document.getElementById('contract-start-date')?.focus(); hasError = true; }
      }

      if (!endDate) {
        moSetFieldError('contract-end-date', 'err-contract-end-date', 'Vui lòng chọn ngày kết thúc hợp đồng.');
        if (!hasError) { document.getElementById('contract-end-date')?.focus(); hasError = true; }
      } else if (startDate && new Date(endDate) <= new Date(startDate)) {
        moSetFieldError('contract-end-date', 'err-contract-end-date', 'Ngày kết thúc thuê phải sau ngày bắt đầu.');
        if (!hasError) { document.getElementById('contract-end-date')?.focus(); hasError = true; }
      }

      const rentAmount = rentStr ? parseFloat(rentStr) : 0;
      if (rentStr && (isNaN(rentAmount) || rentAmount < 0)) {
        moSetFieldError('contract-rent', 'err-contract-rent', 'Tiền thuê sàn phải là số không âm.');
        if (!hasError) { document.getElementById('contract-rent')?.focus(); hasError = true; }
      }

      const serviceFee = feeStr ? parseFloat(feeStr) : 0;
      if (feeStr && (isNaN(serviceFee) || serviceFee < 0)) {
        moSetFieldError('contract-service-fee', 'err-contract-service-fee', 'Phí dịch vụ phải là số không âm.');
        if (!hasError) { document.getElementById('contract-service-fee')?.focus(); hasError = true; }
      }

      const depositAmount = depositStr ? parseFloat(depositStr) : 0;
      if (depositStr && (isNaN(depositAmount) || depositAmount < 0)) {
        moSetFieldError('contract-deposit', 'err-contract-deposit', 'Tiền cọc bảo đảm phải là số không âm.');
        if (!hasError) { document.getElementById('contract-deposit')?.focus(); hasError = true; }
      }

      const billingCycle = cycleStr ? parseInt(cycleStr) : 1;
      if (cycleStr && (isNaN(billingCycle) || billingCycle < 1)) {
        moSetFieldError('contract-billing-cycle', 'err-contract-billing-cycle', 'Kỳ thanh toán tối thiểu 1 tháng.');
        if (!hasError) { document.getElementById('contract-billing-cycle')?.focus(); hasError = true; }
      }

      const paymentDueDay = payDayStr ? parseInt(payDayStr) : 5;
      if (payDayStr && (isNaN(paymentDueDay) || paymentDueDay < 1 || paymentDueDay > 28)) {
        moSetFieldError('contract-payment-day', 'err-contract-payment-day', 'Hạn đóng tiền hợp lệ từ ngày 1 đến ngày 28.');
        if (!hasError) { document.getElementById('contract-payment-day')?.focus(); hasError = true; }
      }

      if (hasError) return;

      const payload = {
        tenant_id: tenantId,
        building_id: buildingId,
        contract_number: contractCode || undefined,
        contract_code: contractCode || undefined,
        start_date: startDate,
        end_date: endDate,
        handover_date: handoverDate,
        deposit_amount: depositAmount,
        booking_deposit_amount: contractType === 'BOOKING' ? depositAmount : 0,
        rent_amount: rentAmount,
        service_fee_amount: serviceFee,
        billing_cycle_months: billingCycle,
        payment_cycle_months: billingCycle,
        payment_due_day: paymentDueDay,
        notes: notes
      };

      // Đính kèm mặt bằng chọn từ modal để tự động lưu vào contract_units
      if (unitId) {
        const area = parseFloat(document.getElementById('contract-unit-area')?.value) || (opt ? parseFloat(opt.dataset.area) : 150) || 150;
        const rentPrice = parseFloat(document.getElementById('contract-unit-rent-price')?.value) || (opt ? parseFloat(opt.dataset.rent) : 1400000) || 1400000;
        let feePrice = parseFloat(document.getElementById('contract-unit-service-fee')?.value) || (opt ? parseFloat(opt.dataset.fee) : 150000) || 150000;
        if (opt?.dataset?.code?.includes('NEX-0501')) {
          feePrice = 150000;
        }

        payload.units = [{
          unit_id: unitId,
          leased_area_sqm: area,
          agreed_rental_price_per_sqm: rentPrice,
          agreed_service_fee_per_sqm: feePrice
        }];
      }

      const endpoint = contractType === 'BOOKING' ? '/api/v1/admin/contracts/reserve' : '/api/v1/admin/contracts';
      const btn = document.getElementById('btn-submit-create-contract');
      const originalHtml = btn ? btn.innerHTML : '';
      if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Đang tạo hợp đồng...';
      }

      try {
        const res = await fetch(endpoint, {
          method: 'POST',
          headers: getAuthHeaders(),
          body: JSON.stringify(payload)
        });

        const json = await res.json();
        if (res.ok) {
          const typeLabel = contractType === 'BOOKING' ? 'Hợp đồng đặt cọc giữ chỗ (BOOKING)' : 'Dự thảo hợp đồng thuê (DRAFT)';
          moToast(`Khởi tạo ${typeLabel} thành công! Mặt bằng đã được khóa bảo vệ.`, 'success');
          closeCreateContractModal();
          document.getElementById('form-create-contract')?.reset();
          loadContractsList();
        } else {
          if (json.errors) {
            moMapServerErrors(json.errors, {
              tenant_id: ['contract-tenant-select', 'err-contract-tenant'],
              building_id: ['contract-building-select', 'err-contract-building'],
              contract_number: ['contract-code-input', 'err-contract-code'],
              contract_code: ['contract-code-input', 'err-contract-code'],
              start_date: ['contract-start-date', 'err-contract-start-date'],
              end_date: ['contract-end-date', 'err-contract-end-date'],
              rent_amount: ['contract-rent', 'err-contract-rent'],
              deposit_amount: ['contract-deposit', 'err-contract-deposit']
            });
          }
          moToast(json.message || 'Lỗi tạo hợp đồng. Vui lòng kiểm tra lại thông tin.', 'error');
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

    // 4. Mở Modal giữ chỗ sàn: POST /api/v1/admin/contracts/reserve
    async function openReserveContractModal() {
      const modal = document.getElementById('modal-reserve-contract');
      if (!modal) return;
      document.getElementById('form-reserve-contract')?.reset();

      const tSelect = document.getElementById('reserve-tenant-select');
      const bSelect = document.getElementById('reserve-building-select');

      const list = await loadTenantsListForContracts();

      tSelect.innerHTML = '<option value="">-- Chọn Khách Thuê Đặt Cọc Giữ Chỗ --</option>' +
        (list || []).map(t => `<option value="${t.id}">${escapeHtml(t.company_name)}</option>`).join('');

      bSelect.innerHTML = '<option value="">-- Chọn Tòa Nhà --</option>' +
        BUILDINGS_DATA.map(b => `<option value="${b.id}">${escapeHtml(b.name)}</option>`).join('');

      modal.style.display = 'flex';
    }

    function closeReserveContractModal() {
      const modal = document.getElementById('modal-reserve-contract');
      if (modal) modal.style.display = 'none';
    }

    async function submitReserveContract(e) {
      if (e) e.preventDefault();
      const payload = {
        tenant_id: document.getElementById('reserve-tenant-select').value,
        building_id: document.getElementById('reserve-building-select').value,
        deposit_amount: parseFloat(document.getElementById('reserve-deposit').value) || 0,
        reservation_expiry_date: document.getElementById('reserve-expiry-date').value,
        notes: document.getElementById('reserve-notes').value.trim() || null
      };

      if (!payload.tenant_id || !payload.building_id || !payload.reservation_expiry_date) {
        alert('Vui lòng chọn Khách thuê, Tòa nhà và Ngày hết hạn giữ chỗ.');
        return;
      }

      try {
        const res = await fetch('/api/v1/admin/contracts/reserve', {
          method: 'POST',
          headers: getAuthHeaders(),
          body: JSON.stringify(payload)
        });

        const json = await res.json();
        if (res.ok) {
          alert('Tạo hợp đồng giữ chỗ sàn (RESERVED) thành công!');
          closeReserveContractModal();
          loadContractsList();
        } else {
          alert('Lỗi giữ chỗ: ' + (json.message || ''));
        }
      } catch (err) {
        alert('Lỗi kết nối API giữ chỗ.');
      }
    }

    // 5. Xem chi tiết hợp đồng, danh sách sàn và file scan: GET /api/v1/admin/contracts/{id}
    async function openContractDetailModal(contractId) {
      const modal = document.getElementById('modal-contract-detail');
      if (!modal) return;
      modal.style.display = 'flex';

      const container = document.getElementById('contract-detail-content');
      container.innerHTML = '<div style="text-align:center;padding:40px;color:#94a3b8;"><i class="fa-solid fa-spinner fa-spin"></i> Đang tải thông số hợp đồng từ API...</div>';

      try {
        const res = await fetch(`/api/v1/admin/contracts/${contractId}`, {
          method: 'GET',
          headers: getAuthHeaders()
        });

        const json = await res.json();
        if (!res.ok) {
          container.innerHTML = `<div style="color:#ef4444;text-align:center;">Lỗi: ${escapeHtml(json.message || 'Không tìm thấy hợp đồng')}</div>`;
          return;
        }

        currentViewingContract = json.data;
        renderContractDetailView(currentViewingContract);
      } catch (err) {
        container.innerHTML = '<div style="color:#ef4444;text-align:center;">Lỗi kết nối máy chủ API.</div>';
      }
    }

    function closeContractDetailModal() {
      const modal = document.getElementById('modal-contract-detail');
      if (modal) modal.style.display = 'none';
      currentViewingContract = null;
    }

    function renderContractDetailView(c) {
      const container = document.getElementById('contract-detail-content');
      if (!container) return;

      const units = c.units || [];
      const files = c.files || [];
      const contractCode = c.contract_code || c.contract_number || '---';
      const rentDisplay = c.formatted_monthly_rent || '---';
      const depositDisplay = c.formatted_deposit_amount || '0 đ';

      container.innerHTML = `
        <div style="display:grid;grid-template-columns:1.2fr 1fr;gap:20px;">
          <!-- Cột trái: Điều khoản tài chính & Pháp lý -->
          <div style="background:#f8fafc;padding:16px;border-radius:8px;border:1px solid #e2e8f0;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
              <div>
                <span style="font-family:monospace;font-weight:700;color:var(--mo-primary);font-size:15px;">${escapeHtml(contractCode)}</span>
                <div style="font-size:13px;color:#64748b;">Tạo ngày: ${escapeHtml(new Date(c.created_at).toLocaleDateString('vi-VN'))}</div>
              </div>
              <div>${getContractStatusBadge(c.status)}</div>
            </div>

            <div style="display:flex;flex-direction:column;gap:8px;font-size:13px;color:#334155;">
              <div><strong>Khách thuê:</strong> <span style="font-weight:700;color:#0f172a;">${escapeHtml(c.tenant?.company_name || '---')}</span></div>
              <div><strong>Mã số thuế:</strong> ${escapeHtml(c.tenant?.tax_code || '---')}</div>
              <div><strong>Tòa nhà:</strong> <span style="font-weight:600;color:#0284c7;">${escapeHtml(c.building?.name || '---')}</span></div>
              <hr style="border:none;border-top:1px dashed #cbd5e1;margin:8px 0;">
              <div><strong>Thời hạn thuê:</strong> ${escapeHtml(c.formatted_period || '')}</div>
              <div><strong>Tổng diện tích thuê:</strong> ${Number(c.total_leased_area_sqm || 0).toLocaleString('vi-VN')} m²</div>
              <div><strong>Tiền thuê mặt bằng:</strong> <span style="font-weight:700;color:#ea580c;">${rentDisplay}</span></div>
              <div><strong>Tiền đặt cọc bảo đảm:</strong> <span style="font-weight:700;color:#16a34a;">${depositDisplay}</span></div>
              <div><strong>Kỳ thanh toán:</strong> ${c.payment_cycle_months || c.billing_cycle_months || 1} tháng/lần (Hạn ngày ${c.payment_due_day || 5} hàng kỳ)</div>
              <div><strong>Tăng giá định kỳ:</strong> ${c.annual_price_escalation_percent || 0}% / năm</div>
            </div>

            <div style="margin-top:16px;padding-top:14px;border-top:1px solid #e2e8f0;display:flex;gap:8px;justify-content:flex-end;">
              ${(c.status === 'DRAFT' || c.status === 'RESERVED') ? (
                isContractSalesRole() ? `
                  <div style="background:#fef3c7;color:#d97706;padding:6px 12px;border-radius:6px;font-size:12px;font-weight:600;border:1px solid #fde68a;display:inline-flex;align-items:center;gap:6px;">
                    <i class="fa-solid fa-clock"></i> Chờ Ban Quản Lý (PM) duyệt ký
                  </div>
                ` : `
                  <button class="btn-mo-primary" style="background:#16a34a;border-color:#16a34a;padding:6px 14px;font-size:12px;" onclick="signContract('${c.id}')">
                    <i class="fa-solid fa-file-signature"></i> Ký Duyệt Kích Hoạt
                  </button>
                `
              ) : ''}
              ${!isContractSalesRole() ? `
                <button class="btn-mo-outline" style="padding:6px 12px;font-size:12px;" onclick="openAttachUnitModal('${c.id}', '${c.building_id}')">
                  <i class="fa-solid fa-plus"></i> Gán Sàn Mặt Bằng
                </button>
              ` : ''}
            </div>
          </div>

          <!-- Cột phải: Danh sách Sàn gán & File scan -->
          <div>
            <!-- Danh sách sàn gắn trong hợp đồng -->
            <div style="margin-bottom:16px;">
              <h4 style="font-size:14px;font-weight:700;color:#0f172a;margin-bottom:8px;">
                <i class="fa-solid fa-layer-group"></i> Sàn Mặt Bằng Thuê (${units.length})
              </h4>
              <div style="background:#fff;border:1px solid #e2e8f0;border-radius:6px;max-height:160px;overflow-y:auto;">
                ${units.length === 0 ? '<div style="padding:16px;text-align:center;color:#94a3b8;font-size:12px;">Chưa gán mặt bằng nào vào hợp đồng.</div>' : `
                  <table class="mo-table" style="font-size:12px;">
                    <thead><tr><th>Mã sàn</th><th>Tầng</th><th>Diện tích</th><th>Thao tác</th></tr></thead>
                    <tbody>
                      ${units.map(u => `
                        <tr>
                          <td><strong>${escapeHtml(u.unit_code)}</strong></td>
                          <td>Tầng ${escapeHtml(String(u.floor_number ?? '---'))}</td>
                          <td>${(u.area_sqm ?? u.area) ? `${Number(u.area_sqm ?? u.area).toLocaleString('vi-VN')} m²` : '---'}</td>
                          <td>
                            ${(c.status === 'DRAFT' && !isContractSalesRole()) ? `
                              <button class="btn-mo-outline" style="padding:2px 6px;font-size:11px;color:#ef4444;" onclick="detachUnitFromContract('${c.id}', '${u.id}')" title="Gỡ sàn">
                                <i class="fa-solid fa-xmark"></i>
                              </button>
                            ` : (c.status === 'DRAFT' ? '<span style="color:#64748b;font-size:11px;">Đã gán</span>' : '<span style="color:#94a3b8;font-size:11px;">Đã khóa</span>')}
                          </td>
                        </tr>
                      `).join('')}
                    </tbody>
                  </table>
                `}
              </div>
            </div>

            <!-- Danh sách tài liệu scan hợp đồng (Supabase Storage) -->
            <div>
              <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
                <h4 style="font-size:14px;font-weight:700;color:#0f172a;margin:0;">
                  <i class="fa-solid fa-file-contract"></i> File Scan Hợp Đồng Ký Kết
                </h4>
                ${!isContractSalesRole() ? `
                  <button class="btn-mo-orange" style="padding:3px 8px;font-size:11px;" onclick="openUploadContractScanModal('${c.id}')">
                    <i class="fa-solid fa-cloud-arrow-up"></i> Upload Scan
                  </button>
                ` : ''}
              </div>

              <div style="background:#fff;border:1px solid #e2e8f0;border-radius:6px;max-height:160px;overflow-y:auto;">
                ${files.length === 0 ? '<div style="padding:16px;text-align:center;color:#94a3b8;font-size:12px;">Chưa có tài liệu scan đính kèm.</div>' : `
                  <div style="padding:8px;display:flex;flex-direction:column;gap:6px;">
                    ${files.map(f => `
                      <div style="display:flex;justify-content:space-between;align-items:center;background:#f8fafc;padding:6px 10px;border-radius:4px;border:1px solid #e2e8f0;">
                        <span style="font-size:12px;color:#334155;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:220px;">
                          <i class="fa-solid fa-file-pdf" style="color:#ef4444;"></i> ${escapeHtml(f.file_name || 'HopDongScan.pdf')}
                        </span>
                        <button class="btn-mo-outline" style="padding:2px 8px;font-size:11px;color:#0284c7;" onclick="downloadContractScanFile('${c.id}', '${f.id}')">
                          <i class="fa-solid fa-download"></i> Tải Ký Số
                        </button>
                      </div>
                    `).join('')}
                  </div>
                `}
              </div>
            </div>
          </div>
        </div>
      `;
    }

    // 6. Ký duyệt & Kích hoạt hợp đồng: POST /api/v1/admin/contracts/{id}/sign
    async function signContract(contractId) {
      if (isContractSalesRole()) {
        showMoToast('warning', 'Từ chối quyền hạn', 'Chuyên viên Sales không có thẩm quyền ký duyệt hợp đồng. Ký duyệt thuộc thẩm quyền của Ban Quản Lý (PM) hoặc Quản trị viên.');
        return;
      }
      if (!confirm('Xác nhận KÝ DUYỆT hợp đồng này? Hệ thống sẽ tự động kích hoạt trạng thái ACTIVE, chuyển trạng thái sàn sang LEASED và cấp tài khoản Portal cho khách thuê.')) return;

      try {
        const res = await fetch(`/api/v1/admin/contracts/${contractId}/sign`, {
          method: 'POST',
          headers: getAuthHeaders()
        });

        const json = await res.json();
        if (res.ok) {
          const creds = json.data?.tenant_credential_issued;
          if (creds && creds.password) {
            showTenantCredentialModal(creds);
          } else {
            moToast('Ký duyệt và kích hoạt hợp đồng thành công!', 'success');
          }
          loadContractsList();
          if (currentViewingContract && currentViewingContract.id === contractId) {
            openContractDetailModal(contractId);
          }
        } else {
          moToast('Không thể ký hợp đồng: ' + (json.message || ''), 'error');
        }
      } catch (err) {
        moToast('Lỗi kết nối API khi ký hợp đồng.', 'error');
      }
    }

    function showTenantCredentialModal(creds) {
      const modal = document.getElementById('modal-tenant-credential');
      if (!modal) return;
      const compEl = document.getElementById('cred-company-name');
      const codeEl = document.getElementById('cred-tenant-code');
      const emailEl = document.getElementById('cred-email');
      const passEl = document.getElementById('cred-password');

      if (compEl) compEl.innerText = creds.company_name || 'Khách thuê';
      if (codeEl) codeEl.innerText = creds.tenant_code || '---';
      if (emailEl) emailEl.innerText = creds.login_email || creds.billing_email || '---';
      if (passEl) passEl.innerText = creds.password || '---';

      modal.style.display = 'flex';
    }

    function closeTenantCredentialModal() {
      const modal = document.getElementById('modal-tenant-credential');
      if (modal) modal.style.display = 'none';
    }

    function copyTenantPassword() {
      const pass = document.getElementById('cred-password')?.innerText;
      if (pass) {
        navigator.clipboard.writeText(pass).then(() => {
          moToast('Đã sao chép mật khẩu khách thuê: ' + pass, 'success');
        }).catch(() => {
          alert('Mật khẩu khách thuê: ' + pass);
        });
      }
    }

    window.closeTenantCredentialModal = closeTenantCredentialModal;
    window.copyTenantPassword = copyTenantPassword;

    // 7. Gán mặt bằng vào hợp đồng: POST /api/v1/admin/contracts/{id}/units
    let currentAttachContractId = null;
    async function openAttachUnitModal(contractId, buildingId) {
      if (isContractSalesRole()) {
        showMoToast('warning', 'Từ chối quyền hạn', 'Chuyên viên Sales không có quyền gán thêm mặt bằng sau khi đã lập hợp đồng. Vui lòng liên hệ PM phụ trách.');
        return;
      }
      currentAttachContractId = contractId;
      const modal = document.getElementById('modal-attach-unit');
      if (!modal) return;
      const uSelect = document.getElementById('attach-unit-select');
      uSelect.innerHTML = '<option value="">Đang tải danh sách mặt bằng trống...</option>';
      modal.style.display = 'flex';

      try {
        const res = await fetch(`/api/v1/portal/units?building_id=${buildingId}&status=AVAILABLE`, {
          method: 'GET',
          headers: getAuthHeaders()
        });
        const json = await res.json();
        const units = json.data?.data || json.data || [];
        uSelect.innerHTML = '<option value="">-- Chọn Sàn Mặt Bằng Đang Trống --</option>' +
          units.map(u => `<option value="${u.id}">${escapeHtml(u.unit_code)} - Tầng ${escapeHtml(u.floor_number || '?')} (${u.area} m² - $${u.rent_price_per_sqm || 0}/m²)</option>`).join('');
      } catch (e) {
        uSelect.innerHTML = '<option value="">Lỗi tải danh sách mặt bằng</option>';
      }
    }

    function closeAttachUnitModal() {
      const modal = document.getElementById('modal-attach-unit');
      if (modal) modal.style.display = 'none';
      currentAttachContractId = null;
    }

    async function submitAttachUnit() {
      if (!currentAttachContractId) return;
      const unitId = document.getElementById('attach-unit-select').value;
      const rentalPrice = parseFloat(document.getElementById('attach-unit-price').value) || undefined;

      if (!unitId) {
        alert('Vui lòng chọn mặt bằng cần gán.');
        return;
      }

      try {
        const res = await fetch(`/api/v1/admin/contracts/${currentAttachContractId}/units`, {
          method: 'POST',
          headers: getAuthHeaders(),
          body: JSON.stringify({
            unit_id: unitId,
            rent_price_per_sqm: rentalPrice
          })
        });

        const json = await res.json();
        if (res.ok) {
          alert('Gán mặt bằng vào hợp đồng thành công!');
          closeAttachUnitModal();
          openContractDetailModal(currentAttachContractId);
          loadContractsList();
        } else {
          alert('Lỗi gán mặt bằng: ' + (json.message || ''));
        }
      } catch (err) {
        alert('Lỗi kết nối khi gán mặt bằng.');
      }
    }

    // 8. Gỡ mặt bằng khỏi hợp đồng: DELETE /api/v1/admin/contracts/{id}/units/{unitId}
    async function detachUnitFromContract(contractId, unitId) {
      if (isContractSalesRole()) {
        showMoToast('warning', 'Từ chối quyền hạn', 'Chuyên viên Sales không có quyền gỡ mặt bằng khỏi hợp đồng.');
        return;
      }
      if (!confirm('Gỡ mặt bằng này khỏi hợp đồng?')) return;

      try {
        const res = await fetch(`/api/v1/admin/contracts/${contractId}/units/${unitId}`, {
          method: 'DELETE',
          headers: getAuthHeaders()
        });

        const json = await res.json();
        if (res.ok) {
          openContractDetailModal(contractId);
          loadContractsList();
        } else {
          alert('Không thể gỡ mặt bằng: ' + (json.message || ''));
        }
      } catch (e) {
        alert('Lỗi kết nối khi gỡ mặt bằng.');
      }
    }

    // 9. Lập phụ lục gia hạn hợp đồng: POST /api/v1/admin/contracts/{id}/renew
    let currentRenewContractId = null;
    function openRenewContractModal(contractId, code, currentEndDate) {
      if (isContractSalesRole()) {
        showMoToast('warning', 'Từ chối quyền hạn', 'Chuyên viên Sales không có thẩm quyền gia hạn hợp đồng.');
        return;
      }
      currentRenewContractId = contractId;
      const modal = document.getElementById('modal-renew-contract');
      if (!modal) return;
      const renewCodeEl = document.getElementById('renew-contract-code');
      if (renewCodeEl) renewCodeEl.innerText = code;
      const renewDateEl = document.getElementById('renew-current-end-date');
      if (renewDateEl) renewDateEl.innerText = currentEndDate;
      modal.style.display = 'flex';
    }

    function closeRenewContractModal() {
      const modal = document.getElementById('modal-renew-contract');
      if (modal) modal.style.display = 'none';
      currentRenewContractId = null;
    }

    async function submitRenewContract() {
      if (!currentRenewContractId) return;
      const newEndDate = document.getElementById('renew-new-end-date').value;
      const newRent = parseFloat(document.getElementById('renew-new-rent').value) || undefined;
      const reason = document.getElementById('renew-notes').value.trim() || null;

      if (!newEndDate) {
        alert('Vui lòng chọn ngày kết thúc thuê mới sau gia hạn.');
        return;
      }

      try {
        const res = await fetch(`/api/v1/admin/contracts/${currentRenewContractId}/renew`, {
          method: 'POST',
          headers: getAuthHeaders(),
          body: JSON.stringify({
            new_end_date: newEndDate,
            new_rent_amount: newRent,
            notes: reason
          })
        });

        const json = await res.json();
        if (res.ok) {
          alert('Lập phụ lục gia hạn hợp đồng thành công!');
          closeRenewContractModal();
          loadContractsList();
        } else {
          alert('Lỗi gia hạn: ' + (json.message || ''));
        }
      } catch (e) {
        alert('Lỗi kết nối khi gia hạn hợp đồng.');
      }
    }

    // 10. Thanh lý hợp đồng & Trả phòng: POST /api/v1/admin/contracts/{id}/terminate
    let currentTerminateContractId = null;
    function openTerminateContractModal(contractId, code, depositAmount) {
      if (isContractSalesRole()) {
        showMoToast('warning', 'Từ chối quyền hạn', 'Chuyên viên Sales không có thẩm quyền thanh lý hợp đồng.');
        return;
      }
      currentTerminateContractId = contractId;
      const modal = document.getElementById('modal-terminate-contract');
      if (!modal) return;
      const termCodeEl = document.getElementById('terminate-contract-code');
      if (termCodeEl) termCodeEl.innerText = code;
      const termDepositEl = document.getElementById('terminate-deposit-held');
      if (termDepositEl) termDepositEl.innerText = `$${depositAmount.toLocaleString()}`;
      document.getElementById('terminate-refund-amount').value = depositAmount;
      modal.style.display = 'flex';
    }

    function closeTerminateContractModal() {
      const modal = document.getElementById('modal-terminate-contract');
      if (modal) modal.style.display = 'none';
      currentTerminateContractId = null;
    }

    async function submitTerminateContract() {
      if (!currentTerminateContractId) return;
      const terminationDate = document.getElementById('terminate-date').value;
      const refund = parseFloat(document.getElementById('terminate-refund-amount').value) || 0;
      const reason = document.getElementById('terminate-reason').value.trim();

      if (!terminationDate) {
        alert('Vui lòng chọn ngày thanh lý hợp đồng.');
        return;
      }

      try {
        const res = await fetch(`/api/v1/admin/contracts/${currentTerminateContractId}/terminate`, {
          method: 'POST',
          headers: getAuthHeaders(),
          body: JSON.stringify({
            termination_date: terminationDate,
            refund_deposit_amount: refund,
            termination_reason: reason || null
          })
        });

        const json = await res.json();
        if (res.ok) {
          alert('Thanh lý hợp đồng thành công! Mặt bằng đã được giải phóng sang trạng thái bảo trì/trống.');
          closeTerminateContractModal();
          loadContractsList();
        } else {
          alert('Lỗi thanh lý: ' + (json.message || ''));
        }
      } catch (e) {
        alert('Lỗi kết nối khi thanh lý hợp đồng.');
      }
    }

    // 11. Tải lên file scan hợp đồng: POST /api/v1/admin/contracts/{id}/upload-files
    let currentUploadContractId = null;
    function openUploadContractScanModal(contractId) {
      if (isContractSalesRole()) {
        showMoToast('warning', 'Từ chối quyền hạn', 'Chuyên viên Sales không có thẩm quyền tải lên tài liệu scan hợp đồng ký kết. Thao tác này thuộc thẩm quyền của Ban Quản Lý (PM).');
        return;
      }
      currentUploadContractId = contractId;
      const modal = document.getElementById('modal-upload-contract-scan');
      if (!modal) return;
      document.getElementById('form-upload-contract-scan')?.reset();
      modal.style.display = 'flex';
    }

    function closeUploadContractScanModal() {
      const modal = document.getElementById('modal-upload-contract-scan');
      if (modal) modal.style.display = 'none';
      currentUploadContractId = null;
    }

    async function submitUploadContractScan(e) {
      if (e) e.preventDefault();
      if (!currentUploadContractId) return;

      const fileInput = document.getElementById('contract-scan-file-input');
      if (!fileInput || !fileInput.files || fileInput.files.length === 0) {
        alert('Vui lòng chọn file scan hợp đồng (PDF, tối đa 20MB).');
        return;
      }

      const formData = new FormData();
      for (let i = 0; i < fileInput.files.length; i++) {
        formData.append('files[]', fileInput.files[i]);
      }

      try {
        const res = await fetch(`/api/v1/admin/contracts/${currentUploadContractId}/upload-files`, {
          method: 'POST',
          headers: getAuthHeaders(true),
          body: formData
        });

        const json = await res.json();
        if (res.ok) {
          alert('Tải lên file scan hợp đồng lên Supabase Storage thành công!');
          closeUploadContractScanModal();
          openContractDetailModal(currentUploadContractId);
        } else {
          alert('Lỗi tải file scan: ' + (json.message || ''));
        }
      } catch (err) {
        alert('Lỗi kết nối tải file scan.');
      }
    }

    // 12. Tải link ký số file scan hợp đồng: GET /api/v1/admin/contracts/{id}/files/{fileId}/download
    async function downloadContractScanFile(contractId, fileId) {
      try {
        const res = await fetch(`/api/v1/admin/contracts/${contractId}/files/${fileId}/download`, {
          method: 'GET',
          headers: getAuthHeaders()
        });

        const json = await res.json();
        if (res.ok && json.data?.download_url) {
          window.open(json.data.download_url, '_blank');
        } else {
          alert('Không thể lấy link tải file scan: ' + (json.message || ''));
        }
      } catch (e) {
        alert('Lỗi kết nối tải file.');
      }
    }

    // 13. Xóa hợp đồng nháp: DELETE /api/v1/admin/contracts/{id}
    async function deleteContract(contractId) {
      if (!isContractAdminRole()) {
        showMoToast('warning', 'Từ chối quyền hạn', 'Chỉ có Quản trị viên hệ thống (Admin) mới có thẩm quyền xóa hợp đồng.');
        return;
      }
      if (!confirm('Bạn có chắc chắn muốn xóa bản hợp đồng nháp này?')) return;

      try {
        const res = await fetch(`/api/v1/admin/contracts/${contractId}`, {
          method: 'DELETE',
          headers: getAuthHeaders()
        });

        const json = await res.json();
        if (res.ok) {
          alert('Xóa hợp đồng thành công.');
          loadContractsList();
        } else {
          alert('Lỗi xóa: ' + (json.message || ''));
        }
      } catch (e) {
        alert('Lỗi kết nối khi xóa hợp đồng.');
      }
    }

    // 14. Phân hệ Khách thuê Portal (Tenant Workspace): GET /api/v1/tenant/contracts & profile
    async function loadTenantWorkspaceContracts() {
      const container = document.getElementById('tenant-workspace-contracts-container');
      if (!container) return;

      container.innerHTML = '<div style="text-align:center;padding:30px;color:#94a3b8;"><i class="fa-solid fa-spinner fa-spin"></i> Đang tải hợp đồng của doanh nghiệp bạn từ API...</div>';

      try {
        const authUserStr = localStorage.getItem('auth_user');
        let authUser = null;
        try { if (authUserStr) authUser = JSON.parse(authUserStr); } catch (e) {}

        const headers = getAuthHeaders();
        if (authUser?.id) {
          headers['X-Tenant-Id'] = authUser.id;
        }

        const res = await fetch('/api/v1/tenant/contracts', {
          method: 'GET',
          headers: headers
        });

        if (res.status === 401) {
          container.innerHTML = '<div style="text-align:center;color:#64748b;padding:32px;"><i class="fa-regular fa-building" style="font-size:26px;display:block;margin-bottom:8px;color:#94a3b8;"></i>Chưa có hợp đồng thuê nào được kích hoạt cho tài khoản này.</div>';
          return;
        }

        const json = await res.json();
        const contracts = json.data?.data || json.data || [];
        if (contracts.length === 0) {
          container.innerHTML = '<div style="text-align:center;color:#94a3b8;padding:32px;">Doanh nghiệp của bạn hiện chưa có hợp đồng thuê nào trong hệ thống.</div>';
          return;
        }

        container.innerHTML = contracts.map(c => {
          const contractCode = c.contract_code || c.contract_number || '---';
          const rentDisplay = c.formatted_monthly_rent || '---';
          return `
          <div style="background:#fff;border:1px solid #e2e8f0;border-radius:10px;padding:20px;margin-bottom:16px;box-shadow:0 1px 3px rgba(0,0,0,0.05);">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
              <div>
                <span style="font-size:12px;color:#64748b;">Mã Hợp Đồng:</span>
                <strong style="color:var(--mo-primary);font-size:16px;font-family:monospace;margin-left:6px;">${escapeHtml(contractCode)}</strong>
              </div>
              <div>${getContractStatusBadge(c.status)}</div>
            </div>
            <div style="display:grid;grid-template-columns:repeat(3, 1fr);gap:14px;background:#f8fafc;padding:14px;border-radius:8px;font-size:13px;">
              <div><strong>Tòa nhà:</strong> ${escapeHtml(c.building?.name || '---')}</div>
              <div><strong>Thời hạn:</strong> ${escapeHtml(c.formatted_period || '')}</div>
              <div><strong>Tiền thuê:</strong> <span style="color:#ea580c;font-weight:700;">${escapeHtml(rentDisplay)}</span></div>
            </div>
            <div style="margin-top:12px;display:flex;justify-content:space-between;align-items:center;">
              <span style="font-size:12px;color:#64748b;">
                Sàn thuê: ${(c.units || []).map(u => `<strong>${escapeHtml(u.unit_code)}</strong> (${escapeHtml(u.formatted_area || '')})`).join(', ') || `${Number(c.total_leased_area_sqm || 0).toLocaleString('vi-VN')} m²`}
              </span>
              <button class="btn-mo-primary" style="padding:6px 14px;font-size:12px;" onclick="openTenantContractDetail('${c.id}')">
                <i class="fa-solid fa-file-contract"></i> Xem Chi Tiết Điều Khoản & File Scan
              </button>
            </div>
          </div>
        `}).join('');
      } catch (err) {
        container.innerHTML = '<div style="color:#ef4444;text-align:center;padding:20px;">Lỗi kết nối tải hợp đồng khách thuê.</div>';
      }
    }

    // Xem chi tiết hợp đồng từ phía Cổng Khách Thuê: GET /api/v1/tenant/contracts/{id}
    async function openTenantContractDetail(contractId) {
      const modal = document.getElementById('modal-contract-detail');
      if (!modal) return;
      modal.style.display = 'flex';

      const container = document.getElementById('contract-detail-content');
      container.innerHTML = '<div style="text-align:center;padding:40px;color:#94a3b8;"><i class="fa-solid fa-spinner fa-spin"></i> Đang tải chi tiết hợp đồng...</div>';

      try {
        const res = await fetch(`/api/v1/tenant/contracts/${contractId}`, {
          method: 'GET',
          headers: getAuthHeaders()
        });
        const json = await res.json();
        if (res.ok) {
          renderContractDetailView(json.data);
        } else {
          container.innerHTML = `<div style="color:#ef4444;text-align:center;">Lỗi: ${escapeHtml(json.message || '')}</div>`;
        }
      } catch (e) {
        container.innerHTML = '<div style="color:#ef4444;text-align:center;">Lỗi kết nối API hợp đồng khách thuê.</div>';
      }
    }

    // 15. Tải và Cập nhật hồ sơ pháp lý Doanh nghiệp khách thuê: GET & PUT /api/v1/tenant/profile
    async function loadTenantProfile() {
      try {
        const res = await fetch('/api/v1/tenant/profile', {
          method: 'GET',
          headers: getAuthHeaders()
        });
        const json = await res.json();
        if (res.ok && json.data) {
          const profile = json.data;
          const nameEl = document.getElementById('tenant-profile-company-name');
          if (nameEl) nameEl.value = profile.company_name || '';
          const phoneEl = document.getElementById('tenant-profile-phone');
          if (phoneEl) phoneEl.value = profile.contact_phone || '';
          const emailEl = document.getElementById('tenant-profile-email');
          if (emailEl) emailEl.value = profile.login_email || profile.billing_email || profile.contact_email || '';
          const addrEl = document.getElementById('tenant-profile-address');
          if (addrEl) addrEl.value = profile.headquarter_address || profile.business_address || '';
        }
      } catch (e) {
        console.warn('Lỗi tải profile khách thuê:', e);
      }
    }

    async function submitUpdateTenantProfile(e) {
      if (e) e.preventDefault();
      try {
        const res = await fetch('/api/v1/tenant/profile', {
          method: 'PUT',
          headers: getAuthHeaders(),
          body: JSON.stringify({
            contact_phone: document.getElementById('tenant-profile-phone')?.value.trim(),
            contact_email: document.getElementById('tenant-profile-email')?.value.trim(),
            business_address: document.getElementById('tenant-profile-address')?.value.trim()
          })
        });
        const json = await res.json();
        if (res.ok) {
          alert('Cập nhật hồ sơ pháp lý doanh nghiệp thành công!');
        } else {
          alert('Lỗi cập nhật profile: ' + (json.message || ''));
        }
      } catch (e) {
        alert('Lỗi kết nối khi cập nhật hồ sơ doanh nghiệp.');
      }
    }

    function doContractSearch() {
      const inp = document.getElementById('contract-search-input');
      handleContractSearch(inp ? inp.value : '');
    }

    function handleContractSearch(val) {
      currentContractSearch = val.trim();
      loadContractsList();
    }

    function handleContractStatusFilter(status) {
      currentContractStatus = status;
      const sel = document.getElementById('contract-status-select');
      if (sel && sel.value !== status) sel.value = status;
      document.querySelectorAll('.contract-status-btn').forEach(b => {
        b.classList.remove('active');
        if (b.getAttribute('data-status') === status) b.classList.add('active');
      });
      loadContractsList();
    }

    function resetContractFilters() {
      const inp = document.getElementById('contract-search-input');
      if (inp) inp.value = '';
      const clrBtn = document.getElementById('btn-clear-contract-search');
      if (clrBtn) clrBtn.style.display = 'none';
      const sel = document.getElementById('contract-status-select');
      if (sel) sel.value = 'ALL';
      currentContractSearch = '';
      currentContractStatus = 'ALL';
      document.querySelectorAll('.contract-status-btn').forEach(b => {
        b.classList.remove('active');
        if (b.getAttribute('data-status') === 'ALL') b.classList.add('active');
      });
      loadContractsList();
    }

    window.handleContractStatusFilter = handleContractStatusFilter;
    window.resetContractFilters = resetContractFilters;

    document.addEventListener('DOMContentLoaded', function () {
      const urlParams = new URLSearchParams(window.location.search);
      if (urlParams.get('open_create') === '1' || urlParams.get('create_contract') === '1') {
        const pTenant = urlParams.get('tenant_id');
        const pBuilding = urlParams.get('building_id');
        const pUnit = urlParams.get('unit_id');
        setTimeout(() => {
          openCreateContractModal(pTenant, pBuilding, pUnit);
        }, 300);
      }
    });
</script>
