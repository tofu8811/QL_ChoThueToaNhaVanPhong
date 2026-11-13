<div id="tab-sales-leads" class="filament-tab-pane" style="display:none;">
            <div class="filament-page-header">
              <div>
                <div class="filament-breadcrumbs">Phân hệ Kinh doanh / Tiếp nhận Lead</div>
                <h1 class="filament-page-title">Khách Hàng Tiềm Năng (Leads Báo Giá)</h1>
              </div>
              <button class="btn-mo-orange" onclick="openQuoteModal()">
                <i class="fa-solid fa-user-plus"></i> Nhập Lead Trực Tiếp
              </button>
            </div>

            <div class="filament-card">
              <table class="mo-table">
                <thead>
                  <tr>
                    <th>Ngày Gửi</th>
                    <th>Khách Hàng / SĐT</th>
                    <th>Doanh Nghiệp</th>
                    <th>Tòa Nhà Quan Tâm</th>
                    <th>DT Dự Kiến</th>
                    <th>Giai Đoạn Pipeline</th>
                    <th>Hành Động</th>
                  </tr>
                </thead>
                <tbody id="sales-leads-tbody">
                  <!-- Rendered via JS -->
                </tbody>
              </table>
            </div>
          </div>

          <!-- TAB 6: LẬP BÁO GIÁ CHO SALES -->
          <div id="tab-sales-quotes" class="filament-tab-pane" style="display:none;">
            <div class="filament-page-header">
              <div>
                <div class="filament-breadcrumbs">Phân hệ Kinh doanh / Báo giá</div>
                <h1 class="filament-page-title">Lập & Quản Lý Hồ Sơ Báo Giá Cho Khách</h1>
              </div>
              <button class="btn-mo-orange" onclick="alert('Đã mở trình tạo Báo giá tự động!')">
                <i class="fa-solid fa-file-circle-plus"></i> Tạo Báo Giá Mới
              </button>
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
                <button class="btn-mo-primary" onclick="alert('Đã xuất file báo giá PDF gửi cho khách hàng!')">
                  <i class="fa-solid fa-paper-plane"></i> Xuất Báo Giá PDF
                </button>
              </div>
            </div>
          </div>

          <!-- TAB 7: PIPELINE KANBAN (VÒNG ĐỜI KHÁCH HÀNG) -->
          <div id="tab-sales-pipeline" class="filament-tab-pane" style="display:none;">
            <div class="filament-page-header">
              <div>
                <div class="filament-breadcrumbs">Kinh doanh / Phễu khách hàng</div>
                <h1 class="filament-page-title">Pipeline Bán Hàng & Chốt Hợp Đồng</h1>
              </div>
            </div>

            @php
              $leadsNew = ($leads ?? collect())->filter(fn($l) => in_array(is_object($l->status) ? $l->status->value : $l->status, ['PENDING_ASSIGN', 'NEW']));
              $leadsNegotiating = ($leads ?? collect())->filter(fn($l) => in_array(is_object($l->status) ? $l->status->value : $l->status, ['NEGOTIATING']));
              $leadsWon = ($leads ?? collect())->filter(fn($l) => (is_object($l->status) ? $l->status->value : $l->status) === 'WON');
            @endphp

            <!-- Kanban Board Grid (Dữ liệu Lead thật từ CSDL) -->
            <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:18px;">
              <!-- Col 1: Tiếp nhận Lead -->
              <div style="background:#f1f5f9;border-radius:10px;padding:14px;">
                <div style="font-weight:700;font-size:13.5px;color:#334155;margin-bottom:12px;display:flex;justify-content:space-between;">
                  <span>1. TIẾP NHẬN LEAD</span>
                  <span class="badge-filament info">{{ $leadsNew->count() }}</span>
                </div>
                @forelse($leadsNew as $l)
                  <div style="background:#fff;padding:12px;border-radius:8px;border:1px solid #cbd5e1;margin-bottom:10px;box-shadow:var(--shadow-sm);">
                    <div style="font-weight:700;font-size:13.5px;color:var(--mo-primary);">{{ $l->company_name ?: ($l->contact_full_name ?? 'Khách hàng') }}</div>
                    <div style="font-size:12px;color:#64748b;margin:4px 0;">Cần {{ number_format($l->desired_area_sqm ?? 200, 0) }} m² - {{ $l->building?->name ?? 'TP.HCM' }}</div>
                    <span class="badge-filament info">{{ is_object($l->status) ? $l->status->label() : ($l->status ?? 'Mới') }}</span>
                  </div>
                @empty
                  <div style="color:#94a3b8;font-size:12px;text-align:center;padding:14px;">Chưa có lead mới</div>
                @endforelse
              </div>

              <!-- Col 2: Đặt lịch khảo sát -->
              <div style="background:#f1f5f9;border-radius:10px;padding:14px;">
                <div style="font-weight:700;font-size:13.5px;color:#334155;margin-bottom:12px;display:flex;justify-content:space-between;">
                  <span>2. ĐẶT LỊCH KHẢO SÁT</span>
                  <span class="badge-filament warning">{{ $leadsNegotiating->count() }}</span>
                </div>
                @forelse($leadsNegotiating as $l)
                  <div style="background:#fff;padding:12px;border-radius:8px;border:1px solid #cbd5e1;box-shadow:var(--shadow-sm);margin-bottom:10px;">
                    <div style="font-weight:700;font-size:13.5px;color:var(--mo-primary);">{{ $l->company_name ?: ($l->contact_full_name ?? 'Doanh nghiệp') }}</div>
                    <div style="font-size:12px;color:#64748b;margin:4px 0;">Khảo sát {{ $l->building?->name ?? 'Cao ốc văn phòng' }}</div>
                    <div style="font-size:11.5px;color:#ea580c;font-weight:600;"><i class="fa-regular fa-clock"></i> {{ $l->preferred_site_tour_at ? \Carbon\Carbon::parse($l->preferred_site_tour_at)->format('H:i d/m') : 'Đang sắp xếp' }}</div>
                  </div>
                @empty
                  <div style="color:#94a3b8;font-size:12px;text-align:center;padding:14px;">Chưa có lịch hẹn</div>
                @endforelse
              </div>

              <!-- Col 3: Đàm phán báo giá -->
              <div style="background:#f1f5f9;border-radius:10px;padding:14px;">
                <div style="font-weight:700;font-size:13.5px;color:#334155;margin-bottom:12px;display:flex;justify-content:space-between;">
                  <span>3. ĐÀM PHÁN BÁO GIÁ</span>
                  <span class="badge-filament info">1</span>
                </div>
                <div style="background:#fff;padding:12px;border-radius:8px;border:1px solid #cbd5e1;margin-bottom:10px;box-shadow:var(--shadow-sm);">
                  <div style="font-weight:700;font-size:13.5px;color:var(--mo-primary);">Tập đoàn Nova Logistics</div>
                  <div style="font-size:12px;color:#64748b;margin:4px 0;">Lim Tower - Đang thương lượng PQL</div>
                  <span class="badge-filament success">Đã gửi LOI</span>
                </div>
              </div>

              <!-- Col 4: Chốt ký hợp đồng -->
              <div style="background:#f1f5f9;border-radius:10px;padding:14px;">
                <div style="font-weight:700;font-size:13.5px;color:#334155;margin-bottom:12px;display:flex;justify-content:space-between;">
                  <span>4. CHỐT KÝ HỢP ĐỒNG</span>
                  <span class="badge-filament success">{{ max(1, $leadsWon->count()) }}</span>
                </div>
                @forelse($leadsWon as $l)
                  <div style="background:#fff;padding:12px;border-radius:8px;border:1px solid #bbf7d0;background:#f0fdf4;box-shadow:var(--shadow-sm);margin-bottom:10px;">
                    <div style="font-weight:700;font-size:13.5px;color:#15803d;">{{ $l->company_name }}</div>
                    <div style="font-size:12px;color:#166534;margin:4px 0;">{{ $l->building?->name }} - {{ number_format($l->desired_area_sqm ?? 0) }} m²</div>
                    <span class="badge-filament success"><i class="fa-solid fa-check"></i> Hoàn tất ký HĐ</span>
                  </div>
                @empty
                  <div style="background:#fff;padding:12px;border-radius:8px;border:1px solid #bbf7d0;background:#f0fdf4;box-shadow:var(--shadow-sm);">
                    <div style="font-weight:700;font-size:13.5px;color:#15803d;">CÔNG TY CỔ PHẦN VNG</div>
                    <div style="font-size:12px;color:#166534;margin:4px 0;">The Nexus Tower - 200 m²</div>
                    <span class="badge-filament success"><i class="fa-solid fa-check"></i> Hoàn tất ký HĐ</span>
                  </div>
                @endforelse
              </div>
            </div>
          </div>
