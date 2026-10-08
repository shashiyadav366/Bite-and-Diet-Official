<?php include 'Header.php'; ?>

<div class="ttm-page-title-row">
  <div class="ttm-bg-layer ttm-page-title-row-bg-layer"></div>
  <div class="container">
    <div class="row">
      <div class="text-center col-md-12">
        <div class="ttm-textcolor-white title-box">
          <div class="ttm-textcolor-white page-title-heading">
            <h1 class="title">Blog Posts</h1>
          </div>
          <div class="breadcrumb-wrapper">
            <span><a href="./" title="Homepage"><i class="ti ti-home"></i> Home </a></span>
            <span class="ttm-bread-sep">: : </span>
            <span><span class="ttm-textcolor-skincolor">Media</span></span>
            <span class="ttm-bread-sep">: : </span>
            <span><span class="ttm-textcolor-skincolor">Blog Posts</span></span>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<section class="error-404">
  <header class="text-center section-title pb-4">
    <h5>Blog Posts</h5>
  </header>
  <div class="container pb-4">

    <!-- Search Bar -->
    <div class="row justify-content-center my-4">
      <div class="col-md-6">
        <div class="input-group">
          <input type="text" id="searchInput" class="form-control" placeholder="Search blog posts..." 
                 value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
          <button id="searchButton" class="btn btn-primary" onclick="performSearch()">Search</button>
        </div>
      </div>
    </div>

    <div class="row" id="blogPosts">
      <?php
      // Load the cached blog posts data from the JSON file
      $blogData = json_decode(file_get_contents('allposts.json'), true);
      if (!is_array($blogData)) {
          $blogData = [];
      }

      // Get the search query if available
      $searchQuery = isset($_GET['search']) ? strtolower(trim($_GET['search'])) : '';

      // Filter posts based on the search query
      if ($searchQuery) {
          $blogData = array_filter($blogData, function ($post) use ($searchQuery) {
              return strpos(strtolower($post['title']), $searchQuery) !== false;
          });
      }

      $initialLimit = 18;

      // Default image used when a blog post has no image of its own
      $BLOG_FALLBACK_IMAGE = 'https://www.biteanddiet.in/images/social_posts_images/Dietician_Priyanka_5_Years_At_BiteAndDiet.jpg';

      // Render ALL posts; hide those beyond the initial limit (revealed by View More)
      if (empty($blogData)) {
          echo "<div class='col-12 text-center'><p class='text-center'>No posts found.</p></div>";
      } else {
          $index = 0;
          foreach ($blogData as $post) {
              $hidden = $index >= $initialLimit ? " style='display:none;'" : '';
              $postImg = $post['firstImgSrc'] ?: $BLOG_FALLBACK_IMAGE;
              echo "
              <div class='col-lg-4 col-md-6 col-sm-12 mb-4 blog-vm-item'$hidden>
                  <div class='card' style='margin: 0 0 40px 0;'>
                      <img src='{$postImg}' class='card-img-top' alt='{$post['title']}' style='object-fit: cover; height: 300px;' loading='lazy'>
                      <div class='card-body'>
                          <h4 class='card-title'>{$post['title']}</h4>
                          <a href='/blog-post/{$post['slug']}' class='btn btn-primary'>Know More</a>
                      </div>
                  </div>
              </div>";
              $index++;
          }
      }
      ?>
    </div>
  </div>

  <!-- View More (healthy-living style) -->
  <div class="text-center mt-4" id="view-more-button-container"<?php echo (count($blogData) <= 18) ? ' style="display:none;"' : ''; ?>>
    <button id="view-more-button" class="btn">View More</button>
  </div>
</section>
<!-- JavaScript -->
<script>
  // Perform search when search button is clicked or Enter key is pressed
  function performSearch() {
      const query = document.getElementById('searchInput').value.trim();
      const urlParams = new URLSearchParams(window.location.search);
      urlParams.set('search', query);
      urlParams.delete('page');
      window.location.search = urlParams.toString();
  }

  // Trigger search on Enter key press
  document.getElementById('searchInput').addEventListener('keypress', function (event) {
      if (event.key === 'Enter') {
          performSearch();
      }
  });

  // View More - client-side reveal (healthy-living style), no pagination
  (function () {
      var btn = document.getElementById('view-more-button');
      if (!btn) { return; }
      var btnContainer = document.getElementById('view-more-button-container');
      var items = document.querySelectorAll('#blogPosts .blog-vm-item');
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
