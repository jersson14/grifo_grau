<?php
require_once __DIR__ . '/../_bootstrap.php';
require_once '../../model/model_comprobantes.php';

set_time_limit(120);

// Un comprobante para varios vales de un cliente. Se guarda primero y luego se envía: si Nubefact no responde,
// queda pendiente con el mismo número para reintentar.
responder_json(function () {
    $m = new Modelo_Comprobantes();
    $ids = array_filter(explode(',', (string) ($_POST['vales'] ?? '')));
    $id = $m->Facturar_Vales((int) ($_POST['id_cliente'] ?? 0), $ids, (string) ($_POST['tipo'] ?? ''), (int) $_SESSION['S_ID']);
    $f = $m->Procesar($id);
    return ['id_comprobante' => $id, 'comprobante' => $f['serie'] . '-' . $f['numero'], 'estado' => $f['estado'], 'ultimo_error' => $f['ultimo_error']];
});
