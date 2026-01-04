<!-- Hero Section -->
<section class="hero-section" id="home">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-7">
                <span class="text-primary-custom fw-bold letter-spacing-2 text-uppercase mb-2 d-block">Premium Sports
                    Venue</span>
                <h1 class="hero-title">
                    Rasakan Sensasi<br>
                    <span class="text-primary-custom">Futsal Profesional</span>
                </h1>
                <p class="hero-subtitle">
                    Lapangan berstandar internasional dengan rumput sintetis premium.
                    Main nyaman, skill meningkat, performa maksimal.
                </p>
                <div class="d-flex gap-3">
                    <?php if (isset($_SESSION['id_user'])): ?>
                        <a href="?page=booking_lapangan" class="btn btn-custom-login btn-lg px-5">Booking Sekarang</a>
                    <?php else: ?>
                        <a href="?page=login" class="btn btn-custom-login btn-lg px-5">Mulai Main</a>
                    <?php endif; ?>
                    <a href="#fasilitas" class="btn btn-custom-outline btn-lg px-4">Lihat Fasilitas</a>
                </div>
            </div>
            <!-- Optional: Image placeholder on the right if needed, but styling uses BG image for cleaner look -->
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="section-padding" id="fasilitas">
    <div class="container">
        <div class="section-title">
            <h2>Fasilitas Premium</h2>
            <div class="divider"></div>
            <p class="text-muted mt-3">Kami menyediakan fasilitas terbaik untuk kenyamanan tim Anda</p>
        </div>

        <div class="row g-4">
            <div class="col-md-4">
                <div class="feature-card text-center">
                    <div class="feature-icon">
                        <i class="bi bi-grid-3x3"></i>
                    </div>
                    <h4>Rumput Sintetis</h4>
                    <p class="text-muted mb-0">
                        Lapangan menggunakan rumput sintetis kualitas import yang empuk dan aman untuk lutut.
                    </p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card text-center">
                    <div class="feature-icon">
                        <i class="bi bi-lightbulb"></i>
                    </div>
                    <h4>Penerangan LED</h4>
                    <p class="text-muted mb-0">
                        Main malam tetap terang benderang dengan sistem pencahayaan LED standar kompetisi.
                    </p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card text-center">
                    <div class="feature-icon">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <h4>Fasilitas Lengkap</h4>
                    <p class="text-muted mb-0">
                        Tersedia ruang ganti bersih, shower air hangat, mushola, dan area parkir luas.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="section-padding" style="background: linear-gradient(45deg, #0f0f0f, #1e1e1e);">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-4 mb-lg-0">
                <h2 class="mb-3">Siap untuk Bertanding?</h2>
                <p class="text-muted mb-0">
                    Jangan sampai kehabisan jadwal! Segera amankan slot lapangan untuk tim kebanggaanmu sekarang juga.
                    Proses booking mudah, cepat, dan transparan.
                </p>
            </div>
            <div class="col-lg-6 text-lg-end">
                <?php if (isset($_SESSION['id_user'])): ?>
                    <a href="?page=booking_lapangan" class="btn btn-custom-login btn-lg px-5">
                        <i class="bi bi-calendar-check me-2"></i> Cek Jadwal Kosong
                    </a>
                <?php else: ?>
                    <a href="?page=login" class="btn btn-custom-login btn-lg px-5">
                        <i class="bi bi-box-arrow-in-right me-2"></i> Login untuk Booking
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- Location Section -->
<section class="section-padding" id="lokasi">
    <div class="container">
        <div class="section-title">
            <h2>Lokasi Kami</h2>
            <div class="divider"></div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="feature-card p-2">
                    <!-- Placeholder Map -->
                    <div
                        style="width: 100%; height: 400px; background-color: #333; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                        <span class="text-muted">Google Maps Embed Placeholder</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>