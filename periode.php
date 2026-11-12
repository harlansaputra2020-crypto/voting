<?php
require_once '../config/koneksi.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

$error = '';
$sukses = '';

// Proses Tambah / Ubah Periode
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_periode    = trim($_POST['nama_periode']);
    $tanggal_mulai   = $_POST['tanggal_mulai'];
    $tanggal_selesai = $_POST['tanggal_selesai'];
    $status          = $_POST['status'];

    if ($status === 'Aktif') {
        mysqli_query($conn, "UPDATE periode_voting SET status = 'Nonaktif'");
    }

    if (isset($_POST['id']) && !empty($_POST['id'])) {
        $id = (int)$_POST['id'];
        $stmt = mysqli_prepare($conn, "UPDATE periode_voting SET nama_periode = ?, tanggal_mulai = ?, tanggal_selesai = ?, status = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "ssssi", $nama_periode, $tanggal_mulai, $tanggal_selesai, $status, $id);
        mysqli_stmt_execute($stmt);
        $sukses = "Periode voting berhasil diperbarui!";
    } else {
        $stmt = mysqli_prepare($conn, "INSERT INTO periode_voting (nama_periode, tanggal_mulai, tanggal_selesai, status) VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "ssss", $nama_periode, $tanggal_mulai, $tanggal_selesai, $status);
        mysqli_stmt_execute($stmt);
        $sukses = "Periode voting baru berhasil ditambahkan!";
    }
}

// Proses Hapus
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    mysqli_query($conn, "DELETE FROM periode_voting WHERE id = $id");
    header("Location: periode.php?status=sukses_hapus");
    exit;
}

$result = mysqli_query($conn, "SELECT * FROM periode_voting ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Periode Voting - Admin Panel</title>
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
        .form-control, .form-select { background-color: rgba(18, 24, 38, 0.8); border: 1px solid rgba(255, 255, 255, 0.08); color: #fff; border-radius: 12px; padding: 12px 18px; }
        .form-control:focus, .form-select:focus { background-color: rgba(18, 24, 38, 1); border-color: #fbbf24; color: #fff; box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.2); }
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
                <a class="nav-link nav-link-admin" href="pemilih.php">Data Pemilih</a>
                <a class="nav-link nav-link-admin active" href="periode.php">Periode Voting</a>
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
            <h2 class="fw-extrabold text-white mb-1"><i class="fa-solid fa-calendar-days text-warning me-2"></i> Pengaturan Periode Voting</h2>
            <p class="text-secondary small mb-0">Tentukan jadwal aktif dan nonaktif pembukaan bilik suara</p>
        </div>
    </div>

    <?php if ($sukses): ?>
        <div class="alert alert-success bg-success bg-opacity-25 border-0 text-success rounded-3 mb-4"><?= $sukses ?></div>
    <?php endif; ?>

    <div class="row g-4">
        <!-- Form Tambah/Edit -->
        <div class="col-lg-4">
            <div class="card card-admin p-4">
                <h5 class="fw-bold text-warning mb-3"><i class="fa-solid fa-plus-circle me-1"></i> Tambah Periode Baru</h5>
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label small text-secondary fw-bold">Nama Periode</label>
                        <input type="text" name="nama_periode" class="form-control" placeholder="Contoh: Grand Final 2026" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small text-secondary fw-bold">Tanggal Mulai</label>
                        <input type="datetime-local" name="tanggal_mulai" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small text-secondary fw-bold">Tanggal Selesai</label>
                        <input type="datetime-local" name="tanggal_selesai" class="form-control" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label small text-secondary fw-bold">Status</label>
                        <select name="status" class="form-select">
                            <option value="Aktif">Aktif</option>
                            <option value="Nonaktif">Nonaktif</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-warning w-100 py-3 fw-bold rounded-3 shadow-sm">Simpan Periode</button>
                </form>
            </div>
        </div>

        <!-- Tabel Daftar Periode -->
        <div class="col-lg-8">
            <div class="card card-admin p-0">
                <div class="table-responsive">
                    <table class="table table-dark table-hover align-middle mb-0 bg-transparent">
                        <thead>
                            <tr class="text-secondary small border-bottom border-secondary border-opacity-10">
                                <th class="ps-4 py-3">Nama Periode</th>
                                <th class="py-3">Mulai</th>
                                <th class="py-3">Selesai</th>
                                <th class="py-3">Status</th>
                                <th class="text-end pe-4 py-3">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($result) === 0): ?>
                            <tr><td colspan="5" class="text-center py-4 text-muted">Belum ada periode voting.</td></tr>
                            <?php endif; ?>
                            <?php while ($r = mysqli_fetch_assoc($result)): ?>
                            <tr class="border-bottom border-secondary border-opacity-10">
                                <td class="ps-4 py-3 fw-bold text-white"><?= e($r['nama_periode']) ?></td>
                                <td class="text-secondary small"><?= e($r['tanggal_mulai']) ?></td>
                                <td class="text-secondary small"><?= e($r['tanggal_selesai']) ?></td>
                                <td>
                                    <span class="badge <?= $r['status'] === 'Aktif' ? 'bg-success' : 'bg-secondary' ?> px-2 py-1">
                                        <?= e($r['status']) ?>
                                    </span>
                                </td>
                                <td class="text-end pe-4">
                                    <a href="periode.php?hapus=<?= $r['id'] ?>" class="btn btn-sm btn-outline-danger px-3 rounded-pill" onclick="return confirm('Hapus periode ini?');"><i class="fa-solid fa-trash"></i> Hapus</a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>