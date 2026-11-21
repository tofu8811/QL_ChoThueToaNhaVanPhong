@extends('layouts.admin')

@section('title', 'Quản Lý Phễu Bán Hàng & CRM Leads | Office BMS')

@section('meta')
  <meta name="description" content="Phân hệ CRM kinh doanh: Tiếp nhận Lead tự động, phân bổ Sales Least-Active-Leads và theo dõi tiến độ đàm phán hợp đồng.">
@endsection

@section('content')
  <div style="min-height: 100vh; background: #0f172a; padding: 24px;">
    <div class="container">
      <div class="mo-breadcrumbs" style="margin-bottom: 24px; display: flex; align-items: center; gap: 8px;">
        <a href="{{ route('admin.dashboard') }}" style="color: #94a3b8; text-decoration: none; font-size: 13.5px;"><i class="fa-solid fa-chart-pie"></i> Bảng điều khiển BMS</a>
        <i class="fa-solid fa-chevron-right" style="font-size:10px; color: #475569;"></i>
        <span style="color: #f8fafc; font-weight: 600; font-size: 13.5px;">Quản lý phễu khách hàng & CRM Leads</span>
      </div>

      <!-- Nhúng Livewire 3 CrmLeadPipeline Component -->
      <livewire:crm-lead-pipeline />
    </div>
  </div>
@endsection
