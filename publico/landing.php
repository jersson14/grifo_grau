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
    $servicios   = $modelo->Servicios(true);
    $promociones = $modelo->Promociones_Vigentes();
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

$redes = array_filter([
    'facebook'  => ['Facebook', 'fab fa-facebook-f', $datos['facebook']],
    'instagram' => ['Instagram', 'fab fa-instagram', $datos['instagram']],
    'tiktok'    => ['TikTok', 'fab fa-tiktok', $datos['tiktok']],
], fn($r) => $r[2] !== '');

$flotaEnviada = ($_SESSION['FLOTA_AVISO'] ?? '') === 'ok';
$flotaError = is_array($_SESSION['FLOTA_AVISO'] ?? null) ? $_SESSION['FLOTA_AVISO'] : null;
unset($_SESSION['FLOTA_AVISO']);
$anterior = $flotaError['datos'] ?? [];
$errorCampo = $flotaError['errores'] ?? [];
$valor = fn($campo) => $e($anterior[$campo] ?? '');
$invalido = fn($campo) => isset($errorCampo[$campo]) ? ' aria-invalid="true" aria-describedby="error_' . $campo . '"' : '';
$mensajeError = fn($campo) => isset($errorCampo[$campo]) ? '<p class="mt-1.5 text-sm font-medium text-red-600" id="error_' . $campo . '">' . $e($errorCampo[$campo]) . '</p>' : '';
?>
<!DOCTYPE html>
<html lang="es-PE">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <meta name="theme-color" content="#ffffff">
  <title><?= $e($nombre) ?> · Precios de hoy, servicios y crédito para flotas</title>
  <meta name="description" content="<?= $e($datos['descripcion'] ?: $nombre) ?>">
  <link rel="preload" href="fuentes/inter.woff2" as="font" type="font/woff2" crossorigin>
  <link rel="stylesheet" href="css/publico.css?v=<?= (int) @filemtime(dirname(__DIR__) . '/css/publico.css') ?>">
  <link rel="stylesheet" href="plantilla/plugins/fontawesome-free/css/all.min.css">
  <?php if ($empresa['logo']): ?><link rel="icon" href="<?= $e($empresa['logo']) ?>"><?php endif; ?>
</head>
<body class="min-h-screen">
  <?php if (!empty($vistaPrevia) && !modulo_activo('pagina_publica')): ?>
  <div class="border-b border-amber-200 bg-amber-50 px-4 py-2 text-center text-sm font-medium text-amber-900">Vista previa: la página aún no es pública. Actívela en Configuración → Módulos.</div>
  <?php endif; ?>

  <header class="sticky top-0 z-30 border-b border-zinc-200 bg-white">
    <div class="mx-auto flex h-16 max-w-6xl items-center gap-6 px-4 sm:px-6">
      <a href="#inicio" class="flex min-h-11 min-w-0 items-center gap-3 font-semibold text-zinc-950 no-underline">
        <?php if ($empresa['logo']): ?>
        <img src="<?= $e($empresa['logo']) ?>" alt="" class="h-9 w-9 shrink-0 rounded-md border border-zinc-200 object-contain">
        <?php endif; ?>
        <span class="truncate"><?= $e($nombre) ?></span>
      </a>
      <nav aria-label="Secciones" class="hidden items-center gap-1 text-sm md:flex">
        <?php if ($precios): ?><a class="btn btn-fantasma h-9 px-3" href="#precios">Precios</a><?php endif; ?>
        <?php if ($servicios): ?><a class="btn btn-fantasma h-9 px-3" href="#servicios">Servicios</a><?php endif; ?>
        <?php if ($promociones): ?><a class="btn btn-fantasma h-9 px-3" href="#promociones">Promociones</a><?php endif; ?>
        <a class="btn btn-fantasma h-9 px-3" href="#flotas">Flotas</a>
        <a class="btn btn-fantasma h-9 px-3" href="#ubicacion">Ubicación</a>
      </nav>
      <a href="login.php" class="btn btn-contorno ml-auto">Ingresar</a>
    </div>
  </header>

  <main>
    <section id="inicio" class="border-b border-zinc-200 bg-zinc-50/60">
      <div class="mx-auto max-w-6xl px-4 py-14 sm:px-6 sm:py-20">
        <div class="max-w-3xl">
          <h1 class="text-4xl font-semibold tracking-tight text-zinc-950 sm:text-5xl [text-wrap:balance]"><?= $e($datos['frase']) ?></h1>
          <?php if ($datos['descripcion'] !== ''): ?>
          <p class="mt-4 max-w-2xl text-lg leading-relaxed text-zinc-600"><?= $e($datos['descripcion']) ?></p>
          <?php endif; ?>
          <?php if ($enlaceMapa || $enlaceWhatsapp || $telefono): ?>
          <div class="mt-8 flex flex-wrap gap-3">
            <?php if ($enlaceMapa): ?>
            <a class="btn btn-primario" href="<?= $e($enlaceMapa) ?>" target="_blank" rel="noopener"><i class="fas fa-map-marker-alt" aria-hidden="true"></i>Cómo llegar</a>
            <?php endif; ?>
            <?php if ($enlaceWhatsapp): ?>
            <a class="btn btn-contorno" href="<?= $e($enlaceWhatsapp) ?>" target="_blank" rel="noopener"><i class="fab fa-whatsapp text-green-600" aria-hidden="true"></i>WhatsApp</a>
            <?php elseif ($telefono): ?>
            <a class="btn btn-contorno" href="tel:<?= $e($telefono) ?>"><i class="fas fa-phone" aria-hidden="true"></i>Llamar</a>
            <?php endif; ?>
          </div>
          <?php endif; ?>
        </div>

        <?php if ($precios): ?>
        <div id="precios" class="mt-12 scroll-mt-20">
          <div class="flex flex-wrap items-baseline justify-between gap-2">
            <h2 class="text-lg font-semibold text-zinc-950">Precios de hoy</h2>
            <?php if ($actualizado): ?><p class="text-sm text-zinc-600">Actualizado <?= $e($fechaCorta($actualizado)) ?></p><?php endif; ?>
          </div>
          <ul class="mt-4 grid gap-4 sm:grid-cols-2 <?= ['', 'lg:grid-cols-1', 'lg:grid-cols-2', 'lg:grid-cols-3', 'lg:grid-cols-4'][min(count($precios), 4)] ?>">
            <?php foreach ($precios as $p): $texto = $dinero($p['precio_actual']); ?>
            <li class="tarjeta precio-combustible p-5">
              <div class="flex items-start justify-between gap-3">
                <span class="text-sm font-medium text-zinc-600"><?= $e($p['nombre']) ?></span>
                <?php if ($p['cambio'] > 0): ?>
                <span class="insignia insignia-sube"><i class="fas fa-arrow-up" aria-hidden="true"></i>Subió <?= $e($dinero($p['cambio'])) ?></span>
                <?php elseif ($p['cambio'] < 0): ?>
                <span class="insignia insignia-baja"><i class="fas fa-arrow-down" aria-hidden="true"></i>Bajó <?= $e($dinero(abs($p['cambio']))) ?></span>
                <?php endif; ?>
              </div>
              <p class="mt-3 text-3xl font-semibold tracking-tight text-zinc-950">
                <span class="numeros" data-valor="<?= $e($texto) ?>">S/ <?= $e($texto) ?></span>
              </p>
              <p class="mt-1 text-sm text-zinc-600">por galón</p>
            </li>
            <?php endforeach; ?>
          </ul>
        </div>
        <?php endif; ?>
      </div>
    </section>

    <?php if ($servicios): ?>
    <section id="servicios" class="scroll-mt-16 py-16 sm:py-24">
      <div class="mx-auto max-w-6xl px-4 sm:px-6">
        <h2 class="titulo-seccion">Servicios</h2>
        <p class="subtitulo-seccion">Todo lo que encuentras en el grifo.</p>
        <ul class="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
          <?php foreach ($servicios as $s): ?>
          <li class="tarjeta flex gap-4 p-5">
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-blue-50 text-blue-700" aria-hidden="true"><i class="fas fa-<?= $e($s['icono']) ?>"></i></span>
            <span>
              <span class="block font-semibold text-zinc-950"><?= $e($s['titulo']) ?></span>
              <?php if ($s['descripcion']): ?><span class="mt-1 block text-sm leading-relaxed text-zinc-600"><?= $e($s['descripcion']) ?></span><?php endif; ?>
            </span>
          </li>
          <?php endforeach; ?>
        </ul>
      </div>
    </section>
    <?php endif; ?>

    <?php if ($promociones): ?>
    <section id="promociones" class="scroll-mt-16 border-t border-zinc-200 bg-zinc-50/60 py-16 sm:py-24">
      <div class="mx-auto max-w-6xl px-4 sm:px-6">
        <h2 class="titulo-seccion">Promociones</h2>
        <ul class="mt-10 grid gap-4 md:grid-cols-2">
          <?php foreach ($promociones as $pr):
              $terminaPronto = $pr['hasta'] && strtotime($pr['hasta']) <= strtotime('+3 days', strtotime(date('Y-m-d'))); ?>
          <li class="tarjeta p-6">
            <?php if ($terminaPronto): ?>
            <span class="insignia insignia-aviso">Termina pronto · hasta el <?= $e(date('d/m', strtotime($pr['hasta']))) ?></span>
            <?php elseif ($pr['hasta']): ?>
            <span class="insignia insignia-neutra">Válida hasta el <?= $e(date('d/m/Y', strtotime($pr['hasta']))) ?></span>
            <?php else: ?>
            <span class="insignia insignia-neutra">Vigente</span>
            <?php endif; ?>
            <p class="mt-4 text-xl font-semibold tracking-tight text-zinc-950"><?= $e($pr['titulo']) ?></p>
            <?php if ($pr['detalle']): ?><p class="mt-2 text-base leading-relaxed text-zinc-600"><?= $e($pr['detalle']) ?></p><?php endif; ?>
          </li>
          <?php endforeach; ?>
        </ul>
      </div>
    </section>
    <?php endif; ?>

    <section id="flotas" class="scroll-mt-16 border-t border-zinc-200 py-16 sm:py-24">
      <div class="mx-auto grid max-w-6xl gap-12 px-4 sm:px-6 lg:grid-cols-[minmax(0,5fr)_minmax(0,7fr)]">
        <div>
          <h2 class="titulo-seccion"><?= $e($datos['flota_titulo'] ?: 'Crédito para tu flota') ?></h2>
          <?php if ($datos['flota_texto'] !== ''): ?><p class="subtitulo-seccion"><?= $e($datos['flota_texto']) ?></p><?php endif; ?>
          <ol class="mt-8 space-y-6">
            <?php foreach ([
                ['Envía tus datos', 'Empresa, unidades y consumo aproximado al mes.'],
                ['Te contactamos', 'Revisamos la solicitud y acordamos el límite de crédito.'],
                ['Cargas con vale', 'Cada placa consume con su vale y recibes el estado de cuenta.'],
            ] as $i => [$paso, $detalle]): ?>
            <li class="flex gap-4">
              <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-zinc-900 text-sm font-semibold text-white"><?= $i + 1 ?></span>
              <span>
                <span class="block font-semibold text-zinc-950"><?= $e($paso) ?></span>
                <span class="mt-1 block text-sm leading-relaxed text-zinc-600"><?= $e($detalle) ?></span>
              </span>
            </li>
            <?php endforeach; ?>
          </ol>
        </div>

        <div class="tarjeta p-6 sm:p-8">
          <div id="flota_listo" class="<?= $flotaEnviada ? '' : 'hidden' ?>" role="status" tabindex="-1">
            <span class="flex h-10 w-10 items-center justify-center rounded-full bg-green-50 text-green-700"><i class="fas fa-check" aria-hidden="true"></i></span>
            <p class="mt-4 text-xl font-semibold text-zinc-950">Solicitud recibida</p>
            <p class="mt-2 text-base leading-relaxed text-zinc-600">Gracias. Te llamaremos al número que dejaste para coordinar el crédito de tu flota.</p>
          </div>
          <form id="form_flota" action="publico/solicitud_flota.php" method="post" class="<?= $flotaEnviada ? 'hidden' : '' ?> grid gap-5 sm:grid-cols-2" novalidate>
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
              <button type="submit" class="btn btn-primario w-full sm:w-auto"><i class="fas fa-paper-plane" aria-hidden="true"></i><span>Enviar solicitud</span></button>
            </div>
          </form>
        </div>
      </div>
    </section>

    <section id="ubicacion" class="scroll-mt-16 border-t border-zinc-200 bg-zinc-50/60 py-16 sm:py-24">
      <div class="mx-auto max-w-6xl px-4 sm:px-6">
        <h2 class="titulo-seccion">Ubicación y horario</h2>
        <dl class="mt-10 grid gap-4 md:grid-cols-3">
          <?php if ($direccion !== ''): ?>
          <div class="tarjeta p-5">
            <dt class="text-sm font-medium text-zinc-600">Dirección</dt>
            <dd class="mt-2 text-base text-zinc-950"><?= $e($direccion) ?><?php if ($datos['referencia'] !== ''): ?><span class="mt-1 block text-sm text-zinc-600"><?= $e($datos['referencia']) ?></span><?php endif; ?></dd>
          </div>
          <?php endif; ?>
          <?php if ($datos['horario'] !== ''): ?>
          <div class="tarjeta p-5">
            <dt class="text-sm font-medium text-zinc-600">Horario</dt>
            <dd class="mt-2 text-base text-zinc-950"><?= $e($datos['horario']) ?></dd>
          </div>
          <?php endif; ?>
          <?php if ($telefono !== ''): ?>
          <div class="tarjeta p-5">
            <dt class="text-sm font-medium text-zinc-600">Teléfono</dt>
            <dd class="mt-1"><a class="numeros inline-flex min-h-11 items-center text-base text-blue-700 hover:underline" href="tel:<?= $e($telefono) ?>"><?= $e($telefono) ?></a></dd>
          </div>
          <?php endif; ?>
        </dl>
        <?php if ($enlaceMapa): ?>
        <a class="btn btn-primario mt-8" href="<?= $e($enlaceMapa) ?>" target="_blank" rel="noopener"><i class="fas fa-route" aria-hidden="true"></i>Abrir en el mapa</a>
        <?php endif; ?>
      </div>
    </section>
  </main>

  <footer class="border-t border-zinc-200">
    <div class="mx-auto flex max-w-6xl flex-wrap items-center gap-x-6 gap-y-3 px-4 py-6 text-sm text-zinc-600 sm:px-6">
      <span>© <?= date('Y') ?> <?= $e($nombre) ?></span>
      <?php if ($redes): ?>
      <ul class="flex gap-1" aria-label="Redes sociales">
        <?php foreach ($redes as [$red, $icono, $url]): ?>
        <li><a class="btn btn-fantasma h-11 w-11 px-0" href="<?= $e($url) ?>" target="_blank" rel="noopener" aria-label="<?= $e($red) ?>"><i class="<?= $e($icono) ?>" aria-hidden="true"></i></a></li>
        <?php endforeach; ?>
      </ul>
      <?php endif; ?>
      <a class="ml-auto inline-flex min-h-11 items-center text-zinc-600 hover:text-zinc-950" href="login.php">Acceso del personal</a>
    </div>
  </footer>

  <script src="js/publico.js?v=<?= (int) @filemtime(dirname(__DIR__) . '/js/publico.js') ?>" defer></script>
</body>
</html>
