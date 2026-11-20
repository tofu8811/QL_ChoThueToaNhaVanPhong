@extends('layouts.portal')

@section('title', 'Quản Lý Phễu Bán Hàng & Tiếp Nhận Lead (CRM) | Phân Hệ Kinh Doanh')

@section('meta')
  <meta name="description" content="Phân hệ CRM kinh doanh: Tiếp nhận Lead tự động, phân bổ Sales Least-Active-Leads và theo dõi tiến độ đàm phán hợp đồng.">
  <meta property="og:title" content="Quản Lý Phễu Bán Hàng & Tiếp Nhận Lead CRM">
  <meta property="og:type" content="website">
@endsection

@section('content')
  <script>
    if (!localStorage.getItem('auth_token')) {
      window.location.replace('/#portal');
    }
  </script>
  <div id="portal-wrapper">
    @include('portal.components.header')

    <main class="page-section active">
      <div class="container" style="padding-top: 30px; padding-bottom: 60px;">
        <div class="mo-breadcrumbs" style="margin-bottom: 20px;">
          <a href="{{ route('home') }}">Trang chủ</a>
          <i class="fa-solid fa-chevron-right" style="font-size:10px;"></i>
          <span>Phân hệ kinh doanh / Quản lý Phễu CRM (Module 2)</span>
        </div>

        <!-- Nhúng Livewire 3 CrmLeadPipeline Component -->
        <livewire:crm-lead-pipeline />
      </div>
    </main>

    @include('portal.components.footer')
  </div>
@endsection
