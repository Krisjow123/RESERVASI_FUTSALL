<?php
login_required();

$id_user = $_SESSION['id_user'];
$query = "SELECT * FROM users WHERE id_user = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id_user);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Get stats
$total_reservasi = $conn->query("SELECT COUNT(*) as total FROM reservasi WHERE id_user = $id_user")->fetch_assoc()['total'];
$total_belum_bayar = $conn->query("SELECT COUNT(*) as total FROM transaksi WHERE id_user = $id_user AND status_pembayaran = 'pending'")->fetch_assoc()['total'];
$total_pengeluaran = $conn->query("SELECT COALESCE(SUM(total_bayar), 0) as total FROM transaksi WHERE id_user = $id_user AND status_pembayaran = 'lunas'")->fetch_assoc()['total'];

if ($_SESSION['role'] == 'admin') {
    header("Location: ?page=admin_dashboard");
}
?>

<div class="sidebar">
    <div class="px-4 mb-4 mt-2">
        <h6 class="text-uppercase text-muted small fw-bold letter-spacing-2">User Menu</h6>
    </div>
    <a href="?page=dashboard" class="active"><i class="bi bi-house"></i> Dashboard</a>
    <a href="?page=lapangan_user"><i class="bi bi-calendar-plus"></i> Booking Lapangan</a>
    <a href="?page=riwayat_reservasi"><i class="bi bi-clock-history"></i> Riwayat & Transaksi</a>
    <a href="?page=profil"><i class="bi bi-person-gear"></i> Pengaturan Profil</a>
    <div class="px-4 mt-5 mb-2">
        <h6 class="text-uppercase text-muted small fw-bold letter-spacing-2">Akun</h6>
    </div>
    <a href="?page=logout" class="text-danger"><i class="bi bi-box-arrow-left"></i> Logout</a>
</div>

<div class="main-content">
    <div class="container-fluid p-0">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1">Halo, <?php echo htmlspecialchars(explode(' ', $user['nama_lengkap'])[0]); ?>!</h2>
                <p class="text-muted mb-0">Siap untuk bermain futsal hari ini?</p>
            </div>
            <div>
                <a href="?page=lapangan_user" class="btn btn-custom-login shadow-sm">
                    <i class="bi bi-plus-lg me-2"></i>Booking Baru
                </a>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="stat-card">
                    <i class="bi bi-ticket-perforated icon-bg"></i>
                    <h5>Total Booking</h5>
                    <div class="number text-primary-custom"><?php echo $total_reservasi; ?></div>
                    <small class="text-muted">Kali bermain</small>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <i class="bi bi-exclamation-circle icon-bg"></i>
                    <h5>Menunggu Pembayaran</h5>
                    <div class="number text-warning"><?php echo $total_belum_bayar; ?></div>
                    <small class="text-muted">Transaksi pending</small>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <i class="bi bi-wallet2 icon-bg"></i>
                    <h5>Total Pengeluaran</h5>
                    <div class="number text-light" style="font-size: 1.5rem;">
                        <?php echo format_rupiah($total_pengeluaran); ?></div>
                    <small class="text-muted">Untuk hobi sehatmu</small>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-7 mb-4">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="bi bi-calendar-week me-2 text-primary-custom"></i> Jadwal Main Kamu
                        </h5>
                        <a href="?page=riwayat_reservasi" class="btn btn-sm btn-custom-outline">Lihat Semua</a>
                    </div>
                    <div class="card-body">
                        <?php
                        $query = "SELECT r.*, l.nama_lapangan FROM reservasi r 
                                  JOIN lapangan l ON r.id_lapangan = l.id_lapangan 
                                  WHERE r.id_user = ? ORDER BY r.created_at DESC LIMIT 3";
                        $stmt = $conn->prepare($query);
                        $stmt->bind_param("i", $id_user);
                        $stmt->execute();
                        $result = $stmt->get_result();

                        if ($result->num_rows > 0) {
                            while ($res = $result->fetch_assoc()) {
                                $status_class = 'badge-' . $res['status_reservasi'];
                                ?>
                                <div class="d-flex align-items-center mb-3 pb-3 border-bottom border-secondary"
                                    style="border-bottom-color: rgba(255,255,255,0.05) !important;">
                                    <div class="rounded-3 bg-dark p-3 text-center me-3 border border-secondary"
                                        style="min-width: 80px;">
                                        <div class="fw-bold text-primary-custom" style="font-size: 1.1rem;">
                                            <?php echo date('d', strtotime($res['tanggal_reservasi'])); ?></div>
                                        <div class="small text-muted text-uppercase">
                                            <?php echo date('M', strtotime($res['tanggal_reservasi'])); ?></div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1 text-light"><?php echo htmlspecialchars($res['nama_lapangan']); ?></h6>
                                        <div class="small text-muted mb-2">
                                            <i class="bi bi-clock me-1"></i>
                                            <?php echo $res['jam_mulai'] . " - " . $res['jam_selesai']; ?>
                                        </div>
                                        <span class="<?php echo $status_class; ?> small">
                                            <?php echo ucfirst($res['status_reservasi']); ?>
                                        </span>
                                    </div>
                                    <div>
                                        <a href="?page=riwayat_reservasi" class="btn btn-sm btn-dark rounded-circle">
                                            <i class="bi bi-chevron-right"></i>
                                        </a>
                                    </div>
                                </div>
                                <?php
                            }
                        } else {
                            echo '<div class="text-center py-5">
                                    <i class="bi bi-calendar-x display-4 text-muted mb-3"></i>
                                    <p class="text-muted">Kamu belum punya jadwal main.</p>
                                    <a href="?page=lapangan_user" class="btn btn-sm btn-custom-outline">Booking Sekarang</a>
                                  </div>';
                        }
                        ?>
                    </div>
                </div>
            </div>

            <div class="col-lg-5 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-person-badge me-2 text-info"></i> Profil Saya</h5>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-4">
                            <div class="bg-dark rounded-circle d-inline-flex align-items-center justify-content-center border border-secondary mb-3"
                                style="width: 80px; height: 80px;">
                                <i class="bi bi-person fs-1 text-muted"></i>
                            </div>
                            <h5 class="mb-0"><?php echo htmlspecialchars($user['nama_lengkap']); ?></h5>
                            <small class="text-muted">@<?php echo htmlspecialchars($user['username']); ?></small>
                        </div>

                        <div class="list-group list-group-flush bg-transparent">
                            <div
                                class="list-group-item bg-transparent text-light border-secondary px-0 d-flex justify-content-between">
                                <span class="text-muted"><i class="bi bi-envelope me-2"></i> Email</span>
                                <span><?php echo htmlspecialchars($user['email']); ?></span>
                            </div>
                            <div
                                class="list-group-item bg-transparent text-light border-secondary px-0 d-flex justify-content-between">
                                <span class="text-muted"><i class="bi bi-telephone me-2"></i> Telepon</span>
                                <span><?php echo $user['no_telepon'] ? htmlspecialchars($user['no_telepon']) : '-'; ?></span>
                            </div>
                            <div class="list-group-item bg-transparent text-light border-secondary px-0">
                                <div class="text-muted mb-1"><i class="bi bi-geo-alt me-2"></i> Alamat</div>
                                <div class="small ps-4">
                                    <?php echo $user['alamat'] ? htmlspecialchars($user['alamat']) : '-'; ?></div>
                            </div>
                        </div>

                        <div class="d-grid mt-3">
                            <a href="?page=profil" class="btn btn-dark border-secondary">Edit Profil</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>