<?php
session_start();
if (!isset($_SESSION['id'])) { header('Location: login.php'); exit; }
require_once __DIR__ . '/koneksi.php';
require_once __DIR__ . '/helpers.php';

$aksi = $_GET['aksi'] ?? '';
$pesan = $ptype = '';

// HAPUS
if ($aksi === 'hapus' && isset($_GET['id'])) {
  $st = mysqli_prepare($koneksi, "DELETE FROM buku WHERE id_buku=?");
  mysqli_stmt_bind_param($st, 's', $_GET['id']);
  if (mysqli_stmt_execute($st)) { $pesan = 'Buku berhasil dihapus.'; $ptype = 'success'; }
  else { $pesan = 'Gagal menghapus: ' . mysqli_error($koneksi); $ptype = 'danger'; }
}

// SIMPAN
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $id       = trim($_POST['id_buku']);
  $judul    = trim($_POST['judul']);
  $pengarang= trim($_POST['pengarang']);
  $penerbit = trim($_POST['penerbit']);
  $tahun    = (int)$_POST['tahun'];
  $stok     = (int)$_POST['stok'];

  if ($_POST['mode'] === 'tambah') {
    $st = mysqli_prepare($koneksi, "INSERT INTO buku VALUES (?,?,?,?,?,?)");
    mysqli_stmt_bind_param($st, 'ssssis', $id, $judul, $pengarang, $penerbit, $tahun, $stok);
  } else {
    $st = mysqli_prepare($koneksi, "UPDATE buku SET judul=?,pengarang=?,penerbit=?,tahun=?,stok=? WHERE id_buku=?");
    mysqli_stmt_bind_param($st, 'sssiss', $judul, $pengarang, $penerbit, $tahun, $stok, $id);
  }
  if (mysqli_stmt_execute($st)) {
    $pesan = 'Data buku berhasil disimpan.'; $ptype = 'success';
    $aksi = '';
  } else {
    $pesan = 'Gagal: ' . mysqli_error($koneksi); $ptype = 'danger';
  }
}

$edit = null;
if ($aksi === 'edit' && isset($_GET['id'])) {
  $st = mysqli_prepare($koneksi, "SELECT * FROM buku WHERE id_buku=?");
  mysqli_stmt_bind_param($st, 's', $_GET['id']);
  mysqli_stmt_execute($st);
  $edit = mysqli_fetch_assoc(mysqli_stmt_get_result($st));
  if (!$edit) { $aksi = ''; }
}

$showForm = ($aksi === 'tambah' || $edit);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Data Buku — Perpustakaan Sekolah</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="layout">
  <?php sidebar('Buku'); ?>
  <div class="main">
    <?php topbar('Data Buku'); ?>
    <div class="content">

      <?php if ($pesan): ?>
        <div class="alert alert-<?= $ptype ?>">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
          <?= h($pesan) ?>
        </div>
      <?php endif; ?>

      <?php if ($showForm): ?>
      <!-- FORM TAMBAH / EDIT -->
      <div class="card" style="margin-bottom:24px">
        <div class="card-head">
          <h2>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
            <?= $edit ? 'Edit Buku' : 'Tambah Buku Baru' ?>
          </h2>
        </div>
        <div class="card-body">
          <form method="post" autocomplete="off">
            <input type="hidden" name="mode" value="<?= $edit ? 'edit' : 'tambah' ?>">
            <div class="form-row form-row-2">
              <div class="form-group">
                <label class="form-label" for="id_buku">Kode Buku</label>
                <input id="id_buku" name="id_buku" class="form-control" required
                  value="<?= h($edit['id_buku'] ?? '') ?>" <?= $edit ? 'readonly' : '' ?>
                  placeholder="cth. BK004">
              </div>
              <div class="form-group">
                <label class="form-label" for="stok">Stok</label>
                <input id="stok" name="stok" type="number" min="0" class="form-control" required
                  value="<?= h($edit['stok'] ?? '0') ?>">
              </div>
              <div class="form-group" style="grid-column:1/-1">
                <label class="form-label" for="judul">Judul Buku</label>
                <input id="judul" name="judul" class="form-control" required
                  value="<?= h($edit['judul'] ?? '') ?>" placeholder="Judul lengkap buku">
              </div>
              <div class="form-group">
                <label class="form-label" for="pengarang">Pengarang</label>
                <input id="pengarang" name="pengarang" class="form-control" required
                  value="<?= h($edit['pengarang'] ?? '') ?>" placeholder="Nama pengarang">
              </div>
              <div class="form-group">
                <label class="form-label" for="penerbit">Penerbit</label>
                <input id="penerbit" name="penerbit" class="form-control" required
                  value="<?= h($edit['penerbit'] ?? '') ?>" placeholder="Nama penerbit">
              </div>
              <div class="form-group">
                <label class="form-label" for="tahun">Tahun Terbit</label>
                <input id="tahun" name="tahun" type="number" min="1900" max="<?= date('Y') ?>" class="form-control" required
                  value="<?= h($edit['tahun'] ?? date('Y')) ?>">
              </div>
            </div>
            <div class="form-actions" style="margin-top:16px">
              <button type="submit" class="btn btn-primary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                Simpan
              </button>
              <a href="data_buku.php" class="btn btn-secondary">Batal</a>
            </div>
          </form>
        </div>
      </div>
      <?php endif; ?>

      <!-- TABLE -->
      <div class="card">
        <div class="card-head">
          <h2>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
            Daftar Buku
          </h2>
          <?php if (!$showForm): ?>
          <a href="?aksi=tambah" class="btn btn-primary btn-sm">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Tambah Buku
          </a>
          <?php endif; ?>
        </div>
        <div class="tbl-wrap">
          <table>
            <thead>
              <tr>
                <th>No</th>
                <th>Kode</th>
                <th>Judul</th>
                <th>Pengarang</th>
                <th>Penerbit</th>
                <th>Tahun</th>
                <th>Stok</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
            <?php
            $no = 1;
            $q = mysqli_query($koneksi, "SELECT * FROM buku ORDER BY judul");
            while ($b = mysqli_fetch_assoc($q)):
            ?>
              <tr>
                <td><?= $no++ ?></td>
                <td><code style="font-size:.78rem;background:var(--cream);padding:2px 6px;border-radius:4px"><?= h($b['id_buku']) ?></code></td>
                <td style="font-weight:600"><?= h($b['judul']) ?></td>
                <td><?= h($b['pengarang']) ?></td>
                <td><?= h($b['penerbit']) ?></td>
                <td><?= h($b['tahun']) ?></td>
                <td>
                  <span style="font-weight:700;color:<?= $b['stok']>0?'var(--success)':'var(--danger)' ?>">
                    <?= $b['stok'] ?>
                  </span>
                </td>
                <td>
                  <div style="display:flex;gap:6px">
                    <a href="?aksi=edit&id=<?= urlencode($b['id_buku']) ?>" class="btn btn-warn btn-sm">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                      Edit
                    </a>
                    <a href="?aksi=hapus&id=<?= urlencode($b['id_buku']) ?>"
                       onclick="return confirm('Hapus buku ini?')"
                       class="btn btn-danger btn-sm">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                      Hapus
                    </a>
                  </div>
                </td>
              </tr>
            <?php endwhile; ?>
            <?php if ($no === 1): ?>
              <tr><td colspan="8" style="text-align:center;color:var(--muted);padding:24px">Belum ada data buku.</td></tr>
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
