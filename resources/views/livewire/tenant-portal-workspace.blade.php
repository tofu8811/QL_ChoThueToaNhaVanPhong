<div>
    @if(isset($rentedBuildings) && $rentedBuildings->isNotEmpty())
    <script>
      window.TENANT_RENTED_BUILDINGS = [
        @foreach($rentedBuildings as $bld)
          { id: "{{ $bld->id }}", name: "{{ $bld->name }}", district: "{{ $bld->district ?? 'TP.HCM' }}" },
        @endforeach
      ];
      if (typeof updateTenantBuildingDropdowns === 'function') {
        updateTenantBuildingDropdowns();
      }
    </script>
    @endif

    <!-- Tenant Portal Sub-Navigation Tabs -->
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;border-bottom:1px solid #e2e8f0;padding-bottom:12px;flex-wrap:wrap;gap:12px;">
        <div style="display:flex;gap:8px;">
            <button wire:click="switchTab('workspace')" 
                style="cursor:pointer;padding:8px 16px;border-radius:6px;font-size:14px;font-weight:600;display:inline-flex;align-items:center;gap:6px;border:none;
                background: {{ $activeTab === 'workspace' ? 'var(--mo-primary)' : '#f1f5f9' }};
                color: {{ $activeTab === 'workspace' ? '#fff' : '#475569' }};">
                <i class="fa-solid fa-building-user"></i> Văn Phòng Của Tôi
            </button>
            <button wire:click="switchTab('bills')" 
                style="cursor:pointer;padding:8px 16px;border-radius:6px;font-size:14px;font-weight:600;display:inline-flex;align-items:center;gap:6px;border:none;
                background: {{ $activeTab === 'bills' ? 'var(--mo-primary)' : '#f1f5f9' }};
                color: {{ $activeTab === 'bills' ? '#fff' : '#475569' }};">
                <i class="fa-solid fa-file-invoice-dollar"></i> Hóa Đơn & Đóng Phí
                @php
                    $pendingCount = $recentDebitNotes->filter(function($item) {
                        $st = is_object($item->status) ? ($item->status->value ?? 'ISSUED') : $item->status;
                        return in_array($st, ['ISSUED', 'PENDING', 'OVERDUE']);
                    })->count();
                @endphp
                @if($pendingCount > 0)
                    <span style="background:#ef4444;color:#fff;border-radius:9999px;font-size:11px;padding:2px 6px;">
                        {{ $pendingCount }}
                    </span>
                @endif
            </button>
            <button wire:click="switchTab('tickets')" 
                style="cursor:pointer;padding:8px 16px;border-radius:6px;font-size:14px;font-weight:600;display:inline-flex;align-items:center;gap:6px;border:none;
                background: {{ $activeTab === 'tickets' ? 'var(--mo-primary)' : '#f1f5f9' }};
                color: {{ $activeTab === 'tickets' ? '#fff' : '#475569' }};">
                <i class="fa-solid fa-screwdriver-wrench"></i> Yêu Cầu Hỗ Trợ & Thi Công
                @if($openTicketsCount > 0)
                    <span style="background:#7e22ce;color:#fff;border-radius:9999px;font-size:11px;padding:2px 7px;font-weight:700;">
                        {{ $openTicketsCount }}
                    </span>
                @endif
            </button>
        </div>

        <div>
            <span style="font-size:13px;color:#64748b;">
                Tài khoản: <strong style="color:#0f172a;">{{ $tenant?->company_name ?? 'Khách thuê Doanh nghiệp' }}</strong>
            </span>
        </div>
    </div>

    @if(!$tenant)
        <div class="filament-card" style="padding:40px;text-align:center;color:#64748b;">
            <i class="fa-solid fa-building-circle-exclamation" style="font-size:40px;margin-bottom:12px;color:#94a3b8;display:block;"></i>
            Chưa có thông tin doanh nghiệp thuê được liên kết. Vui lòng đăng nhập vào Cổng Khách Thuê.
        </div>
    @else
        <!-- TAB 1: VĂN PHÒNG CỦA TÔI -->
        @if($activeTab === 'workspace')
            <div class="filament-page-header" style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;">
                <div>
                    <div class="filament-breadcrumbs" style="font-size:12px;color:#64748b;">Cổng khách thuê / Tổng quan văn phòng</div>
                    <h1 class="filament-page-title" style="font-size:22px;font-weight:700;color:var(--mo-primary);margin:0;">
                        Chào Mừng, {{ $tenant->company_name }}
                    </h1>
                </div>
                @if($activeContract)
                    <span class="badge-filament success" style="font-size:13px;padding:6px 12px;">
                        <i class="fa-solid fa-circle-check"></i> Hợp Đồng Đang Có Hiệu Lực
                    </span>
                @else
                    <span class="badge-filament warning" style="font-size:13px;padding:6px 12px;">
                        Chưa có hợp đồng hiệu lực
                    </span>
                @endif
            </div>

            <!-- Tenant Quick Stats -->
            <div class="filament-widgets-grid" style="display:grid;grid-template-columns:repeat(auto-fit, minmax(220px, 1fr));gap:16px;margin-bottom:24px;">
                <!-- Stat 1: Vị trí -->
                <div class="filament-stat-card" style="background:#fff;padding:18px;border-radius:8px;border:1px solid #e2e8f0;box-shadow:0 1px 3px rgba(0,0,0,0.05);">
                    <div class="filament-stat-label" style="font-size:12.5px;color:#64748b;margin-bottom:4px;">Vị trí mặt bằng</div>
                    <div class="filament-stat-value" style="font-size:20px;font-weight:700;color:#0f172a;margin-bottom:4px;">
                        @php
                            $unitCodes = $activeContract?->contractUnits->map(fn($cu) => $cu->unit?->unit_code)->filter()->toArray() ?? [];
                        @endphp
                        {{ !empty($unitCodes) ? implode(', ', $unitCodes) : 'Tầng văn phòng' }}
                    </div>
                    <div style="font-size:12.5px;color:#64748b;">
                        <i class="fa-solid fa-building" style="color:var(--mo-primary);"></i> {{ $activeContract?->building?->name ?? 'Tòa nhà văn phòng' }}
                    </div>
                    @if($activeContract?->contractUnits && $activeContract->contractUnits->isNotEmpty())
                        <div style="display:flex;gap:6px;flex-wrap:wrap;margin-top:8px;">
                            @foreach($activeContract->contractUnits as $cu)
                                @if($cu->unit_id)
                                    <button wire:click="viewUnitDetail('{{ $cu->unit_id }}')" type="button" class="btn-mo-outline" style="padding:2px 8px;font-size:11.5px;color:#0284c7;border-color:#bae6fd;cursor:pointer;">
                                        <i class="fa-solid fa-map-location-dot"></i> Sơ đồ {{ $cu->unit?->unit_code }}
                                    </button>
                                @endif
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- Stat 2: Diện tích -->
                <div class="filament-stat-card" style="background:#fff;padding:18px;border-radius:8px;border:1px solid #e2e8f0;box-shadow:0 1px 3px rgba(0,0,0,0.05);">
                    <div class="filament-stat-label" style="font-size:12.5px;color:#64748b;margin-bottom:4px;">Diện tích sàn đang thuê</div>
                    <div class="filament-stat-value" style="font-size:20px;font-weight:700;color:#0f172a;margin-bottom:4px;">
                        {{ number_format($activeContract?->total_leased_area_sqm ?? 0, 0) }} <span style="font-size:15px;color:#64748b;">m²</span>
                    </div>
                    <div style="font-size:12.5px;color:#10b981;">
                        <i class="fa-solid fa-users"></i> Đang hoạt động
                    </div>
                </div>

                <!-- Stat 3: Thời hạn -->
                <div class="filament-stat-card" style="background:#fff;padding:18px;border-radius:8px;border:1px solid #e2e8f0;box-shadow:0 1px 3px rgba(0,0,0,0.05);">
                    <div class="filament-stat-label" style="font-size:12.5px;color:#64748b;margin-bottom:4px;">Thời hạn hợp đồng</div>
                    <div class="filament-stat-value" style="font-size:20px;font-weight:700;color:#0f172a;margin-bottom:4px;">
                        {{ $activeContract?->end_date ? max(0, (int) round(abs(\Carbon\Carbon::parse($activeContract->end_date)->diffInMonths(now())))) : 0 }} <span style="font-size:15px;color:#64748b;">tháng còn lại</span>
                    </div>
                    <div style="font-size:12.5px;color:#64748b;">
                        Hết hạn: {{ $activeContract?->end_date ? \Carbon\Carbon::parse($activeContract->end_date)->format('d/m/Y') : '-' }}
                    </div>
                </div>

                <!-- Stat 4: Cước phí gần nhất -->
                <div class="filament-stat-card" style="background:#fff;padding:18px;border-radius:8px;border:1px solid #e2e8f0;box-shadow:0 1px 3px rgba(0,0,0,0.05);">
                    <div class="filament-stat-label" style="font-size:12.5px;color:#64748b;margin-bottom:4px;">
                        Cước phí kỳ gần nhất (T{{ $latestDebitNote?->billing_month ?? now()->month }}/{{ $latestDebitNote?->billing_year ?? now()->year }})
                    </div>
                    <div class="filament-stat-value" style="color:#ea580c;font-size:20px;font-weight:700;margin-bottom:4px;">
                        {{ $latestDebitNote ? number_format($latestDebitNote->total_amount, 0, ',', '.') . ' đ' : 'Chưa có cước' }}
                    </div>
                    <div style="font-size:12.5px;color:{{ $latestDebitNote?->status === 'PAID' ? '#10b981' : '#eab308' }};">
                        <i class="fa-solid fa-clock"></i> {{ $latestDebitNote?->status === 'PAID' ? 'Đã thanh toán' : 'Hạn nộp: ' . ($latestDebitNote?->due_date ? \Carbon\Carbon::parse($latestDebitNote->due_date)->format('d/m/Y') : '15 hàng tháng') }}
                    </div>
                </div>

                <!-- Stat 5: Yêu cầu & Ticket hỗ trợ -->
                <div class="filament-stat-card" style="background:#fff;padding:18px;border-radius:8px;border:1px solid #e2e8f0;box-shadow:0 1px 3px rgba(0,0,0,0.05);cursor:pointer;" wire:click="switchTab('tickets')" title="Bấm để xem danh sách Ticket">
                    <div class="filament-stat-label" style="font-size:12.5px;color:#64748b;margin-bottom:4px;">Yêu cầu hỗ trợ kỹ thuật</div>
                    <div class="filament-stat-value" style="color:#7e22ce;font-size:20px;font-weight:700;margin-bottom:4px;">
                        {{ sprintf('%02d', $openTicketsCount) }} <span style="font-size:14px;color:#64748b;font-weight:500;">việc đang xử lý</span>
                    </div>
                    <div style="font-size:12px;color:#6d28d9;font-weight:600;">
                        <i class="fa-solid fa-headset"></i> Cam kết SLA: Phản hồi &lt; 30p
                    </div>
                </div>
            </div>

            <!-- Two Columns Section -->
            <div style="display:grid;grid-template-columns:1.3fr 1fr;gap:20px;">
                <!-- Current Contract Box -->
                <div class="filament-card" style="background:#fff;border-radius:8px;border:1px solid #e2e8f0;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,0.05);">
                    <div style="padding:14px 20px;border-bottom:1px solid #e2e8f0;display:flex;justify-content:space-between;align-items:center;">
                        <h3 style="margin:0;font-size:15px;font-weight:700;color:var(--mo-primary);">
                            <i class="fa-solid fa-file-contract"></i> Thông Tin Hợp Đồng Hiện Tại
                        </h3>
                        <button class="btn-mo-outline" style="font-size:12px;padding:4px 10px;" onclick="alert('Đang tải file PDF Hợp đồng thuê đầy đủ...')">
                            <i class="fa-solid fa-download"></i> Tải File HĐ
                        </button>
                    </div>
                    <div style="padding:18px 20px;">
                        <table style="width:100%;border-collapse:collapse;font-size:13.5px;">
                            <tr style="border-bottom:1px solid #f1f5f9;">
                                <td style="padding:10px 0;color:#64748b;width:180px;">Mã Hợp Đồng:</td>
                                <td style="padding:10px 0;"><strong style="color:var(--mo-primary);font-family:monospace;">{{ $activeContract?->contract_number ?? 'Chưa có' }}</strong></td>
                            </tr>
                            <tr style="border-bottom:1px solid #f1f5f9;">
                                <td style="padding:10px 0;color:#64748b;">Mặt Bằng Đang Thuê:</td>
                                <td style="padding:10px 0;">
                                    @if($activeContract?->contractUnits && $activeContract->contractUnits->isNotEmpty())
                                        <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
                                            @foreach($activeContract->contractUnits as $cu)
                                                <span style="font-weight:600;color:#1e293b;">{{ $cu->unit?->unit_code }} ({{ number_format($cu->leased_area_sqm, 0) }} m²)</span>
                                                @if($cu->unit_id)
                                                    <button wire:click="viewUnitDetail('{{ $cu->unit_id }}')" type="button" class="btn-mo-outline" style="padding:2px 8px;font-size:11px;color:#0284c7;border-color:#bae6fd;cursor:pointer;">
                                                        <i class="fa-solid fa-eye"></i> Xem sơ đồ & ảnh
                                                    </button>
                                                @endif
                                            @endforeach
                                        </div>
                                    @else
                                        <span>-</span>
                                    @endif
                                </td>
                            </tr>
                            <tr style="border-bottom:1px solid #f1f5f9;">
                                <td style="padding:10px 0;color:#64748b;">Đại diện ký kết:</td>
                                <td style="padding:10px 0;">{{ $tenant->representative_name }} ({{ $tenant->representative_position }})</td>
                            </tr>
                            <tr style="border-bottom:1px solid #f1f5f9;">
                                <td style="padding:10px 0;color:#64748b;">Thời hạn thuê:</td>
                                <td style="padding:10px 0;">
                                    {{ $activeContract?->start_date ? \Carbon\Carbon::parse($activeContract->start_date)->format('d/m/Y') : '-' }} đến {{ $activeContract?->end_date ? \Carbon\Carbon::parse($activeContract->end_date)->format('d/m/Y') : '-' }}
                                </td>
                            </tr>
                            <tr style="border-bottom:1px solid #f1f5f9;">
                                <td style="padding:10px 0;color:#64748b;">Tiền cọc thuê (Ký quỹ):</td>
                                <td style="padding:10px 0;">
                                    <strong style="color:#0f3361;">{{ number_format($activeContract?->deposit_amount ?? 0, 0, ',', '.') }} VNĐ</strong> (Đã ký quỹ bảo đảm)
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:10px 0;color:#64748b;">Kỳ hạn thanh toán:</td>
                                <td style="padding:10px 0;">{{ $activeContract?->payment_cycle_months ?? 1 }} tháng / lần (Hạn đóng cước ngày {{ $activeContract?->payment_due_day ?? 5 }} hàng tháng)</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Support Ticket & Notice Card -->
                <div class="filament-card" style="background:#fff;border-radius:8px;border:1px solid #e2e8f0;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,0.05);">
                    <div style="padding:14px 20px;border-bottom:1px solid #e2e8f0;display:flex;justify-content:space-between;align-items:center;">
                        <h3 style="margin:0;font-size:15px;font-weight:700;color:var(--mo-primary);">
                            <i class="fa-solid fa-headset"></i> Kênh Hỗ Trợ Ban Quản Lý
                        </h3>
                    </div>
                    <div style="padding:18px 20px;font-size:13.5px;">
                        <div style="background:#f8fafc;padding:12px;border-radius:6px;border:1px solid #e2e8f0;margin-bottom:14px;">
                            <div style="font-weight:600;color:#1e293b;"><i class="fa-solid fa-phone" style="color:#10b981;"></i> Hotline Kỹ Thuật 24/7:</div>
                            <div style="font-size:16px;font-weight:700;color:#0f3361;margin-top:4px;">1900 6868 - Nhánh 2 (BQL Tòa Nhà)</div>
                        </div>

                        <div style="color:#475569;line-height:1.6;">
                            <div style="font-weight:600;margin-bottom:6px;">Quy định vận hành & đăng ký:</div>
                            <ul style="margin:0;padding-left:18px;color:#64748b;">
                                <li>Thi công ngoài giờ: Cần gửi yêu cầu trước 24 giờ.</li>
                                <li>Vận chuyển hàng hóa cồng kềnh: Khung giờ sau 18:00 các ngày trong tuần.</li>
                                <li>Hóa đơn cước sẽ được gửi tự động qua email <strong>{{ $tenant->billing_email }}</strong> vào ngày 01 đầu tháng.</li>
                            </ul>
                        </div>

                        <div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:14px;padding-top:14px;border-top:1px solid #f1f5f9;">
                            <button type="button" class="btn-mo-primary" style="background:#2563eb;padding:7px 12px;font-size:12.5px;border-radius:6px;display:inline-flex;align-items:center;gap:6px;" onclick="openTenantTicketModal()">
                                <i class="fa-solid fa-plus"></i> Tạo Ticket kỹ thuật
                            </button>
                            <button type="button" class="btn-mo-outline" style="background:#eff6ff;color:#2563eb;border-color:#93c5fd;font-weight:600;padding:7px 12px;font-size:12.5px;border-radius:6px;display:inline-flex;align-items:center;gap:6px;" onclick="openTenantAfterHoursModal()">
                                <i class="fa-solid fa-clock"></i> Đăng ký thi công ngoài giờ
                            </button>
                            <button type="button" class="btn-mo-outline" style="font-size:12.5px;padding:7px 12px;border-radius:6px;display:inline-flex;align-items:center;gap:6px;" wire:click="switchTab('tickets')">
                                <i class="fa-solid fa-list-check"></i> Xem danh sách ticket ({{ $totalTicketsCount }})
                            </button>
                        </div>
                    </div>
                </div>
            </div>

        <!-- TAB 2: THÔNG BÁO CƯỚC & ĐÓNG PHÍ -->
        @elseif($activeTab === 'bills')
            <div class="filament-page-header" style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;">
                <div>
                    <div class="filament-breadcrumbs" style="font-size:12px;color:#64748b;">Cổng khách thuê / Tài chính</div>
                    <h1 class="filament-page-title" style="font-size:22px;font-weight:700;color:var(--mo-primary);margin:0;">
                        <i class="fa-solid fa-file-invoice-dollar"></i> Lịch Sử Thông Báo Phí & Công Nợ
                    </h1>
                </div>
            </div>

            <div class="filament-card" style="background:#fff;border-radius:8px;border:1px solid #e2e8f0;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,0.05);">
                <div style="overflow-x:auto;">
                    <table class="mo-table" style="width:100%;border-collapse:collapse;text-align:left;font-size:13.5px;">
                        <thead>
                            <tr style="background:#f8fafc;border-bottom:1px solid #e2e8f0;color:#475569;font-weight:600;">
                                <th style="padding:12px 16px;">Mã Phiếu Cước</th>
                                <th style="padding:12px 16px;">Kỳ Cước</th>
                                <th style="padding:12px 16px;">Tòa Nhà</th>
                                <th style="padding:12px 16px;">Tổng Tiền (VNĐ)</th>
                                <th style="padding:12px 16px;">Hạn Nộp</th>
                                <th style="padding:12px 16px;">Trạng Thái</th>
                                <th style="padding:12px 16px;text-align:right;">Thao Tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentDebitNotes as $dn)
                                @php
                                    $st = is_object($dn->status) ? ($dn->status->value ?? 'PENDING') : $dn->status;
                                    $badge = match($st) {
                                        'PAID' => 'success',
                                        'OVERDUE' => 'danger',
                                        default => 'warning',
                                    };
                                @endphp
                                <tr style="border-bottom:1px solid #f1f5f9;">
                                    <td style="padding:14px 16px;">
                                        <strong style="font-family:monospace;color:var(--mo-primary);">{{ $dn->debit_note_code }}</strong>
                                    </td>
                                    <td style="padding:14px 16px;">
                                        <strong>Tháng {{ $dn->billing_month }}/{{ $dn->billing_year }}</strong>
                                    </td>
                                    <td style="padding:14px 16px;">
                                        {{ $dn->building?->name }}
                                    </td>
                                    <td style="padding:14px 16px;">
                                        <strong style="color:#ea580c;font-size:14px;">{{ number_format($dn->total_amount, 0, ',', '.') }} đ</strong>
                                    </td>
                                    <td style="padding:14px 16px;">
                                        {{ $dn->due_date ? \Carbon\Carbon::parse($dn->due_date)->format('d/m/Y') : '-' }}
                                    </td>
                                    <td style="padding:14px 16px;">
                                        <span class="badge-filament {{ $badge }}">
                                            {{ $st === 'PAID' ? 'Đã thanh toán' : ($st === 'OVERDUE' ? 'Quá hạn' : 'Chờ thanh toán') }}
                                        </span>
                                    </td>
                                    <td style="padding:14px 16px;text-align:right;">
                                        <button wire:click="openDebitModal('{{ $dn->id }}')" class="btn-mo-orange" style="padding:5px 12px;font-size:12px;">
                                            <i class="fa-solid fa-list-check"></i> Chi Tiết
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" style="text-align:center;padding:40px 20px;color:#94a3b8;">
                                        <i class="fa-solid fa-file-invoice" style="font-size:36px;margin-bottom:12px;display:block;"></i>
                                        Chưa có thông báo cước nào phát hành cho doanh nghiệp.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        <!-- TAB 3: YÊU CẦU HỖ TRỢ KỸ THUẬT & ĐĂNG KÝ THI CÔNG NGOÀI GIỜ -->
        @elseif($activeTab === 'tickets')
            <div class="filament-page-header" style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;flex-wrap:wrap;gap:12px;">
                <div>
                    <div class="filament-breadcrumbs" style="font-size:12px;color:#64748b;">Cổng khách thuê / Dịch vụ & CSKH</div>
                    <h1 class="filament-page-title" style="font-size:22px;font-weight:700;color:var(--mo-primary);margin:0;">
                        <i class="fa-solid fa-headset"></i> Yêu Cầu Hỗ Trợ, Sửa Chữa & Đăng Ký Thi Công
                    </h1>
                    <p style="font-size:13px;color:#64748b;margin:4px 0 0 0;">
                        Báo cáo sự cố kỹ thuật (điện, nước, điều hòa, vệ sinh) hoặc nộp đơn xin đăng ký thi công nội thất ngoài giờ; theo dõi tiến độ xử lý và đánh giá chất lượng dịch vụ CSAT.
                    </p>
                </div>
                <div style="display:flex;gap:8px;flex-wrap:wrap;">
                    <button wire:click="$refresh" type="button" class="btn-mo-outline" style="background:#fff;padding:8px 14px;font-size:13px;border-radius:6px;">
                        <i class="fa-solid fa-arrows-rotate"></i> Làm mới
                    </button>
                    <button type="button" class="btn-mo-outline" style="background:#eff6ff;color:#2563eb;border-color:#93c5fd;font-weight:700;padding:8px 16px;font-size:13px;border-radius:6px;" onclick="openTenantAfterHoursModal()">
                        <i class="fa-solid fa-clock"></i> Đăng ký thi công ngoài giờ
                    </button>
                    <button type="button" class="btn-mo-primary" style="background:#2563eb;padding:8px 16px;font-size:13px;border-radius:6px;font-weight:600;" onclick="openTenantTicketModal()">
                        <i class="fa-solid fa-plus"></i> Tạo Ticket kỹ thuật
                    </button>
                </div>
            </div>

            <!-- 4 Thẻ KPI thống kê Ticket -->
            <div class="filament-widgets-grid" style="display:grid;grid-template-columns:repeat(auto-fit, minmax(200px, 1fr));gap:16px;margin-bottom:20px;">
                <div class="filament-stat-card" style="background:#fff;padding:16px 18px;border-radius:8px;border:1px solid #e2e8f0;box-shadow:0 1px 3px rgba(0,0,0,0.05);">
                    <div style="font-size:12.5px;color:#64748b;margin-bottom:4px;">Tổng số yêu cầu</div>
                    <div style="font-size:22px;font-weight:800;color:#0f172a;">{{ $totalTicketsCount }} <span style="font-size:13px;font-weight:500;color:#64748b;">phiếu</span></div>
                    <div style="font-size:12px;color:#64748b;margin-top:4px;"><i class="fa-solid fa-receipt"></i> Tất cả các kỳ</div>
                </div>

                <div class="filament-stat-card" style="background:#fff;padding:16px 18px;border-radius:8px;border:1px solid #e2e8f0;box-shadow:0 1px 3px rgba(0,0,0,0.05);">
                    <div style="font-size:12.5px;color:#64748b;margin-bottom:4px;">Sự cố kỹ thuật</div>
                    @php
                        $maintCount = $tenantTickets->filter(function($t) {
                            $c = is_object($t->category) ? ($t->category->value ?? $t->category) : $t->category;
                            return in_array($c, ['MAINTENANCE', 'REPAIR', 'SECURITY_CLEANING']);
                        })->count();
                    @endphp
                    <div style="font-size:22px;font-weight:800;color:#2563eb;">{{ $maintCount }} <span style="font-size:13px;font-weight:500;color:#64748b;">sự cố</span></div>
                    <div style="font-size:12px;color:#2563eb;margin-top:4px;"><i class="fa-solid fa-wrench"></i> Điện, nước, HVAC</div>
                </div>

                <div class="filament-stat-card" style="background:#fff;padding:16px 18px;border-radius:8px;border:1px solid #e2e8f0;box-shadow:0 1px 3px rgba(0,0,0,0.05);">
                    <div style="font-size:12.5px;color:#64748b;margin-bottom:4px;">Đăng ký thi công</div>
                    @php
                        $constCount = $tenantTickets->filter(function($t) {
                            $c = is_object($t->category) ? ($t->category->value ?? $t->category) : $t->category;
                            return in_array($c, ['OVERTIME_CONSTRUCTION', 'CONSTRUCTION']);
                        })->count();
                    @endphp
                    <div style="font-size:22px;font-weight:800;color:#d97706;">{{ $constCount }} <span style="font-size:13px;font-weight:500;color:#64748b;">đơn</span></div>
                    <div style="font-size:12px;color:#d97706;margin-top:4px;"><i class="fa-solid fa-hammer"></i> Ngoài giờ & Nội thất</div>
                </div>

                <div class="filament-stat-card" style="background:#fff;padding:16px 18px;border-radius:8px;border:1px solid #e2e8f0;box-shadow:0 1px 3px rgba(0,0,0,0.05);">
                    <div style="font-size:12.5px;color:#64748b;margin-bottom:4px;">Đang xử lý / Chờ nghiệm thu</div>
                    <div style="font-size:22px;font-weight:800;color:#7e22ce;">{{ $openTicketsCount }} <span style="font-size:13px;font-weight:500;color:#64748b;">phiếu</span></div>
                    <div style="font-size:12px;color:#7e22ce;margin-top:4px;"><i class="fa-solid fa-clock-rotate-left"></i> BQL đang giải quyết</div>
                </div>
            </div>

            <!-- Bộ lọc & Tìm kiếm Ticket với nút X bo tròn -->
            <div class="filament-card" style="padding:16px 20px;margin-bottom:20px;background:#fff;border-radius:8px;border:1px solid #e2e8f0;box-shadow:0 1px 3px rgba(0,0,0,0.05);">
                <div style="display:flex;flex-wrap:wrap;gap:12px;align-items:center;justify-content:space-between;">
                    <div style="display:flex;gap:10px;align-items:center;flex:1;max-width:540px;">
                        <div style="position:relative;flex:1;display:flex;align-items:center;">
                            <i class="fa-solid fa-magnifying-glass" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:#94a3b8;font-size:13px;"></i>
                            <input type="text" wire:model="ticketSearchKeyword" wire:keydown.enter="$refresh" placeholder="Tìm theo mã ticket, tiêu đề, mô tả sự cố..."
                                style="width:100%;padding:8px 30px 8px 32px;border:1px solid #cbd5e1;border-radius:6px;font-size:13px;box-sizing:border-box;">
                            @if(!empty($ticketSearchKeyword))
                                <button type="button" wire:click="clearTicketSearch" style="position:absolute;right:8px;top:50%;transform:translateY(-50%);width:19px;height:19px;border-radius:50%;background:#cbd5e1;color:#475569;border:none;display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:11px;padding:0;" title="Xóa tìm kiếm">
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                            @endif
                        </div>
                        <button type="button" wire:click="$refresh" style="background:#2563eb;color:#fff;border:none;border-radius:6px;padding:8px 14px;font-size:12.5px;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:6px;white-space:nowrap;">
                            <i class="fa-solid fa-magnifying-glass"></i> Tìm kiếm
                        </button>
                    </div>

                    <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
                        <select wire:model.live="ticketCategory" style="padding:7px 12px;border:1px solid #cbd5e1;border-radius:6px;font-size:13px;color:#334155;">
                            <option value="ALL">Tất cả Phân Loại</option>
                            <option value="MAINTENANCE">Sự cố kỹ thuật / Bảo trì</option>
                            <option value="OVERTIME_CONSTRUCTION">Đăng ký thi công ngoài giờ</option>
                            <option value="SECURITY_CLEANING">An ninh / Vệ sinh</option>
                            <option value="SERVICE_COMPLAINT">Khiếu nại dịch vụ</option>
                        </select>

                        <select wire:model.live="ticketStatus" style="padding:7px 12px;border:1px solid #cbd5e1;border-radius:6px;font-size:13px;color:#334155;">
                            <option value="ALL">Tất cả Trạng Thái</option>
                            <option value="NEW">Mới tiếp nhận</option>
                            <option value="OPEN">BQL đã mở phiếu</option>
                            <option value="IN_PROGRESS">Đang xử lý</option>
                            <option value="WAITING_CONFIRMATION">Chờ nghiệm thu</option>
                            <option value="RESOLVED">Đã khắc phục</option>
                            <option value="COMPLETED">Khách đã nghiệm thu</option>
                            <option value="CLOSED">Hoàn tất / Đã đóng</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Bảng danh sách Ticket -->
            <div class="filament-card" style="background:#fff;border-radius:8px;border:1px solid #e2e8f0;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,0.05);">
                <div style="overflow-x:auto;">
                    <table class="mo-table" style="width:100%;border-collapse:collapse;text-align:left;font-size:13.5px;">
                        <thead>
                            <tr style="background:#f8fafc;border-bottom:1px solid #e2e8f0;color:#475569;font-weight:600;">
                                <th style="padding:12px 16px;">Mã Ticket</th>
                                <th style="padding:12px 16px;">Phân Loại</th>
                                <th style="padding:12px 16px;">Tiêu Đề / Nội Dung</th>
                                <th style="padding:12px 16px;">Vị Trí & Tòa Nhà</th>
                                <th style="padding:12px 16px;">Thời Gian Gửi</th>
                                <th style="padding:12px 16px;">Mức Ưu Tiên</th>
                                <th style="padding:12px 16px;">Tiến Độ Xử Lý</th>
                                <th style="padding:12px 16px;text-align:right;">Thao Tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($tenantTickets as $tk)
                                @php
                                    $stRaw = is_object($tk->status) ? ($tk->status->value ?? 'NEW') : $tk->status;
                                    $catRaw = is_object($tk->category) ? ($tk->category->value ?? 'MAINTENANCE') : $tk->category;
                                    $priRaw = is_object($tk->priority) ? ($tk->priority->value ?? 'NORMAL') : $tk->priority;

                                    $catName = match($catRaw) {
                                        'OVERTIME_CONSTRUCTION' => 'Thi công ngoài giờ',
                                        'SECURITY_CLEANING' => 'An ninh & Vệ sinh',
                                        'SERVICE_COMPLAINT' => 'Khiếu nại dịch vụ',
                                        default => 'Sự cố kỹ thuật',
                                    };

                                    $catBadge = match($catRaw) {
                                        'OVERTIME_CONSTRUCTION' => 'warning',
                                        'SECURITY_CLEANING' => 'info',
                                        'SERVICE_COMPLAINT' => 'danger',
                                        default => 'primary',
                                    };

                                    $stName = match($stRaw) {
                                        'NEW' => 'Mới tiếp nhận',
                                        'OPEN' => 'BQL đã mở',
                                        'IN_PROGRESS' => 'Đang xử lý',
                                        'ON_HOLD' => 'Tạm hoãn',
                                        'WAITING_CONFIRMATION', 'RESOLVED' => 'Chờ nghiệm thu',
                                        'COMPLETED' => 'Đã nghiệm thu',
                                        'CLOSED' => 'Đã đóng',
                                        default => $stRaw,
                                    };

                                    $stBadge = match($stRaw) {
                                        'NEW' => 'badge-filament info',
                                        'OPEN', 'IN_PROGRESS' => 'badge-filament warning',
                                        'WAITING_CONFIRMATION', 'RESOLVED' => 'badge-filament purple',
                                        'COMPLETED', 'CLOSED' => 'badge-filament success',
                                        default => 'badge-filament secondary',
                                    };

                                    $priBadge = match($priRaw) {
                                        'URGENT' => 'background:#fef2f2;color:#b91c1c;border:1px solid #fecaca;',
                                        'HIGH' => 'background:#fff7ed;color:#c2410c;border:1px solid #ffedd5;',
                                        default => 'background:#f1f5f9;color:#475569;border:1px solid #e2e8f0;',
                                    };
                                @endphp
                                <tr style="border-bottom:1px solid #f1f5f9;">
                                    <td style="padding:14px 16px;">
                                        <strong style="color:var(--mo-primary);font-family:monospace;cursor:pointer;" onclick="openTicketDetail('{{ $tk->id }}')">
                                            {{ $tk->ticket_code ?: '#TK-' . substr($tk->id, 0, 8) }}
                                        </strong>
                                    </td>
                                    <td style="padding:14px 16px;">
                                        <span class="badge-filament {{ $catBadge }}">{{ $catName }}</span>
                                    </td>
                                    <td style="padding:14px 16px;max-width:280px;">
                                        <div style="font-weight:600;color:#1e293b;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="{{ $tk->title }}">
                                            {{ $tk->title }}
                                        </div>
                                        <div style="font-size:12px;color:#64748b;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                            {{ $tk->description }}
                                        </div>
                                    </td>
                                    <td style="padding:14px 16px;font-size:12.5px;color:#475569;">
                                        <div><i class="fa-solid fa-building" style="color:#94a3b8;"></i> {{ $tk->building?->name ?? 'Tòa nhà văn phòng' }}</div>
                                        @if($tk->unit)
                                            <div style="font-size:11.5px;color:#64748b;">Mặt bằng: <strong>{{ $tk->unit->unit_code }}</strong> (Tầng {{ $tk->unit->floor_number }})</div>
                                        @endif
                                    </td>
                                    <td style="padding:14px 16px;font-size:12.5px;color:#64748b;">
                                        {{ $tk->created_at ? $tk->created_at->format('d/m/Y H:i') : 'Vừa xong' }}
                                    </td>
                                    <td style="padding:14px 16px;">
                                        <span style="font-size:11px;font-weight:700;padding:3px 8px;border-radius:4px;{{ $priBadge }}">
                                            {{ $priRaw }}
                                        </span>
                                    </td>
                                    <td style="padding:14px 16px;">
                                        <span class="{{ $stBadge }}" style="font-size:12px;">{{ $stName }}</span>
                                    </td>
                                    <td style="padding:14px 16px;text-align:right;white-space:nowrap;">
                                        <button type="button" class="btn-mo-primary" style="padding:5px 10px;font-size:12px;border-radius:6px;" onclick="openTicketDetail('{{ $tk->id }}')">
                                            <i class="fa-solid fa-eye"></i> Chi tiết
                                        </button>
                                        @if(in_array($stRaw, ['WAITING_CONFIRMATION', 'RESOLVED']))
                                            <button type="button" class="btn-mo-orange" style="padding:5px 10px;font-size:12px;border-radius:6px;margin-left:4px;" onclick="openConfirmTicketModal('{{ $tk->id }}')">
                                                <i class="fa-solid fa-check"></i> Nghiệm thu
                                            </button>
                                        @endif
                                        @if(in_array($stRaw, ['COMPLETED', 'CLOSED']))
                                            <button type="button" class="btn-mo-outline" style="padding:5px 10px;font-size:12px;border-radius:6px;margin-left:4px;color:#d97706;border-color:#fde68a;" onclick="openRateTicketModal('{{ $tk->id }}')">
                                                <i class="fa-solid fa-star"></i> Đánh giá CSAT
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" style="text-align:center;padding:40px 20px;color:#64748b;">
                                        <div style="width:54px;height:54px;border-radius:50%;background:#eff6ff;color:#2563eb;display:flex;align-items:center;justify-content:center;margin:0 auto 12px;font-size:24px;">
                                            <i class="fa-solid fa-circle-check"></i>
                                        </div>
                                        <div style="font-size:15px;font-weight:700;color:#0f172a;margin-bottom:4px;">Hiện tại không có yêu cầu sự cố nào!</div>
                                        <p style="font-size:13px;color:#64748b;margin:0 0 16px 0;">Văn phòng của bạn đang vận hành ổn định. Nếu gặp bất kỳ sự cố kỹ thuật hoặc cần đăng ký thi công, hãy gửi yêu cầu ngay.</p>
                                        <div style="display:flex;justify-content:center;gap:10px;">
                                            <button type="button" class="btn-mo-primary" style="background:#2563eb;padding:8px 16px;font-size:13px;border-radius:6px;" onclick="openTenantTicketModal()">
                                                <i class="fa-solid fa-plus"></i> Tạo Ticket kỹ thuật
                                            </button>
                                            <button type="button" class="btn-mo-outline" style="padding:8px 16px;font-size:13px;border-radius:6px;" onclick="openTenantAfterHoursModal()">
                                                <i class="fa-solid fa-clock"></i> Đăng ký thi công ngoài giờ
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    @endif

    <!-- MODAL: CHI TIẾT BẢNG KÊ PHIẾU CƯỚC -->
    @if($showDebitModal && $selectedDebitNote)
        <div style="position:fixed;inset:0;background:rgba(0,0,0,0.5);display:flex;align-items:center;justify-content:center;z-index:9999;padding:16px;">
            <div style="background:#fff;border-radius:10px;width:100%;max-width:750px;max-height:90vh;overflow-y:auto;box-shadow:0 20px 25px -5px rgba(0,0,0,0.2);">
                <div style="padding:16px 24px;border-bottom:1px solid #e2e8f0;display:flex;justify-content:space-between;align-items:center;">
                    <div>
                        <h3 style="margin:0;font-size:17px;font-weight:700;color:var(--mo-primary);">
                            Chi Tiết Phiếu Cước: {{ $selectedDebitNote->debit_note_code }}
                        </h3>
                        <div style="font-size:12px;color:#64748b;margin-top:2px;">
                            Kỳ cước: Tháng {{ $selectedDebitNote->billing_month }}/{{ $selectedDebitNote->billing_year }} - Tòa nhà: {{ $selectedDebitNote->building?->name }}
                        </div>
                    </div>
                    <button wire:click="closeDebitModal" style="background:none;border:none;font-size:18px;cursor:pointer;color:#64748b;">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <div style="padding:20px 24px;">
                    <table style="width:100%;border-collapse:collapse;font-size:13px;border:1px solid #e2e8f0;margin-bottom:16px;">
                        <thead>
                            <tr style="background:#f1f5f9;border-bottom:1px solid #e2e8f0;text-align:left;">
                                <th style="padding:8px 12px;">Khoản Mục</th>
                                <th style="padding:8px 12px;">Mô Tả / Mặt Bằng</th>
                                <th style="padding:8px 12px;text-align:right;">Số Lượng</th>
                                <th style="padding:8px 12px;text-align:right;">Đơn Giá</th>
                                <th style="padding:8px 12px;text-align:right;">Thành Tiền (VNĐ)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($selectedDebitNote->details as $d)
                                <tr style="border-bottom:1px solid #f1f5f9;">
                                    <td style="padding:8px 12px;"><strong>{{ $d->item_category }}</strong></td>
                                    <td style="padding:8px 12px;">{{ $d->description }}</td>
                                    <td style="padding:8px 12px;text-align:right;">{{ number_format($d->quantity, 1) }} {{ $d->unit_name }}</td>
                                    <td style="padding:8px 12px;text-align:right;">{{ number_format($d->unit_price, 0, ',', '.') }} đ</td>
                                    <td style="padding:8px 12px;text-align:right;"><strong>{{ number_format($d->line_amount, 0, ',', '.') }} đ</strong></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" style="text-align:center;padding:16px;color:#94a3b8;">Không có chi tiết khoản mục.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div style="display:flex;justify-content:flex-end;">
                        <div style="width:300px;font-size:13.5px;">
                            <div style="display:flex;justify-content:space-between;padding:4px 0;">
                                <span style="color:#64748b;">Tổng tiền trước thuế:</span>
                                <strong>{{ number_format($selectedDebitNote->sub_total_amount, 0, ',', '.') }} đ</strong>
                            </div>
                            <div style="display:flex;justify-content:space-between;padding:4px 0;">
                                <span style="color:#64748b;">Thuế GTGT (VAT {{ $selectedDebitNote->vat_percent }}%):</span>
                                <strong>{{ number_format($selectedDebitNote->sub_total_amount * ($selectedDebitNote->vat_percent / 100), 0, ',', '.') }} đ</strong>
                            </div>
                            @if($selectedDebitNote->previous_debt_amount > 0)
                                <div style="display:flex;justify-content:space-between;padding:4px 0;color:#dc2626;">
                                    <span>Nợ kỳ trước chuyển sang:</span>
                                    <strong>+{{ number_format($selectedDebitNote->previous_debt_amount, 0, ',', '.') }} đ</strong>
                                </div>
                            @endif
                            <div style="display:flex;justify-content:space-between;padding:8px 0;border-top:1px solid #cbd5e1;font-size:16px;color:#ea580c;margin-top:6px;">
                                <strong>Tổng Cộng Thanh Toán:</strong>
                                <strong>{{ number_format($selectedDebitNote->total_amount, 0, ',', '.') }} đ</strong>
                            </div>
                        </div>
                    </div>
                </div>

                <div style="padding:16px 24px;border-top:1px solid #e2e8f0;display:flex;justify-content:flex-end;">
                    <button wire:click="closeDebitModal" class="btn-mo-outline" style="padding:8px 18px;">Đóng</button>
                </div>
            </div>
        </div>
    @endif

    <!-- MODAL XEM CHI TIẾT & SƠ ĐỒ MẶT BẰNG VĂN PHÒNG KHÁCH THUÊ -->
    @if($showUnitModal && $selectedUnit)
        <div style="position:fixed;inset:0;background:rgba(15,23,42,0.65);backdrop-filter:blur(3px);display:flex;align-items:center;justify-content:center;z-index:9999;padding:16px;">
            <div style="background:#ffffff;border-radius:12px;width:100%;max-width:720px;box-shadow:0 20px 25px -5px rgba(0,0,0,0.25);overflow:hidden;border:1px solid #e2e8f0;max-height:92vh;display:flex;flex-direction:column;">
                <div style="background:var(--mo-primary);color:#ffffff;padding:16px 20px;display:flex;justify-content:space-between;align-items:center;">
                    <h3 style="margin:0;font-size:16px;font-weight:700;">
                        <i class="fa-solid fa-map-location-dot"></i> Mặt Bằng Văn Phòng: {{ $selectedUnit->unit_code }} (Tầng {{ $selectedUnit->floor_number }})
                    </h3>
                    <button wire:click="closeUnitModal" type="button" style="background:none;border:none;color:#ffffff;font-size:18px;cursor:pointer;">&times;</button>
                </div>
                <div style="padding:20px;overflow-y:auto;flex:1;">
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:18px;">
                        <div style="background:#f8fafc;padding:12px 16px;border-radius:8px;border:1px solid #e2e8f0;">
                            <div style="font-size:12px;color:#64748b;">Tòa nhà:</div>
                            <div style="font-size:14px;font-weight:700;color:var(--mo-primary);">{{ $selectedUnit->building?->name ?? 'Tòa nhà văn phòng' }}</div>
                            <div style="font-size:12px;color:#64748b;margin-top:8px;">Diện tích mặt bằng:</div>
                            <div style="font-size:16px;font-weight:700;color:#0284c7;">{{ number_format($selectedUnit->area_sqm, 1) }} m²</div>
                        </div>
                        <div style="background:#f8fafc;padding:12px 16px;border-radius:8px;border:1px solid #e2e8f0;">
                            <div style="font-size:12px;color:#64748b;">Mã sàn:</div>
                            <div style="font-size:15px;font-weight:700;color:#1e293b;">{{ $selectedUnit->unit_code }} - Tầng {{ $selectedUnit->floor_number }}</div>
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
                            <i class="fa-solid fa-images"></i> Sơ Đồ Thiết Kế & Hình Ảnh Bàn Giao Văn Phòng:
                        </div>
                        @if($selectedUnit->images && $selectedUnit->images->count() > 0)
                            <div style="display:grid;grid-template-columns:repeat(3, 1fr);gap:10px;">
                                @foreach($selectedUnit->images as $img)
                                    <div style="border-radius:8px;overflow:hidden;border:1px solid #e2e8f0;background:#f8fafc;">
                                        <img src="{{ $img->image_url }}" alt="Ảnh mặt bằng {{ $selectedUnit->unit_code }}" style="width:100%;height:130px;object-fit:cover;display:block;">
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div style="padding:24px;border:1px dashed #cbd5e1;border-radius:8px;text-align:center;color:#64748b;background:#f8fafc;">
                                <i class="fa-regular fa-image" style="font-size:24px;margin-bottom:6px;display:block;color:#94a3b8;"></i>
                                Chưa có bản vẽ sơ đồ hoặc ảnh mặt bằng được tải lên cho gian phòng này.
                            </div>
                        @endif
                    </div>
                </div>
                <div style="padding:14px 20px;border-top:1px solid #e2e8f0;display:flex;justify-content:flex-end;background:#f8fafc;">
                    <button wire:click="closeUnitModal" type="button" class="btn-mo-outline" style="padding:6px 16px;">Đóng</button>
                </div>
            </div>
        </div>
    @endif

    {{-- MODAL BẮT BUỘC ĐỔI MẬT KHẨU LẦN ĐẦU (FIRST-TIME LOGIN SECURITY) --}}
    @if($showForceChangeModal)
        <div style="position:fixed;inset:0;background:rgba(15,23,42,0.75);backdrop-filter:blur(4px);display:flex;align-items:center;justify-content:center;z-index:10008;padding:16px;">
            <div style="background:#fff;border-radius:12px;width:100%;max-width:480px;box-shadow:0 25px 50px -12px rgba(0,0,0,0.35);overflow:hidden;border:1px solid #cbd5e1;">
                <div style="background:linear-gradient(135deg, #ea580c, #c2410c);padding:18px 24px;color:#fff;display:flex;align-items:center;justify-content:space-between;">
                    <div style="display:flex;align-items:center;gap:10px;">
                        <span style="display:inline-flex;width:34px;height:34px;border-radius:50%;background:rgba(255,255,255,0.2);align-items:center;justify-content:center;font-size:16px;">
                            <i class="fa-solid fa-shield-halved"></i>
                        </span>
                        <div>
                            <h3 style="margin:0;font-size:16.5px;font-weight:700;">Đổi Mật Khẩu Khởi Tạo Lần Đầu</h3>
                            <p style="margin:2px 0 0 0;font-size:12px;color:#ffedd5;">Bảo vệ an toàn tài khoản Cổng Khách Thuê</p>
                        </div>
                    </div>
                </div>

                <form wire:submit.prevent="updateInitialPassword" style="padding:22px 24px;">
                    <div style="background:#fff7ed;border:1px solid #ffedd5;border-radius:8px;padding:12px 14px;font-size:12.5px;color:#9a3412;line-height:1.5;margin-bottom:16px;">
                        <i class="fa-solid fa-lock"></i> <strong>Lưu ý bắt buộc:</strong> Tài khoản của Quý khách đang sử dụng mật khẩu khởi tạo ban đầu do Ban Quản Lý cấp. Vui lòng thiết lập mật khẩu cá nhân mới để kích hoạt đầy đủ quyền xem hợp đồng và hóa đơn cước.
                    </div>

                    @if($passwordFlashError)
                        <div style="background:#fef2f2;border:1px solid #fecaca;color:#dc2626;padding:10px 14px;border-radius:6px;font-size:13px;margin-bottom:14px;">
                            <i class="fa-solid fa-circle-exclamation"></i> {{ $passwordFlashError }}
                        </div>
                    @endif

                    <div style="margin-bottom:14px;">
                        <label style="display:block;font-size:12.5px;font-weight:600;color:#334155;margin-bottom:4px;">Mật Khẩu Khởi Tạo Hiện Tại: <span style="color:red;">*</span></label>
                        <input type="password" wire:model="current_password" placeholder="Nhập mật khẩu BQL vừa cấp"
                            style="width:100%;padding:9px 12px;border:1px solid #cbd5e1;border-radius:6px;font-size:13.5px;box-sizing:border-box;">
                        @error('current_password') <span style="color:#dc2626;font-size:12px;">{{ $message }}</span> @enderror
                    </div>

                    <div style="margin-bottom:14px;">
                        <label style="display:block;font-size:12.5px;font-weight:600;color:#334155;margin-bottom:4px;">Mật Khẩu Mới: <span style="color:red;">*</span></label>
                        <input type="password" wire:model="new_password" placeholder="Tối thiểu 8 ký tự (chữ hoa, số, ký hiệu)"
                            style="width:100%;padding:9px 12px;border:1px solid #cbd5e1;border-radius:6px;font-size:13.5px;box-sizing:border-box;">
                        @error('new_password') <span style="color:#dc2626;font-size:12px;">{{ $message }}</span> @enderror
                    </div>

                    <div style="margin-bottom:20px;">
                        <label style="display:block;font-size:12.5px;font-weight:600;color:#334155;margin-bottom:4px;">Xác Nhận Mật Khẩu Mới: <span style="color:red;">*</span></label>
                        <input type="password" wire:model="new_password_confirmation" placeholder="Nhập lại mật khẩu mới"
                            style="width:100%;padding:9px 12px;border:1px solid #cbd5e1;border-radius:6px;font-size:13.5px;box-sizing:border-box;">
                    </div>

                    <div style="display:flex;justify-content:flex-end;gap:10px;">
                        <button type="submit" class="btn-mo-primary" style="background:#ea580c;border-color:#ea580c;padding:9px 20px;font-size:13px;font-weight:600;">
                            <i class="fa-solid fa-check"></i> Lưu & Kích Hoạt Mật Khẩu Mới
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
