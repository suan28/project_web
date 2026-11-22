<?php
require_once 'config/koneksi.php';
if (!empty($_SESSION['user'])) { header('Location: index.php'); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $conn->prepare('SELECT id, nama, username, password, role FROM users WHERE username = ?');
    $stmt->bind_param('s', $_POST['username']);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    if ($row && password_verify($_POST['password'], $row['password'])) {
        session_regenerate_id(true);
        unset($row['password']);
        $_SESSION['user'] = $row;
        header('Location: index.php'); exit;
    }
    $error = 'Username atau password salah. Periksa lagi lalu coba masuk.';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Masuk · Motekar</title>
<link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,500;12..96,700&family=Figtree:wght@400;500;600&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="assets/style.css" rel="stylesheet">
</head>
<body>
<div class="login-wrap">
  <form class="login-card" method="post">
    <h1 class="h3 mb-1">Motekar<span style="color:var(--signage-dark)">.</span></h1>
    <p class="text-secondary mb-4">Masuk untuk membuka kasir.</p>
    <?php if ($error): ?><div class="alert alert-danger py-2"><?= e($error) ?></div><?php endif; ?>
    <label class="form-label" for="username">Username</label>
    <input class="form-control mb-3" id="username" name="username" required autofocus>
    <label class="form-label" for="password">Password</label>
    <input class="form-control mb-4" type="password" id="password" name="password" required>
    <button class="btn btn-signage w-100 py-2">Masuk</button>
  </form>
</div>
</body>
</html>
