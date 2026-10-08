<!DOCTYPE html><html lang="en"><head>
 
<?php
session_start();
$loggedIn = isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true;

require_once 'credentials.php';
$conn = new mysqli($dbHost, $dbUsername, $dbPassword, $dbName);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// ✅ Extract slug from the URL
//$slug = basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
$slug = isset($_GET['slug']) ? $_GET['slug'] : '';
if (empty($slug)) {
    header("Location: /allposts");
    exit;
}

// ✅ Fetch blog post by slug
$stmt = $conn->prepare("SELECT id, title, description, image FROM blog_posts WHERE slug = ?");
$stmt->bind_param("s", $slug);
$stmt->execute();
$result = $stmt->get_result();

$title = $description = $image = $currentCount = "";
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $id = $row['id']; // used for view count file
    $title = $row['title'];
    $description = $row['description'];
    $image = $row['image'];

    // ✅ Generate meta keywords
    $descriptionWords = str_word_count(strip_tags($description), 1);
    $stopWords = ["a", "an", "the", "in", "on", "at", "and", "to", "for", "with", "by"];
    $filteredWords = array_diff($descriptionWords, $stopWords);
    $keywords = array_unique($filteredWords);
    $keywordsString = implode(', ', $keywords);

    // ✅ Meta tags
    echo '<title>Bite And Diet - ' . htmlspecialchars($title) . '</title>';
    echo '<meta name="twitter:title" content="Bite And Diet - ' . htmlspecialchars($title) . '">';
    echo '<meta property="og:title" content="Bite And Diet - ' . htmlspecialchars($title) . '">';
    echo '<meta name="keywords" content="' . htmlspecialchars($keywordsString) . '">';
    echo '<meta name="description" content="' . htmlspecialchars(strip_tags($description)) . '">';
    echo '<meta name="twitter:card" content="' . htmlspecialchars(strip_tags($description)) . '">';
    echo '<meta name="twitter:description" content="' . htmlspecialchars(strip_tags($description)) . '">';
    echo '<meta property="og:description" content="' . htmlspecialchars(strip_tags($description)) . '">';
    echo '<meta content="' . $_SERVER['REQUEST_URI'] . '" property="og:url">';

    // ✅ View count
    $viewCountFile = 'postimages/postcount/view_count_page_' . $id . '.txt';
    if (file_exists($viewCountFile)) {
        $currentCount = (int)file_get_contents($viewCountFile);
        $currentCount++;
        file_put_contents($viewCountFile, $currentCount);
    } else {
        $currentCount = 300;
        file_put_contents($viewCountFile, $currentCount);
    }
} else {
    header("Location: /allposts");
    exit;
}
?>

<?php $disable_header = false; include 'Header.php'; ?>
  
<div class="ttm-page-title-row">
  <div class="ttm-bg-layer ttm-page-title-row-bg-layer"></div>
  <div class="container">
    <div class="row">
      <div class="col-md-12 text-center">
        <div class="ttm-textcolor-white title-box">
          <div class="ttm-textcolor-white page-title-heading">
            <h2 class="title"><?php echo $title; ?></h2>
          </div>
          <div class="breadcrumb-wrapper">
            <span><a href="/" title="Homepage"><i class="ti ti-home"></i> Home </a></span>
            <span class="ttm-bread-sep">: : </span>
            <span><span class="ttm-textcolor-skincolor">Services</span></span>
            <span class="ttm-bread-sep">: : </span>
            <span><span class="ttm-textcolor-skincolor">All Posts</span></span>
            <span class="ttm-bread-sep">: : </span>
            <span><span class="ttm-textcolor-skincolor"><?php echo $title; ?></span></span>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<section class="error-404" style="padding: 60px 0">
  <div class="page-header"><h2 class="title"><?php echo $title; ?></h2></div>
  <div class="container pb-4">
    <div class="row">
      <div class="col-md-12">
        <div class="text-center row-title style4">
          <section>
            <div class="container">
              <div class="post-details">
                <div class="post-image">
                
                  
                  <img alt="<?php echo $title; ?>" src="<?php echo 'https://www.biteanddiet.in/' . ltrim($image, '/'); ?>" class="img-fluid blogimage">
                </div>
                <div class="post-content" style="text-align:start;padding:40px 0">
                  <p><?php echo $description; ?></p>
                </div>
              </div>
              <div class="post-content" style="text-align:start;padding:40px 0">
                <p>Views <i class="fa fa-eye"></i>: <span style="color:red;font-weight:700"><?php echo $currentCount; ?></span></p>
              </div>
              <a href="/allposts" class="mb-20 mt-30 ttm-btn ttm-btn-bgcolor-black ttm-btn-shape-round ttm-btn-size-md ttm-btn-style-fill">See all posts</a>
            </div>
          </section>
        </div>
      </div>
    </div>
  </div>
</section>

<?php include 'footer.php'; ?>

