<?php
// Start the session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Slug helpers shared with blog_sync.php (video slugs must match blog slugs).
require_once __DIR__ . '/slug_lib.php';

// Check if user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    // Set the redirect URL to the current page
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
    
    // Redirect to the login page
    header("Location: ../login.php");
    exit;
}

// Include the header or other necessary files
include '../Header.php';

// Continue with the rest of your choose_action logic here
?>
<?php
//error_reporting(E_ALL);
//ini_set('display_errors', 1);
?>



<div class="ttm-page-title-row">
    <div class="ttm-bg-layer ttm-page-title-row-bg-layer"></div>
    <div class="container">
        <div class="row">
            <div class="text-center col-md-12">
                <div class="ttm-textcolor-white title-box">
                    <div class="ttm-textcolor-white page-title-heading">
                        <h2 class="title">Choose An Action</h2>
                    </div>
                    <div class="breadcrumb-wrapper">
                        <span><a href="/" title="Homepage"><i class="ti ti-home"></i> Home </a></span>
                        <span class="ttm-bread-sep">: : </span>
                        <span><span class="ttm-textcolor-skincolor">Choose An Action</span></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<section class="error-404">
    <header class="text-center section-title">
        <h5>Choose An Action</h5>
    </header>

<div class="container mb-50">
        <div class="row">
            <div class="card-column col-lg-4 col-md-6 col-sm-12 p-4">
        <div class="text-white bg-dark card"><img alt="Card image"class="card-img"src="/images/single-img-one.webp"><div class="align-items-center card-img-overlay d-flex flex-column justify-content-center"><h5 class="text-center card-title text-white">Add Success Story</h5><p class="text-center card-text"><a href="/admin/add_success_story">Click Here To Add Success Story</a></p></div></div></div>
        
            <div class="card-column col-lg-4 col-md-6 col-sm-12 p-4"><div class="text-white bg-dark card"><img alt="Card image"class="card-img"src="/images/single-img-one.webp"><div class="align-items-center card-img-overlay d-flex flex-column justify-content-center"><h5 class="text-center card-title text-white">Check/Edit Success Stories</h5><p class="text-center card-text"><a href="/success-stories">Click Here To Check/Edit Success Stories</a></p></div></div></div>
        
        <div class="card-column col-lg-4 col-md-6 col-sm-12 p-4" style="cursor: not-allowed;">
        <div class="text-white bg-dark card"><img alt="Card image"class="card-img"src="/images/single-img-one.webp"><div class="align-items-center card-img-overlay d-flex flex-column justify-content-center"><h5 class="text-center card-title text-white">Add Blog Post</h5><p class="text-center card-text"><a href="/admin/addpost" style=" pointer-events: none; ">Click Here To Add Blog/Post</a></p></div></div>
    </div>
    
    <!--<div class="card-column col-lg-4 col-md-6 col-sm-12 p-4"style="cursor: not-allowed; "><div class="text-white bg-dark card"><img alt="Card image"class="card-img"src="/images/single-img-one.webp"><div class="align-items-center card-img-overlay d-flex flex-column justify-content-center"><h5 class="text-center card-title text-white">Check/Edit Posts</h5><p class="text-center card-text"><a href="/admin/allposts-not-inuse" style=" pointer-events: none; ">Click Here To Check/Edit Posts</a></p></div></div></div>-->
    
  
    <!-- Card for Update Posts -->
<div class="card-column col-lg-4 col-md-6 col-sm-12 p-4">
  <div class="text-white bg-dark card">
    <img alt="Card image" class="card-img" src="/images/single-img-one.webp">
    <div class="align-items-center card-img-overlay d-flex flex-column justify-content-center">
      <h5 class="text-center card-title text-white">Update Blog Posts</h5>
      <p class="text-center card-text">
        <button class="btn ttm-btn-bgcolor-skincolor" onclick="showUpdatePostsModal()">Click Here To Update Blog Posts</button>
        <div id="status-store" class="alert text-center" role="alert"></div>
      </p>
    </div>
  </div>
</div>
    
    
   <!-- Card for Update Videos -->
<div class="card-column col-lg-4 col-md-6 col-sm-12 p-4">
  <div class="text-white bg-dark card">
    <img alt="Card image" class="card-img" src="/images/single-img-one.webp">
    <div class="align-items-center card-img-overlay d-flex flex-column justify-content-center">
      <h5 class="text-center card-title text-white">Update Videos</h5>
      <p class="text-center card-text">
        <button class="btn ttm-btn-bgcolor-skincolor" onclick="showUpdateVideosModal()">Click Here To Update Videos</button>
        <!-- Status Display Area -->
        <div id="video-status-store" class="text-center alert" role="alert"></div>

      </p>
    </div>
  </div>
</div>


   <div class="card-column col-lg-4 col-md-6 col-sm-12 p-4"><div class="text-white bg-dark card"><img alt="Card image"class="card-img"src="/images/single-img-one.webp"><div class="align-items-center card-img-overlay d-flex flex-column justify-content-center"><h5 class="text-center card-title text-white">Add Social Post</h5><p class="text-center card-text"><a href="/admin/add_social_post">Click Here To Add Social Post</a></p></div></div></div>
        
        <!--<div class="card-column col-lg-4 col-md-6 col-sm-12 p-4"style="cursor: not-allowed; "><div class="text-white bg-dark card"><img alt="Card image"class="card-img"src="/images/single-img-one.webp"><div class="align-items-center card-img-overlay d-flex flex-column justify-content-center"><h5 class="text-center card-title text-white">Change Popup Video</h5><p class="text-center card-text"><a href="/popupvideourl" style=" pointer-events: none; " >Click Here To Change Popup Video</a></p></div></div></div>-->
        
        
                   
            
       
            <div class="card-column col-lg-4 col-md-6 col-sm-12 p-4"><div class="text-white bg-dark card"><img alt="Card image"class="card-img"src="/images/single-img-one.webp"><div class="align-items-center card-img-overlay d-flex flex-column justify-content-center"><h5 class="text-center card-title text-white">Lead Form</h5><p class="text-center card-text"><a href="/form">Click Here To Fill Lead Form</a></p></div></div></div>
            
            
            <div class="card-column col-lg-4 col-md-6 col-sm-12 p-4"><div class="text-white bg-dark card"><img alt="Card image"class="card-img"src="/images/single-img-one.webp"><div class="align-items-center card-img-overlay d-flex flex-column justify-content-center"><h5 class="text-center card-title text-white">Check Leads</h5><p class="text-center card-text"><a href="/admin/contact_details">Click Here To See Leads</a></p></div></div></div>
            
            <div class="card-column col-lg-4 col-md-6 col-sm-12 p-4"><div class="text-white bg-dark card"><img alt="Card image"class="card-img"src="/images/single-img-one.webp"><div class="align-items-center card-img-overlay d-flex flex-column justify-content-center"><h5 class="text-center card-title text-white">Enrollment Form</h5><p class="text-center card-text"><a href="/admin/enrollment_form">Click Here To Fill Enrollment Form</a></p></div></div></div>
      
            <div class="card-column col-lg-4 col-md-6 col-sm-12 p-4"><div class="text-white bg-dark card"><img alt="Card image"class="card-img"src="/images/single-img-one.webp"><div class="align-items-center card-img-overlay d-flex flex-column justify-content-center"><h5 class="text-center card-title text-white">Check Enrolled Client Details</h5><p class="text-center card-text"><a href="/admin/enrolled_contact_details">Click Here To See Enrolled Client Details</a></p></div></div></div>
        
        <div class="card-column col-lg-4 col-md-6 col-sm-12 p-4"><div class="text-white bg-dark card"><img alt="Card image"class="card-img"src="/images/single-img-one.webp"><div class="align-items-center card-img-overlay d-flex flex-column justify-content-center"><h5 class="text-center card-title text-white">Online Payment QR Generator</h5><p class="text-center card-text"><a href="/admin/online-payment-qr-generator">Online Payment QR Generator</a></p></div></div></div>
        
        <div class="card-column col-lg-4 col-md-6 col-sm-12 p-4"><div class="text-white bg-dark card"><img alt="Card image"class="card-img"src="/images/single-img-one.webp"><div class="align-items-center card-img-overlay d-flex flex-column justify-content-center"><h5 class="text-center card-title text-white">Invoice Generator</h5><p class="text-center card-text"><a href="/admin/invoice-generator">Invoice Generator</a></p></div></div>
        </div>
        
     
         <div class="card-column col-lg-4 col-md-6 col-sm-12 p-4"><div class="text-white bg-dark card"><img alt="Card image"class="card-img"src="/images/single-img-one.webp"><div class="align-items-center card-img-overlay d-flex flex-column justify-content-center"><h5 class="text-center card-title text-white">Change Plan Price</h5><p class="text-center card-text"><a href="/plans-and-price">Click Here To Change Plan Price</a></p></div></div></div>
        
         <div class="card-column col-lg-4 col-md-6 col-sm-12 p-4"><div class="text-white bg-dark card"><img alt="Card image"class="card-img"src="/images/single-img-one.webp"><div class="align-items-center card-img-overlay d-flex flex-column justify-content-center"><h5 class="text-center card-title text-white">Get YouTube Data</h5><p class="text-center card-text"><a href="/admin/youtubedata">Click Here To get Youtube Data</a></p></div></div></div>
         
         

<!-- 
<div class="card-column col-lg-4 col-md-6 col-sm-12 p-4">
  <div class="text-white bg-dark card">
    <img alt="Card image" class="card-img" src="/images/single-img-one.webp">
    <div class="align-items-center card-img-overlay d-flex flex-column justify-content-center">
      <h5 class="text-center card-title text-white">Update Social Posts</h5>
      <p class="text-center card-text">
        <button class="btn ttm-btn-bgcolor-skincolor" onclick="showUpdateSocialModal()">Click Here To Update Social Posts</button>
        
        <div id="social-status-store" class="text-center alert" role="alert"></div>
      </p>
    </div>
  </div>
</div> 
-->
         
         
  

        
<div class="card-column col-lg-4 col-md-6 col-sm-12 p-4">
    <div class="text-white bg-dark card">
        <img alt="Card image" class="card-img" src="/images/single-img-one.webp">
        <div class="align-items-center card-img-overlay d-flex flex-column justify-content-center">
            <h5 class="text-center card-title text-white">Update Sitemap</h5>
            <p class="text-center card-text">
                <button id="update-sitemap-btn" class="btn ttm-btn-bgcolor-skincolor">Click Here To Update Sitemap</button>
                <div id="sitemap-status" class="alert text-center" role="alert"></div>
            </p>
        </div>
    </div>
</div>


 <div class="card-column col-lg-4 col-md-6 col-sm-12 p-4"><div class="text-white bg-dark card"><img alt="Card image"class="card-img"src="/images/single-img-one.webp"><div class="align-items-center card-img-overlay d-flex flex-column justify-content-center"><h5 class="text-center card-title text-white">Unique Visitors</h5><p class="text-center card-text"><a href="/admin/view_visitors">Click Here To Check Visitors</a></p></div></div></div>





       </div>
         </div>
        </section>


<!-- Update Posts start-->
<?php

// Shared Blogger sync logic (clean titles, Devanagari->Latin slugs,
// backup + old->new slug alias map). See admin/blog_sync.php.
//
// NOTE: updatePostCache() is deliberately NOT called inline here. It always
// hits the Blogger API (there is no freshness check), so running it on page load
// made this page take ~12s and then wiped the button's confirmation via the
// reload. The "Update Blog Posts" button below is the only trigger; it calls
// admin/updatepost_cache.php, which runs the same function.

?>
<!-- Update Posts end-->


<!-- Update Posts Confirmation Modal -->
<div class="fade modal" id="updatePostsModal" tabindex="-1" role="dialog" aria-labelledby="updatePostsModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="updatePostsModalLabel">Confirm Post Update</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        Do you want to update the posts cache?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="confirm-update-posts">Yes, Update</button>
      </div>
    </div>
  </div>
</div>
<script>
    
  function showUpdatePostsModal() {
    $('#updatePostsModal').modal('show');
}

function updateCache() {
    const statusDiv = document.getElementById('status-store');
    statusDiv.classList.remove('alert-success', 'alert-danger');
    statusDiv.textContent = 'Updating, please wait...';

    fetch('updatepost_cache.php')
        .then(response => response.json().catch(() => ({
            success: false,
            message: 'Unexpected response from server.'
        })))
        .then(data => {
            if (data.success) {
                statusDiv.classList.add('alert-success');
                const countText = typeof data.count === 'number'
                    ? '(' + data.count + ' posts received from Blogger).'
                    : (data.message || '');
                statusDiv.textContent = 'Blog posts updated successfully ' + countText + ' Now beautifying all posts...';
                return beautifyAll();
            } else {
                statusDiv.classList.add('alert-danger');
                statusDiv.textContent = data.message || 'Failed to update post cache.';
                return Promise.resolve();
            }
        })
        .catch(error => {
            console.error('Error updating cache:', error);
            statusDiv.classList.remove('alert-success', 'alert-danger');
            statusDiv.classList.add('alert-danger');
            statusDiv.textContent = 'Error occurred while updating post cache: ' + error.message;
        });
}

function beautifyAll() {
    const statusDiv = document.getElementById('status-store');
    return fetch('auto_beautify_endpoint.php')
        .then(response => response.json().catch(() => ({
            success: false,
            message: 'Unexpected response from beautify endpoint.'
        })))
        .then(data => {
            if (data.success) {
                statusDiv.classList.add('alert-success');
                statusDiv.classList.remove('alert-danger');
                statusDiv.textContent = data.message;
            } else {
                statusDiv.classList.remove('alert-success');
                statusDiv.classList.add('alert-danger');
                statusDiv.textContent = data.message || 'Beautify check failed.';
            }
        })
        .catch(error => {
            console.error('Error beautifying posts:', error);
            statusDiv.classList.remove('alert-success');
            statusDiv.classList.add('alert-danger');
            statusDiv.textContent = 'Error while beautifying posts: ' + error.message;
        });
}

document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('confirm-update-posts').addEventListener('click', function() {
        $('#updatePostsModal').modal('hide');
        updateCache();
    });
});


</script>

<!-- Update Posts  end-->




<?php
// Update Videos start
// Video fetching lives in admin/updatevideo_api.php, which merges new uploads
// into the existing list so nothing is dropped and existing slugs stay stable.
// This page only reads the file. Do not add an auto-refresh here: writing the
// file from this page would bypass that safe merge.
$cacheFile    = __DIR__ . '/../youtube_videos.json';
$youtubeVideos = is_file($cacheFile)
    ? json_decode((string)file_get_contents($cacheFile), true)
    : [];
if (!is_array($youtubeVideos)) {
    $youtubeVideos = [];
}
?>
<div class="fade modal" id="updateVideosModal" tabindex="-1" role="dialog" aria-labelledby="updateVideosModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="updateVideosModalLabel">Confirm Video Update From YouTube</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        Do you want to fetch the latest videos from the YouTube channel? New
        uploads will be added and existing videos refreshed. Existing video
        links will not change.
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="confirm-update-videos">Yes, Update</button>
      </div>
    </div>
  </div>
</div>
<script>
   function showUpdateVideosModal() {
    $('#updateVideosModal').modal('show');
}

function updateVideoCache() {
    const statusDiv = document.getElementById('video-status-store');
    statusDiv.classList.remove('alert-success', 'alert-danger');
    statusDiv.textContent = 'Fetching from YouTube, please wait...';

    fetch('updatevideo_api.php')
        .then(response => response.json().catch(() => ({
            success: false,
            message: 'Unexpected response from server.'
        })))
        .then(data => {
            if (data.success) {
                statusDiv.classList.add('alert-success');
                statusDiv.textContent = data.message
                    + ' Total videos: ' + data.count + '.';
            } else {
                statusDiv.classList.add('alert-danger');
                statusDiv.textContent = data.message || 'Failed to update videos.';
            }
        })
        .catch(error => {
            console.error('Error updating videos:', error);
            statusDiv.classList.remove('alert-success', 'alert-danger');
            statusDiv.classList.add('alert-danger');
            statusDiv.textContent = 'Error occurred while updating videos: ' + error.message;
        });
}

document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('confirm-update-videos').addEventListener('click', function() {
        $('#updateVideosModal').modal('hide');
        updateVideoCache();
    });
});

</script>

<!--  Update Videos ends-->



<!--socialPost  Updates-->


<?php
/*
// Instagram API credentials and configuration
require_once __DIR__ . '/../app_config.php';
$socialAccessToken = cfg('instagram_access_token');
$socialUserId = cfg('instagram_user_id');

// Path to the cache file
$socialCacheFile = '../insta_posts.json'; // Path to cache file
$socialCacheDuration = 1*24*60*60; // 1 days in seconds

function fetchAllSocialPosts($accessToken, $userId) {
    $socialPosts = [];
    $nextPageUrl = "https://graph.instagram.com/{$userId}/media?fields=id,caption,media_type,media_url,thumbnail_url,permalink,timestamp,children{media_url,media_type}&access_token={$accessToken}";

    do {
        // echo "Fetching data from: $nextPageUrl\n"; // Debugging: Print the URL being fetched
        $response = @file_get_contents($nextPageUrl);

        if ($response === false) {
            die('Error fetching data from Instagram API.');
        }
        // Debugging: Output raw API response
echo "<pre>API Response:\n" . htmlspecialchars($response) . "\n</pre>";

        $data = json_decode($response, true);

        if (!isset($data['data'])) {
            break;
        }

        foreach ($data['data'] as $item) {
            $socialPosts[] = [
                'id' => $item['id'],
                'caption' => $item['caption'] ?? '',
                'media_type' => $item['media_type'],
                'media_url' => $item['media_url'],
                'permalink' => $item['permalink'] ?? '',
                'timestamp' => $item['timestamp'] ?? ''
            ];
        }

        $nextPageUrl = isset($data['paging']['next'])
            ? $data['paging']['next']
            : null;
    } while ($nextPageUrl);

    return $socialPosts;
}

function isSocialCacheValid($cacheFile, $cacheDuration) {
    if (!file_exists($cacheFile)) {
        return false;
    }

    $fileModTime = filemtime($cacheFile);
    return (time() - $fileModTime) < $cacheDuration;
}

// Use cache or fetch fresh data
if (isSocialCacheValid($socialCacheFile, $socialCacheDuration)) {
    $socialPosts = json_decode(file_get_contents($socialCacheFile), true);
} else {
    $socialPosts = fetchAllSocialPosts($socialAccessToken, $socialUserId);
    file_put_contents($socialCacheFile, json_encode($socialPosts));
}

// Debugging: Output the number of posts fetched
//echo "Number of posts fetched: " . count($socialPosts) . "\n";

*/
?>



<!-- Update Social Posts Confirmation Modal 


<div class="fade modal" id="updateSocialModal" tabindex="-1" role="dialog" aria-labelledby="updateSocialModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="updateSocialModalLabel">Confirm Social Posts Update</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        Do you want to update the social posts cache?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="confirm-update-social">Yes, Update</button>
      </div>
    </div>
  </div>
</div>


<script>
function showUpdateSocialModal() {
    $('#updateSocialModal').modal('show');
}

function updateSocialCache() {
    fetch('update_insta_posts_cache.php')
        .then(response => response.text())
        .then(data => {
            const statusDiv = document.getElementById('social-status-store');
            statusDiv.classList.remove('alert-success', 'alert-danger');
            
            if (data.includes('successfully')) {
                statusDiv.classList.add('alert-success');
                statusDiv.innerHTML = 'Please Wait, Social Posts Updating...';
                setTimeout(function() {
                    location.reload();
                }, 2000);
            } else {
                statusDiv.classList.add('alert-danger');
                statusDiv.innerHTML = 'Failed to update cache.';
            }
        })
        .catch(error => {
            console.error('Error updating cache:', error);
            const statusDiv = document.getElementById('social-status-store');
            statusDiv.classList.add('alert-danger');
            statusDiv.innerHTML = 'Error occurred while updating cache.';
        });
}

document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('confirm-update-social').addEventListener('click', function() {
        $('#updateSocialModal').modal('hide');
        updateSocialCache();
    });
});
</script>

//socialPost  Updates-->












<!-- Update Sitemap  -->
<div class="fade modal" id="updateSitemapModal" tabindex="-1" role="dialog" aria-labelledby="updateSitemapModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="updateSitemapModalLabel">Confirm Sitemap Update</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        Do you want to update the sitemap?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="confirm-update">Yes, Update</button>
      </div>
    </div>
  </div>
</div>
        
       <script>

function updateSitemap() {
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (this.readyState == 4) {
            if (this.status == 200) {
                document.getElementById("sitemap-status").innerHTML = this.responseText;
            } else {
                console.error('Error: ' + this.status);
                document.getElementById("sitemap-status").innerHTML = 'Failed to generate sitemap.';
            }
        }
    };
    xhttp.open("GET", "sitemap-generate.php", true); // Ensure this path is correct
    xhttp.send();
}

document.addEventListener('DOMContentLoaded', function() {
    // Show the confirmation modal when the button is clicked
    document.getElementById('update-sitemap-btn').addEventListener('click', function() {
        $('#updateSitemapModal').modal('show');
    });

    // Handle the confirmation button click
    document.getElementById('confirm-update').addEventListener('click', function() {
        $('#updateSitemapModal').modal('hide');
        updateSitemap();
    });
});

</script>

<!-- Update Sitemap  ends-->

 
        <?php include '../footer.php'; ?>
