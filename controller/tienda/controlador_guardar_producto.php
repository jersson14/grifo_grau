<?php
require_once __DIR__ . '/../_bootstrap.php';
require_once '../../model/model_tienda.php';

responder_json(function () {
    $id = (int) ($_POST["id_producto"] ?? 0);
    return ["id_producto" => (new Modelo_Tienda())->Guardar_Producto($id ?: null, $_POST, (int) $_SESSION["S_ID"])];
});
