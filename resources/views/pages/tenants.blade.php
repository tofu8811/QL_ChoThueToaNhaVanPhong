@extends('layouts.portal')

@section('title', 'Hồ Sơ Khách Thuê Doanh Nghiệp | Quản Lý Khách Hàng')

@section('meta')
  <meta name="description" content="Quản lý thông tin hồ sơ doanh nghiệp khách thuê, người đại diện pháp luật, mã số thuế và theo dõi lịch sử thuê văn phòng.">
  <meta property="og:title" content="Hồ Sơ Khách Thuê Doanh Nghiệp">
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
          <span>Hồ sơ khách thuê doanh nghiệp (Module 3)</span>
        </div>

        <!-- Nhúng Livewire 3 TenantManager Component -->
        <livewire:tenant-manager />
      </div>
    </main>

    @include('portal.components.footer')
  </div>
@endsection
