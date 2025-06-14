<?php
session_start();
require_once 'config.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$upload_success = false;
$error_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);

    // Validasi file
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $file_name = $_FILES['image']['name'];
        $file_size = $_FILES['image']['size'];
        $file_tmp = $_FILES['image']['tmp_name'];
        $file_type = $_FILES['image']['type'];
        
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $extensions = array("jpeg", "jpg", "png", "gif");

        if (in_array($file_ext, $extensions)) {
            if ($file_size < 5242880) { // 5MB max
                $new_file_name = uniqid() . '.' . $file_ext;
                $upload_path = 'uploads/' . $new_file_name;

                // Pastikan direktori uploads ada
                if (!file_exists('uploads')) {
                    mkdir('uploads', 0777, true);
                }

                if (move_uploaded_file($file_tmp, $upload_path)) {
                    // Simpan data ke database
                    $query = "INSERT INTO artworks (title, description, image_path, user_id, category) VALUES (?, ?, ?, ?, ?)";
                    $stmt = mysqli_prepare($conn, $query);
                    mysqli_stmt_bind_param($stmt, "sssis", $title, $description, $new_file_name, $user_id, $category);

                    if (mysqli_stmt_execute($stmt)) {
                        $upload_success = true;
                    } else {
                        $error_message = "Gagal menyimpan data ke database.";
                    }
                    mysqli_stmt_close($stmt);
                } else {
                    $error_message = "Gagal mengunggah file.";
                }
            } else {
                $error_message = "Ukuran file terlalu besar (maksimal 5MB).";
            }
        } else {
            $error_message = "Format file tidak didukung.";
        }
    } else {
        $error_message = "Pilih file untuk diunggah.";
    }
}

// Ambil karya seni yang sudah diunggah oleh user
$query = "SELECT * FROM artworks WHERE user_id = ? ORDER BY created_at DESC";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unggah Karya - ALs Gallery</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .upload-section {
            max-width: 800px;
            margin: 0 auto;
            padding: 2rem;
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 15px;
        }

        .upload-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .upload-header h1 {
            font-size: 2rem;
            margin-bottom: 0.5rem;
            background: linear-gradient(135deg, var(--primary-lighter), var(--secondary-lighter));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .upload-header p {
            color: var(--text-dim);
        }

        .upload-form {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--text-bright);
        }

        .form-group input[type="text"],
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

        .file-upload {
            border: 2px dashed rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            padding: 2rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .file-upload:hover {
            border-color: var(--primary);
            background: rgba(255, 255, 255, 0.05);
        }

        .file-upload i {
            font-size: 2rem;
            color: var(--primary);
            margin-bottom: 1rem;
        }

        .file-upload span {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--text-bright);
        }

        .file-upload small {
            color: var(--text-dim);
        }

        .file-preview {
            margin-top: 1rem;
            text-align: center;
        }

        .file-preview img {
            max-width: 100%;
            max-height: 300px;
            border-radius: 8px;
            display: none;
        }

        @media (max-width: 768px) {
            .upload-section {
                margin: 1rem;
                padding: 1.5rem;
            }
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

        .my-artworks {
            margin-top: 3rem;
        }

        .my-artworks h2 {
            color: var(--text-brightest);
            margin-bottom: 1.5rem;
            text-align: center;
        }

        .artworks-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
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
            color: var(--text-brightest);
            margin-bottom: 0.5rem;
            font-size: 1.2rem;
        }

        .artwork-info p {
            color: var(--text-dim);
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }

        .artwork-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: var(--text-dim);
            font-size: 0.8rem;
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
                        <a href="upload.php" class="active">Unggah</a>
                        <a href="logout.php">Keluar</a>
                    <?php else: ?>
                        <a href="login.php">Masuk</a>
                        <a href="register.php">Daftar</a>
                    <?php endif; ?>
                </div>
            </nav>
        </header>

        <main>
            <section class="upload-section">
                <div class="upload-header">
                    <h1>Unggah Karya</h1>
                    <p>Bagikan karya seni digital Anda dengan komunitas</p>
                </div>

                <?php if($upload_success): ?>
                    <div class="success-message">Karya berhasil diunggah!</div>
                <?php endif; ?>

                <?php if($error_message): ?>
                    <div class="error-message"><?php echo $error_message; ?></div>
                <?php endif; ?>

                <form class="upload-form" method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="title">Judul Karya</label>
                        <input type="text" id="title" name="title" required>
                    </div>

                    <div class="form-group">
                        <label for="description">Deskripsi</label>
                        <textarea id="description" name="description" required></textarea>
                    </div>

                    <div class="form-group">
                        <label for="category">Kategori</label>
                        <select id="category" name="category" required>
                            <option value="Umum">Umum</option>
                            <option value="Lukisan Digital">Lukisan Digital</option>
                            <option value="Ilustrasi">Ilustrasi</option>
                            <option value="Fotografi">Fotografi</option>
                            <option value="3D Art">3D Art</option>
                            <option value="Vector Art">Vector Art</option>
                            <option value="Pixel Art">Pixel Art</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="image">Pilih Gambar</label>
                        <input type="file" id="image" name="image" accept="image/*" required>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-upload"></i>
                        Unggah Karya
                    </button>
                </form>

                <div class="my-artworks">
                    <h2>Karya Saya</h2>
                    <div class="artworks-grid">
                        <?php while($row = mysqli_fetch_assoc($result)): ?>
                            <div class="artwork-card">
                                <img src="uploads/<?php echo htmlspecialchars($row['image_path']); ?>" 
                                     alt="<?php echo htmlspecialchars($row['title']); ?>" 
                                     class="artwork-image">
                                <div class="artwork-info">
                                    <h3><?php echo htmlspecialchars($row['title']); ?></h3>
                                    <p><?php echo htmlspecialchars($row['description']); ?></p>
                                    <div class="artwork-meta">
                                        <span class="category"><?php echo htmlspecialchars($row['category']); ?></span>
                                        <span class="date"><?php echo date('d M Y', strtotime($row['created_at'])); ?></span>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
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

    <script>
        function previewImage(input) {
            const preview = document.getElementById('preview');
            const file = input.files[0];
            const reader = new FileReader();

            reader.onloadend = function() {
                preview.src = reader.result;
                preview.style.display = 'block';
            }

            if (file) {
                reader.readAsDataURL(file);
            } else {
                preview.src = '#';
                preview.style.display = 'none';
            }
        }
    </script>
</body>
</html> 