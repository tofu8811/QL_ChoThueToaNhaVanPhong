<!-- TAB 4: HỢP ĐỒNG THUÊ -->
          <div id="tab-admin-contracts" class="filament-tab-pane" style="display:none;">
            <div class="filament-page-header">
              <div>
                <div class="filament-breadcrumbs">Ban quản lý / Pháp lý & Hợp đồng</div>
                <h1 class="filament-page-title">Danh Sách Hợp Đồng Thuê Văn Phòng</h1>
              </div>
              <button class="btn-mo-orange" onclick="alert('Mở form tạo hợp đồng mới!')">
                <i class="fa-solid fa-plus"></i> Tạo Hợp Đồng Mới
              </button>
            </div>

            <div class="filament-card">
              <table class="mo-table">
                <thead>
                  <tr>
                    <th>Số HĐ</th>
                    <th>Khách Hàng (Bên Thuê)</th>
                    <th>Tòa Nhà & Tầng</th>
                    <th>Diện Tích</th>
                    <th>Giá Trị / Tháng</th>
                    <th>Thời Hạn Thuê</th>
                    <th>Trạng Thái</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($recentContracts ?? [] as $c)
                    <tr>
                      <td><strong>{{ $c->contract_number }}</strong></td>
                      <td>{{ $c->tenant?->company_name ?? 'Khách thuê doanh nghiệp' }}</td>
                      <td>{{ $c->building?->name ?? 'Tòa nhà văn phòng' }}</td>
                      <td>{{ number_format($c->total_leased_area_sqm ?? 0, 0, ',', '.') }} m²</td>
                      <td><strong>{{ number_format($c->deposit_amount ?? 0, 0, ',', '.') }} đ</strong></td>
                      <td>{{ $c->start_date ? \Carbon\Carbon::parse($c->start_date)->format('d/m/Y') : '-' }} - {{ $c->end_date ? \Carbon\Carbon::parse($c->end_date)->format('d/m/Y') : '-' }}</td>
                      <td>
                        @php
                          $stVal = is_object($c->status) ? ($c->status->value ?? 'ACTIVE') : $c->status;
                          $badgeClass = ($stVal === 'ACTIVE' || $stVal === 'SIGNED') ? 'success' : (($stVal === 'EXPIRING_SOON') ? 'warning' : 'info');
                        @endphp
                        <span class="badge-filament {{ $badgeClass }}">{{ $stVal }}</span>
                      </td>
                    </tr>
                  @empty
                    <tr>
                      <td colspan="7" style="text-align:center;padding:24px;color:#94a3b8;">
                        <i class="fa-solid fa-file-circle-xmark" style="font-size:28px;margin-bottom:8px;display:block;"></i>
                        Chưa có hợp đồng nào trong hệ thống.
                      </td>
                    </tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>
