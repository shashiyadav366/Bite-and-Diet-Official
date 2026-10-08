<?php
session_start();
$_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../login.php");
    exit;
}

include '../Header.php';

// Enable error reporting
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);

?>
<div class="ttm-page-title-row">
    <div class="ttm-bg-layer ttm-page-title-row-bg-layer"></div>
    <div class="container">
        <div class="row">
            <div class="text-center col-md-12">
                <div class="ttm-textcolor-white title-box">
                    <div class="ttm-textcolor-white page-title-heading">
                        <h1 class="title">Visitors Details</h1>
                    </div>
                    <div class="breadcrumb-wrapper">
                        <span><a href="/" title="Homepage"><i class="ti ti-home"></i> Home </a></span>
                        <span class="ttm-bread-sep">: : </span>
                        <span><span class="ttm-textcolor-skincolor">Visitors Details</span></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container my-5" style="
    min-height: 400px;
">
    <h2 class="custom_heading text-center my-4">Visitor Data</h2>

    <?php
    // Path to the JSON file
    $file = __DIR__ . '/visitors.json'; // Absolute path
    $ignored_ips = ['150.129.237.196', '125.63.113.186', '2409:40d0:2029:8c0a:c96:75ff:fe0b:2cd1', '152.59.180.114'];

    $unique_visitors = [];
    $current_date = date('Y-m-d'); // Store the current date

    // Check if file exists
    if (!file_exists($file)) {
        echo "<p class='text-center text-danger'>Error: visitors.json file does not exist.</p>";
        error_log("visitors.json file does not exist in path: " . $file);
    } else {
        // Read the existing data from the JSON file
        $data = json_decode(file_get_contents($file), true);

        // Organize visits by date
        foreach ($data as $visitor) {
            if (in_array($visitor['ip'], $ignored_ips)) {
                continue; // Skip ignored IPs
            }

            foreach ($visitor['visits'] as $visit) {
                $date = date('Y-m-d', strtotime($visit['time']));
                if (!isset($unique_visitors[$date])) {
                    $unique_visitors[$date] = [];
                }
                if (!in_array($visitor['ip'], array_column($unique_visitors[$date], 'ip'))) {
                    $unique_visitors[$date][] = [
                        'ip' => $visitor['ip'],
                        'visits' => [$visit],
                    ];
                } else {
                    // Find the existing visitor and append the visit
                    foreach ($unique_visitors[$date] as &$unique_visitor) {
                        if ($unique_visitor['ip'] === $visitor['ip']) {
                            $unique_visitor['visits'][] = $visit;
                        }
                    }
                }
            }
        }
    }

    // Sort the dates in reverse chronological order (from latest to oldest)
    krsort($unique_visitors);
    ?>

    <!-- Display unique IP count -->
    <h5 class="text-center">Total Unique Visitors: 
        <?php 
        $visitor_ips = [];
        foreach ($unique_visitors as $visitors_by_date) {
            foreach ($visitors_by_date as $visitor) {
                $visitor_ips[] = $visitor['ip'];  // Collect all visitor IPs
            }
        }
        $total_visitors = count(array_unique($visitor_ips));  // Count unique IPs
        echo htmlspecialchars($total_visitors); 
        ?>
    </h5>

    <!-- Accordion structure for displaying visits organized by date -->
    <div class="row">
        <div class="col-md-12">
            <div class="accordion mb-20" id="accordion">
                <?php foreach ($unique_visitors as $date => $visitors): ?>
                    <?php
                    // Count unique visitors for each date
                    $unique_visitor_count = count(array_unique(array_column($visitors, 'ip')));
                    ?>
                    <div class="toggle ttm-style-classic ttm-toggle-title-border <?php echo $date === reset(array_keys($unique_visitors)) ? 'active' : ''; ?>">
                        <div class="toggle-title">
                            <a href="#collapse_<?php echo htmlspecialchars($date); ?>" data-parent="#accordion" data-toggle="collapse">
                                <?php echo htmlspecialchars($date); ?> (Visitors: <?php echo htmlspecialchars($unique_visitor_count); ?>)
                            </a>
                        </div>
                        <div id="collapse_<?php echo htmlspecialchars($date); ?>" class="toggle-content collapse <?php echo $date === reset(array_keys($unique_visitors)) ? 'show' : ''; ?>">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead class="thead-dark">
                                        <tr>
                                            <th>IP Address</th>
                                            <th>Pages Visited</th>
                                            <th>Date & Time</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($visitors as $visitor): ?>
                                            <?php foreach ($visitor['visits'] as $visit): ?>
                                                <tr style="text-align:start">
                                                    <td><?php echo htmlspecialchars($visitor['ip']); ?></td>
                                                    <td>
                                                        <ul class="ttm-list ttm-list-style-icon">
                                                            <?php foreach ($visit['pages'] as $page): 
                                                                $full_url = 'https://biteanddiet.in' . $page;
                                                                $display_url = strlen($page) > 40 ? substr($page, 0, 40) . '...' : $page;
                                                            ?>
                                                                <li>
                                                                    <i class="ttm-textcolor-skincolor fa fa-arrow-circle-right"></i>
                                                                    <span class="ttm-list-li-content">
                                                                        <a href="<?php echo htmlspecialchars($full_url); ?>" target="_blank">
                                                                            <?php echo htmlspecialchars($display_url); ?>
                                                                        </a>
                                                                    </span>
                                                                </li>
                                                            <?php endforeach; ?>
                                                        </ul>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($visit['time']); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div> <!-- end of table-responsive -->
                        </div> <!-- end of collapse content -->
                    </div> <!-- end of toggle -->
                <?php endforeach; ?>
            </div> <!-- end of accordion -->
        </div> <!-- end of col-md-12 -->
    </div> <!-- end of row -->
</div> <!-- end of container -->

          <section class="clearfix ttm-bg ttm-bgimage-yes bg-img4 home2-cta-section ttm-bgcolor-grey ttm11"><div class="ttm-bg-layer ttm-row-wrapper-bg-layer"></div><div class="container"><div class="row"><div class="text-center col-lg-12"><div class="clearfix row-title style2"><div class="title-header"><h2 class="title mb-15">Transform Your Body And Mind With <span class="ttm-textcolor-skincolor">Nutrition</span></h2></div><p>Everyone's body is completely different; therefore it's not acceptable to fit the "one-size-fits-all" concept. However, it may take long<br>to see desirable changes in your body once you begin understanding in depth.</p></div><div class="res-991-mt-30 mt-50"><a href="https://wa.me/918076596075"class="ttm-btn mb-20 ttm-btn-shape-round ttm-btn-size-md ttm-btn-style-fill ttm-btn-bgcolor-black">Start Now!</a> <a href="/form"class="ttm-btn mb-20 ttm-btn-shape-round ttm-btn-size-md ttm-btn-style-fill ml-15 ttm-btn-bgcolor-skincolor">Contact Us</a></div></div></div></div> </section> 


<?php include '../footer.php'; ?>
