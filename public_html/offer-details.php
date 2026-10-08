<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$slug = $_GET['slug'] ?? '';

// Load offers
$offers_data = file_get_contents('latest_offers.json');
$offers = json_decode($offers_data, true);

$offerData = null;

// find offer
foreach ($offers['offers'] as $offer) {

    $offerSlug = $offer['slug'] ?? strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $offer['title'])));

    if ($offerSlug === $slug) {
        $offerData = $offer;
        break;
    }
}

// if not found
if (!$offerData) {
    header("HTTP/1.0 404 Not Found");
    echo "Offer not found";
    exit;
}

$original = (float)$offerData['original_price'];
$discounted = (float)$offerData['discounted_price'];
$percentOff = round(100 - ($discounted / $original) * 100);


?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- Optimized Title and Meta Tags -->
<?php

$title = htmlspecialchars($offerData['title']) . ' | Dietician Priyanka';

$description = htmlspecialchars($offerData['offer_details']['description']);

$image = "https://www.biteanddiet.in/" . htmlspecialchars($offerData['image']);

$url = "https://www.biteanddiet.in/latest-offers/" . htmlspecialchars($offerData['slug']);

?>

<title><?php echo $title; ?></title>

<meta name="description" content="<?php echo $description; ?>">
<meta name="keywords" content="diet plan, weight loss diet plan, student diet plan, healthy diet plan, dietician priyanka">

<link rel="canonical" href="<?php echo $url; ?>">

<meta name="robots" content="index, follow">

<!-- Open Graph -->

<meta property="og:type" content="product">
<meta property="og:title" content="<?php echo $title; ?>">
<meta property="og:description" content="<?php echo $description; ?>">
<meta property="og:image" content="<?php echo $image; ?>">
<meta property="og:url" content="<?php echo $url; ?>">
<meta property="og:site_name" content="Bite & Diet">

<!-- Twitter -->

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?php echo $title; ?>">
<meta name="twitter:description" content="<?php echo $description; ?>">
<meta name="twitter:image" content="<?php echo $image; ?>">


<script type="application/ld+json">
<?php
$offerLd = [
    "@context" => "https://schema.org",
    "@graph" => [
        [
            "@type" => "WebPage",
            "url" => $url,
            "name" => $title,
            "description" => $description,
            "primaryImageOfPage" => [
                "@type" => "ImageObject",
                "url" => $image
            ]
        ],
        [
            "@type" => "Product",
            "name" => $title,
            "description" => $description,
            "image" => $image,
            "brand" => [
                "@type" => "Brand",
                "name" => "Bite & Diet"
            ],
            "offers" => [
                "@type" => "Offer",
                "url" => $url,
                "priceCurrency" => "INR",
                "price" => $discounted,
                "priceSpecification" => [
                    "@type" => "UnitPriceSpecification",
                    "price" => $discounted,
                    "priceCurrency" => "INR"
                ],
                "availability" => "https://schema.org/InStock",
                "seller" => [
                    "@type" => "Organization",
                    "name" => "Bite & Diet - Diet Consultation"
                ]
            ]
        ]
    ]
];
echo json_encode($offerLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>
</script>
<?php
$disable_header = false;
$ogImageSuppressed = true;
include 'Header.php';
?>



<style>

.offer-hero-img{
width:100%;
border-radius:15px;
box-shadow:0 10px 30px rgba(0,0,0,0.15);
border:2px solid #999;
Padding:4px;
margin-top:20px
}

.price-card{
background:#ffffff;
border-radius:15px;
box-shadow:0 15px 35px rgba(0,0,0,0.1);
padding:40px;
margin:auto;

}

.old-price{

font-size:20px;
color:#888;
text-decoration:line-through;
}

.new-price{

font-size:48px;
font-weight:700;
color:#e53935;
}

.discount-badge{

background:#ff5252;
padding:6px 12px;
color:white;
border-radius:30px;
font-size:14px;
margin-left:10px;
}

.enroll-btn{

padding:15px 40px;
font-size:20px;
border-radius:40px;
font-weight:600;
}

.benefits-list li{

font-size:17px;
margin-bottom:10px;
}

/* MOBILE FIX */

@media (max-width:768px){

.price-card{
padding:25px 15px;
}

.old-price{
font-size:16px;
}

.new-price{
font-size:34px;
display:block;
}

.discount-badge{
font-size:12px;
padding:4px 10px;
display:inline-block;
margin-top:6px;
}

.enroll-btn{
padding:12px 30px;
font-size:18px;
}

}

</style>

<div class="ttm-page-title-row">
  <div class="ttm-bg-layer ttm-page-title-row-bg-layer"></div>
  <div class="container">
    <div class="row">
      <div class="col-md-12 text-center">
        <div class="ttm-textcolor-white title-box">

          <div class="ttm-textcolor-white page-title-heading">
            <h1 class="title"><?= htmlspecialchars($offerData['title']) ?></h1>
          </div>

          <div class="breadcrumb-wrapper">
            <span>
              <a href="/" title="Homepage">
                <i class="ti ti-home"></i> Home
              </a>
            </span>

            <span class="ttm-bread-sep"> : : </span>

            <span>
              <a href="/latest-offers">
                <span class="ttm-textcolor-skincolor"> Latest Offers</span>
              </a>
            </span>

            <span class="ttm-bread-sep"> : : </span>

            <span>
              <span class="ttm-textcolor-skincolor">
                <?= htmlspecialchars($offerData['title']) ?>
              </span>
            </span>

          </div>

        </div>
      </div>
    </div>
  </div>
</div>



<div class="site-main">

<section class="clearfix meal-plan-top-section ttm-row m-3">
<div class="container">

<div class="row">

<div class="col-lg-12 col-md-12">

<header class="text-center section-title">
<h5>Special Offer</h5>
</header>

<h1 class="custom_heading text-center mb-4">
<?= htmlspecialchars($offerData['title']) ?>
</h1>


<!-- FULL WIDTH IMAGE -->

<div class="row justify-content-center mb-4">

<div class="col-lg-8 col-md-10 col-12">

<img src="/<?= htmlspecialchars($offerData['image']) ?>"
class="img-fluid offer-hero-img"
alt="<?= htmlspecialchars($offerData['alt_text']) ?>">

</div>

</div>



<!-- Subtitle -->

<?php if(!empty($offerData['offer_details']['subtitle'])): ?>

<h4 class="text-center text-success mt-4">
<?= htmlspecialchars($offerData['offer_details']['subtitle']) ?>
</h4>

<?php endif; ?>


<!-- Description -->

<div class="row justify-content-center mt-3">
<div class="col-md-8">

<p class="lead text-center">
<?= htmlspecialchars($offerData['offer_details']['description']) ?>
</p>

</div>
</div>



<hr class="my-5">



<!-- BENEFITS -->

<div class="row justify-content-center">

<div class="col-md-10 col-12">

<h3 class="mb-4 text-center">Program Benefits</h3>

<ul class="list-unstyled benefits-list">

<?php foreach ($offerData['offer_details']['benefits'] as $benefit): ?>

<li>
<i class="fa fa-check-circle text-success me-2"></i>
<?= htmlspecialchars($benefit) ?>
</li>

<?php endforeach; ?>

</ul>

</div>

</div>



<hr class="my-5">



<!-- PRICING CARD -->

<div class="row justify-content-center my-5 mx-0">


<div class="col-lg-6 col-md-8 col-12 text-center">

<div class="price-card">

<p class="text-muted mb-2">Limited Time Offer</p>

<div>

<span class="old-price">
₹<?= number_format((int)$original) ?>
</span>

</div>

<div class="my-2">

<span class="new-price">
₹<?= number_format((int)$discounted) ?>
</span>

<span class="discount-badge">
<?= $percentOff ?>% OFF
</span>

</div>

<p class="text-muted mt-2">
Start your transformation today
</p>

<div class="mt-4">

<a href="/form" class="btn btn-success enroll-btn">
Enroll Now
</a>

</div>

</div>

</div>

</div>

<!-- BACK + SHARE BUTTONS -->

<div class="row justify-content-center mt-4">

    <!-- Back Button -->
    <div class="col-auto mb-2">
        <a href="/latest-offers"
           class="ttm-btn ttm-btn-shape-round ttm-btn-size-md ttm-btn-style-fill ttm-btn-bgcolor-black">
           Back
        </a>
    </div>

    <!-- Share Button -->
    <div class="col-auto mb-2">
        <button
           class="ttm-btn ttm-btn-shape-round ttm-btn-size-md ttm-btn-style-fill ttm-btn-bgcolor-skincolor"
           onclick="sharePage()">
           Share
        </button>
    </div>

</div>

<script>
function sharePage() {

    const pageUrl = window.location.href;

    let rawTitle = document.title;
    let cleanedTitle = rawTitle.replace(/\s*\|\s*Dietician Priyanka\s*/i, '').trim();

    const message = cleanedTitle + "\n\nKnow More:";

    if (navigator.share) {

        navigator.share({
            title: cleanedTitle,
            text: message,
            url: pageUrl
        });

    } else {

        const whatsappText = cleanedTitle + "\n\nKnow More:\n" + pageUrl;
        const whatsappUrl = "https://wa.me/?text=" + encodeURIComponent(whatsappText);

        window.open(whatsappUrl, "_blank");

    }
}
</script>


</div>
</div>

</div>
</section>
</div>

<?php include 'footer.php'; ?>