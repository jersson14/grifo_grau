<script src="../js/console_productos.js?rev=<?php echo time(); ?>"></script>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-gas-pump"></i> <b>GESTIÓN DE PRODUCTOS (COMBUSTIBLES)</b></h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="../view/index.php">Inicio</a></li>
                    <li class="breadcrumb-item active">Productos</li>
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
                        <h3 class="card-title" style="color:white"><i class="fas fa-list"></i> Listado de Productos</h3>
                        <div class="card-tools">
                            <button class="btn btn-success btn-sm" onclick="AbrirModalRegistro()">
                                <i class="fas fa-plus"></i> Nuevo Producto
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <table id="tabla_productos" class="table table-striped table-bordered table-hover" style="width:100%">
                            <thead style="background-color:#023D77; color:white">
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>Tipo</th>
                                    <th>Precio Actual (S/.)</th>
                                    <th>Estado</th>
                                    <th>Fecha Registro</th>
                                    <th>Última Actualización</th>
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

        <!-- HISTORIAL DE PRECIOS -->
        <div class="row">
            <div class="col-md-12">
                <div class="card card-secondary collapsed-card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-history"></i> Historial de Cambios de Precios</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <table id="tabla_historial" class="table table-sm table-bordered" style="width:100%">
                            <thead style="background-color:#6c757d; color:white">
                                <tr>
                                    <th>Producto</th>
                                    <th>Tipo</th>
                                    <th>Precio Actual</th>
                                    <th>Fecha Cambio</th>
                                    <th>Modificado Por</th>
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
<div class="modal fade" id="modal_registro_producto" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #023D77, #0266C8); color:white">
                <h5 class="modal-title" id="exampleModalLabel"><i class="fas fa-plus-circle"></i> Registrar Producto</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color:white">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-12 form-group">
                        <label for="txt_nombre">Nombre del Producto <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="txt_nombre" placeholder="Ej: Diesel B5">
                    </div>
                    <div class="col-12 form-group">
                        <label for="txt_tipo">Tipo de Combustible <span class="text-danger">*</span></label>
                        <select class="form-control" id="txt_tipo">
                            <option value="">-- Seleccione --</option>
                            <option value="DIESEL">DIESEL</option>
                            <option value="REGULAR">REGULAR</option>
                            <option value="PREMIUM">PREMIUM</option>
                        </select>
                    </div>
                    <div class="col-12 form-group">
                        <label for="txt_precio">Precio Actual (S/.) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" class="form-control" id="txt_precio" placeholder="0.00">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times"></i> Cerrar</button>
                <button type="button" class="btn btn-primary" onclick="Registrar_Producto()"><i class="fas fa-save"></i> Registrar</button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL EDITAR -->
<div class="modal fade" id="modal_editar_producto" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #FFA500, #FF8C00); color:white">
                <h5 class="modal-title"><i class="fas fa-edit"></i> Modificar Producto</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color:white">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-12 form-group" style="display:none">
                        <input type="text" id="txt_id_producto">
                    </div>
                    <div class="col-12 form-group">
                        <label for="txt_nombre_editar">Nombre del Producto <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="txt_nombre_editar">
                    </div>
                    <div class="col-12 form-group">
                        <label for="txt_tipo_editar">Tipo de Combustible <span class="text-danger">*</span></label>
                        <select class="form-control" id="txt_tipo_editar">
                            <option value="DIESEL">DIESEL</option>
                            <option value="REGULAR">REGULAR</option>
                            <option value="PREMIUM">PREMIUM</option>
                        </select>
                    </div>
                    <div class="col-12 form-group">
                        <label for="txt_precio_editar">Precio Actual (S/.) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" class="form-control" id="txt_precio_editar">
                        <small class="text-muted">Este cambio se aplicará a partir del siguiente turno</small>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times"></i> Cerrar</button>
                <button type="button" class="btn btn-warning" onclick="Modificar_Producto()"><i class="fas fa-save"></i> Actualizar</button>
            </div>
        </div>
    </div>
</div>
