<?php
// Load diet plans from JSON file
$json_file = 'diet_plans.json';
if (!file_exists($json_file)) {
    header("HTTP/1.0 500 Internal Server Error");
    echo "Diet plans file not found.";
    exit;
}

$data = json_decode(file_get_contents($json_file), true);

// Check for JSON decoding errors
if (json_last_error() !== JSON_ERROR_NONE) {
    header("HTTP/1.0 500 Internal Server Error");
    echo "Error decoding JSON.";
    exit;
}
?>

<?php include 'Header.php'; ?>
<main>
    <div class="ttm-page-title-row">
        <div class="ttm-bg-layer ttm-page-title-row-bg-layer"></div>
        <div class="container">
            <div class="row">
                <div class="col-md-12 text-center">
                    <div class="ttm-textcolor-white title-box">
                        <div class="ttm-textcolor-white page-title-heading">
                            <h1 class="title">Plans And Packages</h1>
                        </div>
                        <div class="breadcrumb-wrapper">
                            <span><a href="/" title="Homepage"><i class="ti ti-home"></i> Home </a></span>
                            <span class="ttm-bread-sep">: : </span>
                            <span><span class="ttm-textcolor-skincolor">Diet Programs</span></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="site-main">
        <section class="clearfix portfolio-style-section ttm-row">
            <div class="container">
                <div class="row">
                <div class="col-lg-12 mt-5">
                    
                        <header class="text-center section-title"><h5>Diet Plans</h5></header>

                        

            </div>
                    <?php
                    $dietCount = 0;
                    $schemaData = [];

                    foreach ($data['diet_plans'] as $diet) {
                        $dietId = $diet['diet_id'];
                        $dietName = $diet['diet_name'];
                        $dietUrl = $diet['diet_url'];
                        $dietCount++;
                        $columnClass = ($dietCount % 3 == 1) ? 'col-lg-4 col-md-6' : 'col-lg-4 col-md-6 col-sm-6 col-12';

                        echo '<div class="'.$columnClass.'">';
                        echo '    <div class="featured-imagebox featured-imagebox-team ttm-box-view-top-image ttm-team-box-view-overlay box-shadow1 mb-30">';
                        echo '        <div class="featured-imagebox-team-inner">';
                        echo '            <div class="featured-thumbnail">';
                        echo '                <img class="img-fluid" style="max-height:260px" src="/images/dietplan_images/'.$dietId.'.jpg" alt="'.$dietName.'">';
                        echo '            </div>';
                        echo '            <div class="ttm-box-view-overlay">';
                        echo '            </div>';
                        echo '        </div>';
                        echo '        <div class="ttm-box-bottom-content">';
                        echo '            <div class="featured-title">';
                        echo '                <h5><a href="/plans-and-packages/'.$dietUrl.'">'.$dietName.'</a></h5>';
                        echo '            </div>';
                        echo '        </div>';
                        echo '    </div>';
                        echo '</div>';

                        // Schema Markup
                        $schemaMarkup = [
                            "@context" => "http://schema.org",
                            "@type" => "DietPlan",
                            "name" => $dietName,
                            "description" => "Description of the diet plan: ".$dietName,
                            "url" => "https://www.biteanddiet.in/plans-and-packages/".$dietUrl
                        ];
                        $schemaData[] = $schemaMarkup;
                    }
                    ?>
                    <?php if (empty($data['diet_plans'])): ?>
                        <p>No records found</p>
                    <?php endif; ?>
                </div>
                <?php foreach ($schemaData as $schema): ?>
                    <script type="application/ld+json"><?php echo json_encode($schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES); ?></script>
                <?php endforeach; ?>
            </div>
        </section>
    </div>
</main>

<?php include 'footer.php'; ?>


   