<?php
session_start();
require_once '../../model/model_conexion.php';

$c = conexionBD::conexionPDO();

$id_usuario = $_POST['id_usuario'];

// Verificar si se subió una foto
if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
    $foto = $_FILES['foto'];
    $nombre_archivo = $foto['name'];
    $tipo_archivo = $foto['type'];
    $tamano_archivo = $foto['size'];
    $tmp_archivo = $foto['tmp_name'];
    
    // Validar tipo de archivo
    $extensiones_permitidas = array('jpg', 'jpeg', 'png', 'gif');
    $extension = strtolower(pathinfo($nombre_archivo, PATHINFO_EXTENSION));
    
    if (!in_array($extension, $extensiones_permitidas)) {
        echo 0;
        exit;
    }
    
    // Validar tamaño (máximo 2MB)
    if ($tamano_archivo > 2097152) {
        echo 0;
        exit;
    }
    
    // Crear nombre único para el archivo
    $nuevo_nombre = 'usuario_' . $id_usuario . '_' . time() . '.' . $extension;
    $ruta_destino = '../../img/usuarios/' . $nuevo_nombre;
    
    // Crear directorio si no existe
    if (!file_exists('../../img/usuarios/')) {
        mkdir('../../img/usuarios/', 0777, true);
    }
    
    // Mover archivo
    if (move_uploaded_file($tmp_archivo, $ruta_destino)) {
        // Actualizar base de datos
        $ruta_bd = 'img/usuarios/' . $nuevo_nombre;
        
        $sql = "UPDATE usuario SET usu_foto = ?, updated_at = NOW() WHERE id_usuario = ?";
        $stmt = $c->prepare($sql);
        $resultado = $stmt->execute(array($ruta_bd, $id_usuario));
        
        if ($resultado) {
            // Actualizar sesión
            $_SESSION['S_FOTO'] = $ruta_bd;
            echo 1;
        } else {
            echo 0;
        }
    } else {
        echo 0;
    }
} else {
    echo 0;
}
?>
