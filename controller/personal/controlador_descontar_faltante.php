<?php
require_once __DIR__ . '/../_bootstrap.php';
require_once '../../model/model_personal.php';

responder_json(function () {
    return ["id_movimiento" => (new Modelo_Personal())->Descontar_Faltante((int) ($_POST["id_arqueo"] ?? 0), (int) $_SESSION["S_ID"])];
});
