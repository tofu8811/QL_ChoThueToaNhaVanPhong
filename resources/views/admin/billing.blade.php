@extends('layouts.bms-master')

@section('title', 'Kế Toán & Quản Lý Công Nợ Tòa Nhà | Admin Portal')
@section('breadcrumb', 'Kế toán & Công nợ tòa nhà')

@section('sidebar')
  @include('components.sidebars.admin-sidebar')
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
  });
</script>
@endpush

