<?php  
// Paths to the cached and backup JSON files
$cacheFile = __DIR__ . '/insta_posts.json';
$backupFile = __DIR__ . '/insta_posts-backup.json';

// Function to check if a file contains valid JSON
function isValidJsonFile($file) {
    if (file_exists($file) && filesize($file) > 0) {
        $fileContents = file_get_contents($file);
        $jsonData = json_decode($fileContents, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $jsonData;
        } else {
            error_log('JSON decode error: ' . json_last_error_msg());
        }
    }
    return false;
}

// Try to load data from the main cache file
$posts = isValidJsonFile($cacheFile);

// If the main cache file is invalid or empty, fallback to the backup file
if (!$posts) {
    $posts = isValidJsonFile($backupFile);
    if (!$posts) {
        // Log the error and show an error message
        error_log("Error: Unable to load valid Instagram post data.");
        die("Error: Unable to load valid Instagram post data.");
    }
}

$initialLimit = 18;
?>

<?php include '../Header.php'; ?>

<div class="ttm-page-title-row">
  <div class="ttm-bg-layer ttm-page-title-row-bg-layer"></div>
  <div class="container">
    <div class="row">
      <div class="text-center col-md-12">
        <div class="ttm-textcolor-white title-box">
          <div class="ttm-textcolor-white page-title-heading">
            <h1 class="title">Social Posts</h1>
          </div>
          <div class="breadcrumb-wrapper">
            <span><a href="/" title="Homepage"><i class="ti ti-home"></i> Home </a></span>
            <span class="ttm-bread-sep">: : </span>
            <span class="ttm-textcolor-skincolor">Media</span>
            <span class="ttm-bread-sep">: : </span>
            <span class="ttm-textcolor-skincolor">Social Posts</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<section class="error-404">  
  <header class="text-center section-title pb-4">
    <h5>Social Posts</h5>
  </header>

  <div class="container mt-5">
    <!-- Filter Section -->
<!-- Search Section -->
<form method="GET" action="" class="row justify-content-center my-4">
    <div class="col-lg-4 col-md-6 col-sm-12">
        <div class="input-group">
            <input type="text" name="search" class="form-control" placeholder="Search by caption..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
            <button type="submit" class="btn btn-primary">Search</button>
        </div>
    </div>
</form>


    <div class="row" id="post-container">
        <!-- Posts will be loaded here dynamically -->
    </div>

    <div class="text-center mt-4" id="loading-message" style="display: none;">
      <p>Loading more posts...</p>
    </div>

    <div class="text-center mt-4" id="no-posts-message" style="display: none;">
      <p>No posts found.</p>
    </div>

    <div class="text-center mt-4" id="no-more-posts-message" style="display: none;">
      <p>No more posts to load.</p>
    </div> 

    <div class="text-center mt-4" id="view-more-button-container" style="display: none;">
      <button id="view-more-button" class="btn">View More</button>
    </div>
  </div>
</section>

<script>
// Function to remove emojis and non-ASCII characters
function removeEmojis(text) {
    return text.replace(/[^\x20-\x7E]/g, ''); // Removes non-ASCII characters including emojis
}

// Function to trim text to the desired length, excluding emojis from the count
function trimTextWithoutEmojis(text, limit) {
    let cleanText = removeEmojis(text); // Remove emojis from the text
    if (cleanText.length > limit) {
        return cleanText.substring(0, limit) + '...'; // Trim to limit and add ellipsis
    }
    return cleanText;
}

let posts = <?php echo json_encode($posts); ?>;
let limit = <?php echo $initialLimit; ?>;
let offset = 0;
let loading = false;
let searchQuery = '<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>';

function loadPosts(filteredPosts) {
    if (loading) return;

    loading = true;
    $('#loading-message').show(); // Show loading message

    let html = '';
    let currentLimit = Math.min(offset + limit, filteredPosts.length);

    // Loop through the posts and display them
    for (let i = offset; i < currentLimit; i++) {
        let post = filteredPosts[i];

        // Skip if the post is a video
        if (post.media_type === 'VIDEO') {
            continue;
        }

        // Clean and trim the caption and altText by excluding emojis in the character count
        let caption = post.caption ? trimTextWithoutEmojis(post.caption, 80) : '';
        let altText = post.caption ? trimTextWithoutEmojis(post.caption, 40) : ''; // Shortened caption for alt text

        html += `
            <div class="col-lg-4 col-md-6 col-sm-12">
                <div class="card my-2">
                    <div class="card-body">
                        <img src="${post.media_url}" class="card-img-top" alt="${altText}" style="width: 100%; height: 320px; object-fit: cover;">
                        <p class="small text-muted mt-2">Posted on: ${new Date(post.timestamp).toLocaleDateString()}</p>
                        <p class="card-text">${caption}</p>
                        <a href="../social-post/${post.slug}" class="btn btn-primary">View</a>
                    </div>
                </div>
            </div>
        `;
    }

    offset = currentLimit;
    $('#post-container').append(html);
    $('#loading-message').hide(); // Hide loading message
    loading = false;

    // If no more posts to load, show "No more posts to load"
    if (offset >= filteredPosts.length) {
        $('#view-more-button-container').hide(); // Hide the "View More" button
        $('#no-more-posts-message').show(); // Show "No more posts to load"
    } else {
        $('#view-more-button-container').show(); // Show "View More" button if more posts are available
    }

    // Show "No posts found" if no posts match the query
    if (filteredPosts.length === 0) {
        $('#no-posts-message').show();
    } else {
        $('#no-posts-message').hide();
    }
}

// Function to filter posts by caption
function filterPosts(query) {
    return posts.filter(post => {
        let caption = post.caption ? trimTextWithoutEmojis(post.caption, 80) : '';
        return caption.toLowerCase().includes(query.toLowerCase());
    });
}

// Event listener for the search button
$('#searchButton').on('click', function() {
    searchQuery = $('#searchInput').val();
    offset = 0; // Reset the offset when a new search is performed
    $('#post-container').empty(); // Clear existing posts
    window.history.pushState({}, '', `?search=${encodeURIComponent(searchQuery)}`); // Update the URL with the search query
    loadPosts(filterPosts(searchQuery)); // Load posts based on the search query
});

// Event listener for the Enter key in the search input
$('#searchInput').on('keypress', function(e) {
    if (e.which === 13) { // Enter key
        $('#searchButton').click();
    }
});

// Function to handle the "View More" button click
$('#view-more-button').on('click', function() {
    loadPosts(filterPosts(searchQuery)); // Load more posts based on the current filter
});

// Load initial posts based on search query (if any)
$(document).ready(function() {
    loadPosts(filterPosts(searchQuery)); // Load posts based on the initial search query
});
</script>

<?php include '../footer.php'; ?>
