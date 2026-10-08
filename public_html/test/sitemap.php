<?php include 'Header.php'; ?>
<div class="ttm-page-title-row"><div class="ttm-bg-layer ttm-page-title-row-bg-layer"></div><div class="container"><div class="row"><div class="text-center col-md-12"><div class="ttm-textcolor-white title-box"><div class="ttm-textcolor-white page-title-heading"><h1 class="title">Sitemap</h1></div><div class="breadcrumb-wrapper"><span><a href="./"title="Homepage"><i class="ti ti-home"></i> Home </a></span><span class="ttm-bread-sep">: : </span><span><span class="ttm-textcolor-skincolor">Sitemap</span></span></div></div></div></div></div></div><section class="break-991-colum checkout-section clearfix ttm-row"><div class="container"><div class="row"><div class="col-lg-12"><div class="container">
    
    

    
   
    <h1 class="text-center py-5">Sitemap</h1>
    <p>Welcome to the Bite and Diet sitemap. Below is a comprehensive list of all our pages, categorized for easy navigation.</p>
    

<?php

// Initialize a flag to check if any data matches
$foundData = false;
// Capture the search term from the query string
$searchTerm = isset($_GET['q']) ? strtolower(trim($_GET['q'])) : '';

// Function to check if a string contains the search term
function matchesSearch($text, $searchTerm) {
    return $searchTerm === '' || stripos($text, $searchTerm) !== false;
}
?>

<!-- Search Form -->


<?php 
// Capture the search term from the query string
$searchTerm = isset($_GET['q']) ? strtolower(trim($_GET['q'])) : '';

?>

<!-- Search Form -->
<div class="container">
    <div class="row my-5 justify-content-center text-center">
        <!-- Change the form action to ensure the query string is passed to the right file -->
        <form method="get" action="sitemap.php" class="d-flex"> 
            <input 
                type="text" 
                name="q" 
                placeholder="Search (e.g., weight loss)" 
                value="<?= htmlspecialchars($searchTerm) ?>" 
                class="form-control me-2" 
            >
            <button type="submit" class="btn btn-primary">Filter</button>
        </form>
    </div>
</div>

<?php if (!empty($searchTerm)) : ?>
    <!-- Copy Data Alert -->
    <div id="copyAlert" class="alert alert-success d-none" role="alert">
        Data copied to clipboard successfully!
    </div>

    <!-- Copy Button -->
    <div class="text-center my-4">
        <button id="copyButton" class="btn btn-success">Copy Searched Data</button>
    </div>
<?php endif; ?>

<!-- JavaScript for Copy Functionality -->
<script>
    document.getElementById('copyButton')?.addEventListener('click', function () {
        // Collect all displayed data except social posts
        const lists = document.querySelectorAll('ul.ttm-list:not(.social-posts)'); // Exclude social posts list
        let copiedData = '';

        lists.forEach(list => {
            // Section header based on the previous sibling's text (e.g., "Main Pages", "Blog Posts")
            const sectionHeader = list.previousElementSibling ? list.previousElementSibling.textContent.trim() : '';
            if (sectionHeader) {
                copiedData += `\n${sectionHeader}:\n`; // Add section header with spacing
            }

            // Collect data within each section
            const items = list.querySelectorAll('li');
            items.forEach(item => {
                const title = item.textContent.trim();
                const link = item.querySelector('a') ? item.querySelector('a').href : '';

                if (title && link) {
                    copiedData += `  - ${title}\n    ${link}\n\n`; // Add extra line after each item
                }
            });
        });

        // Copy to clipboard
        navigator.clipboard.writeText(copiedData).then(() => {
            const alert = document.getElementById('copyAlert');
            alert.classList.remove('d-none'); // Show the alert
            setTimeout(() => alert.classList.add('d-none'), 3000); // Hide the alert after 3 seconds
        }).catch(err => {
            console.error('Failed to copy text: ', err);
        });
    });
</script>


 <!-----copied data end------>


<!-- Main Pages -->

<?php
$mainPages = json_decode(file_get_contents('main-pages.json'), true)['main_pages'] ?? [];
$foundMainPages = false;
foreach ($mainPages as $page) {
    if (matchesSearch($page['title'], $searchTerm)) {
        $foundMainPages = true;
        $foundData = true;
        break;
    }
}

if ($foundMainPages) {
    echo '<h2>Main Pages</h2><ul class="ttm-list ttm-list-style-icon">';
    foreach ($mainPages as $page) {
        if (matchesSearch($page['title'], $searchTerm)) {
            echo '<li><i class="fa fa-arrow-circle-right ttm-textcolor-skincolor px-2"></i>';
            echo '<a class="mx-2" href="' . htmlspecialchars($page['url']) . '">' . htmlspecialchars($page['title']) . '</a>';
            echo '</li>';
        }
    }
    echo '</ul><hr>';
}
?>

<!-- Diet Plans -->
<?php
$dietPlans = json_decode(file_get_contents('diet_plans.json'), true)['diet_plans'] ?? [];
$foundDietPlans = false;
foreach ($dietPlans as $diet) {
    if (matchesSearch($diet['diet_name'], $searchTerm) || matchesSearch($diet['title'], $searchTerm)) {
        $foundDietPlans = true;
        $foundData = true;
        break;
    }
}

if ($foundDietPlans) {
    echo '<h2>Diet Plans Available</h2><ul class="ttm-list ttm-list-style-icon">';
    foreach ($dietPlans as $diet) {
        if (matchesSearch($diet['diet_name'], $searchTerm) || matchesSearch($diet['title'], $searchTerm)) {
            echo '<li><i class="fa fa-arrow-circle-right ttm-textcolor-skincolor px-2"></i>';
            echo '<a class="mx-2" href="/' . htmlspecialchars($diet['diet_url']) . '">' . htmlspecialchars($diet['diet_name']) . '</a>';
            echo '</li>';
        }
    }
    echo '</ul><hr>';
}
?>

<!-- Blog Posts -->
<?php
$blogPosts = json_decode(file_get_contents('allposts.json'), true) ?? [];
$foundBlogPosts = false;
foreach ($blogPosts as $post) {
    if (matchesSearch($post['title'], $searchTerm)) {
        $foundBlogPosts = true;
        $foundData = true;
        break;
    }
}

if ($foundBlogPosts) {
    echo '<h2>Blog Posts</h2><ul class="ttm-list ttm-list-style-icon">';
    foreach ($blogPosts as $post) {
        if (matchesSearch($post['title'], $searchTerm)) {
            echo '<li><i class="fa fa-arrow-circle-right ttm-textcolor-skincolor px-2"></i>';
            echo '<a class="mx-2" href="/blog-post/' . htmlspecialchars($post['slug']) . '">' . htmlspecialchars($post['title']) . '</a>';
            echo '</li>';
        }
    }
    echo '</ul><hr>';
}
?>

<!-- Videos -->
<?php
$videos = json_decode(file_get_contents('youtube_videos.json'), true) ?? [];
$foundVideos = false;
foreach ($videos as $video) {
    if (matchesSearch($video['title'], $searchTerm)) {
        $foundVideos = true;
        $foundData = true;
        break;
    }
}

if ($foundVideos) {
    echo '<h2>Our Videos</h2><ul class="ttm-list ttm-list-style-icon">';
    foreach ($videos as $video) {
        if (matchesSearch($video['title'], $searchTerm)) {
            echo '<li><i class="fa fa-arrow-circle-right ttm-textcolor-skincolor px-2"></i>';
            echo '<a class="mx-2" href="https://youtu.be/' . htmlspecialchars($video['videoId']) . '">' . html_entity_decode($video['title'], ENT_QUOTES, 'UTF-8') . '</a>';
            echo '</li>';
        }
    }
    echo '</ul><hr>';
}
?>


<!-- Success Stories -->
<?php
$successStories = json_decode(file_get_contents('success-stories.json'), true) ?? [];
$foundSuccessStories = false;
foreach ($successStories as $story) {
    if (matchesSearch($story['title'], $searchTerm)) {
        $foundSuccessStories = true;
        $foundData = true;
        break;
    }
}

if ($foundSuccessStories) {
    echo '<h2>Success Stories</h2><ul class="ttm-list ttm-list-style-icon">';
    foreach ($successStories as $story) {
        if (matchesSearch($story['title'], $searchTerm)) {
            echo '<li><i class="fa fa-arrow-circle-right ttm-textcolor-skincolor px-2"></i>';
            echo '<a class="mx-2" href="' . htmlspecialchars($story['url']) . '">';
            echo htmlspecialchars($story['title']) . '</a>';
            echo '</li>';
        }
    }
    echo '</ul><hr>';
}
?>









<!-- Social Posts -->
<?php
// File paths for Instagram posts
$insta_posts_file = 'insta_posts.json';
$insta_backup_file = 'insta_posts-backup.json';

// Function to check if a file contains valid JSON
function isValidSocialJsonFile($file) {
    if (file_exists($file) && filesize($file) > 0) {
        $fileContents = file_get_contents($file);
        $jsonData = json_decode($fileContents, true);
        return $jsonData !== null ? $jsonData : false;
    }
    return false;
}

// Function to limit the caption to a specified number of words
function limitWords($text, $wordLimit = 20) {
    $words = explode(' ', $text);
    if (count($words) > $wordLimit) {
        return implode(' ', array_slice($words, 0, $wordLimit)) . '...'; // Truncate and add ellipsis
    }
    return $text;
}

// Function to remove emojis and non-ASCII characters
function removeEmojis($text) {
    return preg_replace('/[^\x20-\x7E]/', '', $text); // Removes non-ASCII characters including emojis
}

// Load the main Instagram posts file first
$instaPosts = isValidSocialJsonFile($insta_posts_file);

// If the main file is invalid or empty, fallback to the backup file
if (!$instaPosts) {
    $instaPosts = isValidSocialJsonFile($insta_backup_file);
    if (!$instaPosts) {
        // Handle the case where both files are invalid or empty
        die("Error: Unable to load valid Instagram post data.");
    }
}

$foundInstaPosts = false;
foreach ($instaPosts as $post) {
    $caption = isset($post['caption']) ? html_entity_decode($post['caption'], ENT_QUOTES, 'UTF-8') : ''; // Decode caption safely
    $cleanCaption = removeEmojis($caption); // Remove emojis from the caption
    $limitedCaption = limitWords($cleanCaption, 15); // Limit the cleaned caption to 15 words

    // Check if the post matches the search term in the limited caption
    if (matchesSearch($limitedCaption, $searchTerm)) {
        $foundInstaPosts = true;
        $foundData = true;
        break;
    }
}

if ($foundInstaPosts) {
    echo '<h2>Our Social Posts</h2>';
    echo '<ul class="ttm-list ttm-list-style-icon">';

    // Use an array to track unique post IDs
    $uniquePosts = [];

    // Loop through each Instagram post and create a list item
    foreach ($instaPosts as $post) {
        $postId = $post['id'] ?? null; // Use the 'id' to check for uniqueness, ensure it's set
        $caption = isset($post['caption']) ? html_entity_decode($post['caption'], ENT_QUOTES, 'UTF-8') : ''; // Decode caption safely
        $cleanCaption = removeEmojis($caption); // Remove emojis from the caption
        $limitedCaption = limitWords($cleanCaption, 15); // Limit the cleaned caption to 15 words

        // Check if the post matches the search term in the limited caption
        if ($postId && !in_array($postId, $uniquePosts) && matchesSearch($limitedCaption, $searchTerm)) {
            $uniquePosts[] = $postId; // Add the post ID to the array to mark it as processed

            // Output the list item with properly formatted href using the custom URL format
            echo '<li><i class="fa fa-arrow-circle-right ttm-textcolor-skincolor px-2"></i>';
            echo '<a class="mx-2" href="/social_post_details?id=' . $postId . '">' . $limitedCaption . '</a></li>';
        }
    }
    echo '</ul><hr>';
}
?>



<h2>Social Sites </h2>
<?php
// Load the social sites data from the JSON file
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
// Get the current date
const currentDate = new Date();

// Extract the current month and year
const month = currentDate.getMonth(); // Returns a zero-based month (0 for January, 1 for February, etc.)
const year = currentDate.getFullYear();

// Define an array of month names
const monthNames = [
    "January", "February", "March", "April", "May", "June",
    "July", "August", "September", "October", "November", "December"
];

// Format the date as "Month Year"
const formattedDate = `${monthNames[month]}, ${year}`;

// Set the innerHTML of the element with ID "month-year"
document.getElementById("month-year").innerHTML = formattedDate;
</script>
	
    
    
    </div></div></div></div></section><?php include 'footer.php'; ?>