<?php
session_start();
require_once 'config.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tentang Kami - ALs Gallery</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .about-section {
            max-width: 1000px;
            margin: 0 auto;
            padding: 4rem 2rem;
        }

        .about-header {
            text-align: center;
            margin-bottom: 3rem;
        }

        .about-header h1 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            background: linear-gradient(135deg, var(--primary-lighter), var(--secondary-lighter));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .about-content {
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 3rem;
        }

        .about-content h2 {
            font-size: 1.8rem;
            color: var(--text-brightest);
            margin-bottom: 1.5rem;
        }

        .about-content p {
            color: var(--text-dim);
            line-height: 1.8;
            margin-bottom: 1.5rem;
            font-size: 1.1rem;
        }

        .team-section {
            text-align: center;
            margin-top: 3rem;
        }

        .team-section h2 {
            font-size: 2rem;
            color: var(--text-brightest);
            margin-bottom: 2rem;
        }

        .team-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }

        .team-member {
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            padding: 2rem;
            text-align: center;
            transition: transform 0.3s ease;
        }

        .team-member:hover {
            transform: translateY(-10px);
        }

        .team-member img {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            margin-bottom: 1.5rem;
            border: 3px solid var(--primary);
        }

        .team-member h3 {
            color: var(--text-brightest);
            margin-bottom: 0.5rem;
        }

        .team-member p {
            color: var(--text-dim);
            font-size: 0.9rem;
        }

        .social-links {
            display: flex;
            justify-content: center;
            gap: 1rem;
            margin-top: 1rem;
        }

        .social-links a {
            color: var(--text-dim);
            transition: color 0.3s ease;
        }

        .social-links a:hover {
            color: var(--primary);
        }

        @media (max-width: 768px) {
            .about-section {
                padding: 2rem 1rem;
            }

            .about-header h1 {
                font-size: 2rem;
            }

            .team-grid {
                grid-template-columns: 1fr;
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
            <section class="about-section">
                <div class="about-header">
                    <h1>Tentang ALs Gallery</h1>
                    <p>Platform galeri seni digital Indonesia</p>
                </div>

                <div class="about-content">
                    <h2>Visi Kami</h2>
                    <p>
                        ALs Gallery hadir sebagai wadah bagi seniman digital Indonesia untuk menampilkan dan 
                        membagikan karya-karya terbaik mereka. Kami berkomitmen untuk membangun komunitas seni 
                        digital yang inklusif, kreatif, dan inspiratif.
                    </p>

                    <h2>Misi Kami</h2>
                    <p>
                        - Menyediakan platform yang mudah digunakan untuk menampilkan karya seni digital<br>
                        - Membangun komunitas seniman digital yang saling mendukung<br>
                        - Mendorong kreativitas dan inovasi dalam seni digital<br>
                        - Menjadi jembatan antara seniman dan penikmat seni digital
                    </p>

                    <h2>Sejarah</h2>
                    <p>
                        Didirikan pada tahun 2023, ALs Gallery bermula dari sebuah ide sederhana untuk 
                        menciptakan ruang digital yang mempertemukan para seniman dengan penikmat seni. 
                        Sejak itu, kami terus berkembang dan berinovasi untuk memberikan pengalaman terbaik 
                        bagi komunitas kami.
                    </p>
                </div>

                <div class="team-section">
                    <h2>Tim Kami</h2>
                    <div class="team-grid">
                        <div class="team-member">
                            <img src="uploads/profile1.jpg" alt="Founder">
                            <h3>Almadhan</h3>
                            <p>Founder & Developer</p>
                        </div>
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