<?php
require_once 'includes/auth.php';
wajib_login();
$judul = 'Stok bahan'; $aktif = 'bahan';
$notif = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = $_POST['aksi'] ?? '';
    if ($aksi === 'simpan') {
        $id = (int)$_POST['id']; $nama = trim($_POST['nama_bahan']); $stok = (float)$_POST['stok'];
        $satuan = trim($_POST['satuan']); $min = (float)$_POST['stok_minimum'];
        if ($nama === '' || $satuan === '') { $notif = 'Nama bahan dan satuan wajib diisi.'; }
        elseif ($id) {
            $st = $conn->prepare('UPDATE bahan SET nama_bahan=?, stok=?, satuan=?, stok_minimum=? WHERE id=?');
            $st->bind_param('sdsdi', $nama, $stok, $satuan, $min, $id); $st->execute(); $notif = 'Bahan diperbarui.';
        } else {
            $st = $conn->prepare('INSERT INTO bahan (nama_bahan, stok, satuan, stok_minimum) VALUES (?,?,?,?)');
            $st->bind_param('sdsd', $nama, $stok, $satuan, $min); $st->execute(); $notif = 'Bahan ditambahkan.';
        }
    } elseif ($aksi === 'tambah_stok') {
        $id = (int)$_POST['id']; $jml = (float)$_POST['jumlah'];
        $st = $conn->prepare('UPDATE bahan SET stok = stok + ? WHERE id=?'); $st->bind_param('di', $jml, $id); $st->execute();
        $notif = 'Stok diperbarui.';
    } elseif ($aksi === 'hapus') {
        $id = (int)$_POST['id'];
        $st = $conn->prepare('DELETE FROM bahan WHERE id=?'); $st->bind_param('i', $id); $st->execute(); $notif = 'Bahan dihapus.';
    }
}
$rows = $conn->query('SELECT * FROM bahan ORDER BY nama_bahan')->fetch_all(MYSQLI_ASSOC);
include 'includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h1 class="h3 m-0">Stok bahan</h1>
  <button class="btn btn-signage" data-bs-toggle="modal" data-bs-target="#modalBahan" onclick="isiForm()">Tambah bahan</button>
</div>
<?php if ($notif): ?><div class="alert alert-secondary py-2"><?= e($notif) ?></div><?php endif; ?>

<div class="panel p-0 table-responsive">
<table class="table align-middle mb-0">
  <thead>
    <tr>
      <th>Bahan</th>
      <th class="text-end">Stok</th>
      <th>Satuan</th>
      <th>Kondisi</th
      ><th>Tambah stok</th>
      <th></th>
    </tr>
  </thead>
  <tbody>
  <?php foreach ($rows as $r): $tipis = $r['stok'] <= $r['stok_minimum']; ?>
    <tr>
      <td><?= e($r['nama_bahan']) ?></td>
      <td class="text-end"><strong class="<?= $tipis ? 'text-danger' : '' ?>"><?= (float)$r['stok'] ?></strong></td>
      <td><?= e($r['satuan']) ?></td>
      <td><?= $tipis ? '<span class="badge text-bg-danger">Hampir habis</span>' : '<span class="badge text-bg-success">Aman</span>' ?></td>
      <td>
        <form method="post" class="d-flex gap-1" style="max-width:170px">
          <input type="hidden" name="aksi" value="tambah_stok">
          <input type="hidden" name="id" value="<?= $r['id'] ?>">
          <input class="form-control form-control-sm" type="number" step="0.01" name="jumlah" required aria-label="Jumlah tambahan">
          <button class="btn btn-sm btn-outline-secondary">Tambah</button>
        </form>
      </td>
      <td class="text-end">
        <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#modalBahan"
          onclick='isiForm(<?= json_encode($r, JSON_NUMERIC_CHECK | JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>Ubah</button>
        <form method="post" class="d-inline" onsubmit="return confirm('Hapus bahan <?= e($r['nama_bahan']) ?>?')">
          <input type="hidden" name="aksi" value="hapus"><input type="hidden" name="id" value="<?= $r['id'] ?>">
          <button class="btn btn-sm btn-outline-danger">Hapus</button></form>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
</div>

<div class="modal fade" id="modalBahan" tabindex="-1">
  <div class="modal-dialog"><form method="post" class="modal-content">
  <div class="modal-header"><h2 class="modal-title h5" id="judulModal">Tambah bahan</h2>
  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
</div>
  <div class="modal-body">
    <input type="hidden" name="aksi" value="simpan">
    <input type="hidden" name="id" id="f_id" value="0">
    <label class="form-label" for="f_nama">Nama bahan</label>
    <input class="form-control mb-3" id="f_nama" name="nama_bahan" required>
    <div class="row g-2">
      <div class="col">
        <label class="form-label" for="f_stok">Stok</label>
        <input class="form-control" type="number" step="0.01" id="f_stok" name="stok" required>
      </div>
      <div class="col">
        <label class="form-label" for="f_satuan">Satuan</label>
        <input class="form-control" id="f_satuan" name="satuan" placeholder="kg, butir, bungkus" required>
      </div>
    </div>
    <label class="form-label mt-3" for="f_min">Batas stok menipis</label
    ><input class="form-control" type="number" step="0.01" id="f_min" name="stok_minimum" required>
  </div>
  <div class="modal-footer">
    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
    <button class="btn btn-signage">Simpan bahan</button></div>
</form>
</div>
</div>
<script>
function isiForm(b) {
  b = b || { id: 0, nama_bahan: '', stok: '', satuan: '', stok_minimum: 5 };
  document.getElementById('judulModal').textContent = b.id ? 'Ubah bahan' : 'Tambah bahan';
  f_id.value = b.id; f_nama.value = b.nama_bahan; f_stok.value = b.stok; f_satuan.value = b.satuan; f_min.value = b.stok_minimum;
}
</script>
<?php include 'includes/footer.php'; ?>
