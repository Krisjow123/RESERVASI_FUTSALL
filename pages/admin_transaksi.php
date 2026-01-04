<?php
admin_required();

$filter_month = isset($_GET['month']) ? $_GET['month'] : date('Y-m');
$filter_status = isset($_GET['status']) ? $_GET['status'] : '';

// Build query
$query = "SELECT t.*, u.nama_lengkap, l.nama_lapangan FROM transaksi t 
          JOIN users u ON t.id_user = u.id_user 
          JOIN lapangan l ON t.id_lapangan = l.id_lapangan 
          WHERE DATE_FORMAT(t.tanggal_transaksi, '%Y-%m') = ?";

$params = [$filter_month];
$types = 's';

if (!empty($filter_status)) {
    $query .= " AND t.status_pembayaran = ?";
    $params[] = $filter_status;
    $types .= 's';
}

$query .= " ORDER BY t.tanggal_transaksi DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

// Get summary
$summary_query = "SELECT 
                    COUNT(*) as total_transaksi,
                    SUM(total_bayar) as total_pendapatan,
                    SUM(CASE WHEN status_pembayaran = 'lunas' THEN total_bayar ELSE 0 END) as pendapatan_lunas,
                    SUM(CASE WHEN status_pembayaran = 'pending' THEN total_bayar ELSE 0 END) as pendapatan_pending,
                    SUM(CASE WHEN status_pembayaran = 'terlambat' THEN total_bayar ELSE 0 END) as pendapatan_terlambat
                  FROM transaksi 
                  WHERE DATE_FORMAT(tanggal_transaksi, '%Y-%m') = ?";

$summary_stmt = $conn->prepare($summary_query);
$summary_stmt->bind_param('s', $filter_month);
$summary_stmt->execute();
$summary = $summary_stmt->get_result()->fetch_assoc();
?>

<div class="sidebar">
    <a href="?page=admin_dashboard"><i class="bi bi-house"></i> Dashboard</a>
    <a href="?page=admin_lapangan"><i class="bi bi-list"></i> Lapangan</a>
    <a href="?page=admin_reservasi"><i class="bi bi-calendar"></i> Reservasi</a>
    <a href="?page=admin_transaksi" class="active"><i class="bi bi-receipt"></i> Transaksi</a>
    <a href="?page=admin_users"><i class="bi bi-people"></i> Users</a>
</div>

<div class="main-content">
    <div class="container-fluid">
        <h1 class="mb-4"><i class="bi bi-receipt"></i> Laporan Transaksi</h1>

        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stat-card">
                    <h5>Total Transaksi</h5>
                    <div class="number"><?php echo $summary['total_transaksi'] ?? 0; ?></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <h5>Total Pendapatan</h5>
                    <div class="number" style="font-size: 1.1rem;"><?php echo format_rupiah($summary['total_pendapatan'] ?? 0); ?></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <h5>Lunas</h5>
                    <div class="number"><?php echo format_rupiah($summary['pendapatan_lunas'] ?? 0); ?></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <h5>Pending</h5>
                    <div class="number"><?php echo format_rupiah($summary['pendapatan_pending'] ?? 0); ?></div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="d-flex gap-2 flex-wrap">
                    <input type="hidden" name="page" value="admin_transaksi">
                    
                    <input type="month" class="form-control" name="month" value="<?php echo $filter_month; ?>" style="width: auto;">
                    
                    <select class="form-control" name="status" style="width: auto;">
                        <option value="">Semua Status</option>
                        <option value="lunas" <?php echo $filter_status === 'lunas' ? 'selected' : ''; ?>>Lunas</option>
                        <option value="pending" <?php echo $filter_status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="terlambat" <?php echo $filter_status === 'terlambat' ? 'selected' : ''; ?>>Terlambat</option>
                    </select>
                    
                    <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i> Filter</button>
                </form>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>User</th>
                        <th>Lapangan</th>
                        <th>Tanggal Transaksi</th>
                        <th>Tanggal Reservasi</th>
                        <th>Jam</th>
                        <th>Total Bayar</th>
                        <th>Metode</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result->num_rows > 0) {
                        $no = 1;
                        while ($row = $result->fetch_assoc()) {
                            ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td><?php echo htmlspecialchars($row['nama_lengkap']); ?></td>
                                <td><?php echo htmlspecialchars($row['nama_lapangan']); ?></td>
                                <td><?php echo date('d-m-Y H:i', strtotime($row['tanggal_transaksi'])); ?></td>
                                <td><?php echo format_date($row['tanggal_reservasi']); ?></td>
                                <td><?php echo $row['jam_mulai'] . " - " . $row['jam_selesai']; ?></td>
                                <td><?php echo format_rupiah($row['total_bayar']); ?></td>
                                <td><?php echo ucfirst(str_replace('_', ' ', $row['metode_pembayaran'])); ?></td>
                                <td>
                                <div class="dropdown">
                                    <button class="btn btn-sm badge bg-<?php echo $row['status_pembayaran'] == 'lunas' ? 'success' : ($row['status_pembayaran'] == 'pending' ? 'warning' : 'danger'); ?> dropdown-toggle" type="button" data-bs-toggle="dropdown" style="border: none; cursor: pointer;">
                                        <?php echo ucfirst($row['status_pembayaran']); ?>
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="?page=admin_update_transaksi&action=update_status&id=<?php echo $row['id_transaksi']; ?>&new_status=pending">
                                            <i class="bi bi-clock"></i> Pending
                                        </a></li>
                                        <li><a class="dropdown-item" href="?page=admin_update_transaksi&action=update_status&id=<?php echo $row['id_transaksi']; ?>&new_status=lunas">
                                            <i class="bi bi-check-circle"></i> Lunas
                                        </a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item text-danger" href="?page=admin_update_transaksi&action=update_status&id=<?php echo $row['id_transaksi']; ?>&new_status=terlambat">
                                            <i class="bi bi-exclamation-circle"></i> Terlambat
                                        </a></li>
                                    </ul>
                                </div>
                            </td>
                            </tr>
                            <?php
                        }
                    } else {
                        ?>
                        <tr>
                            <td colspan="9" class="text-center">Tidak ada transaksi</td>
                        </tr>
                        <?php
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
