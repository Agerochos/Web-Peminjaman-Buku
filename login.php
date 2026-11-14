<?php
session_start();
require_once __DIR__ . '/koneksi.php';

$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $user = trim($_POST['username'] ?? '');
  $pass = $_POST['password'] ?? '';

  if ($user && $pass) {
    $st = mysqli_prepare($koneksi, "SELECT * FROM anggota WHERE username = ?");
    mysqli_stmt_bind_param($st, 's', $user);
    mysqli_stmt_execute($st);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($st));

    if ($row && password_verify($pass, $row['password'])) {
      $_SESSION['id']   = $row['id_anggota'];
      $_SESSION['nama'] = $row['nama_anggota'];
      $_SESSION['user'] = $row['username'];
      header('Location: dashboard.php'); exit;
    }
    $err = 'Username atau password salah.';
  } else {
    $err = 'Mohon isi semua kolom.';
  }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Login — Perpustakaan Sekolah</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="login-wrap">
  <div class="login-card">
    <div class="login-brand">
      <div class="logo-mark">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/>
          <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/>
        </svg>
      </div>
      <h1>Perpustakaan Sekolah</h1>
      <p>Masuk untuk mengelola perpustakaan</p>
    </div>

    <?php if ($err): ?>
    <div class="alert alert-danger">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
      <?= htmlspecialchars($err) ?>
    </div>
    <?php endif; ?>

    <form method="post" autocomplete="off">
      <div class="form-group">
        <label class="form-label" for="username">Username</label>
        <input id="username" name="username" class="form-control" type="text"
               placeholder="Masukkan username" required
               value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
      </div>
      <div class="form-group" style="margin-top:14px">
        <label class="form-label" for="password">Password</label>
        <input id="password" name="password" class="form-control" type="password"
               placeholder="Masukkan password" required>
      </div>
      <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;padding:11px;margin-top:20px;font-size:.9rem;">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
        Masuk
      </button>
    </form>
  </div>
</div>
</body>
</html>
