<?php
/**
 * beautifier_verify.php — AUTOMATIC all-posts beautification verification.
 *
 * Runs over the ENTIRE blog_post_cache.json (fetched from the Blogger API by
 * admin/updatepost_cache.php "Update Blog Posts") and verifies every single
 * post against the CURRENT beautifier used by the live site:
 *
 *   - the beautifier blog_beautify_content() is loaded straight out of
 *     blog-post.php (brace-balanced slice -> eval), so it is ALWAYS identical
 *     to what visitors see. No temp files, no sub-processes, nothing to get
 *     out of sync.
 *
 *   - every post is checked for: text preservation (digit/roman list-marker
 *     differences tolerated as NOISE), malformed heading structure, stray
 *     markdown/bold markers, wrapper tags inside headings, h3 inside table
 *     cells, and residual literal "N." numbered paragraphs (post authoring
 *     quirks left intact). Any failure is counted; failures exit != 0.
 *
 * The professional redesign itself (identical headings, spacing, bullet
 * numbering, font sizes and colours on EVERY post) needs no per-post work: it
 * lives in ONE shared .blog-content stylesheet plus the dynamic list engine
 * inside blog_beautify_content(), applied at render time in blog-post.php to
 * every post — old, current and future. This script simply proves it works
 * everywhere, automatically, after every sync.
 *
 * Usage:
 *   php admin/beautifier_verify.php          # verify every cached post
 *   php admin/beautifier_verify.php verify
 *
 * Also used as a library by admin/auto_beautify_endpoint.php ("Beautify &
 * Verify All" button in the admin panel), which runs right after the Blogger
 * sync so newly fetched posts are beautified + verified automatically.
 */

require_once __DIR__ . '/../app_config.php';
require_once __DIR__ . '/../json_db.php';

$base = dirname(__DIR__);

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

/**
 * Normalise away punctuation/whitespace (and, in "noise" mode, the list
 * markers the beautifier is allowed to change: roman numerals and digits).
 */
function ab_strip($s, $noise = false) {
    $s = preg_replace("/^\xEF\xBB\xBF/", '', $s);
    $s = preg_replace('/<(style|script)\b[^>]*>.*?<\/\1>/is', ' ', $s);
    $s = preg_replace('/<[^>]+>/', ' ', $s);
    $s = html_entity_decode($s, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    if ($noise) {
        $s = preg_replace('/\b(i{1,3}|iv|v|vi{1,3}|ix|x[iv]{0,4})[.)]\s*/iu', ' ', $s);
    }
    $s = preg_replace('/[\p{P}\p{S}\s]+/u', '', $s);
    if ($noise) {
        $s = preg_replace('/[0-9]/u', '', $s);
    }
    return $s;
}

/**
 * Verify ALL cached posts through the current beautifier, in-process.
 * Returns [results, failures[]]; result = [slug, outputLine].
 */
function ab_verify($base) {
    $fn = ab_extract_beautifier($base);
    $failures = [];
    if ($fn === null) {
        $failures[] = ['(runner)', 'ERR could-not-extract-beautifier'];
        return [[], $failures];
    }
    if (!function_exists('blog_beautify_content')) {
        eval($fn);
    }
    if (!function_exists('blog_beautify_content')) {
        $failures[] = ['(runner)', 'ERR beautifier-eval-failed'];
        return [[], $failures];
    }

    $cache   = jd_read('blog_post_cache', []);
    $results = [];
    foreach ($cache as $slug => $entry) {
        $content = is_array($entry) ? (string)($entry['content'] ?? '') : '';
        if ($content === '') {
            $failures[] = [$slug, 'ERR no-content'];
            $results[]  = [$slug, 'ERR no-content'];
            continue;
        }

        // Run the CURRENT beautifier directly (defined via eval above).
        try {
            $out = (string)blog_beautify_content($content);
        } catch (\Throwable $e) {
            $line = 'ERR beautifier-exception: ' . $e->getMessage();
            $results[]  = [$slug, $line];
            $failures[] = [$slug, $line];
            continue;
        }

        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML('<meta charset="utf-8"><div id="r">' . $out . '</div>', LIBXML_NOWARNING | LIBXML_NOERROR);
        $r = $dom->getElementById('r');
        if ($r === null) {
            $line = 'ERR no-parsed-root';
            $results[]  = [$slug, $line];
            $failures[] = [$slug, $line];
            continue;
        }

        $errs = [];
        if (mb_strpos($out, '**') !== false) {
            $errs[] = 'stray-**';
        }
        foreach (['td', 'th'] as $t) {
            foreach ($r->getElementsByTagName($t) as $td) {
                foreach ($td->getElementsByTagName('h3') as $h) {
                    $errs[] = 'h3-in-td';
                    break 2;
                }
            }
        }
        foreach (['h1', 'h2', 'h3', 'h4', 'h5', 'h6'] as $tag) {
            foreach ($r->getElementsByTagName($tag) as $h) {
                foreach (['b', 'strong', 'span', 'font', 'i', 'em'] as $w) {
                    if ($h->getElementsByTagName($w)->length) {
                        $errs[] = 'wrapper-in-heading';
                        break 2;
                    }
                }
            }
        }
        $stray = [];
        foreach ($r->getElementsByTagName('p') as $p) {
            $anc  = $p->parentNode;
            $inLi = false;
            $inTd = false;
            while ($anc) {
                $n = strtolower($anc->nodeName);
                if (in_array($n, ['ol', 'ul', 'li'], true)) {
                    $inLi = true;
                }
                if (in_array($n, ['td', 'th'], true)) {
                    $inTd = true;
                }
                $anc = $anc->parentNode;
            }
            if ($inLi || $inTd) {
                continue;
            }
            $txt = trim(preg_replace('/\s+/u', ' ', $p->textContent));
            if (preg_match('/^\s*(\d{1,2})[.)](?:\s|$)/u', $txt, $m)) {
                $stray[] = (int)$m[1];
            }
        }
        $ol = $r->getElementsByTagName('ol')->length;
        $ul = $r->getElementsByTagName('ul')->length;
        $nestedUl = 0;
        foreach ($r->getElementsByTagName('ul') as $u) {
            if (strtolower($u->parentNode->nodeName) === 'li') {
                $nestedUl++;
            }
        }

        $a = ab_strip($content);
        $b = ab_strip($out);
        $state = 'OK';
        if ($a !== $b) {
            $state = ab_strip($content, true) === ab_strip($out, true) ? 'NOISE-ONLY' : 'TEXT-LOSS';
        }

        $line = ($errs ? 'ERR ' . implode(' ', $errs) : 'OK') . " $state stray=" . count($stray)
            . " ol=$ol ul=$ul nestedUl=$nestedUl"
            . (count($stray) ? ' [' . implode(',', $stray) . ']' : '');
        $results[] = [$slug, $line];
        if (strpos($line, 'ERR ') === 0 || strpos($line, 'TEXT-LOSS') !== false) {
            $failures[] = [$slug, $line];
        }
    }
    return [$results, $failures];
}

/** Build a human report + summary. Returns ['ok','summary','log']. */
function ab_summarize($results, $failures) {
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

    $ok = count($failures) === 0;

    $lines = [];
    $lines[] = '=== Blog beautification report ' . date('Y-m-d H:i:s') . ' ===';
    $lines[] = "Posts checked         : $total";
    $lines[] = 'Numbered lists (badges): ' . $ol;
    $lines[] = 'Bullet lists           : ' . $ul;
    $lines[] = 'Nested sub-lists       : ' . $nested;
    $lines[] = 'Digit/roman-only diffs : ' . $noise;
    $lines[] = 'Residual numbered paras: ' . $strays;
    $lines[] = "Failures               : " . count($failures);
    if ($failures) {
        foreach ($failures as $f) {
            $lines[] = '  FAIL ' . $f[0] . ': ' . $f[1];
        }
    } else {
        $lines[] = 'All posts beautify cleanly.';
    }
    $log = implode("\n", $lines) . "\n";
    $summary = $ok
        ? "All $total posts beautified & verified automatically ($ol numbered, $ul bullet, $nested nested lists; 0 failures) — same professional design on every post."
        : "Beautify check FAILED: " . count($failures) . " post(s). See admin/beautify_report.log";
    return ['ok' => $ok, 'summary' => $summary, 'log' => $log];
}

// ---------------------------------------------------------------------------
// CLI entry (library mode for admin/auto_beautify_endpoint.php)
// ---------------------------------------------------------------------------
if (PHP_SAPI === 'cli') {
    set_time_limit(0);
    $mode = strtolower($_SERVER['argv'][1] ?? 'verify');
    if ($mode === 'verify') {
        list($results, $failures) = ab_verify($base);
        $rep = ab_summarize($results, $failures);
        @file_put_contents(__DIR__ . '/beautify_report.log', $rep['log'], FILE_APPEND);
        echo $rep['log'];
        exit($rep['ok'] ? 0 : 1);
    }
    echo "Unknown mode '$mode'. Use: verify\n";
    exit(2);
}