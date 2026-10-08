<?php
date_default_timezone_set('Asia/Kolkata'); // Set timezone

// Load the JSON file
$jsonData = file_get_contents('events.json'); 
$events = json_decode($jsonData, true);

// Get today's date
$today = date('Y-m-d');

// Find Valentine's Day event
$valentineEvent = null;
foreach ($events as $event) {
    if (strpos(strtolower($event['caption']), 'valentine') !== false) {
        $valentineEvent = $event;
        break;
    }
}

// Default settings
$defaultBg = 'default-bg.jpg'; 

if ($valentineEvent) {
    $bgAnimation = "valentine-bg"; // Apply Valentine's theme
} else {
    $bgAnimation = "default-bg";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Valentine's Special | Bite and Diet</title>
    <style>
        /* Soft Pink Animated Background */
        body {
            margin: 0;
            padding: 0;
            overflow: hidden;
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(-45deg, #ff9a9e, #fad0c4, #ffdde1, #ff758c);
            background-size: 400% 400%;
            animation: gradientAnimation 15s ease infinite;
        }

        @keyframes gradientAnimation {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        /* Floating Heart Animation */
        @keyframes floatHearts {
            0% { transform: translateY(100vh) scale(0.6); opacity: 1; }
            100% { transform: translateY(-10vh) scale(1); opacity: 0; }
        }

        .heart {
            position: absolute;
            width: 30px;
            height: 30px;
            animation: floatHearts 5s linear infinite;
        }

        h1 {
            text-align: center;
            color: white;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
            margin-top: 50px;
        }

    </style>
</head>
<body>
    <h1>💖 Happy Valentine's Day from Bite and Diet! 💖</h1>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        for (let i = 0; i < 20; i++) { 
            let heart = document.createElement("div");
            heart.innerHTML = `
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="red" width="50px" height="50px">
                    <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                </svg>`;
            heart.className = "heart";
            heart.style.left = Math.random() * window.innerWidth + "px";
            heart.style.animationDuration = (Math.random() * 5 + 3) + "s";
            document.body.appendChild(heart);
        }
    });
</script>

</body>
</html>