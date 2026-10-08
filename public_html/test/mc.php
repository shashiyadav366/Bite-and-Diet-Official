<?php
// Get the current page URL slug
$currentSlug = basename($_SERVER['REQUEST_URI'], "/");

// Load main pages from the JSON file
$json = file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/main-pages.json");

$mainPages = json_decode($json, true)["main_pages"];

// Start building breadcrumb structure
$breadcrumb = [];
$breadcrumb[] = [
    "@type" => "ListItem",
    "position" => 1,
    "name" => "Home",
    "item" => "https://www.biteanddiet.in/"
];

// Add the main pages as breadcrumbs dynamically
$position = 2; // Start from 2 since Home is position 1
foreach ($mainPages as $page) {
    // Add each page as a breadcrumb
    $breadcrumb[] = [
        "@type" => "ListItem",
        "position" => $position,
        "name" => $page['title'],
        "item" => "https://www.biteanddiet.in" . $page['url']
    ];
    $position++;
}

// If the current page does not match any main page, add the current page as the last breadcrumb
if (empty($breadcrumb) || !in_array($currentSlug, array_column($mainPages, 'url'))) {
    $breadcrumb[] = [
        "@type" => "ListItem",
        "position" => $position,
        "name" => ucfirst(str_replace('-', ' ', $currentSlug)),
        "item" => "https://www.biteanddiet.in/" . $currentSlug
    ];
}

// Output JSON-LD for Breadcrumbs
echo '<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "BreadcrumbList",
  "itemListElement": ' . json_encode($breadcrumb, JSON_UNESCAPED_SLASHES) . '
}
</script>';
?>