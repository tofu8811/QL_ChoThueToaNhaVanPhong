@extends('layouts.bms-master')

@section('title', 'Tài Nguyên Mặt Bằng Tòa Nhà Phụ Trách | Ban Quản Lý (PM)')
@section('breadcrumb', 'Mặt bằng các tòa nhà phụ trách')

@section('sidebar')
  @include('components.sidebars.pm-sidebar')
@endsection

@section('portal-content')
  <style>
    #tab-admin-buildings { display: block !important; }
  </style>

  @include('bms.tabs.buildings')
@endsection

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function () {
    if (typeof switchFilamentTab === 'function') {
      switchFilamentTab('tab-admin-buildings', null, false);
    }
    if (typeof handleRoleSwitch === 'function') {
      window.currentUiRole = 'pm';
    }
  });
</script>
@endpush

