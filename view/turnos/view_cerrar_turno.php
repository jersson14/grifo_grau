<script src="../js/console_turnos.js?rev=<?php echo time(); ?>"></script>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-tasks"></i> <b>MI TURNO ACTUAL</b></h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="../view/index.php">Inicio</a></li>
                    <li class="breadcrumb-item active">Mi Turno</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="content">
    <div class="container-fluid">
        
        <!-- SIN TURNO ABIERTO -->
        <div id="sin_turno_abierto" style="display:none">
            <div class="alert alert-info">
                <h5><i class="icon fas fa-info-circle"></i> No tienes un turno abierto</h5>
                Debes abrir un turno para comenzar a registrar ventas.
                <br><br>
                <button class="btn btn-primary" onclick="cargar_contenido('contenido_principal','turnos/view_abrir_turno.php')">
                    <i class="fas fa-plus-circle"></i> Abrir Turno
                </button>
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
                            <h3 class="card-title"><i class="fas fa-info-circle"></i> Información del Turno</h3>
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
                                <div class="col-md-3">
                                    <p><strong>Horario:</strong><br><span id="info_hora_inicio"></span> - <span id="info_hora_fin"></span></p>
                                </div>
                                <div class="col-md-3">
                                    <p><strong>Estado:</strong><br><span class="badge badge-warning">ABIERTO</span></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- LECTURAS DE SURTIDORES -->
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header" style="background: linear-gradient(135deg, #023D77, #0266C8)">
                            <h3 class="card-title" style="color:white"><i class="fas fa-gas-pump"></i> Lecturas de Surtidores</h3>
                        </div>
                        <div class="card-body">
                            
                            <!-- MÁQUINA 1 -->
                            <h5 style="background-color:#007bff; color:white; padding:10px; border-radius:5px;">
                                <i class="fas fa-cogs"></i> MÁQUINA 1
                            </h5>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered table-hover">
                                    <thead style="background-color:#007bff; color:white">
                                        <tr>
                                            <th>Código</th>
                                            <th>Producto</th>
                                            <th>Lectura Anterior</th>
                                            <th>Lectura Actual</th>
                                            <th>Galones Vendidos</th>
                                            <th>Precio S/.</th>
                                            <th>Total S/.</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tabla_lecturas_cerrar_maquina_1">
                                    </tbody>
                                </table>
                            </div>

                            <br>

                            <!-- MÁQUINA 2 -->
                            <h5 style="background-color:#28a745; color:white; padding:10px; border-radius:5px;">
                                <i class="fas fa-cogs"></i> MÁQUINA 2
                            </h5>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered table-hover">
                                    <thead style="background-color:#28a745; color:white">
                                        <tr>
                                            <th>Código</th>
                                            <th>Producto</th>
                                            <th>Lectura Anterior</th>
                                            <th>Lectura Actual</th>
                                            <th>Galones Vendidos</th>
                                            <th>Precio S/.</th>
                                            <th>Total S/.</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tabla_lecturas_cerrar_maquina_2">
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

            <!-- MÉTODOS DE PAGO -->
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-warning">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-money-bill-wave"></i> Métodos de Pago</h3>
                            <div class="card-tools">
                                <button class="btn btn-success btn-sm" onclick="Abrir_Modal_Agregar_Pago()">
                                    <i class="fas fa-plus"></i> Agregar Pago
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <table id="tabla_pagos_turno" class="table table-sm table-bordered table-hover" style="width:100%">
                                <thead style="background-color:#ffc107; color:black">
                                    <tr>
                                        <th>Tipo de Pago</th>
                                        <th>Código Operación</th>
                                        <th>Monto</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- VENTAS A CRÉDITO -->
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-danger">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-credit-card"></i> Ventas a Crédito</h3>
                            <div class="card-tools">
                                <button class="btn btn-success btn-sm" onclick="Abrir_Modal_Agregar_Credito()">
                                    <i class="fas fa-plus"></i> Agregar Crédito
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <table id="tabla_creditos_turno" class="table table-sm table-bordered table-hover" style="width:100%">
                                <thead style="background-color:#dc3545; color:white">
                                    <tr>
                                        <th>Cliente</th>
                                        <th>N° Vale</th>
                                        <th>Monto</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- OTROS CONCEPTOS -->
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
                                <tr style="background-color:#28a745; color:white; font-size:18px">
                                    <td><strong>FALTANTE/SOBRANTE:</strong></td>
                                    <td class="text-right"><strong><span id="cuadre_faltante">S/. 0.00</span></strong></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- BOTÓN CERRAR TURNO -->
            <div class="row">
                <div class="col-md-12 text-center">
                    <button class="btn btn-danger btn-lg" onclick="Cerrar_Turno_Final()">
                        <i class="fas fa-check-circle"></i> CERRAR TURNO
                    </button>
                </div>
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

<!-- MODAL AGREGAR CRÉDITO -->
<div class="modal fade" id="modal_agregar_credito" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #dc3545, #c82333); color:white">
                <h5 class="modal-title"><i class="fas fa-credit-card"></i> Agregar Crédito</h5>
                <button type="button" class="close" data-dismiss="modal" style="color:white">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6 form-group">
                        <label>Cliente <span class="text-danger">*</span></label>
                        <select class="js-example-basic-single form-control" id="txt_cliente_credito" style="width:100%">
                            <option value="">-- Seleccione --</option>
                        </select>
                    </div>
                    <div class="col-md-6 form-group">
                        <label>N° de Vale <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="txt_numero_vale" placeholder="Número de vale">
                    </div>
                    <div class="col-md-6 form-group">
                        <label>Monto (S/.) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" class="form-control" id="txt_monto_credito" placeholder="0.00">
                    </div>
                    <div class="col-md-6 form-group">
                        <label>Fecha de Vencimiento</label>
                        <input type="date" class="form-control" id="txt_fecha_vencimiento">
                    </div>
                    <div class="col-12 form-group">
                        <label>Observaciones</label>
                        <textarea class="form-control" id="txt_observaciones_credito" rows="2" placeholder="Observaciones opcionales"></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times"></i> Cerrar</button>
                <button type="button" class="btn btn-danger" onclick="Agregar_Credito()"><i class="fas fa-save"></i> Agregar</button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    Cargar_Turno_Actual();
});
</script>
