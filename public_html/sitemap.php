<?php
// Handle search redirect BEFORE any output
if (isset($_GET['q'])) {
    $searchTerm = strtolower(trim($_GET['q']));

    if (!empty($searchTerm)) {

        // Clean input → SEO slug
        $searchTerm = str_replace('.', '', $searchTerm);
        $searchTerm = preg_replace('/\s+/', '-', $searchTerm);
        $searchTerm = preg_replace('/[^a-z0-9\-]/', '', $searchTerm);
        $cleanTerm  = trim($searchTerm, '-');

        if (!empty($cleanTerm)) {
            header("Location: https://www.biteanddiet.in/search/" . urlencode($cleanTerm));
            exit;
        }
    }
}
?>
<?php include 'Header.php'; ?>
<div class="ttm-page-title-row"><div class="ttm-bg-layer ttm-page-title-row-bg-layer"></div><div class="container"><div class="row"><div class="text-center col-md-12"><div class="ttm-textcolor-white title-box"><div class="ttm-textcolor-white page-title-heading"><h1 class="title">Sitemap</h1></div><div class="breadcrumb-wrapper"><span><a href="./"title="Homepage"><i class="ti ti-home"></i> Home </a></span><span class="ttm-bread-sep">: : </span><span><span class="ttm-textcolor-skincolor">Sitemap</span></span></div></div></div></div></div></div>

<section class="break-991-colum checkout-section clearfix ttm-row">
<div class="container"><div class="row"><div class="col-lg-12"><div class="container">

<h1 class="text-center py-5">Sitemap</h1>
<p>Welcome to the Bite and Diet sitemap. Below is a comprehensive list of all our pages, categorized for easy navigation.</p>



<!-- Search Form -->
<div class="container">
    <div class="row my-5 justify-content-center text-center">
        <form method="get" action="" class="d-flex"> 
            <input 
                type="text" 
                name="q" 
                placeholder="Search (e.g., weight loss)" 
                class="form-control me-2"
            >
            <button type="submit" class="btn btn-primary">Filter</button>
        </form>
    </div>
</div>

<!-- Main Pages -->
<?php
$mainPages = json_decode(file_get_contents('main-pages.json'), true)['main_pages'] ?? [];

if (!empty($mainPages)) {
    echo '<h2>Main Pages</h2><ul class="ttm-list ttm-list-style-icon">';
    foreach ($mainPages as $page) {
        echo '<li><i class="fa fa-arrow-circle-right ttm-textcolor-skincolor px-2"></i>';
        echo '<a class="mx-2" href="' . htmlspecialchars($page['url']) . '">' . htmlspecialchars($page['title']) . '</a>';
        echo '</li>';
    }
    echo '</ul><hr>';
}
?>

<!-- Diet Plans -->
<?php
$dietPlans = json_decode(file_get_contents('diet_plans.json'), true)['diet_plans'] ?? [];

if (!empty($dietPlans)) {
    echo '<h2>Diet Plans Available</h2><ul class="ttm-list ttm-list-style-icon">';
    foreach ($dietPlans as $diet) {
        echo '<li><i class="fa fa-arrow-circle-right ttm-textcolor-skincolor px-2"></i>';
        echo '<a class="mx-2" href="/plans-and-packages/' . htmlspecialchars($diet['diet_url']) . '">' . htmlspecialchars($diet['diet_name']) . '</a>';
        echo '</li>';
    }
    echo '</ul><hr>';
}
?>

<!-- Blog Posts -->
<?php
$blogPosts = json_decode(file_get_contents('allposts.json'), true) ?? [];

if (!empty($blogPosts)) {
    echo '<h2>Blog Posts</h2><ul class="ttm-list ttm-list-style-icon">';
    foreach ($blogPosts as $post) {
        echo '<li><i class="fa fa-arrow-circle-right ttm-textcolor-skincolor px-2"></i>';
        echo '<a class="mx-2" href="/blog-post/' . htmlspecialchars($post['slug']) . '">' . htmlspecialchars($post['title']) . '</a>';
        echo '</li>';
    }
    echo '</ul><hr>';
}
?>

<!-- Videos -->
<?php
$videos = json_decode(file_get_contents('youtube_videos.json'), true) ?? [];

if (!empty($videos)) {
    echo '<h2>Our Videos</h2><ul class="ttm-list ttm-list-style-icon">';
    foreach ($videos as $video) {
        echo '<li><i class="fa fa-arrow-circle-right ttm-textcolor-skincolor px-2"></i>';
        echo '<a class="mx-2" href="https://youtu.be/' . htmlspecialchars($video['videoId']) . '">' . html_entity_decode($video['title'], ENT_QUOTES, 'UTF-8') . '</a>';
        echo '</li>';
    }
    echo '</ul><hr>';
}
?>

<!-- Success Stories -->
<?php
$successStories = json_decode(file_get_contents('success-stories.json'), true) ?? [];

if (!empty($successStories)) {
    echo '<h2>Success Stories</h2><ul class="ttm-list ttm-list-style-icon">';
    foreach ($successStories as $story) {
        echo '<li><i class="fa fa-arrow-circle-right ttm-textcolor-skincolor px-2"></i>';
        echo '<a class="mx-2" href="' . htmlspecialchars($story['url']) . '">' . htmlspecialchars($story['title']) . '</a>';
        echo '</li>';
    }
    echo '</ul><hr>';
}
?>


<!-- Health Tips -->
<?php
$health_tips_file = 'health-tips.json';
$health_backup_file = 'health-tips-backup.json';

function isValidHealthJsonFile($file) {
    if (file_exists($file) && filesize($file) > 0) {
        $fileContents = file_get_contents($file);
        $jsonData = json_decode($fileContents, true);
        return $jsonData !== null ? $jsonData : false;
    }
    return false;
}

// Remove emojis & non-ASCII
function removeEmojis($text) {
    return preg_replace('/[^\x20-\x7E]/', '', $text);
}

// Limit words
function limitWords($text, $wordLimit = 20) {
    $words = preg_split('/\s+/', trim($text));
    return (count($words) > $wordLimit) ? implode(' ', array_slice($words, 0, $wordLimit)) . '...' : $text;
}

// Load health tips data
$healthTips = isValidHealthJsonFile($health_tips_file) ?: isValidHealthJsonFile($health_backup_file);

if (!empty($healthTips)) {
    echo '<h2>Health Tips</h2><ul class="ttm-list ttm-list-style-icon">';
    $uniquePosts = [];
    foreach ($healthTips as $post) {
        $postId = $post['id'] ?? null;
        $slug   = trim($post['slug'] ?? '', ' /'); // Clean slug
        $title  = isset($post['title']) ? removeEmojis($post['title']) : '';

        // Limit title for UI
        $limitedTitle = limitWords($title, 15);

        // Avoid duplicates and ensure valid slug
        if ($postId && $slug && !in_array($postId, $uniquePosts)) {
            $uniquePosts[] = $postId;
            echo '<li><i class="fa fa-arrow-circle-right ttm-textcolor-skincolor px-2"></i>';
            echo '<a class="mx-2" href="/health-tips/' . urlencode($slug) . '">' . $limitedTitle . '</a></li>';
        }
    }
    echo '</ul><hr>';
}
?>


<!-- Social Sites -->
<h2>Social Sites</h2>
<?php
$socialSites = json_decode(file_get_contents('social-sites.json'), true)['social_sites'] ?? [];
?>
<ul class="ttm-list ttm-list-style-icon social-posts">
    <?php foreach ($socialSites as $site) : ?>
        <li><i class="fa fa-arrow-circle-right ttm-textcolor-skincolor px-2"></i>
            <a class="mx-2" href="<?= htmlspecialchars($site['url']) ?>"><?= htmlspecialchars($site['name']) ?></a>
        </li>
    <?php endforeach; ?>
</ul>

<hr>
<div id="month-year"></div>

<script>
const currentDate = new Date();
const monthNames = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
document.getElementById("month-year").innerHTML = `${monthNames[currentDate.getMonth()]}, ${currentDate.getFullYear()}`;
</script>

</div></div></div></div></section>

  
    
    <?php include 'footer.php'; ?>