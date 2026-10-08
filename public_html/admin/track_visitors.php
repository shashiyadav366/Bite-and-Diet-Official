<?php
// Visitor tracking - optimized for instant page loads
date_default_timezone_set('Asia/Kolkata');

$file = __DIR__ . '/visitors.json';
$ignored_ips = ['150.129.237.196', '125.63.113.186', '2409:40d0:2029:8c0a:c96:75ff:fe0b:2cd1', '152.59.180.114'];

$ip_address = $_SERVER['REMOTE_ADDR'] ?? '';
$page_visited = $_SERVER['REQUEST_URI'] ?? '/';

if (strpos($page_visited, '/admin/') === 0) {
    if (file_exists($file)) {
        $visitor_data = json_decode(file_get_contents($file), true) ?: [];
        $changed = false;
        foreach ($visitor_data as $key => $visitor) {
            if (($visitor['ip'] ?? '') === $ip_address) {
                unset($visitor_data[$key]);
                $changed = true;
                break;
            }
        }
        if ($changed) {
            file_put_contents($file, json_encode(array_values($visitor_data), JSON_PRETTY_PRINT));
        }
    }
    return;
}

// Skip ignored IPs AND private/loopback addresses (bots, local dev) - instant
if (in_array($ip_address, $ignored_ips)) {
    return;
}
$isPrivate = $ip_address === '' || filter_var($ip_address, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false;
if ($isPrivate) {
    return;
}

// Unique visitor id from cookie, else session, else new
$unique_visitor_id = isset($_COOKIE['visitor_id']) ? $_COOKIE['visitor_id'] : (isset($_SESSION['visitor_id']) ? $_SESSION['visitor_id'] : '');
if (empty($unique_visitor_id)) {
    $unique_visitor_id = uniqid('visitor_', true);
    $_SESSION['visitor_id'] = $unique_visitor_id;
    if (!headers_sent()) {
        setcookie('visitor_id', $unique_visitor_id, time() + (365 * 24 * 60 * 60), "/");
    }
}

$timestamp = date('Y-m-d H:i:s');
$current_date = date('Y-m-d');

$visitor_data = [];
if (file_exists($file)) {
    $visitor_data = json_decode(file_get_contents($file), true);
    if (!is_array($visitor_data)) {
        $visitor_data = [];
    }
}

$duration = isset($_POST['duration']) ? round($_POST['duration'] / 1000, 2) : 0;

$visitor_key = null;
foreach ($visitor_data as $k => $visitor) {
    if (($visitor['ip'] ?? '') === $ip_address || ((isset($visitor['visitor_id']) && $visitor['visitor_id'] === $unique_visitor_id))) {
        $visitor_key = $k;
        break;
    }
}

$location_cached = ($visitor_key !== null && !empty($visitor_data[$visitor_key]['city']))
    ? ['city' => $visitor_data[$visitor_key]['city'], 'region' => $visitor_data[$visitor_key]['region'] ?? '', 'country' => $visitor_data[$visitor_key]['country'] ?? '']
    : null;

// Geo lookup ONLY for a brand-new visitor, with a short timeout
if ($visitor_key === null || $location_cached === null) {
    $ctx = stream_context_create(['http' => ['timeout' => 1, 'ignore_errors' => true]]);
    $location = @json_decode(@file_get_contents("http://ip-api.com/json/{$ip_address}", false, $ctx));
    $location_cached = [
        'city' => isset($location->city) && $location->city ? $location->city : 'Unknown',
        'region' => isset($location->regionName) && $location->regionName ? $location->regionName : 'Unknown',
        'country' => isset($location->country) && $location->country ? $location->country : 'Unknown'
    ];
}

$changed = false;

if ($visitor_key !== null) {
    $visitor =& $visitor_data[$visitor_key];
    if (empty($visitor['city']) || $visitor['city'] === 'Unknown') {
        $visitor['city'] = $location_cached['city'];
        $visitor['region'] = $location_cached['region'];
        $visitor['country'] = $location_cached['country'];
        $changed = true;
    }
    $today_found = false;
    if (isset($visitor['visits']) && is_array($visitor['visits'])) {
        foreach ($visitor['visits'] as &$visit) {
            if (date('Y-m-d', strtotime((string)($visit['time'] ?? ''))) === $current_date) {
                if (!in_array($page_visited, $visit['pages'])) {
                    $visit['pages'][] = $page_visited;
                    $changed = true;
                }
                if ($duration > 0) {
                    $visit['duration'] = round(($visit['duration'] ?? 0) + $duration, 2);
                    $changed = true;
                }
                $today_found = true;
                break;
            }
        }
        unset($visit);
    }
    if (!$today_found) {
        $visitor['visits'][] = ['pages' => [$page_visited], 'time' => $timestamp, 'duration' => $duration];
        $changed = true;
    }
    unset($visitor);
} else {
    $visitor_data[] = [
        'ip' => $ip_address,
        'visitor_id' => $unique_visitor_id,
        'city' => $location_cached['city'],
        'region' => $location_cached['region'],
        'country' => $location_cached['country'],
        'visits' => [['pages' => [$page_visited], 'time' => $timestamp, 'duration' => $duration]]
    ];
    $changed = true;
}

// Write only when something changed, under a lock
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
?>


<script>
    let startTime = Date.now();

    function sendDuration() {
        let duration = (Date.now() - startTime) / 1000;
        const xhr = new XMLHttpRequest();
        xhr.open('POST', '/admin/track_visitor.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.send('duration=' + duration);
    }

    window.addEventListener('beforeunload', sendDuration);
    window.addEventListener('pagehide', sendDuration);
</script>