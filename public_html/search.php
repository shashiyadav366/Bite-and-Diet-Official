<?php
// -------------------- Get Search Term from URL -------------------- //
$requestUri = trim($_SERVER['REQUEST_URI'], '/'); // e.g., search/heart
$parts = explode('/', $requestUri);
$searchTerm = isset($parts[1]) ? strtolower(urldecode($parts[1])) : '';

function matchesSearch($text, $searchTerm) {
    return $searchTerm === '' || stripos($text, $searchTerm) !== false;
}

// Flag to detect if anything found
$foundData = false;
?>
<?php include 'Header.php'; ?>

<!-- PAGE TITLE -->
<div class="ttm-page-title-row">
    <div class="ttm-bg-layer ttm-page-title-row-bg-layer"></div>
    <div class="container">
        <div class="row">
            <div class="text-center col-md-12">
                <div class="ttm-textcolor-white title-box">
                    <div class="ttm-textcolor-white page-title-heading">
                        <h1 class="title"><?= htmlspecialchars(ucwords($searchTerm)) ?></h1>
                    </div>
                    <div class="breadcrumb-wrapper">
                        <span><a href="./" title="Homepage"><i class="ti ti-home"></i> Home </a></span>
                        <span class="ttm-bread-sep">: : </span>
                        <span class="ttm-textcolor-skincolor"><?= htmlspecialchars(ucwords($searchTerm)) ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<section class="break-991-colum checkout-section clearfix ttm-row">
<div class="container"><div class="row"><div class="col-lg-12"><div class="container">

<div class="container">

  <!-- Centered and responsive image -->
  <div class="text-center my-4">
      <img src="https://www.biteanddiet.in/images/share.png" 
           alt="Bite and Diet Share" 
           class="img-fluid mx-auto d-block" 
           style="max-width: 70%; height: auto;">
  </div>

  <p class="mb-30 text-dark text-center">
      All Bite & Diet content related to <?= htmlspecialchars($searchTerm) ?> in one place. 
      <br>Updated <?= date('F d, Y') ?>
  </p>

<!-- ================= MAIN PAGES ================= -->
<?php
$mainPages = json_decode(file_get_contents('main-pages.json'), true)['main_pages'] ?? [];
$found = false;
foreach ($mainPages as $page) {
    if (matchesSearch($page['title'], $searchTerm)) {
        $found = true; $foundData = true;
    }
}
if ($found) {
    echo '<h2>Main Pages</h2><ul class="ttm-list ttm-list-style-icon">';
    foreach ($mainPages as $page) {
        if (matchesSearch($page['title'], $searchTerm)) {
            echo '<li><i class="fa fa-arrow-circle-right ttm-textcolor-skincolor px-2"></i>';
            echo '<a class="mx-2" href="' . htmlspecialchars($page['url']) . '">' . htmlspecialchars($page['title']) . '</a></li>';
        }
    }
    echo '</ul><hr>';
}
?>

<!-- ================= DIET PLANS ================= -->
<?php
$dietPlans = json_decode(file_get_contents('diet_plans.json'), true)['diet_plans'] ?? [];
$found = false;
foreach ($dietPlans as $diet) {
    if (matchesSearch($diet['diet_name'], $searchTerm) || matchesSearch($diet['title'], $searchTerm)) {
        $found = true; $foundData = true;
    }
}
if ($found) {
    echo '<h2>Diet Plans</h2><ul class="ttm-list ttm-list-style-icon">';
    foreach ($dietPlans as $diet) {
        if (matchesSearch($diet['diet_name'], $searchTerm) || matchesSearch($diet['title'], $searchTerm)) {
            echo '<li><i class="fa fa-arrow-circle-right ttm-textcolor-skincolor px-2"></i>';
            echo '<a class="mx-2" href="/plans-and-packages/' . htmlspecialchars($diet['diet_url']) . '">' . htmlspecialchars($diet['diet_name']) . '</a></li>';
        }
    }
    echo '</ul><hr>';
}
?>

<!-- ================= BLOG POSTS ================= -->
<?php
$blogPosts = json_decode(file_get_contents('allposts.json'), true) ?? [];
$found = false;
foreach ($blogPosts as $post) {
    if (matchesSearch($post['title'], $searchTerm)) {
        $found = true; $foundData = true;
    }
}
if ($found) {
    echo '<h2>Blog Posts</h2><ul class="ttm-list ttm-list-style-icon">';
    foreach ($blogPosts as $post) {
        if (matchesSearch($post['title'], $searchTerm)) {
            echo '<li><i class="fa fa-arrow-circle-right ttm-textcolor-skincolor px-2"></i>';
            echo '<a class="mx-2" href="/blog-post/' . htmlspecialchars($post['slug']) . '">' . htmlspecialchars($post['title']) . '</a></li>';
        }
    }
    echo '</ul><hr>';
}
?>

<!-- ================= VIDEOS ================= -->
<?php
$videos = json_decode(file_get_contents('youtube_videos.json'), true) ?? [];
$found = false;
foreach ($videos as $video) {
    if (matchesSearch($video['title'], $searchTerm)) {
        $found = true; $foundData = true;
    }
}
if ($found) {
    echo '<h2>Videos</h2><ul class="ttm-list ttm-list-style-icon">';
    foreach ($videos as $video) {
        if (matchesSearch($video['title'], $searchTerm)) {
            echo '<li><i class="fa fa-arrow-circle-right ttm-textcolor-skincolor px-2"></i>';
            echo '<a class="mx-2" href="https://youtu.be/' . htmlspecialchars($video['videoId']) . '">' . html_entity_decode($video['title'], ENT_QUOTES, 'UTF-8') . '</a></li>';
        }
    }
    echo '</ul><hr>';
}
?>

<!-- ================= SUCCESS STORIES ================= -->
<?php
$successStories = json_decode(file_get_contents('success-stories.json'), true) ?? [];
$found = false;
foreach ($successStories as $story) {
    if (matchesSearch($story['title'], $searchTerm)) {
        $found = true; $foundData = true;
    }
}
if ($found) {
    echo '<h2>Success Stories</h2><ul class="ttm-list ttm-list-style-icon">';
    foreach ($successStories as $story) {
        if (matchesSearch($story['title'], $searchTerm)) {
            echo '<li><i class="fa fa-arrow-circle-right ttm-textcolor-skincolor px-2"></i>';
            echo '<a class="mx-2" href="' . htmlspecialchars($story['url']) . '">' . htmlspecialchars($story['title']) . '</a></li>';
        }
    }
    echo '</ul><hr>';
}
?>

<!-- ================= HEALTH TIPS ================= -->
<?php
$health_tips_file = 'health-tips.json';
$health_backup_file = 'health-tips-backup.json';

// ✅ Use separate variable for GET term (don’t overwrite $searchTerm)
$searchTermGet = strtolower(trim($_GET['term'] ?? ''));

function isValidHealthJsonFile($file) {
    if (file_exists($file) && filesize($file) > 0) {
        $jsonData = json_decode(file_get_contents($file), true);
        return $jsonData !== null ? $jsonData : false;
    }
    return false;
}

function limitWords($text, $wordLimit = 20) {
    $words = preg_split('/\s+/', trim($text));
    return (count($words) > $wordLimit) ? implode(' ', array_slice($words, 0, $wordLimit)) . '...' : $text;
}

function removeEmojis($text) {
    return preg_replace('/[^\x20-\x7E]/', '', $text); // Remove emojis and non-ASCII
}

function matchesSearchTags($tags, $term) {
    foreach ($tags as $tag) {
        if (stripos($tag, $term) !== false) return true;
    }
    return false;
}

// Load health tips data
$healthTips = isValidHealthJsonFile($health_tips_file) ?: isValidHealthJsonFile($health_backup_file);

$foundPosts = [];
if (!empty($healthTips) && $searchTermGet !== '') {
    foreach ($healthTips as $post) {
        if (!empty($post['tag']) && matchesSearchTags($post['tag'], $searchTermGet)) {
            $foundPosts[] = $post;
        }
    }
}

if (!empty($foundPosts)) {
    $foundData = true; // ✅ Mark data as found
    echo '<h2>Health Tips</h2><ul class="ttm-list ttm-list-style-icon">';
    foreach ($foundPosts as $post) {
        $title = isset($post['title']) ? removeEmojis($post['title']) : '';
        $slug = trim($post['slug'] ?? '', ' /');

        if ($slug) {
            $displayTitle = limitWords($title, 15);
            echo '<li><i class="fa fa-arrow-circle-right ttm-textcolor-skincolor px-2"></i>';
            echo '<a class="mx-2" href="/health-tips/' . urlencode($slug) . '">' . htmlspecialchars($displayTitle) . '</a></li>';
        }
    }
    echo '</ul><hr>';
}
?>

<!-- ================= NO DATA CHECK ================= -->
<?php
if (!$foundData) {
    echo '<p class="text-center text-danger mt-5">No Data Found</p>';
}
?>

<?php if (!empty($searchTerm) && $foundData) : ?>
    <!-- Copy + Share Buttons -->
    <div class="row justify-content-center">
        <div class="col-auto">
            <button id="copyButton" class="ttm-btn mb-20 ttm-btn-shape-round ttm-btn-size-md ttm-btn-style-fill ttm-btn-bgcolor-black">Copy</button>
        </div>
        <div class="col-auto">
            <button class="ttm-btn mb-20 ttm-btn-shape-round ttm-btn-size-md ttm-btn-style-fill ttm-btn-bgcolor-skincolor" onclick="sharePost()">Share</button> 
        </div>
    </div>

    <div id="copyAlert" class="alert alert-success d-none" role="alert">
        Data copied to clipboard successfully!
    </div>

<?php
$searchFormatted = ucwords(trim($searchTerm));
$pageUrl = "https://www.biteanddiet.in/search/" . urlencode($searchTerm);

$fullMessage = $searchFormatted . ": Bite & Diet Special\n\n"
             . "Explore all resources curated by Dietician Priyanka.\n"
             . "Visit: " . $pageUrl;
?>
<script>
function sharePost() {
    const fullMessage = `<?php echo addslashes($fullMessage); ?>`;

    if (navigator.share) {
        navigator.share({
            title: "<?php echo addslashes($searchFormatted); ?>",
            text: fullMessage
        }).catch(console.error);
    } else {
        const whatsappUrl = "https://wa.me/?text=" + encodeURIComponent(fullMessage);
        window.open(whatsappUrl, '_blank');
    }
}
</script>
<?php endif; ?>

</div></div></div></div></section>    

<!-- FOOTER BRANDING -->
<p class="text-center small my-4 text-muted">
    Bite & Diet | Curated by Dietician Priyanka | https://www.biteanddiet.in
</p>

<!-- JavaScript for Copy Functionality -->
<script>
    document.getElementById('copyButton')?.addEventListener('click', function () {
        const lists = document.querySelectorAll('ul.ttm-list');
        let copiedData = '';

        lists.forEach(list => {
            const sectionHeader = list.previousElementSibling ? list.previousElementSibling.textContent.trim() : '';
            if (sectionHeader) {
                copiedData += `\n${sectionHeader}:\n`;
            }

            const items = list.querySelectorAll('li');
            items.forEach(item => {
                const title = item.textContent.trim();
                const link = item.querySelector('a') ? item.querySelector('a').href : '';

                if (title && link) {
                    copiedData += `  - ${title}\n    ${link}\n\n`;
                }
            });
        });

        navigator.clipboard.writeText(copiedData).then(() => {
            const alert = document.getElementById('copyAlert');
            alert.classList.remove('d-none');
            setTimeout(() => alert.classList.add('d-none'), 3000);
        }).catch(err => {
            console.error('Failed to copy text: ', err);
        });
    });
</script>

<?php include 'footer.php'; ?>
