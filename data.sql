-- Database Sistem Reservasi Lapangan Futsal
CREATE DATABASE IF NOT EXISTS futsal_reservation;
USE futsal_reservation;

-- TABLE: users
CREATE TABLE users (
    id_user INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nama_lengkap VARCHAR(100) NOT NULL,
    no_telepon VARCHAR(15),
    alamat TEXT,
    foto_profil VARCHAR(255),
    role ENUM('admin', 'user') DEFAULT 'user',
    status ENUM('aktif', 'nonaktif') DEFAULT 'aktif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- TABLE: lapangan
CREATE TABLE lapangan (
    id_lapangan INT PRIMARY KEY AUTO_INCREMENT,
    nama_lapangan VARCHAR(100) NOT NULL,
    deskripsi TEXT,
    foto_lapangan VARCHAR(255),
    harga_per_jam DECIMAL(10, 2) NOT NULL,
    kapasitas INT DEFAULT 10,
    status ENUM('tersedia', 'maintenance', 'nonaktif') DEFAULT 'tersedia',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- TABLE: slot_waktu
CREATE TABLE slot_waktu (
    id_slot INT PRIMARY KEY AUTO_INCREMENT,
    id_lapangan INT NOT NULL,
    jam_mulai TIME NOT NULL,
    jam_selesai TIME NOT NULL,
    hari_kerja VARCHAR(50),
    harga_slot DECIMAL(10, 2),
    status ENUM('tersedia', 'terjual', 'maintenance') DEFAULT 'tersedia',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_lapangan) REFERENCES lapangan(id_lapangan) ON DELETE CASCADE
);

-- TABLE: reservasi
CREATE TABLE reservasi (
    id_reservasi INT PRIMARY KEY AUTO_INCREMENT,
    id_user INT NOT NULL,
    id_lapangan INT NOT NULL,
    tanggal_reservasi DATE NOT NULL,
    jam_mulai TIME NOT NULL,
    jam_selesai TIME NOT NULL,
    durasi_jam INT NOT NULL,
    total_harga DECIMAL(10, 2) NOT NULL,
    status_reservasi ENUM('pending', 'dikonfirmasi', 'selesai', 'dibatalkan') DEFAULT 'pending',
    catatan TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_user) REFERENCES users(id_user) ON DELETE CASCADE,
    FOREIGN KEY (id_lapangan) REFERENCES lapangan(id_lapangan) ON DELETE CASCADE,
    INDEX idx_user (id_user),
    INDEX idx_lapangan (id_lapangan),
    INDEX idx_tanggal (tanggal_reservasi)
);

-- TABLE: transaksi
CREATE TABLE transaksi (
    id_transaksi INT PRIMARY KEY AUTO_INCREMENT,
    id_reservasi INT NOT NULL,
    id_user INT NOT NULL,
    id_lapangan INT NOT NULL,
    nama_lapangan VARCHAR(100),
    tanggal_transaksi DATETIME DEFAULT CURRENT_TIMESTAMP,
    tanggal_reservasi DATE NOT NULL,
    jam_mulai TIME NOT NULL,
    jam_selesai TIME NOT NULL,
    total_bayar DECIMAL(10, 2) NOT NULL,
    metode_pembayaran ENUM('tunai', 'transfer', 'kartu_kredit', 'e_wallet') DEFAULT 'tunai',
    status_pembayaran ENUM('pending', 'lunas', 'terlambat') DEFAULT 'pending',
    keterangan TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_reservasi) REFERENCES reservasi(id_reservasi) ON DELETE CASCADE,
    FOREIGN KEY (id_user) REFERENCES users(id_user) ON DELETE CASCADE,
    FOREIGN KEY (id_lapangan) REFERENCES lapangan(id_lapangan) ON DELETE CASCADE,
    INDEX idx_tanggal (tanggal_transaksi),
    INDEX idx_user (id_user),
    INDEX idx_status (status_pembayaran)
);

-- TABLE: ketersediaan
CREATE TABLE ketersediaan (
    id_ketersediaan INT PRIMARY KEY AUTO_INCREMENT,
    id_lapangan INT NOT NULL,
    tanggal_ketersediaan DATE NOT NULL,
    jam_mulai TIME NOT NULL,
    jam_selesai TIME NOT NULL,
    status ENUM('tersedia', 'terjual', 'maintenance') DEFAULT 'tersedia',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_lapangan) REFERENCES lapangan(id_lapangan) ON DELETE CASCADE,
    UNIQUE KEY unique_slot (id_lapangan, tanggal_ketersediaan, jam_mulai)
);

-- INSERT DATA DUMMY
INSERT INTO users (username, email, password, nama_lengkap, no_telepon, alamat, role) VALUES
('admin', 'admin@futsal.com', 'admin123', 'Admin Futsal', '081234567890', 'Jl. Admin No.1', 'admin'),
('user1', 'user1@gmail.com', 'user123', 'Andi Wijaya', '081234567891', 'Jl. User No.1', 'user'),
('user2', 'user2@gmail.com', 'user123', 'Budi Santoso', '081234567892', 'Jl. User No.2', 'user'),
('user3', 'user3@gmail.com', 'user123', 'Citra Dewi', '081234567893', 'Jl. User No.3', 'user');

INSERT INTO lapangan (nama_lapangan, deskripsi, harga_per_jam, kapasitas, status) VALUES
('Lapangan A - Premium', 'Lapangan futsal dengan standar internasional, dilengkapi dengan pencahayaan LED', 150000, 10, 'tersedia'),
('Lapangan B - Standard', 'Lapangan futsal standar dengan fasilitas lengkap', 120000, 10, 'tersedia'),
('Lapangan C - Economy', 'Lapangan futsal ekonomis dengan fasilitas dasar', 80000, 10, 'tersedia'),
('Lapangan D - VIP', 'Lapangan futsal VIP dengan AC dan fasilitas premium', 200000, 10, 'tersedia');

INSERT INTO slot_waktu (id_lapangan, jam_mulai, jam_selesai, hari_kerja, harga_slot) VALUES
(1, '08:00:00', '09:00:00', 'Senin-Jumat', 150000),
(1, '09:00:00', '10:00:00', 'Senin-Jumat', 150000),
(1, '19:00:00', '20:00:00', 'Senin-Jumat', 180000),
(1, '20:00:00', '21:00:00', 'Senin-Jumat', 180000),
(2, '08:00:00', '09:00:00', 'Senin-Jumat', 120000),
(2, '09:00:00', '10:00:00', 'Senin-Jumat', 120000),
(2, '19:00:00', '20:00:00', 'Senin-Jumat', 150000),
(2, '20:00:00', '21:00:00', 'Senin-Jumat', 150000),
(3, '08:00:00', '09:00:00', 'Senin-Jumat', 80000),
(3, '09:00:00', '10:00:00', 'Senin-Jumat', 80000),
(3, '19:00:00', '20:00:00', 'Senin-Jumat', 100000),
(3, '20:00:00', '21:00:00', 'Senin-Jumat', 100000),
(4, '08:00:00', '09:00:00', 'Senin-Jumat', 200000),
(4, '19:00:00', '20:00:00', 'Senin-Jumat', 250000);

INSERT INTO reservasi (id_user, id_lapangan, tanggal_reservasi, jam_mulai, jam_selesai, durasi_jam, total_harga, status_reservasi) VALUES
(2, 1, '2026-01-05', '19:00:00', '21:00:00', 2, 360000, 'dikonfirmasi'),
(3, 2, '2026-01-05', '20:00:00', '21:00:00', 1, 150000, 'dikonfirmasi'),
(2, 3, '2026-01-06', '08:00:00', '09:00:00', 1, 80000, 'pending'),
(3, 4, '2026-01-06', '19:00:00', '20:00:00', 1, 250000, 'dikonfirmasi');

INSERT INTO transaksi (id_reservasi, id_user, id_lapangan, nama_lapangan, tanggal_reservasi, jam_mulai, jam_selesai, total_bayar, metode_pembayaran, status_pembayaran) VALUES
(1, 2, 1, 'Lapangan A - Premium', '2026-01-05', '19:00:00', '21:00:00', 360000, 'transfer', 'lunas'),
(2, 3, 2, 'Lapangan B - Standard', '2026-01-05', '20:00:00', '21:00:00', 150000, 'tunai', 'lunas'),
(3, 2, 3, 'Lapangan C - Economy', '2026-01-06', '08:00:00', '09:00:00', 80000, 'transfer', 'pending'),
(4, 3, 4, 'Lapangan D - VIP', '2026-01-06', '19:00:00', '20:00:00', 250000, 'e_wallet', 'lunas');
