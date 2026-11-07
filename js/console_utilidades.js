/**
 * Convierte un dato en texto seguro para insertarlo en HTML. Muchos datos antiguos se guardaron ya
 * escapados (&amp;), así que primero se decodifican para no mostrarlos dos veces escapados. Un
 * textarea no crea elementos al decodificar, de modo que el dato nunca se interpreta como HTML.
 */
var Texto_Seguro = (function () {
  var decodificador = document.createElement('textarea');
  var mapa = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' };
  return function (valor) {
    if (valor === null || valor === undefined) { return ''; }
    decodificador.innerHTML = String(valor);
    return decodificador.value.replace(/[&<>"']/g, function (c) { return mapa[c]; });
  };
})();

// Mejoras comunes para todas las pantallas, también las antiguas, sin tocar cada una:
// etiquetas accesibles y tarjetas en celular para tablas de captura, errores junto al campo
// y avisos de DataTables integrados en lugar de alert().
(function () {
  function texto(el) { return (el ? el.textContent : '').replace(/\s+/g, ' ').trim(); }

  function mejorar_tablas(raiz) {
    // La validación nativa bloquearía el envío con un globo antes de poder mostrar el error junto al campo.
    $(raiz).find('form:not([data-sin-validar])').each(function () { this.noValidate = true; });

    // Etiquetas visibles sin vínculo: en un grupo con una etiqueta y un solo campo, se asocian.
    $(raiz).find('.form-group').each(function () {
      var etiquetas = $(this).children('label:not([for])');
      var campos = $(this).find('input:not([type=hidden]):not([type=checkbox]):not([type=radio]), select, textarea');
      if (etiquetas.length === 1 && campos.length === 1 && campos[0].id) {
        etiquetas[0].setAttribute('for', campos[0].id);
      }
    });

    $(raiz).find('table').each(function () {
      var tabla = this;
      var titulos = $(tabla).find('thead tr').last().children('th, td').map(function () { return texto(this); }).get();
      var tieneCampos = false;
      $(tabla).find('tbody tr').each(function () {
        var celdas = $(this).children('td');
        var identificador = texto(celdas.get(0));
        celdas.each(function (i) {
          if (titulos[i] && !this.hasAttribute('data-label')) { this.setAttribute('data-label', titulos[i]); }
          $(this).find('input:not([type=hidden]), select, textarea').each(function () {
            tieneCampos = true;
            var etiquetado = this.getAttribute('aria-label') || (this.id && document.querySelector('label[for="' + this.id + '"]'));
            if (!etiquetado && titulos[i]) {
              this.setAttribute('aria-label', titulos[i] + (identificador && i > 0 ? ' ' + identificador : ''));
            }
          });
        });
      });
      if (tieneCampos) { tabla.classList.add('tabla-apilable'); }
    });
  }

  var pendiente = false;
  function programar() {
    if (pendiente) { return; }
    pendiente = true;
    (window.requestAnimationFrame || setTimeout)(function () { pendiente = false; mejorar_tablas(document.getElementById('contenido_principal') || document.body); });
  }

  $(function () {
    var raiz = document.getElementById('contenido_principal');
    if (raiz && window.MutationObserver) {
      new MutationObserver(programar).observe(raiz, { childList: true, subtree: true });
    }
    programar();
  });

  // Un formulario con campos inválidos no se envía: se marcan los campos y se explica el error debajo de cada uno.
  document.addEventListener('submit', function (evento) {
    var form = evento.target;
    if (!form || typeof form.checkValidity !== 'function' || form.hasAttribute('data-sin-validar')) { return; }
    $(form).find('.campo-error').remove();
    $(form).find('.is-invalid').removeClass('is-invalid').removeAttr('aria-invalid');
    if (form.checkValidity()) { return; }
    evento.preventDefault();
    evento.stopImmediatePropagation();
    var primero = null;
    $(form).find('input, select, textarea').each(function () {
      if (this.willValidate && !this.checkValidity()) {
        var id = (this.id || 'campo') + '_error';
        $(this).addClass('is-invalid').attr({ 'aria-invalid': 'true', 'aria-describedby': id });
        $('<span class="campo-error" role="alert"></span>').attr('id', id).text(this.validationMessage).insertAfter(this);
        primero = primero || this;
      }
    });
    if (primero) { primero.focus(); }
  }, true);
  document.addEventListener('input', function (evento) {
    var campo = evento.target;
    if (campo.classList && campo.classList.contains('is-invalid') && campo.checkValidity()) {
      $(campo).removeClass('is-invalid').removeAttr('aria-invalid');
      $('#' + (campo.id || 'campo') + '_error').remove();
    }
  });

  // DataTables avisa de sus errores con alert(): se registran y se muestra un aviso discreto.
  if ($.fn.dataTable) {
    // Toda columna sin presentación propia muestra su dato como texto, no como HTML.
    $.extend(true, $.fn.dataTable.defaults, {
      columnDefs: [{ targets: '_all', render: function (dato, tipo) { return (tipo === 'display' && typeof dato === 'string') ? Texto_Seguro(dato) : dato; } }]
    });
    $.fn.dataTable.ext.errMode = 'none';
    $(document).on('error.dt', function (e, ajustes, codigo, mensaje) {
      console.error('DataTables:', mensaje);
      if (window.Swal) {
        Swal.fire({ toast: true, position: 'top-end', icon: 'warning', title: 'Algunos datos de esta tabla no se pudieron mostrar.', showConfirmButton: false, timer: 4000 });
      }
    });
  }
})();

// Fecha AAAA-MM-DD en la hora local. toISOString() usa UTC: desde las 19:00 en Lima daría el día siguiente.
function Fecha_ISO_Local(fecha) {
  return new Date(fecha.getTime() - fecha.getTimezoneOffset() * 60000).toISOString().slice(0, 10);
}

// Una fecha sin hora ('2026-11-06') se interpretaría en UTC y en Lima se mostraría el día anterior.
function Fecha_Local(valor) {
  return new Date(/^\d{4}-\d{2}-\d{2}$/.test(valor) ? valor + 'T00:00:00' : valor);
}

// Utilidades comunes de las pantallas nuevas. Los valores se insertan como texto, nunca como HTML.
var G = {
  // CAPACIDADES lo define view/index.php: '*' para el administrador.
  puede: function (capacidad) {
    return window.CAPACIDADES === '*' || (window.CAPACIDADES || []).indexOf(capacidad) !== -1;
  },

  post: function (url, datos, alTerminar) {
    return $.post(url, datos, function (r) {
      if (r && r.success === false) {
        Swal.fire('Revise los datos', r.message || 'No se pudo completar la operación.', 'warning');
      } else if (alTerminar) {
        alTerminar(r);
      }
    }, 'json');
  },

  // columnas: lista de funciones fila -> texto o elemento jQuery
  tabla: function (selector, filas, columnas, vacio) {
    var cuerpo = $(selector).empty();
    if (!filas || !filas.length) {
      cuerpo.append($('<tr></tr>').append($('<td class="text-center text-muted"></td>').attr('colspan', columnas.length).text(vacio || 'Sin registros')));
      return;
    }
    filas.forEach(function (fila) {
      var tr = $('<tr></tr>');
      columnas.forEach(function (col) {
        var valor = col(fila);
        var td = $('<td></td>');
        if (valor instanceof jQuery) { td.append(valor); } else { td.text(valor === null || valor === undefined ? '' : valor); }
        tr.append(td);
      });
      cuerpo.append(tr);
    });
  },

  boton: function (texto, clase, alPulsar) {
    return $('<button type="button" class="btn btn-sm mr-1"></button>').addClass(clase).text(texto).on('click', alPulsar);
  },

  grupo: function () {
    var caja = $('<div class="text-nowrap"></div>');
    Array.prototype.slice.call(arguments).forEach(function (el) { if (el) { caja.append(el); } });
    return caja;
  },

  etiqueta: function (texto, clase) {
    return $('<span class="badge"></span>').addClass(clase).text(texto);
  },

  num: function (v, decimales) {
    return Number(v || 0).toLocaleString('es-PE', { minimumFractionDigits: decimales, maximumFractionDigits: decimales });
  },
  galones: function (v) { return G.num(v, 3); },
  soles: function (v) { return 'S/ ' + G.num(v, 2); },

  hoy: function () {
    var d = new Date();
    return new Date(d.getTime() - d.getTimezoneOffset() * 60000).toISOString().slice(0, 10);
  },
  inicioMes: function () { return G.hoy().slice(0, 8) + '01'; },

  ok: function (mensaje) {
    Swal.fire({ icon: 'success', title: mensaje, timer: 1300, showConfirmButton: false });
  },

  opciones: function (selector, filas, valor, texto, vacio) {
    var select = $(selector).empty();
    if (vacio) { select.append($('<option value=""></option>').text(vacio)); }
    filas.forEach(function (f) { select.append($('<option></option>').attr('value', f[valor]).text(texto(f))); });
  },

  // Pide un texto obligatorio y continúa solo si se confirma.
  pedirMotivo: function (titulo, alConfirmar) {
    Swal.fire({
      title: titulo, input: 'text', inputPlaceholder: 'Motivo', showCancelButton: true, confirmButtonText: 'Confirmar', cancelButtonText: 'Cancelar',
      inputValidator: function (v) { return v && v.trim() ? null : 'Indique el motivo'; }
    }).then(function (r) { if (r.isConfirmed) { alConfirmar(r.value.trim()); } });
  }
};
