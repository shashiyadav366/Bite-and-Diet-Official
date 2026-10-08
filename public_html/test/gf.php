<?php
require_once __DIR__ . '/../app_config.php';
// Instagram API credentials and configuration
$socialAccessToken = cfg('instagram_access_token', '');
$socialUserId = cfg('instagram_user_id', '');
$permalink = 'https://www.instagram.com/p/DAL9tAKyYrB/'; // Replace with the permalink you want to use

// Step 1: Convert permalink to media ID using oEmbed
$oembedUrl = "https://graph.instagram.com/oembed?url=" . urlencode($permalink);
$oembedResponse = file_get_contents($oembedUrl);
$oembedData = json_decode($oembedResponse, true);

if (isset($oembedData['media_id'])) {
    $mediaId = $oembedData['media_id'];

    // Step 2: Fetch media details using the media ID
    $mediaUrl = "https://graph.instagram.com/{$mediaId}?fields=id,media_type,media_url,permalink&access_token={$socialAccessToken}";
    $mediaResponse = file_get_contents($mediaUrl);
    $mediaData = json_decode($mediaResponse, true);

    // Display the latest media URL
    if (isset($mediaData['media_url'])) {
        echo "Latest Media URL: " . $mediaData['media_url'];
    } else {
        echo "Media URL not found.";
    }
} else {
    echo "Unable to retrieve media ID from permalink.";
}
?>
