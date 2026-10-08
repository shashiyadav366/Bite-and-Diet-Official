<?php
// Set timezone to Indian Standard Time (IST)
date_default_timezone_set('Asia/Kolkata');

// Use absolute path to visitors.json
$file = $_SERVER['DOCUMENT_ROOT'] . '/admin/visitors.json'; 
$ignored_ips = ['150.129.237.196', '125.63.113.186', '2409:40d0:2029:8c0a:c96:75ff:fe0b:2cd1', '152.59.180.114'];

$ip_address = $_SERVER['REMOTE_ADDR'];
$page_visited = $_SERVER['REQUEST_URI']; // Get the current page being visited

// If the visitor is accessing any /admin/... pages, ignore tracking or delete IP
if (strpos($page_visited, '/admin/') === 0) {
    // Optionally, remove the IP from the file if it exists
    if (file_exists($file)) {
        $visitor_data = json_decode(file_get_contents($file), true);
        foreach ($visitor_data as $key => $visitor) {
            if ($visitor['ip'] === $ip_address) {
                unset($visitor_data[$key]); // Remove the visitor entry with this IP
                break;
            }
        }
        // Save the modified visitor data back to the file
        file_put_contents($file, json_encode(array_values($visitor_data), JSON_PRETTY_PRINT));
    }
    exit; // Stop execution for admin pages
}

// Check if the IP address is in the ignored list
if (in_array($ip_address, $ignored_ips)) {
    exit; // Stop execution for ignored IPs
}

// Check if a unique visitor cookie exists, if not, create one
$unique_visitor_id = isset($_COOKIE['visitor_id']) ? $_COOKIE['visitor_id'] : '';

if (empty($unique_visitor_id)) {
    // Generate a unique ID for the visitor
    $unique_visitor_id = uniqid('visitor_', true);
    setcookie('visitor_id', $unique_visitor_id, time() + (365 * 24 * 60 * 60), "/"); // 1 year cookie
}

// Get the current timestamp
$timestamp = date('Y-m-d H:i:s'); // This will now be in IST
$current_date = date('Y-m-d'); // This will also be in IST

// Initialize an array to store visitor data
$visitor_data = [];

if (file_exists($file)) {
    $visitor_data = json_decode(file_get_contents($file), true);
}

// Duration sent via AJAX (convert from milliseconds to seconds)
$duration = isset($_POST['duration']) ? $_POST['duration'] / 1000 : 0; // Convert milliseconds to seconds

// Check if the IP or unique visitor ID already exists
$visitor_exists = false;
foreach ($visitor_data as &$visitor) {
    if ($visitor['ip'] === $ip_address || (isset($visitor['visitor_id']) && $visitor['visitor_id'] === $unique_visitor_id)) {
        // Check if there are visits for today
        $today_visit_exists = false;
        foreach ($visitor['visits'] as &$visit) {
            if (date('Y-m-d', strtotime($visit['time'])) === $current_date) {
                // Append to existing pages visited for today
                if (!in_array($page_visited, $visit['pages'])) {
                    $visit['pages'][] = $page_visited; // Store all pages visited
                }
                $visit['time'] = $timestamp; // Update last visit time

                // Update the duration (add it to any existing duration)
                if ($duration > 0) {
                    $visit['duration'] += round($duration, 2); // Round to 2 decimal places
                }
                $today_visit_exists = true;
                break;
            }
        }

        // If there's no visit for today, add a new visit record
        if (!$today_visit_exists) {
            $visitor['visits'][] = [
                'pages' => [$page_visited], // Store as an array
                'time' => $timestamp,
                'duration' => round($duration, 2) // Round to 2 decimal places
            ];
        }

        $visitor_exists = true;
        break;
    }
}

// If the visitor doesn't exist, add a new record
if (!$visitor_exists) {
    $visitor_data[] = [
        'ip' => $ip_address,
        'visitor_id' => $unique_visitor_id, // Store unique visitor ID
        'visits' => [['pages' => [$page_visited], 'time' => $timestamp, 'duration' => round($duration, 2)]]
    ];
}

// Write the updated data back to the JSON file
file_put_contents($file, json_encode($visitor_data, JSON_PRETTY_PRINT));

?>


<script>
    // Track the time when the user enters the page
    let startTime = Date.now();

    // Function to send the time duration when the user is leaving
    function sendDuration() {
        let endTime = Date.now();
        let duration = (endTime - startTime) / 1000; // Convert milliseconds to seconds
        
        // Debugging: Log the duration to verify if it's being calculated correctly
        console.log('Duration:', duration, 'seconds');
        
        // Send duration using AJAX
        const xhr = new XMLHttpRequest();
        xhr.open('POST', '/admin/track_visitor.php', true); // Ensure this path is correct
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        
        // Debugging: Log the status of the request
        xhr.onreadystatechange = function() {
            if (xhr.readyState === XMLHttpRequest.DONE) {
                console.log('Duration sent, server response:', xhr.responseText);
            }
        };
        
        xhr.send('duration=' + duration);
    }

    // Attach the function to events that trigger when the user is leaving the page
    window.addEventListener('beforeunload', sendDuration);
    window.addEventListener('pagehide', sendDuration); // Works better for mobile browsers
    
</script>
