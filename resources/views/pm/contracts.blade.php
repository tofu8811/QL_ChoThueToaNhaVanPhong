@extends('layouts.bms-master')

@section('title', 'Ký Duyệt Hợp Đồng & Bàn Giao Mặt Bằng | Ban Quản Lý (PM)')
@section('breadcrumb', 'Ký duyệt hợp đồng & Bàn giao')

@section('sidebar')
  @include('components.sidebars.pm-sidebar')
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
      window.currentUiRole = 'pm';
    }
  });
</script>
@endpush

