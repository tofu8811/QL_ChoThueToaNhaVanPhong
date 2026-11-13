<div>
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
                        {{ $activeContract?->end_date ? \Carbon\Carbon::parse($activeContract->end_date)->diffInMonths(now()) : 0 }} <span style="font-size:15px;color:#64748b;">tháng còn lại</span>
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
</div>
