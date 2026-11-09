<?php
/**
 * Página pública del grifo. La incluye index.php cuando el módulo está activo (o en vista previa).
 * Todo el contenido sale de la base: Configuración → Página pública, Empresa y Productos.
 */
require_once dirname(__DIR__) . '/model/model_pagina_publica.php';

$e = fn($texto) => htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8');

try {
    $modelo      = new Modelo_Pagina_Publica();
    $datos       = $modelo->Datos();
    $empresa     = $modelo->Empresa();
    $precios     = $datos['mostrar_precios'] === '0' ? [] : $modelo->Precios();
    $historial   = $precios && $datos['mostrar_grafico'] !== '0' ? $modelo->Historial_Precios(90) : null;
    $servicios   = $modelo->Servicios(true);
    $promociones = $modelo->Promociones_Vigentes();
    $cifras      = $modelo->Cifras($datos, modulo_activo('flotas'));
} catch (Throwable $error) {
    error_log('Página pública: ' . $error->getMessage());
    require dirname(__DIR__) . '/login.php';
    return;
}

$nombre = $empresa['nombre'];
$direccion = $datos['direccion'] !== '' ? $datos['direccion'] : $empresa['direccion'];
$telefono = $datos['telefono'] !== '' ? $datos['telefono'] : preg_replace('/[\s-]/', '', $empresa['telefono']);

// WhatsApp: un celular peruano de 9 dígitos se completa con el código del país.
$whatsapp = $datos['whatsapp'];
if (preg_match('/^9\d{8}$/', $whatsapp)) {
    $whatsapp = '51' . $whatsapp;
}
$enlaceWhatsapp = $whatsapp !== '' ? 'https://wa.me/' . ltrim($whatsapp, '+') . '?text=' . rawurlencode('Hola ' . $nombre . ', quisiera información.') : '';
$enlaceMapa = $datos['mapa_url'] !== ''
    ? $datos['mapa_url']
    : ($direccion !== '' ? 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode($nombre . ', ' . $direccion) : '');
$mapaIncrustado = $direccion !== '' ? 'https://www.google.com/maps?output=embed&q=' . rawurlencode($nombre . ', ' . $direccion) : '';

$actualizado = null;
foreach ($precios as $p) {
    if ($p['desde'] !== null && ($actualizado === null || $p['desde'] > $actualizado)) {
        $actualizado = $p['desde'];
    }
}
$fechaCorta = function (?string $fecha): string {
    if (!$fecha) {
        return '';
    }
    $t = strtotime($fecha);
    return date('Y-m-d', $t) === date('Y-m-d') ? 'hoy a las ' . date('H:i', $t) : 'el ' . date('d/m', $t) . ' a las ' . date('H:i', $t);
};
$dinero = fn($v) => number_format((float) $v, 2, '.', ',');

// Color de cada combustible, el mismo del panel (DESIGN.md: grafico-*).
$colorDe = function (string $tipo, string $nombreProducto, int $i): string {
    $t = strtoupper($tipo . ' ' . $nombreProducto);
    if (str_contains($t, 'DIESEL') || str_contains($t, 'DB5')) { return '#2563eb'; }
    if (str_contains($t, 'PREMIUM') || str_contains($t, '95') || str_contains($t, '97')) { return '#f59e0b'; }
    if (str_contains($t, 'REGULAR') || str_contains($t, '84') || str_contains($t, '90')) { return '#059669'; }
    return ['#7c3aed', '#0891b2', '#71717a'][$i % 3];
};
$colores = [];
foreach ($precios as $i => $p) {
    $colores[$p['nombre']] = $colorDe((string) $p['tipo'], (string) $p['nombre'], $i);
}

// Gráfico de precios en SVG, armado en el servidor: se ve aunque no cargue JavaScript.
$grafico = null;
if ($historial && $historial['hay_cambios']) {
    $W = 760; $H = 300; $L = 58; $R = 20; $T = 16; $B = 38;
    $anchoUtil = $W - $L - $R; $altoUtil = $H - $T - $B;
    $todos = [];
    foreach ($historial['series'] as $s) { $todos = array_merge($todos, array_column($s['puntos'], 1)); }
    $yMin = floor((min($todos) - 0.2) * 2) / 2;
    $yMax = ceil((max($todos) + 0.2) * 2) / 2;
    $rango = max($yMax - $yMin, 0.5);
    $paso = $rango <= 3 ? 0.5 : ($rango <= 6 ? 1 : 2);
    $t0 = strtotime($historial['inicio']); $t1 = max(strtotime($historial['fin']), $t0 + 1);
    $x = fn($t) => round($L + ($t - $t0) / ($t1 - $t0) * $anchoUtil, 1);
    $y = fn($v) => round($T + ($yMax - $v) / ($yMax - $yMin) * $altoUtil, 1);

    $meses = ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'set', 'oct', 'nov', 'dic'];
    $marcasX = [];
    for ($d = strtotime(date('Y-m-01', $t0)); $d <= $t1; $d = strtotime('+1 month', $d)) {
        foreach ([$d, strtotime(date('Y-m-15', $d))] as $m) {
            if ($m > $t0 + 86400 * 3 && $m < $t1 - 86400 * 3) {
                $marcasX[] = [$x($m), (int) date('j', $m) . ' ' . $meses[(int) date('n', $m) - 1]];
            }
        }
    }
    $lineas = [];
    $datosJs = [];
    foreach ($historial['series'] as $i => $s) {
        $color = $colores[$s['nombre']] ?? $colorDe((string) $s['tipo'], (string) $s['nombre'], $i);
        $d = '';
        foreach ($s['puntos'] as $k => [$fecha, $valor]) {
            $px = $x(strtotime($fecha)); $py = $y($valor);
            $d .= $k === 0 ? "M{$px} {$py}" : " H{$px} V{$py}";
        }
        $ultimo = end($s['puntos']);
        $lineas[] = ['d' => $d, 'color' => $color, 'fx' => $x(strtotime($ultimo[0])), 'fy' => $y($ultimo[1]), 'nombre' => $s['nombre']];
        $datosJs[] = ['nombre' => $s['nombre'], 'color' => $color, 'puntos' => array_map(fn($pt) => [strtotime($pt[0]), $pt[1]], $s['puntos'])];
    }
    $grafico = [
        'W' => $W, 'H' => $H, 'L' => $L, 'R' => $R, 'T' => $T, 'B' => $B, 'lineas' => $lineas, 'marcasX' => $marcasX,
        'marcasY' => array_map(fn($v) => [$y($v), $dinero($v)], range($yMin, $yMax, $paso)),
        'js' => ['t0' => $t0, 't1' => $t1, 'L' => $L, 'ancho' => $anchoUtil, 'W' => $W, 'T' => $T, 'alto' => $altoUtil, 'yMin' => $yMin, 'yMax' => $yMax, 'series' => $datosJs],
    ];
}

$redes = array_filter([
    'facebook'  => ['Facebook', 'fab fa-facebook-f', $datos['facebook']],
    'instagram' => ['Instagram', 'fab fa-instagram', $datos['instagram']],
    'tiktok'    => ['TikTok', 'fab fa-tiktok', $datos['tiktok']],
], fn($r) => $r[2] !== '');

$abierto24 = (bool) preg_match('/24\s*h/i', $datos['horario']);

$flotaEnviada = ($_SESSION['FLOTA_AVISO'] ?? '') === 'ok';
$flotaError = is_array($_SESSION['FLOTA_AVISO'] ?? null) ? $_SESSION['FLOTA_AVISO'] : null;
unset($_SESSION['FLOTA_AVISO']);
$anterior = $flotaError['datos'] ?? [];
$errorCampo = $flotaError['errores'] ?? [];
$valor = fn($campo) => $e($anterior[$campo] ?? '');
$invalido = fn($campo) => isset($errorCampo[$campo]) ? ' aria-invalid="true" aria-describedby="error_' . $campo . '"' : '';
$mensajeError = fn($campo) => isset($errorCampo[$campo]) ? '<p class="mt-1.5 text-sm font-medium text-red-600" id="error_' . $campo . '">' . $e($errorCampo[$campo]) . '</p>' : '';

$secciones = array_filter([
    'precios'     => $precios ? 'Precios' : null,
    'servicios'   => $servicios ? 'Servicios' : null,
    'promociones' => $promociones ? 'Promociones' : null,
    'flotas'      => 'Flotas',
    'ubicacion'   => 'Ubicación',
]);
?>
<!DOCTYPE html>
<html lang="es-PE">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <meta name="theme-color" content="#09090b">
  <title><?= $e($nombre) ?> · Precios de hoy, servicios y crédito para flotas</title>
  <meta name="description" content="<?= $e($datos['descripcion'] ?: $nombre) ?>">
  <meta property="og:title" content="<?= $e($nombre) ?>">
  <meta property="og:description" content="<?= $e($datos['descripcion'] ?: $datos['frase']) ?>">
  <script>document.documentElement.classList.add('js');</script>
  <link rel="preload" href="fuentes/inter.woff2" as="font" type="font/woff2" crossorigin>
  <link rel="stylesheet" href="css/publico.css?v=<?= (int) @filemtime(dirname(__DIR__) . '/css/publico.css') ?>">
  <link rel="stylesheet" href="plantilla/plugins/fontawesome-free/css/all.min.css">
  <?php if ($empresa['logo']): ?><link rel="icon" href="<?= $e($empresa['logo']) ?>"><?php endif; ?>
</head>
<body class="min-h-screen">
  <a href="#contenido" class="saltar">Saltar al contenido</a>
  <?php if (!empty($vistaPrevia) && !modulo_activo('pagina_publica')): ?>
  <div class="border-b border-amber-200 bg-amber-50 px-4 py-2 text-center text-sm font-medium text-amber-900">Vista previa: la página aún no es pública. Actívela en Configuración → Módulos.</div>
  <?php endif; ?>

  <header id="cabecera" class="cabecera sticky top-0 z-40 border-b border-white/10 bg-zinc-950/85 text-white backdrop-blur-md">
    <div class="mx-auto flex h-16 max-w-6xl items-center gap-4 px-4 sm:px-6">
      <a href="#inicio" class="flex min-h-11 min-w-0 items-center gap-3 font-semibold text-white no-underline">
        <?php if ($empresa['logo']): ?>
        <img src="<?= $e($empresa['logo']) ?>" alt="" class="h-9 w-9 shrink-0 rounded-md bg-white object-contain p-0.5">
        <?php else: ?>
        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-md bg-blue-600 text-white" aria-hidden="true"><i class="fas fa-gas-pump"></i></span>
        <?php endif; ?>
        <span class="truncate"><?= $e($nombre) ?></span>
      </a>
      <nav aria-label="Secciones" class="ml-4 hidden items-center gap-1 text-sm md:flex">
        <?php foreach ($secciones as $ancla => $titulo): ?>
        <a class="btn btn-fantasma-oscuro h-9 px-3" href="#<?= $ancla ?>"><?= $e($titulo) ?></a>
        <?php endforeach; ?>
      </nav>
      <div class="ml-auto flex items-center gap-2">
        <?php if ($enlaceWhatsapp): ?>
        <a class="btn btn-fantasma-oscuro hidden h-10 px-3 sm:inline-flex" href="<?= $e($enlaceWhatsapp) ?>" target="_blank" rel="noopener"><i class="fab fa-whatsapp text-green-400" aria-hidden="true"></i>WhatsApp</a>
        <?php endif; ?>
        <a href="login.php" class="btn btn-claro h-10 px-4">Ingresar</a>
        <details class="menu-movil relative md:hidden">
          <summary class="btn btn-fantasma-oscuro h-11 w-11 cursor-pointer list-none px-0" aria-label="Abrir el menú"><i class="fas fa-bars" aria-hidden="true"></i></summary>
          <nav aria-label="Secciones" class="absolute right-0 top-12 w-56 rounded-xl border border-zinc-200 bg-white p-2 text-zinc-900 shadow-xl">
            <?php foreach ($secciones as $ancla => $titulo): ?>
            <a class="flex min-h-11 items-center rounded-md px-3 text-sm font-medium hover:bg-zinc-100" href="#<?= $ancla ?>"><?= $e($titulo) ?></a>
            <?php endforeach; ?>
          </nav>
        </details>
      </div>
    </div>
  </header>

  <main id="contenido">
    <!-- Portada -->
    <section id="inicio" class="fondo-portada relative overflow-hidden bg-zinc-950 text-white">
      <div class="relative mx-auto grid max-w-6xl items-center gap-12 px-4 pb-28 pt-14 sm:px-6 sm:pt-20 lg:grid-cols-[minmax(0,1.1fr)_minmax(0,1fr)] lg:pb-36">
        <div data-aparecer>
          <p class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/5 px-3 py-1 text-sm font-medium text-zinc-200">
            <?php if ($abierto24): ?>
            <span class="punto-vivo" aria-hidden="true"></span>
            <?php else: ?>
            <i class="far fa-clock text-zinc-400" aria-hidden="true"></i>
            <?php endif; ?>
            <?= $e($datos['horario'] !== '' ? $datos['horario'] : 'Estación de servicio') ?>
          </p>
          <h1 class="mt-6 text-4xl font-semibold tracking-tight text-white sm:text-5xl lg:text-6xl [text-wrap:balance]"><?= $e($datos['frase']) ?></h1>
          <?php if ($datos['descripcion'] !== ''): ?>
          <p class="mt-5 max-w-xl text-lg leading-relaxed text-zinc-300"><?= $e($datos['descripcion']) ?></p>
          <?php endif; ?>
          <div class="mt-8 flex flex-wrap gap-3">
            <?php if ($enlaceMapa): ?>
            <a class="btn btn-acento" href="<?= $e($enlaceMapa) ?>" target="_blank" rel="noopener"><i class="fas fa-location-arrow" aria-hidden="true"></i>Cómo llegar</a>
            <?php endif; ?>
            <?php if ($enlaceWhatsapp): ?>
            <a class="btn btn-contorno-oscuro" href="<?= $e($enlaceWhatsapp) ?>" target="_blank" rel="noopener"><i class="fab fa-whatsapp text-green-400" aria-hidden="true"></i>Escríbenos</a>
            <?php elseif ($telefono): ?>
            <a class="btn btn-contorno-oscuro" href="tel:<?= $e($telefono) ?>"><i class="fas fa-phone" aria-hidden="true"></i>Llamar</a>
            <?php endif; ?>
            <a class="btn btn-contorno-oscuro" href="#flotas"><i class="fas fa-truck" aria-hidden="true"></i>Crédito para flotas</a>
          </div>
          <?php if ($direccion !== ''): ?>
          <p class="mt-8 flex items-start gap-2 text-sm text-zinc-400"><i class="fas fa-map-marker-alt mt-0.5 text-zinc-500" aria-hidden="true"></i><span><?= $e($direccion) ?></span></p>
          <?php endif; ?>
        </div>

        <?php if ($precios): ?>
        <div id="tablero" class="tablero" data-aparecer>
          <div class="flex items-center justify-between gap-3 border-b border-white/10 px-5 py-4 sm:px-6">
            <h2 class="flex shrink-0 items-center gap-2 text-base font-semibold text-white"><span class="punto-vivo" aria-hidden="true"></span>Precios de hoy</h2>
            <?php if ($actualizado): ?><p class="text-right text-xs text-zinc-400">Actualizado <?= $e($fechaCorta($actualizado)) ?></p><?php endif; ?>
          </div>
          <ul class="divide-y divide-white/10">
            <?php foreach ($precios as $p): $texto = $dinero($p['precio_actual']); $color = $colores[$p['nombre']]; ?>
            <li class="precio-combustible flex items-center gap-4 px-5 py-4 sm:px-6" style="--color: <?= $e($color) ?>">
              <span class="barra-combustible" aria-hidden="true"></span>
              <span class="min-w-0 flex-1">
                <span class="block truncate text-sm font-semibold uppercase tracking-wide text-zinc-200"><?= $e($p['nombre']) ?></span>
                <?php if ($p['cambio'] > 0): ?>
                <span class="insignia insignia-sube"><i class="fas fa-arrow-up" aria-hidden="true"></i>Subió <?= $e($dinero($p['cambio'])) ?></span>
                <?php elseif ($p['cambio'] < 0): ?>
                <span class="insignia insignia-baja"><i class="fas fa-arrow-down" aria-hidden="true"></i>Bajó <?= $e($dinero(abs($p['cambio']))) ?></span>
                <?php else: ?>
                <span class="mt-1 block text-xs text-zinc-500">por galón</span>
                <?php endif; ?>
              </span>
              <p class="precio-digitos shrink-0 text-right">
                <span class="mr-1 text-lg font-medium text-zinc-400">S/</span><span class="numeros" data-valor="<?= $e($texto) ?>"><?= $e($texto) ?></span>
              </p>
            </li>
            <?php endforeach; ?>
          </ul>
          <?php if ($grafico): ?>
          <a href="#precios" class="flex min-h-11 items-center justify-center gap-2 border-t border-white/10 text-sm font-medium text-zinc-300 hover:bg-white/5 hover:text-white">Ver cómo se movieron los precios<i class="fas fa-arrow-right text-xs" aria-hidden="true"></i></a>
          <?php endif; ?>
        </div>
        <?php endif; ?>
      </div>
    </section>

    <?php if ($cifras): ?>
    <!-- Cifras -->
    <section aria-label="El grifo en cifras" class="relative z-10 -mt-16 px-4 sm:px-6 lg:-mt-20">
      <dl class="mx-auto grid max-w-6xl grid-cols-2 gap-px overflow-hidden rounded-2xl border border-zinc-200 bg-zinc-200 shadow-xl shadow-zinc-950/5 <?= ['', 'lg:grid-cols-1', 'lg:grid-cols-2', 'lg:grid-cols-3', 'lg:grid-cols-4'][count($cifras)] ?>">
        <?php foreach ($cifras as $i => $c): ?>
        <div class="cifra flex flex-col-reverse gap-1 bg-white p-5 sm:p-7">
          <dt class="text-sm leading-snug text-zinc-600"><?= $e($c['texto']) ?></dt>
          <dd class="numeros text-3xl font-semibold tracking-tight text-zinc-950 sm:text-4xl" data-cifra><?= $e($c['valor']) ?></dd>
        </div>
        <?php endforeach; ?>
      </dl>
    </section>
    <?php endif; ?>

    <?php if ($precios): ?>
    <!-- Precios: evolución y calculadora -->
    <section id="precios" class="scroll-mt-16 py-16 sm:py-24">
      <div class="mx-auto max-w-6xl px-4 sm:px-6">
        <div class="max-w-2xl" data-aparecer>
          <p class="antetitulo">Precios transparentes</p>
          <h2 class="titulo-seccion"><?= $grafico ? 'Así se movieron los precios' : 'Calcula tu carga' ?></h2>
          <p class="subtitulo-seccion"><?= $grafico ? 'El precio por galón de cada combustible en los últimos ' . (int) $historial['dias'] . ' días, tal como lo registramos en el grifo.' : 'Mira cuántos galones llevas con tu presupuesto, o cuánto cuesta llenar el tanque.' ?></p>
        </div>

        <div class="mt-10 grid grid-cols-1 gap-6 <?= $grafico ? 'lg:grid-cols-[minmax(0,1.7fr)_minmax(0,1fr)]' : 'lg:grid-cols-2' ?>">
          <?php if ($grafico): ?>
          <figure class="tarjeta min-w-0 p-4 sm:p-6" data-aparecer>
            <figcaption class="flex flex-wrap items-center justify-between gap-3">
              <span class="text-sm font-semibold text-zinc-950">Precio por galón (S/)</span>
              <ul class="flex flex-wrap gap-x-4 gap-y-1 text-sm text-zinc-600" aria-label="Leyenda">
                <?php foreach ($grafico['lineas'] as $l): ?>
                <li class="flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full" style="background: <?= $e($l['color']) ?>" aria-hidden="true"></span><?= $e($l['nombre']) ?></li>
                <?php endforeach; ?>
              </ul>
            </figcaption>
            <div class="mt-4 -mx-1 overflow-x-auto px-1">
            <div class="grafico relative min-w-[520px]" id="grafico_precios" data-grafico="<?= $e(json_encode($grafico['js'])) ?>">
              <svg viewBox="0 0 <?= $grafico['W'] ?> <?= $grafico['H'] ?>" class="h-auto w-full" role="img" aria-label="Evolución del precio por galón en los últimos <?= (int) $historial['dias'] ?> días">
                <?php foreach ($grafico['marcasY'] as [$py, $etiqueta]): ?>
                <line x1="<?= $grafico['L'] ?>" x2="<?= $grafico['W'] - $grafico['R'] ?>" y1="<?= $py ?>" y2="<?= $py ?>" class="stroke-zinc-200" stroke-width="1"/>
                <text x="<?= $grafico['L'] - 10 ?>" y="<?= $py + 5 ?>" text-anchor="end" class="fill-zinc-500 text-[15px] numeros"><?= $e($etiqueta) ?></text>
                <?php endforeach; ?>
                <?php foreach ($grafico['marcasX'] as [$px, $etiqueta]): ?>
                <text x="<?= $px ?>" y="<?= $grafico['H'] - 10 ?>" text-anchor="middle" class="fill-zinc-500 text-[15px]"><?= $e($etiqueta) ?></text>
                <?php endforeach; ?>
                <?php foreach ($grafico['lineas'] as $l): ?>
                <path d="<?= $e($l['d']) ?>" fill="none" stroke="<?= $e($l['color']) ?>" stroke-width="2.5" stroke-linejoin="round" stroke-linecap="round"/>
                <circle cx="<?= $l['fx'] ?>" cy="<?= $l['fy'] ?>" r="7" fill="<?= $e($l['color']) ?>" opacity=".18"/>
                <circle cx="<?= $l['fx'] ?>" cy="<?= $l['fy'] ?>" r="3.5" fill="<?= $e($l['color']) ?>"/>
                <?php endforeach; ?>
                <line class="guia hidden stroke-zinc-400" x1="0" x2="0" y1="<?= $grafico['T'] ?>" y2="<?= $grafico['H'] - $grafico['B'] ?>" stroke-width="1" stroke-dasharray="3 3"/>
                <g class="marcas-guia"></g>
              </svg>
              <div class="grafico-globo hidden" aria-hidden="true"></div>
            </div>
            </div>
          </figure>

          <ul class="grid content-start gap-3" aria-label="Resumen de <?= (int) $historial['dias'] ?> días">
            <?php foreach ($historial['series'] as $s):
                $color = $colores[$s['nombre']] ?? '#71717a';
                $variacion = round($s['actual'] - $s['inicial'], 2);
                $porcentaje = $s['inicial'] > 0 ? $variacion / $s['inicial'] * 100 : 0; ?>
            <li class="tarjeta p-4" data-aparecer>
              <div class="flex items-center justify-between gap-3">
                <span class="flex min-w-0 items-center gap-2 text-sm font-semibold text-zinc-950"><span class="h-2.5 w-2.5 shrink-0 rounded-full" style="background: <?= $e($color) ?>" aria-hidden="true"></span><span class="truncate"><?= $e($s['nombre']) ?></span></span>
                <?php if ($variacion > 0): ?>
                <span class="insignia border-red-200 bg-red-50 text-red-700"><i class="fas fa-arrow-up" aria-hidden="true"></i><?= $e($dinero($variacion)) ?> (<?= $e(number_format($porcentaje, 1)) ?>%)</span>
                <?php elseif ($variacion < 0): ?>
                <span class="insignia border-green-200 bg-green-50 text-green-700"><i class="fas fa-arrow-down" aria-hidden="true"></i><?= $e($dinero(abs($variacion))) ?> (<?= $e(number_format(abs($porcentaje), 1)) ?>%)</span>
                <?php else: ?>
                <span class="insignia insignia-neutra">Sin cambio</span>
                <?php endif; ?>
              </div>
              <dl class="mt-3 grid grid-cols-3 gap-2 text-center">
                <div class="rounded-md bg-zinc-50 px-2 py-2"><dt class="text-xs text-zinc-500">Mínimo</dt><dd class="numeros mt-0.5 text-sm font-semibold text-zinc-950"><?= $e($dinero($s['minimo'])) ?></dd></div>
                <div class="rounded-md bg-zinc-50 px-2 py-2"><dt class="text-xs text-zinc-500">Máximo</dt><dd class="numeros mt-0.5 text-sm font-semibold text-zinc-950"><?= $e($dinero($s['maximo'])) ?></dd></div>
                <div class="rounded-md bg-zinc-50 px-2 py-2"><dt class="text-xs text-zinc-500">Hoy</dt><dd class="numeros mt-0.5 text-sm font-semibold text-zinc-950"><?= $e($dinero($s['actual'])) ?></dd></div>
              </dl>
            </li>
            <?php endforeach; ?>
          </ul>
          <?php endif; ?>

          <div id="calculadora" class="solo-js tarjeta overflow-hidden <?= $grafico ? 'lg:col-span-2' : '' ?>" data-aparecer>
            <div class="grid grid-cols-1 lg:grid-cols-[minmax(0,1.2fr)_minmax(0,1fr)]">
              <form class="p-5 sm:p-7" onsubmit="return false">
                <p class="flex items-center gap-2 text-base font-semibold text-zinc-950"><i class="fas fa-calculator text-blue-600" aria-hidden="true"></i>Calculadora de carga</p>
                <fieldset class="mt-5">
                  <legend class="etiqueta">Combustible</legend>
                  <div class="flex flex-wrap gap-2">
                    <?php foreach ($precios as $i => $p): ?>
                    <label class="opcion">
                      <input type="radio" name="calc_combustible" class="sr-only" value="<?= $e($p['precio_actual']) ?>" data-nombre="<?= $e($p['nombre']) ?>" <?= $i === 0 ? 'checked' : '' ?>>
                      <span><span class="h-2.5 w-2.5 rounded-full" style="background: <?= $e($colores[$p['nombre']]) ?>" aria-hidden="true"></span><?= $e($p['nombre']) ?></span>
                    </label>
                    <?php endforeach; ?>
                  </div>
                </fieldset>
                <fieldset class="mt-5">
                  <legend class="etiqueta">Quiero saber</legend>
                  <div class="segmentado">
                    <label><input type="radio" name="calc_modo" value="soles" class="sr-only" checked><span>Galones por mi dinero</span></label>
                    <label><input type="radio" name="calc_modo" value="galones" class="sr-only"><span>Cuánto pago por galones</span></label>
                  </div>
                </fieldset>
                <div class="mt-5">
                  <label class="etiqueta" for="calc_monto" id="calc_etiqueta">Monto en soles</label>
                  <div class="relative">
                    <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-zinc-500" id="calc_prefijo">S/</span>
                    <input class="campo numeros pl-10 text-lg" id="calc_monto" type="number" inputmode="decimal" min="0" step="0.01" value="50">
                  </div>
                  <div class="mt-3 flex flex-wrap gap-2" id="calc_rapidos"></div>
                </div>
              </form>
              <div class="resultado-calculadora flex flex-col justify-center p-5 text-white sm:p-7" aria-live="polite">
                <p class="text-sm font-medium text-blue-100" id="calc_resultado_etiqueta">Llevas</p>
                <p class="mt-1 text-5xl font-semibold tracking-tight numeros sm:text-6xl"><span id="calc_resultado">0.00</span> <span class="text-2xl font-medium text-blue-100" id="calc_unidad">gal</span></p>
                <p class="mt-3 text-sm text-blue-100" id="calc_detalle"></p>
                <p class="mt-6 text-xs text-blue-200">Referencial, con el precio vigente de hoy.</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
    <?php endif; ?>

    <?php if ($servicios): ?>
    <!-- Servicios -->
    <section id="servicios" class="scroll-mt-16 border-t border-zinc-200 bg-zinc-50/60 py-16 sm:py-24">
      <div class="mx-auto max-w-6xl px-4 sm:px-6">
        <div class="max-w-2xl" data-aparecer>
          <p class="antetitulo">En la estación</p>
          <h2 class="titulo-seccion">Servicios</h2>
          <p class="subtitulo-seccion">Todo lo que encuentras en el grifo, en un solo lugar.</p>
        </div>
        <ul class="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
          <?php
          // El primero se destaca con el tamaño que deja la rejilla de 3 columnas sin huecos.
          $totalServicios = count($servicios);
          $formaDestacado = ['sm:col-span-2 lg:row-span-2', 'sm:col-span-2 lg:col-span-3', 'sm:col-span-2 lg:col-span-1 lg:row-span-2'][$totalServicios % 3];
          $bandaDestacado = $totalServicios % 3 === 1;
          foreach ($servicios as $i => $s): $destacado = $i === 0 && $totalServicios >= 3; ?>
          <li class="<?= $destacado ? 'servicio-destacado ' . $formaDestacado : 'tarjeta tarjeta-elevable' ?> relative overflow-hidden p-6" data-aparecer>
            <?php if ($destacado): ?><i class="fas fa-<?= $e($s['icono']) ?> marca-agua" aria-hidden="true"></i><?php endif; ?>
            <span class="<?= $destacado ? 'bg-white/10 text-white' : 'bg-blue-50 text-blue-700' ?> flex h-12 w-12 items-center justify-center rounded-lg text-lg" aria-hidden="true"><i class="fas fa-<?= $e($s['icono']) ?>"></i></span>
            <span class="<?= $destacado ? 'mt-10 text-2xl text-white' . ($bandaDestacado ? '' : ' sm:mt-24') : 'mt-5 text-base text-zinc-950' ?> relative block font-semibold"><?= $e($s['titulo']) ?></span>
            <?php if ($s['descripcion']): ?><span class="<?= $destacado ? 'max-w-md text-base text-zinc-300' : 'text-sm text-zinc-600' ?> relative mt-2 block leading-relaxed"><?= $e($s['descripcion']) ?></span><?php endif; ?>
          </li>
          <?php endforeach; ?>
        </ul>
      </div>
    </section>
    <?php endif; ?>

    <?php if ($promociones): ?>
    <!-- Promociones -->
    <section id="promociones" class="scroll-mt-16 border-t border-zinc-200 py-16 sm:py-24">
      <div class="mx-auto max-w-6xl px-4 sm:px-6">
        <div class="max-w-2xl" data-aparecer>
          <p class="antetitulo">Aprovecha</p>
          <h2 class="titulo-seccion">Promociones</h2>
        </div>
        <ul class="mt-10 grid gap-4 md:grid-cols-2 <?= count($promociones) >= 3 ? 'lg:grid-cols-3' : '' ?>">
          <?php foreach ($promociones as $pr):
              $terminaPronto = $pr['hasta'] && strtotime($pr['hasta']) <= strtotime('+3 days', strtotime(date('Y-m-d'))); ?>
          <li class="promocion" data-aparecer>
            <div class="flex items-start justify-between gap-3">
              <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-amber-100 text-amber-700" aria-hidden="true"><i class="fas fa-tag"></i></span>
              <?php if ($terminaPronto): ?>
              <span class="insignia insignia-aviso">Termina pronto · hasta el <?= $e(date('d/m', strtotime($pr['hasta']))) ?></span>
              <?php elseif ($pr['hasta']): ?>
              <span class="insignia insignia-neutra">Válida hasta el <?= $e(date('d/m/Y', strtotime($pr['hasta']))) ?></span>
              <?php else: ?>
              <span class="insignia insignia-neutra">Vigente</span>
              <?php endif; ?>
            </div>
            <p class="mt-5 text-xl font-semibold tracking-tight text-zinc-950"><?= $e($pr['titulo']) ?></p>
            <?php if ($pr['detalle']): ?><p class="mt-2 text-base leading-relaxed text-zinc-600"><?= $e($pr['detalle']) ?></p><?php endif; ?>
          </li>
          <?php endforeach; ?>
        </ul>
      </div>
    </section>
    <?php endif; ?>

    <!-- Flotas -->
    <section id="flotas" class="fondo-flotas scroll-mt-16 bg-zinc-950 py-16 text-white sm:py-24">
      <div class="mx-auto grid max-w-6xl gap-12 px-4 sm:px-6 lg:grid-cols-[minmax(0,5fr)_minmax(0,7fr)]">
        <div data-aparecer>
          <p class="antetitulo text-blue-400">Para empresas</p>
          <h2 class="titulo-seccion text-white"><?= $e($datos['flota_titulo'] ?: 'Crédito para tu flota') ?></h2>
          <?php if ($datos['flota_texto'] !== ''): ?><p class="subtitulo-seccion text-zinc-300"><?= $e($datos['flota_texto']) ?></p><?php endif; ?>
          <ul class="mt-8 grid grid-cols-2 gap-3">
            <?php foreach ([
                ['id-card', 'Consumo por placa'], ['file-invoice', 'Vales controlados'],
                ['chart-line', 'Estado de cuenta mensual'], ['hand-holding-usd', 'Línea de crédito'],
            ] as [$icono, $beneficio]): ?>
            <li class="flex items-center gap-3 rounded-lg border border-white/10 bg-white/5 p-3 text-sm font-medium text-zinc-100">
              <i class="fas fa-<?= $icono ?> w-4 text-blue-400" aria-hidden="true"></i><?= $e($beneficio) ?>
            </li>
            <?php endforeach; ?>
          </ul>
          <ol class="pasos mt-10 space-y-6">
            <?php foreach ([
                ['Envía tus datos', 'Empresa, unidades y consumo aproximado al mes.'],
                ['Te contactamos', 'Revisamos la solicitud y acordamos el límite de crédito.'],
                ['Cargas con vale', 'Cada placa consume con su vale y recibes el estado de cuenta.'],
            ] as $i => [$paso, $detalle]): ?>
            <li class="relative flex gap-4">
              <span class="relative z-10 flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-blue-600 text-sm font-semibold text-white"><?= $i + 1 ?></span>
              <span>
                <span class="block font-semibold text-white"><?= $e($paso) ?></span>
                <span class="mt-1 block text-sm leading-relaxed text-zinc-400"><?= $e($detalle) ?></span>
              </span>
            </li>
            <?php endforeach; ?>
          </ol>
        </div>

        <div class="tarjeta p-6 text-zinc-950 shadow-2xl shadow-black/40 sm:p-8" data-aparecer>
          <div id="flota_listo" class="<?= $flotaEnviada ? '' : 'hidden' ?>" role="status" tabindex="-1">
            <span class="flex h-10 w-10 items-center justify-center rounded-full bg-green-50 text-green-700"><i class="fas fa-check" aria-hidden="true"></i></span>
            <p class="mt-4 text-xl font-semibold text-zinc-950">Solicitud recibida</p>
            <p class="mt-2 text-base leading-relaxed text-zinc-600">Gracias. Te llamaremos al número que dejaste para coordinar el crédito de tu flota.</p>
          </div>
          <form id="form_flota" action="publico/solicitud_flota.php" method="post" class="<?= $flotaEnviada ? 'hidden' : '' ?> grid gap-5 sm:grid-cols-2" novalidate>
            <p class="sm:col-span-2 text-lg font-semibold text-zinc-950">Solicita tu crédito</p>
            <input type="hidden" name="csrf_token" value="<?= $e(token_csrf()) ?>">
            <div class="hidden" aria-hidden="true"><label>No llenar <input type="text" name="sitio_web" tabindex="-1" autocomplete="off"></label></div>
            <?php if ($flotaError && empty($errorCampo)): ?>
            <p class="sm:col-span-2 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700" role="alert"><?= $e($flotaError['mensaje'] ?? 'No se pudo enviar la solicitud.') ?></p>
            <?php endif; ?>
            <div class="sm:col-span-2">
              <label class="etiqueta" for="f_empresa">Empresa</label>
              <input class="campo" id="f_empresa" name="empresa" required maxlength="150" autocomplete="organization" value="<?= $valor('empresa') ?>"<?= $invalido('empresa') ?>>
              <?= $mensajeError('empresa') ?>
            </div>
            <div>
              <label class="etiqueta" for="f_ruc">RUC <span class="font-normal text-zinc-500">(opcional)</span></label>
              <input class="campo numeros" id="f_ruc" name="ruc" inputmode="numeric" maxlength="11" pattern="\d{11}" value="<?= $valor('ruc') ?>"<?= $invalido('ruc') ?>>
              <?= $mensajeError('ruc') ?>
            </div>
            <div>
              <label class="etiqueta" for="f_contacto">Persona de contacto</label>
              <input class="campo" id="f_contacto" name="contacto" required maxlength="120" autocomplete="name" value="<?= $valor('contacto') ?>"<?= $invalido('contacto') ?>>
              <?= $mensajeError('contacto') ?>
            </div>
            <div>
              <label class="etiqueta" for="f_telefono">Celular o teléfono</label>
              <input class="campo numeros" id="f_telefono" name="telefono" type="tel" required inputmode="tel" maxlength="16" autocomplete="tel" value="<?= $valor('telefono') ?>"<?= $invalido('telefono') ?>>
              <?= $mensajeError('telefono') ?>
            </div>
            <div>
              <label class="etiqueta" for="f_correo">Correo <span class="font-normal text-zinc-500">(opcional)</span></label>
              <input class="campo" id="f_correo" name="correo" type="email" maxlength="150" autocomplete="email" value="<?= $valor('correo') ?>"<?= $invalido('correo') ?>>
              <?= $mensajeError('correo') ?>
            </div>
            <div>
              <label class="etiqueta" for="f_unidades">Unidades de la flota</label>
              <input class="campo numeros" id="f_unidades" name="unidades" type="number" min="1" max="5000" inputmode="numeric" value="<?= $valor('unidades') ?>"<?= $invalido('unidades') ?>>
              <?= $mensajeError('unidades') ?>
            </div>
            <div>
              <label class="etiqueta" for="f_galones">Galones al mes <span class="font-normal text-zinc-500">(aprox.)</span></label>
              <input class="campo numeros" id="f_galones" name="galones_mes" type="number" min="0" inputmode="numeric" value="<?= $valor('galones_mes') ?>"<?= $invalido('galones_mes') ?>>
              <?= $mensajeError('galones_mes') ?>
            </div>
            <div class="sm:col-span-2">
              <label class="etiqueta" for="f_mensaje">Mensaje <span class="font-normal text-zinc-500">(opcional)</span></label>
              <textarea class="campo" id="f_mensaje" name="mensaje" maxlength="600"<?= $invalido('mensaje') ?>><?= $valor('mensaje') ?></textarea>
              <?= $mensajeError('mensaje') ?>
            </div>
            <div class="sm:col-span-2">
              <button type="submit" class="btn btn-acento w-full sm:w-auto"><i class="fas fa-paper-plane" aria-hidden="true"></i><span>Enviar solicitud</span></button>
            </div>
          </form>
        </div>
      </div>
    </section>

    <!-- Ubicación -->
    <section id="ubicacion" class="scroll-mt-16 py-16 sm:py-24">
      <div class="mx-auto max-w-6xl px-4 sm:px-6">
        <div class="max-w-2xl" data-aparecer>
          <p class="antetitulo">Visítanos</p>
          <h2 class="titulo-seccion">Ubicación y horario</h2>
        </div>
        <div class="mt-10 grid gap-6 <?= $mapaIncrustado ? 'lg:grid-cols-[minmax(0,1fr)_minmax(0,1.6fr)]' : '' ?>">
          <dl class="grid content-start gap-4 <?= $mapaIncrustado ? '' : 'md:grid-cols-3' ?>">
            <?php if ($direccion !== ''): ?>
            <div class="tarjeta flex gap-4 p-5" data-aparecer>
              <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-blue-700" aria-hidden="true"><i class="fas fa-map-marker-alt"></i></span>
              <div><dt class="text-sm font-medium text-zinc-600">Dirección</dt>
              <dd class="mt-1 text-base text-zinc-950"><?= $e($direccion) ?><?php if ($datos['referencia'] !== ''): ?><span class="mt-1 block text-sm text-zinc-600"><?= $e($datos['referencia']) ?></span><?php endif; ?></dd></div>
            </div>
            <?php endif; ?>
            <?php if ($datos['horario'] !== ''): ?>
            <div class="tarjeta flex gap-4 p-5" data-aparecer>
              <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-blue-700" aria-hidden="true"><i class="far fa-clock"></i></span>
              <div><dt class="text-sm font-medium text-zinc-600">Horario</dt>
              <dd class="mt-1 text-base text-zinc-950"><?= $e($datos['horario']) ?></dd></div>
            </div>
            <?php endif; ?>
            <?php if ($telefono !== ''): ?>
            <div class="tarjeta flex gap-4 p-5" data-aparecer>
              <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-blue-700" aria-hidden="true"><i class="fas fa-phone"></i></span>
              <div><dt class="text-sm font-medium text-zinc-600">Teléfono</dt>
              <dd><a class="numeros inline-flex min-h-11 items-center text-base text-blue-700 hover:underline" href="tel:<?= $e($telefono) ?>"><?= $e($telefono) ?></a></dd></div>
            </div>
            <?php endif; ?>
            <?php if ($enlaceMapa): ?>
            <div><a class="btn btn-primario" href="<?= $e($enlaceMapa) ?>" target="_blank" rel="noopener"><i class="fas fa-route" aria-hidden="true"></i>Abrir en el mapa</a></div>
            <?php endif; ?>
          </dl>
          <?php if ($mapaIncrustado): ?>
          <div class="tarjeta min-h-[20rem] overflow-hidden" data-aparecer>
            <iframe src="<?= $e($mapaIncrustado) ?>" title="Mapa de <?= $e($nombre) ?>" class="h-full min-h-[20rem] w-full border-0" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
          </div>
          <?php endif; ?>
        </div>
      </div>
    </section>

    <!-- Llamado final -->
    <section class="px-4 pb-16 sm:px-6 sm:pb-24">
      <div class="llamado mx-auto flex max-w-6xl flex-col items-start gap-6 rounded-2xl px-6 py-10 text-white sm:px-10 md:flex-row md:items-center md:justify-between" data-aparecer>
        <div>
          <p class="text-2xl font-semibold tracking-tight sm:text-3xl">¿Vas de ruta? Pasa a cargar</p>
          <p class="mt-2 text-blue-100"><?= $e($abierto24 ? 'Te esperamos a cualquier hora, todos los días.' : ($datos['horario'] ?: 'Te esperamos.')) ?></p>
        </div>
        <div class="flex flex-wrap gap-3">
          <?php if ($enlaceMapa): ?><a class="btn btn-claro" href="<?= $e($enlaceMapa) ?>" target="_blank" rel="noopener"><i class="fas fa-location-arrow" aria-hidden="true"></i>Cómo llegar</a><?php endif; ?>
          <?php if ($enlaceWhatsapp): ?><a class="btn btn-contorno-oscuro" href="<?= $e($enlaceWhatsapp) ?>" target="_blank" rel="noopener"><i class="fab fa-whatsapp" aria-hidden="true"></i>WhatsApp</a><?php endif; ?>
        </div>
      </div>
    </section>
  </main>

  <footer class="bg-zinc-950 text-zinc-400">
    <div class="mx-auto grid max-w-6xl gap-10 px-4 py-14 sm:px-6 md:grid-cols-[minmax(0,1.4fr)_repeat(2,minmax(0,1fr))]">
      <div>
        <p class="flex items-center gap-3 font-semibold text-white">
          <span class="flex h-9 w-9 items-center justify-center rounded-md bg-blue-600 text-white" aria-hidden="true"><i class="fas fa-gas-pump"></i></span><?= $e($nombre) ?>
        </p>
        <?php if ($datos['descripcion'] !== ''): ?><p class="mt-4 max-w-sm text-sm leading-relaxed"><?= $e($datos['descripcion']) ?></p><?php endif; ?>
        <?php if ($redes): ?>
        <ul class="mt-5 flex gap-2" aria-label="Redes sociales">
          <?php foreach ($redes as [$red, $icono, $url]): ?>
          <li><a class="btn btn-contorno-oscuro h-11 w-11 px-0" href="<?= $e($url) ?>" target="_blank" rel="noopener" aria-label="<?= $e($red) ?>"><i class="<?= $e($icono) ?>" aria-hidden="true"></i></a></li>
          <?php endforeach; ?>
        </ul>
        <?php endif; ?>
      </div>
      <nav aria-label="Pie de página">
        <p class="text-sm font-semibold text-white">Secciones</p>
        <ul class="mt-3 text-sm">
          <?php foreach ($secciones as $ancla => $titulo): ?>
          <li><a class="inline-flex min-h-9 items-center hover:text-white" href="#<?= $ancla ?>"><?= $e($titulo) ?></a></li>
          <?php endforeach; ?>
        </ul>
      </nav>
      <div>
        <p class="text-sm font-semibold text-white">Contacto</p>
        <ul class="mt-3 space-y-2 text-sm">
          <?php if ($direccion !== ''): ?><li><?= $e($direccion) ?></li><?php endif; ?>
          <?php if ($telefono !== ''): ?><li><a class="numeros hover:text-white" href="tel:<?= $e($telefono) ?>"><?= $e($telefono) ?></a></li><?php endif; ?>
          <?php if ($empresa['correo'] !== ''): ?><li><a class="hover:text-white" href="mailto:<?= $e($empresa['correo']) ?>"><?= $e($empresa['correo']) ?></a></li><?php endif; ?>
          <?php if ($datos['horario'] !== ''): ?><li><?= $e($datos['horario']) ?></li><?php endif; ?>
        </ul>
      </div>
    </div>
    <div class="border-t border-white/10">
      <div class="mx-auto flex max-w-6xl flex-wrap items-center gap-x-6 gap-y-2 px-4 py-5 text-sm sm:px-6">
        <span>© <?= date('Y') ?> <?= $e($nombre) ?></span>
        <a class="ml-auto inline-flex min-h-11 items-center hover:text-white" href="login.php">Acceso del personal</a>
      </div>
    </div>
  </footer>

  <?php if ($enlaceWhatsapp): ?>
  <a class="whatsapp-flotante" href="<?= $e($enlaceWhatsapp) ?>" target="_blank" rel="noopener" aria-label="Escríbenos por WhatsApp"><i class="fab fa-whatsapp" aria-hidden="true"></i></a>
  <?php endif; ?>

  <script src="js/publico.js?v=<?= (int) @filemtime(dirname(__DIR__) . '/js/publico.js') ?>" defer></script>
</body>
</html>
