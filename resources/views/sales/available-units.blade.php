@extends('layouts.bms-master')

@section('title', 'Danh Mục Mặt Bằng Trống Chào Thuê | Chuyên Viên Sales')
@section('breadcrumb', 'Danh mục mặt bằng trống chào thuê')

@section('sidebar')
  @include('components.sidebars.sales-sidebar')
@endsection

@section('portal-content')
  <style>
    #tab-admin-buildings { display: block !important; }
    /* Sales chỉ xem, ẩn nút thêm tòa nhà mới và phân công nhân sự */
    #btn-create-building { display: none !important; }
  </style>

  @include('bms.tabs.buildings')
@endsection

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function () {
    if (typeof switchFilamentTab === 'function') {
      switchFilamentTab('tab-admin-buildings', null, false);
    }
    if (typeof handleRoleSwitch === 'function') {
      window.currentUiRole = 'sales';
    }
  });
</script>
@endpush

