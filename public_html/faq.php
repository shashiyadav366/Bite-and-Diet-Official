<?php include 'Header.php'; ?><div class="ttm-page-title-row"><div class="ttm-bg-layer ttm-page-title-row-bg-layer"></div><div class="container"><div class="row"><div class="col-md-12 text-center"><div class="ttm-textcolor-white title-box"><div class="ttm-textcolor-white page-title-heading"><h1 class="title">FAQ's</h1></div><div class="breadcrumb-wrapper"><span><a href="./"title="Homepage"><i class="ti ti-home"></i> Home </a></span><span class="ttm-bread-sep">: : </span><span><span class="ttm-textcolor-skincolor">About Us</span></span> <span class="ttm-bread-sep">: : </span><span><span class="ttm-textcolor-skincolor">FAQ's</span></span></div></div></div></div></div></div><div class="site-main"><section class="clearfix ttm-row faqs-top-section"><div class="container"><div class="row"><div class="col-md-12"><div class="clearfix mb-10 section-title text-center">
    
 <div class="title-header">
        <h5>Frequently Asked Questions</h5>
        <h2 class="title">Get Your Answer Here</h2></div><div class="title-desc">Below you’ll find answers to some of the most frequently asked question. We are constantly adding most asked question to this page.</div></div></div></div><div class="row"><div class="col-md-3"></div><div class="col-md-6"><div class="widget widget-search"></div><p class="text-center">If you have any other questions <a href="mailto:yourbitemydiet@gmail.com"class="ttm-textcolor-skincolor">yourbitemydiet@gmail.com</a></p></div><div class="col-md-3"></div></div></div></section><div class="clearfix ttm-row faqs-accordion-section"><div class="container">
            
            
          <?php
$faqData = json_decode(file_get_contents('faq.json'), true);
$mainEntity = $faqData['mainEntity'];
?>

<!-- Structured Data for SEO -->
<script type="application/ld+json">
<?= json_encode($faqData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT); ?>
</script>

<!-- HTML FAQ Accordion -->
<div class="row">
  <div class="col-md-12">
    <div class="mb-20 accordion" id="accordion">
      <?php foreach ($mainEntity as $index => $faq): ?>
        <?php
          $collapseId = "collapse" . $index;
          $activeClass = $index === 0 ? 'active show' : '';
        ?>
        <div class="toggle ttm-style-classic ttm-toggle-title-border <?= $index === 0 ? 'active' : '' ?>">
          <div class="toggle-title">
            <a href="#<?= $collapseId ?>" data-toggle="collapse" data-parent="#accordion">
              <?= htmlspecialchars($faq['name']) ?>
            </a>
          </div>
          <div id="<?= $collapseId ?>" class="toggle-content collapse <?= $activeClass ?>">
            <p><?= nl2br(htmlspecialchars($faq['acceptedAnswer']['text'])) ?></p>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>




</div></div><section class="clearfix ttm-row faqs-cta-section ttm-bgcolor-skincolor"><div class="container  my-5 pt-20"><div class="row"><div class="col-md-8"><div class="ttm-textcolor-white row-title style6 text-right"><h2 class="title pb-10">Have you any question for work consultation</h2></div></div><div class="col-md-4"><div class="row-title style6 text-left"><a href="https://wa.me/+918826549878"class="mb-20 ttm-btn ttm-btn-color-white ttm-btn-shape-round ttm-btn-size-md ttm-btn-style-border">Contact Me</a></div></div></div></div></section></div>

<?php include 'footer.php'; ?>