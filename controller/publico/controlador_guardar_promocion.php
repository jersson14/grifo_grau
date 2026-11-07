<?php
require_once __DIR__ . '/../_bootstrap.php';
require_once '../../model/model_pagina_publica.php';

responder_json(function () {
    $id = (int) ($_POST["id_promocion"] ?? 0);
    $id = (new Modelo_Pagina_Publica())->Guardar_Promocion($id ?: null, (string) ($_POST["titulo"] ?? ""), (string) ($_POST["detalle"] ?? ""), (string) ($_POST["desde"] ?? ""), (string) ($_POST["hasta"] ?? ""), ($_POST["activo"] ?? "1") === "1", (int) $_SESSION["S_ID"]);
    return ["id_promocion" => $id];
});
