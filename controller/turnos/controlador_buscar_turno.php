<?php
require_once '../../model/model_conexion.php';

$c = conexionBD::conexionPDO();

$fecha = $_POST['fecha'];
$id_usuario = $_POST['id_usuario'];
$turno = $_POST['turno'];

$sql = "SELECT 
            rt.*,
            CONCAT(u.usu_nombre, ' ', u.usu_apellido) as grifero_nombre
        FROM reportes_turno rt
        INNER JOIN usuario u ON rt.id_usuario = u.id_usuario
        WHERE rt.fecha_reporte = ?
        AND rt.id_usuario = ?
        AND rt.turno = ?
        AND rt.estado = 'CERRADO'
        LIMIT 1";

$stmt = $c->prepare($sql);
$stmt->execute(array($fecha, $id_usuario, $turno));
$turno_data = $stmt->fetch(PDO::FETCH_ASSOC);

if ($turno_data) {
    $turno_data['encontrado'] = true;
    echo json_encode($turno_data);
} else {
    echo json_encode(['encontrado' => false]);
}
?>
