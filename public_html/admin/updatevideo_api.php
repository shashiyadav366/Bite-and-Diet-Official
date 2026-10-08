<?php
/**
 * Authenticated HTTP wrapper around the shared YouTube sync, kept for manual
 * or emergency runs. The regular path is the cron job calling
 * youtube_sync_if_stale() in admin/youtube_sync.php, so there is no longer a
 * button to click.
 *
 * Admin-only: requires an authenticated session.
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

require_once __DIR__ . '/youtube_sync.php';

$result = youtube_sync_videos();

header('Content-Type: application/json; charset=utf-8');
http_response_code(!empty($result['success']) ? 200 : 502);
echo json_encode($result);