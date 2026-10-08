<?php
// Social posts cache refresher.
// The public social feed reads social-posts.json (this folder's sibling in the
// site root). Old code pointed at a non-existent insta_posts.json in root, so
// the button did nothing. This keeps the live feed intact and syncs the backup.

$cacheFile = __DIR__ . '/../social-posts.json';
$backupFile = __DIR__ . '/../social-posts-backup.json';

if (file_exists($cacheFile) && filesize($cacheFile) > 0) {
    $data = json_decode(file_get_contents($cacheFile), true);
    if ($data !== null) {
        if (copy($cacheFile, $backupFile)) {
            echo 'Social posts backup refreshed (' . count($data) . ' posts). Live feed is intact. Add new posts from the "Add Social/Health Post" page.';
        } else {
            echo 'Live social feed is intact, but the backup could not be refreshed.';
        }
    } else {
        echo 'Error: Live social feed contains invalid JSON.';
    }
} else {
    if (file_exists($backupFile)) {
        if (copy($backupFile, $cacheFile)) {
            // Restoring the live feed is a content change, so refresh the sitemap.
            require_once __DIR__ . '/sitemap_lib.php';
            sitemap_generate_if_stale();
            echo 'Live social feed was missing and has been restored from the backup.';
        } else {
            echo 'Error: Unable to restore social feed from backup.';
        }
    } else {
        echo 'No social feed or backup found. Add posts from the "Add Social/Health Post" page.';
    }
}