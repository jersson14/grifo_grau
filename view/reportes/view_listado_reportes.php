<script src="../js/console_turnos.js?rev=<?php echo time(); ?>"></script>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-file-alt"></i> <b>TODOS LOS REPORTES</b></h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="../view/index.php">Inicio</a></li>
                    <li class="breadcrumb-item active">Reportes</li>
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
                        <h3 class="card-title" style="color:white"><i class="fas fa-list"></i> Listado de Turnos/Reportes</h3>
                    </div>
                    <div class="card-body">
                        <!-- FILTROS -->
                        <div class="row mb-3">
                            <div class="col-md-3 form-group">
                                <label>Fecha Inicio</label>
                                <input type="date" class="form-control" id="filtro_fecha_inicio">
                            </div>
                            <div class="col-md-3 form-group">
                                <label>Fecha Fin</label>
                                <input type="date" class="form-control" id="filtro_fecha_fin">
                            </div>
                            <div class="col-md-3 form-group">
                                <label>Estado</label>
                                <select class="form-control" id="filtro_estado">
                                    <option value="">Todos</option>
                                    <option value="ABIERTO">ABIERTO</option>
                                    <option value="CERRADO">CERRADO</option>
                                </select>
                            </div>
                            <div class="col-md-3 form-group">
                                <label>&nbsp;</label><br>
                                <button class="btn btn-primary btn-block" onclick="Filtrar_Turnos()">
                                    <i class="fas fa-search"></i> Buscar
                                </button>
                            </div>
                        </div>

                        <!-- TABLA -->
                        <table id="tabla_historial_turnos" class="table table-striped table-bordered table-hover" style="width:100%">
                            <thead style="background-color:#023D77; color:white">
                                <tr>
                                    <th>N° Documento</th>
                                    <th>Fecha</th>
                                    <th>Turno</th>
                                    <th>Grifero</th>
                                    <th>Total Ventas</th>
                                    <th>Faltante</th>
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
    </div>
</div>

<script>
$(document).ready(function() {
    Listar_Historial_Turnos();
    
    // Establecer fechas por defecto (último mes)
    var hoy = new Date();
    var hace_mes = new Date();
    hace_mes.setMonth(hace_mes.getMonth() - 1);
    
    $("#filtro_fecha_inicio").val(hace_mes.toISOString().split('T')[0]);
    $("#filtro_fecha_fin").val(hoy.toISOString().split('T')[0]);
});
</script>


<!-- MODAL DETALLE TURNO -->
<div class="modal fade" id="modal_detalle_turno" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #023D77, #0056b3); color:white">
                <h5 class="modal-title"><i class="fas fa-eye"></i> Detalle del Turno</h5>
                <button type="button" class="close" data-dismiss="modal" style="color:white">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-3">
                        <strong>N° Documento:</strong><br>
                        <span id="detalle_numero_documento">-</span>
                    </div>
                    <div class="col-md-3">
                        <strong>Fecha:</strong><br>
                        <span id="detalle_fecha">-</span>
                    </div>
                    <div class="col-md-3">
                        <strong>Turno:</strong><br>
                        <span id="detalle_turno">-</span>
                    </div>
                    <div class="col-md-3">
                        <strong>Grifero:</strong><br>
                        <span id="detalle_grifero">-</span>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-3">
                        <div class="info-box bg-danger">
                            <div class="info-box-content">
                                <span class="info-box-text">Diesel</span>
                                <span class="info-box-number" id="detalle_total_diesel">S/. 0.00</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box bg-success">
                            <div class="info-box-content">
                                <span class="info-box-text">Regular</span>
                                <span class="info-box-number" id="detalle_total_regular">S/. 0.00</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box bg-warning">
                            <div class="info-box-content">
                                <span class="info-box-text">Premium</span>
                                <span class="info-box-number" id="detalle_total_premium">S/. 0.00</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box bg-primary">
                            <div class="info-box-content">
                                <span class="info-box-text">Total Ventas</span>
                                <span class="info-box-number" id="detalle_total_ventas">S/. 0.00</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TOTALES GENERALES -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header" style="background-color: #ffc107; color: #000;">
                                <h5 class="card-title mb-0"><i class="fas fa-dollar-sign"></i> Totales en Soles</h5>
                            </div>
                            <div class="card-body">
                                <table class="table table-sm table-bordered">
                                    <tbody>
                                        <tr>
                                            <td><strong>Diesel:</strong></td>
                                            <td class="text-right" id="detalle_soles_diesel">S/. 0.00</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Regular:</strong></td>
                                            <td class="text-right" id="detalle_soles_regular">S/. 0.00</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Premium:</strong></td>
                                            <td class="text-right" id="detalle_soles_premium">S/. 0.00</td>
                                        </tr>
                                        <tr style="background-color: #f0f0f0;">
                                            <td><strong>TOTAL:</strong></td>
                                            <td class="text-right"><strong id="detalle_soles_total">S/. 0.00</strong></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header" style="background-color: #17a2b8; color: white;">
                                <h5 class="card-title mb-0"><i class="fas fa-tint"></i> Totales en Galones</h5>
                            </div>
                            <div class="card-body">
                                <table class="table table-sm table-bordered">
                                    <tbody>
                                        <tr>
                                            <td><strong>Diesel:</strong></td>
                                            <td class="text-right" id="detalle_galones_diesel">0.000 gal</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Regular:</strong></td>
                                            <td class="text-right" id="detalle_galones_regular">0.000 gal</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Premium:</strong></td>
                                            <td class="text-right" id="detalle_galones_premium">0.000 gal</td>
                                        </tr>
                                        <tr style="background-color: #f0f0f0;">
                                            <td><strong>TOTAL:</strong></td>
                                            <td class="text-right"><strong id="detalle_galones_total">0.000 gal</strong></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                
                <h5><i class="fas fa-gas-pump"></i> Lecturas de Surtidores</h5>
                <table id="tabla_detalle_lecturas" class="table table-bordered table-sm">
                    <thead style="background-color:#023D77; color:white">
                        <tr>
                            <th>Máquina</th>
                            <th>Código</th>
                            <th>Producto</th>
                            <th>Lectura Anterior</th>
                            <th>Lectura Actual</th>
                            <th>Galones</th>
                            <th>Precio</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times"></i> Cerrar</button>
            </div>
        </div>
    </div>
</div>
