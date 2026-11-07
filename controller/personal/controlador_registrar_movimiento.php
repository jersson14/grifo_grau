<?php
require_once __DIR__ . '/../_bootstrap.php';
require_once '../../model/model_personal.php';

responder_json(function () {
    $monto = $_POST["monto"] ?? "";
    if (!is_numeric($monto)) {
        throw new DomainException("El monto debe ser un número.");
    }
    return ["id_movimiento" => (new Modelo_Personal())->Registrar_Movimiento((int) ($_POST["id_personal"] ?? 0), (string) ($_POST["fecha"] ?? ""),
        (string) ($_POST["tipo"] ?? ""), (float) $monto, (string) ($_POST["motivo"] ?? ""), (int) $_SESSION["S_ID"])];
});
