<?php
login_required();

$filter_status = isset($_GET['status']) ? $_GET['status'] : '';

$id_user = $_SESSION['id_user'];

if (!empty($filter_status)) {
    $query = "SELECT r.*, l.nama_lapangan, t.metode_pembayaran, t.status_pembayaran 
              FROM reservasi r 
              JOIN lapangan l ON r.id_lapangan = l.id_lapangan 
              LEFT JOIN transaksi t ON r.id_reservasi = t.id_reservasi
              WHERE r.id_user = ? AND r.status_reservasi = ? 
              ORDER BY r.tanggal_reservasi DESC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("is", $id_user, $filter_status);
} else {
    $query = "SELECT r.*, l.nama_lapangan, t.metode_pembayaran, t.status_pembayaran 
              FROM reservasi r 
              JOIN lapangan l ON r.id_lapangan = l.id_lapangan 
              LEFT JOIN transaksi t ON r.id_reservasi = t.id_reservasi
              WHERE r.id_user = ? 
              ORDER BY r.tanggal_reservasi DESC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id_user);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<div class="sidebar">
    <a href="?page=dashboard"><i class="bi bi-house"></i> Dashboard</a>
    <a href="?page=lapangan_user"><i class="bi bi-list"></i> Daftar Lapangan</a>
    <a href="?page=riwayat_reservasi" class="active"><i class="bi bi-clock-history"></i> Riwayat Reservasi</a>
    <a href="?page=profil"><i class="bi bi-person"></i> Profil Saya</a>
</div>

<div class="main-content">
    <div class="container-fluid">
        <h1 class="mb-4"><i class="bi bi-clock-history"></i> Riwayat Reservasi</h1>

        <div class="card mb-4">
            <div class="card-body">
                <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                    <a href="?page=riwayat_reservasi" class="btn <?php echo empty($filter_status) ? 'btn-primary' : 'btn-outline-primary'; ?>">
                        <i class="bi bi-list"></i> Semua
                    </a>
                    <a href="?page=riwayat_reservasi&status=pending" class="btn <?php echo $filter_status === 'pending' ? 'btn-primary' : 'btn-outline-primary'; ?>">
                        <i class="bi bi-clock"></i> Pending
                    </a>
                    <a href="?page=riwayat_reservasi&status=dikonfirmasi" class="btn <?php echo $filter_status === 'dikonfirmasi' ? 'btn-primary' : 'btn-outline-primary'; ?>">
                        <i class="bi bi-check"></i> Dikonfirmasi
                    </a>
                    <a href="?page=riwayat_reservasi&status=selesai" class="btn <?php echo $filter_status === 'selesai' ? 'btn-primary' : 'btn-outline-primary'; ?>">
                        <i class="bi bi-check-circle"></i> Selesai
                    </a>
                    <a href="?page=riwayat_reservasi&status=dibatalkan" class="btn <?php echo $filter_status === 'dibatalkan' ? 'btn-danger' : 'btn-outline-danger'; ?>">
                        <i class="bi bi-x-circle"></i> Dibatalkan
                    </a>
                </div>
            </div>
        </div>

        <?php
        if ($result->num_rows > 0) {
            ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Lapangan</th>
                            <th>Tanggal</th>
                            <th>Jam</th>
                            <th>Total</th>
                            <th>Status Reservasi</th>
                            <th>Pembayaran</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        while ($row = $result->fetch_assoc()) {
                            $status_class = 'badge-' . $row['status_reservasi'];
                            ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td><?php echo htmlspecialchars($row['nama_lapangan']); ?></td>
                                <td><?php echo format_date($row['tanggal_reservasi']); ?></td>
                                <td><?php echo $row['jam_mulai'] . " - " . $row['jam_selesai']; ?></td>
                                <td><?php echo format_rupiah($row['total_harga']); ?></td>
                                <td>
                                    <span class="<?php echo $status_class; ?>">
                                        <?php echo ucfirst($row['status_reservasi']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if (!empty($row['status_pembayaran'])): ?>
                                        <span class="badge bg-<?php echo $row['status_pembayaran'] == 'lunas' ? 'success' : 'warning'; ?>">
                                            <?php echo ucfirst($row['status_pembayaran']); ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="?page=detail_transaksi&id=<?php echo $row['id_reservasi']; ?>" class="btn btn-sm btn-info">
                                    <i class="bi bi-eye"></i> Detail
                                    </a>

                                </td>
                            </tr>
                            <?php
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <?php
        } else {
            ?>
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> Anda belum memiliki reservasi.
                <a href="?page=lapangan_user">Mulai pesan lapangan sekarang!</a>
            </div>
            <?php
        }
        ?>
    </div>
</div>
