<?php include 'Header.php'; ?><div class="fade modal"role="dialog"aria-hidden="true"aria-labelledby="deleteModalLabel"id="deleteModal"tabindex="-1"><div class="modal-dialog"role="document"><div class="modal-content"><div class="modal-header"><h5 class="modal-title"id="deleteModalLabel">Delete Confirmation</h5><button class="close"data-dismiss="modal"type="button"aria-label="Close"><span aria-hidden="true">×</span></button></div><div class="modal-body"><p>Are you sure you want to delete this success story?</p></div><div class="modal-footer"><button class="btn btn-secondary"data-dismiss="modal"type="button">Cancel</button> <a href=""class="btn btn-danger"id="deleteLink">Delete</a></div></div></div></div><div class="ttm-page-title-row"><div class="ttm-bg-layer ttm-page-title-row-bg-layer"></div><div class="container"><div class="row"><div class="text-center col-md-12"><div class="ttm-textcolor-white title-box"><div class="ttm-textcolor-white page-title-heading"><h1 class="title">Success Stories</h1></div><div class="breadcrumb-wrapper"><span><a href="/"title="Homepage"><i class="ti ti-home"></i> Home </a></span><span class="ttm-bread-sep">: : </span><span><span class="ttm-textcolor-skincolor">Success Stories</span></span></div></div></div></div></div></div><div class="clearfix customer-success-story-section ttm-row"><div class="container"><div class="row"><div class="col-md-12"><div class="text-center clearfix section-title"><div class="title-header"><h5>Motivational</h5><h2 class="title">All Success Stories</h2></div><div class="title-desc"><p>Stories for extra motivation and inspiration.</p></div></div></div></div>


<?php require_once __DIR__ . '/json_db.php';
$storyRows = jd_read('success_stories');
usort($storyRows, function ($a, $b) { return strcmp((string)($b['created_at'] ?? ''), (string)($a['created_at'] ?? '')); });
if (!empty($storyRows)) {
    foreach ($storyRows as $row) {
        $customerID = $row['id'];
        $customerName = $row['name'];
        $customerImage = $row['image'];
        $customerDietplanType = $row['dietplan_type'];
        $customerAchievement = $row['achievement'];
        $customerPdf = $row['pdf'];
        if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) { echo '
                    <div class="row ttm-sucessstories-box box-shadow2">
                        <div class="col-md-3">
                            <!-- ttm_single_image-wrapper -->
                            <div class="ttm_single_image-wrapper">
                                <div class="text-left">
<img class="img-fluid" loading="lazy" src="'.$customerImage.'" alt="'.$customerName.' - '.$customerDietplanType.' - Bite And Diet">
  
      </div>
                            </div><!-- ttm_single_image-wrapper end -->
                        </div>
                        <div class="col-md-9">
                            <!-- section title -->
                            <div class="section-title mb-10 clearfix">
                                <div class="title-header">
                                    <h6>'.$customerDietplanType.'</h6>
                                    <h3 class="title">'.$customerName.'</h3>
                                </div>
                                <div class="title-icons">
                                    <a href="#" data-toggle="modal" data-target="#deleteModal" class="delete-icon px-4" data-customer-id="'.$customerID.'"><i class="fa fa-trash"></i></a>
                                    <a href="/admin/edit_success_story.php?id='.$customerID.'" class="edit-icon"><i class="fa fa-pencil"></i></a>
                                </div>
                            </div><!-- section title end -->
                            <div class="separator">
                                <div class="sep-line mt_5 mb-20"></div>
                            </div>
                            <p>'.$customerAchievement.'</p>
                            <a class="ttm-btn ttm-btn-size-sm ttm-btn-color-skincolor btn-inline ttm-btn-underline" href="'.$customerPdf.'" target="_biteandiet">Read More</a>
                        </div>
                    </div><!-- row end -->
                    ';}else{echo '
                    <div class="row ttm-sucessstories-box box-shadow2">
                        <div class="col-md-3">
                            <!-- ttm_single_image-wrapper -->
                            <div class="ttm_single_image-wrapper">
                                <div class="text-left">
<img class="img-fluid" src="'.$customerImage.'" alt="'.$customerName.' - '.$customerDietplanType.' - Bite And Diet">

                        
                                    
                                </div>
                            </div><!-- ttm_single_image-wrapper end -->
                        </div>
                        <div class="col-md-9">
                            <!-- section title -->
                            <div class="section-title mb-10 clearfix">
                                <div class="title-header">
                                    <h6>'.$customerDietplanType.'</h6>
                                    <h3 class="title">'.$customerName.'</h3>
                                </div>
                            </div><!-- section title end -->
                            <div class="separator">
                                <div class="sep-line mt_5 mb-20"></div>
                            </div>
                            <p>'.$customerAchievement.'</p>
                            <a class="ttm-btn ttm-btn-size-sm ttm-btn-color-skincolor btn-inline ttm-btn-underline" href="'.$customerPdf.'" target="_biteandiet">Read More</a>
                        </div>
                    </div><!-- row end -->
                    ';}}} ?>
                    
                    
                    
                    
                    
                    
                    
                    </div>
                      <div class="container text-center">
                    <div class="res-991-mt-30 mt-50"><a href="/admin/add_success_story" class="ttm-btn mb-20 ttm-btn-shape-round ttm-btn-size-md ttm-btn-style-fill ttm-btn-bgcolor-black">Add Story</a> 
                    <!--<a href="form" class="ttm-btn mb-20 ttm-btn-shape-round ttm-btn-size-md ttm-btn-style-fill ml-15 ttm-btn-bgcolor-skincolor">Contact Us</a>-->
                    <a href="/customer-reviews" class="ttm-btn mb-20 ttm-btn-shape-round ttm-btn-size-md ttm-btn-style-fill ml-15 ttm-btn-bgcolor-skincolor">Reviews</a>
                    </div>
                    </div>
                    
                    </div><script>$(document).ready(function(){$("#deleteModal").on("show.bs.modal",function(e){var t=$(e.relatedTarget).data("customer-id");document.getElementById("deleteLink").href="/admin/delete_success_story.php?id="+t})})</script><?php include 'footer.php'; ?>