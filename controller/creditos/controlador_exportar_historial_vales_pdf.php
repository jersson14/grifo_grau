<?php
require_once '../../model/model_creditos.php';
require_once '../../view/MPDF/conexion.php';
require_once '../../view/MPDF/vendor/autoload.php';

$MCreditos = new Modelo_Creditos();

$id_cliente    = isset($_GET['id_cliente'])    ? $_GET['id_cliente']    : null;
$filtro_estado = isset($_GET['filtro_estado']) ? $_GET['filtro_estado'] : '';

if (!$id_cliente) die('Error: ID de cliente no especificado');

$c = conexionBD::conexionPDO();

$q = $c->prepare("SELECT nombre_completo, dni_ruc, telefono FROM clientes WHERE id_cliente = ?");
$q->execute([$id_cliente]);
$cliente = $q->fetch(PDO::FETCH_ASSOC);

$vales_data = $MCreditos->Listar_Vales_Cliente($id_cliente, $filtro_estado);
$vales      = isset($vales_data['data']) ? $vales_data['data'] : [];

$mpdf = new \Mpdf\Mpdf([
    'mode'          => 'utf-8',
    'format'        => 'A4-L',
    'margin_left'   => 12,
    'margin_right'  => 12,
    'margin_top'    => 12,
    'margin_bottom' => 12,
]);

$titulo_reporte = 'HISTORIAL DE VALES — ' . strtoupper($cliente['nombre_completo']);
if ($filtro_estado) $titulo_reporte .= ' — ' . strtoupper($filtro_estado);

$html = '
<style>
    body { font-family: Arial, sans-serif; font-size: 9pt; color: #222; }

    /* ── ENCABEZADO ── */
    .cab-top {
        background-color: #2c1a6e;
        padding: 14px 0 10px 0;
        text-align: center;
    }
    .cab-nombre {
        font-size: 22pt;
        font-weight: bold;
        color: #ffffff;
        letter-spacing: 3px;
        text-transform: uppercase;
    }
    .cab-sub {
        font-size: 9pt;
        color: #c9b8ff;
        letter-spacing: 2px;
        margin-top: 3px;
        text-transform: uppercase;
    }
    .cab-linea {
        height: 4px;
        background-color: #7c4dff;
        margin: 0 0 10px 0;
    }

    /* ── META INFO ── */
    .meta {
        text-align: right;
        font-size: 7.5pt;
        color: #888;
        margin-bottom: 8px;
    }

    /* ── BLOQUE CLIENTE ── */
    .bloque-cliente {
        background-color: #f4f1ff;
        border-left: 4px solid #6f42c1;
        padding: 7px 12px;
        margin-bottom: 10px;
        font-size: 9pt;
    }

    /* ── TÍTULO REPORTE ── */
    .titulo-rep {
        text-align: center;
        font-size: 11pt;
        font-weight: bold;
        color: #2c1a6e;
        margin: 6px 0 10px 0;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    /* ── TABLA ── */
    table.tv {
        width: 100%;
        border-collapse: collapse;
    }
    table.tv thead th {
        background-color: #2c1a6e;
        color: #fff;
        padding: 6px 5px;
        font-size: 8pt;
        text-align: left;
        border: 1px solid #1a0f45;
    }
    table.tv tbody td {
        padding: 5px 5px;
        font-size: 8pt;
        border-bottom: 1px solid #e8e8e8;
        vertical-align: middle;
    }
    table.tv tbody tr:nth-child(even) td { background-color: #f7f5ff; }

    .tr  { text-align: right; }
    .tc  { text-align: center; }

    /* ── BADGES ── */
    .bp { background-color: #ffc107; color: #000; padding: 2px 6px; border-radius: 3px; font-size: 7pt; font-weight: bold; }
    .bg { background-color: #28a745; color: #fff; padding: 2px 6px; border-radius: 3px; font-size: 7pt; font-weight: bold; }
    .br { background-color: #dc3545; color: #fff; padding: 1px 5px; border-radius: 3px; font-size: 7pt; }

    /* ── TOTALES ── */
    .tf td {
        background-color: #2c1a6e;
        color: #fff;
        font-weight: bold;
        font-size: 8.5pt;
        padding: 7px 5px;
        border-top: 3px solid #7c4dff;
    }
</style>

<!-- ENCABEZADO -->
<div class="cab-top">
    <div class="cab-nombre">ESTACI&Oacute;N GOYO</div>
    <div class="cab-sub">Estaci&oacute;n de Servicios &amp; Combustibles</div>
</div>
<div class="cab-linea"></div>

<div class="meta">Generado el ' . date('d/m/Y') . ' a las ' . date('H:i:s') . '</div>

<!-- DATOS DEL CLIENTE -->
<div class="bloque-cliente">
    <strong>Cliente:</strong> ' . htmlspecialchars($cliente['nombre_completo']) . '
    &nbsp;&nbsp;|&nbsp;&nbsp;
    <strong>DNI/RUC:</strong> ' . ($cliente['dni_ruc'] ?: '-') . '
    &nbsp;&nbsp;|&nbsp;&nbsp;
    <strong>Tel&eacute;fono:</strong> ' . ($cliente['telefono'] ?: '-') . '
    &nbsp;&nbsp;|&nbsp;&nbsp;
    <strong>Total de Vales:</strong> ' . count($vales) . '
</div>

<div class="titulo-rep">' . $titulo_reporte . '</div>

<!-- TABLA -->
<table class="tv">
    <thead>
        <tr>
            <th>N&deg; Vale</th>
            <th>Placa</th>
            <th>Fecha</th>
            <th>Turno</th>
            <th class="tr">Monto</th>
            <th class="tr">Pagado</th>
            <th class="tr">Saldo</th>
            <th>Vencimiento</th>
            <th class="tc">Fecha Pago</th>
            <th class="tr">Descuento</th>
            <th class="tc">Cód. Operación</th>
            <th class="tc">Estado</th>
            <th>Observaciones</th>
        </tr>
    </thead>
    <tbody>';

$total_monto = $total_pagado = $total_saldo = 0;

foreach ($vales as $vale) {
    if ($vale['estado'] == 'ANULADO') continue;

    $monto        = floatval($vale['monto']);
    $monto_pagado = floatval($vale['monto_pagado']);
    $saldo        = floatval($vale['saldo_pendiente']);

    $total_monto  += $monto;
    $total_pagado += $monto_pagado;
    $total_saldo  += $saldo;

    $badge = ($vale['estado'] == 'PENDIENTE')
        ? '<span class="bp">PENDIENTE</span>'
        : '<span class="bg">PAGADO</span>';

    $venc = date('d/m/Y', strtotime($vale['fecha_vencimiento']));
    if ($vale['dias_vencido'] > 0 && $vale['estado'] == 'PENDIENTE') {
        $venc .= '<br><span class="br">Vencido</span>';
    }

    $turno = $vale['numero_documento']
        ? $vale['numero_documento'] . '<br>' . $vale['turno']
        : 'Manual';

    if (!empty($vale['fecha_ultimo_pago'])) {
        $fp = date('d/m/Y', strtotime($vale['fecha_ultimo_pago']));
        $fecha_pago = ($vale['estado'] == 'PAGADO')
            ? '<strong style="color:#28a745">' . $fp . '</strong>'
            : '<span style="color:#888">' . $fp . '</span>';
    } else {
        $fecha_pago = '<span style="color:#ccc">—</span>';
    }

    $html .= '
        <tr>
            <td>' . htmlspecialchars($vale['numero_vale']) . '</td>
            <td>' . ($vale['placa'] ? '<strong>' . htmlspecialchars($vale['placa']) . '</strong>' : '<span style="color:#ccc">—</span>') . '</td>
            <td>' . date('d/m/Y', strtotime($vale['created_at'])) . '</td>
            <td><small>' . $turno . '</small></td>
            <td class="tr">S/. ' . number_format($monto, 2) . '</td>
            <td class="tr">S/. ' . number_format($monto_pagado, 2) . '</td>
            <td class="tr"><strong>S/. ' . number_format($saldo, 2) . '</strong></td>
            <td>' . $venc . '</td>
            <td class="tc">' . $fecha_pago . '</td>
            <td class="tr">' . (floatval($vale['ultimo_descuento']) > 0 ? '<strong style="color:#d97706">S/. ' . number_format(floatval($vale['ultimo_descuento']), 2) . '</strong>' : '—') . '</td>
            <td class="tc"><small>' . (!empty($vale['ultimo_codigo_operacion']) ? htmlspecialchars($vale['ultimo_codigo_operacion']) : '—') . '</small></td>
            <td class="tc">' . $badge . '</td>
            <td><small>' . ($vale['observaciones'] ? htmlspecialchars($vale['observaciones']) : '—') . '</small></td>
        </tr>';
}

$html .= '
    </tbody>
    <tfoot>
        <tr class="tf">
            <td colspan="4" class="tr">TOTALES</td>
            <td class="tr">S/. ' . number_format($total_monto, 2) . '</td>
            <td class="tr">S/. ' . number_format($total_pagado, 2) . '</td>
            <td class="tr">S/. ' . number_format($total_saldo, 2) . '</td>
            <td colspan="6"></td>
        </tr>
    </tfoot>
</table>';

$mpdf->WriteHTML($html);
$mpdf->Output('Historial_Vales_' . str_replace(' ', '_', $cliente['nombre_completo']) . '_' . date('Ymd_His') . '.pdf', 'D');
?>
