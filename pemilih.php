<?php
require_once '../config/koneksi.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

// Proses Hapus Pemilih
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    mysqli_query($conn, "DELETE FROM users WHERE id = $id AND role = 'pemilih'");
    header("Location: pemilih.php?status=sukses_hapus");
    exit;
}

$result = mysqli_query($conn, "SELECT * FROM users WHERE role = 'pemilih' ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Data Pemilih - Admin Panel</title>
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
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: var(--bg-deep); color: #f8fafc; min-height: 100vh; position: relative; overflow-x: hidden; }
        body::before { content: ''; position: absolute; top: -120px; left: 50%; transform: translateX(-50%); width: 700px; height: 300px; background: radial-gradient(circle, rgba(245, 158, 11, 0.1) 0%, rgba(7, 9, 14, 0) 70%); z-index: -1; }
        .navbar-custom { background: rgba(7, 9, 14, 0.85); backdrop-filter: blur(16px); border-bottom: 1px solid var(--border-subtle); }
        .navbar-brand { letter-spacing: 0.5px; background: var(--gold-gradient); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .nav-link-admin { color: #cbd5e1 !important; font-weight: 500; font-size: 0.9rem; transition: color 0.25s ease; }
        .nav-link-admin:hover, .nav-link-admin.active { color: #fbbf24 !important; }
        .card-admin { background: var(--bg-card); backdrop-filter: blur(12px); border: 1px solid var(--border-subtle); border-radius: 20px; overflow: hidden; box-shadow: 0 15px 35px rgba(0, 0, 0, 0.45); }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark navbar-custom sticky-top py-3">
    <div class="container">
        <a class="navbar-brand fw-extrabold fs-5" href="dashboard.php"><i class="fa-solid fa-shield-halved text-warning me-2"></i> ADMIN PANEL</a>
        <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#adminNav"><span class="navbar-toggler-icon"></span></button>
        <div class="collapse navbar-collapse" id="adminNav">
            <div class="navbar-nav ms-auto align-items-center gap-3">
                <a class="nav-link nav-link-admin" href="dashboard.php">Dashboard</a>
                <a class="nav-link nav-link-admin" href="kontestan.php">Data Kontestan</a>
                <a class="nav-link nav-link-admin active" href="pemilih.php">Data Pemilih</a>
                <a class="nav-link nav-link-admin" href="periode.php">Periode Voting</a>
                <a class="nav-link nav-link-admin" href="hasil-voting.php">Hasil & Export</a>
                <div class="d-flex gap-2 ms-lg-2">
                    <a class="btn btn-sm btn-outline-warning px-3 rounded-pill" href="../pages/index.php" target="_blank"><i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Lihat Web</a>
                    <a class="btn btn-sm btn-danger px-3 rounded-pill" href="../auth/logout.php"><i class="fa-solid fa-power-off me-1"></i> Logout</a>
                </div>
            </div>
        </div>
    </div>
</nav>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h2 class="fw-extrabold text-white mb-1"><i class="fa-solid fa-user-check text-warning me-2"></i> Manajemen Data Pemilih</h2>
            <p class="text-secondary small mb-0">Daftar akun masyarakat/siswa yang terdaftar sebagai pemilih sah</p>
        </div>
    </div>

    <?php if (isset($_GET['status']) && $_GET['status'] === 'sukses_hapus'): ?>
        <div class="alert alert-success bg-success bg-opacity-25 border-0 text-success rounded-3 mb-4">Akun pemilih berhasil dihapus.</div>
    <?php endif; ?>

    <div class="card card-admin p-0">
        <div class="table-responsive">
            <table class="table table-dark table-hover align-middle mb-0 bg-transparent">
                <thead>
                    <tr class="text-secondary small border-bottom border-secondary border-opacity-10">
                        <th class="ps-4 py-3">ID</th>
                        <th class="py-3">Nama Lengkap</th>
                        <th class="py-3">Email</th>
                        <th class="py-3">No. HP</th>
                        <th class="py-3">Terdaftar Sejak</th>
                        <th class="text-end pe-4 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($result) === 0): ?>
                    <tr><td colspan="6" class="text-center py-4 text-muted">Belum ada pemilih terdaftar.</td></tr>
                    <?php endif; ?>
                    <?php while ($r = mysqli_fetch_assoc($result)): ?>
                    <tr class="border-bottom border-secondary border-opacity-10">
                        <td class="ps-4 py-3 text-warning fw-bold">#<?= $r['id'] ?></td>
                        <td class="fw-bold text-white"><?= e($r['nama']) ?></td>
                        <td class="text-secondary"><?= e($r['email']) ?></td>
                        <td class="text-secondary"><?= e($r['no_hp'] ?: '-') ?></td>
                        <td class="text-secondary small"><?= e($r['created_at']) ?></td>
                        <td class="text-end pe-4">
                            <a href="pemilih.php?hapus=<?= $r['id'] ?>" class="btn btn-sm btn-outline-danger px-3 rounded-pill" onclick="return confirm('Yakin ingin menghapus akun pemilih ini?');"><i class="fa-solid fa-trash"></i> Hapus</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>