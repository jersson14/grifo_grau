<?php
require_once __DIR__ . '/../_bootstrap.php';
require_once '../../model/model_flotas.php';

responder_json(function () {
    $m = new Modelo_Flotas();
    return ["flotas" => $m->Listar(), "clientes" => $m->Clientes_Sin_Flota()];
});
