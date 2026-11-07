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
  <meta name="theme-color" content="#fafafa">
  <title>Iniciar sesión · <?php echo $e($empresa['nombre']); ?></title>
  <link rel="icon" href="<?php echo $e($empresa['logo']); ?>">
  <link rel="preload" href="fuentes/inter.woff2" as="font" type="font/woff2" crossorigin>
  <link rel="stylesheet" href="plantilla/plugins/fontawesome-free/css/all.min.css">
  <style>
    /* Acceso con el estilo convencional de shadcn/ui (ver DESIGN.md). */
    @font-face { font-family: 'Inter'; src: url('fuentes/inter.woff2') format('woff2'); font-weight: 100 900; font-display: swap; }
    :root {
      --fondo: #fafafa;
      --tarjeta: #ffffff;
      --borde: #e4e4e7;
      --campo: #d4d4d8;
      --texto: #09090b;
      --suave: #52525b;
      --primario: #18181b;
      --primario-hover: #27272a;
      --foco: #2563eb;
      --tenue: #f4f4f5;
    }
    * { box-sizing: border-box; }
    html, body { height: 100%; }
    body {
      margin: 0;
      font-family: Inter, system-ui, -apple-system, "Segoe UI", sans-serif;
      font-size: 15px;
      color: var(--texto);
      background: var(--fondo);
      -webkit-font-smoothing: antialiased;
    }
    ::selection { background: #dbeafe; color: var(--texto); }

    .acceso {
      min-height: 100%;
      display: grid;
      place-items: center;
      padding: 32px 16px;
    }
    .acceso-tarjeta {
      width: 100%;
      max-width: 400px;
      background: var(--tarjeta);
      border: 1px solid var(--borde);
      border-radius: 12px;
      box-shadow: 0 1px 2px rgba(0, 0, 0, .05);
    }
    .acceso-marca {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 24px 24px 0;
    }
    .acceso-marca img {
      width: 44px;
      height: 44px;
      object-fit: contain;
      border-radius: 8px;
      border: 1px solid var(--borde);
      flex: none;
    }
    .acceso-marca h1 {
      margin: 0;
      font-size: 15px;
      font-weight: 600;
      line-height: 1.3;
    }
    .acceso-marca p {
      margin: 2px 0 0;
      color: var(--suave);
      font-size: 13px;
    }
    .acceso-form { padding: 20px 24px 24px; }
    .acceso-form h2 {
      margin: 0 0 4px;
      font-size: 24px;
      font-weight: 600;
      letter-spacing: -.02em;
    }
    .acceso-desc + * { margin-top: 24px; }
    .campo { margin-bottom: 16px; }
    .campo label {
      display: block;
      font-size: 14px;
      font-weight: 500;
      margin-bottom: 6px;
    }
    .control { position: relative; display: flex; }
    .control input {
      width: 100%;
      min-height: 44px;
      padding: 8px 12px;
      font: inherit;
      font-size: 16px;
      color: var(--texto);
      background: #fff;
      border: 1px solid var(--campo);
      border-radius: 6px;
      box-shadow: 0 1px 2px rgba(0, 0, 0, .04);
      transition: border-color .15s ease, box-shadow .15s ease;
    }
    .control input:focus {
      outline: none;
      border-color: var(--foco);
      box-shadow: 0 0 0 3px rgba(37, 99, 235, .2);
    }
    .control input.con-boton { padding-right: 50px; }
    .ver-clave {
      position: absolute;
      right: 1px; top: 1px; bottom: 1px;
      width: 44px;
      border: 0;
      border-radius: 6px;
      background: transparent;
      color: var(--suave);
      cursor: pointer;
    }
    .ver-clave:hover { color: var(--texto); background: var(--tenue); }
    .ver-clave:focus-visible, .entrar:focus-visible, .recordar input:focus-visible, .volver:focus-visible {
      outline: 2px solid var(--foco);
      outline-offset: 2px;
    }
    .recordar {
      display: flex;
      align-items: center;
      gap: 10px;
      min-height: 44px;
      margin: 0 0 12px;
      color: var(--suave);
      font-size: 14px;
      cursor: pointer;
    }
    .recordar input { width: 18px; height: 18px; accent-color: var(--primario); margin: 0; }
    .entrar {
      width: 100%;
      min-height: 44px;
      border: 0;
      border-radius: 6px;
      background: var(--primario);
      color: #fff;
      font: inherit;
      font-size: 14px;
      font-weight: 500;
      cursor: pointer;
      transition: background-color .15s ease;
    }
    .entrar:hover { background: var(--primario-hover); }
    .entrar i { margin-right: 8px; }
    .volver { display: inline-flex; align-items: center; gap: 8px; min-height: 44px; margin-top: 8px; color: var(--suave); font-size: 14px; text-decoration: none; }
    .volver:hover { color: var(--texto); }
    .acceso-pie {
      margin: 20px 0 0;
      font-size: 13px;
      color: var(--suave);
    }
    /* Pantalla dividida: foto de la estación a un lado y el formulario al otro. */
    .acceso { padding: 0; grid-template-columns: 1fr; place-items: stretch; }
    .acceso-imagen {
      position: relative;
      min-height: 200px;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      padding: 20px;
      color: #fff;
      background: #09090b url('img/acceso.jpg') center 55% / cover no-repeat;
    }
    .acceso-imagen::before {
      content: '';
      position: absolute;
      inset: 0;
      background: linear-gradient(180deg, rgba(9, 9, 11, .55) 0%, rgba(9, 9, 11, .15) 45%, rgba(9, 9, 11, .75) 100%);
    }
    .acceso-imagen > * { position: relative; }
    .acceso-imagen-marca { display: flex; align-items: center; gap: 10px; font-weight: 600; font-size: 15px; }
    .acceso-imagen-marca img { width: 32px; height: 32px; object-fit: contain; border-radius: 6px; background: #fff; }
    .acceso-imagen-frase { margin: 0; max-width: 28ch; font-size: 18px; font-weight: 500; line-height: 1.4; letter-spacing: -.01em; }
    .acceso-lado { display: grid; place-items: center; padding: 32px 16px 40px; }
    .acceso-lado .acceso-tarjeta { border: 0; box-shadow: none; background: transparent; max-width: 380px; }
    .acceso-lado .acceso-marca { display: none; }
    .acceso-lado .acceso-form { padding: 0; }
    .acceso-desc { margin: 0; font-size: 14px; color: var(--suave); }
    body { background: #fff; }
    @media (min-width: 900px) {
      .acceso { grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); min-height: 100vh; }
      .acceso-imagen { min-height: 100vh; padding: 40px; }
      .acceso-imagen-frase { font-size: 24px; }
      .acceso-lado { padding: 48px; }
    }
    @media (prefers-reduced-motion: reduce) {
      * { transition: none !important; }
    }
  </style>
</head>
<body>
  <main class="acceso">
    <aside class="acceso-imagen" aria-hidden="true">
      <div class="acceso-imagen-marca">
        <img src="<?php echo $e($empresa['logo']); ?>" alt="">
        <span><?php echo $e($empresa['nombre']); ?></span>
      </div>
      <p class="acceso-imagen-frase">Turnos, ventas, créditos y caja del grifo en un solo lugar.</p>
    </aside>
    <div class="acceso-lado">
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
        <p class="acceso-desc">Ingresa con tu usuario y contraseña.</p>

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
        <?php if (modulo_activo('pagina_publica')): ?><a class="volver" href="index.php"><i class="fas fa-arrow-left" aria-hidden="true"></i> Volver a la página del grifo</a><?php endif; ?>
      </form>
    </div>
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
