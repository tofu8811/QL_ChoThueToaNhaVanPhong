<!-- TAB 2: QUẢN LÝ TÒA NHÀ & MẶT BẰNG (Admin Resource) -->
          <div id="tab-admin-buildings" class="filament-tab-pane" style="display:none;">
            <div class="filament-page-header">
              <div>
                <div class="filament-breadcrumbs">Ban quản lý / Danh mục bất động sản</div>
                <h1 class="filament-page-title">Quản Lý Tòa Nhà & Diện Tích Cắt Thuê</h1>
              </div>
              <button class="btn-mo-orange" onclick="alert('Chức năng thêm mới tòa nhà cao ốc vào hệ thống CSDL!')">
                <i class="fa-solid fa-plus"></i> Thêm Tòa Nhà Mới
              </button>
            </div>

            <div class="filament-card">
              <div class="filament-card-header">
                <div style="display:flex;gap:10px;align-items:center;">
                  <input type="text" class="filament-input-sm" placeholder="Tìm theo tên tòa nhà, quận..." style="width:260px;">
                  <select class="filament-input-sm">
                    <option>Tất cả Hạng</option>
                    <option>Hạng A</option>
                    <option>Hạng B</option>
                  </select>
                </div>
                <span style="font-size:13px;color:#64748b;">Tổng cộng {{ $stats['total_buildings'] ?? 22 }} tòa nhà trong hệ thống</span>
              </div>

              <table class="mo-table">
                <thead>
                  <tr>
                    <th>Tên Tòa Nhà</th>
                    <th>Địa Chỉ / Khu Vực</th>
                    <th>Hạng</th>
                    <th>Tổng DT Sàn</th>
                    <th>Tỷ Lệ Lấp Đầy</th>
                    <th>Giá Thuê</th>
                    <th>Thao tác</th>
                  </tr>
                </thead>
                <tbody id="admin-buildings-tbody">
                  <!-- Rendered via JS -->
                </tbody>
              </table>
            </div>
          </div>
