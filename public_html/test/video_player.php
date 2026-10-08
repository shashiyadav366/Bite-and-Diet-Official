<?php
session_start();
?>

<?php
// Handle video title and ID from GET parameters
$videoTitle = isset($_GET['title']) ? htmlspecialchars($_GET['title']) : 'Video';
$videoId = isset($_GET['videoId']) ? htmlspecialchars($_GET['videoId']) : '';

// Paths to the cached JSON files
$cacheFile = 'youtube_videos.json'; // Ensure this path is correct
$backupFile = 'youtube_videos-backup.json'; // Path to the backup file

$videoDescription = ''; // Initialize the description variable
$videoTitleJson = '';
$displayContent = '';
$recommendations = [];

// Set a default thumbnail if not provided in the data
$defaultThumbnailUrl = 'https://www.biteanddiet.in/images/youtube-default-thumbnail.webp'; 

// Number of recommendation videos to show
$recommendationLimit = 8;

// Function to check if a file contains valid JSON
function isValidJsonFile($file) {
    if (file_exists($file) && filesize($file) > 0) {
        $fileContents = file_get_contents($file);
        $jsonData = json_decode($fileContents, true);
        return $jsonData !== null ? $jsonData : false;
    }
    return false;
}

// Try to load data from the main cache file
$videoData = isValidJsonFile($cacheFile);

// If the main cache file is invalid or empty, fallback to the backup file
if (!$videoData) {
    $videoData = isValidJsonFile($backupFile);
    if (!$videoData) {
        // If both the main and backup files are invalid, redirect to the error page
        header("Location: /error");
        exit();
    }
}

// Find the description and title for the given videoId
$postFound = false;
foreach ($videoData as $video) {
    if ($video['videoId'] === $videoId) {
        $videoDescription = $video['description'];
        $videoTitleJson = $video['title'];
        $videopublishedAt = $video['publishedAt'];
        
        // Create a DateTime object from the ISO 8601 string
        $dateTime = new DateTime($videopublishedAt);
        // Format the date to a more readable format
        $formattedDate = $dateTime->format('F j, Y'); // Example format: February 21, 2021
        $postFound = true;

        $videoTitleJson = html_entity_decode($videoTitleJson, ENT_QUOTES, 'UTF-8');
        // Determine the content to display
        $displayContent = !empty($videoDescription) ? $videoDescription : ($videoTitleJson . ' - Dietician Priyanka');
        // Decode HTML entities
        $displayContent = html_entity_decode($displayContent, ENT_QUOTES, 'UTF-8');
        break;
    }
}

if (!$postFound) {
    // Redirect to /error page if no post was found
    header("Location: /error");
    exit();
}

// Filter out the current video from the list
$otherVideos = array_filter($videoData, function($video) use ($videoId) {
    return $video['videoId'] !== $videoId;
});

// Shuffle and select a random subset of videos for recommendations
shuffle($otherVideos);
$recommendations = array_slice($otherVideos, 0, $recommendationLimit);

// Create meta keywords by combining the video title and the first 10 words of the description
$metaKeywords = $videoTitleJson . ', ' . implode(', ', array_slice(explode(' ', $videoDescription), 0, 10));

?>




<!DOCTYPE html>
<html lang="en">
<head>
    <title><?php echo $videoTitleJson; ?></title>
    <meta name="twitter:title" content="<?php echo $videoTitleJson; ?>">
    <meta property="og:title" content="<?php echo $videoTitleJson; ?>">
    <meta name="description" content="<?php echo $displayContent; ?>">
    <meta name="keywords" content="<?php echo $metaKeywords; ?>">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:image" content="https://i.ytimg.com/vi/<?php echo $videoId; ?>/hqdefault.jpg">
    <meta name="twitter:description" content="<?php echo $displayContent; ?>">
    <meta property="og:description" content="<?php echo $displayContent; ?>">
    <meta property="og:url" content="https://www.biteanddiet.in/video_player?title=<?php echo urlencode($videoTitleJson); ?>&videoId=<?php echo $videoId; ?>">
    <link rel="canonical" href="https://www.biteanddiet.in/video_player?title=<?php echo urlencode($videoTitleJson); ?>&videoId=<?php echo $videoId; ?>">

<!-- JSON-LD for SEO -->
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "WebPage",
    "url": "https://www.biteanddiet.in/video_player?title=<?php echo urlencode($videoTitleJson); ?>&videoId=<?php echo $videoId; ?>",
    "name": "<?php echo $videoTitleJson; ?>",
    "description": "<?php echo 
    $displayContent; ?>",
    "thumbnailUrl": "https://i.ytimg.com/vi/<?php echo $videoId; ?>/hqdefault.jpg",
    "mainEntity": {
        "@type": "ItemList",
        "itemListElement": [
            {
                "@type": "VideoObject",
                "position": 1,
                "name": "<?php echo $videoTitleJson; ?>",
                "uploadDate": "<?php echo date('c', strtotime($uploadDate)); ?>", 
                "thumbnailUrl": "https://i.ytimg.com/vi/<?php echo $videoId; ?>/hqdefault.jpg",
                "contentUrl": "https://www.biteanddiet.in/video_player?title=<?php echo urlencode($videoTitleJson); ?>&videoId=<?php echo $videoId; ?>",
                "embedUrl": "https://www.biteanddiet.in/video_player?title=<?php echo urlencode($videoTitleJson); ?>&videoId=<?php echo $videoId; ?>",
                "description": "<?php echo $displayContent; ?>",
                "publisher": {
                    "@type": "Organization",
                    "name": "Bite & Diet - Diet Consultation",
                    "author": "Dietician Priyanka",
                    "logo": {
                        "@type": "ImageObject",
                        "url": "https://www.biteanddiet.in/images/big_logo.png"
                    }
                }
            }
        ]
    }
}
</script>

<style>
    .company-info .footer .social-icons li>a{

    width:45px;

    height:45px;
    line-height:45px;
    color:#fff;
}
 .post-writer {

        justify-content: end;

    }

@media (max-width: 767px) {
    .post-writer {
        justify-content: start;
    }
}


.cta-section {

/* Ensure full height of the viewport */

    
    background: url('/images/bite_and_diet-cta.webp') center center no-repeat;
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
    padding:110px;
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
<?php
$disable_header = false; 
include 'Header.php';
?>
    <div class="ttm-page-title-row">
        <div class="ttm-bg-layer ttm-page-title-row-bg-layer"></div>
        <div class="container">
            <div class="row">
                <div class="text-center col-md-12">
                    <div class="ttm-textcolor-white title-box">
                        <div class="ttm-textcolor-white page-title-heading">
                            <h1 class="title">                            <?php echo $videoTitleJson; ?></h1>
                        </div>
                        <div class="breadcrumb-wrapper">
                            <span><a href="./" title="Homepage"><i class="ti ti-home"></i> Home </a></span>
                            <span class="ttm-bread-sep">: : </span>Videos
                            <span class="ttm-bread-sep">: : </span>
                            <span><span class="ttm-textcolor-skincolor"><?php echo $videoTitleJson; ?></span></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <section class="break-991-colum checkout-section clearfix ttm-row">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 mt-5">
                    
                        <header class="text-center section-title"><h5>Video</h5></header>
                        <h1 class="custom_heading text-center mb-30"> 
                            <?php echo $videoTitleJson; ?>
                        </h1>


<div class="row justify-content-center">
        <div class="col-12 col-md-10">
            <div class="embed-responsive embed-responsive-16by9">
                <iframe class="embed-responsive-item" src="https://www.youtube.com/embed/<?php echo htmlspecialchars($videoId); ?>" allowfullscreen></iframe>
            </div>
        </div>
    </div>
                        
                        <div class="clearfix section-title mt-10">


<div class="title-header mb-50">
    <h5>Description</h5>
   
   <p class="ttm-box-description">
        
        <?php echo htmlspecialchars($displayContent); ?></p>


    
    
    
    <div class="mt-30">
        <pre style="white-space: pre-wrap; font-family: inherit; font-size: inherit;">
🍎🥑🥦 <b><span class="red-title">Bite And Diet</span></b> by<b> <a href="/aboutdtpriyanka">Dietician Priyanka</a></b> 🏋️‍♂️🍽️ who has more than 10 years of experience, provides personalized diet plans for various health issues such as weight management, diabetes, high blood pressure, heart disease, cholesterol management, PCOS/PCOD, knee pain, back pain, chronic cough, fatty liver, digestive problems, IBS, and arthritis. She helps clients reduce their dependence on medicine and live a healthier life.

Join us on our journey to a happier & healthier life! 🚀
<b>💯 3000+ Total Customers
😃 2500+ Satisfied Customers
👥 500 Active Customers
⭐ 5 Star rated services
</b>Let us help you achieve your health goals! 💪🏼.

Don't wait any longer?
Book an appointment now!!
Call: <b><a href="tel:+918826549878">Call Now</a> </b>
WhatsApp: <b><a href="https://wa.me/+918826549878">WhatsApp Now</a></b>

Website: <b><a href="/form">Join Now</a></b>
and start your journey towards better health! 💚🙌
        </pre>
    </div>
 
 

<!-- Share Button -->
<div class="text-center">
    <button class="ttm-btn ttm-btn-shape-round ttm-btn-size-md ttm-btn-style-fill ttm-btn-bgcolor-skincolor" onclick="shareVideo()">Share</button> 
</div>   

<script>
function shareVideo() {
    const description = `<?php echo addslashes($displayContent); ?>`;
    //const videoUrl = "https://www.youtube.com/watch?v=<?php echo htmlspecialchars($videoId); ?>";
    const videoUrl = window.location.href;
    const fullMessage = `${description}\n\nWatch To Know More:\n${videoUrl}`;

    if (navigator.share) {
        navigator.share({
            title: "Watch To Know More",
            text: fullMessage
            // Do NOT set 'url' here, otherwise URL comes twice
        }).then(() => {
            console.log('Thanks for sharing!');
        })
        .catch(console.error);
    } else {
        // Fallback for browsers that don't support Web Share API
        const message = encodeURIComponent(fullMessage);
        const whatsappUrl = "https://wa.me/?text=" + message;
        window.open(whatsappUrl, '_blank');
    }
}
</script>



</div>



                                        </div>
            </div>
        </div>
                       
                    </div>
                    
                <div class="mt-50">
  <div class="container cta-section">
    <div class="row">

      <!-- Left Side with Overlay and Text -->
      <div class="col-md-6 cta-overlay d-flex align-items-center order-2 order-md-1">
        <div class="px-4 cta-content">
          <h1>Are you looking for a diet plan?</h1>
          <p>Join now to get your personalized plan!</p>
          <a href="/form" class="btn btn-primary">Join Now</a>
        </div>
      </div>

      <!-- Right Side without Overlay -->
      <div class="col-md-6 cta-right order-1 order-md-2">
        <!-- Empty Div to maintain image's original appearance -->
      </div>

    </div>
  </div>
</div>


                    <div class="company-info container py-4">

    <div class="row mt-20 align-items-center">

        <!-- Text and Image on the right side for larger screens, first for phones -->
        <div class="col-12 col-md-6 text-left my-4 order-1 order-lg-2">
            <div class="post-writer d-flex align-items-center">
                <!-- Image on the left -->
                <div>
                    <img src="/images/dietician_priyanka.webp" class="img-fluid rounded-circle" alt="Dietician Priyanka" style="width: 100px; height: 100px;">
                </div>
                <!-- Text on the right -->
                <div class="ml-3">

                    <p class="text-right mb-0"><span class="font-italic small text-muted">posted on:   </span>&nbsp<?php echo $formattedDate; ?></p>
                                   <p class=""><span class="font-italic small text-muted">by:&nbsp</span><b>Dietician Priyanka</b></p>
                </div>
            </div>
        </div>

        <!-- Social media icons on the left side for larger screens, second for phones -->
        <div class="footer col-12 col-md-6 text-left my-4 order-2 order-lg-1">
            <h3 class="mb-3">Follow Us:</h3>
            <div class="social-icons">
                <ul class="list-inline">
                    <li class="list-inline-item social-whatsapp">
                        <a href="https://wa.me/+918826549878" class="tooltip-top" data-tooltip="WhatsApp">
                            <i class="fa fa-whatsapp" aria-hidden="true"></i>
                        </a>
                    </li>
                    <li class="list-inline-item social-facebook">
                        <a href="https://www.facebook.com/YourBiteMyDiet" title="Facebook" target="_blank" class="tooltip-top" data-tooltip="Facebook">
                            <i class="fa fa-facebook" aria-hidden="true"></i>
                        </a>
                    </li>
                    <li class="list-inline-item social-linkedin">
                        <a href="https://www.linkedin.com/company/bite-and-diet-nutritionist-consultation/" title="Linkedin" target="_blank" class="tooltip-top" data-tooltip="Linkedin">
                            <i class="fa fa-linkedin" aria-hidden="true"></i>
                        </a>
                    </li>
                    <li class="list-inline-item social-instagram">
                        <a href="https://www.instagram.com/bite_and_diet/" title="Instagram" target="_blank" class="tooltip-top" data-tooltip="Instagram">
                            <i class="fa fa-instagram" aria-hidden="true"></i>
                        </a>
                    </li>
                    <li class="list-inline-item social-youtube">
                        <a href="https://www.youtube.com/channel/UCwl1Lbkl0PhwYm8j3j8fo1A" title="Youtube" target="_blank" class="tooltip-top" data-tooltip="Youtube">
                            <i class="fa fa-youtube" aria-hidden="true"></i>
                        </a>
                    </li>
                    <li class="list-inline-item social-quora">
                        <a href="https://www.quora.com/profile/BiteandDiet" title="Quora" target="_blank" class="tooltip-top" data-tooltip="Quora">
                            <i class="fa fa-quora" aria-hidden="true"></i>
                        </a>
                    </li>
                    <li class="list-inline-item social-twitter">
                        <a href="https://twitter.com/Biteandiet" title="Twitter" target="_blank" class="tooltip-top" data-tooltip="Twitter">
                            <i class="fa fa-twitter" aria-hidden="true"></i>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <hr class="mx-3">


</div>





<!-- Recommendations Section -->
<div class="container">
    <div class="clearfix section-title">
        <div class="title-header">
            <h5>Recommended Videos</h5>
        </div>
    </div>

    <div class="container">
        <div class="row" id="recommendations-row">
            <!-- Cards will be appended here by JavaScript -->
        </div>
        <hr>
        <div class="text-center p-4">
            <a href="/allvideos" class="ttm-btn my-5 ttm-btn-shape-round ttm-btn-size-md ttm-btn-style-fill ml-15 ttm-btn-bgcolor-skincolor">Back to Videos</a>
        </div>
    </div>
</div>
    </section>


<script>
    document.addEventListener('DOMContentLoaded', function() {
        const recommendations = <?php echo json_encode($recommendations); ?>;
        const container = document.getElementById('recommendations-row');

        function renderVideos(videos) {
            container.innerHTML = ''; // Clear the container

            // Show 8 videos with the first 4 in larger format
            videos.slice(0, 4).forEach(video => {
                if (video.videoId) {
                    const thumbnailUrl = video.thumbnail ? video.thumbnail : "<?php echo $defaultThumbnailUrl; ?>";
                    const cardDiv = document.createElement('div');
                    cardDiv.className = 'col-lg-3 col-md-3 col-sm-12 mb-4'; // Ensures 4 cards per row on medium screens and larger

                    cardDiv.innerHTML = `
                        <div class="card" style="margin: 0 0 40px 0;">
                            <a href="video_player?title=${encodeURIComponent(video.title).replace(/%20/g, "+")}&videoId=${video.videoId}" class="video-thumbnail">
                                <img src="${thumbnailUrl}" class="card-img-top" alt="${video.title}" style="object-fit: cover;">
                                <div class="overlay"></div>
                                <div class="play-button"></div>
                            </a>
                            <div class="card-body">
                                <h6 class="card-title">${video.title}</h6>
                            </div>
                        </div>
                    `;

                    container.appendChild(cardDiv);
                }
            });

            // Show the next 4 videos in smaller format
            videos.slice(4, 8).forEach(video => {
                if (video.videoId) {
                    const thumbnailUrl = video.thumbnail ? video.thumbnail : "<?php echo $defaultThumbnailUrl; ?>";
                    const cardDiv = document.createElement('div');
                    cardDiv.className = 'col-md-3 mb-4'; // Ensures 4 cards per row on medium screens and larger

                    cardDiv.innerHTML = `
                        <div class="card" style="margin: 0 0 20px 0;">
                            <a href="video_player?title=${encodeURIComponent(video.title).replace(/%20/g, "+")}&videoId=${video.videoId}" class="video-thumbnail">
                                <img src="${thumbnailUrl}" class="card-img-top" alt="${video.title}" style="object-fit: cover;">
                                <div class="overlay"></div>
                                <div class="play-button"></div>
                            </a>
                            <div class="card-body">
                                <h6 class="card-title">${video.title}</h6>
                            </div>
                        </div>
                    `;

                    container.appendChild(cardDiv);
                }
            });
        }

        renderVideos(recommendations);
    });
</script>


<?php include 'footer.php'; ?>

