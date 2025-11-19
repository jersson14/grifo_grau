<?php
// Configurar zona horaria de Lima, Perú (UTC-5)
date_default_timezone_set('America/Lima');

session_start();
if (!isset($_SESSION['S_ID'])) {
  header('Location: ../index.php');
}
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
                    <a href="#" onclick="cargar_contenido('contenido_principal','turnos/view_abrir_turno.php')" class="nav-link">
                      <i class="nav-icon far fa-circle"></i>
                      <p style="color:white">Abrir Turno</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="#" onclick="cargar_contenido('contenido_principal','turnos/view_abrir_turno.php')" class="nav-link">
                      <i class="nav-icon far fa-circle"></i>
                      <p style="color:white">Turnos Activos</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="#" onclick="cargar_contenido('contenido_principal','turnos/view_historial.php')" class="nav-link">
                      <i class="nav-icon far fa-circle"></i>
                      <p style="color:white">Historial de Turnos</p>
                    </a>
                  </li>
                </ul>
              </li>

              <!-- CRÉDITOS PENDIENTES -->
              <li class="nav-item">
                <a href="#" onclick="cargar_contenido('contenido_principal','creditos/view_creditos_pendientes.php')" class="nav-link">
                  <i class="nav-icon fas fa-credit-card"></i>
                  <p style="color:white">Créditos Pendientes</p>
                </a>
              </li>

              <!-- REPORTES -->
              <li class="nav-item has-treeview">
                <a href="#" class="nav-link">
                  <i class="nav-icon fas fa-file-alt"></i>
                  <p style="color:white">
                    Reportes Diarios
                    <i class="right fas fa-angle-left"></i>
                  </p>
                </a>
                <ul class="nav nav-treeview">
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

            <?php } ?>

            <?php if ($_SESSION['S_ROL'] == "GRIFERO") { ?>
              
              <!-- MI TURNO (Solo Grifero) -->
              <li class="nav-item">
                <a href="#" onclick="cargar_contenido('contenido_principal','turnos/view_abrir_turno.php')" class="nav-link">
                  <i class="nav-icon fas fa-tasks"></i>
                  <p style="color:white">Mi Turno</p>
                </a>
              </li>

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
    
    <!-- DASHBOARD OCULTO PARA REEMPLAZO -->
    <div style="display:none;">
      <?php if ($_SESSION['S_ROL'] == "ADMINISTRADOR") { ?>
        <div class="content-header">
          <div class="container-fluid">
            <div class="row mb-2">
              <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-tachometer-alt"></i> <b>DASHBOARD - ADMINISTRADOR</b></h1>
              </div>
              <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                  <li class="breadcrumb-item"><a href="#">Inicio</a></li>
                  <li class="breadcrumb-item active">Dashboard</li>
                </ol>
              </div>
            </div>
          </div>
        </div>

        <div class="content">
          <div class="container-fluid">
            <div class="row">
              <!-- TURNOS ACTIVOS HOY -->
              <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                  <div class="inner">
                    <h3 id="total_turnos_activos">0</h3>
                    <p>Turnos Activos Hoy</p>
                  </div>
                  <div class="icon">
                    <i class="fas fa-clock"></i>
                  </div>
                  <a href="#" onclick="cargar_contenido('contenido_principal','turnos/view_turnos_activos.php')" class="small-box-footer">
                    Ver más <i class="fas fa-arrow-circle-right"></i>
                  </a>
                </div>
              </div>

              <!-- TOTAL VENTAS DEL DÍA -->
              <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                  <div class="inner">
                    <h3 id="total_ventas_dia">S/ 0.00</h3>
                    <p>Ventas del Día</p>
                  </div>
                  <div class="icon">
                    <i class="fas fa-dollar-sign"></i>
                  </div>
                  <a href="#" onclick="cargar_contenido('contenido_principal','reportes/view_listado_reportes.php')" class="small-box-footer">
                    Ver más <i class="fas fa-arrow-circle-right"></i>
                  </a>
                </div>
              </div>

              <!-- CRÉDITOS PENDIENTES -->
              <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                  <div class="inner">
                    <h3 id="total_creditos_pendientes">0</h3>
                    <p>Créditos Pendientes</p>
                  </div>
                  <div class="icon">
                    <i class="fas fa-credit-card"></i>
                  </div>
                  <a href="#" onclick="cargar_contenido('contenido_principal','creditos/view_creditos_pendientes.php')" class="small-box-footer">
                    Ver más <i class="fas fa-arrow-circle-right"></i>
                  </a>
                </div>
              </div>

              <!-- REPORTES PENDIENTES VALIDACIÓN -->
              <div class="col-lg-3 col-6">
                <div class="small-box bg-danger">
                  <div class="inner">
                    <h3 id="total_reportes_pendientes">0</h3>
                    <p>Reportes por Validar</p>
                  </div>
                  <div class="icon">
                    <i class="fas fa-file-alt"></i>
                  </div>
                  <a href="#" onclick="cargar_contenido('contenido_principal','reportes/view_validar_reportes.php')" class="small-box-footer">
                    Ver más <i class="fas fa-arrow-circle-right"></i>
                  </a>
                </div>
              </div>
            </div>

            <!-- GRÁFICOS -->
            <div class="row">
              <!-- GRÁFICO DE VENTAS ÚLTIMOS 7 DÍAS -->
              <div class="col-md-8">
                <div class="card">
                  <div class="card-header" style="background: linear-gradient(135deg, #023D77, #0266C8)">
                    <h3 class="card-title" style="color:white"><i class="fas fa-chart-line"></i> Ventas de los Últimos 7 Días</h3>
                  </div>
                  <div class="card-body">
                    <canvas id="grafico_ventas_semana" style="height: 250px;"></canvas>
                  </div>
                </div>
              </div>

              <!-- GRÁFICO DE PRODUCTOS MÁS VENDIDOS -->
              <div class="col-md-4">
                <div class="card">
                  <div class="card-header" style="background: linear-gradient(135deg, #28a745, #218838)">
                    <h3 class="card-title" style="color:white"><i class="fas fa-chart-pie"></i> Productos Más Vendidos</h3>
                  </div>
                  <div class="card-body">
                    <canvas id="grafico_productos" style="height: 250px;"></canvas>
                  </div>
                </div>
              </div>
            </div>

            <!-- GRÁFICOS ADICIONALES -->
            <div class="row">
              <!-- GRÁFICO DE CRÉDITOS -->
              <div class="col-md-6">
                <div class="card">
                  <div class="card-header" style="background: linear-gradient(135deg, #ffc107, #ff9800)">
                    <h3 class="card-title" style="color:white"><i class="fas fa-chart-bar"></i> Estado de Créditos</h3>
                  </div>
                  <div class="card-body">
                    <canvas id="grafico_creditos" style="height: 200px;"></canvas>
                  </div>
                </div>
              </div>

              <!-- GRÁFICO DE TURNOS -->
              <div class="col-md-6">
                <div class="card">
                  <div class="card-header" style="background: linear-gradient(135deg, #17a2b8, #138496)">
                    <h3 class="card-title" style="color:white"><i class="fas fa-chart-area"></i> Turnos del Mes</h3>
                  </div>
                  <div class="card-body">
                    <canvas id="grafico_turnos" style="height: 200px;"></canvas>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      <?php } ?>

      <!-- DASHBOARD GRIFERO -->
      <?php if ($_SESSION['S_ROL'] == "GRIFERO") { ?>
        <div class="content-header">
          <div class="container-fluid">
            <div class="row mb-2">
              <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-tachometer-alt"></i> <b>DASHBOARD - GRIFERO</b></h1>
              </div>
              <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                  <li class="breadcrumb-item"><a href="#">Inicio</a></li>
                  <li class="breadcrumb-item active">Dashboard</li>
                </ol>
              </div>
            </div>
          </div>
        </div>

        <div class="content">
          <div class="container-fluid">
            
            <!-- MI TURNO ASIGNADO -->
            <div class="row">
              <div class="col-md-12">
                <div class="card card-primary card-outline">
                  <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-info-circle"></i> Mi Turno Asignado</h3>
                  </div>
                  <div class="card-body">
                    <div id="info_turno_asignado">
                      <div class="alert alert-info">
                        <i class="fas fa-spinner fa-spin"></i> Cargando información del turno...
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="row">
              <!-- MIS VENTAS DE HOY -->
              <div class="col-lg-4 col-6">
                <div class="small-box bg-success">
                  <div class="inner">
                    <h3 id="mis_ventas_hoy">S/ 0.00</h3>
                    <p>Mis Ventas de Hoy</p>
                  </div>
                  <div class="icon">
                    <i class="fas fa-dollar-sign"></i>
                  </div>
                  <a href="#" onclick="cargar_contenido('contenido_principal','ventas/view_mi_turno.php')" class="small-box-footer">
                    Ver detalle <i class="fas fa-arrow-circle-right"></i>
                  </a>
                </div>
              </div>

              <!-- CRÉDITOS REGISTRADOS -->
              <div class="col-lg-4 col-6">
                <div class="small-box bg-warning">
                  <div class="inner">
                    <h3 id="mis_creditos_hoy">0</h3>
                    <p>Créditos Registrados Hoy</p>
                  </div>
                  <div class="icon">
                    <i class="fas fa-credit-card"></i>
                  </div>
                  <a href="#" onclick="cargar_contenido('contenido_principal','creditos/view_mis_creditos.php')" class="small-box-footer">
                    Ver más <i class="fas fa-arrow-circle-right"></i>
                  </a>
                </div>
              </div>

              <!-- MIS TURNOS TOTALES -->
              <div class="col-lg-4 col-6">
                <div class="small-box bg-info">
                  <div class="inner">
                    <h3 id="mis_turnos_totales">0</h3>
                    <p>Total de Mis Turnos</p>
                  </div>
                  <div class="icon">
                    <i class="fas fa-calendar-check"></i>
                  </div>
                  <a href="#" onclick="cargar_contenido('contenido_principal','reportes/view_mis_reportes.php')" class="small-box-footer">
                    Ver historial <i class="fas fa-arrow-circle-right"></i>
                  </a>
                </div>
              </div>
            </div>

            <!-- ACCESO RÁPIDO -->
            <div class="row">
              <div class="col-md-12">
                <div class="card">
                  <div class="card-header" style="background: linear-gradient(135deg, #023D77, #0266C8)">
                    <h3 class="card-title" style="color:white"><i class="fas fa-bolt"></i> Acceso Rápido</h3>
                  </div>
                  <div class="card-body">
                    <a href="#" onclick="cargar_contenido('contenido_principal','ventas/view_mi_turno.php')" class="btn btn-app btn-lg">
                      <i class="fas fa-tasks"></i> Mi Turno
                    </a>
                    <a href="#" onclick="cargar_contenido('contenido_principal','creditos/view_mis_creditos.php')" class="btn btn-app btn-lg">
                      <i class="fas fa-credit-card"></i> Créditos
                    </a>
                    <a href="#" onclick="cargar_contenido('contenido_principal','reportes/view_mis_reportes.php')" class="btn btn-app btn-lg">
                      <i class="fas fa-file-alt"></i> Mis Reportes
                    </a>
                  </div>
                </div>
              </div>
            </div>

          </div>
        </div>
      <?php } ?>

    </div>

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
  <script src="../plantilla/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="../plantilla/dist/js/adminlte.min.js"></script>
  <script src="../utilitario/DataTables/datatables.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  
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
        cargar_contenido('contenido_principal', 'turnos/view_abrir_turno.php');
      <?php } ?>
    });

    function cargar_dashboard_admin() {
      // Total turnos activos
      $.ajax({
        url: '../controller/dashboard/controlador_total_turnos_activos.php',
        type: 'POST',
        success: function(resp) {
          $('#total_turnos_activos').html(resp || '0');
        },
        error: function() {
          $('#total_turnos_activos').html('0');
        }
      });
      
      // Total ventas del día
      $.ajax({
        url: '../controller/dashboard/controlador_total_ventas_dia.php',
        type: 'POST',
        success: function(resp) {
          $('#total_ventas_dia').html('S/ ' + (resp || '0.00'));
        },
        error: function() {
          $('#total_ventas_dia').html('S/ 0.00');
        }
      });
      
      // Créditos pendientes
      $.ajax({
        url: '../controller/dashboard/controlador_total_creditos_pendientes.php',
        type: 'POST',
        success: function(resp) {
          $('#total_creditos_pendientes').html(resp || '0');
        },
        error: function() {
          $('#total_creditos_pendientes').html('0');
        }
      });
      
      // Reportes pendientes de validación
      $.ajax({
        url: '../controller/dashboard/controlador_total_reportes_pendientes.php',
        type: 'POST',
        success: function(resp) {
          $('#total_reportes_pendientes').html(resp || '0');
        },
        error: function() {
          $('#total_reportes_pendientes').html('0');
        }
      });
      
      // Inicializar gráficos con datos reales
      inicializar_graficos();
    }

    var graficoVentas, graficoProductos, graficoCreditos, graficoTurnos;

    function inicializar_graficos() {
      // GRÁFICO DE VENTAS ÚLTIMOS 7 DÍAS - CON DATOS REALES
      const ctx1 = document.getElementById('grafico_ventas_semana');
      if (ctx1) {
        graficoVentas = new Chart(ctx1, {
          type: 'line',
          data: {
            labels: ['Cargando...'],
            datasets: [{
              label: 'Ventas (S/.)',
              data: [0],
              borderColor: '#023D77',
              backgroundColor: 'rgba(2, 61, 119, 0.1)',
              tension: 0.4,
              fill: true
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
              legend: {
                display: true,
                position: 'top'
              }
            },
            scales: {
              y: {
                beginAtZero: true,
                ticks: {
                  callback: function(value) {
                    return 'S/ ' + value.toFixed(2);
                  }
                }
              }
            }
          }
        });
        
        // Cargar datos reales
        $.ajax({
          url: '../controller/dashboard/controlador_ventas_semana_grafico.php',
          type: 'POST',
          dataType: 'json',
          success: function(data) {
            graficoVentas.data.labels = data.labels;
            graficoVentas.data.datasets[0].data = data.values;
            graficoVentas.update();
          },
          error: function() {
            graficoVentas.data.labels = ['Sin datos'];
            graficoVentas.data.datasets[0].data = [0];
            graficoVentas.update();
          }
        });
      }

      // GRÁFICO DE PRODUCTOS MÁS VENDIDOS - CON DATOS REALES
      const ctx2 = document.getElementById('grafico_productos');
      if (ctx2) {
        graficoProductos = new Chart(ctx2, {
          type: 'doughnut',
          data: {
            labels: ['Cargando...'],
            datasets: [{
              data: [0],
              backgroundColor: [
                '#023D77',
                '#0266C8',
                '#28a745',
                '#ffc107',
                '#dc3545'
              ]
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
              legend: {
                position: 'bottom'
              }
            }
          }
        });
        
        // Cargar datos reales
        $.ajax({
          url: '../controller/dashboard/controlador_productos_mas_vendidos.php',
          type: 'POST',
          dataType: 'json',
          success: function(data) {
            graficoProductos.data.labels = data.labels;
            graficoProductos.data.datasets[0].data = data.values;
            graficoProductos.update();
          },
          error: function() {
            graficoProductos.data.labels = ['Sin datos'];
            graficoProductos.data.datasets[0].data = [0];
            graficoProductos.update();
          }
        });
      }

      // GRÁFICO DE ESTADO DE CRÉDITOS - CON DATOS REALES
      const ctx3 = document.getElementById('grafico_creditos');
      if (ctx3) {
        graficoCreditos = new Chart(ctx3, {
          type: 'bar',
          data: {
            labels: ['Pendientes', 'Pagados', 'Vencidos'],
            datasets: [{
              label: 'Cantidad',
              data: [0, 0, 0],
              backgroundColor: [
                '#ffc107',
                '#28a745',
                '#dc3545'
              ]
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
              legend: {
                display: false
              }
            },
            scales: {
              y: {
                beginAtZero: true,
                ticks: {
                  stepSize: 1
                }
              }
            }
          }
        });
        
        // Cargar datos reales
        $.ajax({
          url: '../controller/dashboard/controlador_estado_creditos.php',
          type: 'POST',
          dataType: 'json',
          success: function(data) {
            graficoCreditos.data.datasets[0].data = data.values;
            graficoCreditos.update();
          },
          error: function() {
            graficoCreditos.data.datasets[0].data = [0, 0, 0];
            graficoCreditos.update();
          }
        });
      }

      // GRÁFICO DE TURNOS DEL MES - CON DATOS REALES
      const ctx4 = document.getElementById('grafico_turnos');
      if (ctx4) {
        graficoTurnos = new Chart(ctx4, {
          type: 'line',
          data: {
            labels: ['Cargando...'],
            datasets: [{
              label: 'Turnos Completados',
              data: [0],
              borderColor: '#17a2b8',
              backgroundColor: 'rgba(23, 162, 184, 0.2)',
              tension: 0.4,
              fill: true
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
              legend: {
                display: true
              }
            },
            scales: {
              y: {
                beginAtZero: true,
                ticks: {
                  stepSize: 1
                }
              }
            }
          }
        });
        
        // Cargar datos reales
        $.ajax({
          url: '../controller/dashboard/controlador_turnos_mes.php',
          type: 'POST',
          dataType: 'json',
          success: function(data) {
            graficoTurnos.data.labels = data.labels;
            graficoTurnos.data.datasets[0].data = data.values;
            graficoTurnos.update();
          },
          error: function() {
            graficoTurnos.data.labels = ['Sin datos'];
            graficoTurnos.data.datasets[0].data = [0];
            graficoTurnos.update();
          }
        });
      }
    }

    function cargar_dashboard_grifero() {
      // Cargar información del turno asignado
      $.ajax({
        url: '../controller/turnos/controlador_info_turno_grifero.php',
        type: 'POST',
        success: function(resp) {
          $('#info_turno_asignado').html(resp);
        }
      });
      
      // Mis ventas de hoy
      $.ajax({
        url: '../controller/reportes/controlador_mis_ventas_hoy.php',
        type: 'POST',
        success: function(resp) {
          $('#mis_ventas_hoy').html('S/ ' + resp);
        }
      });
      
      // Mis créditos de hoy
      $.ajax({
        url: '../controller/creditos/controlador_mis_creditos_hoy.php',
        type: 'POST',
        success: function(resp) {
          $('#mis_creditos_hoy').html(resp);
        }
      });
      
      // Total de mis turnos
      $.ajax({
        url: '../controller/turnos/controlador_mis_turnos_totales.php',
        type: 'POST',
        success: function(resp) {
          $('#mis_turnos_totales').html(resp);
        }
      });
    }
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