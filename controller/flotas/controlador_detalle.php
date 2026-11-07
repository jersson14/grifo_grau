<?php
require_once __DIR__ . '/../_bootstrap.php';
require_once '../../model/model_flotas.php';

responder_json(function () {
    return (new Modelo_Flotas())->Detalle((int) ($_POST["id_cliente"] ?? 0), (string) ($_POST["mes"] ?? date("Y-m")));
});
