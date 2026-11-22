<?php
require_once 'includes/auth.php';
wajib_login();
$id = (int)($_GET['id'] ?? 0);
$st = $conn->prepare('SELECT t.*, u.nama kasir FROM transaksi t JOIN users u ON u.id = t.id_user WHERE t.id = ?');
$st->bind_param('i', $id); $st->execute();
$t = $st->get_result()->fetch_assoc();
if (!$t) { http_response_code(404); exit('Transaksi tidak ditemukan.'); }
$d = $conn->query("SELECT * FROM detail_transaksi WHERE id_transaksi = $id")->fetch_all(MYSQLI_ASSOC);
$judul = 'Struk'; $aktif = 'riwayat';
include 'includes/header.php';
?>
<div class="struk">
  <div class="text-center"><strong>MOTEKAR</strong><br>Struk pembelian</div>
  <hr>
  <div><?= e($t['kode_transaksi']) ?></div>
  <div><?= date('d/m/Y H:i', strtotime($t['tanggal'])) ?></div>
  <div>Kasir: <?= e($t['kasir']) ?></div>
  <hr>
  <?php foreach ($d as $r): ?>
    <div><?= e($r['nama_menu']) ?></div>
    <div class="d-flex justify-content-between"><span><?= $r['jumlah'] ?> x <?= number_format($r['harga_satuan'], 0, ',', '.') ?></span><span><?= number_format($r['subtotal'], 0, ',', '.') ?></span></div>
  <?php endforeach; ?>
  <hr>
  <div class="d-flex justify-content-between"><strong>Total</strong><strong><?= rupiah($t['total']) ?></strong></div>
  <div class="d-flex justify-content-between"><span>Bayar</span><span><?= rupiah($t['bayar']) ?></span></div>
  <div class="d-flex justify-content-between"><span>Kembali</span><span><?= rupiah($t['kembalian']) ?></span></div>
  <hr>
  <div class="text-center">Terima kasih, selamat menikmati!</div>
</div>
<div class="text-center no-print mb-4">
  <button class="btn btn-signage" onclick="window.print()">Cetak struk</button>
  <a class="btn btn-outline-secondary" href="transaksi.php">Transaksi baru</a>
</div>
<?php include 'includes/footer.php'; ?>
