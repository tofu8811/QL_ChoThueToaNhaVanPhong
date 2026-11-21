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
        <th>Đơn giá thuê (VNĐ/m²)</th>
        <th>Tình trạng lấp đầy</th>
        <th>Thao tác</th>
      </tr>
    </thead>
    <tbody id="detail-floors-tbody">
      @forelse($units as $unit)
        @php
          $priceVnd = (float) ($unit->base_rental_price ?: 0);
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
          <td><strong style="color:#ea580c;">{{ number_format($priceVnd, 0, ',', '.') }} đ / m²</strong></td>
          <td><span class="badge-filament success">{{ $statusLabel }}</span></td>
          <td style="white-space:nowrap;">
            <button wire:click="viewUnitDetail('{{ $unit->id }}')" type="button" class="btn-mo-outline" style="padding:4px 10px;font-size:12px;margin-right:6px;color:var(--mo-primary);border-color:#cbd5e1;">
              <i class="fa-solid fa-circle-info"></i> Xem Chi Tiết
            </button>
            <a href="{{ route('quote.page', ['building_id' => $building?->id, 'unit_id' => $unit->id]) }}" class="btn-mo-orange" style="padding:4px 10px;font-size:12px;text-decoration:none;display:inline-block;">
              Đăng ký xem sàn này
            </a>
          </td>
        </tr>
      @empty
        <tr>
          <td><strong>Tầng 5</strong></td>
          <td><strong style="color:var(--mo-blue-accent);">180 m²</strong></td>
          <td>Trần sàn thô</td>
          <td><strong style="color:#ea580c;">650.000 đ / m²</strong></td>
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

  <!-- MODAL XEM CHI TIẾT & SƠ ĐỒ MẶT BẰNG (PORTAL DETAIL) -->
  @if($showUnitModal && $selectedUnit)
    <div style="position:fixed;inset:0;background:rgba(15,23,42,0.6);backdrop-filter:blur(3px);display:flex;align-items:center;justify-content:center;z-index:9999;padding:16px;">
      <div style="background:#ffffff;border-radius:12px;width:100%;max-width:720px;box-shadow:0 20px 25px -5px rgba(0,0,0,0.15);overflow:hidden;border:1px solid #e2e8f0;max-height:92vh;display:flex;flex-direction:column;">
        <div style="background:var(--mo-primary);color:#ffffff;padding:16px 20px;display:flex;justify-content:space-between;align-items:center;">
          <h3 style="margin:0;font-size:16px;font-weight:700;">
            <i class="fa-solid fa-layer-group"></i> Chi Tiết Mặt Bằng: {{ $selectedUnit->unit_code }} (Tầng {{ $selectedUnit->floor_number }})
          </h3>
          <button wire:click="closeUnitModal" type="button" style="background:none;border:none;color:#ffffff;font-size:18px;cursor:pointer;">&times;</button>
        </div>
        <div style="padding:20px;overflow-y:auto;flex:1;">
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:18px;">
            <div style="background:#f8fafc;padding:12px 16px;border-radius:8px;border:1px solid #e2e8f0;">
              <div style="font-size:12px;color:#64748b;">Tòa nhà:</div>
              <div style="font-size:14px;font-weight:700;color:var(--mo-primary);">{{ $selectedUnit->building?->name ?? 'Tòa nhà văn phòng' }}</div>
              <div style="font-size:12px;color:#64748b;margin-top:8px;">Diện tích thông thủy:</div>
              <div style="font-size:16px;font-weight:700;color:#0284c7;">{{ number_format($selectedUnit->area_sqm, 1) }} m²</div>
            </div>
            <div style="background:#f8fafc;padding:12px 16px;border-radius:8px;border:1px solid #e2e8f0;">
              <div style="font-size:12px;color:#64748b;">Đơn giá chào thuê:</div>
              <div style="font-size:16px;font-weight:700;color:#ea580c;">{{ number_format($selectedUnit->base_rental_price ?: 0, 0, ',', '.') }} đ / m²</div>
              <div style="font-size:12px;color:#64748b;margin-top:8px;">Hiện trạng bàn giao:</div>
              <div style="font-size:13px;font-weight:600;color:#1e293b;">
                @if($selectedUnit->handover_condition === 'RAW') Trần sàn thô
                @elseif($selectedUnit->handover_condition === 'BASIC') Hoàn thiện cơ bản
                @elseif($selectedUnit->handover_condition === 'FULL_FURNISHED') Đầy đủ nội thất cao cấp
                @else {{ $selectedUnit->handover_condition ?: 'Hoàn thiện tiêu chuẩn' }}
                @endif
              </div>
            </div>
          </div>

          <!-- Album ảnh & Sơ đồ mặt bằng -->
          <div style="margin-bottom:16px;">
            <div style="font-size:13px;font-weight:700;color:#334155;margin-bottom:8px;">
              <i class="fa-solid fa-images"></i> Album Ảnh Thực Tế & Sơ Đồ Thiết Kế Mặt Bằng:
            </div>
            @if($selectedUnit->images && $selectedUnit->images->count() > 0)
              <div style="display:grid;grid-template-columns:repeat(3, 1fr);gap:10px;">
                @foreach($selectedUnit->images as $img)
                  <div style="border-radius:8px;overflow:hidden;border:1px solid #e2e8f0;background:#f8fafc;">
                    <img src="{{ $img->image_url }}" alt="Ảnh mặt bằng {{ $selectedUnit->unit_code }}" style="width:100%;height:130px;object-fit:cover;display:block;">
                  </div>
                @endforeach
              </div>
            @elseif($selectedUnit->floor_plan_image_url)
              <div style="border-radius:8px;overflow:hidden;border:1px solid #e2e8f0;background:#f8fafc;text-align:center;">
                <img src="{{ $selectedUnit->floor_plan_image_url }}" alt="Sơ đồ layout {{ $selectedUnit->unit_code }}" style="max-height:220px;object-fit:contain;margin:0 auto;display:block;">
              </div>
            @else
              <div style="background:#f8fafc;border:1px dashed #cbd5e1;border-radius:8px;padding:24px;text-align:center;color:#94a3b8;font-size:13px;">
                <i class="fa-solid fa-image" style="font-size:24px;margin-bottom:6px;display:block;"></i>
                Chưa có ảnh thực tế hoặc sơ đồ 2D cho mặt bằng này.
              </div>
            @endif
          </div>

          <div style="display:flex;justify-content:flex-end;gap:10px;border-top:1px solid #f1f5f9;padding-top:14px;">
            <button wire:click="closeUnitModal" type="button" class="btn-mo-outline" style="padding:6px 16px;">Đóng</button>
            <a href="{{ route('quote.page', ['building_id' => $selectedUnit->building_id, 'unit_id' => $selectedUnit->id]) }}" class="btn-mo-orange" style="padding:6px 18px;text-decoration:none;display:inline-flex;align-items:center;gap:6px;">
              <i class="fa-solid fa-calendar-check"></i> Đăng Ký Khảo Sát Sàn Này
            </a>
          </div>
        </div>
      </div>
    </div>
  @endif
</div>
