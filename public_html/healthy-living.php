<?php  
session_start();

// Paths to the cached and backup JSON files
$cacheFile = __DIR__ . '/health-tips.json';
$backupFile = __DIR__ . '/health-tips-backup.json';

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

// DELETE FUNCTIONALITY
if (
    isset($_SESSION['loggedin']) &&
    $_SESSION['loggedin'] === true &&
    isset($_GET['delete'])
) {

    $deleteSlug = trim($_GET['delete']);

    $postsData = isValidJsonFile($cacheFile);

    if ($postsData) {

        $updatedPosts = [];

        foreach ($postsData as $singlePost) {

            if ($singlePost['slug'] !== $deleteSlug) {

                $updatedPosts[] = $singlePost;
            }
        }

        // Backup old file
        if (file_exists($cacheFile)) {

            copy($cacheFile, $backupFile);
        }

        // Save updated JSON
        file_put_contents(
            $cacheFile,
            json_encode($updatedPosts, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );

        require_once __DIR__ . '/admin/sitemap_lib.php';
        sitemap_generate_if_stale();
    }

    header("Location: /healthy-living");
    exit;
}

// Try to load data from the main cache file
$posts = isValidJsonFile($cacheFile);

// If the main cache file is invalid or empty, fallback to the backup file
if (!$posts) {

    $posts = isValidJsonFile($backupFile);

    if (!$posts) {

        error_log("Error: Unable to load valid Instagram post data.");

        die("Error: Unable to load valid Instagram post data.");
    }
}

$initialLimit = 18;
?>

<?php include 'Header.php'; ?>

<div class="ttm-page-title-row">
  <div class="ttm-bg-layer ttm-page-title-row-bg-layer"></div>
  <div class="container">
    <div class="row">
      <div class="text-center col-md-12">
        <div class="ttm-textcolor-white title-box">
          <div class="ttm-textcolor-white page-title-heading">
            <h1 class="title">Healthy Living</h1>
          </div>
          <div class="breadcrumb-wrapper">
            <span><a href="/" title="Homepage"><i class="ti ti-home"></i> Home </a></span>
            <span class="ttm-bread-sep">: : </span>
            <span class="ttm-textcolor-skincolor">Media</span>
            <span class="ttm-bread-sep">: : </span>
            <span class="ttm-textcolor-skincolor">Healthy Living</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<section class="error-404">  

  <header class="text-center section-title pb-4">
    <h5>Healthy Living</h5>
  </header>

  <div class="container mt-5">

<form method="GET" action="" class="row justify-content-center my-4">

    <div class="col-lg-4 col-md-6 col-sm-12">

        <div class="input-group">

            <input 
                type="text" 
                name="search" 
                class="form-control" 
                placeholder="Search by caption..." 
                value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>"
            >

            <button type="submit" class="btn btn-primary">
                Search
            </button>

        </div>

    </div>

</form>

    <div class="row" id="post-container">
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
      <button id="view-more-button" class="btn">
        View More
      </button>
    </div>

  </div>

</section>

<script>

// LOGIN STATUS
let isLoggedIn = <?php echo (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) ? 'true' : 'false'; ?>;

// Function to remove emojis and non-ASCII characters
function removeEmojis(text) {

    return text.replace(/[^\x20-\x7E]/g, '');
}

// Function to trim text
function trimTextWithoutEmojis(text, limit) {

    let cleanText = removeEmojis(text);

    if (cleanText.length > limit) {

        return cleanText.substring(0, limit) + '...';
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

    $('#loading-message').show();

    let html = '';

    let currentLimit = Math.min(offset + limit, filteredPosts.length);

    for (let i = offset; i < currentLimit; i++) {

        let post = filteredPosts[i];

        // Skip video posts
        if (post.media_type === 'VIDEO') {
            continue;
        }

        let caption = post.caption
            ? trimTextWithoutEmojis(post.caption, 80)
            : '';

        let altText = post.caption
            ? trimTextWithoutEmojis(post.caption, 40)
            : '';

        html += `
            <div class="col-lg-4 col-md-6 col-sm-12">

                <div class="card my-2">

                    <div class="card-body">

                        <img 
                            src="${post.media_url}" 
                            class="card-img-top" 
                            alt="${post.title}" 
                            style="width: 100%; height: 320px; object-fit: cover;"
                        >

                        <p class="small text-muted mt-2">
                            Posted on: ${new Date(post.timestamp).toLocaleDateString()}
                        </p>

                        <p class="card-text">
                            ${post.title}
                        </p>

                        <a href="/health-tips/${post.slug}" class="btn btn-primary">
                            View
                        </a>${isLoggedIn ? `

                        <!-- Delete Button -->
<a 
    href="javascript:void(0)"
    class="btn btn-danger ml-2"
    data-toggle="modal"
    data-target="#deleteModal${post.slug}"
>
    Delete
</a>

<!-- Bootstrap Delete Modal -->
<div 
    class="modal fade" 
    id="deleteModal${post.slug}" 
    tabindex="-1" 
    role="dialog" 
    aria-labelledby="deleteModalLabel${post.slug}" 
    aria-hidden="true"
>
    <div class="modal-dialog modal-dialog-centered" role="document">

        <div class="modal-content">

            <div class="modal-header">

                <h5 
                    class="modal-title" 
                    id="deleteModalLabel${post.slug}"
                >
                    Confirm Delete
                </h5>

                <button 
                    type="button" 
                    class="close" 
                    data-dismiss="modal" 
                    aria-label="Close"
                >
                    <span aria-hidden="true">&times;</span>
                </button>

            </div>

            <div class="modal-body">

                Are you sure you want to delete this post?

            </div>

            <div class="modal-footer">

                <button 
                    type="button" 
                    class="btn btn-secondary" 
                    data-dismiss="modal"
                >
                    Cancel
                </button>

                <a 
                    href="?delete=${post.slug}" 
                    class="btn btn-danger"
                >
                    Yes
                </a>

            </div>

        </div>

    </div>
</div>
                        ` : ''}

                    </div>

                </div>

            </div>
        `;
    }

    offset = currentLimit;

    $('#post-container').append(html);

    $('#loading-message').hide();

    loading = false;

    if (offset >= filteredPosts.length) {

        $('#view-more-button-container').hide();

        $('#no-more-posts-message').show();

    } else {

        $('#view-more-button-container').show();
    }

    if (filteredPosts.length === 0) {

        $('#no-posts-message').show();

    } else {

        $('#no-posts-message').hide();
    }
}

// Filter posts
function filterPosts(query) {

    return posts.filter(post => {

        let caption = post.caption
            ? trimTextWithoutEmojis(post.caption, 80)
            : '';

        return caption.toLowerCase().includes(query.toLowerCase());
    });
}

// Search button
$('#searchButton').on('click', function() {

    searchQuery = $('#searchInput').val();

    offset = 0;

    $('#post-container').empty();

    window.history.pushState(
        {},
        '',
        `?search=${encodeURIComponent(searchQuery)}`
    );

    loadPosts(filterPosts(searchQuery));
});

// Enter key
$('#searchInput').on('keypress', function(e) {

    if (e.which === 13) {

        $('#searchButton').click();
    }
});

// View more button
$('#view-more-button').on('click', function() {

    loadPosts(filterPosts(searchQuery));
});

// Initial load
$(document).ready(function() {

    loadPosts(filterPosts(searchQuery));
});

</script>

<?php include 'footer.php'; ?>