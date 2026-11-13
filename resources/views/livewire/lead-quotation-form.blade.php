<div class="lead-form-wrap">
  @if($isSubmitted)
    <div style="text-align: center; padding: 40px 20px;">
      <div style="width: 72px; height: 72px; border-radius: 50%; background: {{ $resultType === 'success' ? '#ecfdf5' : '#eff6ff' }}; color: {{ $resultType === 'success' ? '#10b981' : '#3b82f6' }}; display: inline-flex; align-items: center; justify-content: center; font-size: 32px; margin-bottom: 20px;">
        <i class="fa-solid {{ $resultType === 'success' ? 'fa-circle-check' : 'fa-circle-info' }}"></i>
      </div>
      <h2 style="font-size: 24px; font-weight: 800; color: var(--mo-primary); margin-bottom: 12px;">{{ $resultTitle }}</h2>
      <p style="color: #475569; font-size: 15px; max-width: 580px; margin: 0 auto 28px; line-height: 1.6;">{{ $resultMessage }}</p>
      
      <div style="display: flex; gap: 12px; justify-content: center;">
        <button type="button" wire:click="resetForm" class="btn-mo-orange" style="padding: 10px 24px; font-size: 14px;">
          <i class="fa-solid fa-rotate-left"></i> Gửi Yêu Cầu Khác
        </button>
        <a href="{{ route('home') }}" class="btn-mo-outline" style="padding: 10px 24px; font-size: 14px; text-decoration: none;">
          <i class="fa-solid fa-arrow-left"></i> Về Trang Chủ
        </a>
      </div>
    </div>
  @else
    <div style="text-align: center; margin-bottom: 24px;">
      <span class="role-switcher-badge"><i class="fa-solid fa-handshake"></i> Tiếp Nhận Yêu Cầu Thuê Văn Phòng</span>
      <h1 style="font-size: 28px; font-weight: 800; color: var(--mo-primary); margin-top: 8px;">Đăng Ký Nhận Báo Giá & Đặt Lịch Khảo Sát</h1>
      <p style="color: #64748b; font-size: 14px; margin-top: 4px;">Điền thông tin nhu cầu doanh nghiệp. Chuyên viên tư vấn sẽ liên hệ và gửi hồ sơ báo giá trong vòng 30 phút.</p>
    </div>

    <form wire:submit.prevent="submit">
      <div class="lead-form-grid">
        <!-- Họ và tên -->
        <div class="form-field">
          <label for="lead-fullname">Họ và tên người liên hệ <span style="color:#ef4444;">*</span></label>
          <input type="text" id="lead-fullname" wire:model.defer="contact_full_name" placeholder="Nguyễn Văn A" class="@error('contact_full_name') input-error @enderror">
          @error('contact_full_name')
            <span style="color:#ef4444; font-size:12px; margin-top:4px; display:block;">{{ $message }}</span>
          @enderror
        </div>

        <!-- Số điện thoại -->
        <div class="form-field">
          <label for="lead-phone">Số điện thoại liên hệ <span style="color:#ef4444;">*</span></label>
          <input type="tel" id="lead-phone" wire:model.defer="phone" placeholder="0901 234 567" class="@error('phone') input-error @enderror">
          @error('phone')
            <span style="color:#ef4444; font-size:12px; margin-top:4px; display:block;">{{ $message }}</span>
          @enderror
        </div>

        <!-- Email -->
        <div class="form-field">
          <label for="lead-email">Email doanh nghiệp <span style="color:#ef4444;">*</span></label>
          <input type="email" id="lead-email" wire:model.defer="email" placeholder="contact@company.com" class="@error('email') input-error @enderror">
          @error('email')
            <span style="color:#ef4444; font-size:12px; margin-top:4px; display:block;">{{ $message }}</span>
          @enderror
        </div>

        <!-- Tên công ty -->
        <div class="form-field">
          <label for="lead-company">Tên công ty / Doanh nghiệp</label>
          <input type="text" id="lead-company" wire:model.defer="company_name" placeholder="Công ty TNHH ABC Việt Nam">
          @error('company_name')
            <span style="color:#ef4444; font-size:12px; margin-top:4px; display:block;">{{ $message }}</span>
          @enderror
        </div>

        <!-- Tòa nhà quan tâm -->
        <div class="form-field">
          <label for="lead-building">Tòa nhà văn phòng quan tâm <span style="color:#ef4444;">*</span></label>
          <select id="lead-building" wire:model.live="building_id" class="@error('building_id') input-error @enderror">
            <option value="">-- Chọn tòa nhà bạn quan tâm --</option>
            @foreach($buildings as $b)
              @php
                $priceUsd = $b->min_rent_price_per_sqm ? round($b->min_rent_price_per_sqm / 24000) : 30;
              @endphp
              <option value="{{ $b->id }}">{{ $b->name }} ({{ $b->district }} - ${{ $priceUsd }}/m²)</option>
            @endforeach
          </select>
          @error('building_id')
            <span style="color:#ef4444; font-size:12px; margin-top:4px; display:block;">{{ $message }}</span>
          @enderror
        </div>

        <!-- Mặt bằng cụ thể (nếu có) -->
        <div class="form-field">
          <label for="lead-unit">Mặt bằng / Tầng cụ thể (Tùy chọn)</label>
          <select id="lead-unit" wire:model.defer="unit_id" {{ empty($building_id) ? 'disabled' : '' }}>
            <option value="">{{ empty($building_id) ? '-- Hãy chọn tòa nhà trước --' : '-- Chọn mặt bằng sàn trống --' }}</option>
            @foreach($units as $u)
              <option value="{{ $u->id }}">Tầng {{ $u->floor_number }} (Mã: {{ $u->unit_code }}) - {{ number_format($u->area_sqm, 0) }} m²</option>
            @endforeach
          </select>
          @error('unit_id')
            <span style="color:#ef4444; font-size:12px; margin-top:4px; display:block;">{{ $message }}</span>
          @enderror
        </div>

        <!-- Diện tích cần tìm -->
        <div class="form-field">
          <label for="lead-area">Diện tích dự kiến thuê (m²)</label>
          <input type="number" id="lead-area" wire:model.defer="desired_area_sqm" placeholder="Ví dụ: 150" min="1" step="1">
          @error('desired_area_sqm')
            <span style="color:#ef4444; font-size:12px; margin-top:4px; display:block;">{{ $message }}</span>
          @enderror
        </div>

        <!-- Lịch hẹn khảo sát -->
        <div class="form-field">
          <label for="lead-tour">Thời gian muốn khảo sát thực tế (Site tour)</label>
          <input type="datetime-local" id="lead-tour" wire:model.defer="preferred_site_tour_at">
          @error('preferred_site_tour_at')
            <span style="color:#ef4444; font-size:12px; margin-top:4px; display:block;">{{ $message }}</span>
          @enderror
        </div>

        <!-- Yêu cầu chi tiết khác -->
        <div class="form-field full">
          <label for="lead-notes">Yêu cầu chi tiết khác (Vị trí, hướng, ngân sách, số chỗ đỗ xe...)</label>
          <textarea id="lead-notes" wire:model.defer="customer_note" rows="4" placeholder="Ví dụ: Cần bàn giao hoàn thiện cơ bản, có phòng họp sẵn, ngân sách khoảng $25 - $35/m²..."></textarea>
          @error('customer_note')
            <span style="color:#ef4444; font-size:12px; margin-top:4px; display:block;">{{ $message }}</span>
          @enderror
        </div>
      </div>

      <div style="margin-top: 24px; text-align: center;">
        <button type="submit" class="btn-mo-orange" style="font-size: 15px; padding: 12px 36px; min-width: 260px;" wire:loading.attr="disabled">
          <span wire:loading.remove>
            <i class="fa-solid fa-paper-plane"></i> Gửi Yêu Cầu Cho Nhân Viên Tư Vấn
          </span>
          <span wire:loading>
            <i class="fa-solid fa-spinner fa-spin"></i> Đang gửi yêu cầu...
          </span>
        </button>
      </div>
    </form>
  @endif
</div>
