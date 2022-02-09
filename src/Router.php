<?php

$documentRoot = $_SERVER['DOCUMENT_ROOT'];
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$filePath = $documentRoot . '/' . ltrim($requestUri, '/');

if (file_exists($filePath) && is_file($filePath)) {
    return false;
}

$router = get_cfg_var('codecept.router');

return include($documentRoot . '/' . $router);
