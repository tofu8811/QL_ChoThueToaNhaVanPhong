@extends('layouts.bms-master')

@section('title', 'Thông Báo Phí (Debit Notes) & Đối Soát UNC | Kế Toán')
@section('breadcrumb', 'Thông báo phí & Đối soát UNC')

@section('sidebar')
  @include('components.sidebars.accountant-sidebar')
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
      window.currentUiRole = 'accountant';
    }
  });
</script>
@endpush

