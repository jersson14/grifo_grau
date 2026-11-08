var RUTA_PUB = '../controller/publico/controlador_';
var PUB_ICONOS = [];
var PUB_FLOTAS = false;
var PUB_ESTADOS = { NUEVA: ['Nueva', 'badge-warning'], EN_PROCESO: ['En proceso', 'badge-info'], ATENDIDA: ['Atendida', 'badge-success'], DESCARTADA: ['Descartada', 'badge-secondary'] };

function iniciar_pagina_publica() {
  $('#promo_desde').val(G.hoy());

  $('#form_pub_datos').on('submit', function (e) {
    e.preventDefault();
    var form = this;
    $(form).find('.campo-error').remove();
    $(form).find('.is-invalid').removeClass('is-invalid').removeAttr('aria-invalid');
    var datos = {};
    $(form).find('input[name]').each(function () { datos[this.name] = this.type === 'checkbox' ? (this.checked ? '1' : '0') : this.value; });
    G.post(RUTA_PUB + 'guardar_datos.php', datos, function (r) {
      if (!r.guardado) {
        var primero = null;
        Object.keys(r.errores || {}).forEach(function (clave) {
          var campo = document.getElementById('pub_' + clave);
          if (!campo) { return; }
          $(campo).addClass('is-invalid').attr({ 'aria-invalid': 'true', 'aria-describedby': 'pub_' + clave + '_error' });
          $('<span class="campo-error" role="alert"></span>').attr('id', 'pub_' + clave + '_error').text(r.errores[clave]).insertAfter(campo);
          primero = primero || campo;
        });
        if (primero) { primero.focus(); }
        return;
      }
      G.ok('Página actualizada');
    });
  });

  $('#form_pub_servicio').on('submit', function (e) {
    e.preventDefault();
    G.post(RUTA_PUB + 'guardar_servicio.php', {
      id_servicio: $('#srv_id').val(), titulo: $('#srv_titulo').val(), descripcion: $('#srv_descripcion').val(),
      icono: $('#srv_icono').val(), activo: $('#srv_activo').val()
    }, function () { G.ok($('#srv_id').val() ? 'Servicio actualizado' : 'Servicio agregado'); limpiar_servicio(); cargar_pagina_publica(); });
  });
  $('#srv_cancelar').on('click', limpiar_servicio);

  $('#form_pub_promo').on('submit', function (e) {
    e.preventDefault();
    G.post(RUTA_PUB + 'guardar_promocion.php', {
      id_promocion: $('#promo_id').val(), titulo: $('#promo_titulo').val(), detalle: $('#promo_detalle').val(),
      desde: $('#promo_desde').val(), hasta: $('#promo_hasta').val(), activo: $('#promo_activo').val()
    }, function () { G.ok($('#promo_id').val() ? 'Promoción actualizada' : 'Promoción agregada'); limpiar_promo(); cargar_pagina_publica(); });
  });
  $('#promo_cancelar').on('click', limpiar_promo);

  $('#filtro_solicitudes').on('submit', function (e) { e.preventDefault(); listar_solicitudes(); });

  cargar_pagina_publica();
  listar_solicitudes();
}

function cargar_pagina_publica() {
  G.post(RUTA_PUB + 'datos.php', {}, function (r) {
    PUB_ICONOS = r.iconos;
    PUB_FLOTAS = !!r.flotas;
    var estado = $('#pub_estado').removeClass('d-none alert-success alert-warning');
    if (r.activa) {
      estado.addClass('alert-success').text('La página pública está activa: es lo primero que ve quien entra a la dirección del sistema.');
    } else {
      estado.addClass('alert-warning').text('La página pública está apagada: la dirección del sistema muestra el acceso. Prepárela aquí y actívela en Configuración → Módulos.');
    }
    $('#pub_nuevas').text(r.nuevas > 0 ? r.nuevas : '');

    var d = r.datos;
    Object.keys(d).forEach(function (clave) {
      var campo = document.getElementById('pub_' + clave);
      if (!campo) { return; }
      if (campo.type === 'checkbox') { campo.checked = d[clave] !== '0'; } else { campo.value = d[clave]; }
    });

    G.opciones('#srv_icono', r.iconos, 'codigo', function (i) { return i.nombre; });
    G.tabla('#cuerpo_pub_servicios', r.servicios, [
      function (s) { return $('<i></i>').addClass('fas fa-' + s.icono).attr('aria-hidden', 'true'); },
      function (s) { return s.titulo; },
      function (s) { return s.descripcion || ''; },
      function (s) { return G.etiqueta(Number(s.activo) ? 'Sí' : 'No', Number(s.activo) ? 'badge-success' : 'badge-secondary'); },
      function (s, i) {
        return G.grupo(
          G.boton('Subir', 'btn-outline-secondary', function () { mover_servicio(s.id_servicio, 'ARRIBA'); }).attr('aria-label', 'Subir ' + s.titulo),
          G.boton('Bajar', 'btn-outline-secondary', function () { mover_servicio(s.id_servicio, 'ABAJO'); }).attr('aria-label', 'Bajar ' + s.titulo),
          G.boton('Editar', 'btn-outline-primary', function () { editar_servicio(s); }),
          G.boton('Quitar', 'btn-outline-danger', function () { quitar_servicio(s); })
        );
      }
    ], 'Aún no hay servicios. Agregue el primero arriba.');

    G.tabla('#cuerpo_pub_promos', r.promociones, [
      function (p) { return $('<div></div>').append($('<b></b>').text(p.titulo), $('<div class="small text-muted"></div>').text(p.detalle || '')); },
      function (p) { return 'Desde ' + Fecha_Local(p.desde).toLocaleDateString('es-PE') + (p.hasta ? ' hasta ' + Fecha_Local(p.hasta).toLocaleDateString('es-PE') : ', sin fecha final'); },
      function (p) {
        var clases = { VIGENTE: 'badge-success', PROGRAMADA: 'badge-info', VENCIDA: 'badge-secondary', INACTIVA: 'badge-secondary' };
        return G.etiqueta(p.estado.charAt(0) + p.estado.slice(1).toLowerCase(), clases[p.estado]);
      },
      function (p) {
        return G.grupo(
          G.boton('Editar', 'btn-outline-primary', function () { editar_promo(p); }),
          G.boton('Eliminar', 'btn-outline-danger', function () { eliminar_promo(p); })
        );
      }
    ], 'Sin promociones. Las vigentes aparecen en la página en una franja amarilla.');
  });
}

function limpiar_servicio() {
  $('#srv_id').val(''); $('#srv_titulo').val(''); $('#srv_descripcion').val(''); $('#srv_activo').val('1');
  $('#srv_guardar').text('Agregar'); $('#srv_cancelar').addClass('d-none');
}
function editar_servicio(s) {
  $('#srv_id').val(s.id_servicio); $('#srv_titulo').val(s.titulo).focus(); $('#srv_descripcion').val(s.descripcion || '');
  $('#srv_icono').val(s.icono); $('#srv_activo').val(String(Number(s.activo)));
  $('#srv_guardar').text('Guardar cambios'); $('#srv_cancelar').removeClass('d-none');
}
function mover_servicio(id, direccion) {
  G.post(RUTA_PUB + 'mover_servicio.php', { id_servicio: id, direccion: direccion }, cargar_pagina_publica);
}
function quitar_servicio(s) {
  Swal.fire({ titleText: '¿Quitar «' + s.titulo + '»?', text: 'Deja de mostrarse en la página. Si solo quiere ocultarlo un tiempo, edítelo y marque Visible: No.', icon: 'warning', showCancelButton: true, confirmButtonText: 'Quitar', cancelButtonText: 'Cancelar' })
    .then(function (r) { if (r.isConfirmed) { G.post(RUTA_PUB + 'eliminar_servicio.php', { id_servicio: s.id_servicio }, function () { G.ok('Servicio quitado'); cargar_pagina_publica(); }); } });
}

function limpiar_promo() {
  $('#promo_id').val(''); $('#promo_titulo').val(''); $('#promo_detalle').val(''); $('#promo_desde').val(G.hoy()); $('#promo_hasta').val(''); $('#promo_activo').val('1');
  $('#promo_guardar').text('Agregar promoción'); $('#promo_cancelar').addClass('d-none');
}
function editar_promo(p) {
  $('#promo_id').val(p.id_promocion); $('#promo_titulo').val(p.titulo).focus(); $('#promo_detalle').val(p.detalle || '');
  $('#promo_desde').val(p.desde); $('#promo_hasta').val(p.hasta || ''); $('#promo_activo').val(String(Number(p.activo)));
  $('#promo_guardar').text('Guardar cambios'); $('#promo_cancelar').removeClass('d-none');
}
function eliminar_promo(p) {
  Swal.fire({ titleText: '¿Eliminar «' + p.titulo + '»?', icon: 'warning', showCancelButton: true, confirmButtonText: 'Eliminar', cancelButtonText: 'Cancelar' })
    .then(function (r) { if (r.isConfirmed) { G.post(RUTA_PUB + 'eliminar_promocion.php', { id_promocion: p.id_promocion }, function () { G.ok('Promoción eliminada'); cargar_pagina_publica(); }); } });
}

function listar_solicitudes() {
  G.post(RUTA_PUB + 'solicitudes.php', { estado: $('#sol_estado').val() }, function (r) {
    G.tabla('#cuerpo_pub_solicitudes', r.solicitudes, [
      function (s) { return new Date(s.creado_en.replace(' ', 'T')).toLocaleString('es-PE', { dateStyle: 'short', timeStyle: 'short' }); },
      function (s) { return $('<div></div>').append($('<b></b>').text(s.empresa), $('<div class="small text-muted"></div>').text(s.ruc ? 'RUC ' + s.ruc : 'Sin RUC')); },
      function (s) {
        var tel = $('<a></a>').attr('href', 'tel:' + s.telefono).text(s.telefono);
        var caja = $('<div></div>').append($('<div></div>').text(s.contacto), tel);
        if (s.correo) { caja.append($('<div class="small"></div>').append($('<a></a>').attr('href', 'mailto:' + s.correo).text(s.correo))); }
        return caja;
      },
      function (s) { return (s.unidades ? s.unidades + ' unid.' : '—') + (s.galones_mes ? ' · ' + G.num(s.galones_mes, 0) + ' gal/mes' : ''); },
      function (s) { return $('<div class="small"></div>').text(s.mensaje || '').append(s.nota ? $('<div class="text-muted"></div>').text('Nota: ' + s.nota) : null); },
      function (s) { var e = PUB_ESTADOS[s.estado] || [s.estado, 'badge-secondary']; return G.etiqueta(e[0], e[1]); },
      function (s) {
        return G.grupo(
          G.boton('Atender', 'btn-outline-primary', function () { atender_solicitud(s); }).attr('aria-label', 'Atender la solicitud de ' + s.empresa),
          PUB_FLOTAS && s.estado !== 'ATENDIDA' && s.estado !== 'DESCARTADA'
            ? G.boton('Convertir en flota', 'btn-primary', function () { convertir_en_flota(s); }).attr('aria-label', 'Convertir en flota a ' + s.empresa)
            : null
        );
      }
    ], 'No hay solicitudes en este estado.');
  });
}

function atender_solicitud(s) {
  var opciones = {};
  Object.keys(PUB_ESTADOS).forEach(function (k) { opciones[k] = PUB_ESTADOS[k][0]; });
  Swal.fire({
    title: s.empresa,
    html: '<label for="swal_nota" class="d-block text-left mt-2">Nota interna (opcional)</label><textarea id="swal_nota" class="swal2-textarea m-0 w-100" maxlength="400"></textarea>',
    input: 'select', inputOptions: opciones, inputValue: s.estado === 'NUEVA' ? 'EN_PROCESO' : s.estado, inputLabel: 'Estado de la solicitud',
    showCancelButton: true, confirmButtonText: 'Guardar', cancelButtonText: 'Cancelar',
    didOpen: function () { document.getElementById('swal_nota').value = s.nota || ''; },
    // La ventana se destruye al cerrarse: la nota se lee antes de confirmar.
    preConfirm: function (estado) { return { estado: estado, nota: document.getElementById('swal_nota').value }; }
  }).then(function (r) {
    if (!r.isConfirmed) { return; }
    G.post(RUTA_PUB + 'estado_solicitud.php', { id_solicitud: s.id_solicitud, estado: r.value.estado, nota: r.value.nota }, function () {
      G.ok('Solicitud actualizada'); listar_solicitudes(); cargar_pagina_publica();
    });
  });
}

// Crea la flota con los datos de la solicitud (empresa, RUC, contacto) y la marca como atendida.
function convertir_en_flota(s) {
  Swal.fire({
    title: 'Convertir en flota',
    html: '<p class="text-left mb-2"></p><label for="swal_limite" class="d-block text-left">Límite de crédito (S/)</label><input id="swal_limite" type="number" min="0" step="0.01" class="swal2-input m-0 w-100" placeholder="Sin límite">' +
      '<label for="swal_dias" class="d-block text-left mt-3">Días de crédito</label><input id="swal_dias" type="number" min="0" max="180" class="swal2-input m-0 w-100" value="30">',
    showCancelButton: true, confirmButtonText: 'Crear flota', cancelButtonText: 'Cancelar',
    didOpen: function () { Swal.getHtmlContainer().querySelector('p').textContent = s.empresa + (s.ruc ? ' · RUC ' + s.ruc : ''); },
    preConfirm: function () { return { limite: document.getElementById('swal_limite').value, dias: document.getElementById('swal_dias').value }; }
  }).then(function (r) {
    if (!r.isConfirmed) { return; }
    G.post('../controller/flotas/controlador_desde_solicitud.php', { id_solicitud: s.id_solicitud, limite_credito: r.value.limite, dias_credito: r.value.dias, solo_placas_autorizadas: '1' }, function () {
      G.ok('Flota creada. Agregue sus placas en Flotas.');
      listar_solicitudes();
      cargar_pagina_publica();
    });
  });
}
