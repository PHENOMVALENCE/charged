<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';

$adminId = (int) ($_SESSION['admin_id'] ?? 0);
if ($adminId < 1) {
    header('Location: login.php');
    exit;
}

$st = $pdo->prepare('SELECT id, name, email, password, created_at FROM admins WHERE id = ? LIMIT 1');
$st->execute([$adminId]);
$admin = $st->fetch();
if (!$admin) {
    header('Location: logout.php');
    exit;
}

$errors = [];
$success = !empty($_GET['saved']) ? 'Profile updated.' : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!charged_verify_csrf()) {
        $errors[] = 'Invalid security token. Please try again.';
    } else {
        $name = trim((string) ($_POST['name'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $currentPassword = (string) ($_POST['current_password'] ?? '');
        $newPassword = trim((string) ($_POST['new_password'] ?? ''));
        $newPassword2 = trim((string) ($_POST['new_password_confirm'] ?? ''));

        if ($name === '' || charged_utf8_strlen($name) > 120) {
            $errors[] = 'Display name is required (max 120 characters).';
        }
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please enter a valid email address.';
        }
        if (charged_utf8_strlen($email) > 190) {
            $errors[] = 'Email is too long.';
        }
        if ($currentPassword === '') {
            $errors[] = 'Enter your current password to save changes.';
        } elseif (!password_verify($currentPassword, $admin['password'])) {
            $errors[] = 'Current password is incorrect.';
        }

        if ($newPassword !== '' || $newPassword2 !== '') {
            if ($newPassword !== $newPassword2) {
                $errors[] = 'New password and confirmation do not match.';
            } elseif (strlen($newPassword) < 8) {
                $errors[] = 'New password must be at least 8 characters.';
            }
        }

        if ($errors === [] && strcasecmp($email, (string) $admin['email']) !== 0) {
            $chk = $pdo->prepare('SELECT id FROM admins WHERE email = ? AND id != ? LIMIT 1');
            $chk->execute([$email, $adminId]);
            if ($chk->fetch()) {
                $errors[] = 'That email address is already in use by another account.';
            }
        }

        if ($errors === []) {
            $passwordHash = (string) $admin['password'];
            if ($newPassword !== '') {
                $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
            }

            $up = $pdo->prepare('UPDATE admins SET name = ?, email = ?, password = ? WHERE id = ?');
            $up->execute([$name, $email, $passwordHash, $adminId]);

            $_SESSION['admin_name'] = $name;
            $_SESSION['admin_email'] = $email;
            $admin['name'] = $name;
            $admin['email'] = $email;
            $admin['password'] = $passwordHash;

            if ($newPassword !== '') {
                session_regenerate_id(true);
            }

            header('Location: profile.php?saved=1');
            exit;
        }
    }
}

$charged_page_title = 'Your profile — CHARGED Admin';
$charged_nav = 'admin-profile';
$CHARGED_ADMIN = true;
require_once __DIR__ . '/../includes/header.php';
?>

<div class="ch-container ch-admin-wrap">
  <div class="ch-admin-head">
    <div>
      <p class="ch-eyebrow">Admin</p>
      <h1>Your profile</h1>
      <p class="ch-section-dek">Update the name and email used for this admin account. Member since <?php echo charged_e($admin['created_at'] ?? ''); ?>.</p>
    </div>
    <a class="ch-btn ch-btn--on-dark" href="dashboard.php">Dashboard</a>
  </div>

  <?php if ($success !== '') : ?>
  <div class="ch-alert ch-alert--success" role="status"><?php echo charged_e($success); ?></div>
  <?php endif; ?>

  <?php if ($errors !== []) : ?>
  <div class="ch-alert ch-alert--error" role="alert">
    <?php foreach ($errors as $err) : ?>
    <div><?php echo charged_e($err); ?></div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <form method="post" action="" class="ch-admin-form" autocomplete="off">
    <?php echo charged_csrf_field(); ?>

    <div class="ch-field">
      <label class="ch-label" for="profile-name">Display name</label>
      <input class="ch-input" type="text" id="profile-name" name="name" required maxlength="120" autocomplete="name"
        value="<?php echo charged_e($_POST['name'] ?? $admin['name']); ?>">
    </div>

    <div class="ch-field">
      <label class="ch-label" for="profile-email">Email (sign-in)</label>
      <input class="ch-input" type="email" id="profile-email" name="email" required maxlength="190" autocomplete="email"
        value="<?php echo charged_e($_POST['email'] ?? $admin['email']); ?>">
    </div>

    <fieldset class="ch-field" style="border:1px solid var(--ch-border);border-radius:var(--ch-radius);padding:1rem 1.1rem;margin:0;">
      <legend class="ch-label" style="padding:0 0.35rem;">Change password</legend>
      <p class="ch-field-hint" style="margin-top:0;">Leave blank to keep your current password.</p>
      <div class="ch-field" style="margin-bottom:0.75rem;">
        <label class="ch-label" for="profile-new-pass">New password</label>
        <div class="ch-password-wrap">
          <input class="ch-input" type="password" id="profile-new-pass" name="new_password" minlength="8" maxlength="200" autocomplete="new-password">
        </div>
      </div>
      <div class="ch-field" style="margin-bottom:0;">
        <label class="ch-label" for="profile-new-pass2">Confirm new password</label>
        <div class="ch-password-wrap">
          <input class="ch-input" type="password" id="profile-new-pass2" name="new_password_confirm" minlength="8" maxlength="200" autocomplete="new-password">
        </div>
      </div>
    </fieldset>

    <div class="ch-field" style="margin-top:1.25rem;">
      <label class="ch-label" for="profile-current">Current password</label>
      <div class="ch-password-wrap">
        <input class="ch-input" type="password" id="profile-current" name="current_password" required maxlength="200" autocomplete="current-password">
      </div>
      <p class="ch-field-hint">Required to confirm any change to your profile. The password on file cannot be shown (it is stored as a secure hash). Use <strong>Show</strong> to reveal what you type.</p>
    </div>

    <div class="ch-admin-actions">
      <button type="submit" class="ch-btn ch-btn--primary">Save profile</button>
    </div>
  </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
