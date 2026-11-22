@extends('layouts.bms-master')

@section('title', 'Quản Lý Danh Bạ Khách Thuê | Admin Portal')
@section('breadcrumb', 'Quản lý khách hàng thuê')

@section('sidebar')
  @include('components.sidebars.admin-sidebar')
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
  });
</script>
@endpush

