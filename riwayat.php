<?php
require_once 'includes/auth.php';
wajib_login();
$judul = 'Riwayat transaksi'; $aktif = 'riwayat';

$dari = $_GET['dari'] ?? date('Y-m-d');
$sampai = $_GET['sampai'] ?? date('Y-m-d');
$st = $conn->prepare('SELECT t.id, t.kode_transaksi, t.tanggal, t.total, u.nama kasir,
    (SELECT SUM(jumlah) FROM detail_transaksi WHERE id_transaksi = t.id) item
    FROM transaksi t JOIN users u ON u.id = t.id_user
    WHERE DATE(t.tanggal) BETWEEN ? AND ? ORDER BY t.id DESC');
$st->bind_param('ss', $dari, $sampai); $st->execute();
$rows = $st->get_result()->fetch_all(MYSQLI_ASSOC);
include 'includes/header.php';
?>
<h1 class="h3 mb-3">Riwayat transaksi</h1>
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
<div class="panel p-0 table-responsive">
<table class="table mb-0">
  <thead>
    <tr>
      <th>Kode</th>
      <th>Waktu</th>
      <th>Kasir</th>
      <th class="text-end">Item</th>
      <th class="text-end">Total</th>
      <th></th>
    </tr>
  </thead>
  <tbody>
  <?php if (!$rows): ?>
    <tr>
      <td colspan="6" class="text-center text-secondary py-4">Tidak ada transaksi pada rentang tanggal ini.</td></tr><?php endif; ?>
  <?php foreach ($rows as $r): ?>
    <tr>
      <td><?= e($r['kode_transaksi']) ?></td>
      <td><?= date('d/m/Y H:i', strtotime($r['tanggal'])) ?></td>
      <td><?= e($r['kasir']) ?></td>
      <td class="text-end"><?= (int)$r['item'] ?></td>
      <td class="text-end"><?= rupiah($r['total']) ?></td>
      <td class="text-end"><a class="btn btn-sm btn-outline-secondary" href="struk.php?id=<?= $r['id'] ?>">Lihat struk</a></td></tr>
  <?php endforeach; ?>
  </tbody>
</table>
</div>
<?php include 'includes/footer.php'; ?>
