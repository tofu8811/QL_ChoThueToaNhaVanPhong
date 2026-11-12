<!-- TAB 8: TENANT WORKSPACE (Không gian làm việc cho doanh nghiệp thuê) -->
          <div id="tab-tenant-workspace" class="filament-tab-pane" style="display:none;">
            <div class="filament-page-header">
              <div>
                <div class="filament-breadcrumbs">Cổng khách thuê / Tổng quan văn phòng</div>
                <h1 class="filament-page-title" id="tenant-welcome-title">Chào Mừng, {{ $sampleTenant->company_name ?? 'CÔNG TY CỔ PHẦN VNG' }}</h1>
              </div>
              <span class="badge-filament success" style="font-size:13px;padding:6px 12px;"><i class="fa-solid fa-circle-check"></i> Hợp Đồng Đang Có Hiệu Lực</span>
            </div>

            <!-- Tenant Quick Stats (Dữ liệu thực tế từ Hợp đồng & CSDL) -->
            <div class="filament-widgets-grid">
              <div class="filament-stat-card">
                <div class="filament-stat-label">Vị trí mặt bằng</div>
                <div class="filament-stat-value" id="tenant-stat-floor" style="font-size:20px;">Tầng 12</div>
                <div style="font-size:12.5px;color:#64748b;" id="tenant-stat-building"><i class="fa-solid fa-building"></i> {{ $sampleContract?->building?->name ?? 'Tòa Nhà The Nexus Tower' }}</div>
              </div>
              <div class="filament-stat-card">
                <div class="filament-stat-label">Diện tích sàn đang thuê</div>
                <div class="filament-stat-value" id="tenant-stat-area">{{ number_format($sampleContract?->total_leased_area_sqm ?? 200, 0) }} <span style="font-size:16px;color:#64748b;">m²</span></div>
                <div style="font-size:12.5px;color:#10b981;"><i class="fa-solid fa-users"></i> Đang hoạt động</div>
              </div>
              <div class="filament-stat-card">
                <div class="filament-stat-label">Thời hạn hợp đồng còn lại</div>
                <div class="filament-stat-value" id="tenant-stat-term">24 <span style="font-size:16px;color:#64748b;">tháng</span></div>
                <div style="font-size:12.5px;color:#64748b;" id="tenant-stat-end">Hết hạn: {{ $sampleContract?->end_date ? \Carbon\Carbon::parse($sampleContract->end_date)->format('d/m/Y') : '31/12/2027' }}</div>
              </div>
              <div class="filament-stat-card">
                <div class="filament-stat-label">Cước phí kỳ này (T11/2026)</div>
                <div class="filament-stat-value" id="tenant-stat-bill" style="color:#ea580c;font-size:22px;">{{ isset($sampleDebit) ? number_format($sampleDebit->total_amount / 1000000, 2) . ' Tr' : '442,67 Tr' }}</div>
                <div style="font-size:12.5px;color:#eab308;"><i class="fa-solid fa-clock"></i> Hạn thanh toán: 15/11/2026</div>
              </div>
            </div>

            <div style="display:grid;grid-template-columns:1.2fr 1fr;gap:24px;">
              <!-- Current Contract Box -->
              <div class="filament-card">
                <div class="filament-card-header">
                  <h3 class="filament-card-title"><i class="fa-solid fa-file-contract"></i> Thông Tin Hợp Đồng Hiện Tại</h3>
                  <button class="btn-mo-outline" style="font-size:12px;padding:4px 8px;" onclick="alert('Đã tải file PDF Hợp đồng thuê đầy đủ!')">
                    <i class="fa-solid fa-download"></i> Tải File HĐ
                  </button>
                </div>
                <div style="padding:18px;">
                  <table class="detail-specs-table" style="margin:0;">
                    <tr>
                      <td>Mã Hợp Đồng:</td>
                      <td><strong id="tenant-contract-code">{{ $sampleContract?->contract_number ?? 'HD-2026/01-NEXUS' }}</strong></td>
                    </tr>
                    <tr>
                      <td>Đại diện ký:</td>
                      <td id="tenant-contract-rep">{{ $sampleTenant?->representative_name ?? 'Lê Hồng Minh' }} (Chủ tịch HĐQT)</td>
                    </tr>
                    <tr>
                      <td>Thời hạn thuê:</td>
                      <td id="tenant-contract-dates">36 Tháng ({{ $sampleContract?->start_date ? \Carbon\Carbon::parse($sampleContract->start_date)->format('d/m/Y') : '01/01/2026' }} đến {{ $sampleContract?->end_date ? \Carbon\Carbon::parse($sampleContract->end_date)->format('d/m/Y') : '31/12/2028' }})</td>
                    </tr>
                    <tr>
                      <td>Tiền cọc thuê (3 tháng):</td>
                      <td><strong id="tenant-contract-deposit" style="color:#0f3361;">{{ number_format($sampleContract?->deposit_amount ?? 1020000000, 0, ',', '.') }} VNĐ</strong> (Đã ký quỹ)</td>
                    </tr>
                    <tr>
                      <td>Định kỳ thanh toán:</td>
                      <td id="tenant-contract-cycle">Hàng tháng (Thanh toán vào ngày 05 đầu kỳ)</td>
                    </tr>
                  </table>
                </div>
              </div>

              <!-- Quick Ticket Actions -->
              <div class="filament-card">
                <div class="filament-card-header">
                  <h3 class="filament-card-title"><i class="fa-solid fa-headset"></i> Yêu Cầu Hỗ Trợ Gần Đây</h3>
                  <button class="btn-mo-orange" style="font-size:12px;padding:5px 10px;" onclick="switchFilamentTab('tab-tenant-tickets', document.querySelectorAll('#nav-group-tenant .filament-link')[1])">
                    + Tạo Ticket Mới
                  </button>
                </div>
                <div style="padding:16px;">
                  <div style="padding:10px 0;border-bottom:1px solid #f1f5f9;">
                    <div style="display:flex;justify-content:space-between;">
                      <strong>Bảo trì dàn lạnh âm trần phòng họp A</strong>
                      <span class="badge-filament warning">Đang xử lý</span>
                    </div>
                    <div style="font-size:12px;color:#64748b;margin-top:2px;">BQL đã cử kỹ thuật viên kiểm tra lúc 10:30 sáng nay</div>
                  </div>
                  <div style="padding:10px 0;">
                    <div style="display:flex;justify-content:space-between;">
                      <strong>Đăng ký làm thêm ngoài giờ (Tối Thứ Bảy)</strong>
                      <span class="badge-filament success">Đã phê duyệt</span>
                    </div>
                    <div style="font-size:12px;color:#64748b;margin-top:2px;">Kỹ thuật tòa nhà đã kích hoạt hệ thống điện & thang máy</div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- TAB 9: TENANT TICKET MANAGEMENT (Module 4) -->
          <div id="tab-tenant-tickets" class="filament-tab-pane" style="display:none;">
            <div class="filament-page-header">
              <div>
                <div class="filament-breadcrumbs">Cổng khách thuê / Hỗ trợ vận hành (Module 4)</div>
                <h1 class="filament-page-title">Gửi Yêu Cầu Hỗ Trợ & Đăng Ký Thi Công</h1>
              </div>
              <div style="display:flex;gap:8px;">
                <button class="btn-mo-outline" onclick="loadTicketsList('tenant')">
                  <i class="fa-solid fa-arrows-rotate"></i> Tải Lại
                </button>
                <button class="btn-mo-orange" onclick="openTenantTicketModal()">
                  <i class="fa-solid fa-plus"></i> Tạo Ticket Mới
                </button>
              </div>
            </div>

            <div class="filament-card">
              <table class="mo-table">
                <thead>
                  <tr>
                    <th>Mã Ticket</th>
                    <th>Loại Yêu Cầu</th>
                    <th>Tiêu Đề / Mô Tả</th>
                    <th>Thời Gian Tạo</th>
                    <th>Mức Ưu Tiên</th>
                    <th>Trạng Thái Xử Lý</th>
                    <th style="text-align:right;">Thao Tác</th>
                  </tr>
                </thead>
                <tbody id="tenant-tickets-tbody">
                  <!-- Rendered via JS -->
                </tbody>
              </table>
            </div>
          </div>

          <!-- TAB 10: TENANT BILLS & PAYMENTS -->
          <div id="tab-tenant-bills" class="filament-tab-pane" style="display:none;">
            <div class="filament-page-header">
              <div>
                <div class="filament-breadcrumbs">Cổng khách thuê / Tài chính</div>
                <h1 class="filament-page-title">Thông Báo Phí & Lịch Sử Công Nợ</h1>
              </div>
              <button class="btn-mo-primary" onclick="alert('Đã tải toàn bộ bảng sao kê hóa đơn điện tử VAT!')">
                <i class="fa-solid fa-file-invoice"></i> Xuất Bảng Kê VAT
              </button>
            </div>

            <div class="filament-card">
              <table class="mo-table">
                <thead>
                  <tr>
                    <th>Kỳ Cước</th>
                    <th>Khoản Mục Phí</th>
                    <th>Số Tiền (VNĐ)</th>
                    <th>Hạn Nộp</th>
                    <th>Trạng Thái</th>
                    <th>Thao Tác</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($recentDebitNotes ?? [] as $dn)
                    <tr>
                      <td><strong>Tháng {{ $dn->billing_month }}/{{ $dn->billing_year }}</strong></td>
                      <td>Cước thuê tòa nhà {{ $dn->building?->name ?? 'văn phòng' }}</td>
                      <td><strong style="color:#ea580c;">{{ number_format($dn->total_amount ?? 0, 0, ',', '.') }} đ</strong></td>
                      <td>{{ $dn->due_date ? \Carbon\Carbon::parse($dn->due_date)->format('d/m/Y') : '-' }}</td>
                      <td>
                        @php
                          $dnStatus = is_object($dn->status) ? ($dn->status->value ?? 'PENDING') : ($dn->status ?? 'PENDING');
                          $bClass = ($dnStatus === 'PAID') ? 'success' : (($dnStatus === 'OVERDUE') ? 'danger' : 'warning');
                        @endphp
                        <span class="badge-filament {{ $bClass }}">{{ $dnStatus === 'PAID' ? 'Đã thanh toán' : 'Chờ thanh toán' }}</span>
                      </td>
                      <td>
                        <button class="btn-mo-orange" style="padding:4px 10px;font-size:12px;" onclick="alert('Mã phiếu cước: {{ $dn->debit_note_code }}\nTổng tiền: {{ number_format($dn->total_amount, 0, ',', '.') }} VNĐ')">
                          <i class="fa-solid fa-qrcode"></i> Chi Tiết Cước
                        </button>
                      </td>
                    </tr>
                  @empty
                    <tr>
                      <td colspan="6" style="text-align:center;padding:24px;color:#94a3b8;">
                        <i class="fa-solid fa-file-invoice" style="font-size:28px;margin-bottom:8px;display:block;"></i>
                        Chưa có thông báo cước nào phát hành.
                      </td>
                    </tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>
