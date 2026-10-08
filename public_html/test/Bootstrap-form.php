<?php session_start();$_SESSION['redirect_url']=$_SERVER['REQUEST_URI'];if(!isset($_SESSION['loggedin'])||$_SESSION['loggedin']!==true){header("Location: login.php");exit;}?>


<?php include 'Header.php'; ?>
<div class="ttm-page-title-row"><div class="ttm-bg-layer ttm-page-title-row-bg-layer"></div><div class="container"><div class="row"><div class="col-md-12 text-center"><div class="ttm-textcolor-white title-box"><div class="ttm-textcolor-white page-title-heading"><h2 class="title">Sitemap Generator</h2></div><div class="breadcrumb-wrapper"><span><a href="./"title="Homepage"><i class="ti ti-home"></i> Home </a></span><span class="ttm-bread-sep">: : </span><span><span class="ttm-textcolor-skincolor">Sitemap Generator </span></span></div></div></div></div></div></div>




        <!--error-404 start-->
        <section>
           
     
<div class="container mt-4">
        
        <form action="sitemap-generate" method="post">            <div class="form-group px-3">
            <label for="allposts_last_page" class="form-label">Last Page Number for All Posts</label>
                <input type="number" class="form-control" id="allposts_last_page" name="allposts_last_page" placeholder="Enter last page number" required>
            </div>
                        <div class="form-group px-3">
          
                <label for="allvideos_last_page" class="form-label">Last Page Number for All Videos</label>
                <input type="number" class="form-control" id="allvideos_last_page" name="allvideos_last_page" placeholder="Enter last page number" required>
            </div>
            <div class="text-center py-5">
              <button class="mb-20 ttm-btn ttm-btn-bgcolor-black ttm-btn-shape-round ttm-btn-size-md ttm-btn-style-fill" type="submit">Generate Sitemap</button></div>
        </form>
    </div>


       

        </section>
        <!--error-404 end-->

</div></section

>



<?php include 'footer.php'; ?>