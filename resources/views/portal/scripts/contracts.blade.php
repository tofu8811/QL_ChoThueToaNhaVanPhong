<script>
    /* ==========================================================================
       MODULE 3: CONTRACTS & TENANT PORTAL JAVASCRIPT (ENDPOINTS 43 - 56)
       ========================================================================== */
    let contractsList = [];
    let currentContractStatus = 'ALL';
    let currentContractSearch = '';
    let currentViewingContract = null;

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
        const tenantName = c.tenant?.company_name || '---';
        const buildingName = c.building?.name || '---';
        const rentalPeriod = `${escapeHtml(c.start_date || '')} ➔ ${escapeHtml(c.end_date || '')}`;
        const totalRent = c.total_rent_amount ? `$${Number(c.total_rent_amount).toLocaleString()}/tháng` : (c.rent_amount ? `$${Number(c.rent_amount).toLocaleString()}/tháng` : '---');
        const deposit = c.deposit_amount ? `$${Number(c.deposit_amount).toLocaleString()}` : '$0';

        return `
          <tr>
            <td>
              <strong style="color:var(--mo-primary);font-family:monospace;font-size:13px;">${escapeHtml(c.contract_code || c.id.substring(0,8))}</strong>
              <div style="font-size:11px;color:#94a3b8;">Ký bởi: ${escapeHtml(c.creator?.full_name || 'Sales')}</div>
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
              ${c.status === 'DRAFT' || c.status === 'RESERVED' ? `
                <button class="btn-mo-outline" style="padding:4px 8px;font-size:12px;color:#16a34a;border-color:#bbf7d0;margin-left:4px;" onclick="signContract('${c.id}')" title="Ký duyệt & Kích hoạt">
                  <i class="fa-solid fa-file-signature"></i> Ký Duyệt
                </button>
              ` : ''}
              ${c.status === 'ACTIVE' || c.status === 'EXPIRING_SOON' ? `
                <button class="btn-mo-outline" style="padding:4px 8px;font-size:12px;color:#0284c7;border-color:#bae6fd;margin-left:4px;" onclick="openRenewContractModal('${c.id}', '${escapeHtml(c.contract_code)}', '${c.end_date}')" title="Gia hạn hợp đồng">
                  <i class="fa-solid fa-calendar-plus"></i> Gia hạn
                </button>
                <button class="btn-mo-outline" style="padding:4px 8px;font-size:12px;color:#dc2626;border-color:#fecaca;margin-left:4px;" onclick="openTerminateContractModal('${c.id}', '${escapeHtml(c.contract_code)}', ${c.deposit_amount || 0})" title="Thanh lý hợp đồng">
                  <i class="fa-solid fa-file-circle-xmark"></i> Thanh lý
                </button>
              ` : ''}
              ${c.status === 'DRAFT' ? `
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
    async function openCreateContractModal() {
      const modal = document.getElementById('modal-create-contract');
      if (!modal) return;
      document.getElementById('form-create-contract')?.reset();

      // Nạp danh sách Khách thuê vào select
      const tSelect = document.getElementById('contract-tenant-select');
      const bSelect = document.getElementById('contract-building-select');

      tSelect.innerHTML = '<option value="">-- Chọn Khách Thuê Doanh Nghiệp --</option>' +
        tenantsList.map(t => `<option value="${t.id}">${escapeHtml(t.company_name)} (${escapeHtml(t.tax_code || 'MST chưa có')})</option>`).join('');

      bSelect.innerHTML = '<option value="">-- Chọn Tòa Nhà Văn Phòng --</option>' +
        BUILDINGS_DATA.map(b => `<option value="${b.id}">${escapeHtml(b.name)}</option>`).join('');

      modal.style.display = 'flex';
    }

    function closeCreateContractModal() {
      const modal = document.getElementById('modal-create-contract');
      if (modal) modal.style.display = 'none';
    }

    async function submitCreateContract(e) {
      if (e) e.preventDefault();
      const payload = {
        tenant_id: document.getElementById('contract-tenant-select').value,
        building_id: document.getElementById('contract-building-select').value,
        contract_code: document.getElementById('contract-code-input').value.trim() || undefined,
        start_date: document.getElementById('contract-start-date').value,
        end_date: document.getElementById('contract-end-date').value,
        handover_date: document.getElementById('contract-handover-date').value || null,
        deposit_amount: parseFloat(document.getElementById('contract-deposit').value) || 0,
        rent_amount: parseFloat(document.getElementById('contract-rent').value) || 0,
        service_fee_amount: parseFloat(document.getElementById('contract-service-fee').value) || 0,
        billing_cycle_months: parseInt(document.getElementById('contract-billing-cycle').value) || 1,
        payment_due_day: parseInt(document.getElementById('contract-payment-day').value) || 5,
        notes: document.getElementById('contract-notes').value.trim() || null
      };

      if (!payload.tenant_id || !payload.building_id || !payload.start_date || !payload.end_date) {
        alert('Vui lòng điền đầy đủ Khách thuê, Tòa nhà, Ngày bắt đầu và Ngày kết thúc thuê.');
        return;
      }

      try {
        const res = await fetch('/api/v1/admin/contracts', {
          method: 'POST',
          headers: getAuthHeaders(),
          body: JSON.stringify(payload)
        });

        const json = await res.json();
        if (res.ok) {
          alert('Tạo hợp đồng thuê bản nháp (DRAFT) thành công! Hãy gán mặt bằng và ký duyệt.');
          closeCreateContractModal();
          loadContractsList();
        } else {
          alert('Lỗi tạo hợp đồng: ' + (json.message || 'Dữ liệu không hợp lệ.'));
        }
      } catch (err) {
        alert('Lỗi kết nối máy chủ API.');
      }
    }

    // 4. Mở Modal giữ chỗ sàn: POST /api/v1/admin/contracts/reserve
    async function openReserveContractModal() {
      const modal = document.getElementById('modal-reserve-contract');
      if (!modal) return;
      document.getElementById('form-reserve-contract')?.reset();

      const tSelect = document.getElementById('reserve-tenant-select');
      const bSelect = document.getElementById('reserve-building-select');

      tSelect.innerHTML = '<option value="">-- Chọn Khách Thuê Đặt Cọc Giữ Chỗ --</option>' +
        tenantsList.map(t => `<option value="${t.id}">${escapeHtml(t.company_name)}</option>`).join('');

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

      container.innerHTML = `
        <div style="display:grid;grid-template-columns:1.2fr 1fr;gap:20px;">
          <!-- Cột trái: Điều khoản tài chính & Pháp lý -->
          <div style="background:#f8fafc;padding:16px;border-radius:8px;border:1px solid #e2e8f0;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
              <div>
                <span style="font-family:monospace;font-weight:700;color:var(--mo-primary);font-size:15px;">${escapeHtml(c.contract_code)}</span>
                <div style="font-size:13px;color:#64748b;">Tạo ngày: ${escapeHtml(new Date(c.created_at).toLocaleDateString('vi-VN'))}</div>
              </div>
              <div>${getContractStatusBadge(c.status)}</div>
            </div>

            <div style="display:flex;flex-direction:column;gap:8px;font-size:13px;color:#334155;">
              <div><strong>Khách thuê:</strong> <span style="font-weight:700;color:#0f172a;">${escapeHtml(c.tenant?.company_name || '---')}</span></div>
              <div><strong>Mã số thuế:</strong> ${escapeHtml(c.tenant?.tax_code || '---')}</div>
              <div><strong>Tòa nhà:</strong> <span style="font-weight:600;color:#0284c7;">${escapeHtml(c.building?.name || '---')}</span></div>
              <hr style="border:none;border-top:1px dashed #cbd5e1;margin:8px 0;">
              <div><strong>Thời hạn thuê:</strong> ${escapeHtml(c.start_date)} ➔ ${escapeHtml(c.end_date)}</div>
              <div><strong>Tiền thuê mặt bằng:</strong> <span style="font-weight:700;color:#ea580c;">$${Number(c.rent_amount || 0).toLocaleString()}/tháng</span></div>
              <div><strong>Phí dịch vụ vận hành:</strong> $${Number(c.service_fee_amount || 0).toLocaleString()}/tháng</div>
              <div><strong>Tiền đặt cọc bảo đảm:</strong> <span style="font-weight:700;color:#16a34a;">$${Number(c.deposit_amount || 0).toLocaleString()}</span></div>
              <div><strong>Kỳ thanh toán:</strong> ${c.billing_cycle_months || 1} tháng/lần (Hạn ngày ${c.payment_due_day || 5} hàng tháng)</div>
              <div><strong>Ghi chú:</strong> ${escapeHtml(c.notes || 'Không có')}</div>
            </div>

            <div style="margin-top:16px;padding-top:14px;border-top:1px solid #e2e8f0;display:flex;gap:8px;justify-content:flex-end;">
              ${c.status === 'DRAFT' || c.status === 'RESERVED' ? `
                <button class="btn-mo-primary" style="background:#16a34a;border-color:#16a34a;padding:6px 14px;font-size:12px;" onclick="signContract('${c.id}')">
                  <i class="fa-solid fa-file-signature"></i> Ký Duyệt Kích Hoạt
                </button>
              ` : ''}
              <button class="btn-mo-outline" style="padding:6px 12px;font-size:12px;" onclick="openAttachUnitModal('${c.id}', '${c.building_id}')">
                <i class="fa-solid fa-plus"></i> Gán Sàn Mặt Bằng
              </button>
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
                          <td>Tầng ${escapeHtml(u.floor_number || '---')}</td>
                          <td>${u.area ? `${u.area} m²` : '---'}</td>
                          <td>
                            ${c.status === 'DRAFT' ? `
                              <button class="btn-mo-outline" style="padding:2px 6px;font-size:11px;color:#ef4444;" onclick="detachUnitFromContract('${c.id}', '${u.id}')" title="Gỡ sàn">
                                <i class="fa-solid fa-xmark"></i>
                              </button>
                            ` : '<span style="color:#94a3b8;font-size:11px;">Đã khóa</span>'}
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
                <button class="btn-mo-orange" style="padding:3px 8px;font-size:11px;" onclick="openUploadContractScanModal('${c.id}')">
                  <i class="fa-solid fa-cloud-arrow-up"></i> Upload Scan
                </button>
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
      if (!confirm('Xác nhận KÝ DUYỆT hợp đồng này? Hệ thống sẽ tự động kích hoạt trạng thái ACTIVE, khóa các sàn mặt bằng sang OCCUPIED và kiểm tra chống trùng lịch thuê.')) return;

      try {
        const res = await fetch(`/api/v1/admin/contracts/${contractId}/sign`, {
          method: 'POST',
          headers: getAuthHeaders()
        });

        const json = await res.json();
        if (res.ok) {
          alert('Ký duyệt và kích hoạt hợp đồng thành công!');
          loadContractsList();
          if (currentViewingContract && currentViewingContract.id === contractId) {
            openContractDetailModal(contractId);
          }
        } else {
          alert('Không thể ký hợp đồng: ' + (json.message || ''));
        }
      } catch (err) {
        alert('Lỗi kết nối API khi ký hợp đồng.');
      }
    }

    // 7. Gán mặt bằng vào hợp đồng: POST /api/v1/admin/contracts/{id}/units
    let currentAttachContractId = null;
    async function openAttachUnitModal(contractId, buildingId) {
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
      currentRenewContractId = contractId;
      const modal = document.getElementById('modal-renew-contract');
      if (!modal) return;
      document.getElementById('renew-contract-code').innerText = code;
      document.getElementById('renew-current-end-date').innerText = currentEndDate;
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
      currentTerminateContractId = contractId;
      const modal = document.getElementById('modal-terminate-contract');
      if (!modal) return;
      document.getElementById('terminate-contract-code').innerText = code;
      document.getElementById('terminate-deposit-held').innerText = `$${depositAmount.toLocaleString()}`;
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
        const res = await fetch('/api/v1/tenant/contracts', {
          method: 'GET',
          headers: getAuthHeaders()
        });

        if (res.status === 401) {
          container.innerHTML = '<div style="text-align:center;color:#ef4444;padding:24px;">Vui lòng đăng nhập với tài khoản Doanh nghiệp Khách Thuê (Tenant).</div>';
          return;
        }

        const json = await res.json();
        const contracts = json.data?.data || json.data || [];
        if (contracts.length === 0) {
          container.innerHTML = '<div style="text-align:center;color:#94a3b8;padding:32px;">Doanh nghiệp của bạn hiện chưa có hợp đồng thuê nào trong hệ thống.</div>';
          return;
        }

        container.innerHTML = contracts.map(c => `
          <div style="background:#fff;border:1px solid #e2e8f0;border-radius:10px;padding:20px;margin-bottom:16px;box-shadow:0 1px 3px rgba(0,0,0,0.05);">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
              <div>
                <span style="font-size:12px;color:#64748b;">Mã Hợp Đồng:</span>
                <strong style="color:var(--mo-primary);font-size:16px;font-family:monospace;margin-left:6px;">${escapeHtml(c.contract_code)}</strong>
              </div>
              <div>${getContractStatusBadge(c.status)}</div>
            </div>
            <div style="display:grid;grid-template-columns:repeat(3, 1fr);gap:14px;background:#f8fafc;padding:14px;border-radius:8px;font-size:13px;">
              <div><strong>Tòa nhà:</strong> ${escapeHtml(c.building?.name || '---')}</div>
              <div><strong>Thời hạn:</strong> ${escapeHtml(c.start_date)} ➔ ${escapeHtml(c.end_date)}</div>
              <div><strong>Tiền thuê:</strong> <span style="color:#ea580c;font-weight:700;">$${Number(c.rent_amount || 0).toLocaleString()}/tháng</span></div>
            </div>
            <div style="margin-top:12px;display:flex;justify-content:space-between;align-items:center;">
              <span style="font-size:12px;color:#64748b;">
                Sàn thuê: ${(c.units || []).map(u => `<strong>${escapeHtml(u.unit_code)}</strong> (${u.area} m²)`).join(', ') || 'Chưa định danh'}
              </span>
              <button class="btn-mo-primary" style="padding:6px 14px;font-size:12px;" onclick="openTenantContractDetail('${c.id}')">
                <i class="fa-solid fa-file-contract"></i> Xem Chi Tiết Điều Khoản & File Scan
              </button>
            </div>
          </div>
        `).join('');
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
          if (emailEl) emailEl.value = profile.contact_email || '';
          const addrEl = document.getElementById('tenant-profile-address');
          if (addrEl) addrEl.value = profile.business_address || '';
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

    function handleContractSearch(val) {
      currentContractSearch = val.trim();
      loadContractsList();
    }

    function handleContractStatusFilter(status) {
      currentContractStatus = status;
      document.querySelectorAll('.contract-status-btn').forEach(b => {
        b.classList.remove('active');
        if (b.getAttribute('data-status') === status) b.classList.add('active');
      });
      loadContractsList();
    }
</script>
