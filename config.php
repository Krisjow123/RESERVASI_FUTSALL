<?php
// ===== DATABASE CONFIGURATION =====
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'futsal_reservation');

// ===== DATABASE CONNECTION =====
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Koneksi database gagal: " . $conn->connect_error);
}

// Set charset to utf8
$conn->set_charset("utf8");

// ===== HELPER FUNCTIONS =====

function htmlspecialchars_array($data) {
    return is_array($data) ? array_map('htmlspecialchars_array', $data) : htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

function login_required() {
    if (!isset($_SESSION['id_user'])) {
        header("Location: index.php?page=login");
        exit();
    }
}

function admin_required() {
    if (!isset($_SESSION['id_user']) || $_SESSION['role'] != 'admin') {
        header("Location: index.php?page=404");
        exit();
    }
}

function format_rupiah($nominal) {
    return "Rp. " . number_format($nominal, 0, ',', '.');
}

function format_date($date) {
    $bulan = array('Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember');
    $tahun = substr($date, 0, 4);
    $bulan_ke = (int)substr($date, 5, 2);
    $hari = substr($date, 8, 2);
    return $hari . ' ' . $bulan[$bulan_ke - 1] . ' ' . $tahun;
}

function sanitize($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function redirect($page) {
    header("Location: index.php?page=" . $page);
    exit();
}

function get_param($param, $default = '') {
    return isset($_GET[$param]) ? sanitize($_GET[$param]) : $default;
}

function post_param($param, $default = '') {
    return isset($_POST[$param]) ? sanitize($_POST[$param]) : $default;
}
/**
 * Upload file dengan validasi
 */
function upload_file($file, $directory, $allowed_types = ['jpg', 'jpeg', 'png', 'gif']) {
    // Create uploads directory if not exists
    $base_upload_dir = dirname(__FILE__) . '/uploads/';
    if (!is_dir($base_upload_dir)) {
        mkdir($base_upload_dir, 0777, true);
    }
    
    $upload_dir = $base_upload_dir . $directory . '/';
    
    // Create subdirectory if not exists
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    // Jika tidak ada file upload
    if (!isset($file) || $file['size'] == 0) {
        return null;
    }
    
    // Validasi error upload
    if ($file['error'] != UPLOAD_ERR_OK) {
        $error_msg = '';
        switch ($file['error']) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                $error_msg = 'File terlalu besar';
                break;
            case UPLOAD_ERR_PARTIAL:
                $error_msg = 'Upload tidak lengkap';
                break;
            case UPLOAD_ERR_NO_FILE:
                $error_msg = 'Tidak ada file yang dipilih';
                break;
            default:
                $error_msg = 'Error upload';
        }
        return array('error' => $error_msg);
    }
    
    // Validasi ukuran file (max 5MB)
    $max_size = 5 * 1024 * 1024;
    if ($file['size'] > $max_size) {
        return array('error' => 'Ukuran file terlalu besar (max 5MB)');
    }
    
    // Validasi tipe file
    $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($file_ext, $allowed_types)) {
        return array('error' => 'Tipe file tidak diizinkan. Hanya ' . implode(', ', $allowed_types));
    }
    
    // Generate unique filename
    $filename = time() . '_' . uniqid() . '.' . $file_ext;
    $filepath = $upload_dir . $filename;
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        // Return relative path untuk disimpan di database
        return $directory . '/' . $filename;
    } else {
        return array('error' => 'Gagal mengupload file ke server');
    }
}

/**
 * Delete file
 */
function delete_file($filepath) {
    if (!empty($filepath)) {
        $full_path = dirname(__FILE__) . '/uploads/' . $filepath;
        if (file_exists($full_path)) {
            unlink($full_path);
        }
    }
}

/**
 * Get file URL untuk display
 */
function get_file_url($filepath) {
    if (empty($filepath)) {
        return '';
    }
    return 'uploads/' . $filepath;
}
?>
