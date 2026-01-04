<?php
admin_required();

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$action = isset($_GET['action']) ? $_GET['action'] : '';

// Handle Delete
if ($action == 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    // Hapus foto sebelum delete
    $get_foto = "SELECT foto_lapangan FROM lapangan WHERE id_lapangan = ?";
    $stmt_foto = $conn->prepare($get_foto);
    $stmt_foto->bind_param("i", $id);
    $stmt_foto->execute();
    $row_foto = $stmt_foto->get_result()->fetch_assoc();
    if (!empty($row_foto['foto_lapangan'])) {
        delete_file($row_foto['foto_lapangan']);
    }
    
    // Delete record
    $conn->query("DELETE FROM lapangan WHERE id_lapangan = $id");
    header("Location: ?page=admin_lapangan");
    exit();
}

// Handle Add/Edit
$error = '';
$success = '';
$edit_data = null;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_lapangan = isset($_POST['id_lapangan']) ? intval($_POST['id_lapangan']) : 0;
    $nama_lapangan = trim($_POST['nama_lapangan']);
    $deskripsi = trim($_POST['deskripsi']);
    $harga_per_jam = floatval($_POST['harga_per_jam']);
    $kapasitas = intval($_POST['kapasitas']);
    $status = $_POST['status'];
    
    $foto_lapangan = null;

    if (empty($nama_lapangan) || $harga_per_jam <= 0) {
        $error = "Nama lapangan dan harga harus diisi dengan benar!";
    } else {
        
        // HANDLE FILE UPLOAD
        if (isset($_FILES['foto_lapangan']) && $_FILES['foto_lapangan']['size'] > 0) {
            $upload_result = upload_file($_FILES['foto_lapangan'], 'lapangan');
            
            if (is_array($upload_result) && isset($upload_result['error'])) {
                $error = $upload_result['error'];
            } else {
                $foto_lapangan = $upload_result;
            }
        }

        if (empty($error)) {
            if ($id_lapangan > 0) {
                // ===== UPDATE MODE =====
                
                if ($foto_lapangan != null) {
                    // Ada foto baru
                    $get_old = "SELECT foto_lapangan FROM lapangan WHERE id_lapangan = ?";
                    $stmt_old = $conn->prepare($get_old);
                    $stmt_old->bind_param("i", $id_lapangan);
                    $stmt_old->execute();
                    $row_old = $stmt_old->get_result()->fetch_assoc();
                    
                    if (!empty($row_old['foto_lapangan'])) {
                        delete_file($row_old['foto_lapangan']);
                    }
                    
                    // Update dengan foto
                    $query = "UPDATE lapangan SET nama_lapangan = ?, deskripsi = ?, harga_per_jam = ?, kapasitas = ?, status = ?, foto_lapangan = ? WHERE id_lapangan = ?";
                    $stmt = $conn->prepare($query);
                    $stmt->bind_param("ssdissi", $nama_lapangan, $deskripsi, $harga_per_jam, $kapasitas, $status, $foto_lapangan, $id_lapangan);
                } else {
                    // Tidak ada foto baru
                    $query = "UPDATE lapangan SET nama_lapangan = ?, deskripsi = ?, harga_per_jam = ?, kapasitas = ?, status = ? WHERE id_lapangan = ?";
                    $stmt = $conn->prepare($query);
                    $stmt->bind_param("ssdis", $nama_lapangan, $deskripsi, $harga_per_jam, $kapasitas, $status, $id_lapangan);
                }

                if ($stmt->execute()) {
                    $success = "Lapangan berhasil diperbarui!";
                    $_POST = array();
                } else {
                    $error = "Terjadi kesalahan: " . $conn->error;
                }

            } else {
                // ===== INSERT MODE (TAMBAH BARU) =====
                
                $query = "INSERT INTO lapangan (nama_lapangan, deskripsi, harga_per_jam, kapasitas, status, foto_lapangan) VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("ssdiss", $nama_lapangan, $deskripsi, $harga_per_jam, $kapasitas, $status, $foto_lapangan);

                if ($stmt->execute()) {
                    $success = "Lapangan berhasil ditambahkan!";
                    $_POST = array();
                } else {
                    $error = "Terjadi kesalahan: " . $conn->error;
                }
            }
        }
    }
}

// Handle Edit Mode
if ($action == 'edit' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $query = "SELECT * FROM lapangan WHERE id_lapangan = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $edit_data = $stmt->get_result()->fetch_assoc();
}

// Get List
if (!empty($search)) {
    $query = "SELECT * FROM lapangan WHERE nama_lapangan LIKE ? ORDER BY id_lapangan DESC";
    $search_term = "%" . $search . "%";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $search_term);
} else {
    $query = "SELECT * FROM lapangan ORDER BY id_lapangan DESC";
    $stmt = $conn->prepare($query);
}

$stmt->execute();
$result = $stmt->get_result();
?>


<div class="sidebar">
    <a href="?page=admin_dashboard"><i class="bi bi-house"></i> Dashboard</a>
    <a href="?page=admin_lapangan" class="active"><i class="bi bi-list"></i> Lapangan</a>
    <a href="?page=admin_reservasi"><i class="bi bi-calendar"></i> Reservasi</a>
    <a href="?page=admin_transaksi"><i class="bi bi-receipt"></i> Transaksi</a>
    <a href="?page=admin_users"><i class="bi bi-people"></i> Users</a>
</div>

<div class="main-content">
    <div class="container-fluid">
        <h1 class="mb-4"><i class="bi bi-list"></i> Kelola Lapangan</h1>

        <div class="row">
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="d-flex gap-2">
                            <input type="hidden" name="page" value="admin_lapangan">
                            <input type="text" class="form-control" name="search" 
                                   placeholder="Cari lapangan..." value="<?php echo htmlspecialchars($search); ?>">
                            <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i> Cari</button>
                            <?php if (!empty($search)): ?>
                                <a href="?page=admin_lapangan" class="btn btn-secondary"><i class="bi bi-arrow-clockwise"></i> Reset</a>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <?php if (!empty($success)): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>

                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>Nama Lapangan</th>
                                <th>Harga/Jam</th>
                                <th>Kapasitas</th>
                                <th>Status</th>
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
                                        <td><?php echo htmlspecialchars($row['nama_lapangan']); ?></td>
                                        <td><?php echo format_rupiah($row['harga_per_jam']); ?></td>
                                        <td><?php echo $row['kapasitas']; ?> orang</td>
                                        <td>
                                            <span class="badge bg-<?php echo $row['status'] == 'tersedia' ? 'success' : 'warning'; ?>">
                                                <?php echo ucfirst($row['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="?page=admin_lapangan&action=edit&id=<?php echo $row['id_lapangan']; ?>" class="btn btn-sm btn-warning">
                                                <i class="bi bi-pencil"></i> Edit
                                            </a>
                                            <a href="?page=admin_lapangan&action=delete&id=<?php echo $row['id_lapangan']; ?>" 
                                               class="btn btn-sm btn-danger" onclick="return confirmDelete('<?php echo htmlspecialchars($row['nama_lapangan']); ?>')">
                                                <i class="bi bi-trash"></i> Hapus
                                            </a>
                                        </td>
                                    </tr>
                                    <?php
                                }
                            } else {
                                ?>
                                <tr>
                                    <td colspan="6" class="text-center">Tidak ada lapangan</td>
                                </tr>
                                <?php
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5><?php echo $edit_data ? 'Edit Lapangan' : 'Tambah Lapangan'; ?></h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data">
                            <?php if ($edit_data): ?>
                                <input type="hidden" name="id_lapangan" value="<?php echo $edit_data['id_lapangan']; ?>">
                            <?php endif; ?>

                            <div class="mb-3">
                                <label for="nama_lapangan" class="form-label">Nama Lapangan</label>
                                <input type="text" class="form-control" id="nama_lapangan" name="nama_lapangan" 
                                    value="<?php echo $edit_data ? htmlspecialchars($edit_data['nama_lapangan']) : (isset($_POST['nama_lapangan']) ? htmlspecialchars($_POST['nama_lapangan']) : ''); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="deskripsi" class="form-label">Deskripsi</label>
                                <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3"><?php echo $edit_data ? htmlspecialchars($edit_data['deskripsi']) : (isset($_POST['deskripsi']) ? htmlspecialchars($_POST['deskripsi']) : ''); ?></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="foto_lapangan" class="form-label">Foto Lapangan</label>
                                <div style="margin-bottom: 15px;">
                                    <?php if ($edit_data && !empty($edit_data['foto_lapangan'])): ?>
                                        <img src="<?php echo get_file_url($edit_data['foto_lapangan']); ?>" alt="Foto Lapangan" style="width: 100%; max-width: 200px; height: 150px; border-radius: 8px; object-fit: cover; border: 2px solid #ddd;">
                                    <?php else: ?>
                                        <div style="width: 100%; max-width: 200px; height: 150px; border-radius: 8px; background: var(--light-bg); display: flex; align-items: center; justify-content: center;">
                                            <i class="bi bi-image" style="font-size: 2rem; color: #7f8c8d;"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <input type="file" class="form-control" id="foto_lapangan" name="foto_lapangan" accept="image/*">
                                <small class="text-muted">Format: JPG, PNG, GIF (Max 5MB)</small>
                            </div>

                            <div class="mb-3">
                                <label for="harga_per_jam" class="form-label">Harga/Jam (Rp)</label>
                                <input type="number" class="form-control" id="harga_per_jam" name="harga_per_jam" step="1000"
                                    value="<?php echo $edit_data ? $edit_data['harga_per_jam'] : (isset($_POST['harga_per_jam']) ? $_POST['harga_per_jam'] : ''); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="kapasitas" class="form-label">Kapasitas (Orang)</label>
                                <input type="number" class="form-control" id="kapasitas" name="kapasitas"
                                    value="<?php echo $edit_data ? $edit_data['kapasitas'] : (isset($_POST['kapasitas']) ? $_POST['kapasitas'] : '10'); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-control" id="status" name="status">
                                    <option value="tersedia" <?php echo ($edit_data && $edit_data['status'] == 'tersedia') ? 'selected' : ''; ?>>Tersedia</option>
                                    <option value="maintenance" <?php echo ($edit_data && $edit_data['status'] == 'maintenance') ? 'selected' : ''; ?>>Maintenance</option>
                                    <option value="nonaktif" <?php echo ($edit_data && $edit_data['status'] == 'nonaktif') ? 'selected' : ''; ?>>Nonaktif</option>
                                </select>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle"></i> <?php echo $edit_data ? 'Perbarui' : 'Tambah'; ?>
                                </button>
                                <?php if ($edit_data): ?>
                                    <a href="?page=admin_lapangan" class="btn btn-secondary">Batal</a>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
