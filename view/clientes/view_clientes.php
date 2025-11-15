<script src="../js/console_clientes_grifo.js?rev=<?php echo time(); ?>"></script>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-user-friends"></i> <b>GESTIÓN DE CLIENTES</b></h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="../view/index.php">Inicio</a></li>
                    <li class="breadcrumb-item active">Clientes</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header" style="background: linear-gradient(135deg, #023D77, #0266C8)">
                        <h3 class="card-title" style="color:white"><i class="fas fa-list"></i> Listado de Clientes</h3>
                        <div class="card-tools">
                            <button class="btn btn-success btn-sm" onclick="AbrirModalRegistro()">
                                <i class="fas fa-plus"></i> Nuevo Cliente
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <table id="tabla_clientes" class="table table-striped table-bordered table-hover" style="width:100%">
                            <thead style="background-color:#023D77; color:white">
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre Completo</th>
                                    <th>DNI/RUC</th>
                                    <th>Teléfono</th>
                                    <th>Dirección</th>
                                    <th>Saldo Pendiente</th>
                                    <th>Estado</th>
                                    <th>Fecha Registro</th>
                                    <th>Registrado Por</th>
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
</div>

<!-- MODAL REGISTRAR -->
<div class="modal fade" id="modal_registro_cliente" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #023D77, #0266C8); color:white">
                <h5 class="modal-title"><i class="fas fa-plus-circle"></i> Registrar Cliente</h5>
                <button type="button" class="close" data-dismiss="modal" style="color:white">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6 form-group">
                        <label>Nombre Completo <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="txt_nombre" placeholder="Nombre completo del cliente">
                    </div>
                    <div class="col-md-6 form-group">
                        <label>DNI/RUC</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="txt_dni" placeholder="DNI o RUC" maxlength="11">
                            <div class="input-group-append">
                                <button class="btn btn-info" type="button" onclick="Buscar_DNI_RUC()" title="Buscar en RENIEC/SUNAT">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 form-group">
                        <label>Teléfono</label>
                        <input type="text" class="form-control" id="txt_telefono" placeholder="Teléfono" maxlength="15">
                    </div>
                    <div class="col-md-6 form-group">
                        <label>Dirección</label>
                        <input type="text" class="form-control" id="txt_direccion" placeholder="Dirección">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times"></i> Cerrar</button>
                <button type="button" class="btn btn-primary" onclick="Registrar_Cliente()"><i class="fas fa-save"></i> Registrar</button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL EDITAR -->
<div class="modal fade" id="modal_editar_cliente" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #FFA500, #FF8C00); color:white">
                <h5 class="modal-title"><i class="fas fa-edit"></i> Modificar Cliente</h5>
                <button type="button" class="close" data-dismiss="modal" style="color:white">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-12 form-group" style="display:none">
                        <input type="text" id="txt_id_cliente">
                    </div>
                    <div class="col-md-6 form-group">
                        <label>Nombre Completo <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="txt_nombre_editar">
                    </div>
                    <div class="col-md-6 form-group">
                        <label>DNI/RUC</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="txt_dni_editar" maxlength="11">
                            <div class="input-group-append">
                                <button class="btn btn-info" type="button" onclick="Buscar_DNI_RUC_Editar()" title="Buscar en RENIEC/SUNAT">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 form-group">
                        <label>Teléfono</label>
                        <input type="text" class="form-control" id="txt_telefono_editar" maxlength="15">
                    </div>
                    <div class="col-md-6 form-group">
                        <label>Dirección</label>
                        <input type="text" class="form-control" id="txt_direccion_editar">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times"></i> Cerrar</button>
                <button type="button" class="btn btn-warning" onclick="Modificar_Cliente()"><i class="fas fa-save"></i> Actualizar</button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL VER DETALLE -->
<div class="modal fade" id="modal_detalle_cliente" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #17a2b8, #138496); color:white">
                <h5 class="modal-title"><i class="fas fa-info-circle"></i> Detalle del Cliente</h5>
                <button type="button" class="close" data-dismiss="modal" style="color:white">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- DATOS DEL CLIENTE -->
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-user"></i> Información del Cliente</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Nombre:</strong> <span id="detalle_nombre"></span></p>
                                <p><strong>DNI/RUC:</strong> <span id="detalle_dni"></span></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Teléfono:</strong> <span id="detalle_telefono"></span></p>
                                <p><strong>Dirección:</strong> <span id="detalle_direccion"></span></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- RESUMEN DE CRÉDITOS -->
                <div class="card card-warning card-outline">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-chart-bar"></i> Resumen de Créditos</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="info-box bg-info">
                                    <span class="info-box-icon"><i class="fas fa-file-invoice"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Total Créditos</span>
                                        <span class="info-box-number" id="detalle_total_creditos">0</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="info-box bg-secondary">
                                    <span class="info-box-icon"><i class="fas fa-dollar-sign"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Monto Total</span>
                                        <span class="info-box-number" id="detalle_monto_total">S/. 0.00</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="info-box bg-success">
                                    <span class="info-box-icon"><i class="fas fa-check"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Monto Pagado</span>
                                        <span class="info-box-number" id="detalle_monto_pagado">S/. 0.00</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="info-box bg-danger">
                                    <span class="info-box-icon"><i class="fas fa-exclamation-triangle"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Saldo Pendiente</span>
                                        <span class="info-box-number" id="detalle_saldo_pendiente">S/. 0.00</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TABLA DE CRÉDITOS -->
                <div class="card card-danger card-outline">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-list"></i> Historial de Créditos</h3>
                    </div>
                    <div class="card-body">
                        <table id="tabla_creditos_cliente" class="table table-sm table-bordered table-hover" style="width:100%">
                            <thead style="background-color:#dc3545; color:white">
                                <tr>
                                    <th>N° Vale</th>
                                    <th>Fecha</th>
                                    <th>Turno</th>
                                    <th>Monto Total</th>
                                    <th>Pagado</th>
                                    <th>Saldo</th>
                                    <th>Estado</th>
                                    <th>Vencimiento</th>
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
