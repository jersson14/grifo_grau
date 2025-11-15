<?php
require_once '../../model/model_creditos.php';

$MCreditos = new Modelo_Creditos();

$id_credito = htmlspecialchars($_POST['id_credito'], ENT_QUOTES, 'UTF-8');

$consulta = $MCreditos->Listar_Historial_Pagos_Credito($id_credito);

if ($consulta) {
    echo json_encode($consulta);
} else {
    echo json_encode(array("data" => array()));
}
?>
