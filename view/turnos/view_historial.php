<?php require_once __DIR__ . '/../../controller/_bootstrap.php'; ?>
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
        <form id="filtro_historial" class="d-flex flex-wrap align-items-end mb-3" style="gap: .5rem;">
            <div><label for="filtro_fecha_inicio" class="d-block mb-1">Desde</label><input type="date" class="form-control form-control-sm" id="filtro_fecha_inicio" required></div>
            <div><label for="filtro_fecha_fin" class="d-block mb-1">Hasta</label><input type="date" class="form-control form-control-sm" id="filtro_fecha_fin" required></div>
            <div>
                <span class="d-block mb-1 font-weight-bold" id="filtro_estado_titulo">Estado</span>
                <div class="g-segmento" role="radiogroup" aria-labelledby="filtro_estado_titulo">
                    <label><input type="radio" name="filtro_estado" value="" checked><span>Todos</span></label>
                    <label><input type="radio" name="filtro_estado" value="ABIERTO"><span>Abiertos</span></label>
                    <label><input type="radio" name="filtro_estado" value="CERRADO"><span>Cerrados</span></label>
                </div>
            </div>
            <button class="btn btn-primary btn-sm" type="submit"><i class="fas fa-search" aria-hidden="true"></i> Buscar</button>
        </form>

        <div class="row" id="historial_resumen" aria-live="polite"></div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                <table id="tabla_historial_turnos" class="table table-hover" style="width:100%">
                    <thead>
                        <tr>
                            <th>Documento</th>
                            <th>Fecha</th>
                            <th>Turno</th>
                            <th>Grifero</th>
                            <th>Horario</th>
                            <th class="text-right">Ventas</th>
                            <th class="text-right">Pagos</th>
                            <th>Cuadre</th>
                            <th>Estado</th>
                            <th><span class="sr-only">Acciones</span></th>
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

                <!-- OTROS CONCEPTOS -->
                <div class="card card-warning">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-coins"></i> Otros Conceptos</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="info-box bg-danger">
                                    <span class="info-box-icon"><i class="fas fa-minus-circle"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">DESCUENTOS</span>
                                        <span class="info-box-number detalle_descuentos">S/. 0.00</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="info-box bg-secondary">
                                    <span class="info-box-icon"><i class="fas fa-file-invoice-dollar"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">OTROS GASTOS</span>
                                        <span class="info-box-number detalle_otros_gastos">S/. 0.00</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="info-box bg-success">
                                    <span class="info-box-icon"><i class="fas fa-money-bill-wave"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">EFECTIVO</span>
                                        <span class="info-box-number detalle_monto_efectivo">S/. 0.00</span>
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
                        <div class="table-responsive">
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
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times"></i> Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL ELIMINAR TURNO -->
<div class="modal fade" id="modal_eliminar_turno" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #dc3545, #c82333); color:white">
                <h5 class="modal-title"><i class="fas fa-exclamation-triangle"></i> Eliminar Turno</h5>
                <button type="button" class="close" data-dismiss="modal" style="color:white">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="txt_id_reporte_eliminar">
                
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-circle"></i> <strong>¡Advertencia!</strong><br>
                    Esta acción eliminará permanentemente el turno y todos sus datos relacionados (lecturas, pagos, créditos).<br><br>
                    <strong>Las lecturas de los surtidores serán revertidas</strong> a su estado anterior al turno.
                </div>
                
                <div class="form-group">
                    <label>Motivo de Eliminación <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="txt_motivo_eliminacion" rows="3" placeholder="Ingrese el motivo por el cual está eliminando este turno..." required></textarea>
                    <small class="text-muted">Este motivo quedará registrado en los logs del sistema.</small>
                </div>
                
                <div class="alert alert-danger">
                    <i class="fas fa-ban"></i> <strong>Esta acción NO se puede deshacer.</strong>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <button type="button" class="btn btn-danger" onclick="Eliminar_Turno()">
                    <i class="fas fa-trash"></i> Eliminar Turno
                </button>
            </div>
        </div>
    </div>
</div>


<script>
var tabla_historial_turnos;

// monto_faltante negativo es faltante y positivo es sobrante (diferencia = lo justificado menos la venta neta).
function Cuadre_Turno(valor) {
    var v = parseFloat(valor || 0);
    if (Math.abs(v) < 0.005) { return '<span class="g-estado-cuadre" data-estado="cuadra"><i class="fas fa-check" aria-hidden="true"></i> Cuadra</span>'; }
    return v < 0
        ? '<span class="g-estado-cuadre" data-estado="falta"><i class="fas fa-arrow-down" aria-hidden="true"></i> Falta ' + G.soles(-v) + '</span>'
        : '<span class="g-estado-cuadre" data-estado="sobra"><i class="fas fa-plus" aria-hidden="true"></i> Sobra ' + G.soles(v) + '</span>';
}

function Resumen_Historial(filas) {
    var cerrados = filas.filter(function (t) { return t.estado === 'CERRADO'; });
    var suma = function (lista, f) { return lista.reduce(function (s, t) { return s + f(t); }, 0); };
    var ventas = suma(cerrados, function (t) { return Number(t.total_ventas) || 0; });
    var faltantes = suma(cerrados, function (t) { var v = Number(t.monto_faltante) || 0; return v < 0 ? -v : 0; });
    var conFaltante = cerrados.filter(function (t) { return Number(t.monto_faltante) < -0.005; }).length;
    var abiertos = filas.length - cerrados.length;
    var tarjeta = function (titulo, valor, ayuda, clase) {
        return $('<div class="col-6 col-xl-3"></div>').append($('<div class="card mb-3"></div>').append($('<div class="card-body py-3"></div>').append(
            $('<div class="small text-muted"></div>').text(titulo), $('<div class="g-cifra"></div>').addClass(clase || '').text(valor), $('<div class="small text-muted"></div>').text(ayuda))));
    };
    $('#historial_resumen').empty().append(
        tarjeta('Turnos', String(filas.length), abiertos ? abiertos + ' abierto(s)' : 'Todos cerrados'),
        tarjeta('Ventas de turnos cerrados', G.soles(ventas), cerrados.length + ' turno(s) cerrados'),
        tarjeta('Faltantes', G.soles(faltantes), conFaltante ? conFaltante + ' turno(s) con faltante' : 'Ningún turno con faltante', faltantes > 0 ? 'text-danger' : ''),
        tarjeta('Promedio por turno', G.soles(cerrados.length ? ventas / cerrados.length : 0), 'Turnos cerrados')
    );
}

function Listar_Historial_Turnos() {
    if (tabla_historial_turnos) {
        tabla_historial_turnos.destroy();
    }
    tabla_historial_turnos = $("#tabla_historial_turnos").DataTable({
        "ordering": true,
        "order": [], // Respeta el orden del servidor
        "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Todos"]],
        "pageLength": 25,
        "destroy": true,
        "processing": true,
        "ajax": {
            "url": "../controller/turnos/controlador_listar_turnos.php",
            type: 'POST',
            data: {
                filtro_fecha_inicio: $("#filtro_fecha_inicio").val(),
                filtro_fecha_fin: $("#filtro_fecha_fin").val(),
                filtro_estado: $('input[name=filtro_estado]:checked').val() || null,
                filtro_usuario: null,
                filtro_validacion: null
            },
            "dataSrc": function (r) { var filas = (r && r.data) || []; Resumen_Historial(filas); return filas; }
        },
        "columns": [
            { "data": "numero_documento", "render": function (d, tipo) { return tipo === 'display' ? '<span class="font-weight-bold text-nowrap">' + Texto_Seguro(d) + '</span>' : d; } },
            { "data": "fecha_reporte", "render": function (d, tipo) { return tipo === 'display' ? Fecha_Local(d).toLocaleDateString('es-PE') : d; } },
            { "data": "turno", "render": function (d) { return d == 'DIA' ? '<span class="badge badge-light">Día</span>' : '<span class="badge badge-dark">Noche</span>'; } },
            { "data": "grifero_nombre" },
            { "data": "hora_inicio", "render": function (d, tipo, row) { return '<span class="text-nowrap">' + Texto_Seguro(String(d || '').slice(0, 5)) + ' – ' + Texto_Seguro(String(row.hora_fin || '').slice(0, 5)) + '</span>'; } },
            { "data": "total_ventas", "className": "text-right text-nowrap", "render": function (d, tipo) { return tipo === 'display' ? G.soles(d) : Number(d); } },
            { "data": "total_pagos", "className": "text-right text-nowrap", "render": function (d, tipo) { return tipo === 'display' ? G.soles(d) : Number(d); } },
            { "data": "monto_faltante", "render": function (d, tipo, row) { return tipo === 'display' ? (row.estado === 'ABIERTO' ? '<span class="text-muted">—</span>' : Cuadre_Turno(d)) : Number(d); } },
            { "data": "estado", "render": function (d) { return d == 'ABIERTO' ? '<span class="badge badge-warning">Abierto</span>' : '<span class="badge badge-success">Cerrado</span>'; } },
            {
                "data": null, "orderable": false, "className": "text-nowrap",
                "render": function (data, type, row) {
                    var doc = Texto_Seguro(row.numero_documento);
                    var botones = "<button type='button' class='ver btn btn-outline-secondary btn-sm' aria-label='Ver el turno " + doc + "' title='Ver detalle'><i class='fas fa-eye' aria-hidden='true'></i></button> ";
                    botones += "<button type='button' class='imprimir btn btn-outline-secondary btn-sm' aria-label='Imprimir el turno " + doc + "' title='Imprimir'><i class='fas fa-print' aria-hidden='true'></i></button>";
                    if (G.puede('turnos_eliminar')) {
                        botones += " <button type='button' class='eliminar btn btn-outline-danger btn-sm' aria-label='Eliminar el turno " + doc + "' title='Eliminar'><i class='fas fa-trash' aria-hidden='true'></i></button>";
                    }
                    return botones;
                }
            }
        ],
        "language": idioma_espanol
    });
}

function Imprimir_Reporte(id_reporte) {
    window.open("../view/MPDF/REPORTE/reporte_turno.php?id=" + encodeURIComponent(id_reporte), "_blank", "noopener");
}

function Abrir_Modal_Eliminar_Turno(id_reporte, numero_documento) {
    $("#txt_id_reporte_eliminar").val(id_reporte);
    $("#txt_motivo_eliminacion").val('');
    $("#modal_eliminar_turno .modal-title").text('Eliminar el turno ' + numero_documento);
    $("#modal_eliminar_turno").modal('show');
}

function Eliminar_Turno() {
    var id_reporte = $("#txt_id_reporte_eliminar").val();
    var motivo = $("#txt_motivo_eliminacion").val().trim();
    if (motivo.length < 10) {
        Swal.fire({ icon: 'warning', title: 'Revise el motivo', text: 'Explique el motivo con al menos 10 caracteres; queda en la auditoría.' });
        return;
    }
    Swal.fire({ title: 'Eliminando turno…', allowOutsideClick: false, allowEscapeKey: false, didOpen: function () { Swal.showLoading(); } });
    $.ajax({
        url: '../controller/turnos/controlador_eliminar_turno.php',
        type: 'POST',
        dataType: 'json',
        data: { id_reporte: id_reporte, motivo: motivo }
    }).done(function (resp) {
        if (resp.success) {
            $("#modal_eliminar_turno").modal('hide');
            Swal.fire({ icon: 'success', title: 'Turno eliminado', text: resp.message });
            Listar_Historial_Turnos();
        } else {
            Swal.fire({ icon: 'error', title: 'No se pudo eliminar', text: resp.message });
        }
    }).fail(function () {
        Swal.fire({ icon: 'error', title: 'Sin conexión', text: 'No se pudo conectar con el servidor.' });
    });
}

function Filtrar_Turnos() {
    Listar_Historial_Turnos();
}

$(document).ready(function () {
    // Por defecto, el último mes. Las fechas se ponen antes de la primera consulta.
    var hace_mes = new Date();
    hace_mes.setMonth(hace_mes.getMonth() - 1);
    $('#filtro_fecha_inicio').val(Fecha_ISO_Local(hace_mes));
    $('#filtro_fecha_fin').val(G.hoy());

    $('#filtro_historial').on('submit', function (e) { e.preventDefault(); Listar_Historial_Turnos(); });
    $('input[name=filtro_estado]').on('change', Listar_Historial_Turnos);

    // Un solo manejador para los botones de la tabla, aunque la tabla se vuelva a crear.
    $('#tabla_historial_turnos').on('click', 'button', function () {
        var data = tabla_historial_turnos.row($(this).closest('tr')).data();
        if (!data) { return; }
        if ($(this).hasClass('ver')) { Ver_Detalle_Turno(data.id_reporte); }
        if ($(this).hasClass('imprimir')) { Imprimir_Reporte(data.id_reporte); }
        if ($(this).hasClass('eliminar')) { Abrir_Modal_Eliminar_Turno(data.id_reporte, data.numero_documento); }
    });

    Listar_Historial_Turnos();
});
</script>
