var RUTA_VEN = '../controller/ventas/controlador_';

// Color por combustible, el mismo de los gráficos del tablero.
function combustible_de(nombre) {
  var n = String(nombre || '').toUpperCase();
  if (n.indexOf('DIESEL') !== -1) { return 'diesel'; }
  if (n.indexOf('PREMIUM') !== -1 || n.indexOf('95') !== -1) { return 'premium'; }
  if (n.indexOf('REGULAR') !== -1 || n.indexOf('84') !== -1 || n.indexOf('90') !== -1) { return 'regular'; }
  return 'otro';
}

function iniciar_ventas(esAdmin) {
  var surtidores = [];
  var tiposPago = [];
  var RAPIDOS = { SOLES: [10, 20, 50, 100], GALONES: [1, 2, 5, 10] };

  $('#venta_desde').val(G.hoy());
  $('#venta_hasta').val(G.hoy());

  var elegido = function (nombre) { return $('#form_venta input[name=' + nombre + ']:checked').val() || ''; };
  var modo = function () { return elegido('venta_modo') || 'SOLES'; };

  function opcion(nombre, valor, contenido, requerido) {
    var input = $('<input type="radio">').attr({ name: nombre, value: valor }).prop('required', !!requerido);
    return $('<label class="g-opcion"></label>').append(input, contenido);
  }

  function pintar_surtidores() {
    var caja = $('#venta_surtidores').empty();
    if (!surtidores.length) {
      caja.append($('<p class="text-muted mb-0"></p>').text('No hay surtidores activos. Actívelos en Configuración → Surtidores.'));
      return;
    }
    var maquinas = {};
    surtidores.forEach(function (s) { (maquinas[s.numero_maquina] = maquinas[s.numero_maquina] || []).push(s); });
    Object.keys(maquinas).forEach(function (m) {
      var grilla = $('<div class="g-opciones"></div>');
      maquinas[m].forEach(function (s) {
        var cuerpo = $('<span class="g-opcion-cuerpo"></span>').attr('data-combustible', combustible_de(s.producto)).append(
          $('<span class="g-opcion-titulo"></span>').text(s.codigo),
          $('<span class="g-opcion-detalle"></span>').append($('<span class="g-punto" aria-hidden="true"></span>'), document.createTextNode(s.producto)),
          $('<span class="g-opcion-precio"></span>').text(G.soles(s.precio_actual)).append($('<span class="g-por-galon"></span>').text(' / gal'))
        );
        grilla.append(opcion('venta_surtidor', s.id_surtidor, cuerpo, true).attr('title', 'Máquina ' + m + ', ' + s.codigo + ', ' + s.producto));
      });
      caja.append($('<div class="g-maquina"></div>').append($('<span class="g-maquina-titulo"></span>').text('Máquina ' + m), grilla));
    });
  }

  function pintar_pagos() {
    var caja = $('#venta_pagos').empty();
    tiposPago.forEach(function (t) {
      caja.append($('<label></label>').append($('<input type="radio" name="venta_pago" required>').val(t.id_tipo_pago), $('<span></span>').text(t.nombre)));
    });
    // Efectivo es el medio más común y no exige código de operación.
    var efectivo = tiposPago.filter(function (t) { return t.nombre === 'EFECTIVO'; })[0] || tiposPago[0];
    if (efectivo) { caja.find('input').filter(function () { return this.value == efectivo.id_tipo_pago; }).prop('checked', true); }
  }

  function pintar_rapidos() {
    var caja = $('#venta_rapidos').empty();
    RAPIDOS[modo()].forEach(function (v) {
      var texto = modo() === 'SOLES' ? 'S/ ' + v : v + ' gal';
      caja.append($('<button type="button" class="btn btn-outline-secondary btn-sm g-chip"></button>').text(texto).attr('aria-label', 'Poner ' + texto).on('click', function () {
        $('#venta_valor').val(v).trigger('input').removeClass('is-invalid');
        $('#venta_valor_error').remove();
      }));
    });
  }

  G.post(RUTA_VEN + 'datos_formulario.php', {}, function (r) {
    surtidores = r.surtidores;
    tiposPago = r.tipos_pago;
    pintar_surtidores();
    pintar_pagos();
    G.opciones('#venta_cliente', r.clientes, 'id_cliente', function (c) { return c.nombre_completo + (c.dni_ruc ? ' · ' + c.dni_ruc : ''); }, 'Sin cliente');
    actualizar_pago();
    actualizar_calculo();
  });

  function actualizar_pago() {
    var tipo = tiposPago.filter(function (t) { return t.id_tipo_pago == elegido('venta_pago'); })[0];
    var requiere = !!tipo && tipo.requiere_codigo === 'SI';
    $('#grupo_codigo').toggle(requiere);
    $('#venta_codigo').prop('required', requiere);
    if (!requiere) { $('#venta_codigo').val('').removeClass('is-invalid'); $('#venta_codigo_error').remove(); }
  }

  function actualizar_calculo() {
    var s = surtidores.filter(function (x) { return x.id_surtidor == elegido('venta_surtidor'); })[0];
    var valor = Number($('#venta_valor').val()) || 0;
    var porSoles = modo() === 'SOLES';
    $('#venta_valor_etiqueta').text(porSoles ? 'Monto (S/)' : 'Galones');
    $('#venta_valor').attr('placeholder', porSoles ? '0.00' : '0.000');
    var boton = $('#venta_registrar span');
    if (!s || valor <= 0) {
      $('#venta_calculo').text(s ? '—' : 'elija un surtidor');
      boton.text('Registrar venta');
      return;
    }
    var precio = Number(s.precio_actual);
    var total = porSoles ? valor : valor * precio;
    $('#venta_calculo').text(porSoles ? G.galones(valor / precio) + ' gal' : G.soles(total));
    boton.text('Registrar venta · ' + G.soles(total));
  }

  function fecha_hora(texto) {
    var d = Fecha_Local(String(texto || '').replace(' ', 'T'));
    return isNaN(d) ? texto : d.toLocaleString('es-PE', { day: '2-digit', month: '2-digit', hour: '2-digit', minute: '2-digit' });
  }

  function listar() {
    var filtro = { desde: $('#venta_desde').val(), hasta: $('#venta_hasta').val() };
    var der = function (t) { return $('<span class="d-block text-right text-nowrap"></span>').text(t); };
    var columnas = [
      function (v) { return $('<span class="text-nowrap"></span>').text(fecha_hora(v.creado_en)); },
      function (v) { return 'M' + v.numero_maquina + ' ' + v.surtidor; },
      function (v) { return $('<span class="text-nowrap"></span>').append($('<span class="g-punto mr-1" aria-hidden="true"></span>').attr('data-combustible', combustible_de(v.producto)), document.createTextNode(v.producto)); },
      function (v) { return der(G.galones(v.galones)); },
      function (v) { return der(G.soles(v.total)).addClass('font-weight-bold'); },
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
      function (v) { return G.etiqueta(v.estado === 'ANULADA' ? 'Anulada' : 'Vigente', v.estado === 'ANULADA' ? 'badge-secondary' : 'badge-success'); }
    ];
    if (esAdmin) {
      columnas.push(function (v) {
        return v.estado === 'ANULADA' ? '' : G.boton('Anular', 'btn-outline-danger', function () {
          G.pedirMotivo('Anular la venta de ' + G.soles(v.total), function (motivo) {
            G.post(RUTA_VEN + 'anular_venta.php', { id_venta: v.id_venta, motivo: motivo }, function () { G.ok('Venta anulada'); listar(); });
          });
        }).attr('aria-label', 'Anular la venta de ' + G.soles(v.total) + ' del ' + fecha_hora(v.creado_en));
      });
    }
    G.post(RUTA_VEN + 'listar_ventas.php', filtro, function (r) {
      G.tabla('#cuerpo_ventas', r.data, columnas, 'Sin ventas en estas fechas.');
      var vigentes = (r.data || []).filter(function (v) { return v.estado !== 'ANULADA'; });
      var total = vigentes.reduce(function (s, v) { return s + Number(v.total); }, 0);
      var galones = vigentes.reduce(function (s, v) { return s + Number(v.galones); }, 0);
      $('#venta_resumen').empty().toggle(vigentes.length > 0).append(
        $('<strong></strong>').text(vigentes.length + (vigentes.length === 1 ? ' venta' : ' ventas')),
        document.createTextNode(' · ' + G.soles(total) + ' · ' + G.galones(galones) + ' gal')
      );
    });

    if (esAdmin) {
      G.post(RUTA_VEN + 'conciliacion.php', filtro, function (r) {
        G.tabla('#cuerpo_conciliacion', r.data, [
          function (f) { return Fecha_Local(f.fecha_reporte).toLocaleDateString('es-PE'); },
          function (f) { return f.numero_documento + ' · ' + f.turno; },
          function (f) { return f.grifero; },
          function (f) { return f.producto; },
          function (f) { return der(G.galones(f.galones_lecturas)); },
          function (f) { return der(G.galones(f.galones_ventas)); },
          function (f) { return $('<span class="d-block text-right"></span>').append(Math.abs(Number(f.diferencia)) < 0.001 ? G.etiqueta('Conciliado', 'badge-success') : G.etiqueta(G.galones(f.diferencia), 'badge-warning')); }
        ], 'No hay turnos cerrados en este periodo.');
      });
    }
  }

  $('#form_venta').on('change', 'input[name=venta_pago]', actualizar_pago);
  $('#form_venta').on('change', 'input[name=venta_modo]', function () { pintar_rapidos(); actualizar_calculo(); });
  $('#form_venta').on('change', 'input[name=venta_surtidor]', actualizar_calculo);
  $('#venta_valor').on('input change', actualizar_calculo);
  $('#filtro_ventas').on('submit', function (e) { e.preventDefault(); listar(); });

  $('#form_venta').on('submit', function (e) {
    e.preventDefault();
    var boton = $('#venta_registrar').prop('disabled', true);
    G.post(RUTA_VEN + 'registrar_venta.php', {
      id_surtidor: elegido('venta_surtidor'), modo: modo(), valor: $('#venta_valor').val(), id_tipo_pago: elegido('venta_pago'),
      codigo_operacion: $('#venta_codigo').val(), id_cliente: $('#venta_cliente').val(), placa: $('#venta_placa').val(),
      tipo_comprobante: elegido('venta_comprobante') || 'NINGUNO'
    }, function (r) {
      var aviso;
      if (r.comprobante && r.comprobante.estado === 'ACEPTADO') {
        aviso = G.ok(r.comprobante.tipo + ' ' + r.comprobante.serie + '-' + r.comprobante.numero + ' emitida');
      } else if (r.comprobante) {
        aviso = Swal.fire('Venta registrada', 'El comprobante aún no está confirmado. Se puede reenviar desde Comprobantes; la venta ya quedó guardada.', 'info');
      } else {
        aviso = G.ok('Venta registrada: ' + G.soles(r.total));
      }
      // El surtidor y el medio de pago se conservan: el grifero suele atender seguido en el mismo.
      $('#venta_valor, #venta_codigo, #venta_placa').val('');
      $('#venta_cliente').val('');
      $('#form_venta input[name=venta_comprobante][value=NINGUNO]').prop('checked', true);
      actualizar_calculo();
      listar();
      aviso.then(function () { $('#venta_valor').trigger('focus'); });
    }).always(function () { boton.prop('disabled', false); });
  });

  pintar_rapidos();
  listar();
}
