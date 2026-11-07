<?php
require_once __DIR__ . '/../_bootstrap.php';
require_once '../../model/model_pagina_publica.php';

responder_json(function () {
    (new Modelo_Pagina_Publica())->Eliminar_Promocion((int) ($_POST["id_promocion"] ?? 0));
});
