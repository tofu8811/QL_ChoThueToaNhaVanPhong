@php
  $totalB = $stats['total_buildings'] ?? 39;
  $provincesText = $stats['active_provinces_text'] ?? 'Hà Nội, TP.HCM, Đồng Nai & Đà Nẵng';
@endphp
    <main id="page-home" class="page-section active">
      <!-- Hero Banner -->
      <section class="mo-hero-banner">
        <div class="container">
          <div class="mo-breadcrumbs">
            <a href="{{ route('home') }}">Trang chủ</a>
            <i class="fa-solid fa-chevron-right" style="font-size:10px;"></i>
            <span>Cho thuê văn phòng toàn quốc</span>
          </div>
          <h1 class="mo-hero-title">Cho Thuê Văn Phòng Toàn Quốc</h1>
          <p class="mo-hero-desc">Hệ thống khảo sát và phân phối mặt bằng văn phòng chuyên nghiệp toàn quốc. Dữ liệu thực tế {{ $totalB }}+ cao ốc từ Hạng A, B, C trên toàn quốc. Cập nhật báo giá & diện tích trống mới nhất {{ date('Y') }}.</p>
        </div>
      </section>

      <!-- Nhúng Livewire Component Bộ lọc & Grid Tòa Nhà (Module 1) -->
      <livewire:building-search-filter />
    </main>
