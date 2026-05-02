<?php
/**
 * Site footer — closes main + page wrapper.
 * Expects same $CHARGED_ADMIN and $prefix as header (set before header include).
 */

declare(strict_types=1);

$CHARGED_ADMIN = !empty($CHARGED_ADMIN);
$charged_from_admin_dir = !empty($charged_from_admin_dir);
$prefix = ($CHARGED_ADMIN || $charged_from_admin_dir) ? '../' : '';
$charged_admin_login_href = ($CHARGED_ADMIN || $charged_from_admin_dir) ? 'login.php' : 'admin/login.php';
?>
    </main>

    <footer class="ch-footer">
      <div class="ch-container ch-footer__grid">
        <div class="ch-footer__brand">
          <a class="ch-logo ch-logo--footer" href="<?php echo charged_e($prefix); ?>index.php">
            <span class="ch-logo__mark ch-logo__mark--footer" aria-hidden="true">
              <svg viewBox="0 0 24 24" width="22" height="22" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <path d="M13 2L6 14h5l-2 10 10-16h-6l0-6z" fill="currentColor"/>
              </svg>
            </span>
            <span class="ch-logo__text">CHARGED</span>
          </a>
          <p>African fintech and e-mobility intelligence. Edited from Dar es Salaam.</p>
        </div>
        <div>
          <h2 class="ch-footer__title">Navigate</h2>
          <ul class="ch-footer__list">
            <li><a href="<?php echo charged_e($prefix); ?>index.php">Home</a></li>
            <li><a href="<?php echo charged_e($prefix); ?>articles.php">Articles</a></li>
            <li><a href="<?php echo charged_e($prefix); ?>archive.html">Archive</a></li>
            <li><a href="<?php echo charged_e($prefix); ?>about.html">About</a></li>
            <li><a href="<?php echo charged_e($prefix); ?>advertise.html">Advertise</a></li>
          </ul>
        </div>
        <div>
          <h2 class="ch-footer__title">Legal</h2>
          <ul class="ch-footer__list">
            <li><a href="<?php echo charged_e($prefix); ?>privacy.html">Privacy</a></li>
            <li><a href="<?php echo charged_e($charged_admin_login_href); ?>">Admin login</a></li>
          </ul>
        </div>
        <div>
          <h2 class="ch-footer__title">Contact</h2>
          <ul class="ch-footer__list">
            <li><span class="ch-footer__location">Dar es Salaam, Tanzania</span></li>
            <li><span class="ch-footer__hint">Newsletter &amp; partner: use the forms on this site.</span></li>
          </ul>
        </div>
      </div>
      <div class="ch-container ch-footer__bottom">
        <p>© <span id="y"></span> CHARGED. All rights reserved.</p>
      </div>
    </footer>
  </div>

  <script>
    document.getElementById("y").textContent = new Date().getFullYear();
  </script>
  <?php if (!empty($charged_include_article_editor)) : ?>
  <?php require __DIR__ . '/admin-article-editor.php'; ?>
  <?php endif; ?>
  <script>window.CHARGED_SUBMIT_FORM=<?php echo json_encode(charged_submit_form_web_path(), JSON_UNESCAPED_SLASHES); ?>;</script>
  <script src="<?php echo charged_e($prefix); ?>assets/js/charged-path.js" defer></script>
  <script src="<?php echo charged_e($prefix); ?>assets/js/main.js" defer></script>
</body>
</html>
