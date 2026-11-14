<?php
session_start();
if (!isset($_SESSION['id'])) { header('Location: login.php'); exit; }
require_once __DIR__ . '/koneksi.php';
require_once __DIR__ . '/helpers.php';

$total_buku    = mysqli_fetch_row(mysqli_query($koneksi, "SELECT COUNT(*) FROM buku"))[0] ?? 0;
$total_anggota = mysqli_fetch_row(mysqli_query($koneksi, "SELECT COUNT(*) FROM anggota"))[0] ?? 0;
$total_pinjam  = mysqli_fetch_row(mysqli_query($koneksi, "SELECT COUNT(*) FROM peminjaman WHERE status='dipinjam'"))[0] ?? 0;
$total_kembali = mysqli_fetch_row(mysqli_query($koneksi, "SELECT COUNT(*) FROM peminjaman WHERE status='dikembalikan'"))[0] ?? 0;

$recent = mysqli_query($koneksi,
  "SELECT p.id_pinjam, a.nama_anggota, b.judul, p.tgl_pinjam, p.tgl_kembali, p.status
   FROM peminjaman p
   JOIN anggota a ON p.id_anggota=a.id_anggota
   JOIN buku b ON p.id_buku=b.id_buku
   ORDER BY p.id_pinjam DESC LIMIT 6"
);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Dashboard — Perpustakaan Sekolah</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="layout">
  <?php sidebar('Dashboard'); ?>
  <div class="main">
    <?php topbar('Dashboard'); ?>
    <div class="content">

      <!-- Stat Cards -->
      <div class="stat-grid">
        <div class="stat-card">
          <div class="stat-icon brown">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
          </div>
          <div>
            <div class="stat-label">Total Buku</div>
            <div class="stat-value"><?= $total_buku ?></div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon warm">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
          </div>
          <div>
            <div class="stat-label">Total Anggota</div>
            <div class="stat-value"><?= $total_anggota ?></div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon cream">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
          </div>
          <div>
            <div class="stat-label">Sedang Dipinjam</div>
            <div class="stat-value"><?= $total_pinjam ?></div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon brown">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
          </div>
          <div>
            <div class="stat-label">Sudah Dikembalikan</div>
            <div class="stat-value"><?= $total_kembali ?></div>
          </div>
        </div>
      </div>

      <!-- Recent Peminjaman -->
      <div class="card">
        <div class="card-head">
          <h2>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
            Peminjaman Terkini
          </h2>
          <a href="peminjaman.php" class="btn btn-secondary btn-sm">Lihat Semua</a>
        </div>
        <div class="tbl-wrap">
          <table>
            <thead>
              <tr>
                <th>#</th>
                <th>Anggota</th>
                <th>Judul Buku</th>
                <th>Tgl Pinjam</th>
                <th>Tgl Kembali</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
            <?php $no=1; while($r = mysqli_fetch_assoc($recent)): ?>
              <tr>
                <td><?= $no++ ?></td>
                <td><?= h($r['nama_anggota']) ?></td>
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
              </tr>
            <?php endwhile; ?>
            <?php if ($no === 1): ?>
              <tr><td colspan="6" style="text-align:center;color:var(--muted);padding:24px">Belum ada data peminjaman.</td></tr>
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
