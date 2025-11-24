<?php
require_once __DIR__.'/../../config.php';
if (!isset($_SESSION['admin_ok'])) {
  if ($_SERVER['REQUEST_METHOD']==='POST') {
    if (hash_equals(ADMIN_PASSWORD, $_POST['password'] ?? '')) {
      $_SESSION['admin_ok'] = true;
      header('Location: ranking.php'); exit;
    }
  }
  ?>
  <!DOCTYPE html>
  <html lang="es">
  <head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin - <?= htmlspecialchars(SITE_NAME) ?></title>
  <style>
    body{font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif;margin:0;background:#0b0b0f;color:#fff}
    .wrap{max-width:420px;margin:60px auto;padding:24px}
    .card{background:#15151b;border:1px solid #23232b;border-radius:16px;padding:24px;box-shadow:0 10px 30px rgba(0,0,0,.35)}
    input,button{width:100%;padding:12px 14px;border-radius:12px;border:1px solid #34343f;background:#0f0f14;color:#fff;outline:none}
    button{background:#7c5cff;border-color:#7c5cff;font-weight:700;cursor:pointer;margin-top:10px}
  </style>
  </head>
  <body><div class="wrap"><div class="card">
    <h2>Admin – Acceso</h2>
    <form method="post">
      <input type="password" name="password" placeholder="Contraseña admin" required>
      <button type="submit">Entrar</button>
    </form>
  </div></div></body></html>
  <?php
  exit;
}
?>
