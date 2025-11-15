<?php
require_once 'model_conexion.php';

class Modelo_Productos extends conexionBD {
    
    // LISTAR PRODUCTOS
    public function Listar_Productos() {
        $c = conexionBD::conexionPDO();
        $sql = "SELECT 
                    p.id_producto,
                    p.nombre,
                    p.tipo,
                    p.precio_actual,
                    p.estado,
                    p.created_at,
                    p.updated_at,
                    CONCAT(u.usu_nombre, ' ', u.usu_apellido) as usuario_registro
                FROM productos p
                LEFT JOIN usuario u ON p.id_usuario = u.id_usuario
                ORDER BY p.id_producto ASC";
        $arreglo = array();
        $query = $c->prepare($sql);
        $query->execute();
        $resultado = $query->fetchAll(PDO::FETCH_ASSOC);
        foreach ($resultado as $resp) {
            $arreglo["data"][] = $resp;
        }
        return $arreglo;
        conexionBD::cerrar_conexion();
    }

    // REGISTRAR PRODUCTO
    public function Registrar_Producto($nombre, $tipo, $precio, $id_usuario) {
        $c = conexionBD::conexionPDO();
        $sql = "INSERT INTO productos (nombre, tipo, precio_actual, estado, created_at, id_usuario) 
                VALUES (?, ?, ?, 'ACTIVO', NOW(), ?)";
        $query = $c->prepare($sql);
        $resultado = $query->execute(array($nombre, $tipo, $precio, $id_usuario));
        if ($resultado) {
            return 1;
        } else {
            return 0;
        }
        conexionBD::cerrar_conexion();
    }

    // MODIFICAR PRODUCTO
    public function Modificar_Producto($id, $nombre, $tipo, $precio) {
        $c = conexionBD::conexionPDO();
        $sql = "UPDATE productos SET 
                    nombre = ?,
                    tipo = ?,
                    precio_actual = ?,
                    updated_at = NOW()
                WHERE id_producto = ?";
        $query = $c->prepare($sql);
        $resultado = $query->execute(array($nombre, $tipo, $precio, $id));
        if ($resultado) {
            return 1;
        } else {
            return 0;
        }
        conexionBD::cerrar_conexion();
    }

    // CAMBIAR ESTADO
    public function Cambiar_Estado_Producto($id, $estado) {
        $c = conexionBD::conexionPDO();
        $sql = "UPDATE productos SET estado = ?, updated_at = NOW() WHERE id_producto = ?";
        $query = $c->prepare($sql);
        $resultado = $query->execute(array($estado, $id));
        if ($resultado) {
            return 1;
        } else {
            return 0;
        }
        conexionBD::cerrar_conexion();
    }

    // OBTENER PRECIOS ACTUALES (para turnos)
    public function Obtener_Precios_Actuales() {
        $c = conexionBD::conexionPDO();
        $sql = "SELECT id_producto, nombre, tipo, precio_actual 
                FROM productos 
                WHERE estado = 'ACTIVO' 
                ORDER BY tipo ASC";
        $query = $c->prepare($sql);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
        conexionBD::cerrar_conexion();
    }

    // HISTORIAL DE CAMBIOS DE PRECIOS
    public function Historial_Precios() {
        $c = conexionBD::conexionPDO();
        $sql = "SELECT 
                    p.nombre,
                    p.tipo,
                    p.precio_actual,
                    p.updated_at as fecha_cambio,
                    CONCAT(u.usu_nombre, ' ', u.usu_apellido) as modificado_por
                FROM productos p
                LEFT JOIN usuario u ON p.id_usuario = u.id_usuario
                WHERE p.updated_at IS NOT NULL
                ORDER BY p.updated_at DESC
                LIMIT 50";
        $arreglo = array();
        $query = $c->prepare($sql);
        $query->execute();
        $resultado = $query->fetchAll(PDO::FETCH_ASSOC);
        foreach ($resultado as $resp) {
            $arreglo["data"][] = $resp;
        }
        return $arreglo;
        conexionBD::cerrar_conexion();
    }
}
?>
