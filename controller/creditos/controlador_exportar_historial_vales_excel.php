<?php
require_once '../../model/model_creditos.php';

$MCreditos = new Modelo_Creditos();

// Obtener parámetros
$id_cliente = isset($_GET['id_cliente']) ? $_GET['id_cliente'] : null;
$filtro_estado = isset($_GET['filtro_estado']) ? $_GET['filtro_estado'] : '';

if (!$id_cliente) {
    die('Error: ID de cliente no especificado');
}

// Obtener información del cliente
$sql_cliente = "SELECT nombre_completo, dni_ruc, telefono FROM clientes WHERE id_cliente = ?";
require_once '../../model/model_conexion.php';
$c = conexionBD::conexionPDO();
$query_cliente = $c->prepare($sql_cliente);
$query_cliente->execute(array($id_cliente));
$cliente = $query_cliente->fetch(PDO::FETCH_ASSOC);

// Obtener vales del cliente
$vales_data = $MCreditos->Listar_Vales_Cliente($id_cliente, $filtro_estado);
$vales = isset($vales_data['data']) ? $vales_data['data'] : array();

// Nombre del archivo
$filename = 'Historial_Vales_' . str_replace(' ', '_', $cliente['nombre_completo']) . '_' . date('Ymd_His') . '.xls';

// Headers para descarga de Excel
header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Pragma: no-cache");
header("Expires: 0");

// Título del documento
$titulo = "HISTORIAL DE VALES - " . strtoupper($cliente['nombre_completo']);
if ($filtro_estado) {
    $titulo .= " - ESTADO: " . strtoupper($filtro_estado);
}

// Iniciar tabla HTML (Excel lo interpreta)
echo '
<html xmlns:x="urn:schemas-microsoft-com:office:excel">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <style>
        table { border-collapse: collapse; }
        th { background-color: #6f42c1; color: white; font-weight: bold; border: 1px solid #000; padding: 8px; }
        td { border: 1px solid #000; padding: 5px; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .totales { font-weight: bold; background-color: #f0f0f0; }
        .info { background-color: #f8f9fa; padding: 10px; margin-bottom: 10px; }
    </style>
</head>
<body>
    <h2>' . $titulo . '</h2>
    <p>Fecha de generación: ' . date('d/m/Y H:i:s') . '</p>
    
    <div class="info">
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
                <th>Monto</th>
                <th>Pagado</th>
                <th>Saldo</th>
                <th>Vencimiento</th>
                <th>Estado</th>
                <th>Observaciones</th>
            </tr>
        </thead>
        <tbody>';

$total_monto = 0;
$total_pagado = 0;
$total_saldo = 0;

foreach ($vales as $vale) {
    if ($vale['estado'] == 'ANULADO') continue;
    $monto = floatval($vale['monto']);
    $monto_pagado = floatval($vale['monto_pagado']);
    $saldo = floatval($vale['saldo_pendiente']);
    
    $total_monto += $monto;
    $total_pagado += $monto_pagado;
    $total_saldo += $saldo;
    
    $vencimiento_text = date('d/m/Y', strtotime($vale['fecha_vencimiento']));
    if ($vale['dias_vencido'] > 0 && $vale['estado'] == 'PENDIENTE') {
        $vencimiento_text .= ' (Vencido)';
    }
    
    $turno_info = $vale['numero_documento'] ? $vale['numero_documento'] . ' - ' . $vale['turno'] : 'Manual';
    
    echo '
        <tr>
            <td>' . htmlspecialchars($vale['numero_vale']) . '</td>
            <td>' . date('d/m/Y', strtotime($vale['created_at'])) . '</td>
            <td>' . $turno_info . '</td>
            <td class="text-right">' . number_format($monto, 2) . '</td>
            <td class="text-right">' . number_format($monto_pagado, 2) . '</td>
            <td class="text-right">' . number_format($saldo, 2) . '</td>
            <td>' . $vencimiento_text . '</td>
            <td class="text-center">' . $vale['estado'] . '</td>
            <td>' . ($vale['observaciones'] ? htmlspecialchars($vale['observaciones']) : '-') . '</td>
        </tr>';
}

echo '
        <tr class="totales">
            <td colspan="3" class="text-right"><strong>TOTALES:</strong></td>
            <td class="text-right"><strong>' . number_format($total_monto, 2) . '</strong></td>
            <td class="text-right"><strong>' . number_format($total_pagado, 2) . '</strong></td>
            <td class="text-right"><strong>' . number_format($total_saldo, 2) . '</strong></td>
            <td colspan="3"></td>
        </tr>
    </tbody>
</table>
</body>
</html>';
?>
