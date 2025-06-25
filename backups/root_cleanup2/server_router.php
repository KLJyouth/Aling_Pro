<?php
// ?????????
$uri = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);

// ??API??
if ($uri === "/api/v1/system/info") {
    require __DIR__ . "/public/api/v1/system/info.php";
    exit;
} elseif ($uri === "/api/info") {
    require __DIR__ . "/public/api/info.php";
    exit;
}

// ???????????????
$requested_file = __DIR__ . "/public" . $uri;
if (file_exists($requested_file) && !is_dir($requested_file)) {
    return false; // ????????????
}

// ??????index.php??
require __DIR__ . "/public/index.php";