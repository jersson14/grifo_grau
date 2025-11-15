// VERIFICAR SI HAY TURNO ABIERTO
function Verificar_Turno_Abierto() {
    var id_usuario = $("#txtprincipalid").val();
    
    $.ajax({
        url: '../controller/turnos/controlador_verificar_turno_abierto.php',
        type: 'POST',
        data: { id_usuario: id_usuario }
    }).done(function(resp) {
        if (resp > 0) {
            // Ya hay un turno abierto
            $("#alerta_turno_abierto").show();
            $("#formulario_abrir_turno").hide();
        } else {
            $("#alerta_turno_abierto").hide();
            $("#formulario_abrir_turno").show();
        }
    });
}

// CARGAR NÚMERO DE DOCUMENTO AUTOMÁTICO
function Cargar_Numero_Documento() {
    $.ajax({
        url: '../controller/turnos/controlador_generar_numero_documento.php',
        type: 'POST'
    }).done(function(resp) {
        $("#txt_numero_documento").val(resp);
    });
}

// CARGAR LECTURAS INICIALES
function Cargar_Lecturas_Iniciales() {
    $.ajax({
        url: '../controller/surtidores/controlador_surtidores_activos.php',
        type: 'POST'
    }).done(function(resp) {
        var data = JSON.parse(resp);
        
        var html_maquina_1 = '';
        var html_maquina_2 = '';
        
        data.forEach(function(item) {
            var fila = '<tr>';
            fila += '<td><strong>' + item.codigo + '</strong></td>';
            fila += '<td>' + item.producto_nombre + '</td>';
            fila += '<td><strong>' + parseFloat(item.lectura_actual).toFixed(3) + '</strong></td>';
            fila += '<td>S/. ' + parseFloat(item.precio_actual).toFixed(2) + '</td>';
            fila += '</tr>';
            
            if (item.numero_maquina == 1) {
                html_maquina_1 += fila;
            } else {
                html_maquina_2 += fila;
            }
        });
        
        $("#tabla_lecturas_maquina_1").html(html_maquina_1);
        $("#tabla_lecturas_maquina_2").html(html_maquina_2);
    });
}

// ABRIR TURNO
function Abrir_Turno() {
    var numero_documento = $("#txt_numero_documento").val();
    var fecha = $("#txt_fecha_turno").val();
    var turno = $("#txt_tipo_turno").val();
    var hora_inicio = $("#txt_hora_inicio").val();
    var hora_fin = $("#txt_hora_fin").val();
    var id_usuario = $("#txtprincipalid").val();
    
    if (fecha.length == 0 || turno.length == 0 || hora_inicio.length == 0 || hora_fin.length == 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Advertencia',
            text: 'Complete todos los campos obligatorios',
            confirmButtonColor: '#023D77'
        });
        return;
    }
    
    Swal.fire({
        title: '¿Abrir turno?',
        text: "Se registrarán las lecturas iniciales de todos los surtidores",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#023D77',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, abrir turno',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '../controller/turnos/controlador_abrir_turno.php',
                type: 'POST',
                data: {
                    numero_documento: numero_documento,
                    id_usuario: id_usuario,
                    turno: turno,
                    fecha: fecha,
                    hora_inicio: hora_inicio,
                    hora_fin: hora_fin
                }
            }).done(function(resp) {
                if (resp > 0) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Éxito',
                        text: 'Turno abierto correctamente',
                        confirmButtonColor: '#023D77'
                    }).then(() => {
                        cargar_contenido('contenido_principal', 'turnos/view_cerrar_turno.php');
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'No se pudo abrir el turno',
                        confirmButtonColor: '#023D77'
                    });
                }
            });
        }
    });
}

// ============================================
// CERRAR TURNO
// ============================================

var tabla_pagos_turno;
var tabla_creditos_turno;

function Cargar_Turno_Actual() {
    var id_usuario = $("#txtprincipalid").val();
    
    $.ajax({
        url: '../controller/turnos/controlador_obtener_turno_abierto.php',
        type: 'POST',
        data: { id_usuario: id_usuario }
    }).done(function(resp) {
        if (resp == '0') {
            $("#sin_turno_abierto").show();
            $("#formulario_cerrar_turno").hide();
        } else {
            var data = JSON.parse(resp);
            $("#sin_turno_abierto").hide();
            $("#formulario_cerrar_turno").show();
            
            // Llenar datos del turno
            $("#info_numero_documento").text(data.numero_documento);
            $("#info_fecha").text(data.fecha_reporte);
            $("#info_turno").text(data.turno);
            $("#info_hora_inicio").text(data.hora_inicio);
            $("#info_hora_fin").text(data.hora_fin);
            $("#txt_id_reporte").val(data.id_reporte);
            
            // Cargar lecturas
            Cargar_Lecturas_Turno(data.id_reporte);
            
            // Cargar pagos
            Listar_Pagos_Turno(data.id_reporte);
            
            // Cargar créditos
            Listar_Creditos_Turno(data.id_reporte);
        }
    });
}

function Cargar_Lecturas_Turno(id_reporte) {
    $.ajax({
        url: '../controller/turnos/controlador_obtener_lecturas_turno.php',
        type: 'POST',
        data: { id_reporte: id_reporte }
    }).done(function(resp) {
        var data = JSON.parse(resp);
        
        var html_maquina_1 = '';
        var html_maquina_2 = '';
        
        data.forEach(function(item) {
            var fila = '<tr>';
            fila += '<td><strong>' + item.codigo + '</strong></td>';
            fila += '<td>' + item.producto_nombre + '</td>';
            fila += '<td>' + parseFloat(item.lectura_anterior).toFixed(3) + '</td>';
            fila += '<td><input type="number" step="0.001" class="form-control form-control-sm lectura-actual" data-id="' + item.id_lectura + '" value="' + parseFloat(item.lectura_actual).toFixed(3) + '" onchange="Actualizar_Lectura_Turno(' + item.id_lectura + ', this.value)"></td>';
            fila += '<td class="galones-' + item.id_lectura + '">' + parseFloat(item.galones_vendidos).toFixed(3) + '</td>';
            fila += '<td>S/. ' + parseFloat(item.precio_galon).toFixed(2) + '</td>';
            fila += '<td class="total-' + item.id_lectura + '">S/. ' + parseFloat(item.total_soles).toFixed(2) + '</td>';
            fila += '</tr>';
            
            if (item.numero_maquina == 1) {
                html_maquina_1 += fila;
            } else {
                html_maquina_2 += fila;
            }
        });
        
        $("#tabla_lecturas_cerrar_maquina_1").html(html_maquina_1);
        $("#tabla_lecturas_cerrar_maquina_2").html(html_maquina_2);
        
        Calcular_Totales_Turno();
    });
}

function Actualizar_Lectura_Turno(id_lectura, lectura_actual) {
    $.ajax({
        url: '../controller/turnos/controlador_actualizar_lectura_turno.php',
        type: 'POST',
        data: {
            id_lectura: id_lectura,
            lectura_actual: lectura_actual
        }
    }).done(function(resp) {
        if (resp > 0) {
            // Recargar lecturas
            var id_reporte = $("#txt_id_reporte").val();
            Cargar_Lecturas_Turno(id_reporte);
        }
    });
}

function Calcular_Totales_Turno() {
    var total_diesel = 0;
    var total_regular = 0;
    var total_premium = 0;
    var galones_diesel = 0;
    var galones_regular = 0;
    var galones_premium = 0;
    
    // Aquí deberías calcular los totales desde el servidor
    // Por ahora solo actualiza la vista
    
    $("#total_diesel").text('S/. 0.00');
    $("#total_regular").text('S/. 0.00');
    $("#total_premium").text('S/. 0.00');
    $("#total_ventas").text('S/. 0.00');
}

// PAGOS
function Listar_Pagos_Turno(id_reporte) {
    if (tabla_pagos_turno) {
        tabla_pagos_turno.destroy();
    }
    
    tabla_pagos_turno = $("#tabla_pagos_turno").DataTable({
        "ordering": false,
        "bLengthChange": false,
        "searching": false,
        "pageLength": 10,
        "destroy": true,
        "processing": true,
        "ajax": {
            "url": "../controller/turnos/controlador_listar_pagos_turno.php",
            "type": "POST",
            "data": { id_reporte: id_reporte }
        },
        "columns": [
            { "data": "tipo_pago_nombre" },
            { 
                "data": "codigo_operacion",
                "render": function(data) {
                    return data ? data : '-';
                }
            },
            { 
                "data": "monto",
                "render": function(data) {
                    return 'S/. ' + parseFloat(data).toFixed(2);
                }
            },
            {
                "data": "id_pago_reporte",
                "render": function(data) {
                    return "<button class='btn btn-danger btn-sm' onclick='Eliminar_Pago(" + data + ")'><i class='fas fa-trash'></i></button>";
                }
            }
        ],
        "language": idioma_espanol
    });
}

function Abrir_Modal_Agregar_Pago() {
    $("#modal_agregar_pago").modal('show');
    Cargar_Tipos_Pago();
}

function Cargar_Tipos_Pago() {
    $.ajax({
        url: '../controller/turnos/controlador_tipos_pago.php',
        type: 'POST'
    }).done(function(resp) {
        var data = JSON.parse(resp);
        var opciones = '<option value="">-- Seleccione --</option>';
        data.forEach(function(item) {
            opciones += '<option value="' + item.id_tipo_pago + '" data-requiere="' + item.requiere_codigo + '">' + item.nombre + '</option>';
        });
        $("#txt_tipo_pago").html(opciones);
    });
}

function Agregar_Pago() {
    var id_reporte = $("#txt_id_reporte").val();
    var id_tipo_pago = $("#txt_tipo_pago").val();
    var codigo_operacion = $("#txt_codigo_operacion").val();
    var monto = $("#txt_monto_pago").val();
    var observaciones = $("#txt_observaciones_pago").val();
    
    if (id_tipo_pago.length == 0 || monto.length == 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Advertencia',
            text: 'Complete los campos obligatorios',
            confirmButtonColor: '#023D77'
        });
        return;
    }
    
    $.ajax({
        url: '../controller/turnos/controlador_registrar_pago.php',
        type: 'POST',
        data: {
            id_reporte: id_reporte,
            id_tipo_pago: id_tipo_pago,
            codigo_operacion: codigo_operacion,
            monto: monto,
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
            $("#modal_agregar_pago").modal('hide');
            Listar_Pagos_Turno(id_reporte);
            Limpiar_Modal_Pago();
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

function Limpiar_Modal_Pago() {
    $("#txt_tipo_pago").val('');
    $("#txt_codigo_operacion").val('');
    $("#txt_monto_pago").val('');
    $("#txt_observaciones_pago").val('');
}

function Eliminar_Pago(id_pago) {
    Swal.fire({
        title: '¿Eliminar pago?',
        text: "Esta acción no se puede deshacer",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#023D77',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '../controller/turnos/controlador_eliminar_pago.php',
                type: 'POST',
                data: { id_pago: id_pago }
            }).done(function(resp) {
                if (resp > 0) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Éxito',
                        text: 'Pago eliminado correctamente',
                        confirmButtonColor: '#023D77'
                    });
                    var id_reporte = $("#txt_id_reporte").val();
                    Listar_Pagos_Turno(id_reporte);
                }
            });
        }
    });
}

// CRÉDITOS
function Listar_Creditos_Turno(id_reporte) {
    if (tabla_creditos_turno) {
        tabla_creditos_turno.destroy();
    }
    
    tabla_creditos_turno = $("#tabla_creditos_turno").DataTable({
        "ordering": false,
        "bLengthChange": false,
        "searching": false,
        "pageLength": 10,
        "destroy": true,
        "processing": true,
        "ajax": {
            "url": "../controller/turnos/controlador_listar_creditos_turno.php",
            "type": "POST",
            "data": { id_reporte: id_reporte }
        },
        "columns": [
            { "data": "cliente_nombre" },
            { "data": "numero_vale" },
            { 
                "data": "monto",
                "render": function(data) {
                    return 'S/. ' + parseFloat(data).toFixed(2);
                }
            },
            {
                "data": "id_credito",
                "render": function(data) {
                    return "<button class='btn btn-danger btn-sm' onclick='Eliminar_Credito(" + data + ")'><i class='fas fa-trash'></i></button>";
                }
            }
        ],
        "language": idioma_espanol
    });
}

function Abrir_Modal_Agregar_Credito() {
    $("#modal_agregar_credito").modal('show');
    Cargar_Clientes_Activos();
}

function Cargar_Clientes_Activos() {
    $.ajax({
        url: '../controller/clientes/controlador_clientes_activos.php',
        type: 'POST'
    }).done(function(resp) {
        var data = JSON.parse(resp);
        var opciones = '<option value="">-- Seleccione --</option>';
        data.forEach(function(item) {
            opciones += '<option value="' + item.id_cliente + '">' + item.nombre_completo + '</option>';
        });
        $("#txt_cliente_credito").html(opciones);
    });
}

function Agregar_Credito() {
    var id_reporte = $("#txt_id_reporte").val();
    var id_cliente = $("#txt_cliente_credito").val();
    var numero_vale = $("#txt_numero_vale").val();
    var monto = $("#txt_monto_credito").val();
    var fecha_vencimiento = $("#txt_fecha_vencimiento").val();
    var observaciones = $("#txt_observaciones_credito").val();
    
    if (id_cliente.length == 0 || numero_vale.length == 0 || monto.length == 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Advertencia',
            text: 'Complete los campos obligatorios',
            confirmButtonColor: '#023D77'
        });
        return;
    }
    
    $.ajax({
        url: '../controller/turnos/controlador_registrar_credito.php',
        type: 'POST',
        data: {
            id_reporte: id_reporte,
            id_cliente: id_cliente,
            numero_vale: numero_vale,
            monto: monto,
            fecha_vencimiento: fecha_vencimiento,
            observaciones: observaciones
        }
    }).done(function(resp) {
        if (resp > 0) {
            Swal.fire({
                icon: 'success',
                title: 'Éxito',
                text: 'Crédito registrado correctamente',
                confirmButtonColor: '#023D77'
            });
            $("#modal_agregar_credito").modal('hide');
            Listar_Creditos_Turno(id_reporte);
            Limpiar_Modal_Credito();
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'No se pudo registrar el crédito',
                confirmButtonColor: '#023D77'
            });
        }
    });
}

function Limpiar_Modal_Credito() {
    $("#txt_cliente_credito").val('');
    $("#txt_numero_vale").val('');
    $("#txt_monto_credito").val('');
    $("#txt_fecha_vencimiento").val('');
    $("#txt_observaciones_credito").val('');
}

function Eliminar_Credito(id_credito) {
    Swal.fire({
        title: '¿Eliminar crédito?',
        text: "Esta acción no se puede deshacer",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#023D77',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '../controller/turnos/controlador_eliminar_credito.php',
                type: 'POST',
                data: { id_credito: id_credito }
            }).done(function(resp) {
                if (resp > 0) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Éxito',
                        text: 'Crédito eliminado correctamente',
                        confirmButtonColor: '#023D77'
                    });
                    var id_reporte = $("#txt_id_reporte").val();
                    Listar_Creditos_Turno(id_reporte);
                }
            });
        }
    });
}

// CERRAR TURNO
function Cerrar_Turno_Final() {
    var id_reporte = $("#txt_id_reporte").val();
    var descuentos = $("#txt_descuentos").val() || 0;
    var otros_gastos = $("#txt_otros_gastos").val() || 0;
    
    Swal.fire({
        title: '¿Cerrar turno?',
        text: "Se calcularán los totales y se actualizarán las lecturas de los surtidores",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#023D77',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, cerrar turno',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '../controller/turnos/controlador_cerrar_turno.php',
                type: 'POST',
                data: {
                    id_reporte: id_reporte,
                    descuentos: descuentos,
                    otros_gastos: otros_gastos
                }
            }).done(function(resp) {
                if (resp > 0) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Éxito',
                        text: 'Turno cerrado correctamente',
                        confirmButtonColor: '#023D77'
                    }).then(() => {
                        cargar_contenido('contenido_principal', 'turnos/view_historial.php');
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'No se pudo cerrar el turno',
                        confirmButtonColor: '#023D77'
                    });
                }
            });
        }
    });
}
