<!-- TAB 8: TENANT WORKSPACE (Không gian làm việc cho doanh nghiệp thuê - RESTFUL API MODULE 3) -->
<div id="tab-tenant-workspace" class="filament-tab-pane" style="display:none;">
  <div class="filament-page-header">
    <div>
      <div class="filament-breadcrumbs">Cổng khách thuê / Hợp đồng của tôi</div>
      <h1 class="filament-page-title">Hợp Đồng Thuê & Hồ Sơ Pháp Lý Doanh Nghiệp</h1>
    </div>
    <div style="display:flex;gap:8px;">
      <button class="btn-mo-outline" onclick="loadTenantWorkspaceContracts()">
        <i class="fa-solid fa-arrows-rotate"></i> Làm Mới
      </button>
    </div>
  </div>

  <div id="tenant-workspace-contracts-container">
    <!-- Rendered via JS contracts.blade.php (loadTenantWorkspaceContracts) -->
  </div>
</div>

<!-- TAB 9: TENANT TICKET MANAGEMENT (Module 4) -->
<div id="tab-tenant-tickets" class="filament-tab-pane" style="display:none;">
    <div class="filament-page-header">
        <div>
            <div class="filament-breadcrumbs">Cổng khách thuê / Hỗ trợ vận hành (Module 4)</div>
            <h1 class="filament-page-title">Gửi Yêu Cầu Hỗ Trợ & Đăng Ký Thi Công</h1>
        </div>
        <div style="display:flex;gap:8px;">
            <button class="btn-mo-outline" onclick="loadTicketsList('tenant')">
                <i class="fa-solid fa-arrows-rotate"></i> Tải Lại
            </button>
            <button class="btn-mo-orange" onclick="openTenantTicketModal()">
                <i class="fa-solid fa-plus"></i> Tạo Ticket Mới
            </button>
        </div>
    </div>

    <div class="filament-card">
        <table class="mo-table">
            <thead>
                <tr>
                    <th>Mã Ticket</th>
                    <th>Loại Yêu Cầu</th>
                    <th>Tiêu Đề / Mô Tả</th>
                    <th>Thời Gian Tạo</th>
                    <th>Mức Ưu Tiên</th>
                    <th>Trạng Thái Xử Lý</th>
                    <th style="text-align:right;">Thao Tác</th>
                </tr>
            </thead>
            <tbody id="tenant-tickets-tbody">
                <!-- Rendered via JS -->
            </tbody>
        </table>
    </div>
</div>

<!-- TAB 10: TENANT BILLS & PAYMENTS (Module 3) -->
<div id="tab-tenant-bills" class="filament-tab-pane" style="display:none;">
    <div class="filament-page-header">
        <div>
            <div class="filament-breadcrumbs">Cổng khách thuê / Tài chính</div>
            <h1 class="filament-page-title">Hóa Đơn & Cước Phí Dịch Vụ Hàng Tháng</h1>
        </div>
    </div>
    <div class="filament-card" style="padding:32px;text-align:center;color:#64748b;">
        <i class="fa-solid fa-receipt" style="font-size:36px;color:#94a3b8;margin-bottom:12px;"></i>
        <p style="font-size:14px;margin-bottom:4px;">Cước phí thuê và điện/nước/xe của kỳ thanh toán hiện tại được tổng hợp tự động theo chu kỳ hợp đồng.</p>
        <span style="font-size:12px;color:#94a3b8;">Vui lòng kiểm tra mục Hợp đồng thuê để xem chi tiết đơn giá và kỳ đối soát.</span>
    </div>
</div>
