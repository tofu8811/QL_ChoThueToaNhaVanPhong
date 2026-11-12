<!-- ==========================================================================
     MODULE 4: TICKETS & MAINTENANCE MODALS (ENDPOINTS 58 - 66)
     ========================================================================== -->

<!-- MODAL 58: TẠO YÊU CẦU HỖ TRỢ / SỰ CỐ MỚI (TENANT TICKET) -->
<div id="ticket-modal" class="mo-modal">
  <div class="mo-modal-box">
    <div class="mo-modal-head">
      <h3 style="font-size:17px;font-weight:700;"><i class="fa-solid fa-headset"></i> Gửi Yêu Cầu Hỗ Trợ & Sự Cố (Tenant Ticket)</h3>
      <i class="fa-solid fa-xmark" style="cursor:pointer;font-size:20px;" onclick="closeModal('ticket-modal')"></i>
    </div>
    <div class="mo-modal-body">
          <form onsubmit="handleTenantNewTicket(event)">
            <div style="margin-bottom:14px;">
              <label style="display:block;font-size:13px;font-weight:600;margin-bottom:6px;">Tòa nhà văn phòng của bạn *</label>
              <select id="ticket-building-id" required style="width:100%;padding:10px 14px;border:1px solid #cbd5e1;border-radius:6px;font-weight:600;">
                <option value="0192e220-0010-7000-8000-000000000001">Tòa Nhà The Nexus Tower (Tôn Đức Thắng, Quận 1)</option>
                <option value="0192e220-0010-7000-8000-000000000002">Tòa Nhà Saigon Centre Tower 2 (Lê Lợi, Quận 1)</option>
                <option value="0192e220-0010-7000-8000-000000000003">Tòa Nhà Vietcombank Tower (Quận 1)</option>
                <option value="0192e220-0010-7000-8000-000000000004">Tòa Nhà Deutsches Haus (Lê Duẩn, Quận 1)</option>
              </select>
            </div>
            <div style="margin-bottom:14px;">
              <label style="display:block;font-size:13px;font-weight:600;margin-bottom:6px;">Tiêu đề sự cố / yêu cầu *</label>
              <input type="text" id="ticket-title" required placeholder="VD: Hỏng bóng đèn led hành lang, Điều hòa phát tiếng kêu..." style="width:100%;padding:10px 14px;border:1px solid #cbd5e1;border-radius:6px;">
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px;">
              <div>
                <label style="display:block;font-size:13px;font-weight:600;margin-bottom:6px;">Danh mục sự cố</label>
                <select id="ticket-category" style="width:100%;padding:10px 14px;border:1px solid #cbd5e1;border-radius:6px;">
                  <option value="MAINTENANCE" selected>Sửa chữa kỹ thuật / Điện lạnh</option>
                  <option value="OVERTIME_CONSTRUCTION">Đăng ký thi công ngoài giờ</option>
                  <option value="SECURITY_CLEANING">Vệ sinh & An ninh tòa nhà</option>
                  <option value="SERVICE_COMPLAINT">Khiếu nại chất lượng dịch vụ</option>
                </select>
              </div>
              <div>
                <label style="display:block;font-size:13px;font-weight:600;margin-bottom:6px;">Mức độ ưu tiên</label>
                <select id="ticket-priority" style="width:100%;padding:10px 14px;border:1px solid #cbd5e1;border-radius:6px;">
                  <option value="NORMAL">Bình thường</option>
                  <option value="HIGH">Cao</option>
                  <option value="URGENT">Khẩn cấp</option>
                  <option value="LOW">Thấp</option>
                </select>
              </div>
            </div>
            <div style="margin-bottom:14px;">
              <label style="display:block;font-size:13px;font-weight:600;margin-bottom:6px;">Mô tả hiện trạng chi tiết *</label>
              <textarea id="ticket-desc" required rows="3" placeholder="Mô tả cụ thể vị trí phòng, tầng, thời điểm bắt đầu xảy ra sự cố..." style="width:100%;padding:10px 14px;border:1px solid #cbd5e1;border-radius:6px;"></textarea>
            </div>
            <div style="margin-bottom:18px;">
              <label style="display:block;font-size:13px;font-weight:600;margin-bottom:6px;">Ảnh hiện trạng ban đầu (Link ảnh URL tùy chọn)</label>
              <input type="url" id="ticket-image-url" placeholder="https://images.unsplash.com/... hoặc để trống" style="width:100%;padding:9px 14px;border:1px solid #cbd5e1;border-radius:6px;">
            </div>
            <button type="submit" id="btn-submit-create-ticket" class="btn-mo-orange" style="width:100%;justify-content:center;padding:12px;">
              <i class="fa-solid fa-paper-plane"></i> Gửi Phiếu Sự Cố Đến Ban Quản Lý (Tạo Ticket)
            </button>
          </form>
    </div>
  </div>
</div>

<!-- ==========================================================================
         MODULE 4: TICKET DETAIL & ACTION MODALS (ENDPOINTS 59 - 66)
         ========================================================================== -->

    <!-- MODAL 59: CHI TIẾT TIẾN TRÌNH TICKET & LỊCH SỬ -->
    <div id="ticket-detail-modal" class="mo-modal">
      <div class="ticket-detail-modal-box">
        <!-- Header -->
        <div class="login-modal-header" style="padding: 16px 22px;">
          <div style="display:flex; justify-content:space-between; align-items:center;">
            <div>
              <div style="display:flex; align-items:center; gap:8px;">
                <span class="ticket-badge-pill" id="td-status-badge">TRẠNG THÁI</span>
                <span class="ticket-badge-pill" id="td-priority-badge">ƯU TIÊN</span>
                <span style="font-size:12px; color:#94a3b8; font-weight:700;" id="td-code">#TK-XXXX</span>
              </div>
              <h3 class="login-modal-title" style="font-size:17px; margin-top:6px;" id="td-title">Tiêu đề sự cố</h3>
            </div>
            <div class="login-modal-close" onclick="closeTicketDetailModal()">
              <i class="fa-solid fa-xmark"></i>
            </div>
          </div>
        </div>

        <!-- Body -->
        <div class="profile-modal-body" style="padding: 18px 22px;">
          <div class="ticket-grid-2col">
            <!-- Cột trái: Thông tin sự cố -->
            <div>
              <div class="ticket-section-title"><i class="fa-solid fa-circle-info" style="color:#2563eb;"></i> Thông Tin Chi Tiết Sự Cố</div>
              <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; padding:14px; font-size:12.5px; line-height:1.6;">
                <div style="display:grid; grid-template-columns: 110px 1fr; gap:6px 10px; margin-bottom:10px;">
                  <span style="color:#64748b;">Tòa nhà:</span>
                  <strong style="color:#0f3361;" id="td-building-name">-</strong>
                  <span style="color:#64748b;">Mặt bằng / Vị trí:</span>
                  <span id="td-unit-info">-</span>
                  <span style="color:#64748b;">Khách thuê:</span>
                  <strong id="td-tenant-name">-</strong>
                  <span style="color:#64748b;">Danh mục:</span>
                  <span id="td-category-name">-</span>
                  <span style="color:#64748b;">Kỹ thuật phụ trách:</span>
                  <span id="td-pm-name" style="color:#2563eb; font-weight:600;">Chưa phân công</span>
                  <span style="color:#64748b;">Hạn dự kiến:</span>
                  <span id="td-expected-date">-</span>
                  <span style="color:#64748b;">Thời gian tạo:</span>
                  <span id="td-created-at">-</span>
                </div>
                <div style="border-top:1px dashed #cbd5e1; padding-top:10px; margin-top:10px;">
                  <strong style="color:#334155; display:block; margin-bottom:4px;">Nội dung mô tả hiện trường:</strong>
                  <p id="td-description" style="color:#475569; margin:0; white-space:pre-line;">-</p>
                </div>
                <div id="td-resolution-box" style="display:none; border-top:1px dashed #cbd5e1; padding-top:10px; margin-top:10px; background:#f0fdf4; padding:10px; border-radius:6px;">
                  <strong style="color:#16a34a; display:block; margin-bottom:4px;"><i class="fa-solid fa-check-double"></i> Kết quả khắc phục từ BQL:</strong>
                  <p id="td-resolution-summary" style="color:#15803d; margin:0;">-</p>
                </div>
              </div>

              <!-- Thư viện ảnh bằng chứng Trước / Sau (Proof of work) -->
              <div style="margin-top:16px;">
                <div style="display:flex; justify-content:space-between; align-items:center;">
                  <div class="ticket-section-title" style="margin:0; border:none;"><i class="fa-solid fa-camera-retro" style="color:#f59e0b;"></i> Hình Ảnh Bằng Chứng (Before / After)</div>
                  <button type="button" class="btn-mo-outline" style="padding:4px 8px; font-size:11.5px;" onclick="openUploadProofModal()">
                    <i class="fa-solid fa-cloud-arrow-up"></i> Tải ảnh lên
                  </button>
                </div>
                <div class="proof-gallery-grid" id="td-proof-images-list">
                  <!-- Rendered via JS -->
                </div>
              </div>

              <!-- CSAT Rating (Nếu đã đánh giá) -->
              <div id="td-csat-box" style="display:none; margin-top:16px; background:#fffbeb; border:1px solid #fde68a; border-radius:8px; padding:12px;">
                <div style="display:flex; justify-content:space-between; align-items:center;">
                  <strong style="font-size:13px; color:#b45309;"><i class="fa-solid fa-star"></i> Đánh giá của khách thuê (CSAT):</strong>
                  <div class="star-rating-box" id="td-csat-stars" style="font-size:18px;"></div>
                </div>
                <p id="td-csat-feedback" style="font-size:12.5px; color:#92400e; margin:6px 0 0 0; font-style:italic;">-</p>
              </div>
            </div>

            <!-- Cột phải: Dòng thời gian tiến trình xử lý (Timeline) -->
            <div>
              <div class="ticket-section-title"><i class="fa-solid fa-clock-rotate-left" style="color:#8b5cf6;"></i> Nhật Ký Tiến Trình Xử Lý</div>
              <div class="ticket-timeline" id="td-timeline-list">
                <!-- Rendered via JS -->
              </div>
            </div>
          </div>

          <!-- Bottom Action Buttons Bar -->
          <div style="margin-top:20px; padding-top:14px; border-top:1px solid #e2e8f0; display:flex; justify-content:flex-end; gap:8px; flex-wrap:wrap;" id="td-actions-bar">
            <!-- Rendered dynamically by current user role and ticket status -->
          </div>
        </div>
      </div>
    </div>

    <!-- MODAL 62: TIẾP NHẬN & PHÂN CÔNG NHÂN VIÊN KỸ THUẬT (PM / ADMIN) -->
    <div id="ticket-assign-modal" class="mo-modal">
      <div class="mo-modal-box" style="max-width:500px;">
        <div class="mo-modal-head">
          <h3 style="font-size:16px;font-weight:700;"><i class="fa-solid fa-user-gear" style="color:#2563eb;"></i> Phân Công Nhân Viên Kỹ Thuật (PM)</h3>
          <i class="fa-solid fa-xmark" style="cursor:pointer;font-size:20px;" onclick="closeTicketAssignModal()"></i>
        </div>
        <div class="mo-modal-body">
          <form onsubmit="handleAssignTicketSubmit(event)">
            <input type="hidden" id="assign-ticket-id">
            <div style="margin-bottom:14px;">
              <label style="display:block;font-size:13px;font-weight:600;margin-bottom:6px;">Chọn Kỹ sư / Quản lý phụ trách *</label>
              <select id="assign-pm-id" required style="width:100%;padding:10px 14px;border:1px solid #cbd5e1;border-radius:6px;">
                <option value="0192e220-0003-7000-8000-000000000001">Đỗ Minh Quang (NV-BQL-001 - Kỹ sư Trưởng)</option>
                <option value="0192e220-0003-7000-8000-000000000002">Ngô Văn Minh (NV-BQL-002 - Kỹ thuật Điện lạnh)</option>
                <option value="0192e220-0003-7000-8000-000000000003">Bùi Tiến Dũng (NV-BQL-003 - Kỹ thuật Tòa nhà)</option>
              </select>
            </div>
            <div style="margin-bottom:14px;">
              <label style="display:block;font-size:13px;font-weight:600;margin-bottom:6px;">Thời gian dự kiến hoàn tất</label>
              <input type="datetime-local" id="assign-expected-date" style="width:100%;padding:9px 14px;border:1px solid #cbd5e1;border-radius:6px;">
            </div>
            <div style="margin-bottom:18px;">
              <label style="display:block;font-size:13px;font-weight:600;margin-bottom:6px;">Ghi chú điều phối</label>
              <textarea id="assign-note" rows="2" placeholder="Ghi chú phân công công việc, thiết bị cần mang..." style="width:100%;padding:9px 14px;border:1px solid #cbd5e1;border-radius:6px;">Ban quản lý tiếp nhận yêu cầu và điều phối kỹ thuật viên kiểm tra xử lý.</textarea>
            </div>
            <button type="submit" id="btn-submit-assign" class="btn-mo-primary" style="width:100%;justify-content:center;padding:11px;background:#2563eb;color:#fff;">
              <i class="fa-solid fa-check"></i> Xác Nhận Phân Công & Bắt Đầu Xử Lý (IN_PROGRESS)
            </button>
          </form>
        </div>
      </div>
    </div>

    <!-- MODAL 63: CẬP NHẬT TIẾN ĐỘ SỬA CHỮA (ON_HOLD / RESOLVED) -->
    <div id="ticket-status-modal" class="mo-modal">
      <div class="mo-modal-box" style="max-width:520px;">
        <div class="mo-modal-head">
          <h3 style="font-size:16px;font-weight:700;"><i class="fa-solid fa-list-check" style="color:#059669;"></i> Cập Nhật Tiến Độ Xử Lý Ticket</h3>
          <i class="fa-solid fa-xmark" style="cursor:pointer;font-size:20px;" onclick="closeTicketStatusModal()"></i>
        </div>
        <div class="mo-modal-body">
          <form onsubmit="handleUpdateTicketStatusSubmit(event)">
            <input type="hidden" id="status-ticket-id">
            <div style="margin-bottom:14px;">
              <label style="display:block;font-size:13px;font-weight:600;margin-bottom:6px;">Chọn trạng thái cập nhật *</label>
              <select id="status-new-value" required onchange="handleStatusSelectionChange(this.value)" style="width:100%;padding:10px 14px;border:1px solid #cbd5e1;border-radius:6px;font-weight:600;">
                <option value="RESOLVED" selected>RESOLVED - Đã khắc phục xong (Chờ khách nghiệm thu)</option>
                <option value="ON_HOLD">ON_HOLD - Tạm hoãn (Chờ phụ tùng / vật tư)</option>
                <option value="IN_PROGRESS">IN_PROGRESS - Đang tiếp tục xử lý</option>
              </select>
            </div>

            <!-- Trường dành cho RESOLVED -->
            <div id="status-resolved-group" style="margin-bottom:14px;">
              <label style="display:block;font-size:13px;font-weight:600;margin-bottom:6px;color:#16a34a;">Tóm tắt cách khắc phục & sửa chữa *</label>
              <textarea id="status-resolution-summary" rows="3" placeholder="Mô tả các bước kỹ thuật đã làm, linh kiện thay thế..." style="width:100%;padding:9px 14px;border:1px solid #cbd5e1;border-radius:6px;">Đã hoàn thành kiểm tra và xử lý triệt để sự cố. Thiết bị hoạt động ổn định bình thường.</textarea>
            </div>

            <!-- Trường dành cho ON_HOLD -->
            <div id="status-hold-group" style="display:none; margin-bottom:14px;">
              <label style="display:block;font-size:13px;font-weight:600;margin-bottom:6px;color:#7e22ce;">Lý do tạm hoãn *</label>
              <input type="text" id="status-hold-reason" placeholder="VD: Đang đặt hàng bo mạch chính hãng từ nhà máy..." style="width:100%;padding:9px 14px;border:1px solid #cbd5e1;border-radius:6px;">
            </div>

            <div style="margin-bottom:18px;">
              <label style="display:block;font-size:13px;font-weight:600;margin-bottom:6px;">Ghi chú gửi kèm</label>
              <input type="text" id="status-note" placeholder="Thông điệp ghi nhận vào lịch sử tiến trình..." style="width:100%;padding:9px 14px;border:1px solid #cbd5e1;border-radius:6px;">
            </div>

            <button type="submit" id="btn-submit-status" class="btn-mo-primary" style="width:100%;justify-content:center;padding:11px;background:#059669;color:#fff;">
              <i class="fa-solid fa-floppy-disk"></i> Lưu Cập Nhật Tiến Độ
            </button>
          </form>
        </div>
      </div>
    </div>

    <!-- MODAL 64: TẢI ẢNH BẰNG CHỨNG (BEFORE / AFTER) -->
    <div id="ticket-upload-proof-modal" class="mo-modal">
      <div class="mo-modal-box" style="max-width:500px;">
        <div class="mo-modal-head">
          <h3 style="font-size:16px;font-weight:700;"><i class="fa-solid fa-cloud-arrow-up" style="color:#2563eb;"></i> Tải Lên Ảnh Bằng Chứng Sự Cố</h3>
          <i class="fa-solid fa-xmark" style="cursor:pointer;font-size:20px;" onclick="closeUploadProofModal()"></i>
        </div>
        <div class="mo-modal-body">
          <form onsubmit="handleUploadProofSubmit(event)">
            <input type="hidden" id="proof-ticket-id">
            <div style="margin-bottom:14px;">
              <label style="display:block;font-size:13px;font-weight:600;margin-bottom:6px;">Loại bằng chứng *</label>
              <select id="proof-type" required style="width:100%;padding:10px 14px;border:1px solid #cbd5e1;border-radius:6px;font-weight:600;">
                <option value="BEFORE">BEFORE - Hiện trạng hư hỏng ban đầu</option>
                <option value="AFTER" selected>AFTER - Kết quả sau khi đã sửa chữa xong</option>
                <option value="LOG_ATTACHMENT">LOG_ATTACHMENT - Biên bản kiểm tra / Hóa đơn phụ tùng</option>
              </select>
            </div>
            <div style="margin-bottom:14px;">
              <label style="display:block;font-size:13px;font-weight:600;margin-bottom:6px;">Đường dẫn ảnh trực tuyến (Image URL)</label>
              <input type="url" id="proof-url" placeholder="https://images.unsplash.com/... hoặc link ảnh cloud" style="width:100%;padding:9px 14px;border:1px solid #cbd5e1;border-radius:6px;">
            </div>
            <div style="margin-bottom:14px;">
              <label style="display:block;font-size:13px;font-weight:600;margin-bottom:6px;">Hoặc chọn ảnh từ thiết bị</label>
              <input type="file" id="proof-file" accept="image/*" style="width:100%;padding:6px;border:1px solid #cbd5e1;border-radius:6px;font-size:13px;">
            </div>
            <div style="margin-bottom:18px;">
              <label style="display:block;font-size:13px;font-weight:600;margin-bottom:6px;">Ghi chú / Chú thích ảnh</label>
              <input type="text" id="proof-caption" placeholder="VD: Ảnh chụp dàn lạnh đã thay phin lọc..." style="width:100%;padding:9px 14px;border:1px solid #cbd5e1;border-radius:6px;">
            </div>
            <button type="submit" id="btn-submit-proof" class="btn-mo-orange" style="width:100%;justify-content:center;padding:11px;">
              <i class="fa-solid fa-upload"></i> Tải Ảnh Lên Hệ Thống
            </button>
          </form>
        </div>
      </div>
    </div>

    <!-- MODAL 65: KHÁCH THUÊ XÁC NHẬN NGHIỆM THU -->
    <div id="ticket-confirm-modal" class="mo-modal">
      <div class="mo-modal-box" style="max-width:500px;">
        <div class="mo-modal-head">
          <h3 style="font-size:16px;font-weight:700;"><i class="fa-solid fa-stamp" style="color:#059669;"></i> Xác Nhận Nghiệm Thu Kết Quả Sửa Chữa</h3>
          <i class="fa-solid fa-xmark" style="cursor:pointer;font-size:20px;" onclick="closeTicketConfirmModal()"></i>
        </div>
        <div class="mo-modal-body">
          <p style="color:#475569;font-size:13px;margin-bottom:16px;">Vui lòng kiểm tra thực tế hiện trường. Nếu sự cố đã được khắc phục hoàn hảo, bạn bấm <strong>Đồng Ý Nghiệm Thu</strong>. Nếu chưa đạt, bạn có thể <strong>Yêu Cầu Sửa Lại</strong>.</p>
          <input type="hidden" id="confirm-ticket-id">
          <div style="margin-bottom:16px;">
            <label style="display:block;font-size:13px;font-weight:600;margin-bottom:6px;">Ý kiến phản hồi / Ghi nhận hiện trường:</label>
            <textarea id="confirm-feedback" rows="3" placeholder="Nhập nhận xét hoặc lý do nếu cần sửa lại..." style="width:100%;padding:9px 14px;border:1px solid #cbd5e1;border-radius:6px;">Đã nghiệm thu hiện trường, kỹ thuật xử lý tốt.</textarea>
          </div>
          <div style="display:flex;gap:10px;">
            <button type="button" class="btn-mo-primary" style="flex:1;justify-content:center;background:#059669;color:#fff;padding:11px;" onclick="submitTicketConfirm(true)">
              <i class="fa-solid fa-circle-check"></i> Đồng Ý Nghiệm Thu (COMPLETED)
            </button>
            <button type="button" class="btn-mo-outline" style="justify-content:center;color:#dc2626;border-color:#fca5a5;padding:11px;" onclick="submitTicketConfirm(false)">
              <i class="fa-solid fa-rotate-left"></i> Yêu Cầu Sửa Lại
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- MODAL 66: ĐÁNH GIÁ CHẤT LƯỢNG CSAT -->
    <div id="ticket-rate-modal" class="mo-modal">
      <div class="mo-modal-box" style="max-width:480px; text-align:center;">
        <div class="mo-modal-head" style="justify-content:center; position:relative;">
          <h3 style="font-size:17px;font-weight:700;color:#0f3361;"><i class="fa-solid fa-star" style="color:#f59e0b;"></i> Đánh Giá Dịch Vụ Ban Quản Lý</h3>
          <i class="fa-solid fa-xmark" style="position:absolute; right:18px; cursor:pointer; font-size:20px;" onclick="closeTicketRateModal()"></i>
        </div>
        <div class="mo-modal-body" style="padding:22px;">
          <p style="color:#64748b;font-size:13px;margin-bottom:16px;">Bạn hài lòng với tốc độ và chất lượng phục vụ của đội ngũ kỹ thuật chứ?</p>
          <input type="hidden" id="rate-ticket-id">
          
          <!-- 5 Stars Interactor -->
          <div class="star-rating-box" id="rate-stars-input" style="font-size:34px; margin-bottom:16px;">
            <i class="fa-solid fa-star active" data-star="1" onclick="setCsatStar(1)"></i>
            <i class="fa-solid fa-star active" data-star="2" onclick="setCsatStar(2)"></i>
            <i class="fa-solid fa-star active" data-star="3" onclick="setCsatStar(3)"></i>
            <i class="fa-solid fa-star active" data-star="4" onclick="setCsatStar(4)"></i>
            <i class="fa-solid fa-star active" data-star="5" onclick="setCsatStar(5)"></i>
          </div>
          <div id="rate-star-label" style="font-weight:700; color:#ea580c; font-size:14px; margin-bottom:16px;">5 sao - Rất hài lòng</div>

          <div style="text-align:left; margin-bottom:18px;">
            <label style="display:block;font-size:13px;font-weight:600;margin-bottom:6px;">Ý kiến đóng góp thêm:</label>
            <textarea id="rate-feedback" rows="3" placeholder="Đóng góp ý kiến để BQL nâng cao chất lượng phục vụ hơn nữa..." style="width:100%;padding:10px 14px;border:1px solid #cbd5e1;border-radius:6px;">Ban quản lý xử lý rất nhanh và tận tâm!</textarea>
          </div>

          <button type="button" id="btn-submit-rate" class="btn-mo-orange" style="width:100%;justify-content:center;padding:12px;" onclick="submitCsatRating()">
            <i class="fa-solid fa-paper-plane"></i> Gửi Đánh Giá Dịch Vụ
          </button>
        </div>
      </div>
    </div>

    <!-- MODAL 60: CẬP NHẬT NỘI DUNG TICKET (KHI CÒN OPEN) -->
    <div id="ticket-edit-modal" class="mo-modal">
      <div class="mo-modal-box" style="max-width:540px;">
        <div class="mo-modal-head">
          <h3 style="font-size:16px;font-weight:700;"><i class="fa-solid fa-pen-to-square" style="color:#2563eb;"></i> Chỉnh Sửa Nội Dung Phiếu Hỗ Trợ</h3>
          <i class="fa-solid fa-xmark" style="cursor:pointer;font-size:20px;" onclick="closeTicketEditModal()"></i>
        </div>
        <div class="mo-modal-body">
          <form onsubmit="handleUpdateTicketSubmit(event)">
            <input type="hidden" id="edit-ticket-id">
            <div style="margin-bottom:14px;">
              <label style="display:block;font-size:13px;font-weight:600;margin-bottom:6px;">Tiêu đề sự cố *</label>
              <input type="text" id="edit-ticket-title" required style="width:100%;padding:10px 14px;border:1px solid #cbd5e1;border-radius:6px;">
            </div>
            <div style="margin-bottom:14px;">
              <label style="display:block;font-size:13px;font-weight:600;margin-bottom:6px;">Phân loại danh mục sự cố</label>
              <select id="edit-ticket-category" style="width:100%;padding:10px 14px;border:1px solid #cbd5e1;border-radius:6px;">
                <option value="MAINTENANCE">Sửa chữa kỹ thuật / Điện lạnh</option>
                <option value="OVERTIME_CONSTRUCTION">Đăng ký thi công ngoài giờ</option>
                <option value="SECURITY_CLEANING">Vệ sinh & An ninh tòa nhà</option>
                <option value="SERVICE_COMPLAINT">Khiếu nại chất lượng dịch vụ</option>
              </select>
            </div>
            <div style="margin-bottom:14px;">
              <label style="display:block;font-size:13px;font-weight:600;margin-bottom:6px;">Mức độ ưu tiên</label>
              <select id="edit-ticket-priority" style="width:100%;padding:10px 14px;border:1px solid #cbd5e1;border-radius:6px;">
                <option value="NORMAL">Bình thường (Xử lý trong ngày)</option>
                <option value="HIGH">Cao (Cần khắc phục sớm)</option>
                <option value="URGENT">Khẩn cấp (Sự cố mất điện, tràn nước...)</option>
                <option value="LOW">Thấp</option>
              </select>
            </div>
            <div style="margin-bottom:18px;">
              <label style="display:block;font-size:13px;font-weight:600;margin-bottom:6px;">Mô tả hiện trạng chi tiết *</label>
              <textarea id="edit-ticket-desc" required rows="3" style="width:100%;padding:10px 14px;border:1px solid #cbd5e1;border-radius:6px;"></textarea>
            </div>
            <button type="submit" id="btn-submit-edit" class="btn-mo-primary" style="width:100%;justify-content:center;padding:11px;background:#2563eb;color:#fff;">
              <i class="fa-solid fa-floppy-disk"></i> Lưu Thay Đổi Thông Tin
            </button>
          </form>
        </div>
      </div>
    </div>
