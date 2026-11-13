<div>
  <!-- Advanced Filter Floating Box -->
  <div class="container mo-filter-container" id="mo-building-filter-section">
    <div class="mo-filter-box">
      <div class="mo-filter-tabs">
        <div class="mo-filter-tab active"><i class="fa-solid fa-building"></i> Cho Thuê Văn Phòng</div>
        <div class="mo-filter-tab" onclick="alert('Đã chọn phân hệ Văn Phòng Trọn Gói / Coworking Space!')"><i class="fa-solid fa-briefcase"></i> Văn Phòng Trọn Gói</div>
        <div class="mo-filter-tab" onclick="alert('Đã chọn phân hệ Tòa Nhà Nguyên Căn!')"><i class="fa-solid fa-city"></i> Tòa Nhà Nguyên Căn</div>
      </div>

      <div class="mo-filter-grid">
        <!-- Search Keyword -->
        <div class="mo-filter-group">
          <label class="mo-filter-label"><i class="fa-solid fa-magnifying-glass"></i> Tên tòa nhà hoặc đường</label>
          <div class="mo-input-wrap">
            <i class="fa-solid fa-search"></i>
            <input type="text" wire:model.live.debounce.350ms="keyword" placeholder="Ví dụ: Capital Place, Keangnam, Nexus, Pegasus...">
          </div>
        </div>

        <!-- Province / Thành phố -->
        <div class="mo-filter-group">
          <label class="mo-filter-label"><i class="fa-solid fa-map-location-dot"></i> Tỉnh / Thành phố</label>
          <select wire:model.live="province" class="mo-filter-select">
            <option value="all">Toàn quốc (Tất cả khu vực)</option>
            <option value="1">Thành phố Hà Nội</option>
            <option value="79">TP. Hồ Chí Minh</option>
            <option value="75">Thành phố Đồng Nai</option>
            <option value="48">Thành phố Đà Nẵng</option>
          </select>
        </div>

        <!-- District / Quận Huyện -->
        <div class="mo-filter-group">
          <label class="mo-filter-label"><i class="fa-solid fa-location-dot"></i> Quận / Huyện / Khu vực</label>
          <select wire:model.live="district" class="mo-filter-select">
            <option value="all">Tất cả Quận / Huyện</option>

            @if($province === '1')
              <!-- Chỉ hiển thị quận tại Hà Nội -->
              <optgroup label="Thành phố Hà Nội">
                <option value="Quận Ba Đình">Quận Ba Đình (3 tòa)</option>
                <option value="Quận Cầu Giấy">Quận Cầu Giấy (2 tòa)</option>
                <option value="Quận Hoàn Kiếm">Quận Hoàn Kiếm (2 tòa)</option>
                <option value="Quận Nam Từ Liêm">Quận Nam Từ Liêm (1 tòa)</option>
                <option value="Quận Hai Bà Trưng">Quận Hai Bà Trưng (1 tòa)</option>
                <option value="Huyện Gia Lâm">Huyện Gia Lâm (1 tòa)</option>
              </optgroup>
            @elseif($province === '79')
              <!-- Chỉ hiển thị quận tại TP.HCM -->
              <optgroup label="TP. Hồ Chí Minh">
                <option value="Quận 1">Quận 1 (Trung tâm) (7 tòa)</option>
                <option value="Quận 3">Quận 3 (3 tòa)</option>
                <option value="Bình Thạnh">Quận Bình Thạnh (3 tòa)</option>
                <option value="Quận 7">Quận 7 (2 tòa)</option>
                <option value="Quận 10">Quận 10 (2 tòa)</option>
                <option value="Phú Nhuận">Quận Phú Nhuận (2 tòa)</option>
                <option value="TP Thủ Đức">TP. Thủ Đức (1 tòa)</option>
                <option value="Tân Bình">Quận Tân Bình (1 tòa)</option>
                <option value="Quận 4">Quận 4 (1 tòa)</option>
              </optgroup>
            @elseif($province === '75')
              <!-- Chỉ hiển thị Đồng Nai -->
              <optgroup label="Thành phố Đồng Nai">
                <option value="Thành phố Biên Hòa">TP. Biên Hòa (4 tòa)</option>
              </optgroup>
            @elseif($province === '48')
              <!-- Chỉ hiển thị Đà Nẵng -->
              <optgroup label="Thành phố Đà Nẵng">
                <option value="Quận Hải Châu">Quận Hải Châu (3 tòa)</option>
              </optgroup>
            @else
              <!-- Hiển thị toàn bộ nhóm quận theo từng Tỉnh/Thành -->
              <optgroup label="Thủ đô Hà Nội">
                <option value="Quận Ba Đình">Ba Đình</option>
                <option value="Quận Cầu Giấy">Cầu Giấy</option>
                <option value="Quận Hoàn Kiếm">Hoàn Kiếm</option>
                <option value="Quận Nam Từ Liêm">Nam Từ Liêm</option>
                <option value="Quận Hai Bà Trưng">Hai Bà Trưng</option>
                <option value="Huyện Gia Lâm">Gia Lâm</option>
              </optgroup>
              <optgroup label="TP. Hồ Chí Minh">
                <option value="Quận 1">Quận 1</option>
                <option value="Quận 3">Quận 3</option>
                <option value="Quận 7">Quận 7</option>
                <option value="Bình Thạnh">Bình Thạnh</option>
                <option value="TP Thủ Đức">TP. Thủ Đức</option>
                <option value="Quận 10">Quận 10</option>
                <option value="Phú Nhuận">Phú Nhuận</option>
              </optgroup>
              <optgroup label="Đồng Nai">
                <option value="Thành phố Biên Hòa">TP. Biên Hòa</option>
              </optgroup>
              <optgroup label="Đà Nẵng">
                <option value="Quận Hải Châu">Quận Hải Châu</option>
              </optgroup>
            @endif
          </select>
        </div>

        <!-- Grade -->
        <div class="mo-filter-group">
          <label class="mo-filter-label"><i class="fa-solid fa-medal"></i> Hạng văn phòng</label>
          <select wire:model.live="grade" class="mo-filter-select">
            <option value="all">Tất cả hạng</option>
            <option value="A">Hạng A / A+ (Quốc tế & Xanh)</option>
            <option value="B">Hạng B / B+ (Hiện đại)</option>
            <option value="C">Hạng C / Tiêu chuẩn</option>
          </select>
        </div>

        <!-- Price Range -->
        <div class="mo-filter-group">
          <label class="mo-filter-label"><i class="fa-solid fa-dollar-sign"></i> Khoảng giá ($/m²)</label>
          <select wire:model.live="price" class="mo-filter-select">
            <option value="all">Tất cả mức giá</option>
            <option value="under20">Dưới $20 / m² (Kinh tế)</option>
            <option value="20to35">$20 - $35 / m² (Phổ biến)</option>
            <option value="over35">Trên $35 / m² (Hạng A VIP)</option>
          </select>
        </div>

        <!-- Area Range -->
        <div class="mo-filter-group">
          <label class="mo-filter-label"><i class="fa-solid fa-vector-square"></i> Diện tích</label>
          <select wire:model.live="area" class="mo-filter-select">
            <option value="all">Tất cả diện tích</option>
            <option value="under150">Dưới 150 m²</option>
            <option value="150to500">150 - 500 m²</option>
            <option value="over500">Trên 500 m² (Nguyên sàn)</option>
          </select>
        </div>

        <!-- Reset Button -->
        <div>
          <button class="btn-filter-submit" type="button" wire:click="resetFilters">
            <i class="fa-solid fa-rotate-left"></i> Đặt Lại
          </button>
        </div>
      </div>

      <!-- Quick Filter Chips -->
      <div class="mo-quick-tags">
        <span class="mo-quick-tag-label"><i class="fa-solid fa-tags"></i> Gợi ý nhanh:</span>
        <button class="mo-chip {{ $province === '1' ? 'active' : '' }}" type="button" wire:click="setProvince('1')">
          <i class="fa-solid fa-city" style="color:#2563eb;"></i> Hà Nội
        </button>
        <button class="mo-chip {{ $province === '79' ? 'active' : '' }}" type="button" wire:click="setProvince('79')">
          <i class="fa-solid fa-city" style="color:#ea580c;"></i> TP.HCM
        </button>
        <button class="mo-chip {{ $province === '75' ? 'active' : '' }}" type="button" wire:click="setProvince('75')">
          <i class="fa-solid fa-industry" style="color:#10b981;"></i> Đồng Nai
        </button>
        <button class="mo-chip {{ $province === '48' ? 'active' : '' }}" type="button" wire:click="setProvince('48')">
          <i class="fa-solid fa-water" style="color:#6366f1;"></i> Đà Nẵng
        </button>
        <button class="mo-chip {{ $grade === 'A' ? 'active' : '' }}" type="button" wire:click="setGrade('A')">
          <i class="fa-solid fa-award" style="color:#f59e0b;"></i> Tòa nhà Hạng A
        </button>
        <button class="mo-chip {{ $district === 'Quận 1' ? 'active' : '' }}" type="button" wire:click="setDistrict('Quận 1')">
          Quận 1
        </button>
        <button class="mo-chip {{ $district === 'Quận Ba Đình' ? 'active' : '' }}" type="button" wire:click="setDistrict('Quận Ba Đình')">
          Ba Đình
        </button>
        <button class="mo-chip {{ $district === 'Thành phố Biên Hòa' ? 'active' : '' }}" type="button" wire:click="setDistrict('Thành phố Biên Hòa')">
          Biên Hòa
        </button>
        @if($keyword || $province !== 'all' || $district !== 'all' || $grade !== 'all' || $price !== 'all' || $area !== 'all')
          <button class="mo-chip" type="button" wire:click="resetFilters" style="color:#ef4444;font-weight:700;">
            <i class="fa-solid fa-xmark"></i> Xóa tất cả bộ lọc
          </button>
        @endif
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
      <div class="mo-listings-wrapper">
        <div class="mo-listings-header">
          <div class="mo-listings-title">
            <h2>Danh sách tòa nhà văn phòng cho thuê</h2>
            <p>
              Hiển thị {{ $buildings->total() }} tòa nhà văn phòng phù hợp với bộ lọc
              <span wire:loading style="color:#ea580c;margin-left:8px;"><i class="fa-solid fa-spinner fa-spin"></i> Đang tải...</span>
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
              $minUsd = $b->min_rent_price_per_sqm ? round($b->min_rent_price_per_sqm / 24000) : 25;
              $maxUsd = $b->max_rent_price_per_sqm ? round($b->max_rent_price_per_sqm / 24000) : ($minUsd + 10);
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
                    <span class="price-usd">${{ $minUsd }} - ${{ $maxUsd }}</span>
                    <span class="price-unit">/ m² / tháng</span>
                    <div class="price-vnd">Khoảng {{ number_format($b->min_rent_price_per_sqm ?: ($minUsd * 24000), 0, ',', '.') }} đ / m²</div>
                  </div>
                  <div class="mo-card-actions">
                    <button class="btn-card-quote" type="button" onclick="openQuoteForBuilding('{{ addslashes($b->name) }}')">Nhận Báo Giá</button>
                    <a href="{{ route('buildings.show', $slug) }}" class="btn-card-view" style="text-decoration:none; display:inline-flex; align-items:center; justify-content:center;">Xem Chi Tiết</a>
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
</div>
