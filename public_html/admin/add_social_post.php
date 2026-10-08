<?php
session_start();

// Save the current URL in the session
$_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];

// Check if the user is not logged in, redirect to login.php
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../login.php");
    exit;
}
?>

<?php include '../Header.php'; ?>

<div class="ttm-page-title-row">
    <div class="ttm-bg-layer ttm-page-title-row-bg-layer"></div>
    <div class="container">
        <div class="row">
            <div class="text-center col-md-12">
                <div class="ttm-textcolor-white title-box">
                    <div class="ttm-textcolor-white page-title-heading">
                        <h2 class="title">Post Data</h2>
                    </div>
                    <div class="breadcrumb-wrapper">
                        <span><a href="/" title="Homepage"><i class="ti ti-home"></i> Home </a></span>
                        <span class="ttm-bread-sep">: : </span>
                        <span><span class="ttm-textcolor-skincolor">Data</span></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<section class="break-991-colum checkout-section clearfix ttm-row">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="container">
<?php
$errorMessage = '';
$successMessage = '';
$uploadDir = '../images/social_posts_images/';

// Function to generate slug from title
function generateSlug($string, $existingData = []) {
    $slug = strtolower(trim($string));
    $slug = preg_replace('/[^a-z0-9]+/i', '-', $slug);
    $slug = trim($slug, '-');

    // Ensure slug is unique
    $baseSlug = $slug;
    $count = 1;
    $slugs = array_column($existingData, 'slug');
    while (in_array($slug, $slugs)) {
        $slug = $baseSlug . '-' . $count;
        $count++;
    }
    return $slug;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_POST['post_type']; // dropdown value
    $title = trim($_POST['title']);
    $caption = $_POST['caption'];
    $permalink = $_POST['permalink'] ?? '';
    $timestamp = date('Y-m-d\TH:i:s+00:00');

    // Select JSON file based on post type
    if ($type === 'health') {
        $jsonFilePath = '../health-tips.json';
    } else {
        $jsonFilePath = '../social-posts.json';
    }

    // Read existing JSON data
    $existingData = [];
    if (file_exists($jsonFilePath)) {
        $existingData = json_decode(file_get_contents($jsonFilePath), true);
    }

    // Generate slug from title (and ensure uniqueness)
    $slug = generateSlug($title, $existingData);

    // Check if the file is uploaded
    if (isset($_FILES['image']['name']) && !empty($_FILES['image']['name'])) {
        $fileName = basename($_FILES['image']['name']);
        $targetFilePath = $uploadDir . $fileName;

        // Ensure the image file is not duplicated
        foreach ($existingData as $post) {
            if ($post['media_url'] === "/images/social_posts_images/{$fileName}") {
                $errorMessage = "An image with the same name already exists.";
                break;
            }
        }

        if (empty($errorMessage)) {
            // Validate file type (only allow images)
            $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));
            $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            if (!in_array($fileType, $allowedTypes)) {
                $errorMessage = "Only JPG, JPEG, PNG, GIF, and WEBP files are allowed.";
            } else {
                // Move the uploaded file to the target directory
                if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFilePath)) {
                    // Generate unique ID
                    do {
                        $newId = '180428284130652' . mt_rand(1, 99999);
                        $idExists = false;
                        foreach ($existingData as $post) {
                            if ($post['id'] === $newId) {
                                $idExists = true;
                                break;
                            }
                        }
                    } while ($idExists);

                    // Create new post data
                    $newPost = [
                        'id' => $newId,
                        'title' => $title,
                        'slug' => $slug,
                        'caption' => $caption,
                        'media_type' => 'IMAGE',
                        'media_url' => "/images/social_posts_images/{$fileName}",
                        'permalink' => $permalink,
                        'timestamp' => $timestamp
                    ];

                    // Add the new post at the beginning of the array
                    array_unshift($existingData, $newPost);

                    // Save the updated data back to the correct JSON file
                    if (file_put_contents($jsonFilePath, json_encode($existingData, JSON_PRETTY_PRINT))) {
                        require_once __DIR__ . '/sitemap_lib.php';
                        sitemap_generate_if_stale();
                        $successMessage = ucfirst($type) . " post added successfully!";
                    } else {
                        $errorMessage = "Failed to update JSON file.";
                    }
                } else {
                    $errorMessage = "Failed to upload the image.";
                }
            }
        }
    } else {
        $errorMessage = "Please upload an image.";
    }
}
?>

                    <!-- Form for submitting Instagram/Health Tip post data -->
                    <form action="" method="post" enctype="multipart/form-data">
                        <?php if (!empty($errorMessage)): ?>
                            <div class="alert alert-danger"><?php echo $errorMessage; ?></div>
                        <?php endif; ?>
                        <?php if (!empty($successMessage)): ?>
                            <div class="alert alert-success"><?php echo $successMessage; ?></div>
                        <?php endif; ?>

                        <div class="form-group">
                            <label for="post_type">Post Type:</label>
                            <select class="form-control" name="post_type" id="post_type" required>
                                <option value="social">Social Post</option>
                                <option value="health">Health Tip</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="title">Title:</label>
                            <input type="text" class="form-control" name="title" id="title" placeholder="Enter post title" required>
                        </div>

                        <div class="form-group">
                            <label for="caption">Caption:</label>
                            <textarea class="form-control" name="caption" id="caption" rows="4" required></textarea>
                        </div>

                        <div class="form-group">
                            <label for="image">Upload Image:</label>
                            <input type="file" class="form-control" name="image" id="image" accept="image/*" required>
                        </div>

                       <!--- 
                       <div class="form-group">
                            <label for="permalink">Permalink (Optional):</label>
                            <input type="url" class="form-control" name="permalink" id="permalink" placeholder="Enter Instagram permalink">
                        </div>
                        
                        --->

                        <div class="text-center p-4">
                            <button class="mb-20 ttm-btn ttm-btn-bgcolor-black ttm-btn-shape-round ttm-btn-size-md ttm-btn-style-fill" type="submit">Add Post</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include '../footer.php'; ?>
