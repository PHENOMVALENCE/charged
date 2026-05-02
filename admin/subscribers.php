<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';

if (($_GET['export'] ?? '') === 'csv') {
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="charged-newsletter-subscribers-' . date('Y-m-d') . '.csv"');
    echo "\xEF\xBB\xBF";
    $out = fopen('php://output', 'w');
    if ($out !== false) {
        fputcsv($out, ['email', 'signed_up_at', 'source_page']);
        try {
            $st = $pdo->query(
                "SELECT email, created_at, source_page FROM site_form_submissions
                 WHERE form_type = 'subscribe' ORDER BY created_at ASC"
            );
            while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
                fputcsv($out, [
                    $row['email'],
                    $row['created_at'],
                    (string) ($row['source_page'] ?? ''),
                ]);
            }
        } catch (Throwable $e) {
            fputcsv($out, ['(database error — import charged_site_forms.sql)', '', '']);
        }
        fclose($out);
    }
    exit;
}

$rows = [];
$total = 0;
$tableOk = true;
try {
    $total = (int) $pdo->query("SELECT COUNT(*) FROM site_form_submissions WHERE form_type = 'subscribe'")->fetchColumn();
    $rows = $pdo->query(
        "SELECT id, email, source_page, created_at FROM site_form_submissions
         WHERE form_type = 'subscribe' ORDER BY created_at DESC LIMIT 2000"
    )->fetchAll();
} catch (Throwable $e) {
    $tableOk = false;
}

$charged_page_title = 'Newsletter subscribers — CHARGED Admin';
$charged_nav = 'admin-subscribers';
$CHARGED_ADMIN = true;
require_once __DIR__ . '/../includes/header.php';
?>

<div class="ch-container ch-admin-wrap">
  <div class="ch-admin-head">
    <div>
      <p class="ch-eyebrow">Admin</p>
      <h1>Newsletter subscribers</h1>
      <p class="ch-section-dek">Emails from the public subscribe form on the site. Same data as newsletter rows under <a href="form-submissions.php?view=subscribe">Forms → Newsletter filter</a>.</p>
    </div>
    <div class="ch-admin-head-actions" style="display:flex;flex-wrap:wrap;gap:0.5rem;align-items:center;">
      <?php if ($tableOk && $total > 0) : ?>
      <a class="ch-btn ch-btn--primary" href="subscribers.php?export=csv">Download CSV</a>
      <?php endif; ?>
      <a class="ch-btn ch-btn--dark" href="form-submissions.php?view=partner">Partner forms</a>
      <a class="ch-btn ch-btn--on-dark" href="dashboard.php">Dashboard</a>
    </div>
  </div>

  <?php if (!$tableOk) : ?>
  <div class="ch-alert ch-alert--error" role="alert">
    The database table is missing. Import <code>database/charged_site_forms.sql</code> into your CHARGED database, then refresh this page.
  </div>
  <?php else : ?>
  <p class="ch-section-dek" style="margin-bottom:1rem;"><strong><?php echo charged_e((string) $total); ?></strong> total sign-ups · Showing up to 2000 most recent.</p>

  <?php if ($rows === []) : ?>
  <p class="ch-section-dek">No subscribers yet. Submissions appear here when visitors use the newsletter form on the homepage or article pages.</p>
  <?php else : ?>
  <div class="ch-admin-table-wrap">
    <table class="ch-admin-table">
      <thead>
        <tr>
          <th>Signed up</th>
          <th>Email</th>
          <th>Source page</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($rows as $r) : ?>
        <tr>
          <td><?php echo charged_e($r['created_at']); ?></td>
          <td><a href="mailto:<?php echo charged_e($r['email']); ?>"><?php echo charged_e($r['email']); ?></a></td>
          <td style="max-width:280px;font-size:0.8rem;word-break:break-all;"><?php echo charged_e($r['source_page'] ?? ''); ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
