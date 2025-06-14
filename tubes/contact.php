<?php
session_start();
require_once 'config.php';

$message = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $subject = $_POST['subject'];
    $message_content = $_POST['message'];
    
    $sql = "INSERT INTO messages (name, email, subject, message) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $name, $email, $subject, $message_content);
    
    if ($stmt->execute()) {
        $message = "Pesan Anda telah berhasil dikirim!";
    } else {
        $message = "Terjadi kesalahan saat mengirim pesan: " . $stmt->error;
    }
    
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kontak - ALs Gallery</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .contact-section {
            max-width: 1000px;
            margin: 0 auto;
            padding: 4rem 2rem;
        }

        .contact-header {
            text-align: center;
            margin-bottom: 3rem;
        }

        .contact-header h1 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            background: linear-gradient(135deg, var(--primary-lighter), var(--secondary-lighter));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .contact-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 3rem;
        }

        .contact-info {
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            padding: 2rem;
        }

        .contact-info h2 {
            color: var(--text-brightest);
            margin-bottom: 1.5rem;
            font-size: 1.8rem;
        }

        .contact-info-item {
            display: flex;
            align-items: center;
            margin-bottom: 1.5rem;
            color: var(--text-dim);
        }

        .contact-info-item i {
            font-size: 1.5rem;
            margin-right: 1rem;
            color: var(--primary);
        }

        .contact-form {
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            padding: 2rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--text-brightest);
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            background: rgba(30, 41, 59, 0.5);
            color: var(--text-brightest);
            font-family: 'Poppins', sans-serif;
        }

        .form-group textarea {
            height: 150px;
            resize: vertical;
        }

        .submit-btn {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            padding: 1rem 2rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            transition: transform 0.3s ease;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
        }

        .success-message {
            background: rgba(22, 163, 74, 0.7);
            color: white;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            text-align: center;
        }

        .error-message {
            background: rgba(220, 38, 38, 0.7);
            color: white;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            text-align: center;
        }

        @media (max-width: 768px) {
            .contact-content {
                grid-template-columns: 1fr;
            }

            .contact-section {
                padding: 2rem 1rem;
            }

            .contact-header h1 {
                font-size: 2rem;
            }
        }
    </style>
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
                        <a href="register.php">Daftar</a>
                    <?php endif; ?>
                </div>
            </nav>
        </header>

        <main>
            <section class="contact-section">
                <div class="contact-header">
                    <h1>Hubungi Kami</h1>
                    <p>Kami siap membantu Anda</p>
                </div>

                <?php if($message): ?>
                    <div class="<?php echo strpos($message, 'berhasil') !== false ? 'success-message' : 'error-message'; ?>">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>

                <div class="contact-content">
                    <div class="contact-info">
                        <h2>Informasi Kontak</h2>
                        <div class="contact-info-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <div>
                                <h3>Alamat</h3>
                                <p>Jl. Raya Bandung No. 123<br>Bandung, Jawa Barat</p>
                            </div>
                        </div>
                        <div class="contact-info-item">
                            <i class="fas fa-phone"></i>
                            <div>
                                <h3>Telepon</h3>
                                <p>+62 812-3456-7890</p>
                            </div>
                        </div>
                        <div class="contact-info-item">
                            <i class="fas fa-envelope"></i>
                            <div>
                                <h3>Email</h3>
                                <p>info@alsgallery.com</p>
                            </div>
                        </div>
                        <div class="contact-info-item">
                            <i class="fas fa-clock"></i>
                            <div>
                                <h3>Jam Operasional</h3>
                                <p>Senin - Jumat: 09:00 - 17:00<br>Sabtu: 09:00 - 15:00</p>
                            </div>
                        </div>
                    </div>

                    <div class="contact-form">
                        <form method="POST" action="">
                            <div class="form-group">
                                <label for="name">Nama</label>
                                <input type="text" id="name" name="name" required>
                            </div>
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" id="email" name="email" required>
                            </div>
                            <div class="form-group">
                                <label for="subject">Subjek</label>
                                <input type="text" id="subject" name="subject" required>
                            </div>
                            <div class="form-group">
                                <label for="message">Pesan</label>
                                <textarea id="message" name="message" required></textarea>
                            </div>
                            <button type="submit" class="submit-btn">Kirim Pesan</button>
                        </form>
                    </div>
                </div>
            </section>
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