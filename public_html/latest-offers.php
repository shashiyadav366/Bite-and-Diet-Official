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

<div class="row">
  <?php foreach ($offers['offers'] as $offer): ?>

    <?php
    // generate slug if not present
    $slug = $offer['slug'] ?? strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $offer['title'])));
    ?>

    <div class="col-md-6 my-2">
      <div class="position-relative h-100 border-0 custom-card">

        <!-- Exclusive badge -->
        <div class="position-absolute top-0 end-0 bg-warning text-dark px-2 py-1 rounded-start small" style="z-index:1;">
          <i class="fa fa-star text-dark"></i> Exclusive
        </div>

        <!-- Image -->
        <img src="<?= htmlspecialchars($offer['image']) ?>" 
             alt="<?= htmlspecialchars($offer['alt_text']) ?>"
             class="card-img-top img-fluid">

        <div class="card-body text-center">

          <!-- Title -->
          <h5 class="card-title">
            <?= htmlspecialchars($offer['title']) ?>
          </h5>

          <!-- Know More Button -->
          <div class="my-3">
            <a href="/latest-offers/<?= htmlspecialchars($slug) ?>" 
               class="btn btn-success btn-sm px-4">
               Know More Offer
            </a>
          </div>

        </div>

      </div>
    </div>

  <?php endforeach; ?>
</div>
  
      <div class="text-center mt-50 res-991-mt-30">  
        <a href="/plans-and-price" class="mb-20 ttm-btn ttm-btn-bgcolor-black ttm-btn-shape-round ttm-btn-style-fill ttm-btn-size-md">Explore More Offers</a>  
      </div>  
    </div>  
  </section>  
</div>  
  
  
<div id="fomo-popup" class="fomo-container alert alert-success shadow position-fixed">  
  ✅ 🎉 <b id="fomo-name"></b> enrolled in <b id="fomo-offer"></b> a few minutes ago!  
</div>  
  
<?php  
// get offers  
$offersData = json_decode(@file_get_contents('latest_offers.json'), true);  
$offerTitles = [];
if (!empty($offersData['offers'])) {
    $offerTitles = array_map(fn($o) => $o['title'] ?? '', $offersData['offers']);  
}
  
// get customer names  
$reviewsData = json_decode(@file_get_contents('google-review-data.json'), true);  
$customerNames = [];
if (!empty($reviewsData)) {
    $customerNames = array_filter(array_map(fn($r) => $r['customer_name'] ?? '', $reviewsData), fn($n) => $n !== '');
}  
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