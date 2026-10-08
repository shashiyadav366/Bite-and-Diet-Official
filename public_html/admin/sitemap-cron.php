<?php
// ------------------------------------------------------------------
// sitemap-cron.php - automatic sitemap regeneration.
//
//   CLI : php /path/to/public_html/admin/sitemap-cron.php
//   HTTP: /admin/sitemap-cron.php?token=<token>
//
// Only rebuilds when a source JSON file is newer than sitemap.xml, so
// this is cheap enough to schedule every few minutes. The HTTP path is
// token-gated because cron requests carry no PHP session.
// ------------------------------------------------------------------

require_once __DIR__ . '/sitemap_lib.php';

$isCli = (PHP_SAPI === 'cli');

require_once __DIR__ . '/youtube_sync.php';
require_once __DIR__ . '/sitemap_lib.php';

function sitemap_resolve_token()
{
    if (defined('SITEMAP_CRON_TOKEN') && SITEMAP_CRON_TOKEN !== '') {
        return array(SITEMAP_CRON_TOKEN, false);
    }

    // Preferred source: the central config outside the web root.
    $configPath = dirname(__DIR__) . '/app_config.php';
    if (is_file($configPath)) {
        require_once $configPath;
        $token = trim((string) cfg('sitemap_cron_token', ''));
        if ($token !== '') {
            return array($token, false);
        }
    }

    // Fallback: legacy per-token file, if one already exists.
    $file = dirname(dirname(__DIR__)) . '/sitemap_cron_token.php';

    if (is_file($file)) {
        $token = trim((string) @include $file);
        if ($token !== '') {
            return array($token, false);
        }
    }

    $token = bin2hex(random_bytes(24));
    $php   = "<?php\n// Auto-generated. Keep outside the web root.\nreturn '" . $token . "';\n";

    if (@file_put_contents($file, $php, LOCK_EX) !== false) {
        @chmod($file, 0600);
        return array($token, true);
    }

    return array('', false);
}

list($token, $isNewToken) = sitemap_resolve_token();

if (!$isCli) {
    $given = isset($_GET['token']) ? (string) $_GET['token'] : '';
    if ($token === '' || !hash_equals($token, $given)) {
        http_response_code(403);
        header('Content-Type: text/plain; charset=utf-8');
        echo 'Forbidden';
        exit;
    }
}

// 1. Pull any new YouTube uploads. This writes youtube_videos.json, and the
//    sync itself refreshes the sitemap in the same pass. It is age-gated so a
//    frequent cron does not burn API quota.
$videoMaxAge = 6 * 60 * 60; // six hours
$video = youtube_sync_if_stale($videoMaxAge);

// 2. Rebuild the sitemap if anything changed.
$result = sitemap_generate_if_stale();

$lines = [];
if (!$video['skipped']) {
    $lines[] = $video['success']
        ? 'YouTube: ' . $video['message']
        : 'YouTube sync FAILED: ' . $video['message'];
} else {
    $lines[] = 'YouTube: ' . $video['message'];
}
$lines[] = $result['message']
    . ($result['success'] ? ' (' . (int) $result['urls'] . ' URLs)' : '');

if (!$isCli) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(array_merge($result, ['video' => $video]));
    exit;
}

echo implode(PHP_EOL, $lines) . PHP_EOL;

if ($isNewToken) {
    echo 'Cron token created: ' . $token . PHP_EOL;
}