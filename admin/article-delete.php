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

$st = $pdo->prepare('SELECT featured_image, gallery_images FROM articles WHERE id = ? LIMIT 1');
$st->execute([$id]);
$row = $st->fetch();
if (!$row) {
    header('Location: articles.php');
    exit;
}

charged_unlink_managed_article_image($row['featured_image'] ?? null);
foreach (charged_parse_article_gallery($row['gallery_images'] ?? null) as $gp) {
    charged_unlink_managed_article_image($gp);
}

$del = $pdo->prepare('DELETE FROM articles WHERE id = ?');
$del->execute([$id]);

header('Location: articles.php?deleted=1');
exit;
