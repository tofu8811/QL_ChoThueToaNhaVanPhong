@extends('layouts.bms-master')

@section('title', 'Xử Lý Ticket Sự Cố Kỹ Thuật & Thi Công | Admin Portal')
@section('breadcrumb', 'Tiếp nhận & Xử lý Ticket sự cố, thi công')

@section('sidebar')
  @include('components.sidebars.admin-sidebar')
@endsection

@section('portal-content')
  <style>
    #tab-admin-tickets { display: block !important; }
  </style>

  @include('bms.tabs.tickets')
@endsection

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function () {
    if (typeof switchFilamentTab === 'function') {
      switchFilamentTab('tab-admin-tickets', null, false);
    }
  });
</script>
@endpush

