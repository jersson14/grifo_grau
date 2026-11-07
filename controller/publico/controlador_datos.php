<?php
require_once __DIR__ . '/../_bootstrap.php';
require_once '../../model/model_pagina_publica.php';

responder_json(function () {
    $m = new Modelo_Pagina_Publica();
    $iconos = [];
    foreach (Modelo_Pagina_Publica::ICONOS as $codigo => $nombre) {
        $iconos[] = ["codigo" => $codigo, "nombre" => $nombre];
    }
    return [
        "datos"        => $m->Datos(),
        "servicios"    => $m->Servicios(false),
        "promociones"  => $m->Promociones(),
        "iconos"       => $iconos,
        "activa"       => modulo_activo("pagina_publica"),
        "nuevas"       => $m->Solicitudes_Nuevas(),
    ];
});
