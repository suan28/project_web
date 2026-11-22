<?php
require_once 'includes/auth.php';
wajib_login();
$judul = 'Kelola menu'; $aktif = 'menu';
$notif = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = $_POST['aksi'] ?? '';
    if ($aksi === 'simpan') {
        $id = (int)$_POST['id']; $kat = (int)$_POST['id_kategori'];
        $nama = trim($_POST['nama_menu']); $harga = (int)$_POST['harga']; $status = $_POST['status'] === 'habis' ? 'habis' : 'tersedia';
        if ($nama === '' || $harga <= 0) { $notif = 'Nama menu dan harga wajib diisi dengan benar.'; }
        elseif ($id) {
            $st = $conn->prepare('UPDATE menu SET id_kategori=?, nama_menu=?, harga=?, status=? WHERE id=?');
            $st->bind_param('isisi', $kat, $nama, $harga, $status, $id); $st->execute();
            $notif = 'Menu diperbarui.';
        } else {
            $st = $conn->prepare('INSERT INTO menu (id_kategori, nama_menu, harga, status) VALUES (?,?,?,?)');
            $st->bind_param('isis', $kat, $nama, $harga, $status); $st->execute();
            $notif = 'Menu ditambahkan.';
        }
    } elseif ($aksi === 'hapus') {
        $id = (int)$_POST['id'];
        $st = $conn->prepare('DELETE FROM menu WHERE id=?'); $st->bind_param('i', $id); $st->execute();
        $notif = 'Menu dihapus. Riwayat transaksi lama tetap tersimpan.';
    } elseif ($aksi === 'toggle') {
        $id = (int)$_POST['id'];
        $st = $conn->prepare("UPDATE menu SET status = IF(status='tersedia','habis','tersedia') WHERE id=?");
        $st->bind_param('i', $id); $st->execute();
        $notif = 'Status menu diubah.';
    }
}

$kategori = $conn->query('SELECT id, nama_kategori FROM kategori ORDER BY id')->fetch_all(MYSQLI_ASSOC);
$rows = $conn->query('SELECT m.*, k.nama_kategori FROM menu m JOIN kategori k ON k.id = m.id_kategori ORDER BY k.id, m.nama_menu')->fetch_all(MYSQLI_ASSOC);
include 'includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h1 class="h3 m-0">Kelola menu</h1>
  <button class="btn btn-signage" data-bs-toggle="modal" data-bs-target="#modalMenu" onclick="isiForm()">Tambah menu</button>
</div>
<?php if ($notif): ?>
  <div class="alert alert-secondary py-2"><?= e($notif) ?>
</div><?php endif; ?>

<div class="panel p-0 table-responsive">
<table class="table align-middle mb-0">
  <thead>
    <tr>
      <th>Menu</th>
      <th>Kategori</th>
      <th class="text-end">Harga</th>
      <th>Status</th>
      <th></th>
    </tr>
  </thead>
  <tbody>
  <?php foreach ($rows as $r): ?>
    <tr>
      <td><?= e($r['nama_menu']) ?></td>
      <td><?= e($r['nama_kategori']) ?></td>
      <td class="text-end"><?= rupiah($r['harga']) ?></td>
      <td>
        <form method="post" class="d-inline">
          <input type="hidden" name="aksi" value="toggle">
          <input type="hidden" name="id" value="<?= $r['id'] ?>">
          <button class="btn btn-sm <?= $r['status'] === 'tersedia' ? 'btn-outline-success' : 'btn-outline-danger' ?>"><?= $r['status'] === 'tersedia' ? 'Tersedia' : 'Habis' ?></button></form>
      </td>
      <td class="text-end">
        <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#modalMenu"
          onclick='isiForm(<?= json_encode($r, JSON_NUMERIC_CHECK | JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>Ubah</button>
        <form method="post" class="d-inline" onsubmit="return confirm('Hapus menu <?= e($r['nama_menu']) ?>?')">
          <input type="hidden" name="aksi" value="hapus"><input type="hidden" name="id" value="<?= $r['id'] ?>">
          <button class="btn btn-sm btn-outline-danger">Hapus</button></form>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
</div>

<div class="modal fade" id="modalMenu" tabindex="-1">
  <div class="modal-dialog"><form method="post" class="modal-content">
  <div class="modal-header"><h2 class="modal-title h5" id="judulModal">Tambah menu</h2>
  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button></div>
  <div class="modal-body">
    <input type="hidden" name="aksi" value="simpan">
    <input type="hidden" name="id" id="f_id" value="0">
    <label class="form-label" for="f_nama">Nama menu</label>
    <input class="form-control mb-3" id="f_nama" name="nama_menu" required>
    <label class="form-label" for="f_kat">Kategori</label>
    <select class="form-select mb-3" id="f_kat" name="id_kategori"><?php foreach ($kategori as $k): ?>
      <option value="<?= $k['id'] ?>"><?= e($k['nama_kategori']) ?></option><?php endforeach; ?></select>
    <label class="form-label" for="f_harga">Harga (Rp)</label>
    <input class="form-control mb-3" type="number" min="100" step="100" id="f_harga" name="harga" required>
    <label class="form-label" for="f_status">Status</label>
    <select class="form-select" id="f_status" name="status">
      <option value="tersedia">Tersedia</option><option value="habis">Habis</option></select>
  </div>
  <div class="modal-footer">
    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button><button class="btn btn-signage">Simpan menu</button></div>
</form>
</div
></div>
<script>
function isiForm(m) {
  m = m || { id: 0, nama_menu: '', id_kategori: document.getElementById('f_kat').options[0].value, harga: '', status: 'tersedia' };
  document.getElementById('judulModal').textContent = m.id ? 'Ubah menu' : 'Tambah menu';
  f_id.value = m.id; f_nama.value = m.nama_menu; f_kat.value = m.id_kategori; f_harga.value = m.harga; f_status.value = m.status;
}
</script>
<?php include 'includes/footer.php'; ?>
