var tabla_clientes;
var tabla_creditos_cliente;

function Listar_Clientes() {
    tabla_clientes = $("#tabla_clientes").DataTable({
        "ordering": true,
        "bLengthChange": true,
        "searching": { "regex": false },
        "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
        "pageLength": 10,
        "destroy": true,
        "async": false,
        "processing": true,
        "ajax": {
            "url": "../controller/clientes/controlador_listar_clientes.php",
            type: 'POST'
        },
        "columns": [
            { "data": "id_cliente" },
            { "data": "nombre_completo" },
            { 
                "data": "dni_ruc",
                "render": function(data) {
                    return data ? data : '-';
                }
            },
            { 
                "data": "telefono",
                "render": function(data) {
                    return data ? data : '-';
                }
            },
            { 
                "data": "direccion",
                "render": function(data) {
                    return data ? data : '-';
                }
            },
            { 
                "data": "saldo_pendiente",
                "render": function(data) {
                    var saldo = parseFloat(data);
                    if (saldo > 0) {
                        return '<span class="badge badge-danger">S/. ' + saldo.toFixed(2) + '</span>';
                    } else {
                        return '<span class="badge badge-success">S/. 0.00</span>';
                    }
                }
            },
            {
                "data": "estado",
                "render": function(data) {
                    if (data == 'ACTIVO') {
                        return '<span class="badge badge-success">ACTIVO</span>';
                    } else {
                        return '<span class="badge badge-danger">INACTIVO</span>';
                    }
                }
            },
            { 
                "data": "created_at",
                "render": function(data) {
                    if (data) {
                        var fecha = new Date(data);
                        return fecha.toLocaleDateString('es-PE');
                    }
                    return '-';
                }
            },
            { "data": "usuario_registro" },
            {
                "defaultContent": "<button class='ver btn btn-info btn-sm' title='Ver Detalle'><i class='fas fa-eye'></i></button>&nbsp;<button class='editar btn btn-warning btn-sm' title='Editar'><i class='fas fa-edit'></i></button>&nbsp;<button class='cambiar_estado btn btn-secondary btn-sm' title='Cambiar Estado'><i class='fas fa-toggle-on'></i></button>"
            }
        ],
        "language": idioma_espanol,
        select: true
    });

    document.getElementById("tabla_clientes").addEventListener("click", function(e) {
        if (e.target.closest(".ver")) {
            var data = tabla_clientes.row(e.target.closest("tr")).data();
            Ver_Detalle_Cliente(data.id_cliente);
        }
        if (e.target.closest(".editar")) {
            var data = tabla_clientes.row(e.target.closest("tr")).data();
            Abrir_Modal_Editar(data);
        }
        if (e.target.closest(".cambiar_estado")) {
            var data = tabla_clientes.row(e.target.closest("tr")).data();
            Cambiar_Estado_Cliente(data.id_cliente, data.estado);
        }
    });
}

function AbrirModalRegistro() {
    $("#modal_registro_cliente").modal('show');
    $("#txt_nombre").val('');
    $("#txt_dni").val('');
    $("#txt_telefono").val('');
    $("#txt_direccion").val('');
}

function Registrar_Cliente() {
    var nombre = $("#txt_nombre").val();
    var dni = $("#txt_dni").val();
    var telefono = $("#txt_telefono").val();
    var direccion = $("#txt_direccion").val();
    var id_usuario = $("#txtprincipalid").val();

    if (nombre.length == 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Advertencia',
            text: 'El nombre del cliente es obligatorio',
            confirmButtonColor: '#023D77'
        });
        return;
    }

    $.ajax({
        url: '../controller/clientes/controlador_registrar_cliente.php',
        type: 'POST',
        data: {
            nombre: nombre,
            dni: dni,
            telefono: telefono,
            direccion: direccion,
            id_usuario: id_usuario
        }
    }).done(function(resp) {
        if (resp > 0) {
            Swal.fire({
                icon: 'success',
                title: 'Éxito',
                text: 'Cliente registrado correctamente',
                confirmButtonColor: '#023D77'
            });
            tabla_clientes.ajax.reload();
            $("#modal_registro_cliente").modal('hide');
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Error al registrar el cliente',
                confirmButtonColor: '#023D77'
            });
        }
    });
}

function Abrir_Modal_Editar(data) {
    $("#modal_editar_cliente").modal('show');
    $("#txt_id_cliente").val(data.id_cliente);
    $("#txt_nombre_editar").val(data.nombre_completo);
    $("#txt_dni_editar").val(data.dni_ruc);
    $("#txt_telefono_editar").val(data.telefono);
    $("#txt_direccion_editar").val(data.direccion);
}

function Modificar_Cliente() {
    var id = $("#txt_id_cliente").val();
    var nombre = $("#txt_nombre_editar").val();
    var dni = $("#txt_dni_editar").val();
    var telefono = $("#txt_telefono_editar").val();
    var direccion = $("#txt_direccion_editar").val();

    if (nombre.length == 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Advertencia',
            text: 'El nombre del cliente es obligatorio',
            confirmButtonColor: '#023D77'
        });
        return;
    }

    $.ajax({
        url: '../controller/clientes/controlador_modificar_cliente.php',
        type: 'POST',
        data: {
            id: id,
            nombre: nombre,
            dni: dni,
            telefono: telefono,
            direccion: direccion
        }
    }).done(function(resp) {
        if (resp > 0) {
            Swal.fire({
                icon: 'success',
                title: 'Éxito',
                text: 'Cliente actualizado correctamente',
                confirmButtonColor: '#023D77'
            });
            tabla_clientes.ajax.reload();
            $("#modal_editar_cliente").modal('hide');
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Error al actualizar el cliente',
                confirmButtonColor: '#023D77'
            });
        }
    });
}

function Cambiar_Estado_Cliente(id, estado_actual) {
    var nuevo_estado = (estado_actual == 'ACTIVO') ? 'INACTIVO' : 'ACTIVO';
    var texto = (nuevo_estado == 'ACTIVO') ? 'activar' : 'desactivar';

    Swal.fire({
        title: '¿Está seguro de ' + texto + ' este cliente?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#023D77',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, ' + texto,
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '../controller/clientes/controlador_cambiar_estado_cliente.php',
                type: 'POST',
                data: {
                    id: id,
                    estado: nuevo_estado
                }
            }).done(function(resp) {
                if (resp > 0) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Éxito',
                        text: 'Estado actualizado correctamente',
                        confirmButtonColor: '#023D77'
                    });
                    tabla_clientes.ajax.reload();
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Error al cambiar el estado',
                        confirmButtonColor: '#023D77'
                    });
                }
            });
        }
    });
}

function Ver_Detalle_Cliente(id) {
    $.ajax({
        url: '../controller/clientes/controlador_detalle_cliente.php',
        type: 'POST',
        data: { id: id }
    }).done(function(resp) {
        var data = JSON.parse(resp);
        
        $("#detalle_nombre").text(data.nombre_completo);
        $("#detalle_dni").text(data.dni_ruc ? data.dni_ruc : '-');
        $("#detalle_telefono").text(data.telefono ? data.telefono : '-');
        $("#detalle_direccion").text(data.direccion ? data.direccion : '-');
        
        $("#detalle_total_creditos").text(data.total_creditos);
        $("#detalle_monto_total").text('S/. ' + parseFloat(data.monto_total).toFixed(2));
        $("#detalle_monto_pagado").text('S/. ' + parseFloat(data.monto_pagado).toFixed(2));
        $("#detalle_saldo_pendiente").text('S/. ' + parseFloat(data.saldo_pendiente).toFixed(2));
        
        Listar_Creditos_Cliente(id);
        
        $("#modal_detalle_cliente").modal('show');
    });
}

function Listar_Creditos_Cliente(id_cliente) {
    if (tabla_creditos_cliente) {
        tabla_creditos_cliente.destroy();
    }
    
    tabla_creditos_cliente = $("#tabla_creditos_cliente").DataTable({
        "ordering": true,
        "bLengthChange": false,
        "searching": false,
        "pageLength": 10,
        "destroy": true,
        "async": false,
        "processing": true,
        "ajax": {
            "url": "../controller/clientes/controlador_creditos_cliente.php",
            "type": "POST",
            "data": { id_cliente: id_cliente }
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
                    return 'S/. ' + parseFloat(data).toFixed(2);
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
                "data": "fecha_vencimiento",
                "render": function(data) {
                    if (data) {
                        var fecha = new Date(data);
                        return fecha.toLocaleDateString('es-PE');
                    }
                    return '-';
                }
            }
        ],
        "language": idioma_espanol
    });
}

function Buscar_DNI_RUC() {
    var documento = $("#txt_dni").val().trim();
    
    if (documento.length == 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Advertencia',
            text: 'Ingrese un DNI o RUC',
            confirmButtonColor: '#023D77'
        });
        return;
    }
    
    if (documento.length == 8) {
        // Buscar DNI en RENIEC
        Buscar_DNI(documento);
    } else if (documento.length == 11) {
        // Buscar RUC en SUNAT
        Buscar_RUC(documento);
    } else {
        Swal.fire({
            icon: 'warning',
            title: 'Advertencia',
            text: 'El DNI debe tener 8 dígitos o el RUC 11 dígitos',
            confirmButtonColor: '#023D77'
        });
    }
}

function Buscar_DNI(dni) {
    Swal.fire({
        title: 'Buscando...',
        text: 'Consultando datos en RENIEC',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    $.ajax({
        url: '../consulta-dni-ajax.php',
        type: 'POST',
        data: { dni: dni }
    }).done(function(resp) {
        console.log('Respuesta DNI:', resp);
        try {
            var data = JSON.parse(resp);
            console.log('Data parseada:', data);
            
            // Verificar si la respuesta tiene datos (puede venir en diferentes formatos)
            if (data.success === true && data.data) {
                // Formato de decolecta API
                var nombres = data.data.nombres || data.data.first_name || '';
                var apellido_paterno = data.data.apellido_paterno || data.data.first_last_name || '';
                var apellido_materno = data.data.apellido_materno || data.data.second_last_name || '';
                var nombre_completo = (nombres + ' ' + apellido_paterno + ' ' + apellido_materno).trim();
                
                $("#txt_nombre").val(nombre_completo);
                
                Swal.fire({
                    icon: 'success',
                    title: 'Datos encontrados',
                    text: 'Información obtenida de RENIEC',
                    confirmButtonColor: '#023D77',
                    timer: 2000
                });
            } else if (data.first_name) {
                // Formato alternativo (directo sin success/data)
                var nombres = data.first_name || '';
                var apellido_paterno = data.first_last_name || '';
                var apellido_materno = data.second_last_name || '';
                var nombre_completo = (nombres + ' ' + apellido_paterno + ' ' + apellido_materno).trim();
                
                $("#txt_nombre").val(nombre_completo);
                
                Swal.fire({
                    icon: 'success',
                    title: 'Datos encontrados',
                    text: 'Información obtenida de RENIEC',
                    confirmButtonColor: '#023D77',
                    timer: 2000
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'No encontrado',
                    text: data.message || 'No se encontraron datos para este DNI',
                    confirmButtonColor: '#023D77'
                });
            }
        } catch (e) {
            console.error('Error al parsear:', e);
            console.error('Respuesta recibida:', resp);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Error al procesar la respuesta: ' + e.message,
                confirmButtonColor: '#023D77'
            });
        }
    }).fail(function(xhr, status, error) {
        console.error('Error AJAX:', status, error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Error al consultar RENIEC: ' + error,
            confirmButtonColor: '#023D77'
        });
    });
}

function Buscar_RUC(ruc) {
    Swal.fire({
        title: 'Buscando...',
        text: 'Consultando datos en SUNAT',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    $.ajax({
        url: '../consultar-ruc-ajax.php',
        type: 'POST',
        data: { ruc: ruc }
    }).done(function(resp) {
        console.log('Respuesta RUC:', resp);
        try {
            var data = JSON.parse(resp);
            console.log('Data RUC parseada:', data);
            
            // Verificar si la respuesta tiene datos (puede venir en diferentes formatos)
            if (data.success === true && data.data) {
                var razon_social = data.data.razon_social || '';
                var direccion = data.data.direccion_completa || data.data.direccion || '';
                
                $("#txt_nombre").val(razon_social);
                if (direccion) {
                    $("#txt_direccion").val(direccion);
                }
                
                Swal.fire({
                    icon: 'success',
                    title: 'Datos encontrados',
                    text: 'Información obtenida de SUNAT',
                    confirmButtonColor: '#023D77',
                    timer: 2000
                });
            } else if (data.razon_social) {
                // Formato alternativo (directo sin success/data)
                var razon_social = data.razon_social || '';
                var direccion = data.direccion || '';
                
                $("#txt_nombre").val(razon_social);
                if (direccion) {
                    $("#txt_direccion").val(direccion);
                }
                
                Swal.fire({
                    icon: 'success',
                    title: 'Datos encontrados',
                    text: 'Información obtenida de SUNAT',
                    confirmButtonColor: '#023D77',
                    timer: 2000
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'No encontrado',
                    text: data.message || 'No se encontraron datos para este RUC',
                    confirmButtonColor: '#023D77'
                });
            }
        } catch (e) {
            console.error('Error al parsear RUC:', e);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Error al procesar la respuesta: ' + e.message,
                confirmButtonColor: '#023D77'
            });
        }
    }).fail(function(xhr, status, error) {
        console.error('Error AJAX RUC:', status, error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Error al consultar SUNAT: ' + error,
            confirmButtonColor: '#023D77'
        });
    });
}

function Buscar_DNI_RUC_Editar() {
    var documento = $("#txt_dni_editar").val().trim();
    
    if (documento.length == 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Advertencia',
            text: 'Ingrese un DNI o RUC',
            confirmButtonColor: '#023D77'
        });
        return;
    }
    
    if (documento.length == 8) {
        Buscar_DNI_Editar(documento);
    } else if (documento.length == 11) {
        Buscar_RUC_Editar(documento);
    } else {
        Swal.fire({
            icon: 'warning',
            title: 'Advertencia',
            text: 'El DNI debe tener 8 dígitos o el RUC 11 dígitos',
            confirmButtonColor: '#023D77'
        });
    }
}

function Buscar_DNI_Editar(dni) {
    Swal.fire({
        title: 'Buscando...',
        text: 'Consultando datos en RENIEC',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    $.ajax({
        url: '../consulta-dni-ajax.php',
        type: 'POST',
        data: { dni: dni }
    }).done(function(resp) {
        try {
            var data = JSON.parse(resp);
            
            // Verificar si la respuesta tiene datos (puede venir en diferentes formatos)
            if (data.success === true && data.data) {
                var nombres = data.data.nombres || data.data.first_name || '';
                var apellido_paterno = data.data.apellido_paterno || data.data.first_last_name || '';
                var apellido_materno = data.data.apellido_materno || data.data.second_last_name || '';
                var nombre_completo = (nombres + ' ' + apellido_paterno + ' ' + apellido_materno).trim();
                
                $("#txt_nombre_editar").val(nombre_completo);
                
                Swal.fire({
                    icon: 'success',
                    title: 'Datos encontrados',
                    text: 'Información obtenida de RENIEC',
                    confirmButtonColor: '#023D77',
                    timer: 2000
                });
            } else if (data.first_name) {
                // Formato alternativo (directo sin success/data)
                var nombres = data.first_name || '';
                var apellido_paterno = data.first_last_name || '';
                var apellido_materno = data.second_last_name || '';
                var nombre_completo = (nombres + ' ' + apellido_paterno + ' ' + apellido_materno).trim();
                
                $("#txt_nombre_editar").val(nombre_completo);
                
                Swal.fire({
                    icon: 'success',
                    title: 'Datos encontrados',
                    text: 'Información obtenida de RENIEC',
                    confirmButtonColor: '#023D77',
                    timer: 2000
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'No encontrado',
                    text: data.message || 'No se encontraron datos para este DNI',
                    confirmButtonColor: '#023D77'
                });
            }
        } catch (e) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Error al procesar la respuesta',
                confirmButtonColor: '#023D77'
            });
        }
    }).fail(function() {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Error al consultar RENIEC',
            confirmButtonColor: '#023D77'
        });
    });
}

function Buscar_RUC_Editar(ruc) {
    Swal.fire({
        title: 'Buscando...',
        text: 'Consultando datos en SUNAT',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    $.ajax({
        url: '../consultar-ruc-ajax.php',
        type: 'POST',
        data: { ruc: ruc }
    }).done(function(resp) {
        try {
            var data = JSON.parse(resp);
            
            // Verificar si la respuesta tiene datos (puede venir en diferentes formatos)
            if (data.success === true && data.data) {
                var razon_social = data.data.razon_social || '';
                var direccion = data.data.direccion_completa || data.data.direccion || '';
                
                $("#txt_nombre_editar").val(razon_social);
                if (direccion) {
                    $("#txt_direccion_editar").val(direccion);
                }
                
                Swal.fire({
                    icon: 'success',
                    title: 'Datos encontrados',
                    text: 'Información obtenida de SUNAT',
                    confirmButtonColor: '#023D77',
                    timer: 2000
                });
            } else if (data.razon_social) {
                // Formato alternativo (directo sin success/data)
                var razon_social = data.razon_social || '';
                var direccion = data.direccion || '';
                
                $("#txt_nombre_editar").val(razon_social);
                if (direccion) {
                    $("#txt_direccion_editar").val(direccion);
                }
                
                Swal.fire({
                    icon: 'success',
                    title: 'Datos encontrados',
                    text: 'Información obtenida de SUNAT',
                    confirmButtonColor: '#023D77',
                    timer: 2000
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'No encontrado',
                    text: data.message || 'No se encontraron datos para este RUC',
                    confirmButtonColor: '#023D77'
                });
            }
        } catch (e) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Error al procesar la respuesta',
                confirmButtonColor: '#023D77'
            });
        }
    }).fail(function() {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Error al consultar SUNAT',
            confirmButtonColor: '#023D77'
        });
    });
}

$(document).ready(function() {
    Listar_Clientes();
});
