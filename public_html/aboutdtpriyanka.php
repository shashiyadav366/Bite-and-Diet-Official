<?php
// Fetch the JSON data
$jsonFile = 'aboutdieticianpriyanka.json';
if (file_exists($jsonFile)) {
    $jsonData = file_get_contents($jsonFile);
    $data = json_decode($jsonData, true);
} else {
    die("Error: JSON file not found.");
}
?>

<?php include 'Header.php'; ?><div class="ttm-page-title-row"><div class="ttm-bg-layer ttm-page-title-row-bg-layer"></div><div class="container"><div class="row">
  


<div class="col-md-12 text-center"><div class="ttm-textcolor-white title-box"><div class="ttm-textcolor-white page-title-heading"><h1 class="title">Dietician Priyanka</h1></div><div class="breadcrumb-wrapper"><span><a href="./"title="Homepage"><i class="ti ti-home"></i> Home </a></span><span class="ttm-bread-sep">: : </span><span><span class="ttm-textcolor-skincolor">About Us</span></span> <span class="ttm-bread-sep">: : </span><span><span class="ttm-textcolor-skincolor">Our Team</span></span> <span class="ttm-bread-sep">: : </span><span><span class="ttm-textcolor-skincolor">Dietician Priyanka</span></span></div></div></div></div></div></div><div class="site-main"><section class="clearfix team-details-section ttm-row"><div class="container"><div class="row"><div class="col-md-4"><div class="featured-imagebox featured-imagebox-team-details"><div class="featured-thumbnail"><img alt=""class="img-fluid"src="images/single-img-one.webp"></div><div class="featured-content featured-content-data"><div class="featured-title"><h5>Dietician Priyanka</h5></div><div class="ttm-team-position">Dietician</div><ul class="ttm-team-details-list"><li><a href="tel:(+91)88265 49878">Phone: (+91)8826549878</a></li><li><a href="mailto:yourbitemydiet@gmail.com">Mail: yourbitemydiet@gmail.com</a></li><li>Experience: <span class="ttm-textcolor-body">10+ Years</span></li></ul></div></div><div class="row p-3"><div class="col-md-12">
    
    <div><h4 class="font-25">Coach Skills</h4><div class="ttm-progress-bar"><h4>Private Coaching</h4><div class="progress" data-value="85%"><div class="progress-bar progress-bar-color-bar_skincolor"><div class="progress-parcent"><span>85</span></div></div></div></div><div class="ttm-progress-bar"><h4>Weight Loss</h4><div class="progress" data-value="80%"><div class="progress-bar progress-bar-color-bar_skincolor"><div class="progress-parcent"><span>80</span></div></div></div></div><div class="ttm-progress-bar"><h4>Nutrition</h4><div class="progress" data-value="85%"><div class="progress-bar progress-bar-color-bar_skincolor"><div class="progress-parcent"><span>90</span></div></div></div></div><div class="ttm-progress-bar"><h4>Medical Issues</h4><div class="progress" data-value="90%"><div class="progress-bar progress-bar-color-bar_skincolor"><div class="progress-parcent"><span>85</span></div></div></div></div></div>
    
    </div></div></div>

                <div class="col-md-8 mt-5">
                    <div class="ttm-team-member-single-content">
                        <div class="row p-2">
                            <div class="col-md-12">
                          <div class="clearfix section-title">
        <div class="title-header">
            
      <h5 class="title custom_heading"><?php echo htmlspecialchars($data['introduction']['headline']); ?></h5>        </div>
    </div>    
                                <h2><?php echo htmlspecialchars($data['introduction']['subheadline']); ?></h2>
                                <p><?php echo htmlspecialchars($data['introduction']['content']); ?></p>

                                <h3 class="pt-30">Credentials</h3>
                                <ul class="mb-20 ttm-list-style-icon px-1">
                                    <li><li><i class="ttm-textcolor-skincolor fa fa-arrow-circle-right"></i><span class="ttm-list-li-content  px-2"><b>Education:</b> <?php echo htmlspecialchars($data['credentials']['education']); ?></span></li>
                                    <li><li><i class="ttm-textcolor-skincolor fa fa-arrow-circle-right"></i><span class="ttm-list-li-content  px-2"><b>Experience:</b> <?php echo htmlspecialchars($data['credentials']['experience']); ?></span></li>
                                    <li><li><i class="ttm-textcolor-skincolor fa fa-arrow-circle-right"></i><span class="ttm-list-li-content  px-2"><b>Focus:</b> <?php echo htmlspecialchars($data['credentials']['focus']); ?></span></li>
                                </ul>

                                <h3 class="pt-30"><?php echo htmlspecialchars($data['methodology']['headline']); ?></h3>
                                <p><?php echo htmlspecialchars($data['methodology']['content']); ?></p>

                                <h5>Areas Covered:</h5>
                                <ul class="mb-20 ttm-list ttm-list-style-icon">
                                    <!-- Dynamic content will be inserted here -->
                                </ul>

                                <h3 class="pt-30"><?php echo htmlspecialchars($data['philosophy']['headline']); ?></h3>
                                <p><?php echo htmlspecialchars($data['philosophy']['content']); ?></p>

                                <h3 class="pt-30"><?php echo htmlspecialchars($data['clientele']['headline']); ?></h3>
                                <p><?php echo htmlspecialchars($data['clientele']['content']); ?></p>

                                <h3 class="pt-30"><?php echo htmlspecialchars($data['youtube_channel']['headline']); ?></h3>
                                <p><?php echo htmlspecialchars($data['youtube_channel']['description']); ?></p>
                                <a href="<?php echo htmlspecialchars($data['youtube_channel']['url']); ?>" target="_blank">Visit My YouTube Channel</a>
<hr>
                                <h3 class="pt-30">Contact Information</h3>
                                <p><strong>Phone:</strong> <?php echo htmlspecialchars($data['contact']['phone']); ?></p>
                                <p><strong>Email:</strong> <a href="mailto:<?php echo htmlspecialchars($data['contact']['email']); ?>"><?php echo htmlspecialchars($data['contact']['email']); ?></a></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        fetch('diet_plans.json')
            .then(response => response.json())
            .then(data => {
                const areasList = document.querySelector('.ttm-list');
                data.diet_plans.forEach(plan => {
                    const listItem = document.createElement('li');
                    listItem.innerHTML = `<i class="ttm-textcolor-skincolor fa fa-arrow-circle-right"></i><span class="ttm-list-li-content"><a href="/plans-and-packages/${plan.diet_url}">${plan.diet_name}</a></span>`;
                    areasList.appendChild(listItem);
                });
            })
            .catch(error => console.error('Error fetching diet plans:', error));
    });
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Function to animate the progress bars
    function animateProgressBars() {
        $(".progress").each(function() {
            var progressBar = $(this);
            var targetValue = parseFloat(progressBar.data("value"));
            var progressBarElement = progressBar.find(".progress-bar");
            var progressPercent = progressBar.find(".progress-parcent span");
            var currentWidth = 0;
            var duration = 5000; // Adjust animation duration
            var startTime = null;

            // Animation function
            function animate(time) {
                if (!startTime) startTime = time;
                var progress = Math.min((time - startTime) / duration, 1); // Calculate progress
                var newWidth = Math.round(progress * targetValue); // Calculate new width
                progressBarElement.css("width", newWidth + "%");
                progressPercent.text(newWidth + "%");
                if (progress < 1) {
                    requestAnimationFrame(animate); // Continue animation
                } else {
                    progressBarElement.css("width", targetValue + "%"); // Ensure final width matches target
                    progressPercent.text(targetValue + "%");
                }
            }

            requestAnimationFrame(animate);
        });
    }

    // Set up the Intersection Observer
    var options = {
        root: null, // Use the viewport as the container
        rootMargin: '0px',
        threshold: 0.1 // Trigger when at least 10% of the element is visible
    };

    var observer = new IntersectionObserver(function(entries, observer) {
        entries.forEach(function(entry) {
            if (entry.isIntersecting) {
                // Animate the progress bars when they come into view
                animateProgressBars();
                // Unobserve the element after it has been animated
                observer.unobserve(entry.target);
            }
        });
    }, options);

    // Observe each progress bar element
    $(".progress").each(function() {
 
       observer.observe(this);
    });
});
</script>

<?php include 'footer.php'; ?>
