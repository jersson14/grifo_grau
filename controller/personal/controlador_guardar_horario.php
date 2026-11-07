<?php
require_once __DIR__ . '/../_bootstrap.php';
require_once '../../model/model_personal.php';

responder_json(function () {
    (new Modelo_Personal())->Guardar_Horario((int) ($_POST["id_personal"] ?? 0), (string) ($_POST["fecha"] ?? ""), (string) ($_POST["turno"] ?? ""));
});
