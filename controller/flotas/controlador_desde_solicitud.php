<?php
require_once __DIR__ . '/../_bootstrap.php';
require_once '../../model/model_flotas.php';

responder_json(function () {
    return ["id_cliente" => (new Modelo_Flotas())->Desde_Solicitud((int) ($_POST["id_solicitud"] ?? 0), $_POST, (int) $_SESSION["S_ID"])];
});
