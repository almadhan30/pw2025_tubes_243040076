<?php
session_start();
require_once 'config.php';

// Redirect jika sudah login
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $role = 'artist'; // Set default role as artist

    // Check if username already exists
    $check_query = "SELECT id FROM users WHERE username = ?";
    $stmt = mysqli_prepare($conn, $check_query);
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $check_result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($check_result) > 0) {
        $error_message = "Username sudah digunakan. Silakan pilih username lain.";
    } else {
        $insert_query = "INSERT INTO users (username, password, name, role) VALUES (?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $insert_query);
        mysqli_stmt_bind_param($stmt, "ssss", $username, $password, $name, $role);

        if (mysqli_stmt_execute($stmt)) {
            $success_message = "Pendaftaran berhasil! Silakan login.";
            header("refresh:2;url=login.php");
        } else {
            $error_message = "Terjadi kesalahan saat mendaftar: " . mysqli_error($conn);
        }
    }
    mysqli_stmt_close($stmt);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar - ALs Gallery</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="background-wrapper">
        <div class="background-overlay"></div>
        <div class="background-pattern"></div>
    </div>

    <div class="container">
        <header>
            <nav>
                <div class="logo">
                    <a href="index.php">
                        <h1>ALs Gallery</h1>
                    </a>
                </div>
                <div class="nav-links">
                    <a href="index.php">Beranda</a>
                    <a href="gallery.php">Galeri</a>
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <?php if($_SESSION['role'] === 'admin'): ?>
                            <a href="admin.php">Dashboard Admin</a>
                        <?php endif; ?>
                        <a href="upload.php">Unggah</a>
                        <a href="logout.php">Keluar</a>
                    <?php else: ?>
                        <a href="login.php">Masuk</a>
                        <a href="register.php" class="active">Daftar</a>
                    <?php endif; ?>
                </div>
            </nav>
        </header>

        <main class="auth-page">
            <div class="auth-container">
                <div class="auth-header">
                    <h1>Daftar ke ALs Gallery</h1>
                    <p>Bergabunglah dengan komunitas kami dan bagikan karya Anda dengan dunia.</p>
                </div>

                <?php if ($error_message): ?>
                    <div class="auth-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo $error_message; ?>
                    </div>
                <?php endif; ?>

                <?php if ($success_message): ?>
                    <div class="auth-success">
                        <i class="fas fa-check-circle"></i>
                        <?php echo $success_message; ?>
                    </div>
                <?php endif; ?>

                <form class="auth-form" method="POST">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" required
                               pattern="[a-zA-Z0-9]+" title="Hanya huruf dan angka yang diperbolehkan"
                               minlength="3" placeholder="Pilih username Anda">
                    </div>

                    <div class="form-group">
                        <label for="name">Nama Lengkap</label>
                        <input type="text" id="name" name="name" required
                               minlength="3" placeholder="Masukkan nama lengkap Anda">
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required
                               minlength="6" placeholder="Minimal 6 karakter">
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-user-plus"></i>
                        Daftar
                    </button>
                </form>

                <div class="auth-links">
                    Sudah punya akun? <a href="login.php">Masuk sekarang</a>
                </div>
            </div>
        </main>

        <footer class="footer">
            <div class="footer-content">
                <a href="index.php" class="footer-brand">ALs Gallery</a>
                <div class="footer-links">
                    <a href="about.php">Tentang</a>
                    <a href="contact.php">Kontak</a>
                </div>
            </div>
        </footer>
    </div>
</body>
</html> 