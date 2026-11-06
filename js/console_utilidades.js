// Una fecha sin hora ('2026-11-06') se interpretaría en UTC y en Lima se mostraría el día anterior.
function Fecha_Local(valor) {
  return new Date(/^\d{4}-\d{2}-\d{2}$/.test(valor) ? valor + 'T00:00:00' : valor);
}

// Utilidades comunes de las pantallas nuevas. Los valores se insertan como texto, nunca como HTML.
var G = {
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
