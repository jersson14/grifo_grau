<?php
require_once __DIR__ . '/../_bootstrap.php';
require_once '../../model/model_pagina_publica.php';

responder_json(function () {
    $id = (int) ($_POST["id_servicio"] ?? 0);
    $id = (new Modelo_Pagina_Publica())->Guardar_Servicio($id ?: null, (string) ($_POST["titulo"] ?? ""), (string) ($_POST["descripcion"] ?? ""), (string) ($_POST["icono"] ?? ""), ($_POST["activo"] ?? "1") === "1");
    return ["id_servicio" => $id];
});
