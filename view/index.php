<?php
require_once __DIR__ . '/../controller/_bootstrap.php';
require_once __DIR__ . '/../model/model_menu.php';
$modelo_menu = new Modelo_Menu();
$menu_sesion = $modelo_menu->Para_Sesion();
$vista_inicio = $modelo_menu->Vista_Inicio($menu_sesion);
// Mientras deba cambiar su contraseña, el panel abre directamente esa pantalla.
if (!empty($_SESSION['S_CAMBIAR_CLAVE'])) {
    $vista_inicio = 'usuario/view_cambiar_clave.php';
}
$capacidades = capacidades_sesion();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="theme-color" content="#ffffff">
  <title>Sistema de Gestión - Grifo</title>
  <link rel="preload" href="../fuentes/inter.woff2" as="font" type="font/woff2" crossorigin>
  <link rel="stylesheet" href="../plantilla/plugins/fontawesome-free/css/all.min.css">
  <link rel="shortcut icon" href="../img/grau.png" type="image/jpg">
  <link rel="stylesheet" href="../plantilla/dist/css/adminlte.min.css">
  <link href="../utilitario/DataTables/datatables.min.css" type="text/css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="../css/grifo.css?rev=<?php echo filemtime(__DIR__ . '/../css/grifo.css'); ?>">
  <link rel="stylesheet" href="../css/panel.css?rev=<?php echo filemtime(__DIR__ . '/../css/panel.css'); ?>">
</head>

<body class="hold-transition sidebar-mini">
  <div class="wrapper">
    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand g-cabecera">
      <ul class="navbar-nav">
        <li class="nav-item">
          <a class="nav-link" data-widget="pushmenu" href="#" role="button" aria-label="Mostrar u ocultar el menú"><i class="fas fa-bars" aria-hidden="true"></i></a>
        </li>
      </ul>
      <ul class="navbar-nav ml-auto">
        <li class="nav-item dropdown">
          <a class="nav-link g-usuario" data-toggle="dropdown" href="#" aria-haspopup="true" aria-expanded="false">
            <img src="../<?php echo htmlspecialchars((string) $_SESSION['S_FOTO'], ENT_QUOTES, 'UTF-8'); ?>" class="img-circle" width="32" height="32" alt="">
            <span class="g-usuario-nombre"><?php echo htmlspecialchars((string) $_SESSION['S_COMPLETOS'], ENT_QUOTES, 'UTF-8'); ?></span>
            <i class="fas fa-caret-down" aria-hidden="true"></i>
          </a>
          <div class="dropdown-menu dropdown-menu-right">
            <a href="#" onclick="cargar_contenido('contenido_principal','usuario/view_mi_perfil.php')" class="dropdown-item"><i class="fas fa-user-circle mr-2" aria-hidden="true"></i>Mi perfil</a>
            <div class="dropdown-divider"></div>
            <a href="../controller/usuario/controlador_cerrar_sesion.php" class="dropdown-item"><i class="fas fa-power-off mr-2" aria-hidden="true"></i>Cerrar sesión</a>
          </div>
        </li>
      </ul>
    </nav>

    <!-- Main Sidebar -->
    <aside class="main-sidebar sidebar-dark-primary">
      <a href="#"  onclick="cargar_contenido('contenido_principal',<?php echo htmlspecialchars(json_encode($vista_inicio), ENT_QUOTES, 'UTF-8'); ?>)" class="brand-link">
        <img src="../<?php echo htmlspecialchars($_SESSION['S_FOTO_EMPRESA'] ?: 'img/grau.png', ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars((string) $_SESSION['S_RAZON'], ENT_QUOTES, 'UTF-8'); ?>" width="100%" height="auto">
      </a>

      <div class="sidebar">
        <div class="user-panel g-ficha-usuario">
          <img src="../<?php echo htmlspecialchars((string) $_SESSION['S_FOTO'], ENT_QUOTES, 'UTF-8'); ?>" class="img-circle" width="40" height="40" alt="">
          <div>
            <span class="g-ficha-nombre"><?php echo htmlspecialchars((string) $_SESSION['S_NOMBRE'], ENT_QUOTES, 'UTF-8'); ?></span>
            <span class="g-ficha-rol"><?php echo htmlspecialchars((string) $_SESSION['S_ROL'], ENT_QUOTES, 'UTF-8'); ?></span>
          </div>
        </div>

        <nav class="mt-1">
          <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
            <?php foreach ($menu_sesion as $seccion_menu) { ?>
              <li class="nav-header g-seccion"><?php echo htmlspecialchars($seccion_menu['titulo'], ENT_QUOTES, 'UTF-8'); ?></li>
              <?php foreach ($seccion_menu['items'] as $item_menu) { ?>
                <?php if ($item_menu['hijos']) { ?>
                  <li class="nav-item has-treeview">
                    <a href="#" class="nav-link">
                      <i class="nav-icon <?php echo htmlspecialchars((string) $item_menu['icono'], ENT_QUOTES, 'UTF-8'); ?>"></i>
                      <p><?php echo htmlspecialchars($item_menu['titulo'], ENT_QUOTES, 'UTF-8'); ?> <i class="right fas fa-angle-left"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                      <?php foreach ($item_menu['hijos'] as $hijo_menu) { ?>
                        <li class="nav-item">
                          <a href="#" onclick="cargar_contenido('contenido_principal',<?php echo htmlspecialchars(json_encode($hijo_menu['vista'], JSON_UNESCAPED_SLASHES), ENT_QUOTES, 'UTF-8'); ?>)" class="nav-link">
                            <i class="nav-icon <?php echo htmlspecialchars((string) $hijo_menu['icono'], ENT_QUOTES, 'UTF-8'); ?>"></i>
                            <p><?php echo htmlspecialchars($hijo_menu['titulo'], ENT_QUOTES, 'UTF-8'); ?></p>
                          </a>
                        </li>
                      <?php } ?>
                    </ul>
                  </li>
                <?php } else { ?>
                  <li class="nav-item">
                    <a href="#" onclick="cargar_contenido('contenido_principal',<?php echo htmlspecialchars(json_encode($item_menu['vista'], JSON_UNESCAPED_SLASHES), ENT_QUOTES, 'UTF-8'); ?>)" class="nav-link">
                      <i class="nav-icon <?php echo htmlspecialchars((string) $item_menu['icono'], ENT_QUOTES, 'UTF-8'); ?>"></i>
                      <p><?php echo htmlspecialchars($item_menu['titulo'], ENT_QUOTES, 'UTF-8'); ?></p>
                    </a>
                  </li>
                <?php } ?>
              <?php } ?>
            <?php } ?>
          </ul>
        </nav>
      </div>
    </aside>

    <input type="text" id="txtprincipalid" value="<?php echo $_SESSION['S_ID']; ?>" hidden>
    <input type="text" id="txtprincipalusu" value="<?php echo $_SESSION['S_USU']; ?>" hidden>
    <input type="text" id="txtprincipalrol" value="<?php echo $_SESSION['S_ROL']; ?>" hidden>

    <!-- Content Wrapper -->
    <div class="content-wrapper" id="contenido_principal">
      <div class="content-header">
        <div class="container-fluid">
          <div class="row mb-2">
            <div class="col-sm-12 text-center">
              <h1 class="m-0"><i class="fas fa-spinner fa-spin"></i> Cargando...</h1>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <?php
    require_once __DIR__ . '/../model/model_configuracion.php';
    $chat_visible = false;
    if (modulo_activo('chatbot_ia')) {
        $config_chat = new Modelo_Configuracion();
        $chat_visible = in_array($_SESSION['S_ROL'], explode(',', (string) $config_chat->Obtener('ia_roles', 'ADMINISTRADOR')), true);
    }
    if ($chat_visible) { ?>
      <button id="chat_abrir" type="button" class="btn btn-primary rounded-circle elevation-3" title="Asistente de datos" aria-label="Abrir asistente de datos"
              style="position:fixed;right:24px;bottom:24px;width:56px;height:56px;z-index:1050">
        <i class="fas fa-comments"></i>
      </button>
      <div id="chat_panel" class="card d-none elevation-4" style="position:fixed;right:24px;bottom:90px;width:380px;max-width:calc(100vw - 48px);height:520px;max-height:calc(100vh - 120px);z-index:1050;margin:0">
        <div class="card-header d-flex align-items-center" style="background:#023D77;color:#fff">
          <b class="mr-auto"><i class="fas fa-robot"></i> Asistente de datos</b>
          <button id="chat_limpiar" type="button" class="btn btn-sm text-white" title="Nueva conversación" aria-label="Nueva conversación"><i class="fas fa-eraser"></i></button>
          <button id="chat_cerrar" type="button" class="btn btn-sm text-white" title="Cerrar" aria-label="Cerrar"><i class="fas fa-times"></i></button>
        </div>
        <div id="chat_mensajes" class="card-body" style="overflow-y:auto">
          <div class="chat_burbuja chat_bot">Pregúntame por ventas, turnos o créditos. Por ejemplo: «¿Cuántos galones de Premium se vendieron esta semana?». Las respuestas son orientativas: confirma las cifras importantes en los reportes.</div>
        </div>
        <form id="chat_form" class="card-footer d-flex p-2">
          <input id="chat_entrada" class="form-control mr-2" maxlength="1000" placeholder="Escribe tu pregunta" aria-label="Pregunta" autocomplete="off">
          <button class="btn btn-primary" type="submit" aria-label="Enviar"><i class="fas fa-paper-plane"></i></button>
        </form>
      </div>
      <style>
        .chat_burbuja { padding: 8px 12px; border-radius: 10px; margin-bottom: 8px; white-space: pre-wrap; overflow-wrap: anywhere; }
        .chat_usuario { background: #023D77; color: #fff; margin-left: 36px; }
        .chat_bot { background: #f1f3f5; margin-right: 36px; }
      </style>
    <?php } ?>

    <!-- Footer -->
    <footer class="main-footer">
      <div class="float-right d-none d-sm-inline">
        <em>Versión 1.0.0</em>
      </div>
      <strong>Copyright &copy; 2025 - <a href="#" target="_blank"><em>Sistema de Gestión de Grifo</em></a></strong>
    </footer>
  </div>

  <!-- Scripts -->
  <script src="../plantilla/plugins/jquery/jquery.min.js"></script>
  <script>
    // Token CSRF en toda petición AJAX, y respuesta común a sesión vencida o falta de permisos.
    window.CAPACIDADES = <?php echo json_encode($capacidades === null ? '*' : array_values($capacidades)); ?>;
    $.ajaxSetup({ headers: { 'X-CSRF-Token': <?php echo json_encode(token_csrf()); ?> } });
    $(document).ajaxError(function(evento, xhr) {
      if (xhr.status === 401) {
        window.location.href = '../login.php';
      } else if (xhr.status === 403 && window.Swal) {
        var r = xhr.responseJSON || {};
        Swal.fire('Acceso denegado', r.message || 'No tiene permisos para realizar esta acción.', 'warning');
      }
    });
  </script>
  <script src="../plantilla/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="../plantilla/dist/js/adminlte.min.js"></script>
  <script src="../utilitario/DataTables/datatables.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="../js/console_utilidades.js?rev=<?php echo time(); ?>"></script>
  <?php if ($chat_visible) { ?><script src="../js/console_chatbot.js?rev=<?php echo time(); ?>"></script><?php } ?>

  <script>
    function cargar_contenido(id, vista) {
      $("#" + id).load(vista);
      if (id !== 'contenido_principal') { return; }
      // El menú marca la pantalla abierta: las pantallas se cargan sin recargar la página.
      var enlaces = $('.nav-sidebar .nav-link').removeClass('g-actual g-actual-padre').removeAttr('aria-current');
      var actual = enlaces.filter(function () { return (this.getAttribute('onclick') || '').indexOf('"' + vista + '"') !== -1; })
        .addClass('g-actual').attr('aria-current', 'page');
      // Si la pantalla está dentro de un grupo, el grupo se abre para que se vea cuál está seleccionada.
      var grupo = actual.closest('.has-treeview');
      if (grupo.length) {
        grupo.children('.nav-link').addClass('g-actual-padre');
        if (!grupo.hasClass('menu-open')) { grupo.addClass('menu-open').children('.nav-treeview').show(); }
      }
    }

    var idioma_espanol = {
      "sProcessing": "Procesando...",
      "sLengthMenu": "Mostrar _MENU_ registros",
      "sZeroRecords": "No se encontraron resultados",
      "sEmptyTable": "Ningún dato disponible en esta tabla",
      "sInfo": "Registros del (_START_ al _END_) total de _TOTAL_ registros",
      "sInfoEmpty": "Registros del (0 al 0) total de 0 registros",
      "sInfoFiltered": "(filtrado de un total de _MAX_ registros)",
      "sSearch": "Buscar:",
      "oPaginate": {
        "sFirst": "Primero",
        "sLast": "Último",
        "sNext": "Siguiente",
        "sPrevious": "Anterior"
      }
    }

    $(document).ready(function() {
      // Pantalla inicial según el rol
      var vista_inicio = <?php echo json_encode($vista_inicio); ?>;
      if (vista_inicio) { cargar_contenido('contenido_principal', vista_inicio); }
    });

  </script>
</body>
</html>
