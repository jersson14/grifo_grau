// CARGAR LISTA DE GRIFEROS
function Cargar_Griferos() {
    $.ajax({
        url: '../controller/usuario/controlador_listar_griferos.php',
        type: 'POST'
    }).done(function(resp) {
        var data = JSON.parse(resp);
        var opciones = '<option value="">-- Seleccione Grifero --</option>';
        
        data.forEach(function(item) {
            opciones += '<option value="' + item.id_usuario + '">' + item.nombre_completo + '</option>';
        });
        
        $("#txt_grifero").html(opciones);
        
        // Si el usuario logueado es grifero, seleccionarlo automáticamente
        var rol_usuario = $("#txtprincipalrol").val();
        if (rol_usuario == 'GRIFERO') {
            var id_usuario = $("#txtprincipalid").val();
            $("#txt_grifero").val(id_usuario);
            $("#txt_grifero").prop('disabled', true); // Deshabilitar para que no pueda cambiar
        }
    });
}

// VERIFICAR SI HAY TURNO ABIERTO EN EL SISTEMA
function Verificar_Turno_Abierto() {
    $.ajax({
        url: '../controller/turnos/controlador_verificar_turno_sistema.php',
        type: 'POST'
    }).done(function(resp) {
        if (resp > 0) {
            // Ya hay un turno abierto en el sistema
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
    var id_grifero = $("#txt_grifero").val();
    
    if (fecha.length == 0 || turno.length == 0 || hora_inicio.length == 0 || hora_fin.length == 0 || id_grifero.length == 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Advertencia',
            text: 'Complete todos los campos obligatorios (incluyendo el grifero)',
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
                dataType: 'json',
                data: {
                    numero_documento: numero_documento,
                    id_usuario: id_grifero,
                    turno: turno,
                    fecha: fecha,
                    hora_inicio: hora_inicio,
                    hora_fin: hora_fin
                }
            }).done(function(resp) {
                console.log('Respuesta del servidor:', resp);
                
                if (resp.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Éxito',
                        text: resp.message,
                        confirmButtonColor: '#023D77'
                    }).then(() => {
                        cargar_contenido('contenido_principal', 'turnos/view_abrir_turno.php');
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: resp.message,
                        confirmButtonColor: '#023D77'
                    });
                }
            }).fail(function(xhr, status, error) {
                console.error('Error en la petición:', error);
                console.error('Respuesta del servidor:', xhr.responseText);
                Swal.fire({
                    icon: 'error',
                    title: 'Error de conexión',
                    text: 'No se pudo conectar con el servidor.',
                    confirmButtonColor: '#023D77'
                });
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
    // El ADMINISTRADOR gestiona el turno abierto del sistema (sin importar el grifero)
    $.ajax({
        url: '../controller/turnos/controlador_obtener_turno_sistema.php',
        type: 'POST'
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
            $("#info_grifero").text(data.grifero_nombre); // Mostrar nombre del grifero
            $("#txt_id_reporte").val(data.id_reporte);
            
            // Cargar lecturas
            Cargar_Lecturas_Turno(data.id_reporte);
            
            // Cargar pagos y créditos con el NUEVO sistema estilo Excel
            Cargar_Pagos_Iniciales();
            Cargar_Creditos_Iniciales();
        }
    });
}

// Variable global para el debounce
var timeout_debounce;

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
            // Determinar el producto para los cálculos locales
            var producto_tipo = '';
            var nombre_upper = item.producto_nombre.toUpperCase();
            
            if (nombre_upper.includes('DIESEL')) producto_tipo = 'DIESEL';
            else if (nombre_upper.includes('REGULAR')) producto_tipo = 'REGULAR';
            else if (nombre_upper.includes('PREMIUM')) producto_tipo = 'PREMIUM';

            var fila = '<tr>';
            fila += '<td><strong>' + item.codigo + '</strong></td>';
            fila += '<td>' + item.producto_nombre + '</td>';
            fila += '<td>' + parseFloat(item.lectura_anterior).toFixed(3) + '</td>';
            
            // Input con clase 'input-lectura' y data attributes para cálculo local
            // Se eliminó el onchange inline
            fila += '<td><input type="number" step="0.001" class="form-control form-control-sm input-lectura" ' + 
                    'data-id="' + item.id_lectura + '" ' +
                    'data-lectura-anterior="' + item.lectura_anterior + '" ' +
                    'data-precio="' + item.precio_galon + '" ' +
                    'data-producto="' + producto_tipo + '" ' +
                    'value="' + parseFloat(item.lectura_actual).toFixed(3) + '"></td>';
            
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
        
        // Calcular totales iniciales
        Calcular_Totales_Locales();
    });
}

// Evento global para detectar cambios en tiempo real
$(document).on('input', '.input-lectura', function() {
    // 1. Cálculo local inmediato (UI update)
    Calcular_Fila_Local(this);
    Calcular_Totales_Locales();
    
    // 2. Guardado en servidor con debounce (esperar a que deje de escribir)
    var id_lectura = $(this).data('id');
    var lectura_actual = $(this).val();
    
    clearTimeout(timeout_debounce);
    timeout_debounce = setTimeout(function() {
        Actualizar_Lectura_Turno(id_lectura, lectura_actual);
    }, 800); // Esperar 800ms después de la última tecla
});

function Calcular_Fila_Local(input) {
    var id_lectura = $(input).data('id');
    var lectura_anterior = parseFloat($(input).data('lectura-anterior'));
    var precio = parseFloat($(input).data('precio'));
    var lectura_actual = parseFloat($(input).val());
    
    if (isNaN(lectura_actual)) lectura_actual = 0;
    
    var galones = lectura_actual - lectura_anterior;
    // Evitar negativos visuales si es menor a la anterior (aunque validación final es en server)
    // if (galones < 0) galones = 0; 
    
    var total_soles = galones * precio;
    
    // Actualizar celdas de la fila
    $(".galones-" + id_lectura).text(galones.toFixed(3));
    $(".total-" + id_lectura).text('S/. ' + total_soles.toFixed(2));
    
    // Guardar el total calculado en el input para facilitar la suma total
    $(input).attr('data-total-calculado', total_soles);
}

function Calcular_Totales_Locales() {
    var total_diesel = 0;
    var total_regular = 0;
    var total_premium = 0;
    var total_ventas = 0;
    
    // Recorrer todos los inputs de lectura
    $(".input-lectura").each(function() {
        var producto = $(this).data('producto');
        // Usar el total calculado previamente o calcularlo si no existe
        var total_fila = parseFloat($(this).attr('data-total-calculado'));
        
        if (isNaN(total_fila)) {
            // Si carga por primera vez, tomar del HTML original (parseando el texto de la celda total)
            // Pero es más seguro recalcular
            var id_lectura = $(this).data('id');
            var lectura_anterior = parseFloat($(this).data('lectura-anterior'));
            var precio = parseFloat($(this).data('precio'));
            var lectura_actual = parseFloat($(this).val());
            if (isNaN(lectura_actual)) lectura_actual = 0;
            var galones = lectura_actual - lectura_anterior;
            total_fila = galones * precio;
            $(this).attr('data-total-calculado', total_fila);
        }
        
        if (producto == 'DIESEL') total_diesel += total_fila;
        else if (producto == 'REGULAR') total_regular += total_fila;
        else if (producto == 'PREMIUM') total_premium += total_fila;
        
        total_ventas += total_fila;
    });
    
    // Actualizar tarjetas de resumen
    $("#total_diesel").text('S/. ' + total_diesel.toFixed(2));
    $("#total_regular").text('S/. ' + total_regular.toFixed(2));
    $("#total_premium").text('S/. ' + total_premium.toFixed(2));
    $("#total_ventas").text('S/. ' + total_ventas.toFixed(2));
    
    // Actualizar Cuadre de Caja Localmente
    Actualizar_Cuadre_Caja_Local(total_ventas);
}

function Actualizar_Cuadre_Caja_Local(total_ventas) {
    // Obtener valores actuales del DOM (que ya deberían estar cargados)
    // Nota: Para pagos y créditos, necesitamos sumar lo que hay en las tablas o variables globales
    // Por simplicidad y rapidez, podemos leer los totales que ya están en el cuadre, 
    // PERO lo correcto es recalcular todo.
    
    // Vamos a confiar en que las funciones de agregar/eliminar pago/crédito actualizan el DOM del cuadre
    // Así que solo necesitamos actualizar la parte de "Total Ventas" y recalcular el faltante.
    
    $("#cuadre_total_ventas").text('S/. ' + total_ventas.toFixed(2));
    
    // Leer los otros valores del DOM (quitando 'S/. ' y comas si hubiera)
    var total_pagos = parseFloat($("#cuadre_total_pagos").text().replace('S/. ', '')) || 0;
    var total_creditos = parseFloat($("#cuadre_total_creditos").text().replace('S/. ', '')) || 0;
    var descuentos = parseFloat($("#txt_descuentos").val()) || 0;
    var otros_gastos = parseFloat($("#txt_otros_gastos").val()) || 0;
    var monto_efectivo = parseFloat($("#txt_monto_efectivo").val()) || 0;
    
    // Actualizar textos de descuentos/gastos por si cambiaron
    $("#cuadre_descuentos").text('S/. ' + descuentos.toFixed(2));
    $("#cuadre_otros_gastos").text('S/. ' + otros_gastos.toFixed(2));
    $("#cuadre_monto_efectivo").text('S/. ' + monto_efectivo.toFixed(2));
    
    // Cálculo del faltante/sobrante
    // Faltante = (Ventas - Descuentos) - (Pagos + Créditos + Gastos + Efectivo)
    // Si es positivo: Faltante (falta dinero en caja) -> En realidad la lógica suele ser:
    // Dinero que debería haber = Ventas - Descuentos
    // Dinero que hay/justificado = Pagos + Créditos + Gastos + Efectivo
    // Diferencia = (Pagos + Créditos + Gastos + Efectivo) - (Ventas - Descuentos)
    
    // Revisando lógica original del servidor (controlador_cuadre_caja.php):
    // $total_justificado = $total_pagos + $total_creditos + $otros_gastos;
    // $total_neto_ventas = $total_ventas - $descuentos;
    // $diferencia = $total_justificado - $total_neto_ventas;
    
    var total_justificado = total_pagos + total_creditos + otros_gastos + monto_efectivo;
    var total_neto_ventas = total_ventas - descuentos;
    var diferencia = total_justificado - total_neto_ventas;
    
    var diferencia_text = 'S/. ' + Math.abs(diferencia).toFixed(2);
    
    if (diferencia < -0.001) { // Usar pequeña tolerancia por decimales
        $("#cuadre_faltante").html('<span class="text-danger">' + diferencia_text + ' (FALTANTE)</span>');
    } else if (diferencia > 0.001) {
        $("#cuadre_faltante").html('<span class="text-success">' + diferencia_text + ' (SOBRANTE)</span>');
    } else {
        $("#cuadre_faltante").html('<span class="text-success">S/. 0.00 (CUADRADO)</span>');
    }
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
            console.log('Lectura guardada en servidor correctamente');
            // NO recargamos la tabla para no perder el foco del input
            // Tampoco recalculamos totales porque ya se hizo localmente
        } else {
            console.error('Error al guardar lectura en servidor');
        }
    });
}

// Mantenemos esta función para compatibilidad, pero ahora delega en la local
function Calcular_Totales_Turno() {
    Calcular_Totales_Locales();
}

// Mantenemos esta función para compatibilidad
function Actualizar_Cuadre_Caja() {
    // Reutilizamos la lógica local, pasando el total de ventas actual calculado
    var total_ventas = 0;
    $(".input-lectura").each(function() {
        var val = parseFloat($(this).attr('data-total-calculado'));
        if (!isNaN(val)) total_ventas += val;
    });
    Actualizar_Cuadre_Caja_Local(total_ventas);
}

// PAGOS
function Listar_Pagos_Turno(id_reporte) {
    if (tabla_pagos_turno) {
        tabla_pagos_turno.destroy();
    }
    
    tabla_pagos_turno = $("#tabla_pagos_turno").DataTable({
        "ordering": false,
        "paging": false,
        "searching": false,
        "info": false,
        "destroy": true,
        "processing": true,
        "dom": 't',
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
        "language": Object.assign({}, idioma_espanol, {
            "emptyTable": "<div style='padding:20px; text-align:center; color:#6c757d;'><i class='fas fa-info-circle' style='font-size:24px;'></i><br><br>No hay pagos registrados.<br>Haz clic en <strong>'+ Agregar Pago'</strong> para comenzar.</div>"
        }),
        "drawCallback": function(settings) {
            var api = this.api();
            var total = 0;
            
            // Calcular total sumando la columna de monto (índice 2)
            var data = api.column(2).data();
            
            if (data.length > 0) {
                total = data.reduce(function(a, b) {
                    return parseFloat(a) + parseFloat(b);
                }, 0);
            }
            
            $("#cuadre_total_pagos").text('S/. ' + total.toFixed(2));
            Actualizar_Cuadre_Caja();
        }
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
            Actualizar_Cuadre_Caja();
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
                    Actualizar_Cuadre_Caja();
                }
            });
        }
    });
}

// ============================================
// NUEVO SISTEMA DE PAGOS ESTILO EXCEL
// ============================================

var contador_filas_pago = 0;
var pagos_data = []; // Array para almacenar los pagos

function Cargar_Pagos_Iniciales() {
    var id_reporte = $("#txt_id_reporte").val();
    
    $.ajax({
        url: '../controller/turnos/controlador_listar_pagos_turno.php',
        type: 'POST',
        data: { id_reporte: id_reporte }
    }).done(function(resp) {
        var data = JSON.parse(resp);
        pagos_data = data.data || [];
        
        $("#tbody_pagos_editable").empty();
        
        if (pagos_data.length === 0) {
            // Agregar 3 filas vacías por defecto
            for (var i = 0; i < 3; i++) {
                Agregar_Fila_Pago();
            }
        } else {
            // Cargar pagos existentes
            pagos_data.forEach(function(pago) {
                Agregar_Fila_Pago(pago);
            });
        }
        
        Actualizar_Total_Pagos();
    });
}

function Agregar_Fila_Pago(datos = null) {
    contador_filas_pago++;
    var fila_id = 'pago_' + contador_filas_pago;
    
    // Cargar tipos de pago
    $.ajax({
        url: '../controller/turnos/controlador_tipos_pago.php',
        type: 'POST',
        async: false
    }).done(function(resp) {
        var tipos = JSON.parse(resp);
        var opciones = '<option value="">-- Seleccione --</option>';
        tipos.forEach(function(tipo) {
            var selected = (datos && datos.id_tipo_pago == tipo.id_tipo_pago) ? 'selected' : '';
            opciones += '<option value="' + tipo.id_tipo_pago + '" ' + selected + '>' + tipo.nombre + '</option>';
        });
        
        var fila = '<tr id="' + fila_id + '" data-id-pago="' + (datos ? datos.id_pago_reporte : '0') + '">';
        fila += '<td><select class="form-control form-control-sm tipo-pago-select" onchange="Guardar_Pago_Fila(\'' + fila_id + '\')">' + opciones + '</select></td>';
        fila += '<td><input type="text" class="form-control form-control-sm codigo-operacion-input" value="' + (datos ? datos.codigo_operacion || '' : '') + '" onchange="Guardar_Pago_Fila(\'' + fila_id + '\')"></td>';
        fila += '<td><input type="number" step="0.01" class="form-control form-control-sm monto-pago-input" value="' + (datos ? datos.monto : '0') + '" onchange="Guardar_Pago_Fila(\'' + fila_id + '\')"></td>';
        fila += '<td><input type="text" class="form-control form-control-sm observaciones-input" value="' + (datos ? datos.observaciones || '' : '') + '" onchange="Guardar_Pago_Fila(\'' + fila_id + '\')"></td>';
        fila += '<td><button class="btn btn-danger btn-sm" onclick="Eliminar_Fila_Pago(\'' + fila_id + '\')"><i class="fas fa-trash"></i></button></td>';
        fila += '</tr>';
        
        $("#tbody_pagos_editable").append(fila);
    });
}

function Guardar_Pago_Fila(fila_id) {
    var fila = $("#" + fila_id);
    var id_reporte = $("#txt_id_reporte").val();
    var id_pago = fila.data('id-pago');
    var id_tipo_pago = fila.find('.tipo-pago-select').val();
    var codigo_operacion = fila.find('.codigo-operacion-input').val();
    var monto = fila.find('.monto-pago-input').val();
    var observaciones = fila.find('.observaciones-input').val();
    
    // Validar que al menos tenga tipo de pago y monto
    if (!id_tipo_pago || !monto || parseFloat(monto) <= 0) {
        return; // No guardar si no hay datos válidos
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
            // Actualizar el ID del pago en la fila
            if (id_pago == '0') {
                fila.data('id-pago', resp);
            }
            Actualizar_Total_Pagos();
            Actualizar_Cuadre_Caja();
        }
    });
}

function Eliminar_Fila_Pago(fila_id) {
    var fila = $("#" + fila_id);
    var id_pago = fila.data('id-pago');
    
    if (id_pago && id_pago != '0') {
        // Eliminar del servidor
        $.ajax({
            url: '../controller/turnos/controlador_eliminar_pago.php',
            type: 'POST',
            data: { id_pago: id_pago }
        }).done(function(resp) {
            if (resp > 0) {
                fila.remove();
                Actualizar_Total_Pagos();
                Actualizar_Cuadre_Caja();
            }
        });
    } else {
        // Solo eliminar la fila visual
        fila.remove();
    }
}

function Actualizar_Total_Pagos() {
    var total = 0;
    $("#tbody_pagos_editable tr").each(function() {
        var monto = parseFloat($(this).find('.monto-pago-input').val()) || 0;
        total += monto;
    });
    
    $("#cuadre_total_pagos").text('S/. ' + total.toFixed(2));
}

// CRÉDITOS - Las funciones de modal antiguas han sido eliminadas
// Ahora se usa el sistema de tabla editable (ver funciones más abajo)



// ============================================
// NUEVO SISTEMA DE CRÉDITOS ESTILO EXCEL
// ============================================

var contador_filas_credito = 0;
var creditos_data = [];

function Cargar_Creditos_Iniciales() {
    var id_reporte = $("#txt_id_reporte").val();
    
    $.ajax({
        url: '../controller/turnos/controlador_listar_creditos_turno.php',
        type: 'POST',
        data: { id_reporte: id_reporte }
    }).done(function(resp) {
        var data = JSON.parse(resp);
        creditos_data = data.data || [];
        
        $("#tbody_creditos_editable").empty();
        
        if (creditos_data.length === 0) {
            // Agregar 3 filas vacías por defecto
            for (var i = 0; i < 3; i++) {
                Agregar_Fila_Credito();
            }
        } else {
            // Cargar créditos existentes
            creditos_data.forEach(function(credito) {
                Agregar_Fila_Credito(credito);
            });
        }
        
        Actualizar_Total_Creditos();
    });
}

function Agregar_Fila_Credito(datos = null) {
    contador_filas_credito++;
    var fila_id = 'credito_' + contador_filas_credito;
    
    // Cargar clientes
    $.ajax({
        url: '../controller/clientes/controlador_clientes_activos.php',
        type: 'POST',
        async: false
    }).done(function(resp) {
        var clientes = JSON.parse(resp);
        var opciones = '<option value="">-- Seleccione --</option>';
        clientes.forEach(function(cliente) {
            var selected = (datos && datos.id_cliente == cliente.id_cliente) ? 'selected' : '';
            opciones += '<option value="' + cliente.id_cliente + '" ' + selected + '>' + cliente.nombre_completo + '</option>';
        });
        
        var fila = '<tr id="' + fila_id + '" data-id-credito="' + (datos ? datos.id_credito : '0') + '">';
        fila += '<td><select class="form-control form-control-sm cliente-select select2-cliente-turno" data-fila-id="' + fila_id + '" onchange="Guardar_Credito_Fila(\'' + fila_id + '\')">' + opciones + '</select></td>';
        fila += '<td><input type="text" class="form-control form-control-sm numero-vale-input" value="' + (datos ? datos.numero_vale : '') + '" onchange="Guardar_Credito_Fila(\'' + fila_id + '\')"></td>';
        fila += '<td><input type="number" step="0.01" class="form-control form-control-sm monto-credito-input" value="' + (datos ? datos.monto : '0') + '" onchange="Guardar_Credito_Fila(\'' + fila_id + '\')"></td>';
        fila += '<td><input type="date" class="form-control form-control-sm fecha-vencimiento-input" value="' + (datos ? datos.fecha_vencimiento || '' : '') + '" onchange="Guardar_Credito_Fila(\'' + fila_id + '\')"></td>';
        fila += '<td><button class="btn btn-danger btn-sm" onclick="Eliminar_Fila_Credito(\'' + fila_id + '\')"><i class="fas fa-trash"></i></button></td>';
        fila += '</tr>';
        
        $("#tbody_creditos_editable").append(fila);
        
        // Inicializar Select2 para el select recién agregado
        $('#' + fila_id + ' .select2-cliente-turno').select2({
            placeholder: '-- Seleccione --',
            allowClear: true,
            width: '100%',
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

function Guardar_Credito_Fila(fila_id) {
    var fila = $("#" + fila_id);
    var id_reporte = $("#txt_id_reporte").val();
    var id_credito = fila.data('id-credito');
    var id_cliente = fila.find('.cliente-select').val();
    var numero_vale = fila.find('.numero-vale-input').val();
    var monto = fila.find('.monto-credito-input').val();
    var fecha_vencimiento = fila.find('.fecha-vencimiento-input').val();
    
    // Validar que al menos tenga cliente, número de vale y monto
    if (!id_cliente || !numero_vale || !monto || parseFloat(monto) <= 0) {
        return; // No guardar si no hay datos válidos
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
            observaciones: ''
        }
    }).done(function(resp) {
        if (resp > 0) {
            // Actualizar el ID del crédito en la fila
            if (id_credito == '0') {
                fila.data('id-credito', resp);
            }
            Actualizar_Total_Creditos();
            Actualizar_Cuadre_Caja();
        }
    });
}

function Eliminar_Fila_Credito(fila_id) {
    var fila = $("#" + fila_id);
    var id_credito = fila.data('id-credito');
    
    // Destruir Select2 antes de eliminar la fila
    fila.find('.select2-cliente-turno').each(function() {
        if ($(this).hasClass("select2-hidden-accessible")) {
            $(this).select2('destroy');
        }
    });
    
    if (id_credito && id_credito != '0') {
        // Eliminar del servidor
        $.ajax({
            url: '../controller/turnos/controlador_eliminar_credito.php',
            type: 'POST',
            data: { id_credito: id_credito }
        }).done(function(resp) {
            if (resp > 0) {
                fila.remove();
                Actualizar_Total_Creditos();
                Actualizar_Cuadre_Caja();
            }
        });
    } else {
        // Solo eliminar la fila visual
        fila.remove();
    }
}

function Actualizar_Total_Creditos() {
    var total = 0;
    $("#tbody_creditos_editable tr").each(function() {
        var monto = parseFloat($(this).find('.monto-credito-input').val()) || 0;
        total += monto;
    });
    
    $("#cuadre_total_creditos").text('S/. ' + total.toFixed(2));
}

// Actualizar cuadre cuando cambian los descuentos u otros gastos
$(document).on('change', '#txt_descuentos, #txt_otros_gastos, #txt_monto_efectivo', function() {
    Actualizar_Cuadre_Caja();
});

// CERRAR TURNO
function Cerrar_Turno_Final() {
    var id_reporte = $("#txt_id_reporte").val();
    var descuentos = $("#txt_descuentos").val() || 0;
    var otros_gastos = $("#txt_otros_gastos").val() || 0;
    var monto_efectivo = $("#txt_monto_efectivo").val() || 0;
    
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
                    otros_gastos: otros_gastos,
                    monto_efectivo: monto_efectivo
                }
            }).done(function(resp) {
                if (resp > 0) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Éxito',
                        text: 'Turno cerrado correctamente',
                        confirmButtonColor: '#023D77'
                    }).then(() => {
                        // Imprimir el reporte automáticamente
                        Imprimir_Reporte(id_reporte);
                        // Redirigir al historial
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


// FUNCIÓN PARA VER DETALLE DEL TURNO (MODAL)
function Ver_Detalle_Turno(id_reporte) {
    console.log('Ver_Detalle_Turno llamado con ID:', id_reporte);
    
    // Cargar información general del turno
    $.ajax({
        url: '../controller/turnos/controlador_detalle_turno.php',
        type: 'POST',
        data: { id_reporte: id_reporte },
        dataType: 'json'
    }).done(function(data) {
        console.log('Datos del turno:', data);
        $("#detalle_numero_documento").text(data.numero_documento || '-');
        $("#detalle_fecha").text(data.fecha || '-');
        $("#detalle_turno").text(data.turno || '-');
        $("#detalle_grifero").text(data.grifero || '-');
        
        // Totales en tarjetas
        $("#detalle_total_diesel").text('S/. ' + parseFloat(data.total_diesel || 0).toFixed(2));
        $("#detalle_total_regular").text('S/. ' + parseFloat(data.total_regular || 0).toFixed(2));
        $("#detalle_total_premium").text('S/. ' + parseFloat(data.total_premium || 0).toFixed(2));
        $("#detalle_total_ventas").text('S/. ' + parseFloat(data.total_ventas || 0).toFixed(2));
        
        // Totales en Soles
        $("#detalle_soles_diesel").text('S/. ' + parseFloat(data.total_diesel || 0).toFixed(2));
        $("#detalle_soles_regular").text('S/. ' + parseFloat(data.total_regular || 0).toFixed(2));
        $("#detalle_soles_premium").text('S/. ' + parseFloat(data.total_premium || 0).toFixed(2));
        $("#detalle_soles_total").text('S/. ' + parseFloat(data.total_ventas || 0).toFixed(2));
        
        // Totales en Galones
        $("#detalle_galones_diesel").text(parseFloat(data.galones_diesel || 0).toFixed(3) + ' gal');
        $("#detalle_galones_regular").text(parseFloat(data.galones_regular || 0).toFixed(3) + ' gal');
        $("#detalle_galones_premium").text(parseFloat(data.galones_premium || 0).toFixed(3) + ' gal');
        $("#detalle_galones_total").text(parseFloat(data.total_galones || 0).toFixed(3) + ' gal');
        
        // Otros Conceptos
        // Otros Conceptos - Usamos selectores de clase para evitar conflictos de IDs duplicados
        var modal = $("[id='modal_detalle_turno']").last();
        modal.find(".detalle_descuentos").text('S/. ' + parseFloat(data.monto_descuentos || 0).toFixed(2));
        modal.find(".detalle_otros_gastos").text('S/. ' + parseFloat(data.monto_otros_gastos || 0).toFixed(2));
        modal.find(".detalle_monto_efectivo").text('S/. ' + parseFloat(data.monto_efectivo || 0).toFixed(2));
    }).fail(function(xhr, status, error) {
        console.error('Error al cargar datos del turno:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'No se pudieron cargar los datos del turno',
            confirmButtonColor: '#023D77'
        });
    });
    
    // Cargar lecturas
    $.ajax({
        url: '../controller/turnos/controlador_detalle_lecturas.php',
        type: 'POST',
        data: { id_reporte: id_reporte },
        dataType: 'json'
    }).done(function(data) {
        console.log('Lecturas:', data);
        var html = '';
        if (data && data.length > 0) {
            data.forEach(function(item) {
                html += '<tr>';
                html += '<td>' + item.numero_maquina + '</td>';
                html += '<td>' + item.codigo + '</td>';
                html += '<td>' + item.producto + '</td>';
                html += '<td>' + parseFloat(item.lectura_anterior).toFixed(3) + '</td>';
                html += '<td>' + parseFloat(item.lectura_actual).toFixed(3) + '</td>';
                html += '<td>' + parseFloat(item.galones_vendidos).toFixed(3) + '</td>';
                html += '<td>S/. ' + parseFloat(item.precio).toFixed(2) + '</td>';
                html += '<td>S/. ' + parseFloat(item.total).toFixed(2) + '</td>';
                html += '</tr>';
            });
        } else {
            html = '<tr><td colspan="8" class="text-center">No hay lecturas registradas</td></tr>';
        }
        $("#tabla_detalle_lecturas tbody").html(html);
    }).fail(function(xhr, status, error) {
        console.error('Error al cargar lecturas:', error);
        $("#tabla_detalle_lecturas tbody").html('<tr><td colspan="8" class="text-center text-danger">Error al cargar lecturas</td></tr>');
    });
    
    // Aseguramos mostrar el último modal (el que acabamos de actualizar)
    $("[id='modal_detalle_turno']").last().modal('show');
}

function Imprimir_Reporte(id_reporte) {
    console.log("===== DEBUG REPORTE =====");
    console.log("ID recibido:", id_reporte);

    // 1. Detectar dominio actual
    console.log("Origin:", window.location.origin);
    console.log("Path actual:", window.location.pathname);

    // 2. Probar ruta relativa real
    var url = "../view/MPDF/REPORTE/reporte_turno.php?id=" + id_reporte;
    console.log("URL generada:", url);

    // 3. Verificar si el archivo realmente existe
    fetch(url)
        .then(response => {
            console.log("HTTP Status:", response.status);
            if (!response.ok) {
                console.error("❌ Archivo NO encontrado en esa ruta");
            } else {
                console.log("✔ Archivo encontrado correctamente");
            }
        })
        .catch(err => {
            console.error("⚠ Error de Fetch:", err);
        });

    // 4. Abrir PDF
    window.open(url, "_blank");
}


// ============================================
// HISTORIAL DE TURNOS / TODOS LOS REPORTES
// ============================================

var tabla_historial_turnos;

function Listar_Historial_Turnos() {
    var filtro_fecha_inicio = $("#filtro_fecha_inicio").val();
    var filtro_fecha_fin = $("#filtro_fecha_fin").val();
    var filtro_usuario = $("#filtro_usuario").val() || null;
    var filtro_estado = $("#filtro_estado").val() || null;
    var filtro_validacion = $("#filtro_validacion").val() || null;
    
    if (tabla_historial_turnos) {
        tabla_historial_turnos.destroy();
    }
    
    tabla_historial_turnos = $("#tabla_historial_turnos").DataTable({
        "ordering": true,
        "order": [], // Desactivar orden inicial del cliente, respetar orden del servidor
        "bLengthChange": true,
        "searching": { "regex": false },
        "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
        "pageLength": 10,
        "destroy": true,
        "processing": true,
        "ajax": {
            "url": "../controller/turnos/controlador_listar_turnos.php",
            type: 'POST',
            data: {
                filtro_fecha_inicio: filtro_fecha_inicio,
                filtro_fecha_fin: filtro_fecha_fin,
                filtro_usuario: filtro_usuario,
                filtro_estado: filtro_estado,
                filtro_validacion: filtro_validacion
            }
        },
        "columns": [
            { "data": "numero_documento" },
            { 
                "data": "fecha_reporte",
                "render": function(data) {
                    var fecha = new Date(data);
                    return fecha.toLocaleDateString('es-PE');
                }
            },
            { 
                "data": "turno",
                "render": function(data) {
                    if (data == 'DIA') {
                        return '<span class="badge badge-warning">DÍA</span>';
                    } else {
                        return '<span class="badge badge-dark">NOCHE</span>';
                    }
                }
            },
            { "data": "grifero_nombre" },
            { 
                "data": "total_ventas",
                "render": function(data) {
                    return 'S/. ' + parseFloat(data || 0).toFixed(2);
                }
            },
            { 
                "data": "monto_faltante",
                "render": function(data) {
                    var faltante = parseFloat(data || 0);
                    if (faltante < 0) {
                        return '<span class="badge badge-danger">S/. ' + Math.abs(faltante).toFixed(2) + '</span>';
                    } else if (faltante > 0) {
                        return '<span class="badge badge-success">S/. ' + faltante.toFixed(2) + '</span>';
                    } else {
                        return '<span class="badge badge-info">S/. 0.00</span>';
                    }
                }
            },
            { 
                "data": "estado",
                "render": function(data) {
                    if (data == 'ABIERTO') {
                        return '<span class="badge badge-success">ABIERTO</span>';
                    } else {
                        return '<span class="badge badge-secondary">CERRADO</span>';
                    }
                }
            },
            { 
                "data": "estado_validacion",
                "render": function(data, type, row) {
                    if (data == 'VALIDADO') {
                        var tooltip = row.validado_por ? 'Validado por: ' + row.validado_por : 'Validado';
                        return '<span class="badge badge-success" title="' + tooltip + '"><i class="fas fa-check-circle"></i> VALIDADO</span>';
                    } else if (data == 'PENDIENTE') {
                        return '<span class="badge badge-warning"><i class="fas fa-clock"></i> PENDIENTE</span>';
                    } else {
                        return '<span class="badge badge-secondary">N/A</span>';
                    }
                }
            },
            {
                "data": "id_reporte",
                "render": function(data) {
                    return "<button class='ver btn btn-info btn-sm' title='Ver Detalle'><i class='fas fa-eye'></i></button>&nbsp;<button class='imprimir btn btn-primary btn-sm' title='Imprimir'><i class='fas fa-print'></i></button>";
                }
            }
        ],
        "language": idioma_espanol,
        select: true
    });

    document.getElementById("tabla_historial_turnos").addEventListener("click", function(e) {
        if (e.target.closest(".ver")) {
            var data = tabla_historial_turnos.row(e.target.closest("tr")).data();
            Ver_Detalle_Turno(data.id_reporte);
        }
        if (e.target.closest(".imprimir")) {
            var data = tabla_historial_turnos.row(e.target.closest("tr")).data();
            Imprimir_Reporte(data.id_reporte);
        }
    });
}

function Filtrar_Turnos() {
    Listar_Historial_Turnos();
}
