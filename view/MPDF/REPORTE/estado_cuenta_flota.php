<?php
/** Estado de cuenta mensual de una flota en PDF. */
require_once __DIR__ . '/../../../controller/_bootstrap.php';
ini_set('display_errors', '0');

$raiz = dirname(__DIR__, 3);
require_once $raiz . '/view/MPDF/vendor/autoload.php';
require_once $raiz . '/model/model_flotas.php';

$idCliente = (int) ($_GET['id'] ?? 0);
$mes = (string) ($_GET['mes'] ?? date('Y-m'));

try {
    $d = (new Modelo_Flotas())->Detalle($idCliente, $mes);
} catch (DomainException $e) {
    http_response_code(404);
    exit('La flota no existe.');
}

// Los datos antiguos pueden venir guardados ya escapados (&amp;): se decodifican y se escapan una sola vez.
$e = fn($v) => htmlspecialchars(html_entity_decode((string) $v, ENT_QUOTES, 'UTF-8'), ENT_QUOTES, 'UTF-8');
$soles = fn($v) => 'S/ ' . number_format((float) $v, 2);
$fecha = fn($v) => $v ? date('d/m/Y', strtotime($v)) : '';
$meses = ['01' => 'enero', '02' => 'febrero', '03' => 'marzo', '04' => 'abril', '05' => 'mayo', '06' => 'junio', '07' => 'julio', '08' => 'agosto', '09' => 'septiembre', '10' => 'octubre', '11' => 'noviembre', '12' => 'diciembre'];
$nombreMes = ucfirst($meses[substr($d['mes'], 5, 2)] ?? '') . ' de ' . substr($d['mes'], 0, 4);

$empresa = conexionBD::conexionPDO()->query('SELECT nombre, telefono, direccion, email FROM empresa ORDER BY id_empresa LIMIT 1')->fetch(PDO::FETCH_ASSOC) ?: ['nombre' => 'Grifo'];
$f = $d['flota'];
$t = $d['totales'];

$filasVales = '';
foreach ($d['vales'] as $v) {
    $marca = $v['autorizada'] ? '' : ' <span class="alerta">no autorizada</span>';
    $estado = $v['estado'] === 'PAGADO' ? 'Pagado' : ($v['vencido'] ? '<span class="alerta">Vencido</span>' : 'Pendiente');
    $filasVales .= '<tr><td>' . $fecha($v['fecha']) . '</td><td>' . $e($v['numero_vale']) . '</td><td>' . $e($v['placa']) . $marca . '</td><td>' . $e($v['turno']) . '</td>'
        . '<td class="num">' . $soles($v['monto']) . '</td><td class="num">' . $soles($v['saldo_pendiente']) . '</td><td>' . $estado . '</td></tr>';
}
if ($filasVales === '') {
    $filasVales = '<tr><td colspan="7" class="vacio">Sin consumos en el mes.</td></tr>';
}

$filasPlacas = '';
// ABC123 se muestra como ABC-123.
$formatoPlaca = fn($p) => $p === 'SIN PLACA' ? 'Sin placa' : (preg_match('/^[A-Z0-9]{6}$/', $p) ? substr($p, 0, 3) . '-' . substr($p, 3) : $p);
foreach ($d['por_placa'] as $p) {
    $filasPlacas .= '<tr><td>' . $e($formatoPlaca($p['placa'])) . ($p['autorizada'] ? '' : ' <span class="alerta">no autorizada</span>') . '</td><td class="num">' . (int) $p['vales'] . '</td><td class="num">' . $soles($p['total']) . '</td></tr>';
}

$filasPagos = '';
foreach ($d['pagos'] as $p) {
    $filasPagos .= '<tr><td>' . $fecha($p['fecha_pago']) . '</td><td>' . $e($p['medio']) . '</td><td>' . $e($p['codigo_operacion']) . '</td><td>' . $e($p['numero_vale']) . '</td>'
        . '<td class="num">' . $soles((float) $p['monto_pagado'] + (float) $p['descuento']) . '</td></tr>';
}

$html = '
<style>
  body { font-family: sans-serif; font-size: 9.5pt; color: #18181b; }
  h1 { font-size: 15pt; margin: 0; }
  h2 { font-size: 11pt; margin: 18px 0 6px; }
  .suave { color: #52525b; }
  table { width: 100%; border-collapse: collapse; }
  th { text-align: left; font-weight: bold; color: #52525b; border-bottom: 1px solid #d4d4d8; padding: 5px 6px; font-size: 8.5pt; }
  td { border-bottom: 1px solid #e4e4e7; padding: 5px 6px; }
  .num { text-align: right; }
  .resumen td { border: 1px solid #e4e4e7; padding: 8px; width: 20%; }
  .resumen .valor { font-size: 12pt; font-weight: bold; }
  .alerta { color: #b91c1c; font-weight: bold; }
  .vacio { text-align: center; color: #71717a; padding: 12px; }
</style>
<table><tr>
  <td style="border:0; padding:0;"><h1>' . $e($empresa['nombre']) . '</h1>
    <div class="suave">' . $e(trim(($empresa['direccion'] ?? '') . ' ' . ($empresa['telefono'] ?? ''))) . '</div></td>
  <td style="border:0; padding:0; text-align:right;"><div style="font-size:13pt; font-weight:bold;">Estado de cuenta</div><div class="suave">' . $e($nombreMes) . '</div></td>
</tr></table>

<h2>Cliente</h2>
<table>
  <tr><td style="width:50%"><strong>' . $e($f['nombre_completo']) . '</strong><br><span class="suave">' . ($f['dni_ruc'] ? 'RUC ' . $e($f['dni_ruc']) : 'Sin RUC') . '</span></td>
      <td>' . ($f['contacto'] ? 'Contacto: ' . $e($f['contacto']) . '<br>' : '') . ($f['telefono'] ? 'Teléfono: ' . $e($f['telefono']) : '') . '</td></tr>
  <tr><td colspan="2" class="suave">Límite de crédito: ' . ($f['limite_credito'] !== null ? $soles($f['limite_credito']) : 'sin límite') . ' · Plazo: ' . (int) $f['dias_credito'] . ' días</td></tr>
</table>

<h2>Resumen</h2>
<table class="resumen"><tr>
  <td>Consumo del mes<br><span class="valor">' . $soles($t['consumo_mes']) . '</span></td>
  <td>Pagado en el mes<br><span class="valor">' . $soles($t['pagado_mes']) . '</span></td>
  <td>Deuda total<br><span class="valor">' . $soles($t['deuda']) . '</span></td>
  <td>Vencido<br><span class="valor' . ($t['vencido'] > 0 ? ' alerta' : '') . '">' . $soles($t['vencido']) . '</span></td>
  <td>Disponible<br><span class="valor">' . ($t['disponible'] !== null ? $soles($t['disponible']) : '—') . '</span></td>
</tr></table>

<h2>Vales del mes</h2>
<table><thead><tr><th>Fecha</th><th>Vale</th><th>Placa</th><th>Turno</th><th class="num">Monto</th><th class="num">Saldo</th><th>Estado</th></tr></thead><tbody>' . $filasVales . '</tbody></table>
' . ($filasPlacas !== '' ? '<h2>Consumo por placa</h2><table><thead><tr><th>Placa</th><th class="num">Vales</th><th class="num">Total</th></tr></thead><tbody>' . $filasPlacas . '</tbody></table>' : '') . '
' . ($filasPagos !== '' ? '<h2>Pagos recibidos en el mes</h2><table><thead><tr><th>Fecha</th><th>Medio</th><th>Operación</th><th>Vale</th><th class="num">Monto</th></tr></thead><tbody>' . $filasPagos . '</tbody></table>' : '') . '
<p class="suave" style="margin-top:20px; font-size:8pt;">Generado el ' . date('d/m/Y H:i') . '. La deuda total y el vencido corresponden a la fecha de generación.</p>';

$mpdf = new \Mpdf\Mpdf(['mode' => 'utf-8', 'format' => 'A4', 'margin_top' => 14, 'margin_bottom' => 14, 'margin_left' => 12, 'margin_right' => 12]);
$mpdf->SetTitle('Estado de cuenta ' . html_entity_decode((string) $f['nombre_completo'], ENT_QUOTES, 'UTF-8') . ' ' . $nombreMes);
$mpdf->WriteHTML($html);
$mpdf->Output('estado_cuenta_' . $idCliente . '_' . $d['mes'] . '.pdf', \Mpdf\Output\Destination::INLINE);
