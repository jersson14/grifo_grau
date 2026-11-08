<?php require_once __DIR__ . '/../../controller/_bootstrap.php'; ?>
<script src="../js/console_reportes.js?rev=<?php echo time(); ?>"></script>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-chart-bar"></i> <b>VENTAS POR FECHA</b></h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="../view/index.php">Inicio</a></li>
                    <li class="breadcrumb-item active">Ventas por Fecha</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="content">
    <div class="container-fluid">

        <!-- FILTROS -->
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header" style="background: linear-gradient(135deg, #023D77, #0266C8)">
                        <h3 class="card-title" style="color:white"><i class="fas fa-filter"></i> Filtros de Búsqueda</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 form-group">
                                <label>Fecha Inicio <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="filtro_fecha_inicio_ventas">
                            </div>
                            <div class="col-md-3 form-group">
                                <label>Fecha Fin <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="filtro_fecha_fin_ventas">
                            </div>
                            <div class="col-md-3 form-group">
                                <label>Grifero</label>
                                <select class="form-control" id="filtro_grifero_ventas">
                                    <option value="">Todos los Griferos</option>
                                </select>
                            </div>
                            <div class="col-md-3 form-group">
                                <label>&nbsp;</label><br>
                                <button class="btn btn-primary btn-block" onclick="Consultar_Ventas()">
                                    <i class="fas fa-search"></i> Consultar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- RESUMEN GENERAL -->
        <div id="resumen_ventas" style="display:none">
            <div class="row">
                <div class="col-md-3">
                    <div class="info-box bg-primary">
                        <span class="info-box-icon"><i class="fas fa-tint"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Galones</span>
                            <span class="info-box-number" id="total_galones_general">0.000</span>
                            <small>galones</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="info-box bg-success">
                        <span class="info-box-icon"><i class="fas fa-dollar-sign"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Ventas</span>
                            <span class="info-box-number" id="total_soles_general">S/. 0.00</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="info-box bg-warning">
                        <span class="info-box-icon"><i class="fas fa-clock"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Turnos</span>
                            <span class="info-box-number" id="total_turnos_general">0</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="info-box bg-info">
                        <span class="info-box-icon"><i class="fas fa-calculator"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Promedio por Turno</span>
                            <span class="info-box-number" id="promedio_turno_general">S/. 0.00</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- DETALLE POR COMBUSTIBLE: una tarjeta por cada producto vendido en el periodo -->
            <div class="row" id="resumen_productos"></div>

            <!-- GRÁFICOS -->
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header" style="background-color:#28a745; color:white">
                            <h3 class="card-title"><i class="fas fa-chart-pie"></i> Distribución por Combustible (Galones)</h3>
                        </div>
                        <div class="card-body">
                            <canvas id="grafico_galones" style="height: 300px;"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header" style="background-color:#dc3545; color:white">
                            <h3 class="card-title"><i class="fas fa-chart-pie"></i> Distribución por Combustible (Soles)</h3>
                        </div>
                        <div class="card-body">
                            <canvas id="grafico_soles" style="height: 300px;"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- DETALLE POR FECHA -->
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header" style="background-color:#6c757d; color:white">
                            <h3 class="card-title"><i class="fas fa-calendar-alt"></i> Detalle por Fecha</h3>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                            <table id="tabla_ventas_detalle" class="table table-striped table-bordered table-hover" style="width:100%">
                                <thead style="background-color:#6c757d; color:white">
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Grifero</th>
                                        <th>Turno</th>
                                        <th>Diesel (gal)</th>
                                        <th>Regular (gal)</th>
                                        <th>Premium (gal)</th>
                                        <th>Otros (gal)</th>
                                        <th>Total Galones</th>
                                        <th>Total Soles</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                                <tfoot style="background-color:#f0f0f0; font-weight:bold">
                                    <tr>
                                        <td colspan="3" class="text-right">TOTALES:</td>
                                        <td id="foot_diesel">0.000</td>
                                        <td id="foot_regular">0.000</td>
                                        <td id="foot_premium">0.000</td>
                                        <td id="foot_otros">0.000</td>
                                        <td id="foot_total_galones">0.000</td>
                                        <td id="foot_total_soles">S/. 0.00</td>
                                    </tr>
                                </tfoot>
                            </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- DETALLE POR GRIFERO -->
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header" style="background-color:#17a2b8; color:white">
                            <h3 class="card-title"><i class="fas fa-users"></i> Resumen por Grifero</h3>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                            <table id="tabla_ventas_grifero" class="table table-sm table-bordered">
                                <thead style="background-color:#17a2b8; color:white">
                                    <tr>
                                        <th>Grifero</th>
                                        <th>Turnos</th>
                                        <th>Total Galones</th>
                                        <th>Total Ventas</th>
                                        <th>Promedio/Turno</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody_ventas_grifero">
                                </tbody>
                            </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
var grafico_galones_chart = null;
var grafico_soles_chart = null;

$(document).ready(function() {
    // Establecer fechas por defecto (último mes)
    var hoy = new Date();
    var hace_mes = new Date();
    hace_mes.setMonth(hace_mes.getMonth() - 1);

    $("#filtro_fecha_inicio_ventas").val(Fecha_ISO_Local(hace_mes));
    $("#filtro_fecha_fin_ventas").val(Fecha_ISO_Local(hoy));

    // Cargar griferos
    Cargar_Griferos_Filtro();
});

// CARGAR GRIFEROS
function Cargar_Griferos_Filtro() {
    $.ajax({
        url: '../controller/usuario/controlador_listar_griferos.php',
        type: 'POST'
    }).done(function(resp) {
        var data = JSON.parse(resp);
        var opciones = '<option value="">Todos los Griferos</option>';

        data.forEach(function(item) {
            opciones += '<option value="' + item.id_usuario + '">' + Texto_Seguro(item.nombre_completo) + '</option>';
        });

        $("#filtro_grifero_ventas").html(opciones);
    });
}

// CONSULTAR VENTAS
function Consultar_Ventas() {
    var fecha_inicio = $("#filtro_fecha_inicio_ventas").val();
    var fecha_fin = $("#filtro_fecha_fin_ventas").val();
    var id_grifero = $("#filtro_grifero_ventas").val();

    if (fecha_inicio == '' || fecha_fin == '') {
        Swal.fire({
            icon: 'warning',
            title: 'Campos Incompletos',
            text: 'Seleccione las fechas de inicio y fin',
            confirmButtonColor: '#023D77'
        });
        return;
    }

    $.ajax({
        url: '../controller/reportes/controlador_ventas_por_fecha.php',
        type: 'POST',
        data: {
            fecha_inicio: fecha_inicio,
            fecha_fin: fecha_fin,
            id_grifero: id_grifero
        }
    }).done(function(resp) {
        var data = JSON.parse(resp);

        if (data.turnos.length == 0) {
            Swal.fire({
                icon: 'info',
                title: 'Sin Datos',
                text: 'No se encontraron ventas en el rango de fechas seleccionado',
                confirmButtonColor: '#023D77'
            });
            $("#resumen_ventas").hide();
            return;
        }

        // Mostrar resumen
        $("#resumen_ventas").show();

        // Actualizar totales generales
        $("#total_galones_general").text(parseFloat(data.resumen.total_galones).toFixed(3));
        $("#total_soles_general").text('S/. ' + parseFloat(data.resumen.total_soles).toFixed(2));
        $("#total_turnos_general").text(data.resumen.total_turnos);
        $("#promedio_turno_general").text('S/. ' + parseFloat(data.resumen.promedio_turno).toFixed(2));

        // Una tarjeta por producto del periodo, sea cual sea su tipo.
        var caja = $("#resumen_productos").empty();
        (data.por_producto || []).forEach(function (p) {
            caja.append($('<div class="col-sm-6 col-lg-4"></div>').append($('<div class="card"></div>').append(
                $('<div class="card-header"></div>').append($('<h3 class="card-title"></h3>').text(p.nombre)),
                $('<div class="card-body"></div>').append(
                    $('<p class="mb-1"></p>').append($('<strong></strong>').text('Galones: '), document.createTextNode(parseFloat(p.galones).toFixed(3) + ' gal')),
                    $('<p class="mb-0"></p>').append($('<strong></strong>').text('Total: '), document.createTextNode('S/. ' + parseFloat(p.total).toFixed(2)))
                )
            )));
        });

        // Actualizar gráficos
        Actualizar_Graficos(data.por_producto || []);

        // Actualizar tabla detalle
        Actualizar_Tabla_Detalle(data.turnos);

        // Actualizar tabla por grifero
        Actualizar_Tabla_Griferos(data.por_grifero);
    });
}

// ACTUALIZAR GRÁFICOS: uno por galones y otro por soles, con un sector por producto.
function Actualizar_Graficos(productos) {
    if (grafico_galones_chart) grafico_galones_chart.destroy();
    if (grafico_soles_chart) grafico_soles_chart.destroy();
    var COLORES = { DIESEL: '#2563eb', REGULAR: '#059669', PREMIUM: '#f59e0b' };
    var OTROS = ['#71717a', '#a1a1aa', '#52525b', '#d4d4d8'];
    var colores = productos.map(function (p, i) { return COLORES[p.tipo] || OTROS[i % OTROS.length]; });
    var nombres = productos.map(function (p) { return p.nombre; });
    var opciones = { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } };
    grafico_galones_chart = new Chart(document.getElementById('grafico_galones').getContext('2d'), {
        type: 'doughnut',
        data: { labels: nombres, datasets: [{ data: productos.map(function (p) { return parseFloat(p.galones); }), backgroundColor: colores }] },
        options: opciones
    });
    grafico_soles_chart = new Chart(document.getElementById('grafico_soles').getContext('2d'), {
        type: 'doughnut',
        data: { labels: nombres, datasets: [{ data: productos.map(function (p) { return parseFloat(p.total); }), backgroundColor: colores }] },
        options: opciones
    });
}

// ACTUALIZAR TABLA DETALLE
function Actualizar_Tabla_Detalle(turnos) {
    $("#tabla_ventas_detalle").DataTable({
        "data": turnos,
        "destroy": true,
        "ordering": true,
        "pageLength": 25,
        "columns": [
            {
                "data": "fecha_reporte",
                "render": function(data) {
                    var fecha = Fecha_Local(data);
                    return fecha.toLocaleDateString('es-PE');
                }
            },
            { "data": "grifero_nombre" },
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
                "data": "galones_diesel",
                "render": function(data) {
                    return parseFloat(data).toFixed(3);
                }
            },
            {
                "data": "galones_regular",
                "render": function(data) {
                    return parseFloat(data).toFixed(3);
                }
            },
            {
                "data": "galones_premium",
                "render": function(data) {
                    return parseFloat(data).toFixed(3);
                }
            },
            {
                "data": "galones_otros",
                "render": function(data) {
                    return parseFloat(data || 0).toFixed(3);
                }
            },
            {
                "data": "total_galones",
                "render": function(data) {
                    return '<strong>' + parseFloat(data).toFixed(3) + '</strong>';
                }
            },
            {
                "data": "total_ventas",
                "render": function(data) {
                    return '<strong>S/. ' + parseFloat(data).toFixed(2) + '</strong>';
                }
            }
        ],
        "language": idioma_espanol
    });

    // Actualizar totales del footer
    var total_diesel = turnos.reduce((sum, t) => sum + parseFloat(t.galones_diesel), 0);
    var total_regular = turnos.reduce((sum, t) => sum + parseFloat(t.galones_regular), 0);
    var total_premium = turnos.reduce((sum, t) => sum + parseFloat(t.galones_premium), 0);
    var total_otros = turnos.reduce((sum, t) => sum + parseFloat(t.galones_otros || 0), 0);
    var total_galones = turnos.reduce((sum, t) => sum + parseFloat(t.total_galones), 0);
    var total_soles = turnos.reduce((sum, t) => sum + parseFloat(t.total_ventas), 0);

    $("#foot_diesel").text(total_diesel.toFixed(3));
    $("#foot_regular").text(total_regular.toFixed(3));
    $("#foot_premium").text(total_premium.toFixed(3));
    $("#foot_otros").text(total_otros.toFixed(3));
    $("#foot_total_galones").text(total_galones.toFixed(3));
    $("#foot_total_soles").text('S/. ' + total_soles.toFixed(2));
}

// ACTUALIZAR TABLA POR GRIFERO
function Actualizar_Tabla_Griferos(por_grifero) {
    var html = '';

    por_grifero.forEach(function(item) {
        html += '<tr>';
        html += '<td>' + Texto_Seguro(item.grifero_nombre) + '</td>';
        html += '<td>' + item.total_turnos + '</td>';
        html += '<td>' + parseFloat(item.total_galones).toFixed(3) + ' gal</td>';
        html += '<td>S/. ' + parseFloat(item.total_ventas).toFixed(2) + '</td>';
        html += '<td>S/. ' + parseFloat(item.promedio_turno).toFixed(2) + '</td>';
        html += '</tr>';
    });

    $("#tbody_ventas_grifero").html(html);
}
</script>
