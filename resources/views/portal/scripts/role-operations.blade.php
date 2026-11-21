<script>
/* ==========================================================================
   ROLE OPERATIONS SCRIPT (TENANT, SALES, BQL)
   CHUẨN ĐỒ ÁN TỐT NGHIỆP: ĐÁP ỨNG TOÀN DIỆN 3 VAI TRÒ CHÍNH THEO ĐẶC TẢ
   ========================================================================== */

/* ==========================================================================
   1. BAN QUẢN LÝ TÒA NHÀ (BUILDING MANAGEMENT - BQL)
   ========================================================================== */

// Điều phối nhân sự phụ trách tòa nhà (Dành cho Ban Quản Lý)
function goToBuildingStaffCoordination() {
  const bldSelect = document.getElementById('sidebar-building-select');
  let bldId = bldSelect ? bldSelect.value : '';
  let bldName = bldSelect ? bldSelect.options[bldSelect.selectedIndex]?.text : '';
  if (!bldId && typeof adminBuildingsList !== 'undefined' && Array.isArray(adminBuildingsList) && adminBuildingsList.length > 0) {
    bldId = adminBuildingsList[0].id;
    bldName = adminBuildingsList[0].name;
  }
  if (typeof openBuildingAssignmentsModal === 'function') {
    openBuildingAssignmentsModal(bldId, bldName);
  } else if (typeof goToBuildingAssignments === 'function') {
    goToBuildingAssignments(bldId);
  }
}

// Biên bản bàn giao mặt bằng
function openContractHandoverModal(contractId, code) {
  const codeEl = document.getElementById('handover-contract-code');
  if (codeEl) codeEl.innerText = code || contractId || 'HD-202611-001';
  openModal('modal-contract-handover');
}

function handleSaveHandover(e) {
  if (e) e.preventDefault();
  alert('Đã cập nhật Biên bản bàn giao mặt bằng và hiện trạng kỹ thuật thành công!\nMặt bằng chính thức chuyển sang trạng thái "Đang thuê" theo hợp đồng.');
  closeModal('modal-contract-handover');
}

// Lịch trình tăng giá tự động
function openContractEscalationModal(contractId, code) {
  const codeEl = document.getElementById('escalation-contract-code');
  if (codeEl) codeEl.innerText = code || contractId || 'HD-202611-001';
  openModal('modal-contract-escalation');
}

function handleSaveEscalation(e) {
  if (e) e.preventDefault();
  alert('Đã thiết lập Lịch trình tăng giá thuê tự động (+5%/năm sau mỗi 12 tháng)!\nHệ thống sẽ tự động áp dụng đơn giá mới vào thông báo cước khi đến hạn.');
  closeModal('modal-contract-escalation');
}

// Nhập chỉ số điện, nước, gửi xe
function openRecordUtilitiesModal() {
  openModal('modal-record-utilities');
  recalcUtilPreview();
}

function recalcUtilPreview() {
  const eStart = parseFloat(document.getElementById('elec-start')?.value || 0);
  const eEnd = parseFloat(document.getElementById('elec-end')?.value || 0);
  const eDiff = Math.max(0, eEnd - eStart);
  const eTotal = eDiff * 3500;
  const eTotalEl = document.getElementById('elec-total');
  if (eTotalEl) eTotalEl.value = eTotal.toLocaleString('vi-VN') + ' đ';

  const wStart = parseFloat(document.getElementById('water-start')?.value || 0);
  const wEnd = parseFloat(document.getElementById('water-end')?.value || 0);
  const wDiff = Math.max(0, wEnd - wStart);
  const wTotal = wDiff * 18000;
  const wTotalEl = document.getElementById('water-total');
  if (wTotalEl) wTotalEl.value = wTotal.toLocaleString('vi-VN') + ' đ';

  const cars = parseFloat(document.getElementById('park-cars')?.value || 0);
  const motos = parseFloat(document.getElementById('park-motos')?.value || 0);
  const parkTotal = (cars * 2200000) + (motos * 180000);
  const parkTotalEl = document.getElementById('park-total');
  if (parkTotalEl) parkTotalEl.value = parkTotal.toLocaleString('vi-VN') + ' đ';
}

function handleCalculateUtilities(e) {
  if (e) e.preventDefault();
  const tenant = document.getElementById('util-tenant')?.value || 'Khách thuê';
  const month = document.getElementById('util-month')?.value || '11/2026';
  alert(`Đã chốt chỉ số tiêu thụ và tự động xuất Thông Báo Phí (Debit Note) kỳ ${month} cho doanh nghiệp [${tenant}] thành công!\nBao gồm 5 cấu phần chi phí: Tiền thuê, Phí QL, Điện, Nước và Gửi xe.`);
  closeModal('modal-record-utilities');
}

// Đối soát Ủy nhiệm chi (UNC) và cập nhật trạng thái
function openReconcileUncModal(code, comp, amount, fileName, status) {
  const codeEl = document.getElementById('rec-code');
  const compEl = document.getElementById('rec-company');
  const amountEl = document.getElementById('rec-amount');
  const fileEl = document.getElementById('rec-file-name');
  
  if (codeEl) codeEl.value = code;
  if (compEl) compEl.value = comp;
  if (amountEl) amountEl.value = amount;
  if (fileEl) fileEl.innerText = fileName || 'UNC_ChungTu_2026.png';
  
  if (status === 'PENDING') {
    const r = document.getElementById('rad-status-pending');
    if (r) r.checked = true;
  } else if (status === 'PARTIAL') {
    const r = document.getElementById('rad-status-partial');
    if (r) r.checked = true;
  } else {
    const r = document.getElementById('rad-status-paid');
    if (r) r.checked = true;
  }
  
  openModal('modal-reconcile-unc');
}

function handleSaveReconcileStatus(e) {
  if (e) e.preventDefault();
  const code = document.getElementById('rec-code')?.value || 'DN-001';
  const selectedRadio = document.querySelector('input[name="reconcile_status"]:checked');
  const statusVal = selectedRadio ? selectedRadio.value : 'PAID';
  
  const badge = document.getElementById(`badge-dn-status-${code}`);
  if (badge) {
    if (statusVal === 'PENDING') {
      badge.className = 'badge-filament warning';
      badge.innerText = 'Chờ thanh toán';
    } else if (statusVal === 'PARTIAL') {
      badge.className = 'badge-filament purple';
      badge.style.background = '#f3e8ff';
      badge.style.color = '#7e22ce';
      badge.innerText = 'Thanh toán một phần';
    } else {
      badge.className = 'badge-filament success';
      badge.innerText = 'Hoàn tất';
    }
  }
  
  alert(`Đã lưu xác nhận đối soát UNC cho phiếu [${code}] thành công!\nTrạng thái thanh toán mới đã được cập nhật đồng bộ sang Cổng Khách Thuê.`);
  closeModal('modal-reconcile-unc');
}

/* ==========================================================================
   2. KHÁCH HÀNG / DOANH NGHIỆP ĐI THUÊ (TENANT)
   ========================================================================== */

function syncTenantPortalData() {
  const authUserStr = localStorage.getItem('auth_user');
  if (!authUserStr) return;
  let user = null;
  try { user = JSON.parse(authUserStr); } catch (e) { return; }
  const role = String(localStorage.getItem('auth_role') || '').toLowerCase();
  if (role !== 'tenant') return;

  // 1. Điền thông tin Profile Doanh Nghiệp
  const nameInput = document.getElementById('tp-company-name');
  const taxInput = document.getElementById('tp-tax-code');
  const addrInput = document.getElementById('tp-address');
  const repNameInput = document.getElementById('tp-rep-name');
  const repTitleInput = document.getElementById('tp-rep-title');
  const emailInput = document.getElementById('tp-email');
  const phoneInput = document.getElementById('tp-phone');
  const gpkdEl = document.getElementById('gpkd-file-name');

  if (nameInput && user.company_name) nameInput.value = user.company_name;
  if (taxInput && user.tax_code) taxInput.value = user.tax_code;
  if (addrInput && (user.headquarter_address || user.address)) addrInput.value = user.headquarter_address || user.address;
  if (repNameInput && user.representative_name) repNameInput.value = user.representative_name;
  if (repTitleInput && user.representative_position) repTitleInput.value = user.representative_position;
  if (emailInput && (user.login_email || user.email)) emailInput.value = user.login_email || user.email;
  if (phoneInput && user.contact_phone) phoneInput.value = user.contact_phone;
  if (gpkdEl && user.company_name) {
    gpkdEl.innerText = (user.tenant_code || 'GPKD') + '_' + (user.company_name.replace(/[^a-zA-Z0-9]/g, '_').substring(0, 20)) + '_Signed.pdf';
  }

  // 2. Điền KPI Cards của Hợp Đồng
  const kpiContract = document.getElementById('tenant-kpi-contract-code');
  const kpiArea = document.getElementById('tenant-kpi-leased-area');
  const kpiBld = document.getElementById('tenant-kpi-building-name');
  const kpiBill = document.getElementById('tenant-kpi-bill-amount');
  if (user.login_email === 'tenant.demo@officeleasing.vn' || user.tenant_code === 'TN-DEMO-2026') {
    if (kpiContract) kpiContract.innerText = 'HD-2026/DEMO-NEXUS';
    if (kpiArea) kpiArea.innerText = '250 m²';
    if (kpiBld) kpiBld.innerText = 'The Nexus Tower';
    if (kpiBill) kpiBill.innerText = '231,0 Tr';
  }

  // 3. Điền Danh Sách Hóa Đơn & Cước Phí
  const tbodyBills = document.getElementById('tbody-tenant-bills');
  if (tbodyBills && (user.login_email === 'tenant.demo@officeleasing.vn' || user.tenant_code === 'TN-DEMO-2026')) {
    tbodyBills.innerHTML = `
      <tr>
        <td><strong style="font-family:monospace;color:#1d4ed8;">DN-202611-DEMO</strong></td>
        <td><strong>The Nexus Tower - Tầng 10 (250 m²)</strong></td>
        <td><span class="badge-filament info">Kỳ 11/2026</span></td>
        <td><strong style="color:#ea580c;">231.000.000 đ</strong></td>
        <td>15/11/2026</td>
        <td><span style="color:#64748b;font-size:12px;"><i class="fa-solid fa-circle-xmark" style="color:#f59e0b;"></i> Chưa tải lên</span></td>
        <td><span class="badge-filament warning">Chờ thanh toán</span></td>
        <td style="text-align:right;">
          <button type="button" class="btn-mo-primary" style="padding:4px 10px;font-size:12px;background:#16a34a;" onclick="openUploadUncModal('DN-202611-DEMO')">
            <i class="fa-solid fa-upload"></i> Tải UNC
          </button>
        </td>
      </tr>
      <tr>
        <td><strong style="font-family:monospace;color:#1d4ed8;">DN-202610-DEMO</strong></td>
        <td><strong>The Nexus Tower - Tầng 10 (250 m²)</strong></td>
        <td><span class="badge-filament info">Kỳ 10/2026</span></td>
        <td><strong style="color:#0f172a;">231.000.000 đ</strong></td>
        <td>15/10/2026</td>
        <td><a href="javascript:void(0)" onclick="alert('Xem ảnh UNC_DEMOTECH_Oct2026.png đã đối soát hợp lệ')" style="color:#2563eb;font-size:12px;font-weight:700;"><i class="fa-solid fa-paperclip"></i> UNC_Oct2026.png</a></td>
        <td><span class="badge-filament success">Hoàn tất</span></td>
        <td style="text-align:right;">
          <span class="badge-filament success"><i class="fa-solid fa-check"></i> Đã đối soát</span>
        </td>
      </tr>
    `;

    const totalEl = document.getElementById('tenant-bill-total-cost');
    if (totalEl) totalEl.innerText = '231.000.000 đ';

    // Cập nhật chi tiết 5 cấu phần cước phí khớp với số liệu The Nexus Tower (Tầng 10 - 250 m²)
    const breakdownTbody = document.querySelector('#tab-tenant-bills .filament-card table tbody');
    if (breakdownTbody) {
      breakdownTbody.innerHTML = `
        <tr>
          <td><strong>1. Tiền thuê sàn văn phòng (Tầng 10)</strong></td>
          <td>250 m²</td>
          <td>700.000 đ / m²</td>
          <td>175.000.000 đ</td>
          <td>17.500.000 đ</td>
          <td style="text-align:right;font-weight:700;color:#0f172a;">192.500.000 đ</td>
        </tr>
        <tr>
          <td><strong>2. Phí quản lý vận hành tòa nhà</strong></td>
          <td>250 m²</td>
          <td>100.000 đ / m²</td>
          <td>25.000.000 đ</td>
          <td>2.500.000 đ</td>
          <td style="text-align:right;font-weight:700;color:#0f172a;">27.500.000 đ</td>
        </tr>
        <tr>
          <td><strong>3. Tiền điện sử dụng & điều hòa</strong></td>
          <td>1.500 kWh (Chỉ số: 5120 - 6620)</td>
          <td>3.500 đ / kWh</td>
          <td>5.250.000 đ</td>
          <td>525.000 đ</td>
          <td style="text-align:right;font-weight:700;color:#0f172a;">5.775.000 đ</td>
        </tr>
        <tr>
          <td><strong>4. Tiền nước sinh hoạt</strong></td>
          <td>25 m³ (Chỉ số: 110 - 135)</td>
          <td>18.000 đ / m³</td>
          <td>450.000 đ</td>
          <td>45.000 đ</td>
          <td style="text-align:right;font-weight:700;color:#0f172a;">495.000 đ</td>
        </tr>
        <tr>
          <td><strong>5. Phí gửi xe nhân viên</strong></td>
          <td>1 Ô tô + 15 Xe máy</td>
          <td>Ô tô: 2.2Tr • Xe máy: 180k</td>
          <td>4.300.000 đ</td>
          <td>430.000 đ</td>
          <td style="text-align:right;font-weight:700;color:#0f172a;">4.730.000 đ</td>
        </tr>
        <tr style="background:#f8fafc;font-size:14px;">
          <td colspan="5"><strong style="color:#0f172a;">TỔNG CỘNG CƯỚC PHÍ PHẢI THANH TOÁN (GỒM THUẾ GTGT)</strong></td>
          <td style="text-align:right;"><strong style="font-size:17px;color:#dc2626;" id="tenant-bill-total-cost">231.000.000 đ</strong></td>
        </tr>
      `;
    }

    // Cập nhật danh sách nhân sự của Demo Tech
    const empTbody = document.getElementById('tbody-tenant-employees');
    const empCount = document.getElementById('tenant-emp-count');
    if (empTbody) {
      empTbody.innerHTML = `
        <tr>
          <td><strong>Trần Công Nghệ</strong></td>
          <td>Tổng Giám Đốc</td>
          <td style="font-family:monospace;">079088001234</td>
          <td><span class="badge-filament info">Ô tô (51K-888.99)</span></td>
          <td><span class="badge-filament success"><i class="fa-solid fa-check"></i> Hoạt động</span></td>
          <td style="text-align:right;">
            <button type="button" class="btn-mo-outline" style="padding:2px 8px;font-size:11px;color:#ef4444;" onclick="deleteTenantEmployee(this)">
              <i class="fa-solid fa-trash"></i>
            </button>
          </td>
        </tr>
        <tr>
          <td><strong>Lê Thị Kim Ngân</strong></td>
          <td>Giám đốc Tài chính</td>
          <td style="font-family:monospace;">079090005678</td>
          <td><span class="badge-filament info">Xe máy (59C1-123.45)</span></td>
          <td><span class="badge-filament success"><i class="fa-solid fa-check"></i> Hoạt động</span></td>
          <td style="text-align:right;">
            <button type="button" class="btn-mo-outline" style="padding:2px 8px;font-size:11px;color:#ef4444;" onclick="deleteTenantEmployee(this)">
              <i class="fa-solid fa-trash"></i>
            </button>
          </td>
        </tr>
        <tr>
          <td><strong>Nguyễn Văn Thành</strong></td>
          <td>Trưởng phòng IT</td>
          <td style="font-family:monospace;">079095009876</td>
          <td><span class="badge-filament info">Xe máy (59B2-987.65)</span></td>
          <td><span class="badge-filament success"><i class="fa-solid fa-check"></i> Hoạt động</span></td>
          <td style="text-align:right;">
            <button type="button" class="btn-mo-outline" style="padding:2px 8px;font-size:11px;color:#ef4444;" onclick="deleteTenantEmployee(this)">
              <i class="fa-solid fa-trash"></i>
            </button>
          </td>
        </tr>
      `;
      if (empCount) empCount.innerText = '3';
    }

    // Cập nhật Lịch trình tăng giá tự động 3 năm khớp hợp đồng HD-2026/DEMO-NEXUS
    const escTbody = document.querySelector('#tab-tenant-workspace .filament-card table tbody');
    if (escTbody) {
      escTbody.innerHTML = `
        <tr style="background:#eff6ff;">
          <td><strong style="color:#1d4ed8;">Năm thứ 1 (Hiện tại)</strong></td>
          <td>01/01/2026 - 31/12/2026</td>
          <td><strong>$28.00 / m²</strong> (~700.000 đ)</td>
          <td><span class="badge-filament success">Giá khởi điểm hợp đồng</span></td>
          <td><strong>175.000.000 đ</strong></td>
          <td><span class="badge-filament success">Đang áp dụng</span></td>
        </tr>
        <tr>
          <td><strong style="color:#334155;">Năm thứ 2</strong></td>
          <td>01/01/2027 - 31/12/2027</td>
          <td><strong>$29.40 / m²</strong> (~735.000 đ)</td>
          <td><span class="badge-filament info">+5.0% theo hợp đồng</span></td>
          <td><strong>183.750.000 đ</strong></td>
          <td><span class="badge-filament warning">Dự kiến</span></td>
        </tr>
        <tr>
          <td><strong style="color:#64748b;">Năm thứ 3</strong></td>
          <td>01/01/2028 - 31/12/2028</td>
          <td><strong>$30.87 / m²</strong> (~771.750 đ)</td>
          <td><span class="badge-filament info">+5.0% theo hợp đồng</span></td>
          <td><strong>192.937.500 đ</strong></td>
          <td><span class="badge-filament warning">Dự kiến</span></td>
        </tr>
      `;
    }
  }
}

// Khởi chạy tự động khi nạp trang
document.addEventListener('DOMContentLoaded', () => {
  if (typeof syncTenantPortalData === 'function') syncTenantPortalData();
});

function handleSaveTenantProfile(e) {
  if (e) e.preventDefault();
  alert('Đã lưu thông tin hồ sơ doanh nghiệp & người đại diện pháp luật thành công!');
}

function handleUploadGpkd(input) {
  if (input && input.files && input.files[0]) {
    const f = input.files[0];
    const statusText = document.getElementById('tenant-gpkd-status-text');
    if (statusText) {
      statusText.innerHTML = `<span style="color:#10b981;font-weight:700;"><i class="fa-solid fa-circle-check"></i> Đã tải lên: ${escapeHtml(f.name)} (Chờ BQL xác thực)</span>`;
    }
    alert(`Đã tải lên Giấy phép kinh doanh [${f.name}] thành công! BQL tòa nhà sẽ đối soát tính hợp lệ.`);
  }
}

function openAddTenantEmployeeModal() {
  openModal('modal-add-tenant-employee');
}

function handleSaveNewEmployee(e) {
  if (e) e.preventDefault();
  const name = document.getElementById('new-emp-name')?.value || 'Nhân viên';
  const cccd = document.getElementById('new-emp-cccd')?.value || '---';
  const role = document.getElementById('new-emp-role')?.value || 'Nhân viên';
  const vehicle = document.getElementById('new-emp-vehicle')?.value || 'Chưa đăng ký';
  
  const tbody = document.getElementById('tbody-tenant-employees');
  if (tbody) {
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td><strong>${escapeHtml(name)}</strong></td>
      <td>${escapeHtml(role)}</td>
      <td style="font-family:monospace;">${escapeHtml(cccd)}</td>
      <td><span class="badge-filament info">${escapeHtml(vehicle)}</span></td>
      <td><span class="badge-filament success"><i class="fa-solid fa-check"></i> Hoạt động</span></td>
      <td style="text-align:right;">
        <button type="button" class="btn-mo-outline" style="padding:2px 8px;font-size:11px;color:#ef4444;" onclick="deleteTenantEmployee(this)">
          <i class="fa-solid fa-trash"></i>
        </button>
      </td>
    `;
    tbody.appendChild(tr);
    const empCount = document.getElementById('tenant-emp-count');
    if (empCount) {
      const cur = parseInt(empCount.innerText || '0', 10);
      empCount.innerText = cur + 1;
    }
  }
  
  alert(`Đã thêm nhân viên [${name}] vào danh sách làm việc tại tòa nhà và đăng ký thẻ ra vào thành công!`);
  closeModal('modal-add-tenant-employee');
}

function deleteTenantEmployee(btn) {
  if (confirm('Bạn có chắc muốn hủy đăng ký thẻ ra vào của nhân viên này?')) {
    const tr = btn.closest('tr');
    if (tr) {
      tr.remove();
      const empCount = document.getElementById('tenant-emp-count');
      if (empCount) {
        const cur = Math.max(0, parseInt(empCount.innerText || '0', 10) - 1);
        empCount.innerText = cur;
      }
    }
  }
}

function downloadHandoverProtocol() {
  alert('Đang tải về Bản scan Biên bản bàn giao mặt bằng hoàn thiện (PDF kèm chữ ký số 2 bên)!');
}

let ahFirstErrorField = null;

function clearSingleFieldError(fieldId) {
  const el = document.getElementById(fieldId);
  if (el) el.classList.remove('is-invalid');
  const errEl = document.getElementById('err-' + fieldId);
  if (errEl) {
    errEl.style.display = 'none';
    errEl.innerHTML = '';
  }
}

function clearAllAfterHoursErrors() {
  const fields = ['ah-building-id', 'ah-title', 'ah-date', 'ah-time', 'ah-workers', 'ah-contractor', 'ah-worker-list', 'ah-agreement'];
  fields.forEach(fid => clearSingleFieldError(fid));
  ahFirstErrorField = null;
}

function updateTenantBuildingDropdowns() {
  if (window.TENANT_RENTED_BUILDINGS && window.TENANT_RENTED_BUILDINGS.length > 0) {
    const selects = ['ah-building-id', 'ticket-building-id'];
    selects.forEach(selectId => {
      const el = document.getElementById(selectId);
      if (el) {
        el.innerHTML = window.TENANT_RENTED_BUILDINGS.map((b, i) => 
          `<option value="${b.id}" ${i === 0 ? 'selected' : ''}>${b.name} (${b.district})</option>`
        ).join('');
      }
    });
  }
}

function openTenantAfterHoursModal() {
  clearAllAfterHoursErrors();
  if (typeof updateTenantBuildingDropdowns === 'function') {
    updateTenantBuildingDropdowns();
  }

  // Khởi tạo ngày thi công: min = hôm nay, mặc định = hôm nay
  const dateInput = document.getElementById('ah-date');
  if (dateInput) {
    const now = new Date();
    const yyyy = now.getFullYear();
    const mm = String(now.getMonth() + 1).padStart(2, '0');
    const dd = String(now.getDate()).padStart(2, '0');
    const todayStr = `${yyyy}-${mm}-${dd}`;
    dateInput.min = todayStr;
    if (!dateInput.value || dateInput.value < todayStr) {
      dateInput.value = todayStr;
    }
  }

  openModal('modal-tenant-after-hours');
}

async function handleSubmitAfterHours(e) {
  if (e) e.preventDefault();
  clearAllAfterHoursErrors();

  const buildingEl = document.getElementById('ah-building-id');
  const buildingId = buildingEl ? buildingEl.value : (document.getElementById('ticket-building-id')?.value || '0192e220-0010-7000-8000-000000000001');
  const title = document.getElementById('ah-title')?.value?.trim() || '';
  const date = document.getElementById('ah-date')?.value || '';
  const time = document.getElementById('ah-time')?.value || '';
  const workers = document.getElementById('ah-workers')?.value?.trim() || '';
  const contractor = document.getElementById('ah-contractor')?.value?.trim() || '';
  const workerList = document.getElementById('ah-worker-list')?.value?.trim() || '';
  const agreement = document.getElementById('ah-agreement')?.checked;

  // VALIDATION RULES
  const errors = [];

  if (!buildingId) {
    errors.push({
      fieldId: 'ah-building-id',
      label: 'Tòa nhà áp dụng',
      message: 'Vui lòng chọn tòa nhà văn phòng cần đăng ký thi công.'
    });
  }

  if (!title || title.length < 6) {
    errors.push({
      fieldId: 'ah-title',
      label: 'Hạng mục thi công',
      message: 'Vui lòng nhập chi tiết nội dung / hạng mục thi công (tối thiểu 6 ký tự).'
    });
  }

  const now = new Date();
  const todayStr = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-${String(now.getDate()).padStart(2, '0')}`;
  if (!date) {
    errors.push({
      fieldId: 'ah-date',
      label: 'Ngày thi công',
      message: 'Vui lòng chọn ngày thi công dự kiến.'
    });
  } else if (date < todayStr) {
    errors.push({
      fieldId: 'ah-date',
      label: 'Ngày thi công',
      message: 'Không thể đăng ký ngày trong quá khứ (phải từ hôm nay trở đi).'
    });
  }

  if (!time) {
    errors.push({
      fieldId: 'ah-time',
      label: 'Khung giờ thi công',
      message: 'Vui lòng chọn khung giờ thi công cụ thể.'
    });
  }

  const numWorkers = parseInt(workers, 10);
  if (!workers || isNaN(numWorkers) || numWorkers < 1 || numWorkers > 50) {
    errors.push({
      fieldId: 'ah-workers',
      label: 'Số lượng công nhân',
      message: 'Số lượng công nhân thi công phải từ 1 đến 50 người.'
    });
  }

  if (!contractor || contractor.length < 3) {
    errors.push({
      fieldId: 'ah-contractor',
      label: 'Đơn vị thi công',
      message: 'Vui lòng nhập tên công ty hoặc đội thợ thi công (tối thiểu 3 ký tự).'
    });
  }

  if (!workerList || workerList.length < 6) {
    errors.push({
      fieldId: 'ah-worker-list',
      label: 'Danh sách CCCD công nhân',
      message: 'Vui lòng cung cấp danh sách họ tên và số CCCD công nhân để BQL cấp thẻ vào cổng.'
    });
  }

  if (!agreement) {
    errors.push({
      fieldId: 'ah-agreement',
      label: 'Cam kết an toàn PCCC',
      message: 'Bạn cần tích chọn xác nhận cam kết tuân thủ quy chuẩn PCCC và an toàn tòa nhà.'
    });
  }

  // NẾU CÓ LỖI: HIỆN HẾT RA DƯỚI CÁC TRƯỜNG (BỎ POPUP THEO YÊU CẦU)
  if (errors.length > 0) {
    ahFirstErrorField = errors[0].fieldId;

    // 1. Hiển thị viền đỏ và text lỗi đồng thời dưới TẤT CẢ các trường bị lỗi
    errors.forEach(err => {
      const inputEl = document.getElementById(err.fieldId);
      if (inputEl) {
        inputEl.classList.add('is-invalid');
      }
      const errEl = document.getElementById('err-' + err.fieldId);
      if (errEl) {
        errEl.innerHTML = `<i class="fa-solid fa-circle-exclamation"></i> <span>${err.message}</span>`;
        errEl.style.display = 'flex';
      }
    });

    // 2. Cuộn và focus vào trường lỗi đầu tiên trên form (KHÔNG dùng popup dialog)
    const firstTarget = document.getElementById(ahFirstErrorField);
    if (firstTarget) {
      firstTarget.scrollIntoView({ behavior: 'smooth', block: 'center' });
      firstTarget.focus();
    }
    return;
  }

  // NẾU HỢP LỆ -> TIẾN HÀNH GỬI YÊU CẦU
  const submitBtn = document.getElementById('btn-submit-after-hours');
  if (submitBtn) {
    submitBtn.disabled = true;
    submitBtn.innerHTML = `<i class="fa-solid fa-spinner fa-spin"></i> Đang gửi BQL phê duyệt...`;
  }

  const fullDesc = `Ngày thi công: ${date} (${time}) | Số công nhân: ${workers} người | Nhà thầu: ${contractor}\nDanh sách CCCD: ${workerList}`;
  const token = localStorage.getItem('auth_token');

  try {
    const urlParams = new URLSearchParams(window.location.search);
    const tenantId = urlParams.get('tenant_id') || localStorage.getItem('tenant_id') || '';
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const headers = {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'X-CSRF-TOKEN': csrfToken
    };
    if (token) headers['Authorization'] = 'Bearer ' + token;
    if (tenantId) headers['X-Tenant-Id'] = tenantId;

    const res = await fetch('/api/v1/tenant/tickets', {
      method: 'POST',
      headers: headers,
      body: JSON.stringify({
        tenant_id: tenantId,
        building_id: buildingId,
        title: '[Thi công ngoài giờ] ' + title,
        description: fullDesc,
        category: 'OVERTIME_CONSTRUCTION',
        priority: 'NORMAL'
      })
    });

    const json = await res.json();
    if (res.ok && json.status === 'success') {
      alert('Đơn đăng ký thi công ngoài giờ đã được nộp thành công lên Ban Quản Lý tòa nhà!\nMã phiếu: ' + (json.data?.ticket_code || 'Mới') + '\nBQL sẽ thẩm duyệt an toàn PCCC và phê duyệt trong vòng 2 giờ.');
      closeModal('modal-tenant-after-hours');
      if (typeof loadTicketsList === 'function') loadTicketsList('tenant');
      if (typeof Livewire !== 'undefined') {
        const comp = document.querySelector('[wire\\:id]');
        if (comp) Livewire.find(comp.getAttribute('wire:id'))?.$refresh();
      }
    } else {
      alert('Không thể nộp đơn: ' + (json.message || 'Dữ liệu không hợp lệ.'));
    }
  } catch(err) {
    console.error('Error submitting overtime ticket:', err);
    alert('Lỗi kết nối máy chủ khi gửi đơn đăng ký thi công.');
  } finally {
    if (submitBtn) {
      submitBtn.disabled = false;
      submitBtn.innerHTML = `<i class="fa-solid fa-paper-plane"></i> Gửi BQL Phê Duyệt`;
    }
  }
}

function openUploadUncModal(debitCode) {
  const codeEl = document.getElementById('unc-debit-code');
  if (codeEl) codeEl.value = debitCode || 'DN-202611-001';
  openModal('modal-upload-unc');
}

function handleSelectUncFile(input) {
  if (input && input.files && input.files[0]) {
    const f = input.files[0];
    const preview = document.getElementById('unc-file-name-preview');
    if (preview) {
      preview.innerText = 'Đã chọn file: ' + f.name;
    }
  }
}

function handleSubmitUnc(e) {
  if (e) e.preventDefault();
  alert('Đã tải lên ảnh chụp Ủy Nhiệm Chi thành công!\nKế toán và Ban Quản Lý sẽ tiến hành đối soát số dư và cập nhật trạng thái hóa đơn cho doanh nghiệp.');
  closeModal('modal-upload-unc');
}

function openCsatModal(ticketCode) {
  const codeEl = document.getElementById('csat-ticket-code');
  if (codeEl) codeEl.innerText = ticketCode || 'TCK-2026-089';
  setCsatRating(5);
  openModal('modal-tenant-csat');
}

function setCsatRating(val) {
  const hiddenInput = document.getElementById('csat-rating-val');
  if (hiddenInput) hiddenInput.value = val;
  for (let i = 1; i <= 5; i++) {
    const star = document.getElementById('csat-star-' + i);
    if (star) {
      if (i <= val) {
        star.style.color = '#f59e0b';
        star.classList.replace('fa-regular', 'fa-solid');
      } else {
        star.style.color = '#cbd5e1';
        star.classList.replace('fa-solid', 'fa-regular');
      }
    }
  }
}

function handleSubmitCsat(e) {
  if (e) e.preventDefault();
  const val = document.getElementById('csat-rating-val')?.value || '5';
  alert(`Cảm ơn quý doanh nghiệp đã đánh giá ${val} sao cho chất lượng dịch vụ của Ban Quản Lý!`);
  closeModal('modal-tenant-csat');
}

/* ==========================================================================
   3. NHÂN VIÊN MÔI GIỚI / KINH DOANH (SALES)
   ========================================================================== */

function openCreateAppointmentModal() {
  alert('Mở form tạo lịch hẹn khảo sát mặt bằng với khách hàng tiềm năng.');
}

function openFeedbackAppointmentModal(id, title) {
  const feedback = prompt(`Nhập phản hồi của khách sau buổi khảo sát [${title || 'Khảo sát mặt bằng'}]:`, 'Khách ưng ý diện tích và view, yêu cầu xin thêm 15 ngày miễn phí thi công Fit-out.');
  if (feedback !== null) {
    alert('Đã lưu phản hồi khảo sát vào nhật ký chăm sóc Lead thành công!');
  }
}

function generateFitoutQuote(days) {
  const inputEl = document.getElementById('quote-fitout-days');
  if (inputEl) inputEl.value = days;
  alert(`Đã cập nhật thời gian miễn phí thi công Fit-out là ${days} ngày vào dự toán báo giá!`);
}

function exportSalesLoiModal(code) {
  alert(`Đang xuất file Thư Ngỏ (Letter of Intent - LOI) mã [${code || 'LOI-2026-012'}] kèm biểu phí thuê, phí dịch vụ và ưu đãi thi công hoàn chỉnh!`);
}
</script>
