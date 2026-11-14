<?php
session_start();
if (!isset($_SESSION['id'])) { header('Location: login.php'); exit; }
require_once __DIR__ . '/koneksi.php';
require_once __DIR__ . '/helpers.php';

$aksi = $_GET['aksi'] ?? '';
$pesan = $ptype = '';

// KEMBALIKAN
if ($aksi === 'kembali' && isset($_GET['id'])) {
  $tgl = date('Y-m-d');
  $st = mysqli_prepare($koneksi,
    "UPDATE peminjaman SET status='dikembalikan', tgl_kembali=? WHERE id_pinjam=? AND status='dipinjam'"
  );
  mysqli_stmt_bind_param($st, 'si', $tgl, $_GET['id']);
  if (mysqli_stmt_execute($st)) {
    // tambah stok kembali
    mysqli_query($koneksi,
      "UPDATE buku b JOIN peminjaman p ON b.id_buku=p.id_buku
       SET b.stok=b.stok+1
       WHERE p.id_pinjam=" . (int)$_GET['id']
    );
    $pesan = 'Buku berhasil dikembalikan.'; $ptype = 'success';
  } else {
    $pesan = 'Gagal: ' . mysqli_error($koneksi); $ptype = 'danger';
  }
}

// HAPUS
if ($aksi === 'hapus' && isset($_GET['id'])) {
  $st = mysqli_prepare($koneksi, "DELETE FROM peminjaman WHERE id_pinjam=?");
  mysqli_stmt_bind_param($st, 'i', $_GET['id']);
  if (mysqli_stmt_execute($st)) { $pesan = 'Data peminjaman dihapus.'; $ptype = 'success'; }
  else { $pesan = 'Gagal: ' . mysqli_error($koneksi); $ptype = 'danger'; }
}

// SIMPAN PEMINJAMAN BARU
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_anggota'])) {
  $id_anggota = $_POST['id_anggota'];
  $id_buku    = $_POST['id_buku'];
  $tgl_pinjam = $_POST['tgl_pinjam'];

  // cek stok
  $stok_row = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT stok FROM buku WHERE id_buku='".mysqli_real_escape_string($koneksi,$id_buku)."'"));
  if (!$stok_row || $stok_row['stok'] < 1) {
    $pesan = 'Stok buku habis atau tidak ditemukan.'; $ptype = 'danger';
  } else {
    $st = mysqli_prepare($koneksi, "INSERT INTO peminjaman (id_anggota,id_buku,tgl_pinjam,status) VALUES (?,?,?,'dipinjam')");
    mysqli_stmt_bind_param($st, 'sss', $id_anggota, $id_buku, $tgl_pinjam);
    if (mysqli_stmt_execute($st)) {
      mysqli_query($koneksi, "UPDATE buku SET stok=stok-1 WHERE id_buku='".mysqli_real_escape_string($koneksi,$id_buku)."'");
      $pesan = 'Peminjaman berhasil dicatat.'; $ptype = 'success'; $aksi = '';
    } else {
      $pesan = 'Gagal: ' . mysqli_error($koneksi); $ptype = 'danger';
    }
  }
}

$showForm = ($aksi === 'tambah');

// Ambil data untuk dropdown
$anggota_list = mysqli_query($koneksi, "SELECT id_anggota, nama_anggota FROM anggota ORDER BY nama_anggota");
$buku_list    = mysqli_query($koneksi, "SELECT id_buku, judul, stok FROM buku WHERE stok>0 ORDER BY judul");

// Filter
$filter = $_GET['filter'] ?? 'semua';
$where  = match($filter) {
  'dipinjam'      => "WHERE p.status='dipinjam'",
  'dikembalikan'  => "WHERE p.status='dikembalikan'",
  default         => ''
};
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Peminjaman — Perpustakaan Sekolah</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="layout">
  <?php sidebar('Peminjaman'); ?>
  <div class="main">
    <?php topbar('Peminjaman Buku'); ?>
    <div class="content">

      <?php if ($pesan): ?>
        <div class="alert alert-<?= $ptype ?>">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
          <?= h($pesan) ?>
        </div>
      <?php endif; ?>

      <?php if ($showForm): ?>
      <!-- FORM CATAT PEMINJAMAN -->
      <div class="card" style="margin-bottom:24px">
        <div class="card-head">
          <h2>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
            Catat Peminjaman Baru
          </h2>
        </div>
        <div class="card-body">
          <form method="post" autocomplete="off">
            <div class="form-row form-row-2">
              <div class="form-group">
                <label class="form-label" for="id_anggota">Anggota</label>
                <select id="id_anggota" name="id_anggota" class="form-control" required>
                  <option value="">— Pilih Anggota —</option>
                  <?php while ($a = mysqli_fetch_assoc($anggota_list)): ?>
                    <option value="<?= h($a['id_anggota']) ?>"><?= h($a['nama_anggota']) ?></option>
                  <?php endwhile; ?>
                </select>
              </div>
              <div class="form-group">
                <label class="form-label" for="id_buku">Buku (stok tersedia)</label>
                <select id="id_buku" name="id_buku" class="form-control" required>
                  <option value="">— Pilih Buku —</option>
                  <?php while ($b = mysqli_fetch_assoc($buku_list)): ?>
                    <option value="<?= h($b['id_buku']) ?>"><?= h($b['judul']) ?> (stok: <?= $b['stok'] ?>)</option>
                  <?php endwhile; ?>
                </select>
              </div>
              <div class="form-group">
                <label class="form-label" for="tgl_pinjam">Tanggal Pinjam</label>
                <input id="tgl_pinjam" name="tgl_pinjam" type="date" class="form-control" required value="<?= date('Y-m-d') ?>">
              </div>
            </div>
            <div class="form-actions" style="margin-top:16px">
              <button type="submit" class="btn btn-primary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                Simpan Peminjaman
              </button>
              <a href="peminjaman.php" class="btn btn-secondary">Batal</a>
            </div>
          </form>
        </div>
      </div>
      <?php endif; ?>

      <!-- TABLE -->
      <div class="card">
        <div class="card-head">
          <h2>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
            Riwayat Peminjaman
          </h2>
          <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
            <!-- Filter pills -->
            <div style="display:flex;gap:4px;background:var(--cream);padding:3px;border-radius:8px">
              <?php foreach(['semua'=>'Semua','dipinjam'=>'Dipinjam','dikembalikan'=>'Dikembalikan'] as $k=>$v): ?>
              <a href="?filter=<?= $k ?>" style="padding:4px 12px;border-radius:6px;font-size:.78rem;font-weight:600;text-decoration:none;
                <?= $filter===$k ? 'background:var(--brown);color:var(--cream)' : 'color:var(--muted)' ?>">
                <?= $v ?>
              </a>
              <?php endforeach; ?>
            </div>
            <?php if (!$showForm): ?>
            <a href="?aksi=tambah&filter=<?= h($filter) ?>" class="btn btn-primary btn-sm">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
              Catat Peminjaman
            </a>
            <?php endif; ?>
          </div>
        </div>
        <div class="tbl-wrap">
          <table>
            <thead>
              <tr>
                <th>No</th>
                <th>Anggota</th>
                <th>Judul Buku</th>
                <th>Tgl Pinjam</th>
                <th>Tgl Kembali</th>
                <th>Status</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
            <?php
            $no = 1;
            $q  = mysqli_query($koneksi,
              "SELECT p.id_pinjam, a.nama_anggota, b.judul, p.tgl_pinjam, p.tgl_kembali, p.status
               FROM peminjaman p
               JOIN anggota a ON p.id_anggota=a.id_anggota
               JOIN buku b ON p.id_buku=b.id_buku
               $where
               ORDER BY p.id_pinjam DESC"
            );
            while ($r = mysqli_fetch_assoc($q)):
            ?>
              <tr>
                <td><?= $no++ ?></td>
                <td style="font-weight:600"><?= h($r['nama_anggota']) ?></td>
                <td><?= h($r['judul']) ?></td>
                <td><?= h($r['tgl_pinjam']) ?></td>
                <td><?= $r['tgl_kembali'] ? h($r['tgl_kembali']) : '<span style="color:var(--muted)">—</span>' ?></td>
                <td>
                  <?php if ($r['status'] === 'dipinjam'): ?>
                    <span class="badge badge-dipinjam">Dipinjam</span>
                  <?php else: ?>
                    <span class="badge badge-kembali">Dikembalikan</span>
                  <?php endif; ?>
                </td>
                <td>
                  <div style="display:flex;gap:6px;flex-wrap:wrap">
                    <?php if ($r['status'] === 'dipinjam'): ?>
                    <a href="?aksi=kembali&id=<?= $r['id_pinjam'] ?>&filter=<?= h($filter) ?>"
                       onclick="return confirm('Tandai buku ini sudah dikembalikan?')"
                       class="btn btn-success btn-sm">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                      Kembalikan
                    </a>
                    <?php endif; ?>
                    <a href="?aksi=hapus&id=<?= $r['id_pinjam'] ?>&filter=<?= h($filter) ?>"
                       onclick="return confirm('Hapus data ini?')"
                       class="btn btn-danger btn-sm">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                      Hapus
                    </a>
                  </div>
                </td>
              </tr>
            <?php endwhile; ?>
            <?php if ($no === 1): ?>
              <tr><td colspan="7" style="text-align:center;color:var(--muted);padding:24px">Belum ada data peminjaman.</td></tr>
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
