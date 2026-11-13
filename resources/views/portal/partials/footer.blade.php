    <footer class="mo-footer">
      <div class="container">
        <div class="mo-footer-grid">
          <div class="mo-footer-col">
            <div class="mo-logo" style="margin-bottom: 14px;">
              <div class="mo-logo-icon" style="background:#ea580c;">
                <i class="fa-solid fa-building-wheat"></i>
              </div>
              <div class="mo-logo-text">
                <h1 style="color:#ffffff;">Office</h1>
              </div>
            </div>
            <p style="line-height: 1.6; margin-bottom: 14px;">Office - Nền tảng quản lý và tư vấn cho thuê cao ốc văn phòng hàng đầu TP.HCM. Cung cấp giải pháp tìm kiếm mặt bằng thông minh và số hóa quy trình quản trị tòa nhà trọn gói.</p>
            <p><i class="fa-solid fa-location-dot" style="color:#ea580c;"></i> Văn phòng TP.HCM: 65 Huỳnh Thúc Kháng, Bến Nghé, Quận 1, TP.HCM</p>
          </div>

          <div class="mo-footer-col">
            <h4>Khu Vực & Tỉnh Thành</h4>
            <ul>
              <li><a href="{{ route('home', ['province' => '1']) }}" onclick="return handleHeaderFilter(event, 'province', '1', '{{ route('home', ['province' => '1']) }}')">Văn phòng Hà Nội (Ba Đình, Cầu Giấy)</a></li>
              <li><a href="{{ route('home', ['province' => '79']) }}" onclick="return handleHeaderFilter(event, 'province', '79', '{{ route('home', ['province' => '79']) }}')">Văn phòng TP.HCM (Quận 1, Quận 3)</a></li>
              <li><a href="{{ route('home', ['province' => '75']) }}" onclick="return handleHeaderFilter(event, 'province', '75', '{{ route('home', ['province' => '75']) }}')">Văn phòng Đồng Nai (Biên Hòa)</a></li>
              <li><a href="{{ route('home', ['province' => '48']) }}" onclick="return handleHeaderFilter(event, 'province', '48', '{{ route('home', ['province' => '48']) }}')">Văn phòng Đà Nẵng (Hải Châu)</a></li>
              <li><a href="{{ route('home', ['district' => 'Bình Thạnh']) }}" onclick="return handleHeaderFilter(event, 'district', 'Bình Thạnh', '{{ route('home', ['district' => 'Bình Thạnh']) }}')">Cho thuê văn phòng Bình Thạnh</a></li>
            </ul>
          </div>

          <div class="mo-footer-col">
            <h4>Phân Loại Hạng & Giá</h4>
            <ul>
              <li><a href="{{ route('home', ['grade' => 'A']) }}" onclick="return handleHeaderFilter(event, 'grade', 'A', '{{ route('home', ['grade' => 'A']) }}')">Tòa nhà văn phòng Hạng A (21 tòa)</a></li>
              <li><a href="{{ route('home', ['grade' => 'B']) }}" onclick="return handleHeaderFilter(event, 'grade', 'B', '{{ route('home', ['grade' => 'B']) }}')">Tòa nhà văn phòng Hạng B (14 tòa)</a></li>
              <li><a href="{{ route('home', ['grade' => 'C']) }}" onclick="return handleHeaderFilter(event, 'grade', 'C', '{{ route('home', ['grade' => 'C']) }}')">Văn phòng Hạng C & Tiêu chuẩn</a></li>
              <li><a href="{{ route('home') }}" onclick="return handleHeaderFilter(event, 'reset', null, '{{ route('home') }}')">Tất cả tòa nhà toàn quốc</a></li>
              <li><a href="{{ route('quote.page') }}">Bảng giá thuê văn phòng 2026</a></li>
            </ul>
          </div>

          <div class="mo-footer-col">
            <h4>Hỗ Trợ & Dịch Vụ</h4>
            <ul>
              <li><a href="#" onclick="openQuoteModal()">Tư vấn tìm văn phòng miễn phí</a></li>
              <li><a href="#" onclick="openQuoteModal()">Đặt lịch khảo sát thực tế</a></li>
              <li><a href="#" onclick="showPublicPage('page-home')">Bảng giá thuê văn phòng 2026</a></li>
              <li><a href="#" onclick="openQuoteModal()">Quy trình thuê & Hợp đồng pháp lý</a></li>
            </ul>
          </div>
        </div>

        <div class="mo-footer-bottom" style="justify-content: center; text-align: center;">
          <div>Hệ thống Quản lý và Cho thuê Văn phòng</div>
        </div>
      </div>
    </footer>
  </div>

  <!-- ==========================================================================
       VIEW B: FILAMENT PHP ADMIN PANEL (QUẢN TRỊ 4 TÁC NHÂN: ADMIN, SALES, BQL, TENANT)
