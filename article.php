<?php

declare(strict_types=1);

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

$slug = trim((string) ($_GET['slug'] ?? ''));
$article = null;

if ($slug !== '') {
    $st = $pdo->prepare(
        "SELECT title, slug, excerpt, content, featured_image, gallery_images, published_at
         FROM articles WHERE slug = ? AND status = 'published' LIMIT 1"
    );
    $st->execute([$slug]);
    $article = $st->fetch() ?: null;
}

if ($article) {
    $articleGallery = charged_parse_article_gallery($article['gallery_images'] ?? null);
    $charged_page_title = $article['title'] . ' — CHARGED';
    $charged_meta_description = $article['excerpt'] ?? '';
    $charged_nav = 'articles';
    $charged_extra_head = '';
} else {
    $charged_page_title = 'Article not found — CHARGED';
    $charged_meta_description = '';
    $charged_nav = 'articles';
    $charged_extra_head = '';
}

require_once __DIR__ . '/includes/header.php';
?>

<?php if (!$article) : ?>
<header class="ch-page-hero">
  <div class="ch-container ch-container--narrow">
    <nav aria-label="Breadcrumb">
      <ol class="ch-breadcrumb">
        <li><a href="index.php">Home</a></li>
        <li><a href="articles.php">Articles</a></li>
        <li aria-current="page">Not found</li>
      </ol>
    </nav>
    <h1>Article not found</h1>
    <p class="ch-hero__dek">This article does not exist or is not published.</p>
    <p style="margin-top:1.5rem;"><a class="ch-btn ch-btn--primary" href="articles.php">Back to articles</a></p>
  </div>
</header>
<?php else : ?>
<header class="ch-page-hero">
  <div class="ch-container ch-container--narrow">
    <nav aria-label="Breadcrumb">
      <ol class="ch-breadcrumb">
        <li><a href="index.php">Home</a></li>
        <li><a href="articles.php">Articles</a></li>
        <li aria-current="page"><?php echo charged_e($article['title']); ?></li>
      </ol>
    </nav>
    <p class="ch-kicker">CHARGED · Article</p>
    <h1><?php echo charged_e($article['title']); ?></h1>
    <?php if (!empty($article['excerpt'])) : ?>
    <p class="ch-hero__dek"><?php echo charged_e($article['excerpt']); ?></p>
    <?php endif; ?>
    <div class="ch-meta-row">
      <?php if (!empty($article['published_at'])) : ?>
      <time datetime="<?php echo charged_e(charged_format_date_iso($article['published_at'])); ?>">Published <?php echo charged_e(charged_format_date($article['published_at'])); ?></time>
      <?php endif; ?>
    </div>
  </div>
</header>

<div class="ch-article-wrap ch-section">
  <div class="ch-container">
  <article class="ch-article<?php echo !empty($article['featured_image']) ? ' ch-article--has-feature' : ''; ?>" itemscope itemtype="https://schema.org/NewsArticle">
    <?php if (!empty($article['featured_image'])) : ?>
    <div class="ch-article__split">
      <figure class="ch-article__media">
        <img src="<?php echo charged_e($article['featured_image']); ?>" alt="<?php echo charged_e($article['title']); ?>" loading="eager" decoding="async">
      </figure>
      <div class="ch-article__content">
        <div class="ch-article__body" id="article-body">
          <?php echo charged_render_article_body((string) $article['content']); ?>
        </div>
        <?php if ($articleGallery !== []) : ?>
        <div class="ch-article__gallery" role="group" aria-label="Image gallery">
          <div class="ch-article__gallery-grid">
            <?php foreach ($articleGallery as $gurl) : ?>
            <a class="ch-article__gallery-link" href="<?php echo charged_e($gurl); ?>" target="_blank" rel="noopener noreferrer">
              <img class="ch-article__gallery-img" src="<?php echo charged_e($gurl); ?>" alt="" loading="lazy" decoding="async">
            </a>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endif; ?>
        <p class="ch-article__footer-actions">
          <a class="ch-btn ch-btn--on-dark" href="articles.php">Back to articles</a>
        </p>
      </div>
    </div>
    <?php else : ?>
    <div class="ch-article__body" id="article-body">
      <?php echo charged_render_article_body((string) $article['content']); ?>
    </div>
    <?php if ($articleGallery !== []) : ?>
    <div class="ch-article__gallery" role="group" aria-label="Image gallery">
      <div class="ch-article__gallery-grid">
        <?php foreach ($articleGallery as $gurl) : ?>
        <a class="ch-article__gallery-link" href="<?php echo charged_e($gurl); ?>" target="_blank" rel="noopener noreferrer">
          <img class="ch-article__gallery-img" src="<?php echo charged_e($gurl); ?>" alt="" loading="lazy" decoding="async">
        </a>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>
    <p class="ch-article__footer-actions">
      <a class="ch-btn ch-btn--on-dark" href="articles.php">Back to articles</a>
    </p>
    <?php endif; ?>
  </article>
  </div>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
