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
  <meta name="theme-color" content="#071d34">
  <title>Iniciar sesión · <?php echo $e($empresa['nombre']); ?></title>
  <link rel="icon" href="<?php echo $e($empresa['logo']); ?>">
  <link rel="preload" href="fuentes/big-shoulders-display.woff2" as="font" type="font/woff2" crossorigin>
  <link rel="stylesheet" href="plantilla/plugins/fontawesome-free/css/all.min.css">
  <style>
    /* Acceso en el mundo «Carrocería y cinta reflectiva» (ver DESIGN.md). */
    @font-face { font-family: 'Big Shoulders Display'; src: url('fuentes/big-shoulders-display.woff2') format('woff2'); font-weight: 100 900; font-display: swap; }
    @font-face { font-family: 'Barlow Semi Condensed'; src: url('fuentes/barlow-semi-condensed-400.woff2') format('woff2'); font-weight: 400; font-display: swap; }
    @font-face { font-family: 'Barlow Semi Condensed'; src: url('fuentes/barlow-semi-condensed-600.woff2') format('woff2'); font-weight: 600; font-display: swap; }
    @font-face { font-family: 'Barlow Semi Condensed'; src: url('fuentes/barlow-semi-condensed-700.woff2') format('woff2'); font-weight: 700; font-display: swap; }
    :root {
      --cabina: #0b2a4a;
      --cabina-profunda: #071d34;
      --cabina-clara: #123a63;
      --cabina-linea: #2b5582;
      --hueso: #f4f1ea;
      --hueso-oscuro: #e4ded1;
      --reflectivo: #d7261e;
      --reflectivo-tinta: #a8170f;
      --cromo: #c9ced4;
      --tinta: #0d1117;
      --texto-suave: #4a525c;
      --borde: #b9b1a1;
    }
    * { box-sizing: border-box; }
    html, body { height: 100%; }
    body {
      margin: 0;
      font-family: "Barlow Semi Condensed", system-ui, sans-serif;
      font-size: 17px;
      color: var(--tinta);
      background: var(--cabina-profunda);
      caret-color: var(--cabina);
    }
    ::selection { background: var(--cabina); color: var(--hueso); }

    .acceso {
      min-height: 100%;
      display: grid;
      grid-template-columns: 1fr;
      place-items: center;
      padding: 24px 16px;
      border-top: 8px solid transparent;
      border-image: repeating-linear-gradient(-45deg, var(--reflectivo) 0 14px, #fff 14px 28px) 8;
    }
    .acceso-tarjeta {
      width: 100%;
      max-width: 880px;
      display: grid;
      grid-template-columns: 1fr;
      background: var(--hueso);
      border-radius: 6px;
      overflow: hidden;
      border: 2px solid var(--cabina-linea);
    }

    .acceso-marca {
      position: relative;
      background: var(--cabina);
      color: var(--hueso);
      padding: 28px 24px 34px;
      display: flex;
      align-items: center;
      gap: 16px;
    }
    .acceso-marca::after {
      content: '';
      position: absolute;
      left: 0; right: 0; bottom: 0;
      height: 8px;
      background: repeating-linear-gradient(-45deg, var(--reflectivo) 0 10px, #fff 10px 20px);
    }
    .acceso-marca img {
      width: 104px;
      height: auto;
      border-radius: 6px;
      flex: none;
    }
    .acceso-marca h1 {
      margin: 0;
      font-family: "Big Shoulders Display", sans-serif;
      font-weight: 900;
      font-size: 2rem;
      line-height: .95;
      letter-spacing: .01em;
      text-transform: uppercase;
      text-wrap: balance;
      text-shadow: 0.06em 0.06em 0 var(--reflectivo), 0.09em 0.1em 0 var(--cabina-profunda);
    }
    .acceso-marca p {
      margin: 8px 0 0;
      color: var(--cromo);
      font-size: 1rem;
    }

    .acceso-form { padding: 28px 24px 32px; }
    .acceso-form h2 {
      margin: 0 0 24px;
      font-family: "Big Shoulders Display", sans-serif;
      font-weight: 800;
      font-size: 2.25rem;
      line-height: 1;
      text-transform: uppercase;
      color: var(--cabina);
    }
    .campo { margin-bottom: 18px; }
    .campo label {
      display: block;
      font-weight: 700;
      margin-bottom: 6px;
    }
    .control {
      position: relative;
      display: flex;
    }
    .control input {
      width: 100%;
      min-height: 50px;
      padding: 10px 14px;
      font: inherit;
      color: var(--tinta);
      background: #fff;
      border: 2px solid var(--borde);
      border-radius: 4px;
      transition: border-color .15s ease, box-shadow .15s ease;
    }
    .control input:focus {
      outline: none;
      border-color: var(--cabina-clara);
      box-shadow: 0 0 0 3px rgba(18, 58, 99, .28);
    }
    .control input.con-boton { padding-right: 52px; }
    .ver-clave {
      position: absolute;
      right: 2px;
      top: 2px;
      bottom: 2px;
      width: 46px;
      border: 0;
      border-radius: 3px;
      background: transparent;
      color: var(--texto-suave);
      cursor: pointer;
    }
    .ver-clave:hover { color: var(--cabina); background: var(--hueso-oscuro); }
    .ver-clave:focus-visible, .entrar:focus-visible, .recordar input:focus-visible, .volver:focus-visible {
      outline: 3px solid var(--cabina-clara);
      outline-offset: 2px;
    }

    .recordar {
      display: flex;
      align-items: center;
      gap: 10px;
      min-height: 44px;
      margin: 4px 0 20px;
      color: var(--texto-suave);
      font-weight: 600;
      cursor: pointer;
    }
    .recordar input { width: 20px; height: 20px; accent-color: var(--cabina); margin: 0; }

    .entrar {
      width: 100%;
      min-height: 52px;
      border: 0;
      border-radius: 5px;
      background: var(--cabina);
      color: var(--hueso);
      font: inherit;
      font-weight: 700;
      font-size: 1.125rem;
      cursor: pointer;
      transition: background-color .15s ease;
    }
    .entrar:hover { background: var(--cabina-profunda); }
    .entrar:active { background: #04121f; }
    .entrar i { margin-right: 8px; }

    .volver { display: inline-flex; align-items: center; gap: 8px; min-height: 44px; margin-top: 4px; color: var(--cabina-clara); font-weight: 600; }
    .volver:hover { color: var(--cabina); }
    .acceso-pie {
      margin: 24px 0 0;
      font-size: .9375rem;
      color: var(--texto-suave);
    }

    @media (min-width: 768px) {
      .acceso-tarjeta { grid-template-columns: 5fr 6fr; min-height: 460px; }
      .acceso-marca {
        flex-direction: column;
        justify-content: center;
        text-align: center;
        padding: 40px 32px 48px;
      }
      .acceso-marca::after { left: auto; top: 0; bottom: 0; right: 0; width: 8px; height: auto; }
      .acceso-marca img { width: 184px; }
      .acceso-marca h1 { font-size: 2.75rem; }
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
        <?php if (modulo_activo('pagina_publica')): ?><a class="volver" href="index.php"><i class="fas fa-arrow-left" aria-hidden="true"></i> Volver a la página del grifo</a><?php endif; ?>
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
