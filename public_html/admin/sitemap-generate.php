<?php
// ------------------------------------------------------------------
// Admin UI wrapper for on-demand sitemap regeneration.
//
// Kept session-gated because the "Update Sitemap" button in
// choose_action.php injects this response straight into the page.
// All real work lives in sitemap_lib.php so the same builder can be
// triggered by cron without a PHP session.
// ------------------------------------------------------------------

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: ../login.php');
    exit;
}

require_once __DIR__ . '/sitemap_lib.php';

// Force a rebuild: the admin explicitly asked for a fresh sitemap.
$result = sitemap_force_generate();

$class = $result['success'] ? 'alert-success' : 'alert-danger';
echo '<div class="alert ' . $class . '">' . htmlspecialchars($result['message']);
if ($result['success']) {
    echo ' (' . (int) $result['urls'] . ' URLs)';
}
echo '</div>';