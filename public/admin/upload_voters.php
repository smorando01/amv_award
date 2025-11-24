<?php
require_once __DIR__.'/_auth.php';
require_once __DIR__.'/../../db.php';
$pdo = db();

$msg = '';
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_FILES['csv'])) {
  if ($_FILES['csv']['error']===UPLOAD_ERR_OK) {
    $tmp = $_FILES['csv']['tmp_name'];
    $fh = fopen($tmp, 'r');
    $header = fgetcsv($fh, 0, ',');
    // expected header: ci,name,email,sector,role,active
    $map = array_map('strtolower', $header ?? []);
    $req = ['ci','name','email','sector','role','active'];
    if (array_diff($req, $map)) {
      $msg = 'Encabezados CSV inválidos. Deben ser: ci,name,email,sector,role,active';
    } else {
      $ci_i = array_search('ci',$map);
      $name_i = array_search('name',$map);
      $email_i = array_search('email',$map);
      $sector_i = array_search('sector',$map);
      $role_i = array_search('role',$map);
      $active_i = array_search('active',$map);
      $ins = $pdo->prepare("REPLACE INTO voters (ci,name,email,sector,role,active) VALUES (?,?,?,?,?,?)");
      $n=0;
      while(($row=fgetcsv($fh,0,','))!==false){
        $ci = preg_replace('/\D+/', '', $row[$ci_i] ?? '');
        if (!$ci) continue;
        $name = trim($row[$name_i] ?? '');
        $email = trim($row[$email_i] ?? '');
        $sector = trim($row[$sector_i] ?? '');
        $role = strtolower(trim($row[$role_i] ?? 'empleado'));
        if ($role!=='empleado' && $role!=='encargado') $role='empleado';
        $active = (int)($row[$active_i] ?? 1);
        $ins->execute([$ci,$name,$email,$sector,$role,$active]);
        $n++;
      }
      fclose($fh);
      $msg = "Se importaron/actualizaron $n registros.";
    }
  } else {
    $msg = 'Error al subir el archivo.';
  }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Subir votantes - <?= htmlspecialchars(SITE_NAME) ?></title>
<style>
  body{font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif;margin:0;background:#0b0b0f;color:#fff}
  .wrap{max-width:720px;margin:40px auto;padding:24px}
  .card{background:#15151b;border:1px solid #23232b;border-radius:16px;padding:24px;box-shadow:0 10px 30px rgba(0,0,0,.35)}
  .msg{margin-top:10px}
  a{color:#aab4ff}
  input[type=file]{background:#0f0f14;border:1px solid #34343f;border-radius:12px;padding:10px}
  button{background:#7c5cff;border:1px solid #7c5cff;color:#fff;padding:10px 14px;border-radius:12px;margin-top:10px;cursor:pointer}
  .foot{opacity:.7;font-size:13px;margin-top:12px}
  code{background:#111;padding:2px 6px;border-radius:6px;border:1px solid #333}
</style>
</head>
<body>
<div class="wrap">
  <div class="card">
    <h1>📥 Cargar/actualizar votantes (CSV)</h1>
    <form method="post" enctype="multipart/form-data">
      <input type="file" name="csv" accept=".csv" required>
      <button>Subir</button>
    </form>
    <?php if ($msg): ?><div class="msg"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
    <div class="foot">
      Formato esperado de columnas: <code>ci,name,email,sector,role,active</code><br>
      <a href="sample_voters.csv">Descargar plantilla</a>
    </div>
    <p class="foot"><a href="ranking.php">⬅ Volver al ranking</a></p>
  </div>
</div>
</body>
</html>
