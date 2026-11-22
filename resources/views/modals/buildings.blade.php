@php
  $modalProvinces = $provinces ?? \App\Models\Province::orderBy('name')->get();
@endphp
<!-- DATALISTS GỢI Ý THÔNG SỐ KỸ THUẬT DÙNG CHUNG CHO FORM TÒA NHÀ -->
<datalist id="dl-air-conditioner">
  <option value="Hệ thống điều hòa trung tâm Chiller Daikin giải nhiệt nước"></option>
  <option value="Hệ thống điều hòa trung tâm VRV Daikin biến tần tiết kiệm điện"></option>
  <option value="Hệ thống điều hòa trung tâm VRF Mitsubishi Electric"></option>
  <option value="Điều hòa cục bộ âm trần cassette từng phòng"></option>
  <option value="Hệ thống HVAC trung tâm công nghệ sinh thái chuẩn Green Mark"></option>
</datalist>

<datalist id="dl-backup-generator">
  <option value="Máy phát điện Cummins đáp ứng 100% công suất 24/7"></option>
  <option value="Máy phát điện đồng bộ 100% công suất chuyển mạch tự động ATS"></option>
  <option value="100% điện dự phòng Mitsubishi Heavy Industries"></option>
  <option value="Đáp ứng 100% công suất tải hoạt động liên tục"></option>
  <option value="Chỉ hỗ trợ thang máy & hệ thống chiếu sáng công cộng"></option>
  <option value="Không có máy phát điện dự phòng"></option>
</datalist>

<!-- MODAL 1: TẠO TÒA NHÀ MỚI (POST /api/v1/admin/buildings) - BỐ CỤC PHÂN TAB GỌN ĐẸP -->
<div id="modal-create-building" class="mo-modal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(15,23,42,0.6);backdrop-filter:blur(3px);z-index:9999;align-items:center;justify-content:center;">
  <div style="background:#ffffff;border-radius:12px;width:920px;max-width:95vw;height:680px;max-height:94vh;box-shadow:0 25px 50px -12px rgba(0,0,0,0.25);overflow:hidden;border:1px solid #cbd5e1;display:flex;flex-direction:column;">
    <div style="background:var(--mo-primary);color:#ffffff;padding:12px 20px;display:flex;justify-content:space-between;align-items:center;">
      <h3 style="margin:0;font-size:15px;font-weight:700;"><i class="fa-solid fa-building"></i> Thêm Tòa Nhà Văn Phòng Mới Vào Hệ Thống</h3>
      <button type="button" onclick="closeCreateBuildingModal()" style="background:none;border:none;color:#ffffff;font-size:20px;cursor:pointer;line-height:1;">&times;</button>
    </div>
    <form id="form-create-building" novalidate onsubmit="submitCreateBuilding(event)" style="padding:16px 20px 18px;overflow-y:auto;flex:1;display:flex;flex-direction:column;box-sizing:border-box;">
      <!-- TAB NAVIGATION STRIP -->
      <div style="display:flex;gap:6px;border-bottom:2px solid #e2e8f0;margin-bottom:14px;background:#f8fafc;padding:5px 6px 0;border-radius:8px 8px 0 0;">
        <button type="button" id="tab-btn-create-bld-1" onclick="switchBuildingModalTab('create', 1)" style="flex:1;padding:9px 12px;font-size:13px;font-weight:700;border:none;background:#ffffff;cursor:pointer;border-radius:6px 6px 0 0;border-bottom:3px solid var(--mo-primary);color:var(--mo-primary);display:flex;align-items:center;justify-content:center;gap:7px;white-space:nowrap;transition:all 0.15s;">
          <i class="fa-solid fa-map-location-dot" style="color:#0284c7;"></i> Thông Tin & Địa Giới
        </button>
        <button type="button" id="tab-btn-create-bld-2" onclick="switchBuildingModalTab('create', 2)" style="flex:1;padding:9px 12px;font-size:13px;font-weight:600;border:none;background:none;cursor:pointer;border-radius:6px 6px 0 0;border-bottom:3px solid transparent;color:#64748b;display:flex;align-items:center;justify-content:center;gap:7px;white-space:nowrap;transition:all 0.15s;">
          <i class="fa-solid fa-sliders" style="color:#eab308;"></i> Thông Số Kỹ Thuật
        </button>
        <button type="button" id="tab-btn-create-bld-3" onclick="switchBuildingModalTab('create', 3)" style="flex:1;padding:9px 12px;font-size:13px;font-weight:600;border:none;background:none;cursor:pointer;border-radius:6px 6px 0 0;border-bottom:3px solid transparent;color:#64748b;display:flex;align-items:center;justify-content:center;gap:7px;white-space:nowrap;transition:all 0.15s;">
          <i class="fa-solid fa-file-invoice-dollar" style="color:#10b981;"></i> Chính Sách & Biểu Phí
        </button>
        <button type="button" id="tab-btn-create-bld-4" onclick="switchBuildingModalTab('create', 4)" style="flex:1;padding:9px 12px;font-size:13px;font-weight:600;border:none;background:none;cursor:pointer;border-radius:6px 6px 0 0;border-bottom:3px solid transparent;color:#64748b;display:flex;align-items:center;justify-content:center;gap:7px;white-space:nowrap;transition:all 0.15s;">
          <i class="fa-solid fa-images" style="color:#ec4899;"></i> Hình Ảnh <span style="color:#ef4444;font-weight:700;">*</span>
        </button>
      </div>

      <!-- TAB PANE 1: THÔNG TIN TÒA NHÀ & ĐỊA GIỚI HÀNH CHÍNH -->
      <div id="tab-pane-create-bld-1" style="display:flex;flex-direction:column;flex:1;">
        <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:16px 18px;display:flex;flex-direction:column;gap:6px;flex:1;box-sizing:border-box;">
          <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;">
            <div class="form-field" style="grid-column: span 2;">
              <label style="font-size:12px;font-weight:600;color:#334155;margin-bottom:0;display:block;">Tên Tòa Nhà <span style="color:#ef4444;">*</span></label>
              <input type="text" id="build-name" class="filament-input-sm" placeholder="VD: Landmark 81 Tower" style="height:36px;width:100%;" oninput="moClearFieldError('build-name', 'err-build-name')" onblur="validateBuildingField('create', 'name')">
              <div class="field-error-slot" id="err-build-name"></div>
            </div>
            <div class="form-field" style="grid-column: span 1;">
              <label style="font-size:12px;font-weight:600;color:#334155;margin-bottom:0;display:block;">Mã Tòa Nhà (Tự sinh)</label>
              <input type="text" id="build-code" class="filament-input-sm" placeholder="VD: BLD-LM81" style="height:36px;width:100%;" oninput="moClearFieldError('build-code', 'err-build-code')" onblur="validateBuildingField('create', 'code')">
              <div class="field-error-slot" id="err-build-code"></div>
            </div>
          </div>

          <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
            <div class="form-field">
              <label style="font-size:12px;font-weight:600;color:#334155;margin-bottom:0;display:block;">Phân Hạng Tòa Nhà</label>
              <select id="build-grade" class="filament-input-sm" style="height:36px;width:100%;" onchange="moClearFieldError('build-grade', 'err-build-grade'); validateBuildingField('create', 'grade');">
                <option value="A" selected>Hạng A (Cao cấp)</option>
                <option value="B">Hạng B (Tiêu chuẩn)</option>
                <option value="C">Hạng C (Giá rẻ)</option>
              </select>
              <div class="field-error-slot" id="err-build-grade"></div>
            </div>
            <div class="form-field">
              <label style="font-size:12px;font-weight:600;color:#334155;margin-bottom:0;display:block;">Hướng Chính Diện Tòa Nhà</label>
              <select id="build-direction" class="filament-input-sm" style="height:36px;width:100%;">
                <option value="" selected>-- Chọn hướng tòa nhà --</option>
                <option value="Đông">Đông (East)</option>
                <option value="Tây">Tây (West)</option>
                <option value="Nam">Nam (South)</option>
                <option value="Bắc">Bắc (North)</option>
                <option value="Đông Nam">Đông Nam (Southeast)</option>
                <option value="Đông Bắc">Đông Bắc (Northeast)</option>
                <option value="Tây Nam">Tây Nam (Southwest)</option>
                <option value="Tây Bắc">Tây Bắc (Northwest)</option>
              </select>
              <div class="field-error-slot"></div>
            </div>
          </div>

          <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;">
            <div class="form-field">
              <label style="font-size:12px;font-weight:600;color:#334155;margin-bottom:0;display:block;">Tỉnh / Thành Phố <span style="color:#ef4444;">*</span></label>
              <select id="build-province" class="filament-input-sm" onchange="onBuildingProvinceChange(this, 'build-district'); moClearFieldError('build-province', 'err-build-province'); validateBuildingField('create', 'province');" style="height:36px;width:100%;">
                @foreach($modalProvinces as $p)
                  <option value="{{ $p->code }}" data-name="{{ $p->name }}" {{ $loop->first ? 'selected' : '' }}>{{ $p->name }}</option>
                @endforeach
              </select>
              <div class="field-error-slot" id="err-build-province"></div>
            </div>
            <div class="form-field">
              <label style="font-size:12px;font-weight:600;color:#334155;margin-bottom:0;display:block;">Quận / Huyện <span style="color:#ef4444;">*</span></label>
              <select id="build-district" class="filament-input-sm" style="height:36px;width:100%;" onchange="moClearFieldError('build-district', 'err-build-district'); validateBuildingField('create', 'district');">
                <option value="">-- Chọn Quận / Huyện --</option>
              </select>
              <div class="field-error-slot" id="err-build-district"></div>
            </div>
            <div class="form-field">
              <label style="font-size:12px;font-weight:600;color:#334155;margin-bottom:0;display:block;">Phường / Xã</label>
              <input type="text" id="build-ward" class="filament-input-sm" placeholder="VD: Phường 22" style="height:36px;width:100%;" oninput="moClearFieldError('build-ward', 'err-build-ward')">
              <div class="field-error-slot" id="err-build-ward"></div>
            </div>
          </div>

          <div class="form-field">
            <label style="font-size:12px;font-weight:600;color:#334155;margin-bottom:0;display:block;">Địa Chỉ Chi Tiết (Số nhà, tên đường) <span style="color:#ef4444;">*</span></label>
            <input type="text" id="build-address" class="filament-input-sm" placeholder="Số 720A Điện Biên Phủ, Phường 22" style="height:36px;width:100%;" oninput="moClearFieldError('build-address', 'err-build-address')" onblur="validateBuildingField('create', 'address')">
            <div class="field-error-slot" id="err-build-address"></div>
          </div>
        </div>
      </div>

      <!-- TAB PANE 2: THÔNG SỐ KỸ THUẬT & GIỜ VẬN HÀNH -->
      <div id="tab-pane-create-bld-2" style="display:none;flex-direction:column;flex:1;">
        <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:16px 18px;display:flex;flex-direction:column;gap:6px;flex:1;box-sizing:border-box;">
          <div style="display:grid;grid-template-columns:1fr 1fr 1.2fr;gap:12px;">
            <div class="form-field">
              <label style="font-size:12px;font-weight:600;color:#334155;margin-bottom:0;display:block;">Số Tầng Nổi <span style="color:#ef4444;">*</span></label>
              <input type="number" id="build-floors" value="1" min="1" class="filament-input-sm" style="height:36px;width:100%;" oninput="moSanitizeNumeric(this); moClearFieldError('build-floors', 'err-build-floors');" onblur="validateBuildingField('create', 'floors')">
              <div class="field-error-slot" id="err-build-floors"></div>
            </div>
            <div class="form-field">
              <label style="font-size:12px;font-weight:600;color:#334155;margin-bottom:0;display:block;">Số Tầng Hầm</label>
              <input type="number" id="build-basements" value="0" min="0" class="filament-input-sm" style="height:36px;width:100%;" oninput="moSanitizeNumeric(this); moClearFieldError('build-basements', 'err-build-basements');" onblur="validateBuildingField('create', 'basements')">
              <div class="field-error-slot" id="err-build-basements"></div>
            </div>
            <div class="form-field">
              <label style="font-size:12px;font-weight:600;color:#334155;margin-bottom:0;display:block;">Tổng DT Sàn Khả Dụng (m²) <span style="color:#ef4444;">*</span></label>
              <input type="number" step="0.1" min="0" id="build-net-area" class="filament-input-sm" placeholder="VD: 12000" style="height:36px;width:100%;" oninput="moSanitizeDecimal(this); moClearFieldError('build-net-area', 'err-build-net-area');" onblur="validateBuildingField('create', 'net-area')">
              <div class="field-error-slot" id="err-build-net-area"></div>
            </div>
          </div>

          <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
            <div class="form-field">
              <label style="font-size:12px;font-weight:600;color:#334155;margin-bottom:0;display:block;">Chiều Cao Trần Tiêu Chuẩn (m)</label>
              <input type="number" step="0.05" min="1" id="build-ceiling-height" value="2.70" class="filament-input-sm" placeholder="2.70" style="height:36px;width:100%;" oninput="moSanitizeDecimal(this); moClearFieldError('build-ceiling-height', 'err-build-ceiling-height');" onblur="validateBuildingField('create', 'ceiling-height')">
              <div class="field-error-slot" id="err-build-ceiling-height"></div>
            </div>
            <div class="form-field">
              <label style="font-size:12px;font-weight:600;color:#334155;margin-bottom:0;display:block;">Số Lượng Thang Máy</label>
              <input type="number" id="build-elevators" value="1" min="0" class="filament-input-sm" style="height:36px;width:100%;" oninput="moSanitizeNumeric(this); moClearFieldError('build-elevators', 'err-build-elevators');" onblur="validateBuildingField('create', 'elevators')">
              <div class="field-error-slot" id="err-build-elevators"></div>
            </div>
          </div>

          <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
            <div class="form-field">
              <label style="font-size:12px;font-weight:600;color:#334155;margin-bottom:0;display:block;">Hệ Thống Điều Hòa Không Khí</label>
              <input type="text" id="build-air-conditioner" list="dl-air-conditioner" class="filament-input-sm" placeholder="Chọn gợi ý hoặc nhập thông số điều hòa..." style="height:36px;width:100%;">
              <div class="field-error-slot"></div>
            </div>
            <div class="form-field">
              <label style="font-size:12px;font-weight:600;color:#334155;margin-bottom:0;display:block;">Máy Phát Điện Dự Phòng</label>
              <input type="text" id="build-backup-generator" list="dl-backup-generator" class="filament-input-sm" placeholder="Chọn gợi ý hoặc nhập công suất dự phòng..." style="height:36px;width:100%;">
              <div class="field-error-slot"></div>
            </div>
          </div>

          <div class="form-field">
            <label style="font-size:12px;font-weight:600;color:#334155;margin-bottom:0;display:block;">Giờ Làm Việc Tiêu Chuẩn</label>
            <input type="text" id="build-working-hours" class="filament-input-sm" value="" placeholder="VD: Thứ 2 - 6: 08:00 - 18:00, Thứ 7: 08:00 - 12:00" style="height:36px;width:100%;">
            <div class="field-error-slot"></div>
          </div>
        </div>
      </div>

      <!-- TAB PANE 3: CHÍNH SÁCH THUÊ & BIỂU PHÍ DỊCH VỤ -->
      <div id="tab-pane-create-bld-3" style="display:none;flex-direction:column;flex:1;">
        <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:16px 18px;display:flex;flex-direction:column;gap:6px;flex:1;box-sizing:border-box;">
          <div style="display:grid;grid-template-columns:1.2fr 1fr 1fr;gap:12px;">
            <div class="form-field">
              <label style="font-size:12px;font-weight:600;color:#334155;margin-bottom:0;display:block;">Phí Ngoài Giờ (VNĐ/h)</label>
              <input type="number" step="1000" id="build-overtime-fee" value="0" min="0" class="filament-input-sm" placeholder="0 = Miễn phí" style="height:36px;width:100%;" oninput="moSanitizeNumeric(this); moClearFieldError('build-overtime-fee', 'err-build-overtime-fee');">
              <div class="field-error-slot" id="err-build-overtime-fee"></div>
            </div>
            <div class="form-field">
              <label style="font-size:12px;font-weight:600;color:#334155;margin-bottom:0;display:block;">Đặt Cọc (tháng)</label>
              <input type="number" id="build-deposit-term" value="3" min="1" class="filament-input-sm" style="height:36px;width:100%;" oninput="moSanitizeNumeric(this); moClearFieldError('build-deposit-term', 'err-build-deposit-term');" onblur="validateBuildingField('create', 'deposit-term')">
              <div class="field-error-slot" id="err-build-deposit-term"></div>
            </div>
            <div class="form-field">
              <label style="font-size:12px;font-weight:600;color:#334155;margin-bottom:0;display:block;">Kỳ TT (tháng/lần)</label>
              <input type="number" id="build-payment-term" value="3" min="1" class="filament-input-sm" style="height:36px;width:100%;" oninput="moSanitizeNumeric(this); moClearFieldError('build-payment-term', 'err-build-payment-term');" onblur="validateBuildingField('create', 'payment-term')">
              <div class="field-error-slot" id="err-build-payment-term"></div>
            </div>
          </div>

          <div style="display:grid;grid-template-columns:1.2fr 1fr;gap:12px;">
            <div class="form-field">
              <label style="font-size:12px;font-weight:600;color:#334155;margin-bottom:0;display:block;">Khoảng Giá Thuê (Min - Max)</label>
              <input type="text" disabled class="filament-input-sm" value="Tự động cập nhật theo mặt bằng" style="background:#f1f5f9;color:#64748b;cursor:not-allowed;font-style:italic;height:36px;width:100%;" title="Tự động cập nhật theo mặt bằng">
              <div class="field-error-slot"></div>
            </div>
            <div class="form-field">
              <label style="font-size:12px;font-weight:600;color:#334155;margin-bottom:0;display:block;">Phí Quản Lý Dịch Vụ (VNĐ/m²) <span style="color:#ef4444;">*</span></label>
              <input type="number" step="1000" min="0" id="build-service-fee" value="0" class="filament-input-sm" placeholder="0" style="height:36px;width:100%;" oninput="moSanitizeNumeric(this); moClearFieldError('build-service-fee', 'err-build-service-fee');" onblur="validateBuildingField('create', 'service-fee')">
              <div class="field-error-slot" id="err-build-service-fee"></div>
            </div>
          </div>

          <div style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:12px;">
            <div class="form-field">
              <label style="font-size:12px;font-weight:600;color:#334155;margin-bottom:0;display:block;">Điện (đ/kWh) <span style="color:#ef4444;">*</span></label>
              <input type="number" step="100" min="0" id="build-electric-fee" value="4500" class="filament-input-sm" placeholder="0" style="height:36px;width:100%;" oninput="moSanitizeNumeric(this); moClearFieldError('build-electric-fee', 'err-build-electric-fee');" onblur="validateBuildingField('create', 'electric-fee')">
              <div class="field-error-slot" id="err-build-electric-fee"></div>
            </div>
            <div class="form-field">
              <label style="font-size:12px;font-weight:600;color:#334155;margin-bottom:0;display:block;">Nước (đ/m³) <span style="color:#ef4444;">*</span></label>
              <input type="number" step="500" min="0" id="build-water-fee" value="25000" class="filament-input-sm" placeholder="0" style="height:36px;width:100%;" oninput="moSanitizeNumeric(this); moClearFieldError('build-water-fee', 'err-build-water-fee');" onblur="validateBuildingField('create', 'water-fee')">
              <div class="field-error-slot" id="err-build-water-fee"></div>
            </div>
            <div class="form-field">
              <label style="font-size:12px;font-weight:600;color:#334155;margin-bottom:0;display:block;">Xe Máy (đ/tháng)</label>
              <input type="number" step="5000" min="0" id="build-motor-fee" value="0" class="filament-input-sm" placeholder="0" style="height:36px;width:100%;" oninput="moSanitizeNumeric(this); moClearFieldError('build-motor-fee', 'err-build-motor-fee');">
              <div class="field-error-slot" id="err-build-motor-fee"></div>
            </div>
            <div class="form-field">
              <label style="font-size:12px;font-weight:600;color:#334155;margin-bottom:0;display:block;">Ô Tô (đ/tháng)</label>
              <input type="number" step="50000" min="0" id="build-car-fee" value="0" class="filament-input-sm" placeholder="0" style="height:36px;width:100%;" oninput="moSanitizeNumeric(this); moClearFieldError('build-car-fee', 'err-build-car-fee');">
              <div class="field-error-slot" id="err-build-car-fee"></div>
            </div>
          </div>

          <div class="form-field">
            <label style="font-size:12px;font-weight:600;color:#334155;margin-bottom:0;display:block;">Trạng Thái Hiển Thị</label>
            <label style="display:flex !important;align-items:center !important;justify-content:flex-start !important;gap:8px;font-size:13px;font-weight:600;color:#1e293b;cursor:pointer;background:#fff;padding:0 14px;height:36px !important;min-height:36px !important;max-height:36px !important;line-height:normal !important;border-radius:6px;border:1px solid #cbd5e1;box-sizing:border-box !important;margin:0 !important;overflow:visible !important;">
              <input type="checkbox" id="build-published" checked style="width:16px;height:16px;cursor:pointer;flex-shrink:0;">
              <span><i class="fa-solid fa-globe" style="color:#0284c7;"></i> Hiển thị công khai trên Cổng Portal chào thuê khách hàng</span>
            </label>
            <div class="field-error-slot"></div>
          </div>
        </div>
      </div>

      <!-- TAB PANE 4: HÌNH ẢNH TÒA NHÀ (BẮT BUỘC) -->
      <div id="tab-pane-create-bld-4" style="display:none;flex-direction:column;flex:1;">
        <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:14px 16px;display:flex;flex-direction:column;flex:1;box-sizing:border-box;">
          <!-- Dropzone button -->
          <div onclick="document.getElementById('create-build-images-input').click()" style="border:2px dashed #cbd5e1;border-radius:8px;background:#ffffff;padding:10px;text-align:center;cursor:pointer;transition:border-color 0.2s;" ondragover="event.preventDefault();this.style.borderColor='var(--mo-primary)';" ondragleave="this.style.borderColor='#cbd5e1';" ondrop="handleCreateBuildingDrop(event)">
            <input type="file" id="create-build-images-input" accept="image/png,image/jpeg,image/webp,image/jpg" multiple style="display:none;" onchange="handleCreateBuildingImagesSelect(this); moClearFieldError('create-build-images-input', 'err-build-images');">
            <i class="fa-solid fa-cloud-arrow-up" style="font-size:22px;color:#0284c7;margin-bottom:2px;display:block;"></i>
            <span style="font-size:12.5px;font-weight:600;color:#1e293b;display:block;">Nhấn vào đây hoặc kéo thả ảnh để tải lên (PNG, JPG, WEBP)</span>
            <span style="font-size:11px;color:#ef4444;font-weight:600;"><i class="fa-solid fa-circle-exclamation"></i> Bắt buộc tải lên ít nhất 1 ảnh đại diện cho tòa nhà (Tối đa 5MB/ảnh)</span>
          </div>
          <div class="field-error-slot" id="err-build-images" style="justify-content:center;"></div>

          <!-- Preview Grid -->
          <div style="flex:1;margin-top:6px;overflow-y:auto;background:#ffffff;border:1px solid #e2e8f0;border-radius:6px;padding:8px;box-sizing:border-box;">
            <div id="create-build-images-preview" style="display:grid;grid-template-columns:repeat(4, 1fr);gap:10px;">
              <div id="create-build-images-empty" style="grid-column:1/-1;text-align:center;padding:36px 10px;color:#94a3b8;font-size:12.5px;">
                <i class="fa-regular fa-images" style="font-size:32px;margin-bottom:8px;display:block;color:#cbd5e1;"></i>
                Chưa có ảnh nào được chọn. Nhấn nút phía trên để tải lên hình ảnh tòa nhà.
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- THANH NÚT ĐIỀU KHIỂN DƯỚI CÙNG -->
      <div style="display:flex;justify-content:flex-end;gap:8px;border-top:1px solid #e2e8f0;padding-top:14px;margin-top:auto;">
        <button type="button" class="btn-mo-outline" onclick="closeCreateBuildingModal()" style="padding:7px 16px;font-size:13px;">Hủy</button>
        <button type="submit" class="btn-mo-orange" style="padding:7px 24px;font-size:13px;font-weight:600;"><i class="fa-solid fa-save"></i> Lưu</button>
      </div>
    </form>
  </div>
</div>

<!-- MODAL 2: SỬA TÒA NHÀ (PUT /api/v1/admin/buildings/{id}) - BỐ CỤC PHÂN TAB GỌN ĐẸP -->
<div id="modal-edit-building" class="mo-modal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(15,23,42,0.6);backdrop-filter:blur(3px);z-index:9999;align-items:center;justify-content:center;">
  <div style="background:#ffffff;border-radius:12px;width:920px;max-width:95vw;height:680px;max-height:94vh;box-shadow:0 25px 50px -12px rgba(0,0,0,0.25);overflow:hidden;border:1px solid #cbd5e1;display:flex;flex-direction:column;">
    <div style="background:var(--mo-primary);color:#ffffff;padding:12px 20px;display:flex;justify-content:space-between;align-items:center;">
      <h3 style="margin:0;font-size:15px;font-weight:700;"><i class="fa-solid fa-pen-to-square"></i> Cập Nhật Thông Số Quản Trị Tòa Nhà</h3>
      <button type="button" onclick="closeEditBuildingModal()" style="background:none;border:none;color:#ffffff;font-size:20px;cursor:pointer;line-height:1;">&times;</button>
    </div>
    <form id="form-edit-building" novalidate onsubmit="submitEditBuilding(event)" style="padding:16px 20px 18px;overflow-y:auto;flex:1;display:flex;flex-direction:column;box-sizing:border-box;">
      <!-- TAB NAVIGATION STRIP -->
      <div style="display:flex;gap:6px;border-bottom:2px solid #e2e8f0;margin-bottom:14px;background:#f8fafc;padding:5px 6px 0;border-radius:8px 8px 0 0;">
        <button type="button" id="tab-btn-edit-bld-1" onclick="switchBuildingModalTab('edit', 1)" style="flex:1;padding:9px 12px;font-size:13px;font-weight:700;border:none;background:#ffffff;cursor:pointer;border-radius:6px 6px 0 0;border-bottom:3px solid var(--mo-primary);color:var(--mo-primary);display:flex;align-items:center;justify-content:center;gap:7px;white-space:nowrap;transition:all 0.15s;">
          <i class="fa-solid fa-map-location-dot" style="color:#0284c7;"></i> Thông Tin & Địa Giới
        </button>
        <button type="button" id="tab-btn-edit-bld-2" onclick="switchBuildingModalTab('edit', 2)" style="flex:1;padding:9px 12px;font-size:13px;font-weight:600;border:none;background:none;cursor:pointer;border-radius:6px 6px 0 0;border-bottom:3px solid transparent;color:#64748b;display:flex;align-items:center;justify-content:center;gap:7px;white-space:nowrap;transition:all 0.15s;">
          <i class="fa-solid fa-sliders" style="color:#eab308;"></i> Thông Số Kỹ Thuật
        </button>
        <button type="button" id="tab-btn-edit-bld-3" onclick="switchBuildingModalTab('edit', 3)" style="flex:1;padding:9px 12px;font-size:13px;font-weight:600;border:none;background:none;cursor:pointer;border-radius:6px 6px 0 0;border-bottom:3px solid transparent;color:#64748b;display:flex;align-items:center;justify-content:center;gap:7px;white-space:nowrap;transition:all 0.15s;">
          <i class="fa-solid fa-file-invoice-dollar" style="color:#10b981;"></i> Chính Sách & Biểu Phí
        </button>
        <button type="button" id="tab-btn-edit-bld-4" onclick="switchBuildingModalTab('edit', 4)" style="flex:1;padding:9px 12px;font-size:13px;font-weight:600;border:none;background:none;cursor:pointer;border-radius:6px 6px 0 0;border-bottom:3px solid transparent;color:#64748b;display:flex;align-items:center;justify-content:center;gap:7px;white-space:nowrap;transition:all 0.15s;">
          <i class="fa-solid fa-images" style="color:#ec4899;"></i> Hình Ảnh <span style="color:#ef4444;font-weight:700;">*</span>
        </button>
      </div>

      <!-- TAB PANE 1: THÔNG TIN TÒA NHÀ & ĐỊA GIỚI HÀNH CHÍNH -->
      <div id="tab-pane-edit-bld-1" style="display:flex;flex-direction:column;flex:1;">
        <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:16px 18px;display:flex;flex-direction:column;gap:6px;flex:1;box-sizing:border-box;">
          <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;">
            <div class="form-field" style="grid-column: span 2;">
              <label style="font-size:12px;font-weight:600;color:#334155;margin-bottom:0;display:block;">Tên Tòa Nhà <span style="color:#ef4444;">*</span></label>
              <input type="text" id="edit-build-name" class="filament-input-sm" style="height:36px;width:100%;" oninput="moClearFieldError('edit-build-name', 'err-edit-build-name')" onblur="validateBuildingField('edit', 'name')">
              <div class="field-error-slot" id="err-edit-build-name"></div>
            </div>
            <div class="form-field" style="grid-column: span 1;">
              <label style="font-size:12px;font-weight:600;color:#334155;margin-bottom:0;display:block;">Mã Tòa Nhà</label>
              <input type="text" id="edit-build-code" class="filament-input-sm" style="height:36px;width:100%;" oninput="moClearFieldError('edit-build-code', 'err-edit-build-code')" onblur="validateBuildingField('edit', 'code')">
              <div class="field-error-slot" id="err-edit-build-code"></div>
            </div>
          </div>

          <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
            <div class="form-field">
              <label style="font-size:12px;font-weight:600;color:#334155;margin-bottom:0;display:block;">Phân Hạng Tòa Nhà</label>
              <select id="edit-build-grade" class="filament-input-sm" style="height:36px;width:100%;" onchange="moClearFieldError('edit-build-grade', 'err-edit-build-grade'); validateBuildingField('edit', 'grade');">
                <option value="A">Hạng A (Cao cấp)</option>
                <option value="B">Hạng B (Tiêu chuẩn)</option>
                <option value="C">Hạng C (Giá rẻ)</option>
              </select>
              <div class="field-error-slot" id="err-edit-build-grade"></div>
            </div>
            <div class="form-field">
              <label style="font-size:12px;font-weight:600;color:#334155;margin-bottom:0;display:block;">Hướng Chính Diện Tòa Nhà</label>
              <select id="edit-build-direction" class="filament-input-sm" style="height:36px;width:100%;">
                <option value="">-- Chọn hướng tòa nhà --</option>
                <option value="Đông">Đông (East)</option>
                <option value="Tây">Tây (West)</option>
                <option value="Nam">Nam (South)</option>
                <option value="Bắc">Bắc (North)</option>
                <option value="Đông Nam">Đông Nam (Southeast)</option>
                <option value="Đông Bắc">Đông Bắc (Northeast)</option>
                <option value="Tây Nam">Tây Nam (Southwest)</option>
                <option value="Tây Bắc">Tây Bắc (Northwest)</option>
              </select>
              <div class="field-error-slot"></div>
            </div>
          </div>

          <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;">
            <div class="form-field">
              <label style="font-size:12px;font-weight:600;color:#334155;margin-bottom:0;display:block;">Tỉnh / Thành Phố <span style="color:#ef4444;">*</span></label>
              <select id="edit-build-province" class="filament-input-sm" onchange="onBuildingProvinceChange(this, 'edit-build-district'); moClearFieldError('edit-build-province', 'err-edit-build-province'); validateBuildingField('edit', 'province');" style="height:36px;width:100%;">
                @foreach($modalProvinces as $p)
                  <option value="{{ $p->code }}" data-name="{{ $p->name }}">{{ $p->name }}</option>
                @endforeach
              </select>
              <div class="field-error-slot" id="err-edit-build-province"></div>
            </div>
            <div class="form-field">
              <label style="font-size:12px;font-weight:600;color:#334155;margin-bottom:0;display:block;">Quận / Huyện <span style="color:#ef4444;">*</span></label>
              <select id="edit-build-district" class="filament-input-sm" style="height:36px;width:100%;" onchange="moClearFieldError('edit-build-district', 'err-edit-build-district'); validateBuildingField('edit', 'district');">
                <option value="">-- Đang nạp danh sách... --</option>
              </select>
              <div class="field-error-slot" id="err-edit-build-district"></div>
            </div>
            <div class="form-field">
              <label style="font-size:12px;font-weight:600;color:#334155;margin-bottom:0;display:block;">Phường / Xã</label>
              <input type="text" id="edit-build-ward" class="filament-input-sm" placeholder="VD: Phường 22" style="height:36px;width:100%;" oninput="moClearFieldError('edit-build-ward', 'err-edit-build-ward')">
              <div class="field-error-slot" id="err-edit-build-ward"></div>
            </div>
          </div>

          <div class="form-field">
            <label style="font-size:12px;font-weight:600;color:#334155;margin-bottom:0;display:block;">Địa Chỉ Chi Tiết (Số nhà, tên đường) <span style="color:#ef4444;">*</span></label>
            <input type="text" id="edit-build-address" class="filament-input-sm" style="height:36px;width:100%;" oninput="moClearFieldError('edit-build-address', 'err-edit-build-address')" onblur="validateBuildingField('edit', 'address')">
            <div class="field-error-slot" id="err-edit-build-address"></div>
          </div>
        </div>
      </div>

      <!-- TAB PANE 2: THÔNG SỐ KỸ THUẬT & GIỜ VẬN HÀNH -->
      <div id="tab-pane-edit-bld-2" style="display:none;flex-direction:column;flex:1;">
        <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:16px 18px;display:flex;flex-direction:column;gap:6px;flex:1;box-sizing:border-box;">
          <div style="display:grid;grid-template-columns:1fr 1fr 1.2fr;gap:12px;">
            <div class="form-field">
              <label style="font-size:12px;font-weight:600;color:#334155;margin-bottom:0;display:block;">Số Tầng Nổi <span style="color:#ef4444;">*</span></label>
              <input type="number" id="edit-build-floors" min="1" class="filament-input-sm" style="height:36px;width:100%;" oninput="moSanitizeNumeric(this); moClearFieldError('edit-build-floors', 'err-edit-build-floors');" onblur="validateBuildingField('edit', 'floors')">
              <div class="field-error-slot" id="err-edit-build-floors"></div>
            </div>
            <div class="form-field">
              <label style="font-size:12px;font-weight:600;color:#334155;margin-bottom:0;display:block;">Số Tầng Hầm</label>
              <input type="number" id="edit-build-basements" min="0" class="filament-input-sm" style="height:36px;width:100%;" oninput="moSanitizeNumeric(this); moClearFieldError('edit-build-basements', 'err-edit-build-basements');" onblur="validateBuildingField('edit', 'basements')">
              <div class="field-error-slot" id="err-edit-build-basements"></div>
            </div>
            <div class="form-field">
              <label style="font-size:12px;font-weight:600;color:#334155;margin-bottom:0;display:block;">Tổng DT Sàn (m²) <span style="color:#ef4444;">*</span></label>
              <input type="number" step="0.1" min="0" id="edit-build-net-area" class="filament-input-sm" style="height:36px;width:100%;" oninput="moSanitizeDecimal(this); moClearFieldError('edit-build-net-area', 'err-edit-build-net-area');" onblur="validateBuildingField('edit', 'net-area')">
              <div class="field-error-slot" id="err-edit-build-net-area"></div>
            </div>
          </div>

          <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
            <div class="form-field">
              <label style="font-size:12px;font-weight:600;color:#334155;margin-bottom:0;display:block;">Chiều Cao Trần (m)</label>
              <input type="number" step="0.05" min="1" id="edit-build-ceiling-height" class="filament-input-sm" style="height:36px;width:100%;" oninput="moSanitizeDecimal(this); moClearFieldError('edit-build-ceiling-height', 'err-edit-build-ceiling-height');" onblur="validateBuildingField('edit', 'ceiling-height')">
              <div class="field-error-slot" id="err-edit-build-ceiling-height"></div>
            </div>
            <div class="form-field">
              <label style="font-size:12px;font-weight:600;color:#334155;margin-bottom:0;display:block;">Số Lượng Thang Máy</label>
              <input type="number" id="edit-build-elevators" min="0" class="filament-input-sm" style="height:36px;width:100%;" oninput="moSanitizeNumeric(this); moClearFieldError('edit-build-elevators', 'err-edit-build-elevators');" onblur="validateBuildingField('edit', 'elevators')">
              <div class="field-error-slot" id="err-edit-build-elevators"></div>
            </div>
          </div>

          <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
            <div class="form-field">
              <label style="font-size:12px;font-weight:600;color:#334155;margin-bottom:0;display:block;">Hệ Thống Điều Hòa</label>
              <input type="text" id="edit-build-air-conditioner" list="dl-air-conditioner" class="filament-input-sm" placeholder="Chọn gợi ý hoặc nhập thông số điều hòa..." style="height:36px;width:100%;">
              <div class="field-error-slot"></div>
            </div>
            <div class="form-field">
              <label style="font-size:12px;font-weight:600;color:#334155;margin-bottom:0;display:block;">Máy Phát Điện Dự Phòng</label>
              <input type="text" id="edit-build-backup-generator" list="dl-backup-generator" class="filament-input-sm" placeholder="Chọn gợi ý hoặc nhập công suất dự phòng..." style="height:36px;width:100%;">
              <div class="field-error-slot"></div>
            </div>
          </div>

          <div class="form-field">
            <label style="font-size:12px;font-weight:600;color:#334155;margin-bottom:0;display:block;">Giờ Làm Việc Tiêu Chuẩn</label>
            <input type="text" id="edit-build-working-hours" class="filament-input-sm" placeholder="VD: Thứ 2 - 6: 08:00 - 18:00, Thứ 7: 08:00 - 12:00" style="height:36px;width:100%;">
            <div class="field-error-slot"></div>
          </div>
        </div>
      </div>

      <!-- TAB PANE 3: CHÍNH SÁCH THUÊ & BIỂU PHÍ DỊCH VỤ -->
      <div id="tab-pane-edit-bld-3" style="display:none;flex-direction:column;flex:1;">
        <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:16px 18px;display:flex;flex-direction:column;gap:6px;flex:1;box-sizing:border-box;">
          <div style="display:grid;grid-template-columns:1.2fr 1fr 1fr;gap:12px;">
            <div class="form-field">
              <label style="font-size:12px;font-weight:600;color:#334155;margin-bottom:0;display:block;">Ngoài Giờ (đ/h)</label>
              <input type="number" step="1000" id="edit-build-overtime-fee" min="0" class="filament-input-sm" placeholder="0 = Free" style="height:36px;width:100%;" oninput="moSanitizeNumeric(this); moClearFieldError('edit-build-overtime-fee', 'err-edit-build-overtime-fee');">
              <div class="field-error-slot" id="err-edit-build-overtime-fee"></div>
            </div>
            <div class="form-field">
              <label style="font-size:12px;font-weight:600;color:#334155;margin-bottom:0;display:block;">Cọc (tháng)</label>
              <input type="number" id="edit-build-deposit-term" min="1" class="filament-input-sm" style="height:36px;width:100%;" oninput="moSanitizeNumeric(this); moClearFieldError('edit-build-deposit-term', 'err-edit-build-deposit-term');" onblur="validateBuildingField('edit', 'deposit-term')">
              <div class="field-error-slot" id="err-edit-build-deposit-term"></div>
            </div>
            <div class="form-field">
              <label style="font-size:12px;font-weight:600;color:#334155;margin-bottom:0;display:block;">Kỳ TT (tháng)</label>
              <input type="number" id="edit-build-payment-term" min="1" class="filament-input-sm" style="height:36px;width:100%;" oninput="moSanitizeNumeric(this); moClearFieldError('edit-build-payment-term', 'err-edit-build-payment-term');" onblur="validateBuildingField('edit', 'payment-term')">
              <div class="field-error-slot" id="err-edit-build-payment-term"></div>
            </div>
          </div>

          <div style="display:grid;grid-template-columns:1.2fr 1fr;gap:12px;">
            <div class="form-field">
              <label style="font-size:12px;font-weight:600;color:#334155;margin-bottom:0;display:block;">Khoảng Giá Thuê (Min - Max)</label>
              <input type="text" id="edit-build-price-range" class="filament-input-sm" disabled style="background:#f1f5f9;color:#0284c7;font-weight:600;cursor:not-allowed;height:36px;width:100%;" title="Khoảng giá được tính tự động từ danh sách mặt bằng">
              <div class="field-error-slot"></div>
            </div>
            <div class="form-field">
              <label style="font-size:12px;font-weight:600;color:#334155;margin-bottom:0;display:block;">Phí QL (đ/m²) <span style="color:#ef4444;">*</span></label>
              <input type="number" step="1000" id="edit-build-service-fee" class="filament-input-sm" style="height:36px;width:100%;" oninput="moSanitizeNumeric(this); moClearFieldError('edit-build-service-fee', 'err-edit-build-service-fee');" onblur="validateBuildingField('edit', 'service-fee')">
              <div class="field-error-slot" id="err-edit-build-service-fee"></div>
            </div>
          </div>

          <div style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:12px;">
            <div class="form-field">
              <label style="font-size:12px;font-weight:600;color:#334155;margin-bottom:0;display:block;">Điện (đ/kWh) <span style="color:#ef4444;">*</span></label>
              <input type="number" step="100" id="edit-build-electric-fee" class="filament-input-sm" placeholder="VD: 4500" style="height:36px;width:100%;" oninput="moSanitizeNumeric(this); moClearFieldError('edit-build-electric-fee', 'err-edit-build-electric-fee');" onblur="validateBuildingField('edit', 'electric-fee')">
              <div class="field-error-slot" id="err-edit-build-electric-fee"></div>
            </div>
            <div class="form-field">
              <label style="font-size:12px;font-weight:600;color:#334155;margin-bottom:0;display:block;">Nước (đ/m³) <span style="color:#ef4444;">*</span></label>
              <input type="number" step="500" id="edit-build-water-fee" class="filament-input-sm" placeholder="VD: 25000" style="height:36px;width:100%;" oninput="moSanitizeNumeric(this); moClearFieldError('edit-build-water-fee', 'err-edit-build-water-fee');" onblur="validateBuildingField('edit', 'water-fee')">
              <div class="field-error-slot" id="err-edit-build-water-fee"></div>
            </div>
            <div class="form-field">
              <label style="font-size:12px;font-weight:600;color:#334155;margin-bottom:0;display:block;">Xe Máy (đ/tháng)</label>
              <input type="number" step="5000" id="edit-build-motor-fee" class="filament-input-sm" placeholder="VD: 150000" style="height:36px;width:100%;" oninput="moSanitizeNumeric(this); moClearFieldError('edit-build-motor-fee', 'err-edit-build-motor-fee');">
              <div class="field-error-slot" id="err-edit-build-motor-fee"></div>
            </div>
            <div class="form-field">
              <label style="font-size:12px;font-weight:600;color:#334155;margin-bottom:0;display:block;">Ô Tô (đ/tháng)</label>
              <input type="number" step="50000" id="edit-build-car-fee" class="filament-input-sm" placeholder="VD: 2000000" style="height:36px;width:100%;" oninput="moSanitizeNumeric(this); moClearFieldError('edit-build-car-fee', 'err-edit-build-car-fee');">
              <div class="field-error-slot" id="err-edit-build-car-fee"></div>
            </div>
          </div>

          <div class="form-field">
            <label style="font-size:12px;font-weight:600;color:#334155;margin-bottom:0;display:block;">Trạng Thái Hiển Thị</label>
            <label style="display:flex !important;align-items:center !important;justify-content:flex-start !important;gap:8px;font-size:13px;font-weight:600;color:#1e293b;cursor:pointer;background:#fff;padding:0 14px;height:36px !important;min-height:36px !important;max-height:36px !important;line-height:normal !important;border-radius:6px;border:1px solid #cbd5e1;box-sizing:border-box !important;margin:0 !important;overflow:visible !important;">
              <input type="checkbox" id="edit-build-published" style="width:16px;height:16px;cursor:pointer;flex-shrink:0;">
              <span>Hiển thị công khai trên Cổng Portal chào thuê khách hàng</span>
            </label>
            <div class="field-error-slot"></div>
          </div>
        </div>
      </div>

      <!-- TAB PANE 4: HÌNH ẢNH TÒA NHÀ (QUẢN LÝ ALBUM & TẢI ẢNH) -->
      <div id="tab-pane-edit-bld-4" style="display:none;flex-direction:column;flex:1;">
        <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:14px 16px;display:flex;flex-direction:column;flex:1;box-sizing:border-box;">
          <!-- Top bar with quick upload -->
          <div style="display:flex;justify-content:space-between;align-items:center;background:#fff;padding:8px 12px;border:1px solid #e2e8f0;border-radius:6px;">
            <div style="font-size:12.5px;font-weight:600;color:#334155;">
              <i class="fa-solid fa-photo-film" style="color:#0284c7;"></i> Thư viện ảnh (<span id="edit-bld-images-count">0</span> ảnh) - <span style="color:#ef4444;font-size:11.5px;">Bắt buộc tối thiểu 1 ảnh</span>
            </div>
            <div>
              <input type="file" id="edit-build-images-input" accept="image/png,image/jpeg,image/webp,image/jpg" multiple style="display:none;" onchange="handleEditUploadBuildingImages(this)">
              <button type="button" class="btn-mo-orange" onclick="document.getElementById('edit-build-images-input').click()" style="padding:5px 12px;font-size:12px;font-weight:600;">
                <i class="fa-solid fa-cloud-arrow-up"></i> Tải Thêm Ảnh
              </button>
            </div>
          </div>
          <div class="field-error-slot" id="err-edit-build-images" style="justify-content:center;"></div>

          <!-- Existing Images Grid -->
          <div style="flex:1;margin-top:6px;overflow-y:auto;background:#ffffff;border:1px solid #e2e8f0;border-radius:6px;padding:8px;box-sizing:border-box;">
            <div id="edit-build-images-grid" style="display:grid;grid-template-columns:repeat(4, 1fr);gap:10px;">
              <div style="grid-column:1/-1;text-align:center;padding:36px 10px;color:#94a3b8;font-size:12.5px;">
                <i class="fa-solid fa-spinner fa-spin" style="font-size:24px;margin-bottom:8px;display:block;"></i>
                Đang tải thư viện ảnh...
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- THANH NÚT ĐIỀU KHIỂN DƯỚI CÙNG -->
      <div style="display:flex;justify-content:flex-end;gap:8px;border-top:1px solid #e2e8f0;padding-top:14px;margin-top:auto;">
        <button type="button" class="btn-mo-outline" onclick="closeEditBuildingModal()" style="padding:7px 16px;font-size:13px;">Hủy</button>
        <button type="submit" class="btn-mo-primary" style="padding:7px 24px;font-size:13px;font-weight:600;"><i class="fa-solid fa-save"></i> Lưu</button>
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
            <label style="font-size:11px;font-weight:600;color:#475569;">Giá Thuê (VNĐ/m²) <span style="color:#ef4444;">*</span></label>
            <input type="number" step="1000" id="new-unit-price" placeholder="VD: 650000" class="filament-input-sm" required>
          </div>
          <div class="form-field">
            <label style="font-size:11px;font-weight:600;color:#475569;">Phí DV (VNĐ/m²)</label>
            <input type="number" step="1000" id="new-unit-service-fee" placeholder="VD: 120000" class="filament-input-sm">
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

<!-- MODAL 5: QUẢN LÝ ALBUM ẢNH MẶT BẰNG (Unit Images - Supabase / Local Storage) -->
<div id="modal-unit-media" class="mo-modal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(15,23,42,0.6);backdrop-filter:blur(3px);z-index:10001;align-items:center;justify-content:center;">
  <div style="background:#ffffff;border-radius:12px;width:100%;max-width:760px;box-shadow:0 20px 25px -5px rgba(0,0,0,0.15);overflow:hidden;border:1px solid #e2e8f0;max-height:90vh;display:flex;flex-direction:column;">
    <div style="background:var(--mo-primary);color:#ffffff;padding:16px 20px;display:flex;justify-content:space-between;align-items:center;">
      <h3 style="margin:0;font-size:15px;font-weight:700;"><i class="fa-solid fa-camera"></i> Thư Viện Ảnh & Sơ Đồ Mặt Bằng: <span id="media-unit-code"></span></h3>
      <button type="button" onclick="closeUnitMediaModal()" style="background:none;border:none;color:#ffffff;font-size:18px;cursor:pointer;">&times;</button>
    </div>
    <div style="padding:18px;overflow-y:auto;flex:1;">
      <!-- Form Upload ảnh mới cho sàn -->
      <form onsubmit="submitUploadUnitImages(event)" style="background:#f8fafc;padding:12px;border:1px dashed #cbd5e1;border-radius:8px;margin-bottom:16px;display:flex;gap:10px;align-items:center;">
        <input type="file" id="unit-images-input" accept="image/*" multiple required style="font-size:12px;flex:1;">
        <button type="submit" class="btn-mo-orange" style="padding:6px 14px;font-size:12px;white-space:nowrap;">
          <i class="fa-solid fa-cloud-arrow-up"></i> Upload Ảnh Sàn
        </button>
      </form>
      <!-- Grid hiển thị ảnh mặt bằng -->
      <div id="unit-images-grid" style="display:grid;grid-template-columns:repeat(3, 1fr);gap:12px;"></div>
    </div>
  </div>
</div>
