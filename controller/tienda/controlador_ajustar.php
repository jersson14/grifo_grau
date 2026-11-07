<?php
require_once __DIR__ . '/../_bootstrap.php';
require_once '../../model/model_tienda.php';

responder_json(function () {
    return ["stock" => (new Modelo_Tienda())->Ajustar((int) ($_POST["id_producto"] ?? 0), (float) ($_POST["contado"] ?? -1), (string) ($_POST["motivo"] ?? ""), (int) $_SESSION["S_ID"])];
});
