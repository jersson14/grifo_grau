<?php
require_once __DIR__ . '/../_bootstrap.php';
require_once '../../model/model_tienda.php';

responder_json(function () {
    (new Modelo_Tienda())->Anular_Venta((int) ($_POST["id_venta"] ?? 0), (string) ($_POST["motivo"] ?? ""), (int) $_SESSION["S_ID"]);
});
