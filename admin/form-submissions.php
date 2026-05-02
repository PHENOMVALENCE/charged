<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';

$view = strtolower(trim((string) ($_GET['view'] ?? 'all')));
if (!in_array($view, ['all', 'subscribe', 'partner'], true)) {
    $view = 'all';
}

$rows = [];
$tableOk = true;
try {
    if ($view === 'all') {
        $st = $pdo->query(
            'SELECT id, form_type, email, name, message, source_page, ip_address, created_at
             FROM site_form_submissions
             ORDER BY created_at DESC
             LIMIT 500'
        );
    } else {
        $st = $pdo->prepare(
            'SELECT id, form_type, email, name, message, source_page, ip_address, created_at
             FROM site_form_submissions
             WHERE form_type = ?
             ORDER BY created_at DESC
             LIMIT 500'
        );
        $st->execute([$view]);
    }
    $rows = $st->fetchAll();
} catch (Throwable $e) {
    $tableOk = false;
}

$charged_page_title = 'Form submissions — CHARGED Admin';
$charged_nav = 'admin-forms';
$CHARGED_ADMIN = true;
require_once __DIR__ . '/../includes/header.php';
?>

<div class="ch-container ch-admin-wrap">
  <div class="ch-admin-head">
    <div>
      <p class="ch-eyebrow">Admin</p>
      <h1>Form submissions</h1>
      <p class="ch-section-dek">Newsletter sign-ups and partner inquiries from the public site.</p>
      <nav class="ch-admin-subnav" aria-label="Filter submissions" style="margin-top:1rem;display:flex;flex-wrap:wrap;gap:0.5rem;align-items:center;">
        <a class="ch-admin-nav-btn ch-admin-nav-btn--neutral<?php echo $view === 'all' ? ' is-active' : ''; ?>" href="form-submissions.php?view=all">All</a>
        <a class="ch-admin-nav-btn ch-admin-nav-btn--newsletter<?php echo $view === 'subscribe' ? ' is-active' : ''; ?>" href="form-submissions.php?view=subscribe">Newsletter</a>
        <a class="ch-admin-nav-btn ch-admin-nav-btn--forms<?php echo $view === 'partner' ? ' is-active' : ''; ?>" href="form-submissions.php?view=partner">Partner inquiries</a>
        <a class="ch-admin-nav-btn ch-admin-nav-btn--newsletter ch-admin-nav-btn--compact" href="subscribers.php">List &amp; CSV</a>
      </nav>
    </div>
    <div style="display:flex;flex-wrap:wrap;gap:0.5rem;align-items:center;">
      <a class="ch-admin-nav-btn ch-admin-nav-btn--newsletter ch-admin-nav-btn--compact" href="subscribers.php">Newsletter list</a>
      <a class="ch-btn ch-btn--on-dark" href="dashboard.php">Dashboard</a>
    </div>
  </div>

  <?php if (!$tableOk) : ?>
  <div class="ch-alert ch-alert--error" role="alert">
    The database table is missing. Import <code>database/charged_site_forms.sql</code> into your CHARGED database, then refresh this page.
  </div>
  <?php elseif ($rows === []) : ?>
  <p class="ch-section-dek"><?php echo $view === 'partner'
    ? 'No partner inquiries yet. They are submitted from the Advertise page.'
    : ($view === 'subscribe'
        ? 'No newsletter sign-ups yet. Use the Subscriber list page or try the subscribe form on the live site.'
        : 'No submissions yet. They will appear here after visitors use the subscribe or partner forms.'); ?></p>
  <?php else : ?>
  <div class="ch-admin-table-wrap">
    <table class="ch-admin-table">
      <thead>
        <tr>
          <th>Date</th>
          <th>Type</th>
          <th>Email</th>
          <th>Name</th>
          <th>Message</th>
          <th>Source</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($rows as $r) : ?>
        <tr>
          <td><?php echo charged_e($r['created_at']); ?></td>
          <td>
            <?php if ($r['form_type'] === 'subscribe') : ?>
            <span class="ch-badge ch-badge--published">Subscribe</span>
            <?php else : ?>
            <span class="ch-badge ch-badge--draft">Partner</span>
            <?php endif; ?>
          </td>
          <td><a href="mailto:<?php echo charged_e($r['email']); ?>"><?php echo charged_e($r['email']); ?></a></td>
          <td><?php echo charged_e($r['name'] ?? ''); ?></td>
          <td style="max-width:280px;white-space:pre-wrap;font-size:0.85rem;"><?php
              $msg = (string) ($r['message'] ?? '');
            if (function_exists('mb_strimwidth')) {
                $msg = mb_strimwidth($msg, 0, 400, '…', 'UTF-8');
            } elseif (strlen($msg) > 400) {
                $msg = substr($msg, 0, 397) . '...';
            }
            echo charged_e($msg);
            ?></td>
          <td style="max-width:160px;font-size:0.8rem;word-break:break-all;"><?php echo charged_e($r['source_page'] ?? ''); ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <p class="ch-field-hint" style="margin-top:1rem;">Showing the latest 500 rows. IP is stored server-side for abuse review (not shown in this table).</p>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
