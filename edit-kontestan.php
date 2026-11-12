<?php
require_once '../config/koneksi.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') { header("Location: ../auth/login.php"); exit; }

$id = (int)($_GET['id'] ?? 0);
$k  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM kontestan WHERE id = $id"));
if (!$k) die("Data tidak ditemukan");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nomor    = trim($_POST['nomor_peserta']);
    $nama     = trim($_POST['nama']);
    $kategori = $_POST['kategori'];
    $ttl      = trim($_POST['ttl']);
    $instansi = trim($_POST['asal_instansi']);
    $visi     = trim($_POST['visi']);
    $misi     = trim($_POST['misi']);
    $prestasi = trim($_POST['prestasi']);
    $desk     = trim($_POST['deskripsi']);
    $fotoName = $k['foto'];

    if (!empty($_FILES['foto']['name'])) {
        $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
            $fotoName = time() . '_' . uniqid() . '.' . $ext;
            move_uploaded_file($_FILES['foto']['tmp_name'], '../uploads/' . $fotoName);
        }
    }

    $stmt = mysqli_prepare($conn, "UPDATE kontestan SET nomor_peserta=?, nama=?, kategori=?, foto=?, ttl=?, asal_instansi=?, visi=?, misi=?, prestasi=?, deskripsi=? WHERE id=?");
    mysqli_stmt_bind_param($stmt, "ssssssssssi", $nomor, $nama, $kategori, $fotoName, $ttl, $instansi, $visi, $misi, $prestasi, $desk, $id);
    mysqli_stmt_execute($stmt);
    header("Location: kontestan.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Kontestan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-dark text-light">
<div class="container py-4" style="max-width:720px;">
    <h3 class="text-warning mb-3">Edit Data Kontestan</h3>
    <form method="POST" enctype="multipart/form-data" class="card bg-secondary bg-opacity-25 border-warning p-4">
        <div class="row g-3">
            <div class="col-md-4"><label>Nomor Peserta</label><input type="text" name="nomor_peserta" value="<?= e($k['nomor_peserta']) ?>" class="form-control" required></div>
            <div class="col-md-4"><label>Kategori</label><select name="kategori" class="form-select"><option value="Kakang" <?= $k['kategori']=='Kakang'?'selected':'' ?>>Kakang</option><option value="Senduk" <?= $k['kategori']=='Senduk'?'selected':'' ?>>Senduk</option></select></div>
            <div class="col-md-4"><label>Ganti Foto (Opsional)</label><input type="file" name="foto" class="form-control" accept="image/*"></div>
            <div class="col-md-6"><label>Nama Lengkap</label><input type="text" name="nama" value="<?= e($k['nama']) ?>" class="form-control" required></div>
            <div class="col-md-6"><label>Tempat, Tanggal Lahir</label><input type="text" name="ttl" value="<?= e($k['ttl']) ?>" class="form-control"></div>
            <div class="col-12"><label>Asal Sekolah / Instansi</label><input type="text" name="asal_instansi" value="<?= e($k['asal_instansi']) ?>" class="form-control" required></div>
            <div class="col-12"><label>Deskripsi Profil</label><textarea name="deskripsi" class="form-control" rows="2" required><?= e($k['deskripsi']) ?></textarea></div>
            <div class="col-12"><label>Visi</label><textarea name="visi" class="form-control" rows="2" required><?= e($k['visi']) ?></textarea></div>
            <div class="col-12"><label>Misi</label><textarea name="misi" class="form-control" rows="3" required><?= e($k['misi']) ?></textarea></div>
            <div class="col-12"><label>Prestasi</label><textarea name="prestasi" class="form-control" rows="2"><?= e($k['prestasi']) ?></textarea></div>
        </div>
        <div class="mt-4 d-flex gap-2">
            <button type="submit" class="btn btn-warning fw-bold">Perbarui Data</button>
            <a href="kontestan.php" class="btn btn-outline-light">Batal</a>
        </div>
    </form>
</div>
</body>
</html>