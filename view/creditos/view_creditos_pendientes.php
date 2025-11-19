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
                <div class="info-box bg-danger">
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
                            <div class="col-md-4 form-group">
                                <label>Cliente</label>
                                <select class="js-example-basic-single form-control" id="filtro_cliente" style="width:100%">
                                    <option value="">Todos los clientes</option>
                                </select>
                            </div>
                            <div class="col-md-4 form-group">
                                <label>Estado</label>
                                <select class="form-control" id="filtro_estado">
                                    <option value="">Todos</option>
                                    <option value="PENDIENTE" selected>PENDIENTE</option>
                                    <option value="PAGADO">PAGADO</option>
                                    <option value="ANULADO">ANULADO</option>
                                </select>
                            </div>
                            <div class="col-md-4 form-group">
                                <label>&nbsp;</label><br>
                                <button class="btn btn-primary btn-block" onclick="Filtrar_Creditos()">
                                    <i class="fas fa-search"></i> Buscar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- TABLA DE CRÉDITOS -->
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header" style="background: linear-gradient(135deg, #dc3545, #c82333)">
                        <h3 class="card-title" style="color:white"><i class="fas fa-list"></i> Listado de Créditos</h3>
                    </div>
                    <div class="card-body">
                        <table id="tabla_creditos_pendientes" class="table table-striped table-bordered table-hover" style="width:100%">
                            <thead style="background-color:#dc3545; color:white">
                                <tr>
                                    <th>N° Vale</th>
                                    <th>Cliente</th>
                                    <th>Fecha</th>
                                    <th>Turno</th>
                                    <th>Monto Total</th>
                                    <th>Pagado</th>
                                    <th>Saldo</th>
                                    <th>Vencimiento</th>
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

        <!-- TOP CLIENTES CON MÁS DEUDA -->
        <div class="row">
            <div class="col-md-12">
                <div class="card card-danger">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-chart-bar"></i> Top 10 Clientes con Más Deuda</h3>
                    </div>
                    <div class="card-body">
                        <table id="tabla_top_deudores" class="table table-sm table-bordered">
                            <thead style="background-color:#dc3545; color:white">
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
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times"></i> Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    Cargar_Resumen_Creditos();
    Listar_Creditos_Pendientes();
    Cargar_Top_Deudores();
    Cargar_Clientes_Filtro();
});
</script>
