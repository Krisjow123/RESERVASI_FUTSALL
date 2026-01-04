<?php
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $nama_lengkap = trim($_POST['nama_lengkap']);
    $no_telepon = trim($_POST['no_telepon']);
    $alamat = trim($_POST['alamat']);

    if (empty($username) || empty($email) || empty($password) || empty($nama_lengkap)) {
        $error = "Semua field harus diisi!";
    } elseif (strlen($password) < 6) {
        $error = "Password minimal 6 karakter!";
    } elseif ($password !== $confirm_password) {
        $error = "Password tidak cocok!";
    } else {
        $query = "SELECT id_user FROM users WHERE username = ? OR email = ? LIMIT 1";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error = "Username atau email sudah terdaftar!";
        } else {
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            $query = "INSERT INTO users (username, email, password, nama_lengkap, no_telepon, alamat, role, status) 
                      VALUES (?, ?, ?, ?, ?, ?, 'user', 'aktif')";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ssssss", $username, $email, $hashed_password, $nama_lengkap, $no_telepon, $alamat);

            if ($stmt->execute()) {
                $success = "Registrasi berhasil! Silakan login.";
                $_POST = array(); // Clear form
            } else {
                $error = "Terjadi kesalahan saat registrasi!";
            }
        }
    }
}
?>

<div class="auth-container">
    <div class="auth-card animated fadeIn" style="max-width: 600px;">
        <div class="text-center mb-4">
            <h2 class="mb-1"><i class="bi bi-heptagon-half text-primary-custom"></i></h2>
            <h3 class="fw-bold">Buat Akun Baru</h3>
            <p class="text-muted small">Bergabunglah dengan komunitas futsal terbesar</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger border-0 bg-danger bg-opacity-10 text-danger" role="alert">
                <i class="bi bi-exclamation-circle me-2"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success border-0 bg-success bg-opacity-10 text-success text-center" role="alert">
                <i class="bi bi-check-circle me-2"></i> <?php echo $success; ?>
                <br>
                <a href="?page=login" class="alert-link fw-bold mt-2 d-inline-block">Login Sekarang</a>
            </div>
        <?php else: ?>

            <form method="POST">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="nama_lengkap" class="form-label">Nama Lengkap</label>
                        <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap"
                            value="<?php echo isset($_POST['nama_lengkap']) ? htmlspecialchars($_POST['nama_lengkap']) : ''; ?>"
                            placeholder="Nama Anda" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username"
                            value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                            placeholder="Pilih username" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email"
                        value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                        placeholder="email@contoh.com" required>
                </div>

                <div class="mb-3">
                    <label for="no_telepon" class="form-label">No. Telepon</label>
                    <input type="text" class="form-control" id="no_telepon" name="no_telepon"
                        value="<?php echo isset($_POST['no_telepon']) ? htmlspecialchars($_POST['no_telepon']) : ''; ?>"
                        placeholder="0812...">
                </div>

                <div class="mb-3">
                    <label for="alamat" class="form-label">Alamat</label>
                    <textarea class="form-control" id="alamat" name="alamat" rows="2"
                        placeholder="Alamat lengkap..."><?php echo isset($_POST['alamat']) ? htmlspecialchars($_POST['alamat']) : ''; ?></textarea>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" placeholder="******"
                            required>
                        <small class="text-muted" style="font-size: 0.75rem;">Min. 6 karakter</small>
                    </div>
                    <div class="col-md-6 mb-4">
                        <label for="confirm_password" class="form-label">Konfirmasi Password</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password"
                            placeholder="******" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-custom-login w-100 py-2 mb-3">
                    <i class="bi bi-person-plus me-2"></i> Daftar Sekarang
                </button>

                <div class="text-center text-muted small">
                    Sudah punya akun? <a href="?page=login" class="text-primary-custom text-decoration-none fw-bold">Login
                        di sini</a>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>