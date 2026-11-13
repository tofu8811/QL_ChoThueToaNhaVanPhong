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
      --mo-bg: #ffffff;
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
    html {
      scroll-behavior: smooth;
      overflow-x: clip;
    }
    body {
      margin: 0 !important;
      padding: 0 !important;
      width: 100%;
      font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
      color: var(--mo-text-main);
      background-color: #ffffff;
      line-height: 1.5;
      -webkit-font-smoothing: antialiased;
    }
    #portal-wrapper {
      background-color: #ffffff;
      margin: 0 !important;
      padding: 0 !important;
    }
    #admin-wrapper {
      margin: 0 !important;
      padding: 0 !important;
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
      white-space: nowrap !important;
      flex-shrink: 0;
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
      white-space: nowrap !important;
      flex-shrink: 0;
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
      white-space: nowrap !important;
      flex-shrink: 0;
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
      margin: 0 !important;
      background-color: var(--mo-primary-dark);
      height: 34px;
      width: 100%;
      border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    }
    .mo-topbar .container,
    .mo-header .container {
      gap: 16px;
      max-width: 1400px;
      width: 100%;
      margin: 0 auto;
      padding: 0 24px;
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

    /* Main Navbar (Smart Sticky Header / Headroom) */
    .mo-header {
      margin: 0 !important;
      background: #ffffff;
      box-shadow: 0 2px 10px rgba(0,0,0,0.06);
      position: sticky;
      top: 0;
      left: 0;
      right: 0;
      width: 100%;
      z-index: 1000;
      transition: transform 0.35s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.25s ease;
      will-change: transform;
    }
    .mo-header.header-hidden {
      transform: translateY(-115%) !important;
      box-shadow: none !important;
      pointer-events: none !important;
    }
    .mo-header.header-scrolled {
      box-shadow: 0 8px 24px -4px rgba(15, 23, 42, 0.12), 0 4px 6px -2px rgba(15, 23, 42, 0.05);
    }
    .mo-header .container {
      gap: 16px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      height: 74px;
    }
    .mo-logo {
      flex-shrink: 0;
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
      white-space: nowrap !important;
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
      flex-shrink: 0;
      display: flex;
      align-items: center;
      gap: 16px;
    }
    .mo-nav-item {
      white-space: nowrap !important;
      flex-shrink: 0;
      letter-spacing: 0.15px;
      font-size: 13.5px;
      font-weight: 600;
      color: #334155;
      padding: 8px 4px;
      position: relative;
      cursor: pointer;
      display: flex;
      align-items: center;
      gap: 4px;
      background: none;
      border: none;
      font-family: inherit;
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

    /* Sub-dropdown cho menu địa bàn tỉnh thành */
    .mo-nav-dropdown-wrap {
      position: relative;
      display: inline-flex;
      align-items: center;
    }
    .mo-nav-dropdown-wrap:hover .mo-nav-dropdown,
    .mo-nav-dropdown-wrap.open .mo-nav-dropdown {
      opacity: 1;
      visibility: visible;
      transform: translateY(0);
      pointer-events: auto;
    }
    .mo-nav-dropdown {
      position: absolute;
      top: 100%;
      left: 0;
      margin-top: 4px;
      min-width: 230px;
      background: #ffffff;
      border: 1px solid #e2e8f0;
      border-radius: 8px;
      box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.12), 0 8px 10px -6px rgba(0, 0, 0, 0.08);
      padding: 6px 0;
      z-index: 1050;
      opacity: 0;
      visibility: hidden;
      transform: translateY(6px);
      transition: opacity 0.2s cubic-bezier(0.16, 1, 0.3, 1), transform 0.2s cubic-bezier(0.16, 1, 0.3, 1);
      pointer-events: none;
    }
    /* Cầu đệm tàng hình giúp rê chuột xuống dropdown không bao giờ bị mất */
    .mo-nav-dropdown::before {
      content: '';
      position: absolute;
      top: -16px;
      left: 0;
      right: 0;
      height: 16px;
      background: transparent;
    }
    .mo-nav-dropdown-wrap:hover .nav-caret,
    .mo-nav-dropdown-wrap.open .nav-caret {
      transform: rotate(180deg);
    }
    .mo-dropdown-link {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 8px 16px;
      font-size: 13px;
      font-weight: 500;
      color: #334155;
      text-decoration: none;
      transition: background 0.15s, color 0.15s;
    }
    .mo-dropdown-link:hover {
      background: #f8fafc;
      color: var(--mo-orange);
    }
    .mo-dropdown-link .badge-num {
      font-size: 11px;
      font-weight: 600;
      background: #f1f5f9;
      color: #64748b;
      padding: 2px 7px;
      border-radius: 9999px;
    }
    .mo-dropdown-divider {
      height: 1px;
      background: #e2e8f0;
      margin: 4px 0;
    }
    .mo-dropdown-empty {
      padding: 10px 14px;
      font-size: 12.5px;
      color: #64748b;
      display: flex;
      align-items: center;
      gap: 8px;
      background: #f8fafc;
      border-radius: 6px;
      margin: 4px 8px;
      line-height: 1.4;
    }
    .nav-caret {
      font-size: 10px;
      margin-left: 2px;
      transition: transform 0.2s;
    }
    .mo-nav-dropdown-wrap:hover .nav-caret {
      transform: rotate(180deg);
    }

    /* Kịch bản 1: Thẻ Empty State chuyên nghiệp & Thu thập khách hàng tiềm năng */
    .mo-empty-lead-card {
      grid-column: 1 / -1;
      background: #ffffff;
      border: 1px solid #e2e8f0;
      border-radius: 16px;
      padding: 36px 32px;
      box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.05);
      position: relative;
      overflow: hidden;
    }
    .mo-empty-lead-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 4px;
      background: linear-gradient(90deg, #ea580c, #f59e0b, #2563eb);
    }
    .mo-empty-lead-badge {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      background: #fff7ed;
      color: #ea580c;
      border: 1px solid #fed7aa;
      font-size: 12px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      padding: 4px 12px;
      border-radius: 9999px;
      margin-bottom: 14px;
    }
    .mo-empty-lead-title {
      font-size: 21px;
      font-weight: 800;
      color: #0f172a;
      margin-bottom: 10px;
      line-height: 1.35;
    }
    .mo-empty-lead-desc {
      font-size: 14.5px;
      color: #64748b;
      max-width: 760px;
      line-height: 1.6;
      margin-bottom: 22px;
    }
    .mo-empty-lead-form {
      background: #f8fafc;
      border: 1px solid #e2e8f0;
      border-radius: 12px;
      padding: 18px;
      max-width: 820px;
      margin-bottom: 20px;
    }
    .mo-empty-lead-grid {
      display: grid;
      grid-template-columns: 1.2fr 1.2fr 1fr auto;
      gap: 12px;
      align-items: end;
    }
    .mo-empty-input-group label {
      display: block;
      font-size: 12px;
      font-weight: 600;
      color: #475569;
      margin-bottom: 6px;
    }
    .mo-empty-input {
      width: 100%;
      height: 40px;
      background: #ffffff;
      border: 1px solid #cbd5e1;
      border-radius: 8px;
      padding: 0 12px;
      font-size: 13.5px;
      color: #1e293b;
      transition: border-color 0.15s, box-shadow 0.15s;
    }
    .mo-empty-input:focus {
      border-color: #ea580c;
      box-shadow: 0 0 0 3px rgba(234, 88, 12, 0.15);
    }
    .mo-empty-lead-footer {
      display: flex;
      align-items: center;
      gap: 16px;
      flex-wrap: wrap;
      padding-top: 16px;
      border-top: 1px dashed #e2e8f0;
    }
    @media (max-width: 860px) {
      .mo-empty-lead-grid {
        grid-template-columns: 1fr;
      }
      .mo-empty-lead-card {
        padding: 24px 18px;
      }
    }

    .mo-header-actions button,
    .mo-header-actions a {
      white-space: nowrap !important;
      flex-shrink: 0;
    }
    .mo-header-actions {
      flex-shrink: 0;
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
      background: linear-gradient(180deg, rgba(12, 33, 62, 0.84) 0%, rgba(8, 20, 38, 0.93) 100%);
      color: #ffffff;
      padding: 75px 0 155px;
      min-height: 480px;
      position: relative;
      overflow: hidden;
      display: flex;
      flex-direction: column;
      justify-content: center;
    }
    .mo-hero-banner::before {
      content: '';
      position: absolute;
      top: 0; left: 0; right: 0; bottom: 0;
      background: url('https://images.unsplash.com/photo-1583417319070-4a69db38a482?auto=format&fit=crop&w=2200&q=85') center 35%/cover no-repeat;
      opacity: 0.40;
      pointer-events: none;
      filter: brightness(0.95) contrast(1.08);
      transform: scale(1.02);
      transition: transform 6s ease-out;
    }
    .mo-hero-banner:hover::before {
      transform: scale(1.05);
    }
    .mo-breadcrumbs {
      display: flex;
      align-items: center;
      gap: 8px;
      font-size: 13.5px;
      color: #94a3b8;
      margin-bottom: 20px;
      position: relative;
      z-index: 2;
    }
    .mo-breadcrumbs a { color: #e2e8f0; font-weight: 500; }
    .mo-breadcrumbs a:hover { color: #f97316; }
    .mo-hero-title {
      font-size: 34px;
      font-weight: 800;
      color: #ffffff;
      line-height: 1.3;
      margin-bottom: 14px;
      max-width: 950px;
      position: relative;
      z-index: 2;
      text-shadow: 0 2px 10px rgba(0, 0, 0, 0.45);
    }
    .mo-hero-desc {
      font-size: 16px;
      color: #e2e8f0;
      max-width: 860px;
      line-height: 1.68;
      position: relative;
      z-index: 2;
      text-shadow: 0 1px 6px rgba(0, 0, 0, 0.35);
    }

    /* Maison Office Floating Filter Box */
    .mo-filter-container {
      margin-top: -75px;
      position: relative;
      z-index: 20;
      margin-bottom: 40px;
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
      grid-template-columns: 1.4fr 1.1fr 1.1fr 0.9fr 1fr 1fr auto;
      gap: 10px;
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

    /* Market Highlights Counter (5 Cards Maison Office Style on 1 Row) */
    .mo-stats-strip {
      background: transparent;
      border: none;
      border-radius: 0;
      padding: 0;
      margin-bottom: 32px;
      display: grid;
      grid-template-columns: repeat(5, 1fr);
      gap: 16px;
      text-align: left;
    }
    .mo-stat-item {
      background: #ffffff;
      border: 1px solid #e2e8f0;
      border-radius: 16px;
      padding: 24px 20px;
      display: flex;
      flex-direction: column;
      justify-content: flex-start;
      align-items: flex-start;
      text-align: left;
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
      transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
      min-height: 155px;
    }
    .mo-stat-item:hover {
      transform: translateY(-4px);
      box-shadow: 0 12px 24px -6px rgba(15, 23, 42, 0.08);
      border-color: #cbd5e1;
    }
    .mo-stat-num {
      font-size: clamp(23px, 2.1vw, 30px);
      font-weight: 800;
      color: #e11d48;
      line-height: 1.15;
      margin-bottom: 12px;
      letter-spacing: -0.5px;
      white-space: nowrap;
    }
    .mo-stat-label {
      font-size: 14px;
      color: #475569;
      font-weight: 600;
      line-height: 1.5;
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
      display: flex;
      flex-direction: column;
      gap: 12px;
    }
    .detail-main-img {
      height: 330px;
      border-radius: var(--radius-md);
      overflow: hidden;
      box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
      background: #0f172a;
    }
    .detail-main-img img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      transition: all 0.25s ease-in-out;
    }
    .detail-thumbs-slider {
      position: relative;
      width: 100%;
      display: flex;
      align-items: center;
    }
    .thumb-slider-btn {
      position: absolute;
      top: 50%;
      transform: translateY(-50%);
      width: 32px;
      height: 32px;
      border-radius: 50%;
      background: rgba(15, 23, 42, 0.75);
      backdrop-filter: blur(6px);
      -webkit-backdrop-filter: blur(6px);
      color: #ffffff;
      border: 1px solid rgba(255, 255, 255, 0.25);
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      z-index: 10;
      font-size: 13px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
      transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .thumb-slider-btn:hover {
      background: var(--mo-orange);
      border-color: var(--mo-orange);
      color: #ffffff;
      transform: translateY(-50%) scale(1.1);
      box-shadow: 0 6px 16px rgba(234, 88, 12, 0.4);
    }
    .thumb-slider-btn:active {
      transform: translateY(-50%) scale(0.95);
    }
    .thumb-slider-btn.prev {
      left: 6px;
    }
    .thumb-slider-btn.next {
      right: 6px;
    }
    .detail-sub-thumbs {
      display: flex;
      flex-wrap: nowrap;
      gap: 8px;
      width: 100%;
      overflow-x: auto;
      overflow-y: hidden;
      padding: 4px 2px;
      scrollbar-width: none;
      -webkit-overflow-scrolling: touch;
      scroll-behavior: smooth;
    }
    .detail-sub-thumbs::-webkit-scrollbar {
      display: none;
    }
    .detail-sub-thumbs img {
      flex: 0 0 calc((100% - 32px) / 5);
      min-width: calc((100% - 32px) / 5);
      max-width: calc((100% - 32px) / 5);
      height: 62px;
      object-fit: cover;
      border-radius: 6px;
      cursor: pointer;
      border: 2px solid transparent;
      transition: all 0.2s ease-in-out;
      box-sizing: border-box;
      user-select: none;
    }
    .detail-sub-thumbs img:hover {
      opacity: 0.92;
      transform: translateY(-2px);
    }
    .detail-sub-thumbs img.active {
      border-color: var(--mo-orange);
      box-shadow: 0 0 0 1.5px var(--mo-orange);
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
    @media (max-width: 1200px) {
      .mo-stats-strip { grid-template-columns: repeat(3, 1fr); }
    }
    @media (max-width: 1024px) {
      .mo-filter-grid { grid-template-columns: 1fr 1fr; }
      .mo-content-grid { grid-template-columns: 1fr; }
      .mo-building-grid { grid-template-columns: 1fr; }
      .filament-widgets-grid { grid-template-columns: 1fr 1fr; }
      .mo-footer-grid { grid-template-columns: 1fr 1fr; }
      .detail-hero-grid { grid-template-columns: 1fr; }
    }
    @media (max-width: 768px) {
      .mo-nav {
      flex-shrink: 0; display: none; }
      .mo-topbar-right { display: none; }
      .filament-sidebar { display: none; }
      .filament-widgets-grid { grid-template-columns: 1fr; }
      .mo-stats-strip { grid-template-columns: repeat(2, 1fr); }
      .lead-form-grid { grid-template-columns: 1fr; }
      .mo-footer-grid { grid-template-columns: 1fr; }
    }
    @media (max-width: 480px) {
      .mo-stats-strip { grid-template-columns: 1fr; }
    }

    /* Login Modal Specific Styling */
    .login-tabs {
      display: flex;
      border-bottom: 2px solid #e2e8f0;
      margin-bottom: 20px;
    }
    .login-tab-btn {
      flex: 1;
      padding: 10px;
      text-align: center;
      background: none;
      border: none;
      font-weight: 600;
      font-size: 13.5px;
      color: #64748b;
      cursor: pointer;
      border-bottom: 2px solid transparent;
      margin-bottom: -2px;
      transition: all 0.2s;
    }
    .login-tab-btn.active {
      color: var(--mo-primary);
      border-bottom-color: var(--mo-orange);
    }
    .login-role-chips {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 10px;
      margin-bottom: 18px;
    }
    .role-sample-card {
      background: #f8fafc;
      border: 1px solid #e2e8f0;
      border-radius: var(--radius-sm);
      padding: 10px 12px;
      cursor: pointer;
      transition: all 0.2s;
      text-align: left;
    }
    .role-sample-card:hover {
      border-color: #2563eb;
      background: #eff6ff;
    }
    .role-sample-card strong {
      display: block;
      font-size: 13px;
      color: #1e293b;
    }
    .role-sample-card small {
      font-size: 11px;
      color: #64748b;
    }
    /* ==========================================================================
       USER PROFILE PILL & DROPDOWN MENU
       ========================================================================== */
    .user-profile-pill {
      background: #f8fafc;
      border: 1px solid #cbd5e1;
      padding: 4px 10px 4px 5px;
      border-radius: 24px;
      display: inline-flex;
      align-items: center;
      gap: 8px;
      cursor: pointer;
      transition: all 0.2s ease;
      white-space: nowrap !important;
      flex-shrink: 0;
    }
    .user-profile-pill:hover {
      background: #eff6ff;
      border-color: #93c5fd;
      box-shadow: 0 2px 8px rgba(37, 99, 235, 0.12);
    }
    .user-avatar-circle {
      width: 30px;
      height: 30px;
      border-radius: 50%;
      background: linear-gradient(135deg, #0f3361, #2563eb);
      color: #ffffff;
      font-weight: 700;
      font-size: 11.5px;
      display: flex;
      align-items: center;
      justify-content: center;
      box-shadow: 0 2px 6px rgba(15, 51, 97, 0.25);
    }
    .user-info-text {
      display: flex;
      flex-direction: column;
      text-align: left;
      line-height: 1.2;
    }
    .user-name-label {
      font-size: 13px;
      font-weight: 700;
      color: #0f3361;
      max-width: 110px;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }
    .user-role-tag {
      font-size: 10px;
      font-weight: 600;
      color: #2563eb;
      text-transform: uppercase;
      letter-spacing: 0.3px;
    }
    .user-dropdown-caret {
      font-size: 10px;
      color: #64748b;
      transition: transform 0.2s;
    }
    .user-profile-pill.active .user-dropdown-caret {
      transform: rotate(180deg);
    }

    .user-nav-dropdown {
      position: absolute;
      top: calc(100% + 8px);
      right: 0;
      width: 240px;
      background: #ffffff;
      border-radius: 12px;
      box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.15), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
      border: 1px solid #e2e8f0;
      padding: 8px 0;
      display: none;
      z-index: 1100;
      animation: dropdownFade 0.2s ease-out;
    }
    .user-nav-dropdown.show {
      display: block;
    }
    @keyframes dropdownFade {
      from { opacity: 0; transform: translateY(-6px); }
      to { opacity: 1; transform: translateY(0); }
    }
    .user-dropdown-header {
      padding: 10px 16px 8px;
    }
    .badge-role-pill {
      display: inline-block;
      font-size: 10px;
      font-weight: 700;
      color: #1e40af;
      background: #dbeafe;
      padding: 2px 8px;
      border-radius: 12px;
      text-transform: uppercase;
    }
    .user-dropdown-divider {
      height: 1px;
      background: #f1f5f9;
      margin: 6px 0;
    }
    .user-dropdown-item {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 9px 16px;
      font-size: 13px;
      font-weight: 500;
      color: #334155;
      transition: all 0.15s;
      cursor: pointer;
    }
    .user-dropdown-item:hover {
      background: #f8fafc;
      color: #0f3361;
    }
    .user-dropdown-item.text-danger {
      color: #dc2626;
    }
    .user-dropdown-item.text-danger:hover {
      background: #fef2f2;
      color: #b91c1c;
    }

    /* ==========================================================================
       MODAL LOGIN PREMIUM STYLING
       ========================================================================== */
    .login-modal-box {
      background: #ffffff;
      border-radius: 18px;
      overflow: hidden;
      box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.25);
      border: 1px solid #e2e8f0;
      max-width: 490px;
      width: 100%;
      margin: 20px;
      animation: modalSlideUp 0.25s ease-out;
    }
    @keyframes modalSlideUp {
      from { opacity: 0; transform: translateY(16px) scale(0.98); }
      to { opacity: 1; transform: translateY(0) scale(1); }
    }
    .login-modal-header {
      background: linear-gradient(135deg, #09203f 0%, #0f3361 60%, #1e3a8a 100%);
      color: #ffffff;
      padding: 24px 28px 20px;
      position: relative;
    }
    .login-header-badge {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      background: rgba(255, 255, 255, 0.12);
      border: 1px solid rgba(255, 255, 255, 0.2);
      color: #bfdbfe;
      font-size: 11px;
      font-weight: 600;
      padding: 3px 10px;
      border-radius: 20px;
      margin-bottom: 8px;
      backdrop-filter: blur(4px);
    }
    .login-modal-title {
      font-size: 20px;
      font-weight: 800;
      letter-spacing: -0.3px;
      margin: 0 0 4px;
      color: #ffffff;
    }
    .login-modal-sub {
      font-size: 13px;
      color: #94a3b8;
      margin: 0;
    }
    .login-modal-close {
      position: absolute;
      top: 18px;
      right: 18px;
      width: 32px;
      height: 32px;
      border-radius: 50%;
      background: rgba(255, 255, 255, 0.1);
      color: #ffffff;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      font-size: 16px;
      transition: all 0.2s;
    }
    .login-modal-close:hover {
      background: rgba(255, 255, 255, 0.25);
      transform: rotate(90deg);
    }
    .login-modal-body {
      padding: 24px 28px;
    }
    .sample-roles-grid {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 8px;
      margin-bottom: 18px;
    }
    .sample-role-pill {
      background: #f8fafc;
      border: 1px solid #e2e8f0;
      border-radius: 10px;
      padding: 9px 12px;
      cursor: pointer;
      transition: all 0.2s ease;
      display: flex;
      align-items: center;
      gap: 10px;
      text-align: left;
    }
    .sample-role-pill:hover, .sample-role-pill.selected {
      border-color: #2563eb;
      background: #eff6ff;
      transform: translateY(-1px);
      box-shadow: 0 3px 8px rgba(37, 99, 235, 0.12);
    }
    .sample-role-icon {
      width: 30px;
      height: 30px;
      border-radius: 8px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 14px;
      flex-shrink: 0;
    }
    .sample-role-meta strong {
      display: block;
      font-size: 12.5px;
      font-weight: 700;
      color: #1e293b;
      line-height: 1.2;
    }
    .sample-role-meta small {
      display: block;
      font-size: 11px;
      color: #64748b;
      margin-top: 2px;
    }
    .login-divider-text {
      display: flex;
      align-items: center;
      text-align: center;
      margin: 16px 0;
      color: #94a3b8;
      font-size: 11.5px;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }
    .login-divider-text::before,
    .login-divider-text::after {
      content: '';
      flex: 1;
      border-bottom: 1px solid #e2e8f0;
    }
    .login-divider-text span {
      padding: 0 10px;
    }
    .input-icon-wrapper {
      position: relative;
      margin-bottom: 15px;
    }
    .input-icon-wrapper label {
      display: block;
      font-size: 12.5px;
      font-weight: 600;
      color: #334155;
      margin-bottom: 6px;
    }
    .input-icon-wrapper .field-icon {
      position: absolute;
      left: 14px;
      bottom: 11px;
      color: #94a3b8;
      font-size: 15px;
      pointer-events: none;
    }
    .input-icon-wrapper input {
      width: 100%;
      padding: 10px 42px 10px 38px;
      border: 1px solid #cbd5e1;
      border-radius: 8px;
      font-size: 13.5px;
      color: #1e293b;
      background: #ffffff;
      transition: all 0.2s;
    }
    .input-icon-wrapper input:focus {
      border-color: #2563eb;
      box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
    }
    .input-icon-wrapper .toggle-pwd-btn {
      position: absolute;
      right: 12px;
      bottom: 10px;
      background: none;
      border: none;
      color: #94a3b8;
      cursor: pointer;
      padding: 4px;
      font-size: 14px;
    }
    .input-icon-wrapper .toggle-pwd-btn:hover {
      color: #334155;
    }
    .btn-login-submit {
      width: 100%;
      background: linear-gradient(135deg, #0f3361 0%, #1e40af 100%);
      color: #ffffff;
      border: none;
      padding: 12px 20px;
      border-radius: 8px;
      font-size: 14px;
      font-weight: 700;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      box-shadow: 0 4px 12px rgba(15, 51, 97, 0.25);
      transition: all 0.2s ease;
    }
    .btn-login-submit:hover {
      background: linear-gradient(135deg, #09203f 0%, #1d4ed8 100%);
      transform: translateY(-1px);
      box-shadow: 0 6px 16px rgba(15, 51, 97, 0.35);
    }

    /* ==========================================================================
       PAGINATION STYLING (MAISON OFFICE STYLE)
       ========================================================================== */
    .mo-pagination-wrapper {
      margin-top: 32px;
      padding: 16px 22px;
      background: #ffffff;
      border: 1px solid #e2e8f0;
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      flex-wrap: wrap;
      gap: 16px;
      box-shadow: 0 1px 3px rgba(0,0,0,0.04);
    }
    .mo-pagination-info {
      font-size: 13.5px;
      color: #64748b;
    }
    .mo-pagination-info strong {
      color: #0f3361;
      font-weight: 700;
    }
    .mo-pagination-nav {
      display: flex;
      align-items: center;
      gap: 6px;
    }
    .mo-page-btn {
      min-width: 38px;
      height: 38px;
      padding: 0 12px;
      border-radius: 8px;
      border: 1px solid #cbd5e1;
      background: #ffffff;
      color: #334155;
      font-size: 13.5px;
      font-weight: 600;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      transition: all 0.2s ease;
    }
    .mo-page-btn:hover:not(:disabled) {
      background: #f1f5f9;
      border-color: #0f3361;
      color: #0f3361;
      transform: translateY(-1px);
    }
    .mo-page-btn.active {
      background: #0f3361;
      border-color: #0f3361;
      color: #ffffff;
      box-shadow: 0 3px 8px rgba(15, 51, 97, 0.25);
    }
    .mo-page-btn:disabled {
      opacity: 0.4;
      cursor: not-allowed;
      background: #f8fafc;
      border-color: #e2e8f0;
    }
    .mo-page-dots {
      padding: 0 6px;
      color: #94a3b8;
      font-weight: 700;
      font-size: 14px;
    }
    .mo-pagination-pagesize {
      display: flex;
      align-items: center;
      gap: 8px;
      font-size: 13px;
      color: #64748b;
    }
    .mo-pagination-pagesize select {
      padding: 6px 12px;
      border: 1px solid #cbd5e1;
      border-radius: 6px;
      background: #ffffff;
      font-size: 13px;
      color: #334155;
      font-weight: 600;
      cursor: pointer;
      outline: none;
    }
    .mo-pagination-pagesize select:focus {
      border-color: #2563eb;
    }
    .mo-pagination-wrapper svg,
    nav[role="navigation"] svg {
      width: 14px !important;
      height: 14px !important;
      max-width: 14px !important;
      max-height: 14px !important;
      display: inline-block !important;
      vertical-align: middle;
    }

    /* ==========================================================================
       USER PROFILE & SECURITY MODAL STYLES (ENDPOINTS 67 - 71)
       ========================================================================== */
    .profile-modal-box {
      background: #ffffff;
      border-radius: 16px;
      width: 100%;
      max-width: 680px;
      box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.25);
      border: 1px solid #e2e8f0;
      overflow: hidden;
      display: flex;
      flex-direction: column;
      max-height: 90vh;
      animation: modalSlideUp 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .profile-tabs-header {
      display: flex;
      gap: 8px;
      margin-top: 14px;
      border-bottom: 1px solid #e2e8f0;
      padding-bottom: 2px;
    }
    .profile-tab-btn {
      background: transparent;
      border: none;
      padding: 8px 14px;
      font-size: 13px;
      font-weight: 600;
      color: #64748b;
      cursor: pointer;
      border-radius: 6px 6px 0 0;
      display: inline-flex;
      align-items: center;
      gap: 6px;
      position: relative;
      transition: all 0.2s ease;
    }
    .profile-tab-btn:hover {
      color: #0f3361;
      background: #f8fafc;
    }
    .profile-tab-btn.active {
      color: #2563eb;
      font-weight: 700;
    }
    .profile-tab-btn.active::after {
      content: '';
      position: absolute;
      bottom: -3px;
      left: 0;
      right: 0;
      height: 3px;
      background: #2563eb;
      border-radius: 3px 3px 0 0;
    }
    .profile-modal-body {
      padding: 22px 24px;
      overflow-y: auto;
      max-height: calc(90vh - 120px);
    }
    .profile-tab-pane {
      display: none;
    }
    .profile-tab-pane.active {
      display: block;
      animation: fadeIn 0.25s ease;
    }
    .profile-user-card {
      display: flex;
      align-items: center;
      gap: 16px;
      padding: 16px 18px;
      background: linear-gradient(135deg, #f8fafc 0%, #eff6ff 100%);
      border: 1px solid #dbeafe;
      border-radius: 12px;
    }
    .profile-big-avatar {
      width: 58px;
      height: 58px;
      border-radius: 50%;
      background: linear-gradient(135deg, #0f3361, #2563eb);
      color: #ffffff;
      font-size: 20px;
      font-weight: 800;
      display: flex;
      align-items: center;
      justify-content: center;
      box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25);
      flex-shrink: 0;
    }
    .profile-user-details {
      flex: 1;
      min-width: 0;
    }
    .profile-user-name {
      font-size: 16.5px;
      font-weight: 800;
      color: #0f3361;
      margin: 0;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }
    .profile-meta-row {
      display: flex;
      flex-wrap: wrap;
      gap: 14px;
      margin-top: 6px;
      font-size: 12.5px;
      color: #475569;
    }
    .profile-meta-row span {
      display: inline-flex;
      align-items: center;
      gap: 5px;
    }
    .profile-meta-row i {
      color: #2563eb;
      font-size: 12px;
    }
    .profile-buildings-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
      gap: 10px;
      max-height: 240px;
      overflow-y: auto;
      padding: 2px;
    }
    .profile-building-card {
      background: #ffffff;
      border: 1px solid #e2e8f0;
      border-radius: 8px;
      padding: 10px 12px;
      display: flex;
      align-items: center;
      gap: 10px;
      transition: all 0.2s ease;
    }
    .profile-building-card:hover {
      border-color: #93c5fd;
      background: #f8fafc;
      transform: translateY(-1px);
    }
    .profile-building-icon {
      width: 32px;
      height: 32px;
      border-radius: 6px;
      background: #eff6ff;
      color: #2563eb;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 14px;
      flex-shrink: 0;
    }
    .profile-building-info {
      flex: 1;
      min-width: 0;
    }
    .profile-building-name {
      font-size: 13px;
      font-weight: 700;
      color: #1e293b;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }
    .profile-building-meta {
      font-size: 11px;
      color: #64748b;
      display: flex;
      align-items: center;
      gap: 6px;
      margin-top: 2px;
    }
    .session-info-box {
      background: #f8fafc;
      border: 1px solid #e2e8f0;
      border-radius: 12px;
      padding: 18px 20px;
    }

    /* ==========================================================================
       MODULE 4: TICKETS & MAINTENANCE UI STYLES (ENDPOINTS 57 - 66)
       ========================================================================== */
    .ticket-detail-modal-box {
      background: #ffffff;
      border-radius: 16px;
      width: 100%;
      max-width: 860px;
      box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.25);
      border: 1px solid #e2e8f0;
      overflow: hidden;
      display: flex;
      flex-direction: column;
      max-height: 92vh;
      animation: modalSlideUp 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .ticket-badge-pill {
      display: inline-flex;
      align-items: center;
      gap: 5px;
      padding: 3px 10px;
      border-radius: 20px;
      font-size: 11.5px;
      font-weight: 700;
      border: 1px solid transparent;
    }
    .ticket-badge-pill.status-NEW { background: #eff6ff; color: #1d4ed8; border-color: #bfdbfe; }
    .ticket-badge-pill.status-IN_PROGRESS { background: #fefce8; color: #a16207; border-color: #fde047; }
    .ticket-badge-pill.status-ON_HOLD { background: #faf5ff; color: #7e22ce; border-color: #e9d5ff; }
    .ticket-badge-pill.status-WAITING_CONFIRMATION { background: #ecfeff; color: #0e7490; border-color: #a5f3fc; }
    .ticket-badge-pill.status-COMPLETED { background: #f0fdf4; color: #15803d; border-color: #bbf7d0; }
    .ticket-badge-pill.status-CANCELLED { background: #fef2f2; color: #b91c1c; border-color: #fecaca; }

    .ticket-badge-pill.priority-URGENT { background: #fef2f2; color: #dc2626; border-color: #fca5a5; }
    .ticket-badge-pill.priority-HIGH { background: #fff7ed; color: #c2410c; border-color: #fed7aa; }
    .ticket-badge-pill.priority-NORMAL { background: #f1f5f9; color: #475569; border-color: #cbd5e1; }
    .ticket-badge-pill.priority-LOW { background: #f8fafc; color: #64748b; border-color: #e2e8f0; }

    .ticket-grid-2col {
      display: grid;
      grid-template-columns: 1.15fr 0.85fr;
      gap: 20px;
    }
    @media (max-width: 768px) {
      .ticket-grid-2col { grid-template-columns: 1fr; }
    }
    .ticket-section-title {
      font-size: 13.5px;
      font-weight: 700;
      color: #0f3361;
      display: flex;
      align-items: center;
      gap: 7px;
      margin-bottom: 10px;
      padding-bottom: 6px;
      border-bottom: 1px solid #e2e8f0;
    }
    .ticket-timeline {
      position: relative;
      padding-left: 24px;
      margin-top: 12px;
    }
    .ticket-timeline::before {
      content: '';
      position: absolute;
      left: 7px;
      top: 6px;
      bottom: 6px;
      width: 2px;
      background: #e2e8f0;
    }
    .ticket-timeline-item {
      position: relative;
      margin-bottom: 16px;
    }
    .ticket-timeline-item:last-child {
      margin-bottom: 0;
    }
    .timeline-dot {
      position: absolute;
      left: -24px;
      top: 2px;
      width: 16px;
      height: 16px;
      border-radius: 50%;
      background: #ffffff;
      border: 3px solid #2563eb;
      box-shadow: 0 0 0 2px #eff6ff;
    }
    .timeline-dot.completed { border-color: #16a34a; }
    .timeline-dot.hold { border-color: #9333ea; }
    .timeline-dot.warning { border-color: #f59e0b; }
    .timeline-content {
      background: #f8fafc;
      border: 1px solid #e2e8f0;
      border-radius: 8px;
      padding: 10px 12px;
      font-size: 12px;
    }
    .timeline-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 4px;
    }
    .proof-gallery-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(110px, 1fr));
      gap: 10px;
      margin-top: 8px;
    }
    .proof-card {
      position: relative;
      border-radius: 8px;
      overflow: hidden;
      border: 1px solid #cbd5e1;
      height: 90px;
      background: #000;
    }
    .proof-card img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      transition: transform 0.2s ease;
    }
    .proof-card:hover img {
      transform: scale(1.06);
    }
    .proof-ribbon {
      position: absolute;
      top: 4px;
      left: 4px;
      font-size: 9px;
      font-weight: 800;
      padding: 2px 6px;
      border-radius: 4px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }
    .proof-ribbon.before { background: rgba(220, 38, 38, 0.9); color: #fff; }
    .proof-ribbon.after { background: rgba(22, 163, 74, 0.9); color: #fff; }
    .star-rating-box {
      display: inline-flex;
      gap: 6px;
      font-size: 24px;
      color: #cbd5e1;
      cursor: pointer;
    }
    .star-rating-box i.active {
      color: #f59e0b;
    }
    .star-rating-box i:hover {
      color: #fbbf24;
    }
  
    /* ==========================================================================
       MODULE 5: STAFF & RBAC MANAGEMENT STYLES
       ========================================================================== */
    .staff-subtab-bar {
      display: flex;
      gap: 8px;
      padding: 6px;
      background: #f1f5f9;
      border-radius: 10px;
      margin-bottom: 20px;
      flex-wrap: wrap;
      border: 1px solid #e2e8f0;
    }
    .staff-subtab-btn {
      padding: 9px 18px;
      border: none;
      background: transparent;
      border-radius: 8px;
      cursor: pointer;
      font-weight: 700;
      font-size: 13px;
      color: #475569;
      display: inline-flex;
      align-items: center;
      gap: 8px;
      transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .staff-subtab-btn:hover {
      background: #e2e8f0;
      color: #0f172a;
    }
    .staff-subtab-btn.active {
      background: #0f3361;
      color: #ffffff;
      box-shadow: 0 2px 8px rgba(15, 51, 97, 0.3);
    }
    .staff-subtab-btn .badge-pill {
      background: rgba(255, 255, 255, 0.2);
      padding: 2px 7px;
      border-radius: 10px;
      font-size: 11px;
    }
    .staff-subtab-btn:not(.active) .badge-pill {
      background: #cbd5e1;
      color: #334155;
    }
    .building-select-grid {
      max-height: 180px;
      overflow-y: auto;
      border: 1px solid #cbd5e1;
      border-radius: 8px;
      padding: 10px;
      background: #f8fafc;
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(210px, 1fr));
      gap: 8px;
    }
    .building-check-label {
      display: flex;
      align-items: center;
      gap: 8px;
      font-size: 12.5px;
      color: #334155;
      background: #ffffff;
      padding: 6px 10px;
      border-radius: 6px;
      border: 1px solid #e2e8f0;
      cursor: pointer;
      transition: all 0.15s;
    }
    .building-check-label:hover {
      border-color: #2563eb;
      background: #eff6ff;
    }
    .building-check-label input[type="checkbox"] {
      cursor: pointer;
      accent-color: #0f3361;
    }
    .staff-badge-super {
      background: #fee2e2;
      color: #b91c1c;
      border: 1px solid #fecaca;
      font-weight: 700;
      padding: 2px 8px;
      border-radius: 9999px;
      font-size: 11px;
      display: inline-flex;
      align-items: center;
      gap: 4px;
    }
    .staff-badge-admin {
      background: #e0e7ff;
      color: #3730a3;
      border: 1px solid #c7d2fe;
      font-weight: 600;
      padding: 2px 8px;
      border-radius: 9999px;
      font-size: 11px;
    }
  </style>
