@extends('layouts.bms-master')

@section('title', 'Hợp Đồng & Lịch Trình Thanh Toán Tiền Thuê | Kế Toán')
@section('breadcrumb', 'Hợp đồng & Lịch thanh toán tiền thuê')

@section('sidebar')
  @include('components.sidebars.accountant-sidebar')
@endsection

@section('portal-content')
  <style>
    #tab-admin-contracts { display: block !important; }
  </style>

  @include('bms.tabs.contracts')
@endsection

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function () {
    if (typeof switchFilamentTab === 'function') {
      switchFilamentTab('tab-admin-contracts', null, false);
    }
    if (typeof handleRoleSwitch === 'function') {
      window.currentUiRole = 'accountant';
    }
  });
</script>
@endpush

