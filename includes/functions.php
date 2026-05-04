<?php
/**
 * Shared helpers: escaping, CSRF, slugs, article body formatting, uploads.
 */

declare(strict_types=1);

/**
 * Escape string for HTML output.
 */
function charged_e(?string $s): string
{
    return htmlspecialchars((string) $s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Human-readable date for article listings (e.g. 18 Apr 2026). */
function charged_format_date(?string $sqlDatetime): string
{
    if ($sqlDatetime === null || $sqlDatetime === '') {
        return '';
    }
    $ts = strtotime($sqlDatetime);
    return $ts ? date('j M Y', $ts) : '';
}

/** ISO date for <time datetime="…">. */
function charged_format_date_iso(?string $sqlDatetime): string
{
    if ($sqlDatetime === null || $sqlDatetime === '') {
        return '';
    }
    $ts = strtotime($sqlDatetime);
    return $ts ? date('Y-m-d', $ts) : '';
}

/**
 * Absolute URL path to public submit-form.php (e.g. /charged/submit-form.php).
 * Derived from DOCUMENT_ROOT vs project root so AJAX hits the correct script from any PHP URL.
 */
function charged_submit_form_web_path(): string
{
    $docRoot = realpath((string) ($_SERVER['DOCUMENT_ROOT'] ?? ''));
    $chargedRoot = realpath(dirname(__DIR__));
    if ($docRoot === false || $chargedRoot === false) {
        return '/submit-form.php';
    }
    $doc = str_replace('\\', '/', $docRoot);
    $root = str_replace('\\', '/', $chargedRoot);
    $docLen = strlen($doc);
    if ($docLen === 0 || strlen($root) < $docLen || substr($root, 0, $docLen) !== $doc) {
        return '/submit-form.php';
    }
    $rel = substr($root, $docLen);
    $rel = trim(str_replace('\\', '/', $rel), '/');

    return ($rel === '' ? '' : '/' . $rel) . '/submit-form.php';
}

/**
 * URL-safe slug from title.
 */
function charged_slugify(string $title): string
{
    $title = strtolower(trim($title));
    $title = preg_replace('/[^a-z0-9]+/u', '-', $title) ?? '';
    $title = trim($title, '-');
    return $title !== '' ? $title : 'article';
}

/**
 * Ensure slug is unique in articles table.
 */
function charged_unique_slug(PDO $pdo, string $baseSlug, ?int $excludeId = null): string
{
    $slug = $baseSlug;
    $n = 2;
    while (true) {
        if ($excludeId === null) {
            $st = $pdo->prepare('SELECT id FROM articles WHERE slug = ? LIMIT 1');
            $st->execute([$slug]);
        } else {
            $st = $pdo->prepare('SELECT id FROM articles WHERE slug = ? AND id != ? LIMIT 1');
            $st->execute([$slug, $excludeId]);
        }
        if (!$st->fetch()) {
            return $slug;
        }
        $slug = $baseSlug . '-' . $n;
        $n++;
    }
}

/**
 * Start session if needed (public pages may call this before header).
 */
function charged_session_start(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        $secure = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
        if (PHP_VERSION_ID >= 70300) {
            session_set_cookie_params([
                'lifetime' => 0,
                'path' => '/',
                'secure' => $secure,
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
        } else {
            session_set_cookie_params(0, '/', '', $secure, true);
        }
        session_start();
    }
}

/**
 * CSRF token in session.
 */
function charged_csrf_token(): string
{
    charged_session_start();
    if (empty($_SESSION['_csrf_token'])) {
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf_token'];
}

function charged_csrf_field(): string
{
    $t = charged_e(charged_csrf_token());
    return '<input type="hidden" name="_csrf" value="' . $t . '">';
}

function charged_verify_csrf(): bool
{
    charged_session_start();
    $sent = $_POST['_csrf'] ?? '';
    $ok = is_string($sent)
        && isset($_SESSION['_csrf_token'])
        && hash_equals($_SESSION['_csrf_token'], $sent);
    return $ok;
}

/**
 * Format plain-text article body as safe HTML paragraphs (blank line = new paragraph).
 */
function charged_format_article_content(string $text): string
{
    $parts = preg_split('/\R\s*\R/', trim($text)) ?: [];
    $html = '';
    foreach ($parts as $part) {
        $part = trim($part);
        if ($part === '') {
            continue;
        }
        $escaped = charged_e($part);
        $html .= '<p>' . nl2br($escaped, false) . '</p>';
    }
    return $html !== '' ? $html : '<p>' . nl2br(charged_e($text), false) . '</p>';
}

/**
 * True when stored body is rich HTML (from the editor), not legacy plain text.
 */
function charged_is_rich_article_html(string $s): bool
{
    return (bool) preg_match('/<\s*(p|div|h[1-6]|ul|ol|li|strong|em|blockquote|table|thead|tbody|tr|td|th|br)\b/i', $s);
}

/**
 * True if article body has no meaningful text (handles empty TinyMCE output).
 */
function charged_article_body_is_empty(string $html): bool
{
    $plain = strip_tags($html);
    $plain = html_entity_decode($plain, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $plain = str_replace("\xc2\xa0", ' ', $plain);
    $plain = preg_replace('/\s+/u', '', $plain) ?? '';
    return $plain === '';
}

/**
 * Allowed elements and attributes for article HTML (after TinyMCE).
 *
 * @return array<string, list<string>>
 */
function charged_article_allowed_elements(): array
{
    return [
        'p' => [], 'div' => [], 'br' => [],
        'strong' => [], 'b' => [], 'em' => [], 'i' => [], 'u' => [], 's' => [], 'strike' => [],
        'h2' => [], 'h3' => [], 'h4' => [],
        'ul' => [], 'ol' => [], 'li' => [],
        'blockquote' => [], 'hr' => [], 'sub' => [], 'sup' => [],
        'table' => [], 'thead' => [], 'tbody' => [], 'tfoot' => [], 'caption' => [], 'tr' => [],
        'th' => ['colspan', 'rowspan'],
        'td' => ['colspan', 'rowspan'],
        'a' => ['href', 'rel', 'target'],
    ];
}

/**
 * Strip unsafe HTML from article body; keep semantic markup from the editor.
 */
function charged_sanitize_article_html(string $html): string
{
    $html = trim($html);
    if ($html === '') {
        return '';
    }

    $html = preg_replace('/<script\b[^>]*>.*?<\/script>/is', '', $html) ?? '';
    $html = preg_replace('/<style\b[^>]*>.*?<\/style>/is', '', $html) ?? '';
    $html = preg_replace('/<\?xml[^>]*>/i', '', $html) ?? '';

    $allowedTags = implode('', array_map(
        static fn (string $t): string => '<' . $t . '>',
        array_keys(charged_article_allowed_elements())
    ));
    $allowedTags .= '<br>'; // strip_tags needs <br> for void

    $stripped = strip_tags($html, $allowedTags);
    $stripped = trim($stripped);

    if (!class_exists(DOMDocument::class)) {
        return $stripped;
    }

    libxml_use_internal_errors(true);
    $dom = new DOMDocument('1.0', 'UTF-8');
    $wrapped = '<!DOCTYPE html><html><head><meta charset="UTF-8"></head><body><div id="charged-root">' . $stripped . '</div></body></html>';
    if (!@$dom->loadHTML($wrapped)) {
        libxml_clear_errors();
        return $stripped;
    }
    libxml_clear_errors();

    $root = $dom->getElementById('charged-root');
    if (!$root) {
        return $stripped;
    }

    $allowed = charged_article_allowed_elements();
    $xpath = new DOMXPath($dom);
    $nodes = $xpath->query('.//*', $root);
    if ($nodes !== false) {
        $list = [];
        for ($i = $nodes->length - 1; $i >= 0; $i--) {
            $list[] = $nodes->item($i);
        }
        foreach ($list as $el) {
            if (!$el instanceof DOMElement || !$el->parentNode) {
                continue;
            }
            $name = strtolower($el->nodeName);
            if (!isset($allowed[$name])) {
                charged_dom_unwrap_element($el);
                continue;
            }
            charged_dom_strip_attributes($el, $allowed[$name]);
            if ($name === 'a') {
                charged_sanitize_article_anchor_element($el);
            } elseif ($name === 'td' || $name === 'th') {
                charged_sanitize_table_span($el, 'colspan');
                charged_sanitize_table_span($el, 'rowspan');
            }
        }
    }

    $out = '';
    foreach ($root->childNodes as $child) {
        $out .= $dom->saveHTML($child);
    }

    return trim($out);
}

function charged_dom_unwrap_element(DOMElement $el): void
{
    $parent = $el->parentNode;
    if (!$parent) {
        return;
    }
    while ($el->firstChild) {
        $parent->insertBefore($el->firstChild, $el);
    }
    $parent->removeChild($el);
}

/**
 * @param list<string> $keepNames
 */
function charged_dom_strip_attributes(DOMElement $el, array $keepNames): void
{
    if (!$el->hasAttributes()) {
        return;
    }
    $toRemove = [];
    foreach ($el->attributes as $attr) {
        if (!in_array($attr->name, $keepNames, true)) {
            $toRemove[] = $attr->name;
        }
    }
    foreach ($toRemove as $n) {
        $el->removeAttribute($n);
    }
}

function charged_sanitize_table_span(DOMElement $el, string $attr): void
{
    if (!$el->hasAttribute($attr)) {
        return;
    }
    $v = (int) $el->getAttribute($attr);
    if ($v < 1 || $v > 24) {
        $el->removeAttribute($attr);
    } else {
        $el->setAttribute($attr, (string) $v);
    }
}

function charged_article_href_is_safe(string $href): bool
{
    $href = trim($href);
    if ($href === '') {
        return false;
    }
    if (strpbrk($href, "\r\n\t\x00") !== false) {
        return false;
    }
    $lower = strtolower($href);
    if (str_starts_with($lower, 'javascript:') || str_starts_with($lower, 'vbscript:')) {
        return false;
    }
    if (str_starts_with($lower, 'data:')) {
        return false;
    }
    if (preg_match('/\.\.[\/\\\\]/', $href)) {
        return false;
    }
    return true;
}

function charged_sanitize_article_anchor_element(DOMElement $a): void
{
    if (!$a->hasAttribute('href')) {
        charged_dom_unwrap_element($a);

        return;
    }
    $href = $a->getAttribute('href');
    if (!charged_article_href_is_safe($href)) {
        charged_dom_unwrap_element($a);

        return;
    }
    $target = $a->getAttribute('target');
    if ($target !== '' && strtolower($target) !== '_blank') {
        $a->removeAttribute('target');
    } elseif (strtolower($target) === '_blank') {
        $a->setAttribute('target', '_blank');
        $rel = $a->getAttribute('rel');
        if ($rel === '' || stripos($rel, 'noopener') === false) {
            $a->setAttribute('rel', trim($rel . ' noopener noreferrer'));
        }
    }
}

/**
 * Output article body: rich HTML (sanitized again) or legacy escaped plain text.
 */
function charged_render_article_body(string $raw): string
{
    $raw = trim($raw);
    if ($raw === '') {
        return '';
    }
    if (charged_is_rich_article_html($raw)) {
        $html = charged_sanitize_article_html($raw);
        if ($html !== '' && str_contains($html, '<table')) {
            return '<div class="ch-table-scroll">' . $html . '</div>';
        }

        return $html;
    }

    return charged_format_article_content($raw);
}

/* -----------------------------------------------------------------------------
   Public site forms (newsletter + partner) — CSRF session, DB storage
   ----------------------------------------------------------------------------- */

/** UTF-8 length; falls back to iconv or byte length if mbstring is unavailable. */
function charged_utf8_strlen(string $s): int
{
    if (function_exists('mb_strlen')) {
        return (int) mb_strlen($s, 'UTF-8');
    }
    if (function_exists('iconv_strlen')) {
        $n = @iconv_strlen($s, 'UTF-8');

        return $n !== false ? $n : strlen($s);
    }

    return strlen($s);
}

/** UTF-8 substring; falls back to iconv or byte substring if mbstring is unavailable. */
function charged_utf8_substr(string $s, int $start, ?int $length = null): string
{
    if (function_exists('mb_substr')) {
        return $length === null
            ? mb_substr($s, $start, null, 'UTF-8')
            : mb_substr($s, $start, $length, 'UTF-8');
    }
    if (function_exists('iconv_substr')) {
        return $length === null
            ? iconv_substr($s, $start, PHP_INT_MAX, 'UTF-8')
            : iconv_substr($s, $start, $length, 'UTF-8');
    }

    return $length === null ? substr($s, $start) : substr($s, $start, $length);
}

function charged_public_form_csrf_token(): string
{
    charged_session_start();
    if (empty($_SESSION['charged_public_form_csrf'])) {
        $_SESSION['charged_public_form_csrf'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['charged_public_form_csrf'];
}

function charged_public_form_csrf_verify(?string $token): bool
{
    charged_session_start();
    $t = is_string($token) ? $token : '';
    if ($t === '' || empty($_SESSION['charged_public_form_csrf'])) {
        return false;
    }

    return hash_equals($_SESSION['charged_public_form_csrf'], $t);
}

/** Simple per-session rate limit for public forms (abuse mitigation). */
function charged_public_form_rate_allow(): bool
{
    charged_session_start();
    $now = microtime(true);
    $hits = $_SESSION['charged_form_rate_hits'] ?? [];
    if (!is_array($hits)) {
        $hits = [];
    }
    $hits = array_filter($hits, static fn ($t): bool => ($now - (float) $t) < 3600.0);
    if (count($hits) >= 25) {
        return false;
    }
    $hits[] = $now;
    $_SESSION['charged_form_rate_hits'] = array_values($hits);

    return true;
}

function charged_sanitize_form_field(?string $s, int $maxLen): string
{
    $s = trim((string) $s);
    $s = strip_tags($s);
    $s = preg_replace('/\s+/u', ' ', $s) ?? '';

    return charged_utf8_substr($s, 0, $maxLen);
}

/**
 * Store a public form submission. Returns true on success.
 */
function charged_site_form_submission_save(
    PDO $pdo,
    string $formType,
    string $email,
    ?string $name,
    ?string $message,
    ?string $sourcePage
): bool {
    if (!in_array($formType, ['subscribe', 'partner'], true)) {
        return false;
    }
    $email = charged_utf8_substr(trim($email), 0, 190);
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return false;
    }

    $name = $name !== null && $name !== '' ? charged_sanitize_form_field($name, 200) : null;
    $message = $message !== null && $message !== '' ? charged_sanitize_form_field($message, 8000) : null;
    $sourcePage = $sourcePage !== null ? charged_sanitize_form_field($sourcePage, 500) : null;

    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    if (!is_string($ip) || strlen($ip) > 45) {
        $ip = '';
    }
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
    if (!is_string($ua)) {
        $ua = '';
    }
    $ua = charged_utf8_substr($ua, 0, 500);

    $st = $pdo->prepare(
        'INSERT INTO site_form_submissions (form_type, email, name, message, source_page, ip_address, user_agent)
         VALUES (?, ?, ?, ?, ?, ?, ?)'
    );

    return $st->execute([$formType, $email, $name, $message, $sourcePage, $ip !== '' ? $ip : null, $ua !== '' ? $ua : null]);
}

/** Max bytes per article image (bytes are stored as uploaded; no recompression). */
const CHARGED_ARTICLE_IMAGE_MAX_BYTES = 20971520;

/** Max gallery images per article (in addition to optional featured image). */
const CHARGED_ARTICLE_GALLERY_MAX_IMAGES = 24;

/**
 * Normalize a multi-file input into a list of single-file arrays.
 *
 * @return list<array{name: string, type: string, tmp_name: string, error: int, size: int}>
 */
function charged_upload_field_files(string $fieldName): array
{
    if (!isset($_FILES[$fieldName])) {
        return [];
    }
    $f = $_FILES[$fieldName];
    if (!is_array($f['name'] ?? null)) {
        return [$f];
    }
    $out = [];
    $n = count($f['name']);
    for ($i = 0; $i < $n; $i++) {
        $out[] = [
            'name' => (string) ($f['name'][$i] ?? ''),
            'type' => (string) ($f['type'][$i] ?? ''),
            'tmp_name' => (string) ($f['tmp_name'][$i] ?? ''),
            'error' => (int) ($f['error'][$i] ?? UPLOAD_ERR_NO_FILE),
            'size' => (int) ($f['size'][$i] ?? 0),
        ];
    }

    return $out;
}

/**
 * Count selected files (excluding empty slots) for a multi-file field.
 */
function charged_upload_field_nonempty_count(string $fieldName): int
{
    $n = 0;
    foreach (charged_upload_field_files($fieldName) as $file) {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            $n++;
        }
    }

    return $n;
}

/**
 * Decode gallery JSON from DB into a list of safe relative paths.
 *
 * @return list<string>
 */
function charged_parse_article_gallery(mixed $raw): array
{
    if ($raw === null || $raw === '') {
        return [];
    }
    if (is_array($raw)) {
        $data = $raw;
    } else {
        $decoded = json_decode((string) $raw, true);
        $data = is_array($decoded) ? $decoded : [];
    }
    $out = [];
    foreach ($data as $item) {
        if (!is_string($item) || $item === '' || str_contains($item, '..')) {
            continue;
        }
        if (str_starts_with($item, 'uploads/articles/')) {
            $out[] = $item;
        }
    }

    return array_values(array_unique($out));
}

/**
 * @param list<string> $paths
 */
function charged_article_gallery_to_json(array $paths): ?string
{
    $paths = array_values(array_unique(array_filter($paths, static fn ($p) => is_string($p) && $p !== '')));
    if ($paths === []) {
        return null;
    }

    return json_encode($paths, JSON_UNESCAPED_SLASHES);
}

function charged_unlink_managed_article_image(?string $relativePath): void
{
    if ($relativePath === null || $relativePath === '' || !str_starts_with($relativePath, 'uploads/articles/')
        || str_contains($relativePath, '..')) {
        return;
    }
    $full = CHARGED_ROOT . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
    if (is_file($full)) {
        @unlink($full);
    }
}

/**
 * Validate and store one uploaded image. Returns null with empty $errorMessage when no file was sent.
 */
function charged_process_article_image_upload(array $file, ?string &$errorMessage): ?string
{
    $errorMessage = '';
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        $errorMessage = 'Image upload failed. Please try again.';
        return null;
    }
    if (($file['size'] ?? 0) > CHARGED_ARTICLE_IMAGE_MAX_BYTES) {
        $errorMessage = 'Each image must be ' . (int) round(CHARGED_ARTICLE_IMAGE_MAX_BYTES / (1024 * 1024)) . 'MB or smaller.';
        return null;
    }

    $tmp = $file['tmp_name'] ?? '';
    if (!is_uploaded_file($tmp)) {
        $errorMessage = 'Invalid upload.';
        return null;
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($tmp) ?: '';
    $map = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
    ];
    if (!isset($map[$mime])) {
        $errorMessage = 'Allowed types: JPG, PNG, WEBP only.';
        return null;
    }

    $probe = @getimagesize($tmp);
    if ($probe === false) {
        $errorMessage = 'File is not a valid image.';
        return null;
    }

    $ext = $map[$mime];
    $dir = CHARGED_ROOT . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'articles';
    if (!is_dir($dir)) {
        if (!@mkdir($dir, 0755, true)) {
            $errorMessage = 'Could not create upload directory.';
            return null;
        }
    }

    $basename = 'article-' . bin2hex(random_bytes(8)) . '.' . $ext;
    $dest = $dir . DIRECTORY_SEPARATOR . $basename;
    if (!move_uploaded_file($tmp, $dest)) {
        $errorMessage = 'Could not save image.';
        return null;
    }

    return 'uploads/articles/' . $basename;
}

/**
 * Validate and move uploaded featured image. Returns relative web path or null on skip/failure.
 * Sets $errorMessage by reference on failure.
 */
function charged_handle_featured_upload(array $file, ?string $previousPath, ?string &$errorMessage): ?string
{
    $errorMessage = '';
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return $previousPath;
    }
    $path = charged_process_article_image_upload($file, $errorMessage);
    if ($path === null || $errorMessage !== '') {
        return null;
    }

    if ($previousPath && str_starts_with($previousPath, 'uploads/articles/')) {
        charged_unlink_managed_article_image($previousPath);
    }

    return $path;
}
