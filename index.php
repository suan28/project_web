<?php
require_once 'includes/auth.php';
wajib_login();
$judul = 'Beranda'; $aktif = 'index';

$hari = date('Y-m-d');
$r = $conn->query("SELECT COUNT(*) n, COALESCE(SUM(total),0) omzet FROM transaksi WHERE DATE(tanggal) = '$hari'")->fetch_assoc();
$terlaris = $conn->query("SELECT d.nama_menu, SUM(d.jumlah) qty FROM detail_transaksi d
    JOIN transaksi t ON t.id = d.id_transaksi WHERE DATE(t.tanggal) = '$hari'
    GROUP BY d.nama_menu ORDER BY qty DESC LIMIT 5");
$menipis = $conn->query("SELECT nama_bahan, stok, satuan FROM bahan WHERE stok <= stok_minimum ORDER BY stok ASC");
$terakhir = $conn->query("SELECT kode_transaksi, tanggal, total, id FROM transaksi ORDER BY id DESC LIMIT 5");
include 'includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-4">
  <h1 class="h3 m-0">Beranda</h1>
  <a href="transaksi.php" class="btn btn-signage">Buka kasir</a>
</div>

<div class="row g-3 mb-3">
  <div class="col-md-6">
    <div class="panel">
      <div class="stat"><?= rupiah($r['omzet']) ?></div>
      <div class="stat-label">Pendapatan hari ini</div>
    </div>
  </div>
  <div class="col-md-6">
    <div class="panel">
      <div class="stat"><?= (int)$r['n'] ?></div>
      <div class="stat-label">Transaksi hari ini</div>
    </div>
  </div>
</div>

<div class="row g-3">
  <div class="col-lg-4">
    <div class="panel h-100">
      <h2>Menu terlaris hari ini</h2>
      <?php if ($terlaris->num_rows === 0): ?>
        <p class="text-secondary mb-0">Belum ada penjualan hari ini.</p>
      <?php endif; ?>
      <?php while ($m = $terlaris->fetch_assoc()): ?>
        <div class="d-flex justify-content-between py-1">
          <span><?= e($m['nama_menu']) ?></span>
          <strong><?= (int)$m['qty'] ?></strong>
        </div>
      <?php endwhile; ?>
    </div>
  </div>
  <div class="col-lg-4">
    <div class="panel h-100">
      <h2>Stok hampir habis</h2>
      <?php if ($menipis->num_rows === 0): ?>
        <p class="text-secondary mb-0">Semua stok aman.</p>
      <?php endif; ?>
      <?php while ($b = $menipis->fetch_assoc()): ?>
        <div class="d-flex justify-content-between py-1">
          <span><?= e($b['nama_bahan']) ?></span>
          <strong class="text-danger"><?= (float)$b['stok'] ?> <?= e($b['satuan']) ?></strong>
        </div>
      <?php endwhile; ?>
    </div>
  </div>
  <div class="col-lg-4">
    <div class="panel h-100">
      <h2>Transaksi terakhir</h2>
      <?php if ($terakhir->num_rows === 0): ?>
        <p class="text-secondary mb-0">Belum ada transaksi.</p>
      <?php endif; ?>
      <?php while ($t = $terakhir->fetch_assoc()): ?>
        <div class="d-flex justify-content-between py-1">
          <a href="struk.php?id=<?= $t['id'] ?>"><?= e($t['kode_transaksi']) ?></a>
          <strong><?= rupiah($t['total']) ?></strong>
        </div>
      <?php endwhile; ?>
    </div>
  </div>
</div>
<?php include 'includes/footer.php'; ?>