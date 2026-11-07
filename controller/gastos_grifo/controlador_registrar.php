<?php
require_once __DIR__ . '/../_bootstrap.php';
require_once '../../model/model_gastos_grifo.php';

responder_json(function () {
    $id = (new Modelo_Gastos_Grifo())->Registrar((string) ($_POST["fecha"] ?? ""), (int) ($_POST["id_categoria"] ?? 0), (string) ($_POST["descripcion"] ?? ""),
        (float) ($_POST["monto"] ?? 0), (string) ($_POST["medio_pago"] ?? ""), (string) ($_POST["comprobante"] ?? ""), (int) $_SESSION["S_ID"]);
    return ["id_gasto" => $id];
});
