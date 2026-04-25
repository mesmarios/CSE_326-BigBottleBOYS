<!-- common footer include: closes main wrapper and loads shared scripts -->
      </main>
      <!--end::App Main-->
      <style>
        .footer-about-btn {
          font-family: 'Source Sans 3', sans-serif;
          background: none;
          border: 1px solid #b0bec5;
          border-radius: 4px;
          color: #5f6f82;
          font-size: 0.84rem;
          padding: 2px 10px;
          cursor: pointer;
          transition: color 0.2s, border-color 0.2s;
        }

        .footer-about-btn:hover,
        .footer-about-btn:focus {
          color: #1a3a5c;
          border-color: #1a3a5c;
        }

        .footer-about-btn:focus,
        .footer-about-btn:focus-visible {
          outline: none;
          box-shadow: none;
          border-color: #b0bec5;
          color: #5f6f82;
        }

        .about-modal-close-btn:hover {
          background-color: #dc3545;
          border-color: #dc3545;
          color: #fff;
        }

        .about-modal-close-btn:focus:not(:hover),
        .about-modal-close-btn:focus-visible:not(:hover),
        .about-modal-close-btn:active:not(:hover) {
          background-color: var(--bs-secondary);
          border-color: var(--bs-secondary);
          color: #fff;
          box-shadow: none;
          outline: none;
        }

        .floating-home-btn {
          position: fixed;
          left: 22px;
          bottom: 22px;
          z-index: 1080;
          display: inline-flex;
          align-items: center;
          gap: 8px;
          padding: 10px 14px;
          border-radius: 999px;
          background: #0b2a6b;
          color: #fff;
          text-decoration: none;
          font-size: 0.92rem;
          font-weight: 600;
          box-shadow: 0 12px 28px rgba(11, 42, 107, 0.24);
          transition: transform 0.18s ease, background-color 0.18s ease;
        }

        .floating-home-btn:hover,
        .floating-home-btn:focus {
          background: #1f5fbf;
          color: #fff;
          transform: translateY(-2px);
        }

        @media (max-width: 768px) {
          .floating-home-btn {
            left: 14px;
            bottom: 14px;
            padding: 9px 12px;
            font-size: 0.86rem;
          }
        }

      </style>
      <a href="../../index.php" class="floating-home-btn" aria-label="Επιστροφή στην αρχική">
        <i class="bi bi-house-door-fill"></i>Αρχική
      </a>
      <!--begin::Footer-->
      <footer class="app-footer d-flex align-items-center justify-content-center gap-2 flex-wrap text-center">
        <!--begin::Copyright-->
        <strong>
          Copyright &copy; <?php echo date('Y'); ?>&nbsp;
          <a href="#" class="text-decoration-none">TheBigBottleBoys</a>.
        </strong>&nbsp;All rights reserved.
        <button
          type="button"
          class="footer-about-btn"
          data-bs-toggle="modal"
          data-bs-target="#aboutModal"
          aria-label="Σχετικά με την ιστοσελίδα"
        >
          About
        </button>
        <!--end::Copyright-->
      </footer>
      <!--end::Footer-->

      <div class="modal fade" id="aboutModal" tabindex="-1" aria-labelledby="aboutModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-header" style="background:#1a3a5c; color:#fff;">
              <h5 class="modal-title" id="aboutModalLabel">Σχετικά με την Ιστοσελίδα</h5>
            </div>
            <div class="modal-body" style="font-size:0.95rem; line-height:1.7; color:#344055;">
              <p class="mb-2">Η ιστοσελίδα δημιουργήθηκε στο πλαίσιο του μαθήματος <strong>Μηχανική Ιστού (CSE_326 / CEI_326)</strong>.</p>
              <p class="mb-2">Διδάσκων καθηγητής: <strong>ΠΑΠΑΓΙΑΝΝΗΣ ΠΕΤΡΟΣ</strong>.</p>
              <p class="mb-1">Η ομάδα ανάπτυξης αποτελείται από τους:</p>
              <p class="mb-1">Μάριος Σιήττας, Α.Φ.Τ. 27432</p>
              <p class="mb-1">Μάριος Μεσαρίτης, Α.Φ.Τ. 27818</p>
              <p class="mb-3">Μιχαλής Τσαδιώτης, Α.Φ.Τ. 28053</p>
              <p class="mt-3 mb-0">&copy; <?php echo date('Y'); ?> BigBottleBOYS. All rights reserved.</p>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary btn-sm about-modal-close-btn" data-bs-dismiss="modal">Κλείσιμο</button>
            </div>
          </div>
        </div>
      </div>

    </div>
    <!--end::App Wrapper-->
    <!--begin::Script-->
    <!--begin::Third Party Plugin(OverlayScrollbars)-->
    <script
      src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/browser/overlayscrollbars.browser.es6.min.js"
      crossorigin="anonymous"
    ></script>
    <!--end::Third Party Plugin(OverlayScrollbars)--><!--begin::Required Plugin(popperjs for Bootstrap 5)-->
    <script
      src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
      crossorigin="anonymous"
    ></script>
    <!--end::Required Plugin(popperjs for Bootstrap 5)--><!--begin::Required Plugin(Bootstrap 5)-->
    <script
      src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.min.js"
      crossorigin="anonymous"
    ></script>
    <!--end::Required Plugin(Bootstrap 5)--><!--begin::Required Plugin(AdminLTE)-->
    <script src="../../recruitment/assets/js/adminlte.js"></script>
    <!--end::Required Plugin(AdminLTE)--><!--begin::OverlayScrollbars Configure-->
    <script>
      const SELECTOR_SIDEBAR_WRAPPER = '.sidebar-wrapper';
      const Default = {
        scrollbarTheme: 'os-theme-light',
        scrollbarAutoHide: 'leave',
        scrollbarClickScroll: true,
      };
      document.addEventListener('DOMContentLoaded', function () {
        const sidebarWrapper = document.querySelector(SELECTOR_SIDEBAR_WRAPPER);
        if (sidebarWrapper && OverlayScrollbarsGlobal?.OverlayScrollbars !== undefined) {
          OverlayScrollbarsGlobal.OverlayScrollbars(sidebarWrapper, Default);
        }
      });
    </script>
    <!--end::OverlayScrollbars Configure-->

    <!-- OPTIONAL SCRIPTS -->
    <!-- sortablejs -->
    <script
      src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"
      crossorigin="anonymous"
    ></script>
    <!-- sorted items script -->
    <script>
      new Sortable(document.querySelector('.connectedSortable'), {
        group: 'shared',
        handle: '.card-header',
      });

      const cardHeaders = document.querySelectorAll('.connectedSortable .card-header');
      cardHeaders.forEach((cardHeader) => {
        cardHeader.style.cursor = 'move';
      });
    </script>

    <!-- apexcharts -->
    <script
      src="https://cdn.jsdelivr.net/npm/apexcharts@3.37.1/dist/apexcharts.min.js"
      integrity="sha256-+vh8GkaU7C9/wbSLIcwq82tQ2wTf44aOHA8HlBMwRI8="
      crossorigin="anonymous"
    ></script>

    <?php
    // include shared functions, both PHP utilities and javascript helpers
    include_once __DIR__ . '/functions.php';
    ?>
  </body>
  <!--end::Body-->
</html>
