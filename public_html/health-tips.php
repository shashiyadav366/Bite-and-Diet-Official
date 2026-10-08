<?php 
session_start(); 

$cacheFile = __DIR__ . '/health-tips.json'; 
$backupFile = __DIR__ . '/health-tips-backup.json'; 

function isValidJsonFile($file) { 
    if (file_exists($file) && filesize($file) > 0) { 
        $jsonData = json_decode(file_get_contents($file), true); 
        return $jsonData ?: false; 
    } 
    return false; 
} 

function removeEmojis($text) { 
    return preg_replace('/[^\x20-\x7E]/', '', $text); 
} 

function limitText($text, $charLimit = 100) { 
    return (strlen($text) > $charLimit) ? substr($text, 0, $charLimit) . '...' : $text; 
} 

function cleanAndLimit($text, $charLimit = 100) { 
    return limitText(removeEmojis($text), $charLimit); 
} 

$posts = isValidJsonFile($cacheFile) ?: isValidJsonFile($backupFile); 
if (!$posts) { 
    die("Error: Unable to load health tips."); 
} 

// Get slug from URL
$slug = trim($_GET['slug'] ?? '', " \t\n\r\0\x0B/"); // trim spaces and slashes

$post = null;
foreach ($posts as $p) {
    if (trim($p['slug']) === $slug) { // trim JSON slug too
        $post = $p;
        break;
    }
}

if (!$post) {
   // header("HTTP/1.0 404 Not Found");
            header("Location: /error");
            
    //include '404.php';
    exit;
}
// ==========================================
// EDIT HEALTH TIP FUNCTIONALITY
// ==========================================

$postIndex = null;

foreach ($posts as $index => $p) {

    if (trim($p['slug']) === $slug) {

        $postIndex = $index;
        break;
    }
}

if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    &&
    isset($_POST['update_post'])
    &&
    isset($_SESSION['loggedin'])
    &&
    $_SESSION['loggedin'] === true
) {

    // UPDATE TITLE
    $posts[$postIndex]['title'] =
        trim($_POST['edit_title']);

    // UPDATE CAPTION
    $posts[$postIndex]['caption'] =
        trim($_POST['edit_caption']);

    // UPDATE IMAGE
    if (
        isset($_FILES['edit_image'])
        &&
        $_FILES['edit_image']['error'] === 0
    ) {

        $uploadDir =
            __DIR__ . '/uploads/health-tips/';

        if (!file_exists($uploadDir)) {

            mkdir($uploadDir, 0777, true);
        }

        $extension =
            strtolower(
                pathinfo(
                    $_FILES['edit_image']['name'],
                    PATHINFO_EXTENSION
                )
            );

        $newFileName =
            'health-tip-' .
            time() .
            '.' .
            $extension;

        $targetFile =
            $uploadDir .
            $newFileName;

        if (
            move_uploaded_file(
                $_FILES['edit_image']['tmp_name'],
                $targetFile
            )
        ) {

            $posts[$postIndex]['media_url'] =
                '/uploads/health-tips/' .
                $newFileName;
        }
    }

    // BACKUP JSON

    if (file_exists($cacheFile)) {

        copy($cacheFile, $backupFile);
    }

    // SAVE UPDATED JSON

    file_put_contents(

        $cacheFile,

        json_encode(

            $posts,

            JSON_PRETTY_PRINT |
            JSON_UNESCAPED_UNICODE |
            JSON_UNESCAPED_SLASHES
        )
    );

    require_once __DIR__ . '/admin/sitemap_lib.php';
    sitemap_generate_if_stale();

    header("Location: " . $_SERVER['REQUEST_URI']);
    exit;
}

// Recommendations
$recommendationLimit = 8;
$otherPosts = array_filter($posts, function ($p) use ($post) {
    return $p['id'] !== $post['id'] && strpos($p['media_url'], 'video') === false;
});
shuffle($otherPosts);
$recommendations = array_slice($otherPosts, 0, $recommendationLimit);

$metaKeywords = implode(', ', array_slice(explode(' ', $post['caption']), 0, 40)); 
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- Optimized Title and Meta Tags -->
<?php $title = $post['title'] . ' | Dietician Priyanka'; ?>
<title><?php echo $title; ?></title>

<meta name="twitter:title" content="<?php echo $title; ?>">

<meta property="og:title" content="<?php echo $title; ?>">

    <meta name="description" content="<?php echo htmlspecialchars($post['caption']); ?>">
    <meta name="keywords" content="<?php echo htmlspecialchars($metaKeywords); ?>">
    <meta name="robots" content="index, follow">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:description" content="<?php echo htmlspecialchars($post['caption']); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($post['caption']); ?>">
   <meta property="og:url" content="https://www.biteanddiet.in/health-tips/<?php echo $post['slug']; ?>">
<link rel="canonical" href="https://www.biteanddiet.in/health-tips/<?php echo $post['slug']; ?>">


<!-- JSON-LD for SEO -->
<script type="application/ld+json">
<?php
$htCaption = trim(html_entity_decode(strip_tags($post['caption']), ENT_QUOTES, 'UTF-8'));
$htMedia = (strpos($post['media_url'], 'http') === 0) ? $post['media_url'] : 'https://www.biteanddiet.in' . $post['media_url'];
$htName = html_entity_decode($post['title'], ENT_QUOTES, 'UTF-8') . ' | Dietician Priyanka';
$healthTipJsonLd = [
    "@context" => "https://schema.org",
    "@type" => "WebPage",
    "url" => "https://www.biteanddiet.in/health-tips/" . $post['slug'],
    "name" => $htName,
    "description" => $htCaption,
    "thumbnailUrl" => $htMedia,
    "mainEntity" => [
        "@type" => "ItemList",
        "itemListElement" => [[
            "@type" => "ImageObject",
            "position" => 1,
            "name" => $htName,
            "thumbnailUrl" => $htMedia,
            "contentUrl" => "https://www.biteanddiet.in/health-tips/" . $post['slug'],
            "description" => $htCaption,
            "publisher" => [
                "@type" => "Organization",
                "name" => "Bite & Diet - Diet Consultation",
                "logo" => [
                    "@type" => "ImageObject",
                    "url" => "https://www.biteanddiet.in/images/big_logo.png"
                ]
            ],
            "author" => [
                "@type" => "Person",
                "name" => "Dietician Priyanka"
            ]
        ]]
    ]
];
echo json_encode($healthTipJsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>
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
<?php echo ($post['title']); ?>
  

    </h1>
                        </div>
                        <div class="breadcrumb-wrapper">
                            <span><a href="/" title="Homepage"><i class="ti ti-home"></i> Home </a></span>
                            <span class="ttm-bread-sep">: : </span>Health Tips
                            <span class="ttm-bread-sep">: : </span>
                           <span>
    <span class="ttm-textcolor-skincolor">
<?php echo ($post['title']); ?>

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
                <h5>Health Tips</h5>
            </header>

            <h1 class="custom_heading text-center mb-30">
                <?php echo ($post['title']); ?>
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

<!-- Buttons Row: Copy + Share + Edit -->
<div class="row justify-content-center align-items-center">

    <!-- Copy Button -->
    <div class="col-auto">
        <button 
            id="copyButton" 
            class="ttm-btn mb-20 ttm-btn-shape-round ttm-btn-size-md ttm-btn-style-fill ttm-btn-bgcolor-black"
        >
            Copy
        </button>
    </div>

    <!-- Share Button -->
    <div class="col-auto">
        <button 
            class="ttm-btn mb-20 ttm-btn-shape-round ttm-btn-size-md ttm-btn-style-fill ttm-btn-bgcolor-skincolor" 
            onclick="sharePage()"
        >
            Share
        </button>
    </div>

    <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) { ?>

        <!-- Edit Button -->
        <div class="col-auto">
            <button
                type="button"
                class="ttm-btn mb-20 ttm-btn-shape-round ttm-btn-size-md ttm-btn-style-fill ttm-btn-bgcolor-black"
                onclick="toggleEditForm()"
            >
                Edit
            </button>
        </div>

    <?php } ?>

</div>

<?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) { ?>

<div
    id="editPostForm"
    style="
        display:none;
        margin-top:30px;
        padding:25px;
        border:2px solid #ddd;
        border-radius:15px;
        background:#fff;
    "
>

    <form method="POST" enctype="multipart/form-data">

        <div class="form-group mb-4">

            <label><b>Edit Title</b></label>

            <input
                type="text"
                name="edit_title"
                class="form-control"
                value="<?php echo htmlspecialchars($post['title']); ?>"
                required
            >

        </div>

        <div class="form-group mb-4">

            <label><b>Edit Content</b></label>

            <textarea
                name="edit_caption"
                class="form-control"
                rows="10"
                required
            ><?php echo htmlspecialchars($post['caption']); ?></textarea>

        </div>

        <div class="form-group mb-4">

            <label><b>Current Image</b></label>

            <br>

            <img
                src="<?php echo $post['media_url']; ?>"
                style="
                    width:220px;
                    border-radius:12px;
                    border:2px solid #ccc;
                "
            >

        </div>

        <div class="form-group mb-4">

            <label><b>Replace Image</b></label>

            <input
                type="file"
                name="edit_image"
                class="form-control"
                accept="image/*"
            >

        </div>

        <button
            type="submit"
            name="update_post"
            class="ttm-btn mb-20 ttm-btn-shape-round ttm-btn-size-md ttm-btn-style-fill ttm-btn-bgcolor-black"
        >
            Save Changes
        </button>

    </form>

</div>

<script>

function toggleEditForm() {

    let form = document.getElementById('editPostForm');

    if (
        form.style.display === 'none'
        || form.style.display === ''
    ) {

        form.style.display = 'block';

    } else {

        form.style.display = 'none';

    }
}

</script>

<?php } ?>

<!-- Reserved Space for Alert -->
<div 
    id="copyAlert" 
    class="alert alert-success text-center" 
    role="alert" 
    style="visibility: hidden; opacity: 0; transition: opacity 0.3s;"
>
    Caption copied to clipboard!
</div>
</div>
<!-- Scripts -->
<script>

// Copy Caption Button
document.getElementById('copyButton').addEventListener('click', function () {

    const caption = document.getElementById('hiddenRawCaption');

    caption.select();

    caption.setSelectionRange(0, 99999);

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

    let rawTitle = document.title;

    let cleanedTitle = rawTitle
        .replace(/\s*\|\s*Dietician Priyanka\s*/i, '')
        .trim();

    const fullMessage =
`${cleanedTitle}

Read Now:
${pageUrl}`;

    if (navigator.share) {

        navigator.share({
            title: cleanedTitle,
            text: fullMessage
        })
        .then(() => {
            console.log('Thanks for sharing!');
        })
        .catch(console.error);

    } else {

        const encodedMessage = encodeURIComponent(fullMessage);

        const whatsappUrl =
            "https://wa.me/?text=" + encodedMessage;

        window.open(whatsappUrl, '_blank');
    }
}

</script>

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
            <h5>Recommended Health Tips</h5>
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
                <a href="<?php echo urlencode($recommend['slug']); ?>">
                    <img 
                        loading="lazy" 
                        src="<?php echo $recommend['media_url']; ?>" 
                        class="card-img-top social-posts-recommendations" 
                        alt="<?php echo ($recommend['title']); ?>"
                        style="max-height: 400px; object-fit:cover"
                    >
                </a>
                <div class="card-body">
                    <p class="card-text">
                        <?php echo ($recommend['title'])?>
                    </p>
                    <a href="<?php echo urlencode($recommend['slug']); ?>" class="btn btn-primary btn-sm">View Post</a>
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
            <a href="/healthy-living" class="ttm-btn my-5 ttm-btn-shape-round ttm-btn-size-md ttm-btn-style-fill ml-15 ttm-btn-bgcolor-skincolor">Back to Healthy Living</a>
        </div>
    </div>
            
            
        
    
     </section>
    

<?php include 'footer.php'; ?>
