<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $query = "SELECT * FROM users WHERE username = ? LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Simple password check (for testing purposes as per original code)
        // In real app, use password_verify()
        if (
            ($password === 'admin123' && $user['username'] === 'admin') ||
            ($password === 'user123' && in_array($user['username'], ['user1', 'user2', 'user3'])) ||
            password_verify($password, $user['password'])
        ) { // Add support for real hashed passwords if they exist

            $_SESSION['id_user'] = $user['id_user'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];

            if ($user['role'] == 'admin') {
                header("Location: ?page=admin_dashboard");
            } else {
                header("Location: ?page=dashboard");
            }
            exit();
        } else {
            $error = "Password salah!";
        }
    } else {
        $error = "Username tidak ditemukan!";
    }
}
?>

<div class="auth-container">
    <div class="auth-card animated fadeIn">
        <div class="text-center mb-4">
            <h2 class="mb-1"><i class="bi bi-heptagon-half text-primary-custom"></i></h2>
            <h3 class="fw-bold">Welcome Back</h3>
            <p class="text-muted small">Silakan login untuk melanjutkan booking</p>
        </div>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger border-0 bg-danger bg-opacity-10 text-danger" role="alert">
                <i class="bi bi-exclamation-circle me-2"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <div class="input-group">
                    <span class="input-group-text bg-transparent border-end-0 border-secondary text-muted"><i
                            class="bi bi-person"></i></span>
                    <input type="text" class="form-control border-start-0 ps-0" id="username" name="username"
                        placeholder="Masukkan username" required style="border-left: none;">
                </div>
            </div>

            <div class="mb-4">
                <div class="d-flex justify-content-between align-items-center">
                    <label for="password" class="form-label mb-0">Password</label>
                    <a href="#" class="small text-primary-custom text-decoration-none">Lupa Password?</a>
                </div>
                <div class="input-group mt-2">
                    <span class="input-group-text bg-transparent border-end-0 border-secondary text-muted"><i
                            class="bi bi-lock"></i></span>
                    <input type="password" class="form-control border-start-0 ps-0" id="password" name="password"
                        placeholder="Masukkan password" required style="border-left: none;">
                </div>
            </div>

            <button type="submit" class="btn btn-custom-login w-100 py-2 mb-3">
                Login <i class="bi bi-arrow-right ms-2"></i>
            </button>

            <div class="text-center text-muted small mb-4">
                Belum punya akun? <a href="?page=register"
                    class="text-primary-custom text-decoration-none fw-bold">Daftar Sekarang</a>
            </div>

            <!-- Developer helper (can be removed in production) -->
            <div class="p-3 rounded bg-dark border border-secondary">
                <small class="d-block text-muted mb-1"><i class="bi bi-info-circle me-1"></i> <strong>Akun
                        Demo:</strong></small>
                <div class="d-flex justify-content-between small text-muted">
                    <span>Admin: admin / admin123</span>
                </div>
                <div class="d-flex justify-content-between small text-muted">
                    <span>User: user1 / user123</span>
                </div>
            </div>
        </form>
    </div>
</div>

<style>
    /* Fix input group styling overrides for dark theme */
    .input-group-text {
        background-color: #2a2a2a !important;
        border-color: #404040 !important;
    }

    .input-group .form-control {
        border-left: none;
    }

    .input-group .form-control:focus {
        border-color: #404040;
        box-shadow: none;
        /* Custom focus ring handling if needed, but simplifying for now */
        border-bottom-color: var(--primary-color);
    }
</style>