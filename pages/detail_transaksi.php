<?php
login_required();

$id_reservasi = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id_reservasi == 0) {
    header("Location: ?page=riwayat_reservasi");
    exit();
}

// Get transaksi details
$query = "SELECT r.*, l.nama_lapangan, t.metode_pembayaran, t.status_pembayaran, t.id_transaksi
          FROM reservasi r 
          JOIN lapangan l ON r.id_lapangan = l.id_lapangan 
          LEFT JOIN transaksi t ON r.id_reservasi = t.id_reservasi
          WHERE r.id_reservasi = ? AND r.id_user = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $id_reservasi, $_SESSION['id_user']);
$stmt->execute();
$reservasi = $stmt->get_result()->fetch_assoc();

if (!$reservasi) {
    header("Location: ?page=riwayat_reservasi");
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
        <div class="row mb-4">
            <div class="col-md-12">
                <a href="?page=riwayat_reservasi" class="btn btn-secondary mb-3">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
            </div>
        </div>

        <h1 class="mb-4"><i class="bi bi-receipt"></i> Detail Transaksi #<?php echo str_pad($id_reservasi, 8, '0', STR_PAD_LEFT); ?></h1>

        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5>Detail Reservasi & Pembayaran</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h6 style="color: #2c3e50; margin-bottom: 10px;">Informasi Lapangan</h6>
                                <table style="width: 100%;">
                                    <tr>
                                        <td style="padding: 8px 0;"><strong>Lapangan:</strong></td>
                                        <td style="padding: 8px 0;"><?php echo htmlspecialchars($reservasi['nama_lapangan']); ?></td>
                                    </tr>
                                    <tr style="background: var(--light-bg);">
                                        <td style="padding: 8px;"><strong>Tanggal:</strong></td>
                                        <td style="padding: 8px;"><?php echo format_date($reservasi['tanggal_reservasi']); ?></td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 8px 0;"><strong>Jam Mulai:</strong></td>
                                        <td style="padding: 8px 0;"><?php echo $reservasi['jam_mulai']; ?></td>
                                    </tr>
                                    <tr style="background: var(--light-bg);">
                                        <td style="padding: 8px;"><strong>Jam Selesai:</strong></td>
                                        <td style="padding: 8px;"><?php echo $reservasi['jam_selesai']; ?></td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 8px 0;"><strong>Durasi:</strong></td>
                                        <td style="padding: 8px 0;"><?php echo $reservasi['durasi_jam']; ?> jam</td>
                                    </tr>
                                </table>
                            </div>

                            <div class="col-md-6">
                                <h6 style="color: #2c3e50; margin-bottom: 10px;">Status Reservasi</h6>
                                <table style="width: 100%;">
                                    <tr>
                                        <td style="padding: 8px 0;"><strong>No. Reservasi:</strong></td>
                                        <td style="padding: 8px 0;">
                                            <code style="background: var(--light-bg); padding: 5px 10px; border-radius: 3px;">
                                                <?php echo str_pad($id_reservasi, 8, '0', STR_PAD_LEFT); ?>
                                            </code>
                                        </td>
                                    </tr>
                                    <tr style="background: var(--light-bg);">
                                        <td style="padding: 8px;"><strong>Status:</strong></td>
                                        <td style="padding: 8px;">
                                            <span class="badge-<?php echo $reservasi['status_reservasi']; ?>">
                                                <?php echo ucfirst($reservasi['status_reservasi']); ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 8px 0;"><strong>Catatan:</strong></td>
                                        <td style="padding: 8px 0;"><?php echo htmlspecialchars($reservasi['catatan']); ?></td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <hr>

                        <h6 style="color: #2c3e50; margin-bottom: 10px;">Informasi Pembayaran</h6>
                        
                        <?php if (!empty($reservasi['metode_pembayaran'])): ?>
                            <table style="width: 100%;">
                                <tr>
                                    <td style="padding: 8px 0;"><strong>Metode Pembayaran:</strong></td>
                                    <td style="padding: 8px 0;"><?php echo ucfirst(str_replace('_', ' ', $reservasi['metode_pembayaran'])); ?></td>
                                </tr>
                                <tr style="background: var(--light-bg);">
                                    <td style="padding: 8px;"><strong>Status Pembayaran:</strong></td>
                                    <td style="padding: 8px;">
                                        <span class="badge bg-<?php echo $reservasi['status_pembayaran'] == 'lunas' ? 'success' : ($reservasi['status_pembayaran'] == 'pending' ? 'warning' : 'danger'); ?>">
                                            <?php echo ucfirst($reservasi['status_pembayaran']); ?>
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 8px 0;"><strong>Total Pembayaran:</strong></td>
                                    <td style="padding: 8px 0; color: #3498db; font-weight: bold;">
                                        <?php echo format_rupiah($reservasi['total_harga']); ?>
                                    </td>
                                </tr>
                            </table>
                        <?php else: ?>
                            <div class="alert alert-warning">
                                <i class="bi bi-info-circle"></i> Reservasi belum diproses pembayaran
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="card mt-3">
                    <div class="card-header">
                        <h5>Timeline</h5>
                    </div>
                    <div class="card-body">
                        <div style="position: relative; padding: 20px 0;">
                            <!-- Timeline Item 1 -->
                            <div style="display: flex; margin-bottom: 30px;">
                                <div style="width: 30px; display: flex; align-items: center; justify-content: center;">
                                    <div style="width: 20px; height: 20px; background: #27ae60; border-radius: 50%; border: 3px solid white; box-shadow: 0 0 0 2px #27ae60;"></div>
                                </div>
                                <div style="margin-left: 20px; flex: 1;">
                                    <strong style="color: #2c3e50;">Reservasi Dibuat</strong>
                                    <p style="color: #7f8c8d; margin: 5px 0;">
                                        <?php echo date('d-m-Y H:i', strtotime($reservasi['created_at'])); ?>
                                    </p>
                                </div>
                            </div>

                            <!-- Timeline Item 2 -->
                            <div style="display: flex; margin-bottom: 30px;">
                                <div style="width: 30px; display: flex; align-items: center; justify-content: center;">
                                    <div style="width: 20px; height: 20px; background: <?php echo in_array($reservasi['status_reservasi'], ['dikonfirmasi', 'selesai']) ? '#3498db' : '#bdc3c7'; ?>; border-radius: 50%; border: 3px solid white; box-shadow: 0 0 0 2px <?php echo in_array($reservasi['status_reservasi'], ['dikonfirmasi', 'selesai']) ? '#3498db' : '#bdc3c7'; ?>;"></div>
                                </div>
                                <div style="margin-left: 20px; flex: 1;">
                                    <strong style="color: #2c3e50;">Pembayaran Dikonfirmasi</strong>
                                    <p style="color: #7f8c8d; margin: 5px 0;">
                                        <?php echo in_array($reservasi['status_reservasi'], ['dikonfirmasi', 'selesai']) ? 'Selesai' : 'Menunggu'; ?>
                                    </p>
                                </div>
                            </div>

                            <!-- Timeline Item 3 -->
                            <div style="display: flex;">
                                <div style="width: 30px; display: flex; align-items: center; justify-content: center;">
                                    <div style="width: 20px; height: 20px; background: <?php echo $reservasi['status_reservasi'] == 'selesai' ? '#27ae60' : '#bdc3c7'; ?>; border-radius: 50%; border: 3px solid white; box-shadow: 0 0 0 2px <?php echo $reservasi['status_reservasi'] == 'selesai' ? '#27ae60' : '#bdc3c7'; ?>;"></div>
                                </div>
                                <div style="margin-left: 20px; flex: 1;">
                                    <strong style="color: #2c3e50;">Reservasi Selesai</strong>
                                    <p style="color: #7f8c8d; margin: 5px 0;">
                                        <?php echo $reservasi['status_reservasi'] == 'selesai' ? 'Selesai' : 'Menunggu'; ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5>Ringkasan Pembayaran</h5>
                    </div>
                    <div class="card-body">
                        <table style="width: 100%;">
                            <tr>
                                <td style="padding: 10px 0;"><strong>Harga per Jam:</strong></td>
                                <td style="padding: 10px 0; text-align: right;">
                                    <?php echo format_rupiah($reservasi['total_harga'] / $reservasi['durasi_jam']); ?>
                                </td>
                            </tr>
                            <tr style="background: var(--light-bg);">
                                <td style="padding: 10px;"><strong>Durasi:</strong></td>
                                <td style="padding: 10px; text-align: right;">
                                    <?php echo $reservasi['durasi_jam']; ?> jam
                                </td>
                            </tr>
                            <tr>
                                <td style="padding: 10px 0;"><strong>Subtotal:</strong></td>
                                <td style="padding: 10px 0; text-align: right;">
                                    <?php echo format_rupiah($reservasi['total_harga']); ?>
                                </td>
                            </tr>
                            <tr style="border-top: 2px solid #ddd; background: var(--light-bg);">
                                <td style="padding: 10px;"><strong style="font-size: 1.1rem;">TOTAL:</strong></td>
                                <td style="padding: 10px; text-align: right;">
                                    <strong style="font-size: 1.1rem; color: #3498db;">
                                        <?php echo format_rupiah($reservasi['total_harga']); ?>
                                    </strong>
                                </td>
                            </tr>
                        </table>

                        <div class="alert alert-info mt-3">
                            <strong><i class="bi bi-info-circle"></i> Status:</strong>
                            <p style="margin-top: 10px; margin-bottom: 0;">
                                <?php 
                                if ($reservasi['status_reservasi'] == 'pending') {
                                    echo "Menunggu konfirmasi pembayaran";
                                } elseif ($reservasi['status_reservasi'] == 'dikonfirmasi') {
                                    echo "Pembayaran sudah dikonfirmasi, siap untuk menggunakan lapangan";
                                } elseif ($reservasi['status_reservasi'] == 'selesai') {
                                    echo "Reservasi sudah selesai";
                                } elseif ($reservasi['status_reservasi'] == 'dibatalkan') {
                                    echo "Reservasi telah dibatalkan";
                                }
                                ?>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="card mt-3">
                    <div class="card-header">
                        <h5>Aksi</h5>
                    </div>
                    <div class="card-body d-grid gap-2">
                        <a href="?page=riwayat_reservasi" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Kembali ke Riwayat
                        </a>
                        <a href="?page=lapangan_user" class="btn btn-primary">
                            <i class="bi bi-plus-circle"></i> Pesan Lagi
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
