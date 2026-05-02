<?php

declare(strict_types=1);

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

$list = $pdo->query(
    "SELECT id, title, slug, excerpt, featured_image, published_at
     FROM articles
     WHERE status = 'published'
     ORDER BY published_at DESC, id DESC"
)->fetchAll();

$charged_page_title = 'Articles — CHARGED';
$charged_meta_description = 'Published articles and analysis from CHARGED.';
$charged_nav = 'articles';
$charged_extra_head = '';
require_once __DIR__ . '/includes/header.php';
?>

<header class="ch-page-hero">
  <div class="ch-container">
    <nav aria-label="Breadcrumb">
      <ol class="ch-breadcrumb">
        <li><a href="index.php">Home</a></li>
        <li aria-current="page">Articles</li>
      </ol>
    </nav>
    <h1>Articles</h1>
    <p class="ch-hero__dek ch-hero__dek--narrow">Published pieces from the CHARGED desk—fintech, e-mobility, and markets across Africa.</p>
  </div>
</header>

<section class="ch-section" aria-labelledby="articles-list-heading">
  <div class="ch-container">
    <h2 id="articles-list-heading" class="ch-sr-only">All published articles</h2>

    <?php if ($list === []) : ?>
    <p class="ch-section-dek">No published articles yet. Check back soon.</p>
    <?php else : ?>
    <div class="ch-card-grid ch-card-grid--3">
      <?php foreach ($list as $row) : ?>
      <article class="ch-card ch-card--issue">
        <?php if (!empty($row['featured_image'])) : ?>
        <div class="ch-card__visual" style="min-height:160px;background-size:cover;background-position:center;background-image:url(<?php echo charged_e($row['featured_image']); ?>);" role="img" aria-label=""></div>
        <?php else : ?>
        <div class="ch-card__visual" style="min-height:160px;background:var(--ch-surface-2);" aria-hidden="true"></div>
        <?php endif; ?>
        <div class="ch-tag-row">
          <span class="ch-badge">Article</span>
        </div>
        <h3 class="ch-card__title"><a href="article.php?slug=<?php echo charged_e($row['slug']); ?>"><?php echo charged_e($row['title']); ?></a></h3>
        <?php if (!empty($row['excerpt'])) : ?>
        <p class="ch-card__excerpt"><?php echo charged_e($row['excerpt']); ?></p>
        <?php endif; ?>
        <div class="ch-card__meta">
          <?php if (!empty($row['published_at'])) : ?>
          <time datetime="<?php echo charged_e(charged_format_date_iso($row['published_at'])); ?>"><?php echo charged_e(charged_format_date($row['published_at'])); ?></time>
          <?php endif; ?>
          <a class="ch-btn ch-btn--primary" href="article.php?slug=<?php echo charged_e($row['slug']); ?>">Read more</a>
        </div>
      </article>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
