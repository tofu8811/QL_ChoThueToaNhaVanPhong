    <main id="page-quote" class="page-section">
      <div class="container" style="padding-top: 35px; padding-bottom: 60px;">
        <div class="lead-form-wrap">
          <div style="text-align: center; margin-bottom: 24px;">
            <span class="role-switcher-badge"><i class="fa-solid fa-handshake"></i> Tiếp Nhận Yêu Cầu Thuê Văn Phòng</span>
            <h1 style="font-size: 28px; font-weight: 800; color: var(--mo-primary); margin-top: 8px;">Đăng Ký Nhận Báo Giá & Đặt Lịch Khảo Sát</h1>
            <p style="color: #64748b; font-size: 14px; margin-top: 4px;">Điền thông tin nhu cầu doanh nghiệp. Chuyên viên tư vấn sẽ liên hệ và gửi hồ sơ báo giá trong vòng 30 phút.</p>
          </div>

          <form id="lead-consult-form" onsubmit="handleLeadSubmit(event)">
            <div class="lead-form-grid">
              <div class="form-field">
                <label>Họ và tên người liên hệ <span style="color:#ef4444;">*</span></label>
                <input type="text" id="quote-name" required placeholder="Nguyễn Văn A">
              </div>
              <div class="form-field">
                <label>Số điện thoại liên hệ <span style="color:#ef4444;">*</span></label>
                <input type="tel" id="quote-phone" required placeholder="0901 234 567">
              </div>
              <div class="form-field">
                <label>Email doanh nghiệp <span style="color:#ef4444;">*</span></label>
                <input type="email" id="quote-email" required placeholder="contact@company.com">
              </div>
              <div class="form-field">
                <label>Tên công ty / Doanh nghiệp <span style="color:#ef4444;">*</span></label>
                <input type="text" id="quote-company" required placeholder="Công ty TNHH ABC Việt Nam">
              </div>

              <div class="form-field">
                <label>Tòa nhà hoặc Khu vực quan tâm</label>
                <select id="quote-building">
                  <option value="">-- Chọn tòa nhà bạn quan tâm --</option>
                  @foreach($serverBuildings ?? [] as $b)
                    <option value="{{ $b['name'] }} - {{ $b['district'] }}">{{ $b['name'] }} ({{ $b['district'] }} - ${{ $b['priceUsd'] ?? 30 }}/m²)</option>
                  @endforeach
                </select>
              </div>

              <div class="form-field">
                <label>Diện tích cần tìm (m²)</label>
                <select id="quote-area-need">
                  <option value="Dưới 100 m²">Dưới 100 m² (Quy mô 10 - 20 nhân sự)</option>
                  <option value="100 - 250 m²" selected>100 - 250 m² (Quy mô 20 - 45 nhân sự)</option>
                  <option value="250 - 500 m²">250 - 500 m² (Quy mô 50 - 90 nhân sự)</option>
                  <option value="500 - 1000 m²">500 - 1000 m² (Quy mô 100 - 200 nhân sự)</option>
                  <option value="Trên 1000 m²">Trên 1000 m² (Tập đoàn / Thuê nhiều sàn)</option>
                </select>
              </div>

              <div class="form-field">
                <label>Ngân sách dự kiến ($/m²/tháng)</label>
                <input type="text" id="quote-budget" placeholder="Ví dụ: $20 - $35/m²">
              </div>

              <div class="form-field">
                <label>Thời gian dự kiến chuyển vào</label>
                <input type="date" id="quote-date">
              </div>

              <div class="form-field full">
                <label>Yêu cầu chi tiết khác (Vị trí, hướng, số chỗ đỗ xe...)</label>
                <textarea id="quote-notes" rows="4" placeholder="Ví dụ: Cần bàn giao hoàn thiện cơ bản, có phòng họp sẵn, ưu tiên tầng cao view sông..."></textarea>
              </div>
            </div>

            <div style="margin-top: 24px; text-align: center;">
              <button type="submit" class="btn-mo-orange" style="font-size: 15px; padding: 12px 36px;">
                <i class="fa-solid fa-paper-plane"></i> Gửi Yêu Cầu Cho Nhân Viên Tư Vấn
              </button>
            </div>
          </form>
        </div>
      </div>
    </main>

    <!-- Maison Office Footer -->
