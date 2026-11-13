<div>
  <!-- Header Bar -->
  <div class="filament-page-header" style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px;">
    <div>
      <div class="filament-breadcrumbs">Phân hệ Kinh doanh / Tiếp nhận & Quản trị Lead</div>
      <h1 class="filament-page-title" style="margin-top: 4px;">Quản Lý Phễu Bán Hàng & Chăm Sóc Khách Hàng (CRM)</h1>
    </div>

    <div style="display: flex; gap: 10px; align-items: center;">
      <!-- Toggle Chế độ xem: Kanban vs Table -->
      <div style="background: #e2e8f0; padding: 3px; border-radius: 8px; display: inline-flex;">
        <button type="button" wire:click="setViewMode('kanban')" style="border:none; padding:6px 14px; border-radius:6px; font-size:13px; font-weight:600; cursor:pointer; background: {{ $viewMode === 'kanban' ? '#fff' : 'transparent' }}; color: {{ $viewMode === 'kanban' ? 'var(--mo-primary)' : '#64748b' }}; box-shadow: {{ $viewMode === 'kanban' ? 'var(--shadow-sm)' : 'none' }};">
          <i class="fa-solid fa-table-columns"></i> Kanban Phễu
        </button>
        <button type="button" wire:click="setViewMode('table')" style="border:none; padding:6px 14px; border-radius:6px; font-size:13px; font-weight:600; cursor:pointer; background: {{ $viewMode === 'table' ? '#fff' : 'transparent' }}; color: {{ $viewMode === 'table' ? 'var(--mo-primary)' : '#64748b' }}; box-shadow: {{ $viewMode === 'table' ? 'var(--shadow-sm)' : 'none' }};">
          <i class="fa-solid fa-list-ul"></i> Danh Sách Bảng
        </button>
      </div>

      <button type="button" class="btn-mo-orange" wire:click="openCreateLead">
        <i class="fa-solid fa-user-plus"></i> Nhập Lead Thủ Công
      </button>
    </div>
  </div>

  <!-- Flash Messages -->
  @if($flashSuccess)
    <div style="background:#f0fdf4; border:1px solid #bbf7d0; color:#15803d; padding:12px 18px; border-radius:8px; margin-bottom:16px; display:flex; justify-content:space-between; align-items:center;">
      <div><i class="fa-solid fa-circle-check" style="margin-right:8px;"></i> {{ $flashSuccess }}</div>
      <button type="button" wire:click="clearFlash" style="background:none; border:none; color:#15803d; cursor:pointer; font-size:16px;"><i class="fa-solid fa-xmark"></i></button>
    </div>
  @endif

  @if($flashError)
    <div style="background:#fef2f2; border:1px solid #fecaca; color:#b91c1c; padding:12px 18px; border-radius:8px; margin-bottom:16px; display:flex; justify-content:space-between; align-items:center;">
      <div><i class="fa-solid fa-circle-exclamation" style="margin-right:8px;"></i> {{ $flashError }}</div>
      <button type="button" wire:click="clearFlash" style="background:none; border:none; color:#b91c1c; cursor:pointer; font-size:16px;"><i class="fa-solid fa-xmark"></i></button>
    </div>
  @endif

  <!-- Bộ lọc Reactive -->
  <div class="filament-card" style="padding: 16px; margin-bottom: 20px;">
    <div style="display: grid; grid-template-columns: 2fr 1.5fr {{ $viewMode === 'table' ? '1fr' : '' }} auto; gap: 14px; align-items: center;">
      <!-- Tìm kiếm từ khóa -->
      <div style="position: relative;">
        <i class="fa-solid fa-magnifying-glass" style="position:absolute; left:12px; top:50%; transform:translateY(-50%); color:#94a3b8;"></i>
        <input type="text" wire:model.live.debounce.300ms="searchKeyword" placeholder="Tìm theo tên khách, công ty, SĐT..." style="width:100%; padding:9px 12px 9px 36px; border:1px solid #cbd5e1; border-radius:6px; font-size:13.5px;">
      </div>

      <!-- Lọc theo Tòa nhà -->
      <div>
        <select wire:model.live="selectedBuildingId" style="width:100%; padding:9px 12px; border:1px solid #cbd5e1; border-radius:6px; font-size:13.5px;">
          <option value="all">-- Tất cả Tòa nhà --</option>
          @foreach($buildings as $b)
            <option value="{{ $b->id }}">{{ $b->name }}</option>
          @endforeach
        </select>
      </div>

      <!-- Lọc theo Trạng thái (chỉ hiện khi xem Bảng) -->
      @if($viewMode === 'table')
        <div>
          <select wire:model.live="selectedStatus" style="width:100%; padding:9px 12px; border:1px solid #cbd5e1; border-radius:6px; font-size:13.5px;">
            <option value="all">-- Tất cả trạng thái --</option>
            <option value="NEW">Mới tiếp nhận</option>
            <option value="PENDING_ASSIGN">Chờ phân bổ</option>
            <option value="NEGOTIATING">Đang đàm phán</option>
            <option value="WON">Chốt thành công</option>
            <option value="LOST">Thất bại</option>
          </select>
        </div>
      @endif

      <!-- Thống kê nhanh -->
      <div style="display: flex; gap: 10px;">
        <span class="badge-filament info" style="font-size: 12.5px; padding: 6px 12px;">
          <i class="fa-solid fa-users"></i> Đang chăm sóc: <strong>{{ $totalActiveLeads }}</strong>
        </span>
        <span class="badge-filament success" style="font-size: 12.5px; padding: 6px 12px;">
          <i class="fa-solid fa-handshake"></i> Đã chốt: <strong>{{ $totalWonLeads }}</strong>
        </span>
      </div>
    </div>
  </div>

  <!-- CHẾ ĐỘ 1: KANBAN PIPELINE -->
  @if($viewMode === 'kanban')
    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px;">
      @foreach($kanbanColumns as $colKey => $col)
        <div style="background: #f1f5f9; border-radius: 10px; padding: 14px; min-height: 480px; display: flex; flex-direction: column;">
          <!-- Cột Header -->
          <div style="font-weight: 700; font-size: 13.5px; color: #334155; margin-bottom: 12px; display: flex; justify-content: space-between; align-items: center;">
            <span>{{ $col['title'] }}</span>
            <span class="badge-filament {{ $col['badge'] }}">{{ $col['leads']->count() }}</span>
          </div>

          <!-- Danh sách Card Lead trong cột -->
          <div style="flex: 1; display: flex; flex-direction: column; gap: 10px;">
            @forelse($col['leads'] as $l)
              <div style="background: #fff; padding: 12px; border-radius: 8px; border: 1px solid #cbd5e1; box-shadow: var(--shadow-sm); position: relative;" wire:key="lead-card-{{ $l->id }}">
                <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                  <div style="font-weight: 700; font-size: 14px; color: var(--mo-primary); cursor: pointer;" wire:click="openDetail('{{ $l->id }}')">
                    {{ $l->company_name ?: $l->contact_full_name }}
                  </div>
                  <span class="badge-filament {{ is_object($l->status) ? $l->status->badgeColor() : 'info' }}" style="font-size: 11px;">
                    {{ is_object($l->status) ? $l->status->label() : $l->status }}
                  </span>
                </div>

                <div style="font-size: 12.5px; color: #475569; margin: 6px 0;">
                  <i class="fa-regular fa-user" style="color: #94a3b8; width: 14px;"></i> {{ $l->contact_full_name }} ({{ $l->phone }})
                </div>

                <div style="font-size: 12px; color: #64748b; margin-bottom: 8px;">
                  <i class="fa-regular fa-building" style="color: #ea580c; width: 14px;"></i> {{ $l->building?->name ?? 'Tòa nhà văn phòng' }} - <strong>{{ number_format($l->desired_area_sqm ?? 0) }} m²</strong>
                </div>

                <!-- Sales phụ trách & SLA -->
                <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px dashed #e2e8f0; padding-top: 8px; font-size: 11.5px; color: #64748b;">
                  <div>
                    <i class="fa-solid fa-headset" style="color: #3b82f6;"></i>
                    {{ $l->assignedSales?->full_name ?? 'Chưa gán Sales' }}
                  </div>
                  <div>
                    {{ $l->created_at->format('d/m') }}
                  </div>
                </div>

                <!-- Nút thao tác nhanh trên Card -->
                <div style="display: flex; gap: 6px; margin-top: 8px; padding-top: 8px; border-top: 1px solid #f1f5f9;">
                  <button type="button" class="btn-mo-outline" style="padding: 3px 8px; font-size: 11px; flex: 1; justify-content: center;" wire:click="openDetail('{{ $l->id }}')">
                    <i class="fa-solid fa-eye"></i> Chi tiết
                  </button>

                  @if($l->status !== \App\Enums\LeadStatus::WON)
                    @if($l->status === \App\Enums\LeadStatus::NEW)
                      <button type="button" class="btn-mo-orange" style="padding: 3px 8px; font-size: 11px;" wire:click="updateStatus('{{ $l->id }}', 'NEGOTIATING')" title="Bắt đầu chăm sóc / Đàm phán">
                        <i class="fa-solid fa-phone"></i>
                      </button>
                    @endif

                    <button type="button" class="btn-mo-outline" style="padding: 3px 8px; font-size: 11px;" wire:click="openAssign('{{ $l->id }}')" title="Gán / Chuyển Sales">
                      <i class="fa-solid fa-user-gear"></i>
                    </button>

                    <button type="button" class="btn-mo-primary" style="padding: 3px 8px; font-size: 11px;" wire:click="openConvert('{{ $l->id }}')" title="Chốt hợp đồng / Chuyển Tenant">
                      <i class="fa-solid fa-award"></i>
                    </button>

                    <button type="button" style="border:1px solid #fecaca; background:#fff; color:#ef4444; padding:3px 8px; border-radius:6px; font-size:11px; cursor:pointer;" wire:click="updateStatus('{{ $l->id }}', 'LOST')" title="Đóng Thất bại">
                      <i class="fa-solid fa-ban"></i>
                    </button>
                  @endif
                </div>
              </div>
            @empty
              <div style="color: #94a3b8; font-size: 12.5px; text-align: center; padding: 24px 10px; background: #fff; border-radius: 8px; border: 1px dashed #cbd5e1;">
                Không có lead nào trong giai đoạn này
              </div>
            @endforelse
          </div>
        </div>
      @endforeach
    </div>
  @endif

  <!-- CHẾ ĐỘ 2: BẢNG DANH SÁCH (TABLE VIEW) -->
  @if($viewMode === 'table')
    <div class="filament-card">
      <table class="mo-table">
        <thead>
          <tr>
            <th>Ngày Gửi</th>
            <th>Khách Hàng / SĐT</th>
            <th>Doanh Nghiệp</th>
            <th>Tòa Nhà Quan Tâm</th>
            <th>DT Dự Kiến</th>
            <th>Sales Phụ Trách</th>
            <th>Giai Đoạn Pipeline</th>
            <th style="text-align: right;">Hành Động</th>
          </tr>
        </thead>
        <tbody>
          @forelse($paginatedLeads as $lead)
            <tr wire:key="lead-row-{{ $lead->id }}">
              <td style="font-size: 12.5px; color: #64748b;">
                {{ $lead->created_at->format('d/m/Y H:i') }}
              </td>
              <td>
                <div style="font-weight: 700; color: var(--mo-primary); cursor: pointer;" wire:click="openDetail('{{ $lead->id }}')">
                  {{ $lead->contact_full_name }}
                </div>
                <div style="font-size: 12px; color: #64748b;">
                  <i class="fa-solid fa-phone" style="font-size: 10px;"></i> {{ $lead->phone }}
                </div>
              </td>
              <td>{{ $lead->company_name ?: '---' }}</td>
              <td>
                <span style="color: var(--mo-primary); font-weight: 600;">
                  {{ $lead->building?->name ?? '---' }}
                </span>
              </td>
              <td>{{ $lead->desired_area_sqm ? number_format($lead->desired_area_sqm, 0) . ' m²' : '---' }}</td>
              <td>
                <span style="font-size: 12.5px;">
                  {{ $lead->assignedSales?->full_name ?? 'Chưa phân bổ' }}
                </span>
              </td>
              <td>
                <span class="badge-filament {{ is_object($lead->status) ? $lead->status->badgeColor() : 'info' }}">
                  {{ is_object($lead->status) ? $lead->status->label() : $lead->status }}
                </span>
              </td>
              <td style="text-align: right;">
                <div style="display: inline-flex; gap: 6px;">
                  <button type="button" class="btn-mo-outline" style="padding: 4px 8px; font-size: 12px;" wire:click="openDetail('{{ $lead->id }}')">
                    <i class="fa-solid fa-eye"></i> Xem
                  </button>
                  <button type="button" class="btn-mo-outline" style="padding: 4px 8px; font-size: 12px;" wire:click="openAssign('{{ $lead->id }}')">
                    <i class="fa-solid fa-user-gear"></i> Gán
                  </button>
                  @if($lead->status !== \App\Enums\LeadStatus::WON)
                    <button type="button" class="btn-mo-orange" style="padding: 4px 8px; font-size: 12px;" wire:click="openConvert('{{ $lead->id }}')">
                      <i class="fa-solid fa-award"></i> Chốt HĐ
                    </button>
                  @endif
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="8" style="text-align: center; padding: 36px; color: #94a3b8;">
                <i class="fa-solid fa-inbox" style="font-size: 32px; margin-bottom: 8px; display: block;"></i>
                Không tìm thấy khách hàng tiềm năng nào phù hợp với bộ lọc.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>

      <!-- Phân trang Maison Office -->
      <div style="padding: 12px 18px;">
        {{ $paginatedLeads->links() }}
      </div>
    </div>
  @endif

  <!-- DRAWER / MODAL: HỒ SƠ CHI TIẾT LEAD & ACTIVITY TIMELINE -->
  @if($showDetailDrawer && $selectedLead)
    <div class="mo-modal show" style="display: flex;">
      <div class="mo-modal-box" style="max-width: 840px; width: 90%;">
        <div class="mo-modal-head">
          <div>
            <span class="badge-filament {{ is_object($selectedLead->status) ? $selectedLead->status->badgeColor() : 'info' }}" style="margin-bottom: 4px;">
              {{ is_object($selectedLead->status) ? $selectedLead->status->label() : $selectedLead->status }}
            </span>
            <h3 style="font-size: 20px; font-weight: 800; color: var(--mo-primary); margin-top: 4px;">
              {{ $selectedLead->contact_full_name }} {{ $selectedLead->company_name ? " - " . $selectedLead->company_name : "" }}
            </h3>
          </div>
          <i class="fa-solid fa-xmark" style="cursor: pointer; font-size: 22px; color: #94a3b8;" wire:click="closeDetail"></i>
        </div>

        <div class="mo-modal-body" style="max-height: 75vh; overflow-y: auto;">
          <!-- Thông tin tổng quan Grid -->
          <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 16px; margin-bottom: 20px;">
            <div>
              <div style="font-size: 11.5px; color: #64748b; text-transform: uppercase;">Số điện thoại</div>
              <div style="font-weight: 700; color: var(--mo-primary); font-size: 14px;">{{ $selectedLead->phone }}</div>
            </div>
            <div>
              <div style="font-size: 11.5px; color: #64748b; text-transform: uppercase;">Email</div>
              <div style="font-weight: 600; color: #334155; font-size: 13.5px;">{{ $selectedLead->email }}</div>
            </div>
            <div>
              <div style="font-size: 11.5px; color: #64748b; text-transform: uppercase;">Nguồn tiếp nhận</div>
              <div style="font-weight: 700; color: #ea580c; font-size: 13px;">{{ $selectedLead->lead_source }}</div>
            </div>
            <div>
              <div style="font-size: 11.5px; color: #64748b; text-transform: uppercase;">Tòa nhà quan tâm</div>
              <div style="font-weight: 700; color: var(--mo-primary); font-size: 13.5px;">{{ $selectedLead->building?->name ?? 'Chưa xác định' }}</div>
            </div>
            <div>
              <div style="font-size: 11.5px; color: #64748b; text-transform: uppercase;">Diện tích cần thuê</div>
              <div style="font-weight: 700; color: #0284c7; font-size: 14px;">{{ $selectedLead->desired_area_sqm ? number_format($selectedLead->desired_area_sqm, 0) . ' m²' : 'Chưa rõ' }}</div>
            </div>
            <div>
              <div style="font-size: 11.5px; color: #64748b; text-transform: uppercase;">Sales phụ trách</div>
              <div style="font-weight: 700; color: #15803d; font-size: 13.5px;">{{ $selectedLead->assignedSales?->full_name ?? 'Chưa phân bổ' }}</div>
            </div>
          </div>

          @if($selectedLead->customer_note)
            <div style="background: #fffbeb; border: 1px solid #fef3c7; border-radius: 6px; padding: 12px; margin-bottom: 20px; font-size: 13.5px; color: #92400e;">
              <strong><i class="fa-solid fa-message"></i> Ghi chú của khách:</strong> {{ $selectedLead->customer_note }}
            </div>
          @endif

          <!-- Thao tác thay đổi trạng thái -->
          <div style="display: flex; gap: 8px; margin-bottom: 24px; padding-bottom: 16px; border-bottom: 1px solid #e2e8f0; align-items: center;">
            <span style="font-size: 13px; font-weight: 600; color: #475569;">Cập nhật tiến trình:</span>
            <button type="button" class="btn-mo-outline" style="font-size: 12px; padding: 5px 10px;" wire:click="updateStatus('{{ $selectedLead->id }}', 'NEW')">
              Mới tiếp nhận
            </button>
            <button type="button" class="btn-mo-outline" style="font-size: 12px; padding: 5px 10px;" wire:click="updateStatus('{{ $selectedLead->id }}', 'NEGOTIATING')">
              Đang đàm phán
            </button>
            <button type="button" class="btn-mo-primary" style="font-size: 12px; padding: 5px 10px;" wire:click="openConvert('{{ $selectedLead->id }}')">
              <i class="fa-solid fa-award"></i> Chốt hợp đồng (WON)
            </button>
            <button type="button" style="border:1px solid #fecaca; background:#fff; color:#ef4444; padding:5px 10px; border-radius:6px; font-size:12px; cursor:pointer;" wire:click="updateStatus('{{ $selectedLead->id }}', 'LOST')">
              <i class="fa-solid fa-ban"></i> Đóng thất bại (LOST)
            </button>
          </div>

          <!-- DÒNG THỜI GIAN CHĂM SÓC (ACTIVITY TIMELINE) -->
          <div style="margin-top: 10px;">
            <h4 style="font-size: 16px; font-weight: 700; color: var(--mo-primary); margin-bottom: 14px;">
              <i class="fa-solid fa-timeline"></i> Lịch Sử Tương Tác & Chăm Sóc (Timeline)
            </h4>

            <!-- Form ghi nhật ký mới -->
            <form wire:submit.prevent="addActivity" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px; margin-bottom: 20px;">
              <div style="display: grid; grid-template-columns: 1.5fr 2fr 1.5fr auto; gap: 10px; align-items: flex-end;">
                <div>
                  <label style="font-size: 12px; font-weight: 600; color: #475569; display: block; margin-bottom: 4px;">Hình thức</label>
                  <select wire:model.defer="newActivityType" style="width:100%; padding:7px 10px; border:1px solid #cbd5e1; border-radius:6px; font-size:13px;">
                    <option value="CALL">Cuộc gọi điện</option>
                    <option value="SITE_TOUR">Dẫn xem sàn (Site tour)</option>
                    <option value="EMAIL">Gửi Email / Báo giá</option>
                    <option value="MEETING">Gặp mặt trực tiếp</option>
                    <option value="NOTE">Ghi chú nội bộ</option>
                  </select>
                </div>
                <div>
                  <label style="font-size: 12px; font-weight: 600; color: #475569; display: block; margin-bottom: 4px;">Tiêu đề tóm tắt *</label>
                  <input type="text" wire:model.defer="newActivityTitle" placeholder="Ví dụ: Gọi trao đổi ngân sách..." style="width:100%; padding:7px 10px; border:1px solid #cbd5e1; border-radius:6px; font-size:13px;">
                </div>
                <div>
                  <label style="font-size: 12px; font-weight: 600; color: #475569; display: block; margin-bottom: 4px;">Thời gian</label>
                  <input type="datetime-local" wire:model.defer="newActivityEventAt" style="width:100%; padding:7px 10px; border:1px solid #cbd5e1; border-radius:6px; font-size:13px;">
                </div>
                <div>
                  <button type="submit" class="btn-mo-orange" style="padding: 7px 16px; font-size: 13px;">
                    <i class="fa-solid fa-plus"></i> Lưu
                  </button>
                </div>
              </div>
              <div style="margin-top: 10px;">
                <textarea wire:model.defer="newActivityContent" rows="2" placeholder="Chi tiết nội dung trao đổi, phản hồi của khách hàng..." style="width:100%; padding:8px 10px; border:1px solid #cbd5e1; border-radius:6px; font-size:13px;"></textarea>
              </div>
            </form>

            <!-- Danh sách các tương tác Timeline -->
            <div style="position: relative; padding-left: 24px; border-left: 2px solid #e2e8f0; margin-left: 10px;">
              @forelse($selectedLead->activities as $act)
                @php
                  $iconMap = [
                    'CALL' => 'fa-phone',
                    'SITE_TOUR' => 'fa-person-walking-luggage',
                    'EMAIL' => 'fa-envelope',
                    'MEETING' => 'fa-handshake',
                    'NOTE' => 'fa-note-sticky',
                    'QUOTATION_SENT' => 'fa-file-pdf',
                  ];
                  $icon = $iconMap[$act->activity_type] ?? 'fa-circle-dot';
                @endphp
                <div style="position: relative; margin-bottom: 20px;" wire:key="act-{{ $act->id }}">
                  <div style="position: absolute; left: -32px; top: 0; width: 20px; height: 20px; border-radius: 50%; background: #ea580c; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 10px;">
                    <i class="fa-solid {{ $icon }}"></i>
                  </div>
                  <div>
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                      <strong style="font-size: 14px; color: var(--mo-primary);">{{ $act->title }}</strong>
                      <span style="font-size: 12px; color: #94a3b8;">{{ $act->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                    @if($act->content)
                      <div style="font-size: 13px; color: #475569; margin-top: 4px; line-height: 1.5; background: #fff; border: 1px solid #f1f5f9; padding: 8px 12px; border-radius: 6px;">
                        {{ $act->content }}
                      </div>
                    @endif
                    <div style="font-size: 11.5px; color: #64748b; margin-top: 4px;">
                      Thực hiện bởi: <strong>{{ $act->sales?->full_name ?? 'Hệ thống tự động' }}</strong>
                    </div>
                  </div>
                </div>
              @empty
                <div style="color: #94a3b8; font-size: 13px; padding: 12px 0;">Chưa có tương tác nào được ghi nhận cho khách hàng này.</div>
              @endforelse
            </div>
          </div>
        </div>
      </div>
    </div>
  @endif

  <!-- MODAL: ĐÓNG THẤT BẠI (LOST) -->
  @if($showLostModal)
    <div class="mo-modal show" style="display: flex;">
      <div class="mo-modal-box" style="max-width: 500px;">
        <div class="mo-modal-head">
          <h3 style="font-size: 17px; font-weight: 700; color: #dc2626;">
            <i class="fa-solid fa-triangle-exclamation"></i> Xác Nhận Đóng Thất Bại (LOST)
          </h3>
          <i class="fa-solid fa-xmark" style="cursor: pointer; font-size: 20px;" wire:click="$set('showLostModal', false)"></i>
        </div>
        <div class="mo-modal-body">
          <p style="color: #64748b; font-size: 13.5px; margin-bottom: 16px;">
            Quy định nghiệp vụ bắt buộc chuyên viên phải giải trình lý do từ chối để đối soát dữ liệu bán hàng.
          </p>
          <form wire:submit.prevent="confirmLost">
            <div style="margin-bottom: 14px;">
              <label style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px;">Danh mục lý do *</label>
              <select wire:model.defer="lostCategory" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px;">
                <option value="">-- Chọn danh mục lý do --</option>
                <option value="Giá thuê vượt ngân sách">Giá thuê vượt ngân sách dự kiến</option>
                <option value="Vị trí không phù hợp">Vị trí địa lý không phù hợp</option>
                <option value="Không ưng mặt bằng/hướng">Không ưng kết cấu mặt bằng / Hướng phong thủy</option>
                <option value="Đã chọn tòa nhà khác">Đã ký hợp đồng thuê tòa nhà khác</option>
                <option value="Tạm hoãn kế hoạch mở rộng">Tạm hoãn kế hoạch mở rộng văn phòng</option>
                <option value="Khách không nghe máy/Spam">Khách không nghe máy nhiều lần / Số ảo</option>
              </select>
              @error('lostCategory') <span style="color:#ef4444; font-size:12px;">{{ $message }}</span> @enderror
            </div>

            <div style="margin-bottom: 20px;">
              <label style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px;">Chi tiết lý do thất bại *</label>
              <textarea wire:model.defer="lostDetail" rows="3" placeholder="Ghi chú cụ thể lý do khách từ chối để rút kinh nghiệm..." style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px;"></textarea>
              @error('lostDetail') <span style="color:#ef4444; font-size:12px;">{{ $message }}</span> @enderror
            </div>

            <div style="display: flex; gap: 10px; justify-content: flex-end;">
              <button type="button" class="btn-mo-outline" wire:click="$set('showLostModal', false)">Hủy Bỏ</button>
              <button type="submit" style="background:#dc2626; color:#fff; border:none; padding:8px 18px; border-radius:6px; font-weight:700; cursor:pointer;">
                <i class="fa-solid fa-ban"></i> Đóng Thất Bại
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  @endif

  <!-- MODAL: ĐIỀU PHỐI / GÁN SALES (ASSIGN) -->
  @if($showAssignModal && $assignLead)
    <div class="mo-modal show" style="display: flex;">
      <div class="mo-modal-box" style="max-width: 500px;">
        <div class="mo-modal-head">
          <h3 style="font-size: 17px; font-weight: 700; color: var(--mo-primary);">
            <i class="fa-solid fa-user-gear"></i> Bàn Giao / Điều Phối Nhân Viên Sales
          </h3>
          <i class="fa-solid fa-xmark" style="cursor: pointer; font-size: 20px;" wire:click="$set('showAssignModal', false)"></i>
        </div>
        <div class="mo-modal-body">
          <p style="color: #64748b; font-size: 13px; margin-bottom: 16px;">
            Bàn giao khách <strong>{{ $assignLead->contact_full_name }}</strong> (Hiện tại: <em>{{ $assignLead->assignedSales?->full_name ?? 'Chưa có' }}</em>).
          </p>

          <form wire:submit.prevent="submitAssign">
            <div style="margin-bottom: 14px;">
              <label style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px;">Chọn nhân viên kinh doanh tiếp nhận *</label>
              <select wire:model.defer="targetSalesId" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px;">
                <option value="">-- Chọn Sales phụ trách --</option>
                @foreach($salesList as $s)
                  <option value="{{ $s->id }}">{{ $s->full_name }} ({{ $s->employee_code }} - Tối đa {{ $s->max_concurrent_leads }} leads)</option>
                @endforeach
              </select>
              @error('targetSalesId') <span style="color:#ef4444; font-size:12px;">{{ $message }}</span> @enderror
            </div>

            <div style="margin-bottom: 20px;">
              <label style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px;">Ghi chú bàn giao</label>
              <textarea wire:model.defer="assignNote" rows="3" placeholder="Ghi chú dặn dò cho Sales mới tiếp quản..." style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px;"></textarea>
            </div>

            <div style="display: flex; gap: 10px; justify-content: flex-end;">
              <button type="button" class="btn-mo-outline" wire:click="$set('showAssignModal', false)">Hủy</button>
              <button type="submit" class="btn-mo-orange">
                <i class="fa-solid fa-check"></i> Xác Nhận Gán Sales
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  @endif

  <!-- MODAL: CHUYỂN ĐỔI SANG KHÁCH THUÊ CHÍNH THỨC (CONVERT TO TENANT) -->
  @if($showConvertModal && $convertLead)
    <div class="mo-modal show" style="display: flex;">
      <div class="mo-modal-box" style="max-width: 620px;">
        <div class="mo-modal-head">
          <h3 style="font-size: 18px; font-weight: 800; color: #15803d;">
            <i class="fa-solid fa-award"></i> Chuyển Đổi Sang Khách Thuê Doanh Nghiệp (Tenant)
          </h3>
          <i class="fa-solid fa-xmark" style="cursor: pointer; font-size: 20px;" wire:click="$set('showConvertModal', false)"></i>
        </div>
        <div class="mo-modal-body">
          <div style="background:#f0fdf4; border:1px solid #bbf7d0; color:#166534; padding:10px 14px; border-radius:6px; font-size:13px; margin-bottom:16px;">
            Hệ thống sẽ tự động cấp mã định danh khách thuê <strong>TN-{{ date('Y') }}-XXXX</strong> và bảo vệ tính bất biến của hồ sơ đối soát hợp đồng.
          </div>

          <form wire:submit.prevent="submitConvert">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px;">
              <div>
                <label style="display:block; font-size:12.5px; font-weight:600; margin-bottom:4px;">Mã số thuế *</label>
                <input type="text" wire:model.defer="tenantTaxCode" placeholder="0312345678" style="width:100%; padding:8px 12px; border:1px solid #cbd5e1; border-radius:6px;">
                @error('tenantTaxCode') <span style="color:#ef4444; font-size:11.5px;">{{ $message }}</span> @enderror
              </div>

              <div>
                <label style="display:block; font-size:12.5px; font-weight:600; margin-bottom:4px;">Tên công ty doanh nghiệp *</label>
                <input type="text" wire:model.defer="tenantCompanyName" placeholder="Công ty TNHH Giải Pháp Số" style="width:100%; padding:8px 12px; border:1px solid #cbd5e1; border-radius:6px;">
                @error('tenantCompanyName') <span style="color:#ef4444; font-size:11.5px;">{{ $message }}</span> @enderror
              </div>

              <div>
                <label style="display:block; font-size:12.5px; font-weight:600; margin-bottom:4px;">Người đại diện pháp luật *</label>
                <input type="text" wire:model.defer="tenantRepName" style="width:100%; padding:8px 12px; border:1px solid #cbd5e1; border-radius:6px;">
              </div>

              <div>
                <label style="display:block; font-size:12.5px; font-weight:600; margin-bottom:4px;">Chức vụ đại diện</label>
                <input type="text" wire:model.defer="tenantRepPosition" style="width:100%; padding:8px 12px; border:1px solid #cbd5e1; border-radius:6px;">
              </div>

              <div>
                <label style="display:block; font-size:12.5px; font-weight:600; margin-bottom:4px;">Email liên hệ / Đăng nhập *</label>
                <input type="email" wire:model.defer="tenantEmail" style="width:100%; padding:8px 12px; border:1px solid #cbd5e1; border-radius:6px;">
              </div>

              <div>
                <label style="display:block; font-size:12.5px; font-weight:600; margin-bottom:4px;">Số điện thoại liên hệ *</label>
                <input type="tel" wire:model.defer="tenantPhone" style="width:100%; padding:8px 12px; border:1px solid #cbd5e1; border-radius:6px;">
              </div>
            </div>

            <div style="margin-bottom: 20px;">
              <label style="display:block; font-size:12.5px; font-weight:600; margin-bottom:4px;">Địa chỉ trụ sở chính</label>
              <input type="text" wire:model.defer="tenantHeadquarter" placeholder="Tầng 12, Tòa nhà Bitexco..." style="width:100%; padding:8px 12px; border:1px solid #cbd5e1; border-radius:6px;">
            </div>

            <div style="display: flex; gap: 10px; justify-content: flex-end;">
              <button type="button" class="btn-mo-outline" wire:click="$set('showConvertModal', false)">Hủy</button>
              <button type="submit" class="btn-mo-orange" style="background:#15803d; border-color:#15803d;">
                <i class="fa-solid fa-check"></i> Xác Nhận Chuyển Đổi Khách Thuê
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  @endif

  <!-- MODAL: TẠO LEAD THỦ CÔNG (HOTLINE / OFFLINE) -->
  @if($showCreateModal)
    <div class="mo-modal show" style="display: flex;">
      <div class="mo-modal-box" style="max-width: 580px;">
        <div class="mo-modal-head">
          <h3 style="font-size: 18px; font-weight: 700; color: var(--mo-primary);">
            <i class="fa-solid fa-user-plus"></i> Nhập Hồ Sơ Lead Trực Tiếp (Hotline / Offline)
          </h3>
          <i class="fa-solid fa-xmark" style="cursor: pointer; font-size: 20px;" wire:click="$set('showCreateModal', false)"></i>
        </div>
        <div class="mo-modal-body">
          <form wire:submit.prevent="submitCreateManual">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px;">
              <div>
                <label style="display:block; font-size:12.5px; font-weight:600; margin-bottom:4px;">Họ tên khách hàng *</label>
                <input type="text" wire:model.defer="manualContactName" placeholder="Trần Quốc Bảo" style="width:100%; padding:8px 12px; border:1px solid #cbd5e1; border-radius:6px;">
                @error('manualContactName') <span style="color:#ef4444; font-size:11.5px;">{{ $message }}</span> @enderror
              </div>

              <div>
                <label style="display:block; font-size:12.5px; font-weight:600; margin-bottom:4px;">Số điện thoại *</label>
                <input type="tel" wire:model.defer="manualPhone" placeholder="0912 345 678" style="width:100%; padding:8px 12px; border:1px solid #cbd5e1; border-radius:6px;">
                @error('manualPhone') <span style="color:#ef4444; font-size:11.5px;">{{ $message }}</span> @enderror
              </div>

              <div>
                <label style="display:block; font-size:12.5px; font-weight:600; margin-bottom:4px;">Email liên hệ *</label>
                <input type="email" wire:model.defer="manualEmail" placeholder="bao@alpha.vn" style="width:100%; padding:8px 12px; border:1px solid #cbd5e1; border-radius:6px;">
                @error('manualEmail') <span style="color:#ef4444; font-size:11.5px;">{{ $message }}</span> @enderror
              </div>

              <div>
                <label style="display:block; font-size:12.5px; font-weight:600; margin-bottom:4px;">Tên công ty / Doanh nghiệp</label>
                <input type="text" wire:model.defer="manualCompanyName" placeholder="Công ty CP Alpha" style="width:100%; padding:8px 12px; border:1px solid #cbd5e1; border-radius:6px;">
              </div>

              <div>
                <label style="display:block; font-size:12.5px; font-weight:600; margin-bottom:4px;">Tòa nhà quan tâm</label>
                <select wire:model.defer="manualBuildingId" style="width:100%; padding:8px 12px; border:1px solid #cbd5e1; border-radius:6px;">
                  <option value="">-- Chọn tòa nhà --</option>
                  @foreach($buildings as $b)
                    <option value="{{ $b->id }}">{{ $b->name }}</option>
                  @endforeach
                </select>
              </div>

              <div>
                <label style="display:block; font-size:12.5px; font-weight:600; margin-bottom:4px;">Diện tích dự kiến (m²)</label>
                <input type="number" wire:model.defer="manualAreaSqm" placeholder="250" style="width:100%; padding:8px 12px; border:1px solid #cbd5e1; border-radius:6px;">
              </div>
            </div>

            <div style="margin-bottom: 20px;">
              <label style="display:block; font-size:12.5px; font-weight:600; margin-bottom:4px;">Ghi chú yêu cầu của khách</label>
              <textarea wire:model.defer="manualNote" rows="3" placeholder="Yêu cầu tầng cao, hướng, ngân sách..." style="width:100%; padding:8px 12px; border:1px solid #cbd5e1; border-radius:6px;"></textarea>
            </div>

            <div style="display: flex; gap: 10px; justify-content: flex-end;">
              <button type="button" class="btn-mo-outline" wire:click="$set('showCreateModal', false)">Hủy</button>
              <button type="submit" class="btn-mo-orange">
                <i class="fa-solid fa-plus"></i> Lưu Khách Tiềm Năng
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  @endif
</div>
