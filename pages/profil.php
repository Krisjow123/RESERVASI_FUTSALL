<?php
login_required();

$id_user = $_SESSION['id_user'];
$error = '';
$success = '';

// Get user data
$query = "SELECT * FROM users WHERE id_user = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id_user);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_lengkap = trim($_POST['nama_lengkap']);
    $email = trim($_POST['email']);
    $no_telepon = trim($_POST['no_telepon']);
    $alamat = trim($_POST['alamat']);
    
    $foto_profil = $user['foto_profil']; // Keep existing photo

    if (empty($nama_lengkap) || empty($email)) {
        $error = "Nama dan email harus diisi!";
    } else {
        // Check email
        $query = "SELECT id_user FROM users WHERE email = ? AND id_user != ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("si", $email, $id_user);
        $stmt->execute();

        if ($stmt->get_result()->num_rows > 0) {
            $error = "Email sudah digunakan!";
        } else {
            // Handle file upload
            if (isset($_FILES['foto_profil']) && $_FILES['foto_profil']['size'] > 0) {
                $upload_result = upload_file($_FILES['foto_profil'], 'profil');
                
                if (is_array($upload_result) && isset($upload_result['error'])) {
                    $error = $upload_result['error'];
                } else {
                    // Delete old file
                    if (!empty($user['foto_profil'])) {
                        delete_file($user['foto_profil']);
                    }
                    $foto_profil = $upload_result;
                }
            }

            if (empty($error)) {
                $query = "UPDATE users SET nama_lengkap = ?, email = ?, no_telepon = ?, alamat = ?, foto_profil = ? WHERE id_user = ?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("sssssi", $nama_lengkap, $email, $no_telepon, $alamat, $foto_profil, $id_user);

                if ($stmt->execute()) {
                    // Update session
                    $_SESSION['nama_lengkap'] = $nama_lengkap;
                    $_SESSION['email'] = $email;
                    
                    // Refresh user data
                    $query = "SELECT * FROM users WHERE id_user = ?";
                    $stmt = $conn->prepare($query);
                    $stmt->bind_param("i", $id_user);
                    $stmt->execute();
                    $user = $stmt->get_result()->fetch_assoc();

                    $success = "Profil berhasil diperbarui!";
                } else {
                    $error = "Terjadi kesalahan saat memperbarui profil!";
                }
            }
        }
    }
}
?>

<div class="sidebar">
    <a href="?page=dashboard"><i class="bi bi-house"></i> Dashboard</a>
    <a href="?page=lapangan_user"><i class="bi bi-list"></i> Daftar Lapangan</a>
    <a href="?page=riwayat_reservasi"><i class="bi bi-clock-history"></i> Riwayat Reservasi</a>
    <a href="?page=profil" class="active"><i class="bi bi-person"></i> Profil Saya</a>
</div>

<div class="main-content">
    <div class="container-fluid">
        <h1 class="mb-4"><i class="bi bi-person"></i> Profil Saya</h1>

        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5>Edit Profil</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>

                        <?php if (!empty($success)): ?>
                            <div class="alert alert-success"><?php echo $success; ?></div>
                        <?php endif; ?>

                        <form method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="nama_lengkap" class="form-label">Nama Lengkap</label>
                                <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" 
                                       value="<?php echo htmlspecialchars($user['nama_lengkap']); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" 
                                       value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
                                <small class="text-muted">Username tidak dapat diubah</small>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($user['email']); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="no_telepon" class="form-label">No. Telepon</label>
                                <input type="text" class="form-control" id="no_telepon" name="no_telepon" 
                                       value="<?php echo htmlspecialchars($user['no_telepon']); ?>">
                            </div>

                            <div class="mb-3">
                                <label for="alamat" class="form-label">Alamat</label>
                                <textarea class="form-control" id="alamat" name="alamat" rows="4"><?php echo htmlspecialchars($user['alamat']); ?></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="foto_profil" class="form-label">Foto Profil</label>
                                <div style="margin-bottom: 15px;">
                                    <?php if (!empty($user['foto_profil'])): ?>
                                        <img src="<?php echo get_file_url($user['foto_profil']); ?>" alt="Foto Profil" style="width: 150px; height: 150px; border-radius: 10px; object-fit: cover; border: 2px solid #ddd;">
                                    <?php else: ?>
                                        <div style="width: 150px; height: 150px; border-radius: 10px; background: var(--light-bg); display: flex; align-items: center; justify-content: center;">
                                            <i class="bi bi-image" style="font-size: 3rem; color: #7f8c8d;"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <input type="file" class="form-control" id="foto_profil" name="foto_profil" accept="image/*">
                                <small class="text-muted">Format: JPG, PNG, GIF (Max 5MB)</small>
                            </div>

                            <div class="mb-3">
                                <label for="role" class="form-label">Role</label>
                                <input type="text" class="form-control" id="role" 
                                       value="<?php echo ucfirst($user['role']); ?>" disabled>
                            </div>

                            <div class="mb-3">
                                <label for="status" class="form-label">Status</label>
                                <input type="text" class="form-control" id="status" 
                                       value="<?php echo ucfirst($user['status']); ?>" disabled>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle"></i> Simpan Perubahan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5>Info Akun</h5>
                    </div>
                    <div class="card-body">
                        <div style="text-align: center; margin-bottom: 20px;">
                            <?php if (!empty($user['foto_profil'])): ?>
                                <img src="<?php echo get_file_url($user['foto_profil']); ?>" alt="Foto Profil" style="width: 120px; height: 120px; border-radius: 50%; object-fit: cover; border: 3px solid #3498db;">
                            <?php else: ?>
                                <i class="bi bi-person-circle" style="font-size: 5rem; color: #3498db;"></i>
                            <?php endif; ?>
                        </div>
                        <p>
                            <strong>Nama:</strong><br>
                            <?php echo htmlspecialchars($user['nama_lengkap']); ?>
                        </p>
                        <p>
                            <strong>Username:</strong><br>
                            <?php echo htmlspecialchars($user['username']); ?>
                        </p>
                        <p>
                            <strong>Email:</strong><br>
                            <small><?php echo htmlspecialchars($user['email']); ?></small>
                        </p>
                        <p>
                            <strong>Role:</strong><br>
                            <span class="badge bg-info"><?php echo ucfirst($user['role']); ?></span>
                        </p>
                        <p>
                            <strong>Status:</strong><br>
                            <span class="badge bg-success"><?php echo ucfirst($user['status']); ?></span>
                        </p>
                        <p>
                            <strong>Terdaftar Sejak:</strong><br>
                            <small><?php echo format_date(date('Y-m-d', strtotime($user['created_at']))); ?></small>
                        </p>
                    </div>
                </div>

                <div class="card mt-3">
                    <div class="card-header">
                        <h5>Aksi Cepat</h5>
                    </div>
                    <div class="card-body d-grid gap-2">
                        <a href="?page=dashboard" class="btn btn-outline-primary">
                            <i class="bi bi-house"></i> Dashboard
                        </a>
                        <a href="?page=riwayat_reservasi" class="btn btn-outline-primary">
                            <i class="bi bi-clock-history"></i> Riwayat
                        </a>
                        <a href="?page=logout" class="btn btn-outline-danger">
                            <i class="bi bi-box-arrow-left"></i> Logout
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
