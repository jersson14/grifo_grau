<?php
require_once __DIR__ . '/../../controller/_bootstrap.php';
require_once __DIR__ . '/../../model/model_inicio.php';

// Qué ve cada uno: las cifras de todo el grifo solo con «Reportes gerenciales»; el grifero (sin «ver todos») ve su resumen.
$bloques = Modelo_Inicio::Elegidos();
$estacion = puede('reportes_gerenciales');
$ver = fn(string $b) => in_array($b, $bloques, true) && ($b === 'accesos' || ($b === 'mi_resumen' ? !puede('ver_todos') : $estacion));
$accesos = array_values(array_filter([
    ['vista' => 'turnos/view_registrar_turno.php', 'icono' => 'fa-clipboard-check', 'texto' => 'Registrar turno'],
    ['vista' => 'ventas/view_ventas.php', 'icono' => 'fa-cash-register', 'texto' => 'Registrar venta'],
    ['vista' => 'reportes/view_validar_reportes.php', 'icono' => 'fa-check-double', 'texto' => 'Validar turnos'],
    ['vista' => 'creditos/view_creditos_pendientes.php', 'icono' => 'fa-credit-card', 'texto' => 'Créditos pendientes'],
    ['vista' => 'caja/view_caja.php', 'icono' => 'fa-coins', 'texto' => 'Caja y arqueo'],
    ['vista' => 'reportes/view_analisis.php', 'icono' => 'fa-chart-pie', 'texto' => 'Análisis'],
    ['vista' => 'reportes/view_mis_reportes.php', 'icono' => 'fa-file-alt', 'texto' => 'Mis reportes'],
    ['vista' => 'productos/view_productos.php', 'icono' => 'fa-tags', 'texto' => 'Precios'],
], fn($a) => ruta_accesible('view/' . $a['vista'])));
$accesos = array_slice($accesos, 0, 6);
$e = fn($t) => htmlspecialchars((string) $t, ENT_QUOTES, 'UTF-8');
?>
<script src="../js/console_reportes.js?rev=<?php echo time(); ?>"></script>

<div class="content-header">
    <div class="container-fluid">
        <h1 class="m-0"><i class="fas fa-chart-line"></i> <b>DASHBOARD</b></h1>
    </div>
</div>

<div class="content">
    <div class="container-fluid">

        <?php if (puede('avisos')) { ?><div id="panel_alertas"></div><?php } ?>

        <?php if ($ver('mi_resumen')) { ?>
        <!-- RESUMEN PROPIO -->
        <h2 class="h6 font-weight-bold text-muted text-uppercase mb-2">Mi mes</h2>
        <div class="row" id="mi_resumen" aria-live="polite"></div>
        <p class="small text-muted mb-4" id="mi_ultimo_turno"></p>
        <?php } ?>

        <?php if ($ver('indicadores')) { ?>
        <!-- INDICADORES DEL DÍA -->
        <div class="row" id="indicadores_dia">
            <div class="col-6 col-xl-3"><div class="card mb-3"><div class="card-body py-3">
                <div class="small text-muted">Turnos abiertos</div>
                <div class="g-cifra" id="total_turnos_activos">0</div>
                <div class="small text-muted" id="total_turnos_cerrados_hoy">&nbsp;</div>
            </div></div></div>
            <div class="col-6 col-xl-3"><div class="card mb-3"><div class="card-body py-3">
                <div class="small text-muted">Ventas de hoy</div>
                <div class="g-cifra" id="total_ventas_dia">S/ 0.00</div>
                <div class="small text-muted">Turnos cerrados hoy</div>
            </div></div></div>
            <div class="col-6 col-xl-3"><div class="card mb-3"><div class="card-body py-3">
                <div class="small text-muted">Créditos por cobrar</div>
                <div class="g-cifra" id="total_creditos_saldo">S/ 0.00</div>
                <div class="small text-muted" id="total_creditos_pendientes">&nbsp;</div>
            </div></div></div>
            <div class="col-6 col-xl-3"><div class="card mb-3"><div class="card-body py-3">
                <div class="small text-muted">Turnos por validar</div>
                <div class="g-cifra" id="total_reportes_pendientes">0</div>
                <div class="small text-muted">Cerrados sin validar</div>
            </div></div></div>
        </div>
        <?php } ?>

        <?php if ($ver('ventas_semana')) { ?>
        <div class="card">
            <div class="card-header"><h2 class="card-title">Ventas de los últimos 7 días</h2></div>
            <div class="card-body"><canvas id="grafico_ventas_semana" style="height: 300px;"></canvas></div>
        </div>
        <?php } ?>

        <?php if ($ver('combustibles')) { ?>
        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header"><h2 class="card-title">Ventas por combustible · mes actual</h2></div>
                    <div class="card-body"><canvas id="grafico_combustibles" style="height: 250px;"></canvas></div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header"><h2 class="card-title">Métodos de pago · mes</h2></div>
                    <div class="card-body"><canvas id="grafico_metodos_pago" style="height: 250px;"></canvas></div>
                </div>
            </div>
        </div>
        <?php } ?>

        <?php if ($ver('turnos')) { ?>
        <div class="row">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header"><h2 class="card-title">Comparativo por turno · mes actual</h2></div>
                    <div class="card-body"><canvas id="grafico_turnos" style="height: 250px;"></canvas></div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header"><h2 class="card-title">Desempeño por grifero · mes actual</h2></div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead><tr><th>Grifero</th><th class="text-right">Turnos</th><th class="text-right">Ventas</th><th class="text-right">Promedio</th></tr></thead>
                                <tbody id="tabla_desempeno_griferos"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php } ?>

        <?php if ($ver('resumen_mes')) { ?>
        <div class="card">
            <div class="card-header"><h2 class="card-title">Resumen del mes</h2></div>
            <div class="card-body">
                <div class="row" id="resumen_mes_productos"></div>
                <p class="mb-0"><strong>Total del mes:</strong> <span id="mes_total_ventas">S/ 0.00</span> · <span id="mes_total_galones">0 gal</span></p>
            </div>
        </div>
        <?php } ?>

        <?php if ($ver('creditos')) { ?>
        <div class="card">
            <div class="card-header"><h2 class="card-title">Créditos del mes</h2></div>
            <div class="card-body">
                <div class="row">
                    <div class="col-6 col-md-3"><div class="small text-muted">Vales</div><div class="font-weight-bold" id="mes_total_creditos">0</div></div>
                    <div class="col-6 col-md-3"><div class="small text-muted">Monto</div><div class="font-weight-bold" id="mes_monto_creditos">S/ 0.00</div></div>
                    <div class="col-6 col-md-3"><div class="small text-muted">Cobrado</div><div class="font-weight-bold" id="mes_pagado_creditos">S/ 0.00</div></div>
                    <div class="col-6 col-md-3"><div class="small text-muted">Por cobrar</div><div class="font-weight-bold" id="mes_saldo_creditos">S/ 0.00</div></div>
                </div>
            </div>
        </div>
        <?php } ?>

        <?php if ($ver('accesos') && $accesos) { ?>
        <h2 class="h6 font-weight-bold text-muted text-uppercase mb-2">Accesos rápidos</h2>
        <div class="row">
            <?php foreach ($accesos as $a) { ?>
            <div class="col-6 col-md-4 col-xl-2 mb-3">
                <a href="#" class="card h-100 mb-0 text-center text-reset g-acceso" onclick="cargar_contenido('contenido_principal', <?php echo $e(json_encode($a['vista'], JSON_UNESCAPED_SLASHES)); ?>); return false;">
                    <span class="card-body py-3"><i class="fas <?php echo $e($a['icono']); ?> fa-lg text-primary mb-2 d-block" aria-hidden="true"></i><span class="font-weight-bold"><?php echo $e($a['texto']); ?></span></span>
                </a>
            </div>
            <?php } ?>
        </div>
        <?php } ?>

        <?php if (!$bloques || (!$estacion && !$ver('mi_resumen') && !($ver('accesos') && $accesos) && !puede('avisos'))) { ?>
        <div class="card"><div class="card-body text-muted">No hay bloques para mostrar. El administrador los elige en Configuración → Página principal.</div></div>
        <?php } ?>
    </div>
</div>

<script>
$(document).ready(function() {
    Cargar_Dashboard();
    <?php if ($estacion) { ?>
    // La ventana de créditos vencidos sale una vez por sesión; después quedan en la campana de avisos.
    var yaMostrada = false;
    try { yaMostrada = sessionStorage.getItem('g_vencidos_mostrados') === '1'; sessionStorage.setItem('g_vencidos_mostrados', '1'); } catch (e) {}
    if (!yaMostrada) { Verificar_Creditos_Vencidos(); }
    <?php } ?>
});

function Verificar_Creditos_Vencidos() {
    $.ajax({
        url: '../controller/creditos/controlador_creditos_vencidos.php',
        type: 'POST',
        dataType: 'json'
    }).done(function(data) {
        if (data.total > 0) {
            var mensaje = '<div style="text-align: left;">';
            mensaje += '<p><strong>Hay ' + data.total + ' crédito(s) vencido(s):</strong></p>';
            mensaje += '<ul>';
            data.creditos.forEach(function(credito) {
                mensaje += '<li><strong>' + Texto_Seguro(credito.cliente) + '</strong> - Vale: ' + Texto_Seguro(credito.numero_vale) + ' - Monto: S/. ' + parseFloat(credito.saldo_pendiente).toFixed(2) + ' - Vencido: ' + credito.dias_vencido + ' días</li>';
            });
            mensaje += '</ul>';
            mensaje += '</div>';

            Swal.fire({
                icon: 'warning',
                title: 'Créditos vencidos',
                html: mensaje,
                confirmButtonText: 'Ver créditos',
                showCancelButton: true,
                cancelButtonText: 'Cerrar',
                width: '600px'
            }).then((result) => {
                if (result.isConfirmed) {
                    cargar_contenido('contenido_principal', 'creditos/view_creditos_pendientes.php');
                }
            });
        }
    });
}
</script>
