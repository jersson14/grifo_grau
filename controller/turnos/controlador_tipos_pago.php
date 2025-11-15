<?php
require_once '../../model/model_conexion.php';

$conexion = new conexionBD();
$c = $conexion->conexionPDO();

$sql = "SELECT * FROM tipos_pago WHERE estado = 'ACTIVO' ORDER BY nombre";
$query = $c->prepare($sql);
$query->execute();
$resultado = $query->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($resultado);
?>
