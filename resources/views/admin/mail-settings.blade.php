@extends('layouts.bms-master')

@section('title', 'Cấu Hình Email SMTP Hệ Thống | Super Admin Portal')
@section('breadcrumb', 'Thiết lập Email hệ thống')

@section('sidebar')
  @include('components.sidebars.admin-sidebar')
@endsection

@section('portal-content')
  <style>
    #tab-admin-mail-settings { display: block !important; }
  </style>

  @include('bms.tabs.mail-settings')
@endsection

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function () {
    if (typeof switchFilamentTab === 'function') {
      switchFilamentTab('tab-admin-mail-settings', null, false);
    }
  });
</script>
@endpush

