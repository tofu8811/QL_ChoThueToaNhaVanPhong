@extends('layouts.bms-master')

@section('title', 'Tổng Quan Vận Hành Toàn Hệ Thống | Super Admin Portal')
@section('breadcrumb', 'Tổng quan vận hành toàn hệ thống')

@section('sidebar')
  @include('components.sidebars.admin-sidebar')
@endsection

@section('portal-content')
  <style>
    #tab-admin-dashboard { display: block !important; }
  </style>

  @include('bms.tabs.dashboard')
@endsection

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function () {
    if (typeof switchFilamentTab === 'function') {
      switchFilamentTab('tab-admin-dashboard', null, false);
    }
  });
</script>
@endpush

