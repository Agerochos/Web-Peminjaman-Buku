<?php
// shared helper — include this at the top of every page (after session_start & auth check)

function h($s) { return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }

function nav_link($href, $label, $icon, $page) {
  $active = ($page === $label) ? ' active' : '';
  echo "<li><a href=\"$href\" class=\"$active\">$icon <span>$label</span></a></li>";
}

function sidebar($active = '') {
  $user = $_SESSION['nama'] ?? 'Admin';
  $initial = strtoupper(mb_substr($user, 0, 1));
?>
<aside class="sidebar">
  <div class="sidebar-brand">
    <div class="logo-mark">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/>
      </svg>
    </div>
    <h1>Perpustakaan<br>Sekolah</h1>
    <small>Sistem Manajemen</small>
  </div>

  <ul class="sidebar-nav">
    <li><a href="dashboard.php" class="<?= $active==='Dashboard'?'active':'' ?>">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
      <span>Dashboard</span>
    </a></li>
    <li><a href="data_buku.php" class="<?= $active==='Buku'?'active':'' ?>">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
      <span>Data Buku</span>
    </a></li>
    <li><a href="data_anggota.php" class="<?= $active==='Anggota'?'active':'' ?>">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
      <span>Data Anggota</span>
    </a></li>
    <li><a href="peminjaman.php" class="<?= $active==='Peminjaman'?'active':'' ?>">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
      <span>Peminjaman</span>
    </a></li>
  </ul>

  <div class="sidebar-footer">
    <a href="logout.php">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
      Logout
    </a>
  </div>
</aside>
<?php
}

function topbar($title, $user = '') {
  if (!$user) $user = $_SESSION['nama'] ?? 'Admin';
  $initial = strtoupper(mb_substr($user, 0, 1));
?>
<header class="topbar">
  <span class="topbar-title"><?= h($title) ?></span>
  <div class="topbar-user">
    <div class="avatar"><?= $initial ?></div>
    <span><?= h($user) ?></span>
  </div>
</header>
<?php
}
?>
