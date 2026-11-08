<?php
require_once __DIR__ . '/../_bootstrap.php';
require_once '../../model/model_comprobantes.php';

set_time_limit(120);

// Nota de crédito o de débito sobre una factura o boleta ya enviada. Se guarda y luego se envía.
responder_json(function () {
    $m = new Modelo_Comprobantes();
    $monto = $_POST['monto'] ?? '0';
    $id = $m->Crear_Nota((int) ($_POST['id_referencia'] ?? 0), (string) ($_POST['clase'] ?? ''), (string) ($_POST['motivo'] ?? ''),
        (string) ($_POST['sustento'] ?? ''), is_numeric($monto) ? (float) $monto : 0.0, (int) $_SESSION['S_ID']);
    $f = $m->Procesar($id);
    return ['id_comprobante' => $id, 'comprobante' => $f['serie'] . '-' . $f['numero'], 'estado' => $f['estado'], 'ultimo_error' => $f['ultimo_error']];
});
