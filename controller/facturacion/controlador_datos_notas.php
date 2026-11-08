<?php
require_once __DIR__ . '/../_bootstrap.php';
require_once '../../model/model_comprobantes.php';

// Motivos de las notas (catálogos de SUNAT) y clientes con vales por facturar.
responder_json(function () {
    $lista = fn(array $a) => array_map(fn($k, $v) => ['codigo' => $k, 'nombre' => $v], array_keys($a), $a);
    return [
        'motivos_nc' => $lista(Modelo_Comprobantes::MOTIVOS_NC),
        'motivos_nd' => $lista(Modelo_Comprobantes::MOTIVOS_ND),
        'clientes' => (new Modelo_Comprobantes())->Clientes_Con_Vales(),
    ];
});
