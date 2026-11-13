<!-- TAB 5: QUẢN LÝ TIẾP NHẬN LEAD CRM (LIVEWIRE 3) -->
<div id="tab-sales-leads" class="filament-tab-pane" style="display:none;">
  <livewire:crm-lead-pipeline />
</div>

<!-- TAB 6: LẬP BÁO GIÁ CHO SALES & CÔNG CỤ TÍNH NHANH -->
<div id="tab-sales-quotes" class="filament-tab-pane" style="display:none;">
  <div class="filament-page-header">
    <div>
      <div class="filament-breadcrumbs">Phân hệ Kinh doanh / Báo giá</div>
      <h1 class="filament-page-title">Lập & Quản Lý Hồ Sơ Báo Giá Cho Khách</h1>
    </div>
    <a href="{{ route('quote.page') }}" target="_blank" class="btn-mo-orange" style="text-decoration:none;">
      <i class="fa-solid fa-file-circle-plus"></i> Mở Trang Nhận Báo Giá SSR
    </a>
  </div>

  <div class="filament-card" style="padding:24px;">
    <h3 style="font-size:18px;font-weight:700;margin-bottom:14px;color:var(--mo-primary);">
      <i class="fa-solid fa-calculator"></i> Công Cụ Tính Nhanh Chi Phí Thuê Văn Phòng
    </h3>
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:18px;">
      <div class="form-field">
        <label>Chọn Tòa Nhà</label>
        <select id="calc-building" onchange="calculateQuickQuote()">
          @forelse($serverBuildings ?? [] as $b)
            <option value="{{ $b['priceUsd'] ?? 30 }}">{{ $b['name'] }} (${{ $b['priceUsd'] ?? 30 }}/m²)</option>
          @empty
            <option value="48">Vietcombank Tower ($48/m²)</option>
            <option value="58">Deutsches Haus ($58/m²)</option>
            <option value="28">Lim Tower 3 ($28/m²)</option>
            <option value="22">Pearl Plaza ($22/m²)</option>
          @endforelse
        </select>
      </div>
      <div class="form-field">
        <label>Diện Tích Thuê (m²)</label>
        <input type="number" id="calc-area" value="250" oninput="calculateQuickQuote()">
      </div>
      <div class="form-field">
        <label>Phí Quản Lý ($/m²)</label>
        <input type="number" id="calc-service" value="6" oninput="calculateQuickQuote()">
      </div>
    </div>

    <!-- Calculation Result Banner -->
    <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:20px;display:flex;justify-content:space-between;align-items:center;">
      <div>
        <div style="font-size:13px;color:#64748b;">Tổng tiền thuê dự kiến hàng tháng:</div>
        <div style="font-size:28px;font-weight:800;color:#ea580c;" id="calc-total-usd">$13,500 / tháng</div>
        <div style="font-size:13px;color:#94a3b8;" id="calc-total-vnd">Tương đương: ~330.750.000 VNĐ / tháng (Chưa gồm VAT)</div>
      </div>
      <button class="btn-mo-primary" type="button" onclick="alert('Đã xuất file báo giá PDF gửi cho khách hàng!')">
        <i class="fa-solid fa-paper-plane"></i> Xuất Báo Giá PDF
      </button>
    </div>
  </div>
</div>

<!-- TAB 7: PIPELINE KANBAN (VÒNG ĐỜI KHÁCH HÀNG CRM) -->
<div id="tab-sales-pipeline" class="filament-tab-pane" style="display:none;">
  <livewire:crm-lead-pipeline />
</div>
