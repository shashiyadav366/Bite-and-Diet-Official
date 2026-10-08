<?php
/**
 * Shared YouTube uploads sync.
 *
 * Used by the cron entry point (admin/sitemap-cron.php) and by the
 * authenticated endpoint admin/updatevideo_api.php. Contains no session or
 * auth logic so it can run from CLI.
 *
 * Merge semantics (deliberately non-destructive):
 *   - Videos already in the file keep their existing slug, so /video/<slug>
 *     URLs never change and existing SEO is preserved.
 *   - New uploads are appended with a fresh slugify()'d slug.
 *   - Entries whose videoId the API did not return are KEPT, never deleted.
 */

if (defined('YOUTUBE_SYNC_LOADED')) {
    return;
}
define('YOUTUBE_SYNC_LOADED', true);

require_once __DIR__ . '/../app_config.php';
require_once __DIR__ . '/slug_lib.php';

function youtube_sync_cache_file()
{
    return __DIR__ . '/../youtube_videos.json';
}

function youtube_sync_http_get($url)
{
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 25,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_FOLLOWLOCATION => true,
        ]);
        $body = curl_exec($ch);
        $err  = curl_error($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($body === false) {
            return ['_error' => 'Network/TLS failure: ' . $err];
        }
        $json = json_decode((string) $body, true);
        if (!is_array($json)) {
            return ['_error' => 'Unreadable response (HTTP ' . $code . ').'];
        }
        if (isset($json['error'])) {
            return ['_error' => $json['error']['message'] ?? 'Unknown API error'];
        }
        return $json;
    }

    $ctx  = stream_context_create(['http' => ['timeout' => 25, 'ignore_errors' => true]]);
    $body = @file_get_contents($url, false, $ctx);
    if ($body === false) {
        return ['_error' => 'file_get_contents failed: ' . (error_get_last()['message'] ?? '?')];
    }
    $json = json_decode((string) $body, true);
    if (!is_array($json)) {
        return ['_error' => 'Unreadable response.'];
    }
    if (isset($json['error'])) {
        return ['_error' => $json['error']['message'] ?? 'Unknown API error'];
    }
    return $json;
}

/**
 * Fetch every upload and merge it into youtube_videos.json.
 *
 * @return array{success:bool,message:string,api_videos:int,added:int,updated:int,kept:int,count:int,pages:int}
 */
function youtube_sync_videos()
{
    $apiKey       = (string) cfg('google_api_key', '');
    $channelId    = (string) cfg('youtube_channel_id', '');
    $uploadListId = (string) cfg('youtube_upload_playlist_id', '');

    if ($apiKey === '' || $uploadListId === '') {
        return ['success' => false, 'message' => 'YouTube credentials missing from config.',
                'api_videos' => 0, 'added' => 0, 'updated' => 0, 'kept' => 0, 'count' => 0, 'pages' => 0];
    }

    if ($uploadListId === '' && $channelId !== '') {
        $uploadListId = 'UU' . substr($channelId, 2);
    }

    $cacheFile  = youtube_sync_cache_file();
    $backupFile = $cacheFile . '.bak.json';

    $previous = [];
    if (is_file($cacheFile)) {
        $decoded = json_decode((string) @file_get_contents($cacheFile), true);
        if (is_array($decoded)) {
            $previous = $decoded;
        }
    }

    $fetched = [];
    $token   = '';
    $pages   = 0;
    do {
        $url = 'https://www.googleapis.com/youtube/v3/playlistItems?key=' . urlencode($apiKey)
             . '&playlistId=' . urlencode($uploadListId)
             . '&part=snippet,contentDetails&maxResults=50&pageToken=' . urlencode($token);
        $page = youtube_sync_http_get($url);
        if (isset($page['_error'])) {
            return ['success' => false, 'message' => 'YouTube API: ' . $page['_error'],
                    'api_videos' => 0, 'added' => 0, 'updated' => 0, 'kept' => 0, 'count' => 0, 'pages' => $pages];
        }
        foreach ($page['items'] ?? [] as $item) {
            $vid = $item['contentDetails']['videoId'] ?? null;
            if (!$vid) {
                continue;
            }
            $snip = $item['snippet'] ?? [];
            $fetched[$vid] = [
                'title'       => (string) ($snip['title'] ?? ''),
                'videoId'     => $vid,
                'publishedAt' => (string) ($snip['publishedAt'] ?? ''),
                'thumbnail'   => (string) ($snip['thumbnails']['medium']['url']
                                 ?? $snip['thumbnails']['high']['url']
                                 ?? $snip['thumbnails']['default']['url']
                                 ?? ('https://i.ytimg.com/vi/' . $vid . '/mqdefault.jpg')),
                'description' => (string) ($snip['description'] ?? ''),
            ];
        }
        $token = $page['nextPageToken'] ?? '';
        $pages++;
    } while ($token && $pages < 40);

    if (!$fetched) {
        return ['success' => false, 'message' => 'YouTube returned no uploads. Nothing was written.',
                'api_videos' => 0, 'added' => 0, 'updated' => 0, 'kept' => 0, 'count' => 0, 'pages' => $pages];
    }

    $byId      = [];
    $usedSlugs = [];
    foreach ($previous as $row) {
        $vid = $row['videoId'] ?? null;
        if ($vid !== null && $vid !== '') {
            $byId[$vid] = $row;
        }
        if (!empty($row['slug'])) {
            $usedSlugs[$row['slug']] = true;
        }
    }

    $merged  = [];
    $added   = 0;
    $updated = 0;
    $kept    = 0;

    foreach ($fetched as $vid => $api) {
        if (isset($byId[$vid])) {
            $row = $byId[$vid];
            // Preserve the slug so the /video/<slug> URL stays stable.
            $row['title']       = $api['title'];
            $row['description'] = $api['description'];
            $row['thumbnail']   = $api['thumbnail'];
            $row['publishedAt'] = $api['publishedAt'];
            $row['videoId']     = $vid;
            if (empty($row['slug'])) {
                $row['slug'] = slugify($api['title']);
                $usedSlugs[$row['slug']] = true;
            }
            $merged[] = $row;
            $updated++;
            continue;
        }

        $base = slugify($api['title']);
        if ($base === '') {
            $base = 'video-' . $vid;
        }
        $slug = $base;
        $n    = 2;
        while (isset($usedSlugs[$slug])) {
            $slug = $base . '-' . $n;
            $n++;
        }
        $usedSlugs[$slug] = true;

        $merged[] = [
            'title'       => $api['title'],
            'slug'        => $slug,
            'videoId'     => $vid,
            'publishedAt' => $api['publishedAt'],
            'thumbnail'   => $api['thumbnail'],
            'description' => $api['description'],
        ];
        $added++;
    }

    // Anything already stored that the API did not list: keep, never drop.
    foreach ($previous as $row) {
        $vid = $row['videoId'] ?? null;
        if ($vid !== null && $vid !== '' && isset($fetched[$vid])) {
            continue;
        }
        $merged[] = $row;
        $kept++;
    }

    usort($merged, function ($a, $b) {
        return strcmp((string) ($b['publishedAt'] ?? ''), (string) ($a['publishedAt'] ?? ''));
    });

    if ($previous) {
        @file_put_contents($backupFile, json_encode($previous, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    $json = json_encode($merged, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    $tmp  = $cacheFile . '.tmp' . getmypid();
    if (@file_put_contents($tmp, $json) === false || !@rename($tmp, $cacheFile)) {
        @unlink($tmp);
        return ['success' => false, 'message' => 'Could not write youtube_videos.json',
                'api_videos' => count($fetched), 'added' => 0, 'updated' => 0, 'kept' => 0,
                'count' => count($previous), 'pages' => $pages];
    }

    // Intentionally does NOT touch the sitemap. The scheduled cron in
    // admin/sitemap-cron.php rebuilds the sitemap straight after this call,
    // and its fingerprint check picks up the changed youtube_videos.json
    // automatically. Keeping this function sitemap-free means a manual video
    // sync cannot change sitemap.xml as a side effect.

    return [
        'success'    => true,
        'message'    => sprintf(
            'Fetched %d uploads from YouTube. %d new, %d refreshed, %d kept from existing file.',
            count($fetched), $added, $updated, $kept
        ),
        'api_videos' => count($fetched),
        'added'      => $added,
        'updated'    => $updated,
        'kept'       => $kept,
        'count'      => count($merged),
        'pages'      => $pages,
    ];
}

/**
 * Run the sync only when youtube_videos.json is older than $maxAgeSeconds.
 * Keeps the frequent cron from burning API quota.
 *
 * @return array{success:bool,skipped:bool,message:string}
 */
function youtube_sync_if_stale($maxAgeSeconds = 21600)
{
    $cacheFile = youtube_sync_cache_file();
    clearstatcache(true, $cacheFile);

    if (is_file($cacheFile) && (time() - filemtime($cacheFile)) < $maxAgeSeconds) {
        $count = 0;
        $data  = json_decode((string) @file_get_contents($cacheFile), true);
        if (is_array($data)) {
            $count = count($data);
        }
        return [
            'success' => true,
            'skipped' => true,
            'message' => 'Video cache is current (' . $count . ' videos).',
        ];
    }

    $result = youtube_sync_videos();
    $result['skipped'] = false;
    return $result;
}