@extends('layouts.bms-master')

@section('title', 'Sổ Danh Bạ Khách Thuê Doanh Nghiệp | Kế Toán')
@section('breadcrumb', 'Sổ danh bạ khách thuê doanh nghiệp')

@section('sidebar')
  @include('components.sidebars.accountant-sidebar')
@endsection

@section('portal-content')
  <style>
    #tab-admin-tenants { display: block !important; }
  </style>

  @include('bms.tabs.tenants')
@endsection

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function () {
    if (typeof switchFilamentTab === 'function') {
      switchFilamentTab('tab-admin-tenants', null, false);
    }
    if (typeof handleRoleSwitch === 'function') {
      window.currentUiRole = 'accountant';
    }
  });
</script>
@endpush

