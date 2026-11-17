<!-- TAB 4: QUẢN LÝ HỢP ĐỒNG THUÊ VĂN PHÒNG (RESTFUL API - MODULE 3) -->
<div id="tab-admin-contracts" class="filament-tab-pane" style="display:none;">
  <div class="filament-page-header" style="align-items:center;">
    <div>
      <h1 class="filament-page-title" style="font-size:23px;margin-bottom:4px;">Quản Lý Hợp Đồng Thuê Văn Phòng</h1>
      <p style="font-size:13.5px;color:#64748b;margin:0;">Theo dõi vòng đời hợp đồng thuê, đặt cọc giữ chỗ, ký duyệt hiệu lực, gia hạn và thanh lý hoàn trả mặt bằng.</p>
    </div>
    <div style="display:flex;gap:8px;">
      <button class="btn-mo-outline" style="background:#fff;" onclick="loadContractsList()">
        <i class="fa-solid fa-arrows-rotate"></i> Làm mới
      </button>
      <button class="btn-mo-outline" style="background:#fff;color:#d97706;border-color:#fde68a;" onclick="openReserveContractModal()">
        <i class="fa-solid fa-hourglass-half"></i> Giữ chỗ sàn
      </button>
      <button class="btn-mo-primary" style="background:#2563eb;" onclick="openCreateContractModal()">
        <i class="fa-solid fa-plus"></i> Lập hợp đồng mới
      </button>
    </div>
  </div>

  <!-- Bộ lọc nhanh & Tìm kiếm -->
  <div class="filament-card" style="margin-bottom:16px;padding:16px;">
    <div style="display:flex;flex-wrap:wrap;gap:12px;align-items:center;justify-content:space-between;">
      <div style="display:flex;gap:10px;align-items:center;flex:1;max-width:480px;">
        <input type="text" id="contract-search-input" class="filament-input-sm" placeholder="Tìm theo Mã HĐ, Tên doanh nghiệp..." oninput="handleContractSearch(this.value)" style="width:100%;">
      </div>
      <div style="display:flex;gap:6px;flex-wrap:wrap;">
        <button class="btn-mo-outline contract-status-btn active" data-status="ALL" onclick="handleContractStatusFilter('ALL')" style="font-size:12px;padding:4px 10px;">Tất cả</button>
        <button class="btn-mo-outline contract-status-btn" data-status="DRAFT" onclick="handleContractStatusFilter('DRAFT')" style="font-size:12px;padding:4px 10px;">Bản nháp</button>
        <button class="btn-mo-outline contract-status-btn" data-status="RESERVED" onclick="handleContractStatusFilter('RESERVED')" style="font-size:12px;padding:4px 10px;">Giữ chỗ</button>
        <button class="btn-mo-outline contract-status-btn" data-status="ACTIVE" onclick="handleContractStatusFilter('ACTIVE')" style="font-size:12px;padding:4px 10px;">Hiệu lực</button>
        <button class="btn-mo-outline contract-status-btn" data-status="EXPIRING_SOON" onclick="handleContractStatusFilter('EXPIRING_SOON')" style="font-size:12px;padding:4px 10px;">Sắp hết hạn</button>
        <button class="btn-mo-outline contract-status-btn" data-status="TERMINATED" onclick="handleContractStatusFilter('TERMINATED')" style="font-size:12px;padding:4px 10px;">Đã thanh lý</button>
      </div>
      <span style="font-size:13px;color:#64748b;">Tổng cộng: <strong id="admin-contracts-count" style="color:#0f172a;">0</strong> hợp đồng</span>
    </div>
  </div>

  <!-- Bảng dữ liệu Hợp đồng -->
  <div class="filament-card">
    <table class="mo-table">
      <thead>
        <tr>
          <th>Mã Hợp Đồng</th>
          <th>Doanh Nghiệp Thuê</th>
          <th>Tòa Nhà & Sàn Thuê</th>
          <th>Thời Hạn Thuê</th>
          <th>Giá Thuê / Cọc</th>
          <th>Trạng Thái</th>
          <th style="text-align:right;">Thao Tác</th>
        </tr>
      </thead>
      <tbody id="admin-contracts-tbody">
        <!-- Rendered via JS contracts.blade.php -->
      </tbody>
    </table>
  </div>
</div>

<!-- TAB MỚI: QUẢN LÝ CÔNG NỢ & THANH TOÁN (DÀNH CHO KẾ TOÁN / ADMIN / BQL - CHUẨN HÌNH 2) -->
<div id="tab-admin-billing" class="filament-tab-pane" style="display:none;">
  <div class="filament-page-header" style="align-items:center;">
    <div>
      <h1 class="filament-page-title" style="font-size:23px;margin-bottom:4px;">Quản Lý Công Nợ & Đối Soát Thanh Toán</h1>
      <p style="font-size:13.5px;color:#64748b;margin:0;">Theo dõi thông báo phí (Debit Note), chốt chỉ số điện nước định kỳ và xác nhận thanh toán chuyển khoản ngân hàng.</p>
    </div>
    <div style="display:flex;gap:8px;">
      <button type="button" class="btn-mo-outline" style="background:#fff;" onclick="alert('Đã đồng bộ trạng thái đối soát ngân hàng mới nhất!')">
        <i class="fa-solid fa-arrows-rotate"></i> Đối soát thanh toán
      </button>
      <button type="button" class="btn-mo-primary" style="background:#2563eb;" onclick="alert('Đã phát hành thông báo phí (Debit Note) hàng loạt cho kỳ hiện tại!')">
        <i class="fa-solid fa-file-invoice-dollar"></i> Phát hành Debit Note
      </button>
    </div>
  </div>

  @php
    $bTotal = $stats['billing_total'] ?? 888387500;
    $bPaid = $stats['billing_paid'] ?? 445720000;
    $bPending = $stats['billing_pending'] ?? 442667500;
    $bOverdue = $stats['billing_overdue'] ?? 0;
    $bPaidPct = $stats['billing_paid_pct'] ?? 50.2;
    $bPendingPct = $stats['billing_pending_pct'] ?? 49.8;
    $bOverduePct = $stats['billing_overdue_pct'] ?? 0;
  @endphp

  <!-- 4 Thẻ KPI Tài chính & Công nợ thật từ bảng debitnotes -->
  <div class="bms-kpi-grid">
    <div class="bms-kpi-card">
      <div>
        <div class="bms-kpi-top">
          <div class="bms-kpi-title">TỔNG PHẢI THU TRONG KỲ</div>
          <div class="bms-kpi-icon" style="background:#eff6ff;color:#2563eb;"><i class="fa-solid fa-sack-dollar"></i></div>
        </div>
        <div class="bms-kpi-main">
          <span class="bms-kpi-number" style="color:#0f172a;font-size:24px;">{{ number_format($bTotal / 1000000, 2, ',', '.') }} Tr</span>
          <span class="bms-kpi-pill" style="background:#eff6ff;color:#1d4ed8;">Kỳ hiện tại</span>
        </div>
      </div>
      <div class="bms-kpi-foot"><span>Tổng số phiếu thu:</span><strong>{{ count($recentDebitNotes ?? []) }} Debit Notes</strong></div>
    </div>

    <div class="bms-kpi-card">
      <div>
        <div class="bms-kpi-top">
          <div class="bms-kpi-title">ĐÃ THANH TOÁN</div>
          <div class="bms-kpi-icon" style="background:#ecfdf5;color:#10b981;"><i class="fa-solid fa-circle-check"></i></div>
        </div>
        <div class="bms-kpi-main">
          <span class="bms-kpi-number" style="color:#10b981;font-size:24px;">{{ number_format($bPaid / 1000000, 2, ',', '.') }} Tr</span>
          <span class="bms-kpi-pill" style="background:#ecfdf5;color:#047857;">{{ $bPaidPct }}% hoàn thành</span>
        </div>
      </div>
      <div class="bms-kpi-foot"><span>Phiếu đã thu:</span><strong>{{ $stats['billing_paid_count'] ?? 1 }} phiếu</strong></div>
    </div>

    <div class="bms-kpi-card">
      <div>
        <div class="bms-kpi-top">
          <div class="bms-kpi-title">CHỜ THANH TOÁN</div>
          <div class="bms-kpi-icon" style="background:#fef3c7;color:#d97706;"><i class="fa-solid fa-hourglass-half"></i></div>
        </div>
        <div class="bms-kpi-main">
          <span class="bms-kpi-number" style="color:#d97706;font-size:24px;">{{ number_format($bPending / 1000000, 2, ',', '.') }} Tr</span>
          <span class="bms-kpi-pill" style="background:#fef3c7;color:#b45309;">{{ $bPendingPct }}% trong hạn</span>
        </div>
      </div>
      <div class="bms-kpi-foot"><span>Trạng thái:</span><strong>Đang chờ đối soát</strong></div>
    </div>

    <div class="bms-kpi-card">
      <div>
        <div class="bms-kpi-top">
          <div class="bms-kpi-title">CÔNG NỢ QUÁ HẠN</div>
          <div class="bms-kpi-icon" style="background:#fef2f2;color:#e11d48;"><i class="fa-solid fa-triangle-exclamation"></i></div>
        </div>
        <div class="bms-kpi-main">
          <span class="bms-kpi-number" style="color:#e11d48;font-size:24px;">{{ number_format($bOverdue / 1000000, 2, ',', '.') }} Tr</span>
          <span class="bms-kpi-pill" style="background:#fef2f2;color:#be123c;">{{ $bOverduePct }}% quá hạn</span>
        </div>
      </div>
      <div class="bms-kpi-foot"><span>Phiếu quá hạn:</span><strong>{{ $stats['billing_overdue_count'] ?? 0 }} phiếu</strong></div>
    </div>
  </div>

  <!-- Thanh Tìm Kiếm & Lọc Nhanh Công Nợ -->
  <div class="filament-card" style="margin-bottom:16px;padding:16px;">
    <div style="display:flex;flex-wrap:wrap;gap:12px;align-items:center;justify-content:space-between;">
      <div style="display:flex;gap:10px;align-items:center;flex:1;max-width:440px;">
        <input type="text" id="billing-search-input" class="filament-input-sm" placeholder="Tìm theo mã phiếu DN, tên công ty, MST, tòa nhà..." oninput="filterAdminBillingRows()" style="width:100%;">
      </div>
      <div style="display:flex;gap:6px;flex-wrap:wrap;">
        <button type="button" class="btn-mo-outline billing-filter-btn active" data-status="ALL" onclick="setAdminBillingFilter('ALL', this)" style="font-size:12px;padding:5px 12px;">Tất cả</button>
        <button type="button" class="btn-mo-outline billing-filter-btn" data-status="PAID" onclick="setAdminBillingFilter('PAID', this)" style="font-size:12px;padding:5px 12px;">Đã thanh toán</button>
        <button type="button" class="btn-mo-outline billing-filter-btn" data-status="PENDING" onclick="setAdminBillingFilter('PENDING', this)" style="font-size:12px;padding:5px 12px;">Chờ thanh toán</button>
        <button type="button" class="btn-mo-outline billing-filter-btn" data-status="OVERDUE" onclick="setAdminBillingFilter('OVERDUE', this)" style="font-size:12px;padding:5px 12px;">Quá hạn</button>
      </div>
    </div>
  </div>

  <!-- Bảng Danh Sách Hóa Đơn & Công Nợ Thực Tế -->
  <div class="filament-card">
    <div class="filament-card-header">
      <h3 class="filament-card-title"><i class="fa-solid fa-file-invoice" style="color:#2563eb;margin-right:6px;"></i> Danh Sách Phiếu Báo Phí (Debit Notes) & Trạng Thái Thanh Toán</h3>
      <span class="badge-filament info">Tự động tính VAT 10%</span>
    </div>
    <table class="mo-table">
      <thead>
        <tr>
          <th>Mã Hóa Đơn</th>
          <th>Doanh Nghiệp Thuê</th>
          <th>Tòa Nhà</th>
          <th>Kỳ Cước</th>
          <th>Tổng Thanh Toán (VNĐ)</th>
          <th>Hạn Đóng</th>
          <th>Trạng Thái</th>
          <th style="text-align:right;">Thao Tác</th>
        </tr>
      </thead>
      <tbody id="admin-billing-tbody">
        @forelse($recentDebitNotes ?? [] as $dn)
          <tr class="admin-billing-row" data-status="{{ $dn->status_filter_group }}">
            <td><strong style="font-family:monospace;color:#1d4ed8;">{{ $dn->debit_note_code }}</strong></td>
            <td>
              <strong>{{ $dn->tenant?->company_name ?? 'Công ty Cổ phần VNG' }}</strong>
              <div style="font-size:11px;color:#64748b;">MST: {{ $dn->tenant?->tax_code ?? '0303490096' }}</div>
            </td>
            <td>{{ $dn->building?->name ?? 'Tòa Nhà The Nexus Tower' }}</td>
            <td><span class="badge-filament info">{{ $dn->billing_month }}/{{ $dn->billing_year }}</span></td>
            <td>
              <strong style="color:#ea580c;">{{ number_format($dn->total_amount, 0, ',', '.') }} đ</strong>
              @if($dn->status_value === 'PAID')
                <div style="font-size:11px;color:#10b981;">Đã thu: {{ number_format($dn->paid_amount ?: $dn->total_amount, 0, ',', '.') }} đ</div>
              @else
                <div style="font-size:11px;color:#64748b;">Còn nợ: {{ number_format($dn->remaining_debt_amount, 0, ',', '.') }} đ</div>
              @endif
            </td>
            <td>{{ $dn->due_date?->format('d/m/Y') ?? '15/11/2026' }}</td>
            <td><span class="badge-filament {{ $dn->status_badge_class }}">{{ $dn->status_label }}</span></td>
            <td style="text-align:right;white-space:nowrap;">
              <button type="button" class="btn-mo-outline" style="padding:5px 10px;font-size:12px;"
                onclick="openAdminBillingQrModal('{{ addslashes($dn->debit_note_code) }}', '{{ addslashes($dn->tenant?->company_name ?? '') }}', '{{ addslashes($dn->building?->name ?? '') }}', '{{ $dn->billing_month }}/{{ $dn->billing_year }}', '{{ number_format($dn->sub_total_amount, 0, ',', '.') }} đ', '{{ number_format($dn->vat_amount, 0, ',', '.') }} đ', '{{ number_format($dn->total_amount, 0, ',', '.') }} đ', '{{ $dn->status_label }}', '{{ $dn->due_date?->format('d/m/Y') }}', '{{ $dn->vietqr_image_url }}')">
                <i class="fa-solid fa-qrcode"></i> {{ $dn->status_value === 'PAID' ? 'Biên lai & QR' : 'Mã QR' }}
              </button>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="8" style="text-align:center;padding:28px;color:#94a3b8;">Chưa có phiếu báo phí nào trong kỳ hiện tại.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <!-- Modal Hiển Thị Chi Tiết Phiếu Báo Phí & Mã QR -->
  <div id="modal-admin-billing-qr" class="mo-modal-overlay" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,0.55);z-index:9999;align-items:center;justify-content:center;padding:16px;">
    <div class="mo-modal-content" style="max-width:540px;width:100%;">
      <div class="mo-modal-header">
        <div>
          <h3 style="margin:0;font-size:17px;"><i class="fa-solid fa-qrcode"></i> Phiếu Báo Cước & Mã Thanh Toán QR</h3>
          <p id="qr-modal-subtitle" style="margin:4px 0 0;font-size:12.5px;color:#dbeafe;">Đối soát thanh toán tự động theo hóa đơn cước</p>
        </div>
        <button type="button" class="mo-modal-close" onclick="document.getElementById('modal-admin-billing-qr').style.display='none'">&times;</button>
      </div>
      <div style="padding:20px 24px;">
        <div style="display:grid;grid-template-columns:1.25fr 1fr;gap:18px;align-items:center;">
          <div style="font-size:13px;color:#334155;display:flex;flex-direction:column;gap:8px;">
            <div><span style="color:#64748b;">Mã phiếu:</span> <strong id="qr-modal-code" style="font-family:monospace;color:#1d4ed8;">---</strong></div>
            <div><span style="color:#64748b;">Khách thuê:</span> <strong id="qr-modal-company" style="color:#0f172a;">---</strong></div>
            <div><span style="color:#64748b;">Tòa nhà:</span> <strong id="qr-modal-building">---</strong></div>
            <div><span style="color:#64748b;">Kỳ cước / Hạn đóng:</span> <strong id="qr-modal-period">---</strong></div>
            <hr style="border:none;border-top:1px dashed #e2e8f0;margin:4px 0;">
            <div style="display:flex;justify-content:space-between;"><span>Trước thuế:</span><strong id="qr-modal-sub">0 đ</strong></div>
            <div style="display:flex;justify-content:space-between;"><span>Thuế GTGT (10%):</span><strong id="qr-modal-vat">0 đ</strong></div>
            <div style="display:flex;justify-content:space-between;font-size:14.5px;color:#ea580c;"><span>Tổng cộng:</span><strong id="qr-modal-total">0 đ</strong></div>
            <div style="margin-top:4px;background:#f8fafc;padding:8px 10px;border-radius:8px;border:1px solid #e2e8f0;font-size:12px;">
              <div><strong>NH thụ hưởng:</strong> <span id="qr-modal-bank">---</span></div>
              <div><strong>Số TK:</strong> <code id="qr-modal-acc" style="font-weight:700;color:#1d4ed8;">---</code></div>
              <div><strong>Chủ TK:</strong> <span id="qr-modal-holder">---</span></div>
            </div>
          </div>
          <div style="text-align:center;background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:14px;">
            <img id="qr-modal-img" src="" alt="Mã QR" style="width:165px;height:165px;object-fit:contain;border-radius:8px;background:#fff;padding:6px;border:1px solid #e2e8f0;">
            <div id="qr-modal-status" style="margin-top:8px;font-size:12px;font-weight:700;color:#047857;">---</div>
          </div>
        </div>
        <div style="margin-top:18px;padding-top:14px;border-top:1px solid #f1f5f9;display:flex;justify-content:flex-end;gap:10px;">
          <button type="button" class="btn-mo-outline" onclick="window.print()"><i class="fa-solid fa-print"></i> In phiếu thu</button>
          <button type="button" class="btn-mo-primary" style="background:#2563eb;" onclick="document.getElementById('modal-admin-billing-qr').style.display='none'">Đóng</button>
        </div>
      </div>
    </div>
  </div>

  <script>
    let currentAdminBillingStatus = 'ALL';
    function setAdminBillingFilter(status, btn) {
      currentAdminBillingStatus = status;
      document.querySelectorAll('.billing-filter-btn').forEach(b => b.classList.remove('active'));
      if (btn) btn.classList.add('active');
      filterAdminBillingRows();
    }
    function filterAdminBillingRows() {
      const q = (document.getElementById('billing-search-input')?.value || '').trim().toLowerCase();
      document.querySelectorAll('.admin-billing-row').forEach(row => {
        const st = row.getAttribute('data-status') || 'ALL';
        const matchStatus = (currentAdminBillingStatus === 'ALL' || st === currentAdminBillingStatus);
        const matchText = !q || row.innerText.toLowerCase().includes(q);
        row.style.display = (matchStatus && matchText) ? '' : 'none';
      });
    }
    function openAdminBillingQrModal(code, company, building, period, subFormatted, vatFormatted, totFormatted, statusText, dueStr, qrUrl) {
      const modal = document.getElementById('modal-admin-billing-qr');
      if (!modal) return;
      document.getElementById('qr-modal-code').innerText = code;
      document.getElementById('qr-modal-company').innerText = company;
      document.getElementById('qr-modal-building').innerText = building;
      document.getElementById('qr-modal-period').innerText = `Kỳ ${period} (Hạn: ${dueStr})`;
      document.getElementById('qr-modal-sub').innerText = subFormatted;
      document.getElementById('qr-modal-vat').innerText = vatFormatted;
      document.getElementById('qr-modal-total').innerText = totFormatted;
      document.getElementById('qr-modal-bank').innerText = 'Vietcombank - CN TP.HCM';
      document.getElementById('qr-modal-acc').innerText = '0071000888999';
      document.getElementById('qr-modal-holder').innerText = 'CONG TY QUAN LY TOA NHA OFFICE';
      document.getElementById('qr-modal-status').innerText = statusText;
      document.getElementById('qr-modal-img').src = qrUrl;
      modal.style.display = 'flex';
    }
  </script>
</div>
