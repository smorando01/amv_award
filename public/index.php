<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>AMV Store Award 2.0</title>
  <link rel="stylesheet" href="/assets/style.css">
</head>
<body>
  <main class="shell">
    <header class="hero">
      <div>
        <div class="badge">AMV Store Award · 2025</div>
        <div class="title">Empleado/a del Año</div>
        <p class="subtitle">SPA en modo oscuro conectada a la API REST segura. Un voto por persona, ponderado por rol.</p>
      </div>
      <div class="pill">Encargado = 2 pts · Empleado = 1 pt</div>
    </header>

    <div id="status" class="status hidden"></div>

    <section id="login-card" class="panel">
      <h2>Ingresá para votar</h2>
      <form id="login-form" autocomplete="off">
        <label for="identifier">Cédula o correo corporativo</label>
        <input id="identifier" name="identifier" placeholder="12345678 o tu@amvstore.com.uy" required>

        <label for="password">Contraseña</label>
        <input id="password" name="password" type="password" placeholder="••••••••" required>

        <p class="subtitle">Las credenciales se validan contra la API. Los encargados no pueden ser votados, pero su voto vale doble.</p>
        <button type="submit">Ingresar</button>
      </form>
    </section>

    <section id="dashboard-card" class="panel hidden">
      <div class="header-row">
        <div>
          <div class="title" id="user-name"></div>
          <div class="subtitle" id="user-role"></div>
        </div>
        <div class="actions">
          <span class="pill" id="vote-badge"></span>
          <button class="secondary" id="logout" type="button">Cerrar sesión</button>
        </div>
      </div>
      <div class="subtitle" style="margin-bottom:14px;">Elegí a quién votás. El sistema evita votos repetidos y auto-votos.</div>
      <div class="grid" id="candidate-list"></div>
    </section>

    <section id="ranking-card" class="panel hidden">
      <div class="header-row">
        <div>
          <div class="title">Ranking en tiempo real</div>
          <div class="subtitle">Suma puntos ponderados. Visible solo para usuarios autenticados.</div>
        </div>
      </div>
      <div class="ranking-list" id="ranking-list"></div>
    </section>
  </main>

  <script src="/assets/app.js" defer></script>
</body>
</html>
