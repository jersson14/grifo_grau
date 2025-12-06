<script src="../js/console_turnos.js?rev=<?php echo time(); ?>"></script>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-clipboard-check"></i> <b>REGISTRAR TURNO</b></h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="../view/index.php">Inicio</a></li>
                    <li class="breadcrumb-item active">Registrar Turno</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="content">
    <div class="container-fluid">
        
        <!-- FORMULARIO REGISTRAR TURNO -->
        <div id="formulario_registrar_turno">
            <input type="hidden" id="txt_id_reporte">
            
            <!-- PASO 1: INFORMACIÓN DEL TURNO -->
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-info-circle"></i> <strong>PASO 1:</strong> INFORMACIÓN DEL TURNO</h3>
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
                                    <label>Grifero <span class="text-danger">*</span></label>
                                    <select class="form-control" id="txt_grifero">
                                        <option value="">-- Seleccione Grifero --</option>
                                    </select>
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
                        </div>
                    </div>
                </div>
            </div>

            <!-- PASO 2: LECTURAS DE SURTIDORES -->
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-success">
                        <div class="card-header" data-card-widget="collapse">
                            <h3 class="card-title"><i class="fas fa-gas-pump"></i> <strong>PASO 2:</strong> LECTURAS DE SURTIDORES</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            
                            <!-- MÁQUINA 1 -->
                            <h5 style="background-color:#28a745; color:white; padding:10px; border-radius:5px;">
                                <i class="fas fa-cogs"></i> MÁQUINA 1
                            </h5>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered table-hover">
                                    <thead style="background-color:#28a745; color:white">
                                        <tr>
                                            <th>Código</th>
                                            <th>Producto</th>
                                            <th>Lectura Inicial</th>
                                            <th>Lectura Final</th>
                                            <th>Galones Vendidos</th>
                                            <th>Precio S/.</th>
                                            <th>Total S/.</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tabla_lecturas_maquina_1">
                                    </tbody>
                                </table>
                            </div>

                            <br>

                            <!-- MÁQUINA 2 -->
                            <h5 style="background-color:#17a2b8; color:white; padding:10px; border-radius:5px;">
                                <i class="fas fa-cogs"></i> MÁQUINA 2
                            </h5>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered table-hover">
                                    <thead style="background-color:#17a2b8; color:white">
                                        <tr>
                                            <th>Código</th>
                                            <th>Producto</th>
                                            <th>Lectura Inicial</th>
                                            <th>Lectura Final</th>
                                            <th>Galones Vendidos</th>
                                            <th>Precio S/.</th>
                                            <th>Total S/.</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tabla_lecturas_maquina_2">
                                    </tbody>
                                </table>
                            </div>

                            <!-- RESUMEN POR COMBUSTIBLE -->
                            <div class="row mt-3">
                                <div class="col-md-12">
                                    <h5 style="background-color:#6c757d; color:white; padding:10px; border-radius:5px;">
                                        <i class="fas fa-chart-bar"></i> RESUMEN POR COMBUSTIBLE
                                    </h5>
                                </div>
                                <div class="col-md-4">
                                    <div class="info-box bg-dark">
                                        <span class="info-box-icon"><i class="fas fa-gas-pump"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">DIESEL</span>
                                            <span class="info-box-number" id="total_diesel">S/. 0.00</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="info-box bg-info">
                                        <span class="info-box-icon"><i class="fas fa-gas-pump"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">REGULAR</span>
                                            <span class="info-box-number" id="total_regular">S/. 0.00</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="info-box bg-warning">
                                        <span class="info-box-icon"><i class="fas fa-gas-pump"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">PREMIUM</span>
                                            <span class="info-box-number" id="total_premium">S/. 0.00</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="info-box bg-success">
                                        <span class="info-box-icon"><i class="fas fa-dollar-sign"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">TOTAL VENTAS</span>
                                            <span class="info-box-number" id="total_ventas">S/. 0.00</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- PASO 3: MÉTODOS DE PAGO -->
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-warning">
                        <div class="card-header" data-card-widget="collapse">
                            <h3 class="card-title"><i class="fas fa-money-bill-wave"></i> <strong>PASO 3:</strong> MÉTODOS DE PAGO (Opcional)</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <button class="btn btn-success btn-sm mb-2" onclick="Agregar_Fila_Pago_Registro()">
                                <i class="fas fa-plus"></i> Agregar Fila
                            </button>
                            <div class="table-responsive">
                            <table id="tabla_pagos_registro" class="table table-sm table-bordered table-hover">
                                <thead style="background-color:#ffc107; color:black">
                                    <tr>
                                        <th width="30%">Tipo de Pago</th>
                                        <th width="25%">Código Operación</th>
                                        <th width="20%">Monto (S/.)</th>
                                        <th width="25%">Observaciones</th>
                                        <th width="10%">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody_pagos_registro">
                                    <!-- Filas se agregarán dinámicamente -->
                                </tbody>
                            </table>
                            </div>
                            <div class="alert alert-info mt-2">
                                <i class="fas fa-info-circle"></i> <strong>Tip:</strong> Escribe directamente en las celdas. Los datos se guardarán al registrar el turno.
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- PASO 4: VENTAS A CRÉDITO -->
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-danger">
                        <div class="card-header" data-card-widget="collapse">
                            <h3 class="card-title"><i class="fas fa-credit-card"></i> <strong>PASO 4:</strong> VENTAS A CRÉDITO (Opcional)</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <button class="btn btn-success btn-sm mb-2" onclick="Agregar_Fila_Credito_Registro()">
                                <i class="fas fa-plus"></i> Agregar Fila
                            </button>
                            <div class="table-responsive">
                            <table id="tabla_creditos_registro" class="table table-sm table-bordered table-hover">
                                <thead style="background-color:#dc3545; color:white">
                                    <tr>
                                        <th width="30%">Cliente</th>
                                        <th width="20%">N° Vale</th>
                                        <th width="20%">Monto (S/.)</th>
                                        <th width="20%">Fecha Vencimiento</th>
                                        <th width="10%">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody_creditos_registro">
                                    <!-- Filas se agregarán dinámicamente -->
                                </tbody>
                            </table>
                            </div>
                            <div class="alert alert-info mt-2">
                                <i class="fas fa-info-circle"></i> <strong>Tip:</strong> Escribe directamente en las celdas. Los datos se guardarán al registrar el turno.
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- PASO 5: OTROS CONCEPTOS Y CUADRE -->
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-secondary">
                        <div class="card-header" data-card-widget="collapse">
                            <h3 class="card-title"><i class="fas fa-calculator"></i> <strong>PASO 5:</strong> OTROS CONCEPTOS Y CUADRE DE CAJA</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header" style="background-color:#6c757d; color:white">
                            <h3 class="card-title"><i class="fas fa-minus-circle"></i> Otros Conceptos</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label>Descuentos (S/.)</label>
                                <input type="number" step="0.01" class="form-control" id="txt_descuentos" value="0" placeholder="0.00">
                            </div>
                            <div class="form-group">
                                <label>Otros Gastos (S/.)</label>
                                <input type="number" step="0.01" class="form-control" id="txt_otros_gastos" value="0" placeholder="0.00">
                            </div>
                            <div class="form-group">
                                <label>Efectivo (S/.)</label>
                                <input type="number" step="0.01" class="form-control" id="txt_monto_efectivo" value="0" placeholder="0.00">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CUADRE DE CAJA -->
                <div class="col-md-6">
                    <div class="card card-success">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-calculator"></i> Cuadre de Caja</h3>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                            <table class="table table-sm">
                                <tr>
                                    <td><strong>Total Ventas:</strong></td>
                                    <td class="text-right"><span id="cuadre_total_ventas">S/. 0.00</span></td>
                                </tr>
                                <tr>
                                    <td><strong>Total Pagos:</strong></td>
                                    <td class="text-right"><span id="cuadre_total_pagos">S/. 0.00</span></td>
                                </tr>
                                <tr>
                                    <td><strong>Total Créditos:</strong></td>
                                    <td class="text-right"><span id="cuadre_total_creditos">S/. 0.00</span></td>
                                </tr>
                                <tr>
                                    <td><strong>Descuentos:</strong></td>
                                    <td class="text-right"><span id="cuadre_descuentos">S/. 0.00</span></td>
                                </tr>
                                <tr>
                                    <td><strong>Otros Gastos:</strong></td>
                                    <td class="text-right"><span id="cuadre_otros_gastos">S/. 0.00</span></td>
                                </tr>
                                <tr>
                                    <td><strong>Efectivo:</strong></td>
                                    <td class="text-right"><span id="cuadre_monto_efectivo">S/. 0.00</span></td>
                                </tr>
                                <tr style="background-color:#28a745; color:white; font-size:18px">
                                    <td><strong>FALTANTE/SOBRANTE:</strong></td>
                                    <td class="text-right"><strong><span id="cuadre_faltante">S/. 0.00</span></strong></td>
                                </tr>
                            </table>
                            </div>
                        </div>
                        </div>
                    </div>
                </div>
            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- BOTÓN REGISTRAR TURNO -->
            <div class="row">
                <div class="col-md-12 text-center mb-4">
                    <button class="btn btn-success btn-lg" onclick="Registrar_Turno_Completo()" style="font-size:20px; padding:15px 50px; box-shadow: 0 4px 6px rgba(0,0,0,0.2);">
                        <i class="fas fa-save"></i> REGISTRAR TURNO
                    </button>
                    <br><br>
                    <small class="text-muted">Al registrar el turno se guardarán todos los datos y se actualizarán las lecturas de los surtidores</small>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    Cargar_Numero_Documento();
    Cargar_Lecturas_Para_Registro();
    Cargar_Griferos();
    
    // Establecer fecha actual
    var hoy = new Date().toISOString().split('T')[0];
    $('#txt_fecha_turno').val(hoy);
    
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
    
    // Agregar 3 filas iniciales de pagos y créditos
    for (var i = 0; i < 3; i++) {
        Agregar_Fila_Pago_Registro();
        Agregar_Fila_Credito_Registro();
    }
});
</script>
