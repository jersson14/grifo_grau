<?php
require_once '../../model/model_creditos.php';
require_once '../../model/model_conexion.php';

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

$filename = 'Historial_Vales_' . str_replace(' ', '_', $cliente['nombre_completo']) . '_' . date('Ymd_His') . '.xls';

header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Pragma: no-cache');
header('Expires: 0');

$titulo = 'HISTORIAL DE VALES — ' . strtoupper($cliente['nombre_completo']);
if ($filtro_estado) $titulo .= ' — ' . strtoupper($filtro_estado);

// Contar totales primero
$total_monto = $total_pagado = $total_saldo = 0;
foreach ($vales as $v) {
    if ($v['estado'] == 'ANULADO') continue;
    $total_monto  += floatval($v['monto']);
    $total_pagado += floatval($v['monto_pagado']);
    $total_saldo  += floatval($v['saldo_pendiente']);
}

echo '
<html xmlns:x="urn:schemas-microsoft-com:office:excel">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<style>
    body { font-family: Arial, sans-serif; font-size: 10pt; }
    table { border-collapse: collapse; width: 100%; }
    .cab        { background-color: #2c1a6e; color: #ffffff; text-align: center;
                  font-size: 18pt; font-weight: bold; letter-spacing: 2px;
                  padding: 12px 8px; border: 2px solid #2c1a6e; }
    .cab-sub    { background-color: #2c1a6e; color: #c9b8ff; text-align: center;
                  font-size: 9pt; letter-spacing: 2px; padding: 4px 8px;
                  border: 2px solid #2c1a6e; }
    .acento     { background-color: #7c4dff; padding: 2px; border: none; }
    .meta       { text-align: right; font-size: 8pt; color: #888888;
                  padding: 4px 8px; border: none; }
    .bloque     { background-color: #f4f1ff; font-size: 9pt;
                  padding: 7px 12px; border-left: 4px solid #6f42c1;
                  border-bottom: 1px solid #e0d8ff; }
    .titulo-rep { text-align: center; font-size: 12pt; font-weight: bold;
                  color: #2c1a6e; padding: 8px; border: none; }
    th          { background-color: #2c1a6e; color: #ffffff; font-weight: bold;
                  border: 1px solid #1a0f45; padding: 7px 6px; font-size: 9pt; }
    td          { border: 1px solid #dddddd; padding: 5px 6px;
                  font-size: 9pt; vertical-align: middle; }
    .tr         { text-align: right; }
    .tc         { text-align: center; }
    .par        { background-color: #f7f5ff; }
    .tf         { background-color: #2c1a6e; color: #ffffff;
                  font-weight: bold; font-size: 9.5pt;
                  border: 2px solid #2c1a6e; padding: 7px 6px; }
</style>
</head>
<body>
<table>
    <!-- ENCABEZADO EMPRESA -->
    <tr><td colspan="12" class="cab">ESTACI&Oacute;N GOYO</td></tr>
    <tr><td colspan="12" class="cab-sub">ESTACI&Oacute;N DE SERVICIOS &amp; COMBUSTIBLES</td></tr>
    <tr><td colspan="12" class="acento"></td></tr>
    <tr><td colspan="12" class="meta">Generado el ' . date('d/m/Y') . ' a las ' . date('H:i:s') . '</td></tr>

    <!-- DATOS CLIENTE -->
    <tr>
        <td colspan="12" class="bloque">
            <strong>Cliente:</strong> ' . htmlspecialchars($cliente['nombre_completo']) . '
            &nbsp;&nbsp;|&nbsp;&nbsp;
            <strong>DNI/RUC:</strong> ' . ($cliente['dni_ruc'] ?: '-') . '
            &nbsp;&nbsp;|&nbsp;&nbsp;
            <strong>Tel&eacute;fono:</strong> ' . ($cliente['telefono'] ?: '-') . '
            &nbsp;&nbsp;|&nbsp;&nbsp;
            <strong>Total de Vales:</strong> ' . count($vales) . '
        </td>
    </tr>

    <!-- TÍTULO REPORTE -->
    <tr><td colspan="12" class="titulo-rep">' . $titulo . '</td></tr>

    <!-- CABECERA COLUMNAS -->
    <tr>
        <th>N&deg; Vale</th>
        <th>Placa</th>
        <th>Fecha</th>
        <th>Turno</th>
        <th class="tr">Monto</th>
        <th class="tr">Pagado</th>
        <th class="tr">Saldo</th>
        <th class="tc">Vencimiento</th>
        <th class="tc">Fecha Pago</th>
        <th class="tc">Cód. Operación</th>
        <th class="tc">Estado</th>
        <th>Observaciones</th>
    </tr>';

$fila = 0;
foreach ($vales as $vale) {
    if ($vale['estado'] == 'ANULADO') continue;

    $monto        = floatval($vale['monto']);
    $monto_pagado = floatval($vale['monto_pagado']);
    $saldo        = floatval($vale['saldo_pendiente']);
    $par          = ($fila++ % 2 == 1) ? ' class="par"' : '';

    $venc = date('d/m/Y', strtotime($vale['fecha_vencimiento']));
    if ($vale['dias_vencido'] > 0 && $vale['estado'] == 'PENDIENTE') {
        $venc .= ' (Vencido)';
    }

    $turno = $vale['numero_documento']
        ? $vale['numero_documento'] . ' - ' . $vale['turno']
        : 'Manual';

    $fp = (!empty($vale['fecha_ultimo_pago']))
        ? date('d/m/Y', strtotime($vale['fecha_ultimo_pago']))
        : '—';

    echo '
    <tr' . $par . '>
        <td class="tc">' . htmlspecialchars($vale['numero_vale']) . '</td>
        <td class="tc">' . ($vale['placa'] ? htmlspecialchars($vale['placa']) : '—') . '</td>
        <td class="tc">' . date('d/m/Y', strtotime($vale['created_at'])) . '</td>
        <td>' . $turno . '</td>
        <td class="tr">S/. ' . number_format($monto, 2) . '</td>
        <td class="tr">S/. ' . number_format($monto_pagado, 2) . '</td>
        <td class="tr"><strong>S/. ' . number_format($saldo, 2) . '</strong></td>
        <td class="tc">' . $venc . '</td>
        <td class="tc">' . $fp . '</td>
        <td class="tc">' . (!empty($vale['ultimo_codigo_operacion']) ? htmlspecialchars($vale['ultimo_codigo_operacion']) : '—') . '</td>
        <td class="tc">' . $vale['estado'] . '</td>
        <td>' . ($vale['observaciones'] ? htmlspecialchars($vale['observaciones']) : '—') . '</td>
    </tr>';
}

echo '
    <!-- FILA TOTALES -->
    <tr>
        <td colspan="4" class="tr tf">TOTALES</td>
        <td class="tr tf">S/. ' . number_format($total_monto, 2) . '</td>
        <td class="tr tf">S/. ' . number_format($total_pagado, 2) . '</td>
        <td class="tr tf">S/. ' . number_format($total_saldo, 2) . '</td>
        <td colspan="5" class="tf"></td>
    </tr>

</table>
</body>
</html>';
?>
