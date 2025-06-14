<?php
session_start();
require_once '../config.php';

// Cek apakah user sudah login dan memiliki role admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// Mengambil statistik
$query_total_users = "SELECT COUNT(*) as total FROM users";
$query_total_artworks = "SELECT COUNT(*) as total FROM artworks";
$query_recent_uploads = "SELECT artworks.*, users.username 
                        FROM artworks 
                        JOIN users ON artworks.user_id = users.id 
                        ORDER BY artworks.created_at DESC 
                        LIMIT 5";

$result_users = mysqli_query($conn, $query_total_users);
$result_artworks = mysqli_query($conn, $query_total_artworks);
$result_recent = mysqli_query($conn, $query_recent_uploads);

$total_users = mysqli_fetch_assoc($result_users)['total'];
$total_artworks = mysqli_fetch_assoc($result_artworks)['total'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>Dashboard Admin - ALs Gallery</title>
    <link rel="stylesheet" href="../style.css?v=<?php echo time(); ?>">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: #1a1f2e;
            color: #fff;
            min-height: 100vh;
        }

        .art-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            background: 
                radial-gradient(at 40% 20%, rgba(88, 28, 135, 0.15) 0, transparent 50%),
                radial-gradient(at 80% 0%, rgba(129, 140, 248, 0.15) 0, transparent 50%),
                radial-gradient(at 0% 50%, rgba(192, 132, 252, 0.15) 0, transparent 50%);
        }

        .navbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 2rem;
            background: rgba(17, 25, 40, 0.75);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.125);
        }

        .navbar-brand {
            font-size: 1.5rem;
            font-weight: 600;
            text-decoration: none;
            background: linear-gradient(to right, #c084fc, #a855f7);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            transition: opacity 0.3s ease;
        }

        .navbar-brand:hover {
            opacity: 0.8;
        }

        .navbar-links {
            display: flex;
            gap: 2rem;
            align-items: center;
        }

        .navbar-links a {
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            font-size: 1rem;
            font-weight: 500;
            transition: all 0.3s ease;
            position: relative;
        }

        .navbar-links a:hover {
            color: #fff;
        }

        .navbar-links a::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: -4px;
            left: 0;
            background: linear-gradient(to right, #c084fc, #a855f7);
            transition: width 0.3s ease;
        }

        .navbar-links a:hover::after {
            width: 100%;
        }

        .navbar-links a.active {
            color: #fff;
        }

        .navbar-links a.active::after {
            width: 100%;
        }

        @media (max-width: 768px) {
            .navbar {
                padding: 1rem;
                flex-direction: column;
                gap: 1rem;
            }

            .navbar-links {
                flex-direction: column;
                gap: 1rem;
                width: 100%;
                text-align: center;
            }

            .navbar-links a {
                display: block;
                padding: 0.5rem;
            }
        }

        .dashboard-header {
            padding-top: 6rem;
            text-align: center;
            margin: 3rem 0;
        }

        .dashboard-header h1 {
            font-size: 2.5rem;
            color: #c4b5fd;
            margin-bottom: 1rem;
            font-weight: 600;
        }

        .dashboard-header p {
            color: #94a3b8;
            font-size: 1.1rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 2rem;
            margin: 2rem auto;
            max-width: 800px;
        }

        .stat-card {
            background: rgba(30, 41, 59, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 1rem;
            padding: 2rem;
            text-align: center;
            backdrop-filter: blur(10px);
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-card i {
            font-size: 2rem;
            color: #c4b5fd;
            margin-bottom: 1rem;
        }

        .stat-card .number {
            font-size: 3rem;
            font-weight: 600;
            color: #fff;
            margin: 1rem 0;
        }

        .stat-card .label {
            color: #94a3b8;
            font-size: 1.1rem;
        }

        .recent-uploads {
            background: rgba(30, 41, 59, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 1rem;
            padding: 2rem;
            margin: 3rem auto;
            max-width: 1000px;
        }

        .recent-uploads h2 {
            color: #fff;
            font-size: 1.8rem;
            margin-bottom: 2rem;
            text-align: center;
        }

        .artwork-list {
            display: grid;
            gap: 1.5rem;
        }

        .artwork-item {
            display: flex;
            align-items: center;
            gap: 1.5rem;
            padding: 1.5rem;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 0.8rem;
            transition: transform 0.3s ease;
        }

        .artwork-item:hover {
            transform: translateX(5px);
            background: rgba(255, 255, 255, 0.08);
        }

        .artwork-item img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 0.5rem;
        }

        .artwork-info {
            flex: 1;
        }

        .artwork-info h4 {
            color: #fff;
            margin-bottom: 0.5rem;
            font-size: 1.1rem;
        }

        .artwork-info p {
            color: #94a3b8;
            font-size: 0.9rem;
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
        }

        .btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 0.5rem;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #fff;
        }

        .btn-edit {
            background: #4f46e5;
        }

        .btn-delete {
            background: #dc2626;
        }

        .btn:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="art-bg"></div>

    <nav>
        <div class="logo">
            <h1><a href="../index.php">ALs Gallery</a></h1>
        </div>
        <div class="nav-links">
            <a href="../index.php">Beranda</a>
            <a href="../gallery.php">Galeri</a>
            <a href="dashboard.php" class="active">Dashboard Admin</a>
            <a href="../logout.php">Keluar</a>
        </div>
    </nav>

    <div class="dashboard-header">
        <h1>Dashboard Admin</h1>
        <p></p>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <i class="fas fa-users"></i>
            <div class="number"><?php echo $total_users; ?></div>
            <div class="label">Total Pengguna</div>
        </div>
        <div class="stat-card">
            <i class="fas fa-images"></i>
            <div class="number"><?php echo $total_artworks; ?></div>
            <div class="label">Total Karya</div>
        </div>
    </div>

    <div class="recent-uploads">
        <h2>Unggahan Terbaru</h2>
        <div class="artwork-list">
            <?php while ($artwork = mysqli_fetch_assoc($result_recent)): ?>
                <div class="artwork-item">
                    <img src="../uploads/<?php echo $artwork['image_path']; ?>" alt="<?php echo $artwork['title']; ?>">
                    <div class="artwork-info">
                        <h4><?php echo $artwork['title']; ?></h4>
                        <p>Seniman: <?php echo $artwork['username']; ?></p>
                    </div>
                    <div class="action-buttons">
                        <button class="btn btn-edit">Ubah</button>
                        <button class="btn btn-delete">Hapus</button>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

    <footer class="footer">
        <div class="footer-content">
            <a href="../index.php" class="footer-brand">ALs Gallery</a>
            <div class="footer-links">
                <a href="../about.php">Tentang</a>
                <a href="../contact.php">Kontak</a>
            </div>
        </div>
    </footer>
</body>
</html> 