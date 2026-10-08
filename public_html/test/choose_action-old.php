<?php
require_once __DIR__ . '/../app_config.php';
// Start the session
session_start();

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
      <h5 class="text-center card-title text-white">Update Posts</h5>
      <p class="text-center card-text">
        <button class="btn ttm-btn-bgcolor-skincolor" onclick="showUpdatePostsModal()">Click Here To Update Posts</button>
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


<!-- Update Posts  start-->
<?php

// Path to cache file
$postsCacheFile = __DIR__ . '/../allposts.json';

// Function to update the post cache
function updatePostCache($cacheFile) {
    // API credentials and parameters
    $apiKey = cfg('google_api_key', '');
    $blogId = cfg('blogger_blog_id', '');
    $maxResults = 100;
    $allPosts = [];
    $startIndex = 1;

    do {
        $apiUrl = "https://www.googleapis.com/blogger/v3/blogs/{$blogId}/posts?maxResults={$maxResults}&startIndex={$startIndex}&key={$apiKey}";
        $response = @file_get_contents($apiUrl);
        if ($response === FALSE) {
            return 'Error fetching blog posts from API';
        }
        $data = json_decode($response, true);

        if (isset($data['items'])) {
            foreach ($data['items'] as $post) {
                $title = $post['title'];
                $postUrl = $post['url'];
                $published = isset($post['published']) ? $post['published'] : ''; // Ensure published is set
                
                $content = $post['content'];
                preg_match('/<img.*?src=["\'](.*?)["\']/', $content, $matches);
                $firstImgSrc = isset($matches[1]) ? $matches[1] : '';

                $allPosts[] = [
                    'title' => $title,
                    'postUrl' => $postUrl,
                    'firstImgSrc' => $firstImgSrc,
                    'published' => $published // Include the published date
                ];
            }
        }

        $startIndex += $maxResults;
    } while (isset($data['items']) && count($data['items']) == $maxResults);

    $cachedData = json_encode($allPosts, JSON_PRETTY_PRINT);
    if (file_put_contents($cacheFile, $cachedData) === false) {
        return 'Failed to write cache file';
    }
    return '';
}

$responseMessage = updatePostCache($postsCacheFile);
echo $responseMessage;
?>




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
    fetch('updatepost_cache.php')
        .then(response => response.text())
        .then(data => {
            const statusDiv = document.getElementById('status-store');
            statusDiv.classList.remove('alert-success', 'alert-danger');
            if (data.includes('successfully')) {
                statusDiv.classList.add('alert-success');
                statusDiv.innerHTML = 'Please wait, post updating...';
                setTimeout(() => {
                    location.reload();
                }, 2000);
            } else {
                statusDiv.classList.add('alert-danger');
                statusDiv.innerHTML = 'Failed to update post cache: ' + data;
            }
        })
        .catch(error => {
            console.error('Error updating cache:', error);
            const statusDiv = document.getElementById('status-store');
            statusDiv.classList.remove('alert-success', 'alert-danger');
            statusDiv.classList.add('alert-danger');
            statusDiv.innerHTML = 'Error occurred while updating post cache: ' + error.message;
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




 <!--  Update Videos start-->
<?php

 $apiKey = cfg('google_api_key', ''); 
 $channelId = cfg('youtube_channel_id', '');
 
$cacheFile = '../youtube_videos.json'; // Path to cache file in root folder
$cacheDuration = 28 * 24 * 60 * 60; // 28 days in seconds

function fetchAllYouTubeVideos($apiKey, $channelId) {
    $videos = [];
    $nextPageToken = '';
    
    do {
        $url = "https://www.googleapis.com/youtube/v3/search?key={$apiKey}&channelId={$channelId}&part=snippet,id&order=date&maxResults=50&pageToken={$nextPageToken}";
        $response = @file_get_contents($url);

        if ($response === false) {
            die('Error fetching data from YouTube API.');
        }

        $data = json_decode($response, true);

        // Filter out items with null videoId
        $currentVideos = array_filter(array_map(function($item) {
            // Check if videoId is set
            if (!isset($item['id']['videoId'])) {
                return null; // Skip if videoId is not present
            }

            return [
                'title' => $item['snippet']['title'],
                'videoId' => $item['id']['videoId'],
                'publishedAt' => date("Y-m-d\TH:i:sP", strtotime($item['snippet']['publishedAt'])),
                'thumbnail' => $item['snippet']['thumbnails']['medium']['url'],
                'description' => $item['snippet']['description']
            ];
        }, $data['items']));

        // Remove null values from the array after filtering
        $currentVideos = array_values(array_filter($currentVideos));

        $videos = array_merge($videos, $currentVideos);
        $nextPageToken = isset($data['nextPageToken']) ? $data['nextPageToken'] : '';
    } while ($nextPageToken);

    return $videos;
}

function isCacheValid($cacheFile, $cacheDuration) {
    if (!file_exists($cacheFile)) {
        return false;
    }

    $fileModTime = filemtime($cacheFile);
    return (time() - $fileModTime) < $cacheDuration;
}

if (isCacheValid($cacheFile, $cacheDuration)) {
    $youtubeVideos = json_decode(file_get_contents($cacheFile), true);
} else {
    $youtubeVideos = fetchAllYouTubeVideos($apiKey, $channelId);
    file_put_contents($cacheFile, json_encode($youtubeVideos));
}
?>



<!-- Update Videos Confirmation Modal -->
<div class="fade modal" id="updateVideosModal" tabindex="-1" role="dialog" aria-labelledby="updateVideosModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="updateVideosModalLabel">Confirm Video Update</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        Do you want to update the videos cache?
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
    fetch('updatevideo_cache.php')
        .then(response => response.text())
        .then(data => {
            const statusDiv = document.getElementById('video-status-store');
            statusDiv.classList.remove('alert-success', 'alert-danger');
            
            if (data.includes('successfully')) {
                statusDiv.classList.add('alert-success');
                statusDiv.innerHTML = 'Please Wait, Video Updating...';
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
            const statusDiv = document.getElementById('video-status-store');
            statusDiv.classList.ad