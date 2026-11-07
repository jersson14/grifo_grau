<?php
require_once __DIR__ . '/../_bootstrap.php';
require_once '../../model/model_pagina_publica.php';

responder_json(function () {
    (new Modelo_Pagina_Publica())->Cambiar_Estado_Solicitud((int) ($_POST["id_solicitud"] ?? 0), (string) ($_POST["estado"] ?? ""), (string) ($_POST["nota"] ?? ""), (int) $_SESSION["S_ID"]);
});
