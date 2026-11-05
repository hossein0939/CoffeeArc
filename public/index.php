<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';

$router = require dirname(__DIR__) . '/app/routes.php';
$router->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', $_SERVER['REQUEST_URI'] ?? '/');
