<?php
session_start();
require_once 'config.php';

// Redirect jika sudah login
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];

    $query = "SELECT * FROM users WHERE username = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($user = mysqli_fetch_assoc($result)) {
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            header('Location: index.php');
            exit();
        } else {
            $error_message = 'Password salah!';
        }
    } else {
        $error_message = 'Username tidak ditemukan!';
    }
    mysqli_stmt_close($stmt);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - ALs Gallery</title>
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
                        <a href="login.php" class="active">Masuk</a>
                        <a href="register.php">Daftar</a>
                    <?php endif; ?>
                </div>
            </nav>
        </header>

        <main class="auth-page">
            <div class="auth-container">
                <div class="auth-header">
                    <h1>Masuk ke ALs Gallery</h1>
                    <p>Selamat datang kembali! Silakan masuk ke akun Anda.</p>
                </div>

                <?php if ($error_message): ?>
                    <div class="auth-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo $error_message; ?>
                    </div>
                <?php endif; ?>

                <form class="auth-form" method="POST">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" required
                               placeholder="Masukkan username Anda">
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required
                               placeholder="Masukkan password Anda">
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-sign-in-alt"></i>
                        Masuk
                    </button>
                </form>

                <div class="auth-links">
                    Belum punya akun? <a href="register.php">Daftar sekarang</a>
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