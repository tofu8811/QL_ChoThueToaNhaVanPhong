    <main id="page-home" class="page-section active">
      <!-- Hero Banner -->
      <section class="mo-hero-banner">
        <div class="container">
          <div class="mo-breadcrumbs">
            <a href="#">Trang chủ</a>
            <i class="fa-solid fa-chevron-right" style="font-size:10px;"></i>
            <span>Cho thuê văn phòng TP.HCM</span>
          </div>
          <h1 class="mo-hero-title">Cho Thuê Văn Phòng TP.HCM - Cập Nhật Báo Giá & Diện Tích Mới Nhất 2026</h1>
          <p class="mo-hero-desc">Hệ thống khảo sát và phân phối mặt bằng văn phòng chuyên nghiệp tại TP. Hồ Chí Minh. Dữ liệu thực tế hơn {{ $stats['total_buildings'] ?? 22 }}+ cao ốc từ Hạng A, B, C và Giá rẻ. Tư vấn miễn phí và hỗ trợ thương lượng giá tốt nhất cho doanh nghiệp.</p>
        </div>
      </section>

      <!-- Advanced Filter Floating Box -->
      <div class="container mo-filter-container">
        <div class="mo-filter-box">
          <div class="mo-filter-tabs">
            <div class="mo-filter-tab active"><i class="fa-solid fa-building"></i> Cho Thuê Văn Phòng</div>
            <div class="mo-filter-tab" onclick="alert('Đã chọn phân hệ Văn Phòng Trọn Gói / Coworking Space!')"><i class="fa-solid fa-briefcase"></i> Văn Phòng Trọn Gói</div>
            <div class="mo-filter-tab" onclick="alert('Đã chọn phân hệ Tòa Nhà Nguyên Căn!')"><i class="fa-solid fa-city"></i> Tòa Nhà Nguyên Căn</div>
          </div>

          <div class="mo-filter-grid">
            <!-- Search Text -->
            <div class="mo-filter-group">
              <label class="mo-filter-label"><i class="fa-solid fa-magnifying-glass"></i> Tên tòa nhà hoặc đường</label>
              <div class="mo-input-wrap">
                <i class="fa-solid fa-search"></i>
                <input type="text" id="filter-keyword" placeholder="Ví dụ: Vietcombank, Landmark, Lê Duẩn..." oninput="applyFilters()">
              </div>
            </div>

            <!-- District -->
            <div class="mo-filter-group">
              <label class="mo-filter-label"><i class="fa-solid fa-location-dot"></i> Khu vực (Quận)</label>
              <select id="filter-district" class="mo-filter-select" onchange="applyFilters()">
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
              <select id="filter-grade" class="mo-filter-select" onchange="applyFilters()">
                <option value="all">Tất cả hạng</option>
                <option value="Hạng A">Hạng A / A+</option>
                <option value="Hạng B">Hạng B / B+</option>
                <option value="Hạng C">Hạng C / Giá rẻ</option>
              </select>
            </div>

            <!-- Price -->
            <div class="mo-filter-group">
              <label class="mo-filter-label"><i class="fa-solid fa-dollar-sign"></i> Khoảng giá ($/m²)</label>
              <select id="filter-price" class="mo-filter-select" onchange="applyFilters()">
                <option value="all">Tất cả mức giá</option>
                <option value="under20">Dưới $20 / m²</option>
                <option value="20to35">$20 - $35 / m²</option>
                <option value="over35">Trên $35 / m²</option>
              </select>
            </div>

            <!-- Area -->
            <div class="mo-filter-group">
              <label class="mo-filter-label"><i class="fa-solid fa-vector-square"></i> Diện tích</label>
              <select id="filter-area" class="mo-filter-select" onchange="applyFilters()">
                <option value="all">Tất cả diện tích</option>
                <option value="under150">Dưới 150 m²</option>
                <option value="150to500">150 - 500 m²</option>
                <option value="over500">Trên 500 m²</option>
              </select>
            </div>

            <!-- Submit Button -->
            <div>
              <button class="btn-filter-submit" onclick="applyFilters()">
                <i class="fa-solid fa-filter"></i> Lọc Tòa Nhà
              </button>
            </div>
          </div>

          <!-- Quick Filter Chips -->
          <div class="mo-quick-tags">
            <span class="mo-quick-tag-label"><i class="fa-solid fa-tags"></i> Gợi ý nhanh:</span>
            <button class="mo-chip" onclick="quickFilterDistrict('Quận 1')">Quận 1</button>
            <button class="mo-chip" onclick="quickFilterDistrict('Quận 3')">Quận 3</button>
            <button class="mo-chip" onclick="quickFilterDistrict('Bình Thạnh')">Bình Thạnh</button>
            <button class="mo-chip" onclick="quickFilterGrade('Hạng A')">Tòa nhà Hạng A</button>
            <button class="mo-chip" onclick="quickFilterStatus('Đang trống')">Sẵn sàng bàn giao (Đang trống)</button>
            <button class="mo-chip" onclick="quickFilterStatus('Sắp trống')">Sắp trống tháng tới</button>
            <button class="mo-chip" onclick="resetAllFilters()" style="color:#ef4444;"><i class="fa-solid fa-rotate-left"></i> Đặt lại</button>
          </div>
        </div>
      </div>

      <!-- Main Content Area -->
      <div class="container">
        <!-- Market Stats Strip (Dữ liệu thống kê thật từ Database) -->
        <div class="mo-stats-strip">
          <div class="mo-stat-item">
            <span class="mo-stat-num">{{ number_format($stats['total_buildings'] ?? 22) }}+</span>
            <span class="mo-stat-label">Tòa nhà văn phòng TP.HCM</span>
          </div>
          <div class="mo-stat-item">
            <span class="mo-stat-num">{{ number_format($stats['total_leasable_area'] ?? 428430, 0, ',', '.') }}+ m²</span>
            <span class="mo-stat-label">Diện tích sàn đang chào thuê</span>
          </div>
          <div class="mo-stat-item">
            <span class="mo-stat-num">100%</span>
            <span class="mo-stat-label">Miễn phí dịch vụ tư vấn khách thuê</span>
          </div>
          <div class="mo-stat-item">
            <span class="mo-stat-num">{{ number_format($stats['total_tenants'] ?? 10) }}+</span>
            <span class="mo-stat-label">Doanh nghiệp đã đồng hành</span>
          </div>
        </div>

        <div class="mo-content-grid">
          <!-- Left Sidebar Filters & Lead Assistance -->
          <aside class="mo-sidebar">
            <div class="mo-sidebar-card">
              <h3 class="mo-sidebar-title"><i class="fa-solid fa-layer-group"></i> Phân theo Hạng VP</h3>
              <div class="mo-filter-list">
                <div class="mo-filter-item" onclick="quickFilterGrade('Hạng A')">
                  <span><i class="fa-solid fa-award" style="color:#f59e0b;"></i> Văn phòng Hạng A</span>
                  <span class="mo-filter-count">{{ $stats['by_grade']['A'] ?? 7 }}</span>
                </div>
                <div class="mo-filter-item" onclick="quickFilterGrade('Hạng B')">
                  <span><i class="fa-solid fa-building" style="color:#3b82f6;"></i> Văn phòng Hạng B</span>
                  <span class="mo-filter-count">{{ $stats['by_grade']['B'] ?? 11 }}</span>
                </div>
                <div class="mo-filter-item" onclick="quickFilterGrade('Hạng C')">
                  <span><i class="fa-regular fa-building" style="color:#10b981;"></i> Văn phòng Hạng C & Giá rẻ</span>
                  <span class="mo-filter-count">{{ $stats['by_grade']['C'] ?? 4 }}</span>
                </div>
              </div>
            </div>

            <div class="mo-sidebar-card">
              <h3 class="mo-sidebar-title"><i class="fa-solid fa-map-location-dot"></i> Quận trung tâm nổi bật</h3>
              <div class="mo-filter-list">
                <div class="mo-filter-item" onclick="quickFilterDistrict('Quận 1')">
                  <span>Quận 1 (Trung tâm tài chính)</span>
                  <span class="mo-filter-count">{{ $stats['by_district']['Quận 1'] ?? 7 }} tòa</span>
                </div>
                <div class="mo-filter-item" onclick="quickFilterDistrict('Quận 3')">
                  <span>Quận 3 (Khu ngoại giao)</span>
                  <span class="mo-filter-count">{{ $stats['by_district']['Quận 3'] ?? 3 }} tòa</span>
                </div>
                <div class="mo-filter-item" onclick="quickFilterDistrict('Bình Thạnh')">
                  <span>Bình Thạnh (Cửa ngõ Đông)</span>
                  <span class="mo-filter-count">{{ $stats['by_district']['Bình Thạnh'] ?? 3 }} tòa</span>
                </div>
                <div class="mo-filter-item" onclick="quickFilterDistrict('Quận 7')">
                  <span>Quận 7 (Đô thị Phú Mỹ Hưng)</span>
                  <span class="mo-filter-count">{{ $stats['by_district']['Quận 7'] ?? 2 }} tòa</span>
                </div>
                <div class="mo-filter-item" onclick="quickFilterDistrict('TP Thủ Đức')">
                  <span>TP. Thủ Đức (Khu công nghệ cao)</span>
                  <span class="mo-filter-count">{{ $stats['by_district']['TP Thủ Đức'] ?? 1 }} tòa</span>
                </div>
              </div>
            </div>

            <!-- Direct Contact Box -->
            <div class="mo-consult-banner">
              <i class="fa-solid fa-headset" style="font-size:36px;color:#fdba74;margin-bottom:12px;"></i>
              <h4>Bạn cần báo giá toàn bộ tòa nhà phù hợp?</h4>
              <p>Chuyên viên sẽ gửi bảng so sánh chi tiết mặt bằng trống, giá thuê và phí quản lý trong vòng 30 phút.</p>
              <button class="btn-mo-orange" style="width:100%;justify-content:center;" onclick="openQuoteModal()">
                Gửi Yêu Cầu Báo Giá
              </button>
            </div>
          </aside>

          <!-- Right Content: Building Listings -->
          <div class="mo-listings-wrapper">
            <div class="mo-listings-header">
              <div class="mo-listings-title">
                <h2>Danh sách tòa nhà văn phòng cho thuê</h2>
                <p id="listing-count-text">Hiển thị 8 tòa nhà văn phòng tiêu biểu tại TP. Hồ Chí Minh</p>
              </div>
              <div class="mo-sort-controls">
                <span style="font-size:13px;color:#64748b;"><i class="fa-solid fa-arrow-down-wide-short"></i> Sắp xếp:</span>
                <select class="mo-sort-select" id="sort-selector" onchange="sortBuildings(this.value)">
                  <option value="featured">Đề xuất nổi bật</option>
                  <option value="price-asc">Giá từ thấp đến cao</option>
                  <option value="price-desc">Giá từ cao đến thấp</option>
                  <option value="area-desc">Diện tích sàn lớn nhất</option>
                </select>
              </div>
            </div>

            <!-- Building Cards Grid -->
            <div class="mo-building-grid" id="building-cards-container">
              <!-- Rendered via Javascript -->
            </div>

            <!-- Pagination Controls (Phân trang danh sách tòa nhà) -->
            <div class="mo-pagination-wrapper" id="pagination-wrapper">
              <div class="mo-pagination-info" id="pagination-info-text">
                Trang <strong>1</strong> / <strong>4</strong> (Hiển thị <strong>1 - 6</strong> / 22 tòa nhà)
              </div>
              
              <div class="mo-pagination-nav" id="pagination-controls">
                <!-- Rendered via Javascript -->
              </div>

              <div class="mo-pagination-pagesize">
                <span>Hiển thị:</span>
                <select id="pagesize-select" onchange="changePageSize(this.value)">
                  <option value="6" selected>6 tòa nhà / trang</option>
                  <option value="9">9 tòa nhà / trang</option>
                  <option value="12">12 tòa nhà / trang</option>
                  <option value="18">18 tòa nhà / trang</option>
                </select>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>

    <!-- PAGE 2: BUILDING DETAIL (Xem chi tiết thông số, mặt bằng cắt thuê, bản đồ) -->
