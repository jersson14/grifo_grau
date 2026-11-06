<?php
require_once __DIR__ . '/../_bootstrap.php';
require_once '../../model/model_conexion.php';

responder_json(function () {
    $c = conexionBD::conexionPDO();
    return [
        'surtidores' => $c->query("SELECT s.id_surtidor, s.numero_maquina, s.codigo, p.nombre AS producto, p.precio_actual FROM surtidores s INNER JOIN productos p ON s.id_producto = p.id_producto WHERE s.estado = 'ACTIVO' AND p.estado = 'ACTIVO' ORDER BY s.numero_maquina, s.codigo")->fetchAll(PDO::FETCH_ASSOC),
        'tipos_pago' => $c->query("SELECT id_tipo_pago, nombre, requiere_codigo FROM tipos_pago WHERE estado = 'ACTIVO' AND nombre NOT IN ('DESCUENTO', 'OTROS_GASTOS') ORDER BY id_tipo_pago")->fetchAll(PDO::FETCH_ASSOC),
        'clientes' => $c->query("SELECT id_cliente, nombre_completo, dni_ruc FROM clientes WHERE estado = 'ACTIVO' ORDER BY nombre_completo")->fetchAll(PDO::FETCH_ASSOC),
    ];
});
