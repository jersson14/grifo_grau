var RUTA_FLO = '../controller/flotas/controlador_';
var FLO_ACTUAL = null;

function iniciar_flotas() {
  $('#flo_mes').val(G.hoy().slice(0, 7));

  $('#flo_nueva').on('click', function () { $('#form_flota_nueva')[0].reset(); alternar_tipo_flota(); $('#modal_flota').modal('show'); });
  $('input[name="nf_tipo"]').on('change', alternar_tipo_flota);
  $('#form_flota_nueva').on('submit', function (e) {
    e.preventDefault();
    var nueva = $('#nf_tipo_nueva').is(':checked');
    G.post(RUTA_FLO + 'guardar.php', {
      nueva: '1', id_cliente: nueva ? '' : $('#nf_cliente').val(), nombre: $('#nf_nombre').val(), ruc: $('#nf_ruc').val(),
      limite_credito: $('#nf_limite').val(), dias_credito: $('#nf_dias').val(), contacto: $('#nf_contacto').val(),
      telefono: $('#nf_telefono').val(), correo: $('#nf_correo').val(), solo_placas_autorizadas: '1'
    }, function (r) {
      $('#modal_flota').modal('hide');
      G.ok('Flota creada');
      listar_flotas();
      abrir_flota(r.id_cliente);
    });
  });

  $('#flo_volver').on('click', function () { $('#flo_detalle').addClass('d-none'); $('#flo_lista').removeClass('d-none'); FLO_ACTUAL = null; listar_flotas(); });
  $('#filtro_flota_mes').on('submit', function (e) { e.preventDefault(); cargar_flota(); });

  $('#form_placa').on('submit', function (e) {
    e.preventDefault();
    G.post(RUTA_FLO + 'guardar_placa.php', { id_cliente: FLO_ACTUAL, id_placa: $('#pla_id').val(), placa: $('#pla_placa').val(), vehiculo: $('#pla_vehiculo').val(), conductor: $('#pla_conductor').val(), activo: $('#pla_activo').val() }, function () {
      G.ok($('#pla_id').val() ? 'Placa actualizada' : 'Placa agregada');
      limpiar_placa();
      cargar_flota();
    });
  });
  $('#pla_cancelar').on('click', limpiar_placa);

  $('#form_flota_datos').on('submit', function (e) {
    e.preventDefault();
    G.post(RUTA_FLO + 'guardar.php', {
      id_cliente: FLO_ACTUAL, limite_credito: $('#dat_limite').val(), dias_credito: $('#dat_dias').val(), estado: $('#dat_estado').val(),
      contacto: $('#dat_contacto').val(), telefono: $('#dat_telefono').val(), correo: $('#dat_correo').val(), observaciones: $('#dat_observaciones').val(),
      solo_placas_autorizadas: $('#dat_solo_placas').is(':checked') ? '1' : '0'
    }, function () { G.ok('Datos guardados'); cargar_flota(); });
  });

  listar_flotas();
}

function alternar_tipo_flota() {
  var nueva = $('#nf_tipo_nueva').is(':checked');
  $('.nf-nueva').toggleClass('d-none', !nueva);
  $('.nf-cliente').toggleClass('d-none', nueva);
  $('#nf_cliente').prop('required', !nueva);
  $('#nf_nombre').prop('required', nueva);
}

function barra_uso(uso) {
  if (uso === null) { return $('<span class="small text-muted"></span>').text('Sin límite'); }
  var color = uso > 100 ? 'tw-bg-red-600' : (uso >= 80 ? 'tw-bg-amber-500' : 'tw-bg-blue-600');
  return $('<div class="tw-mt-1 tw-flex tw-items-center tw-gap-2"></div>').append(
    $('<div class="tw-h-1.5 tw-w-28 tw-overflow-hidden tw-rounded-full tw-bg-zinc-100"></div>').append($('<div class="tw-h-full tw-rounded-full"></div>').addClass(color).css('width', Math.min(100, uso) + '%')),
    $('<span class="small text-muted"></span>').text(uso + '%')
  );
}

function listar_flotas() {
  G.post(RUTA_FLO + 'listar.php', {}, function (r) {
    G.opciones('#nf_cliente', r.clientes, 'id_cliente', function (c) { return Texto_Decodificado(c.nombre_completo) + (c.dni_ruc ? ' · ' + c.dni_ruc : ''); }, 'Elija un cliente');

    var deuda = 0, vencido = 0, alertas = 0;
    r.flotas.forEach(function (f) { deuda += f.deuda; vencido += f.vencido; alertas += (f.sobre_limite ? 1 : 0) + (Number(f.vales_no_autorizados) ? 1 : 0); });
    var tarjeta = function (clase, icono, titulo, valor) {
      return $('<div class="col-sm-6 col-lg-3"></div>').append($('<div class="info-box"></div>').addClass(clase).append(
        $('<span class="info-box-icon"></span>').append($('<i aria-hidden="true"></i>').addClass('fas ' + icono)),
        $('<div class="info-box-content"></div>').append($('<span class="info-box-text"></span>').text(titulo), $('<span class="info-box-number"></span>').text(valor))));
    };
    $('#flo_tarjetas').empty().append(
      tarjeta('bg-info', 'fa-truck', 'Flotas', r.flotas.length),
      tarjeta('', 'fa-file-invoice-dollar', 'Deuda total', G.soles(deuda)),
      tarjeta(vencido > 0 ? 'bg-danger' : '', 'fa-calendar-times', 'Vencido', G.soles(vencido)),
      tarjeta(alertas > 0 ? 'bg-warning' : 'bg-success', 'fa-exclamation-triangle', 'Flotas con avisos', alertas)
    );

    G.tabla('#cuerpo_flotas', r.flotas, [
      function (f) { return $('<div></div>').append($('<b></b>').text(Texto_Decodificado(f.nombre_completo)), $('<div class="small text-muted"></div>').text((f.dni_ruc ? 'RUC ' + f.dni_ruc : 'Sin RUC') + ' · ' + f.placas + ' placa(s)')); },
      function (f) { return $('<div></div>').append($('<span></span>').text(G.soles(f.deuda) + (f.limite_credito !== null ? ' de ' + G.soles(f.limite_credito) : '')), barra_uso(f.uso_limite)); },
      function (f) { return $('<span class="d-block text-right"></span>').text(G.soles(f.consumo_mes)); },
      function (f) { return $('<span class="d-block text-right"></span>').addClass(f.vencido > 0 ? 'text-danger font-weight-bold' : '').text(G.soles(f.vencido)); },
      function (f) {
        var caja = $('<div class="d-flex flex-wrap" style="gap:.25rem"></div>');
        if (f.estado === 'SUSPENDIDA') { caja.append(G.etiqueta('Suspendida', 'badge-secondary')); }
        if (f.sobre_limite) { caja.append(G.etiqueta('Sobre el límite', 'badge-danger')); }
        if (Number(f.vales_no_autorizados)) { caja.append(G.etiqueta(f.vales_no_autorizados + ' vale(s) de placa no autorizada', 'badge-warning')); }
        if (f.vencido > 0) { caja.append(G.etiqueta('Deuda vencida', 'badge-danger')); }
        return caja.children().length ? caja : $('<span class="text-muted small">Sin avisos</span>');
      },
      function (f) { return G.boton('Ver', 'btn-outline-primary', function () { abrir_flota(f.id_cliente); }).attr('aria-label', 'Ver la flota ' + Texto_Decodificado(f.nombre_completo)); }
    ], 'Aún no hay flotas. Cree la primera con «Nueva flota» o desde una solicitud de la página pública.');
  });
}

function abrir_flota(id) {
  FLO_ACTUAL = id;
  limpiar_placa();
  $('#flo_lista').addClass('d-none');
  $('#flo_detalle').removeClass('d-none');
  $('a[href="#tab_flo_cuenta"]').tab('show');
  cargar_flota();
}

function cargar_flota() {
  var mes = $('#flo_mes').val();
  $('#flo_pdf').attr('href', '../view/MPDF/REPORTE/estado_cuenta_flota.php?' + $.param({ id: FLO_ACTUAL, mes: mes }));
  G.post(RUTA_FLO + 'detalle.php', { id_cliente: FLO_ACTUAL, mes: mes }, function (d) {
    var f = d.flota, t = d.totales;
    $('#flo_titulo').text(Texto_Decodificado(f.nombre_completo) + (f.dni_ruc ? ' · RUC ' + f.dni_ruc : ''));
    $('#flo_estado_badge').empty().append(G.etiqueta(f.estado === 'ACTIVA' ? 'Activa' : 'Suspendida', f.estado === 'ACTIVA' ? 'badge-success' : 'badge-secondary'));

    var tarjeta = function (clase, titulo, valor, ayuda) {
      var c = $('<div class="info-box-content"></div>').append($('<span class="info-box-text"></span>').text(titulo), $('<span class="info-box-number"></span>').text(valor));
      if (ayuda) { c.append($('<span class="small text-muted"></span>').text(ayuda)); }
      return $('<div class="col-sm-6 col-xl"></div>').append($('<div class="info-box"></div>').addClass(clase).append(c));
    };
    $('#flo_totales').empty().append(
      tarjeta('', 'Consumo del mes', G.soles(t.consumo_mes)),
      tarjeta('', 'Pagado en el mes', G.soles(t.pagado_mes)),
      tarjeta('', 'Deuda total', G.soles(t.deuda), f.limite_credito !== null ? 'Límite ' + G.soles(f.limite_credito) : 'Sin límite'),
      tarjeta(t.vencido > 0 ? 'bg-danger' : '', 'Vencido', G.soles(t.vencido)),
      tarjeta(t.disponible !== null && t.disponible < 0 ? 'bg-danger' : '', 'Disponible', t.disponible !== null ? G.soles(t.disponible) : '—')
    );

    G.tabla('#cuerpo_flota_vales', d.vales, [
      function (v) { return Fecha_Local(v.fecha).toLocaleDateString('es-PE'); },
      function (v) { return v.numero_vale || '—'; },
      function (v) { var c = $('<span></span>').text(v.placa || '—'); return v.autorizada ? c : $('<span></span>').append(c, ' ', G.etiqueta('No autorizada', 'badge-warning')); },
      function (v) { return v.turno || '—'; },
      function (v) { return $('<span class="d-block text-right"></span>').text(G.soles(v.monto)); },
      function (v) { return $('<span class="d-block text-right"></span>').text(G.soles(v.saldo_pendiente)); },
      function (v) { return v.estado === 'PAGADO' ? G.etiqueta('Pagado', 'badge-success') : (v.vencido ? G.etiqueta('Vencido', 'badge-danger') : G.etiqueta('Pendiente', 'badge-secondary')); }
    ], 'Sin consumos en este mes.');

    G.tabla('#cuerpo_flota_placas_consumo', d.por_placa, [
      function (p) { var t = p.placa === 'SIN PLACA' ? 'Sin placa' : formato_placa(p.placa); return p.autorizada ? t : $('<span></span>').append(document.createTextNode(t + ' '), G.etiqueta('No autorizada', 'badge-warning')); },
      function (p) { return p.vales; },
      function (p) { return $('<span class="d-block text-right"></span>').text(G.soles(p.total)); }
    ], 'Sin consumos.');

    G.tabla('#cuerpo_flota_pagos', d.pagos, [
      function (p) { return Fecha_Local(p.fecha_pago).toLocaleDateString('es-PE'); },
      function (p) { return (p.medio || '') + (p.codigo_operacion ? ' · ' + p.codigo_operacion : ''); },
      function (p) { return p.numero_vale || '—'; },
      function (p) { return $('<span class="d-block text-right"></span>').text(G.soles(Number(p.monto_pagado) + Number(p.descuento))); }
    ], 'Sin pagos en este mes.');

    G.tabla('#cuerpo_flota_placas', d.placas, [
      function (p) { return $('<b></b>').text(formato_placa(p.placa)); },
      function (p) { return p.vehiculo || '—'; },
      function (p) { return p.conductor || '—'; },
      function (p) { return G.etiqueta(Number(p.activo) ? 'Autorizada' : 'Inactiva', Number(p.activo) ? 'badge-success' : 'badge-secondary'); },
      function (p) { return G.boton('Editar', 'btn-outline-primary', function () { editar_placa(p); }).attr('aria-label', 'Editar la placa ' + formato_placa(p.placa)); }
    ], 'Sin placas registradas. Agregue las placas que pueden cargar con vale.');

    $('#dat_limite').val(f.limite_credito !== null ? f.limite_credito : '');
    $('#dat_dias').val(f.dias_credito);
    $('#dat_estado').val(f.estado);
    $('#dat_contacto').val(f.contacto || '');
    $('#dat_telefono').val(f.telefono || '');
    $('#dat_correo').val(f.correo || '');
    $('#dat_observaciones').val(f.observaciones || '');
    $('#dat_solo_placas').prop('checked', Number(f.solo_placas_autorizadas) === 1);
  });
}

// ABC123 se muestra como ABC-123.
function formato_placa(placa) {
  return /^[A-Z0-9]{6}$/.test(placa) ? placa.slice(0, 3) + '-' + placa.slice(3) : placa;
}

// Los nombres antiguos pueden estar guardados ya escapados (&amp;): se muestran decodificados, siempre como texto.
function Texto_Decodificado(valor) {
  var t = document.createElement('textarea');
  t.innerHTML = valor == null ? '' : String(valor);
  return t.value;
}

function limpiar_placa() {
  $('#pla_id').val(''); $('#pla_placa').val(''); $('#pla_vehiculo').val(''); $('#pla_conductor').val(''); $('#pla_activo').val('1');
  $('#pla_guardar').text('Agregar placa'); $('#pla_cancelar').addClass('d-none');
}

function editar_placa(p) {
  $('#pla_id').val(p.id_placa); $('#pla_placa').val(formato_placa(p.placa)).trigger('focus'); $('#pla_vehiculo').val(p.vehiculo || ''); $('#pla_conductor').val(p.conductor || ''); $('#pla_activo').val(String(Number(p.activo)));
  $('#pla_guardar').text('Guardar cambios'); $('#pla_cancelar').removeClass('d-none');
}
