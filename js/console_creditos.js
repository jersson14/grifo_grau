var tabla_creditos_pendientes;
var tabla_creditos_por_cliente;
var tabla_vales_cliente;
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

// LISTAR CRÉDITOS AGRUPADOS POR CLIENTE
function Listar_Creditos_Por_Cliente() {
    var filtro_estado = $('#filtro_estado').val() || 'PENDIENTE';
    
    if (tabla_creditos_por_cliente) {
        tabla_creditos_por_cliente.destroy();
    }
    
    tabla_creditos_por_cliente = $("#tabla_creditos_por_cliente").DataTable({
        "ordering": true,
        "bLengthChange": true,
        "searching": true,
        "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Todos"]],
        "pageLength": 10,
        "destroy": true,
        "processing": true,
        "ajax": {
            "url": "../controller/creditos/controlador_listar_creditos_por_cliente.php",
            type: 'POST',
            data: {
                filtro_estado: filtro_estado
            }
        },
        "columns": [
            { "data": "nombre_completo" },
            { "data": "dni_ruc" },
            { 
                "data": "telefono",
                "render": function(data) {
                    return data ? data : '-';
                }
            },
            { 
                "data": "total_vales",
                "render": function(data) {
                    return '<span class="badge badge-info">' + data + '</span>';
                }
            },
            { 
                "data": "monto_total",
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
                    return '<strong class="text-danger">S/. ' + parseFloat(data).toFixed(2) + '</strong>';
                }
            },
            { 
                "data": "fecha_vencimiento_mas_antigua",
                "render": function(data, type, row) {
                    if (data) {
                        var fecha = new Date(data);
                        var dias = row.dias_vencido_max;
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
                "data": "id_cliente",
                "render": function(data) {
                    return "<button class='ver_vales btn btn-primary btn-sm' title='Ver Vales'><i class='fas fa-eye'></i> Ver Vales</button>";
                }
            }
        ],
        "language": idioma_espanol,
        select: true
    });

    // Remover listeners anteriores y agregar nuevos
    $('#tabla_creditos_por_cliente tbody').off('click');
    $('#tabla_creditos_por_cliente tbody').on('click', '.ver_vales', function() {
        var data = tabla_creditos_por_cliente.row($(this).closest('tr')).data();
        Ver_Vales_Cliente(data.id_cliente, data.nombre_completo, data.dni_ruc, data.total_vales, data.saldo_pendiente);
    });
}

// VER VALES DE UN CLIENTE
function Ver_Vales_Cliente(id_cliente, nombre_cliente, dni, total_vales, saldo_total) {
    $('#txt_id_cliente_vales').val(id_cliente);
    $('#info_cliente_vales').text(nombre_cliente);
    $('#info_dni_vales').text(dni);
    $('#info_total_vales').text(total_vales);
    $('#info_saldo_total_vales').text('S/. ' + parseFloat(saldo_total).toFixed(2));
    
    // Actualizar el monto total a pagar en el botón
    $('#monto_total_pagar').text('S/. ' + parseFloat(saldo_total).toFixed(2));
    
    // Mostrar u ocultar el botón "Pagar Todo" según el saldo
    if (parseFloat(saldo_total) > 0) {
        $('#btn_pagar_todo_cliente').show();
    } else {
        $('#btn_pagar_todo_cliente').hide();
    }
    
    // Cargar tabla de vales
    if (tabla_vales_cliente) {
        tabla_vales_cliente.destroy();
    }
    
    tabla_vales_cliente = $("#tabla_vales_cliente").DataTable({
        "ordering": true,
        "bLengthChange": false,
        "searching": false,
        "pageLength": 10,
        "destroy": true,
        "processing": true,
        "rowCallback": function(row, data) {
            // Resaltar vales pagados con fondo verde claro
            if (data.estado == 'PAGADO') {
                $(row).css('background-color', '#d4edda');
            }
        },
        "ajax": {
            "url": "../controller/creditos/controlador_listar_vales_cliente.php",
            type: 'POST',
            data: {
                id_cliente: id_cliente,
                filtro_estado: '' // Mostrar TODOS los vales (pendientes y pagados)
            }
        },
        "columns": [
            { "data": "numero_vale" },
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
                            html += '<br><span class="badge badge-danger">Vencido</span>';
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
                "data": "id_credito",
                "render": function(data, type, row) {
                    var botones = "";
                    if (row.estado == 'PENDIENTE') {
                        // Botón de pagar
                        botones = "<button class='pagar_vale btn btn-success btn-sm' title='Registrar Pago'><i class='fas fa-money-bill-wave'></i></button>&nbsp;";
                        // Botón de editar
                        botones += "<button class='editar_vale btn btn-warning btn-sm' title='Editar'><i class='fas fa-edit'></i></button>&nbsp;";
                    }
                    // Siempre mostrar historial
                    botones += "<button class='historial_vale btn btn-info btn-sm' title='Ver Historial'><i class='fas fa-history'></i></button>";
                    return botones;
                }
            }
        ],
        "language": idioma_espanol
    });

    // Remover listeners anteriores y agregar nuevos
    $('#tabla_vales_cliente tbody').off('click');
    $('#tabla_vales_cliente tbody').on('click', '.pagar_vale', function() {
        var data = tabla_vales_cliente.row($(this).closest('tr')).data();
        $('#modal_vales_cliente').modal('hide');
        setTimeout(function() {
            Abrir_Modal_Pago(data.id_credito);
        }, 300);
    });
    
    $('#tabla_vales_cliente tbody').on('click', '.editar_vale', function() {
        var data = tabla_vales_cliente.row($(this).closest('tr')).data();
        $('#modal_vales_cliente').modal('hide');
        setTimeout(function() {
            Abrir_Modal_Editar_Credito(data.id_credito);
        }, 300);
    });
    
    $('#tabla_vales_cliente tbody').on('click', '.historial_vale', function() {
        var data = tabla_vales_cliente.row($(this).closest('tr')).data();
        $('#modal_vales_cliente').modal('hide');
        setTimeout(function() {
            Ver_Historial_Pagos(data.id_credito);
        }, 300);
    });
    
    $('#modal_vales_cliente').modal('show');
}


// FILTRAR CRÉDITOS
function Filtrar_Creditos() {
    Listar_Creditos_Por_Cliente();
    Cargar_Resumen_Creditos();
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
                    var rol_usuario = $("#txtprincipalrol").val(); // Obtener el rol del usuario
                    var botones = "";
                    
                    if (data == 'PENDIENTE') {
                        // Botón de pagar (todos pueden)
                        botones += "<button class='pagar btn btn-success btn-sm' title='Registrar Pago'><i class='fas fa-money-bill-wave'></i></button>&nbsp;";
                        // Botón de historial (todos pueden)
                        botones += "<button class='historial btn btn-info btn-sm' title='Ver Historial'><i class='fas fa-history'></i></button>";
                        // Botón de anular (solo ADMINISTRADOR)
                        if (rol_usuario == 'ADMINISTRADOR') {
                            botones += "&nbsp;<button class='anular btn btn-danger btn-sm' title='Anular'><i class='fas fa-ban'></i></button>";
                        }
                    } else {
                        // Solo historial para créditos pagados o anulados
                        botones = "<button class='historial btn btn-info btn-sm' title='Ver Historial'><i class='fas fa-history'></i></button>";
                    }
                    
                    return botones;
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
    
    // Detectar si es un pago total de cliente (múltiples créditos)
    var es_pago_total = id_credito.indexOf('CLIENTE_') === 0;
    var url_controlador = '';
    var datos_envio = {};
    
    if (es_pago_total) {
        // Pago total de todas las deudas del cliente
        var id_cliente = id_credito.replace('CLIENTE_', '');
        url_controlador = '../controller/creditos/controlador_pagar_todo_cliente.php';
        datos_envio = {
            id_cliente: id_cliente,
            id_tipo_pago: id_tipo_pago,
            codigo_operacion: codigo_operacion,
            monto_pagado: monto_pagado,
            id_usuario: id_usuario,
            observaciones: observaciones
        };
    } else {
        // Pago individual de un crédito
        url_controlador = '../controller/creditos/controlador_registrar_pago_credito.php';
        datos_envio = {
            id_credito: id_credito,
            id_tipo_pago: id_tipo_pago,
            codigo_operacion: codigo_operacion,
            monto_pagado: monto_pagado,
            id_usuario: id_usuario,
            observaciones: observaciones
        };
    }
    
    $.ajax({
        url: url_controlador,
        type: 'POST',
        data: datos_envio
    }).done(function(resp) {
        if (resp > 0) {
            Swal.fire({
                icon: 'success',
                title: 'Éxito',
                text: es_pago_total ? 'Pago total registrado correctamente' : 'Pago registrado correctamente',
                confirmButtonColor: '#023D77'
            });
            $('#modal_registrar_pago').modal('hide');
            Listar_Creditos_Por_Cliente();
            Cargar_Resumen_Creditos();
            Cargar_Top_Deudores();
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

// PAGAR TODO - TODAS LAS DEUDAS DEL CLIENTE
function Pagar_Todo_Cliente() {
    var id_cliente = $('#txt_id_cliente_vales').val();
    var nombre_cliente = $('#info_cliente_vales').text();
    var saldo_total = $('#info_saldo_total_vales').text().replace('S/. ', '');
    
    if (parseFloat(saldo_total) <= 0) {
        Swal.fire({
            icon: 'info',
            title: 'Información',
            text: 'No hay deudas pendientes para este cliente',
            confirmButtonColor: '#023D77'
        });
        return;
    }
    
    // Cerrar modal de vales
    $('#modal_vales_cliente').modal('hide');
    
    // Esperar a que se cierre el modal antes de abrir el nuevo
    setTimeout(function() {
        // Configurar el modal de pago para todas las deudas
        $('#txt_id_credito_pago').val('CLIENTE_' + id_cliente); // Indicador especial
        $('#info_cliente_pago').html('<strong>' + nombre_cliente + '</strong><br><span class="badge badge-warning">PAGO TOTAL DE TODAS LAS DEUDAS</span>');
        $('#info_vale_pago').text('MÚLTIPLES VALES');
        $('#info_monto_total_pago').text('S/. ' + parseFloat(saldo_total).toFixed(2));
        $('#info_saldo_pendiente_pago').text('S/. ' + parseFloat(saldo_total).toFixed(2));
        $('#max_monto_pago').text('S/. ' + parseFloat(saldo_total).toFixed(2));
        
        // Pre-llenar el monto con el saldo total
        $('#txt_monto_pago_credito').val(parseFloat(saldo_total).toFixed(2));
        
        Cargar_Tipos_Pago_Credito();
        
        $('#modal_registrar_pago').modal('show');
    }, 300);
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
                    Listar_Creditos_Por_Cliente();
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
            html += '<td><button class="btn btn-info btn-sm" onclick="Ver_Vales_Cliente(' + item.id_cliente + ', \'' + item.nombre_completo.replace(/'/g, "\\'") + '\', \'' + (item.dni_ruc ? item.dni_ruc : '-') + '\', ' + item.total_creditos + ', ' + parseFloat(item.saldo_pendiente).toFixed(2) + ')"><i class="fas fa-eye"></i> Ver</button></td>';
            html += '</tr>';
            contador++;
        });
        
        $('#tbody_top_deudores').html(html);
    });
}

// FILTRAR POR CLIENTE (mantener para compatibilidad)
function Filtrar_Por_Cliente(id_cliente, nombre_cliente, dni, total_vales, saldo_total) {
    // Abrir directamente el modal de vales del cliente
    Ver_Vales_Cliente(id_cliente, nombre_cliente, dni, total_vales, saldo_total);
}

// FILTRAR CRÉDITOS
function Filtrar_Creditos() {
    Listar_Creditos_Por_Cliente();
    Cargar_Resumen_Creditos();
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

// EXPORTAR CRÉDITOS A PDF
function Exportar_Creditos_PDF() {
    var filtro_cliente = $('#filtro_cliente').val() || '';
    var filtro_estado = $('#filtro_estado').val() || '';
    
    var url = '../controller/creditos/controlador_exportar_creditos_pdf.php?filtro_cliente=' + filtro_cliente + '&filtro_estado=' + filtro_estado;
    window.open(url, '_blank');
}

// EXPORTAR CRÉDITOS A EXCEL
function Exportar_Creditos_Excel() {
    var filtro_cliente = $('#filtro_cliente').val() || '';
    var filtro_estado = $('#filtro_estado').val() || '';
    
    var url = '../controller/creditos/controlador_exportar_creditos_excel.php?filtro_cliente=' + filtro_cliente + '&filtro_estado=' + filtro_estado;
    window.location.href = url;
}

// ABRIR MODAL AGREGAR CRÉDITO MANUAL
function Abrir_Modal_Agregar_Credito_Manual() {
    // Cargar clientes
    $.ajax({
        url: '../controller/clientes/controlador_clientes_activos.php',
        type: 'POST'
    }).done(function(resp) {
        var data = JSON.parse(resp);
        var opciones = '<option value="">-- Seleccione --</option>';
        data.forEach(function(item) {
            opciones += '<option value="' + item.id_cliente + '">' + item.nombre_completo + '</option>';
        });
        $("#txt_cliente_manual").html(opciones);
        
        // Inicializar Select2
        $('#txt_cliente_manual').select2({
            dropdownParent: $('#modal_agregar_credito_manual'),
            placeholder: '-- Seleccione --',
            allowClear: true
        });
    });
    
    // Limpiar formulario
    $('#txt_numero_vale_manual').val('');
    $('#txt_monto_manual').val('');
    $('#txt_fecha_vencimiento_manual').val('');
    $('#txt_observaciones_manual').val('');
    
    $('#modal_agregar_credito_manual').modal('show');
}

// AGREGAR CRÉDITO MANUAL
function Agregar_Credito_Manual() {
    var id_cliente = $('#txt_cliente_manual').val();
    var numero_vale = $('#txt_numero_vale_manual').val();
    var monto = $('#txt_monto_manual').val();
    var fecha_vencimiento = $('#txt_fecha_vencimiento_manual').val();
    var observaciones = $('#txt_observaciones_manual').val();
    
    console.log('Datos a enviar:', {id_cliente, numero_vale, monto, fecha_vencimiento, observaciones});
    
    if (!id_cliente || !numero_vale || !monto) {
        Swal.fire({
            icon: 'warning',
            title: 'Advertencia',
            text: 'Complete todos los campos obligatorios (Cliente, N° Vale y Monto)',
            confirmButtonColor: '#023D77'
        });
        return;
    }
    
    if (parseFloat(monto) <= 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Advertencia',
            text: 'El monto debe ser mayor a 0',
            confirmButtonColor: '#023D77'
        });
        return;
    }
    
    $.ajax({
        url: '../controller/creditos/controlador_agregar_credito_manual.php',
        type: 'POST',
        data: {
            id_cliente: id_cliente,
            numero_vale: numero_vale,
            monto: monto,
            fecha_vencimiento: fecha_vencimiento,
            observaciones: observaciones
        }
    }).done(function(resp) {
        console.log('Respuesta del servidor:', resp);
        console.log('Tipo de respuesta:', typeof resp);
        
        try {
            // Si ya es un objeto, no parsear
            var data = (typeof resp === 'object') ? resp : JSON.parse(resp);
            console.log('Datos procesados:', data);
            
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Éxito',
                    text: data.message,
                    confirmButtonColor: '#023D77'
                });
                $('#modal_agregar_credito_manual').modal('hide');
                Listar_Creditos_Por_Cliente();
                Cargar_Resumen_Creditos();
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: data.message,
                    confirmButtonColor: '#023D77'
                });
            }
        } catch(e) {
            console.error('Error al parsear respuesta:', e);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Error al procesar la respuesta del servidor',
                confirmButtonColor: '#023D77'
            });
        }
    }).fail(function(xhr, status, error) {
        console.error('Error en la petición:', error);
        console.error('Respuesta:', xhr.responseText);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Error al conectar con el servidor: ' + error,
            confirmButtonColor: '#023D77'
        });
    });
}

// ABRIR MODAL EDITAR CRÉDITO
function Abrir_Modal_Editar_Credito(id_credito) {
    $.ajax({
        url: '../controller/creditos/controlador_detalle_credito.php',
        type: 'POST',
        data: { id_credito: id_credito }
    }).done(function(resp) {
        var data = JSON.parse(resp);
        
        $('#txt_id_credito_editar').val(data.id_credito);
        $('#txt_numero_vale_editar').val(data.numero_vale);
        $('#txt_monto_editar').val(data.monto);
        $('#txt_fecha_vencimiento_editar').val(data.fecha_vencimiento);
        $('#txt_estado_editar').val(data.estado);
        $('#txt_observaciones_editar').val(data.observaciones);
        
        $('#modal_editar_credito').modal('show');
    });
}

// ACTUALIZAR CRÉDITO
function Actualizar_Credito() {
    var id_credito = $('#txt_id_credito_editar').val();
    var numero_vale = $('#txt_numero_vale_editar').val();
    var monto = $('#txt_monto_editar').val();
    var fecha_vencimiento = $('#txt_fecha_vencimiento_editar').val();
    var estado = $('#txt_estado_editar').val();
    var observaciones = $('#txt_observaciones_editar').val();
    
    if (monto && parseFloat(monto) <= 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Advertencia',
            text: 'El monto debe ser mayor a 0',
            confirmButtonColor: '#023D77'
        });
        return;
    }
    
    $.ajax({
        url: '../controller/creditos/controlador_editar_credito.php',
        type: 'POST',
        data: {
            id_credito: id_credito,
            numero_vale: numero_vale,
            monto: monto,
            fecha_vencimiento: fecha_vencimiento,
            estado: estado,
            observaciones: observaciones
        }
    }).done(function(resp) {
        var data = JSON.parse(resp);
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Éxito',
                text: data.message,
                confirmButtonColor: '#023D77'
            });
            $('#modal_editar_credito').modal('hide');
            Listar_Creditos_Por_Cliente();
            Cargar_Resumen_Creditos();
            
            // Si hay un modal de vales abierto, recargarlo
            if ($('#modal_vales_cliente').hasClass('show')) {
                var id_cliente = $('#txt_id_cliente_vales').val();
                if (tabla_vales_cliente) {
                    tabla_vales_cliente.ajax.reload();
                }
            }
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.message,
                confirmButtonColor: '#023D77'
            });
        }
    });
}

// EXPORTAR HISTORIAL DE VALES A PDF
function Exportar_Historial_Vales_PDF() {
    var id_cliente = $('#txt_id_cliente_vales').val();
    var filtro_estado = ''; // Exportar todos los vales del cliente
    
    if (!id_cliente) {
        Swal.fire({
            icon: 'warning',
            title: 'Advertencia',
            text: 'No se ha seleccionado un cliente',
            confirmButtonColor: '#023D77'
        });
        return;
    }
    
    var url = '../controller/creditos/controlador_exportar_historial_vales_pdf.php?id_cliente=' + id_cliente + '&filtro_estado=' + filtro_estado;
    window.open(url, '_blank');
}

// EXPORTAR HISTORIAL DE VALES A EXCEL
function Exportar_Historial_Vales_Excel() {
    var id_cliente = $('#txt_id_cliente_vales').val();
    var filtro_estado = ''; // Exportar todos los vales del cliente
    
    if (!id_cliente) {
        Swal.fire({
            icon: 'warning',
            title: 'Advertencia',
            text: 'No se ha seleccionado un cliente',
            confirmButtonColor: '#023D77'
        });
        return;
    }
    
    var url = '../controller/creditos/controlador_exportar_historial_vales_excel.php?id_cliente=' + id_cliente + '&filtro_estado=' + filtro_estado;
    window.location.href = url;
}
