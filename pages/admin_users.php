<?php
admin_required();

$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Handle Delete
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $conn->query("DELETE FROM users WHERE id_user = $id AND role = 'user'");
    header("Location: ?page=admin_users");
    exit();
}

// Get List
if (!empty($search)) {
    $query = "SELECT * FROM users WHERE role = 'user' AND (nama_lengkap LIKE ? OR email LIKE ? OR username LIKE ?) ORDER BY id_user DESC";
    $search_term = "%" . $search . "%";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sss", $search_term, $search_term, $search_term);
} else {
    $query = "SELECT * FROM users WHERE role = 'user' ORDER BY id_user DESC";
    $stmt = $conn->prepare($query);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<div class="sidebar">
    <a href="?page=admin_dashboard"><i class="bi bi-house"></i> Dashboard</a>
    <a href="?page=admin_lapangan"><i class="bi bi-list"></i> Lapangan</a>
    <a href="?page=admin_reservasi"><i class="bi bi-calendar"></i> Reservasi</a>
    <a href="?page=admin_transaksi"><i class="bi bi-receipt"></i> Transaksi</a>
    <a href="?page=admin_users" class="active"><i class="bi bi-people"></i> Users</a>
</div>

<div class="main-content">
    <div class="container-fluid">
        <h1 class="mb-4"><i class="bi bi-people"></i> Kelola Users</h1>

        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="d-flex gap-2">
                    <input type="hidden" name="page" value="admin_users">
                    <input type="text" class="form-control" name="search" 
                           placeholder="Cari nama, email, atau username..." value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i> Cari</button>
                    <?php if (!empty($search)): ?>
                        <a href="?page=admin_users" class="btn btn-secondary"><i class="bi bi-arrow-clockwise"></i> Reset</a>
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Nama</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>No. Telepon</th>
                        <th>Status</th>
                        <th>Terdaftar</th>
                        <th>Aksi</th>
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
                                <td><?php echo htmlspecialchars($row['username']); ?></td>
                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                <td><?php echo htmlspecialchars($row['no_telepon']); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $row['status'] == 'aktif' ? 'success' : 'danger'; ?>">
                                        <?php echo ucfirst($row['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo format_date(date('Y-m-d', strtotime($row['created_at']))); ?></td>
                                <td>
                                    <a href="?page=admin_users&action=delete&id=<?php echo $row['id_user']; ?>" 
                                       class="btn btn-sm btn-danger" onclick="return confirmDelete('<?php echo htmlspecialchars($row['nama_lengkap']); ?>')">
                                        <i class="bi bi-trash"></i> Hapus
                                    </a>
                                </td>
                            </tr>
                            <?php
                        }
                    } else {
                        ?>
                        <tr>
                            <td colspan="8" class="text-center">Tidak ada user</td>
                        </tr>
                        <?php
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
