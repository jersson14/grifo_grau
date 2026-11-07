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
$mensajeError = fn($campo) => isset($errorCampo[$campo]) ? '<p class="mt-1.5 text-base font-semibold text-reflectivo-tinta" id="error_' . $campo . '">' . $e($errorCampo[$campo]) . '</p>' : '';
?>
<!DOCTYPE html>
<html lang="es-PE">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <meta name="theme-color" content="#071d34">
  <title><?= $e($nombre) ?> · Precios de hoy, servicios y crédito para flotas</title>
  <meta name="description" content="<?= $e($datos['descripcion'] ?: $nombre) ?>">
  <link rel="preload" href="fuentes/big-shoulders-display.woff2" as="font" type="font/woff2" crossorigin>
  <link rel="stylesheet" href="css/publico.css?v=<?= (int) @filemtime(dirname(__DIR__) . '/css/publico.css') ?>">
  <link rel="stylesheet" href="plantilla/plugins/fontawesome-free/css/all.min.css">
  <?php if ($empresa['logo']): ?><link rel="icon" href="<?= $e($empresa['logo']) ?>"><?php endif; ?>
</head>
<body class="min-h-screen">
  <?php if (!empty($vistaPrevia) && !modulo_activo('pagina_publica')): ?>
  <div class="bg-hueso px-4 py-2 text-center text-base font-semibold text-tinta">Vista previa: la página aún no es pública. Actívela en Configuración → Módulos.</div>
  <?php endif; ?>

  <header class="bg-cabina-profunda">
    <div class="mx-auto flex max-w-6xl items-center gap-4 px-4 py-2.5 sm:px-6">
      <?php if ($empresa['logo']): ?>
      <a href="#inicio" class="flex min-h-11 shrink-0 items-center" aria-label="Inicio">
        <img src="<?= $e($empresa['logo']) ?>" alt="" class="h-11 w-11 rounded-full bg-hueso object-contain p-0.5">
      </a>
      <?php endif; ?>
      <nav aria-label="Secciones" class="hidden items-center gap-7 text-lg font-semibold md:flex <?= $empresa['logo'] ? 'ml-6' : '' ?>">
        <?php if ($precios): ?><a class="text-hueso/90 no-underline hover:text-hueso hover:underline" href="#precios">Precios</a><?php endif; ?>
        <?php if ($servicios): ?><a class="text-hueso/90 no-underline hover:text-hueso hover:underline" href="#servicios">Servicios</a><?php endif; ?>
        <?php if ($promociones): ?><a class="text-hueso/90 no-underline hover:text-hueso hover:underline" href="#promociones">Promociones</a><?php endif; ?>
        <a class="text-hueso/90 no-underline hover:text-hueso hover:underline" href="#flotas">Flotas</a>
        <a class="text-hueso/90 no-underline hover:text-hueso hover:underline" href="#ubicacion">Cómo llegar</a>
      </nav>
      <a href="login.php" class="ml-auto inline-flex min-h-11 shrink-0 items-center rounded-[4px] border-2 border-cabina-linea px-4 text-base font-semibold text-hueso/90 no-underline hover:border-hueso hover:text-hueso">Ingresar</a>
    </div>
  </header>
  <div class="cinta h-3" aria-hidden="true"></div>

  <main>
    <section id="inicio" class="relative">
      <div class="mx-auto max-w-6xl px-4 pt-5 sm:px-6 sm:pt-6">
        <h1 class="rotulo pintado text-center text-[clamp(2.25rem,5vw,4rem)] text-hueso"><?= $e($nombre) ?></h1>

        <div class="luna mx-auto mt-4 max-w-4xl px-5 py-6 text-center sm:mt-5 sm:px-12 sm:py-7">
          <p class="rotulo pintado text-[clamp(2.4rem,7vw,4.5rem)] leading-[0.98] text-hueso [text-wrap:balance]"><?= $e($datos['frase']) ?></p>
          <?php if ($datos['descripcion'] !== ''): ?>
          <p class="mx-auto mt-4 max-w-[52ch] text-lg leading-snug text-hueso/85 sm:text-xl"><?= $e($datos['descripcion']) ?></p>
          <?php endif; ?>
        </div>
      </div>

      <?php if ($enlaceMapa || $enlaceWhatsapp || $telefono || $precios): ?>
      <div class="defensa relative z-0 mt-5 sm:mt-6">
        <div class="mx-auto flex max-w-6xl flex-wrap items-center justify-center gap-x-5 gap-y-3 px-4 pb-5 pt-4 sm:px-6 md:justify-between">
          <div class="flex w-full gap-4 sm:w-auto">
            <?php if ($enlaceMapa): ?>
            <a class="placa placa-roja flex-1 sm:flex-none" href="<?= $e($enlaceMapa) ?>" target="_blank" rel="noopener"><i class="fas fa-map-marker-alt" aria-hidden="true"></i><span>Cómo llegar</span></a>
            <?php endif; ?>
            <?php if ($enlaceWhatsapp): ?>
            <a class="placa flex-1 sm:flex-none" href="<?= $e($enlaceWhatsapp) ?>" target="_blank" rel="noopener"><i class="fab fa-whatsapp" aria-hidden="true"></i><span>WhatsApp</span></a>
            <?php elseif ($telefono): ?>
            <a class="placa flex-1 sm:flex-none" href="tel:<?= $e($telefono) ?>"><i class="fas fa-phone" aria-hidden="true"></i><span>Llamar</span></a>
            <?php endif; ?>
          </div>
          <?php if ($precios): ?>
          <p class="text-center text-base font-semibold leading-tight text-cromo-claro sm:text-lg md:text-right">
            Precios en vivo del sistema del grifo<?php if ($actualizado): ?><br class="hidden md:inline"><span class="md:hidden"> · </span>Último cambio <?= $e($fechaCorta($actualizado)) ?><?php endif; ?>
          </p>
          <?php endif; ?>
        </div>
      </div>
      <?php endif; ?>

      <?php if ($precios): ?>
      <div id="precios" class="scroll-mt-4 px-2 sm:px-4 lg:px-8">
        <h2 class="sr-only">Precios de hoy por galón</h2>
        <ul class="grid gap-2 sm:gap-5 lg:gap-8" style="grid-template-columns: repeat(<?= min(count($precios), 4) ?>, minmax(0, 1fr));">
          <?php foreach ($precios as $p): $texto = $dinero($p['precio_actual']); ?>
          <li class="lodera">
            <span class="rotulo text-[clamp(1.05rem,3.4vw,2.25rem)] text-hueso"><?= $e($p['nombre']) ?></span>
            <span class="mt-2 flex items-end justify-center gap-1 text-hueso sm:mt-3" aria-hidden="true">
              <span class="rotulo pintado pb-[0.12em] text-[clamp(1rem,2.8vw,2rem)]">S/</span>
              <span class="rotulo numeros contador pintado text-[clamp(2.2rem,8.6vw,6rem)]" data-valor="<?= $e($texto) ?>"><?= $e($texto) ?></span>
            </span>
            <span class="sr-only"><?= $e($p['nombre']) ?>: <?= $e($texto) ?> soles por galón<?= $p['cambio'] != 0 ? ($p['cambio'] > 0 ? ', subió ' : ', bajó ') . $e($dinero(abs($p['cambio']))) . ' hoy' : '' ?></span>
            <span class="mt-1.5 text-sm font-semibold uppercase tracking-wide text-cromo sm:text-base" aria-hidden="true">por galón</span>
            <?php if ($p['cambio'] > 0): ?>
            <span class="sello sello-sube mt-3" aria-hidden="true"><i class="fas fa-arrow-up"></i>Subió <?= $e($dinero($p['cambio'])) ?></span>
            <?php elseif ($p['cambio'] < 0): ?>
            <span class="sello sello-baja mt-3" aria-hidden="true"><i class="fas fa-arrow-down"></i>Bajó <?= $e($dinero(abs($p['cambio']))) ?></span>
            <?php endif; ?>
          </li>
          <?php endforeach; ?>
        </ul>
      </div>
      <?php endif; ?>
      <div class="h-14 sm:h-20"></div>
    </section>

    <?php if ($servicios): ?>
    <section id="servicios" class="scroll-mt-4 bg-cabina-profunda py-16 sm:py-24">
      <div class="mx-auto max-w-6xl px-4 sm:px-6">
        <h2 class="rotulo text-[clamp(2.5rem,6vw,4.5rem)] text-hueso">Servicios</h2>
        <div class="franjas mt-4 max-w-xs" aria-hidden="true"></div>
        <ul class="mt-8">
          <?php foreach ($servicios as $s): ?>
          <li class="grid gap-x-10 gap-y-1 border-b-[3px] border-dashed border-cabina-linea py-6 md:grid-cols-[minmax(0,6fr)_minmax(0,5fr)] md:items-center">
            <span class="rotulo pintado flex items-center gap-4 text-[clamp(2rem,4.6vw,3.5rem)] text-hueso">
              <i class="fas fa-<?= $e($s['icono']) ?> w-[1.15em] shrink-0 text-center text-[0.8em] text-hueso" aria-hidden="true"></i><?= $e($s['titulo']) ?>
            </span>
            <?php if ($s['descripcion']): ?><span class="text-lg leading-snug text-hueso/80 sm:text-xl"><?= $e($s['descripcion']) ?></span><?php endif; ?>
          </li>
          <?php endforeach; ?>
        </ul>
      </div>
    </section>
    <?php endif; ?>

    <?php if ($promociones): ?>
    <section id="promociones" class="scroll-mt-4 bg-reflectivo-tinta py-16 text-hueso sm:py-24">
      <div class="mx-auto max-w-6xl px-4 sm:px-6">
        <h2 class="rotulo pintado text-[clamp(2.5rem,6vw,4.5rem)]">Promociones</h2>
        <ul class="mt-10 grid gap-x-12 gap-y-12 md:grid-cols-2">
          <?php foreach ($promociones as $pr):
              $terminaPronto = $pr['hasta'] && strtotime($pr['hasta']) <= strtotime('+3 days', strtotime(date('Y-m-d'))); ?>
          <li>
            <div class="<?= $terminaPronto ? 'cinta-cortada' : 'cinta' ?> h-3 [--paso:12px]" aria-hidden="true"></div>
            <p class="rotulo mt-5 text-[clamp(2rem,4.5vw,3.25rem)]"><?= $e($pr['titulo']) ?></p>
            <?php if ($pr['detalle']): ?><p class="mt-3 max-w-[60ch] text-lg leading-snug text-hueso/90"><?= $e($pr['detalle']) ?></p><?php endif; ?>
            <p class="mt-3 text-base font-bold uppercase tracking-wide">
              <?php if ($terminaPronto): ?>Termina pronto · hasta el <?= $e(date('d/m', strtotime($pr['hasta']))) ?>
              <?php elseif ($pr['hasta']): ?>Válida hasta el <?= $e(date('d/m/Y', strtotime($pr['hasta']))) ?>
              <?php else: ?>Vigente hasta nuevo aviso<?php endif; ?>
            </p>
          </li>
          <?php endforeach; ?>
        </ul>
      </div>
    </section>
    <?php endif; ?>

    <section id="flotas" class="scroll-mt-4 bg-hueso py-16 text-tinta sm:py-24">
      <div class="mx-auto grid max-w-6xl gap-12 px-4 sm:px-6 lg:grid-cols-[minmax(0,5fr)_minmax(0,7fr)]">
        <div>
          <h2 class="rotulo text-[clamp(2.5rem,6vw,4.5rem)] text-cabina"><?= $e($datos['flota_titulo'] ?: 'Crédito para tu flota') ?></h2>
          <?php if ($datos['flota_texto'] !== ''): ?><p class="mt-4 max-w-[48ch] text-xl leading-snug"><?= $e($datos['flota_texto']) ?></p><?php endif; ?>
          <ol class="ficha mt-10">
            <?php foreach ([
                ['Envía tus datos', 'Empresa, unidades y consumo aproximado al mes.'],
                ['Te contactamos', 'Revisamos la solicitud y acordamos el límite de crédito.'],
                ['Cargas con vale', 'Cada placa consume con su vale y recibes el estado de cuenta.'],
            ] as $i => [$paso, $detalle]): ?>
            <li class="grid grid-cols-[3rem_minmax(0,1fr)] gap-x-3 border-b-2 border-dashed border-cabina-linea py-4 first:pt-1 last:border-0 last:pb-1">
              <span class="rotulo pintado text-[2.75rem] leading-none text-hueso"><?= $i + 1 ?></span>
              <span>
                <span class="rotulo block text-[1.75rem] leading-none"><?= $e($paso) ?></span>
                <span class="mt-1.5 block text-lg leading-snug text-hueso/85"><?= $e($detalle) ?></span>
              </span>
            </li>
            <?php endforeach; ?>
          </ol>
        </div>

        <div class="rounded-[6px] border-[3px] border-tinta bg-white p-5 sm:p-8">
          <div id="flota_listo" class="<?= $flotaEnviada ? '' : 'hidden' ?>" role="status" tabindex="-1">
            <p class="rotulo text-[2.5rem] text-cabina">Solicitud recibida</p>
            <p class="mt-3 text-xl leading-snug">Gracias. Te llamaremos al número que dejaste para coordinar el crédito de tu flota.</p>
          </div>
          <form id="form_flota" action="publico/solicitud_flota.php" method="post" class="<?= $flotaEnviada ? 'hidden' : '' ?> grid gap-5 sm:grid-cols-2" novalidate>
            <input type="hidden" name="csrf_token" value="<?= $e(token_csrf()) ?>">
            <div class="hidden" aria-hidden="true"><label>No llenar <input type="text" name="sitio_web" tabindex="-1" autocomplete="off"></label></div>
            <?php if ($flotaError && empty($errorCampo)): ?>
            <p class="sm:col-span-2 rounded-[4px] border-2 border-reflectivo-tinta px-4 py-3 text-lg font-semibold text-reflectivo-tinta" role="alert"><?= $e($flotaError['mensaje'] ?? 'No se pudo enviar la solicitud.') ?></p>
            <?php endif; ?>
            <div class="sm:col-span-2">
              <label class="etiqueta" for="f_empresa">Empresa</label>
              <input class="campo" id="f_empresa" name="empresa" required maxlength="150" autocomplete="organization" value="<?= $valor('empresa') ?>"<?= $invalido('empresa') ?>>
              <?= $mensajeError('empresa') ?>
            </div>
            <div>
              <label class="etiqueta" for="f_ruc">RUC <span class="font-normal text-[#4a525c]">(opcional)</span></label>
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
              <label class="etiqueta" for="f_correo">Correo <span class="font-normal text-[#4a525c]">(opcional)</span></label>
              <input class="campo" id="f_correo" name="correo" type="email" maxlength="150" autocomplete="email" value="<?= $valor('correo') ?>"<?= $invalido('correo') ?>>
              <?= $mensajeError('correo') ?>
            </div>
            <div>
              <label class="etiqueta" for="f_unidades">Unidades de la flota</label>
              <input class="campo numeros" id="f_unidades" name="unidades" type="number" min="1" max="5000" inputmode="numeric" value="<?= $valor('unidades') ?>"<?= $invalido('unidades') ?>>
              <?= $mensajeError('unidades') ?>
            </div>
            <div>
              <label class="etiqueta" for="f_galones">Galones al mes <span class="font-normal text-[#4a525c]">(aprox.)</span></label>
              <input class="campo numeros" id="f_galones" name="galones_mes" type="number" min="0" inputmode="numeric" value="<?= $valor('galones_mes') ?>"<?= $invalido('galones_mes') ?>>
              <?= $mensajeError('galones_mes') ?>
            </div>
            <div class="sm:col-span-2">
              <label class="etiqueta" for="f_mensaje">Mensaje <span class="font-normal text-[#4a525c]">(opcional)</span></label>
              <textarea class="campo min-h-[7rem]" id="f_mensaje" name="mensaje" maxlength="600"<?= $invalido('mensaje') ?>><?= $valor('mensaje') ?></textarea>
              <?= $mensajeError('mensaje') ?>
            </div>
            <div class="sm:col-span-2">
              <button type="submit" class="placa placa-roja w-full sm:w-auto"><i class="fas fa-paper-plane" aria-hidden="true"></i><span>Enviar solicitud</span></button>
            </div>
          </form>
        </div>
      </div>
    </section>

    <section id="ubicacion" class="scroll-mt-4 bg-cabina py-16 sm:py-24">
      <div class="mx-auto max-w-6xl px-4 sm:px-6">
        <h2 class="rotulo text-[clamp(2.5rem,6vw,4.5rem)] text-hueso">Cómo llegar</h2>
        <div class="franjas mt-4 max-w-xs" aria-hidden="true"></div>
        <dl class="mt-10 grid gap-8 text-xl md:grid-cols-3">
          <?php if ($direccion !== ''): ?>
          <div>
            <dt class="text-base font-semibold uppercase tracking-wide text-cromo">Dirección</dt>
            <dd class="mt-2 leading-snug text-hueso"><?= $e($direccion) ?><?php if ($datos['referencia'] !== ''): ?><span class="mt-1 block text-lg text-hueso/75"><?= $e($datos['referencia']) ?></span><?php endif; ?></dd>
          </div>
          <?php endif; ?>
          <?php if ($datos['horario'] !== ''): ?>
          <div>
            <dt class="text-base font-semibold uppercase tracking-wide text-cromo">Horario</dt>
            <dd class="mt-2 leading-snug text-hueso"><?= $e($datos['horario']) ?></dd>
          </div>
          <?php endif; ?>
          <?php if ($telefono !== ''): ?>
          <div>
            <dt class="text-base font-semibold uppercase tracking-wide text-cromo">Teléfono</dt>
            <dd class="mt-2"><a class="numeros inline-flex min-h-11 items-center text-hueso hover:underline" href="tel:<?= $e($telefono) ?>"><?= $e($telefono) ?></a></dd>
          </div>
          <?php endif; ?>
        </dl>
        <?php if ($enlaceMapa): ?>
        <a class="placa placa-roja mt-10" href="<?= $e($enlaceMapa) ?>" target="_blank" rel="noopener"><i class="fas fa-route" aria-hidden="true"></i><span>Abrir en el mapa</span></a>
        <?php endif; ?>
      </div>
    </section>
  </main>

  <footer class="bg-cabina-profunda">
    <div class="cinta h-3" aria-hidden="true"></div>
    <div class="mx-auto flex max-w-6xl flex-wrap items-center gap-x-8 gap-y-4 px-4 py-8 text-base text-cromo sm:px-6">
      <span class="rotulo text-2xl text-hueso"><?= $e($nombre) ?></span>
      <?php if ($redes): ?>
      <ul class="flex gap-3" aria-label="Redes sociales">
        <?php foreach ($redes as [$red, $icono, $url]): ?>
        <li><a class="flex h-11 w-11 items-center justify-center rounded-full border-2 border-cabina-linea text-xl text-hueso hover:border-hueso hover:text-hueso" href="<?= $e($url) ?>" target="_blank" rel="noopener" aria-label="<?= $e($red) ?>"><i class="<?= $e($icono) ?>" aria-hidden="true"></i></a></li>
        <?php endforeach; ?>
      </ul>
      <?php endif; ?>
      <span class="ml-auto">© <?= date('Y') ?> <?= $e($nombre) ?> · <a class="inline-flex min-h-11 items-center text-cromo hover:text-hueso" href="login.php">Acceso del personal</a></span>
    </div>
  </footer>

  <script src="js/publico.js?v=<?= (int) @filemtime(dirname(__DIR__) . '/js/publico.js') ?>" defer></script>
</body>
</html>
