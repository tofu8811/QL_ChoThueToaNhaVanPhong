@extends('layouts.bms-master')

@section('title', 'Xử Lý Sự Cố Kỹ Thuật & Thi Công Ngoài Giờ | Ban Quản Lý (PM)')
@section('breadcrumb', 'Tiếp nhận & Xử lý Ticket sự cố')

@section('sidebar')
  @include('components.sidebars.pm-sidebar')
@endsection

@section('portal-content')
  <style>
    #tab-admin-tickets { display: block !important; }
  </style>

  @include('bms.tabs.tickets')
@endsection

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function () {
    if (typeof switchFilamentTab === 'function') {
      switchFilamentTab('tab-admin-tickets', null, false);
    }
    if (typeof handleRoleSwitch === 'function') {
      window.currentUiRole = 'pm';
    }
  });
</script>
@endpush

