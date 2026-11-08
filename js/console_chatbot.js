/**
 * Asistente de datos: burbuja en todas las pantallas.
 * Todo lo que viene del servidor se pinta como texto (nodos de texto o .text()), nunca como HTML:
 * una respuesta puede traer nombres de clientes u otro texto escrito por personas.
 */
(function () {
  var RUTA = '../controller/ia/controlador_';
  var panel = $('#chat_panel');
  var boton = $('#chat_abrir');
  var mensajes = $('#chat_mensajes');
  var entrada = $('#chat_entrada');
  var bienvenida = $('#chat_bienvenida');
  var enviando = false;
  var cargado = false;

  // ---------- Respuesta con formato sencillo: párrafos, listas, negritas y tablas ----------

  function en_linea(texto) {
    // Solo **negrita**; el resto queda como texto.
    var caja = $('<span></span>');
    texto.split(/(\*\*[^*]+\*\*)/).forEach(function (parte) {
      if (/^\*\*[^*]+\*\*$/.test(parte)) { caja.append($('<strong></strong>').text(parte.slice(2, -2))); }
      else if (parte) { caja.append(document.createTextNode(parte)); }
    });
    return caja.contents();
  }

  function numero(celda) {
    var limpio = String(celda).replace(/S\/|USD|gal(ones)?|%|\s/gi, '').replace(/,/g, '');
    return /^-?\d+(\.\d+)?$/.test(limpio) ? Number(limpio) : null;
  }

  function celdas(linea) {
    return linea.replace(/^\s*\|/, '').replace(/\|\s*$/, '').split('|').map(function (c) { return $.trim(c); });
  }

  function tabla(filas) {
    var cabecera = celdas(filas[0]);
    var cuerpo = filas.slice(2).map(celdas);
    var t = $('<table class="g-chat-tabla"></table>');
    var tr = $('<tr></tr>');
    cabecera.forEach(function (c) { tr.append($('<th scope="col"></th>').append(en_linea(c))); });
    t.append($('<thead></thead>').append(tr));
    var tb = $('<tbody></tbody>');
    cuerpo.forEach(function (fila) {
      var f = $('<tr></tr>');
      fila.forEach(function (c) { f.append($('<td></td>').toggleClass('g-num', numero(c) !== null).append(en_linea(c))); });
      tb.append(f);
    });
    t.append(tb);
    var bloque = $('<div class="table-responsive"></div>').append(t);

    // Una tabla de etiqueta y cifra, con pocas filas, también se muestra como barras.
    var valores = cuerpo.map(function (f) { return f.length === 2 ? numero(f[1]) : null; });
    if (cuerpo.length >= 2 && cuerpo.length <= 12 && valores.every(function (v) { return v !== null && v >= 0; })) {
      var maximo = Math.max.apply(null, valores) || 1;
      var barras = $('<div class="g-chat-barras" aria-hidden="true"></div>');
      cuerpo.forEach(function (f, i) {
        barras.append($('<div class="g-chat-barra"></div>').append(
          $('<span></span>').text(f[0]).attr('title', f[0]),
          $('<i></i>').css('width', (valores[i] / maximo * 100).toFixed(1) + '%'),
          $('<b></b>').text(f[1])
        ));
      });
      return $('<div></div>').append(barras, bloque);
    }
    return bloque;
  }

  function formatear(texto) {
    var caja = $('<div></div>');
    var lineas = String(texto || '').replace(/\r/g, '').split('\n');
    var i = 0;
    while (i < lineas.length) {
      var l = lineas[i];
      if (/^\s*\|.*\|\s*$/.test(l) && i + 1 < lineas.length && /^\s*\|?[\s:|-]+\|?\s*$/.test(lineas[i + 1]) && lineas[i + 1].indexOf('-') !== -1) {
        var filas = [];
        while (i < lineas.length && /^\s*\|.*\|\s*$/.test(lineas[i])) { filas.push(lineas[i]); i++; }
        caja.append(tabla(filas));
        continue;
      }
      if (/^\s*([-*•]|\d+\.)\s+/.test(l)) {
        var lista = $('<ul></ul>');
        while (i < lineas.length && /^\s*([-*•]|\d+\.)\s+/.test(lineas[i])) {
          lista.append($('<li></li>').append(en_linea(lineas[i].replace(/^\s*([-*•]|\d+\.)\s+/, ''))));
          i++;
        }
        caja.append(lista);
        continue;
      }
      if ($.trim(l) === '') { i++; continue; }
      var parrafo = [];
      while (i < lineas.length && $.trim(lineas[i]) !== '' && !/^\s*\|/.test(lineas[i]) && !/^\s*([-*•]|\d+\.)\s+/.test(lineas[i])) {
        parrafo.push(lineas[i].replace(/^#+\s*/, '')); i++;
      }
      var p = $('<p></p>');
      parrafo.forEach(function (linea, n) { if (n) { p.append('<br>'); } p.append(en_linea(linea)); });
      caja.append(p);
    }
    return caja.contents();
  }

  // ---------- Mensajes ----------

  function bajar() { mensajes.scrollTop(mensajes[0].scrollHeight); }

  function hora(fecha) {
    return fecha ? $('<time class="g-chat-hora"></time>').text(fecha.slice(11, 16)) : null;
  }

  function mensaje_usuario(texto, fecha) {
    bienvenida.addClass('d-none');
    mensajes.append($('<div class="g-chat-msg g-chat-yo"></div>').text(texto).append(hora(fecha)));
  }

  function mensaje_bot(texto, error, fecha) {
    var m = $('<div class="g-chat-msg g-chat-bot"></div>').toggleClass('g-chat-error', !!error).append(formatear(texto)).append(hora(fecha));
    mensajes.append(m);
    return m;
  }

  function cargar_historial() {
    cargado = true;
    $.post(RUTA + 'historial.php', {}, function (r) {
      (r.data || []).forEach(function (c) {
        mensaje_usuario(c.pregunta, c.creado_en);
        mensaje_bot(c.respuesta, c.estado !== 'OK', c.creado_en);
      });
      bajar();
    }, 'json');
  }

  function abrir(abierto) {
    panel.toggleClass('d-none', !abierto);
    boton.attr('aria-expanded', abierto ? 'true' : 'false');
    if (abierto) {
      if (!cargado) { cargar_historial(); }
      entrada.trigger('focus');
      bajar();
    } else {
      boton.trigger('focus');
    }
  }

  boton.on('click', function () { abrir(true); });
  $('#chat_cerrar').on('click', function () { abrir(false); });
  panel.on('keydown', function (e) { if (e.key === 'Escape') { abrir(false); } });

  function preguntar(pregunta) {
    if (!pregunta || enviando) { return; }
    enviando = true;
    entrada.val('').css('height', '');
    mensaje_usuario(pregunta);
    var espera = $('<div class="g-chat-msg g-chat-bot"></div>').append($('<span class="g-chat-avance"></span>').text('Pensando…'));
    mensajes.append(espera);
    bajar();

    var leido = 0;
    var final = null;
    function procesar(texto) {
      // El servidor envía una línea JSON por avance y la última con la respuesta.
      var lineas = texto.slice(leido).split('\n');
      var resto = lineas.pop();
      leido = texto.length - resto.length;
      lineas.forEach(function (linea) {
        if (!$.trim(linea)) { return; }
        try {
          var dato = JSON.parse(linea);
          if (dato.avance) { espera.find('.g-chat-avance').text(dato.avance + '…'); }
          if ('respuesta' in dato) { final = dato; }
        } catch (e) { /* línea incompleta */ }
      });
    }

    $.ajax({
      url: RUTA + 'preguntar.php', type: 'POST', data: { pregunta: pregunta, flujo: '1' }, dataType: 'text', timeout: 120000,
      xhrFields: { onprogress: function (e) { procesar(e.target.responseText || ''); } }
    }).done(function (texto) {
      procesar(texto + '\n');
      espera.remove();
      if (final) { mensaje_bot(final.respuesta, final.ok === false); }
      else { mensaje_bot('No se pudo leer la respuesta. Intente de nuevo.', true); }
    }).fail(function () {
      espera.remove();
      mensaje_bot('No se pudo obtener respuesta. Intente de nuevo.', true);
    }).always(function () {
      enviando = false;
      bajar();
      entrada.trigger('focus');
    });
  }

  $('#chat_form').on('submit', function (e) { e.preventDefault(); preguntar($.trim(entrada.val())); });
  entrada.on('keydown', function (e) {
    // Enter envía; Mayús+Enter salta de línea.
    if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); $('#chat_form').trigger('submit'); }
  }).on('input', function () {
    this.style.height = '';
    this.style.height = Math.min(this.scrollHeight, 128) + 'px';
  });
  mensajes.on('click', '.g-chat-sugerencia', function () { preguntar($(this).text()); });

  $('#chat_limpiar').on('click', function () {
    if (enviando) { return; }
    $.post(RUTA + 'limpiar_conversacion.php', {}, function () {
      mensajes.children().not(bienvenida).remove();
      bienvenida.removeClass('d-none');
      entrada.trigger('focus');
    });
  });
})();
