<?php
if (isset($_POST['file'])) {
    $filePath = urldecode($_POST['file']);
    $filePath = str_replace('https://www.biteanddiet.in/', '', $filePath); // Adjust base URL if needed
    $fullPath = __DIR__ . '/test/' . $filePath;

    if (file_exists($fullPath)) {
        if (unlink($fullPath)) {
            echo "File deleted.";
        } else {
            echo "Failed to delete file.";
        }
    } else {
        echo "File does not exist.";
    }
} else {
    echo "No file path provided.";
}
?>
