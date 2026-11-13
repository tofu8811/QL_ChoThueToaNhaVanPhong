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
            <input type="text" wire:model.live.debounce.350ms="keyword" placeholder="Ví dụ: Vietcombank, Landmark, Lê Duẩn...">
          </div>
        </div>

        <!-- District -->
        <div class="mo-filter-group">
          <label class="mo-filter-label"><i class="fa-solid fa-location-dot"></i> Khu vực (Quận)</label>
          <select wire:model.live="district" class="mo-filter-select">
            <option value="all">Tất cả Quận / Huyện</option>
            <option value="Quận 1">Quận 1</option>
            <option value="Quận 3">Quận 3</option>
            <option value="Quận 7">Quận 7</option>
            <option value="Quận 10">Quận 10</option>
            <option value="Bình Thạnh">Bình Thạnh</option>
            <option value="Phú Nhuận">Phú Nhuận</option>
            <option value="TP Thủ Đức">TP. Thủ Đức</option>
          </select>
        </div>

        <!-- Grade -->
        <div class="mo-filter-group">
          <label class="mo-filter-label"><i class="fa-solid fa-medal"></i> Hạng văn phòng</label>
          <select wire:model.live="grade" class="mo-filter-select">
            <option value="all">Tất cả hạng</option>
            <option value="A">Hạng A / A+</option>
            <option value="B">Hạng B / B+</option>
            <option value="C">Hạng C / Giá rẻ</option>
          </select>
        </div>

        <!-- Price Range -->
        <div class="mo-filter-group">
          <label class="mo-filter-label"><i class="fa-solid fa-dollar-sign"></i> Khoảng giá ($/m²)</label>
          <select wire:model.live="price" class="mo-filter-select">
            <option value="all">Tất cả mức giá</option>
            <option value="under20">Dưới $20 / m²</option>
            <option value="20to35">$20 - $35 / m²</option>
            <option value="over35">Trên $35 / m²</option>
          </select>
        </div>

        <!-- Area Range -->
        <div class="mo-filter-group">
          <label class="mo-filter-label"><i class="fa-solid fa-vector-square"></i> Diện tích</label>
          <select wire:model.live="area" class="mo-filter-select">
            <option value="all">Tất cả diện tích</option>
            <option value="under150">Dưới 150 m²</option>
            <option value="150to500">150 - 500 m²</option>
            <option value="over500">Trên 500 m²</option>
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
        <button class="mo-chip {{ $district === 'Quận 1' ? 'active' : '' }}" type="button" wire:click="setDistrict('Quận 1')">Quận 1</button>
        <button class="mo-chip {{ $district === 'Quận 3' ? 'active' : '' }}" type="button" wire:click="setDistrict('Quận 3')">Quận 3</button>
        <button class="mo-chip {{ $district === 'Bình Thạnh' ? 'active' : '' }}" type="button" wire:click="setDistrict('Bình Thạnh')">Bình Thạnh</button>
        <button class="mo-chip {{ $grade === 'A' ? 'active' : '' }}" type="button" wire:click="setGrade('A')">Tòa nhà Hạng A</button>
        @if($keyword || $district !== 'all' || $grade !== 'all' || $price !== 'all' || $area !== 'all')
          <button class="mo-chip" type="button" wire:click="resetFilters" style="color:#ef4444;">
            <i class="fa-solid fa-xmark"></i> Xóa bộ lọc
          </button>
        @endif
      </div>
    </div>
  </div>

  <!-- Main Content Area -->
  <div class="container">
    <!-- Market Stats Strip -->
    <div class="mo-stats-strip">
      <div class="mo-stat-item">
        <span class="mo-stat-num">{{ number_format($stats['total_buildings'] ?: 22) }}+</span>
        <span class="mo-stat-label">Tòa nhà văn phòng TP.HCM</span>
      </div>
      <div class="mo-stat-item">
        <span class="mo-stat-num">{{ number_format($stats['total_area'] ?: 428430, 0, ',', '.') }} m²</span>
        <span class="mo-stat-label">Diện tích sàn đang chào thuê</span>
      </div>
      <div class="mo-stat-item">
        <span class="mo-stat-num">100%</span>
        <span class="mo-stat-label">Miễn phí dịch vụ tư vấn khách thuê</span>
      </div>
      <div class="mo-stat-item">
        <span class="mo-stat-num">{{ ($stats['total_tenants'] ?? 10) }}+</span>
        <span class="mo-stat-label">Doanh nghiệp đã đồng hành</span>
      </div>
    </div>

    <div class="mo-content-grid">
      <!-- Left Sidebar Filters -->
      <aside class="mo-sidebar">
        <div class="mo-sidebar-card">
          <h3 class="mo-sidebar-title"><i class="fa-solid fa-layer-group"></i> Phân theo Hạng VP</h3>
          <div class="mo-filter-list">
            <div class="mo-filter-item" style="cursor:pointer;" wire:click="setGrade('A')">
              <span><i class="fa-solid fa-award" style="color:#f59e0b;"></i> Văn phòng Hạng A</span>
              <span class="mo-filter-count">{{ $stats['count_grade_a'] ?: 7 }}</span>
            </div>
            <div class="mo-filter-item" style="cursor:pointer;" wire:click="setGrade('B')">
              <span><i class="fa-solid fa-building" style="color:#3b82f6;"></i> Văn phòng Hạng B</span>
              <span class="mo-filter-count">{{ $stats['count_grade_b'] ?: 11 }}</span>
            </div>
            <div class="mo-filter-item" style="cursor:pointer;" wire:click="setGrade('C')">
              <span><i class="fa-regular fa-building" style="color:#10b981;"></i> Văn phòng Hạng C & Giá rẻ</span>
              <span class="mo-filter-count">{{ $stats['count_grade_c'] ?: 4 }}</span>
            </div>
          </div>
        </div>

        <div class="mo-sidebar-card">
          <h3 class="mo-sidebar-title"><i class="fa-solid fa-map-location-dot"></i> Quận trung tâm nổi bật</h3>
          <div class="mo-filter-list">
            <div class="mo-filter-item" style="cursor:pointer;" wire:click="setDistrict('Quận 1')">
              <span>Quận 1 (Trung tâm tài chính)</span>
              <span class="mo-filter-count">{{ $stats['count_q1'] ?: 7 }} tòa</span>
            </div>
            <div class="mo-filter-item" style="cursor:pointer;" wire:click="setDistrict('Quận 3')">
              <span>Quận 3 (Khu ngoại giao)</span>
              <span class="mo-filter-count">{{ $stats['count_q3'] ?: 3 }} tòa</span>
            </div>
            <div class="mo-filter-item" style="cursor:pointer;" wire:click="setDistrict('Bình Thạnh')">
              <span>Bình Thạnh (Cửa ngõ Đông)</span>
              <span class="mo-filter-count">{{ $stats['count_bt'] ?: 3 }} tòa</span>
            </div>
            <div class="mo-filter-item" style="cursor:pointer;" wire:click="setDistrict('Quận 7')">
              <span>Quận 7 (Đô thị Phú Mỹ Hưng)</span>
              <span class="mo-filter-count">{{ $stats['count_q7'] ?: 2 }} tòa</span>
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
            <div style="grid-column: 1 / -1; background:#fff; border:1px solid #e2e8f0; border-radius:12px; padding:40px; text-align:center;">
              <i class="fa-solid fa-building-circle-exclamation" style="font-size:44px; color:#94a3b8; margin-bottom:12px;"></i>
              <h3 style="font-size:18px; color:var(--mo-primary); margin-bottom:6px;">Không tìm thấy tòa nhà phù hợp</h3>
              <p style="color:#64748b; font-size:14px; margin-bottom:16px;">Vui lòng thử bỏ bớt bộ lọc hoặc chọn khoảng giá/diện tích khác.</p>
              <button class="btn-mo-orange" type="button" wire:click="resetFilters">Đặt lại bộ lọc</button>
            </div>
          @endforelse
        </div>

        <!-- Pagination (Maison Office Styled Livewire Pagination) -->
        {{ $buildings->links('livewire.pagination') }}
      </div>
    </div>
  </div>
</div>
