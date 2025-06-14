<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'config.php';

echo "<h2>Upload System Check</h2>";

// Cek koneksi database
if ($conn) {
    echo "✅ Database connection successful<br>";
} else {
    echo "❌ Database connection failed: " . mysqli_connect_error() . "<br>";
}

// Cek folder uploads
$upload_dir = 'uploads';
if (is_dir($upload_dir)) {
    echo "✅ Uploads directory exists<br>";
    if (is_writable($upload_dir)) {
        echo "✅ Uploads directory is writable<br>";
    } else {
        echo "❌ Uploads directory is not writable<br>";
    }
} else {
    echo "❌ Uploads directory does not exist<br>";
}

// Cek tabel artworks
$table_check = mysqli_query($conn, "SHOW TABLES LIKE 'artworks'");
if (mysqli_num_rows($table_check) > 0) {
    echo "✅ Artworks table exists<br>";
    
    // Cek struktur tabel
    $columns = mysqli_query($conn, "SHOW COLUMNS FROM artworks");
    echo "<h3>Table Structure:</h3>";
    echo "<pre>";
    while ($column = mysqli_fetch_assoc($columns)) {
        echo $column['Field'] . " - " . $column['Type'] . "<br>";
    }
    echo "</pre>";
} else {
    echo "❌ Artworks table does not exist<br>";
}

// Cek PHP upload settings
echo "<h3>PHP Upload Settings:</h3>";
echo "Upload max filesize: " . ini_get('upload_max_filesize') . "<br>";
echo "Post max size: " . ini_get('post_max_size') . "<br>";
echo "Memory limit: " . ini_get('memory_limit') . "<br>";
echo "Max execution time: " . ini_get('max_execution_time') . " seconds<br>";

// Cek permission folder
echo "<h3>Directory Permissions:</h3>";
echo "Upload directory: " . substr(sprintf('%o', fileperms($upload_dir)), -4) . "<br>";

// Test write ke folder uploads
$test_file = $upload_dir . '/test.txt';
$write_test = @file_put_contents($test_file, 'test');
if ($write_test !== false) {
    echo "✅ Successfully wrote test file<br>";
    unlink($test_file);
    echo "✅ Successfully deleted test file<br>";
} else {
    echo "❌ Failed to write test file<br>";
}
?> 