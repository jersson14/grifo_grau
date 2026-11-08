<?php
require_once __DIR__ . '/../_bootstrap.php';
require_once '../../model/model_comprobantes.php';

// Vales de un cliente que aún no están en un comprobante, en el periodo elegido.
responder_json(function () {
    [$d, $h] = rango_fechas();
    return ['data' => (new Modelo_Comprobantes())->Vales_Facturables((int) ($_POST['id_cliente'] ?? 0), $d, $h)];
});
