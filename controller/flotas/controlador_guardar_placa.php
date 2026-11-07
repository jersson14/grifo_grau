<?php
require_once __DIR__ . '/../_bootstrap.php';
require_once '../../model/model_flotas.php';

responder_json(function () {
    $id = (int) ($_POST["id_placa"] ?? 0);
    return ["id_placa" => (new Modelo_Flotas())->Guardar_Placa((int) ($_POST["id_cliente"] ?? 0), $id ?: null, (string) ($_POST["placa"] ?? ""), (string) ($_POST["vehiculo"] ?? ""), (string) ($_POST["conductor"] ?? ""), ($_POST["activo"] ?? "1") === "1")];
});
