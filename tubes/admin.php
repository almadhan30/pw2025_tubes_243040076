<?php
session_start();
require_once 'config.php';

// Cek apakah user sudah login dan memiliki role admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Handle hapus user
if (isset($_POST['delete_user'])) {
    $user_id = (int)$_POST['user_id'];
    mysqli_query($conn, "DELETE FROM users WHERE id = $user_id AND role != 'admin'");
}

// Handle hapus karya
if (isset($_POST['delete_artwork'])) {
    $artwork_id = (int)$_POST['artwork_id'];
    
    // Ambil nama file sebelum menghapus data
    $result = mysqli_query($conn, "SELECT image_path FROM artworks WHERE id = $artwork_id");
    if ($row = mysqli_fetch_assoc($result)) {
        $image_path = $row['image_path'];
        // Hapus file fisik
        if (file_exists("uploads/" . $image_path)) {
            unlink("uploads/" . $image_path);
        }
    }
    
    // Hapus data dari database
    mysqli_query($conn, "DELETE FROM artworks WHERE id = $artwork_id");
    header("Location: admin.php");
    exit;
}

// Handle edit karya
if (isset($_POST['edit_artwork'])) {
    $artwork_id = (int)$_POST['artwork_id'];
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    
    mysqli_query($conn, "UPDATE artworks SET title = '$title', description = '$description' WHERE id = $artwork_id");
    header("Location: admin.php");
    exit;
}

// Mengambil statistik
$query_total_users = "SELECT COUNT(*) as total FROM users";
$query_total_artworks = "SELECT COUNT(*) as total FROM artworks";
$query_recent_uploads = "SELECT artworks.*, users.username, users.name 
                        FROM artworks 
                        JOIN users ON artworks.user_id = users.id 
                        ORDER BY artworks.created_at DESC";

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
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .dashboard {
            padding: 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .dashboard-header {
            margin-bottom: 2rem;
            text-align: center;
        }

        .dashboard-header h1 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            background: linear-gradient(135deg, var(--primary-lighter), var(--secondary-lighter));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .dashboard-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 3rem;
        }

        .stat-card {
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            padding: 1.5rem;
            text-align: center;
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-card i {
            font-size: 2rem;
            margin-bottom: 1rem;
            background: linear-gradient(135deg, var(--primary-lighter), var(--secondary-lighter));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .stat-card h3 {
            font-size: 2rem;
            margin-bottom: 0.5rem;
            color: var(--text-brightest);
        }

        .stat-card p {
            color: var(--text-dim);
        }

        .artwork-management {
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            padding: 2rem;
            margin-top: 2rem;
        }

        .artwork-management h2 {
            font-size: 1.8rem;
            margin-bottom: 1.5rem;
            color: var(--text-brightest);
            text-align: center;
        }

        .artwork-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 2rem;
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
            height: 200px;
            object-fit: cover;
        }

        .artwork-info {
            padding: 1.5rem;
        }

        .artwork-info h3 {
            font-size: 1.25rem;
            color: var(--text-brightest);
            margin-bottom: 0.5rem;
        }

        .artwork-info p {
            color: var(--text-dim);
            margin-bottom: 1rem;
        }

        .artist {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--text-dim);
            margin-bottom: 1rem;
        }

        .artist i {
            color: var(--primary-lighter);
        }

        .artwork-actions {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
        }

        .btn-edit, .btn-delete {
            flex: 1;
            padding: 0.5rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn-edit {
            background: var(--primary);
            color: white;
        }

        .btn-delete {
            background: #ef4444;
            color: white;
        }

        .btn-edit:hover, .btn-delete:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            z-index: 1000;
        }

        .modal-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: var(--bg-darker);
            padding: 2rem;
            border-radius: 15px;
            width: 90%;
            max-width: 500px;
        }

        .modal-content h2 {
            margin-bottom: 1.5rem;
            color: var(--text-brightest);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--text-bright);
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 0.8rem;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            color: var(--text-bright);
            font-family: inherit;
        }

        .form-group textarea {
            min-height: 150px;
            resize: vertical;
        }

        .close {
            position: absolute;
            right: 1.5rem;
            top: 1.5rem;
            font-size: 1.5rem;
            color: var(--text-dim);
            cursor: pointer;
            transition: color 0.3s ease;
        }

        .close:hover {
            color: var(--text-bright);
        }

        @media (max-width: 768px) {
            .dashboard {
                padding: 1rem;
            }

            .artwork-grid {
                grid-template-columns: 1fr;
            }

            .modal-content {
                width: 95%;
                padding: 1.5rem;
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
                    <a href="admin.php" class="active">Dashboard Admin</a>
                    <a href="logout.php">Keluar</a>
                </div>
            </nav>
        </header>

        <main>
            <section class="dashboard">
                <div class="dashboard-header">
                    <h1>Dashboard Admin</h1>
                    <p>Kelola konten dan pengguna ALs Gallery</p>
                </div>

                <div class="dashboard-stats">
                    <div class="stat-card">
                        <i class="fas fa-users"></i>
                        <h3><?php echo $total_users; ?></h3>
                        <p>Total Pengguna</p>
                    </div>
                    <div class="stat-card">
                        <i class="fas fa-images"></i>
                        <h3><?php echo $total_artworks; ?></h3>
                        <p>Total Karya</p>
                    </div>
                </div>

                <section class="artwork-management">
                    <h2>Kelola Karya</h2>
                    <div class="artwork-grid">
                        <?php while ($artwork = mysqli_fetch_assoc($result_recent)): ?>
                            <div class="artwork-card">
                                <img src="uploads/<?php echo htmlspecialchars($artwork['image_path']); ?>" 
                                     alt="<?php echo htmlspecialchars($artwork['title']); ?>" 
                                     class="artwork-image">
                                <div class="artwork-info">
                                    <h3><?php echo htmlspecialchars($artwork['title']); ?></h3>
                                    <p><?php echo htmlspecialchars($artwork['description']); ?></p>
                                    <p class="artist">
                                        <i class="fas fa-user"></i>
                                        <?php echo htmlspecialchars($artwork['name']); ?>
                                    </p>
                                    <div class="artwork-actions">
                                        <button class="btn-edit" onclick="openEditModal(<?php echo $artwork['id']; ?>, '<?php echo addslashes($artwork['title']); ?>', '<?php echo addslashes($artwork['description']); ?>')">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Apakah Anda yakin ingin menghapus karya ini?');">
                                            <input type="hidden" name="artwork_id" value="<?php echo $artwork['id']; ?>">
                                            <button type="submit" name="delete_artwork" class="btn-delete">
                                                <i class="fas fa-trash"></i> Hapus
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </section>
            </section>
        </main>
    </div>

    <!-- Modal Edit -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeEditModal()">&times;</span>
            <h2>Edit Karya</h2>
            <form method="POST">
                <input type="hidden" name="artwork_id" id="editArtworkId">
                <div class="form-group">
                    <label for="editTitle">Judul</label>
                    <input type="text" id="editTitle" name="title" required>
                </div>
                <div class="form-group">
                    <label for="editDescription">Deskripsi</label>
                    <textarea id="editDescription" name="description" required></textarea>
                </div>
                <button type="submit" name="edit_artwork" class="btn-edit">
                    <i class="fas fa-save"></i> Simpan
                </button>
            </form>
        </div>
    </div>

    <script>
        function openEditModal(id, title, description) {
            document.getElementById('editModal').style.display = 'block';
            document.getElementById('editArtworkId').value = id;
            document.getElementById('editTitle').value = title;
            document.getElementById('editDescription').value = description;
        }

        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            if (event.target == document.getElementById('editModal')) {
                closeEditModal();
            }
        }
    </script>
</body>
</html>