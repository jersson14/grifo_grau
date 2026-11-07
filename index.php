<?php
require_once __DIR__ . '/controller/_bootstrap.php';

if (isset($_SESSION['S_ID'])) {
    header('Location: view/index.php');
    exit;
}

// Nombre y logo de la empresa, editables en Configuración → Empresa.
$empresa = ['nombre' => 'Sistema de Gestión de Grifo', 'logo' => 'img/grau.png'];
try {
    $consulta = conexionBD::conexionPDO()->query('CALL SP_LISTAR_EMPRESA()');
    $fila = $consulta ? $consulta->fetch(PDO::FETCH_ASSOC) : false;
    if ($fila) {
        $empresa['nombre'] = $fila['nombre'] ?: $empresa['nombre'];
        if (!empty($fila['logo']) && is_file(__DIR__ . '/' . $fila['logo'])) {
            $empresa['logo'] = $fila['logo'];
        }
    }
} catch (Throwable $e) {
    error_log('Login: no se pudo leer la empresa: ' . $e->getMessage());
}
$e = fn($texto) => htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="theme-color" content="#023D77">
  <title>Iniciar sesión · <?php echo $e($empresa['nombre']); ?></title>
  <link rel="icon" href="<?php echo $e($empresa['logo']); ?>">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:400,600,700&display=swap">
  <link rel="stylesheet" href="plantilla/plugins/fontawesome-free/css/all.min.css">
  <style>
    :root {
      --azul: #023D77;
      --azul-claro: #0266C8;
      --azul-tenue: #e8f0fa;
      --texto: #1c2733;
      --texto-suave: #4f5d6b;
      --borde: #c9d3de;
      --fondo: #eef2f6;
      --superficie: #ffffff;
      --foco: #2f7de1;
    }
    * { box-sizing: border-box; }
    html, body { height: 100%; }
    body {
      margin: 0;
      font-family: "Source Sans Pro", system-ui, -apple-system, "Segoe UI", sans-serif;
      font-size: 16px;
      color: var(--texto);
      background: var(--fondo);
    }
    ::selection { background: #cfe0f5; color: var(--azul); }

    .acceso {
      min-height: 100%;
      display: grid;
      grid-template-columns: 1fr;
      place-items: center;
      padding: 24px 16px;
    }
    .acceso-tarjeta {
      width: 100%;
      max-width: 880px;
      display: grid;
      grid-template-columns: 1fr;
      background: var(--superficie);
      border-radius: 14px;
      overflow: hidden;
      box-shadow: 0 18px 40px -18px rgba(2, 37, 74, .35), 0 2px 6px rgba(2, 37, 74, .08);
    }

    .acceso-marca {
      background: var(--azul);
      color: #fff;
      padding: 28px 24px;
      display: flex;
      align-items: center;
      gap: 16px;
    }
    .acceso-marca img {
      width: 112px;
      height: auto;
      border-radius: 8px;
      flex: none;
    }
    .acceso-marca h1 {
      margin: 0;
      font-size: 1.375rem;
      line-height: 1.2;
      font-weight: 700;
      text-wrap: balance;
    }
    .acceso-marca p {
      margin: 6px 0 0;
      color: #c6d8ee;
      font-size: .95rem;
    }

    .acceso-form { padding: 28px 24px 32px; }
    .acceso-form h2 {
      margin: 0 0 24px;
      font-size: 1.5rem;
      font-weight: 700;
    }
    .campo { margin-bottom: 18px; }
    .campo label {
      display: block;
      font-weight: 600;
      margin-bottom: 6px;
    }
    .control {
      position: relative;
      display: flex;
    }
    .control input {
      width: 100%;
      min-height: 48px;
      padding: 10px 14px;
      font: inherit;
      color: var(--texto);
      background: #fff;
      border: 1px solid var(--borde);
      border-radius: 8px;
      transition: border-color .15s ease, box-shadow .15s ease;
    }
    .control input:focus {
      outline: none;
      border-color: var(--azul-claro);
      box-shadow: 0 0 0 3px rgba(2, 102, 200, .22);
    }
    .control input.con-boton { padding-right: 52px; }
    .ver-clave {
      position: absolute;
      right: 2px;
      top: 2px;
      bottom: 2px;
      width: 46px;
      border: 0;
      border-radius: 6px;
      background: transparent;
      color: var(--texto-suave);
      cursor: pointer;
    }
    .ver-clave:hover { color: var(--azul); background: var(--azul-tenue); }
    .ver-clave:focus-visible, .entrar:focus-visible, .recordar input:focus-visible {
      outline: 3px solid var(--foco);
      outline-offset: 2px;
    }

    .recordar {
      display: flex;
      align-items: center;
      gap: 10px;
      min-height: 44px;
      margin: 4px 0 20px;
      color: var(--texto-suave);
      cursor: pointer;
    }
    .recordar input { width: 20px; height: 20px; accent-color: var(--azul); margin: 0; }

    .entrar {
      width: 100%;
      min-height: 50px;
      border: 0;
      border-radius: 8px;
      background: var(--azul);
      color: #fff;
      font: inherit;
      font-weight: 700;
      font-size: 1.05rem;
      cursor: pointer;
      transition: background-color .15s ease;
    }
    .entrar:hover { background: #0a4f92; }
    .entrar:active { background: #01305e; }
    .entrar i { margin-right: 8px; }

    .acceso-pie {
      margin: 24px 0 0;
      font-size: .875rem;
      color: var(--texto-suave);
    }

    @media (min-width: 768px) {
      .acceso-tarjeta { grid-template-columns: 5fr 6fr; min-height: 460px; }
      .acceso-marca {
        flex-direction: column;
        justify-content: center;
        text-align: center;
        padding: 40px 32px;
      }
      .acceso-marca img { width: 200px; }
      .acceso-marca h1 { font-size: 1.75rem; }
      .acceso-form { padding: 48px 48px; align-self: center; }
    }

    @media (prefers-reduced-motion: reduce) {
      * { transition: none !important; }
    }
  </style>
</head>
<body>
  <main class="acceso">
    <div class="acceso-tarjeta">
      <section class="acceso-marca" aria-label="Empresa">
        <img src="<?php echo $e($empresa['logo']); ?>" alt="Logo de <?php echo $e($empresa['nombre']); ?>">
        <div>
          <h1><?php echo $e($empresa['nombre']); ?></h1>
          <p>Sistema de gestión del grifo</p>
        </div>
      </section>

      <form class="acceso-form" id="form_acceso" autocomplete="on">
        <h2>Iniciar sesión</h2>

        <div class="campo">
          <label for="txt_usuario">Usuario</label>
          <div class="control">
            <input type="text" id="txt_usuario" name="usuario" autocomplete="username" autocapitalize="none" spellcheck="false" required>
          </div>
        </div>

        <div class="campo">
          <label for="txt_contra">Contraseña</label>
          <div class="control">
            <input type="password" id="txt_contra" name="contrasena" class="con-boton" autocomplete="current-password" required>
            <button type="button" class="ver-clave" id="togglePassword" aria-label="Mostrar contraseña" aria-pressed="false">
              <i class="fas fa-eye" aria-hidden="true"></i>
            </button>
          </div>
        </div>

        <label class="recordar" for="remember">
          <input type="checkbox" id="remember" value="1">
          Recordar mi usuario en este equipo
        </label>

        <button type="submit" class="entrar" id="entrar">
          <i class="fas fa-sign-in-alt" aria-hidden="true"></i>Ingresar
        </button>

        <p class="acceso-pie">&copy; <?php echo date('Y') . ' ' . $e($empresa['nombre']); ?></p>
      </form>
    </div>
  </main>

  <script src="plantilla/plugins/jquery/jquery.min.js"></script>
  <script src="js/console_usuario.js?rev=<?php echo time(); ?>"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script>
    const rmcheck = document.getElementById('remember'),
          usuarioInput = document.getElementById('txt_usuario'),
          passInput = document.getElementById('txt_contra'),
          togglePassword = document.getElementById('togglePassword');

    togglePassword.addEventListener('click', function () {
      const mostrar = passInput.type === 'password';
      passInput.type = mostrar ? 'text' : 'password';
      this.setAttribute('aria-pressed', String(mostrar));
      this.setAttribute('aria-label', mostrar ? 'Ocultar contraseña' : 'Mostrar contraseña');
      this.querySelector('i').classList.toggle('fa-eye-slash', mostrar);
    });

    // Solo se recuerda el usuario. Una contraseña guardada antes en el navegador se borra.
    try {
      localStorage.removeItem('pass');
      if (localStorage.checkbox) {
        rmcheck.checked = true;
        usuarioInput.value = localStorage.usuario || '';
      }
    } catch (e) {}

    (usuarioInput.value ? passInput : usuarioInput).focus();

    document.getElementById('form_acceso').addEventListener('submit', function (evento) {
      evento.preventDefault();
      Iniciar_Sesion();
    });
  </script>
</body>
</html>
