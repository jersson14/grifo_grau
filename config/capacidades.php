<?php
/**
 * Catálogo de capacidades asignables a un rol desde Roles y permisos.
 * Cada una agrupa las rutas que necesita. ADMINISTRADOR las tiene todas; una ruta que no
 * aparece aquí ni en config/permisos.php es solo para ADMINISTRADOR.
 */
$turnoLectura = [
    'view/MPDF/REPORTE/reporte_turno.php',
    'controller/turnos/controlador_buscar_turno.php',
    'controller/turnos/controlador_detalle_lecturas.php',
    'controller/turnos/controlador_detalle_turno.php',
    'controller/turnos/controlador_listar_creditos_turno.php',
    'controller/turnos/controlador_listar_pagos_turno.php',
    'controller/turnos/controlador_listar_turnos.php',
];
$lecturasBase = [
    'controller/productos/controlador_listar_productos.php',
    'controller/productos/controlador_productos_activos.php',
    'controller/surtidores/controlador_listar_surtidores.php',
    'controller/surtidores/controlador_surtidores_activos.php',
    'controller/surtidores/controlador_surtidores_por_maquina.php',
];

return [
    'turnos_registrar' => ['Turnos', 'Registrar y cerrar turnos', array_merge($turnoLectura, $lecturasBase, [
        'view/turnos/view_registrar_turno.php',
        'view/turnos/view_abrir_turno.php',
        'view/turnos/view_cerrar_turno.php',
        'view/reportes/view_mis_reportes.php',
        'controller/usuario/controlador_listar_griferos.php',
        'controller/turnos/controlador_abrir_turno.php',
        'controller/turnos/controlador_actualizar_lectura_turno.php',
        'controller/turnos/controlador_calcular_totales_turno.php',
        'controller/turnos/controlador_cerrar_turno.php',
        'controller/turnos/controlador_cuadre_caja.php',
        'controller/turnos/controlador_eliminar_credito.php',
        'controller/turnos/controlador_eliminar_pago.php',
        'controller/turnos/controlador_eliminar_pago_reporte.php',
        'controller/turnos/controlador_generar_numero_documento.php',
        'controller/turnos/controlador_obtener_lecturas_turno.php',
        'controller/turnos/controlador_obtener_turno_abierto.php',
        'controller/turnos/controlador_obtener_turno_sistema.php',
        'controller/turnos/controlador_registrar_credito.php',
        'controller/turnos/controlador_registrar_pago.php',
        'controller/turnos/controlador_registrar_turno_completo.php',
        'controller/turnos/controlador_tipos_pago.php',
        'controller/turnos/controlador_verificar_turno_abierto.php',
        'controller/turnos/controlador_verificar_turno_sistema.php',
    ])],
    'turnos_historial' => ['Turnos', 'Ver el historial de turnos', array_merge($turnoLectura, ['view/turnos/view_historial.php', 'controller/usuario/controlador_listar_griferos.php'])],
    'turnos_validar'   => ['Turnos', 'Validar turnos', array_merge($turnoLectura, [
        'view/reportes/view_validar_reportes.php',
        'controller/turnos/controlador_validar_reporte.php',
        'controller/turnos/controlador_reportes_pendientes.php',
    ])],
    'turnos_eliminar'  => ['Turnos', 'Eliminar turnos', ['controller/turnos/controlador_eliminar_turno.php']],
    'ver_todos'        => ['Turnos', 'Ver datos de todos los griferos (si no, solo los propios)', []],

    'dashboard_ver'        => ['Reportes', 'Ver el Dashboard', ['view/reportes/view_dashboard.php', 'controller/dashboard/*', 'controller/reportes/*']],
    'avisos'               => ['Reportes', 'Recibir avisos en el Dashboard', []],
    'reportes_gerenciales' => ['Reportes', 'Ver reportes de ventas y listados', array_merge($turnoLectura, ['view/reportes/view_ventas_por_fecha.php', 'view/reportes/view_listado_reportes.php', 'controller/reportes/*', 'controller/usuario/controlador_listar_griferos.php'])],

    'clientes_gestionar' => ['Clientes', 'Ver, registrar y editar clientes', [
        'view/clientes/view_clientes.php',
        'consulta-dni-ajax.php',
        'consultar-ruc-ajax.php',
        'controller/clientes/controlador_clientes_activos.php',
        'controller/clientes/controlador_creditos_cliente.php',
        'controller/clientes/controlador_detalle_cliente.php',
        'controller/clientes/controlador_listar_clientes.php',
        'controller/clientes/controlador_modificar_cliente.php',
        'controller/clientes/controlador_obtener_cliente.php',
        'controller/clientes/controlador_registrar_cliente.php',
    ]],
    'clientes_estado' => ['Clientes', 'Activar y desactivar clientes', ['controller/clientes/controlador_cambiar_estado_cliente.php']],

    'creditos_cobrar' => ['Créditos', 'Ver créditos, agregarlos y registrar pagos', [
        'view/creditos/view_creditos_pendientes.php',
        'controller/creditos/controlador_agregar_credito_manual.php',
        'controller/creditos/controlador_agregar_creditos_multiple.php',
        'controller/creditos/controlador_creditos_vencidos.php',
        'controller/creditos/controlador_detalle_credito.php',
        'controller/creditos/controlador_exportar_creditos_excel.php',
        'controller/creditos/controlador_exportar_creditos_pdf.php',
        'controller/creditos/controlador_exportar_historial_vales_excel.php',
        'controller/creditos/controlador_exportar_historial_vales_pdf.php',
        'controller/creditos/controlador_historial_pagos_credito.php',
        'controller/creditos/controlador_listar_creditos.php',
        'controller/creditos/controlador_listar_creditos_por_cliente.php',
        'controller/creditos/controlador_listar_vales_cliente.php',
        'controller/creditos/controlador_pagar_todo_cliente.php',
        'controller/creditos/controlador_registrar_pago_credito.php',
        'controller/creditos/controlador_resumen_creditos.php',
        'controller/creditos/controlador_top_deudores.php',
        'controller/creditos/controlador_ultimos_vales.php',
    ]],
    'creditos_gestionar' => ['Créditos', 'Editar, anular y eliminar créditos y pagos', [
        'controller/creditos/controlador_anular_credito.php',
        'controller/creditos/controlador_anular_pago_credito.php',
        'controller/creditos/controlador_editar_credito.php',
        'controller/creditos/controlador_eliminar_credito.php',
        'controller/creditos/controlador_revertir_monto_cliente.php',
    ]],

    'ventas_registrar' => ['Ventas', 'Registrar ventas', [
        'view/ventas/view_ventas.php',
        'controller/ventas/controlador_datos_formulario.php',
        'controller/ventas/controlador_registrar_venta.php',
        'controller/ventas/controlador_listar_ventas.php',
    ]],
    'ventas_gestionar' => ['Ventas', 'Anular ventas y ver la conciliación', ['controller/ventas/controlador_anular_venta.php', 'controller/ventas/controlador_conciliacion.php']],

    'varillaje_registrar' => ['Inventario', 'Registrar mediciones de varillaje', [
        'view/inventario/view_varillaje.php',
        'controller/inventario/controlador_listar_tanques.php',
        'controller/inventario/controlador_registrar_varillaje.php',
        'controller/inventario/controlador_listar_varillajes.php',
    ]],
    'inventario_gestionar' => ['Inventario', 'Gestionar tanques, kardex y ajustes de stock', ['view/inventario/view_tanques.php', 'controller/inventario/*', 'controller/productos/controlador_productos_activos.php']],
    'compras_gestionar'    => ['Inventario', 'Gestionar compras y proveedores', ['view/inventario/view_compras.php', 'controller/compras/*', 'controller/inventario/controlador_listar_tanques.php']],

    'caja_gestionar' => ['Caja', 'Arqueos, diferencias y depósitos', ['view/caja/*', 'controller/caja/*']],

    'comprobantes_gestionar' => ['Ventas', 'Gestionar comprobantes electrónicos', ['view/facturacion/*', 'controller/facturacion/*']],

    'productos_gestionar'  => ['Catálogos', 'Gestionar combustibles y precios', ['view/productos/view_productos.php', 'controller/productos/*']],
    'surtidores_gestionar' => ['Catálogos', 'Gestionar surtidores', ['view/surtidores/view_surtidores.php', 'controller/surtidores/*', 'controller/productos/controlador_productos_activos.php']],

    'usuarios_gestionar' => ['Administración', 'Gestionar usuarios (nunca administradores)', [
        'view/usuario/view_usuario.php',
        'controller/usuario/controlador_listar_usuario.php',
        'controller/usuario/controlador_modificar_usuario.php',
        'controller/usuario/controlador_modificar_usuario_contra.php',
        'controller/usuario/controlador_modificar_usuario_estatus.php',
        'controller/usuario/controlador_registro_usuario.php',
    ]],
    'auditoria_ver' => ['Administración', 'Consultar la auditoría', ['view/administracion/view_auditoria.php', 'controller/administracion/controlador_listar_auditoria.php']],
];
