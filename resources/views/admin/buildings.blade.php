@extends('layouts.bms-master')

@section('title', 'Quản Lý Tài Nguyên Mặt Bằng Tòa Nhà | Admin Portal')
@section('breadcrumb', 'Quản lý tài nguyên mặt bằng tòa nhà')

@section('sidebar')
  @include('components.sidebars.admin-sidebar')
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
  });
</script>
@endpush

