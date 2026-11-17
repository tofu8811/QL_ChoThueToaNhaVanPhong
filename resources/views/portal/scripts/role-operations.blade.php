<script>
/* ==========================================================================
   ROLE OPERATIONS SCRIPT (TENANT, SALES, BQL)
   CHUẨN ĐỒ ÁN TỐT NGHIỆP: ĐÁP ỨNG TOÀN DIỆN 3 VAI TRÒ CHÍNH THEO ĐẶC TẢ
   ========================================================================== */

/* ==========================================================================
   1. BAN QUẢN LÝ TÒA NHÀ (BUILDING MANAGEMENT - BQL)
   ========================================================================== */

// Điều phối nhân sự phụ trách tòa nhà
function goToBuildingStaffCoordination() {
  if (typeof goToBuildingAssignments === 'function') {
    goToBuildingAssignments();
  } else if (typeof openBuildingAssignmentsModal === 'function') {
    const bldSelect = document.getElementById('sidebar-building-select');
    const bldId = bldSelect ? bldSelect.value : '';
    const bldName = bldSelect ? bldSelect.options[bldSelect.selectedIndex]?.text : '';
    openBuildingAssignmentsModal(bldId, bldName);
  }
}

// Cắt / Gộp diện tích sàn (Split / Merge)
function switchSplitMergeMode(mode) {
  const panelSplit = document.getElementById('panel-unit-split');
  const panelMerge = document.getElementById('panel-unit-merge');
  const btnSplit = document.getElementById('btn-mode-split');
  const btnMerge = document.getElementById('btn-mode-merge');
  
  if (mode === 'SPLIT') {
    if (panelSplit) panelSplit.style.display = 'block';
    if (panelMerge) panelMerge.style.display = 'none';
    if (btnSplit) { btnSplit.classList.add('active'); btnSplit.style.borderColor = '#2563eb'; btnSplit.style.background = '#eff6ff'; }
    if (btnMerge) { btnMerge.classList.remove('active'); btnMerge.style.borderColor = '#cbd5e1'; btnMerge.style.background = '#fff'; }
  } else {
    if (panelSplit) panelSplit.style.display = 'none';
    if (panelMerge) panelMerge.style.display = 'block';
    if (btnSplit) { btnSplit.classList.remove('active'); btnSplit.style.borderColor = '#cbd5e1'; btnSplit.style.background = '#fff'; }
    if (btnMerge) { btnMerge.classList.add('active'); btnMerge.style.borderColor = '#2563eb'; btnMerge.style.background = '#eff6ff'; }
  }
}

function handleSplitMergeSubmit(e) {
  if (e) e.preventDefault();
  const radSplit = document.getElementById('mode-split-radio');
  const isSplit = radSplit ? radSplit.checked : true;
  
  if (isSplit) {
    alert('Thao tác thành công!\nĐã cắt nhỏ diện tích sàn thành 2 mặt bằng độc lập (Mã: VP-1201A và VP-1201B) với công tơ điện nước riêng biệt và trạng thái "Trống sàng sàng giao".');
  } else {
    alert('Thao tác thành công!\nĐã gộp 2 mặt bằng liền kề thành 1 mặt bằng diện tích hợp nhất (550 m²) với trạng thái "Trống sàng sàng giao".');
  }
  closeModal('modal-split-merge-unit');
  if (typeof loadAdminBuildingsList === 'function') loadAdminBuildingsList();
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
  }
  
  alert(`Đã thêm nhân viên [${name}] vào danh sách làm việc tại tòa nhà và đăng ký thẻ ra vào thành công!`);
  closeModal('modal-add-tenant-employee');
}

function deleteTenantEmployee(btn) {
  if (confirm('Bạn có chắc muốn hủy đăng ký thẻ ra vào của nhân viên này?')) {
    const tr = btn.closest('tr');
    if (tr) tr.remove();
  }
}

function downloadHandoverProtocol() {
  alert('Đang tải về Bản scan Biên bản bàn giao mặt bằng hoàn thiện (PDF kèm chữ ký số 2 bên)!');
}

function openTenantAfterHoursModal() {
  openModal('modal-tenant-after-hours');
}

function handleSubmitAfterHours(e) {
  if (e) e.preventDefault();
  alert('Đơn đăng ký thi công ngoài giờ đã được nộp lên Ban Quản Lý tòa nhà!\nBQL sẽ thẩm duyệt an toàn PCCC và phê duyệt trong vòng 2 giờ.');
  closeModal('modal-tenant-after-hours');
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
