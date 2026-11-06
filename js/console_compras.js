var RUTA_COM = '../controller/compras/controlador_';
var tanques_compra = [];

function iniciar_compras() {
  $('#compra_desde').val(G.inicioMes());
  $('#compra_hasta').val(G.hoy());

  cargar_compras();
  cargar_proveedores();

  $('#filtro_compras').on('submit', function (e) { e.preventDefault(); cargar_compras(); });
  $('#btn_nuevo_proveedor').on('click', function () { abrir_proveedor(null); });
  $('#btn_nueva_compra').on('click', abrir_compra);
  $('#btn_agregar_linea').on('click', agregar_linea);
  $('#compra_percepcion').on('input', calcular_total);

  $('#form_proveedor').on('submit', function (e) {
    e.preventDefault();
    G.post(RUTA_COM + 'guardar_proveedor.php', {
      id_proveedor: $('#prov_id').val(), ruc: $('#prov_ruc').val(), razon_social: $('#prov_razon').val(),
      telefono: $('#prov_telefono').val(), direccion: $('#prov_direccion').val()
    }, function () { $('#modal_proveedor').modal('hide'); G.ok('Proveedor guardado'); cargar_proveedores(); });
  });

  $('#form_compra').on('submit', function (e) {
    e.preventDefault();
    var lineas = $('#lineas_compra tr').map(function () {
      return { id_tanque: $(this).find('.linea_tanque').val(), galones: $(this).find('.linea_galones').val(), costo_galon: $(this).find('.linea_costo').val() };
    }).get();
    G.post(RUTA_COM + 'registrar_compra.php', {
      id_proveedor: $('#compra_proveedor').val(), tipo_documento: $('#compra_tipo').val(), numero_documento: $('#compra_numero').val(),
      fecha_compra: $('#compra_fecha').val(), fecha_vencimiento: $('#compra_vence').val(), percepcion: $('#compra_percepcion').val(),
      observaciones: $('#compra_obs').val(), lineas: JSON.stringify(lineas)
    }, function (r) {
      $('#modal_compra').modal('hide');
      if (r.avisos && r.avisos.length) { Swal.fire('Compra registrada', r.avisos.join(' '), 'warning'); } else { G.ok('Compra registrada'); }
      cargar_compras();
    });
  });

  $('#form_pago').on('submit', function (e) {
    e.preventDefault();
    G.post(RUTA_COM + 'registrar_pago.php', {
      id_compra: $('#pago_compra').val(), monto: $('#pago_monto').val(), fecha_pago: $('#pago_fecha').val(),
      medio: $('#pago_medio').val(), referencia: $('#pago_referencia').val()
    }, function () { $('#modal_pago').modal('hide'); G.ok('Pago registrado'); cargar_compras(); });
  });
}

function cargar_compras() {
  G.post(RUTA_COM + 'listar_compras.php', { desde: $('#compra_desde').val(), hasta: $('#compra_hasta').val(), filtro: $('#compra_filtro').val() }, function (r) {
    G.tabla('#cuerpo_compras', r.data, [
      function (c) { return c.fecha_compra; },
      function (c) { return c.proveedor; },
      function (c) { return c.tipo_documento + ' ' + c.numero_documento; },
      function (c) { return G.soles(c.total); },
      function (c) { return G.soles(c.saldo); },
      function (c) { return c.fecha_vencimiento; },
      function (c) {
        if (c.estado === 'ANULADA') { return G.etiqueta('Anulada', 'badge-secondary'); }
        if (Number(c.saldo) <= 0) { return G.etiqueta('Pagada', 'badge-success'); }
        return c.vencida == 1 ? G.etiqueta('Vencida', 'badge-danger') : G.etiqueta('Por pagar', 'badge-warning');
      },
      function (c) {
        var activa = c.estado !== 'ANULADA';
        return G.grupo(
          G.boton('Detalle', 'btn-info', function () { ver_compra(c); }),
          activa && Number(c.saldo) > 0 ? G.boton('Pagar', 'btn-success', function () { abrir_pago(c); }) : null,
          activa && Number(c.monto_pagado) === 0 ? G.boton('Anular', 'btn-outline-danger', function () {
            G.pedirMotivo('Anular la compra ' + c.numero_documento, function (motivo) {
              G.post(RUTA_COM + 'anular_compra.php', { id_compra: c.id_compra, motivo: motivo }, function () { G.ok('Compra anulada'); cargar_compras(); });
            });
          }) : null
        );
      }
    ]);
  });
}

function ver_compra(c) {
  G.post(RUTA_COM + 'detalle_compra.php', { id_compra: c.id_compra }, function (r) {
    var caja = $('<div class="text-left"></div>');
    r.lineas.forEach(function (l) {
      caja.append($('<div></div>').text(l.tanque + ': ' + G.galones(l.galones) + ' gal a ' + G.soles(l.costo_galon) + ' = ' + G.soles(l.importe)));
    });
    caja.append($('<div class="mt-2"></div>').append($('<b></b>').text('Percepción: ' + G.soles(c.percepcion))));
    if (r.pagos.length) { caja.append($('<div class="mt-2"></div>').append($('<b></b>').text('Pagos'))); }
    r.pagos.forEach(function (p) {
      caja.append($('<div></div>').text(p.fecha_pago + ': ' + G.soles(p.monto) + (p.medio ? ' (' + p.medio + ')' : '')));
    });
    Swal.fire({ title: c.numero_documento, html: caja[0] });
  });
}

function abrir_pago(c) {
  $('#pago_compra').val(c.id_compra);
  $('#pago_monto').val(Number(c.saldo).toFixed(2));
  $('#pago_fecha').val(G.hoy());
  $('#pago_medio, #pago_referencia').val('');
  $('#modal_pago').modal('show');
}

function abrir_compra() {
  $.when(
    $.post(RUTA_COM + 'listar_proveedores.php', { solo_activos: 1 }, null, 'json'),
    $.post('../controller/inventario/controlador_listar_tanques.php', { solo_activos: 1 }, null, 'json')
  ).done(function (p, t) {
    if (!p[0].data.length || !t[0].data.length) {
      Swal.fire('Faltan datos', 'Antes de registrar una compra necesitas al menos un proveedor y un tanque activos.', 'info');
      return;
    }
    tanques_compra = t[0].data;
    G.opciones('#compra_proveedor', p[0].data, 'id_proveedor', function (x) { return x.razon_social; }, 'Seleccione');
    $('#compra_numero, #compra_vence, #compra_obs').val('');
    $('#compra_percepcion').val(0);
    $('#compra_fecha').val(G.hoy());
    $('#lineas_compra').empty();
    agregar_linea();
    $('#modal_compra').modal('show');
  });
}

function agregar_linea() {
  var select = $('<select class="form-control form-control-sm linea_tanque" required aria-label="Tanque"></select>');
  tanques_compra.forEach(function (t) { select.append($('<option></option>').attr('value', t.id_tanque).text(t.nombre + ' · ' + (t.producto || ''))); });
  var fila = $('<tr></tr>')
    .append($('<td></td>').append(select))
    .append($('<td></td>').append('<input type="number" step="0.001" min="0.001" class="form-control form-control-sm linea_galones" required aria-label="Galones">'))
    .append($('<td></td>').append('<input type="number" step="0.0001" min="0.0001" class="form-control form-control-sm linea_costo" required aria-label="Costo por galón">'))
    .append($('<td class="linea_importe align-middle"></td>').text('S/ 0.00'))
    .append($('<td></td>').append(G.boton('Quitar', 'btn-outline-danger', function () { fila.remove(); calcular_total(); })));
  fila.find('input').on('input', calcular_total);
  $('#lineas_compra').append(fila);
}

function calcular_total() {
  var subtotal = 0;
  $('#lineas_compra tr').each(function () {
    var importe = Math.round((Number($(this).find('.linea_galones').val()) || 0) * (Number($(this).find('.linea_costo').val()) || 0) * 100) / 100;
    $(this).find('.linea_importe').text(G.soles(importe));
    subtotal += importe;
  });
  $('#compra_total').text(G.soles(subtotal + (Number($('#compra_percepcion').val()) || 0)));
}

function cargar_proveedores() {
  G.post(RUTA_COM + 'listar_proveedores.php', {}, function (r) {
    G.tabla('#cuerpo_proveedores', r.data, [
      function (p) { return p.ruc; },
      function (p) { return p.razon_social; },
      function (p) { return p.telefono; },
      function (p) { return p.direccion; },
      function (p) { return G.etiqueta(p.estado, p.estado === 'ACTIVO' ? 'badge-success' : 'badge-secondary'); },
      function (p) {
        var nuevo = p.estado === 'ACTIVO' ? 'INACTIVO' : 'ACTIVO';
        return G.grupo(
          G.boton('Editar', 'btn-primary', function () { abrir_proveedor(p); }),
          G.boton(nuevo === 'ACTIVO' ? 'Activar' : 'Desactivar', 'btn-outline-secondary', function () {
            G.post(RUTA_COM + 'cambiar_estado_proveedor.php', { id_proveedor: p.id_proveedor, estado: nuevo }, cargar_proveedores);
          })
        );
      }
    ], 'Aún no hay proveedores.');
  });
}

function abrir_proveedor(p) {
  $('#titulo_proveedor').text(p ? 'Editar proveedor' : 'Nuevo proveedor');
  $('#prov_id').val(p ? p.id_proveedor : '');
  $('#prov_ruc').val(p ? p.ruc : '');
  $('#prov_razon').val(p ? p.razon_social : '');
  $('#prov_telefono').val(p ? p.telefono : '');
  $('#prov_direccion').val(p ? p.direccion : '');
  $('#modal_proveedor').modal('show');
}
