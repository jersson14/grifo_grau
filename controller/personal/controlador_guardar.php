<?php
require_once __DIR__ . '/../_bootstrap.php';
require_once '../../model/model_personal.php';

responder_json(function () {
    $id = (int) ($_POST["id_personal"] ?? 0);
    return ["id_personal" => (new Modelo_Personal())->Guardar($id ?: null, $_POST)];
});
