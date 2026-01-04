<?php
admin_required();

// Get statistics
$total_users = $conn->query("SELECT COUNT(*) as total FROM users WHERE role = 'user'")->fetch_assoc()['total'];
$total_lapangan = $conn->query("SELECT COUNT(*) as total FROM lapangan")->fetch_assoc()['total'];
$total_reservasi = $conn->query("SELECT COUNT(*) as total FROM reservasi")->fetch_assoc()['total'];
$total_transaksi = $conn->query("SELECT COALESCE(SUM(total_bayar), 0) as total FROM transaksi WHERE status_pembayaran = 'lunas'")->fetch_assoc()['total'];

// Get recent reservasi
$recent_query = "SELECT r.*, l.nama_lapangan, u.nama_lengkap FROM reservasi r 
                 JOIN lapangan l ON r.id_lapangan = l.id_lapangan 
                 JOIN users u ON r.id_user = u.id_user 
                 ORDER BY r.created_at DESC LIMIT 5";
$recent_result = $conn->query($recent_query);
?>

<div class="sidebar">
    <div class="px-4 mb-4 mt-2">
        <h6 class="text-uppercase text-muted small fw-bold letter-spacing-2">Admin Menu</h6>
    </div>
    <a href="?page=admin_dashboard" class="active"><i class="bi bi-speedometer2"></i> Dashboard</a>
    <a href="?page=admin_lapangan"><i class="bi bi-grid"></i> Data Lapangan</a>
    <a href="?page=admin_reservasi"><i class="bi bi-calendar-check"></i> Data Reservasi</a>
    <a href="?page=admin_transaksi"><i class="bi bi-receipt"></i> Laporan Keuangan</a>
    <a href="?page=admin_users"><i class="bi bi-people"></i> Data Users</a>
    <div class="px-4 mt-5 mb-2">
        <h6 class="text-uppercase text-muted small fw-bold letter-spacing-2">Akun</h6>
    </div>
    <a href="?page=logout" class="text-danger"><i class="bi bi-box-arrow-left"></i> Logout</a>
</div>

<div class="main-content">
    <div class="container-fluid p-0">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1">Dashboard Overview</h2>
                <p class="text-muted mb-0">Selamat datang kembali, admin!</p>
            </div>
            <div>
                <span class="badge bg-dark border border-secondary p-2 px-3"><?php echo date('d F Y'); ?></span>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-6 col-lg-3">
                <div class="stat-card">
                    <i class="bi bi-people icon-bg"></i>
                    <h5>Total User</h5>
                    <div class="number text-primary-custom"><?php echo $total_users; ?></div>
                    <small class="text-muted">User terdaftar</small>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="stat-card">
                    <i class="bi bi-grid icon-bg"></i>
                    <h5>Total Lapangan</h5>
                    <div class="number text-info"><?php echo $total_lapangan; ?></div>
                    <small class="text-muted">Arena tersedia</small>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="stat-card">
                    <i class="bi bi-calendar-check icon-bg"></i>
                    <h5>Reservasi</h5>
                    <div class="number text-warning"><?php echo $total_reservasi; ?></div>
                    <small class="text-muted">Total booking</small>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="stat-card">
                    <i class="bi bi-wallet2 icon-bg"></i>
                    <h5>Pendapatan</h5>
                    <div class="number text-success" style="font-size: 1.5rem;">
                        <?php echo format_rupiah($total_transaksi); ?></div>
                    <small class="text-muted">Total pemasukan</small>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8 mb-4">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="bi bi-clock-history me-2 text-primary-custom"></i> Reservasi Terbaru
                        </h5>
                        <a href="?page=admin_reservasi" class="btn btn-sm btn-custom-outline">Lihat Semua</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Lapangan</th>
                                    <th>Tanggal</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($recent_result->num_rows > 0): ?>
                                    <?php while ($row = $recent_result->fetch_assoc()):
                                        $status_class = 'badge-' . $row['status_reservasi'];
                                        ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="bg-dark rounded-circle d-flex align-items-center justify-content-center me-2"
                                                        style="width: 35px; height: 35px; border: 1px solid #333;">
                                                        <i class="bi bi-person text-muted"></i>
                                                    </div>
                                                    <span
                                                        class="fw-medium"><?php echo htmlspecialchars($row['nama_lengkap']); ?></span>
                                                </div>
                                            </td>
                                            <td><?php echo htmlspecialchars($row['nama_lapangan']); ?></td>
                                            <td class="small text-muted"><?php echo format_date($row['tanggal_reservasi']); ?>
                                            </td>
                                            <td>
                                                <span class="<?php echo $status_class; ?>">
                                                    <?php echo ucfirst($row['status_reservasi']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="?page=admin_reservasi" class="btn btn-sm btn-dark border-secondary">
                                                    <i class="bi bi-arrow-right"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">Belum ada data reservasi</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-lightning-charge me-2 text-warning"></i> Aksi Cepat</h5>
                    </div>
                    <div class="card-body d-grid gap-3">
                        <a href="?page=admin_lapangan"
                            class="btn btn-dark border-secondary text-start p-3 hover-effect">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-plus-circle fs-4 text-primary-custom me-3"></i>
                                <div>
                                    <div class="fw-bold">Tambah Lapangan</div>
                                    <small class="text-muted">Input arena baru</small>
                                </div>
                            </div>
                        </a>
                        <a href="?page=admin_reservasi"
                            class="btn btn-dark border-secondary text-start p-3 hover-effect">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-check2-circle fs-4 text-warning me-3"></i>
                                <div>
                                    <div class="fw-bold">Verifikasi Reservasi</div>
                                    <small class="text-muted">Cek booking pending</small>
                                </div>
                            </div>
                        </a>
                        <a href="?page=admin_transaksi"
                            class="btn btn-dark border-secondary text-start p-3 hover-effect">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-file-earmark-text fs-4 text-info me-3"></i>
                                <div>
                                    <div class="fw-bold">Rekap Laporan</div>
                                    <small class="text-muted">Download data transaksi</small>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .hover-effect:hover {
        background-color: rgba(255, 255, 255, 0.05);
        border-color: var(--primary-color) !important;
    }
</style>