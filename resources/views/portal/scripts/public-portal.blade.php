<script>
    /* ==========================================================================
       3. RENDER PUBLIC BUILDINGS & CLIENT PAGINATION
       ========================================================================== */
    let currentFilteredList = [...BUILDINGS_DATA];
    let currentListingPage = 1;
    let currentListingPageSize = 6;

    function renderBuildings(items, page = 1) {
      if (items) {
        currentFilteredList = items;
      }
      currentListingPage = page;

      const totalItems = currentFilteredList.length;
      const totalPages = Math.ceil(totalItems / currentListingPageSize) || 1;

      if (currentListingPage > totalPages) currentListingPage = totalPages;
      if (currentListingPage < 1) currentListingPage = 1;

      const startIdx = (currentListingPage - 1) * currentListingPageSize;
      const endIdx = Math.min(startIdx + currentListingPageSize, totalItems);
      const pagedItems = currentFilteredList.slice(startIdx, endIdx);

      const container = document.getElementById("building-cards-container");
      const countText = document.getElementById("listing-count-text");
      if (!container) return;

      if (countText) {
        countText.innerText = totalItems > 0
          ? `Hiển thị ${startIdx + 1} - ${endIdx} trên tổng số ${totalItems} tòa nhà văn phòng phù hợp`
          : 'Không có tòa nhà nào phù hợp với bộ lọc';
      }

      if (totalItems === 0) {
        container.innerHTML = `
          <div style="grid-column: 1 / -1; background:#fff; border:1px solid #e2e8f0; border-radius:12px; padding:40px; text-align:center;">
            <i class="fa-solid fa-building-circle-exclamation" style="font-size:44px; color:#94a3b8; margin-bottom:12px;"></i>
            <h3 style="font-size:18px; color:var(--mo-primary); margin-bottom:6px;">Không tìm thấy tòa nhà phù hợp</h3>
            <p style="color:#64748b; font-size:14px; margin-bottom:16px;">Vui lòng thử bỏ bớt bộ lọc hoặc chọn khoảng giá/diện tích khác.</p>
            <button class="btn-mo-orange" onclick="resetAllFilters()">Đặt lại bộ lọc</button>
          </div>
        `;
        renderPaginationControls(0, 1, 1);
        return;
      }

      container.innerHTML = pagedItems.map(b => `
        <article class="mo-building-card">
          <div class="mo-card-thumb" onclick="openBuildingDetail('${b.id}')" style="cursor:pointer;">
            <img src="${b.image}" alt="${b.name}">
            <div class="mo-thumb-badges">
              <span class="mo-badge-grade">${b.grade}</span>
              <span class="mo-badge-status ${b.statusClass}"><i class="fa-solid fa-circle" style="font-size:8px;"></i> ${b.status}</span>
            </div>
          </div>
          <div class="mo-card-body">
            <span class="mo-card-district"><i class="fa-solid fa-location-dot"></i> ${b.district}</span>
            <h3 class="mo-card-name" onclick="openBuildingDetail('${b.id}')" style="cursor:pointer;">${b.name}</h3>
            <p class="mo-card-address">${b.address}</p>

            <div class="mo-card-specs">
              <div class="mo-spec-item"><i class="fa-solid fa-expand"></i> Sàn: <strong>${b.floorPlate}</strong></div>
              <div class="mo-spec-item"><i class="fa-solid fa-layer-group"></i> Quy mô: <strong>${b.structure ? b.structure.split('-')[0] : '20 Tầng'}</strong></div>
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
                <button class="btn-card-view" onclick="openBuildingDetail('${b.id}')">Xem Chi Tiết</button>
              </div>
            </div>
          </div>
        </article>
      `).join('');

      renderPaginationControls(totalItems, currentListingPage, totalPages);
    }

    function renderPaginationControls(totalItems, currentPage, totalPages) {
      const wrapper = document.getElementById("pagination-wrapper");
      const infoText = document.getElementById("pagination-info-text");
      const navContainer = document.getElementById("pagination-controls");
      if (!wrapper || !navContainer) return;

      if (totalItems === 0) {
        wrapper.style.display = "none";
        return;
      }
      wrapper.style.display = "flex";

      const startIdx = (currentPage - 1) * currentListingPageSize + 1;
      const endIdx = Math.min(currentPage * currentListingPageSize, totalItems);
      if (infoText) {
        infoText.innerHTML = `Trang <strong>${currentPage}</strong> / <strong>${totalPages}</strong> (Hiển thị <strong>${startIdx} - ${endIdx}</strong> / ${totalItems} tòa nhà)`;
      }

      let html = '';

      // Nút Previous
      const prevDisabled = currentPage <= 1 ? 'disabled' : '';
      html += `<button class="mo-page-btn" ${prevDisabled ? 'disabled' : ''} onclick="goToListingPage(${currentPage - 1})" title="Trang trước">
        <i class="fa-solid fa-chevron-left"></i>
      </button>`;

      // Hiển thị các số trang
      const maxButtons = 5;
      let startPage = Math.max(1, currentPage - 2);
      let endPage = Math.min(totalPages, startPage + maxButtons - 1);
      if (endPage - startPage < maxButtons - 1) {
        startPage = Math.max(1, endPage - maxButtons + 1);
      }

      if (startPage > 1) {
        html += `<button class="mo-page-btn" onclick="goToListingPage(1)">1</button>`;
        if (startPage > 2) {
          html += `<span class="mo-page-dots">...</span>`;
        }
      }

      for (let i = startPage; i <= endPage; i++) {
        const active = i === currentPage ? 'active' : '';
        html += `<button class="mo-page-btn ${active}" onclick="goToListingPage(${i})">${i}</button>`;
      }

      if (endPage < totalPages) {
        if (endPage < totalPages - 1) {
          html += `<span class="mo-page-dots">...</span>`;
        }
        html += `<button class="mo-page-btn" onclick="goToListingPage(${totalPages})">${totalPages}</button>`;
      }

      // Nút Next
      const nextDisabled = currentPage >= totalPages ? 'disabled' : '';
      html += `<button class="mo-page-btn" ${nextDisabled ? 'disabled' : ''} onclick="goToListingPage(${currentPage + 1})" title="Trang tiếp theo">
        <i class="fa-solid fa-chevron-right"></i>
      </button>`;

      navContainer.innerHTML = html;
    }

    function goToListingPage(targetPage) {
      const totalPages = Math.ceil(currentFilteredList.length / currentListingPageSize) || 1;
      if (targetPage < 1 || targetPage > totalPages) return;
      renderBuildings(currentFilteredList, targetPage);

      const listEl = document.querySelector('.mo-listings-header');
      if (listEl) {
        listEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }
    }

    function changePageSize(newSize) {
      currentListingPageSize = Number(newSize) || 6;
      renderBuildings(currentFilteredList, 1);
    }

    /* ==========================================================================
       4. FILTERING LOGIC
       ========================================================================== */
    function applyFilters() {
      const keyword = (document.getElementById("filter-keyword")?.value || "").toLowerCase().trim();
      const district = document.getElementById("filter-district")?.value || "all";
      const grade = document.getElementById("filter-grade")?.value || "all";
      const price = document.getElementById("filter-price")?.value || "all";
      const area = document.getElementById("filter-area")?.value || "all";

      let filtered = BUILDINGS_DATA.filter(b => {
        if (keyword && !b.name.toLowerCase().includes(keyword) && !b.address.toLowerCase().includes(keyword)) return false;
        if (district !== "all" && b.district !== district) return false;
        if (grade !== "all" && !b.grade.includes(grade.replace(' / A+', '').replace(' / B+', '').replace(' / Giá rẻ', ''))) return false;
        if (price === "under20" && b.priceUsd >= 20) return false;
        if (price === "20to35" && (b.priceUsd < 20 || b.priceUsd > 35)) return false;
        if (price === "over35" && b.priceUsd <= 35) return false;
        return true;
      });

      renderBuildings(filtered, 1);
    }

    function quickFilterDistrict(dist) {
      const el = document.getElementById("filter-district");
      if (el) el.value = dist;
      applyFilters();
      window.scrollTo({ top: 380, behavior: 'smooth' });
    }

    function quickFilterGrade(grd) {
      const el = document.getElementById("filter-grade");
      if (el) el.value = grd;
      applyFilters();
      window.scrollTo({ top: 380, behavior: 'smooth' });
    }

    function resetAllFilters() {
      if (document.getElementById("filter-keyword")) document.getElementById("filter-keyword").value = "";
      if (document.getElementById("filter-district")) document.getElementById("filter-district").value = "all";
      if (document.getElementById("filter-grade")) document.getElementById("filter-grade").value = "all";
      if (document.getElementById("filter-price")) document.getElementById("filter-price").value = "all";
      if (document.getElementById("filter-area")) document.getElementById("filter-area").value = "all";
      renderBuildings(BUILDINGS_DATA, 1);
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
      let list = [...currentFilteredList];
      if (criteria === "price-asc") {
        list.sort((a,b) => a.priceUsd - b.priceUsd);
      } else if (criteria === "price-desc") {
        list.sort((a,b) => b.priceUsd - a.priceUsd);
      } else if (criteria === "area-desc") {
        list.sort((a,b) => parseInt(b.floorPlate) - parseInt(a.floorPlate));
      }
      renderBuildings(list, 1);
    }

    /* ==========================================================================
       5. DETAIL PAGE & BOOKING LOGIC
       ========================================================================== */
    function openBuildingDetail(buildingId) {
      const b = BUILDINGS_DATA.find(x => String(x.id) === String(buildingId));
      if (b && (b.slug || b.id)) {
        window.location.href = '/toa-nha/' + (b.slug || b.id);
        return;
      }
      window.location.href = '/toa-nha/' + buildingId;
    }

    function scrollThumbs(dir) {
      const el = document.getElementById("detail-sub-thumbs");
      if (!el) return;
      const step = el.clientWidth * 0.8;
      el.scrollBy({ left: dir === 'next' ? step : -step, behavior: 'smooth' });
    }

    function changeDetailImg(thumbEl, src) {
      const hero = document.getElementById("detail-hero-img");
      if (hero) hero.src = src;
      const thumbs = document.querySelectorAll("#detail-sub-thumbs img");
      thumbs.forEach(t => t.classList.remove("active"));
      if (thumbEl) thumbEl.classList.add("active");
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

    /* ==========================================================================
       6. PUBLIC SPA NAVIGATION
       ========================================================================== */
    function showPublicPage(pageId, updateRoute = true) {
      document.querySelectorAll('#portal-wrapper .page-section').forEach(sec => {
        sec.classList.remove('active');
      });
      const target = document.getElementById(pageId);
      if (target) target.classList.add('active');

      document.querySelectorAll('.mo-nav-item').forEach(item => item.classList.remove('active'));
      if (pageId === 'page-home') {
        const first = document.querySelector('.mo-nav-item');
        if (first) first.classList.add('active');
      }

      if (updateRoute && typeof updateAppRoute === 'function') {
        updateAppRoute(pageId === 'page-home' ? 'portal' : (pageId === 'page-quote' ? 'quote' : 'detail'));
      }
    }

    </script>
