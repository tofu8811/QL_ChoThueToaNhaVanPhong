@extends('layouts.bms-master')

@section('title', 'Quản Lý Hợp Đồng Thuê & Bàn Giao | Admin Portal')
@section('breadcrumb', 'Hợp đồng thuê & Biên bản bàn giao')

@section('sidebar')
  @php
    $currentRole = request()->attributes->get('current_web_role');
    $user = auth()->user() ?? request()->attributes->get('current_web_user');
  @endphp
  @if($currentRole === 'sales' || $user instanceof \App\Models\Sale)
    @include('components.sidebars.sales-sidebar')
  @elseif($currentRole === 'pm' || $user instanceof \App\Models\PropertyManager)
    @include('components.sidebars.pm-sidebar')
  @else
    @include('components.sidebars.admin-sidebar')
  @endif
@endsection

@section('portal-content')
  <style>
    #tab-admin-contracts { display: block !important; }
  </style>

  @include('bms.tabs.contracts')
@endsection

@push('scripts')
<script>
  window.currentUiRole = '{{ ($currentRole === "sales" || $user instanceof \App\Models\Sale) ? "sales" : (($currentRole === "pm" || $user instanceof \App\Models\PropertyManager) ? "pm" : "admin") }}';
  @if($currentRole === 'sales' || $user instanceof \App\Models\Sale)
    window.isSalesUser = true;
  @else
    window.isSalesUser = false;
  @endif

  document.addEventListener('DOMContentLoaded', function () {
    if (typeof switchFilamentTab === 'function') {
      const link = document.querySelector('.filament-link[href*="contracts"]') || document.querySelector('.filament-link[onclick*="tab-admin-contracts"]');
      switchFilamentTab('tab-admin-contracts', link, true);
    }
    if (typeof loadContractsList === 'function') {
      loadContractsList();
    }
  });
</script>
@endpush

