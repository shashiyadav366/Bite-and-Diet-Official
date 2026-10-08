<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Content-Type: application/json; charset=utf-8');
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Not authorised.']);
    exit;
}

require_once __DIR__ . '/blog_sync.php';

header('Content-Type: application/json; charset=utf-8');

$cacheFile = __DIR__ . '/../allposts.json';

$result = updatePostCache($cacheFile);

if ($result === '') {
    $data = json_decode(
        (string) @file_get_contents($cacheFile),
        true
    );

    $count = is_array($data) ? count($data) : 0;

    echo json_encode([
        'success' => true,
        'message' => 'Blog posts cache updated successfully.',
        'count'   => $count
    ]);

    exit;
}

http_response_code(500);

echo json_encode([
    'success' => false,
    'message' => $result
]);

exit;