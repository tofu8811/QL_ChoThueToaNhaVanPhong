<!-- TAB 3: ADMIN TICKETS & BẢO TRÌ (Ban quản lý xử lý yêu cầu khách thuê - Module 4) -->
<div id="tab-admin-tickets" class="filament-tab-pane" style="display:none;">
  <div class="filament-page-header">
    <div>
      <div class="filament-breadcrumbs">Vận hành / Hỗ trợ khách thuê (Module 4)</div>
      <h1 class="filament-page-title">Tiếp Nhận & Điều Phối Ticket Bảo Trì</h1>
    </div>
    <div style="display:flex;gap:8px;">
      <button class="btn-mo-outline" onclick="loadTicketsList('admin')">
        <i class="fa-solid fa-arrows-rotate"></i> Tải Lại Dữ Liệu
      </button>
      <button class="btn-mo-primary" onclick="alert('Đã kết xuất danh sách phiếu kỹ thuật gửi đội cơ điện tòa nhà!')">
        <i class="fa-solid fa-print"></i> In Phiếu Kỹ Thuật
      </button>
    </div>
  </div>

  <!-- Thanh lọc Ticket BQL (Endpoint 57) -->
  <div class="filament-card" style="margin-bottom:14px; padding:12px 18px;">
    <div style="display:flex; flex-wrap:wrap; gap:10px; align-items:center;">
      <div style="flex:1; min-width:200px;">
        <input type="text" id="ticket-filter-keyword" class="filament-input-sm" placeholder="Tìm theo mã ticket, tiêu đề, phòng..." onkeyup="if(event.key==='Enter') loadTicketsList('admin')">
      </div>
      <div>
        <select id="ticket-filter-status" class="filament-input-sm" onchange="loadTicketsList('admin')">
          <option value="">Tất cả Trạng thái</option>
          <option value="OPEN">Mới tiếp nhận (OPEN)</option>
          <option value="IN_PROGRESS">Đang xử lý (IN_PROGRESS)</option>
          <option value="ON_HOLD">Tạm hoãn (ON_HOLD)</option>
          <option value="RESOLVED">Chờ nghiệm thu (RESOLVED)</option>
          <option value="CLOSED">Hoàn tất (CLOSED)</option>
        </select>
      </div>
      <div>
        <select id="ticket-filter-priority" class="filament-input-sm" onchange="loadTicketsList('admin')">
          <option value="">Tất cả Mức ưu tiên</option>
          <option value="URGENT">Khẩn cấp</option>
          <option value="HIGH">Cao</option>
          <option value="NORMAL">Bình thường</option>
          <option value="LOW">Thấp</option>
        </select>
      </div>
      <button class="btn-mo-orange" style="padding:6px 14px; font-size:12.5px;" onclick="loadTicketsList('admin')">
        <i class="fa-solid fa-filter"></i> Lọc Ticket
      </button>
    </div>
  </div>

  <div class="filament-card">
    <table class="mo-table">
      <thead>
        <tr>
          <th>Mã Ticket</th>
          <th>Khách Thuê / Doanh Nghiệp</th>
          <th>Tòa Nhà & Vị Trí</th>
          <th>Nội Dung Yêu Cầu</th>
          <th>Mức Ưu Tiên</th>
          <th>Kỹ Thuật Viên</th>
          <th>Trạng Thái</th>
          <th style="text-align:right;">Hành Động</th>
        </tr>
      </thead>
      <tbody id="admin-tickets-tbody">
        <!-- Rendered via JS -->
      </tbody>
    </table>
  </div>
</div>
