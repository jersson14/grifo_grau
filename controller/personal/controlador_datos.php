<?php
require_once __DIR__ . '/../_bootstrap.php';
require_once '../../model/model_personal.php';

responder_json(function () {
    $m = new Modelo_Personal();
    $lista = fn(array $a) => array_map(fn($k, $v) => ["codigo" => $k, "nombre" => $v], array_keys($a), $a);
    return [
        "personal" => $m->Listar(),
        "usuarios" => $m->Usuarios_Libres(isset($_POST["id_personal"]) ? (int) $_POST["id_personal"] : null),
        "cargos" => Modelo_Personal::CARGOS,
        "turnos" => $lista(Modelo_Personal::TURNOS),
        "estados" => $lista(Modelo_Personal::ESTADOS),
        "tipos" => $lista(Modelo_Personal::TIPOS),
    ];
});
