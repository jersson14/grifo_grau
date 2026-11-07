<?php
require_once __DIR__ . '/../_bootstrap.php';
require_once '../../model/model_tienda.php';

responder_json(function () {
    $items = json_decode((string) ($_POST["items"] ?? "[]"), true);
    if (!is_array($items)) {
        throw new DomainException("Revise los productos de la venta.");
    }
    return (new Modelo_Tienda())->Vender($items, (string) ($_POST["medio_pago"] ?? ""), (string) ($_POST["codigo_operacion"] ?? ""), (int) $_SESSION["S_ID"]);
});
