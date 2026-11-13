<div>
    <!-- Header & Action Bar -->
    <div class="filament-page-header" style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:12px;">
        <div>
            <div class="filament-breadcrumbs" style="font-size:12px;color:#64748b;margin-bottom:4px;">
                Quản Trị Doanh Nghiệp / Danh Sách Khách Thuê
            </div>
            <h1 class="filament-page-title" style="font-size:22px;font-weight:700;color:var(--mo-primary);margin:0;">
                <i class="fa-solid fa-building-user"></i> Quản Lý Hồ Sơ Khách Thuê Doanh Nghiệp
            </h1>
        </div>
        <div>
            <button wire:click="openCreateModal" class="btn-mo-orange" style="cursor:pointer;display:inline-flex;align-items:center;gap:6px;padding:8px 16px;border-radius:6px;font-weight:600;">
                <i class="fa-solid fa-plus"></i> Thêm Khách Thuê Mới
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
        <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(240px, 1fr));gap:14px;align-items:end;">
            <div>
                <label style="display:block;font-size:12px;font-weight:600;color:#475569;margin-bottom:6px;">Tìm kiếm khách thuê:</label>
                <div style="position:relative;">
                    <i class="fa-solid fa-magnifying-glass" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:#94a3b8;font-size:13px;"></i>
                    <input type="text" wire:model.live.debounce.300ms="searchKeyword" placeholder="Nhập Tên công ty, MST, Mã khách, Người đại diện..."
                        style="width:100%;padding:8px 12px 8px 32px;border:1px solid #cbd5e1;border-radius:6px;font-size:13px;box-sizing:border-box;">
                </div>
            </div>

            <div>
                <label style="display:block;font-size:12px;font-weight:600;color:#475569;margin-bottom:6px;">Trạng thái tài khoản:</label>
                <select wire:model.live="selectedStatus" style="width:100%;padding:8px 12px;border:1px solid #cbd5e1;border-radius:6px;font-size:13px;">
                    <option value="all">Tất cả trạng thái</option>
                    <option value="ACTIVE">ACTIVE (Đang hoạt động)</option>
                    <option value="LOCKED">LOCKED (Bị khóa / Vô hiệu hóa)</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Tenants Table -->
    <div class="filament-card" style="background:#fff;border-radius:8px;border:1px solid #e2e8f0;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,0.05);">
        <div style="overflow-x:auto;">
            <table class="mo-table" style="width:100%;border-collapse:collapse;text-align:left;font-size:13.5px;">
                <thead>
                    <tr style="background:#f8fafc;border-bottom:1px solid #e2e8f0;color:#475569;font-weight:600;">
                        <th style="padding:12px 16px;">Mã Khách</th>
                        <th style="padding:12px 16px;">Tên Doanh Nghiệp</th>
                        <th style="padding:12px 16px;">Mã Số Thuế</th>
                        <th style="padding:12px 16px;">Đại Diện Pháp Luật</th>
                        <th style="padding:12px 16px;">Liên Hệ & Hóa Đơn</th>
                        <th style="padding:12px 16px;">Số HĐ Thuê</th>
                        <th style="padding:12px 16px;">Trạng Thái</th>
                        <th style="padding:12px 16px;text-align:right;">Thao Tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tenants as $t)
                        <tr style="border-bottom:1px solid #f1f5f9;transition:background 0.15s ease;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
                            <td style="padding:14px 16px;">
                                <strong style="font-family:monospace;color:var(--mo-primary);font-size:13px;">{{ $t->tenant_code }}</strong>
                            </td>
                            <td style="padding:14px 16px;">
                                <div style="font-weight:700;color:#0f172a;">{{ $t->company_name }}</div>
                                <div style="font-size:11.5px;color:#64748b;max-width:260px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                    <i class="fa-solid fa-location-dot"></i> {{ $t->headquarter_address }}
                                </div>
                            </td>
                            <td style="padding:14px 16px;">
                                <span style="font-family:monospace;font-size:12.5px;background:#f1f5f9;padding:2px 6px;border-radius:4px;">{{ $t->tax_code }}</span>
                            </td>
                            <td style="padding:14px 16px;">
                                <div>{{ $t->representative_name }}</div>
                                <div style="font-size:11.5px;color:#64748b;">{{ $t->representative_position ?? 'Đại diện' }}</div>
                            </td>
                            <td style="padding:14px 16px;font-size:12.5px;">
                                <div><i class="fa-regular fa-envelope" style="color:#64748b;"></i> {{ $t->login_email }}</div>
                                <div style="color:#64748b;"><i class="fa-solid fa-phone" style="font-size:11px;"></i> {{ $t->contact_phone }}</div>
                            </td>
                            <td style="padding:14px 16px;text-align:center;">
                                <span style="display:inline-block;padding:2px 8px;border-radius:9999px;background:#e0f2fe;color:#0369a1;font-weight:700;font-size:12px;">
                                    {{ $t->contracts_count }} HĐ
                                </span>
                            </td>
                            <td style="padding:14px 16px;">
                                <span class="badge-filament {{ $t->status === 'ACTIVE' ? 'success' : 'danger' }}" style="display:inline-block;padding:4px 8px;border-radius:4px;font-size:11.5px;font-weight:600;">
                                    {{ $t->status === 'ACTIVE' ? 'Hoạt động' : 'Bị khóa' }}
                                </span>
                            </td>
                            <td style="padding:14px 16px;text-align:right;">
                                <div style="display:inline-flex;gap:6px;">
                                    <button wire:click="openDetailModal('{{ $t->id }}')" class="btn-mo-outline" style="padding:5px 8px;font-size:12px;" title="Xem hồ sơ">
                                        <i class="fa-solid fa-eye"></i>
                                    </button>
                                    <button wire:click="openEditModal('{{ $t->id }}')" class="btn-mo-outline" style="padding:5px 8px;font-size:12px;color:#2563eb;border-color:#bfdbfe;" title="Chỉnh sửa">
                                        <i class="fa-solid fa-pen"></i>
                                    </button>
                                    @if($t->status === 'ACTIVE')
                                        <button wire:click="deactivateTenant('{{ $t->id }}')" 
                                            wire:confirm="Bạn có chắc chắn muốn vô hiệu hóa khách thuê này? Hành động này sẽ bị chặn nếu còn hợp đồng hoặc công nợ chưa giải quyết."
                                            class="btn-mo-outline" style="padding:5px 8px;font-size:12px;color:#dc2626;border-color:#fca5a5;" title="Khóa tài khoản">
                                            <i class="fa-solid fa-lock"></i>
                                        </button>
                                    @else
                                        <button wire:click="activateTenant('{{ $t->id }}')" class="btn-mo-outline" style="padding:5px 8px;font-size:12px;color:#10b981;border-color:#a7f3d0;" title="Mở khóa tài khoản">
                                            <i class="fa-solid fa-lock-open"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align:center;padding:40px 20px;color:#94a3b8;">
                                <i class="fa-solid fa-building-circle-xmark" style="font-size:36px;margin-bottom:12px;display:block;"></i>
                                <span style="font-size:15px;font-weight:500;">Không có dữ liệu khách thuê phù hợp.</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($tenants->hasPages())
            <div style="padding:14px 20px;border-top:1px solid #e2e8f0;">
                {{ $tenants->links() }}
            </div>
        @endif
    </div>

    <!-- MODAL 1: TẠO MỚI / CHỈNH SỬA HỒ SƠ KHÁCH THUÊ -->
    @if($showFormModal)
        <div style="position:fixed;inset:0;background:rgba(0,0,0,0.5);display:flex;align-items:center;justify-content:center;z-index:9999;padding:16px;">
            <div style="background:#fff;border-radius:10px;width:100%;max-width:700px;max-height:90vh;overflow-y:auto;box-shadow:0 20px 25px -5px rgba(0,0,0,0.2);">
                <div style="padding:16px 24px;border-bottom:1px solid #e2e8f0;display:flex;justify-content:space-between;align-items:center;">
                    <h3 style="margin:0;font-size:18px;font-weight:700;color:var(--mo-primary);">
                        <i class="fa-solid fa-building"></i> {{ $isEditing ? 'Cập Nhật Hồ Sơ Khách Thuê' : 'Thêm Mới Hồ Sơ Khách Thuê Doanh Nghiệp' }}
                    </h3>
                    <button wire:click="closeFormModal" style="background:none;border:none;font-size:18px;cursor:pointer;color:#64748b;">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <div style="padding:20px 24px;">
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:14px;">
                        <div>
                            <label style="display:block;font-size:12.5px;font-weight:600;color:#334155;margin-bottom:4px;">Mã Khách Thuê:</label>
                            <input type="text" wire:model="tenant_code" {{ $isEditing ? 'readonly' : '' }}
                                style="width:100%;padding:8px 12px;border:1px solid #cbd5e1;border-radius:6px;font-size:13.5px;box-sizing:border-box;background:{{ $isEditing ? '#f8fafc' : '#fff' }};">
                        </div>
                        <div>
                            <label style="display:block;font-size:12.5px;font-weight:600;color:#334155;margin-bottom:4px;">Mã Số Thuế (MST): <span style="color:red;">*</span></label>
                            <input type="text" wire:model="tax_code" placeholder="VD: 0303885555"
                                style="width:100%;padding:8px 12px;border:1px solid #cbd5e1;border-radius:6px;font-size:13.5px;box-sizing:border-box;">
                            @error('tax_code') <span style="color:red;font-size:12px;">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div style="margin-bottom:14px;">
                        <label style="display:block;font-size:12.5px;font-weight:600;color:#334155;margin-bottom:4px;">Tên Công Ty / Doanh Nghiệp: <span style="color:red;">*</span></label>
                        <input type="text" wire:model="company_name" placeholder="VD: CÔNG TY CỔ PHẦN CÔNG NGHỆ VNG"
                            style="width:100%;padding:8px 12px;border:1px solid #cbd5e1;border-radius:6px;font-size:13.5px;box-sizing:border-box;">
                        @error('company_name') <span style="color:red;font-size:12px;">{{ $message }}</span> @enderror
                    </div>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:14px;">
                        <div>
                            <label style="display:block;font-size:12.5px;font-weight:600;color:#334155;margin-bottom:4px;">Người Đại Diện Pháp Luật: <span style="color:red;">*</span></label>
                            <input type="text" wire:model="representative_name" placeholder="VD: Lê Hồng Minh"
                                style="width:100%;padding:8px 12px;border:1px solid #cbd5e1;border-radius:6px;font-size:13.5px;box-sizing:border-box;">
                            @error('representative_name') <span style="color:red;font-size:12px;">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label style="display:block;font-size:12.5px;font-weight:600;color:#334155;margin-bottom:4px;">Chức Vụ Đại Diện:</label>
                            <input type="text" wire:model="representative_position" placeholder="VD: Tổng Giám Đốc"
                                style="width:100%;padding:8px 12px;border:1px solid #cbd5e1;border-radius:6px;font-size:13.5px;box-sizing:border-box;">
                        </div>
                    </div>

                    <div style="margin-bottom:14px;">
                        <label style="display:block;font-size:12.5px;font-weight:600;color:#334155;margin-bottom:4px;">Địa Chỉ Trụ Sở ĐKKD: <span style="color:red;">*</span></label>
                        <input type="text" wire:model="headquarter_address" placeholder="VD: Tòa nhà Z06, Đường 13, KCX Tân Thuận, Quận 7, TP.HCM"
                            style="width:100%;padding:8px 12px;border:1px solid #cbd5e1;border-radius:6px;font-size:13.5px;box-sizing:border-box;">
                        @error('headquarter_address') <span style="color:red;font-size:12px;">{{ $message }}</span> @enderror
                    </div>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:14px;">
                        <div>
                            <label style="display:block;font-size:12.5px;font-weight:600;color:#334155;margin-bottom:4px;">Email Đăng Nhập Portal: <span style="color:red;">*</span></label>
                            <input type="email" wire:model="login_email" placeholder="contact@vng.com.vn"
                                style="width:100%;padding:8px 12px;border:1px solid #cbd5e1;border-radius:6px;font-size:13.5px;box-sizing:border-box;">
                            @error('login_email') <span style="color:red;font-size:12px;">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label style="display:block;font-size:12.5px;font-weight:600;color:#334155;margin-bottom:4px;">Email Nhận Thông Báo Cước: <span style="color:red;">*</span></label>
                            <input type="email" wire:model="billing_email" placeholder="accounting@vng.com.vn"
                                style="width:100%;padding:8px 12px;border:1px solid #cbd5e1;border-radius:6px;font-size:13.5px;box-sizing:border-box;">
                            @error('billing_email') <span style="color:red;font-size:12px;">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:14px;">
                        <div>
                            <label style="display:block;font-size:12.5px;font-weight:600;color:#334155;margin-bottom:4px;">Số Điện Thoại Liên Hệ: <span style="color:red;">*</span></label>
                            <input type="text" wire:model="contact_phone" placeholder="0909123456"
                                style="width:100%;padding:8px 12px;border:1px solid #cbd5e1;border-radius:6px;font-size:13.5px;box-sizing:border-box;">
                            @error('contact_phone') <span style="color:red;font-size:12px;">{{ $message }}</span> @enderror
                        </div>
                        @if(!$isEditing)
                            <div>
                                <label style="display:block;font-size:12.5px;font-weight:600;color:#334155;margin-bottom:4px;">Mật Khẩu Khởi Tạo:</label>
                                <input type="text" wire:model="password"
                                    style="width:100%;padding:8px 12px;border:1px solid #cbd5e1;border-radius:6px;font-size:13.5px;box-sizing:border-box;font-family:monospace;">
                            </div>
                        @endif
                    </div>
                </div>

                <div style="padding:16px 24px;border-top:1px solid #e2e8f0;display:flex;justify-content:flex-end;gap:12px;">
                    <button wire:click="closeFormModal" type="button" class="btn-mo-outline" style="padding:8px 18px;">Hủy Bỏ</button>
                    <button wire:click="saveTenant" type="button" class="btn-mo-orange" style="padding:8px 20px;">
                        <i class="fa-solid fa-floppy-disk"></i> {{ $isEditing ? 'Lưu Thay Đổi' : 'Tạo Khách Thuê' }}
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- MODAL 2: CHI TIẾT HỒ SƠ DOANH NGHIỆP & LỊCH SỬ HỢP ĐỒNG -->
    @if($showDetailModal && $selectedTenant)
        <div style="position:fixed;inset:0;background:rgba(0,0,0,0.5);display:flex;align-items:center;justify-content:center;z-index:9999;padding:16px;">
            <div style="background:#fff;border-radius:10px;width:100%;max-width:800px;max-height:90vh;overflow-y:auto;box-shadow:0 20px 25px -5px rgba(0,0,0,0.2);">
                <div style="padding:16px 24px;border-bottom:1px solid #e2e8f0;display:flex;justify-content:space-between;align-items:center;">
                    <div>
                        <h3 style="margin:0;font-size:18px;font-weight:700;color:var(--mo-primary);">
                            {{ $selectedTenant->company_name }}
                        </h3>
                        <div style="font-size:12px;color:#64748b;margin-top:2px;">Mã khách: {{ $selectedTenant->tenant_code }} | MST: {{ $selectedTenant->tax_code }}</div>
                    </div>
                    <button wire:click="closeDetailModal" style="background:none;border:none;font-size:18px;cursor:pointer;color:#64748b;">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <div style="padding:20px 24px;">
                    <div style="background:#f8fafc;padding:16px;border-radius:8px;border:1px solid #e2e8f0;margin-bottom:20px;font-size:13.5px;">
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                            <div><strong>Người đại diện:</strong> {{ $selectedTenant->representative_name }} ({{ $selectedTenant->representative_position }})</div>
                            <div><strong>Số điện thoại:</strong> {{ $selectedTenant->contact_phone }}</div>
                            <div><strong>Email đăng nhập:</strong> {{ $selectedTenant->login_email }}</div>
                            <div><strong>Email nhận cước:</strong> {{ $selectedTenant->billing_email }}</div>
                            <div style="grid-column:span 2;"><strong>Trụ sở:</strong> {{ $selectedTenant->headquarter_address }}</div>
                        </div>
                    </div>

                    <h4 style="margin:0 0 10px 0;font-size:15px;color:#1e293b;">Các Hợp Đồng Thuê Của Doanh Nghiệp:</h4>
                    <table style="width:100%;border-collapse:collapse;font-size:13px;border:1px solid #e2e8f0;">
                        <thead>
                            <tr style="background:#f1f5f9;border-bottom:1px solid #e2e8f0;text-align:left;">
                                <th style="padding:8px 12px;">Số HĐ</th>
                                <th style="padding:8px 12px;">Tòa Nhà</th>
                                <th style="padding:8px 12px;">Diện Tích</th>
                                <th style="padding:8px 12px;">Thời Hạn</th>
                                <th style="padding:8px 12px;">Trạng Thái</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($selectedTenant->contracts as $c)
                                <tr style="border-bottom:1px solid #f1f5f9;">
                                    <td style="padding:8px 12px;"><strong>{{ $c->contract_number }}</strong></td>
                                    <td style="padding:8px 12px;">{{ $c->building?->name }}</td>
                                    <td style="padding:8px 12px;">{{ number_format($c->total_leased_area_sqm, 0) }} m²</td>
                                    <td style="padding:8px 12px;">{{ \Carbon\Carbon::parse($c->start_date)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($c->end_date)->format('d/m/Y') }}</td>
                                    <td style="padding:8px 12px;">
                                        <span class="badge-filament {{ $c->status === 'ACTIVE' ? 'success' : 'info' }}">{{ $c->status }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" style="text-align:center;padding:16px;color:#94a3b8;">Khách thuê chưa ký hợp đồng nào.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div style="padding:16px 24px;border-top:1px solid #e2e8f0;display:flex;justify-content:flex-end;">
                    <button wire:click="closeDetailModal" class="btn-mo-outline" style="padding:8px 18px;">Đóng</button>
                </div>
            </div>
        </div>
    @endif
</div>
