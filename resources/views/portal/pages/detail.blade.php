    <main id="page-detail" class="page-section">
      <div class="container" style="padding-top: 24px; padding-bottom: 50px;">
        <div class="mo-breadcrumbs">
          <a href="#" onclick="showPublicPage('page-home')">Trang chủ</a>
          <i class="fa-solid fa-chevron-right" style="font-size:10px;"></i>
          <a href="#" onclick="showPublicPage('page-home')">Văn phòng cho thuê TP.HCM</a>
          <i class="fa-solid fa-chevron-right" style="font-size:10px;"></i>
          <span id="detail-breadcrumb-name">Vietcombank Tower</span>
        </div>

        <!-- Detail Hero Grid -->
        <div class="detail-hero-grid">
          <!-- Gallery -->
          <div class="detail-gallery">
            <div class="detail-main-img">
              <img id="detail-hero-img" src="https://images.unsplash.com/photo-1577495508048-b635879837f1?auto=format&fit=crop&w=1200&q=80" alt="Building">
            </div>
            <div class="detail-sub-thumbs">
              <img class="active" src="https://images.unsplash.com/photo-1577495508048-b635879837f1?auto=format&fit=crop&w=600&q=80" onclick="changeDetailImg(this.src)">
              <img src="https://images.unsplash.com/photo-1497366216548-37526070297c?auto=format&fit=crop&w=600&q=80" onclick="changeDetailImg(this.src)">
              <img src="https://images.unsplash.com/photo-1497366811353-6870744d04b2?auto=format&fit=crop&w=600&q=80" onclick="changeDetailImg(this.src)">
              <img src="https://images.unsplash.com/photo-1504384308090-c894fdcc538d?auto=format&fit=crop&w=600&q=80" onclick="changeDetailImg(this.src)">
            </div>
          </div>

          <!-- Quick Specs & Action -->
          <div class="detail-info-card">
            <span class="badge-filament info" id="detail-badge-grade"><i class="fa-solid fa-award"></i> Tòa nhà Hạng A</span>
            <span class="badge-filament success" id="detail-badge-status" style="margin-left: 6px;"><i class="fa-solid fa-circle-check"></i> Đang trống</span>
            <h1 id="detail-name" style="font-size: 26px; font-weight: 800; color: var(--mo-primary); margin-top: 10px;">Vietcombank Tower</h1>
            <p id="detail-address" style="font-size: 14px; color: #64748b; margin-top: 4px;"><i class="fa-solid fa-location-dot" style="color:#ea580c;"></i> Số 5 Công Trường Mê Linh, Phường Bến Nghé, Quận 1, TP.HCM</p>

            <div class="detail-price-highlight">
              <div style="font-size: 13px; color: #64748b; font-weight: 500;">Giá chào thuê chính thức:</div>
              <div style="font-size: 26px; font-weight: 800; color: #ea580c;" id="detail-price-text">$48 - $54 / m² / tháng</div>
              <div style="font-size: 12px; color: #94a3b8;">(Khoảng 1.180.000đ - 1.330.000đ/m² - Chưa bao gồm VAT & PQL)</div>
            </div>

            <!-- Specs Table -->
            <table class="detail-specs-table">
              <tr>
                <td><i class="fa-solid fa-layer-group"></i> Kết cấu tòa nhà</td>
                <td id="detail-spec-structure">35 Tầng nổi - 4 Tầng hầm</td>
              </tr>
              <tr>
                <td><i class="fa-solid fa-expand"></i> Diện tích sàn điển hình</td>
                <td id="detail-spec-floorplate">1.100 m² / sàn</td>
              </tr>
              <tr>
                <td><i class="fa-solid fa-ruler-vertical"></i> Chiều cao trần</td>
                <td>2.75 mét (tiêu chuẩn hạng A)</td>
              </tr>
              <tr>
                <td><i class="fa-solid fa-elevator"></i> Hệ thống thang máy</td>
                <td>16 Thang máy Schindler tốc độ cao</td>
              </tr>
              <tr>
                <td><i class="fa-solid fa-snowflake"></i> Hệ thống điều hòa</td>
                <td>Máy lạnh trung tâm Chiller Carrier thông minh</td>
              </tr>
              <tr>
                <td><i class="fa-solid fa-shield-virus"></i> Máy phát điện dự phòng</td>
                <td>100% Công suất tự động chuyển mạch</td>
              </tr>
            </table>

            <div style="display: flex; gap: 10px; margin-top: 20px;">
              <button class="btn-mo-orange" style="flex: 1; justify-content: center;" type="button" onclick="bookSurveyFromDetail()">
                <i class="fa-regular fa-calendar-check"></i> Đặt Lịch Khảo Sát
              </button>
              <button class="btn-mo-outline" type="button" onclick="openQuoteModal()">
                <i class="fa-solid fa-file-pdf"></i> Tải Báo Giá PDF
              </button>
            </div>
          </div>
        </div>

        <!-- Nhúng Livewire Component Mặt Bằng Trống (Module 1) -->
        <livewire:floor-vacancy-list :buildingId="''" />

        <!-- Bản đồ vị trí tòa nhà -->
        <div class="detail-floors-card">
          <h2 style="font-size: 20px; font-weight: 800; color: var(--mo-primary);"><i class="fa-solid fa-map-pin"></i> Vị Trí Tòa Nhà Trên Bản Đồ TP. Hồ Chí Minh</h2>
          <p style="font-size: 13.5px; color: #64748b;">Tọa lạc tại vị trí đắc địa, giao thông thuận tiện kết nối các tuyến đường huyết mạch</p>
          <div class="detail-map-mock">
            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3919.460195574523!2d106.7037743152605!3d10.775253862148153!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31752f447a192137%3A0xb35a09ad7b26002f!2sVietcombank%20Tower!5e0!3m2!1svi!2s!4v1689000000000!5m2!1svi!2s" loading="lazy"></iframe>
          </div>
        </div>
      </div>
    </main>
