<!-- MODAL 1: TẠO TÒA NHÀ MỚI (POST /api/v1/admin/buildings) -->
<div id="modal-create-building" class="mo-modal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(15,23,42,0.6);backdrop-filter:blur(3px);z-index:9999;align-items:center;justify-content:center;">
  <div style="background:#ffffff;border-radius:12px;width:100%;max-width:640px;box-shadow:0 20px 25px -5px rgba(0,0,0,0.1);overflow:hidden;border:1px solid #e2e8f0;max-height:92vh;display:flex;flex-direction:column;">
    <div style="background:var(--mo-primary);color:#ffffff;padding:16px 20px;display:flex;justify-content:space-between;align-items:center;">
      <h3 style="margin:0;font-size:16px;font-weight:700;"><i class="fa-solid fa-building"></i> Thêm Tòa Nhà Văn Phòng Mới Vào Hệ Thống</h3>
      <button type="button" onclick="closeCreateBuildingModal()" style="background:none;border:none;color:#ffffff;font-size:18px;cursor:pointer;">&times;</button>
    </div>
    <form id="form-create-building" onsubmit="submitCreateBuilding(event)" style="padding:20px;overflow-y:auto;flex:1;">
      <div style="display:grid;grid-template-columns:1.4fr 1fr;gap:12px;margin-bottom:12px;">
        <div class="form-field">
          <label style="font-size:12px;font-weight:600;color:#334155;">Tên Tòa Nhà <span style="color:#ef4444;">*</span></label>
          <input type="text" id="build-name" class="filament-input-sm" required placeholder="VD: Landmark 81 Office Tower">
        </div>
        <div class="form-field">
          <label style="font-size:12px;font-weight:600;color:#334155;">Mã Tòa Nhà (Tự sinh nếu trống)</label>
          <input type="text" id="build-code" class="filament-input-sm" placeholder="VD: BLD-LM81">
        </div>
      </div>
      <div style="display:grid;grid-template-columns:1.5fr 1fr 1fr;gap:12px;margin-bottom:12px;">
        <div class="form-field">
          <label style="font-size:12px;font-weight:600;color:#334155;">Địa Chỉ <span style="color:#ef4444;">*</span></label>
          <input type="text" id="build-address" class="filament-input-sm" required placeholder="Số 720A Điện Biên Phủ">
        </div>
        <div class="form-field">
          <label style="font-size:12px;font-weight:600;color:#334155;">Quận / Huyện</label>
          <input type="text" id="build-district" class="filament-input-sm" value="Bình Thạnh">
        </div>
        <div class="form-field">
          <label style="font-size:12px;font-weight:600;color:#334155;">Phân Hạng</label>
          <select id="build-grade" class="filament-input-sm">
            <option value="A">Hạng A</option>
            <option value="B" selected>Hạng B</option>
            <option value="C">Hạng C</option>
          </select>
        </div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;margin-bottom:12px;">
        <div class="form-field">
          <label style="font-size:12px;font-weight:600;color:#334155;">Số Tầng Nổi</label>
          <input type="number" id="build-floors" value="15" min="1" class="filament-input-sm">
        </div>
        <div class="form-field">
          <label style="font-size:12px;font-weight:600;color:#334155;">Số Hầm</label>
          <input type="number" id="build-basements" value="2" min="0" class="filament-input-sm">
        </div>
        <div class="form-field">
          <label style="font-size:12px;font-weight:600;color:#334155;">Tổng Diện Tích Sử Dụng (m²)</label>
          <input type="number" step="0.1" id="build-net-area" class="filament-input-sm" placeholder="VD: 12000">
        </div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px;">
        <div class="form-field">
          <label style="font-size:12px;font-weight:600;color:#334155;">Đơn Giá Thuê Niêm Yết ($/m²/tháng)</label>
          <input type="number" step="0.1" id="build-base-price" class="filament-input-sm" placeholder="VD: 32">
        </div>
        <div class="form-field">
          <label style="font-size:12px;font-weight:600;color:#334155;">Phí Dịch Vụ Quản Lý ($/m²/tháng)</label>
          <input type="number" step="0.1" id="build-service-fee" class="filament-input-sm" placeholder="VD: 6">
        </div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px;">
        <div class="form-field">
          <label style="font-size:12px;font-weight:600;color:#334155;">Tiền Điện</label>
          <input type="text" id="build-electric-fee" class="filament-input-sm" placeholder="Theo đồng hồ riêng / giá nhà nước">
        </div>
        <div class="form-field">
          <label style="font-size:12px;font-weight:600;color:#334155;">Tiền Nước</label>
          <input type="text" id="build-water-fee" class="filament-input-sm" placeholder="Miễn phí / theo chỉ số">
        </div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px;">
        <div class="form-field">
          <label style="font-size:12px;font-weight:600;color:#334155;">Phí Gửi Xe Máy (VNĐ/tháng)</label>
          <input type="text" id="build-motor-fee" class="filament-input-sm" placeholder="150.000 đ/chiếc">
        </div>
        <div class="form-field">
          <label style="font-size:12px;font-weight:600;color:#334155;">Phí Gửi Ô Tô (VNĐ/tháng)</label>
          <input type="text" id="build-car-fee" class="filament-input-sm" placeholder="2.000.000 đ/chiếc">
        </div>
      </div>
      <div style="margin-bottom:16px;">
        <label style="display:flex;align-items:center;gap:8px;font-size:13px;font-weight:600;color:#1e293b;cursor:pointer;">
          <input type="checkbox" id="build-published" checked style="width:16px;height:16px;">
          <span>Phát hành công khai lên Cổng Portal chào thuê khách hàng</span>
        </label>
      </div>
      <div style="display:flex;justify-content:flex-end;gap:8px;border-top:1px solid #f1f5f9;padding-top:14px;">
        <button type="button" class="btn-mo-outline" onclick="closeCreateBuildingModal()">Hủy</button>
        <button type="submit" class="btn-mo-orange"><i class="fa-solid fa-plus"></i> Lưu Tòa Nhà Mới</button>
      </div>
    </form>
  </div>
</div>

<!-- MODAL 2: SỬA TÒA NHÀ (PUT /api/v1/admin/buildings/{id}) -->
<div id="modal-edit-building" class="mo-modal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(15,23,42,0.6);backdrop-filter:blur(3px);z-index:9999;align-items:center;justify-content:center;">
  <div style="background:#ffffff;border-radius:12px;width:100%;max-width:640px;box-shadow:0 20px 25px -5px rgba(0,0,0,0.1);overflow:hidden;border:1px solid #e2e8f0;max-height:92vh;display:flex;flex-direction:column;">
    <div style="background:var(--mo-primary);color:#ffffff;padding:16px 20px;display:flex;justify-content:space-between;align-items:center;">
      <h3 style="margin:0;font-size:16px;font-weight:700;"><i class="fa-solid fa-pen-to-square"></i> Cập Nhật Thông Số Quản Trị Tòa Nhà</h3>
      <button type="button" onclick="closeEditBuildingModal()" style="background:none;border:none;color:#ffffff;font-size:18px;cursor:pointer;">&times;</button>
    </div>
    <form id="form-edit-building" onsubmit="submitEditBuilding(event)" style="padding:20px;overflow-y:auto;flex:1;">
      <div style="display:grid;grid-template-columns:1.4fr 1fr;gap:12px;margin-bottom:12px;">
        <div class="form-field">
          <label style="font-size:12px;font-weight:600;color:#334155;">Tên Tòa Nhà <span style="color:#ef4444;">*</span></label>
          <input type="text" id="edit-build-name" class="filament-input-sm" required>
        </div>
        <div class="form-field">
          <label style="font-size:12px;font-weight:600;color:#334155;">Mã Tòa Nhà</label>
          <input type="text" id="edit-build-code" class="filament-input-sm">
        </div>
      </div>
      <div style="display:grid;grid-template-columns:1.5fr 1fr 1fr;gap:12px;margin-bottom:12px;">
        <div class="form-field">
          <label style="font-size:12px;font-weight:600;color:#334155;">Địa Chỉ <span style="color:#ef4444;">*</span></label>
          <input type="text" id="edit-build-address" class="filament-input-sm" required>
        </div>
        <div class="form-field">
          <label style="font-size:12px;font-weight:600;color:#334155;">Quận / Huyện</label>
          <input type="text" id="edit-build-district" class="filament-input-sm">
        </div>
        <div class="form-field">
          <label style="font-size:12px;font-weight:600;color:#334155;">Phân Hạng</label>
          <select id="edit-build-grade" class="filament-input-sm">
            <option value="A">Hạng A</option>
            <option value="B">Hạng B</option>
            <option value="C">Hạng C</option>
          </select>
        </div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;margin-bottom:12px;">
        <div class="form-field">
          <label style="font-size:12px;font-weight:600;color:#334155;">Số Tầng Nổi</label>
          <input type="number" id="edit-build-floors" class="filament-input-sm">
        </div>
        <div class="form-field">
          <label style="font-size:12px;font-weight:600;color:#334155;">Số Hầm</label>
          <input type="number" id="edit-build-basements" class="filament-input-sm">
        </div>
        <div class="form-field">
          <label style="font-size:12px;font-weight:600;color:#334155;">Tổng DT Sử Dụng (m²)</label>
          <input type="number" step="0.1" id="edit-build-net-area" class="filament-input-sm">
        </div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px;">
        <div class="form-field">
          <label style="font-size:12px;font-weight:600;color:#334155;">Đơn Giá Thuê Niêm Yết ($/m²)</label>
          <input type="number" step="0.1" id="edit-build-base-price" class="filament-input-sm">
        </div>
        <div class="form-field">
          <label style="font-size:12px;font-weight:600;color:#334155;">Phí Quản Lý ($/m²)</label>
          <input type="number" step="0.1" id="edit-build-service-fee" class="filament-input-sm">
        </div>
      </div>
      <div style="margin-bottom:16px;">
        <label style="display:flex;align-items:center;gap:8px;font-size:13px;font-weight:600;color:#1e293b;cursor:pointer;">
          <input type="checkbox" id="edit-build-published" style="width:16px;height:16px;">
          <span>Hiển thị công khai trên Portal Bất động sản</span>
        </label>
      </div>
      <div style="display:flex;justify-content:flex-end;gap:8px;border-top:1px solid #f1f5f9;padding-top:14px;">
        <button type="button" class="btn-mo-outline" onclick="closeEditBuildingModal()">Đóng</button>
        <button type="submit" class="btn-mo-primary"><i class="fa-solid fa-save"></i> Cập Nhật Tòa Nhà</button>
      </div>
    </form>
  </div>
</div>

<!-- MODAL 3: QUẢN LÝ ALBUM ẢNH TÒA NHÀ (Supabase Storage) -->
<div id="modal-building-media" class="mo-modal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(15,23,42,0.6);backdrop-filter:blur(3px);z-index:9999;align-items:center;justify-content:center;">
  <div style="background:#ffffff;border-radius:12px;width:100%;max-width:760px;box-shadow:0 20px 25px -5px rgba(0,0,0,0.1);overflow:hidden;border:1px solid #e2e8f0;max-height:90vh;display:flex;flex-direction:column;">
    <div style="background:var(--mo-primary);color:#ffffff;padding:16px 20px;display:flex;justify-content:space-between;align-items:center;">
      <h3 style="margin:0;font-size:16px;font-weight:700;"><i class="fa-solid fa-images"></i> Thư Viện Ảnh Tòa Nhà: <span id="media-building-name"></span></h3>
      <button type="button" onclick="closeBuildingMediaModal()" style="background:none;border:none;color:#ffffff;font-size:18px;cursor:pointer;">&times;</button>
    </div>
    <div style="padding:18px;overflow-y:auto;flex:1;">
      <!-- Form Upload ảnh mới -->
      <form onsubmit="submitUploadBuildingImages(event)" style="background:#f8fafc;padding:12px;border:1px dashed #cbd5e1;border-radius:8px;margin-bottom:16px;display:flex;gap:10px;align-items:center;">
        <input type="file" id="building-images-input" accept="image/*" multiple required style="font-size:12px;flex:1;">
        <button type="submit" class="btn-mo-orange" style="padding:6px 14px;font-size:12px;white-space:nowrap;">
          <i class="fa-solid fa-cloud-arrow-up"></i> Upload Supabase
        </button>
      </form>
      <!-- Grid hiển thị ảnh -->
      <div id="building-images-grid" style="display:grid;grid-template-columns:repeat(4, 1fr);gap:12px;"></div>
    </div>
  </div>
</div>

<!-- MODAL 4: QUẢN LÝ CÁC SÀN MẶT BẰNG (UNITS) -->
<div id="modal-manage-units" class="mo-modal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(15,23,42,0.6);backdrop-filter:blur(3px);z-index:9999;align-items:center;justify-content:center;">
  <div style="background:#ffffff;border-radius:12px;width:100%;max-width:880px;box-shadow:0 20px 25px -5px rgba(0,0,0,0.1);overflow:hidden;border:1px solid #e2e8f0;max-height:92vh;display:flex;flex-direction:column;">
    <div style="background:var(--mo-primary);color:#ffffff;padding:16px 20px;display:flex;justify-content:space-between;align-items:center;">
      <h3 style="margin:0;font-size:16px;font-weight:700;"><i class="fa-solid fa-layer-group"></i> Quản Lý Mặt Bằng & Diện Tích Cắt Thuê: <span id="units-modal-building-name"></span></h3>
      <button type="button" onclick="closeManageBuildingUnitsModal()" style="background:none;border:none;color:#ffffff;font-size:18px;cursor:pointer;">&times;</button>
    </div>
    <div style="padding:18px;overflow-y:auto;flex:1;">
      <!-- Form thêm sàn mới nhanh -->
      <form id="form-create-unit" onsubmit="submitCreateUnit(event)" style="background:#f8fafc;padding:14px;border-radius:8px;border:1px solid #e2e8f0;margin-bottom:16px;">
        <div style="font-size:13px;font-weight:700;color:var(--mo-primary);margin-bottom:10px;"><i class="fa-solid fa-plus-circle"></i> Tạo Thêm Sàn Mặt Bằng Mới</div>
        <div style="display:grid;grid-template-columns:1.2fr 1fr 1fr 1fr 1.5fr auto;gap:10px;align-items:flex-end;">
          <div class="form-field">
            <label style="font-size:11px;font-weight:600;color:#475569;">Mã Sàn <span style="color:#ef4444;">*</span></label>
            <input type="text" id="new-unit-code" placeholder="VD: L12-01" class="filament-input-sm" required>
          </div>
          <div class="form-field">
            <label style="font-size:11px;font-weight:600;color:#475569;">Tầng</label>
            <input type="text" id="new-unit-floor" placeholder="VD: 12" class="filament-input-sm">
          </div>
          <div class="form-field">
            <label style="font-size:11px;font-weight:600;color:#475569;">Diện Tích (m²) <span style="color:#ef4444;">*</span></label>
            <input type="number" step="0.1" id="new-unit-area" placeholder="VD: 250" class="filament-input-sm" required>
          </div>
          <div class="form-field">
            <label style="font-size:11px;font-weight:600;color:#475569;">Giá Thuê ($/m²) <span style="color:#ef4444;">*</span></label>
            <input type="number" step="0.1" id="new-unit-price" placeholder="VD: 35" class="filament-input-sm" required>
          </div>
          <div class="form-field">
            <label style="font-size:11px;font-weight:600;color:#475569;">Phí DV ($/m²)</label>
            <input type="number" step="0.1" id="new-unit-service-fee" placeholder="VD: 6" class="filament-input-sm">
          </div>
          <button type="submit" class="btn-mo-orange" style="padding:6px 14px;font-size:12px;height:32px;">
            <i class="fa-solid fa-plus"></i> Thêm Sàn
          </button>
        </div>
      </form>

      <!-- Bảng danh sách sàn -->
      <table class="mo-table" style="font-size:12px;">
        <thead>
          <tr>
            <th>Mã Sàn</th>
            <th>Vị Trí Tầng</th>
            <th>Diện Tích</th>
            <th>Đơn Giá Thuê</th>
            <th>Trạng Thái</th>
            <th style="text-align:right;">Thao Tác</th>
          </tr>
        </thead>
        <tbody id="building-units-tbody">
          <!-- Rendered via JS -->
        </tbody>
      </table>
    </div>
  </div>
</div>
