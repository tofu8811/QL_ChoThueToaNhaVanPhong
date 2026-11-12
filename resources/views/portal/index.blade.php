<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Office - Cho thuê văn phòng TP.HCM | Hệ thống Quản lý Tòa nhà</title>
  <!-- Google Fonts: Inter -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
  <!-- FontAwesome Icons CDN -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <style>
    /* ==========================================================================
       1. VARIABLES & RESET (Maison Office Palette + Filament Admin Palette)
       ========================================================================== */
    :root {
      /* Maison Office Brand Colors */
      --mo-primary: #0f3361;       /* Deep Corporate Navy */
      --mo-primary-dark: #09203f;  /* Header & Dark backgrounds */
      --mo-primary-light: #1b4982;
      --mo-orange: #ea580c;        /* Maison Office Accent / Action Orange */
      --mo-orange-hover: #c2410c;
      --mo-orange-light: #fff7ed;
      --mo-blue-accent: #2563eb;
      --mo-text-main: #1e293b;
      --mo-text-muted: #64748b;
      --mo-text-light: #94a3b8;
      --mo-bg: #f8fafc;
      --mo-card-bg: #ffffff;
      --mo-border: #e2e8f0;
      --mo-border-light: #f1f5f9;

      /* Filament PHP Admin Panel Colors */
      --fl-bg: #f1f5f9;
      --fl-sidebar: #0f172a;       /* Slate 900 */
      --fl-sidebar-hover: #1e293b; /* Slate 800 */
      --fl-sidebar-active: #2563eb;/* Primary blue */
      --fl-card: #ffffff;
      --fl-border: #e2e8f0;
      --fl-primary: #f59e0b;       /* Filament Amber accent */
      --fl-blue: #3b82f6;
      --fl-success: #10b981;
      --fl-warning: #f59e0b;
      --fl-danger: #ef4444;
      --fl-info: #06b6d4;

      --radius-sm: 6px;
      --radius-md: 10px;
      --radius-lg: 16px;
      --shadow-sm: 0 1px 3px rgba(0,0,0,0.06);
      --shadow-md: 0 4px 12px rgba(15, 51, 97, 0.08);
      --shadow-lg: 0 12px 30px rgba(15, 51, 97, 0.12);
      --shadow-xl: 0 20px 40px rgba(15, 51, 97, 0.16);
    }

    * { box-sizing: border-box; margin: 0; padding: 0; }
    html { scroll-behavior: smooth; }
    body {
      font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
      color: var(--mo-text-main);
      background-color: var(--mo-bg);
      line-height: 1.5;
      -webkit-font-smoothing: antialiased;
    }

    a { text-decoration: none; color: inherit; transition: all 0.2s; }
    button, input, select, textarea { font-family: inherit; outline: none; }
    button { cursor: pointer; transition: all 0.2s; border: none; }
    img { max-width: 100%; display: block; }
    ul { list-style: none; }

    /* Layout Containers */
    .container { max-width: 1240px; margin: 0 auto; padding: 0 20px; }
    .page-section { display: none; }
    .page-section.active { display: block; animation: fadeIn 0.25s ease-in-out; }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(4px); }
      to { opacity: 1; transform: translateY(0); }
    }

    /* Common Buttons */
    .btn-mo-orange {
      background: linear-gradient(135deg, #ea580c, #f97316);
      color: #fff;
      font-weight: 600;
      padding: 10px 20px;
      border-radius: var(--radius-sm);
      display: inline-flex;
      align-items: center;
      gap: 8px;
      box-shadow: 0 4px 14px rgba(234, 88, 12, 0.3);
    }
    .btn-mo-orange:hover {
      background: linear-gradient(135deg, #c2410c, #ea580c);
      transform: translateY(-1px);
      box-shadow: 0 6px 18px rgba(234, 88, 12, 0.4);
    }
    .btn-mo-primary {
      background: var(--mo-primary);
      color: #fff;
      font-weight: 600;
      padding: 10px 20px;
      border-radius: var(--radius-sm);
      display: inline-flex;
      align-items: center;
      gap: 8px;
    }
    .btn-mo-primary:hover {
      background: var(--mo-primary-light);
    }
    .btn-mo-outline {
      border: 1px solid var(--mo-border);
      background: #fff;
      color: var(--mo-text-main);
      font-weight: 600;
      padding: 9px 18px;
      border-radius: var(--radius-sm);
      display: inline-flex;
      align-items: center;
      gap: 8px;
    }
    .btn-mo-outline:hover {
      border-color: var(--mo-primary);
      color: var(--mo-primary);
      background: #f1f5f9;
    }

    /* ==========================================================================
       2. MAISON OFFICE PUBLIC HEADER & TOPBAR
       ========================================================================== */
    .mo-topbar {
      background-color: var(--mo-primary-dark);
      color: #cbd5e1;
      font-size: 12.5px;
      border-bottom: 1px solid rgba(255,255,255,0.08);
      padding: 7px 0;
    }
    .mo-topbar .container {
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
      gap: 10px;
    }
    .mo-topbar-left, .mo-topbar-right {
      display: flex;
      align-items: center;
      gap: 18px;
    }
    .mo-topbar a { color: #cbd5e1; display: inline-flex; align-items: center; gap: 6px; }
    .mo-topbar a:hover { color: #f97316; }
    .mo-topbar strong { color: #fdba74; }
    .role-switcher-badge {
      background: rgba(234, 88, 12, 0.2);
      color: #fdba74;
      border: 1px solid rgba(234, 88, 12, 0.4);
      padding: 2px 8px;
      border-radius: 4px;
      font-size: 11px;
      font-weight: 600;
    }

    /* Main Navbar */
    .mo-header {
      background: #ffffff;
      box-shadow: 0 2px 10px rgba(0,0,0,0.06);
      position: sticky;
      top: 0;
      z-index: 1000;
    }
    .mo-header .container {
      display: flex;
      align-items: center;
      justify-content: space-between;
      height: 74px;
    }
    .mo-logo {
      display: flex;
      align-items: center;
      gap: 12px;
      cursor: pointer;
    }
    .mo-logo-icon {
      width: 42px;
      height: 42px;
      background: linear-gradient(135deg, var(--mo-primary), #1e40af);
      color: #fff;
      display: flex;
      align-items: center;
      justify-content: center;
      border-radius: 10px;
      font-size: 20px;
      box-shadow: 0 4px 10px rgba(15, 51, 97, 0.25);
    }
    .mo-logo-text h1 {
      font-size: 20px;
      font-weight: 900;
      color: var(--mo-primary);
      line-height: 1.1;
      letter-spacing: -0.5px;
    }
    .mo-logo-text span {
      font-size: 11px;
      font-weight: 600;
      color: var(--mo-orange);
      text-transform: uppercase;
      letter-spacing: 0.8px;
    }

    .mo-nav {
      display: flex;
      align-items: center;
      gap: 24px;
    }
    .mo-nav-item {
      font-size: 14px;
      font-weight: 600;
      color: #334155;
      padding: 8px 4px;
      position: relative;
      cursor: pointer;
      display: flex;
      align-items: center;
      gap: 5px;
    }
    .mo-nav-item:hover, .mo-nav-item.active {
      color: var(--mo-orange);
    }
    .mo-nav-item.active::after {
      content: '';
      position: absolute;
      bottom: -14px;
      left: 0;
      right: 0;
      height: 3px;
      background: var(--mo-orange);
      border-radius: 2px;
    }

    .mo-header-actions {
      display: flex;
      align-items: center;
      gap: 12px;
    }
    .portal-dropdown-btn {
      background: #f1f5f9;
      color: var(--mo-primary);
      border: 1px solid #cbd5e1;
      padding: 8px 14px;
      border-radius: var(--radius-sm);
      font-size: 13px;
      font-weight: 600;
      display: flex;
      align-items: center;
      gap: 8px;
    }
    .portal-dropdown-btn:hover {
      background: #e2e8f0;
      border-color: #94a3b8;
    }

    /* ==========================================================================
       3. MAISON OFFICE PUBLIC HERO & SEARCH FILTER (Trang Cho thuê VP TPHCM)
       ========================================================================== */
    .mo-hero-banner {
      background: linear-gradient(180deg, #0f2b4c 0%, #091a2f 100%);
      color: #ffffff;
      padding: 40px 0 75px;
      position: relative;
      overflow: hidden;
    }
    .mo-hero-banner::before {
      content: '';
      position: absolute;
      top: 0; left: 0; right: 0; bottom: 0;
      background: url('https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?auto=format&fit=crop&w=1800&q=80') center/cover no-repeat;
      opacity: 0.12;
      pointer-events: none;
    }
    .mo-breadcrumbs {
      display: flex;
      align-items: center;
      gap: 8px;
      font-size: 13px;
      color: #94a3b8;
      margin-bottom: 18px;
    }
    .mo-breadcrumbs a { color: #cbd5e1; }
    .mo-breadcrumbs a:hover { color: #f97316; }
    .mo-hero-title {
      font-size: 32px;
      font-weight: 800;
      color: #ffffff;
      line-height: 1.25;
      margin-bottom: 12px;
      max-width: 900px;
    }
    .mo-hero-desc {
      font-size: 15px;
      color: #cbd5e1;
      max-width: 820px;
      line-height: 1.6;
    }

    /* Maison Office Floating Filter Box */
    .mo-filter-container {
      margin-top: -45px;
      position: relative;
      z-index: 20;
      margin-bottom: 35px;
    }
    .mo-filter-box {
      background: #ffffff;
      border-radius: var(--radius-lg);
      padding: 24px;
      box-shadow: var(--shadow-xl);
      border: 1px solid var(--mo-border);
    }
    .mo-filter-tabs {
      display: flex;
      gap: 12px;
      border-bottom: 1px solid #f1f5f9;
      padding-bottom: 14px;
      margin-bottom: 18px;
    }
    .mo-filter-tab {
      padding: 6px 14px;
      border-radius: 6px;
      font-size: 13.5px;
      font-weight: 600;
      color: #64748b;
      cursor: pointer;
      display: flex;
      align-items: center;
      gap: 6px;
    }
    .mo-filter-tab.active {
      background: var(--mo-orange-light);
      color: var(--mo-orange);
    }
    .mo-filter-grid {
      display: grid;
      grid-template-columns: 1.8fr 1.2fr 1fr 1fr 1fr auto;
      gap: 12px;
      align-items: end;
    }
    .mo-filter-group {
      display: flex;
      flex-direction: column;
      gap: 6px;
    }
    .mo-filter-label {
      font-size: 12.5px;
      font-weight: 700;
      color: #334155;
      display: flex;
      align-items: center;
      gap: 6px;
    }
    .mo-input-wrap {
      position: relative;
    }
    .mo-input-wrap i {
      position: absolute;
      left: 12px;
      top: 50%;
      transform: translateY(-50%);
      color: #94a3b8;
      font-size: 14px;
    }
    .mo-input-wrap input, .mo-filter-select {
      width: 100%;
      height: 44px;
      border: 1px solid #cbd5e1;
      border-radius: var(--radius-sm);
      padding: 0 12px 0 36px;
      font-size: 14px;
      color: var(--mo-text-main);
      background: #ffffff;
      transition: all 0.2s;
    }
    .mo-filter-select {
      padding-left: 12px;
      cursor: pointer;
    }
    .mo-input-wrap input:focus, .mo-filter-select:focus {
      border-color: var(--mo-blue-accent);
      box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
    }
    .btn-filter-submit {
      height: 44px;
      padding: 0 24px;
      background: linear-gradient(135deg, #ea580c, #f97316);
      color: white;
      font-weight: 700;
      font-size: 14px;
      border-radius: var(--radius-sm);
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      box-shadow: 0 4px 12px rgba(234, 88, 12, 0.35);
    }
    .btn-filter-submit:hover {
      background: linear-gradient(135deg, #c2410c, #ea580c);
    }

    /* Quick Filter Chips */
    .mo-quick-tags {
      display: flex;
      align-items: center;
      gap: 8px;
      flex-wrap: wrap;
      margin-top: 14px;
      padding-top: 14px;
      border-top: 1px dashed #e2e8f0;
      font-size: 13px;
    }
    .mo-quick-tag-label {
      font-weight: 600;
      color: #64748b;
      margin-right: 4px;
    }
    .mo-chip {
      background: #f1f5f9;
      border: 1px solid #e2e8f0;
      color: #475569;
      padding: 5px 12px;
      border-radius: 20px;
      font-size: 12.5px;
      font-weight: 500;
      cursor: pointer;
      transition: all 0.15s;
    }
    .mo-chip:hover, .mo-chip.active {
      background: #eff6ff;
      border-color: #93c5fd;
      color: #1d4ed8;
      font-weight: 600;
    }

    /* Market Highlights Counter */
    .mo-stats-strip {
      background: #ffffff;
      border: 1px solid var(--mo-border);
      border-radius: var(--radius-md);
      padding: 16px 24px;
      margin-bottom: 28px;
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 16px;
      text-align: center;
    }
    .mo-stat-item {
      display: flex;
      flex-direction: column;
      align-items: center;
    }
    .mo-stat-num {
      font-size: 24px;
      font-weight: 800;
      color: var(--mo-primary);
    }
    .mo-stat-label {
      font-size: 13px;
      color: #64748b;
      font-weight: 500;
    }

    /* ==========================================================================
       4. LISTINGS SECTION (MAISON OFFICE STYLE)
       ========================================================================== */
    .mo-content-grid {
      display: grid;
      grid-template-columns: 290px 1fr;
      gap: 28px;
      margin-bottom: 50px;
    }

    /* Sidebar Filters */
    .mo-sidebar-card {
      background: #ffffff;
      border: 1px solid var(--mo-border);
      border-radius: var(--radius-md);
      padding: 20px;
      margin-bottom: 20px;
    }
    .mo-sidebar-title {
      font-size: 16px;
      font-weight: 700;
      color: var(--mo-primary);
      margin-bottom: 14px;
      padding-bottom: 10px;
      border-bottom: 1px solid #f1f5f9;
      display: flex;
      align-items: center;
      gap: 8px;
    }
    .mo-filter-list {
      display: flex;
      flex-direction: column;
      gap: 8px;
    }
    .mo-filter-item {
      display: flex;
      align-items: center;
      justify-content: space-between;
      font-size: 13.5px;
      color: #475569;
      cursor: pointer;
      padding: 4px 0;
    }
    .mo-filter-item:hover { color: var(--mo-orange); }
    .mo-filter-item input { margin-right: 8px; }
    .mo-filter-count {
      background: #f1f5f9;
      color: #64748b;
      font-size: 11.5px;
      padding: 2px 7px;
      border-radius: 10px;
      font-weight: 600;
    }

    .mo-consult-banner {
      background: linear-gradient(135deg, var(--mo-primary-dark), var(--mo-primary));
      color: #ffffff;
      border-radius: var(--radius-md);
      padding: 24px;
      text-align: center;
    }
    .mo-consult-banner h4 {
      font-size: 16px;
      font-weight: 700;
      margin-bottom: 8px;
    }
    .mo-consult-banner p {
      font-size: 13px;
      color: #cbd5e1;
      margin-bottom: 16px;
    }
    .mo-consult-hotline {
      font-size: 20px;
      font-weight: 800;
      color: #fdba74;
      display: block;
      margin-bottom: 14px;
    }

    /* Main Listings Content */
    .mo-listings-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 18px;
      flex-wrap: wrap;
      gap: 12px;
    }
    .mo-listings-title h2 {
      font-size: 22px;
      font-weight: 800;
      color: var(--mo-primary);
    }
    .mo-listings-title p {
      font-size: 13.5px;
      color: #64748b;
    }
    .mo-sort-controls {
      display: flex;
      align-items: center;
      gap: 12px;
    }
    .mo-sort-select {
      height: 38px;
      border: 1px solid #cbd5e1;
      border-radius: var(--radius-sm);
      padding: 0 10px;
      font-size: 13.5px;
      color: #334155;
      background: #fff;
    }

    /* Building Cards (Maison Office Spec) */
    .mo-building-grid {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 20px;
    }
    .mo-building-card {
      background: #ffffff;
      border: 1px solid var(--mo-border);
      border-radius: var(--radius-md);
      overflow: hidden;
      box-shadow: var(--shadow-sm);
      transition: all 0.25s ease;
      display: flex;
      flex-direction: column;
    }
    .mo-building-card:hover {
      transform: translateY(-4px);
      box-shadow: var(--shadow-lg);
      border-color: #cbd5e1;
    }
    .mo-card-thumb {
      height: 200px;
      position: relative;
      overflow: hidden;
      background-color: #0f172a;
    }
    .mo-card-thumb img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      transition: transform 0.4s ease;
    }
    .mo-building-card:hover .mo-card-thumb img {
      transform: scale(1.05);
    }
    .mo-thumb-badges {
      position: absolute;
      top: 12px;
      left: 12px;
      display: flex;
      gap: 6px;
    }
    .mo-badge-grade {
      background: rgba(15, 51, 97, 0.9);
      backdrop-filter: blur(4px);
      color: #fff;
      font-size: 11.5px;
      font-weight: 700;
      padding: 4px 9px;
      border-radius: 4px;
      letter-spacing: 0.5px;
    }
    .mo-badge-status {
      font-size: 11.5px;
      font-weight: 700;
      padding: 4px 9px;
      border-radius: 4px;
    }
    .mo-badge-status.empty {
      background: #dcfce7;
      color: #15803d;
      border: 1px solid #bbf7d0;
    }
    .mo-badge-status.soon {
      background: #fef9c3;
      color: #a16207;
      border: 1px solid #fef08a;
    }

    .mo-card-body {
      padding: 18px;
      display: flex;
      flex-direction: column;
      flex: 1;
    }
    .mo-card-district {
      font-size: 12px;
      font-weight: 700;
      color: var(--mo-orange);
      text-transform: uppercase;
      letter-spacing: 0.5px;
      margin-bottom: 4px;
    }
    .mo-card-name {
      font-size: 18px;
      font-weight: 800;
      color: var(--mo-primary);
      margin-bottom: 6px;
      line-height: 1.3;
    }
    .mo-card-address {
      font-size: 13px;
      color: #64748b;
      margin-bottom: 14px;
      display: flex;
      align-items: center;
      gap: 6px;
    }
    .mo-card-specs {
      background: #f8fafc;
      border-radius: var(--radius-sm);
      padding: 10px 12px;
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 8px;
      margin-bottom: 14px;
      font-size: 12.5px;
    }
    .mo-spec-item {
      display: flex;
      align-items: center;
      gap: 6px;
      color: #475569;
    }
    .mo-spec-item i {
      color: #0284c7;
      width: 14px;
    }
    .mo-card-pricing {
      margin-top: auto;
      padding-top: 12px;
      border-top: 1px solid #f1f5f9;
      display: flex;
      justify-content: space-between;
      align-items: flex-end;
    }
    .mo-price-box .price-usd {
      font-size: 20px;
      font-weight: 800;
      color: #ea580c;
    }
    .mo-price-box .price-unit {
      font-size: 12px;
      color: #64748b;
      font-weight: normal;
    }
    .mo-price-box .price-vnd {
      font-size: 11.5px;
      color: #94a3b8;
    }
    .mo-card-actions {
      display: flex;
      gap: 8px;
    }
    .btn-card-quote {
      background: var(--mo-orange-light);
      color: var(--mo-orange);
      border: 1px solid rgba(234, 88, 12, 0.3);
      padding: 7px 12px;
      border-radius: var(--radius-sm);
      font-size: 12.5px;
      font-weight: 700;
    }
    .btn-card-quote:hover {
      background: var(--mo-orange);
      color: #fff;
    }
    .btn-card-view {
      background: var(--mo-primary);
      color: #fff;
      padding: 7px 12px;
      border-radius: var(--radius-sm);
      font-size: 12.5px;
      font-weight: 700;
    }
    .btn-card-view:hover {
      background: var(--mo-primary-light);
    }

    /* ==========================================================================
       5. BUILDING DETAIL PAGE (Theo đề tài: mặt bằng, diện tích, bản đồ, booking)
       ========================================================================== */
    .detail-hero-grid {
      display: grid;
      grid-template-columns: 1.4fr 1fr;
      gap: 28px;
      margin-bottom: 30px;
    }
    .detail-gallery {
      display: grid;
      grid-template-rows: 320px 90px;
      gap: 12px;
    }
    .detail-main-img {
      border-radius: var(--radius-md);
      overflow: hidden;
      box-shadow: var(--shadow-md);
    }
    .detail-main-img img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }
    .detail-sub-thumbs {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 10px;
    }
    .detail-sub-thumbs img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      border-radius: 6px;
      cursor: pointer;
      border: 2px solid transparent;
    }
    .detail-sub-thumbs img.active {
      border-color: var(--mo-orange);
    }

    .detail-info-card {
      background: #ffffff;
      border: 1px solid var(--mo-border);
      border-radius: var(--radius-md);
      padding: 24px;
      box-shadow: var(--shadow-sm);
    }
    .detail-price-highlight {
      background: var(--mo-orange-light);
      border-left: 4px solid var(--mo-orange);
      padding: 12px 16px;
      margin: 16px 0;
      border-radius: 0 var(--radius-sm) var(--radius-sm) 0;
    }
    .detail-specs-table {
      width: 100%;
      border-collapse: collapse;
      font-size: 13.5px;
      margin: 18px 0;
    }
    .detail-specs-table td {
      padding: 8px 6px;
      border-bottom: 1px solid #f1f5f9;
    }
    .detail-specs-table td:first-child {
      color: #64748b;
      width: 40%;
      font-weight: 500;
    }
    .detail-specs-table td:last-child {
      font-weight: 600;
      color: #1e293b;
    }

    /* Table Mặt Bằng Cắt Thuê Theo Tầng (Yêu cầu trọng tâm của đề tài) */
    .detail-floors-card {
      background: #ffffff;
      border: 1px solid var(--mo-border);
      border-radius: var(--radius-md);
      padding: 24px;
      margin-bottom: 30px;
    }
    .mo-table {
      width: 100%;
      border-collapse: collapse;
      text-align: left;
      font-size: 14px;
      margin-top: 14px;
    }
    .mo-table th {
      background: #f8fafc;
      color: #475569;
      font-weight: 700;
      font-size: 12.5px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      padding: 12px 14px;
      border-bottom: 2px solid #e2e8f0;
    }
    .mo-table td {
      padding: 14px;
      border-bottom: 1px solid #f1f5f9;
      color: #334155;
    }
    .mo-table tr:hover td {
      background: #f8fafc;
    }

    /* Mock Google Map Container */
    .detail-map-mock {
      height: 280px;
      background: #e2e8f0;
      border-radius: var(--radius-md);
      overflow: hidden;
      position: relative;
      margin-top: 16px;
      display: flex;
      align-items: center;
      justify-content: center;
      border: 1px solid var(--mo-border);
    }
    .detail-map-mock iframe {
      width: 100%;
      height: 100%;
      border: 0;
    }

    /* ==========================================================================
       6. LEAD & QUOTE FORM (Phân hệ tiếp nhận Báo giá / Khảo sát)
       ========================================================================== */
    .lead-form-wrap {
      max-width: 840px;
      margin: 0 auto 50px;
      background: #ffffff;
      border: 1px solid var(--mo-border);
      border-radius: var(--radius-lg);
      padding: 36px;
      box-shadow: var(--shadow-lg);
    }
    .lead-form-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 18px;
      margin-top: 20px;
    }
    .form-field {
      display: flex;
      flex-direction: column;
      gap: 6px;
    }
    .form-field.full {
      grid-column: 1 / -1;
    }
    .form-field label {
      font-size: 13.5px;
      font-weight: 700;
      color: #334155;
    }
    .form-field input, .form-field select, .form-field textarea {
      width: 100%;
      padding: 11px 14px;
      border: 1px solid #cbd5e1;
      border-radius: var(--radius-sm);
      font-size: 14px;
      background: #fff;
    }
    .form-field input:focus, .form-field select:focus, .form-field textarea:focus {
      border-color: var(--mo-orange);
      box-shadow: 0 0 0 3px rgba(234, 88, 12, 0.15);
    }

    /* ==========================================================================
       7. FILAMENT PHP INSPIRED ADMIN PANEL (FOR ADMIN, SALES & TENANT WORKSPACES)
       ========================================================================== */
    .filament-shell {
      display: flex;
      min-height: 100vh;
      background-color: #f8fafc;
      font-size: 14px;
    }

    /* Filament Sidebar */
    .filament-sidebar {
      width: 270px;
      background-color: var(--fl-sidebar);
      color: #94a3b8;
      display: flex;
      flex-direction: column;
      flex-shrink: 0;
      border-right: 1px solid #1e293b;
      position: sticky;
      top: 0;
      height: 100vh;
      overflow-y: auto;
      z-index: 50;
    }
    .filament-brand {
      padding: 20px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      border-bottom: 1px solid #1e293b;
    }
    .filament-brand-logo {
      display: flex;
      align-items: center;
      gap: 10px;
      color: #ffffff;
      font-weight: 800;
      font-size: 17px;
    }
    .filament-badge-role {
      background: #f59e0b;
      color: #000;
      font-size: 10px;
      font-weight: 800;
      padding: 2px 6px;
      border-radius: 4px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    .filament-nav-section {
      padding: 16px 12px 6px;
    }
    .filament-section-heading {
      font-size: 11px;
      font-weight: 700;
      color: #64748b;
      text-transform: uppercase;
      letter-spacing: 1px;
      padding: 0 10px 8px;
    }
    .filament-link {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 12px;
      padding: 9px 12px;
      border-radius: 8px;
      color: #cbd5e1;
      font-weight: 500;
      font-size: 13.5px;
      cursor: pointer;
      margin-bottom: 2px;
      transition: all 0.15s;
    }
    .filament-link-content {
      display: flex;
      align-items: center;
      gap: 12px;
    }
    .filament-link i {
      font-size: 16px;
      width: 20px;
      text-align: center;
      color: #94a3b8;
    }
    .filament-link:hover {
      background: var(--fl-sidebar-hover);
      color: #ffffff;
    }
    .filament-link:hover i { color: #f59e0b; }
    .filament-link.active {
      background: #2563eb;
      color: #ffffff;
      font-weight: 600;
    }
    .filament-link.active i {
      color: #ffffff;
    }
    .filament-nav-badge {
      font-size: 11px;
      padding: 2px 7px;
      border-radius: 999px;
      font-weight: 700;
    }

    /* Filament Main Area */
    .filament-main {
      flex: 1;
      display: flex;
      flex-direction: column;
      overflow-x: hidden;
    }
    .filament-topbar {
      height: 64px;
      background: #ffffff;
      border-bottom: 1px solid #e2e8f0;
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0 28px;
      position: sticky;
      top: 0;
      z-index: 40;
    }
    .filament-search-mock {
      display: flex;
      align-items: center;
      gap: 8px;
      background: #f1f5f9;
      border: 1px solid #e2e8f0;
      padding: 7px 14px;
      border-radius: 8px;
      width: 320px;
      font-size: 13px;
      color: #64748b;
    }
    .filament-search-mock kbd {
      background: #ffffff;
      border: 1px solid #cbd5e1;
      border-radius: 4px;
      padding: 1px 5px;
      font-size: 10.5px;
      margin-left: auto;
    }
    .filament-topbar-actions {
      display: flex;
      align-items: center;
      gap: 16px;
    }
    .role-selector-pill {
      display: flex;
      align-items: center;
      gap: 8px;
      background: #eff6ff;
      border: 1px solid #bfdbfe;
      padding: 5px 12px;
      border-radius: 20px;
      font-size: 12.5px;
      color: #1e40af;
      font-weight: 600;
    }
    .role-selector-pill select {
      background: transparent;
      border: none;
      font-weight: 700;
      color: #1d4ed8;
      cursor: pointer;
    }
    .filament-user-pill {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 4px 8px;
      border-radius: 8px;
      cursor: pointer;
    }
    .filament-avatar {
      width: 36px;
      height: 36px;
      border-radius: 50%;
      background: linear-gradient(135deg, #0f3361, #2563eb);
      color: #fff;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 700;
      font-size: 14px;
    }

    /* Filament Workspace Body */
    .filament-body {
      padding: 28px;
      flex: 1;
    }
    .filament-page-header {
      display: flex;
      justify-content: space-between;
      align-items: flex-end;
      margin-bottom: 24px;
    }
    .filament-breadcrumbs {
      font-size: 12.5px;
      color: #64748b;
      margin-bottom: 6px;
    }
    .filament-page-title {
      font-size: 24px;
      font-weight: 800;
      color: #0f172a;
    }

    /* Filament Widgets (Stat Cards) */
    .filament-widgets-grid {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 18px;
      margin-bottom: 24px;
    }
    .filament-stat-card {
      background: #ffffff;
      border: 1px solid #e2e8f0;
      border-radius: 12px;
      padding: 20px;
      box-shadow: var(--shadow-sm);
      display: flex;
      flex-direction: column;
      justify-content: space-between;
    }
    .filament-stat-label {
      font-size: 13px;
      font-weight: 600;
      color: #64748b;
      margin-bottom: 8px;
    }
    .filament-stat-value {
      font-size: 28px;
      font-weight: 800;
      color: #0f172a;
      line-height: 1.1;
      margin-bottom: 10px;
    }
    .filament-stat-trend {
      display: flex;
      align-items: center;
      gap: 6px;
      font-size: 12.5px;
      font-weight: 600;
    }
    .trend-up { color: #10b981; }
    .trend-down { color: #ef4444; }

    /* Filament Tables */
    .filament-card {
      background: #ffffff;
      border: 1px solid #e2e8f0;
      border-radius: 12px;
      box-shadow: var(--shadow-sm);
      overflow: hidden;
      margin-bottom: 24px;
    }
    .filament-card-header {
      padding: 16px 20px;
      border-bottom: 1px solid #f1f5f9;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    .filament-card-title {
      font-size: 16px;
      font-weight: 700;
      color: #0f172a;
    }
    .filament-card-actions {
      display: flex;
      align-items: center;
      gap: 10px;
    }
    .filament-input-sm {
      padding: 6px 12px;
      border: 1px solid #cbd5e1;
      border-radius: 6px;
      font-size: 13px;
    }

    /* Filament Badges */
    .badge-filament {
      font-size: 11.5px;
      font-weight: 700;
      padding: 3px 8px;
      border-radius: 6px;
      display: inline-flex;
      align-items: center;
      gap: 4px;
    }
    .badge-filament.success { background: #dcfce7; color: #15803d; }
    .badge-filament.warning { background: #fef3c7; color: #b45309; }
    .badge-filament.danger { background: #fee2e2; color: #b91c1c; }
    .badge-filament.info { background: #e0f2fe; color: #0369a1; }

    /* ==========================================================================
       8. MAISON OFFICE FOOTER (Chuẩn BĐS cao cấp & thông tin đề tài)
       ========================================================================== */
    .mo-footer {
      background-color: var(--mo-primary-dark);
      color: #94a3b8;
      font-size: 13.5px;
      padding: 50px 0 20px;
      border-top: 1px solid rgba(255,255,255,0.06);
    }
    .mo-footer-grid {
      display: grid;
      grid-template-columns: 1.5fr 1fr 1fr 1.2fr;
      gap: 36px;
      margin-bottom: 40px;
    }
    .mo-footer-col h4 {
      color: #ffffff;
      font-size: 16px;
      font-weight: 700;
      margin-bottom: 18px;
      position: relative;
    }
    .mo-footer-col h4::after {
      content: '';
      position: absolute;
      left: 0;
      bottom: -6px;
      width: 32px;
      height: 2px;
      background: var(--mo-orange);
    }
    .mo-footer-col ul {
      display: flex;
      flex-direction: column;
      gap: 9px;
    }
    .mo-footer-col a { color: #cbd5e1; }
    .mo-footer-col a:hover { color: #f97316; padding-left: 3px; }
    .mo-footer-bottom {
      border-top: 1px solid rgba(255,255,255,0.08);
      padding-top: 20px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
      gap: 14px;
      font-size: 12.5px;
    }
    .mo-project-tag {
      background: rgba(255,255,255,0.08);
      padding: 4px 10px;
      border-radius: 4px;
      color: #fdba74;
      font-size: 11.5px;
    }

    /* Modal Styling */
    .mo-modal {
      display: none;
      position: fixed;
      top: 0; left: 0; right: 0; bottom: 0;
      background: rgba(15, 23, 42, 0.7);
      backdrop-filter: blur(4px);
      z-index: 2000;
      align-items: center;
      justify-content: center;
      padding: 20px;
    }
    .mo-modal.show { display: flex; animation: fadeIn 0.2s ease; }
    .mo-modal-box {
      background: #ffffff;
      border-radius: var(--radius-lg);
      max-width: 600px;
      width: 100%;
      overflow: hidden;
      box-shadow: var(--shadow-xl);
    }
    .mo-modal-head {
      background: var(--mo-primary);
      color: #fff;
      padding: 18px 24px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    .mo-modal-body {
      padding: 24px;
    }

    /* Responsive */
    @media (max-width: 1024px) {
      .mo-filter-grid { grid-template-columns: 1fr 1fr; }
      .mo-content-grid { grid-template-columns: 1fr; }
      .mo-building-grid { grid-template-columns: 1fr; }
      .filament-widgets-grid { grid-template-columns: 1fr 1fr; }
      .mo-footer-grid { grid-template-columns: 1fr 1fr; }
      .detail-hero-grid { grid-template-columns: 1fr; }
    }
    @media (max-width: 768px) {
      .mo-nav { display: none; }
      .mo-topbar-right { display: none; }
      .filament-sidebar { display: none; }
      .filament-widgets-grid { grid-template-columns: 1fr; }
      .mo-stats-strip { grid-template-columns: 1fr 1fr; }
      .lead-form-grid { grid-template-columns: 1fr; }
      .mo-footer-grid { grid-template-columns: 1fr; }
    }
  </style>
</head>
<body>

  <!-- ==========================================================================
       VIEW A: MAISON OFFICE PUBLIC PORTAL (CỔNG THÔNG TIN CÔNG CỘNG)
       ========================================================================== -->
  <div id="portal-wrapper">
    

    <!-- Main Header -->
    <header class="mo-header">
      <div class="container">
        <div class="mo-logo" onclick="showPublicPage('page-home')">
          <div class="mo-logo-icon">
            <i class="fa-solid fa-building-wheat"></i>
          </div>
          <div class="mo-logo-text">
            <h1>Office</h1>
          </div>
        </div>

        <nav class="mo-nav">
          <div class="mo-nav-item active" onclick="showPublicPage('page-home')">Cho Thuê VP TP.HCM</div>
          <div class="mo-nav-item" onclick="filterByDistrict('Quận 1')">Văn Phòng Quận 1</div>
          <div class="mo-nav-item" onclick="filterByDistrict('Quận 3')">Văn Phòng Quận 3</div>
          <div class="mo-nav-item" onclick="filterByGrade('Hạng A')">Tòa Nhà Hạng A</div>
          <div class="mo-nav-item" onclick="showPublicPage('page-quote')">Nhận Báo Giá</div>
        </nav>

        <div class="mo-header-actions">
          <button class="btn-mo-orange" onclick="openQuoteModal()">
            <i class="fa-solid fa-file-invoice-dollar"></i> Nhận Báo Giá Nhanh
          </button>
          <button class="btn-mo-outline" onclick="switchView('admin-view')">
            <i class="fa-solid fa-desktop"></i> Portal 4 Tác Nhân
          </button>
          <a href="/admin" class="btn-mo-primary" style="background:#2563eb; color:#fff; padding:8px 14px; border-radius:6px; font-weight:600; text-decoration:none; display:inline-flex; align-items:center; gap:6px;">
            <i class="fa-solid fa-shield-halved"></i> Filament Admin
          </a>
          <!-- Quick Role Selector Dropdown -->
          <div class="role-selector-pill">
            <i class="fa-solid fa-user-gear"></i>
            <select onchange="handleRoleSwitch(this.value)">
              <option value="portal" selected>Khách vãng lai</option>
              <option value="tenant">Khách thuê (Doanh nghiệp)</option>
              <option value="sales">Nhân viên Sales (Môi giới)</option>
              <option value="admin">Ban Quản Lý & Admin</option>
            </select>
          </div>
        </div>
      </div>
    </header>

    <!-- PAGE 1: HOME LISTING (Tương đương maisonoffice.vn/cho-thue-van-phong-tphcm/) -->
    <main id="page-home" class="page-section active">
      <!-- Hero Banner -->
      <section class="mo-hero-banner">
        <div class="container">
          <div class="mo-breadcrumbs">
            <a href="#">Trang chủ</a>
            <i class="fa-solid fa-chevron-right" style="font-size:10px;"></i>
            <span>Cho thuê văn phòng TP.HCM</span>
          </div>
          <h1 class="mo-hero-title">Cho Thuê Văn Phòng TP.HCM - Cập Nhật Báo Giá & Diện Tích Mới Nhất 2026</h1>
          <p class="mo-hero-desc">Hệ thống khảo sát và phân phối mặt bằng văn phòng chuyên nghiệp tại TP. Hồ Chí Minh. Dữ liệu thực tế hơn 2.100+ cao ốc từ Hạng A, B, C và Giá rẻ. Tư vấn miễn phí và hỗ trợ thương lượng giá tốt nhất cho doanh nghiệp.</p>
        </div>
      </section>

      <!-- Advanced Filter Floating Box -->
      <div class="container mo-filter-container">
        <div class="mo-filter-box">
          <div class="mo-filter-tabs">
            <div class="mo-filter-tab active"><i class="fa-solid fa-building"></i> Cho Thuê Văn Phòng</div>
            <div class="mo-filter-tab" onclick="alert('Đã chọn phân hệ Văn Phòng Trọn Gói / Coworking Space!')"><i class="fa-solid fa-briefcase"></i> Văn Phòng Trọn Gói</div>
            <div class="mo-filter-tab" onclick="alert('Đã chọn phân hệ Tòa Nhà Nguyên Căn!')"><i class="fa-solid fa-city"></i> Tòa Nhà Nguyên Căn</div>
          </div>

          <div class="mo-filter-grid">
            <!-- Search Text -->
            <div class="mo-filter-group">
              <label class="mo-filter-label"><i class="fa-solid fa-magnifying-glass"></i> Tên tòa nhà hoặc đường</label>
              <div class="mo-input-wrap">
                <i class="fa-solid fa-search"></i>
                <input type="text" id="filter-keyword" placeholder="Ví dụ: Vietcombank, Landmark, Lê Duẩn..." oninput="applyFilters()">
              </div>
            </div>

            <!-- District -->
            <div class="mo-filter-group">
              <label class="mo-filter-label"><i class="fa-solid fa-location-dot"></i> Khu vực (Quận)</label>
              <select id="filter-district" class="mo-filter-select" onchange="applyFilters()">
                <option value="all">Tất cả Quận / Huyện</option>
                <option value="Quận 1">Quận 1</option>
                <option value="Quận 3">Quận 3</option>
                <option value="Quận 7">Quận 7</option>
                <option value="Quận 10">Quận 10</option>
                <option value="Bình Thạnh">Bình Thạnh</option>
                <option value="Phú Nhuận">Phú Nhuận</option>
                <option value="TP Thủ Đức">TP. Thủ Đức</option>
              </select>
            </div>

            <!-- Grade -->
            <div class="mo-filter-group">
              <label class="mo-filter-label"><i class="fa-solid fa-medal"></i> Hạng văn phòng</label>
              <select id="filter-grade" class="mo-filter-select" onchange="applyFilters()">
                <option value="all">Tất cả hạng</option>
                <option value="Hạng A">Hạng A / A+</option>
                <option value="Hạng B">Hạng B / B+</option>
                <option value="Hạng C">Hạng C / Giá rẻ</option>
              </select>
            </div>

            <!-- Price -->
            <div class="mo-filter-group">
              <label class="mo-filter-label"><i class="fa-solid fa-dollar-sign"></i> Khoảng giá ($/m²)</label>
              <select id="filter-price" class="mo-filter-select" onchange="applyFilters()">
                <option value="all">Tất cả mức giá</option>
                <option value="under20">Dưới $20 / m²</option>
                <option value="20to35">$20 - $35 / m²</option>
                <option value="over35">Trên $35 / m²</option>
              </select>
            </div>

            <!-- Area -->
            <div class="mo-filter-group">
              <label class="mo-filter-label"><i class="fa-solid fa-vector-square"></i> Diện tích</label>
              <select id="filter-area" class="mo-filter-select" onchange="applyFilters()">
                <option value="all">Tất cả diện tích</option>
                <option value="under150">Dưới 150 m²</option>
                <option value="150to500">150 - 500 m²</option>
                <option value="over500">Trên 500 m²</option>
              </select>
            </div>

            <!-- Submit Button -->
            <div>
              <button class="btn-filter-submit" onclick="applyFilters()">
                <i class="fa-solid fa-filter"></i> Lọc Tòa Nhà
              </button>
            </div>
          </div>

          <!-- Quick Filter Chips -->
          <div class="mo-quick-tags">
            <span class="mo-quick-tag-label"><i class="fa-solid fa-tags"></i> Gợi ý nhanh:</span>
            <button class="mo-chip" onclick="quickFilterDistrict('Quận 1')">Quận 1</button>
            <button class="mo-chip" onclick="quickFilterDistrict('Quận 3')">Quận 3</button>
            <button class="mo-chip" onclick="quickFilterDistrict('Bình Thạnh')">Bình Thạnh</button>
            <button class="mo-chip" onclick="quickFilterGrade('Hạng A')">Tòa nhà Hạng A</button>
            <button class="mo-chip" onclick="quickFilterStatus('Đang trống')">Sẵn sàng bàn giao (Đang trống)</button>
            <button class="mo-chip" onclick="quickFilterStatus('Sắp trống')">Sắp trống tháng tới</button>
            <button class="mo-chip" onclick="resetAllFilters()" style="color:#ef4444;"><i class="fa-solid fa-rotate-left"></i> Đặt lại</button>
          </div>
        </div>
      </div>

      <!-- Main Content Area -->
      <div class="container">
        <!-- Market Stats Strip -->
        <div class="mo-stats-strip">
          <div class="mo-stat-item">
            <span class="mo-stat-num">2.150+</span>
            <span class="mo-stat-label">Tòa nhà văn phòng TP.HCM</span>
          </div>
          <div class="mo-stat-item">
            <span class="mo-stat-num">145.000+ m²</span>
            <span class="mo-stat-label">Diện tích sàn đang chào thuê</span>
          </div>
          <div class="mo-stat-item">
            <span class="mo-stat-num">100%</span>
            <span class="mo-stat-label">Miễn phí dịch vụ tư vấn khách thuê</span>
          </div>
          <div class="mo-stat-item">
            <span class="mo-stat-num">8.500+</span>
            <span class="mo-stat-label">Doanh nghiệp đã đồng hành</span>
          </div>
        </div>

        <div class="mo-content-grid">
          <!-- Left Sidebar Filters & Lead Assistance -->
          <aside class="mo-sidebar">
            <div class="mo-sidebar-card">
              <h3 class="mo-sidebar-title"><i class="fa-solid fa-layer-group"></i> Phân theo Hạng VP</h3>
              <div class="mo-filter-list">
                <div class="mo-filter-item" onclick="quickFilterGrade('Hạng A')">
                  <span><i class="fa-solid fa-award" style="color:#f59e0b;"></i> Văn phòng Hạng A</span>
                  <span class="mo-filter-count">85</span>
                </div>
                <div class="mo-filter-item" onclick="quickFilterGrade('Hạng B')">
                  <span><i class="fa-solid fa-building" style="color:#3b82f6;"></i> Văn phòng Hạng B</span>
                  <span class="mo-filter-count">342</span>
                </div>
                <div class="mo-filter-item" onclick="quickFilterGrade('Hạng C')">
                  <span><i class="fa-regular fa-building" style="color:#10b981;"></i> Văn phòng Hạng C & Giá rẻ</span>
                  <span class="mo-filter-count">1.720</span>
                </div>
              </div>
            </div>

            <div class="mo-sidebar-card">
              <h3 class="mo-sidebar-title"><i class="fa-solid fa-map-location-dot"></i> Quận trung tâm nổi bật</h3>
              <div class="mo-filter-list">
                <div class="mo-filter-item" onclick="quickFilterDistrict('Quận 1')">
                  <span>Quận 1 (Trung tâm tài chính)</span>
                  <span class="mo-filter-count">480 tòa</span>
                </div>
                <div class="mo-filter-item" onclick="quickFilterDistrict('Quận 3')">
                  <span>Quận 3 (Khu ngoại giao)</span>
                  <span class="mo-filter-count">290 tòa</span>
                </div>
                <div class="mo-filter-item" onclick="quickFilterDistrict('Bình Thạnh')">
                  <span>Bình Thạnh (Cửa ngõ Đông)</span>
                  <span class="mo-filter-count">210 tòa</span>
                </div>
                <div class="mo-filter-item" onclick="quickFilterDistrict('Quận 7')">
                  <span>Quận 7 (Đô thị Phú Mỹ Hưng)</span>
                  <span class="mo-filter-count">145 tòa</span>
                </div>
                <div class="mo-filter-item" onclick="quickFilterDistrict('TP Thủ Đức')">
                  <span>TP. Thủ Đức (Khu công nghệ cao)</span>
                  <span class="mo-filter-count">180 tòa</span>
                </div>
              </div>
            </div>

            <!-- Direct Contact Box -->
            <div class="mo-consult-banner">
              <i class="fa-solid fa-headset" style="font-size:36px;color:#fdba74;margin-bottom:12px;"></i>
              <h4>Bạn cần báo giá toàn bộ tòa nhà phù hợp?</h4>
              <p>Chuyên viên sẽ gửi bảng so sánh chi tiết mặt bằng trống, giá thuê và phí quản lý trong vòng 30 phút.</p>
              <button class="btn-mo-orange" style="width:100%;justify-content:center;" onclick="openQuoteModal()">
                Gửi Yêu Cầu Báo Giá
              </button>
            </div>
          </aside>

          <!-- Right Content: Building Listings -->
          <div class="mo-listings-wrapper">
            <div class="mo-listings-header">
              <div class="mo-listings-title">
                <h2>Danh sách tòa nhà văn phòng cho thuê</h2>
                <p id="listing-count-text">Hiển thị 8 tòa nhà văn phòng tiêu biểu tại TP. Hồ Chí Minh</p>
              </div>
              <div class="mo-sort-controls">
                <span style="font-size:13px;color:#64748b;"><i class="fa-solid fa-arrow-down-wide-short"></i> Sắp xếp:</span>
                <select class="mo-sort-select" id="sort-selector" onchange="sortBuildings(this.value)">
                  <option value="featured">Đề xuất nổi bật</option>
                  <option value="price-asc">Giá từ thấp đến cao</option>
                  <option value="price-desc">Giá từ cao đến thấp</option>
                  <option value="area-desc">Diện tích sàn lớn nhất</option>
                </select>
              </div>
            </div>

            <!-- Building Cards Grid -->
            <div class="mo-building-grid" id="building-cards-container">
              <!-- Rendered via Javascript -->
            </div>
          </div>
        </div>
      </div>
    </main>

    <!-- PAGE 2: BUILDING DETAIL (Xem chi tiết thông số, mặt bằng cắt thuê, bản đồ) -->
    <main id="page-detail" class="page-section">
      <div class="container" style="padding-top: 24px; padding-bottom: 50px;">
        <div class="mo-breadcrumbs">
          <a href="#" onclick="showPublicPage('page-home')">Trang chủ</a>
          <i class="fa-solid fa-chevron-right" style="font-size:10px;"></i>
          <a href="#" onclick="showPublicPage('page-home')">Văn phòng cho thuê TP.HCM</a>
          <i class="fa-solid fa-chevron-right" style="font-size:10px;"></i>
          <span id="detail-breadcrumb-name">Vietcombank Tower</span>
        </div>

        <!-- Detail Hero Grid -->
        <div class="detail-hero-grid">
          <!-- Gallery -->
          <div class="detail-gallery">
            <div class="detail-main-img">
              <img id="detail-hero-img" src="https://images.unsplash.com/photo-1577495508048-b635879837f1?auto=format&fit=crop&w=1200&q=80" alt="Building">
            </div>
            <div class="detail-sub-thumbs">
              <img class="active" src="https://images.unsplash.com/photo-1577495508048-b635879837f1?auto=format&fit=crop&w=600&q=80" onclick="changeDetailImg(this.src)">
              <img src="https://images.unsplash.com/photo-1497366216548-37526070297c?auto=format&fit=crop&w=600&q=80" onclick="changeDetailImg(this.src)">
              <img src="https://images.unsplash.com/photo-1497366811353-6870744d04b2?auto=format&fit=crop&w=600&q=80" onclick="changeDetailImg(this.src)">
              <img src="https://images.unsplash.com/photo-1504384308090-c894fdcc538d?auto=format&fit=crop&w=600&q=80" onclick="changeDetailImg(this.src)">
            </div>
          </div>

          <!-- Quick Specs & Action -->
          <div class="detail-info-card">
            <span class="badge-filament info" id="detail-badge-grade"><i class="fa-solid fa-award"></i> Tòa nhà Hạng A</span>
            <span class="badge-filament success" id="detail-badge-status" style="margin-left: 6px;"><i class="fa-solid fa-circle-check"></i> Đang trống</span>
            <h1 id="detail-name" style="font-size: 26px; font-weight: 800; color: var(--mo-primary); margin-top: 10px;">Vietcombank Tower</h1>
            <p id="detail-address" style="font-size: 14px; color: #64748b; margin-top: 4px;"><i class="fa-solid fa-location-dot" style="color:#ea580c;"></i> Số 5 Công Trường Mê Linh, Phường Bến Nghé, Quận 1, TP.HCM</p>

            <div class="detail-price-highlight">
              <div style="font-size: 13px; color: #64748b; font-weight: 500;">Giá chào thuê chính thức:</div>
              <div style="font-size: 26px; font-weight: 800; color: #ea580c;" id="detail-price-text">$48 - $54 / m² / tháng</div>
              <div style="font-size: 12px; color: #94a3b8;">(Khoảng 1.180.000đ - 1.330.000đ/m² - Chưa bao gồm VAT & PQL)</div>
            </div>

            <!-- Specs Table -->
            <table class="detail-specs-table">
              <tr>
                <td><i class="fa-solid fa-layer-group"></i> Kết cấu tòa nhà</td>
                <td id="detail-spec-structure">35 Tầng nổi - 4 Tầng hầm</td>
              </tr>
              <tr>
                <td><i class="fa-solid fa-expand"></i> Diện tích sàn điển hình</td>
                <td id="detail-spec-floorplate">1.100 m² / sàn</td>
              </tr>
              <tr>
                <td><i class="fa-solid fa-ruler-vertical"></i> Chiều cao trần</td>
                <td>2.75 mét (tiêu chuẩn hạng A)</td>
              </tr>
              <tr>
                <td><i class="fa-solid fa-elevator"></i> Hệ thống thang máy</td>
                <td>16 Thang máy Schindler tốc độ cao</td>
              </tr>
              <tr>
                <td><i class="fa-solid fa-snowflake"></i> Hệ thống điều hòa</td>
                <td>Máy lạnh trung tâm Chiller Carrier thông minh</td>
              </tr>
              <tr>
                <td><i class="fa-solid fa-shield-virus"></i> Máy phát điện dự phòng</td>
                <td>100% Công suất tự động chuyển mạch</td>
              </tr>
            </table>

            <div style="display: flex; gap: 10px; margin-top: 20px;">
              <button class="btn-mo-orange" style="flex: 1; justify-content: center;" onclick="bookSurveyFromDetail()">
                <i class="fa-regular fa-calendar-check"></i> Đặt Lịch Khảo Sát
              </button>
              <button class="btn-mo-outline" onclick="openQuoteModal()">
                <i class="fa-solid fa-file-pdf"></i> Tải Báo Giá PDF
              </button>
            </div>
          </div>
        </div>

        <!-- BẢNG MẶT BẰNG & DIỆN TÍCH CẮT THUÊ (YÊU CẦU ĐỀ TÀI TỐT NGHIỆP) -->
        <div class="detail-floors-card">
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
            <div>
              <h2 style="font-size: 20px; font-weight: 800; color: var(--mo-primary);"><i class="fa-solid fa-table-cells"></i> Danh Sách Mặt Bằng & Diện Tích Cắt Thuê Trống</h2>
              <p style="font-size: 13.5px; color: #64748b;">Dữ liệu cập nhật theo thời gian thực từ Ban Quản Lý tòa nhà</p>
            </div>
            <span class="badge-filament success"><i class="fa-solid fa-circle"></i> Sẵn sàng bàn giao</span>
          </div>

          <table class="mo-table">
            <thead>
              <tr>
                <th>Vị trí Tầng</th>
                <th>Diện tích trống</th>
                <th>Hiện trạng bàn giao</th>
                <th>Đơn giá thuê</th>
                <th>Tình trạng lấp đầy</th>
                <th>Thao tác</th>
              </tr>
            </thead>
            <tbody id="detail-floors-tbody">
              <!-- Rendered via Javascript -->
            </tbody>
          </table>
        </div>

        <!-- Bản đồ vị trí tòa nhà (Interactive Map Mockup) -->
        <div class="detail-floors-card">
          <h2 style="font-size: 20px; font-weight: 800; color: var(--mo-primary);"><i class="fa-solid fa-map-pin"></i> Vị Trí Tòa Nhà Trên Bản Đồ TP. Hồ Chí Minh</h2>
          <p style="font-size: 13.5px; color: #64748b;">Tọa lạc tại vị trí đắc địa, giao thông thuận tiện kết nối các tuyến đường huyết mạch</p>
          <div class="detail-map-mock">
            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3919.460195574523!2d106.7037743152605!3d10.775253862148153!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31752f447a192137%3A0xb35a09ad7b26002f!2sVietcombank%20Tower!5e0!3m2!1svi!2s!4v1689000000000!5m2!1svi!2s" loading="lazy"></iframe>
          </div>
        </div>
      </div>
    </main>

    <!-- PAGE 3: QUOTE & SURVEY FORM (PHÂN HỆ THU THẬP LEAD) -->
    <main id="page-quote" class="page-section">
      <div class="container" style="padding-top: 35px; padding-bottom: 60px;">
        <div class="lead-form-wrap">
          <div style="text-align: center; margin-bottom: 24px;">
            <span class="role-switcher-badge"><i class="fa-solid fa-handshake"></i> Tiếp Nhận Yêu Cầu Thuê Văn Phòng</span>
            <h1 style="font-size: 28px; font-weight: 800; color: var(--mo-primary); margin-top: 8px;">Đăng Ký Nhận Báo Giá & Đặt Lịch Khảo Sát</h1>
            <p style="color: #64748b; font-size: 14px; margin-top: 4px;">Điền thông tin nhu cầu doanh nghiệp. Chuyên viên tư vấn sẽ liên hệ và gửi hồ sơ báo giá trong vòng 30 phút.</p>
          </div>

          <form id="lead-consult-form" onsubmit="handleLeadSubmit(event)">
            <div class="lead-form-grid">
              <div class="form-field">
                <label>Họ và tên người liên hệ <span style="color:#ef4444;">*</span></label>
                <input type="text" id="quote-name" required placeholder="Nguyễn Văn A">
              </div>
              <div class="form-field">
                <label>Số điện thoại liên hệ <span style="color:#ef4444;">*</span></label>
                <input type="tel" id="quote-phone" required placeholder="0901 234 567">
              </div>
              <div class="form-field">
                <label>Email doanh nghiệp <span style="color:#ef4444;">*</span></label>
                <input type="email" id="quote-email" required placeholder="contact@company.com">
              </div>
              <div class="form-field">
                <label>Tên công ty / Doanh nghiệp <span style="color:#ef4444;">*</span></label>
                <input type="text" id="quote-company" required placeholder="Công ty TNHH ABC Việt Nam">
              </div>

              <div class="form-field">
                <label>Tòa nhà hoặc Khu vực quan tâm</label>
                <select id="quote-building">
                  <option value="Vietcombank Tower - Quận 1">Vietcombank Tower - Quận 1</option>
                  <option value="Deutsches Haus - Quận 1">Deutsches Haus - Quận 1</option>
                  <option value="Bitexco Financial Tower - Quận 1">Bitexco Financial Tower - Quận 1</option>
                  <option value="Lim Tower 3 - Quận 1">Lim Tower 3 - Quận 1</option>
                  <option value="Pearl Plaza - Bình Thạnh">Pearl Plaza - Bình Thạnh</option>
                  <option value="Landmark 81 Office - Bình Thạnh">Landmark 81 Office - Bình Thạnh</option>
                  <option value="Viettel Complex - Quận 10">Viettel Complex - Quận 10</option>
                  <option value="Mapletree Business Centre - Quận 7">Mapletree Business Centre - Quận 7</option>
                </select>
              </div>

              <div class="form-field">
                <label>Diện tích cần tìm (m²)</label>
                <select id="quote-area-need">
                  <option value="Dưới 100 m²">Dưới 100 m² (Quy mô 10 - 20 nhân sự)</option>
                  <option value="100 - 250 m²" selected>100 - 250 m² (Quy mô 20 - 45 nhân sự)</option>
                  <option value="250 - 500 m²">250 - 500 m² (Quy mô 50 - 90 nhân sự)</option>
                  <option value="500 - 1000 m²">500 - 1000 m² (Quy mô 100 - 200 nhân sự)</option>
                  <option value="Trên 1000 m²">Trên 1000 m² (Tập đoàn / Thuê nhiều sàn)</option>
                </select>
              </div>

              <div class="form-field">
                <label>Ngân sách dự kiến ($/m²/tháng)</label>
                <input type="text" id="quote-budget" placeholder="Ví dụ: $20 - $35/m²">
              </div>

              <div class="form-field">
                <label>Thời gian dự kiến chuyển vào</label>
                <input type="date" id="quote-date">
              </div>

              <div class="form-field full">
                <label>Yêu cầu chi tiết khác (Vị trí, hướng, số chỗ đỗ xe...)</label>
                <textarea id="quote-notes" rows="4" placeholder="Ví dụ: Cần bàn giao hoàn thiện cơ bản, có phòng họp sẵn, ưu tiên tầng cao view sông..."></textarea>
              </div>
            </div>

            <div style="margin-top: 24px; text-align: center;">
              <button type="submit" class="btn-mo-orange" style="font-size: 15px; padding: 12px 36px;">
                <i class="fa-solid fa-paper-plane"></i> Gửi Yêu Cầu Cho Nhân Viên Tư Vấn
              </button>
            </div>
          </form>
        </div>
      </div>
    </main>

    <!-- Maison Office Footer -->
    <footer class="mo-footer">
      <div class="container">
        <div class="mo-footer-grid">
          <div class="mo-footer-col">
            <div class="mo-logo" style="margin-bottom: 14px;">
              <div class="mo-logo-icon" style="background:#ea580c;">
                <i class="fa-solid fa-building-wheat"></i>
              </div>
              <div class="mo-logo-text">
                <h1 style="color:#ffffff;">Office</h1>
              </div>
            </div>
            <p style="line-height: 1.6; margin-bottom: 14px;">Office - Nền tảng quản lý và tư vấn cho thuê cao ốc văn phòng hàng đầu TP.HCM. Cung cấp giải pháp tìm kiếm mặt bằng thông minh và số hóa quy trình quản trị tòa nhà trọn gói.</p>
            <p><i class="fa-solid fa-location-dot" style="color:#ea580c;"></i> Văn phòng TP.HCM: 65 Huỳnh Thúc Kháng, Bến Nghé, Quận 1, TP.HCM</p>
          </div>

          <div class="mo-footer-col">
            <h4>Văn Phòng Theo Quận</h4>
            <ul>
              <li><a href="#" onclick="quickFilterDistrict('Quận 1')">Cho thuê văn phòng Quận 1</a></li>
              <li><a href="#" onclick="quickFilterDistrict('Quận 3')">Cho thuê văn phòng Quận 3</a></li>
              <li><a href="#" onclick="quickFilterDistrict('Bình Thạnh')">Cho thuê văn phòng Bình Thạnh</a></li>
              <li><a href="#" onclick="quickFilterDistrict('Quận 7')">Cho thuê văn phòng Quận 7</a></li>
              <li><a href="#" onclick="quickFilterDistrict('TP Thủ Đức')">Cho thuê văn phòng TP Thủ Đức</a></li>
            </ul>
          </div>

          <div class="mo-footer-col">
            <h4>Phân Loại Hạng</h4>
            <ul>
              <li><a href="#" onclick="quickFilterGrade('Hạng A')">Tòa nhà văn phòng Hạng A</a></li>
              <li><a href="#" onclick="quickFilterGrade('Hạng B')">Tòa nhà văn phòng Hạng B</a></li>
              <li><a href="#" onclick="quickFilterGrade('Hạng C')">Văn phòng Hạng C & Giá Rẻ</a></li>
              <li><a href="#" onclick="alert('Đang kết nối danh sách!')">Văn phòng trọn gói Quận 1</a></li>
              <li><a href="#" onclick="alert('Đang kết nối danh sách!')">Bảng giá thuê văn phòng 2026</a></li>
            </ul>
          </div>

          <div class="mo-footer-col">
            <h4>Hỗ Trợ & Dịch Vụ</h4>
            <ul>
              <li><a href="#" onclick="openQuoteModal()">Tư vấn tìm văn phòng miễn phí</a></li>
              <li><a href="#" onclick="openQuoteModal()">Đặt lịch khảo sát thực tế</a></li>
              <li><a href="#" onclick="showPublicPage('page-home')">Bảng giá thuê văn phòng 2026</a></li>
              <li><a href="#" onclick="switchView('admin-view')">Hệ thống quản lý trực tuyến</a></li>
            </ul>
            <button class="btn-mo-primary" style="width:100%;justify-content:center;margin-top:14px;" onclick="switchView('admin-view')">
              <i class="fa-solid fa-sliders"></i> Mở Bảng Quản Trị
            </button>
          </div>
        </div>

        <div class="mo-footer-bottom">
          <div>© 2026 Office. Hệ thống quản lý và cho thuê tòa nhà văn phòng.</div>
          <div style="display:flex;gap:12px;align-items:center;">
            <span class="mo-project-tag"><i class="fa-solid fa-code"></i> Laravel Backend + Blade + Filament Admin</span>
            <span class="mo-project-tag"><i class="fa-solid fa-database"></i> MySQL Database</span>
          </div>
        </div>
      </div>
    </footer>
  </div>

  <!-- ==========================================================================
       VIEW B: FILAMENT PHP ADMIN PANEL (QUẢN TRỊ 4 TÁC NHÂN: ADMIN, SALES, BQL, TENANT)
       ========================================================================== -->
  <div id="admin-wrapper" style="display: none;">
    <div class="filament-shell">
      <!-- Filament Dark Sidebar -->
      <aside class="filament-sidebar">
        <div class="filament-brand">
          <div class="filament-brand-logo">
            <i class="fa-solid fa-layer-group" style="color:#f59e0b;"></i>
            <span>Office</span>
          </div>
          <span class="filament-badge-role" id="sidebar-role-badge">Admin</span>
        </div>

        <!-- Section: QUẢN TRỊ HỆ THỐNG & VẬN HÀNH (Admin / BQL) -->
        <div class="filament-nav-section" id="nav-group-admin">
          <div class="filament-section-heading">TỔNG QUAN & VẬN HÀNH</div>
          <a class="filament-link active" onclick="switchFilamentTab('tab-admin-dashboard', this)">
            <div class="filament-link-content">
              <i class="fa-solid fa-chart-pie"></i>
              <span>Dashboard Vận Hành</span>
            </div>
          </a>
          <a class="filament-link" onclick="switchFilamentTab('tab-admin-buildings', this)">
            <div class="filament-link-content">
              <i class="fa-solid fa-city"></i>
              <span>Quản Lý Tòa Nhà & Sàn</span>
            </div>
            <span class="filament-nav-badge" style="background:#334155;color:#94a3b8;">18</span>
          </a>
          <a class="filament-link" onclick="switchFilamentTab('tab-admin-tickets', this)">
            <div class="filament-link-content">
              <i class="fa-solid fa-wrench"></i>
              <span>Ticket & Sửa Chữa BQL</span>
            </div>
            <span class="filament-nav-badge" style="background:#ef4444;color:#fff;">5 mới</span>
          </a>
          <a class="filament-link" onclick="switchFilamentTab('tab-admin-contracts', this)">
            <div class="filament-link-content">
              <i class="fa-solid fa-file-contract"></i>
              <span>Quản Lý Hợp Đồng Thuê</span>
            </div>
          </a>
        </div>

        <!-- Section: KINH DOANH & SALES PIPELINE (Sales) -->
        <div class="filament-nav-section" id="nav-group-sales">
          <div class="filament-section-heading">PHÂN HỆ KINH DOANH (SALES)</div>
          <a class="filament-link" onclick="switchFilamentTab('tab-sales-leads', this)">
            <div class="filament-link-content">
              <i class="fa-solid fa-filter-circle-dollar"></i>
              <span>Leads Khách Hàng</span>
            </div>
            <span class="filament-nav-badge" style="background:#f59e0b;color:#000;" id="lead-count-badge">3 mới</span>
          </a>
          <a class="filament-link" onclick="switchFilamentTab('tab-sales-quotes', this)">
            <div class="filament-link-content">
              <i class="fa-solid fa-receipt"></i>
              <span>Lập & Quản Lý Báo Giá</span>
            </div>
          </a>
          <a class="filament-link" onclick="switchFilamentTab('tab-sales-pipeline', this)">
            <div class="filament-link-content">
              <i class="fa-solid fa-timeline"></i>
              <span>Quy Trình Chốt HĐ (Pipeline)</span>
            </div>
          </a>
        </div>

        <!-- Section: CỔNG KHÁCH THUÊ (Tenant Portal) -->
        <div class="filament-nav-section" id="nav-group-tenant">
          <div class="filament-section-heading">CỔNG DOANH NGHIỆP THUÊ</div>
          <a class="filament-link" onclick="switchFilamentTab('tab-tenant-workspace', this)">
            <div class="filament-link-content">
              <i class="fa-solid fa-building-user"></i>
              <span>Văn Phòng Của Tôi</span>
            </div>
          </a>
          <a class="filament-link" onclick="switchFilamentTab('tab-tenant-tickets', this)">
            <div class="filament-link-content">
              <i class="fa-solid fa-ticket"></i>
              <span>Yêu Cầu Hỗ Trợ (Ticket)</span>
            </div>
          </a>
          <a class="filament-link" onclick="switchFilamentTab('tab-tenant-bills', this)">
            <div class="filament-link-content">
              <i class="fa-solid fa-file-invoice"></i>
              <span>Hóa Đơn & Đóng Phí</span>
            </div>
            <span class="filament-nav-badge" style="background:#eab308;color:#000;">Kỳ 11</span>
          </a>
        </div>

        <!-- Exit back to portal -->
        <div style="margin-top:auto;padding:16px;">
          <button class="btn-mo-outline" style="width:100%;justify-content:center;background:#1e293b;border-color:#334155;color:#f8fafc;" onclick="switchView('portal-view')">
            <i class="fa-solid fa-arrow-left"></i> Về Cổng Thông Tin
          </button>
        </div>
      </aside>

      <!-- Filament Main Area -->
      <div class="filament-main">
        <!-- Filament Topbar -->
        <header class="filament-topbar">
          <div class="filament-search-mock">
            <i class="fa-solid fa-magnifying-glass"></i>
            <span>Tìm kiếm dữ liệu tòa nhà, lead, hợp đồng...</span>
            <kbd>Ctrl K</kbd>
          </div>

          <div class="filament-topbar-actions">
            <!-- Role switcher in admin header -->
            <div class="role-selector-pill">
              <i class="fa-solid fa-user-shield"></i>
              <span>Đang xem vai trò:</span>
              <select id="filament-role-select" onchange="handleRoleSwitch(this.value)">
                <option value="admin">Ban Quản Lý & Admin</option>
                <option value="sales">Chuyên Viên Sales / Môi Giới</option>
                <option value="tenant">Khách Thuê (DN An Khang Tech)</option>
              </select>
            </div>

            <div class="filament-user-pill">
              <div class="filament-avatar" id="filament-user-avatar">AD</div>
              <div>
                <div style="font-weight:700;font-size:13.5px;" id="filament-user-name">Nguyễn Quản Trị</div>
                <div style="font-size:11px;color:#64748b;" id="filament-user-sub">Quản trị viên hệ thống</div>
              </div>
            </div>
          </div>
        </header>

        <!-- Filament Body Content -->
        <div class="filament-body">

          <!-- TAB 1: ADMIN DASHBOARD (Tỷ lệ lấp đầy, doanh thu, việc cần xử lý) -->
          <div id="tab-admin-dashboard" class="filament-tab-pane active">
            <div class="filament-page-header">
              <div>
                <div class="filament-breadcrumbs">Trang chủ / Ban quản lý & Admin</div>
                <h1 class="filament-page-title">Tổng Quan Vận Hành & Lấp Đầy Cao Ốc</h1>
              </div>
              <button class="btn-mo-primary" onclick="alert('Đã tạo bản kết xuất PDF Báo cáo Vận Hành!')">
                <i class="fa-solid fa-file-export"></i> Xuất Báo Cáo
              </button>
            </div>

            <!-- Stat Widgets -->
            <div class="filament-widgets-grid">
              <div class="filament-stat-card">
                <div class="filament-stat-label">Tổng số tòa nhà quản lý</div>
                <div class="filament-stat-value">18 <span style="font-size:16px;color:#64748b;font-weight:normal;">tòa</span></div>
                <div class="filament-stat-trend trend-up"><i class="fa-solid fa-arrow-trend-up"></i> +2 tòa nhà mới trong quý</div>
              </div>
              <div class="filament-stat-card">
                <div class="filament-stat-label">Tỷ lệ lấp đầy trung bình</div>
                <div class="filament-stat-value">84,6%</div>
                <div class="filament-stat-trend trend-up"><i class="fa-solid fa-arrow-trend-up"></i> +4,2% so với tháng trước</div>
              </div>
              <div class="filament-stat-card">
                <div class="filament-stat-label">Tổng diện tích đang quản lý</div>
                <div class="filament-stat-value">142.800 <span style="font-size:16px;color:#64748b;font-weight:normal;">m²</span></div>
                <div class="filament-stat-trend" style="color:#64748b;"><i class="fa-solid fa-cube"></i> Còn trống 22.000 m²</div>
              </div>
              <div class="filament-stat-card">
                <div class="filament-stat-label">Ticket bảo trì đang mở</div>
                <div class="filament-stat-value" style="color:#ef4444;">05</div>
                <div class="filament-stat-trend trend-down"><i class="fa-solid fa-circle-exclamation"></i> 2 yêu cầu khẩn cấp hôm nay</div>
              </div>
            </div>

            <!-- Filament Chart & Tasks Layout -->
            <div style="display:grid;grid-template-columns:1.6fr 1fr;gap:24px;margin-bottom:24px;">
              <!-- Chart Card -->
              <div class="filament-card" style="margin-bottom:0;">
                <div class="filament-card-header">
                  <h3 class="filament-card-title"><i class="fa-solid fa-chart-column" style="color:#2563eb;"></i> Tỷ Lệ Lấp Đầy & Diện Tích Thuê (6 Tháng Gần Nhất)</h3>
                  <span class="badge-filament info">Năm 2026</span>
                </div>
                <div style="padding:20px;">
                  <div style="display:flex;align-items:flex-end;gap:18px;height:200px;padding-top:20px;border-bottom:1px solid #e2e8f0;">
                    <div style="flex:1;display:flex;flex-direction:column;align-items:center;height:100%;justify-content:flex-end;">
                      <div style="font-size:11px;font-weight:700;color:#64748b;margin-bottom:4px;">76%</div>
                      <div style="width:100%;height:76%;background:linear-gradient(180deg,#93c5fd,#3b82f6);border-radius:6px 6px 0 0;"></div>
                      <div style="font-size:11px;margin-top:6px;color:#64748b;">T6</div>
                    </div>
                    <div style="flex:1;display:flex;flex-direction:column;align-items:center;height:100%;justify-content:flex-end;">
                      <div style="font-size:11px;font-weight:700;color:#64748b;margin-bottom:4px;">78%</div>
                      <div style="width:100%;height:78%;background:linear-gradient(180deg,#93c5fd,#3b82f6);border-radius:6px 6px 0 0;"></div>
                      <div style="font-size:11px;margin-top:6px;color:#64748b;">T7</div>
                    </div>
                    <div style="flex:1;display:flex;flex-direction:column;align-items:center;height:100%;justify-content:flex-end;">
                      <div style="font-size:11px;font-weight:700;color:#64748b;margin-bottom:4px;">80%</div>
                      <div style="width:100%;height:80%;background:linear-gradient(180deg,#93c5fd,#3b82f6);border-radius:6px 6px 0 0;"></div>
                      <div style="font-size:11px;margin-top:6px;color:#64748b;">T8</div>
                    </div>
                    <div style="flex:1;display:flex;flex-direction:column;align-items:center;height:100%;justify-content:flex-end;">
                      <div style="font-size:11px;font-weight:700;color:#64748b;margin-bottom:4px;">81%</div>
                      <div style="width:100%;height:81%;background:linear-gradient(180deg,#93c5fd,#3b82f6);border-radius:6px 6px 0 0;"></div>
                      <div style="font-size:11px;margin-top:6px;color:#64748b;">T9</div>
                    </div>
                    <div style="flex:1;display:flex;flex-direction:column;align-items:center;height:100%;justify-content:flex-end;">
                      <div style="font-size:11px;font-weight:700;color:#64748b;margin-bottom:4px;">83%</div>
                      <div style="width:100%;height:83%;background:linear-gradient(180deg,#93c5fd,#3b82f6);border-radius:6px 6px 0 0;"></div>
                      <div style="font-size:11px;margin-top:6px;color:#64748b;">T10</div>
                    </div>
                    <div style="flex:1;display:flex;flex-direction:column;align-items:center;height:100%;justify-content:flex-end;">
                      <div style="font-size:11px;font-weight:700;color:#2563eb;margin-bottom:4px;">84.6%</div>
                      <div style="width:100%;height:84.6%;background:linear-gradient(180deg,#f59e0b,#ea580c);border-radius:6px 6px 0 0;"></div>
                      <div style="font-size:11px;margin-top:6px;color:#ea580c;font-weight:700;">T11 (Nay)</div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Action Pending List -->
              <div class="filament-card" style="margin-bottom:0;">
                <div class="filament-card-header">
                  <h3 class="filament-card-title"><i class="fa-solid fa-list-check" style="color:#f59e0b;"></i> Việc Cần Phê Duyệt Ngay</h3>
                  <span class="badge-filament warning">4 mục</span>
                </div>
                <div style="padding:14px 20px;">
                  <div style="padding:10px 0;border-bottom:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center;">
                    <div>
                      <div style="font-weight:600;font-size:13.5px;">Duyệt thi công ngoài giờ (Tầng 12)</div>
                      <div style="font-size:12px;color:#64748b;">Công ty ABC Tech - Vietcombank Tower</div>
                    </div>
                    <button class="btn-mo-primary" style="padding:5px 10px;font-size:12px;" onclick="alert('Đã phê duyệt giấy phép thi công ngoài giờ!')">Duyệt</button>
                  </div>
                  <div style="padding:10px 0;border-bottom:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center;">
                    <div>
                      <div style="font-weight:600;font-size:13.5px;">Bảo trì điều hòa trung tâm phòng Server</div>
                      <div style="font-size:12px;color:#64748b;">Deutsches Haus - Yêu cầu khẩn cấp</div>
                    </div>
                    <button class="btn-mo-orange" style="padding:5px 10px;font-size:12px;" onclick="alert('Đã phân công kỹ sư BQL tới hiện trường!')">Giao việc</button>
                  </div>
                  <div style="padding:10px 0;display:flex;justify-content:space-between;align-items:center;">
                    <div>
                      <div style="font-weight:600;font-size:13.5px;">Gia hạn hợp đồng thuê thêm 2 năm</div>
                      <div style="font-size:12px;color:#64748b;">Tập đoàn Nova Logistics - Lim Tower</div>
                    </div>
                    <button class="btn-mo-outline" style="padding:5px 10px;font-size:12px;" onclick="alert('Đã mở hồ sơ hợp đồng gia hạn!')">Xem</button>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- TAB 2: QUẢN LÝ TÒA NHÀ & MẶT BẰNG (Admin Resource) -->
          <div id="tab-admin-buildings" class="filament-tab-pane" style="display:none;">
            <div class="filament-page-header">
              <div>
                <div class="filament-breadcrumbs">Ban quản lý / Danh mục bất động sản</div>
                <h1 class="filament-page-title">Quản Lý Tòa Nhà & Diện Tích Cắt Thuê</h1>
              </div>
              <button class="btn-mo-orange" onclick="alert('Chức năng thêm mới tòa nhà cao ốc vào hệ thống CSDL!')">
                <i class="fa-solid fa-plus"></i> Thêm Tòa Nhà Mới
              </button>
            </div>

            <div class="filament-card">
              <div class="filament-card-header">
                <div style="display:flex;gap:10px;align-items:center;">
                  <input type="text" class="filament-input-sm" placeholder="Tìm theo tên tòa nhà, quận..." style="width:260px;">
                  <select class="filament-input-sm">
                    <option>Tất cả Hạng</option>
                    <option>Hạng A</option>
                    <option>Hạng B</option>
                  </select>
                </div>
                <span style="font-size:13px;color:#64748b;">Tổng cộng 8 tòa nhà đang hiển thị</span>
              </div>

              <table class="mo-table">
                <thead>
                  <tr>
                    <th>Tên Tòa Nhà</th>
                    <th>Địa Chỉ / Khu Vực</th>
                    <th>Hạng</th>
                    <th>Tổng DT Sàn</th>
                    <th>Tỷ Lệ Lấp Đầy</th>
                    <th>Giá Thuê</th>
                    <th>Thao tác</th>
                  </tr>
                </thead>
                <tbody id="admin-buildings-tbody">
                  <!-- Rendered via JS -->
                </tbody>
              </table>
            </div>
          </div>

          <!-- TAB 3: ADMIN TICKETS & BẢO TRÌ (Ban quản lý xử lý yêu cầu khách thuê) -->
          <div id="tab-admin-tickets" class="filament-tab-pane" style="display:none;">
            <div class="filament-page-header">
              <div>
                <div class="filament-breadcrumbs">Vận hành / Hỗ trợ khách thuê</div>
                <h1 class="filament-page-title">Tiếp Nhận & Điều Phối Ticket Bảo Trì</h1>
              </div>
              <button class="btn-mo-primary" onclick="alert('Chức năng xuất danh sách ticket cho đội kỹ thuật BQL!')">
                <i class="fa-solid fa-print"></i> In Phiếu Kỹ Thuật
              </button>
            </div>

            <div class="filament-card">
              <table class="mo-table">
                <thead>
                  <tr>
                    <th>Mã Ticket</th>
                    <th>Khách Thuê / Doanh Nghiệp</th>
                    <th>Tòa Nhà & Vị Trí</th>
                    <th>Nội Dung Yêu Cầu</th>
                    <th>Mức Ưu Tiên</th>
                    <th>Trạng Thái</th>
                    <th>Hành Động</th>
                  </tr>
                </thead>
                <tbody id="admin-tickets-tbody">
                  <!-- Rendered via JS -->
                </tbody>
              </table>
            </div>
          </div>

          <!-- TAB 4: HỢP ĐỒNG THUÊ -->
          <div id="tab-admin-contracts" class="filament-tab-pane" style="display:none;">
            <div class="filament-page-header">
              <div>
                <div class="filament-breadcrumbs">Ban quản lý / Pháp lý & Hợp đồng</div>
                <h1 class="filament-page-title">Danh Sách Hợp Đồng Thuê Văn Phòng</h1>
              </div>
              <button class="btn-mo-orange" onclick="alert('Mở form tạo hợp đồng mới!')">
                <i class="fa-solid fa-plus"></i> Tạo Hợp Đồng Mới
              </button>
            </div>

            <div class="filament-card">
              <table class="mo-table">
                <thead>
                  <tr>
                    <th>Số HĐ</th>
                    <th>Khách Hàng (Bên Thuê)</th>
                    <th>Tòa Nhà & Tầng</th>
                    <th>Diện Tích</th>
                    <th>Giá Trị / Tháng</th>
                    <th>Thời Hạn Thuê</th>
                    <th>Trạng Thái</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td><strong>#HĐ-2025/OCT-089</strong></td>
                    <td>Công ty TNHH Giải Pháp An Khang</td>
                    <td>Vietcombank Tower - Tầng 12</td>
                    <td>350 m²</td>
                    <td><strong>420.000.000 đ</strong></td>
                    <td>01/01/2025 - 31/12/2027</td>
                    <td><span class="badge-filament success">Hiệu lực</span></td>
                  </tr>
                  <tr>
                    <td><strong>#HĐ-2024/NOV-042</strong></td>
                    <td>Tập đoàn Nova Logistics</td>
                    <td>Lim Tower 3 - Tầng 8</td>
                    <td>500 m²</td>
                    <td><strong>350.000.000 đ</strong></td>
                    <td>15/11/2024 - 14/11/2026</td>
                    <td><span class="badge-filament warning">Sắp hết hạn</span></td>
                  </tr>
                  <tr>
                    <td><strong>#HĐ-2026/FEB-105</strong></td>
                    <td>Hikari Trading Co., Ltd</td>
                    <td>Deutsches Haus - Tầng 16</td>
                    <td>620 m²</td>
                    <td><strong>860.000.000 đ</strong></td>
                    <td>01/03/2026 - 28/02/2029</td>
                    <td><span class="badge-filament success">Hiệu lực</span></td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- TAB 5: SALES LEADS (Khách hàng tiềm năng gửi từ website) -->
          <div id="tab-sales-leads" class="filament-tab-pane" style="display:none;">
            <div class="filament-page-header">
              <div>
                <div class="filament-breadcrumbs">Phân hệ Kinh doanh / Tiếp nhận Lead</div>
                <h1 class="filament-page-title">Khách Hàng Tiềm Năng (Leads Báo Giá)</h1>
              </div>
              <button class="btn-mo-orange" onclick="openQuoteModal()">
                <i class="fa-solid fa-user-plus"></i> Nhập Lead Trực Tiếp
              </button>
            </div>

            <div class="filament-card">
              <table class="mo-table">
                <thead>
                  <tr>
                    <th>Ngày Gửi</th>
                    <th>Khách Hàng / SĐT</th>
                    <th>Doanh Nghiệp</th>
                    <th>Tòa Nhà Quan Tâm</th>
                    <th>DT Dự Kiến</th>
                    <th>Giai Đoạn Pipeline</th>
                    <th>Hành Động</th>
                  </tr>
                </thead>
                <tbody id="sales-leads-tbody">
                  <!-- Rendered via JS -->
                </tbody>
              </table>
            </div>
          </div>

          <!-- TAB 6: LẬP BÁO GIÁ CHO SALES -->
          <div id="tab-sales-quotes" class="filament-tab-pane" style="display:none;">
            <div class="filament-page-header">
              <div>
                <div class="filament-breadcrumbs">Phân hệ Kinh doanh / Báo giá</div>
                <h1 class="filament-page-title">Lập & Quản Lý Hồ Sơ Báo Giá Cho Khách</h1>
              </div>
              <button class="btn-mo-orange" onclick="alert('Đã mở trình tạo Báo giá tự động!')">
                <i class="fa-solid fa-file-circle-plus"></i> Tạo Báo Giá Mới
              </button>
            </div>

            <div class="filament-card" style="padding:24px;">
              <h3 style="font-size:18px;font-weight:700;margin-bottom:14px;color:var(--mo-primary);">
                <i class="fa-solid fa-calculator"></i> Công Cụ Tính Nhanh Chi Phí Thuê Văn Phòng
              </h3>
              <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:18px;">
                <div class="form-field">
                  <label>Chọn Tòa Nhà</label>
                  <select id="calc-building" onchange="calculateQuickQuote()">
                    <option value="48">Vietcombank Tower ($48/m²)</option>
                    <option value="58">Deutsches Haus ($58/m²)</option>
                    <option value="28">Lim Tower 3 ($28/m²)</option>
                    <option value="22">Pearl Plaza ($22/m²)</option>
                  </select>
                </div>
                <div class="form-field">
                  <label>Diện Tích Thuê (m²)</label>
                  <input type="number" id="calc-area" value="250" oninput="calculateQuickQuote()">
                </div>
                <div class="form-field">
                  <label>Phí Quản Lý ($/m²)</label>
                  <input type="number" id="calc-service" value="6" oninput="calculateQuickQuote()">
                </div>
              </div>

              <!-- Calculation Result Banner -->
              <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:20px;display:flex;justify-content:space-between;align-items:center;">
                <div>
                  <div style="font-size:13px;color:#64748b;">Tổng tiền thuê dự kiến hàng tháng:</div>
                  <div style="font-size:28px;font-weight:800;color:#ea580c;" id="calc-total-usd">$13,500 / tháng</div>
                  <div style="font-size:13px;color:#94a3b8;" id="calc-total-vnd">Tương đương: ~330.750.000 VNĐ / tháng (Chưa gồm VAT)</div>
                </div>
                <button class="btn-mo-primary" onclick="alert('Đã xuất file báo giá PDF gửi cho khách hàng!')">
                  <i class="fa-solid fa-paper-plane"></i> Xuất Báo Giá PDF
                </button>
              </div>
            </div>
          </div>

          <!-- TAB 7: PIPELINE KANBAN (VÒNG ĐỜI KHÁCH HÀNG) -->
          <div id="tab-sales-pipeline" class="filament-tab-pane" style="display:none;">
            <div class="filament-page-header">
              <div>
                <div class="filament-breadcrumbs">Kinh doanh / Phễu khách hàng</div>
                <h1 class="filament-page-title">Pipeline Bán Hàng & Chốt Hợp Đồng</h1>
              </div>
            </div>

            <!-- Kanban Board Grid -->
            <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:18px;">
              <!-- Col 1 -->
              <div style="background:#f1f5f9;border-radius:10px;padding:14px;">
                <div style="font-weight:700;font-size:13.5px;color:#334155;margin-bottom:12px;display:flex;justify-content:space-between;">
                  <span>1. TIẾP NHẬN LEAD</span>
                  <span class="badge-filament info">2</span>
                </div>
                <div style="background:#fff;padding:12px;border-radius:8px;border:1px solid #cbd5e1;margin-bottom:10px;box-shadow:var(--shadow-sm);">
                  <div style="font-weight:700;font-size:13.5px;color:var(--mo-primary);">Công ty CP FPT Software</div>
                  <div style="font-size:12px;color:#64748b;margin:4px 0;">Cần 1.200 m² - Quận 1</div>
                  <span class="badge-filament warning">Khẩn cấp</span>
                </div>
                <div style="background:#fff;padding:12px;border-radius:8px;border:1px solid #cbd5e1;box-shadow:var(--shadow-sm);">
                  <div style="font-weight:700;font-size:13.5px;color:var(--mo-primary);">Kido Group</div>
                  <div style="font-size:12px;color:#64748b;margin:4px 0;">Cần 350 m² - Bình Thạnh</div>
                  <span class="badge-filament info">Mới gửi 1h trước</span>
                </div>
              </div>

              <!-- Col 2 -->
              <div style="background:#f1f5f9;border-radius:10px;padding:14px;">
                <div style="font-weight:700;font-size:13.5px;color:#334155;margin-bottom:12px;display:flex;justify-content:space-between;">
                  <span>2. ĐẶT LỊCH KHẢO SÁT</span>
                  <span class="badge-filament warning">1</span>
                </div>
                <div style="background:#fff;padding:12px;border-radius:8px;border:1px solid #cbd5e1;box-shadow:var(--shadow-sm);">
                  <div style="font-weight:700;font-size:13.5px;color:var(--mo-primary);">TNHH FinTech VN</div>
                  <div style="font-size:12px;color:#64748b;margin:4px 0;">Khảo sát Vietcombank Tầng 15</div>
                  <div style="font-size:11.5px;color:#ea580c;font-weight:600;"><i class="fa-regular fa-clock"></i> 14:00 Chiều mai</div>
                </div>
              </div>

              <!-- Col 3 -->
              <div style="background:#f1f5f9;border-radius:10px;padding:14px;">
                <div style="font-weight:700;font-size:13.5px;color:#334155;margin-bottom:12px;display:flex;justify-content:space-between;">
                  <span>3. ĐÀM PHÁN BÁO GIÁ</span>
                  <span class="badge-filament info">2</span>
                </div>
                <div style="background:#fff;padding:12px;border-radius:8px;border:1px solid #cbd5e1;margin-bottom:10px;box-shadow:var(--shadow-sm);">
                  <div style="font-weight:700;font-size:13.5px;color:var(--mo-primary);">Tập đoàn Nova Logistics</div>
                  <div style="font-size:12px;color:#64748b;margin:4px 0;">Lim Tower - Đang thương lượng PQL</div>
                  <span class="badge-filament success">Đã gửi LOI</span>
                </div>
              </div>

              <!-- Col 4 -->
              <div style="background:#f1f5f9;border-radius:10px;padding:14px;">
                <div style="font-weight:700;font-size:13.5px;color:#334155;margin-bottom:12px;display:flex;justify-content:space-between;">
                  <span>4. CHỐT KÝ HỢP ĐỒNG</span>
                  <span class="badge-filament success">1</span>
                </div>
                <div style="background:#fff;padding:12px;border-radius:8px;border:1px solid #bbf7d0;background:#f0fdf4;box-shadow:var(--shadow-sm);">
                  <div style="font-weight:700;font-size:13.5px;color:#15803d;">Hikari Trading Co.</div>
                  <div style="font-size:12px;color:#166534;margin:4px 0;">Deutsches Haus - 620 m²</div>
                  <span class="badge-filament success"><i class="fa-solid fa-check"></i> Hoàn tất ký HĐ</span>
                </div>
              </div>
            </div>
          </div>

          <!-- TAB 8: TENANT WORKSPACE (Không gian làm việc cho doanh nghiệp thuê) -->
          <div id="tab-tenant-workspace" class="filament-tab-pane" style="display:none;">
            <div class="filament-page-header">
              <div>
                <div class="filament-breadcrumbs">Cổng khách thuê / Tổng quan văn phòng</div>
                <h1 class="filament-page-title">Chào Mừng, Công Ty CP Giải Pháp Công Nghệ An Khang</h1>
              </div>
              <span class="badge-filament success" style="font-size:13px;padding:6px 12px;"><i class="fa-solid fa-circle-check"></i> Hợp Đồng Đang Có Hiệu Lực</span>
            </div>

            <!-- Tenant Quick Stats -->
            <div class="filament-widgets-grid">
              <div class="filament-stat-card">
                <div class="filament-stat-label">Vị trí mặt bằng</div>
                <div class="filament-stat-value" style="font-size:22px;">Tầng 12</div>
                <div style="font-size:12.5px;color:#64748b;"><i class="fa-solid fa-building"></i> Vietcombank Tower, Quận 1</div>
              </div>
              <div class="filament-stat-card">
                <div class="filament-stat-label">Diện tích sàn đang thuê</div>
                <div class="filament-stat-value">350 <span style="font-size:16px;color:#64748b;">m²</span></div>
                <div style="font-size:12.5px;color:#10b981;"><i class="fa-solid fa-users"></i> Quy mô ~45 nhân sự</div>
              </div>
              <div class="filament-stat-card">
                <div class="filament-stat-label">Thời hạn hợp đồng còn lại</div>
                <div class="filament-stat-value">14 <span style="font-size:16px;color:#64748b;">tháng</span></div>
                <div style="font-size:12.5px;color:#64748b;">Hết hạn: 31/12/2027</div>
              </div>
              <div class="filament-stat-card">
                <div class="filament-stat-label">Cước phí kỳ này (T11/2026)</div>
                <div class="filament-stat-value" style="color:#ea580c;font-size:22px;">420,00 Tr</div>
                <div style="font-size:12.5px;color:#eab308;"><i class="fa-solid fa-clock"></i> Hạn thanh toán: 15/11/2026</div>
              </div>
            </div>

            <div style="display:grid;grid-template-columns:1.2fr 1fr;gap:24px;">
              <!-- Current Contract Box -->
              <div class="filament-card">
                <div class="filament-card-header">
                  <h3 class="filament-card-title"><i class="fa-solid fa-file-contract"></i> Thông Tin Hợp Đồng Hiện Tại</h3>
                  <button class="btn-mo-outline" style="font-size:12px;padding:4px 8px;" onclick="alert('Đã tải file PDF Hợp đồng thuê đầy đủ!')">
                    <i class="fa-solid fa-download"></i> Tải File HĐ
                  </button>
                </div>
                <div style="padding:18px;">
                  <table class="detail-specs-table" style="margin:0;">
                    <tr>
                      <td>Mã Hợp Đồng:</td>
                      <td><strong>#HĐ-2025/OCT-089</strong></td>
                    </tr>
                    <tr>
                      <td>Đại diện ký:</td>
                      <td>Trần Văn Nam (Giám đốc Điều hành)</td>
                    </tr>
                    <tr>
                      <td>Thời hạn thuê:</td>
                      <td>36 Tháng (01/01/2025 đến 31/12/2027)</td>
                    </tr>
                    <tr>
                      <td>Tiền cọc thuê (3 tháng):</td>
                      <td>1.260.000.000 VNĐ (Đã ký quỹ)</td>
                    </tr>
                    <tr>
                      <td>Định kỳ thanh toán:</td>
                      <td>Theo quý (3 tháng/lần vào ngày 05 đầu kỳ)</td>
                    </tr>
                  </table>
                </div>
              </div>

              <!-- Quick Ticket Actions -->
              <div class="filament-card">
                <div class="filament-card-header">
                  <h3 class="filament-card-title"><i class="fa-solid fa-headset"></i> Yêu Cầu Hỗ Trợ Gần Đây</h3>
                  <button class="btn-mo-orange" style="font-size:12px;padding:5px 10px;" onclick="switchFilamentTab('tab-tenant-tickets', document.querySelectorAll('#nav-group-tenant .filament-link')[1])">
                    + Tạo Ticket Mới
                  </button>
                </div>
                <div style="padding:16px;">
                  <div style="padding:10px 0;border-bottom:1px solid #f1f5f9;">
                    <div style="display:flex;justify-content:space-between;">
                      <strong>Bảo trì dàn lạnh âm trần phòng họp A</strong>
                      <span class="badge-filament warning">Đang xử lý</span>
                    </div>
                    <div style="font-size:12px;color:#64748b;margin-top:2px;">BQL đã cử kỹ thuật viên kiểm tra lúc 10:30 sáng nay</div>
                  </div>
                  <div style="padding:10px 0;">
                    <div style="display:flex;justify-content:space-between;">
                      <strong>Đăng ký làm thêm ngoài giờ (Tối Thứ Bảy)</strong>
                      <span class="badge-filament success">Đã phê duyệt</span>
                    </div>
                    <div style="font-size:12px;color:#64748b;margin-top:2px;">Kỹ thuật tòa nhà đã kích hoạt hệ thống điện & thang máy</div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- TAB 9: TENANT TICKET MANAGEMENT -->
          <div id="tab-tenant-tickets" class="filament-tab-pane" style="display:none;">
            <div class="filament-page-header">
              <div>
                <div class="filament-breadcrumbs">Cổng khách thuê / Hỗ trợ vận hành</div>
                <h1 class="filament-page-title">Gửi Yêu Cầu Hỗ Trợ & Đăng Ký Thi Công</h1>
              </div>
              <button class="btn-mo-orange" onclick="openTenantTicketModal()">
                <i class="fa-solid fa-plus"></i> Tạo Ticket Mới
              </button>
            </div>

            <div class="filament-card">
              <table class="mo-table">
                <thead>
                  <tr>
                    <th>Mã Ticket</th>
                    <th>Loại Yêu Cầu</th>
                    <th>Tiêu Đề / Mô Tả</th>
                    <th>Thời Gian Tạo</th>
                    <th>Mức Ưu Tiên</th>
                    <th>Trạng Thái Xử Lý</th>
                  </tr>
                </thead>
                <tbody id="tenant-tickets-tbody">
                  <!-- Rendered via JS -->
                </tbody>
              </table>
            </div>
          </div>

          <!-- TAB 10: TENANT BILLS & PAYMENTS -->
          <div id="tab-tenant-bills" class="filament-tab-pane" style="display:none;">
            <div class="filament-page-header">
              <div>
                <div class="filament-breadcrumbs">Cổng khách thuê / Tài chính</div>
                <h1 class="filament-page-title">Thông Báo Phí & Lịch Sử Công Nợ</h1>
              </div>
              <button class="btn-mo-primary" onclick="alert('Đã tải toàn bộ bảng sao kê hóa đơn điện tử VAT!')">
                <i class="fa-solid fa-file-invoice"></i> Xuất Bảng Kê VAT
              </button>
            </div>

            <div class="filament-card">
              <table class="mo-table">
                <thead>
                  <tr>
                    <th>Kỳ Cước</th>
                    <th>Khoản Mục Phí</th>
                    <th>Số Tiền (VNĐ)</th>
                    <th>Hạn Nộp</th>
                    <th>Trạng Thái</th>
                    <th>Thao Tác</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td><strong>Tháng 11/2026</strong></td>
                    <td>Tiền thuê mặt bằng T12 + Phí quản lý dịch vụ</td>
                    <td><strong style="color:#ea580c;">420.000.000 đ</strong></td>
                    <td>15/11/2026</td>
                    <td><span class="badge-filament warning">Chờ thanh toán</span></td>
                    <td>
                      <button class="btn-mo-orange" style="padding:4px 10px;font-size:12px;" onclick="alert('Mã QR chuyển khoản VietQR nộp tiền thuê:\nSTK: 0071000888999\nNgân hàng: Vietcombank\nSố tiền: 420.000.000 VNĐ')">
                        <i class="fa-solid fa-qrcode"></i> Quét Mã Nộp
                      </button>
                    </td>
                  </tr>
                  <tr>
                    <td>Tháng 10/2026</td>
                    <td>Tiền thuê + Điện lạnh ngoài giờ (32 giờ)</td>
                    <td>428.500.000 đ</td>
                    <td>15/10/2026</td>
                    <td><span class="badge-filament success">Đã thanh toán</span></td>
                    <td>
                      <button class="btn-mo-outline" style="padding:4px 10px;font-size:12px;" onclick="alert('Đã tải hóa đơn điện tử số #HD-109283!')">
                        Xem HĐ VAT
                      </button>
                    </td>
                  </tr>
                  <tr>
                    <td>Tháng 09/2026</td>
                    <td>Tiền thuê mặt bằng + Phí quản lý</td>
                    <td>420.000.000 đ</td>
                    <td>15/09/2026</td>
                    <td><span class="badge-filament success">Đã thanh toán</span></td>
                    <td>
                      <button class="btn-mo-outline" style="padding:4px 10px;font-size:12px;" onclick="alert('Đã tải hóa đơn điện tử số #HD-098231!')">
                        Xem HĐ VAT
                      </button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>

  <!-- ==========================================================================
       MODALS: QUOTE MODAL & TICKET MODAL
       ========================================================================== -->
  <!-- Fast Quote Modal -->
  <div id="quote-modal" class="mo-modal">
    <div class="mo-modal-box">
      <div class="mo-modal-head">
        <h3 style="font-size:17px;font-weight:700;"><i class="fa-solid fa-file-invoice-dollar"></i> Nhận Báo Giá Văn Phòng Nhanh (30 Phút)</h3>
        <i class="fa-solid fa-xmark" style="cursor:pointer;font-size:20px;" onclick="closeQuoteModal()"></i>
      </div>
      <div class="mo-modal-body">
        <form onsubmit="handleQuickQuoteModal(event)">
          <div class="form-field" style="margin-bottom:12px;">
            <label>Họ và tên của bạn</label>
            <input type="text" id="modal-name" required placeholder="Nguyễn Văn A">
          </div>
          <div class="form-field" style="margin-bottom:12px;">
            <label>Số điện thoại nhận báo giá</label>
            <input type="tel" id="modal-phone" required placeholder="0901 234 567">
          </div>
          <div class="form-field" style="margin-bottom:12px;">
            <label>Tòa nhà hoặc Khu vực muốn xem</label>
            <input type="text" id="modal-target" placeholder="Ví dụ: Vietcombank Tower hoặc Quận 1">
          </div>
          <div class="form-field" style="margin-bottom:18px;">
            <label>Diện tích mong muốn</label>
            <select id="modal-area">
              <option>100 - 250 m²</option>
              <option>250 - 500 m²</option>
              <option>Trên 500 m²</option>
              <option>Dưới 100 m²</option>
            </select>
          </div>
          <button type="submit" class="btn-mo-orange" style="width:100%;justify-content:center;padding:12px;">
            Gửi Yêu Cầu Tư Vấn Miễn Phí
          </button>
        </form>
      </div>
    </div>
  </div>

  <!-- New Ticket Modal For Tenant -->
  <div id="ticket-modal" class="mo-modal">
    <div class="mo-modal-box">
      <div class="mo-modal-head" style="background:#0f3361;">
        <h3 style="font-size:17px;font-weight:700;"><i class="fa-solid fa-wrench"></i> Tạo Ticket Yêu Cầu Hỗ Trợ Cho BQL</h3>
        <i class="fa-solid fa-xmark" style="cursor:pointer;font-size:20px;" onclick="closeTenantTicketModal()"></i>
      </div>
      <div class="mo-modal-body">
        <form onsubmit="handleTenantNewTicket(event)">
          <div class="form-field" style="margin-bottom:12px;">
            <label>Loại yêu cầu</label>
            <select id="ticket-type">
              <option>Sửa chữa & Bảo trì điện tử / Máy lạnh</option>
              <option>Đăng ký nhà thầu thi công ngoài giờ</option>
              <option>Cấp thêm thẻ xe máy / ô tô nhân viên</option>
              <option>Vệ sinh mặt bằng & Xử lý rác thải công nghiệp</option>
              <option>Khác</option>
            </select>
          </div>
          <div class="form-field" style="margin-bottom:12px;">
            <label>Mức độ ưu tiên</label>
            <select id="ticket-priority">
              <option value="Bình thường">Bình thường</option>
              <option value="Cao">Cao</option>
              <option value="Khẩn cấp">Khẩn cấp</option>
            </select>
          </div>
          <div class="form-field" style="margin-bottom:12px;">
            <label>Tiêu đề yêu cầu</label>
            <input type="text" id="ticket-title" required placeholder="Ví dụ: Máy lạnh khu vực phòng họp bị rò rỉ nước">
          </div>
          <div class="form-field" style="margin-bottom:18px;">
            <label>Nội dung chi tiết</label>
            <textarea id="ticket-desc" rows="4" required placeholder="Mô tả cụ thể vị trí và tình trạng để kỹ sư BQL xử lý kịp thời..."></textarea>
          </div>
          <button type="submit" class="btn-mo-primary" style="width:100%;justify-content:center;padding:12px;">
            Gửi Ticket Lên Ban Quản Lý
          </button>
        </form>
      </div>
    </div>
  </div>

  <!-- ==========================================================================
       JAVASCRIPT LOGIC (DATA, STATE, FILTERS, SPA ROUTING)
       ========================================================================== -->
  <script>
    /* 1. DATABASE & SERVER SYNC (BLADE + REST API) */
    const SERVER_BUILDINGS = @json($serverBuildings ?? []);
    const DEFAULT_BUILDINGS = [
      {
        id: 1,
        name: "Vietcombank Tower",
        slug: "vietcombank-tower",
        grade: "Hạng A",
        district: "Quận 1",
        address: "Số 5 Công Trường Mê Linh, Phường Bến Nghé, Quận 1",
        priceUsd: 48,
        priceMaxUsd: 54,
        priceVnd: "1.180.000 đ",
        totalArea: "55.000 m²",
        availableArea: "2.400 m²",
        floorPlate: "1.100 m²",
        structure: "35 Tầng nổi - 4 Hầm",
        status: "Đang trống",
        statusClass: "empty",
        image: "https://images.unsplash.com/photo-1577495508048-b635879837f1?auto=format&fit=crop&w=800&q=80",
        floors: [
          { floor: "Tầng 5", area: "180 m²", deliver: "Trần sàn thô", price: "$48 / m²", status: "Đang trống" },
          { floor: "Tầng 12", area: "350 m²", deliver: "Hoàn thiện cơ bản", price: "$50 / m²", status: "Đang trống" },
          { floor: "Tầng 18", area: "650 m²", deliver: "Nội thất cao cấp", price: "$52 / m²", status: "Đang trống" },
          { floor: "Tầng 25", area: "1.100 m² (Nguyên sàn)", deliver: "Trần thô", price: "$54 / m²", status: "Sắp trống (T12/2026)" }
        ]
      },
      {
        id: 2,
        name: "Deutsches Haus Ho Chi Minh City",
        slug: "deutsches-haus",
        grade: "Hạng A",
        district: "Quận 1",
        address: "33 Lê Duẩn, Phường Bến Nghé, Quận 1",
        priceUsd: 58,
        priceMaxUsd: 68,
        priceVnd: "1.420.000 đ",
        totalArea: "40.000 m²",
        availableArea: "1.850 m²",
        floorPlate: "1.500 m²",
        structure: "25 Tầng nổi - 4 Hầm",
        status: "Sắp trống",
        statusClass: "soon",
        image: "https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?auto=format&fit=crop&w=800&q=80",
        floors: [
          { floor: "Tầng 8", area: "220 m²", deliver: "Tiêu chuẩn LEED Platinum", price: "$58 / m²", status: "Đang trống" },
          { floor: "Tầng 16", area: "620 m²", deliver: "Nội thất hạng sang", price: "$62 / m²", status: "Sắp trống" },
          { floor: "Tầng 21", area: "1.000 m²", deliver: "Trần sàn hoàn thiện", price: "$68 / m²", status: "Đang trống" }
        ]
      },
      {
        id: 3,
        name: "Bitexco Financial Tower",
        slug: "bitexco-financial-tower",
        grade: "Hạng A",
        district: "Quận 1",
        address: "Số 2 Hải Triều, Phường Bến Nghé, Quận 1",
        priceUsd: 42,
        priceMaxUsd: 48,
        priceVnd: "1.030.000 đ",
        totalArea: "37.000 m²",
        availableArea: "1.200 m²",
        floorPlate: "1.000 m²",
        structure: "68 Tầng nổi - 3 Hầm",
        status: "Đang trống",
        statusClass: "empty",
        image: "https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?auto=format&fit=crop&w=800&q=80",
        floors: [
          { floor: "Tầng 14", area: "150 m²", deliver: "Hoàn thiện cơ bản", price: "$42 / m²", status: "Đang trống" },
          { floor: "Tầng 28", area: "450 m²", deliver: "View sông Sài Gòn", price: "$46 / m²", status: "Đang trống" },
          { floor: "Tầng 35", area: "800 m²", deliver: "Trần thô", price: "$48 / m²", status: "Sắp trống" }
        ]
      },
      {
        id: 4,
        name: "Lim Tower 3",
        slug: "lim-tower-3",
        grade: "Hạng B",
        district: "Quận 1",
        address: "29A Nguyễn Đình Chiểu, Phường Đa Kao, Quận 1",
        priceUsd: 28,
        priceMaxUsd: 32,
        priceVnd: "690.000 đ",
        totalArea: "22.000 m²",
        availableArea: "950 m²",
        floorPlate: "750 m²",
        structure: "25 Tầng nổi - 3 Hầm",
        status: "Đang trống",
        statusClass: "empty",
        image: "https://images.unsplash.com/photo-1554469384-e58fac16e23a?auto=format&fit=crop&w=800&q=80",
        floors: [
          { floor: "Tầng 6", area: "120 m²", deliver: "Trần thô, sàn gạch", price: "$28 / m²", status: "Đang trống" },
          { floor: "Tầng 11", area: "380 m²", deliver: "Bàn giao sẵn máy lạnh", price: "$30 / m²", status: "Đang trống" },
          { floor: "Tầng 19", area: "500 m²", deliver: "Hoàn thiện cơ bản", price: "$32 / m²", status: "Đang trống" }
        ]
      },
      {
        id: 5,
        name: "Pearl Plaza",
        slug: "pearl-plaza",
        grade: "Hạng B",
        district: "Bình Thạnh",
        address: "561A Điện Biên Phủ, Phường 25, Bình Thạnh",
        priceUsd: 22,
        priceMaxUsd: 26,
        priceVnd: "540.000 đ",
        totalArea: "32.000 m²",
        availableArea: "1.400 m²",
        floorPlate: "1.200 m²",
        structure: "32 Tầng nổi - 4 Hầm",
        status: "Đang trống",
        statusClass: "empty",
        image: "https://images.unsplash.com/photo-1512917774080-9991f1c4c750?auto=format&fit=crop&w=800&q=80",
        floors: [
          { floor: "Tầng 9", area: "95 m²", deliver: "Ngăn vách kính sẵn", price: "$22 / m²", status: "Đang trống" },
          { floor: "Tầng 14", area: "320 m²", deliver: "Hoàn thiện", price: "$24 / m²", status: "Đang trống" },
          { floor: "Tầng 22", area: "850 m²", deliver: "Sàn nâng", price: "$26 / m²", status: "Sắp trống" }
        ]
      },
      {
        id: 6,
        name: "Landmark 81 Office Hub",
        slug: "landmark-81-office",
        grade: "Hạng A",
        district: "Bình Thạnh",
        address: "720A Điện Biên Phủ, Phường 22, Bình Thạnh",
        priceUsd: 45,
        priceMaxUsd: 52,
        priceVnd: "1.100.000 đ",
        totalArea: "45.000 m²",
        availableArea: "2.100 m²",
        floorPlate: "1.600 m²",
        structure: "81 Tầng nổi - 3 Hầm",
        status: "Sắp trống",
        statusClass: "soon",
        image: "https://images.unsplash.com/photo-1497366216548-37526070297c?auto=format&fit=crop&w=800&q=80",
        floors: [
          { floor: "Tầng 22", area: "300 m²", deliver: "View toàn cảnh công viên", price: "$45 / m²", status: "Đang trống" },
          { floor: "Tầng 38", area: "750 m²", deliver: "Hạng A sang trọng", price: "$48 / m²", status: "Sắp trống" },
          { floor: "Tầng 42", area: "1.200 m²", deliver: "Trần sàn nguyên sàn", price: "$52 / m²", status: "Sắp trống" }
        ]
      },
      {
        id: 7,
        name: "Viettel Complex Building",
        slug: "viettel-complex",
        grade: "Hạng B",
        district: "Quận 10",
        address: "285 Cách Mạng Tháng 8, Phường 12, Quận 10",
        priceUsd: 24,
        priceMaxUsd: 27,
        priceVnd: "590.000 đ",
        totalArea: "30.000 m²",
        availableArea: "1.100 m²",
        floorPlate: "900 m²",
        structure: "30 Tầng nổi - 3 Hầm",
        status: "Đang trống",
        statusClass: "empty",
        image: "https://images.unsplash.com/photo-1497366811353-6870744d04b2?auto=format&fit=crop&w=800&q=80",
        floors: [
          { floor: "Tầng 7", area: "140 m²", deliver: "Bàn giao cơ bản", price: "$24 / m²", status: "Đang trống" },
          { floor: "Tầng 15", area: "480 m²", deliver: "Sẵn trần điều hòa", price: "$26 / m²", status: "Đang trống" }
        ]
      },
      {
        id: 8,
        name: "Mapletree Business Centre",
        slug: "mapletree-business-centre",
        grade: "Hạng A",
        district: "Quận 7",
        address: "1060 Nguyễn Văn Linh, Phường Tân Phong, Quận 7",
        priceUsd: 26,
        priceMaxUsd: 30,
        priceVnd: "640.000 đ",
        totalArea: "28.000 m²",
        availableArea: "1.600 m²",
        floorPlate: "1.400 m²",
        structure: "17 Tầng nổi - 2 Hầm",
        status: "Đang trống",
        statusClass: "empty",
        image: "https://images.unsplash.com/photo-1504384308090-c894fdcc538d?auto=format&fit=crop&w=800&q=80",
        floors: [
          { floor: "Tầng 4", area: "250 m²", deliver: "Tiêu chuẩn quốc tế", price: "$26 / m²", status: "Đang trống" },
          { floor: "Tầng 9", area: "700 m²", deliver: "Trần thô sàn nâng", price: "$28 / m²", status: "Đang trống" },
          { floor: "Tầng 15", area: "1.100 m²", deliver: "Bàn giao hoàn thiện", price: "$30 / m²", status: "Đang trống" }
        ]
      }
    ];

    // Ưu tiên dùng dữ liệu thật từ Database Laravel Blade, nếu rỗng thì fallback mảng chuẩn
    const BUILDINGS_DATA = (SERVER_BUILDINGS && SERVER_BUILDINGS.length > 0) ? SERVER_BUILDINGS : DEFAULT_BUILDINGS;

    let currentSelectedBuilding = BUILDINGS_DATA[0];

    // Mock Leads List (For Sales Module)
    let LEADS_DATA = [
      { id: 101, date: "06/11/2026", name: "Nguyễn Văn Hưng (0908 123 456)", company: "Công ty CP FPT Software", building: "Vietcombank Tower", area: "1.200 m²", stage: "Tiếp nhận mới", stageBadge: "warning" },
      { id: 102, date: "06/11/2026", name: "Trần Thị Mai (0912 345 678)", company: "Kido Group", building: "Pearl Plaza", area: "350 m²", stage: "Đã gửi báo giá", stageBadge: "info" },
      { id: 103, date: "05/11/2026", name: "Lê Hoàng Quân (0933 888 999)", company: "FinTech Solutions", building: "Deutsches Haus", area: "620 m²", stage: "Đặt lịch khảo sát", stageBadge: "primary" }
    ];

    // Mock Tickets List (For BQL and Tenant Module)
    let TICKETS_DATA = [
      { id: "TK-2026-081", tenant: "Công ty CP An Khang", location: "Vietcombank Tower - T12", title: "Bảo trì dàn lạnh âm trần phòng họp A", priority: "Cao", status: "Đang xử lý", statusBadge: "warning", time: "10:30 Hôm nay" },
      { id: "TK-2026-080", tenant: "Nova Logistics", location: "Lim Tower 3 - T8", title: "Đăng ký thi công lắp vách nhôm kính ngoài giờ", priority: "Bình thường", status: "Đã duyệt", statusBadge: "success", time: "Hôm qua" },
      { id: "TK-2026-079", tenant: "Hikari Trading", location: "Deutsches Haus - T16", title: "Cấp phát thêm 5 thẻ quẹt xe máy cho nhân sự", priority: "Bình thường", status: "Đã hoàn tất", statusBadge: "success", time: "04/11/2026" },
      { id: "TK-2026-078", tenant: "Công ty CP An Khang", location: "Vietcombank Tower - T12", title: "Bóng đèn Led hành lang chớp nháy", priority: "Bình thường", status: "Đã hoàn tất", statusBadge: "success", time: "02/11/2026" }
    ];

    /* 2. RENDER PUBLIC BUILDINGS (MAISON OFFICE STYLE) */
    function renderBuildings(items) {
      const container = document.getElementById("building-cards-container");
      const countText = document.getElementById("listing-count-text");
      if (!container) return;

      countText.innerText = `Hiển thị ${items.length} tòa nhà văn phòng phù hợp với bộ lọc`;

      if (items.length === 0) {
        container.innerHTML = `
          <div style="grid-column: 1 / -1; background:#fff; border:1px solid #e2e8f0; border-radius:12px; padding:40px; text-align:center;">
            <i class="fa-solid fa-building-circle-exclamation" style="font-size:44px; color:#94a3b8; margin-bottom:12px;"></i>
            <h3 style="font-size:18px; color:var(--mo-primary); margin-bottom:6px;">Không tìm thấy tòa nhà phù hợp</h3>
            <p style="color:#64748b; font-size:14px; margin-bottom:16px;">Vui lòng thử bỏ bớt bộ lọc hoặc chọn khoảng giá/diện tích khác.</p>
            <button class="btn-mo-orange" onclick="resetAllFilters()">Đặt lại bộ lọc</button>
          </div>
        `;
        return;
      }

      container.innerHTML = items.map(b => `
        <article class="mo-building-card">
          <div class="mo-card-thumb" onclick="openBuildingDetail(${b.id})" style="cursor:pointer;">
            <img src="${b.image}" alt="${b.name}">
            <div class="mo-thumb-badges">
              <span class="mo-badge-grade">${b.grade}</span>
              <span class="mo-badge-status ${b.statusClass}"><i class="fa-solid fa-circle" style="font-size:8px;"></i> ${b.status}</span>
            </div>
          </div>
          <div class="mo-card-body">
            <span class="mo-card-district"><i class="fa-solid fa-location-dot"></i> ${b.district}</span>
            <h3 class="mo-card-name" onclick="openBuildingDetail(${b.id})" style="cursor:pointer;">${b.name}</h3>
            <p class="mo-card-address">${b.address}</p>

            <div class="mo-card-specs">
              <div class="mo-spec-item"><i class="fa-solid fa-expand"></i> Sàn: <strong>${b.floorPlate}</strong></div>
              <div class="mo-spec-item"><i class="fa-solid fa-layer-group"></i> Quy mô: <strong>${b.structure.split('-')[0]}</strong></div>
              <div class="mo-spec-item"><i class="fa-solid fa-table-cells"></i> Cắt thuê: <strong>100 - ${b.availableArea}</strong></div>
              <div class="mo-spec-item"><i class="fa-solid fa-shield-halved"></i> Tiêu chuẩn: <strong>Quốc tế</strong></div>
            </div>

            <div class="mo-card-pricing">
              <div class="mo-price-box">
                <span class="price-usd">$${b.priceUsd} - $${b.priceMaxUsd}</span>
                <span class="price-unit">/ m² / tháng</span>
                <div class="price-vnd">Khoảng ${b.priceVnd} / m²</div>
              </div>
              <div class="mo-card-actions">
                <button class="btn-card-quote" onclick="openQuoteForBuilding('${b.name}')">Nhận Báo Giá</button>
                <button class="btn-card-view" onclick="openBuildingDetail(${b.id})">Xem Chi Tiết</button>
              </div>
            </div>
          </div>
        </article>
      `).join('');
    }

    /* 3. FILTERING LOGIC */
    function applyFilters() {
      const keyword = (document.getElementById("filter-keyword").value || "").toLowerCase().trim();
      const district = document.getElementById("filter-district").value;
      const grade = document.getElementById("filter-grade").value;
      const price = document.getElementById("filter-price").value;
      const area = document.getElementById("filter-area").value;

      let filtered = BUILDINGS_DATA.filter(b => {
        // Keyword
        if (keyword && !b.name.toLowerCase().includes(keyword) && !b.address.toLowerCase().includes(keyword)) {
          return false;
        }
        // District
        if (district !== "all" && b.district !== district) {
          return false;
        }
        // Grade
        if (grade !== "all" && !b.grade.includes(grade.replace(' / A+', '').replace(' / B+', '').replace(' / Giá rẻ', ''))) {
          return false;
        }
        // Price
        if (price === "under20" && b.priceUsd >= 20) return false;
        if (price === "20to35" && (b.priceUsd < 20 || b.priceUsd > 35)) return false;
        if (price === "over35" && b.priceUsd <= 35) return false;

        return true;
      });

      renderBuildings(filtered);
    }

    function quickFilterDistrict(dist) {
      document.getElementById("filter-district").value = dist;
      applyFilters();
      window.scrollTo({ top: 380, behavior: 'smooth' });
    }

    function quickFilterGrade(grd) {
      document.getElementById("filter-grade").value = grd;
      applyFilters();
      window.scrollTo({ top: 380, behavior: 'smooth' });
    }

    function quickFilterStatus(stat) {
      let filtered = BUILDINGS_DATA.filter(b => b.status === stat);
      renderBuildings(filtered);
      window.scrollTo({ top: 380, behavior: 'smooth' });
    }

    function resetAllFilters() {
      document.getElementById("filter-keyword").value = "";
      document.getElementById("filter-district").value = "all";
      document.getElementById("filter-grade").value = "all";
      document.getElementById("filter-price").value = "all";
      document.getElementById("filter-area").value = "all";
      renderBuildings(BUILDINGS_DATA);
    }

    function filterByDistrict(dist) {
      showPublicPage('page-home');
      quickFilterDistrict(dist);
    }

    function filterByGrade(grd) {
      showPublicPage('page-home');
      quickFilterGrade(grd);
    }

    function sortBuildings(criteria) {
      let list = [...BUILDINGS_DATA];
      if (criteria === "price-asc") {
        list.sort((a,b) => a.priceUsd - b.priceUsd);
      } else if (criteria === "price-desc") {
        list.sort((a,b) => b.priceUsd - a.priceUsd);
      } else if (criteria === "area-desc") {
        list.sort((a,b) => parseInt(b.floorPlate) - parseInt(a.floorPlate));
      }
      renderBuildings(list);
    }

    /* 4. DETAIL PAGE LOGIC */
    function openBuildingDetail(buildingId) {
      const b = BUILDINGS_DATA.find(x => x.id === buildingId);
      if (!b) return;
      currentSelectedBuilding = b;

      document.getElementById("detail-breadcrumb-name").innerText = b.name;
      document.getElementById("detail-hero-img").src = b.image;
      document.getElementById("detail-name").innerText = b.name;
      document.getElementById("detail-address").innerHTML = `<i class="fa-solid fa-location-dot" style="color:#ea580c;"></i> ${b.address}`;
      document.getElementById("detail-badge-grade").innerText = b.grade;
      document.getElementById("detail-badge-status").innerText = b.status;
      document.getElementById("detail-price-text").innerText = `$${b.priceUsd} - $${b.priceMaxUsd} / m² / tháng`;
      document.getElementById("detail-spec-structure").innerText = b.structure;
      document.getElementById("detail-spec-floorplate").innerText = b.floorPlate;

      // Render floors table
      const tbody = document.getElementById("detail-floors-tbody");
      tbody.innerHTML = b.floors.map(f => `
        <tr>
          <td><strong>${f.floor}</strong></td>
          <td><strong style="color:var(--mo-blue-accent);">${f.area}</strong></td>
          <td>${f.deliver}</td>
          <td><strong style="color:#ea580c;">${f.price}</strong></td>
          <td><span class="badge-filament success">${f.status}</span></td>
          <td>
            <button class="btn-mo-orange" style="padding:4px 10px;font-size:12px;" onclick="bookSpecificFloor('${b.name}', '${f.floor}', '${f.area}')">
              Đăng ký xem sàn này
            </button>
          </td>
        </tr>
      `).join('');

      showPublicPage('page-detail');
      window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function changeDetailImg(src) {
      document.getElementById("detail-hero-img").src = src;
    }

    function bookSurveyFromDetail() {
      showPublicPage('page-quote');
      document.getElementById("quote-building").value = `${currentSelectedBuilding.name} - ${currentSelectedBuilding.district}`;
      window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function bookSpecificFloor(bName, floor, area) {
      showPublicPage('page-quote');
      document.getElementById("quote-building").value = `${currentSelectedBuilding.name} - ${currentSelectedBuilding.district}`;
      document.getElementById("quote-notes").value = `Tôi quan tâm mặt bằng ${floor} diện tích ${area} của ${bName}. Vui lòng sắp xếp lịch khảo sát thực tế.`;
      window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    /* 5. PUBLIC SPA NAVIGATION */
    function showPublicPage(pageId) {
      document.querySelectorAll('#portal-wrapper .page-section').forEach(sec => {
        sec.classList.remove('active');
      });
      const target = document.getElementById(pageId);
      if (target) target.classList.add('active');

      // Update navbar active state
      document.querySelectorAll('.mo-nav-item').forEach(item => item.classList.remove('active'));
      if (pageId === 'page-home') {
        const first = document.querySelector('.mo-nav-item');
        if (first) first.classList.add('active');
      }
    }

    /* 6. SWITCHING BETWEEN MAISON OFFICE PORTAL & FILAMENT ADMIN SHELL */
    function switchView(viewType) {
      const portal = document.getElementById("portal-wrapper");
      const admin = document.getElementById("admin-wrapper");

      if (viewType === 'admin-view') {
        portal.style.display = 'none';
        admin.style.display = 'block';
        window.scrollTo({ top: 0, behavior: 'smooth' });
        renderAdminTables();
      } else {
        admin.style.display = 'none';
        portal.style.display = 'block';
        window.scrollTo({ top: 0, behavior: 'smooth' });
      }
    }

    function handleRoleSwitch(role) {
      switchView('admin-view');
      const selectHeader = document.getElementById("filament-role-select");
      if (selectHeader) selectHeader.value = role;

      const sidebarRoleBadge = document.getElementById("sidebar-role-badge");
      const userName = document.getElementById("filament-user-name");
      const userSub = document.getElementById("filament-user-sub");
      const userAvatar = document.getElementById("filament-user-avatar");

      const groupAdmin = document.getElementById("nav-group-admin");
      const groupSales = document.getElementById("nav-group-sales");
      const groupTenant = document.getElementById("nav-group-tenant");

      if (role === 'admin') {
        sidebarRoleBadge.innerText = 'Admin / BQL';
        userName.innerText = 'Nguyễn Quản Trị';
        userSub.innerText = 'Ban Quản Lý Tòa Nhà & Admin';
        userAvatar.innerText = 'AD';
        groupAdmin.style.display = 'block';
        groupSales.style.display = 'block';
        groupTenant.style.display = 'none';
        switchFilamentTab('tab-admin-dashboard', document.querySelector('#nav-group-admin .filament-link'));
      } else if (role === 'sales') {
        sidebarRoleBadge.innerText = 'Sales / Môi Giới';
        userName.innerText = 'Lê Thị Thu Thủy';
        userSub.innerText = 'Chuyên viên Báo giá & Leasing';
        userAvatar.innerText = 'SL';
        groupAdmin.style.display = 'none';
        groupSales.style.display = 'block';
        groupTenant.style.display = 'none';
        switchFilamentTab('tab-sales-leads', document.querySelector('#nav-group-sales .filament-link'));
      } else if (role === 'tenant') {
        sidebarRoleBadge.innerText = 'Khách Thuê';
        userName.innerText = 'Trần Văn Nam';
        userSub.innerText = 'Công ty TNHH An Khang Tech';
        userAvatar.innerText = 'AK';
        groupAdmin.style.display = 'none';
        groupSales.style.display = 'none';
        groupTenant.style.display = 'block';
        switchFilamentTab('tab-tenant-workspace', document.querySelector('#nav-group-tenant .filament-link'));
      } else if (role === 'portal') {
        switchView('portal-view');
      }
    }

    function switchFilamentTab(tabId, el) {
      document.querySelectorAll('.filament-tab-pane').forEach(pane => {
        pane.style.display = 'none';
        pane.classList.remove('active');
      });
      const target = document.getElementById(tabId);
      if (target) {
        target.style.display = 'block';
        target.classList.add('active');
      }

      document.querySelectorAll('.filament-link').forEach(link => link.classList.remove('active'));
      if (el) el.classList.add('active');
      window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    /* 7. POPULATING FILAMENT ADMIN TABLES */
    function renderAdminTables() {
      // 1. Admin Buildings Table
      const bTable = document.getElementById("admin-buildings-tbody");
      if (bTable) {
        bTable.innerHTML = BUILDINGS_DATA.map(b => `
          <tr>
            <td><strong>${b.name}</strong></td>
            <td>${b.district}</td>
            <td><span class="badge-filament info">${b.grade}</span></td>
            <td>${b.totalArea}</td>
            <td><strong style="color:#10b981;">85%</strong></td>
            <td>$${b.priceUsd} / m²</td>
            <td>
              <button class="btn-mo-outline" style="padding:4px 8px;font-size:12px;" onclick="openBuildingDetail(${b.id})">Sửa sàn</button>
            </td>
          </tr>
        `).join('');
      }

      // 2. Admin Tickets Table
      const tAdminTable = document.getElementById("admin-tickets-tbody");
      if (tAdminTable) {
        tAdminTable.innerHTML = TICKETS_DATA.map(t => `
          <tr>
            <td><strong>#${t.id}</strong></td>
            <td><strong>${t.tenant}</strong></td>
            <td>${t.location}</td>
            <td>${t.title}</td>
            <td><span class="badge-filament ${t.priority === 'Khẩn cấp' ? 'danger' : 'info'}">${t.priority}</span></td>
            <td><span class="badge-filament ${t.statusBadge}">${t.status}</span></td>
            <td>
              <button class="btn-mo-primary" style="padding:4px 8px;font-size:12px;" onclick="alert('Đã cập nhật trạng thái xử lý cho ticket #${t.id}!')">Xử lý</button>
            </td>
          </tr>
        `).join('');
      }

      // 3. Sales Leads Table
      const sLeadsTable = document.getElementById("sales-leads-tbody");
      if (sLeadsTable) {
        sLeadsTable.innerHTML = LEADS_DATA.map(l => `
          <tr>
            <td>${l.date}</td>
            <td><strong>${l.name}</strong></td>
            <td>${l.company}</td>
            <td><span style="color:var(--mo-primary);font-weight:600;">${l.building}</span></td>
            <td>${l.area}</td>
            <td><span class="badge-filament ${l.stageBadge}">${l.stage}</span></td>
            <td>
              <button class="btn-mo-orange" style="padding:4px 8px;font-size:12px;" onclick="alert('Mở form gửi bảng báo giá PDF cho doanh nghiệp: ${l.company}')">Gửi báo giá</button>
            </td>
          </tr>
        `).join('');
      }

      // 4. Tenant Tickets Table
      const tUserTable = document.getElementById("tenant-tickets-tbody");
      if (tUserTable) {
        const myTickets = TICKETS_DATA.filter(x => x.tenant.includes("An Khang"));
        tUserTable.innerHTML = myTickets.map(t => `
          <tr>
            <td><strong>#${t.id}</strong></td>
            <td>Bảo trì kỹ thuật</td>
            <td>${t.title}</td>
            <td>${t.time}</td>
            <td><span class="badge-filament ${t.priority === 'Khẩn cấp' ? 'danger' : 'info'}">${t.priority}</span></td>
            <td><span class="badge-filament ${t.statusBadge}">${t.status}</span></td>
          </tr>
        `).join('');
      }
    }

    /* 8. FORMS & MODALS HANDLERS */
    function openQuoteModal() {
      document.getElementById("quote-modal").classList.add("show");
    }
    function closeQuoteModal() {
      document.getElementById("quote-modal").classList.remove("show");
    }
    function openQuoteForBuilding(bName) {
      document.getElementById("modal-target").value = bName;
      openQuoteModal();
    }
    async function handleQuickQuoteModal(e) {
      e.preventDefault();
      const name = document.getElementById("modal-name").value;
      const phone = document.getElementById("modal-phone").value;
      const target = document.getElementById("modal-target").value;
      const area = document.getElementById("modal-area").value;

      // Tìm building id từ danh sách hiện tại
      const matchedB = BUILDINGS_DATA.find(b => b.name === target) || BUILDINGS_DATA[0];
      const buildingId = (matchedB && typeof matchedB.id === 'string' && matchedB.id.length > 20) ? matchedB.id : null;

      // Gửi API thật tới CRM Leads Backend nếu có building_id hợp lệ
      if (buildingId) {
        try {
          const cleanPhone = phone.replace(/[^0-9]/g, '');
          const payload = {
            contact_full_name: name,
            phone: phone,
            email: `lead_${cleanPhone}@officeleasing.vn`,
            building_id: buildingId,
            desired_area_sqm: parseFloat(area) || 100,
            customer_note: `[Quick Quote Modal] Yêu cầu báo giá nhanh tòa nhà: ${target}`
          };

          const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
          fetch('/api/v1/portal/leads', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'Accept': 'application/json',
              'X-CSRF-TOKEN': token || ''
            },
            body: JSON.stringify(payload)
          }).catch(err => console.log('Lead API Sync Note:', err));
        } catch (e) {
          console.error(e);
        }
      }

      // Add to leads database hiển thị giao diện
      LEADS_DATA.unshift({
        id: 100 + LEADS_DATA.length + 1,
        date: "Hôm nay",
        name: `${name} (${phone})`,
        company: "Doanh nghiệp mới liên hệ",
        building: target || "Khảo sát chung",
        area: area || "150 m²",
        stage: "Tiếp nhận mới",
        stageBadge: "warning"
      });

      closeQuoteModal();
      alert(`Cảm ơn bạn ${name}! Chuyên viên tư vấn Office đã tiếp nhận yêu cầu và sẽ liên hệ qua SĐT ${phone} trong vòng 30 phút.`);
      renderAdminTables();
    }

    async function handleLeadSubmit(e) {
      e.preventDefault();
      const name = document.getElementById("quote-name").value;
      const phone = document.getElementById("quote-phone").value;
      const company = document.getElementById("quote-company").value;
      const building = document.getElementById("quote-building").value;
      const area = document.getElementById("quote-area-need").value;
      const notes = document.getElementById("quote-notes")?.value || "";

      // Gửi API thật tới CRM Leads Backend
      const matchedB = BUILDINGS_DATA.find(b => building.includes(b.name)) || BUILDINGS_DATA[0];
      const buildingId = (matchedB && typeof matchedB.id === 'string' && matchedB.id.length > 20) ? matchedB.id : null;

      if (buildingId) {
        try {
          const cleanPhone = phone.replace(/[^0-9]/g, '');
          const payload = {
            contact_full_name: name,
            company_name: company,
            phone: phone,
            email: `lead_${cleanPhone}@officeleasing.vn`,
            building_id: buildingId,
            desired_area_sqm: parseFloat(area) || 150,
            customer_note: `[Form Báo Giá] Quan tâm: ${building}. Ghi chú: ${notes}`
          };

          const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
          fetch('/api/v1/portal/leads', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'Accept': 'application/json',
              'X-CSRF-TOKEN': token || ''
            },
            body: JSON.stringify(payload)
          }).catch(err => console.log('Lead API Sync Note:', err));
        } catch (e) {
          console.error(e);
        }
      }

      LEADS_DATA.unshift({
        id: 100 + LEADS_DATA.length + 1,
        date: "Hôm nay",
        name: `${name} (${phone})`,
        company: company,
        building: building,
        area: area || "200 m²",
        stage: "Tiếp nhận mới",
        stageBadge: "warning"
      });

      alert(`Đã ghi nhận yêu cầu báo giá của ${company} thành công!\nChuyên viên tư vấn của Office sẽ liên hệ ngay qua số điện thoại ${phone}.`);
      document.getElementById("lead-consult-form").reset();
      showPublicPage('page-home');
      renderAdminTables();
    }

    function openTenantTicketModal() {
      document.getElementById("ticket-modal").classList.add("show");
    }
    function closeTenantTicketModal() {
      document.getElementById("ticket-modal").classList.remove("show");
    }
    function handleTenantNewTicket(e) {
      e.preventDefault();
      const title = document.getElementById("ticket-title").value;
      const desc = document.getElementById("ticket-desc").value;
      const priority = document.getElementById("ticket-priority").value;

      const newTicket = {
        id: "TK-2026-" + Math.floor(100 + Math.random() * 900),
        tenant: "Công ty CP An Khang",
        location: "Vietcombank Tower - T12",
        title: title,
        priority: priority,
        status: "Chờ tiếp nhận",
        statusBadge: "warning",
        time: "Vừa xong"
      };

      TICKETS_DATA.unshift(newTicket);
      closeTenantTicketModal();
      alert("Đã gửi ticket hỗ trợ lên Ban Quản Lý thành công! Kỹ thuật viên sẽ xử lý trong thời gian sớm nhất.");
      renderAdminTables();
    }

    function calculateQuickQuote() {
      const pricePerM2 = parseFloat(document.getElementById("calc-building").value) || 0;
      const area = parseFloat(document.getElementById("calc-area").value) || 0;
      const service = parseFloat(document.getElementById("calc-service").value) || 0;

      const totalUsd = area * (pricePerM2 + service);
      const totalVnd = totalUsd * 24500;

      document.getElementById("calc-total-usd").innerText = `$${totalUsd.toLocaleString()} / tháng`;
      document.getElementById("calc-total-vnd").innerText = `Tương đương: ~${totalVnd.toLocaleString('vi-VN')} VNĐ / tháng (Chưa gồm VAT)`;
    }

    // Initialize on load
    window.addEventListener("DOMContentLoaded", () => {
      renderBuildings(BUILDINGS_DATA);
      renderAdminTables();
      calculateQuickQuote();
    });
  </script>
</body>
</html>
