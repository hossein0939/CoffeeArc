<?php

declare(strict_types=1);

/**
 * Fallback entry when document root is the project root.
 * Prefer pointing the domain Document Root to /public on cPanel.
 */
if (is_file(__DIR__ . '/public/index.php')) {
    // Adjust request so router sees clean paths while assets stay under /public
    require __DIR__ . '/public/index.php';
    exit;
}

http_response_code(500);
echo 'public/index.php missing';
