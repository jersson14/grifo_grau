<?php
require_once __DIR__ . '/../_bootstrap.php';
require_once '../../model/model_creditos.php';

$MCreditos = new Modelo_Creditos();

$id_credito = trim((string) ($_POST['id_credito']));

$consulta = $MCreditos->Listar_Historial_Pagos_Credito($id_credito);

if ($consulta) {
    echo json_encode($consulta);
} else {
    echo json_encode(array("data" => array()));
}
?>
