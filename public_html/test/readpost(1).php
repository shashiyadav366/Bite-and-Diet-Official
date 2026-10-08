<?php
require_once __DIR__ . '/../app_config.php';
// Get slug from URL
$slug = strtolower(trim($_GET['slug'] ?? ''));

// API setup
$apiKey = cfg('google_api_key', '');
$blogId = cfg('blogger_blog_id', '');
$maxResults = 100;
$startIndex = 1;
$allPosts = [];
$matchedPost = null;

// Slug generator function
function generateSlug($string) {
    $slug = strtolower(trim($string));
    $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
    $slug = preg_replace('/[\s-]+/', '-', $slug);
    return $slug;
}

// Fetch from Blogger API in pages (up to maxResults at a time)
do {
    $apiUrl = "https://www.googleapis.com/blogger/v3/blogs/{$blogId}/posts?maxResults={$maxResults}&startIndex={$startIndex}&key={$apiKey}";
    $response = @file_get_contents($apiUrl);
    
    if (!$response) {
        echo "<h2>Error fetching data from Blogger API</h2>";
        exit;
    }

    $data = json_decode($response, true);
    if (!isset($data['items'])) break;

    foreach ($data['items'] as $post) {
        $postSlug = generateSlug($post['title']);
        
        if ($postSlug === $slug) {
            $matchedPost = $post;
            break 2; // Stop search if match found
        }
    }

    $startIndex += $maxResults;
} while (isset($data['items']) && count($data['items']) === $maxResults);

// Show 404 if no match
if (!$matchedPost) {
    http_response_code(404);
    echo "<h1>404 - Blog post not found</h1>";
    exit;
}

// If match found, display post
$title = htmlspecialchars($matchedPost['title']);
$content = $matchedPost['content'];
$published = date('F j, Y', strtotime($matchedPost['published']));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title><?php echo $title; ?> | Bite and Diet</title>
    <meta name="description" content="Read the latest blog on health, diet and nutrition.">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Add your CSS here -->
</head>
<body>
    <h1><?php echo $title; ?></h1>
    <p><small>Published on <?php echo $published; ?></small></p>
    <div><?php echo $content; ?></div>
</body>
</html>
