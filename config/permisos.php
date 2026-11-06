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

    'base' => [
        'view/index.php',
        // Quién usa realmente el chatbot se configura en la interfaz (ia_roles).
        'controller/ia/controlador_preguntar.php',
        'controller/ia/controlador_limpiar_conversacion.php',
    ],
];
