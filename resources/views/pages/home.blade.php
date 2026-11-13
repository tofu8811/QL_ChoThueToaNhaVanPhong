@extends('layouts.portal')

@section('title', 'Cho Thuê Văn Phòng TP.HCM - Cập Nhật Báo Giá & Diện Tích Mới Nhất 2026')

@section('meta')
  <meta name="description" content="Hệ thống khảo sát và phân phối mặt bằng văn phòng chuyên nghiệp tại TP. Hồ Chí Minh với hơn 2.100+ cao ốc từ Hạng A, B, C và Giá rẻ.">
  <!-- OpenGraph Meta Tags -->
  <meta property="og:title" content="Cho Thuê Văn Phòng TP.HCM - Khảo Sát & Báo Giá Nhanh 2026">
  <meta property="og:description" content="Hệ thống khảo sát và phân phối mặt bằng văn phòng chuyên nghiệp tại TP. Hồ Chí Minh. Dữ liệu thực tế, tư vấn miễn phí.">
  <meta property="og:type" content="website">
  <meta property="og:url" content="{{ url()->current() }}">
  <meta property="og:image" content="https://images.unsplash.com/photo-1577495508048-b635879837f1?auto=format&fit=crop&w=1200&q=80">
  <!-- Schema.org JSON-LD -->
  <script type="application/ld+json">
  {!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'RealEstateAgent',
    'name' => 'Office Leasing Portal',
    'description' => 'Hệ thống Quản lý và Cho thuê Tòa nhà Văn phòng chuyên nghiệp',
    'areaServed' => 'Thành phố Hồ Chí Minh',
    'priceRange' => '$20 - $70 / m²',
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
            <span>Cho thuê văn phòng TP.HCM</span>
          </div>
          <h1 class="mo-hero-title">Cho Thuê Văn Phòng TP.HCM - Cập Nhật Báo Giá & Diện Tích Mới Nhất 2026</h1>
          <p class="mo-hero-desc">
            Hệ thống khảo sát và phân phối mặt bằng văn phòng chuyên nghiệp tại TP. Hồ Chí Minh. Dữ liệu thực tế hơn 2.100+ cao ốc từ Hạng A, B, C và Giá rẻ. Tư vấn miễn phí và hỗ trợ thương lượng giá tốt nhất cho doanh nghiệp.
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
