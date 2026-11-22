<?php
require_once 'includes/auth.php';
wajib_admin();
$judul = 'Laporan penjualan'; $aktif = 'laporan';

$dari = $_GET['dari'] ?? date('Y-m-01');
$sampai = $_GET['sampai'] ?? date('Y-m-d');

$st = $conn->prepare('SELECT COUNT(*) n, COALESCE(SUM(total),0) omzet FROM transaksi WHERE DATE(tanggal) BETWEEN ? AND ?');
$st->bind_param('ss', $dari, $sampai); $st->execute(); $ringkas = $st->get_result()->fetch_assoc();

$st = $conn->prepare('SELECT DATE(tanggal) hari, COUNT(*) n, SUM(total) omzet FROM transaksi WHERE DATE(tanggal) BETWEEN ? AND ? GROUP BY DATE(tanggal) ORDER BY hari DESC');
$st->bind_param('ss', $dari, $sampai); $st->execute(); $harian = $st->get_result()->fetch_all(MYSQLI_ASSOC);

$st = $conn->prepare('SELECT d.nama_menu, SUM(d.jumlah) qty, SUM(d.subtotal) omzet FROM detail_transaksi d JOIN transaksi t ON t.id = d.id_transaksi
    WHERE DATE(t.tanggal) BETWEEN ? AND ? GROUP BY d.nama_menu ORDER BY qty DESC LIMIT 10');
$st->bind_param('ss', $dari, $sampai); $st->execute(); $terlaris = $st->get_result()->fetch_all(MYSQLI_ASSOC);
include 'includes/header.php';
?>
<h1 class="h3 mb-3">Laporan penjualan</h1>
<form class="row g-2 mb-3">
  <div class="col-auto">
    <label class="form-label small mb-0" for="dari">Dari</label>
    <input class="form-control" type="date" id="dari" name="dari" value="<?= e($dari) ?>">
  </div>
  <div class="col-auto">
    <label class="form-label small mb-0" for="sampai">Sampai</label>
    <input class="form-control" type="date" id="sampai" name="sampai" value="<?= e($sampai) ?>">
  </div>
  <div class="col-auto align-self-end">
    <button class="btn btn-signage">Tampilkan</button>
  </div>
</form>

<div class="row g-3 mb-3">
  <div class="col-md-4">
    <div class="panel">
      <div class="stat"><?= rupiah($ringkas['omzet']) ?></div>
      <div class="stat-label">Total pendapatan</div>
    </div>
  </div>
  <div class="col-md-4"><div class="panel">
    <div class="stat"><?= (int)$ringkas['n'] ?>
  </div>
  <div class="stat-label">Jumlah transaksi</div>
</div>
</div>
  <div class="col-md-4">
    <div class="panel">
      <div class="stat"><?= $ringkas['n'] ? rupiah($ringkas['omzet'] / $ringkas['n']) : 'Rp 0' ?></div>
      <div class="stat-label">Rata-rata per transaksi</div>
    </div>
  </div>
</div>

<div class="row g-3">
  <div class="col-lg-7"><div class="panel p-0 table-responsive">
    <table class="table mb-0">
      <thead>
        <tr>
          <th>Tanggal</th>
          <th class="text-end">Transaksi</th>
          <th class="text-end">Pendapatan</th>
        </tr>
      </thead>
      <tbody>
    <?php if (!$harian): ?><tr><td colspan="3" class="text-center text-secondary py-4">Belum ada penjualan pada rentang ini.</td></tr><?php endif; ?>
    <?php foreach ($harian as $h): ?>
      <tr>
      <td><?= date('d/m/Y', strtotime($h['hari'])) ?></td>
      <td class="text-end"><?= $h['n'] ?></td>
      <td class="text-end"><?= rupiah($h['omzet']) ?></td>
    </tr><?php endforeach; ?>
    </tbody>
  </table>
</div>
</div>
  <div class="col-lg-5"><div class="panel p-0 table-responsive">
    <table class="table mb-0">
      <thead>
        <tr>
          <th>Menu terlaris</th>
          <th class="text-end">Terjual</th>
          <th class="text-end">Pendapatan</th>
        </tr>
      </thead>
      <tbody>
    <?php if (!$terlaris): ?>
      <tr>
        <td colspan="3" class="text-center text-secondary py-4">Belum ada data.</td>
      </tr><?php endif; ?>
    <?php foreach ($terlaris as $m): ?>
      <tr>
        <td><?= e($m['nama_menu']) ?></td>
        <td class="text-end"><?= $m['qty'] ?></td>
        <td class="text-end"><?= rupiah($m['omzet']) ?></td>
      </tr><?php endforeach; ?>
    </tbody>
  </table>
</div>
</div>
</div>
<?php include 'includes/footer.php'; ?>
