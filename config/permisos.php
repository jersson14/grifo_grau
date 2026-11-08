<?php
/**
 * Rutas fuera del sistema de capacidades (ver config/capacidades.php).
 *
 *  publico  Sin sesión.
 *  base     Para todo usuario con sesión.
 *  propios  Para todo usuario, siempre sobre su propia cuenta: el id_usuario enviado se reemplaza por el de la sesión.
 *  modulos  Rutas que dejan de responder cuando su módulo está desactivado.
 */
return [
    'modulos' => [
        'controller/ia/controlador_preguntar.php'            => 'chatbot_ia',
        'controller/ia/controlador_limpiar_conversacion.php' => 'chatbot_ia',
        'controller/inventario/*' => 'inventario',
        'view/inventario/*'       => 'inventario',
        'controller/compras/*'    => 'inventario',
        'controller/ventas/*'     => 'ventas',
        'view/ventas/*'           => 'ventas',
        'controller/caja/*'       => 'caja',
        'view/caja/*'             => 'caja',
        'controller/facturacion/*' => 'facturacion',
        'view/facturacion/*'      => 'facturacion',
        'controller/gastos_grifo/*' => 'gastos',
        'view/gastos_grifo/*'      => 'gastos',
        'controller/flotas/*'     => 'flotas',
        'controller/tienda/*'     => 'tienda',
        'view/tienda/*'           => 'tienda',
        'view/flotas/*'           => 'flotas',
        'view/MPDF/REPORTE/estado_cuenta_flota.php' => 'flotas',
        'controller/personal/*'   => 'personal',
        'view/personal/*'         => 'personal',
    ],

    'publico' => [
        'index.php',
        'login.php',
        // Solo funciona mientras no existe ningún usuario: lo comprueba la propia página.
        'instalar.php',
        'publico/solicitud_flota.php',
        'controller/usuario/controlador_iniciar_sesion.php',
        'controller/usuario/controlador_cerrar_sesion.php',
    ],

    // Lo único disponible mientras el usuario debe cambiar su contraseña.
    'cambio_clave' => [
        'view/index.php',
        'view/usuario/view_cambiar_clave.php',
        'controller/usuario/controlador_cambiar_contrasena.php',
        'controller/usuario/controlador_cerrar_sesion.php',
    ],

    'propios' => [
        'controller/usuario/controlador_actualizar_perfil.php',
        'controller/usuario/controlador_cambiar_contrasena.php',
        'view/usuario/view_cambiar_clave.php',
        'controller/usuario/controlador_cambiar_foto.php',
        'controller/usuario/controlador_obtener_perfil.php',
        'view/usuario/view_mi_perfil.php',
    ],

    'base' => [
        'view/index.php',
        // Cada usuario recibe solo los avisos que le corresponden: lo decide el controlador.
        'controller/avisos/controlador_listar.php',
        // Quién usa realmente el chatbot se configura en la interfaz (ia_roles).
        'controller/ia/controlador_preguntar.php',
        'controller/ia/controlador_limpiar_conversacion.php',
    ],
];
