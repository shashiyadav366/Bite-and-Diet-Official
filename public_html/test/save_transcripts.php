<?php

function getTranscriptFromYTTS($videoId) {
    $url = "https://youtubetotranscript.net/video?v=" . $videoId;

    // cURL setup
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)', // Simulate real browser
    ]);

    $html = curl_exec($ch);
    curl_close($ch);

    if (!$html || strpos($html, 'accordion-body') === false) {
        return "❌ Transcript not found or blocked.";
    }

    // Load HTML into DOM
    libxml_use_internal_errors(true);
    $dom = new DOMDocument();
    $dom->loadHTML($html);
    libxml_clear_errors();

    $finder = new DOMXPath($dom);
    $nodes = $finder->query("//div[contains(@class, 'accordion-body')]");

    if ($nodes->length === 0) {
        return "❌ Transcript not found in HTML.";
    }

    $transcript = "";
    foreach ($nodes as $node) {
        $transcript .= trim($node->textContent) . "\n\n";
    }

    return trim($transcript);
}

// === MAIN ===
$videoIds = ["wjCxQT3e-YU", "RUvUHZrrFb8", "WAu9HmFdXZc"];

if (!file_exists("transcripts")) {
    mkdir("transcripts", 0777, true);
}

foreach ($videoIds as $videoId) {
    $transcript = getTranscriptFromYTTS($videoId);
    file_put_contents("transcripts/{$videoId}_hi.txt", $transcript);
    echo "✅ Saved Hindi transcript for: $videoId<br>";
}
?>
