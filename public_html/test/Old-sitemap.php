<?php include 'Header.php'; ?>
<div class="ttm-page-title-row"><div class="ttm-bg-layer ttm-page-title-row-bg-layer"></div><div class="container"><div class="row"><div class="text-center col-md-12"><div class="ttm-textcolor-white title-box"><div class="ttm-textcolor-white page-title-heading"><h1 class="title">Sitemap</h1></div><div class="breadcrumb-wrapper"><span><a href="./"title="Homepage"><i class="ti ti-home"></i> Home </a></span><span class="ttm-bread-sep">: : </span><span><span class="ttm-textcolor-skincolor">Sitemap</span></span></div></div></div></div></div></div><section class="break-991-colum checkout-section clearfix ttm-row"><div class="container"><div class="row"><div class="col-lg-12"><div class="container">
    
    

    
   
    <h1 class="text-center py-5">Sitemap</h1>
    <p>Welcome to the Bite and Diet sitemap. Below is a comprehensive list of all our pages, categorized for easy navigation.</p>
    
    <h2>Main Pages</h2>
      <ul class="ttm-list ttm-list-style-icon">
        <li><i class="fa fa-arrow-circle-right ttm-textcolor-skincolor px-2"></i><a class="mx-2" href="/latest-offers">Latest Offers on Diet Plans</a></li>

        <li><i class="fa fa-arrow-circle-right ttm-textcolor-skincolor px-2"></i><a class="mx-2" href="/aboutdtpriyanka">About Dt. Priyanka - Founder & Chief Dietician</a></li>
        <li><i class="fa fa-arrow-circle-right ttm-textcolor-skincolor px-2"></i><a class="mx-2" href="/aboutbiteanddiet">About Bite and Diet - Our Mission & Vision</a></li>
        <li><i class="fa fa-arrow-circle-right ttm-textcolor-skincolor px-2"></i><a class="mx-2" href="/whybiteanddiet">Why Choose Bite and Diet?</a></li>
        <li><i class="fa fa-arrow-circle-right ttm-textcolor-skincolor px-2"></i><a class="mx-2" href="/success-stories">Success Stories - Real Results from Our Clients</a></li>
        <li><i class="fa fa-arrow-circle-right ttm-textcolor-skincolor px-2"></i><a class="mx-2" href="/our-team">Meet Our Team</a></li>
        <li><i class="fa fa-arrow-circle-right ttm-textcolor-skincolor px-2"></i><a class="mx-2" href="/online-counselling">Online Diet Counselling Services</a></li>
        <li><i class="fa fa-arrow-circle-right ttm-textcolor-skincolor px-2"></i><a class="mx-2" href="/personal-counselling">Personal Counselling & Tailored Diet Plans</a></li>
          <li><i class="fa fa-arrow-circle-right ttm-textcolor-skincolor px-2"></i><a class="mx-2" href="/plans-and-packages">Plans and Packages - Choose Your Plan</a></li>
                <li><i class="fa fa-arrow-circle-right ttm-textcolor-skincolor px-2"></i><a class="mx-2" href="/plans-and-price">Plans and Pricing - Affordable Diet Solutions</a></li>
   
        <li><i class="fa fa-arrow-circle-right ttm-textcolor-skincolor px-2"></i><a class="mx-2" href="/training">Training Programs - Learn from the Experts</a></li>
        <li><i class="fa fa-arrow-circle-right ttm-textcolor-skincolor px-2"></i><a class="mx-2" href="/form">Get Started with Diet Plan- Registration Form</a></li>
                <li><i class="fa fa-arrow-circle-right ttm-textcolor-skincolor px-2"></i><a class="mx-2" href="/allposts">Blog Posts & Articles</a></li>
        <li>         <i class="fa fa-arrow-circle-right ttm-textcolor-skincolor px-2"></i><a href="/social_posts" class="mx-2">Latest Social Posts</a></li>
             <li><i class="fa fa-arrow-circle-right ttm-textcolor-skincolor px-2"></i><a class="mx-2" href="/allvideos">Videos - Health Tips & Diet Plans</a></li>
       
        <li><i class="fa fa-arrow-circle-right ttm-textcolor-skincolor px-2"></i><a class="mx-2" href="/ayurvedic-herbs">Ayurvedic Herbs & Their Benefits</a></li>
   
      
        
        <li><i class="fa fa-arrow-circle-right ttm-textcolor-skincolor px-2"></i><a class="mx-2" href="/bmi-calculator">BMI Calculator - Check Your Body Mass Index</a></li>
     <li><i class="fa fa-arrow-circle-right ttm-textcolor-skincolor px-2"></i><a class="mx-2" href="/customer_reviews">Customer Reviews - Hear from Our Clients</a></li>
         <li><i class="fa fa-arrow-circle-right ttm-textcolor-skincolor px-2"></i><a class="mx-2" href="/faq">Frequently Asked Questions</a></li>
        <li><i class="fa fa-arrow-circle-right ttm-textcolor-skincolor px-2"></i><a class="mx-2" href="/privacy-policy">Privacy Policy</a></li>
        <li><i class="fa fa-arrow-circle-right ttm-textcolor-skincolor px-2"></i><a class="mx-2" href="/contact-us">Contact Us</a></li>
        <li><i class="fa fa-arrow-circle-right ttm-textcolor-skincolor px-2"></i><a class="mx-2" href="/terms-and-conditions">Terms and Conditions</a></li>
    </ul>
<hr>
<h2>Diet Plans Available</h2>
 <?php
// Load diet plans from JSON file
$diets = json_decode(file_get_contents('diet_plans.json'), true);

// Check if the JSON data was successfully decoded
if (!$diets || !isset($diets['diet_plans'])) {
    echo "Error loading diet plans.";
    exit;
}

// Generate HTML list
echo '<ul class="ttm-list ttm-list-style-icon">';
foreach ($diets['diet_plans'] as $diet) {
    $diet_name = htmlspecialchars($diet['diet_name']);
    $diet_url = htmlspecialchars($diet['diet_url']);
    echo '<li><i class="fa fa-arrow-circle-right ttm-textcolor-skincolor px-2"></i><a class="mx-2" href="/' . $diet_url . '">' . $diet_name . ' - ' . htmlspecialchars($diet['title']) . '</a></li>';
}
echo '</ul>';
?>

<hr>
<h2>Success Story PDFs</h2>
  <ul class="ttm-list ttm-list-style-icon">
    <li><i class="fa fa-arrow-circle-right ttm-textcolor-skincolor px-2"></i><a class="mx-2" href="/success_story/How%20Sakshi%20reduced%20her%20blood%20sugar%20from%20350%20to%20125.pdf">How Sakshi Reduced Her Blood Sugar from 350 to 125 - Success Story 14</a></li>
    <li><i class="fa fa-arrow-circle-right ttm-textcolor-skincolor px-2"></i><a class="mx-2" href="/success_story/Nutrition%20Strategy%20followed%20by%20Geeta%20for%20weight%20management.pdf">Nutrition Strategy Followed by Geeta for Weight Management - Success Story 13</a></li>
    <li><i class="fa fa-arrow-circle-right ttm-textcolor-skincolor px-2"></i><a class="mx-2" href="/success_story/Rakesh%20sperm.pdf">Rakesh's Journey to Improved Sperm Quality - Success Story 12</a></li>
    <li><i class="fa fa-arrow-circle-right ttm-textcolor-skincolor px-2"></i><a class="mx-2" href="/success_story/Rishi.pdf">Rishi's Transformative Health Journey - Success Story 11</a></li>
    <li><i class="fa fa-arrow-circle-right ttm-textcolor-skincolor px-2"></i><a class="mx-2" href="/success_story/Ashwini.pdf">Ashwini's Diet Plan for a Healthier Life - Success Story 10</a></li>
    <li><i class="fa fa-arrow-circle-right ttm-textcolor-skincolor px-2"></i><a class="mx-2" href="/success_story/Duggu.pdf">Duggu's Incredible Weight Loss Journey - Success Story 9</a></li>
    <li><i class="fa fa-arrow-circle-right ttm-textcolor-skincolor px-2"></i><a class="mx-2" href="/success_story/Nitasa.pdf">Nitasa's Nutrition Plan for Better Health - Success Story 8</a></li>
    <li><i class="fa fa-arrow-circle-right ttm-textcolor-skincolor px-2"></i><a class="mx-2" href="/success_story/How%20Barkha%20lost%2010Kg%20in%203%20months.pdf">How Barkha Lost 10Kg in 3 Months - Success Story 7</a></li>
    <li><i class="fa fa-arrow-circle-right ttm-textcolor-skincolor px-2"></i><a class="mx-2" href="/success_story/story6.pdf">Mr. Swarn Singh has successfully reduced
his cholesterol without medicines - Success Story 6 - A Journey of Health and Wellness</a></li>
    <li><i class="fa fa-arrow-circle-right ttm-textcolor-skincolor px-2"></i><a class="mx-2" href="/success_story/story5.pdf">Mr.Abhay shally lost 7Kilos in 6 weeks
 - Success Story 5 - From Struggles to Success</a></li>
    <li><i class="fa fa-arrow-circle-right ttm-textcolor-skincolor px-2"></i><a class="mx-2" href="/success_story/story4.pdf">Dr.Madanlal successfully reduced his dose 
of insulin from 40units to 15 units
- Success Story 4 - Transforming Lives Through Diet</a></li>
    <li><i class="fa fa-arrow-circle-right ttm-textcolor-skincolor px-2"></i><a class="mx-2" href="/success_story/story3.pdf">I Lost Weight Despite My Thyroid Condition ...
- Success Story 3 - A Path to Better Health</a></li>
    <li><i class="fa fa-arrow-circle-right ttm-textcolor-skincolor px-2"></i><a class="mx-2" href="/success_story/story2.pdf">Now Mr. Naresh Tiwari Donâ€™t Need To
Take Diabetes Medicines
- Success Story 2 - Overcoming Health Challenges</a></li>
    <li><i class="fa fa-arrow-circle-right ttm-textcolor-skincolor px-2"></i><a class="mx-2" href="/success_story/story1.pdf">Anjali reduced her hair fall complaint
with Antiaging Diet - Success Story 1 - A New Beginning Through Nutrition</a></li>
</ul>

<hr>
<?php
// File paths
$youtube_videos_file = 'youtube_videos.json';
$youtube_backup_file = 'youtube_videos-backup.json';

// Function to check if a file contains valid JSON
function isValidJsonFile($file) {
    if (file_exists($file) && filesize($file) > 0) {
        $fileContents = file_get_contents($file);
        $jsonData = json_decode($fileContents, true);
        return $jsonData !== null ? $jsonData : false;
    }
    return false;
}

// Load the main file first
$videos = isValidJsonFile($youtube_videos_file);

// If the main file is invalid or empty, fallback to the backup file
if (!$videos) {
    $videos = isValidJsonFile($youtube_backup_file);
    if (!$videos) {
        // Handle the case where both files are invalid or empty
        die("Error: Unable to load valid YouTube video data.");
    }
}

// Generate the HTML
echo '<h2>Our Videos</h2>';
echo '<ul class="ttm-list ttm-list-style-icon">';

// Loop through each video and create a list item
foreach ($videos as $video) {
    $videoId = $video['videoId']; // Use 'videoId' from your JSON data
    $videoTitleJson = html_entity_decode($video['title'], ENT_QUOTES, 'UTF-8'); // Decode title

    if (!empty($videoId)) {
        $fullUrl = "https://youtu.be/" . htmlspecialchars($videoId); // Construct the full URL

        // Output the list item with properly formatted href
        echo '<li><i class="fa fa-arrow-circle-right ttm-textcolor-skincolor px-2"></i>';
        echo '<a class="mx-2" href="' . $fullUrl . '">' . $videoTitleJson . '</a></li>';
    }
}

echo '</ul>';
?>

<hr>

<h2>Blog Posts</h2>

   <?php
// Load the cached blog posts data from the JSON file
$blogData = json_decode(file_get_contents(__DIR__ . '/allposts.json'), true);

function renderPostListItem($post) {
    return "
        <li class=''>
            <i class='fa fa-arrow-circle-right ttm-textcolor-skincolor px-2'></i>
            <a class='mx-2' href='
            
            /readpost?url={$post['postUrl']}
            
            '
            >{$post['title']}</a>
        </li>
    ";
}

// Generate HTML for all posts
$postsHtml = '<ul class="ttm-list ttm-list-style-icon">';
foreach ($blogData as $post) {
    $postsHtml .= renderPostListItem($post);
}
$postsHtml .= '</ul>';

// Output the HTML
echo $postsHtml;
?>

<hr>



<?php
// File paths for Instagram posts
$insta_posts_file = 'insta_posts.json';
$insta_backup_file = 'insta_posts-backup.json';

// Function to check if a file contains valid JSON
function isValidSocialJsonFile($file) {
    if (file_exists($file) && filesize($file) > 0) {
        $fileContents = file_get_contents($file);
        $jsonData = json_decode($fileContents, true);
        return $jsonData !== null ? $jsonData : false;
    }
    return false;
}

// Function to limit the caption to a specified number of words
function limitWords($text, $wordLimit = 20) {
    $words = explode(' ', $text);
    if (count($words) > $wordLimit) {
        return implode(' ', array_slice($words, 0, $wordLimit)) . '...'; // Truncate and add ellipsis
    }
    return $text;
}

// Load the main Instagram posts file first
$instaPosts = isValidSocialJsonFile($insta_posts_file);

// If the main file is invalid or empty, fallback to the backup file
if (!$instaPosts) {
    $instaPosts = isValidSocialJsonFile($insta_backup_file);
    if (!$instaPosts) {
        // Handle the case where both files are invalid or empty
        die("Error: Unable to load valid Instagram post data.");
    }
}

// Generate the HTML
echo '<h2>Our Social Posts</h2>';
echo '<ul class="ttm-list ttm-list-style-icon">';
// Function to remove emojis and non-ASCII characters
function removeEmojis($text) {
    return preg_replace('/[^\x20-\x7E]/', '', $text); // Removes non-ASCII characters including emojis
}

// Use an array to track unique post IDs
$uniquePosts = [];

// Loop through each Instagram post and create a list item
foreach ($instaPosts as $post) {
    $postId = $post['id'] ?? null; // Use the 'id' to check for uniqueness, ensure it's set
    $caption = isset($post['caption']) ? html_entity_decode($post['caption'], ENT_QUOTES, 'UTF-8') : ''; // Decode caption safely
    $cleanCaption = removeEmojis($caption); // Remove emojis from the caption
    $limitedCaption = limitWords($cleanCaption, 15); // Limit the cleaned caption to 20 words

    // Check for duplicates based on the post ID
    if ($postId && !in_array($postId, $uniquePosts)) {
        $uniquePosts[] = $postId; // Add the post ID to the array to mark it as processed

        // Output the list item with properly formatted href using the custom URL format
        echo '<li><i class="fa fa-arrow-circle-right ttm-textcolor-skincolor px-2"></i>';
        echo '<a class="mx-2" href="/social_post_details?id=' . $postId . '">' . $limitedCaption . '</a></li>';
    }
}

echo '</ul>';


?>








<hr>
<h2>Our Social Media </h2>
<ul class="ttm-list ttm-list-style-icon">
    <li><i class="fa fa-arrow-circle-right ttm-textcolor-skincolor px-2"></i><a class="mx-2" href="https://facebook.com/YourBiteMyDiet">Facebook - Bite And Diet</a></li>
     <li><i class="fa fa-arrow-circle-right ttm-textcolor-skincolor px-2"></i><a class="mx-2" href="https://www.facebook.com/profile.php?id=100008839540596">Facebook - Dietician Priyanka</a></li>
        <li><i class="fa fa-arrow-circle-right ttm-textcolor-skincolor px-2"></i><a class="mx-2" href="https://www.facebook.com/groups/607609174160047/?ref=share&mibextid=NSMWBT">Facebook Group - Bite And Diet</a></li>
    <li><i class="fa fa-arrow-circle-right ttm-textcolor-skincolor px-2"></i><a class="mx-2" href="https://linkedin.com/company/bite-and-diet-nutritionist-consultation/">LinkedIn - Bite And Diet - Diet Consultation</a></li>
        <li><i class="fa fa-arrow-circle-right ttm-textcolor-skincolor px-2"></i><a class="mx-2" href="https://www.linkedin.com/in/dietician-priyanka">LinkedIn - Dietician Priyanka</a></li>
    <li><i class="fa fa-arrow-circle-right ttm-textcolor-skincolor px-2"></i><a class="mx-2" href="https://instagram.com/bite_and_diet/">Instagram - Bite And Diet</a></li>
      <li><i class="fa fa-arrow-circle-right ttm-textcolor-skincolor px-2"></i><a class="mx-2" href="https://www.instagram.com/dietician_priyanka_">Instagram - Dietician Priyanka</a></li>
    <li><i class="fa fa-arrow-circle-right ttm-textcolor-skincolor px-2"></i><a class="mx-2" href="https://youtube.com/channel/UCwl1Lbkl0PhwYm8j3j8fo1A">YouTube - Bite And Diet Channel</a></li>
    <li><i class="fa fa-arrow-circle-right ttm-textcolor-skincolor px-2"></i><a class="mx-2" href="https://twitter.com/Biteandiet">Twitter - Bite And Diet</a></li>
    <li><i class="fa fa-arrow-circle-right ttm-textcolor-skincolor px-2"></i><a class="mx-2" href="https://biteandiet.blogspot.com/">Blog - Bite And Diet</a></li>
    <li><i class="fa fa-arrow-circle-right ttm-textcolor-skincolor px-2"></i><a class="mx-2" href="https://quora.com/profile/BiteandDiet">Quora - Bite And Diet</a></li>
    <li><i class="fa fa-arrow-circle-right ttm-textcolor-skincolor px-2"></i><a class="mx-2" href="https://whatsapp.com/channel/0029VaEqc7OLtOjA7NMwYV0u">WhatsApp Channel - Bite And Diet</a></li>


</ul>



<hr>
<div id="month-year"></div>


<script>
// Get the current date
const currentDate = new Date();

// Extract the current month and year
const month = currentDate.getMonth(); // Returns a zero-based month (0 for January, 1 for February, etc.)
const year = currentDate.getFullYear();

// Define an array of month names
const monthNames = [
    "January", "February", "March", "April", "May", "June",
    "July", "August", "September", "October", "November", "December"
];

// Format the date as "Month Year"
const formattedDate = `${monthNames[month]}, ${year}`;

// Set the innerHTML of the element with ID "month-year"
document.getElementById("month-year").innerHTML = formattedDate;
</script>
	
    
    
    </div></div></div></div></section><?php include 'footer.php'; ?>