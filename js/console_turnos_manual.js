// ============================================
// REGISTRO DE TURNO MANUAL (ESTILO EXCEL)
// ============================================

var datos_lecturas = [];
var datos_clientes = [];

function Cargar_Turno_Manual() {
    $.ajax({
        url: '../controller/turnos/controlador_obtener_turno_sistema.php',
        type: 'POST'
    }).done(function(resp) {
        if (resp == '0') {
            $("#sin_turno_abierto").show();
            $("#formulario_registro_turno").hide();
        } else {
            var data = JSON.parse(resp);
            $("#sin_turno_abierto").hide();
            $("#formulario_registro_turno").show();
            
            // Llenar datos del turno
            $("#info_numero_documento").text(data.numero_documento);
            $("#info_fecha").text(data.fecha_reporte);
            $("#info_turno").text(data.turno);
            $("#info_hora_inicio").text(data.hora_inicio);
            $("#info_hora_fin").text(data.hora_fin);
            $("#info_grifero").text(data.grifero_nombre);
            $("#txt_id_reporte").val(data.id_reporte);
            
            // Cargar lecturas y datos
            Cargar_Lecturas_Manual(data.id_reporte, data.fecha_reporte);
            Cargar_Clientes_Para_Creditos();
        }
    });
}

function Cargar_Lecturas_Manual(id_reporte, fecha) {
    $.ajax({
        url: '../controller/turnos/controlador_obtener_lecturas_turno.php',
        type: 'POST',
        data: { id_reporte: id_reporte }
    }).done(function(resp) {
        datos_lecturas = JSON.parse(resp);
        
        // Generar filas para Máquina 1
        var html_m1 = '';
        var productos_m1 = datos_lecturas.filter(item => item.numero_maquina == 1);
        productos_m1.forEach(function(item) {
            html_m1 += Generar_Fila_Lectura(item, fecha);
        });
        $("#tabla_maquina_1").html(html_m1);
        
        // Generar filas para Máquina 2
        var html_m2 = '';
        var productos_m2 = datos_lecturas.filter(item => item.numero_maquina == 2);
        productos_m2.forEach(function(item) {
            html_m2 += Generar_Fila_Lectura(item, fecha);
        });
        $("#tabla_maquina_2").html(html_m2);
        
        // Generar 15 filas para pagos
        Generar_Filas_Pagos();
        
        // Generar 15 filas para créditos
        Generar_Filas_Creditos();
        
        // Calcular totales iniciales
        Calcular_Todos_Los_Totales();
    });
}

function Generar_Fila_Lectura(item, fecha) {
    var lectura_actual = parseFloat(item.lectura_actual) || 0;
    var lectura_anterior = parseFloat(item.lectura_anterior) || 0;
    var galones = lectura_actual - lectura_anterior;
    var precio = parseFloat(item.precio_galon) || 0;
    var total = galones * precio;
    
    // Determinar tipo de producto
    var producto_tipo = '';
    var nombre_upper = item.producto_nombre.toUpperCase();
    if (nombre_upper.includes('DIESEL')) producto_tipo = 'DIESEL';
    else if (nombre_upper.includes('REGULAR')) producto_tipo = 'REGULAR';
    else if (nombre_upper.includes('PREMIUM')) producto_tipo = 'PREMIUM';
    
    var fila = '<tr>';
    fila += '<td>' + fecha + '</td>';
    fila += '<td>' + item.producto_nombre + '</td>';
    fila += '<td>' + lectura_anterior.toFixed(3) + '</td>';
    fila += '<td><input type="number" step="0.001" class="lectura-actual" ' +
            'data-id="' + item.id_lectura + '" ' +
            'data-lectura-anterior="' + lectura_anterior + '" ' +
            'data-precio="' + precio + '" ' +
            'data-producto="' + producto_tipo + '" ' +
            'data-maquina="' + item.numero_maquina + '" ' +
            'value="' + lectura_actual.toFixed(3) + '"></td>';
    fila += '<td class="galones-' + item.id_lectura + '">' + galones.toFixed(3) + '</td>';
    fila += '<td>' + precio.toFixed(2) + '</td>';
    fila += '<td class="total-' + item.id_lectura + '">' + total.toFixed(2) + '</td>';
    fila += '</tr>';
    
    return fila;
}

var contador_filas_pagos = 15;

function Generar_Filas_Pagos() {
    var html = '';
    for (var i = 0; i < 15; i++) {
        html += '<tr class="fila-pago">';
        html += '<td><input type="number" step="0.01" class="pago-yape" data-fila="' + i + '" value="0"></td>';
        html += '<td><input type="text" class="cod-yape" data-fila="' + i + '" placeholder="Código"></td>';
        html += '<td><input type="number" step="0.01" class="pago-bcp" data-fila="' + i + '" value="0"></td>';
        html += '<td><input type="text" class="cod-bcp" data-fila="' + i + '" placeholder="Código"></td>';
        html += '<td><input type="number" step="0.01" class="pago-visa" data-fila="' + i + '" value="0"></td>';
        html += '<td><input type="text" class="cod-visa" data-fila="' + i + '" placeholder="Código"></td>';
        html += '<td><input type="number" step="0.01" class="pago-descuento" data-fila="' + i + '" value="0"></td>';
        html += '<td><input type="number" step="0.01" class="pago-efectivo" data-fila="' + i + '" value="0"></td>';
        html += '<td><input type="number" step="0.01" class="pago-otros-gastos" data-fila="' + i + '" value="0"></td>';
        html += '<td><button type="button" class="btn btn-danger btn-sm" onclick="Eliminar_Fila_Pago(this)" title="Eliminar fila"><i class="fas fa-trash"></i></button></td>';
        html += '</tr>';
    }
    $("#tabla_pagos").html(html);
}

function Agregar_Fila_Pago() {
    var fila = contador_filas_pagos;
    var html = '<tr class="fila-pago">';
    html += '<td><input type="number" step="0.01" class="pago-yape" data-fila="' + fila + '" value="0"></td>';
    html += '<td><input type="text" class="cod-yape" data-fila="' + fila + '" placeholder="Código"></td>';
    html += '<td><input type="number" step="0.01" class="pago-bcp" data-fila="' + fila + '" value="0"></td>';
    html += '<td><input type="text" class="cod-bcp" data-fila="' + fila + '" placeholder="Código"></td>';
    html += '<td><input type="number" step="0.01" class="pago-visa" data-fila="' + fila + '" value="0"></td>';
    html += '<td><input type="text" class="cod-visa" data-fila="' + fila + '" placeholder="Código"></td>';
    html += '<td><input type="number" step="0.01" class="pago-descuento" data-fila="' + fila + '" value="0"></td>';
    html += '<td><input type="number" step="0.01" class="pago-efectivo" data-fila="' + fila + '" value="0"></td>';
    html += '<td><input type="number" step="0.01" class="pago-otros-gastos" data-fila="' + fila + '" value="0"></td>';
    html += '<td><button type="button" class="btn btn-danger btn-sm" onclick="Eliminar_Fila_Pago(this)" title="Eliminar fila"><i class="fas fa-trash"></i></button></td>';
    html += '</tr>';
    
    $("#tabla_pagos").append(html);
    contador_filas_pagos++;
    
    // Mostrar mensaje
    Swal.fire({
        icon: 'success',
        title: 'Fila agregada',
        text: 'Nueva fila de pago agregada correctamente',
        timer: 1500,
        showConfirmButton: false
    });
}

function Eliminar_Fila_Pago(btn) {
    $(btn).closest('tr').remove();
    Calcular_Todos_Los_Totales();
    
    Swal.fire({
        icon: 'success',
        title: 'Fila eliminada',
        text: 'Fila de pago eliminada correctamente',
        timer: 1500,
        showConfirmButton: false
    });
}

var contador_filas_creditos = 15;

function Generar_Filas_Creditos() {
    var html = '';
    for (var i = 0; i < 15; i++) {
        html += '<tr class="fila-credito">';
        html += '<td><input type="number" step="0.01" class="credito-monto" data-fila="' + i + '" value="0"></td>';
        html += '<td><input type="text" class="credito-cliente" data-fila="' + i + '" placeholder="Nombre del cliente" list="lista_clientes"></td>';
        html += '<td><input type="text" class="credito-vale" data-fila="' + i + '" placeholder="N° Vale"></td>';
        html += '<td><button type="button" class="btn btn-danger btn-sm" onclick="Eliminar_Fila_Credito(this)" title="Eliminar fila"><i class="fas fa-trash"></i></button></td>';
        html += '</tr>';
    }
    $("#tabla_creditos").html(html);
}

function Agregar_Fila_Credito() {
    var fila = contador_filas_creditos;
    var html = '<tr class="fila-credito">';
    html += '<td><input type="number" step="0.01" class="credito-monto" data-fila="' + fila + '" value="0"></td>';
    html += '<td><input type="text" class="credito-cliente" data-fila="' + fila + '" placeholder="Nombre del cliente" list="lista_clientes"></td>';
    html += '<td><input type="text" class="credito-vale" data-fila="' + fila + '" placeholder="N° Vale"></td>';
    html += '<td><button type="button" class="btn btn-danger btn-sm" onclick="Eliminar_Fila_Credito(this)" title="Eliminar fila"><i class="fas fa-trash"></i></button></td>';
    html += '</tr>';
    
    $("#tabla_creditos").append(html);
    contador_filas_creditos++;
    
    // Mostrar mensaje
    Swal.fire({
        icon: 'success',
        title: 'Fila agregada',
        text: 'Nueva fila de crédito agregada correctamente',
        timer: 1500,
        showConfirmButton: false
    });
}

function Eliminar_Fila_Credito(btn) {
    $(btn).closest('tr').remove();
    Calcular_Todos_Los_Totales();
    
    Swal.fire({
        icon: 'success',
        title: 'Fila eliminada',
        text: 'Fila de crédito eliminada correctamente',
        timer: 1500,
        showConfirmButton: false
    });
}

function Cargar_Clientes_Para_Creditos() {
    $.ajax({
        url: '../controller/clientes/controlador_clientes_activos.php',
        type: 'POST'
    }).done(function(resp) {
        datos_clientes = JSON.parse(resp);
        
        // Crear datalist para autocompletar
        var datalist = '<datalist id="lista_clientes">';
        datos_clientes.forEach(function(cliente) {
            datalist += '<option value="' + cliente.nombre_completo + '" data-id="' + cliente.id_cliente + '">';
        });
        datalist += '</datalist>';
        
        // Agregar al body si no existe
        if ($("#lista_clientes").length == 0) {
            $("body").append(datalist);
        }
    });
}

// EVENTOS DE CÁLCULO EN TIEMPO REAL
$(document).on('input', '.lectura-actual', function() {
    var id_lectura = $(this).data('id');
    var lectura_anterior = parseFloat($(this).data('lectura-anterior'));
    var precio = parseFloat($(this).data('precio'));
    var lectura_actual = parseFloat($(this).val()) || 0;
    
    var galones = lectura_actual - lectura_anterior;
    if (galones < 0) galones = 0;
    
    var total = galones * precio;
    
    $(".galones-" + id_lectura).text(galones.toFixed(3));
    $(".total-" + id_lectura).text(total.toFixed(2));
    
    Calcular_Todos_Los_Totales();
});

$(document).on('input', '.pago-yape, .pago-bcp, .pago-visa, .pago-descuento, .pago-efectivo, .pago-otros-gastos', function() {
    Calcular_Todos_Los_Totales();
});

$(document).on('input', '.credito-monto', function() {
    Calcular_Todos_Los_Totales();
});

function Calcular_Todos_Los_Totales() {
    // TOTALES DE MÁQUINAS
    var total_m1 = 0;
    var total_m2 = 0;
    
    $(".lectura-actual").each(function() {
        var maquina = $(this).data('maquina');
        var lectura_anterior = parseFloat($(this).data('lectura-anterior'));
        var precio = parseFloat($(this).data('precio'));
        var lectura_actual = parseFloat($(this).val()) || 0;
        var galones = lectura_actual - lectura_anterior;
        if (galones < 0) galones = 0;
        var total = galones * precio;
        
        if (maquina == 1) {
            total_m1 += total;
        } else {
            total_m2 += total;
        }
    });
    
    $("#total_maquina_1").text(total_m1.toFixed(2));
    $("#total_maquina_2").text(total_m2.toFixed(2));
    
    var total_general = total_m1 + total_m2;
    $("#total_general").text('S/. ' + total_general.toFixed(2));
    
    // TOTALES POR COMBUSTIBLE
    var total_diesel = 0;
    var total_regular = 0;
    var total_premium = 0;
    
    $(".lectura-actual").each(function() {
        var producto = $(this).data('producto');
        var lectura_anterior = parseFloat($(this).data('lectura-anterior'));
        var precio = parseFloat($(this).data('precio'));
        var lectura_actual = parseFloat($(this).val()) || 0;
        var galones = lectura_actual - lectura_anterior;
        if (galones < 0) galones = 0;
        var total = galones * precio;
        
        if (producto == 'DIESEL') total_diesel += total;
        else if (producto == 'REGULAR') total_regular += total;
        else if (producto == 'PREMIUM') total_premium += total;
    });
    
    $("#resumen_diesel").text('S/. ' + total_diesel.toFixed(2));
    $("#resumen_regular").text('S/. ' + total_regular.toFixed(2));
    $("#resumen_premium").text('S/. ' + total_premium.toFixed(2));
    $("#resumen_total_soles").text('S/. ' + total_general.toFixed(2));
    
    // TOTALES DE PAGOS
    var total_yape = 0;
    var total_bcp = 0;
    var total_visa = 0;
    var total_descuentos = 0;
    var total_efectivo = 0;
    var total_otros_gastos = 0;
    
    $(".pago-yape").each(function() {
        total_yape += parseFloat($(this).val()) || 0;
    });
    $(".pago-bcp").each(function() {
        total_bcp += parseFloat($(this).val()) || 0;
    });
    $(".pago-visa").each(function() {
        total_visa += parseFloat($(this).val()) || 0;
    });
    $(".pago-descuento").each(function() {
        total_descuentos += parseFloat($(this).val()) || 0;
    });
    $(".pago-efectivo").each(function() {
        total_efectivo += parseFloat($(this).val()) || 0;
    });
    $(".pago-otros-gastos").each(function() {
        total_otros_gastos += parseFloat($(this).val()) || 0;
    });
    
    $("#total_yape").text(total_yape.toFixed(2));
    $("#total_bcp").text(total_bcp.toFixed(2));
    $("#total_visa").text(total_visa.toFixed(2));
    $("#total_descuentos").text(total_descuentos.toFixed(2));
    $("#total_efectivo").text(total_efectivo.toFixed(2));
    $("#total_otros_gastos").text(total_otros_gastos.toFixed(2));
    
    // TOTALES DE CRÉDITOS
    var total_creditos = 0;
    $(".credito-monto").each(function() {
        total_creditos += parseFloat($(this).val()) || 0;
    });
    $("#total_creditos").text(total_creditos.toFixed(2));
    
    // CUADRE DE CAJA
    var total_pagos = total_yape + total_bcp + total_visa;
    
    $("#cuadre_total_ventas").text('S/. ' + total_general.toFixed(2));
    $("#cuadre_total_pagos").text('S/. ' + total_pagos.toFixed(2));
    $("#cuadre_total_creditos").text('S/. ' + total_creditos.toFixed(2));
    $("#cuadre_descuentos").text('S/. ' + total_descuentos.toFixed(2));
    $("#cuadre_otros_gastos").text('S/. ' + total_otros_gastos.toFixed(2));
    $("#cuadre_efectivo").text('S/. ' + total_efectivo.toFixed(2));
    
    // Cálculo de diferencia
    var total_justificado = total_pagos + total_creditos + total_otros_gastos + total_efectivo;
    var total_neto_ventas = total_general - total_descuentos;
    var diferencia = total_justificado - total_neto_ventas;
    
    var diferencia_text = 'S/. ' + Math.abs(diferencia).toFixed(2);
    
    if (diferencia < -0.01) {
        $("#cuadre_diferencia").html('<span class="text-danger">' + diferencia_text + ' (FALTANTE)</span>');
    } else if (diferencia > 0.01) {
        $("#cuadre_diferencia").html('<span class="text-success">' + diferencia_text + ' (SOBRANTE)</span>');
    } else {
        $("#cuadre_diferencia").html('<span class="text-success">S/. 0.00 (CUADRADO)</span>');
    }
}

// CERRAR TURNO (GUARDA TODO AUTOMÁTICAMENTE)
function Cerrar_Turno_Manual() {
    Swal.fire({
        title: '¿Cerrar turno?',
        text: "Se guardarán todos los datos y se cerrará el turno definitivamente",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#023D77',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, cerrar turno',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            // Mostrar loading
            Swal.fire({
                title: 'Guardando y cerrando turno...',
                text: 'Por favor espere',
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            var id_reporte = $("#txt_id_reporte").val();
            
            // Recopilar todos los datos
            var lecturas = [];
            $(".lectura-actual").each(function() {
                lecturas.push({
                    id_lectura: $(this).data('id'),
                    lectura_actual: parseFloat($(this).val()) || 0
                });
            });
            
            var pagos = [];
            // Recorrer todas las filas de pagos (dinámicas)
            $(".pago-yape").each(function() {
                var fila = $(this).data('fila');
                var yape = parseFloat($(this).val()) || 0;
                var cod_yape = $(".cod-yape[data-fila='" + fila + "']").val();
                var bcp = parseFloat($(".pago-bcp[data-fila='" + fila + "']").val()) || 0;
                var cod_bcp = $(".cod-bcp[data-fila='" + fila + "']").val();
                var visa = parseFloat($(".pago-visa[data-fila='" + fila + "']").val()) || 0;
                var cod_visa = $(".cod-visa[data-fila='" + fila + "']").val();
                var descuento = parseFloat($(".pago-descuento[data-fila='" + fila + "']").val()) || 0;
                var efectivo = parseFloat($(".pago-efectivo[data-fila='" + fila + "']").val()) || 0;
                var otros_gastos = parseFloat($(".pago-otros-gastos[data-fila='" + fila + "']").val()) || 0;
                
                if (yape > 0) pagos.push({ tipo: 'YAPE', monto: yape, codigo: cod_yape });
                if (bcp > 0) pagos.push({ tipo: 'BCP', monto: bcp, codigo: cod_bcp });
                if (visa > 0) pagos.push({ tipo: 'VISA', monto: visa, codigo: cod_visa });
                if (descuento > 0) pagos.push({ tipo: 'DESCUENTO', monto: descuento, codigo: '' });
                if (efectivo > 0) pagos.push({ tipo: 'EFECTIVO', monto: efectivo, codigo: '' });
                if (otros_gastos > 0) pagos.push({ tipo: 'OTROS_GASTOS', monto: otros_gastos, codigo: '' });
            });
            
            var creditos = [];
            // Recorrer todas las filas de créditos (dinámicas)
            $(".credito-monto").each(function() {
                var fila = $(this).data('fila');
                var monto = parseFloat($(this).val()) || 0;
                var cliente_nombre = $(".credito-cliente[data-fila='" + fila + "']").val();
                var numero_vale = $(".credito-vale[data-fila='" + fila + "']").val();
                
                if (monto > 0 && cliente_nombre && numero_vale) {
                    var id_cliente = null;
                    datos_clientes.forEach(function(c) {
                        if (c.nombre_completo == cliente_nombre) {
                            id_cliente = c.id_cliente;
                        }
                    });
                    
                    if (id_cliente) {
                        creditos.push({
                            id_cliente: id_cliente,
                            monto: monto,
                            numero_vale: numero_vale
                        });
                    }
                }
            });
            
            // Primero guardar todos los datos
            $.ajax({
                url: '../controller/turnos/controlador_guardar_turno_manual.php',
                type: 'POST',
                data: {
                    id_reporte: id_reporte,
                    lecturas: JSON.stringify(lecturas),
                    pagos: JSON.stringify(pagos),
                    creditos: JSON.stringify(creditos)
                }
            }).done(function(resp) {
                if (resp > 0) {
                    // Datos guardados, ahora cerrar el turno
                    $.ajax({
                        url: '../controller/turnos/controlador_cerrar_turno_manual.php',
                        type: 'POST',
                        data: { id_reporte: id_reporte }
                    }).done(function(resp2) {
                        if (resp2 > 0) {
                            Swal.fire({
                                icon: 'success',
                                title: '¡Turno cerrado!',
                                text: 'Todos los datos se guardaron correctamente',
                                confirmButtonColor: '#023D77'
                            }).then(() => {
                                cargar_contenido('contenido_principal', 'turnos/view_historial.php');
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Los datos se guardaron pero no se pudo cerrar el turno',
                                confirmButtonColor: '#023D77'
                            });
                        }
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'No se pudieron guardar los datos',
                        confirmButtonColor: '#023D77'
                    });
                }
            }).fail(function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error de conexión',
                    text: 'No se pudo conectar con el servidor',
                    confirmButtonColor: '#023D77'
                });
            });
        }
    });
}
