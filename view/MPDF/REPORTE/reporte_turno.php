<?php
// Error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Determine the base path for includes
// Try to find the correct base path
$possible_paths = [
    $_SERVER['DOCUMENT_ROOT'] ?? '',
    dirname(dirname(dirname(dirname(__FILE__)))), // Go up from REPORTE to grifo_grau
    '/home/estaciongoyo/public_html', // Common hosting path
];

$base_path = null;
foreach ($possible_paths as $path) {
    if (!empty($path) && file_exists($path . '/model/model_conexion.php')) {
        $base_path = $path;
        break;
    }
}

// Fallback to relative paths if base_path not found
if ($base_path === null) {
    $base_path = dirname(dirname(dirname(dirname(__FILE__))));
}

// Load dependencies
$autoload_path = $base_path . '/view/MPDF/vendor/autoload.php';
$conexion_path = $base_path . '/model/model_conexion.php';

if (!file_exists($autoload_path)) {
    die('Error: No se puede encontrar autoload.php en: ' . $autoload_path);
}

if (!file_exists($conexion_path)) {
    die('Error: No se puede encontrar model_conexion.php en: ' . $conexion_path);
}

require_once $autoload_path;
require_once $conexion_path;

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

// Obtener pagos agrupados por tipo
$sql_pagos = "SELECT 
                tp.codigo as tipo_codigo,
                tp.nombre as tipo_pago,
                pr.codigo_operacion,
                pr.monto
            FROM pagos_reporte pr
            INNER JOIN tipos_pago tp ON pr.id_tipo_pago = tp.id_tipo_pago
            WHERE pr.id_reporte = ?
            ORDER BY tp.codigo";

$stmt = $c->prepare($sql_pagos);
$stmt->execute(array($id_turno));
$pagos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Agrupar pagos por tipo
$pagos_agrupados = array(
    'YAPE' => array(),
    'BCP' => array(),
    'VISA' => array(),
    'EFECTIVO' => array()
);

$total_pagos = 0;
foreach ($pagos as $pago) {
    $tipo = strtoupper($pago['tipo_codigo']);
    if (isset($pagos_agrupados[$tipo])) {
        $pagos_agrupados[$tipo][] = $pago;
    }
    $total_pagos += floatval($pago['monto']);
}

// Calcular totales por tipo
$total_yape = 0;
$total_bcp = 0;
$total_visa = 0;
$total_efectivo = 0;

foreach ($pagos_agrupados['YAPE'] as $p) $total_yape += floatval($p['monto']);
foreach ($pagos_agrupados['BCP'] as $p) $total_bcp += floatval($p['monto']);
foreach ($pagos_agrupados['VISA'] as $p) $total_visa += floatval($p['monto']);
$total_efectivo = floatval($turno['monto_efectivo']);

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

// Array de meses en español
$meses_es = array(
    1 => 'ENERO', 2 => 'FEBRERO', 3 => 'MARZO', 4 => 'ABRIL',
    5 => 'MAYO', 6 => 'JUNIO', 7 => 'JULIO', 8 => 'AGOSTO',
    9 => 'SEPTIEMBRE', 10 => 'OCTUBRE', 11 => 'NOVIEMBRE', 12 => 'DICIEMBRE'
);
$mes_numero = date('n', strtotime($turno['fecha_reporte']));
$mes_espanol = $meses_es[$mes_numero];

$html = '
<style>
    body { font-family: Arial, sans-serif; font-size: 9px; }
    .header { text-align: center; margin-bottom: 10px; }
    .header-title { background-color: #90EE90; padding: 5px; text-align: right; font-weight: bold; font-size: 10px; }
    .info-line { margin: 3px 0; font-size: 9px; }
    table { width: 100%; border-collapse: collapse; margin-bottom: 10px; font-size: 8px; }
    th { background-color: #FFA500; color: black; padding: 4px; text-align: center; font-weight: bold; border: 1px solid #000; }
    td { border: 1px solid #000; padding: 3px; text-align: center; }
    .text-right { text-align: right; }
    .text-left { text-align: left; }
    .section-title { background-color: #FFA500; color: black; padding: 5px; margin-top: 10px; margin-bottom: 5px; font-weight: bold; text-align: center; border: 1px solid #000; }
    .total-row { background-color: #FFA500; font-weight: bold; }
    .green-header { background-color: #90EE90; font-weight: bold; }
    .firma-box { text-align: center; padding: 10px; margin-top: 30px; }
    .firma-line { border-top: 1px solid #000; margin: 0 30px; padding-top: 5px; }
</style>

<table style="width: 100%; border-collapse: collapse; margin-bottom: 10px; font-size: 9px; font-family: Arial, sans-serif;">
    <tr>
        <td colspan="3" style="width: 85%; background-color: #D3D3D3; border: 1px solid #000; padding: 5px; text-align: center; font-weight: bold;">
            REPORTE DE VENTAS DIARIAS - ' . $mes_espanol . ' DE ' . date('Y', strtotime($turno['fecha_reporte'])) . '
        </td>
        <td style="width: 15%; background-color: #90EE90; border: 1px solid #000; padding: 5px; text-align: center; font-weight: bold;">
            ' . $turno['numero_documento'] . '
        </td>
    </tr>
    <tr>
        <td colspan="2" style="width: 50%; border: 1px solid #000; padding: 5px;">
            <strong>NOMBRE DEL GRIFERO:</strong> ' . strtoupper($turno['grifero_nombre']) . '
        </td>
        <td colspan="2" style="width: 50%; border: 1px solid #000; padding: 5px;">
            <strong>TURNO:</strong> ' . $turno['turno'] . ' (Del ' . $turno['hora_inicio_formateada'] . ' al ' . $turno['hora_fin_formateada'] . ')
        </td>
    </tr>
    <tr>
        <td colspan="2" style="border: 1px solid #000; padding: 5px;">
            <strong>FECHA DE REPORTE:</strong> ' . $turno['fecha_formateada'] . '
        </td>
        <td colspan="2" style="border: 1px solid #000; padding: 5px;">
            <strong>HORARIO:</strong> ' . $turno['hora_inicio_formateada'] . ' - ' . $turno['hora_fin_formateada'] . '
        </td>
    </tr>
</table>

<!-- MÁQUINA 1 -->
<div class="section-title">MAQUINA 1</div>
<table>
    <thead>
        <tr>
            <th>FECHA</th>
            <th>PRODUCTO</th>
            <th>LECTURA ANTERIOR</th>
            <th>LECTURA ACTUAL</th>
            <th>GALONES VENDIDOS</th>
            <th>PRECIO S/.</th>
            <th>TOTAL EN SOLES</th>
        </tr>
    </thead>
    <tbody>';

$total_maq1 = 0;
foreach ($lecturas as $lectura) {
    if ($lectura['numero_maquina'] == 1) {
        $html .= '<tr>
            <td>' . date('d/m/Y', strtotime($turno['fecha_reporte'])) . '</td>
            <td class="text-left">' . $lectura['codigo'] . '-' . $lectura['nombre_producto'] . '</td>
            <td>' . number_format($lectura['lectura_anterior'], 3) . '</td>
            <td>' . number_format($lectura['lectura_actual'], 3) . '</td>
            <td>' . number_format($lectura['galones_vendidos'], 3) . '</td>
            <td>' . number_format($lectura['precio'], 2) . '</td>
            <td>' . number_format($lectura['total'], 2) . '</td>
        </tr>';
        $total_maq1 += floatval($lectura['total']);
    }
}

$html .= '
        <tr class="total-row">
            <td colspan="6" class="text-right">TOTAL 1</td>
            <td>' . number_format($total_maq1, 2) . '</td>
        </tr>
    </tbody>
</table>

<!-- MÁQUINA 2 -->
<div class="section-title">MAQUINA 2</div>
<table>
    <thead>
        <tr>
            <th>FECHA</th>
            <th>PRODUCTO</th>
            <th>LECTURA ANTERIOR</th>
            <th>LECTURA ACTUAL</th>
            <th>GALONES VENDIDOS</th>
            <th>PRECIO S/.</th>
            <th>TOTAL EN SOLES</th>
        </tr>
    </thead>
    <tbody>';

$total_maq2 = 0;
foreach ($lecturas as $lectura) {
    if ($lectura['numero_maquina'] == 2) {
        $html .= '<tr>
            <td>' . date('d/m/Y', strtotime($turno['fecha_reporte'])) . '</td>
            <td class="text-left">' . $lectura['codigo'] . '-' . $lectura['nombre_producto'] . '</td>
            <td>' . number_format($lectura['lectura_anterior'], 3) . '</td>
            <td>' . number_format($lectura['lectura_actual'], 3) . '</td>
            <td>' . number_format($lectura['galones_vendidos'], 3) . '</td>
            <td>' . number_format($lectura['precio'], 2) . '</td>
            <td>' . number_format($lectura['total'], 2) . '</td>
        </tr>';
        $total_maq2 += floatval($lectura['total']);
    }
}

$html .= '
        <tr class="total-row">
            <td colspan="6" class="text-right">TOTAL 2</td>
            <td>' . number_format($total_maq2, 2) . '</td>
        </tr>
    </tbody>
</table>

<!-- TOTALES GENERALES -->
<div class="section-title">TOTALES (1+2)</div>
<table style="margin-bottom: 5px;">
    <tr class="total-row">
        <td style="width: 70%;" class="text-right">TOTAL S/.</td>
        <td style="width: 30%;">S/. ' . number_format($total_general, 2) . '</td>
    </tr>
</table>

<!-- TOTALES POR COMBUSTIBLE -->
<table style="margin-bottom: 5px;">
    <tr class="green-header">
        <td style="width: 25%;">DIESEL</td>
        <td style="width: 25%;">GASOL_REGULAR</td>
        <td style="width: 25%;">GASOL_PREMIUM</td>
        <td style="width: 25%;">TOTAL EN SOLES</td>
    </tr>
    <tr>
        <td>' . number_format($total_diesel, 2) . '</td>
        <td>' . number_format($total_regular, 2) . '</td>
        <td>' . number_format($total_premium, 2) . '</td>
        <td style="background-color: #FFA500; font-weight: bold;">' . number_format($total_general, 2) . '</td>
    </tr>
</table>

<!-- TOTALES EN GALONES -->
<table style="margin-bottom: 10px;">
    <tr class="green-header">
        <td style="width: 25%;">DIESEL</td>
        <td style="width: 25%;">GASOL_REGULAR</td>
        <td style="width: 25%;">GASOL_PREMIUM</td>
        <td style="width: 25%;">TOTAL EN GALONES</td>
    </tr>
    <tr>
        <td>' . number_format($galones_diesel, 3) . '</td>
        <td>' . number_format($galones_regular, 3) . '</td>
        <td>' . number_format($galones_premium, 3) . '</td>
        <td style="background-color: #FFA500; font-weight: bold;">' . number_format($total_galones, 3) . '</td>
    </tr>
</table>

<!-- TABLA DE PAGOS Y CRÉDITOS -->
<table>
    <thead>
        <tr>
            <th colspan="2">YAPE</th>
            <th colspan="2">BCP</th>
            <th colspan="2">VISA</th>
            <th>DESCUENTOS</th>
            <th>EFECTIVO</th>
            <th>OTROS GASTOS</th>
            <th>N° DE VALE</th>
            <th colspan="2">MONTO DE CRÉDITO</th>
        </tr>
        <tr>
            <th>S/.</th>
            <th>COD. OPERACIÓN</th>
            <th>S/.</th>
            <th>COD. OPERACIÓN</th>
            <th>S/.</th>
            <th>COD. OPERACIÓN</th>
            <th>S/.</th>
            <th>S/.</th>
            <th>S/.</th>
            <th></th>
            <th>NOMBRE DEL CLIENTE</th>
            <th>S/.</th>
        </tr>
    </thead>
    <tbody>';

// Determinar el número máximo de filas necesarias
$max_rows = max(
    count($pagos_agrupados['YAPE']),
    count($pagos_agrupados['BCP']),
    count($pagos_agrupados['VISA']),
    count($pagos_agrupados['EFECTIVO']),
    count($creditos),
    1
);

// Generar filas
for ($i = 0; $i < $max_rows; $i++) {
    $html .= '<tr>';
    
    // YAPE
    if (isset($pagos_agrupados['YAPE'][$i])) {
        $html .= '<td>' . number_format($pagos_agrupados['YAPE'][$i]['monto'], 2) . '</td>';
        $html .= '<td>' . ($pagos_agrupados['YAPE'][$i]['codigo_operacion'] ?: '') . '</td>';
    } else {
        $html .= '<td></td><td></td>';
    }
    
    // BCP
    if (isset($pagos_agrupados['BCP'][$i])) {
        $html .= '<td>' . number_format($pagos_agrupados['BCP'][$i]['monto'], 2) . '</td>';
        $html .= '<td>' . ($pagos_agrupados['BCP'][$i]['codigo_operacion'] ?: '') . '</td>';
    } else {
        $html .= '<td></td><td></td>';
    }
    
    // VISA
    if (isset($pagos_agrupados['VISA'][$i])) {
        $html .= '<td>' . number_format($pagos_agrupados['VISA'][$i]['monto'], 2) . '</td>';
        $html .= '<td>' . ($pagos_agrupados['VISA'][$i]['codigo_operacion'] ?: '') . '</td>';
    } else {
        $html .= '<td></td><td></td>';
    }
    
    // DESCUENTOS (solo en la primera fila)
    if ($i == 0 && floatval($turno['monto_descuentos']) > 0) {
        $html .= '<td>' . number_format($turno['monto_descuentos'], 2) . '</td>';
    } else {
        $html .= '<td></td>';
    }
    
    // EFECTIVO (solo en la primera fila, usando el monto del turno)
    if ($i == 0 && floatval($turno['monto_efectivo']) > 0) {
        $html .= '<td>' . number_format($turno['monto_efectivo'], 2) . '</td>';
    } else {
        $html .= '<td></td>';
    }
    
    // OTROS GASTOS (solo en la primera fila)
    if ($i == 0 && floatval($turno['monto_otros_gastos']) > 0) {
        $html .= '<td>' . number_format($turno['monto_otros_gastos'], 2) . '</td>';
    } else {
        $html .= '<td></td>';
    }
    
    // CRÉDITOS
    if (isset($creditos[$i])) {
        $html .= '<td>' . $creditos[$i]['numero_vale'] . '</td>';
        $html .= '<td class="text-left">' . strtoupper($creditos[$i]['cliente']) . '</td>';
        $html .= '<td>' . number_format($creditos[$i]['monto'], 2) . '</td>';
    } else {
        $html .= '<td></td><td></td><td></td>';
    }
    
    $html .= '</tr>';
}

// Fila de totales
$descuentos = floatval($turno['monto_descuentos']);
$otros_gastos = floatval($turno['monto_otros_gastos']);

$html .= '
        <tr class="total-row">
            <td>' . number_format($total_yape, 2) . '</td>
            <td></td>
            <td>' . number_format($total_bcp, 2) . '</td>
            <td></td>
            <td>' . number_format($total_visa, 2) . '</td>
            <td></td>
            <td>' . number_format($descuentos, 2) . '</td>
            <td>' . number_format($total_efectivo, 2) . '</td>
            <td>' . number_format($otros_gastos, 2) . '</td>
            <td colspan="2"></td>
            <td>' . number_format($total_creditos, 2) . '</td>
        </tr>
    </tbody>
</table>';

// Resumen final
$total_ventas = $total_general;
$total_suma = $total_yape + $total_bcp + $total_visa + $total_efectivo + $total_creditos;
$monto_faltante = $total_ventas - $total_suma - $descuentos - $otros_gastos;

$html .= '
<table style="width: 50%; margin-left: auto; margin-top: 10px;">
    <tr style="background-color: #FFE4E1;">
        <td class="text-right" style="font-weight: bold;">TOTAL S/.</td>
        <td style="font-weight: bold;">S/. ' . number_format($total_ventas, 2) . '</td>
    </tr>
    <tr style="background-color: #E0FFE0;">
        <td class="text-right" style="font-weight: bold;">Total venta</td>
        <td style="font-weight: bold;">S/. ' . number_format($total_suma, 2) . '</td>
    </tr>
    <tr style="background-color: #FFE4E1;">
        <td class="text-right" style="font-weight: bold;">MONTO SOBRANTE</td>
        <td style="font-weight: bold; color: ' . ($monto_faltante < 0 ? 'red' : 'green') . ';">S/. ' . number_format($monto_faltante, 2) . '</td>
    </tr>
</table>

<!-- SECCIÓN DE FIRMAS -->
<div style="margin-top: 40px; page-break-inside: avoid;">
    <table style="border: none; width: 100%;">
        <tr>
            <td style="width: 50%; text-align: center; border: none; padding: 20px; vertical-align: bottom;">
                <div style="height: 50px;"></div>
                <div style="border-top: 1px solid #000; padding-top: 5px; margin: 0 50px;">
                    <div style="font-size: 10px; font-weight: bold; margin-bottom: 2px;">PERSONAL DEL GRIFO</div>
                    <div style="font-size: 9px; margin-bottom: 2px;">' . strtoupper($turno['grifero_nombre']) . '</div>
                    <div style="font-size: 9px; font-weight: bold;">FIRMA</div>
                </div>
            </td>
            <td style="width: 50%; text-align: center; border: none; padding: 20px; vertical-align: bottom;">
                <div style="height: 50px;"></div>
                <div style="border-top: 1px solid #000; padding-top: 5px; margin: 0 50px;">
                    <div style="font-size: 10px; font-weight: bold; margin-bottom: 2px;">ADMINISTRADORA</div>
                    <div style="font-size: 9px; margin-bottom: 2px;">Sra. Romee Azcarza Salazar</div>
                    <div style="font-size: 9px; font-weight: bold;">FIRMA</div>
                </div>
            </td>
        </tr>
    </table>
</div>';

$mpdf->WriteHTML($html);
$mpdf->Output('Reporte_Turno_' . $turno['numero_documento'] . '.pdf', 'I');
?>
