<?php
require_once 'includes/auth.php';
header('Content-Type: application/json');
if (empty($_SESSION['user'])) { echo json_encode(['ok' => false, 'pesan' => 'Sesi habis. Masuk lagi.']); exit; }

$in = json_decode(file_get_contents('php://input'), true);
$items = $in['items'] ?? [];
$bayar = (int)($in['bayar'] ?? 0);
if (!$items) { echo json_encode(['ok' => false, 'pesan' => 'Pesanan masih kosong.']); exit; }

$conn->begin_transaction();
try {
    $baris = []; $total = 0;
    $cek = $conn->prepare("SELECT id, nama_menu, harga FROM menu WHERE id = ? AND status = 'tersedia'");
    foreach ($items as $it) {
        $id = (int)$it['id']; $qty = max(1, (int)$it['qty']);
        $cek->bind_param('i', $id); $cek->execute();
        $m = $cek->get_result()->fetch_assoc();
        if (!$m) throw new Exception('Ada menu yang sudah habis atau dihapus. Muat ulang halaman kasir.');
        $sub = $m['harga'] * $qty; $total += $sub;
        $baris[] = [$m['id'], $m['nama_menu'], $qty, $m['harga'], $sub];
    }
    if ($bayar < $total) throw new Exception('Uang dibayar kurang dari total.');

    $kode = 'WM' . date('ymdHis') . random_int(10, 99);
    $tgl = date('Y-m-d H:i:s'); $kembali = $bayar - $total; $uid = $_SESSION['user']['id'];
    $st = $conn->prepare('INSERT INTO transaksi (kode_transaksi, id_user, tanggal, total, bayar, kembalian) VALUES (?,?,?,?,?,?)');
    $st->bind_param('sisiii', $kode, $uid, $tgl, $total, $bayar, $kembali);
    $st->execute();
    $idT = $conn->insert_id;

    $sd = $conn->prepare('INSERT INTO detail_transaksi (id_transaksi, id_menu, nama_menu, jumlah, harga_satuan, subtotal) VALUES (?,?,?,?,?,?)');
    foreach ($baris as [$mid, $nama, $qty, $harga, $sub]) {
        $sd->bind_param('iisiii', $idT, $mid, $nama, $qty, $harga, $sub);
        $sd->execute();
    }
    $conn->commit();
    echo json_encode(['ok' => true, 'id' => $idT]);
} catch (Exception $ex) {
    $conn->rollback();
    echo json_encode(['ok' => false, 'pesan' => $ex->getMessage()]);
}
