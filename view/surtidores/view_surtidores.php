<script src="../js/console_surtidores.js?rev=<?php echo time(); ?>"></script>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-gas-pump"></i> <b>GESTIÓN DE SURTIDORES</b></h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="../view/index.php">Inicio</a></li>
                    <li class="breadcrumb-item active">Surtidores</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="content">
    <div class="container-fluid">
        
        <!-- FILTROS -->
        <div class="row mb-3">
            <div class="col-md-3">
                <label>Filtrar por Máquina:</label>
                <select class="form-control" id="filtro_maquina" onchange="Filtrar_Por_Maquina()">
                    <option value="">Todas las Máquinas</option>
                    <option value="1">Máquina 1</option>
                    <option value="2">Máquina 2</option>
                </select>
            </div>
            <div class="col-md-3">
                <label>Filtrar por Estado:</label>
                <select class="form-control" id="filtro_estado" onchange="Filtrar_Por_Estado()">
                    <option value="">Todos los Estados</option>
                    <option value="ACTIVO">ACTIVO</option>
                    <option value="INACTIVO">INACTIVO</option>
                    <option value="MANTENIMIENTO">MANTENIMIENTO</option>
                </select>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header" style="background: linear-gradient(135deg, #023D77, #0266C8)">
                        <h3 class="card-title" style="color:white"><i class="fas fa-list"></i> Listado de Surtidores</h3>
                        <div class="card-tools">
                            <button class="btn btn-success btn-sm" onclick="AbrirModalRegistro()">
                                <i class="fas fa-plus"></i> Nuevo Surtidor
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <table id="tabla_surtidores" class="table table-striped table-bordered table-hover" style="width:100%">
                            <thead style="background-color:#023D77; color:white">
                                <tr>
                                    <th>ID</th>
                                    <th>Máquina</th>
                                    <th>Código</th>
                                    <th>Producto</th>
                                    <th>Tipo</th>
                                    <th>Precio Actual</th>
                                    <th>Lectura Actual</th>
                                    <th>Estado</th>
                                    <th>Última Actualización</th>
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

        <!-- VISTA POR MÁQUINAS -->
        <div class="row">
            <div class="col-md-6">
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
                                    <th>Lectura</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody id="tabla_maquina_1">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
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
                                    <th>Lectura</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody id="tabla_maquina_2">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL REGISTRAR -->
<div class="modal fade" id="modal_registro_surtidor" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #023D77, #0266C8); color:white">
                <h5 class="modal-title"><i class="fas fa-plus-circle"></i> Registrar Surtidor</h5>
                <button type="button" class="close" data-dismiss="modal" style="color:white">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6 form-group">
                        <label>Número de Máquina <span class="text-danger">*</span></label>
                        <select class="form-control" id="txt_numero_maquina">
                            <option value="">-- Seleccione --</option>
                            <option value="1">Máquina 1</option>
                            <option value="2">Máquina 2</option>
                        </select>
                    </div>
                    <div class="col-md-6 form-group">
                        <label>Código del Surtidor <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="txt_codigo" placeholder="Ej: BS1, R1, P1" maxlength="10">
                        <small class="text-muted">Ejemplos: BS1, BS2, R1, R2, P1, P2</small>
                    </div>
                    <div class="col-md-6 form-group">
                        <label>Producto (Combustible) <span class="text-danger">*</span></label>
                        <select class="form-control" id="txt_producto">
                            <option value="">-- Seleccione --</option>
                        </select>
                    </div>
                    <div class="col-md-6 form-group">
                        <label>Lectura Inicial <span class="text-danger">*</span></label>
                        <input type="number" step="0.001" class="form-control" id="txt_lectura_inicial" placeholder="0.000">
                        <small class="text-muted">Lectura inicial del contador</small>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times"></i> Cerrar</button>
                <button type="button" class="btn btn-primary" onclick="Registrar_Surtidor()"><i class="fas fa-save"></i> Registrar</button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL EDITAR -->
<div class="modal fade" id="modal_editar_surtidor" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #FFA500, #FF8C00); color:white">
                <h5 class="modal-title"><i class="fas fa-edit"></i> Modificar Surtidor</h5>
                <button type="button" class="close" data-dismiss="modal" style="color:white">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-12 form-group" style="display:none">
                        <input type="text" id="txt_id_surtidor">
                    </div>
                    <div class="col-md-6 form-group">
                        <label>Número de Máquina <span class="text-danger">*</span></label>
                        <select class="form-control" id="txt_numero_maquina_editar">
                            <option value="1">Máquina 1</option>
                            <option value="2">Máquina 2</option>
                        </select>
                    </div>
                    <div class="col-md-6 form-group">
                        <label>Código del Surtidor <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="txt_codigo_editar" maxlength="10">
                    </div>
                    <div class="col-md-12 form-group">
                        <label>Producto (Combustible) <span class="text-danger">*</span></label>
                        <select class="form-control" id="txt_producto_editar">
                            <option value="">-- Seleccione --</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times"></i> Cerrar</button>
                <button type="button" class="btn btn-warning" onclick="Modificar_Surtidor()"><i class="fas fa-save"></i> Actualizar</button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL ACTUALIZAR LECTURA -->
<div class="modal fade" id="modal_actualizar_lectura" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #17a2b8, #138496); color:white">
                <h5 class="modal-title"><i class="fas fa-tachometer-alt"></i> Actualizar Lectura</h5>
                <button type="button" class="close" data-dismiss="modal" style="color:white">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i> <strong>Advertencia:</strong> Solo actualice la lectura si es necesario corregir un error. Las lecturas se actualizan automáticamente al cerrar cada turno.
                </div>
                <div class="row">
                    <div class="col-12 form-group" style="display:none">
                        <input type="text" id="txt_id_surtidor_lectura">
                    </div>
                    <div class="col-12 form-group">
                        <label>Surtidor:</label>
                        <p><strong id="txt_info_surtidor"></strong></p>
                    </div>
                    <div class="col-12 form-group">
                        <label>Lectura Actual:</label>
                        <p><strong id="txt_lectura_actual_info"></strong></p>
                    </div>
                    <div class="col-12 form-group">
                        <label>Nueva Lectura <span class="text-danger">*</span></label>
                        <input type="number" step="0.001" class="form-control" id="txt_nueva_lectura" placeholder="0.000">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times"></i> Cerrar</button>
                <button type="button" class="btn btn-info" onclick="Actualizar_Lectura()"><i class="fas fa-save"></i> Actualizar</button>
            </div>
        </div>
    </div>
</div>
