var RUTA_CAJA = '../controller/caja/controlador_';
var DENOMINACIONES = ['200', '100', '50', '20', '10', '5', '2', '1', '0.5', '0.2', '0.1'];

function iniciar_caja() {
  $('#caja_desde').val(G.inicioMes());
  $('#caja_hasta').val(G.hoy());
  $('#dep_fecha').val(G.hoy());

  var contenedor = $('#arqueo_denominaciones');
  DENOMINACIONES.forEach(function (d) {
    var id = 'den_' + d.replace('.', '_');
    var moneda = Number(d) < 10 ? 'Moneda' : 'Billete';
    contenedor.append($('<div class="col-6 col-md-3 form-group"></div>')
      .append($('<label></label>').attr('for', id).text(moneda + ' S/ ' + Number(d).toFixed(Number(d) < 1 ? 2 : 0)))
      .append($('<input type="number" min="0" step="1" class="form-control form-control-sm den_cantidad">').attr('id', id).attr('data-den', d)));
  });
  $('.den_cantidad').on('input', calcular_arqueo);

  listar_caja();

  $('#filtro_caja').on('submit', function (e) { e.preventDefault(); listar_caja(); });

  $('#form_arqueo').on('submit', function (e) {
    e.preventDefault();
    var cantidades = {};
    $('.den_cantidad').each(function () { cantidades[$(this).attr('data-den')] = this.value === '' ? 0 : this.value; });
    G.post(RUTA_CAJA + 'registrar_arqueo.php', { id_reporte: $('#arqueo_reporte').val(), cantidades: JSON.stringify(cantidades), observaciones: $('#arqueo_obs').val() }, function (r) {
      $('#modal_arqueo').modal('hide');
      if (r.dentro_tolerancia) {
        G.ok('Arqueo guardado');
      } else {
        Swal.fire('Diferencia fuera de tolerancia', 'Contado: ' + G.soles(r.contado) + '. Esperado: ' + G.soles(r.esperado) + '. Diferencia: ' + G.soles(r.diferencia) + '.', 'warning');
      }
      listar_caja();
    });
  });

  $('#form_deposito').on('submit', function (e) {
    e.preventDefault();
    G.post(RUTA_CAJA + 'registrar_deposito.php', { fecha: $('#dep_fecha').val(), banco: $('#dep_banco').val(), numero_operacion: $('#dep_operacion').val(), monto: $('#dep_monto').val(), observaciones: $('#dep_obs').val() }, function () {
      $('#dep_operacion, #dep_monto, #dep_obs').val('');
      G.ok('Depósito registrado');
      listar_caja();
    });
  });
}

function filtro_caja() { return { desde: $('#caja_desde').val(), hasta: $('#caja_hasta').val() }; }

function listar_caja() {
  G.post(RUTA_CAJA + 'listar_turnos.php', filtro_caja(), function (r) {
    G.tabla('#cuerpo_arqueos', r.data, [
      function (t) { return t.fecha_reporte; },
      function (t) { return t.numero_documento + ' · ' + t.turno; },
      function (t) { return t.grifero; },
      function (t) { return G.soles(t.efectivo_esperado); },
      function (t) { return G.soles(t.efectivo_declarado); },
      function (t) { return t.id_arqueo ? G.soles(t.efectivo_contado) : ''; },
      function (t) {
        if (!t.id_arqueo) { return ''; }
        var d = Number(t.diferencia);
        return G.etiqueta(G.soles(d), d < 0 ? 'badge-danger' : (d > 0 ? 'badge-info' : 'badge-success'));
      },
      function (t) {
        if (!t.id_arqueo) { return G.etiqueta('Sin arqueo', 'badge-warning'); }
        if (Number(t.diferencia) === 0) { return G.etiqueta('Cuadrado', 'badge-success'); }
        return t.resolucion === 'PENDIENTE' ? G.etiqueta('Por resolver', 'badge-danger') : G.etiqueta(t.resolucion, 'badge-secondary');
      },
      function (t) {
        var resuelto = t.id_arqueo && t.resolucion !== 'PENDIENTE';
        return G.grupo(
          resuelto ? null : G.boton(t.id_arqueo ? 'Corregir' : 'Arquear', 'btn-primary', function () { abrir_arqueo(t); }),
          t.id_arqueo && t.resolucion === 'PENDIENTE' && Number(t.diferencia) !== 0 ? G.boton('Resolver', 'btn-warning', function () { resolver_diferencia(t); }) : null,
          G.boton('Medios', 'btn-outline-info', function () { ver_medios(t); })
        );
      }
    ], 'No hay turnos cerrados en este periodo.');
  });

  G.post(RUTA_CAJA + 'listar_depositos.php', filtro_caja(), function (r) {
    G.tabla('#cuerpo_depositos', r.data, [
      function (d) { return d.fecha; },
      function (d) { return d.banco; },
      function (d) { return d.numero_operacion; },
      function (d) { return G.soles(d.monto); },
      function (d) { return d.usuario; },
      function (d) { return G.etiqueta(d.estado, d.estado === 'ANULADO' ? 'badge-secondary' : 'badge-success'); },
      function (d) {
        return d.estado === 'ANULADO' ? '' : G.boton('Anular', 'btn-outline-danger', function () {
          G.pedirMotivo('Anular el depósito ' + d.numero_operacion, function (motivo) {
            G.post(RUTA_CAJA + 'anular_deposito.php', { id_deposito: d.id_deposito, motivo: motivo }, function () { G.ok('Depósito anulado'); listar_caja(); });
          });
        });
      }
    ], 'No hay depósitos en este periodo.');
  });

  G.post(RUTA_CAJA + 'resumen.php', filtro_caja(), function (r) {
    var tarjeta = function (titulo, valor, ayuda) {
      return $('<div class="col-md-4"></div>').append($('<div class="info-box"></div>').append($('<div class="info-box-content"></div>')
        .append($('<span class="info-box-text"></span>').text(titulo))
        .append($('<span class="info-box-number"></span>').text(G.soles(valor)))
        .append($('<span class="text-muted small"></span>').text(ayuda))));
    };
    $('#caja_tarjetas').empty()
      .append(tarjeta('Efectivo contado', r.contado, 'En el periodo'))
      .append(tarjeta('Depositado', r.depositado, 'En el periodo'))
      .append(tarjeta('Saldo en caja', r.saldo_caja, 'Todo lo contado menos todo lo depositado'));
    G.tabla('#cuerpo_griferos', r.griferos, [
      function (g) { return g.grifero; },
      function (g) { return g.arqueos; },
      function (g) { return G.soles(g.faltantes); },
      function (g) { return G.soles(g.sobrantes); },
      function (g) { return G.soles(g.pendiente); }
    ], 'Sin arqueos en este periodo.');
  });
}

function abrir_arqueo(t) {
  $('#titulo_arqueo').text('Arqueo del turno ' + t.numero_documento + ' · ' + t.grifero);
  $('#arqueo_reporte').val(t.id_reporte);
  $('#arqueo_obs').val(t.observaciones || '');
  $('#arqueo_esperado').text(G.soles(t.efectivo_esperado)).data('valor', Number(t.efectivo_esperado));
  var previas = t.denominaciones ? JSON.parse(t.denominaciones) : {};
  $('.den_cantidad').each(function () {
    var v = previas[$(this).attr('data-den')];
    this.value = v ? v : '';
  });
  calcular_arqueo();
  $('#modal_arqueo').modal('show');
}

function calcular_arqueo() {
  var centimos = 0;
  $('.den_cantidad').each(function () {
    centimos += Math.round(Number($(this).attr('data-den')) * 100) * (parseInt(this.value, 10) || 0);
  });
  var contado = centimos / 100;
  var diferencia = Math.round((contado - $('#arqueo_esperado').data('valor')) * 100) / 100;
  $('#arqueo_contado').text(G.soles(contado));
  $('#arqueo_diferencia').text(G.soles(diferencia) + (diferencia < 0 ? ' (faltante)' : (diferencia > 0 ? ' (sobrante)' : '')))
    .toggleClass('text-danger', diferencia < 0).toggleClass('text-success', diferencia >= 0);
}

function resolver_diferencia(t) {
  Swal.fire({
    title: 'Resolver diferencia de ' + G.soles(t.diferencia),
    input: 'select',
    inputOptions: { DESCONTADO: 'Se descuenta al grifero', CONDONADO: 'Se condona', JUSTIFICADO: 'Está justificada' },
    showCancelButton: true, confirmButtonText: 'Continuar', cancelButtonText: 'Cancelar'
  }).then(function (r) {
    if (!r.isConfirmed) { return; }
    G.pedirMotivo('Explica la decisión', function (nota) {
      G.post(RUTA_CAJA + 'resolver_diferencia.php', { id_arqueo: t.id_arqueo, resolucion: r.value, nota: nota }, function () { G.ok('Diferencia resuelta'); listar_caja(); });
    });
  });
}

function ver_medios(t) {
  G.post(RUTA_CAJA + 'comparar_medios.php', { id_reporte: t.id_reporte }, function (r) {
    var tabla = $('<table class="table table-sm text-left"><thead><tr><th>Medio</th><th>Declarado al cerrar</th><th>Ventas registradas</th></tr></thead></table>');
    var cuerpo = $('<tbody></tbody>').appendTo(tabla);
    r.data.forEach(function (m) {
      cuerpo.append($('<tr></tr>').append($('<td></td>').text(m.medio)).append($('<td></td>').text(G.soles(m.declarado))).append($('<td></td>').text(G.soles(m.ventas))));
    });
    Swal.fire({ title: 'Turno ' + t.numero_documento, html: tabla[0], width: 620 });
  });
}
