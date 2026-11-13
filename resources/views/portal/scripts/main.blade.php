  <script>
    /* ==========================================================================
       1. DATABASE & SERVER SYNC (BLADE + RESTFUL API)
       ========================================================================== */
    const SERVER_BUILDINGS = @json($serverBuildings ?? []);
    const BUILDINGS_DATA = (SERVER_BUILDINGS && SERVER_BUILDINGS.length > 0) ? SERVER_BUILDINGS : [];
    let currentSelectedBuilding = BUILDINGS_DATA[0] || null;

    // Helper toàn cục bảo vệ XSS khi render HTML động
    function escapeHtml(str) {
      if (str === null || str === undefined) return '';
      return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
    }


  </script>
