    <!-- Dòng kẻ xanh dương trên cùng -->
    <div class="mo-topbar"></div>

    <!-- Main Header -->
    <header class="mo-header">
      <div class="container">
        <a href="{{ route('home') }}" class="mo-logo" style="cursor: pointer; text-decoration: none;">
          <div class="mo-logo-icon">
            <i class="fa-solid fa-building-wheat"></i>
          </div>
          <div class="mo-logo-text">
            <h1>Office</h1>
          </div>
        </a>

        <nav class="mo-nav">
          <!-- 1. Tất cả tòa nhà toàn quốc -->
          <a href="{{ route('home') }}" 
             class="mo-nav-item {{ (request()->routeIs('home') && !request()->has('province') && !request()->has('province_code') && !request()->has('district') && !request()->has('grade')) ? 'active' : '' }}" 
             onclick="return handleHeaderFilter(event, 'reset', null, '{{ route('home') }}')"
             style="text-decoration:none;">Toàn Quốc</a>

          <!-- 2. Hà Nội Dropdown (Cố định) -->
          @php
            $hnInfo = $headerLocations['hanoi'] ?? ['count' => 10, 'districts' => []];
            $hnCount = $hnInfo['count'] ?? 0;
            $hnDistricts = is_array($hnInfo['districts'] ?? null) ? $hnInfo['districts'] : (is_iterable($hnInfo['districts'] ?? null) ? $hnInfo['districts'] : []);
          @endphp
          <div class="mo-nav-dropdown-wrap">
            <button type="button" 
               class="mo-nav-item {{ (request('province') === '1' || request('province_code') === '1') ? 'active' : '' }}" 
               onclick="toggleNavDropdown(event, this)">
              Hà Nội <i class="fa-solid fa-chevron-down nav-caret"></i>
            </button>
            <div class="mo-nav-dropdown">
              <a href="{{ route('home', ['province' => '1']) }}" 
                 class="mo-dropdown-link" 
                 onclick="return handleHeaderFilter(event, 'province', '1', '{{ route('home', ['province' => '1']) }}')">
                <span><i class="fa-solid fa-city" style="color:#2563eb;margin-right:6px;"></i> <strong>Tất cả Hà Nội</strong></span>
                <span class="badge-num">{{ $hnCount }} tòa</span>
              </a>
              <div class="mo-dropdown-divider"></div>
              @if($hnCount > 0 && !empty($hnDistricts))
                @foreach(array_slice((array) $hnDistricts, 0, 6) as $d)
                  @php
                    $dName = is_array($d) ? ($d['district'] ?? '') : ($d->district ?? '');
                    $dNum = is_array($d) ? ($d['count'] ?? 0) : ($d->count ?? 0);
                  @endphp
                  <a href="{{ route('home', ['district' => $dName, 'province' => '1']) }}" 
                     class="mo-dropdown-link" 
                     onclick="return handleHeaderFilter(event, 'district', '{{ $dName }}', '{{ route('home', ['district' => $dName, 'province' => '1']) }}')">
                    <span>{{ $dName }}</span> <span class="badge-num">{{ $dNum }} tòa</span>
                  </a>
                @endforeach
              @else
                <div class="mo-dropdown-empty">
                  <i class="fa-solid fa-clock-rotate-left"></i>
                  <span>Đang thẩm định nguồn cung mới</span>
                </div>
              @endif
            </div>
          </div>

          <!-- 3. TP. Hồ Chí Minh Dropdown (Cố định) -->
          @php
            $hcmInfo = $headerLocations['hcm'] ?? ['count' => 22, 'districts' => []];
            $hcmCount = $hcmInfo['count'] ?? 0;
            $hcmDistricts = is_array($hcmInfo['districts'] ?? null) ? $hcmInfo['districts'] : (is_iterable($hcmInfo['districts'] ?? null) ? $hcmInfo['districts'] : []);
          @endphp
          <div class="mo-nav-dropdown-wrap">
            <button type="button" 
               class="mo-nav-item {{ (request('province') === '79' || request('province_code') === '79' || (!request()->has('province') && request()->has('district') && in_array(request('district'), ['Quận 1', 'Quận 3', 'Quận 7', 'Bình Thạnh', 'TP Thủ Đức']))) ? 'active' : '' }}" 
               onclick="toggleNavDropdown(event, this)">
              TP. Hồ Chí Minh <i class="fa-solid fa-chevron-down nav-caret"></i>
            </button>
            <div class="mo-nav-dropdown">
              <a href="{{ route('home', ['province' => '79']) }}" 
                 class="mo-dropdown-link" 
                 onclick="return handleHeaderFilter(event, 'province', '79', '{{ route('home', ['province' => '79']) }}')">
                <span><i class="fa-solid fa-city" style="color:#ea580c;margin-right:6px;"></i> <strong>Tất cả TP.HCM</strong></span>
                <span class="badge-num">{{ $hcmCount }} tòa</span>
              </a>
              <div class="mo-dropdown-divider"></div>
              @if($hcmCount > 0 && !empty($hcmDistricts))
                @foreach(array_slice((array) $hcmDistricts, 0, 6) as $d)
                  @php
                    $dName = is_array($d) ? ($d['district'] ?? '') : ($d->district ?? '');
                    $dNum = is_array($d) ? ($d['count'] ?? 0) : ($d->count ?? 0);
                  @endphp
                  <a href="{{ route('home', ['district' => $dName, 'province' => '79']) }}" 
                     class="mo-dropdown-link" 
                     onclick="return handleHeaderFilter(event, 'district', '{{ $dName }}', '{{ route('home', ['district' => $dName, 'province' => '79']) }}')">
                    <span>{{ $dName }}</span> <span class="badge-num">{{ $dNum }} tòa</span>
                  </a>
                @endforeach
              @else
                <div class="mo-dropdown-empty">
                  <i class="fa-solid fa-clock-rotate-left"></i>
                  <span>Đang thẩm định nguồn cung mới</span>
                </div>
              @endif
            </div>
          </div>

          <!-- 4. Đà Nẵng Dropdown (Cố định) -->
          @php
            $dnInfo = $headerLocations['danang'] ?? ['count' => 3, 'districts' => []];
            $dnCount = $dnInfo['count'] ?? 0;
            $dnDistricts = is_array($dnInfo['districts'] ?? null) ? $dnInfo['districts'] : (is_iterable($dnInfo['districts'] ?? null) ? $dnInfo['districts'] : []);
          @endphp
          <div class="mo-nav-dropdown-wrap">
            <button type="button" 
               class="mo-nav-item {{ (request('province') === '48' || request('province_code') === '48') ? 'active' : '' }}" 
               onclick="toggleNavDropdown(event, this)">
              Đà Nẵng <i class="fa-solid fa-chevron-down nav-caret"></i>
            </button>
            <div class="mo-nav-dropdown">
              <a href="{{ route('home', ['province' => '48']) }}" 
                 class="mo-dropdown-link" 
                 onclick="return handleHeaderFilter(event, 'province', '48', '{{ route('home', ['province' => '48']) }}')">
                <span><i class="fa-solid fa-city" style="color:#6366f1;margin-right:6px;"></i> <strong>Tất cả Đà Nẵng</strong></span>
                <span class="badge-num">{{ $dnCount }} tòa</span>
              </a>
              <div class="mo-dropdown-divider"></div>
              @if($dnCount > 0 && !empty($dnDistricts))
                @foreach(array_slice((array) $dnDistricts, 0, 6) as $d)
                  @php
                    $dName = is_array($d) ? ($d['district'] ?? '') : ($d->district ?? '');
                    $dNum = is_array($d) ? ($d['count'] ?? 0) : ($d->count ?? 0);
                  @endphp
                  <a href="{{ route('home', ['district' => $dName, 'province' => '48']) }}" 
                     class="mo-dropdown-link" 
                     onclick="return handleHeaderFilter(event, 'district', '{{ $dName }}', '{{ route('home', ['district' => $dName, 'province' => '48']) }}')">
                    <span>{{ $dName }}</span> <span class="badge-num">{{ $dNum }} tòa</span>
                  </a>
                @endforeach
              @else
                <div class="mo-dropdown-empty">
                  <i class="fa-solid fa-clock-rotate-left"></i>
                  <span>Đang thẩm định nguồn cung mới</span>
                </div>
              @endif
            </div>
          </div>

          <!-- 5. Khu Vực Khác Dropdown (Động từ Database) -->
          @php
            $otherProvinces = $headerLocations['other_provinces'] ?? [];
            $otherList = is_array($otherProvinces) ? $otherProvinces : (is_iterable($otherProvinces) ? $otherProvinces : []);
            $isOtherActive = request()->has('province') && !in_array(request('province'), ['1', '79', '48']);
          @endphp
          <div class="mo-nav-dropdown-wrap">
            <button type="button" 
               class="mo-nav-item {{ $isOtherActive ? 'active' : '' }}" 
               onclick="toggleNavDropdown(event, this)">
              Khu Vực Khác <i class="fa-solid fa-chevron-down nav-caret"></i>
            </button>
            <div class="mo-nav-dropdown">
              @if(!empty($otherList))
                @foreach($otherList as $op)
                  @php
                    $pName = is_array($op) ? ($op['province'] ?? '') : ($op->province ?? '');
                    $pCode = is_array($op) ? ($op['province_code'] ?? $pName) : ($op->province_code ?? $pName);
                    $pCount = is_array($op) ? ($op['count'] ?? 0) : ($op->count ?? 0);
                    $cleanProvName = str_replace('Thành phố ', '', $pName);
                  @endphp
                  <a href="{{ route('home', ['province' => $pCode]) }}" 
                     class="mo-dropdown-link" 
                     onclick="return handleHeaderFilter(event, 'province', '{{ $pCode }}', '{{ route('home', ['province' => $pCode]) }}')">
                    <span><i class="fa-solid fa-location-dot" style="color:#10b981;margin-right:6px;"></i> <strong>{{ $cleanProvName }}</strong></span>
                    <span class="badge-num">{{ $pCount }} tòa</span>
                  </a>
                @endforeach
              @else
                <div class="mo-dropdown-empty">
                  <i class="fa-solid fa-compass"></i>
                  <span>Đang mở rộng mạng lưới tỉnh thành</span>
                </div>
              @endif
              <div class="mo-dropdown-divider"></div>
              <a href="{{ route('quote.page') }}" 
                 class="mo-dropdown-link" 
                 style="color:#ea580c;font-weight:600;">
                <span><i class="fa-solid fa-plus-circle"></i> Đề xuất tỉnh thành khác</span>
              </a>
            </div>
          </div>

          <!-- 6. Tòa Nhà Hạng A -->
          <a href="{{ route('home', ['grade' => 'A']) }}" 
             class="mo-nav-item {{ (request('grade') === 'A') ? 'active' : '' }}" 
             onclick="return handleHeaderFilter(event, 'grade', 'A', '{{ route('home', ['grade' => 'A']) }}')"
             style="text-decoration:none;">Tòa Nhà Hạng A</a>

          <!-- 7. Nhận Báo Giá -->
          <a href="{{ route('quote.page') }}" 
             class="mo-nav-item {{ request()->routeIs('quote.page') ? 'active' : '' }}" 
             style="text-decoration:none;">Nhận Báo Giá</a>
        </nav>

        <div class="mo-header-actions">
          <button class="btn-mo-outline" id="btn-workspace-nav" style="display:none;" onclick="handleGoToWorkspace()">
            <i class="fa-solid fa-desktop"></i> <span id="btn-workspace-text">Không Gian Làm Việc</span>
          </button>

          <!-- Nút Đăng Nhập / Thông tin User Auth -->
          <div id="auth-actions-container" style="display: inline-flex; align-items: center; position: relative;">
            <!-- Chưa đăng nhập: Nút mở Modal Login -->
            <button id="btn-open-login" class="btn-mo-primary" style="background:#0f3361; color:#fff; padding:7px 14px; border-radius:6px; font-weight:700; font-size:13px; white-space: nowrap; border: 1px solid #2563eb; box-shadow: 0 2px 8px rgba(37, 99, 235, 0.25);" onclick="openLoginModal()">
              <i class="fa-solid fa-right-to-bracket"></i> Đăng Nhập
            </button>

            <!-- Đã đăng nhập: User Profile Pill với Dropdown Menu -->
            <div id="user-logged-box" style="display: none; position: relative;">
              <div id="user-profile-trigger" class="user-profile-pill" onclick="toggleUserDropdown(event)">
                <div class="user-avatar-circle" id="user-avatar-text">US</div>
                <div class="user-info-text">
                  <span class="user-name-label" id="user-display-name">Người dùng</span>
                  <span class="user-role-tag" id="user-display-role">ROLE</span>
                </div>
                <i class="fa-solid fa-chevron-down user-dropdown-caret"></i>
              </div>

              <!-- User Dropdown Menu -->
              <div id="user-dropdown-menu" class="user-nav-dropdown">
                <div class="user-dropdown-header">
                  <div style="font-weight:700; color:#0f3361; font-size:13px;" id="dropdown-full-name">Người dùng</div>
                  <div style="font-size:11.5px; color:#64748b;" id="dropdown-email">user@officeleasing.vn</div>
                  <div style="margin-top:5px;"><span class="badge-role-pill" id="dropdown-role-badge">ROLE</span></div>
                </div>
                <div class="user-dropdown-divider"></div>
                <a href="#" class="user-dropdown-item" onclick="openProfileModal('tab-profile-info'); closeUserDropdown(); return false;">
                  <i class="fa-solid fa-id-card-clip" style="color:#2563eb;"></i> Hồ Sơ & Tòa Nhà Phụ Trách
                </a>
                <a href="#" class="user-dropdown-item" onclick="openProfileModal('tab-profile-password'); closeUserDropdown(); return false;">
                  <i class="fa-solid fa-key" style="color:#f59e0b;"></i> Đổi Mật Khẩu Cá Nhân
                </a>
                <a href="#" class="user-dropdown-item" id="dropdown-session-link" style="display:none;" onclick="openProfileModal('tab-profile-session'); closeUserDropdown(); return false;">
                  <i class="fa-solid fa-shield-halved" style="color:#10b981;"></i> Quản Trị Phiên & Token (Admin)
                </a>
                <div class="user-dropdown-divider"></div>
                <a href="#" class="user-dropdown-item" id="dropdown-workspace-link" onclick="handleGoToWorkspace(); closeUserDropdown(); return false;">
                  <i class="fa-solid fa-desktop" style="color:#6366f1;"></i> <span id="dropdown-workspace-text">Không Gian Làm Việc Số</span>
                </a>
                <a href="/admin" target="_blank" class="user-dropdown-item" id="dropdown-filament-link" style="display:none;">
                  <i class="fa-solid fa-gear" style="color:#ea580c;"></i> Quản Trị Filament Gốc
                </a>
                <div class="user-dropdown-divider"></div>
                <a href="#" class="user-dropdown-item text-danger" onclick="handleLogout(); closeUserDropdown(); return false;">
                  <i class="fa-solid fa-arrow-right-from-bracket" style="color:#dc2626;"></i> Đăng Xuất (Thoát)
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </header>
