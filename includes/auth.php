<?php
/**
 * Protect admin pages: requires logged-in admin session.
 * Include this at the top of every file under /admin/ except login.php and logout.php.
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/functions.php';

charged_session_start();

if (empty($_SESSION['admin_id']) || empty($_SESSION['admin_email'])) {
    header('Location: login.php');
    exit;
}
