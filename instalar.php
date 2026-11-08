<?php
/**
 * Asistente de instalación de un grifo nuevo. Solo funciona mientras no existe ningún usuario:
 * después redirige al acceso. Tiene su propio token CSRF porque es una ruta pública.
 */
require_once __DIR__ . '/controller/_bootstrap.php';
require_once __DIR__ . '/model/model_instalacion.php';

if (!Modelo_Instalacion::Pendiente()) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'message' => 'El sistema ya está instalado. Inicie sesión.', 'instalado' => true]);
    } else {
        header('Location: login.php');
    }
    exit;
}

if (empty($_SESSION['TOKEN_INSTALACION'])) {
    $_SESSION['TOKEN_INSTALACION'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8');
    $cuerpo = json_decode((string) file_get_contents('php://input'), true);
    if (!is_array($cuerpo) || !is_string($cuerpo['token'] ?? null) || !hash_equals($_SESSION['TOKEN_INSTALACION'], $cuerpo['token'])) {
        echo json_encode(['success' => false, 'message' => 'La página caducó. Recárguela e inténtelo de nuevo.']);
        exit;
    }
    try {
        (new Modelo_Instalacion())->Instalar($cuerpo['datos'] ?? []);
        unset($_SESSION['TOKEN_INSTALACION']);
        echo json_encode(['success' => true]);
    } catch (DomainException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    } catch (Throwable $e) {
        error_log('Instalación: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'No se pudo completar la instalación. Revise el registro de errores.']);
    }
    exit;
}

$e = fn($t) => htmlspecialchars((string) $t, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex">
  <title>Instalación · Sistema de Gestión de Grifo</title>
  <link rel="icon" href="img/grau.png">
  <link rel="preload" href="fuentes/inter.woff2" as="font" type="font/woff2" crossorigin>
  <link rel="stylesheet" href="css/publico.css?v=<?php echo filemtime(__DIR__ . '/css/publico.css'); ?>">
</head>
<body class="bg-zinc-50 text-zinc-900 antialiased">
  <main class="mx-auto max-w-3xl px-4 py-10 sm:py-14">
    <header class="mb-8">
      <p class="text-sm font-medium text-blue-700">Primera puesta en marcha</p>
      <h1 class="mt-1 text-3xl font-semibold tracking-tight text-zinc-950">Instalar el sistema del grifo</h1>
      <p class="mt-2 text-zinc-600">Complete estos cuatro pasos una sola vez. Todo se puede cambiar después desde el panel: la empresa en Configuración, los precios en Productos y los surtidores en Surtidores.</p>
    </header>

    <form id="form_instalar" class="space-y-6" novalidate>
      <section class="tarjeta p-5 sm:p-6" aria-labelledby="paso1">
        <h2 id="paso1" class="flex items-center gap-3 text-lg font-semibold"><span class="paso">1</span>Datos del grifo</h2>
        <div class="mt-4 grid gap-4 sm:grid-cols-2">
          <div class="sm:col-span-2"><label class="etiqueta" for="em_nombre">Nombre del grifo</label><input class="campo" id="em_nombre" maxlength="200" required autocomplete="organization" placeholder="Estación de servicios Grau"></div>
          <div><label class="etiqueta" for="em_ruc">RUC <span class="font-normal text-zinc-500">(opcional)</span></label><input class="campo" id="em_ruc" inputmode="numeric" maxlength="11" placeholder="20123456789"></div>
          <div><label class="etiqueta" for="em_telefono">Teléfono <span class="font-normal text-zinc-500">(opcional)</span></label><input class="campo" id="em_telefono" maxlength="30" inputmode="tel" autocomplete="tel"></div>
          <div class="sm:col-span-2"><label class="etiqueta" for="em_direccion">Dirección <span class="font-normal text-zinc-500">(opcional)</span></label><input class="campo" id="em_direccion" maxlength="255" autocomplete="street-address"></div>
        </div>
      </section>

      <section class="tarjeta p-5 sm:p-6" aria-labelledby="paso2">
        <h2 id="paso2" class="flex items-center gap-3 text-lg font-semibold"><span class="paso">2</span>Administrador</h2>
        <p class="mt-1 text-sm text-zinc-600">La persona que configura el sistema y crea los usuarios de los griferos.</p>
        <div class="mt-4 grid gap-4 sm:grid-cols-2">
          <div><label class="etiqueta" for="ad_nombres">Nombres</label><input class="campo" id="ad_nombres" maxlength="80" required autocomplete="given-name"></div>
          <div><label class="etiqueta" for="ad_apellidos">Apellidos <span class="font-normal text-zinc-500">(opcional)</span></label><input class="campo" id="ad_apellidos" maxlength="80" autocomplete="family-name"></div>
          <div class="sm:col-span-2"><label class="etiqueta" for="ad_usuario">Usuario para entrar</label><input class="campo" id="ad_usuario" maxlength="30" required autocomplete="username" autocapitalize="none" spellcheck="false" placeholder="administrador" aria-describedby="ad_usuario_ayuda"><p id="ad_usuario_ayuda" class="mt-1 text-xs text-zinc-500">Letras, números, punto o guion, sin espacios.</p></div>
          <div><label class="etiqueta" for="ad_clave">Contraseña</label><input class="campo" id="ad_clave" type="password" minlength="8" maxlength="72" required autocomplete="new-password" aria-describedby="ad_clave_ayuda"><p id="ad_clave_ayuda" class="mt-1 text-xs text-zinc-500">Al menos 8 caracteres; evite claves como 123456.</p></div>
          <div><label class="etiqueta" for="ad_repetir">Repita la contraseña</label><input class="campo" id="ad_repetir" type="password" required autocomplete="new-password"></div>
        </div>
      </section>

      <section class="tarjeta p-5 sm:p-6" aria-labelledby="paso3">
        <h2 id="paso3" class="flex items-center gap-3 text-lg font-semibold"><span class="paso">3</span>Combustibles y precios</h2>
        <p class="mt-1 text-sm text-zinc-600">Precio por galón de hoy. Quite los que el grifo no vende.</p>
        <div id="lista_combustibles" class="mt-4 space-y-3"></div>
        <button type="button" class="btn btn-contorno mt-3" id="agregar_combustible">Agregar combustible</button>
      </section>

      <section class="tarjeta p-5 sm:p-6" aria-labelledby="paso4">
        <h2 id="paso4" class="flex items-center gap-3 text-lg font-semibold"><span class="paso">4</span>Surtidores</h2>
        <p class="mt-1 text-sm text-zinc-600">Cada manguera con su código, su combustible y la lectura que marca hoy su contómetro. El primer turno empieza desde esa lectura.</p>
        <div id="lista_surtidores" class="mt-4 space-y-3"></div>
        <div class="mt-3 flex flex-wrap gap-2">
          <button type="button" class="btn btn-contorno" id="agregar_maquina">Agregar una máquina</button>
          <button type="button" class="btn btn-fantasma" id="agregar_surtidor">Agregar un surtidor</button>
        </div>
      </section>

      <div id="mensaje" class="hidden rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800" role="alert"></div>
      <div class="flex flex-wrap items-center gap-3">
        <button type="submit" class="btn btn-primario h-11 px-6" id="instalar">Instalar</button>
        <p class="text-sm text-zinc-500">Se guarda todo junto; si algo falta, nada queda a medias.</p>
      </div>
    </form>

    <section id="listo" class="tarjeta hidden p-6 text-center" aria-live="polite">
      <h2 class="text-xl font-semibold">El sistema está instalado</h2>
      <p class="mt-2 text-zinc-600">Entre con el usuario que acaba de crear. Después, en Usuarios, cree las cuentas de los griferos.</p>
      <a class="btn btn-primario mt-5 inline-flex" href="login.php">Ir al acceso</a>
    </section>
  </main>

<script>
(function () {
  var TOKEN = <?php echo json_encode($_SESSION['TOKEN_INSTALACION']); ?>;
  var TIPOS = ['DIESEL', 'REGULAR', 'PREMIUM', 'GLP', 'GNV', 'OTRO'];
  var SUGERIDOS = <?php echo json_encode(Modelo_Instalacion::COMBUSTIBLES_SUGERIDOS); ?>;
  var listaC = document.getElementById('lista_combustibles');
  var listaS = document.getElementById('lista_surtidores');

  function el(etiqueta, atributos, hijos) {
    var n = document.createElement(etiqueta);
    Object.keys(atributos || {}).forEach(function (k) { if (k === 'texto') { n.textContent = atributos[k]; } else { n.setAttribute(k, atributos[k]); } });
    (hijos || []).forEach(function (h) { n.appendChild(h); });
    return n;
  }
  var cuenta = 0;
  function campo(etiqueta, input, clase) {
    var id = 'c' + (++cuenta);
    input.id = id;
    return el('div', { class: clase || '' }, [el('label', { class: 'etiqueta', for: id, texto: etiqueta }), input]);
  }
  function quitar(fila, nombre, despues) {
    return el('button', { type: 'button', class: 'btn btn-fantasma h-10 text-red-700', 'aria-label': 'Quitar ' + nombre, texto: 'Quitar' });
  }

  function combustibles() {
    return Array.prototype.map.call(listaC.children, function (f) {
      return { nombre: f.querySelector('.c-nombre').value.trim(), tipo: f.querySelector('.c-tipo').value, precio: f.querySelector('.c-precio').value };
    });
  }
  function refrescarOpciones() {
    var nombres = combustibles().map(function (c, i) { return c.nombre || ('Combustible ' + (i + 1)); });
    listaS.querySelectorAll('.s-combustible').forEach(function (sel) {
      var elegido = sel.value;
      sel.innerHTML = '';
      sel.appendChild(el('option', { value: '', texto: 'Elija el combustible' }));
      nombres.forEach(function (n, i) { sel.appendChild(el('option', { value: String(i), texto: n })); });
      if (elegido !== '' && Number(elegido) < nombres.length) { sel.value = elegido; }
    });
  }

  function agregarCombustible(c) {
    var tipo = el('select', { class: 'campo c-tipo' });
    TIPOS.forEach(function (t) { tipo.appendChild(el('option', { value: t, texto: t })); });
    tipo.value = c.tipo || 'OTRO';
    var fila = el('div', { class: 'grid items-end gap-3 rounded-lg border border-zinc-200 p-3 sm:grid-cols-[1fr_9rem_8rem_auto]' }, [
      campo('Nombre', el('input', { class: 'campo c-nombre', maxlength: '100', value: c.nombre || '', required: 'required' })),
      campo('Tipo', tipo),
      campo('Precio (S/)', el('input', { class: 'campo c-precio numeros', type: 'number', step: '0.001', min: '0.001', inputmode: 'decimal', value: c.precio || '', required: 'required' }))
    ]);
    var boton = quitar();
    boton.setAttribute('aria-label', 'Quitar este combustible');
    boton.addEventListener('click', function () {
      var i = Array.prototype.indexOf.call(listaC.children, fila);
      fila.remove();
      // Los surtidores de ese combustible quedan sin elegir (no se les asigna otro sin avisar);
      // los que apuntaban a combustibles posteriores se corren un lugar.
      listaS.querySelectorAll('.s-combustible').forEach(function (s) {
        if (s.value === '') { return; }
        var v = Number(s.value);
        if (v === i) { s.value = ''; } else if (v > i) { s.value = String(v - 1); }
      });
      refrescarOpciones();
    });
    fila.appendChild(boton);
    fila.querySelector('.c-nombre').addEventListener('input', refrescarOpciones);
    listaC.appendChild(fila);
    refrescarOpciones();
  }

  function agregarSurtidor(s) {
    var comb = el('select', { class: 'campo s-combustible' });
    var fila = el('div', { class: 'grid items-end gap-3 rounded-lg border border-zinc-200 p-3 grid-cols-2 sm:grid-cols-[5.5rem_6rem_1fr_9rem_auto]' }, [
      campo('Máquina', el('input', { class: 'campo s-maquina numeros', type: 'number', min: '1', max: '50', inputmode: 'numeric', value: String(s.maquina || 1) })),
      campo('Código', el('input', { class: 'campo s-codigo', maxlength: '10', value: s.codigo || '', autocapitalize: 'characters', spellcheck: 'false' })),
      campo('Combustible', comb, 'col-span-2 sm:col-span-1'),
      campo('Lectura de hoy', el('input', { class: 'campo s-lectura numeros', type: 'number', step: '0.001', min: '0', inputmode: 'decimal', value: s.lectura || '' }))
    ]);
    var boton = quitar();
    boton.setAttribute('aria-label', 'Quitar este surtidor');
    boton.addEventListener('click', function () { fila.remove(); });
    fila.appendChild(boton);
    listaS.appendChild(fila);
    refrescarOpciones();
    comb.value = String(s.combustible || 0);
  }

  // Una máquina nueva trae un surtidor por combustible, con códigos sugeridos (D1, R1, P1...).
  function agregarMaquina() {
    var maquinas = Array.prototype.map.call(listaS.querySelectorAll('.s-maquina'), function (i) { return Number(i.value) || 0; });
    var numero = (maquinas.length ? Math.max.apply(null, maquinas) : 0) + 1;
    combustibles().forEach(function (c, i) {
      var letra = (c.tipo === 'OTRO' ? (c.nombre || 'X') : c.tipo).charAt(0);
      agregarSurtidor({ maquina: numero, codigo: letra + numero, combustible: i });
    });
  }

  SUGERIDOS.forEach(agregarCombustible);
  agregarMaquina();
  document.getElementById('agregar_combustible').addEventListener('click', function () { agregarCombustible({}); listaC.lastChild.querySelector('.c-nombre').focus(); });
  document.getElementById('agregar_maquina').addEventListener('click', agregarMaquina);
  document.getElementById('agregar_surtidor').addEventListener('click', function () {
    var maquinas = Array.prototype.map.call(listaS.querySelectorAll('.s-maquina'), function (i) { return Number(i.value) || 1; });
    agregarSurtidor({ maquina: maquinas.length ? maquinas[maquinas.length - 1] : 1 });
    listaS.lastChild.querySelector('.s-codigo').focus();
  });

  var valor = function (id) { return document.getElementById(id).value; };
  document.getElementById('form_instalar').addEventListener('submit', function (ev) {
    ev.preventDefault();
    var mensaje = document.getElementById('mensaje');
    var boton = document.getElementById('instalar');
    mensaje.classList.add('hidden');
    boton.disabled = true;
    boton.textContent = 'Instalando…';
    var datos = {
      empresa: { nombre: valor('em_nombre'), ruc: valor('em_ruc'), telefono: valor('em_telefono'), direccion: valor('em_direccion') },
      administrador: { nombres: valor('ad_nombres'), apellidos: valor('ad_apellidos'), usuario: valor('ad_usuario'), clave: valor('ad_clave'), repetir: valor('ad_repetir') },
      combustibles: combustibles(),
      surtidores: Array.prototype.map.call(listaS.children, function (f) {
        return { maquina: f.querySelector('.s-maquina').value, codigo: f.querySelector('.s-codigo').value, combustible: f.querySelector('.s-combustible').value, lectura: f.querySelector('.s-lectura').value };
      })
    };
    fetch('instalar.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ token: TOKEN, datos: datos }) })
      .then(function (r) { return r.json(); })
      .then(function (r) {
        if (r.success) {
          document.getElementById('form_instalar').classList.add('hidden');
          document.getElementById('listo').classList.remove('hidden');
          document.getElementById('listo').querySelector('a').focus();
          return;
        }
        if (r.instalado) { window.location.href = 'login.php'; return; }
        mensaje.textContent = r.message || 'No se pudo instalar.';
        mensaje.classList.remove('hidden');
        mensaje.scrollIntoView({ block: 'center', behavior: 'smooth' });
      })
      .catch(function () {
        mensaje.textContent = 'No se pudo conectar con el servidor.';
        mensaje.classList.remove('hidden');
      })
      .finally(function () { boton.disabled = false; boton.textContent = 'Instalar'; });
  });
})();
</script>
</body>
</html>
