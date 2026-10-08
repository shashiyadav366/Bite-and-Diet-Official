<?php  
include 'Header.php';  
  
// File paths  
$youtube_videos_file = 'youtube_videos.json';  
$youtube_backup_file = 'youtube_videos-backup.json';  
  
// Function to check if a file contains valid JSON  
function isValidJsonFile($file) {  
    if (file_exists($file) && filesize($file) > 0) {  
        $fileContents = file_get_contents($file);  
        $jsonData = json_decode($fileContents, true);  
        return $jsonData !== null ? $jsonData : false;  
    }  
    return false;  
}  
  

// Load the main file first  
$videos = isValidJsonFile($youtube_videos_file);  
  
// If the main file is invalid or empty, fallback to the backup file  
if (!$videos) {  
    $videos = isValidJsonFile($youtube_backup_file);  
    if (!$videos) {  
        die("Error: Unable to load valid YouTube video data.");  
    }  
}  
  
// Shuffle and select 4 random videos  
shuffle($videos);  
$random_videos = array_slice($videos, 0, 4);  
?>  



<?php
// Fetch the JSON data from the allposts.json file
$jsonFilePath = 'allposts.json';
$jsonData = file_get_contents($jsonFilePath);
$allPosts = json_decode($jsonData, true);

// Function to get random posts
function getRandomPosts($posts, $count = 4) {
    $randomKeys = array_rand($posts, min($count, count($posts)));
    $randomPosts = [];
    foreach ($randomKeys as $key) {
        $randomPosts[] = $posts[$key];
    }
    return $randomPosts;
}

// Get 4 random posts
$recommendations = getRandomPosts($allPosts);

// Encode recommendations as JSON for JavaScript
$recommendationsJson = json_encode($recommendations);
?>

<!-- Include recommendationsJson in the script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const recommendations = <?php echo $recommendationsJson; ?>;
    const FALLBACK_IMG = 'https://www.biteanddiet.in/images/social_posts_images/Dietician_Priyanka_5_Years_At_BiteAndDiet.jpg';
    const container = document.getElementById('recommendations-row');

    function renderPosts(posts) {
        container.innerHTML = ''; // Clear the container

        posts.forEach(post => {
            const imgUrl = post.firstImgSrc || FALLBACK_IMG;
            // No need to encode the URL, just use the post.postUrl directly
            const postUrl = post.postUrl; 

            const cardDiv = document.createElement('div');
            cardDiv.className = 'col-md-6 col-lg-3 mb-4'; // Ensures 4 cards per row on medium screens and larger

            cardDiv.innerHTML = `
                <div class='card' style='margin: 0 0 40px 0;'>
                    <img src='${imgUrl}' class='card-img-top' alt='${post.title}' style='object-fit: cover;max-height:200px;min-height:200px'>
                    <div class='card-body text-center'>
                        <h6 class='card-title'>${post.title}</h6>
                        <a href='/blog-post/${post.slug}' class='btn btn-primary'>Read More</a>
                    </div>
                </div>
            `;

            container.appendChild(cardDiv);
        });
    }

    renderPosts(recommendations);
});
</script>



<main>
    <div class="ttm-page-title-row">
        <div class="ttm-bg-layer ttm-page-title-row-bg-layer"></div>
        <div class="container">
            <div class="row">
                <div class="col-md-12 text-center">
                    <div class="ttm-textcolor-white title-box">
                        <div class="ttm-textcolor-white page-title-heading">
                            <h1 class="title"><?php echo htmlspecialchars($title); ?></h1>
                        </div>
                        <div class="breadcrumb-wrapper">
                            <span><a href="/" title="Homepage"><i class="ti ti-home"></i> Home </a></span>
                            <span class="ttm-bread-sep">: : </span>
                            <span><a href="/plans-and-packages" title="Plans And Packages">Diet Programs</a></span>
                            <span class="ttm-bread-sep">: : </span>
                            <span><span class="ttm-textcolor-skincolor"><?php echo htmlspecialchars($title); ?></span></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="site-main">
        <section class="clearfix meal-plan-top-section ttm-row">
            <div class="container">
                <div class="row">
                    <div class="col-md-12 col-sm-12 col-lg-4">
                        <div class="res-991-mb-50">
                            <div class="res-991-mt-60 ttm_single_image-wrapper">
                                <div class="pattern-style position-relative text-left">
                                    <img alt="<?php echo htmlspecialchars($title); ?>" class="img-fluid width-100" src="<?php echo htmlspecialchars($image); ?>">
                                </div>
                            </div>
                            <div class="about-overlay-shape">
                                <div class="row">
                                    <div class="col-md-1"></div>
                                    <div class="text-center col-md-10 mj">
                                        <div class="ttm-textcolor-white about-content mt_109 spacing-8 ttm-bg ttm-bgcolor-skincolor ttm-col-bgcolor-yes">
                                            <div class="ttm-bg-layer mc ttm-col-wrapper-bg-layer"></div>
                                            <div class="layer-content">
                                                <h4 class="mb-15"><a href="/plans-and-price">View Packages</a></h4>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-1"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-12 col-sm-12 col-lg-8">
                        <div class="clearfix section-title">
                            <div class="title-header">
                                <h5>Special Offers</h5>
                                <h2 class="title"><?php echo htmlspecialchars($title); ?></h2>
                            </div>
                            <div class="title-desc">
                                <p><?php echo htmlspecialchars($description); ?></p>
                                <ul class="mb-20 ttm-list ttm-list-style-icon">
                                    <?php foreach ($diet_plan['special_offers']['bullet_points'] as $point): ?>
                                        <li><i class="ttm-textcolor-skincolor fa fa-arrow-circle-right"></i><span class="ttm-list-li-content"><?php echo htmlspecialchars($point); ?></span></li>
                                    <?php endforeach; ?>
                                </ul>
                                <p>Other complications include</p>
                                <ul class="mb-20 ttm-list ttm-list-style-icon">
                                    <?php foreach ($diet_plan['complications'] as $complication): ?>
                                        <li><i class="ttm-textcolor-skincolor fa fa-arrow-circle-right"></i><span class="ttm-list-li-content"><?php echo htmlspecialchars($complication); ?></span></li>
                                    <?php endforeach; ?>
                                </ul>
                                <hr>
                                <h2 class="title mb-20">How <span class="red-title">Bite And Diet</span> helps?</h2>
                                <p>We help you to identify and cure the root cause of weight gain which may include</p>
                                <ul class="mb-20 ttm-list ttm-list-style-icon">
                                    <?php foreach ($diet_plan['how_it_helps'] as $help): ?>
                                        <li><i class="ttm-textcolor-skincolor fa fa-arrow-circle-right"></i><span class="ttm-list-li-content"><?php echo htmlspecialchars($help); ?></span></li>
                                    <?php endforeach; ?>
                                </ul>
                                <p>We work on your diet and lifestyle to help you lose weight without losing your charm and energy.</p>
                                <ul class="mb-20 ttm-list ttm-list-style-icon">
                                    <?php foreach ($diet_plan['lifestyle_improvements'] as $improvement): ?>
                                        <li><i class="ttm-textcolor-skincolor fa fa-arrow-circle-right"></i><span class="ttm-list-li-content"><?php echo htmlspecialchars($improvement); ?></span></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-center mt-50 res-991-mt-30"> 
                    <a href="/plans-and-price" class="mb-20 ttm-btn ttm-btn-bgcolor-black ttm-btn-shape-round ttm-btn-size-md ttm-btn-style-fill">View Packages</a>
                </div> 
<hr>
</div>
             <div class="container mt-20">
    <div class="clearfix section-title">
        <div class="title-header">
            <h5>Recommended Videos</h5>
        </div>
    </div>

    <div class="row">
        <?php foreach ($random_videos as $video): 
            $slug = $video['slug'];
        ?>
            <div class="col-md-6 col-lg-3 mb-4">
                <div class="card" style="margin: 0 0 40px 0;">
                    <a href="/video/<?php echo $slug; ?>" class="video-thumbnail">
                        <img src="<?php echo htmlspecialchars($video['thumbnail']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($video['title']); ?>">
                        <div class="overlay"></div>
                        <div class="play-button"></div>
                    </a>
                    <div class="card-body">
                        <h6 class="card-title"><?php echo htmlspecialchars($video['title']); ?></h6>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
                
                <div class="container"> 
    <div class="clearfix section-title">
                        <div class="title-header">
                            <h5>Recommended Blog Posts</h5>
                        </div>
                    </div>
    <div class="row" id="recommendations-row">
        <!-- Cards will be appended here by JavaScript -->
    </div>

    <hr>
 
          
    
</div>

            
        </section>
       
        <section class="clearfix ttm-bg ttm-bgimage-yes bg-img4 home2-cta-section ttm-bgcolor-grey ttm11"><div class="ttm-bg-layer ttm-row-wrapper-bg-layer"></div><div class="container"><div class="row"><div class="text-center col-lg-12"><div class="clearfix row-title style2"><div class="title-header"><h2 class="title mb-15">Transform Your Body And Mind With <span class="ttm-textcolor-skincolor">Nutrition</span></h2></div><p>Everyone's body is completely different; therefore it's not acceptable to fit the "one-size-fits-all" concept. However, it may take long<br>to see desirable changes in your body once you begin understanding in depth.</p></div><div class="res-991-mt-30 mt-50"><a href="https://wa.me/918826549878"class="ttm-btn mb-20 ttm-btn-shape-round ttm-btn-size-md ttm-btn-style-fill ttm-btn-bgcolor-black">Start Now!</a> <a href="form"class="ttm-btn mb-20 ttm-btn-shape-round ttm-btn-size-md ttm-btn-style-fill ml-15 ttm-btn-bgcolor-skincolor">Contact Us</a></div></div></div></div> </section>
    </div>
</main>

<?php
$current_date = date('Y-m-d');

// JSON-LD structured data for SEO
$json_ld = [
    "@context" => "https://schema.org",
    "@type" => "HealthDiet",
    "mainEntityOfPage" => [
        "@type" => "WebPage",
        "@id" => "https://www.biteanddiet.in/plans-and-packages/{$diet_plan['diet_url']}"
    ],
    "headline" => $diet_plan['title'],
    "description" => $diet_plan['description'],
    "image" => [
        "@type" => "ImageObject",
        "url" => "https://www.biteanddiet.in/{$diet_plan['image']}",
        "height" => 800,
        "width" => 1200
    ],
    "author" => [
        "@type" => "Person",
        "name" => "Dietician Priyanka",
        "image" => [
            "@type" => "ImageObject",
            "url" => "https://www.biteanddiet.in/images/single-img-one-new.webp",
            "height" => 600,
            "width" => 400
        ]
    ],
    "publisher" => [
        "@type" => "Organization",
        "name" => "Bite And Diet",
        "logo" => [
            "@type" => "ImageObject",
            "url" => "https://www.biteanddiet.in/images/big_logo.png",
            "width" => 250,
            "height" => 60
        ]
    ],
    "datePublished" => $current_date, // Update this dynamically if available
    "dietPlanDetails" => [
        "@type" => "Offer",
        "priceCurrency" => "INR",
        "price" => "Contact for details", // You can add the price if available in the JSON
        "availability" => "https://schema.org/InStock",
        "url" => "https://www.biteanddiet.in/plans-and-packages/{$diet_plan['diet_url']}"
    ],
    "specialOffers" => [
        "@type" => "Offer",
        "name" => $diet_plan['special_offers']['heading'],
        "description" => $diet_plan['special_offers']['description'],
        "features" => $diet_plan['special_offers']['bullet_points']
    ],
    "complicationsAddressed" => $diet_plan['complications'],
    "howItHelps" => $diet_plan['how_it_helps'],
    "lifestyleImprovements" => $diet_plan['lifestyle_improvements']
];

?>
    <script type="application/ld+json">
        <?= json_encode($json_ld, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) ?>
    </script>
<?php include 'footer.php'; ?>



