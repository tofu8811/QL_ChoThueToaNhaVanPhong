<div class="lead-form-wrap" x-data="{
    // Quản lý trạng thái lỗi Client-side (0ms delay, ZERO request server)
    clientErrors: {
        fullname: '',
        phone: '',
        email: '',
        tour: ''
    },

    // Quản lý cờ lỗi từ Server (bị hủy ngay khi người dùng chạm vào/chỉnh sửa trường đó)
    serverErrors: {
        fullname: @error('contact_full_name') true @else false @enderror,
        phone: @error('phone') true @else false @enderror,
        email: @error('email') true @else false @enderror,
        tour: @error('preferred_site_tour_at') true @else false @enderror
    },

    // Kiểm tra tính hợp lệ của từng trường
    validateFullname(val) {
        this.serverErrors.fullname = false;
        if (!val || val.trim().length < 2) {
            this.clientErrors.fullname = 'Vui lòng nhập họ và tên người liên hệ.';
            return false;
        }
        this.clientErrors.fullname = '';
        return true;
    },

    validatePhone(val) {
        this.serverErrors.phone = false;
        let clean = (val || '').replace(/[^0-9]/g, '');
        if (!clean) {
            this.clientErrors.phone = 'Vui lòng cung cấp số điện thoại hợp lệ.';
            return false;
        }
        // Chuẩn Việt Nam: bắt đầu bằng 0, độ dài 10 hoặc 11 chữ số (chấp nhận mọi đầu số di động và cố định)
        let regex = /^0[0-9]{9,10}$/;
        if (!regex.test(clean)) {
            this.clientErrors.phone = 'Vui lòng cung cấp số điện thoại hợp lệ.';
            return false;
        }
        this.clientErrors.phone = '';
        return true;
    },

    validateEmail(val) {
        this.serverErrors.email = false;
        if (!val || val.trim() === '') {
            this.clientErrors.email = 'Vui lòng cung cấp địa chỉ email hợp lệ.';
            return false;
        }
        let regex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
        if (!regex.test(val.trim())) {
            this.clientErrors.email = 'Vui lòng cung cấp địa chỉ email hợp lệ.';
            return false;
        }
        this.clientErrors.email = '';
        return true;
    },

    validateTour(val) {
        this.serverErrors.tour = false;
        if (!val) {
            this.clientErrors.tour = '';
            return true;
        }
        let selectedTime = new Date(val).getTime();
        // Thời gian khảo sát phải cách thời điểm gửi form ít nhất 3 ngày (72 giờ)
        let minTime = Date.now() + (3 * 24 * 60 * 60 * 1000);
        if (selectedTime < minTime) {
            this.clientErrors.tour = 'Thời gian muốn khảo sát thực tế phải cách thời điểm gửi yêu cầu ít nhất 3 ngày.';
            return false;
        }
        this.clientErrors.tour = '';
        return true;
    },

    submitForm(e) {
        let valid = true;
        let fn = document.getElementById('lead-fullname')?.value || '';
        let ph = document.getElementById('lead-phone')?.value || '';
        let em = document.getElementById('lead-email')?.value || '';
        let tr = document.getElementById('lead-tour')?.value || '';

        if (!this.validateFullname(fn)) valid = false;
        if (!this.validatePhone(ph)) valid = false;
        if (!this.validateEmail(em)) valid = false;
        if (tr && !this.validateTour(tr)) valid = false;

        if (!valid) {
            if (typeof moToast === 'function') {
                moToast('Vui lòng kiểm tra và điền chính xác các trường thông tin bắt buộc.', 'error', 'Thông tin chưa hợp lệ');
            }
            return;
        }

        $wire.submit();
    }
}">
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
      <h1 style="font-size: 28px; font-weight: 800; color: var(--mo-primary); margin-top: 8px;">Đăng Ký Nhận Báo Giá & Đặt Lịch Khảo Sát</h1>
      <p style="color: #64748b; font-size: 14px; margin-top: 4px;">Điền thông tin nhu cầu doanh nghiệp. Chuyên viên tư vấn sẽ liên hệ và gửi hồ sơ báo giá trong vòng 30 phút.</p>
    </div>

    <form @submit.prevent="submitForm($event)" novalidate>
      <div class="lead-form-grid">
        <!-- 1. Họ và tên người liên hệ -->
        <div class="form-field">
          <label for="lead-fullname">Họ và tên người liên hệ <span style="color:#ef4444;">*</span></label>
          <input type="text" 
                 id="lead-fullname" 
                 wire:model.defer="contact_full_name" 
                 placeholder="Nguyễn Văn A" 
                 @input="serverErrors.fullname = false; if (clientErrors.fullname) validateFullname($event.target.value)"
                 @blur="serverErrors.fullname = false; validateFullname($event.target.value)"
                 :class="(clientErrors.fullname || serverErrors.fullname) ? 'input-error' : ''"
                 class="@error('contact_full_name') input-error @enderror">
          <div class="field-error-slot">
            <span x-show="clientErrors.fullname" x-text="clientErrors.fullname" x-cloak></span>
            @error('contact_full_name')
              <span x-show="serverErrors.fullname && !clientErrors.fullname" x-cloak>{{ $message }}</span>
            @enderror
          </div>
        </div>

        <!-- 2. Số điện thoại liên hệ (Chặn chữ, dấu cách ngay lập tức ở client-side) -->
        <div class="form-field">
          <label for="lead-phone">Số điện thoại liên hệ <span style="color:#ef4444;">*</span></label>
          <input type="tel" 
                 id="lead-phone" 
                 wire:model.defer="phone" 
                 inputmode="numeric" 
                 autocomplete="tel"
                 maxlength="11"
                 placeholder="0901234567" 
                 oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                 @input="serverErrors.phone = false; let clean = $event.target.value.replace(/[^0-9]/g, ''); $event.target.value = clean; if (clientErrors.phone) validatePhone(clean);"
                 @blur="serverErrors.phone = false; validatePhone($event.target.value)"
                 :class="(clientErrors.phone || serverErrors.phone) ? 'input-error' : ''"
                 class="@error('phone') input-error @enderror">
          <div class="field-error-slot">
            <span x-show="clientErrors.phone" x-text="clientErrors.phone" x-cloak></span>
            @error('phone')
              <span x-show="serverErrors.phone && !clientErrors.phone" x-cloak>{{ $message }}</span>
            @enderror
          </div>
        </div>

        <!-- 3. Email doanh nghiệp -->
        <div class="form-field">
          <label for="lead-email">Email doanh nghiệp <span style="color:#ef4444;">*</span></label>
          <input type="email" 
                 id="lead-email" 
                 wire:model.defer="email" 
                 autocomplete="email"
                 placeholder="contact@company.com" 
                 @input="serverErrors.email = false; if (clientErrors.email) validateEmail($event.target.value)"
                 @blur="serverErrors.email = false; validateEmail($event.target.value)"
                 :class="(clientErrors.email || serverErrors.email) ? 'input-error' : ''"
                 class="@error('email') input-error @enderror">
          <div class="field-error-slot">
            <span x-show="clientErrors.email" x-text="clientErrors.email" x-cloak></span>
            @error('email')
              <span x-show="serverErrors.email && !clientErrors.email" x-cloak>{{ $message }}</span>
            @enderror
          </div>
        </div>

        <!-- 4. Tên công ty / Doanh nghiệp -->
        <div class="form-field">
          <label for="lead-company">Tên công ty / Doanh nghiệp</label>
          <input type="text" id="lead-company" wire:model.defer="company_name" placeholder="Công ty TNHH ABC Việt Nam">
          <div class="field-error-slot">
            @error('company_name')
              <span>{{ $message }}</span>
            @enderror
          </div>
        </div>

        <!-- 5. KHU VỰC (TỈNH / THÀNH PHỐ) - NẰM RIÊNG 1 DÒNG ĐỘC LẬP PHÍA TRÊN Ô TÒA NHÀ -->
        @php
          $pSelected = $province_code ? $provinces->firstWhere('code', $province_code) : null;
          $pInitialLabel = $pSelected ? ($pSelected->name . ($pSelected->buildings_count > 0 ? " ({$pSelected->buildings_count} tòa nhà)" : "")) : '-- Tất cả khu vực (Toàn quốc) --';
        @endphp
        <div class="form-field full">
          <label for="lead-province"><span>Khu vực (Tỉnh / Thành phố) <span style="font-weight: 400; color: #64748b; font-size: 12.5px;">- Lọc danh sách tòa nhà theo khu vực</span></span></label>

          <div class="custom-search-select"
               x-data="moSearchableSelect({
                 wireProp: 'province_code',
                 initialValue: {{ $province_code ? (int)$province_code : 'null' }},
                 defaultLabel: '-- Tất cả khu vực (Toàn quốc) --',
                 items: [
                   { value: '', label: '-- Tất cả khu vực (Toàn quốc) --' },
                   @foreach($provinces as $p)
                     { value: '{{ $p->code }}', label: '{{ addslashes($p->name . ($p->buildings_count > 0 ? " ({$p->buildings_count} tòa nhà)" : "")) }}' },
                   @endforeach
                 ]
               })"
               wire:key="province-search-select"
               @click.outside="close()"
               @keydown="onKeyDown($event)">
            
            <!-- Trigger button hiển thị giống 100% thẻ select -->
            <div class="custom-select-trigger"
                 :class="{ 'is-open': open, 'input-error': {{ $errors->has('province_code') ? 'true' : 'false' }} }"
                 tabindex="0"
                 @click="toggle()">
              <span class="trigger-text" x-text="currentLabel">{{ $pInitialLabel }}</span>
              <span wire:loading.remove wire:target="province_code">
                <i class="fa-solid fa-chevron-down trigger-arrow"></i>
              </span>
              <span wire:loading wire:target="province_code">
                <i class="fa-solid fa-spinner fa-spin" style="font-size: 12px; color: var(--mo-orange, #ea580c); margin-left: 8px;"></i>
              </span>
            </div>

            <!-- Dropdown popup chứa ô tìm kiếm nhanh (có dấu và không dấu) -->
            <div class="custom-select-dropdown" x-show="open" x-cloak>
              <div class="custom-select-search-wrap">
                <div class="custom-select-search-inner">
                  <i class="fa-solid fa-magnifying-glass" style="font-size: 12px; color: #94a3b8; margin-right: 6px;"></i>
                  <input type="text"
                         class="custom-select-search-input"
                         x-ref="searchInput"
                         x-model="search"
                         @input="highlightedIndex = 0"
                         placeholder="Nhập tên khu vực / tỉnh thành để tìm nhanh..."
                         autocomplete="off">
                  <button type="button" x-show="search" @click="search = ''; $refs.searchInput.focus()" style="background:none; border:none; padding:0; cursor:pointer; color:#94a3b8; font-size:12px;">
                    <i class="fa-solid fa-xmark"></i>
                  </button>
                </div>
              </div>

              <div class="custom-select-options-list" x-ref="optionsList">
                <template x-for="(item, idx) in filteredItems" :key="item.value">
                  <div class="custom-select-option"
                       :class="{
                         'is-selected': selectedValue == item.value || (!selectedValue && !item.value),
                         'is-highlighted': highlightedIndex === idx
                       }"
                       @mouseenter="highlightedIndex = idx"
                       @click="select(item.value)"
                       x-text="item.label">
                  </div>
                </template>
                <div x-show="filteredItems.length === 0" class="custom-select-empty">
                  <i class="fa-solid fa-magnifying-glass" style="margin-right: 6px;"></i> Không tìm thấy khu vực phù hợp
                </div>
              </div>
            </div>
          </div>

          <div class="field-error-slot">
            @error('province_code')
              <span>{{ $message }}</span>
            @enderror
          </div>
        </div>

        <!-- 6. Tòa nhà văn phòng quan tâm (Được lọc tự động sau khi chọn khu vực) -->
        @php
          $bSelected = $building_id ? $buildings->firstWhere('id', $building_id) : null;
          $bDefaultLabel = $buildings->isEmpty() ? '-- Chưa có tòa nhà tại khu vực này (Tư vấn tìm kiếm thêm) --' : "-- Để trống nếu muốn nhận tư vấn thêm (hoặc chọn trong {$buildings->count()} tòa nhà) --";
          $bInitialLabel = $bSelected ? ($bSelected->district ? "{$bSelected->name} ({$bSelected->district})" : $bSelected->name) : $bDefaultLabel;
        @endphp
        <div class="form-field">
          <label for="lead-building"><span>Tòa nhà văn phòng quan tâm <span style="font-size: 12px; color: #64748b; font-weight: normal;">(Tùy chọn)</span></span></label>

          <div class="custom-search-select"
               x-data="moSearchableSelect({
                 wireProp: 'building_id',
                 initialValue: {{ $building_id ? json_encode($building_id) : 'null' }},
                 defaultLabel: '{{ addslashes($bDefaultLabel) }}',
                 items: [
                   @if(!$buildings->isEmpty())
                     { value: '', label: '{{ addslashes($bDefaultLabel) }}' },
                   @endif
                   @foreach($buildings as $b)
                     @php
                       $bLabel = $b->district ? "{$b->name} ({$b->district})" : $b->name;
                     @endphp
                     { value: '{{ $b->id }}', label: '{{ addslashes($bLabel) }}' },
                   @endforeach
                 ]
               })"
               wire:key="building-search-select-{{ $province_code ?? 'all' }}-{{ $buildings->count() }}"
               @click.outside="close()"
               @keydown="onKeyDown($event)">
            
            <!-- Trigger button hiển thị giống 100% thẻ select -->
            <div class="custom-select-trigger"
                 :class="{ 'is-open': open, 'input-error': {{ $errors->has('building_id') ? 'true' : 'false' }} }"
                 tabindex="0"
                 @click="toggle()">
              <span class="trigger-text" x-text="currentLabel">{{ $bInitialLabel }}</span>
              <span wire:loading.remove wire:target="province_code">
                <i class="fa-solid fa-chevron-down trigger-arrow"></i>
              </span>
              <span wire:loading wire:target="province_code">
                <i class="fa-solid fa-spinner fa-spin" style="font-size: 12px; color: var(--mo-orange, #ea580c); margin-left: 8px;"></i>
              </span>
            </div>

            <!-- Dropdown popup chứa ô tìm kiếm nhanh (có dấu và không dấu) -->
            <div class="custom-select-dropdown" x-show="open" x-cloak>
              <div class="custom-select-search-wrap">
                <div class="custom-select-search-inner">
                  <i class="fa-solid fa-magnifying-glass" style="font-size: 12px; color: #94a3b8; margin-right: 6px;"></i>
                  <input type="text"
                         class="custom-select-search-input"
                         x-ref="searchInput"
                         x-model="search"
                         @input="highlightedIndex = 0"
                         placeholder="Nhập tên tòa nhà, quận để tìm nhanh..."
                         autocomplete="off">
                  <button type="button" x-show="search" @click="search = ''; $refs.searchInput.focus()" style="background:none; border:none; padding:0; cursor:pointer; color:#94a3b8; font-size:12px;">
                    <i class="fa-solid fa-xmark"></i>
                  </button>
                </div>
              </div>

              <div class="custom-select-options-list" x-ref="optionsList">
                <template x-for="(item, idx) in filteredItems" :key="item.value">
                  <div class="custom-select-option"
                       :class="{
                         'is-selected': selectedValue == item.value || (!selectedValue && !item.value),
                         'is-highlighted': highlightedIndex === idx
                       }"
                       @mouseenter="highlightedIndex = idx"
                       @click="select(item.value)"
                       x-text="item.label">
                  </div>
                </template>
                <div x-show="filteredItems.length === 0" class="custom-select-empty">
                  <i class="fa-solid fa-magnifying-glass" style="margin-right: 6px;"></i> Không tìm thấy tòa nhà phù hợp
                </div>
              </div>
            </div>
          </div>

          <div class="field-error-slot">
            @error('building_id')
              <span>{{ $message }}</span>
            @enderror
          </div>
        </div>

        <!-- 7. Mặt bằng cụ thể (nếu có) -->
        <div class="form-field">
          <label for="lead-unit"><span>Mặt bằng / Tầng cụ thể <span style="font-size: 12px; color: #64748b; font-weight: normal;">(Tùy chọn)</span></span></label>
          <select id="lead-unit" wire:model.defer="unit_id" {{ empty($building_id) ? 'disabled' : '' }}>
            <option value="">{{ empty($building_id) ? '-- Hãy chọn tòa nhà trước --' : '-- Chọn mặt bằng sàn trống --' }}</option>
            @foreach($units as $u)
              <option value="{{ $u->id }}">Tầng {{ $u->floor_number }} - {{ number_format($u->area_sqm, 0) }} m²</option>
            @endforeach
          </select>
          <div class="field-error-slot">
            @error('unit_id')
              <span>{{ $message }}</span>
            @enderror
          </div>
        </div>

        <!-- 8. Diện tích cần tìm -->
        <div class="form-field">
          <label for="lead-area"><span>Diện tích dự kiến thuê <span style="font-size: 12px; color: #64748b; font-weight: normal;">(m²)</span></span></label>
          <input type="number" id="lead-area" wire:model.defer="desired_area_sqm" placeholder="Ví dụ: 150" min="1" step="1">
          <div class="field-error-slot">
            @error('desired_area_sqm')
              <span>{{ $message }}</span>
            @enderror
          </div>
        </div>

        <!-- 9. Lịch hẹn khảo sát (Validate cách thời gian gửi form tối thiểu 3 ngày) -->
        <div class="form-field">
          <label for="lead-tour"><span>Thời gian muốn khảo sát thực tế <span style="font-size: 12px; color: #64748b; font-weight: normal;">(Site tour)</span></span></label>
          <input type="datetime-local" 
                 id="lead-tour" 
                 wire:model.defer="preferred_site_tour_at"
                 min="{{ now()->addDays(3)->format('Y-m-d\TH:i') }}"
                 @input="serverErrors.tour = false; if (clientErrors.tour) validateTour($event.target.value)"
                 @change="serverErrors.tour = false; validateTour($event.target.value)"
                 @blur="serverErrors.tour = false; validateTour($event.target.value)"
                 :class="(clientErrors.tour || serverErrors.tour) ? 'input-error' : ''"
                 class="@error('preferred_site_tour_at') input-error @enderror">
          <div class="field-error-slot">
            <span x-show="clientErrors.tour" x-text="clientErrors.tour" x-cloak></span>
            @error('preferred_site_tour_at')
              <span x-show="serverErrors.tour && !clientErrors.tour" x-cloak>{{ $message }}</span>
            @enderror
          </div>
        </div>

        <!-- 10. Yêu cầu chi tiết khác -->
        <div class="form-field full">
          <label for="lead-notes">Yêu cầu chi tiết khác (Vị trí, hướng, ngân sách, số chỗ đỗ xe...)</label>
          <textarea id="lead-notes" wire:model.defer="customer_note" rows="4" placeholder="Ví dụ: Cần bàn giao hoàn thiện cơ bản, có phòng họp sẵn, ngân sách khoảng 500.000 - 800.000 đ/m²..."></textarea>
          <div class="field-error-slot">
            @error('customer_note')
              <span>{{ $message }}</span>
            @enderror
          </div>
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

  <style>
    .custom-search-select {
      position: relative;
      width: 100%;
      min-width: 0;
      max-width: 100%;
      box-sizing: border-box;
    }
    .custom-select-trigger {
      width: 100%;
      min-width: 0;
      max-width: 100%;
      height: 44px !important;
      min-height: 44px !important;
      max-height: 44px !important;
      padding: 10px 14px;
      border: 1px solid #cbd5e1;
      border-radius: var(--radius-sm, 6px);
      font-size: 14px;
      background: #fff;
      color: #1e293b;
      display: flex;
      align-items: center;
      justify-content: space-between;
      cursor: pointer;
      user-select: none;
      box-sizing: border-box !important;
      transition: border-color 0.15s, box-shadow 0.15s;
      overflow: hidden;
    }
    .custom-select-trigger:focus,
    .custom-select-trigger.is-open {
      border-color: var(--mo-orange, #ea580c);
      box-shadow: 0 0 0 3px rgba(234, 88, 12, 0.15);
      outline: none;
    }
    .custom-select-trigger.input-error {
      border-color: #ef4444 !important;
    }
    .custom-select-trigger .trigger-text {
      flex: 1 1 0%;
      min-width: 0;
      width: 0;
      max-width: 100%;
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
      font-size: 14px;
      line-height: 22px;
      color: #334155;
      text-align: left;
    }
    .custom-select-trigger .trigger-arrow {
      font-size: 12px;
      color: #64748b;
      margin-left: 8px;
      flex-shrink: 0;
      transition: transform 0.2s;
    }
    .custom-select-trigger.is-open .trigger-arrow {
      transform: rotate(180deg);
    }
    .custom-select-dropdown {
      position: absolute;
      top: calc(100% + 4px);
      left: 0;
      right: 0;
      width: 100%;
      box-sizing: border-box;
      background: #fff;
      border: 1px solid #cbd5e1;
      border-radius: 6px;
      z-index: 100;
      box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1), 0 4px 6px -4px rgba(0,0,0,0.1);
      overflow: hidden;
    }
    .custom-select-search-wrap {
      padding: 8px 10px;
      background: #f8fafc;
      border-bottom: 1px solid #e2e8f0;
    }
    .custom-select-search-inner {
      display: flex;
      align-items: center;
      background: #fff;
      border: 1px solid #cbd5e1;
      border-radius: 4px;
      padding: 0 10px;
      height: 36px;
      transition: border-color 0.15s;
    }
    .custom-select-search-inner:focus-within {
      border-color: var(--mo-orange, #ea580c);
    }
    .custom-select-search-input {
      width: 100%;
      border: none !important;
      outline: none !important;
      box-shadow: none !important;
      font-size: 13.5px !important;
      padding: 0 6px !important;
      background: transparent !important;
      height: auto !important;
      color: #1e293b;
    }
    .custom-select-options-list {
      max-height: 220px;
      overflow-y: auto;
      padding: 4px 0;
    }
    .custom-select-option {
      padding: 9px 14px;
      font-size: 13.5px;
      color: #334155;
      cursor: pointer;
      transition: background 0.12s;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
      user-select: none;
      text-align: left;
    }
    .custom-select-option:hover,
    .custom-select-option.is-highlighted {
      background: #f1f5f9;
    }
    .custom-select-option.is-selected {
      background: #eff6ff;
      color: #1e40af;
      font-weight: 600;
    }
    .custom-select-empty {
      padding: 16px;
      text-align: center;
      color: #94a3b8;
      font-size: 13px;
    }
  </style>

  <script>
    if (typeof window.moSearchableSelect !== 'function') {
      window.moSearchableSelect = function(config) {
        return {
          open: false,
          search: '',
          highlightedIndex: -1,
          items: config.items || [],
          defaultLabel: config.defaultLabel || '',
          wireProp: config.wireProp,
          selectedValue: config.initialValue ?? null,

          init() {
            this.$watch('$wire.' + this.wireProp, (val) => {
              this.selectedValue = val ?? null;
            });
          },

          get currentLabel() {
            let val = this.selectedValue;
            if (val === null || val === undefined || val === '') {
              return this.defaultLabel;
            }
            let found = this.items.find(i => String(i.value) === String(val));
            return found ? found.label : this.defaultLabel;
          },

          get filteredItems() {
            if (!this.search || !this.search.trim()) {
              return this.items;
            }
            let s = this.removeTones(this.search);
            return this.items.filter(item => {
              let t = this.removeTones(item.label);
              return t.includes(s) || item.label.toLowerCase().includes(this.search.toLowerCase());
            });
          },

          removeTones(str) {
            if (!str) return '';
            str = String(str).toLowerCase();
            str = str.replace(/à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ/g, 'a');
            str = str.replace(/è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ/g, 'e');
            str = str.replace(/ì|í|ị|ỉ|ĩ/g, 'i');
            str = str.replace(/ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ/g, 'o');
            str = str.replace(/ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ/g, 'u');
            str = str.replace(/ỳ|ý|ỵ|ỷ|ỹ/g, 'y');
            str = str.replace(/đ|Đ/g, 'd');
            str = str.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
            return str.trim();
          },

          toggle() {
            this.open = !this.open;
            if (this.open) {
              this.search = '';
              this.highlightedIndex = -1;
              this.$nextTick(() => {
                this.$refs.searchInput?.focus();
              });
            }
          },

          close() {
            this.open = false;
            this.search = '';
            this.highlightedIndex = -1;
          },

          select(val) {
            let cleanVal = (val === '' || val === null || val === undefined) ? null : val;
            this.selectedValue = cleanVal;
            this.$wire.set(this.wireProp, cleanVal);
            this.close();
          },

          onKeyDown(e) {
            if (!this.open) {
              if (e.key === 'ArrowDown' || e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                this.toggle();
              }
              return;
            }
            if (e.key === 'ArrowDown') {
              e.preventDefault();
              if (this.highlightedIndex < this.filteredItems.length - 1) {
                this.highlightedIndex++;
                this.scrollHighlightedIntoView();
              }
            } else if (e.key === 'ArrowUp') {
              e.preventDefault();
              if (this.highlightedIndex > 0) {
                this.highlightedIndex--;
                this.scrollHighlightedIntoView();
              }
            } else if (e.key === 'Enter') {
              e.preventDefault();
              if (this.highlightedIndex >= 0 && this.highlightedIndex < this.filteredItems.length) {
                this.select(this.filteredItems[this.highlightedIndex].value);
              } else if (this.filteredItems.length === 1) {
                this.select(this.filteredItems[0].value);
              }
            } else if (e.key === 'Escape') {
              e.preventDefault();
              this.close();
            }
          },

          scrollHighlightedIntoView() {
            this.$nextTick(() => {
              let el = this.$refs.optionsList?.querySelector('.is-highlighted');
              el?.scrollIntoView({ block: 'nearest' });
            });
          }
        };
      };
    }
  </script>
</div>
