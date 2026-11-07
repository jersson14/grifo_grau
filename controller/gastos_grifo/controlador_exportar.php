<?php
require_once __DIR__ . '/../_bootstrap.php';
require_once '../../model/model_gastos_grifo.php';

// Descarga los gastos del periodo como CSV para Excel (UTF-8 con BOM y punto y coma, como lo espera Excel en español).
$valida = fn($f) => is_string($f) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $f);
$desde = $valida($_GET['desde'] ?? null) ? $_GET['desde'] : date('Y-m-01');
$hasta = $valida($_GET['hasta'] ?? null) ? $_GET['hasta'] : date('Y-m-d');
$categoria = (int) ($_GET['id_categoria'] ?? 0);

$gastos = (new Modelo_Gastos_Grifo())->Listar($desde, $hasta, $categoria ?: null, ($_GET['anulados'] ?? '0') === '1');

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="gastos_' . $desde . '_' . $hasta . '.csv"');
$salida = fopen('php://output', 'w');
fwrite($salida, "\xEF\xBB\xBF");
// Una celda que empieza con = + - @ se interpretaría como fórmula en Excel: se antepone un apóstrofo.
$celda = fn($v) => preg_match('/^[=+\-@\t\r]/', (string) $v) ? "'" . $v : $v;
fputcsv($salida, ['Fecha', 'Categoría', 'Descripción', 'Medio de pago', 'Comprobante', 'Monto (S/)', 'Estado', 'Registrado por', 'Motivo de anulación'], ';');
foreach ($gastos as $g) {
    fputcsv($salida, array_map($celda, [
        date('d/m/Y', strtotime($g['fecha'])), $g['categoria'], $g['descripcion'], Modelo_Gastos_Grifo::MEDIOS[$g['medio_pago']] ?? $g['medio_pago'],
        $g['comprobante'], number_format((float) $g['monto'], 2, '.', ''), $g['estado'], $g['registrado_por'], $g['motivo_anulacion'],
    ]), ';');
}
fclose($salida);
