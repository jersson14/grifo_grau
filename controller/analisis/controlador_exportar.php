<?php
require_once __DIR__ . '/../_bootstrap.php';
require_once '../../model/model_analisis.php';

// Descarga una sección del análisis como CSV para Excel (UTF-8 con BOM y punto y coma, como lo espera Excel en español).
$seccion = (string) ($_GET['seccion'] ?? '');
$desde = (string) ($_GET['desde'] ?? date('Y-m-01'));
$hasta = (string) ($_GET['hasta'] ?? date('Y-m-d'));
$m = new Modelo_Analisis();
$num = fn($v, $d = 2) => $v === null ? '' : number_format((float) $v, $d, '.', '');

try {
    switch ($seccion) {
        case 'rentabilidad':
            $r = $m->Rentabilidad($desde, $hasta);
            $titulos = ['Combustible', 'Galones', 'Ingresos (S/)', 'Precio promedio (S/ por galón)', 'Costo por galón (S/)', 'Costo total (S/)', 'Margen bruto (S/)', 'Margen (%)'];
            $filas = array_map(fn($p) => [$p['nombre'], $num($p['galones'], 3), $num($p['ingresos']), $num($p['precio_promedio'], 3), $num($p['costo_galon'], 4), $num($p['costo_total']), $num($p['margen']), $num($p['margen_pct'], 1)], $r['productos']);
            if ($r['tienda'] !== null) {
                $t = $r['tienda'];
                $filas[] = ['Tienda', '', $num($t['ingresos']), '', '', $num($t['costo']), $num($t['margen']), $num($t['margen_pct'], 1)];
            }
            break;
        case 'griferos':
            $r = $m->Griferos($desde, $hasta);
            $titulos = ['Puesto', 'Grifero', 'Turnos', 'Galones', 'Ventas (S/)', 'Promedio por turno (S/)', 'Créditos (S/)', 'Faltantes (S/)', 'Sobrantes (S/)', 'Turnos cuadrados (%)'];
            $filas = array_map(fn($g) => [$g['puesto'], $g['grifero'], $g['turnos'], $num($g['galones'], 3), $num($g['ventas']), $num($g['promedio_turno']), $num($g['creditos']), $num($g['faltantes']), $num($g['sobrantes']), $g['cuadre_pct']], $r['griferos']);
            break;
        case 'morosidad':
            $r = $m->Morosidad();
            $desde = $hasta = $r['corte'];
            $titulos = ['Cliente', 'DNI/RUC', 'Teléfono', 'Vales', 'Pendiente (S/)', 'Por vencer (S/)', 'Vencido 1-30 días (S/)', 'Vencido 31-60 días (S/)', 'Vencido 61-90 días (S/)', 'Vencido más de 90 días (S/)', 'Días del vale más atrasado'];
            $filas = array_map(fn($c) => [$c['nombre_completo'], $c['dni_ruc'], $c['telefono'], $c['vales'], $num($c['pendiente']), $num($c['por_vencer']), $num($c['d1_30']), $num($c['d31_60']), $num($c['d61_90']), $num($c['d90']), $c['dias_max']], $r['clientes']);
            break;
        case 'horario':
            $r = $m->Horario($desde, $hasta);
            $titulos = ['Día', 'Hora', 'Ventas', 'Galones', 'Total (S/)'];
            usort($r['celdas'], fn($a, $b) => [$a['dia'], $a['hora']] <=> [$b['dia'], $b['hora']]);
            $filas = array_map(fn($c) => [Modelo_Analisis::DIAS[$c['dia']], sprintf('%02d:00', $c['hora']), $c['ventas'], $num($c['galones'], 3), $num($c['total'])], $r['celdas']);
            break;
        default:
            throw new DomainException('Sección desconocida.');
    }
} catch (DomainException $e) {
    http_response_code(400);
    header('Content-Type: text/plain; charset=utf-8');
    exit($e->getMessage());
}

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="analisis_' . $seccion . '_' . $desde . '_' . $hasta . '.csv"');
$salida = fopen('php://output', 'w');
fwrite($salida, "\xEF\xBB\xBF");
// Una celda que empieza con = + - @ se interpretaría como fórmula en Excel: se antepone un apóstrofo.
$celda = fn($v) => preg_match('/^[=+@\t\r]|^-(?!\d)/', (string) $v) ? "'" . $v : $v;
fputcsv($salida, $titulos, ';');
foreach ($filas as $f) {
    fputcsv($salida, array_map($celda, $f), ';');
}
fclose($salida);
