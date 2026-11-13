<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'Office - Cho thuê văn phòng TP.HCM | Hệ thống Quản lý Tòa nhà')</title>
  <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
  <link rel="alternate icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">

  <!-- Google Fonts: Inter -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
  
  <!-- FontAwesome Icons CDN -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

  <!-- Portal Stylesheet -->
  @include('portal.partials.styles')
  @livewireStyles
  @stack('styles')
</head><body style="margin: 0 !important; padding: 0 !important;">
  @yield('content')

  <!-- Modals (Báo giá nhanh, Ticket sự cố, Đăng nhập đa tác nhân) -->
  @include('portal.partials.modals')

  <!-- JavaScript Client Logic & API Sync -->
  @include('portal.partials.scripts')
  @livewireScripts
  @stack('scripts')
</body>
</html>
