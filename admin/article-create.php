<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!charged_verify_csrf()) {
        $errors[] = 'Invalid security token. Please try again.';
    } else {
        $title = trim((string) ($_POST['title'] ?? ''));
        $slugInput = trim((string) ($_POST['slug'] ?? ''));
        $excerpt = trim((string) ($_POST['excerpt'] ?? ''));
        $content = trim((string) ($_POST['content'] ?? ''));
        $content = charged_sanitize_article_html($content);
        $status = ($_POST['status'] ?? 'draft') === 'published' ? 'published' : 'draft';
        $publishedInput = trim((string) ($_POST['published_at'] ?? ''));

        if ($title === '') {
            $errors[] = 'Title is required.';
        }
        if (charged_article_body_is_empty($content)) {
            $errors[] = 'Content is required.';
        }

        $slugBase = $slugInput !== '' ? charged_slugify($slugInput) : charged_slugify($title);
        $slug = charged_unique_slug($pdo, $slugBase);

        $publishedAt = null;
        if ($status === 'published') {
            if ($publishedInput !== '') {
                $ts = strtotime($publishedInput);
                $publishedAt = $ts ? date('Y-m-d H:i:s', $ts) : date('Y-m-d H:i:s');
            } else {
                $publishedAt = date('Y-m-d H:i:s');
            }
        }

        $featuredPath = null;
        $uploadErr = '';
        if ($errors === [] && isset($_FILES['featured_image'])) {
            $featuredPath = charged_handle_featured_upload($_FILES['featured_image'], null, $uploadErr);
            if ($uploadErr !== '') {
                $errors[] = $uploadErr;
            }
        }

        if ($errors === []) {
            $st = $pdo->prepare(
                'INSERT INTO articles (title, slug, excerpt, content, featured_image, status, author_id, published_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
            );
            $st->execute([
                $title,
                $slug,
                $excerpt !== '' ? $excerpt : null,
                $content,
                $featuredPath,
                $status,
                (int) $_SESSION['admin_id'],
                $publishedAt,
            ]);
            header('Location: article-edit.php?id=' . (int) $pdo->lastInsertId() . '&saved=1');
            exit;
        }
    }
}

$charged_page_title = 'New article — CHARGED Admin';
$charged_nav = 'admin-create';
$CHARGED_ADMIN = true;
require_once __DIR__ . '/../includes/header.php';
?>

<div class="ch-container ch-admin-wrap">
  <div class="ch-admin-head">
    <div>
      <p class="ch-eyebrow">Admin</p>
      <h1>Create article</h1>
    </div>
    <a class="ch-btn ch-btn--on-dark" href="articles.php">Back to list</a>
  </div>

  <?php foreach ($errors as $msg) : ?>
  <div class="ch-alert ch-alert--error" role="alert"><?php echo charged_e($msg); ?></div>
  <?php endforeach; ?>

  <form id="article-editor-form" method="post" action="" enctype="multipart/form-data" class="ch-admin-form ch-admin-form--wide">
    <?php echo charged_csrf_field(); ?>
    <div class="ch-field">
      <label class="ch-label" for="title">Title <span style="color:var(--ch-accent);">*</span></label>
      <input class="ch-input" type="text" id="title" name="title" required maxlength="255"
        value="<?php echo charged_e($_POST['title'] ?? ''); ?>">
    </div>
    <div class="ch-field">
      <label class="ch-label" for="slug">Slug</label>
      <input class="ch-input" type="text" id="slug" name="slug" maxlength="255" placeholder="Auto from title if left blank"
        value="<?php echo charged_e($_POST['slug'] ?? ''); ?>">
      <p class="ch-field-hint">URL segment. Leave blank to generate from the title.</p>
    </div>
    <div class="ch-field">
      <label class="ch-label" for="excerpt">Excerpt</label>
      <textarea class="ch-input" id="excerpt" name="excerpt" rows="3" maxlength="2000"><?php echo charged_e($_POST['excerpt'] ?? ''); ?></textarea>
    </div>
    <div class="ch-field ch-field--editor">
      <label class="ch-label" for="content">Content <span style="color:var(--ch-accent);">*</span></label>
      <textarea class="ch-input ch-input--wysiwyg" id="content" name="content" required rows="16" cols="40"><?php echo charged_e($_POST['content'] ?? ''); ?></textarea>
      <p class="ch-field-hint">Use the editor for headings, lists, links, quotes, tables, and horizontal rules. Content is cleaned on save for safety.</p>
    </div>
    <div class="ch-field">
      <label class="ch-label" for="featured_image">Featured image</label>
      <input class="ch-input" type="file" id="featured_image" name="featured_image" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
      <p class="ch-field-hint">JPG, PNG, or WEBP. Max 2MB.</p>
    </div>
    <div class="ch-field">
      <label class="ch-label" for="status">Status</label>
      <select class="ch-input" id="status" name="status">
        <option value="draft"<?php echo (($_POST['status'] ?? '') === 'published') ? '' : ' selected'; ?>>Draft</option>
        <option value="published"<?php echo (($_POST['status'] ?? '') === 'published') ? ' selected' : ''; ?>>Published</option>
      </select>
    </div>
    <div class="ch-field">
      <label class="ch-label" for="published_at">Published date</label>
      <input class="ch-input" type="datetime-local" id="published_at" name="published_at" value="<?php echo charged_e($_POST['published_at'] ?? ''); ?>">
      <p class="ch-field-hint">Optional when publishing; defaults to now if empty.</p>
    </div>
    <div class="ch-admin-actions">
      <button type="submit" class="ch-btn ch-btn--primary">Save article</button>
    </div>
  </form>
</div>

<?php
$charged_include_article_editor = true;
require_once __DIR__ . '/../includes/footer.php';
?>
