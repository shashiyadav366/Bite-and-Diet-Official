<?php include '../Header.php'; ?><?php require_once '../credentials.php';$conn=new mysqli($dbHost,$dbUsername,$dbPassword,$dbName);if($conn->connect_error){die("Connection failed: ".$conn->connect_error);}$sql="SELECT id, title, slug, description, image FROM blog_posts";$result=$conn->query($sql); ?><div class="ttm-page-title-row"><div class="ttm-bg-layer ttm-page-title-row-bg-layer"></div><div class="container"><div class="row"><div class="text-center col-md-12"><div class="ttm-textcolor-white title-box"><div class="ttm-textcolor-white page-title-heading"><h1 class="title">Blog Posts</h1></div><div class="breadcrumb-wrapper"><span><a href="/"title="Homepage"><i class="ti ti-home"></i> Home </a></span><span class="ttm-bread-sep">: : </span><span><span class="ttm-textcolor-skincolor">Services</span></span> <span class="ttm-bread-sep">: : </span><span><span class="ttm-textcolor-skincolor">Blog Posts</span></span></div></div></div></div></div></div><div class="fade modal"role="dialog"aria-hidden="true"aria-labelledby="deleteModalLabel"id="deleteModal"tabindex="-1"><div class="modal-dialog"role="document"><div class="modal-content"><div class="modal-header"><h5 class="modal-title"id="deleteModalLabel">Delete Confirmation</h5><button class="close"data-dismiss="modal"type="button"aria-label="Close"><span aria-hidden="true">×</span></button></div><div class="modal-body"><p>Are you sure you want to delete this post?</p></div><div class="modal-footer"><button class="btn btn-secondary"data-dismiss="modal"type="button">Cancel</button> <a href="#"class="btn btn-primary"id="deletePostLink">Delete</a></div></div></div></div><section class="error-404"><header class="text-center section-title"><h5>Blog Posts</h5></header><div class="container pb-4"><div class="row"><?php while($row=$result->fetch_assoc()){ ?><div class="col-md-4"><div class="card my-4">
    
    <img alt="<?php echo $row["title"]; ?>"class="card-img-top"
    src="<?php echo '../' . htmlspecialchars($row["image"]); ?>" 
    
    style="object-fit:cover;height:300px">


<div class="card-body"><h5 class="align-items-center card-title d-flex justify-content-center"style="max-height:63px"><?php echo($row["title"]); ?></h5><p class="card-text"><?php echo substr($row["description"],0,80); ?></p>



<div class="mt-2">
    <?php if ($loggedIn) { ?>
        <a href="#" class="px-3" data-target="#deleteModal" data-toggle="modal" onclick="confirmDelete(<?php echo $row['id']; ?>)">
            <i class="fa fa-trash"></i>
        </a>
        <a href="editpost?id=<?php echo $row['id']; ?>" class="px-3">
            <i class="fa fa-edit"></i>
        </a>
    <?php } ?>

    <a href="/blogpost/<?php echo urlencode($row['slug']); ?>" class="px-3">
        <i class="fa fa-eye"></i>
    </a>
</div>
</div></div></div><?php } ?></div><div class="container text-center"><a href="addpost.php"class="mb-20 mt-30 ttm-btn ttm-btn-bgcolor-black ttm-btn-shape-round ttm-btn-size-md ttm-btn-style-fill">Add Blog Post</a></div></div></section><script>function confirmDelete(e){document.getElementById("deletePostLink").href="./deletepost.php?id="+e,$("#deleteModal").modal("show")}</script><?php include '../footer.php'; ?><?php $conn->close(); ?>