<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

date_default_timezone_set('Asia/Jakarta');

$host = "localhost";
$user = "root";
$pass = "";
$db   = "db_voting_kakang_senduk";

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}
mysqli_set_charset($conn, "utf8mb4");

// Helper fungsi keamanan XSS
function e($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

// Helper cek periode voting aktif
function getPeriodeAktif($conn) {
    $now = date('Y-m-d H:i:s');
    $sql = "SELECT * FROM periode_voting 
            WHERE status = 'Aktif' 
            AND tanggal_mulai <= '$now' 
            AND tanggal_selesai >= '$now' 
            ORDER BY id DESC LIMIT 1";
    $res = mysqli_query($conn, $sql);
    return mysqli_fetch_assoc($res);
}
?>