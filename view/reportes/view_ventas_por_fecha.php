<script src="../js/console_reportes.js?rev=<?php echo time(); ?>"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

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

            <!-- DETALLE POR COMBUSTIBLE -->
            <div class="row">
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header" style="background-color:#343a40; color:white">
                            <h3 class="card-title"><i class="fas fa-gas-pump"></i> DIESEL</h3>
                        </div>
                        <div class="card-body">
                            <p><strong>Galones:</strong> <span id="galones_diesel" class="text-primary" style="font-size:20px">0.000</span> gal</p>
                            <p><strong>Total:</strong> <span id="soles_diesel" class="text-success" style="font-size:20px">S/. 0.00</span></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header" style="background-color:#17a2b8; color:white">
                            <h3 class="card-title"><i class="fas fa-gas-pump"></i> REGULAR</h3>
                        </div>
                        <div class="card-body">
                            <p><strong>Galones:</strong> <span id="galones_regular" class="text-primary" style="font-size:20px">0.000</span> gal</p>
                            <p><strong>Total:</strong> <span id="soles_regular" class="text-success" style="font-size:20px">S/. 0.00</span></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header" style="background-color:#ffc107; color:black">
                            <h3 class="card-title"><i class="fas fa-gas-pump"></i> PREMIUM</h3>
                        </div>
                        <div class="card-body">
                            <p><strong>Galones:</strong> <span id="galones_premium" class="text-primary" style="font-size:20px">0.000</span> gal</p>
                            <p><strong>Total:</strong> <span id="soles_premium" class="text-success" style="font-size:20px">S/. 0.00</span></p>
                        </div>
                    </div>
                </div>
            </div>

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
    
    $("#filtro_fecha_inicio_ventas").val(hace_mes.toISOString().split('T')[0]);
    $("#filtro_fecha_fin_ventas").val(hoy.toISOString().split('T')[0]);
    
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
            opciones += '<option value="' + item.id_usuario + '">' + item.nombre_completo + '</option>';
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
        
        // Actualizar por combustible
        $("#galones_diesel").text(parseFloat(data.resumen.galones_diesel).toFixed(3));
        $("#soles_diesel").text('S/. ' + parseFloat(data.resumen.soles_diesel).toFixed(2));
        
        $("#galones_regular").text(parseFloat(data.resumen.galones_regular).toFixed(3));
        $("#soles_regular").text('S/. ' + parseFloat(data.resumen.soles_regular).toFixed(2));
        
        $("#galones_premium").text(parseFloat(data.resumen.galones_premium).toFixed(3));
        $("#soles_premium").text('S/. ' + parseFloat(data.resumen.soles_premium).toFixed(2));
        
        // Actualizar gráficos
        Actualizar_Graficos(data.resumen);
        
        // Actualizar tabla detalle
        Actualizar_Tabla_Detalle(data.turnos);
        
        // Actualizar tabla por grifero
        Actualizar_Tabla_Griferos(data.por_grifero);
    });
}

// ACTUALIZAR GRÁFICOS
function Actualizar_Graficos(resumen) {
    // Destruir gráficos anteriores
    if (grafico_galones_chart) grafico_galones_chart.destroy();
    if (grafico_soles_chart) grafico_soles_chart.destroy();
    
    // Gráfico de Galones
    var ctx_galones = document.getElementById('grafico_galones').getContext('2d');
    grafico_galones_chart = new Chart(ctx_galones, {
        type: 'doughnut',
        data: {
            labels: ['Diesel', 'Regular', 'Premium'],
            datasets: [{
                data: [
                    parseFloat(resumen.galones_diesel),
                    parseFloat(resumen.galones_regular),
                    parseFloat(resumen.galones_premium)
                ],
                backgroundColor: ['#343a40', '#17a2b8', '#ffc107']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });
    
    // Gráfico de Soles
    var ctx_soles = document.getElementById('grafico_soles').getContext('2d');
    grafico_soles_chart = new Chart(ctx_soles, {
        type: 'doughnut',
        data: {
            labels: ['Diesel', 'Regular', 'Premium'],
            datasets: [{
                data: [
                    parseFloat(resumen.soles_diesel),
                    parseFloat(resumen.soles_regular),
                    parseFloat(resumen.soles_premium)
                ],
                backgroundColor: ['#343a40', '#17a2b8', '#ffc107']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom' }
            }
        }
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
                    var fecha = new Date(data);
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
    var total_galones = turnos.reduce((sum, t) => sum + parseFloat(t.total_galones), 0);
    var total_soles = turnos.reduce((sum, t) => sum + parseFloat(t.total_ventas), 0);
    
    $("#foot_diesel").text(total_diesel.toFixed(3));
    $("#foot_regular").text(total_regular.toFixed(3));
    $("#foot_premium").text(total_premium.toFixed(3));
    $("#foot_total_galones").text(total_galones.toFixed(3));
    $("#foot_total_soles").text('S/. ' + total_soles.toFixed(2));
}

// ACTUALIZAR TABLA POR GRIFERO
function Actualizar_Tabla_Griferos(por_grifero) {
    var html = '';
    
    por_grifero.forEach(function(item) {
        html += '<tr>';
        html += '<td>' + item.grifero_nombre + '</td>';
        html += '<td>' + item.total_turnos + '</td>';
        html += '<td>' + parseFloat(item.total_galones).toFixed(3) + ' gal</td>';
        html += '<td>S/. ' + parseFloat(item.total_ventas).toFixed(2) + '</td>';
        html += '<td>S/. ' + parseFloat(item.promedio_turno).toFixed(2) + '</td>';
        html += '</tr>';
    });
    
    $("#tbody_ventas_grifero").html(html);
}
</script>
