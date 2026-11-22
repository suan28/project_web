<?php
require_once 'includes/auth.php';
wajib_login();
$judul = 'Kasir'; $aktif = 'transaksi';

$kategori = $conn->query('SELECT id, nama_kategori FROM kategori ORDER BY id')->fetch_all(MYSQLI_ASSOC);
$menu = $conn->query('SELECT id, id_kategori, nama_menu, harga, status FROM menu ORDER BY nama_menu')->fetch_all(MYSQLI_ASSOC);
include 'includes/header.php';
?>
<div class="row g-3">
  <div class="col-lg-8">
    <div class="d-flex flex-wrap gap-2 mb-3 align-items-center">
      <button class="chip on" data-kat="0">Semua</button>
      <?php foreach ($kategori as $k): ?>
        <button class="chip" data-kat="<?= $k['id'] ?>"><?= e($k['nama_kategori']) ?></button>
      <?php endforeach; ?>
      <input id="cari" class="form-control form-control-sm ms-auto" style="max-width:200px" placeholder="Cari menu" aria-label="Cari menu">
    </div>
    <div class="menu-grid" id="grid"></div>
  </div>

  <div class="col-lg-4">
    <div class="panel cart">
      <h2>Pesanan</h2>
      <div id="isi"></div>
      <div class="total-box mt-3 d-flex justify-content-between align-items-center">
        <span>Total</span><span class="angka" id="total">Rp 0</span>
      </div>
      <label class="form-label mt-3" for="bayar">Uang dibayar</label>
      <input id="bayar" class="form-control" inputmode="numeric" placeholder="0">
      <div class="d-flex flex-wrap gap-2 mt-2" id="cepat"></div>
      <div class="d-flex justify-content-between mt-3"><span>Kembalian</span><strong id="kembali">Rp 0</strong></div>
      <div id="pesan" class="text-danger small mt-2"></div>
      <button id="simpan" class="btn btn-signage w-100 mt-3 py-2" disabled>Simpan transaksi</button>
      <button id="kosong" class="btn btn-link text-secondary w-100 mt-1">Kosongkan pesanan</button>
    </div>
  </div>
</div>

<script>
const MENU = <?= json_encode($menu, JSON_NUMERIC_CHECK) ?>;
let katAktif = 0, keranjang = {};
const rp = n => 'Rp ' + Number(n).toLocaleString('id-ID');
const $ = id => document.getElementById(id);

function gambarMenu() {
  const q = $('cari').value.toLowerCase();
  $('grid').innerHTML = MENU
    .filter(m => (katAktif === 0 || m.id_kategori === katAktif) && m.nama_menu.toLowerCase().includes(q))
    .map(m => `<button class="menu-item" data-id="${m.id}" ${m.status === 'habis' ? 'disabled' : ''}>
        <span class="nama">${m.nama_menu}</span>
        <span class="harga">${m.status === 'habis' ? 'Habis' : rp(m.harga)}</span></button>`).join('');
}

function gambarKeranjang() {
  const ids = Object.keys(keranjang);
  $('isi').innerHTML = ids.length ? ids.map(id => {
    const m = MENU.find(x => x.id == id), q = keranjang[id];
    return `<div class="cart-row"><div><strong>${m.nama_menu}</strong><div class="text-secondary small">${rp(m.harga)}</div></div>
      <div class="text-end"><strong>${rp(m.harga * q)}</strong></div>
      <div class="qty d-flex align-items-center gap-2">
        <button class="btn btn-outline-secondary" data-aksi="kurang" data-id="${id}" aria-label="Kurangi">−</button>
        <span>${q}</span>
        <button class="btn btn-outline-secondary" data-aksi="tambah" data-id="${id}" aria-label="Tambah">+</button></div></div>`;
  }).join('') : '<p class="text-secondary mb-0">Pilih menu di sebelah kiri untuk memulai pesanan.</p>';
  hitung();
}

function total() { return Object.entries(keranjang).reduce((s, [id, q]) => s + MENU.find(x => x.id == id).harga * q, 0); }

function hitung() {
  const t = total(), b = parseInt(($('bayar').value || '').replace(/\D/g, '')) || 0;
  $('total').textContent = rp(t);
  $('kembali').textContent = rp(Math.max(0, b - t));
  const kurang = t > 0 && b < t;
  $('pesan').textContent = kurang ? `Uang kurang ${rp(t - b)}.` : '';
  $('simpan').disabled = !(t > 0 && b >= t);
  const opsi = [...new Set([t, Math.ceil(t / 5000) * 5000, Math.ceil(t / 10000) * 10000, 50000, 100000])].filter(x => x >= t && x > 0).slice(0, 4);
  $('cepat').innerHTML = opsi.map(x => `<button class="btn btn-sm btn-outline-secondary" data-bayar="${x}">${rp(x)}</button>`).join('');
}

$('grid').addEventListener('click', e => {
  const b = e.target.closest('.menu-item'); if (!b) return;
  keranjang[b.dataset.id] = (keranjang[b.dataset.id] || 0) + 1; gambarKeranjang();
});
$('isi').addEventListener('click', e => {
  const b = e.target.closest('button'); if (!b) return;
  const id = b.dataset.id;
  if (b.dataset.aksi === 'tambah') keranjang[id]++;
  else if (--keranjang[id] <= 0) delete keranjang[id];
  gambarKeranjang();
});
$('cepat').addEventListener('click', e => {
  const b = e.target.closest('button'); if (!b) return;
  $('bayar').value = b.dataset.bayar; hitung();
});
document.querySelectorAll('.chip').forEach(c => c.addEventListener('click', () => {
  document.querySelectorAll('.chip').forEach(x => x.classList.remove('on'));
  c.classList.add('on'); katAktif = +c.dataset.kat; gambarMenu();
}));
$('cari').addEventListener('input', gambarMenu);
$('bayar').addEventListener('input', hitung);
$('kosong').addEventListener('click', () => { keranjang = {}; $('bayar').value = ''; gambarKeranjang(); });

$('simpan').addEventListener('click', async () => {
  $('simpan').disabled = true;
  const items = Object.entries(keranjang).map(([id, qty]) => ({ id: +id, qty }));
  const bayar = parseInt($('bayar').value.replace(/\D/g, '')) || 0;
  try {
    const res = await fetch('simpan_transaksi.php', {
      method: 'POST', headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ items, bayar })
    });
    const data = await res.json();
    if (data.ok) location.href = 'struk.php?id=' + data.id;
    else { $('pesan').textContent = data.pesan; $('simpan').disabled = false; }
  } catch (err) {
    $('pesan').textContent = 'Transaksi belum tersimpan. Periksa koneksi lalu coba lagi.';
    $('simpan').disabled = false;
  }
});

gambarMenu(); gambarKeranjang();
</script>
<?php include 'includes/footer.php'; ?>
