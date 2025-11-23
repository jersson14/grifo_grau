<?php
require_once '../../model/model_conexion.php';

$c = conexionBD::conexionPDO();

// Listar solo usuarios con rol GRIFERO y estado ACTIVO
$sql = "SELECT 
            id_usuario,
            CONCAT(usu_nombre, ' ', usu_apellido) as nombre_completo,
            usu_usuario
        FROM usuario
        WHERE usu_rol = 'GRIFERO'
        AND usu_estatus = 'ACTIVO'
        ORDER BY usu_nombre, usu_apellido";

$stmt = $c->prepare($sql);
$stmt->execute();
$griferos = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($griferos);
?>
