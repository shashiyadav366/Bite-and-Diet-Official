<?php include 'Header.php'; ?>  
  
<div class="ttm-page-title-row">  
    <div class="ttm-bg-layer ttm-page-title-row-bg-layer"></div>  
    <div class="container">  
        <div class="row">  
            <div class="text-center col-md-12">  
                <div class="ttm-textcolor-white title-box">  
                    <div class="ttm-textcolor-white page-title-heading">  
                        <h1 class="title">Plans And Price</h1>  
                    </div>  
                    <div class="breadcrumb-wrapper">  
                        <span><a href="./" title="Homepage"><i class="ti ti-home"></i> Home </a></span>  
                        <span class="ttm-bread-sep">: : </span>  
                        <span><a href="plans-and-packages.php" title="Homepage">Plans And Packages </a></span>  
                        <span class="ttm-bread-sep">: : </span>  
                        <span><span class="ttm-textcolor-skincolor">Plans And Price</span></span>  
                    </div>  
                </div>  
            </div>  
        </div>  
    </div>  
</div>  
  
<div class="site-main">  
    <section class="clearfix ttm-bgcolor-grey about-blog-section ttm-row">  
        <div class="container">  
            <div class="row">  
                <div class="col-md-12">  
                    <div class="clearfix section-title text-center">  
                        <div class="title-header">  
                            <h5>Plan & Pricing</h5>  
                        </div>  
                        <h2>Choose Your Plan</h2>  
                    </div>  
                </div>  
            </div>  
            <div class="row pricetable_row px-1">  
<?php  
                require_once __DIR__ . '/json_db.php';  
                $plans = jd_read('price_plans');  
                $schemaData = array();  

                function formatPlanPrice($raw, $planName) {  
                    $name = strtolower((string)$planName);  
                    if (strpos($name, 'orginal') !== false) {  
                        return '<del>' . $raw . '</del>';  
                    }  
                    if (strpos($name, 'offer') !== false) {  
                        return '<strong class="red">' . $raw . '</strong>';  
                    }  
                    return $raw;  
                }  

                if (!empty($plans)) {  
                    echo '<style>.red { color: red;font-weight: bold; }</style>';  
                    echo '<div class="table-responsive pricetable">';  
                    echo '<table class="table table-bordered table-hover table-striped">';  
                    echo '    <thead>';  
                    echo '        <tr>';  
                    echo '            <th scope="col" class="text-center align-middle">PLANS</th>';  
                    echo '            <th scope="col" class="text-center align-middle">CUSTOMIZED PLAN</th>';  
                    echo '            <th scope="col" class="text-center align-middle">30 DAYS</th>';  
                    echo '            <th scope="col" class="text-center align-middle">100 DAYS</th>';  
                    echo '            <th scope="col" class="text-center align-middle">200 DAYS</th>';  
                    echo '            <th scope="col" class="text-center align-middle">365 DAYS</th>';  
                    if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {  
                        echo '            <th scope="col" class="text-center align-middle">Action</th>';  
                    }  
                    echo '        </tr>';  
                    echo '    </thead>';  
                    echo '    <tbody>';  
                       
                    foreach ($plans as $row) {  
                        $row['customized_plan'] = formatPlanPrice($row['customized_plan'], $row['plan_name']);  
                        $row['days_30'] = formatPlanPrice($row['days_30'], $row['plan_name']);  
                        $row['days_100'] = formatPlanPrice($row['days_100'], $row['plan_name']);  
                        $row['days_200'] = formatPlanPrice($row['days_200'], $row['plan_name']);  
                        $row['days_365'] = formatPlanPrice($row['days_365'], $row['plan_name']);  

                        $schemaMarkup = array(  
                            "@context" => "http://schema.org",  
                            "@type" => "Product",  
                            "name" => $row['plan_name'],  
                            "description" => "Description of the plan: " . $row['plan_name'],  
                            "offers" => array(  
                                "@type" => "Offer",  
                                "priceCurrency" => "INR",  
                                "price" => preg_replace('/<[^>]+>/', '', $row['customized_plan']), // Remove HTML tags  
                                "priceValidUntil" => date('Y-m-d', strtotime('+1 year')), // Example validity date  
                                "itemOffered" => array(  
                                    "@type" => "Service",  
                                    "serviceType" => $row['plan_name'],  
                                    "additionalType" => "http://schema.org/Plan"  
                                )  
                            ),  
                            "customizedPlan" => $row['customized_plan'],  
                            "days30" => $row['days_30'],  
                            "days100" => $row['days_100'],  
                            "days200" => $row['days_200'],  
                            "days365" => $row['days_365']  
                        );  
                        $schemaData[] = json_encode($schemaMarkup, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);  
                           
                        echo '        <tr>';  
                        echo '            <th scope="row" itemprop="name">' . $row['plan_name'] . '</th>';  
                        echo '            <td itemprop="customizedPlan">' . $row['customized_plan'] . '</td>';  
                        echo '            <td itemprop="days30">' . $row['days_30'] . '</td>';  
                        echo '            <td itemprop="days100">' . $row['days_100'] . '</td>';  
                        echo '            <td itemprop="days200">' . $row['days_200'] . '</td>';  
                        echo '            <td itemprop="days365">' . $row['days_365'] . '</td>';  
                        if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {  
                            echo '            <td class="text-center align-middle">';  
                            echo '                <a href="/admin/edit_price_plan.php?id=' . $row['id'] . '"><i class="fa fa-edit"></i></a>';  
                            echo '            </td>';  
                        }  
                        echo '        </tr>';  
                    }  

                    echo '    </tbody>';  
                    echo '</table>';  
                    echo '</div>';  
                } else {  
                    echo "No records found";  
                }  
                ?>
            </div  
          
          
              
              
            <p class="text-center pt-4" style="color: red">Note: Plan is non-refundable and non-transferable.</p>  
        </div>  
    </section>  
  
    <section class="clearfix ttm-bgcolor-grey bg-img4 home2-cta-section ttm-bg ttm-bgimage-yes ttm11">  
        <div class="ttm-bg-layer ttm-row-wrapper-bg-layer"></div>  
        <div class="container">  
            <div class="row">  
                <div class="text-center col-lg-12">  
                    <div class="clearfix row-title style2">  
                        <div class="title-header">  
                            <h2 class="title mb-15">Transform Your Body And Mind With <u class="ttm-textcolor-skincolor">Nutrition</u></h2>  
                        </div>  
                        <p>Everyone's body is completely different, therefore its not acceptable to fit "one-size-fits-all" Concept, However it may take long<br>to see desirable changes in your body once you begin understanding in depth.</p>  
                    </div>  
                    <div class="mt-50 res-991-mt-30">  
                        <a href="https://wa.me/918826549878" class="mb-20 ttm-btn ttm-btn-shape-round ttm-btn-size-md ttm-btn-style-fill ttm-btn-bgcolor-black">Start Now!</a>  
                        <a href="form.php" class="mb-20 ttm-btn ttm-btn-shape-round ttm-btn-size-md ttm-btn-style-fill ml-15 ttm-btn-bgcolor-skincolor">Contact Us</a>  
                    </div>  
                </div>  
            </div>  
        </div>  
    </section>  
</div>  
  
  
<script type="application/ld+json"><?php echo '[' . implode(",\n", $schemaData) . ']'; ?></script>  
  
  
<?php include 'footer.php'; ?>  
