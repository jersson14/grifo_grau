<?php
require_once __DIR__ . '/../_bootstrap.php';
require_once '../../model/model_flotas.php';

// Aviso para el formulario del turno. Con el módulo de flotas apagado responde que no es flota: nunca bloquea el turno.
responder_json(function () {
    if (!modulo_activo('flotas')) {
        return ['es_flota' => false];
    }
    return (new Modelo_Flotas())->Verificar((int) ($_POST['id_cliente'] ?? 0), (string) ($_POST['placa'] ?? ''), (float) ($_POST['monto'] ?? 0));
});
