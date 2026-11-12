@extends('layouts.portal')

@section('title', 'Office - Cho thuê văn phòng TP.HCM | Hệ thống Quản lý Tòa nhà')

@section('content')
  <div id="portal-wrapper">
    <!-- Header & Topbar -->
    @include('portal.components.header')

    <!-- Page 1: Trang chủ tìm kiếm tòa nhà & bộ lọc nâng cao -->
    @include('portal.pages.home')

    <!-- Page 2: Chi tiết tòa nhà & danh sách diện tích sàn trống -->
    @include('portal.pages.detail')

    <!-- Page 3: Form đăng ký tư vấn & nhận báo giá chi tiết -->
    @include('portal.pages.quote')

    <!-- Footer thông tin liên hệ & bản quyền -->
    @include('portal.components.footer')
  </div>

  <!-- VIEW B: FILAMENT ADMIN PANEL SHELL (QUẢN TRỊ 4 TÁC NHÂN: ADMIN, SALES, BQL, TENANT) -->
  @include('portal.admin.shell')
@endsection
