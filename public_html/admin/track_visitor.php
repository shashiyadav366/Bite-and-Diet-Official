<?php
// Duration-tracking endpoint. Receives POST duration (milliseconds) from the
// pagehide/beforeunload beacon and adds it to today's visit record.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(204);
    exit;
}

date_default_timezone_set('Asia/Kolkata');
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$file = __DIR__ . '/visitors.json';
$ip_address = $_SERVER['REMOTE_ADDR'] ?? '';
$unique_visitor_id = isset($_COOKIE['visitor_id']) ? $_COOKIE['visitor_id'] : (isset($_SESSION['visitor_id']) ? $_SESSION['visitor_id'] : '');

$duration = isset($_POST['duration']) ? round($_POST['duration'] / 1000, 2) : 0;
if ($duration <= 0 || $duration > 86400) {
    http_response_code(204);
    exit;
}

if (!file_exists($file)) {
    http_response_code(204);
    exit;
}

$visitor_data = json_decode(file_get_contents($file), true);
if (!is_array($visitor_data)) {
    http_response_code(204);
    exit;
}

$current_date = date('Y-m-d');
$changed = false;

foreach ($visitor_data as &$visitor) {
    if (($visitor['ip'] ?? '') !== $ip_address && ((isset($visitor['visitor_id']) && $unique_visitor_id && $visitor['visitor_id'] === $unique_visitor_id)) === false) {
        continue;
    }
    if (isset($visitor['visits']) && is_array($visitor['visits'])) {
        foreach ($visitor['visits'] as &$visit) {
            if (date('Y-m-d', strtotime((string)($visit['time'] ?? ''))) === $current_date) {
                $visit['duration'] = round(($visit['duration'] ?? 0) + $duration, 2);
                $changed = true;
                break 2;
            }
        }
        unset($visit);
    }
}
unset($visitor);

if ($changed) {
    $fh = fopen($file, 'c');
    if ($fh) {
        flock($fh, LOCK_EX);
        ftruncate($fh, 0);
        rewind($fh);
        fwrite($fh, json_encode($visitor_data, JSON_PRETTY_PRINT));
        fflush($fh);
        flock($fh, LOCK_UN);
        fclose($fh);
    }
}

http_response_code(204);
exit;