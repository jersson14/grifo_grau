// Página pública: envío de la solicitud de flota sin recargar, con errores junto a cada campo.
(function () {
  'use strict';
  // Solicitud de flota: se envía sin recargar y los errores aparecen junto a cada campo.
  var form = document.getElementById('form_flota');
  if (!form || !window.fetch || !window.FormData) { return; }

  function limpiar() {
    form.querySelectorAll('[data-error]').forEach(function (p) { p.remove(); });
    form.querySelectorAll('[aria-invalid]').forEach(function (c) { c.removeAttribute('aria-invalid'); c.removeAttribute('aria-describedby'); });
    var general = form.querySelector('[data-error-general]');
    if (general) { general.remove(); }
  }

  function marcar(nombre, mensaje) {
    var campo = form.elements[nombre];
    if (!campo) { return null; }
    var id = 'error_' + nombre;
    var aviso = document.getElementById(id);
    if (aviso) { aviso.remove(); }
    aviso = document.createElement('p');
    aviso.id = id;
    aviso.setAttribute('data-error', '');
    aviso.className = 'mt-1.5 text-sm font-medium text-red-600';
    aviso.textContent = mensaje;
    campo.setAttribute('aria-invalid', 'true');
    campo.setAttribute('aria-describedby', id);
    campo.insertAdjacentElement('afterend', aviso);
    return campo;
  }

  function avisoGeneral(mensaje) {
    var p = document.createElement('p');
    p.setAttribute('data-error-general', '');
    p.setAttribute('role', 'alert');
    p.className = 'sm:col-span-2 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700';
    p.textContent = mensaje;
    form.insertBefore(p, form.querySelector('.sm\\:col-span-2'));
  }

  form.addEventListener('input', function (evento) {
    var campo = evento.target;
    if (campo.getAttribute('aria-invalid') === 'true') {
      campo.removeAttribute('aria-invalid');
      var aviso = document.getElementById('error_' + campo.name);
      if (aviso) { aviso.remove(); }
    }
  });

  form.addEventListener('submit', function (evento) {
    evento.preventDefault();
    limpiar();

    var primero = null;
    ['empresa', 'contacto', 'telefono'].forEach(function (nombre) {
      if (!form.elements[nombre].value.trim()) {
        var c = marcar(nombre, 'Este dato es obligatorio.');
        primero = primero || c;
      }
    });
    if (primero) { primero.focus(); return; }

    var boton = form.querySelector('button[type=submit]');
    var texto = boton.querySelector('span');
    boton.disabled = true;
    texto.textContent = 'Enviando…';

    fetch(form.action, { method: 'POST', body: new FormData(form), headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
      .then(function (r) { return r.json(); })
      .then(function (r) {
        if (r.success) {
          form.classList.add('hidden');
          var listo = document.getElementById('flota_listo');
          listo.classList.remove('hidden');
          listo.focus();
          return;
        }
        var campos = Object.keys(r.errores || {});
        if (campos.length) {
          var foco = null;
          campos.forEach(function (n) { var c = marcar(n, r.errores[n]); foco = foco || c; });
          if (foco) { foco.focus(); }
        } else {
          avisoGeneral(r.message || 'No se pudo enviar la solicitud. Inténtalo de nuevo.');
        }
      })
      .catch(function () { avisoGeneral('No hay conexión. Revisa tu internet e inténtalo de nuevo.'); })
      .then(function () { boton.disabled = false; texto.textContent = 'Enviar solicitud'; });
  });
}());

// Portada: bloques que aparecen, cifras que cuentan, globo del gráfico y calculadora de carga.
(function () {
  'use strict';
  var quieto = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  // Cifras: cuenta desde cero la parte numérica («24 h», «+12», «3»).
  function contar(el) {
    var m = el.textContent.trim().match(/^(\D*)(\d+)(.*)$/);
    if (!m || quieto) { return; }
    var meta = parseInt(m[2], 10), inicio = null, dur = 1200;
    function paso(t) {
      inicio = inicio || t;
      var k = Math.min((t - inicio) / dur, 1);
      el.textContent = m[1] + Math.round(meta * (1 - Math.pow(1 - k, 3))) + m[3];
      if (k < 1) { requestAnimationFrame(paso); }
    }
    requestAnimationFrame(paso);
  }

  var bloques = document.querySelectorAll('[data-aparecer]');
  var cifras = document.querySelectorAll('[data-cifra]');
  if ('IntersectionObserver' in window) {
    var obs = new IntersectionObserver(function (entradas) {
      entradas.forEach(function (en) {
        if (!en.isIntersecting) { return; }
        en.target.classList.add('visible');
        if (en.target.hasAttribute('data-cifra')) { contar(en.target); }
        obs.unobserve(en.target);
      });
    }, { rootMargin: '0px 0px -8% 0px' });
    bloques.forEach(function (b) { obs.observe(b); });
    cifras.forEach(function (c) { obs.observe(c); });
  } else {
    bloques.forEach(function (b) { b.classList.add('visible'); });
  }

  // Menú del celular: se cierra al elegir una sección.
  document.querySelectorAll('.menu-movil a').forEach(function (a) {
    a.addEventListener('click', function () { a.closest('details').removeAttribute('open'); });
  });

  var dinero = function (v) { return v.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ','); };
  var meses = ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'set', 'oct', 'nov', 'dic'];

  // Gráfico: al pasar el dedo o el mouse muestra el precio de cada combustible ese día.
  var caja = document.getElementById('grafico_precios');
  if (caja) {
    var g = JSON.parse(caja.getAttribute('data-grafico'));
    var svg = caja.querySelector('svg');
    var guia = svg.querySelector('.guia');
    var marcas = svg.querySelector('.marcas-guia');
    var globo = caja.querySelector('.grafico-globo');
    var ns = 'http://www.w3.org/2000/svg';
    var precioEn = function (puntos, t) {
      var v = puntos[0][1];
      for (var i = 0; i < puntos.length && puntos[i][0] <= t; i++) { v = puntos[i][1]; }
      return v;
    };
    var ocultar = function () {
      guia.classList.add('hidden');
      globo.classList.add('hidden');
      while (marcas.firstChild) { marcas.removeChild(marcas.firstChild); }
    };
    var mover = function (ev) {
      var r = svg.getBoundingClientRect();
      var vx = (ev.clientX - r.left) / r.width * g.W;
      if (vx < g.L || vx > g.L + g.ancho) { ocultar(); return; }
      var t = g.t0 + (vx - g.L) / g.ancho * (g.t1 - g.t0);
      var f = new Date(t * 1000);
      guia.setAttribute('x1', vx);
      guia.setAttribute('x2', vx);
      guia.classList.remove('hidden');
      while (marcas.firstChild) { marcas.removeChild(marcas.firstChild); }
      globo.textContent = '';
      var titulo = document.createElement('p');
      titulo.className = 'mb-1.5 font-semibold text-zinc-300';
      titulo.textContent = f.getDate() + ' ' + meses[f.getMonth()] + ' ' + f.getFullYear();
      globo.appendChild(titulo);
      g.series.forEach(function (s) {
        var v = precioEn(s.puntos, t);
        var c = document.createElementNS(ns, 'circle');
        c.setAttribute('cx', vx);
        c.setAttribute('cy', g.T + (g.yMax - v) / (g.yMax - g.yMin) * g.alto);
        c.setAttribute('r', 4.5);
        c.setAttribute('fill', s.color);
        c.setAttribute('stroke', '#fff');
        c.setAttribute('stroke-width', 2);
        marcas.appendChild(c);
        var fila = document.createElement('p');
        fila.className = 'flex items-center justify-between gap-4 py-0.5';
        var nombre = document.createElement('span');
        nombre.className = 'flex items-center gap-1.5';
        var punto = document.createElement('span');
        punto.className = 'h-2 w-2 rounded-full';
        punto.style.background = s.color;
        nombre.appendChild(punto);
        nombre.appendChild(document.createTextNode(s.nombre));
        var precio = document.createElement('span');
        precio.className = 'numeros font-semibold';
        precio.textContent = 'S/ ' + dinero(v);
        fila.appendChild(nombre);
        fila.appendChild(precio);
        globo.appendChild(fila);
      });
      globo.classList.remove('hidden');
      var px = vx / g.W * r.width;
      var izquierda = px + 16 + globo.offsetWidth > r.width ? px - 16 - globo.offsetWidth : px + 16;
      globo.style.left = Math.max(0, izquierda) + 'px';
    };
    svg.addEventListener('pointermove', mover);
    svg.addEventListener('pointerdown', mover);
    svg.addEventListener('pointerleave', ocultar);
  }

  // Calculadora: galones por un monto o monto por galones, con el precio vigente.
  var calc = document.getElementById('calculadora');
  if (calc) {
    var monto = document.getElementById('calc_monto');
    var rapidos = document.getElementById('calc_rapidos');
    var RAPIDOS = { soles: [20, 50, 100, 200], galones: [1, 5, 10, 15] };
    var modoActual = null;
    var elegido = function (nombre) { return calc.querySelector('input[name=' + nombre + ']:checked'); };
    var calcular = function () {
      var comb = elegido('calc_combustible');
      var modo = elegido('calc_modo').value;
      var precio = parseFloat(comb.value);
      var n = Math.max(parseFloat(monto.value) || 0, 0);
      if (modo !== modoActual) {
        modoActual = modo;
        document.getElementById('calc_etiqueta').textContent = modo === 'soles' ? 'Monto en soles' : 'Galones';
        document.getElementById('calc_prefijo').textContent = modo === 'soles' ? 'S/' : 'gal';
        rapidos.textContent = '';
        RAPIDOS[modo].forEach(function (v) {
          var b = document.createElement('button');
          b.type = 'button';
          b.className = 'rapido numeros';
          b.textContent = modo === 'soles' ? 'S/ ' + v : v + ' gal';
          b.addEventListener('click', function () { monto.value = v; calcular(); });
          rapidos.appendChild(b);
        });
      }
      var res = modo === 'soles' ? (precio > 0 ? n / precio : 0) : n * precio;
      document.getElementById('calc_resultado_etiqueta').textContent = modo === 'soles' ? 'Llevas' : 'Pagas';
      document.getElementById('calc_resultado').textContent = modo === 'soles' ? dinero(res) : 'S/ ' + dinero(res);
      document.getElementById('calc_unidad').textContent = modo === 'soles' ? 'gal' : '';
      document.getElementById('calc_detalle').textContent = comb.getAttribute('data-nombre') + ' a S/ ' + dinero(precio) + ' el galón';
    };
    calc.addEventListener('change', function (ev) {
      if (ev.target.name === 'calc_modo') { monto.value = ev.target.value === 'soles' ? 50 : 10; }
      calcular();
    });
    monto.addEventListener('input', calcular);
    calcular();
  }
}());
