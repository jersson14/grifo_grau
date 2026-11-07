// Página pública: destello de la cinta, contador de precios y envío de la solicitud de flota.
(function () {
  'use strict';
  var quieto = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  // La cinta reflectiva destella una vez, como bajo los faros.
  requestAnimationFrame(function () { document.body.classList.add('encendida'); });

  // Contador de surtidor: el precio ya está escrito; si se permite movimiento, cada cifra rueda hasta su valor.
  if (!quieto) {
    document.querySelectorAll('.contador[data-valor]').forEach(function (contador, n) {
      var texto = contador.getAttribute('data-valor');
      contador.textContent = '';
      var primeras = [];
      texto.split('').forEach(function (caracter) {
        if (!/\d/.test(caracter)) {
          var fijo = document.createElement('span');
          fijo.textContent = caracter;
          contador.appendChild(fijo);
          return;
        }
        var tira = document.createElement('span');
        tira.className = 'tira';
        for (var d = 0; d <= 9; d++) {
          var cifra = document.createElement('span');
          cifra.textContent = d;
          tira.appendChild(cifra);
        }
        contador.appendChild(tira);
        primeras.push([tira.firstChild, Number(caracter)]);
      });
      setTimeout(function () {
        contador.classList.add('rodando');
        primeras.forEach(function (par) { par[0].style.marginTop = (-par[1]) + 'em'; });
      }, 250 + n * 120);
    });
  }

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
    aviso.className = 'mt-1.5 text-base font-semibold text-reflectivo-tinta';
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
    p.className = 'sm:col-span-2 rounded-[4px] border-2 border-reflectivo-tinta px-4 py-3 text-lg font-semibold text-reflectivo-tinta';
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
