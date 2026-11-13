@extends('layouts.portal')

@section('title', 'Cổng Khách Thuê Doanh Nghiệp | Quản Lý Mặt Bằng & Cước Phí')

@section('meta')
  <meta name="description" content="Cổng thông tin tự phục vụ dành cho doanh nghiệp thuê văn phòng: theo dõi hợp đồng, xem chi tiết mặt bằng, nhận thông báo cước phí và tra cứu công nợ.">
  <meta property="og:title" content="Cổng Khách Thuê Doanh Nghiệp">
  <meta property="og:type" content="website">
@endsection

@section('content')
  <div id="portal-wrapper">
    @include('portal.components.header')

    <main class="page-section active">
      <div class="container" style="padding-top: 30px; padding-bottom: 60px;">
        <div class="mo-breadcrumbs" style="margin-bottom: 20px;">
          <a href="{{ route('home') }}">Trang chủ</a>
          <i class="fa-solid fa-chevron-right" style="font-size:10px;"></i>
          <span>Cổng khách thuê văn phòng (Tenant Portal)</span>
        </div>

        <!-- Nhúng Livewire 3 TenantPortalWorkspace Component -->
        <livewire:tenant-portal-workspace />
      </div>
    </main>

    @include('portal.components.footer')
  </div>
@endsection
