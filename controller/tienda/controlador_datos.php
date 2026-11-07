<?php
require_once __DIR__ . '/../_bootstrap.php';
require_once '../../model/model_tienda.php';

responder_json(function () {
    $gestiona = puede("tienda_gestionar");
    $lista = fn(array $a) => array_map(fn($k, $v) => ["codigo" => $k, "nombre" => $v], array_keys($a), array_values($a));
    return [
        "productos"  => (new Modelo_Tienda())->Productos(!$gestiona),
        "categorias" => Modelo_Tienda::CATEGORIAS,
        "unidades"   => $lista(Modelo_Tienda::UNIDADES),
        "medios"     => $lista(Modelo_Tienda::MEDIOS),
        "gestiona"   => $gestiona,
    ];
});
