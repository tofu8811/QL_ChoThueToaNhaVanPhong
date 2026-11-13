@extends('layouts.portal')

@section('title', 'Đăng Ký Nhận Báo Giá & Đặt Lịch Khảo Sát Văn Phòng TP.HCM | Maison Office')

@section('meta')
  <meta name="description" content="Đăng ký nhận bảng báo giá chi tiết và đặt lịch khảo sát mặt bằng văn phòng cho thuê tại TP.HCM. Chuyên viên tư vấn phản hồi trong vòng 30 phút, miễn phí 100%.">
  <!-- OpenGraph Meta Tags -->
  <meta property="og:title" content="Đăng Ký Nhận Báo Giá Văn Phòng TP.HCM - Maison Office">
  <meta property="og:description" content="Nhận báo giá văn phòng nhanh chóng, so sánh hơn 2.100+ cao ốc từ Hạng A, B, C tại TP.HCM. Hỗ trợ đàm phán giá tốt nhất.">
  <meta property="og:type" content="website">
  <meta property="og:url" content="{{ url()->current() }}">
  <meta property="og:image" content="https://images.unsplash.com/photo-1497366216548-37526070297c?auto=format&fit=crop&w=1200&q=80">
  <!-- Schema.org JSON-LD -->
  <script type="application/ld+json">
  {!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'ContactPage',
    'name' => 'Đăng Ký Nhận Báo Giá Thuê Văn Phòng',
    'description' => 'Cổng tiếp nhận yêu cầu báo giá và đặt lịch khảo sát mặt bằng văn phòng',
    'url' => url()->current(),
    'provider' => [
      '@type' => 'RealEstateAgent',
      'name' => 'Maison Office',
      'telephone' => '+84 901 234 567',
      'areaServed' => 'Thành phố Hồ Chí Minh'
    ]
  ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
  </script>
@endsection

@section('content')
  <div id="portal-wrapper">
    <!-- Header & Topbar -->
    @include('portal.components.header')

    <main id="page-quote-standalone" class="page-section active">
      <div class="container" style="padding-top: 35px; padding-bottom: 60px;">
        <!-- Breadcrumb chuẩn SEO -->
        <div class="mo-breadcrumbs" style="margin-bottom: 24px;">
          <a href="{{ route('home') }}">Trang chủ</a>
          <i class="fa-solid fa-chevron-right" style="font-size:10px;"></i>
          <span>Đăng ký nhận báo giá văn phòng</span>
        </div>

        <!-- Nhúng Livewire 3 Component Form Báo Giá (Module 2) -->
        <livewire:lead-quotation-form :building-id="request('building_id')" :unit-id="request('unit_id')" />
      </div>
    </main>

    <!-- Footer -->
    @include('portal.components.footer')
  </div>
@endsection
