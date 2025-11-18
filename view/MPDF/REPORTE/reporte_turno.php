<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../../../model/model_conexion.php';

$id_turno = $_GET['id'] ?? 0;

if ($id_turno == 0) {
    die('ID de turno no válido');
}

$c = conexionBD::conexionPDO();

// Obtener información del turno
$sql = "SELECT 
            rt.*,
            CONCAT(u.usu_nombre, ' ', u.usu_apellido) as grifero_nombre,
            DATE_FORMAT(rt.fecha_reporte, '%d/%m/%Y') as fecha_formateada,
            DATE_FORMAT(rt.hora_inicio, '%H:%i') as hora_inicio_formateada,
            DATE_FORMAT(rt.hora_fin, '%H:%i') as hora_fin_formateada
        FROM reportes_turno rt
        INNER JOIN usuario u ON rt.id_usuario = u.id_usuario
        WHERE rt.id_reporte = ?";

$stmt = $c->prepare($sql);
$stmt->execute(array($id_turno));
$turno = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$turno) {
    die('Turno no encontrado');
}

// Obtener lecturas del turno
$sql_lecturas = "SELECT 
                    s.numero_maquina,
                    s.codigo,
                    p.nombre as nombre_producto,
                    lt.lectura_anterior,
                    lt.lectura_actual,
                    lt.galones_vendidos,
                    lt.precio_galon as precio,
                    lt.total_soles as total
                FROM lecturas_turno lt
                INNER JOIN surtidores s ON lt.id_surtidor = s.id_surtidor
                INNER JOIN productos p ON s.id_producto = p.id_producto
                WHERE lt.id_reporte = ?
                ORDER BY s.numero_maquina, s.codigo";

$stmt = $c->prepare($sql_lecturas);
$stmt->execute(array($id_turno));
$lecturas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calcular totales
$total_general = 0;
$total_diesel = 0;
$total_regular = 0;
$total_premium = 0;
$galones_diesel = 0;
$galones_regular = 0;
$galones_premium = 0;
$total_galones = 0;

foreach ($lecturas as $lectura) {
    $total = floatval($lectura['total']);
    $galones = floatval($lectura['galones_vendidos']);
    $total_general += $total;
    $total_galones += $galones;
    
    $producto = strtoupper($lectura['nombre_producto']);
    if (strpos($producto, 'DIESEL') !== false || strpos($producto, 'DB5') !== false) {
        $total_diesel += $total;
        $galones_diesel += $galones;
    } elseif (strpos($producto, 'REGULAR') !== false || strpos($producto, '90') !== false || strpos($producto, '84') !== false) {
        $total_regular += $total;
        $galones_regular += $galones;
    } elseif (strpos($producto, 'PREMIUM') !== false || strpos($producto, '95') !== false || strpos($producto, '97') !== false) {
        $total_premium += $total;
        $galones_premium += $galones;
    }
}

// Obtener pagos
$sql_pagos = "SELECT 
                tp.nombre as tipo_pago,
                pr.codigo_operacion,
                pr.monto
            FROM pagos_reporte pr
            INNER JOIN tipos_pago tp ON pr.id_tipo_pago = tp.id_tipo_pago
            WHERE pr.id_reporte = ?";

$stmt = $c->prepare($sql_pagos);
$stmt->execute(array($id_turno));
$pagos = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total_pagos = 0;
foreach ($pagos as $pago) {
    $total_pagos += floatval($pago['monto']);
}

// Obtener créditos
$sql_creditos = "SELECT 
                    vc.numero_vale,
                    c.nombre_completo as cliente,
                    vc.monto
                FROM ventas_credito vc
                INNER JOIN clientes c ON vc.id_cliente = c.id_cliente
                WHERE vc.id_reporte = ?";

$stmt = $c->prepare($sql_creditos);
$stmt->execute(array($id_turno));
$creditos = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total_creditos = 0;
foreach ($creditos as $credito) {
    $total_creditos += floatval($credito['monto']);
}

// Crear PDF
$mpdf = new \Mpdf\Mpdf([
    'mode' => 'utf-8',
    'format' => 'A4',
    'margin_left' => 10,
    'margin_right' => 10,
    'margin_top' => 10,
    'margin_bottom' => 10
]);

$html = '
<style>
    body { font-family: Arial, sans-serif; font-size: 10px; }
    .header { text-align: center; margin-bottom: 20px; }
    .header h2 { margin: 5px 0; color: #023D77; }
    .info-box { border: 1px solid #ddd; padding: 10px; margin-bottom: 15px; }
    .info-row { margin: 5px 0; }
    .info-label { font-weight: bold; display: inline-block; width: 150px; }
    table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
    th { background-color: #023D77; color: white; padding: 8px; text-align: left; font-size: 9px; }
    td { border: 1px solid #ddd; padding: 6px; font-size: 9px; }
    .text-right { text-align: right; }
    .text-center { text-align: center; }
    .total-row { background-color: #f0f0f0; font-weight: bold; }
    .section-title { background-color: #023D77; color: white; padding: 8px; margin-top: 15px; margin-bottom: 10px; font-weight: bold; }
    .resumen-box { border: 2px solid #023D77; padding: 10px; margin: 10px 0; }
    .resumen-item { margin: 5px 0; font-size: 11px; }
</style>

<div class="header">
    <div style="text-align: center;">
        <img src="' . __DIR__ . '/../../../img/grau.png" style="width: 120px; height: auto; margin-bottom: 10px;">
        <p style="margin: 5px 0;"><strong>ULLPUTO - CHUQUI</strong></p>
        <h3 style="margin: 5px 0; color: #023D77;">REPORTE DE TURNO</h3>
    </div>
</div>

<div class="info-box">
    <div class="info-row">
        <span class="info-label">N° Documento:</span>
        <span>' . $turno['numero_documento'] . '</span>
    </div>
    <div class="info-row">
        <span class="info-label">Fecha:</span>
        <span>' . $turno['fecha_formateada'] . '</span>
    </div>
    <div class="info-row">
        <span class="info-label">Turno:</span>
        <span>' . $turno['turno'] . '</span>
    </div>
    <div class="info-row">
        <span class="info-label">Horario:</span>
        <span>' . $turno['hora_inicio_formateada'] . ' - ' . $turno['hora_fin_formateada'] . '</span>
    </div>
    <div class="info-row">
        <span class="info-label">Grifero:</span>
        <span>' . $turno['grifero_nombre'] . '</span>
    </div>
    <div class="info-row">
        <span class="info-label">Estado:</span>
        <span>' . $turno['estado'] . '</span>
    </div>
</div>

<div class="section-title">LECTURAS DE SURTIDORES</div>
<table>
    <thead>
        <tr>
            <th>Máq.</th>
            <th>Código</th>
            <th>Producto</th>
            <th class="text-right">Lect. Anterior</th>
            <th class="text-right">Lect. Actual</th>
            <th class="text-right">Galones</th>
            <th class="text-right">Precio</th>
            <th class="text-right">Total</th>
        </tr>
    </thead>
    <tbody>';

foreach ($lecturas as $lectura) {
    $html .= '<tr>
        <td class="text-center">' . $lectura['numero_maquina'] . '</td>
        <td>' . $lectura['codigo'] . '</td>
        <td>' . $lectura['nombre_producto'] . '</td>
        <td class="text-right">' . number_format($lectura['lectura_anterior'], 3) . '</td>
        <td class="text-right">' . number_format($lectura['lectura_actual'], 3) . '</td>
        <td class="text-right">' . number_format($lectura['galones_vendidos'], 3) . '</td>
        <td class="text-right">S/. ' . number_format($lectura['precio'], 2) . '</td>
        <td class="text-right">S/. ' . number_format($lectura['total'], 2) . '</td>
    </tr>';
}

$html .= '
        <tr class="total-row">
            <td colspan="7" class="text-right">TOTAL VENTAS:</td>
            <td class="text-right">S/. ' . number_format($total_general, 2) . '</td>
        </tr>
    </tbody>
</table>

<div class="resumen-box">
    <div class="resumen-item"><strong>DIESEL:</strong> S/. ' . number_format($total_diesel, 2) . '</div>
    <div class="resumen-item"><strong>REGULAR:</strong> S/. ' . number_format($total_regular, 2) . '</div>
    <div class="resumen-item"><strong>PREMIUM:</strong> S/. ' . number_format($total_premium, 2) . '</div>
    <div class="resumen-item" style="font-size: 12px; color: #023D77;"><strong>TOTAL:</strong> S/. ' . number_format($total_general, 2) . '</div>
</div>';

if (count($pagos) > 0) {
    $html .= '
    <div class="section-title">MÉTODOS DE PAGO</div>
    <table>
        <thead>
            <tr>
                <th>Tipo de Pago</th>
                <th>Código Operación</th>
                <th class="text-right">Monto</th>
            </tr>
        </thead>
        <tbody>';
    
    foreach ($pagos as $pago) {
        $html .= '<tr>
            <td>' . $pago['tipo_pago'] . '</td>
            <td>' . ($pago['codigo_operacion'] ?: '-') . '</td>
            <td class="text-right">S/. ' . number_format($pago['monto'], 2) . '</td>
        </tr>';
    }
    
    $html .= '
            <tr class="total-row">
                <td colspan="2" class="text-right">TOTAL PAGOS:</td>
                <td class="text-right">S/. ' . number_format($total_pagos, 2) . '</td>
            </tr>
        </tbody>
    </table>';
}

if (count($creditos) > 0) {
    $html .= '
    <div class="section-title">VENTAS A CRÉDITO</div>
    <table>
        <thead>
            <tr>
                <th>N° Vale</th>
                <th>Cliente</th>
                <th class="text-right">Monto</th>
            </tr>
        </thead>
        <tbody>';
    
    foreach ($creditos as $credito) {
        $html .= '<tr>
            <td>' . $credito['numero_vale'] . '</td>
            <td>' . $credito['cliente'] . '</td>
            <td class="text-right">S/. ' . number_format($credito['monto'], 2) . '</td>
        </tr>';
    }
    
    $html .= '
            <tr class="total-row">
                <td colspan="2" class="text-right">TOTAL CRÉDITOS:</td>
                <td class="text-right">S/. ' . number_format($total_creditos, 2) . '</td>
            </tr>
        </tbody>
    </table>';
}

$faltante = $total_general - ($total_pagos + $total_creditos);
$html .= '
<div class="resumen-box" style="border-color: ' . ($faltante == 0 ? '#28a745' : ($faltante < 0 ? '#dc3545' : '#ffc107')) . ';">
    <div class="resumen-item"><strong>Total Ventas:</strong> S/. ' . number_format($total_general, 2) . '</div>
    <div class="resumen-item"><strong>Total Pagos:</strong> S/. ' . number_format($total_pagos, 2) . '</div>
    <div class="resumen-item"><strong>Total Créditos:</strong> S/. ' . number_format($total_creditos, 2) . '</div>
    <div class="resumen-item" style="font-size: 13px; color: ' . ($faltante == 0 ? '#28a745' : ($faltante < 0 ? '#dc3545' : '#ffc107')) . ';"><strong>FALTANTE/SOBRANTE:</strong> S/. ' . number_format(abs($faltante), 2) . ' ' . ($faltante < 0 ? '(FALTANTE)' : ($faltante > 0 ? '(SOBRANTE)' : '(CUADRADO)')) . '</div>
</div>

<!-- TOTALES GENERALES -->
<div class="section-title" style="background-color: #ffc107; color: #000; margin-top: 20px;">TOTALES GENERALES</div>

<table style="margin-bottom: 10px;">
    <thead style="background-color: #ffc107; color: #000;">
        <tr>
            <th colspan="2" class="text-center">VENTAS EN SOLES</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><strong>DIESEL</strong></td>
            <td class="text-right"><strong>S/. ' . number_format($total_diesel, 2) . '</strong></td>
        </tr>
        <tr>
            <td><strong>REGULAR</strong></td>
            <td class="text-right"><strong>S/. ' . number_format($total_regular, 2) . '</strong></td>
        </tr>
        <tr>
            <td><strong>PREMIUM</strong></td>
            <td class="text-right"><strong>S/. ' . number_format($total_premium, 2) . '</strong></td>
        </tr>
        <tr class="total-row">
            <td><strong>TOTAL EN SOLES</strong></td>
            <td class="text-right"><strong>S/. ' . number_format($total_general, 2) . '</strong></td>
        </tr>
    </tbody>
</table>

<table style="margin-bottom: 20px;">
    <thead style="background-color: #ffc107; color: #000;">
        <tr>
            <th colspan="2" class="text-center">VENTAS EN GALONES</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><strong>DIESEL</strong></td>
            <td class="text-right"><strong>' . number_format($galones_diesel, 3) . ' gal</strong></td>
        </tr>
        <tr>
            <td><strong>REGULAR</strong></td>
            <td class="text-right"><strong>' . number_format($galones_regular, 3) . ' gal</strong></td>
        </tr>
        <tr>
            <td><strong>PREMIUM</strong></td>
            <td class="text-right"><strong>' . number_format($galones_premium, 3) . ' gal</strong></td>
        </tr>
        <tr class="total-row">
            <td><strong>TOTAL EN GALONES</strong></td>
            <td class="text-right"><strong>' . number_format($total_galones, 3) . ' gal</strong></td>
        </tr>
    </tbody>
</table>

<!-- SECCIÓN DE FIRMAS -->
<div style="margin-top: 50px; page-break-inside: avoid;">
    <table style="border: none; width: 100%;">
        <tr>
            <td style="width: 50%; text-align: center; border: none; padding: 10px; vertical-align: bottom;">
                <div style="height: 60px;"></div>
                <div style="border-top: 2px solid #023D77; padding-top: 8px; margin: 0 40px;">
                    <div style="font-size: 11px; font-weight: bold; color: #023D77; margin-bottom: 3px;">PERSONAL DEL GRIFO</div>
                    <div style="font-size: 10px; color: #333; margin-bottom: 2px;">' . $turno['grifero_nombre'] . '</div>
                    <div style="font-size: 9px; font-weight: bold; color: #666;">FIRMA</div>
                </div>
            </td>
            <td style="width: 50%; text-align: center; border: none; padding: 10px; vertical-align: bottom;">
                <div style="height: 60px;"></div>
                <div style="border-top: 2px solid #023D77; padding-top: 8px; margin: 0 40px;">
                    <div style="font-size: 11px; font-weight: bold; color: #023D77; margin-bottom: 3px;">ADMINISTRADORA</div>
                    <div style="font-size: 10px; color: #333; margin-bottom: 2px;">Sra. Thalia Inés Palomino</div>
                    <div style="font-size: 9px; font-weight: bold; color: #666;">FIRMA</div>
                </div>
            </td>
        </tr>
    </table>
</div>

<div style="margin-top: 20px; text-align: center; font-size: 9px; color: #666;">
    <p>Reporte generado el ' . date('d/m/Y H:i:s') . '</p>
    <p>Estación Goyo - Ullputo, Chuqui</p>
</div>';

$mpdf->WriteHTML($html);
$mpdf->Output('Reporte_Turno_' . $turno['numero_documento'] . '.pdf', 'I');
?>
