@extends('layouts.bms-master')

@section('title', 'Lập Báo Giá & Thư Ngỏ Thuê (LOI) | Chuyên Viên Sales')
@section('breadcrumb', 'Lập báo giá & Thư ngỏ (LOI)')

@section('sidebar')
  @include('components.sidebars.sales-sidebar')
@endsection

@section('portal-content')
  <style>
    #tab-sales-quotes { display: block !important; }
    #tab-sales-performance { display: none !important; }
    #tab-sales-leads { display: none !important; }
  </style>

  @include('bms.tabs.sales')
@endsection

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function () {
    if (typeof switchFilamentTab === 'function') {
      switchFilamentTab('tab-sales-quotes', null, false);
    }
    if (typeof handleRoleSwitch === 'function') {
      window.currentUiRole = 'sales';
    }
  });
</script>
@endpush

