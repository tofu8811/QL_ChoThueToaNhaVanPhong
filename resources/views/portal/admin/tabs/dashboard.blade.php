<div id="tab-admin-dashboard" class="filament-tab-pane active">
            <div class="filament-page-header">
              <div>
                <div class="filament-breadcrumbs">Trang chủ / Ban quản lý & Admin</div>
                <h1 class="filament-page-title">Tổng Quan Vận Hành & Lấp Đầy Cao Ốc</h1>
              </div>
              <button class="btn-mo-primary" onclick="alert('Đã tạo bản kết xuất PDF Báo cáo Vận Hành!')">
                <i class="fa-solid fa-file-export"></i> Xuất Báo Cáo
              </button>
            </div>

            <!-- Stat Widgets (Dữ liệu vận hành thực tế từ Database) -->
            <div class="filament-widgets-grid">
              <div class="filament-stat-card">
                <div class="filament-stat-label">Tổng số tòa nhà quản lý</div>
                <div class="filament-stat-value">{{ $stats['total_buildings'] ?? 22 }} <span style="font-size:16px;color:#64748b;font-weight:normal;">tòa</span></div>
                <div class="filament-stat-trend trend-up"><i class="fa-solid fa-arrow-trend-up"></i> Tỷ lệ khai thác cao</div>
              </div>
              <div class="filament-stat-card">
                <div class="filament-stat-label">Tỷ lệ lấp đầy trung bình</div>
                <div class="filament-stat-value">84,6%</div>
                <div class="filament-stat-trend trend-up"><i class="fa-solid fa-arrow-trend-up"></i> +4,2% so với tháng trước</div>
              </div>
              <div class="filament-stat-card">
                <div class="filament-stat-label">Tổng diện tích đang quản lý</div>
                <div class="filament-stat-value">{{ number_format($stats['total_leasable_area'] ?? 428430, 0, ',', '.') }} <span style="font-size:16px;color:#64748b;font-weight:normal;">m²</span></div>
                <div class="filament-stat-trend" style="color:#64748b;"><i class="fa-solid fa-cube"></i> Phân bổ theo Hạng A, B, C</div>
              </div>
              <div class="filament-stat-card">
                <div class="filament-stat-label">Ticket bảo trì đang mở</div>
                <div class="filament-stat-value" style="color:#ef4444;">{{ sprintf('%02d', $stats['open_tickets'] ?? 5) }}</div>
                <div class="filament-stat-trend trend-down"><i class="fa-solid fa-circle-exclamation"></i> Theo dõi xử lý trong ngày</div>
              </div>
            </div>

            <!-- Filament Chart & Tasks Layout -->
            <div style="display:grid;grid-template-columns:1.6fr 1fr;gap:24px;margin-bottom:24px;">
              <!-- Chart Card -->
              <div class="filament-card" style="margin-bottom:0;">
                <div class="filament-card-header">
                  <h3 class="filament-card-title"><i class="fa-solid fa-chart-column" style="color:#2563eb;"></i> Tỷ Lệ Lấp Đầy & Diện Tích Thuê (6 Tháng Gần Nhất)</h3>
                  <span class="badge-filament info">Năm 2026</span>
                </div>
                <div style="padding:20px;">
                  <div style="display:flex;align-items:flex-end;gap:18px;height:200px;padding-top:20px;border-bottom:1px solid #e2e8f0;">
                    <div style="flex:1;display:flex;flex-direction:column;align-items:center;height:100%;justify-content:flex-end;">
                      <div style="font-size:11px;font-weight:700;color:#64748b;margin-bottom:4px;">76%</div>
                      <div style="width:100%;height:76%;background:linear-gradient(180deg,#93c5fd,#3b82f6);border-radius:6px 6px 0 0;"></div>
                      <div style="font-size:11px;margin-top:6px;color:#64748b;">T6</div>
                    </div>
                    <div style="flex:1;display:flex;flex-direction:column;align-items:center;height:100%;justify-content:flex-end;">
                      <div style="font-size:11px;font-weight:700;color:#64748b;margin-bottom:4px;">78%</div>
                      <div style="width:100%;height:78%;background:linear-gradient(180deg,#93c5fd,#3b82f6);border-radius:6px 6px 0 0;"></div>
                      <div style="font-size:11px;margin-top:6px;color:#64748b;">T7</div>
                    </div>
                    <div style="flex:1;display:flex;flex-direction:column;align-items:center;height:100%;justify-content:flex-end;">
                      <div style="font-size:11px;font-weight:700;color:#64748b;margin-bottom:4px;">80%</div>
                      <div style="width:100%;height:80%;background:linear-gradient(180deg,#93c5fd,#3b82f6);border-radius:6px 6px 0 0;"></div>
                      <div style="font-size:11px;margin-top:6px;color:#64748b;">T8</div>
                    </div>
                    <div style="flex:1;display:flex;flex-direction:column;align-items:center;height:100%;justify-content:flex-end;">
                      <div style="font-size:11px;font-weight:700;color:#64748b;margin-bottom:4px;">81%</div>
                      <div style="width:100%;height:81%;background:linear-gradient(180deg,#93c5fd,#3b82f6);border-radius:6px 6px 0 0;"></div>
                      <div style="font-size:11px;margin-top:6px;color:#64748b;">T9</div>
                    </div>
                    <div style="flex:1;display:flex;flex-direction:column;align-items:center;height:100%;justify-content:flex-end;">
                      <div style="font-size:11px;font-weight:700;color:#64748b;margin-bottom:4px;">83%</div>
                      <div style="width:100%;height:83%;background:linear-gradient(180deg,#93c5fd,#3b82f6);border-radius:6px 6px 0 0;"></div>
                      <div style="font-size:11px;margin-top:6px;color:#64748b;">T10</div>
                    </div>
                    <div style="flex:1;display:flex;flex-direction:column;align-items:center;height:100%;justify-content:flex-end;">
                      <div style="font-size:11px;font-weight:700;color:#2563eb;margin-bottom:4px;">84.6%</div>
                      <div style="width:100%;height:84.6%;background:linear-gradient(180deg,#f59e0b,#ea580c);border-radius:6px 6px 0 0;"></div>
                      <div style="font-size:11px;margin-top:6px;color:#ea580c;font-weight:700;">T11 (Nay)</div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Action Pending List -->
              <div class="filament-card" style="margin-bottom:0;">
                <div class="filament-card-header">
                  <h3 class="filament-card-title"><i class="fa-solid fa-list-check" style="color:#f59e0b;"></i> Việc Cần Phê Duyệt Ngay</h3>
                  <span class="badge-filament warning">4 mục</span>
                </div>
                <div style="padding:14px 20px;">
                  <div style="padding:10px 0;border-bottom:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center;">
                    <div>
                      <div style="font-weight:600;font-size:13.5px;">Duyệt thi công ngoài giờ (Tầng 12)</div>
                      <div style="font-size:12px;color:#64748b;">Công ty ABC Tech - Vietcombank Tower</div>
                    </div>
                    <button class="btn-mo-primary" style="padding:5px 10px;font-size:12px;" onclick="alert('Đã phê duyệt giấy phép thi công ngoài giờ!')">Duyệt</button>
                  </div>
                  <div style="padding:10px 0;border-bottom:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center;">
                    <div>
                      <div style="font-weight:600;font-size:13.5px;">Bảo trì điều hòa trung tâm phòng Server</div>
                      <div style="font-size:12px;color:#64748b;">Deutsches Haus - Yêu cầu khẩn cấp</div>
                    </div>
                    <button class="btn-mo-orange" style="padding:5px 10px;font-size:12px;" onclick="alert('Đã phân công kỹ sư BQL tới hiện trường!')">Giao việc</button>
                  </div>
                  <div style="padding:10px 0;display:flex;justify-content:space-between;align-items:center;">
                    <div>
                      <div style="font-weight:600;font-size:13.5px;">Gia hạn hợp đồng thuê thêm 2 năm</div>
                      <div style="font-size:12px;color:#64748b;">Tập đoàn Nova Logistics - Lim Tower</div>
                    </div>
                    <button class="btn-mo-outline" style="padding:5px 10px;font-size:12px;" onclick="alert('Đã mở hồ sơ hợp đồng gia hạn!')">Xem</button>
                  </div>
                </div>
              </div>
            </div>
          </div>
