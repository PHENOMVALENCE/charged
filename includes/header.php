<?php
/**
 * Site header — CHARGED public nav or admin nav.
 * Set before include:
 *   $charged_page_title (string)
 *   $charged_meta_description (optional string)
 *   $charged_nav (string): home | articles | archive | about | advertise | privacy | admin
 *   $CHARGED_ADMIN (bool, optional): true when serving from /admin/
 *   $charged_login_page (bool, optional): dedicated admin sign-in layout + admin.css
 *   $charged_from_admin_dir (bool, optional): set on admin/login.php so ../ asset and site links resolve
 *   $charged_extra_head (optional string) — extra HTML for <head>
 */

declare(strict_types=1);

require_once __DIR__ . '/functions.php';

if (!isset($charged_page_title)) {
    $charged_page_title = 'CHARGED';
}
$charged_meta_description = $charged_meta_description ?? '';
$charged_nav = $charged_nav ?? 'home';
$CHARGED_ADMIN = !empty($CHARGED_ADMIN);
$charged_login_page = !empty($charged_login_page);
$charged_from_admin_dir = !empty($charged_from_admin_dir);
$charged_extra_head = $charged_extra_head ?? '';

$prefix = ($CHARGED_ADMIN || $charged_from_admin_dir) ? '../' : '';
$assets = $prefix . 'assets/';

function charged_nav_attrs(string $key, string $current, string $aria = 'aria-current="page"'): string
{
    return $key === $current ? ' ' . $aria : '';
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title><?php echo charged_e($charged_page_title); ?></title>
  <?php if ($charged_meta_description !== '') : ?>
  <meta name="description" content="<?php echo charged_e($charged_meta_description); ?>">
  <?php endif; ?>
  <link rel="icon" href="<?php echo charged_e($assets); ?>images/favicon.svg" type="image/svg+xml">
  <link rel="stylesheet" href="<?php echo charged_e($assets); ?>css/charged.css">
  <?php if ($CHARGED_ADMIN || $charged_login_page) : ?>
  <link rel="stylesheet" href="<?php echo charged_e($assets); ?>css/admin.css">
  <?php endif; ?>
  <?php echo $charged_extra_head; ?>
  <meta name="theme-color" content="#f2ede4" media="(prefers-color-scheme: light)">
  <meta name="theme-color" content="#080810" media="(prefers-color-scheme: dark)">
</head>
<body>
  <a class="ch-skip" href="<?php echo $charged_login_page ? '#admin-login-form' : '#main'; ?>"><?php echo $CHARGED_ADMIN ? 'Skip to admin content' : ($charged_login_page ? 'Skip to sign in' : 'Skip to content'); ?></a>

  <div class="ch-page<?php echo $charged_login_page ? ' ch-page--admin-login' : ''; ?>">
    <header class="ch-header<?php echo $charged_login_page ? ' ch-header--login' : ''; ?>" id="top">
      <div class="ch-container ch-header__inner<?php echo $charged_login_page ? ' ch-header__inner--login' : ''; ?>">
        <a class="ch-logo" href="<?php echo charged_e($prefix); ?>index.php" aria-label="CHARGED home">
          <span class="ch-logo__mark" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
              <path d="M13 2L6 14h5l-2 10 10-16h-6l0-6z" fill="currentColor"/>
            </svg>
          </span>
          <span class="ch-logo__text">CHARGED</span>
        </a>

        <?php if ($CHARGED_ADMIN) : ?>
        <nav class="ch-nav ch-nav--admin" aria-label="Admin">
          <a href="dashboard.php"<?php echo charged_nav_attrs('admin-dash', $charged_nav); ?>>Dashboard</a>
          <a href="articles.php"<?php echo charged_nav_attrs('admin-articles', $charged_nav); ?>>Articles</a>
          <a href="subscribers.php" class="ch-admin-nav-btn ch-admin-nav-btn--newsletter<?php echo $charged_nav === 'admin-subscribers' ? ' is-active' : ''; ?>"<?php echo charged_nav_attrs('admin-subscribers', $charged_nav); ?>>Newsletter</a>
          <a href="form-submissions.php" class="ch-admin-nav-btn ch-admin-nav-btn--forms<?php echo $charged_nav === 'admin-forms' ? ' is-active' : ''; ?>"<?php echo charged_nav_attrs('admin-forms', $charged_nav); ?>>Forms</a>
          <a href="article-create.php"<?php echo charged_nav_attrs('admin-create', $charged_nav); ?>>New</a>
          <a href="profile.php"<?php echo charged_nav_attrs('admin-profile', $charged_nav); ?>>Profile</a>
          <a href="<?php echo charged_e($prefix); ?>articles.php">View site</a>
          <a href="logout.php">Log out</a>
        </nav>
        <?php elseif ($charged_login_page) : ?>
        <p class="ch-header__login-dek"><span class="ch-eyebrow">Secure area</span> Sign in to manage articles and media.</p>
        <?php else : ?>
        <nav class="ch-nav" aria-label="Primary">
          <a href="<?php echo charged_e($prefix); ?>index.php"<?php echo charged_nav_attrs('home', $charged_nav); ?>>Home</a>
          <a href="<?php echo charged_e($prefix); ?>articles.php"<?php echo charged_nav_attrs('articles', $charged_nav); ?>>Articles</a>
          <a href="<?php echo charged_e($prefix); ?>archive.html"<?php echo charged_nav_attrs('archive', $charged_nav); ?>>Archive</a>
          <a href="<?php echo charged_e($prefix); ?>about.html"<?php echo charged_nav_attrs('about', $charged_nav); ?>>About</a>
          <a href="<?php echo charged_e($prefix); ?>advertise.html"<?php echo charged_nav_attrs('advertise', $charged_nav); ?>>Advertise</a>
          <a href="<?php echo charged_e($prefix); ?>privacy.html"<?php echo charged_nav_attrs('privacy', $charged_nav); ?>>Privacy</a>
        </nav>
        <?php endif; ?>

        <div class="ch-header__actions">
          <?php if (!$CHARGED_ADMIN) : ?>
          <button type="button" class="ch-icon-btn" id="theme-toggle" aria-label="Switch color theme">
            <svg class="ch-theme-icon ch-theme-icon--sun" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
              <circle cx="12" cy="12" r="4" stroke="currentColor" stroke-width="2"/>
              <path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            </svg>
            <svg class="ch-theme-icon ch-theme-icon--moon" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
              <path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </button>
          <?php if ($charged_login_page) : ?>
          <a class="ch-btn ch-btn--on-dark ch-header__back-site" href="<?php echo charged_e($prefix); ?>index.php">Back to site</a>
          <?php else : ?>
          <a class="ch-btn ch-btn--primary ch-hide-mobile" href="<?php echo charged_e($prefix); ?>index.php#subscribe">Subscribe</a>
          <?php endif; ?>
          <?php endif; ?>
          <?php if (!$charged_login_page) : ?>
          <button type="button" class="ch-nav-toggle" id="nav-toggle" aria-expanded="false" aria-controls="mobile-nav" aria-label="Open menu">
            <span></span><span></span><span></span>
          </button>
          <?php endif; ?>
        </div>
      </div>
      <?php if (!$charged_login_page) : ?>
      <div class="ch-mobile-nav" id="mobile-nav" aria-hidden="true">
        <ul>
          <?php if ($CHARGED_ADMIN) : ?>
          <li><a href="dashboard.php">Dashboard</a></li>
          <li><a href="articles.php">Articles</a></li>
          <li><a href="subscribers.php" class="ch-admin-nav-btn ch-admin-nav-btn--newsletter<?php echo $charged_nav === 'admin-subscribers' ? ' is-active' : ''; ?>"<?php echo charged_nav_attrs('admin-subscribers', $charged_nav); ?>>Newsletter</a></li>
          <li><a href="form-submissions.php" class="ch-admin-nav-btn ch-admin-nav-btn--forms<?php echo $charged_nav === 'admin-forms' ? ' is-active' : ''; ?>"<?php echo charged_nav_attrs('admin-forms', $charged_nav); ?>>Forms</a></li>
          <li><a href="article-create.php">New article</a></li>
          <li><a href="profile.php"<?php echo charged_nav_attrs('admin-profile', $charged_nav); ?>>Profile</a></li>
          <li><a href="<?php echo charged_e($prefix); ?>articles.php">View site</a></li>
          <li><a href="logout.php">Log out</a></li>
          <?php else : ?>
          <li><a href="<?php echo charged_e($prefix); ?>index.php">Home</a></li>
          <li><a href="<?php echo charged_e($prefix); ?>articles.php">Articles</a></li>
          <li><a href="<?php echo charged_e($prefix); ?>archive.html">Archive</a></li>
          <li><a href="<?php echo charged_e($prefix); ?>about.html">About</a></li>
          <li><a href="<?php echo charged_e($prefix); ?>advertise.html">Advertise</a></li>
          <li><a href="<?php echo charged_e($prefix); ?>privacy.html">Privacy</a></li>
          <li><a href="<?php echo charged_e($prefix); ?>index.php#subscribe">Subscribe</a></li>
          <?php endif; ?>
        </ul>
      </div>
      <?php endif; ?>
    </header>

    <main class="ch-main<?php echo $charged_login_page ? ' ch-main--admin-login' : ''; ?>" id="main">
