<?php
admin_required();

$filter_status = isset($_GET['status']) ? $_GET['status'] : '';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Handle Status Update
if (isset($_GET['action']) && $_GET['action'] == 'update_status' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $status = $_GET['new_status'];
    $valid_statuses = ['pending', 'dikonfirmasi', 'selesai', 'dibatalkan'];
    
    if (in_array($status, $valid_statuses)) {
        $conn->query("UPDATE reservasi SET status_reservasi = '$status' WHERE id_reservasi = $id");
    }
    header("Location: ?page=admin_reservasi");
    exit();
}

// Build query
$query = "SELECT r.*, l.nama_lapangan, u.nama_lengkap, u.no_telepon FROM reservasi r 
          JOIN lapangan l ON r.id_lapangan = l.id_lapangan 
          JOIN users u ON r.id_user = u.id_user WHERE 1=1";

if (!empty($filter_status)) {
    $query .= " AND r.status_reservasi = '$filter_status'";
}

if (!empty($search)) {
    $query .= " AND (l.nama_lapangan LIKE '%$search%' OR u.nama_lengkap LIKE '%$search%')";
}

$query .= " ORDER BY r.tanggal_reservasi DESC";
$result = $conn->query($query);
?>

<div class="sidebar">
    <a href="?page=admin_dashboard"><i class="bi bi-house"></i> Dashboard</a>
    <a href="?page=admin_lapangan"><i class="bi bi-list"></i> Lapangan</a>
    <a href="?page=admin_reservasi" class="active"><i class="bi bi-calendar"></i> Reservasi</a>
    <a href="?page=admin_transaksi"><i class="bi bi-receipt"></i> Transaksi</a>
    <a href="?page=admin_users"><i class="bi bi-people"></i> Users</a>
</div>

<div class="main-content">
    <div class="container-fluid">
        <h1 class="mb-4"><i class="bi bi-calendar"></i> Kelola Reservasi</h1>

        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="d-flex gap-2 flex-wrap">
                    <input type="hidden" name="page" value="admin_reservasi">
                    <input type="text" class="form-control" style="flex: 1; min-width: 200px;" name="search" 
                           placeholder="Cari lapangan atau user..." value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i> Cari</button>
                </form>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                    <a href="?page=admin_reservasi" class="btn <?php echo empty($filter_status) ? 'btn-primary' : 'btn-outline-primary'; ?>">
                        <i class="bi bi-list"></i> Semua
                    </a>
                    <a href="?page=admin_reservasi&status=pending" class="btn <?php echo $filter_status === 'pending' ? 'btn-primary' : 'btn-outline-primary'; ?>">
                        <i class="bi bi-clock"></i> Pending
                    </a>
                    <a href="?page=admin_reservasi&status=dikonfirmasi" class="btn <?php echo $filter_status === 'dikonfirmasi' ? 'btn-primary' : 'btn-outline-primary'; ?>">
                        <i class="bi bi-check"></i> Dikonfirmasi
                    </a>
                    <a href="?page=admin_reservasi&status=selesai" class="btn <?php echo $filter_status === 'selesai' ? 'btn-primary' : 'btn-outline-primary'; ?>">
                        <i class="bi bi-check-circle"></i> Selesai
                    </a>
                    <a href="?page=admin_reservasi&status=dibatalkan" class="btn <?php echo $filter_status === 'dibatalkan' ? 'btn-danger' : 'btn-outline-danger'; ?>">
                        <i class="bi bi-x-circle"></i> Dibatalkan
                    </a>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>User</th>
                        <th>Lapangan</th>
                        <th>Tanggal</th>
                        <th>Jam</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result->num_rows > 0) {
                        $no = 1;
                        while ($row = $result->fetch_assoc()) {
                            $status_class = 'badge-' . $row['status_reservasi'];
                            ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td><?php echo htmlspecialchars($row['nama_lengkap']); ?></td>
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
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                            <i class="bi bi-pencil"></i> Update
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item" href="?page=admin_reservasi&action=update_status&id=<?php echo $row['id_reservasi']; ?>&new_status=pending">Pending</a></li>
                                            <li><a class="dropdown-item" href="?page=admin_reservasi&action=update_status&id=<?php echo $row['id_reservasi']; ?>&new_status=dikonfirmasi">Dikonfirmasi</a></li>
                                            <li><a class="dropdown-item" href="?page=admin_reservasi&action=update_status&id=<?php echo $row['id_reservasi']; ?>&new_status=selesai">Selesai</a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li><a class="dropdown-item text-danger" href="?page=admin_reservasi&action=update_status&id=<?php echo $row['id_reservasi']; ?>&new_status=dibatalkan">Batalkan</a></li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                            <?php
                        }
                    } else {
                        ?>
                        <tr>
                            <td colspan="8" class="text-center">Tidak ada reservasi</td>
                        </tr>
                        <?php
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
