var tabla_creditos_pendientes;
var tabla_historial_pagos_credito;

// CARGAR RESUMEN DE CRÉDITOS
function Cargar_Resumen_Creditos() {
    $.ajax({
        url: '../controller/creditos/controlador_resumen_creditos.php',
        type: 'POST'
    }).done(function(resp) {
        var data = JSON.parse(resp);
        $('#total_creditos_pendientes').text(data.creditos_pendientes);
        $('#total_creditos_vencidos').text(data.creditos_vencidos);
        $('#total_saldo_pendiente').text('S/. ' + parseFloat(data.saldo_pendiente).toFixed(2));
        $('#total_monto_pagado').text('S/. ' + parseFloat(data.monto_pagado).toFixed(2));
    });
}

// LISTAR CRÉDITOS PENDIENTES
function Listar_Creditos_Pendientes() {
    var filtro_cliente = $('#filtro_cliente').val();
    var filtro_estado = $('#filtro_estado').val();
    
    tabla_creditos_pendientes = $("#tabla_creditos_pendientes").DataTable({
        "ordering": true,
        "bLengthChange": true,
        "searching": { "regex": false },
        "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
        "pageLength": 10,
        "destroy": true,
        "async": false,
        "processing": true,
        "ajax": {
            "url": "../controller/creditos/controlador_listar_creditos.php",
            type: 'POST',
            data: {
                filtro_cliente: filtro_cliente,
                filtro_estado: filtro_estado
            }
        },
        "columns": [
            { "data": "numero_vale" },
            { "data": "cliente_nombre" },
            { 
                "data": "created_at",
                "render": function(data) {
                    var fecha = new Date(data);
                    return fecha.toLocaleDateString('es-PE');
                }
            },
            { 
                "data": "turno",
                "render": function(data, type, row) {
                    return '<small>' + row.numero_documento + '<br>' + data + '</small>';
                }
            },
            { 
                "data": "monto",
                "render": function(data) {
                    return 'S/. ' + parseFloat(data).toFixed(2);
                }
            },
            { 
                "data": "monto_pagado",
                "render": function(data) {
                    return 'S/. ' + parseFloat(data).toFixed(2);
                }
            },
            { 
                "data": "saldo_pendiente",
                "render": function(data) {
                    return '<strong>S/. ' + parseFloat(data).toFixed(2) + '</strong>';
                }
            },
            { 
                "data": "fecha_vencimiento",
                "render": function(data, type, row) {
                    if (data) {
                        var fecha = new Date(data);
                        var dias = row.dias_vencido;
                        var html = fecha.toLocaleDateString('es-PE');
                        if (dias > 0) {
                            html += '<br><span class="badge badge-danger">Vencido ' + dias + ' días</span>';
                        } else if (dias < 0) {
                            html += '<br><span class="badge badge-warning">Vence en ' + Math.abs(dias) + ' días</span>';
                        }
                        return html;
                    }
                    return '-';
                }
            },
            {
                "data": "estado",
                "render": function(data) {
                    if (data == 'PENDIENTE') {
                        return '<span class="badge badge-warning">PENDIENTE</span>';
                    } else if (data == 'PAGADO') {
                        return '<span class="badge badge-success">PAGADO</span>';
                    } else {
                        return '<span class="badge badge-danger">ANULADO</span>';
                    }
                }
            },
            {
                "data": "estado",
                "render": function(data, type, row) {
                    if (data == 'PENDIENTE') {
                        return "<button class='pagar btn btn-success btn-sm' title='Registrar Pago'><i class='fas fa-money-bill-wave'></i></button>&nbsp;<button class='historial btn btn-info btn-sm' title='Ver Historial'><i class='fas fa-history'></i></button>&nbsp;<button class='anular btn btn-danger btn-sm' title='Anular'><i class='fas fa-ban'></i></button>";
                    } else {
                        return "<button class='historial btn btn-info btn-sm' title='Ver Historial'><i class='fas fa-history'></i></button>";
                    }
                }
            }
        ],
        "language": idioma_espanol,
        select: true
    });

    document.getElementById("tabla_creditos_pendientes").addEventListener("click", function(e) {
        if (e.target.closest(".pagar")) {
            var data = tabla_creditos_pendientes.row(e.target.closest("tr")).data();
            Abrir_Modal_Pago(data.id_credito);
        }
        if (e.target.closest(".historial")) {
            var data = tabla_creditos_pendientes.row(e.target.closest("tr")).data();
            Ver_Historial_Pagos(data.id_credito);
        }
        if (e.target.closest(".anular")) {
            var data = tabla_creditos_pendientes.row(e.target.closest("tr")).data();
            Anular_Credito(data.id_credito);
        }
    });
}

// ABRIR MODAL REGISTRAR PAGO
function Abrir_Modal_Pago(id_credito) {
    $.ajax({
        url: '../controller/creditos/controlador_detalle_credito.php',
        type: 'POST',
        data: { id_credito: id_credito }
    }).done(function(resp) {
        var data = JSON.parse(resp);
        
        $('#txt_id_credito_pago').val(data.id_credito);
        $('#info_cliente_pago').text(data.cliente_nombre);
        $('#info_vale_pago').text(data.numero_vale);
        $('#info_monto_total_pago').text('S/. ' + parseFloat(data.monto).toFixed(2));
        $('#info_saldo_pendiente_pago').text('S/. ' + parseFloat(data.saldo_pendiente).toFixed(2));
        $('#max_monto_pago').text('S/. ' + parseFloat(data.saldo_pendiente).toFixed(2));
        
        Cargar_Tipos_Pago_Credito();
        
        $('#modal_registrar_pago').modal('show');
    });
}

// CARGAR TIPOS DE PAGO
function Cargar_Tipos_Pago_Credito() {
    $.ajax({
        url: '../controller/turnos/controlador_tipos_pago.php',
        type: 'POST'
    }).done(function(resp) {
        var data = JSON.parse(resp);
        var opciones = '<option value="">-- Seleccione --</option>';
        data.forEach(function(item) {
            opciones += '<option value="' + item.id_tipo_pago + '" data-requiere="' + item.requiere_codigo + '">' + item.nombre + '</option>';
        });
        $("#txt_tipo_pago_credito").html(opciones);
    });
}

// PAGAR SALDO COMPLETO
function Pagar_Saldo_Completo() {
    var saldo = $('#info_saldo_pendiente_pago').text().replace('S/. ', '');
    $('#txt_monto_pago_credito').val(saldo);
}

// REGISTRAR PAGO DE CRÉDITO
function Registrar_Pago_Credito() {
    var id_credito = $('#txt_id_credito_pago').val();
    var id_tipo_pago = $('#txt_tipo_pago_credito').val();
    var codigo_operacion = $('#txt_codigo_operacion_credito').val();
    var monto_pagado = $('#txt_monto_pago_credito').val();
    var observaciones = $('#txt_observaciones_pago_credito').val();
    var id_usuario = $('#txtprincipalid').val();
    
    if (id_tipo_pago.length == 0 || monto_pagado.length == 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Advertencia',
            text: 'Complete los campos obligatorios',
            confirmButtonColor: '#023D77'
        });
        return;
    }
    
    if (parseFloat(monto_pagado) <= 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Advertencia',
            text: 'El monto debe ser mayor a 0',
            confirmButtonColor: '#023D77'
        });
        return;
    }
    
    $.ajax({
        url: '../controller/creditos/controlador_registrar_pago_credito.php',
        type: 'POST',
        data: {
            id_credito: id_credito,
            id_tipo_pago: id_tipo_pago,
            codigo_operacion: codigo_operacion,
            monto_pagado: monto_pagado,
            id_usuario: id_usuario,
            observaciones: observaciones
        }
    }).done(function(resp) {
        if (resp > 0) {
            Swal.fire({
                icon: 'success',
                title: 'Éxito',
                text: 'Pago registrado correctamente',
                confirmButtonColor: '#023D77'
            });
            $('#modal_registrar_pago').modal('hide');
            Listar_Creditos_Pendientes();
            Cargar_Resumen_Creditos();
            Limpiar_Modal_Pago();
        } else if (resp == -1) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'El monto es mayor al saldo pendiente',
                confirmButtonColor: '#023D77'
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'No se pudo registrar el pago',
                confirmButtonColor: '#023D77'
            });
        }
    });
}

// LIMPIAR MODAL PAGO
function Limpiar_Modal_Pago() {
    $('#txt_tipo_pago_credito').val('');
    $('#txt_codigo_operacion_credito').val('');
    $('#txt_monto_pago_credito').val('');
    $('#txt_observaciones_pago_credito').val('');
}

// VER HISTORIAL DE PAGOS
function Ver_Historial_Pagos(id_credito) {
    // Cargar información del crédito
    $.ajax({
        url: '../controller/creditos/controlador_detalle_credito.php',
        type: 'POST',
        data: { id_credito: id_credito }
    }).done(function(resp) {
        var data = JSON.parse(resp);
        
        $('#hist_cliente').text(data.cliente_nombre);
        $('#hist_vale').text(data.numero_vale);
        $('#hist_monto_total').text('S/. ' + parseFloat(data.monto).toFixed(2));
        $('#hist_saldo_pendiente').text('S/. ' + parseFloat(data.saldo_pendiente).toFixed(2));
    });
    
    // Cargar historial de pagos
    if (tabla_historial_pagos_credito) {
        tabla_historial_pagos_credito.destroy();
    }
    
    tabla_historial_pagos_credito = $("#tabla_historial_pagos_credito").DataTable({
        "ordering": false,
        "bLengthChange": false,
        "searching": false,
        "pageLength": 10,
        "destroy": true,
        "processing": true,
        "ajax": {
            "url": "../controller/creditos/controlador_historial_pagos_credito.php",
            "type": "POST",
            "data": { id_credito: id_credito }
        },
        "columns": [
            { 
                "data": "fecha_pago",
                "render": function(data) {
                    var fecha = new Date(data);
                    return fecha.toLocaleDateString('es-PE') + '<br>' + fecha.toLocaleTimeString('es-PE');
                }
            },
            { "data": "tipo_pago_nombre" },
            { 
                "data": "codigo_operacion",
                "render": function(data) {
                    return data ? data : '-';
                }
            },
            { 
                "data": "monto_pagado",
                "render": function(data) {
                    return '<strong>S/. ' + parseFloat(data).toFixed(2) + '</strong>';
                }
            },
            { 
                "data": "saldo_anterior",
                "render": function(data) {
                    return 'S/. ' + parseFloat(data).toFixed(2);
                }
            },
            { 
                "data": "saldo_nuevo",
                "render": function(data) {
                    return 'S/. ' + parseFloat(data).toFixed(2);
                }
            },
            { "data": "registrado_por" },
            { 
                "data": "observaciones",
                "render": function(data) {
                    return data ? data : '-';
                }
            }
        ],
        "language": idioma_espanol
    });
    
    $('#modal_historial_pagos').modal('show');
}

// ANULAR CRÉDITO
function Anular_Credito(id_credito) {
    Swal.fire({
        title: '¿Anular crédito?',
        text: "Esta acción no se puede deshacer",
        icon: 'warning',
        input: 'textarea',
        inputPlaceholder: 'Motivo de anulación...',
        inputAttributes: {
            'aria-label': 'Motivo de anulación'
        },
        showCancelButton: true,
        confirmButtonColor: '#023D77',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, anular',
        cancelButtonText: 'Cancelar',
        inputValidator: (value) => {
            if (!value) {
                return 'Debe ingresar el motivo de anulación'
            }
        }
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '../controller/creditos/controlador_anular_credito.php',
                type: 'POST',
                data: {
                    id_credito: id_credito,
                    motivo: result.value
                }
            }).done(function(resp) {
                if (resp > 0) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Éxito',
                        text: 'Crédito anulado correctamente',
                        confirmButtonColor: '#023D77'
                    });
                    Listar_Creditos_Pendientes();
                    Cargar_Resumen_Creditos();
                }
            });
        }
    });
}

// CARGAR TOP DEUDORES
function Cargar_Top_Deudores() {
    $.ajax({
        url: '../controller/creditos/controlador_top_deudores.php',
        type: 'POST'
    }).done(function(resp) {
        var data = JSON.parse(resp);
        var html = '';
        var contador = 1;
        
        data.forEach(function(item) {
            html += '<tr>';
            html += '<td>' + contador + '</td>';
            html += '<td>' + item.nombre_completo + '</td>';
            html += '<td>' + (item.dni_ruc ? item.dni_ruc : '-') + '</td>';
            html += '<td>' + (item.telefono ? item.telefono : '-') + '</td>';
            html += '<td>' + item.total_creditos + '</td>';
            html += '<td><strong class="text-danger">S/. ' + parseFloat(item.saldo_pendiente).toFixed(2) + '</strong></td>';
            html += '<td><button class="btn btn-info btn-sm" onclick="Filtrar_Por_Cliente(' + item.id_cliente + ')"><i class="fas fa-eye"></i> Ver</button></td>';
            html += '</tr>';
            contador++;
        });
        
        $('#tbody_top_deudores').html(html);
    });
}

// FILTRAR POR CLIENTE
function Filtrar_Por_Cliente(id_cliente) {
    $('#filtro_cliente').val(id_cliente);
    $('#filtro_estado').val('PENDIENTE');
    Filtrar_Creditos();
}

// FILTRAR CRÉDITOS
function Filtrar_Creditos() {
    tabla_creditos_pendientes.ajax.reload();
}

// CARGAR CLIENTES PARA FILTRO
function Cargar_Clientes_Filtro() {
    $.ajax({
        url: '../controller/clientes/controlador_clientes_activos.php',
        type: 'POST'
    }).done(function(resp) {
        var data = JSON.parse(resp);
        var opciones = '<option value="">Todos los clientes</option>';
        data.forEach(function(item) {
            opciones += '<option value="' + item.id_cliente + '">' + item.nombre_completo + '</option>';
        });
        $("#filtro_cliente").html(opciones);
        
        // Inicializar Select2
        $('#filtro_cliente').select2({
            placeholder: 'Todos los clientes',
            allowClear: true,
            language: {
                noResults: function() {
                    return "No se encontraron resultados";
                },
                searching: function() {
                    return "Buscando...";
                }
            }
        });
    });
}
