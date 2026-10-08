<?php 
// Set the HTTP response code to 404
http_response_code(404); 

// Prevent search engines from indexing the 404 page
$noindex = true;

// Include header file
include 'Header.php'; 
?>

<!--error-404 start-->
<section class="error-404 bg-img2">

    <div class="ttm-big-icon ttm-textcolor-skincolor">
        <i class="fa fa-thumbs-down"></i>
    </div>

    <header class="page-header">
        <h1 class="page-title">ERROR 404</h1>
    </header>

    <div class="page-content">
        <p>PAGE DOESN'T EXIST</p>
    </div>

    <a class="ttm-btn ttm-btn-size-md ttm-btn-shape-round ttm-btn-style-border ttm-btn-color-black mb-15" href="/">Back To Home</a>

</section>
<!--error-404 end-->

<?php 
// Include footer file
include 'footer.php'; 
?>
