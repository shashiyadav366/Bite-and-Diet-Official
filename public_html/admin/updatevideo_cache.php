<?php
// Video backup refresher.
//
// This is NOT an API cache. Videos are added manually from admin/youtubedata.php,
// which writes youtube_videos.json directly. There is no YouTube API fetch and no
// TTL here on purpose.
//
// This endpoint only keeps the backup in sync: it copies the live file over
// youtube_videos-backup.json. If the live file is ever missing or corrupt, it is
// restored from the backup instead.

header('Content-Type: application/json; charset=utf-8');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Not authorised.']);
    exit;
}

$cacheFile = __DIR__ . '/../youtube_videos.json';
$backupFile = __DIR__ . '/../youtube_videos-backup.json';

function respond($success, $message, $count = 0, $status = 200) {
    http_response_code($status);
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'count'   => $count
    ]);
    exit;
}

if (file_exists($cacheFile) && filesize($cacheFile) > 0) {
    $data = json_decode(file_get_contents($cacheFile), true);

    if ($data === null) {
        respond(false, 'Live video file contains invalid JSON. Backup left untouched.', 0, 500);
    }

    if (!copy($cacheFile, $backupFile)) {
        respond(false, 'Live video data is intact, but the backup could not be refreshed.', 0, 500);
    }

    respond(
        true,
        'Video backup refreshed successfully (' . count($data) . ' videos).',
        count($data)
    );
}

if (file_exists($backupFile)) {
    if (!copy($backupFile, $cacheFile)) {
        respond(false, 'Live video file was missing and could not be restored from the backup.', 0, 500);
    }

    respond(true, 'Live video file was missing and has been restored from the backup successfully.');
}

respond(false, 'No video file or backup found. Add videos from the "Youtube Data" page.', 0, 404);