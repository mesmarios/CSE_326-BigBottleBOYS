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
</style>

<footer class="app-footer">
  <div class="float-end d-none d-sm-inline">BigBottleBOYS &copy; 2026</div>
  <strong>Copyright &copy; 2026 <a href="#" class="text-decoration-none">TheBigBottleBoys</a>.</strong>
  All rights reserved.
  <button
    type="button"
    class="footer-about-btn ms-2"
    data-bs-toggle="modal"
    data-bs-target="#aboutModal"
    aria-label="Σχετικά με την ιστοσελίδα"
  >
    About
  </button>
</footer>

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
