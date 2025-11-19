<script src="../js/console_turnos.js?rev=<?php echo time(); ?>"></script>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-file-alt"></i> <b>MIS REPORTES</b></h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="../view/index.php">Inicio</a></li>
                    <li class="breadcrumb-item active">Mis Reportes</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> <strong>Historial de tus turnos</strong><br>
                    Aquí puedes ver todos los turnos que has realizado.
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header" style="background: linear-gradient(135deg, #023D77, #0266C8)">
                        <h3 class="card-title" style="color:white"><i class="fas fa-list"></i> Mis Turnos/Reportes</h3>
                    </div>
                    <div class="card-body">
                        <!-- FILTROS -->
                        <div class="row mb-3">
                            <div class="col-md-4 form-group">
                                <label>Fecha Inicio</label>
                                <input type="date" class="form-control" id="filtro_fecha_inicio_mis">
                            </div>
                            <div class="col-md-4 form-group">
                                <label>Fecha Fin</label>
                                <input type="date" class="form-control" id="filtro_fecha_fin_mis">
                            </div>
                            <div class="col-md-4 form-group">
                                <label>&nbsp;</label><br>
                                <button class="btn btn-primary btn-block" onclick="Filtrar_Mis_Turnos()">
                                    <i class="fas fa-search"></i> Buscar
                                </button>
                            </div>
                        </div>

                        <!-- TABLA -->
                        <table id="tabla_mis_reportes" class="table table-striped table-bordered table-hover" style="width:100%">
                            <thead style="background-color:#023D77; color:white">
                                <tr>
                                    <th>N° Documento</th>
                                    <th>Fecha</th>
                                    <th>Turno</th>
                                    <th>Total Ventas</th>
                                    <th>Faltante</th>
                                    <th>Estado</th>
                                    <th>Validación</th>
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

<script>
var tabla_mis_reportes;

function Listar_Mis_Reportes() {
    var filtro_fecha_inicio = $("#filtro_fecha_inicio_mis").val();
    var filtro_fecha_fin = $("#filtro_fecha_fin_mis").val();
    var id_usuario = $("#txtprincipalid").val(); // ID del grifero logueado
    
    if (tabla_mis_reportes) {
        tabla_mis_reportes.destroy();
    }
    
    tabla_mis_reportes = $("#tabla_mis_reportes").DataTable({
        "ordering": true,
        "bLengthChange": true,
        "searching": { "regex": false },
        "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
        "pageLength": 10,
        "destroy": true,
        "processing": true,
        "ajax": {
            "url": "../controller/turnos/controlador_listar_turnos.php",
            type: 'POST',
            data: {
                filtro_fecha_inicio: filtro_fecha_inicio,
                filtro_fecha_fin: filtro_fecha_fin,
                filtro_usuario: id_usuario, // Filtrar solo por este usuario
                filtro_estado: null,
                filtro_validacion: null
            }
        },
        "columns": [
            { "data": "numero_documento" },
            { 
                "data": "fecha_reporte",
                "render": function(data) {
                    var fecha = new Date(data);
                    return fecha.toLocaleDateString('es-PE');
                }
            },
            { 
                "data": "turno",
                "render": function(data) {
                    if (data == 'DIA') {
                        return '<span class="badge badge-warning">DÍA</span>';
                    } else {
                        return '<span class="badge badge-dark">NOCHE</span>';
                    }
                }
            },
            { 
                "data": "total_ventas",
                "render": function(data) {
                    return 'S/. ' + parseFloat(data || 0).toFixed(2);
                }
            },
            { 
                "data": "monto_faltante",
                "render": function(data) {
                    var faltante = parseFloat(data || 0);
                    if (faltante < 0) {
                        return '<span class="badge badge-danger">S/. ' + Math.abs(faltante).toFixed(2) + '</span>';
                    } else if (faltante > 0) {
                        return '<span class="badge badge-success">S/. ' + faltante.toFixed(2) + '</span>';
                    } else {
                        return '<span class="badge badge-info">S/. 0.00</span>';
                    }
                }
            },
            { 
                "data": "estado",
                "render": function(data) {
                    if (data == 'ABIERTO') {
                        return '<span class="badge badge-success">ABIERTO</span>';
                    } else {
                        return '<span class="badge badge-secondary">CERRADO</span>';
                    }
                }
            },
            { 
                "data": "estado_validacion",
                "render": function(data, type, row) {
                    if (data == 'VALIDADO') {
                        var tooltip = row.validado_por ? 'Validado por: ' + row.validado_por : 'Validado';
                        return '<span class="badge badge-success" title="' + tooltip + '"><i class="fas fa-check-circle"></i> VALIDADO</span>';
                    } else if (data == 'PENDIENTE') {
                        return '<span class="badge badge-warning"><i class="fas fa-clock"></i> PENDIENTE</span>';
                    } else {
                        return '<span class="badge badge-secondary">N/A</span>';
                    }
                }
            },
            {
                "data": "id_reporte",
                "render": function(data) {
                    return "<button class='ver btn btn-info btn-sm' title='Ver Detalle'><i class='fas fa-eye'></i></button>&nbsp;<button class='imprimir btn btn-primary btn-sm' title='Imprimir'><i class='fas fa-print'></i></button>";
                }
            }
        ],
        "language": idioma_espanol,
        select: true
    });

    document.getElementById("tabla_mis_reportes").addEventListener("click", function(e) {
        if (e.target.closest(".ver")) {
            var data = tabla_mis_reportes.row(e.target.closest("tr")).data();
            Ver_Detalle_Turno(data.id_reporte);
        }
        if (e.target.closest(".imprimir")) {
            var data = tabla_mis_reportes.row(e.target.closest("tr")).data();
            Imprimir_Reporte(data.id_reporte);
        }
    });
}

function Filtrar_Mis_Turnos() {
    Listar_Mis_Reportes();
}

$(document).ready(function() {
    Listar_Mis_Reportes();
    
    // Establecer fechas por defecto (último mes)
    var hoy = new Date();
    var hace_mes = new Date();
    hace_mes.setMonth(hace_mes.getMonth() - 1);
    
    $("#filtro_fecha_inicio_mis").val(hace_mes.toISOString().split('T')[0]);
    $("#filtro_fecha_fin_mis").val(hoy.toISOString().split('T')[0]);
});
</script>
