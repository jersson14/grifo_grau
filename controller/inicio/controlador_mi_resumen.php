<?php
require_once __DIR__ . '/../_bootstrap.php';
require_once '../../model/model_inicio.php';

// Resumen propio: siempre del usuario en sesión.
responder_json(function () {
    return (new Modelo_Inicio())->Mi_Resumen((int) $_SESSION['S_ID']);
});
