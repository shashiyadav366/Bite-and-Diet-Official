<?php
if (isset($_GET['url'])) {
    $imageUrl = $_GET['url'];

    // Initialize cURL
    $ch = curl_init($imageUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $imageContent = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // Check HTTP response code
    if ($httpCode != 200) {
        error_log("Failed to retrieve image. HTTP Status Code: $httpCode", 3, 'errors.log');
        die("Failed to retrieve image. HTTP Status Code: $httpCode");
    }

    $imageName = basename($imageUrl);
    $directoryPath = __DIR__ . '/test/';
    $savePath = $directoryPath . $imageName;

    // Check if directory exists, create if it doesn't
    if (!file_exists($directoryPath)) {
        if (!mkdir($directoryPath, 0755, true)) {
            error_log("Failed to create directory: $directoryPath", 3, 'errors.log');
            die("Failed to create directory.");
        }
    }

    // Check if directory is writable
    if (!is_writable($directoryPath)) {
        error_log("Directory is not writable: $directoryPath", 3, 'errors.log');
        die("Directory is not writable.");
    }

    // Save the image
    if (file_put_contents($savePath, $imageContent) === FALSE) {
        error_log("Failed to save image. Path: $savePath", 3, 'errors.log');
        die("Failed to save image to the server. Check errors.log for details.");
    }

    echo "Image saved. <a id='download-link' href='$savePath' download>Click here to download the image to your phone</a>";
} else {
    echo "No URL provided.";
}
?>


<script>
document.getElementById('download-link').addEventListener('click', function() {
    // Trigger the file deletion after download
    setTimeout(function() {
        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'delete_image.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        
        // Prepare file path for deletion
        var filePath = encodeURIComponent(document.getElementById('download-link').href);
        xhr.send('file=' + filePath);

        xhr.onload = function() {
            if (xhr.status >= 200 && xhr.status < 300) {
                console.log('File deletion response: ' + xhr.responseText);
            } else {
                console.error('Failed to delete file.');
            }
        };
    }, 1000); // Adjust time if necessary
});
</script>


