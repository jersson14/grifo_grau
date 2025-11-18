<?php
header('Content-Type: application/json');

try {
    require_once '../../model/model_conexion.php';
    
    $c = conexionBD::conexionPDO();
    
    if (!isset($_POST['id_usuario']) || empty($_POST['id_usuario'])) {
        echo json_encode(['error' => 'ID de usuario no proporcionado']);
        exit;
    }
    
    $id_usuario = $_POST['id_usuario'];
    
    $sql = "SELECT * FROM usuario WHERE id_usuario = ?";
    $stmt = $c->prepare($sql);
    $stmt->execute(array($id_usuario));
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($usuario) {
        echo json_encode($usuario);
    } else {
        echo json_encode(['error' => 'Usuario no encontrado']);
    }
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
