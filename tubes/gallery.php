<?php
session_start();
require_once 'config.php';

// Get categories from database
$categories_query = "SELECT DISTINCT category FROM artworks ORDER BY category";
$categories_result = $conn->query($categories_query);
$categories = [];
while ($row = $categories_result->fetch_assoc()) {
    $categories[] = $row['category'];
}

// Handle search and filter
$search = isset($_GET['search']) ? $_GET['search'] : '';
$category_filter = isset($_GET['category']) ? $_GET['category'] : '';

// Build the SQL query with search and category filter
$sql = "SELECT artworks.*, users.username as artist_name 
        FROM artworks 
        JOIN users ON artworks.user_id = users.id 
        WHERE 1=1";

if (!empty($search)) {
    $sql .= " AND (title LIKE ? OR description LIKE ? OR users.username LIKE ?)";
}

if (!empty($category_filter)) {
    $sql .= " AND category = ?";
}

$sql .= " ORDER BY created_at DESC";

$stmt = $conn->prepare($sql);

// Bind parameters based on filters
if (!empty($search) && !empty($category_filter)) {
    $search_param = "%$search%";
    $stmt->bind_param("ssss", $search_param, $search_param, $search_param, $category_filter);
} elseif (!empty($search)) {
    $search_param = "%$search%";
    $stmt->bind_param("sss", $search_param, $search_param, $search_param);
} elseif (!empty($category_filter)) {
    $stmt->bind_param("s", $category_filter);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Galeri - ALs Gallery</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .search-filter-container {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            align-items: center;
            justify-content: center;
            flex-wrap: wrap;
        }

        .search-box, .filter-box {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 8px;
            padding: 0.5rem 1rem;
            color: var(--text-brightest);
            min-width: 200px;
        }

        .search-box input, .filter-box select {
            background: transparent;
            border: none;
            color: var(--text-brightest);
            width: 100%;
            outline: none;
        }

        .filter-box select option {
            background: rgba(30, 41, 59, 0.95);
            color: var(--text-brightest);
        }

        .search-box input::placeholder {
            color: var(--text-dim);
        }

        .filter-label {
            color: var(--text-dim);
            margin-right: 0.5rem;
        }

        .gallery-header {
            text-align: center;
            margin-bottom: 3rem;
        }

        .gallery-header h1 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            background: linear-gradient(135deg, var(--primary-lighter), var(--secondary-lighter));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .gallery-header p {
            color: var(--text-dim);
        }

        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 2rem;
            padding: 0 1rem;
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
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }

        .artwork-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
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
            .gallery-header h1 {
                font-size: 2rem;
            }

            .search-filter-container {
                flex-direction: column;
                padding: 0 1rem;
            }

            .gallery-grid {
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
                    <a href="gallery.php" class="active">Galeri</a>
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
            <section class="gallery-header">
                <h1>Galeri Karya</h1>
                <p>Jelajahi dan temukan karya-karya menakjubkan dari seniman berbakat</p>
            </section>

            <div class="search-filter-container">
                <form action="" method="GET" class="search-box">
                    <input type="text" name="search" placeholder="Cari karya seni..." value="<?php echo htmlspecialchars($search); ?>">
                </form>
                <div class="filter-box">
                    <select name="category" onchange="this.form.submit()">
                        <option value="">Semua Kategori</option>
                        <?php foreach($categories as $category): ?>
                            <option value="<?php echo htmlspecialchars($category); ?>" 
                                    <?php echo $category_filter === $category ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($category); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="gallery-grid">
                <?php while($row = $result->fetch_assoc()): ?>
                    <div class="artwork-card">
                        <img src="uploads/<?php echo htmlspecialchars($row['image_path']); ?>" 
                             alt="<?php echo htmlspecialchars($row['title']); ?>" 
                             class="artwork-image">
                        <div class="artwork-info">
                            <h3><?php echo htmlspecialchars($row['title']); ?></h3>
                            <p><?php echo htmlspecialchars($row['description']); ?></p>
                            <div class="artwork-meta">
                                <div class="artist">
                                    <i class="fas fa-user"></i>
                                    <span><?php echo htmlspecialchars($row['artist_name']); ?></span>
                                </div>
                                <div class="date">
                                    <?php echo date('d M Y', strtotime($row['created_at'])); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
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

    <script>
        // Auto-submit form when category changes
        document.querySelector('select[name="category"]').addEventListener('change', function() {
            const searchValue = document.querySelector('input[name="search"]').value;
            window.location.href = `gallery.php?search=${encodeURIComponent(searchValue)}&category=${encodeURIComponent(this.value)}`;
        });
    </script>
</body>
</html> 