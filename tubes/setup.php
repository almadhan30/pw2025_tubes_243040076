<?php
// Koneksi ke MySQL tanpa memilih database
$conn = mysqli_connect('localhost', 'root', 'root');

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// Buat database jika belum ada
$sql = "CREATE DATABASE IF NOT EXISTS artgallery";
if (mysqli_query($conn, $sql)) {
    echo "Database berhasil dibuat atau sudah ada<br>";
} else {
    echo "Error creating database: " . mysqli_error($conn) . "<br>";
}

// Pilih database
mysqli_select_db($conn, "artgallery");

// Buat tabel-tabel
$tables = [
    "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        name VARCHAR(100) NOT NULL,
        role ENUM('admin', 'artist') DEFAULT 'artist'
    )",
    "CREATE TABLE IF NOT EXISTS categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(50) NOT NULL
    )",
    "CREATE TABLE IF NOT EXISTS artworks (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        category_id INT,
        title VARCHAR(255) NOT NULL,
        description TEXT NOT NULL,
        image VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
    )"
];

foreach ($tables as $sql) {
    if (mysqli_query($conn, $sql)) {
        echo "Tabel berhasil dibuat atau sudah ada<br>";
    } else {
        echo "Error creating table: " . mysqli_error($conn) . "<br>";
    }
}

// Hapus akun admin yang ada (untuk memastikan tidak ada duplikasi)
mysqli_query($conn, "DELETE FROM users WHERE username = 'admin'");

// Buat akun admin baru
$hashedPassword = password_hash("admin123", PASSWORD_DEFAULT);
$sql = "INSERT INTO users (username, password, name, role) VALUES ('admin', '$hashedPassword', 'Administrator', 'admin')";
if (mysqli_query($conn, $sql)) {
    echo "Akun admin berhasil dibuat<br>";
    echo "Username: admin<br>";
    echo "Password: admin123<br>";
} else {
    echo "Error creating admin account: " . mysqli_error($conn) . "<br>";
}

// Tambah kategori default jika belum ada
if (mysqli_num_rows(mysqli_query($conn, "SELECT * FROM categories")) == 0) {
    $sql = "INSERT INTO categories (name) VALUES ('Lukisan'), ('Patung'), ('Fotografi')";
    if (mysqli_query($conn, $sql)) {
        echo "Kategori default berhasil ditambahkan<br>";
    } else {
        echo "Error adding categories: " . mysqli_error($conn) . "<br>";
    }
}

echo "<br>Setup selesai! Silakan <a href='login.php'>login</a> dengan akun admin.";

mysqli_close($conn);
?> 