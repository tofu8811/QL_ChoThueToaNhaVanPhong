<div>
    <!-- Header & Action Bar -->
    <div class="filament-page-header" style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:12px;">
        <div>
            <div class="filament-breadcrumbs" style="font-size:12px;color:#64748b;margin-bottom:4px;">
                Ban Quản Lý & Pháp Lý / Quản Lý Hợp Đồng Thuê
            </div>
            <h1 class="filament-page-title" style="font-size:22px;font-weight:700;color:var(--mo-primary);margin:0;">
                <i class="fa-solid fa-file-contract"></i> Quản Lý Hợp Đồng & Chống Trùng Lịch Sàn
            </h1>
        </div>
        <div style="display:flex;gap:10px;">
            <button wire:click="openCreateModal" class="btn-mo-orange" style="cursor:pointer;display:inline-flex;align-items:center;gap:6px;padding:8px 16px;border-radius:6px;font-weight:600;">
                <i class="fa-solid fa-file-circle-plus"></i> Lập Hợp Đồng Mới
            </button>
        </div>
    </div>

    <!-- Flash Alert -->
    @if ($flashMessage)
        <div style="padding:12px 18px;border-radius:8px;margin-bottom:18px;display:flex;justify-content:space-between;align-items:center;
            background: {{ $flashType === 'success' ? '#ecfdf5' : ($flashType === 'warning' ? '#fffbeb' : '#fef2f2') }};
            color: {{ $flashType === 'success' ? '#065f46' : ($flashType === 'warning' ? '#92400e' : '#991b1b') }};
            border: 1px solid {{ $flashType === 'success' ? '#a7f3d0' : ($flashType === 'warning' ? '#fde68a' : '#fecaca') }};">
            <div style="display:flex;align-items:center;gap:10px;font-size:14px;font-weight:500;">
                <i class="fa-solid {{ $flashType === 'success' ? 'fa-circle-check' : ($flashType === 'warning' ? 'fa-triangle-exclamation' : 'fa-circle-exclamation') }}"></i>
                <span>{{ $flashMessage }}</span>
            </div>
            <button wire:click="$set('flashMessage', null)" style="background:none;border:none;cursor:pointer;color:inherit;font-size:16px;">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
    @endif

    <!-- Filter Bar -->
    <div class="filament-card" style="padding:16px 20px;margin-bottom:20px;background:#fff;border-radius:8px;border:1px solid #e2e8f0;box-shadow:0 1px 3px rgba(0,0,0,0.05);">
        <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(200px, 1fr));gap:14px;align-items:end;">
            <!-- Search Keyword -->
            <div>
                <label style="display:block;font-size:12px;font-weight:600;color:#475569;margin-bottom:6px;">Tìm kiếm (Số HĐ, Công ty, MST):</label>
                <div style="display:flex;gap:8px;align-items:center;">
                    <div style="position:relative;flex:1;">
                        <i class="fa-solid fa-magnifying-glass" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:#94a3b8;font-size:13px;"></i>
                        <input type="text" wire:model="searchKeyword" wire:keydown.enter="$refresh" placeholder="Nhập từ khóa tra cứu..."
                            style="width:100%;padding:8px 30px 8px 32px;border:1px solid #cbd5e1;border-radius:6px;font-size:13px;box-sizing:border-box;">
                        @if(!empty($searchKeyword))
                            <button type="button" wire:click="$set('searchKeyword', '')" style="position:absolute;right:8px;top:50%;transform:translateY(-50%);width:19px;height:19px;border-radius:50%;background:#cbd5e1;color:#475569;border:none;display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:11px;padding:0;" title="Xóa tìm kiếm">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        @endif
                    </div>
                    <button type="button" wire:click="$refresh" style="background:#2563eb;color:#fff;border:none;border-radius:6px;padding:8px 14px;font-size:12.5px;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:6px;white-space:nowrap;">
                        <i class="fa-solid fa-magnifying-glass"></i> Tìm kiếm
                    </button>
                </div>
            </div>

            <!-- Filter Building -->
            <div>
                <label style="display:block;font-size:12px;font-weight:600;color:#475569;margin-bottom:6px;">Tòa nhà:</label>
                <select wire:model.live="selectedBuildingId" style="width:100%;padding:8px 12px;border:1px solid #cbd5e1;border-radius:6px;font-size:13px;">
                    <option value="all">Tất cả tòa nhà</option>
                    @foreach($buildings as $b)
                        <option value="{{ $b->id }}">{{ $b->name }} ({{ $b->building_code }})</option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Tenant -->
            <div>
                <label style="display:block;font-size:12px;font-weight:600;color:#475569;margin-bottom:6px;">Khách thuê:</label>
                <select wire:model.live="selectedTenantId" style="width:100%;padding:8px 12px;border:1px solid #cbd5e1;border-radius:6px;font-size:13px;">
                    <option value="all">Tất cả khách thuê</option>
                    @foreach($tenants as $t)
                        <option value="{{ $t->id }}">{{ $t->company_name }} ({{ $t->tax_code }})</option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Status -->
            <div>
                <label style="display:block;font-size:12px;font-weight:600;color:#475569;margin-bottom:6px;">Trạng thái:</label>
                <select wire:model.live="selectedStatus" style="width:100%;padding:8px 12px;border:1px solid #cbd5e1;border-radius:6px;font-size:13px;">
                    <option value="all">Tất cả trạng thái</option>
                    <option value="DRAFT">DRAFT (Bản nháp)</option>
                    <option value="RESERVED">RESERVED (Giữ chỗ)</option>
                    <option value="ACTIVE">ACTIVE (Đang hiệu lực)</option>
                    <option value="EXPIRING_SOON">EXPIRING_SOON (Sắp hết hạn)</option>
                    <option value="EXPIRED">EXPIRED (Hết hạn)</option>
                    <option value="TERMINATED">TERMINATED (Đã thanh lý)</option>
                    <option value="RENEWED">RENEWED (Đã gia hạn)</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Contracts Table -->
    <div class="filament-card" style="background:#fff;border-radius:8px;border:1px solid #e2e8f0;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,0.05);">
        <div style="overflow-x:auto;">
            <table class="mo-table" style="width:100%;border-collapse:collapse;text-align:left;font-size:13.5px;">
                <thead>
                    <tr style="background:#f8fafc;border-bottom:1px solid #e2e8f0;color:#475569;font-weight:600;">
                        <th style="padding:12px 16px;">Số Hợp Đồng</th>
                        <th style="padding:12px 16px;">Khách Hàng (Bên Thuê)</th>
                        <th style="padding:12px 16px;">Tòa Nhà & Sàn Thuê</th>
                        <th style="padding:12px 16px;">Diện Tích</th>
                        <th style="padding:12px 16px;">Tiền Cọc (Ký Quỹ)</th>
                        <th style="padding:12px 16px;">Thời Hạn Thuê</th>
                        <th style="padding:12px 16px;">Trạng Thái</th>
                        <th style="padding:12px 16px;text-align:right;">Thao Tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($contracts as $c)
                        @php
                            $stVal = is_object($c->status) ? ($c->status->value ?? 'DRAFT') : $c->status;
                            $badgeClass = match($stVal) {
                                'ACTIVE' => 'success',
                                'RESERVED' => 'info',
                                'EXPIRING_SOON' => 'warning',
                                'TERMINATED' => 'danger',
                                'RENEWED' => 'info',
                                default => 'warning',
                            };
                        @endphp
                        <tr style="border-bottom:1px solid #f1f5f9;transition:background 0.15s ease;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
                            <td style="padding:14px 16px;">
                                <strong style="color:var(--mo-primary);font-family:monospace;font-size:13px;">{{ $c->contract_number }}</strong>
                                @if($c->parent_contract_id)
                                    <div style="font-size:11px;color:#64748b;margin-top:2px;">
                                        <i class="fa-solid fa-arrows-split-up-and-left"></i> Phụ lục gia hạn
                                    </div>
                                @endif
                            </td>
                            <td style="padding:14px 16px;">
                                <div style="font-weight:600;color:#1e293b;">{{ $c->tenant?->company_name ?? 'Khách thuê doanh nghiệp' }}</div>
                                <div style="font-size:11.5px;color:#64748b;">MST: {{ $c->tenant?->tax_code ?? 'N/A' }}</div>
                            </td>
                            <td style="padding:14px 16px;">
                                <div style="font-weight:500;">{{ $c->building?->name ?? 'Tòa nhà' }}</div>
                                <div style="font-size:11.5px;color:#64748b;">
                                    @php
                                        $unitNames = $c->units->pluck('unit_code')->toArray();
                                    @endphp
                                    {{ !empty($unitNames) ? implode(', ', $unitNames) : 'Chưa gán phòng' }}
                                </div>
                            </td>
                            <td style="padding:14px 16px;">
                                <strong>{{ number_format($c->total_leased_area_sqm ?? 0, 0, ',', '.') }}</strong> m²
                            </td>
                            <td style="padding:14px 16px;">
                                <strong style="color:#0f3361;">{{ number_format($c->deposit_amount ?? 0, 0, ',', '.') }} đ</strong>
                            </td>
                            <td style="padding:14px 16px;font-size:12.5px;">
                                <div>{{ $c->start_date ? \Carbon\Carbon::parse($c->start_date)->format('d/m/Y') : '-' }}</div>
                                <div style="color:#64748b;">đến {{ $c->end_date ? \Carbon\Carbon::parse($c->end_date)->format('d/m/Y') : '-' }}</div>
                            </td>
                            <td style="padding:14px 16px;">
                                <span class="badge-filament {{ $badgeClass }}" style="display:inline-block;padding:4px 8px;border-radius:4px;font-size:11.5px;font-weight:600;">
                                    {{ $stVal }}
                                </span>
                            </td>
                            <td style="padding:14px 16px;text-align:right;">
                                <div style="display:inline-flex;gap:6px;">
                                    <!-- Xuất PDF hợp đồng chuẩn -->
                                    <a href="{{ route('contracts.print', $c->id) }}" target="_blank" class="btn-mo-outline" style="padding:5px 8px;font-size:12px;color:#ea580c;border-color:#fdba74;text-decoration:none;display:inline-flex;align-items:center;gap:4px;" title="Xuất PDF Hợp Đồng">
                                        <i class="fa-solid fa-file-pdf"></i> Xuất PDF
                                    </a>

                                    <!-- Xem chi tiết -->
                                    <button wire:click="openDetailModal('{{ $c->id }}')" class="btn-mo-outline" style="padding:5px 8px;font-size:12px;" title="Xem chi tiết">
                                        <i class="fa-solid fa-eye"></i>
                                    </button>

                                    <!-- Ký duyệt (Nếu là DRAFT hoặc RESERVED) -->
                                    @if(in_array($stVal, ['DRAFT', 'RESERVED']))
                                        <button wire:click="signContract('{{ $c->id }}')" 
                                            wire:confirm="Bạn có chắc chắn muốn KÝ DUYỆT và KÍCH HOẠT hợp đồng này? Mặt bằng sẽ chuyển sang trạng thái ĐÃ THUÊ."
                                            class="btn-mo-primary" style="padding:5px 8px;font-size:12px;background:#10b981;border-color:#10b981;" title="Ký duyệt Hợp đồng">
                                            <i class="fa-solid fa-signature"></i> Ký
                                        </button>
                                    @endif

                                    <!-- Gia hạn (Nếu là ACTIVE hoặc EXPIRING_SOON) -->
                                    @if(in_array($stVal, ['ACTIVE', 'EXPIRING_SOON']))
                                        <button wire:click="openRenewModal('{{ $c->id }}')" class="btn-mo-outline" style="padding:5px 8px;font-size:12px;color:#2563eb;border-color:#93c5fd;" title="Gia hạn hợp đồng">
                                            <i class="fa-solid fa-arrows-rotate"></i>
                                        </button>
                                        <button wire:click="openTerminateModal('{{ $c->id }}')" class="btn-mo-outline" style="padding:5px 8px;font-size:12px;color:#dc2626;border-color:#fca5a5;" title="Thanh lý hợp đồng">
                                            <i class="fa-solid fa-file-excel"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align:center;padding:40px 20px;color:#94a3b8;">
                                <i class="fa-solid fa-file-circle-xmark" style="font-size:36px;margin-bottom:12px;display:block;"></i>
                                <span style="font-size:15px;font-weight:500;">Không tìm thấy hợp đồng nào phù hợp bộ lọc.</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($contracts->hasPages())
            <div style="padding:14px 20px;border-top:1px solid #e2e8f0;">
                {{ $contracts->links() }}
            </div>
        @endif
    </div>

    <!-- MODAL 1: LẬP HỢP ĐỒNG MỚI (DRAFT / BOOKING) KÈM REALTIME OVERLAP CHECK -->
    @if($showCreateModal)
        <div style="position:fixed;inset:0;background:rgba(0,0,0,0.5);display:flex;align-items:center;justify-content:center;z-index:9999;padding:16px;">
            <div style="background:#fff;border-radius:10px;width:100%;max-width:850px;max-height:90vh;overflow-y:auto;box-shadow:0 20px 25px -5px rgba(0,0,0,0.2);">
                <!-- Modal Header -->
                <div style="padding:16px 24px;border-bottom:1px solid #e2e8f0;display:flex;justify-content:space-between;align-items:center;">
                    <h3 style="margin:0;font-size:18px;font-weight:700;color:var(--mo-primary);">
                        <i class="fa-solid fa-file-signature"></i> Khởi Tạo Hợp Đồng Thuê Văn Phòng
                    </h3>
                    <button wire:click="closeCreateModal" style="background:none;border:none;font-size:18px;cursor:pointer;color:#64748b;">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <!-- Modal Body -->
                <div style="padding:20px 24px;">
                    <!-- Overlap Warning Banner -->
                    @if($overlapWarning)
                        <div style="background:#fef2f2;border:1px solid #fecaca;color:#991b1b;padding:12px 16px;border-radius:6px;margin-bottom:18px;font-size:13.5px;display:flex;gap:10px;align-items:center;">
                            <i class="fa-solid fa-triangle-exclamation" style="font-size:18px;flex-shrink:0;"></i>
                            <div><strong>Cảnh báo vi phạm:</strong> {{ $overlapWarning }}</div>
                        </div>
                    @endif

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;">
                        <!-- Loại Hợp Đồng -->
                        <div>
                            <label style="display:block;font-size:12.5px;font-weight:600;color:#334155;margin-bottom:4px;">Loại Hợp Đồng:</label>
                            <select wire:model.live="createType" style="width:100%;padding:8px 12px;border:1px solid #cbd5e1;border-radius:6px;font-size:13.5px;">
                                <option value="DRAFT">Hợp Đồng Thuê Chính Thức (DRAFT)</option>
                                <option value="BOOKING">Hợp Đồng Đặt Cọc Giữ Chỗ (BOOKING / RESERVED)</option>
                            </select>
                        </div>

                        <!-- Số Hợp Đồng -->
                        <div>
                            <label style="display:block;font-size:12.5px;font-weight:600;color:#334155;margin-bottom:4px;">Số Hợp Đồng (Mã Số):</label>
                            <input type="text" wire:model="contract_number" placeholder="Tự động sinh theo tòa nhà..."
                                style="width:100%;padding:8px 12px;border:1px solid #cbd5e1;border-radius:6px;font-size:13.5px;box-sizing:border-box;">
                        </div>
                    </div>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;">
                        <!-- Tòa Nhà -->
                        <div>
                            <label style="display:block;font-size:12.5px;font-weight:600;color:#334155;margin-bottom:4px;">Tòa Nhà Văn Phòng: <span style="color:red;">*</span></label>
                            <select wire:model.live="building_id" style="width:100%;padding:8px 12px;border:1px solid #cbd5e1;border-radius:6px;font-size:13.5px;">
                                <option value="">-- Chọn tòa nhà --</option>
                                @foreach($buildings as $b)
                                    <option value="{{ $b->id }}">{{ $b->name }} ({{ $b->building_code }})</option>
                                @endforeach
                            </select>
                            @error('building_id') <span style="color:red;font-size:12px;">{{ $message }}</span> @enderror
                        </div>

                        <!-- Khách Thuê -->
                        <div>
                            <label style="display:block;font-size:12.5px;font-weight:600;color:#334155;margin-bottom:4px;">Doanh Nghiệp Thuê: <span style="color:red;">*</span></label>
                            <select wire:model="tenant_id" style="width:100%;padding:8px 12px;border:1px solid #cbd5e1;border-radius:6px;font-size:13.5px;">
                                <option value="">-- Chọn khách thuê --</option>
                                @foreach($tenants as $t)
                                    <option value="{{ $t->id }}">{{ $t->company_name }} ({{ $t->tax_code }})</option>
                                @endforeach
                            </select>
                            @error('tenant_id') <span style="color:red;font-size:12px;">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;">
                        <!-- Ngày bắt đầu -->
                        <div>
                            <label style="display:block;font-size:12.5px;font-weight:600;color:#334155;margin-bottom:4px;">Ngày Bắt Đầu Thuê: <span style="color:red;">*</span></label>
                            <input type="date" wire:model="start_date" style="width:100%;padding:8px 12px;border:1px solid #cbd5e1;border-radius:6px;font-size:13.5px;box-sizing:border-box;">
                            @error('start_date') <span style="color:red;font-size:12px;">{{ $message }}</span> @enderror
                        </div>

                        <!-- Ngày kết thúc -->
                        <div>
                            <label style="display:block;font-size:12.5px;font-weight:600;color:#334155;margin-bottom:4px;">Ngày Kết Thúc Thuê: <span style="color:red;">*</span></label>
                            <input type="date" wire:model="end_date" style="width:100%;padding:8px 12px;border:1px solid #cbd5e1;border-radius:6px;font-size:13.5px;box-sizing:border-box;">
                            @error('end_date') <span style="color:red;font-size:12px;">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <!-- Gán Mặt Bằng & Chống Trùng Lịch -->
                    <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:16px;margin-bottom:18px;">
                        <div style="font-weight:700;color:#1e293b;margin-bottom:10px;display:flex;align-items:center;gap:8px;">
                            <i class="fa-solid fa-layer-group" style="color:var(--mo-primary);"></i> Chọn Mặt Bằng & Kiểm Tra Trùng Lịch
                        </div>

                        @if(!$building_id)
                            <div style="font-size:13px;color:#64748b;">Vui lòng chọn Tòa nhà phía trên để tải danh sách các mặt bằng còn trống.</div>
                        @else
                            <div style="display:flex;gap:10px;margin-bottom:12px;">
                                <select wire:model="selectedUnitToAdd" style="flex:1;padding:8px 12px;border:1px solid #cbd5e1;border-radius:6px;font-size:13.5px;">
                                    <option value="">-- Chọn mặt bằng còn trống --</option>
                                    @foreach($availableUnitsInBuilding as $u)
                                        <option value="{{ $u->id }}">
                                            Tầng {{ $u->floor_number }} - {{ $u->unit_code }} ({{ $u->area_sqm }} m² - {{ number_format($u->base_rental_price ?: 0, 0, ',', '.') }} đ/m²)
                                        </option>
                                    @endforeach
                                </select>
                                <button type="button" wire:click="addUnit" class="btn-mo-primary" style="padding:8px 16px;white-space:nowrap;font-size:13px;">
                                    <i class="fa-solid fa-plus"></i> Thêm Vào HĐ
                                </button>
                            </div>
                        @endif

                        <!-- Bảng các mặt bằng đã chọn -->
                        @if(!empty($selectedUnits))
                            <table style="width:100%;border-collapse:collapse;font-size:13px;background:#fff;border-radius:6px;overflow:hidden;border:1px solid #e2e8f0;">
                                <thead>
                                    <tr style="background:#f1f5f9;border-bottom:1px solid #e2e8f0;text-align:left;">
                                        <th style="padding:8px 12px;">Mã Sàn</th>
                                        <th style="padding:8px 12px;">Tầng</th>
                                        <th style="padding:8px 12px;">Diện Tích</th>
                                        <th style="padding:8px 12px;">Đơn Giá Thuê (VNĐ/m²)</th>
                                        <th style="padding:8px 12px;text-align:right;">Xóa</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($selectedUnits as $idx => $su)
                                        <tr style="border-bottom:1px solid #f1f5f9;">
                                            <td style="padding:8px 12px;"><strong>{{ $su['unit_code'] }}</strong></td>
                                            <td style="padding:8px 12px;">Tầng {{ $su['floor_number'] }}</td>
                                            <td style="padding:8px 12px;">{{ $su['leased_area_sqm'] }} m²</td>
                                            <td style="padding:8px 12px;">{{ number_format($su['agreed_rental_price_per_sqm'], 0, ',', '.') }} đ</td>
                                            <td style="padding:8px 12px;text-align:right;">
                                                <button type="button" wire:click="removeUnit({{ $idx }})" style="background:none;border:none;color:#ef4444;cursor:pointer;">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endif
                        @error('selectedUnits') <div style="color:red;font-size:12px;margin-top:6px;">{{ $message }}</div> @enderror
                    </div>

                    <!-- Tiền Cọc & Điều Khoản Thanh Toán -->
                    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;margin-bottom:16px;">
                        <div>
                            <label style="display:block;font-size:12.5px;font-weight:600;color:#334155;margin-bottom:4px;">Tiền Cọc Thuê (VNĐ):</label>
                            <input type="number" wire:model="deposit_amount" style="width:100%;padding:8px 12px;border:1px solid #cbd5e1;border-radius:6px;font-size:13.5px;box-sizing:border-box;">
                        </div>
                        @if($createType === 'BOOKING')
                            <div>
                                <label style="display:block;font-size:12.5px;font-weight:600;color:#334155;margin-bottom:4px;">Tiền Cọc Giữ Chỗ (VNĐ):</label>
                                <input type="number" wire:model="booking_deposit_amount" style="width:100%;padding:8px 12px;border:1px solid #cbd5e1;border-radius:6px;font-size:13.5px;box-sizing:border-box;">
                            </div>
                            <div>
                                <label style="display:block;font-size:12.5px;font-weight:600;color:#334155;margin-bottom:4px;">Hạn Giữ Chỗ Đến:</label>
                                <input type="datetime-local" wire:model="reservation_expires_at" style="width:100%;padding:8px 12px;border:1px solid #cbd5e1;border-radius:6px;font-size:13.5px;box-sizing:border-box;">
                            </div>
                        @else
                            <div>
                                <label style="display:block;font-size:12.5px;font-weight:600;color:#334155;margin-bottom:4px;">Kỳ Hạn Thanh Toán (Tháng):</label>
                                <input type="number" wire:model="payment_cycle_months" style="width:100%;padding:8px 12px;border:1px solid #cbd5e1;border-radius:6px;font-size:13.5px;box-sizing:border-box;">
                            </div>
                            <div>
                                <label style="display:block;font-size:12.5px;font-weight:600;color:#334155;margin-bottom:4px;">Ngày Hạn Đóng Tiền Hàng Tháng:</label>
                                <input type="number" wire:model="payment_due_day" min="1" max="28" style="width:100%;padding:8px 12px;border:1px solid #cbd5e1;border-radius:6px;font-size:13.5px;box-sizing:border-box;">
                            </div>
                        @endif
                    </div>

                    <!-- Đính kèm file scan hợp đồng / ảnh hợp đồng (Supabase Private Storage) -->
                    <div style="margin-top:16px;padding:14px;background:#f8fafc;border-radius:8px;border:1px dashed #cbd5e1;">
                        <label style="display:block;font-size:13px;font-weight:700;color:var(--mo-primary);margin-bottom:6px;">
                            <i class="fa-solid fa-file-shield" style="color:#0284c7;"></i> Đính Kèm File Scan / Ảnh Hợp Đồng (Private Bucket):
                        </label>
                        <input type="file" wire:model="newContractFiles" multiple accept="image/*,application/pdf" style="font-size:13px;color:#334155;width:100%;">
                        <div wire:loading wire:target="newContractFiles" style="font-size:12px;color:#0284c7;margin-top:4px;">
                            <i class="fa-solid fa-spinner fa-spin"></i> Đang tải tệp lên tạm thời...
                        </div>
                        <div style="font-size:11.5px;color:#64748b;margin-top:4px;">
                            Hỗ trợ PDF, PNG, JPG. Tệp được mã hóa và bảo mật tại <strong>Supabase Private Storage</strong>, chỉ xem được qua Signed URL có thời hạn.
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div style="padding:16px 24px;border-top:1px solid #e2e8f0;display:flex;justify-content:flex-end;gap:12px;">
                    <button wire:click="closeCreateModal" type="button" class="btn-mo-outline" style="padding:8px 18px;">Hủy Bỏ</button>
                    <button wire:click="saveContract" type="button" class="btn-mo-orange" style="padding:8px 20px;">
                        <i class="fa-solid fa-floppy-disk"></i> Lưu Hợp Đồng
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- MODAL 2: CHI TIẾT HỢP ĐỒNG -->
    @if($showDetailModal && $contractDetail)
        <div style="position:fixed;inset:0;background:rgba(0,0,0,0.5);display:flex;align-items:center;justify-content:center;z-index:9999;padding:16px;">
            <div style="background:#fff;border-radius:10px;width:100%;max-width:800px;max-height:90vh;overflow-y:auto;box-shadow:0 20px 25px -5px rgba(0,0,0,0.2);">
                <div style="padding:16px 24px;border-bottom:1px solid #e2e8f0;display:flex;justify-content:space-between;align-items:center;">
                    <div>
                        <h3 style="margin:0;font-size:18px;font-weight:700;color:var(--mo-primary);">
                            Chi Tiết Hợp Đồng: {{ $contractDetail->contract_number }}
                        </h3>
                        <div style="font-size:12px;color:#64748b;margin-top:2px;">
                            Tòa nhà: {{ $contractDetail->building?->name }} | Bên thuê: {{ $contractDetail->tenant?->company_name }}
                        </div>
                    </div>
                    <div style="display:flex;align-items:center;gap:10px;">
                        <a href="{{ route('contracts.print', $contractDetail->id) }}" target="_blank" class="btn-mo-orange" style="text-decoration:none;display:inline-flex;align-items:center;gap:6px;padding:6px 14px;border-radius:6px;font-size:12.5px;font-weight:600;" title="Mở trang in và Xuất PDF khổ A4">
                            <i class="fa-solid fa-file-pdf"></i> Xuất PDF
                        </a>
                        <button wire:click="closeDetailModal" style="background:none;border:none;font-size:18px;cursor:pointer;color:#64748b;">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                </div>

                <div style="padding:20px 24px;">
                    <!-- Overview Grid -->
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:18px;font-size:13.5px;">
                        <div style="background:#f8fafc;padding:12px;border-radius:6px;border:1px solid #e2e8f0;">
                            <div><strong>Đại diện pháp luật:</strong> {{ $contractDetail->tenant?->representative_name }}</div>
                            <div style="margin-top:4px;"><strong>Mã số thuế:</strong> {{ $contractDetail->tenant?->tax_code }}</div>
                            <div style="margin-top:4px;"><strong>Email nhận hóa đơn:</strong> {{ $contractDetail->tenant?->billing_email }}</div>
                            <div style="margin-top:4px;"><strong>Số điện thoại:</strong> {{ $contractDetail->tenant?->contact_phone }}</div>
                        </div>
                        <div style="background:#f8fafc;padding:12px;border-radius:6px;border:1px solid #e2e8f0;">
                            <div><strong>Thời hạn thuê:</strong> {{ \Carbon\Carbon::parse($contractDetail->start_date)->format('d/m/Y') }} đến {{ \Carbon\Carbon::parse($contractDetail->end_date)->format('d/m/Y') }}</div>
                            <div style="margin-top:4px;"><strong>Tiền cọc thuê:</strong> <span style="color:#0f3361;font-weight:700;">{{ number_format($contractDetail->deposit_amount, 0, ',', '.') }} VNĐ</span></div>
                            <div style="margin-top:4px;"><strong>Kỳ thanh toán:</strong> {{ $contractDetail->payment_cycle_months }} tháng / lần (Hạn ngày {{ $contractDetail->payment_due_day }})</div>
                            <div style="margin-top:4px;"><strong>Trạng thái:</strong> <span class="badge-filament info">{{ $contractDetail->status }}</span></div>
                        </div>
                    </div>

                    <!-- Leased Units Table -->
                    <h4 style="margin:0 0 10px 0;font-size:14px;color:#1e293b;">Danh Sách Mặt Bằng Thuê (Sàn Văn Phòng):</h4>
                    <table style="width:100%;border-collapse:collapse;font-size:13px;border:1px solid #e2e8f0;margin-bottom:18px;">
                        <thead>
                            <tr style="background:#f1f5f9;border-bottom:1px solid #e2e8f0;text-align:left;">
                                <th style="padding:8px 12px;">Mã Sàn</th>
                                <th style="padding:8px 12px;">Tên Sàn</th>
                                <th style="padding:8px 12px;">Diện Tích</th>
                                <th style="padding:8px 12px;">Giá Thuê Đã Chốt</th>
                                <th style="padding:8px 12px;">Phí Quản Lý DV</th>
                                <th style="padding:8px 12px;text-align:center;">Thao Tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($contractDetail->contractUnits as $cu)
                                <tr style="border-bottom:1px solid #f1f5f9;">
                                    <td style="padding:8px 12px;"><strong>{{ $cu->unit?->unit_code }}</strong></td>
                                    <td style="padding:8px 12px;">{{ $cu->unit?->name }} (Tầng {{ $cu->unit?->floor_number }})</td>
                                    <td style="padding:8px 12px;">{{ number_format($cu->leased_area_sqm, 0) }} m²</td>
                                    <td style="padding:8px 12px;">{{ number_format($cu->agreed_rental_price_per_sqm, 0, ',', '.') }} đ/m²</td>
                                    <td style="padding:8px 12px;">{{ number_format($cu->agreed_service_fee_per_sqm, 0, ',', '.') }} đ/m²</td>
                                    <td style="padding:8px 12px;text-align:center;">
                                        @if($cu->unit_id)
                                            <button wire:click="viewUnitDetail('{{ $cu->unit_id }}')" type="button" class="btn-mo-outline" style="padding:3px 8px;font-size:11.5px;color:#0284c7;border-color:#bae6fd;cursor:pointer;">
                                                <i class="fa-solid fa-eye"></i> Xem Mặt Bằng
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" style="text-align:center;padding:16px;color:#94a3b8;">Không có mặt bằng nào.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <!-- Tài liệu & File scan hợp đồng (Supabase Private Storage) -->
                    <div style="margin-top:16px;padding:16px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;">
                        <h4 style="margin:0 0 12px 0;font-size:14px;font-weight:700;color:var(--mo-primary);display:flex;align-items:center;gap:8px;">
                            <i class="fa-solid fa-folder-closed" style="color:#0284c7;"></i> Tài Liệu & Bản Scan Hợp Đồng (Bảo Mật Private Bucket):
                        </h4>

                        <!-- Danh sách tệp đã tải lên -->
                        @if($contractDetail->images && $contractDetail->images->count() > 0)
                            <div style="display:flex;flex-direction:column;gap:8px;margin-bottom:14px;">
                                @foreach($contractDetail->images as $img)
                                    <div style="display:flex;justify-content:space-between;align-items:center;background:#fff;padding:10px 14px;border-radius:6px;border:1px solid #e2e8f0;">
                                        <div style="display:flex;align-items:center;gap:10px;">
                                            <i class="fa-solid fa-file-pdf" style="font-size:20px;color:#ef4444;"></i>
                                            <div>
                                                <div style="font-size:13px;font-weight:600;color:#1e293b;">
                                                    {{ $img->file_name ?? basename($img->image_url) }}
                                                </div>
                                                <div style="font-size:11.5px;color:#64748b;">
                                                    Loại: <span class="badge-filament info" style="font-size:10.5px;">{{ $img->document_type }}</span>
                                                    &bull; Đường dẫn: <code style="font-size:11px;background:#f1f5f9;padding:1px 4px;border-radius:3px;">{{ $img->image_url }}</code>
                                                </div>
                                            </div>
                                        </div>
                                        <div>
                                            @if(!empty($generatedSignedUrls[$img->id]))
                                                <a href="{{ $generatedSignedUrls[$img->id] }}" target="_blank" class="btn-mo-orange" style="padding:4px 10px;font-size:12px;text-decoration:none;display:inline-flex;align-items:center;gap:4px;">
                                                    <i class="fa-solid fa-arrow-up-right-from-square"></i> Mở File (10 phút)
                                                </a>
                                            @else
                                                <button type="button" wire:click="generateDownloadUrl({{ $img->id }})" class="btn-mo-primary" style="padding:4px 10px;font-size:12px;cursor:pointer;display:inline-flex;align-items:center;gap:4px;">
                                                    <i class="fa-solid fa-key"></i> Lấy Link Tải
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div style="padding:16px;border:1px dashed #cbd5e1;border-radius:6px;text-align:center;color:#64748b;font-size:13px;margin-bottom:14px;background:#fff;">
                                <i class="fa-regular fa-folder-open" style="font-size:24px;color:#94a3b8;margin-bottom:6px;display:block;"></i>
                                Chưa có tệp scan hoặc ảnh hợp đồng nào được tải lên.
                            </div>
                        @endif

                        <!-- Form tải thêm tệp scan -->
                        <div style="display:flex;gap:10px;align-items:flex-end;background:#fff;padding:12px;border-radius:6px;border:1px solid #e2e8f0;">
                            <div style="flex:1;">
                                <label style="display:block;font-size:12px;font-weight:600;color:#334155;margin-bottom:4px;">Thêm File Scan / Ảnh Mới:</label>
                                <input type="file" wire:model="uploadContractScans" multiple accept="image/*,application/pdf" style="font-size:12.5px;color:#334155;width:100%;">
                            </div>
                            <div style="width:200px;">
                                <label style="display:block;font-size:12px;font-weight:600;color:#334155;margin-bottom:4px;">Phân Loại Tài Liệu:</label>
                                <select wire:model="documentType" style="width:100%;padding:6px 10px;border:1px solid #cbd5e1;border-radius:6px;font-size:12.5px;">
                                    <option value="SIGNED_CONTRACT_SCAN">Bản Scan Hợp Đồng</option>
                                    <option value="HANDOVER_MINUTES">Biên Bản Bàn Giao</option>
                                    <option value="ANNEX">Phụ Lục Hợp Đồng</option>
                                </select>
                            </div>
                            <div>
                                <button type="button" wire:click="uploadScans" class="btn-mo-orange" style="padding:7px 14px;font-size:12.5px;cursor:pointer;white-space:nowrap;display:inline-flex;align-items:center;gap:6px;">
                                    <i class="fa-solid fa-cloud-arrow-up"></i> Tải Lên Supabase
                                </button>
                            </div>
                        </div>
                        <div wire:loading wire:target="uploadContractScans" style="font-size:12px;color:#0284c7;margin-top:6px;">
                            <i class="fa-solid fa-spinner fa-spin"></i> Đang tải tệp lên... Vui lòng đợi!
                        </div>
                    </div>
                </div>

                <div style="padding:16px 24px;border-top:1px solid #e2e8f0;display:flex;justify-content:flex-end;">
                    <button wire:click="closeDetailModal" class="btn-mo-outline" style="padding:8px 18px;">Đóng</button>
                </div>
            </div>
        </div>
    @endif

    <!-- MODAL XEM CHI TIẾT SÀN / ẢNH MẶT BẰNG TRONG HỢP ĐỒNG -->
    @if($showUnitDetailModal && $selectedUnitDetail)
        <div style="position:fixed;inset:0;background:rgba(15,23,42,0.65);backdrop-filter:blur(3px);display:flex;align-items:center;justify-content:center;z-index:10002;padding:16px;">
            <div style="background:#ffffff;border-radius:12px;width:100%;max-width:720px;box-shadow:0 20px 25px -5px rgba(0,0,0,0.25);overflow:hidden;border:1px solid #e2e8f0;max-height:92vh;display:flex;flex-direction:column;">
                <div style="background:var(--mo-primary);color:#ffffff;padding:16px 20px;display:flex;justify-content:space-between;align-items:center;">
                    <h3 style="margin:0;font-size:16px;font-weight:700;">
                        <i class="fa-solid fa-layer-group"></i> Chi Tiết Mặt Bằng: {{ $selectedUnitDetail->unit_code }} (Tầng {{ $selectedUnitDetail->floor_number }})
                    </h3>
                    <button wire:click="closeUnitDetailModal" type="button" style="background:none;border:none;color:#ffffff;font-size:18px;cursor:pointer;">&times;</button>
                </div>
                <div style="padding:20px;overflow-y:auto;flex:1;">
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:18px;">
                        <div style="background:#f8fafc;padding:12px 16px;border-radius:8px;border:1px solid #e2e8f0;">
                            <div style="font-size:12px;color:#64748b;">Tòa nhà:</div>
                            <div style="font-size:14px;font-weight:700;color:var(--mo-primary);">{{ $selectedUnitDetail->building?->name ?? 'Tòa nhà văn phòng' }}</div>
                            <div style="font-size:12px;color:#64748b;margin-top:8px;">Diện tích thông thủy:</div>
                            <div style="font-size:16px;font-weight:700;color:#0284c7;">{{ number_format($selectedUnitDetail->area_sqm, 1) }} m²</div>
                        </div>
                        <div style="background:#f8fafc;padding:12px 16px;border-radius:8px;border:1px solid #e2e8f0;">
                            <div style="font-size:12px;color:#64748b;">Giá niêm yết gốc:</div>
                            <div style="font-size:16px;font-weight:700;color:#ea580c;">{{ number_format($selectedUnitDetail->base_rental_price ?: 0, 0, ',', '.') }} đ/m²</div>
                            <div style="font-size:12px;color:#64748b;margin-top:8px;">Hiện trạng bàn giao:</div>
                            <div style="font-size:13px;font-weight:600;color:#1e293b;">
                                @if($selectedUnitDetail->handover_condition === 'RAW') Trần sàn thô
                                @elseif($selectedUnitDetail->handover_condition === 'BASIC') Hoàn thiện cơ bản
                                @elseif($selectedUnitDetail->handover_condition === 'FULL_FURNISHED') Đầy đủ nội thất cao cấp
                                @else {{ $selectedUnitDetail->handover_condition ?: 'Hoàn thiện tiêu chuẩn' }}
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Album ảnh & Sơ đồ mặt bằng -->
                    <div style="margin-bottom:16px;">
                        <div style="font-size:13px;font-weight:700;color:#334155;margin-bottom:8px;">
                            <i class="fa-solid fa-images"></i> Album Ảnh Thực Tế & Sơ Đồ Thiết Kế Mặt Bằng:
                        </div>
                        @if($selectedUnitDetail->images && $selectedUnitDetail->images->count() > 0)
                            <div style="display:grid;grid-template-columns:repeat(3, 1fr);gap:10px;">
                                @foreach($selectedUnitDetail->images as $img)
                                    <div style="border-radius:8px;overflow:hidden;border:1px solid #e2e8f0;background:#f8fafc;">
                                        <img src="{{ $img->image_url }}" alt="Ảnh mặt bằng {{ $selectedUnitDetail->unit_code }}" style="width:100%;height:130px;object-fit:cover;display:block;">
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div style="padding:24px;border:1px dashed #cbd5e1;border-radius:8px;text-align:center;color:#64748b;background:#f8fafc;">
                                <i class="fa-regular fa-image" style="font-size:24px;margin-bottom:6px;display:block;color:#94a3b8;"></i>
                                Chưa có ảnh thực tế hoặc bản vẽ sơ đồ được tải lên cho mặt bằng này.
                            </div>
                        @endif
                    </div>
                </div>
                <div style="padding:14px 20px;border-top:1px solid #e2e8f0;display:flex;justify-content:flex-end;background:#f8fafc;">
                    <button wire:click="closeUnitDetailModal" type="button" class="btn-mo-outline" style="padding:6px 16px;">Đóng</button>
                </div>
            </div>
        </div>
    @endif

    <!-- MODAL 3: GIA HẠN HỢP ĐỒNG (RENEW) -->
    @if($showRenewModal && $renewContract)
        <div style="position:fixed;inset:0;background:rgba(0,0,0,0.5);display:flex;align-items:center;justify-content:center;z-index:9999;padding:16px;">
            <div style="background:#fff;border-radius:10px;width:100%;max-width:550px;box-shadow:0 20px 25px -5px rgba(0,0,0,0.2);">
                <div style="padding:16px 24px;border-bottom:1px solid #e2e8f0;display:flex;justify-content:space-between;align-items:center;">
                    <h3 style="margin:0;font-size:17px;font-weight:700;color:var(--mo-primary);">
                        <i class="fa-solid fa-arrows-rotate"></i> Lập Phụ Lục Gia Hạn Hợp Đồng
                    </h3>
                    <button wire:click="closeRenewModal" style="background:none;border:none;font-size:18px;cursor:pointer;color:#64748b;">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <div style="padding:20px 24px;font-size:13.5px;">
                    <p style="margin-top:0;color:#64748b;">
                        Gia hạn hợp đồng số <strong>{{ $renewContract->contract_number }}</strong> của đối tác <strong>{{ $renewContract->tenant?->company_name }}</strong>.
                    </p>

                    <div style="margin-bottom:14px;">
                        <label style="display:block;font-weight:600;margin-bottom:4px;">Số HĐ Phụ Lục Mới:</label>
                        <input type="text" wire:model="renew_contract_number" style="width:100%;padding:8px 12px;border:1px solid #cbd5e1;border-radius:6px;box-sizing:border-box;">
                    </div>

                    <div style="margin-bottom:14px;">
                        <label style="display:block;font-weight:600;margin-bottom:4px;">Ngày Hết Hạn Chu Kỳ Mới: <span style="color:red;">*</span></label>
                        <input type="date" wire:model="new_end_date" style="width:100%;padding:8px 12px;border:1px solid #cbd5e1;border-radius:6px;box-sizing:border-box;">
                        @error('new_end_date') <span style="color:red;font-size:12px;">{{ $message }}</span> @enderror
                    </div>

                    <div style="margin-bottom:14px;">
                        <label style="display:block;font-weight:600;margin-bottom:4px;">Biên Độ Tăng Giá Niêm Yết (%/năm):</label>
                        <input type="number" step="0.1" wire:model="renew_escalation_percent" style="width:100%;padding:8px 12px;border:1px solid #cbd5e1;border-radius:6px;box-sizing:border-box;">
                    </div>
                </div>

                <div style="padding:16px 24px;border-top:1px solid #e2e8f0;display:flex;justify-content:flex-end;gap:10px;">
                    <button wire:click="closeRenewModal" class="btn-mo-outline" style="padding:8px 16px;">Hủy</button>
                    <button wire:click="confirmRenew" class="btn-mo-primary" style="padding:8px 18px;">
                        <i class="fa-solid fa-check"></i> Xác Nhận Gia Hạn
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- MODAL 4: THANH LÝ HỢP ĐỒNG (TERMINATE) -->
    @if($showTerminateModal && $terminateContract)
        <div style="position:fixed;inset:0;background:rgba(0,0,0,0.5);display:flex;align-items:center;justify-content:center;z-index:9999;padding:16px;">
            <div style="background:#fff;border-radius:10px;width:100%;max-width:580px;box-shadow:0 20px 25px -5px rgba(0,0,0,0.2);">
                <div style="padding:16px 24px;border-bottom:1px solid #e2e8f0;display:flex;justify-content:space-between;align-items:center;">
                    <h3 style="margin:0;font-size:17px;font-weight:700;color:#dc2626;">
                        <i class="fa-solid fa-file-excel"></i> Thanh Lý Hợp Đồng & Hoàn Trả Mặt Bằng
                    </h3>
                    <button wire:click="closeTerminateModal" style="background:none;border:none;font-size:18px;cursor:pointer;color:#64748b;">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <div style="padding:20px 24px;font-size:13.5px;">
                    <div style="background:#fef2f2;border:1px solid #fecaca;padding:10px 14px;border-radius:6px;margin-bottom:14px;color:#991b1b;">
                        Hợp đồng: <strong>{{ $terminateContract->contract_number }}</strong>. Tổng cọc ban đầu: <strong>{{ number_format($maxDepositAllowed, 0, ',', '.') }} VNĐ</strong>.
                        Sau khi hoàn tất thanh lý, toàn bộ mặt bằng thuê sẽ được tự động giải phóng về trạng thái <strong>TRỐNG (AVAILABLE)</strong>.
                    </div>

                    <div style="margin-bottom:14px;">
                        <label style="display:block;font-weight:600;margin-bottom:4px;">Ngày Thanh Lý Nghiệm Thu: <span style="color:red;">*</span></label>
                        <input type="date" wire:model="terminated_at" style="width:100%;padding:8px 12px;border:1px solid #cbd5e1;border-radius:6px;box-sizing:border-box;">
                    </div>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px;">
                        <div>
                            <label style="display:block;font-weight:600;margin-bottom:4px;">Tiền Cọc Hoàn Trả (VNĐ):</label>
                            <input type="number" wire:model="refund_deposit_amount" style="width:100%;padding:8px 12px;border:1px solid #cbd5e1;border-radius:6px;box-sizing:border-box;">
                        </div>
                        <div>
                            <label style="display:block;font-weight:600;margin-bottom:4px;">Tiền Phạt / Khấu Trừ (VNĐ):</label>
                            <input type="number" wire:model="penalty_deposit_amount" style="width:100%;padding:8px 12px;border:1px solid #cbd5e1;border-radius:6px;box-sizing:border-box;">
                        </div>
                    </div>

                    <div style="margin-bottom:14px;">
                        <label style="display:block;font-weight:600;margin-bottom:4px;">Lý Do Thanh Lý & Ghi Chú: <span style="color:red;">*</span></label>
                        <textarea wire:model="termination_note" rows="3" placeholder="Nhập lý do thanh lý, tình trạng bàn giao mặt bằng..."
                            style="width:100%;padding:8px 12px;border:1px solid #cbd5e1;border-radius:6px;box-sizing:border-box;"></textarea>
                        @error('termination_note') <span style="color:red;font-size:12px;">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div style="padding:16px 24px;border-top:1px solid #e2e8f0;display:flex;justify-content:flex-end;gap:10px;">
                    <button wire:click="closeTerminateModal" class="btn-mo-outline" style="padding:8px 16px;">Hủy</button>
                    <button wire:click="confirmTerminate" class="btn-mo-primary" style="background:#dc2626;border-color:#dc2626;padding:8px 18px;">
                        <i class="fa-solid fa-triangle-exclamation"></i> Xác Nhận Thanh Lý
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
