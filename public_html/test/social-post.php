<?php
session_start();

// Paths to the cached and backup JSON files
$cacheFile = __DIR__ . '/insta_posts.json';
$backupFile = __DIR__ . '/insta_posts-backup.json';

// Helper function to check if a file contains valid JSON
function isValidJsonFile($file) {
    if (file_exists($file) && filesize($file) > 0) {
        $fileContents = file_get_contents($file);
        $jsonData = json_decode($fileContents, true);
        return $jsonData !== null ? $jsonData : false;
    }
    return false;
}

// Function to remove emojis and non-ASCII characters
function removeEmojis($text) {
    return preg_replace('/[^\x20-\x7E]/', '', $text); // Removes non-ASCII characters including emojis
}

// Function to limit text to a specific character length
function limitText($text, $charLimit = 100) {
    return (strlen($text) > $charLimit) ? substr($text, 0, $charLimit) . '...' : $text;
}

// Function to clean, remove emojis, and limit text in one step
function cleanAndLimit($text, $charLimit = 100) {
    $text = removeEmojis($text); // Remove emojis and stickers
    return limitText($text, $charLimit); // Limit the text to the desired length
}

// Load data from the main cache file or fallback to the backup file
$posts = isValidJsonFile($cacheFile) ?: isValidJsonFile($backupFile);
if (!$posts) {
    die("Error: Unable to load valid Instagram post data.");
}

// Fetch the post ID from the URL
if (isset($_GET['id'])) {
    $postId = $_GET['id'];

    // Find the specific post by ID
    $post = null;
    foreach ($posts as $p) {
        if ($p['id'] === $postId) {
            $post = $p;
            break;
        }
    }

    if (!$post) {
        header("Location: /error");
        exit;
    }
} else {
    header("Location: /error");
    exit;
}

// Load recommendations (random 8 posts excluding the current one and videos) 
$recommendationLimit = 8;

// Filter out the current post and videos
$otherPosts = array_filter($posts, function ($p) use ($postId) {
    // Exclude current post and video posts
    return $p['id'] !== $postId && strpos($p['media_url'], 'video') === false;
});

// Shuffle the remaining posts to randomize
shuffle($otherPosts);

// Get exactly 8 recommendations (or fewer if there aren't enough)
$recommendations = array_slice($otherPosts, 0, $recommendationLimit);



// Create meta keywords from the caption
$metaKeywords = implode(', ', array_slice(explode(' ', $post['caption']), 0, 40));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- Optimized Title and Meta Tags -->
<?php $title = cleanAndLimit($post['caption'], 100) . ' | Dietician Priyanka'; ?>
<title><?php echo $title; ?></title>

<meta name="twitter:title" content="<?php echo $title; ?>">

<meta property="og:title" content="<?php echo $title; ?>">

    <meta name="description" content="<?php echo htmlspecialchars($post['caption']); ?>">
    <meta name="keywords" content="<?php echo htmlspecialchars($metaKeywords); ?>">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:image" content="<?php echo $post['media_url']; ?>">
    <meta name="twitter:description" content="<?php echo htmlspecialchars($post['caption']); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($post['caption']); ?>">
    <meta property="og:url" content="https://www.biteanddiet.in/social_post_details?id=<?php echo urlencode($postId); ?>">
    <link rel="canonical" href="https://www.biteanddiet.in/social_post_details?id=<?php echo urlencode($postId); ?>">

<!-- JSON-LD for SEO -->
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "WebPage",
    "url": "https://www.biteanddiet.in/social_post_details?id=<?php echo urlencode($postId); ?>",
"name": "<?php echo htmlspecialchars(cleanAndLimit($post['caption'], 100)); ?> | Dietician Priyanka",

    
    "description": "<?php echo htmlspecialchars($post['caption']); ?>",
    "thumbnailUrl": "https://www.biteanddiet.in<?php echo $post['media_url']; ?>",
    "mainEntity": {
        "@type": "ItemList",
        "itemListElement": [
            {
                "@type": "ImageObject",
                "position": 1,
              "name": "<?php echo htmlspecialchars(cleanAndLimit($post['caption'], 100)); ?> | Dietician Priyanka",

                "thumbnailUrl": "https://www.biteanddiet.in<?php echo $post['media_url']; ?>",
                "contentUrl": "https://www.biteanddiet.in/social_post_details?id=<?php echo urlencode($postId); ?>",
                "description": "<?php echo htmlspecialchars($post['caption']); ?>",
                "publisher": {
                    "@type": "Organization",
                    "name": "Bite & Diet - Diet Consultation",
                    "logo": {
                        "@type": "ImageObject",
                        "url": "https://www.biteanddiet.in/images/big_logo.png"
                    }
                },
                "author": {
                    "@type": "Person",
                    "name": "Dietician Priyanka"
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

.social-post-details{
    padding:4px;
    border:2px solid #999;
    border-radius:12px;
}
.social-posts-recommendations{

        max-height :250px;

    }
    
    

/* Media query for tablet devices (992px and below) */
@media (max-width: 992px) {
    .cta-section {
        background-position: right center; /* Focus on the right side of the image for tablets */
    }
    .social-posts-recommendations{

        max-height :unset;

    }
}

@media (max-width: 768px) {
    .cta-section {
        background-position: right center; /* Shift focus to the right side of the image */
    }
    .social-posts-recommendations{
        max-height :unset;
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
                            <h1 class="title">
<?php echo cleanAndLimit(removeEmojis($post['caption']), 100); ?>
  

    </h1>
                        </div>
                        <div class="breadcrumb-wrapper">
                            <span><a href="./" title="Homepage"><i class="ti ti-home"></i> Home </a></span>
                            <span class="ttm-bread-sep">: : </span>Social Post Details
                            <span class="ttm-bread-sep">: : </span>
                           <span>
    <span class="ttm-textcolor-skincolor">
<?php echo cleanAndLimit(removeEmojis($post['caption']), 100); ?>

</span>

</span>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

 <section class="break-991-colum checkout-section clearfix ttm-row">
       
<div class="container"> 
    <div class="row">
        <div class="col-lg-12 mt-5 col-md-12">
            <header class="text-center section-title">
                <h5>Social Post</h5>
            </header>

            <h1 class="custom_heading text-center mb-30">
                <?php echo cleanAndLimit(removeEmojis($post['caption']), 100); ?>
            </h1>

            <!-- Media Section -->
            <div class="row justify-content-center">
                <?php if (strpos($post['media_url'], 'video') !== false): ?>
                    <div class="col-md-12">
                        <div class="embed-responsive embed-responsive-16by9">
                            <video controls class="embed-responsive-item">
                                <source src="<?php echo $post['media_url']; ?>" type="video/mp4">
                                Your browser does not support the video tag.
                            </video>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="col-md-6 col-12">
                        <img src="<?php echo $post['media_url']; ?>" class="img-fluid social-post-details" 
                             alt="<?php echo cleanAndLimit(removeEmojis($post['caption']), 100); ?>">
                    </div>
                <?php endif; ?>
            </div>

<!-- Caption Section -->
<div class="mt-40">
    <?php
    function convertUrlsToLinks($text) {
        // Match all URLs in the text
        $urlPattern = '/(https?:\/\/[^\s]+)/i';

        // Replace URLs with the word "link" that redirects to the actual URL
        return preg_replace($urlPattern, '<b><a href="$1" target="_blank" rel="noopener noreferrer">link</a></b>', $text);
    }

    // Process caption for display with "link" replacing URLs
    $captionWithLinks = convertUrlsToLinks($post['caption']);
    ?>

    <!-- Display Caption -->
    <p id="postCaption" class="mb-40">
        <?php echo nl2br(html_entity_decode($captionWithLinks)); ?>
    </p>

    <!-- Hidden Raw Caption for Copy -->
    <textarea id="hiddenRawCaption" style="display: none;"><?php echo htmlspecialchars_decode($post['caption']); ?></textarea>
</div>

<!-- Buttons Row: Copy + Share -->
<div class="row justify-content-center">
    <!-- Copy Button -->
    <div class="col-auto">
        <button id="copyButton" class="ttm-btn mb-20 ttm-btn-shape-round ttm-btn-size-md ttm-btn-style-fill ttm-btn-bgcolor-black">Copy</button>
    </div>

    <!-- Share Button -->
    <div class="col-auto">
        <button class="ttm-btn mb-20 ttm-btn-shape-round ttm-btn-size-md ttm-btn-style-fill ttm-btn-bgcolor-skincolor" onclick="sharePage()">Share</button> 
    </div>
</div>

<!-- Reserved Space for Alert -->
<div id="copyAlert" class="alert alert-success text-center" role="alert" style="visibility: hidden; opacity: 0; transition: opacity 0.3s;">
    Caption copied to clipboard!
</div>

<!-- Scripts -->
<script>
// Copy Caption Button
document.getElementById('copyButton').addEventListener('click', function () {
    const caption = document.getElementById('hiddenRawCaption');
    caption.select();
    caption.setSelectionRange(0, 99999); // For mobile devices
    document.execCommand('copy');

    const alertBox = document.getElementById('copyAlert');
    alertBox.style.visibility = 'visible';
    alertBox.style.opacity = '1';
    setTimeout(() => {
        alertBox.style.opacity = '0';
        alertBox.style.visibility = 'hidden';
    }, 2000);
});

function sharePage() {
    const pageUrl = window.location.href;

    // Get and clean title (remove branding suffix if any)
    let rawTitle = document.title;
    let cleanedTitle = rawTitle.replace(/\s*\|\s*Dietician Priyanka\s*/i, '').trim();

    // Ensure the correct format with proper new lines
    const fullMessage = `${cleanedTitle}\n\nRead More:\n${pageUrl}`;

    if (navigator.share) {
        navigator.share({
            title: cleanedTitle,
            text: fullMessage
        }).then(() => {
            console.log('Thanks for sharing!');
        }).catch(console.error);
    } else {
        const encodedMessage = encodeURIComponent(fullMessage);
        const whatsappUrl = "https://wa.me/?text=" + encodedMessage;
        window.open(whatsappUrl, '_blank');
    }
}

</script>


        </div>
    </div>
</div>

<script>
    // JavaScript for Copying the Full Caption
document.getElementById('copyButton').addEventListener('click', function () {
    // Get the hidden raw caption
    const hiddenCaption = document.getElementById('hiddenRawCaption');

    // Select the raw text in the textarea
    hiddenCaption.style.display = 'block'; // Temporarily show it
    hiddenCaption.select(); // Select the content
    hiddenCaption.setSelectionRange(0, hiddenCaption.value.length); // For mobile compatibility
    document.execCommand('copy'); // Copy to clipboard
    hiddenCaption.style.display = 'none'; // Hide it again

    // Show success message
    const copyAlert = document.getElementById('copyAlert');
    copyAlert.style.visibility = 'visible';
    copyAlert.style.opacity = '1';
    setTimeout(() => {
        copyAlert.style.visibility = 'hidden';
        copyAlert.style.opacity = '0';
    }, 3000);
});

</script>                
                
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

                    <p class="text-right mb-0"><span class="font-italic small text-muted">posted on:   </span>&nbsp<?php echo date('d M Y', strtotime($post['timestamp'])); ?></p>
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

 
            
             
        <div class="container mb-50">
            <div class="clearfix section-title">
        <div class="title-header">
            <h5>Recommended Social Posts</h5>
        </div>
    </div>

<div class="row"> 
    <?php foreach ($recommendations as $recommend): ?>

        <?php 
        // Skip video posts
        if (strpos($recommend['media_url'], 'video') !== false) {
            continue;
        }
        ?>

        <div class="col-lg-3 col-md-4 col-sm-6">
            <div class="card mb-20">
                <a href="social_post_details?id=<?php echo urlencode($recommend['id']); ?>">
                    <img 
                        loading="lazy" 
                        src="<?php echo $recommend['media_url']; ?>" 
                        class="card-img-top social-posts-recommendations" 
                        alt="<?php echo cleanAndLimit(removeEmojis($recommend['caption']), 80); ?>"
                        style="max-height: 400px; object-fit:cover"
                    >
                </a>
                <div class="card-body">
                    <p class="card-text">
                        <?php echo cleanAndLimit(removeEmojis($recommend['caption']), 80); ?>
                    </p>
                    <a href="social_post_details?id=<?php echo urlencode($recommend['id']); ?>" class="btn btn-primary btn-sm">View Post</a>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

            
            <div class="container">
        <div class="row" id="recommendations-row">
            <!-- Cards will be appended here by JavaScript -->
        </div>
        <hr>
        <div class="text-center p-4">
            <a href="/social-posts" class="ttm-btn my-5 ttm-btn-shape-round ttm-btn-size-md ttm-btn-style-fill ml-15 ttm-btn-bgcolor-skincolor">Back to Social Posts</a>
        </div>
    </div>
            
            
        </div>
    
     </section>
    

<?php include 'footer.php'; ?>
