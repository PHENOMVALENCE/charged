<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';

$total = (int) $pdo->query('SELECT COUNT(*) FROM articles')->fetchColumn();
$published = (int) $pdo->query("SELECT COUNT(*) FROM articles WHERE status = 'published'")->fetchColumn();
$drafts = (int) $pdo->query("SELECT COUNT(*) FROM articles WHERE status = 'draft'")->fetchColumn();

$subscribeCount = 0;
$partnerCount = 0;
$latestSubscribers = [];
$latestPartners = [];
$formsTableOk = true;
try {
    $subscribeCount = (int) $pdo->query("SELECT COUNT(*) FROM site_form_submissions WHERE form_type = 'subscribe'")->fetchColumn();
    $partnerCount = (int) $pdo->query("SELECT COUNT(*) FROM site_form_submissions WHERE form_type = 'partner'")->fetchColumn();
    $latestSubscribers = $pdo->query(
        "SELECT email, created_at FROM site_form_submissions WHERE form_type = 'subscribe' ORDER BY created_at DESC LIMIT 15"
    )->fetchAll();
    $latestPartners = $pdo->query(
        "SELECT email, name, message, created_at FROM site_form_submissions WHERE form_type = 'partner' ORDER BY created_at DESC LIMIT 8"
    )->fetchAll();
} catch (Throwable $e) {
    $formsTableOk = false;
}

$latest = $pdo->query(
    'SELECT a.id, a.title, a.slug, a.status, a.published_at, a.updated_at
     FROM articles a
     ORDER BY a.updated_at DESC
     LIMIT 5'
)->fetchAll();

$charged_page_title = 'Dashboard — CHARGED Admin';
$charged_nav = 'admin-dash';
$CHARGED_ADMIN = true;
require_once __DIR__ . '/../includes/header.php';
?>

<div class="ch-container ch-admin-wrap">
  <div class="ch-admin-head">
    <div>
      <p class="ch-eyebrow">Admin</p>
      <h1>Dashboard</h1>
      <p class="ch-section-dek">Signed in as <?php echo charged_e($_SESSION['admin_email'] ?? ''); ?>.</p>
    </div>
    <a class="ch-btn ch-btn--primary" href="article-create.php">Create new article</a>
  </div>

  <div class="ch-admin-stats">
    <div class="ch-admin-stat">
      <div class="ch-admin-stat__value"><?php echo charged_e((string) $total); ?></div>
      <div class="ch-admin-stat__label">Total articles</div>
    </div>
    <div class="ch-admin-stat">
      <div class="ch-admin-stat__value"><?php echo charged_e((string) $published); ?></div>
      <div class="ch-admin-stat__label">Published</div>
    </div>
    <div class="ch-admin-stat">
      <div class="ch-admin-stat__value"><?php echo charged_e((string) $drafts); ?></div>
      <div class="ch-admin-stat__label">Drafts</div>
    </div>
    <div class="ch-admin-stat">
      <div class="ch-admin-stat__value"><?php echo charged_e((string) $subscribeCount); ?></div>
      <div class="ch-admin-stat__label">Newsletter sign-ups</div>
    </div>
    <div class="ch-admin-stat">
      <div class="ch-admin-stat__value"><?php echo charged_e((string) $partnerCount); ?></div>
      <div class="ch-admin-stat__label">Partner inquiries</div>
    </div>
  </div>

  <?php if (!$formsTableOk) : ?>
  <div class="ch-alert ch-alert--error" role="alert" style="margin-bottom:1.5rem;">Form storage table missing. Import <code>database/charged_site_forms.sql</code> (or use the full <code>charged_articles.sql</code> schema).</div>
  <?php else : ?>

  <h2 class="ch-section-title" style="font-size:1.25rem;">Latest newsletter sign-ups</h2>
  <p class="ch-section-dek" style="margin-top:0;">Emails captured from the public subscribe form (newest first). <a href="subscribers.php">Open subscriber list &amp; CSV export</a>.</p>
  <div class="ch-admin-table-wrap" style="margin-top:0.75rem;margin-bottom:2rem;">
    <table class="ch-admin-table">
      <thead>
        <tr>
          <th>Email</th>
          <th>Signed up</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($latestSubscribers === []) : ?>
        <tr><td colspan="2">No subscribers yet.</td></tr>
        <?php else : ?>
        <?php foreach ($latestSubscribers as $s) : ?>
        <tr>
          <td><?php echo charged_e($s['email']); ?></td>
          <td><?php echo charged_e($s['created_at']); ?></td>
        </tr>
        <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <h2 class="ch-section-title" style="font-size:1.25rem;">Recent partner inquiries</h2>
  <p class="ch-section-dek" style="margin-top:0;">From the Advertise page inquiry form. <a href="form-submissions.php?view=partner">View all partner submissions</a>.</p>
  <div class="ch-admin-table-wrap" style="margin-top:0.75rem;margin-bottom:2rem;">
    <table class="ch-admin-table">
      <thead>
        <tr>
          <th>Date</th>
          <th>Email</th>
          <th>Name</th>
          <th>Message</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($latestPartners === []) : ?>
        <tr><td colspan="4">No partner inquiries yet.</td></tr>
        <?php else : ?>
        <?php foreach ($latestPartners as $p) : ?>
        <tr>
          <td><?php echo charged_e($p['created_at']); ?></td>
          <td><?php echo charged_e($p['email']); ?></td>
          <td><?php echo charged_e($p['name'] ?? ''); ?></td>
          <td style="max-width:240px;font-size:0.85rem;white-space:pre-wrap;"><?php
            $pm = (string) ($p['message'] ?? '');
            if (function_exists('mb_strimwidth')) {
                $pm = mb_strimwidth($pm, 0, 200, '…', 'UTF-8');
            } elseif (strlen($pm) > 200) {
                $pm = substr($pm, 0, 197) . '...';
            }
            echo charged_e($pm);
            ?></td>
        </tr>
        <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <?php endif; ?>

  <h2 class="ch-section-title" style="font-size:1.25rem;">Latest articles</h2>
  <div class="ch-admin-table-wrap" style="margin-top:0.75rem;">
    <table class="ch-admin-table">
      <thead>
        <tr>
          <th>Title</th>
          <th>Status</th>
          <th>Updated</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php if ($latest === []) : ?>
        <tr><td colspan="4">No articles yet. <a href="article-create.php">Create one</a>.</td></tr>
        <?php else : ?>
        <?php foreach ($latest as $row) : ?>
        <tr>
          <td><?php echo charged_e($row['title']); ?></td>
          <td>
            <?php if ($row['status'] === 'published') : ?>
            <span class="ch-badge ch-badge--published">Published</span>
            <?php else : ?>
            <span class="ch-badge ch-badge--draft">Draft</span>
            <?php endif; ?>
          </td>
          <td><?php echo charged_e($row['updated_at']); ?></td>
          <td><a href="article-edit.php?id=<?php echo (int) $row['id']; ?>">Edit</a></td>
        </tr>
        <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <p style="margin-top:1.5rem;">
    <a href="articles.php">View all articles</a>
    · <a href="subscribers.php">Newsletter subscribers</a>
    · <a href="form-submissions.php?view=partner">Partner forms</a>
    · <a href="form-submissions.php">All form submissions</a>
  </p>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
