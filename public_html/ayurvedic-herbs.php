<?php include 'Header.php'; ?><div class="ttm-page-title-row"><div class="ttm-bg-layer ttm-page-title-row-bg-layer"></div><div class="container"><div class="row"><div class="col-md-12 text-center"><div class="ttm-textcolor-white title-box"><div class="ttm-textcolor-white page-title-heading"><h1 class="title">Ayurvedic Herbs</h1></div><div class="breadcrumb-wrapper"><span><a href="./"title="Homepage"><i class="ti ti-home"></i> Home </a></span><span class="ttm-bread-sep">: : </span><span><span class="ttm-textcolor-skincolor">Media</span></span> <span class="ttm-bread-sep">: : </span><span><span class="ttm-textcolor-skincolor">Ayurvedic Herbs</span></span></div></div></div></div></div></div><div class="site-main"><section class="clearfix blog-left-section ttm-row"><div class="container"><div class="row ttm-boxes-spacing-30px"><div class="col-md-12"><div class="clearfix section-title text-center"><div class="title-header">
<h5>Ayurvedic Herbs</h5>
<h2 class="title">Herbs And Their Usage</h2></div><div class="title-desc pb-30">If you are looking for a fast-paced, collaborative environment You'll enjoy an innovative & results-oriented culture driven by the facts.</div></div></div><?php require_once __DIR__ . '/json_db.php';
$herbs = jd_read('ayurvedic_herbs');
$herbsArray = array();
if (!empty($herbs)) {
    foreach ($herbs as $row) {
        $name = $row['name'];
        $medicalUsage = $row['medical_usage'];
        $description = $row['description'];
        $imageId = $row['id'];
        echo '<div class="col-md-6 col-sm-6 col-lg-4 col-12 ttm-box-col-wrapper">';echo '    <div class="row featured-imagebox ttm-box-view-left-image no-gutters break-991-colum box-shadow3">';echo '        <div class="col-md-12 col-lg-12 d-flex justify-content-center align-items-center">';

echo '<div class="featured-thumbnail1 mt-30">';
echo '<img class="img-fluid" src="images/plants/'.$imageId.'.jpg" alt="'.$name . ' - ' . $description . ' - Bite And Diet">';

echo '            </div>';echo '        </div>';echo '        <div class="col-md-12 col-lg-12">';echo '            <div class="ttm-bg ttm-bgcolor-white ttm-col-bgcolor-yes spacing-13">';echo '                <div class="ttm-col-wrapper-bg-layer ttm-bg-layer">';echo '                    <div class="ttm-bg-layer-inner"></div>';echo '                </div>';echo '                <div class="layer-content" style="min-height:152px">';echo '                    <div class="featured-title">';echo '                        <h5>'.$name.'</h5>';echo '                    </div>';echo '                    <div class="featured-desc text-center">';echo '                        <h6>'.$medicalUsage.'</h6>';echo '                        <p>'.$description.'</p>';echo '                    </div>';echo '                </div>';echo '            </div>';echo '        </div>';echo '    </div>';echo '</div>';$herbData=array("@type"=>"MedicalEntity","name"=>$name,"description"=>$description,"image"=>"images/plants/".$imageId.".jpg",);array_push($herbsArray,$herbData);}
} else {
    echo "No records found";
}
$schemaMarkup=array("@context"=>"http://schema.org","@type"=>"ItemList","name"=>"Ayurvedic Herbs List","itemListElement"=>$herbsArray);echo '<script type="application/ld+json">';echo json_encode($schemaMarkup,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);echo '</script>'; ?></div></div></section></div><?php include 'footer.php'; ?>