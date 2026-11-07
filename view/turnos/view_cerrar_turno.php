<?php require_once __DIR__ . '/../../controller/_bootstrap.php'; ?>
<script src="../js/console_turnos.js?rev=<?php echo time(); ?>"></script>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-lock"></i> <b>CERRAR TURNO</b></h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="../view/index.php">Inicio</a></li>
                    <li class="breadcrumb-item active">Cerrar turno</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="content">
    <div class="container-fluid">
        
        <!-- SIN TURNO ABIERTO -->
        <div id="sin_turno_abierto" style="display:none">
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="fas fa-door-open fa-2x text-muted mb-3" aria-hidden="true"></i>
                    <h2 class="h5 font-weight-bold">No hay un turno abierto</h2>
                    <p class="text-muted mx-auto mb-4" style="max-width: 32rem">Para cerrar un turno primero hay que abrirlo. Si el turno ya terminó y no se abrió en el sistema, regístrelo completo de una vez.</p>
                    <button type="button" class="btn btn-primary mb-2" onclick="cargar_contenido('contenido_principal','turnos/view_abrir_turno.php')"><i class="fas fa-plus-circle" aria-hidden="true"></i> Abrir turno</button>
                    <button type="button" class="btn btn-outline-secondary mb-2" onclick="cargar_contenido('contenido_principal','turnos/view_registrar_turno.php')"><i class="fas fa-clipboard-check" aria-hidden="true"></i> Registrar turno completo</button>
                </div>
            </div>
        </div>

        <!-- FORMULARIO CERRAR TURNO -->
        <div id="formulario_cerrar_turno" style="display:none">
            <input type="hidden" id="txt_id_reporte">
            
            <!-- INFORMACIÓN DEL TURNO -->
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title g-titulo-paso"><span class="g-paso" aria-hidden="true">1</span> <span class="sr-only">Paso 1: </span>Turno abierto</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-2">
                                    <p><strong>N° Documento:</strong><br><span id="info_numero_documento" class="badge badge-primary" style="font-size:14px"></span></p>
                                </div>
                                <div class="col-md-2">
                                    <p><strong>Fecha:</strong><br><span id="info_fecha"></span></p>
                                </div>
                                <div class="col-md-2">
                                    <p><strong>Turno:</strong><br><span id="info_turno" class="badge badge-info"></span></p>
                                </div>
                                <div class="col-md-2">
                                    <p><strong>Horario:</strong><br><span id="info_hora_inicio"></span> - <span id="info_hora_fin"></span></p>
                                </div>
                                <div class="col-md-2">
                                    <p><strong>Grifero:</strong><br><span id="info_grifero" class="badge badge-success"></span></p>
                                </div>
                                <div class="col-md-2">
                                    <p><strong>Estado:</strong><br><span class="badge badge-warning">ABIERTO</span></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- PASO 2: LECTURAS DE SURTIDORES -->
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-primary">
                        <div class="card-header" data-card-widget="collapse">
                            <h3 class="card-title g-titulo-paso"><span class="g-paso" aria-hidden="true">2</span> <span class="sr-only">Paso 2: </span>Lecturas finales de surtidores</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            
                            <!-- Una sección por máquina, generada desde los surtidores activos -->
                            <div id="maquinas_lecturas_cerrar"></div>

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
                            <h3 class="card-title g-titulo-paso"><span class="g-paso" aria-hidden="true">3</span> <span class="sr-only">Paso 3: </span>Métodos de pago <small>(opcional)</small></h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <button class="btn btn-success btn-sm mb-2" onclick="Agregar_Fila_Pago()">
                                <i class="fas fa-plus"></i> Agregar Fila
                            </button>
                            <div class="table-responsive">
                            <table id="tabla_pagos_turno_editable" class="table table-sm table-bordered table-hover">
                                <thead style="background-color:#ffc107; color:black">
                                    <tr>
                                        <th width="30%">Tipo de Pago</th>
                                        <th width="25%">Código Operación</th>
                                        <th width="20%">Monto (S/.)</th>
                                        <th width="25%">Observaciones</th>
                                        <th width="10%">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody_pagos_editable">
                                    <!-- Filas se agregarán dinámicamente -->
                                </tbody>
                            </table>
                            </div>
                            <div class="alert alert-info mt-2">
                                <i class="fas fa-info-circle"></i> <strong>Tip:</strong> Escribe directamente en las celdas. Los datos se guardan automáticamente.
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
                            <h3 class="card-title g-titulo-paso"><span class="g-paso" aria-hidden="true">4</span> <span class="sr-only">Paso 4: </span>Ventas a crédito <small>(opcional)</small></h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <button class="btn btn-success btn-sm mb-2" onclick="Agregar_Fila_Credito()">
                                <i class="fas fa-plus"></i> Agregar Fila
                            </button>
                            <div class="table-responsive">
                            <table id="tabla_creditos_turno_editable" class="table table-sm table-bordered table-hover">
                                <thead style="background-color:#dc3545; color:white">
                                    <tr>
                                        <th width="30%">Cliente</th>
                                        <th width="20%">N° Vale</th>
                                        <th width="20%">Monto (S/.)</th>
                                        <th width="20%">Fecha Vencimiento</th>
                                        <th width="10%">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody_creditos_editable">
                                    <!-- Filas se agregarán dinámicamente -->
                                </tbody>
                            </table>
                            </div>
                            <div class="alert alert-info mt-2">
                                <i class="fas fa-info-circle"></i> <strong>Tip:</strong> Escribe directamente en las celdas. Los datos se guardan automáticamente.
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
                            <h3 class="card-title g-titulo-paso"><span class="g-paso" aria-hidden="true">5</span> <span class="sr-only">Paso 5: </span>Otros conceptos y cuadre de caja</h3>
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

            <p class="text-muted text-center small mb-3">Al cerrar el turno se guardan todos los datos y se actualizan las lecturas de los surtidores.</p>

            <!-- Barra de cuadre: siempre a la vista mientras se completa el cierre -->
            <div class="g-barra-cuadre" role="region" aria-label="Cuadre del turno">
                <div class="g-barra-dato">
                    <span class="g-barra-etiqueta">Total ventas</span>
                    <span class="g-cifra" id="barra_total">S/ 0.00</span>
                </div>
                <div class="g-barra-dato">
                    <span class="g-barra-etiqueta">Cuadre</span>
                    <span class="g-estado-cuadre" id="barra_estado" data-estado="vacio" aria-live="polite">Faltan lecturas</span>
                </div>
                <button type="button" class="btn btn-danger btn-lg g-barra-boton" onclick="Cerrar_Turno_Final()"><i class="fas fa-lock" aria-hidden="true"></i> Cerrar turno</button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL AGREGAR PAGO -->
<div class="modal fade" id="modal_agregar_pago" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #ffc107, #ff9800); color:white">
                <h5 class="modal-title"><i class="fas fa-money-bill-wave"></i> Agregar Pago</h5>
                <button type="button" class="close" data-dismiss="modal" style="color:white">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-12 form-group">
                        <label>Tipo de Pago <span class="text-danger">*</span></label>
                        <select class="form-control" id="txt_tipo_pago">
                            <option value="">-- Seleccione --</option>
                        </select>
                    </div>
                    <div class="col-12 form-group">
                        <label>Código de Operación</label>
                        <input type="text" class="form-control" id="txt_codigo_operacion" placeholder="Código de operación">
                        <small class="text-muted">Obligatorio para Yape, BCP y Visa</small>
                    </div>
                    <div class="col-12 form-group">
                        <label>Monto (S/.) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" class="form-control" id="txt_monto_pago" placeholder="0.00">
                    </div>
                    <div class="col-12 form-group">
                        <label>Observaciones</label>
                        <textarea class="form-control" id="txt_observaciones_pago" rows="2" placeholder="Observaciones opcionales"></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times"></i> Cerrar</button>
                <button type="button" class="btn btn-warning" onclick="Agregar_Pago()"><i class="fas fa-save"></i> Agregar</button>
            </div>
        </div>
    </div>
</div>



<script>
$(document).ready(function() {
    Cargar_Turno_Actual();
    Iniciar_Barra_Cuadre();
});
</script>
