@extends('layouts.bms-master')

@section('title', 'Quản Lý Hợp Đồng Thuê & Bàn Giao | Admin Portal')
@section('breadcrumb', 'Hợp đồng thuê & Biên bản bàn giao')

@section('sidebar')
  @include('components.sidebars.admin-sidebar')
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
  });
</script>
@endpush

