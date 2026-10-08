<?php include 'Header.php'; ?> 

<div class="ttm-page-title-row">
  <div class="ttm-bg-layer ttm-page-title-row-bg-layer"></div>
  <div class="container">
    <div class="row">
      <div class="text-center col-md-12">
        <div class="ttm-textcolor-white title-box">
          <div class="ttm-textcolor-white page-title-heading">
            <h1 class="title">Videos</h1>
          </div>
          <div class="breadcrumb-wrapper">
            <span><a href="./" title="Homepage"><i class="ti ti-home"></i> Home </a></span>
            <span class="ttm-bread-sep">: : </span>
            <span><span class="ttm-textcolor-skincolor">Media</span></span>
            <span class="ttm-bread-sep">: : </span>
            <span><span class="ttm-textcolor-skincolor">Videos</span></span>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<section class="error-404">
  <header class="text-center section-title pb-4">
    <h5>Videos</h5>
  </header>
<div class="container mt-5">
    <!-- Search Section -->
    <form method="GET" action="" class="row justify-content-center my-4">
        <div class="col-lg-4 col-md-6 col-sm-12">
        <div class="input-group">
            <input type="text" name="search" class="form-control" placeholder="Search videos by title" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
            <button type="submit" class="btn btn-primary">Search</button>
        </div></div>
    </form>

    <div class="row" id="video-cards-container">
        <?php 
        // Paths to the cached and backup JSON files
        $cacheFile = __DIR__ . '/youtube_videos.json';
        $backupFile = __DIR__ . '/youtube_videos-backup.json';

        // Function to check if a file contains valid JSON
        function isValidJsonFile($file) {
            if (file_exists($file) && filesize($file) > 0) {
                $fileContents = file_get_contents($file);
                $jsonData = json_decode($fileContents, true);
                return $jsonData !== null ? $jsonData : false;
            }
            return false;
        }

        // Try to load data from the main cache file
        $jsonData = isValidJsonFile($cacheFile);

        // If the main cache file is invalid or empty, fallback to the backup file
        if (!$jsonData) {
            $jsonData = isValidJsonFile($backupFile);
            if (!$jsonData) {
                die("Error: Unable to load valid YouTube video data.");
            }
        }

        // Get the search query
        $searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';

        // Filter videos if a search query is entered
        if (!empty($searchQuery)) {
            $jsonData = array_filter($jsonData, function($video) use ($searchQuery) {
                return stripos($video['title'], $searchQuery) !== false;
            });
        }

        // Get the current page from the URL, default to 1 if not set
        $current_page = isset($_GET['page']) ? intval($_GET['page']) : 1;
        $postsPerPage = 18;
        $totalVideos = count($jsonData);
        $totalPages = ceil($totalVideos / $postsPerPage);

        // Calculate the offset for the current page
        $offset = ($current_page - 1) * $postsPerPage;

        // Slice the array to get only the videos for the current page
        $currentVideos = array_slice($jsonData, $offset, $postsPerPage);

        // Generate HTML for the current videos
        foreach ($currentVideos as $video) {
            if (isset($video['videoId']) && trim($video['videoId']) !== '') {
                echo "
                <div class='col-lg-4 col-md-6 col-sm-12 mb-4'>
                    <div class='card' style='margin: 0 0 40px 0;'>
                        <a href='/video_player?title=" . urlencode($video['title']) . "&videoId={$video['videoId']}' class='video-thumbnail'>
                            <img src='{$video['thumbnail']}' class='card-img-top' alt='{$video['title']}' style='object-fit: cover;' loading='lazy'>
                            <div class='overlay'></div>
                            <div class='play-button'></div>
                        </a>
                        <div class='card-body'>
                            <h5 class='card-title'>{$video['title']}</h5>
                        </div>
                    </div>
                </div>";
            }
        }
// Display a message if no videos are found
if (empty($currentVideos)) {
    echo '<div class="col-12 text-center"><p class="text-center">No videos found for \'' . htmlspecialchars($searchQuery) . '\'.</p></div>';
}


        ?>
    </div>

    <div class="text-center mt-4">
        <div class="pagination text-center d-block mt-4">
            <?php
            if ($totalPages > 1) {
                // Build the base URL
                $baseUrl = '?page=';

                // Append search query if it exists
                if (!empty($searchQuery)) {
                    $baseUrl .= '&search=' . urlencode($searchQuery);
                }

                // Display previous link
                if ($current_page > 1) {
                    echo '<a href="' . $baseUrl . ($current_page - 1) . '" class="btn ttm-btn-bgcolor-skincolor m-1"><i class="ti ti-arrow-left"></i></a>';
                }

                // Display page numbers
                for ($i = 1; $i <= $totalPages; $i++) {
                    $activeClass = ($i == $current_page) ? 'btn-primary' : 'ttm-btn-bgcolor-skincolor';
                    echo '<a href="' . $baseUrl . $i . '" class="btn ' . $activeClass . ' m-1">' . $i . '</a>';
                }

                // Display next link
                if ($current_page < $totalPages) {
                    echo '<a href="' . $baseUrl . ($current_page + 1) . '" class="btn ttm-btn-bgcolor-skincolor m-1"><i class="ti ti-arrow-right"></i></a>';
                }
            }
            ?>
        </div>
    </div>
</div>

</section>

<?php include 'footer.php'; ?>
