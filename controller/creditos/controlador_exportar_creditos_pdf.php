<?php
require_once '../../model/model_creditos.php';
require_once '../../view/MPDF/conexion.php';
require_once '../../view/MPDF/vendor/autoload.php';

$MCreditos = new Modelo_Creditos();

// Obtener filtros
$filtro_cliente = isset($_GET['filtro_cliente']) ? $_GET['filtro_cliente'] : null;
$filtro_estado = isset($_GET['filtro_estado']) ? $_GET['filtro_estado'] : null;

// Obtener créditos
$creditos = $MCreditos->Listar_Todos_Creditos($filtro_cliente, $filtro_estado);

// Crear instancia de mPDF
$mpdf = new \Mpdf\Mpdf([
    'mode' => 'utf-8',
    'format' => 'A4-L', // Landscape
    'margin_left' => 10,
    'margin_right' => 10,
    'margin_top' => 10,
    'margin_bottom' => 10
]);

// Título del documento
$titulo = "REPORTE DE CRÉDITOS";
if ($filtro_cliente) {
    $titulo .= " - CLIENTE: " . strtoupper($filtro_cliente);
}
if ($filtro_estado) {
    $titulo .= " - ESTADO: " . strtoupper($filtro_estado);
}

// HTML del reporte
$html = '
<style>
    body { font-family: Arial, sans-serif; font-size: 10pt; }
    h2 { text-align: center; color: #023D77; margin-bottom: 20px; }
    table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    th { background-color: #023D77; color: white; padding: 8px; text-align: left; font-size: 9pt; }
    td { padding: 6px; border-bottom: 1px solid #ddd; font-size: 9pt; }
    tr:hover { background-color: #f5f5f5; }
    .text-right { text-align: right; }
    .text-center { text-align: center; }
    .badge-pendiente { background-color: #ffc107; color: #000; padding: 3px 8px; border-radius: 3px; }
    .badge-pagado { background-color: #28a745; color: white; padding: 3px 8px; border-radius: 3px; }
    .badge-anulado { background-color: #dc3545; color: white; padding: 3px 8px; border-radius: 3px; }
    .totales { font-weight: bold; background-color: #f0f0f0; }
</style>

<h2>' . $titulo . '</h2>
<p style="text-align: right; font-size: 9pt; color: #666;">Fecha de generación: ' . date('d/m/Y H:i:s') . '</p>

<table>
    <thead>
        <tr>
            <th>N°</th>
            <th>Cliente</th>
            <th>N° Vale</th>
            <th>Fecha</th>
            <th>Vencimiento</th>
            <th class="text-right">Monto</th>
            <th class="text-right">Pagado</th>
            <th class="text-right">Saldo</th>
            <th class="text-center">Estado</th>
            <th>Observaciones</th>
        </tr>
    </thead>
    <tbody>';

$total_monto = 0;
$total_pagado = 0;
$total_saldo = 0;
$contador = 1;

foreach ($creditos as $credito) {
    if ($credito['estado'] == 'ANULADO') continue;
    $monto = floatval($credito['monto']);
    $monto_pagado = floatval($credito['monto_pagado']);
    $saldo = floatval($credito['saldo_pendiente']);
    
    $total_monto += $monto;
    $total_pagado += $monto_pagado;
    $total_saldo += $saldo;
    
    $estado_badge = '';
    if ($credito['estado'] == 'PENDIENTE') {
        $estado_badge = '<span class="badge-pendiente">PENDIENTE</span>';
    } elseif ($credito['estado'] == 'PAGADO') {
        $estado_badge = '<span class="badge-pagado">PAGADO</span>';
    } elseif ($credito['estado'] == 'ANULADO') {
        $estado_badge = '<span class="badge-anulado">ANULADO</span>';
    }
    
    $html .= '
        <tr>
            <td>' . $contador . '</td>
            <td>' . htmlspecialchars($credito['cliente_nombre']) . '</td>
            <td>' . htmlspecialchars($credito['numero_vale']) . '</td>
            <td>' . date('d/m/Y', strtotime($credito['created_at'])) . '</td>
            <td>' . date('d/m/Y', strtotime($credito['fecha_vencimiento'])) . '</td>
            <td class="text-right">S/. ' . number_format($monto, 2) . '</td>
            <td class="text-right">S/. ' . number_format($monto_pagado, 2) . '</td>
            <td class="text-right">S/. ' . number_format($saldo, 2) . '</td>
            <td class="text-center">' . $estado_badge . '</td>
            <td>' . htmlspecialchars($credito['observaciones']) . '</td>
        </tr>';
    
    $contador++;
}

$html .= '
        <tr class="totales">
            <td colspan="5" class="text-right">TOTALES:</td>
            <td class="text-right">S/. ' . number_format($total_monto, 2) . '</td>
            <td class="text-right">S/. ' . number_format($total_pagado, 2) . '</td>
            <td class="text-right">S/. ' . number_format($total_saldo, 2) . '</td>
            <td colspan="2"></td>
        </tr>
    </tbody>
</table>
';

// Escribir HTML en el PDF
$mpdf->WriteHTML($html);

// Nombre del archivo
$filename = 'Reporte_Creditos_' . date('Ymd_His') . '.pdf';

// Salida del PDF
$mpdf->Output($filename, 'D'); // 'D' = Descargar
?>
