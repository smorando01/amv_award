<?php
require_once __DIR__.'/_auth.php';
require_once __DIR__.'/../../db.php';

$pdo = db();

// Traer ranking con ponderación (2 puntos para encargados, 1 para empleados)
$sql = "
SELECT vto.ci, vto.name,
       SUM(CASE WHEN vtr.role='encargado' THEN 2 ELSE 1 END) AS puntos,
       SUM(CASE WHEN vtr.role='encargado' THEN 1 ELSE 0 END) AS votos_dobles,
       SUM(CASE WHEN vtr.role='empleado' THEN 1 ELSE 0 END) AS votos_simples
FROM votes vt
JOIN voters vtr ON vtr.ci = vt.voter_ci
JOIN voters vto ON vto.ci = vt.voted_ci
WHERE vto.role='empleado' AND vto.active=1
GROUP BY vto.ci, vto.name
ORDER BY puntos DESC, vto.name ASC;
";
$ranking = $pdo->query($sql)->fetchAll();

// Total de votos
$total = $pdo->query("SELECT COUNT(*) FROM votes")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Ranking - <?= htmlspecialchars(SITE_NAME) ?></title>
<style>
  body{font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif;margin:0;background:#0b0b0f;color:#fff}
  .wrap{max-width:920px;margin:40px auto;padding:24px}
  .card{background:#15151b;border:1px solid #23232b;border-radius:16px;padding:24px;box-shadow:0 10px 30px rgba(0,0,0,.35)}
  table{width:100%;border-collapse:collapse;margin-top:12px}
  th,td{padding:10px;border-bottom:1px solid #2b2b33;text-align:left}
  th{color:#c9c9d1;font-weight:700}
  .bar{height:12px;border-radius:6px;background:#222;overflow:hidden}
  .bar > div{height:100%;background:#7c5cff}
  .head{display:flex;justify-content:space-between;gap:12px;align-items:center;flex-wrap:wrap}
  .pill{background:#222;border:1px solid #34343f;border-radius:999px;padding:6px 10px;font-size:13px}
  a.btn{color:#fff;text-decoration:none;background:#7c5cff;padding:8px 12px;border-radius:10px}
</style>
</head>
<body>
<div class="wrap">
  <div class="card">
    <div class="head">
      <h1>🏁 Ranking – <?= htmlspecialchars(SITE_NAME) ?></h1>
      <span class="pill">Total votos: <?= (int)$total ?></span>
      <a class="btn" href="upload_voters.php">Cargar votantes (CSV)</a>
    </div>

    <table>
      <thead>
        <tr><th>#</th><th>Empleado</th><th>Puntos</th><th>Simples</th><th>Dobles</th><th>%</th></tr>
      </thead>
      <tbody>
      <?php
      $max = 0; foreach ($ranking as $r) { if ($r['puntos']>$max) $max=$r['puntos']; }
      $i=1;
      foreach ($ranking as $r):
        $pct = $max ? round(($r['puntos']/$max)*100) : 0;
      ?>
        <tr>
          <td><?= $i++ ?></td>
          <td><?= htmlspecialchars($r['name']) ?></td>
          <td><?= (int)$r['puntos'] ?></td>
          <td><?= (int)$r['votos_simples'] ?></td>
          <td><?= (int)$r['votos_dobles'] ?></td>
          <td style="min-width:160px">
            <div class="bar"><div style="width: <?= $pct ?>%"></div></div>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
</body>
</html>
