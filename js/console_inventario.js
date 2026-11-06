var RUTA_INV = '../controller/inventario/controlador_';

function iniciar_tanques() {
  $('#kardex_desde').val(G.inicioMes());
  $('#kardex_hasta').val(G.hoy());

  $.post('../controller/productos/controlador_productos_activos.php', function (r) {
    var productos = Array.isArray(r) ? r : (r.data || []);
    G.opciones('#tanque_producto', productos, 'id_producto', function (p) { return p.nombre; }, 'Seleccione');
  }, 'json');

  cargar_tanques();
  cargar_asignaciones();

  $('#btn_nuevo_tanque').on('click', function () { abrir_tanque(null); });

  $('#form_tanque').on('submit', function (e) {
    e.preventDefault();
    G.post(RUTA_INV + 'guardar_tanque.php', {
      id_tanque: $('#tanque_id').val(), nombre: $('#tanque_nombre').val(), id_producto: $('#tanque_producto').val(),
      capacidad: $('#tanque_capacidad').val(), stock_minimo: $('#tanque_minimo').val(), stock_inicial: $('#tanque_inicial').val()
    }, function () {
      $('#modal_tanque').modal('hide');
      G.ok('Tanque guardado');
      cargar_tanques();
      cargar_asignaciones();
    });
  });

  $('#form_kardex').on('submit', function (e) {
    e.preventDefault();
    G.post(RUTA_INV + 'kardex.php', { id_tanque: $('#kardex_tanque').val(), desde: $('#kardex_desde').val(), hasta: $('#kardex_hasta').val() }, function (r) {
      G.tabla('#cuerpo_kardex', r.data, [
        function (m) { return m.creado_en; },
        function (m) { return m.tipo.replace(/_/g, ' '); },
        function (m) { return G.galones(m.galones); },
        function (m) { return G.galones(m.saldo); },
        function (m) { return m.turno || m.compra || m.observaciones || ''; },
        function (m) { return m.usuario; }
      ]);
    });
  });
}

function abrir_tanque(t) {
  $('#titulo_tanque').text(t ? 'Editar tanque' : 'Nuevo tanque');
  $('#tanque_id').val(t ? t.id_tanque : '');
  $('#tanque_nombre').val(t ? t.nombre : '');
  $('#tanque_producto').val(t ? t.id_producto : '').prop('disabled', !!t);
  $('#tanque_capacidad').val(t ? t.capacidad_galones : '');
  $('#tanque_minimo').val(t ? t.stock_minimo : 0);
  $('#tanque_inicial').val(0);
  $('#grupo_stock_inicial').toggle(!t);
  $('#modal_tanque').modal('show');
}

function cargar_tanques() {
  G.post(RUTA_INV + 'listar_tanques.php', {}, function (r) {
    G.tabla('#cuerpo_tanques', r.data, [
      function (t) { return t.nombre; },
      function (t) { return t.producto; },
      function (t) { return G.galones(t.capacidad_galones); },
      function (t) { return G.galones(t.stock_minimo); },
      function (t) {
        var caja = $('<span></span>').text(G.galones(t.stock_actual) + ' ');
        return t.stock_bajo == 1 ? caja.append(G.etiqueta('Stock bajo', 'badge-danger')) : caja;
      },
      function (t) { return G.etiqueta(t.estado, t.estado === 'ACTIVO' ? 'badge-success' : 'badge-secondary'); },
      function (t) {
        var nuevo = t.estado === 'ACTIVO' ? 'INACTIVO' : 'ACTIVO';
        return G.grupo(
          G.boton('Editar', 'btn-primary', function () { abrir_tanque(t); }),
          G.boton(nuevo === 'ACTIVO' ? 'Activar' : 'Desactivar', 'btn-outline-secondary', function () {
            G.post(RUTA_INV + 'cambiar_estado_tanque.php', { id_tanque: t.id_tanque, estado: nuevo }, cargar_tanques);
          })
        );
      }
    ], 'Aún no hay tanques. Crea el primero con «Nuevo tanque».');

    var tarjetas = $('#tarjetas_tanques').empty();
    r.data.filter(function (t) { return t.estado === 'ACTIVO'; }).forEach(function (t) {
      var porcentaje = Math.max(0, Math.min(100, Number(t.porcentaje) || 0));
      var color = t.stock_bajo == 1 ? 'bg-danger' : 'bg-success';
      var caja = $('<div class="info-box"></div>').append($('<div class="info-box-content"></div>')
        .append($('<span class="info-box-text"></span>').text(t.nombre + ' · ' + (t.producto || '')))
        .append($('<span class="info-box-number"></span>').text(G.galones(t.stock_actual) + ' gal'))
        .append($('<div class="progress"></div>').append($('<div class="progress-bar"></div>').addClass(color).css('width', porcentaje + '%')))
        .append($('<span class="progress-description"></span>').text(porcentaje + ' % de la capacidad')));
      tarjetas.append($('<div class="col-md-4"></div>').append(caja));
    });

    G.opciones('#kardex_tanque', r.data, 'id_tanque', function (t) { return t.nombre; });
    window.tanques_cargados = r.data;
  });
}

function cargar_asignaciones() {
  $.when(
    $.post(RUTA_INV + 'listar_asignaciones.php', {}, null, 'json'),
    $.post(RUTA_INV + 'listar_tanques.php', { solo_activos: 1 }, null, 'json')
  ).done(function (a, t) {
    var tanques = t[0].data || [];
    G.tabla('#cuerpo_asignacion', a[0].data, [
      function (s) { return s.numero_maquina; },
      function (s) { return s.codigo; },
      function (s) { return s.producto; },
      function (s) {
        var select = $('<select class="form-control form-control-sm"></select>').attr('aria-label', 'Tanque de ' + s.codigo);
        select.append($('<option value="0"></option>').text('Automático'));
        tanques.filter(function (x) { return x.id_producto == s.id_producto; }).forEach(function (x) {
          select.append($('<option></option>').attr('value', x.id_tanque).text(x.nombre));
        });
        select.val(s.id_tanque || 0);
        select.on('change', function () {
          G.post(RUTA_INV + 'asignar_surtidor.php', { id_surtidor: s.id_surtidor, id_tanque: this.value }, function () { G.ok('Asignación guardada'); });
        });
        return select;
      }
    ]);
  });
}

function iniciar_varillaje(esAdmin) {
  $('#var_fecha').val(G.hoy());
  $('#var_desde').val(G.inicioMes());
  $('#var_hasta').val(G.hoy());

  G.post(RUTA_INV + 'listar_tanques.php', { solo_activos: 1 }, function (r) {
    G.opciones('#var_tanque', r.data, 'id_tanque', function (t) { return t.nombre + ' · ' + (t.producto || ''); }, 'Seleccione');
  });

  function listar() {
    G.post(RUTA_INV + 'listar_varillajes.php', { desde: $('#var_desde').val(), hasta: $('#var_hasta').val() }, function (r) {
      G.tabla('#cuerpo_varillajes', r.data, [
        function (v) { return v.fecha; },
        function (v) { return v.tanque; },
        function (v) { return G.galones(v.galones_fisicos); },
        function (v) { return G.galones(v.galones_teoricos); },
        function (v) { return G.galones(v.diferencia); },
        function (v) { return v.usuario; },
        function (v) { return v.observaciones; },
        function (v) {
          if (v.ajuste_aplicado == 1) { return G.etiqueta('Ajustado', 'badge-info'); }
          if (!esAdmin || Number(v.diferencia) === 0) { return ''; }
          return G.boton('Ajustar stock', 'btn-warning', function () {
            Swal.fire({ title: '¿Ajustar el stock?', text: 'Se aplicará una diferencia de ' + G.galones(v.diferencia) + ' galones al tanque.', icon: 'question', showCancelButton: true, confirmButtonText: 'Ajustar', cancelButtonText: 'Cancelar' })
              .then(function (c) {
                if (c.isConfirmed) {
                  G.post(RUTA_INV + 'aplicar_ajuste.php', { id_varillaje: v.id_varillaje }, function () { G.ok('Stock ajustado'); listar(); });
                }
              });
          });
        }
      ]);
    });
  }
  listar();

  $('#filtro_varillaje').on('submit', function (e) { e.preventDefault(); listar(); });

  $('#form_varillaje').on('submit', function (e) {
    e.preventDefault();
    G.post(RUTA_INV + 'registrar_varillaje.php', {
      id_tanque: $('#var_tanque').val(), fecha: $('#var_fecha').val(), medida_cm: $('#var_medida').val(),
      galones_fisicos: $('#var_galones').val(), observaciones: $('#var_obs').val()
    }, function (r) {
      $('#var_medida, #var_galones, #var_obs').val('');
      var texto = 'Teórico: ' + G.galones(r.teoricos) + ' gal. Diferencia: ' + G.galones(r.diferencia) + ' gal.';
      Swal.fire(r.fuera_de_tolerancia ? 'Diferencia fuera de tolerancia' : 'Medición registrada', texto, r.fuera_de_tolerancia ? 'warning' : 'success');
      listar();
    });
  });
}
