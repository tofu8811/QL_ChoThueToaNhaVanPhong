@extends('layouts.bms-master')

@section('title', 'Tổng Quan Vận Hành Cao Ốc | Ban Quản Lý (PM)')
@section('breadcrumb', 'Tổng quan vận hành tòa nhà phụ trách')

@section('sidebar')
  @include('components.sidebars.pm-sidebar')
@endsection

@section('portal-content')
  <style>
    #tab-admin-dashboard { display: block !important; }
    .kpi-admin-content { display: none !important; }
    .kpi-pm-content {
      display: flex !important;
      flex-direction: column !important;
      justify-content: space-between !important;
      height: 100% !important;
      width: 100% !important;
    }
    .kpi-pm-content > .bms-kpi-foot {
      margin-top: auto !important;
    }
    #dashboard-section-admin { display: none !important; }
    #dashboard-section-pm { display: block !important; }
  </style>

  @include('bms.tabs.dashboard')
@endsection

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function () {
    if (typeof switchFilamentTab === 'function') {
      switchFilamentTab('tab-admin-dashboard', null, false);
    }
    // Chuyển role hiển thị sang PM
    if (typeof handleRoleSwitch === 'function') {
      window.currentUiRole = 'pm';
    }
  });
</script>
@endpush

