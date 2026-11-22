@extends('layouts.bms-master')

@section('title', 'Quản Lý Leads & Điều Phối Khách Hàng CRM | Super Admin Portal')
@section('breadcrumb', 'Quản lý Leads Khách hàng & Pipeline CRM')

@section('sidebar')
  @include('components.sidebars.admin-sidebar')
@endsection

@section('portal-content')
  <style>
    #tab-sales-leads { display: block !important; }
    #tab-sales-performance { display: none !important; }
    #tab-sales-quotes { display: none !important; }
  </style>

  @include('bms.tabs.sales')
@endsection

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function () {
    if (typeof switchFilamentTab === 'function') {
      switchFilamentTab('tab-sales-leads', null, false);
    }
    if (typeof handleRoleSwitch === 'function') {
      window.currentUiRole = 'admin';
    }
    if (typeof loadCrmLeads === 'function') {
      loadCrmLeads();
    }
  });
</script>
@endpush

