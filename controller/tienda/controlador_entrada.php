<?php
require_once __DIR__ . '/../_bootstrap.php';
require_once '../../model/model_tienda.php';

responder_json(function () {
    $costo = trim((string) ($_POST["costo"] ?? ""));
    return ["stock" => (new Modelo_Tienda())->Entrada((int) ($_POST["id_producto"] ?? 0), (float) ($_POST["cantidad"] ?? 0), $costo === "" ? null : (float) $costo, (string) ($_POST["detalle"] ?? ""), (int) $_SESSION["S_ID"])];
});
