<?php
require_once '../config/koneksi.php';

// Proteksi Halaman Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

// Fungsi Ambil Klasemen Admin
function getKlasemenAdmin($conn, $kategori) {
    $sql = "SELECT k.id, k.nomor_peserta, k.nama, k.kategori, k.foto, k.asal_instansi, COUNT(v.id) AS total_suara
            FROM kontestan k
            LEFT JOIN voting v ON k.id = v.kontestan_id
            WHERE k.kategori = ?
            GROUP BY k.id, k.nomor_peserta, k.nama, k.kategori, k.foto, k.asal_instansi
            ORDER BY total_suara DESC, k.nomor_peserta ASC";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $kategori);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $data = [];
    $total = 0;
    while ($row = mysqli_fetch_assoc($res)) {
        $total += (int)$row['total_suara'];
        $data[] = $row;
    }
    return ['rows' => $data, 'total' => $total];
}

$kakang = getKlasemenAdmin($conn, 'Kakang');
$senduk = getKlasemenAdmin($conn, 'Senduk');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Hasil & Klasemen Voting - Admin Panel</title>
    <!-- Google Fonts, Bootstrap 5 & FontAwesome -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
        /* Kartu Klasemen Admin */
        .card-klasemen {
            background: var(--bg-card);
            backdrop-filter: blur(12px);
            border: 1px solid var(--border-subtle);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.45);
        }
        .progress {
            background-color: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
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
                <a class="nav-link nav-link-admin" href="dashboard.php">Dashboard</a>
                <a class="nav-link nav-link-admin" href="kontestan.php">Data Kontestan</a>
                <a class="nav-link nav-link-admin" href="pemilih.php">Data Pemilih</a>
                <a class="nav-link nav-link-admin" href="periode.php">Periode Voting</a>
                <a class="nav-link nav-link-admin active" href="hasil-voting.php">Hasil & Export</a>
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
    <!-- Header Halaman -->
    <div class="d-flex justify-content-between align-items-center mb-5 flex-wrap gap-3">
        <div>
            <h2 class="fw-extrabold text-white mb-1" style="letter-spacing: -0.5px;">
                <i class="fa-solid fa-trophy text-warning me-2"></i> Hasil & Klasemen Akhir Voting
            </h2>
            <p class="text-secondary small mb-0">Rekapitulasi perolehan suara resmi kontestan Kakang Senduk Ponorogo</p>
        </div>
        <div class="d-flex gap-2">
            <button onclick="window.print()" class="btn btn-outline-warning btn-sm px-4 rounded-pill fw-bold">
                <i class="fa-solid fa-print me-1"></i> Cetak Laporan
            </button>
        </div>
    </div>

    <!-- Grid Klasemen Admin -->
    <div class="row g-4">
        <?php foreach (['Kakang' => $kakang, 'Senduk' => $senduk] as $kat => $grup): ?>
        <div class="col-lg-6">
            <div class="card card-klasemen h-100">
                <div class="card-header bg-warning bg-opacity-10 border-bottom border-warning border-opacity-20 text-warning fw-bold d-flex justify-content-between p-3 px-4">
                    <span><i class="fa-solid fa-ranking-star me-2"></i> KLASEMEN <?= strtoupper($kat) ?></span>
                    <span class="badge bg-warning text-dark px-3">Total Suara: <?= $grup['total'] ?></span>
                </div>
                <div class="card-body p-0 table-responsive">
                    <table class="table table-dark table-hover align-middle mb-0 bg-transparent">
                        <thead>
                            <tr class="text-secondary small border-bottom border-secondary border-opacity-10">
                                <th class="ps-4 py-3">Rank</th>
                                <th class="py-3">Kontestan</th>
                                <th class="py-3">Perolehan</th>
                                <th class="pe-4 py-3" style="width: 140px;">Persentase</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $rank = 1;
                            if (empty($grup['rows'])):
                            ?>
                            <tr><td colspan="4" class="text-center py-4 text-muted">Belum ada data suara masuk.</td></tr>
                            <?php
                            endif;
                            foreach ($grup['rows'] as $r): 
                                $persen = $grup['total'] > 0 ? round(($r['total_suara'] / $grup['total']) * 100, 1) : 0;
                            ?>
                            <tr class="border-bottom border-secondary border-opacity-10">
                                <td class="ps-4">
                                    <span class="badge <?= $rank === 1 ? 'bg-warning text-dark' : ($rank === 2 ? 'bg-light text-dark' : ($rank === 3 ? 'bg-secondary text-white' : 'bg-dark text-muted border border-secondary')) ?> px-2 py-1">
                                        #<?= $rank++ ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-3 py-1">
                                        <img src="../uploads/<?= e($r['foto']) ?>" onerror="this.src='https://placehold.co/50x50/121826/fbbf24?text=<?= urlencode($r['nomor_peserta']) ?>'" class="rounded-circle border border-warning border-opacity-25" width="44" height="44" style="object-fit:cover;">
                                        <div>
                                            <div class="fw-bold text-white small"><?= e($r['nomor_peserta']) ?> - <?= e($r['nama']) ?></div>
                                            <small class="text-secondary" style="font-size: 0.75rem;"><?= e($r['asal_instansi']) ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td class="fw-bold text-warning"><?= $r['total_suara'] ?> Suara</td>
                                <td class="pe-4">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="progress flex-grow-1" style="height: 8px;">
                                            <div class="progress-bar bg-warning" role="progressbar" style="width: <?= $persen ?>%;"></div>
                                        </div>
                                        <span class="small text-secondary fw-semibold" style="font-size: 0.75rem; width: 35px;"><?= $persen ?>%</span>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>