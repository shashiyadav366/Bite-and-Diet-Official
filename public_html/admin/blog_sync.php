<?php
/**
 * Shared Blogger post-cache sync helpers.
 *
 * Responsibilities:
 *  - Clean/sanitize blog titles (smart quotes, mojibake, whitespace).
 *  - Transliterate Devanagari (Hindi) titles to Latin/ASCII without the
 *    "intl" extension (which is NOT loaded on this XAMPP install).
 *  - Build collision-free, English-friendly slugs.
 *  - Regenerate allposts.json from the Blogger API, keeping:
 *      * a backup of the previous file (allposts.json.bak.json)
 *      * an old-slug -> new-slug alias map (data/blog_slug_aliases.json)
 *        so existing URLs can be 301-redirected after slug changes.
 */

require_once __DIR__ . '/slug_lib.php';

if (!function_exists('devanagariToLatin')) {
    function devanagariToLatin($text) {
        // Bail out quickly when the string contains no Devanagari.
        if (!preg_match('/[\x{0900}-\x{097F}]/u', $text)) {
            return $text;
        }

        // Devanagari block (U+0900 - U+097F) => ASCII (ITRANS-style).
        static $map = [
            "\xE0\xA4\x81" => 'n',  // ँ  candrabindu
            "\xE0\xA4\x82" => 'm',  // ं  anusvara
            "\xE0\xA4\x83" => 'h',  // ः  visarga
            "\xE0\xA4\x85" => 'a',  // अ
            "\xE0\xA4\x86" => 'a',  // आ
            "\xE0\xA4\x87" => 'i',  // इ
            "\xE0\xA4\x88" => 'i',  // ई
            "\xE0\xA4\x89" => 'u',  // उ
            "\xE0\xA4\x8A" => 'u',  // ऊ
            "\xE0\xA4\x8B" => 'ri', // ऋ
            "\xE0\xA4\x8E" => 'e',  // ऎ
            "\xE0\xA4\x8F" => 'e',  // ए
            "\xE0\xA4\x90" => 'ai', // ऐ
            "\xE0\xA4\x91" => 'o',  // ऑ
            "\xE0\xA4\x92" => 'o',  // ऒ
            "\xE0\xA4\x93" => 'o',  // ओ
            "\xE0\xA4\x94" => 'au', // औ
            "\xE0\xA4\x95" => 'k',  // क
            "\xE0\xA4\x96" => 'kh', // ख
            "\xE0\xA4\x97" => 'g',  // ग
            "\xE0\xA4\x98" => 'gh', // घ
            "\xE0\xA4\x99" => 'n',  // ङ
            "\xE0\xA4\x9A" => 'c',  // च
            "\xE0\xA4\x9B" => 'ch', // छ
            "\xE0\xA4\x9C" => 'j',  // ज
            "\xE0\xA4\x9D" => 'jh', // झ
            "\xE0\xA4\x9E" => 'n',  // ञ
            "\xE0\xA4\x9F" => 't',  // ट
            "\xE0\xA4\xA0" => 'th', // ठ
            "\xE0\xA4\xA1" => 'd',  // ड
            "\xE0\xA4\xA2" => 'dh', // ढ
            "\xE0\xA4\xA3" => 'n',  // ण
            "\xE0\xA4\xA4" => 't',  // त
            "\xE0\xA4\xA5" => 'th', // थ
            "\xE0\xA4\xA6" => 'd',  // द
            "\xE0\xA4\xA7" => 'dh', // ध
            "\xE0\xA4\xA8" => 'n',  // न
            "\xE0\xA4\xA9" => 'n',  // ऩ
            "\xE0\xA4\xAA" => 'p',  // प
            "\xE0\xA4\xAB" => 'ph', // फ
            "\xE0\xA4\xAC" => 'b',  // ब
            "\xE0\xA4\xAD" => 'bh', // भ
            "\xE0\xA4\xAE" => 'm',  // म
            "\xE0\xA4\xAF" => 'y',  // य
            "\xE0\xA4\xB0" => 'r',  // र
            "\xE0\xA4\xB1" => 'r',  // ऱ
            "\xE0\xA4\xB2" => 'l',  // ल
            "\xE0\xA4\xB3" => 'l',  // ळ
            "\xE0\xA4\xB4" => 'l',  // ऴ
            "\xE0\xA4\xB5" => 'v',  // व
            "\xE0\xA4\xB6" => 's',  // श
            "\xE0\xA4\xB7" => 's',  // ष
            "\xE0\xA4\xB8" => 's',  // स
            "\xE0\xA4\xB9" => 'h',  // ह
            "\xE0\xA4\xBC" => '',   // nukta (handled below)
            "\xE0\xA4\xBE" => 'a',  // ा
            "\xE0\xA4\xBF" => 'i',  // ि
            "\xE0\xA5\x80" => 'i',  // ी
            "\xE0\xA5\x81" => 'u',  // ु
            "\xE0\xA5\x82" => 'u',  // ू
            "\xE0\xA5\x83" => 'ri', // ृ
            "\xE0\xA5\x84" => 'ri', // ॄ
            "\xE0\xA5\x85" => 'e',  // ॅ
            "\xE0\xA5\x87" => 'e',  // े
            "\xE0\xA5\x88" => 'ai', // ै
            "\xE0\xA5\x89" => 'o',  // ॉ
            "\xE0\xA5\x8B" => 'o',  // ो
            "\xE0\xA5\x8C" => 'au', // ौ
            "\xE0\xA5\x8D" => '',   // ्  virama (halant) - drop
            "\xE0\xA5\x98" => 'k',  // क़
            "\xE0\xA5\x99" => 'k',  // ख़
            "\xE0\xA5\x9A" => 'g',  // ग़
            "\xE0\xA5\x9B" => 'z',  // ज़
            "\xE0\xA5\x9C" => 'r',  // ड़
            "\xE0\xA5\x9D" => 'r',  // ढ़
            "\xE0\xA5\x9E" => 'f',  // फ़
            "\xE0\xA5\xA6" => '0',  // ०
            "\xE0\xA5\xA7" => '1',  // १
            "\xE0\xA5\xA8" => '2',  // २
            "\xE0\xA5\xA9" => '3',  // ३
            "\xE0\xA5\xAA" => '4',  // ४
            "\xE0\xA5\xAB" => '5',  // ५
            "\xE0\xA5\xAC" => '6',  // ६
            "\xE0\xA5\xAD" => '7',  // ७
            "\xE0\xA5\xAE" => '8',  // ८
            "\xE0\xA5\xAF" => '9',  // ९
        ];

        $chars = preg_split('//u', $text, -1, PREG_SPLIT_NO_EMPTY);
        if ($chars === false) {
            return $text;
        }

        $out = '';
        foreach ($chars as $ch) {
            $out .= isset($map[$ch]) ? $map[$ch] : $ch;
        }
        return $out;
    }
}

if (!function_exists('cleanTitle')) {
    function cleanTitle($text) {
        $text = (string)$text;
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // Typographic quotes / punctuation -> ASCII
        $replace = [
            "\xE2\x80\x98" => "'", // '
            "\xE2\x80\x99" => "'", // '
            "\xE2\x80\x9A" => "'", // ‚
            "\xE2\x80\x9B" => "'", // ‛
            "\xE2\x80\x9C" => '"', // "
            "\xE2\x80\x9D" => '"', // "
            "\xE2\x80\x9E" => '"', // „
            "\xE2\x80\x9F" => '"', // ‟
            "\xE2\x80\x93" => '-', // –
            "\xE2\x80\x94" => '-', // —
            "\xE2\x80\xA6" => '...', // …
            "\xC2\xA0"       => ' ',  // no-break space
        ];
        $text = strtr($text, $replace);

        // Remove invalid replacement chars and control chars
        $text = preg_replace('/[\x{FFFD}]/u', '', $text);
        $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $text);

        // Collapse whitespace
        $text = preg_replace('/\s+/u', ' ', $text);
        return trim($text);
    }
}

if (!function_exists('slugify')) {
    function slugify($text) {
        // 1. Clean HTML entities + smart punctuation
        $text = cleanTitle($text);

        // 2. Devanagari (Hindi) -> Latin/ASCII, then any remaining
        //    accented Latin characters through iconv.
        $text = devanagariToLatin($text);
        $text = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);

        // 3. Only letters + numbers remain in the slug
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);
        $text = trim($text, '-');
        $text = preg_replace('~-+~', '-', $text);

        // 4. Lowercase
        return strtolower($text);
    }
}

if (!function_exists('fetchBloggerPosts')) {
    /**
     * Fetch ALL blog posts from the Blogger API (paginated).
     * Returns array of [title, postUrl, published, content, firstImgSrc]
     * or null on network failure.
     */
    function fetchBloggerPosts($apiKey, $blogId) {
        $maxResults = 100;
        $items = [];
        $pageToken = '';

        // Verified TLS context first (uses php.ini cafile when available).
        $cafile = ini_get('openssl.cafile') ?: ini_get('curl.cainfo');
        $baseCtx = null;
        if ($cafile && is_file($cafile)) {
            $baseCtx = stream_context_create([
                'ssl' => [
                    'cafile' => $cafile,
                    'verify_peer' => true,
                    'verify_peer_name' => true,
                ],
            ]);
        }

        do {
            $apiUrl = "https://www.googleapis.com/blogger/v3/blogs/{$blogId}/posts?maxResults={$maxResults}&key={$apiKey}";
            if (!empty($pageToken)) {
                $apiUrl .= "&pageToken=" . urlencode($pageToken);
            }

            $response = $baseCtx
                ? @file_get_contents($apiUrl, false, $baseCtx)
                : @file_get_contents($apiUrl);

            if ($response === false) {
                // Local/admin maintenance tool: fall back to relaxed TLS so the
                // site keeps working even when the bundled CA bundle is stale.
                $relaxedCtx = stream_context_create([
                    'ssl' => ['verify_peer' => false, 'verify_peer_name' => false],
                ]);
                $response = @file_get_contents($apiUrl, false, $relaxedCtx);
            }

            if ($response === false) {
                return null;
            }

            $data = json_decode($response, true);
            if (!isset($data['items'])) {
                break;
            }

            foreach ($data['items'] as $post) {
                $content = $post['content'] ?? '';
                preg_match('/<img.*?src=["\'](.*?)["\']/i', $content, $matches);
                $items[] = [
                    'title'       => (string)($post['title'] ?? ''),
                    'postUrl'     => (string)($post['url'] ?? ''),
                    'published'   => (string)($post['published'] ?? ''),
                    'content'     => $content,
                    'firstImgSrc' => $matches[1] ?? '',
                ];
            }

            $pageToken = $data['nextPageToken'] ?? '';
        } while (!empty($pageToken));

        return $items;
    }
}

if (!function_exists('updatePostCache')) {
    /**
     * Regenerate allposts.json from the Blogger API.
     *
     * On success writes:
     *   <cacheFile>                 allposts.json (new data)
     *   <cacheFile>.bak.json        backup of the PREVIOUS list
     *   ../data/blog_slug_aliases.json   old-slug -> new-slug map (301s)
     *
     * Returns '' on success or an error string.
     */
    function updatePostCache($cacheFile, $apiKey = null, $blogId = null) {
        require_once __DIR__ . '/../app_config.php';
        $apiKey  = $apiKey  ?: cfg('google_api_key');
        $blogId  = $blogId  ?: cfg('blogger_blog_id');
        $aliasFile = dirname($cacheFile) . '/data/blog_slug_aliases.json';

        // 1. Remember the previous list (backup + alias resolution).
        $prevList = [];
        $prevByUrl = [];
        if (is_file($cacheFile)) {
            $prev = json_decode((string)@file_get_contents($cacheFile), true);
            if (is_array($prev)) {
                $prevList = $prev;
                foreach ($prev as $p) {
                    if (isset($p['postUrl'], $p['slug'])) {
                        $prevByUrl[$p['postUrl']] = $p['slug'];
                    }
                }
            }
        }

        // 2. Fetch fresh data from Blogger.
        $items = fetchBloggerPosts($apiKey, $blogId);
        if ($items === null) {
            return 'Error fetching blog posts from API';
        }

        // 3. Build the new list with clean, unique slugs.
        $allPosts = [];
        $usedSlugs = [];
        foreach ($items as $post) {
            // Translate Devanagari/Hindi titles to English (transliteration);
            // non-Hindi titles pass through unchanged.
            $title = devanagariToLatin(cleanTitle($post['title']));

            $prevSlug = $prevByUrl[$post['postUrl']] ?? null;
            if ($prevSlug !== null && $prevSlug !== ''
                && !preg_match('/^post(-\d+)?$/', $prevSlug)) {
                // Preserve existing friendly slug so URLs stay unchanged.
                $slug = $prevSlug;
            } else {
                $slug = slugify($title);
                if ($slug === '') {
                    $slug = 'post-' . (count($usedSlugs) + 1);
                }
            }

            $base = $slug;
            $i = 2;
            while (isset($usedSlugs[$slug])) {
                $slug = $base . '-' . $i;
                $i++;
            }
            $usedSlugs[$slug] = true;

            $allPosts[] = [
                'title'       => $title,
                'slug'        => $slug,
                'postUrl'     => $post['postUrl'],
                'firstImgSrc' => $post['firstImgSrc'],
                'published'   => $post['published'],
            ];
        }

        // 4. Persist backup of the previous list.
        if (!empty($prevList)) {
            $backupFile = $cacheFile . '.bak.json';
            @file_put_contents($backupFile, json_encode($prevList, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }

        // 5. Build old -> new slug alias map (matched by postUrl).
        $aliases = [];
        $newByUrl = [];
        foreach ($allPosts as $p) {
            $newByUrl[$p['postUrl']] = $p['slug'];
        }
        foreach ($prevList as $p) {
            if (isset($p['postUrl'], $p['slug']) && isset($newByUrl[$p['postUrl']])) {
                $old = $p['slug'];
                $new = $newByUrl[$p['postUrl']];
                if ($old !== $new) {
                    $aliases[$old] = $new;
                }
            }
        }
        @file_put_contents($aliasFile, json_encode($aliases, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        // 6. Save the new list.
        $cachedData = json_encode($allPosts, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        if (file_put_contents($cacheFile, $cachedData) === false) {
            return 'Failed to write cache file';
        }

        require_once __DIR__ . '/sitemap_lib.php';
        sitemap_generate_if_stale();

        return '';
    }
}