<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('Method not allowed.');
}

if (!charged_verify_csrf()) {
    http_response_code(403);
    exit('Invalid security token.');
}

$id = (int) ($_POST['id'] ?? 0);
if ($id < 1) {
    header('Location: articles.php');
    exit;
}

$st = $pdo->prepare('SELECT featured_image FROM articles WHERE id = ? LIMIT 1');
$st->execute([$id]);
$row = $st->fetch();
if (!$row) {
    header('Location: articles.php');
    exit;
}

$img = $row['featured_image'] ?? '';
if ($img && str_starts_with($img, 'uploads/articles/')) {
    $path = CHARGED_ROOT . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $img);
    if (is_file($path)) {
        @unlink($path);
    }
}

$del = $pdo->prepare('DELETE FROM articles WHERE id = ?');
$del->execute([$id]);

header('Location: articles.php?deleted=1');
exit;
