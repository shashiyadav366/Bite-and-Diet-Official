<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);


// Start the session
session_start();

$_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
    
// Check if the user is not logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    // Redirect to the login page or any other page you want
    header("Location: ../login.php");
    exit;
}
?>



<?php

// JSON data layer
require_once __DIR__ . '/../json_db.php';

?>




<html>
	<head>
		<title>Bite And Diet - Invoice Generator</title>
		
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
		
		<script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
		
		<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
		
		<link rel='stylesheet' href='https://code.jquery.com/ui/1.13.0/themes/base/jquery-ui.css'>
		<script src="https://code.jquery.com/ui/1.13.0-rc.3/jquery-ui.min.js" integrity="sha256-R6eRO29lbCyPGfninb/kjIXeRjMOqY3VWPVk6gMhREk=" crossorigin="anonymous"></script>
		<meta name="viewport" content="width=device-width, initial-scale=1" />
		
		<style>
		   .form-control{
		       min-width:100px;
		   }
		    
		</style>
	</head>
	<body>


   
		<div class="p-3">
<h5 class="text-center p-4"> Enter The Details</h5><hr>
			<?php
			

			
				if(isset($_POST["submit"])){
					$invoice_no=$_POST["invoice_no"];
					$invoice_date=date("Y-m-d",strtotime($_POST["invoice_date"]));
					$cname=$_POST["cname"];
					$caddress=$_POST["caddress"];
					$ccity=$_POST["ccity"];
					$grand_total=$_POST["grand_total"];
					
					$invoices=jd_read('invoices');
					$sid=jd_next_id($invoices,'sid');
					$products=[];
					for($i=0;$i<count($_POST["pname"]);$i++)
					{
						$products[]=array(
							'id'=>$i,
							'name'=>$_POST["pname"][$i],
							'price'=>$_POST["price"][$i],
							'qty'=>$_POST["qty"][$i],
							'total'=>$_POST["total"][$i]
						);
					}
					$invoices[]=array(
						'sid'=>$sid,
						'invoice_no'=>$invoice_no,
						'invoice_date'=>$invoice_date,
						'cname'=>$cname,
						'caddress'=>$caddress,
						'ccity'=>$ccity,
						'grand_total'=>$grand_total,
						'products'=>$products
					);
					if(jd_write('invoices',$invoices)){
						echo "<div class='alert alert-success'>Invoice Added. <a href='print.php?id={$sid}' target='_BLANK'>Click</a> here to Print Invoice</div>";
					}else{
						echo "<div class='alert alert-danger'>Invoice Added Failed.</div>";
					}
				}
				
			?>
			<div class="container">
			<form method='post' action='invoice-generator' autocomplete='off'>
				<div class='row'>
					<div class='col-md-4'>
						<h5 style="color:#18c471">Invoice Details</h5>
						<div class='form-group'>
							<label>Invoice No</label>
							<input type='number' name='invoice_no' required class='form-control'>
						</div>
						<div class='form-group'>
							<label>Invoice Date</label>
							<input type='text' name='invoice_date' id='date' required class='form-control'>
						</div>
					</div>
					<div class='col-md-8'>
						<h5 style="color:#18c471">Customer Details</h5>
						<div class='form-group'>
							<label>Name</label>
							<input type='text' name='cname' required class='form-control'>
						</div>
						<div class='form-group'>
							<label>Address</label>
							<input type='text' name='caddress' required class='form-control'>
						</div>
						<div class='form-group'>
							<label>City</label>
							<input type='text' name='ccity' required class='form-control'>
						</div>
					</div>
				</div>
				<div class='row'>
					<div class='col-md-12'>
						<h5 style="color:#18c471">Product Details</h5>
						<div class="table-responsive">
						<table class='table table-bordered'>
							<thead style="background:#18c471;color:#fff">
								<tr>
									<th>Product</th>
									<th>Price</th>
									<th>Qty</th>
									<th>Total</th>
									<th>Action</th>
								</tr>
							</thead>
							<tbody id='product_tbody'>
								<tr>
									<td><input type='text' required name='pname[]' class='form-control'></td>
									<td><input type='text' required name='price[]' class='form-control price'></td>
									<td><input type='text' required name='qty[]' class='form-control qty'></td>
									<td><input type='text' required name='total[]' class='form-control total'></td>
									<td><input type='button' value='x' class='btn btn-danger btn-sm btn-row-remove'> </td>
								</tr>
							</tbody>
							<tfoot>
								<tr>
									<td><input type='button' value='+ Add Row' class='btn btn-primary btn-sm' id='btn-add-row'style="background:#18c471"></td>
									<td colspan='2' class='text-right'>Total</td>
									<td><input type='text' name='grand_total' id='grand_total' class='form-control' required></td>
								</tr>
							</tfoot>
						</table></div>
						<div class="text-center my-5">

					<input type="submit" name="submit" value="Save Invoice" class="btn float-right" style="color: #fff;background:#18c471">

						
						    </div>

						  <!---  <button id="generateLinkBtn" class="btn btn-primary">Generate Link and QR</button>
						    
						    <div id="qrCodeContainer" class="text-center mt-4" style="display: none;">
    <h5>QR Code for Payment</h5>
    <img id="qrCodeImg" src="" alt="QR Code" style="border: 1px solid #ccc;">
    <button id="copyLinkBtn" class="btn btn-primary mt-3">Copy Payment Link</button>
</div>--->



					</div>
				</div>
			</form>
		</div>
		</div>
		
<script>
    $(document).ready(function(){
        $("#date").datepicker({
            dateFormat: "dd-mm-yy"
        });

        $("#btn-add-row").click(function(){
            var row = "<tr> <td><input type='text' required name='pname[]' class='form-control'></td> <td><input type='text' required name='price[]' class='form-control price'></td> <td><input type='text' required name='qty[]' class='form-control qty'></td> <td><input type='text' required name='total[]' class='form-control total'></td> <td><input type='button' value='x' class='btn btn-danger btn-sm btn-row-remove'> </td> </tr>";
            $("#product_tbody").append(row);
        });

        $("body").on("click", ".btn-row-remove", function(){
            if(confirm("Are You Sure?")){
                $(this).closest("tr").remove();
                grand_total();
            }
        });

        $("body").on("keyup", ".price", function(){
            var price = Number($(this).val());
            var qty = Number($(this).closest("tr").find(".qty").val());
            $(this).closest("tr").find(".total").val(price * qty);
            grand_total();
        });

        $("body").on("keyup", ".qty", function(){
            var qty = Number($(this).val());
            var price = Number($(this).closest("tr").find(".price").val());
            $(this).closest("tr").find(".total").val(price * qty);
            grand_total();
        });

        function grand_total(){
            var tot = 0;
            $(".total").each(function(){
                tot += Number($(this).val());
            });
            $("#grand_total").val(tot);
        }

        // Hide the copy link button by default
        $("#copy_link_btn").hide();

        $("#save_invoice").click(function(){
            // Submit the form normally
            $("#invoice_form").submit();
        });

        // Show the copy link button when grand total is calculated
        $("#grand_total").on("keyup", function() {
            $("#copy_link_btn").show();
        });

    });
</script>






	
	</body>

</html>



