<?php
ob_start();
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Display errors during development (disable in production)
//ini_set('display_errors', 1);
//error_reporting(E_ALL);
// Initialize variables
$encodedTitle = '';
$encodedDescription = '';
$encodedUrl = '';
$encodedImageUrl = '';
$postUrl = ''; // To be filled from allposts.json
$needsBackgroundRefresh = false;
// Default image used when a blog post has no image of its own
$BLOG_FALLBACK_IMAGE = 'https://www.biteanddiet.in/images/social_posts_images/Dietician_Priyanka_5_Years_At_BiteAndDiet.jpg';

/**
 * Clean up Blogger post HTML so it renders as tidy, readable article markup.
 * Handles cached and freshly fetched content alike:
 *  - strips the full-document wrapper DOMDocument::saveHTML() leaves behind
 *  - removes comments, junk attributes and stray <style>/<script> blocks
 *  - keeps text-align (captions/quotes) while dropping everything else
 *  - turns bold pseudo-headings into real <h3> headings
 *  - splits "<br><br>" line breaks into real paragraphs
 *  - unwraps links around images (lightbox opens them instead)
 *  - wraps tables in a responsive, scrollable container
 */
function blog_beautify_content($html)
{
    if (!is_string($html) || trim($html) === '') {
        return '';
    }

    // 1) Strip the full-document wrapper saved by DOMDocument::saveHTML()
    if (preg_match('/<!DOCTYPE|<html[\s>]|<body[\s>]/i', $html)) {
        if (preg_match('/<body[^>]*>(.*)<\/body>/is', $html, $m)) {
            $html = $m[1];
        }
        $html = preg_replace('/<!DOCTYPE[^>]*>/is', '', $html);
        $html = preg_replace('/<head\b[^>]*>.*?<\/head>/is', '', $html);
        $html = preg_replace('/<\/?(?:html|body)[^>]*>/is', '', $html);
    }

    // 2) Drop HTML comments (Blogger leaves editor hints behind)
    $html = preg_replace('/<!--.*?-->/s', '', $html);

    // 2b) Markdown "**bold**" markers some editors leave behind -> real <b>
    //     tags, then drop any stray "**" so it never reaches the page.
    $html = preg_replace('/\*\*(.+?)\*\*/su', '<b>$1</b>', $html);
    $html = str_replace('**', '', $html);

    // 3) Line breaks become real paragraphs: "<br><br>" runs, and a single
    //    "<br>" that sits right before a bullet/numbered line.
    if (preg_match('/(?:<br\s*\/?>\s*){2,}/i', $html)) {
        $html = preg_replace('/(?:<br\s*\/?>\s*){2,}/i', '</p><p>', $html);
    }
    $html = preg_replace(
        '/<br\s*\/?>(?:\s|&nbsp;)*((?:&bull;|&#8226;|&#x2022;|&#8211;|&#8212;|\x{2022}|\x{00B7}|\x{25CF}|\x{25AA}|\x{25E6}|\x{2023}|\x{27A2}|\x{27A4}|\x{25B6}|\x{2756}|\*|\x{2D}|\x{2013}|\x{2014})\s*|\d{1,2}[.)]\s+)/u',
        '</p><p>$1',
        $html
    );

    $dom = new DOMDocument();
    libxml_use_internal_errors(true);
    $dom->loadHTML(
        '<meta charset="utf-8"><div id="bd-beauty-root">' . $html . '</div>',
        LIBXML_NOWARNING | LIBXML_NOERROR
    );
    libxml_clear_errors();

    $root = null;
    foreach ($dom->getElementsByTagName('div') as $div) {
        if ($div->getAttribute('id') === 'bd-beauty-root') {
            $root = $div;
            break;
        }
    }
    if (!$root) {
        return $html;
    }

    $toElement = function ($node, $tag) use ($dom) {
        $new = $dom->createElement($tag);
        while ($node->firstChild) {
            $new->appendChild($node->firstChild);
        }
        $node->parentNode->replaceChild($new, $node);
        return $new;
    };

    // 4) Remove elements that never belong in article content
    foreach (['style', 'script', 'link', 'meta', 'head', 'title', 'noscript'] as $tag) {
        while (true) {
            $nodes = $root->getElementsByTagName($tag);
            if ($nodes->length === 0) {
                break;
            }
            $node = $nodes->item(0);
            $node->parentNode->removeChild($node);
        }
    }

    // 5) Normalise attributes: small allow-list plus text-align only
    $allowed = [
        'a'      => ['href', 'title'],
        'img'    => ['src', 'alt', 'title'],
        'td'     => ['colspan', 'rowspan'],
        'th'     => ['colspan', 'rowspan'],
        'ol'     => ['start'],
        'iframe' => ['src', 'title', 'allow', 'allowfullscreen', 'frameborder'],
    ];
    foreach ($root->getElementsByTagName('*') as $el) {
        $tag = strtolower($el->nodeName);
        $attrs = [];
        while ($el->hasAttributes()) {
            $attr = $el->attributes->item(0);
            $attrs[strtolower($attr->name)] = $attr->value;
            $el->removeAttribute($attr->name);
        }

        $textAlign = '';
        if (isset($attrs['style']) && preg_match('/text-align\s*:\s*(center|right|justify)/i', $attrs['style'], $tm)) {
            $textAlign = strtolower($tm[1]);
        }
        if ($textAlign === '' && isset($attrs['align']) && in_array(strtolower($attrs['align']), ['center', 'right', 'justify'], true)) {
            $textAlign = strtolower($attrs['align']);
        }

        foreach (($allowed[$tag] ?? []) as $name) {
            if (isset($attrs[$name]) && $attrs[$name] !== '') {
                $el->setAttribute($name, $attrs[$name]);
            }
        }
        if ($textAlign !== '') {
            $el->setAttribute('style', 'text-align:' . $textAlign);
        }
    }

    // Unwrap redundant inline wrappers (their attributes were stripped above);
    // keep only wrappers that still carry a style such as text-align
    $wrappers = [];
    foreach (['span', 'font'] as $wname) {
        foreach ($root->getElementsByTagName($wname) as $w) {
            $wrappers[] = $w;
        }
    }
    foreach ($wrappers as $w) {
        if (!$w->parentNode || $w->attributes->length > 0) {
            continue;
        }
        while ($w->firstChild) {
            $w->parentNode->insertBefore($w->firstChild, $w);
        }
        $w->parentNode->removeChild($w);
    }

    // 6) Chat-export / Word HTML uses <div> paragraphs and stray <br> lines:
    //    real paragraphs, empty spacers removed, loose text kept readable.
    $blockTags = ['p','div','h1','h2','h3','h4','h5','h6','ul','ol','table','blockquote','pre','hr','figure','section','article','nav','header','footer','aside','address','form','dl'];

    $brs = [];
    foreach ($root->getElementsByTagName('br') as $br) {
        $brs[] = $br;
    }
    foreach ($brs as $br) {
        if (!$br->parentNode) {
            continue;
        }
        $prev = $br->previousSibling;
        $next = $br->nextSibling;
        $prevBlock = $prev && $prev->nodeType === XML_ELEMENT_NODE && in_array(strtolower($prev->nodeName), $blockTags, true);
        $nextBlock = $next && $next->nodeType === XML_ELEMENT_NODE && in_array(strtolower($next->nodeName), $blockTags, true);
        if ($prevBlock || $nextBlock) {
            $br->parentNode->removeChild($br);
        }
    }

    $processDiv = null;
    $processDiv = function ($container) use (&$processDiv, $dom, $blockTags) {
        $children = [];
        foreach ($container->childNodes as $n) {
            $children[] = $n;
        }
        foreach ($children as $n) {
            if ($n->nodeType === XML_ELEMENT_NODE) {
                $processDiv($n);
            }
        }

        $divs = [];
        foreach ($container->childNodes as $n) {
            if ($n->nodeType === XML_ELEMENT_NODE && strtolower($n->nodeName) === 'div') {
                $divs[] = $n;
            }
        }
        foreach ($divs as $d) {
            if (!$d->parentNode) {
                continue;
            }
            $hasBlock = false;
            $hasInline = false;
            foreach ($d->childNodes as $n) {
                if ($n->nodeType === XML_TEXT_NODE) {
                    if (trim($n->nodeValue) !== '') {
                        $hasInline = true;
                    }
                } elseif ($n->nodeType === XML_ELEMENT_NODE) {
                    $t = strtolower($n->nodeName);
                    if ($t === 'br') {
                        continue;
                    }
                    if (in_array($t, $blockTags, true)) {
                        $hasBlock = true;
                    } else {
                        $hasInline = true;
                    }
                }
            }

            if (!$hasBlock && !$hasInline) {
                // Empty or <br>-only spacer block
                $d->parentNode->removeChild($d);
                continue;
            }
            if (!$hasBlock && $hasInline) {
                // Plain paragraph container -> real <p>
                $p = $dom->createElement('p');
                while ($d->firstChild) {
                    $p->appendChild($d->firstChild);
                }
                $d->parentNode->replaceChild($p, $d);
                continue;
            }

            // Mixed content: loose inline runs become <p>, block children move up
            $parent = $d->parentNode;
            $run = [];
            $frag = [];
            $flushRun = function () use (&$run, $dom, &$frag) {
                if (count($run)) {
                    $p = $dom->createElement('p');
                    foreach ($run as $r) {
                        $p->appendChild($r);
                    }
                    $frag[] = $p;
                    $run = [];
                }
            };
            $kids = [];
            foreach ($d->childNodes as $k) {
                $kids[] = $k;
            }
            foreach ($kids as $k) {
                if ($k->nodeType === XML_TEXT_NODE) {
                    if (trim($k->nodeValue) === '') {
                        if (count($run)) {
                            $run[] = $k;
                        } else {
                            $d->removeChild($k);
                        }
                        continue;
                    }
                    $run[] = $k;
                    continue;
                }
                if ($k->nodeType !== XML_ELEMENT_NODE) {
                    continue;
                }
                $t = strtolower($k->nodeName);
                if ($t === 'br') {
                    $d->removeChild($k);
                    continue;
                }
                if (in_array($t, $blockTags, true)) {
                    $flushRun();
                    $frag[] = $k;
                } else {
                    $run[] = $k;
                }
            }
            $flushRun();
            foreach ($frag as $f) {
                $parent->insertBefore($f, $d);
            }
            $parent->removeChild($d);
        }
    };
    $processDiv($root);

    // 7) Plain "• line" / "1. line" paragraphs become real bullet/numbered lists
    $ulItem = '/^\s*(?:•|·|●|▪|◦|‣|➢|➤|►|❖|\*|[-–—])(?:\s+|$)/u';
    $olItem = '/^\s*(\d{1,2})[.)](?:\s+|$)/u';

    $classifyItem = function ($text) use ($ulItem, $olItem) {
        $text = trim(preg_replace('/\s+/u', ' ', (string)$text));
        if ($text === '') {
            return null;
        }
        if (preg_match($ulItem, $text)) {
            return ['ul', 0];
        }
        if (preg_match($olItem, $text, $m)) {
            return ['ol', (int)$m[1]];
        }
        return null;
    };

    // Marker + short bold label ("* **DO's:**") is a sub-heading, not a list item
    $labelCandidates = [];
    foreach ($root->getElementsByTagName('p') as $p) {
        $labelCandidates[] = $p;
    }
    foreach ($labelCandidates as $p) {
        if (!$p->parentNode) {
            continue;
        }
        $cls = $classifyItem((string)$p->textContent);
        if ($cls === null) {
            continue;
        }
        $marker = ($cls[0] === 'ol') ? $olItem : $ulItem;
        $norm = trim(preg_replace('/\s+/u', ' ', (string)$p->textContent));
        $remainder = trim(preg_replace($marker, '', $norm, 1));
        if ($remainder === '' || mb_strlen($remainder) > 40 || substr($remainder, -1) !== ':') {
            continue;
        }
        $bold = '';
        foreach ($p->getElementsByTagName('b') as $bn) {
            $bold .= $bn->textContent . ' ';
        }
        foreach ($p->getElementsByTagName('strong') as $bn) {
            $bold .= $bn->textContent . ' ';
        }
        if (trim(preg_replace('/\s+/u', ' ', $bold)) !== $remainder) {
            continue;
        }
        // Everything outside the bold element may only be marker/whitespace
        $stray = false;
        $strip = function ($n) use (&$strip, &$stray, $marker) {
            foreach ($n->childNodes as $cn) {
                if ($cn->nodeType === XML_TEXT_NODE) {
                    if (!in_array(strtolower($cn->parentNode->nodeName), ['b', 'strong'], true)) {
                        $left = preg_replace($marker, '', $cn->nodeValue, 1);
                        if (preg_replace('/\s+/u', '', str_replace("\xC2\xA0", '', $left)) !== '') {
                            $stray = true;
                        }
                    }
                } elseif ($cn->nodeType === XML_ELEMENT_NODE) {
                    if (!in_array(strtolower($cn->nodeName), ['b', 'strong'], true)) {
                        $stray = true;
                    } else {
                        $strip($cn);
                    }
                }
            }
        };
        $strip($p);
        if ($stray) {
            continue;
        }
        // Drop marker/whitespace text nodes, promote paragraph to <h4>
        $drop = [];
        foreach ($p->childNodes as $cn) {
            if ($cn->nodeType === XML_TEXT_NODE) {
                $cn->nodeValue = '';
                $drop[] = $cn;
            }
        }
        foreach ($drop as $dn) {
            if ($dn->parentNode) {
                $dn->parentNode->removeChild($dn);
            }
        }
        $h = $dom->createElement('h4');
        while ($p->firstChild) {
            $h->appendChild($p->firstChild);
        }
        $p->parentNode->replaceChild($h, $p);
    }

    // Paragraphs holding several line-broken items ("1. a\n2. b") split apart
    $splitCandidates = [];
    foreach ($root->getElementsByTagName('p') as $p) {
        $splitCandidates[] = $p;
    }
    foreach ($splitCandidates as $p) {
        if (!$p->parentNode || $p->getElementsByTagName('*')->length > 0) {
            continue;
        }
        $raw = (string)$p->textContent;
        if (strpos($raw, "\n") === false) {
            continue;
        }
        $lines = preg_split('/\r?\n/', $raw);
        if (count($lines) < 2) {
            continue;
        }
        $ok = true;
        $items = [];
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }
            if ($classifyItem($line) === null) {
                $ok = false;
                break;
            }
            $items[] = $line;
        }
        if (!$ok || count($items) < 2) {
            continue;
        }
        $parent = $p->parentNode;
        foreach ($items as $line) {
            $np = $dom->createElement('p');
            $np->appendChild($dom->createTextNode($line));
            $parent->insertBefore($np, $p);
        }
        $parent->removeChild($p);
    }

    // A numbered item wrapped inside the previous item after a <br>
    // ("... sit down to work.<br><b>5. </b><b>Eat a balanced diet -</b> ...")
    // is a real list item, not a continuation of the previous one. Split it
    // into its own paragraph so the list converter marks it "5.".
    $brSplitCandidates = [];
    foreach ($root->getElementsByTagName('p') as $p) {
        $brSplitCandidates[] = $p;
    }
    foreach ($brSplitCandidates as $p) {
        if (!$p->parentNode) {
            continue;
        }
        if ($classifyItem($p->textContent) === null) {
            continue;
        }
        $cut = true;
        while ($cut) {
            $cut = false;
            $kids = [];
            foreach ($p->childNodes as $cn) {
                $kids[] = $cn;
            }
            $br = null;
            foreach ($kids as $cn) {
                if ($cn->nodeType !== XML_ELEMENT_NODE || strtolower($cn->nodeName) !== 'br') {
                    continue;
                }
                if ($cn->parentNode !== $p) {
                    continue;
                }
                $nxt = $cn->nextSibling;
                while ($nxt && $nxt->nodeType === XML_TEXT_NODE && trim($nxt->nodeValue) === '') {
                    $nxt = $nxt->nextSibling;
                }
                if ($nxt === null) {
                    continue;
                }
                $prefix = '';
                $guard = $nxt;
                while ($guard !== null && $guard !== $p && mb_strlen($prefix) < 14) {
                    if ($guard->nodeType === XML_TEXT_NODE) {
                        $prefix .= $guard->nodeValue;
                    } elseif ($guard->nodeType === XML_ELEMENT_NODE) {
                        $gt = strtolower($guard->nodeName);
                        if (in_array($gt, $blockTags, true) || $gt === 'br') {
                            break;
                        }
                        $prefix .= $guard->textContent;
                    }
                    $guard = $guard->nextSibling;
                }
                if (preg_match('/^\s*(\d{1,2})[.)](?:\s+|$)/u', $prefix)) {
                    $br = $cn;
                    break;
                }
            }
            if ($br === null) {
                break;
            }
            $np = $dom->createElement('p');
            $p->parentNode->insertBefore($np, $p->nextSibling);
            $move = $br;
            while ($move !== null) {
                $m2 = $move->nextSibling;
                $np->appendChild($move);
                $move = $m2;
            }
            if ($np->firstChild && strtolower($np->firstChild->nodeName) === 'br') {
                $np->removeChild($np->firstChild);
            }
            $p = $np;
            $cut = true;
        }
    }

    $convertListsIn = null;
    $convertListsIn = function ($container) use (&$convertListsIn, $dom, $classifyItem, $ulItem, $olItem) {
        $child = $container->firstChild;
        while ($child !== null) {
            if ($child->nodeType === XML_ELEMENT_NODE) {
                $tag = strtolower($child->nodeName);
                if ($tag === 'p' && $classifyItem($child->textContent) !== null) {
                    $first = $classifyItem($child->textContent);
                    $olSubRegex = '/^\s*(i{1,3}|iv|v|vi{1,3}|ix|x[iv]{0,4})[.)]/iu';
                    $run = [];
                    $subMap = [];
                    $subs = [];
                    $cursor = $child;
                    $expect = $first[1];
                    while ($cursor !== null) {
                        // Blogger/Word source may carry real newlines between
                        // block tags, which DOMDocument keeps as whitespace text
                        // nodes. Skip them so a run of <p> items is not broken.
                        if ($cursor->nodeType === XML_TEXT_NODE || $cursor->nodeType === XML_CDATA_SECTION_NODE) {
                            if (trim($cursor->nodeValue) === '') {
                                $cursor = $cursor->nextSibling;
                                continue;
                            }
                            break;
                        }
                        if ($cursor->nodeType !== XML_ELEMENT_NODE || strtolower($cursor->nodeName) !== 'p') {
                            break;
                        }
                        if (trim($cursor->textContent, " \t\n\r\0\x0B\xC2\xA0") === '') {
                            $cursor = $cursor->nextSibling;
                            continue;
                        }
                        $c = $classifyItem($cursor->textContent);
                        if ($c === null) {
                            // i) / ii) subtitles belong to the previous numbered item
                            if ($first[0] === 'ol' && count($run) && preg_match($olSubRegex, $cursor->textContent)) {
                                $subs[] = $cursor;
                                $cursor = $cursor->nextSibling;
                                continue;
                            }
                            break;
                        }
                        if ($first[0] === 'ol' && $c[0] === 'ul') {
                            // Bullet lines under a numbered item ("- Calcium content: ...")
                            $subs[] = $cursor;
                            $cursor = $cursor->nextSibling;
                            continue;
                        }
                        if ($c[0] !== $first[0]) {
                            break;
                        }
                        if ($first[0] === 'ol' && $c[1] !== $expect) {
                            break;
                        }
                        $subMap[count($run)] = $subs;
                        $subs = [];
                        $run[] = $cursor;
                        if ($first[0] === 'ol') {
                            $expect++;
                        }
                        $cursor = $cursor->nextSibling;
                    }
                    $subMap[count($run)] = $subs;
                    if ($cursor === $child) {
                        $child = $child->nextSibling;
                        continue;
                    }
                    if (count($run) >= 2) {
                        $list = $dom->createElement($first[0]);
                        if ($first[0] === 'ol' && $first[1] > 1) {
                            $list->setAttribute('style', 'counter-reset: bd ' . ($first[1] - 1));
                        }
                        $parent = $run[0]->parentNode;
                        $parent->insertBefore($list, $run[0]);
                        $marker = ($first[0] === 'ol') ? $olItem : $ulItem;
                        foreach ($run as $i => $p) {
                            $li = $dom->createElement('li');
                            while ($p->firstChild) {
                                $li->appendChild($p->firstChild);
                            }
                            $list->appendChild($li);
                            // Bullets gathered between item N and item N+1 belong
                            // to item N (subMap is keyed by the item that FOLLOWS).
                            $attachIdx = $i + 1;
                            if (isset($subMap[$attachIdx]) && count($subMap[$attachIdx])) {
                                $inner = $dom->createElement('ul');
                                foreach ($subMap[$attachIdx] as $sp) {
                                    $sli = $dom->createElement('li');
                                    while ($sp->firstChild) {
                                        $sli->appendChild($sp->firstChild);
                                    }
                                    $inner->appendChild($sli);
                                    if ($sp->parentNode) {
                                        $sp->parentNode->removeChild($sp);
                                    }
                                }
                                $li->appendChild($inner);
                            }
                            if ($p->parentNode) {
                                $p->parentNode->removeChild($p);
                            }
                        }
                        $stripMarker = function ($li, $re) {
                            $texts = [];
                            $collect = function ($n) use (&$collect, &$texts) {
                                foreach ($n->childNodes as $c) {
                                    if ($c->nodeType === XML_TEXT_NODE) {
                                        $texts[] = $c;
                                    } elseif ($c->nodeType === XML_ELEMENT_NODE) {
                                        $collect($c);
                                    }
                                }
                            };
                            $collect($li);
                            $joined = '';
                            foreach ($texts as $t) {
                                $joined .= $t->nodeValue;
                            }
                            if (preg_match($re, $joined, $mm)) {
                                $cut = strlen($mm[0]);
                                foreach ($texts as $t) {
                                    if ($cut <= 0) {
                                        break;
                                    }
                                    $len = strlen($t->nodeValue);
                                    if ($len <= $cut) {
                                        $cut -= $len;
                                        $t->nodeValue = '';
                                    } else {
                                        $t->nodeValue = substr($t->nodeValue, $cut);
                                        $cut = 0;
                                    }
                                }
                            }
                            if (count($texts)) {
                                foreach ($texts as $t) {
                                    if (trim($t->nodeValue) !== '') {
                                        $t->nodeValue = ltrim($t->nodeValue, " \t\n\r\0\x0B\xC2\xA0");
                                        break;
                                    }
                                }
                            }
                        };
                        foreach ($list->childNodes as $li) {
                            $stripMarker($li, $marker);
                            foreach ($li->getElementsByTagName('li') as $sli) {
                                if ($sli->parentNode && strtolower($sli->parentNode->nodeName) === 'ul') {
                                    $stripMarker($sli, $ulItem);
                                    $stripMarker($sli, $olSubRegex);
                                }
                            }
                        }
                    }
                    $child = $cursor;
                    continue;
                }
                if (in_array($tag, ['div', 'section', 'article'], true)) {
                    $convertListsIn($child);
                }
            }
            $child = $child->nextSibling;
        }
    };
    $convertListsIn($root);

    // A lone numbered paragraph that resumes an earlier list ("6. Breathing
    // exercises" after an unrelated paragraph) should still render as item
    // "6.", not as literal text. Only when a <ol> exists earlier among the
    // sibling blocks and the number continues past 1 (a real continuation).
    $singleItems = [];
    foreach ($root->getElementsByTagName('p') as $p) {
        $singleItems[] = $p;
    }
    foreach ($singleItems as $p) {
        if (!$p->parentNode) {
            continue;
        }
        $cl = $classifyItem($p->textContent);
        if ($cl === null || $cl[0] !== 'ol' || $cl[1] <= 1) {
            continue;
        }
        $anc = $p->parentNode;
        $inTd = false;
        while ($anc) {
            if (in_array(strtolower($anc->nodeName), ['td', 'th'], true)) {
                $inTd = true;
                break;
            }
            $anc = $anc->parentNode;
        }
        if ($inTd) {
            continue;
        }
        $hasList = false;
        $node = $p->previousSibling;
        while ($node !== null) {
            if ($node->nodeType === XML_ELEMENT_NODE) {
                $t = strtolower($node->nodeName);
                if ($t === 'ol') {
                    $hasList = true;
                    break;
                }
                if (in_array($t, ['table', 'blockquote'], true)) {
                    break;
                }
            }
            $node = $node->previousSibling;
        }
        if (!$hasList) {
            continue;
        }
        $list = $dom->createElement('ol');
        $list->setAttribute('style', 'counter-reset: bd ' . ($cl[1] - 1));
        $li = $dom->createElement('li');
        while ($p->firstChild) {
            $li->appendChild($p->firstChild);
        }
        $list->appendChild($li);
        $p->parentNode->replaceChild($list, $p);
        $texts = [];
        $collect = function ($n) use (&$collect, &$texts) {
            foreach ($n->childNodes as $c) {
                if ($c->nodeType === XML_TEXT_NODE) {
                    $texts[] = $c;
                } elseif ($c->nodeType === XML_ELEMENT_NODE) {
                    $collect($c);
                }
            }
        };
        $collect($li);
        $joined = '';
        foreach ($texts as $t) {
            $joined .= $t->nodeValue;
        }
        if (preg_match($olItem, $joined, $mm)) {
            $cut = strlen($mm[0]);
            foreach ($texts as $t) {
                if ($cut <= 0) {
                    break;
                }
                $len = strlen($t->nodeValue);
                if ($len <= $cut) {
                    $cut -= $len;
                    $t->nodeValue = '';
                } else {
                    $t->nodeValue = substr($t->nodeValue, $cut);
                    $cut = 0;
                }
            }
        }
        if (count($texts)) {
            foreach ($texts as $t) {
                if (trim($t->nodeValue) !== '') {
                    $t->nodeValue = ltrim($t->nodeValue, " \t\n\r\0\x0B\xC2\xA0");
                    break;
                }
            }
        }
    }

    // 8) Images: lazy-load, alt text, lightbox-friendly cursor
    foreach ($root->getElementsByTagName('img') as $img) {
        $img->setAttribute('loading', 'lazy');
        if ($img->getAttribute('alt') === '') {
            $img->setAttribute('alt', 'Bite And Diet');
        }
        $img->setAttribute('class', 'post-img');
    }

    // 9) Unwrap links that only wrap images (the lightbox opens them instead)
    $anchors = [];
    foreach ($root->getElementsByTagName('a') as $a) {
        $anchors[] = $a;
    }
    foreach ($anchors as $a) {
        if (!$a->parentNode) {
            continue;
        }
        $imgs = [];
        foreach ($a->getElementsByTagName('img') as $im) {
            $imgs[] = $im;
        }
        if (trim($a->textContent) === '' && count($imgs) > 0) {
            foreach ($imgs as $im) {
                $a->parentNode->insertBefore($im, $a);
            }
            $a->parentNode->removeChild($a);
        } elseif (trim($a->textContent) === '' && $a->getAttribute('href') === '') {
            $a->parentNode->removeChild($a);
        }
    }

    // 10) Demote oversized headings so every post uses the same heading scale
    //     (the page already has its own <h1>; content tops out at <h3>)
    foreach ([['h1', 'h3'], ['h2', 'h3'], ['h5', 'h4'], ['h6', 'h4']] as $pair) {
        $nodes = [];
        foreach ($root->getElementsByTagName($pair[0]) as $n) {
            $nodes[] = $n;
        }
        foreach ($nodes as $n) {
            if ($n->parentNode) {
                $toElement($n, $pair[1]);
            }
        }
    }

    // 11) Bold pseudo-headings become real headings
    $inlineParents = ['p', 'li', 'td', 'th', 'dd', 'dt', 'blockquote', 'a', 'span', 'em', 'i', 'font', 'label', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6'];
    $blockTags = ['p', 'div', 'ul', 'ol', 'table', 'blockquote', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'hr', 'section', 'article', 'figure', 'tr', 'pre', 'br'];
    $boldNodes = [];
    foreach ($root->getElementsByTagName('b') as $n) {
        $boldNodes[] = $n;
    }
    foreach ($root->getElementsByTagName('strong') as $n) {
        $boldNodes[] = $n;
    }
    foreach ($boldNodes as $b) {
        if (!$b->parentNode || !($b->parentNode instanceof DOMElement)) {
            continue;
        }
        $text = trim(preg_replace('/\s+/u', ' ', $b->textContent));
        if ($text === '' || mb_strlen($text) > 250) {
            continue;
        }
        $parent = $b->parentNode;
        $parentTag = strtolower($parent->nodeName);

        // Never promote bold text inside tables to headings - table cells
        // stay compact (first row is styled as the header by CSS)
        $inTable = false;
        $walkUp = $parent;
        while ($walkUp !== null && $walkUp instanceof DOMElement) {
            $upTag = strtolower($walkUp->nodeName);
            if (in_array($upTag, ['table', 'thead', 'tbody', 'tr', 'td', 'th'], true)) {
                $inTable = true;
                break;
            }
            $walkUp = $walkUp->parentNode;
        }
        if ($inTable) {
            continue;
        }

        // <p> whose text is entirely bold becomes a subheading <h3>
        // (handles inline wrappers and multiple <b> runs in one paragraph)
        if ($parentTag === 'p') {
            $ok = true;
            $hasBold = false;
            $walkBold = function ($node, $inBold) use (&$walkBold, &$ok, &$hasBold) {
                $junk = [];
                foreach ($node->childNodes as $cn) {
                    if ($cn->nodeType === XML_TEXT_NODE) {
                        if (preg_match('/\S/u', $cn->nodeValue) && !$inBold) {
                            // Punctuation glue between bold runs (e.g. "<b>1</b>.<b>Title</b>")
                            // belongs to the heading, not to body text.
                            $plain = trim(preg_replace('/\s+/u', ' ', $cn->nodeValue));
                            if (preg_match('/^[\p{P}\p{S}]+$/u', $plain)) {
                                $cn->nodeValue = $plain . ' ';
                            } else {
                                $ok = false;
                            }
                        }
                        continue;
                    }
                    if ($cn->nodeType !== XML_ELEMENT_NODE) {
                        $ok = false;
                        continue;
                    }
                    $t = strtolower($cn->nodeName);
                    if ($t === 'b' || $t === 'strong') {
                        $hasBold = true;
                        $walkBold($cn, true);
                    } elseif (in_array($t, ['span', 'font', 'i', 'em', 'u'], true)) {
                        $walkBold($cn, $inBold);
                    } elseif (in_array($t, ['img', 'iframe', 'video', 'audio', 'table', 'ul', 'ol', 'blockquote'], true)) {
                        // Real content inside a would-be heading: reject instead of
                        // deleting it (the empty-shell branch below removes nodes
                        // without text, which would otherwise eat media/tables).
                        $ok = false;
                    } elseif (!preg_match('/\S/u', $cn->textContent)) {
                        // Blogger leaves empty shells (e.g. "<p/>" from br cleanup)
                        // inside bold text - drop them so the heading still converts.
                        $junk[] = $cn;
                    } else {
                        $ok = false;
                    }
                }
                foreach ($junk as $jn) {
                    if ($jn->parentNode) {
                        $jn->parentNode->removeChild($jn);
                    }
                }
            };
            $walkBold($parent, false);
            if ($ok && $hasBold && $parent->parentNode) {
                $h = $dom->createElement('h3');
                while ($parent->firstChild) {
                    $h->appendChild($parent->firstChild);
                }
                $parent->parentNode->replaceChild($h, $parent);
            }
            continue;
        }
        if (in_array($parentTag, $inlineParents, true)) {
            continue;
        }

        // Standalone bold line: neighbours are blocks (or nothing)
        $prev = $b->previousSibling;
        while ($prev && $prev->nodeType === XML_TEXT_NODE && trim($prev->nodeValue) === '') {
            $prev = $prev->previousSibling;
        }
        $next = $b->nextSibling;
        while ($next && $next->nodeType === XML_TEXT_NODE && trim($next->nodeValue) === '') {
            $next = $next->nextSibling;
        }
        $prevOk = ($prev === null)
            || ($prev->nodeType === XML_ELEMENT_NODE && in_array(strtolower($prev->nodeName), $blockTags, true));
        $nextOk = ($next === null)
            || ($next->nodeType === XML_ELEMENT_NODE && in_array(strtolower($next->nodeName), $blockTags, true));
        if ($prevOk && $nextOk) {
            $toElement($b, 'h3');
        }
    }

    // 11b) Headings keep their own typeface: unwrap inline wrappers
    //      (<b>, <span>, <font>, ...) so heading text is never split
    //      between Prata (heading) and Lato (wrapper) styles.
    $inlineWrap = ['b', 'strong', 'span', 'font', 'i', 'em', 'u', 'small', 'mark'];
    foreach (['h1', 'h2', 'h3', 'h4', 'h5', 'h6'] as $htag) {
        $heads = [];
        foreach ($root->getElementsByTagName($htag) as $hn) {
            $heads[] = $hn;
        }
        foreach ($heads as $hn) {
            // Repeat until stable: unwrapping a <span> can expose a deeper
            // <b>/<i> as a direct child on the next pass.
            while (true) {
                $wrapped = [];
                foreach ($inlineWrap as $wtag) {
                    foreach ($hn->getElementsByTagName($wtag) as $wn) {
                        if ($wn->parentNode === $hn) {
                            $wrapped[] = $wn;
                        }
                    }
                }
                if (!$wrapped) {
                    break;
                }
                foreach ($wrapped as $wn) {
                    while ($wn->firstChild) {
                        $hn->insertBefore($wn->firstChild, $wn);
                    }
                    $hn->removeChild($wn);
                }
            }
        }
    }

    // 12) Responsive table wrappers
    $tables = [];
    foreach ($root->getElementsByTagName('table') as $t) {
        $tables[] = $t;
    }
    foreach ($tables as $t) {
        if (!$t->parentNode) {
            continue;
        }
        $wrap = $dom->createElement('div');
        $wrap->setAttribute('class', 'bd-table-wrap');
        $t->parentNode->insertBefore($wrap, $t);
        $wrap->appendChild($t);
    }

    // 13) Remove empty spacer paragraphs (nbsp-only counts as empty)
    $paras = [];
    foreach ($root->getElementsByTagName('p') as $p) {
        $paras[] = $p;
    }
    foreach ($paras as $p) {
        if (!$p->parentNode || preg_match('/\S/u', str_replace("\xC2\xA0", ' ', $p->textContent))) {
            continue;
        }
        if ($p->getElementsByTagName('img')->length > 0 || $p->getElementsByTagName('iframe')->length > 0) {
            continue;
        }
        $p->parentNode->removeChild($p);
    }

    // 13b) Remove table rows/cells that ended up completely empty
    $rows = [];
    foreach ($root->getElementsByTagName('tr') as $tr) {
        $rows[] = $tr;
    }
    foreach ($rows as $tr) {
        if (!$tr->parentNode) {
            continue;
        }
        $hasContent = false;
        foreach ($tr->childNodes as $cell) {
            if ($cell->nodeType === XML_ELEMENT_NODE
                && preg_match('/\S/u', str_replace("\xC2\xA0", ' ', $cell->textContent))) {
                $hasContent = true;
                break;
            }
        }
        if (!$hasContent) {
            $tr->parentNode->removeChild($tr);
        }
    }

    // 14) Output the cleaned fragment
    $out = '';
    foreach ($root->childNodes as $child) {
        $out .= $dom->saveHTML($child);
    }
    libxml_clear_errors();
    return $out;
}

/**
 * Hybrid renderer for blog-post pages.
 *
 * Layer 1 — professional plain text: the 'formatted' field of
 * data/blog_posts_plain.json (rules mirror admin/format_plaintext.php) is
 * turned into semantic HTML so every post renders identically:
 *   - the leading post-title block (already shown by the page <h1>) is skipped
 *   - short single lines without terminal punctuation become <h2> headings
 *   - numbered lines become <ol> lists (true numbers kept via counter-reset)
 *   - "- "/"• " lines become <ul> lists; bullets following a numbered item nest
 *     under that item
 *   - everything else becomes one <p> per line; text is escaped XSS-safely.
 *
 * Layer 2 — original media & links: the raw Blogger HTML of the same post is
 * walked in DOM order and its images / tables / videos are re-inserted at the
 * position that matches the surrounding text. Images and videos carry no
 * letters, so a letter-offset anchor maps exactly back into the rendered text;
 * tables re-insert the real table and suppress the duplicate cell-text blocks
 * they replace. Inline hyperlinks are restored by wrapping their visible text.
 * Nothing is added, dropped, duplicated or reordered.
 */

/** Letters-only normalisation (same as the formatter's pb_norm). */
function pb_pnorm($s) {
    $s = mb_strtolower((string)$s);
    $s = preg_replace('/\d+/u', '', $s);
    return preg_replace('/[\p{P}\p{S}\s]+/u', '', $s);
}

/** Idempotent line cleaning (mirrors pb_clean_line; formatted text is already clean). */
function pb_pclean_lines($lines) {
    $out = [];
    foreach ($lines as $l) {
        $l = str_replace('**', '', $l);
        $l = str_replace('`', '', $l);
        $l = preg_replace('/^\s*(#+)\s+/u', '', $l);
        $l = trim(preg_replace('/[ \t]+/u', ' ', $l));
        if ($l !== '') {
            $out[] = $l;
        }
    }
    return $out;
}

/** Classify a block exactly like the formatter's pb_classify_block. */
/** A short data line like "2 moong dal cheela provides approx 12g protein".
 *  These read as recipe facts, not section headings, and must render as
 *  paragraphs (consistent with the same lines that happen to end with a "."). */
function pb_is_fact_line($t) {
    $t = trim($t);
    return (bool)preg_match('/\b\d+(?:\.\d+)?\s*(?:g|gm|grams?)\s+(?:of\s+)?protein\.?$/iu', $t);
}

function pb_pclassify($lines) {
    $markers = 0;
    foreach ($lines as $l) {
        if (preg_match('/^\s*\d{1,3}\s*[.)]\s*/u', $l) || preg_match('/^\s*[-•*>]\s*/u', $l)) {
            $markers++;
        }
    }
    // Any block that contains a numbered/bulleted line renders as a list so
    // numbered items are never dropped into inline paragraphs (which made the
    // numbering look skipped/inconsistent).
    if ($markers > 0) {
        return 'list';
    }
    if (count($lines) === 1) {
        $l = $lines[0];
        if (mb_strlen($l) <= 90 && !preg_match('/[.!?,;:।]$/u', $l)) {
            if (pb_is_fact_line($l)) {
                return 'para';
            }
            return 'heading';
        }
    }
    return 'para';
}

/** Count the Unicode letters in a string. */
function pb_pletters($s) {
    if (!preg_match_all('/\p{L}/u', (string)$s, $m)) {
        return 0;
    }
    return count($m[0]);
}

/** Letters-only lowercase form of a string (used for link matching). */
function pb_pletters2($s) {
    $s = mb_strtolower((string)$s);
    return preg_replace('/[^\p{L}]/u', '', $s);
}

/** Build the HTML for one list block, nesting sub-bullets under numbered items. */
function pb_plist_html($lines, &$lettersOut = null) {
    $out = '';
    $lettersLocal = 0;
    $inOl = false;
    $inUl = false;
    $liOpen = false;
    $subOpen = false;
    $expect = null;

    foreach ($lines as $line) {
        if (preg_match('/^\s*(\d{1,3})\s*[.)]\s*(.*)$/u', $line, $m)) {
            $num = (int)$m[1];
            $text = $m[2];

            if ($subOpen) {
                $out .= '</ul>';
                $subOpen = false;
            }
            if ($liOpen) {
                $out .= '</li>';
                $liOpen = false;
            }
            if (!$inOl) {
                if ($inUl) {
                    $out .= '</ul>';
                    $inUl = false;
                }
                $inOl = true;
                $expect = $num;
                $out .= '<ol start="' . $num . '" style="counter-reset: bd ' . ($num - 1) . '">';
            }
            $dev = ($num === $expect) ? '' : ' style="counter-reset: bd ' . ($num - 1) . '"';
            $out .= '<li' . $dev . '>' . pb_ptext($text);
            $lettersLocal += pb_pletters($text);
            $liOpen = true;
            $expect = $num + 1;
        } elseif (preg_match('/^\s*[-•*]\s*(.*)$/u', $line, $m)) {
            $text = pb_pdestrip($m[1]);
            $lettersLocal += pb_pletters($text);
            if ($liOpen) {
                if (!$subOpen) {
                    $out .= '<ul>';
                    $subOpen = true;
                }
                $out .= '<li>' . pb_ptext($text) . '</li>';
            } elseif ($inOl) {
                // Bullet directly between items (shouldn't occur after formatting):
                // emit as an <li> so the content is never dropped.
                $out .= '<li>' . pb_ptext($text) . '</li>';
            } else {
                if (!$inUl) {
                    $out .= '<ul>';
                    $inUl = true;
                }
                $out .= '<li>' . pb_ptext($text) . '</li>';
            }
        } else {
            // Unmarked line in a list block: fold into the open item like the
            // formatter does, or emit as a paragraph so no text is ever lost.
            $lettersLocal += pb_pletters($line);
            if ($liOpen) {
                $out .= ' ' . pb_ptext($line);
            } else {
                $out .= '<p>' . pb_ptext($line) . '</p>';
            }
        }
    }

    if ($subOpen) {
        $out .= '</ul>';
    }
    if ($liOpen) {
        $out .= '</li>';
    }
    if ($inOl) {
        $out .= '</ol>';
    }
    if ($inUl) {
        $out .= '</ul>';
    }
    if ($lettersOut !== null) {
        $lettersOut = $lettersLocal;
    }
    return $out;
}

/** Drop leftover ">" blockquote markers (mirrors pb_destrip_gt). */
function pb_pdestrip($s) {
    $s = preg_replace('/^\s*>+\s*/u', '', $s);
    return preg_replace('/^[-•*]\s*>\s*/u', '', $s);
}

/**
 * Escape text (XSS-safe). Bare URLs are left as plain text: only links that
 * existed in the original post are restored by pb_restore_links_html(), so the
 * final page carries exactly the original hyperlinks and no invented ones.
 */
function pb_ptext($s) {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

/** Turn 'formatted' plain text into emission units (html + letter count). */
function pb_plain_units($plain, $pageTitle) {
    $plain = str_replace(["\r\n", "\r"], "\n", (string)$plain);
    $blocks = preg_split('/\n\s*\n/u', $plain);
    if ($blocks === false) {
        return [];
    }

    $tNorm = $pageTitle !== '' ? pb_pnorm($pageTitle) : '';
    $units = [];
    $titleSkipped = false;

    foreach ($blocks as $b) {
        $rawLines = [];
        foreach (explode("\n", $b) as $raw) {
            $raw = trim(str_replace(["\xC2\xA0", "\xE2\x80\x8B", "\xE2\x80\xAF"], ' ', $raw));
            if ($raw !== '') {
                $rawLines[] = $raw;
            }
        }
        if (!$rawLines) {
            continue;
        }

        if (!$titleSkipped && count($rawLines) === 1 && $tNorm !== '' && pb_pnorm($rawLines[0]) === $tNorm) {
            // Keep a zero-width unit carrying the title's letters so letter
            // offsets keep matching the raw stream (including the embedded title).
            $units[] = ['html' => '', 'letters' => pb_pletters($rawLines[0]), 'kind' => 'title', 'text' => '', 'lead' => false];
            $titleSkipped = true;
            continue;
        }
        $titleSkipped = true;

        $lines = pb_pclean_lines($rawLines);
        if (!$lines) {
            continue;
        }

        // A block whose final line ends in ":-" (colon plus hyphen, possibly with
        // a range dash) explicitly introduces a list of following points, e.g.
        // "follow these steps:-" or "...are given below:-". A plain trailing ":"
        // (e.g. "Click on this link to register...:") is common in running prose
        // and must NOT turn the next block into a list.
        $lead = (bool)preg_match('/:\s*-+\s*$/u', trim($lines[count($lines) - 1]));

        $type = pb_pclassify($lines);
        if ($type === 'list') {
            $letters = 0;
            $html = pb_plist_html($lines, $letters);
            $units[] = ['html' => $html, 'letters' => $letters, 'kind' => 'list', 'text' => '', 'lead' => $lead];
        } elseif ($type === 'heading') {
            $units[] = ['html' => '<h2>' . pb_ptext($lines[0]) . '</h2>',
                        'letters' => pb_pletters($lines[0]), 'kind' => 'heading', 'text' => $lines[0], 'lead' => $lead];
        } else {
            foreach ($lines as $ln) {
                $units[] = ['html' => '<p>' . pb_ptext($ln) . '</p>',
                            'letters' => pb_pletters($ln), 'kind' => 'para', 'text' => '', 'lead' => $lead];
            }
        }
    }

    // NOTE: consecutive headings are intentionally NOT merged here. The hybrid
    // renderer must decide table suppression per heading line first (a merged
    // run could straddle a table span and drop real text); merging happens at
    // emission time via pb_emit_headings().

    return $units;
}

/**
 * CTA / signpost headings (e.g. "Click here to Enroll Now", "Check our
 * different diet plans") are closing banners, not list points. They must
 * never be merged into an <li> list and always render as their own heading.
 */
function pb_is_cta_line($t) {
    $t = mb_strtolower(trim($t));
    if ($t === 'check our different diet plans') {
        return true;
    }
    return (bool)preg_match('/\b(?:click here|enroll now|enroll today|enroll for|register now|join now|book (?:your )?now|sign up now|call now|know more|apply now|get started|read more|book appointment)\b|^(?:find relief with)/u', $t);
}

/** A bare URL heading (a link/source line, never a list point). */
function pb_is_url_line($t) {
    $t = trim($t);
    return (bool)preg_match('/^(?:\S+:\/\/\S+|www\.[^\s]+)$/u', $t);
}

/** Meal-time labels used to structure a diet plan into labelled rows. */
function pb_is_meal_time($t) {
    $t = mb_strtolower(trim($t));
    $t = preg_replace('/[\s\-–—\/]+/u', '', $t);
    static $labels = [
        'earlymorning', 'beforebreakfast', 'prebreakfast', 'breakfast',
        'brunch', 'midmorning', 'morning', 'lunch', 'midafternoon',
        'afternoon', 'postlunch', 'evening', 'postdinner', 'dinnertime',
        'dinner', 'bedtime', 'preworkout', 'postworkout', 'snack',
        'snacks', 'morningsnack', 'eveningsnack', 'nightsnack', 'midnightsnack',
    ];
    return in_array($t, $labels, true);
}

/** Build the structured diet-plan table for heading span [i..j]. */
function pb_meal_plan_html($units, $i, $j) {
    $rows = [];
    $cur = null;
    for ($x = $i; $x <= $j; $x++) {
        $t = trim($units[$x]['text']);
        if (pb_is_meal_time($t)) {
            if ($cur !== null && $cur['items']) {
                $rows[] = $cur;
            }
            $cur = ['label' => pb_ptext($t), 'items' => []];
        } else {
            if ($cur === null) {
                $cur = ['label' => '', 'items' => []];
            }
            $cur['items'][] = pb_ptext($t);
        }
    }
    if ($cur !== null && $cur['items']) {
        $rows[] = $cur;
    }
    $html = '';
    foreach ($rows as $row) {
        $lis = '';
        foreach ($row['items'] as $it) {
            $lis .= '<li>' . $it . '</li>';
        }
        $html .= '<tr><th class="bd-meal-time">' . $row['label'] . '</th>'
            . '<td class="bd-meal-items"><ul>' . $lis . '</ul></td></tr>';
    }
    return '<div class="bd-meal-wrap"><table class="bd-meal-plan"><tbody>' . $html . '</tbody></table></div>';
}

/**
 * Emit units, turning anything that looks like a list point into a real <li>:
 *  - consecutive short heading lines -> a compact list instead of stacked <h2>
 *  - points/rows that follow a ":-" (or ":") lead line -> a list too
 *  - the lead line itself (e.g. "...steps:-") is kept as its own heading /
 *    paragraph / numbered item and is never merged into a list
 *  - CTA / signpost headings and the standard post-closing lines are never
 *    inside a list
 *  - bare URL headings are kept as headings
 * Runs are decided AFTER table suppression, so a heading that belongs to a
 * reattached table is removed first and never bundled with real headings.
 * Never loses letters and never reorders anything.
 */
function pb_emit_headings($units, $insert, $suppress, $n) {
    $out = '';
    $run = [];
    $insertBefore = [];
    $steps = false;
    $stepsList = [];

    $emitRun = function () use (&$run, &$insertBefore, &$out) {
        if (!$run) {
            return;
        }
        $pre = '';
        foreach ($insertBefore as $h) {
            $pre .= $h;
        }
        if (count($run) >= 2) {
            $items = '';
            foreach ($run as $h) {
                $items .= '<li>' . pb_ptext($h['text']) . '</li>';
            }
            $out .= $pre . '<ul class="bd-heading-list">' . $items . '</ul>';
        } else {
            $out .= $pre . $run[0]['html'];
        }
        $run = [];
        $insertBefore = [];
    };

    $emitSteps = function () use (&$steps, &$stepsList, &$out) {
        if ($steps && $stepsList) {
            $items = '';
            foreach ($stepsList as $s) {
                $items .= '<li>' . $s['html'] . '</li>';
            }
            $out .= '<ul class="bd-steps">' . $items . '</ul>';
        }
        $steps = false;
        $stepsList = [];
    };

    // Pre-pass: locate consecutive heading spans that form a diet plan (a
    // meal-time label followed by item headings) so they render as a proper
    // meal table instead of a flat <li> list. CTA / URL headings end a span.
    $mealStart = [];
    for ($k = 0; $k < $n; $k++) {
        if (isset($suppress[$k]) || ($units[$k]['kind'] ?? '') !== 'heading') {
            continue;
        }
        if (!pb_is_meal_time($units[$k]['text'])) {
            continue;
        }
        $j = $k;
        while ($j + 1 < $n) {
            $nx = $j + 1;
            if (isset($suppress[$nx]) || ($units[$nx]['kind'] ?? '') !== 'heading') {
                break;
            }
            $tx = trim($units[$nx]['text']);
            if (pb_is_cta_line($tx) || pb_is_url_line($tx)) {
                break;
            }
            $j = $nx;
        }
        if ($j > $k) {
            $mealStart[$k] = $j;
            $k = $j;
        }
    }

    for ($k = 0; $k < $n; $k++) {
        if (isset($suppress[$k])) {
            $emitRun();
            $emitSteps();
            foreach (($insert[$k] ?? []) as $h) {
                $out .= $h;
            }
            continue;
        }

        $u = $units[$k];
        if ($u['kind'] === 'title') {
            continue; // zero-width title unit; letters tracked, nothing emitted
        }

        // Standard post-closing lines and CTA banners must never sit inside a
        // step list.
        if ($steps && $u['kind'] === 'para'
            && stripos(trim(strip_tags($u['html'])), 'please comment below') === 0) {
            $emitSteps();
            foreach (($insert[$k] ?? []) as $h) {
                $out .= $h;
            }
            $out .= $u['html'];
            continue;
        }
        if ($u['kind'] === 'heading' && pb_is_cta_line($u['text'])) {
            $emitRun();
            $emitSteps();
            foreach (($insert[$k] ?? []) as $h) {
                $out .= $h;
            }
            $out .= $u['html'];
            continue;
        }

        // A diet-plan span begins here: emit its meal table and skip over it.
        if (isset($mealStart[$k])) {
            $emitRun();
            $emitSteps();
            $j = $mealStart[$k];
            for ($x = $k; $x <= $j; $x++) {
                if (isset($suppress[$x])) {
                    continue;
                }
                foreach (($insert[$x] ?? []) as $h) {
                    $out .= $h;
                }
            }
            $out .= pb_meal_plan_html($units, $k, $j);
            $k = $j;
            continue;
        }

        $isNumItem = ($u['kind'] === 'list');
        $stepItem = null;
        if ($steps && !$u['lead'] && !$isNumItem && $u['kind'] !== 'para') {
            // Inside a colon-dash list, short heading lines become points.
            $stepItem = $u;
        } elseif ($steps && !$u['lead'] && !$isNumItem && $u['kind'] === 'para') {
            // Short single-line paragraphs also look like list points; real
            // prose paragraphs (trailing template text, longer sentences) stay
            // as ordinary paragraphs.
            if (mb_strlen(trim(strip_tags($u['html']))) <= 120) {
                $stepItem = $u;
            }
        }

        if ($stepItem !== null) {
            // A media element targeted inside this list splits it into two
            // lists, preserving the media's original flow position.
            if (($insert[$k] ?? []) !== []) {
                $emitSteps();
                foreach (($insert[$k] ?? []) as $h) {
                    $out .= $h;
                }
                $steps = true;
                $stepsList = [];
            }
            if ($stepItem['kind'] === 'heading') {
                $stepItem = ['html' => '<strong>' . $stepItem['text'] . '</strong>'];
            } else {
                $stepItem = ['html' => trim(preg_replace('/^<p>(.*)<\/p>$/s', '$1', $stepItem['html']))];
            }
            $stepsList[] = $stepItem;
            continue;
        }

        if ($u['lead']) {
            // Lead line ( "...:-" ): close any open list / run, emit it as-is,
            // then the following content becomes the new list.
            $emitRun();
            $emitSteps();
            foreach (($insert[$k] ?? []) as $h) {
                $out .= $h;
            }
            $out .= $u['html'];
            $steps = true;
            $stepsList = [];
            continue;
        }

        if ($isNumItem) {
            // Fresh numbered item starts a new section.
            $emitRun();
            $emitSteps();
            foreach (($insert[$k] ?? []) as $h) {
                $out .= $h;
            }
            $out .= $u['html'];
            continue;
        }

        if ($u['kind'] === 'heading') {
            // A bare URL heading is a link/source line, not a list point; do
            // not merge it into a heading list.
            if (pb_is_url_line($u['text'])) {
                $emitRun();
                $emitSteps();
                foreach (($insert[$k] ?? []) as $h) {
                    $out .= $h;
                }
                $out .= $u['html'];
                continue;
            }
            foreach (($insert[$k] ?? []) as $h) {
                $insertBefore[] = $h;
            }
            $run[] = $u;
            continue;
        }

        // Ordinary paragraph etc.
        $emitRun();
        $emitSteps();
        foreach (($insert[$k] ?? []) as $h) {
            $out .= $h;
        }
        $out .= $u['html'];
    }

    $emitRun();
    $emitSteps();
    foreach (($insert[$n] ?? []) as $h) {
        $out .= $h;
    }
    return $out;
}

/**
 * Walk the raw Blogger HTML in DOM order and collect:
 *   - 'media': images/videos/tables with their letter-offset anchors
 *   - 'links': inline hyperlinks (visible letters + position)
 *   - 'totalLetters': letters of the visible text stream (tables included,
 *     style/script ignored)
 */
function pb_extract_stream($raw) {
    $media = [];
    $links = [];
    $letters = 0;

    $raw = (string)$raw;
    if ($raw === '') {
        return ['media' => $media, 'links' => $links, 'totalLetters' => 0];
    }

    $dom = new DOMDocument();
    libxml_use_internal_errors(true);
    $loaded = $dom->loadHTML('<meta http-equiv="Content-Type" content="text/html; charset=utf-8">' . $raw);
    libxml_clear_errors();
    if (!$loaded || !$dom->documentElement) {
        return ['media' => $media, 'links' => $links, 'totalLetters' => 0];
    }

    $skipTags = ['style', 'script', 'noscript', 'head', 'meta', 'link', 'title', 'template'];

    $walk = function ($node) use (&$walk, &$media, &$links, &$letters, $skipTags, $dom) {
        if ($node->nodeType === XML_TEXT_NODE) {
            $letters += pb_pletters($node->nodeValue);
            return;
        }
        if ($node->nodeType !== XML_ELEMENT_NODE) {
            return;
        }
        $tag = strtolower($node->nodeName);
        if (in_array($tag, $skipTags, true)) {
            return;
        }

        if ($tag === 'img' || $tag === 'iframe' || $tag === 'video' || $tag === 'embed') {
            $media[] = ['type' => $tag, 'html' => $dom->saveHTML($node), 'anchor' => $letters,
                        'cellStart' => null, 'cellEnd' => null];
            // Media elements carry no text of their own (alt/src are attributes).
            if ($tag !== 'img' && $tag !== 'embed') {
                for ($i = 0; $i < $node->childNodes->length; $i++) {
                    $child = $node->childNodes->item($i);
                    if ($child->nodeType === XML_TEXT_NODE) {
                        $letters += pb_pletters($child->nodeValue);
                    }
                }
            }
            return;
        }

        if ($tag === 'table') {
            $cellLetters = pb_pletters($node->textContent);
            $media[] = ['type' => 'table', 'html' => $dom->saveHTML($node), 'anchor' => $letters,
                        'cellStart' => $letters, 'cellEnd' => $letters + $cellLetters];
            $letters += $cellLetters;
            return;
        }

        if ($tag === 'a') {
            $href = $node->getAttribute('href');
            $inner = pb_pletters2($node->textContent);
            // Anchor that merely wraps a media element (no visible text) is kept
            // as-is so the clickable image/video keeps its original hyperlink
            // instead of being reduced to a bare, unlinked <img>.
            if ($href !== '' && $inner === '') {
                $hasMedia = false;
                foreach ($node->childNodes as $child) {
                    if ($child->nodeType === XML_ELEMENT_NODE) {
                        $ct = strtolower($child->nodeName);
                        if (in_array($ct, ['img', 'iframe', 'video', 'embed'], true)) {
                            $hasMedia = true;
                            break;
                        }
                    }
                }
                if ($hasMedia) {
                    $media[] = ['type' => 'a-media', 'html' => $dom->saveHTML($node), 'anchor' => $letters,
                                'cellStart' => null, 'cellEnd' => null];
                    return;
                }
            }
            $linkPos = $letters;
            for ($i = 0; $i < $node->childNodes->length; $i++) {
                $walk($node->childNodes->item($i));
            }
            if ($href !== '' && $inner !== '') {
                $links[] = ['href' => $href, 'txt' => $inner, 'pos' => $linkPos, 'len' => preg_match_all('/\p{L}/u', $inner, $mm)];
            }
            return;
        }

        for ($i = 0; $i < $node->childNodes->length; $i++) {
            $walk($node->childNodes->item($i));
        }
    };

    $walk($dom->documentElement);

    return ['media' => $media, 'links' => $links, 'totalLetters' => $letters];
}

/** Decide the letter-offset shift between the raw stream and the formatted text. */
function pb_resolve_m($rawTotal, $fmtTotal, $titleLetters) {
    if ($rawTotal === $fmtTotal) {
        return 0; // raw stream already embeds the post title
    }
    if ($rawTotal > 0 && ($rawTotal + $titleLetters) === $fmtTotal) {
        return $titleLetters; // formatted text prepends the title
    }
    $delta = $fmtTotal - $rawTotal;
    return ($delta > 0) ? $delta : 0;
}

/** Number of units whose cumulative letters end at or before $target. */
function pb_insert_index($cum, $target) {
    $idx = 0;
    foreach ($cum as $c) {
        if ($c <= $target) {
            $idx++;
        } else {
            break;
        }
    }
    return $idx;
}

/** Wrap a raw <table> in the existing responsive overflow container. */
function pb_table_html($html) {
    return '<div class="bd-table-wrap">' . $html . '</div>';
}

/** True when a unit is a bare numeric table cell ("0", "210", "7. 8"…) with
 *  no letters of its own. Such cells sit at the same letter offset as the
 *  previous cell, so on their own they fall outside the table's suppressed
 *  window and would otherwise re-render as stray heading-list <li> items. */
function pb_is_numeric_cell($u) {
    $t = trim($u['text'] !== '' ? $u['text'] : strip_tags($u['html']));
    if ($t === '') {
        return false;
    }
    return (bool)preg_match('/^\d[\d\s.,()%+\-\/½¼¾]*$/u', $t);
}

/**
 * Hybrid content: plain-text units re-attached with the original media
 * (images/tables/videos) in their correct flow positions, then inline
 * hyperlinks restored. Falls back to the plain text alone when raw HTML is
 * empty or media fails to parse.
 */
function pb_hybrid_content($plainText, $pageTitle, $rawHtml) {
    $units = pb_plain_units($plainText, $pageTitle);
    if (!$units) {
        return '';
    }

    $stream = pb_extract_stream($rawHtml);
    if (!$stream['media'] && !$stream['links']) {
        $out = '';
        foreach ($units as $u) {
            $out .= $u['html'];
        }
        return $out;
    }

    $n = count($units);
    $cum = [];
    $acc = 0;
    foreach ($units as $u) {
        $acc += $u['letters'];
        $cum[] = $acc;
    }
    $fmtTotal = $acc;

    $titleLetters = (isset($units[0]) && $units[0]['html'] === '') ? $units[0]['letters'] : 0;
    $m = pb_resolve_m($stream['totalLetters'], $fmtTotal, $titleLetters);

    $insert = [];
    $suppress = [];

    foreach ($stream['media'] as $med) {
        if ($med['type'] === 'table') {
            $start = $med['cellStart'] + $m;
            $end = $med['cellEnd'] + $m;
            $firstIdx = -1;
            for ($k = 0; $k < $n; $k++) {
                $uStart = ($k === 0) ? 0 : $cum[$k - 1];
                $uEnd = $cum[$k];
                if ($uEnd > $start && $uStart < $end) {
                    if ($firstIdx === -1) {
                        $firstIdx = $k;
                    }
                    $suppress[$k] = true;
                }
            }
            if ($firstIdx !== -1) {
                // Numbers in the last row carry zero letters and land exactly
                // on the window boundary (uStart == uEnd == $end); fold them
                // into the table so they never re-emit as stray <li> items.
                for ($k = $firstIdx; $k < $n; $k++) {
                    $uStart = ($k === 0) ? 0 : $cum[$k - 1];
                    $uEnd = $cum[$k];
                    if ($uStart === $end && $uEnd === $end && pb_is_numeric_cell($units[$k])) {
                        $suppress[$k] = true;
                    }
                }
            }
            $idx = ($firstIdx === -1) ? pb_insert_index($cum, $start) : $firstIdx;
            $insert[$idx][] = pb_table_html($med['html']);
        } else {
            $idx = pb_insert_index($cum, $med['anchor'] + $m);
            $insert[$idx][] = $med['html'];
        }
    }

    $out = pb_emit_headings($units, $insert, $suppress, $n);
    foreach (($insert[$n] ?? []) as $h) {
        $out .= $h;
    }

    if ($stream['links']) {
        // Link windows are positioned in raw letter space shifted by $m (title
        // handling). The pass that re-inserts <a> tags walks only the letters
        // actually EMITTED into the HTML, so the zero-width title unit (whose
        // letters never reach the output) must be subtracted back out.
        $titleSkip = (isset($units[0]) && $units[0]['html'] === '') ? $units[0]['letters'] : 0;
        $linksFmt = [];
        foreach ($stream['links'] as $L) {
            $posE = $L['pos'] + $m - $titleSkip;
            $endE = $L['pos'] + $L['len'] + $m - $titleSkip;
            if ($posE < 0 || $endE <= $posE) {
                continue; // link sits inside the (un-emitted) title
            }
            $linksFmt[] = ['href' => $L['href'], 'txt' => $L['txt'],
                           'pos' => $posE, 'end' => $endE];
        }
        $out = pb_restore_links_html($out, $linksFmt);
    }

    return $out;
}

/** Count letters in an already-escaped text segment (HTML entities count zero). */
function pb_seg_letters($s) {
    $chars = preg_split('//u', $s, -1, PREG_SPLIT_NO_EMPTY);
    if ($chars === false) {
        return 0;
    }
    $letters = 0;
    $ciTotal = count($chars);
    for ($ci = 0; $ci < $ciTotal; $ci++) {
        $ch = $chars[$ci];
        if ($ch === '&') {
            for ($j = $ci + 1; $j < $ciTotal; $j++) {
                if ($chars[$j] === ';') {
                    $ci = $j;
                    break;
                }
            }
            continue;
        }
        if (preg_match('/\p{L}/u', $ch)) {
            $letters++;
        }
    }
    return $letters;
}

/**
 * Wrap the link windows (letter ranges) inside one escaped text segment.
 * Only windows fully contained in this segment are applied; existing anchors
 * (auto-linked bare URLs) and overlapping windows are left untouched.
 */
function pb_seg_apply_windows($s, $segStart, $windows, $inAnchor) {
    if (!$windows || $inAnchor) {
        return $s;
    }
    $chars = preg_split('//u', $s, -1, PREG_SPLIT_NO_EMPTY);
    if ($chars === false) {
        return $s;
    }
    $ciTotal = count($chars);

    $map = [];
    $li = 0;
    for ($ci = 0; $ci < $ciTotal; $ci++) {
        $ch = $chars[$ci];
        if ($ch === '&') {
            for ($j = $ci + 1; $j < $ciTotal; $j++) {
                if ($chars[$j] === ';') {
                    $ci = $j;
                    break;
                }
            }
            continue;
        }
        if (preg_match('/\p{L}/u', $ch)) {
            $map[$li] = $ci;
            $li++;
        }
    }
    if (!$map) {
        return $s;
    }

    $applied = [];
    foreach ($windows as $w) {
        $ws = $w['start'] - $segStart;
        $we = $w['end'] - $segStart;
        if ($ws < 0 || $we <= $ws || !isset($map[$ws]) || !isset($map[$we - 1])) {
            continue;
        }
        $cs = $map[$ws];
        $ce = $map[$we - 1] + 1;
        $got = '';
        for ($i = $cs; $i < $ce; $i++) {
            if ($chars[$i] === '&') {
                // Skip HTML entities so their names never add bogus letters.
                for ($j = $i + 1; $j < $ce && $j < $ciTotal; $j++) {
                    if ($chars[$j] === ';') {
                        $i = $j;
                        break;
                    }
                }
                continue;
            }
            if (preg_match('/\p{L}/u', $chars[$i])) {
                $got .= mb_strtolower($chars[$i]);
            }
        }
        if ($got !== $w['txt']) {
            continue;
        }
        $overlap = false;
        foreach ($applied as $a) {
            if ($cs < $a['ce'] && $ce > $a['cs']) {
                $overlap = true;
                break;
            }
        }
        if ($overlap) {
            continue;
        }
        $applied[] = ['cs' => $cs, 'ce' => $ce, 'href' => $w['href']];
    }

    if (!$applied) {
        return $s;
    }

    usort($applied, function ($a, $b) {
        return $b['cs'] <=> $a['cs'];
    });
    foreach ($applied as $a) {
        $inner = implode('', array_slice($chars, $a['cs'], $a['ce'] - $a['cs']));
        $tag = '<a href="' . htmlspecialchars($a['href'], ENT_QUOTES, 'UTF-8') . '">' . $inner . '</a>';
        $tagChars = preg_split('//u', $tag, -1, PREG_SPLIT_NO_EMPTY);
        if ($tagChars !== false) {
            array_splice($chars, $a['cs'], $a['ce'] - $a['cs'], $tagChars);
        }
    }
    return implode('', $chars);
}

/** Restore inline hyperlinks back into the rendered final HTML (position-based). */
function pb_restore_links_html($html, $links) {
    if (!$links) {
        return $html;
    }
    usort($links, function ($a, $b) {
        if ($a['pos'] !== $b['pos']) {
            return $a['pos'] <=> $b['pos'];
        }
        return $a['end'] <=> $b['end'];
    });

    $seg = preg_split('/(<\/?[a-zA-Z][^>]*>)/', $html, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
    if ($seg === false) {
        return $html;
    }

    $out = '';
    $lettersSoFar = 0;
    $inAnchor = false;
    $li = 0;
    $nl = count($links);

    foreach ($seg as $s) {
        if ($s === '') {
            continue;
        }
        if ($s[0] === '<') {
            if (preg_match('/^<a\b/i', $s)) {
                $inAnchor = true;
            } elseif (preg_match('#^</a>#i', trim($s)) === 1) {
                $inAnchor = false;
            }
            $out .= $s;
            continue;
        }

        $segLen = pb_seg_letters($s);
        $segStart = $lettersSoFar;
        $segEnd = $segStart + $segLen;

        $windows = [];
        while ($li < $nl && $links[$li]['pos'] < $segEnd) {
            $L = $links[$li];
            if ($L['end'] > $segStart) {
                $ws = max($segStart, $L['pos']);
                $we = min($segEnd, $L['end']);
                if ($we - $ws >= 1) {
                    $windows[] = ['start' => $ws, 'end' => $we, 'href' => $L['href'], 'txt' => $L['txt']];
                }
            }
            $li++;
        }

        $out .= pb_seg_apply_windows($s, $segStart, $windows, $inAnchor);
        $lettersSoFar = $segEnd;
    }

    return $out;
}

/** No-media fallback render (plain text HTML only, title already in <h1>). */
function pb_render_plain_html($plain, $pageTitle) {
    $out = '';
    foreach (pb_plain_units($plain, $pageTitle) as $u) {
        $out .= $u['html'];
    }
    return $out;
}

// Step 1: Extract slug from current URL (blog-post.php?slug=...)
if (isset($_GET['slug'])) {
    $slug = $_GET['slug'];

    // Step 1b: Old slug -> new slug (301) after blog slugs were regenerated.
    $aliasesFile = __DIR__ . '/data/blog_slug_aliases.json';
    if (is_file($aliasesFile)) {
        $aliases = json_decode((string)file_get_contents($aliasesFile), true);
        if (is_array($aliases) && isset($aliases[$slug])) {
            $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'] ?? 'www.biteanddiet.in';
            header('Location: ' . $scheme . '://' . $host . '/blog-post/' . $aliases[$slug], true, 301);
            exit;
        }
    }

    // Step 2: Load allposts.json and match slug
    $postsJson = file_get_contents('allposts.json');
    $posts = json_decode($postsJson, true);
    $matchedPost = null;

    foreach ($posts as $post) {
        if (isset($post['slug']) && $post['slug'] === $slug) {
            $postUrl = $post['postUrl'];
            break;
        }
    }

    if (!$postUrl) {
        header("Location: /error");
        exit();
    }

    // Step 2b: Serve from local cache for instant load (avoids slow Blogger API on repeat views)
    require_once __DIR__ . '/json_db.php';
    $postCache = jd_read('blog_post_cache', []);
    $staleEntry = null;
    $postFound = false;
    $isRefreshPass = isset($_GET['blog_refresh']) && $_GET['blog_refresh'] === '1';

    $applyCachedPost = function ($entry) use (&$postFound, &$encodedTitle, &$encodedDescription, &$keywords, &$encodedImageUrl, &$encodedUrl, &$formattedDate, &$content, &$authorName, &$publisherName) {
        $postFound = true;
        $encodedTitle = $entry['encodedTitle'] ?? '';
        $encodedDescription = $entry['encodedDescription'] ?? '';
        $keywords = $entry['keywords'] ?? '';
        $encodedImageUrl = $entry['encodedImageUrl'] ?? '';
        $encodedUrl = $entry['encodedUrl'] ?? '';
        $formattedDate = $entry['formattedDate'] ?? '';
        $content = $entry['content'] ?? '';
        $authorName = 'Dietician Priyanka';
        $publisherName = 'Bite and Diet';
    };

    if (isset($postCache[$slug]) && is_array($postCache[$slug])) {
        $entry = $postCache[$slug];
        $isFresh = isset($entry['cached_at']) && (time() - (int)$entry['cached_at']) < 12 * 3600;
        if ($isFresh && !$isRefreshPass) {
            $applyCachedPost($entry);
        } else {
            $staleEntry = $entry;
        }
    }

    if (!$postFound && $staleEntry !== null && !$isRefreshPass) {
        $applyCachedPost($staleEntry);
        $needsBackgroundRefresh = true;
    }

    if (!$postFound) {
    // Step 3: Call Blogger API and search for the post with this postUrl
    require_once __DIR__ . '/app_config.php';
    $apiKey = cfg('google_api_key');
    $blogId = cfg('blogger_blog_id');
    $pageToken = '';
    $postFound = false;

    $fastData = null;
    $postPath = parse_url($postUrl, PHP_URL_PATH) ?: '';
    if ($postPath !== '') {
        $bypathUrl = "https://www.googleapis.com/blogger/v3/blogs/$blogId/posts/bypath?path=" . rawurlencode($postPath) . "&key=$apiKey";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $bypathUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $fastResp = curl_exec($ch);
        $fastErr = curl_error($ch);
        curl_close($ch);
        if ($fastResp !== false && !$fastErr) {
            $fastData = json_decode($fastResp, true);
            if (!is_array($fastData) || isset($fastData['error']) || !isset($fastData['id']) || !isset($fastData['content'])) {
                $fastData = null;
            } else {
                $fastData = ['items' => [$fastData]];
                $fastData['nextPageToken'] = null;
            }
        }
    }

    do {
        if ($fastData !== null) {
            $data = $fastData;
            $fastData = null;
        } else {
            $apiUrl = "https://www.googleapis.com/blogger/v3/blogs/$blogId/posts?key=$apiKey";
            if ($pageToken) {
                $apiUrl .= "&pageToken=$pageToken";
            }

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $apiUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            $response = curl_exec($ch);

            if (curl_errno($ch)) {
                echo 'Curl error: ' . curl_error($ch);
                curl_close($ch);
                break;
            }
            curl_close($ch);

            $data = json_decode($response, true);
        }

        if (isset($data['error'])) {
            echo "API Error: " . $data['error']['message'];
            break;
        }

        if (isset($data['items']) && !empty($data['items'])) {
            foreach ($data['items'] as $post) {
                if (isset($post['url']) && $post['url'] === $postUrl) {
                    // ✅ Post matched, now handle rest like before
    
                  $encodedTitle = isset($post['title']) ? html_entity_decode($post['title'], ENT_QUOTES | ENT_HTML5, 'UTF-8') : 'No title';
                    $titlePostsJson = file_get_contents(__DIR__ . '/allposts.json');
                    $titlePosts = json_decode($titlePostsJson, true);
                    foreach ($titlePosts as $tp) {
                        if (isset($tp['slug']) && $tp['slug'] === $slug && !empty($tp['title'])) {
                            $encodedTitle = $tp['title'];
                            break;
                        }
                    }
                    
                    $formattedDate = isset($post['published']) ? date("F j, Y", strtotime($post['published'])) : 'No date';
                    $content = isset($post['content']) ? $post['content'] : 'No content';

                    // Remove <style>/<script> blocks and inline styles, but keep
                    // text-align (Blogger uses it for captions, quotes, lists).
                    $content = preg_replace('/<(style|script)\b[^>]*>.*?<\/\1>/is', '', $content);
                    $content = preg_replace_callback('/style="([^"]*)"/i', function ($m) {
                        if (preg_match('/text-align\s*:\s*(center|right|justify)/i', $m[1], $t)) {
                            return 'style="text-align:' . strtolower($t[1]) . '"';
                        }
                        return '';
                    }, $content);

                    // Process the HTML content
                    $dom = new DOMDocument();
                    libxml_use_internal_errors(true); // Prevent HTML errors from being displayed
                    $dom->loadHTML(mb_convert_encoding($content, 'HTML-ENTITIES', 'UTF-8'));

                    // Real headings are kept; blog_beautify_content() styles them
                    // and demotes <h1>/<h5>/<h6> before the page is rendered.

// 2. Remove anchor tags around images, keep other anchors intact
$anchors = $dom->getElementsByTagName('a');

for ($i = $anchors->length - 1; $i >= 0; $i--) {
    $anchor = $anchors->item($i);
    $img = $anchor->getElementsByTagName('img')->item(0);
    if ($img) {
        // Add lazy loading attribute to the image
        $img->setAttribute('loading', 'lazy');
        $img->setAttribute('style', 'cursor:pointer;');  // Add pointer cursor for clickable images
$img->setAttribute('onclick', '');

        // Replace <a><img></a> with just <img>
        $anchor->parentNode->replaceChild($img, $anchor);
    }
}

                    // Load and decode the YouTube videos JSON
                    $youtubeVideosJson = file_get_contents('youtube_videos.json');
                    $youtubeVideos = json_decode($youtubeVideosJson, true);

                    // Extract the video ID from the YouTube URL
// Extract video ID
function extractVideoId($url) {
    if (preg_match('/(?:https?:\/\/)?(?:www\.|m\.)?youtube\.com\/.*[?&]v=([^"&?\/\s]{11})/', $url, $matches)) {
        return $matches[1];
    } elseif (preg_match('/(?:https?:\/\/)?youtu\.be\/([^"&?\/\s]{11})/', $url, $matches)) {
        return $matches[1];
    }
    return null;
}
                    // Update anchor tags with YouTube video URLs
                    $anchors = $dom->getElementsByTagName('a');
                    for ($i = $anchors->length - 1; $i >= 0; $i--) {
                        $anchor = $anchors->item($i);
                        $href = $anchor->getAttribute('href');
                        $videoId = extractVideoId($href);

                        if ($videoId) {
                            // Check if the video ID is in the JSON file
                            $videoFound = false;
                            foreach ($youtubeVideos as $video) {
                                if (isset($video['videoId']) && $video['videoId'] === $videoId) {
                                    $videoTitle = isset($video['title']) ? urlencode($video['title']) : 'No Title';
                                  $formattedUrl = "https://www.biteanddiet.in/video/{$video['slug']}";

                                    $anchor->setAttribute('href', $formattedUrl);
                                    $videoFound = true;
                                    break;
                                }
                            }

                            if (!$videoFound) {
                                // Use the original URL if the video ID is not found
                                $anchor->setAttribute('href', $href);
                            }
                        }
                    }

                    // Remove anchor tags that link to the home page
                    for ($i = $anchors->length - 1; $i >= 0; $i--) {
                        $anchor = $anchors->item($i);
                        $href = $anchor->getAttribute('href');

                        // Check if the href is the home page URL
                        if (in_array($href, ['https://www.biteanddiet.in', 'https://www.biteanddiet.in/', 'https://biteanddiet.in/', 'www.biteanddiet.in'])) {
                            // Remove the anchor tag but keep the inner text
                            $anchor->parentNode->replaceChild($dom->createTextNode($anchor->nodeValue), $anchor);
                        }
                    }


// Load and decode allposts.json
$allPostsJson = file_get_contents(__DIR__ . '/allposts.json');
$allPosts = json_decode($allPostsJson, true);

// Build a mapping of normalized postUrl => slug
$postUrlToSlug = [];
foreach ($allPosts as $post) {
    $cleanUrl = preg_replace('/\?.*/', '', $post['postUrl']); // Remove query params
    $cleanUrl = str_replace(['http://', 'https://'], '', $cleanUrl); // Remove protocol
    if (substr($cleanUrl, -5) !== '.html') {
        $cleanUrl .= '.html';
    }
    $postUrlToSlug[$cleanUrl] = $post['slug'];
}

// Process all anchor tags in DOM
$anchors = $dom->getElementsByTagName('a');

for ($i = $anchors->length - 1; $i >= 0; $i--) {
    $anchor = $anchors->item($i);
    $href = $anchor->getAttribute('href');

    // Step 1: Extract actual URL if using /readpost?url=...
    if (strpos($href, '/readpost?url=') !== false) {
        parse_str(parse_url($href, PHP_URL_QUERY), $params);
        $actualUrl = $params['url'] ?? '';
    } else {
        $actualUrl = $href;
    }

    // Step 2: Normalize the URL (remove ?m=1 etc., ensure .html, remove http/https)
    $actualUrl = preg_replace('/\?.*/', '', $actualUrl);
    $actualUrl = str_replace(['http://', 'https://'], '', $actualUrl);
    if (substr($actualUrl, -5) !== '.html') {
        $actualUrl .= '.html';
    }

    // Step 3: Match and replace
    if (isset($postUrlToSlug[$actualUrl])) {
        $replacementSlug = $postUrlToSlug[$actualUrl];
        $anchor->setAttribute('href', '/blog-post/' . htmlspecialchars($replacementSlug));
    }
}







                    // Save the modified content
                    $content = $dom->saveHTML();

                    // Extract 65 words from content
                    $text = strip_tags($content);
                    $text = preg_replace('/\*\*(.+?)\*\*/su', '$1', $text);
                    $text = str_replace('**', '', $text);
                    $words = explode(' ', $text, 156);
                    if (count($words) > 155) {
                        array_pop($words);
                        $description = implode(' ', $words) . '...';
                    } else {
                        $description = $text;
                    }

                    // Combine title and description for keywords
                    $keywords = htmlentities($encodedTitle . ', ' . $description);

                    // Encode the description and URL
                    $encodedDescription = htmlentities($description);
                    
                    $encodedUrl = isset($post['url']) ? $post['url'] : '';


                    // Extract the first image URL from the content
                    preg_match('/<img[^>]+src="([^">]+)"/i', $content, $matches);
                    $firstImgUrl = isset($matches[1]) ? $matches[1] : '';

                    // Encode the image URL for HTML output
                    $encodedImageUrl = htmlentities($firstImgUrl);

                    // Set the author and publisher details
                    $authorName = 'Dietician Priyanka';
                    $publisherName = 'Bite and Diet';

                    $postFound = true;

                    $postCache[$slug] = [
                        'cached_at' => time(),
                        'encodedTitle' => $encodedTitle,
                        'encodedDescription' => $encodedDescription,
                        'keywords' => $keywords,
                        'encodedImageUrl' => $encodedImageUrl,
                        'encodedUrl' => $encodedUrl,
                        'formattedDate' => $formattedDate,
                        'content' => $content,
                    ];
                    jd_write('blog_post_cache', $postCache);

                    break 2; // Exit both loops
                }
            }
        }

        $pageToken = $data['nextPageToken'] ?? '';

    } while ($pageToken);

    if (!$postFound && is_array($staleEntry)) {
        $applyCachedPost($staleEntry);
    }
    }

    if (!$postFound) {
        header("Location: /error");
        exit();
    }

    // Render the professionally structured plain text (data/blog_posts_plain.json)
    // re-attached with the post's original images/tables/links from the raw
    // Blogger HTML. Falls back to the legacy HTML beautifier for posts without
    // plain text (e.g. cache-only placeholder posts not present on Blogger).
    $plainPosts = jd_read('blog_posts_plain', []);
    if (is_array($plainPosts)
        && isset($plainPosts[$slug]['formatted'])
        && is_string($plainPosts[$slug]['formatted'])
        && trim($plainPosts[$slug]['formatted']) !== '') {
        $content = pb_hybrid_content($plainPosts[$slug]['formatted'], $encodedTitle ?? '', $content);
    } else {
        $content = blog_beautify_content($content);
    }

    // Post has no image: use the default image for og:image/JSON-LD
    // and show it at the top of the blog content as well.
    if ($encodedImageUrl === '') {
        $encodedImageUrl = htmlentities($BLOG_FALLBACK_IMAGE);
    }
    if (stripos($content ?? '', '<img') === false) {
        $content = '<p><img src="' . $BLOG_FALLBACK_IMAGE . '" alt="Bite And Diet"></p>' . $content;
    }

} else {
    header("Location: /error");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="index, follow">
<title translate="no"><?php echo $encodedTitle; ?></title>
<meta name="description" content="<?php echo $encodedDescription; ?>">
<meta name="keywords" content="<?php echo $keywords; ?>">
<meta property="og:title" content="<?php echo $encodedTitle; ?>">
<meta property="og:description" content="<?php echo $encodedDescription; ?>">
<meta property="og:image" content="<?php echo $encodedImageUrl; ?>">
<meta property="og:url" content="https://www.biteanddiet.in/blog-post/<?php echo $slug; ?>">
<link rel="canonical" href="https://www.biteanddiet.in/blog-post/<?php echo $slug; ?>">
<style>
/* ---------------- Article body (Blogger content) ---------------- */

.notranslate {
    -webkit-translate: no;
}
.blog-content {
    font-family: lato, Arial, Helvetica, sans-serif;
    font-size: 17px;
    line-height: 1.9;
    color: #2f3a3c;
    background-color: #fff;
    border: 1px solid #e3eeec;
    border-radius: 20px;
    box-shadow: 0 24px 48px -30px rgba(11, 61, 57, .45);
    padding: 44px 50px;
    margin: 0 0 44px;
    letter-spacing: 0;
    overflow-wrap: break-word;
}
.blog-content > *:first-child { margin-top: 0 !important; }
.blog-content > *:last-child { margin-bottom: 0 !important; }

/* Paragraphs: generous rhythm, clean alignment */
.blog-content p {
    margin: 0 0 24px;
    padding: 0;
    font-weight: 400;
    text-align: justify;
}
.blog-content > p:first-of-type {
    font-size: 18.5px;
    line-height: 1.85;
    color: #3b4a4c;
}
.blog-content li > p {
    margin-bottom: 6px;
    text-align: left;
}
.blog-content div {
    margin: 0 0 24px;
}

/* One typeface everywhere inside article content */
.blog-content,
.blog-content * {
    font-family: lato, Arial, Helvetica, sans-serif;
}

/* Headings */
.blog-content h2,
.blog-content h3,
.blog-content h4,
.blog-content h5,
.blog-content h6 {
    font-family: Prata, Georgia, serif;
    color: #123f3c;
    font-weight: 400;
    line-height: 1.45;
    margin: 40px 0 18px;
    padding: 0;
    text-align: left;
}
.blog-content h2 {
    font-size: 22px;
    padding-bottom: 12px;
    border-bottom: 2px solid rgba(48, 116, 112, .16);
}
.blog-content h3 {
    font-size: 22px;
    position: relative;
    padding-left: 18px;
}
.blog-content h3::before {
    content: "";
    position: absolute;
    left: 0;
    top: .3em;
    bottom: .2em;
    width: 5px;
    border-radius: 4px;
    background: linear-gradient(180deg, #3B9188, #4FA99F);
}
.blog-content h4,
.blog-content h5,
.blog-content h6 {
    font-size: 19px;
    color: #1c5a55;
    margin: 30px 0 14px;
}

/* Standalone bold lines kept as block headings */
.blog-content > b,
.blog-content > strong {
    display: block;
    margin: 34px 0 16px;
    font-family: Prata, Georgia, serif;
    font-size: 21px;
    line-height: 1.5;
    color: #123f3c;
}
.blog-content b,
.blog-content strong { color: #143c3a; }
.blog-content i,
.blog-content em { font-style: italic; }

/* Consecutive single-line headings merged into a compact list */
.blog-content ul.bd-heading-list {
    margin: 6px 0 32px;
    padding-left: 0;
    list-style: none;
}
.blog-content ul.bd-heading-list > li {
    padding: 13px 16px 13px 46px;
    margin: 0 0 10px;
    background: linear-gradient(135deg, rgba(59, 145, 136, .08), rgba(79, 169, 159, .06));
    border: 1px solid rgba(59, 145, 136, .18);
    border-radius: 14px;
    font-family: Prata, Georgia, serif;
    font-size: 18px;
    line-height: 1.5;
    color: #123f3c;
    position: relative;
}
.blog-content ul.bd-heading-list > li::before {
    content: '';
    position: absolute;
    left: 20px;
    top: 50%;
    transform: translateY(-50%);
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: linear-gradient(135deg, #3B9188, #4FA99F);
}
.blog-content ul.bd-heading-list > li a { font-family: inherit; }

/* Points introduced by a list lead line ("...:-") rendered as list items */
.blog-content ul.bd-steps {
    margin: 4px 0 28px;
    padding-left: 0;
    list-style: none;
}
.blog-content ul.bd-steps > li {
    padding: 10px 16px 10px 40px;
    margin: 0 0 10px;
    background: rgba(123, 104, 238, .05);
    border: 1px solid rgba(123, 104, 238, .16);
    border-radius: 12px;
    font-size: 17px;
    line-height: 1.6;
    color: #2c2340;
    position: relative;
}
.blog-content ul.bd-steps > li::before {
    content: '';
    position: absolute;
    left: 18px;
    top: 50%;
    transform: translateY(-50%);
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background: linear-gradient(135deg, #7B68EE, #9B85FF);
}
.blog-content ul.bd-steps > li strong { font-family: Prata, Georgia, serif; color: #123f3c; }
.blog-content ul.bd-steps > li a { font-family: inherit; }

/* Structured diet plan: meal-time label + food items in each row */
.blog-content .bd-meal-wrap {
    overflow-x: auto;
    margin: 8px 0 32px;
    border: 1px solid rgba(59, 145, 136, .18);
    border-radius: 14px;
    background: #fff;
}
.blog-content table.bd-meal-plan {
    width: 100%;
    border-collapse: collapse;
    margin: 0;
}
.blog-content table.bd-meal-plan th.bd-meal-time {
    text-align: left;
    vertical-align: top;
    width: 34%;
    min-width: 150px;
    padding: 14px 16px;
    background: linear-gradient(135deg, rgba(59, 145, 136, .12), rgba(79, 169, 159, .08));
    font-family: Prata, Georgia, serif;
    font-size: 16px;
    font-weight: normal;
    color: #123f3c;
    border-bottom: 1px solid rgba(59, 145, 136, .14);
    border-right: 1px dashed rgba(59, 145, 136, .22);
    white-space: nowrap;
}
.blog-content table.bd-meal-plan td.bd-meal-items {
    padding: 10px 16px;
    border-bottom: 1px solid rgba(59, 145, 136, .14);
}
.blog-content table.bd-meal-plan tr:last-child th.bd-meal-time,
.blog-content table.bd-meal-plan tr:last-child td.bd-meal-items { border-bottom: 0; }
.blog-content table.bd-meal-plan td.bd-meal-items ul {
    margin: 0;
    padding: 0;
    list-style: none;
}
.blog-content table.bd-meal-plan td.bd-meal-items li {
    position: relative;
    padding: 7px 0 7px 22px;
    font-size: 16px;
    line-height: 1.5;
    color: #2c2340;
}
.blog-content table.bd-meal-plan td.bd-meal-items li::before {
    content: '';
    position: absolute;
    left: 2px;
    top: 50%;
    transform: translateY(-50%);
    width: 9px;
    height: 9px;
    border-radius: 50%;
    background: linear-gradient(135deg, #3B9188, #4FA99F);
}

/* Images: contained size (never oversized), rounded, soft shadow, click to zoom */
.blog-content img,
.blog-content .post-img {
    display: block;
    width: auto;
    max-width: 100%;
    height: auto;
    max-height: 460px;
    margin: 30px auto;
    padding: 0;
    border: 0;
    border-radius: 16px;
    box-shadow: 0 18px 36px -20px rgba(6, 44, 41, .5);
    cursor: zoom-in;
}

/* Re-attached videos (hybrid render): responsive, centered, rounded */
.blog-content iframe,
.blog-content video {
    display: block;
    width: 100%;
    max-width: 640px;
    height: auto;
    aspect-ratio: 16 / 9;
    margin: 28px auto;
    border: 0;
    border-radius: 14px;
    box-shadow: 0 18px 36px -20px rgba(6, 44, 41, .5);
}

/* Bullet lists */
.blog-content ul,
.blog-content ol {
    margin: 6px 0 26px;
    font-weight: 400;
}
.blog-content ul {
    list-style: none;
    padding-left: 4px;
}
.blog-content ul > li {
    position: relative;
    margin-bottom: 14px;
    padding-left: 32px;
    line-height: 1.85;
    text-align: left;
}
.blog-content ul > li::before {
    content: "";
    position: absolute;
    left: 4px;
    top: .72em;
    width: 9px;
    height: 9px;
    border-radius: 50%;
    background: linear-gradient(135deg, #3B9188, #4FA99F);
    box-shadow: 0 0 0 4px rgba(59, 145, 136, .14);
}

/* Numbered lists: gradient number badges */
.blog-content ol {
    list-style: none;
    counter-reset: bd;
    padding-left: 0;
}
.blog-content ol > li {
    counter-increment: bd;
    position: relative;
    margin-bottom: 16px;
    padding-left: 48px;
    min-height: 32px;
    line-height: 1.85;
    text-align: left;
}
.blog-content ol > li::before {
    content: counter(bd);
    position: absolute;
    left: 0;
    top: .12em;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: linear-gradient(135deg, #3B9188, #4FA99F);
    color: #fff;
    font-family: lato, Arial, sans-serif;
    font-size: 15px;
    font-weight: 700;
    line-height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 6px 16px -6px rgba(59, 145, 136, .65), inset 0 1px 0 rgba(255, 255, 255, .25);
}
.blog-content li ul,
.blog-content li ol {
    margin-top: 14px;
    margin-bottom: 4px;
}
.blog-content li ul { padding-left: 24px; }

/* Links */
.blog-content a {
    color: #307470;
    font-weight: 700;
    text-decoration: underline;
    text-underline-offset: 3px;
    text-decoration-thickness: 1.5px;
    text-decoration-color: rgba(48, 116, 112, .45);
}
.blog-content a:hover {
    color: #123f3c;
    text-decoration-color: #123f3c;
}

/* Pull quotes */
.blog-content blockquote {
    position: relative;
    margin: 30px 0;
    padding: 24px 28px 24px 58px;
    background: linear-gradient(135deg, #f3faf8, #e9f5f2);
    border: 1px solid rgba(48, 116, 112, .14);
    border-left: 4px solid #307470;
    border-radius: 0 16px 16px 0;
    font-style: italic;
    color: #2f4a48;
}
.blog-content blockquote::before {
    content: "\201C";
    position: absolute;
    left: 18px;
    top: 4px;
    font-family: Prata, Georgia, serif;
    font-size: 52px;
    line-height: 1;
    color: rgba(48, 116, 112, .35);
}

/* Tables */
.blog-content .bd-table-wrap {
    overflow-x: auto;
    margin: 26px 0;
    border: 1px solid #dfebe9;
    border-radius: 14px;
    -webkit-overflow-scrolling: touch;
}
.blog-content table {
    width: 100%;
    border-collapse: collapse;
    font-size: 14px;
    background: #fff;
}
.blog-content th,
.blog-content td {
    border: 1px solid #e4eeed;
    padding: 8px 12px;
    text-align: left;
    vertical-align: top;
    line-height: 1.6;
}
.blog-content td h3,
.blog-content td h4,
.blog-content td h5,
.blog-content td h6,
.blog-content th h3,
.blog-content th h4,
.blog-content th h5,
.blog-content th h6 {
    font-family: lato, Arial, Helvetica, sans-serif;
    font-size: 14px;
    font-weight: 700;
    margin: 0;
    border: 0;
    padding: 0;
    color: inherit;
}
.blog-content td p,
.blog-content th p {
    margin: 0;
    font-size: inherit;
}
/* First row acts as the header on Blogger tables (they rarely use <th>) */
.blog-content th {
    background: linear-gradient(135deg, #3B9188, #4FA99F);
    color: #fff;
    font-weight: 700;
}
.blog-content tr:nth-child(even) td { background: #f7faf9; }
.blog-content tr:first-child td { background: linear-gradient(135deg, #3B9188, #4FA99F); }

/* Decorative divider */
.blog-content hr {
    border: 0;
    height: 3px;
    width: 110px;
    margin: 36px auto;
    border-radius: 3px;
    background: linear-gradient(90deg, #307470, rgba(48, 116, 112, 0));
}

@media (max-width: 767px) {
    .blog-content {
        font-size: 16px;
        line-height: 1.85;
        padding: 26px 18px;
        border-radius: 14px;
    }
    .blog-content img,
    .blog-content .post-img { max-height: 320px; }
    .blog-content p { text-align: left; }
    .blog-content > p:first-of-type { font-size: 17px; }
   
    .blog-content h2, .blog-content h3 { font-size: 20px; }
    .blog-content ol > li { padding-left: 48px; }
    .blog-content ol > li::before { width: 32px; height: 32px; font-size: 15px; }
}

.company-info .footer .social-icons li>a{
    width:45px;
    height:45px;
    line-height:45px;
    color:#fff;
}
 .post-writer {

        justify-content: end;

    }

@media (max-width: 767px) {
    .post-writer {
        justify-content: start;
    }
}


.cta-section {
/* Ensure full height of the viewport */
    
    background: url('/images/bite_and_diet-cta.webp') center center no-repeat;
    background-size: cover; /* Ensure the entire image is visible */
   /* background-attachment: fixed;* /
    /* Optional: Makes image stay in place on scroll */
    position: relative;
    width: 100%;
    
        }

        .cta-content h1, .cta-content p {
            color: white;
        }
        .cta-content .btn {
            margin-top: 20px;
        }
        .cta-right {
            height: 100%;
        }
        
        
        .cta-overlay {
    background-color: rgba(0, 0, 0, 0.5);
    height: 100%;
    padding: 110px;
    width: fit-content;
    
    
}


/* Media query for tablet devices (992px and below) */
@media (max-width: 992px) {
    .cta-section {
        background-position: right center; /* Focus on the right side of the image for tablets */
    }
}

@media (max-width: 768px) {
    .cta-section {
        background-position: right center; /* Shift focus to the right side of the image */
    }
    .cta-overlay {
        width: 100%;
        padding: 45px 25px;
    }
}

/* ---------------- Promo / CTA block ---------------- */
.video-promo {
    background: linear-gradient(135deg, #f4faf9 0%, #e8f4f2 100%);
    border: 1px solid rgba(48, 116, 112, 0.2);
    border-radius: 16px;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.06);
    padding: 35px;
    margin: 30px 0 40px;
}

.video-promo .video-promo-brand {
    text-align: center;
    font-size: 16px;
    line-height: 1.8;
    color: #333;
    margin-bottom: 8px;
}

.video-promo .video-promo-brand a {
    color: #307470;
    font-weight: 700;
}

.video-promo .video-promo-join {
    text-align: center;
    font-weight: 700;
    color: #307470;
    margin-bottom: 25px;
}

.video-promo-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 15px;
    margin-bottom: 25px;
}

.video-promo-stat {
    background: #fff;
    border: 1px solid rgba(48, 116, 112, 0.12);
    border-radius: 12px;
    padding: 18px 12px;
    text-align: center;
    box-shadow: 0 4px 14px rgba(0, 0, 0, 0.05);
}

.video-promo-stat .stat-value {
    display: block;
    font-size: 26px;
    font-weight: 700;
    color: #307470;
    line-height: 1.2;
}

.video-promo-stat .stat-label {
    display: block;
    margin-top: 6px;
    font-size: 13px;
    color: #666;
}

.video-promo-cta {
    text-align: center;
}

.video-promo-cta .video-promo-question {
    font-size: 16px;
    color: #444;
    margin-bottom: 18px;
}

.video-promo-cta .video-promo-question strong {
    display: block;
    font-size: 18px;
    color: #307470;
    margin-bottom: 6px;
}

.video-promo-actions {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 12px;
    margin-bottom: 18px;
}

.video-promo-actions .ttm-btn {
    margin: 0;
}

.video-promo-foot {
    margin: 0;
    color: #555;
}

@media (max-width: 576px) {
    .video-promo {
        padding: 22px 18px;
    }
    .video-promo-stat .stat-value {
        font-size: 22px;
    }
}

</style>
<!-- Schema.org JSON-LD -->
<script type="application/ld+json">
<?php
$blogHeadline = trim(html_entity_decode(strip_tags($encodedTitle), ENT_QUOTES, 'UTF-8'));
$blogDesc = trim(html_entity_decode(strip_tags($encodedDescription), ENT_QUOTES, 'UTF-8'));
$blogPublishDate = ($formattedDate && $formattedDate !== 'No date')
    ? date('c', strtotime($formattedDate))
    : date('c');
$blogText = mb_substr(trim(html_entity_decode(strip_tags($content), ENT_QUOTES, 'UTF-8')), 0, 500, 'UTF-8');
$articleJsonLd = [
    "@context" => "https://schema.org",
    "@type" => "Article",
    "url" => "https://www.biteanddiet.in/blog-post/" . $slug,
    "headline" => $blogHeadline,
    "description" => $blogDesc,
    "image" => $encodedImageUrl,
    "datePublished" => $blogPublishDate,
    "dateModified" => $blogPublishDate,
    "author" => [
        "@type" => "Person",
        "name" => $authorName
    ],
    "publisher" => [
        "@type" => "Organization",
        "name" => $publisherName,
        "logo" => [
            "@type" => "ImageObject",
            "url" => "https://www.biteanddiet.in/images/big_logo.png",
            "width" => 250,
            "height" => 60
        ]
    ],
    "mainEntityOfPage" => [
        "@type" => "WebPage",
        "@id" => "https://www.biteanddiet.in/blog-post/" . $slug
    ],
    "articleBody" => $blogText
];
echo json_encode($articleJsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE | JSON_PRETTY_PRINT);
?>
</script>



<?php $disable_header = false; $ogImageSuppressed = true; include 'Header.php'; ?>


<div class="ttm-page-title-row notranslate" translate="no">
    <div class="ttm-bg-layer ttm-page-title-row-bg-layer"></div>
    <div class="container px-2">
        <div class="row">
            <div class="text-center col-md-12">
                <div class="ttm-textcolor-white title-box">
                    <div class="ttm-textcolor-white page-title-heading">
                        <h1 class="title notranslate" translate="no"><?php echo $encodedTitle; ?></h1>
                    </div>
                    <div class="breadcrumb-wrapper notranslate" translate="no">
                        <span><a href="./" title="Homepage"><i class="ti ti-home"></i> Home </a></span>
                        <span class="ttm-bread-sep">: : </span>Blog Posts
                        <span class="ttm-bread-sep">: : </span>
                        <span><span class="ttm-textcolor-skincolor"><?php echo $encodedTitle; ?></span></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<section class="break-991-colum checkout-section clearfix ttm-row">
    <div class="container px-1">
        <header class="text-center section-title notranslate" translate="no">
            <h5>Blog Post</h5>
        </header>

        <div class="row">
            <div class="col-lg-12">
                        <h1 class="custom_heading text-center mb-50 notranslate" translate="no"><?php echo $encodedTitle; ?></h1>

                <!-- Blog content language toggle: English | हिंदी (translates ONLY .blog-content) -->
                <div class="bd-gt-bar" id="bdGtBar">
                    <span class="bd-gt-label">Language</span>
                    <button type="button" class="bd-gt-btn is-active" data-lang="en">English</button>
                    <button type="button" class="bd-gt-btn" data-lang="hi">हिंदी</button>
                </div>
                <div id="google_translate_element"></div>
                <style>
                    #bdGtBar.bd-gt-bar{display:flex;align-items:center;justify-content:center;flex-wrap:wrap;gap:10px;margin:0 0 22px;padding:8px 10px;background:#fff;border:1px solid #e3ece9;border-radius:999px;box-shadow:0 2px 8px rgba(20,60,58,.08);position:relative;z-index:999;}
                    .bd-gt-label{font:14px/1 Lato,Arial,sans-serif;color:#5b7a74;margin-right:2px;}
                    .bd-gt-btn{cursor:pointer;font:600 14px/1.2 Lato,Arial,sans-serif;color:#143c3a;background:#f1f7f5;border:1px solid #d7e6e1;border-radius:999px;padding:9px 20px;transition:background .2s,color .2s,border-color .2s;pointer-events:auto;}
                    .bd-gt-btn:hover{background:#e6f1ed;}
                    .bd-gt-btn.is-active{background:linear-gradient(135deg,#3B9188,#4FA99F);border-color:#3B9188;color:#fff;}
                    .bd-gt-note{display:block;text-align:center;font:12px/1.4 Lato,Arial,sans-serif;color:#a35d3a;margin:-14px 0 22px;}
                    #google_translate_element{position:absolute!important;left:-9999px!important;top:0!important;width:400px;height:40px;overflow:hidden;opacity:0;pointer-events:none;}
                    .goog-te-banner-frame,.goog-te-menu-frame,.goog-te-balloon-frame,.goog-te-gadget-simple,.goog-gt-tt,#goog-gt-tt,.goog-te-spinner-pos{display:none!important;visibility:hidden!important;pointer-events:none!important;}
                    iframe.goog-te-banner-frame,iframe.goog-te-menu-frame,iframe.goog-te-balloon-frame{display:none!important;visibility:hidden!important;}
                    body > div.skiptranslate:not(#google_translate_element){display:none!important;visibility:hidden!important;pointer-events:none!important;}
                    body{top:0!important;margin-top:0!important;}
                    body.translated-ltr,body.translated-rtl,html.translated-ltr,html.translated-rtl{top:0!important;margin-top:0!important;}
                </style>
                <script type="text/javascript">
                function googleTranslateElementInit() {
                  new google.translate.TranslateElement({pageLanguage: 'en'}, 'google_translate_element');
                }
                </script>
                <script type="text/javascript">
                (function(){
                    var pending = null;

                    // Google remembers the last chosen language in a cookie and
                    // auto-translates other blog posts on load. Default: English.
                    var userChosen = false;
                    function clearGtCookie(){
                        var exp = 'expires=Thu, 01 Jan 1970 00:00:00 GMT';
                        document.cookie = 'googtrans=; ' + exp + '; path=/';
                        var parts = location.hostname.split('.');
                        if(parts.length > 1){
                            var root = '.' + parts.slice(-2).join('.');
                            document.cookie = 'googtrans=; ' + exp + '; path=/; domain=' + root;
                        }
                    }
                    function ensureEnglish(){
                        if(userChosen){ return; }
                        var sel = document.querySelector('.goog-te-combo');
                        if(!sel){ return; }
                        if(htmlLang() === 'hi' || sel.value !== ''){
                            sel.value = '';
                            sel.dispatchEvent(new Event('change'));
                        }
                    }
                    clearGtCookie();

                    // Mark everything OUTSIDE .blog-content as notranslate/translate=no so Google
                    // translates only that div. .blog-content and its ancestors must
                    // stay clean, otherwise Google skips the whole subtree.
                    function scope(){
                        var bc = document.querySelector('.blog-content');
                        if(!bc || !document.body){ return; }
                    var keep = [];
                    for(var cur = bc; cur && cur !== document.documentElement; cur = cur.parentNode){
                        if(keep.indexOf(cur) === -1){ keep.push(cur); }
                    }
                    var all = document.body.getElementsByTagName('*');
                    for(var i = 0; i < all.length; i++){
                        var el = all[i];
                        if(el.id === 'google_translate_element'){ continue; }
                        if(el.closest && el.closest('#google_translate_element')){ continue; }
                        if(typeof el.className === 'string' && el.className.indexOf('goog-') !== -1){ continue; }
                        var inArticle = bc.contains(el) || keep.indexOf(el) !== -1;
                            el.classList.toggle('notranslate', !inArticle);
                            if(inArticle){ el.removeAttribute('translate'); }
                            else { el.setAttribute('translate', 'no'); }
                        }
                    }

                    var ORIG_TITLE = document.title;
                    function keepTitle(){ document.title = ORIG_TITLE; }

                    function markActive(lang){
                        var btns = document.querySelectorAll('.bd-gt-btn');
                        for(var i = 0; i < btns.length; i++){
                            btns[i].classList.toggle('is-active', btns[i].getAttribute('data-lang') === lang);
                        }
                    }

                    // Google sets translated-ltr/translated-rtl on <html> when active.
                    function htmlLang(){
                        var e = document.documentElement;
                        if(e.classList.contains('translated-ltr') || e.classList.contains('translated-rtl')){ return 'hi'; }
                        return 'en';
                    }

                    function fire(lang){
                        var sel = document.querySelector('.goog-te-combo');
                        if(!sel){ return false; }
                        if(sel.value !== lang){ sel.value = lang; }
                        sel.dispatchEvent(new Event('change'));
                        return true;
                    }

                    var desired = null, timer = null, age = 0, stable = 0, lastSeen = null;

                    function step(){
                        keepTitle();
                        var cur = htmlLang();
                        stable = (cur === lastSeen) ? stable + 1 : 0;
                        lastSeen = cur;
                        age++;
                        if(cur === desired){
                            if(stable >= 3){ clearInterval(timer); timer = null; }
                            return;
                        }
                        // Didn't take: fire again (mid-flight changes get dropped by Google).
                        if(age === 4 || age === 9){ fire(desired); }
                        if(age > 13){ clearInterval(timer); timer = null; }
                    }

                    function arm(){
                        if(timer){ clearInterval(timer); }
                        age = 0; stable = 0; lastSeen = htmlLang();
                        timer = setInterval(step, 400);
                    }

                    function setLang(lang){
                        desired = lang;
                        userChosen = true;
                        scope();     // fresh marks right before Google runs (covers late-added elements)
                        markActive(lang);
                        fire(lang);   // immediate response
                        keepTitle();  // Google may translate <title> - restore it
                        arm();        // then verify + auto-redo until the page actually matches
                    }

                    document.addEventListener('click', function(e){
                        var t = e.target;
                        while(t && t.nodeType === 1 && !t.classList.contains('bd-gt-btn')){
                            t = t.parentNode;
                        }
                        if(!t || t.nodeType !== 1){ return; }
                        var lang = t.getAttribute('data-lang');
                        if(!lang){ return; }
                        setLang(lang);
                    });

                    scope();
                    if(document.readyState === 'loading'){
                        document.addEventListener('DOMContentLoaded', scope);
                    }
                    var s = 0;
                    var siv = setInterval(function(){
                        scope();
                        ensureEnglish();
                        if(++s >= 12){ clearInterval(siv); }
                    }, 500);
                })();
                </script>
                <script type="text/javascript" src="https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit" async></script>

                <!-- Bootstrap styled content -->
                <div class="blog-content">
                    <?php echo $content; ?>
               
 

 
 
 
 
               

                </div><!-- Share Button -->
<div class="text-center mb-50">
    <button class="ttm-btn ttm-btn-shape-round ttm-btn-size-md ttm-btn-style-fill ttm-btn-bgcolor-skincolor" onclick="sharePost()">Share</button> 
</div>   

<script>
function sharePost() {
    const pageTitle = "<?php echo addslashes($encodedTitle); ?>";
    const pageUrl = window.location.href;
    const fullMessage = `${pageTitle}\n\nRead Now:\n${pageUrl}`;

    if (navigator.share) {
        navigator.share({
            title: pageTitle,
            text: fullMessage
            // url is optional; avoid duplication
        }).then(() => {
            console.log('Thanks for sharing!');
        }).catch(console.error);
    } else {
        // Fallback for browsers that don't support Web Share API
        const message = encodeURIComponent(fullMessage);
        const whatsappUrl = "https://wa.me/?text=" + message;
        window.open(whatsappUrl, '_blank');
    }
}
</script>

<!-- Promo / Appointment Block (same as video pages) -->
<div class="video-promo">
    <p class="video-promo-brand">
        <strong class="red-title">Bite And Diet</strong> by <strong><a href="/aboutdtpriyanka">Dietician Priyanka</a></strong> who has more than 10 years of experience, provides personalized diet plans for various health issues such as weight management, diabetes, high blood pressure, heart disease, cholesterol management, PCOS/PCOD, knee pain, back pain, chronic cough, fatty liver, digestive problems, IBS, and arthritis. She helps clients reduce their dependence on medicine and live a healthier life.
    </p>
    <p class="video-promo-join">Join us on our journey to a happier &amp; healthier life!</p>

    <div class="video-promo-stats">
        <div class="video-promo-stat">
            <span class="stat-value">3000+</span>
            <span class="stat-label">💯 Total Customers</span>
        </div>
        <div class="video-promo-stat">
            <span class="stat-value">2500+</span>
            <span class="stat-label">😃 Satisfied Customers</span>
        </div>
        <div class="video-promo-stat">
            <span class="stat-value">500</span>
            <span class="stat-label">👥 Active Customers</span>
        </div>
        <div class="video-promo-stat">
            <span class="stat-value">⭐ 5 Star</span>
            <span class="stat-label">Rated Services</span>
        </div>
    </div>

    <div class="video-promo-cta">
        <p class="video-promo-question">
            <strong>Let us help you achieve your health goals!</strong>
            Don't wait any longer? Book an appointment now!!
        </p>
        <div class="video-promo-actions">
            <a href="tel:+918826549878" class="ttm-btn ttm-btn-shape-round ttm-btn-size-md ttm-btn-style-fill ttm-btn-bgcolor-skincolor">📞 Call Now</a>
            <a href="https://wa.me/+918826549878" target="_blank" rel="noopener" class="ttm-btn ttm-btn-shape-round ttm-btn-size-md ttm-btn-style-fill ttm-btn-bgcolor-skincolor">💬 WhatsApp Now</a>
            <a href="/form" class="ttm-btn ttm-btn-shape-round ttm-btn-size-md ttm-btn-style-fill ttm-btn-bgcolor-skincolor">🌐 Join Now</a>
        </div>
        <p class="video-promo-foot">and start your journey towards better health!</p>
    </div>
</div>

            </div>
        </div>
    </div>
    
    <div class="container cta-section">
    <div class="row">
        <!-- Left Side with Overlay and Text -->
        <div class="col-md-6 cta-overlay d-flex align-items-center">
            <div class="px-4 cta-content">
                <h1>Are you looking for a diet plan?</h1>
                <p>Join now to get your personalized plan!</p>
                <a href="/form" class="btn btn-primary">Join Now</a>
            </div>
        </div>
        
        <!-- Right Side without Overlay -->
        <div class="col-md-6 cta-right">
            <!-- Empty Div to maintain image's original appearance -->
        </div>
    </div>
</div>

    
<div class="company-info container py-4">
    <div class="row mt-20 align-items-center">
        <!-- Text and Image on the right side for larger screens, first for phones -->
        <div class="col-12 col-md-6 text-left my-4 order-1 order-md-2">
            <div class="post-writer d-flex align-items-center">
                <!-- Image on the left -->
                <div>
                    <img src="/images/dietician_priyanka.webp" class="img-fluid rounded-circle" alt="Dietician Priyanka" style="width: 100px; height: 100px;">
                </div>
                <!-- Text on the right -->
                <div class="ml-3">

                    <p class="text-right mb-0"><span class="font-italic small text-muted">posted on:   </span>&nbsp<?php echo $formattedDate; ?></p>
                                   <p class=""><span class="font-italic small text-muted">by:&nbsp</span><b>Dietician Priyanka</b></p>
                </div>
            </div>
        </div>

        <!-- Social media icons on the left side for larger screens, second for phones -->
        <div class="footer col-12 col-md-6 text-left my-4 order-2 order-md-1">
            <h3 class="mb-3">Follow Us:</h3>
            <div class="social-icons">
                <ul class="list-inline">
                    <li class="list-inline-item social-whatsapp">
                        <a href="https://wa.me/+918826549878" class="tooltip-top" data-tooltip="WhatsApp">
                            <i class="fa fa-whatsapp" aria-hidden="true"></i>
                        </a>
                    </li>
                    <li class="list-inline-item social-facebook">
                        <a href="https://www.facebook.com/YourBiteMyDiet" title="Facebook" target="_blank" class="tooltip-top" data-tooltip="Facebook">
                            <i class="fa fa-facebook" aria-hidden="true"></i>
                        </a>
                    </li>
                    <li class="list-inline-item social-linkedin">
                        <a href="https://www.linkedin.com/company/bite-and-diet-nutritionist-consultation/" title="Linkedin" target="_blank" class="tooltip-top" data-tooltip="Linkedin">
                            <i class="fa fa-linkedin" aria-hidden="true"></i>
                        </a>
                    </li>
                    <li class="list-inline-item social-instagram">
                        <a href="https://www.instagram.com/bite_and_diet/" title="Instagram" target="_blank" class="tooltip-top" data-tooltip="Instagram">
                            <i class="fa fa-instagram" aria-hidden="true"></i>
                        </a>
                    </li>
                    <li class="list-inline-item social-youtube">
                        <a href="https://www.youtube.com/channel/UCwl1Lbkl0PhwYm8j3j8fo1A" title="Youtube" target="_blank" class="tooltip-top" data-tooltip="Youtube">
                            <i class="fa fa-youtube" aria-hidden="true"></i>
                        </a>
                    </li>
                    <li class="list-inline-item social-quora">
                        <a href="https://www.quora.com/profile/BiteandDiet" title="Quora" target="_blank" class="tooltip-top" data-tooltip="Quora">
                            <i class="fa fa-quora" aria-hidden="true"></i>
                        </a>
                    </li>
                    <li class="list-inline-item social-twitter">
                        <a href="https://twitter.com/Biteandiet" title="Twitter" target="_blank" class="tooltip-top" data-tooltip="Twitter">
                            <i class="fa fa-twitter" aria-hidden="true"></i>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <hr class="mx-3">
</div>



<div class="container">
    <div class="clearfix section-title">
        <div class="title-header">
            <h5>Recommended Blog Posts</h5>
        </div>
    </div>
</div>
<div class="container">
    <div class="row" id="recommendations-row">
        <!-- Cards will be appended here by JavaScript -->
    </div>
    <hr>
    <div class="text-center p-4">
        <a href="/blog-posts" class="ttm-btn my-5 ttm-btn-shape-round ttm-btn-size-md ttm-btn-style-fill ml-15 ttm-btn-bgcolor-skincolor">Back to Blog Posts</a>
    </div>
</div>
</section>


<?php
// Fetch the JSON data from the allposts.json file
$jsonFilePath = 'allposts.json';
$jsonData = file_get_contents($jsonFilePath);
$allPosts = json_decode($jsonData, true);

// Function to get random posts
function getRandomPosts($posts, $count = 8) {
    $randomKeys = array_rand($posts, min($count, count($posts)));
    $randomPosts = [];
    foreach ($randomKeys as $key) {
        $randomPosts[] = $posts[$key];
    }
    return $randomPosts;
}

// Get 8 random posts
$recommendations = getRandomPosts($allPosts);

// Encode recommendations as JSON for JavaScript
$recommendationsJson = json_encode($recommendations);
?>

<!-- Include recommendationsJson in the script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const recommendations = <?php echo $recommendationsJson; ?>;
    const FALLBACK_IMG = '<?php echo $BLOG_FALLBACK_IMAGE; ?>';
    const container = document.getElementById('recommendations-row');

    function renderPosts(posts) {
        container.innerHTML = ''; // Clear the container

        posts.forEach((post, index) => {
            const imgUrl = post.firstImgSrc || FALLBACK_IMG;
            // Use the postUrl directly without encoding it
            const postUrl = post.postUrl;
            const colClass = (index < 4) ? 'col-lg-3 col-sm-6' : 'col-lg-3 col-sm-6 col-12'; // Adjust column classes for responsiveness
            const cardDiv = document.createElement('div');
            cardDiv.className = colClass + ' mb-4'; // Add margin-bottom

            cardDiv.innerHTML = `
                <div class='card' style='margin: 0 0 20px 0;'>
                    <img src='${imgUrl}' class='card-img-top' alt='${post.title}' style='object-fit: cover; max-height: 200px; min-height: 200px;'loading="lazy">
                    <div class='card-body text-center'>
                        <h6 class='card-title'>${post.title}</h6>
                        <a href='${post.slug}' class='btn btn-primary'>Know More</a>
                    </div>
                </div>
            `;

            container.appendChild(cardDiv);
        });
    }

    renderPosts(recommendations);
});
</script>

<!-- Modal for full-screen image view --->
<div class="modal fade" id="imageModal" tabindex="-1" role="dialog" aria-labelledby="imageModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-body position-relative">
        <!-- Close button placed on top-left of the image -->
        <button type="button" class="close position-absolute" style="top: 10px; right: 10px; font-size: 1rem; background-color: black; color: white; width: 25px; height: 25px; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: none; z-index: 1000;" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span> <!-- The "X" button -->
        </button>
        <img src="" id="modalImage" class="img-fluid" alt="Full Image">
      </div>
    </div> 
  </div>
</div> 


<script>
// Restrict the image modal functionality to images within the blog-content
document.addEventListener('DOMContentLoaded', function() {
    // Select only images within the .blog-content container
    document.querySelectorAll('.blog-content img').forEach(img => {
        img.addEventListener('click', function() {
            const imgSrc = this.getAttribute('src');
            document.getElementById('modalImage').setAttribute('src', imgSrc);
            $('#imageModal').modal('show');  // Open the modal
        }); 
    });
});
</script>

<?php include 'footer.php'; ?>

<?php
while (ob_get_level() > 1) {
    ob_end_flush();
}
$pageHtml = ob_get_contents();
ob_end_flush();
flush();

if ($postFound && $pageHtml !== '') {
    $staticDir = __DIR__ . '/data/pages';
    if (!is_dir($staticDir)) {
        @mkdir($staticDir, 0777, true);
    }
    $safeSlug = preg_replace('/[^A-Za-z0-9._-]/', '_', (string)($slug ?? ''));
    if ($safeSlug !== '') {
        @file_put_contents($staticDir . '/' . $safeSlug . '.html', $pageHtml);
    }
}

if ($needsBackgroundRefresh && function_exists('curl_init')) {
    ignore_user_abort(true);
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $refreshUrl = $scheme . '://' . $host . '/blog-post.php?slug=' . rawurlencode((string)$slug) . '&blog_refresh=1';
    $ch = curl_init($refreshUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'BiteAndDiet-BackgroundRefresher');
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_exec($ch);
    curl_close($ch);
}
?>


