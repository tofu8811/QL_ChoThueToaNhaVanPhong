    @php
      $hnInfo = $headerLocations['hanoi'] ?? ['count' => 0, 'districts' => [], 'wards' => [], 'col1' => [], 'col2' => [], 'col3' => []];
      $hnCount = $hnInfo['count'] ?? 0;
      $hnDistricts = $hnInfo['districts'] ?? [];
      $hnCol1 = $hnInfo['col1'] ?? [];
      $hnCol2 = $hnInfo['col2'] ?? [];
      $hnCol3 = $hnInfo['col3'] ?? [];
      $isHnActive = (request('province') === '1' || request('province_code') === '1');

      $hcmInfo = $headerLocations['hcm'] ?? ['count' => 0, 'districts' => [], 'wards' => [], 'col1' => [], 'col2' => [], 'col3' => []];
      $hcmCount = $hcmInfo['count'] ?? 0;
      $hcmDistricts = $hcmInfo['districts'] ?? [];
      $hcmCol1 = $hcmInfo['col1'] ?? [];
      $hcmCol2 = $hcmInfo['col2'] ?? [];
      $hcmCol3 = $hcmInfo['col3'] ?? [];
      $isHcmActive = (request('province') === '79' || request('province_code') === '79' || (!request()->has('province') && request()->has('district')));

      $dnInfo = $headerLocations['danang'] ?? ['count' => 0, 'districts' => [], 'wards' => [], 'col1' => [], 'col2' => [], 'col3' => []];
      $dnCount = $dnInfo['count'] ?? 0;
      $dnDistricts = $dnInfo['districts'] ?? [];
      $dnCol1 = $dnInfo['col1'] ?? [];
      $dnCol2 = $dnInfo['col2'] ?? [];
      $dnCol3 = $dnInfo['col3'] ?? [];
      $isDnActive = (request('province') === '48' || request('province_code') === '48');

      $otherProvinces = $headerLocations['other_provinces'] ?? [];
      $otherList = is_array($otherProvinces) ? $otherProvinces : (is_iterable($otherProvinces) ? $otherProvinces : []);
      $isOtherActive = request()->has('province') && !in_array(request('province'), ['1', '79', '48']);
      $isGradeActive = request()->has('grade') || (request()->has('price') && in_array(request('price'), ['under500k', 'under20']));
      $totalNationwide = $headerLocations['total_nationwide'] ?? 22;

      $formatDistSubtext = function (?string $dist) {
          if (!$dist) return '';
          if (preg_match('/^Quận\s+(\d+)$/ui', $dist, $m)) {
              return 'Q.' . $m[1];
          }
          return trim(preg_replace('/^(Quận|Huyện|Thành phố)\s+/ui', '', $dist));
      };
    @endphp

    <!-- Dòng kẻ xanh dương trên cùng -->
    <div class="mo-topbar"></div>

    <!-- Main Header -->
    <header class="mo-header">
      <div class="container">
        <a href="{{ route('home') }}" class="mo-logo" onclick="return handlePublicLogoClick(event);" title="Về trang chủ Cổng thông tin" style="cursor: pointer; text-decoration: none;">
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

          <!-- 2. Hà Nội Dropdown (Đọc động 100% từ Database) -->
          @php
            $hnInfo = $headerLocations['hanoi'] ?? ['count' => 0, 'districts' => [], 'wards' => [], 'col1' => [], 'col2' => [], 'col3' => []];
            $hnCount = $hnInfo['count'] ?? 0;
            $hnDistricts = $hnInfo['districts'] ?? [];
            $hnCol1 = $hnInfo['col1'] ?? [];
            $hnCol2 = $hnInfo['col2'] ?? [];
            $hnCol3 = $hnInfo['col3'] ?? [];
            $isHnActive = (request('province') === '1' || request('province_code') === '1');
          @endphp
          <div class="mo-nav-dropdown-wrap">
            <button type="button" 
               class="mo-nav-item {{ $isHnActive ? 'active' : '' }}" 
               onclick="toggleNavDropdown(event, this)">
              Hà Nội <i class="fa-solid fa-chevron-down nav-caret"></i>
            </button>
            <div class="mo-nav-dropdown mo-mega-dropdown">
              <a href="{{ route('home', ['province' => '1']) }}" 
                 class="mo-mega-header-link" 
                 onclick="return handleHeaderFilter(event, 'province', '1', '{{ route('home', ['province' => '1']) }}')">
                <span><i class="fa-solid fa-city" style="color:#2563eb;margin-right:8px;"></i> <strong>Xem tất cả văn phòng cho thuê Thủ đô Hà Nội</strong></span>
                <span class="badge-num" style="background:#dbeafe;color:#1e40af;font-weight:700;">{{ $hnCount }} cao ốc</span>
              </a>

              <div class="mo-nav-mega-grid">
                <!-- Cột 1: Phường / Tuyến Phố -->
                <div class="mo-mega-col">
                  <div class="mo-mega-col-title"><i class="fa-solid fa-map-pin" style="color:#2563eb;margin-right:6px;font-size:12px;"></i> Phường / Tuyến Phố</div>
                  @forelse($hnCol1 as $w)
                    <a href="{{ route('home', ['district' => $w['display_label'], 'province' => '1']) }}" class="mo-mega-item" onclick="return handleHeaderFilter(event, 'district', '{{ $w['display_label'] }}', '{{ route('home', ['district' => $w['display_label'], 'province' => '1']) }}')">
                      <span class="mo-mega-item-text" title="{{ $w['display_label'] }}">
                        <span>{{ $w['ward'] }}</span>
                        @if(!empty($w['district']))
                          <small class="mo-mega-subtext">({{ $formatDistSubtext($w['district']) }})</small>
                        @endif
                      </span>
                      @if(($w['count'] ?? 0) > 0)
                        <span class="badge-num" style="background:#dbeafe;color:#1e40af;font-size:11px;">{{ $w['count'] }} tòa</span>
                      @endif
                    </a>
                  @empty
                    <div class="mo-dropdown-empty" style="padding:10px;font-size:12px;color:#94a3b8;">Đang cập nhật nguồn cung</div>
                  @endforelse
                </div>

                <!-- Cột 2: Phường / Tuyến Phố (tiếp) -->
                <div class="mo-mega-col">
                  <div class="mo-mega-col-title mo-mega-col-sub">&nbsp;</div>
                  @forelse($hnCol2 as $w)
                    <a href="{{ route('home', ['district' => $w['display_label'], 'province' => '1']) }}" class="mo-mega-item" onclick="return handleHeaderFilter(event, 'district', '{{ $w['display_label'] }}', '{{ route('home', ['district' => $w['display_label'], 'province' => '1']) }}')">
                      <span class="mo-mega-item-text" title="{{ $w['display_label'] }}">
                        <span>{{ $w['ward'] }}</span>
                        @if(!empty($w['district']))
                          <small class="mo-mega-subtext">({{ $formatDistSubtext($w['district']) }})</small>
                        @endif
                      </span>
                      @if(($w['count'] ?? 0) > 0)
                        <span class="badge-num" style="background:#dbeafe;color:#1e40af;font-size:11px;">{{ $w['count'] }} tòa</span>
                      @endif
                    </a>
                  @empty
                    <div class="mo-dropdown-empty" style="padding:10px;font-size:12px;color:#94a3b8;">Đang cập nhật nguồn cung</div>
                  @endforelse
                </div>

                <!-- Cột 3: Phường / Tuyến Phố (tiếp) -->
                <div class="mo-mega-col">
                  <div class="mo-mega-col-title mo-mega-col-sub">&nbsp;</div>
                  @forelse($hnCol3 as $w)
                    <a href="{{ route('home', ['district' => $w['display_label'], 'province' => '1']) }}" class="mo-mega-item" onclick="return handleHeaderFilter(event, 'district', '{{ $w['display_label'] }}', '{{ route('home', ['district' => $w['display_label'], 'province' => '1']) }}')">
                      <span class="mo-mega-item-text" title="{{ $w['display_label'] }}">
                        <span>{{ $w['ward'] }}</span>
                        @if(!empty($w['district']))
                          <small class="mo-mega-subtext">({{ $formatDistSubtext($w['district']) }})</small>
                        @endif
                      </span>
                      @if(($w['count'] ?? 0) > 0)
                        <span class="badge-num" style="background:#dbeafe;color:#1e40af;font-size:11px;">{{ $w['count'] }} tòa</span>
                      @endif
                    </a>
                  @empty
                    <div class="mo-dropdown-empty" style="padding:10px;font-size:12px;color:#94a3b8;">Đang cập nhật nguồn cung</div>
                  @endforelse
                </div>

                <!-- Cột 4: Khu Vực -->
                <div class="mo-mega-col">
                  <div class="mo-mega-col-title"><i class="fa-solid fa-location-dot" style="color:#2563eb;margin-right:6px;font-size:12px;"></i> Quận / Huyện</div>
                  @forelse($hnDistricts as $d)
                    <a href="{{ route('home', ['district' => $d['name'], 'province' => '1']) }}" class="mo-mega-item" onclick="return handleHeaderFilter(event, 'district', '{{ $d['name'] }}', '{{ route('home', ['district' => $d['name'], 'province' => '1']) }}')">
                      <span class="mo-mega-item-text" title="{{ $d['name'] }}">{{ $d['name'] }}</span>
                      <span class="badge-num" style="background:#dbeafe;color:#1e40af;font-size:11px;">{{ $d['count'] }} tòa</span>
                    </a>
                  @empty
                    <div class="mo-dropdown-empty" style="padding:10px;font-size:12px;color:#94a3b8;">Đang cập nhật nguồn cung</div>
                  @endforelse
                </div>
              </div>
            </div>
          </div>

          <!-- 3. TP. Hồ Chí Minh Dropdown (Đọc động 100% từ Database) -->
          @php
            $hcmInfo = $headerLocations['hcm'] ?? ['count' => 0, 'districts' => [], 'wards' => [], 'col1' => [], 'col2' => [], 'col3' => []];
            $hcmCount = $hcmInfo['count'] ?? 0;
            $hcmDistricts = $hcmInfo['districts'] ?? [];
            $hcmCol1 = $hcmInfo['col1'] ?? [];
            $hcmCol2 = $hcmInfo['col2'] ?? [];
            $hcmCol3 = $hcmInfo['col3'] ?? [];
            $isHcmActive = (request('province') === '79' || request('province_code') === '79' || (!request()->has('province') && request()->has('district')));
          @endphp
          <div class="mo-nav-dropdown-wrap">
            <button type="button" 
               class="mo-nav-item {{ $isHcmActive ? 'active' : '' }}" 
               onclick="toggleNavDropdown(event, this)">
              TP. Hồ Chí Minh <i class="fa-solid fa-chevron-down nav-caret"></i>
            </button>
            <div class="mo-nav-dropdown mo-mega-dropdown mo-mega-hcm">
              <a href="{{ route('home', ['province' => '79']) }}" 
                 class="mo-mega-header-link" 
                 onclick="return handleHeaderFilter(event, 'province', '79', '{{ route('home', ['province' => '79']) }}')">
                <span><i class="fa-solid fa-city" style="color:#2563eb;margin-right:8px;"></i> <strong>Xem tất cả văn phòng cho thuê TP. Hồ Chí Minh</strong></span>
                <span class="badge-num" style="background:#dbeafe;color:#1e40af;font-weight:700;">{{ $hcmCount }} cao ốc</span>
              </a>

              <div class="mo-nav-mega-grid">
                <!-- Cột 1: Phường / Tuyến Phố -->
                <div class="mo-mega-col">
                  <div class="mo-mega-col-title"><i class="fa-solid fa-map-pin" style="color:#2563eb;margin-right:6px;font-size:12px;"></i> Phường / Tuyến Phố</div>
                  @forelse($hcmCol1 as $w)
                    <a href="{{ route('home', ['district' => $w['display_label'], 'province' => '79']) }}" class="mo-mega-item" onclick="return handleHeaderFilter(event, 'district', '{{ $w['display_label'] }}', '{{ route('home', ['district' => $w['display_label'], 'province' => '79']) }}')">
                      <span class="mo-mega-item-text" title="{{ $w['display_label'] }}">
                        <span>{{ $w['ward'] }}</span>
                        @if(!empty($w['district']))
                          <small class="mo-mega-subtext">({{ $formatDistSubtext($w['district']) }})</small>
                        @endif
                      </span>
                      @if(($w['count'] ?? 0) > 0)
                        <span class="badge-num" style="background:#dbeafe;color:#1e40af;font-size:11px;">{{ $w['count'] }} tòa</span>
                      @endif
                    </a>
                  @empty
                    <div class="mo-dropdown-empty" style="padding:10px;font-size:12px;color:#94a3b8;">Đang cập nhật nguồn cung</div>
                  @endforelse
                </div>

                <!-- Cột 2: Phường / Tuyến Phố (tiếp) -->
                <div class="mo-mega-col">
                  <div class="mo-mega-col-title mo-mega-col-sub">&nbsp;</div>
                  @forelse($hcmCol2 as $w)
                    <a href="{{ route('home', ['district' => $w['display_label'], 'province' => '79']) }}" class="mo-mega-item" onclick="return handleHeaderFilter(event, 'district', '{{ $w['display_label'] }}', '{{ route('home', ['district' => $w['display_label'], 'province' => '79']) }}')">
                      <span class="mo-mega-item-text" title="{{ $w['display_label'] }}">
                        <span>{{ $w['ward'] }}</span>
                        @if(!empty($w['district']))
                          <small class="mo-mega-subtext">({{ $formatDistSubtext($w['district']) }})</small>
                        @endif
                      </span>
                      @if(($w['count'] ?? 0) > 0)
                        <span class="badge-num" style="background:#dbeafe;color:#1e40af;font-size:11px;">{{ $w['count'] }} tòa</span>
                      @endif
                    </a>
                  @empty
                    <div class="mo-dropdown-empty" style="padding:10px;font-size:12px;color:#94a3b8;">Đang cập nhật nguồn cung</div>
                  @endforelse
                </div>

                <!-- Cột 3: Phường / Tuyến Phố (tiếp) -->
                <div class="mo-mega-col">
                  <div class="mo-mega-col-title mo-mega-col-sub">&nbsp;</div>
                  @forelse($hcmCol3 as $w)
                    <a href="{{ route('home', ['district' => $w['display_label'], 'province' => '79']) }}" class="mo-mega-item" onclick="return handleHeaderFilter(event, 'district', '{{ $w['display_label'] }}', '{{ route('home', ['district' => $w['display_label'], 'province' => '79']) }}')">
                      <span class="mo-mega-item-text" title="{{ $w['display_label'] }}">
                        <span>{{ $w['ward'] }}</span>
                        @if(!empty($w['district']))
                          <small class="mo-mega-subtext">({{ $formatDistSubtext($w['district']) }})</small>
                        @endif
                      </span>
                      @if(($w['count'] ?? 0) > 0)
                        <span class="badge-num" style="background:#dbeafe;color:#1e40af;font-size:11px;">{{ $w['count'] }} tòa</span>
                      @endif
                    </a>
                  @empty
                    <div class="mo-dropdown-empty" style="padding:10px;font-size:12px;color:#94a3b8;">Đang cập nhật nguồn cung</div>
                  @endforelse
                </div>

                <!-- Cột 4: Khu Vực -->
                <div class="mo-mega-col">
                  <div class="mo-mega-col-title"><i class="fa-solid fa-location-dot" style="color:#2563eb;margin-right:6px;font-size:12px;"></i> Quận / Huyện</div>
                  @forelse($hcmDistricts as $d)
                    <a href="{{ route('home', ['district' => $d['name'], 'province' => '79']) }}" class="mo-mega-item" onclick="return handleHeaderFilter(event, 'district', '{{ $d['name'] }}', '{{ route('home', ['district' => $d['name'], 'province' => '79']) }}')">
                      <span class="mo-mega-item-text" title="{{ $d['name'] }}">{{ $d['name'] }}</span>
                      <span class="badge-num" style="background:#dbeafe;color:#1e40af;font-size:11px;">{{ $d['count'] }} tòa</span>
                    </a>
                  @empty
                    <div class="mo-dropdown-empty" style="padding:10px;font-size:12px;color:#94a3b8;">Đang cập nhật nguồn cung</div>
                  @endforelse
                </div>
              </div>
            </div>
          </div>

          <!-- 4. Đà Nẵng Dropdown (Đọc động 100% từ Database) -->
          @php
            $dnInfo = $headerLocations['danang'] ?? ['count' => 0, 'districts' => [], 'wards' => [], 'col1' => [], 'col2' => [], 'col3' => []];
            $dnCount = $dnInfo['count'] ?? 0;
            $dnDistricts = $dnInfo['districts'] ?? [];
            $dnCol1 = $dnInfo['col1'] ?? [];
            $dnCol2 = $dnInfo['col2'] ?? [];
            $dnCol3 = $dnInfo['col3'] ?? [];
            $isDnActive = (request('province') === '48' || request('province_code') === '48');
          @endphp
          <div class="mo-nav-dropdown-wrap">
            <button type="button" 
               class="mo-nav-item {{ $isDnActive ? 'active' : '' }}" 
               onclick="toggleNavDropdown(event, this)">
              Đà Nẵng <i class="fa-solid fa-chevron-down nav-caret"></i>
            </button>
            <div class="mo-nav-dropdown mo-mega-dropdown mo-mega-danang">
              <a href="{{ route('home', ['province' => '48']) }}" 
                 class="mo-mega-header-link" 
                 onclick="return handleHeaderFilter(event, 'province', '48', '{{ route('home', ['province' => '48']) }}')">
                <span><i class="fa-solid fa-city" style="color:#2563eb;margin-right:8px;"></i> <strong>Xem tất cả văn phòng cho thuê Đà Nẵng</strong></span>
                <span class="badge-num" style="background:#dbeafe;color:#1e40af;font-weight:700;">{{ $dnCount }} cao ốc</span>
              </a>

              <div class="mo-nav-mega-grid mo-grid-danang">
                <!-- Cột 1: Phường / Tuyến Phố -->
                <div class="mo-mega-col">
                  <div class="mo-mega-col-title"><i class="fa-solid fa-map-pin" style="color:#2563eb;margin-right:6px;font-size:12px;"></i> Phường / Tuyến Phố</div>
                  @forelse(($dnInfo['wards'] ?? []) as $w)
                    <a href="{{ route('home', ['district' => $w['display_label'], 'province' => '48']) }}" class="mo-mega-item" onclick="return handleHeaderFilter(event, 'district', '{{ $w['display_label'] }}', '{{ route('home', ['district' => $w['display_label'], 'province' => '48']) }}')">
                      <span class="mo-mega-item-text" title="{{ $w['display_label'] }}">
                        <span>{{ $w['ward'] }}</span>
                        @if(!empty($w['district']))
                          <small class="mo-mega-subtext">({{ $formatDistSubtext($w['district']) }})</small>
                        @endif
                      </span>
                      @if(($w['count'] ?? 0) > 0)
                        <span class="badge-num" style="background:#dbeafe;color:#1e40af;font-size:11px;">{{ $w['count'] }} tòa</span>
                      @endif
                    </a>
                  @empty
                    <div class="mo-dropdown-empty" style="padding:10px;font-size:12px;color:#94a3b8;">Đang cập nhật nguồn cung</div>
                  @endforelse
                </div>

                <!-- Cột 2: Quận / Huyện -->
                <div class="mo-mega-col">
                  <div class="mo-mega-col-title"><i class="fa-solid fa-location-dot" style="color:#2563eb;margin-right:6px;font-size:12px;"></i> Quận / Huyện</div>
                  @forelse($dnDistricts as $d)
                    <a href="{{ route('home', ['district' => $d['name'], 'province' => '48']) }}" class="mo-mega-item" onclick="return handleHeaderFilter(event, 'district', '{{ $d['name'] }}', '{{ route('home', ['district' => $d['name'], 'province' => '48']) }}')">
                      <span class="mo-mega-item-text" title="{{ $d['name'] }}">{{ $d['name'] }}</span>
                      <span class="badge-num" style="background:#dbeafe;color:#1e40af;font-size:11px;">{{ $d['count'] }} tòa</span>
                    </a>
                  @empty
                    <div class="mo-dropdown-empty" style="padding:10px;font-size:12px;color:#94a3b8;">Đang cập nhật nguồn cung</div>
                  @endforelse
                </div>
              </div>
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

          <!-- 6. Văn Phòng Theo Hạng Dropdown -->
          @php
            $isGradeActive = request()->has('grade') || (request()->has('price') && in_array(request('price'), ['under500k', 'under20']));
          @endphp
          <div class="mo-nav-dropdown-wrap">
            <button type="button" 
               class="mo-nav-item {{ $isGradeActive ? 'active' : '' }}" 
               onclick="toggleNavDropdown(event, this)">
              Văn Phòng Theo Hạng <i class="fa-solid fa-chevron-down nav-caret"></i>
            </button>
            <div class="mo-nav-dropdown mo-dropdown-grades">
              <a href="{{ route('home') }}" 
                 class="mo-dropdown-link" 
                 onclick="return handleHeaderFilter(event, 'reset', null, '{{ route('home') }}')">
                <span><i class="fa-solid fa-layer-group" style="color:#64748b;margin-right:8px;"></i> <strong>Tất cả theo hạng</strong></span>
              </a>
              <div class="mo-dropdown-divider"></div>
              <a href="{{ route('home', ['grade' => 'A']) }}" 
                 class="mo-dropdown-link {{ request('grade') === 'A' ? 'active-link' : '' }}" 
                 onclick="return handleHeaderFilter(event, 'grade', 'A', '{{ route('home', ['grade' => 'A']) }}')">
                <span><i class="fa-solid fa-award" style="color:#f59e0b;margin-right:8px;"></i> Văn phòng Hạng A</span>
                <span class="badge-num" style="background:#fef3c7;color:#d97706;font-size:11px;">Quốc tế</span>
              </a>
              <a href="{{ route('home', ['grade' => 'B']) }}" 
                 class="mo-dropdown-link {{ request('grade') === 'B' ? 'active-link' : '' }}" 
                 onclick="return handleHeaderFilter(event, 'grade', 'B', '{{ route('home', ['grade' => 'B']) }}')">
                <span><i class="fa-solid fa-building" style="color:#3b82f6;margin-right:8px;"></i> Văn phòng Hạng B</span>
                <span class="badge-num" style="background:#dbeafe;color:#1d4ed8;font-size:11px;">Chuyên nghiệp</span>
              </a>
              <a href="{{ route('home', ['grade' => 'C']) }}" 
                 class="mo-dropdown-link {{ request('grade') === 'C' ? 'active-link' : '' }}" 
                 onclick="return handleHeaderFilter(event, 'grade', 'C', '{{ route('home', ['grade' => 'C']) }}')">
                <span><i class="fa-regular fa-building" style="color:#10b981;margin-right:8px;"></i> Văn phòng Hạng C</span>
                <span class="badge-num" style="background:#d1fae5;color:#047857;font-size:11px;">Tiêu chuẩn</span>
              </a>
              <a href="{{ route('home', ['price' => 'under500k']) }}" 
                 class="mo-dropdown-link {{ in_array(request('price'), ['under500k', 'under20']) ? 'active-link' : '' }}" 
                 onclick="return handleHeaderFilter(event, 'price', 'under500k', '{{ route('home', ['price' => 'under500k']) }}')">
                <span><i class="fa-solid fa-tag" style="color:#8b5cf6;margin-right:8px;"></i> Văn phòng Giá Rẻ</span>
                <span class="badge-num" style="background:#ede9fe;color:#6d28d9;font-size:11px;">&lt; 500k/m²</span>
              </a>
            </div>
          </div>

          <!-- 7. Nhận Báo Giá -->
          <a href="{{ route('quote.page') }}" 
             class="mo-nav-item {{ request()->routeIs('quote.page') ? 'active' : '' }}" 
             style="text-decoration:none;">Nhận Báo Giá</a>

        </nav>

        <div class="mo-header-actions">
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
                <a href="/filament" target="_blank" class="user-dropdown-item" id="dropdown-filament-link" style="display:none;">
                  <i class="fa-solid fa-gear" style="color:#ea580c;"></i> Quản Trị Filament Gốc
                </a>
                <div class="user-dropdown-divider"></div>
                <a href="#" class="user-dropdown-item text-danger" onclick="handleLogout(); closeUserDropdown(); return false;">
                  <i class="fa-solid fa-arrow-right-from-bracket" style="color:#dc2626;"></i> Đăng Xuất (Thoát)
                </a>
              </div>
            </div>
          </div>

          <!-- Nút Hamburger Menu trên Mobile / Tablet -->
          <button type="button" class="mo-hamburger-btn" id="btn-mo-hamburger" onclick="toggleMobileMenu()" aria-label="Mở Menu Điều Hướng">
            <i class="fa-solid fa-bars"></i>
          </button>
        </div>
      </div>
    </header>

    <!-- Off-canvas Mobile Menu Drawer Backdrop -->
    <div id="mo-mobile-backdrop" class="mo-mobile-backdrop" onclick="closeMobileMenu()"></div>

    <!-- Off-canvas Mobile Menu Drawer -->
    <aside id="mo-mobile-drawer" class="mo-mobile-drawer" aria-label="Menu điều hướng di động">
      <div class="mo-drawer-header">
        <a href="{{ route('home') }}" class="mo-logo" onclick="closeMobileMenu(); return handlePublicLogoClick(event);" title="Về trang chủ Cổng thông tin" style="cursor: pointer; text-decoration: none;">
          <div class="mo-logo-icon">
            <i class="fa-solid fa-building-wheat"></i>
          </div>
          <div class="mo-logo-text">
            <h1 style="font-size: 18px;">Office</h1>
          </div>
        </a>
        <button type="button" class="mo-drawer-close-btn" onclick="closeMobileMenu()" aria-label="Đóng menu">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>

      <div class="mo-drawer-body">
        <!-- Quick Action Quote Button -->
        <div class="mo-drawer-quick-actions">
          <a href="{{ route('quote.page') }}" class="btn-mo-orange" style="width: 100%; justify-content: center; font-size: 13.5px; padding: 10px 14px; text-decoration: none;" onclick="closeMobileMenu()">
            <i class="fa-solid fa-calculator"></i> Nhận Báo Giá Nhanh 2026
          </a>
        </div>

        <!-- Navigation Accordion List -->
        <div class="mo-drawer-nav">
          <!-- 1. Toàn quốc -->
          <a href="{{ route('home') }}" 
             class="mo-drawer-link {{ (request()->routeIs('home') && !request()->has('province') && !request()->has('province_code') && !request()->has('district') && !request()->has('grade')) ? 'active' : '' }}"
             onclick="closeMobileMenu(); return handleHeaderFilter(event, 'reset', null, '{{ route('home') }}')">
            <i class="fa-solid fa-globe" style="color: #2563eb; width: 18px;"></i>
            <span>Toàn Quốc</span>
            <span class="badge-num" style="background:#dbeafe;color:#1e40af;margin-left:auto;font-size:11px;font-weight:700;">{{ $totalNationwide }} cao ốc</span>
          </a>

          <!-- 2. Hà Nội Accordion -->
          <div class="mo-drawer-accordion-item">
            <button type="button" class="mo-drawer-accordion-btn" onclick="toggleDrawerAccordion(this)">
              <span><i class="fa-solid fa-city" style="color: #2563eb; width: 18px;"></i> Hà Nội</span>
              <span class="badge-num" style="background:#dbeafe;color:#1e40af;font-size:11px;font-weight:700;">{{ $hnCount }} tòa</span>
              <i class="fa-solid fa-chevron-down accordion-caret"></i>
            </button>
            <div class="mo-drawer-accordion-content">
              <a href="{{ route('home', ['province' => '1']) }}" class="mo-drawer-sublink highlight" onclick="closeMobileMenu(); return handleHeaderFilter(event, 'province', '1', '{{ route('home', ['province' => '1']) }}')">
                <span><i class="fa-solid fa-arrow-right"></i> Tất cả văn phòng Hà Nội</span>
                <span class="badge-num" style="background:#dbeafe;color:#1e40af;font-size:10.5px;">{{ $hnCount }}</span>
              </a>
              @if(!empty($hnDistricts))
                <div class="mo-drawer-subgroup-title">Khu vực / Quận trọng điểm:</div>
                <div class="mo-drawer-tags-grid">
                  @foreach($hnDistricts as $d)
                    <a href="{{ route('home', ['district' => $d['name'], 'province' => '1']) }}" class="mo-drawer-tag" onclick="closeMobileMenu(); return handleHeaderFilter(event, 'district', '{{ $d['name'] }}', '{{ route('home', ['district' => $d['name'], 'province' => '1']) }}')">
                      {{ $d['name'] }} <small>({{ $d['count'] }})</small>
                    </a>
                  @endforeach
                </div>
              @endif
            </div>
          </div>

          <!-- 3. TP. Hồ Chí Minh Accordion -->
          <div class="mo-drawer-accordion-item">
            <button type="button" class="mo-drawer-accordion-btn" onclick="toggleDrawerAccordion(this)">
              <span><i class="fa-solid fa-city" style="color: #2563eb; width: 18px;"></i> TP. Hồ Chí Minh</span>
              <span class="badge-num" style="background:#dbeafe;color:#1e40af;font-size:11px;font-weight:700;">{{ $hcmCount }} tòa</span>
              <i class="fa-solid fa-chevron-down accordion-caret"></i>
            </button>
            <div class="mo-drawer-accordion-content">
              <a href="{{ route('home', ['province' => '79']) }}" class="mo-drawer-sublink highlight" onclick="closeMobileMenu(); return handleHeaderFilter(event, 'province', '79', '{{ route('home', ['province' => '79']) }}')">
                <span><i class="fa-solid fa-arrow-right"></i> Tất cả văn phòng TP.HCM</span>
                <span class="badge-num" style="background:#dbeafe;color:#1e40af;font-size:10.5px;">{{ $hcmCount }}</span>
              </a>
              @if(!empty($hcmDistricts))
                <div class="mo-drawer-subgroup-title">Khu vực / Quận trọng điểm:</div>
                <div class="mo-drawer-tags-grid">
                  @foreach($hcmDistricts as $d)
                    <a href="{{ route('home', ['district' => $d['name'], 'province' => '79']) }}" class="mo-drawer-tag" onclick="closeMobileMenu(); return handleHeaderFilter(event, 'district', '{{ $d['name'] }}', '{{ route('home', ['district' => $d['name'], 'province' => '79']) }}')">
                      {{ $d['name'] }} <small>({{ $d['count'] }})</small>
                    </a>
                  @endforeach
                </div>
              @endif
            </div>
          </div>

          <!-- 4. Đà Nẵng Accordion -->
          <div class="mo-drawer-accordion-item">
            <button type="button" class="mo-drawer-accordion-btn" onclick="toggleDrawerAccordion(this)">
              <span><i class="fa-solid fa-water" style="color: #2563eb; width: 18px;"></i> Đà Nẵng</span>
              <span class="badge-num" style="background:#dbeafe;color:#1e40af;font-size:11px;font-weight:700;">{{ $dnCount }} tòa</span>
              <i class="fa-solid fa-chevron-down accordion-caret"></i>
            </button>
            <div class="mo-drawer-accordion-content">
              <a href="{{ route('home', ['province' => '48']) }}" class="mo-drawer-sublink highlight" onclick="closeMobileMenu(); return handleHeaderFilter(event, 'province', '48', '{{ route('home', ['province' => '48']) }}')">
                <span><i class="fa-solid fa-arrow-right"></i> Tất cả văn phòng Đà Nẵng</span>
                <span class="badge-num" style="background:#dbeafe;color:#1e40af;font-size:10.5px;">{{ $dnCount }}</span>
              </a>
              @if(!empty($dnDistricts))
                <div class="mo-drawer-tags-grid">
                  @foreach($dnDistricts as $d)
                    <a href="{{ route('home', ['district' => $d['name'], 'province' => '48']) }}" class="mo-drawer-tag" onclick="closeMobileMenu(); return handleHeaderFilter(event, 'district', '{{ $d['name'] }}', '{{ route('home', ['district' => $d['name'], 'province' => '48']) }}')">
                      {{ $d['name'] }} <small>({{ $d['count'] }})</small>
                    </a>
                  @endforeach
                </div>
              @endif
            </div>
          </div>

          <!-- 5. Tỉnh thành khác Accordion -->
          <div class="mo-drawer-accordion-item">
            <button type="button" class="mo-drawer-accordion-btn" onclick="toggleDrawerAccordion(this)">
              <span><i class="fa-solid fa-map-location-dot" style="color: #10b981; width: 18px;"></i> Khu Vực Khác</span>
              <i class="fa-solid fa-chevron-down accordion-caret"></i>
            </button>
            <div class="mo-drawer-accordion-content">
              @forelse($otherList as $op)
                @php
                  $pName = is_array($op) ? ($op['province'] ?? '') : ($op->province ?? '');
                  $pCode = is_array($op) ? ($op['province_code'] ?? $pName) : ($op->province_code ?? $pName);
                  $pCount = is_array($op) ? ($op['count'] ?? 0) : ($op->count ?? 0);
                  $cleanProvName = str_replace('Thành phố ', '', $pName);
                @endphp
                <a href="{{ route('home', ['province' => $pCode]) }}" class="mo-drawer-sublink" onclick="closeMobileMenu(); return handleHeaderFilter(event, 'province', '{{ $pCode }}', '{{ route('home', ['province' => $pCode]) }}')">
                  <span>{{ $cleanProvName }}</span>
                  <span class="badge-num" style="background:#f1f5f9;color:#475569;font-size:10.5px;">{{ $pCount }} tòa</span>
                </a>
              @empty
                <div style="padding: 8px 12px; font-size: 12px; color: #94a3b8;">Đang mở rộng mạng lưới tỉnh thành</div>
              @endforelse
            </div>
          </div>

          <!-- 6. Phân Loại Hạng & Giá Accordion -->
          <div class="mo-drawer-accordion-item">
            <button type="button" class="mo-drawer-accordion-btn" onclick="toggleDrawerAccordion(this)">
              <span><i class="fa-solid fa-layer-group" style="color: #8b5cf6; width: 18px;"></i> Hạng & Giá Thuê</span>
              <i class="fa-solid fa-chevron-down accordion-caret"></i>
            </button>
            <div class="mo-drawer-accordion-content">
              <a href="{{ route('home', ['grade' => 'A']) }}" class="mo-drawer-sublink" onclick="closeMobileMenu(); return handleHeaderFilter(event, 'grade', 'A', '{{ route('home', ['grade' => 'A']) }}')">
                <span><i class="fa-solid fa-award" style="color:#eab308;margin-right:6px;"></i> Văn phòng Hạng A</span>
              </a>
              <a href="{{ route('home', ['grade' => 'B']) }}" class="mo-drawer-sublink" onclick="closeMobileMenu(); return handleHeaderFilter(event, 'grade', 'B', '{{ route('home', ['grade' => 'B']) }}')">
                <span><i class="fa-solid fa-building" style="color:#3b82f6;margin-right:6px;"></i> Văn phòng Hạng B</span>
              </a>
              <a href="{{ route('home', ['grade' => 'C']) }}" class="mo-drawer-sublink" onclick="closeMobileMenu(); return handleHeaderFilter(event, 'grade', 'C', '{{ route('home', ['grade' => 'C']) }}')">
                <span><i class="fa-regular fa-building" style="color:#10b981;margin-right:6px;"></i> Văn phòng Hạng C</span>
              </a>
              <a href="{{ route('home', ['price' => 'under500k']) }}" class="mo-drawer-sublink" onclick="closeMobileMenu(); return handleHeaderFilter(event, 'price', 'under500k', '{{ route('home', ['price' => 'under500k']) }}')">
                <span><i class="fa-solid fa-tag" style="color:#8b5cf6;margin-right:6px;"></i> Văn phòng Tiết Kiệm (&lt; 500k/m²)</span>
              </a>
            </div>
          </div>

          <!-- 7. Nhận Báo Giá Link -->
          <a href="{{ route('quote.page') }}"
             class="mo-drawer-link {{ request()->routeIs('quote.page') ? 'active' : '' }}"
             onclick="closeMobileMenu();">
            <i class="fa-solid fa-file-invoice-dollar" style="color: #f97316; width: 18px;"></i>
            <span>Tư Vấn Miễn Phí & Khảo Sát</span>
          </a>
        </div>
      </div>

      <div class="mo-drawer-footer">
        <div style="font-size: 11.5px; color: #64748b; margin-bottom: 4px;">Hotline tư vấn hỗ trợ khảo sát:</div>
        <a href="tel:19006868" style="font-weight: 700; color: #0f3361; font-size: 14.5px; display: inline-flex; align-items: center; gap: 6px; text-decoration: none;">
          <i class="fa-solid fa-phone-volume" style="color: #ea580c;"></i> 1900 6868 - 0903 000 888
        </a>
      </div>
    </aside>

    <script>
      /* Hover & Click Manager cho thanh điều hướng Header (Tránh mất hover khi rê chuột chéo) */
      (function initNavHoverManager() {
        let closeTimeout = null;
        let activeWrap = null;
        const CLOSE_DELAY = 260; // 260ms grace period giúp rê chuột xuống danh sách mượt mà không bị mất

        function openDropdown(wrap) {
          if (closeTimeout) {
            clearTimeout(closeTimeout);
            closeTimeout = null;
          }
          if (activeWrap && activeWrap !== wrap) {
            activeWrap.classList.remove('open');
          }
          activeWrap = wrap;
          wrap.classList.add('open');
        }

        function scheduleClose(wrap) {
          if (closeTimeout) clearTimeout(closeTimeout);
          closeTimeout = setTimeout(() => {
            if (activeWrap === wrap) {
              wrap.classList.remove('open');
              activeWrap = null;
            }
          }, CLOSE_DELAY);
        }

        document.querySelectorAll('.mo-nav-dropdown-wrap').forEach(wrap => {
          wrap.addEventListener('mouseenter', () => openDropdown(wrap));
          wrap.addEventListener('mouseleave', () => scheduleClose(wrap));
        });

        window.toggleNavDropdown = function(event, btn) {
          if (event) {
            event.preventDefault();
            event.stopPropagation();
          }
          const wrap = btn.closest('.mo-nav-dropdown-wrap');
          if (!wrap) return;
          if (closeTimeout) {
            clearTimeout(closeTimeout);
            closeTimeout = null;
          }
          const isOpen = wrap.classList.contains('open');
          document.querySelectorAll('.mo-nav-dropdown-wrap.open').forEach(el => el.classList.remove('open'));
          if (!isOpen) {
            wrap.classList.add('open');
            activeWrap = wrap;
          } else {
            activeWrap = null;
          }
        };

        document.addEventListener('click', function(e) {
          if (!e.target.closest('.mo-nav-dropdown-wrap')) {
            if (closeTimeout) clearTimeout(closeTimeout);
            document.querySelectorAll('.mo-nav-dropdown-wrap.open').forEach(el => el.classList.remove('open'));
            activeWrap = null;
          }
        });
      })();

      window.openMobileMenu = function() {
        const drawer = document.getElementById('mo-mobile-drawer');
        const overlay = document.getElementById('mo-drawer-overlay');
        if (drawer) drawer.classList.add('open');
        if (overlay) overlay.classList.add('open');
        document.body.style.overflow = 'hidden';
      };

      window.closeMobileMenu = function() {
        const drawer = document.getElementById('mo-mobile-drawer');
        const overlay = document.getElementById('mo-drawer-overlay');
        if (drawer) drawer.classList.remove('open');
        if (overlay) overlay.classList.remove('open');
        document.body.style.overflow = '';
      };

      window.toggleDrawerAccordion = function(btn) {
        const item = btn.closest('.mo-drawer-accordion-item');
        if (!item) return;
        item.classList.toggle('open');
      };
    </script>
