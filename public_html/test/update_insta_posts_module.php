<?php
require_once __DIR__ . '/../app_config.php';
$accessToken = cfg('instagram_access_token', '');
$userId = cfg('instagram_user_id', ''); // You can get this from the API

// Function to fetch all posts recursively
function fetchAllInstagramPosts($accessToken, $userId) {
    $allPosts = [];
    $url = "https://graph.instagram.com/{$userId}/media?fields=id,caption,media_type,media_url,permalink,thumbnail_url,timestamp&access_token={$accessToken}&limit=100"; // Start fetching posts

    do {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($response, true);

        if (isset($data['data'])) {
            $allPosts = array_merge($allPosts, $data['data']); // Append new posts to allPosts array
        } else {
            echo "Failed to fetch posts or no posts available.";
            break;
        }

        // Check if there's a next page
        $url = isset($data['paging']['next']) ? $data['paging']['next'] : null;
    } while ($url); // Loop until there's no next page

    return $allPosts;
}

// Fetch all posts
$posts = fetchAllInstagramPosts($accessToken, $userId);

// Save posts to insta_posts.json
if (!empty($posts)) {
    file_put_contents('insta_posts.json', json_encode($posts, JSON_PRETTY_PRINT));
    echo "All Instagram posts have been saved to insta_posts.json.";
} else {
    echo "No posts found.";
}
?>
