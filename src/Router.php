<?php

declare(strict_types=1);

$now = new DateTimeImmutable();
$line = sprintf('%s: %s %s', $now->format('d.m.Y H:i:s.u'), $_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);

file_put_contents('php://stdout', $line . PHP_EOL, FILE_APPEND);

$documentRoot = $_SERVER['DOCUMENT_ROOT'];
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$filePath = $documentRoot . '/' . ltrim($requestUri, '/');

if (file_exists($filePath)) {
    return false;
}

$router = get_cfg_var('codecept.router');

return include($documentRoot . '/' . $router);
