<?php include 'Header.php'; ?>

<div class="ttm-page-title-row">
  <div class="ttm-bg-layer ttm-page-title-row-bg-layer"></div>
  <div class="container">
    <div class="row">
      <div class="col-md-12 text-center">
        <div class="ttm-textcolor-white title-box">
          <div class="ttm-textcolor-white page-title-heading">
            <h1 class="title">Latest Offers</h1>
          </div>
          <div class="breadcrumb-wrapper">
            <span><a href="./" title="Homepage"><i class="ti ti-home"></i> Home </a></span>
            <span class="ttm-bread-sep">: : </span>
            <span><span class="ttm-textcolor-skincolor">Diet Programs</span></span>
            <span class="ttm-bread-sep">: : </span>
            <span><span class="ttm-textcolor-skincolor">Latest Offers</span></span>
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
        <div class="col-md-12 my-5">
          <div class="text-center clearfix section-title">
            <div class="title-header">
              <h5>Exciting Offers for You</h5>
              <h2 class="title">Discover Our Latest Deals</h2>
            </div>
            <div class="py-4">
              <p>At Bite & Diet, we're thrilled to present our latest offers tailored to help you achieve your health goals. These exclusive deals provide incredible value and are designed to make your journey towards a healthier lifestyle both affordable and enjoyable. Explore our latest offers below and take advantage of these limited-time discounts!</p>
            </div>
          </div>
        </div>
      </div>

      <?php
      // Fetch the latest offers from the JSON file
      $offers_data = file_get_contents('latest_offers.json');
      $offers = json_decode($offers_data, true);
      ?>

      <div class="container shadowbox">
        <?php foreach ($offers['offers'] as $offer): ?>
        <div class="row">
          <div class="col-md-12 col-sm-12 col-lg-4">
            <div class="res-991-mb-50">
              <div class="res-991-mt-60 ttm_single_image-wrapper">
                <div class="pattern-style position-relative text-left">
                  <img alt="<?= htmlspecialchars($offer['alt_text']) ?>" class="img-fluid width-100" src="<?= htmlspecialchars($offer['image']) ?>" loading="lazy">
                </div>
              </div>
              <div class="about-overlay-shape">
                <div class="row">
                  <div class="col-md-1"></div>
                  <div class="text-center col-md-10 mj">
                    <div class="ttm-textcolor-white about-content mt_109 spacing-8 ttm-bg ttm-bgcolor-skincolor ttm-col-bgcolor-yes">
                      <div class="ttm-bg-layer mc ttm-col-wrapper-bg-layer"></div>
                      <div class="layer-content">
                        <h4 class="mb-15"><?= htmlspecialchars($offer['tagline']) ?></h4>
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
                <h5>Special Offer</h5>
                <h2 class="title"><?= htmlspecialchars($offer['title']) ?></h2>
                <h3>Now Only Rs. <s><?= htmlspecialchars($offer['original_price']) ?></s> <span style="font-size:30px;color:red;"><?= htmlspecialchars($offer['discounted_price']) ?>/-</span> </h3>
              </div>
              <div class="title-desc">
                <h5><?= htmlspecialchars($offer['offer_details']['subtitle']) ?></h5>
                <p><?= htmlspecialchars($offer['offer_details']['description']) ?></p>
                <h5>Why Choose This Offer?</h5>
                <ul class="mb-20 ttm-list ttm-list-style-icon">
                  <?php foreach ($offer['offer_details']['benefits'] as $benefit): ?>
                  <li><i class="ttm-textcolor-skincolor fa fa-arrow-circle-right"></i><span class="ttm-list-li-content"><?= htmlspecialchars($benefit) ?></span></li>
                  <?php endforeach; ?>
                </ul>
                <a href="/form"
                class="mb-20 ttm-btn ttm-btn-bgcolor-black ttm-btn-shape-round ttm-btn-style-fill mt-10 ttm-btn-size-sm">
Enroll Now!
                    </a>
              </div>
            </div>
          </div>
        </div>
        <hr>
        <?php endforeach; ?>
      </div>

      <div class="text-center mt-50 res-991-mt-30">
        <a href="/plans-and-price" class="mb-20 ttm-btn ttm-btn-bgcolor-black ttm-btn-shape-round ttm-btn-style-fill ttm-btn-size-md">Explore More Offers</a>
      </div>
    </div>
  </section>
</div>


<div id="fomo-popup" class="alert alert-success shadow position-fixed"
     style="bottom: 300px; left: 20px; display:none; z-index:9999; min-width:250px;">
  ✅ 🎉 <b id="fomo-name"></b> enrolled in <b id="fomo-offer"></b> a few minutes ago!
</div>

<?php
// get offers
$offersData = json_decode(file_get_contents('latest_offers.json'), true);
$offerTitles = array_map(function($o) { return $o['title']; }, $offersData['offers']);

// get customer names
$reviewsData = json_decode(file_get_contents('google-review-data.json'), true);
$customerNames = array_map(function($r) { return $r['customer_name']; }, $reviewsData);
?>

<script>
const names = <?php echo json_encode($customerNames); ?>;
const offers = <?php echo json_encode($offerTitles); ?>;

function showFomo() {
  const name = names[Math.floor(Math.random() * names.length)];
  const offer = offers[Math.floor(Math.random() * offers.length)];

  document.getElementById("fomo-name").textContent = name;
  document.getElementById("fomo-offer").textContent = offer;

  const popup = document.getElementById("fomo-popup");
  popup.style.display = 'block';

  setTimeout(() => {
    popup.style.display = 'none';
  }, 5000);
}

// Trigger once after 4 seconds
setTimeout(showFomo, 4000);

// Optionally: repeat every ~20–40 seconds randomly
setInterval(() => {
  if (Math.random() < 0.5) { // 50% chance
    showFomo();
  }
}, 20000);
</script>


<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "OfferCatalog",
  "name": "Latest Offers",
  "url": "https://www.biteanddiet.in/latest-offers", 
  "itemListElement": [
    <?php foreach ($offers['offers'] as $offer): ?>
    {
      "@type": "Offer",
      "name": "<?= $offer['title'] ?>",
      "url": "https://www.biteanddiet.in/form",
      "image": "<?= $offer['image'] ?>",
      "description": "<?= $offer['offer_details']['description'] ?>",
      "priceCurrency": "INR",
      "price": "<?= $offer['discounted_price'] ?>",
      "priceValidUntil": "<?= date('Y-m-d', strtotime('+1 month')) ?>",
      "availability": "https://schema.org/InStock",
      "seller": {
        "@type": "Organization",
        "name": "Bite And Diet"
      },
      "author": {
        "@type": "Person",
        "name": "Dietician Priyanka",
        "url": "https://www.biteanddiet.in/aboutdtpriyanka"
      }
    }<?php if (end($offers['offers']) !== $offer): ?>,<?php endif; ?>
    <?php endforeach; ?>
  ]
}
</script>


<script src="https://cdn.jsdelivr.net/npm/tsparticles-confetti@2.11.0/tsparticles.confetti.bundle.min.js"></script>
<script>
const end = Date.now() + 15 * 1000;
(function frame() {
  confetti({
    particleCount: 2,
    angle: 60,
    spread: 55,
    origin: { x: 0 },
  });

  confetti({
    particleCount: 2,
    angle: 120,
    spread: 55,
    origin: { x: 1 },
  });

  if (Date.now() < end) {
    requestAnimationFrame(frame);
  }
})();
</script>



<?php include 'footer.php'; ?>

