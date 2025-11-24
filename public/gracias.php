<?php
require_once __DIR__.'/../config.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Gracias - <?= htmlspecialchars(SITE_NAME) ?></title>
<style>
  body{font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif;margin:0;background:#0b0b0f;color:#fff}
  .wrap{max-width:720px;margin:40px auto;padding:24px}
  .card{background:#15151b;border:1px solid #23232b;border-radius:16px;padding:24px;box-shadow:0 10px 30px rgba(0,0,0,.35)}
  h1{margin:0 0 12px;font-size:28px}
  p{opacity:.9}
  a.btn{display:inline-block;margin-top:16px;padding:10px 14px;border-radius:10px;background:#7c5cff;color:#fff;text-decoration:none}
</style>
</head>
<body>
<div class="wrap">
  <div class="card">
    <h1>✅ ¡Tu voto fue registrado!</h1>
    <p>Gracias por participar en el <strong><?= htmlspecialchars(SITE_NAME) ?></strong>.</p>
    <a class="btn" href="index.php">Volver</a>
  </div>
</div>
</body>
</html>
