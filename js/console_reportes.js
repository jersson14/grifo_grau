// CARGAR DASHBOARD COMPLETO
function Cargar_Alertas() {
    return $.post('../controller/dashboard/controlador_alertas.php', function (r) {
        var panel = $('#panel_alertas').empty();
        (r.data || []).forEach(function (a) {
            var enlace = $('<a href="#" class="alert-link"></a>').text(a.cantidad + ' ' + a.texto);
            enlace.on('click', function (e) { e.preventDefault(); cargar_contenido('contenido_principal', a.vista); });
            panel.append($('<div class="alert py-2 mb-2" role="alert"></div>').addClass('alert-' + a.nivel).append(enlace));
        });
    }, 'json');
}

function Cargar_Dashboard(extras) {
    // Cada bloque se carga solo si está en la pantalla: el servidor decide cuáles ve cada usuario.
    var hay = function (id) { return document.getElementById(id) !== null; };
    var tareas = [];
    if (hay('panel_alertas')) { tareas.push(Cargar_Alertas); }
    if (hay('mi_resumen')) { tareas.push(Cargar_Mi_Resumen); }
    if (hay('indicadores_dia')) { tareas.push(Cargar_Indicadores_Dia); }
    if (hay('grafico_ventas_semana')) { tareas.push(Cargar_Grafico_Ventas_Semana); }
    if (hay('resumen_mes_productos')) { tareas.push(Cargar_Ventas_Mes); }
    if (hay('grafico_combustibles')) { tareas.push(Cargar_Grafico_Combustibles); }
    if (hay('grafico_metodos_pago')) { tareas.push(Cargar_Grafico_Metodos_Pago); }
    if (hay('grafico_turnos')) { tareas.push(Cargar_Grafico_Turnos); }
    if (hay('tabla_desempeno_griferos')) { tareas.push(Cargar_Desempeno_Griferos); }
    if (hay('mes_total_creditos')) { tareas.push(Cargar_Creditos_Mes); }
    Ejecutar_En_Cola(tareas.concat(extras || []), 2);
}

/**
 * Ejecuta las cargas de a pocas a la vez. Un hosting compartido limita los procesos PHP simultáneos
 * y responde 503 a los que pasan del límite: esas se reintentan hasta tres veces con una pausa creciente.
 * Cada tarea devuelve su petición AJAX.
 */
function Ejecutar_En_Cola(tareas, simultaneas) {
    var pendientes = tareas.slice();
    var siguiente = function () {
        var tarea = pendientes.shift();
        if (!tarea) { return; }
        var intentar = function (intento) {
            var peticion = tarea();
            if (!peticion || typeof peticion.always !== 'function') { siguiente(); return; }
            peticion.fail(function (xhr) {
                if ((xhr.status === 503 || xhr.status === 429 || xhr.status === 0) && intento < 3) {
                    setTimeout(function () { intentar(intento + 1); }, 700 * (intento + 1));
                } else {
                    siguiente();
                }
            }).done(siguiente);
        };
        intentar(0);
    };
    for (var i = 0; i < simultaneas; i++) { siguiente(); }
}

// RESUMEN PROPIO DEL GRIFERO
function Cargar_Mi_Resumen() {
    return $.post('../controller/inicio/controlador_mi_resumen.php', function (r) {
        if (!r || !r.success) { return; }
        var m = r.mes;
        var tarjeta = function (titulo, valor, ayuda, clase) {
            return $('<div class="col-6 col-xl-3"></div>').append($('<div class="card mb-3"></div>').append($('<div class="card-body py-3"></div>').append(
                $('<div class="small text-muted"></div>').text(titulo), $('<div class="g-cifra"></div>').addClass(clase || '').text(valor), $('<div class="small text-muted"></div>').text(ayuda))));
        };
        $('#mi_resumen').empty().append(
            tarjeta('Mis turnos del mes', String(m.turnos), r.abiertos ? r.abiertos + ' abierto(s) ahora' : 'Turnos cerrados'),
            tarjeta('Mis ventas del mes', G.soles(m.ventas), G.num(m.galones, 3) + ' galones'),
            tarjeta('Faltantes del mes', G.soles(m.faltantes), m.turnos ? m.cuadrados + ' de ' + m.turnos + ' turnos cuadraron' : 'Sin turnos cerrados', m.faltantes > 0 ? 'text-danger' : ''),
            tarjeta('Vendido a crédito', G.soles(m.creditos), 'En mis turnos del mes')
        );
        var u = r.ultimo;
        if (u) {
            var cuadre = u.estado === 'ABIERTO' ? 'todavía abierto' : (Math.abs(Number(u.monto_faltante)) < 0.01 ? 'cuadró' : (Number(u.monto_faltante) < 0 ? 'faltaron ' + G.soles(-u.monto_faltante) : 'sobraron ' + G.soles(u.monto_faltante)));
            $('#mi_ultimo_turno').text('Último turno: ' + u.numero_documento + ' del ' + Fecha_Local(u.fecha_reporte).toLocaleDateString('es-PE') + ' (' + (u.turno === 'DIA' ? 'día' : 'noche') + '), ' + G.soles(u.total_ventas) + ', ' + cuadre + '.');
        } else {
            $('#mi_ultimo_turno').text('Todavía no registra turnos.');
        }
    }, 'json');
}

// INDICADORES DEL DÍA
function Cargar_Indicadores_Dia() {
    return $.ajax({
        url: '../controller/reportes/controlador_indicadores_dia.php',
        type: 'POST'
    }).done(function(resp) {
        var data = JSON.parse(resp);
        $('#total_turnos_activos').text(data.turnos_activos);
        $('#total_turnos_cerrados_hoy').text(data.turnos_cerrados_hoy + (data.turnos_cerrados_hoy === 1 ? ' turno cerrado hoy' : ' turnos cerrados hoy'));
        $('#total_ventas_dia').text(G.soles(data.ventas_dia));
        $('#total_creditos_saldo').text(G.soles(data.creditos_saldo));
        $('#total_creditos_pendientes').empty().append(document.createTextNode(data.creditos_pendientes + (data.creditos_pendientes === 1 ? ' vale' : ' vales')),
            data.creditos_vencidos ? $('<span class="text-danger font-weight-bold"></span>').text(' · ' + data.creditos_vencidos + ' vencido(s)') : null);
        $('#total_reportes_pendientes').text(data.reportes_pendientes);
    });
}

// GRÁFICO VENTAS ÚLTIMOS 7 DÍAS
function Cargar_Grafico_Ventas_Semana() {
    return $.ajax({
        url: '../controller/reportes/controlador_ventas_semana.php',
        type: 'POST'
    }).done(function(resp) {
        var data = JSON.parse(resp);

        var fechas = [];
        var diesel = [];
        var regular = [];
        var premium = [];
        var totales = [];

        data.forEach(function(item) {
            var fecha = Fecha_Local(item.fecha_reporte);
            fechas.push(fecha.toLocaleDateString('es-PE', { day: '2-digit', month: '2-digit' }));
            diesel.push(parseFloat(item.total_diesel));
            regular.push(parseFloat(item.total_regular));
            premium.push(parseFloat(item.total_premium));
            totales.push(parseFloat(item.total_ventas));
        });

        var ctx = document.getElementById('grafico_ventas_semana').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: fechas,
                datasets: [
                    {
                        label: 'Diesel',
                        data: diesel,
                        borderColor: '#2563eb',
                        backgroundColor: 'rgba(37, 99, 235, 0.1)',
                        tension: 0.4
                    },
                    {
                        label: 'Regular',
                        data: regular,
                        borderColor: '#059669',
                        backgroundColor: 'rgba(5, 150, 105, 0.1)',
                        tension: 0.4
                    },
                    {
                        label: 'Premium',
                        data: premium,
                        borderColor: '#f59e0b',
                        backgroundColor: 'rgba(245, 158, 11, 0.12)',
                        tension: 0.4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        suggestedMax: 100,
                        ticks: {
                            precision: 0,
                            callback: function(value) {
                                return 'S/. ' + Number(value).toLocaleString('es-PE');
                            }
                        }
                    }
                }
            }
        });
    });
}

// VENTAS DEL MES
function Cargar_Ventas_Mes() {
    return $.ajax({
        url: '../controller/reportes/controlador_ventas_mes.php',
        type: 'POST'
    }).done(function(resp) {
        var data = JSON.parse(resp);
        // Una tarjeta por producto vendido en el mes, sea cual sea su tipo.
        var caja = $('#resumen_mes_productos').empty();
        (data.por_producto || []).forEach(function (p) {
            caja.append($('<div class="col-6 col-lg-3 mb-3"></div>').append(
                $('<div class="small text-muted"></div>').text(p.nombre),
                $('<div class="font-weight-bold"></div>').text(G.soles(p.total)),
                $('<div class="small text-muted"></div>').text(G.num(p.galones, 2) + ' gal')
            ));
        });
        if (!(data.por_producto || []).length) {
            caja.append($('<div class="col-12 text-muted mb-3"></div>').text('Sin turnos cerrados este mes.'));
        }
        $('#mes_total_ventas').text(G.soles(data.total_ventas || 0));
        $('#mes_total_galones').text(G.num(data.total_galones || 0, 2) + ' gal');
    });
}

// GRÁFICO COMBUSTIBLES
function Cargar_Grafico_Combustibles() {
    return $.ajax({
        url: '../controller/reportes/controlador_ventas_mes.php',
        type: 'POST'
    }).done(function(resp) {
        var data = JSON.parse(resp);

        // Una barra por producto del mes. Diésel, regular y premium mantienen sus colores; los demás van en gris.
        var COLORES = { DIESEL: '#2563eb', REGULAR: '#059669', PREMIUM: '#f59e0b' };
        var productos = data.por_producto || [];
        var ctx = document.getElementById('grafico_combustibles').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: productos.length ? productos.map(function (p) { return p.nombre; }) : ['Diesel', 'Regular', 'Premium'],
                datasets: [{
                    label: 'Ventas (S/.)',
                    data: productos.length ? productos.map(function (p) { return parseFloat(p.total); }) : [0, 0, 0],
                    backgroundColor: productos.map(function (p) { return COLORES[p.tipo] || '#71717a'; }),
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        suggestedMax: 100,
                        ticks: {
                            precision: 0,
                            callback: function(value) {
                                return 'S/. ' + Number(value).toLocaleString('es-PE');
                            }
                        }
                    }
                }
            }
        });
    });
}

// GRÁFICO MÉTODOS DE PAGO
function Cargar_Grafico_Metodos_Pago() {
    return $.ajax({
        url: '../controller/reportes/controlador_metodos_pago.php',
        type: 'POST'
    }).done(function(resp) {
        var data = JSON.parse(resp);

        var ctx = document.getElementById('grafico_metodos_pago').getContext('2d');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Efectivo', 'Yape', 'BCP', 'Visa', 'Crédito'],
                datasets: [{
                    data: [
                        parseFloat(data.total_efectivo),
                        parseFloat(data.total_yape),
                        parseFloat(data.total_bcp),
                        parseFloat(data.total_visa),
                        parseFloat(data.total_credito)
                    ],
                    backgroundColor: [
                        '#2563eb',
                        '#059669',
                        '#7c3aed',
                        '#71717a',
                        'rgba(245, 158, 11, 0.85)'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    });
}

// GRÁFICO TURNOS
function Cargar_Grafico_Turnos() {
    return $.ajax({
        url: '../controller/reportes/controlador_ventas_turno.php',
        type: 'POST'
    }).done(function(resp) {
        var data = JSON.parse(resp);

        var turnos = [];
        var ventas = [];
        var promedios = [];

        data.forEach(function(item) {
            turnos.push(item.turno);
            ventas.push(parseFloat(item.total_ventas));
            promedios.push(parseFloat(item.promedio_ventas));
        });

        var ctx = document.getElementById('grafico_turnos').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: turnos,
                datasets: [
                    {
                        label: 'Total Ventas',
                        data: ventas,
                        backgroundColor: 'rgba(5, 150, 105, 0.85)',
                        borderColor: '#059669',
                        borderWidth: 2
                    },
                    {
                        label: 'Promedio',
                        data: promedios,
                        backgroundColor: 'rgba(245, 158, 11, 0.85)',
                        borderColor: '#f59e0b',
                        borderWidth: 2
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        suggestedMax: 100,
                        ticks: {
                            precision: 0,
                            callback: function(value) {
                                return 'S/. ' + Number(value).toLocaleString('es-PE');
                            }
                        }
                    }
                }
            }
        });
    });
}

// DESEMPEÑO GRIFEROS
function Cargar_Desempeno_Griferos() {
    return $.ajax({
        url: '../controller/reportes/controlador_desempeno_griferos.php',
        type: 'POST'
    }).done(function(resp) {
        var data = JSON.parse(resp);
        var der = function (t) { return $('<span class="d-block text-right text-nowrap"></span>').text(t); };
        G.tabla('#tabla_desempeno_griferos', data, [
            function (g) { return g.grifero_nombre; },
            function (g) { return der(g.total_turnos); },
            function (g) { return der(G.soles(g.total_ventas)); },
            function (g) { return der(G.soles(g.promedio_ventas)); }
        ], 'Sin turnos cerrados este mes.');
    });
}

// CRÉDITOS DEL MES
function Cargar_Creditos_Mes() {
    return $.ajax({
        url: '../controller/reportes/controlador_creditos_mes.php',
        type: 'POST'
    }).done(function(resp) {
        var data = JSON.parse(resp);

        $('#mes_total_creditos').text(data.total_creditos);
        $('#mes_monto_creditos').text(G.soles(data.monto_total));
        $('#mes_pagado_creditos').text(G.soles(data.monto_pagado));
        $('#mes_saldo_creditos').text(G.soles(data.saldo_pendiente));
    });
}
