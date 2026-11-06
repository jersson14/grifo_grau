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

  $('#cpe_desde').val(G.inicioMes());
  $('#cpe_hasta').val(G.hoy());

  function listar() {
    G.post(RUTA_CPE + 'listar_comprobantes.php', { desde: $('#cpe_desde').val(), hasta: $('#cpe_hasta').val(), estado: $('#cpe_estado').val() }, function (r) {
      G.tabla('#cuerpo_comprobantes', r.data, [
        function (c) { return c.creado_en; },
        function (c) { return c.tipo + ' ' + c.serie + '-' + c.numero; },
        function (c) { return c.cliente_denominacion + (c.cliente_documento !== '-' ? ' · ' + c.cliente_documento : ''); },
        function (c) { return G.soles(c.total); },
        function (c) { var e = ESTADOS[c.estado] || [c.estado, 'badge-secondary']; return G.etiqueta(e[0], e[1]); },
        function (c) { return $('<span style="overflow-wrap:anywhere"></span>').text(c.ultimo_error || c.sunat_descripcion || c.motivo_anulacion || ''); },
        function (c) {
          return G.grupo(enlace_externo('Ver', c.enlace), enlace_externo('PDF', c.enlace_pdf), enlace_externo('XML', c.enlace_xml), enlace_externo('CDR', c.enlace_cdr));
        },
        function (c) {
          var reintentable = c.estado === 'PENDIENTE' || c.estado === 'RECHAZADO';
          var anulable = c.estado === 'ACEPTADO' || c.estado === 'ENVIADO';
          return G.grupo(
            reintentable ? G.boton('Reintentar', 'btn-primary', function () {
              G.post(RUTA_CPE + 'reintentar.php', { id_comprobante: c.id_comprobante }, listar);
            }) : null,
            anulable ? G.boton('Anular', 'btn-outline-danger', function () {
              G.pedirMotivo('Anular ' + c.serie + '-' + c.numero, function (motivo) {
                G.post(RUTA_CPE + 'anular_comprobante.php', { id_comprobante: c.id_comprobante, motivo: motivo }, function () { G.ok('Anulación enviada'); listar(); });
              });
            }) : null
          );
        }
      ], 'No hay comprobantes en este periodo.');
    });
  }

  $('#filtro_comprobantes').on('submit', function (e) { e.preventDefault(); listar(); });
  $('#btn_procesar_cola').on('click', function () {
    G.post(RUTA_CPE + 'procesar_cola.php', {}, function (r) {
      G.ok('Enviados: ' + r.enviados + '. Actualizados: ' + r.actualizados + '.');
      listar();
    });
  });

  listar();
}
