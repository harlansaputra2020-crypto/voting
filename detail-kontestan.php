<?php
require_once '../config/koneksi.php';

$id = (int)($_GET['id'] ?? 0);
$stmt = mysqli_prepare($conn, "SELECT * FROM kontestan WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$k = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$k) {
    die("<div class='bg-dark text-light min-vh-100 d-flex flex-column align-items-center justify-content-center'><h3>Kontestan tidak ditemukan.</h3><a href='kontestan.php' class='btn btn-warning mt-3'>Kembali</a></div>");
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Profil <?= e($k['nama']) ?> - Kakang Senduk Ponorogo</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --bg-deep: #07090e; --gold-gradient: linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%); }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: var(--bg-deep); color: #f8fafc; }
        .navbar-custom { background: rgba(7, 9, 14, 0.85); backdrop-filter: blur(16px); border-bottom: 1px solid rgba(255, 255, 255, 0.06); }
        .card-detail { background: rgba(18, 24, 38, 0.6); backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 20px; overflow: hidden; }
        .btn-gold { background: var(--gold-gradient); color: #07090e; font-weight: 700; border: none; border-radius: 12px; transition: all 0.3s ease; }
        .btn-gold:hover { opacity: 0.9; box-shadow: 0 6px 20px rgba(245, 158, 11, 0.4); }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark navbar-custom sticky-top py-3">
    <div class="container">
        <a class="navbar-brand fw-bold text-warning" href="index.php"><i class="fa-solid fa-crown me-2"></i> KAKANG SENDUK</a>
        <div class="navbar-nav ms-auto">
            <a class="btn btn-sm btn-outline-light px-3 border-secondary text-light rounded-pill" href="kontestan.php">← Kembali ke Kontestan</a>
        </div>
    </div>
</nav>

<div class="container py-5">
    <div class="card card-detail border-secondary border-opacity-25 shadow-lg">
        <div class="row g-0">
            <div class="col-md-5">
                <img src="../uploads/<?= e($k['foto']) ?>" onerror="this.src='https://placehold.co/500x650/121826/fbbf24?text=<?= urlencode($k['nomor_peserta']) ?>'" class="img-fluid w-100 h-100" style="object-fit:cover; min-height:450px;" alt="Foto Profil">
            </div>
            <div class="col-md-7 p-4 p-lg-5 d-flex flex-column justify-content-between">
                <div>
                    <div class="d-flex gap-2 mb-3">
                        <span class="badge bg-warning text-dark px-3 py-2 fw-bold rounded-2"><?= e($k['nomor_peserta']) ?></span>
                        <span class="badge bg-info bg-opacity-75 text-dark px-3 py-2 fw-bold rounded-2">Kategori <?= e($k['kategori']) ?></span>
                    </div>
                    <h1 class="fw-extrabold text-white mb-2"><?= e($k['nama']) ?></h1>
                    <p class="text-warning fw-semibold mb-1"><i class="fa-solid fa-school me-2"></i> <?= e($k['asal_instansi']) ?></p>
                    <p class="text-secondary small mb-4"><i class="fa-solid fa-cake-candles me-2"></i> <?= e($k['ttl'] ?: 'Informasi TTL tidak tersedia') ?></p>
                    
                    <hr class="border-secondary opacity-25 my-4">

                    <h6 class="text-warning fw-bold text-uppercase small mb-2">Tentang Finalis</h6>
                    <p class="text-secondary small mb-4" style="line-height: 1.8;"><?= nl2br(e($k['deskripsi'])) ?></p>

                    <div class="row g-3 mb-4">
                        <div class="col-12">
                            <h6 class="text-warning fw-bold small text-uppercase">Visi</h6>
                            <p class="text-secondary small bg-dark bg-opacity-50 p-3 rounded-3 mb-0"><?= nl2br(e($k['visi'])) ?></p>
                        </div>
                        <div class="col-12">
                            <h6 class="text-warning fw-bold small text-uppercase">Misi</h6>
                            <p class="text-secondary small bg-dark bg-opacity-50 p-3 rounded-3 mb-0"><?= nl2br(e($k['misi'])) ?></p>
                        </div>
                        <?php if (!empty($k['prestasi'])): ?>
                        <div class="col-12">
                            <h6 class="text-warning fw-bold small text-uppercase">Prestasi & Pengalaman</h6>
                            <p class="text-secondary small bg-dark bg-opacity-50 p-3 rounded-3 mb-0"><?= nl2br(e($k['prestasi'])) ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="mt-4 pt-3 border-top border-secondary border-opacity-25">
                    <a href="voting.php?pilih=<?= $k['id'] ?>" class="btn btn-gold w-100 py-3 fw-bold shadow-sm">
                        <i class="fa-solid fa-check-to-slot me-2"></i> Berikan Suara (Vote) untuk <?= e($k['nama']) ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>