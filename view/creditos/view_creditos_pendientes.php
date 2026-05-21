<script src="../js/console_creditos.js?rev=<?php echo time(); ?>"></script>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-credit-card"></i> <b>CRÉDITOS PENDIENTES</b></h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="../view/index.php">Inicio</a></li>
                    <li class="breadcrumb-item active">Créditos Pendientes</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="content">
    <div class="container-fluid">
        
        <!-- RESUMEN DE CRÉDITOS -->
        <div class="row">
            <div class="col-md-3">
                <div class="info-box bg-warning">
                    <span class="info-box-icon"><i class="fas fa-file-invoice"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Créditos Pendientes</span>
                        <span class="info-box-number" id="total_creditos_pendientes">0</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="info-box" style="background-color:#e57373; color:white">
                    <span class="info-box-icon"><i class="fas fa-exclamation-triangle"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Créditos Vencidos</span>
                        <span class="info-box-number" id="total_creditos_vencidos">0</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="info-box bg-info">
                    <span class="info-box-icon"><i class="fas fa-dollar-sign"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Saldo Pendiente</span>
                        <span class="info-box-number" id="total_saldo_pendiente">S/. 0.00</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="info-box bg-success">
                    <span class="info-box-icon"><i class="fas fa-check-circle"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Monto Pagado</span>
                        <span class="info-box-number" id="total_monto_pagado">S/. 0.00</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- FILTROS -->
        <div class="row mb-3">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header" style="background-color:#6c757d; color:white">
                        <h3 class="card-title"><i class="fas fa-filter"></i> Filtros</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label>Estado</label>
                                <select class="form-control" id="filtro_estado">
                                    <option value="PENDIENTE" selected>PENDIENTE</option>
                                    <option value="PAGADO">PAGADO</option>
                                    <option value="">TODOS</option>
                                </select>
                            </div>
                            <div class="col-md-6 form-group">
                                <label>&nbsp;</label><br>
                                <button class="btn btn-primary btn-block" onclick="Filtrar_Creditos()">
                                    <i class="fas fa-search"></i> Buscar
                                </button>
                            </div>
                        </div>
                        
                        <!-- BOTONES DE ACCIÓN -->
                        <div class="row mt-3">
                            <div class="col-md-3">
                                <button class="btn btn-success btn-block" onclick="Abrir_Modal_Agregar_Credito_Manual()">
                                    <i class="fas fa-plus-circle"></i> Agregar Crédito
                                </button>
                            </div>
                            <div class="col-md-3">
                                <button class="btn btn-block" style="background-color:#e57373; color:white; border-color:#e57373" onclick="Exportar_Creditos_PDF()">
                                    <i class="fas fa-file-pdf"></i> Exportar PDF
                                </button>
                            </div>
                            <div class="col-md-3">
                                <button class="btn btn-info btn-block" onclick="Exportar_Creditos_Excel()">
                                    <i class="fas fa-file-excel"></i> Exportar Excel
                                </button>
                            </div>
                        </div>
                        
                        <div class="alert alert-info mt-2">
                            <i class="fas fa-info-circle"></i> <strong>Nota:</strong> Usa el buscador de la tabla para filtrar por cliente, DNI o teléfono.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- TABLA DE CRÉDITOS AGRUPADOS POR CLIENTE -->
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header" style="background: linear-gradient(135deg, #e57373, #ef5350)">
                        <h3 class="card-title" style="color:white"><i class="fas fa-users"></i> Clientes con Créditos Pendientes</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                        <table id="tabla_creditos_por_cliente" class="table table-striped table-bordered table-hover" style="width:100%">
                            <thead style="background-color:#e57373; color:white">
                                <tr>
                                    <th>Cliente</th>
                                    <th>DNI/RUC</th>
                                    <th>Teléfono</th>
                                    <th>Total Vales</th>
                                    <th>Monto Total</th>
                                    <th>Pagado</th>
                                    <th>Saldo Pendiente</th>
                                    <th>Vencimiento</th>
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
        </div>

        <!-- TOP CLIENTES CON MÁS DEUDA -->
        <div class="row">
            <div class="col-md-12">
                <div class="card" style="border-top: 3px solid #e57373">
                    <div class="card-header" style="background-color:#e57373; color:white">
                        <h3 class="card-title"><i class="fas fa-chart-bar"></i> Top 10 Clientes con Más Deuda</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                        <table id="tabla_top_deudores" class="table table-sm table-bordered">
                            <thead style="background-color:#e57373; color:white">
                                <tr>
                                    <th>#</th>
                                    <th>Cliente</th>
                                    <th>DNI/RUC</th>
                                    <th>Teléfono</th>
                                    <th>Total Créditos</th>
                                    <th>Saldo Pendiente</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="tbody_top_deudores">
                            </tbody>
                        </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL REGISTRAR PAGO -->
<div class="modal fade" id="modal_registrar_pago" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #28a745, #218838); color:white">
                <h5 class="modal-title"><i class="fas fa-money-bill-wave"></i> Registrar Pago de Crédito</h5>
                <button type="button" class="close" data-dismiss="modal" style="color:white">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="txt_id_credito_pago">
                
                <!-- INFORMACIÓN DEL CRÉDITO -->
                <div class="card card-info">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-info-circle"></i> Información del Crédito</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Cliente:</strong> <span id="info_cliente_pago"></span></p>
                                <p><strong>N° Vale:</strong> <span id="info_vale_pago"></span></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Monto Total:</strong> <span id="info_monto_total_pago"></span></p>
                                <p><strong>Saldo Pendiente:</strong> <span id="info_saldo_pendiente_pago" class="text-danger" style="font-size:18px; font-weight:bold"></span></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- FORMULARIO DE PAGO -->
                <div class="row">
                    <div class="col-md-6 form-group">
                        <label>Tipo de Pago <span class="text-danger">*</span></label>
                        <select class="form-control" id="txt_tipo_pago_credito">
                            <option value="">-- Seleccione --</option>
                        </select>
                    </div>
                    <div class="col-md-6 form-group">
                        <label>Código de Operación</label>
                        <input type="text" class="form-control" id="txt_codigo_operacion_credito" placeholder="Código de operación">
                        <small class="text-muted">Obligatorio para Yape, BCP y Visa</small>
                    </div>
                    <div class="col-md-6 form-group">
                        <label>Monto a Pagar (S/.) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" class="form-control" id="txt_monto_pago_credito" placeholder="0.00">
                        <small class="text-muted">Máximo: <span id="max_monto_pago"></span></small>
                    </div>
                    <div class="col-md-6 form-group">
                        <label>&nbsp;</label><br>
                        <button type="button" class="btn btn-secondary btn-block" onclick="Pagar_Saldo_Completo()">
                            <i class="fas fa-check-double"></i> Pagar Saldo Completo
                        </button>
                    </div>
                    <div class="col-12 form-group">
                        <label>Observaciones</label>
                        <textarea class="form-control" id="txt_observaciones_pago_credito" rows="2" placeholder="Observaciones opcionales"></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times"></i> Cerrar</button>
                <button type="button" class="btn btn-success" onclick="Registrar_Pago_Credito()"><i class="fas fa-save"></i> Registrar Pago</button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL VER VALES DEL CLIENTE -->
<div class="modal fade" id="modal_vales_cliente" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #6f42c1, #5a32a3); color:white">
                <h5 class="modal-title"><i class="fas fa-file-invoice"></i> Vales del Cliente</h5>
                <button type="button" class="close" data-dismiss="modal" style="color:white">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="txt_id_cliente_vales">
                
                <!-- INFORMACIÓN DEL CLIENTE -->
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-user"></i> Información del Cliente</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <p><strong>Cliente:</strong><br><span id="info_cliente_vales"></span></p>
                            </div>
                            <div class="col-md-3">
                                <p><strong>DNI/RUC:</strong><br><span id="info_dni_vales"></span></p>
                            </div>
                            <div class="col-md-3">
                                <p><strong>Total Vales Pendientes:</strong><br><span id="info_total_vales" class="badge badge-info" style="font-size:16px"></span></p>
                            </div>
                            <div class="col-md-2">
                                <p><strong>Saldo Total:</strong><br><span id="info_saldo_total_vales" class="text-danger" style="font-size:18px; font-weight:bold"></span></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- BOTÓN PAGAR TODO -->
                <div class="row mb-3">
                    <div class="col-md-12">
                        <button class="btn btn-success btn-lg btn-block" onclick="Pagar_Todo_Cliente()" id="btn_pagar_todo_cliente" style="font-size:18px; padding:15px;">
                            <i class="fas fa-check-double"></i> PAGAR TODO - <span id="monto_total_pagar" class="font-weight-bold">S/. 0.00</span>
                        </button>
                    </div>
                </div>

                <!-- BOTONES DE EXPORTACIÓN -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <button class="btn btn-block" style="background-color:#e57373; color:white; border-color:#e57373" onclick="Exportar_Historial_Vales_PDF()">
                            <i class="fas fa-file-pdf"></i> Exportar PDF
                        </button>
                    </div>
                    <div class="col-md-6">
                        <button class="btn btn-info btn-block" onclick="Exportar_Historial_Vales_Excel()">
                            <i class="fas fa-file-excel"></i> Exportar Excel
                        </button>
                    </div>
                </div>

                <!-- TABLA DE VALES -->
                <div class="card">
                    <div class="card-header" style="background-color:#6f42c1; color:white">
                        <h3 class="card-title"><i class="fas fa-list"></i> Detalle de Vales</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                        <table id="tabla_vales_cliente" class="table table-sm table-bordered table-hover" style="width:100%">
                            <thead style="background-color:#6f42c1; color:white">
                                <tr>
                                    <th>N° Vale</th>
                                    <th>Placa</th>
                                    <th>Fecha</th>
                                    <th>Turno</th>
                                    <th>Monto</th>
                                    <th>Pagado</th>
                                    <th>Saldo</th>
                                    <th>Vencimiento</th>
                                    <th>Fecha Pago</th>
                                    <th>Estado</th>
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
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times"></i> Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL VER HISTORIAL -->
<div class="modal fade" id="modal_historial_pagos" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #17a2b8, #138496); color:white">
                <h5 class="modal-title"><i class="fas fa-history"></i> Historial de Pagos</h5>
                <button type="button" class="close" data-dismiss="modal" style="color:white">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- INFORMACIÓN DEL CRÉDITO -->
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-info-circle"></i> Información del Crédito</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <p><strong>Cliente:</strong><br><span id="hist_cliente"></span></p>
                            </div>
                            <div class="col-md-3">
                                <p><strong>N° Vale:</strong><br><span id="hist_vale"></span></p>
                            </div>
                            <div class="col-md-3">
                                <p><strong>Monto Total:</strong><br><span id="hist_monto_total"></span></p>
                            </div>
                            <div class="col-md-3">
                                <p><strong>Saldo Pendiente:</strong><br><span id="hist_saldo_pendiente" class="text-danger" style="font-size:18px; font-weight:bold"></span></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TABLA DE PAGOS -->
                <div class="card">
                    <div class="card-header" style="background-color:#17a2b8; color:white">
                        <h3 class="card-title"><i class="fas fa-list"></i> Pagos Realizados</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                        <table id="tabla_historial_pagos_credito" class="table table-sm table-bordered table-hover" style="width:100%">
                            <thead style="background-color:#17a2b8; color:white">
                                <tr>
                                    <th>Fecha</th>
                                    <th>Tipo de Pago</th>
                                    <th>Código</th>
                                    <th>Monto Pagado</th>
                                    <th>Saldo Anterior</th>
                                    <th>Saldo Nuevo</th>
                                    <th>Registrado Por</th>
                                    <th>Observaciones</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times"></i> Cerrar</button>
            </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL AGREGAR CRÉDITO MANUAL -->
<div class="modal fade" id="modal_agregar_credito_manual" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #28a745, #218838); color:white">
                <h5 class="modal-title"><i class="fas fa-plus-circle"></i> Agregar Crédito Manual</h5>
                <button type="button" class="close" data-dismiss="modal" style="color:white">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <button class="btn btn-success btn-sm mb-2" onclick="Agregar_Nueva_Fila_Credito()">
                    <i class="fas fa-plus"></i> Agregar Fila
                </button>
                <div class="table-responsive">
                    <table id="tabla_creditos_manual_editable" class="table table-sm table-bordered table-hover">
                        <thead style="background-color:#28a745; color:white">
                            <tr>
                                <th width="4%">#</th>
                                <th width="28%">Cliente</th>
                                <th width="15%">N° Vale</th>
                                <th width="12%">Placa</th>
                                <th width="14%">Monto (S/.)</th>
                                <th width="15%">Fecha de Registro</th>
                                <th width="12%">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tbody_creditos_manual_editable">
                            <!-- Filas se agregarán dinámicamente -->
                        </tbody>
                    </table>
                </div>
                <div class="alert alert-info mt-2">
                    <i class="fas fa-info-circle"></i> <strong>Tip:</strong> Escribe directamente en las celdas. Solo se guardarán las filas con datos completos (Cliente, N° Vale y Monto).
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times"></i> Cerrar</button>
                <button type="button" class="btn btn-success" onclick="Guardar_Todos_Creditos_Manual()"><i class="fas fa-save"></i> Guardar Todo</button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL EDITAR CRÉDITO -->
<div class="modal fade" id="modal_editar_credito" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #ffc107, #ff9800); color:white">
                <h5 class="modal-title"><i class="fas fa-edit"></i> Editar Crédito</h5>
                <button type="button" class="close" data-dismiss="modal" style="color:white">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="txt_id_credito_editar">
                
                <div class="row">
                    <div class="col-md-6 form-group">
                        <label>Número de Vale</label>
                        <input type="text" class="form-control" id="txt_numero_vale_editar" placeholder="Número de vale">
                    </div>
                    <div class="col-md-6 form-group">
                        <label>Monto (S/.)</label>
                        <input type="number" step="0.01" class="form-control" id="txt_monto_editar" placeholder="0.00">
                    </div>
                    <div class="col-md-6 form-group">
                        <label>Fecha de Vencimiento</label>
                        <input type="date" class="form-control" id="txt_fecha_vencimiento_editar">
                    </div>
                    <div class="col-md-6 form-group">
                        <label>Estado</label>
                        <select class="form-control" id="txt_estado_editar">
                            <option value="PENDIENTE">PENDIENTE</option>
                            <option value="PAGADO">PAGADO</option>
                            <option value="ANULADO">ANULADO</option>
                        </select>
                    </div>
                    <div class="col-12 form-group">
                        <label>Observaciones</label>
                        <textarea class="form-control" id="txt_observaciones_editar" rows="2" placeholder="Observaciones"></textarea>
                    </div>
                </div>
                
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i> <strong>Nota:</strong> Si cambias el monto, el saldo se recalculará automáticamente.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times"></i> Cerrar</button>
                <button type="button" class="btn btn-warning" onclick="Actualizar_Credito()"><i class="fas fa-save"></i> Actualizar</button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    Cargar_Resumen_Creditos();
    Listar_Creditos_Por_Cliente();
    Cargar_Top_Deudores();
});
</script>
