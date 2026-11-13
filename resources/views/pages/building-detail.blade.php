@extends('layouts.portal')

@section('title', ($building->name ?? 'Chi tiết tòa nhà') . ' - Cho Thuê Văn Phòng ' . ($building->district ?? 'TP.HCM'))

@section('meta')
  <meta name="description" content="Thông tin diện tích trống, giá thuê, kết cấu và phí dịch vụ tại tòa nhà {{ $building->name ?? '' }}, {{ $building->address ?? '' }}.">
  <meta property="og:title" content="{{ $building->name ?? '' }} - Cho Thuê Văn Phòng Chuyên Nghiệp">
  <meta property="og:description" content="Giá thuê từ ${{ round(($building->min_rent_price_per_sqm ?? 600000) / 24000) }}/m². Cập nhật mặt bằng trống mới nhất 2026.">
  <meta property="og:type" content="article">
  <meta property="og:url" content="{{ url()->current() }}">
  @php
    $heroImg = $building->primaryImage?->image_url ?? $building->images?->first()?->image_url ?? 'https://images.unsplash.com/photo-1577495508048-b635879837f1?auto=format&fit=crop&w=1200&q=80';
    $minUsd = $building->min_rent_price_per_sqm ? round($building->min_rent_price_per_sqm / 24000) : 35;
    $maxUsd = $building->max_rent_price_per_sqm ? round($building->max_rent_price_per_sqm / 24000) : 45;
  @endphp
  <meta property="og:image" content="{{ $heroImg }}">
  <!-- Schema.org Product / Place -->
  <script type="application/ld+json">
  {!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'Place',
    'name' => $building->name ?? 'Cao ốc văn phòng',
    'address' => [
      '@type' => 'PostalAddress',
      'streetAddress' => $building->address ?? '',
      'addressLocality' => $building->district ?? 'TP.HCM',
      'addressRegion' => 'Hồ Chí Minh',
      'addressCountry' => 'VN',
    ],
  ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
  </script>
@endsection

@section('content')
  <div id="portal-wrapper">
    <!-- Header -->
    @include('portal.components.header')

    <main id="page-detail" class="page-section active" style="display:block;">
      <div class="container" style="padding-top: 24px; padding-bottom: 50px;">
        <div class="mo-breadcrumbs">
          <a href="{{ route('home') }}">Trang chủ</a>
          <i class="fa-solid fa-chevron-right" style="font-size:10px;"></i>
          <a href="{{ route('home') }}">Văn phòng cho thuê TP.HCM</a>
          <i class="fa-solid fa-chevron-right" style="font-size:10px;"></i>
          <span>{{ $building->name ?? 'Tòa nhà văn phòng' }}</span>
        </div>

        <!-- Detail Hero Grid -->
        <div class="detail-hero-grid">
          <!-- Gallery ảnh bằng Alpine.js (0ms delay) -->
          <div class="detail-gallery" x-data="{ activeImg: '{{ $heroImg }}' }">
            <div class="detail-main-img">
              <img :src="activeImg" alt="{{ $building->name ?? 'Tòa nhà' }}" id="detail-hero-img">
            </div>
            <div class="detail-sub-thumbs">
              <img src="{{ $heroImg }}" @click="activeImg = '{{ $heroImg }}'" :class="{ 'active': activeImg === '{{ $heroImg }}' }">
              @if(isset($building->images) && $building->images->count() > 0)
                @foreach($building->images->take(3) as $img)
                  <img src="{{ $img->image_url }}" @click="activeImg = '{{ $img->image_url }}'" :class="{ 'active': activeImg === '{{ $img->image_url }}' }">
                @endforeach
              @else
                <img src="https://images.unsplash.com/photo-1497366216548-37526070297c?auto=format&fit=crop&w=600&q=80" @click="activeImg = 'https://images.unsplash.com/photo-1497366216548-37526070297c?auto=format&fit=crop&w=600&q=80'" :class="{ 'active': activeImg === 'https://images.unsplash.com/photo-1497366216548-37526070297c?auto=format&fit=crop&w=600&q=80' }">
                <img src="https://images.unsplash.com/photo-1497366811353-6870744d04b2?auto=format&fit=crop&w=600&q=80" @click="activeImg = 'https://images.unsplash.com/photo-1497366811353-6870744d04b2?auto=format&fit=crop&w=600&q=80'" :class="{ 'active': activeImg === 'https://images.unsplash.com/photo-1497366811353-6870744d04b2?auto=format&fit=crop&w=600&q=80' }">
                <img src="https://images.unsplash.com/photo-1504384308090-c894fdcc538d?auto=format&fit=crop&w=600&q=80" @click="activeImg = 'https://images.unsplash.com/photo-1504384308090-c894fdcc538d?auto=format&fit=crop&w=600&q=80'" :class="{ 'active': activeImg === 'https://images.unsplash.com/photo-1504384308090-c894fdcc538d?auto=format&fit=crop&w=600&q=80' }">
              @endif
            </div>
          </div>

          <!-- Quick Specs & Action -->
          <div class="detail-info-card">
            <span class="badge-filament info"><i class="fa-solid fa-award"></i> Tòa nhà Hạng {{ $building->grade ?? 'A' }}</span>
            <span class="badge-filament success" style="margin-left: 6px;"><i class="fa-solid fa-circle-check"></i> Đang trống</span>
            <h1 style="font-size: 26px; font-weight: 800; color: var(--mo-primary); margin-top: 10px;">{{ $building->name ?? 'Tòa nhà' }}</h1>
            <p style="font-size: 14px; color: #64748b; margin-top: 4px;">
              <i class="fa-solid fa-location-dot" style="color:#ea580c;"></i> {{ $building->address ?? 'TP. Hồ Chí Minh' }}
            </p>

            <div class="detail-price-highlight">
              <div style="font-size: 13px; color: #64748b; font-weight: 500;">Giá chào thuê chính thức:</div>
              <div style="font-size: 26px; font-weight: 800; color: #ea580c;">${{ $minUsd }} - ${{ $maxUsd }} / m² / tháng</div>
              <div style="font-size: 12px; color: #94a3b8;">
                (Khoảng {{ number_format($building->min_rent_price_per_sqm ?: ($minUsd * 24000), 0, ',', '.') }}đ - {{ number_format($building->max_rent_price_per_sqm ?: ($maxUsd * 24000), 0, ',', '.') }}đ/m² - Chưa bao gồm VAT & PQL)
              </div>
            </div>

            <!-- Specs Table -->
            <table class="detail-specs-table">
              <tr>
                <td><i class="fa-solid fa-layer-group"></i> Kết cấu tòa nhà</td>
                <td>{{ ($building->total_floors ?: 25) }} Tầng nổi - {{ ($building->total_basements ?: 3) }} Tầng hầm</td>
              </tr>
              <tr>
                <td><i class="fa-solid fa-expand"></i> Diện tích sàn điển hình</td>
                <td>{{ number_format($building->total_leasable_area_sqm ? $building->total_leasable_area_sqm / ($building->total_floors ?: 25) : 1000) }} m² / sàn</td>
              </tr>
              <tr>
                <td><i class="fa-solid fa-ruler-vertical"></i> Chiều cao trần</td>
                <td>2.75 mét (tiêu chuẩn quốc tế)</td>
              </tr>
              <tr>
                <td><i class="fa-solid fa-elevator"></i> Hệ thống thang máy</td>
                <td>Tốc độ cao có phân tầng thông minh</td>
              </tr>
              <tr>
                <td><i class="fa-solid fa-snowflake"></i> Hệ thống điều hòa</td>
                <td>Máy lạnh trung tâm Chiller Carrier thông minh</td>
              </tr>
              <tr>
                <td><i class="fa-solid fa-shield-virus"></i> Máy phát điện dự phòng</td>
                <td>100% Công suất tự động chuyển mạch</td>
              </tr>
            </table>

            <div style="display: flex; gap: 10px; margin-top: 20px;">
              <button class="btn-mo-orange" style="flex: 1; justify-content: center;" type="button" onclick="openQuoteForBuilding('{{ addslashes($building->name ?? '') }}')">
                <i class="fa-regular fa-calendar-check"></i> Đặt Lịch Khảo Sát
              </button>
              <button class="btn-mo-outline" type="button" onclick="openQuoteModal()">
                <i class="fa-solid fa-file-pdf"></i> Nhận Báo Giá Chi Tiết
              </button>
            </div>
          </div>
        </div>

        <!-- Nhúng Livewire 3 Component: Bảng Mặt Bằng & Diện Tích Cắt Thuê Trống -->
        <livewire:floor-vacancy-list :buildingId="$building->id ?? ''" />

        <!-- Bản đồ vị trí tòa nhà -->
        <div class="detail-floors-card">
          <h2 style="font-size: 20px; font-weight: 800; color: var(--mo-primary);"><i class="fa-solid fa-map-pin"></i> Vị Trí Tòa Nhà Trên Bản Đồ TP. Hồ Chí Minh</h2>
          <p style="font-size: 13.5px; color: #64748b;">Tọa lạc tại vị trí đắc địa, giao thông thuận tiện kết nối các tuyến đường huyết mạch</p>
          <div class="detail-map-mock">
            <iframe src="https://maps.google.com/maps?q={{ urlencode($building->address ?? 'Quận 1 TP.HCM') }}&t=&z=15&ie=UTF8&iwloc=&output=embed" loading="lazy"></iframe>
          </div>
        </div>
      </div>
    </main>

    <!-- Footer -->
    @include('portal.components.footer')
  </div>
@endsection
