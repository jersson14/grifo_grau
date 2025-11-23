<?php
require_once '../../model/model_conexion.php';

$c = conexionBD::conexionPDO();

$id_cliente = $_POST['id_cliente'];

$sql = "SELECT 
            id_cliente,
            nombre_completo,
            COALESCE(dni_ruc, '') as dni_ruc,
            COALESCE(telefono, '') as telefono,
            COALESCE(direccion, '') as direccion,
            estado
        FROM clientes 
        WHERE id_cliente = ?";
        
$stmt = $c->prepare($sql);
$stmt->execute(array($id_cliente));
$cliente = $stmt->fetch(PDO::FETCH_ASSOC);

if ($cliente) {
    echo json_encode($cliente);
} else {
    echo json_encode([
        'id_cliente' => 0,
        'nombre_completo' => '',
        'dni_ruc' => '',
        'telefono' => '',
        'direccion' => '',
        'estado' => ''
    ]);
}
?>
