@extends('layouts.admin')

@section('title', 'Quản Lý Hợp Đồng Thuê Văn Phòng | Office BMS')

@section('meta')
  <meta name="description" content="Hệ thống quản lý hợp đồng thuê văn phòng, gia hạn hợp đồng, ký kết pháp lý và chống trùng lịch sàn cho thuê.">
@endsection

@section('content')
  <div style="min-height: 100vh; background: #0f172a; padding: 24px;">
    <div class="container">
      <div class="mo-breadcrumbs" style="margin-bottom: 24px; display: flex; align-items: center; gap: 8px;">
        <a href="{{ route('admin.dashboard') }}" style="color: #94a3b8; text-decoration: none; font-size: 13.5px;"><i class="fa-solid fa-chart-pie"></i> Bảng điều khiển BMS</a>
        <i class="fa-solid fa-chevron-right" style="font-size:10px; color: #475569;"></i>
        <span style="color: #f8fafc; font-weight: 600; font-size: 13.5px;">Quản lý hợp đồng thuê văn phòng</span>
      </div>

      <!-- Nhúng Livewire 3 ContractManager Component -->
      <livewire:contract-manager />
    </div>
  </div>
@endsection
