<script src="../js/console_turnos.js?rev=<?php echo time(); ?>"></script>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-check-circle"></i> <b>VALIDAR REPORTES</b></h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="../view/index.php">Inicio</a></li>
                    <li class="breadcrumb-item active">Validar Reportes</li>
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
                    <i class="fas fa-info-circle"></i> <strong>Reportes Pendientes de Validación</strong><br>
                    Revisa y valida los turnos cerrados por los griferos.
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header" style="background: linear-gradient(135deg, #ffc107, #ff9800)">
                        <h3 class="card-title" style="color:white"><i class="fas fa-clock"></i> Turnos Pendientes de Validación</h3>
                    </div>
                    <div class="card-body">
                        <table id="tabla_reportes_pendientes" class="table table-striped table-bordered table-hover" style="width:100%">
                            <thead style="background-color:#ffc107; color:black">
                                <tr>
                                    <th>N° Documento</th>
                                    <th>Fecha</th>
                                    <th>Turno</th>
                                    <th>Grifero</th>
                                    <th>Total Ventas</th>
                                    <th>Faltante</th>
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

<!-- MODAL VALIDAR -->
<div class="modal fade" id="modal_validar_reporte" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #28a745, #218838); color:white">
                <h5 class="modal-title"><i class="fas fa-check-circle"></i> Validar Reporte</h5>
                <button type="button" class="close" data-dismiss="modal" style="color:white">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="txt_id_reporte_validar">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i> Al validar este reporte, confirmas que los datos son correctos.
                </div>
                <div class="form-group">
                    <label>Observaciones (opcional)</label>
                    <textarea class="form-control" id="txt_observaciones_validacion" rows="3" placeholder="Observaciones sobre la validación"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times"></i> Cerrar</button>
                <button type="button" class="btn btn-success" onclick="Confirmar_Validacion()"><i class="fas fa-check"></i> Validar</button>
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
var tabla_reportes_pendientes;

function Listar_Reportes_Pendientes() {
    tabla_reportes_pendientes = $("#tabla_reportes_pendientes").DataTable({
        "ordering": true,
        "bLengthChange": true,
        "searching": { "regex": false },
        "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
        "pageLength": 10,
        "destroy": true,
        "processing": true,
        "ajax": {
            "url": "../controller/turnos/controlador_reportes_pendientes.php",
            type: 'POST'
        },
        "columns": [
            { "data": "numero_documento" },
            { 
                "data": "fecha_apertura",
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
            { "data": "grifero" },
            { 
                "data": "monto_total",
                "render": function(data) {
                    return 'S/. ' + parseFloat(data || 0).toFixed(2);
                }
            },
            { 
                "data": "faltante",
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
                "data": "id_turno",
                "render": function(data) {
                    return "<button class='ver btn btn-info btn-sm' title='Ver Detalle'><i class='fas fa-eye'></i></button>&nbsp;<button class='validar btn btn-success btn-sm' title='Validar'><i class='fas fa-check'></i></button>&nbsp;<button class='imprimir btn btn-primary btn-sm' title='Imprimir'><i class='fas fa-print'></i></button>";
                }
            }
        ],
        "language": idioma_espanol,
        select: true
    });

    document.getElementById("tabla_reportes_pendientes").addEventListener("click", function(e) {
        if (e.target.closest(".ver")) {
            var data = tabla_reportes_pendientes.row(e.target.closest("tr")).data();
            Ver_Detalle_Turno(data.id_turno);
        }
        if (e.target.closest(".validar")) {
            var data = tabla_reportes_pendientes.row(e.target.closest("tr")).data();
            Abrir_Modal_Validar(data.id_turno);
        }
        if (e.target.closest(".imprimir")) {
            var data = tabla_reportes_pendientes.row(e.target.closest("tr")).data();
            Imprimir_Reporte(data.id_turno);
        }
    });
}

function Abrir_Modal_Validar(id_reporte) {
    $("#txt_id_reporte_validar").val(id_reporte);
    $("#txt_observaciones_validacion").val('');
    $("#modal_validar_reporte").modal('show');
}

function Confirmar_Validacion() {
    var id_reporte = $("#txt_id_reporte_validar").val();
    var observaciones = $("#txt_observaciones_validacion").val();
    
    $.ajax({
        url: '../controller/turnos/controlador_validar_reporte.php',
        type: 'POST',
        data: {
            id_reporte: id_reporte,
            observaciones: observaciones
        }
    }).done(function(resp) {
        if (resp > 0) {
            Swal.fire({
                icon: 'success',
                title: 'Éxito',
                text: 'Reporte validado correctamente',
                confirmButtonColor: '#023D77'
            });
            $("#modal_validar_reporte").modal('hide');
            tabla_reportes_pendientes.ajax.reload();
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'No se pudo validar el reporte',
                confirmButtonColor: '#023D77'
            });
        }
    });
}

$(document).ready(function() {
    Listar_Reportes_Pendientes();
});
</script>
