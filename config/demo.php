<?php
/**
 * Modo demostración: se enciende con MODO_DEMO=1 en el .env del servidor (o en el entorno).
 * Va fuera de la base de datos a propósito: quien entra a la demo como administrador no debe poder apagarlo.
 *
 *  modulos  Se tratan como desactivados aunque la tabla `modulos` diga otra cosa: desaparecen del menú y no responden.
 *  rutas    No responden para nadie, ni para el administrador.
 *  cuentas  Se muestran en el acceso para entrar con un clic. Solo salen las que existen y están activas;
 *           en la demo nadie puede cambiar contraseñas, así que siguen sirviendo.
 */
return [
    'cuentas' => [
        ['usuario' => 'admin', 'clave' => 'admin123', 'perfil' => 'Administrador', 'detalle' => 'Ve todo: tablero, reportes, caja, créditos y flotas'],
        ['usuario' => 'supervisor1', 'clave' => 'supervisor123', 'perfil' => 'Supervisor', 'detalle' => 'Turnos, caja, inventario y flotas'],
        ['usuario' => 'contador1', 'clave' => 'contador123', 'perfil' => 'Contador', 'detalle' => 'Reportes, créditos, gastos y compras'],
        ['usuario' => 'grifero1', 'clave' => 'grifero123', 'perfil' => 'Grifero', 'detalle' => 'Abre y cierra su turno y registra ventas'],
    ],

    'modulos' => ['facturacion', 'chatbot_ia', 'personal'],

    'rutas' => [
        // Usuarios: ni listarlos, ni crearlos, ni cambiar contraseñas o perfiles.
        'view/usuario/*',
        'controller/usuario/controlador_listar_usuario.php',
        'controller/usuario/controlador_registro_usuario.php',
        'controller/usuario/controlador_modificar_usuario.php',
        'controller/usuario/controlador_modificar_usuario_contra.php',
        'controller/usuario/controlador_modificar_usuario_estatus.php',
        'controller/usuario/controlador_actualizar_perfil.php',
        'controller/usuario/controlador_cambiar_contrasena.php',
        'controller/usuario/controlador_cambiar_foto.php',
        'controller/usuario/controlador_obtener_perfil.php',
        // Roles y permisos, editor del menú y auditoría.
        'view/administracion/*',
        'controller/administracion/*',
        // Configuración y respaldos.
        'view/configuracion/*',
        'controller/configuracion/*',
        'controller/respaldos/*',
        // Análisis y rentabilidad.
        'view/reportes/view_analisis.php',
        'controller/analisis/*',
        'view/MPDF/REPORTE/analisis.php',
    ],
];
