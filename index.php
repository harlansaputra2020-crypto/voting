<?php
require_once '../config/koneksi.php';
$periode = getPeriodeAktif($conn);
$qAllPeriode = mysqli_query($conn, "SELECT * FROM periode_voting ORDER BY id DESC LIMIT 1");
$infoJadwal = mysqli_fetch_assoc($qAllPeriode);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pemilihan Kakang Senduk Ponorogo 2026</title>
    <!-- Google Fonts & Bootstrap 5 & FontAwesome -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --bg-deep: #07090e;
            --bg-card: rgba(18, 24, 38, 0.7);
            --gold-primary: #fbbf24;
            --gold-gradient: linear-gradient(135deg, #f59e0b 0%, #fbbf24 50%, #fef08a 100%);
            --border-glow: rgba(251, 191, 36, 0.25);
        }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-deep);
            color: #f8fafc;
            overflow-x: hidden;
            position: relative;
        }
        /* Background Glow Ornaments */
        body::before {
            content: '';
            position: absolute;
            top: -10%;
            left: 50%;
            transform: translateX(-50%);
            width: 600px;
            height: 300px;
            background: radial-gradient(circle, rgba(245, 158, 11, 0.12) 0%, rgba(7, 9, 14, 0) 70%);
            z-index: -1;
        }
        /* Navbar Modern Glassmorphism */
        .navbar-custom {
            background: rgba(7, 9, 14, 0.85);
            backdrop-filter: blur(16px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.06);
        }
        .navbar-brand {
            letter-spacing: 0.5px;
            background: var(--gold-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        /* Hero Section */
        .hero-section {
            padding: 110px 0 90px 0;
        }
        .badge-gold {
            background: rgba(245, 158, 11, 0.08);
            color: #fbbf24;
            border: 1px solid rgba(245, 158, 11, 0.25);
            letter-spacing: 2px;
            font-size: 0.75rem;
            padding: 10px 20px;
            border-radius: 50rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
        }
        /* Kartu Jadwal Premium */
        .card-gold-glow {
            background: var(--bg-card);
            backdrop-filter: blur(12px);
            border: 1px solid var(--border-glow);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.5);
            transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
        }
        .card-gold-glow:hover {
            border-color: rgba(251, 191, 36, 0.6);
            transform: translateY(-3px);
            box-shadow: 0 20px 40px rgba(245, 158, 11, 0.15);
        }
        /* Tombol Mewah */
        .btn-gold {
            background: var(--gold-gradient);
            color: #07090e;
            font-weight: 700;
            border: none;
            padding: 14px 32px;
            border-radius: 12px;
            transition: all 0.3s ease;
            box-shadow: 0 6px 20px rgba(245, 158, 11, 0.35);
        }
        .btn-gold:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(245, 158, 11, 0.55);
            color: #07090e;
        }
        .btn-outline-gold {
            border: 1px solid rgba(245, 158, 11, 0.4);
            color: #fbbf24;
            background: rgba(255, 255, 255, 0.02);
            padding: 14px 32px;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-outline-gold:hover {
            background: rgba(245, 158, 11, 0.1);
            color: #fef08a;
            border-color: #fbbf24;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark navbar-custom sticky-top py-3">
    <div class="container">
        <a class="navbar-brand fw-extrabold fs-5" href="index.php">
            <i class="fa-solid fa-crown text-warning me-2"></i> KAKANG SENDUK PONOROGO
        </a>
        <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <div class="navbar-nav ms-auto align-items-center gap-3">
                <a class="nav-link text-light px-2" href="kontestan.php">Kontestan</a>
                <a class="nav-link text-light px-2" href="voting.php">Bilik Suara</a>
                <a class="nav-link text-light px-2" href="klasemen.php">Klasemen Live</a>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <?php if ($_SESSION['role'] === 'admin'): ?>
                        <a class="btn btn-sm btn-outline-warning px-3 rounded-pill" href="../admin/dashboard.php">Admin Panel</a>
                    <?php endif; ?>
                    <a class="btn btn-sm btn-danger px-3 rounded-pill" href="../auth/logout.php"><i class="fa-solid fa-power-off"></i> Logout</a>
                <?php else: ?>
                    <a class="btn btn-gold btn-sm px-4 rounded-pill" href="../auth/login.php">Login / Daftar</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<!-- Hero Section -->
<div class="hero-section text-center">
    <div class="container">
        <span class="badge badge-gold mb-4 text-uppercase fw-bold">
            <i class="fa-solid fa-star text-warning me-1"></i> Duta Wisata Kabupaten Ponorogo 2026
        </span>
        <h1 class="display-3 fw-extrabold mb-4 text-white" style="letter-spacing: -1.5px;">
            Pemilihan <span style="background: var(--gold-gradient); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Kakang Senduk</span>
        </h1>
        <p class="text-secondary mx-auto mb-5 fs-6" style="max-width: 650px; font-weight: 300; line-height: 1.8;">
            Aplikasi resmi pemungutan suara duta wisata daerah berbasis digital. Berikan dukungan terbaik Anda untuk putra-putri pilihan Bumi Reog dalam menginspirasi pariwisata dan budaya.
        </p>

        <!-- Kartu Jadwal Informasi -->
        <div class="card card-gold-glow mx-auto p-4 mb-5 text-start" style="max-width: 540px;">
            <div class="d-flex align-items-center gap-3">
                <div class="fs-2 text-warning bg-warning bg-opacity-10 p-3 rounded-4">
                    <i class="fa-regular fa-calendar-days"></i>
                </div>
                <div>
                    <h6 class="text-warning fw-bold mb-1">Status Periode Voting</h6>
                    <?php if ($infoJadwal): ?>
                        <p class="mb-1 text-light fw-medium small"><?= e($infoJadwal['nama_periode']) ?></p>
                        <p class="text-secondary small mb-2"><i class="fa-regular fa-clock me-1"></i> <?= e($infoJadwal['tanggal_mulai']) ?> s/d <?= e($infoJadwal['tanggal_selesai']) ?></p>
                        <span class="badge <?= $periode ? 'bg-success text-white' : 'bg-danger text-white' ?> px-3 py-1 rounded-pill fw-semibold">
                            <i class="fa-solid fa-circle fa-2xs me-1"></i> <?= $periode ? 'VOTING DIBUKA' : 'VOTING DITUTUP' ?>
                        </span>
                    <?php else: ?>
                        <p class="mb-0 text-muted small">Belum ada jadwal aktif.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Tombol Menu Utama -->
        <div class="d-flex justify-content-center gap-3 flex-wrap">
            <a href="kontestan.php" class="btn btn-outline-gold">
                <i class="fa-solid fa-users-viewfinder me-2"></i> Lihat Kontestan
            </a>
            <a href="voting.php" class="btn btn-gold">
                <i class="fa-solid fa-check-to-slot me-2"></i> Voting Sekarang
            </a>
            <a href="klasemen.php" class="btn btn-outline-gold">
                <i class="fa-solid fa-ranking-star me-2"></i> Klasemen Live
            </a>
        </div>
    </div>
</div>

<!-- Footer -->
<footer class="text-center py-4 border-top border-secondary border-opacity-10 text-secondary small mt-5">
    <p class="mb-0">&copy; 2026 Pemilihan Kakang Senduk Ponorogo • Web Development PPLG</p>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>