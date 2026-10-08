<?php 
  require ("fpdf/fpdf.php");
  require ("word.php");
  require_once __DIR__ . '/../json_db.php';

  //customer and invoice details
  $info=[
    "customer"=>"",
    "address"=>",",
    "city"=>"",
    "invoice_no"=>"",
    "invoice_date"=>"",
    "total_amt"=>"",
    "words"=>"",
  ];
  
  //Select Invoice Details From JSON
  $found=false;
  foreach(jd_read('invoices') as $inv){
	  if(isset($inv['sid'])&&(int)$inv['sid']===(int)$_GET["id"]){ $row=$inv; $found=true; break; }
  }
  if($found){
	  $obj=new IndianCurrency($row["grand_total"]);
	 

	  $info=[
		"customer"=>$row["cname"],
		"address"=>$row["caddress"],
		"city"=>$row["ccity"],
		"invoice_no"=>$row["invoice_no"],
		"invoice_date"=>date("d-m-Y",strtotime($row["invoice_date"])),
		"total_amt"=>$row["grand_total"],
		"words"=> $obj->get_words(),
	  ];
  }
  
  //invoice Products
  $products_info=[];
  
  //Select Invoice Product Details From JSON
  if($found){
	  foreach($row["products"] as $prod){
		   $products_info[]=[
			"name"=>$prod["name"],
			"price"=>$prod["price"],
			"qty"=>$prod["qty"],
			"total"=>$prod["total"],
		   ];
	  }
  }
  
  class PDF extends FPDF
  {
    function Header(){
      
      //Display Company Info
      $this->SetFont('Arial','B',14);
      $this->Cell(50,10,"BITE AND DIET",0,1);
      $this->SetFont('Arial','',14);
      $this->Cell(50,7,"DLF Phase 3,",0,1);
      $this->Cell(50,7,"Gurugram,",0,1);
      $this->Cell(50,7,"Phone : +91 8826549878",0,1);
      
      //Display INVOICE text
    //  $this->SetY(15);
     // $this->SetX(-40);
     // $this->SetFont('Arial','B',18);
    //  $this->Cell(50,10,"INVOICE",0,1);
    
    // Display Logo Image
        $this->Image('https://www.biteanddiet.in/images/big_logo.png', 150, 10, 50); // Adjust the coordinates as needed
      
      
      //Display Horizontal line
      $this->Line(0,48,210,48);
    }
    
    function body($info,$products_info){
      
      //Billing Details
      $this->SetY(55);
      $this->SetX(10);
      $this->SetFont('Arial','B',12);
      $this->Cell(50,10,"Bill To: ",0,1);
      $this->SetFont('Arial','',12);
      $this->Cell(50,7,$info["customer"],0,1);
      $this->Cell(50,7,$info["address"],0,1);
      $this->Cell(50,7,$info["city"],0,1);
      
      //Display Invoice no
      $this->SetY(55);
      $this->SetX(-60);
      $this->Cell(50,7,"Invoice No : ".$info["invoice_no"]);
      
      //Display Invoice date
      $this->SetY(63);
      $this->SetX(-60);
      $this->Cell(50,7,"Invoice Date : ".$info["invoice_date"]);
      
      //Display Table headings
      $this->SetY(95);
      $this->SetX(10);
      $this->SetFont('Arial','B',12);
      $this->Cell(80,9,"DESCRIPTION",1,0);
      $this->Cell(40,9,"PRICE",1,0,"C");
      $this->Cell(30,9,"QTY",1,0,"C");
      $this->Cell(40,9,"TOTAL",1,1,"C");
      $this->SetFont('Arial','',12);
      
      //Display table product rows
      foreach($products_info as $row){
        $this->Cell(80,9,$row["name"],"LR",0);
        $this->Cell(40,9,$row["price"],"R",0,"C");
        $this->Cell(30,9,$row["qty"],"R",0,"C");
        $this->Cell(40,9,$row["total"],"R",1,"C");
      }
      //Display table empty rows
      for($i=0;$i<12-count($products_info);$i++)
      {
        $this->Cell(80,9,"","LR",0);
        $this->Cell(40,9,"","R",0,"R");
        $this->Cell(30,9,"","R",0,"C");
        $this->Cell(40,9,"","R",1,"R");
      }
      //Display table total row
      $this->SetFont('Arial','B',12);
      $this->Cell(150,9,"TOTAL",1,0,"R");
      $this->Cell(40,9,$info["total_amt"],1,1,"C");
      
      //Display amount in words
      $this->SetY(225);
      $this->SetX(10);
      $this->SetFont('Arial','B',12);
      $this->Cell(0,9,"Amount in Words ",0,1);
      $this->SetFont('Arial','',12);
      $this->Cell(0,9,$info["words"],0,1);
      
    }
    function Footer(){
      
      //set footer position
      $this->SetY(-50);
      $this->SetFont('Arial','B',12);
      $this->Cell(0,10,"for BITE AND DIET",0,1,"R");
      $this->Ln(15);
      $this->SetFont('Arial','',12);
      $this->Cell(0,10,"Authorized Signature",0,1,"R");
      $this->SetFont('Arial','',10);
      
      //Display Footer Text
      $this->Cell(0,10,"This is a computer generated invoice",0,1,"C");
      
    }
    
  }
  
  // Determine the filename based on the client's name and invoice date
$clientName = $info["customer"];
$invoiceDate = date("Y-m-d", strtotime($info["invoice_date"]));
$filename = "Invoice_" . preg_replace('/\W+/', '_', $clientName) . "_" . $invoiceDate . ".pdf";

  
  
// Create A4 Page with Portrait
$pdf = new PDF("P","mm","A4");
$pdf->AddPage();
$pdf->body($info, $products_info);

// Output the PDF with the specified filename
$pdf->Output($filename, "D"); // "D" parameter forces a download

?>