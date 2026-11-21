@extends('layouts.tenant')

@section('title', 'Cổng Khách Thuê Doanh Nghiệp | Tenant Portal')

@section('meta')
  <meta name="description" content="Cổng thông tin tự phục vụ dành cho doanh nghiệp thuê văn phòng: theo dõi hợp đồng, xem chi tiết mặt bằng, nhận thông báo cước phí và tra cứu công nợ.">
@endsection

@section('content')
  <main class="page-section active" style="min-height: calc(100vh - 64px); padding: 30px 0 60px;">
    <div class="container">
      <div class="mo-breadcrumbs" style="margin-bottom: 24px; display: flex; align-items: center; gap: 8px;">
        <a href="{{ route('home') }}" style="color: #64748b; text-decoration: none; font-size: 13.5px;"><i class="fa-solid fa-globe"></i> Trang chủ</a>
        <i class="fa-solid fa-chevron-right" style="font-size:10px; color: #94a3b8;"></i>
        <span style="color: #0f172a; font-weight: 600; font-size: 13.5px;">Cổng khách thuê (Tenant Workspace)</span>
      </div>

      <!-- Nhúng Livewire 3 TenantPortalWorkspace Component -->
      <livewire:tenant-portal-workspace :tenant-id="$tenantId ?? request('tenant_id')" />
    </div>
  </main>
@endsection
