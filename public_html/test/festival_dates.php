<?php
// Set API key and other configuration details
$apiKey = '5kLLhQ11ZgjgWqG4ZMhv5wecN1sgl8qY';

$country = 'IN'; // Replace with your country code, e.g., 'IN' for India
$currentYear = date("Y");
$cacheFile = 'festival_dates.json'; // Path to cache file in root folder
$cacheDuration = 28 * 24 * 60 * 60; // 28 days in seconds

// Function to fetch festival dates from the API
function fetchFestivalDates($apiKey, $country, $currentYear) {
    $url = "https://calendarific.com/api/v2/holidays?&api_key={$apiKey}&country={$country}&year={$currentYear}";

    $response = file_get_contents($url);
    $data = json_decode($response, true);

    $holidays = $data['response']['holidays'];

    // Map the data to return only the relevant information
    $festivalDates = array_map(function($holiday) {
        return [
            'name' => $holiday['name'],
            'date' => date("m/d/Y", strtotime($holiday['date']['iso'])),
        ];
    }, $holidays);

    return $festivalDates;
}

// Function to check if the cache is valid
function isCacheValid($cacheFile, $cacheDuration) {
    if (!file_exists($cacheFile)) {
        return false;
    }

    $fileModTime = filemtime($cacheFile);
    $now = time();

    // If the file modification time + cache duration is less than current time, cache is expired
    return ($now - $fileModTime) < $cacheDuration;
}

// Get festival dates either from cache or API
if (isCacheValid($cacheFile, $cacheDuration)) {
    // If cache is valid, read the data from the cache file
    $festivalDates = json_decode(file_get_contents($cacheFile), true);
} else {
    // Cache is expired or doesn't exist, fetch fresh data
    $festivalDates = fetchFestivalDates($apiKey, $country, $currentYear);

    // Save the fresh data to the cache file
    file_put_contents($cacheFile, json_encode($festivalDates));
}

// Serve the data as JSON
header('Content-Type: application/json');
echo json_encode($festivalDates);
?>
