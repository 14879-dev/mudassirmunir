<?php
/**
 * Portfolio OS — API Router / Base
 */

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');

echo json_encode([
    'name' => 'Portfolio OS API',
    'version' => '1.0',
    'status' => 'online'
]);
