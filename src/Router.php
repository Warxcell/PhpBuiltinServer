<?php

declare(strict_types=1);

$stdout = fopen('php://stdout', 'w');
$now = new DateTimeImmutable();
$line = sprintf('%s: %s %s', $now->format('d.m.Y H:i:s.u'), $_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
fwrite($stdout, $_SERVER['REQUEST_URI'] . PHP_EOL);

$documentRoot = $_SERVER['DOCUMENT_ROOT'];
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$filePath = $documentRoot . '/' . ltrim($requestUri, '/');

if (file_exists($filePath)) {
    return false;
}

$router = get_cfg_var('codecept.router');

return include($documentRoot . '/' . $router);
