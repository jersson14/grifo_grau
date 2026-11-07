<?php
require_once __DIR__ . '/../_bootstrap.php';
require_once '../../model/model_personal.php';

responder_json(function () {
    return (new Modelo_Personal())->Resumen_Mes((string) ($_POST["mes"] ?? ""));
});
