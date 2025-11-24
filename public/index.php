<?php
require_once __DIR__.'/../config.php';
require_once __DIR__.'/../db.php';

$pdo = db();

// If already voted, redirect to thanks
if (isset($_SESSION['voted']) && $_SESSION['voted'] === true) {
  header('Location: gracias.php'); exit;
}

// Handle login
$login_error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action']==='login') {
  csrf_check();
  $ci = preg_replace('/\D+/', '', $_POST['ci'] ?? '');
  $email = trim($_POST['email'] ?? '');

  if ($ci === '') {
    $login_error = 'Ingresá tu cédula (solo números).';
  } else {
    // fetch voter
    $st = $pdo->prepare("SELECT * FROM voters WHERE ci = ? AND active = 1 LIMIT 1");
    $st->execute([$ci]);
    $me = $st->fetch();

    if (!$me) {
      $login_error = 'No estás habilitado/a para votar. Verificá tu cédula.';
    } else {
      // Optional email check domain
      if (!empty($email) && defined('ALLOW_DOMAINS_REGEX') && ALLOW_DOMAINS_REGEX) {
        if (!preg_match(ALLOW_DOMAINS_REGEX, $email)) {
          $login_error = 'El correo no pertenece al dominio permitido.';
        }
      }
      if ($login_error==='') {
        // Check if already voted
        $chk = $pdo->prepare("SELECT 1 FROM votes WHERE voter_ci = ? LIMIT 1");
        $chk->execute([$ci]);
        if ($chk->fetchColumn()) {
          $_SESSION['voted'] = true;
          header('Location: gracias.php'); exit;
        }
        // Set session
        $_SESSION['me'] = [
          'ci' => $me['ci'],
          'name' => $me['name'],
          'role' => $me['role'],
          'sector' => $me['sector'],
          'email' => $email,
        ];
      }
    }
  }
}

// Handle vote
$vote_error = $vote_ok = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action']==='vote') {
  csrf_check();
  if (empty($_SESSION['me'])) { header('Location: index.php'); exit; }
  $me = $_SESSION['me'];
  $voted_ci = preg_replace('/\D+/', '', $_POST['voted_ci'] ?? '');

  if ($voted_ci === '') {
    $vote_error = 'Seleccioná a quién votás.';
  } elseif ($voted_ci === $me['ci']) {
    $vote_error = 'No podés votarte a vos mismo/a.';
  } else {
    // Ensure voted person is eligible (must be empleado and active)
    $st = $pdo->prepare("SELECT ci,name,role FROM voters WHERE ci = ? AND active=1 LIMIT 1");
    $st->execute([$voted_ci]);
    $cand = $st->fetch();
    if (!$cand) {
      $vote_error = 'El candidato seleccionado no es válido.';
    } elseif ($cand['role'] !== 'empleado') {
      $vote_error = 'No podés votar encargados. Elegí un empleado habilitado.';
    } else {
      // Insert vote if not exists
      try {
        $ins = $pdo->prepare("INSERT INTO votes (voter_ci, voted_ci) VALUES (?, ?)");
        $ins->execute([$me['ci'], $voted_ci]);
        $_SESSION['voted'] = true;
        header('Location: gracias.php'); exit;
      } catch (PDOException $e) {
        if ($e->getCode() === '23000') {
          // unique violation
          $_SESSION['voted'] = true;
          header('Location: gracias.php'); exit;
        } else {
          $vote_error = 'Error al registrar el voto.';
        }
      }
    }
  }
}

// Pull candidates list (empleados activos), excluding self if logged
$candidates = [];
$me = $_SESSION['me'] ?? null;
if ($me) {
  $st = $pdo->query("SELECT ci,name FROM voters WHERE role='empleado' AND active=1 ORDER BY name ASC");
  $all = $st->fetchAll();
  foreach ($all as $row) {
    if ($row['ci'] !== $me['ci']) $candidates[] = $row;
  }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= htmlspecialchars(SITE_NAME) ?></title>
<style>
  body{font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif;margin:0;background:#0b0b0f;color:#fff}
  .wrap{max-width:720px;margin:40px auto;padding:24px}
  .card{background:#15151b;border:1px solid #23232b;border-radius:16px;padding:24px;box-shadow:0 10px 30px rgba(0,0,0,.35)}
  h1{margin:0 0 12px;font-size:28px}
  h2{margin:8px 0 18px;font-size:18px;color:#c9c9d1;font-weight:600}
  label{display:block;margin:12px 0 6px;color:#c9c9d1}
  input,select,button{width:100%;padding:12px 14px;border-radius:12px;border:1px solid #34343f;background:#0f0f14;color:#fff;outline:none}
  input:focus,select:focus{border-color:#6ea8fe;box-shadow:0 0 0 4px rgba(110,168,254,.15)}
  button{background:#7c5cff;border-color:#7c5cff;font-weight:700;cursor:pointer}
  button:hover{filter:brightness(1.05)}
  .error{background:#401d25;border:1px solid #7a2d3f;color:#ffcad4;padding:12px;border-radius:12px;margin:10px 0}
  .ok{background:#163522;border:1px solid #2a6b43;color:#b9f6cf;padding:12px;border-radius:12px;margin:10px 0}
  .foot{opacity:.7;font-size:13px;margin-top:12px}
</style>
</head>
<body>
<div class="wrap">
  <div class="card">
    <h1>🏆 <?= htmlspecialchars(SITE_NAME) ?></h1>
    <h2>Votación al Empleado/a del Año</h2>

    <?php if (!$me): ?>
      <?php if ($login_error): ?><div class="error"><?= htmlspecialchars($login_error) ?></div><?php endif; ?>
      <form method="post">
        <?php csrf_input(); ?>
        <input type="hidden" name="action" value="login">
        <label>Número de cédula (solo números)</label>
        <input name="ci" inputmode="numeric" pattern="\d+" required placeholder="Ej: 51234567">
        <label>Correo (opcional, corporativo)</label>
        <input name="email" type="email" placeholder="tu@amvstore.com.uy">
        <div class="foot">Los datos se usan solo para validar un único voto por persona.</div>
        <button type="submit" style="margin-top:14px">Ingresar y votar</button>
      </form>
    <?php else: ?>
      <?php if ($vote_error): ?><div class="error"><?= htmlspecialchars($vote_error) ?></div><?php endif; ?>
      <form method="post">
        <?php csrf_input(); ?>
        <input type="hidden" name="action" value="vote">
        <label>Estás votando como:</label>
        <input value="<?= htmlspecialchars($me['name'].' (CI '.$me['ci'].')') ?>" readonly>
        <label>Elegí a tu compañero/a</label>
        <select name="voted_ci" required>
          <option value="" disabled selected>— Seleccioná —</option>
          <?php foreach ($candidates as $c): ?>
            <option value="<?= htmlspecialchars($c['ci']) ?>"><?= htmlspecialchars($c['name']) ?></option>
          <?php endforeach; ?>
        </select>
        <button type="submit" style="margin-top:14px">Enviar voto</button>
      </form>
      <div class="foot">No podés votarte a vos mismo/a. Encargados no son candidatos.</div>
    <?php endif; ?>
  </div>
</div>
</body>
</html>
