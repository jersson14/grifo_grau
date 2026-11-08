// Campana de avisos de la cabecera: los mismos avisos del tablero, filtrados por lo que cada usuario puede abrir.
(function () {
  var ICONOS = { danger: 'fas fa-exclamation-circle text-danger', warning: 'fas fa-exclamation-triangle text-warning', info: 'fas fa-info-circle text-primary' };

  function cargar() {
    if (!document.getElementById('g_avisos')) { return; }
    $.ajax({
      url: '../controller/avisos/controlador_listar.php', type: 'POST', dataType: 'json',
      // No cuenta como actividad: la sesión vence igual si nadie usa el sistema.
      headers: { 'X-Segundo-Plano': '1' }
    }).done(function (r) {
      var lista = (r && r.data) || [];
      var items = $('#g_avisos_items').empty();
      var total = lista.length;
      $('#g_avisos_cuenta').toggleClass('d-none', !total).text(total > 9 ? '9+' : String(total));
      $('#g_avisos > a').attr('aria-label', total ? 'Avisos: ' + total + (total === 1 ? ' pendiente' : ' pendientes') : 'Avisos: ninguno');
      $('#g_avisos > a i').attr('class', total ? 'fas fa-bell' : 'far fa-bell');
      if (!total) {
        items.append($('<span class="dropdown-item-text text-muted small"></span>').text('Todo en orden: no hay avisos.'));
        return;
      }
      lista.forEach(function (a) {
        items.append($('<a href="#" class="dropdown-item g-aviso"></a>')
          .append($('<i aria-hidden="true"></i>').addClass(ICONOS[a.nivel] || ICONOS.info), $('<span></span>').text(a.cantidad + ' ' + a.texto))
          .on('click', function (e) { e.preventDefault(); cargar_contenido('contenido_principal', a.vista); }));
      });
    });
  }

  $(function () {
    cargar();
    setInterval(cargar, 5 * 60 * 1000);
    // Al abrir la campana se actualiza, por si algo cambió desde la última consulta.
    $('#g_avisos').on('show.bs.dropdown', cargar);
  });
  window.Recargar_Avisos = cargar;
})();
