<?php
login_required();

$search = isset($_GET['search']) ? trim($_GET['search']) : '';

if (!empty($search)) {
    $query = "SELECT * FROM lapangan WHERE nama_lapangan LIKE ? AND status = 'tersedia'";
    $search_term = "%" . $search . "%";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $search_term);
} else {
    $query = "SELECT * FROM lapangan WHERE status = 'tersedia'";
    $stmt = $conn->prepare($query);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<div class="sidebar">
    <a href="?page=dashboard"><i class="bi bi-house"></i> Dashboard</a>
    <a href="?page=lapangan_user" class="active"><i class="bi bi-list"></i> Daftar Lapangan</a>
    <a href="?page=riwayat_reservasi"><i class="bi bi-clock-history"></i> Riwayat Reservasi</a>
    <a href="?page=profil"><i class="bi bi-person"></i> Profil Saya</a>
</div>

<div class="main-content">
    <div class="container-fluid">
        <h1 class="mb-4"><i class="bi bi-list"></i> Daftar Lapangan Futsal</h1>

        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="d-flex gap-2">
                    <input type="hidden" name="page" value="lapangan_user">
                    <input type="text" class="form-control" name="search" 
                           placeholder="Cari lapangan..." value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i> Cari</button>
                    <?php if (!empty($search)): ?>
                        <a href="?page=lapangan_user" class="btn btn-secondary"><i class="bi bi-arrow-clockwise"></i> Reset</a>
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <div class="row">
           <?php
if ($result->num_rows > 0) {
    while ($lapangan = $result->fetch_assoc()) {
        ?>
        <div class="col-md-6 col-lg-4">
            <div class="lapangan-card">
                <!-- Foto Lapangan -->
                <div style="height: 200px; overflow: hidden;">
                    <?php if (!empty($lapangan['foto_lapangan'])): ?>
                        <img src="uploads/<?php echo htmlspecialchars($lapangan['foto_lapangan']); ?>"
                            alt="<?php echo htmlspecialchars($lapangan['nama_lapangan']); ?>"
                            style="width: 100%; height: 100%; object-fit: cover;">
                    <?php else: ?>
                        <!-- gradient biru + icon -->
                        <div style="width: 100%; height: 100%; background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%); display: flex; align-items: center; justify-content: center; color: white;">
                            <i class="bi bi-image" style="font-size: 3rem;"></i>
                        </div>
                    <?php endif; ?>
                </div>
                <!-- END Foto Lapangan -->

                <!-- Info Lapangan -->
                <div class="lapangan-info">
                    <h5><?php echo htmlspecialchars($lapangan['nama_lapangan']); ?></h5>
                    <p style="color: #7f8c8d; font-size: 0.9rem;">
                        <?php echo htmlspecialchars(substr($lapangan['deskripsi'], 0, 100)) . '...'; ?>
                    </p>
                    <div class="lapangan-price">
                        <?php echo format_rupiah($lapangan['harga_per_jam']); ?>/jam
                    </div>
                    <p style="color: #7f8c8d; margin-bottom: 15px;">
                        <i class="bi bi-people"></i> Kapasitas: <?php echo $lapangan['kapasitas']; ?> orang
                    </p>
                    <a href="?page=booking_lapangan&id=<?php echo $lapangan['id_lapangan']; ?>" class="btn btn-primary w-100">
                        <i class="bi bi-calendar2-check"></i> Pesan Sekarang
                    </a>
                </div>
                <!-- END Info Lapangan -->
            </div>
        </div>
        <?php
    }
} else {
    ?>
    <div class="col-12">
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i> Tidak ada lapangan yang tersedia saat ini.
        </div>
    </div>
    <?php
}
?>

        </div>
    </div>
</div>
