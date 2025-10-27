<script src="../js/console_reportes.js?rev=<?php echo time(); ?>"></script>

<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><b>MANTENIMIENTO DE EXPEDIENTES POR FECHA Y PROVINCIA</b></h1>
            </div><!-- /.col -->
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="../index.php">MENU</a></li>
                    <li class="breadcrumb-item active">EXPEDIENTES</li>
                </ol>
            </div><!-- /.col -->
        </div><!-- /.row -->
    </div><!-- /.container-fluid -->
</div>
<!-- /.content-header -->

<!-- Main content -->
<div class="content">
    <div class="container-fluid">
        <div class="row">
            <!-- /.col-md-6 -->
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="nav-icon fas fas fa-folder"></i>&nbsp;&nbsp;<b>Listado de Expedientes</b></h3>
                    </div>
                    <div class="table-responsive" style="text-align:left">
                        <div class="card-body">
                            <div class="row" style="border: 1px solid #ccc; padding: 15px; border-radius: 8px;">
                                <div class="col-2 form-group">
                                    <label for="">Fecha desde:</label>
                                    <input type="date" class="form-control" id="txt_fecha_desde">
                                </div>
                                <div class="col-2 form-group">
                                    <label for="">Fecha hasta:</label>
                                    <input type="date" class="form-control" id="txt_fecha_hasta">

                                </div>
                                <div class="col-3 form-group">
                                    <label for="">Departamento:</label>
                                    <select class="js-example-basic-single" id="select_depar_buscar" style="width:100%">
                                    </select>
                                </div>
                                <div class="col-3 form-group">
                                    <label for="">Provincia:</label>
                                    <select class="js-example-basic-single" id="select_provincia_buscar" style="width:100%">
                                    </select>
                                </div>
                                <div class="col-12 col-md-2" role="document">
                                    <label for="">&nbsp;</label><br>
                                    <button onclick="listar_expedientes_filtro()" class="btn btn-danger mr-2" style="width:100%" onclick><i class="fas fa-search mr-1"></i>Buscar expedientes</button>
                                </div>

                            </div>
                        </div>
                    </div>
                    <div class="row justify-content-center">
                        <div class="col-lg-10 text-center">
                            <div class="alert border rounded p-3 shadow-sm" style="background-color: #f8f9fa;">
                                <h5 class="mb-3" style="color:#0154A0; font-weight: bold;">🔔 Alertas por Días Transcurridos</h5>
                                <div class="d-flex justify-content-center flex-wrap gap-3">
                                    <span class="badge bg-dark p-2 px-3">
                                        <i class="fas fa-clock me-1"></i> 1 día a más - Negro
                                    </span>
                                    <span class="badge bg-success p-2 px-3">
                                        <i class="fas fa-clock me-1"></i> 10 días a más - Verde
                                    </span>
                                    <span class="badge bg-warning text-dark p-2 px-3">
                                        <i class="fas fa-clock me-1"></i> 20 días a más - Amarillo
                                    </span>
                                    <span class="badge bg-danger p-2 px-3">
                                        <i class="fas fa-clock me-1"></i> 30 días a más - Rojo
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row justify-content-center mt-4">
                        <div class="col-lg-10 text-center">
                            <div class="alert border rounded p-3 shadow-sm" style="background-color: #f8f9fa;">
                                <h5 class="mb-3" style="color:#0154A0; font-weight: bold;">📌 Leyenda de Estados</h5>
                                <div class="d-flex flex-column gap-2 align-items-start">
                                <div class="d-flex align-items-center gap-3">
                                        <span class="badge bg-info text-dark p-2 px-3">
                                            <i class="fas fa-spinner me-1"></i> EN PROCESO
                                        </span>
                                        <span class="text-start">
                                        <b>&nbsp; : Documentación en revisión interna y con documentos faltantes.</b>
                                        </span>
                                    </div>
                                    <div class="d-flex align-items-center gap-3">
                                        <span class="badge bg-primary p-2 px-3">
                                            <i class="fas fa-paper-plane me-1"></i> EN TRAMITE
                                        </span>
                                        <span class="text-start">
                                        <b>&nbsp; : Enviado para trámite con entidad correspondiente.</b>
                                        </span>
                                    </div>
                                    <div class="d-flex align-items-center gap-3">
                                        <span class="badge bg-danger p-2 px-3">
                                            <i class="fas fa-exclamation-triangle me-1"></i> OBSERVADO
                                        </span>
                                        <span class="text-start">
                                        <b>&nbsp; : Tiene observaciones por parte de la entidad correspondiente que deben ser subsanadas.</b>
                                        </span>
                                    </div>
                                    <div class="d-flex align-items-center gap-3">
                                        <span class="badge bg-warning text-dark p-2 px-3">
                                            <i class="fas fa-info-circle me-1"></i> REINGRESADO
                                        </span>
                                        <span class="text-start"> <!-- Aquí puedes escribir la explicación -->
                                        <b>&nbsp; : Se reenvía a la entidad correspondiente para su revisión.</b>
                                        </span>
                                    </div>
                                    <div class="d-flex align-items-center gap-3">
                                        <span class="badge bg-success p-2 px-3">
                                            <i class="fas fa-check-circle me-1"></i> FINALIZADO
                                        </span>
                                        <span class="text-start">
                                        <b>&nbsp; : Cuando se recibe la aprobación de la entidad correspondiente y se confirma la aprobación, el expediente se marca como finalizado.</b>
                                        </span>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive" style="text-align:center">
                        <div class="card-body">
                            <table id="tabla_expedientes" class="table table-striped table-bordered" style="width:100%">
                                <thead style="background-color:#023D77;color:#FFFFFF;">
                                    <tr>
                                        <th style="text-align:center">Nro Expediente.</th>
                                        <th style="text-align:center">Documento</th>
                                        <th style="text-align:center">Cliente</th>
                                        <th style="text-align:center">Departamento</th>
                                        <th style="text-align:center">Provincia</th>
                                        <th style="text-align:center">Servicio</th>
                                        <th style="text-align:center">Folios</th>
                                        <th style="text-align:center">Estado</th>
                                        <th style="text-align:center">Fecha de Registro</th>
                                        <th style="text-align:center">Días transcurrido</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>

                </div>
                <!-- /.col-md-6 -->
            </div>
            <!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content -->
    <!-- Modal -->




  <script>
  $(document).ready(function () {
    // Al iniciar, deshabilitamos el textarea
    $('#txt_motivo').prop('disabled', true);

    // Escuchamos cambios en el select
    $('#select_estado_edit').on('change', function () {
      const estado = $(this).val();

      if (estado === 'OBSERVADO') {
        $('#txt_motivo').prop('disabled', false);
      } else {
        $('#txt_motivo').prop('disabled', true).val('');
      }
    });
  });
</script>
    <script>
        $(document).ready(function() {
            $('.js-example-basic-single').select2({
    
            });
            listar_expedientes();
            Cargar_Select_Regiones();

        });


        $("#select_region_editar").change(function() {
            var id = $("#select_region_editar").val();
            Cargar_Select_Provincia(id);
        });


    </script>