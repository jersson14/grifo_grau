<?php
require_once __DIR__ . '/../controller/_bootstrap.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Sistema de Gestión - Grifo</title>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <link rel="stylesheet" href="../plantilla/plugins/fontawesome-free/css/all.min.css">
  <link rel="shortcut icon" href="../img/grau.png" type="image/jpg">
  <link rel="stylesheet" href="../plantilla/dist/css/adminlte.min.css">
  <link href="../utilitario/DataTables/datatables.min.css" type="text/css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
  <!-- Colores Suaves - Mejora Visual -->
  <link rel="stylesheet" href="../css/colores-suaves.css">
</head>

<body class="hold-transition sidebar-mini">
  <div class="wrapper">
    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
      <ul class="navbar-nav">
        <li class="nav-item">
          <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
        </li>
      </ul>
      <ul class="navbar-nav ml-auto">
        <li class="nav-item dropdown">
          <a class="nav-link" data-toggle="dropdown" href="#">
            <img src="../<?php echo $_SESSION['S_FOTO']; ?>" class="img-circle elevation-1" width="15" height="18">
            <b>Usuario: <?php echo $_SESSION['S_COMPLETOS'] ?></b>
            <i class="fas fa-caret-down"></i>
          </a>
          <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
            <a href="#" onclick="cargar_contenido('contenido_principal','usuario/view_mi_perfil.php')" class="dropdown-item">
              <i class="fas fa-user-circle mr-2"></i><b>Mi Perfil</b>
            </a>
            <div class="dropdown-divider"></div>
            <a href="../controller/usuario/controlador_cerrar_sesion.php" class="dropdown-item">
              <i class="fas fa-power-off mr-2"></i><u><b>Cerrar Sesión</b></u>
            </a>
            <div class="dropdown-divider"></div>
          </div>
        </li>
      </ul>
    </nav>

    <!-- Main Sidebar -->
    <aside class="main-sidebar sidebar-dark-primary elevation-4">
      <a href="#"  onclick="cargar_contenido('contenido_principal','reportes/view_dashboard.php')" class="brand-link">
        <img src="../img/grau.png" alt="<?php echo $_SESSION['S_RAZON']; ?>" width="100%" height="auto">
      </a>

      <div class="sidebar">
        <div class="user-panel mt-1 pb-3 mb-3 d-flex">
          <div class="image">
            <img src="../<?php echo $_SESSION['S_FOTO']; ?>" class="img-circle elevation-2" style="max-width: 100%;height: auto;">
          </div>
          <div class="info">
            <a style="text-align:center;" href="#" class="d-block"><i class="fa fa-circle text-success fa-0x"></i> ¡Hola!<br> <b style="color:white"><?php echo $_SESSION['S_NOMBRE']; ?></b></a>
            <a style="text-align:center;margin:5px;color:white;font-size:15px" href="#" class="d-block">&nbsp;&nbsp;<b style="text-align:center"><i class="fa fa-user text-success fa-0x"></i><em> ROL: <?php echo $_SESSION['S_ROL']; ?></em></b></a>
          </div>
        </div>

        <nav class="mt-1">
          <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
            <li class="header text-center" style="color:#FFFFFF; background-color:#023D77; border-radius: 10px;">
              <b>MENÚ PRINCIPAL</b>
            </li>

            <!-- Dashboard -->
            <li class="nav-item">
              <a href="#" onclick="cargar_contenido('contenido_principal','reportes/view_dashboard.php')" class="nav-link">
                <i class="nav-icon fas fa-tachometer-alt"></i>
                <p style="color:white">Dashboard</p>
              </a>
            </li>

            <?php if ($_SESSION['S_ROL'] == "ADMINISTRADOR") { ?>
              
              <!-- GESTIÓN DE TURNOS (Solo Admin) -->
              <li class="nav-item has-treeview">
                <a href="#" class="nav-link">
                  <i class="nav-icon fas fa-clock"></i>
                  <p style="color:white">
                    Gestión de Turnos
                    <i class="right fas fa-angle-left"></i>
                  </p>
                </a>
                <ul class="nav nav-treeview">
                  <li class="nav-item">
                    <a href="#" onclick="cargar_contenido('contenido_principal','turnos/view_registrar_turno.php')" class="nav-link">
                      <i class="nav-icon far fa-circle"></i>
                      <p style="color:white">Registrar Turno</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="#" onclick="cargar_contenido('contenido_principal','turnos/view_historial.php')" class="nav-link">
                      <i class="nav-icon far fa-circle"></i>
                      <p style="color:white">Historial de Turnos</p>
                    </a>
                  </li>
                  <li class="nav-item" style="display:none;">
                    <a href="#" onclick="cargar_contenido('contenido_principal','turnos/view_abrir_turno.php')" class="nav-link">
                      <i class="nav-icon far fa-circle"></i>
                      <p style="color:white">Abrir Turno (Antiguo)</p>
                    </a>
                  </li>
                  <li class="nav-item" style="display:none;">
                    <a href="#" onclick="cargar_contenido('contenido_principal','turnos/view_cerrar_turno.php')" class="nav-link">
                      <i class="nav-icon far fa-circle"></i>
                      <p style="color:white">Gestionar Turno (Antiguo)</p>
                    </a>
                  </li>
                </ul>
              </li>

              <?php if (modulo_activo('ventas')) { ?>
              <li class="nav-item">
                <a href="#" onclick="cargar_contenido('contenido_principal','ventas/view_ventas.php')" class="nav-link">
                  <i class="nav-icon fas fa-cash-register"></i>
                  <p style="color:white">Ventas</p>
                </a>
              </li>
              <?php } ?>

              <?php if (modulo_activo('inventario')) { ?>
              <li class="nav-item has-treeview">
                <a href="#" class="nav-link">
                  <i class="nav-icon fas fa-oil-can"></i>
                  <p style="color:white">
                    Inventario
                    <i class="right fas fa-angle-left"></i>
                  </p>
                </a>
                <ul class="nav nav-treeview">
                  <li class="nav-item">
                    <a href="#" onclick="cargar_contenido('contenido_principal','inventario/view_tanques.php')" class="nav-link">
                      <i class="nav-icon far fa-circle"></i>
                      <p style="color:white">Tanques y kardex</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="#" onclick="cargar_contenido('contenido_principal','inventario/view_varillaje.php')" class="nav-link">
                      <i class="nav-icon far fa-circle"></i>
                      <p style="color:white">Varillaje</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="#" onclick="cargar_contenido('contenido_principal','inventario/view_compras.php')" class="nav-link">
                      <i class="nav-icon far fa-circle"></i>
                      <p style="color:white">Compras y proveedores</p>
                    </a>
                  </li>
                </ul>
              </li>
              <?php } ?>

              <?php if (modulo_activo('caja')) { ?>
              <li class="nav-item">
                <a href="#" onclick="cargar_contenido('contenido_principal','caja/view_caja.php')" class="nav-link">
                  <i class="nav-icon fas fa-coins"></i>
                  <p style="color:white">Caja y arqueo</p>
                </a>
              </li>
              <?php } ?>

              <!-- CRÉDITOS/VALES -->
              <li class="nav-item has-treeview">
                <a href="#" class="nav-link">
                  <i class="nav-icon fas fa-credit-card"></i>
                  <p style="color:white">
                    Créditos/Vales
                    <i class="right fas fa-angle-left"></i>
                  </p>
                </a>
                <ul class="nav nav-treeview">
                  <li class="nav-item">
                    <a href="#" onclick="cargar_contenido('contenido_principal','creditos/view_creditos_pendientes.php')" class="nav-link">
                      <i class="nav-icon far fa-circle"></i>
                      <p style="color:white">Gestionar Créditos</p>
                    </a>
                  </li>
                </ul>
              </li>

              <!-- REPORTES -->
              <li class="nav-item has-treeview">
                <a href="#" class="nav-link">
                  <i class="nav-icon fas fa-file-alt"></i>
                  <p style="color:white">
                    Reportes
                    <i class="right fas fa-angle-left"></i>
                  </p>
                </a>
                <ul class="nav nav-treeview">
                  <li class="nav-item">
                    <a href="#" onclick="cargar_contenido('contenido_principal','reportes/view_ventas_por_fecha.php')" class="nav-link">
                      <i class="nav-icon far fa-circle"></i>
                      <p style="color:white">Ventas por Fecha</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="#" onclick="cargar_contenido('contenido_principal','reportes/view_listado_reportes.php')" class="nav-link">
                      <i class="nav-icon far fa-circle"></i>
                      <p style="color:white">Todos los Reportes</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="#" onclick="cargar_contenido('contenido_principal','reportes/view_validar_reportes.php')" class="nav-link">
                      <i class="nav-icon far fa-circle"></i>
                      <p style="color:white">Validar Reportes</p>
                    </a>
                  </li>
                </ul>
              </li>

              <li class="header text-center" style="color:#FFFFFF; background-color:#023D77; border-radius: 10px;">
                <b>CONFIGURACIÓN</b>
              </li>

              <!-- CLIENTES -->
              <li class="nav-item">
                <a href="#" onclick="cargar_contenido('contenido_principal','clientes/view_clientes.php')" class="nav-link">
                  <i class="nav-icon fas fa-user-friends"></i>
                  <p style="color:white">Clientes</p>
                </a>
              </li>

              <!-- PRODUCTOS -->
              <li class="nav-item">
                <a href="#" onclick="cargar_contenido('contenido_principal','productos/view_productos.php')" class="nav-link">
                  <i class="nav-icon fas fa-gas-pump"></i>
                  <p style="color:white">Productos (Combustibles)</p>
                </a>
              </li>

              <!-- SURTIDORES -->
              <li class="nav-item">
                <a href="#" onclick="cargar_contenido('contenido_principal','surtidores/view_surtidores.php')" class="nav-link">
                  <i class="nav-icon fas fa-tint"></i>
                  <p style="color:white">Surtidores</p>
                </a>
              </li>

              <!-- USUARIOS -->
              <li class="nav-item">
                <a onclick="cargar_contenido('contenido_principal','usuario/view_usuario.php')" class="nav-link">
                  <i class="nav-icon fas fa-users"></i>
                  <p style="color:white">Usuarios</p>
                </a>
              </li>

              <!-- CONFIGURACIÓN GENERAL -->
              <li class="nav-item">
                <a href="#" onclick="cargar_contenido('contenido_principal','configuracion/view_configuracion.php')" class="nav-link">
                  <i class="nav-icon fas fa-cogs"></i>
                  <p style="color:white">Configuración</p>
                </a>
              </li>

            <?php } ?>

            <?php if ($_SESSION['S_ROL'] == "GRIFERO") { ?>
              
              <!-- REGISTRAR TURNO (Solo Grifero) -->
              <li class="nav-item">
                <a href="#" onclick="cargar_contenido('contenido_principal','turnos/view_registrar_turno.php')" class="nav-link">
                  <i class="nav-icon fas fa-clipboard-check"></i>
                  <p style="color:white">Registrar Turno</p>
                </a>
              </li>

              <?php if (modulo_activo('ventas')) { ?>
              <li class="nav-item">
                <a href="#" onclick="cargar_contenido('contenido_principal','ventas/view_ventas.php')" class="nav-link">
                  <i class="nav-icon fas fa-cash-register"></i>
                  <p style="color:white">Ventas</p>
                </a>
              </li>
              <?php } ?>

              <?php if (modulo_activo('inventario')) { ?>
              <li class="nav-item">
                <a href="#" onclick="cargar_contenido('contenido_principal','inventario/view_varillaje.php')" class="nav-link">
                  <i class="nav-icon fas fa-ruler-vertical"></i>
                  <p style="color:white">Varillaje</p>
                </a>
              </li>
              <?php } ?>

              <!-- MIS CRÉDITOS -->
              <li class="nav-item">
                <a href="#" onclick="cargar_contenido('contenido_principal','creditos/view_creditos_pendientes.php')" class="nav-link">
                  <i class="nav-icon fas fa-credit-card"></i>
                  <p style="color:white">Mis Créditos</p>
                </a>
              </li>

              <!-- MIS REPORTES -->
              <li class="nav-item">
                <a href="#" onclick="cargar_contenido('contenido_principal','reportes/view_mis_reportes.php')" class="nav-link">
                  <i class="nav-icon fas fa-file-alt"></i>
                  <p style="color:white">Mis Reportes</p>
                </a>
              </li>

              <!-- CLIENTES -->
              <li class="nav-item">
                <a href="#" onclick="cargar_contenido('contenido_principal','clientes/view_clientes.php')" class="nav-link">
                  <i class="nav-icon fas fa-user-friends"></i>
                  <p style="color:white">Clientes</p>
                </a>
              </li>

            <?php } ?>

            <!-- MI PERFIL (Ambos roles) -->
            <li class="header text-center" style="color:#FFFFFF; background-color:#023D77; border-radius: 10px;">
              <b>MI CUENTA</b>
            </li>
            <li class="nav-item">
              <a href="#" onclick="cargar_contenido('contenido_principal','usuario/view_mi_perfil.php')" class="nav-link">
                <i class="nav-icon fas fa-user-circle"></i>
                <p style="color:white">Mi Perfil</p>
              </a>
            </li>

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
    $.ajaxSetup({ headers: { 'X-CSRF-Token': <?php echo json_encode(token_csrf()); ?> } });
    $(document).ajaxError(function(evento, xhr) {
      if (xhr.status === 401) {
        window.location.href = '../index.php';
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
      // Cargar dashboard automáticamente según el rol
      <?php if ($_SESSION['S_ROL'] == "ADMINISTRADOR") { ?>
        cargar_contenido('contenido_principal', 'reportes/view_dashboard.php');
      <?php } else if ($_SESSION['S_ROL'] == "GRIFERO") { ?>
        cargar_contenido('contenido_principal', 'turnos/view_registrar_turno.php');
      <?php } ?>
    });

  </script>
</body>
</html>

<style>
  .main-sidebar {
    background: linear-gradient(135deg, #023D77, #0266C8) !important;
    color: white !important;
  }

  .sidebar-dark-primary {
    background: linear-gradient(135deg, #023D77, #0266C8) !important;
    color: white !important;
  }
</style>