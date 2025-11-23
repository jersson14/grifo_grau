<?php
require_once '../../model/model_conexion.php';

$c = conexionBD::conexionPDO();

// Primero verificar si existen las columnas necesarias
$sql_check = "SHOW COLUMNS FROM ventas_credito LIKE 'id_usuario_grifero'";
$stmt_check = $c->prepare($sql_check);
$stmt_check->execute();
$column_exists = $stmt_check->fetch();

// Si no existe la columna, agregarla
if (!$column_exists) {
    try {
        // Agregar columnas necesarias
        $c->exec("ALTER TABLE ventas_credito 
                  ADD COLUMN id_usuario_grifero INT(11) NULL AFTER id_cliente,
                  ADD COLUMN id_usuario_registro INT(11) NULL AFTER id_usuario_grifero,
                  ADD COLUMN turno ENUM('DIA','NOCHE') NULL AFTER id_usuario_registro");
        
        // Agregar índices
        $c->exec("ALTER TABLE ventas_credito 
                  ADD INDEX idx_usuario_grifero (id_usuario_grifero),
                  ADD INDEX idx_usuario_registro (id_usuario_registro)");
    } catch (Exception $e) {
        // Si falla, continuar sin error
    }
}

// Listar vales del día actual
$sql = "SELECT 
            vc.*,
            c.nombre_completo as cliente_nombre,
            CONCAT(u.usu_nombre, ' ', u.usu_apellido) as grifero_nombre
        FROM ventas_credito vc
        INNER JOIN clientes c ON vc.id_cliente = c.id_cliente
        LEFT JOIN usuario u ON vc.id_usuario_grifero = u.id_usuario
        WHERE DATE(vc.created_at) = CURDATE()
        ORDER BY vc.created_at DESC
        LIMIT 10";

$stmt = $c->prepare($sql);
$stmt->execute();
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

$response = ['data' => $data];
echo json_encode($response);
?>
