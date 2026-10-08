<?php
// Single diet plan page - renders ANY diet plan from diet_plans.json.
//
// Canonical URL:
// /plans-and-packages/<diet-url>
//
// Legacy old URL:
// /<diet-url>
//
// Old URL -> 301 -> New canonical URL
// New URL -> 200 and renders normally.

$slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';

if ($slug === '') {
    http_response_code(404);
    echo "Diet plan not found.";
    exit;
}


// ---------------------------------------------------
// Load diet plans JSON
// ---------------------------------------------------

$json_file = __DIR__ . '/diet_plans.json';

if (!file_exists($json_file)) {
    http_response_code(500);
    echo "Diet plans file not found.";
    exit;
}

$data = json_decode(
    file_get_contents($json_file),
    true
);


// ---------------------------------------------------
// Validate JSON
// ---------------------------------------------------

if (
    json_last_error() !== JSON_ERROR_NONE ||
    empty($data['diet_plans']) ||
    !is_array($data['diet_plans'])
) {
    http_response_code(500);
    echo "Error decoding JSON.";
    exit;
}


// ---------------------------------------------------
// Find diet plan using diet_url
// Case-insensitive matching
// ---------------------------------------------------

$diet_plan = null;

foreach ($data['diet_plans'] as $diet) {

    if (
        isset($diet['diet_url']) &&
        strcasecmp(
            trim($diet['diet_url']),
            $slug
        ) === 0
    ) {
        $diet_plan = $diet;
        break;
    }
}


// ---------------------------------------------------
// Diet plan not found
// ---------------------------------------------------

if (!$diet_plan) {
    http_response_code(404);
    echo "Diet plan not found.";
    exit;
}


// ---------------------------------------------------
// OLD URL -> NEW URL 301 REDIRECT
//
// Old:
// /weight-loss-plan
//
// New:
// /plans-and-packages/weight-loss-plan
//
// IMPORTANT:
// If already on /plans-and-packages/,
// DO NOT REDIRECT.
// Render the page normally.
// ---------------------------------------------------

$requestPath = parse_url(
    $_SERVER['REQUEST_URI'] ?? '/',
    PHP_URL_PATH
);

$requestPath = '/' . ltrim($requestPath, '/');

$canonicalPrefix = '/plans-and-packages/';

if (stripos($requestPath, $canonicalPrefix) !== 0) {

    $canonicalSlug = trim($diet_plan['diet_url']);

    $location = '/plans-and-packages/' . rawurlencode($canonicalSlug);

    header('Location: ' . $location, true, 301);
    exit;
}


// ---------------------------------------------------
// SEO meta tags
// ---------------------------------------------------

$title = htmlspecialchars(
    $diet_plan['title'] ?? '',
    ENT_QUOTES,
    'UTF-8'
);

$description = htmlspecialchars(
    $diet_plan['description'] ?? '',
    ENT_QUOTES,
    'UTF-8'
);


// ---------------------------------------------------
// Image path
//
// JSON example:
// images/dietplan_images/0.jpg
//
// Convert to:
// /images/dietplan_images/0.jpg
//
// Otherwise browser may look for:
// /plans-and-packages/images/...
// ---------------------------------------------------

$imageRaw = trim(
    $diet_plan['image'] ?? ''
);

if (
    $imageRaw !== '' &&
    !preg_match('~^(?:https?:)?//~i', $imageRaw)
) {
    $imageRaw = '/' . ltrim($imageRaw, '/');
}

$image = htmlspecialchars(
    $imageRaw,
    ENT_QUOTES,
    'UTF-8'
);


// ---------------------------------------------------
// Render diet plan
// ---------------------------------------------------

include __DIR__ . '/diet_plan_module.php';

?>