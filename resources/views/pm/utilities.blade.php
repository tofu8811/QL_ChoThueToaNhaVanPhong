@extends('layouts.bms-master')

@section('title', 'Chỉ Số Tiêu Thụ Điện Nước & Dịch Vụ | Ban Quản Lý (PM)')
@section('breadcrumb', 'Chỉ số điện nước & Dịch vụ tòa nhà')

@section('sidebar')
  @include('components.sidebars.pm-sidebar')
@endsection

@section('portal-content')
  <style>
    #tab-admin-billing { display: block !important; }
  </style>

  @include('bms.tabs.billing')
@endsection

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function () {
    if (typeof switchFilamentTab === 'function') {
      switchFilamentTab('tab-admin-billing', null, false);
    }
    if (typeof handleRoleSwitch === 'function') {
      window.currentUiRole = 'pm';
    }
  });
</script>
@endpush

