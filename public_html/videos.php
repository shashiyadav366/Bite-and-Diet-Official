<?php include 'Header.php';?> 

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
        </div>
      </div>
    </form>

    <div class="row" id="video-cards-container">
      <?php 
        // Load video data from cache or backup
        $cacheFile = __DIR__ . '/youtube_videos.json';
        $backupFile = __DIR__ . '/youtube_videos-backup.json';

        function isValidJsonFile($file) {
            if (file_exists($file) && filesize($file) > 0) {
                $data = json_decode(file_get_contents($file), true);
                return $data !== null ? $data : false;
            }
            return false;
        }

        $jsonData = isValidJsonFile($cacheFile) ?: isValidJsonFile($backupFile);
      //  if (!$jsonData) die("Error: Unable to load valid YouTube video data.");
        
        if (!$jsonData) {
    echo "<div class='container my-5 text-center'><p>Error: Unable to load YouTube videos.</p></div>";
    $jsonData = []; // Continue with empty array so page renders and footer shows
}

        // Search
        $searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';
        if (!empty($searchQuery)) {
            $jsonData = array_filter($jsonData, function($video) use ($searchQuery) {
                return stripos($video['title'], $searchQuery) !== false;
            });
        }

        $initialLimit = 18;
        $videoCount = 0;

        // Render ALL videos; hide those beyond the initial limit (revealed by View More)
        foreach ($jsonData as $video) {
            if (isset($video['videoId']) && trim($video['videoId']) !== '') {
              $slug = ($video['slug']);
                $hidden = $videoCount >= $initialLimit ? " style='display:none;'" : '';
                echo "
                <div class='col-lg-4 col-md-6 col-sm-12 mb-4 video-vm-item'$hidden>
                    <div class='card' style='margin: 0 0 40px 0;'>
                        <a href='/video/{$slug}' class='video-thumbnail'>
                            <img src='{$video['thumbnail']}' class='card-img-top' alt='{$video['title']}' style='object-fit: cover;' loading='lazy'>
                            <div class='overlay'></div>
                            <div class='play-button'></div>
                        </a>
                        <div class='card-body'>
                            <h5 class='card-title'>{$video['title']}</h5>
                        </div>
                    </div>
                </div>";
                $videoCount++;
            }
        }

        if ($videoCount === 0) {
            echo '<div class="col-12 text-center"><p class="text-center">No videos found for \'' . htmlspecialchars($searchQuery) . '\'.</p></div>';
        }
      ?>
    </div>

    <!-- View More (healthy-living style) -->
    <div class="text-center mt-4" id="view-more-button-container"<?php echo ($videoCount <= 18) ? ' style="display:none;"' : ''; ?>>
      <button id="view-more-button" class="btn">View More</button>
    </div>
  </div>
</section>

<script>
  // Search on Enter key press
  var searchInput = document.querySelector('input[name="search"]');
  if (searchInput) {
      searchInput.addEventListener('keypress', function (e) {
          if (e.key === 'Enter') { searchInput.form.submit(); }
      });
  }

// View More - client-side reveal (healthy-living style), no pagination
  (function () {
      var btn = document.getElementById('view-more-button');
      if (!btn) { return; }
      var btnContainer = document.getElementById('view-more-button-container');
      var items = document.querySelectorAll('#video-cards-container .video-vm-item');
      var step = 18;
      var shown = 0;
      items.forEach(function (item) {
          if (item.style.display !== 'none') { shown++; }
      });

      btn.addEventListener('click', function () {
          var revealed = 0;
          items.forEach(function (item) {
              if (revealed < step && item.style.display === 'none') {
                  item.style.display = '';
                  revealed++;
                  shown++;
              }
          });
          if (shown >= items.length && btnContainer) { btnContainer.style.display = 'none'; }
      });
  })();
</script>

<?php include 'footer.php'; ?>

