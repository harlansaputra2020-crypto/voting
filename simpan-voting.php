<?php
require_once '../config/koneksi.php';

if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../auth/login.php");
    exit;
}

$user_id      = (int)$_SESSION['user_id'];
$kontestan_id = (int)($_POST['kontestan_id'] ?? 0);

// 1. Validasi Periode Voting Aktif
if (!getPeriodeAktif($conn)) {
    header("Location: ../pages/voting.php?error=" . urlencode("Periode voting sedang nonaktif!"));
    exit;
}

// 2. Mulai Transaksi Database untuk mencegah race condition / voting ganda
mysqli_begin_transaction($conn);

try {
    // Ambil kategori kontestan
    $stmtK = mysqli_prepare($conn, "SELECT kategori FROM kontestan WHERE id = ?");
    mysqli_stmt_bind_param($stmtK, "i", $kontestan_id);
    mysqli_stmt_execute($stmtK);
    $resK = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtK));

    if (!$resK) {
        throw new Exception("Kontestan tidak valid.");
    }
    $kategori = $resK['kategori'];

    // Cek apakah user sudah voting di kategori ini
    $stmtCek = mysqli_prepare($conn, "SELECT id FROM voting WHERE user_id = ? AND kategori = ? FOR UPDATE");
    mysqli_stmt_bind_param($stmtCek, "is", $user_id, $kategori);
    mysqli_stmt_execute($stmtCek);
    mysqli_stmt_store_result($stmtCek);

    if (mysqli_stmt_num_rows($stmtCek) > 0) {
        throw new Exception("Anda sudah menggunakan hak suara untuk kategori $kategori!");
    }

    // Simpan suara
    $stmtIns = mysqli_prepare($conn, "INSERT INTO voting (user_id, kontestan_id, kategori, created_at) VALUES (?, ?, ?, NOW())");
    mysqli_stmt_bind_param($stmtIns, "iis", $user_id, $kontestan_id, $kategori);
    mysqli_stmt_execute($stmtIns);

    mysqli_commit($conn);
    header("Location: ../pages/voting.php?status=sukses");
    exit;

} catch (Exception $e) {
    mysqli_rollback($conn);
    header("Location: ../pages/voting.php?error=" . urlencode($e->getMessage()));
    exit;
}