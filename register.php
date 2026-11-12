<?php
require_once '../config/koneksi.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama     = trim($_POST['nama']);
    $email    = trim($_POST['email']);
    $no_hp    = trim($_POST['no_hp']);
    $password = $_POST['password'];

    // Cek email unik
    $stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ?");
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);

    if (mysqli_stmt_num_rows($stmt) > 0) {
        $error = "Email sudah terdaftar! Silakan gunakan email lain.";
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $role = 'pemilih';
        $ins = mysqli_prepare($conn, "INSERT INTO users (nama, email, no_hp, password, role) VALUES (?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($ins, "sssss", $nama, $email, $no_hp, $hash, $role);
        if (mysqli_stmt_execute($ins)) {
            header("Location: login.php?registered=1");
            exit;
        } else {
            $error = "Registrasi gagal: " . mysqli_error($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Registrasi Pemilih - Kakang Senduk Ponorogo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-dark text-light d-flex align-items-center min-vh-100">
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card bg-secondary bg-opacity-25 border-warning text-light shadow">
                <div class="card-body p-4">
                    <h4 class="text-warning fw-bold mb-3">Registrasi Akun Pemilih</h4>
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?= e($error) ?></div>
                    <?php endif; ?>
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Nama Lengkap</label>
                            <input type="text" name="nama" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nomor HP / WhatsApp</label>
                            <input type="text" name="no_hp" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" minlength="6" required>
                        </div>
                        <button type="submit" class="btn btn-warning w-100 fw-bold">Daftar Sekarang</button>
                    </form>
                    <p class="mt-3 text-center small">Sudah punya akun? <a href="login.php" class="text-warning">Login di sini</a></p>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>