<?php
/** Análisis gerencial del periodo en PDF: rentabilidad, griferos, morosidad y ventas por turno. */
require_once __DIR__ . '/../../../controller/_bootstrap.php';
ini_set('display_errors', '0');

$raiz = dirname(__DIR__, 3);
require_once $raiz . '/view/MPDF/vendor/autoload.php';
require_once $raiz . '/model/model_analisis.php';

$desde = (string) ($_GET['desde'] ?? date('Y-m-01'));
$hasta = (string) ($_GET['hasta'] ?? date('Y-m-d'));
$m = new Modelo_Analisis();
try {
    $ren = $m->Rentabilidad($desde, $hasta);
    $gri = $m->Griferos($desde, $hasta);
    $mor = $m->Morosidad();
    $hor = $m->Horario($desde, $hasta);
} catch (DomainException $e) {
    http_response_code(400);
    exit($e->getMessage());
}

// Los datos antiguos pueden venir guardados ya escapados (&amp;): se decodifican y se escapan una sola vez.
$e = fn($v) => htmlspecialchars(html_entity_decode((string) $v, ENT_QUOTES, 'UTF-8'), ENT_QUOTES, 'UTF-8');
$soles = fn($v) => $v === null ? '—' : 'S/ ' . number_format((float) $v, 2);
$gal = fn($v) => number_format((float) $v, 3);
$pct = fn($v) => $v === null ? '—' : number_format((float) $v, 1) . ' %';
$fecha = fn($v) => date('d/m/Y', strtotime($v));
$vacio = fn($cols, $texto) => '<tr><td colspan="' . $cols . '" class="vacio">' . $texto . '</td></tr>';
$empresa = conexionBD::conexionPDO()->query('SELECT nombre, telefono, direccion FROM empresa ORDER BY id_empresa LIMIT 1')->fetch(PDO::FETCH_ASSOC) ?: ['nombre' => 'Grifo'];

$filasRen = '';
foreach ($ren['productos'] as $p) {
    $filasRen .= '<tr><td>' . $e($p['nombre']) . '</td><td class="num">' . $gal($p['galones']) . '</td><td class="num">' . $soles($p['ingresos']) . '</td><td class="num">'
        . ($p['costo_galon'] !== null ? 'S/ ' . number_format($p['costo_galon'], 3) : '<span class="suave">sin compras</span>') . '</td><td class="num">' . $soles($p['margen']) . '</td><td class="num">' . $pct($p['margen_pct']) . '</td></tr>';
}
if ($ren['tienda'] !== null && $ren['tienda']['ingresos'] > 0) {
    $t = $ren['tienda'];
    $filasRen .= '<tr><td>Tienda</td><td class="num">—</td><td class="num">' . $soles($t['ingresos']) . '</td><td class="num">—</td><td class="num">' . $soles($t['margen']) . '</td><td class="num">' . $pct($t['margen_pct']) . '</td></tr>';
}
$filasRen = $filasRen ?: $vacio(6, 'No hay turnos cerrados en el periodo.');

$filasGri = '';
foreach ($gri['griferos'] as $g) {
    $filasGri .= '<tr><td>' . $g['puesto'] . '</td><td>' . $e($g['grifero']) . '</td><td class="num">' . $g['turnos'] . '</td><td class="num">' . $gal($g['galones']) . '</td><td class="num">' . $soles($g['ventas'])
        . '</td><td class="num">' . $soles($g['promedio_turno']) . '</td><td class="num' . ($g['faltantes'] > 0 ? ' alerta' : '') . '">' . $soles($g['faltantes']) . '</td><td class="num">' . $g['cuadre_pct'] . ' %</td></tr>';
}
$filasGri = $filasGri ?: $vacio(8, 'No hay turnos cerrados en el periodo.');

$filasMor = '';
foreach ($mor['clientes'] as $c) {
    $filasMor .= '<tr><td>' . $e($c['nombre_completo']) . ($c['dni_ruc'] ? '<br><span class="suave">' . $e($c['dni_ruc']) . '</span>' : '') . '</td><td class="num">' . $soles($c['pendiente']) . '</td><td class="num">' . $soles($c['por_vencer'])
        . '</td><td class="num">' . $soles($c['d1_30']) . '</td><td class="num">' . $soles($c['d31_60']) . '</td><td class="num">' . $soles($c['d61_90']) . '</td><td class="num' . ($c['d90'] > 0 ? ' alerta' : '') . '">' . $soles($c['d90']) . '</td></tr>';
}
$filasMor = $filasMor ?: $vacio(7, 'No hay créditos pendientes.');

$filasTur = '';
$orden = [2, 3, 4, 5, 6, 7, 1];
foreach ($orden as $d) {
    $celda = function ($turno) use ($hor, $d, $soles) {
        foreach ($hor['turnos'] as $t) {
            if ($t['dia'] === $d && $t['turno'] === $turno) {
                return $soles($t['promedio']) . ' <span class="suave">(' . $t['turnos'] . ')</span>';
            }
        }
        return '<span class="suave">—</span>';
    };
    $filasTur .= '<tr><td>' . Modelo_Analisis::DIAS[$d] . '</td><td class="num">' . $celda('DIA') . '</td><td class="num">' . $celda('NOCHE') . '</td></tr>';
}

$tr = $ren['totales'];
$html = '
<style>
  body { font-family: sans-serif; font-size: 9pt; color: #18181b; }
  h1 { font-size: 15pt; margin: 0; }
  h2 { font-size: 11pt; margin: 18px 0 4px; }
  .nota { color: #52525b; font-size: 8pt; margin: 0 0 6px; }
  .suave { color: #71717a; }
  table { width: 100%; border-collapse: collapse; }
  th { text-align: left; font-weight: bold; color: #52525b; border-bottom: 1px solid #d4d4d8; padding: 4px 5px; font-size: 8pt; }
  th.num { text-align: right; }
  td { border-bottom: 1px solid #e4e4e7; padding: 4px 5px; }
  .num { text-align: right; }
  .resumen td { border: 1px solid #e4e4e7; padding: 7px; width: 25%; }
  .resumen .valor { font-size: 12pt; font-weight: bold; }
  .alerta { color: #b91c1c; font-weight: bold; }
  .vacio { text-align: center; color: #71717a; padding: 10px; }
</style>
<table><tr>
  <td style="border:0; padding:0;"><h1>' . $e($empresa['nombre']) . '</h1><div class="suave">' . $e(trim(($empresa['direccion'] ?? '') . ' ' . ($empresa['telefono'] ?? ''))) . '</div></td>
  <td style="border:0; padding:0; text-align:right;"><div style="font-size:13pt; font-weight:bold;">Análisis gerencial</div><div class="suave">Del ' . $fecha($desde) . ' al ' . $fecha($hasta) . '</div></td>
</tr></table>

<h2>Rentabilidad por combustible</h2>
<p class="nota">Ingresos según las lecturas de los turnos cerrados, antes de descuentos. Costo: promedio ponderado de las compras de los ' . $ren['dias_costo'] . ' días previos al fin del periodo, sin percepción.</p>
<table class="resumen"><tr>
  <td>Ingresos<br><span class="valor">' . $soles($tr['ingresos']) . '</span></td>
  <td>Galones<br><span class="valor">' . $gal($tr['galones']) . '</span></td>
  <td>Margen bruto estimado<br><span class="valor">' . $soles($tr['margen']) . '</span></td>
  <td>Margen<br><span class="valor">' . $pct($tr['margen_pct']) . '</span></td>
</tr></table>
<table style="margin-top:6px"><thead><tr><th>Producto</th><th class="num">Galones</th><th class="num">Ingresos</th><th class="num">Costo por galón</th><th class="num">Margen</th><th class="num">%</th></tr></thead><tbody>' . $filasRen . '</tbody></table>

<h2>Ranking de griferos</h2>
<p class="nota">Turnos cerrados del periodo. Faltante: diferencia negativa del cuadre de cada turno.</p>
<table><thead><tr><th>#</th><th>Grifero</th><th class="num">Turnos</th><th class="num">Galones</th><th class="num">Ventas</th><th class="num">Por turno</th><th class="num">Faltantes</th><th class="num">Cuadrados</th></tr></thead><tbody>' . $filasGri . '</tbody></table>

<h2>Morosidad</h2>
<p class="nota">Créditos con saldo al ' . $fecha($mor['corte']) . '. Pendiente total: ' . $soles($mor['totales']['pendiente']) . '; vencido: ' . $soles($mor['totales']['vencido']) . '.</p>
<table><thead><tr><th>Cliente</th><th class="num">Pendiente</th><th class="num">Por vencer</th><th class="num">1-30 días</th><th class="num">31-60 días</th><th class="num">61-90 días</th><th class="num">Más de 90</th></tr></thead><tbody>' . $filasMor . '</tbody></table>

<h2>Venta promedio por día y turno</h2>
<p class="nota">Promedio de ventas por turno cerrado; entre paréntesis, cuántos turnos.</p>
<table style="width:60%"><thead><tr><th>Día de la semana</th><th class="num">Turno día</th><th class="num">Turno noche</th></tr></thead><tbody>' . $filasTur . '</tbody></table>
<p class="suave" style="margin-top:16px; font-size:8pt;">Generado el ' . date('d/m/Y H:i') . '.</p>';

$mpdf = new \Mpdf\Mpdf(['mode' => 'utf-8', 'format' => 'A4', 'margin_top' => 12, 'margin_bottom' => 12, 'margin_left' => 12, 'margin_right' => 12]);
$mpdf->SetTitle('Análisis gerencial ' . $desde . ' a ' . $hasta);
$mpdf->WriteHTML($html);
$mpdf->Output('analisis_' . $desde . '_' . $hasta . '.pdf', \Mpdf\Output\Destination::INLINE);
