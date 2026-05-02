<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';

$rows = $pdo->query(
    'SELECT id, title, slug, status, published_at, updated_at FROM articles ORDER BY updated_at DESC'
)->fetchAll();

$charged_page_title = 'Articles — CHARGED Admin';
$charged_nav = 'admin-articles';
$CHARGED_ADMIN = true;
require_once __DIR__ . '/../includes/header.php';

$deletedFlash = isset($_GET['deleted']);
?>

<div class="ch-container ch-admin-wrap">
  <?php if ($deletedFlash) : ?>
  <div class="ch-alert ch-alert--success" role="status">Article deleted.</div>
  <?php endif; ?>
  <div class="ch-admin-head">
    <div>
      <p class="ch-eyebrow">Admin</p>
      <h1>All articles</h1>
    </div>
    <a class="ch-btn ch-btn--primary" href="article-create.php">Create new article</a>
  </div>

  <div class="ch-admin-table-wrap">
    <table class="ch-admin-table">
      <thead>
        <tr>
          <th>Title</th>
          <th>Slug</th>
          <th>Status</th>
          <th>Published</th>
          <th>Updated</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php if ($rows === []) : ?>
        <tr><td colspan="6">No articles yet.</td></tr>
        <?php else : ?>
        <?php foreach ($rows as $r) : ?>
        <tr>
          <td><?php echo charged_e($r['title']); ?></td>
          <td><code style="font-size:0.8rem;"><?php echo charged_e($r['slug']); ?></code></td>
          <td>
            <?php if ($r['status'] === 'published') : ?>
            <span class="ch-badge ch-badge--published">Published</span>
            <?php else : ?>
            <span class="ch-badge ch-badge--draft">Draft</span>
            <?php endif; ?>
          </td>
          <td><?php echo charged_e($r['published_at'] ?? '—'); ?></td>
          <td><?php echo charged_e($r['updated_at']); ?></td>
          <td>
            <a href="article-edit.php?id=<?php echo (int) $r['id']; ?>">Edit</a>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
