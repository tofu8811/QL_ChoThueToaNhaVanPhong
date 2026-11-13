@extends('layouts.portal')

@php
  $totalB = $stats['total_buildings'] ?? 39;
  $provincesText = $stats['active_provinces_text'] ?? 'Hà Nội, TP.HCM, Đồng Nai & Đà Nẵng';
  $provincesArr = !empty($stats['active_provinces']) ? $stats['active_provinces'] : ['Thành phố Hà Nội', 'Thành phố Hồ Chí Minh', 'Thành phố Đồng Nai', 'Thành phố Đà Nẵng'];
  $minPrice = $stats['min_price_usd'] ?? 10;
  $maxPrice = $stats['max_price_usd'] ?? 70;
@endphp

@section('title', 'Cho Thuê Văn Phòng Toàn Quốc | Office Portal')

@section('meta')
  <meta name="description" content="Hệ thống khảo sát và phân phối mặt bằng văn phòng chuyên nghiệp toàn quốc với {{ $totalB }}+ cao ốc từ Hạng A, B, C trên toàn quốc. Cập nhật báo giá & diện tích trống mới nhất.">
  <!-- OpenGraph Meta Tags -->
  <meta property="og:title" content="Cho Thuê Văn Phòng Toàn Quốc">
  <meta property="og:description" content="Hệ thống khảo sát và phân phối mặt bằng văn phòng chuyên nghiệp với {{ $totalB }}+ cao ốc từ Hạng A, B, C trên toàn quốc.">
  <meta property="og:type" content="website">
  <meta property="og:url" content="{{ url()->current() }}">
  <meta property="og:image" content="https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?auto=format&fit=crop&w=1200&q=80">
  <!-- Schema.org JSON-LD -->
  <script type="application/ld+json">
  {!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'RealEstateAgent',
    'name' => 'Office Leasing Portal',
    'description' => 'Hệ thống Quản lý và Cho thuê Tòa nhà Văn phòng chuyên nghiệp toàn quốc',
    'areaServed' => $provincesArr,
    'priceRange' => '$' . $minPrice . ' - $' . $maxPrice . ' / m²',
  ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
  </script>
@endsection

@section('content')
  <div id="portal-wrapper">
    <!-- Header & Topbar -->
    @include('portal.components.header')

    <main id="page-home" class="page-section active">
      <!-- Hero Banner Chuẩn SEO -->
      <section class="mo-hero-banner">
        <div class="container">
          <div class="mo-breadcrumbs">
            <a href="{{ route('home') }}">Trang chủ</a>
            <i class="fa-solid fa-chevron-right" style="font-size:10px;"></i>
            <span>Cho thuê văn phòng toàn quốc</span>
          </div>
          <h1 class="mo-hero-title">Cho Thuê Văn Phòng Toàn Quốc</h1>
          <p class="mo-hero-desc">
            Hệ thống khảo sát và phân phối mặt bằng văn phòng chuyên nghiệp toàn quốc. Dữ liệu thực tế {{ $totalB }}+ cao ốc từ Hạng A, B, C trên toàn quốc. Cập nhật báo giá & diện tích trống mới nhất {{ date('Y') }}.
          </p>
        </div>
      </section>

      <!-- Nhúng Livewire 3 Component: Bộ lọc Reactive & Grid Tòa Nhà -->
      <livewire:building-search-filter />
    </main>

    <!-- Page 2: Chi tiết tòa nhà -->
    @include('portal.pages.detail')

    <!-- Page 3: Form đăng ký tư vấn & nhận báo giá -->
    @include('portal.pages.quote')

    <!-- Footer -->
    @include('portal.components.footer')
  </div>

  <!-- Filament Admin Shell (Quản trị BQL, Sales, Tenant) -->
  @include('portal.admin.shell')
@endsection
