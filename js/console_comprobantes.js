var RUTA_CPE = '../controller/facturacion/controlador_';

function enlace_externo(texto, url) {
  // Solo enlaces web: una respuesta externa no debe poder inyectar otro tipo de dirección.
  if (!url || !/^https?:\/\//i.test(url)) { return null; }
  return $('<a target="_blank" rel="noopener noreferrer" class="mr-2"></a>').attr('href', url).text(texto);
}

function iniciar_comprobantes() {
  var ESTADOS = {
    PENDIENTE: ['Pendiente de envío', 'badge-warning'],
    ENVIADO: ['Por confirmar en SUNAT', 'badge-info'],
    ACEPTADO: ['Aceptado', 'badge-success'],
    RECHAZADO: ['Rechazado', 'badge-danger'],
    ANULACION_PENDIENTE: ['Anulación por confirmar', 'badge-info'],
    ANULADO: ['Anulado', 'badge-secondary'],
    CANCELADO: ['Cancelado sin enviar', 'badge-secondary']
  };
  var NOMBRES = { FACTURA: 'Factura', BOLETA: 'Boleta', NCREDITO: 'Nota de crédito', NDEBITO: 'Nota de débito' };
  var MOTIVOS = { NCREDITO: [], NDEBITO: [] };
  // Notas de crédito que van por todo lo que queda del comprobante (anulación, error en el RUC, devolución total).
  var NC_TOTALES = ['01', '02', '06'];
  var notaRef = null;

  $('#cpe_desde').val(G.inicioMes());
  $('#cpe_hasta').val(G.hoy());

  function resultado(r, que) {
    var e = ESTADOS[r.estado] || [r.estado];
    if (r.estado === 'ACEPTADO' || r.estado === 'ENVIADO') {
      return G.ok(que + ' ' + r.comprobante + ': ' + e[0].toLowerCase());
    }
    return Swal.fire({ icon: 'info', title: que + ' ' + r.comprobante + ' guardada', text: (r.ultimo_error ? r.ultimo_error + ' ' : '') + 'Quedó ' + e[0].toLowerCase() + '; se puede reintentar desde la lista con el mismo número.' });
  }

  function listar() {
    G.post(RUTA_CPE + 'listar_comprobantes.php', { desde: $('#cpe_desde').val(), hasta: $('#cpe_hasta').val(), estado: $('#cpe_estado').val() }, function (r) {
      G.tabla('#cuerpo_comprobantes', r.data, [
        function (c) { return $('<span class="text-nowrap"></span>').text(c.creado_en); },
        function (c) {
          var caja = $('<div></div>').append($('<span class="font-weight-bold text-nowrap"></span>').text(c.serie + '-' + c.numero), $('<div class="small text-muted"></div>').text(NOMBRES[c.tipo] || c.tipo));
          if (c.ref_serie) { caja.append($('<div class="small text-muted"></div>').text('Modifica ' + c.ref_serie + '-' + c.ref_numero)); }
          if (Number(c.vales) > 0) { caja.append($('<div class="small text-muted"></div>').text(c.vales + (Number(c.vales) === 1 ? ' vale' : ' vales'))); }
          return caja;
        },
        function (c) { return c.cliente_denominacion + (c.cliente_documento !== '-' ? ' · ' + c.cliente_documento : ''); },
        function (c) {
          var caja = $('<div class="text-right text-nowrap"></div>').text(G.soles(c.total));
          if ((c.tipo === 'FACTURA' || c.tipo === 'BOLETA') && Number(c.restante) < Number(c.total) - 0.005) {
            caja.append($('<div class="small text-muted"></div>').text('Queda ' + G.soles(c.restante)));
          }
          return caja;
        },
        function (c) { var e = ESTADOS[c.estado] || [c.estado, 'badge-secondary']; return G.etiqueta(e[0], e[1]); },
        function (c) { return $('<span style="overflow-wrap:anywhere"></span>').text(c.ultimo_error || c.motivo_nota || c.sunat_descripcion || c.motivo_anulacion || ''); },
        function (c) {
          return G.grupo(enlace_externo('Ver', c.enlace), enlace_externo('PDF', c.enlace_pdf), enlace_externo('XML', c.enlace_xml), enlace_externo('CDR', c.enlace_cdr));
        },
        function (c) {
          var reintentable = c.estado === 'PENDIENTE' || c.estado === 'RECHAZADO';
          var enviado = c.estado === 'ACEPTADO' || c.estado === 'ENVIADO';
          var admiteNota = enviado && (c.tipo === 'FACTURA' || c.tipo === 'BOLETA');
          return G.grupo(
            reintentable ? G.boton('Reintentar', 'btn-primary', function () {
              G.post(RUTA_CPE + 'reintentar.php', { id_comprobante: c.id_comprobante }, listar);
            }) : null,
            admiteNota ? G.boton('Nota', 'btn-outline-secondary', function () { abrir_nota(c); }).attr('aria-label', 'Emitir una nota sobre ' + c.serie + '-' + c.numero) : null,
            enviado ? G.boton('Anular', 'btn-outline-danger', function () {
              G.pedirMotivo('Anular ' + c.serie + '-' + c.numero, function (motivo) {
                G.post(RUTA_CPE + 'anular_comprobante.php', { id_comprobante: c.id_comprobante, motivo: motivo }, function () { G.ok('Anulación enviada'); listar(); });
              });
            }) : null
          );
        }
      ], 'No hay comprobantes en este periodo.');
    });
  }

  // ---------- Notas de crédito y débito ----------

  function pintar_motivos() {
    var clase = $('input[name=nota_clase]:checked').val();
    G.opciones('#nota_motivo', MOTIVOS[clase], 'codigo', function (m) { return m.codigo + ' · ' + m.nombre; }, 'Elija el motivo');
    $('#nota_ayuda_clase').text(clase === 'NCREDITO' ? 'Resta del comprobante: anulación, devolución, descuento o corrección.' : 'Suma al comprobante: intereses por mora, aumento en el valor o penalidades.');
    actualizar_monto();
  }

  function actualizar_monto() {
    var clase = $('input[name=nota_clase]:checked').val();
    var total = clase === 'NCREDITO' && NC_TOTALES.indexOf($('#nota_motivo').val()) !== -1;
    $('#nota_grupo_monto').toggleClass('d-none', total);
    $('#nota_monto_total').toggleClass('d-none', !total).text(total && notaRef ? 'Monto: ' + G.soles(notaRef.restante) + ', todo lo que queda del comprobante.' : '');
    $('#nota_monto').attr('max', clase === 'NCREDITO' && notaRef ? notaRef.restante : null);
  }

  function abrir_nota(c) {
    notaRef = c;
    $('#nota_info').empty().append(
      $('<strong></strong>').text((NOMBRES[c.tipo] || c.tipo) + ' ' + c.serie + '-' + c.numero),
      document.createTextNode(' · ' + c.cliente_denominacion + ' · total ' + G.soles(c.total) + (Number(c.restante) < Number(c.total) - 0.005 ? ', queda ' + G.soles(c.restante) : ''))
    );
    $('input[name=nota_clase][value=NCREDITO]').prop('checked', true);
    $('#nota_sustento, #nota_monto').val('');
    pintar_motivos();
    $('#modal_nota').modal('show');
  }

  $('input[name=nota_clase]').on('change', pintar_motivos);
  $('#nota_motivo').on('change', actualizar_monto);
  $('#nota_emitir').on('click', function () {
    var boton = $(this).prop('disabled', true);
    G.post(RUTA_CPE + 'crear_nota.php', {
      id_referencia: notaRef.id_comprobante, clase: $('input[name=nota_clase]:checked').val(), motivo: $('#nota_motivo').val(),
      sustento: $('#nota_sustento').val(), monto: $('#nota_monto').val() || 0
    }, function (r) {
      $('#modal_nota').modal('hide');
      resultado(r, $('input[name=nota_clase]:checked').val() === 'NCREDITO' ? 'Nota de crédito' : 'Nota de débito');
      listar();
    }).always(function () { boton.prop('disabled', false); });
  });

  // ---------- Facturar vales ----------

  function cargar_vales() {
    var cliente = $('#val_cliente').val();
    $('#val_todos').prop('checked', false);
    if (!cliente) { G.tabla('#cuerpo_vales', [], [function () { return ''; }], 'Elija un cliente.'); total_vales(); return; }
    G.post(RUTA_CPE + 'vales_cliente.php', { id_cliente: cliente, desde: $('#val_desde').val(), hasta: $('#val_hasta').val() }, function (r) {
      G.tabla('#cuerpo_vales', r.data, [
        function (v) { return $('<input type="checkbox" class="val_marca">').val(v.id_credito).attr('data-monto', v.monto).attr('aria-label', 'Incluir el vale ' + v.numero_vale); },
        function (v) { return Fecha_Local(String(v.created_at).slice(0, 10)).toLocaleDateString('es-PE'); },
        function (v) { return v.numero_vale; },
        function (v) { return v.placa || '—'; },
        function (v) { return v.turno || '—'; },
        function (v) { return $('<span class="d-block text-right text-nowrap"></span>').text(G.soles(v.monto)); }
      ], 'Este cliente no tiene vales por facturar en esas fechas.');
      total_vales();
    });
  }

  function total_vales() {
    var marcados = $('.val_marca:checked');
    var total = 0;
    marcados.each(function () { total += Number($(this).attr('data-monto')) || 0; });
    $('#val_total').text(G.soles(total));
    $('#val_emitir').prop('disabled', !marcados.length).text(marcados.length ? 'Emitir comprobante por ' + marcados.length + (marcados.length === 1 ? ' vale' : ' vales') : 'Emitir comprobante');
  }

  $('#btn_facturar_vales').on('click', function () {
    $('#val_desde').val(G.inicioMes());
    $('#val_hasta').val(G.hoy());
    G.post(RUTA_CPE + 'datos_notas.php', {}, function (r) {
      MOTIVOS.NCREDITO = r.motivos_nc; MOTIVOS.NDEBITO = r.motivos_nd;
      G.opciones('#val_cliente', r.clientes, 'id_cliente', function (cl) { return cl.nombre_completo + (cl.dni_ruc ? ' · ' + cl.dni_ruc : '') + ' (' + cl.vales + (Number(cl.vales) === 1 ? ' vale)' : ' vales)'); }, r.clientes.length ? 'Elija un cliente' : 'No hay vales por facturar');
      cargar_vales();
      $('#modal_vales').modal('show');
    });
  });
  $('#val_cliente, #val_desde, #val_hasta').on('change', cargar_vales);
  $('#val_todos').on('change', function () { $('.val_marca').prop('checked', this.checked); total_vales(); });
  $('#cuerpo_vales').on('change', '.val_marca', total_vales);
  $('#val_emitir').on('click', function () {
    var boton = $(this).prop('disabled', true);
    G.post(RUTA_CPE + 'facturar_vales.php', {
      id_cliente: $('#val_cliente').val(), tipo: $('input[name=val_tipo]:checked').val(),
      vales: $('.val_marca:checked').map(function () { return this.value; }).get().join(',')
    }, function (r) {
      $('#modal_vales').modal('hide');
      resultado(r, $('input[name=val_tipo]:checked').val() === 'FACTURA' ? 'Factura' : 'Boleta');
      listar();
    }).always(function () { total_vales(); });
  });

  // Motivos de las notas al abrir la pantalla, para la ventana de notas.
  G.post(RUTA_CPE + 'datos_notas.php', {}, function (r) { MOTIVOS.NCREDITO = r.motivos_nc; MOTIVOS.NDEBITO = r.motivos_nd; });

  $('#filtro_comprobantes').on('submit', function (e) { e.preventDefault(); listar(); });
  $('#btn_procesar_cola').on('click', function () {
    G.post(RUTA_CPE + 'procesar_cola.php', {}, function (r) {
      G.ok('Enviados: ' + r.enviados + '. Actualizados: ' + r.actualizados + '.');
      listar();
    });
  });

  listar();
}
