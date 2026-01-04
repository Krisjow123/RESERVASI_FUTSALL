<div class="home-container">
    <div class="home-content">
        <h1 style="font-size: 5rem; margin-bottom: 20px;">404</h1>
        <h2 style="margin-bottom: 20px;">Halaman Tidak Ditemukan</h2>
        <p style="font-size: 1.2rem; margin-bottom: 30px;">Maaf, halaman yang Anda cari tidak ada.</p>
        
        <a href="?page=<?php echo isset($_SESSION['id_user']) ? ($_SESSION['role'] == 'admin' ? 'admin_dashboard' : 'dashboard') : 'home'; ?>" class="btn btn-light" style="padding: 12px 30px; font-size: 1.1rem;">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>
</div>
