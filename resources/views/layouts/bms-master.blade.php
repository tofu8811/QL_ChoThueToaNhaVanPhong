@extends('layouts.admin')

@section('content')
<div id="admin-wrapper" style="display: block;">
  <div class="filament-shell">
    @yield('sidebar')

    <div class="filament-main">
      @include('components.topbar')

      <div class="filament-body" style="padding: 20px 24px; min-height: calc(100vh - 70px);">
        @if(session('warning'))
          <div style="margin-bottom:16px;padding:12px 16px;background:rgba(245,158,11,0.15);border:1px solid rgba(245,158,11,0.3);color:#fbbf24;border-radius:8px;font-size:13.5px;display:flex;align-items:center;gap:10px;">
            <i class="fa-solid fa-triangle-exclamation"></i>
            <span>{{ session('warning') }}</span>
          </div>
        @endif

        @if(session('info'))
          <div style="margin-bottom:16px;padding:12px 16px;background:rgba(59,130,246,0.15);border:1px solid rgba(59,130,246,0.3);color:#60a5fa;border-radius:8px;font-size:13.5px;display:flex;align-items:center;gap:10px;">
            <i class="fa-solid fa-circle-info"></i>
            <span>{{ session('info') }}</span>
          </div>
        @endif

        @yield('portal-content')
      </div>
    </div>
  </div>
</div>
@endsection
