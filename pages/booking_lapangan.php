<?php
login_required();

$id_lapangan = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id_lapangan == 0) {
    header("Location: ?page=lapangan_user");
    exit();
}

// Get lapangan info
$query = "SELECT * FROM lapangan WHERE id_lapangan = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id_lapangan);
$stmt->execute();
$lapangan = $stmt->get_result()->fetch_assoc();

if (!$lapangan) {
    header("Location: ?page=lapangan_user");
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $tanggal_reservasi = $_POST['tanggal_reservasi'];
    $jam_mulai = $_POST['jam_mulai'];
    $jam_selesai = $_POST['jam_selesai'];
    $catatan = trim($_POST['catatan']);

    // Validasi tanggal
    if (strtotime($tanggal_reservasi) < strtotime(date('Y-m-d'))) {
        $error = "Tanggal tidak boleh kurang dari hari ini!";
    } elseif ($jam_mulai >= $jam_selesai) {
        $error = "Jam selesai harus lebih besar dari jam mulai!";
    } else {
        // Check conflict
        $query = "SELECT COUNT(*) as total FROM reservasi 
                  WHERE id_lapangan = ? AND tanggal_reservasi = ? 
                  AND status_reservasi != 'dibatalkan'
                  AND (
                    (jam_mulai < ? AND jam_selesai > ?) OR
                    (jam_mulai < ? AND jam_selesai > ?) OR
                    (jam_mulai >= ? AND jam_selesai <= ?)
                  )";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("isssssss", $id_lapangan, $tanggal_reservasi, $jam_selesai, $jam_mulai, $jam_mulai, $jam_selesai, $jam_mulai, $jam_selesai);
        $stmt->execute();
        $conflict = $stmt->get_result()->fetch_assoc()['total'];

        if ($conflict > 0) {
            $error = "Lapangan sudah dipesan pada jam tersebut!";
        } else {
            // Calculate duration
            $start = new DateTime($jam_mulai);
            $end = new DateTime($jam_selesai);
            $interval = $start->diff($end);
            $durasi_jam = $interval->h + ($interval->i / 60);
            $total_harga = $durasi_jam * $lapangan['harga_per_jam'];

            // Insert reservasi
            $query = "INSERT INTO reservasi (id_user, id_lapangan, tanggal_reservasi, jam_mulai, jam_selesai, durasi_jam, total_harga, status_reservasi, catatan) 
                      VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', ?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("iisssdds", $_SESSION['id_user'], $id_lapangan, $tanggal_reservasi, $jam_mulai, $jam_selesai, $durasi_jam, $total_harga, $catatan);

            if ($stmt->execute()) {
                $id_reservasi = $conn->insert_id;
                header("Location: ?page=checkout&id=" . $id_reservasi);
                exit();
            } else {
                $error = "Terjadi kesalahan saat membuat reservasi!";
            }
        }
    }
}
?>

<div class="sidebar">
    <a href="?page=dashboard"><i class="bi bi-house"></i> Dashboard</a>
    <a href="?page=lapangan_user" class="active"><i class="bi bi-list"></i> Daftar Lapangan</a>
    <a href="?page=riwayat_reservasi"><i class="bi bi-clock-history"></i> Riwayat Reservasi</a>
    <a href="?page=profil"><i class="bi bi-person"></i> Profil Saya</a>
</div>

<div class="main-content">
    <div class="container-fluid">
        <h1 class="mb-4"><i class="bi bi-calendar2-check"></i> Pesan Lapangan</h1>

        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5>Form Reservasi</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>

                        <form method="POST">
                            <div class="mb-3">
                                <label for="lapangan" class="form-label">Lapangan</label>
                                <input type="text" class="form-control" id="lapangan" 
                                       value="<?php echo htmlspecialchars($lapangan['nama_lapangan']); ?>" disabled>
                            </div>

                            <div class="mb-3">
                                <label for="tanggal_reservasi" class="form-label">Tanggal Reservasi</label>
                                <input type="date" class="form-control" id="tanggal_reservasi" 
                                       name="tanggal_reservasi" min="<?php echo date('Y-m-d'); ?>" required>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="jam_mulai" class="form-label">Jam Mulai</label>
                                        <input type="time" class="form-control" id="jam_mulai" 
                                               name="jam_mulai" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="jam_selesai" class="form-label">Jam Selesai</label>
                                        <input type="time" class="form-control" id="jam_selesai" 
                                               name="jam_selesai" required>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="catatan" class="form-label">Catatan (Opsional)</label>
                                <textarea class="form-control" id="catatan" name="catatan" rows="3" 
                                          placeholder="Tambahkan catatan apapun..."></textarea>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle"></i> Lanjut ke Pembayaran</button>
                                <a href="?page=lapangan_user" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Kembali</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5>Informasi Lapangan</h5>
                    </div>
                    <div class="card-body">
                        <p>
                            <strong>Nama:</strong><br>
                            <?php echo htmlspecialchars($lapangan['nama_lapangan']); ?>
                        </p>
                        <p>
                            <strong>Deskripsi:</strong><br>
                            <small><?php echo htmlspecialchars($lapangan['deskripsi']); ?></small>
                        </p>
                        <p>
                            <strong>Harga:</strong><br>
                            <span style="font-size: 1.3rem; color: #3498db; font-weight: bold;">
                                <?php echo format_rupiah($lapangan['harga_per_jam']); ?>/jam
                            </span>
                        </p>
                        <p>
                            <strong>Kapasitas:</strong><br>
                            <i class="bi bi-people"></i> <?php echo $lapangan['kapasitas']; ?> orang
                        </p>
                        <p>
                            <strong>Status:</strong><br>
                            <span class="badge bg-success"><?php echo ucfirst($lapangan['status']); ?></span>
                        </p>
                    </div>
                </div>

                <div class="card mt-3">
                    <div class="card-header">
                        <h5>Tata Cara Pemesanan</h5>
                    </div>
                    <div class="card-body">
                        <ol style="font-size: 0.9rem;">
                            <li>Pilih tanggal reservasi</li>
                            <li>Tentukan jam mulai dan selesai</li>
                            <li>Klik "Lanjut ke Pembayaran"</li>
                            <li>Pilih metode pembayaran</li>
                            <li>Selesaikan pembayaran</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

