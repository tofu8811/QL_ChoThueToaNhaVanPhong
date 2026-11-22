@extends('layouts.bms-master')

@section('title', 'Quản Trị Nhân Sự & Phân Quyền RBAC | Super Admin Portal')
@section('breadcrumb', 'Quản trị nhân sự & Phân quyền RBAC')

@section('sidebar')
  @include('components.sidebars.admin-sidebar')
@endsection

@section('portal-content')
  <style>
    #tab-admin-staff { display: block !important; }
  </style>

  @include('bms.tabs.staff-rbac')
@endsection

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function () {
    if (typeof switchFilamentTab === 'function') {
      switchFilamentTab('tab-admin-staff', null, false);
    }
  });
</script>
@endpush

