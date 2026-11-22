<?php
// Variabel: $judul, $aktif
$u = $_SESSION['user'];
$nav = [
  'index'     => ['Beranda', 'index.php', false],
  'transaksi' => ['Kasir', 'transaksi.php', false],
  'riwayat'   => ['Riwayat', 'riwayat.php', false],
  'menu'      => ['Menu', 'menu.php', false],
  'bahan'     => ['Stok bahan', 'bahan.php', false],
  'laporan'   => ['Laporan', 'laporan.php', true],
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($judul) ?> · Motekar</title>
<link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,500;12..96,700&family=Figtree:wght@400;500;600&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="assets/style.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg topbar">
  <div class="container-xl">
    <a class="navbar-brand brand" href="index.php">Motekar<span>.</span></a>
    <button class="navbar-toggler border-0" data-bs-toggle="collapse" data-bs-target="#nav"><span class="navbar-toggler-icon"></span></button>
    <div class="collapse navbar-collapse" id="nav">
      <ul class="navbar-nav me-auto">
        <?php foreach ($nav as $k => [$label, $href, $adminOnly]):
          if ($adminOnly && $u['role'] !== 'admin') continue; ?>
          <li class="nav-item"><a class="nav-link <?= $aktif === $k ? 'active' : '' ?>" href="<?= $href ?>"><?= $label ?></a></li>
        <?php endforeach; ?>
      </ul>
      <div class="d-flex align-items-center gap-3 small text-white-50">
        <span><?= e($u['nama']) ?> (<?= e($u['role']) ?>)</span>
        <a href="logout.php" class="btn btn-sm btn-outline-light">Keluar</a>
      </div>
    </div>
  </div>
</nav>
<main class="container-xl py-4">
