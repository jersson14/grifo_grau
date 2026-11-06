(function () {
  var panel = $('#chat_panel');
  var mensajes = $('#chat_mensajes');
  var entrada = $('#chat_entrada');
  var enviando = false;

  $('#chat_abrir, #chat_cerrar').on('click', function () {
    panel.toggleClass('d-none');
    if (!panel.hasClass('d-none')) { entrada.trigger('focus'); }
  });

  function agregar(texto, clase) {
    // .text() evita que un dato con HTML, como el nombre de un cliente, se ejecute en la página.
    var burbuja = $('<div class="chat_burbuja"></div>').addClass(clase).text(texto);
    mensajes.append(burbuja);
    mensajes.scrollTop(mensajes[0].scrollHeight);
    return burbuja;
  }

  $('#chat_form').on('submit', function (e) {
    e.preventDefault();
    var pregunta = $.trim(entrada.val());
    if (!pregunta || enviando) { return; }

    enviando = true;
    entrada.val('');
    agregar(pregunta, 'chat_usuario');
    var espera = agregar('Consultando…', 'chat_bot');

    $.ajax({ url: '../controller/ia/controlador_preguntar.php', type: 'POST', data: { pregunta: pregunta }, dataType: 'json', timeout: 120000 })
      .done(function (r) { espera.text(r.respuesta); })
      .fail(function () { espera.text('No se pudo obtener respuesta. Intente de nuevo.'); })
      .always(function () { enviando = false; mensajes.scrollTop(mensajes[0].scrollHeight); });
  });

  $('#chat_limpiar').on('click', function () {
    $.post('../controller/ia/controlador_limpiar_conversacion.php', function () { mensajes.empty(); });
  });
})();
