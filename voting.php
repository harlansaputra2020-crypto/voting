<?php
require_once '../config/koneksi.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = (int)$_SESSION['user_id'];
$periode = getPeriodeAktif($conn);

$voted = [];
$qVoted = mysqli_query($conn, "SELECT v.kategori, k.nama, k.nomor_peserta FROM voting v JOIN kontestan k ON v.kontestan_id = k.id WHERE v.user_id = $user_id");
while ($v = mysqli_fetch_assoc($qVoted)) {
    $voted[$v['kategori']] = $v;
}

$qKontestan = mysqli_query($conn, "SELECT * FROM kontestan ORDER BY kategori ASC, nomor_peserta ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bilik Suara Pemilih - Kakang Senduk</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --bg-deep: #07090e; --gold-gradient: linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%); }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: var(--bg-deep); color: #f8fafc; }
        .navbar-custom { background: rgba(7, 9, 14, 0.85); backdrop-filter: blur(16px); border-bottom: 1px solid rgba(255, 255, 255, 0.06); }
        .card-voting { background: rgba(18, 24, 38, 0.6); backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.06); border-radius: 18px; overflow: hidden; transition: all 0.3s ease; }
        .card-voting:hover { border-color: rgba(251, 191, 36, 0.4); transform: translateY(-4px); }
        .btn-gold { background: var(--gold-gradient); color: #07090e; font-weight: 700; border: none; border-radius: 12px; }
        .btn-gold:hover { opacity: 0.9; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark navbar-custom sticky-top py-3">
    <div class="container">
        <a class="navbar-brand fw-bold text-warning" href="index.php"><i class="fa-solid fa-crown me-2"></i> KAKANG SENDUK</a>
        <div class="navbar-nav ms-auto gap-3">
            <a class="nav-link text-light" href="kontestan.php">Kontestan</a>
            <a class="nav-link text-warning fw-bold" href="voting.php">Bilik Suara</a>
            <a class="nav-link text-light" href="klasemen.php">Klasemen</a>
            <a class="btn btn-sm btn-outline-light border-secondary text-light px-3 rounded-pill" href="index.php">Beranda</a>
        </div>
    </div>
</nav>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h2 class="fw-extrabold text-white mb-1">Bilik Suara Pemilih</h2>
            <p class="text-secondary small mb-0">Pemilih Aktif: <strong class="text-warning"><?= e($_SESSION['nama']) ?></strong> (Maksimal 1 Kakang & 1 Senduk)</p>
        </div>
        <a href="klasemen.php" class="btn btn-outline-warning btn-sm px-4 rounded-pill">Lihat Klasemen Live →</a>
    </div>

    <?php if (isset($_GET['status']) && $_GET['status'] === 'sukses'): ?>
        <div class="alert alert-success border-0 bg-success bg-opacity-25 text-success fw-medium p-3 rounded-3 mb-4">
            <i class="fa-solid fa-circle-check me-2"></i> <strong>Berhasil!</strong> Suara pilihan Anda telah tercatat dan terkunci secara aman.
        </div>
    <?php elseif (isset($_GET['error'])): ?>
        <div class="alert alert-danger border-0 bg-danger bg-opacity-25 text-danger fw-medium p-3 rounded-3 mb-4">
            <i class="fa-solid fa-triangle-exclamation me-2"></i> <?= e($_GET['error']) ?>
        </div>
    <?php endif; ?>

    <?php if (!$periode): ?>
        <div class="alert alert-danger border-0 bg-danger bg-opacity-25 text-danger fw-medium p-3 rounded-3 mb-4">
            <i class="fa-solid fa-ban me-2"></i> <strong>Voting Ditutup!</strong> Saat ini tidak ada periode voting aktif yang dijadwalkan oleh panitia.
        </div>
    <?php endif; ?>

    <!-- Status Suara User -->
    <div class="row g-3 mb-5">
        <div class="col-md-6">
            <div class="p-3 rounded-4 border <?= isset($voted['Kakang']) ? 'border-success bg-success bg-opacity-10 text-success' : 'border-secondary bg-secondary bg-opacity-10 text-secondary' ?>">
                <div class="small fw-bold text-uppercase">Status Kategori Kakang</div>
                <div class="fs-6 fw-semibold mt-1">
                    <?= isset($voted['Kakang']) ? '<i class="fa-solid fa-check me-1"></i> Sudah memilih: ' . e($voted['Kakang']['nomor_peserta'] . ' - ' . $voted['Kakang']['nama']) : '<i class="fa-solid fa-clock me-1"></i> Belum memberikan suara' ?>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="p-3 rounded-4 border <?= isset($voted['Senduk']) ? 'border-success bg-success bg-opacity-10 text-success' : 'border-secondary bg-secondary bg-opacity-10 text-secondary' ?>">
                <div class="small fw-bold text-uppercase">Status Kategori Senduk</div>
                <div class="fs-6 fw-semibold mt-1">
                    <?= isset($voted['Senduk']) ? '<i class="fa-solid fa-check me-1"></i> Sudah memilih: ' . e($voted['Senduk']['nomor_peserta'] . ' - ' . $voted['Senduk']['nama']) : '<i class="fa-solid fa-clock me-1"></i> Belum memberikan suara' ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Grid Pilihan Voting -->
    <div class="row g-4">
        <?php while ($k = mysqli_fetch_assoc($qKontestan)): 
            $sudahKategoriIni = isset($voted[$k['kategori']]);
        ?>
        <div class="col-md-4 col-lg-3">
            <div class="card card-voting h-100">
                <div style="height:250px; overflow:hidden; position:relative;">
                    <img src="../uploads/<?= e($k['foto']) ?>" onerror="this.src='https://placehold.co/400x450/121826/fbbf24?text=<?= urlencode($k['nomor_peserta']) ?>'" class="w-100 h-100" style="object-fit:cover;" alt="Foto">
                    <div class="position-absolute top-0 start-0 m-3">
                        <span class="badge bg-warning text-dark fw-bold"><?= e($k['nomor_peserta']) ?></span>
                        <span class="badge bg-info text-dark fw-bold"><?= e($k['kategori']) ?></span>
                    </div>
                </div>
                <div class="card-body d-flex flex-column p-4">
                    <h5 class="fw-bold text-white mb-1"><?= e($k['nama']) ?></h5>
                    <p class="small text-warning mb-3"><?= e($k['asal_instansi']) ?></p>

                    <form action="../proses/simpan-voting.php" method="POST" class="mt-auto" onsubmit="return confirm('Konfirmasi: Apakah Anda yakin ingin mendukung <?= e($k['nama']) ?>? Pilihan tidak dapat diubah setelah disimpan.');">
                        <input type="hidden" name="kontestan_id" value="<?= $k['id'] ?>">
                        <?php if (!$periode): ?>
                            <button type="button" class="btn btn-secondary w-100 btn-sm py-2 rounded-3" disabled>Periode Nonaktif</button>
                        <?php elseif ($sudahKategoriIni): ?>
                            <button type="button" class="btn btn-success w-100 btn-sm py-2 fw-semibold rounded-3" disabled><i class="fa-solid fa-lock me-1"></i> Terkunci</button>
                        <?php else: ?>
                            <button type="submit" class="btn btn-gold w-100 btn-sm py-2 fw-bold rounded-3"><i class="fa-solid fa-check me-1"></i> Pilih <?= e($k['nomor_peserta']) ?></button>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>