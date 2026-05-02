<?php
/**
 * Admin login — session-based; redirects to dashboard if already logged in.
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

charged_session_start();

if (!empty($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    if ($email === '' || $password === '') {
        $error = 'Please enter your email and password.';
    } else {
        $st = $pdo->prepare('SELECT id, name, email, password FROM admins WHERE email = ? LIMIT 1');
        $st->execute([$email]);
        $row = $st->fetch();
        if (!$row || !password_verify($password, $row['password'])) {
            $error = 'Invalid email or password.';
        } else {
            session_regenerate_id(true);
            $_SESSION['admin_id'] = (int) $row['id'];
            $_SESSION['admin_name'] = $row['name'];
            $_SESSION['admin_email'] = $row['email'];
            header('Location: dashboard.php');
            exit;
        }
    }
}

$charged_page_title = 'Admin sign-in — CHARGED';
$charged_meta_description = 'Sign in to the CHARGED editorial admin.';
$charged_nav = 'home';
$CHARGED_ADMIN = false;
$charged_login_page = true;
$charged_from_admin_dir = true;
require_once __DIR__ . '/../includes/header.php';
?>

<section class="ch-admin-login" aria-labelledby="login-heading">
  <div class="ch-admin-login__mesh" aria-hidden="true"></div>
  <div class="ch-admin-login__glow" aria-hidden="true"></div>

  <div class="ch-admin-login__card">
    <header class="ch-admin-login__head">
      <div class="ch-admin-login__mark" aria-hidden="true">
        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" width="28" height="28">
          <path d="M13 2L6 14h5l-2 10 10-16h-6l0-6z" fill="currentColor"/>
        </svg>
      </div>
      <div class="ch-admin-login__titles">
        <p class="ch-admin-login__eyebrow">CHARGED</p>
        <h1 id="login-heading" class="ch-admin-login__title">Admin sign-in</h1>
        <p class="ch-admin-login__dek">Publish and edit articles for the public site.</p>
      </div>
    </header>

    <?php if ($error !== '') : ?>
    <div class="ch-alert ch-alert--error ch-admin-login__alert" role="alert"><?php echo charged_e($error); ?></div>
    <?php endif; ?>

    <form method="post" action="" class="ch-admin-login__form" id="admin-login-form" autocomplete="on" novalidate>
      <div class="ch-field">
        <label class="ch-label" for="email">Email</label>
        <input class="ch-input ch-input--login" type="email" id="email" name="email" required autocomplete="username" inputmode="email" placeholder="you@company.com"
          value="<?php echo charged_e($_POST['email'] ?? ''); ?>">
      </div>
      <div class="ch-field">
        <label class="ch-label" for="password">Password</label>
        <input class="ch-input ch-input--login" type="password" id="password" name="password" required autocomplete="current-password" placeholder="••••••••">
      </div>
      <button type="submit" class="ch-btn ch-btn--primary ch-admin-login__submit">Sign in</button>
    </form>

    <p class="ch-admin-login__fineprint">For authorized editors only. Sessions are secured with cookies.</p>
  </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
