<?php
require_once '../config/koneksi.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nomor_peserta = trim($_POST['nomor_peserta']);
    $nama          = trim($_POST['nama']);
    $kategori      = $_POST['kategori'];
    $asal_instansi = trim($_POST['asal_instansi']);
    $ttl           = trim($_POST['ttl']);
    $deskripsi     = trim($_POST['deskripsi']);
    $visi          = trim($_POST['visi']);
    $misi          = trim($_POST['misi']);
    $prestasi      = trim($_POST['prestasi']);

    // Handle Upload Foto
    $foto = '';
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath   = $_FILES['foto']['tmp_name'];
        $fileName      = $_FILES['foto']['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
        if (in_array($fileExtension, $allowedExtensions)) {
            $newFileName = 'kontestan_' . time() . '_' . mt_rand(1000, 9999) . '.' . $fileExtension;
            $uploadFileDir = '../uploads/';
            
            if (!is_dir($uploadFileDir)) {
                mkdir($uploadFileDir, 0755, true);
            }
            
            $dest_path = $uploadFileDir . $newFileName;
            if(move_uploaded_file($fileTmpPath, $dest_path)) {
                $foto = $newFileName;
            } else {
                $error = "Gagal memindahkan file foto yang di-upload.";
            }
        } else {
            $error = "Format file foto tidak diizinkan. Gunakan JPG, JPEG, PNG, atau WEBP.";
        }
    } else {
        $error = "Foto kontestan wajib di-upload!";
    }

    if (empty($error)) {
        $stmt = mysqli_prepare($conn, "INSERT INTO kontestan (nomor_peserta, nama, kategori, asal_instansi, ttl, deskripsi, visi, misi, prestasi, foto) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "ssssssssss", $nomor_peserta, $nama, $kategori, $asal_instansi, $ttl, $deskripsi, $visi, $misi, $prestasi, $foto);
        
        if (mysqli_stmt_execute($stmt)) {
            header("Location: kontestan.php?status=sukses_tambah");
            exit;
        } else {
            $error = "Gagal menyimpan ke database: " . mysqli_error($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tambah Kontestan - Admin Panel</title>
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
        .btn-gold { background: var(--gold-gradient); color: #07090e; font-weight: 700; border: none; border-radius: 12px; transition: all 0.3s ease; }
        .btn-gold:hover { opacity: 0.9; box-shadow: 0 6px 20px rgba(245, 158, 11, 0.4); }
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
                <a class="nav-link nav-link-admin active" href="kontestan.php">Data Kontestan</a>
                <a class="nav-link nav-link-admin" href="pemilih.php">Data Pemilih</a>
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
            <h2 class="fw-extrabold text-white mb-1"><i class="fa-solid fa-user-plus text-warning me-2"></i> Tambah Kontestan Baru</h2>
            <p class="text-secondary small mb-0">Isi formulir lengkap untuk mendaftarkan finalis baru Kakang Senduk</p>
        </div>
        <a href="kontestan.php" class="btn btn-outline-secondary btn-sm px-4 rounded-pill text-light border-secondary">← Kembali</a>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger bg-danger bg-opacity-25 border-0 text-danger rounded-3 mb-4"><i class="fa-solid fa-triangle-exclamation me-2"></i> <?= e($error) ?></div>
    <?php endif; ?>

    <div class="card card-admin p-4 p-lg-5">
        <form method="POST" enctype="multipart/form-data">
            <div class="row g-4 mb-4">
                <div class="col-md-4">
                    <label class="form-label small text-secondary fw-bold">Nomor Peserta</label>
                    <input type="text" name="nomor_peserta" class="form-control" placeholder="Contoh: K-01 atau S-01" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label small text-secondary fw-bold">Kategori</label>
                    <select name="kategori" class="form-select" required>
                        <option value="Kakang">Kakang (Putra)</option>
                        <option value="Senduk">Senduk (Putri)</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small text-secondary fw-bold">Foto Kontestan (Portrait)</label>
                    <input type="file" name="foto" class="form-control" accept=".jpg, .jpeg, .png, .webp" required>
                </div>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-md-6">
                    <label class="form-label small text-secondary fw-bold">Nama Lengkap</label>
                    <input type="text" name="nama" class="form-control" placeholder="Masukkan nama lengkap finalis" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label small text-secondary fw-bold">Asal Instansi / Sekolah / Umum</label>
                    <input type="text" name="asal_instansi" class="form-control" placeholder="Contoh: SMA Negeri 1 Ponorogo" required>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label small text-secondary fw-bold">Tempat, Tanggal Lahir (TTL)</label>
                <input type="text" name="ttl" class="form-control" placeholder="Contoh: Ponorogo, 17 Agustus 2008">
            </div>

            <div class="mb-4">
                <label class="form-label small text-secondary fw-bold">Deskripsi / Biografi Singkat</label>
                <textarea name="deskripsi" class="form-control" rows="3" placeholder="Tuliskan biografi atau profil singkat kontestan..."></textarea>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-md-6">
                    <label class="form-label small text-secondary fw-bold">Visi</label>
                    <textarea name="visi" class="form-control" rows="3" placeholder="Visi kontestan..."></textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label small text-secondary fw-bold">Misi</label>
                    <textarea name="misi" class="form-control" rows="3" placeholder="Misi kontestan..."></textarea>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label small text-secondary fw-bold">Prestasi & Pengalaman (Opsional)</label>
                <textarea name="prestasi" class="form-control" rows="3" placeholder="Tuliskan daftar prestasi atau pengalaman organisasi..."></textarea>
            </div>

            <div class="d-flex justify-content-end gap-3 pt-3 border-top border-secondary border-opacity-10">
                <a href="kontestan.php" class="btn btn-outline-secondary px-4 py-2 rounded-pill text-light border-secondary">Batal</a>
                <button type="submit" class="btn btn-gold px-5 py-2 fw-bold shadow-sm">Simpan Kontestan</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>