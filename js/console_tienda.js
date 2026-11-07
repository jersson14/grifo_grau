var RUTA_TIE = '../controller/tienda/controlador_';
var TIE = { productos: [], carrito: {}, medios: {}, gestiona: false };
var TIE_CON_CODIGO = ['YAPE', 'TARJETA', 'TRANSFERENCIA'];

function iniciar_tienda() {
  $('#tie_desde').val(G.hoy());
  $('#tie_hasta').val(G.hoy());

  $('#tie_buscar').on('input', pintar_catalogo).on('keydown', function (e) {
    // Un lector de código de barras escribe el código y pulsa Enter: el producto pasa directo a la venta.
    if (e.key !== 'Enter') { return; }
    e.preventDefault();
    var texto = this.value.trim().toLowerCase();
    var exacto = TIE.productos.find(function (p) { return Number(p.activo) && p.codigo && p.codigo.toLowerCase() === texto; });
    var visibles = productos_filtrados();
    var elegido = exacto || (visibles.length === 1 ? visibles[0] : null);
    if (elegido) { agregar_al_carrito(elegido.id_producto); this.value = ''; pintar_catalogo(); }
  });
  $('#tie_medio').on('change', alternar_operacion);
  $('#form_cobrar').on('submit', function (e) { e.preventDefault(); cobrar(); });
  $('#filtro_tienda').on('submit', function (e) { e.preventDefault(); listar_ventas_tienda(); });
  $('a[href="#tab_tie_ventas"]').on('shown.bs.tab', listar_ventas_tienda);

  $('#form_producto_tienda').on('submit', function (e) {
    e.preventDefault();
    G.post(RUTA_TIE + 'guardar_producto.php', {
      id_producto: $('#tp_id').val(), nombre: $('#tp_nombre').val(), codigo: $('#tp_codigo').val(), categoria: $('#tp_categoria').val(), unidad: $('#tp_unidad').val(),
      precio_venta: $('#tp_precio').val(), costo: $('#tp_costo').val(), stock_minimo: $('#tp_minimo').val(), stock_inicial: $('#tp_inicial').val(), activo: $('#tp_activo').val()
    }, function () { G.ok($('#tp_id').val() ? 'Producto actualizado' : 'Producto agregado'); limpiar_producto_tienda(); cargar_tienda(); });
  });
  $('#tp_cancelar').on('click', limpiar_producto_tienda);

  cargar_tienda();
}

function cargar_tienda() {
  G.post(RUTA_TIE + 'datos.php', {}, function (r) {
    TIE.productos = r.productos;
    TIE.gestiona = r.gestiona;
    TIE.medios = {};
    r.medios.forEach(function (m) { TIE.medios[m.codigo] = m.nombre; });
    if (!$('#tie_medio option').length) {
      G.opciones('#tie_medio', r.medios, 'codigo', function (m) { return m.nombre; });
      G.opciones('#tp_categoria', r.categorias.map(function (c) { return { c: c }; }), 'c', function (x) { return x.c; });
      G.opciones('#tp_unidad', r.unidades, 'codigo', function (u) { return u.nombre; });
      alternar_operacion();
    }
    $('#tie_tab_productos_item').toggleClass('d-none', !r.gestiona);
    var bajos = r.productos.filter(function (p) { return Number(p.stock_bajo); }).length;
    $('#tie_stock_bajo').text(bajos ? bajos : '');
    pintar_catalogo();
    pintar_carrito();
    if (r.gestiona) { pintar_productos_tienda(); }
  });
}

function cantidad_texto(n) {
  return Number(n).toLocaleString('es-PE', { maximumFractionDigits: 2 });
}

function productos_filtrados() {
  var texto = ($('#tie_buscar').val() || '').trim().toLowerCase();
  return TIE.productos.filter(function (p) {
    return Number(p.activo) && (!texto || p.nombre.toLowerCase().indexOf(texto) !== -1 || (p.codigo || '').toLowerCase().indexOf(texto) !== -1);
  });
}

function pintar_catalogo() {
  var caja = $('#tie_catalogo').empty();
  var lista = productos_filtrados();
  if (!lista.length) {
    caja.append($('<p class="text-muted tw-col-span-full"></p>').text(TIE.productos.length ? 'No hay productos con ese nombre o código.' : 'Aún no hay productos. Agréguelos en «Productos y stock».'));
    return;
  }
  lista.forEach(function (p) {
    var agotado = Number(p.stock) <= 0;
    var boton = $('<button type="button" role="listitem" class="tw-flex tw-min-h-[5.5rem] tw-flex-col tw-items-start tw-justify-between tw-rounded-lg tw-border tw-border-zinc-200 tw-bg-white tw-p-3 tw-text-left hover:tw-border-zinc-400 hover:tw-bg-zinc-50 disabled:tw-cursor-not-allowed disabled:tw-opacity-50"></button>')
      .prop('disabled', agotado)
      .append(
        $('<span class="tw-text-sm tw-font-medium tw-text-zinc-900"></span>').text(Texto_Seguro_Texto(p.nombre)),
        $('<span class="tw-mt-2 tw-flex tw-w-full tw-items-baseline tw-justify-between tw-gap-2"></span>').append(
          $('<span class="tw-font-semibold tw-text-zinc-950"></span>').text(G.soles(p.precio_venta)),
          $('<span class="tw-text-xs"></span>').addClass(agotado ? 'tw-text-red-700' : (Number(p.stock_bajo) ? 'tw-text-amber-700' : 'tw-text-zinc-500')).text(agotado ? 'Agotado' : 'Stock ' + cantidad_texto(p.stock))
        )
      )
      .attr('aria-label', 'Agregar ' + Texto_Seguro_Texto(p.nombre) + ', ' + G.soles(p.precio_venta) + (agotado ? ', agotado' : ''))
      .on('click', function () { agregar_al_carrito(p.id_producto); });
    caja.append(boton);
  });
}

// Los nombres antiguos pueden estar guardados escapados: se decodifican para mostrarlos como texto.
function Texto_Seguro_Texto(valor) {
  var t = document.createElement('textarea');
  t.innerHTML = valor == null ? '' : String(valor);
  return t.value;
}

function producto(id) {
  return TIE.productos.find(function (p) { return Number(p.id_producto) === Number(id); });
}

function agregar_al_carrito(id) {
  var p = producto(id);
  if (!p) { return; }
  var actual = TIE.carrito[id] || 0;
  if (actual + 1 > Number(p.stock)) {
    Swal.fire({ toast: true, position: 'top-end', icon: 'warning', title: 'No hay más stock de ' + Texto_Seguro_Texto(p.nombre), showConfirmButton: false, timer: 2500 });
    return;
  }
  TIE.carrito[id] = actual + 1;
  pintar_carrito();
}

function cambiar_cantidad(id, valor) {
  var p = producto(id);
  var n = Math.round(Number(valor) * 100) / 100;
  if (!(n > 0)) { delete TIE.carrito[id]; } else { TIE.carrito[id] = Math.min(n, Number(p.stock)); }
  pintar_carrito();
}

function total_carrito() {
  return Object.keys(TIE.carrito).reduce(function (s, id) { var p = producto(id); return s + (p ? Number(p.precio_venta) * TIE.carrito[id] : 0); }, 0);
}

function pintar_carrito() {
  var ids = Object.keys(TIE.carrito).filter(function (id) { return producto(id); });
  var filas = ids.map(function (id) { return { id: id, p: producto(id), cantidad: TIE.carrito[id] }; });
  G.tabla('#tie_carrito', filas, [
    function (f) { return $('<div></div>').append($('<div></div>').text(Texto_Seguro_Texto(f.p.nombre)), $('<div class="small text-muted"></div>').text(G.soles(f.p.precio_venta) + ' c/u')); },
    function (f) {
      var entrada = $('<input type="number" min="0" step="1" class="form-control form-control-sm text-center" inputmode="decimal">').val(f.cantidad)
        .attr('aria-label', 'Cantidad de ' + Texto_Seguro_Texto(f.p.nombre))
        .on('change', function () { cambiar_cantidad(f.id, this.value); });
      return $('<div class="input-group input-group-sm"></div>').append(
        $('<div class="input-group-prepend"></div>').append(G.boton('−', 'btn-outline-secondary mr-0', function () { cambiar_cantidad(f.id, f.cantidad - 1); }).attr('aria-label', 'Quitar uno')),
        entrada,
        $('<div class="input-group-append"></div>').append(G.boton('+', 'btn-outline-secondary mr-0', function () { agregar_al_carrito(f.id); }).attr('aria-label', 'Agregar uno'))
      );
    },
    function (f) { return $('<span class="d-block text-right text-nowrap"></span>').text(G.soles(Number(f.p.precio_venta) * f.cantidad)); },
    function (f) { return G.boton('', 'btn-outline-danger', function () { delete TIE.carrito[f.id]; pintar_carrito(); }).append($('<i class="fas fa-times" aria-hidden="true"></i>')).attr('aria-label', 'Quitar ' + Texto_Seguro_Texto(f.p.nombre)); }
  ], 'Toque un producto para agregarlo.');
  $('#tie_total').text(G.soles(total_carrito()));
  $('#tie_cobrar').prop('disabled', !ids.length);
}

function alternar_operacion() {
  var necesita = TIE_CON_CODIGO.indexOf($('#tie_medio').val()) !== -1;
  $('#tie_operacion_grupo').toggleClass('d-none', !necesita);
  $('#tie_operacion').prop('required', necesita);
  if (!necesita) { $('#tie_operacion').val(''); }
}

function cobrar() {
  var items = Object.keys(TIE.carrito).map(function (id) { return { id_producto: Number(id), cantidad: TIE.carrito[id] }; });
  if (!items.length) { return; }
  $('#tie_cobrar').prop('disabled', true);
  $.post(RUTA_TIE + 'vender.php', { items: JSON.stringify(items), medio_pago: $('#tie_medio').val(), codigo_operacion: $('#tie_operacion').val() }, function (r) {
    if (!r || r.success === false) {
      Swal.fire('No se pudo cobrar', (r && r.message) || 'Inténtelo de nuevo.', 'warning');
      $('#tie_cobrar').prop('disabled', false);
      cargar_tienda();
      return;
    }
    TIE.carrito = {};
    $('#tie_operacion').val('');
    Swal.fire({ icon: 'success', title: 'Venta ' + r.numero, text: 'Total cobrado: ' + G.soles(r.total), timer: 1800, showConfirmButton: false, returnFocus: false,
      didClose: function () { $('#tie_buscar').trigger('focus'); } });
    cargar_tienda();
  }, 'json');
}

function listar_ventas_tienda() {
  G.post(RUTA_TIE + 'ventas.php', { desde: $('#tie_desde').val(), hasta: $('#tie_hasta').val() }, function (r) {
    var s = r.resumen;
    var tarjeta = function (clase, icono, titulo, valor) {
      return $('<div class="col-sm-6 col-lg-3"></div>').append($('<div class="info-box"></div>').addClass(clase).append(
        $('<span class="info-box-icon"></span>').append($('<i aria-hidden="true"></i>').addClass('fas ' + icono)),
        $('<div class="info-box-content"></div>').append($('<span class="info-box-text"></span>').text(titulo), $('<span class="info-box-number"></span>').text(valor))));
    };
    $('#tie_tarjetas').empty().append(
      tarjeta('bg-info', 'fa-cash-register', 'Vendido', G.soles(s.total)),
      tarjeta('', 'fa-receipt', 'Ventas', s.ventas),
      tarjeta('bg-success', 'fa-chart-line', 'Margen', TIE.gestiona ? G.soles(s.margen) : '—'),
      tarjeta(s.stock_bajo ? 'bg-warning' : '', 'fa-box-open', 'Productos con stock bajo', s.stock_bajo)
    );
    G.tabla('#tie_por_medio', s.por_medio, [
      function (m) { return TIE.medios[m.medio] || m.medio; },
      function (m) { return m.ventas; },
      function (m) { return $('<span class="d-block text-right"></span>').text(G.soles(m.total)); }
    ], 'Sin ventas en el periodo.');
    G.tabla('#tie_por_producto', s.por_producto, [
      function (p) { return Texto_Seguro_Texto(p.nombre); },
      function (p) { return $('<span class="d-block text-right"></span>').text(cantidad_texto(p.cantidad)); },
      function (p) { return $('<span class="d-block text-right"></span>').text(G.soles(p.total)); },
      function (p) { return $('<span class="d-block text-right"></span>').text(p.margen === null || !TIE.gestiona ? '—' : G.soles(p.margen)); }
    ], 'Sin ventas en el periodo.');
    G.tabla('#tie_ventas', r.ventas, [
      function (v) { return v.numero; },
      function (v) { return new Date(v.fecha.replace(' ', 'T')).toLocaleString('es-PE', { dateStyle: 'short', timeStyle: 'short' }); },
      function (v) {
        var c = $('<div class="small"></div>').text(Texto_Seguro_Texto(v.productos || ''));
        if (v.estado === 'ANULADA' && v.motivo_anulacion) { c.append($('<div class="text-danger"></div>').text('Anulada: ' + v.motivo_anulacion)); }
        return c;
      },
      function (v) { return (TIE.medios[v.medio_pago] || v.medio_pago) + (v.codigo_operacion ? ' · ' + v.codigo_operacion : ''); },
      function (v) { var t = $('<span class="d-block text-right"></span>').text(G.soles(v.total)); if (v.estado === 'ANULADA') { t.css('text-decoration', 'line-through'); } return t; },
      function (v) { return v.vendedor || ''; },
      function (v) { return G.etiqueta(v.estado === 'ACTIVA' ? 'Activa' : 'Anulada', v.estado === 'ACTIVA' ? 'badge-success' : 'badge-secondary'); },
      function (v) {
        return TIE.gestiona && v.estado === 'ACTIVA' && G.boton('Anular', 'btn-outline-danger', function () {
          G.pedirMotivo('Anular la venta ' + v.numero + ' (' + G.soles(v.total) + ')', function (motivo) {
            G.post(RUTA_TIE + 'anular.php', { id_venta: v.id_venta, motivo: motivo }, function () { G.ok('Venta anulada: los productos vuelven al stock'); listar_ventas_tienda(); cargar_tienda(); });
          });
        }).attr('aria-label', 'Anular la venta ' + v.numero);
      }
    ], 'Sin ventas en el periodo.');
  });
}

function pintar_productos_tienda() {
  G.tabla('#tie_productos', TIE.productos, [
    function (p) { return $('<div></div>').append($('<b></b>').text(Texto_Seguro_Texto(p.nombre)), $('<div class="small text-muted"></div>').text(p.codigo ? 'Código ' + p.codigo : 'Sin código')); },
    function (p) { return p.categoria; },
    function (p) { return $('<span class="d-block text-right"></span>').text(G.soles(p.precio_venta)); },
    function (p) { return $('<span class="d-block text-right"></span>').text(cantidad_texto(p.stock) + ' ' + p.unidad); },
    function (p) {
      if (!Number(p.activo)) { return G.etiqueta('Inactivo', 'badge-secondary'); }
      if (Number(p.stock) <= 0) { return G.etiqueta('Agotado', 'badge-danger'); }
      return Number(p.stock_bajo) ? G.etiqueta('Stock bajo', 'badge-warning') : G.etiqueta('Disponible', 'badge-success');
    },
    function (p) {
      var nombre = Texto_Seguro_Texto(p.nombre);
      return G.grupo(
        G.boton('Ingreso', 'btn-outline-primary', function () { ingreso_tienda(p); }).attr('aria-label', 'Registrar ingreso de ' + nombre),
        G.boton('Ajustar', 'btn-outline-secondary', function () { ajuste_tienda(p); }).attr('aria-label', 'Ajustar el stock de ' + nombre),
        G.boton('Kardex', 'btn-outline-secondary', function () { kardex_tienda(p); }).attr('aria-label', 'Ver el kardex de ' + nombre),
        G.boton('Editar', 'btn-outline-secondary', function () { editar_producto_tienda(p); }).attr('aria-label', 'Editar ' + nombre)
      );
    }
  ], 'Aún no hay productos. Agregue el primero arriba.');
}

function ingreso_tienda(p) {
  Swal.fire({
    title: 'Ingreso de ' + Texto_Seguro_Texto(p.nombre),
    html: '<label for="sw_cant" class="d-block text-left">Cantidad que ingresa</label><input id="sw_cant" type="number" min="0.01" step="0.01" class="swal2-input m-0 w-100">' +
      '<label for="sw_costo" class="d-block text-left mt-3">Costo unitario (S/, opcional)</label><input id="sw_costo" type="number" min="0" step="0.01" class="swal2-input m-0 w-100">' +
      '<label for="sw_det" class="d-block text-left mt-3">Detalle (proveedor, factura)</label><input id="sw_det" maxlength="200" class="swal2-input m-0 w-100">',
    showCancelButton: true, confirmButtonText: 'Registrar ingreso', cancelButtonText: 'Cancelar',
    didOpen: function () { document.getElementById('sw_costo').value = p.costo || ''; document.getElementById('sw_cant').focus(); },
    preConfirm: function () { return { cantidad: document.getElementById('sw_cant').value, costo: document.getElementById('sw_costo').value, detalle: document.getElementById('sw_det').value }; }
  }).then(function (r) {
    if (!r.isConfirmed) { return; }
    G.post(RUTA_TIE + 'entrada.php', { id_producto: p.id_producto, cantidad: r.value.cantidad, costo: r.value.costo, detalle: r.value.detalle }, function (x) {
      G.ok('Ingreso registrado. Stock: ' + cantidad_texto(x.stock)); cargar_tienda();
    });
  });
}

function ajuste_tienda(p) {
  Swal.fire({
    title: 'Ajustar ' + Texto_Seguro_Texto(p.nombre),
    html: '<p class="text-left small text-muted mb-2"></p><label for="sw_contado" class="d-block text-left">Cantidad contada</label><input id="sw_contado" type="number" min="0" step="0.01" class="swal2-input m-0 w-100">' +
      '<label for="sw_motivo" class="d-block text-left mt-3">Motivo</label><input id="sw_motivo" maxlength="200" class="swal2-input m-0 w-100" placeholder="Conteo mensual, merma, producto vencido…">',
    showCancelButton: true, confirmButtonText: 'Ajustar stock', cancelButtonText: 'Cancelar',
    didOpen: function () { Swal.getHtmlContainer().querySelector('p').textContent = 'Stock en el sistema: ' + cantidad_texto(p.stock) + ' ' + p.unidad; document.getElementById('sw_contado').focus(); },
    preConfirm: function () { return { contado: document.getElementById('sw_contado').value, motivo: document.getElementById('sw_motivo').value }; }
  }).then(function (r) {
    if (!r.isConfirmed) { return; }
    G.post(RUTA_TIE + 'ajustar.php', { id_producto: p.id_producto, contado: r.value.contado, motivo: r.value.motivo }, function (x) {
      G.ok('Stock ajustado a ' + cantidad_texto(x.stock)); cargar_tienda();
    });
  });
}

function kardex_tienda(p) {
  var tipos = { ENTRADA: 'Ingreso', VENTA: 'Venta', AJUSTE: 'Ajuste', ANULACION: 'Anulación de venta' };
  G.post(RUTA_TIE + 'kardex.php', { id_producto: p.id_producto }, function (r) {
    $('#kardex_titulo').text('Kardex · ' + Texto_Seguro_Texto(p.nombre));
    G.tabla('#cuerpo_kardex_tienda', r.movimientos, [
      function (m) { return new Date(m.creado_en.replace(' ', 'T')).toLocaleString('es-PE', { dateStyle: 'short', timeStyle: 'short' }); },
      function (m) { return tipos[m.tipo] || m.tipo; },
      function (m) { return $('<span class="d-block text-right"></span>').addClass(Number(m.cantidad) < 0 ? 'text-danger' : 'text-success').text((Number(m.cantidad) > 0 ? '+' : '') + cantidad_texto(m.cantidad)); },
      function (m) { return $('<span class="d-block text-right"></span>').text(cantidad_texto(m.stock_resultante)); },
      function (m) { return m.venta ? 'Venta ' + m.venta : (m.detalle || ''); },
      function (m) { return m.usuario || ''; }
    ], 'Sin movimientos.');
    $('#modal_kardex_tienda').modal('show');
  });
}

function limpiar_producto_tienda() {
  $('#tp_id').val(''); $('#form_producto_tienda')[0].reset(); $('#tp_minimo').val('0');
  $('#tp_inicial_grupo').removeClass('d-none');
  $('#tp_guardar').text('Agregar producto'); $('#tp_cancelar').addClass('d-none');
}

function editar_producto_tienda(p) {
  $('#tp_id').val(p.id_producto); $('#tp_nombre').val(Texto_Seguro_Texto(p.nombre)).trigger('focus'); $('#tp_codigo').val(p.codigo || '');
  $('#tp_categoria').val(p.categoria); $('#tp_unidad').val(p.unidad); $('#tp_precio').val(p.precio_venta); $('#tp_costo').val(p.costo || '');
  $('#tp_minimo').val(p.stock_minimo); $('#tp_activo').val(String(Number(p.activo)));
  // El stock de un producto existente cambia con ingresos y ajustes, que quedan en el kardex.
  $('#tp_inicial').val(''); $('#tp_inicial_grupo').addClass('d-none');
  $('#tp_guardar').text('Guardar cambios'); $('#tp_cancelar').removeClass('d-none');
}
