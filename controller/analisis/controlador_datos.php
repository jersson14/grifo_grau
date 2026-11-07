<?php
require_once __DIR__ . '/../_bootstrap.php';
require_once '../../model/model_analisis.php';

// Una sección del análisis para el periodo pedido. La morosidad siempre se calcula a hoy.
responder_json(function () {
    $m = new Modelo_Analisis();
    $desde = (string) ($_POST['desde'] ?? '');
    $hasta = (string) ($_POST['hasta'] ?? '');
    switch ((string) ($_POST['seccion'] ?? '')) {
        case 'rentabilidad': return $m->Rentabilidad($desde, $hasta);
        case 'griferos':     return $m->Griferos($desde, $hasta);
        case 'morosidad':    return $m->Morosidad();
        case 'horario':      return $m->Horario($desde, $hasta);
    }
    throw new DomainException('Sección desconocida.');
});
