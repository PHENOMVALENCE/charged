<?php
/**
 * CLI helper: print a password_hash() for use in SQL or manual admin updates.
 * Usage: php scripts/charged_hash_password.php your-secret-password
 */

declare(strict_types=1);

$pass = $argv[1] ?? '';
if ($pass === '') {
    fwrite(STDERR, "Usage: php scripts/charged_hash_password.php <password>\n");
    exit(1);
}

echo password_hash($pass, PASSWORD_DEFAULT) . PHP_EOL;
