<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';

$id = (int) ($_GET['id'] ?? 0);
if ($id < 1) {
    header('Location: articles.php');
    exit;
}

$st = $pdo->prepare('SELECT * FROM articles WHERE id = ? LIMIT 1');
$st->execute([$id]);
$article = $st->fetch();
if (!$article) {
    header('Location: articles.php');
    exit;
}

$errors = [];
if (!empty($_GET['saved'])) {
    $success = 'Article saved.';
} else {
    $success = '';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!charged_verify_csrf()) {
        $errors[] = 'Invalid security token. Please try again.';
    } else {
        $title = trim((string) ($_POST['title'] ?? ''));
        $slugInput = trim((string) ($_POST['slug'] ?? ''));
        $excerpt = trim((string) ($_POST['excerpt'] ?? ''));
        $contentRaw = trim((string) ($_POST['content'] ?? ''));
        $content = charged_sanitize_article_html($contentRaw);
        $status = ($_POST['status'] ?? 'draft') === 'published' ? 'published' : 'draft';
        $publishedInput = trim((string) ($_POST['published_at'] ?? ''));

        if ($title === '') {
            $errors[] = 'Title is required.';
        }
        if (charged_article_body_is_empty($content)) {
            $errors[] = 'Content is required.';
        }

        $slugBase = $slugInput !== '' ? charged_slugify($slugInput) : charged_slugify($title);
        $slug = charged_unique_slug($pdo, $slugBase, $id);

        $publishedAt = null;
        if ($status === 'published') {
            if ($publishedInput !== '') {
                $ts = strtotime($publishedInput);
                $publishedAt = $ts ? date('Y-m-d H:i:s', $ts) : date('Y-m-d H:i:s');
            } elseif (!empty($article['published_at'])) {
                $publishedAt = $article['published_at'];
            } else {
                $publishedAt = date('Y-m-d H:i:s');
            }
        } else {
            // Draft: preserve previous published timestamp for editorial history
            $publishedAt = $article['published_at'];
        }

        $featuredPath = $article['featured_image'];
        $uploadErr = '';
        if ($errors === [] && isset($_FILES['featured_image'])) {
            $newPath = charged_handle_featured_upload($_FILES['featured_image'], $featuredPath, $uploadErr);
            if ($uploadErr !== '') {
                $errors[] = $uploadErr;
            } else {
                $featuredPath = $newPath;
            }
        }

        if ($errors === []) {
            $up = $pdo->prepare(
                'UPDATE articles SET title = ?, slug = ?, excerpt = ?, content = ?, featured_image = ?,
                 status = ?, published_at = ? WHERE id = ?'
            );
            $up->execute([
                $title,
                $slug,
                $excerpt !== '' ? $excerpt : null,
                $content,
                $featuredPath,
                $status,
                $publishedAt,
                $id,
            ]);
            header('Location: article-edit.php?id=' . $id . '&saved=1');
            exit;
        }
    }
}

// Reload on validation error so form shows POST data; else use DB row
if ($errors !== [] && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $article = array_merge($article, [
        'title' => $_POST['title'] ?? $article['title'],
        'slug' => $_POST['slug'] ?? $article['slug'],
        'excerpt' => $_POST['excerpt'] ?? $article['excerpt'],
        'content' => $_POST['content'] ?? $article['content'],
        'status' => ($_POST['status'] ?? 'draft') === 'published' ? 'published' : 'draft',
        'published_at' => $_POST['published_at'] ?? $article['published_at'],
    ]);
}

$dtLocal = '';
if (!empty($article['published_at'])) {
    $ts = strtotime((string) $article['published_at']);
    if ($ts) {
        $dtLocal = date('Y-m-d\TH:i', $ts);
    }
}

$charged_page_title = 'Edit article — CHARGED Admin';
$charged_nav = 'admin-articles';
$CHARGED_ADMIN = true;
require_once __DIR__ . '/../includes/header.php';
?>

<div class="ch-container ch-admin-wrap">
  <div class="ch-admin-head">
    <div>
      <p class="ch-eyebrow">Admin</p>
      <h1>Edit article</h1>
    </div>
    <a class="ch-btn ch-btn--on-dark" href="articles.php">Back to list</a>
  </div>

  <?php if ($success !== '') : ?>
  <div class="ch-alert ch-alert--success" role="status"><?php echo charged_e($success); ?></div>
  <?php endif; ?>
  <?php foreach ($errors as $msg) : ?>
  <div class="ch-alert ch-alert--error" role="alert"><?php echo charged_e($msg); ?></div>
  <?php endforeach; ?>

  <form id="article-editor-form" method="post" action="" enctype="multipart/form-data" class="ch-admin-form ch-admin-form--wide">
    <?php echo charged_csrf_field(); ?>
    <input type="hidden" name="id" value="<?php echo (int) $article['id']; ?>">

    <div class="ch-field">
      <label class="ch-label" for="title">Title <span style="color:var(--ch-accent);">*</span></label>
      <input class="ch-input" type="text" id="title" name="title" required maxlength="255"
        value="<?php echo charged_e((string) $article['title']); ?>">
    </div>
    <div class="ch-field">
      <label class="ch-label" for="slug">Slug</label>
      <input class="ch-input" type="text" id="slug" name="slug" maxlength="255"
        value="<?php echo charged_e((string) $article['slug']); ?>">
    </div>
    <div class="ch-field">
      <label class="ch-label" for="excerpt">Excerpt</label>
      <textarea class="ch-input" id="excerpt" name="excerpt" rows="3" maxlength="2000"><?php echo charged_e((string) ($article['excerpt'] ?? '')); ?></textarea>
    </div>
    <div class="ch-field ch-field--editor">
      <label class="ch-label" for="content">Content <span style="color:var(--ch-accent);">*</span></label>
      <textarea class="ch-input ch-input--wysiwyg" id="content" name="content" required rows="16" cols="40"><?php echo charged_e((string) $article['content']); ?></textarea>
      <p class="ch-field-hint">Rich text: headings, lists, links, block quotes, tables, and more. Sanitized automatically on save.</p>
    </div>
    <div class="ch-field">
      <label class="ch-label" for="featured_image">Featured image</label>
      <?php if (!empty($article['featured_image'])) : ?>
      <p class="ch-field-hint ch-admin-inline">Current:</p>
      <img class="ch-admin-thumb" src="<?php echo charged_e('../' . $article['featured_image']); ?>" alt="">
      <?php endif; ?>
      <input class="ch-input" type="file" id="featured_image" name="featured_image" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
      <p class="ch-field-hint">JPG, PNG, or WEBP. Max 2MB. Leave empty to keep the current image.</p>
    </div>
    <div class="ch-field">
      <label class="ch-label" for="status">Status</label>
      <select class="ch-input" id="status" name="status">
        <option value="draft"<?php echo $article['status'] === 'draft' ? ' selected' : ''; ?>>Draft</option>
        <option value="published"<?php echo $article['status'] === 'published' ? ' selected' : ''; ?>>Published</option>
      </select>
    </div>
    <div class="ch-field">
      <label class="ch-label" for="published_at">Published date</label>
      <input class="ch-input" type="datetime-local" id="published_at" name="published_at" value="<?php echo charged_e($dtLocal); ?>">
    </div>
    <div class="ch-admin-actions">
      <button type="submit" class="ch-btn ch-btn--primary">Save changes</button>
    </div>
  </form>

  <hr style="border:0;border-top:1px solid var(--ch-border);margin:2.5rem 0 1.5rem;">

  <h2 class="ch-section-title" style="font-size:1.1rem;">Delete article</h2>
  <p class="ch-section-dek">This cannot be undone. The featured image file will be removed if stored in uploads.</p>
  <form method="post" action="article-delete.php" onsubmit="return confirm('Delete this article permanently?');">
    <?php echo charged_csrf_field(); ?>
    <input type="hidden" name="id" value="<?php echo (int) $article['id']; ?>">
    <button type="submit" class="ch-btn ch-btn--on-dark" style="border-color:var(--ch-border-strong);">Delete article</button>
  </form>
</div>

<?php
$charged_include_article_editor = true;
require_once __DIR__ . '/../includes/footer.php';
?>
