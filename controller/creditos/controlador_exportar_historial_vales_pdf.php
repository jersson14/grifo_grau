<?php
require_once '../../model/model_creditos.php';
require_once '../../view/MPDF/conexion.php';
require_once '../../view/MPDF/vendor/autoload.php';

$MCreditos = new Modelo_Creditos();

// Obtener parámetros
$id_cliente = isset($_GET['id_cliente']) ? $_GET['id_cliente'] : null;
$filtro_estado = isset($_GET['filtro_estado']) ? $_GET['filtro_estado'] : '';

if (!$id_cliente) {
    die('Error: ID de cliente no especificado');
}

// Obtener información del cliente
$sql_cliente = "SELECT nombre_completo, dni_ruc, telefono FROM clientes WHERE id_cliente = ?";
$c = conexionBD::conexionPDO();
$query_cliente = $c->prepare($sql_cliente);
$query_cliente->execute(array($id_cliente));
$cliente = $query_cliente->fetch(PDO::FETCH_ASSOC);

// Obtener vales del cliente
$vales_data = $MCreditos->Listar_Vales_Cliente($id_cliente, $filtro_estado);
$vales = isset($vales_data['data']) ? $vales_data['data'] : array();

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
$titulo = "HISTORIAL DE VALES - " . strtoupper($cliente['nombre_completo']);
if ($filtro_estado) {
    $titulo .= " - ESTADO: " . strtoupper($filtro_estado);
}

// HTML del reporte
$html = '
<style>
    body { font-family: Arial, sans-serif; font-size: 10pt; }
    h2 { text-align: center; color: #6f42c1; margin-bottom: 10px; }
    .info-cliente { background-color: #f8f9fa; padding: 10px; border-radius: 5px; margin-bottom: 15px; }
    .info-cliente p { margin: 5px 0; }
    table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    th { background-color: #6f42c1; color: white; padding: 8px; text-align: left; font-size: 9pt; }
    td { padding: 6px; border-bottom: 1px solid #ddd; font-size: 9pt; }
    tr:hover { background-color: #f5f5f5; }
    .text-right { text-align: right; }
    .text-center { text-align: center; }
    .badge-pendiente { background-color: #ffc107; color: #000; padding: 3px 8px; border-radius: 3px; }
    .badge-pagado { background-color: #28a745; color: white; padding: 3px 8px; border-radius: 3px; }
    .badge-anulado { background-color: #dc3545; color: white; padding: 3px 8px; border-radius: 3px; }
    .badge-vencido { background-color: #dc3545; color: white; padding: 2px 6px; border-radius: 3px; font-size: 8pt; }
    .totales { font-weight: bold; background-color: #f0f0f0; }
</style>

<h2>' . $titulo . '</h2>
<p style="text-align: right; font-size: 9pt; color: #666;">Fecha de generación: ' . date('d/m/Y H:i:s') . '</p>

<div class="info-cliente">
    <p><strong>Cliente:</strong> ' . htmlspecialchars($cliente['nombre_completo']) . '</p>
    <p><strong>DNI/RUC:</strong> ' . ($cliente['dni_ruc'] ? htmlspecialchars($cliente['dni_ruc']) : '-') . '</p>
    <p><strong>Teléfono:</strong> ' . ($cliente['telefono'] ? htmlspecialchars($cliente['telefono']) : '-') . '</p>
    <p><strong>Total de Vales:</strong> ' . count($vales) . '</p>
</div>

<table>
    <thead>
        <tr>
            <th>N° Vale</th>
            <th>Fecha</th>
            <th>Turno</th>
            <th class="text-right">Monto</th>
            <th class="text-right">Pagado</th>
            <th class="text-right">Saldo</th>
            <th>Vencimiento</th>
            <th class="text-center">Estado</th>
            <th>Observaciones</th>
        </tr>
    </thead>
    <tbody>';

$total_monto = 0;
$total_pagado = 0;
$total_saldo = 0;

foreach ($vales as $vale) {
    $monto = floatval($vale['monto']);
    $monto_pagado = floatval($vale['monto_pagado']);
    $saldo = floatval($vale['saldo_pendiente']);
    
    $total_monto += $monto;
    $total_pagado += $monto_pagado;
    $total_saldo += $saldo;
    
    $estado_badge = '';
    if ($vale['estado'] == 'PENDIENTE') {
        $estado_badge = '<span class="badge-pendiente">PENDIENTE</span>';
    } elseif ($vale['estado'] == 'PAGADO') {
        $estado_badge = '<span class="badge-pagado">PAGADO</span>';
    } elseif ($vale['estado'] == 'ANULADO') {
        $estado_badge = '<span class="badge-anulado">ANULADO</span>';
    }
    
    $vencimiento_html = date('d/m/Y', strtotime($vale['fecha_vencimiento']));
    if ($vale['dias_vencido'] > 0 && $vale['estado'] == 'PENDIENTE') {
        $vencimiento_html .= '<br><span class="badge-vencido">Vencido</span>';
    }
    
    $turno_info = $vale['numero_documento'] ? $vale['numero_documento'] . '<br>' . $vale['turno'] : 'Manual';
    
    $html .= '
        <tr>
            <td>' . htmlspecialchars($vale['numero_vale']) . '</td>
            <td>' . date('d/m/Y', strtotime($vale['created_at'])) . '</td>
            <td><small>' . $turno_info . '</small></td>
            <td class="text-right">S/. ' . number_format($monto, 2) . '</td>
            <td class="text-right">S/. ' . number_format($monto_pagado, 2) . '</td>
            <td class="text-right">S/. ' . number_format($saldo, 2) . '</td>
            <td>' . $vencimiento_html . '</td>
            <td class="text-center">' . $estado_badge . '</td>
            <td><small>' . ($vale['observaciones'] ? htmlspecialchars($vale['observaciones']) : '-') . '</small></td>
        </tr>';
}

$html .= '
        <tr class="totales">
            <td colspan="3" class="text-right">TOTALES:</td>
            <td class="text-right">S/. ' . number_format($total_monto, 2) . '</td>
            <td class="text-right">S/. ' . number_format($total_pagado, 2) . '</td>
            <td class="text-right">S/. ' . number_format($total_saldo, 2) . '</td>
            <td colspan="3"></td>
        </tr>
    </tbody>
</table>
';

// Escribir HTML en el PDF
$mpdf->WriteHTML($html);

// Nombre del archivo
$filename = 'Historial_Vales_' . str_replace(' ', '_', $cliente['nombre_completo']) . '_' . date('Ymd_His') . '.pdf';

// Salida del PDF
$mpdf->Output($filename, 'D'); // 'D' = Descargar
?>
