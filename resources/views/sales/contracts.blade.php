@extends('layouts.bms-master')

@section('title', 'Hợp Đồng & Lập HĐ Nháp | Chuyên Viên Sales')
@section('breadcrumb', 'Hợp đồng & Lập HĐ nháp')

@section('sidebar')
  @include('components.sidebars.sales-sidebar')
@endsection

@section('portal-content')
  <style>
    #tab-admin-contracts { display: block !important; }
  </style>

  @include('bms.tabs.contracts')
@endsection

@push('scripts')
<script>
  window.isSalesUser = true;
  window.currentUiRole = 'sales';
  try {
    localStorage.setItem('auth_role', 'sales');
  } catch (e) {}

  document.addEventListener('DOMContentLoaded', function () {
    if (typeof switchFilamentTab === 'function') {
      const link = document.querySelector('.filament-link[href*="contracts"]') || document.querySelector('.filament-link[onclick*="tab-admin-contracts"]');
      switchFilamentTab('tab-admin-contracts', link, false);
    }
    if (typeof loadContractsList === 'function') {
      loadContractsList();
    }
  });
</script>
@endpush
