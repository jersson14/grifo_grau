<?php
require_once __DIR__ . '/../_bootstrap.php';
require_once '../../model/model_gastos_grifo.php';

responder_json(function () {
    (new Modelo_Gastos_Grifo())->Anular((int) ($_POST["id_gasto"] ?? 0), (string) ($_POST["motivo"] ?? ""), (int) $_SESSION["S_ID"]);
});
