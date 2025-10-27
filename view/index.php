<?php
session_start();
if (!isset($_SESSION['S_ID'])) {
  header('Location: ../index.php');
}
?>
<!DOCTYPE html>
<!--
This is a starter template page. Use this page to start your new project from
scratch. This page gets rid of all links and provides the needed markup only.
-->
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>INCOCAT S.R.L.</title>
  <!-- En tu <head> -->

  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome Icons -->
  <link rel="stylesheet" href="../plantilla/plugins//fontawesome-free/css/all.min.css">
  <!-- Theme style -->
  <link rel="shortcut icon" href="../img/incoca.png" type="image/jpg">

  <link rel="stylesheet" href="../plantilla/dist//css/adminlte.min.css">
  <link href="../utilitario/DataTables/datatables.min.css" type="text/css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
</head>

<body class="">
  <div class="wrapper">
    <?php if ($_SESSION['S_ROL'] == "ADMINISTRADOR") { ?>
      <!-- Navbar -->
      <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <!-- Left navbar links -->
        <ul class="navbar-nav">
          <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
          </li>
        </ul>
        <!-- Right navbar links -->
        <ul class="navbar-nav ml-auto">
          <!-- Notifications Dropdown Menu -->

          <li class="nav-item dropdown">
            <a class="nav-link" data-toggle="dropdown" href="#">
              <img src="../<?php echo $_SESSION['S_FOTO']; ?>" class="img-circle elevation-1" width="15" height="18">
              <b>Usuario: <?php echo $_SESSION['S_COMPLETOS'] ?></b>
              <i class="fas fa-caret-down"></i>
            </a>
            <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
              <div class="dropdown-divider"></div>
              <a href="../controller/usuario/controlador_cerrar_sesion.php" class="dropdown-item">
                <i class="fas fa-power-off mr-2"></i><u><b>Cerrar Sesión</b></u>
              </a>
              <div class="dropdown-divider"></div>
            </div>
          </li>
        </ul>

      </nav>
    <?php
    }
    ?>
    <?php if ($_SESSION['S_ROL'] == "SECRETARIA") { ?>
      <!-- Navbar -->
      <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <!-- Left navbar links -->
        <ul class="navbar-nav">
          <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
          </li>
        </ul>

        <!-- Right navbar links -->
        <ul class="navbar-nav ml-auto">
          <!-- Notifications Dropdown Menu -->


          <li class="nav-item dropdown">
            <a class="nav-link" data-toggle="dropdown" href="#">
              <img src="../<?php echo $_SESSION['S_FOTO']; ?>" class="img-circle elevation-1" width="15" height="18">

              <b>Usuario: <?php echo $_SESSION['S_COMPLETOS'] ?></b>
              <i class="fas fa-caret-down"></i>
            </a>
            <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
              <div class="dropdown-divider"></div>
              <a href="../controller/usuario/controlador_cerrar_sesion.php" class="dropdown-item">
                <i class="fas fa-power-off mr-2"></i><u><b>Cerrar Sesión</b></u>
              </a>
              <div class="dropdown-divider"></div>
            </div>
          </li>
        </ul>

      </nav>
    <?php
    }
    ?>
    <!-- /.navbar -->

    <!-- Main Sidebar Container -->
    <aside class="main-sidebar sidebar-dark-primary elevation-4">
      <!-- Brand Logo -->
      <a href="index.php" class="brand-link">
        <img src="../img/incocat.jpeg" alt="<?php echo $_SESSION['S_RAZON']; ?>" width="100%" height="auto">
      </a>

      <!-- Sidebar -->
      <div class="sidebar">
        <!-- Sidebar user panel (optional) -->
        <div class="user-panel mt-1 pb-3 mb-3 d-flex">
          <div class="image">
            <img src="../<?php echo $_SESSION['S_FOTO']; ?>" class="img-circle elevation-2" style="max-width: 100%;height: auto;">
          </div>
          <div class="info">
            <a style="text-align:center;" href="#" class="d-block"><i class="fa fa-circle text-success fa-0x"></i> ¡Hola!<br> <b style="color:white"><?php echo $_SESSION['S_NOMBRE']; ?></b></a>
            <a style="text-align:center;margin:5px;color:white;font-size:15px" href="#" class="d-block">&nbsp;&nbsp;<b style="text-align:center"><i class="fa fa-user text-success fa-0x"></i><em> ROL: <?php echo $_SESSION['S_ROL']; ?></em></b></a>
          </div>
        </div>
        <!-- Sidebar Menu -->
        <nav class="mt-1">
          <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
            <li class="header text-center" style="color:#FFFFFF; background-color:#023D77; border-radius: 10px;">
              <b>GESTIÓN GENERAL</b>
            </li>

            <!-- Add icons to the links using the .nav-icon class
               with font-awesome or any other icon font library -->
            <?php if ($_SESSION['S_ROL'] == "ADMINISTRADOR") { ?>
              <li class="nav-item">
                <a href="#" class="nav-link">
                  <i class="nav-icon fas fa-globe"></i>

                  <p style="color:white">
                    Ubigeo
                    <i class="right fas fa-angle-left"></i>
                  </p>
                </a>
                <ul class="nav nav-treeview">
                  <li class="nav-item">
                    <a href="#" onclick="cargar_contenido('contenido_principal','regiones/view_regiones.php')" class="nav-link">
                      <i class="nav-icon far fa-circle"></i>
                      <p style="color:white">
                        Regiones
                      </p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="#" onclick="cargar_contenido('contenido_principal','provincias/view_provincias.php')" class="nav-link">
                      <i class="nav-icon far fa-circle"></i>
                      <p style="color:white">
                        Provincias
                      </p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="#" onclick="cargar_contenido('contenido_principal','distritos/view_distritos.php')" class="nav-link">
                      <i class="nav-icon far fa-circle"></i>
                      <p style="color:white">
                        Distritos
                      </p>
                    </a>
                  </li>
                </ul>
              </li>

              <li class="nav-item">
                <a href="#" onclick="cargar_contenido('contenido_principal','servicios/view_servicios.php')" class="nav-link">
                  <i class="nav-icon fas fas fa-hands-helping"></i>

                  <p style="color:white">
                    Servicios
                  </p>
                </a>
              </li>
              <li class="nav-item">
                <a href="#" onclick="cargar_contenido('contenido_principal','reuniones/view_reuniones.php')" class="nav-link">
                  <i class="nav-icon fas fa-calendar-check"></i>

                  <p style="color:white">
                    Agendar reuniones
                  </p>
                </a>
              </li>
              <li class="nav-item">
                <a href="#" onclick="cargar_contenido('contenido_principal','clientes/view_clientes.php')" class="nav-link">
                  <i class="nav-icon fas fa-users"></i>
                  <p style="color:white">
                    Clientes
                  </p>
                </a>
              </li>
              <li class="nav-item">
                <a href="#" class="nav-link">
                  <i class="nav-icon fas fa-folder"></i>
                  <p style="color:white">
                    Gestión de Expedientes
                    <i class="right fas fa-angle-left"></i>
                  </p>
                </a>
                <ul class="nav nav-treeview">
                  <li class="nav-item">
                    <a href="#" onclick="cargar_contenido('contenido_principal','expedientes/view_registro_expediente.php')" class="nav-link">
                      <i class="nav-icon far fa-circle"></i>
                      <p style="color:white">
                        Registro de Expediente
                      </p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="#" onclick="cargar_contenido('contenido_principal','expedientes/view_expedientes.php')" class="nav-link">
                      <i class="nav-icon far fa-circle"></i>
                      <p style="color:white">
                        Lista de Expedientes
                      </p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="#" onclick="cargar_contenido('contenido_principal','expedientes/view_expediente_archivados.php')" class="nav-link">
                      <i class="nav-icon far fa-circle"></i>
                      <p style="color:white">
                        Expedientes Archivados
                      </p>
                    </a>
                  </li>
                  <li class="nav-item" hidden>
                    <a href="#" onclick="cargar_contenido('contenido_principal','expedientes/view_editar_expediente.php')" class="nav-link">
                      <i class="nav-icon far fa-circle"></i>
                      <p style="color:white">
                        Expedientes Archivados
                      </p>
                    </a>
                  </li>
                </ul>
              </li>
              <li class="nav-item">
                <a href="#" onclick="cargar_contenido('contenido_principal','pagos/view_pagos.php')" class="nav-link">
                  <i class="nav-icon fas fa-hand-holding-usd"></i>
                  <p style="color:white">
                    Registro de Pagos
                  </p>
                </a>
              </li>
              <li class="nav-item">
                <a href="#" class="nav-link">
                  <i class="nav-icon fas fa-coins"></i>

                  <p style="color:white">
                    Ingresos y Gastos
                    <i class="right fas fa-angle-left"></i>
                  </p>
                </a>
                <ul class="nav nav-treeview">
                  <li class="nav-item">
                    <a href="#" onclick="cargar_contenido('contenido_principal','indicadores/view_indicadores.php')" class="nav-link">
                      <i class="nav-icon far fa-circle"></i>
                      <p style="color:white">
                        Indicadores
                      </p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="#" onclick="cargar_contenido('contenido_principal','ingresos/view_ingresos.php')" class="nav-link">
                      <i class="nav-icon far fa-circle"></i>
                      <p style="color:white">
                        Ingresos
                      </p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="#" onclick="cargar_contenido('contenido_principal','gastos/view_gastos.php')" class="nav-link">
                      <i class="nav-icon far fa-circle"></i>
                      <p style="color:white">
                        Gastos
                      </p>
                    </a>
                  </li>

                </ul>
              </li>

              <li class="header text-center" style="color:#FFFFFF; background-color:#023D77; border-radius: 10px;">
                <b>REPORTES DE EXPEDIENTES</b>
              </li>
              <li class="nav-item">
                <a href="#" onclick="cargar_contenido('contenido_principal','reportes/view_expedientes_fecha_provincia.php')" class="nav-link">
                  <i class="nav-icon fas fa-file-pdf"></i>

                  <p style="color:white">
                    Por fecha y provincia
                  </p>
                </a>
              </li>
              <li class="nav-item">
                <a href="#" onclick="cargar_contenido('contenido_principal','reportes/view_expediente_fecha_distritos.php')" class="nav-link">
                  <i class="nav-icon fas fa-file-pdf"></i> <!-- Archivada -->

                  <p style="color:white">
                    Por fecha y distrito
                  </p>
                </a>
              </li>
              <li class="nav-item">
                <a href="#" onclick="cargar_contenido('contenido_principal','reportes/view_fechas_estado.php')" class="nav-link">
                  <i class="nav-icon fas fa-file-pdf"></i> <!-- Archivada -->

                  <p style="color:white">
                    Por fecha y estado
                  </p>
                </a>
              </li>
              <li class="header text-center" style="color:#FFFFFF; background-color:#023D77; border-radius: 10px;">
                <b>CONFIGURACIÓN Y MANUAL</b>
              </li>
              <li class="nav-item">
                <a onclick="cargar_contenido('contenido_principal','usuario/view_usuario.php')" class="nav-link">
                  <i class="nav-icon fas fa-user"></i>
                  <p style="color:white">
                    Usuarios
                  </p>
                </a>
              </li>
              <li class="nav-item">
                <a href="#" target="blank" onclick="" class="nav-link">
                  <i class="nav-icon fas fa-file-pdf"></i>
                  <p style="color:white">
                    Manual de Usuario
                  </p>
                </a>
              </li>
            <?php
            }
            ?>
            <?php if ($_SESSION['S_ROL'] == "SECRETARIA") { ?>



              <li class="nav-item">
                <a href="#" onclick="cargar_contenido('contenido_principal','servicios/view_servicios.php')" class="nav-link">
                  <i class="nav-icon fas fas fa-hands-helping"></i>

                  <p style="color:white">
                    Servicios
                  </p>
                </a>
              </li>
              <li class="nav-item">
                <a href="#" onclick="cargar_contenido('contenido_principal','reuniones/view_reuniones.php')" class="nav-link">
                  <i class="nav-icon fas fa-calendar-check"></i>

                  <p style="color:white">
                    Agendar reuniones
                  </p>
                </a>
              </li>
              <li class="nav-item">
                <a href="#" onclick="cargar_contenido('contenido_principal','clientes/view_clientes.php')" class="nav-link">
                  <i class="nav-icon fas fa-users"></i>
                  <p style="color:white">
                    Clientes
                  </p>
                </a>
              </li>
              <li class="nav-item">
                <a href="#" class="nav-link">
                  <i class="nav-icon fas fa-folder"></i>
                  <p style="color:white">
                    Gestión de Expedientes
                    <i class="right fas fa-angle-left"></i>
                  </p>
                </a>
                <ul class="nav nav-treeview">
                  <li class="nav-item">
                    <a href="#" onclick="cargar_contenido('contenido_principal','expedientes/view_registro_expediente.php')" class="nav-link">
                      <i class="nav-icon far fa-circle"></i>
                      <p style="color:white">
                        Registro de Expediente
                      </p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="#" onclick="cargar_contenido('contenido_principal','expedientes/view_expedientes.php')" class="nav-link">
                      <i class="nav-icon far fa-circle"></i>
                      <p style="color:white">
                        Lista de Expedientes
                      </p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="#" onclick="cargar_contenido('contenido_principal','expedientes/view_expediente_archivados.php')" class="nav-link">
                      <i class="nav-icon far fa-circle"></i>
                      <p style="color:white">
                        Expedientes Archivados
                      </p>
                    </a>
                  </li>
                </ul>
              </li>
              <li class="nav-item">
                <a href="#" onclick="cargar_contenido('contenido_principal','pagos/view_pagos.php')" class="nav-link">
                  <i class="nav-icon fas fa-hand-holding-usd"></i>
                  <p style="color:white">
                    Registro de Pagos
                  </p>
                </a>
              </li>
              <li class="nav-item">
                <a href="#" class="nav-link">
                  <i class="nav-icon fas fa-coins"></i>

                  <p style="color:white">
                    Ingresos y Gastos
                    <i class="right fas fa-angle-left"></i>
                  </p>
                </a>
                <ul class="nav nav-treeview">

                  <li class="nav-item">
                    <a href="#" onclick="cargar_contenido('contenido_principal','ingresos/view_ingresos.php')" class="nav-link">
                      <i class="nav-icon far fa-circle"></i>
                      <p style="color:white">
                        Ingresos
                      </p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="#" onclick="cargar_contenido('contenido_principal','gastos/view_gastos.php')" class="nav-link">
                      <i class="nav-icon far fa-circle"></i>
                      <p style="color:white">
                        Gastos
                      </p>
                    </a>
                  </li>

                </ul>
              </li>

              <li class="header text-center" style="color:#FFFFFF; background-color:#023D77; border-radius: 10px;">
                <b>REPORTES DE EXPEDIENTES</b>
              </li>
              <li class="nav-item">
                <a href="#" onclick="cargar_contenido('contenido_principal','reportes/view_expedientes_fecha_provincia.php')" class="nav-link">
                  <i class="nav-icon fas fa-file-pdf"></i>

                  <p style="color:white">
                    Por fecha y provincia
                  </p>
                </a>
              </li>
              <li class="nav-item">
                <a href="#" onclick="cargar_contenido('contenido_principal','reportes/view_expediente_fecha_distritos.php')" class="nav-link">
                  <i class="nav-icon fas fa-file-pdf"></i> <!-- Archivada -->

                  <p style="color:white">
                    Por fecha y distrito
                  </p>
                </a>
              </li>
              <li class="nav-item">
                <a href="#" onclick="cargar_contenido('contenido_principal','reportes/view_fechas_estado.php')" class="nav-link">
                  <i class="nav-icon fas fa-file-pdf"></i> <!-- Archivada -->

                  <p style="color:white">
                    Por fecha y estado
                  </p>
                </a>
              </li>
              <li class="header text-center" style="color:#FFFFFF; background-color:#023D77; border-radius: 10px;">
                <b>CONFIGURACIÓN Y MANUAL</b>
              </li>

              <li class="nav-item">
                <a href="#" target="blank" onclick="" class="nav-link">
                  <i class="nav-icon fas fa-file-pdf"></i>
                  <p style="color:white">
                    Manual de Usuario
                  </p>
                </a>
              </li>
            <?php
            }
            ?>
          </ul>
        </nav>
        <!-- /.sidebar-menu -->
      </div>
      <!-- /.sidebar -->
    </aside>
    <input type="text" id="txtprincipalid" value="<?php echo $_SESSION['S_ID']; ?>" hidden>
    <input type="text" id="txtprincipalusu" value="<?php echo $_SESSION['S_USU']; ?>" hidden>
    <input type="text" id="txtprincipalrol" value="<?php echo $_SESSION['S_ROL']; ?>" hidden>
    <input type="text" id="txtfotoempresa" value="<?php echo $_SESSION['S_FOTO_EMPRESA']; ?>" hidden>
    <input type="text" id="txtrazon" value="<?php echo $_SESSION['S_RAZON']; ?>" hidden>


    <div class="content-wrapper" id="contenido_principal">


      <!-- Content Wrapper. Contains page content -->

      <!-- Content Header (Page header) -->

      <?php if ($_SESSION['S_ROL'] == "ADMINISTRADOR") { ?>
        <div class="content-header">
          <div class="container-fluid">
            <div class="row mb-2">
              <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-home"></i>
                  <b>BIENVENIDOS AL SISTEMA</b>
                </h1>
              </div><!-- /.col -->
              <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                  <li class="breadcrumb-item"><a href="#">MENÚ</a></li>
                  <li class="breadcrumb-item active">MENÚ PRINCIPAL</li>
                </ol>
              </div><!-- /.col -->
            </div><!-- /.row -->
          </div><!-- /.container-fluid -->
        </div>
        <!-- /.content-header -->


        <div class="col-md-12">
          <div class="card card-primary">
            <div class="card-header py-2" style="background: linear-gradient(135deg, #023D77, #0266C8)">
              <h5 class="m-0" style="font-family:cooper; text-align:center; line-height: 1; padding: 0;">
                <i class="fas fa-list-ol" style="margin-right: 8px;"></i>DATOS IMPORTANTES
              </h5>
              <div class="card-tools" style="position: absolute; right: 10px; top: 5px;">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                  <i class="fas fa-minus"></i>
                </button>
              </div>
            </div>
            <div class="table-responsive" style="text-align:center">
              <!-- Resto del contenido igual -->
              <div class="card-body" style="background-color:white">
                <div class="row">
                  <div class="col-lg-3 col-6">
                    <!-- small box -->
                    <div class="small-box bg-secondary">
                      <div class="inner">
                        <b>Reuniones Hoy</b>
                        <h3 id="total_reuniones_hoy"><sup style="font-size: 20px"></sup></h3>

                      </div>
                      <div class="icon">
                        <i class="fas fa-calendar-check"></i> <!-- Factura con signo de dólar -->
                      </div>
                      <a href="#" onclick="cargar_contenido('contenido_principal','reuniones/view_reuniones.php')" class="small-box-footer"><b>Ver Reuniones</b>&nbsp;<i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                  </div>
                  <!-- ./col -->
                  <div class="col-lg-3 col-6">
                    <!-- small box -->
                    <div class="small-box bg-dark">
                      <div class="inner">
                        <b>Total de Expedientes</b>
                        <h3 id="total_expedientes"><sup style="font-size: 20px"></sup></h3>

                      </div>
                      <div class="icon">
                        <i class="fas fas fa-folder-open"></i> <!-- Factura con signo de dólar -->
                      </div>
                      <a href="#" onclick="cargar_contenido('contenido_principal','expedientes/view_expedientes.php')" class="small-box-footer"><b>Ver Expedientes</b>&nbsp;<i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                  </div>
                  <!-- ./col -->
                  <div class="col-lg-3 col-6">
                    <!-- small box -->
                    <div class="small-box bg-info">
                      <div class="inner">

                        <b>Expedientes en Proceso</b>
                        <h3 id="total_expedientes_proceso"><sup style="font-size: 20px"></sup></h3>

                      </div>
                      <div class="icon">
                        <i class="fas fas fa-folder-open"></i> <!-- Factura con signo de dólar -->
                      </div>
                      <a href="#" onclick="cargar_contenido('contenido_principal','expedientes/view_expedientes.php')" class="small-box-footer"><b>Ver Expedientes</b>&nbsp;<i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                  </div>
                  <!-- ./col -->
                  <div class="col-lg-3 col-6">
                    <!-- small box -->
                    <div class="small-box bg-primary">
                      <div class="inner">
                        <b>Expedientes en Trámite</b>
                        <h3 id="total_expedientes_tramite"><sup style="font-size: 20px"></sup></h3>

                      </div>
                      <div class="icon">
                        <i class="fas fas fa-folder-open"></i> <!-- Factura con signo de dólar -->
                      </div>

                      <a href="#" onclick="cargar_contenido('contenido_principal','expedientes/view_expedientes.php')" class="small-box-footer"><b>Ver Expedientes</b>&nbsp;<i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                  </div>
                  <div class="col-lg-3 col-6">
                    <!-- small box -->
                    <div class="small-box bg-danger">
                      <div class="inner">
                        <b> Expedientes Observados</b>
                        <h3 id="total_expedientes_observados"><sup style="font-size: 20px"></sup></h3>

                      </div>
                      <div class="icon">
                        <i class="fas fas fa-folder-open"></i> <!-- Factura con signo de dólar -->
                      </div>
                      <a href="#" onclick="cargar_contenido('contenido_principal','expedientes/view_expedientes.php')" class="small-box-footer"><b>Ver Expedientes</b>&nbsp;<i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                  </div>
                  <!-- ./col -->
                  <div class="col-lg-3 col-6">
                    <!-- small box -->
                    <div class="small-box bg-success">
                      <div class="inner">
                        <b>Expedientes Finalizados</b>
                        <h3 id="total_expedientes_finalizados"><sup style="font-size: 20px"></sup></h3>

                      </div>
                      <div class="icon">
                        <i class="fas fas fa-folder-open"></i> <!-- Factura con signo de dólar -->
                      </div>
                      <a href="#" onclick="cargar_contenido('contenido_principal','expedientes/view_expedientes.php')" class="small-box-footer"><b>Ver Expedientes</b>&nbsp;<i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                  </div>
                  <!-- ./col -->
                  <div class="col-lg-3 col-6">
                    <!-- small box -->
                    <div class="small-box bg-warning">
                      <div class="inner">

                        <b>Total de Clientes</b>
                        <h3 id="total_clientes"><sup style="font-size: 20px"></sup></h3>

                      </div>
                      <div class="icon">
                        <i class="nav-icon fas fa-users"></i>
                      </div>
                      <a href="#" onclick="cargar_contenido('contenido_principal','clientes/view_clientes.php')" class="small-box-footer"><b>Ver Clientes</b>&nbsp; <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                  </div>
                  <!-- ./col -->
                  <div class="col-lg-3 col-6">
                    <!-- small box -->
                    <div class="small-box bg-success">
                      <div class="inner">
                        <b>Total de Reuniones</b>
                        <h3 id="total_reuniones"><sup style="font-size: 20px"></sup></h3>

                      </div>
                      <div class="icon">
                        <i class="fas fa-calendar-check"></i> <!-- Factura con signo de dólar -->
                      </div>

                      <a href="#" onclick="cargar_contenido('contenido_principal','reuniones/view_reuniones.php')" class="small-box-footer"><b>Ver Reuniones</b>&nbsp; <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                  </div>
                  <div class="col-lg-3 col-6">
                    <!-- small box -->
                    <hr>

                    <div class="small-box bg-success">
                      <div class="inner">

                        <b> Total de Ingresos Hoy</b>
                        <h3 id="total_ingresos_hoy"><sup style="font-size: 20px"></sup></h3>

                      </div>
                      <div class="icon">
                        <i class="nav-icon fas fa-coins"></i>
                      </div>
                      <a href="#" onclick="cargar_contenido('contenido_principal','ingresos/view_ingresos.php')" class="small-box-footer"><b>Ver Ingresos</b>&nbsp;<i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                  </div>
                  <!-- ./col -->
                  <div class="col-lg-3 col-6">
                    <!-- small box -->
                    <hr>
                    <div class="small-box bg-danger">
                      <div class="inner">
                        <b>Total de Gastos Hoy</b>
                        <h3 id="total_gastos_hoy"><sup style="font-size: 20px"></sup></h3>

                      </div>
                      <div class="icon">
                        <i class="fas fas fas fa-coins"></i> <!-- Factura con signo de dólar -->
                      </div>
                      <a href="#" onclick="cargar_contenido('contenido_principal','gastos/view_gastos.php')" class="small-box-footer"><b>Ver Gastos</b>&nbsp;<i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                  </div>
                  <!-- ./col -->
                  <div class="col-lg-3 col-6">
                    <!-- small box -->
                    <hr>
                    <div class="small-box bg-success">
                      <div class="inner">

                        <b>Total de Ingresos Mes Actual</b>
                        <h3 id="total_ingresos_mes_actual"><sup style="font-size: 20px"></sup></h3>

                      </div>
                      <div class="icon">
                        <i class="nav-icon fas fa-coins"></i>
                      </div>
                      <a href="#" onclick="cargar_contenido('contenido_principal','ingresos/view_ingresos.php')" class="small-box-footer"><b>Ver Ingresos</b>&nbsp;<i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                  </div>
                  <!-- ./col -->
                  <div class="col-lg-3 col-6">
                    <!-- small box -->
                    <hr>
                    <div class="small-box bg-danger">
                      <div class="inner">
                        <b>Total de Gastos Mes Actual</b>
                        <h3 id="total_gastos_mes_actual"><sup style="font-size: 20px"></sup></h3>

                      </div>
                      <div class="icon">
                        <i class="fas fa-coins"></i> <!-- Factura con signo de dólar -->
                      </div>

                      <a href="#" onclick="cargar_contenido('contenido_principal','gastos/view_gastos.php')" class="small-box-footer"><b>Ver Gastos</b>&nbsp;<i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="col-md-12">
          <div class="card card-primary" style="border-radius: 15px; overflow: hidden;">
            <div class="card-header py-2" style="background: linear-gradient(135deg, #023D77, #0266C8)">
              <h5 class="m-0" style="font-family:cooper; text-align:center; line-height: 1; padding: 0; color: white;">
                <i class="fas fa-building" style="margin-right: 8px;"></i> DATOS DE LA EMPRESA
              </h5>
              <div class="card-tools" style="position: absolute; right: 10px; top: 5px;">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                  <i class="fas fa-minus"></i>
                </button>
              </div>
            </div>
            <div class="table-responsive" style="text-align:center">
              <div class="card-body" style="display: block;">
                <table id="tabla_empresa" class="table table-striped table-bordered" style="width:100%; border-radius: 10px; overflow: hidden; border-collapse: separate; border-spacing: 0;">
                  <thead style="background-color:#023D77;color:white;">
                    <tr>
                      <th style="text-align:center; border-top: none;">Nro.</th>
                      <th style="text-align:center; border-top: none;">Logo</th>
                      <th style="text-align:center; border-top: none;">Nombre</th>
                      <th style="text-align:center; border-top: none;">Email</th>
                      <th style="text-align:center; border-top: none;">Teléfono</th>
                      <th style="text-align:center; border-top: none;">Dirección</th>
                      <th style="text-align:center; border-top: none;">Acciones</th>
                    </tr>
                  </thead>
                </table>
              </div>
            </div>
          </div>
        </div>




        <!-- /.content-wrapper -->
        <div class="modal fade" id="modal_editar" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
          <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
              <div class="modal-header" style="background-color:#1FA0E0;">
                <h5 class="modal-title" id="exampleModalLabel" style="color:white; text-align:center"><b>EDITAR DATOS DE LA INSTITUCIÒN</b></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <div class="modal-body">
                <div class="row">
                  <div class="col-12 form-group" style="color:red">
                    <h6><b>Campos Obligatorios (*)</b></h6>
                  </div><br>
                  <div class="col-6 form-group">
                    <input type="text" id="txt_id_empresa" hidden>
                    <label for="">Nombre(*):</label>
                    <input type="text" class="form-control" id="txt_nombre" maxlenght="8">
                  </div>
                  <div class="col-6 form-group">
                    <label for="">Email(*):</label>
                    <input type="text" class="form-control" id="txt_email">
                  </div>
                  <div class="col-6 form-group">
                    <label for="">Código(*):</label>
                    <input type="text" class="form-control" id="txt_codigo">
                  </div>
                  <div class="col-6 form-group">
                    <label for="">Teléfono / Celular(*):</label>
                    <input type="text" class="form-control" id="txt_telefono" maxlenght="9" onkeypress="return soloNumeros(event)">
                  </div>
                  <div class="col-12 form-group">
                    <label for="">Dirección(*):</label>
                    <input type="text" class="form-control" id="txt_direccion">
                  </div>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fas fa-times ml-1"></i> Cerrar</button>
                <button type="button" class="btn btn-success" onclick="Modificar_Empleado()"><i class="fas fa-check"></i> Modificar</button>
              </div>
            </div>
          </div>
        </div>

        <div class="modal fade" id="modal_editar_foto" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
          <div class="modal-dialog" role="document">
            <div class="modal-content">
              <div class="modal-header" style="background: linear-gradient(135deg, #023D77, #0266C8)">
                <h5 class="modal-title" id="exampleModalLabel" style="color:white; text-align:center"><b>EDITAR FOTO DE LA INSTITUCIÓN: </b><label for="" id="lb_empresa"></label></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <div class="modal-body">
                <div class="row">
                  <div class="col-12">
                    <input type="text" id="fotoactual" hidden>
                    <input type="text" id="txt_idempresa_foto" hidden>
                    <label for="checkboxSuccess2" style="align:justify;color:red">
                      OJO: Una vez cambiado el logo, tambien se cambiara el logo en los reportes y ticket.
                    </label>
                    <label for="">Subir Foto:</label>
                    <input class="form-control" type="file" id="txt_foto">
                  </div>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fas fa-times ml-1"></i> Cerrar</button>
                <button type="button" class="btn btn-success" onclick="Modificar_Foto_Empresa()"><i class="fas fa-check"></i> Modificar</button>
              </div>
            </div>
          </div>
        </div>

      <?php
      }
      ?>
      <?php if ($_SESSION['S_ROL'] == "SECRETARIA") { ?>

        <!-- Main content -->

        <div class="content-header">
          <div class="container-fluid">
            <div class="row mb-2">
              <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-home"></i>
                  <b>BIENVENIDOS AL SISTEMA</b>
                </h1>
              </div><!-- /.col -->
              <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                  <li class="breadcrumb-item"><a href="#">MENÚ</a></li>
                  <li class="breadcrumb-item active">MENÚ PRINCIPAL</li>
                </ol>
              </div><!-- /.col -->
            </div><!-- /.row -->
          </div><!-- /.container-fluid -->
        </div>
        <!-- /.content-header -->


        <div class="col-md-12">
          <div class="card card-primary">
            <div class="card-header py-2" style="background: linear-gradient(135deg, #023D77, #0266C8)">
              <h5 class="m-0" style="font-family:cooper; text-align:center; line-height: 1; padding: 0;">
                <i class="fas fa-list-ol" style="margin-right: 8px;"></i>DATOS IMPORTANTES
              </h5>
              <div class="card-tools" style="position: absolute; right: 10px; top: 5px;">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                  <i class="fas fa-minus"></i>
                </button>
              </div>
            </div>
            <div class="table-responsive" style="text-align:center">
              <!-- Resto del contenido igual -->
              <div class="card-body" style="background-color:white">
                <div class="row">
                  <div class="col-lg-3 col-6">
                    <!-- small box -->
                    <div class="small-box bg-secondary">
                      <div class="inner">
                        <b>Reuniones Hoy</b>
                        <h3 id="total_reuniones_hoy"><sup style="font-size: 20px"></sup></h3>

                      </div>
                      <div class="icon">
                        <i class="fas fa-calendar-check"></i> <!-- Factura con signo de dólar -->
                      </div>
                      <a href="#" onclick="cargar_contenido('contenido_principal','reuniones/view_reuniones.php')" class="small-box-footer"><b>Ver Reuniones</b>&nbsp;<i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                  </div>
                  <!-- ./col -->
                  <div class="col-lg-3 col-6">
                    <!-- small box -->
                    <div class="small-box bg-dark">
                      <div class="inner">
                        <b>Total de Expedientes</b>
                        <h3 id="total_expedientes"><sup style="font-size: 20px"></sup></h3>

                      </div>
                      <div class="icon">
                        <i class="fas fas fa-folder-open"></i> <!-- Factura con signo de dólar -->
                      </div>
                      <a href="#" onclick="cargar_contenido('contenido_principal','expedientes/view_expedientes.php')" class="small-box-footer"><b>Ver Expedientes</b>&nbsp;<i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                  </div>
                  <!-- ./col -->
                  <div class="col-lg-3 col-6">
                    <!-- small box -->
                    <div class="small-box bg-info">
                      <div class="inner">

                        <b>Expedientes en Proceso</b>
                        <h3 id="total_expedientes_proceso"><sup style="font-size: 20px"></sup></h3>

                      </div>
                      <div class="icon">
                        <i class="fas fas fa-folder-open"></i> <!-- Factura con signo de dólar -->
                      </div>
                      <a href="#" onclick="cargar_contenido('contenido_principal','expedientes/view_expedientes.php')" class="small-box-footer"><b>Ver Expedientes</b>&nbsp;<i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                  </div>
                  <!-- ./col -->
                  <div class="col-lg-3 col-6">
                    <!-- small box -->
                    <div class="small-box bg-primary">
                      <div class="inner">
                        <b>Expedientes en Trámite</b>
                        <h3 id="total_expedientes_tramite"><sup style="font-size: 20px"></sup></h3>

                      </div>
                      <div class="icon">
                        <i class="fas fas fa-folder-open"></i> <!-- Factura con signo de dólar -->
                      </div>

                      <a href="#" onclick="cargar_contenido('contenido_principal','expedientes/view_expedientes.php')" class="small-box-footer"><b>Ver Expedientes</b>&nbsp;<i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                  </div>
                  <div class="col-lg-3 col-6">
                    <!-- small box -->
                    <div class="small-box bg-danger">
                      <div class="inner">
                        <b> Expedientes Observados</b>
                        <h3 id="total_expedientes_observados"><sup style="font-size: 20px"></sup></h3>

                      </div>
                      <div class="icon">
                        <i class="fas fas fa-folder-open"></i> <!-- Factura con signo de dólar -->
                      </div>
                      <a href="#" onclick="cargar_contenido('contenido_principal','expedientes/view_expedientes.php')" class="small-box-footer"><b>Ver Expedientes</b>&nbsp;<i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                  </div>
                  <!-- ./col -->
                  <div class="col-lg-3 col-6">
                    <!-- small box -->
                    <div class="small-box bg-success">
                      <div class="inner">
                        <b>Expedientes Finalizados</b>
                        <h3 id="total_expedientes_finalizados"><sup style="font-size: 20px"></sup></h3>

                      </div>
                      <div class="icon">
                        <i class="fas fas fa-folder-open"></i> <!-- Factura con signo de dólar -->
                      </div>
                      <a href="#" onclick="cargar_contenido('contenido_principal','expedientes/view_expedientes.php')" class="small-box-footer"><b>Ver Expedientes</b>&nbsp;<i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                  </div>
                  <!-- ./col -->
                  <div class="col-lg-3 col-6">
                    <!-- small box -->
                    <div class="small-box bg-warning">
                      <div class="inner">

                        <b>Total de Clientes</b>
                        <h3 id="total_clientes"><sup style="font-size: 20px"></sup></h3>

                      </div>
                      <div class="icon">
                        <i class="nav-icon fas fa-users"></i>
                      </div>
                      <a href="#" onclick="cargar_contenido('contenido_principal','clientes/view_clientes.php')" class="small-box-footer"><b>Ver Clientes</b>&nbsp; <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                  </div>
                  <!-- ./col -->
                  <div class="col-lg-3 col-6">
                    <!-- small box -->
                    <div class="small-box bg-success">
                      <div class="inner">
                        <b>Total de Reuniones</b>
                        <h3 id="total_reuniones"><sup style="font-size: 20px"></sup></h3>

                      </div>
                      <div class="icon">
                        <i class="fas fa-calendar-check"></i> <!-- Factura con signo de dólar -->
                      </div>

                      <a href="#" onclick="cargar_contenido('contenido_principal','reuniones/view_reuniones.php')" class="small-box-footer"><b>Ver Reuniones</b>&nbsp; <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                  </div>
                  <div class="col-lg-3 col-6">
                    <!-- small box -->
                    <hr>

                    <div class="small-box bg-success">
                      <div class="inner">

                        <b> Total de Ingresos Hoy</b>
                        <h3 id="total_ingresos_hoy"><sup style="font-size: 20px"></sup></h3>

                      </div>
                      <div class="icon">
                        <i class="nav-icon fas fa-coins"></i>
                      </div>
                      <a href="#" onclick="cargar_contenido('contenido_principal','ingresos/view_ingresos.php')" class="small-box-footer"><b>Ver Ingresos</b>&nbsp;<i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                  </div>
                  <!-- ./col -->
                  <div class="col-lg-3 col-6">
                    <!-- small box -->
                    <hr>
                    <div class="small-box bg-danger">
                      <div class="inner">
                        <b>Total de Gastos Hoy</b>
                        <h3 id="total_gastos_hoy"><sup style="font-size: 20px"></sup></h3>

                      </div>
                      <div class="icon">
                        <i class="fas fas fas fa-coins"></i> <!-- Factura con signo de dólar -->
                      </div>
                      <a href="#" onclick="cargar_contenido('contenido_principal','gastos/view_gastos.php')" class="small-box-footer"><b>Ver Gastos</b>&nbsp;<i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                  </div>
                  <!-- ./col -->
                  <div class="col-lg-3 col-6">
                    <!-- small box -->
                    <hr>
                    <div class="small-box bg-success">
                      <div class="inner">

                        <b>Total de Ingresos Mes Actual</b>
                        <h3 id="total_ingresos_mes_actual"><sup style="font-size: 20px"></sup></h3>

                      </div>
                      <div class="icon">
                        <i class="nav-icon fas fa-coins"></i>
                      </div>
                      <a href="#" onclick="cargar_contenido('contenido_principal','ingresos/view_ingresos.php')" class="small-box-footer"><b>Ver Ingresos</b>&nbsp;<i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                  </div>
                  <!-- ./col -->
                  <div class="col-lg-3 col-6">
                    <!-- small box -->
                    <hr>
                    <div class="small-box bg-danger">
                      <div class="inner">
                        <b>Total de Gastos Mes Actual</b>
                        <h3 id="total_gastos_mes_actual"><sup style="font-size: 20px"></sup></h3>

                      </div>
                      <div class="icon">
                        <i class="fas fa-coins"></i> <!-- Factura con signo de dólar -->
                      </div>

                      <a href="#" onclick="cargar_contenido('contenido_principal','gastos/view_gastos.php')" class="small-box-footer"><b>Ver Gastos</b>&nbsp;<i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>



        <!-- /.content -->

        <!-- /.content-wrapper -->
      <?php
      }
      ?>
    </div>
    <!-- Control Sidebar -->
    <aside class="control-sidebar control-sidebar-dark">
      <!-- Control sidebar content goes here -->
      <div class="p-3">
        <h5>Title</h5>
        <p>Sidebar content</p>
      </div>
    </aside>
    <!-- /.control-sidebar -->

    <!-- Main Footer -->
    <footer class="main-footer">
      <!-- To the right -->
      <div class="float-right d-none d-sm-inline">
        <em>Versión 1.0.0</em>
      </div>
      <!-- Default to the left -->
      <strong>Copyright &copy; 2025 - <a href="https://samicnestorkirchner.org/" target="_blank"><em>INCOCAT S.R.L.</em></a></strong>
    </footer>
  </div>
  <!-- ./wrapper -->
  <!-- MODAL EDITAR HORARIO -->




  <!-- REQUIRED SCRIPTS -->
  <script>
    function cargar_contenido(id, vista) {
      $("#" + id).load(vista);
    }
    var idioma_espanol = {
      select: {
        rows: "%d fila seleccionada"
      },
      "sProcessing": "Procesando...",
      "sLengthMenu": "Mostrar _MENU_ registros",
      "sZeroRecords": "No se encontraron resultados",
      "sEmptyTable": "Ning&uacute;n dato disponible en esta tabla",
      "sInfo": "Registros del (_START_ al _END_) total de _TOTAL_ registros",
      "sInfoEmpty": "Registros del (0 al 0) total de 0 registros",
      "sInfoFiltered": "(filtrado de un total de _MAX_ registros)",
      "sInfoPostFix": "",
      "sSearch": "Buscar:",
      "sUrl": "",
      "sInfoThousands": ",",
      "sLoadingRecords": "<b>No se encontraron datos</b>",
      "oPaginate": {
        "sFirst": "Primero",
        "sLast": "Último",
        "sNext": "Siguiente",
        "sPrevious": "Atras"
      },
      "oAria": {
        "sSortAscending": ": Activar para ordenar la columna de manera ascendente",
        "sSortDescending": ": Activar para ordenar la columna de manera descendente"
      }
    }

    function sololetras(e) {
      key = e.keyCode || e.which;

      teclado = String.fromCharCode(key).toLowerCase();

      letras = "qwertyuiopasdfghjklñzxcvbnmáéíóúAEIOUÁÉÍÓÚ.-:/?¡!@#$%&/()=+*^¨{}[]´`´ ";

      especiales = "8-37-38-46-164";

      teclado_especial = false;

      for (var i in especiales) {
        if (key == especiales[i]) {
          teclado_especial = true;
          break;
        }
      }

      if (letras.indexOf(teclado) == -1 && !teclado_especial) {
        return false;
      }
    }


    function soloNumeros(e) {
      tecla = (document.all) ? e.keyCode : e.which;
      if (tecla == 8) {
        return true;
      }
      // Patron de entrada, en este caso solo acepta numeros
      patron = /[0-9]/;
      tecla_final = String.fromCharCode(tecla);
      return patron.test(tecla_final);
    }



    ///////VALIDAR EMAIL
    function validar_email(email) {
      var regex = /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
      return regex.test(email) ? true : false;
    }
  </script>
  <!-- jQuery -->
  <script src="../plantilla/plugins//jquery/jquery.min.js"></script>
  <!-- Bootstrap 4 -->
  <script src="../plantilla/plugins//bootstrap/js/bootstrap.bundle.min.js"></script>
  <!-- AdminLTE App -->
  <script src="../plantilla/dist/js/adminlte.min.js"></script>


  <script src="../utilitario/DataTables/datatables.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
  <script src="../js/console_usuario.js?rev=<?php echo time(); ?>"></script>
  <script src="../js/console_empresa.js?rev=<?php echo time(); ?>"></script>

</body>

</html>
<script>
  $(document).ready(function() {
    listar_empresa();
    Total_reuniones_hoy();
    Total_reuniones();
    Total_expedientes();
    Total_expedientes_proceso();
    Total_expedientes_tramite();
    Total_expedientes_observados();
    Total_expedientes_finalizados();
    Total_clientes();
    Total_ingresos_hoy();
    Total_gastos_hoy();
    Total_ingresos_mensual();
    Total_gastos_mensual();
  });
</script>

<style>
  /* Color de fondo principal del aside con degradado */
  .main-sidebar {
    background: linear-gradient(135deg, #023D77, #0266C8) !important;
    color: white !important;
    /* Texto en blanco */
  }

  /* Eliminar cualquier color heredado del tema dark */
  .sidebar-dark-primary {
    background: linear-gradient(135deg, #023D77, #0266C8) !important;
    color: white !important;
  }

  /* Asegurar que la elevación no afecte el color */
  .elevation-4 {
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1) !important;
  }
</style>

<style>
  /* Estilos para la tabla con bordes redondeados */
  .table-bordered {
    border: 1px solid #dee2e6;
    border-radius: 10px;
  }

  /* Bordes redondeados para las celdas de las esquinas */
  .table-bordered thead th:first-child {
    border-top-left-radius: 10px;
  }

  .table-bordered thead th:last-child {
    border-top-right-radius: 10px;
  }

  .table-bordered tbody tr:last-child td:first-child {
    border-bottom-left-radius: 10px;
  }

  .table-bordered tbody tr:last-child td:last-child {
    border-bottom-right-radius: 10px;
  }

  /* Ajustes para mantener los bordes consistentes */
  .table-bordered th,
  .table-bordered td {
    border: 1px solid #dee2e6;
  }

  /* Estilos para el card contenedor */
  .card {
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
  }

  /* Ajuste para el responsive */
  .table-responsive {
    border-radius: 10px;
    overflow: hidden;
  }
</style>
<style>
  .carousel {
    position: relative;
    overflow: hidden;
  }

  .carousel-item {
    transition: transform 1s ease, opacity 1s ease;
  }


  .carousel-item.active {
    display: block;
  }
</style>
<script>
  // Iniciar el cambio automático de imagen cada 5 segundos
  var carousel = document.querySelector('#photoCarousel');
  var nextBtn = document.querySelector('#nextBtn');
  var prevBtn = document.querySelector('#prevBtn');

  // Configurar el intervalo para cambiar automáticamente cada 5 segundos
  setInterval(function() {
    $(carousel).carousel('next');
  }, 5000); // 5000 milisegundos = 5 segundos

  // Botón "Siguiente"
  nextBtn.addEventListener('click', function() {
    $(carousel).carousel('next');
  });

  // Botón "Anterior"
  prevBtn.addEventListener('click', function() {
    $(carousel).carousel('prev');
  });
</script>