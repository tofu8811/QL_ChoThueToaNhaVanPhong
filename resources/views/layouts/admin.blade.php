<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'Quản Trị Vận Hành Tòa Nhà | Office BMS')</title>
  @yield('meta')
  <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
  <link rel="alternate icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">

  <!-- Google Fonts: Inter -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
  
  <!-- FontAwesome Icons CDN -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

  <!-- BMS & Portal Stylesheet -->
  @include('partials.styles')
  @livewireStyles
  @stack('styles')

  <!-- Auth Guard Client-Side Sync -->
  <script>
    (function() {
      try {
        var t = localStorage.getItem('auth_token');
        if (!t) {
          // Chưa đăng nhập thì chuyển về trang chủ mở login modal
          window.location.replace('/?auth=required');
          return;
        }
        var role = String(localStorage.getItem('auth_role') || '').toUpperCase();
        if (role === 'TENANT') {
          var tid = localStorage.getItem('tenant_id') || '';
          window.location.replace('/tenant/portal' + (tid ? '?tenant_id=' + tid : ''));
          return;
        }
        if (t && !document.cookie.includes('auth_token=')) {
          document.cookie = 'auth_token=' + t + '; path=/; max-age=86400; SameSite=Lax';
        }
      } catch (e) {}
    })();
  </script>
</head>
<body style="margin: 0 !important; padding: 0 !important; background: #0f172a; color: #f8fafc;">
  @yield('content')

  <!-- Modals Quản trị BMS -->
  @include('partials.modals')

  <!-- Core JavaScript & API Modules -->
  @include('partials.scripts.main')
  @include('partials.scripts.validation')
  @include('partials.scripts.auth')
  @include('partials.scripts.shell')
  @include('partials.scripts.tickets')
  @include('partials.scripts.staff')
  @include('partials.scripts.assignments')
  @include('partials.scripts.admin-buildings')
  @include('partials.scripts.crm-leads')
  @include('partials.scripts.tenants')
  @include('partials.scripts.contracts')
  @include('partials.scripts.mail-settings')
  @include('partials.scripts.role-operations')
  @include('partials.scripts.init')

  @livewireScripts
  @stack('scripts')
</body>
</html>
