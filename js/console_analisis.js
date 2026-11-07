var RUTA_ANA = '../controller/analisis/controlador_';
var ANA_DIAS = [2, 3, 4, 5, 6, 7, 1]; // De lunes a domingo, con la numeración de MySQL (1 = domingo).
var ANA_NOMBRES = { 1: 'Domingo', 2: 'Lunes', 3: 'Martes', 4: 'Miércoles', 5: 'Jueves', 6: 'Viernes', 7: 'Sábado' };
// Escala secuencial de un solo tono (azul del panel), de claro a oscuro.
var ANA_ESCALA = ['#eff6ff', '#dbeafe', '#bfdbfe', '#93c5fd', '#60a5fa', '#3b82f6', '#2563eb', '#1d4ed8'];

function iniciar_analisis() {
  periodo_rapido('mes');
  $('#filtro_analisis').on('submit', function (e) { e.preventDefault(); cargar_seccion(); });
  $('input[name=ana_rapido]').on('change', function () { periodo_rapido(this.value); cargar_seccion(); });
  $('#ana_desde, #ana_hasta').on('change', function () { $('input[name=ana_rapido]').prop('checked', false); actualizar_enlaces(); });
  $('#ana_pestanas a').on('shown.bs.tab', cargar_seccion);
  cargar_seccion();
}

function periodo_rapido(tipo) {
  var hoy = Fecha_Local(G.hoy());
  var desde, hasta;
  if (tipo === 'anterior') {
    desde = new Date(hoy.getFullYear(), hoy.getMonth() - 1, 1);
    hasta = new Date(hoy.getFullYear(), hoy.getMonth(), 0);
  } else if (tipo === '30') {
    desde = new Date(hoy); desde.setDate(desde.getDate() - 29);
    hasta = hoy;
  } else {
    desde = new Date(hoy.getFullYear(), hoy.getMonth(), 1);
    hasta = hoy;
  }
  $('#ana_desde').val(Fecha_ISO_Local(desde));
  $('#ana_hasta').val(Fecha_ISO_Local(hasta));
}

function seccion_actual() { return $('#ana_pestanas a.active').data('seccion') || 'rentabilidad'; }

function actualizar_enlaces() {
  var periodo = { desde: $('#ana_desde').val(), hasta: $('#ana_hasta').val() };
  $('#ana_excel').attr('href', RUTA_ANA + 'exportar.php?' + $.param($.extend({ seccion: seccion_actual() }, periodo)));
  $('#ana_pdf').attr('href', '../view/MPDF/REPORTE/analisis.php?' + $.param(periodo));
}

function cargar_seccion() {
  var seccion = seccion_actual();
  actualizar_enlaces();
  G.post(RUTA_ANA + 'datos.php', { seccion: seccion, desde: $('#ana_desde').val(), hasta: $('#ana_hasta').val() }, function (r) {
    ({ rentabilidad: pintar_rentabilidad, griferos: pintar_griferos, morosidad: pintar_morosidad, horario: pintar_horario })[seccion](r);
  });
}

// ---------- Piezas comunes ----------

function ana_tarjeta(titulo, valor, ayuda, clase) {
  var cuerpo = $('<div class="card-body py-3"></div>').append(
    $('<div class="small text-muted"></div>').text(titulo),
    $('<div class="g-cifra"></div>').addClass(clase || '').text(valor)
  );
  if (ayuda) { cuerpo.append($('<div class="small text-muted"></div>').text(ayuda)); }
  return $('<div class="col-6 col-xl-3"></div>').append($('<div class="card mb-3"></div>').append(cuerpo));
}

function ana_der(texto) { return $('<span class="d-block text-right text-nowrap"></span>').text(texto); }

// Barra de una sola serie: el valor en texto al lado, la barra solo da la proporción.
function ana_barra(proporcion, texto, etiqueta) {
  var p = Math.max(0, Math.min(1, proporcion || 0));
  return $('<div class="g-barra"></div>').attr('title', etiqueta || texto).append(
    $('<span class="g-barra-pista" aria-hidden="true"></span>').append($('<span class="g-barra-relleno"></span>').css('width', (p * 100).toFixed(1) + '%')),
    $('<span class="g-barra-valor"></span>').text(texto)
  );
}

function ana_pct(v) { return v === null || v === undefined ? '—' : G.num(v, 1) + ' %'; }

// ---------- Rentabilidad ----------

function pintar_rentabilidad(r) {
  var t = r.totales;
  $('#ren_tarjetas').empty().append(
    ana_tarjeta('Ingresos por combustible', G.soles(t.ingresos), G.galones(t.galones) + ' galones'),
    ana_tarjeta('Costo estimado', t.ingresos_con_costo > 0 ? G.soles(t.costo) : '—', t.sin_costo ? t.sin_costo + ' producto(s) sin compras recientes' : 'Según compras recientes'),
    ana_tarjeta('Margen bruto estimado', t.ingresos_con_costo > 0 ? G.soles(t.margen) : '—', t.margen_pct !== null ? ana_pct(t.margen_pct) + ' de los ingresos con costo' : 'Registre compras para estimarlo', t.margen < 0 ? 'text-danger' : ''),
    r.tienda ? ana_tarjeta('Margen de la tienda', G.soles(r.tienda.margen), r.tienda.ingresos > 0 ? ana_pct(r.tienda.margen_pct) + ' de ' + G.soles(r.tienda.ingresos) : 'Sin ventas en el periodo') : null
  );
  var maximo = r.productos.reduce(function (m, p) { return Math.max(m, p.margen_pct || 0); }, 0) || 1;
  G.tabla('#cuerpo_rentabilidad', r.productos, [
    function (p) { return $('<span class="text-nowrap"></span>').append($('<span class="g-punto mr-2" aria-hidden="true"></span>').attr('data-combustible', combustible_analisis(p.nombre)), document.createTextNode(p.nombre)); },
    function (p) { return ana_der(G.galones(p.galones)); },
    function (p) { return ana_der(G.soles(p.ingresos)); },
    function (p) { return ana_der(p.precio_promedio !== null ? 'S/ ' + G.num(p.precio_promedio, 3) : '—'); },
    function (p) { return p.costo_galon !== null ? ana_der('S/ ' + G.num(p.costo_galon, 3)) : $('<span class="d-block text-right text-muted small"></span>').text('Sin compras recientes'); },
    function (p) { return p.margen !== null ? ana_der(G.soles(p.margen)).toggleClass('text-danger', p.margen < 0) : ana_der('—'); },
    function (p) { return p.margen_pct !== null ? ana_barra(p.margen_pct / maximo, ana_pct(p.margen_pct), 'Margen de ' + p.nombre + ': ' + ana_pct(p.margen_pct)) : $('<span class="text-muted"></span>').text('—'); }
  ], 'No hay turnos cerrados en el periodo.');
  $('#ren_nota').text('Ingresos según las lecturas de los turnos cerrados, antes de descuentos. El costo por galón es el promedio ponderado de las compras de los ' + r.dias_costo + ' días previos al fin del periodo, sin percepción.');
}

function combustible_analisis(nombre) {
  return window.combustible_de ? combustible_de(nombre) : (/DIESEL/i.test(nombre) ? 'diesel' : /PREMIUM|95/i.test(nombre) ? 'premium' : /REGULAR|84|90/i.test(nombre) ? 'regular' : 'otro');
}

// ---------- Griferos ----------

function pintar_griferos(r) {
  var maximo = r.griferos.reduce(function (m, g) { return Math.max(m, g.ventas); }, 0) || 1;
  G.tabla('#cuerpo_griferos', r.griferos, [
    function (g) { return g.puesto; },
    function (g) { return g.grifero; },
    function (g) { return ana_der(g.turnos); },
    function (g) { return ana_der(G.galones(g.galones)); },
    function (g) { return ana_barra(g.ventas / maximo, G.soles(g.ventas), g.grifero + ': ' + G.soles(g.ventas)); },
    function (g) { return ana_der(G.soles(g.promedio_turno)); },
    function (g) { return ana_der(G.soles(g.faltantes)).toggleClass('text-danger font-weight-bold', g.faltantes > 0); },
    function (g) { return ana_der(G.soles(g.sobrantes)); },
    function (g) { return ana_der(g.cuadre_pct + ' %'); }
  ], 'No hay turnos cerrados en el periodo.');
}

// ---------- Morosidad ----------

function pintar_morosidad(r) {
  var t = r.totales;
  $('#mor_tarjetas').empty().append(
    ana_tarjeta('Por cobrar', G.soles(t.pendiente), t.clientes + (t.clientes === 1 ? ' cliente' : ' clientes')),
    ana_tarjeta('Vencido', G.soles(t.vencido), t.pendiente > 0 ? ana_pct(t.vencido / t.pendiente * 100) + ' de lo pendiente' : '', t.vencido > 0 ? 'text-danger' : ''),
    ana_tarjeta('Vencido hace más de 90 días', G.soles(t.d90), 'Difícil de cobrar', t.d90 > 0 ? 'text-danger' : ''),
    ana_tarjeta('Por vencer', G.soles(t.por_vencer), 'Dentro del plazo')
  );
  $('#mor_nota').text('Créditos con saldo al ' + Fecha_Local(r.corte).toLocaleDateString('es-PE') + '. No depende del periodo elegido. Primero quien debe más vencido.');
  var monto = function (v, alerta) { return ana_der(v > 0 ? G.soles(v) : '—').toggleClass('text-danger font-weight-bold', !!alerta && v > 0).toggleClass('text-muted', !(v > 0)); };
  G.tabla('#cuerpo_morosidad', r.clientes, [
    function (c) { return $('<div></div>').text(c.nombre_completo).append(c.dni_ruc ? $('<div class="small text-muted"></div>').text(c.dni_ruc) : null); },
    function (c) { return ana_der(c.vales); },
    function (c) { return ana_der(G.soles(c.pendiente)).addClass('font-weight-bold'); },
    function (c) { return monto(c.por_vencer); },
    function (c) { return monto(c.d1_30); },
    function (c) { return monto(c.d31_60); },
    function (c) { return monto(c.d61_90, true); },
    function (c) { return monto(c.d90, true); },
    function (c) { return ana_der(c.dias_max ? c.dias_max + (c.dias_max === 1 ? ' día' : ' días') : 'Al día'); }
  ], 'No hay créditos pendientes.');
}

// ---------- Horario ----------

function pintar_horario(r) {
  var cabecera = $('#cabecera_calor').empty(), cuerpo = $('#cuerpo_calor').empty();
  if (!r.celdas.length) {
    $('#tabla_calor').addClass('d-none');
    $('#hor_nota').text('No hay ventas individuales en el periodo. Este cuadro se llena con las ventas registradas una por una en Ventas; los turnos solo tienen el total.');
  } else {
    $('#tabla_calor').removeClass('d-none');
    var mapa = {}, maximo = 0, desde = 23, hasta = 0;
    r.celdas.forEach(function (c) {
      mapa[c.dia + '-' + c.hora] = c;
      maximo = Math.max(maximo, c.total);
      desde = Math.min(desde, c.hora); hasta = Math.max(hasta, c.hora);
    });
    var fila = $('<tr></tr>').append('<th scope="col">Día</th>');
    for (var h = desde; h <= hasta; h++) { fila.append($('<th scope="col" class="text-center"></th>').text(('0' + h).slice(-2))); }
    cabecera.append(fila);
    ANA_DIAS.forEach(function (d) {
      var tr = $('<tr></tr>').append($('<th scope="row" class="font-weight-normal"></th>').text(ANA_NOMBRES[d]));
      for (var h = desde; h <= hasta; h++) {
        var c = mapa[d + '-' + h];
        var td = $('<td class="text-center"></td>');
        if (c) {
          var nivel = Math.min(ANA_ESCALA.length - 1, Math.floor(c.total / maximo * (ANA_ESCALA.length - 1) + 0.0001));
          td.css({ background: ANA_ESCALA[nivel], color: nivel >= 5 ? '#fff' : '#18181b' }).text(Math.round(c.total))
            .attr('title', ANA_NOMBRES[d] + ' ' + ('0' + h).slice(-2) + ':00 · ' + c.ventas + (c.ventas === 1 ? ' venta' : ' ventas') + ' · ' + G.soles(c.total));
        } else {
          td.addClass('text-muted').text('·').attr('aria-label', 'Sin ventas');
        }
        tr.append(td);
      }
      cuerpo.append(tr);
    });
    var pico = r.hora_pico;
    $('#hor_nota').text('Soles vendidos en ventas individuales; más oscuro, más venta. ' + (pico !== null ? 'Hora con más venta: ' + ('0' + pico).slice(-2) + ':00 a ' + ('0' + ((pico + 1) % 24)).slice(-2) + ':00 (' + G.soles(r.por_hora[pico].total) + ').' : ''));
  }

  var turnos = {};
  r.turnos.forEach(function (t) { turnos[t.dia + '-' + t.turno] = t; });
  var celda = function (d, turno) {
    var t = turnos[d + '-' + turno];
    return t ? ana_der(G.soles(t.promedio) + ' (' + t.turnos + ')') : ana_der('—').addClass('text-muted');
  };
  G.tabla('#cuerpo_turnos_dia', r.turnos.length ? ANA_DIAS : [], [
    function (d) { return ANA_NOMBRES[d]; },
    function (d) { return celda(d, 'DIA'); },
    function (d) { return celda(d, 'NOCHE'); }
  ], 'No hay turnos cerrados en el periodo.');
}
