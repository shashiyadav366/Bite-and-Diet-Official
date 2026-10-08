<?php
// ------------------------------------------------------------------
// sitemap_lib.php - JSON-driven sitemap builder shared by the admin
// UI, authenticated endpoints and CLI/cron. Intentionally contains no
// session or auth logic so it can be called from any context.
// ------------------------------------------------------------------

if (defined('SITEMAP_LIB_LOADED')) {
    return;
}
define('SITEMAP_LIB_LOADED', true);

define('SITEMAP_BASE_URL', 'https://www.biteanddiet.in');
define('SITEMAP_ROOT_DIR', dirname(__DIR__) . '/');
define('SITEMAP_XML_FILE', SITEMAP_ROOT_DIR . 'sitemap.xml');
define('SITEMAP_LOCK_FILE', SITEMAP_ROOT_DIR . 'data/sitemap.lock');

function sitemap_load_json($path)
{
    if (!is_file($path)) {
        return [];
    }
    $data = json_decode((string) file_get_contents($path), true);
    return is_array($data) ? $data : [];
}

function sitemap_xml_escape($value)
{
    return htmlspecialchars(
        html_entity_decode((string) $value, ENT_QUOTES | ENT_HTML5, 'UTF-8'),
        ENT_XML1 | ENT_QUOTES,
        'UTF-8'
    );
}

function sitemap_truncate($value, $limit)
{
    $value = html_entity_decode((string) $value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    if (function_exists('mb_substr')) {
        return mb_substr($value, 0, $limit, 'UTF-8');
    }
    return substr($value, 0, $limit);
}

function sitemap_source_files()
{
    return array(
        SITEMAP_ROOT_DIR . 'main-pages.json',
        SITEMAP_ROOT_DIR . 'diet_plans.json',
        SITEMAP_ROOT_DIR . 'allposts.json',
        SITEMAP_ROOT_DIR . 'youtube_videos.json',
        SITEMAP_ROOT_DIR . 'health-tips.json',
        SITEMAP_ROOT_DIR . 'social-posts.json',
        SITEMAP_ROOT_DIR . 'latest_offers.json',
        SITEMAP_ROOT_DIR . 'data/success_stories.json',
    );
}

function sitemap_newest_source_time()
{
    $newest = 0;
    foreach (sitemap_source_files() as $file) {
        if (is_file($file)) {
            $ts = filemtime($file);
            if ($ts && $ts > $newest) {
                $newest = $ts;
            }
        }
    }
    return $newest;
}

function sitemap_file_lastmod($path, $fallback)
{
    if (is_file($path)) {
        $ts = filemtime($path);
        if ($ts) {
            return date('c', $ts);
        }
    }
    return $fallback;
}

/**
 * lastmod policy: prefer the record's own date, fall back to the source
 * file's mtime, and only then to the newest mtime across all sources.
 * Never stamp the regeneration time - that inflates lastmod for URLs
 * whose content did not change.
 */
function sitemap_record_lastmod($value, $path, $fallback)
{
    if (!empty($value)) {
        $ts = strtotime((string) $value);
        if ($ts !== false && $ts > 0) {
            return date('c', $ts);
        }
    }
    return sitemap_file_lastmod($path, $fallback);
}

function sitemap_encode_path($path)
{
    $parts = explode('/', trim((string) $path, '/'));
    return implode('/', array_map('rawurlencode', $parts));
}

function sitemap_url($loc, $lastmod, $changefreq, $priority, $extra = '')
{
    $out  = '  <url>' . PHP_EOL;
    $out .= '    <loc>' . sitemap_xml_escape($loc) . '</loc>' . PHP_EOL;
    $out .= '    <lastmod>' . sitemap_xml_escape($lastmod) . '</lastmod>' . PHP_EOL;
    $out .= '    <changefreq>' . $changefreq . '</changefreq>' . PHP_EOL;
    $out .= '    <priority>' . $priority . '</priority>' . PHP_EOL;
    $out .= $extra;
    $out .= '  </url>' . PHP_EOL;
    return $out;
}

function sitemap_build()
{
    $newest   = sitemap_newest_source_time();
    $fallback = date('c', $newest ? $newest : time());
    $baseUrl  = SITEMAP_BASE_URL;
    $rootDir  = SITEMAP_ROOT_DIR;

    $seenAllLoc = $seenMain = $seenDiet = $seenBlog = $seenHealth = $seenSocial = $seenOffer = [];

    $sitemap  = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
    $sitemap .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" ' . PHP_EOL;
    $sitemap .= '        xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" ' . PHP_EOL;
    $sitemap .= '        xmlns:image="http://www.google.com/schemas/sitemap-image/1.1" ' . PHP_EOL;
    $sitemap .= '        xmlns:video="http://www.google.com/schemas/sitemap-video/1.1">' . PHP_EOL;

    $sitemap .= '  <url>' . PHP_EOL;
    $sitemap .= '    <loc>' . $baseUrl . '/</loc>' . PHP_EOL;
    $sitemap .= '    <lastmod>' . $fallback . '</lastmod>' . PHP_EOL;
    $sitemap .= '    <changefreq>daily</changefreq>' . PHP_EOL;
    $sitemap .= '    <priority>1.00</priority>' . PHP_EOL;
    $sitemap .= '  </url>' . PHP_EOL;
    $seenAllLoc[$baseUrl . '/'] = true;

    $mainPagesFile = $rootDir . 'main-pages.json';
    $mainPages     = sitemap_load_json($mainPagesFile);
    if (!empty($mainPages['main_pages']) && is_array($mainPages['main_pages'])) {
        $pageLastmod = sitemap_file_lastmod($mainPagesFile, $fallback);
        foreach ($mainPages['main_pages'] as $page) {
            $url = trim($page['url'] ?? '', '/');
            $loc = $baseUrl . '/' . $url;
            if ($url === '' || isset($seenMain[$url]) || isset($seenAllLoc[$loc])) {
                continue;
            }
            $seenMain[$url] = true;
            $seenAllLoc[$loc] = true;
            $sitemap .= sitemap_url($loc, $pageLastmod, 'weekly', '0.8');
        }
    }

    $sitemap .= sitemap_url($baseUrl . '/sitemap', $fallback, 'weekly', '0.5');
    $seenAllLoc[$baseUrl . '/sitemap'] = true;

    $dietPlansFile = $rootDir . 'diet_plans.json';
    $dietPlans     = sitemap_load_json($dietPlansFile);
    if (!empty($dietPlans['diet_plans']) && is_array($dietPlans['diet_plans'])) {
        $planLastmod = sitemap_file_lastmod($dietPlansFile, $fallback);
        foreach ($dietPlans['diet_plans'] as $diet) {
            $url = trim($diet['diet_url'] ?? '', '/');
            $loc = $baseUrl . '/plans-and-packages/' . $url;
            if ($url === '' || isset($seenDiet[$url]) || isset($seenAllLoc[$loc])) {
                continue;
            }
            $seenDiet[$url] = true;
            $seenAllLoc[$loc] = true;
            $sitemap .= sitemap_url($loc, $planLastmod, 'weekly', '0.8');
        }
    }

    $blogPostsFile = $rootDir . 'allposts.json';
    $blogPosts     = sitemap_load_json($blogPostsFile);
    foreach ($blogPosts as $post) {
        $slug = trim($post['slug'] ?? '');
        $loc  = $baseUrl . '/blog-post/' . $slug;
        if ($slug === '' || isset($seenBlog[$slug]) || isset($seenAllLoc[$loc])) {
            continue;
        }
        $seenBlog[$slug] = true;
        $seenAllLoc[$loc] = true;

        $ts = !empty($post['published']) ? strtotime($post['published']) : 0;
        if (!empty($post['updated'])) {
            $ts2 = strtotime($post['updated']);
            if ($ts2 !== false && $ts2 > $ts) {
                $ts = $ts2;
            }
        }
        $lastmod = ($ts !== false && $ts > 0)
            ? date('c', $ts)
            : sitemap_file_lastmod($blogPostsFile, $fallback);

        $extra = '';
        if (!empty($post['firstImgSrc'])) {
            $extra .= '    <image:image>' . PHP_EOL;
            $extra .= '      <image:loc>' . sitemap_xml_escape($post['firstImgSrc']) . '</image:loc>' . PHP_EOL;
            $extra .= '    </image:image>' . PHP_EOL;
        }

        $sitemap .= sitemap_url($loc, $lastmod, 'weekly', '0.7', $extra);
    }

    $videosFile = $rootDir . 'youtube_videos.json';
    $videos     = sitemap_load_json($videosFile);
    foreach ($videos as $video) {
        if (empty($video['videoId'])) {
            continue;
        }

        $url = $baseUrl . '/video/' . trim($video['slug'] ?? '');
        if (isset($seenAllLoc[$url])) {
            continue;
        }
        $seenAllLoc[$url] = true;

        $lastmod = sitemap_record_lastmod($video['publishedAt'] ?? '', $videosFile, $fallback);
        $thumb   = sitemap_xml_escape($video['thumbnail'] ?? '');
        $title   = sitemap_xml_escape(sitemap_truncate($video['title'] ?? '', 100));
        $desc    = sitemap_xml_escape(sitemap_truncate($video['description'] ?? '', 2048));
        $videoId = sitemap_xml_escape($video['videoId']);

        $extra  = '    <video:video>' . PHP_EOL;
        $extra .= '      <video:thumbnail_loc>' . $thumb . '</video:thumbnail_loc>' . PHP_EOL;
        $extra .= '      <video:title>' . $title . '</video:title>' . PHP_EOL;
        $extra .= '      <video:description>' . $desc . '</video:description>' . PHP_EOL;
        $extra .= '      <video:content_loc>https://www.youtube.com/watch?v=' . $videoId . '</video:content_loc>' . PHP_EOL;
        $extra .= '      <video:player_loc allow_embed="yes">https://www.youtube.com/embed/' . $videoId . '</video:player_loc>' . PHP_EOL;
        $extra .= '      <video:publication_date>' . $lastmod . '</video:publication_date>' . PHP_EOL;
        $extra .= '      <video:family_friendly>yes</video:family_friendly>' . PHP_EOL;
        $extra .= '    </video:video>' . PHP_EOL;

        $sitemap .= sitemap_url($url, $lastmod, 'weekly', '0.7', $extra);
    }

    $healthFile = $rootDir . 'health-tips.json';
    $healthPosts = sitemap_load_json($healthFile);
    foreach ($healthPosts as $health) {
        $slug = trim($health['slug'] ?? '');
        $loc  = $baseUrl . '/health-tips/' . urlencode($slug);
        if ($slug === '' || isset($seenHealth[$slug]) || isset($seenAllLoc[$loc])) {
            continue;
        }
        $seenHealth[$slug] = true;
        $seenAllLoc[$loc] = true;
        $lastmod = sitemap_record_lastmod($health['timestamp'] ?? '', $healthFile, $fallback);
        $sitemap .= sitemap_url($loc, $lastmod, 'weekly', '0.7');
    }

    $socialFile  = $rootDir . 'social-posts.json';
    $socialPosts = sitemap_load_json($socialFile);
    foreach ($socialPosts as $social) {
        $slug = trim($social['slug'] ?? '');
        $loc  = $baseUrl . '/social-post/' . $slug;
        if ($slug === '' || isset($seenSocial[$slug]) || isset($seenAllLoc[$loc])) {
            continue;
        }
        if (stripos($slug, 'offer') !== false) {
            continue;
        }
        $seenSocial[$slug] = true;
        $seenAllLoc[$loc] = true;
        $lastmod = sitemap_record_lastmod($social['timestamp'] ?? '', $socialFile, $fallback);
        $sitemap .= sitemap_url($loc, $lastmod, 'weekly', '0.7');
    }

    $offersFile = $rootDir . 'latest_offers.json';
    $offersData = sitemap_load_json($offersFile);
    if (isset($offersData['offers']) && is_array($offersData['offers'])) {
        $offerLastmod = sitemap_file_lastmod($offersFile, $fallback);
        foreach ($offersData['offers'] as $offer) {
            $slug = trim($offer['slug'] ?? '');
            $loc  = $baseUrl . '/latest-offers/' . $slug;
            if ($slug === '' || isset($seenOffer[$slug]) || isset($seenAllLoc[$loc])) {
                continue;
            }
            $seenOffer[$slug] = true;
            $seenAllLoc[$loc] = true;
            $sitemap .= sitemap_url($loc, $offerLastmod, 'weekly', '0.8');
        }
    }

    $storiesFile = $rootDir . 'data/success_stories.json';
    $stories     = sitemap_load_json($storiesFile);
    foreach ($stories as $story) {
        $pdf = trim($story['pdf'] ?? '');
        if ($pdf === '') {
            continue;
        }
        $loc = $baseUrl . '/' . sitemap_encode_path($pdf);
        if (isset($seenAllLoc[$loc])) {
            continue;
        }
        $seenAllLoc[$loc] = true;
        $lastmod = sitemap_record_lastmod($story['created_at'] ?? '', $storiesFile, $fallback);
        $sitemap .= sitemap_url($loc, $lastmod, 'weekly', '0.5');
    }

    $sitemap .= '</urlset>' . PHP_EOL;

    return array('xml' => $sitemap, 'urls' => count($seenAllLoc));
}

function sitemap_state_file()
{
    return SITEMAP_ROOT_DIR . 'data/sitemap_state.json';
}

/**
 * Fingerprint of every source file: name, size and mtime.
 *
 * mtime alone is not enough. filemtime() has one-second resolution, so two
 * writes inside the same second (add a post, then delete it again) leave the
 * source looking unchanged to a plain mtime comparison and the sitemap would
 * silently stay stale. Mixing in filesize catches those.
 */
function sitemap_source_signature()
{
    $parts = [];
    foreach (sitemap_source_files() as $file) {
        $name = basename($file);
        if (!is_file($file)) {
            $parts[] = $name . ':-';
            continue;
        }
        clearstatcache(true, $file);
        $parts[] = $name . ':' . filesize($file) . ':' . filemtime($file);
    }
    sort($parts);
    return sha1(implode('|', $parts));
}

function sitemap_is_stale_by_mtime()
{
    if (!is_file(SITEMAP_XML_FILE)) {
        return true;
    }
    $xmlTime = filemtime(SITEMAP_XML_FILE);
    if (!$xmlTime) {
        return true;
    }
    foreach (sitemap_source_files() as $file) {
        clearstatcache(true, $file);
        if (is_file($file) && filemtime($file) > $xmlTime) {
            return true;
        }
    }
    return false;
}

function sitemap_is_stale()
{
    if (!is_file(SITEMAP_XML_FILE)) {
        return true;
    }

    $stateFile = sitemap_state_file();
    if (!is_file($stateFile)) {
        // No fingerprint recorded yet: fall back to mtime comparison so the
        // first run after upgrading still behaves sensibly.
        return sitemap_is_stale_by_mtime();
    }

    $state = json_decode((string) @file_get_contents($stateFile), true);
    $stored = is_array($state) ? (string) ($state['sig'] ?? '') : '';

    if ($stored === '') {
        return sitemap_is_stale_by_mtime();
    }

    return $stored !== sitemap_source_signature();
}

/**
 * Record the fingerprint the sitemap was actually built from, so the next
 * call can tell "already up to date" from "changed and missed".
 */
function sitemap_record_state()
{
    $file = sitemap_state_file();
    $dir  = dirname($file);
    if (!is_dir($dir)) {
        @mkdir($dir, 0775, true);
    }
    $payload = json_encode([
        'sig'  => sitemap_source_signature(),
        'urls' => sitemap_current_url_count(),
        'at'   => time(),
    ]);
    if ($payload === false) {
        return false;
    }
    $tmp = $file . '.tmp' . getmypid();
    if (@file_put_contents($tmp, $payload, LOCK_EX) === false) {
        return false;
    }
    if (!@rename($tmp, $file)) {
        @unlink($tmp);
        return false;
    }
    return true;
}

function sitemap_current_url_count()
{
    if (!is_file(SITEMAP_XML_FILE)) {
        return 0;
    }
    return substr_count((string) file_get_contents(SITEMAP_XML_FILE), '<loc>');
}

/**
 * Rebuild sitemap.xml when any source is newer than the current file.
 * Writes to a temp file then renames, so crawlers never observe a
 * partially written sitemap. Safe to call from every write path.
 *
 * @return array{success:bool,skipped:bool,message:string,urls:int}
 */
function sitemap_generate($force = false)
{
    $lockDir = dirname(SITEMAP_LOCK_FILE);
    if (!is_dir($lockDir)) {
        @mkdir($lockDir, 0775, true);
    }

    $lock = @fopen(SITEMAP_LOCK_FILE, 'c');
    if (!$lock) {
        return array(
            'success' => false,
            'skipped' => false,
            'message'  => 'Failed to acquire sitemap lock. Check data/ permissions.',
            'urls'     => 0,
        );
    }

    if (!flock($lock, LOCK_EX | LOCK_NB)) {
        fclose($lock);
        return array(
            'success' => false,
            'skipped' => true,
            'message'  => 'Another sitemap generation is already in progress.',
            'urls'     => sitemap_current_url_count(),
        );
    }

    try {
        if (!$force && !sitemap_is_stale()) {
            return array(
                'success' => true,
                'skipped' => true,
                'message'  => 'Sitemap already up to date.',
                'urls'     => sitemap_current_url_count(),
            );
        }

        $built = sitemap_build();

        $tmp = SITEMAP_XML_FILE . '.tmp' . getmypid();
        if (@file_put_contents($tmp, $built['xml'], LOCK_EX) === false) {
            @unlink($tmp);
            return array(
                'success' => false,
                'skipped' => false,
                'message'  => 'Failed to write temporary sitemap file.',
                'urls'     => 0,
            );
        }

        if (!@rename($tmp, SITEMAP_XML_FILE)) {
            @unlink(SITEMAP_XML_FILE);
            if (!@rename($tmp, SITEMAP_XML_FILE)) {
                @unlink($tmp);
                return array(
                    'success' => false,
                    'skipped' => false,
                    'message'  => 'Failed to replace sitemap.xml.',
                    'urls'     => 0,
                );
            }
        }

        sitemap_record_state();

        return array(
            'success' => true,
            'skipped' => false,
            'message'  => 'Sitemap generated successfully!',
            'urls'     => $built['urls'],
        );
    } finally {
        flock($lock, LOCK_UN);
        fclose($lock);
    }
}

function sitemap_generate_if_stale()
{
    return sitemap_generate(false);
}

function sitemap_force_generate()
{
    return sitemap_generate(true);
}