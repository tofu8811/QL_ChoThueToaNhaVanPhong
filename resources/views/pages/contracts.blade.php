@extends('layouts.portal')

@section('title', 'Quản Lý Hợp Đồng Thuê Văn Phòng | Hệ Thống Tòa Nhà')

@section('meta')
  <meta name="description" content="Hệ thống quản lý hợp đồng thuê văn phòng, gia hạn hợp đồng, ký kết pháp lý và chống trùng lịch sàn cho thuê.">
  <meta property="og:title" content="Quản Lý Hợp Đồng Thuê Văn Phòng">
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
          <span>Quản lý hợp đồng thuê (Module 3)</span>
        </div>

        <!-- Nhúng Livewire 3 ContractManager Component -->
        <livewire:contract-manager />
      </div>
    </main>

    @include('portal.components.footer')
  </div>
@endsection
