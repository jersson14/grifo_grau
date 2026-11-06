<?php
/**
 * Permisos de acceso por archivo. Lo que no aparece aquí es solo para ADMINISTRADOR.
 *
 *  publico    Sin sesión.
 *  griferos   También para GRIFERO. Admite comodín final: "controller/reportes/*".
 *  propios    Disponibles para todo usuario, siempre sobre su propia cuenta:
 *             el id_usuario enviado se reemplaza por el de la sesión.
 *  modulos    Rutas que dejan de responder cuando su módulo está desactivado.
 */
return [
    'modulos' => [
        'controller/ia/controlador_preguntar.php'            => 'chatbot_ia',
        'controller/ia/controlador_limpiar_conversacion.php' => 'chatbot_ia',
    ],

    'publico' => [
        'index.php',
        'controller/usuario/controlador_iniciar_sesion.php',
        'controller/usuario/controlador_cerrar_sesion.php',
    ],

    'propios' => [
        'controller/usuario/controlador_actualizar_perfil.php',
        'controller/usuario/controlador_cambiar_contrasena.php',
        'controller/usuario/controlador_cambiar_foto.php',
        'controller/usuario/controlador_obtener_perfil.php',
        'view/usuario/view_mi_perfil.php',
    ],

    'griferos' => [
        'view/index.php',
        'view/turnos/view_registrar_turno.php',
        'view/creditos/view_creditos_pendientes.php',
        'view/reportes/view_mis_reportes.php',
        'view/reportes/view_dashboard.php',
        'view/clientes/view_clientes.php',
        'view/MPDF/REPORTE/reporte_turno.php',

        'consulta-dni-ajax.php',
        'consultar-ruc-ajax.php',

        'controller/dashboard/*',
        'controller/reportes/*',

        'controller/clientes/controlador_clientes_activos.php',
        'controller/clientes/controlador_creditos_cliente.php',
        'controller/clientes/controlador_detalle_cliente.php',
        'controller/clientes/controlador_listar_clientes.php',
        'controller/clientes/controlador_modificar_cliente.php',
        'controller/clientes/controlador_obtener_cliente.php',
        'controller/clientes/controlador_registrar_cliente.php',

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

        'controller/productos/controlador_listar_productos.php',
        'controller/productos/controlador_productos_activos.php',

        'controller/surtidores/controlador_listar_surtidores.php',
        'controller/surtidores/controlador_surtidores_activos.php',
        'controller/surtidores/controlador_surtidores_por_maquina.php',

        'controller/turnos/controlador_abrir_turno.php',
        'controller/turnos/controlador_actualizar_lectura_turno.php',
        'controller/turnos/controlador_buscar_turno.php',
        'controller/turnos/controlador_calcular_totales_turno.php',
        'controller/turnos/controlador_cerrar_turno.php',
        'controller/turnos/controlador_cuadre_caja.php',
        'controller/turnos/controlador_detalle_lecturas.php',
        'controller/turnos/controlador_detalle_turno.php',
        'controller/turnos/controlador_eliminar_credito.php',
        'controller/turnos/controlador_eliminar_pago.php',
        'controller/turnos/controlador_eliminar_pago_reporte.php',
        'controller/turnos/controlador_generar_numero_documento.php',
        'controller/turnos/controlador_listar_creditos_turno.php',
        'controller/turnos/controlador_listar_pagos_turno.php',
        'controller/turnos/controlador_listar_turnos.php',
        'controller/turnos/controlador_obtener_lecturas_turno.php',
        'controller/turnos/controlador_obtener_turno_abierto.php',
        'controller/turnos/controlador_obtener_turno_sistema.php',
        'controller/turnos/controlador_registrar_credito.php',
        'controller/turnos/controlador_registrar_pago.php',
        'controller/turnos/controlador_registrar_turno_completo.php',
        'controller/turnos/controlador_tipos_pago.php',
        'controller/turnos/controlador_verificar_turno_abierto.php',
        'controller/turnos/controlador_verificar_turno_sistema.php',

        'controller/usuario/controlador_listar_griferos.php',

        // El rol con acceso real al chatbot se configura en la interfaz (ia_roles).
        'controller/ia/controlador_preguntar.php',
        'controller/ia/controlador_limpiar_conversacion.php',
    ],
];
