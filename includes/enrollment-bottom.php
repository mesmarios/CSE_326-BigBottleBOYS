          </div>
        </div>
      </main>

      <?php require_once __DIR__ . '/admin-footer.php'; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/browser/overlayscrollbars.browser.es6.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.min.js" crossorigin="anonymous"></script>
    <script src="../../assets/js/adminlte.js" defer></script>
    <script src="../../assets/js/changes.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.37.1/dist/apexcharts.min.js" crossorigin="anonymous"></script>
    <script>
      document.addEventListener('DOMContentLoaded', function () {
        var sw = document.querySelector('.sidebar-wrapper');
        if (sw && window.OverlayScrollbarsGlobal && OverlayScrollbarsGlobal.OverlayScrollbars) {
          OverlayScrollbarsGlobal.OverlayScrollbars(sw, {
            scrollbars: { theme: 'os-theme-light', autoHide: 'leave', clickScroll: true }
          });
        }
      });

      function toggleSidebar(event) {
        event.preventDefault();
        document.body.classList.toggle('sidebar-collapse');
      }
    </script>
  </body>
</html>

