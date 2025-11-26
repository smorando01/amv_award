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

    <section id="admin-card" class="panel hidden">
      <div class="header-row">
        <div>
          <div class="title">Mantenimiento de usuarios</div>
          <div class="subtitle">Disponible solo para encargados. Podés crear empleados/encargados y eliminar usuarios.</div>
        </div>
      </div>
      <div class="grid admin-grid">
        <div class="card">
          <h3>Alta de usuario</h3>
          <form id="admin-create-form">
            <label>Nombre completo</label>
            <input name="name" placeholder="Nombre y apellido" required>
            <label>Email</label>
            <input name="email" type="email" placeholder="usuario@amvstore.com.uy" required>
            <label>Cédula</label>
            <input name="ci" placeholder="Solo números" required>
            <label>Sector</label>
            <input name="sector" placeholder="Ej: Ventas">
            <label>Rol</label>
            <select name="role">
              <option value="empleado">Empleado (votable, 1 punto)</option>
              <option value="encargado">Encargado (no votable, 2 puntos)</option>
            </select>
            <label>Contraseña</label>
            <input name="password" type="password" placeholder="Mínimo 8 caracteres" required>
            <button type="submit">Crear usuario</button>
          </form>
        </div>

        <div class="card">
          <h3>Usuarios existentes</h3>
          <div id="admin-users" class="ranking-list"></div>
        </div>

        <div class="card">
          <h3>Importar usuarios (CSV)</h3>
          <form id="admin-import-form" enctype="multipart/form-data">
            <label>Archivo CSV</label>
            <input type="file" name="file" accept=".csv" required>
            <div class="subtitle">Encabezados esperados: ci,name,email,sector,role,password</div>
            <button type="submit">Importar CSV</button>
          </form>
        </div>
      </div>
    </section>
  </main>

  <script src="/assets/app.js" defer></script>
</body>
</html>
