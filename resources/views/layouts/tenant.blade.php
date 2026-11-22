<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'Cổng Khách Thuê Doanh Nghiệp | Tenant Portal')</title>
  @yield('meta')
  <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
  <link rel="alternate icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">

  <!-- Google Fonts: Inter -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
  
  <!-- FontAwesome Icons CDN -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

  <!-- Portal & Tenant Stylesheet -->
  @include('portal.partials.styles')
  @livewireStyles
  @stack('styles')

  <!-- Auth Guard Client-Side Sync -->
  <script>
    (function() {
      try {
        var t = localStorage.getItem('auth_token');
        if (!t) {
          window.location.replace('/?auth=required');
          return;
        }
        if (t && !document.cookie.includes('auth_token=')) {
          document.cookie = 'auth_token=' + t + '; path=/; max-age=86400; SameSite=Lax';
        }
        var tid = localStorage.getItem('tenant_id');
        if (tid && !document.cookie.includes('tenant_id=' + tid)) {
          document.cookie = 'tenant_id=' + tid + '; path=/; max-age=86400; SameSite=Lax';
        }
      } catch (e) {}
    })();
  </script>
</head>
<body style="margin: 0 !important; padding: 0 !important; background: #f8fafc; color: #1e293b;">
  <!-- Tenant Workspace Header -->
  <header style="background: #ffffff; border-bottom: 1px solid #e2e8f0; position: sticky; top: 0; z-index: 50; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
    <div class="container" style="display: flex; justify-content: space-between; align-items: center; height: 64px;">
      <div style="display: flex; align-items: center; gap: 12px;">
        <a href="{{ route('home') }}" style="display: flex; align-items: center; gap: 10px; text-decoration: none; color: #0f172a;" title="Về trang chủ Cổng thông tin">
          <div style="width: 36px; height: 36px; border-radius: 9px; background: linear-gradient(135deg, #2563eb, #1d4ed8); display: flex; align-items: center; justify-content: center; color: #fff; font-size: 16px;">
            <i class="fa-solid fa-building"></i>
          </div>
          <div>
            <span style="font-size: 18px; font-weight: 800; letter-spacing: -0.3px; color: #0f172a;">Office</span>
            <span style="font-size: 11px; font-weight: 700; color: #2563eb; background: #eff6ff; padding: 2px 7px; border-radius: 6px; margin-left: 6px; text-transform: uppercase;">Tenant</span>
          </div>
        </a>
      </div>

      <div style="display: flex; align-items: center; gap: 16px;">
        <a href="{{ route('home') }}" style="font-size: 13.5px; font-weight: 600; color: #64748b; text-decoration: none; display: flex; align-items: center; gap: 6px;">
          <i class="fa-solid fa-globe"></i> Cổng thông tin
        </a>
        <div style="height: 20px; width: 1px; background: #cbd5e1;"></div>
        <button type="button" class="btn-mo-outline" style="padding: 6px 14px; font-size: 13px; border-radius: 8px;" onclick="handleLogout()">
          <i class="fa-solid fa-arrow-right-from-bracket"></i> Đăng xuất
        </button>
      </div>
    </div>
  </header>

  @yield('content')

  <!-- Modals -->
  @include('portal.partials.modals')

  <!-- Core JavaScript & API Modules -->
  @include('portal.scripts.main')
  @include('portal.scripts.validation')
  @include('portal.scripts.auth')
  @include('portal.scripts.tickets')
  @include('portal.scripts.role-operations')
  @include('portal.scripts.init')

  @livewireScripts
  @stack('scripts')
</body>
</html>
