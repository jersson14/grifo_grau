// CARGAR DASHBOARD COMPLETO
function Cargar_Dashboard() {
    Cargar_Indicadores_Dia();
    Cargar_Grafico_Ventas_Semana();
    Cargar_Ventas_Mes();
    Cargar_Grafico_Combustibles();
    Cargar_Grafico_Metodos_Pago();
    Cargar_Grafico_Turnos();
    Cargar_Desempeno_Griferos();
    Cargar_Creditos_Mes();
}

// INDICADORES DEL DÍA
function Cargar_Indicadores_Dia() {
    $.ajax({
        url: '../controller/reportes/controlador_indicadores_dia.php',
        type: 'POST'
    }).done(function(resp) {
        var data = JSON.parse(resp);
        $('#total_turnos_activos').text(data.turnos_activos);
        $('#total_ventas_dia').text('S/. ' + parseFloat(data.ventas_dia).toFixed(2));
        $('#total_creditos_pendientes').text(data.creditos_pendientes);
        $('#total_reportes_pendientes').text(data.reportes_pendientes);
    });
}

// GRÁFICO VENTAS ÚLTIMOS 7 DÍAS
function Cargar_Grafico_Ventas_Semana() {
    $.ajax({
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
            var fecha = new Date(item.fecha_reporte);
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
                        borderColor: '#343a40',
                        backgroundColor: 'rgba(52, 58, 64, 0.1)',
                        tension: 0.4
                    },
                    {
                        label: 'Regular',
                        data: regular,
                        borderColor: '#17a2b8',
                        backgroundColor: 'rgba(23, 162, 184, 0.1)',
                        tension: 0.4
                    },
                    {
                        label: 'Premium',
                        data: premium,
                        borderColor: '#ffc107',
                        backgroundColor: 'rgba(255, 193, 7, 0.1)',
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
                        ticks: {
                            callback: function(value) {
                                return 'S/. ' + value.toFixed(0);
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
    $.ajax({
        url: '../controller/reportes/controlador_ventas_mes.php',
        type: 'POST'
    }).done(function(resp) {
        var data = JSON.parse(resp);
        
        $('#mes_total_diesel').text('S/. ' + parseFloat(data.total_diesel).toFixed(2));
        $('#mes_galones_diesel').text(parseFloat(data.galones_diesel).toFixed(2) + ' gal');
        
        $('#mes_total_regular').text('S/. ' + parseFloat(data.total_regular).toFixed(2));
        $('#mes_galones_regular').text(parseFloat(data.galones_regular).toFixed(2) + ' gal');
        
        $('#mes_total_premium').text('S/. ' + parseFloat(data.total_premium).toFixed(2));
        $('#mes_galones_premium').text(parseFloat(data.galones_premium).toFixed(2) + ' gal');
        
        $('#mes_total_ventas').text('S/. ' + parseFloat(data.total_ventas).toFixed(2));
        $('#mes_total_galones').text(parseFloat(data.total_galones).toFixed(2) + ' gal');
    });
}

// GRÁFICO COMBUSTIBLES
function Cargar_Grafico_Combustibles() {
    $.ajax({
        url: '../controller/reportes/controlador_ventas_mes.php',
        type: 'POST'
    }).done(function(resp) {
        var data = JSON.parse(resp);
        
        var ctx = document.getElementById('grafico_combustibles').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Diesel', 'Regular', 'Premium'],
                datasets: [{
                    label: 'Ventas (S/.)',
                    data: [
                        parseFloat(data.total_diesel),
                        parseFloat(data.total_regular),
                        parseFloat(data.total_premium)
                    ],
                    backgroundColor: [
                        'rgba(52, 58, 64, 0.8)',
                        'rgba(23, 162, 184, 0.8)',
                        'rgba(255, 193, 7, 0.8)'
                    ],
                    borderColor: [
                        '#343a40',
                        '#17a2b8',
                        '#ffc107'
                    ],
                    borderWidth: 2
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
                        ticks: {
                            callback: function(value) {
                                return 'S/. ' + value.toFixed(0);
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
    $.ajax({
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
                        'rgba(40, 167, 69, 0.8)',
                        'rgba(220, 53, 69, 0.8)',
                        'rgba(0, 123, 255, 0.8)',
                        'rgba(108, 117, 125, 0.8)',
                        'rgba(255, 193, 7, 0.8)'
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
    $.ajax({
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
                        backgroundColor: 'rgba(23, 162, 184, 0.8)',
                        borderColor: '#17a2b8',
                        borderWidth: 2
                    },
                    {
                        label: 'Promedio',
                        data: promedios,
                        backgroundColor: 'rgba(255, 193, 7, 0.8)',
                        borderColor: '#ffc107',
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
                        ticks: {
                            callback: function(value) {
                                return 'S/. ' + value.toFixed(0);
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
    $.ajax({
        url: '../controller/reportes/controlador_desempeno_griferos.php',
        type: 'POST'
    }).done(function(resp) {
        var data = JSON.parse(resp);
        var html = '';
        
        data.forEach(function(item) {
            html += '<tr>';
            html += '<td>' + item.grifero_nombre + '</td>';
            html += '<td>' + item.total_turnos + '</td>';
            html += '<td>S/. ' + parseFloat(item.total_ventas).toFixed(2) + '</td>';
            html += '<td>S/. ' + parseFloat(item.promedio_ventas).toFixed(2) + '</td>';
            html += '</tr>';
        });
        
        $('#tabla_desempeno_griferos').html(html);
    });
}

// CRÉDITOS DEL MES
function Cargar_Creditos_Mes() {
    $.ajax({
        url: '../controller/reportes/controlador_creditos_mes.php',
        type: 'POST'
    }).done(function(resp) {
        var data = JSON.parse(resp);
        
        $('#mes_total_creditos').text(data.total_creditos);
        $('#mes_monto_creditos').text('S/. ' + parseFloat(data.monto_total).toFixed(2));
        $('#mes_pagado_creditos').text('S/. ' + parseFloat(data.monto_pagado).toFixed(2));
        $('#mes_saldo_creditos').text('S/. ' + parseFloat(data.saldo_pendiente).toFixed(2));
    });
}
