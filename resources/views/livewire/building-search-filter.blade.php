<div>
  <!-- Advanced Filter Floating Box -->
  <div class="container mo-filter-container" id="mo-building-filter-section">
    <div class="mo-filter-box" x-data="{
      keyword: $wire.entangle('keyword'),
      province: $wire.entangle('province'),
      district: $wire.entangle('district'),
      grade: $wire.entangle('grade'),
      price: $wire.entangle('price'),
      area: $wire.entangle('area'),

      provinceMap: {
        '1': 'Hà Nội',
        '79': 'TP. Hồ Chí Minh',
        '75': 'Đồng Nai',
        '48': 'Đà Nẵng'
      },
      gradeMap: {
        'A': 'Tòa nhà Hạng A',
        'B': 'Tòa nhà Hạng B',
        'C': 'Tòa nhà Hạng C'
      },
      priceMap: {
        'under500k': 'Dưới 500.000 đ/m²',
        '500kto1m': '500k - 1tr đ/m²',
        'over1m': 'Trên 1.000.000 đ/m²',
        'under20': 'Dưới 500.000 đ/m²',
        '20to35': '500k - 1tr đ/m²',
        'over35': 'Trên 1.000.000 đ/m²'
      },
      areaMap: {
        'under150': 'Dưới 150 m²',
        '150to500': '150 - 500 m²',
        'over500': 'Trên 500 m²'
      },

      get hasActive() {
        return (Boolean(this.keyword) && String(this.keyword).trim() !== '') ||
               (Boolean(this.province) && this.province !== 'all') ||
               (Boolean(this.district) && this.district !== 'all') ||
               (Boolean(this.grade) && this.grade !== 'all') ||
               (Boolean(this.price) && this.price !== 'all') ||
               (Boolean(this.area) && this.area !== 'all');
      },

      remove(type) {
        if (type === 'keyword') { this.keyword = ''; }
        else if (type === 'province') { this.province = 'all'; this.district = 'all'; }
        else if (type === 'district') { this.district = 'all'; }
        else if (type === 'grade') { this.grade = 'all'; }
        else if (type === 'price') { this.price = 'all'; }
        else if (type === 'area') { this.area = 'all'; }
        $wire.removeFilter(type);
      },

      resetAll() {
        this.keyword = '';
        this.province = 'all';
        this.district = 'all';
        this.grade = 'all';
        this.price = 'all';
        this.area = 'all';
        $wire.resetFilters();
      }
    }">
      <div class="mo-filter-grid">
        <!-- Search Keyword -->
        <div class="mo-filter-group">
          <label class="mo-filter-label"><i class="fa-solid fa-magnifying-glass"></i> Tên tòa nhà hoặc đường</label>
          <div class="mo-input-wrap" style="position:relative;">
            <i class="fa-solid fa-search"></i>
            <input type="text" x-model="keyword" wire:keydown.enter="applySearch" placeholder="Ví dụ: Capital Place, Keangnam, Nexus, Pegasus..." style="padding-right:32px;">
            <button type="button" x-show="keyword && keyword.trim().length > 0" @click="keyword = ''; $wire.set('keyword', ''); applySearch();" style="display:none; position:absolute; right:10px; top:50%; transform:translateY(-50%); width:19px; height:19px; border-radius:50%; background:#cbd5e1; color:#475569; border:none; align-items:center; justify-content:center; cursor:pointer; font-size:11px; padding:0; z-index:5;" title="Xóa tìm kiếm">
              <i class="fa-solid fa-xmark"></i>
            </button>
          </div>
        </div>

        <!-- Province / Thành phố -->
        <div class="mo-filter-group">
          <label class="mo-filter-label"><i class="fa-solid fa-map-location-dot"></i> Tỉnh / Thành phố</label>
          <select x-model="province" @change="$wire.set('province', province); $wire.set('district', 'all')" class="mo-filter-select">
            <option value="all">Toàn quốc (Tất cả khu vực)</option>
            <option value="1">Thành phố Hà Nội</option>
            <option value="79">TP. Hồ Chí Minh</option>
            <option value="75">Thành phố Đồng Nai</option>
            <option value="48">Thành phố Đà Nẵng</option>
          </select>
        </div>

        <!-- District / Quận Huyện (Đọc ĐỘNG 100% từ Database) -->
        <div class="mo-filter-group">
          <label class="mo-filter-label"><i class="fa-solid fa-location-dot"></i> Quận / Huyện / Khu vực</label>
          <select x-model="district" class="mo-filter-select">
            <option value="all">Tất cả Quận / Huyện</option>

            @if($province === 'all')
              {{-- Toàn quốc: Nhóm động theo từng Tỉnh/Thành trực tiếp từ DB --}}
              @php
                $grouped = collect($dbDistricts)->groupBy('province');
              @endphp
              @foreach($grouped as $provName => $dList)
                <optgroup label="{{ $provName }}">
                  @foreach($dList as $d)
                    <option value="{{ $d['name'] }}">{{ $d['name'] }} ({{ $d['count'] }} tòa)</option>
                  @endforeach
                </optgroup>
              @endforeach

              @if(!empty($dbWards))
                <optgroup label="Phường / Xã có văn phòng cho thuê">
                  @foreach($dbWards as $w)
                    <option value="{{ $w['display_label'] }}">{{ $w['display_label'] }} ({{ $w['count'] }} tòa)</option>
                  @endforeach
                </optgroup>
              @endif
            @else
              {{-- Tỉnh/Thành cụ thể: Liệt kê động từ DB --}}
              @if(!empty($dbDistricts))
                <optgroup label="Khu Vực (Quận / Huyện có văn phòng)">
                  @foreach($dbDistricts as $d)
                    <option value="{{ $d['name'] }}">{{ $d['name'] }} ({{ $d['count'] }} tòa)</option>
                  @endforeach
                </optgroup>
              @endif

              @if(!empty($dbWards))
                <optgroup label="Phường / Xã (Có văn phòng cho thuê)">
                  @foreach($dbWards as $w)
                    <option value="{{ $w['display_label'] }}">{{ $w['display_label'] }} ({{ $w['count'] }} tòa)</option>
                  @endforeach
                </optgroup>
              @endif

              {{-- Bổ sung các phường mới từ file địa giới hành chính nếu chưa có trong DB --}}
              @if(!empty($geoData['wards']))
                <optgroup label="Địa chỉ sau sáp nhập (Phường Mới)">
                  @foreach($geoData['wards'] as $ward)
                    @php $wLabel = $ward['display_label'] ?? $ward['name']; @endphp
                    @if(!empty($ward['district_hint']) && !collect($dbWards)->contains('display_label', $wLabel))
                      <option value="{{ $wLabel }}">{{ $wLabel }}</option>
                    @endif
                  @endforeach
                </optgroup>
              @endif
            @endif

            @if(!empty($district) && $district !== 'all' && !collect($dbDistricts)->contains('name', $district) && !collect($dbWards)->contains('display_label', $district))
              <option value="{{ $district }}" selected>{{ $district }}</option>
            @endif
          </select>
        </div>

        <!-- Grade -->
        <div class="mo-filter-group">
          <label class="mo-filter-label"><i class="fa-solid fa-medal"></i> Hạng văn phòng</label>
          <select x-model="grade" class="mo-filter-select">
            <option value="all">Tất cả hạng</option>
            <option value="A">Hạng A / A+ (Quốc tế & Xanh)</option>
            <option value="B">Hạng B / B+ (Hiện đại)</option>
            <option value="C">Hạng C / Tiêu chuẩn</option>
          </select>
        </div>

        <!-- Price Range -->
        <div class="mo-filter-group">
          <label class="mo-filter-label"><i class="fa-solid fa-coins"></i> Khoảng giá (VNĐ/m²)</label>
          <select x-model="price" class="mo-filter-select">
            <option value="all">Tất cả mức giá</option>
            <option value="under500k">Dưới 500.000 đ / m² (Kinh tế)</option>
            <option value="500kto1m">500.000 - 1.000.000 đ / m² (Phổ biến)</option>
            <option value="over1m">Trên 1.000.000 đ / m² (Hạng A VIP)</option>
          </select>
        </div>

        <!-- Area Range -->
        <div class="mo-filter-group">
          <label class="mo-filter-label"><i class="fa-solid fa-vector-square"></i> Diện tích</label>
          <select x-model="area" class="mo-filter-select">
            <option value="all">Tất cả diện tích</option>
            <option value="under150">Dưới 150 m²</option>
            <option value="150to500">150 - 500 m²</option>
            <option value="over500">Trên 500 m² (Nguyên sàn)</option>
          </select>
        </div>

        <!-- Search Submit Button -->
        <div>
          <button class="btn-filter-submit" type="button" wire:click="applySearch" id="btn-filter-search" title="Tìm kiếm theo các tiêu chí đã chọn">
            <i class="fa-solid fa-magnifying-glass"></i> Tìm
          </button>
        </div>
      </div>

      <!-- Active Applied Filters Chips (Hiển thị các bộ lọc đang chọn - Phản hồi 0ms bằng Alpine.js) -->
      <div class="mo-active-filters-bar" x-show="hasActive" x-cloak style="display: none;">
        <span class="mo-active-filters-label">
          <i class="fa-solid fa-filter" style="color: var(--mo-orange);"></i> Đang lọc theo:
        </span>

        <div class="mo-active-chips-wrap">
          {{-- 1. Từ khóa --}}
          <span class="mo-active-filter-chip" x-show="Boolean(keyword) && String(keyword).trim() !== ''" style="display: none;">
            <i class="fa-solid fa-magnifying-glass" style="color:#2563eb;font-size:11px;"></i>
            <span>Từ khóa: <strong x-text="'&quot;' + String(keyword).trim() + '&quot;'"></strong></span>
            <button type="button" class="chip-remove-btn" @click="remove('keyword')" title="Xóa từ khóa">
              <i class="fa-solid fa-xmark"></i>
            </button>
          </span>

          {{-- 2. Tỉnh / Thành phố --}}
          <span class="mo-active-filter-chip" x-show="Boolean(province) && province !== 'all'" style="display: none;">
            <i class="fa-solid fa-city" style="color:#ea580c;font-size:11px;"></i>
            <span x-text="provinceMap[province] || province"></span>
            <button type="button" class="chip-remove-btn" @click="remove('province')" title="Bỏ chọn tỉnh thành">
              <i class="fa-solid fa-xmark"></i>
            </button>
          </span>

          {{-- 3. Quận / Huyện / Khu vực --}}
          <span class="mo-active-filter-chip" x-show="Boolean(district) && district !== 'all'" style="display: none;">
            <i class="fa-solid fa-location-dot" style="color:#10b981;font-size:11px;"></i>
            <span x-text="district"></span>
            <button type="button" class="chip-remove-btn" @click="remove('district')" title="Bỏ chọn khu vực">
              <i class="fa-solid fa-xmark"></i>
            </button>
          </span>

          {{-- 4. Hạng văn phòng --}}
          <span class="mo-active-filter-chip" x-show="Boolean(grade) && grade !== 'all'" style="display: none;">
            <i class="fa-solid fa-award" style="color:#f59e0b;font-size:11px;"></i>
            <span x-text="gradeMap[grade] || ('Hạng ' + grade)"></span>
            <button type="button" class="chip-remove-btn" @click="remove('grade')" title="Bỏ chọn hạng">
              <i class="fa-solid fa-xmark"></i>
            </button>
          </span>

          {{-- 5. Khoảng giá --}}
          <span class="mo-active-filter-chip" x-show="Boolean(price) && price !== 'all'" style="display: none;">
            <i class="fa-solid fa-coins" style="color:#8b5cf6;font-size:11px;"></i>
            <span x-text="priceMap[price] || price"></span>
            <button type="button" class="chip-remove-btn" @click="remove('price')" title="Bỏ chọn mức giá">
              <i class="fa-solid fa-xmark"></i>
            </button>
          </span>

          {{-- 6. Diện tích --}}
          <span class="mo-active-filter-chip" x-show="Boolean(area) && area !== 'all'" style="display: none;">
            <i class="fa-solid fa-vector-square" style="color:#0284c7;font-size:11px;"></i>
            <span x-text="areaMap[area] || area"></span>
            <button type="button" class="chip-remove-btn" @click="remove('area')" title="Bỏ chọn diện tích">
              <i class="fa-solid fa-xmark"></i>
            </button>
          </span>

          {{-- Nút Xóa tất cả bộ lọc --}}
          <button class="mo-chip-clear-all" type="button" @click="resetAll()" title="Đặt lại toàn bộ tiêu chí tìm kiếm">
            <i class="fa-solid fa-rotate-left"></i> Xóa tất cả bộ lọc
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Main Content Area -->
  <div class="container">
    <!-- Market Stats Strip (5 Cards from Real DB Data on 1 Row) -->
    <div class="mo-stats-strip">
      <div class="mo-stat-item">
        <span class="mo-stat-num">{{ number_format($stats['total_buildings'] ?: 39) }}+</span>
        <span class="mo-stat-label">Tòa nhà văn phòng toàn quốc</span>
      </div>
      <div class="mo-stat-item">
        <span class="mo-stat-num">{{ number_format($stats['total_area'] ?: 1068730, 0, ',', '.') }} m²</span>
        <span class="mo-stat-label">Tổng diện tích khả dụng</span>
      </div>
      <div class="mo-stat-item">
        <span class="mo-stat-num">{{ $stats['count_grade_a'] ?: 18 }} tòa</span>
        <span class="mo-stat-label">Chuẩn Hạng A & Chứng chỉ Xanh</span>
      </div>
      <div class="mo-stat-item">
        <span class="mo-stat-num">{{ $stats['total_provinces'] ?? 4 }} Khu vực</span>
        <span class="mo-stat-label">Hà Nội, TP.HCM, Đồng Nai, Đà Nẵng</span>
      </div>
      <div class="mo-stat-item">
        <span class="mo-stat-num">100%</span>
        <span class="mo-stat-label">Miễn phí dịch vụ tư vấn khách thuê</span>
      </div>
    </div>

    <div class="mo-content-grid">
      <!-- Left Sidebar Filters -->
      <aside class="mo-sidebar">
        <!-- Card 1: Khu Vực / Tỉnh Thành Trọng Điểm -->
        <div class="mo-sidebar-card">
          <h3 class="mo-sidebar-title"><i class="fa-solid fa-map-location-dot"></i> Tỉnh / Thành Trọng Điểm</h3>
          <div class="mo-filter-list">
            <div class="mo-filter-item {{ $province === '1' ? 'active' : '' }}" style="cursor:pointer;" wire:click="setProvince('1')">
              <span><i class="fa-solid fa-city" style="color:#2563eb;"></i> Thủ Đô Hà Nội</span>
              <span class="mo-filter-count">{{ $stats['count_hanoi'] ?: 10 }} tòa</span>
            </div>
            <div class="mo-filter-item {{ $province === '79' ? 'active' : '' }}" style="cursor:pointer;" wire:click="setProvince('79')">
              <span><i class="fa-solid fa-city" style="color:#ea580c;"></i> TP. Hồ Chí Minh</span>
              <span class="mo-filter-count">{{ $stats['count_hcm'] ?: 22 }} tòa</span>
            </div>
            <div class="mo-filter-item {{ $province === '75' ? 'active' : '' }}" style="cursor:pointer;" wire:click="setProvince('75')">
              <span><i class="fa-solid fa-industry" style="color:#10b981;"></i> Thành phố Đồng Nai</span>
              <span class="mo-filter-count">{{ $stats['count_dongnai'] ?: 4 }} tòa</span>
            </div>
            <div class="mo-filter-item {{ $province === '48' ? 'active' : '' }}" style="cursor:pointer;" wire:click="setProvince('48')">
              <span><i class="fa-solid fa-water" style="color:#6366f1;"></i> Thành phố Đà Nẵng</span>
              <span class="mo-filter-count">{{ $stats['count_danang'] ?: 3 }} tòa</span>
            </div>
          </div>
        </div>

        <!-- Card 2: Phân theo Hạng VP -->
        <div class="mo-sidebar-card">
          <h3 class="mo-sidebar-title"><i class="fa-solid fa-layer-group"></i> Phân theo Hạng VP</h3>
          <div class="mo-filter-list">
            <div class="mo-filter-item {{ $grade === 'A' ? 'active' : '' }}" style="cursor:pointer;" wire:click="setGrade('A')">
              <span><i class="fa-solid fa-award" style="color:#f59e0b;"></i> Văn phòng Hạng A</span>
              <span class="mo-filter-count">{{ $stats['count_grade_a'] ?: 21 }}</span>
            </div>
            <div class="mo-filter-item {{ $grade === 'B' ? 'active' : '' }}" style="cursor:pointer;" wire:click="setGrade('B')">
              <span><i class="fa-solid fa-building" style="color:#3b82f6;"></i> Văn phòng Hạng B</span>
              <span class="mo-filter-count">{{ $stats['count_grade_b'] ?: 14 }}</span>
            </div>
            <div class="mo-filter-item {{ $grade === 'C' ? 'active' : '' }}" style="cursor:pointer;" wire:click="setGrade('C')">
              <span><i class="fa-regular fa-building" style="color:#10b981;"></i> Văn phòng Hạng C & Giá rẻ</span>
              <span class="mo-filter-count">{{ $stats['count_grade_c'] ?: 4 }}</span>
            </div>
          </div>
        </div>

        <!-- Card 3: Quận / Khu vực nổi bật -->
        <div class="mo-sidebar-card">
          <h3 class="mo-sidebar-title"><i class="fa-solid fa-location-crosshairs"></i> Quận & Địa Bàn Tiêu Biểu</h3>
          <div class="mo-filter-list">
            <div class="mo-filter-item {{ $district === 'Quận 1' ? 'active' : '' }}" style="cursor:pointer;" wire:click="setDistrict('Quận 1')">
              <span>Quận 1 (TP.HCM)</span>
              <span class="mo-filter-count">{{ $stats['count_q1'] ?: 7 }} tòa</span>
            </div>
            <div class="mo-filter-item {{ $district === 'Quận Ba Đình' ? 'active' : '' }}" style="cursor:pointer;" wire:click="setDistrict('Quận Ba Đình')">
              <span>Quận Ba Đình (Hà Nội)</span>
              <span class="mo-filter-count">{{ $stats['count_badinh'] ?: 3 }} tòa</span>
            </div>
            <div class="mo-filter-item {{ $district === 'Quận Cầu Giấy' ? 'active' : '' }}" style="cursor:pointer;" wire:click="setDistrict('Quận Cầu Giấy')">
              <span>Quận Cầu Giấy (Hà Nội)</span>
              <span class="mo-filter-count">{{ $stats['count_caugiay'] ?: 2 }} tòa</span>
            </div>
            <div class="mo-filter-item {{ $district === 'Quận 3' ? 'active' : '' }}" style="cursor:pointer;" wire:click="setDistrict('Quận 3')">
              <span>Quận 3 (TP.HCM)</span>
              <span class="mo-filter-count">{{ $stats['count_q3'] ?: 3 }} tòa</span>
            </div>
            <div class="mo-filter-item {{ $district === 'Thành phố Biên Hòa' ? 'active' : '' }}" style="cursor:pointer;" wire:click="setDistrict('Thành phố Biên Hòa')">
              <span>TP. Biên Hòa (Đồng Nai)</span>
              <span class="mo-filter-count">{{ $stats['count_bienhoa'] ?: 4 }} tòa</span>
            </div>
            <div class="mo-filter-item {{ $district === 'Quận Hải Châu' ? 'active' : '' }}" style="cursor:pointer;" wire:click="setDistrict('Quận Hải Châu')">
              <span>Quận Hải Châu (Đà Nẵng)</span>
              <span class="mo-filter-count">{{ $stats['count_haichau'] ?: 3 }} tòa</span>
            </div>
          </div>
        </div>

        <!-- Direct Contact Box -->
        <div class="mo-consult-banner">
          <i class="fa-solid fa-headset" style="font-size:36px;color:#fdba74;margin-bottom:12px;"></i>
          <h4>Bạn cần báo giá toàn bộ tòa nhà phù hợp?</h4>
          <p>Chuyên viên sẽ gửi bảng so sánh chi tiết mặt bằng trống, giá thuê và phí quản lý trong vòng 30 phút.</p>
          <button class="btn-mo-orange" style="width:100%;justify-content:center;" type="button" onclick="openQuoteModal()">
            Gửi Yêu Cầu Báo Giá
          </button>
        </div>
      </aside>

      <!-- Right Content: Building Listings -->
      <div class="mo-listings-wrapper" id="mo-building-listings" style="scroll-margin-top: 90px;">
        <div class="mo-listings-header">
          <div class="mo-listings-title">
            <h2>Danh sách tòa nhà văn phòng cho thuê</h2>
            <p>
              Hiển thị {{ $buildings->total() }} tòa nhà văn phòng phù hợp với bộ lọc
              <span wire:loading wire:target="applySearch, sortBy" style="color:#ea580c;margin-left:8px;"><i class="fa-solid fa-spinner fa-spin"></i> Đang tải...</span>
            </p>
          </div>
          <div class="mo-sort-controls">
            <span style="font-size:13px;color:#64748b;"><i class="fa-solid fa-arrow-down-wide-short"></i> Sắp xếp:</span>
            <select class="mo-sort-select" wire:model.live="sortBy">
              <option value="featured">Đề xuất nổi bật</option>
              <option value="price-asc">Giá từ thấp đến cao</option>
              <option value="price-desc">Giá từ cao đến thấp</option>
              <option value="area-desc">Diện tích sàn lớn nhất</option>
            </select>
          </div>
        </div>

        <!-- Building Cards Grid -->
        <div class="mo-building-grid">
          @forelse($buildings as $b)
            @php
              $minPriceVnd = (float) ($b->min_rent_price_per_sqm ?: 0);
              $maxPriceVnd = (float) ($b->max_rent_price_per_sqm ?: 0);
              $image = $b->primaryImage?->image_url ?? $b->images->first()?->image_url ?? 'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?auto=format&fit=crop&w=800&q=80';
              $slug = $b->slug ?? \Illuminate\Support\Str::slug($b->name);
              $gradeLabel = 'Hạng ' . ($b->grade ?? 'A');
            @endphp
            <article class="mo-building-card" wire:key="building-card-{{ $b->id }}">
              <a href="{{ route('buildings.show', $slug) }}" style="display:block; text-decoration:none; color:inherit;">
                <div class="mo-card-thumb" style="cursor:pointer;">
                  <img src="{{ $image }}" alt="{{ $b->name }}">
                  <div class="mo-thumb-badges">
                    <span class="mo-badge-grade">{{ $gradeLabel }}</span>
                    <span class="mo-badge-status empty"><i class="fa-solid fa-circle" style="font-size:8px;"></i> Đang trống</span>
                  </div>
                </div>
              </a>
              <div class="mo-card-body">
                <span class="mo-card-district"><i class="fa-solid fa-location-dot"></i> {{ $b->district ?? 'TP.HCM' }}</span>
                <h3 class="mo-card-name">
                  <a href="{{ route('buildings.show', $slug) }}" style="text-decoration:none; color:inherit; display:inline-block;">{{ $b->name }}</a>
                </h3>
                <p class="mo-card-address">{{ $b->address }}</p>

                <div class="mo-card-specs">
                  <div class="mo-spec-item"><i class="fa-solid fa-expand"></i> Sàn: <strong>{{ number_format($b->total_leasable_area_sqm ? $b->total_leasable_area_sqm / ($b->total_floors ?: 20) : 1000) }} m²</strong></div>
                  <div class="mo-spec-item"><i class="fa-solid fa-layer-group"></i> Quy mô: <strong>{{ $b->total_floors ?: 25 }} Tầng</strong></div>
                  <div class="mo-spec-item"><i class="fa-solid fa-table-cells"></i> Cắt thuê: <strong>100 - {{ number_format($b->total_leasable_area_sqm ?: 1500) }} m²</strong></div>
                  <div class="mo-spec-item"><i class="fa-solid fa-shield-halved"></i> Tiêu chuẩn: <strong>Quốc tế</strong></div>
                </div>

                <div class="mo-card-pricing">
                  <div class="mo-price-box">
                    <span class="price-usd" style="font-size:18px;font-weight:800;color:#ea580c;">
                      {{ number_format($minPriceVnd, 0, ',', '.') }} đ@if($maxPriceVnd > $minPriceVnd) - {{ number_format($maxPriceVnd, 0, ',', '.') }} đ@endif
                    </span>
                    <span class="price-unit" style="font-size:13px;color:#64748b;font-weight:500;margin-left:4px;">/ m²</span>
                  </div>
                </div>
              </div>
            </article>
          @empty
            @php
              $currentRegionName = 'Toàn Quốc';
              if ($district !== 'all') {
                $currentRegionName = $district;
              } elseif ($province === '1') {
                $currentRegionName = 'Hà Nội';
              } elseif ($province === '79') {
                $currentRegionName = 'TP. Hồ Chí Minh';
              } elseif ($province === '48') {
                $currentRegionName = 'Đà Nẵng';
              } elseif ($province === '75') {
                $currentRegionName = 'Đồng Nai';
              } elseif ($province !== 'all') {
                $currentRegionName = is_numeric($province) ? ('Tỉnh thành mã ' . $province) : $province;
              } elseif ($keyword) {
                $currentRegionName = 'từ khóa "' . $keyword . '"';
              }
            @endphp
            <div class="mo-empty-lead-card">
              <span class="mo-empty-lead-badge">
                <i class="fa-solid fa-bell"></i> Kế hoạch thẩm định nguồn cung 2026
              </span>
              <h3 class="mo-empty-lead-title">
                Văn phòng cho thuê tại {{ $currentRegionName }} đang được thẩm định nguồn cung mới
              </h3>
              <p class="mo-empty-lead-desc">
                Hiện tại các diện tích văn phòng tại <strong>{{ $currentRegionName }}</strong> đã được các doanh nghiệp lấp đầy hoặc đội ngũ chuyên viên đang hoàn tất thẩm định pháp lý và biểu phí quản lý của các tòa nhà mới. Quý khách vui lòng để lại thông tin nhu cầu để được chuyên viên phụ trách khu vực ưu tiên gửi thông tin giữ chỗ ngay khi có mặt bằng trống phù hợp.
              </p>

              <div class="mo-empty-lead-form">
                <form id="lead-capture-form" onsubmit="handleEmptyLeadSubmit(event, '{{ addslashes($currentRegionName) }}')">
                  <div class="mo-empty-lead-grid">
                    <div class="mo-empty-input-group">
                      <label><i class="fa-solid fa-user"></i> Họ và tên của bạn</label>
                      <input type="text" id="empty-lead-name" class="mo-empty-input" placeholder="Ví dụ: Nguyễn Văn A" required>
                    </div>
                    <div class="mo-empty-input-group">
                      <label><i class="fa-solid fa-phone"></i> Số điện thoại nhận tin</label>
                      <input type="tel" id="empty-lead-phone" class="mo-empty-input" placeholder="0901 xxx xxx" required>
                    </div>
                    <div class="mo-empty-input-group">
                      <label><i class="fa-solid fa-expand"></i> Diện tích cần (m²)</label>
                      <input type="number" id="empty-lead-area" class="mo-empty-input" placeholder="Ví dụ: 150" min="20">
                    </div>
                    <div>
                      <button type="submit" class="btn-mo-orange" style="height:40px;width:100%;justify-content:center;">
                        <i class="fa-solid fa-paper-plane"></i> Đăng Ký Giữ Chỗ
                      </button>
                    </div>
                  </div>
                </form>
              </div>

              <div class="mo-empty-lead-footer">
                <button class="btn-mo-orange" type="button" wire:click="resetFilters" style="background:#f1f5f9;color:#334155;box-shadow:none;border:1px solid #cbd5e1;">
                  <i class="fa-solid fa-rotate-left"></i> Xem tất cả tòa nhà toàn quốc ({{ $stats['total_buildings'] ?? 39 }}+ tòa)
                </button>
                <div style="font-size:13.5px;color:#64748b;display:flex;align-items:center;gap:8px;">
                  <i class="fa-solid fa-headset" style="color:#ea580c;font-size:16px;"></i>
                  <span>Hotline ưu tiên 24/7: <strong style="color:var(--mo-primary);font-size:15px;">0901.888.789</strong> (Khảo sát miễn phí)</span>
                </div>
              </div>
            </div>
          @endforelse
        </div>

        <!-- Pagination (Maison Office Styled Livewire Pagination) -->
        {{ $buildings->links('livewire.pagination') }}
      </div>
    </div>
  </div>

  @script
  <script>
    $wire.on('scroll-to-listings', () => {
      setTimeout(() => {
        const el = document.getElementById('mo-building-listings');
        if (el) {
          el.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
      }, 100);
    });
  </script>
  @endscript
</div>
