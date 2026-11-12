<?php
require_once '../config/koneksi.php';

// Proteksi Halaman Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

// Ambil statistik utama
$totalKontestan = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM kontestan"))['total'];
$totalPemilih   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM users WHERE role = 'pemilih'"))['total'];
$suaraKakang    = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM voting WHERE kategori = 'Kakang'"))['total'];
$suaraSenduk    = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM voting WHERE kategori = 'Senduk'"))['total'];

// Cek periode aktif
$periode = getPeriodeAktif($conn);

// Data untuk Grafik Perolehan Suara
$qGrafik = mysqli_query($conn, "
    SELECT k.nomor_peserta, k.nama, k.kategori, COUNT(v.id) AS jumlah_suara
    FROM kontestan k
    LEFT JOIN voting v ON k.id = v.kontestan_id
    GROUP BY k.id, k.nomor_peserta, k.nama, k.kategori
    ORDER BY k.kategori ASC, k.nomor_peserta ASC
");

$labels = [];
$dataSuara = [];
while ($g = mysqli_fetch_assoc($qGrafik)) {
    $labels[] = $g['nomor_peserta'] . ' ' . $g['nama'];
    $dataSuara[] = (int)$g['jumlah_suara'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard Admin - Kakang Senduk Ponorogo</title>
    <!-- Google Fonts, Bootstrap 5, FontAwesome & Chart.js -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --bg-deep: #07090e;
            --bg-card: rgba(18, 24, 38, 0.65);
            --gold-gradient: linear-gradient(135deg, #f59e0b 0%, #fbbf24 50%, #fef08a 100%);
            --border-subtle: rgba(255, 255, 255, 0.08);
            --border-gold: rgba(251, 191, 36, 0.3);
        }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-deep);
            color: #f8fafc;
            min-height: 100vh;
            position: relative;
            overflow-x: hidden;
        }
        body::before {
            content: '';
            position: absolute;
            top: -120px;
            left: 50%;
            transform: translateX(-50%);
            width: 700px;
            height: 300px;
            background: radial-gradient(circle, rgba(245, 158, 11, 0.1) 0%, rgba(7, 9, 14, 0) 70%);
            z-index: -1;
        }
        /* Navbar Admin Glassmorphism */
        .navbar-custom {
            background: rgba(7, 9, 14, 0.85);
            backdrop-filter: blur(16px);
            border-bottom: 1px solid var(--border-subtle);
        }
        .navbar-brand {
            letter-spacing: 0.5px;
            background: var(--gold-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .nav-link-admin {
            color: #cbd5e1 !important;
            font-weight: 500;
            font-size: 0.9rem;
            transition: color 0.25s ease;
        }
        .nav-link-admin:hover, .nav-link-admin.active {
            color: #fbbf24 !important;
        }
        /* Banner Status Periode */
        .status-banner {
            background: rgba(16, 185, 129, 0.12);
            border: 1px solid rgba(16, 185, 129, 0.35);
            border-radius: 16px;
            backdrop-filter: blur(10px);
        }
        .status-banner.inactive {
            background: rgba(239, 68, 68, 0.12);
            border: 1px solid rgba(239, 68, 68, 0.35);
        }
        /* Kartu Statistik Mewah */
        .stat-card {
            background: var(--bg-card);
            backdrop-filter: blur(12px);
            border: 1px solid var(--border-subtle);
            border-radius: 18px;
            padding: 24px;
            transition: all 0.35s cubic-bezier(0.165, 0.84, 0.44, 1);
            position: relative;
            overflow: hidden;
        }
        .stat-card:hover {
            transform: translateY(-4px);
            border-color: var(--border-gold);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.5);
        }
        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }
        /* Kartu Grafik */
        .chart-card {
            background: var(--bg-card);
            backdrop-filter: blur(12px);
            border: 1px solid var(--border-subtle);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.45);
        }
    </style>
</head>
<body>

<!-- Navbar Admin -->
<nav class="navbar navbar-expand-lg navbar-dark navbar-custom sticky-top py-3">
    <div class="container">
        <a class="navbar-brand fw-extrabold fs-5" href="dashboard.php">
            <i class="fa-solid fa-shield-halved text-warning me-2"></i> ADMIN PANEL
        </a>
        <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#adminNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="adminNav">
            <div class="navbar-nav ms-auto align-items-center gap-3">
                <a class="nav-link nav-link-admin active" href="dashboard.php">Dashboard</a>
                <a class="nav-link nav-link-admin" href="kontestan.php">Data Kontestan</a>
                <a class="nav-link nav-link-admin" href="pemilih.php">Data Pemilih</a>
                <a class="nav-link nav-link-admin" href="periode.php">Periode Voting</a>
                <a class="nav-link nav-link-admin" href="hasil-voting.php">Hasil & Export</a>
                <div class="d-flex gap-2 ms-lg-2">
                    <a class="btn btn-sm btn-outline-warning px-3 rounded-pill" href="../pages/index.php" target="_blank">
                        <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Lihat Web
                    </a>
                    <a class="btn btn-sm btn-danger px-3 rounded-pill" href="../auth/logout.php">
                        <i class="fa-solid fa-power-off me-1"></i> Logout
                    </a>
                </div>
            </div>
        </div>
    </div>
</nav>

<div class="container py-5">
    <!-- Header Dashboard -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h2 class="fw-extrabold text-white mb-1" style="letter-spacing: -0.5px;">Dashboard Ikhtisar Pemilihan</h2>
            <p class="text-secondary small mb-0">Selamat datang kembali, <span class="text-warning fw-semibold"><?= e($_SESSION['nama']) ?></span>. Pantau statistik voting secara real-time.</p>
        </div>
        <a href="tambah-kontestan.php" class="btn btn-warning fw-bold px-4 py-2 rounded-pill shadow-sm">
            <i class="fa-solid fa-plus me-1"></i> Tambah Kontestan
        </a>
    </div>

    <!-- Banner Status Periode -->
    <div class="status-banner <?= $periode ? '' : 'inactive' ?> p-3 px-4 mb-5 d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div class="d-flex align-items-center gap-3">
            <span class="badge <?= $periode ? 'bg-success' : 'bg-danger' ?> rounded-pill px-3 py-2">
                <i class="fa-solid fa-circle fa-2xs me-1"></i> <?= $periode ? 'AKTIF' : 'DITUTUP' ?>
            </span>
            <span class="small fw-medium text-light">
                <strong>Status Periode Voting:</strong> 
                <?= $periode ? e($periode['nama_periode']) . ' (' . e($periode['tanggal_mulai']) . ' s/d ' . e($periode['tanggal_selesai']) . ')' : 'Tidak ada jadwal voting yang sedang berlangsung.' ?>
            </span>
        </div>
        <a href="periode.php" class="btn btn-sm btn-outline-light border-secondary rounded-pill px-3">Atur Jadwal →</a>
    </div>

    <!-- Grid 4 Kartu Statistik -->
    <div class="row g-4 mb-5">
        <div class="col-sm-6 col-lg-3">
            <div class="stat-card h-100">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <span class="text-secondary small fw-semibold text-uppercase">Total Kontestan</span>
                    <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                        <i class="fa-solid fa-users"></i>
                    </div>
                </div>
                <h2 class="fw-extrabold text-warning mb-0"><?= $totalKontestan ?></h2>
                <small class="text-secondary">Finalis terdaftar</small>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="stat-card h-100">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <span class="text-secondary small fw-semibold text-uppercase">Total Pemilih</span>
                    <div class="stat-icon bg-info bg-opacity-10 text-info">
                        <i class="fa-solid fa-user-check"></i>
                    </div>
                </div>
                <h2 class="fw-extrabold text-info mb-0"><?= $totalPemilih ?></h2>
                <small class="text-secondary">Akun pemilih terdaftar</small>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="stat-card h-100">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <span class="text-secondary small fw-semibold text-uppercase">Suara Kakang</span>
                    <div class="stat-icon bg-success bg-opacity-10 text-success">
                        <i class="fa-solid fa-user-tie"></i>
                    </div>
                </div>
                <h2 class="fw-extrabold text-success mb-0"><?= $suaraKakang ?></h2>
                <small class="text-secondary">Suara kategori putra</small>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="stat-card h-100">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <span class="text-secondary small fw-semibold text-uppercase">Suara Senduk</span>
                    <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                        <i class="fa-solid fa-crown"></i>
                    </div>
                </div>
                <h2 class="fw-extrabold text-warning mb-0"><?= $suaraSenduk ?></h2>
                <small class="text-secondary">Suara kategori putri</small>
            </div>
        </div>
    </div>

    <!-- Grafik Perolehan Suara -->
    <div class="chart-card p-4 p-lg-5">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <div>
                <h5 class="fw-bold text-warning mb-1"><i class="fa-solid fa-chart-column me-2"></i> Grafik Perolehan Suara Seluruh Kontestan</h5>
                <p class="text-secondary small mb-0">Distribusi suara masuk berdasarkan nomor dan nama peserta</p>
            </div>
            <a href="hasil-voting.php" class="btn btn-sm btn-outline-warning rounded-pill px-3">Detail Laporan & CSV</a>
        </div>
        <div style="height: 320px;">
            <canvas id="votingChart"></canvas>
        </div>
    </div>
</div>

<script>
const ctx = document.getElementById('votingChart').getContext('2d');
const goldGradient = ctx.createLinearGradient(0, 0, 0, 320);
goldGradient.addColorStop(0, 'rgba(251, 191, 36, 0.9)');
goldGradient.addColorStop(1, 'rgba(245, 158, 11, 0.25)');

new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?= json_encode($labels) ?>,
        datasets: [{
            label: 'Jumlah Suara',
            data: <?= json_encode($dataSuara) ?>,
            backgroundColor: goldGradient,
            borderColor: '#fbbf24',
            borderWidth: 1.5,
            borderRadius: 10,
            maxBarThickness: 55
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                labels: { color: '#cbd5e1', font: { family: 'Plus Jakarta Sans', weight: '600' } }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: { stepSize: 1, color: '#94a3b8', font: { family: 'Plus Jakarta Sans' } },
                grid: { color: 'rgba(255, 255, 255, 0.06)' }
            },
            x: {
                ticks: { color: '#e2e8f0', font: { family: 'Plus Jakarta Sans', weight: '500' } },
                grid: { display: false }
            }
        }
    }
});
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>