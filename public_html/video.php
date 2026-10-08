<?php
session_start();
?>



<?php
// Get the slug from the URL
$slug = isset($_GET['slug']) ? $_GET['slug'] : '';

// JSON files
$cacheFile = 'youtube_videos.json';
$backupFile = 'youtube_videos-backup.json';
$defaultThumbnailUrl = 'https://www.biteanddiet.in/images/youtube-default-thumbnail.webp';
$recommendationLimit = 8;

// Function to check if a file contains valid JSON
function isValidJsonFile($file) {
    if (file_exists($file) && filesize($file) > 0) {
        $fileContents = file_get_contents($file);
        $jsonData = json_decode($fileContents, true);
        return $jsonData !== null ? $jsonData : false;
    }
    return false;
}

// Try to load data from the cache or backup
$videoData = isValidJsonFile($cacheFile) ?: isValidJsonFile($backupFile);
if (!$videoData) {
    header("Location: /error");
    exit();
}

// Remove the promotional boilerplate that YouTube descriptions carry
// (it is also hardcoded below on the page, so it used to show up twice)
// together with hashtags and emoji-only lines.
function cleanVideoDescription($text) {
    $promoMarkers = [
        'At the Bite And Diet',
        'Book an appointment',
        'To know more about us',
        "Let's connect on social media",
        'Your support means everything',
        'Join us on our journey',
    ];

    $cutAt = false;
    foreach ($promoMarkers as $marker) {
        $pos = stripos($text, $marker);
        if ($pos !== false && ($cutAt === false || $pos < $cutAt)) {
            $cutAt = $pos;
        }
    }
    if ($cutAt !== false) {
        $text = substr($text, 0, $cutAt);
    }

    $text = str_replace(["\r\n", "\r"], "\n", $text);
    $withoutHashTags = preg_replace('/(?:^|\s)#[\p{L}\p{N}_]+/u', '', $text);
    $text = $withoutHashTags !== null ? $withoutHashTags : $text;

    $lines = [];
    foreach (explode("\n", $text) as $line) {
        $line = trim(preg_replace('/[ \t]+/', ' ', $line));
        // Keep only lines that carry real words (drops emoji/separator leftovers)
        if ($line !== '' && preg_match('/[\p{L}\p{N}]/u', $line)) {
            $lines[] = $line;
        }
    }

    $text = trim(implode("\n", $lines));
    $collapsed = preg_replace('/\n{3,}/', "\n\n", $text);
    return $collapsed !== null ? $collapsed : $text;
}

// Render the plain-text description as safe HTML paragraphs with clickable links
function descriptionToHtml($text) {
    $html = '';
    foreach (explode("\n", $text) as $line) {
        $line = trim($line);
        if ($line === '') {
            continue;
        }
        $line = htmlspecialchars($line, ENT_QUOTES, 'UTF-8');
        $linked = preg_replace_callback('#https?://[^\s<]+#i', function ($matches) {
            $url = rtrim($matches[0], '.,;:!?)\'"');
            $safeUrl = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
            $trailing = substr($matches[0], strlen($url));
            return '<a href="' . $safeUrl . '" target="_blank" rel="noopener">' . $safeUrl . '</a>' . $trailing;
        }, $line);
        $html .= '<p>' . ($linked !== null ? $linked : $line) . '</p>';
    }
    return $html;
}

// Shorten a string on a word boundary (for meta descriptions)
function excerptText($text, $max = 160) {
    if (mb_strlen($text) <= $max) {
        return $text;
    }
    $cut = mb_substr($text, 0, $max);
    $lastSpace = mb_strrpos($cut, ' ');
    return rtrim($lastSpace ? mb_substr($cut, 0, $lastSpace) : $cut) . '...';
}


// Find video by matching slug
$videoFound = false;
foreach ($videoData as $video) {
    if ($video['slug'] === $slug) {
        $videoId = $video['videoId'];
        $videoSlug = $video['slug'];
        $videoTitleJson = html_entity_decode($video['title'], ENT_QUOTES, 'UTF-8');
        $videoDescription = html_entity_decode($video['description'], ENT_QUOTES, 'UTF-8');
        $videopublishedAt = $video['publishedAt'];
        $dateTime = new DateTime($videopublishedAt);
        $formattedDate = $dateTime->format('F j, Y');
        $videoFound = true;
        break;
    }
}

if (!$videoFound) {
    header("Location: /error");
    exit();
}

// Prepare display content (clean, promo-free description)
$displayContent = cleanVideoDescription($videoDescription);
if ($displayContent === '') {
    $titleWithoutHashTags = trim(preg_replace('/(?:^|\s)#[\p{L}\p{N}_]+/u', '', $videoTitleJson));
    $displayContent = ($titleWithoutHashTags !== '')
        ? $titleWithoutHashTags
        : 'Watch this video by Dietician Priyanka at Bite And Diet.';
}
$descriptionHtml = descriptionToHtml($displayContent);
$metaDescription = excerptText($displayContent);

// Filter out current video from recommendations
$otherVideos = array_filter($videoData, fn($video) => $video['videoId'] !== $videoId);
shuffle($otherVideos);
$recommendations = array_slice($otherVideos, 0, $recommendationLimit);
// Keep only the fields the cards actually use (full descriptions bloat the page)
$recommendations = array_map(fn($video) => [
    'videoId'   => $video['videoId'],
    'slug'      => $video['slug'],
    'title'     => $video['title'],
    'thumbnail' => $video['thumbnail'] ?? '',
], $recommendations);

// Keywords
$descriptionWords = preg_split('/\s+/u', trim($displayContent));
$metaKeywords = $videoTitleJson;
if (!empty($descriptionWords)) {
    $metaKeywords .= ', ' . implode(', ', array_slice($descriptionWords, 0, 12));
}
// Canonical URL
$canonicalUrl = "https://www.biteanddiet.in/video/" . $videoSlug;

$escapedTitle = htmlspecialchars($videoTitleJson, ENT_QUOTES, 'UTF-8');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title><?php echo $escapedTitle; ?></title>
    <meta name="twitter:title" content="<?php echo $escapedTitle; ?>">
    <meta property="og:title" content="<?php echo $escapedTitle; ?>">
    <meta name="description" content="<?php echo htmlspecialchars($metaDescription, ENT_QUOTES, 'UTF-8'); ?>">
    <meta name="keywords" content="<?php echo htmlspecialchars($metaKeywords, ENT_QUOTES, 'UTF-8'); ?>">
    <meta name="robots" content="index, follow">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:description" content="<?php echo htmlspecialchars($metaDescription, ENT_QUOTES, 'UTF-8'); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($metaDescription, ENT_QUOTES, 'UTF-8'); ?>">
    <meta property="og:url" content="<?php echo $canonicalUrl; ?>">

  <link rel="canonical" href="<?php echo $canonicalUrl; ?>">
<!-- JSON-LD for SEO -->
<script type="application/ld+json">
<?php
$videoThumb = 'https://i.ytimg.com/vi/' . $videoId . '/maxresdefault.jpg';
$videoJsonLd = [
    "@context" => "https://schema.org",
    "@type" => "WebPage",
    "url" => $canonicalUrl,
    "name" => $videoTitleJson,
    "description" => $displayContent,
    "thumbnailUrl" => $videoThumb,
    "mainEntity" => [
        "@type" => "ItemList",
        "itemListElement" => [[
            "@type" => "VideoObject",
            "position" => 1,
            "name" => $videoTitleJson,
            "uploadDate" => $videopublishedAt,
            "thumbnailUrl" => $videoThumb,
            "contentUrl" => 'https://www.youtube.com/watch?v=' . $videoId,
            "embedUrl" => 'https://www.youtube.com/embed/' . $videoId,
            "description" => $displayContent,
            "publisher" => [
                "@type" => "Organization",
                "name" => "Bite & Diet - Diet Consultation",
                "logo" => [
                    "@type" => "ImageObject",
                    "url" => "https://www.biteanddiet.in/images/big_logo.png"
                ]
            ]
        ]]
    ]
];
echo json_encode($videoJsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>
</script>

<style>
    .company-info .footer .social-icons li > a {
        width: 45px;
        height: 45px;
        line-height: 45px;
        color: #fff;
    }

    .post-writer {
        justify-content: end;
    }

    @media (max-width: 767px) {
        .post-writer {
            justify-content: start;
        }
    }

    /* ---------------- CTA banner ---------------- */
    .cta-section {
        background: url('/images/bite_and_diet-cta.webp') center center no-repeat;
        background-size: cover;
        position: relative;
        width: 100%;
    }

    .cta-content h1, .cta-content p {
        color: #fff;
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

    @media (max-width: 992px) {
        .cta-section {
            background-position: right center;
        }
    }

    @media (max-width: 768px) {
        .cta-section {
            background-position: right center;
        }
        .cta-overlay {
            width: 100%;
            padding: 45px 25px;
        }
    }

    /* ---------------- Description card ---------------- */
    .video-description {
        background: #fff;
        border: 1px solid rgba(48, 116, 112, 0.18);
        border-radius: 16px;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.06);
        padding: 30px;
        margin: 40px 0 30px;
    }

    .video-description .video-description-title {
        position: relative;
        display: block;
        text-align: center;
        font-size: 22px;
        font-weight: 700;
        color: #307470;
        margin-bottom: 6px;
    }

    .video-description .video-description-title:after {
        content: "";
        display: block;
        width: 60px;
        height: 3px;
        background: #307470;
        border-radius: 3px;
        margin: 12px auto 0;
    }

    .video-description .video-description-body {
        margin-top: 20px;
    }

    .video-description .video-description-body p {
        font-size: 16px;
        line-height: 1.9;
        color: #4a4a4a;
        margin-bottom: 16px;
    }

    .video-description .video-description-body p:last-child {
        margin-bottom: 0;
    }

    .video-description .video-description-body a {
        color: #307470;
        font-weight: 600;
        word-break: break-word;
    }

    .video-description .video-description-body a:hover {
        color: #1f5c58;
    }

    /* ---------------- Promo block ---------------- */
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
        .video-description, .video-promo {
            padding: 22px 18px;
        }
        .video-promo-stat .stat-value {
            font-size: 22px;
        }
    }
</style>
<?php
$disable_header = false;
include 'Header.php';
?>
    <div class="ttm-page-title-row">
        <div class="ttm-bg-layer ttm-page-title-row-bg-layer"></div>
        <div class="container">
            <div class="row">
                <div class="text-center col-md-12">
                    <div class="ttm-textcolor-white title-box">
                        <div class="ttm-textcolor-white page-title-heading">
                            <h1 class="title"><?php echo $escapedTitle; ?></h1>
                        </div>
                        <div class="breadcrumb-wrapper">
                            <span><a href="/" title="Homepage"><i class="ti ti-home"></i> Home </a></span>
                            <span class="ttm-bread-sep">: : </span>Videos
                            <span class="ttm-bread-sep">: : </span>
                            <span><span class="ttm-textcolor-skincolor"><?php echo $escapedTitle; ?></span></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <section class="break-991-colum checkout-section clearfix ttm-row">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 mt-5">

                        <header class="text-center section-title"><h5>Video</h5></header>
                        <h2 class="custom_heading text-center mb-30">
                            <?php echo $escapedTitle; ?>
                        </h2>


<div class="row justify-content-center">
        <div class="col-12 col-md-10">
            <div class="embed-responsive embed-responsive-16by9">
                <iframe class="embed-responsive-item" src="https://www.youtube.com/embed/<?php echo htmlspecialchars($videoId); ?>" title="<?php echo $escapedTitle; ?>" allowfullscreen></iframe>
            </div>
        </div>
    </div>

                        <div class="video-description">
                            <h5 class="video-description-title">Description</h5>
                            <div class="video-description-body">
                                <?php echo $descriptionHtml; ?>
                            </div>
                        </div>

<!-- Share Button -->
<div class="text-center mb-4">
    <button class="ttm-btn ttm-btn-shape-round ttm-btn-size-md ttm-btn-style-fill ttm-btn-bgcolor-skincolor" onclick="shareVideo()">Share</button>
</div>

<!-- Promo / Appointment Block -->
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
            <strong>Let us help you achieve your health goals! </strong>
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

<script>
function shareVideo() {
    const description = <?php echo json_encode($displayContent, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
    const videoTitle = <?php echo json_encode($videoTitleJson, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
    const videoUrl = window.location.href;
    const fullMessage = description + "\n\nWatch To Know More:\n" + videoUrl;

    if (navigator.share) {
        navigator.share({
            title: videoTitle,
            text: fullMessage
            // Do NOT set 'url' here, otherwise URL comes twice
        }).then(() => {
            console.log('Thanks for sharing!');
        })
        .catch(console.error);
    } else {
        // Fallback for browsers that don't support Web Share API
        const message = encodeURIComponent(fullMessage);
        const whatsappUrl = "https://wa.me/?text=" + message;
        window.open(whatsappUrl, '_blank');
    }
}
</script>



                                        </div>
            </div>
        </div>

                    </div>

                <div class="mt-50">
  <div class="container cta-section">
    <div class="row">

      <!-- Left Side with Overlay and Text -->
      <div class="col-md-6 cta-overlay d-flex align-items-center order-2 order-md-1">
        <div class="px-4 cta-content">
          <h1>Are you looking for a diet plan?</h1>
          <p>Join now to get your personalized plan!</p>
          <a href="/form" class="btn btn-primary">Join Now</a>
        </div>
      </div>

      <!-- Right Side without Overlay -->
      <div class="col-md-6 cta-right order-1 order-md-2">
        <!-- Empty Div to maintain image's original appearance -->
      </div>

    </div>
  </div>
</div>


                    <div class="company-info container py-4">

    <div class="row mt-20 align-items-center">

        <!-- Text and Image on the right side for larger screens, first for phones -->
        <div class="col-12 col-md-6 text-left my-4 order-1 order-lg-2">
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
        <div class="footer col-12 col-md-6 text-left my-4 order-2 order-lg-1">
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





<!-- Recommendations Section -->
<div class="container">
    <div class="clearfix section-title">
        <div class="title-header">
            <h5>Recommended Videos</h5>
        </div>
    </div>

    <div class="row" id="recommendations-row">
        <!-- Cards will be appended here by JavaScript -->
    </div>
    <hr>
    <div class="text-center p-4">
        <a href="/videos" class="ttm-btn my-5 ttm-btn-shape-round ttm-btn-size-md ttm-btn-style-fill ml-15 ttm-btn-bgcolor-skincolor">Back to Videos</a>
    </div>
</div>
    </section>


<script>
    document.addEventListener('DOMContentLoaded', function () {
        const recommendations = <?php echo json_encode($recommendations); ?>;
        const fallbackThumbnail = <?php echo json_encode($defaultThumbnailUrl); ?>;
        const container = document.getElementById('recommendations-row');

        function esc(value) {
            return String(value == null ? '' : value)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }

        (recommendations || []).forEach(video => {
            if (!video.videoId) {
                return;
            }

            const thumbnailUrl = video.thumbnail || fallbackThumbnail;
            const cardDiv = document.createElement('div');
            cardDiv.className = 'col-lg-3 col-md-4 col-sm-6 mb-4';

            cardDiv.innerHTML = `
                <div class="card h-100" style="margin: 0 0 20px 0;">
                    <a href="/video/${esc(video.slug)}" class="video-thumbnail">
                        <img src="${esc(thumbnailUrl)}" class="card-img-top" alt="${esc(video.title)}" loading="lazy" style="object-fit: cover;">
                        <div class="overlay"></div>
                        <div class="play-button"></div>
                    </a>
                    <div class="card-body">
                        <h6 class="card-title">${esc(video.title)}</h6>
                    </div>
                </div>
            `;

            container.appendChild(cardDiv);
        });
    });
</script>

<?php include 'footer.php'; ?>
