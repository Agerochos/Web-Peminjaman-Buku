<?php
session_start();
if (!isset($_SESSION['id'])) { header('Location: login.php'); exit; }
require_once __DIR__ . '/koneksi.php';
require_once __DIR__ . '/helpers.php';

$aksi = $_GET['aksi'] ?? '';
$pesan = $ptype = '';

// HAPUS
if ($aksi === 'hapus' && isset($_GET['id'])) {
  $st = mysqli_prepare($koneksi, "DELETE FROM anggota WHERE id_anggota=?");
  mysqli_stmt_bind_param($st, 's', $_GET['id']);
  if (mysqli_stmt_execute($st)) { $pesan = 'Anggota berhasil dihapus.'; $ptype = 'success'; }
  else { $pesan = 'Gagal: ' . mysqli_error($koneksi); $ptype = 'danger'; }
}

// SIMPAN
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $id   = trim($_POST['id_anggota']);
  $nama = trim($_POST['nama_anggota']);
  $user = trim($_POST['username']);
  $kelas= trim($_POST['kelas']);
  $pass = $_POST['password'];

  if ($_POST['mode'] === 'tambah') {
    $hash = password_hash($pass, PASSWORD_DEFAULT);
    $st = mysqli_prepare($koneksi, "INSERT INTO anggota VALUES (?,?,?,?,?)");
    mysqli_stmt_bind_param($st, 'sssss', $id, $nama, $user, $hash, $kelas);
  } elseif ($pass !== '') {
    $hash = password_hash($pass, PASSWORD_DEFAULT);
    $st = mysqli_prepare($koneksi, "UPDATE anggota SET nama_anggota=?,username=?,kelas=?,password=? WHERE id_anggota=?");
    mysqli_stmt_bind_param($st, 'sssss', $nama, $user, $kelas, $hash, $id);
  } else {
    $st = mysqli_prepare($koneksi, "UPDATE anggota SET nama_anggota=?,username=?,kelas=? WHERE id_anggota=?");
    mysqli_stmt_bind_param($st, 'ssss', $nama, $user, $kelas, $id);
  }
  if (mysqli_stmt_execute($st)) {
    $pesan = 'Data anggota berhasil disimpan.'; $ptype = 'success'; $aksi = '';
  } else {
    $pesan = 'Gagal: ' . mysqli_error($koneksi); $ptype = 'danger';
  }
}

$edit = null;
if ($aksi === 'edit' && isset($_GET['id'])) {
  $st = mysqli_prepare($koneksi, "SELECT * FROM anggota WHERE id_anggota=?");
  mysqli_stmt_bind_param($st, 's', $_GET['id']);
  mysqli_stmt_execute($st);
  $edit = mysqli_fetch_assoc(mysqli_stmt_get_result($st));
  if (!$edit) $aksi = '';
}

$showForm = ($aksi === 'tambah' || $edit);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Data Anggota — Perpustakaan Sekolah</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="layout">
  <?php sidebar('Anggota'); ?>
  <div class="main">
    <?php topbar('Data Anggota'); ?>
    <div class="content">

      <?php if ($pesan): ?>
        <div class="alert alert-<?= $ptype ?>">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
          <?= h($pesan) ?>
        </div>
      <?php endif; ?>

      <?php if ($showForm): ?>
      <div class="card" style="margin-bottom:24px">
        <div class="card-head">
          <h2>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
            <?= $edit ? 'Edit Anggota' : 'Tambah Anggota Baru' ?>
          </h2>
        </div>
        <div class="card-body">
          <form method="post" autocomplete="off">
            <input type="hidden" name="mode" value="<?= $edit ? 'edit' : 'tambah' ?>">
            <div class="form-row form-row-2">
              <div class="form-group">
                <label class="form-label" for="id_anggota">NIS</label>
                <input id="id_anggota" name="id_anggota" class="form-control" required
                  value="<?= h($edit['id_anggota'] ?? '') ?>" <?= $edit ? 'readonly' : '' ?>
                  placeholder="Nomor Induk Siswa">
              </div>
              <div class="form-group">
                <label class="form-label" for="kelas">Kelas</label>
                <input id="kelas" name="kelas" class="form-control" required
                  value="<?= h($edit['kelas'] ?? '') ?>" placeholder="cth. XI RPL 1">
              </div>
              <div class="form-group" style="grid-column:1/-1">
                <label class="form-label" for="nama_anggota">Nama Lengkap</label>
                <input id="nama_anggota" name="nama_anggota" class="form-control" required
                  value="<?= h($edit['nama_anggota'] ?? '') ?>" placeholder="Nama lengkap siswa">
              </div>
              <div class="form-group">
                <label class="form-label" for="username">Username</label>
                <input id="username" name="username" class="form-control" required
                  value="<?= h($edit['username'] ?? '') ?>" placeholder="Username login">
              </div>
              <div class="form-group">
                <label class="form-label" for="password">
                  Password <?= $edit ? '<span style="font-weight:400;color:var(--muted)">(kosong = tidak diubah)</span>' : '' ?>
                </label>
                <input id="password" name="password" type="password" class="form-control"
                  <?= $edit ? '' : 'required' ?> placeholder="<?= $edit ? 'Isi untuk ubah password' : 'Buat password' ?>">
              </div>
            </div>
            <div class="form-actions" style="margin-top:16px">
              <button type="submit" class="btn btn-primary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                Simpan
              </button>
              <a href="data_anggota.php" class="btn btn-secondary">Batal</a>
            </div>
          </form>
        </div>
      </div>
      <?php endif; ?>

      <!-- TABLE -->
      <div class="card">
        <div class="card-head">
          <h2>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            Daftar Anggota
          </h2>
          <?php if (!$showForm): ?>
          <a href="?aksi=tambah" class="btn btn-primary btn-sm">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Tambah Anggota
          </a>
          <?php endif; ?>
        </div>
        <div class="tbl-wrap">
          <table>
            <thead>
              <tr>
                <th>No</th>
                <th>NIS</th>
                <th>Nama Anggota</th>
                <th>Username</th>
                <th>Kelas</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
            <?php
            $no = 1;
            $q = mysqli_query($koneksi, "SELECT * FROM anggota ORDER BY nama_anggota");
            while ($a = mysqli_fetch_assoc($q)):
            ?>
              <tr>
                <td><?= $no++ ?></td>
                <td><code style="font-size:.78rem;background:var(--cream);padding:2px 6px;border-radius:4px"><?= h($a['id_anggota']) ?></code></td>
                <td style="font-weight:600"><?= h($a['nama_anggota']) ?></td>
                <td><?= h($a['username']) ?></td>
                <td><?= h($a['kelas']) ?></td>
                <td>
                  <div style="display:flex;gap:6px">
                    <a href="?aksi=edit&id=<?= urlencode($a['id_anggota']) ?>" class="btn btn-warn btn-sm">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                      Edit
                    </a>
                    <a href="?aksi=hapus&id=<?= urlencode($a['id_anggota']) ?>"
                       onclick="return confirm('Hapus anggota ini?')"
                       class="btn btn-danger btn-sm">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                      Hapus
                    </a>
                  </div>
                </td>
              </tr>
            <?php endwhile; ?>
            <?php if ($no === 1): ?>
              <tr><td colspan="6" style="text-align:center;color:var(--muted);padding:24px">Belum ada data anggota.</td></tr>
            <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

    </div>
  </div>
</div>
</body>
</html>
