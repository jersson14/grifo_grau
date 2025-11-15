<script src="../js/console_turnos.js?rev=<?php echo time(); ?>"></script>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-clock"></i> <b>ABRIR TURNO</b></h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="../view/index.php">Inicio</a></li>
                    <li class="breadcrumb-item active">Abrir Turno</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="content">
    <div class="container-fluid">
        
        <!-- VERIFICAR SI YA HAY TURNO ABIERTO -->
        <div id="alerta_turno_abierto" style="display:none">
            <div class="alert alert-warning">
                <h5><i class="icon fas fa-exclamation-triangle"></i> Ya tienes un turno abierto</h5>
                Debes cerrar tu turno actual antes de abrir uno nuevo.
                <br><br>
                <button class="btn btn-primary" onclick="cargar_contenido('contenido_principal','turnos/view_cerrar_turno.php')">
                    <i class="fas fa-tasks"></i> Ir a Mi Turno
                </button>
            </div>
        </div>

        <!-- FORMULARIO ABRIR TURNO -->
        <div id="formulario_abrir_turno">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header" style="background: linear-gradient(135deg, #023D77, #0266C8)">
                            <h3 class="card-title" style="color:white"><i class="fas fa-plus-circle"></i> Nuevo Turno</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3 form-group">
                                    <label>Número de Documento</label>
                                    <input type="text" class="form-control" id="txt_numero_documento" readonly style="background-color:#f0f0f0; font-weight:bold">
                                </div>
                                <div class="col-md-3 form-group">
                                    <label>Fecha del Turno <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="txt_fecha_turno">
                                </div>
                                <div class="col-md-3 form-group">
                                    <label>Tipo de Turno <span class="text-danger">*</span></label>
                                    <select class="form-control" id="txt_tipo_turno">
                                        <option value="">-- Seleccione --</option>
                                        <option value="DIA">DÍA (07:00 - 19:00)</option>
                                        <option value="NOCHE">NOCHE (19:00 - 07:00)</option>
                                    </select>
                                </div>
                                <div class="col-md-3 form-group">
                                    <label>Grifero</label>
                                    <input type="text" class="form-control" id="txt_grifero" readonly style="background-color:#f0f0f0">
                                </div>
                                <div class="col-md-3 form-group">
                                    <label>Hora Inicio <span class="text-danger">*</span></label>
                                    <input type="time" class="form-control" id="txt_hora_inicio">
                                </div>
                                <div class="col-md-3 form-group">
                                    <label>Hora Fin <span class="text-danger">*</span></label>
                                    <input type="time" class="form-control" id="txt_hora_fin">
                                </div>
                            </div>

                            <hr>

                            <h5 class="text-center" style="background-color:#023D77; color:white; padding:10px; border-radius:5px;">
                                <i class="fas fa-gas-pump"></i> LECTURAS INICIALES DE SURTIDORES
                            </h5>

                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> Las lecturas iniciales se cargarán automáticamente desde las lecturas actuales de cada surtidor.
                            </div>

                            <!-- MÁQUINA 1 -->
                            <div class="card card-primary">
                                <div class="card-header">
                                    <h3 class="card-title"><i class="fas fa-cogs"></i> MÁQUINA 1</h3>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm table-bordered">
                                        <thead style="background-color:#007bff; color:white">
                                            <tr>
                                                <th>Código</th>
                                                <th>Producto</th>
                                                <th>Lectura Inicial</th>
                                                <th>Precio (S/.)</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tabla_lecturas_maquina_1">
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- MÁQUINA 2 -->
                            <div class="card card-success">
                                <div class="card-header">
                                    <h3 class="card-title"><i class="fas fa-cogs"></i> MÁQUINA 2</h3>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm table-bordered">
                                        <thead style="background-color:#28a745; color:white">
                                            <tr>
                                                <th>Código</th>
                                                <th>Producto</th>
                                                <th>Lectura Inicial</th>
                                                <th>Precio (S/.)</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tabla_lecturas_maquina_2">
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12 text-center">
                                    <button class="btn btn-success btn-lg" onclick="Abrir_Turno()">
                                        <i class="fas fa-check-circle"></i> Abrir Turno
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    Verificar_Turno_Abierto();
    Cargar_Numero_Documento();
    Cargar_Lecturas_Iniciales();
    
    // Establecer fecha actual
    var hoy = new Date().toISOString().split('T')[0];
    $('#txt_fecha_turno').val(hoy);
    
    // Cargar nombre del grifero
    $('#txt_grifero').val($('#txtprincipalusu').val());
    
    // Establecer horas por defecto según el turno
    $('#txt_tipo_turno').change(function() {
        if ($(this).val() == 'DIA') {
            $('#txt_hora_inicio').val('07:00');
            $('#txt_hora_fin').val('19:00');
        } else if ($(this).val() == 'NOCHE') {
            $('#txt_hora_inicio').val('19:00');
            $('#txt_hora_fin').val('07:00');
        }
    });
});
</script>
