var RUTA_VEN = '../controller/ventas/controlador_';

function iniciar_ventas(esAdmin) {
  var surtidores = [];
  var tiposPago = [];

  $('#venta_desde').val(G.hoy());
  $('#venta_hasta').val(G.hoy());

  G.post(RUTA_VEN + 'datos_formulario.php', {}, function (r) {
    surtidores = r.surtidores;
    tiposPago = r.tipos_pago;
    G.opciones('#venta_surtidor', r.surtidores, 'id_surtidor', function (s) {
      return 'M' + s.numero_maquina + ' ' + s.codigo + ' · ' + s.producto + ' (' + G.soles(s.precio_actual) + ')';
    }, 'Seleccione');
    G.opciones('#venta_pago', r.tipos_pago, 'id_tipo_pago', function (t) { return t.nombre; });
    // Efectivo es el medio más común y no exige código de operación.
    var efectivo = r.tipos_pago.filter(function (t) { return t.nombre === 'EFECTIVO'; })[0];
    if (efectivo) { $('#venta_pago').val(efectivo.id_tipo_pago); }
    G.opciones('#venta_cliente', r.clientes, 'id_cliente', function (c) { return c.nombre_completo + (c.dni_ruc ? ' · ' + c.dni_ruc : ''); }, 'Sin cliente');
    actualizar_pago();
  });

  function actualizar_pago() {
    var tipo = tiposPago.filter(function (t) { return t.id_tipo_pago == $('#venta_pago').val(); })[0];
    var requiere = !!tipo && tipo.requiere_codigo === 'SI';
    $('#grupo_codigo').toggle(requiere);
    $('#venta_codigo').prop('required', requiere);
    if (!requiere) { $('#venta_codigo').val(''); }
  }

  function actualizar_calculo() {
    var s = surtidores.filter(function (x) { return x.id_surtidor == $('#venta_surtidor').val(); })[0];
    var valor = Number($('#venta_valor').val()) || 0;
    var porSoles = $('#venta_modo').val() === 'SOLES';
    $('#venta_valor_etiqueta').text(porSoles ? 'Monto (S/)' : 'Galones');
    if (!s || valor <= 0) { $('#venta_calculo').text('—'); return; }
    var precio = Number(s.precio_actual);
    $('#venta_calculo').text(porSoles ? G.galones(valor / precio) + ' gal' : G.soles(valor * precio));
  }

  function listar() {
    var filtro = { desde: $('#venta_desde').val(), hasta: $('#venta_hasta').val() };
    var columnas = [
      function (v) { return v.creado_en; },
      function (v) { return 'M' + v.numero_maquina + ' ' + v.surtidor; },
      function (v) { return v.producto; },
      function (v) { return G.galones(v.galones); },
      function (v) { return G.soles(v.total); },
      function (v) { return v.medio_pago; },
      function (v) { return v.cliente; },
      function (v) { return v.placa; },
      function (v) {
        if (v.tipo_comprobante === 'NINGUNO') { return ''; }
        var texto = v.tipo_comprobante + (v.comprobante_numero ? ' ' + v.comprobante_numero : '');
        var seguro = v.comprobante_enlace && /^https?:\/\//i.test(v.comprobante_enlace);
        var caja = seguro ? $('<a target="_blank" rel="noopener noreferrer"></a>').attr('href', v.comprobante_enlace).text(texto) : $('<span></span>').text(texto);
        return v.comprobante_estado && v.comprobante_estado !== 'ACEPTADO' ? $('<span></span>').append(caja).append(' ').append(G.etiqueta(v.comprobante_estado.replace('_', ' '), 'badge-warning')) : caja;
      },
      function (v) { return v.turno || 'Pendiente'; },
      function (v) { return v.usuario; },
      function (v) { return G.etiqueta(v.estado, v.estado === 'ANULADA' ? 'badge-secondary' : 'badge-success'); }
    ];
    if (esAdmin) {
      columnas.push(function (v) {
        return v.estado === 'ANULADA' ? '' : G.boton('Anular', 'btn-outline-danger', function () {
          G.pedirMotivo('Anular esta venta', function (motivo) {
            G.post(RUTA_VEN + 'anular_venta.php', { id_venta: v.id_venta, motivo: motivo }, function () { G.ok('Venta anulada'); listar(); });
          });
        });
      });
    }
    G.post(RUTA_VEN + 'listar_ventas.php', filtro, function (r) { G.tabla('#cuerpo_ventas', r.data, columnas); });

    if (esAdmin) {
      G.post(RUTA_VEN + 'conciliacion.php', filtro, function (r) {
        G.tabla('#cuerpo_conciliacion', r.data, [
          function (f) { return f.fecha_reporte; },
          function (f) { return f.numero_documento + ' · ' + f.turno; },
          function (f) { return f.grifero; },
          function (f) { return f.producto; },
          function (f) { return G.galones(f.galones_lecturas); },
          function (f) { return G.galones(f.galones_ventas); },
          function (f) { return Math.abs(Number(f.diferencia)) < 0.001 ? G.etiqueta('Conciliado', 'badge-success') : G.etiqueta(G.galones(f.diferencia), 'badge-warning'); }
        ], 'No hay turnos cerrados en este periodo.');
      });
    }
  }

  $('#venta_pago').on('change', actualizar_pago);
  $('#venta_surtidor, #venta_modo, #venta_valor').on('input change', actualizar_calculo);
  $('#filtro_ventas').on('submit', function (e) { e.preventDefault(); listar(); });

  $('#form_venta').on('submit', function (e) {
    e.preventDefault();
    G.post(RUTA_VEN + 'registrar_venta.php', {
      id_surtidor: $('#venta_surtidor').val(), modo: $('#venta_modo').val(), valor: $('#venta_valor').val(), id_tipo_pago: $('#venta_pago').val(),
      codigo_operacion: $('#venta_codigo').val(), id_cliente: $('#venta_cliente').val(), placa: $('#venta_placa').val(), tipo_comprobante: $('#venta_comprobante').val()
    }, function (r) {
      if (r.comprobante && r.comprobante.estado === 'ACEPTADO') {
        G.ok(r.comprobante.tipo + ' ' + r.comprobante.serie + '-' + r.comprobante.numero + ' emitida');
      } else if (r.comprobante) {
        Swal.fire('Venta registrada', 'El comprobante aún no está confirmado. Se puede reenviar desde Comprobantes; la venta ya quedó guardada.', 'info');
      } else {
        G.ok('Venta registrada: ' + G.soles(r.total));
      }
      $('#venta_valor, #venta_codigo, #venta_placa').val('');
      $('#venta_cliente').val('');
      $('#venta_comprobante').val('NINGUNO');
      actualizar_calculo();
      listar();
    });
  });

  listar();
}
