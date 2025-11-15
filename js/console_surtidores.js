var tabla_surtidores;

function Listar_Surtidores() {
    tabla_surtidores = $("#tabla_surtidores").DataTable({
        "ordering": true,
        "bLengthChange": true,
        "searching": { "regex": false },
        "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
        "pageLength": 10,
        "destroy": true,
        "async": false,
        "processing": true,
        "ajax": {
            "url": "../controller/surtidores/controlador_listar_surtidores.php",
            type: 'POST'
        },
        "columns": [
            { "data": "id_surtidor" },
            { 
                "data": "numero_maquina",
                "render": function(data) {
                    return '<span class="badge badge-primary">Máquina ' + data + '</span>';
                }
            },
            { 
                "data": "codigo",
                "render": function(data) {
                    return '<strong>' + data + '</strong>';
                }
            },
            { "data": "producto_nombre" },
            { 
                "data": "producto_tipo",
                "render": function(data) {
                    if (data == 'DIESEL') {
                        return '<span class="badge badge-dark">DIESEL</span>';
                    } else if (data == 'REGULAR') {
                        return '<span class="badge badge-info">REGULAR</span>';
                    } else if (data == 'PREMIUM') {
                        return '<span class="badge badge-warning">PREMIUM</span>';
                    }
                }
            },
            { 
                "data": "precio_actual",
                "render": function(data) {
                    return 'S/. ' + parseFloat(data).toFixed(2);
                }
            },
            { 
                "data": "lectura_actual",
                "render": function(data) {
                    return '<strong>' + parseFloat(data).toFixed(3) + '</strong>';
                }
            },
            {
                "data": "estado",
                "render": function(data) {
                    if (data == 'ACTIVO') {
                        return '<span class="badge badge-success">ACTIVO</span>';
                    } else if (data == 'INACTIVO') {
                        return '<span class="badge badge-danger">INACTIVO</span>';
                    } else {
                        return '<span class="badge badge-warning">MANTENIMIENTO</span>';
                    }
                }
            },
            { 
                "data": "updated_at",
                "render": function(data) {
                    if (data) {
                        var fecha = new Date(data);
                        return fecha.toLocaleDateString('es-PE') + '<br>' + fecha.toLocaleTimeString('es-PE');
                    }
                    return '-';
                }
            },
            {
                "defaultContent": "<button class='editar btn btn-warning btn-sm' title='Editar'><i class='fas fa-edit'></i></button>&nbsp;<button class='lectura btn btn-info btn-sm' title='Actualizar Lectura'><i class='fas fa-tachometer-alt'></i></button>&nbsp;<button class='cambiar_estado btn btn-secondary btn-sm' title='Cambiar Estado'><i class='fas fa-toggle-on'></i></button>"
            }
        ],
        "language": idioma_espanol,
        select: true
    });

    document.getElementById("tabla_surtidores").addEventListener("click", function(e) {
        if (e.target.closest(".editar")) {
            var data = tabla_surtidores.row(e.target.closest("tr")).data();
            Abrir_Modal_Editar(data);
        }
        if (e.target.closest(".lectura")) {
            var data = tabla_surtidores.row(e.target.closest("tr")).data();
            Abrir_Modal_Lectura(data);
        }
        if (e.target.closest(".cambiar_estado")) {
            var data = tabla_surtidores.row(e.target.closest("tr")).data();
            Cambiar_Estado_Surtidor(data.id_surtidor, data.estado);
        }
    });
}

function Cargar_Productos_Select() {
    $.ajax({
        url: '../controller/productos/controlador_productos_activos.php',
        type: 'POST'
    }).done(function(resp) {
        var data = JSON.parse(resp);
        var opciones = '<option value="">-- Seleccione --</option>';
        data.forEach(function(producto) {
            opciones += '<option value="' + producto.id_producto + '">' + producto.nombre + ' (' + producto.tipo + ')</option>';
        });
        $("#txt_producto").html(opciones);
        $("#txt_producto_editar").html(opciones);
    });
}

function Cargar_Vista_Maquinas() {
    $.ajax({
        url: '../controller/surtidores/controlador_surtidores_por_maquina.php',
        type: 'POST',
        data: { numero_maquina: 1 }
    }).done(function(resp) {
        var data = JSON.parse(resp);
        var html = '';
        data.forEach(function(item) {
            var badge_estado = item.estado == 'ACTIVO' ? 'success' : (item.estado == 'INACTIVO' ? 'danger' : 'warning');
            html += '<tr>';
            html += '<td><strong>' + item.codigo + '</strong></td>';
            html += '<td>' + item.producto_nombre + '</td>';
            html += '<td>' + parseFloat(item.lectura_actual).toFixed(3) + '</td>';
            html += '<td><span class="badge badge-' + badge_estado + '">' + item.estado + '</span></td>';
            html += '</tr>';
        });
        $("#tabla_maquina_1").html(html);
    });

    $.ajax({
        url: '../controller/surtidores/controlador_surtidores_por_maquina.php',
        type: 'POST',
        data: { numero_maquina: 2 }
    }).done(function(resp) {
        var data = JSON.parse(resp);
        var html = '';
        data.forEach(function(item) {
            var badge_estado = item.estado == 'ACTIVO' ? 'success' : (item.estado == 'INACTIVO' ? 'danger' : 'warning');
            html += '<tr>';
            html += '<td><strong>' + item.codigo + '</strong></td>';
            html += '<td>' + item.producto_nombre + '</td>';
            html += '<td>' + parseFloat(item.lectura_actual).toFixed(3) + '</td>';
            html += '<td><span class="badge badge-' + badge_estado + '">' + item.estado + '</span></td>';
            html += '</tr>';
        });
        $("#tabla_maquina_2").html(html);
    });
}

function AbrirModalRegistro() {
    $("#modal_registro_surtidor").modal('show');
    $("#txt_numero_maquina").val('');
    $("#txt_codigo").val('');
    $("#txt_producto").val('');
    $("#txt_lectura_inicial").val('');
    Cargar_Productos_Select();
}

function Registrar_Surtidor() {
    var numero_maquina = $("#txt_numero_maquina").val();
    var codigo = $("#txt_codigo").val().trim().toUpperCase();
    var id_producto = $("#txt_producto").val();
    var lectura_inicial = $("#txt_lectura_inicial").val();

    if (numero_maquina.length == 0 || codigo.length == 0 || id_producto.length == 0 || lectura_inicial.length == 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Advertencia',
            text: 'Todos los campos son obligatorios',
            confirmButtonColor: '#023D77'
        });
        return;
    }

    if (parseFloat(lectura_inicial) < 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Advertencia',
            text: 'La lectura inicial no puede ser negativa',
            confirmButtonColor: '#023D77'
        });
        return;
    }

    $.ajax({
        url: '../controller/surtidores/controlador_registrar_surtidor.php',
        type: 'POST',
        data: {
            numero_maquina: numero_maquina,
            codigo: codigo,
            id_producto: id_producto,
            lectura_inicial: lectura_inicial
        }
    }).done(function(resp) {
        if (resp > 0) {
            Swal.fire({
                icon: 'success',
                title: 'Éxito',
                text: 'Surtidor registrado correctamente',
                confirmButtonColor: '#023D77'
            });
            tabla_surtidores.ajax.reload();
            Cargar_Vista_Maquinas();
            $("#modal_registro_surtidor").modal('hide');
        } else if (resp == -1) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'El código del surtidor ya existe en esta máquina',
                confirmButtonColor: '#023D77'
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Error al registrar el surtidor',
                confirmButtonColor: '#023D77'
            });
        }
    });
}

function Abrir_Modal_Editar(data) {
    $("#modal_editar_surtidor").modal('show');
    $("#txt_id_surtidor").val(data.id_surtidor);
    $("#txt_numero_maquina_editar").val(data.numero_maquina);
    $("#txt_codigo_editar").val(data.codigo);
    $("#txt_producto_editar").val(data.id_producto);
    Cargar_Productos_Select();
}

function Modificar_Surtidor() {
    var id = $("#txt_id_surtidor").val();
    var numero_maquina = $("#txt_numero_maquina_editar").val();
    var codigo = $("#txt_codigo_editar").val().trim().toUpperCase();
    var id_producto = $("#txt_producto_editar").val();

    if (numero_maquina.length == 0 || codigo.length == 0 || id_producto.length == 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Advertencia',
            text: 'Todos los campos son obligatorios',
            confirmButtonColor: '#023D77'
        });
        return;
    }

    $.ajax({
        url: '../controller/surtidores/controlador_modificar_surtidor.php',
        type: 'POST',
        data: {
            id: id,
            numero_maquina: numero_maquina,
            codigo: codigo,
            id_producto: id_producto
        }
    }).done(function(resp) {
        if (resp > 0) {
            Swal.fire({
                icon: 'success',
                title: 'Éxito',
                text: 'Surtidor actualizado correctamente',
                confirmButtonColor: '#023D77'
            });
            tabla_surtidores.ajax.reload();
            Cargar_Vista_Maquinas();
            $("#modal_editar_surtidor").modal('hide');
        } else if (resp == -1) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'El código del surtidor ya existe en esta máquina',
                confirmButtonColor: '#023D77'
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Error al actualizar el surtidor',
                confirmButtonColor: '#023D77'
            });
        }
    });
}

function Abrir_Modal_Lectura(data) {
    $("#modal_actualizar_lectura").modal('show');
    $("#txt_id_surtidor_lectura").val(data.id_surtidor);
    $("#txt_info_surtidor").text('Máquina ' + data.numero_maquina + ' - ' + data.codigo + ' (' + data.producto_nombre + ')');
    $("#txt_lectura_actual_info").text(parseFloat(data.lectura_actual).toFixed(3));
    $("#txt_nueva_lectura").val('');
}

function Actualizar_Lectura() {
    var id = $("#txt_id_surtidor_lectura").val();
    var nueva_lectura = $("#txt_nueva_lectura").val();

    if (nueva_lectura.length == 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Advertencia',
            text: 'Debe ingresar la nueva lectura',
            confirmButtonColor: '#023D77'
        });
        return;
    }

    if (parseFloat(nueva_lectura) < 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Advertencia',
            text: 'La lectura no puede ser negativa',
            confirmButtonColor: '#023D77'
        });
        return;
    }

    Swal.fire({
        title: '¿Está seguro de actualizar la lectura?',
        text: "Esta acción modificará el contador del surtidor",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#023D77',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, actualizar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '../controller/surtidores/controlador_actualizar_lectura.php',
                type: 'POST',
                data: {
                    id: id,
                    lectura: nueva_lectura
                }
            }).done(function(resp) {
                if (resp > 0) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Éxito',
                        text: 'Lectura actualizada correctamente',
                        confirmButtonColor: '#023D77'
                    });
                    tabla_surtidores.ajax.reload();
                    Cargar_Vista_Maquinas();
                    $("#modal_actualizar_lectura").modal('hide');
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Error al actualizar la lectura',
                        confirmButtonColor: '#023D77'
                    });
                }
            });
        }
    });
}

function Cambiar_Estado_Surtidor(id, estado_actual) {
    var opciones = '<select id="select_nuevo_estado" class="form-control">';
    opciones += '<option value="ACTIVO" ' + (estado_actual == 'ACTIVO' ? 'selected' : '') + '>ACTIVO</option>';
    opciones += '<option value="INACTIVO" ' + (estado_actual == 'INACTIVO' ? 'selected' : '') + '>INACTIVO</option>';
    opciones += '<option value="MANTENIMIENTO" ' + (estado_actual == 'MANTENIMIENTO' ? 'selected' : '') + '>MANTENIMIENTO</option>';
    opciones += '</select>';

    Swal.fire({
        title: 'Cambiar Estado del Surtidor',
        html: '<label>Seleccione el nuevo estado:</label>' + opciones,
        showCancelButton: true,
        confirmButtonColor: '#023D77',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Actualizar',
        cancelButtonText: 'Cancelar',
        preConfirm: () => {
            return document.getElementById('select_nuevo_estado').value;
        }
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '../controller/surtidores/controlador_cambiar_estado_surtidor.php',
                type: 'POST',
                data: {
                    id: id,
                    estado: result.value
                }
            }).done(function(resp) {
                if (resp > 0) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Éxito',
                        text: 'Estado actualizado correctamente',
                        confirmButtonColor: '#023D77'
                    });
                    tabla_surtidores.ajax.reload();
                    Cargar_Vista_Maquinas();
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

function Filtrar_Por_Maquina() {
    var maquina = $("#filtro_maquina").val();
    if (maquina) {
        tabla_surtidores.column(1).search(maquina).draw();
    } else {
        tabla_surtidores.column(1).search('').draw();
    }
}

function Filtrar_Por_Estado() {
    var estado = $("#filtro_estado").val();
    if (estado) {
        tabla_surtidores.column(7).search(estado).draw();
    } else {
        tabla_surtidores.column(7).search('').draw();
    }
}

$(document).ready(function() {
    Listar_Surtidores();
    Cargar_Vista_Maquinas();
});
