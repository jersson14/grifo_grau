<?php
require_once __DIR__ . '/../_bootstrap.php';
require_once '../../model/model_personal.php';

responder_json(function () {
    (new Modelo_Personal())->Anular_Movimiento((int) ($_POST["id_movimiento"] ?? 0), (string) ($_POST["motivo"] ?? ""), (int) $_SESSION["S_ID"]);
});
