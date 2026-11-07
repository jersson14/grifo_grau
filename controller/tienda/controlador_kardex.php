<?php
require_once __DIR__ . '/../_bootstrap.php';
require_once '../../model/model_tienda.php';

responder_json(function () {
    return ["movimientos" => (new Modelo_Tienda())->Kardex((int) ($_POST["id_producto"] ?? 0))];
});
