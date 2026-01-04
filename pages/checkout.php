<?php
login_required();

$id_reservasi = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id_reservasi == 0) {
    header("Location: ?page=lapangan_user");
    exit();
}

// Get reservasi info
$query = "SELECT r.*, l.nama_lapangan FROM reservasi r 
          JOIN lapangan l ON r.id_lapangan = l.id_lapangan 
          WHERE r.id_reservasi = ? AND r.id_user = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $id_reservasi, $_SESSION['id_user']);
$stmt->execute();
$reservasi = $stmt->get_result()->fetch_assoc();

if (!$reservasi) {
    header("Location: ?page=lapangan_user");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $metode_pembayaran = $_POST['metode_pembayaran'];
    $status_pembayaran = 'lunas';
    $total_bayar = $reservasi['total_harga'];

    // Insert transaksi
    $query = "INSERT INTO transaksi (id_reservasi, id_user, id_lapangan, nama_lapangan, tanggal_reservasi, jam_mulai, jam_selesai, total_bayar, metode_pembayaran, status_pembayaran) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    
    // Type: i=int, i=int, i=int, s=string, s=string, s=string, s=string, s=string, s=string, s=string
    $stmt->bind_param("iiisssssss", 
        $id_reservasi, 
        $_SESSION['id_user'], 
        $reservasi['id_lapangan'], 
        $reservasi['nama_lapangan'], 
        $reservasi['tanggal_reservasi'], 
        $reservasi['jam_mulai'], 
        $reservasi['jam_selesai'], 
        $total_bayar, 
        $metode_pembayaran, 
        $status_pembayaran
    );

    if ($stmt->execute()) {
        // Update status reservasi
        $status = 'dikonfirmasi';
        $update_stmt = $conn->prepare("UPDATE reservasi SET status_reservasi = ? WHERE id_reservasi = ?");
        $update_stmt->bind_param("si", $status, $id_reservasi);
        $update_stmt->execute();

        header("Location: ?page=konfirmasi_pembayaran&id=" . $id_reservasi);
        exit();
    } else {
        $error = "Terjadi kesalahan saat memproses pembayaran!";
    }
}

?>

<div class="sidebar">
    <a href="?page=dashboard"><i class="bi bi-house"></i> Dashboard</a>
    <a href="?page=lapangan_user"><i class="bi bi-list"></i> Daftar Lapangan</a>
    <a href="?page=riwayat_reservasi"><i class="bi bi-clock-history"></i> Riwayat Reservasi</a>
    <a href="?page=profil"><i class="bi bi-person"></i> Profil Saya</a>
</div>

<div class="main-content">
    <div class="container-fluid">
        <h1 class="mb-4"><i class="bi bi-credit-card"></i> Checkout</h1>

        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5>Detail Reservasi</h5>
                    </div>
                    <div class="card-body">
                        <table class="w-100">
                            <tr>
                                <td style="padding: 10px 0;"><strong>Lapangan:</strong></td>
                                <td style="padding: 10px 0;"><?php echo htmlspecialchars($reservasi['nama_lapangan']); ?></td>
                            </tr>
                            <tr style="background: var(--light-bg);">
                                <td style="padding: 10px;"><strong>Tanggal:</strong></td>
                                <td style="padding: 10px;"><?php echo format_date($reservasi['tanggal_reservasi']); ?></td>
                            </tr>
                            <tr>
                                <td style="padding: 10px 0;"><strong>Jam:</strong></td>
                                <td style="padding: 10px 0;"><?php echo $reservasi['jam_mulai'] . " - " . $reservasi['jam_selesai']; ?></td>
                            </tr>
                            <tr style="background: var(--light-bg);">
                                <td style="padding: 10px;"><strong>Durasi:</strong></td>
                                <td style="padding: 10px;"><?php echo $reservasi['durasi_jam']; ?> jam</td>
                            </tr>
                            <tr>
                                <td style="padding: 10px 0;"><strong>Catatan:</strong></td>
                                <td style="padding: 10px 0;"><?php echo htmlspecialchars($reservasi['catatan']); ?></td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="card mt-3">
                    <div class="card-header">
                        <h5>Pilih Metode Pembayaran</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>

                        <form method="POST">
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="metode_pembayaran" 
                                           id="tunai" value="tunai" checked>
                                    <label class="form-check-label" for="tunai">
                                        <strong><i class="bi bi-cash-coin"></i> Tunai</strong>
                                        <br>
                                        <small class="text-muted">Bayar langsung di lapangan</small>
                                    </label>
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="metode_pembayaran" 
                                           id="transfer" value="transfer">
                                    <label class="form-check-label" for="transfer">
                                        <strong><i class="bi bi-bank"></i> Transfer Bank</strong>
                                        <br>
                                        <small class="text-muted">Untuk verifikasi lebih lanjut</small>
                                    </label>
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="metode_pembayaran" 
                                           id="kartu" value="kartu_kredit">
                                    <label class="form-check-label" for="kartu">
                                        <strong><i class="bi bi-credit-card"></i> Kartu Kredit</strong>
                                        <br>
                                        <small class="text-muted">Visa, Mastercard, Amex</small>
                                    </label>
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="metode_pembayaran" 
                                           id="ewallet" value="e_wallet">
                                    <label class="form-check-label" for="ewallet">
                                        <strong><i class="bi bi-wallet"></i> E-Wallet</strong>
                                        <br>
                                        <small class="text-muted">GCash, OVO, DANA, dll</small>
                                    </label>
                                </div>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-success"><i class="bi bi-check-circle"></i> Konfirmasi & Bayar</button>
                                <a href="?page=lapangan_user" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Batal</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5>Ringkasan Pembayaran</h5>
                    </div>
                    <div class="card-body">
                        <table class="w-100">
                            <tr>
                                <td style="padding: 10px 0;">
                                    <strong>Harga/Jam:</strong>
                                </td>
                                <td style="padding: 10px 0; text-align: right;">
                                    <?php echo format_rupiah($reservasi['total_harga'] / $reservasi['durasi_jam']); ?>
                                </td>
                            </tr>
                            <tr style="background: var(--light-bg);">
                                <td style="padding: 10px;">
                                    <strong>Durasi:</strong>
                                </td>
                                <td style="padding: 10px; text-align: right;">
                                    <?php echo $reservasi['durasi_jam']; ?> jam
                                </td>
                            </tr>
                            <tr>
                                <td style="padding: 10px 0;">
                                    <strong>Subtotal:</strong>
                                </td>
                                <td style="padding: 10px 0; text-align: right;">
                                    <?php echo format_rupiah($reservasi['total_harga']); ?>
                                </td>
                            </tr>
                            <tr style="border-top: 2px solid #ddd; background: var(--light-bg);">
                                <td style="padding: 10px;">
                                    <strong style="font-size: 1.2rem;">TOTAL:</strong>
                                </td>
                                <td style="padding: 10px; text-align: right;">
                                    <strong style="font-size: 1.2rem; color: #3498db;">
                                        <?php echo format_rupiah($reservasi['total_harga']); ?>
                                    </strong>
                                </td>
                            </tr>
                        </table>

                        <div class="alert alert-info mt-3">
                            <i class="bi bi-info-circle"></i> Pastikan informasi di atas sudah benar sebelum melanjutkan.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
