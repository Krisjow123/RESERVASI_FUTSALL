<?php
login_required();

$id_reservasi = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id_reservasi == 0) {
    header("Location: ?page=lapangan_user");
    exit();
}

// Get reservasi info
$query = "SELECT r.*, l.nama_lapangan, t.metode_pembayaran, t.status_pembayaran FROM reservasi r 
          JOIN lapangan l ON r.id_lapangan = l.id_lapangan 
          LEFT JOIN transaksi t ON r.id_reservasi = t.id_reservasi
          WHERE r.id_reservasi = ? AND r.id_user = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $id_reservasi, $_SESSION['id_user']);
$stmt->execute();
$reservasi = $stmt->get_result()->fetch_assoc();

if (!$reservasi) {
    header("Location: ?page=lapangan_user");
    exit();
}
?>

<div class="sidebar">
    <a href="?page=dashboard"><i class="bi bi-house"></i> Dashboard</a>
    <a href="?page=lapangan_user"><i class="bi bi-list"></i> Daftar Lapangan</a>
    <a href="?page=riwayat_reservasi" class="active"><i class="bi bi-clock-history"></i> Riwayat Reservasi</a>
    <a href="?page=profil"><i class="bi bi-person"></i> Profil Saya</a>
</div>

<div class="main-content">
    <div class="container-fluid">
        <h1 class="mb-4"><i class="bi bi-check-circle"></i> Konfirmasi Pembayaran</h1>

        <div class="row">
            <div class="col-md-8">
                <div class="card" style="border-left: 5px solid #27ae60;">
                    <div class="card-header" style="background: #27ae60;">
                        <h5 style="color: white; margin: 0;"><i class="bi bi-check-circle"></i> Pembayaran Berhasil</h5>
                    </div>
                    <div class="card-body">
                        <div style="text-align: center; padding: 20px;">
                            <i class="bi bi-check-circle" style="font-size: 4rem; color: #27ae60;"></i>
                            <h2 style="color: #27ae60; margin-top: 20px;">Terima Kasih!</h2>
                            <p style="font-size: 1.1rem; color: #7f8c8d;">Reservasi Anda telah dikonfirmasi</p>
                        </div>

                        <hr>

                        <h5><i class="bi bi-receipt"></i> Detail Konfirmasi</h5>
                        
                        <table class="w-100">
                            <tr>
                                <td style="padding: 10px 0; width: 50%;"><strong>No. Reservasi:</strong></td>
                                <td style="padding: 10px 0;"><?php echo str_pad($id_reservasi, 8, '0', STR_PAD_LEFT); ?></td>
                            </tr>
                            <tr style="background: var(--light-bg);">
                                <td style="padding: 10px;"><strong>Lapangan:</strong></td>
                                <td style="padding: 10px;"><?php echo htmlspecialchars($reservasi['nama_lapangan']); ?></td>
                            </tr>
                            <tr>
                                <td style="padding: 10px 0;"><strong>Tanggal:</strong></td>
                                <td style="padding: 10px 0;"><?php echo format_date($reservasi['tanggal_reservasi']); ?></td>
                            </tr>
                            <tr style="background: var(--light-bg);">
                                <td style="padding: 10px;"><strong>Jam:</strong></td>
                                <td style="padding: 10px;"><?php echo $reservasi['jam_mulai'] . " - " . $reservasi['jam_selesai']; ?></td>
                            </tr>
                            <tr>
                                <td style="padding: 10px 0;"><strong>Durasi:</strong></td>
                                <td style="padding: 10px 0;"><?php echo $reservasi['durasi_jam']; ?> jam</td>
                            </tr>
                            <tr style="background: var(--light-bg);">
                                <td style="padding: 10px;"><strong>Metode Pembayaran:</strong></td>
                                <td style="padding: 10px;"><?php echo ucfirst(str_replace('_', ' ', $reservasi['metode_pembayaran'])); ?></td>
                            </tr>
                            <tr>
                                <td style="padding: 10px 0;"><strong>Status Pembayaran:</strong></td>
                                <td style="padding: 10px 0;">
                                    <span class="badge bg-success"><?php echo ucfirst($reservasi['status_pembayaran']); ?></span>
                                </td>
                            </tr>
                            <tr style="border-top: 2px solid #ddd; background: var(--light-bg);">
                                <td style="padding: 10px;"><strong style="font-size: 1.1rem;">TOTAL BAYAR:</strong></td>
                                <td style="padding: 10px; text-align: right;">
                                    <strong style="font-size: 1.1rem; color: #3498db;">
                                        <?php echo format_rupiah($reservasi['total_harga']); ?>
                                    </strong>
                                </td>
                            </tr>
                        </table>

                        <div class="alert alert-info mt-3">
                            <strong><i class="bi bi-info-circle"></i> Informasi Penting:</strong>
                            <ul style="margin-bottom: 0; margin-top: 10px;">
                                <li>Simpan nomor reservasi Anda untuk referensi</li>
                                <li>Datang 10-15 menit sebelum jadwal reservasi</li>
                                <li>Bawa bukti pembayaran/konfirmasi ini</li>
                                <li>Hubungi admin jika ada perubahan jadwal</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5>Status Reservasi</h5>
                    </div>
                    <div class="card-body">
                        <div style="text-align: center; padding: 20px;">
                            <i class="bi bi-check-square" style="font-size: 3rem; color: #27ae60;"></i>
                            <p style="margin-top: 15px; font-size: 1.1rem;">
                                <strong>Dikonfirmasi</strong>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="card mt-3">
                    <div class="card-header">
                        <h5>Langkah Selanjutnya</h5>
                    </div>
                    <div class="card-body">
                        <ol style="font-size: 0.9rem; margin-bottom: 0;">
                            <li>Simpan nomor reservasi</li>
                            <li>Siapkan jadwal Anda</li>
                            <li>Datang tepat waktu</li>
                            <li>Nikmati permainan!</li>
                        </ol>
                    </div>
                </div>

                <div class="d-grid gap-2 mt-3">
                    <a href="?page=riwayat_reservasi" class="btn btn-primary">
                        <i class="bi bi-clock-history"></i> Lihat Riwayat
                    </a>
                    <a href="?page=lapangan_user" class="btn btn-secondary">
                        <i class="bi bi-search"></i> Pesan Lagi
                    </a>
                    <a href="?page=dashboard" class="btn btn-outline-primary">
                        <i class="bi bi-house"></i> Kembali ke Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
