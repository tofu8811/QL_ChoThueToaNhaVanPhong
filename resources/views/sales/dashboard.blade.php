@extends('layouts.bms-master')

@section('title', 'Báo Cáo Hiệu Suất Kinh Doanh Cá Nhân | Chuyên Viên Sales')
@section('breadcrumb', 'Báo cáo hiệu suất kinh doanh cá nhân')

@section('sidebar')
  @include('components.sidebars.sales-sidebar')
@endsection

@section('portal-content')
  <style>
    #tab-sales-performance { display: block !important; }
    #tab-sales-leads { display: none !important; }
    #tab-sales-quotes { display: none !important; }
  </style>

  @include('bms.tabs.sales')
@endsection

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function () {
    if (typeof switchFilamentTab === 'function') {
      switchFilamentTab('tab-sales-performance', null, false);
    }
    if (typeof handleRoleSwitch === 'function') {
      window.currentUiRole = 'sales';
    }
  });
</script>
@endpush

