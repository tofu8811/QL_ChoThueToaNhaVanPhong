<!-- TAB 10: QUẢN TRỊ NHÂN SỰ & PHÂN QUYỀN ĐA TÁC NHÂN (MODULE 5 - ENDPOINTS 72-91) -->
          <div id="tab-admin-staff" class="filament-tab-pane" style="display:none;">
            <div class="filament-card" style="margin-bottom: 20px;">
              <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 12px;">
                <div>
                  <h3 style="font-size: 19px; font-weight: 800; color: #0f172a; margin-bottom: 6px; display: flex; align-items: center; gap: 10px;">
                    <i class="fa-solid fa-users-gear" style="color: #2563eb;"></i> Quản Trị Nhân Sự & Phân Quyền Đa Tác Nhân (RBAC)
                  </h3>
                  <p style="font-size: 13.5px; color: #64748b; margin: 0;">
                    Hệ thống xác thực và phân quyền 4 nhóm tác nhân: Quản trị viên (Admins), Nhân viên Sales, Ban Quản lý (PM) và Kế toán công nợ.
                  </p>
                </div>
                <div>
                  <button class="btn-mo-outline" style="font-size: 13px; padding: 7px 14px;" onclick="reloadCurrentStaffTab()">
                    <i class="fa-solid fa-rotate"></i> Làm Mới Dữ Liệu
                  </button>
                </div>
              </div>

              <!-- Thanh chuyển đổi 4 nhóm nhân sự (Sub-tabs) -->
              <div class="staff-subtab-bar" style="margin-top: 18px;">
                <button class="staff-subtab-btn active" id="btn-subtab-admins" onclick="switchStaffSubTab('admins', this)">
                  <i class="fa-solid fa-shield-halved" style="color:#ef4444;"></i> Quản Trị Viên (Admins)
                  <span class="badge-pill" id="badge-count-admins">0</span>
                </button>
                <button class="staff-subtab-btn" id="btn-subtab-sales" onclick="switchStaffSubTab('sales', this)">
                  <i class="fa-solid fa-user-tie" style="color:#3b82f6;"></i> Nhân Viên Sales
                  <span class="badge-pill" id="badge-count-sales">0</span>
                </button>
                <button class="staff-subtab-btn" id="btn-subtab-pm" onclick="switchStaffSubTab('pm', this)">
                  <i class="fa-solid fa-helmet-safety" style="color:#f59e0b;"></i> Ban Quản Lý (PM)
                  <span class="badge-pill" id="badge-count-pm">0</span>
                </button>
                <button class="staff-subtab-btn" id="btn-subtab-accountants" onclick="switchStaffSubTab('accountants', this)">
                  <i class="fa-solid fa-calculator" style="color:#10b981;"></i> Kế Toán
                  <span class="badge-pill" id="badge-count-accountants">0</span>
                </button>

                <button class="staff-subtab-btn" id="btn-subtab-assignments" onclick="switchStaffSubTab('assignments', this)">
                  <i class="fa-solid fa-building-user" style="color:#6366f1;"></i> Phân Công Tòa Nhà (RBAC)
                  <span class="badge-pill" id="badge-count-assignments">Chọn tòa nhà</span>
                </button>
                <button class="staff-subtab-btn" id="btn-subtab-assignments" onclick="switchStaffSubTab('assignments', this)">
                  <i class="fa-solid fa-building-user" style="color:#6366f1;"></i> Phân Công Tòa Nhà (RBAC)
                  <span class="badge-pill" id="badge-count-assignments">Chọn tòa nhà</span>
                </button>

              </div>
            </div>

            <!-- SUBPANEL 1: QUẢN TRỊ VIÊN (ADMINS) — API 72-76 -->
            <div id="subpanel-staff-admins" class="staff-subpanel">
              <div class="filament-card" style="margin-bottom: 20px; border-left: 4px solid #ef4444; background: #fff5f5; padding: 14px 18px;">
                <div style="display:flex; align-items:center; gap: 12px;">
                  <i class="fa-solid fa-circle-exclamation" style="font-size: 20px; color: #dc2626;"></i>
                  <div style="font-size: 13px; color: #991b1b; line-height: 1.5;">
                    <strong>Phân quyền bảo mật cao cấp (Super Admin):</strong> Chỉ tài khoản Quản trị viên cấp cao (Super Admin) mới có thẩm quyền xem danh sách, cấp tài khoản và điều chỉnh quyền hạn của các Admin khác trong hệ thống.
                  </div>
                </div>
              </div>

              <div class="filament-card" style="margin-bottom: 20px;">
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 16px;">
                  <div style="display: flex; gap: 10px; flex-wrap: wrap; align-items: center;">
                    <div style="position: relative; width: 260px;">
                      <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 12px; top: 11px; color: #94a3b8; font-size: 13px;"></i>
                      <input type="text" id="filter-admin-keyword" placeholder="Tìm theo tên, email, SĐT..." class="form-control" style="padding-left: 34px; height: 38px; font-size: 13px; border-radius: 6px;" onkeyup="if(event.key==='Enter') loadStaffAdmins(1)">
                    </div>
                    <select id="filter-admin-status" class="form-control" style="height: 38px; font-size: 13px; width: 150px; border-radius: 6px;" onchange="loadStaffAdmins(1)">
                      <option value="">-- Trạng thái --</option>
                      <option value="ACTIVE">Hoạt động</option>
                      <option value="LOCKED">Đã khóa</option>
                      <option value="PENDING">Chờ duyệt</option>
                    </select>
                    <button class="btn-mo-primary" style="height: 38px; padding: 0 16px; font-size: 13px; border-radius: 6px;" onclick="loadStaffAdmins(1)">
                      <i class="fa-solid fa-filter"></i> Lọc
                    </button>
                  </div>
                  <div>
                    <button class="btn-mo-orange" style="font-size: 13px; padding: 8px 16px; border-radius: 6px;" onclick="openCreateAdminModal()">
                      <i class="fa-solid fa-user-plus"></i> Thêm Admin Mới
                    </button>
                  </div>
                </div>

                <div class="table-responsive">
                  <table class="filament-table">
                    <thead>
                      <tr>
                        <th>Họ Và Tên</th>
                        <th>Email / Tài Khoản</th>
                        <th>Số Điện Thoại</th>
                        <th>Cấp Bậc</th>
                        <th>Trạng Thái</th>
                        <th>Ngày Tạo</th>
                        <th style="text-align: right;">Hành Động</th>
                      </tr>
                    </thead>
                    <tbody id="tbody-staff-admins">
                      <tr><td colspan="7" style="text-align:center; padding: 30px; color:#94a3b8;"><i class="fa-solid fa-spinner fa-spin"></i> Đang tải danh sách Admin...</td></tr>
                    </tbody>
                  </table>
                </div>

                <div id="pagination-staff-admins" style="margin-top: 16px; display: flex; justify-content: space-between; align-items: center;"></div>
              </div>
            </div>

            <!-- SUBPANEL 2: NHÂN VIÊN SALES — API 77-81 -->
            <div id="subpanel-staff-sales" class="staff-subpanel" style="display:none;">
              <div class="filament-card" style="margin-bottom: 20px;">
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 16px;">
                  <div style="display: flex; gap: 10px; flex-wrap: wrap; align-items: center;">
                    <div style="position: relative; width: 260px;">
                      <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 12px; top: 11px; color: #94a3b8; font-size: 13px;"></i>
                      <input type="text" id="filter-sale-keyword" placeholder="Mã NV, tên, email, SĐT..." class="form-control" style="padding-left: 34px; height: 38px; font-size: 13px; border-radius: 6px;" onkeyup="if(event.key==='Enter') loadStaffSales(1)">
                    </div>
                    <select id="filter-sale-status" class="form-control" style="height: 38px; font-size: 13px; width: 150px; border-radius: 6px;" onchange="loadStaffSales(1)">
                      <option value="">-- Trạng thái --</option>
                      <option value="ACTIVE">Hoạt động</option>
                      <option value="LOCKED">Đã khóa</option>
                      <option value="PENDING">Chờ duyệt</option>
                    </select>
                    <button class="btn-mo-primary" style="height: 38px; padding: 0 16px; font-size: 13px; border-radius: 6px;" onclick="loadStaffSales(1)">
                      <i class="fa-solid fa-filter"></i> Lọc
                    </button>
                  </div>
                  <div>
                    <button class="btn-mo-orange" style="font-size: 13px; padding: 8px 16px; border-radius: 6px;" onclick="openCreateSaleModal()">
                      <i class="fa-solid fa-user-plus"></i> Thêm Nhân Viên Sales
                    </button>
                  </div>
                </div>

                <div class="table-responsive">
                  <table class="filament-table">
                    <thead>
                      <tr>
                        <th>Mã NV</th>
                        <th>Họ Và Tên</th>
                        <th>Email / Điện Thoại</th>
                        <th>Tòa Nhà Phụ Trách</th>
                        <th>Hạn Mức Leads</th>
                        <th>Hoa Hồng</th>
                        <th>Trạng Thái</th>
                        <th style="text-align: right;">Hành Động</th>
                      </tr>
                    </thead>
                    <tbody id="tbody-staff-sales">
                      <tr><td colspan="8" style="text-align:center; padding: 30px; color:#94a3b8;"><i class="fa-solid fa-spinner fa-spin"></i> Đang tải danh sách Sales...</td></tr>
                    </tbody>
                  </table>
                </div>

                <div id="pagination-staff-sales" style="margin-top: 16px; display: flex; justify-content: space-between; align-items: center;"></div>
              </div>
            </div>

            <!-- SUBPANEL 3: BAN QUẢN LÝ (PM) — API 82-86 -->
            <div id="subpanel-staff-pm" class="staff-subpanel" style="display:none;">
              <div class="filament-card" style="margin-bottom: 20px;">
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 16px;">
                  <div style="display: flex; gap: 10px; flex-wrap: wrap; align-items: center;">
                    <div style="position: relative; width: 260px;">
                      <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 12px; top: 11px; color: #94a3b8; font-size: 13px;"></i>
                      <input type="text" id="filter-pm-keyword" placeholder="Mã BQL, tên, email, hotline..." class="form-control" style="padding-left: 34px; height: 38px; font-size: 13px; border-radius: 6px;" onkeyup="if(event.key==='Enter') loadStaffPm(1)">
                    </div>
                    <select id="filter-pm-department" class="form-control" style="height: 38px; font-size: 13px; width: 170px; border-radius: 6px;" onchange="loadStaffPm(1)">
                      <option value="">-- Bộ phận chuyên trách --</option>
                      <option value="TECHNICAL">Kỹ thuật tòa nhà</option>
                      <option value="SECURITY">An ninh & Bảo vệ</option>
                      <option value="CLEANING">Vệ sinh & Cảnh quan</option>
                      <option value="CUSTOMER_CARE">Chăm sóc khách thuê</option>
                    </select>
                    <select id="filter-pm-status" class="form-control" style="height: 38px; font-size: 13px; width: 140px; border-radius: 6px;" onchange="loadStaffPm(1)">
                      <option value="">-- Trạng thái --</option>
                      <option value="ACTIVE">Hoạt động</option>
                      <option value="LOCKED">Đã khóa</option>
                      <option value="PENDING">Chờ duyệt</option>
                    </select>
                    <button class="btn-mo-primary" style="height: 38px; padding: 0 16px; font-size: 13px; border-radius: 6px;" onclick="loadStaffPm(1)">
                      <i class="fa-solid fa-filter"></i> Lọc
                    </button>
                  </div>
                  <div>
                    <button class="btn-mo-orange" style="font-size: 13px; padding: 8px 16px; border-radius: 6px;" onclick="openCreatePmModal()">
                      <i class="fa-solid fa-user-plus"></i> Thêm Nhân Sự BQL
                    </button>
                  </div>
                </div>

                <div class="table-responsive">
                  <table class="filament-table">
                    <thead>
                      <tr>
                        <th>Mã NV</th>
                        <th>Họ Và Tên</th>
                        <th>Hotline Khẩn Cấp</th>
                        <th>Bộ Phận Chuyên Trách</th>
                        <th>Tòa Nhà Quản Lý</th>
                        <th>Trạng Thái</th>
                        <th style="text-align: right;">Hành Động</th>
                      </tr>
                    </thead>
                    <tbody id="tbody-staff-pm">
                      <tr><td colspan="7" style="text-align:center; padding: 30px; color:#94a3b8;"><i class="fa-solid fa-spinner fa-spin"></i> Đang tải danh sách BQL...</td></tr>
                    </tbody>
                  </table>
                </div>

                <div id="pagination-staff-pm" style="margin-top: 16px; display: flex; justify-content: space-between; align-items: center;"></div>
              </div>
            </div>

            <!-- SUBPANEL 4: KẾ TOÁN (ACCOUNTANTS) — API 87-91 -->
            <div id="subpanel-staff-accountants" class="staff-subpanel" style="display:none;">
              <div class="filament-card" style="margin-bottom: 20px;">
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 16px;">
                  <div style="display: flex; gap: 10px; flex-wrap: wrap; align-items: center;">
                    <div style="position: relative; width: 260px;">
                      <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 12px; top: 11px; color: #94a3b8; font-size: 13px;"></i>
                      <input type="text" id="filter-acc-keyword" placeholder="Mã KT, tên, email, SĐT..." class="form-control" style="padding-left: 34px; height: 38px; font-size: 13px; border-radius: 6px;" onkeyup="if(event.key==='Enter') loadStaffAccountants(1)">
                    </div>
                    <select id="filter-acc-adjustment" class="form-control" style="height: 38px; font-size: 13px; width: 170px; border-radius: 6px;" onchange="loadStaffAccountants(1)">
                      <option value="">-- Quyền duyệt cước --</option>
                      <option value="1">Có quyền duyệt</option>
                      <option value="0">Không có quyền duyệt</option>
                    </select>
                    <select id="filter-acc-status" class="form-control" style="height: 38px; font-size: 13px; width: 140px; border-radius: 6px;" onchange="loadStaffAccountants(1)">
                      <option value="">-- Trạng thái --</option>
                      <option value="ACTIVE">Hoạt động</option>
                      <option value="LOCKED">Đã khóa</option>
                      <option value="PENDING">Chờ duyệt</option>
                    </select>
                    <button class="btn-mo-primary" style="height: 38px; padding: 0 16px; font-size: 13px; border-radius: 6px;" onclick="loadStaffAccountants(1)">
                      <i class="fa-solid fa-filter"></i> Lọc
                    </button>
                  </div>
                  <div>
                    <button class="btn-mo-orange" style="font-size: 13px; padding: 8px 16px; border-radius: 6px;" onclick="openCreateAccountantModal()">
                      <i class="fa-solid fa-user-plus"></i> Thêm Kế Toán Mới
                    </button>
                  </div>
                </div>

                <div class="table-responsive">
                  <table class="filament-table">
                    <thead>
                      <tr>
                        <th>Mã NV</th>
                        <th>Họ Và Tên</th>
                        <th>Email / Điện Thoại</th>
                        <th>Quyền Duyệt Điều Chỉnh</th>
                        <th>Tòa Nhà Theo Dõi Công Nợ</th>
                        <th>Trạng Thái</th>
                        <th style="text-align: right;">Hành Động</th>
                      </tr>
                    </thead>
                    <tbody id="tbody-staff-accountants">
                      <tr><td colspan="7" style="text-align:center; padding: 30px; color:#94a3b8;"><i class="fa-solid fa-spinner fa-spin"></i> Đang tải danh sách Kế toán...</td></tr>
                    </tbody>
                  </table>
                </div>

                <div id="pagination-staff-accountants" style="margin-top: 16px; display: flex; justify-content: space-between; align-items: center;"></div>
              </div>
            </div>

          
<!-- SUBPANEL 5: PHÂN CÔNG TÒA NHÀ & RBAC (ENDPOINTS 92 - 98) -->
            <div id="subpanel-staff-assignments" class="staff-subpanel" style="display:none;">
              <!-- Card chọn tòa nhà -->
              <div class="filament-card" style="margin-bottom: 20px; background: linear-gradient(135deg, #f8fafc, #eff6ff); border: 1px solid #bfdbfe;">
                <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px;">
                  <div style="display:flex; align-items:center; gap:14px; flex-wrap:wrap;">
                    <div style="width:44px; height:44px; border-radius:10px; background:#2563eb; color:#fff; display:flex; align-items:center; justify-content:center; font-size:20px;">
                      <i class="fa-solid fa-city"></i>
                    </div>
                    <div>
                      <label style="display:block; font-size:12px; font-weight:700; color:#1e40af; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:4px;">
                        Chọn Tòa Nhà Để Phân Công Nhân Sự (RBAC)
                      </label>
                      <select id="select-assignment-building" class="form-control" style="min-width:320px; height:42px; font-weight:700; font-size:14px; color:#0f3361; border:2px solid #93c5fd; border-radius:8px;" onchange="onBuildingAssignmentChange(this.value)">
                        <option value="">-- Đang tải danh sách tòa nhà... --</option>
                      </select>
                    </div>
                  </div>
                  <div style="display:flex; gap:10px;">
                    <button class="btn-mo-outline" style="height:42px; padding:0 16px; font-size:13px; background:#fff;" onclick="reloadBuildingAssignments()">
                      <i class="fa-solid fa-rotate"></i> Tải Lại Phân Công
                    </button>
                  </div>
                </div>

                <!-- Box tóm tắt thông tin tòa nhà -->
                <div id="box-assignment-building-info" style="display:none; margin-top:16px; padding-top:16px; border-top:1px dashed #cbd5e1; display:flex; gap:20px; flex-wrap:wrap; font-size:13px; color:#334155;">
                  <div><strong>Mã tòa nhà:</strong> <span id="info-bld-code" style="font-family:monospace; color:#2563eb;">---</span></div>
                  <div><strong>Tên tòa nhà:</strong> <span id="info-bld-name" style="font-weight:700; color:#0f172a;">---</span></div>
                  <div><strong>Địa chỉ:</strong> <span id="info-bld-address">---</span></div>
                  <div><strong>Hạng:</strong> <span id="info-bld-grade" class="badge-filament info">---</span></div>
                </div>
              </div>

              <!-- KHỐI 1: SALES PHỤ TRÁCH TÒA NHÀ (API 92, 93, 94) -->
              <div class="filament-card" style="margin-bottom: 24px;">
                <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; margin-bottom:16px;">
                  <div>
                    <h4 style="margin:0 0 4px 0; font-size:16px; font-weight:700; color:#1e3a8a; display:flex; align-items:center; gap:8px;">
                      <i class="fa-solid fa-user-tie" style="color:#2563eb;"></i> 1. Đội Ngũ Sales Phụ Trách Kinh Doanh & Tiếp Nhận Lead
                    </h4>
                    <p style="margin:0; font-size:12.5px; color:#64748b;">Nhân viên Sales phụ trách tiếp nhận Leads khách thuê có nhu cầu tại tòa nhà này.</p>
                  </div>
                  <div>
                    <button class="btn-mo-orange" style="font-size:12.5px; padding:7px 14px;" onclick="openAssignSalesModal()">
                      <i class="fa-solid fa-user-plus"></i> Phân Công Sales Vào Tòa Nhà
                    </button>
                  </div>
                </div>

                <div class="table-responsive">
                  <table class="filament-table">
                    <thead>
                      <tr>
                        <th>Mã NV</th>
                        <th>Họ Và Tên</th>
                        <th>Email / Điện Thoại</th>
                        <th>Hạn Mức Leads</th>
                        <th>Trạng Thái Gán</th>
                        <th>Thời Điểm Phân Công</th>
                        <th style="text-align: right;">Hành Động</th>
                      </tr>
                    </thead>
                    <tbody id="tbody-building-assigned-sales">
                      <tr><td colspan="7" style="text-align:center; padding: 25px; color:#94a3b8;">Vui lòng chọn tòa nhà để xem danh sách Sales phụ trách.</td></tr>
                    </tbody>
                  </table>
                </div>
              </div>

              <!-- KHỐI 2: BAN QUẢN LÝ TÒA NHÀ (PM - API 92, 95, 96) -->
              <div class="filament-card" style="margin-bottom: 24px;">
                <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; margin-bottom:16px;">
                  <div>
                    <h4 style="margin:0 0 4px 0; font-size:16px; font-weight:700; color:#92400e; display:flex; align-items:center; gap:8px;">
                      <i class="fa-solid fa-helmet-safety" style="color:#f59e0b;"></i> 2. Ban Quản Lý & Kỹ Thuật Tòa Nhà (Property Managers)
                    </h4>
                    <p style="margin:0; font-size:12.5px; color:#64748b;">Đội ngũ kỹ thuật, an ninh trực tiếp tiếp nhận và xử lý sự cố bảo trì tại tòa nhà.</p>
                  </div>
                  <div>
                    <button class="btn-mo-orange" style="font-size:12.5px; padding:7px 14px; background:#d97706; border-color:#d97706;" onclick="openAssignPmModal()">
                      <i class="fa-solid fa-user-shield"></i> Gán Ban Quản Lý Vào Tòa Nhà
                    </button>
                  </div>
                </div>

                <div class="table-responsive">
                  <table class="filament-table">
                    <thead>
                      <tr>
                        <th>Mã BQL</th>
                        <th>Họ Và Tên</th>
                        <th>Hotline Khẩn Cấp</th>
                        <th>Bộ Phận Chuyên Trách</th>
                        <th>Vai Trò BQL</th>
                        <th>Thời Điểm Phân Công</th>
                        <th style="text-align: right;">Hành Động</th>
                      </tr>
                    </thead>
                    <tbody id="tbody-building-assigned-pm">
                      <tr><td colspan="7" style="text-align:center; padding: 25px; color:#94a3b8;">Vui lòng chọn tòa nhà để xem danh sách BQL phụ trách.</td></tr>
                    </tbody>
                  </table>
                </div>
              </div>

              <!-- KHỐI 3: KẾ TOÁN CÔNG NỢ TÒA NHÀ (API 92, 97, 98) -->
              <div class="filament-card" style="margin-bottom: 24px;">
                <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; margin-bottom:16px;">
                  <div>
                    <h4 style="margin:0 0 4px 0; font-size:16px; font-weight:700; color:#065f46; display:flex; align-items:center; gap:8px;">
                      <i class="fa-solid fa-calculator" style="color:#10b981;"></i> 3. Bộ Phận Kế Toán Phụ Trách Công Nợ & Thu Cước
                    </h4>
                    <p style="margin:0; font-size:12.5px; color:#64748b;">Kế toán phụ trách lập phiếu thu cước, xuất Debit Note và theo dõi công nợ khách thuê.</p>
                  </div>
                  <div>
                    <button class="btn-mo-orange" style="font-size:12.5px; padding:7px 14px; background:#059669; border-color:#059669;" onclick="openAssignAccountantModal()">
                      <i class="fa-solid fa-file-invoice-dollar"></i> Gán Kế Toán Vào Tòa Nhà
                    </button>
                  </div>
                </div>

                <div class="table-responsive">
                  <table class="filament-table">
                    <thead>
                      <tr>
                        <th>Mã KT</th>
                        <th>Họ Và Tên</th>
                        <th>Email / Điện Thoại</th>
                        <th>Quyền Duyệt Điều Chỉnh</th>
                        <th>Trạng Thái</th>
                        <th>Thời Điểm Phân Công</th>
                        <th style="text-align: right;">Hành Động</th>
                      </tr>
                    </thead>
                    <tbody id="tbody-building-assigned-accountants">
                      <tr><td colspan="7" style="text-align:center; padding: 25px; color:#94a3b8;">Vui lòng chọn tòa nhà để xem danh sách Kế toán phụ trách.</td></tr>
                    </tbody>
                  </table>
                </div>
              </div>

            </div>
</div>
