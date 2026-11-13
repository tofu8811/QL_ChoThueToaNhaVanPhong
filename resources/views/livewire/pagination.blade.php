@if ($paginator->hasPages() || $paginator->total() > 0)
  <div class="mo-pagination-wrapper">
    <!-- Thống kê số lượng kết quả -->
    <div class="mo-pagination-info">
      Hiển thị <strong>{{ $paginator->firstItem() ?? 0 }}</strong> - <strong>{{ $paginator->lastItem() ?? 0 }}</strong>
      trong tổng số <strong>{{ $paginator->total() }}</strong> tòa nhà
    </div>

    <!-- Thanh chuyển trang (Pagination Nav) -->
    <div class="mo-pagination-nav">
      {{-- Nút Trang Trước (Previous) --}}
      @if ($paginator->onFirstPage())
        <button class="mo-page-btn" type="button" disabled title="Trang đầu">
          <i class="fa-solid fa-chevron-left" style="font-size:12px;"></i>
        </button>
      @else
        <button class="mo-page-btn" type="button" wire:click="previousPage" wire:loading.attr="disabled"
                x-on:click="document.querySelector('.mo-listings-wrapper')?.scrollIntoView({ behavior: 'smooth' })"
                title="Trang trước">
          <i class="fa-solid fa-chevron-left" style="font-size:12px;"></i>
        </button>
      @endif

      {{-- Danh sách các số trang --}}
      @foreach ($elements as $element)
        {{-- Dấu 3 chấm ngăn cách (...) --}}
        @if (is_string($element))
          <span class="mo-page-dots">{{ $element }}</span>
        @endif

        {{-- Các nút số trang --}}
        @if (is_array($element))
          @foreach ($element as $page => $url)
            @if ($page == $paginator->currentPage())
              <button class="mo-page-btn active" type="button" wire:key="page-btn-active-{{ $page }}">
                {{ $page }}
              </button>
            @else
              <button class="mo-page-btn" type="button" wire:key="page-btn-{{ $page }}"
                      wire:click="gotoPage({{ $page }})"
                      x-on:click="document.querySelector('.mo-listings-wrapper')?.scrollIntoView({ behavior: 'smooth' })">
                {{ $page }}
              </button>
            @endif
          @endforeach
        @endif
      @endforeach

      {{-- Nút Trang Kế (Next) --}}
      @if ($paginator->hasMorePages())
        <button class="mo-page-btn" type="button" wire:click="nextPage" wire:loading.attr="disabled"
                x-on:click="document.querySelector('.mo-listings-wrapper')?.scrollIntoView({ behavior: 'smooth' })"
                title="Trang kế tiếp">
          <i class="fa-solid fa-chevron-right" style="font-size:12px;"></i>
        </button>
      @else
        <button class="mo-page-btn" type="button" disabled title="Trang cuối">
          <i class="fa-solid fa-chevron-right" style="font-size:12px;"></i>
        </button>
      @endif
    </div>

    <!-- Dropdown tùy chọn số lượng hiển thị trên trang ($perPage) -->
    <div class="mo-pagination-pagesize">
      <span>Hiển thị:</span>
      <select wire:model.live="perPage">
        <option value="4">4 / trang</option>
        <option value="8">8 / trang</option>
        <option value="12">12 / trang</option>
        <option value="20">20 / trang</option>
      </select>
    </div>
  </div>
@endif
