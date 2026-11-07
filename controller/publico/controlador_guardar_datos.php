<?php
require_once __DIR__ . '/../_bootstrap.php';
require_once '../../model/model_pagina_publica.php';

responder_json(function () {
    $errores = (new Modelo_Pagina_Publica())->Guardar($_POST);
    if ($errores) {
        return ["errores" => $errores, "guardado" => false];
    }
    return ["guardado" => true];
});
