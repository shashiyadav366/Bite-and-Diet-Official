<?php
/**
 * auto_beautify_endpoint.php — AJAX endpoint for "Beautify & Verify All Posts".
 *
 * Runs the automated beautification verification over every cached post,
 * fully in-process (no shell, no HTTP loop-back warm, no temp files), using
 * beautifier_verify.php as a library — so it can never desync from the
 * beautifier the live site actually uses.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Content-Type: application/json; charset=utf-8');
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Not authorised.']);
    exit;
}

@set_time_limit(0);
ini_set('max_execution_time', '0');

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/beautifier_verify.php';

$base = dirname(__DIR__);

list($results, $failures) = ab_verify($base);
$rep = ab_summarize($results, $failures);

@file_put_contents(__DIR__ . '/beautify_report.log', $rep['log'], FILE_APPEND);

echo json_encode([
    'success' => $rep['ok'],
    'message' => $rep['summary'],
    'posts'   => count($results),
    'failures'=> count($failures),
], JSON_UNESCAPED_UNICODE);
exit;