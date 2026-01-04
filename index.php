<?php
session_start();
require_once 'config.php';

$page = isset($_GET['page']) ? $_GET['page'] : 'home';
$page_file = "pages/$page.php";

// Judul halaman dinamis
$page_title = ucfirst(str_replace('_', ' ', $page)) . " - Futsal Reservation";
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>

    <!-- Google Fonts: Poppins (Clean & Modern) -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="style.css">
</head>

<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">
                <i class="bi bi-heptagon-half"></i> FUTSAL<span class="text-primary-custom">ZONE</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link <?php echo $page == 'home' ? 'active' : ''; ?>" href="index.php">Beranda</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#fasilitas">Fasilitas</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#lokasi">Lokasi</a>
                    </li>
                    <?php if (isset($_SESSION['id_user'])): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle btn-custom-outline px-4 ms-2" href="#" role="button" data-bs-toggle="dropdown">
                                Hai, <?php echo explode(' ', $_SESSION['nama_lengkap'])[0]; ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <?php if ($_SESSION['role'] == 'admin'): ?>
                                    <li><a class="dropdown-item" href="?page=admin_dashboard"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
                                <?php else: ?>
                                    <li><a class="dropdown-item" href="?page=dashboard"><i class="bi bi-grid-fill"></i> Dashboard</a></li>
                                    <li><a class="dropdown-item" href="?page=riwayat_reservasi"><i class="bi bi-clock-history"></i> Riwayat</a></li>
                                <?php endif; ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="?page=logout"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item ms-lg-2">
                            <a class="nav-link btn btn-custom-login px-4 text-white" href="?page=login">Masuk</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main>
        <?php
        if (file_exists($page_file)) {
            include $page_file;
        } else {
            include 'pages/404.php';
        }
        ?>
    </main>

    <!-- Footer -->
    <footer class="footer mt-auto py-4">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <h5 class="fw-bold text-white mb-3"><i class="bi bi-heptagon-half"></i> FUTSAL<span class="text-primary-custom">ZONE</span></h5>
                    <p class="text-muted small">
                        Tempat terbaik untuk menyalurkan hobi futsal Anda. 
                        Fasilitas lengkap, lapangan berkualitas standar internasional, 
                        dan harga yang terjangkau.
                    </p>
                </div>
                <div class="col-lg-4 mb-4">
                    <h5 class="fw-bold text-white mb-3">Kontak Kami</h5>
                    <ul class="list-unstyled text-muted small">
                        <li class="mb-2"><i class="bi bi-geo-alt me-2"></i> Jl. Stadion Baru No. 123, Kota Sport</li>
                        <li class="mb-2"><i class="bi bi-whatsapp me-2"></i> +62 812-3456-7890</li>
                        <li class="mb-2"><i class="bi bi-envelope me-2"></i> info@futsalzone.com</li>
                    </ul>
                </div>
                <div class="col-lg-4 mb-4">
                    <h5 class="fw-bold text-white mb-3">Ikuti Kami</h5>
                    <div class="d-flex gap-3">
                        <a href="#" class="social-link"><i class="bi bi-instagram"></i></a>
                        <a href="#" class="social-link"><i class="bi bi-facebook"></i></a>
                        <a href="#" class="social-link"><i class="bi bi-twitter-x"></i></a>
                    </div>
                </div>
            </div>
            <hr class="border-secondary">
            <div class="text-center text-muted small">
                &copy; <?php echo date('Y'); ?> FutsalZone. All rights reserved.
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Script untuk Navbar scroll effect -->
    <script>
        window.addEventListener('scroll', function() {
            if (window.scrollY > 50) {
                document.querySelector('.navbar').classList.add('scrolled');
            } else {
                document.querySelector('.navbar').classList.remove('scrolled');
            }
        });
    </script>
</body>
</html>
