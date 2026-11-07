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
