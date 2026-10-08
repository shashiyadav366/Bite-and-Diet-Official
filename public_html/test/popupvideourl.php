<?php
session_start();

// Save the current URL in the session
$_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];

// Check if the user is not logged in, redirect to login.php
//if (!isset($_SESSION['loggedin']) || //$_SESSION['loggedin'] !== true) {
   // header("Location: login.php");
   // exit;
//}

// Function to validate a YouTube video URL
function isValidYouTubeUrl($url) {
    $pattern = '/^(https?:\/\/)?(www\.)?(youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]{11})/';
    return preg_match($pattern, $url);
}

// Handle form submission
$errorMessage = $successMessage = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $videoUrl = $_POST['video_url'];
    $videoTitle = $_POST['video_title'];

    // Validate input
    if (!isValidYouTubeUrl($videoUrl)) {
        $errorMessage = "Invalid YouTube URL. Please provide a valid video URL.";
    } else {
        // Extract videoId from the provided video URL
        $videoId = null;
        $pattern = '/(?:https?:\/\/)?(?:www\.)?(?:youtube\.com\/(?:[^\/\n\s]+\/\S+\/|(?:v|e(?:mbed)?)\/|\S*?[?&]v=)|youtu\.be\/)([a-zA-Z0-9_-]{11})/';
        preg_match($pattern, $videoUrl, $matches);

        if (!empty($matches[1])) {
            $videoId = $matches[1];
        }

        if ($videoId === null) {
            // Handle extraction failure
            $errorMessage = "Failed to extract videoId from the provided URL.";
        } else {
            // Fetch existing JSON data
            $existingData = json_decode(file_get_contents('data.json'), true);
                        // Check if the video URL already exists in the data
            if (!in_array($videoUrl, array_column($existingData, 'videoUrl'))) {
                // Create new video data
                $newVideoData = [
                    "videoUrl" => $videoUrl,
                    "videoId" => $videoId,
                    "title" => $videoTitle,
                    "description" => $videoTitle, // Use title as description for simplicity
                    "thumbnails" => [
                        "default" => ["url" => "https://i.ytimg.com/vi/{$videoId}/default.jpg", "width" => 120, "height" => 90],
                        "medium" => ["url" => "https://i.ytimg.com/vi/{$videoId}/mqdefault.jpg", "width" => 320, "height" => 180],
                        "high" => ["url" => "https://i.ytimg.com/vi/{$videoId}/hqdefault.jpg", "width" => 480, "height" => 360],
                    ],
                    "channelTitle" => "Bite And Diet - Dietician Priyanka",
                    "publishTime" => date("Y-m-d\TH:i:s\Z"), // Use current time as publish time
                ];

                // Add new video data to the array
                array_unshift($existingData, $newVideoData);

                // Write the updated JSON data back to the file
                file_put_contents('data.json', json_encode($existingData, JSON_PRETTY_PRINT));

                $successMessage = "Video submitted successfully.";
            } else {
                $errorMessage = "Video with this URL already exists.";
            }
        }
    }
}
?>
<style>
        .cta-section {
/* Ensure full height of the viewport */
    
    background: url('bite_and_diet-cta.png') center center no-repeat;
    background-size: cover; /* Ensure the entire image is visible */
   /* background-attachment: fixed;* /
    /* Optional: Makes image stay in place on scroll */
    position: relative;
    width: 100%;
    
        }

        .cta-content h1, .cta-content p {
            color: white;
        }
        .cta-content .btn {
            margin-top: 20px;
        }
        .cta-right {
            height: 100%;
        }
        
        
        .cta-overlay {
    background-color: rgba(0, 0, 0, 0.5);
    height: 100%;
    padding: 220px;
    width: fit-content;
    
    
}






/* Media query for tablet devices (992px and below) */
@media (max-width: 992px) {
    .cta-section {
        background-position: right center; /* Focus on the right side of the image for tablets */
    }
}

@media (max-width: 768px) {
    .cta-section {
        background-position: right center; /* Shift focus to the right side of the image */
    }
}

       
    </style>
    
<?php include '../Header.php'; ?>

<div class="ttm-page-title-row"><div class="ttm-bg-layer ttm-page-title-row-bg-layer"></div><div class="container"><div class="row"><div class="text-center col-md-12"><div class="ttm-textcolor-white title-box"><div class="ttm-textcolor-white page-title-heading"><h1 class="title">Add YouTube Popup Video</h1></div><div class="breadcrumb-wrapper"><span><a href="./" title="Homepage"><i class="ti ti-home"></i> Home </a></span><span class="ttm-bread-sep">: : </span><span><span class="ttm-textcolor-skincolor">Add YouTube Popup Video</span></span></div></div></div></div></div></div>

<section class="break-991-colum checkout-section clearfix ttm-row">
    
    <div class="container"><div class="row"><div class="col-lg-12">
    <div class="container">
    
    <!-- Form for submitting video data -->
    <form action="" method="post">
        <?php if (!empty($successMessage)): ?>
            <div class="alert alert-success"><?php echo $successMessage; ?></div>
        <?php endif; ?>
        <?php if (!empty($errorMessage)): ?>
            <div class="alert alert-danger"><?php echo $errorMessage; ?></div>
        <?php endif; ?>
        <div class="form-group">
            <label for="video_url">Video URL:</label>
            <input type="text" name="video_url" required>

            <label for="video_title">Video Title:</label>
            <input type="text" name="video_title" required>
        </div>

        <div class="text-center p-4">
            <button class="mb-20 ttm-btn ttm-btn-bgcolor-black ttm-btn-shape-round ttm-btn-size-md ttm-btn-style-fill" type="submit">Submit</button>
        </div>
    </form>

    </div>
</div></div></div></section>


<div class="container-fluid cta-section">
    <div class="row">
        <!-- Left Side with Overlay and Text -->
        <div class="col-md-6 cta-overlay d-flex align-items-center">
            <div class="px-4 cta-content">
                <h1>Are you looking for a diet plan?</h1>
                <p>Join now to get your personalized plan!</p>
                <a href="/form" class="btn btn-primary">Join Now</a>
            </div>
        </div>
        
        <!-- Right Side without Overlay -->
        <div class="col-md-6 cta-right">
            <!-- Empty Div to maintain image's original appearance -->
        </div>
    </div>
</div>


<?php include '../footer.php'; ?>
