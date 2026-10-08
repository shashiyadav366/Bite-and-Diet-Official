<?php include 'Header.php'; ?>
<div class="ttm-page-title-row">
    <div class="ttm-bg-layer ttm-page-title-row-bg-layer"></div>
    <div class="container">
        <div class="row">
            <div class="col-md-12 text-center">
                <div class="ttm-textcolor-white title-box">
                    <div class="ttm-textcolor-white page-title-heading">
                        <h1 class="title">Training & Certification Programs</h1>
                    </div>
                    <div class="breadcrumb-wrapper">
                        <span><a href="./" title="Homepage"><i class="ti ti-home"></i> Home </a></span>
                        <span class="ttm-bread-sep">: : </span>
                        <span><span class="ttm-textcolor-skincolor">Services</span></span>
                        <span class="ttm-bread-sep">: : </span>
                        <span><span class="ttm-textcolor-skincolor">Training & Certification</span></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="site-main">
    <div class="clearfix sidebar ttm-bgcolor-white ttm-sidebar-left">
        <div class="container">
            <div class="row d-block">
                <div class="col-lg-9 content-area">
                    <div class="ttm-service-single-content-area">
                        <div class="mb-25 ttm_single_image-wrapper">
                            <img alt="Training and Certification Programs" class="img-fluid" src="images/training.jpg">
                        </div>

                        <div class="ttm-service-description">
                            <div class="clearfix section-title">
                                <div class="title-header">
                                    <h5>Training & Certification</h5>
                                </div>
                            </div>

                            <h4 class="font-27 line-h35">Enhance Your Career with Our Training & Certification Programs</h4>

                            <div class="mb-35">
                                <p>Embark on a rewarding career in nutrition with our comprehensive training and certification programs. Ideal for those passionate about promoting a healthy lifestyle, our courses are designed to help you improve diets, manage health conditions, and offer expert advice to clients.</p>
                                <p>Our programs cover various health issues that can be positively impacted by diet, including:</p>
                                <ul class="mb-20 ttm-list ttm-list-style-icon">
                                    <li><i class="ttm-textcolor-skincolor fa fa-arrow-circle-right"></i><span class="ttm-list-li-content"> Diabetes</span></li>
                                    <li><i class="ttm-textcolor-skincolor fa fa-arrow-circle-right"></i><span class="ttm-list-li-content"> Polycystic Ovarian Syndrome (PCOS)</span></li>
                                    <li><i class="ttm-textcolor-skincolor fa fa-arrow-circle-right"></i><span class="ttm-list-li-content"> High Blood Pressure</span></li>
                                    <li><i class="ttm-textcolor-skincolor fa fa-arrow-circle-right"></i><span class="ttm-list-li-content"> High Cholesterol</span></li>
                                    <li><i class="ttm-textcolor-skincolor fa fa-arrow-circle-right"></i><span class="ttm-list-li-content"> Knee Pain</span></li>
                                    <li><i class="ttm-textcolor-skincolor fa fa-arrow-circle-right"></i><span class="ttm-list-li-content"> Food Sensitivities and Allergies</span></li>
                                </ul>
                                <p>Join our courses to gain valuable knowledge and become a certified nutrition expert.</p>
                            </div>
                        </div>

                        
                            <div class="mb-35 mt_5 sep-line"></div>
                        

                        <?php
                        $courses = json_decode(file_get_contents('training.json'), true);
                        foreach ($courses as $course): ?>
<div class="custom-card position-relative 
">
    <!-- premium badge -->
    <div class="position-absolute" style="top: 8px; right: 10px;">
        <span class="badge badge-warning">
            <i class="fa fa-star"></i> Course
        </span>
    </div>

    <!-- Title -->
    <div class="ttm-service-description mb-3">
        <h4 class="font-27 line-h35">
            <?php echo htmlspecialchars($course['title']); ?>
        </h4>

        <!-- Description -->
        <ul class="mb-2 ttm-list ttm-list-style-icon">
            <?php foreach ($course['description'] as $point): ?>
                <li>
                    <i class="ttm-textcolor-skincolor fa fa-arrow-circle-right"></i>
                    <span class="ttm-list-li-content"><?php echo htmlspecialchars($point); ?></span>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>

    <hr>

    <div class="d-flex justify-content-between align-items-center">
        <!-- Left: Prices -->
<div>
    <small class="text-muted">Limited Time Offer</small><br>
    <del>₹ <?php echo number_format((int)$course['originalPrice']); ?>/-</del><br>
    <strong class="text-danger">
        Now: ₹ <?php echo number_format((int)$course['offerPrice']); ?>/-
    </strong>
</div>


        <!-- Right: Discount -->
        <div class="text-right">
            <?php
                $original = (float)$course['originalPrice'];
                $offer = (float)$course['offerPrice'];
                $discount = round(100 - (($offer / $original) * 100));
            ?>
            <span class="badge badge-danger p-2">
                <?php echo $discount; ?>% OFF
            </span>
        </div>
    </div>

    <!-- Enroll button -->
    <div class="my-3 text-center">
        <a href="/form" class="btn btn-success btn-sm px-4">Enroll Now!</a>
    </div>
</div>


                            
                                <div class="mb-35 mt_5 sep-line"></div>
                            
                        <?php endforeach; ?>
                    </div> <!-- .ttm-service-single-content-area -->
                </div> <!-- .col-lg-9 -->

                <div class="col-lg-3 sidebar-left ttm-bg ttm-bgcolor-grey ttm-col-bgcolor-yes ttm-left-span widget-area">
                    <div class="ttm-bg-layer ttm-col-wrapper-bg-layer"></div>

                    <aside class="widget widget-nav-menu">
                        <ul class="widget-menu">
                            <li><a href="/online-counselling">Online Counselling</a></li>
                            <li><a href="/personal-counselling">Personal Counselling</a></li>
                            <li><a href="/coming-soon">Corporate Programs</a></li>
                            <li><a href="/coming-soon">School Health Programs</a></li>
                            <li><a href="/ayurvedic-herbs">Ayurvedic Herbs</a></li>
                            <li class="active"><a href="/training">Training</a></li>
                            <li><a href="/faq">FAQs</a></li>
                        </ul>
                    </aside>

                    <aside class="widget contact-widget">
                        <h3 class="widget-title">Get in Touch</h3>
                        <ul class="contact-widget-wrapper">
                            <li><i class="fa fa-map-marker"></i>U-52/25, DLF Phase 3, Gurugram 122002<br>India</li>
                            <li><i class="fa fa-envelope-o"></i><a href="mailto:yourbitemydiet@gmail.com" target="_blank">yourbitemydiet@gmail.com</a></li>
                            <li><i class="fa fa-phone"></i>(+91)8826549878</li>
                        </ul>
                    </aside>
                </div> <!-- .col-lg-3 -->
            </div> <!-- 
.row -->
        </div> <!-- .container -->
    </div> <!-- .sidebar -->
</div> <!-- .site-main -->


<!-- FOMO popup -->
<div id="fomo-popup" class="fomo-container alert alert-success shadow position-fixed" 
     
     >✅🎉
   <b id="fomo-name"></b> enrolled in <b id="fomo-course"></b> a few minutes ago!
</div>

<script>
    <?php
$coursesData = json_decode(@file_get_contents('training.json'), true);
$courseTitles = [];
if (!empty($coursesData)) {
    $courseTitles = array_map(fn($c) => $c['title'] ?? '', $coursesData);
}

// get customer names
$reviewsData = json_decode(@file_get_contents('google-review-data.json'), true);
$customerNames = [];
if (!empty($reviewsData)) {
    $customerNames = array_filter(array_map(fn($r) => $r['customer_name'] ?? '', $reviewsData), fn($n) => $n !== '');
}
?>
const names = <?php echo json_encode($customerNames); ?>;

const courses = <?php echo json_encode($courseTitles); ?>;

function showFomo() {
  const name = names[Math.floor(Math.random() * names.length)];
  const course = courses[Math.floor(Math.random() * courses.length)];

  document.getElementById("fomo-name").textContent = name;
  document.getElementById("fomo-course").textContent = course;

  const popup = document.getElementById("fomo-popup");
  popup.style.display = 'block';

  setTimeout(() => {
    popup.style.display = 'none';
  }, 5000);
}

// Trigger once after 4 seconds:
setTimeout(showFomo, 4000);

// Optionally: repeat every ~20–40 seconds randomly:
setInterval(() => {
  if (Math.random() < 0.5) { // 50% chance
    showFomo();
  }
}, 20000);
</script>




<script type="application/ld+json">
<?php
$offers = [];
foreach ($courses as $course) {
  $offers[] = [
    "@type" => "Offer",
    "name" => $course['title'],
    "url" => "https://www.biteanddiet.in/training.php",
    "priceCurrency" => "INR",
    "price" => $course['offerPrice'],
    "itemOffered" => [
      "@type" => "EducationalOrganization",
      "name" => $course['title'],
      "description" => $course['description'][0] ?? ''
    ]
  ];
}
$schema = [
  "@context" => "https://schema.org",
  "@type" => "EducationalOrganization",
  "name" => "Bite and Diet",
  "url" => "https://www.biteanddiet.in/training.php",
  "logo" => "https://www.biteanddiet.in/images/logo.png",
  "address" => [
    "@type" => "PostalAddress",
    "streetAddress" => "U-52/25, DLF Phase 3",
    "addressLocality" => "Gurugram",
    "postalCode" => "122002",
    "addressCountry" => "India"
  ],
  "contactPoint" => [
    "@type" => "ContactPoint",
    "contactType" => "Customer Service",
    "telephone" => "+91 8826549878",
    "email" => "yourbitemydiet@gmail.com"
  ],
  "offers" => $offers
];
echo json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
?>
</script>


<?php include 'footer.php'; ?>

