@extends('layouts.admin')

@section('title', 'Hồ Sơ Khách Thuê Doanh Nghiệp | Office BMS')

@section('meta')
  <meta name="description" content="Quản lý thông tin hồ sơ doanh nghiệp khách thuê, người đại diện pháp luật, mã số thuế và theo dõi lịch sử thuê văn phòng.">
@endsection

@section('content')
  <div style="min-height: 100vh; background: #0f172a; padding: 24px;">
    <div class="container">
      <div class="mo-breadcrumbs" style="margin-bottom: 24px; display: flex; align-items: center; gap: 8px;">
        <a href="{{ route('admin.dashboard') }}" style="color: #94a3b8; text-decoration: none; font-size: 13.5px;"><i class="fa-solid fa-chart-pie"></i> Bảng điều khiển BMS</a>
        <i class="fa-solid fa-chevron-right" style="font-size:10px; color: #475569;"></i>
        <span style="color: #f8fafc; font-weight: 600; font-size: 13.5px;">Hồ sơ khách thuê doanh nghiệp</span>
      </div>

      <!-- Nhúng Livewire 3 TenantManager Component -->
      <livewire:tenant-manager />
    </div>
  </div>
@endsection
