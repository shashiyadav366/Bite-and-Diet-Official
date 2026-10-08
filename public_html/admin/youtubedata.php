<?php

session_start();

// Save the current URL in the session
$_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];

// Check if the user is not logged in, redirect to login.php
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../login.php");
    exit;
}
?>

<?php include '../Header.php'; ?>
<div class="ttm-page-title-row"><div class="ttm-bg-layer ttm-page-title-row-bg-layer"></div><div class="container"><div class="row"><div class="text-center col-md-12"><div class="ttm-textcolor-white title-box"><div class="ttm-textcolor-white page-title-heading"><h2 class="title">Youtube Data</h2></div><div class="breadcrumb-wrapper"><span><a href="/"title="Homepage"><i class="ti ti-home"></i> Home </a></span><span class="ttm-bread-sep">: : </span><span><span class="ttm-textcolor-skincolor">Youtube Data</span></span></div></div></div></div></div></div><section class="error-404"><header class="text-center section-title"><h5>Youtube Data</h5></header></section>

<?php
// Function to validate a YouTube video URL
function isValidYouTubeUrl($url) {
    $pattern = '/^(https?:\/\/)?(www\.)?(youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]{11})/';
    return preg_match($pattern, $url);
}

// Function to get video data from data.json based on YouTube URL
function getVideoData($url) {
    // Fetch existing JSON data
    $existingData = json_decode(file_get_contents('../youtube_videos.json'), true);

    // Extract videoId from the provided video URL
    $videoId = null;
    $pattern = '/(?:https?:\/\/)?(?:www\.)?(?:youtube\.com\/(?:[^\/\n\s]+\/\S+\/|(?:v|e(?:mbed)?)\/|\S*?[?&]v=)|youtu\.be\/)([a-zA-Z0-9_-]{11})/';
    preg_match($pattern, $url, $matches);

    if (!empty($matches[1])) {
        $videoId = $matches[1];
    }

    // Search for the video data with the matching videoId
    foreach ($existingData as $video) {
        if ($video['videoId'] === $videoId) {
            return $video;
        }
    }

    return null; // Video not found
}

// Handle form submission
$errorMessage = '';
$videoTitle = $thumbnailUrl = $videoUrl = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $videoUrl = $_POST['video_url'];

    // Validate input
    if (!isValidYouTubeUrl($videoUrl)) {
        $errorMessage = "Invalid YouTube URL. Please provide a valid video URL.";
    } else {
        // Get video data based on YouTube URL
        $videoData = getVideoData($videoUrl);

        if ($videoData !== null) {
            $videoTitle = $videoData['title'];
            $thumbnailUrl = "https://img.youtube.com/vi/{$videoData['videoId']}/maxresdefault.jpg";
        } else {
            $errorMessage = "No Data Found";
        }
    }
}
?>

<section class="break-991-colum checkout-section clearfix ttm-row">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="container">

                    <!-- Form for submitting video URL -->
                    <form action="" method="post">
                        <?php if (!empty($errorMessage)): ?>
                            <div class="alert alert-danger"><?php echo $errorMessage; ?></div>
                        <?php endif; ?>
                        <div class="form-group">
                            <label for="video_url">YouTube URL:</label>
                            <input type="text" name="video_url" required>
                        </div>

                        <div class="text-center p-4">
                            <button class="mb-20 ttm-btn ttm-btn-bgcolor-black ttm-btn-shape-round ttm-btn-size-md ttm-btn-style-fill" type="submit">Get Title and Thumbnail</button>
                        </div>
                    </form>

                    <?php if (!empty($videoTitle) && !empty($thumbnailUrl)): ?>
                        <div class="card">
                            <img src="<?php echo $thumbnailUrl; ?>" alt="<?php echo $videoTitle; ?>">                     
                            <div class="card-body">
                                <h5 class="card-title">
                                    <?php echo $videoTitle; ?>
                                    <button class="btn btn-secondary btn-sm" id="copyButton" data-title="<?php echo $videoTitle; ?>" data-url="<?php echo $videoUrl; ?>">Copy</button>
                                </h5>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Bootstrap alert for copied text -->
                    <div id="copyAlert" class="alert alert-success mt-3" style="display: none;">
                        Video title and link copied successfully!
                    </div>

                </div>
            </div>
        </div>
    </div>
</section>

<script>
    document.getElementById('copyButton')?.addEventListener('click', function() {
        var videoTitle = this.getAttribute('data-title');
        var videoUrl = this.getAttribute('data-url');
        var copiedText = `${videoTitle}\n\nWatch Now: ${videoUrl}`;
        
        // Create a temporary textarea element to hold the text
        var tempInput = document.createElement('textarea');
        tempInput.value = copiedText;
        document.body.appendChild(tempInput);
        tempInput.select();
        var success = document.execCommand('copy');
        document.body.removeChild(tempInput);

        // Show success alert
        if (success) {
            var copyAlert = document.getElementById('copyAlert');
            copyAlert.style.display = 'block';
            setTimeout(function() {
                copyAlert.style.display = 'none';
            }, 2000);
        } else {
            alert("Failed to copy the content. Please try again.");
        }
    });
</script>

<?php include '../footer.php'; ?>
