<?php include 'Header.php'; ?><div class="ttm-page-title-row"><div class="ttm-bg-layer ttm-page-title-row-bg-layer"></div><div class="container"><div class="row"><div class="col-md-12 text-center"><div class="ttm-textcolor-white title-box"><div class="ttm-textcolor-white page-title-heading"><h1 class="title">BMI Calculator</h1></div><div class="breadcrumb-wrapper"><span><a href="./"title="Homepage"><i class="ti ti-home"></i> Home </a></span><span class="ttm-bread-sep">: : </span><span><span class="ttm-textcolor-skincolor">BMI Calculator</span></span></div></div></div></div></div></div><div class="bg-white"><section style="padding-top:50px"><div class="container"><div class="row"><div class="col-md-12"><div class="text-center clearfix section-title"><div class="title-header"><h5>BMI Calculator</h5></div><h2>Check Your BMI</h2></div></div></div></div></section></div><div class="bg-white"><div class="container py-5"><div class="row"><div class="col-lg-2"></div><div class="col-lg-8"><div class="bmi-container"><div class="bmi-item-container"><div class="bmi-item"><input id="weight"max="200"min="20"oninput="calculate()"type="range"value="20"> <span id="weight-val"style="margin:10px">20 kg</span></div><div class="bmi-item"><input id="height"max="250"min="100"oninput="calculate()"type="range"value="100"> <span id="height-val"style="margin:10px">100 cm</span></div><div class="my-3"><p id="result">20.0</p><p id="category">Normal weight</p></div></div></div></div><div class="col-lg-2"></div></div></div></div><div class="bg-white"><div class="container p-5 pt-5"><div class="row"><div class="col-md-12"><div class="text-center clearfix section-title"><h3 style="font-size:30px">Choose Your Plan</h3><p>Considering your BMI, it's great that you're taking steps towards a healthier lifestyle. For more personalized guidance, I recommend reaching out to Dietician Priyanka. She can help create a detailed diet plan that suits your individual needs and preferences. Remember, maintaining a healthy diet can greatly contribute to your overall well-being. Keep up the good work!</p></div></div></div></div></div>


<script>
    function calculate(){

    var bmi;

    var result = document.getElementById("result");

    var weight = parseInt(document.getElementById("weight").value);
    document.getElementById("weight-val").textContent = weight + " kg";

    var height = parseInt(document.getElementById("height").value);
    document.getElementById("height-val").textContent = height + " cm";

    bmi = (weight / Math.pow( (height/100), 2 )).toFixed(1);
    result.textContent = bmi;
    
    if(bmi < 18.5){
        category = "Underweight";
        result.style.color = "#ffc44d";
    }
    else if( bmi >= 18.5 && bmi <= 24.9 ){
        category = "Normal Weight";
        result.style.color = "#27ae60";
    }
    else if( bmi >= 25 && bmi <= 29.9 ){
        category = "Overweight";
        result.style.color = "#ff884d";
    }
    else{
        category = "Obese";
        result.style.color = "#ff5e57";
    }
    document.getElementById("category").textContent = category;
}



    
</script>




<?php include 'footer.php'; ?>