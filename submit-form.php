<?php

declare(strict_types=1);

/**
 * Public form API: newsletter subscribe + partner inquiry.
 * GET  ?action=csrf  → JSON { ok, token } (same-origin session cookie)
 * POST JSON or form  → JSON { ok } | { ok:false, error }
 */

require_once __DIR__ . '/includes/functions.php';

charged_session_start();

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'GET' && ($_GET['action'] ?? '') === 'csrf') {
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode(['ok' => true, 'token' => charged_public_form_csrf_token()]);

    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    http_response_code(405);
    header('Content-Type: application/json; charset=UTF-8');
    header('Allow: GET, POST');
    echo json_encode(['ok' => false, 'error' => 'Method not allowed']);

    exit;
}

require_once __DIR__ . '/config/db.php';

header('Content-Type: application/json; charset=UTF-8');

$data = $_POST;
$contentType = $_SERVER['CONTENT_TYPE'] ?? '';
if (is_string($contentType) && str_contains(strtolower($contentType), 'application/json')) {
    $raw = file_get_contents('php://input');
    $decoded = is_string($raw) ? json_decode($raw, true) : null;
    $data = is_array($decoded) ? $decoded : [];
}

if (!charged_public_form_csrf_verify($data['_csrf'] ?? null)) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'Security check failed. Refresh the page and try again.']);

    exit;
}

// Honeypot (bots fill hidden "website" field)
if (trim((string) ($data['website'] ?? '')) !== '') {
    echo json_encode(['ok' => true]);

    exit;
}

if (!charged_public_form_rate_allow()) {
    http_response_code(429);
    echo json_encode(['ok' => false, 'error' => 'Too many requests. Please try again later.']);

    exit;
}

$formType = strtolower(trim((string) ($data['form_type'] ?? '')));
if ($formType !== 'subscribe' && $formType !== 'partner') {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Invalid form.']);

    exit;
}

$email = trim((string) ($data['email'] ?? ''));
$name = isset($data['name']) ? (string) $data['name'] : null;
$message = isset($data['message']) ? (string) $data['message'] : null;
$sourcePage = isset($data['source_page']) ? (string) $data['source_page'] : ($_SERVER['HTTP_REFERER'] ?? '');

if ($formType === 'subscribe') {
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'Please enter a valid email address.']);

        exit;
    }
    $name = null;
    $message = null;
} else {
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'Please enter a valid work email.']);

        exit;
    }
    $msgTrim = trim((string) $message);
    if (charged_utf8_strlen($msgTrim) < 10) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'Please add a bit more detail (at least 10 characters).']);

        exit;
    }
}

try {
    $ok = charged_site_form_submission_save($pdo, $formType, $email, $name, $message, $sourcePage);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Could not save your submission. Please try again later.']);

    exit;
}

if (!$ok) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Invalid submission.']);

    exit;
}

echo json_encode(['ok' => true, 'message' => $formType === 'subscribe'
    ? 'You are on the list. Watch your inbox for the next issue.'
    : 'Thanks — we received your inquiry and will reply soon.']);
