var tabla_productos;

function Listar_Productos() {
    tabla_productos = $("#tabla_productos").DataTable({
        "ordering": true,
        "bLengthChange": true,
        "searching": { "regex": false },
        "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
        "pageLength": 10,
        "destroy": true,
        "async": false,
        "processing": true,
        "ajax": {
            "url": "../controller/productos/controlador_listar_productos.php",
            type: 'POST'
        },
        "columns": [
            { "data": "id_producto" },
            { "data": "nombre" },
            { 
                "data": "tipo",
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
                "data": "estado",
                "render": function(data, type, row) {
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
                        var fecha = Fecha_Local(data);
                        return fecha.toLocaleDateString('es-PE') + ' ' + fecha.toLocaleTimeString('es-PE');
                    }
                    return '-';
                }
            },
            { 
                "data": "updated_at",
                "render": function(data) {
                    if (data) {
                        var fecha = Fecha_Local(data);
                        return fecha.toLocaleDateString('es-PE') + ' ' + fecha.toLocaleTimeString('es-PE');
                    }
                    return '-';
                }
            },
            { "data": "usuario_registro" },
            {
                "defaultContent": "<button class='editar btn btn-warning btn-sm' title='Editar'><i class='fas fa-edit'></i></button>&nbsp;<button class='cambiar_estado btn btn-secondary btn-sm' title='Cambiar Estado'><i class='fas fa-toggle-on'></i></button>"
            }
        ],
        "language": idioma_espanol,
        select: true
    });

    document.getElementById("tabla_productos").addEventListener("click", function(e) {
        if (e.target.closest(".editar")) {
            var data = tabla_productos.row(e.target.closest("tr")).data();
            Abrir_Modal_Editar(data);
        }
        if (e.target.closest(".cambiar_estado")) {
            var data = tabla_productos.row(e.target.closest("tr")).data();
            Cambiar_Estado_Producto(data.id_producto, data.estado);
        }
    });
}

function Listar_Historial() {
    $("#tabla_historial").DataTable({
        "ordering": true,
        "bLengthChange": false,
        "searching": false,
        "pageLength": 10,
        "destroy": true,
        "async": false,
        "processing": true,
        "ajax": {
            "url": "../controller/productos/controlador_historial_precios.php",
            type: 'POST'
        },
        "columns": [
            { "data": "nombre" },
            { "data": "tipo" },
            { 
                "data": "precio_actual",
                "render": function(data, type, fila) {
                    var nuevo = 'S/. ' + parseFloat(data).toFixed(2);
                    return fila.precio_anterior === null ? nuevo : 'S/. ' + parseFloat(fila.precio_anterior).toFixed(2) + ' → ' + nuevo;
                }
            },
            {
                "data": "fecha_cambio",
                "render": function(data) {
                    if (data) {
                        var fecha = Fecha_Local(data);
                        return fecha.toLocaleDateString('es-PE') + ' ' + fecha.toLocaleTimeString('es-PE');
                    }
                    return '-';
                }
            },
            { "data": "modificado_por" }
        ],
        "language": idioma_espanol
    });
}

function AbrirModalRegistro() {
    $("#modal_registro_producto").modal('show');
    $("#txt_nombre").val('');
    $("#txt_tipo").val('');
    $("#txt_precio").val('');
}

function Registrar_Producto() {
    var nombre = $("#txt_nombre").val();
    var tipo = $("#txt_tipo").val();
    var precio = $("#txt_precio").val();
    var id_usuario = $("#txtprincipalid").val();

    if (nombre.length == 0 || tipo.length == 0 || precio.length == 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Advertencia',
            text: 'Todos los campos son obligatorios',
            confirmButtonColor: '#023D77'
        });
        return;
    }

    if (parseFloat(precio) <= 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Advertencia',
            text: 'El precio debe ser mayor a 0',
            confirmButtonColor: '#023D77'
        });
        return;
    }

    $.ajax({
        url: '../controller/productos/controlador_registrar_producto.php',
        type: 'POST',
        data: {
            nombre: nombre,
            tipo: tipo,
            precio: precio,
            id_usuario: id_usuario
        }
    }).done(function(resp) {
        if (resp > 0) {
            Swal.fire({
                icon: 'success',
                title: 'Éxito',
                text: 'Producto registrado correctamente',
                confirmButtonColor: '#023D77'
            });
            tabla_productos.ajax.reload();
            $("#modal_registro_producto").modal('hide');
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Error al registrar el producto',
                confirmButtonColor: '#023D77'
            });
        }
    });
}

function Abrir_Modal_Editar(data) {
    $("#modal_editar_producto").modal('show');
    $("#txt_id_producto").val(data.id_producto);
    $("#txt_nombre_editar").val(data.nombre);
    $("#txt_tipo_editar").val(data.tipo);
    $("#txt_precio_editar").val(data.precio_actual);
}

function Modificar_Producto() {
    var id = $("#txt_id_producto").val();
    var nombre = $("#txt_nombre_editar").val();
    var tipo = $("#txt_tipo_editar").val();
    var precio = $("#txt_precio_editar").val();

    if (nombre.length == 0 || tipo.length == 0 || precio.length == 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Advertencia',
            text: 'Todos los campos son obligatorios',
            confirmButtonColor: '#023D77'
        });
        return;
    }

    if (parseFloat(precio) <= 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Advertencia',
            text: 'El precio debe ser mayor a 0',
            confirmButtonColor: '#023D77'
        });
        return;
    }

    Swal.fire({
        title: '¿Está seguro de actualizar el precio?',
        text: "Este cambio se aplicará a partir del siguiente turno",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#023D77',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, actualizar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '../controller/productos/controlador_modificar_producto.php',
                type: 'POST',
                data: {
                    id: id,
                    nombre: nombre,
                    tipo: tipo,
                    precio: precio
                }
            }).done(function(resp) {
                if (resp > 0) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Éxito',
                        text: 'Producto actualizado correctamente',
                        confirmButtonColor: '#023D77'
                    });
                    tabla_productos.ajax.reload();
                    Listar_Historial();
                    $("#modal_editar_producto").modal('hide');
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Error al actualizar el producto',
                        confirmButtonColor: '#023D77'
                    });
                }
            });
        }
    });
}

function Cambiar_Estado_Producto(id, estado_actual) {
    var nuevo_estado = (estado_actual == 'ACTIVO') ? 'INACTIVO' : 'ACTIVO';
    var texto = (nuevo_estado == 'ACTIVO') ? 'activar' : 'desactivar';

    Swal.fire({
        title: '¿Está seguro de ' + texto + ' este producto?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#023D77',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, ' + texto,
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '../controller/productos/controlador_cambiar_estado_producto.php',
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
                    tabla_productos.ajax.reload();
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

$(document).ready(function() {
    Listar_Productos();
    Listar_Historial();
});

function iniciar_programacion_precios() {
    var ruta = '../controller/productos/controlador_';

    function listar() {
        G.post(ruta + 'listar_programados.php', {}, function (r) {
            G.tabla('#cuerpo_programados', r.data, [
                function (p) { return p.producto; },
                function (p) { return G.soles(p.precio_actual); },
                function (p) { return G.soles(p.precio); },
                function (p) { return p.vigente_desde; },
                function (p) { return G.etiqueta(p.estado, p.estado === 'PENDIENTE' ? 'badge-warning' : (p.estado === 'APLICADO' ? 'badge-success' : 'badge-secondary')); },
                function (p) { return p.usuario; },
                function (p) {
                    return p.estado !== 'PENDIENTE' ? '' : G.boton('Cancelar', 'btn-outline-danger', function () {
                        G.post(ruta + 'cancelar_programado.php', { id_programado: p.id_programado }, function () { G.ok('Cambio cancelado'); listar(); });
                    });
                }
            ], 'No hay cambios programados.');
        });
    }

    $.post(ruta + 'productos_activos.php', function (r) {
        var productos = Array.isArray(r) ? r : (r.data || []);
        G.opciones('#prog_producto', productos, 'id_producto', function (p) { return p.nombre; }, 'Seleccione');
    }, 'json');

    $('#form_programar').on('submit', function (e) {
        e.preventDefault();
        G.post(ruta + 'programar_precio.php', { id_producto: $('#prog_producto').val(), precio: $('#prog_precio').val(), vigente_desde: $('#prog_fecha').val() }, function () {
            $('#prog_precio, #prog_fecha').val('');
            G.ok('Cambio programado');
            listar();
        });
    });

    listar();
}
