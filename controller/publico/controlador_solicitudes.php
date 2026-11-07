<?php
require_once __DIR__ . '/../_bootstrap.php';
require_once '../../model/model_pagina_publica.php';

responder_json(function () {
    return ["solicitudes" => (new Modelo_Pagina_Publica())->Solicitudes((string) ($_POST["estado"] ?? ""))];
});
