<?php
require_once __DIR__ . '/../config/koneksi.php';

function wajib_login() {
    if (empty($_SESSION['user'])) { header('Location: login.php'); exit; }
}
function wajib_admin() {
    wajib_login();
    if ($_SESSION['user']['role'] !== 'admin') {
        http_response_code(403);
        exit('Halaman ini hanya untuk admin.');
    }
}
