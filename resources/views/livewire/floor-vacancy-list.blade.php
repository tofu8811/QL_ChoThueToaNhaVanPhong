<div class="detail-floors-card">
  <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
    <div>
      <h2 style="font-size: 20px; font-weight: 800; color: var(--mo-primary);"><i class="fa-solid fa-table-cells"></i> Danh Sách Mặt Bằng & Diện Tích Cắt Thuê Trống</h2>
      <p style="font-size: 13.5px; color: #64748b;">Dữ liệu cập nhật theo thời gian thực từ Ban Quản Lý tòa nhà</p>
    </div>
    <span class="badge-filament success"><i class="fa-solid fa-circle"></i> Sẵn sàng bàn giao</span>
  </div>

  <table class="mo-table">
    <thead>
      <tr>
        <th>Vị trí Tầng</th>
        <th>Diện tích trống</th>
        <th>Hiện trạng bàn giao</th>
        <th>Đơn giá thuê</th>
        <th>Tình trạng lấp đầy</th>
        <th>Thao tác</th>
      </tr>
    </thead>
    <tbody id="detail-floors-tbody">
      @forelse($units as $unit)
        @php
          $priceUsd = $unit->base_rental_price ? round($unit->base_rental_price / 24000) : 35;
          $handover = match($unit->handover_condition) {
              'RAW' => 'Trần sàn thô',
              'BASIC' => 'Hoàn thiện cơ bản',
              'FULL_FURNISHED' => 'Nội thất cao cấp',
              default => $unit->handover_condition ?: 'Hoàn thiện tiêu chuẩn'
          };
          $statusLabel = match($unit->status) {
              'AVAILABLE' => 'Đang trống',
              'EXPIRING_SOON' => 'Sắp trống',
              default => 'Sẵn sàng'
          };
        @endphp
        <tr wire:key="unit-row-{{ $unit->id }}">
          <td><strong>Tầng {{ $unit->floor_number }} ({{ $unit->unit_code }})</strong></td>
          <td><strong style="color:var(--mo-blue-accent);">{{ number_format($unit->area_sqm, 0) }} m²</strong></td>
          <td>{{ $handover }}</td>
          <td><strong style="color:#ea580c;">${{ $priceUsd }} / m²</strong></td>
          <td><span class="badge-filament success">{{ $statusLabel }}</span></td>
          <td>
            <button class="btn-mo-orange" style="padding:4px 10px;font-size:12px;" type="button"
                    onclick="bookSpecificFloor('{{ addslashes($building?->name ?? 'Tòa nhà') }}', 'Tầng {{ $unit->floor_number }}', '{{ number_format($unit->area_sqm, 0) }} m²')">
              Đăng ký xem sàn này
            </button>
          </td>
        </tr>
      @empty
        <tr>
          <td><strong>Tầng 5</strong></td>
          <td><strong style="color:var(--mo-blue-accent);">180 m²</strong></td>
          <td>Trần sàn thô</td>
          <td><strong style="color:#ea580c;">$48 / m²</strong></td>
          <td><span class="badge-filament success">Đang trống</span></td>
          <td>
            <button class="btn-mo-orange" style="padding:4px 10px;font-size:12px;" type="button"
                    onclick="bookSpecificFloor('{{ addslashes($building?->name ?? 'Tòa nhà') }}', 'Tầng 5', '180 m²')">
              Đăng ký xem sàn này
            </button>
          </td>
        </tr>
        <tr>
          <td><strong>Tầng 12</strong></td>
          <td><strong style="color:var(--mo-blue-accent);">350 m²</strong></td>
          <td>Hoàn thiện cơ bản</td>
          <td><strong style="color:#ea580c;">$50 / m²</strong></td>
          <td><span class="badge-filament success">Đang trống</span></td>
          <td>
            <button class="btn-mo-orange" style="padding:4px 10px;font-size:12px;" type="button"
                    onclick="bookSpecificFloor('{{ addslashes($building?->name ?? 'Tòa nhà') }}', 'Tầng 12', '350 m²')">
              Đăng ký xem sàn này
            </button>
          </td>
        </tr>
        <tr>
          <td><strong>Tầng 18</strong></td>
          <td><strong style="color:var(--mo-blue-accent);">650 m²</strong></td>
          <td>Nội thất cao cấp</td>
          <td><strong style="color:#ea580c;">$52 / m²</strong></td>
          <td><span class="badge-filament success">Đang trống</span></td>
          <td>
            <button class="btn-mo-orange" style="padding:4px 10px;font-size:12px;" type="button"
                    onclick="bookSpecificFloor('{{ addslashes($building?->name ?? 'Tòa nhà') }}', 'Tầng 18', '650 m²')">
              Đăng ký xem sàn này
            </button>
          </td>
        </tr>
      @endforelse
    </tbody>
  </table>
</div>
