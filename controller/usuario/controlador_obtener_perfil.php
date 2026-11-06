<?php
require_once __DIR__ . '/../_bootstrap.php';
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
        unset($usuario['usu_contrasenia']);
        require_once '../../model/model_usuario.php';
        $usuario['usu_foto'] = Modelo_Usuario::Foto_Mostrable($usuario['usu_foto'] ?? null);
        echo json_encode($usuario);
    } else {
        echo json_encode(['error' => 'Usuario no encontrado']);
    }
} catch (Exception $e) {
    error_log('Error al obtener perfil: ' . $e->getMessage());
    echo json_encode(['error' => 'No se pudo obtener el perfil']);
}
?>
