<?php
require_once '../../model/model_turnos.php';

$MTurnos = new Modelo_Turnos();

$numero_documento = htmlspecialchars($_POST['numero_documento'], ENT_QUOTES, 'UTF-8');
$id_usuario = htmlspecialchars($_POST['id_usuario'], ENT_QUOTES, 'UTF-8');
$turno = htmlspecialchars($_POST['turno'], ENT_QUOTES, 'UTF-8');
$fecha = htmlspecialchars($_POST['fecha'], ENT_QUOTES, 'UTF-8');
$hora_inicio = htmlspecialchars($_POST['hora_inicio'], ENT_QUOTES, 'UTF-8');
$hora_fin = htmlspecialchars($_POST['hora_fin'], ENT_QUOTES, 'UTF-8');

// Verificar que no tenga turno abierto
$tiene_turno = $MTurnos->Verificar_Turno_Abierto($id_usuario);
if ($tiene_turno > 0) {
    echo 0;
    exit;
}

// Abrir turno
$id_reporte = $MTurnos->Abrir_Turno($numero_documento, $id_usuario, $turno, $fecha, $hora_inicio, $hora_fin);

if ($id_reporte > 0) {
    // Registrar lecturas iniciales
    $MTurnos->Registrar_Lecturas_Iniciales($id_reporte);
    echo $id_reporte;
} else {
    echo 0;
}
?>
