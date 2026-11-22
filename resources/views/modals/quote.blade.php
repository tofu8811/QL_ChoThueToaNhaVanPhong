<!-- Fast Quote Modal -->
    <div id="quote-modal" class="mo-modal">
      <div class="mo-modal-box">
        <div class="mo-modal-head">
          <h3 style="font-size:17px;font-weight:700;"><i class="fa-solid fa-file-invoice-dollar"></i> Nhận Báo Giá Văn Phòng Nhanh (30 Phút)</h3>
          <i class="fa-solid fa-xmark" style="cursor:pointer;font-size:20px;" onclick="closeQuoteModal()"></i>
        </div>
        <div class="mo-modal-body">
          <p style="color:#64748b;font-size:13.5px;margin-bottom:16px;">Vui lòng để lại thông tin, đội ngũ chuyên viên Maison Office sẽ liên hệ tư vấn diện tích trống và gửi bảng giá chi tiết kèm ưu đãi tốt nhất.</p>
          <form onsubmit="handleQuickQuoteModal(event)">
            <div style="margin-bottom:14px;">
              <label style="display:block;font-size:13px;font-weight:600;margin-bottom:6px;">Họ tên của bạn *</label>
              <input type="text" id="modal-name" required placeholder="Ví dụ: Nguyễn Văn A" style="width:100%;padding:10px 14px;border:1px solid #cbd5e1;border-radius:6px;">
            </div>
            <div style="margin-bottom:14px;">
              <label style="display:block;font-size:13px;font-weight:600;margin-bottom:6px;">Số điện thoại / Zalo *</label>
              <input type="tel" id="modal-phone" required placeholder="090x xxx xxx" style="width:100%;padding:10px 14px;border:1px solid #cbd5e1;border-radius:6px;">
            </div>
            <div style="margin-bottom:14px;">
              <label style="display:block;font-size:13px;font-weight:600;margin-bottom:6px;">Tòa nhà hoặc khu vực quan tâm</label>
              <input type="text" id="modal-target" placeholder="Ví dụ: Vietcombank Tower hoặc Quận 1" style="width:100%;padding:10px 14px;border:1px solid #cbd5e1;border-radius:6px;">
            </div>
            <div style="margin-bottom:20px;">
              <label style="display:block;font-size:13px;font-weight:600;margin-bottom:6px;">Diện tích ước tính (m²)</label>
              <select id="modal-area" style="width:100%;padding:10px 14px;border:1px solid #cbd5e1;border-radius:6px;">
                <option value="50 - 100 m²">50 - 100 m² (10 - 20 nhân sự)</option>
                <option value="100 - 250 m²" selected>100 - 250 m² (20 - 50 nhân sự)</option>
                <option value="250 - 500 m²">250 - 500 m² (50 - 100 nhân sự)</option>
                <option value="500 - 1000 m²">500 - 1.000 m² (100 - 200 nhân sự)</option>
                <option value="Trên 1.000 m²">Trên 1.000 m² (Nguyên sàn / Nhiều tầng)</option>
              </select>
            </div>
            <button type="submit" class="btn-mo-orange" style="width:100%;justify-content:center;padding:12px;">
              <i class="fa-solid fa-paper-plane"></i> Gửi Yêu Cầu Nhận Báo Giá
            </button>
            <div style="text-align:center;margin-top:12px;">
              <a href="{{ route('quote.page') }}" style="font-size:12.5px;color:var(--mo-primary);text-decoration:none;font-weight:600;">
                <i class="fa-solid fa-arrow-up-right-from-square"></i> Mở Trang Đăng Ký Khảo Sát Chi Tiết
              </a>
            </div>
          </form>
        </div>
      </div>
    </div>
