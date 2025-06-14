<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once 'config.php';

// Create database if it doesn't exist
mysqli_query($conn, "CREATE DATABASE IF NOT EXISTS artgallery");
mysqli_select_db($conn, "artgallery");

// Create tables if they don't exist (without dropping)
$createTables = [
    "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        name VARCHAR(100) NOT NULL,
        role ENUM('admin', 'artist') DEFAULT 'artist'
    )",
    "CREATE TABLE IF NOT EXISTS artworks (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        title VARCHAR(255) NOT NULL,
        description TEXT,
        image_path VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )"
];

foreach ($createTables as $query) {
    mysqli_query($conn, $query);
}

// Insert default admin if not exist
if (mysqli_num_rows(mysqli_query($conn, "SELECT * FROM users WHERE username = 'admin'")) == 0) {
    $hashedPassword = password_hash("admin123", PASSWORD_DEFAULT);
    mysqli_query($conn, "INSERT INTO users (username, password, name, role) VALUES ('admin', '$hashedPassword', 'Administrator', 'admin')");
}

// Insert sample artworks if not exist
$sampleArtworks = [
    [
        'title' => 'Harmoni Warna',
        'description' => 'Sebuah eksplorasi warna yang menggambarkan harmoni alam dan kehidupan. Karya ini terinspirasi dari keindahan spektrum warna yang ada di sekitar kita.',
        'image_path' => 'sample1.jpeg'
    ],
    [
        'title' => 'Abstraksi Modern',
        'description' => 'Interpretasi modern dari bentuk-bentuk abstrak yang menyatu dalam komposisi yang dinamis. Menggabungkan elemen geometris dengan aliran organik.',
        'image_path' => 'sample2.jpeg'
    ],
    [
        'title' => 'Digital Dreams',
        'description' => 'Perpaduan antara teknologi dan seni yang menciptakan dimensi baru dalam berkarya. Mengeksplorasi batas antara realitas dan digital.',
        'image_path' => 'sample3.jpeg'
    ]
];

$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM artworks");
$row = mysqli_fetch_assoc($result);
if ($row['count'] == 0) {
    $admin_id = mysqli_fetch_assoc(mysqli_query($conn, "SELECT id FROM users WHERE username = 'admin'"))['id'];
    foreach ($sampleArtworks as $artwork) {
        mysqli_query($conn, "INSERT INTO artworks (user_id, title, description, image_path) 
                           VALUES ('$admin_id', '{$artwork['title']}', '{$artwork['description']}', '{$artwork['image_path']}')");
    }
}

// Mengambil karya terbaru
$query = "SELECT artworks.*, users.username, users.name 
          FROM artworks 
          JOIN users ON artworks.user_id = users.id 
          ORDER BY artworks.created_at DESC 
          LIMIT 6";
$result = mysqli_query($conn, $query);
$latest_artworks = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ALs Gallery - Platform Galeri Seni Digital Indonesia</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .hero {
            text-align: center;
            padding: 6rem 2rem;
            margin-bottom: 4rem;
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            position: relative;
            overflow: hidden;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(192, 132, 252, 0.1) 0%, transparent 50%);
            animation: rotate 20s linear infinite;
        }

        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .hero h1 {
            font-size: 4rem;
            margin-bottom: 1.5rem;
            background: linear-gradient(135deg, var(--primary-lighter), var(--secondary-lighter));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            position: relative;
            animation: fadeInUp 1s ease-out;
        }

        .hero p {
            color: var(--text-dim);
            font-size: 1.3rem;
            max-width: 800px;
            margin: 0 auto 2.5rem;
            line-height: 1.8;
            animation: fadeInUp 1s ease-out 0.2s backwards;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .cta-buttons {
            display: flex;
            gap: 1.5rem;
            justify-content: center;
            animation: fadeInUp 1s ease-out 0.4s backwards;
        }

        .btn {
            padding: 1rem 2rem;
            font-size: 1.1rem;
            border-radius: 12px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: 0.5s;
        }

        .btn:hover::before {
            left: 100%;
        }

        .features {
            padding: 4rem 0;
            margin-bottom: 4rem;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2rem;
            margin: 0 auto;
            max-width: 1200px;
            padding: 0 1rem;
        }

        .feature-card {
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            padding: 2rem;
            text-align: center;
            transition: all 0.3s ease;
        }

        .feature-card:hover {
            transform: translateY(-10px);
            background: rgba(30, 41, 59, 0.9);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }

        .feature-icon {
            font-size: 2.5rem;
            margin-bottom: 1.5rem;
            background: linear-gradient(135deg, var(--primary-lighter), var(--secondary-lighter));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            transition: all 0.3s ease;
        }

        .feature-card:hover .feature-icon {
            transform: scale(1.1);
        }

        .feature-title {
            font-size: 1.5rem;
            color: var(--text-brightest);
            margin-bottom: 1rem;
        }

        .feature-description {
            color: var(--text-dim);
            line-height: 1.6;
            font-size: 0.95rem;
        }

        .latest-artworks {
            margin-bottom: 6rem;
        }

        .section-title {
            text-align: center;
            margin-bottom: 3rem;
            position: relative;
        }

        .section-title::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 3px;
            background: linear-gradient(90deg, var(--primary-lighter), var(--secondary-lighter));
            border-radius: 3px;
        }

        .section-title h2 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            background: linear-gradient(135deg, var(--primary-lighter), var(--secondary-lighter));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .artwork-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-bottom: 4rem;
        }

        .artwork-card {
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            overflow: hidden;
            transition: transform 0.3s ease;
        }

        .artwork-card:hover {
            transform: translateY(-5px);
        }

        .artwork-image {
            width: 100%;
            height: 250px;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .artwork-card:hover .artwork-image {
            transform: scale(1.05);
        }

        .artwork-info {
            padding: 1.5rem;
            background: linear-gradient(to top, rgba(30, 41, 59, 0.95), rgba(30, 41, 59, 0.7));
        }

        .artwork-info h3 {
            font-size: 1.25rem;
            color: var(--text-brightest);
            margin-bottom: 0.5rem;
        }

        .artwork-info p {
            color: var(--text-dim);
            font-size: 0.9rem;
            line-height: 1.6;
        }

        .artwork-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .artist {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--text-dim);
        }

        .artist i {
            color: var(--primary-lighter);
        }

        .date {
            color: var(--text-dimmer);
            font-size: 0.9rem;
        }

        @media (max-width: 768px) {
            .hero {
                padding: 4rem 1rem;
            }

            .hero h1 {
                font-size: 2.5rem;
            }

            .hero p {
                font-size: 1.1rem;
            }

            .cta-buttons {
                flex-direction: column;
            }

            .features {
                padding: 2rem 0;
            }

            .features-grid {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }

            .feature-card {
                padding: 1.5rem;
            }

            .artwork-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="art-bg"></div>
    
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
                    <a href="index.php" class="active">Beranda</a>
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
            <section class="hero">
                <h1>Selamat Datang di ALs Gallery</h1>
                <p>Platform galeri seni digital Indonesia yang menampilkan karya-karya terbaik dari seniman berbakat. 
                   Temukan inspirasi, bagikan karya, dan jadilah bagian dari komunitas seni digital kami.</p>
                <div class="cta-buttons">
                    <a href="gallery.php" class="btn btn-primary">
                        <i class="fas fa-images"></i>
                        Jelajahi Galeri
                    </a>
                    <?php if(!isset($_SESSION['user_id'])): ?>
                        <a href="register.php" class="btn btn-secondary">
                            <i class="fas fa-user-plus"></i>
                            Bergabung Sekarang
                        </a>
                    <?php endif; ?>
                </div>
            </section>

            <section class="features">
                <div class="section-title">
                    <h2>Fitur Unggulan</h2>
                    <p>Nikmati berbagai fitur menarik yang kami sediakan</p>
                </div>
                <div class="features-grid">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-paint-brush"></i>
                        </div>
                        <h3 class="feature-title">Galeri Digital</h3>
                        <p class="feature-description">
                            Tampilkan karya seni digitalmu dalam galeri modern dengan tampilan yang memukau. 
                            Dengan desain responsif dan efek visual yang menarik.
                        </p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <h3 class="feature-title">Komunitas Seni</h3>
                        <p class="feature-description">
                            Bergabung dengan komunitas seniman digital Indonesia yang kreatif dan inspiratif. 
                            Bagikan pengalaman dan dapatkan inspirasi baru.
                        </p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-cloud-upload-alt"></i>
                        </div>
                        <h3 class="feature-title">Mudah Digunakan</h3>
                        <p class="feature-description">
                            Unggah dan kelola karya senimu dengan antarmuka yang sederhana dan intuitif. 
                            Proses upload cepat dan aman.
                        </p>
                    </div>
                </div>
            </section>

            <section class="latest-artworks">
                <div class="section-title">
                    <h2>Karya Terbaru</h2>
                    <p>Temukan inspirasi dari karya-karya terbaru seniman kami</p>
                </div>
                <div class="artwork-grid">
                    <?php foreach ($latest_artworks as $artwork): ?>
                        <div class="artwork-card">
                            <img src="uploads/<?php echo $artwork['image_path']; ?>" alt="<?php echo $artwork['title']; ?>" class="artwork-image">
                            <div class="artwork-info">
                                <h3><?php echo $artwork['title']; ?></h3>
                                <p><?php echo substr($artwork['description'], 0, 100) . '...'; ?></p>
                                <div class="artwork-meta">
                                    <div class="artist">
                                        <i class="fas fa-user"></i>
                                        <span><?php echo $artwork['name']; ?></span>
                                    </div>
                                    <div class="date">
                                        <?php echo date('d M Y', strtotime($artwork['created_at'])); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
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