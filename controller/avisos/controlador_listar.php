<?php
require_once __DIR__ . '/../_bootstrap.php';
require_once '../../model/model_alertas.php';

// Avisos de la campana. Solo para quien recibe avisos, y solo los que llevan a una pantalla que puede abrir.
responder_json(function () {
    if (!puede('avisos')) {
        return ['activo' => false, 'data' => []];
    }
    return ['activo' => true, 'data' => Modelo_Alertas::Visibles((new Modelo_Alertas())->Listar())];
});
