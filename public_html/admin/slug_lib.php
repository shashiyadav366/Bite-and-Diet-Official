<?php

// Shared slug helpers.
// Extracted verbatim from blog_sync.php so video slugs stay byte-identical.
// Changing the algorithm here would silently alter existing /video/ URLs.

if (!function_exists('devanagariToLatin')) {
    function devanagariToLatin($text) {
        // Bail out quickly when the string contains no Devanagari.
        if (!preg_match('/[\x{0900}-\x{097F}]/u', $text)) {
            return $text;
        }

        // Devanagari block (U+0900 - U+097F) => ASCII (ITRANS-style).
        static $map = [
            "\xE0\xA4\x81" => 'n',  // à¤  candrabindu
            "\xE0\xA4\x82" => 'm',  // à¤‚  anusvara
            "\xE0\xA4\x83" => 'h',  // à¤ƒ  visarga
            "\xE0\xA4\x85" => 'a',  // à¤…
            "\xE0\xA4\x86" => 'a',  // à¤†
            "\xE0\xA4\x87" => 'i',  // à¤‡
            "\xE0\xA4\x88" => 'i',  // à¤ˆ
            "\xE0\xA4\x89" => 'u',  // à¤‰
            "\xE0\xA4\x8A" => 'u',  // à¤Š
            "\xE0\xA4\x8B" => 'ri', // à¤‹
            "\xE0\xA4\x8E" => 'e',  // à¤Ž
            "\xE0\xA4\x8F" => 'e',  // à¤
            "\xE0\xA4\x90" => 'ai', // à¤
            "\xE0\xA4\x91" => 'o',  // à¤‘
            "\xE0\xA4\x92" => 'o',  // à¤’
            "\xE0\xA4\x93" => 'o',  // à¤“
            "\xE0\xA4\x94" => 'au', // à¤”
            "\xE0\xA4\x95" => 'k',  // à¤•
            "\xE0\xA4\x96" => 'kh', // à¤–
            "\xE0\xA4\x97" => 'g',  // à¤—
            "\xE0\xA4\x98" => 'gh', // à¤˜
            "\xE0\xA4\x99" => 'n',  // à¤™
            "\xE0\xA4\x9A" => 'c',  // à¤š
            "\xE0\xA4\x9B" => 'ch', // à¤›
            "\xE0\xA4\x9C" => 'j',  // à¤œ
            "\xE0\xA4\x9D" => 'jh', // à¤
            "\xE0\xA4\x9E" => 'n',  // à¤ž
            "\xE0\xA4\x9F" => 't',  // à¤Ÿ
            "\xE0\xA4\xA0" => 'th', // à¤ 
            "\xE0\xA4\xA1" => 'd',  // à¤¡
            "\xE0\xA4\xA2" => 'dh', // à¤¢
            "\xE0\xA4\xA3" => 'n',  // à¤£
            "\xE0\xA4\xA4" => 't',  // à¤¤
            "\xE0\xA4\xA5" => 'th', // à¤¥
            "\xE0\xA4\xA6" => 'd',  // à¤¦
            "\xE0\xA4\xA7" => 'dh', // à¤§
            "\xE0\xA4\xA8" => 'n',  // à¤¨
            "\xE0\xA4\xA9" => 'n',  // à¤©
            "\xE0\xA4\xAA" => 'p',  // à¤ª
            "\xE0\xA4\xAB" => 'ph', // à¤«
            "\xE0\xA4\xAC" => 'b',  // à¤¬
            "\xE0\xA4\xAD" => 'bh', // à¤­
            "\xE0\xA4\xAE" => 'm',  // à¤®
            "\xE0\xA4\xAF" => 'y',  // à¤¯
            "\xE0\xA4\xB0" => 'r',  // à¤°
            "\xE0\xA4\xB1" => 'r',  // à¤±
            "\xE0\xA4\xB2" => 'l',  // à¤²
            "\xE0\xA4\xB3" => 'l',  // à¤³
            "\xE0\xA4\xB4" => 'l',  // à¤´
            "\xE0\xA4\xB5" => 'v',  // à¤µ
            "\xE0\xA4\xB6" => 's',  // à¤¶
            "\xE0\xA4\xB7" => 's',  // à¤·
            "\xE0\xA4\xB8" => 's',  // à¤¸
            "\xE0\xA4\xB9" => 'h',  // à¤¹
            "\xE0\xA4\xBC" => '',   // nukta (handled below)
            "\xE0\xA4\xBE" => 'a',  // à¤¾
            "\xE0\xA4\xBF" => 'i',  // à¤¿
            "\xE0\xA5\x80" => 'i',  // à¥€
            "\xE0\xA5\x81" => 'u',  // à¥
            "\xE0\xA5\x82" => 'u',  // à¥‚
            "\xE0\xA5\x83" => 'ri', // à¥ƒ
            "\xE0\xA5\x84" => 'ri', // à¥„
            "\xE0\xA5\x85" => 'e',  // à¥…
            "\xE0\xA5\x87" => 'e',  // à¥‡
            "\xE0\xA5\x88" => 'ai', // à¥ˆ
            "\xE0\xA5\x89" => 'o',  // à¥‰
            "\xE0\xA5\x8B" => 'o',  // à¥‹
            "\xE0\xA5\x8C" => 'au', // à¥Œ
            "\xE0\xA5\x8D" => '',   // à¥  virama (halant) - drop
            "\xE0\xA5\x98" => 'k',  // à¤•à¤¼
            "\xE0\xA5\x99" => 'k',  // à¤–à¤¼
            "\xE0\xA5\x9A" => 'g',  // à¤—à¤¼
            "\xE0\xA5\x9B" => 'z',  // à¤œà¤¼
            "\xE0\xA5\x9C" => 'r',  // à¤¡à¤¼
            "\xE0\xA5\x9D" => 'r',  // à¤¢à¤¼
            "\xE0\xA5\x9E" => 'f',  // à¤«à¤¼
            "\xE0\xA5\xA6" => '0',  // à¥¦
            "\xE0\xA5\xA7" => '1',  // à¥§
            "\xE0\xA5\xA8" => '2',  // à¥¨
            "\xE0\xA5\xA9" => '3',  // à¥©
            "\xE0\xA5\xAA" => '4',  // à¥ª
            "\xE0\xA5\xAB" => '5',  // à¥«
            "\xE0\xA5\xAC" => '6',  // à¥¬
            "\xE0\xA5\xAD" => '7',  // à¥­
            "\xE0\xA5\xAE" => '8',  // à¥®
            "\xE0\xA5\xAF" => '9',  // à¥¯
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
            "\xE2\x80\x9A" => "'", // â€š
            "\xE2\x80\x9B" => "'", // â€›
            "\xE2\x80\x9C" => '"', // "
            "\xE2\x80\x9D" => '"', // "
            "\xE2\x80\x9E" => '"', // â€ž
            "\xE2\x80\x9F" => '"', // â€Ÿ
            "\xE2\x80\x93" => '-', // â€“
            "\xE2\x80\x94" => '-', // â€”
            "\xE2\x80\xA6" => '...', // â€¦
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
