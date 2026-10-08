<?php
session_start();
$_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}
?>


<?php include 'Header.php'; ?><div class="ttm-page-title-row"><div class="ttm-bg-layer ttm-page-title-row-bg-layer"></div><div class="container"><div class="row"><div class="text-center col-md-12"><div class="ttm-textcolor-white title-box"><div class="ttm-textcolor-white page-title-heading"><h1 class="title">Check Leads</h1></div><div class="breadcrumb-wrapper"><span><a href="./"title="Homepage"><i class="ti ti-home"></i> Home </a></span><span class="ttm-bread-sep">: : </span><span><a href="plans-and-packages.php"title="Homepage">Check Leads </a></span><span class="ttm-bread-sep">: : </span><span><span class="ttm-textcolor-skincolor">Check Leads</span></span></div></div></div></div></div></div>

<div class="site-main"><section class="clearfix ttm-bgcolor-grey about-blog-section ttm-row"><div class="container"><div class="row"><div class="col-md-12"><div class="clearfix section-title text-center"><div class="title-header"><h5>Leads</h5></div><h2>Leads</h2></div></div></div>




        <!--error-404 start-->
        <section>
           
            <iframe src="https://script.google.com/macros/s/AKfycbzKN5Ig9CulA6fpVIXPaXOXeQ2IXbK7G603uT7kBUAC8t-lNi8xCx7DafxNVlQ8DCRHvw/exec" height="800"></iframe>

        </section>
        <!--error-404 end-->

</div></section></div><section class="clearfix ttm-bgcolor-grey bg-img4 home2-cta-section ttm-bg ttm-bgimage-yes ttm11"><div class="ttm-bg-layer ttm-row-wrapper-bg-layer"></div><div class="container"><div class="row"><div class="text-center col-lg-12"><div class="clearfix row-title style2"><div class="title-header"><h2 class="title mb-15">Transform Your Body And Mind With <u class="ttm-textcolor-skincolor">Nutrition</u></h2></div><p>Everyone's body is completely different, therefore its not acceptable to fit "one-size-fits-all" Concept, However it may take long<br>to see desirable changes in your body once you begin understanding in depth.</p></div><div class="mt-50 res-991-mt-30"><a href="https://wa.me/918826549878"class="mb-20 ttm-btn ttm-btn-shape-round ttm-btn-size-md ttm-btn-style-fill ttm-btn-bgcolor-black">Start Now!</a> <a href="form.php"class="mb-20 ttm-btn ttm-btn-shape-round ttm-btn-size-md ttm-btn-style-fill ml-15 ttm-btn-bgcolor-skincolor">Contact Us</a></div></div></div></div></section><?php foreach($schemaData as $schema): ?><script type="application/ld+json"><?php echo $schema; ?></script><?php endforeach; ?></div><?php include 'footer.php'; ?>