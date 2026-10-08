<html lang="en"><head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>No Internet Connection</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            background-color: #f2f2f2;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background: #013220;
            color:#fff;
        }
        
        
        .bmi-section{
               width: 800px;
    height: 100%;
    display: flex;
    justify-content: center;
    flex-direction: column;

        }
        
        .bmi-container {
    display: flex;
    justify-content: center;
    text-align: center;
    padding: 50px 20px;
    align-items: center;
    border-radius: 10px;
    background-color: #27ae60;
    box-shadow: 0px 2px 1px -1px rgba(0,0,0,0.2), 0px 1px 1px 0px rgba(0,0,0,0.14), 0px 1px 3px 0px rgba(0,0,0,0.12);
}
        
       .bmi-item-container {
           
           width: 100%;
    display: flex;
    justify-content: center;
    align-items: center;
    flex-direction: column;
       }
        
        
        .bmi-item {
    margin: 10px;
    display: flex;
    justify-content: center;
    align-items: center;
    text-align: center;
    width: 60%;
}
        
        
        
        

        h1 {
            color: #ffff00;
        }
        p {
            font-size: 18px;
        }
        
        
       
        
        /* Media query for phones (less than 768px) */
@media screen and (max-width: 767px) {
    
    
    
    
    .bmi-section {
    padding: 0 50px;
}
    
     .bmi-item {
         width:100%;
     }
    
    
    
    
    
    /*.container {*/
    /*    max-width: 75%;*/
    /*}*/
    
    h1 {
        font-size: 26px;
    }

    h1 {
        font-size: 24px;
    }

    p {
        font-size: 16px;
    }
}
        
        * Media query for tablets (768px to 1023px) */
@media screen and (min-width: 768px) and (max-width: 1023px) {
   

    h1 {
        font-size: 32px;
    }

    p {
        font-size: 20px;
    }
}
        
    </style>
</head>
<body>
    
   <div class="bmi-section">

    <div class="internet-container">
<!---<img style="width:100px" src="/images/favicon.png">--->

                    
        <h1>No Internet Connection</h1>
        <p>Please check your internet connection and try again.</p>
    </div>
    
     
    
 <div class="bmi-heading-container">
                        <h3 style="font-size: 28px;">Wellness Awaits, Even Without the Web</h3>
                        <p>Discover your BMI and embrace a healthier lifestyle.</p>
                    </div>
                    
    
    <!-- BMI Calculator Section -->
    
               
                
                    <div class="bmi-container">
                        <div class="bmi-item-container">
                            <div class="bmi-item">
                                <input id="weight" max="200" min="20" oninput="calculate()" type="range" value="20" style="
    width: 100%;">
                                <span id="weight-val" style="width: 80px;margin: 10px">20 kg</span>
                            </div>
                            <div class="bmi-item">
                                <input id="height" max="250" min="100" oninput="calculate()" type="range" value="100" style="
    width: 100%;">
                                <span id="height-val" style="width: 80px;margin: 10px">100 cm</span>
                            </div>
                            <div class="my-3">
                                <p style="font-size:24px; font-weight:900" id="result">20.0</p>
                                <p id="category">Normal weight</p>
                            </div>
                        </div>
                    </div>

                    
    
    </div>
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
        result.style.color = "#fff";
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
    
    


</body></html>