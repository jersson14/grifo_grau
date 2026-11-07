<?php
require_once __DIR__ . '/../_bootstrap.php';
require_once '../../model/model_gastos_grifo.php';

responder_json(function () {
    $id = (int) ($_POST["id_categoria"] ?? 0);
    return ["id_categoria" => (new Modelo_Gastos_Grifo())->Guardar_Categoria($id ?: null, (string) ($_POST["nombre"] ?? ""), ($_POST["activo"] ?? "1") === "1")];
});
