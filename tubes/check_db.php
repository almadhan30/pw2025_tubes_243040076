<?php
require_once 'config.php';

echo "<h2>Database Connection Check</h2>";

// Cek koneksi database
if ($conn) {
    echo "✅ Database connection successful<br><br>";
} else {
    die("❌ Connection failed: " . mysqli_connect_error());
}

// Cek tabel users
$users_query = "DESCRIBE users";
$users_result = mysqli_query($conn, $users_query);

echo "<h3>Users Table Structure:</h3>";
if ($users_result) {
    echo "✅ Users table exists<br>";
    echo "<pre>";
    while ($row = mysqli_fetch_assoc($users_result)) {
        echo $row['Field'] . " - " . $row['Type'] . "<br>";
    }
    echo "</pre>";
} else {
    echo "❌ Users table not found<br>";
}

// Cek tabel artworks
$artworks_query = "DESCRIBE artworks";
$artworks_result = mysqli_query($conn, $artworks_query);

echo "<h3>Artworks Table Structure:</h3>";
if ($artworks_result) {
    echo "✅ Artworks table exists<br>";
    echo "<pre>";
    while ($row = mysqli_fetch_assoc($artworks_result)) {
        echo $row['Field'] . " - " . $row['Type'] . "<br>";
    }
    echo "</pre>";
} else {
    echo "❌ Artworks table not found<br>";
}

// Cek jumlah data
$users_count = mysqli_query($conn, "SELECT COUNT(*) as count FROM users");
$artworks_count = mysqli_query($conn, "SELECT COUNT(*) as count FROM artworks");

echo "<h3>Data Count:</h3>";
if ($users_count) {
    $count = mysqli_fetch_assoc($users_count);
    echo "Users: " . $count['count'] . "<br>";
}

if ($artworks_count) {
    $count = mysqli_fetch_assoc($artworks_count);
    echo "Artworks: " . $count['count'] . "<br>";
}
?> 