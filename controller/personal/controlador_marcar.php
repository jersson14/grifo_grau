<?php
require_once __DIR__ . '/../_bootstrap.php';
require_once '../../model/model_personal.php';

responder_json(function () {
    (new Modelo_Personal())->Marcar_Asistencia((int) ($_POST["id_personal"] ?? 0), (string) ($_POST["fecha"] ?? ""), (string) ($_POST["estado"] ?? ""),
        (string) ($_POST["hora_entrada"] ?? ""), (string) ($_POST["hora_salida"] ?? ""), (string) ($_POST["observacion"] ?? ""), (int) $_SESSION["S_ID"]);
});
