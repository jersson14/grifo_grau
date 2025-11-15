<script src="../js/console_turnos.js?rev=<?php echo time(); ?>"></script>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-history"></i> <b>HISTORIAL DE TURNOS</b></h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="../view/index.php">Inicio</a></li>
                    <li class="breadcrumb-item active">Historial</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="content">
    <div class="container-fluid">
        
        <!-- FILTROS -->
        <div class="row mb-3">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header" style="background-color:#6c757d; color:white">
                        <h3 class="card-title"><i class="fas fa-filter"></i> Filtros de Búsqueda</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
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
                    </div>
                </div>
            </div>
        </div>

        <!-- TABLA DE TURNOS -->
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header" style="background: linear-gradient(135deg, #023D77, #0266C8)">
                        <h3 class="card-title" style="color:white"><i class="fas fa-list"></i> Listado de Turnos</h3>
                    </div>
                    <div class="card-body">
                        <table id="tabla_historial_turnos" class="table table-striped table-bordered table-hover" style="width:100%">
                            <thead style="background-color:#023D77; color:white">
                                <tr>
                                    <th>N° Documento</th>
                                    <th>Fecha</th>
                                    <th>Turno</th>
                                    <th>Grifero</th>
                                    <th>Horario</th>
                                    <th>Total Ventas</th>
                                    <th>Total Pagos</th>
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

<!-- MODAL VER DETALLE -->
<div class="modal fade" id="modal_detalle_turno" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #17a2b8, #138496); color:white">
                <h5 class="modal-title"><i class="fas fa-eye"></i> Detalle del Turno</h5>
                <button type="button" class="close" data-dismiss="modal" style="color:white">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- INFORMACIÓN DEL TURNO -->
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-info-circle"></i> Información General</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <p><strong>N° Documento:</strong><br><span id="detalle_numero_documento"></span></p>
                            </div>
                            <div class="col-md-3">
                                <p><strong>Fecha:</strong><br><span id="detalle_fecha"></span></p>
                            </div>
                            <div class="col-md-3">
                                <p><strong>Turno:</strong><br><span id="detalle_turno"></span></p>
                            </div>
                            <div class="col-md-3">
                                <p><strong>Grifero:</strong><br><span id="detalle_grifero"></span></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- RESUMEN DE VENTAS -->
                <div class="card card-success">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-chart-bar"></i> Resumen de Ventas</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="info-box bg-dark">
                                    <span class="info-box-icon"><i class="fas fa-gas-pump"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">DIESEL</span>
                                        <span class="info-box-number" id="detalle_total_diesel">S/. 0.00</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="info-box bg-info">
                                    <span class="info-box-icon"><i class="fas fa-gas-pump"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">REGULAR</span>
                                        <span class="info-box-number" id="detalle_total_regular">S/. 0.00</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="info-box bg-warning">
                                    <span class="info-box-icon"><i class="fas fa-gas-pump"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">PREMIUM</span>
                                        <span class="info-box-number" id="detalle_total_premium">S/. 0.00</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="info-box bg-success">
                                    <span class="info-box-icon"><i class="fas fa-dollar-sign"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">TOTAL</span>
                                        <span class="info-box-number" id="detalle_total_ventas">S/. 0.00</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- LECTURAS -->
                <div class="card">
                    <div class="card-header" style="background-color:#6c757d; color:white">
                        <h3 class="card-title"><i class="fas fa-list"></i> Lecturas de Surtidores</h3>
                    </div>
                    <div class="card-body">
                        <table id="tabla_detalle_lecturas" class="table table-sm table-bordered" style="width:100%">
                            <thead style="background-color:#6c757d; color:white">
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
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times"></i> Cerrar</button>
                <button type="button" class="btn btn-primary" onclick="Imprimir_Reporte()"><i class="fas fa-print"></i> Imprimir</button>
            </div>
        </div>
    </div>
</div>

<script>
var tabla_historial_turnos;

function Listar_Historial_Turnos() {
    tabla_historial_turnos = $("#tabla_historial_turnos").DataTable({
        "ordering": true,
        "bLengthChange": true,
        "searching": { "regex": false },
        "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
        "pageLength": 10,
        "destroy": true,
        "async": false,
        "processing": true,
        "ajax": {
            "url": "../controller/turnos/controlador_listar_turnos.php",
            type: 'POST'
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
            { "data": "grifero_nombre" },
            { 
                "data": "hora_inicio",
                "render": function(data, type, row) {
                    return data + ' - ' + row.hora_fin;
                }
            },
            { 
                "data": "total_ventas",
                "render": function(data) {
                    return 'S/. ' + parseFloat(data).toFixed(2);
                }
            },
            { 
                "data": "total_pagos",
                "render": function(data) {
                    return 'S/. ' + parseFloat(data).toFixed(2);
                }
            },
            { 
                "data": "monto_faltante",
                "render": function(data) {
                    var faltante = parseFloat(data);
                    if (faltante > 0) {
                        return '<span class="badge badge-danger">S/. ' + faltante.toFixed(2) + '</span>';
                    } else if (faltante < 0) {
                        return '<span class="badge badge-success">S/. ' + Math.abs(faltante).toFixed(2) + '</span>';
                    } else {
                        return '<span class="badge badge-secondary">S/. 0.00</span>';
                    }
                }
            },
            {
                "data": "estado",
                "render": function(data) {
                    if (data == 'ABIERTO') {
                        return '<span class="badge badge-warning">ABIERTO</span>';
                    } else {
                        return '<span class="badge badge-success">CERRADO</span>';
                    }
                }
            },
            {
                "defaultContent": "<button class='ver btn btn-info btn-sm' title='Ver Detalle'><i class='fas fa-eye'></i></button>&nbsp;<button class='imprimir btn btn-primary btn-sm' title='Imprimir'><i class='fas fa-print'></i></button>"
            }
        ],
        "language": idioma_espanol,
        select: true
    });

    document.getElementById("tabla_historial_turnos").addEventListener("click", function(e) {
        if (e.target.closest(".ver")) {
            var data = tabla_historial_turnos.row(e.target.closest("tr")).data();
            Ver_Detalle_Turno(data.id_reporte);
        }
        if (e.target.closest(".imprimir")) {
            var data = tabla_historial_turnos.row(e.target.closest("tr")).data();
            Imprimir_Reporte(data.id_reporte);
        }
    });
}

function Ver_Detalle_Turno(id_reporte) {
    // Aquí cargarías los detalles del turno
    $("#modal_detalle_turno").modal('show');
}

function Imprimir_Reporte(id_reporte) {
    window.open('../reportes/reporte_turno.php?id=' + id_reporte, '_blank');
}

function Filtrar_Turnos() {
    tabla_historial_turnos.ajax.reload();
}

$(document).ready(function() {
    Listar_Historial_Turnos();
    
    // Establecer fechas por defecto (último mes)
    var hoy = new Date();
    var hace_mes = new Date();
    hace_mes.setMonth(hace_mes.getMonth() - 1);
    
    $('#filtro_fecha_inicio').val(hace_mes.toISOString().split('T')[0]);
    $('#filtro_fecha_fin').val(hoy.toISOString().split('T')[0]);
});
</script>
