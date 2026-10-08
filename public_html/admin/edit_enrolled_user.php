<?php include '../Header.php'; ?><div class="ttm-page-title-row"><div class="ttm-bg-layer ttm-page-title-row-bg-layer"></div><div class="container"><div class="row"><div class="col-md-12 text-center"><div class="ttm-textcolor-white title-box"><div class="ttm-textcolor-white page-title-heading"><h1 class="title">Edit Enrolled User Details</h1></div><div class="breadcrumb-wrapper"><span><a href="/"title="Homepage"><i class="ti ti-home"></i> Home </a></span><span class="ttm-bread-sep">: : </span><span><span class="ttm-textcolor-skincolor">Edit Enrolled User Details</span></span></div></div></div></div></div></div><section class="error-404"><header class="text-center section-title"><h5>Edit Enrolled Details</h5></header><div class="container pb-4"><div class="row"><div class="col-md-12"><div class="row-title style4"><section><div class="container"style="text-align:start"><?php if (session_status() === PHP_SESSION_NONE) { session_start(); }$_SESSION['redirect_url']=$_SERVER['REQUEST_URI'];if(!isset($_SESSION['loggedin'])||$_SESSION['loggedin']!==true){header("Location:../login.php");exit;}require_once __DIR__ . '/../json_db.php';if($_SERVER["REQUEST_METHOD"]=="POST"){$id=$_POST['id'];$name=$_POST['name'];$date_of_birth=$_POST['date_of_birth'];$contact_number=$_POST['contact_number'];$address=$_POST['address'];$occupation=$_POST['occupation'];$source_of_reference=$_POST['source_of_reference'];$email_id=$_POST['email_id'];$take_medicines=$_POST['take_medicines'];$medical_history=$_POST['medical_history'];$diet_habits=$_POST['diet_habits'];$food_allergy_or_intolerance=$_POST['food_allergy_or_intolerance'];$food_likes_and_dislikes=$_POST['food_likes_and_dislikes'];$unhealthy_symptoms=$_POST['unhealthy_symptoms'];$date_of_joining=$_POST['date_of_joining'];$my_notes=$_POST['my_notes'];$birthDate=new DateTime($date_of_birth);$today=new DateTime();$ageInterval=$birthDate->diff($today);$age=$ageInterval->y;$users=jd_read('enrolled_users');$updated=false;foreach($users as $i=>$u){if(isset($u['id'])&&(int)$u['id']===(int)$id){$users[$i]['name']=$name;$users[$i]['contact_number']=$contact_number;$users[$i]['date_of_birth']=$date_of_birth;$users[$i]['age']=$age;$users[$i]['address']=$address;$users[$i]['occupation']=$occupation;$users[$i]['source_of_reference']=$source_of_reference;$users[$i]['email_id']=$email_id;$users[$i]['medical_history']=$medical_history;$users[$i]['diet_habits']=$diet_habits;$users[$i]['food_allergy_or_intolerance']=$food_allergy_or_intolerance;$users[$i]['food_likes_and_dislikes']=$food_likes_and_dislikes;$users[$i]['take_medicines?']=$take_medicines;$users[$i]['unhealthy_symptoms']=$unhealthy_symptoms;$users[$i]['date_of_joining']=$date_of_joining;$users[$i]['my_notes']=$my_notes;$updated=true;break;}}if($updated&&jd_write('enrolled_users',$users)){echo '<div class="alert alert-success" role="alert">';echo 'Update successful. Redirecting to Enrolled Contact Details Page . . .';echo '</div>';echo '<script>';echo 'setTimeout(function() { window.location.href = "enrolled_contact_details.php"; }, 3000);';echo '</script>';}else{echo '<div class="alert alert-danger" role="alert">';echo 'Error updating record: '.($updated?'Error writing file.':'Invalid record id.');echo '</div>';}}else{if(isset($_GET['id'])){$id=$_GET['id'];$row=array();foreach(jd_read('enrolled_users') as $u){if(isset($u['id'])&&(int)$u['id']===(int)$id){$row=$u;break;}}echo '<form method="POST" action="">';echo '<input type="hidden" name="id" value="'.$row['id'].'">';echo '<div class="form-group">
                                    <label for="name">Name:</label>
                                    <input type="text" name="name" required class="form-control" required value="'.$row['name'].'">
                                </div>';echo '<div class="form-group">
                                    <label for="date_of_birth">Date of Birth:</label>
                                    <input type="date" required name="date_of_birth" class="form-control" value="'.$row['date_of_birth'].'">
                                </div>';echo '<div class="form-group">
                                    <label for="contact_number">Contact Number:</label>
                                    <input type="number" name="contact_number" required class="form-control" value="'.$row['contact_number'].'">
                                </div>';echo '<div class="form-group">
                                    <label for="address">Address:</label>
                                    <textarea name="address" required class="form-control">'.$row['address'].'</textarea>
                                </div>';echo '<div class="form-group">
                                    <label for="occupation">Occupation:</label>
                                    <input type="text" required name="occupation" class="form-control" value="'.$row['occupation'].'">
                                </div>';echo '<div class="form-group">
                                    <label for="source_of_reference">Source of Reference:</label>
                                    <input type="text" required name="source_of_reference" class="form-control" value="'.$row['source_of_reference'].'">
                                </div>';echo '<div class="form-group">
                                    <label for="email_id">Email:</label>
                                    <input type="email" required name="email_id" class="form-control" value="'.$row['email_id'].'">
                                </div>';echo '<div class="form-group">
                                    <label for="medical_history">Medical History:</label>
                                    <textarea name="medical_history" class="form-control">'.$row['medical_history'].'</textarea>
                                </div>';echo '<div class="form-group">
                                    <label for="diet_habits">Diet Habits:</label>
                                    <input type="text" required name="diet_habits" class="form-control" value="'.$row['diet_habits'].'">
                                </div>';echo '<div class="form-group">
                                    <label for="food_allergy_or_intolerance">Food Allergy or Intolerance:</label>
                                    <textarea required name="food_allergy_or_intolerance" class="form-control">'.$row['food_allergy_or_intolerance'].'</textarea>
                                </div>';echo '<div class="form-group">
                                    <label for="food_likes_and_dislikes">Food Likes and Dislikes:</label>
                                    <textarea required name="food_likes_and_dislikes" class="form-control">'.$row['food_likes_and_dislikes'].'</textarea>
                                </div>';echo '<div class="form-group">
    <label for="take_medicines">Taking Medicines?</label>
    <textarea required name="take_medicines" class="form-control">'.$row['take_medicines?'].'</textarea>
</div>';echo '<div class="form-group">
                                    <label for="unhealthy_symptoms">Unhealthy Symptoms:</label>
                                    <textarea required  name="unhealthy_symptoms" class="form-control">'.$row['unhealthy_symptoms'].'</textarea>
                                </div>';echo '<div class="form-group">
                                    <label for="date_of_joining">Date of Joining:</label>
                                    <input type="date" required name="date_of_joining" class="form-control" value="'.$row['date_of_joining'].'">
                                </div>';echo '<div class="form-group">
                                    <label for="my_notes">My Notes:</label>
                                    <textarea name="my_notes" required class="form-control">'.$row['my_notes'].'</textarea>
                                </div>';echo '<div class="text-center">
                                    <input class="ttm-btn ttm-btn-size-md ttm-btn-shape-round ttm-btn-style-fill ttm-btn-bgcolor-black mb-20 mt-30" type="submit" value="Update">
                                </div>';echo '</form>';}} ?></div></section></div></div></div></div></section><?php include '../footer.php'; ?>