<script src="../js/console_reportes.js?rev=<?php echo time(); ?>"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-chart-line"></i> <b>DASHBOARD Y REPORTES</b></h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="../view/index.php">Inicio</a></li>
                    <li class="breadcrumb-item active">Dashboard</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="content">
    <div class="container-fluid">
        
        <!-- INDICADORES DEL DÍA -->
        <div class="row">
            <div class="col-md-3">
                <div class="info-box bg-info">
                    <span class="info-box-icon"><i class="fas fa-clock"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Turnos Activos Hoy</span>
                        <span class="info-box-number" id="total_turnos_activos">0</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="info-box bg-success">
                    <span class="info-box-icon"><i class="fas fa-dollar-sign"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Ventas del Día</span>
                        <span class="info-box-number" id="total_ventas_dia">S/. 0.00</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="info-box bg-warning">
                    <span class="info-box-icon"><i class="fas fa-credit-card"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Créditos Pendientes</span>
                        <span class="info-box-number" id="total_creditos_pendientes">0</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="info-box bg-danger">
                    <span class="info-box-icon"><i class="fas fa-file-alt"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Reportes por Validar</span>
                        <span class="info-box-number" id="total_reportes_pendientes">0</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- GRÁFICO DE VENTAS ÚLTIMOS 7 DÍAS -->
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header" style="background: linear-gradient(135deg, #023D77, #0266C8)">
                        <h3 class="card-title" style="color:white"><i class="fas fa-chart-line"></i> Ventas de los Últimos 7 Días</h3>
                    </div>
                    <div class="card-body">
                        <canvas id="grafico_ventas_semana" style="height: 300px;"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- VENTAS POR COMBUSTIBLE (MES ACTUAL) -->
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header" style="background-color:#28a745; color:white">
                        <h3 class="card-title"><i class="fas fa-gas-pump"></i> Ventas por Combustible - Mes Actual</h3>
                    </div>
                    <div class="card-body">
                        <canvas id="grafico_combustibles" style="height: 250px;"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header" style="background-color:#ffc107; color:black">
                        <h3 class="card-title"><i class="fas fa-money-bill-wave"></i> Métodos de Pago - Mes</h3>
                    </div>
                    <div class="card-body">
                        <canvas id="grafico_metodos_pago" style="height: 250px;"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- VENTAS POR TURNO -->
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header" style="background-color:#17a2b8; color:white">
                        <h3 class="card-title"><i class="fas fa-clock"></i> Comparativo por Turno - Mes Actual</h3>
                    </div>
                    <div class="card-body">
                        <canvas id="grafico_turnos" style="height: 250px;"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header" style="background-color:#6c757d; color:white">
                        <h3 class="card-title"><i class="fas fa-users"></i> Desempeño por Grifero - Mes Actual</h3>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm table-bordered">
                            <thead style="background-color:#6c757d; color:white">
                                <tr>
                                    <th>Grifero</th>
                                    <th>Turnos</th>
                                    <th>Total Ventas</th>
                                    <th>Promedio</th>
                                </tr>
                            </thead>
                            <tbody id="tabla_desempeno_griferos">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- RESUMEN MENSUAL -->
        <div class="row">
            <div class="col-md-12">
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-calendar-alt"></i> Resumen del Mes Actual</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="info-box">
                                    <span class="info-box-icon bg-dark"><i class="fas fa-gas-pump"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Diesel</span>
                                        <span class="info-box-number" id="mes_total_diesel">S/. 0.00</span>
                                        <small id="mes_galones_diesel">0 gal</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="info-box">
                                    <span class="info-box-icon bg-info"><i class="fas fa-gas-pump"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Regular</span>
                                        <span class="info-box-number" id="mes_total_regular">S/. 0.00</span>
                                        <small id="mes_galones_regular">0 gal</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="info-box">
                                    <span class="info-box-icon bg-warning"><i class="fas fa-gas-pump"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Premium</span>
                                        <span class="info-box-number" id="mes_total_premium">S/. 0.00</span>
                                        <small id="mes_galones_premium">0 gal</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="info-box">
                                    <span class="info-box-icon bg-success"><i class="fas fa-dollar-sign"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Total Mes</span>
                                        <span class="info-box-number" id="mes_total_ventas">S/. 0.00</span>
                                        <small id="mes_total_galones">0 gal</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- CRÉDITOS DEL MES -->
        <div class="row">
            <div class="col-md-12">
                <div class="card card-danger">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-credit-card"></i> Créditos del Mes</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <p><strong>Total Créditos:</strong> <span id="mes_total_creditos">0</span></p>
                            </div>
                            <div class="col-md-3">
                                <p><strong>Monto Total:</strong> <span id="mes_monto_creditos">S/. 0.00</span></p>
                            </div>
                            <div class="col-md-3">
                                <p><strong>Monto Pagado:</strong> <span id="mes_pagado_creditos">S/. 0.00</span></p>
                            </div>
                            <div class="col-md-3">
                                <p><strong>Saldo Pendiente:</strong> <span id="mes_saldo_creditos" class="text-danger">S/. 0.00</span></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ACCESOS RÁPIDOS -->
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header" style="background-color:#6c757d; color:white">
                        <h3 class="card-title"><i class="fas fa-bolt"></i> Accesos Rápidos</h3>
                    </div>
                    <div class="card-body">
                        <a href="#" onclick="cargar_contenido('contenido_principal','reportes/view_reporte_diario.php')" class="btn btn-app btn-lg">
                            <i class="fas fa-calendar-day"></i> Reporte Diario
                        </a>
                        <a href="#" onclick="cargar_contenido('contenido_principal','reportes/view_reporte_mensual.php')" class="btn btn-app btn-lg">
                            <i class="fas fa-calendar-alt"></i> Reporte Mensual
                        </a>
                        <a href="#" onclick="cargar_contenido('contenido_principal','turnos/view_historial.php')" class="btn btn-app btn-lg">
                            <i class="fas fa-history"></i> Historial Turnos
                        </a>
                        <a href="#" onclick="cargar_contenido('contenido_principal','creditos/view_creditos_pendientes.php')" class="btn btn-app btn-lg">
                            <i class="fas fa-credit-card"></i> Créditos
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    Cargar_Dashboard();
});
</script>
