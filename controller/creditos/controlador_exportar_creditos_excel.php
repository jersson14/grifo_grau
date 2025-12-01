<?php
require_once '../../model/model_creditos.php';

$MCreditos = new Modelo_Creditos();

// Obtener filtros
$filtro_cliente = isset($_GET['filtro_cliente']) ? $_GET['filtro_cliente'] : null;
$filtro_estado = isset($_GET['filtro_estado']) ? $_GET['filtro_estado'] : null;

// Obtener créditos
$creditos = $MCreditos->Listar_Todos_Creditos($filtro_cliente, $filtro_estado);

// Nombre del archivo
$filename = 'Reporte_Creditos_' . date('Ymd_His') . '.xls';

// Headers para descarga de Excel
header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Pragma: no-cache");
header("Expires: 0");

// Título del documento
$titulo = "REPORTE DE CRÉDITOS";
if ($filtro_cliente) {
    $titulo .= " - CLIENTE: " . strtoupper($filtro_cliente);
}
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
        th { background-color: #023D77; color: white; font-weight: bold; border: 1px solid #000; padding: 8px; }
        td { border: 1px solid #000; padding: 5px; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .totales { font-weight: bold; background-color: #f0f0f0; }
    </style>
</head>
<body>
    <h2>' . $titulo . '</h2>
    <p>Fecha de generación: ' . date('d/m/Y H:i:s') . '</p>
    
    <table>
        <thead>
            <tr>
                <th>N°</th>
                <th>Cliente</th>
                <th>N° Vale</th>
                <th>Fecha</th>
                <th>Vencimiento</th>
                <th>Monto</th>
                <th>Pagado</th>
                <th>Saldo</th>
                <th>Estado</th>
                <th>Observaciones</th>
            </tr>
        </thead>
        <tbody>';

$total_monto = 0;
$total_pagado = 0;
$total_saldo = 0;
$contador = 1;

foreach ($creditos as $credito) {
    $monto = floatval($credito['monto']);
    $monto_pagado = floatval($credito['monto_pagado']);
    $saldo = floatval($credito['saldo_pendiente']);
    
    $total_monto += $monto;
    $total_pagado += $monto_pagado;
    $total_saldo += $saldo;
    
    echo '
        <tr>
            <td>' . $contador . '</td>
            <td>' . htmlspecialchars($credito['cliente_nombre']) . '</td>
            <td>' . htmlspecialchars($credito['numero_vale']) . '</td>
            <td>' . date('d/m/Y', strtotime($credito['created_at'])) . '</td>
            <td>' . date('d/m/Y', strtotime($credito['fecha_vencimiento'])) . '</td>
            <td class="text-right">' . number_format($monto, 2) . '</td>
            <td class="text-right">' . number_format($monto_pagado, 2) . '</td>
            <td class="text-right">' . number_format($saldo, 2) . '</td>
            <td class="text-center">' . $credito['estado'] . '</td>
            <td>' . htmlspecialchars($credito['observaciones']) . '</td>
        </tr>';
    
    $contador++;
}

echo '
        <tr class="totales">
            <td colspan="5" class="text-right"><strong>TOTALES:</strong></td>
            <td class="text-right"><strong>' . number_format($total_monto, 2) . '</strong></td>
            <td class="text-right"><strong>' . number_format($total_pagado, 2) . '</strong></td>
            <td class="text-right"><strong>' . number_format($total_saldo, 2) . '</strong></td>
            <td colspan="2"></td>
        </tr>
    </tbody>
</table>
</body>
</html>';
?>
