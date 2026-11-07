var RUTA_PER = '../controller/personal/controlador_';
var PER = { personal: [], turnos: {}, estados: [], tipos: {}, lunes: null };
var PER_DIAS = ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'];

function iniciar_personal() {
  var mes = G.hoy().slice(0, 7);
  $('#mov_mes, #res_mes').val(mes);
  $('#mov_fecha').val(G.hoy());
  $('#asi_fecha').val(G.hoy()).attr('max', G.hoy());
  PER.lunes = lunes_de(G.hoy());

  $('#form_personal').on('submit', function (e) { e.preventDefault(); guardar_personal(); });
  $('#per_cancelar').on('click', limpiar_personal);
  $('#hor_anterior').on('click', function () { PER.lunes = sumar_dias(PER.lunes, -7); cargar_horario(); });
  $('#hor_siguiente').on('click', function () { PER.lunes = sumar_dias(PER.lunes, 7); cargar_horario(); });
  $('#hor_hoy').on('click', function () { PER.lunes = lunes_de(G.hoy()); cargar_horario(); });
  $('#hor_copiar').on('click', copiar_semana);
  $('#asi_fecha').on('change', cargar_asistencia);
  $('#mov_mes').on('change', cargar_movimientos);
  $('#res_mes').on('change', cargar_resumen_personal);
  $('#form_movimiento').on('submit', function (e) { e.preventDefault(); registrar_movimiento(); });

  $('a[href="#tab_per_horario"]').on('shown.bs.tab', cargar_horario);
  $('a[href="#tab_per_asistencia"]').on('shown.bs.tab', cargar_asistencia);
  $('a[href="#tab_per_movimientos"]').on('shown.bs.tab', cargar_movimientos);
  $('a[href="#tab_per_resumen"]').on('shown.bs.tab', cargar_resumen_personal);

  cargar_personal();
  cargar_movimientos();
}

// ---------- Fechas ----------

function lunes_de(iso) {
  var d = Fecha_Local(iso);
  d.setDate(d.getDate() - ((d.getDay() + 6) % 7));
  return Fecha_ISO_Local(d);
}

function sumar_dias(iso, n) {
  var d = Fecha_Local(iso);
  d.setDate(d.getDate() + n);
  return Fecha_ISO_Local(d);
}

function fecha_corta(iso) {
  return Fecha_Local(iso).toLocaleDateString('es-PE', { day: '2-digit', month: '2-digit' });
}

// ---------- Fichas ----------

function cargar_personal() {
  G.post(RUTA_PER + 'datos.php', { id_personal: $('#per_id').val() || '' }, function (r) {
    PER.personal = r.personal;
    PER.turnos = {}; r.turnos.forEach(function (t) { PER.turnos[t.codigo] = t.nombre; });
    PER.estados = r.estados;
    PER.tipos = {}; r.tipos.forEach(function (t) { PER.tipos[t.codigo] = t.nombre; });

    var cargo = $('#per_cargo').val();
    G.opciones('#per_cargo', r.cargos.map(function (c) { return { c: c }; }), 'c', function (x) { return x.c; });
    if (cargo) { $('#per_cargo').val(cargo); }
    var usuario = $('#per_usuario').val();
    G.opciones('#per_usuario', r.usuarios, 'id_usuario', function (u) { return u.nombre + ' (' + u.usu_usuario + ')'; }, 'Sin usuario');
    $('#per_usuario').val(usuario || '');
    if (!$('#mov_tipo option').length) { G.opciones('#mov_tipo', r.tipos, 'codigo', function (t) { return t.nombre; }); }
    var persona = $('#mov_persona').val();
    G.opciones('#mov_persona', r.personal.filter(function (p) { return Number(p.activo); }), 'id_personal', function (p) { return p.nombres; }, 'Elija a la persona');
    $('#mov_persona').val(persona || '');

    G.tabla('#cuerpo_personal', r.personal, [
      function (p) { return p.nombres; },
      function (p) { return p.cargo; },
      function (p) { return p.dni || '—'; },
      function (p) { return p.telefono || '—'; },
      function (p) { return p.usuario || '—'; },
      function (p) { return $('<span class="d-block text-right text-nowrap"></span>').text(p.sueldo !== null ? G.soles(p.sueldo) : '—'); },
      function (p) { return G.etiqueta(Number(p.activo) ? 'Activo' : 'Cesado', Number(p.activo) ? 'badge-success' : 'badge-secondary'); },
      function (p) { return G.boton('Editar', 'btn-outline-primary', function () { editar_personal(p); }).attr('aria-label', 'Editar a ' + p.nombres); }
    ], 'Aún no hay personal registrado. Agregue a la primera persona con el formulario.');
  });
}

function guardar_personal() {
  var id = $('#per_id').val();
  G.post(RUTA_PER + 'guardar.php', {
    id_personal: id, nombres: $('#per_nombres').val(), dni: $('#per_dni').val(), cargo: $('#per_cargo').val(), telefono: $('#per_telefono').val(),
    fecha_ingreso: $('#per_ingreso').val(), sueldo: $('#per_sueldo').val(), id_usuario: $('#per_usuario').val(), activo: $('#per_activo').val(), observaciones: $('#per_obs').val()
  }, function () {
    G.ok(id ? 'Datos actualizados' : 'Persona agregada');
    limpiar_personal();
  });
}

function editar_personal(p) {
  $('#per_id').val(p.id_personal);
  // Los usuarios libres dependen de la ficha: se recargan para que el usuario vinculado aparezca en la lista.
  G.post(RUTA_PER + 'datos.php', { id_personal: p.id_personal }, function (r) {
    G.opciones('#per_usuario', r.usuarios, 'id_usuario', function (u) { return u.nombre + ' (' + u.usu_usuario + ')'; }, 'Sin usuario');
    $('#per_nombres').val(p.nombres); $('#per_dni').val(p.dni || ''); $('#per_cargo').val(p.cargo); $('#per_telefono').val(p.telefono || '');
    $('#per_ingreso').val(p.fecha_ingreso || ''); $('#per_sueldo').val(p.sueldo !== null ? p.sueldo : ''); $('#per_usuario').val(p.id_usuario || '');
    $('#per_activo').val(Number(p.activo) ? '1' : '0'); $('#per_obs').val(p.observaciones || '');
    $('#per_guardar').text('Guardar cambios'); $('#per_cancelar').removeClass('d-none');
    $('#per_nombres').trigger('focus');
  });
}

function limpiar_personal() {
  $('#form_personal')[0].reset();
  $('#per_id').val('');
  $('#per_guardar').html('<i class="fas fa-plus" aria-hidden="true"></i> Agregar persona');
  $('#per_cancelar').addClass('d-none');
  cargar_personal();
}

// ---------- Horario semanal ----------

function cargar_horario() {
  G.post(RUTA_PER + 'horario.php', { fecha: PER.lunes }, function (r) {
    PER.lunes = r.dias[0];
    $('#hor_titulo').text('Semana del ' + Fecha_Local(r.dias[0]).toLocaleDateString('es-PE', { day: 'numeric', month: 'long' }) + ' al ' + Fecha_Local(r.dias[6]).toLocaleDateString('es-PE', { day: 'numeric', month: 'long', year: 'numeric' }));
    var cabecera = $('#cabecera_horario').empty().append('<th>Persona</th>');
    r.dias.forEach(function (d, i) {
      cabecera.append($('<th class="text-center"></th>').toggleClass('table-primary', d === G.hoy()).append(document.createTextNode(PER_DIAS[i] + ' '), $('<span class="text-muted font-weight-normal"></span>').text(fecha_corta(d))));
    });
    var cuerpo = $('#cuerpo_horario').empty();
    if (!r.personas.length) {
      cuerpo.append($('<tr></tr>').append($('<td colspan="8" class="text-center text-muted"></td>').text('Registre al personal en la pestaña «Personal» para armar el horario.')));
      return;
    }
    r.personas.forEach(function (p) {
      var tr = $('<tr></tr>').append($('<th scope="row" class="font-weight-normal text-nowrap"></th>').text(p.nombres).append($('<div class="small text-muted"></div>').text(p.cargo)));
      r.dias.forEach(function (d, i) {
        var select = $('<select class="form-control form-control-sm"></select>')
          .attr('aria-label', p.nombres + ', ' + PER_DIAS[i] + ' ' + fecha_corta(d))
          .append('<option value="">—</option>');
        Object.keys(PER.turnos).forEach(function (k) { select.append($('<option></option>').attr('value', k).text(PER.turnos[k])); });
        select.val(p.turnos[i]).attr('data-turno', p.turnos[i]).on('change', function () {
          var s = $(this);
          G.post(RUTA_PER + 'guardar_horario.php', { id_personal: p.id_personal, fecha: d, turno: s.val() }, function () { s.attr('data-turno', s.val()); });
        });
        // La etiqueta va explícita: en celular la tabla se apila y la primera celda es un <th>, no cuenta como columna.
        tr.append($('<td style="min-width: 6.5rem"></td>').attr('data-label', PER_DIAS[i] + ' ' + fecha_corta(d)).append(select));
      });
      cuerpo.append(tr);
    });
  });
}

function copiar_semana() {
  G.post(RUTA_PER + 'copiar_semana.php', { lunes: PER.lunes }, function (r) {
    G.ok(r.copiados ? 'Se copiaron ' + r.copiados + ' turnos' : 'No había turnos que copiar');
    cargar_horario();
  });
}

// ---------- Asistencia ----------

function cargar_asistencia() {
  G.post(RUTA_PER + 'asistencia.php', { fecha: $('#asi_fecha').val() }, function (r) {
    G.tabla('#cuerpo_asistencia', r.personas, [
      function (p) { return $('<div></div>').text(p.nombres).append($('<div class="small text-muted"></div>').text(p.cargo)); },
      function (p) { return p.programado ? G.etiqueta(PER.turnos[p.programado] || p.programado, p.programado === 'DESCANSO' ? 'badge-light' : 'badge-info') : '—'; },
      function (p) { return Number(p.turnos_sistema) ? G.etiqueta('Sí', 'badge-success') : '—'; },
      function (p) {
        var s = $('<select class="form-control form-control-sm asi-estado"></select>').attr('aria-label', 'Asistencia de ' + p.nombres).append('<option value="">Sin marcar</option>');
        PER.estados.forEach(function (e) { s.append($('<option></option>').attr('value', e.codigo).text(e.nombre)); });
        return s.val(p.estado || '');
      },
      function (p) { return $('<input type="time" class="form-control form-control-sm asi-entrada">').attr('aria-label', 'Entrada de ' + p.nombres).val(p.hora_entrada ? p.hora_entrada.slice(0, 5) : ''); },
      function (p) { return $('<input type="time" class="form-control form-control-sm asi-salida">').attr('aria-label', 'Salida de ' + p.nombres).val(p.hora_salida ? p.hora_salida.slice(0, 5) : ''); },
      function (p) { return $('<input class="form-control form-control-sm asi-obs" maxlength="200">').attr('aria-label', 'Observación de ' + p.nombres).val(p.observacion || ''); },
      function (p) { return G.boton('Guardar', 'btn-primary asi-guardar', function () { marcar_asistencia(p, $(this).closest('tr')); }).attr('aria-label', 'Guardar la asistencia de ' + p.nombres); }
    ], 'No hay personal activo.');
    $('#cuerpo_asistencia .asi-estado').on('change', function () {
      var tr = $(this).closest('tr'), sinHora = ['FALTA', 'PERMISO'].indexOf($(this).val()) !== -1;
      tr.find('.asi-entrada, .asi-salida').prop('disabled', sinHora);
    }).trigger('change');
  });
}

function marcar_asistencia(p, tr) {
  var estado = tr.find('.asi-estado').val();
  if (!estado) { Swal.fire('Revise los datos', 'Elija presente, tardanza, falta o permiso.', 'warning'); return; }
  G.post(RUTA_PER + 'marcar.php', {
    id_personal: p.id_personal, fecha: $('#asi_fecha').val(), estado: estado,
    hora_entrada: tr.find('.asi-entrada').val(), hora_salida: tr.find('.asi-salida').val(), observacion: tr.find('.asi-obs').val()
  }, function () {
    var b = tr.find('.asi-guardar');
    b.removeClass('btn-primary').addClass('btn-success').text('Guardado');
    setTimeout(function () { b.removeClass('btn-success').addClass('btn-primary').text('Guardar'); }, 1500);
  });
}

// ---------- Adelantos, descuentos y bonos ----------

function cargar_movimientos() {
  G.post(RUTA_PER + 'movimientos.php', { mes: $('#mov_mes').val() }, function (r) {
    var clases = { ADELANTO: 'badge-info', DESCUENTO: 'badge-warning', BONO: 'badge-success' };
    G.tabla('#cuerpo_movimientos', r.movimientos, [
      function (m) { return Fecha_Local(m.fecha).toLocaleDateString('es-PE'); },
      function (m) { return m.nombres; },
      function (m) { return G.etiqueta(PER.tipos[m.tipo] || m.tipo, clases[m.tipo] || 'badge-light'); },
      function (m) {
        var caja = $('<div></div>').text(m.motivo);
        if (m.estado === 'ANULADO' && m.motivo_anulacion) { caja.append($('<div class="small text-muted"></div>').text('Anulado: ' + m.motivo_anulacion)); }
        return caja;
      },
      function (m) { return $('<span class="d-block text-right text-nowrap"></span>').toggleClass('text-muted', m.estado === 'ANULADO').text(G.soles(m.monto)); },
      function (m) { return G.etiqueta(m.estado === 'ACTIVO' ? 'Vigente' : 'Anulado', m.estado === 'ACTIVO' ? 'badge-success' : 'badge-secondary'); },
      function (m) { return m.estado === 'ACTIVO' ? G.boton('Anular', 'btn-outline-danger', function () { anular_movimiento(m); }).attr('aria-label', 'Anular ' + (PER.tipos[m.tipo] || m.tipo).toLowerCase() + ' de ' + m.nombres) : ''; }
    ], 'Sin adelantos, descuentos ni bonos en el mes.');

    $('#per_faltantes').toggleClass('d-none', !r.faltantes.length);
    $('#per_faltantes_cuenta').toggleClass('d-none', !r.faltantes.length).text(r.faltantes.length);
    G.tabla('#cuerpo_faltantes', r.faltantes, [
      function (f) { return f.numero_documento + ' · ' + (f.turno || ''); },
      function (f) { return Fecha_Local(f.fecha_reporte).toLocaleDateString('es-PE'); },
      function (f) { return f.nombres || $('<span></span>').text(f.grifero || '—').append($('<div class="small text-danger"></div>').text('Sin ficha de personal vinculada')); },
      function (f) { return $('<span class="d-block text-right font-weight-bold"></span>').text(G.soles(f.monto)); },
      function (f) { return f.id_personal ? G.boton('Descontar', 'btn-warning', function () { descontar_faltante(f); }).attr('aria-label', 'Descontar ' + G.soles(f.monto) + ' a ' + f.nombres) : ''; }
    ]);
  });
}

function registrar_movimiento() {
  G.post(RUTA_PER + 'registrar_movimiento.php', {
    id_personal: $('#mov_persona').val(), tipo: $('#mov_tipo').val(), fecha: $('#mov_fecha').val(), monto: $('#mov_monto').val(), motivo: $('#mov_motivo').val()
  }, function () {
    $('#mov_monto, #mov_motivo').val('');
    G.ok('Registrado');
    cargar_movimientos();
  });
}

function anular_movimiento(m) {
  G.pedirMotivo('Anular ' + (PER.tipos[m.tipo] || m.tipo).toLowerCase() + ' de ' + G.soles(m.monto) + ' a ' + m.nombres, function (motivo) {
    G.post(RUTA_PER + 'anular_movimiento.php', { id_movimiento: m.id_movimiento, motivo: motivo }, function () { G.ok('Anulado'); cargar_movimientos(); });
  });
}

function descontar_faltante(f) {
  Swal.fire({
    title: '¿Descontar ' + G.soles(f.monto) + '?', text: 'Se registra como descuento a ' + f.nombres + ' por el faltante del turno ' + f.numero_documento + '.',
    icon: 'question', showCancelButton: true, confirmButtonText: 'Descontar', cancelButtonText: 'Cancelar'
  }).then(function (r) {
    if (!r.isConfirmed) { return; }
    G.post(RUTA_PER + 'descontar_faltante.php', { id_arqueo: f.id_arqueo }, function () { G.ok('Descuento registrado'); cargar_movimientos(); });
  });
}

// ---------- Resumen del mes ----------

function cargar_resumen_personal() {
  G.post(RUTA_PER + 'resumen.php', { mes: $('#res_mes').val() }, function (r) {
    var der = function (v) { return $('<span class="d-block text-right text-nowrap"></span>').text(v); };
    G.tabla('#cuerpo_resumen_personal', r.personas, [
      function (p) { return $('<div></div>').text(p.nombres).append($('<div class="small text-muted"></div>').text(p.cargo)); },
      function (p) { return der(p.programados); },
      function (p) { return der(p.asistidos); },
      function (p) { return der(p.tardanzas); },
      function (p) { return Number(p.faltas) ? der(p.faltas).addClass('text-danger font-weight-bold') : der(0); },
      function (p) { return der(p.permisos); },
      function (p) { return der(p.turnos_sistema); },
      function (p) { return der(p.sueldo !== null ? G.soles(p.sueldo) : '—'); },
      function (p) { return der(G.soles(p.adelantos)); },
      function (p) { return der(G.soles(p.descuentos)); },
      function (p) { return der(G.soles(p.bonos)); },
      function (p) { return der(p.neto !== null ? G.soles(p.neto) : 'Sin sueldo').addClass('font-weight-bold'); }
    ], 'No hay personal activo.');
    var suma = function (k) { return r.personas.reduce(function (s, p) { return s + Number(p[k] || 0); }, 0); };
    var pie = $('#pie_resumen_personal').empty();
    if (r.personas.length) {
      var tr = $('<tr class="font-weight-bold"></tr>').append('<td colspan="7">Total</td>');
      ['sueldo', 'adelantos', 'descuentos', 'bonos', 'neto'].forEach(function (k) { tr.append($('<td class="text-right text-nowrap"></td>').text(G.soles(suma(k)))); });
      pie.append(tr);
    }
  });
}
