<?php
/**
 * auto_beautify.php — FULLY AUTOMATIC blog beautification pipeline.
 *
 * Runs on the whole post list (no manual per-post checking):
 *
 *   warm   - fetch EVERY post in allposts.json into blog_post_cache.json by
 *            running each through the live render pipeline (blog-post.php
 *            ?blog_refresh=1), the exact same path a real visitor takes
 *            (API fetch -> cleanup -> cache write -> beautify). Missing or
 *            stale posts are filled in automatically so no post is ever left
 *            raw/untouched.
 *
 *   verify - loads the current blog_beautify_content() straight out of
 *            blog-post.php (brace-balanced slice, so it is ALWAYS in sync with
 *            what the site serves), runs it over EVERY cached post, and
 *            auto-checks: text preservation (digit/roman list-markers
 *            tolerated), heading structure, stray markdown/bold, wrapper tags
 *            inside headings, h3 inside table cells, and literal "N." numbered
 *            paragraphs. Any post that fails is reported; failures exit != 0.
 *
 *   all    - warm + verify (default).
 *
 * Usage:
 *   php admin/auto_beautify.php            # warm + verify everything
 *   php admin/auto_beautify.php warm
 *   php admin/auto_beautify.php verify
 *   php admin/auto_beautify.php warm --only=slug1,slug2
 *   php admin/auto_beautify.php all --limit=5   # warm at most 5 stale posts
 *
 * Future posts: run it after the admin "Update Blog Posts" sync (or click
 * "Beautify & Verify All" in admin), and every new post is beautified and
 * checked automatically. Render-time beautify in blog-post.php means even a
 * brand-new post is already styled the moment its URL is visited.
 */

require_once __DIR__ . '/../app_config.php';
require_once __DIR__ . '/../json_db.php';

$base = dirname(__DIR__);

/** All posts from allposts.json (indexed list). */
function ab_all_posts($base) {
    $file = $base . '/allposts.json';
    if (!is_file($file)) {
        return [];
    }
    $data = json_decode((string)@file_get_contents($file), true);
    return is_array($data) ? $data : [];
}

/** Local base URL used to warm the cache through the live pipeline. */
function ab_base_url() {
    $cfg = function_exists('cfg') ? @cfg('site_base_local', '') : '';
    return $cfg !== '' ? rtrim($cfg, '/') : 'http://localhost:8080';
}

/** One HTTP GET (curl) with generous timeout. Returns false or [code, body]. */
function ab_http($url, $timeout = 30) {
    if (!function_exists('curl_init')) {
        return false;
    }
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    $body = curl_exec($ch);
    $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err  = curl_error($ch);
    curl_close($ch);
    return $err !== '' ? false : [$code, (string)$body];
}

/**
 * Warm every missing/stale entry in blog_post_cache.json through the live
 * pipeline. Pass $only = [slug,...] to restrict to specific posts (handy for
 * targeted refreshes); $limit caps how many stale posts are warmed at once.
 * Returns [requestedSlugs, warmedCount, errors[]].
 */
function ab_warm($base, $maxAge = 12 * 3600, $only = [], $limit = 0) {
    $wantOnly = $only !== [];
    $cache = jd_read('blog_post_cache', []);
    $todo  = [];
    foreach (ab_all_posts($base) as $p) {
        $slug = $p['slug'] ?? '';
        if ($slug === '') {
            continue;
        }
        if ($wantOnly && !in_array($slug, $only, true)) {
            continue;
        }
        $e     = $cache[$slug] ?? null;
        $fresh = is_array($e)
            && isset($e['content']) && $e['content'] !== ''
            && isset($e['cached_at'])
            && (time() - (int)$e['cached_at']) < $maxAge;
        if (!$fresh) {
            $todo[$slug] = true;
        }
        if ($limit > 0 && count($todo) >= $limit) {
            break;
        }
    }

    $errors = [];
    $warmed = 0;
    $n = count($todo);
    $i = 0;
    foreach ($todo as $slug => $_) {
        $i++;
        $url  = ab_base_url() . '/blog-post/' . rawurlencode($slug) . '?blog_refresh=1';
        $resp = ab_http($url, 45);
        if ($resp === false) {
            $errors[] = "warm $slug: http-failure";
            echo "  [$i/$n] FAIL $slug (http-failure)\n";
            continue;
        }
        // The request should have written (or refreshed) the cache entry.
        $cache2 = jd_read('blog_post_cache', []);
        $e2     = $cache2[$slug] ?? [];
        $ok = is_array($e2)
            && isset($e2['content']) && $e2['content'] !== ''
            && (int)($e2['cached_at'] ?? 0) >= (int)($cache[$slug]['cached_at'] ?? 0);
        if ($ok) {
            $cache = $cache2;
            $warmed++;
            echo "  [$i/$n] warm OK $slug\n";
        } else {
            $errors[] = "warm $slug: cache not updated (status " . ($resp === false ? 'n/a' : $resp[0]) . ')';
            echo "  [$i/$n] FAIL $slug (cache not updated)\n";
        }
    }

    return [$todo, $warmed, $errors];
}

/** Brace-balanced slice of blog_beautify_content() from blog-post.php. */
function ab_extract_beautifier($base) {
    $src = (string)@file_get_contents($base . '/blog-post.php');
    $i   = strpos($src, 'function blog_beautify_content');
    if ($i === false) {
        return null;
    }
    $s = strpos($src, '{', $i);
    if ($s === false) {
        return null;
    }
    $depth = 0;
    $len   = strlen($src);
    $j     = $s;
    for (; $j < $len; $j++) {
        if ($src[$j] === '{') {
            $depth++;
        } elseif ($src[$j] === '}') {
            $depth--;
            if ($depth === 0) {
                break;
            }
        }
    }
    return substr($src, $i, $j - $i + 1);
}

/** Write data/beautify_runner.php (standalone harness with the current function). */
function ab_build_runner($base) {
    $fn = ab_extract_beautifier($base);
    if ($fn === null) {
        return 'Could not locate blog_beautify_content() in blog-post.php';
    }
    $runner = '<?php' . "\n"
        . "/* Auto-generated by admin/auto_beautify.php - do not edit. */\n"
        . '$html = (string) file_get_contents($argv[1]);' . "\n"
        . '$html = preg_replace("/^\xEF\xBB\xBF/", "", $html);' . "\n"
        . 'set_time_limit(60);' . "\n\n"
        . $fn . "\n\n"
        . '$out = blog_beautify_content($html);' . "\n"
        . '$romanRex = "/\b(i{1,3}|iv|v|vi{1,3}|ix|x[iv]{0,4})[.)]\s*/iu";' . "\n"
        . '$strip = function ($s, $noise = false) use ($romanRex) {' . "\n"
        . '    $s = preg_replace("/^\xEF\xBB\xBF/", "", $s);' . "\n"
        . '    $s = preg_replace("/<(style|script)\b[^>]*>.*?<\/\1>/is", " ", $s);' . "\n"
        . '    $s = preg_replace("/<[^>]+>/", " ", $s);' . "\n"
        . '    $s = html_entity_decode($s, ENT_QUOTES | ENT_HTML5, "UTF-8");' . "\n"
        . '    if ($noise) $s = preg_replace($romanRex, " ", $s);' . "\n"
        . '    $s = preg_replace("/[\p{P}\p{S}\s]+/u", "", $s);' . "\n"
        . '    if ($noise) $s = preg_replace("/[0-9]/u", "", $s);' . "\n"
        . '    return $s;' . "\n"
        . '};' . "\n\n"
        . '$dom = new DOMDocument();' . "\n"
        . 'libxml_use_internal_errors(true);' . "\n"
        . '$dom->loadHTML(\'<meta charset="utf-8"><div id="r">\' . $out . \'</div>\', LIBXML_NOWARNING | LIBXML_NOERROR);' . "\n"
        . '$r = $dom->getElementById("r");' . "\n"
        . '$errs = [];' . "\n"
        . 'if (mb_strpos($out, "**") !== false) $errs[] = "stray-**";' . "\n"
        . 'foreach (["td", "th"] as $t) foreach ($r->getElementsByTagName($t) as $td) foreach ($td->getElementsByTagName("h3") as $h) { $errs[] = "h3-in-td"; break 2; }' . "\n"
        . 'foreach (["h1","h2","h3","h4","h5","h6"] as $tag) foreach ($r->getElementsByTagName($tag) as $h) foreach (["b","strong","span","font","i","em"] as $w) if ($h->getElementsByTagName($w)->length) { $errs[] = "wrapper-in-heading"; break 2; }' . "\n"
        . '$stray = [];' . "\n"
        . 'foreach ($r->getElementsByTagName("p") as $p) {' . "\n"
        . '    $anc = $p->parentNode; $inLi = false; $inTd = false;' . "\n"
        . '    while ($anc) { $n = strtolower($anc->nodeName); if (in_array($n, ["ol","ul","li"], true)) $inLi = true; if (in_array($n, ["td","th"], true)) $inTd = true; $anc = $anc->parentNode; }' . "\n"
        . '    if ($inLi || $inTd) continue;' . "\n"
        . '    $txt = trim(preg_replace("/\s+/u", " ", $p->textContent));' . "\n"
        . '    if (preg_match("/^\s*(\d{1,2})[.)](?:\s|$)/u", $txt, $m)) $stray[] = (int)$m[1];' . "\n"
        . '}' . "\n"
        . '$ol = $r->getElementsByTagName("ol")->length;' . "\n"
        . '$ul = $r->getElementsByTagName("ul")->length;' . "\n"
        . '$nestedUl = 0;' . "\n"
        . 'foreach ($r->getElementsByTagName("ul") as $u) if (strtolower($u->parentNode->nodeName) === "li") $nestedUl++;' . "\n"
        . '$a = $strip($html); $b = $strip($out);' . "\n"
        . '$state = "OK";' . "\n"
        . 'if ($a !== $b) { $state = ($strip($html, true) === $strip($out, true)) ? "NOISE-ONLY" : "TEXT-LOSS"; }' . "\n"
        . 'echo ($errs ? "ERR " . implode(" ", $errs) : "OK"), " $state stray=", count($stray), " ol=$ol ul=$ul nestedUl=$nestedUl", (count($stray) ? " [" . implode(",", $stray) . "]" : ""), "\n";' . "\n";
    if (@file_put_contents($base . '/data/beautify_runner.php', $runner) === false) {
        return 'Could not write data/beautify_runner.php';
    }
    return '';
}

/**
 * Verify EVERY cached post through the beautifier.
 * Returns [results, failures[]]; result = [slug, outputLine].
 */
function ab_verify($base) {
    $err = ab_build_runner($base);
    $failures = [];
    if ($err !== '') {
        $failures[] = ['(runner)', 'ERR ' . $err];
        return [[], $failures];
    }
    $runner = $base . '/data/beautify_runner.php';
    $cache  = jd_read('blog_post_cache', []);
    $tmp    = $base . '/data/beautify_input.html';
    $results = [];
    foreach ($cache as $slug => $entry) {
        $content = is_array($entry) ? (string)($entry['content'] ?? '') : '';
        if ($content === '') {
            $failures[] = [$slug, 'ERR no-content'];
            $results[]  = [$slug, 'ERR no-content'];
            continue;
        }
        @file_put_contents($tmp, $content);
        $line = trim((string)shell_exec(
            escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($runner) . ' ' . escapeshellarg($tmp) . ' 2>&1'
        ));
        if ($line === '') {
            $line = 'ERR runner-output-empty';
        }
        $results[] = [$slug, $line];
        if (strpos($line, 'ERR ') === 0 || strpos($line, 'TEXT-LOSS') !== false) {
            $failures[] = [$slug, $line];
        }
    }
    @unlink($tmp);
    return [$results, $failures];
}

/** Build a human report + summary. Returns ['ok','summary','log']. */
function ab_summarize($warmNeeded, $warmOk, $warmErrors, $results, $failures) {
    $total  = count($results);
    $noise  = 0;
    $strays = 0;
    $ol = 0;
    $ul = 0;
    $nested = 0;
    foreach ($results as $r) {
        if (strpos($r[1], 'NOISE-ONLY') !== false) {
            $noise++;
        }
        if (preg_match('/stray=(\d+)/', $r[1], $m)) {
            $strays += (int)$m[1];
        }
        if (preg_match('/ ol=(\d+)/', $r[1], $m)) {
            $ol += (int)$m[1];
        }
        if (preg_match('/ ul=(\d+)/', $r[1], $m)) {
            $ul += (int)$m[1];
        }
        if (preg_match('/nestedUl=(\d+)/', $r[1], $m)) {
            $nested += (int)$m[1];
        }
    }

    $ok = count($failures) === 0 && count($warmErrors) === 0;

    $lines = [];
    $lines[] = '=== Blog beautification report ' . date('Y-m-d H:i:s') . ' ===';
    $lines[] = "Posts checked         : $total";
    $lines[] = "Posts warmed (needed) : $warmNeeded (warmed $warmOk)";
    $lines[] = 'Numbered lists (badges): ' . $ol;
    $lines[] = 'Bullet lists           : ' . $ul;
    $lines[] = 'Nested sub-lists       : ' . $nested;
    $lines[] = 'Digit/roman-only diffs : ' . $noise;
    $lines[] = 'Residual numbered paras: ' . $strays;
    $lines[] = "Failures               : " . count($failures) . "  warm-errors: " . count($warmErrors);
    foreach ($warmErrors as $e) {
        $lines[] = "  warm: $e";
    }
    if ($failures) {
        foreach ($failures as $f) {
            $lines[] = '  FAIL ' . $f[0] . ': ' . $f[1];
        }
    } else {
        $lines[] = 'All posts beautify cleanly.';
    }
    $log = implode("\n", $lines) . "\n";
    $summary = $ok
        ? "All $total posts beautified & verified automatically ($ol numbered, $ul bullet, $nested nested lists; 0 failures)."
        : "Beautify check FAILED: " . count($failures) . " post(s), " . count($warmErrors) . " warm error(s). See admin/beautify_report.log";
    return ['ok' => $ok, 'summary' => $summary, 'log' => $log];
}

// ---------------------------------------------------------------------------
// CLI entry (library mode for admin/auto_beautify_endpoint.php)
// ---------------------------------------------------------------------------
if (PHP_SAPI === 'cli') {
    set_time_limit(0);
    $mode  = strtolower($_SERVER['argv'][1] ?? 'all');
    $only  = [];
    $limit = 0;
    foreach (array_slice($_SERVER['argv'], 2) as $a) {
        if (strpos($a, '--only=') === 0) {
            $only = explode(',', substr($a, 7));
        } elseif (strpos($a, '--limit=') === 0) {
            $limit = max(0, (int)substr($a, 8));
        }
    }
    if ($mode === 'warm') {
        list($wanted, $warmedN, $warmErr) = ab_warm($base, 12 * 3600, $only, $limit);
        echo 'Warm done: ' . count($wanted) . " needed, $warmedN warmed, " . count($warmErr) . " errors.\n";
        foreach ($warmErr as $e) {
            echo "  - $e\n";
        }
        exit(count($warmErr) ? 1 : 0);
    }
    if ($mode === 'verify' || $mode === 'all') {
        $wanted  = [];
        $warmOk  = 0;
        $warmErr = [];
        if ($mode === 'all') {
            echo "Warming stale posts through the live pipeline...\n";
            list($wanted, $warmOk, $warmErr) = ab_warm($base, 12 * 3600, $only, $limit);
            echo "Warm done: " . count($wanted) . " needed, $warmOk warmed, " . count($warmErr) . " errors.\n";
        }
        list($results, $failures) = ab_verify($base);
        $rep = ab_summarize(count($wanted), $warmOk, $warmErr, $results, $failures);
        @file_put_contents(__DIR__ . '/beautify_report.log', $rep['log'], FILE_APPEND);
        echo $rep['log'];
        exit($rep['ok'] ? 0 : 1);
    }
    echo "Unknown mode '$mode'. Use: all | warm | verify\n";
    exit(2);
}