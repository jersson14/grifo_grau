<?php
require_once __DIR__ . '/../_bootstrap.php';
require_once '../../model/model_personal.php';

responder_json(function () {
    return ["copiados" => (new Modelo_Personal())->Copiar_Semana_Anterior((string) ($_POST["lunes"] ?? ""))];
});
