<?php
$currentPage = basename($_SERVER['PHP_SELF']);

if (!in_array($currentPage, ['training.php', 'latest-offers.php','form.php','coming-soon.php'])):

// load diet plans
$dietData = json_decode(@file_get_contents(__DIR__ . '/diet_plans.json'), true);
$dietTitles = [];
if (!empty($dietData['diet_plans'])) {
    $dietTitles = array_map(fn($c) => $c['diet_name'], $dietData['diet_plans']);
}

// load courses
$coursesData = json_decode(@file_get_contents(__DIR__ . '/training.json'), true);
$courseTitles = [];
if (!empty($coursesData)) {
    $courseTitles = array_map(fn($c) => $c['title'], $coursesData);
}

// load offers
$offersData = json_decode(@file_get_contents(__DIR__ . '/latest_offers.json'), true);
$offerTitles = [];
if (!empty($offersData['offers'])) {
    $offerTitles = array_map(fn($o) => $o['title'], $offersData['offers']);
}

// load customer names
$reviewsData = json_decode(@file_get_contents(__DIR__ . '/google-review-data.json'), true);
$customerNames = [];
if (!empty($reviewsData)) {
    $customerNames = array_map(fn($r) => $r['customer_name'], $reviewsData);
}

// combine all titles
$allTitles = array_merge($dietTitles, $courseTitles, $offerTitles);
?>

<div id="fomo-popup" class="fomo-container alert alert-success shadow position-fixed"
     
     >
  ✅ 🎉 <b id="fomo-name"></b> enrolled in <b id="fomo-item"></b> a few hours ago!
</div>

<script>
const names = <?php echo json_encode($customerNames); ?>;
const items = <?php echo json_encode($allTitles); ?>;

function showFomo() {
  if (!names.length || !items.length) return;

  const name = names[Math.floor(Math.random() * names.length)];
  const item = items[Math.floor(Math.random() * items.length)];

  document.getElementById("fomo-name").textContent = name;
  document.getElementById("fomo-item").textContent = item;

  const popup = document.getElementById("fomo-popup");
  popup.style.display = 'block';

  setTimeout(() => {
    popup.style.display = 'none';
  }, 5000);
}

setTimeout(showFomo, 4000);

setInterval(() => {
  if (Math.random() < 0.5) {
    showFomo();
  }
}, 20000);
</script>

<?php endif; ?>