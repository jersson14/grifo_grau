var RUTA_GAS = '../controller/gastos_grifo/controlador_';
var GAS_MEDIOS = {};

function iniciar_gastos() {
  $('#gas_desde').val(G.inicioMes());
  $('#gas_hasta').val(G.hoy());
  $('#gas_fecha').val(G.hoy()).attr('max', G.hoy());

  $('#filtro_gastos').on('submit', function (e) { e.preventDefault(); listar_gastos(); });
  $('#filtro_gastos input, #filtro_gastos select').on('change', actualizar_exportar);

  $('#form_gasto').on('submit', function (e) {
    e.preventDefault();
    G.post(RUTA_GAS + 'registrar.php', {
      fecha: $('#gas_fecha').val(), id_categoria: $('#gas_categoria').val(), descripcion: $('#gas_descripcion').val(),
      monto: $('#gas_monto').val(), medio_pago: $('#gas_medio').val(), comprobante: $('#gas_comprobante').val()
    }, function () {
      $('#gas_descripcion, #gas_monto, #gas_comprobante').val('');
      // Al cerrarse el aviso, el foco vuelve a la descripción para anotar el siguiente gasto.
      // Al cerrarse el aviso, el foco va a la descripción (y no al botón) para anotar el siguiente gasto.
      Swal.fire({ icon: 'success', title: 'Gasto registrado', timer: 1300, showConfirmButton: false, returnFocus: false,
        didClose: function () { $('#gas_descripcion').trigger('focus'); } });
      listar_gastos();
    });
  });

  $('#form_categoria_gasto').on('submit', function (e) {
    e.preventDefault();
    G.post(RUTA_GAS + 'guardar_categoria.php', { id_categoria: $('#cat_id').val(), nombre: $('#cat_nombre').val(), activo: $('#cat_activo').val() }, function () {
      G.ok($('#cat_id').val() ? 'Categoría actualizada' : 'Categoría agregada');
      limpiar_categoria();
      cargar_catalogos_gastos();
    });
  });
  $('#cat_cancelar').on('click', limpiar_categoria);

  cargar_catalogos_gastos(listar_gastos);
}

function cargar_catalogos_gastos(despues) {
  G.post(RUTA_GAS + 'datos.php', {}, function (r) {
    GAS_MEDIOS = {};
    r.medios.forEach(function (m) { GAS_MEDIOS[m.codigo] = m.nombre; });
    var activas = r.categorias.filter(function (c) { return Number(c.activo); });
    var elegida = $('#gas_categoria').val();
    G.opciones('#gas_categoria', activas, 'id_categoria', function (c) { return c.nombre; }, 'Elija una categoría');
    if (elegida) { $('#gas_categoria').val(elegida); }
    var filtro = $('#gas_filtro_categoria').val();
    G.opciones('#gas_filtro_categoria', r.categorias, 'id_categoria', function (c) { return c.nombre; }, 'Todas');
    $('#gas_filtro_categoria').val(filtro || '');
    G.opciones('#gas_medio', r.medios, 'codigo', function (m) { return m.nombre; });

    G.tabla('#cuerpo_categorias_gasto', r.categorias, [
      function (c) { return c.nombre; },
      function (c) { return Number(c.usos); },
      function (c) { return G.etiqueta(Number(c.activo) ? 'Activa' : 'Inactiva', Number(c.activo) ? 'badge-success' : 'badge-secondary'); },
      function (c) { return G.boton('Editar', 'btn-outline-primary', function () { editar_categoria(c); }).attr('aria-label', 'Editar ' + c.nombre); }
    ], 'Sin categorías.');
    actualizar_exportar();
    if (despues) { despues(); }
  });
}

function filtros_gastos() {
  return { desde: $('#gas_desde').val(), hasta: $('#gas_hasta').val(), id_categoria: $('#gas_filtro_categoria').val() || '', anulados: $('#gas_anulados').is(':checked') ? '1' : '0' };
}

function actualizar_exportar() {
  $('#gas_exportar').attr('href', RUTA_GAS + 'exportar.php?' + $.param(filtros_gastos()));
}

function listar_gastos() {
  actualizar_exportar();
  G.post(RUTA_GAS + 'listar.php', filtros_gastos(), function (r) {
    pintar_resumen_gastos(r.resumen);
    G.tabla('#cuerpo_gastos', r.gastos, [
      function (g) { return Fecha_Local(g.fecha).toLocaleDateString('es-PE'); },
      function (g) { return g.categoria; },
      function (g) {
        var caja = $('<div></div>').text(g.descripcion);
        if (g.comprobante) { caja.append($('<div class="small text-muted"></div>').text('Doc. ' + g.comprobante)); }
        if (g.estado === 'ANULADO' && g.motivo_anulacion) { caja.append($('<div class="small text-danger"></div>').text('Anulado: ' + g.motivo_anulacion)); }
        return caja;
      },
      function (g) { return GAS_MEDIOS[g.medio_pago] || g.medio_pago; },
      function (g) { var s = $('<span class="d-block text-right"></span>').text(G.soles(g.monto)); if (g.estado === 'ANULADO') { s.css('text-decoration', 'line-through'); } return s; },
      function (g) { return G.etiqueta(g.estado === 'ACTIVO' ? 'Activo' : 'Anulado', g.estado === 'ACTIVO' ? 'badge-success' : 'badge-secondary'); },
      function (g) {
        return g.estado === 'ACTIVO' && G.boton('Anular', 'btn-outline-danger', function () { anular_gasto(g); }).attr('aria-label', 'Anular el gasto ' + g.descripcion);
      }
    ], 'No hay gastos en este periodo.');
  });
}

function pintar_resumen_gastos(r) {
  var totalGastos = r.gastos + r.otros_gastos_turno;
  var tarjeta = function (clase, icono, titulo, valor, ayuda) {
    var contenido = $('<div class="info-box-content"></div>')
      .append($('<span class="info-box-text"></span>').text(titulo), $('<span class="info-box-number"></span>').text(valor));
    if (ayuda) { contenido.append($('<span class="small text-muted"></span>').text(ayuda)); }
    return $('<div class="col-sm-6 col-lg-3"></div>').append($('<div class="info-box"></div>').addClass(clase)
      .append($('<span class="info-box-icon"></span>').append($('<i aria-hidden="true"></i>').addClass('fas ' + icono)), contenido));
  };
  $('#gas_tarjetas').empty().append(
    tarjeta('bg-info', 'fa-gas-pump', 'Ventas', G.soles(r.ventas), 'Según los turnos del periodo'),
    tarjeta('', 'fa-truck-moving', 'Compras', G.soles(r.compras), 'Combustible a proveedores'),
    tarjeta('bg-warning', 'fa-receipt', 'Gastos', G.soles(totalGastos), r.otros_gastos_turno > 0 ? 'Incluye ' + G.soles(r.otros_gastos_turno) + ' de otros gastos en turnos' : 'Registrados en este módulo'),
    tarjeta(r.diferencia >= 0 ? 'bg-success' : 'bg-danger', r.diferencia >= 0 ? 'fa-arrow-up' : 'fa-arrow-down', 'Resultado del periodo', G.soles(r.diferencia), 'Ventas − compras − gastos, antes de impuestos')
  );

  var maximo = r.por_categoria.reduce(function (m, c) { return Math.max(m, c.total); }, 0) || 1;
  G.tabla('#cuerpo_gastos_categoria', r.por_categoria, [
    function (c) { return c.categoria; },
    function (c) { return c.cantidad; },
    function (c) { return $('<span class="d-block text-right"></span>').text(G.soles(c.total)); },
    function (c) {
      var porcentaje = r.gastos > 0 ? Math.round(c.total * 100 / r.gastos) : 0;
      var barra = $('<div class="tw-h-2 tw-flex-1 tw-overflow-hidden tw-rounded-full tw-bg-zinc-100"></div>')
        .append($('<div class="tw-h-full tw-rounded-full tw-bg-blue-600"></div>').css('width', Math.max(2, c.total * 100 / maximo) + '%'));
      return $('<div class="tw-flex tw-items-center tw-gap-3"></div>').append(barra, $('<span class="tw-w-10 tw-text-right tw-text-sm tw-text-zinc-600"></span>').text(porcentaje + '%'));
    }
  ], 'No hay gastos en este periodo.');
}

function anular_gasto(g) {
  G.pedirMotivo('Anular el gasto «' + g.descripcion + '» de ' + G.soles(g.monto), function (motivo) {
    G.post(RUTA_GAS + 'anular.php', { id_gasto: g.id_gasto, motivo: motivo }, function () { G.ok('Gasto anulado'); listar_gastos(); });
  });
}

function limpiar_categoria() {
  $('#cat_id').val(''); $('#cat_nombre').val(''); $('#cat_activo').val('1');
  $('#cat_guardar').text('Agregar categoría'); $('#cat_cancelar').addClass('d-none');
}

function editar_categoria(c) {
  $('#cat_id').val(c.id_categoria); $('#cat_nombre').val(c.nombre).trigger('focus'); $('#cat_activo').val(String(Number(c.activo)));
  $('#cat_guardar').text('Guardar cambios'); $('#cat_cancelar').removeClass('d-none');
}
