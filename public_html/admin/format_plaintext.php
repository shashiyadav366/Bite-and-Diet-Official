<?php
/**
 * format_plaintext.php — STEP 2: restructure every plain-text post into a
 * professional, consistently formatted plain-text blog post.
 *
 * Source: data/blog_posts_plain.json  (step 1: raw plain text from Blogger API)
 * Adds:   'formatted' field per post  (structure restored from the text itself)
 *
 * Rules (identical for every post):
 *   - preserve wording/meaning; never add/duplicate/reorder content
 *   - post 'title' becomes the main heading; other short lines become headings
 *   - explicit markers become lists: "1. 2. 3." stay numbered, "-"/"•" become "-"
 *   - paragraphs stay paragraphs with a blank line between
 *   - only very long paragraphs are split at natural sentence boundaries
 *   - strip markdown meaning-preserving (** etc.); no decorative characters
 *   - output is still PURE TEXT: no HTML/CSS/classes/attributes/markdown
 *
 * Usage: php admin/format_plaintext.php
 */

require_once __DIR__ . '/../app_config.php';
require_once __DIR__ . '/../json_db.php';

function pb_norm($s) {
    $s = mb_strtolower((string)$s);
    $s = preg_replace('/\d+/u', '', $s);
    return preg_replace('/[\p{P}\p{S}\s]+/u', '', $s);
}

function pb_clean_line($l) {
    $l = str_replace('**', '', $l);
    $l = str_replace('`', '', $l);
    $l = preg_replace('/^\s*(#+)\s+/u', '', $l);
    $l = str_replace(["\xC2\xA0", "\xE2\x80\x8B", "\xE2\x80\xAF"], ' ', $l);
    return trim(preg_replace('/[ \t]+/u', ' ', $l));
}

const PB_ITEM_RE = '/^\s*(\d{1,3})\s*[.)]\s*(.*)$/u';
const PB_BULLET_RE = '/^\s*([-•*>])\s*(.*)$/u';
const PB_END_PUNCT = '/[.!?,;:।]$/u';

/** Drop leftover blockquote ">" markers from the start of a line. */
function pb_destrip_gt($s) {
    $s = preg_replace('/^\s*>+\s*/u', '', $s);
    $s = preg_replace('/^[-•*]\s*>\s*/u', '', $s);
    return $s;
}

/** Classify a block (list of cleaned lines) as 'list' | 'heading' | 'para'. */
function pb_classify_block($lines) {
    $markers = 0;
    foreach ($lines as $l) {
        if (preg_match(PB_ITEM_RE, $l) || preg_match(PB_BULLET_RE, $l)) {
            $markers++;
        }
    }
    // Any block containing a numbered/bulleted line is a list, so numbered
    // items are never buried as inline paragraphs (keeps numbering consistent).
    if ($markers > 0) {
        return 'list';
    }
    if (count($lines) === 1 && $markers === 0) {
        $l = $lines[0];
        if (mb_strlen($l) <= 90 && !preg_match(PB_END_PUNCT, $l)) {
            return 'heading';
        }
    }
    return 'para';
}

/** Letters-only normalisation (lowercase; strips punctuation, symbols, digits). */
function pb_letter_runs($s) {
    $s = mb_strtolower((string)$s);
    return preg_replace('/[\p{P}\p{S}\d\s]+/u', '', $s);
}

/**
 * Any output line whose letters are NOT contained inside the input (or the
 * title, which is prepended) = invented content. Returns violating lines.
 */
function pb_added_lines($plain, $formatted, $title) {
    $inLetters = pb_letter_runs($plain);
    $tLetters  = pb_letter_runs($title);
    $found = [];
    $outNoMark = preg_replace('/^(\d{1,3}\. |\- )/um', '', $formatted);
    foreach (preg_split('/\n+/u', $outNoMark) as $line) {
        $tr = trim($line);
        if ($tr === '') {
            continue;
        }
        $lr = pb_letter_runs($tr);
        if ($lr !== '' && mb_strpos($inLetters, $lr) === false && mb_strpos($tLetters, $lr) === false) {
            $found[] = $tr;
        }
    }
    return $found;
}

/** Emit a list block (marker-normalised, meaning preserved). */
function pb_emit_list($lines, &$out) {
    $prevWasItem = false;
    $prevIdx = -1;
    foreach ($lines as $line) {
        if (preg_match(PB_ITEM_RE, $line, $m)) {
            $out[] = $m[1] . '. ' . $m[2];
            $prevWasItem = true;
            $prevIdx = count($out) - 1;
        } elseif (preg_match(PB_BULLET_RE, $line, $m)) {
            $out[] = '- ' . pb_destrip_gt($m[2]);
            $prevWasItem = true;
            $prevIdx = count($out) - 1;
        } else {
            // No marker: likely wrapped text of the previous item.
            if ($prevWasItem && $prevIdx >= 0) {
                $out[$prevIdx] .= ' ' . $line;
            } else {
                $out[] = $line;
            }
            $prevWasItem = false;
        }
    }
}

/** Join a paragraph and (only if very long) split at natural sentence breaks. */
function pb_emit_para($lines, &$out) {
    $text = trim(implode(' ', $lines));
    if ($text === '') {
        return;
    }
    if (mb_strlen($text) <= 360) {
        $out[] = $text;
        return;
    }
    $parts = preg_split('/(?<=[.!?])\s+(?=[\p{L}\p{N}])/u', $text);
    $chunks = [];
    $cur = '';
    foreach ($parts as $p) {
        if ($cur === '') {
            $cur = $p;
        } elseif (mb_strlen($cur) + mb_strlen($p) + 1 <= 240) {
            $cur .= ' ' . $p;
        } else {
            $chunks[] = $cur;
            $cur = $p;
        }
    }
    if ($cur !== '') {
        $chunks[] = $cur;
    }
    foreach ($chunks as $c) {
        $out[] = $c;
    }
}

function pb_emit_heading($line, &$out) {
    $out[] = $line;
}

/** Main transform: plain text -> structured, professional plain text. */
function pb_beautify($title, $plain) {
    $plain = str_replace(["\r\n", "\r"], "\n", (string)$plain);

    $blocks = preg_split('/\n\s*\n/u', $plain);

    $out = [];
    $t = trim((string)$title);
    if ($t !== '') {
        $out[] = pb_clean_line($t);
        $out[] = '';
    }

    $flushSingle = function () use (&$single, &$out) {
        if ($single) {
            pb_emit_list($single, $out);
            $single = [];
            $out[] = '';
        }
    };

    $single = [];
    foreach ($blocks as $b) {
        $lines = array_values(array_filter(array_map('pb_clean_line', explode("\n", $b)), function ($l) {
            return $l !== '';
        }));
        if (!$lines) {
            continue;
        }

        // Consecutive single marker lines (which Blogger separated with blank
        // lines) are one continuous list -> merge them.
        if (count($lines) === 1 && (preg_match(PB_ITEM_RE, $lines[0]) || preg_match(PB_BULLET_RE, $lines[0]))) {
            $single[] = $lines[0];
            continue;
        }
        $flushSingle();

        $type = pb_classify_block($lines);
        if ($type === 'list') {
            pb_emit_list($lines, $out);
        } elseif ($type === 'heading') {
            // Avoid duplicating the post title as a heading block.
            if (pb_norm($lines[0]) === pb_norm($t)) {
                continue;
            }
            pb_emit_heading($lines[0], $out);
        } else {
            pb_emit_para($lines, $out);
        }
        $out[] = '';
    }
    $flushSingle();

    while ($out && end($out) === '') {
        array_pop($out);
    }
    return implode("\n", $out) . "\n";
}

// ---------------------------------------------------------------------------
// CLI entry
// ---------------------------------------------------------------------------
if (PHP_SAPI === 'cli') {
    set_time_limit(0);
    $store = jd_read('blog_posts_plain', []);
    $lines = [];
    $lines[] = '=== Plain-text formatting report ' . date('Y-m-d H:i:s') . ' ===';

    $changed = 0;
    $empty = 0;
    $added = [];
    foreach ($store as $slug => &$entry) {
        if (is_array($entry) && isset($entry['plain'])) {
            $beautified = pb_beautify($entry['title'] ?? '', $entry['plain']);
            if (trim($beautified) === '') {
                $empty++;
            }
            $add = pb_added_lines($entry['plain'], $beautified, $entry['title'] ?? '');
            if ($add) {
                $added[$slug] = $add;
            }
            $entry['formatted'] = $beautified;
            $changed++;
        }
    }
    unset($entry);

    $wrote = jd_write('blog_posts_plain', $store, true);

    $lines[] = 'Posts formatted         : ' . $changed;
    $lines[] = 'Empty results            : ' . $empty;
    $lines[] = 'Posts with added content : ' . count($added) . '  (must be 0)';
    foreach ($added as $slug => $lines2) {
        $lines[] = "  ADDED ($slug):";
        foreach ($lines2 as $l) {
            $lines[] = "    + " . mb_strimwidth($l, 0, 90);
        }
    }
    $lines[] = 'Stored field             : formatted (data/blog_posts_plain.json)';
    $lines[] = 'Formatting rules         : identical for every post (title heading, short lines -> headings, 1./- markers -> lists, blank-line paragraphs, natural splits only)';
    $log = implode("\n", $lines) . "\n";
    @file_put_contents(__DIR__ . '/format_report.log', $log, FILE_APPEND);
    echo $log;
    exit(($wrote && $empty === 0 && $added === []) ? 0 : 1);
}